<?php
// affiche_grille_multiple.php
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
$nbCycle = isset($_GET['nbCycle']) ? (int) $_GET['nbCycle'] : 3;

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
        $titrePage = "TeamTime"; // Le titre de la page
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
	$conf['page']['javascript']['jquery'] = true;
	// Utilisation de ajax
	$conf['page']['javascript']['ajax'] = true;
	// Utilisation de grille2.js.php
	$conf['page']['javascript']['grille2'] = true;
	// Utilisation de grille2.js
	$conf['page']['javascript']['grille2js'] = false;

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


// Choix de la date de début
$dateDebut = new Date(isset($_GET['dateDebut']) ? $_GET['dateDebut'] : date("Y-m-d"));
if ($dateDebut != DATE_ERR_INVALID_FORMAT) {
	$nextCycle = clone $dateDebut;
	$nextCycle->addJours(Cycle::getCycleLength());
	$sql = sprintf("SELECT `tg`.`date` FROM `TBL_GRILLE` AS `tg`, `TBL_CYCLE` AS `tc` WHERE `date` BETWEEN '%s' AND '%s' AND `tc`.`cid` = `tg`.`cid` AND `tc`.`vacation` != '%s'", $dateDebut->date(), $nextCycle->date(), REPOS);
	$vacation = $_SESSION['db']->db_fetch_row($_SESSION['db']->db_interroge($sql));
	$dateDebut = $vacation[0];
} else {
	$dateDebut = date("Y-m-d");
}

// Chargement des propriétés des dispos
$proprietesDispos = jourTravail::proprietesDispo(1);


// Date permettant de décaler d'un cycle plus tard ou avant
$nextCycle = new Date($dateDebut);
$previousCycle = new Date($dateDebut);
$nextCycle->addJours(Cycle::getCycleLength()*$nbCycle);
$smarty->assign('nextCycle', $nextCycle->date());
$previousCycle->subJours(Cycle::getCycleLength()*$nbCycle);


// Recherche des utilisateurs
//

if ($DEBUG) debug::getInstance()->startChrono('recherche utilisateurs'); // Début chrono


// Les deux premières lignes du tableau sont dédiées au jourTravail (date, vacation...)
$users[] = array('nom'		=> 'navigateur'
		,'classe'	=> 'dpt'
		,'id'		=> ''
		,'uid'		=> 'jourTravail'
	);
$users[] = array('nom'		=> '<div class="boule"></div>'
		,'classe'	=> 'dpt'
		,'id'		=> ''
		,'uid'		=> 'jourTravail'
	);

$sql = "SELECT * FROM `TBL_USERS` WHERE `actif` = 1 ORDER BY `poids` ASC";

$results = $_SESSION['db']->db_interroge($sql);
while ($res = $_SESSION['db']->db_fetch_assoc($results)) {
	$users[] = array('nom'	=> htmlentities($res['nom'])
		,'classe'	=> sprintf('nom %s', implode(' ', explode(',',  $res['classe'])))
		,'id'		=> sprintf("u%s", $res['uid'])
		,'uid'		=> $res['uid']
	);
}
mysqli_free_result($results);
// Ajout d'une rangée pour le décompte des présences
$users[] = array('nom'		=> 'décompte'
		,'class'	=> 'dpt'
		,'id'		=> 'dec'
		,'uid'		=> 'dcpt'
	);

if ($DEBUG) debug::getInstance()->stopChrono("recherche utilisateurs"); // Fin chrono

// Recherche des jours de travail
//
$cycle = array();
$dateIni = new Date($dateDebut);
if ($DEBUG) debug::getInstance()->startChrono('load_planning_duree_norepos'); // Début chrono
for ($i=0; $i<$nbCycle; $i++) {
	$cycle[$i] = new Cycle(($dateIni));
	$dateIni->addJours(Cycle::getCycleLength());
	$cycle[$i]->cycleId($i);
}
if ($DEBUG) debug::getInstance()->stopChrono('load_planning_duree_norepos'); // Fin chrono


if ($DEBUG) debug::getInstance()->startChrono('jour_de_la_semaine_courts'); // Début chrono
$jdsc = Date::$jourSemaineCourt;
if ($DEBUG) debug::getInstance()->stopChrono('jour_de_la_semaine_courts'); // Fin chrono


if ($DEBUG) debug::getInstance()->startChrono('création de la table'); // Début chrono

