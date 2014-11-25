<?php
// monCompte.php
//
// Page permettant de gérer l'état civil et les informations de la carrière d'un utilisateur

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
        $conf['page']['titre'] = sprintf("mon compte"); // Le titre de la page
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


	// Facilite la saisie d'un intervalle de date
	$conf['page']['elements']['intervalDate'] = true;

	// Utilisation de jquery
	$conf['page']['javascript']['jquery'] = true;
	// Utilisation de ajax
	$conf['page']['javascript']['ajax'] = true;
	// Utilisation de grille2.js.php
	$conf['page']['javascript']['grille2'] = false;
	// Utilisation de grille2.js
	$conf['page']['javascript']['grille2js'] = false;
	// Utilisation de utilisateur.js
	$conf['page']['javascript']['utilisateur'] = true;
	// Utilisation de administration
	$conf['page']['javascript']['administration'] = true;

	// Feuilles de styles
	// Utilisation de la feuille de style general.css
	$conf['page']['stylesheet']['general'] = true;
	$conf['page']['stylesheet']['grille'] = true;
	$conf['page']['stylesheet']['grilleUnique'] = false;
	$conf['page']['stylesheet']['utilisateur'] = true;

	// Compactage des pages
	$conf['page']['compact'] = false;
	
/*
 * Fin de la configuration de la page
 */

require 'required_files.inc.php';

$reloadUser = false; // Positionner si l'utilisateur doit être rechargé (c'est le cas lorsque le compte édité est le compte de l'utilisateur connecté).
// Les utilisateurs non admin et non editeurs ne peuvent accéder qu'à leurs données
if ((array_key_exists('ADMIN', $_SESSION) || array_key_exists('EDITEURS', $_SESSION)) && array_key_exists('uid', $_REQUEST)) {
	$utilisateur = new UtilisateurGrille( (int) $_REQUEST['uid']);
} else {
	// Les utilisateurs teamEdit peuvent accéder au compte des utilisateurs de leur équipe
	if (array_key_exists('TEAMEDIT', $_SESSION) && array_key_exists('uid', $_REQUEST)) {
		$utilisateur = new UtilisateurGrille( (int) $_REQUEST['uid']);
		if ($_SESSION['utilisateur']->centre() != $utilisateur->centre() || $_SESSION['utilisateur']->team() != $utilisateur->team()) {
			$err = "Vous n'êtes pas autorisé à modifier ce compte...";
			die ($err);
		}
	} else {
		$utilisateur = new UtilisateurGrille( $_SESSION['utilisateur']->uid());
	}
}
if ($_SESSION['utilisateur']->uid() === $utilisateur->uid()) {
	$reloadUser = true;
}
if (!is_a($utilisateur, 'utilisateurGrille')) {
	die("On n'a pas obtenu l'objet attendu");
}

if (sizeof($_POST) > 0) {
	if (!array_key_exists('TEAMEDIT', $_SESSION) && array_key_exists('uid', $_POST) && $_POST['uid'] != $_SESSION['utilisateur']->uid()) {
		$err = "Vous n'êtes pas autorisé à modifier ce compte...";
		die ($err);
	}
	if (array_key_exists('submitAffect', $_POST)) {
		$utilisateur->addAffectation($_POST);
	} else {
		if (array_key_exists('actif', $_POST) && array_key_exists('EDITEURS', $_SESSION)) $_POST['actif'] = 1;
		if (array_key_exists('locked', $_POST) && array_key_exists('EDITEURS', $_SESSION)) $_POST['locked'] = 0;
		if (array_key_exists('totd', $_POST) && array_key_exists('EDITEURS', $_SESSION)) $_POST['showtipoftheday'] = 0;

		$utilisateur->setFromRow($_POST);

		/*
		 * Préférences utilisateur
		 */
		// Ajout de la page favorite (jointe après la connexion)
		if ($utilisateur->page($utilisateur->availablePages('uri', $_POST['read'])) === false) {
			print "Erreur de mise à jour de la page...";
		}
		// Compteurs
		//
		// Il suffit d'ajouter un cookie, la préférence sera enregistrée en vérifiant
		// l'existence du cookie lors de la mise à jour des infos de l'utilisateur
		if (array_key_exists('cpt', $_POST)) {
			setcookie('cpt', 1, 0, $conf['session_cookie']['path'], NULL, $conf['session_cookie']['secure']);
			$utilisateur->addPref('cpt', 1);
		} else {
			setcookie('cpt', 0, 0, $conf['session_cookie']['path'], NULL, $conf['session_cookie']['secure']);
			$utilisateur->addPref('cpt', 0);
		}

		// S'il y a un nouveau téléphone à ajouter
		if (array_key_exists('newnb', $_POST)) {
			$newPhone = array(
				'uid'	=> $utilisateur->uid()
				, 'phone'		=> $_POST['newnb']
				, 'description'	=> $_POST['newdesc']
			);
			if (isset($_POST['newpal'])) {
				$newPhone['principal'] = true;
			} else {
				$newPhone['principal'] = false;
			}
			$utilisateur->addPhone($newPhone);
		}

		// S'il y a une nouvelle adresse à ajouter
		if (array_key_exists('newadresse', $_POST) && array_key_exists('newville', $_POST) && array_key_exists('newcp', $_POST)) {
			$utilisateur->addAdresse(array(
				'uid'	=> $utilisateur->uid()
				, 'adresse'	=> $_POST['newadresse']
				, 'ville'	=> $_POST['newville']
				, 'cp'		=> $_POST['newcp']
				)
			);
		}
		$utilisateur->fullUpdateDB();
	}
	if ($reloadUser) {
		// On recharge l'utilisateur pour prendre en compte les modifications de son compte
		$_SESSION['utilisateur'] = new utilisateurGrille($_SESSION['utilisateur']->uid());
	}
}

// Données nécessaires à la partie contact
$affectation = $_SESSION['utilisateur']->affectationOnDate(date('Y-m-d'));

$smarty->assign('centres', Affectation::listeAffectations('centre', $affectation['centre'] ));
$smarty->assign('teams', Affectation::listeAffectations('team', $affectation['team']));
$smarty->assign('grades', Affectation::listeAffectations('grade', $affectation['grade']));
$smarty->assign('utilisateur', $utilisateur);
$smarty->assign('pref', $utilisateur->prefAsArray());
$smarty->assign('locked', $utilisateur->locked());
$smarty->assign('actif', $utilisateur->actif());
$smarty->assign('totd', $utilisateur->showtipoftheday());

// Données relatives à la carrière

$smarty->assign('datas', $utilisateur->orderedAffectations());

$smarty->display("monCompte.tpl");

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
