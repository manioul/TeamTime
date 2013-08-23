<?php
// class_utilisateurGrille.inc.php
//
// étend la classe utilisateur aux utilisateurs de la grille
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

set_include_path(implode(PATH_SEPARATOR, array(realpath('.'), get_include_path())));

require_once 'class_debug.inc.php';
require_once 'class_utilisateur.inc.php';
require_once 'class_jourTravail.inc.php';
require_once 'config.inc.php';


class utilisateurGrille extends utilisateur {
	private $uid;
	private $nom;
	private $gid;
	private $prenom;
	private $classe = array(); // array('c', 'pc', 'ce', 'cds', 'dtch')
	private $dateArrivee;
	private $dateTheorique;
	private $datePC;
	private $dateCE;
	private $dateCDS;
	private $dateVisMed; // Date de la prochaine visite médicale
	private $affectations = array();
	private $centre = array();
	private $team = array();
	private $poids; // La position d'affichage dans la grille (du plus faible au plus gros)
	private $showtipoftheday; // L'utilisateur veut-il voir les tips of the day
	private $page = "affiche_grille.php";
	private $dispos; /* un tableau contenant un tableau des dispos indexées par les dates:
			* $dispos[date] = array('dispo1', 'dispo2',... 'dispoN'); */
	private static $label = array();
	protected static function _label($index) {
		if (isset(self::$label[$index])) {
			return self::$label[$index];
		} else {
			return false;
		}
	}
	protected static function _localFieldsDefinition($regen = NULL) {
		foreach ($_SESSION['db']->db_getColumnsTable("TBL_AFFECTATION") as $row) {
			$fieldsDefinition[$row['Field']]['Field'] = isset($label[$row['Field']]) ? $label[$row['Field']] : $row['Field'];
			if ($row['Extra'] == 'auto_increment' || $row['Field'] == 'nblogin' || $row['Field'] == 'lastlogin') {
				// Ce champ ne sera pas saisi par l'utilisateur
			} else {
				$fieldsDefinition[$row['Field']]['width'] = -1;
				if (preg_match('/\((\d*)\)/', $row['Type'], $match) == 1) {
					if ($match[1] > 1) {
						$fieldsDefinition[$row['Field']]['width'] = ($match[1] < 10) ? $match[1] : 10;
						$fieldsDefinition[$row['Field']]['maxlength'] = $match[1];
					}
				}
				if (preg_match('/int\((\d*)\)/', $row['Type'], $match)) {
					if ($match[1] == 1) {
						$fieldsDefinition[$row['Field']]['type'] = "checkbox";
						$fieldsDefinition[$row['Field']]['value'] = 1;
					} else {
						$fieldsDefinition[$row['Field']]['type'] = "text";
					}
				} elseif ($row['Field'] == 'email') {
					$fieldsDefinition[$row['Field']]['type'] = 'email';
				} elseif ($row['Field'] == 'password') {
					$fieldsDefinition[$row['Field']]['type'] = 'password';
				} elseif ($row['Type'] == 'date') {
					$fieldsDefinition[$row['Field']]['type'] = 'date';
					$fieldsDefinition[$row['Field']]['maxlength'] = 10;
					$fieldsDefinition[$row['Field']]['width'] = 6;
				} else {
					$fieldsDefinition[$row['Field']]['type'] = 'text';
				}
			}
		}
	}
	public static function _fieldsDefinition($regen = NULL) {
		$correspondances = array(
			'sha1'		=> htmlspecialchars("Mot de passe", ENT_COMPAT)
			, 'arrivee'	=> htmlspecialchars("Date d'arrivée", ENT_COMPAT)
			, 'theorique'	=> htmlspecialchars("Date du théorique", ENT_COMPAT)
			, 'pc'		=> htmlspecialchars("Date du pc", ENT_COMPAT)
			, 'ce'		=> htmlspecialchars("Date ce", ENT_COMPAT)
			, 'cds'		=> htmlspecialchars("Date cds", ENT_COMPAT)
			, 'vismed'	=> htmlspecialchars("Date visite médicale", ENT_COMPAT)
			, 'lastlogin'	=> htmlspecialchars("Date de dernière connexion", ENT_COMPAT)
		);
		parent::_fieldsDefinition($correspondances, $regen);
	}
// Constructeur
	public function __construct ($row = NULL) {
		if (NULL !== $row) {
			parent::__construct($row);
			$this->gid = 2; // Par défaut, on fixe le gid à la valeur la plus élevée
			$valid = true;
			foreach ($row as $cle => $valeur) {
				if (method_exists($this, $cle)) {
					$this->$cle($valeur);
				} else {
					debug::getInstance()->triggerError('Valeur inconnue' . $cle . " => " . $valeur);
					debug::getInstance()->lastError(ERR_BAD_PARAM);
					$valid = false;
				}
			}
			return $valid; // Retourne true si l'affectation s'est bien passée, false sinon
		}
		return true;
	}
	public function __destruct() {
		unset($this);
		parent::__destruct();
	}
// Accesseurs
	public function uid($uid=false) {
		if (false !== $uid) {
			$this->uid = (int) $uid;
		}
		if (isset($this->uid)) {
			return $this->uid;
		} else {
			return false;
		}
	}
	public function gid($gid=false) {
		if (false !== $gid) {
			$this->gid = (int) $gid;
		}
		if (isset($this->gid)) {
			return $this->gid;
		} else {
			return false;
		}
	}
	public function nom($nom=false) {
		if (false !== $nom) {
			$this->nom = (string) $nom;
		}
		if (isset($this->nom)) {
			return $this->nom;
		} else {
			return false;
		}
	}
	public function prenom($prenom=false) {
		if (false !== $prenom) {
			$this->prenom = (string) $prenom;
		}
		if (isset($this->prenom)) {
			return $this->prenom;
		} else {
			return false;
		}
	}
	// $date est la date pour laquelle on veut obtenir les classes de l'utilisateur
	public function classe($date = false) {
		if (sizeof($this->classe) < 1) $this->getClassesFromDb();
		if (false === $date) return $this->classe;
		if (!is_object($date)) $date = new Date($date);
		$classes = array();
		foreach ($this->classe as $classe => $array) {
			foreach ($array as $key => $value) {
				if ($date->compareDate($value['beginning']) >= 0 && $date->compareDate($value['end']) <= 0) $classes[] = $classe;
			}
		}
		return $classes;
	}
	public function getClassesFromDb() {
		$result = $_SESSION['db']->db_interroge(sprintf("
			SELECT * FROM `TBL_CLASSE`
			WHERE `uid` = '%s'
			", $this->uid()
		));
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$this->addClasse($row);
		}
		mysqli_free_result($result);
	}
	public function addClasse($classe = false) {
		if (false === $classe) return false;
		$index = isset($this->classe[$classe['classe']]) ? sizeof($this->classe[$classe['classe']]) : 0;
		$this->classe[$classe['classe']][$index]['beginning'] = $classe['beginning'];
		$this->classe[$classe['classe']][$index]['end'] = $classe['end'];
	}
	public function db_condition_like_classe($champ) { // Retourne une condition LIKE sur les classes de l'utilisateur pour le champ $champ à la date $date
		$condition = sprintf("`$champ` = 'all' OR `$champ` LIKE '%%%s%%' OR ", $this->login());
		foreach ($this->classe(date('Y-m-d')) as $classe) {
			$condition .= sprintf("`%s` LIKE '%%%s%%' OR ", $champ, $classe);
		}
		return substr($condition, 0, -4);
	}
	public function arrivee($arrivee=false) {
		if (false !== $arrivee) {
			$this->arrivee = (string) $arrivee;
		}
		if (isset($this->arrivee)) {
			return $this->arrivee;
		} else {
			return false;
		}
	}
	public function theorique($theorique=false) {
		if (false !== $theorique) {
			$this->theorique = (string) $theorique;
		}
		if (isset($this->theorique)) {
			return $this->theorique;
		} else {
			return false;
		}
	}
	public function pc($pc=false) {
		if (false !== $pc) {
			$this->pc = (string) $pc;
		}
		if (isset($this->pc)) {
			return $this->pc;
		} else {
			return false;
		}
	}
	public function ce($ce=false) {
		if (false !== $ce) {
			$this->ce = (string) $ce;
		}
		if (isset($this->ce)) {
			return $this->ce;
		} else {
			return false;
		}
	}
	public function cds($cds=false) {
		if (false !== $cds) {
			$this->cds = (string) $cds;
		}
		if (isset($this->cds)) {
			return $this->cds;
		} else {
			return false;
		}
	}
	public function vismed($vismed=false) {
		if (false !== $vismed) {
			$this->vismed = (string) $vismed;
		}
		if (isset($this->vismed)) {
			return $this->vismed;
		} else {
			return false;
		}
	}
	public function getAffectationFromDb() {
		$result = $_SESSION['db']->db_interroge(sprintf("
			SELECT * FROM `TBL_AFFECTATION`
			WHERE `uid` = '%s'
			", $this->uid()
		));
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$this->addAffectation($row);
		}
		mysqli_free_result($result);
	}
	protected function addAffectation($row = false) {
		if (!is_array($row)) return false;
		$index = isset($this->centre[$row['centre']]) ? sizeof($this->centre[$row['centre']]) : 0;
		$this->centre[$row['centre']][$index]['beginning'] = $row['beginning'];
		$this->centre[$row['centre']][$index]['end'] = $row['end'];
		$this->team[$row['team']][$index]['beginning'] = $row['beginning'];
		$this->team[$row['team']][$index]['end'] = $row['end'];
		$this->affectations[] = array('beginning' => $row['beginning']
					, 'end'		=> $row['end']
					, 'centre'	=> $row['centre']
					, 'team'	=> $row['team']
		);
	}
	public function centre ($date = false) {
		if (sizeof($this->centre) < 1) $this->getAffectationFromDb();
		if (false === $date) $date = date('Y-m-d');
		if (!is_object($date)) $date = new Date($date);
		foreach ($this->centre as $centre => $array) {
			foreach ($array as $index => $value) {
				if ($date->compareDate($value['beginning']) >= 0 && $date->compareDate($value['end']) <= 0) return $centre;
			}
		}
	}
	public function team ($date = false) {
		if (sizeof($this->team) < 1) $this->getAffectationFromDb();
		if (false === $date) $date = date('Y-m-d');
		if (!is_object($date)) $date = new Date($date);
		foreach ($this->team as $team => $array) {
			foreach ($array as $index => $value) {
				if ($date->compareDate($value['beginning']) >= 0 && $date->compareDate($value['end']) <= 0) return $team;
			}
		}
	}
	public function affectations() {
		if (sizeof($this->affectations) < 1) $this->getAffectationFromDb();
		return $this->affectations;
	}
	public function poids($poids=false) {
		if (false !== $poids) {
			$this->poids = (int) $poids;
		}
		if (isset($this->poids)) {
			return $this->poids;
		} else {
			return false;
		}
	}
	public function showtipoftheday($showtipoftheday=false) {
		if (false !== $showtipoftheday) {
			$this->showtipoftheday = (int) $showtipoftheday;
		}
		if (isset($this->showtipoftheday)) {
			return $this->showtipoftheday;
		} else {
			return false;
		}
	}
	public function dispos($dispos=false) {
		if (is_array($dispos)) {
			$this->dispos = $dispos;
		}
		if (isset($this->dispos)) {
			return $this->dispos;
		} else {
			return false;
		}
	}
	// Méthodes utiles pour l'affichage
	public function userCell($dateDebut) {
		return array('nom'	=> htmlentities($this->nom())
			,'classe'	=> 'nom ' . implode(' ', $this->classe($dateDebut))
			,'id'		=> "u". $this->uid()
			,'uid'		=> $this->uid()
		);
	}
	// Prépare l'affichage des informations de l'utilisateur
	// Si $myself est positionné, on peut modifier le mot de passe
	public function displayUserInfos($myself = false) {
	}
}

