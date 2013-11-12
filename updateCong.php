<?php
// updateCong.php
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
// L'utilisateur doit être editeur pour accéder à cette page
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
	$conf['page']['include']['class_utilisateurGrille'] = NULL; // Le sript utilise la classe utilisateurGrille
	$conf['page']['include']['class_cycle'] = 1; // La classe cycle est nécessaire à ce script (remplace grille.inc.php
	$conf['page']['include']['smarty'] = NULL; // Smarty sera utilisé sur cette page
/*
 * Fin de la définition des include
 */

$conf['page']['elements']['firePHP'] = true;

require 'required_files.inc.php';

firePhpLog($_POST, 'POST');

$etat = ($_POST['f'] >= 2 ? 2 : 1);
if (preg_match('/u(.+)d(\d{2,4}-\d{2}-\d{2,4})/', $_POST['id'], $array)) { // La date doit respecter les formats fr et us
	$date = new Date($array[2]);
	$sql = sprintf("UPDATE `TBL_VACANCES` SET `etat` = %d WHERE `sdid` = (SELECT `sdid` FROM `TBL_L_SHIFT_DISPO` WHERE `date` = '%s' AND `uid` = %d LIMIT 1)", $etat, $date->date(), $array[1]);
	$_SESSION['db']->db_interroge($sql);
} else {
	$err = "Date inconnue";
}

if (!empty($err)) {
	print(nl2br(htmlspecialchars($err)));
} else {
	print htmlspecialchars("Mise à jour effectuée.");
}
?>
