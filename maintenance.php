<?php
// maintenance.php
//
// Opérations de maintenance de la base de données
// Recherche les incongruences et permet de les corriger

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
$requireAdmin = true;

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
	$conf['page']['include']['bibliothequeMaintenance'] = 1; // La bibliothèque des fonctions de maintenance est nécessaire
/*
 * Fin de la définition des include
 */


/*
 * Configuration de la page
 */
        $titrePage = sprintf("Maintenance"); // Le titre de la page
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
	// Utilisation de utilisateur.js
	$conf['page']['javascript']['utilisateur'] = false;

	// Feuilles de styles
	// Utilisation de la feuille de style general.css
	$conf['page']['stylesheet']['general'] = true;
	$conf['page']['stylesheet']['grille'] = false;
	$conf['page']['stylesheet']['grilleUnique'] = false;
	$conf['page']['stylesheet']['utilisateur'] = false;

	// Compactage des pages
	$conf['page']['compact'] = false;
	
/*
 * Fin de la configuration de la page
 */

require 'required_files.inc.php';

/*
 * Traitement des actions demandées
 */
if ($_GET['id']) {
	switch ($_GET['op']) { 
	case 'del':
		$_SESSION['db']->db_interroge("DELETE FROM `TBL_L_SHIFT_DISPO` WHERE `sdid` = " . (int) $_GET['id']);
		$_SESSION['db']->db_interroge("DELETE FROM `TBL_VACANCES` WHERE `sdid` = " . (int) $_GET['id']);
		break;
	case 'upd':
		if ($_GET['t'] == 'l') {
			switch ($_GET['field']) {
			case 'pereq':
				$val = empty($_GET['val']) ? 'FALSE' : 'TRUE';
				$sql = sprintf("
					UPDATE `TBL_L_SHIFT_DISPO`
					SET `pereq` = %s
					WHERE `sdid` = %d
					", $val
					, $_GET['id']
				);
				$_SESSION['db']->db_interroge($sql);
				break;
			}
		}
		break;
	}
}


$results = search_double_l(0);
if (sizeof($results) > 0) {
	$smarty->assign("titre", "Doubles TBL_L_SHIFT_DISPO");
	$smarty->assign('results', $results);
	$smarty->display('dispo_multiples.tpl');
}

if (($results = search_double_sdid_v(1)) !== false) {
	if (sizeof($results) > 0) {
		$smarty->assign("titre", "Doubles TBL_VACANCES");
		$smarty->assign('results', $results);
		$smarty->display('dispo_multiples.tpl');
	}
}

if (($results = search_orphan_v(1)) !== false) {
	if (sizeof($results) > 0) {
		$smarty->assign("titre", "Orphelins de TBL_VACANCES");
		$smarty->assign('results', $results);
		$smarty->display('dispo_multiples.tpl');
	}
}

$results = liste_pereq();
if (sizeof($results) > 0) {
	$smarty->assign("titre", "Péréquations");
	$smarty->assign('results', $results);
	$smarty->display('dispo_multiples.tpl');
}

$results = search_event_on_rest();
if (sizeof($results) > 0) {
	$smarty->assign("titre", "Évènements sur des repos");
	$smarty->assign('results', $results);
	$smarty->display('dispo_multiples.tpl');
}

$results = search_orphan_l();
if (sizeof($results) > 0) {
	$smarty->assign("titre", "Congés orphelins de TBL_L_SHIFT_DISPO");
	$smarty->assign('results', $results);
	$smarty->display('dispo_multiples.tpl');
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
