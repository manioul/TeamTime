<?php
// bibliotheque_maintenance.inc.php
//
// Bibliothèque de fonctions pour assurer des opérations
// de maintenance de la base de données
// Recherche les incongruences et permet de les corriger

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
$requireAdmin = true;

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
	$conf['page']['include']['class_utilisateurGrille'] = 1; // Le sript utilise la classe utilisateurGrille
	$conf['page']['include']['class_cycle'] = 1; // La classe cycle est nécessaire à ce script (remplace grille.inc.php
/*
 * Fin de la définition des include
 */


require 'required_files.inc.php';

/*
 * Création d'une vue affichant nom, dispo, date et vacation
 */
function db_create_view_liste_dispo() {
	$_SESSION['db']->db_interroge("DROP VIEW IF EXISTS `VIEW_LIST_DISPO`");
	$_SESSION['db']->db_interroge("CREATE VIEW `VIEW_LIST_DISPO` AS
		SELECT `sdid`
		, `l`.`uid`
		, `nom`
		, `dispo`
		, `l`.`date`
		, `vacation`
		, YEAR(`l`.`date`) AS `year`
		, `pereq`
		FROM `TBL_L_SHIFT_DISPO` `l`
		, `TBL_USERS` `u`
		, `TBL_DISPO` `d`
		, `TBL_GRILLE` `g`
		, `TBL_CYCLE` `c`
		WHERE `l`.`date` = `g`.`date`
		AND `g`.`cid` = `c`.`cid`
		AND `u`.`uid` = `l`.`uid`
		AND `d`.`did` = `l`.`did`
		UNION
		SELECT `l`.`sdid`
		, `l`.`uid`
		, `nom`
		, `dispo`
		, `l`.`date`
		, `l`.`date`
		, `year`
		, `pereq`
		FROM `TBL_L_SHIFT_DISPO` `l`
		, `TBL_USERS` `u`
		, `TBL_DISPO` `d`
		, `TBL_VACANCES` `v`
		WHERE `l`.`date` = 0
		AND `l`.`sdid` = `v`.`sdid`
		AND `u`.`uid` = `l`.`uid`
		AND `d`.`did` = `l`.`did`
		ORDER BY `date`, `nom`
		");
}

/*
 * Création d'une vue pour les évènements spéciaux
 */
function db_create_view_evenements_speciaux() {
	$_SESSION['db']->db_interroge("DROP VIEW IF EXISTS `VIEW_LIST_EVEN`");
	$_SESSION['db']->db_interroge("CREATE VIEW `VIEW_LIST_EVEN` AS
		SELECT `l`.`sdid`
		, `l`.`date`
		, `u`.`nom`
		, `d`.`dispo`
		, `c`.`vacation`
		, `d`.`type decompte`
		 FROM `TBL_L_SHIFT_DISPO` `l`
		 , `TBL_DISPO` `d`
		 , `TBL_USERS` `u`
		 , `TBL_CYCLE` `c`
		 , `TBL_GRILLE` `g`
		  WHERE `l`.`date` = `g`.`date`
		  AND `g`.`cid` = `c`.`cid`
		  AND `u`.`uid` = `l`.`uid`
		  AND `d`.`did` = `l`.`did`
		  AND `d`.`type decompte` != 'conges'
		  AND `d`.`type decompte` != 'dispo'
		  AND `d`.`type decompte` != ''
		  ORDER BY `type decompte`,`date`, `nom`");
}

/*
 * Recherche les dispo multiples pour un même utilisateur, un même jour dans TBL_L_SHIFT_DISPO
 * Positionner $del permet de supprimer les dispo en double et identiques pour un même uid et une même date
 */
function search_double_l($del = 0) {
	$results = array();
	$i = 0;
	$sql = "SELECT COUNT(`sdid`) AS `surnombre`
		, `uid`
		, `date`
		FROM `TBL_L_SHIFT_DISPO`
		GROUP BY `uid`
		, `date`
		HAVING COUNT(`did`) > 1
		";

	$result = $_SESSION['db']->db_interroge($sql);
	while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
		$dispos =  array();
		$sql = sprintf("
			SELECT *
			FROM `VIEW_LIST_DISPO`
			WHERE `date` = '%s'
			AND `uid` = %d
			", $row['date']
			, $row['uid']
		);
		$r = $_SESSION['db']->db_interroge($sql);
		while ($wor = $_SESSION['db']->db_fetch_assoc($r)) {
			if (array_key_exists($wor['dispo'], $dispos)) {
				if ($del) {
					$sql = sprintf ("
						DELETE FROM `TBL_L_SHIFT_DISPO`
						WHERE `sdid` = %d
						", $wor['sdid']
					);
					$_SESSION['db']->db_interroge("$sql");
				}
			}
			$results[$i][] = $wor;
			$dispos[$wor['dispo']] = 1;

		}
		mysqli_free_result($r);
		$i++;
	}
	mysqli_free_result($result);
	return $results;
}

/*
 * Recherche des sdid identiques dans TBL_VACANCES
 * et non orphelins (sdid != 0)
 * si $del est positionné, les doubles sont supprimés s'ils
 * correspondent à un même évènement
 */
function search_double_sdid_v($del = 1) {
	// On vérifie si sdid est déjà la clé primaire de TBL_VACANCES
	// Car si c'est le cas, il ne peut y avoir de valeurs en double
	$result = $_SESSION['db']->db_interroge("SHOW COLUMNS FROM `TBL_VACANCES`");
	while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
		if ($row['Field'] == 'sdid' && $row['Key'] == 'PRI') {
			mysqli_free_result($result);
			return false;
		}
	}
	mysqli_free_result($result);

	$results = array();
	$i = 0;
	$sql = "SELECT COUNT(`sdid`) AS `surnombre`
		, `sdid`
		FROM `TBL_VACANCES`
		GROUP BY `sdid`
		HAVING `surnombre` > 1
		AND `sdid` != 0";
	$result = $_SESSION['db']->db_interroge($sql);
	while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
		$j = 0;
		$sql = sprintf("
			SELECT `w`.`date`
			, `w`.`etat`
			, `w`.`year`
			, `u`.`nom`
			, `d`.`dispo`
			, `c`.`vacation`
			, `v`.`sdid`
			FROM `TBL_VACANCES` `v`
			, `TBL_VACANCES_SAUVE` `w`
			, `TBL_USERS` `u`
			, `TBL_DISPO` `d`
			, `TBL_CYCLE` `c`
			, `TBL_GRILLE` `g`
			WHERE `sdid` = %d
			AND `d`.`did` = `w`.`did`
			AND `w`.`uid` = `u`.`uid`
			AND `v`.`vid` = `w`.`vid`
			AND `c`.`cid` = `g`.`cid`
			AND `g`.`date` = `w`.`date`
			AND `g`.`team` = '9e'
			AND `g`.`centre` = 'athis'
			AND `c`.`team` = 'all'
			AND `c`.`centre` = 'athis'
			", $row['sdid']
		);
		$r = $_SESSION['db']->db_interroge($sql);
		while ($wor = $_SESSION['db']->db_fetch_assoc($r)) {
			$results[$i][$j++] = $wor;
			/*?><pre><? print_r($results[$i][$j]); ?></pre><?*/
			// Supprime les identiques
			if ($j > 1 && $del) {
				$test = true;
				foreach ($results[$i][$j-1] as $key => $val) {
					//print "$j : $key / $val - " . $results[$i][0][$key] . "<br>";
					if ($results[$i][0][$key] != $val) {
						$test = false;
					}
				}
				if ($test) {
					$sql = sprintf("
						DELETE FROM `TBL_VACANCES`
						WHERE `sdid` = %d
						LIMIT 1
						", $row['sdid']
					);
					$_SESSION['db']->db_interroge($sql);
					//print $row['sdid'] . " deleted !";
					$j--;
					unset($results[$i][$j]);
				}
			}
		}
		mysqli_free_result($r);
		// Si il ne reste qu'un élèment, inutile de l'afficher
		if ($j < 2) {
			unset($results[$i]);
		} else {
			$i++;
		}
	}
	mysqli_free_result($result);
	return $results;
}

