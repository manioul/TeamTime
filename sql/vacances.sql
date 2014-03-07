-- Requiert utilisateurs.sql pour searchAffectation
source utilisateurs.sql

CREATE TABLE IF NOT EXISTS TBL_VACANCES_A_ANNULER (
	uid INT(11) NOT NULL,
	did INT(11) NOT NULL,
	date DATE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELIMITER |
DROP PROCEDURE IF EXISTS attribAnneeConge|
CREATE PROCEDURE attribAnneeConge( IN userid INT(11) , IN dat DATE , IN anne INT )
BEGIN
	DECLARE shiftDid INT(11);
	DECLARE dispoid INT(11);
	DECLARE	debutDemiCycle DATE; -- La date de début du demi-cycle
	DECLARE	finDemiCycle DATE; -- La date de fin du demi-cycle
	DECLARE centr VARCHAR(50); -- Centre de l'utilisateur à la date dat
	DECLARE tea VARCHAR(10); -- L'équipe de l'utilisateur à la date dat
	DECLARE grad VARCHAR(64); -- Le grade de l'utilisateur à la date dat

	CALL searchAffectation(userid, dat, centr, tea, grad);

	-- Recherche le sdid en vérifiant qu'il correspond à un congé
	-- Recherche également le did pour les traitements spéciaux (cas des demi-cycles)
	SELECT l.sdid, did
	INTO shiftDid, dispoid
	FROM TBL_L_SHIFT_DISPO AS l,
	TBL_VACANCES AS v
	WHERE l.sdid = v.sdid
	AND date = dat
	AND uid = userid;

	IF dispoid = 1 THEN
		-- Définit le début et fin du demi-cycle
		CALL demiCycle(dat, centr, tea, debutDemiCycle, finDemiCycle);
		SET dat = debutDemiCycle;
		REPEAT
			SELECT l.sdid, did
			INTO shiftDid, dispoid
			FROM TBL_L_SHIFT_DISPO AS l,
			TBL_VACANCES AS v
			WHERE l.sdid = v.sdid
			AND date = dat
			AND uid = userid;
			IF dispoid = 1 THEN
				-- Attribue l'année du congé
				CALL __attribAnneeConge(shiftDid, anne);
			ELSE
				-- Le did n'est pas celui attendu : le type de congé ne correspond pas.
				CALL messageSystem('Le type de congé ne correspond pas : on attendait un did de 1', 'ERREUR', 'attribAnneeConge', 'erreur de did', NULL, CONCAT('userid:', userid, ';date:', dat, ';anne:', anne, ';dispoid:', dispoid));
			END IF;
			SET dat = DATE_ADD(dat, INTERVAL 1 DAY);
		UNTIL dat > finDemiCycle END REPEAT;
	ELSE
		CALL __attribAnneeConge(shiftDid, anne);
	END IF;
END
|
DROP PROCEDURE IF EXISTS __attribAnneeConge|
CREATE PROCEDURE __attribAnneeConge( IN shiftDid INT(11) , IN anne INT )
BEGIN
	UPDATE TBL_VACANCES
	SET year = anne
	WHERE sdid = shiftDid;
END
|
DROP PROCEDURE IF EXISTS dateLimiteConges|
CREATE PROCEDURE dateLimiteConges( IN year INT(11) , IN centr VARCHAR(50) , OUT dateLimite DATE )
BEGIN
	-- Recherche la date limite de dépôt des congés

	-- Recherche si une date a été définie pour l'année concernée
	SELECT valeur
	INTO dateLimite
	FROM TBL_CONSTANTS
	WHERE nom LIKE CONCAT("dlCong_", year , '_' ,  centr);
	-- Si aucune date n'a été définie, on se base sur la date par défaut
	IF dateLimite IS NULL THEN
		SELECT CONCAT(year + 1, "-" , valeur)
		INTO dateLimite
		FROM TBL_CONSTANTS
		WHERE nom = CONCAT("dlCong_default_", centr);
	END IF;
	SET dateLimite = CAST(dateLimite AS DATE);
END
|
DROP PROCEDURE IF EXISTS demiCycle|
CREATE PROCEDURE demiCycle( IN dat DATE , IN centr VARCHAR(50) , IN tea VARCHAR(10) , OUT debutDemiCycle DATE , OUT finDemiCycle DATE )
BEGIN
	DECLARE vac VARCHAR(8);

	-- Vérifie que le jour n'est pas un jour de repos
	SELECT vacation
	INTO vac
	FROM TBL_CYCLE AS c,
	TBL_GRILLE AS g
	WHERE date = dat
	AND (c.centre = centr OR c.centre = 'all')
	AND (c.team = tea OR c.team = 'all')
	AND (g.centre = centr OR g.centre = 'all')
	AND (g.team = tea OR g.team = 'all')
	AND c.cid = g.cid;

	IF vac != 'Repos' THEN
		-- TODO Ceci n'est pas très portable...
		-- Recherche la date qui n'est pas un jour de repos deux jours avant
		-- un demi-cycle dure 3 jours
		SELECT MIN(date)
		INTO debutDemiCycle
		FROM TBL_GRILLE AS g,
		TBL_CYCLE AS c
		WHERE c.cid = g.cid
		AND c.vacation != 'Repos'
		AND date >= DATE_SUB(dat, INTERVAL 2 DAY)
		AND (c.centre = centr OR c.centre = 'all')
		AND (c.team = tea OR c.team = 'all')
		AND (g.centre = centr OR g.centre = 'all')
		AND (g.team = tea OR g.team = 'all');
		SELECT MAX(date)
		INTO finDemiCycle
		FROM TBL_GRILLE AS g,
		TBL_CYCLE AS c
		WHERE c.cid = g.cid
		AND c.vacation != 'Repos'
		AND date <= DATE_ADD(dat, INTERVAL 2 DAY)
		AND (c.centre = centr OR c.centre = 'all')
		AND (c.team = tea OR c.team = 'all')
		AND (g.centre = centr OR g.centre = 'all')
		AND (g.team = tea OR g.team = 'all');
	ELSE
		-- Le jour n'est pas un jour de travail
		CALL messageSystem(CONCAT('Le jour attendu est un jour de travail, or le jour ', dat, ' est un ', vac), 'ERREUR', 'demiCycle', 'invalid day', CONCAT('date:', dat, ';centre:', centr, ';team:', tea, ';debutDemiCycle:', debutDemiCycle, ';finDemiCycle:', finDemiCycle));
	END IF;
END
|
DROP PROCEDURE IF EXISTS addDispo|
CREATE PROCEDURE addDispo( IN userid INT(11) , IN dat DATE , IN disponibilite VARCHAR(16) , IN oldDisponibilite VARCHAR(16) , IN perequation BOOLEAN )
BEGIN
	-- /!\
	-- La date détermine l'affectation de l'utilisateur
	-- Ceci est à prendre en considération dans le cas de péréquations
	-- /!\
	DECLARE isReadOnly BOOLEAN DEFAULT 0;
	DECLARE dispoid INT(11);
	DECLARE	typeDecompte VARCHAR(255);
	DECLARE vac VARCHAR(8);
	DECLARE centr VARCHAR(50); -- Centre de l'utilisateur à la date dat
	DECLARE tea VARCHAR(10); -- L'équipe de l'utilisateur à la date dat
	DECLARE grad VARCHAR(64); -- Le grade de l'utilisateur à la date dat

	CALL searchAffectation(userid, dat, centr, tea, grad);

	-- Vérifie si la date est éditable
	SELECT readOnly
	INTO isReadOnly
	FROM TBL_GRILLE
	WHERE date = dat
	AND (centre = centr OR centre = 'all')
	AND (team = tea OR team = 'all');

	-- Vérifie que la date correspond à un jour travaillé si il ne s'agit pas d'une péreq
	IF NOT perequation THEN
		SELECT vacation
		INTO vac
		FROM TBL_CYCLE AS c,
		TBL_GRILLE AS g
		WHERE c.cid = g.cid
		AND date = dat
		AND (g.centre = centr OR g.centre = 'all')
		AND (g.team = tea OR g.team = 'all')
		AND (c.centre = centr OR c.centre = 'all')
		AND (c.team = tea OR c.team = 'all');
		IF vac = 'Repos' THEN
			SET isReadOnly = 0;
		END IF;
	END IF;

	IF NOT isReadOnly THEN
			
		-- Supprime l'ancienne dispo
		IF oldDisponibilite != "" THEN
			CALL delDispo( userid, dat, oldDisponibilite);
		END IF;
		-- Vérifie si la nouvelle dispo est un congé
		SELECT did, `type decompte`
		INTO dispoid, typeDecompte
		FROM TBL_DISPO
		WHERE dispo = disponibilite
		AND (centre = centr OR centre = 'all')
		AND (team = tea OR team = 'all');

		CALL messageSystem(CONCAT(dispoid, ' ', typeDecompte), 'DEBUG', 'addDispo', NULL, CONCAT('dispoid:', dispoid, ';typeDecompte:', typeDecompte));
		-- Si la dispo est un congé
		IF typeDecompte = 'conges' THEN
			CALL addConges( userid, dat, dispoid, perequation );
		ELSE
			INSERT INTO TBL_L_SHIFT_DISPO
			(date, uid, did, pereq)
			VALUES
			(dat, userid, dispoid, perequation);
		END IF;
	ELSE -- La date n'est pas éditable
		CALL messageSystem("La date n'est pas éditable", 'USER', 'addDispo', 'read only', NULL);
	END IF;
END
|
DROP PROCEDURE IF EXISTS addConges|
CREATE PROCEDURE addConges( IN userid INT(11) , IN dat DATE , IN dispoid INT(11) , IN perequation BOOLEAN )
BEGIN
	DECLARE isReadOnly BOOLEAN DEFAULT 0;
	DECLARE	congeDispo BOOLEAN DEFAULT 1;
	DECLARE	anneeConge INT(11); -- l'année du congé
	DECLARE	reliquat INT(11); -- le reliquat de congés de ce type
	DECLARE	dateLimite VARCHAR(10); -- la date limite de dépôt des congés
	DECLARE	debutDemiCycle DATE; -- La date de début du demi-cycle
	DECLARE	finDemiCycle DATE; -- La date d efin du demi-cycle
	DECLARE centr VARCHAR(50); -- Centre de l'utilisateur à la date dat
	DECLARE tea VARCHAR(10); -- L'équipe de l'utilisateur à la date dat
	DECLARE grad VARCHAR(64); -- Le grade de l'utilisateur à la date dat

	CALL searchAffectation(userid, dat, centr, tea, grad);

	-- Vérifie si la date est éditable
	SELECT readOnly
	INTO isReadOnly
	FROM TBL_GRILLE
	WHERE date = dat
	AND (centre = centr OR centre = 'all')
	AND (team = tea OR team = 'all');

	IF NOT isReadOnly THEN
		-- Recherche la date limite des congés)s 
		CALL dateLimiteConges(YEAR(dat)-1, centr, dateLimite);

		-- Recherche les congés identiques sur l'année qui seraient postérieurs TODO
		-- SELECT 
		-- FROM TBL_VACANCES AS v,
		-- TBL_L_SHIFT_DISPO AS l
		-- WHERE v.sdid = l.sdid
		-- AND year = 
		-- AND did = dispoid
		-- AND date > dat
		-- ORDER BY date DESC;

		-- Si le congé est situé avant la date limite de l'année passée
		IF dat <= dateLimite THEN
			-- On recherche si il reste des congés de ce type
			-- sur l'année passée
			SELECT quantity - COUNT(v.sdid)
			INTO reliquat
			FROM TBL_VACANCES AS v,
			TBL_L_SHIFT_DISPO AS l,
			TBL_DISPO AS d
			WHERE l.did = dispoid
			AND d.did = l.did
			AND v.sdid = l.sdid
			AND uid = userid
			AND year = YEAR(dat) - 1;
			CALL messageSystem('Reliquat de congés', 'DEBUG', 'addConges', NULL, CONCAT('reliquat_', YEAR(dat) - 1, ':', reliquat, ';uid:', userid));
			IF reliquat > 0 THEN
				SET anneeConge = YEAR(dat) - 1;
			ELSE
				-- On recherche si il reste des congés de ce type
				-- sur l'année en cours
				SELECT quantity - COUNT(v.sdid)
				INTO reliquat
				FROM TBL_VACANCES AS v,
				TBL_L_SHIFT_DISPO AS l,
				TBL_DISPO AS d
				WHERE l.did = dispoid
				AND d.did = l.did
				AND v.sdid = l.sdid
				AND uid = userid
				AND year = YEAR(dat);
				CALL messageSystem('Reliquat de congés', 'DEBUG', 'addConges', NULL, CONCAT('reliquat_', YEAR(dat), ':', reliquat, ';uid:', userid));
				IF reliquat > 0 THEN
					SET anneeConge = YEAR(dat);
				ELSE
					-- Plus de congé de ce type disponible
					SET congeDispo = FALSE;
					CALL messageSystem('Plus de congé de ce type disponible', 'USER', 'addConges', 'Plus de congé', NULL);
				END IF;
			END IF;
		ELSE
			-- On recherche si il reste des congés de ce type
			-- sur l'année en cours
			SELECT quantity - COUNT(v.sdid)
			INTO reliquat
			FROM TBL_VACANCES AS v,
			TBL_L_SHIFT_DISPO AS l,
			TBL_DISPO AS d
			WHERE l.did = dispoid
			AND d.did = l.did
			AND v.sdid = l.sdid
			AND uid = userid
			AND year = YEAR(dat);
			CALL messageSystem('Reliquat de congés', 'DEBUG', 'addConges', NULL, CONCAT('reliquat:', reliquat, ';uid:', userid));
			IF reliquat > 0 THEN
				SET anneeConge = YEAR(dat);
			ELSE
				-- Plus de congé de ce type disponible
				SET congeDispo = FALSE;
				CALL messageSystem('Plus de congé de ce type disponible', 'USER', 'addConges', 'Plus de congé', NULL);
			END IF;
		END IF;

		IF congeDispo IS TRUE THEN
			-- Traitement du cas particulier des congés en demi-cycle
			IF dispoid = 1 THEN
				CALL demiCycle(dat, centr, tea, debutDemiCycle, finDemiCycle);
				SET dat = debutDemiCycle;
				REPEAT
					-- Ajout des congés dans la table
					CALL __addConges(userid, dat, dispoid, anneeConge, perequation);
					SET dat = DATE_ADD(dat, INTERVAL 1 DAY);
				UNTIL dat > finDemiCycle END REPEAT;
			ELSE
				-- Ajout des congés dans la table
				CALL __addConges(userid, dat, dispoid, anneeConge, perequation);
			END IF;
		END IF;
	ELSE -- La date n'est pas éditable
		CALL messageSystem("La date n'est pas éditable", 'USER', 'addConges', 'read only', NULL);
	END IF;
