<?php
// impersonate.php
//
// Page permettant à un admin de devenir quelqu'un d'autre

/*
	TeamTime is a software to manage people working in team on a cyclic shift.
	Copyright (C) 2012 Manioul - webmaster@teamtime.me

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as
	published by the Free Software Foundation, either version 3 of the
	License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Require authenticated user
// L'utilisateur doit être logué pour accéder à cette page
$requireVirtualAdmin = true;

ob_start(); // Obligatoire pour firePHP

/*
 * Configuration de la page
 * Définition des include nécessaires
 */
	$conf['page']['include']['constantes'] = 1; // Ce script nécessite la définition des constantes
	$conf['page']['include']['errors'] = 1; // le script gère les erreurs avec errors.inc.php
	$conf['page']['include']['class_debug'] = 1; // La classe debug est nécessaire à ce script
	$conf['page']['include']['globalConfig'] = 1; // Ce script nécessite config.inc.php
	$conf['page']['include']['init'] = 1; // la session est initialisée par init.inc.php
	$conf['page']['include']['globals_db'] = 1; // Le DSN de la connexion bdd est stockée dans globals_db.inc.php
	$conf['page']['include']['class_db'] = 1; // Le script utilise class_db.inc.php
	$conf['page']['include']['session'] = 1; // Le script utilise les sessions par session.imc
	$conf['page']['include']['classUtilisateur'] = NULL; // Le sript utilise uniquement la classe utilisateur (auquel cas, le fichier class_utilisateur.inc.php
	$conf['page']['include']['class_utilisateurGrille'] = 1; // Le sript utilise la classe utilisateurGrille
	$conf['page']['include']['class_cycle'] = 1; // La classe cycle est nécessaire à ce script (remplace grille.inc.php
	$conf['page']['include']['class_menu'] = 1; // La classe menu est nécessaire à ce script
	$conf['page']['include']['smarty'] = 1; // Smarty sera utilisé sur cette page
	$conf['page']['compact'] = false; // Compactage des scripts javascript et css
/*
 * Fin de la définition des include
 */


/*
 * Configuration de la page
 */
        $conf['page']['titre'] = "Impersonate"; // Le titre de la page
// Définit la valeur de $DEBUG pour le script
// on peut activer le debug sur des parties de script et/ou sur certains scripts :
// $DEBUG peut être activer dans certains scripts de required et désactivé dans d'autres
	$DEBUG = false;

	/*
	 * Choix des éléments à afficher
	 */
	
	// Affichage du menu horizontal
	$conf['page']['elements']['menuHorizontal'] = true;
	// Affichage messages
	$conf['page']['elements']['messages'] = true;
	// Affichage du choix du thème
	$conf['page']['elements']['choixTheme'] = false;
	// Affichage du menu d'administration
	$conf['page']['elements']['menuAdmin'] = false;
	
	// éléments de debug
	
	// FirePHP
	$conf['page']['elements']['firePHP'] = true;
	// Affichage des timeInfos
	$conf['page']['elements']['timeInfo'] = $DEBUG;
	// Affichage de l'utilisation mémoire
	$conf['page']['elements']['memUsage'] = $DEBUG;
	// Affichage des WherewereU
	$conf['page']['elements']['whereWereU'] = $DEBUG;
	// Affichage du lastError
	$conf['page']['elements']['lastError'] = $DEBUG;
	// Affichage du lastErrorMessage
	$conf['page']['elements']['lastErrorMessage'] = $DEBUG;
	// Affichage des messages de debug
	$conf['page']['elements']['debugMessages'] = $DEBUG;



	// Utilisation de jquery
	$conf['page']['javascript']['jquery'] = false;
	// Utilisation de ajax
	$conf['page']['javascript']['ajax'] = false;
	// Utilisation de grille2.js.php
	$conf['page']['javascript']['grille2'] = false;
	// Utilisation de grille2.js
	$conf['page']['javascript']['grille2js'] = false;

	// Feuilles de styles
	// Utilisation de la feuille de style general.css
	$conf['page']['stylesheet']['general'] = true;
	$conf['page']['stylesheet']['grille'] = false;

	// Compactage des pages
	$conf['page']['compact'] = false;
	
/*
 * Fin de la configuration de la page
 */

require 'required_files.inc.php';