/*
 * Recherche les orphelins (sdid == 0) dans TBL_VACANCES
 * Ceux-ci correspondent en principe à des péséq
 * si $pereq est positionné, les dispo en questions sont créées en péréq
 */
function search_orphan_v($pereq = 1) {
	// On vérifie si sdid est déjà la clé primaire de TBL_VACANCES
	// Car si c'est le cas, il ne peut y avoir de valeurs en double
	$result = $_SESSION['db']->db_interroge("SHOW COLUMNS FROM `TBL_VACANCES`");
	while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
		if ($row['Field'] == 'sdid' && $row['Key'] == 'PRI') {
			mysqli_free_result($result);
			return false;
		}
	}
	mysqli_free_result($result);

	$results = array();
	$sql = "
		SELECT `w`.`date`
		, `w`.`etat`
		, `w`.`year`
		, `u`.`nom`
		, `d`.`did`
		, `d`.`dispo`
		, `c`.`vacation`
		, `v`.`sdid`
		, `u`.`uid`
		FROM `TBL_VACANCES` `v`
		, `TBL_VACANCES_SAUVE` `w`
		, `TBL_USERS` `u`
		, `TBL_DISPO` `d`
		, `TBL_CYCLE` `c`
		, `TBL_GRILLE` `g`
		WHERE `sdid` = 0
		AND `d`.`did` = `w`.`did`
		AND `w`.`uid` = `u`.`uid`
		AND `v`.`vid` = `w`.`vid`
		AND `c`.`cid` = `g`.`cid`
		AND `g`.`date` = `w`.`date`
		AND `g`.`team` = '9e'
		AND `g`.`centre` = 'athis'
		AND `c`.`team` = 'all'
		AND `c`.`centre` = 'athis'
		";
	$r = $_SESSION['db']->db_interroge($sql);
	while ($wor = $_SESSION['db']->db_fetch_assoc($r)) {
		$wor['pereq'] = $pereq;
		$results[0][] = $wor;
		// On crée un évènement pour une péréq
		if ($pereq) {
			jourTravail::addPereq($wor);
		}
	}
	mysqli_free_result($r);
	// Suppression des orphelins lorsqu'ils ont été transformés en péréq
	if ($pereq) {
		$_SESSION['db']->db_interroge("
			DELETE FROM `TBL_VACANCES`
			WHERE `sdid` = 0
			");
	}
	return $results;
}