END
|
DROP PROCEDURE IF EXISTS __addConges|
CREATE PROCEDURE __addConges( IN userid INT(11) , IN dat DATE , IN dispoid INT(11) , IN anneeConge INT(11) , IN perequation BOOLEAN )
BEGIN
	INSERT INTO TBL_L_SHIFT_DISPO
	(date, uid, did, pereq)
	VALUES
	(dat, userid, dispoid, perequation);
	INSERT INTO TBL_VACANCES
	(sdid, etat, year)
	VALUES
	(LAST_INSERT_ID(), 0, anneeConge);
END
|
DROP PROCEDURE IF EXISTS delDispo|
CREATE PROCEDURE delDispo( IN userid INT(11), IN dat DATE , IN disponibilite VARCHAR(16) )
BEGIN
	-- /!\
	-- La date déterrmine l'affectation de l'utilisateur
	-- Ceci est à prendre en considération dans le cas de péréquations
	-- /!\
	DECLARE isReadOnly BOOLEAN DEFAULT 0;
	DECLARE dispoid INT(11);
	DECLARE	isConge INT(11) DEFAULT 0;
	DECLARE	typeDecompte VARCHAR(255);
	DECLARE centr VARCHAR(50); -- Centre de l'utilisateur à la date dat
	DECLARE tea VARCHAR(10); -- L'équipe de l'utilisateur à la date dat
	DECLARE grad VARCHAR(64); -- Le grade de l'utilisateur à la date dat

	CALL searchAffectation(userid, dat, centr, tea, grad);

	-- Vérifie si la date est éditable
	SELECT readOnly
	INTO isReadOnly
	FROM TBL_GRILLE
	WHERE date = dat
	AND (centre = centr OR centre = 'all')
	AND (team = tea OR team = 'all');

	IF NOT isReadOnly THEN
		-- Vérifie si la dispo est un congé
		SELECT did, `type decompte`
		INTO dispoid, typeDecompte
		FROM TBL_DISPO
		WHERE dispo = disponibilite
		AND (centre = centr OR centre = 'all')
		AND (team = tea OR team = 'all');

		-- Si la dispo est un congé
		IF typeDecompte = 'conges' THEN
			CALL delConges( userid, dat, dispoid, NULL, perequation );
		ELSE
			DELETE FROM TBL_L_SHIFT_DISPO
			WHERE uid = userid
			AND did = dispoid
			AND date = dat
			AND pereq IS FALSE
			LIMIT 1;
		END IF;
	ELSE -- La date n'est pas éditable
		CALL messageSystem("La date n'est pas éditable", 'USER', 'addConges', 'read only', NULL);
	END IF;
