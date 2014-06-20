DELIMITER |
-- CREATION/SUPPRESSION UTILISATEURS
DROP PROCEDURE IF EXISTS __createUtilisateurDb|
CREATE PROCEDURE __createUtilisateurDb ( IN uid_ SMALLINT(6) , IN passwd VARCHAR(64) )
BEGIN
	INSERT INTO mysql.user
		(Host, User, Password)
		VALUES
		('localhost', CONCAT('ttm.', uid_), PASSWORD(passwd));
	INSERT INTO mysql.db
		(Host, Db, User, Select_priv, Insert_priv, Update_priv, Delete_priv, Execute_priv, Create_tmp_table_priv)
		VALUES
		('localhost', 'ttm', CONCAT('ttm.', uid_), 'Y', 'Y', 'Y', 'Y', 'Y', 'Y'); 
	FLUSH PRIVILEGES;
END
|
-- Accepte l'inscription d'un utilisateur prépare les informations pour le compte
DROP PROCEDURE IF EXISTS acceptUser|
CREATE PROCEDURE acceptUser( IN id_ INT(11), IN dateD_ DATE, IN dateF_ DATE, IN grade_ VARCHAR(64), IN classe_ VARCHAR(10) )
BEGIN
	UPDATE TBL_SIGNUP_ON_HOLD
		SET url = SHA1(CONCAT(NOW(), RAND()))
		, beginning = dateD_
		, end = dateF_
		, grade = grade_
		, classe = classe_
		WHERE id = id_;

END
|
-- Création d'un utilisateur à partir d'un enregistrement (signup)
-- La longueur des mots de passe est tronquée à 40 caractères
DROP PROCEDURE IF EXISTS createFromSignup|
CREATE PROCEDURE createFromSignup( IN url_ VARCHAR(40), login_ VARCHAR(15), password_ VARCHAR(40), IN dbpasswd_ VARCHAR(64) )
BEGIN
	DECLARE nom_, prenom_, grade_ VARCHAR(64);
	DECLARE email_ VARCHAR(128);
	DECLARE centre_ VARCHAR(50);
	DECLARE team_, classe_ VARCHAR(10);
	DECLARE beginning_, end_ DATE;
	DECLARE uid_ SMALLINT(6);
	DECLARE count_ INT(11);

	-- Recherche si le login est déjà utilisé
	SELECT COUNT(*)
	INTO count_
	FROM TBL_USERS
	WHERE login = 'login_';

	-- Le login existe déjà
	IF count_ != 0 THEN
		-- Un message est envoyé avec comme destinataire la clé unique de l'utilisateur
		-- (url dans TBL_SIGNUP_ON_HOLD)
		INSERT INTO TBL_MESSAGES_SYSTEME
		(mid, utilisateur, catégorie, appelant, short, message, contexte, timestamp)
		VALUES
		(NULL, url_, 'ERREUR', 'createFromSignup', 'duplicate login', 'Le login existe déjà dans la base', CONCAT('login:', login_, ';nom:', nom_, ';prenom:', prenom_, ';email:', email_));
	ELSE
		-- Recherche les noms et prénoms
		SELECT nom, prenom, email, beginning, end, grade, classe
		INTO nom_, prenom_, email_, beginning_, end_, grade_, classe_
		FROM TBL_SIGNUP_ON_HOLD
		WHERE url = url_;

		-- Suppression de la demande d'enregistrement
		DELETE FROM TBL_SIGNUP_ON_HOLD
		WHERE url = 'url_';

		CALL createUser(nom_, prenom_, login_, email_, password_, FALSE, 50, TRUE, TRUE, '', dbpasswd_, centre_, team_, grade_, classe_, beginning_, end_);
	END IF;