/*
 * Recherche des orphelins (entrées qui devraient avoir leur pendant 
 * dans TBL_VACANCES et dont ce pendant manque) dans TBL_L_SHIFT_DISPO
 * Retourne les sdid des orphelins ou NULL sinon il n'y a pas d'orphelin
 */
function search_orphan_l() {
	$results = array();
	$sql = "SELECT `l`.`sdid`
		, `nom`
		, `dispo`
		, `l`.`date`
		, `vacation`
		, `pereq`
		FROM `TBL_L_SHIFT_DISPO` `l`
		, `TBL_GRILLE` `g`
		, `TBL_CYCLE` `c`
		, `TBL_USERS` `u`
		, `TBL_DISPO` `d`
		WHERE `l`.`uid` = `u`.`uid`
		AND `l`.`did` = `d`.`did`
		AND `l`.`date` = `g`.`date`
		AND `c`.`cid` = `g`.`cid`
		AND `l`.`did` IN (SELECT `did`
			FROM `TBL_DISPO`
			WHERE `type decompte` = 'conges')
		AND `l`.`sdid` NOT IN (SELECT `sdid`
			FROM `TBL_VACANCES`)
		";
	$result = $_SESSION['db']->db_interroge($sql);
	while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
		// Correction si l'année du congé peut être déduite
		// (postérieure à fin avril)
		$date = new Date($row['date']);
		if ($date->mois() >= 5) {
			$row['year'] = $date->annee();
			$sql = sprintf("
				INSERT INTO `TBL_VACANCES`
				(`sdid`, `etat`, `year`)
				VALUES
				(%d, 0, %d)
				", $row['sdid']
				, $row['year']
			);
			$_SESSION['db']->db_interroge($sql);
		}
		$results[0][] = $row;
	}
	mysqli_free_result($result);
	return $results;
}

/*
 * Liste les péréq à partir de l'année $year
 */
function liste_pereq($year = 0) {
	$affectation = $_SESSION['utilisateur']->affectationOnDate(date('Y-m-d')); 
	$results = array();
	$sql = sprintf("
		SELECT *
		FROM `VIEW_LIST_DISPO` AS `v`
		, `TBL_AFFECTATION` AS `a`
		WHERE `pereq` = TRUE
		AND (YEAR(`date`) >= %d
		  OR `year` >= %d)		
		  AND `v`.`uid` = `a`.`uid`
		  AND `a`.`centre` = '%s'
		  AND `a`.`team` = '%s'
		  AND '%s' BETWEEN `beginning` AND `end`
		  ", $year
		  , $year
		  , $affectation['centre']
		  , $affectation['team']
		  , date('Y-m-d')
	);
	$result = $_SESSION['db']->db_interroge($sql);
	while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
		$results[0][] = $row;
	}
	return $results;
}

/*
 * Recherche les évènements sur des jours de repos qui ne sont pas des péréq
 * Cette recherche devrait être vide sauf dans le cas d'évènement qui se
 * serait passés dans une autre équipe (renfort)
 */
function search_event_on_rest() {
	$results = array();
	$sql = "SELECT `l`.`sdid`
		, `nom`
		, `dispo`
		, `l`.`date`
		, `vacation`
		, NULL AS `year`
		, `pereq`
		FROM `TBL_L_SHIFT_DISPO` `l`
		, `TBL_GRILLE` `g`
		, `TBL_CYCLE` `c`
		, `TBL_USERS` `u`
		, `TBL_DISPO` `d`
		WHERE `l`.`uid` = `u`.`uid`
		AND `l`.`did` = `d`.`did`
		AND `l`.`date` = `g`.`date`
		AND `c`.`cid` = `g`.`cid`
		AND `vacation` = 'Repos'
		AND `pereq` = FALSE
		AND `type decompte` != 'conges'
		UNION
		SELECT `l`.`sdid`
		, `nom`
		, `dispo`
		, `l`.`date`
		, `vacation`
		, `year`
		, `pereq`
		FROM `TBL_L_SHIFT_DISPO` `l`
		, `TBL_GRILLE` `g`
		, `TBL_CYCLE` `c`
		, `TBL_USERS` `u`
		, `TBL_DISPO` `d`
		, `TBL_VACANCES` `v`
		WHERE `l`.`uid` = `u`.`uid`
		AND `l`.`did` = `d`.`did`
		AND `l`.`date` = `g`.`date`
		AND `c`.`cid` = `g`.`cid`
		AND `vacation` = 'Repos'
		AND `pereq` = FALSE
		AND `l`.`sdid` = `v`.`sdid`
		";
	$r = $_SESSION['db']->db_interroge($sql);
	while ($wor = $_SESSION['db']->db_fetch_assoc($r)) {
		$results[0][] = $wor;
	}
	mysqli_free_result($r);
	return $results;
}

?>
