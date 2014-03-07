<?php
/* administration.php
 *
 * Ajout de briefing, période de charge, vacances scolaires
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

$requireEditeur = true; // L'utilisateur doit être admin pour accéder à cette page

/*
 * INCLUDES
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


/*
 * Configuration de la page
 */
        $conf['page']['titre'] = "Briefing, période de charge et vacances scolaires"; // Le titre de la page
// Définit la valeur de $DEBUG pour le script
// on peut activer le debug sur des parties de script et/ou sur certains scripts :
// $DEBUG peut être activer dans certains scripts de required et désactivé dans d'autres
	$DEBUG = true;
	$conf['page']['elements']['firePHP'] = true;

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
	
	// Affichage des timeInfos
	$conf['page']['elements']['timeInfo'] = true;
	// Affichage de l'utilisation mémoire
	$conf['page']['elements']['memUsage'] = true;
	// Affichage des WherewereU
	$conf['page']['elements']['whereWereU'] = true;
	// Affichage du lastError
	$conf['page']['elements']['lastError'] = true;
	// Affichage du lastErrorMessage
	$conf['page']['elements']['lastErrorMessage'] = true;
	// Affichage des messages de debug
	$conf['page']['elements']['debugMessages'] = true;


	// Gestion des briefings
	$conf['page']['elements']['intervalDate'] = true;

	// Utilisation de jquery
	$conf['page']['javascript']['jquery'] = true;
	// Utilisation de ajax
	$conf['page']['javascript']['ajax'] = true;
	// Utilisation de grille2.js.php
	$conf['page']['javascript']['grille2'] = false;
	// Utilisation de online
	$conf['page']['javascript']['online'] = true;

	// Feuilles de styles
	// Utilisation de la feuille de style general.css
	$conf['page']['stylesheet']['general'] = true;
	// Utilisation de la feuille de style online.css
	$conf['page']['stylesheet']['online'] = true;

	// Compactage des pages
	$conf['page']['compact'] = false;
/*
 * Fin de la configuration de la page
 */

require 'required_files.inc.php';

$forms = array(
	'briefing'	=> array(
		'table'		=> 'TBL_BRIEFING'
		,'intitule'	=> 'briefings'
		,'t'		=> "0" // Permet de connaître la table à modifier
	)
	,'vacances'		=> array(
		'intitule'	=> 'vacances scolaires'
		,'table'	=> 'TBL_VACANCES_SCOLAIRES'
		,'t'		=> "1" // Permet de connaître la table à modifier
	)
	,'charge'		=> array(
		'intitule'	=> 'périodes de charge'
		,'table'	=> 'TBL_PERIODE_CHARGE'
		,'t'		=> "2" // Permet de connaître la table à modifier
	)
);

// Choix des éléments à gérer (vacances, charge, briefing)
$get = $_GET['q'];

$titres = array();
$datas = array();
$arr = $forms[$get];
// Recherche des évènements déjà existant et postérieurs à la date courante
$sql = sprintf("
	SELECT `id`
	, `dateD`
	, `dateF`
	, `description`
	FROM `%s`
	WHERE `dateF` > NOW()
	AND `centre` = '%s'
	", $arr['table']
	, $_SESSION['utilisateur']->centre()
);
$result = $_SESSION['db']->db_interroge($sql);
$a = array();
while ($row = $_SESSION['db']->db_fetch_array($result)) {
	$dateD = new Date($row[1]);
	$dateF = new Date($row[2]);
	$a[] = array(
		'id'		=> $row[0]
		,'dateD'	=> $dateD->formatDate('fr')
		,'dateF'	=> $dateF->formatDate('fr')
		,'description'	=> $row[3]
		,'t'		=> $arr['t']
	);
}
mysqli_free_result($result);
$datas = $a;
$titres = $arr;

$smarty->assign('titre', $titres);
$smarty->assign('datas', $datas);
$smarty->assign('descLength', 255); // longueur max de la description
$smarty->display('formulaireGestion.tpl');


/*
 * Informations de debug
 */
include 'debug.inc.php';

// Affichage du bas de page
$smarty->display('footer.tpl');

?>