END
|
DROP PROCEDURE IF EXISTS createUser|
CREATE PROCEDURE createUser( IN nom_ VARCHAR(64), IN prenom_ VARCHAR(64), IN login_ VARCHAR(15), IN email_ VARCHAR(128), IN password_ VARCHAR(255), IN locked_ BOOLEAN, IN poids_ SMALLINT(6), IN actif_ BOOLEAN, IN showtipoftheday_ BOOLEAN, IN page_ VARCHAR(255), IN dbpasswd_ VARCHAR(64), IN centre_ VARCHAR(50), IN team_ VARCHAR(10), IN grade_ VARCHAR(64), IN beginning_ DATE, IN end_ DATE )
BEGIN
	DECLARE count_ INT(11);
	DECLARE uid_ SMALLINT(6);

	-- Recherche un email ou un login identique
	SELECT COUNT(*)
	INTO count_
	FROM TBL_USERS
	WHERE email = email_
	OR login = login_;

	IF count_ = 0 THEN
		CALL messageSystem(CONCAT("Création de l'utilisateur ", nom_), "USER", 'createUser', "Création utilissateur", CONCAT('nom:', nom_, ';prenom:', prenom_, ';login:', login_, ';email:', email_, ';via:', USER()));

		INSERT INTO TBL_USERS
		(nom, prenom, login, email, sha1, locked, poids, actif, showtipoftheday, page)
		VALUES
		(nom_, prenom_, login_, email_, SHA1(CONCAT(login_, password_)), locked_, poids_, actif_, showtipoftheday_, page_);

		SET uid_ = LAST_INSERT_ID();

		CALL __createUtilisateurDb(uid_, dbpasswd_);
		
		CALL addRole(uid_, 'my_edit', centre_, team_, beginning_, end_, '', TRUE);
		
		INSERT INTO TBL_AFFECTATION
		(aid, uid, centre, team, grade, beginning, end, validated)
		VALUES
		(NULL, uid_, centre_, team_, grade_, beginning_, end_, TRUE);

		-- Ajoute les anciennetés de l'utilisateur
		CALL setAncienneteUser(uid_, centre_, team_);
	ELSE
		CALL messageSystem("Création de l'utilisateur impossible : le login ou le mail sont déjà utilisés", "USER", 'createUser', "duplicate user info", CONCAT('nom:', nom_, ';prenom:', prenom_, ';login:', login_, ';email:', email_));
	END IF;
END
|
DROP PROCEDURE IF EXISTS __deleteUtilisateurDb|
CREATE PROCEDURE __deleteUtilisateurDb (IN uid_ SMALLINT(6))
BEGIN
	DELETE FROM mysql.db WHERE User = CONCAT('ttm.', uid_);
	DELETE FROM mysql.user WHERE User = CONCAT('ttm.', uid_);
	FLUSH PRIVILEGES;
END
|
DROP PROCEDURE IF EXISTS deleteUser|
CREATE PROCEDURE deleteUser( IN uid_ SMALLINT(6) )
BEGIN
	CALL __deleteUtilisateurDb( uid_ );
	UPDATE TBL_USERS
	SET actif = FALSE
	WHERE uid = uid_;
	DELETE FROM TBL_ROLES WHERE uid = uid_;
END
|
DROP PROCEDURE IF EXISTS reallyDeleteUser|
CREATE PROCEDURE reallyDeleteUser( IN uid_ SMALLINT(6) )
BEGIN
	CALL deleteUser(uid_);
	DELETE FROM TBL_USERS WHERE uid = uid_;
	-- DELETE FROM TBL_AFFECTATION WHERE uid = uid_;
	-- DELETE FROM TBL_L_SHIFT_DISPO WHERE uid = uid_;
	-- DELETE FROM TBL_ADRESSES WHERE uid = uid_;
	-- DELETE FROM TBL_PHONE WHERE uid = uid_;
	-- DELETE FROM TBL_HEURES WHERE uid = uid_;
	-- DELETE FROM TBL_EVENEMENTS_SPECIAUX WHERE uid = uid_;
	-- DELETE FROM TBL_ANCIENNETE_EQUIPE WHERE uid = uid_;
