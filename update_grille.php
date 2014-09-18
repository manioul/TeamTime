<?php
// update_grille.php
//
// update la base de données à partir de modifications de la grille
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
// L'utilisateur doit être logué pour accéder à cette page
$requireAuthenticatedUser = true;

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

if (!$_SESSION['TEAMEDIT'] && $_SESSION['utilisateur']->uid() != $_POST['uid']) {
	print "N'éditez que votre ligne, svp.";
	return false;
}

$date = new Date();
$date->annee($_POST['Year']);
$date->mois($_POST['Month']);
$date->jour($_POST['Day']);
$dispo = array(
	'uid'		=> sprintf('%02d', $_POST['uid'])
	,'date'		=> $date->date()
	,'dispo'	=> $_SESSION['db']->db_real_escape_string($_POST['dispo'])
	,'did'		=> (int) $_POST['did']
	,'oldDispo'	=> $_SESSION['db']->db_real_escape_string($_POST['oldDispo'])
	,'oldDid'	=> (int) $_POST['oldDid']
);
$affectation = $_SESSION['utilisateur']->affectationOnDate($date);


$err = jourTravail::addDispo($dispo, $affectation['centre'], $affectation['team']);

if ($err != "") {
	print(nl2br(htmlspecialchars($err)));
} else {
	print htmlspecialchars("Mise à jour effectuée.");
}
?>