class utilisateursDeLaGrille {
	private static $_instance = null;
	public static function getInstance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new utilisateursDeLaGrille();
		}
		return self::$_instance;
	}
	private $users = array();

	public function __construct() {
	}
	// Retourne une table d'utilisateurGrille
	// en fonction de la requête sql passée en argument
	public function retourneUsers($sql) {
		$result = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$this->users[] = new utilisateurGrille($row);
		}
		mysqli_free_result($result);
		return $this->users;
	}
	// Efface la table des utilisateurGrille
	public function flushUsers() {
		$this->users = array();
	}
	// Retourne une table d'utilisateurGrille
	// $condition est une chaîne de caractère contenant la condition ou
	// un tableau définissant les conditions de recherche des utilisateurs :
	// $condition = array("`field` = 'value'", ...)
	// Les conditions sont liées par AND
	public function getUsers($condition = NULL, $order = "ORDER BY `poids` ASC") {
		// Ajoute la condition
		$cond = "";
		if (is_string($condition)) $cond = "WHERE " . $condition;
		if (is_array($condition)) {
			$cond = "WHERE " . implode(' AND ', $condition);
		}
		$sql = sprintf("
			SELECT `TBL_USERS`.*
			FROM `TBL_USERS`
			%s
		       	%s"
			, $cond
			, $order
		);
		return $this->retourneUsers($sql);
	}
	public function getActiveUsers($centre = 'athis', $team = '9e', $condition = NULL, $order = "ORDER BY `poids` ASC") {
		$cond = array("`actif` = 1");
		if (is_array($condition)) $cond = array_merge($cond, $condition);
		return $this->getUsers($cond, $order);
	}
	// Retourne une table d'utilisateurGrille d'utilisateurs actifs pour une affectation précise
	public function getActiveUsersFromTo($from = NULL, $to = NULL, $centre = NULL, $team = NULL) {
		return $this->getUsersFromTo($from, $to, $centre, $team, 1);
	}
	public function getUsersFromTo($from = NULL, $to = NULL, $centre = NULL, $team = NULL, $active = 1) {
		if (is_null($from)) $from = date('Y-m-d');
		if (is_null($to)) $to = date('Y-m-d');
		if (is_null($centre)) $centre = 'athis';
		if (is_null($team)) $team = '9e';
		if ('all' == $centre && 'all' == $team) {
			$sql = "SELECT `TU`.*,
				`TA`.`centre`,
				`TA`.`team`
				FROM `TBL_USERS` AS `TU`
				, `TBL_AFFECTATION` AS `TA`
				WHERE `TU`.`uid` = `TA`.`uid`";
			if (-1 != $from && -1 != $to) $sql .= "
				AND `TA`.`beginning` <= \"$to\"
				AND `TA`.`end`  >= \"$from\"";
			if (1 == $active) $sql .= "
			       	AND `TU`.`actif` = 1 ";
			$sql .= "ORDER BY `TU`.`poids` ASC";
		} elseif ('all' == $team) {
			$sql = "SELECT `TU`.*,
				`TA`.`centre`,
				`TA`.`team`
				FROM `TBL_USERS` AS `TU`
				, `TBL_AFFECTATION` AS `TA`
				WHERE `TU`.`uid` = `TA`.`uid`
				AND `TA`.`centre`= \"$centre\"";
			if (-1 != $from && -1 != $to) $sql .= "
				AND `TA`.`beginning` <= \"$to\"
				AND `TA`.`end`  >= \"$from\"";
			if (1 == $active) $sql .= "
			       	AND `TU`.`actif` = 1 ";
			$sql .= "ORDER BY `TU`.`poids` ASC";
		} else {
			$sql = "SELECT `TU`.*,
				`TA`.`centre`,
				`TA`.`team`
				FROM `TBL_USERS` AS `TU`
				, `TBL_AFFECTATION` AS `TA`
				WHERE `TU`.`uid` = `TA`.`uid`
				AND `TA`.`centre`= \"$centre\"
				AND `TA`.`team` = \"$team\"";
			if (-1 != $from && -1 != $to) $sql .= "
				AND `TA`.`beginning` <= \"$to\"
				AND `TA`.`end`  >= \"$from\"";
			if (1 == $active) $sql .= "
			       	AND `TU`.`actif` = 1 ";
			$sql .= "ORDER BY `TU`.`poids` ASC";
		}
		return $this->retourneUsers($sql);
	}
	// Méthodes utiles pour l'affichage
	public function usersCell($dateDebut) {
		$array = array();
		foreach ($this->users as $user) {
			$array[] = $user->userCell($dateDebut);
		}
		return $array;
	}
	public function getUsersCell($dateDebut, $condition = NULL, $order = "ORDER BY `poids` ASC") {
		$this->getUsers($condition, $order);
		return $this->usersCell($dateDebut);
	}
	public function getActiveUsersCell($from, $to, $centre = 'athis', $team = '9e') {
		$this->getActiveUsersFromTo($from, $to, $centre, $team);
		return $this->usersCell($from);
	}
	public function getGrilleActiveUsers($dateDebut, $nbCycle = 1, $centre = 'athis', $team = '9e') {
		// Recherche des infos de date pour créer un navigateur
		$nextCycle = new Date($dateDebut);
		$previousCycle = new Date($dateDebut);
		$nextCycle->addJours(Cycle::getCycleLength($centre, $team)*$nbCycle);
		$previousCycle->subJours(Cycle::getCycleLength($centre, $team)*$nbCycle);

		// Recherche la date de fin du cycle
		$dateFin = new Date($dateDebut);
		$dateFin->addJours(Cycle::getCycleLength($centre, $team) * $nbCycle - 1);

		// Chargement des propriétés des dispos
		$proprietesDispos = jourTravail::proprietesDispo(1, $centre, $team);

		// Jours de semaine au format court
		$jdsc = Date::$jourSemaineCourt;

		// Le tableau $users qui constituera la grille
		$users = array();

		// Les deux premières lignes du tableau sont dédiées au jourTravail (date, vacation...)
		$users[] = array('nom'		=> 'navigateur'
			,'classe'	=> 'dpt'
			,'id'		=> ''
			,'uid'		=> 'jourTravail'
		);
		$users[] = array('nom'		=> '<div class="boule"></div>'
			,'classe'	=> 'dpt'
			,'id'		=> ''
			,'uid'		=> 'jourTravail'
		);

		$users = array_merge($users, utilisateursDeLaGrille::getInstance()->getActiveUsersCell($dateDebut, $dateFin->date(), $centre, $team));

		// Ajout d'une rangée pour le décompte des présences
		$users[] = array('nom'		=> 'décompte'
			,'class'	=> 'dpt'
			,'id'		=> 'dec'
			,'uid'		=> 'dcpt'
		);

		// Recherche des jours de travail
		//
		$cycle = array();
		$dateIni = new Date($dateDebut);
		if ($DEBUG) debug::getInstance()->startChrono('load_planning_duree_norepos'); // Début chrono
		for ($i=0; $i<$nbCycle; $i++) {
			$cycle[$i] = new Cycle($dateIni, $centre, $team);
			$dateIni->addJours(Cycle::getCycleLength($centre, $team));
			$cycle[$i]->cycleId($i);
		}
		if ($DEBUG) debug::getInstance()->stopChrono('load_planning_duree_norepos'); // Fin chrono

		// Lorsque l'on n'affiche qu'un cycle, on ajoute des compteurs en fin de tableau
		$evenSpec = array();
		if ($nbCycle == 1) {
			// Récupération des compteurs
			if ($DEBUG) debug::getInstance()->startChrono('Relève compteur'); // Début chrono
			$sql = "SELECT `dispo`, `nom_long`
				FROM `TBL_DISPO`
				WHERE `actif` = TRUE
				AND `need_compteur` = TRUE
			       	AND `type decompte` != 'conges'";
			$results = $_SESSION['db']->db_interroge($sql);
			while ($res = $_SESSION['db']->db_fetch_array($results)) {
				$evenSpec[$res[0]] = array(
					'nomLong'	=> htmlspecialchars($res[1], ENT_COMPAT)
				);
			}
			mysqli_free_result($results);

			/*
			 * Recherche le décompte des évènements spéciaux
			 * La liste est limitée en dur
			 */
			$sql = sprintf("SELECT `uid`, `dispo`, COUNT(`td`.`did`), MAX(`date`)
				FROM `TBL_L_SHIFT_DISPO` AS `tl`, `TBL_DISPO` AS `td`
				WHERE `td`.`did` = `tl`.`did`
				AND `td`.`actif` = TRUE
				AND `date` <= '%s'
				AND `need_compteur` = TRUE
				AND `type decompte` != 'conges'
				GROUP BY `td`.`did`, `uid`"
				, $cycle[0]->dateRef()->date());

			$results = $_SESSION['db']->db_interroge($sql);
			while ($res = $_SESSION['db']->db_fetch_array($results)) {
				$evenSpec[$res[1]]['uid'][$res[0]] = array(
					'nom'		=> $res[2]
					,'title'	=> $res[3]
					,'id'		=> "u" . $res[0] . "even" . $res[1]
					,'classe'	=> ""
				);
			}
			mysqli_free_result($results);
			if ($DEBUG) debug::getInstance()->stopChrono('Relève compteur'); // Fin chrono
		}

		$lastLine = count($users)-1;
		for ($i=0; $i<$nbCycle; $i++) {
			$compteurLigne = 0;
			foreach ($users as $user) {
				switch ($compteurLigne) {
					/*
					 * Première ligne contenant le navigateur, l'année et le nom du mois
					 */
				case 0:
					if ($i == 0) {
						$grille[$compteurLigne][] = array(
							'nom'		=> $cycle[$i]->dateRef()->annee()
							,'id'		=> 'navigateur'
							,'classe'	=> ''
							,'colspan'	=> 2
							,'navigateur'	=> 1 // Ceci permet à smarty de construire un navigateur entre les cycles
						);
					}
					$grille[$compteurLigne][] = array(
						'nom'		=> $cycle[$i]->dateRef()->moisAsHTML()
						,'id'		=> 'moisDuCycle' . $cycle[$i]->dateRef()->dateAsId()
						,'classe'	=> ''
						,'colspan'	=> Cycle::getCycleLengthNoRepos()+1+count($evenSpec)
					);
					break;
					/*
					 * Deuxième ligne contenant les dates, les vacations, charge et vacances scolaires
					 */
				case 1:
					// La deuxième ligne contient la description de la vacation (date...)
					if ($i == 0) {
						// Ajout d'une colonne pour le nom de l'utilisateur
						$grille[$compteurLigne][] = array(
							'classe'		=> "entete"
							,'id'			=> ""
							,'nom'			=> htmlentities("Nom", ENT_NOQUOTES, 'utf-8')
						);
						// Ajout d'une colonne pour les décomptes
						$grille[$compteurLigne][] = array(
							'classe'		=> "conf"
							,'id'			=> "conf" . $cycle[$i]->dateRef()->dateAsId()
							,'nom'			=> $cycle[$i]->conf()
						);
					}
					foreach ($cycle[$i]->dispos() as $dateVacation => $vacation) {
						// Préparation des informations de jours, date, jour du cycle (en-têtes de la grille)
						$grille[$compteurLigne][] = array(
							'jds'			=> $jdsc[$vacation['jourTravail']->jourDeLaSemaine()]
							,'jdm'			=> $vacation['jourTravail']->jour()
							,'classe'		=> $vacation['jourTravail']->ferie() ? 'ferie' : 'semaine'
							,'annee'		=> $vacation['jourTravail']->annee()
							,'mois'			=> $vacation['jourTravail']->moisAsHTML()
							,'vacation'		=> htmlentities($vacation['jourTravail']->vacation())
							,'vacances'		=> $vacation['jourTravail']->vsid() > 0 ? 'vacances' : 'notvacances'
							,'periodeCharge'	=> $vacation['jourTravail']->pcid() > 0 ? 'charge' : 'notcharge'
							,'briefing'		=> $vacation['jourTravail']->briefing()
							,'id'			=> sprintf("%ss%s", $vacation['jourTravail']->dateAsId(), $vacation['jourTravail']->vacation())
							,'date'			=> $vacation['jourTravail']->date()
						);
					}
					// Ajout d'une colonne en fin de cycle
					// avec la configuration cds
					// ou une image pour la dernière colonne
					if ($i < $nbCycle-1) {
						$grille[$compteurLigne][] = array(
							'classe'		=> "conf"
							,'id'			=> "conf" . $cycle[$i+1]->dateRef()->dateAsId()
							,'nom'			=> $cycle[$i+1]->conf()
						);
					} else {
						$grille[$compteurLigne][] = array(
							'classe'		=> ""
							,'id'			=> sprintf("sepA%sM%sJ%s", $vacation['jourTravail']->annee(), $vacation['jourTravail']->mois(), $vacation['jourTravail']->jour())
							,'date'			=> $vacation['jourTravail']->date()
							,'nom'			=> '<div class="boule"></div>'
						);
					}
					if ($nbCycle == 1) {
						// Ajout d'une colonne pour les compteurs
						foreach (array_keys($evenSpec) as $even) {
							$grille[$compteurLigne][] = array(
								'classe'		=> ""
								,'id'			=> str_replace(" ", "", $evenSpec[$even]['nomLong']) // Certains noms longs comportent des espaces, ce qui n'est pas autorisé pour un id
								,'date'			=> ""
								,'nom'			=> ucfirst(substr($even, 0, 1))
								,'title'		=> $evenSpec[$even]['nomLong']
							);
						}
					}
					break;
					/*
					 * Dernière ligne contenant le nombre de présents
					 */
				case $lastLine:
					if ($i == 0) {
						$grille[$compteurLigne][] = array(
							'classe'		=> "decompte"
							,'id'			=> ""
							,'nom'			=> htmlentities("Présents", ENT_NOQUOTES, 'utf-8')
							,'colspan'	=> 2
						);
					}
					foreach ($cycle[$i]->dispos() as $dateVacation => $vacation) {
						$grille[$compteurLigne][] = array(
							'classe'		=> 'dcpt'
							,'id'			=> sprintf("deca%sm%sj%ss%sc%s", $vacation['jourTravail']->annee(), $vacation['jourTravail']->mois(), $vacation['jourTravail']->jour(), $vacation['jourTravail']->vacation(), $cycle[$i]->cycleId())
						);
					}
					// Ajout d'une colonne en fin de cycle qui permet le (dé)verrouillage du cycle
					$jtRef = $cycle[$i]->dispos($cycle[$i]->dateRef()->date());
					$lockClass = $jtRef['jourTravail']->readOnly() ? 'cadenasF' : 'cadenasO';
					$lockTitle = $jtRef['jourTravail']->readOnly() ? 'Déverrouiller le cycle' : 'Verrouiller le cycle';
					$un_lock = $jtRef['jourTravail']->readOnly() ? 'ouvre' : 'bloque';

					$grille[$compteurLigne][] = array(
						'classe'		=> "locker"
						,'id'			=> sprintf("locka%sm%sj%sc%s", $cycle[$i]->dateRef()->annee(), $cycle[$i]->dateRef()->mois(), $cycle[$i]->dateRef()->jour(), $cycle[$i]->cycleId())
						,'nom'			=> isset($_SESSION['EDITEURS']) ? sprintf("<div class=\"imgwrapper12\"><a href=\"lock.php?date=%s&amp;lock=%s&amp;noscript=1\"><img src=\"themes/%s/images/glue.png\" class=\"%s\" alt=\"#\" /></a></div>", $cycle[$i]->dateRef()->date(), $un_lock, $_COOKIE['theme'], $lockClass) : sprintf("<div class=\"imgwrapper12\"><img src=\"themes/%s/images/glue.png\" class=\"%s\" alt=\"#\" /></div>", $_COOKIE['theme'], $lockClass) // Les éditeurs ont le droit de (dé)verrouiller la grille
						,'title'	=> htmlentities($lockTitle, ENT_NOQUOTES, 'utf-8')
						,'colspan'	=> 1+count($evenSpec)
					);
					break;
					/*
					 * Lignes utilisateurs
					 */
				default:
					if ($i == 0) {
						// La première colonne contient les infos sur l'utilisateur
						$grille[$compteurLigne][] = $user;
						// La deuxième colonne contient les décomptes horizontaux
						$grille[$compteurLigne][] = array(
							'nom'		=> 0+$cycle[$i]->compteTypeUser($user['uid'], 'dispo')
							,'id'		=> sprintf("decDispou%sc%s", $user['uid'], $cycle[$i]->cycleId())
							,'classe'	=> ''
						);
					}
					// On itère sur les vacations du cycle
					foreach ($cycle[$i]->dispos() as $dateVacation => $vacation) {
						$classe = "presence";
						if ($vacation['jourTravail']->readOnly()) $classe .= " protected";
						if (!empty($vacation[$user['uid']]) && !empty($proprietesDispos[$vacation[$user['uid']]]) && 1 == $proprietesDispos[$vacation[$user['uid']]]['absence']) {
							$classe .= " absent";
							// Ajout d'une classe particulière pour les congés validés
							if ('conges' == $proprietesDispos[$vacation[$user['uid']]]['type decompte']) {
								$result = $_SESSION['db']->db_interroge(sprintf("
									SELECT `etat`
									FROM `TBL_VACANCES`
									WHERE `date` = '%s'
									AND `uid` = %d
									", $dateVacation
									, $user['uid']
								));
								if (mysqli_num_rows($result) < 1) {
									$classe .= " erreur";
								} else {
									$row = $_SESSION['db']->db_fetch_row($result);
									if (2 == $row[0]) $classe .= " valide";
								}
								mysqli_free_result($result);
							}
						} else {
							$classe .= " present";
						}
						/*
						 * Affichage remplacements
						 */
						if (!empty($vacation[$user['uid']]) && "Rempla" == $vacation[$user['uid']]) {
							$proprietesDispos[$vacation[$user['uid']]]['nom_long'] = "Mon remplaçant";
							$sql = sprintf("SELECT * FROM `TBL_REMPLA` WHERE `uid` = %s AND `date` = '%s'", $user['uid'], $vacation['jourTravail']->date());
							$row = $_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($sql));
							$proprietesDispos[$vacation[$user['uid']]]['nom_long'] = $row['nom'] . " | " . $row['phone'];
						} //
						$grille[$compteurLigne][] = array(
							'nom'		=> isset($vacation[$user['uid']]) ? htmlentities($vacation[$user['uid']], ENT_NOQUOTES, 'utf-8') : " "
							,'id'		=> sprintf("u%s%ss%sc%s", $user['uid'], $vacation['jourTravail']->dateAsId(), $vacation['jourTravail']->vacation(), $cycle[$i]->cycleId())
							,'classe'	=> $classe
							,'title'	=> isset($proprietesDispos[$vacation[$user['uid']]]['nom_long']) ? $proprietesDispos[$vacation[$user['uid']]]['nom_long'] : ''
						);
					}
					// La dernière colonne contient les décomptes horizontaux calculés
					// La date est celle de dateRef + durée du cycle
			/*$dateSuivante = clone $cycle[$i]->dateRef();
			$dateSuivante->addJours(Cycle::getCycleLength());*/
					$grille[$compteurLigne][] = array(
						'nom'		=> 0+$cycle[$i]->compteTypeUserFin($user['uid'], 'dispo')
						,'id'		=> sprintf("decDispou%sc%s", $user['uid'], $cycle[$i]->cycleId()+1)
						,'classe'	=> ''
					);
					if ($nbCycle == 1) {
						foreach (array_keys($evenSpec) as $even) {
							$grille[$compteurLigne][] = array(
								'nom'		=> empty($evenSpec[$even]['uid'][$user['uid']]['nom']) ? 0 : $evenSpec[$even]['uid'][$user['uid']]['nom']
								,'id'		=> empty($evenSpec[$even]['uid'][$user['uid']]['id']) ? "" : $evenSpec[$even]['uid'][$user['uid']]['id']
								,'title'	=> empty($evenSpec[$even]['uid'][$user['uid']]['title']) ? "" : $evenSpec[$even]['uid'][$user['uid']]['title']
								,'classe'	=> empty($evenSpec[$even]['uid'][$user['uid']]['classe']) ? "" : $evenSpec[$even]['uid'][$user['uid']]['classe']
							);
						}
					}
				}
				$compteurLigne++;
			}
		}

		/*
		 * Préparation des valeurs de retour
		 */
		$return = array();
		$return['nextCycle'] = $nextCycle->date();
		$return['previousCycle'] = $previousCycle->date();
		$return['presentCycle'] = date("Y-m-d");
		$return['dureeCycle'] = Cycle::getCycleLengthNoRepos();
		$return['anneeCycle'] = $cycle[0]->dateRef()->annee();
		$return['moisCycle'] = $cycle[0]->dateRef()->mois();
		$return['grille'] = $grille;
		$return['nbCycle'] = $nbCycle;
		/*
		 * Fin des assignations des valeurs de retour
		 */
		return $return;
	}
}
?>
