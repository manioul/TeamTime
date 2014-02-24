-- Création des tables si elles n'existent pas

DROP TABLE IF EXISTS TBL_HEURES;
CREATE TABLE IF NOT EXISTS TBL_HEURES (
	uid INT(11) NOT NULL,
	did INT(11) NOT NULL,
	date DATE NOT NULL,
	normales DECIMAL(4,2) NOT NULL,
	instruction DECIMAL(4,2) NOT NULL,
	simulateur DECIMAL(4,2) NOT NULL,
	statut ENUM('fixed', 'shared', 'unattr') DEFAULT 'unattr',
	PRIMARY KEY (uid, date)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `TBL_HEURES_A_PARTAGER` (
	  `centre` varchar(50) NOT NULL,
	  `team` varchar(10) NOT NULL,
	  `date` date NOT NULL,
	  `heures` decimal(4,2) NOT NULL,
	  `dispatched` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'POsitionné lorsque les heures ont été calculées',
	  `writable` tinyint(1) NOT NULL DEFAULT '1',
	  PRIMARY KEY (`centre`,`team`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Le nombre d''heures à paratager par jour';

CREATE TABLE IF NOT EXISTS `TBL_DISPATCH_HEURES` (
	  `rid` int(11) NOT NULL AUTO_INCREMENT,
	  `cids` varchar(64) NOT NULL,
	  `centre` varchar(50) NOT NULL DEFAULT 'athis',
	  `team` varchar(10) NOT NULL DEFAULT '9e',
	  `grades` varchar(60) NOT NULL DEFAULT 'pc',
	  `dids` varchar(128) DEFAULT NULL,
	  `type` enum('norm','instru','simu') NOT NULL,
	  `statut` enum('shared','fixed') NOT NULL COMMENT 'Les heures sont partagées ou fixes',
	  `heures` decimal(4,2) NOT NULL COMMENT 'Nombre de minutes allouées',
	  PRIMARY KEY (`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `TBL_DISPATCH_HEURES_USER` (
	  `rid` int(11) NOT NULL,
	  `cycles` varchar(64) NOT NULL,
	  `centre` varchar(50) NOT NULL DEFAULT 'athis',
	  `team` varchar(10) NOT NULL DEFAULT '9e',
	  `grades` varchar(60) NOT NULL DEFAULT 'pc',
	  `dispos` varchar(128) DEFAULT NULL,
	  `type` enum('norm','instru','simu') NOT NULL,
	  `statut` enum('shared','fixed') NOT NULL COMMENT 'Les heures sont partagées ou fixes',
	  `heures` decimal(4,2) NOT NULL COMMENT 'Nombre d''heures allouées (en décimal)',
	  PRIMARY KEY (`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELIMITER |
DROP PROCEDURE IF EXISTS dispatchAllHeures|
CREATE PROCEDURE dispatchAllHeures ( IN centr VARCHAR(50) , IN tea VARCHAR(10) )
BEGIN
	DECLARE done BOOLEAN DEFAULT 0;
	DECLARE dateTD DATE;
	DECLARE curDatesToDispatch CURSOR FOR SELECT date
		FROM TBL_HEURES_A_PARTAGER
		WHERE centre = centr
		AND team = tea
		AND dispatched IS FALSE
		AND writable IS TRUE;
	
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	OPEN curDatesToDispatch;

	REPEAT
	FETCH curDatesToDispatch INTO dateTD;
	IF NOT done THEN
		CALL dispatchOneDayHeures( centr , tea , dateTD );
	END IF;
	UNTIL done END REPEAT;

	CLOSE curDatesToDispatch;
END|
DROP PROCEDURE IF EXISTS dispatchHeuresBetween|
CREATE PROCEDURE dispatchHeuresBetween( IN centr CHAR(50) , IN tea CHAR(10) , IN debut DATE , IN fin DATE )
BEGIN
	DECLARE done BOOLEAN DEFAULT 0;
	DECLARE current DATE;
	DECLARE curDate CURSOR FOR SELECT date
		FROM TBL_HEURES_A_PARTAGER
		WHERE centre = centr
		AND team = tea
		AND date BETWEEN debut AND fin;

	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	OPEN curDate;

	REPEAT
	FETCH curDate INTO current;
	IF NOT done THEN
		CALL dispatchOneDayHeures( centr, tea, current);
	END IF;
	UNTIL done END REPEAT;

	CLOSE curDate;
END|
DROP PROCEDURE IF EXISTS dispatchOneDayHeures|
CREATE PROCEDURE dispatchOneDayHeures ( IN centr CHAR(50) , IN tea CHAR(10) , IN dat DATE )
BEGIN
	DECLARE done BOOLEAN DEFAULT 0;
	DECLARE gradeFixed VARCHAR(64);
	DECLARE typeFixed VARCHAR(64);
	DECLARE userid, dispoid, ruleid, eleves, instructeursId, unattr, valeurFixed, didFixed, heuresLeft INT;
	DECLARE inst, heuresEach FLOAT;
	DECLARE nbInstructeurs INT DEFAULT 5; -- Nombre de personnes (moins une) qui se partageront les heures d'instruction
	-- Recherche les dispo des utilisateurs présents
	DECLARE curDisp CURSOR FOR SELECT uid, did
		FROM TBL_L_SHIFT_DISPO
		WHERE date = dat
		AND did NOT IN (SELECT did
			FROM TBL_DISPO
			WHERE absence IS TRUE
			OR dispo = 'fmp'
			OR dispo = 'cds');
	-- Liste les heures fixes attribuées à des dispos qui sont présentes dans la grille ce jour
	DECLARE curFixed CURSOR FOR SELECT rid, grades, type, heures, dids
		FROM TBL_DISPATCH_HEURES
		WHERE statut = 'fixed'
		AND FIND_IN_SET((SELECT cid
				FROM TBL_GRILLE
				WHERE date = dat
				AND centre = centr
				AND team = tea)
			, cids);
	-- Recherche deux pc qui ont le moins d'heures d'instruction pour leur attribuer les heures disponibles
	-- Ils doivent avoir une certaine ancienneté dans l'affectation (4 mois)
	DECLARE curInstructeurs CURSOR FOR SELECT uid, SUM(instruction) AS instru
		FROM TBL_HEURES
		WHERE date BETWEEN DATE_SUB(dat, INTERVAL 4 MONTH) AND dat
		AND uid IN (SELECT uid -- utilisateur dans la bonne affectation
			FROM TBL_AFFECTATION
			WHERE centre = centr
			AND team = tea
			AND grade != 'dtch'
			AND grade != 'c'
			AND grade != 'theo'
			AND validated IS TRUE
			AND dat BETWEEN beginning AND end
			AND DATE_SUB(dat, INTERVAL 4 MONTH) BETWEEN beginning AND end)
		AND did NOT IN (SELECT did
			FROM TBL_DISPO
			WHERE (absence IS TRUE OR dispo = 'fmp' OR dispo = 'cds'))
		GROUP BY uid
		ORDER BY instru ASC
		LIMIT nbInstructeurs;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	-- Table temporaire listant les utilisateurs présents au regard du décompte d'heure
	CREATE TEMPORARY TABLE IF NOT EXISTS tmpPresents (
		uid INT(11) NOT NULL,
		grade VARCHAR(64) NOT NULL,
		did INT(11) NOT NULL,
		normales DECIMAL(4,2) NOT NULL,
		instruction DECIMAL(4,2) NOT NULL,
		simulateur DECIMAL(4,2) NOT NULL,
		statut ENUM('fixed', 'shared', 'unattr') DEFAULT 'unattr',
		rid INT(11),
		centre VARCHAR(50),
		team VARCHAR(10),
		PRIMARY KEY (uid)
	);

	DELETE FROM tmpPresents WHERE team = tea AND centre = centr;
	INSERT INTO tmpPresents
		SELECT uid, grade, 0, 0, 0, 0, 'unattr', 0, centr, tea
		FROM TBL_AFFECTATION
		WHERE centre = centr
		AND team = tea
		AND dat BETWEEN beginning AND end
		AND validated IS TRUE
		-- Les c n'ont pas d'heure
		AND grade != 'c'
		AND grade != 'theo'
		-- les utilisateurs qui ont une case remplie qui n'est pas une absence
		AND uid NOT IN (SELECT uid
			FROM TBL_L_SHIFT_DISPO
			WHERE date = dat
			AND did IN (SELECT did
				FROM TBL_DISPO
				WHERE absence IS TRUE
				OR dispo = 'fmp'
				OR dispo = 'cds')
			)
		-- cds en nuit (2)
		AND uid NOT IN (SELECT uid
			FROM TBL_L_SHIFT_DISPO
			WHERE date = dat
			AND did = (SELECT did
				FROM TBL_DISPO
				WHERE dispo = '2')
			AND uid IN (SELECT uid
				FROM TBL_AFFECTATION
				WHERE grade = 'cds'
				AND dat BETWEEN beginning AND end)
			)
		;
	-- Remplit les did
	OPEN curDisp;
	REPEAT
	FETCH curDisp INTO userid, dispoid;
	IF NOT done THEN
		UPDATE tmpPresents
		SET did = dispoid
		WHERE uid = userid;
	END IF;
	UNTIL done END REPEAT;
	SET done = 0;

	-- On vide les heures correspondant à la date
	DELETE FROM TBL_HEURES
		WHERE date = dat
		AND uid IN (SELECT uid
			FROM TBL_AFFECTATION
			WHERE centre = centr
			AND team = tea
			AND dat BETWEEN beginning AND end)
		AND statut != 'unattr'; -- les heures saisies par l'utilisateur ont un statut unattr

	OPEN curFixed;

	-- Ajout des heures fixes
	REPEAT
	FETCH curFixed INTO ruleid, gradeFixed, typeFixed, valeurFixed, didFixed;
	IF NOT done THEN
		IF typeFixed = 'norm' THEN
			UPDATE tmpPresents
			SET normales = valeurFixed
			, statut = 'fixed'
			, rid = ruleid
			WHERE (FIND_IN_SET(grade, gradeFixed) OR grade = gradeFixed)
			AND (FIND_IN_SET(did, didFixed) OR did = didFixed);
		ELSEIF typeFixed = 'instru' THEN
			UPDATE tmpPresents
			SET instruction = valeurFixed
			, statut = 'fixed'
			, rid = ruleid
			WHERE (FIND_IN_SET(grade, gradeFixed) OR grade = gradeFixed)
			AND (FIND_IN_SET(did, didFixed) OR did = didFixed);
		ELSEIF typeFixed = 'simu' THEN
			UPDATE tmpPresents
			SET simulateur = valeurFixed
			, statut = 'fixed'
			, rid = ruleid
			WHERE (FIND_IN_SET(grade, gradeFixed) OR grade = gradeFixed)
			AND (FIND_IN_SET(did, didFixed) OR did = didFixed);
		END IF;
	END IF;
	UNTIL done END REPEAT;

	CLOSE curFixed;
	SET done = 0;

	-- Vérifie si il y a des élèves et le cas échéant si des heures instruction ont été attribuées
	SELECT COUNT(uid)
	INTO eleves
	FROM TBL_AFFECTATION
	WHERE centre = centr
	AND team = tea
	AND (grade = 'c' OR grade = 'theo')
	AND dat BETWEEN beginning AND end
	AND uid NOT IN (SELECT uid
		FROM TBL_L_SHIFT_DISPO
		WHERE date = dat
		AND did IN (SELECT did
			FROM TBL_DISPO
			WHERE absence IS TRUE
			AND centre = centr
			AND team = tea));
	IF eleves > 0 THEN
		SELECT SUM(instruction)
		INTO inst
		FROM tmpPresents
		WHERE centre = centr
		AND team = tea;
	END IF;

	-- Sélectionne les utilisateurs ayant le moins d'heures d'instruction sur une certaine période (cf cursor)
	IF inst = 0 THEN
		-- Calcule les heures restantes
		SELECT (p.heures) - SUM(t.normales) - SUM(t.instruction)
		INTO heuresLeft
		FROM TBL_HEURES_A_PARTAGER AS p
		, tmpPresents AS t
		WHERE t.centre = centr
		AND t.centre = p.centre
		AND t.team = tea
		AND t.team = p.team
		AND p.date = dat;
		-- Recherche le nombre de présents qui n'ont pas encore d'heures attribuées
		SELECT COUNT(uid)
		INTO unattr
		FROM tmpPresents
		WHERE statut = 'unattr'
		AND centre = centr
		AND team = tea;

		OPEN curInstructeurs;
		REPEAT
		FETCH curInstructeurs INTO instructeursId, inst;
		IF NOT done THEN
			-- Attribue des heures d'instruction en fonction du nombre d'heures restantes à partager et le nombre de présents qui n'ont pas encore d'heures attribuées
			UPDATE tmpPresents
			SET instruction = ROUND(heuresLeft * 4 / unattr + .49) / 4, -- heuresLeft * 4 quarts d'heures / unattr + .49 pour arrondir au-dessus
			normales = ROUND(heuresLeft * 2 / unattr + .49) / 4, -- heuresLeft * 4 quarts d'heures / (2 * unattr) +.49 pour arrondir au-dessus
			statut = 'shared'
			WHERE uid = instructeursId;
		END IF;
		UNTIL done END REPEAT;
		CLOSE curInstructeurs;
	END IF;

	-- Calcule les heures restantes
	SELECT (p.heures) - SUM(t.normales) - SUM(t.instruction)
	INTO heuresLeft
	FROM TBL_HEURES_A_PARTAGER AS p
	, tmpPresents AS t
	WHERE t.centre = centr
	AND t.centre = p.centre
	AND t.team = tea
	AND t.team = p.team
	AND p.date = dat;

	SELECT heuresLeft / (SELECT COUNT(uid) FROM tmpPresents
			WHERE statut = 'unattr'
			AND centre = centr
			AND team = tea
		) INTO heuresEach;
	
	-- On ne peut avoir 0 heure sur une journée
	IF heuresEach < .25 THEN
		UPDATE tmpPresents
		SET normales = .25
		, statut = 'shared'
		WHERE statut = 'unattr'
		AND centre = centr
		AND team = tea;
	ELSE
		UPDATE tmpPresents
		SET normales = (SELECT ROUND(heuresEach * 4) / 4)
		, statut = 'shared'
		WHERE statut = 'unattr'
		AND centre = centr
		AND team = tea;
	END IF;

	REPLACE INTO TBL_HEURES
		(SELECT uid, did, dat, normales, instruction, simulateur, statut
		FROM tmpPresents
		WHERE centre = centr
		AND team = tea);

	UPDATE TBL_HEURES_A_PARTAGER
	SET dispatched = TRUE
	WHERE centre = centr
	AND team = tea
	AND date = dat;
END
|
DROP PROCEDURE IF EXISTS addDispatchSchema|
CREATE PROCEDURE addDispatchSchema( IN cycles VARCHAR(64) , IN centr VARCHAR(50) , IN tea VARCHAR(10) , IN grad VARCHAR(64) , IN dispos VARCHAR(128) , IN typ VARCHAR(64) , IN statu VARCHAR(64) , IN nbHeures DECIMAL(4,2) )
BEGIN
	DECLARE done BOOLEAN DEFAULT 0;
	DECLARE dids VARCHAR(64);
	DECLARE cids VARCHAR(64);
	DECLARE pivot VARCHAR(64);
	DECLARE tmp VARCHAR(64);
	DECLARE curDispo CURSOR FOR
		SELECT did
		FROM TBL_DISPO
		WHERE FIND_IN_SET(dispo, dispos);

	DECLARE curCycle CURSOR FOR
		SELECT cid
		FROM TBL_CYCLE
		WHERE FIND_IN_SET(vacation, cycles);

	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

	-- Création de la table utilisateur qui offre une version lisible des schémas
	CREATE TABLE IF NOT EXISTS TBL_DISPATCH_HEURES_USER (
		rid INT( 11 ) NOT NULL ,
		cycles VARCHAR( 64 ) NOT NULL ,
		centre VARCHAR( 50 ) NOT NULL DEFAULT 'athis',
		team VARCHAR( 10 ) NOT NULL DEFAULT '9e',
		grades VARCHAR( 64 ) NOT NULL DEFAULT 'pc',
		dispos VARCHAR( 128 ) DEFAULT NULL ,
		type ENUM( 'norm', 'instru', 'simu' ) NOT NULL ,
		statut ENUM( 'shared', 'fixed' ) NOT NULL COMMENT 'Les heures sont partagées ou fixes',
		heures DECIMAL( 4, 2 ) NOT NULL COMMENT 'Nombre d\'heures allouées (en décimal)',
		PRIMARY KEY ( rid )
	) ENGINE = InnoDB DEFAULT CHARSET = utf8;

	-- Création du SET dispo
	OPEN curDispo;
	REPEAT
	FETCH curDispo INTO tmp;
	IF NOT done THEN
		SET pivot = CONCAT_WS(',', dids, tmp);
		SET dids = pivot;
	END IF;
	UNTIL done END REPEAT;
	CLOSE curDispo;
	SET done = 0;

	-- Création du SET cycle
	OPEN curCycle;
	REPEAT
	FETCH curCycle INTO tmp;
	IF NOT done THEN
		SET pivot = CONCAT_WS(',', cids, tmp);
		SET cids = pivot;
	END IF;
	UNTIL done END REPEAT;
	CLOSE curCycle;
	SET done = 0;

	INSERT INTO TBL_DISPATCH_HEURES
	(rid, cids, centre, team, grades, dids, type, statut, heures)
	VALUES
	(NULL, cids, centr, tea, grad, dids, typ, statu, nbHeures);

	INSERT INTO TBL_DISPATCH_HEURES_USER
	(rid, cycles, centre, team, grades, dispos, type, statut, heures)
	VALUES
	(LAST_INSERT_ID(), cycles, centr, tea, UPPER(grad), dispos, typ, statu, nbHeures);

	-- SELECT * FROM TBL_DISPATCH_HEURES;
	-- SELECT * FROM TBL_DISPATCH_HEURES_USER;
END
|
DROP PROCEDURE IF EXISTS searchAffectation|
CREATE PROCEDURE searchAffectation( IN userid INT(11) , IN dat DATE , OUT centr VARCHAR(50) , OUT tea VARCHAR(10) , OUT grad VARCHAR(64) )
BEGIN
	SELECT centre, team, grade
	INTO centr, tea, grad
	FROM TBL_AFFECTATION
	WHERE dat BETWEEN beginning AND end
	AND uid = userid
	AND validated IS TRUE;
END
|
DROP PROCEDURE IF EXISTS addAffectation|
CREATE PROCEDURE addAffectation( IN userid INT , IN centr VARCHAR(50) , IN tea VARCHAR(10) , IN grad VARCHAR(64) , IN debut DATE , IN fin DATE )
BEGIN
	DECLARE notFound, prevFound, nextFound BOOLEAN DEFAULT 0;
	DECLARE prevAffectId, nextAffectId INT;
	DECLARE prevBeginning, prevEnd DATE;
	DECLARE prevGrade VARCHAR(64);
	DECLARE prevCentre VARCHAR(64);
	DECLARE prevTeam VARCHAR(64);
	DECLARE nextBeginning, nextEnd DATE;
	DECLARE nextGrade VARCHAR(64);
	DECLARE nextCentre VARCHAR(64);
	DECLARE nextTeam VARCHAR(64);

	DECLARE CONTINUE HANDLER FOR NOT FOUND SET notFound = 1;

	IF debut < fin THEN
		-- Supprime les périodes recouvertes entièrement
		DELETE FROM TBL_AFFECTATION
	       		WHERE beginning >= debut
			AND end <= fin
			AND uid = userid;
		-- Cherche l'affectation dont le début précède l'affectation à ajouter
		SELECT aid, centre, team, grade, beginning, end
			INTO prevAffectId, prevCentre, prevTeam, prevGrade, prevBeginning, prevEnd
			FROM TBL_AFFECTATION
			WHERE uid = userid
			AND beginning < debut
			AND end >= debut
			ORDER BY beginning DESC
			LIMIT 0, 1;

		IF NOT notFound THEN
			SET prevFound = 1;
		END IF;

		SET notFound = 0;

		-- Cherche l'affectation dont la fin suit l'affectation à ajouter
		SELECT aid, centre, team, grade, beginning, end
			INTO nextAffectId, nextCentre, nextTeam, nextGrade, nextBeginning, nextEnd
			FROM TBL_AFFECTATION
			WHERE uid = userid
			AND end > fin
			AND beginning < fin
			AND beginning > debut
			ORDER BY end ASC
			LIMIT 0, 1;

		IF NOT notFound THEN
			SET nextFound = 1;
		END IF;

		SELECT prevFound, nextFound;

		IF NOT prevFound AND NOT nextFound THEN
			-- Ajoute la nouvelle affectation
			INSERT INTO TBL_AFFECTATION
			(aid, uid, centre, team, grade, beginning, end, validated)
			VALUES
			(NULL, userid, centr, tea, grad, debut, fin, TRUE);
		ELSE
			-- Si la nouvelle affectation est identique à la précédente, on prolonge la précédente au besoin
			IF centr = prevCentre AND tea = prevTeam AND grad = prevGrade AND prevEnd < fin THEN
				UPDATE TBL_AFFECTATION
				SET end = fin
				WHERE aid = prevAffectId;
			ELSE
				-- Si la nouvelle affectation est identique à la précédente, on étend la précédente au besoin
				IF centr = nextCentre AND tea = nextTeam AND grad = nextGrade AND nextBeginning > debut THEN
					UPDATE TBL_AFFECTATION
					SET beginning = debut
					WHERE aid = prevAffectId;
				ELSE
					-- Ajoute la nouvelle affectation
					INSERT INTO TBL_AFFECTATION
					(aid, uid, centre, team, grade, beginning, end, validated)
					VALUES
					(NULL, userid, centr, tea, grad, debut, fin, TRUE);

					IF prevFound THEN
						-- Modifie la date de fin de l'affectation précédente
						UPDATE TBL_AFFECTATION
						SET end = DATE_SUB(debut, INTERVAL 1 DAY)
						WHERE aid = prevAffectId;
						-- Si la date de fin de l'affectation précédente est postérieure
						-- à la date de fin de la nouvelle affectation, on réaffecte dans
						-- l'ancien poste, après la nouvelle affectation
						IF fin < prevEnd AND NOT nextFound THEN
							INSERT INTO TBL_AFFECTATION
							(aid, uid, centre, team, grade, beginning, end, validated)
							VALUES
							(NULL, userid, prevCentre, prevTeam, prevGrade, DATE_ADD(fin, INTERVAL 1 DAY), prevEnd, TRUE);
						END IF;
					END IF;
					IF nextFound THEN
						-- Modifie la date de début de l'affectation précédente
						UPDATE TBL_AFFECTATION
						SET beginning = DATE_ADD(fin, INTERVAL 1 DAY)
						WHERE aid = nextAffectId;
						-- Si la date de fin de l'affectation précédente est postérieure
						-- à la date de fin de la nouvelle affectation, on réaffecte dans
						-- l'ancien poste, après la nouvelle affectation
						IF debut > prevBeginning AND NOT prevFound THEN
							INSERT INTO TBL_AFFECTATION
							(aid, uid, centre, team, grade, beginning, end, validated)
							VALUES
							(NULL, userid, nextCentre, nextTeam, nextGrade, nextBeginning, DATE_SUB(fin, INTERVAL 1 DAY), TRUE);
						END IF;
					END IF;
				END IF;
			END IF;
		END IF;
	END IF;
END
|
DROP PROCEDURE IF EXISTS updateDispatchSchema|
CREATE PROCEDURE updateDispatchSchema ( IN ruleid INT )
BEGIN
	DECLARE done BOOLEAN DEFAULT 0;
	DECLARE dispoids VARCHAR(64);
	DECLARE tmp VARCHAR(64);
	DECLARE pivot VARCHAR(64);
	DECLARE cycls VARCHAR(64);
	DECLARE cyclids VARCHAR(64);
	DECLARE centr VARCHAR(50);
	DECLARE tea VARCHAR(10);
	DECLARE grad VARCHAR(64);
	DECLARE disp VARCHAR(64);
	DECLARE typ VARCHAR(64);
	DECLARE statu VARCHAR(64);
	DECLARE heur DECIMAL(4,2);

	DECLARE curCycle CURSOR FOR
		SELECT vacation
		FROM TBL_CYCLE
		WHERE FIND_IN_SET(rang, (SELECT cids
			FROM TBL_DISPATCH_HEURES
			WHERE rid = ruleid)
		);

	DECLARE curDispo CURSOR FOR
		SELECT dispo
		FROM TBL_DISPO
		WHERE FIND_IN_SET(did, (SELECT dids
			FROM TBL_DISPATCH_HEURES
			WHERE rid = ruleid)
		);

	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

	SELECT centre, team, grades, type, statut, heures
		INTO centr, tea, grad, typ, statu, heur
		FROM TBL_DISPATCH_HEURES
		WHERE rid = ruleid;

	OPEN curCycle;
	REPEAT
	FETCH curCycle INTO tmp;
	IF NOT done THEN
		SET pivot = CONCAT_WS(',', cycls, tmp);
		SET cycls = pivot;
	END IF;
	UNTIL done END REPEAT;
	CLOSE curCycle;
	SET done = 0;

	OPEN curDispo;
	REPEAT
	FETCH curDispo INTO tmp;
	IF NOT done THEN
		SET pivot = CONCAT_WS(',', disp, tmp);
		SET disp = pivot;
	END IF;
	UNTIL done END REPEAT;
	CLOSE curDispo;

	REPLACE INTO TBL_DISPATCH_HEURES_USER
	(rid, cycles, centre, team, grades, dispos, type, statut, heures)
	VALUES
	(ruleid, cycls, centr, tea, UPPER(grad), disp, typ, statu, heur);
END
|
DROP PROCEDURE IF EXISTS addHeuresIndividuelles|
CREATE PROCEDURE addHeuresIndividuelles( IN userid INT, IN dateH DATE, IN normal FLOAT, IN instruc FLOAT, IN simul FLOAT )
BEGIN
	REPLACE INTO TBL_HEURES
		(uid, nom, date, normales, instruction, simulateur, statut)
		VALUES
		(userid, (SELECT nom FROM TBL_USERS WHERE uid = userid), dateH, normal, instruc, simul, 'unattr');
END
|
DROP PROCEDURE IF EXISTS attribAnneeConge|
CREATE PROCEDURE attribAnneeConge( IN nomU CHAR(64) , IN dateC DATE , IN anneeC INT )
BEGIN
	DECLARE done BOOLEAN DEFAULT 0;
	DECLARE count_rows INT;
	DECLARE userid INT;
	DECLARE super INT;

	SELECT SQL_CALC_FOUND_ROWS uid
	INTO userid
	FROM TBL_USERS
	WHERE nom = nomU;

	SELECT FOUND_ROWS()
	INTO count_rows;

	IF count_rows > 1 THEN
		SET done = 1;
	END IF;

	IF NOT done THEN
		SELECT SQL_CALC_FOUND_ROWS sdid
		INTO super
		FROM TBL_L_SHIFT_DISPO
		WHERE uid = userid
		AND date = dateC;

		SELECT FOUND_ROWS()
		INTO count_rows;

		IF count_rows > 1 THEN
			SET done = 1;
		END IF;

		IF NOT done THEN
			UPDATE TBL_VACANCES
			SET year = anneeC
			WHERE sdid = super;
		END IF;
	END IF;
END
|

DROP TRIGGER IF EXISTS deleteDispatchSchema|
CREATE TRIGGER deleteDispatchSchema
	AFTER DELETE ON TBL_DISPATCH_HEURES
	FOR EACH ROW
	DELETE FROM TBL_DISPATCH_HEURES_USER
		WHERE rid = OLD.rid|
DROP TRIGGER IF EXISTS updateDispatchSchema|
CREATE TRIGGER updateDispatchSchema
	AFTER UPDATE ON TBL_DISPATCH_HEURES
	FOR EACH ROW
	CALL updateDispatchSchema(OLD.rid)|
DROP TRIGGER IF EXISTS deleteHours|
CREATE TRIGGER deleteHours
	AFTER DELETE ON TBL_HEURES_A_PARTAGER
	FOR EACH ROW
	DELETE FROM TBL_HEURES
		WHERE date = OLD.date
			AND uid IN (SELECT uid
				FROM TBL_AFFECTATION
				WHERE centre = OLD.centre
				AND team = OLD.team
				AND OLD.date BETWEEN beginning AND end)|
DELIMITER ;

DROP VIEW IF EXISTS affectations;
CREATE VIEW affectations AS
	SELECT nom, centre, team, grade, beginning, end
	FROM TBL_AFFECTATION a
	, TBL_USERS u
	WHERE a.uid = u.uid
	ORDER BY actif DESC,nom ASC, beginning ASC;
