<?php
/* tableauxEvenSpec.php
 *
 * Page gérant l'affichage de l'état des évènements spéciaux des agents
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
	$conf['page']['include']['smarty'] = 1; // Smarty sera utilisé sur cette page


/*
 * Configuration de la page
 */
        $conf['page']['titre'] = "Gestion des compteurs - TeamTime"; // Le titre de la page
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
	// Utilisation de ajax
	$conf['page']['javascript']['ajax'] = true;
	// Utilisation de grille2.js.php
	$conf['page']['javascript']['grille2'] = false;
	// Utilisation de tableauxCong.js.php
	$conf['page']['javascript']['conG'] = true;

	// Feuilles de styles
	// Utilisation de la feuille de style general.css
	$conf['page']['stylesheet']['general'] = true;
	// La feuille de style pour la page de gestion des congés
	$conf['page']['stylesheet']['conG'] = true;

	// Compactage des pages
	$conf['page']['compact'] = false;
/*
 * Fin de la configuration de la page
 */

require 'required_files.inc.php';

// Année du tableau de congés (année courante par défaut)
$year = date('Y');
$titre = "Évènements";

$users = utilisateursDeLaGrille::getInstance()->getActiveUsersFromTo("$year-01-01", "$year-12-31", $_SESSION['centre'], $_SESSION['team']);
$uids = array(); // Un tableau des uid des utilisateurs
foreach ($users as $user) {
	$uids[] = $user->uid();
}

$tab = array();
$sql = sprintf("
	SELECT `did`
	, `nom_long`
	FROM `TBL_DISPO`
	WHERE `need_compteur` IS TRUE
	AND `actif` IS TRUE
	AND `type decompte` != 'conges'
	AND `centre` = '%s'
	AND `team` = '%s'
	", $_SESSION['centre']
	, $_SESSION['team']
);
$results = $_SESSION['db']->db_interroge($sql);
$index = 0;
while ($res = $_SESSION['db']->db_fetch_assoc($results)) {
	$onglets[$index] = array('nom'	=> htmlspecialchars($res['nom_long'], ENT_COMPAT, 'UTF-8')
		,'quantity'		=> 10
		,'param'		=> $res['did']
	);
	// Recherche des évènements
	// Les évènements ne sont pas groupés par année
	// On recherche le nombre minimal de chaque évènement
	// ainsi que le maximum (pour définir le nombre de colonnes nécessaires)
	$sql = sprintf("
		SELECT `l`.`uid`, COUNT(`l`.`uid`) AS `compte`
		FROM `TBL_L_SHIFT_DISPO` `l`
		, `TBL_USERS` `u`
		WHERE `did` = %d
		AND `l`.`uid` = `u`.`uid`
		AND `actif` IS TRUE
		GROUP BY `l`.`uid`
		ORDER BY `compte` ASC
		", $res['did']
		, $_SESSION['centre']
		, $_SESSION['team']
	);
	$i = 0;
	$result = $_SESSION['db']->db_interroge($sql);
	while ($row = $_SESSION['db']->db_fetch_row($result)) {
		if (in_array($row[0], $uids)) {
			// On souhaite connaître le minimum,
			// soit donc le premier résultat retourné
			if (!$i++) {
				$min = $row[1] - 1; // On veut au moins une date pour l'utilisateur ayant le moins d'entrées
			} else {
				$onglets[$index]['quantity'] = $row[1] - $min;
			}
		}
	}
	// Le nombre de colonnes à afficher est défini
	// par le max - min + 1
	// TODO prévoir une alerte pour vérifier la nécessité de péréquations
	// lorsque la différence (min, max) est importante
	mysqli_free_result($result);

	// et on limite l'affichage des évènement à partir de ce nombre minimal
	// pour chaque utilisateur
	foreach ($uids as $uid) {
		$sql = sprintf("
			SELECT `uid`, `did`, `date`
			FROM `TBL_L_SHIFT_DISPO`
			WHERE `did` = %d
			AND `uid` = %d
			ORDER BY `date` ASC
			LIMIT %d, %d
			", $res['did']
			, $uid
			, $min
			, $onglets[$index]['quantity']
		);

		$r = $_SESSION['db']->db_interroge($sql);
		while ($s = $_SESSION['db']->db_fetch_assoc($r)) {
			$class = '';
			$date = new Date($s['date']);
			$tab[$res['did']][$s['uid']][] = array(	'date' => $date->formatDate()
				,'classe' => $class
			);
		}
	}
	mysqli_free_result($r);
	$index++;
}
mysqli_free_result($results);

$smarty->assign('year', $year);
$smarty->assign('titre', $titre);
$smarty->assign('onglets', $onglets);
$smarty->assign('users', $users);
$smarty->assign('tab', $tab);

$smarty->display('tableauxCong.tpl');

/*
 * Informations de debug
 */
include 'debug.inc.php';
firePhpLog($onglets, 'onglets');
firePhpLog($users, 'users');
firePhpLog($tab, 'tab');

// Affichage du bas de page
$smarty->display('footer.tpl');

ob_end_flush();

?>
