<?php
// lock.php
//
// Permet de (dé)protéger une partie de la grille contre les modifications
// La valeur de référence (qui est inversée) est la valeur `readOnly` de
// la grille à la date passée en argument au format "/decsepA(\d+)M(\d+)J(\d+)$/";

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

// Require Editeur user
// L'utilisateur doit être éditeur pour accéder à cette page
// Seuls les utilisateurs habilités à modifier librement la grille peuvent
// (dé)locker une grille
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
require 'required_files.inc.php';

ob_start();
$err = "";


/*
 * Traitement des paramètres
 */

$dateOK = FALSE;
$userOK = FALSE;

$date = new Date($_REQUEST['date']);

$affectation = $_SESSION['utilisateur']->affectationOnDate($date);

if ($cycle = new Cycle($date, $affectation['centre'], $affectation['team'])) {
	$dateOK = TRUE;
} else {
	$err .= "Je ne comprends pas la date de base pour (dé)protéger la grille.\n";
}


/*
 * Verrouillage
 */
if ($dateOK) {
	if ($_REQUEST['lock'] === 'bloque') {
		$cycle->lockCycle();
		print("Cycle verrouillé");
	}
	if ($_REQUEST['lock'] === 'ouvre') {
		$cycle->unlockCycle();
		print("Cycle déverrouillé");
	}
}


header("Location:".$_SERVER['HTTP_REFERER']);

/*
 * Gestion des erreurs
 */
if ($err != "") {
	printf("<span class='erreur'>Erreur:</span> %s", nl2br(htmlentities($err, ENT_NOQUOTES, 'utf-8')));
} else {
	print htmlentities("Mise à jour effectuée.", ENT_NOQUOTES, 'utf-8');
}
if (isset($_GET['noscript'])) { // Si le paramètre noscript est passé alors javascript n'est pas utilisé
	print ("<br /><a href=\"affiche_grille.php\">Revenir &agrave; la grille</a>");
}

ob_end_flush();

?>
