<?php
// class_cycle.inc.php
//
// Classe de gestion des cycles de la grille
//

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

require_once('class_jourTravail.inc.php');

class Cycle {
	private static $_cycleLength = null; // La longueur du cycle
	private static $_cycleLengthNoRepos = null; // La longueur du cycle sans compter les jours de repos
	private $cycleId; // Un Id pour identifier le cycle
	private $dateRef; // La date de référence du cycle (date du premier jour) sous forme d'objet Date
	protected $dispos = array(); // La grille (jourTravail et dispos)
	private $conf; // La configuration cds
	private $decompte; // Un tableau contenant le décompte pour chaque utilisateur
	private $compteTypeUser = array(); // Un tableau des décomptes par type (cf `type decompte` dans la bdd) et par utilisateur
	private $compteTypeUserFin = array(); // Un tableau des décomptes par type (cf `type decompte` dans la bdd) et par utilisateur pour la fin du cycle
	private $centre = NULL;
	private $team = NULL;
	public function __construct($date=NULL, $centre = NULL, $team = NULL) {
		if (is_null($date)) return false;
		$this->centre($_SESSION['centre']); // FIXME Supporter réellement le multicentre en ne limitant pas l'usage aux valeurs de la session
		$this->team($_SESSION['team']);
		return $this->loadCycle($date);
	}
	public function __destruct() { // TODO Supprimer l'objet de la liste $_definedCycles
	}
	/*
	 * Des fonctions statiques en rapport avec le planning annuel
	 */