END
|
-- ROLES
DROP PROCEDURE IF EXISTS addRole|
CREATE PROCEDURE addRole( IN uid_ SMALLINT(6), IN role_ VARCHAR(10), IN centre_ VARCHAR(50), IN team_ VARCHAR(10), IN beginning_ DATE, IN end_ DATE, IN commentaire_ VARCHAR(150), IN confirmed_ BOOLEAN )
BEGIN
	-- On ne vérifie pas si l'appelant a le droit d'attribuer le role
	-- la vérification doit être effectuée côté front-office
	DECLARE rid_ INT(11) DEFAULT NULL;
	DECLARE debut,fin DATE;

	SELECT rid_, debut, fin
		INTO rid_, debut, fin
		FROM TBL_ROLES
		WHERE uid = uid_
		AND role = role_
		AND centre = centre_
		AND team = team_
		AND (beginning BETWEEN beginning_ AND end_
			OR end BETWEEN beginning_ AND end_
			OR beginning_ BETWEEN beginning AND end
		       	OR end_ BETWEEN beginning AND end);
	IF rid_ IS NULL THEN
		INSERT INTO TBL_ROLES
			(uid, role, centre, team, beginning, end, commentaire, confirmed)
			VALUES
			(uid_, role_, centre_, team_, beginning_, end_, commentaire_, confirmed_);
	ELSE
		IF beginning_ < debut THEN
			IF end_ > fin THEN
				UPDATE TBL_ROLE
				SET beginning = beginning_
				, end = end_
				WHERE rid = rid_;
			ELSE
				UPDATE TBL_ROLE
				SET beginning = beginning_
				WHERE rid = rid_;
			END IF;
		ELSE
			IF end_ > fin THEN
				UPDATE TBL_ROLE
				SET end = end_
				WHERE rid = rid_;
			END IF;
		END IF;
	END IF;
END
|
-- AFFECTATIONS
DROP PROCEDURE IF EXISTS searchAffectation|
CREATE PROCEDURE searchAffectation( IN uid_ SMALLINT(6) , IN dat DATE , OUT centr VARCHAR(50) , OUT tea VARCHAR(10) , OUT grad VARCHAR(64) )
BEGIN
	SELECT centre, team, grade
	INTO centr, tea, grad
	FROM TBL_AFFECTATION
	WHERE dat BETWEEN beginning AND end
	AND uid = uid_
	AND validated IS TRUE;
END
|
DROP PROCEDURE IF EXISTS addAffectation|
CREATE PROCEDURE addAffectation( IN uid_ INT , IN centre_ VARCHAR(50) , IN team_ VARCHAR(10) , IN grade_ VARCHAR(64) , IN debut DATE , IN fin DATE )
BEGIN
	DECLARE notFound, prevFound, nextFound BOOLEAN DEFAULT 0;
	DECLARE prevAffectId, nextAffectId INT;
	DECLARE prevBeginning, prevEnd DATE;
	DECLARE prevGrade VARCHAR(64);
	DECLARE prevCentre VARCHAR(50);
	DECLARE prevTeam VARCHAR(10);
	DECLARE nextBeginning, nextEnd DATE;
	DECLARE nextGrade VARCHAR(64);
	DECLARE nextCentre VARCHAR(50);
	DECLARE nextTeam VARCHAR(10);

	DECLARE CONTINUE HANDLER FOR NOT FOUND SET notFound = 1;

	IF debut < fin THEN
		-- Supprime les périodes recouvertes entièrement
		DELETE FROM TBL_AFFECTATION
	       		WHERE beginning >= debut
			AND end <= fin
			AND uid = uid_;
		-- Cherche l'affectation dont le début précède l'affectation à ajouter
		SELECT aid, centre, team, grade, beginning, end
			INTO prevAffectId, prevCentre, prevTeam, prevGrade, prevBeginning, prevEnd
			FROM TBL_AFFECTATION
			WHERE uid = uid_
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
			WHERE uid = uid_
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
			(NULL, uid_, centre_, team_, grade_, debut, fin, TRUE);
		ELSE
			-- Si la nouvelle affectation est identique à la précédente, on prolonge la précédente au besoin
			IF centre_ = prevCentre AND team_ = prevTeam AND grade_ = prevGrade AND prevEnd < fin THEN
				UPDATE TBL_AFFECTATION
				SET end = fin
				WHERE aid = prevAffectId;
			ELSE
				-- Si la nouvelle affectation est identique à la précédente, on étend la précédente au besoin
				IF centre_ = nextCentre AND team_ = nextTeam AND grade_ = nextGrade AND nextBeginning > debut THEN
					UPDATE TBL_AFFECTATION
					SET beginning = debut
					WHERE aid = prevAffectId;
				ELSE
					-- Ajoute la nouvelle affectation
					INSERT INTO TBL_AFFECTATION
					(aid, uid, centre, team, grade, beginning, end, validated)
					VALUES
					(NULL, uid_, centre_, team_, grade_, debut, fin, TRUE);

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
							(NULL, uid_, prevCentre, prevTeam, prevGrade, DATE_ADD(fin, INTERVAL 1 DAY), prevEnd, TRUE);
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
							(NULL, uid_, nextCentre, nextTeam, nextGrade, nextBeginning, DATE_SUB(fin, INTERVAL 1 DAY), TRUE);
						END IF;
					END IF;
				END IF;
			END IF;
		END IF;

		-- Met à jour l'ancienneté équipe
		CALL setAncienneteAffectQualif(uid_, centre_, team_);
	END IF;
