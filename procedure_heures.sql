DELIMITER |
DROP PROCEDURE IF EXISTS dispatchHeures|
CREATE PROCEDURE dispatchHeures ( IN dat DATE , IN centr CHAR(50) , IN tea CHAR(10), IN heur FLOAT )
BEGIN
	DECLARE done BOOLEAN DEFAULT 0;
	DECLARE gradeFixed CHAR(64);
	DECLARE typeFixed CHAR(64);
	DECLARE valeurFixed, didFixed, heuresLeft INT;
	DECLARE heuresEach FLOAT;
	DECLARE curFixed CURSOR FOR SELECT grade, type, heures, did
		FROM TBL_DISPATCH_HEURES
		WHERE statut = 'fixed'
		AND cid = (SELECT cid FROM TBL_GRILLE WHERE date = dat);

	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	-- Création des tables si elles n'existent pas

	CREATE TABLE IF NOT EXISTS TBL_HEURES_A_PARTAGER (
		  centre varchar(50) NOT NULL,
		  team varchar(10) NOT NULL,
		  date date NOT NULL,
		  heures decimal(4,2) NOT NULL,
		  PRIMARY KEY (centre, team, date)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Le nombre d''heures à partager par jour';
	CREATE TABLE IF NOT EXISTS TBL_HEURES (
		 uid int(11) NOT NULL,
		 nom varchar(64) NOT NULL,
		 grade varchar(64) NOT NULL,
		 did int(11) NOT NULL,
		 date date NOT NULL,
		 heures decimal(4,2) NOT NULL,
		 type enum('norm','instru','simu'),
		 PRIMARY KEY (uid, date)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


	SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
	REPLACE INTO TBL_HEURES_A_PARTAGER
		(centre, team, date, heures)
		VALUES
		(centr, tea, dat, heur);
	REPLACE INTO TBL_HEURES
	(SELECT u.uid, nom, grade, did, dat, 0, 'norm'
		FROM TBL_USERS u
		, TBL_L_SHIFT_DISPO l
		, TBL_AFFECTATION a
	       	WHERE date = dat
	       	AND l.uid = u.uid
	       	AND u.uid = a.uid
		AND centre = centr
		AND team = tea
		AND beginning <= dat
		AND end >= dat
		AND grade != 'c'
		AND grade != 'theo'
		AND did NOT IN (SELECT did
			FROM TBL_DISPO
		       	WHERE (absence IS TRUE OR dispo = 'fmp' OR dispo = 'cds')
		AND actif IS TRUE));
	REPLACE INTO TBL_HEURES
	(SELECT u.uid, nom, grade, NULL, dat, 0, 'norm'
	       	FROM TBL_USERS u
		, TBL_AFFECTATION a
		WHERE u.uid = a.uid
	       	AND centre = centr
	       	AND team = tea
	       	AND grade != 'c'
	       	AND grade != 'theo'
	       	AND beginning <= dat
	       	AND end >= dat
	       	AND a.uid NOT IN (SELECT uid
		       	FROM TBL_L_SHIFT_DISPO
		       	WHERE date = dat)
	       	AND actif IS TRUE);

	OPEN curFixed;

	-- Ajout des heures fixes
	REPEAT
	FETCH curFixed INTO gradeFixed, typeFixed, valeurFixed, didFixed;
	IF NOT done THEN
		UPDATE TBL_HEURES
		SET heures = valeurFixed
		, type = typeFixed
		WHERE date = dat
		AND grade = gradeFixed
		AND did = didFixed;
	END IF;
	UNTIL done END REPEAT;

	CLOSE curFixed;

	-- Calcul les heures restantes
	SELECT (p.heures) - SUM(t.heures)
	INTO heuresLeft
	FROM TBL_HEURES_A_PARTAGER p
	, TBL_HEURES t
	WHERE p.date = t.date
	AND p.date = dat;

	SELECT heuresLeft / (SELECT COUNT(uid) FROM TBL_HEURES
		WHERE heures = 0) INTO heuresEach;

	UPDATE TBL_HEURES SET heures = (SELECT ROUND(heuresEach * 4) / 4) WHERE heures = 0;
END
|
DROP PROCEDURE IF EXISTS addAffectation|
CREATE PROCEDURE addAffectation( IN userid INT , IN centr CHAR(50) , IN tea CHAR(10) , IN gra CHAR(64) , IN debut DATE , IN fin DATE )
BEGIN
	DECLARE notFound, prevFound, nextFound BOOLEAN DEFAULT 0;
	DECLARE prevAffectId, nextAffectId INT;
	DECLARE prevBeginning, prevEnd DATE;
	DECLARE prevGrade CHAR(64);
	DECLARE prevCentre CHAR(64);
	DECLARE prevTeam CHAR(64);
	DECLARE nextBeginning, nextEnd DATE;
	DECLARE nextGrade CHAR(64);
	DECLARE nextCentre CHAR(64);
	DECLARE nextTeam CHAR(64);

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
			(aid, uid, centre, team, grade, beginning, end)
			VALUES
			(NULL, userid, centr, tea, gra, debut, fin);
		ELSE
			-- Si la nouvelle affectation est identique à la précédente, on prolonge la précédente au besoin
			IF centr = prevCentre AND tea = prevTeam AND gra = prevGrade AND prevEnd < fin THEN
				UPDATE TBL_AFFECTATION
				SET end = fin
				WHERE aid = prevAffectId;
			ELSE
				-- Si la nouvelle affectation est identique à la précédente, on étend la précédente au besoin
				IF centr = nextCentre AND tea = nextTeam AND gra = nextGrade AND nextBeginning > debut THEN
					UPDATE TBL_AFFECTATION
					SET beginning = debut
					WHERE aid = prevAffectId;
				ELSE
					-- Ajoute la nouvelle affectation
					INSERT INTO TBL_AFFECTATION
					(aid, uid, centre, team, grade, beginning, end)
					VALUES
					(NULL, userid, centr, tea, gra, debut, fin);

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
							(aid, uid, centre, team, grade, beginning, end)
							VALUES
							(NULL, userid, centr, tea, prevGrade, DATE_ADD(fin, INTERVAL 1 DAY), prevEnd);
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
							(aid, uid, centre, team, grade, beginning, end)
							VALUES
							(NULL, userid, centr, tea, nextGrade, nextBeginning, DATE_SUB(fin, INTERVAL 1 DAY));
						END IF;
					END IF;
				END IF;
			END IF;
		END IF;
	END IF;
END
|
DELIMITER ;

DROP TABLE IF EXISTS TBL_CHECK_AFFECTATIONS;

CREATE TABLE TBL_CHECK_AFFECTATIONS (
	aid int(11)
);

CALL addAffectation( 99999, 'somewhere', 'someteam', 'pc', '0000-00-00', '2025-12-31');
SELECT * FROM TBL_AFFECTATION WHERE uid = 99999 ORDER BY beginning;
CALL addAffectation( 99999, 'somewhere', 'someteam', 'pc', '2001-01-01', '2030-12-31');
SELECT * FROM TBL_AFFECTATION WHERE uid = 99999 ORDER BY beginning;
CALL addAffectation( 99999, 'somewhere', 'someteam', 'c', '2000-01-01', '2002-12-31');
SELECT * FROM TBL_AFFECTATION WHERE uid = 99999 ORDER BY beginning;
CALL addAffectation( 99999, 'somewhere', 'someteam', 'ce', '2010-01-01', '2015-12-31');
SELECT * FROM TBL_AFFECTATION WHERE uid = 99999 ORDER BY beginning;
CALL addAffectation( 99999, 'somewhere', 'someteam', 'cds', '2013-01-01', '2019-12-31');
SELECT * FROM TBL_AFFECTATION WHERE uid = 99999 ORDER BY beginning;
-- CALL addAffectation( 99999, 'somewhere', 'someteam', 'fmp', '2008-01-01', '2008-12-31');
-- SELECT * FROM TBL_AFFECTATION WHERE uid = 99999 ORDER BY beginning;