END
|
DROP PROCEDURE IF EXISTS delConges|
CREATE PROCEDURE delConges( IN userid INT(11) , IN dat DATE , IN dispoid INT(11) , IN anneeConge INT(11) , IN perequation BOOLEAN )
BEGIN
	-- anneeConge doit être NULL sauf dans le cas de péréquation
	-- /!\
	-- Dans le cas de péréquations, la date permet de définir l'affectation
	-- /!\
	DECLARE isReadOnly BOOLEAN DEFAULT 0;
	DECLARE	shiftDid INT(11); -- sdid du congé
	DECLARE	etatConge INT(11); -- etat du congé
	DECLARE	debutDemiCycle DATE; -- La date de début du demi-cycle
	DECLARE	finDemiCycle DATE; -- La date d efin du demi-cycle
	DECLARE centr VARCHAR(50); -- Centre de l'utilisateur à la date dat
	DECLARE tea VARCHAR(10); -- L'équipe de l'utilisateur à la date dat
	DECLARE grad VARCHAR(64); -- Le grade de l'utilisateur à la date dat

	CALL searchAffectation(userid, dat, centr, tea, grad);

	IF NOT perequation THEN
		-- Vérifie si la date est éditable
		SELECT readOnly
		INTO isReadOnly
		FROM TBL_GRILLE
		WHERE date = dat
		AND (centre = centr OR centre = 'all')
		AND (team = tea OR team = 'all');
	ELSE
		SET isReadOnly = 0;
	END IF;

	IF NOT isReadOnly THEN
		-- Cas particulier des congés en demi-cycle
		IF dispoid = 1 THEN
			IF NOT perequation THEN
				CALL demiCycle(dat, centr, tea, debutDemiCycle, finDemiCycle);
				SET dat = debutDemiCycle;
				REPEAT
				-- Supprime des congés de la table
				CALL __delConges(userid, dat, dispoid, anneeConge, 0);
				SET dat = DATE_ADD(dat, INTERVAL 1 DAY);
				UNTIL dat > finDemiCycle END REPEAT;
			ELSE
				-- Supprime des congés de la table
				CALL __delConges(userid, dat, dispoid, anneeConge, 1);
				CALL __delConges(userid, dat, dispoid, anneeConge, 1);
				CALL __delConges(userid, dat, dispoid, anneeConge, 1);
			END IF;
		ELSE
			-- Supprime des congés de la table
			CALL __delConges(userid, dat, dispoid, anneeConge, perequation);
		END IF;
	ELSE -- La date n'est pas éditable
		CALL messageSystem("La date n'est pas éditable", 'USER', 'addConges', 'read only', NULL);
	END IF;