END
|
-- Cherche l'ancienneté qualifiée (statut C non comptabilisé) dans une affectation de l'agent
DROP PROCEDURE IF EXISTS setAncienneteAffectQualif|
CREATE PROCEDURE setAncienneteAffectQualif( IN uid_ SMALLINT(6), IN centre_ VARCHAR(50), IN team_ VARCHAR(10) )
BEGIN
	DECLARE done BOOLEAN DEFAULT 0;
	DECLARE ancid_ INT(11);
	DECLARE prevCentre VARCHAR(50);
	DECLARE prevTeam VARCHAR(10);
	DECLARE beginning_, prevBeginning, end_ DATE;
	DECLARE curAnciennete CURSOR FOR
		SELECT centre, team, beginning
		FROM TBL_AFFECTATION
		WHERE uid = uid_
		AND grade IN (SELECT nom
			FROM TBL_CONFIG_AFFECTATIONS
			WHERE description NOT LIKE '%Élève%'
			AND type = 'grade')
		ORDER BY beginning DESC;
	
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	SELECT ancid, beginning
	INTO ancid_, beginning_
	FROM TBL_ANCIENNETE_EQUIPE
	WHERE uid = uid_
	AND centre = centre_
	AND team = team_
	ORDER BY beginning DESC
	LIMIT 1;

	IF ancid_ IS NULL THEN
		SELECT beginning, end
		INTO beginning_, end_
		FROM TBL_AFFECTATION
		WHERE uid = uid_
		AND centre = centre_
		AND team = team_
		AND grade IN (SELECT nom
			FROM TBL_CONFIG_AFFECTATIONS
			WHERE description NOT LIKE '%Élève%'
			AND type = 'grade')
		ORDER BY beginning DESC
		LIMIT 1;
		-- CALL messageSystem("ancid_ IS NULL", "DEBUG", 'setAnciennete', "short", CONCAT('centre:', centre_, ';team:', team_, ';beginning:', beginning_, ';end:', end_)); 	
		IF beginning_ IS NULL THEN
			SET done = 1;
		ELSE
			INSERT INTO TBL_ANCIENNETE_EQUIPE
			(ancid, uid, centre, team, beginning, end, global)
			VALUES
			(NULL, uid_, centre_, team_, beginning_, end_, FALSE);
			SET done = 0;
			SET ancid_ = LAST_INSERT_ID();
		END IF;
	END IF;
	
	-- Recherche la date de début
	OPEN curAnciennete;

	REPEAT
	FETCH curAnciennete INTO prevCentre, prevTeam, prevBeginning;
	-- CALL messageSystem("msg", "DEBUG", 'curAnciennete', "short", CONCAT('prevCentre:', prevCentre, ';prevTeam:', prevTeam, ';prevBeginning:', prevBeginning, ';done:', done)); 
	-- Si le centre ou l'équipe sont différent de celui que l'on vient de saisir
	IF prevTeam = team_ AND prevCentre = centre_ THEN
		UPDATE TBL_ANCIENNETE_EQUIPE
		SET beginning = prevBeginning
		WHERE ancid = ancid_;
		-- CALL messageSystem("msg", "DEBUG", 'UPDATE', "short", CONCAT('prevCentre:', prevCentre, ';prevTeam:', prevTeam, ';prevBeginning:', prevBeginning)); 
	ELSE
		SET done = 1;
	END IF;
	UNTIL done END REPEAT;

	CLOSE curAnciennete;
