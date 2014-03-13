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

ob_start(); // Obligatoire pour firePHP

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
	$conf['page']['include']['smarty'] = (empty($_POST['nom']) ? 1 : 0); // Smarty sera utilisé sur cette page


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

if (empty($_POST['nom'])) { // On vérifie que des données de formulaire n'ont pas été envoyées
	if (isset($_SESSION['ADMIN'])) {
		if (isset($_GET['centre'])) {
			$centre = $_GET['centre'];
		} else {
			$centre = NULL;
		}
		if (isset($_GET['team'])) {
			$team = $_GET['team'];
		} else {
			$team = NULL;
		}
	} else {
		$centre = $_SESSION['utilisateur']->centre();
		$team = $_SESSION['utilisateur']->team();
	}
	$users = utilisateursDeLaGrille::getInstance()->getActiveUsersFromTo(date('Y-m-d'), date('Y-m-d'), $centre, $team);
	$usersInfos = array();
	$form = array();
	$header = array();
}
// Recherche les champs de la table des utilisateurs
$j = 0;
$result = $_SESSION['db']->db_interroge("SHOW COLUMNS FROM `TBL_USERS`");
while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
	if ($row['Field'] == 'sha1') $row['Field'] = 'password';
	$usersInfos[0][$row['Field']] = $row['Field'];
	if ($row['Extra'] == 'auto_increment' || $row['Field'] == 'nblogin' || $row['Field'] == 'lastlogin') {
		// Ce champ ne sera pas saisi par l'utilisateur
	} else {
		$row['width'] = -1;
		if (preg_match('/\((\d*)\)/', $row['Type'], $match) == 1) {
			if ($match[1] > 1) {
				$row['width'] = ($match[1] < 10) ? $match[1] : 10;
				$row['maxlength'] = $match[1];
			}
		}
		if (preg_match('/int\((\d*)\)/', $row['Type'], $match)) {
			if ($match[1] == 1) {
				$row['type'] = "checkbox";
				$row['value'] = 1;
			} else {
				$row['type'] = "text";
			}
		} elseif ($row['Field'] == 'email') {
			$row['type'] = 'email';
		} elseif ($row['Field'] == 'password') {
			$row['type'] = 'password';
		} elseif ($row['Type'] == 'date') {
			$row['type'] = 'date';
			$row['maxlength'] = 10;
			$row['width'] = 6;
		} else {
			$row['type'] = 'text';
		}
		$header[$j] = $row['Field'];
		$form[$j++] = $row;
	}
	if (empty($_POST['nom'])) {
		// Construit les données pour le tableau des utilisateurs
		$i = 1;
		foreach ($users as $user) {
			if ('password' == $row['Field']) { // Remplis le mot de passe avec des *
				$usersInfos[$i][$row['Field']] = "*****";
			} elseif (is_a($user->$row['Field'](), 'Date')) {
				$usersInfos[$i][$row['Field']] = $user->$row['Field']()->formatDate('fr');
			} elseif (method_exists('utilisateurGrille', $row['Field'])) {
				$usersInfos[$i][$row['Field']] = $user->$row['Field']();
			} else {
				$usersInfos[$i][$row['Field']] = 'Unknown';
			}
			$i++;
		}
	}
}
mysqli_free_result($result);


// Ajout des colonnes centre et team pour les admin
if ($_SESSION['ADMIN']) {
	$usersInfos[0]['centre'] = "Centre";
	$usersInfos[0]['team'] = htmlspecialchars("Équipe", ENT_COMPAT, 'utf-8');;
	$header[$j] = $usersInfos[0]['centre'];
	$form[$j] = array('Field'	=> "Centre"
		, 'width'		=> 5
		, 'maxlength'		=> 50
	);
	$header[++$j] = $usersInfos[0]['team'];
	$form[$j] = array('Field'	=> htmlspecialchars("Équipe", ENT_COMPAT, 'utf-8')
		, 'width'		=> 3
		, 'maxlength'		=> 10
	);
	$i = 1;
	foreach ($users as $user) {
		$usersInfos[$i]['centre'] = $user->centre();
		$usersInfos[$i]['team'] = $user->team();
		$i++;
	}
}


$smarty->assign('usersInfos', $usersInfos);

$smarty->assign('header', $header);

$smarty->display('gestionUtilisateurs.tpl');
/*
 * Informations de debug
 */
include 'debug.inc.php';

// Affichage du bas de page
$smarty->display('footer.tpl');

?>
