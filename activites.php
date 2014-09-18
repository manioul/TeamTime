<?php
// activites.php
//
// Ajoute de nouvelles activités disponible
// Les Editeurs peuvent ajouter de nouvelles activités qui seront disponibles pour leur équipe

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
$requireEditeur = true;

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
	$conf['page']['include']['bibliothequeMaintenance'] = false; // La bibliothèque des fonctions de maintenance est nécessaire
/*
 * Fin de la définition des include
 */


/*
 * Configuration de la page
 */
        $conf['page']['titre'] = sprintf("Gestion des activités"); // Le titre de la page
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



	// Utilisation de jquery
	$conf['page']['javascript']['jquery'] = true;
	// Utilisation de ajax
	$conf['page']['javascript']['ajax'] = false;
	// Utilisation de grille2.js.php
	$conf['page']['javascript']['grille2'] = false;
	// Utilisation de utilisateur.js
	$conf['page']['javascript']['utilisateur'] = false;
	// Utilisation de administration.js
	$conf['page']['javascript']['gestionTeam'] = true;

	// Feuilles de styles
	// Utilisation de la feuille de style general.css
	$conf['page']['stylesheet']['general'] = true;
	$conf['page']['stylesheet']['grille'] = true;
	$conf['page']['stylesheet']['grilleUnique'] = false;
	$conf['page']['stylesheet']['utilisateur'] = false;

	// Compactage des pages
	$conf['page']['compact'] = false;
	
/*
 * Fin de la configuration de la page
 */

require 'required_files.inc.php';

// Remonter une entrée
if (array_key_exists('u', $_GET) && array_key_exists('did', $_GET) && array_key_exists('poids', $_GET)) {
	$sql = sprintf("
		UPDATE `TBL_DISPO`
		SET `poids` = `poids` + 1
		WHERE `poids` = %d + 49
		AND `centre` = '%s'
		AND `team` = '%s'
		", $_GET['poids']
		, $_SESSION['utilisateur']->centre()
		, $_SESSION['utilisateur']->team()
	);
	$_SESSION['db']->db_interroge($sql);
	$sql = sprintf("
		UPDATE `TBL_DISPO`
		SET `poids` = `poids` - 1
		WHERE `did` = %d
		AND `centre` = '%s'
		AND `team` = '%s'
		", $_GET['did']
		, $_SESSION['utilisateur']->centre()
		, $_SESSION['utilisateur']->team()
	);
	$_SESSION['db']->db_interroge($sql);
}
// Redescendre une entrée
if (array_key_exists('d', $_GET) && array_key_exists('did', $_GET) && array_key_exists('poids', $_GET)) {
	// On remonte l'entrée suivante
	$sql = sprintf("
		UPDATE `TBL_DISPO`
		SET `poids` = `poids` - 1
		WHERE `poids` = %d + 51
		AND `centre` = '%s'
		AND `team` = '%s'
		LIMIT 1
		", $_GET['poids']
		, $_SESSION['utilisateur']->centre()
		, $_SESSION['utilisateur']->team()
	);
	$_SESSION['db']->db_interroge($sql);
	$sql = sprintf("
		UPDATE `TBL_DISPO`
		SET `poids` = %d + 51
		WHERE `did` = %d
		AND `centre` = '%s'
		AND `team` = '%s'
		", $_GET['poids']
		, $_GET['did']
		, $_SESSION['utilisateur']->centre()
		, $_SESSION['utilisateur']->team()
	);
	$_SESSION['db']->db_interroge($sql);
}
// (Dés)activer une entrée
if (array_key_exists('a', $_GET) && array_key_exists('did', $_GET)) {
	$sql = sprintf("
		UPDATE `TBL_DISPO`
		SET `actif` = %s
		WHERE `did` = %d
		AND `centre` = '%s'
		AND `team` = '%s'
		", ($_GET['a'] == 0 ? 'FALSE' : 'TRUE')
		, $_GET['did']
		, $_SESSION['utilisateur']->centre()
		, $_SESSION['utilisateur']->team()
	);
	$_SESSION['db']->db_interroge($sql);
}