END
|
-- Cherche l'ancienneté qualifiée (statut C non comptabilisé) dans la dernière affectation de l'agent
DROP PROCEDURE IF EXISTS setAncienneteLastQualif|
CREATE PROCEDURE setAncienneteLastQualif( IN uid_ SMALLINT(6) )
BEGIN
	DECLARE done BOOLEAN DEFAULT 0;
	DECLARE ancid_ INT(11);
	DECLARE centre_, prevCentre VARCHAR(50);
	DECLARE team_, prevTeam VARCHAR(10);
	DECLARE beginning_, prevBeginning, end_ DATE;
	DECLARE curAnciennete CURSOR FOR
		SELECT centre, team, beginning
		FROM TBL_AFFECTATION
		WHERE uid = uid_
		AND grade IN (SELECT nom
			FROM TBL_CONFIG_AFFECTATIONS
			WHERE description NOT LIKE '%Élève%'
			AND type = 'grade')
		ORDER BY beginning DESC;
	
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	SELECT ancid, centre, team, beginning
	INTO ancid_, centre_, team_, beginning_
	FROM TBL_ANCIENNETE_EQUIPE
	WHERE uid = uid_
	ORDER BY beginning DESC
	LIMIT 1;

	IF ancid_ IS NULL THEN
		SELECT centre, team, beginning, end
		INTO centre_, team_, beginning_, end_
		FROM TBL_AFFECTATION
		WHERE uid = uid_
		AND grade IN (SELECT nom
			FROM TBL_CONFIG_AFFECTATIONS
			WHERE description NOT LIKE '%Élève%'
			AND type = 'grade')
		ORDER BY beginning DESC
		LIMIT 1;
		-- CALL messageSystem("ancid_ IS NULL", "DEBUG", 'setAnciennete', "short", CONCAT('centre:', centre_, ';team:', team_, ';beginning:', beginning_, ';end:', end_)); 	
		IF centre_ IS NULL THEN
			SET done = 1;
		ELSE
			INSERT INTO TBL_ANCIENNETE_EQUIPE
			(ancid, uid, centre, team, beginning, end, global)
			VALUES
			(NULL, uid_, centre_, team_, beginning_, end_, FALSE);
			SET done = 0;
			SET ancid_ = LAST_INSERT_ID();
		END IF;
	END IF;
	
	-- Recherche la date de début
	OPEN curAnciennete;

	REPEAT
	FETCH curAnciennete INTO prevCentre, prevTeam, prevBeginning;
	-- CALL messageSystem("msg", "DEBUG", 'curAnciennete', "short", CONCAT('prevCentre:', prevCentre, ';prevTeam:', prevTeam, ';prevBeginning:', prevBeginning, ';done:', done)); 
	-- Si le centre ou l'équipe sont différent de celui que l'on vient de saisir
	IF prevTeam = team_ AND prevCentre = centre_ THEN
		UPDATE TBL_ANCIENNETE_EQUIPE
		SET beginning = prevBeginning
		WHERE ancid = ancid_;
		-- CALL messageSystem("msg", "DEBUG", 'UPDATE', "short", CONCAT('prevCentre:', prevCentre, ';prevTeam:', prevTeam, ';prevBeginning:', prevBeginning)); 
	ELSE
		SET done = 1;
	END IF;
	UNTIL done END REPEAT;

	CLOSE curAnciennete;
