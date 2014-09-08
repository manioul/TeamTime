<?php
/* index.php
 *
 * Page de login
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

$conf['page']['elements']['firePHP'] = 1;
$conf['page']['include']['constantes'] = 1; // Ce script nécessite la définition des constantes
$conf['page']['include']['errors'] = 1; // le script gère les erreurs avec errors.inc.php
$conf['page']['include']['class_debug'] = NULL; // La classe debug est nécessaire à ce script
$conf['page']['include']['globalConfig'] = 1; // Ce script nécessite config.inc.php
$conf['page']['include']['init'] = 1; // la session est initialisée par init.inc.php
$conf['page']['include']['globals_db'] = 0; // Le DSN de la connexion bdd est stockée dans globals_db.inc.php
$conf['page']['include']['class_db'] = 0; // Le script utilise class_db.inc.php
$conf['page']['include']['session'] = 1; // Le script utilise les sessions par session.imc
$conf['page']['include']['classUtilisateur'] = NULL; // Le sript utilise uniquement la classe utilisateur (auquel cas, le fichier class_utilisateur.inc.php
$conf['page']['include']['class_utilisateurGrille'] = 1; // Le sript utilise la classe utilisateurGrille
$conf['page']['include']['class_cycle'] = NULL; // La classe cycle est nécessaire à ce script (remplace grille.inc.php
$conf['page']['include']['class_article'] = 1; // Le script utilise class_article.inc.php'affichage de certaines pages (licence)
$conf['page']['include']['smarty'] = 1; // Smarty sera utilisé sur cette page



/*
 * Configuration de la page
 */
        $conf['page']['titre'] = "TeamTime"; // Le titre de la page
// Définit la valeur de $DEBUG pour le script
// on peut activer le debug sur des parties de script et/ou sur certains scripts :
// $DEBUG peut être activer dans certains scripts de required et désactivé dans d'autres
	$DEBUG = true;
	$conf['page']['elements']['firePHP'] = true;

	/*
	 * Choix des éléments à afficher
	 */
	
	// Affichage du menu horizontal
	$conf['page']['elements']['menuHorizontal'] = !empty($_SESSION['AUTHENTICATED']); // Le menu est affiché aux seules personnes loguées
	// Affichage du choix du thème
	$conf['page']['elements']['choixTheme'] = false;
	// Affichage du menu d'administration
	$conf['page']['elements']['menuAdmin'] = false;
	
	// éléments de debug
	
	// Affichage des timeInfos
	$conf['page']['elements']['timeInfo'] = false;
	// Affichage de l'utilisation mémoire
	$conf['page']['elements']['memUsage'] = false;
	// Affichage des WherewereU
	$conf['page']['elements']['whereWereU'] = false;
	// Affichage du lastError
	$conf['page']['elements']['lastError'] = false;
	// Affichage du lastErrorMessage
	$conf['page']['elements']['lastErrorMessage'] = false;
	// Affichage des messages de debug
	$conf['page']['elements']['debugMessages'] = false;



	// Utilisation de jquery
	$conf['page']['javascript']['jquery'] = true;
	// Utilisation de ajax
	$conf['page']['javascript']['ajax'] = true;
	// Utilisation de grille2.js.php
	$conf['page']['javascript']['grille2'] = false;
	// Pour l'affichage du formulaire de connexion
	$conf['page']['javascript']['index'] = true;

	// Feuilles de styles
	// Utilisation de la feuille de style general.css
	$conf['page']['stylesheet']['general'] = false;
	$conf['page']['stylesheet']['index'] = true;

	// Compactage des pages
	$conf['page']['compact'] = true;
	
/*
 * Fin de la configuration de la page
 */
	
ob_start(); // Obligatoire pour firePHP

require 'required_files.inc.php';

if (isset($_SESSION['AUTHENTICATED']) && empty($_GET['norights']) && empty($_GET['k'])) {
	if (isset($_GET['back'])) {
		header("Location:" . $_GET['back']);
	}
	header("Location:" . (is_null($_SESSION['utilisateur']->page()) ? "affiche_grille.php" : $_SESSION['utilisateur']->page() ));
}

