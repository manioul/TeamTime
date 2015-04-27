DELIMITER |
-- Réordonne les poids dans TBL_DISPO à partir du poids from => un poids de start
DROP PROCEDURE IF EXISTS reorderDispo|
CREATE PROCEDURE reorderDispo( IN from_ INT(11) , IN start_ INT(11) , IN centre_ VARCHAR(50) , IN team_ VARCHAR(10) )
BEGIN
	DECLARE did_ INT(11);
	DECLARE done BOOLEAN DEFAULT 0;
	DECLARE curDispo CURSOR FOR
		SELECT did FROM TBL_DISPO
		WHERE centre = centre_
		AND team = team_
		AND poids >= from_
		AND (`type decompte` != 'conges' OR `type decompte` IS NULL)
		ORDER BY actif DESC, poids;
	
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;
	
	OPEN curDispo;

	REPEAT
	FETCH curDispo INTO did_;
	UPDATE TBL_DISPO
		SET poids = start_
		WHERE did = did_;
	SET start_ = start_ + 1;
	UNTIL done END REPEAT;

	CLOSE curDispo;
END
|

-- Supprime les activités multiples posées sur une même journée pour un
-- même utilisateur et qui ne correspondent pas une pereq.
DROP PROCEDURE IF EXISTS cleanMultipleActivites|
CREATE PROCEDURE cleanMultipleActivites()
BEGIN
	DECLARE uid_, compt_, did_ SMALLINT(6);
	DECLARE date_ DATE;
	DECLARE done BOOLEAN DEFAULT 0;

	-- Recherche les activités multiples ayant le même did
	-- Certaines entrées peuvent avoir un titre (title) défini, d'autres non,
	-- ce qui aide dans le choix de l'entrée à supprimer...
	DECLARE curDblAct CURSOR FOR
		SELECT date, uid, COUNT(uid) AS compteur, did
		FROM TBL_L_SHIFT_DISPO
		WHERE pereq IS FALSE
		AND date != 0
		GROUP BY date, uid, did
		HAVING compteur > 1
		ORDER BY uid;
	
	-- Recherche les activités multiples ayant des did différents
	-- L'activité ayant le sdid le plus élevé est l'entrée la plus récente
	-- et sera, à ce titre, considérée comme valide
	DECLARE curMultAct CURSOR FOR
		SELECT date, uid, COUNT(uid) AS compteur
		FROM TBL_L_SHIFT_DISPO
		WHERE pereq IS FALSE
		AND date != 0
		GROUP BY date, uid
		HAVING compteur > 1
		ORDER BY uid;
	
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;
	
	OPEN curDblAct;

	lDoubleActivites : LOOP
		FETCH curDblAct INTO date_, uid_, compt_, did_;

		CALL cleanDoubleActivitesUidDate(uid_, date_, did_);

		IF done THEN
			CLOSE curDblAct;
			LEAVE lDoubleActivites;
		END IF;
	END LOOP;

	SET done = 0;
	SET compt_ = 0;

	OPEN curMultAct;

	lMultipleActivites : LOOP
		FETCH curMultAct INTO date_, uid_, compt_;

		CALL cleanMultipleActivitesUidDate(uid_, date_);

		IF done THEN
			CLOSE curMultAct;
			LEAVE lMultipleActivites;
		END IF;

	END LOOP;
END
|
-- Supprime les activités en double pour l'utilisateur dont l'uid est passé à la date date_
DROP PROCEDURE IF EXISTS cleanDoubleActivitesUidDate|
CREATE PROCEDURE cleanDoubleActivitesUidDate( IN uid_ SMALLINT(6) , IN date_ DATE , IN did_ SMALLINT(6) )
BEGIN
	DECLARE withTitle, compteur BIGINT(20);

	CREATE TABLE IF NOT EXISTS sauvegardeActivitesMultiples (
		 `sdid` bigint( 20  ) NOT NULL AUTO_INCREMENT COMMENT 'identifiant unique de l''occupation',
		`date` date NOT NULL ,
		`uid` smallint( 6  ) NOT NULL ,
		`did` smallint( 6  ) NOT NULL ,
		`pereq` tinyint( 1  ) NOT NULL COMMENT 'Ceci est une péréquation et ne correspond pas à un évènement réel',
		`priorite` tinyint( 4  ) DEFAULT NULL COMMENT 'Définit un ordre dans le cas de dispo multiples',
		`title` text COMMENT 'Le contenu du champ title (affiché au survol)',
		`newDid` smallint( 6  ) DEFAULT NULL,
		PRIMARY KEY ( `sdid`  ) ,
		KEY `date` ( `date`  ) ,
		KEY `uid` ( `uid`  ) ,
		KEY `did` ( `did`  )
	) ENGINE = InnoDB DEFAULT CHARSET = utf8;

	-- Recherche si une activité contient un title (le cas échéant, c'est probablement l'entrée à conserver)
	SELECT COUNT(date) - 1
	INTO withTitle
	FROM TBL_L_SHIFT_DISPO
	WHERE date = date_
	AND uid = uid_
	AND did = did_
	AND pereq IS FALSE
	AND title IS NOT NULL;

	IF withTitle >= 0 THEN
		-- Suppression des activités n'ayant pas de title défini
		-- Sauvegarde avant suppression
		INSERT INTO sauvegardeActivitesMultiples
		(SELECT sdid, date, uid, did, pereq, priorite, title, NULL
			FROM TBL_L_SHIFT_DISPO
			WHERE uid = uid_
			AND date = date_
			AND did = did_
			AND pereq IS FALSE
			AND title IS NULL);
		DELETE FROM TBL_L_SHIFT_DISPO
		WHERE uid = uid_
		AND date = date_
		AND did = did_
		AND pereq IS FALSE
		AND title IS NULL;

		IF withTitle >= 1 THEN
			INSERT INTO sauvegardeActivitesMultiples
			(SELECT sdid, date, uid, did, pereq, priorite, title, NULL
				FROM TBL_L_SHIFT_DISPO
				WHERE uid = uid_
				AND date = date_
				AND did = did_
				AND pereq IS FALSE
				LIMIT withTitle);
			DELETE FROM TBL_L_SHIFT_DISPO
			WHERE uid = uid_
			AND date = date_
			AND did = did_
			AND pereq IS FALSE
			LIMIT withTitle;
		END IF;
	END IF;

	SELECT COUNT(uid) - 1
	INTO compteur
	FROM TBL_L_SHIFT_DISPO
	WHERE date = date_
	AND uid = uid_
	AND did = did_
	AND pereq IS FALSE
	ORDER BY uid;

	IF compteur >= 1 THEN
		INSERT INTO sauvegardeActivitesMultiples
		(SELECT sdid, date, uid, did, pereq, priorite, title, NULL
			FROM TBL_L_SHIFT_DISPO
			WHERE date = date_
			AND uid = uid_
			AND did = did_
			AND pereq IS FALSE
			LIMIT compteur);
			
		DELETE FROM TBL_L_SHIFT_DISPO
		WHERE date = date_
		AND uid = uid_
		AND did = did_
		AND pereq IS FALSE
		LIMIT compteur;
	END IF;