END
|
-- Cherche l'ancienneté globale (qualifié et non qualifié) dans une affectation de l'agent
DROP PROCEDURE IF EXISTS setAncienneteAffectGlobal|
CREATE PROCEDURE setAncienneteAffectGlobal( IN uid_ SMALLINT(6), IN centre_ VARCHAR(50), IN team_ VARCHAR(10) )
BEGIN
	DECLARE done BOOLEAN DEFAULT 0;
	DECLARE ancid_ INT(11);
	DECLARE prevCentre VARCHAR(50);
	DECLARE prevTeam VARCHAR(10);
	DECLARE beginning_, prevBeginning, end_ DATE;
	DECLARE curAnciennete CURSOR FOR
		SELECT centre, team, beginning
		FROM TBL_AFFECTATION
		WHERE uid = uid_
		ORDER BY beginning DESC;
	
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	SELECT ancid, beginning
	INTO ancid_, beginning_
	FROM TBL_ANCIENNETE_EQUIPE
	WHERE uid = uid_
	AND centre = centre_
	AND team = team_
	AND global IS TRUE
	ORDER BY beginning DESC
	LIMIT 1;

	IF ancid_ IS NULL THEN
		SELECT beginning, end
		INTO beginning_, end_
		FROM TBL_AFFECTATION
		WHERE uid = uid_
		AND centre = centre_
		AND team = team_
		ORDER BY beginning DESC
		LIMIT 1;
		-- CALL messageSystem("ancid_ IS NULL", "DEBUG", 'setAnciennete', "short", CONCAT('centre:', centre_, ';team:', team_, ';beginning:', beginning_, ';end:', end_)); 	
		IF beginning_ IS NULL THEN
			SET done = 1;
		ELSE
			INSERT INTO TBL_ANCIENNETE_EQUIPE
			(ancid, uid, centre, team, beginning, end, global)
			VALUES
			(NULL, uid_, centre_, team_, beginning_, end_, TRUE);
			SET done = 0;
			SET ancid_ = LAST_INSERT_ID();
		END IF;
	END IF;
	
	-- Recherche la date de début
	OPEN curAnciennete;

	REPEAT
	FETCH curAnciennete INTO prevCentre, prevTeam, prevBeginning;
	-- CALL messageSystem("msg", "DEBUG", 'curAnciennete', "short", CONCAT('prevCentre:', prevCentre, ';prevTeam:', prevTeam, ';prevBeginning:', prevBeginning, ';done:', done)); 
	-- Si le centre ou l'équipe sont différent de celui que l'on vient de saisir
	IF prevTeam = team_ AND prevCentre = centre_ THEN
		UPDATE TBL_ANCIENNETE_EQUIPE
		SET beginning = prevBeginning
		WHERE ancid = ancid_;
		-- CALL messageSystem("msg", "DEBUG", 'UPDATE', "short", CONCAT('prevCentre:', prevCentre, ';prevTeam:', prevTeam, ';prevBeginning:', prevBeginning)); 
	ELSE
		SET done = 1;
	END IF;
	UNTIL done END REPEAT;

	CLOSE curAnciennete;
END
|
-- Cherche l'ancienneté globale (qualifié et non qualifié) dans la dernière affectation de l'agent
DROP PROCEDURE IF EXISTS setAncienneteLastGlobal|
CREATE PROCEDURE setAncienneteLastGlobal( IN uid_ SMALLINT(6) )
BEGIN
	DECLARE done BOOLEAN DEFAULT 0;
	DECLARE ancid_ INT(11);
	DECLARE centre_, prevCentre VARCHAR(50);
	DECLARE team_, prevTeam VARCHAR(10);
	DECLARE beginning_, prevBeginning, end_ DATE;
	DECLARE curAnciennete CURSOR FOR
		SELECT centre, team, beginning
		FROM TBL_AFFECTATION
		WHERE uid = uid_
		ORDER BY beginning DESC;
	
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	SELECT ancid, centre, team, beginning
	INTO ancid_, centre_, team_, beginning_
	FROM TBL_ANCIENNETE_EQUIPE
	WHERE uid = uid_
	AND global IS TRUE
	ORDER BY beginning DESC
	LIMIT 1;

	IF ancid_ IS NULL THEN
		SELECT centre, team, beginning, end
		INTO centre_, team_, beginning_, end_
		FROM TBL_AFFECTATION
		WHERE uid = uid_
		ORDER BY beginning DESC
		LIMIT 1;
		-- CALL messageSystem("ancid_ IS NULL", "DEBUG", 'setAnciennete', "short", CONCAT('centre:', centre_, ';team:', team_, ';beginning:', beginning_, ';end:', end_)); 	
		IF centre_ IS NULL THEN
			SET done = 1;
		ELSE
			INSERT INTO TBL_ANCIENNETE_EQUIPE
			(ancid, uid, centre, team, beginning, end, global)
			VALUES
			(NULL, uid_, centre_, team_, beginning_, end_, TRUE);
			SET done = 0;
			SET ancid_ = LAST_INSERT_ID();
		END IF;
	END IF;
	
	-- Recherche la date de début
	OPEN curAnciennete;

	REPEAT
	FETCH curAnciennete INTO prevCentre, prevTeam, prevBeginning;
	-- CALL messageSystem("msg", "DEBUG", 'curAnciennete', "short", CONCAT('prevCentre:', prevCentre, ';prevTeam:', prevTeam, ';prevBeginning:', prevBeginning, ';done:', done)); 
	-- Si le centre ou l'équipe sont différent de celui que l'on vient de saisir
	IF prevTeam = team_ AND prevCentre = centre_ THEN
		UPDATE TBL_ANCIENNETE_EQUIPE
		SET beginning = prevBeginning
		WHERE ancid = ancid_;
		-- CALL messageSystem("msg", "DEBUG", 'UPDATE', "short", CONCAT('prevCentre:', prevCentre, ';prevTeam:', prevTeam, ';prevBeginning:', prevBeginning)); 
	ELSE
		SET done = 1;
	END IF;
	UNTIL done END REPEAT;

	CLOSE curAnciennete;