// Traitement du formulaire
if (array_key_exists('add', $_POST)) {
	$needCompteur = 'FALSE';
	$typeDecompte = 'NULL';
	// Traitement des activités qui sont comptabilisées en fin de cycle (compteur 'dispo' et need_compteur == NULL)
	if (array_key_exists('isd', $_POST) && 'on' == $_POST['isd']) {
		$typeDecompte = 'dispo';
	}
	// Traitement des activités qui demandent un compteur
	if (array_key_exists('needCpt', $_POST) && 'on' == $_POST['needCpt']) {
		if (array_key_exists('dp', $_POST)) {
			$typeDecompte = $_SESSION['db']->db_real_escape_string($_POST['dp']);
			$needCompteur = 'TRUE';
		}
	}
	// Le poids en dessous de 50 est réservé aux activités tous centres / toutes équipes
	$sql = sprintf("
		INSERT INTO `TBL_DISPO`
		(`dispo`, `jours possibles`, `peut poser`, `classes possibles`, `absence`, `actif`, `type decompte`, `need_compteur`, `nom_long`, `poids`, `centre`, `team`)
		VALUES
		('%s', '%s', '%s', '%s', %1.1f, TRUE, '%s', %s, '%s', 50, '%s', '%s')
		", array_key_exists('nc', $_POST) ? $_SESSION['db']->db_real_escape_string($_POST['nc']) : ''
		, array_key_exists('jp', $_POST) ? $_SESSION['db']->db_real_escape_string($_POST['jp']) : ''
		, array_key_exists('pp', $_POST) ? $_SESSION['db']->db_real_escape_string($_POST['pp']) : ''
		, array_key_exists('cp', $_POST) ? $_SESSION['db']->db_real_escape_string($_POST['cp']) : ''
		, $_POST['absence']
		, $typeDecompte
		, $needCompteur
		, array_key_exists('nl', $_POST) ? $_SESSION['db']->db_real_escape_string($_POST['nl']) : ''
		, $_SESSION['utilisateur']->centre()
		, $_SESSION['utilisateur']->team()
	);
	$_SESSION['db']->db_interroge($sql);
}

// Recherche des compteurs déjà disponibles
$sql = sprintf("
	SELECT DISTINCT `type decompte`
	FROM `TBL_DISPO`
	WHERE `type decompte` != 'conges'
	AND `type decompte` != 'dispo'
	AND `type decompte` IS NOT NULL
	AND (`centre` = '%s' OR `centre` = 'all')
	AND (`team` = '%s' OR `team` = 'all')
	", $_SESSION['utilisateur']->centre()
	, $_SESSION['utilisateur']->team()
);
$result = $_SESSION['db']->db_interroge($sql);
$i = 0;
$compteurs[$i]['value'] = ' ';
$compteurs[$i]['content'] = ' ';
while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
	 $i++;
	 $compteurs[$i]['value'] = $row['type decompte'];
	 $compteurs[$i]['content'] = $row['type decompte'];
}
mysqli_free_result($result);

/* TODO Permettre l'édition d'une activité déjà saisie
if (array_key_exists('e', $_POST) && $_POST['e'] == 1 && array_key_exists('did', $_POST)) {
	$sql = sprintf("SELECT * FROM `TBL_DISPO`
		WHERE `did` = %d"
		, $_POST['did']
	);
	$result = $_SESSION['db']->db_interroge($sql);
	$selected = $_SESSION['db']->db_fetch_assoc($result);
	mysqli_free_result($result);	
}
 */

// Recherche les grades disponibles dans l'équipe
$sql = sprintf("
	SELECT DISTINCT `grade`
	FROM `TBL_AFFECTATION`
	WHERE (`centre` = '%s' OR `centre` = 'all')
	AND (`team` = '%s' OR `team` = 'all')
	UNION
	SELECT `login` AS `grade`
	FROM `TBL_USERS` AS `u`
	, `TBL_AFFECTATION` AS `a`
	WHERE `u`.`uid` = `a`.`uid`
	AND `beginning` <= '%s'
	AND `end` >= '%s'
	AND `a`.`centre` = '%s'
	AND `a`.`team` = '%s'
	", $_SESSION['utilisateur']->centre()
	, $_SESSION['utilisateur']->team()
	, date('Y-m-d')
	, date('Y-m-d')
	, $_SESSION['utilisateur']->centre()
	, $_SESSION['utilisateur']->team()
);
$result = $_SESSION['db']->db_interroge($sql);
$i = 0;
$grades[$i]['value'] = 'all';
$grades[$i]['content'] = 'all';
$grades[$i]['selected'] = 'selected';
$i++;
$grades[$i]['value'] = 'teamEdit';
$grades[$i]['content'] = 'teamEdit';
while($row = $_SESSION['db']->db_fetch_assoc($result)) {
	 $i++;
	 $grades[$i]['value'] = $row['grade'];
	 $grades[$i]['content'] = $row['grade'];
}
mysqli_free_result($result);