$fcon = array(
	'name'		=> "fConn"
	, 'id'		=> "fConn"
	, 'method'	=> "POST"
	, 'action'	=> "logon.php"
	, 'classe'	=> "ng"
	, 'fieldsets'	=> array(
		array(
			'display'	=> "none"
			, 'row'	=> array(
				array(
					'type'		=> "text"
					, 'name'	=> "login"
					, 'placeholder'	=> 'Login'
				)
				, array(
					'type'		=> "password"
					, 'name'	=> "pwd"
					, 'placeholder'	=> '****'
				)
				, array(
					'type'		=> "submit"
					, 'value'	=> "Connexion"
				)
			)
		)
	)
);
$form = array(
	'name'		=> "fSignup"
	, 'id'		=> "fSignup"
	, 'method'	=> "POST"
	, 'action'	=> "signup.php"
	, 'classe'	=> "ng"
	, 'fieldsets'	=> array(
		array(
			'titre'		=> "Création de compte"
			, 'display'	=> "none"
			, 'row'		=> array(
				array(
					'type'	=> 'text'
					, 'label'	=> 'Nom'
					, 'name'	=> 'iNom'
					//, 'placeholder'	=> 'Votre nom'
				)
				, array(
					'type'	=> 'text'
					, 'label'	=> 'Prénom'
					, 'name'	=> 'iPrenom'
					//, 'placeholder'	=> 'Votre prénom'
				)
				, array(
					'type'	=> 'email'
					, 'label'	=> 'Email'
					, 'name'	=> 'iEmail'
					, 'placeholder'	=> 'monnom@email.com'
				)
				, array_merge(
					array(
						'type'	=> 'select'
						, 'label'	=> "Centre"
					)
					, Affectation::listeAffectations('centre')
				)
				, array_merge(
					array(
						'type'	=> 'select'
						, 'label'	=> "Équipe"
					)
					, Affectation::listeAffectations('team')
				)
				, array(
					'type'	=> 'submit'
					, 'name'	=> 'iSubmit'
					, 'value'	=> 'Créer le compte'
				)
			)
		)
	)
);
$fRecover = array(
	'name'		=> "fRecover"
	, 'id'		=> "fRecover"
	, 'method'	=> "POST"
	, 'action'	=> "ajax.php"
	, 'classe'	=> "ng"
	, 'fieldsets'	=> array(
		array(
			'display'	=> "yes"
			, 'legend'	=> "Mot de passe oublié ?"
			, 'row'		=> array(
				array(
					'type'		=> "hidden"
					, 'name'	=> "ajax"
					, 'value'	=> 'true'
				)
				, array(
					'type'		=> 'hidden'
					, 'name'	=> 'form'
					, 'value'	=> 'RP'
				)
				, array(
					'type'		=> 'hidden'
					, 'name'	=> 'cachemoi'
					, 'value'	=> '1'
				)
				, array(
					'type'		=> "hidden"
					, 'name'	=> "w"
					, 'value'	=> "resetPwd"
				)
				, array(
					'type'		=> "text"
					, 'label'	=> ""
					, 'name'	=> "email"
					, 'placeholder'	=> "recover@my.id"
					, 'title'	=> "Saisissez votre email pour récupérer vos identifiants"
				)
				, array(
					'type'		=> "submit"
					, 'name'	=> "reset"
					, 'value'	=> "Envoyer"
				)
			)
		)
	)
);
$smarty->assign('fRecover', $fRecover);
$smarty->assign('fcon', $fcon);
$smarty->assign('form', $form);

