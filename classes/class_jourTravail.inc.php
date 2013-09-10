<?php
// class_jourTravail.inc.php

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
require_once("class_date.inc.php");

class jourTravail extends Date {
	private $cid; // l'id de la journée dans le cycle (cf bdd)
	private $vacation; // la journée de travail au format textuel
	private $vsid = false; // les id des vacances scolaires vsid
	private $pcid = false; // les id des périodes de charge pcid
	private $briefing = false; // L'intitulé du briefing si existant sinon false
	private $conf; // La configuration Est ou Ouest
	private $readOnly; // si ce jour est éditable
	private $nextWorkingDay; // Un objet jourTravail qui représente le prochain jour de travail != REPOS
	private $previousWorkingDay; // Un objet jourTravail qui représente le précédent jour de travail != REPOS
	private $traitementParLots; // Si positionné, les requêtes doivent être traitées par lots (les méthodes qui mettent autrement à jour la bdd, se contentent d'ajouter la requête SQL au tableau this->waitingSQL) On limite ainsi le nombre de requêtes SQL en les groupant.
	private $awaitingSQL = array(); // Tableau des requêtes en attente de traitement
	private $Users; // Un tableau des utilisateurs avec leurs dispos
			// $Users['uid'] = array('dispo1', 'dispo2',...'dispoN') ; cid est le cid du jourTravail (V, J, w, stage...)
	private $centre;
	private $team;
	//private $dispo; // Un tableau des disponibilités possible pour ce jour (V, J, W...)
	/*
	 * Méthodes statiques
	 */
	//-----------------------------------------
	// Ajoute une occupation pour l'utilisateur
	// dispo = array (uid=> , date=> , dispo => , oldDispo => )
	// log = TRUE => les opérations sont loguées
	//-----------------------------------------
	public static function addDispo($dispo, $centre = 'athis', $team = '9e', $log=false) {
		if (isset($_SESSION['MY_EDIT']) && $dispo['uid'] != $_SESSION['utilisateur']->uid() && !isset($_SESSION['ADMIN']) && !isset($_SESSION['EDITEURS'])) {
			return "N'éditez que votre ligne, svp.";
		}
		$return = "";
		// Vérifie si la date est éditable
		$sql = sprintf("
			SELECT `readOnly`
			FROM `TBL_GRILLE`
			WHERE `date` = '%s'
			AND (`centre` = '%s' OR `centre` = 'all')
			AND (`team` = '%s' OR `team` = 'all')
			"
			, $dispo['date']
			, $centre
			, $team
		);
		$result = $_SESSION['db']->db_interroge($sql);
		$row = $_SESSION['db']->db_fetch_row($result);
		mysqli_free_result($result);
		if ($row[0] == 1) return "Cette grille ne peut plus être éditée";
		firePhpInfo($sql, $row[0] == 0 ? 'la date est éditable' : "la date n'est pas éditable");

		// Supprime la précédente dispo
		$sql = sprintf("
			DELETE FROM `TBL_L_SHIFT_DISPO`
			WHERE `date` = '%s'
			AND `uid` = '%s'
			AND `did` =
		       		(SELECT `did`
				FROM `TBL_DISPO`
				WHERE `dispo` = '%s'
				AND (`centre` = '%s' OR `centre` = 'all')
				AND (`team` = '%s' OR `team` = 'all')
				)
			"
			, $dispo['date']
			, $dispo['uid']
			, $dispo['oldDispo']
			, $centre
			, $team
			);
		firePhpInfo($sql, 'Suppression de la précédente dispo');
		$_SESSION['db']->db_interroge($sql);

		// Les congés sont traités particulièrement
		// Ils sont comptabilisés dans TBL_VACANCES
		$aVacances = array();
		$sql = sprintf("
			SELECT `dispo`,
			`did`,
			`date limite depot`,
			`quantity`
			FROM `TBL_DISPO`
			WHERE `need_compteur` = TRUE
			AND `actif` = TRUE
			AND `type decompte` = 'conges'
			AND (`centre` = '%s' OR `centre` = 'all')
			AND (`team` = '%s' OR `team` = 'all')
			"
			, $centre
			, $team
		);
		$result = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_row($result)) {
			$aVacances[$row[0]]['did'] = $row[1]; // dispo -> did
			$aVacances[$row[0]]['dld'] = $row[2]; // dispo -> date limite de dépôt du congé
			$aVacances[$row[0]]['quantity'] = $row[3]; // dispo -> quantité du congé par année
		}
		mysqli_free_result($result);
		// Si la nouvelle dispo est un congé
		// on doit l'ajouter dans TBL_VACANCES
		if (in_array($dispo['dispo'], array_keys($aVacances))) {
			// Calcul de l'année du congé
			//
			// La date limite de dépôt des congés de l'année est fixée dans TBL_CONSTANTS
			// elle se nomme "dlCong_$year" où $year est l'année concernée (par ex dlCong_2012)
			// Si cette constante n'est pas définie, elle prend la valeur par défaut dlCong_default
			// Si le congé est sur une date au-delà de la date limite des congés de l'année,
			// il est passé sur l'année suivante.
			// Si il est sur une date avant la date limite des congés de l'année,
			// et si il reste suffisamment de congés de ce type pour l'année en cours
			// il est posé sur l'année en cours
			if ( ! $date = new Date($dispo['date'])) {
				firePhpError("dispo['date'] incorrecte", $dispo['date']);
				return false;
			}
			$Year = $date->annee();
			$previousYear = $Year - 1;
			// Si la date du congé est antérieure à la date limite de dépôt de l'année précédente,
			// il est possible (si il reste des vacances) que l'année du congé soit l'année passée
			$dlCong = $Year . "-" . $aVacances[$dispo['dispo']]['dld'];
			firePhpInfo($dlCong, 'dlCong');
			if ($date->compareDate($dlCong) < 0) {
				$sql = sprintf("
					SELECT %d - COUNT(`vid`)
					FROM `TBL_VACANCES`
					WHERE `did` = %d
					AND `uid` = %d
					AND `year` = %d
					"
					, $aVacances[$dispo['dispo']]['quantity']
					, $aVacances[$dispo['dispo']]['did']
					, $dispo['uid']
					, $previousYear
				);
				firePhpInfo($sql, 'Décompte de congés');
				$result = $_SESSION['db']->db_interroge($sql);
				$row = $_SESSION['db']->db_fetch_row($result);
				mysqli_free_result($result);
				if ($row[0] > 0) $Year = $previousYear;
			}
			// fin du calcul de l'année du congé

			// Recherche si il reste des congés disponibles
			$sql = sprintf("
				SELECT %d - COUNT(`vid`)
				FROM `TBL_VACANCES`
				WHERE `did` = %d
				AND `uid` = %d
				AND `year` = %d
				"
				, $aVacances[$dispo['dispo']]['quantity']
				, $aVacances[$dispo['dispo']]['did']
				, $dispo['uid']
				, $Year
			);
			firePhpInfo($sql, 'Décompte de congés');
			$result = $_SESSION['db']->db_interroge($sql);
			$row = $_SESSION['db']->db_fetch_row($result);
			mysqli_free_result($result);
			if ($row[0] <= 0) return "Ce congé n'est plus disponible";

			$sql = sprintf("
				INSERT INTO `TBL_VACANCES`
				(`uid`, `date`, `did`, `year`)
				VALUES (%d, '%s', %d, %d)
				"
				, $dispo['uid']
				, $dispo['date']
				, $aVacances[$dispo['dispo']]['did']
				, $Year
			);
			firePhpInfo($sql, 'Décompte vacances');
			$_SESSION['db']->db_interroge($sql);
			if (mysql_error()) {
				firePhpError($sql, mysql_error());
				$return = sprintf("add_dispo(): %s / %s", mysql_error(), $sql);
			}
		}
		// Si l'ancienne dispo était un congé
		// on doit supprimer son entrée dans TBL_VACANCES
		if (in_array($dispo['oldDispo'], array_keys($aVacances))) {
			$sql = sprintf("
				DELETE FROM `TBL_VACANCES`
				WHERE `uid` = %d
				AND `date` = '%s'
				AND `did` = %d
				"
				, $dispo['uid']
				, $dispo['date']
				, $aVacances[$dispo['oldDispo']]['did']
			);
			firePhpInfo($sql, 'Décompte vacances');
			$_SESSION['db']->db_interroge($sql);
			if (mysql_error()) {
				firePhpError($sql, mysql_error());
				$return = sprintf("add_dispo(): %s / %s", mysql_error(), $sql);
			}
		}

