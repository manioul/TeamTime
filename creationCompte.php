<?php
/* creationCompte.php
 *
 * Page gérer les comptes des utilisateurs
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

$requireAdmin = true; // L'utilisateur doit être admin pour accéder à cette page

ob_start(); // Obligatoire pour firePHP

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
	$conf['page']['include']['class_cycle'] = NULL; // La classe cycle est nécessaire à ce script (remplace grille.inc.php
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
	$conf['page']['elements']['menuHorizontal'] = true;//!empty($_SESSION['AUTHENTICATED']); // Le menu est affiché aux seules personnes loguées
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



	// Utilisation de jquery
	$conf['page']['javascript']['jquery'] = true;
	// Utilisation de ajax
	$conf['page']['javascript']['ajax'] = true;
	// Utilisation de grille2.js.php
	$conf['page']['javascript']['grille2'] = false;
	// Utilisation de administration.js.php
	$conf['page']['javascript']['administration'] = true;

	// Feuilles de styles
	// Utilisation de la feuille de style general.css
	$conf['page']['stylesheet']['general'] = true;

	// Compactage des pages
	$conf['page']['compact'] = false;
/*
 * Fin de la configuration de la page
 */

require 'required_files.inc.php';


// Recherche des infos des utilisateurs
$infos = array();
$sql = "SELECT `nom` FROM `TBL_USERS` WHERE `locked` = FALSE AND `actif` = TRUE ORDER BY `poids` ASC";
$result = $_SESSION['db']->db_interroge($sql);
while ($row = $_SESSION['db']->db_fetch_array($result)) {
	$infos[] = $row[0];
}
mysqli_free_result($result);
$smarty->assign('infos', $infos);

firePhpLog($_SESSION, '$_SESSION');

$smarty->display('creationCompte.tpl');

/*
 * Informations de debug
 */
include 'debug.inc.php';

// Affichage du bas de page
$smarty->display('footer.tpl');

ob_end_flush(); // Obligatoire pour firePHP

?>