END
|
DROP PROCEDURE IF EXISTS __delConges|
CREATE PROCEDURE __delConges( IN userid INT(11) , IN dat DATE , IN dispoid INT(11) , IN anneeConge INT(11) , IN perequation BOOLEAN )
BEGIN
	DECLARE shiftDid INT(11); -- sdid du congé
	DECLARE	dateLimite DATE; -- date limite des congés de l'année précédente
	DECLARE	etatConge INT(11); -- etat du congé
	DECLARE	congeBougeable INT(11); -- Le sdid d'un congé qui peut prendre l'année libérée
	
	-- Recherche le sdid du congé
	SELECT sdid
	INTO shiftDid
	FROM TBL_L_SHIFT_DISPO
	WHERE date = dat
	AND uid = userid
	AND pereq = perequation
	LIMIT 1;
	-- Supprime le congé
	DELETE FROM TBL_L_SHIFT_DISPO
	WHERE sdid = shiftDid;
	IF NOT perequation THEN
		-- Recherche l'état du congé
		SELECT etat
		INTO etatConge
		FROM TBL_VACANCES
		WHERE sdid = shiftDid;
		-- Si le congé a été déposé
		IF etatConge > 0 THEN
			INSERT INTO TBL_VACANCES_A_ANNULER
			(uid, did, date)
			VALUES
			(userid, dispoid, dat);
		END IF;
		-- Recherche l'année du congé
		SELECT annee
		INTO anneeConge
		FROM TBL_VACANCES
		WHERE sdid = shiftDid;
		-- Si l'année du congé est l'année qui précède l'année de la date du congé
		IF anneeConge < YEAR(dat) THEN
			-- On recherche la date limite des congés
			CALL dateLimiteConges(anneeConge, dateLimite);
			-- On recherche les congés de même type entre le début d'année et la date limite qui sont posés sur l'année suivante
			SELECT v.sdid
			INTO congeBougeable
			FROM TBL_VACANCES AS v,
			TBL_L_SHIFT_DISPO AS l
			WHERE l.sdid = v.sdid
			AND YEAR(date) = YEAR(dat)
			AND date <= dateLimite
			AND year = YEAR(dat)
			AND uid = userid
			ORDER BY date ASC
			LIMIT 1;
			-- On attribue l'année au congé
			UPDATE TBL_VACANCES
			SET year = YEAR(dat) - 1
			WHERE sdid = congeBougeable;
		END IF;
	END IF;
	-- Supprime le congé de la table des congés
	DELETE FROM TBL_VACANCES
	WHERE sdid = shiftDid;
END
|
DELIMITER ;
