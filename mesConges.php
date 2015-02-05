<?php
/* tableauxCong.php
 *
 * Page gérant l'affichage de l'état de congés des agents
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

$requireAuthenticatedUser = true; // L'utilisateur doit être authentifié pour accéder à cette page
ob_start();

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
	$conf['page']['include']['class_article'] = 1; // Le script utilise class_article.inc.php'affichage de certaines pages (licence)
	$conf['page']['include']['smarty'] = 1; // Smarty sera utilisé sur cette page


/*
 * Configuration de la page
 */
        $conf['page']['titre'] = "Gestion des congés - TeamTime"; // Le titre de la page
// Définit la valeur de $DEBUG pour le script
// on peut activer le debug sur des parties de script et/ou sur certains scripts :
// $DEBUG peut être activer dans certains scripts de required et désactivé dans d'autres
	$DEBUG = true;
	$conf['page']['elements']['firePHP'] = true;

	/*
	 * Choix des éléments à afficher
	 */
	
	// Affichage du menu horizontal
	$conf['page']['elements']['menuHorizontal'] = true; //!empty($_SESSION['AUTHENTICATED']); // Le menu est affiché aux seules personnes loguées
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



	// Utilisation de jquery
	$conf['page']['javascript']['jquery'] = true;
	// Utilisation de jquery-ui
	$conf['page']['javascript']['jquery-ui'] = true;
	// Utilisation de ajax
	$conf['page']['javascript']['ajax'] = true;
	// Utilisation de grille2.js.php
	$conf['page']['javascript']['grille2'] = false;
	// Utilisation de tableauxCong.js.php
	$conf['page']['javascript']['conG'] = true;

	// Feuilles de styles
	// Utilisation de la feuille de style general.css
	$conf['page']['stylesheet']['general'] = true;
	$conf['page']['stylesheet']['grille'] = true;
	// La feuille de style pour jquery-ui
	$conf['page']['stylesheet']['jquery-ui'] = true;

	// Compactage des pages
	$conf['page']['compact'] = false;
/*
 * Fin de la configuration de la page
 */

require 'required_files.inc.php';

// Année du tableau de congés (année courante par défaut)
$year = (!empty($_GET['year']) ? sprintf("%04d", $_GET['year']) : date('Y') - 1);

$uid = NULL;

$affectation = $_SESSION['utilisateur']->affectationOnDate(date('Y-m-d'));

// Mise à jour du récapitulatif des congés pour tout le monde
$_SESSION['db']->db_interroge(sprintf("CALL recapConges(%d)", $year));

if ($_SESSION['utilisateur']->hasRole('admin')) {
	$sql = "SELECT *
		FROM TBL_VACANCES_RECAP
		ORDER BY
		centre,
		team,
		poids,
		nom,
		year,
		did
		"
	;
	if (array_key_exists('uid', $_GET)) {
		$uid = (int) $_GET['uid'];
	}
	$smarty->assign('help', article::article('help conges'));
} elseif ($_SESSION['utilisateur']->hasRole('editeurs') || $_SESSION['utilisateur']->hasRole('teamEdit')) {
	$sql = sprintf("
		SELECT *
		FROM TBL_VACANCES_RECAP
		WHERE centre = '%s'
		AND team = '%s'
		ORDER BY
		poids,
		nom,
		year,
		did
		"
		, $affectation['centre']
		, $affectation['team']
	);
	if (array_key_exists('uid', $_GET)) {
		$uid = (int) $_GET['uid'];
	}
	$smarty->assign('help', article::article('help conges'));
} else {
	$uid = $_SESSION['utilisateur']->uid();
	$sql = "SELECT *
		FROM TBL_VACANCES_RECAP
		WHERE uid = $uid
		ORDER BY year, did";
	$smarty->assign('help', article::article('help conges'));
}
$results = $_SESSION['db']->db_interroge($sql);
while ($res = $_SESSION['db']->db_fetch_assoc($results)) {
	if ($_SESSION['utilisateur']->hasRole('admin') || $_SESSION['utilisateur']->hasRole('editeurs') || $_SESSION['utilisateur']->hasRole('teamEdit')) {
		$onglets[$res['centre']][$res['team']][$res['year']][] = $res;
	}
	if (!is_null($uid) && $res['uid'] == $uid) {
		$conges[$res['year']]['decompte'][] = $res;
	}
}
mysqli_free_result($results);

$smarty->assign('onglets', $onglets);
$smarty->display('tableauxCong2.tpl');

$smarty->display('help.tpl');

// Le détail des congés d'un utilisateur est demandé
if (!is_null($uid)) {
	$sql = sprintf("
		SELECT u.nom AS nom,
		d.did,
		u.uid,
		dispo,
		nom_long,
		year,
		date,
		l.sdid,
		quantity
		FROM TBL_VACANCES AS v,
		TBL_L_SHIFT_DISPO AS l,
		TBL_DISPO AS d,
		TBL_USERS AS u
		WHERE u.uid = l.uid
		AND l.uid = %d
		AND l.sdid = v.sdid
		AND l.did = d.did
		AND `type decompte` = 'conges'
		AND year BETWEEN %d AND %d + 1
		ORDER BY year, d.did, date ASC
		", $uid
		, $year
		, $year
	);
	$result = $_SESSION['db']->db_interroge($sql);
	while($row = $_SESSION['db']->db_fetch_assoc($result)) {
		$date = new Date($row['date']);
		$row['date'] = $date->formatDate();
		$conges[$row['year']]['detail'][$row['dispo']][] = $row;
	}
	mysqli_free_result($result);

	$smarty->assign('conges', $conges);
	$smarty->display('mesConges.tpl');
}

/*
 * Informations de debug
 */
include 'debug.inc.php';

// Affichage du bas de page
$smarty->display('footer.tpl');

ob_end_flush();

?>