// Création du formulaire de création de nouvelle activité
$form = array(
	'name'	=> "fActvt"
	, "id"	=> "fActvt"
	, 'method'	=> "POST"
	, 'action'	=> ""
	, 'classe'	=> "ng"
	, 'legend'	=> "Ajouter une activité à mon équipe"
	, 'pp'		=> array(
		'type'		=> "select"
		, 'label'	=> "Autorisés à poser"
		, 'name'	=> "p[]"
		, 'id'		=> "p"
		, 'multiple'	=> 'multiple'
		, 'onchange'	=> "displayVals('p')"
		, 'options'	=> $grades
	)
	, 'cp'		=> array(
		'type'		=> "select"
		, 'label'	=> "Classes recevant l'activité"
		, 'name'	=> "c[]"
		, 'id'		=> "c"
		, 'multiple'	=> 'multiple'
		, 'onchange'	=> "displayVals('c')"
		, 'options'	=> $grades
	)
	, 'jp'		=> array_merge(
		array(
			'type'		=> "select"
			, 'label'	=> "Jours possibles"
			, 'id'		=> "j"
			, 'multiple'	=> 'multiple'
			, 'onchange'	=> "displayVals('j')"
		)
		, Cycle::listeCycle('j[]', NULL, NULL, 'all')
	)
	, 'absence'	=> array(
		'type'		=> "select"
		, 'label'	=> "Absence"
		, 'name'	=> "absence"
		, 'options'	=> array(
			array(
				'value'		=> 1
				, 'content'	=> "absent"
			)
			, array(
				'value'		=> .5
				, 'content'	=> "demi-équipe"
			)
			, array(
				'value'		=> 0
				, 'content'	=> "présent"
			)
		)
	)
	, 'isDispo' => array(
		'type'		=> "checkbox"
		, 'label'	=> "L'activité doit être comptabilisée à chaque fin de cycle"
		, 'name'	=> "isd"
		, 'id'		=> "isd"
	)
	, 'needCompteur' => array(
		'type'		=> "checkbox"
		, 'label'	=> "L'activité nécessite un compteur"
		, 'name'	=> "needCpt"
		, 'id'		=> "needCpt"
	)
	, 'typeDecompte' => array(
		'type'		=> "select"
		, 'name'	=> "compteur"
		, 'id'		=> "compteur"
		, 'label'	=> "Nom du compteur"
		, 'options'	=> $compteurs
	)
	, 'validation'	=> array(
		'type'		=> 'submit'
		, 'name'	=> "add"
		, 'value'	=> "Ajouter l'activité"
	)
); 

$smarty->assign('form', $form);

$smarty->display('activites.tpl');

/*
 * Liste des activités existantes
 */
$activites = array(0	=> array(
	'did'			=> 'Id'
	, 'nc'			=> "Nom court"
	, 'nl'			=> "Nom long"
	, 'jp'			=> "Jours possibles"
	, 'classes'		=> "Classes à qui l'activité est attribuable"
	, 'pp'			=> "Peut poser"
	, 'absence'		=> "Absence"
	, 'dp'			=> "Nom compteur"
	, 'needCpt'		=> "Nécessite un compteur"
	, 'poids'		=> "Poids"
	, 'actif'		=> "Actif"
	)
);
$sql = sprintf("
	SELECT
	`did`
	, `dispo` AS `nc`
	, `nom_long` AS `nl`
	, `Jours possibles` AS `jp`
	, `classes possibles` AS `classes`
	, `peut poser` AS `pp`
	, `absence`
	, `type decompte` AS `dp`
	, `need_compteur` AS `needCpt`
	, `poids` - 50 AS `poids`
	, `actif`
	FROM `TBL_DISPO`
	WHERE `centre` = '%s'
	AND `team` = '%s'
	ORDER BY `actif` DESC
	, `poids` ASC
	", $_SESSION['utilisateur']->centre()
	, $_SESSION['utilisateur']->team()
);
$result = $_SESSION['db']->db_interroge($sql);
$i = 1;
while($row = $_SESSION['db']->db_fetch_assoc($result)) {
	$activites[$i] = $row;
	$i++;
}
mysqli_free_result($result);

$smarty->assign('activites', $activites);
$smarty->display('listeActivites.tpl');

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
