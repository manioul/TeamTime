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
