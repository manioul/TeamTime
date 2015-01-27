<?php
/* pereq.php
 *
 * Page squelette pour créer des pages personnalisées
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

$requireTeamEdit = true; // L'utilisateur doit être admin pour accéder à cette page

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
	$conf['page']['include']['classUtilisateur'] = false; // Le sript utilise uniquement la classe utilisateur (auquel cas, le fichier class_utilisateur.inc.php
	$conf['page']['include']['class_utilisateurGrille'] = 1; // Le sript utilise la classe utilisateurGrille
	$conf['page']['include']['class_cycle'] = 1; // La classe cycle est nécessaire à ce script (remplace grille.inc.php
	$conf['page']['include']['class_menu'] = 1; // La classe menu est nécessaire à ce script
	$conf['page']['include']['smarty'] = 1; // Smarty sera utilisé sur cette page
	$conf['page']['include']['bibliothequeMaintenance'] = 1; // La bibliothèque des fonctions de maintenance est nécessaire
	$conf['page']['compact'] = false; // Compactage des scripts javascript et css


/*
 * Configuration de la page
 */
        $conf['page']['titre'] = "Péréquations - Administration de TeamTime"; // Le titre de la page
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
	$conf['page']['elements']['intervalDate'] = true;

	// Utilisation de jquery
	$conf['page']['javascript']['jquery'] = true;
	// Utilisation de ajax
	$conf['page']['javascript']['ajax'] = true;
	// Utilisation de grille2.js.php
	$conf['page']['javascript']['grille2'] = false;
	// Utilisation de online
	$conf['page']['javascript']['online'] = true;
	// Utilisation de administration
	$conf['page']['javascript']['administration'] = true;

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

$affectation = $_SESSION['utilisateur']->affectationOnDate(date('Y') . '-01-01');

// Recherche des utilisateurs
$users = utilisateursDeLaGrille::getInstance()->getActiveUsersFromTo(date('Y') . "-01-01", date('Y') . "-12-31", $affectation['centre'], $affectation['team']);

// Recherche des dispos candidates à péréq
$dispos = array();
$dispos['name'] = 'did';
$dispos['label'] = 'Activité';
$sql = sprintf("
	SELECT `did`
	, `dispo`
	FROM `TBL_DISPO`
	WHERE `actif` IS TRUE
	AND (`type decompte` IS NOT NULL
		OR `need_compteur` IS TRUE)
	AND (`centre` = 'all' OR `centre` = '%s')
	AND (`team` = 'all' OR `team` = '%s')
	ORDER BY `poids` ASC
	", $affectation['centre']
	, $affectation['team']
);
$index = 0;
$result = $_SESSION['db']->db_interroge($sql);
while ($row = $_SESSION['db']->db_fetch_array($result)) {
	$dispos['options'][$index]['value'] = $row[0];
	$dispos['options'][$index]['content'] = $row[1];
	$index++;
}
mysqli_free_result($result);
$smarty->assign('users', $users);
$smarty->assign('dispos', $dispos);
$years = array(
	'name'	=> 'year'
	, 'label'	=> 'Année (Congés)'
	, 'options'	=> array(
		0	=> array(
			'value'		=> date("Y")-1
			, 'content'	=> date("Y")-1)
		, 1	=> array(
			'value'		=> date("Y")
			, 'content'	=> date("Y"))
		, 2	=> array(
			'value'		=> date("Y")+1
			, 'content'	=> date("Y")+1)

		)
	);
$smarty->assign('years', $years);
if (isset($_POST['uid']) || isset($_POST['did']) || isset($_POST['year'])) {
	$pereq = array();
	if (empty($_POST['nb'])) {
		$pereq['nb'] = 1;
	} else {
		$pereq['nb'] = (int) abs($_POST['nb']);
	}
	if (isset($_POST['dateD'])) {
		$date = new Date($_POST['dateD']);
		if (false !== $date) $pereq['date'] = $date->date();
	}	
	if ($_POST['did'] != (int) $_POST['did'] || $_POST['uid'] != (int) $_POST['uid'] || $_POST['year'] != (int) $_POST['year'] || $_POST['did'] != abs($_POST['did']) || $_POST['uid'] != abs($_POST['uid']) || $_POST['year'] != abs($_POST['year'])) {
		$err = "Paramètre incorrect... :o";
	} else {
		$pereq['did'] = (int) $_POST['did'];
		$pereq['uid'] = (int) $_POST['uid'];
		$pereq['year'] = (int) $_POST['year'];
		if (isset($_POST['suppr'])) {
			jourTravail::delPereq($pereq);
		} else {
			while ($pereq['nb']--) {
				jourTravail::addPereq($pereq);
			}
		}
	}
}

$smarty->display('pereq.tpl');

$results = liste_pereq();
if (sizeof($results) > 0) {
	$smarty->assign("titre", "Gestion des péréquations");
	$smarty->assign('results', $results);
	$smarty->display('dispo_multiples.tpl');
}


/*
 * Informations de debug
 */
include 'debug.inc.php';

// Affichage du bas de page
$smarty->display('footer.tpl');

ob_end_flush(); // Obligatoire pour firePHP

?>
