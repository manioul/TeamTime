<?php
// addGestion.php
//
// Ajoute des périodes de congés, de vacances scolaires ou de briefing
// Les valeurs sont passées en POST de l'url

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
// L'utilisateur doit être admin pour accéder à cette page
$requireEditeur = true;

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
	$conf['page']['include']['class_cycle'] = NULL; // La classe cycle est nécessaire à ce script (remplace grille.inc.php
	$conf['page']['include']['class_date'] = 1; // La classe cycle est nécessaire à ce script (remplace grille.inc.php
	$conf['page']['include']['smarty'] = 1; // Smarty sera utilisé sur cette page
/*
 * Fin de la définition des include
 */

$conf['page']['elements']['firePHP'] = true;

require 'required_files.inc.php';

firePhpLog($_POST, 'POST');

if (!isset($_POST['dateD']) || !isset($_POST['t']) || !isset($_POST['dateF'])) {
	$err = "Paramètre manquant... :o";
} else {
	$dateD = new Date($_POST['dateD']);
	$dateF = new Date($_POST['dateF']);
	$tables =  array('TBL_BRIEFING', 'TBL_VACANCES_SCOLAIRES', 'TBL_PERIODE_CHARGE');
	if (false === $dateD || false === $dateF || $_POST['t'] != (int) $_POST['t']) {
		$err = "Paramètre incorrect... :o";
	} elseif ($_POST['t'] <= sizeof($tables)) {
		$description = $_SESSION['db']->db_real_escape_string($_POST['desc']);
		$sql = sprintf("
			INSERT INTO `%s`
			(`dateD`, `dateF`, `description`, `centre`)
			VALUES ('%s', '%s', '%s', '%s')"
			, $tables[$_POST['t']]
			, $dateD->date()
			, $dateF->date()
			, $description
			, $_SESSION['utilisateur']->centre()
		);
		$_SESSION['db']->db_interroge($sql);
		$champs = array(
			array(
				'briefing'
				, $description
			)
			,array(
				'vsid'
				,$_SESSION['db']->db_insert_id()
			)
			,array(
				'pcid'
				,$_SESSION['db']->db_insert_id()
			)
		);
		$sql = sprintf("
			UPDATE `TBL_GRILLE`
			SET `%s` = '%s'
			WHERE `date` BETWEEN '%s' AND '%s'
			AND `centre` = '%s'
			", $champs[$_POST['t']][0]
			, $champs[$_POST['t']][1]
			, $dateD->date()
			, $dateF->date()
			, $_SESSION['utilisateur']->centre()
		);
		$_SESSION['db']->db_interroge($sql);
	}
}

if (!empty($err) && $err != "") {
	print(nl2br(htmlspecialchars($err)));
} else {
	print htmlspecialchars("Mise à jour effectuée.");
}
?>
