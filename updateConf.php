<?php
// updateConf.php
//
// update la base de données à partir de modifications de l'état de congés
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

// Require admin
// L'utilisateur doit être admin pour accéder à cette page
$requireTeamEdit = true;

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
	$conf['page']['include']['smarty'] = NULL; // Smarty sera utilisé sur cette page
/*
 * Fin de la définition des include
 */

$conf['page']['elements']['firePHP'] = true;

require 'required_files.inc.php';

firePhpLog($_POST, 'POST');

if ($_POST['conf'] != 'W' && $_POST['conf'] != 'E') {
	print(htmlspecialchars('Conf inconnue'));
	exit;
}

if (preg_match('/confa(\d{4})m(\d*)j(\d*)/', $_POST['id'], $array)) {
	firePhpLog($array, 'arr');
	$date = new Date(sprintf("%04d-%02d-%02d", $array[1], $array[2], $array[3]));
	$sql = sprintf("UPDATE `TBL_GRILLE`
		SET `conf` = '%s'
		WHERE `readonly` = FALSE
		AND `date` BETWEEN '%s' AND '%s'
		AND `centre` = '%s'
		AND `team` = '%s'
		", $_POST['conf']
		, $date->date()
		, $date->addJours(Cycle::getCycleLength()-1)->date()
		, $_SESSION['utilisateur']->centre()
		, $_SESSION['utilisateur']->team()
	);

	$_SESSION['db']->db_interroge($sql);
	if ($_SESSION['db']->db_affected_rows() < Cycle::getCycleLength()) { // Le verrouillage ne verrouille pas les jours de REPOS, d'où un nombre de données affectées même lorsque la grille n'est pas modifiable
		$err = "Modification impossible...";
		$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Modification de la configuration impossible.", "DEBUG", "updateConf.php", "mod failed", "affected_rows:%d;shouldBe:%d;POST:%s;SESSION:%s")'
			, $_SESSION['db']->db_affected_rows()
			, Cycle::getCycleLength()
			, $_SESSION['db']->db_real_escape_string(json_encode($_POST))
			, $_SESSION['db']->db_real_escape_string(json_encode($_SESSION))
			)
		);
	} else {
		$err = mysql_error();
	}
	firePhpLog($sql, 'SQL');
} else {
	$err = "Date inconnue";
}

if ($err != "") {
	print(nl2br(htmlspecialchars($err)));
} else {
	print htmlspecialchars("Mise à jour effectuée.");
}
?>