if (isset($_GET['k'])) {
	switch($_GET['k']) {
	case 'licence':
		$article = new Article(1);
		if ($article->actif()) {
			$contenu = array(
				"id1" => array(
					'titre'	=> $article->titre()
					,'texte' => $article->texte()
				)
			);
		}
		firePhpLog($article->titre(), 'titre article');
		firePhpLog($article->texte(), 'texte article');
		$nav = array(
			'1'	=> "accueil"
			,'2'	=> "connexion"
			,'3'	=> "support"
			,'4'	=> "signup"
		);
		$content[1] = ""; // On ne veut pas du message affiché au survol d'accueil
		break;
	case 'source':
		$article = new Article(2);
		if ($article->actif()) {
			$contenu = array(
				"id1" => array(
					'titre'	=> $article->titre()
					,'texte' => $article->texte()
				)
			);
		}
		firePhpLog($article->titre(), 'titre article');
		firePhpLog($article->texte(), 'texte article');
		$nav = array(
			'1'	=> "accueil"
			,'2'	=> "connexion"
			,'3'	=> "support"
			,'4'	=> "signup"
		);
		$content[1] = ""; // On ne veut pas du message affiché au survol d'accueil
		break;
	case 'contrib':
		$article = new Article(3);
		if ($article->actif()) {
			$contenu = array(
				"id1" => array(
					'titre'	=> $article->titre()
					,'texte' => $article->texte()
				)
			);
		}
		firePhpLog($article->titre(), 'titre article');
		firePhpLog($article->texte(), 'texte article');
		$nav = array(
			'1'	=> "accueil"
			,'2'	=> "connexion"
			,'3'	=> "support"
			,'4'	=> "signup"
		);
		$content[1] = ""; // On ne veut pas du message affiché au survol d'accueil
		break;
	case 'compteok':
		$article = new Article(2);
		if ($article->actif()) {
			$contenu = array(
				"id1" => array(
					'titre'	=> $article->titre()
					,'texte' => $article->texte()
				)
			);
		}
		$nav = array(
			'1'	=> "accueil"
			,'2'	=> "connexion"
			,'3'	=> "support"
			,'4'	=> "signup"
		);
		$content[1] = ""; // On ne veut pas du message affiché au survol d'accueil
		break;
	default:
		$nav = array(
			'1'	=> "accueil"
			,'2'	=> "connexion"
			,'3'	=> "support"
			,'4'	=> "signup"
		);
	}
} else {
	$nav = array(
		'1'	=> "accueil"
		,'2'	=> "connexion"
		,'3'	=> "support"
		,'4'	=> "signup"
	);
}

if (isset($_SESSION['AUTHENTICATED']) && !isset($_GET['norights'])) {
	unset($nav[2]);
	$nav[1] = "shift";
}

foreach ($nav as $key => $val) {
	if (!isset($content[$key]) && $val != "" && file_exists("$val.inc.php")) { // Pour ne pas inclure les valeurs par défaut du contenu de chaque élément, il faut donner une valeur à $content[$key]
		include("$val.inc.php");
		$content[$key] = empty($exported) ? '' : $exported ;
	}
}

$smarty->assign('nav', $nav);
if (isset($content[1])) $smarty->assign('content1', $content[1]);
if (isset($content[2])) $smarty->assign('content2', $content[2]);
if (isset($content[3])) $smarty->assign('content3', $content[3]);
if (isset($content[4])) $smarty->assign('content4', $content[4]);
if (!empty($contenu)) $smarty->assign('contenu', $contenu);

if (isset($_SESSION['AUTHENTICATED'])) {
	if (isset($_GET['norights'])) {
		$smarty->assign('erreur', "Droits insuffisants... Déloguez-vous et reconnectez-vous avec un compte ayant des droits suffisants.");
		$smarty->display('erreur.tpl');
		$smarty->assign('salt', mt_rand());
	} else {
	}
} else { // Présente le formulaire de login en pleine page
	$smarty->assign('salt', mt_rand());
}

$smarty->display('indexTtm.tpl');
$smarty->display('resetPwd.tpl');

/*
 * Informations de debug
 */
include 'debug.inc.php';
firePhpLog($conf, '$conf');
firePhpLog($javascript, '$javascript');

// Affichage du bas de page
$smarty->display('emptyFooter.tpl');

ob_end_flush();
?>
