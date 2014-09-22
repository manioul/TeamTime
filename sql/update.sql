DELIMITER |

-- Mise à jour à partir de la version 2.1c
DROP PROCEDURE IF EXISTS post_2_1c|
CREATE PROCEDURE post_2_1c()
BEGIN
	-- Mise à jour des menus
	/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
	/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
	/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
	/*!40101 SET NAMES utf8 */;
	/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
	/*!40103 SET TIME_ZONE='+00:00' */;
	/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
	/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
	/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
	/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
	DROP TABLE IF EXISTS `TBL_MENUS`;
	/*!40101 SET @saved_cs_client     = @@character_set_client */;
	/*!40101 SET character_set_client = utf8 */;
	CREATE TABLE `TBL_MENUS` (
		`idx` int(11) NOT NULL AUTO_INCREMENT,
		`titre` varchar(128) NOT NULL,
		`description` varchar(255) NOT NULL,
		`parent` int(11) NOT NULL,
		`creation` datetime NOT NULL,
		`modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`allowed` varchar(255) NOT NULL DEFAULT 'all',
		`actif` tinyint(1) NOT NULL,
		`type` varchar(32) DEFAULT NULL,
		PRIMARY KEY (`idx`)
	) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
	/*!40101 SET character_set_client = @saved_cs_client */;

	/*!40000 ALTER TABLE `TBL_MENUS` DISABLE KEYS */;
	INSERT INTO `TBL_MENUS` (`idx`, `titre`, `description`, `parent`, `creation`, `modification`, `allowed`, `actif`, `type`) VALUES (1,'principal','Menu principal',0,'0000-00-00 00:00:00','2012-10-14 12:14:25','all',1,'0'),(2,'Grille','Menu permettant d\'accéder aux différents affichages de la grille',1,'0000-00-00 00:00:00','2012-10-14 12:43:37','all',1,'0'),(3,'Décomptes','Décomptes de congés, repas...',1,'0000-00-00 00:00:00','2012-10-14 12:43:50','all',1,'0'),(4,'Administration','Accès aux pages d\'administration',1,'0000-00-00 00:00:00','2014-06-18 23:12:19','teamEdit',1,'0'),(5,'année congés','Accéder aux congés de différentes années',3,'2012-10-25 00:27:34','2012-10-25 08:06:11','all',1,NULL),(6,'calendrier','Gestion des calendriers vacances scolaires, briefings, période de  charge',4,'2012-10-25 10:05:17','2014-06-18 23:14:07','teamEdit',1,NULL),(7,'Planning','',0,'2012-10-25 12:27:48','2012-10-25 10:27:48','all',1,NULL),(8,'évènements','Les évènements spéciaux',3,'2012-11-04 01:28:08','2012-11-04 00:42:05','all',1,NULL),(9,'Gestion utilisateurs','Sous-menu de gestion des utilisateurs de TeamTime',4,'2013-07-10 23:37:06','2014-06-18 23:12:19','teamEdit',1,NULL),(10,'Maintenance','',4,'2013-11-12 18:25:07','2013-11-12 17:25:07','admin',1,NULL),(11,'utilisateur','',1,'2013-11-20 17:16:45','2013-11-20 16:16:45','all',1,NULL),(12,'Heures','Saisie des heures et configuration de la répartition des heures',4,'2013-12-20 14:32:42','2014-06-18 23:13:35','heures,editeurs',1,NULL),(13,'Gestion Équipe','Gestion de l\'équipe, des activités...',4,'2014-04-27 17:16:13','2014-04-27 15:16:13','editeurs',1,NULL);
	/*!40000 ALTER TABLE `TBL_MENUS` ENABLE KEYS */;

	DROP TABLE IF EXISTS `TBL_ELEMS_MENUS`;
	/*!40101 SET @saved_cs_client     = @@character_set_client */;
	/*!40101 SET character_set_client = utf8 */;
	CREATE TABLE `TBL_ELEMS_MENUS` (
		`idx` int(11) NOT NULL AUTO_INCREMENT,
		`titre` varchar(128) NOT NULL,
		`description` varchar(255) NOT NULL,
		`lien` varchar(255) NOT NULL,
		`sousmenu` int(11) DEFAULT NULL,
		`creation` datetime NOT NULL,
		`modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`allowed` varchar(255) NOT NULL DEFAULT 'all',
		`actif` tinyint(1) NOT NULL,
		PRIMARY KEY (`idx`)
	) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8;
	/*!40101 SET character_set_client = @saved_cs_client */;

	/*!40000 ALTER TABLE `TBL_ELEMS_MENUS` DISABLE KEYS */;
	INSERT INTO `TBL_ELEMS_MENUS` (`idx`, `titre`, `description`, `lien`, `sousmenu`, `creation`, `modification`, `allowed`, `actif`) VALUES (1,'Planning','Planning annuel','planning_universel.html',7,'0000-00-00 00:00:00','2012-10-25 10:28:20','all',1),(2,'Grille','Affichage de la grille sous différents formats','affiche_grille.php?nbCycle=3',2,'0000-00-00 00:00:00','2013-06-05 17:02:49','all',1),(3,'Décomptes','Affichages des décomptes divers','tableauxCong.php',3,'0000-00-00 00:00:00','2012-10-13 23:29:33','all',1),(4,'Administration','Lien vers les pages d\'administration','',4,'0000-00-00 00:00:00','2014-06-18 23:03:05','teamEdit',1),(5,'logout','Déconnexion de l\'interface','logout.php',NULL,'0000-00-00 00:00:00','2012-10-13 23:29:33','all',1),(6,'Un cycle','Affichage d\'un cycle unique de la grille','affiche_grille.php',NULL,'0000-00-00 00:00:00','2013-12-21 11:56:56','all',1),(7,'Trois cycles','Affichage de trois cycles de la grille','affiche_grille.php?nbCycle=3',NULL,'0000-00-00 00:00:00','2013-06-05 17:06:24','all',1),(8,'Congés','','tableauxCong.php',5,'0000-00-00 00:00:00','2012-10-24 22:27:55','all',1),(9,'Évènements','','tableauxEvenSpec.php',8,'0000-00-00 00:00:00','2012-11-04 00:30:29','all',1),(10,'Mon compte','','monCompte.php',11,'0000-00-00 00:00:00','2013-12-20 13:57:19','all',1),(11,'Gestion utilisateurs','','creationCompte.php',NULL,'0000-00-00 00:00:00','2012-10-25 11:24:55','admin',1),(12,'Gestion calendrier','Saisir les dates de vacances scolaires et des périodes de charge','',6,'2012-10-14 15:04:11','2014-06-18 23:03:05','teamEdit',1),(13,'2014','','tableauxCong.php?year=2014',NULL,'0000-00-00 00:00:00','2014-06-18 23:14:11','all',1),(14,'2015','','tableauxCong.php?year=2015',NULL,'0000-00-00 00:00:00','2014-06-18 23:14:12','all',1),(15,'Titres édités','Liste des titres de congés déjà édités','litc.php',NULL,'2014-02-28 13:15:45','2014-06-18 23:05:18','teamEdit',1),(30,'Mise hors ligne','Mettre en et hors ligne le site','administration.php',NULL,'2012-10-25 01:43:27','2012-10-25 10:39:56','admin',1),(31,'Briefings','Ajout et modification des dates de briefings','gestion.php?q=briefing',NULL,'2012-10-25 10:08:32','2014-06-18 23:03:05','teamEdit',1),(32,'Période de charge','Ajout et modif des périodes de charge','gestion.php?q=charge',NULL,'2012-10-25 10:08:32','2014-06-18 23:03:05','teamEdit',1),(33,'Vacances scolaires','Ajout et modif des vacances scolaires','gestion.php?q=vacances',NULL,'2012-10-25 10:09:10','2014-06-18 23:03:05','teamEdit',1),(34,'Planning universel','','planning_universel.html',NULL,'2012-10-25 12:29:33','2012-10-25 10:37:37','all',0),(35,'Planning','','planning.php',NULL,'2012-10-25 12:29:33','2012-10-25 10:29:33','all',0),(36,'Situations répétitives','Ajoute des dispo sur de longue périodes','addMultipleDispoUser.php',NULL,'2013-04-21 16:08:28','2013-12-20 13:27:40','all',1),(37,'Saisie Heures','Saisie des heures','saisieHeures.php',NULL,'2013-05-10 09:23:01','2014-06-18 23:00:26','heures',1),(38,'Gestion utilisateur','Ajout et suppression d\'utilisateur','',9,'2013-06-30 09:50:26','2014-06-18 23:05:58','editeurs',1),(39,'Impersonate','Prendre la personnalité de quelqu\'un d\'autre','impersonate.php',NULL,'2013-07-10 23:14:15','2013-07-10 21:15:28','admin',1),(40,'gestion des utilisateurs','Ajoute, supprime des utilisateurs, affecte les droits...','gestionUtilisateur.php',NULL,'2013-08-22 18:46:07','2014-06-18 23:06:14','editeurs',1),(41,'Maintenance','','maintenance.php',10,'2013-11-12 18:25:07','2013-11-12 17:25:07','admin',1),(42,'update','Script de mise à jour','update.php',NULL,'2013-11-12 18:25:07','2013-11-12 17:25:58','admin',0),(43,'Maintenance DB','Vérification et réparation de la base de données','maintenance.php',NULL,'2013-11-12 18:25:07','2013-11-12 17:25:07','admin',1),(44,'Péréquations','Ajoute, supprime des péréquations aux utilisateurs','pereq.php',NULL,'2013-11-13 15:30:09','2014-06-18 23:03:05','teamEdit',1),(45,'Mes infos','information se rapportant à un compte utilisateur','monCompte.php',NULL,'2013-11-20 17:15:53','2013-12-20 13:54:40','all',1),(46,'Mon équipe','Annuaire des utlilsateurs','annuaire.php',NULL,'2013-11-20 17:20:30','2013-12-20 13:55:05','all',1),(47,'Distribution des heures','','distribHeures.php',NULL,'2013-12-18 14:46:55','2014-06-18 23:00:26','heures',1),(48,'Mes heures','','mesHeures.php',NULL,'2013-12-18 14:48:14','2013-12-18 13:48:14','all',1),(49,'Totaux heures','Liste les totaux des heures pour vérifier que la configuration de la répartiution est convenable','lesHeures.php',NULL,'2013-12-20 14:34:58','2014-06-18 23:00:26','heures',1),(50,'Heures','Gestion interne des heures (saisie, config et vérification)','',12,'2013-12-20 14:37:28','2014-06-18 23:00:26','heures',1),(51,'Ajout utilisateur','','ajoutUtilisateur.php',NULL,'2014-03-06 22:25:15','2014-03-06 21:25:15','admin',1),(52,'Gestion des rôles','','rolesUtilisateurs.php',NULL,'2014-03-06 22:25:15','2014-06-18 23:06:52','editeurs',1),(53,'Gestion Équipe','Gestion de l\'équipe, des activités...','',13,'2014-04-27 17:16:13','2014-04-27 15:16:13','editeurs',1),(54,'Ajout d\'activité','Ajoute des activités pour l\'équipe','activites.php',NULL,'2014-04-27 17:16:13','2014-04-27 15:16:13','editeurs',1);
	/*!40000 ALTER TABLE `TBL_ELEMS_MENUS` ENABLE KEYS */;

	DROP TABLE IF EXISTS `TBL_MENUS_ELEMS_MENUS`;
	/*!40101 SET @saved_cs_client     = @@character_set_client */;
	/*!40101 SET character_set_client = utf8 */;
	CREATE TABLE `TBL_MENUS_ELEMS_MENUS` (
		`idxm` int(11) NOT NULL COMMENT 'index du menu',
		`idxem` int(11) NOT NULL,
		`position` tinyint(4) NOT NULL,
		PRIMARY KEY (`idxm`,`idxem`),
		KEY `idxem` (`idxem`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	/*!40101 SET character_set_client = @saved_cs_client */;

	/*!40000 ALTER TABLE `TBL_MENUS_ELEMS_MENUS` DISABLE KEYS */;
	INSERT INTO `TBL_MENUS_ELEMS_MENUS` (`idxm`, `idxem`, `position`) VALUES (1,1,1),(1,2,2),(1,3,3),(1,4,4),(1,5,7),(1,10,5),(2,6,1),(2,7,2),(3,8,1),(3,9,2),(4,12,20),(4,30,100),(4,36,30),(4,38,40),(4,41,90),(4,50,10),(4,53,55),(5,13,1),(5,14,2),(5,15,3),(6,31,1),(6,32,2),(6,33,3),(7,34,2),(7,35,1),(8,16,2),(9,11,1),(9,39,15),(9,40,3),(9,44,5),(9,51,2),(9,52,4),(10,42,2),(10,43,1),(11,45,30),(11,46,20),(11,48,10),(12,37,10),(12,47,20),(12,49,30),(13,54,1);
	/*!40000 ALTER TABLE `TBL_MENUS_ELEMS_MENUS` ENABLE KEYS */;

	/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

	/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
	/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
	/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
	/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
	/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
	/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
	/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

	-- Création d'une table pour lister les différentes affectations (centre, team, grade)
	CREATE TABLE IF NOT EXISTS `TBL_CONFIG_AFFECTATIONS` (
		`caid` int(11) NOT NULL AUTO_INCREMENT,
		`type` varchar(64) NOT NULL,
		`nom` varchar(64) NOT NULL,
		`description` text NOT NULL,
		PRIMARY KEY (`caid`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	CREATE TABLE IF NOT EXISTS `TBL_ANCIENNETE_EQUIPE` (
		`ancid` int(11) NOT NULL AUTO_INCREMENT,
		`uid` int(11) NOT NULL,
		`centre` varchar(50) NOT NULL,
		`team` varchar(10) NOT NULL,
		`beginning` date NOT NULL,
		`end` date DEFAULT NULL,
		`global` BOOLEAN DEFAULT FALSE,
		PRIMARY KEY (`ancid`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	CREATE TABLE IF NOT EXISTS TBL_SIGNUP_ON_HOLD (
		id INT(11) NOT NULL AUTO_INCREMENT,
		nom varchar(64) NOT NULL,
		prenom VARCHAR(64) NOT NULL,
		email VARCHAR(128) NOT NULL,
		centre VARCHAR(50) NOT NULL,
		team VARCHAR(10) NOT NULL,
		beginning DATE,
		end DATE,
		timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
		url VARCHAR(40) NULL DEFAULT NULL,
		grade VARCHAR(64) NULL DEFAULT NULL,
		classe VARCHAR(10) NULL DEFAULT NULL,
		PRIMARY KEY (id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	-- Attribue les congés à toutes les équipes
	UPDATE TBL_DISPO SET team = 'all' WHERE `type decompte` = 'conges';
	ALTER TABLE `TBL_DISPO` CHANGE `absence` `absence` DECIMAL( 2, 1  ) NOT NULL COMMENT 'Indique si la dispo correspond à une absence (0), à une présence (1) ou à une demi-équipe (.5)';

	ALTER TABLE TBL_VACANCES_A_ANNULER ADD edited BOOLEAN NOT NULL DEFAULT FALSE;

--	INSERT INTO TBL_CONFIG_AFFECTATIONS
--	(caid, type, nom, description)
--	VALUES
--	(NULL, 'classe', 'c', 'Élève'),
--	(NULL, 'classe', 'pc', 'Premier contrôleur'),
--	(NULL, 'classe', 'ce', "Chef d''équipe"),
--	(NULL, 'classe', 'dtch', 'détaché'),
--	(NULL, 'classe', 'fmp', 'Adjoint chef de salle'),
--	(NULL, 'classe', 'cds', 'Chef de salle');
--
--	INSERT INTO `ttm`.`TBL_ARTICLES` (`idx`, `titre`, `description`, `texte`, `analyse`, `creation`, `modification`, `restricted`, `actif`) VALUES (NULL, 'Création de votre compte', '', 'Votre compte a été créé. Vous pouvez vous connecter dès maintenant en cliquant sur « Connexion ».', '', NOW(), CURRENT_TIMESTAMP, '0', '1');

	DROP TABLE IF EXISTS TBL_CLASSE;

	-- Relations pour les utilisateurs (uid)
	ALTER TABLE `TBL_ROLES` CHANGE `uid` `uid` SMALLINT NOT NULL, ADD KEY `uid` (`uid`), ADD CONSTRAINT `TBL_ROLES_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `TBL_USERS` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `TBL_AFFECTATION` CHANGE `uid` `uid` SMALLINT NOT NULL, ADD KEY `uid` (`uid`), ADD CONSTRAINT `TBL_AFFECTATION_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `TBL_USERS` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `TBL_ANCIENNETE_EQUIPE` CHANGE `uid` `uid` SMALLINT NOT NULL, ADD KEY `uid` (`uid`), ADD CONSTRAINT `TBL_ANCIENNETE_EQUIPE_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `TBL_USERS` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `TBL_PHONE` CHANGE `uid` `uid` SMALLINT NOT NULL, ADD KEY `uid` (`uid`), ADD CONSTRAINT `TBL_PHONE_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `TBL_USERS` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `TBL_ADRESSES` CHANGE `uid` `uid` SMALLINT NOT NULL, ADD KEY `uid` (`uid`), ADD CONSTRAINT `TBL_ADRESSES_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `TBL_USERS` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `TBL_REMPLA` CHANGE `uid` `uid` SMALLINT NOT NULL, ADD KEY `uid` (`uid`), ADD CONSTRAINT `TBL_REMPLA_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `TBL_USERS` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `TBL_EVENEMENTS_SPECIAUX` CHANGE `uid` `uid` SMALLINT NOT NULL, ADD KEY `uid` (`uid`), ADD CONSTRAINT `TBL_EVENEMENTS_SPECIAUX_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `TBL_USERS` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `TBL_HEURES` CHANGE `uid` `uid` SMALLINT NOT NULL, ADD KEY `uid` (`uid`), ADD CONSTRAINT `TBL_HEURES_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `TBL_USERS` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `TBL_VACANCES_A_ANNULER` CHANGE `uid` `uid` SMALLINT NOT NULL, ADD KEY `uid` (`uid`), ADD CONSTRAINT `TBL_VACANCES_A_ANNULER_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `TBL_USERS` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `TBL_TIPOFTHEDAY` CHANGE `uid` `uid` SMALLINT NOT NULL, ADD KEY `uid` (`uid`), ADD CONSTRAINT `TBL_TIPOFTHEDAY_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `TBL_USERS` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `TBL_LOG` CHANGE `uid` `uid` SMALLINT NOT NULL, ADD KEY `uid` (`uid`), ADD CONSTRAINT `TBL_LOG_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `TBL_USERS` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `TBL_L_SHIFT_DISPO` CHANGE `uid` `uid` SMALLINT NOT NULL, ADD KEY `uid` (`uid`), ADD CONSTRAINT `TBL_L_SHIFT_DISPO_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `TBL_USERS` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

	-- Relations pour les activités (did)
	ALTER TABLE `TBL_L_SHIFT_DISPO` CHANGE `did` `did` SMALLINT NOT NULL, ADD KEY `did` (`did`), ADD CONSTRAINT `TBL_L_SHIFT_DISPO_ibfk_2` FOREIGN KEY (`did`) REFERENCES `TBL_DISPO` (`did`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `TBL_VACANCES` ADD KEY `sdid` (`sdid`), ADD CONSTRAINT `TBL_VACANCES_ibfk_1` FOREIGN KEY (`sdid`) REFERENCES `TBL_L_SHIFT_DISPO` (`sdid`) ON DELETE CASCADE ON UPDATE CASCADE;

	-- Relations pour les menus
	ALTER TABLE `TBL_MENUS_ELEMS_MENUS` ADD CONSTRAINT `TBL_MENUS_ELEMS_MENUS_ibfk_1` FOREIGN KEY (`idxm`) REFERENCES `TBL_MENUS` (`idx`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `TBL_MENUS_ELEMS_MENUS` ADD CONSTRAINT `TBL_MENUS_ELEMS_MENUS_ibfk_2` FOREIGN KEY (`idxem`) REFERENCES `TBL_ELEMS_MENUS` (`idx`) ON DELETE CASCADE ON UPDATE CASCADE;

	DROP VIEW IF EXISTS classes;
	CREATE VIEW classes AS
		SELECT u.uid AS uid, nom, prenom, 'pc' AS `classe`, MIN(beginning) AS `beginning`, MAX(end) AS `end`, `poids`, `actif`
		FROM TBL_AFFECTATION AS c, TBL_USERS AS u
		WHERE u.uid = c.uid
		AND (grade = 'pc' OR grade = 'dtch' OR grade = 'fmp')
		AND `validated` IS TRUE
		GROUP BY u.uid
		UNION
		SELECT u.uid AS uid, nom, prenom, 'dtch' AS `classe`, MIN(beginning) AS `beginning`, MAX(end) AS `end`, `poids`, `actif`
		FROM TBL_AFFECTATION AS c, TBL_USERS AS u
		WHERE u.uid = c.uid
		AND grade = 'dtch'
		AND `validated` IS TRUE
		GROUP BY u.uid
		UNION
		SELECT u.uid AS uid, nom, prenom, 'fmp' AS `classe`, MIN(beginning) AS `beginning`, MAX(end) AS `end`, `poids`, `actif`
		FROM TBL_AFFECTATION AS c, TBL_USERS AS u
		WHERE u.uid = c.uid 
		AND grade = 'fmp'
		AND `validated` IS TRUE
		GROUP BY u.uid
		UNION
		SELECT u.uid AS uid, nom, prenom, 'ce' AS `classe`, MIN(beginning) AS `beginning`, MAX(end) AS `end`, `poids`, `actif`
		FROM TBL_AFFECTATION AS c, TBL_USERS AS u
		WHERE u.uid = c.uid
		AND grade = 'ce'
		AND `validated` IS TRUE
		GROUP BY u.uid
		UNION
		SELECT u.uid AS uid, nom, prenom, 'c' AS `classe`, MIN(beginning) AS `beginning`, MAX(end) AS `end`, `poids`, `actif`
		FROM TBL_AFFECTATION AS c, TBL_USERS AS u
		WHERE u.uid = c.uid
		AND (grade = 'c' OR `grade` = 'theo')
		AND `validated` IS TRUE
		GROUP BY u.uid
		UNION
		SELECT u.uid AS uid, nom, prenom, 'cds' AS `classe`, MIN(beginning) AS `beginning`, MAX(end) AS `end`, `poids`, `actif`
		FROM TBL_AFFECTATION AS c, TBL_USERS AS u
		WHERE u.uid = c.uid
		AND grade = 'cds'
		AND `validated` IS TRUE
		GROUP BY u.uid;
END
|

-- Mise à jour à partir de la version 2.2a
DROP PROCEDURE IF EXISTS post_2_2a|
CREATE PROCEDURE post_2_2a()
BEGIN
	ALTER TABLE `TBL_DISPATCH_HEURES_USER` ADD KEY `rid` (`rid`), ADD CONSTRAINT `TBL_DISPATCH_HEURES_USER_ibfk_1` FOREIGN KEY (`rid`) REFERENCES `TBL_DISPATCH_HEURES` (`rid`) ON DELETE CASCADE ON UPDATE CASCADE;
	DROP TRIGGER IF EXISTS updateDispatchSchema;
	DROP TRIGGER IF EXISTS deleteDispatchSchema;
	ALTER TABLE `TBL_L_SHIFT_DISPO` CHANGE `uid` `uid` SMALLINT NOT NULL ,
		CHANGE `did` `did` SMALLINT NOT NULL;
	-- email doit être unique pour notamment la récupération des mots de passe et l'inscription
	ALTER TABLE `TBL_SIGNUP_ON_HOLD` ADD UNIQUE (`email`);
	-- Ajout de la catégorie TRACE pour les messages système
	ALTER TABLE `TBL_MESSAGES_SYSTEME` CHANGE `catégorie` `catégorie` SET( 'DEBUG', 'INFO', 'ERREUR', 'LOG', 'USER', 'TRACE'  ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'USER';
	INSERT INTO `ttm`.`TBL_ARTICLES` (`idx`, `titre`, `description`, `texte`, `analyse`, `creation`, `modification`, `restricted`, `actif`) VALUES (NULL, 'Création de votre compte TeamTime', 'account created', 'Bonjour %s,
		
Un administrateur vous a créé un compte sur TeamTime et la communauté de
ses utilisateurs vous souhaite la bienvenue.

Vous pouvez compléter votre inscription en cliquant sur le lien suivant :
http%s://%s/createAccount.php?k=%s
(Ce lien est valable une semaine ; au-delà, vous devrez effectuer une
nouvelle inscription ou demander à votre administrateur de vous créer un
nouveau compte)

Nous vous recommandons de choisir un mot de passe suffisamment complexe
et unique afin de protéger vos données personnelles et les données des
autres utilisateurs de TeamTime.

Grâce à TeamTime, vous pouvez déposer vos congés, récup, stages
où que vous soyez et quand vous le souhaitez.
Vous pouvez également vérifier les prochaines vacations, et voir qui sera
présent.
Vous visualisez, aisément, les briefings à venir, la période de charge,
les vacances scolaires.
Vous suivez votre décompte de congés et de récup, à tout moment.
Vous accédez, également, à votre décompte d''heures très facilement.

Ne Répondez pas à cet email, svp.

Pour toute question, contactez le webmaster :
Mail : webmaster@teamtime.me
XMPP : manioul@teamtime.me
Friendica : https://titoux.info/profile/teamtime

Bonne utilisation.

++ ;)', '0', NOW(), CURRENT_TIMESTAMP, '1', '1');
	INSERT INTO `ttm`.`TBL_ARTICLES` (`idx`, `titre`, `description`, `texte`, `analyse`, `creation`, `modification`, `restricted`, `actif`) VALUES (NULL, 'Mise à jour de votre compte TeamTime', 'account updated', 'Bonjour %s,

Votre compte pour utiliser TeamTime a été mis à jour.
Vous pouvez désormais y accéder sur :
http%s://%s
à l''aide des identifiants suivant (gare aux majuscules/minuscules)
login : %s
mot de passe : %s

Grâce à TeamTime, vous pouvez déposer vos congés, récup, stages
où que vous soyez et quand vous le souhaitez.
Vous pouvez également vérifier les prochaines vacations, et voir qui sera
présent.
Vous visualisez, aisément, les briefings à venir, la période de charge,
les vacances scolaires.
Vous suivez votre décompte de congés et de récup, à tout moment.
Vous accédez, également, à votre décompte d''heures très facilement.

Ne Répondez pas à cet email, svp.

Pour toute question, contactez le webmaster :
Mail : webmaster@teamtime.me
XMPP : manioul@teamtime.me
Friendica : https://titoux.info/profile/teamtime

Bonne utilisation.

++ ;)', '0', NOW(), CURRENT_TIMESTAMP, '1', '1');
	INSERT INTO `ttm`.`TBL_ARTICLES` (`idx`, `titre`, `description`, `texte`, `analyse`, `creation`, `modification`, `restricted`, `actif`) VALUES (NULL, 'Validation de votre inscription sur TeamTime', 'account accepted', 'Bonjour %s,

Félicitations, votre inscription sur TeamTime vient d''etre acceptée.

Vous pouvez maintenant renseigner votre compte sur la page suivante :
http%s://%s/createAccount.php?k=%s
(Ce lien est valable une semaine ; au-delà, vous devrez à nouveau
compléter une inscription).

Nous vous conseillons instamment de créer un mot de passe complexe et unique
afin de protéger vos données et celles des autres utilisateurs de TeamTime.

Ne Répondez pas à cet email, svp.

Pour toute question, contactez le webmaster :
Mail : webmaster@teamtime.me
XMPP : manioul@teamtime.me
Friendica : https://titoux.info/profile/teamtime

Bonne utilisation.

++ ;)', '0', NOW(), CURRENT_TIMESTAMP, '1', '1');
	INSERT INTO `ttm`.`TBL_ARTICLES` (`idx`, `titre`, `description`, `texte`, `analyse`, `creation`, `modification`, `restricted`, `actif`) VALUES (NULL, 'Demande de réinitialisation de votre mot de passe TeamTime', 'reset password', 'Bonjour %s,

Vous avez demandé à réinitialiser votre mot de passe pour utiliser TeamTime.
Pour cela, il vous suffit de suivre le lien ci-dessous :
http%s://%s/createAccount.php?k=%s
(Ce lien est valable une semaine ; au-delà, vous devrez reformuler une
demande de réinitialisation de votre mot de passe).

Si vous n''avez pas fait de demande de modification de votre mot de passe,
merci de nous signaler cet abus en cliquant le lien ci-dessous :
http%s://%s/abuse.php?k=%s&t=pwdchg

Ne Répondez pas à cet email, svp.

Pour toute question, contactez le webmaster :
Mail : webmaster@teamtime.me
XMPP : manioul@teamtime.me
Friendica : https://titoux.info/profile/teamtime

++ ;)', '0', NOW(), CURRENT_TIMESTAMP, '1', '1');
	INSERT INTO `ttm`.`TBL_ARTICLES` (`idx`, `titre`, `description`, `texte`, `analyse`, `creation`, `modification`, `restricted`, `actif`) VALUES (NULL, 'Mise à jour de votre mot de passe TeamTime', 'password updated', 'Bonjour %s,

Votre mot de passe a été mis à jour avec succès.
Votre login est : %s

Ne Répondez pas à cet email, svp.

Pour toute question, contactez le webmaster :
Mail : webmaster@teamtime.me
XMPP : manioul@teamtime.me
Friendica : https://titoux.info/profile/teamtime

++ ;)', '0', NOW(), CURRENT_TIMESTAMP, '1', '1');
		
END
|

-- Mise à jour à partir de la version 2.3a
DROP PROCEDURE IF EXISTS post_2_3a|
CREATE PROCEDURE post_2_3a()
BEGIN
	UPDATE `TBL_ARTICLES` SET `description` = 'licence' WHERE `TBL_ARTICLES`.`titre` = 'Licence' LIMIT 1;
	INSERT INTO `ttm`.`TBL_ARTICLES` (
		`idx` ,
		`titre` ,
		`description` ,
		`texte` ,
		`analyse` ,
		`creation` ,
		`modification` ,
		`restricted` ,
		`actif`

	)
	VALUES (
		NULL , 'Mise à jour de votre mot de passe', 'reset password ok', 'Votre mot de passe a été mis à jour. Vous allez recevoir un email avec votre identifiant.{br}Vous pouvez vous connecter avec votre nouveau mot de passe dès maintenant.', '1', NOW(  ) ,
		CURRENT_TIMESTAMP , '0', '1'

		), (
		NULL , 'Mise à jour de votre mot passe échouée', 'reset password failed', 'Désolé, la mise à jour de votre mot de passe a échouée...', '', NOW(  ) ,
		CURRENT_TIMESTAMP , '', '1'

	);
END
|

DELIMITER ;

-- CALL post_2_1c();
-- CALL post_2_2a();
CALL post_2_3a();