END
|
-- Affecte toutes les anciennetés de l'utilisateur
DROP PROCEDURE IF EXISTS setAncienneteUser|
CREATE PROCEDURE setAncienneteUser( IN uid_ SMALLINT(6), IN centre_ VARCHAR(50), IN team_ VARCHAR(10) )
BEGIN
	CALL setAncienneteAffectQualif(uid_, centre_, team_);
	CALL setAncienneteAffectGlobal(uid_, centre_, team_);
	CALL setAncienneteLastQualif(uid_);
	CALL setAncienneteLastGlobal(uid_);
END
|
DROP PROCEDURE IF EXISTS setAnciennete|
CREATE PROCEDURE setAnciennete( IN uid_ SMALLINT(6) )
BEGIN
	CALL setAncienneteLastQualif(uid_);
	CALL setAncienneteLastGlobal(uid_);
END
|
DROP PROCEDURE IF EXISTS setAncienneteAffect|
CREATE PROCEDURE setAncienneteAffect( IN uid_ SMALLINT(6), IN centre_ VARCHAR(50), IN team_ VARCHAR(10) )
BEGIN
	CALL setAncienneteAffectQualif(uid_, centre_, team_);
	CALL setAncienneteAffectGlobal(uid_, centre_, team_);
END
|
-- Retrouve l'ancienneté de l'affectation courante pour les utilisateurs actifs
DROP PROCEDURE IF EXISTS ____attribAnciennete|
CREATE PROCEDURE ____attribAnciennete()
BEGIN
	DECLARE uid_ SMALLINT(6);
	DECLARE centre_ VARCHAR(50);
	DECLARE team_ VARCHAR(10);
	DECLARE done BOOLEAN DEFAULT FALSE;

	DECLARE curUids CURSOR FOR
		SELECT uid
		FROM TBL_USERS
		WHERE actif IS TRUE;
	
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	OPEN curUids;

	REPEAT
	FETCH curUids INTO uid_;
	-- Recherche l'ancienneté dans l'affectation actuelle
	CALL setAnciennete(uid_);
	-- Rechercher l'ancienneté dans les affectations anciennes si il y a
	SELECT centre, team
	INTO centre_, team_
	FROM TBL_ANCIENNETE_EQUIPE
	WHERE uid = uid_
	ORDER BY beginning DESC
	LIMIT 1;
	CALL ____attribOldAnciennete(uid_, centre_, team_);
	UNTIL done END REPEAT;

	CLOSE curUids;
