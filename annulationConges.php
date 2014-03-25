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
	$conf['page']['include']['class_debug'] = NULL; // La classe debug est nécessaire à ce script
	$conf['page']['include']['globalConfig'] = 1; // Ce script nécessite config.inc.php
	$conf['page']['include']['init'] = 1; // la session est initialisée par init.inc.php
	$conf['page']['include']['globals_db'] = 1; // Le DSN de la connexion bdd est stockée dans globals_db.inc.php
	$conf['page']['include']['class_db'] = 1; // Le script utilise class_db.inc.php
	$conf['page']['include']['session'] = 1; // Le script utilise les sessions par session.imc
	$conf['page']['include']['classUtilisateur'] = NULL; // Le sript utilise uniquement la classe utilisateur (auquel cas, le fichier class_utilisateur.inc.php
	$conf['page']['include']['class_utilisateurGrille'] = 1; // Le sript utilise la classe utilisateurGrille
	$conf['page']['include']['class_cycle'] = 1; // La classe cycle est nécessaire à ce script (remplace grille.inc.php
	$conf['page']['include']['smarty'] = NULL; // Smarty sera utilisé sur cette page


/*
 * Configuration de la page
 */
        $conf['page']['titre'] = "TeamTime"; // Le titre de la page
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

$date = (isset($_POST['datePicker']) ? $_POST['datePicker'] : date('Y-m-d'));

$date1 = new Date($date);
$date2 = clone $date1;
$date2->addJours(Cycle::getCycleLength($affectation['centre'], $affectation['team'])); // FIXME génère une erreur 500 si $date2 est une date vide

$affectation = $_SESSION['utilisateur']->affectationOnDate(date('Y-m-d'));

// Recherche les congés qui doivent être déposés
$sql = sprintf("SELECT `nom`,
	`prenom`,
	`did`,
	`date`,
	`u`.`uid`
	FROM `TBL_USERS` AS `u`
	, `TBL_VACANCES_A_ANNULER` AS `v`
	WHERE `u`.`uid` = `v`.`uid`
	AND `edited` IS FALSE
	AND `u`.`uid` IN (SELECT `uid`
			FROM `TBL_ANCIENNETE_EQUIPE`
			WHERE NOW() BETWEEN `beginning` AND `end`
			AND `centre` = '%s'
			AND `team` = '%s'
		)
		", $affectation['centre']
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
	exit;
}

$titreConges = new TitreConges();
$titreConges->annulation();

foreach (array_keys($arr) as $uid) {
	$oldDid = NULL;
	$nbCong = 0;
	foreach(array_keys($arr[$uid]) as $date) {
		if (is_null($oldDid) && $arr[$uid][$date]['traite'] === 0) { // On ne traite que les congés qui ne l'ont pas encore été
			$dateDebut = new Date($date);
			$oldDid = $arr[$uid][$date]['did'];
			$lendemain = clone $dateDebut;
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("oldDid is null && traite == 0", "DEBUG", "", "%s", "oldDid:%d;date:%s")'
				, $arr[$uid][$date]['nom']
				, $oldDid
				, $date
				)
			);
		} elseif ($oldDid != $arr[$uid][$date]['did'] || $lendemain->compareDate($date)) {
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("oldDid != did", "DEBUG", "", "%s", "did:%d;oldDid:%d;compareDate:%d;date:%s;lendemain:%s")'
				, $arr[$uid][$date]['nom']
				, $arr[$uid][$date]['did']
				, $oldDid
				, $lendemain->compareDate($date)
				, $date
				, $lendemain->formatDate()
				)
			); 
			if ($arr[$uid][$date]['did'] == 1) {
				$nbCong = (string) $nbCong / 6;
				$nbCong = preg_replace('/\./', ',', $nbCong);
			}
			$titreConges->editTitreConges($arr[$uid][$date]['nom'], $arr[$uid][$date]['did'], $nbCong, $dateDebut->formatDate(), $dateFin->formatDate(), $dateReprise, date('d-m-Y'), $affectation['team']);
			$sql = sprintf("
				UPDATE `TBL_VACANCES_A_ANNULER`
				SET `edited` = TRUE
				WHERE `uid` = %d
				AND `did` = %d
				AND `date` BETWEEN '%s' AND '%s'
				", $uid
				, $arr[$uid][$date]['did']
				, $dateDebut->date()
				, $dateFin->date()
			);
			$_SESSION['db']->db_interroge($sql);
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Édition titre", "DEBUG", "", "%s", "nbCong:%s;did:%d;dateDebut:%s;dateFin:%s;dateReprise:%s")'
				, $arr[$uid][$date]['nom']
				, $nbCong
				, $arr[$uid][$date]['did']
				, $dateDebut->formatDate()
				, $dateFin->formatDate()
				, $dateReprise
				)
			);
			$oldDid = NULL;
			$dateDebut = new Date($date);
			$nbCong = 0;
		}
		$nbCong++;
		$arr[$uid][$date]['traite'] = 1;
		$prochainJt = new jourTravail($date, $affectation['centre'], $affectation['team']);
		$dateFin = clone $prochainJt;
		$lendemain->incDate();
		$dateReprise = $prochainJt->nextWorkingDay()->formatDate(); // La date de reprise est la prochaine date de jour travaillé
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Long", "DEBUG", "", "%s", "did:%d;oldDid:%d;compareDate:%d;date:%s;lendemain:%s")'
				, $arr[$uid][$date]['nom']
				, $arr[$uid][$date]['did']
				, $oldDid
				, $lendemain->compareDate($date)
				, $date
				, $lendemain->formatDate()
				)
			); 
	}
	if ($nbCong > 0) {
		$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Édition titre", "DEBUG", "", "%s", "nbCong:%s;did:%d;dateDebut:%s;dateFin:%s;dateReprise:%s")'
			, $arr[$uid][$date]['nom']
			, $nbCong
			, $arr[$uid][$date]['did']
			, $dateDebut->formatDate()
			, $dateFin->formatDate()
			, $dateReprise
		)
	);
		if ($arr[$uid][$date]['did'] == 1) {
			$nbCong = (string) $nbCong / 6;
			$nbCong = preg_replace('/\./', ',', $nbCong);
		}
		$titreConges->editTitreConges($arr[$uid][$date]['nom'], $arr[$uid][$date]['did'], $nbCong, $dateDebut->formatDate(), $dateFin->formatDate(), $dateReprise, date('d-m-Y'), $affectation['team']);
		$sql = sprintf("
			UPDATE `TBL_VACANCES_A_ANNULER`
			SET `edited` = TRUE
			WHERE `uid` = %d
			AND `did` = %d
			AND `date` BETWEEN '%s' AND '%s'
			", $uid
			, $arr[$uid][$date]['did']
			, $dateDebut->date()
			, $dateFin->date()
		);
		$_SESSION['db']->db_interroge($sql);
	}
}

$titreConges->editTitres();

/*
 * Informations de debug
 */
include 'debug.inc.php';
firePhpLog($conf, '$conf');

// Affichage du bas de page
//$smarty->display('footer.tpl');

?>
