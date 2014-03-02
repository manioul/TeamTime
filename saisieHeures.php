<?php
// affiche_grille.php
//
// Affiche la grille sous différents formats

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

// Choix du nombre de cycle à présenter
$nbCycle = isset($_GET['nbCycle']) ? (int) $_GET['nbCycle'] : 1;

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
	$conf['page']['include']['classUtilisateur'] = false; // Le sript utilise uniquement la classe utilisateur (auquel cas, le fichier class_utilisateur.inc.php
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
        $conf['page']['titre'] = "Saisie des heures"; // Le titre de la page
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
	$conf['page']['javascript']['jquery'] = true;
	// Utilisation de ajax
	$conf['page']['javascript']['ajax'] = true;
	// Utilisation de grille2.js.php
	$conf['page']['javascript']['grille2'] = true;

	// Feuilles de styles
	// Utilisation de la feuille de style general.css
	$conf['page']['stylesheet']['general'] = true;
	$conf['page']['stylesheet']['grille'] = true;
	$conf['page']['stylesheet']['grilleUnique'] = ($nbCycle == 1 ? true : false);

	// Compactage des pages
	$conf['page']['compact'] = false;
	
/*
 * Fin de la configuration de la page
 */

require 'required_files.inc.php';


$aHeures = array();
$aDates = array();
$aListe = array();
$dHeures = NULL;
$fHeures = NULL;

// Saisie des heures
if (!empty($_POST['tableau'])) {
	if (preg_match_all('/([\.\d]+)\s+([,\d]+)/', $_POST['tableau'], $array, PREG_PATTERN_ORDER)) {
		$aDates = preg_replace('/(\d{2})\.(\d{2})\.(\d{2})/', '${1}/${2}/20${3}', $array[1]);
		$aHeures = preg_replace('/,/', '.', $array[2]);
		foreach ($aDates as $i => $date) {
			if (($oDate = new Date($date)) !== false) {
				$sql = sprintf("
					REPLACE INTO `TBL_HEURES_A_PARTAGER`
					(`centre`, `team`, `date`, `heures`, `dispatched`, `writable`)
					VALUES
					('%s', '%s', '%s', %02d, 0, 1)
					", $_SESSION['centre']
					, $_SESSION['team']
					, $_SESSION['db']->db_real_escape_string($oDate->date())
					, $aHeures[$i]
				);
				$_SESSION['db']->db_interroge($sql);
				if (is_null($dHeures)) {
					$dHeures = clone $oDate;
				} else {
					if ($dHeures->compareDate($oDate) > 0) $dHeures = clone $oDate;
				}
				if (is_null($fHeures)) {
					$fHeures = clone $oDate;
				} else {
					if ($fHeures->compareDate($oDate) < 0) $fHeures = clone $oDate;
				}
				/*$sql = sprintf("
					CALL dispatchOneDayHeures('%s', '%s', '%s')
					", $_SESSION['centre']
					, $_SESSION['team']
					, $_SESSION['db']->db_real_escape_string($oDate->date())
				);
				$_SESSION['db']->db_interroge($sql);
				 */
			} else {
				$err[] = sprintf("La date %s n'est pas correcte et n'a pas été insérée...", $aDates[$i]);
			}
		}
		$sql = sprintf ("
			CALL dispatchHeuresBetween('%s', '%s', '%s', '%s');
			", $_SESSION['centre']
			, $_SESSION['team']
			, $dHeures->date()
			, $fHeures->date()
		);
		$_SESSION['db']->db_interroge($sql);
	} else {
		$err[] = "Les heures saisies ne sont pas au format attendu... :(";
	}
}
// Recalcul des heures
if (!empty($_POST['dateD']) && !empty($_POST['dateF'])) {
	$dateD = new Date($_POST['dateD']);
	$dateF = new Date($_POST['dateF']);
	$sql = sprintf("
		CALL dispatchHeuresBetween('%s', '%s', '%s', '%s');
		", $_SESSION['centre']
		, $_SESSION['team']
		, $dateD->date()
		, $dateF->date()
	);
	$_SESSION['db']->db_interroge($sql);
}
if (!empty($_POST['calcAll'])) {
	$sql = sprintf("
		CALL dispatchAllHeures('%s', '%s')
		", $_SESSION['centre']
		, $_SESSION['team']
	);
	$_SESSION['db']->db_interroge($sql);
}

/*
 * Liste des heures déjà saisies
 */
if (empty($_POST['dateFrom'])) {
	$dateFrom = new Date(date('Y-m-d'));
	$dateFrom->subJours(36);
} else {
	$dateFrom = new Date($_POST['dateFrom']);
}
if (empty($_POST['dateTo'])) {
	$dateTo = new Date(date('Y-m-d'));
} else {
	$dateTo = new Date($_POST['dateTo']);
}
$sql = sprintf("
	SELECT `date`, `heures`, `dispatched`, `writable`
	FROM `TBL_HEURES_A_PARTAGER`
	WHERE `centre` = '%s'
	AND `team` = '%s'
	AND `date` BETWEEN '%s' AND '%s'
	", $_SESSION['centre']
	, $_SESSION['team']
	, $dateFrom->date()
	, $dateTo->date()
);
$result = $_SESSION['db']->db_interroge($sql);
while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
	$aListe[] = $row;
}
mysqli_free_result($result);
$smarty->assign('aListe', $aListe);

/*
 * Début des appels d'affichage Smarty
 */

$smarty->display('saisieHeures.tpl');

$smarty->display('recalcHeures.tpl');

$smarty->display('listeHeuresSaisies.tpl');

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