if (!array_key_exists('uid', $_POST) && !array_key_exists('iWantMyselfBack', $_GET)) {
	// On crée un formulaire pour sélectionner la personnalité à prendre
	$result = $_SESSION['db']->db_interroge("SELECT `uid`, `nom` FROM `TBL_USERS` ORDER BY `nom`");
	$users = array();
	while ($row = $_SESSION['db']->db_fetch_row($result)) {
		$users[$row[0]] = $row[1];
	}
	mysqli_free_result($result);
	$smarty->assign('users', $users);
	$smarty->display('impersonate.tpl');
} elseif (array_key_exists('uid', $_POST)) { // On prend la personnalité de l'uid passé par le formulaire
	// Nettoie la variable de session
	unset($_SESSION['ADMIN']);
	unset($_SESSION['EDITEURS']);
	printf ("Vous êtes %s et vous devenez %d avec un virtuel %d", $_SESSION['utilisateur']->login(), $_POST['uid'], $_SESSION['utilisateur']->uid());
	// Attribue une valeur spéciale pour savoir que l'on est devenu un utilisateur virtuel
	$_SESSION['iAmVirtual'] = $_SESSION['utilisateur']->uid();
	$sql = sprintf("
		SELECT * FROM `TBL_USERS` AS `TU`
		, `TBL_AFFECTATION` AS `TA`
		WHERE `TA`.`uid` = `TU`.`uid`
		AND `TU`.`uid` = '%s'
		", $_SESSION['db']->db_real_escape_string($_POST['uid'])
	);
	$result = $_SESSION['db']->db_interroge($sql);
	if (mysqli_num_rows($result) > 0) {
		session_regenerate_id(); // Éviter les attaques par fixation de session
		$row = $_SESSION['db']->db_fetch_assoc($result);
		$row['sha1'] = NULL; // Le sha1 n'a pas vocation à apparaître
		$_SESSION['utilisateur'] = new utilisateurGrille($row);
		// Mise à jour des informations de connexion
		$upd = sprintf("UPDATE `TBL_USERS` SET `lastlogin` = NOW(), `nblogin` = %s WHERE `login` = '%s'", $row['nblogin'] + 1, $row['login']);
		$_SESSION['db']->db_interroge($upd);
		$sql = sprintf("SELECT `groupe` FROM `TBL_GROUPS` WHERE `gid` >= '%s'", $row['gid']);
		$result2 = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_array($result2)) {
			$_SESSION[strtoupper($row[0])] = true;
		}
		mysqli_free_result($result2);
	}
	mysqli_free_result($result);
} elseif (array_key_exists('iWantMyselfBack', $_GET) && array_key_exists('iAmVirtual', $_SESSION)) { // On reprend sa personnalité
	$sql = sprintf("
		SELECT * FROM `TBL_USERS` AS `TU`
		, `TBL_AFFECTATION` AS `TA`
		WHERE `TA`.`uid` = `TU`.`uid`
		AND `TU`.`uid` = '%s'
		", $_SESSION['db']->db_real_escape_string($_SESSION['iAmVirtual'])
	);
	unset($_SESSION['iAmVirtual']);
	$result = $_SESSION['db']->db_interroge($sql);
	if (mysqli_num_rows($result) > 0) {
		session_regenerate_id(); // Éviter les attaques par fixation de session
		$row = $_SESSION['db']->db_fetch_assoc($result);
		$row['sha1'] = NULL; // Le sha1 n'a pas vocation à apparaître
		$_SESSION['utilisateur'] = new utilisateurGrille($row);
		$_SESSION['AUTHENTICATED'] = true;
		$sql = sprintf("SELECT `groupe` FROM `TBL_GROUPS` WHERE `gid` >= '%s'", $row['gid']);
		$result2 = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_array($result2)) {
			$_SESSION[strtoupper($row[0])] = true;
		}
		mysqli_free_result($result2);
	}
}

/*
 * Informations de debug
 */
include 'debug.inc.php';
firePhpLog($conf, '$conf');
firePhpLog(debug::getInstance()->format(), 'format debug messages');
firePhpLog($javascript, '$javascript');
firePhpLog($stylesheet, '$stylesheet');

// Affichage du bas de page
$smarty->display('footer.tpl');

ob_end_flush(); // Obligatoire pour firePHP

?>
