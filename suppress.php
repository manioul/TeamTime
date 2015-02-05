<?php
// suppress.php
//
// Suppression de contact téléphonique, adresse, affectation...

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
	$conf['page']['include']['class_cycle'] = NULL; // La classe cycle est nécessaire à ce script (remplace grille.inc.php
	$conf['page']['include']['class_menu'] = NULL; // La classe menu est nécessaire à ce script
	$conf['page']['include']['smarty'] = NULL; // Smarty sera utilisé sur cette page
	$conf['page']['compact'] = NULL; // Compactage des scripts javascript et css
/*
 * Fin de la définition des include
 */


/*
 * Configuration de la page
 */
/*
 * Fin de la configuration de la page
 */

require 'required_files.inc.php';


switch ($_REQUEST['q']) {
case 'phone' :
	$oPhone = new Phone( (int) $_REQUEST['id']);
	if (!empty($_SESSION['ADMIN']) || !empty($_SESSION['EDITEURS']) || !empty($_SESSION['TEAMEDIT']) || $_SESSION['utilisateur']->uid() == $oPhone->uid()) {
		$oPhone->delete();
		print("Mise à jour effectuée");
	} else {
		print("Désolé, vous ne pouvez pas faire cela...");
	}
	break;
case 'adresse' :
	$oAdresse = new Adresse( (int) $_REQUEST['id']);
	if (!empty($_SESSION['ADMIN']) || !empty($_SESSION['EDITEURS']) || !empty($_SESSION['TEAMEDIT']) || $_SESSION['utilisateur']->uid() == $oAdresse->uid()) {
		$oAdresse->delete();
		print("Mise à jour effectuée");
	} else {
		print("Désolé, vous ne pouvez pas faire cela...");
	}
	break;
case 'affectation' :
	$oAffectation = new Affectation( (int) $_REQUEST['id']);
	if (!empty($_SESSION['ADMIN']) || !empty($_SESSION['EDITEURS']) || !empty($_SESSION['TEAMEDIT']) || $_SESSION['utilisateur']->uid() == $oAffectation->uid()) {
		$oAffectation->delete();
		print("Mise à jour effectuée");
	} else {
		print("Désolé, vous ne pouvez pas faire cela...");
	}
	break;
case 'dispatchSchema' :
	$sql = sprintf("
		CALL suppressDispatchSchema(%d)
		", $_REQUEST['id']
	);
	$_SESSION['db']->db_interroge($sql);
	print "Mise à jour effectuée";
	break;
case 'dispo' :
	if ($_SESSION['ADMIN']) {
		$sql = sprintf("DELETE FROM `TBL_DISPO`
			WHERE `did` = %d
			", $_GET['did']);
	} elseif ($_SESSION['EDITEURS']) {
		$sql = sprintf("DELETE FROM `TBL_DISPO`
			WHERE `did` = %d
			AND `centre` = '%s'
			AND `team` = '%s'
			", $_GET['did']
			, $_SESSION['utilisateur']->centre()
			, $_SESSION['utilisateur']->team()
		);
	}
	if (isset($sql)) {
		$_SESSION['db']->db_interroge($sql);
		print "Mise à jour effectuée";
	} else {
		print "Vous n'avez pas le droit de faire cela...";
	}
	break;
}

?>