	// Recherche la longueur du cycle
	public static function getCycleLength($centre = 'athis', $team = '9e') {
		if (is_null(self::$_cycleLength)) {
			$requete = sprintf("
				SELECT COUNT(*)
				FROM `TBL_CYCLE`
				WHERE (`centre` = '%s' OR `centre` = 'all')
				AND (`team` = '%s' OR `team` = 'all')
				"
				, $centre
				, $team
			);
			$out = $_SESSION['db']->db_fetch_row($_SESSION['db']->db_interroge($requete));
			self::$_cycleLength = $out[0];
		}
		return self::$_cycleLength;
	}
	// Recherche la longueur du cycle sans compter les jours de repos
	public static function getCycleLengthNoRepos($centre = 'athis', $team = '9e') {
		if (is_null(self::$_cycleLengthNoRepos)) {
			$requete = sprintf("
				SELECT COUNT(*)
				FROM `TBL_CYCLE`
				WHERE `vacation` != '%s'
				AND (`centre` = '%s' OR `centre` = 'all')
				AND (`team` = '%s' OR `team` = 'all')
				"
				, REPOS
				, $centre
				, $team
			);
			$out = $_SESSION['db']->db_fetch_row($_SESSION['db']->db_interroge($requete));
			self::$_cycleLengthNoRepos = $out[0];
		}
		return self::$_cycleLengthNoRepos;
	}
	// La liste des jours de travail d'un cycle
	public static function jtCycle($centre = NULL) {
		if (is_null($centre)) $centre = $_SESSION['centre'];
		$array = array();
		$sql = sprintf("
			SELECT `rang`, `vacation`, `horaires`
			FROM `TBL_CYCLE`
			WHERE `vacation` != '%s'
			AND `centre` = '%s'
			", REPOS
			, $centre
		);
		$result = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$array[] = $row;
		}
		mysqli_free_result($result);
		return $array;
	}

	//-----------
	// Accesseurs
	//-----------
	public function dateRef() {
		return $this->dateRef;
	}
	public function cycleId($cycleId=NULL) { // Attribue et/ou retourne le cycleId au cycle en fonction des Id utilisés
		if ($cycleId !== NULL) $this->cycleId = (int) $cycleId;
		if (isset($this->cycleId)) return $this->cycleId;
		return false;
	}
	public function conf($conf = false) {
		if ($conf) {
			$this->conf = $conf;
		}
		return $this->conf;
	}
	public function centre($centre = NULL) {
		if (is_null($centre)) return $this->centre;
		return $this->centre = $centre;
	}
	public function team($team = NULL) {
		if (is_null($team)) return $this->team;
		return $this->team = $team;
	}
	//-----------------------------------------------------------
	// Retourne les joursTravail du tableau $dispos
	// Avec le décompte de début de cycle en première colonne
	//
	//-----------------------------------------------------------
	public function dispos($date=NULL) {
		if (is_null($date)) {
			return $this->dispos;
		} else {
			return $this->dispos[$date];
		}
	}
	//--------------------------------------------------------------
	// Vérifie que la grille existe pour une année, sinon, la génère
	//
	// Pour cela, on recherche si la table TBL_GRILLE a des données
	// correspondant au 31 décembre de l'année passée en paramètre
	//--------------------------------------------------------------
	private static function grilleExists($annee, $centre = 'athis', $team = '9e') {
		$requete = sprintf("
			SELECT *
			FROM `TBL_GRILLE`
			WHERE `date` = '%4d-12-31'
			AND (`centre` = '%s' OR `centre` = 'all')
			AND (`team` = '%s' OR `team` = 'all')"
			, $annee
			, $centre
			, $team
		);
		$result = $_SESSION['db']->db_interroge($requete);
		$row = $_SESSION['db']->db_fetch_assoc($result);
		mysqli_free_result($result);
		if (!is_array($row) && $annee >= date('Y')) { // On a les conditions remplies pour générer une nouvelle grille
			$requete = sprintf("
				SELECT *
				FROM `TBL_GRILLE`
				WHERE `date` = '%4d-12-31'
				AND (`centre` = '%s' OR `centre` = 'all')
				AND (`team` = '%s' OR `team` = 'all')"
				, $annee-1
				, $centre
				, $team
			);
			$result = $_SESSION['db']->db_interroge($requete);
			$row = $_SESSION['db']->db_fetch_assoc($result);
			mysqli_free_result($result);
			if (!is_array($row)) {
				self::grilleExists($annee-1, $centre, $team);
				$requete = sprintf("
					SELECT *
					FROM `TBL_GRILLE`
					WHERE `date` = '%4d-12-31'
					AND (`centre` = '%s' OR `centre` = 'all')
					AND (`team` = '%s' OR `team` = 'all'"
					, $annee-1
					, $centre
					, $team
				);
				$result = $_SESSION['db']->db_interroge($requete);
				$row = $_SESSION['db']->db_fetch_assoc($result);
				mysqli_free_result($result);
			}
			//echo "Génère la grille pour $annee";
			$jourTravail = new jourTravail($row, $centre, $team);
			self::genere_grille($jourTravail);
			return true;
		} else if (is_array($row)) {
			return true;
		} else {
			debug::getInstance()->lastError(ERR_DB_NORESULT);
			return false;
		}
	}
	//--------------------------------
	// Construit la grille dans la bdd
	//--------------------------------
	private static function genere_grille($jourTravail) {
		// $jourTravail est un objet jourTravail

		// L'année de la grille que l'on va créer
		$an = $jourTravail->annee();
		// Si le mois du jour de référence est en fin d'année on crée la grille de l'année suivante
		if ($jourTravail->mois() >= 11) { $an++; }
		// Le dernier jour de la grille à créer est le 31 décembre de l'année $an
		$cpt = 1; // Un compteur de sécurité
		//  TODO Ouch ! Ça va être un peu long gare au timeExceeded
		while ($jourTravail->annee() <= $an && $cpt++ < 430) {
			$jourTravail->incDate();
			$jourTravail->cid((string)$jourTravail->nextCid());
			$jourTravail->dbSafeInsert();
		}
	}
	//-------------------------------
	// Charge le planning d'une année
	//-------------------------------
	public static function load_planning($annee, $centre = 'athis', $team = '9e') { // Retourne un tableau de la forme $planning[mois][jourDuMois] = jourDuCycle
		// On doit d'abord créer la grille de l'année si elle n'existe pas
		self::grilleExists($annee, $centre, $team);
		$sql = sprintf("
			SELECT *
			FROM `TBL_GRILLE`
			WHERE YEAR(`date`) = '%s'
			AND (`centre` = '%s' OR `centre` ='all')
			AND (`team` = '%s' OR `team` = 'all')"
			, $annee
			, $centre
			, $team
		);
		$result = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$planning[] = new jourTravail($row, $centre, $team);
		}
		mysqli_free_result($result);
		return $planning;
	}
	/*
	 * Fin des fonctions statiques
	 */

	//----------------------------------
	// Vérifie que le cycle
	// débutant à $dateDebut
	// est défini dans TBL_GRILLE de la bdd 
	//
	//  FIXME INUTILISÉ et NON TESTÉ
	//----------------------------------
	private function cycleExistsInDb($dateDebut) {
		if (!is_a($dateDebut, 'Date')) { // On teste si le paramètre est un objet de la classe Date
			$dateDebut = new Date($dateDebut);
			if (!$dateDebut) return false;
		}
		$sql = sprintf("
			SELECT COUNT(`date`)
			FROM `TBL_GRILLE`
			WHERE `date` BETWEEN '%s' AND '%s'
			AND (`centre` = '%s' OR `centre` = 'all')
			AND (`team` = '%s' OR `team` = 'all')
			"
			, $dateDebut->date()
			, $dateDebut->addJours(self::getCycleLength($this->centre, $this->team)-1)->date()
			, $this->centre
			, $this->team
		);
		$result = $_SESSION['db']->db_interroge($sql);
		if (mysqli_num_rows($result) != self::getCycleLength($this->centre, $this->team)) genCycleIntoDb($dateDebut);
		mysqli_free_result($result);
		return true;
	}
	//----------------------------------
	// Génère les cycles dans TBL_GRILLE
	// jusqu'au cycle contenant $dateDebut
	//----------------------------------
	private function genCycleIntoDb($dateDebut) {
		if (!is_a($dateDebut, 'Date')) { // On teste si le paramètre est un objet de la classe Date
			$dateDebut = new Date($dateDebut);
			if (!$dateDebut) return false;
		}
		$confTab = array('E' => 'W', 'W' => 'E');
		$dateDebut->addJours(self::getCycleLength($this->centre, $this->team)); // On veut s'assurer que le cycle complet contenant la date est généré
		// Recherche la dernière entrée de cycle dans la base
		$sql = sprintf("
			SELECT `date`,
				`cid`,
				`conf`
			FROM `TBL_GRILLE`
			WHERE `date` = (SELECT MAX(`date`)
				FROM `TBL_GRILLE`
				WHERE (`centre` = '%s' OR `centre` = 'all')
				AND (`team` = '%s' OR `team` = 'all')
			)
			AND (`centre` = '%s' OR `centre` = 'all')
			AND (`team` = '%s' OR `team` = 'all')
			"
			, $this->centre
			, $this->team
			, $this->centre
			, $this->team
		);
		$result = $_SESSION['db']->db_interroge($sql);
		$row = $_SESSION['db']->db_fetch_row($result);
		mysqli_free_result($result);
		$startDate = new Date($row[0]);
		$cid = $row[1];
		$conf = $row[2];

		if ($dateDebut->compareDate($row[0]) < 0) return true;

		$sql = "";
		$intro = "INSERT INTO `TBL_GRILLE`
			( `grid`,
			`date`,
			`cid`,
			`grilleId`,
			`conf`,
			`pcid`,
			`vsid`,
			`briefing`,
			`readOnly`,
			`ferie`,
			`centre`,
			`team` )
			VALUES ";
		while ($startDate->compareDate($dateDebut) < 0) {
			$startDate->incDate();
			$nCid = ($cid % self::getCycleLength($this->centre, $this->team)) + 1;
			if ($nCid < $cid) $conf = $confTab[$conf];
			$cid = $nCid;
			$sql .= sprintf("
				('', '%s', %d, '', '%s', '', '', '', '', '', '%s', '%s'),"
				, $startDate->date()
				, $cid
				, $conf
				, $this->centre
				, $this->team
			);
		}
		if ($sql != "") {
			$sql = $intro . substr($sql, 0, -1);
			$_SESSION['db']->db_interroge($sql);
			$this->dbDiscrepancy();
		}
	}
	//-------------------------------------------------
	// Charge le cycle
	//
	// Renvoie true si la création s'est bien passée
	// false sinon. lastError contient le code d'erreur
	//-------------------------------------------------
	private function loadCycle($dateDebut) {
		if (!is_a($dateDebut, 'Date')) { // On teste si le paramètre est un objet de la classe Date
			$dateDebut = new Date($dateDebut);
			if (!$dateDebut) return false;
		}
		// On va chercher le cycle qui contient la date $dateDebut
		$dateMin = clone $dateDebut;
		$dateMin->subJours(self::getCycleLength($this->centre, $this->team)-1);
		$dateMaxS = clone $dateDebut;
		$dateMaxS->addJours(self::getCycleLength($this->centre, $this->team)-1);
		// D'abord s'assurer que la grille existe pour le cycle demandé
		$this->genCycleIntoDb($dateMaxS);
		$sql = sprintf("
			SELECT `date`,
			`TBL_GRILLE`.`cid`,
			`TBL_GRILLE`.`vsid`,
			`TBL_GRILLE`.`pcid`,
			`TBL_GRILLE`.`briefing`,
			`TBL_GRILLE`.`conf`,
			`TBL_GRILLE`.`readOnly`,
			`TBL_GRILLE`.`ferie`,
			`TBL_CYCLE`.`vacation`
			FROM `TBL_GRILLE`,
		       		`TBL_CYCLE`
			WHERE `TBL_CYCLE`.`cid` = `TBL_GRILLE`.`cid`
			AND `TBL_CYCLE`.`vacation` <> 'Repos'
			AND `date` BETWEEN
				(SELECT `date`
				FROM `TBL_GRILLE`
				WHERE `cid` =
					(SELECT `cid`
						FROM `TBL_CYCLE`
						WHERE `rang` = 1
						AND (`centre` = '%s' OR `centre` = 'all')
						AND (`team` = '%s' OR `team` = 'all')
					)
				AND `date` BETWEEN '%s' AND '%s'
				LIMIT 0,1
				)
			AND
			       	(SELECT `date`
				FROM `TBL_GRILLE`
				WHERE `cid` =
					(SELECT MAX(`cid`)
						FROM `TBL_CYCLE`
						WHERE (`centre` = '%s' OR `centre` = 'all')
						AND (`team` = '%s' OR `team` = 'all')
					)
				AND `date` BETWEEN '%s' AND '%s'
				LIMIT 0,1
				)
			AND (`TBL_CYCLE`.`centre` = '%s' OR `TBL_CYCLE`.`centre` = 'all')
			AND (`TBL_CYCLE`.`team` = '%s' OR `TBL_CYCLE`.`team` = 'all')
			ORDER BY `date` ASC"
			, $this->centre
			, $this->team
			, $dateMin->date()
			, $dateDebut->date()
			, $this->centre // On suppose ici que les `cid` d'une même entité croissent avec le `rang`
			, $this->team	// Ce qui est logique sauf si le cycle est ultérieurement modifié
			, $dateDebut->date()
			, $dateMaxS->date()
			, $this->centre
			, $this->team
		);
		//debug::getInstance()->postMessage($sql);
		$result = $_SESSION['db']->db_interroge($sql);
		$check = true;
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$this->dispos[$row['date']]['jourTravail'] = new jourTravail($row, $this->centre, $this->team); // $dispo[date]['jourTravail'] = jourTravail
			if ($check) { // la date de référence est la première date du cycle
				$this->dateRef = new Date($row['date']);
				$this->moisAsHTML = $this->dispos[$row['date']]['jourTravail']->moisAsHTML();
				$this->conf($row['conf']);
				$check = false;
			}
		}
		mysqli_free_result($result);
		$sql =  sprintf("
			SELECT `TL`.`uid`,
			`TL`.`date`,
			`dispo`
		       	FROM `TBL_L_SHIFT_DISPO` AS `TL`,
			`TBL_DISPO` AS `TD`,
			`TBL_USERS` AS `TU`,
			`TBL_GRILLE` AS `TG`,
			`TBL_CYCLE` AS `TC`
			WHERE `TG`.`date` = `TL`.`date`
			AND `TC`.`vacation` != '%s'
			AND `TC`.`cid` = `TG`.`cid`
			AND `TU`.`uid` = `TL`.`uid`
			AND `TU`.`actif` = '1'
			AND `TL`.`date` BETWEEN
				(SELECT `date`
		       			FROM `TBL_GRILLE`
					WHERE `cid` =
					(SELECT `cid`
						FROM `TBL_CYCLE`
						WHERE `rang` = 1
						AND (`centre` = '%s' OR `centre` = 'all')
						AND (`team` = '%s' OR `team` = 'all')
					)
					AND `date` BETWEEN '%s' AND '%s'
				      	LIMIT 0,1
				)
				AND
			       	(SELECT `date`
			       		FROM `TBL_GRILLE`
					WHERE `cid` =
					(SELECT MAX(`cid`)
						FROM `TBL_CYCLE`
						WHERE (`centre` = '%s' OR `centre` = 'all')
						AND (`team` = '%s' OR `team` = 'all')
					)
					AND `date` BETWEEN '%s' AND '%s'
					LIMIT 0,1
				)
			AND `TD`.`did` = `TL`.`did`
			ORDER BY date ASC"
			, REPOS
			, $this->centre
			, $this->team
			, $dateMin->date()
			, $dateDebut->date()
			, $this->centre // On suppose ici que les `cid` d'une même entité croissent avec le `rang`
			, $this->team	// Ce qui est logique sauf si le cycle est ultérieurement modifié
			, $dateDebut->date()
			, $dateMaxS->date());
		//debug::getInstance()->postMessage($sql);
		$result = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_row($result)) {
			$this->dispos[$row[1]][$row[0]] = $row[2]; // $dispos[date][uid] = dispo
		}
		mysqli_free_result($result);
		return true;
	}
	//-----------------------------------------------
	// Retourne le décompte en début de cycle pour le
	// type passé en paramètre (dispo par défaut)
	//-----------------------------------------------
	public function compteType($type = 'dispo') {
		$date = clone $this->dateRef();
		$sql = sprintf("
			SELECT `l`.`uid`,
			MOD(COUNT(`l`.`sdid`), 10)
			FROM `TBL_L_SHIFT_DISPO` `l`,
			`TBL_AFFECTATION` `a`,
			`TBL_DISPO` `d`
			WHERE `l`.`did` = `d`.`did`
			AND `l`.`uid` = `a`.`uid`
			AND (`a`.`centre` = '%s' OR `a`.`centre` = 'all')
			AND (`a`.`team` = '%s' OR `a`.`team` = 'all')
			AND `d`.`type decompte` = '%s'
			AND `l`.`date` <= '%s'
			AND `l`.`date` >= `a`.`beginning`
			AND '%s' BETWEEN `a`.`beginning` AND `a`.`end`
			GROUP BY `uid`"
			, $this->centre
			, $this->team
			, $type
			, $date->date()
			, $date->date()
		);
		$result = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_array($result)) {
			$this->compteTypeUser[$type][$row[0]] = $row[1];
		}
		mysqli_free_result($result);
	}
	//----------------------------------------------------------
	// Retourne le décompte en début de cycle pour l'utilisateur
	// et le type passé en paramètre (dispo par défaut)
	//----------------------------------------------------------
	public function compteTypeUser($uid, $type = 'dispo') {
		if (!isset($this->compteTypeUser[$type][$uid])) $this->compteType();
		return (isset($this->compteTypeUser[$type][$uid])) ? $this->compteTypeUser[$type][$uid] : 0;
	}
	//-----------------------------------------------
	// Retourne le décompte en début de cycle pour le
	// type passé en paramètre (dispo par défaut)
	//-----------------------------------------------
	public function compteTypeFin($type = 'dispo') {
		$date = clone $this->dateRef();
		$date->addJours(self::getCycleLength()-1);
		$sql = sprintf("
			SELECT `l`.`uid`,
			MOD(COUNT(DISTINCT `l`.`sdid`), 10)
			FROM `TBL_L_SHIFT_DISPO` AS `l`,
			`TBL_AFFECTATION` AS `a`,
			`TBL_DISPO` AS `d`
			WHERE `l`.`did` = `d`.`did`
			AND `l`.`uid` = `a`.`uid`
			AND (`a`.`centre` = '%s' OR `a`.`centre` = 'all')
			AND (`a`.`team` = '%s' OR `a`.`team` = 'all')
			AND `d`.`type decompte` = '%s'
			AND `l`.`date` <= '%s'
			AND `l`.`date` >= `a`.`beginning`
			AND `a`.`beginning` <= '%s'
			AND `a`.`end` >= '%s'
			GROUP BY `uid`"
			, $this->centre
			, $this->team
			, $type
			, $date->date()
			, $date->date()
			, $this->dateRef()->date()
		);
		$result = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_array($result)) {
			$this->compteTypeUserFin[$type][$row[0]] = $row[1];
		}
		mysqli_free_result($result);
	}
	//----------------------------------------------------------
	// Retourne le décompte en début de cycle pour l'utilisateur
	// et le type passé en paramètre (dispo par défaut)
	//----------------------------------------------------------
	public function compteTypeUserFin($uid, $type = 'dispo') {
		if (!isset($this->compteTypeUserFin[$type][$uid])) $this->compteTypeFin();
		return (isset($this->compteTypeUserFin[$type][$uid])) ? $this->compteTypeUserFin[$type][$uid] : 0;
	}
	//----------------------------------
	// Interdit la modification du cycle
	//----------------------------------
	public function lockCycle() {
		debug::getInstance()->iWasHere('lockCycle');
		foreach ($this->dispos as $date => $array) {
			$array['jourTravail']->setReadOnly();
		}
	}
	//----------------------------------
	// Autorise la modification du cycle
	//----------------------------------
	public function unlockCycle() {
		debug::getInstance()->iWasHere('unlockCycle');
		foreach ($this->dispos as $date => $array) {
			$array['jourTravail']->setReadWrite();
		}
	}
