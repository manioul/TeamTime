
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
INSERT INTO `TBL_ELEMS_MENUS` (`idx`, `titre`, `description`, `lien`, `sousmenu`, `creation`, `modification`, `allowed`, `actif`) VALUES (1,'Planning','Planning annuel','planning_universel.html',7,'0000-00-00 00:00:00','2012-10-25 10:28:20','all',1),(2,'Grille','Affichage de la grille sous différents formats','affiche_grille.php?nbCycle=3',2,'0000-00-00 00:00:00','2013-06-05 17:02:49','all',1),(3,'Décomptes','Affichages des décomptes divers','tableauxCong.php',3,'0000-00-00 00:00:00','2012-10-13 23:29:33','all',1),(4,'Administration','Lien vers les pages d\'administration','',4,'0000-00-00 00:00:00','2014-06-18 23:03:05','teamEdit',1),(5,'logout','Déconnexion de l\'interface','logout.php',NULL,'0000-00-00 00:00:00','2012-10-13 23:29:33','all',1),(6,'Un cycle','Affichage d\'un cycle unique de la grille','affiche_grille.php',NULL,'0000-00-00 00:00:00','2013-12-21 11:56:56','all',1),(7,'Trois cycles','Affichage de trois cycles de la grille','affiche_grille.php?nbCycle=3',NULL,'0000-00-00 00:00:00','2013-06-05 17:06:24','all',1),(8,'Congés','','tableauxCong.php',5,'0000-00-00 00:00:00','2012-10-24 22:27:55','all',1),(9,'Évènements','','tableauxEvenSpec.php',8,'0000-00-00 00:00:00','2012-11-04 00:30:29','all',1),(10,'Mon compte','','monCompte.php',11,'0000-00-00 00:00:00','2013-12-20 13:57:19','all',1),(11,'Gestion utilisateurs','','creationCompte.php',NULL,'0000-00-00 00:00:00','2012-10-25 11:24:55','admin',1),(12,'Gestion calendrier','Saisir les dates de vacances scolaires et des périodes de charge','',6,'2012-10-14 15:04:11','2014-06-18 23:03:05','teamEdit',1),(13,'2014','','tableauxCong.php?year=2014',NULL,'0000-00-00 00:00:00','2014-06-20 18:36:22','all',1),(14,'2015','','tableauxCong.php?year=2015',NULL,'0000-00-00 00:00:00','2014-06-20 18:36:22','all',1),(15,'Titres édités','Liste des titres de congés déjà édités','litc.php',NULL,'2014-02-28 13:15:45','2014-06-18 23:05:18','teamEdit',1),(30,'Mise hors ligne','Mettre en et hors ligne le site','administration.php',NULL,'2012-10-25 01:43:27','2012-10-25 10:39:56','admin',1),(31,'Briefings','Ajout et modification des dates de briefings','gestion.php?q=briefing',NULL,'2012-10-25 10:08:32','2014-06-18 23:03:05','teamEdit',1),(32,'Période de charge','Ajout et modif des périodes de charge','gestion.php?q=charge',NULL,'2012-10-25 10:08:32','2014-06-18 23:03:05','teamEdit',1),(33,'Vacances scolaires','Ajout et modif des vacances scolaires','gestion.php?q=vacances',NULL,'2012-10-25 10:09:10','2014-06-18 23:03:05','teamEdit',1),(34,'Planning universel','','planning_universel.html',NULL,'2012-10-25 12:29:33','2012-10-25 10:37:37','all',0),(35,'Planning','','planning.php',NULL,'2012-10-25 12:29:33','2012-10-25 10:29:33','all',0),(36,'Situations répétitives','Ajoute des dispo sur de longue périodes','addMultipleDispoUser.php',NULL,'2013-04-21 16:08:28','2013-12-20 13:27:40','all',1),(37,'Saisie Heures','Saisie des heures','saisieHeures.php',NULL,'2013-05-10 09:23:01','2014-06-18 23:00:26','heures',1),(38,'Gestion utilisateur','Ajout et suppression d\'utilisateur','',9,'2013-06-30 09:50:26','2014-06-18 23:05:58','editeurs',1),(39,'Impersonate','Prendre la personnalité de quelqu\'un d\'autre','impersonate.php',NULL,'2013-07-10 23:14:15','2013-07-10 21:15:28','admin',1),(40,'gestion des utilisateurs','Ajoute, supprime des utilisateurs, affecte les droits...','gestionUtilisateur.php',NULL,'2013-08-22 18:46:07','2014-06-18 23:06:14','editeurs',1),(41,'Maintenance','','maintenance.php',10,'2013-11-12 18:25:07','2013-11-12 17:25:07','admin',1),(42,'update','Script de mise à jour','update.php',NULL,'2013-11-12 18:25:07','2013-11-12 17:25:58','admin',0),(43,'Maintenance DB','Vérification et réparation de la base de données','maintenance.php',NULL,'2013-11-12 18:25:07','2013-11-12 17:25:07','admin',1),(44,'Péréquations','Ajoute, supprime des péréquations aux utilisateurs','pereq.php',NULL,'2013-11-13 15:30:09','2014-06-18 23:03:05','teamEdit',1),(45,'Mes infos','information se rapportant à un compte utilisateur','monCompte.php',NULL,'2013-11-20 17:15:53','2013-12-20 13:54:40','all',1),(46,'Mon équipe','Annuaire des utlilsateurs','annuaire.php',NULL,'2013-11-20 17:20:30','2013-12-20 13:55:05','all',1),(47,'Distribution des heures','','distribHeures.php',NULL,'2013-12-18 14:46:55','2014-06-18 23:00:26','heures',1),(48,'Mes heures','','mesHeures.php',NULL,'2013-12-18 14:48:14','2013-12-18 13:48:14','all',1),(49,'Totaux heures','Liste les totaux des heures pour vérifier que la configuration de la répartiution est convenable','lesHeures.php',NULL,'2013-12-20 14:34:58','2014-06-18 23:00:26','heures',1),(50,'Heures','Gestion interne des heures (saisie, config et vérification)','',12,'2013-12-20 14:37:28','2014-06-18 23:00:26','heures',1),(51,'Ajout utilisateur','','ajoutUtilisateur.php',NULL,'2014-03-06 22:25:15','2014-03-06 21:25:15','admin',1),(52,'Gestion des rôles','','rolesUtilisateurs.php',NULL,'2014-03-06 22:25:15','2014-06-18 23:06:52','editeurs',1),(53,'Gestion Équipe','Gestion de l\'équipe, des activités...','',13,'2014-04-27 17:16:13','2014-04-27 15:16:13','editeurs',1),(54,'Ajout d\'activité','Ajoute des activités pour l\'équipe','activites.php',NULL,'2014-04-27 17:16:13','2014-04-27 15:16:13','editeurs',1);
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

