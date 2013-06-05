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
	public function __construct($date=false) {
		return $this->loadCycle($date);
	}
	public function __destruct() { // TODO Supprimer l'objet de la liste $_definedCycles
	}
	/*
	 * Des fonctions statiques en rapport avec le planning annuel
	 */

	// Recherche la longueur du cycle
	public static function getCycleLength() {
		if (is_null(self::$_cycleLength)) {
			$requete = "SELECT COUNT(*) FROM `TBL_CYCLE`";
			$out = $_SESSION['db']->db_fetch_row($_SESSION['db']->db_interroge($requete));
			self::$_cycleLength = $out[0];
		}
		return self::$_cycleLength;
	}
	// Recherche la longueur du cycle sans compter les jours de repos
	public static function getCycleLengthNoRepos() {
		if (is_null(self::$_cycleLengthNoRepos)) {
			$requete = sprintf("SELECT COUNT(*) FROM `TBL_CYCLE` WHERE `vacation` != '%s'", REPOS);
			$out = $_SESSION['db']->db_fetch_row($_SESSION['db']->db_interroge($requete));
			self::$_cycleLengthNoRepos = $out[0];
		}
		return self::$_cycleLengthNoRepos;
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
	private static function grilleExists($annee) {
		$requete = sprintf("SELECT * FROM `TBL_GRILLE` WHERE `date` = '%4d-12-31'", $annee);
		$result = $_SESSION['db']->db_interroge($requete);
		$row = $_SESSION['db']->db_fetch_assoc($result);
		mysqli_free_result($result);
		if (!is_array($row) && $annee >= date('Y')) { // On a les conditions remplies pour générer une nouvelle grille
			$requete = sprintf("SELECT * FROM `TBL_GRILLE` WHERE `date` = '%4d-12-31'", $annee-1);
			$result = $_SESSION['db']->db_interroge($requete);
			$row = $_SESSION['db']->db_fetch_assoc($result);
			mysqli_free_result($result);
			if (!is_array($row)) {
				self::grilleExists($annee-1);
				$requete = sprintf("SELECT * FROM `TBL_GRILLE` WHERE `date` = '%4d-12-31'", $annee-1);
				$result = $_SESSION['db']->db_interroge($requete);
				$row = $_SESSION['db']->db_fetch_assoc($result);
				mysqli_free_result($result);
			}
			//echo "Génère la grille pour $annee";
			$jourTravail = new jourTravail($row);
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
	public static function load_planning($annee) { // Retourne un tableau de la forme $planning[mois][jourDuMois] = jourDuCycle
		// On doit d'abord créer la grille de l'année si elle n'existe pas
		self::grilleExists($annee);
		$sql = sprintf("SELECT * FROM `TBL_GRILLE` WHERE YEAR(`date`) = '%s'", $annee);
		$result = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$planning[] = new jourTravail($row);
		}
		mysqli_free_result($result);
		return $planning;
	}
	/*
	 * Fin des fonctions statiques
	 */

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
		// D'abord s'assurer que la grille existe pour l'année
		if (!self::grilleExists($dateDebut->annee())) { // Something went wrong...
			return false; // lastError est déjà remplie
		}
		// On va chercher le cycle qui contient la date $dateDebut
		$dateMin = clone $dateDebut;
		$dateMin->subJours(self::getCycleLength()-1);
		$dateMaxS = clone $dateDebut;
		$dateMaxS->addJours(self::getCycleLength()-1);
		$sql = sprintf("
			SELECT `date`, `TBL_GRILLE`.`cid`,
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
		       		(SELECT `date` FROM `TBL_GRILLE` WHERE `cid` = 1 AND `date` BETWEEN '%s' AND '%s' LIMIT 0,1)
				AND (SELECT `date` FROM `TBL_GRILLE` WHERE `cid` = '%s' AND `date` BETWEEN '%s' AND '%s' LIMIT 0,1)
				ORDER BY date ASC"
				, $dateMin->date()
				, $dateDebut->date()
				, self::getCycleLength()
				, $dateDebut->date()
				, $dateMaxS->date());
		//debug::getInstance()->postMessage($sql);
		$result = $_SESSION['db']->db_interroge($sql);
		$check = true;
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$this->dispos[$row['date']]['jourTravail'] = new jourTravail($row); // $dispo[date]['jourTravail'] = jourTravail
			if ($check) { // la date de référence est la première date du cycle
				$this->dateRef = new Date($row['date']);
				$this->moisAsHTML = $this->dispos[$row['date']]['jourTravail']->moisAsHTML();
				$this->conf($row['conf']);
				$check = false;
			}
		}
		mysqli_free_result($result);
		$sql =  sprintf("
			SELECT `TL`.`uid`, `TL`.`date`,
			`dispo` FROM `TBL_L_SHIFT_DISPO` AS `TL`,
			`TBL_DISPO` AS `TD`,
			`TBL_USERS` AS `TU`,
			`TBL_GRILLE` AS `TG`
			WHERE `TG`.`date` = `TL`.`date`
			AND `TU`.`uid` = `TL`.`uid`
			AND `TU`.`actif` = '1'
			AND `TL`.`date` BETWEEN
		       		(SELECT `date` FROM `TBL_GRILLE` WHERE `cid` = 1 AND `date` BETWEEN '%s' AND '%s' LIMIT 0,1)
				AND (SELECT `date` FROM `TBL_GRILLE` WHERE `cid` = '%s' AND `date` BETWEEN '%s' AND '%s' LIMIT 0,1)
			AND `TD`.`did` = `TL`.`did`
			ORDER BY date ASC"
			, $dateMin->date()
			, $dateDebut->date()
			, self::getCycleLength()
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
		$date->decDate();
		$sql = sprintf("SELECT `uid`, MOD(COUNT(`tl`.`sdid`), 10) FROM `TBL_L_SHIFT_DISPO` AS `tl`, `TBL_DISPO` AS `td` WHERE `tl`.`did` = `td`.`did` AND `td`.`type decompte` = '%s' AND `tl`.`date` < '%s' GROUP BY `uid`", $type, $date->date());
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
		$sql = sprintf("SELECT `uid`, MOD(COUNT(`tl`.`sdid`), 10) FROM `TBL_L_SHIFT_DISPO` AS `tl`, `TBL_DISPO` AS `td` WHERE `tl`.`did` = `td`.`did` AND `td`.`type decompte` = '%s' AND `tl`.`date` < '%s' GROUP BY `uid`", $type, $date->date());
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
}

?>
