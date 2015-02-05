<?php
// delGestion.php
//
// Supprime des périodes de congés, de vacances scolaires ou de briefing
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
	$conf['page']['include']['smarty'] = 1; // Smarty sera utilisé sur cette page
/*
 * Fin de la définition des include
 */

$conf['page']['elements']['firePHP'] = true;

require 'required_files.inc.php';

firePhpLog($_POST, 'POST');

if (!isset($_GET['id']) || !isset($_GET['t'])) {
	$err = "Paramètre manquant... :o";
} else if ($_GET['id'] != (int) $_GET['id'] || $_GET['t'] != (int) $_GET['t']) {
	$err = "Paramètre incorrect... :o";
} else {
	$affectation = $_SESSION['utilisateur']->affectationOnDate(date('Y-m-d'));
	$tables =  array('TBL_BRIEFING', 'TBL_VACANCES_SCOLAIRES', 'TBL_PERIODE_CHARGE');
	$champs = array(
		array(
			'briefing'
			,'NULL'
		)
		,array(
			'vsid'
			,0
		)
		,array(
			'pcid'
			,0
		)
	);
	$sql1 = sprintf("
		DELETE FROM `%s`
		WHERE `id` = %d
		", $tables[$_GET['t']]
		, $_GET['id']);
	$sql2 = sprintf("
		UPDATE `TBL_GRILLE`
		SET `%s` = %s
		WHERE `date` BETWEEN (SELECT `dateD` FROM `%s` WHERE `id` = %s)
		AND (SELECT `dateF` FROM `%s` WHERE `id` = %s)
		AND `centre` = '%s'
		", $champs[$_GET['t']][0]
		, $champs[$_GET['t']][1]
		, $tables[$_GET['t']]
		, $_GET['id']
		, $tables[$_GET['t']]
		, $_GET['id']
		, $affectation['centre']
	);
	$_SESSION['db']->db_interroge($sql1);
	$_SESSION['db']->db_interroge($sql2);
}

if (!empty($err)) {
	print(nl2br(htmlspecialchars($err)));
} else {
	print htmlspecialchars("Mise à jour effectuée.");
}
?>
