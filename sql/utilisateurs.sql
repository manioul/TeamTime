DELIMITER |
DROP PROCEDURE IF EXISTS __createUtilisateurDb|
CREATE PROCEDURE __createUtilisateurDb ( IN userid INT(11) , IN passwd VARCHAR(64) )
BEGIN
	INSERT INTO mysql.user
		(Host, User, Password)
		VALUES
		('localhost', CONCAT('ttm.', userid), PASSWORD(passwd));
	INSERT INTO mysql.db
		(Host, Db, User, Select_priv, Insert_priv, Update_priv, Delete_priv, Execute_priv, Create_tmp_table_priv)
		VALUES
		('localhost', 'ttm', CONCAT('ttm.', userid), 'Y', 'Y', 'Y', 'Y', 'Y', 'Y'); 
	FLUSH PRIVILEGES;
END
|
DROP PROCEDURE IF EXISTS __deleteUtilisateurDb|
CREATE PROCEDURE __deleteUtilisateurDb (IN userid INT(11))
BEGIN
	DELETE FROM mysql.db WHERE User = CONCAT('ttm.', userid);
	DELETE FROM mysql.user WHERE User = CONCAT('ttm.', userid);
	FLUSH PRIVILEGES;
END
|
DROP PROCEDURE IF EXISTS deleteUser|
CREATE PROCEDURE deleteUser( IN userid INT(11) )
BEGIN
	CALL __deleteUtilisateurDb( userid );
	UPDATE TBL_USERS
	SET active = FALSE
	WHERE uid = userid;
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

DELIMITER ;

DROP VIEW IF EXISTS affectations;
CREATE VIEW affectations AS
	SELECT nom, centre, team, grade, beginning, end
	FROM TBL_AFFECTATION a
	, TBL_USERS u
	WHERE a.uid = u.uid
	ORDER BY actif DESC,nom ASC, beginning ASC;
