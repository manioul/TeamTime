-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Jeu 02 Mai 2013 à 15:16
-- Version du serveur: 5.1.66
-- Version de PHP: 5.3.3-7+squeeze15

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `ttm`
--

-- --------------------------------------------------------

--
-- Structure de la table `TBL_ARTICLES`
--

CREATE TABLE IF NOT EXISTS `TBL_ARTICLES` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_ARTICLES_RUBRIQUES`
--

CREATE TABLE IF NOT EXISTS `TBL_ARTICLES_RUBRIQUES` (
  `idxa` int(11) NOT NULL,
  `idxu` int(11) NOT NULL,
  PRIMARY KEY (`idxa`,`idxu`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_BRIEFING`
--

CREATE TABLE IF NOT EXISTS `TBL_BRIEFING` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `dateD` date NOT NULL COMMENT 'date de début',
  `dateF` date NOT NULL COMMENT 'date de fin',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_CONSTANTS`
--

CREATE TABLE IF NOT EXISTS `TBL_CONSTANTS` (
  `nom` varchar(255) NOT NULL,
  `valeur` text NOT NULL,
  `type` enum('int','float','string','bool') NOT NULL,
  `commentaires` text NOT NULL,
  PRIMARY KEY (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_CYCLE`
--

CREATE TABLE IF NOT EXISTS `TBL_CYCLE` (
  `cid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `vacation` varchar(8) NOT NULL,
  `horaires` varchar(10) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Description du cycle de travail';

-- --------------------------------------------------------

--
-- Structure de la table `TBL_DECOMPTE_HEURES`
--

CREATE TABLE IF NOT EXISTS `TBL_DECOMPTE_HEURES` (
  `date` date NOT NULL,
  `uid` tinyint(4) NOT NULL,
  `heure` float NOT NULL,
  PRIMARY KEY (`date`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_DISPO`
--

CREATE TABLE IF NOT EXISTS `TBL_DISPO` (
  `did` tinyint(4) NOT NULL AUTO_INCREMENT,
  `dispo` varchar(16) NOT NULL,
  `Jours possibles` varchar(20) NOT NULL COMMENT 'Liste des jours possibles pour la présence définie',
  `classes possibles` varchar(32) NOT NULL DEFAULT 'all' COMMENT 'la classe d''utilisateurs pour laquelle la dispo est possible',
  `peut poser` varchar(1024) NOT NULL DEFAULT 'all' COMMENT 'Liste des personnes ou classes qui peuvent poser cette dispo (all, classes, ou login)',
  `poids` tinyint(4) NOT NULL COMMENT 'Le poids de la dispo permet de définir la place dans le menu',
  `absence` tinyint(1) NOT NULL COMMENT 'Indique si la dispo correspond à une absence (true) ou pas',
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `type decompte` enum('dispo','premiere','seconde','dodo','repas nuit','repas s2','conges') NOT NULL,
  `need_compteur` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Définit si cette dispo doit être comptabilisée dans la table vacances',
  `quantity` int(11) NOT NULL COMMENT 'Quantité de cette dispo autorisée annuellement (need_compteur = TRUE)',
  `date limite depot` varchar(5) DEFAULT NULL COMMENT 'Date limite de dépôt du congé',
  `nom_long` varchar(45) NOT NULL,
  PRIMARY KEY (`did`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_ELEMS_MENUS`
--

CREATE TABLE IF NOT EXISTS `TBL_ELEMS_MENUS` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_EVENEMENTS_SPECIAUX`
--

CREATE TABLE IF NOT EXISTS `TBL_EVENEMENTS_SPECIAUX` (
  `teid` int(11) NOT NULL AUTO_INCREMENT,
  `did` tinyint(4) NOT NULL,
  `uid` tinyint(4) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`teid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_GRILLE`
--

CREATE TABLE IF NOT EXISTS `TBL_GRILLE` (
  `date` date NOT NULL,
  `cid` tinyint(4) NOT NULL,
  `grilleId` int(11) NOT NULL COMMENT 'Le numéro de la grille à laquelle appartient ce jour de travail',
  `conf` enum('E','W') NOT NULL,
  `pcid` smallint(6) NOT NULL,
  `vsid` smallint(6) NOT NULL,
  `briefing` varchar(40) DEFAULT NULL,
  `readOnly` tinyint(1) NOT NULL DEFAULT '0',
  `ferie` tinyint(1) NOT NULL COMMENT 'true si le jour doit être considéré comme férié',
  PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_GROUPS`
--

CREATE TABLE IF NOT EXISTS `TBL_GROUPS` (
  `gid` tinyint(4) NOT NULL COMMENT 'Plus gid est petit, plus les droits sont importants',
  `groupe` varchar(16) NOT NULL,
  `description` mediumtext NOT NULL,
  PRIMARY KEY (`groupe`),
  UNIQUE KEY `gid` (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_LOG`
--

CREATE TABLE IF NOT EXISTS `TBL_LOG` (
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

-- --------------------------------------------------------

--
-- Structure de la table `TBL_L_SHIFT_DISPO`
--

CREATE TABLE IF NOT EXISTS `TBL_L_SHIFT_DISPO` (
  `sdid` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'identifiant unique de l''occupation',
  `date` date NOT NULL,
  `uid` tinyint(4) NOT NULL,
  `did` tinyint(4) NOT NULL,
  PRIMARY KEY (`sdid`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_MENUS`
--

CREATE TABLE IF NOT EXISTS `TBL_MENUS` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_MENUS_ELEMS_MENUS`
--

CREATE TABLE IF NOT EXISTS `TBL_MENUS_ELEMS_MENUS` (
  `idxm` int(11) NOT NULL COMMENT 'index du menu',
  `idxem` int(11) NOT NULL,
  `position` tinyint(4) NOT NULL,
  PRIMARY KEY (`idxm`,`idxem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_PERIODE_CHARGE`
--

CREATE TABLE IF NOT EXISTS `TBL_PERIODE_CHARGE` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `dateD` date NOT NULL COMMENT 'date de début',
  `dateF` date NOT NULL COMMENT 'date de fin',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_REMPLA`
--

CREATE TABLE IF NOT EXISTS `TBL_REMPLA` (
  `uid` tinyint(4) NOT NULL,
  `date` date NOT NULL,
  `nom` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_RUBRIQUES`
--

CREATE TABLE IF NOT EXISTS `TBL_RUBRIQUES` (
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

-- --------------------------------------------------------

--
-- Structure de la table `TBL_USERS`
--

CREATE TABLE IF NOT EXISTS `TBL_USERS` (
  `uid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `nom` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `prenom` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `classe` set('admin','cds','ce','pc','c','dtch','fmp') COLLATE utf8_unicode_ci NOT NULL,
  `date arrivee` date NOT NULL,
  `date theorique` date NOT NULL,
  `date pc` date NOT NULL,
  `date ce` date NOT NULL,
  `date cds` date NOT NULL,
  `date vismed` date NOT NULL,
  `login` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `sha1` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `lastlogin` date NOT NULL,
  `nblogin` int(11) NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `gid` tinyint(4) NOT NULL DEFAULT '2',
  `poids` smallint(6) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `showtipoftheday` tinyint(1) NOT NULL DEFAULT '1',
  `page` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'affiche_grille.php' COMMENT 'La page affichée après la connexion d''un utilisateur',
  PRIMARY KEY (`uid`),
  KEY `poids` (`poids`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_VACANCES`
--

CREATE TABLE IF NOT EXISTS `TBL_VACANCES` (
  `vid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` tinyint(4) NOT NULL COMMENT 'L''id utilisateur',
  `date` date NOT NULL COMMENT 'la date du congé',
  `did` tinyint(4) NOT NULL COMMENT 'l''id de la dispo correspondant au congé',
  `etat` tinyint(4) NOT NULL COMMENT 'Définit un état incrémental du congé. L''état standard a pour valeur 0, filed a pour valeur 1 et confirmed a pour valeur 2',
  `year` year(4) NOT NULL COMMENT 'L''année sur laquelle sera décompté le congé',
  PRIMARY KEY (`vid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `TBL_VACANCES_SCOLAIRES`
--

CREATE TABLE IF NOT EXISTS `TBL_VACANCES_SCOLAIRES` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `dateD` date NOT NULL COMMENT 'date de début',
  `dateF` date NOT NULL COMMENT 'date de fin',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


INSERT INTO `TBL_ARTICLES` (`idx`, `titre`, `description`, `texte`, `analyse`, `creation`, `modification`, `restricted`, `actif`) VALUES
(1, 'Licence', '', 'TeamTime est distribué sous licence AGPL v3.\r\nVous pouvez donc l''utiliser librement, le copier, le modifier, le redistribuer à condition de respecter les termes de la licence.{br}{br}\r\nPour de plus amples informations, reportez-vous au site {lien:http://gnu.org/licenses/|http://gnu.org/licenses/} ou rapprochez-vous de l''auteur.', 1, '2012-08-27 03:07:53', '2012-10-14 13:06:53', 0, 1),
(2, 'Source', '', 'Le code source de TeamTime est disponible sous license AGPL v3.{br}Vous pouvez le retrouver sur {lien: https://lab.manioul.org/trac|https://lab.manioul.org/trac}', 1, '2013-04-19 14:52:02', '2013-04-19 15:00:43', 0, 1);

INSERT INTO `TBL_CONSTANTS` (`nom`, `valeur`, `type`, `commentaires`) VALUES
('online', 'true', 'bool', '');

INSERT INTO `TBL_GROUPS` (`gid`, `groupe`, `description`) VALUES
(0, 'admin', 'Administrateurs du site'),
(1, 'editeurs', 'Éditeurs de la grille - habilités à modifier librement la grille.'),
(2, 'my_edit', 'Éditeurs habilités à ne changer que leurs dispo');

INSERT INTO `TBL_ELEMS_MENUS` (`idx`, `titre`, `description`, `lien`, `sousmenu`, `creation`, `modification`, `allowed`, `actif`) VALUES
(1, 'Planning', 'Planning annuel', 'planning_universel.html', 7, '0000-00-00 00:00:00', '2012-10-25 12:28:20', 'all', 1),
(2, 'Grille', 'Affichage de la grille sous différents formats', 'affiche_grille_multiple.php', 2, '0000-00-00 00:00:00', '2012-10-14 14:21:05', 'all', 1),
(3, 'Décomptes', 'Affichages des décomptes divers', 'tableauxCong.php', 3, '0000-00-00 00:00:00', '2012-10-14 01:29:33', 'all', 1),
(4, 'Administration', 'Lien vers les pages d''administration', '', 4, '0000-00-00 00:00:00', '2012-10-25 12:22:58', 'cds ce', 1),
(5, 'logout', 'Déconnexion de l''interface', 'logout.php', NULL, '0000-00-00 00:00:00', '2012-10-14 01:29:33', 'all', 1),
(6, 'Cycle unique', 'Affichage d''un cycle unique de la grille', 'affiche_grille.php', NULL, '0000-00-00 00:00:00', '2012-10-14 01:29:33', 'all', 1),
(7, 'Trois cycles', 'Affichage de trois cycles de la grille', 'affiche_grille_multiple.php', NULL, '0000-00-00 00:00:00', '2012-10-14 14:58:42', 'all', 1),
(8, 'Congés', '', 'tableauxCong.php', 5, '0000-00-00 00:00:00', '2012-10-25 00:27:55', 'all', 1),
(9, 'Évènements', '', 'tableauxEvenSpec.php', 8, '0000-00-00 00:00:00', '2012-11-04 01:30:29', 'all', 1),
(10, 'Annuaire', '', 'annuaire.php', NULL, '0000-00-00 00:00:00', '2012-10-14 01:29:33', 'all', 1),
(11, 'Gestion utilisateurs', '', 'creationCompte.php', NULL, '0000-00-00 00:00:00', '2012-10-25 13:24:55', 'admin', 1),
(12, 'Gestion calendrier', 'Saisir les dates de vacances scolaires et des périodes de charge', '', 6, '2012-10-14 15:04:11', '2012-12-30 21:42:05', 'cds', 1),
(13, '2013', '', 'tableauxCong.php?year=2013', NULL, '0000-00-00 00:00:00', '2013-05-02 14:42:26', 'all', 1),
(14, '2014', '', 'tableauxCong.php?year=2014', NULL, '0000-00-00 00:00:00', '2013-05-02 14:42:26', 'all', 1),
(15, '2013', '', 'tableauxEvenSpec.php?year=2013', NULL, '0000-00-00 00:00:00', '2013-05-02 14:42:26', 'all', 1),
(16, '2014', '', 'tableauxEvenSpec.php?year=2014', NULL, '0000-00-00 00:00:00', '2013-05-02 14:42:26', 'all', 1),
(30, 'Mise hors ligne', 'Mettre en et hors ligne le site', 'administration.php', NULL, '2012-10-25 01:43:27', '2012-10-25 12:39:56', 'admin', 1),
(31, 'Briefings', 'Ajout et modification des dates de briefings', 'gestion.php?q=briefing', NULL, '2012-10-25 10:08:32', '2012-12-30 21:42:05', 'cds ce', 1),
(32, 'Période de charge', 'Ajout et modif des périodes de charge', 'gestion.php?q=charge', NULL, '2012-10-25 10:08:32', '2012-12-30 21:42:05', 'cds', 1),
(33, 'Vacances scolaires', 'Ajout et modif des vacances scolaires', 'gestion.php?q=vacances', NULL, '2012-10-25 10:09:10', '2012-12-30 21:42:05', 'cds', 1),
(34, 'Planning universel', '', 'planning_universel.html', NULL, '2012-10-25 12:29:33', '2012-10-25 12:37:37', 'all', 0),
(35, 'Planning', '', 'planning.php', NULL, '2012-10-25 12:29:33', '2012-10-25 12:29:33', 'all', 0),
(36, 'Dispo étendues', 'Ajoute des dispo sur de longue périodes', 'addMultipleDispoUser.php', NULL, '2013-04-21 16:08:28', '2013-04-21 15:58:41', 'all', 1);

INSERT INTO `TBL_MENUS_ELEMS_MENUS` (`idxm`, `idxem`, `position`) VALUES
(1, 1, 1),
(1, 2, 2),
(1, 3, 3),
(1, 4, 4),
(1, 5, 6),
(1, 10, 5),
(2, 6, 1),
(2, 7, 2),
(3, 8, 1),
(3, 9, 2),
(4, 11, 1),
(4, 12, 2),
(4, 30, 4),
(4, 36, 3),
(5, 13, 1),
(5, 14, 2),
(6, 31, 1),
(6, 32, 2),
(6, 33, 3),
(7, 34, 2),
(7, 35, 1),
(8, 15, 1),
(8, 16, 2);

INSERT INTO `TBL_MENUS` (`idx`, `titre`, `description`, `parent`, `creation`, `modification`, `allowed`, `actif`, `type`) VALUES
(1, 'principal', 'Menu principal', 0, '0000-00-00 00:00:00', '2012-10-14 14:14:25', 'all', 1, '0'),
(2, 'Grille', 'Menu permettant d''accéder aux différents affichages de la grille', 1, '0000-00-00 00:00:00', '2012-10-14 14:43:37', 'all', 1, '0'),
(3, 'Décomptes', 'Décomptes de congés, repas...', 1, '0000-00-00 00:00:00', '2012-10-14 14:43:50', 'all', 1, '0'),
(4, 'Administration', 'Accès aux pages d''administration', 1, '0000-00-00 00:00:00', '2012-10-14 14:43:50', 'cds', 1, '0'),
(5, 'année congés', 'Accéder aux congés de différentes années', 3, '2012-10-25 00:27:34', '2012-10-25 10:06:11', 'all', 1, NULL),
(6, 'calendrier', 'Gestion des calendriers vacances scolaires, briefings, période de  charge', 4, '2012-10-25 10:05:17', '2012-10-25 10:06:11', 'cds', 1, NULL),
(7, 'Planning', '', 0, '2012-10-25 12:27:48', '2012-10-25 12:27:48', 'all', 1, NULL),
(8, 'évènements', 'Les évènements spéciaux', 3, '2012-11-04 01:28:08', '2012-11-04 01:42:05', 'all', 1, NULL);