		// Ajoute la nouvelle dispo
		if ($dispo['dispo'] != "") {
			$sql = sprintf("
				INSERT INTO `TBL_L_SHIFT_DISPO`
				(`date`, `uid`, `did`)
				VALUES ('%s', '%s', (
					SELECT `did`
					FROM `TBL_DISPO`
					WHERE `dispo` = '%s')
				)
				"
				, $dispo['date']
				, $dispo['uid']
				, $dispo['dispo']
			);
			//$undo = sprintf("DELETE FROM `TBL_L_SHIFT_DISPO` WHERE `date` = '%s' AND `uid` = '%s' AND `did` = (SELECT `did` FROM `TBL_DISPO` WHERE `dispo` = '%s'))", $dispo['date'], $dispo['uid'], $dispo['dispo']);
			$_SESSION['db']->db_interroge($sql);
			firePhpInfo($sql, 'Màj');
			if (mysql_error()) {
				$return = sprintf("safe_insert_dispo(): %s / %s", mysql_error(), $sql);
				firePhpError($sql, mysql_error());
			}
		}
		firePhpInfo($sql, 'vacances');
		firePhpInfo($aVacances, '$aVacances');
		firePhpInfo($dispo, '$dispo');
		/*if ($log) { // Faut-il loguer la modification ?
			$logSql = sprintf("INSERT INTO `TBL_LOG` (`date`, `uid`, `dateCel`, `uidCel`, `did`, `previous`, `undo`, `ip`) VALUES (NOW(), '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $_SESSION['utilisateur']->uid(), $dispo['date'], $dispo['uid'], $dispo['did'], $dispo['oldDid'], $_SESSION['db']->escape_string($undo), $_SERVER['REMOTE_ADDR']);
			firePhpInfo($logSql, "Requête de log de l'opération");
			$_SESSION['db']->db_interroge($logSql);
			if (mysql_error()) {
				$return = sprintf("safe_insert_dispo(): %s / %s", mysql_error(), $sql);
			}
		}*/
		return $return;
	}
	//-----------------------------------------
	// Ajoute les coordonnées d'un remplaçant
	// dans la bdd.
	// Le paramètre est un tableau :
	// $rempla[(uid, date, nom, phone, email)
	// Les valeurs sont traitées|protégées par
	// la méthode elle-même
	//-----------------------------------------
	public static function addRempla($rempla) {
		$sql = sprintf("
			REPLACE INTO `TBL_REMPLA`
			(`uid`,`date`,`nom`,`phone`,`email`)
			VALUES (%02d, '%s', '%s', '%s', '%s')
			"
			, $rempla['uid']
			, $_SESSION['db']->db_real_escape_string($rempla['date'])
			, $_SESSION['db']->db_real_escape_string($rempla['nom'])
			, $_SESSION['db']->db_real_escape_string($rempla['phone'])
			, $_SESSION['db']->db_real_escape_string($rempla['email'])
		);
		$_SESSION['db']->db_interroge($sql);
	}
	//-----------------------------------
	// Retourne les propriétés des dispos
	// si actif est non nul, seules les
	// dispos actives seront analysées
	//-----------------------------------
	public static function proprietesDispo($actif = NULL, $centre = 'athis', $team = '9e') {
		$array = array();
		$sql = sprintf("
			SELECT *
			FROM `TBL_DISPO`
			WHERE (`centre` = '%s' OR `centre` = 'all')
			AND (`team` = '%s' OR `team` = 'all')
			"
			, $centre
			, $team
		);
		if (!is_null($actif)) $sql .= " AND `actif` = 1";
		$result = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			foreach ($row as $key => $value) {
				if ($key != 'dispo') $array[$row['dispo']][$key] = $value;
			}
		}
		mysqli_free_result($result);
		return $array;
	}

	/*
	 * constructeur
	 */
	public function __construct($row=false, $centre = 'athis', $team = '9e') {
		$this->centre($_SESSION['centre']); // FIXME Supporter réellement le multicentre en ne limitant pas l'usage aux valeurs de la session
		$this->team($_SESSION['team']);
		if ($row) {
			$check = 0;
			if (is_string($row)) { // si $row est une chaîne...
				if (parent::__construct($row)) { // ... au format date
					$sql = sprintf("
						SELECT *
						FROM `TBL_GRILLE`
						WHERE `date` = '%s'
						AND (`centre` = '%s' OR `centre` = 'all')
						AND (`team` = '%s' OR `team` = 'all')
						"
						, $this->date()
						, $this->centre
						, $this->team
					);
					$result = $_SESSION['db']->db_interroge($sql);
					if (mysqli_num_rows($result) == 1) {
						$row = $_SESSION['db']->db_fetch_assoc($result);
					}
					mysqli_free_result($result);
					$check = 1;
				}
			}
			if (is_array($row)) {
				$this->setFromRow($row);
			}
			$this->jourDeLaSemaine();
		}
		$this->traitementParLots = false;
	}
	public function __destruct() {
	}
	public function __set($name, $value) {
		$this->$name = $value;
	}
	/*
	 * Accesseurs
	 */
	// Attention à bien passer un (string) en paramètre.
	// IL est fortement conseillé de caster le paramètre passé
	public function cid($param = false) {
		if (ctype_digit($param)) { // On vérifie que cid est composé uniquement de chiffres
			// On doit ruser car modulo renvoie [0 .. cycleLength]
			$this->cid = ($param-1) % Cycle::getCycleLength() + 1;
			$this->vacation(true);
		} else if ($param) {
			$this->cid = false;
		}
		return $this->cid;
	}
	// Obtient le nom de la journée de travail à partir de la bdd si un paramètre est passé
	// $param s'il existe, devrait être positionné à true
	// Retourne le nom de la journée de travail si aucun paramètre n'est passé
	public function vacation($param = false) {
		if ($param || empty($this->vacation)) {
			if (!isset($this->cid)) {
				$query = sprintf("
					SELECT `cid`
					FROM `TBL_CYCLE`
					WHERE `vacation` = '%s'
					AND (`centre` = '%s' OR `centre` = 'all')
					AND (`team` = '%s' OR `team` = 'all')
					"
					, (string) $param
					, $this->centre
					, $this->team
				);
				$result = $_SESSION['db']->db_interroge($query);
				if ($result && mysqli_num_rows($result)==1) {
					$row = $_SESSION['db']->db_fetch_row($result);
					$this->cid($row[0]);
				} else {
					$this->cid = false;
				}
				mysqli_free_result($result);
		       	}
			$query = sprintf("
				SELECT `vacation`
				FROM `TBL_CYCLE`
				WHERE `cid` = %s
				"
				, $this->cid()
			);
			$result = $_SESSION['db']->db_interroge($query);
			if ($result) {
				$row = $_SESSION['db']->db_fetch_row($result);
				$this->vacation = $row[0];
			} else {
				$this->vacation = false;
			}
			mysqli_free_result($result);
	       	}
		return $this->vacation;
	}
	// Retourne les dispo du jour (J, V, W, stg...)
	// sous forme d'un tableau
	/*public function dispo() {
		return (array) $this->dispo;
	}*/
	public function jourDeLaSemaine() {
		$jds = $this->jourSemaine();
		// Fixe $ferie pour les weekend
		if ($this->isWeekend() || $this->ferie()) { $this->set_ferie(); }
		else { $this->unset_ferie(); }
		return $jds;
	}
	public function vsid($vsid = false) {
		if ($vsid) {
			$this->vsid = $vsid;
		}
		return $this->vsid;
	}
	public function pcid($pcid = false) {
		if ($pcid) {
			$this->pcid = $pcid;
		}
		return $this->pcid;
	}
	public function briefing($briefing = false) {
		if ($briefing) {
			$this->briefing = $briefing;
		}
		return $this->briefing;
	}
	public function conf($conf = false) {
		if ($conf) {
			$this->conf = $conf;
		}
		return $this->conf;
	}
	public function readOnly($param = NULL) {
		if (!is_null($param)) {
			if ($param) {
				$this->readOnly = true;
			} else {
				$this->readOnly = false;
			}
		}
		return $this->readOnly;
	}
	public function setReadWrite() {
		$this->readOnly = false;
		firePhpInfo($this->_dbQueryUpdateReadOnly());
		if (!$this->traitementParLots) {
			$this->_dbUpdateReadOnly();
		} else {
			$this->awaitingSQL[] = $this->_dbQueryUpdateReadOnly();
		}
	}
	public function setReadOnly() {
		$this->readOnly = true;
		firePhpInfo($this->_dbQueryUpdateReadOnly());
		if (!$this->traitementParLots) {
			$this->_dbUpdateReadOnly();
		} else {
			$this->awaitingSQL[] = $this->_dbQueryUpdateReadOnly();
		}
	}
	public function centre($centre = NULL) {
		if (is_null($centre)) return $this->centre;
		return $this->centre = $centre;
	}
	public function team($team = NULL) {
		if (is_null($team)) return $this->team;
		return $this->team = $team;
	}
	// Retourne le cid suivant celui du jourTravail
	public function nextCid() {
		$nCid = ($this->cid() % Cycle::getCycleLength($this->centre, $this->team)) + 1;
		return $nCid;
	}
	// Retourne le prochain jour de travail (!= REPOS)
	public function nextWorkingDay() {
		if (empty($this->nextWorkingDay)) {
			$date = new Date($this->date());
			do {
				$date->incDate();
				$this->nextWorkingDay = new jourTravail($date->date(), $this->centre, $this->team);
			} while ($this->nextWorkingDay->vacation() == REPOS);
		}
		return $this->nextWorkingDay;
	}
	// Retourne le précédent jour de travail (!= REPOS)
	public function previousWorkingDay() {
		if (empty($this->previousWorkingDay)) {
			$date = new Date($this->date());
			do {
				$date->decDate();
				$this->previousWorkingDay = new jourTravail($date->date(), $this->centre, $this->team);
			} while ($this->previousWorkingDay->vacation() == REPOS);
		}
		return $this->previousWorkingDay;
	}
	// Essaie de créer l'objet à partir d'une chaîne correspondant à un id html
	public function createFromId($id) {
		if (!is_string($id)) return false;
		$pattern = "/^deca(\d{4})m(\d{1,2})j(\d{1,2})s(.+)c\d+$/";
		if (preg_match($pattern, $id, $row)) {
			$this->annee($row[1]);
			$this->mois($row[2]);
			$this->jour($row[3]);
			$this->vacation($row[4]);
		}
	}
	// Affichage de l'objet
	public function presente() {
		//printf("%s ---> %s - %s<br />", $this->date_(), $this->vacation(), $this->jourDeLaSemaine());
	}
	public function setFromRow($row) {
		parent::__construct($row['date']);
		foreach ($row as $key => $value) {
			if (method_exists($this, $key)) {
				$this->$key($value);
			} else {
				$this->key = $value;
			}
		}
	}
