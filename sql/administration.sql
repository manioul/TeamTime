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

-- Supprime les activités posées en double sur une même journée pour un
-- même utilisateur et qui ne correspondent pas une pereq.
DROP PROCEDURE IF EXISTS cleanMultipleActivites|
CREATE PROCEDURE cleanMultipleActivites()
BEGIN
	DECLARE uid_, compteur, did_ SMALLINT(6);
	DECLARE date_ DATE;
	DECLARE pereq_ BOOLEAN;
	DECLARE done BOOLEAN DEFAULT 0;

	DECLARE curMulAct CURSOR FOR
		SELECT date, uid, COUNT(uid), pereq, did
		FROM TBL_L_SHIFT_DISPO
		GROUP BY date, uid, did
		HAVING compteur > 1
		AND date != 0
		AND pereq IS FALSE
		ORDER BY uid;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;
	
	OPEN curMulAct;

	REPEAT
	FETCH curMulAct INTO date_, uid_, compteur, pereq_, did_;
		CALL cleanMultipleActivitesUidDate(uid_, date_, did_);
	UNTIL done END REPEAT;

	CLOSE curMulAct;
END;
-- Supprime les activités en double pour l'utilisateur dont l'uid est passé à la date date_
DROP PROCEDURE IF EXISTS cleanMultipleActivitesUidDate|
CREATE PROCEDURE cleanMultipleActivitesUidDate( IN uid_ SMALLINT(6) , IN date_ DATE , IN did_ SMALLINT(6) )
BEGIN
	DECLARE currentSdid, savedSdid BIGINT(20);
	DECLARE done BOOLEAN;
	DECLARE curSdid CURSOR FOR
		SELECT sdid
		FROM TBL_L_SHIFT_DISPO
		WHERE date = date_
		AND uid = uid_
		AND did = did_
		AND pereq IS FALSE;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;
	OPEN curSdid;
	REPEAT
	FETCH curSdid INTO ;
	UNTIL done END REPEAT;
	CLOSE curSdid;
END;
DELIMITER ;
