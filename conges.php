<?php
/* conges.php
 *
 * Recherche des congés à déposer et édition des titres
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

$requireEditeur = true; // L'utilisateur doit être authentifié pour accéder à cette page

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
	$conf['page']['include']['smarty'] = NULL; // Smarty sera utilisé sur cette page

ob_start();

/*
 * Configuration de la page
 */
// Définit la valeur de $DEBUG pour le script
// on peut activer le debug sur des parties de script et/ou sur certains scripts :
// $DEBUG peut être activer dans certains scripts de required et désactivé dans d'autres
	$DEBUG = true;
	$conf['page']['elements']['firePHP'] = true;

	// Compactage des pages
	$conf['page']['compact'] = false;
/*
 * Fin de la configuration de la page
 */

require 'required_files.inc.php';
require 'classes/class_titreConges.inc.php';

$affectation = $_SESSION['utilisateur']->affectationOnDate(date('Y-m-d'));

$date = (isset($_POST['datePicker']) ? $_POST['datePicker'] : date('Y-m-d'));

$date1 = new Date($date);
$date2 = clone $date1;
$date2->addJours(Cycle::getCycleLength($affectation['centre'], $affectation['team']) - 1); // FIXME génère une erreur 500 si $date2 est une date vide

// Recherche les congés qui doivent être déposés
$sql = sprintf("
	SELECT `nom`,
	`prenom`,
	`did`,
	`date`,
	`u`.`uid`
	FROM `TBL_USERS` `u`
	, `TBL_VACANCES` `v`
	, `TBL_L_SHIFT_DISPO` `l`
	, `TBL_ANCIENNETE_EQUIPE` AS `a`
	WHERE `u`.`uid` = `l`.`uid`
	AND `a`.`uid` = `u`.`uid`
	AND `l`.`sdid` = `v`.`sdid`
	AND `etat` = 0
	AND `date` < (SELECT `date`
       		FROM `TBL_GRILLE`
		WHERE `date` BETWEEN '%s' AND '%s'
		AND `cid` = (
			SELECT MAX(`rang`)
			FROM `TBL_CYCLE`
			WHERE (`centre` = 'all' OR `centre` = '%s')
			AND (`team` = 'all' OR `team` = '%s')
			)
		AND `centre` = '%s'
		AND `team` = '%s'
		)
	AND `beginning` <= '%s'
	AND `end` >= '%s'
	AND `centre` = '%s'
	AND `team` = '%s'
	ORDER BY `l`.`did`
	, `nom`, `date`
	", $date1->date()
	, $date2->date()
	, $affectation['centre']
	, $affectation['team']
	, $affectation['centre']
	, $affectation['team']
	, $date2->date()
	, $date1->date()
	, $affectation['centre']
	, $affectation['team']
	);
$result = $_SESSION['db']->db_interroge($sql);
$arr = array();
while ($row = $_SESSION['db']->db_fetch_array($result)) {
	$arr[$row['uid']][$row['date']] = array(
		'did'		=> $row['did']
		,'nom'		=> $row['nom'] . " " . $row['prenom']
		,'traite'	=> 0 // Si ce congé a été traité
	);
}
mysqli_free_result($result);
if (sizeof($arr) == 0) { // Il n'y a pas de congé à poser...
	die ("Aucun congé à poser.");
}
$dateTitre = date('d-m-Y');

$titreConges = new TitreConges();

foreach (array_keys($arr) as $uid) {
	foreach(array_keys($arr[$uid]) as $date) {
		if ($arr[$uid][$date]['traite'] === 0) { // On ne traite que les congés qui ne l'ont pas encore été
			$dateDebut = new Date($date);
			$nbCong = 0;
			// On doit vérifier si le jour travaillé suivant est un congé et de même type
			$prochainJt = new jourTravail($date, $affectation['centre'], $affectation['team']);
			//$dateDepart = $prochainJt->previousWorkingDay()->date(); // La date de départ en congé est la dernière date travaillée
			do {
				$nbCong++;
				$dateFin = clone $prochainJt; // La date de fin de congé est le jour du congé si il n'y a qu'un seul jour de congé
				// Le jour présentement traité passe à l'état 1 (filed)
				// Ceci corrige un bug : précédemment, l'état des jours
				// où des congés étaient déposés passaient en totalité à 1
				// Or, il pouvait très bien y avoir dans cette période des
				// congés dont l'état était déjà à 2 (confirmed).
				$_SESSION['db']->db_interroge(sprintf("
					UPDATE `TBL_VACANCES`
					SET `etat` = 1
					WHERE `sdid` = (SELECT `sdid`
					FROM `TBL_L_SHIFT_DISPO`
					WHERE `date` = '%s'
					AND `uid` = %d
					)
					", $prochainJt->date()
					, $uid
				));
				$arr[$uid][$dateFin->date()]['traite'] = 1; // Ce congé est traîté
				$prochainJt = clone $prochainJt->nextWorkingDay();
				$dateReprise = $prochainJt->formatDate(); // La date de reprise est la prochaine date de jour travaillé
				$sql = sprintf("
					SELECT `etat`
					, `did`
					, `l`.`sdid`
					FROM `TBL_VACANCES` `v`
					, `TBL_L_SHIFT_DISPO` `l`
					WHERE `v`.`sdid` = `l`.`sdid`
				       	AND `uid` = %d
				       	AND `date` = '%s'", $uid, $prochainJt->date());
				$row = $_SESSION['db']->db_fetch_array($_SESSION['db']->db_interroge($sql));
			} while (!empty($row[1]) && $row[1] == $arr[$uid][$date]['did']);
			if ($arr[$uid][$date]['did'] == 1) {
				$nbCong = (string) $nbCong / 6;
				$nbCong = preg_replace('/\./', ',', $nbCong);
				$dateFin->addJours(3); // Un congé demi-cycle comprend les trois jours de repos
			}
			$titreConges->editTitreConges($arr[$uid][$date]['nom'], $arr[$uid][$date]['did'], $nbCong, $dateDebut->formatDate(), $dateFin->formatDate(), $dateReprise, $dateTitre, $affectation['team']);
		}
	}
}

$titreConges->editTitres();


/*
 * Informations de debug
 */
include 'debug.inc.php';
firePhpLog($conf, '$conf');

ob_end_flush();

?>

