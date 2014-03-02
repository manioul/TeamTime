-- Création des tables si elles n'existent pas

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
	  `dispatched` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Positionné lorsque les heures ont été calculées',
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
	  `heures` decimal(4,2) NOT NULL COMMENT 'Nombre d''heures allouées',
	  `ordre` INT NOT NULL COMMENT 'définit la précédence des règles',
	  PRIMARY KEY (`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `TBL_DISPATCH_HEURES` ADD `ordre` INT NOT NULL COMMENT 'définit la précédence des règles';

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
	DECLARE userid, dispoid, ruleid, eleves, instructeursId, unattr, didFixed INT;
	DECLARE valeurFixed, heuresLeft, inst, heuresEach FLOAT;
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
	-- Au cas où plusieurs règles sont susceptibles de s'appliquer, celle dont ordre est le plus élevé est retenue (traitée en dernier)
	DECLARE curFixed CURSOR FOR SELECT rid, grades, type, heures, dids
		FROM TBL_DISPATCH_HEURES
		WHERE statut = 'fixed'
		AND FIND_IN_SET((SELECT cid
				FROM TBL_GRILLE
				WHERE date = dat
				AND centre = centr
				AND team = tea)
			, cids)
		ORDER BY ordre ASC;
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
		-- CALL messageSystem("Ajout des heures fixes", "DEBUG", 'dispatchOneDayHeures', NULL, CONCAT("ruleid:",ruleid,";grade:",gradeFixed,";heures:",valeurFixed,";did:",didFixed,";"));
		IF typeFixed = 'norm' THEN
			UPDATE tmpPresents
			SET normales = valeurFixed
			, statut = 'fixed'
			, rid = ruleid
			WHERE (FIND_IN_SET(grade, gradeFixed) OR grade = gradeFixed)
			AND (FIND_IN_SET(did, didFixed) OR did = didFixed);
			-- Pour les attribution fixes à des grades particuliers sans dispo particulières
			UPDATE tmpPresents
			SET normales = valeurFixed
			, statut = 'fixed'
			, rid = ruleid
			WHERE (FIND_IN_SET(grade, gradeFixed) OR grade = gradeFixed)
			AND didFixed IS NULL
			AND did IS NULL;
		ELSEIF typeFixed = 'instru' THEN
			UPDATE tmpPresents
			SET instruction = valeurFixed
			, statut = 'fixed'
			, rid = ruleid
			WHERE (FIND_IN_SET(grade, gradeFixed) OR grade = gradeFixed)
			AND (FIND_IN_SET(did, didFixed) OR did = didFixed);
			-- Pour les attribution fixes à des grades particuliers sans dispo particulières
			UPDATE tmpPresents
			SET normales = valeurFixed
			, statut = 'fixed'
			, rid = ruleid
			WHERE (FIND_IN_SET(grade, gradeFixed) OR grade = gradeFixed)
			AND didFixed IS NULL
			AND did IS NULL;
		ELSEIF typeFixed = 'simu' THEN
			UPDATE tmpPresents
			SET simulateur = valeurFixed
			, statut = 'fixed'
			, rid = ruleid
			WHERE (FIND_IN_SET(grade, gradeFixed) OR grade = gradeFixed)
			AND (FIND_IN_SET(did, didFixed) OR did = didFixed);
			-- Pour les attribution fixes à des grades particuliers sans dispo particulières
			UPDATE tmpPresents
			SET normales = valeurFixed
			, statut = 'instru'
			, rid = ruleid
			WHERE (FIND_IN_SET(grade, gradeFixed) OR grade = gradeFixed)
			AND didFixed IS NULL
			AND did IS NULL;
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
		(uid, date, normales, instruction, simulateur, statut)
		VALUES
		(userid, dateH, normal, instruc, simul, 'unattr');
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
