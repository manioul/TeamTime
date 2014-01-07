<?php
// lesHeures.php
//
// Affiche les heures de tous les utilisateurs d'un même centre, même équipe depuis la date demandée

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
$requireAuthenticatedUser = true;

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
	$conf['page']['include']['bibliothequeMaintenance'] = false; // La bibliothèque des fonctions de maintenance est nécessaire
/*
 * Fin de la définition des include
 */


/*
 * Configuration de la page
 */
        $conf['page']['titre'] = sprintf("Liste les heures de l'équipe"); // Le titre de la page
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


	// Gestion des briefings
	$conf['page']['elements']['intervalDate'] = true;

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

$aHeures = array();
$aTotaux = array();
$checked = array();

/*
 * Recherche des dispo pour créer une liste d'exclusion
 */
$sql = sprintf("
	SELECT `did`, `dispo`, `nom_long`
	FROM TBL_DISPO
	WHERE `actif` IS TRUE
	AND absence IS NOT TRUE
	AND (`centre` = '%s' OR `centre` = 'all')
	AND (`team` = '%s' OR `team` = 'all')
	", $_SESSION['centre']
	, $_SESSION['team']
);
$result = $_SESSION['db']->db_interroge($sql);
while ($row = $_SESSION['db']->db_fetch_row($result)) {
	$aDispos[$row[0]] = (!empty($row[2]) ? $row[2] : $row[1]) ;
	if ($row[1] == 'Rempla') $checked[$row[0]] = 1;
}
mysqli_free_result($result);

$dd = '01-01-2013';

$smarty->assign('defaultD', $dd);
$smarty->assign('aDispos', $aDispos);
$smarty->assign('checked', $checked);

$smarty->display('debutHeuresForm.tpl');


/*
 * Traitement du formulaire d'affichage des heures
 */
if (!empty($_POST['dateD'])) {
	$dateDebut = new Date($_POST['dateD']);
	if (!empty($_POST['dateF'])) {
		$dateFin = new Date($_POST['dateF']);
	}
} else {
	$dateDebut = new Date($dd);
	$dateFin = clone $dateDebut;
	$dateFin->addJours(365);
}
	/*
	 * Gestion des exclusions
	 */
	$exclude = "";
	if (sizeof($_POST['dispo']) > 0) {
		$exclude = sprintf("
			AND NOT FIND_IN_SET(`did`, '%s')
			", $_SESSION['db']->db_real_escape_string(implode(',', array_keys($_POST['dispo'])))
		);
	}

	/*
	 * Calcul des totaux
	 */
	$sql = sprintf("
		SELECT `h`.`uid`, `nom`, SUM(`normales`) AS `normales`, SUM(`instruction`) AS `instruction`, SUM(`simulateur`) AS `simulateur`
		FROM `TBL_HEURES` AS `h`,
		`TBL_USERS` AS `u`
		WHERE `date` BETWEEN '%s' AND '%s'
		AND `h`.`uid` = `u`.`uid`
		%s
		GROUP BY `h`.`uid`
		UNION
		SELECT 'uid', 'Total', SUM(`normales`) AS `normales`, SUM(`instruction`) AS `instruction`, SUM(`simulateur`) AS `simulateur`
		FROM `TBL_HEURES`
		WHERE `date` BETWEEN '%s' AND '%s'
		%s
		", $dateDebut->date()
		, $dateFin->date()
		, $exclude
		, $dateDebut->date()
		, $dateFin->date()
		, $exclude
	);
	$result = $_SESSION['db']->db_interroge($sql);
	while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
		$aTotaux[] = $row;
	}
	mysqli_free_result($result);

	$smarty->assign('dateDebut', $dateDebut->formatDate('fr'));
	$smarty->assign('dateFin', $dateFin->formatDate('fr'));
	$smarty->assign('mTotaux', $aTotaux);
	$smarty->display('lesHeures.tpl');

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