$lastLine = count($users)-1;
for ($i=0; $i<$nbCycle; $i++) {
	$compteurLigne = 0;
	foreach ($users as $user) {
		switch ($compteurLigne) {
			/*
			 * Première ligne contenant le navigateur, l'année et le nom du mois
			 */
		case 0:
			if ($i == 0) {
				$grille[$compteurLigne][] = array(
					'nom'		=> $cycle[$i]->dateRef()->annee()
					,'id'		=> 'navigateur'
					,'classe'	=> ''
					,'colspan'	=> 2
					,'navigateur'	=> 1 // Ceci permet à smarty de construire un navigateur entre les cycles
				);
			}
			$grille[$compteurLigne][] = array(
				'nom'		=> $cycle[$i]->dateRef()->moisAsHTML()
				,'id'		=> 'moisDuCycle' . $cycle[$i]->dateRef()->dateAsId()
				,'classe'	=> ''
				,'colspan'	=> Cycle::getCycleLengthNoRepos()+1
			);
			break;
			/*
			 * Deuxième ligne contenant les dates, les vacations, charge et vacances scolaires
			 */
		case 1:
			// La deuxième ligne contient la description de la vacation (date...)
			if ($i == 0) {
				// Ajout d'une colonne pour le nom de l'utilisateur
				$grille[$compteurLigne][] = array(
					'classe'		=> "entete"
					,'id'			=> ""
					,'nom'			=> htmlentities("Nom", ENT_NOQUOTES, 'utf-8')
				);
				// Ajout d'une colonne pour les décomptes
				$grille[$compteurLigne][] = array(
					'classe'		=> "conf"
					,'id'			=> "conf" . $cycle[$i]->dateRef()->dateAsId()
					,'nom'			=> $cycle[$i]->conf()
				);
			}
			foreach ($cycle[$i]->dispos() as $dateVacation => $vacation) {
				// Préparation des informations de jours, date, jour du cycle (en-têtes de la grille)
				$grille[$compteurLigne][] = array(
					'jds'			=> $jdsc[$vacation['jourTravail']->jourDeLaSemaine()]
					,'jdm'			=> $vacation['jourTravail']->jour()
					,'classe'		=> $vacation['jourTravail']->ferie() ? 'ferie' : 'semaine'
					,'annee'		=> $vacation['jourTravail']->annee()
					,'mois'			=> $vacation['jourTravail']->moisAsHTML()
					,'vacation'		=> htmlentities($vacation['jourTravail']->vacation())
					,'vacances'		=> $vacation['jourTravail']->vsid() > 0 ? 'vacances' : 'notvacances'
					,'periodeCharge'	=> $vacation['jourTravail']->pcid() > 0 ? 'charge' : 'notcharge'
					,'briefing'		=> $vacation['jourTravail']->briefing()
					,'id'			=> sprintf("%ss%s", $vacation['jourTravail']->dateAsId(), $vacation['jourTravail']->vacation())
					,'date'			=> $vacation['jourTravail']->date()
				);
			}
			// Ajout d'une colonne en fin de cycle
			// avec la configuration cds
			// ou une image pour la dernière colonne
			if ($i < $nbCycle-1) {
				$grille[$compteurLigne][] = array(
					'classe'		=> "conf"
					,'id'			=> "conf" . $cycle[$i+1]->dateRef()->dateAsId()
					,'nom'			=> $cycle[$i+1]->conf()
				);
			} else {
				$grille[$compteurLigne][] = array(
					'classe'		=> ""
					,'id'			=> sprintf("sepA%sM%sJ%s", $vacation['jourTravail']->annee(), $vacation['jourTravail']->mois(), $vacation['jourTravail']->jour())
					,'date'			=> $vacation['jourTravail']->date()
					,'nom'			=> '<div class="boule"></div>'
				);
			}
			break;
			/*
			 * Dernière ligne contenant le nombre de présents
			 */
		case $lastLine:
			if ($i == 0) {
				$grille[$compteurLigne][] = array(
					'classe'		=> "decompte"
					,'id'			=> ""
					,'nom'			=> htmlentities("Présents", ENT_NOQUOTES, 'utf-8')
					,'colspan'	=> 2
				);
			}
			foreach ($cycle[$i]->dispos() as $dateVacation => $vacation) {
				$grille[$compteurLigne][] = array(
					'classe'		=> 'dcpt'
					,'id'			=> sprintf("deca%sm%sj%ss%sc%s", $vacation['jourTravail']->annee(), $vacation['jourTravail']->mois(), $vacation['jourTravail']->jour(), $vacation['jourTravail']->vacation(), $cycle[$i]->cycleId())
				);
			}
			// Ajout d'une colonne en fin de cycle qui permet le (dé)verrouillage du cycle
			$jtRef = $cycle[$i]->dispos($cycle[$i]->dateRef()->date());
			$lockClass = $jtRef['jourTravail']->readOnly() ? 'cadenasF' : 'cadenasO';
			$lockTitle = $jtRef['jourTravail']->readOnly() ? 'Déverrouiller le cycle' : 'Verrouiller le cycle';
			$un_lock = $jtRef['jourTravail']->readOnly() ? 'ouvre' : 'bloque';

			$grille[$compteurLigne][] = array(
				'classe'		=> "locker"
				,'id'			=> sprintf("locka%sm%sj%sc%s", $cycle[$i]->dateRef()->annee(), $cycle[$i]->dateRef()->mois(), $cycle[$i]->dateRef()->jour(), $cycle[$i]->cycleId())
				,'nom'			=> isset($_SESSION['EDITEURS']) ? sprintf("<div class=\"imgwrapper12\"><a href=\"lock.php?date=%s&amp;lock=%s&amp;noscript=1\"><img src=\"themes/%s/images/glue.png\" class=\"%s\" alt=\"#\" /></a></div>", $cycle[$i]->dateRef()->date(), $un_lock, $conf['theme']['current'], $lockClass) : sprintf("<div class=\"imgwrapper12\"><img src=\"themes/%s/images/glue.png\" class=\"%s\" alt=\"#\" /></div>", $conf['theme']['current'], $lockClass) // Les éditeurs ont le droit de (dé)verrouiller la grille
				,'title'	=> htmlentities($lockTitle, ENT_NOQUOTES, 'utf-8')
			);
			break;
			/*
			 * Lignes utilisateurs
			 */
		default:
			if ($i == 0) {
				// La première colonne contient les infos sur l'utilisateur
				$grille[$compteurLigne][] = $user;
				// La deuxième colonne contient les décomptes horizontaux
				$grille[$compteurLigne][] = array(
					'nom'		=> 0+$cycle[$i]->compteTypeUser($user['uid'], 'dispo')
					,'id'		=> sprintf("decDispou%sc%s", $user['uid'], $cycle[$i]->cycleId())
					,'classe'	=> ''
				);
			}
			// On itère sur les vacations du cycle
			foreach ($cycle[$i]->dispos() as $dateVacation => $vacation) {
				$classe = "presence";
				if ($vacation['jourTravail']->readOnly()) $classe .= " protected";
				if (!empty($vacation[$user['uid']]) && !empty($proprietesDispos[$vacation[$user['uid']]]) && 1 == $proprietesDispos[$vacation[$user['uid']]]['absence']) {
					$classe .= " absent";
				} else {
					$classe .= " present";
				}
				/*
				 * Affichage remplacements
				 */
				if (!empty($vacation[$user['uid']]) && "Rempla" == $vacation[$user['uid']]) {
					$proprietesDispos[$vacation[$user['uid']]]['nom_long'] = "Mon remplaçant";
					$sql = sprintf("SELECT * FROM `TBL_REMPLA` WHERE `uid` = %s AND `date` = '%s'", $user['uid'], $vacation['jourTravail']->date());
					$row = $_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($sql));
					$proprietesDispos[$vacation[$user['uid']]]['nom_long'] = $row['nom'] . " | " . $row['phone'];
				} //
				$grille[$compteurLigne][] = array(
					'nom'		=> isset($vacation[$user['uid']]) ? htmlentities($vacation[$user['uid']], ENT_NOQUOTES, 'utf-8') : " "
					,'id'		=> sprintf("u%s%ss%sc%s", $user['uid'], $vacation['jourTravail']->dateAsId(), $vacation['jourTravail']->vacation(), $cycle[$i]->cycleId())
					,'classe'	=> $classe
					,'title'	=> isset($vacation[$user['uid']]['nom_long']) ? $proprietesDispos[$vacation[$user['uid']]]['nom_long'] : ''
				);
			}
			// La dernière colonne contient les décomptes horizontaux calculés
			// La date est celle de dateRef + durée du cycle
			/*$dateSuivante = clone $cycle[$i]->dateRef();
			$dateSuivante->addJours(Cycle::getCycleLength());*/
			$grille[$compteurLigne][] = array(
				'nom'		=> 0+$cycle[$i]->compteTypeUserFin($user['uid'], 'dispo')
				,'id'		=> sprintf("decDispou%sc%s", $user['uid'], $cycle[$i]->cycleId()+1)
				,'classe'	=> ''
			);
		}
		$compteurLigne++;
	}
}

if ($DEBUG) debug::getInstance()->stopChrono('création de la table'); // Fin chrono


/*
 * Assignation des variables Smarty
 */

$smarty->assign('previousCycle', $previousCycle->date());
$smarty->assign('presentCycle', date("Y-m-d"));
$smarty->assign('dureeCycle', Cycle::getCycleLengthNoRepos());
$smarty->assign('anneeCycle', $cycle[0]->dateRef()->annee());
$smarty->assign('moisCycle', $cycle[0]->dateRef()->mois());
$smarty->assign('grille', $grille);

/*
 * Fin des assignations de variable Smarty
 */

/*
 * Début des appels d'affichage Smarty
 */
// Affichage des en-têtes de page
$smarty->display('header.tpl');

// Ajout du menu horizontal
if ($conf['page']['elements']['menuHorizontal']) include('menuHorizontal.inc.php');

// Ajout des messages
if ($conf['page']['elements']['messages']) include('messages.inc.php');

// Ajout du choix du thème
if ($conf['page']['elements']['choixTheme']) include('choixTheme.inc.php');

// Affichage du menu d'administration
if ($conf['page']['elements']['menuAdmin']) include('menuAdmin.inc.php');


$smarty->display('grille2.tpl');

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