// Interactions avec la bdd
	// Insertion dans la base
	public function __dbQueryInsert() {
		$sql = sprintf("
			INSERT INTO `TBL_GRILLE`
			(`date`, `cid`, `ferie`, `centre`, `team`)
			VALUES ('%s', '%s', '%s', '%s', '%s')
			"
			, $this->date()
			, $this->cid()
			, $this->ferie()
			, $this->centre
			, $this->team
		);
		return $sql;
	}
	public function _dbInsert() {
		$_SESSION['db']->db_interroge($this->__dbQueryInsert());
	}
	// Mise à jour
	// Méthode qui retourne les requêtes en attente de traitement
	public function awaitingSQL() {
		return $this->awaitingSQL;
	}
	// Retourne les requêtes en attente et les efface
	public function flushAwaitingSQL() {
		$awaiting = $this->awaitingSQL();
		$this->awaitingSQL = array();
		return $awaiting;
	}
	public function __dbQueryUpdate() {
		$sql = sprintf("
			UPDATE `TBL_GRILLE`
			SET `cid` = '%s',
			`ferie` = '%s'
			WHERE `date` = '%s'
			AND (`centre` = '%s' OR `centre` = 'all')
			AND (`team` = '%s' OR `team` = 'all')
			"
			, $this->cid()
			, $this->ferie()
			, $this->date()
			, $this->centre
			, $this->team
		);
		return $sql;
	}
	private function _dbQueryUpdateReadOnly() {
		return sprintf("
			UPDATE `TBL_GRILLE`
			SET `readOnly` = '%s'
			WHERE `date` = '%s'
			AND (`centre` = '%s' OR `centre` = 'all')
			AND (`team` = '%s' OR `team` = 'all')
			"
			, $this->readOnly()
			, $this->date()
			, $this->centre
			, $this->team
		);
	}
	private function _dbUpdateReadOnly() {
		$_SESSION['db']->db_interroge($this->_dbQueryUpdateReadOnly());
	}
	public function _dbUpdate() {
		$_SESSION['db']->db_interroge($this->__dbQueryUpdate());
	}
	public function dbSafeInsert() {
		$sql = sprintf("
			SELECT *
			FROM `TBL_GRILLE`
			WHERE `date` = '%s'
			AND (`centre` = '%s' OR `centre` = 'all')
			AND (`team` = '%s' OR `team` = 'all')
			"
			, $this->date()
			, $this->centre
			, $this->team
		);
		if (mysqli_num_rows($_SESSION['db']->db_interroge($sql)) > 0) {
			$this->_dbUpdate();
		} else {
			$this->_dbInsert();
		}
	}
}

?>
