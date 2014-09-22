<?php
// createAccount.php
//
// Permet à un utilisateur dont le compte vient d'être validé de créer son compte
// Permet également à un utilisateur de recréer son mot de passe
// Dans ce dernier cas, les date de début et de fin dans TBL_SIGNUP_ON_HOLD sont NULL
// et le nom contient le uid

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
	$conf['page']['include']['class_cycle'] = NULL; // La classe cycle est nécessaire à ce script (remplace grille.inc.php
	$conf['page']['include']['class_menu'] = NULL; // La classe menu est nécessaire à ce script
	$conf['page']['include']['smarty'] = 1; // Smarty sera utilisé sur cette page
	$conf['page']['compact'] = false; // Compactage des scripts javascript et css
	$conf['page']['include']['bibliothequeMaintenance'] = false; // La bibliothèque des fonctions de maintenance est nécessaire
/*
 * Fin de la définition des include
 */


/*
 * Configuration de la page
 */
        $conf['page']['titre'] = sprintf("Création de mon compte utilisateur"); // Le titre de la page
// Définit la valeur de $DEBUG pour le script
// on peut activer le debug sur des parties de script et/ou sur certains scripts :
// $DEBUG peut être activer dans certains scripts de required et désactivé dans d'autres
	$DEBUG = false;

	/*
	 * Choix des éléments à afficher
	 */
	
	// Affichage du menu horizontal
	$conf['page']['elements']['menuHorizontal'] = false;
	// Affichage messages
	$conf['page']['elements']['messages'] = false;
	// Affichage du choix du thème
	$conf['page']['elements']['choixTheme'] = false;
	// Affichage du menu d'administration
	$conf['page']['elements']['menuAdmin'] = false;
	
	// éléments de debug
	
	// FirePHP
	$conf['page']['elements']['firePHP'] = false;
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



	// Utilisation de jquery
	$conf['page']['javascript']['jquery'] = false;
	// Utilisation de ajax
	$conf['page']['javascript']['ajax'] = false;
	// Utilisation de grille2.js.php
	$conf['page']['javascript']['grille2'] = false;
	// Utilisation de utilisateur.js
	$conf['page']['javascript']['utilisateur'] = true;

	// Feuilles de styles
	// Utilisation de la feuille de style general.css
	$conf['page']['stylesheet']['general'] = true;
	$conf['page']['stylesheet']['grille'] = false;
	$conf['page']['stylesheet']['grilleUnique'] = false;
	$conf['page']['stylesheet']['utilisateur'] = false;

	// Compactage des pages
	$conf['page']['compact'] = false;
	
/*
 * Fin de la configuration de la page
 */

require 'required_files.inc.php';

if (!array_key_exists('k', $_REQUEST) || (array_key_exists('k', $_REQUEST) && strlen($_REQUEST['k']) != 40)) {
	header('Location:index.php');
}

//
// Recherche la clé dans la base
//
$sql = sprintf("
	SELECT *
	FROM `TBL_SIGNUP_ON_HOLD`
	WHERE `url` = '%s'
	AND NOW() <= DATE_ADD(`timestamp`, INTERVAL 1 WEEK)
	", $_SESSION['db']->db_real_escape_string($_REQUEST['k'])
);
$result = $_SESSION['db']->db_interroge($sql);
/*
 * Erreur sur la clé
 */
if (mysqli_num_rows($result) > 1) {
	$_SESSION['db']->db_interroge(sprintf("
		CALL messageSystem('La clé existe plusieurs fois dans la table.', 'DEBUG', 'createAccount.php', 'key error', 'key:%s;sql:%s')
		", $_SESSION['db']->db_real_escape_string($_REQUEST['k'])
		, $_SESSION['db']->db_real_escape_string($sql)
	));
	die("Quelque chose s'est mal passé. Les administrateurs ont été avertis. Nous sommes désolés pour la gêne occasionnée.");
} elseif (mysqli_num_rows($result) == 0) {
	$_SESSION['db']->db_interroge(sprintf("
		CALL messageSystem('La clé n\'existe pas', 'DEBUG', 'createAccount.php', 'key error', 'key:%s;sql:%s')
		", $_SESSION['db']->db_real_escape_string($_REQUEST['k'])
		, $_SESSION['db']->db_real_escape_string($sql)
	));
	die("Aucune clé ne correspond. Les administrateurs ont été avisés. Si vous êtes certain de l'adresse contactez un administrateur. Désolés pour la gêne occasionnée.");
}
$row = $_SESSION['db']->db_fetch_assoc($result);
mysqli_free_result($result);