END
|
-- Retrouve les anciennetés des affectations qui ne sont pas notCentre_ et notTeam_ pour l'utilisateur uid_
-- Ceci permet de calculer les anciennetés précédentes à l'ancienneté courante
DROP PROCEDURE IF EXISTS ____attribOldAnciennete|
CREATE PROCEDURE ____attribOldAnciennete( IN uid_ int(11), notCentre_ VARCHAR(50), notTeam_ VARCHAR(10) )
BEGIN
	DECLARE centre_ VARCHAR(50);
	DECLARE team_ VARCHAR(10);
	DECLARE done BOOLEAN DEFAULT FALSE;

	DECLARE curAffect CURSOR FOR
		SELECT centre, team
		FROM TBL_AFFECTATION
		WHERE uid = uid_
		AND (centre != notCentre_ OR team != notTeam_)
		;

	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	OPEN curAffect;

	REPEAT
	FETCH curAffect INTO centre_, team_;
	CALL setAncienneteAffect(uid_, centre_, team_);
	UNTIL done END REPEAT;

	CLOSE curAffect;
END
|

DELIMITER ;

DROP VIEW IF EXISTS affectations;
CREATE VIEW affectations AS
	SELECT nom, centre, team, grade, beginning, end
	FROM TBL_AFFECTATION a
	, TBL_USERS u
	WHERE a.uid = u.uid
	ORDER BY actif DESC,nom ASC, beginning ASC;

-- AJOUT DES VALEURS DANS LA BASE
-- INSERT INTO TBL_CONFIG_AFFECTATIONS
-- 	(caid, type, nom, description)
-- 	VALUES
-- 	(NULL, 'centre', 'athis', 'CRNA Nord - Athis-Mons'),
-- 	(NULL, 'centre', 'Aix', 'CRNA Sud-Est - Aix-en-Provence'),
-- 	(NULL, 'centre', 'Reims', 'CRNA Est - Reims'),
-- 	(NULL, 'centre', 'Bordeaux', 'CRNA Sud-Ouest - Bordeaux'),
-- 	(NULL, 'centre', 'Brest', 'CRNA Ouest - Brest'),
-- 	(NULL, 'grade', 'C', 'Élève'),
-- 	(NULL, 'grade', 'Théorique', 'Élève ayant obtenu son théorique'),
-- 	(NULL, 'grade', 'PC', 'Premier contrôleur'),
-- 	(NULL, 'grade', 'FMP', 'FMPiste'),
-- 	(NULL, 'grade', 'Détaché', 'contrôleur détaché'),
-- 	(NULL, 'grade', 'CE', "Chef d'équipe"),
-- 	(NULL, 'grade', 'CDS', 'Chef de salle'),
-- 	(NULL, 'team', '1e', 'Équipe 1 Est'),
-- 	(NULL, 'team', '2e', 'Équipe 2 Est'),
-- 	(NULL, 'team', '3e', 'Équipe 3 Est'),
-- 	(NULL, 'team', '4e', 'Équipe 4 Est'),
-- 	(NULL, 'team', '5e', 'Équipe 5 Est'),
-- 	(NULL, 'team', '6e', 'Équipe 6 Est'),
-- 	(NULL, 'team', '7e', 'Équipe 7 Est'),
-- 	(NULL, 'team', '8e', 'Équipe 8 Est'),
-- 	(NULL, 'team', '9e', 'Équipe 9 Est'),
-- 	(NULL, 'team', '10e', 'Équipe 10 Est'),
-- 	(NULL, 'team', '11e', 'Équipe 11 Est'),
-- 	(NULL, 'team', '12e', 'Équipe 12 Est'),
-- 	(NULL, 'team', '1w', 'Équipe 1 Ouest'),
-- 	(NULL, 'team', '2w', 'Équipe 2 Ouest'),
-- 	(NULL, 'team', '3w', 'Équipe 3 Ouest'),
-- 	(NULL, 'team', '4w', 'Équipe 4 Ouest'),
-- 	(NULL, 'team', '5w', 'Équipe 5 Ouest'),
-- 	(NULL, 'team', '6w', 'Équipe 6 Ouest'),
-- 	(NULL, 'team', '7w', 'Équipe 7 Ouest'),
-- 	(NULL, 'team', '8w', 'Équipe 8 Ouest'),
-- 	(NULL, 'team', '9w', 'Équipe 9 Ouest'),
-- 	(NULL, 'team', '10w', 'Équipe 10 Ouest'),
-- 	(NULL, 'team', '11w', 'Équipe 11 Ouest'),
-- 	(NULL, 'team', '12w', 'Équipe 12 Ouest');

CALL ____attribAnciennete();
