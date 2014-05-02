ALTER TABLE `TBL_USERS` CHANGE `nom` `nom` VARCHAR( 64  ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `prenom` `prenom` VARCHAR( 64  ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `login` `login` VARCHAR( 15  ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `email` `email` VARCHAR( 128  ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `sha1` `sha1` VARCHAR( 40  ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `page` `page` VARCHAR( 255  ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'affiche_grille.php' COMMENT 'La page affichée après la connexion d''un utilisateur';

ALTER TABLE `TBL_ADRESSES` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci,
CHANGE `adresse` `adresse` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `cp` `cp` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `ville` `ville` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `TBL_AFFECTATION` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `TBL_BRIEFING` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci,
CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `TBL_HEURES` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `TBL_HEURES_A_PARTAGER` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci,
CHANGE `centre` `centre`  VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `team` `team`  VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `TBL_PERIODE_CHARGE` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci,
CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `TBL_REMPLA` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci,
CHANGE `nom` `nom`  VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `phone` `phone`  VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `email` `email`  VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `TBL_USERS` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `TBL_VACANCES_SCOLAIRES` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci,
CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
-- UPDATE TBL_DISPO SET poids = poids + 50 WHERE centre != 'all' AND team != 'all';
ALTER TABLE `TBL_DISPO` CHANGE `type decompte` `type decompte` VARCHAR( 64  ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;

CREATE VIEW VIEW_LIST_DISPO AS select `l`.`sdid` AS `sdid`,`l`.`uid` AS `uid`,`u`.`nom` AS `nom`,`d`.`dispo` AS `dispo`,`l`.`date` AS `date`,`c`.`vacation` AS `vacation`,year(`l`.`date`) AS `year`,`l`.`pereq` AS `pereq` from ((((`TBL_L_SHIFT_DISPO` `l` join `TBL_USERS` `u`) join `TBL_DISPO` `d`) join `TBL_GRILLE` `g`) join `TBL_CYCLE` `c`) where ((`l`.`date` = `g`.`date`) and (`g`.`cid` = `c`.`cid`) and (`u`.`uid` = `l`.`uid`) and (`d`.`did` = `l`.`did`)) union select `l`.`sdid` AS `sdid`,`l`.`uid` AS `uid`,`u`.`nom` AS `nom`,`d`.`dispo` AS `dispo`,`l`.`date` AS `date`,`l`.`date` AS `date`,`v`.`year` AS `year`,`l`.`pereq` AS `pereq` from (((`TBL_L_SHIFT_DISPO` `l` join `TBL_USERS` `u`) join `TBL_DISPO` `d`) join `TBL_VACANCES` `v`) where ((`l`.`date` = 0) and (`l`.`sdid` = `v`.`sdid`) and (`u`.`uid` = `l`.`uid`) and (`d`.`did` = `l`.`did`)) order by `date`,`nom`;

-- Modification des menus
--
-- Ajout d'un menu Gestion d'équipe
DROP PROCEDURE IF EXISTS __up;

DELIMITER |

CREATE PROCEDURE __up()
BEGIN
	DECLARE idxGE INT(11) DEFAULT NULL;
	DECLARE idxAdministration INT(11);

	SELECT idx
		INTO idxAdministration
		FROM TBL_MENUS
		WHERE titre = 'Administration'
		LIMIT 1;
	SELECT idx
		INTO idxGE
		FROM TBL_MENUS
		WHERE titre = 'Gestion Équipe'
		LIMIT 1;

	IF idxGE IS NULL THEN
		INSERT INTO TBL_MENUS
			(titre, description, parent, creation, allowed, actif)
			VALUES
			('Gestion Équipe', 'Gestion de l''équipe, des activités...', idxAdministration, NOW(), 'editeurs', TRUE);
	END IF;

	SET idxGE = NULL;
	SELECT idx
		INTO idxGE
		FROM TBL_ELEMS_MENUS
		WHERE titre = 'Gestion Équipe'
		LIMIT 1;

	IF idxGE IS NULL THEN
		INSERT INTO TBL_ELEMS_MENUS
			(titre, description, lien, sousmenu, creation, allowed, actif)
			VALUES
			('Gestion Équipe', 'Gestion de l''équipe, des activités...', '', (SELECT idx FROM TBL_MENUS WHERE titre = 'Gestion Équipe' LIMIT 1), NOW(), 'editeurs', TRUE);
	END IF;

	SET idxGE = NULL;
	SELECT idx
		INTO idxGE
		FROM TBL_ELEMS_MENUS
		WHERE titre = 'Ajout d''activité'
		LIMIT 1;

	IF idxGE IS NULL THEN
		INSERT INTO TBL_ELEMS_MENUS
			(titre, description, lien, sousmenu, creation, allowed, actif)
			VALUES
			('Ajout d''activité', 'Ajoute des activités pour l''équipe', 'activites.php', NULL, NOW(), 'editeurs', TRUE);
	END IF;

	REPLACE INTO TBL_MENUS_ELEMS_MENUS
		(idxm, idxem, position)
		VALUES
		((SELECT idx FROM TBL_MENUS WHERE titre = 'Gestion Équipe' LIMIT 1), (SELECT idx FROM TBL_ELEMS_MENUS WHERE titre = 'Ajout d''activité' LIMIT 1), 1),
		((SELECT idx FROM TBL_MENUS WHERE titre = 'Administration' LIMIT 1), (SELECT idx FROM TBL_ELEMS_MENUS WHERE titre = 'Gestion Équipe' LIMIT 1), 55);
END|

DELIMITER ;

-- CALL __up;