/*
* DB Discrepancy

* Effectue des tests sur la base de données et corrige les éventuelles erreurs
*
*/
	/*
	 * Vérification des vacances scolaires
	 *
	 * Lorsqu'un nouveau cycle est créé dans la base,
	 * on ne met pas les infos de vacances scolaires.
	 * Cette méthode met à jour la base pour ajouter
	 * les vacances scolaires aux jours de la grille
	 * qui auraient été créés après la saisie des
	 * dates de vacances scolaires.
	 *
	 * $date est une chaîne contenant la date à partir
	 * de laquelle il faut mettre à jour.
	 * Par défaut, il s'agit de la date du jour.
	 */
	public function dbDiscrepancyVacancesScolaires($date = NULL) {
		if (is_null($date)) {
			$date = date('Y-m-d');
		} elseif (!is_string($date)) {
			return false;
		}
		// Recherche des vacances scolaires
		$sql = sprintf("SELECT *
			FROM `TBL_VACANCES_SCOLAIRES`
			WHERE `dateF` > '%s'
			AND (`centre` = '%s' OR `centre` = 'all')
			AND (`team` = '%s' OR `team` = 'all')
			"
			, $date
			, $this->centre
			, $this->team
		);
		$result = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$sql = sprintf("
				UPDATE `TBL_GRILLE`
				SET `vsid` = %d
				WHERE `date` BETWEEN '%s' AND '%s'
				"
				, $row['id']
				, $row['dateD']
				, $row['dateF']
			);
			if ($row['centre'] != 'all') {
				$sql .= sprintf("
					AND `centre` = '%s'"
					, $row['centre']
				);
			}
			if ($row['team'] != 'all') {
				$sql .= sprintf("
					AND `team` = '%s'"
					, $row['team']
				);
			}
			$_SESSION['db']->db_interroge($sql);
		}
		mysqli_free_result($result);
	}
	/*
	 * Vérification des briefings
	 *
	 * Lorsqu'un nouveau cycle est créé dans la base,
	 * on ne met pas les infos de briefing.
	 * Cette méthode met à jour la base pour ajouter
	 * les briefings aux jours de la grille
	 * qui auraient été créés après la saisie des
	 * dates de briefings.
	 *
	 * $date est une chaîne contenant la date à partir
	 * de laquelle il faut mettre à jour.
	 * Par défaut, il s'agit de la date du jour.
	 */
	public function dbDiscrepancyBriefing($date = NULL) {
		if (is_null($date)) {
			$date = date('Y-m-d');
		} elseif (!is_string($date)) {
			return false;
		}
		// Recherche des vacances scolaires
		$sql = sprintf("SELECT *
			FROM `TBL_BRIEFING`
			WHERE `dateF` > '%s'
			AND (`centre` = '%s' OR `centre` = 'all')
			AND (`team` = '%s' OR `team` = 'all')
			"
			, $date
			, $this->centre
			, $this->team
		);
		$result = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$sql = sprintf("
				UPDATE `TBL_GRILLE`
				SET `briefing` = %d
				WHERE `date` BETWEEN '%s' AND '%s'
				"
				, $row['id']
				, $row['dateD']
				, $row['dateF']
			);
			if ($row['centre'] != 'all') {
				$sql .= sprintf("
					AND `centre` = '%s'"
					, $row['centre']
				);
			}
			if ($row['team'] != 'all') {
				$sql .= sprintf("
					AND `team` = '%s'"
					, $row['team']
				);
			}
			$_SESSION['db']->db_interroge($sql);
		}
		mysqli_free_result($result);
	}
	/*
	 * Vérification de la période de charge
	 *
	 * Lorsqu'un nouveau cycle est créé dans la base,
	 * on ne met pas les infos de la période de charge.
	 * Cette méthode met à jour la base pour ajouter
	 * la période de charge aux jours de la grille
	 * qui auraient été créés après la saisie des
	 * dates de période de charge.
	 *
	 * $date est une chaîne contenant la date à partir
	 * de laquelle il faut mettre à jour.
	 * Par défaut, il s'agit de la date du jour.
	 */
	public function dbDiscrepancyPeriodeDeCharge($date = NULL) {
		if (is_null($date)) {
			$date = date('Y-m-d');
		} elseif (!is_string($date)) {
			return false;
		}
		// Recherche des vacances scolaires
		$sql = sprintf("SELECT *
			FROM `TBL_PERIODE_CHARGE`
			WHERE `dateF` > '%s'
			AND (`centre` = '%s' OR `centre` = 'all')
			AND (`team` = '%s' OR `team` = 'all')
			"
			, $date
			, $this->centre
			, $this->team
		);
		$result = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$sql = sprintf("
				UPDATE `TBL_GRILLE`
				SET `pcid` = %d
				WHERE `date` BETWEEN '%s' AND '%s'
				"
				, $row['id']
				, $row['dateD']
				, $row['dateF']
			);
			if ($row['centre'] != 'all') {
				$sql .= sprintf("
					AND `centre` = '%s'"
					, $row['centre']
				);
			}
			if ($row['team'] != 'all') {
				$sql .= sprintf("
					AND `team` = '%s'"
					, $row['team']
				);
			}
			$_SESSION['db']->db_interroge($sql);
		}
		mysqli_free_result($result);
	}
	public function dbDiscrepancy($date = NULL) {
		$this->dbDiscrepancyVacancesScolaires($date);
		$this->dbDiscrepancyBriefing($date);
		$this->dbDiscrepancyPeriodeDeCharge($date);
	}
}

?>