$err = array();
/*
 * Traitement du formulaire
 */
if (sizeof($_POST) > 0) {
	if (is_null($row['beginning']) && is_null($row['end'])) {
		// Récupération de compte
		if (TRUE === utilisateurGrille::resetDaPwd($_POST['k'], $_POST['pwd'])) {
			// Le mot de passe a été mis à jour
			header('Location:/index.php?k=rpok');
		} else {
			// Le reset de mot de passe ne s'est pas fait correctement
			header('Location:/index.php?k=rpfail');
		}
	} else {
		// Création de compte
		$_SESSION['db']->db_interroge(sprintf("
			CALL createFromSignup('%s', '%s', '%s', '%s')
			", $_SESSION['db']->db_real_escape_string($_POST['k'])
			, $_SESSION['db']->db_real_escape_string($_POST['login'])
			, $_SESSION['db']->db_real_escape_string($_POST['pwd'])
			, $_SESSION['db']->db_real_escape_string($GLOBALS['DSN']['user']['password'])
		));

		// Recherche d'erreur
		$sql = sprintf("
			SELECT *
			FROM `TBL_MESSAGES_SYSTEM`
			WHERE `utilisateur` = '%s'
			", $_SESSION['db']->db_real_escape_string($_POST['k'])
		);
		$result = $_SESSION['db']->db_interroge($sql);
		if (mysqli_num_rows($result) > 0) {
			// Une erreur est survenue et le compte n'a pas été créé
			while($row = $_SESSION['db']->db_fetch_assoc($result)) {
				$err[] = $row;
			}
		} else {
			// Le compte a été correctement créé
			header('Location:/index.php?k=cptok');
		}
		mysqli_free_result($result);	
	}
} else {
	// Si il s'agit d'un reset password
	if (is_null($row['beginning']) && is_null($row['end'])) {
		// Les champs du formulaire
		$champs = array(
			array(
				'type'		=> 'hidden'
				, 'name'	=> 'k'
				, 'value'	=> htmlentities($_REQUEST['k'])
			)
			, array(
				'type'		=> 'hidden'
				, 'name'	=> 'uid'
				, 'value'	=> (int) $row['nom']
			)
			, array(
				'type'		=> 'password'
				, 'label'	=> 'Nouveau mot de passe'
				, 'name'	=> 'pwd'
				, 'placeholder'	=> '****'
			)
			, array(
				'type'		=> 'password'
				, 'label'	=> 'Répétez le mot de passe'
				, 'name'	=> 'pwdchk'
				, 'placeholder'	=> '****'
			)
			, array(
				'type'		=> 'submit'
				, 'value'	=> 'Mettre à jour'
			)
		);
	} else {
		// Si il s'agit d'une création de compte
		$champs = array(
			array(
				'type'		=> 'hidden'
				, 'name'	=> 'k'
				, 'value'	=> htmlentities($_REQUEST['k'])
			)
			, array(
				'type'		=> 'text'
				, 'label'	=> 'Login'
				, 'name'	=> 'login'
				, 'placeholder'	=> 'Mon_login'
			)
			, array(
				'type'		=> 'password'
				, 'label'	=> 'Mot de passe'
				, 'name'	=> 'pwd'
				, 'placeholder'	=> '****'
			)
			, array(
				'type'		=> 'password'
				, 'label'	=> 'Répétez le mot de passe'
				, 'name'	=> 'pwd1'
				, 'placeholder'	=> '****'
			)
			, array(
				'type'		=> 'submit'
				, 'value'	=> 'Créer mon compte'
			)
		);
	}
	$form = array(
	'name'		=> 'fcrtAcct'
	, 'id'		=> 'fcrtAcct'
	, 'method'	=> 'POST'
	, 'classe'	=> 'w24 ng'
	, 'fieldsets'	=> array(
		array(
			'legend'	=> 'Création de mon compte'
			, 'display'	=> 'none'
			, 'row'		=> $champs
			)
		)
	);

	$smarty->assign('header', array('content' => 'TeamTime v' . VERSION));
	$smarty->display('header.tpl');
	$smarty->assign('form', $form);
	$smarty->display('html.form_ul.tpl');
}


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
