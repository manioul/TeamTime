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

$requireTeamEdit = true; // L'utilisateur doit être authentifié pour accéder à cette page
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
$year = (!empty($_GET['year']) ? sprintf("%04d", $_GET['year']) : date('Y'));

$affectation = $_SESSION['utilisateur']->affectationOnDate(date('Y-m-d'));

if (array_key_exists('uid', $_GET)) {
		if (preg_match('/^\d+-\d+$/', $row['dlCong'])) {
			$row['dlCong'] = $year . '-' . $row['dlCong'];
		}
		$dlCong = new Date($row['dlCong']);
		$row['dlCong'] = $dlCong->formatDate();
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
		ORDER BY year, date ASC
		", (int) $_GET['uid']
		, $year
		, $year
	);
	$result = $_SESSION['db']->db_interroge($sql);
	while($row = $_SESSION['db']->db_fetch_assoc($result)) {
		$date = new Date($row['date']);
		$row['date'] = $date->formatDate();
		$detail[$row['dispo']][] = $row;
	}
	mysqli_free_result($result);
	$smarty->assign('detail', $detail);
}
if (array_key_exists('ADMIN', $_SESSION)) {
	$sql = sprintf("
		SELECT a.centre,
		a.team,
		u.uid,
		nom,
		d.did,
		dispo,
		nom_long,
		year,
		COUNT(v.sdid) AS déposé,
		quantity - COUNT(v.sdid) AS reliquat,
		quantity
		FROM TBL_USERS AS u,
		TBL_L_SHIFT_DISPO AS l,
		TBL_DISPO AS d,
		TBL_VACANCES AS v,
		TBL_AFFECTATION AS a
		WHERE u.uid = l.uid
		AND u.uid = a.uid
		AND NOW() BETWEEN beginning AND end
		AND l.sdid = v.sdid
		AND l.did = d.did
		AND year >= %d
		GROUP BY l.uid,
		l.did,
		year
		ORDER BY
		a.centre,
		a.team,
		u.poids,
		year
		", $year
	);
} elseif (array_key_exists('EDITEURS', $_SESSION)) {
	$sql = sprintf("
		SELECT a.centre,
		a.team,
		u.uid,
		nom,
		d.did,
		dispo,
		nom_long,
		year,
		COUNT(v.sdid) AS déposé,
		quantity - COUNT(v.sdid) AS reliquat,
		quantity
		FROM TBL_USERS AS u,
		TBL_L_SHIFT_DISPO AS l,
		TBL_DISPO AS d,
		TBL_VACANCES AS v,
		TBL_AFFECTATION AS a
		WHERE u.uid = l.uid
		AND u.uid = a.uid
		AND NOW() BETWEEN beginning AND end
		AND l.sdid = v.sdid
		AND l.did = d.did
		AND year >= %d
		AND a.centre = '%s'
		AND a.team = '%s'
		GROUP BY l.uid,
		l.did,
		year
		ORDER BY
		a.centre,
		a.team,
		u.poids,
		year
		", $year
		, $affectation['centre']
		, $affectation['team']
	);
}
$results = $_SESSION['db']->db_interroge($sql);
while ($res = $_SESSION['db']->db_fetch_assoc($results)) {
	$onglets[$res['centre']][$res['team']][] = $res;
}
mysqli_free_result($results);

$smarty->assign('onglets', $onglets);

$smarty->display('tableauxCong2.tpl');

/*
 * Informations de debug
 */
include 'debug.inc.php';

// Affichage du bas de page
$smarty->display('footer.tpl');

ob_end_flush();

?>
