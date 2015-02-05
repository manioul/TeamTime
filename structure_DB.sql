
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

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `ttm` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `ttm`;
DROP TABLE IF EXISTS `TBL_ADRESSES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_ADRESSES` (
  `adresseid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` smallint(6) NOT NULL,
  `adresse` text NOT NULL,
  `cp` varchar(15) NOT NULL,
  `ville` varchar(80) NOT NULL,
  PRIMARY KEY (`adresseid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_AFFECTATION`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_AFFECTATION` (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `centre` varchar(50) NOT NULL DEFAULT 'athis',
  `team` varchar(10) NOT NULL DEFAULT '9e',
  `grade` varchar(64) NOT NULL,
  `beginning` date NOT NULL,
  `end` date NOT NULL,
  `validated` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Permet à un admin de valider cette entrée',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_ANCIENNETE_EQUIPE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_ANCIENNETE_EQUIPE` (
  `ancid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `centre` varchar(50) NOT NULL,
  `team` varchar(10) NOT NULL,
  `beginning` date NOT NULL,
  `end` date DEFAULT NULL,
  `global` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`ancid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_ARTICLES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_ARTICLES` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(128) NOT NULL,
  `description` varchar(255) NOT NULL,
  `texte` mediumtext NOT NULL,
  `analyse` tinyint(1) NOT NULL,
  `creation` datetime NOT NULL,
  `modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `restricted` tinyint(1) NOT NULL,
  `actif` tinyint(1) NOT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_ARTICLES_RUBRIQUES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_ARTICLES_RUBRIQUES` (
  `idxa` int(11) NOT NULL,
  `idxu` int(11) NOT NULL,
  PRIMARY KEY (`idxa`,`idxu`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_BRIEFING`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_BRIEFING` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `dateD` date NOT NULL COMMENT 'date de début',
  `dateF` date NOT NULL COMMENT 'date de fin',
  `description` varchar(255) NOT NULL,
  `centre` varchar(50) NOT NULL DEFAULT 'athis',
  `team` varchar(10) NOT NULL DEFAULT 'all',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_CONFIG_AFFECTATIONS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_CONFIG_AFFECTATIONS` (
  `caid` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(64) NOT NULL,
  `nom` varchar(64) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`caid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_CONSTANTS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_CONSTANTS` (
  `nom` varchar(255) NOT NULL,
  `valeur` text NOT NULL,
  `type` enum('int','float','string','bool') NOT NULL,
  `commentaires` text NOT NULL,
  PRIMARY KEY (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_CYCLE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_CYCLE` (
  `cid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `vacation` varchar(8) NOT NULL,
  `horaires` varchar(10) NOT NULL,
  `rang` tinyint(4) NOT NULL,
  `centre` varchar(50) NOT NULL,
  `team` varchar(10) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Description du cycle de travail';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_DISPATCH_HEURES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_DISPATCH_HEURES` (
  `rid` int(11) NOT NULL AUTO_INCREMENT,
  `cids` varchar(64) NOT NULL,
  `centre` varchar(50) NOT NULL DEFAULT 'athis',
  `team` varchar(10) NOT NULL DEFAULT '9e',
  `grades` varchar(60) NOT NULL DEFAULT 'pc',
  `dids` varchar(128) DEFAULT NULL,
  `type` enum('norm','instru','simu') NOT NULL,
  `statut` enum('shared','fixed') NOT NULL COMMENT 'Les heures sont partagées ou fixes',
  `heures` decimal(4,2) NOT NULL COMMENT 'Nombre de minutes allouées',
  `ordre` int(11) NOT NULL COMMENT 'définit la précédence des règles',
  PRIMARY KEY (`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER updateDispatchSchema
	AFTER UPDATE ON TBL_DISPATCH_HEURES
	FOR EACH ROW
	CALL updateDispatchSchema(OLD.rid) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `TBL_DISPATCH_HEURES_USER`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_DISPATCH_HEURES_USER` (
  `rid` int(11) NOT NULL,
  `cycles` varchar(64) NOT NULL,
  `centre` varchar(50) NOT NULL DEFAULT 'athis',
  `team` varchar(10) NOT NULL DEFAULT '9e',
  `grades` varchar(60) NOT NULL DEFAULT 'pc',
  `dispos` varchar(128) DEFAULT NULL,
  `type` enum('norm','instru','simu') NOT NULL,
  `statut` enum('shared','fixed') NOT NULL COMMENT 'Les heures sont partagées ou fixes',
  `heures` decimal(4,2) NOT NULL COMMENT 'Nombre d''heures allouées (en décimal)',
  PRIMARY KEY (`rid`),
  KEY `rid` (`rid`),
  CONSTRAINT `TBL_DISPATCH_HEURES_USER_ibfk_1` FOREIGN KEY (`rid`) REFERENCES `TBL_DISPATCH_HEURES` (`rid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_DISPO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_DISPO` (
  `did` smallint(6) NOT NULL AUTO_INCREMENT,
  `dispo` varchar(16) NOT NULL,
  `Jours possibles` varchar(20) NOT NULL COMMENT 'Liste des jours possibles pour la présence définie',
  `classes possibles` varchar(32) NOT NULL DEFAULT 'all' COMMENT 'la classe d''utilisateurs pour laquelle la dispo est possible',
  `peut poser` varchar(1024) NOT NULL DEFAULT 'all' COMMENT 'Liste des personnes ou classes qui peuvent poser cette dispo (all, classes, ou login)',
  `poids` tinyint(4) NOT NULL COMMENT 'Le poids de la dispo permet de définir la place dans le menu',
  `absence` decimal(2,1) NOT NULL COMMENT 'Indique si la dispo correspond à une absence (0), à une présence (1) ou à une demi-équipe (.5)',
  `heures` set('pc','coach','fmp','cds','c','recyclage','rfrt') DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `type decompte` varchar(64) DEFAULT NULL,
  `need_compteur` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Définit si cette dispo doit être comptabilisée dans la table vacances',
  `quantity` int(11) NOT NULL COMMENT 'Quantité de cette dispo autorisée annuellement (need_compteur = TRUE)',
  `date limite depot` varchar(5) DEFAULT NULL COMMENT 'Date limite de dépôt du congé',
  `nom_long` varchar(45) NOT NULL,
  `centre` varchar(50) NOT NULL DEFAULT 'athis',
  `team` varchar(10) NOT NULL DEFAULT '9e',
  PRIMARY KEY (`did`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_EVENEMENTS_SPECIAUX`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_EVENEMENTS_SPECIAUX` (
  `teid` int(11) NOT NULL AUTO_INCREMENT,
  `did` tinyint(4) NOT NULL,
  `uid` tinyint(4) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`teid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_GRILLE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_GRILLE` (
  `grid` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `cid` tinyint(4) NOT NULL,
  `grilleId` int(11) NOT NULL COMMENT 'Le numéro de la grille à laquelle appartient ce jour de travail',
  `conf` enum('E','W') NOT NULL,
  `pcid` smallint(6) NOT NULL,
  `vsid` smallint(6) NOT NULL,
  `briefing` varchar(40) DEFAULT NULL,
  `readOnly` tinyint(1) NOT NULL DEFAULT '0',
  `ferie` tinyint(1) NOT NULL COMMENT 'true si le jour doit être considéré comme férié',
  `centre` varchar(50) NOT NULL DEFAULT 'athis',
  `team` varchar(10) NOT NULL DEFAULT '9e',
  PRIMARY KEY (`grid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_GROUPS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_GROUPS` (
  `gid` tinyint(4) unsigned NOT NULL COMMENT 'Plus gid est petit, plus les droits sont importants',
  `groupe` varchar(16) NOT NULL,
  `description` mediumtext NOT NULL,
  PRIMARY KEY (`groupe`),
  UNIQUE KEY `gid` (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_HEURES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_HEURES` (
  `uid` int(11) NOT NULL,
  `did` int(11) NOT NULL,
  `date` date NOT NULL,
  `normales` decimal(4,2) NOT NULL,
  `instruction` decimal(4,2) NOT NULL,
  `simulateur` decimal(4,2) NOT NULL,
  `double` decimal(4,2) NOT NULL,
  `statut` enum('fixed','shared','unattr') DEFAULT 'unattr',
  PRIMARY KEY (`uid`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_HEURES_A_PARTAGER`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_HEURES_A_PARTAGER` (
  `centre` varchar(50) NOT NULL,
  `team` varchar(10) NOT NULL,
  `date` date NOT NULL,
  `heures` decimal(4,2) NOT NULL,
  `dispatched` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'POsitionné lorsque les heures ont été calculées',
  `writable` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`centre`,`team`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Le nombre d''heures à paratager par jour';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER deleteHours
	AFTER DELETE ON TBL_HEURES_A_PARTAGER
	FOR EACH ROW
	DELETE FROM TBL_HEURES
		WHERE date = OLD.date
			AND uid IN (SELECT uid
				FROM TBL_AFFECTATION
				WHERE centre = OLD.centre
				AND team = OLD.team
				AND OLD.date BETWEEN beginning AND end) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `TBL_LOG`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_LOG` (
  `lid` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL COMMENT 'date heure de l''opération',
  `uid` tinyint(4) NOT NULL,
  `dateCel` date NOT NULL COMMENT 'date de la cellule concernée',
  `uidCel` tinyint(4) NOT NULL COMMENT 'uid de l''utilisateur concerné par la modification',
  `did` tinyint(4) NOT NULL COMMENT 'valeur attribuée à la celulle',
  `previous` varchar(10) NOT NULL COMMENT 'valeur précédente de la celulle',
  `undo` text NOT NULL COMMENT 'Requête pour défaire l''action entreprise',
  `ip` varchar(40) NOT NULL COMMENT 'IP de l''initiateur de la modification',
  PRIMARY KEY (`lid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_L_SHIFT_DISPO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_L_SHIFT_DISPO` (
  `sdid` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'identifiant unique de l''occupation',
  `date` date NOT NULL,
  `uid` tinyint(4) NOT NULL,
  `did` tinyint(4) NOT NULL,
  `pereq` tinyint(1) NOT NULL COMMENT 'Ceci est une péréquation et ne correspond pas à un évènement réel',
  `priorite` tinyint(4) DEFAULT NULL COMMENT 'Définit un ordre dans le cas de dispo multiples',
  PRIMARY KEY (`sdid`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
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
DROP TABLE IF EXISTS `TBL_MESSAGES_SYSTEME`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_MESSAGES_SYSTEME` (
  `mid` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur` varchar(63) NOT NULL,
  `catégorie` set('DEBUG','INFO','ERREUR','LOG','USER') NOT NULL DEFAULT 'USER',
  `appelant` varchar(64) NOT NULL DEFAULT 'unknown',
  `short` tinytext NOT NULL,
  `message` text NOT NULL,
  `contexte` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lu` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_PERIODE_CHARGE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_PERIODE_CHARGE` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `dateD` date NOT NULL COMMENT 'date de début',
  `dateF` date NOT NULL COMMENT 'date de fin',
  `description` varchar(255) NOT NULL,
  `centre` varchar(50) NOT NULL DEFAULT 'athis',
  `team` varchar(10) NOT NULL DEFAULT 'all',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_PHONE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_PHONE` (
  `phoneid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` smallint(6) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `description` text NOT NULL,
  `principal` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`phoneid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tables des numéro de téléphone des utilisateurs';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_REMPLA`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_REMPLA` (
  `uid` tinyint(4) NOT NULL,
  `date` date NOT NULL,
  `nom` varchar(40) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`uid`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_ROLES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_ROLES` (
  `rid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` smallint(6) NOT NULL,
  `role` varchar(10) NOT NULL,
  `centre` varchar(50) NOT NULL,
  `team` varchar(10) NOT NULL,
  `beginning` date NOT NULL,
  `end` date NOT NULL,
  `commentaire` varchar(150) NOT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rid`,`uid`,`role`,`centre`,`team`),
  KEY `uid` (`uid`),
  CONSTRAINT `TBL_ROLES_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `TBL_USERS` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_RUBRIQUES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_RUBRIQUES` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(128) NOT NULL,
  `description` varchar(255) NOT NULL,
  `analyse` tinyint(1) NOT NULL,
  `creation` datetime NOT NULL,
  `modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `restricted` tinyint(1) NOT NULL,
  `actif` tinyint(1) NOT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_SIGNUP_ON_HOLD`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_SIGNUP_ON_HOLD` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(64) NOT NULL,
  `prenom` varchar(64) NOT NULL,
  `email` varchar(128) NOT NULL,
  `centre` varchar(50) NOT NULL,
  `team` varchar(10) NOT NULL,
  `beginning` date DEFAULT NULL,
  `end` date DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `url` varchar(40) DEFAULT NULL,
  `grade` varchar(64) DEFAULT NULL,
  `classe` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_TIPOFTHEDAY`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_TIPOFTHEDAY` (
  `tipid` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `tip` text NOT NULL,
  `uid` tinyint(4) NOT NULL,
  PRIMARY KEY (`tipid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_USERS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_USERS` (
  `uid` smallint(6) NOT NULL AUTO_INCREMENT,
  `nom` varchar(64) NOT NULL,
  `prenom` varchar(64) NOT NULL,
  `vismed` date NOT NULL,
  `login` varchar(15) NOT NULL,
  `email` varchar(128) NOT NULL,
  `sha1` varchar(40) NOT NULL,
  `lastlogin` date NOT NULL,
  `nblogin` int(11) NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `poids` smallint(6) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `showtipoftheday` tinyint(1) NOT NULL DEFAULT '1',
  `page` varchar(255) NOT NULL DEFAULT 'affiche_grille.php' COMMENT 'La page affichée après la connexion d''un utilisateur',
  `pref` text NOT NULL COMMENT 'préférences utilisateurs au format JSON',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_VACANCES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_VACANCES` (
  `sdid` bigint(20) NOT NULL,
  `etat` tinyint(4) NOT NULL COMMENT 'Définit un état incrémental du congé. L''état standard a pour valeur 0, filed a pour valeur 1 et confirmed a pour valeur 2',
  `year` year(4) NOT NULL COMMENT 'L''année sur laquelle sera décompté le congé',
  PRIMARY KEY (`sdid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_VACANCES_A_ANNULER`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_VACANCES_A_ANNULER` (
  `uid` int(11) NOT NULL,
  `did` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `edited` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TBL_VACANCES_SCOLAIRES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TBL_VACANCES_SCOLAIRES` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `dateD` date NOT NULL COMMENT 'date de début',
  `dateF` date NOT NULL COMMENT 'date de fin',
  `description` varchar(255) NOT NULL,
  `centre` varchar(50) NOT NULL DEFAULT 'athis',
  `team` varchar(10) NOT NULL DEFAULT 'all',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `VIEW_LIST_DISPO`;
/*!50001 DROP VIEW IF EXISTS `VIEW_LIST_DISPO`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `VIEW_LIST_DISPO` (
  `sdid` tinyint NOT NULL,
  `uid` tinyint NOT NULL,
  `nom` tinyint NOT NULL,
  `dispo` tinyint NOT NULL,
  `date` tinyint NOT NULL,
  `vacation` tinyint NOT NULL,
  `year` tinyint NOT NULL,
  `pereq` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `affectations`;
/*!50001 DROP VIEW IF EXISTS `affectations`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `affectations` (
  `nom` tinyint NOT NULL,
  `centre` tinyint NOT NULL,
  `team` tinyint NOT NULL,
  `grade` tinyint NOT NULL,
  `beginning` tinyint NOT NULL,
  `end` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `classes`;
/*!50001 DROP VIEW IF EXISTS `classes`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `classes` (
  `uid` tinyint NOT NULL,
  `nom` tinyint NOT NULL,
  `prenom` tinyint NOT NULL,
  `classe` tinyint NOT NULL,
  `beginning` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `poids` tinyint NOT NULL,
  `actif` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

USE `ttm`;
/*!50001 DROP TABLE IF EXISTS `VIEW_LIST_DISPO`*/;
/*!50001 DROP VIEW IF EXISTS `VIEW_LIST_DISPO`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `VIEW_LIST_DISPO` AS select `l`.`sdid` AS `sdid`,`l`.`uid` AS `uid`,`u`.`nom` AS `nom`,`d`.`dispo` AS `dispo`,`l`.`date` AS `date`,`c`.`vacation` AS `vacation`,year(`l`.`date`) AS `year`,`l`.`pereq` AS `pereq` from ((((`TBL_L_SHIFT_DISPO` `l` join `TBL_USERS` `u`) join `TBL_DISPO` `d`) join `TBL_GRILLE` `g`) join `TBL_CYCLE` `c`) where ((`l`.`date` = `g`.`date`) and (`g`.`cid` = `c`.`cid`) and (`u`.`uid` = `l`.`uid`) and (`d`.`did` = `l`.`did`)) union select `l`.`sdid` AS `sdid`,`l`.`uid` AS `uid`,`u`.`nom` AS `nom`,`d`.`dispo` AS `dispo`,`l`.`date` AS `date`,`l`.`date` AS `date`,`v`.`year` AS `year`,`l`.`pereq` AS `pereq` from (((`TBL_L_SHIFT_DISPO` `l` join `TBL_USERS` `u`) join `TBL_DISPO` `d`) join `TBL_VACANCES` `v`) where ((`l`.`date` = 0) and (`l`.`sdid` = `v`.`sdid`) and (`u`.`uid` = `l`.`uid`) and (`d`.`did` = `l`.`did`)) order by `date`,`nom` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP TABLE IF EXISTS `affectations`*/;
/*!50001 DROP VIEW IF EXISTS `affectations`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `affectations` AS select `u`.`nom` AS `nom`,`a`.`centre` AS `centre`,`a`.`team` AS `team`,`a`.`grade` AS `grade`,`a`.`beginning` AS `beginning`,`a`.`end` AS `end` from (`TBL_AFFECTATION` `a` join `TBL_USERS` `u`) where (`a`.`uid` = `u`.`uid`) order by `u`.`actif` desc,`u`.`nom`,`a`.`beginning` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP TABLE IF EXISTS `classes`*/;
/*!50001 DROP VIEW IF EXISTS `classes`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `classes` AS select `u`.`uid` AS `uid`,`u`.`nom` AS `nom`,`u`.`prenom` AS `prenom`,'pc' AS `classe`,min(`c`.`beginning`) AS `beginning`,max(`c`.`end`) AS `end`,`u`.`poids` AS `poids`,`u`.`actif` AS `actif` from (`TBL_AFFECTATION` `c` join `TBL_USERS` `u`) where ((`u`.`uid` = `c`.`uid`) and ((`c`.`grade` = 'pc') or (`c`.`grade` = 'dtch') or (`c`.`grade` = 'fmp')) and (`c`.`validated` is true)) group by `u`.`uid` union select `u`.`uid` AS `uid`,`u`.`nom` AS `nom`,`u`.`prenom` AS `prenom`,'dtch' AS `classe`,min(`c`.`beginning`) AS `beginning`,max(`c`.`end`) AS `end`,`u`.`poids` AS `poids`,`u`.`actif` AS `actif` from (`TBL_AFFECTATION` `c` join `TBL_USERS` `u`) where ((`u`.`uid` = `c`.`uid`) and (`c`.`grade` = 'dtch') and (`c`.`validated` is true)) group by `u`.`uid` union select `u`.`uid` AS `uid`,`u`.`nom` AS `nom`,`u`.`prenom` AS `prenom`,'fmp' AS `classe`,min(`c`.`beginning`) AS `beginning`,max(`c`.`end`) AS `end`,`u`.`poids` AS `poids`,`u`.`actif` AS `actif` from (`TBL_AFFECTATION` `c` join `TBL_USERS` `u`) where ((`u`.`uid` = `c`.`uid`) and (`c`.`grade` = 'fmp') and (`c`.`validated` is true)) group by `u`.`uid` union select `u`.`uid` AS `uid`,`u`.`nom` AS `nom`,`u`.`prenom` AS `prenom`,'ce' AS `classe`,min(`c`.`beginning`) AS `beginning`,max(`c`.`end`) AS `end`,`u`.`poids` AS `poids`,`u`.`actif` AS `actif` from (`TBL_AFFECTATION` `c` join `TBL_USERS` `u`) where ((`u`.`uid` = `c`.`uid`) and (`c`.`grade` = 'ce') and (`c`.`validated` is true)) group by `u`.`uid` union select `u`.`uid` AS `uid`,`u`.`nom` AS `nom`,`u`.`prenom` AS `prenom`,'c' AS `classe`,min(`c`.`beginning`) AS `beginning`,max(`c`.`end`) AS `end`,`u`.`poids` AS `poids`,`u`.`actif` AS `actif` from (`TBL_AFFECTATION` `c` join `TBL_USERS` `u`) where ((`u`.`uid` = `c`.`uid`) and ((`c`.`grade` = 'c') or (`c`.`grade` = 'theo')) and (`c`.`validated` is true)) group by `u`.`uid` union select `u`.`uid` AS `uid`,`u`.`nom` AS `nom`,`u`.`prenom` AS `prenom`,'cds' AS `classe`,min(`c`.`beginning`) AS `beginning`,max(`c`.`end`) AS `end`,`u`.`poids` AS `poids`,`u`.`actif` AS `actif` from (`TBL_AFFECTATION` `c` join `TBL_USERS` `u`) where ((`u`.`uid` = `c`.`uid`) and (`c`.`grade` = 'cds') and (`c`.`validated` is true)) group by `u`.`uid` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


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

LOCK TABLES `TBL_MENUS` WRITE;
/*!40000 ALTER TABLE `TBL_MENUS` DISABLE KEYS */;
INSERT INTO `TBL_MENUS` (`idx`, `titre`, `description`, `parent`, `creation`, `modification`, `allowed`, `actif`, `type`) VALUES (1,'principal','Menu principal',0,'0000-00-00 00:00:00','2012-10-14 12:14:25','all',1,'0'),(2,'Grille','Menu permettant d\'accéder aux différents affichages de la grille',1,'0000-00-00 00:00:00','2012-10-14 12:43:37','all',1,'0'),(3,'Décomptes','Décomptes de congés, repas...',1,'0000-00-00 00:00:00','2012-10-14 12:43:50','all',1,'0'),(4,'Administration','Accès aux pages d\'administration',1,'0000-00-00 00:00:00','2014-06-18 23:12:19','teamEdit',1,'0'),(5,'année congés','Accéder aux congés de différentes années',3,'2012-10-25 00:27:34','2012-10-25 08:06:11','all',1,NULL),(6,'calendrier','Gestion des calendriers vacances scolaires, briefings, période de  charge',4,'2012-10-25 10:05:17','2014-06-18 23:14:07','teamEdit',1,NULL),(7,'Planning','',0,'2012-10-25 12:27:48','2012-10-25 10:27:48','all',1,NULL),(8,'évènements','Les évènements spéciaux',3,'2012-11-04 01:28:08','2012-11-04 00:42:05','all',1,NULL),(9,'Gestion utilisateurs','Sous-menu de gestion des utilisateurs de TeamTime',4,'2013-07-10 23:37:06','2014-06-18 23:12:19','teamEdit',1,NULL),(10,'Maintenance','',4,'2013-11-12 18:25:07','2013-11-12 17:25:07','admin',1,NULL),(11,'utilisateur','',1,'2013-11-20 17:16:45','2013-11-20 16:16:45','all',1,NULL),(12,'Heures','Saisie des heures et configuration de la répartition des heures',4,'2013-12-20 14:32:42','2014-06-18 23:13:35','heures,editeurs',1,NULL),(13,'Gestion Équipe','Gestion de l\'équipe, des activités...',4,'2014-04-27 17:16:13','2014-04-27 15:16:13','editeurs',1,NULL);
/*!40000 ALTER TABLE `TBL_MENUS` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `TBL_ELEMS_MENUS` WRITE;
/*!40000 ALTER TABLE `TBL_ELEMS_MENUS` DISABLE KEYS */;
INSERT INTO `TBL_ELEMS_MENUS` (`idx`, `titre`, `description`, `lien`, `sousmenu`, `creation`, `modification`, `allowed`, `actif`) VALUES (1,'Planning','Planning annuel','planning_universel.html',7,'0000-00-00 00:00:00','2012-10-25 10:28:20','all',1),(2,'Grille','Affichage de la grille sous différents formats','affiche_grille.php?nbCycle=3',2,'0000-00-00 00:00:00','2013-06-05 17:02:49','all',1),(3,'Décomptes','Affichages des décomptes divers','tableauxCong.php',3,'0000-00-00 00:00:00','2012-10-13 23:29:33','all',1),(4,'Administration','Lien vers les pages d\'administration','',4,'0000-00-00 00:00:00','2014-06-18 23:03:05','teamEdit',1),(5,'logout','Déconnexion de l\'interface','logout.php',NULL,'0000-00-00 00:00:00','2012-10-13 23:29:33','all',1),(6,'Un cycle','Affichage d\'un cycle unique de la grille','affiche_grille.php',NULL,'0000-00-00 00:00:00','2013-12-21 11:56:56','all',1),(7,'Trois cycles','Affichage de trois cycles de la grille','affiche_grille.php?nbCycle=3',NULL,'0000-00-00 00:00:00','2013-06-05 17:06:24','all',1),(8,'Congés','','tableauxCong.php',5,'0000-00-00 00:00:00','2012-10-24 22:27:55','all',1),(9,'Évènements','','tableauxEvenSpec.php',8,'0000-00-00 00:00:00','2012-11-04 00:30:29','all',1),(10,'Mon compte','','monCompte.php',11,'0000-00-00 00:00:00','2013-12-20 13:57:19','all',1),(11,'Gestion utilisateurs','','creationCompte.php',NULL,'0000-00-00 00:00:00','2012-10-25 11:24:55','admin',1),(12,'Gestion calendrier','Saisir les dates de vacances scolaires et des périodes de charge','',6,'2012-10-14 15:04:11','2014-06-18 23:03:05','teamEdit',1),(13,'2014','','tableauxCong.php?year=2014',NULL,'0000-00-00 00:00:00','2014-06-28 14:08:38','all',1),(14,'2015','','tableauxCong.php?year=2015',NULL,'0000-00-00 00:00:00','2014-06-28 14:08:38','all',1),(15,'Titres édités','Liste des titres de congés déjà édités','litc.php',NULL,'2014-02-28 13:15:45','2014-06-18 23:05:18','teamEdit',1),(30,'Mise hors ligne','Mettre en et hors ligne le site','administration.php',NULL,'2012-10-25 01:43:27','2012-10-25 10:39:56','admin',1),(31,'Briefings','Ajout et modification des dates de briefings','gestion.php?q=briefing',NULL,'2012-10-25 10:08:32','2014-06-18 23:03:05','teamEdit',1),(32,'Période de charge','Ajout et modif des périodes de charge','gestion.php?q=charge',NULL,'2012-10-25 10:08:32','2014-06-18 23:03:05','teamEdit',1),(33,'Vacances scolaires','Ajout et modif des vacances scolaires','gestion.php?q=vacances',NULL,'2012-10-25 10:09:10','2014-06-18 23:03:05','teamEdit',1),(34,'Planning universel','','planning_universel.html',NULL,'2012-10-25 12:29:33','2012-10-25 10:37:37','all',0),(35,'Planning','','planning.php',NULL,'2012-10-25 12:29:33','2012-10-25 10:29:33','all',0),(36,'Situations répétitives','Ajoute des dispo sur de longue périodes','addMultipleDispoUser.php',NULL,'2013-04-21 16:08:28','2013-12-20 13:27:40','all',1),(37,'Saisie Heures','Saisie des heures','saisieHeures.php',NULL,'2013-05-10 09:23:01','2014-06-18 23:00:26','heures',1),(38,'Gestion utilisateur','Ajout et suppression d\'utilisateur','',9,'2013-06-30 09:50:26','2014-06-18 23:05:58','editeurs',1),(39,'Impersonate','Prendre la personnalité de quelqu\'un d\'autre','impersonate.php',NULL,'2013-07-10 23:14:15','2013-07-10 21:15:28','admin',1),(40,'gestion des utilisateurs','Ajoute, supprime des utilisateurs, affecte les droits...','gestionUtilisateur.php',NULL,'2013-08-22 18:46:07','2014-06-18 23:06:14','editeurs',1),(41,'Maintenance','','maintenance.php',10,'2013-11-12 18:25:07','2013-11-12 17:25:07','admin',1),(42,'update','Script de mise à jour','update.php',NULL,'2013-11-12 18:25:07','2013-11-12 17:25:58','admin',0),(43,'Maintenance DB','Vérification et réparation de la base de données','maintenance.php',NULL,'2013-11-12 18:25:07','2013-11-12 17:25:07','admin',1),(44,'Péréquations','Ajoute, supprime des péréquations aux utilisateurs','pereq.php',NULL,'2013-11-13 15:30:09','2014-06-18 23:03:05','teamEdit',1),(45,'Mes infos','information se rapportant à un compte utilisateur','monCompte.php',NULL,'2013-11-20 17:15:53','2013-12-20 13:54:40','all',1),(46,'Mon équipe','Annuaire des utlilsateurs','annuaire.php',NULL,'2013-11-20 17:20:30','2013-12-20 13:55:05','all',1),(47,'Distribution des heures','','distribHeures.php',NULL,'2013-12-18 14:46:55','2014-06-18 23:00:26','heures',1),(48,'Mes heures','','mesHeures.php',NULL,'2013-12-18 14:48:14','2013-12-18 13:48:14','all',1),(49,'Totaux heures','Liste les totaux des heures pour vérifier que la configuration de la répartiution est convenable','lesHeures.php',NULL,'2013-12-20 14:34:58','2014-06-18 23:00:26','heures',1),(50,'Heures','Gestion interne des heures (saisie, config et vérification)','',12,'2013-12-20 14:37:28','2014-06-18 23:00:26','heures',1),(51,'Ajout utilisateur','','ajoutUtilisateur.php',NULL,'2014-03-06 22:25:15','2014-03-06 21:25:15','admin',1),(52,'Gestion des rôles','','rolesUtilisateurs.php',NULL,'2014-03-06 22:25:15','2014-06-18 23:06:52','editeurs',1),(53,'Gestion Équipe','Gestion de l\'équipe, des activités...','',13,'2014-04-27 17:16:13','2014-04-27 15:16:13','editeurs',1),(54,'Ajout d\'activité','Ajoute des activités pour l\'équipe','activites.php',NULL,'2014-04-27 17:16:13','2014-04-27 15:16:13','editeurs',1);
/*!40000 ALTER TABLE `TBL_ELEMS_MENUS` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `TBL_MENUS_ELEMS_MENUS` WRITE;
/*!40000 ALTER TABLE `TBL_MENUS_ELEMS_MENUS` DISABLE KEYS */;
INSERT INTO `TBL_MENUS_ELEMS_MENUS` (`idxm`, `idxem`, `position`) VALUES (1,1,1),(1,2,2),(1,3,3),(1,4,4),(1,5,7),(1,10,5),(2,6,1),(2,7,2),(3,8,1),(3,9,2),(4,12,20),(4,30,100),(4,36,30),(4,38,40),(4,41,90),(4,50,10),(4,53,55),(5,13,1),(5,14,2),(5,15,3),(6,31,1),(6,32,2),(6,33,3),(7,34,2),(7,35,1),(8,16,2),(9,11,1),(9,39,15),(9,40,3),(9,44,5),(9,51,2),(9,52,4),(10,42,2),(10,43,1),(11,45,30),(11,46,20),(11,48,10),(12,37,10),(12,47,20),(12,49,30),(13,54,1);
/*!40000 ALTER TABLE `TBL_MENUS_ELEMS_MENUS` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `TBL_ARTICLES` WRITE;
/*!40000 ALTER TABLE `TBL_ARTICLES` DISABLE KEYS */;
INSERT INTO `TBL_ARTICLES` (`idx`, `titre`, `description`, `texte`, `analyse`, `creation`, `modification`, `restricted`, `actif`) VALUES (1,'Licence','','TeamTime est distribué sous licence AGPL v3.\r\nVous pouvez donc l\'utiliser librement, le copier, le modifier, le redistribuer à condition de respecter les termes de la licence.{br}{br}\r\nPour de plus amples informations, reportez-vous au site {lien:http://gnu.org/licenses/|http://gnu.org/licenses/} ou rapprochez-vous de l\'auteur.',1,'2012-08-27 03:07:53','2012-10-14 11:06:53',0,1);
/*!40000 ALTER TABLE `TBL_ARTICLES` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `TBL_RUBRIQUES` WRITE;
/*!40000 ALTER TABLE `TBL_RUBRIQUES` DISABLE KEYS */;
/*!40000 ALTER TABLE `TBL_RUBRIQUES` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `TBL_ARTICLES_RUBRIQUES` WRITE;
/*!40000 ALTER TABLE `TBL_ARTICLES_RUBRIQUES` DISABLE KEYS */;
/*!40000 ALTER TABLE `TBL_ARTICLES_RUBRIQUES` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


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

LOCK TABLES `TBL_CONSTANTS` WRITE;
/*!40000 ALTER TABLE `TBL_CONSTANTS` DISABLE KEYS */;
INSERT INTO `TBL_CONSTANTS` (`nom`, `valeur`, `type`, `commentaires`) VALUES ('effectif_mini','9','int','Effectif minimum requis pour ne pas avoir d\'alerte sur un nombre trop faible de présents sur une journée.'),('online','true','bool','');
/*!40000 ALTER TABLE `TBL_CONSTANTS` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