END
|
-- Supprile les activités multiples pour l'utilisateur dont l'uid est passé à la date date_
DROP PROCEDURE IF EXISTS cleanMultipleActivitesUidDate|
CREATE PROCEDURE cleanMultipleActivitesUidDate( IN uid_ SMALLINT(6) , IN date_ DATE )
BEGIN
	DECLARE compt_ BIGINT(20);

	SELECT COUNT(uid) - 1
	INTO compt_
	FROM TBL_L_SHIFT_DISPO
	WHERE date = date_
	AND uid = uid_
	AND pereq IS FALSE
	ORDER BY uid;

	IF compt_ >= 1 THEN
		INSERT INTO sauvegardeActivitesMultiples
		(SELECT sdid, date, uid, did, pereq, priorite, title, NULL
			FROM TBL_L_SHIFT_DISPO
			WHERE date = date_
			AND uid = uid_
			AND pereq IS FALSE
			ORDER BY sdid
			LIMIT compt_);
			
		DELETE FROM TBL_L_SHIFT_DISPO
		WHERE date = date_
		AND uid = uid_
		AND pereq IS FALSE
		ORDER BY sdid
		LIMIT compt_;
	END IF;

	UPDATE sauvegardeActivitesMultiples
	SET newDid = (SELECT did
		FROM TBL_L_SHIFT_DISPO
		WHERE date = date_
		AND uid = uid_)
	WHERE date = date_
	AND uid = uid_;
END
|
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;
	OPEN curSdid;
	REPEAT
	FETCH curSdid INTO ;
	UNTIL done END REPEAT;
|
-- Supprime les activités saisies dans TBL_L_SHIFT_DISPO et qui n'existent plus
DROP PROCEDURE IF EXISTS deleteInexistantActivities|
CREATE PROCEDURE deleteInexistantActivities()
BEGIN
	DELETE FROM TBL_L_SHIFT_DISPO WHERE did NOT IN (SELECT did FROM TBL_DISPO);
END
|
-- Supprime les activités saisies dans TBL_L_SHIFT_DISPO pour des utilisateurs qui n'existent pas
DROP PROCEDURE IF EXISTS deleteInexistantUserActivities|
CREATE PROCEDURE deleteInexistantUserActivities()
BEGIN
	DELETE FROM TBL_L_SHIFT_DISPO WHERE uid NOT IN (SELECT uid FROM TBL_DISPO);
END
|
-- Supprime les uid inexistant de TBL_AFFECTATION
DROP PROCEDURE IF EXISTS deleteInexistantAffectations|
CREATE PROCEDURE deleteInexistantAffectations()
BEGIN
	DELETE FROM TBL_AFFECTATION WHERE uid NOT IN (SELECT uid FROM TBL_USERS);
END
|
-- Supprime les uid inexistant de TBL_HEURES
DROP PROCEDURE IF EXISTS deleteInexistantHeures|
CREATE PROCEDURE deleteInexistantHeures()
BEGIN
	DELETE FROM TBL_HEURES WHERE uid NOT IN (SELECT uid FROM TBL_USERS);
END
|
-- Maintenance quotidienne
DROP EVENT IF EXISTS dailyMaintenance|
CREATE EVENT dailyMaintenance
ON SCHEDULE
EVERY 1 DAY
COMMENT 'Maintenance quotidienne'
DO
	BEGIN
		CALL deleteInexistantActivities();
		CALL deleteInexistantUserActivities();
		CALL deleteInexistantAffectations();
		CALL deleteInexistantHeures();
	END
	|
-- Maintenance hebdomadaire
DROP EVENT IF EXISTS weeklyMaintenance|
CREATE EVENT weeklyMaintenance
ON SCHEDULE
EVERY 1 WEEK
COMMENT 'Maintenance hebdomadaire'
DO
	BEGIN
	END
	|
DELIMITER ;
