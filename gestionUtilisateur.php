<?php
/* gestionUtilisateur.php
 *
 * Page permetta,t de gérer les informations d'un utilisateur
 *
 */

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

$requireEditeur = true; // L'utilisateur doit être admin pour accéder à cette page

/*
 * INCLUDES
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


/*
 * Configuration de la page
 */
        $conf['page']['titre'] = "Gestion utilisateurs"; // Le titre de la page
// Définit la valeur de $DEBUG pour le script
// on peut activer le debug sur des parties de script et/ou sur certains scripts :
// $DEBUG peut être activer dans certains scripts de required et désactivé dans d'autres
	$DEBUG = true;
	$conf['page']['elements']['firePHP'] = true;

	/*
	 * Choix des éléments à afficher
	 */
	
	// Affichage du menu horizontal
	$conf['page']['elements']['menuHorizontal'] = true;
	// Affichage messages
	$conf['page']['elements']['messages'] = false;
	// Affichage du choix du thème
	$conf['page']['elements']['choixTheme'] = false;
	// Affichage du menu d'administration
	$conf['page']['elements']['menuAdmin'] = false;
	
	// éléments de debug
	
	// Affichage des timeInfos
	$conf['page']['elements']['timeInfo'] = true;
	// Affichage de l'utilisation mémoire
	$conf['page']['elements']['memUsage'] = true;
	// Affichage des WherewereU
	$conf['page']['elements']['whereWereU'] = true;
	// Affichage du lastError
	$conf['page']['elements']['lastError'] = true;
	// Affichage du lastErrorMessage
	$conf['page']['elements']['lastErrorMessage'] = true;
	// Affichage des messages de debug
	$conf['page']['elements']['debugMessages'] = true;


	// Gestion des briefings
	$conf['page']['elements']['formulaireBriefing'] = false;

	// Utilisation de jquery
	$conf['page']['javascript']['jquery'] = true;
	// Utilisation de ajax
	$conf['page']['javascript']['ajax'] = false;
	// Utilisation de grille2.js.php
	$conf['page']['javascript']['grille2'] = false;
	// Utilisation de online
	$conf['page']['javascript']['online'] = true;
	// Gestion des utilisateurs
	$conf['page']['javascript']['gestionUtilisateurs'] = true;

	// Feuilles de styles
	// Utilisation de la feuille de style general.css
	$conf['page']['stylesheet']['general'] = true;
	// Utilisation de la feuille de style online.css
	$conf['page']['stylesheet']['online'] = true;

	// Compactage des pages
	$conf['page']['compact'] = false;
/*
 * Fin de la configuration de la page
 */

require 'required_files.inc.php';

$active = 1;
$affectations = array();
$centre = $_SESSION['utilisateur']->centre();
$team = $_SESSION['utilisateur']->team();
$from = date('Y-m-d');
$to = (isset($_GET['all'])) ? -1 : $from;

if ($_SESSION['ADMIN']) {
	$affectations['all']['all'] = 1; // Permet de sélectionner tous les utilisateurs
	$result = $_SESSION['db']->db_interroge("
		SELECT `centre`,
		`team`
		FROM `TBL_AFFECTATION`
		GROUP BY `centre`, `team`");
	while ($row = $_SESSION['db']->db_fetch_row($result)) {
		$affectations[$row[0]]['all'] = 1; // Permet de sélectionner toutes les équipes d'un même centre
		$affectations[$row[0]][$row[1]] = 1;
	}
	mysqli_free_result($result);

	if (isset($_GET['filter'])) {
		$a = explode("-", $_GET['filter']);
		$centre = $a[0];
		$team = $a[1];
	}
	if (isset($_GET['centre'])) $centre = $_SESSION['db']->db_real_escape_string($_GET['centre']);
	if (isset($_GET['team'])) $team = $_SESSION['db']->db_real_escape_string($_GET['team']);
	$active = (isset($_GET['active'])) ? (int) $_GET['active'] : NULL;
}

$users = utilisateursDeLaGrille::getInstance()->getUsersFromTo($from, $to, $centre, $team, (int) $active);
$usersInfos = array();

$result = $_SESSION['db']->db_interroge("SHOW COLUMNS FROM `TBL_USERS`");
while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
	if ($row['Field'] == 'sha1') $row['Field'] = 'password';
	$usersInfos[0][$row['Field']] = $row['Field'];
	$i = 1;
	foreach ($users as $user) {
		if ('uid' == $row['Field']) { // Ajoute des liens dans la colonne uid
			$usersInfos[$i][$row['Field']] = $user->$row['Field']() . '&nbsp;<a href="">+</a>&nbsp;<a href="">-</a>';
		} elseif ('password' == $row['Field']) { // Remplis le mot de passe avec des *
			$usersInfos[$i][$row['Field']] = "*****";
		} elseif (method_exists('utilisateurGrille', $row['Field'])) {
			$usersInfos[$i][$row['Field']] = $user->$row['Field']();
		} else {
			$usersInfos[$i][$row['Field']] = 'Unknown';
		}
		$i++;
	}
}
mysqli_free_result($result);

if ($_SESSION['ADMIN']) {
	$usersInfos[0]['centre'] = "Centre";
	$usersInfos[0]['team'] = htmlspecialchars("Équipe", ENT_COMPAT, 'utf-8');;
	$i = 1;
	foreach ($users as $user) {
		$usersInfos[$i]['centre'] = $user->centre();
		$usersInfos[$i]['team'] = $user->team();
		$i++;
	}
}
$smarty->assign('affectations', $affectations);

$smarty->assign('usersInfos', $usersInfos);

$smarty->display('gestionUtilisateurs.tpl');
/*
 * Informations de debug
 */
include 'debug.inc.php';

// Affichage du bas de page
$smarty->display('footer.tpl');

?>
