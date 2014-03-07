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
require_once 'class_contacts.inc.php';
require_once 'class_affectations.inc.php';
require_once 'class_message.inc.php';


class utilisateurGrille extends utilisateur {
	private $uid;
	private $nom;
	private $gid;
	private $prenom;
	private $classe = array(); // array('c', 'pc', 'ce', 'cds', 'dtch')
	private $roles = array(); // Les autorisations de l'utilisateur
	private $vismed; // Date de la prochaine visite médicale
	private $phone = array(); // Numéro de téléphone
	private $adresse; // adresse
	private $affectations = array(); // tableau des affectations
	private $orderedAffectations = array(); // tableau des affectations rangées en ordre croissant
	private $centre = NULL; // centre actuel
	private $team = NULL; // team actuelle
	private $grade = NULL; // grade actuel
	private $poids; // La position d'affichage dans la grille (du plus faible au plus gros)
	private $showtipoftheday; // L'utilisateur veut-il voir les tips of the day
	private $indexPage; // L'index de la page favorite (ouverte après la connexion) dans le tableaux $availablePages
	private $dispos; /* un tableau contenant un tableau des dispos indexées par les dates:
			* $dispos[date] = array('dispo1', 'dispo2',... 'dispoN'); */
	private $messages;
	private static $label = array();
	private static $availablePages = array(
		1	=> array ('titre'	=> 'Cycle unique'
				  , 'uri'	=> 'affiche_grille.php'
				  , 'gid'	=> 255)
		, 2	=> array('titre'	=> 'Trois cycles'
				  , 'uri'	=> 'affiche_grille.php?nbCycle=3'
				  , 'gid'	=> 255)
		, 3  	=> array('titre'	=> "mon compte"
				 , 'uri'	=> 'monCompte.php'
				  , 'gid'	=> 255)
		, 4  	=> array('titre'	=> "Gestion des utilisateurs"
				 , 'uri'	=> 'utilisateur.php'
				 , 'gid'	=> 0)
	);
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
	public static function createUser($row) {
		$dateD = new Date($row['dateD']);
		$dateF = new Date($row['dateF']);
		$sql = sprintf("CALL createUser('%s', '%s', '%s', '%s', '%s', %s, %d, %s, %s, '%s', '%s', '%s', '%s', '%s', '%s', '%s')
			", $_SESSION['db']->db_real_escape_string($row['nom'])
			, $_SESSION['db']->db_real_escape_string($row['prenom'])
			, $_SESSION['db']->db_real_escape_string($row['login'])
			, $_SESSION['db']->db_real_escape_string($row['email'])
			, $_SESSION['db']->db_real_escape_string($row['password'])
			, $row['locked'] == 'on' ? 'TRUE' : 'FALSE'
			, (int) $row['poids']
			, $row['actif'] == 'on' ? 'TRUE' : 'FALSE'
			, $row['showtipoftheday'] == 'on' ? 'TRUE' : 'FALSE'
			, $_SESSION['db']->db_real_escape_string($row['page'])
			, $GLOBALS['DSN']['user']['password']
			, $_SESSION['db']->db_real_escape_string($row['centre'])
			, $_SESSION['db']->db_real_escape_string($row['team'])
			, $_SESSION['db']->db_real_escape_string($row['grade'])
			, $dateD->date()
			, $dateF->date()
		);
		$_SESSION['db']->db_interroge($sql);
		mysqli_free_result($result);
	}
	// Liste les pages accessibles par tous les utilisateurs à partir des entrées de menus
	// et retourne un tableau utilisable par un html.form.select.tpl
	public static function listAvailablePages() {
		$select = array('label' => 'Première page', 'name' => 'page');
		$sql = "SELECT `titre` AS `content`, `lien` AS `value`
			FROM `TBL_ELEMS_MENUS`
			WHERE `allowed` = 'all'
			AND `titre` != 'logout'";
		$result = $_SESSION['db']->db_interroge($sql);
		while($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$select['options'][] = $row;
		}
		mysqli_free_result($result);
		return $select;
	}
// Constructeur
	public function __construct ($param = NULL) {
		//firePhpLog($param, "Création d'un nouvel objet utilisateurGrille");
		if (NULL !== $param) {
			if (is_array($param)) {
				parent::page('affiche_grille.php'); // La page par défaut des utilisateurs
				parent::__construct($param);
				$this->gid = 255; // Par défaut, on fixe le gid à la valeur la plus élevée
				return @ $this->setFromRow($param); // Retourne true si l'affectation s'est bien passée, false sinon
			} elseif (is_int($param)) {
				$this->setFromDb($param);
			}
		}
		return true;
	}
	public function __destruct() {
		parent::__destruct();
		unset($this);
	}
// Accesseurs
	public function userAsArray() {
		return array_merge(
			parent::asArray()
		       	, array(
			'uid'			=> $this->uid
			, 'nom'			=> $this->nom
			, 'gid'			=> $this->gid
			, 'prenom'		=> $this->prenom
			, 'vismed'		=> $this->vismed
			, 'poids'		=> $this->poids
			, 'showtipoftheday'	=> $this->showtipoftheday
		));
	}
	public function setFromRow($row) {
		$valid = true;
		foreach ($row as $key => $value) {
			if (method_exists($this, $key)) {
				$this->$key($value);
			} else {
				$this->$key = $value;
				firePhpError($this->$key . " => " . $value, 'Valeur inconnue');
				debug::getInstance()->triggerError('Valeur inconnue' . $this->$key . " => " . $value);
				debug::getInstance()->lastError(ERR_BAD_PARAM);
				$valid = false;
			}
		}
		return $valid;
	}
	public function uid($uid = NULL) {
		if (!is_null($uid)) {
			$this->uid = (int) $uid;
		}
		if (isset($this->uid)) {
			return $this->uid;
		} else {
			return NULL;
		}
	}
	public function gid($gid = NULL) {
		if (!is_null($gid)) {
			$this->gid = (int) $gid;
		}
		if (isset($this->gid)) {
			return $this->gid;
		} else {
			return false;
		}
	}
	public function nom($nom = NULL) {
		if (!is_null($nom)) {
			$this->nom = (string) $nom;
		}
		if (isset($this->nom)) {
			return $this->nom;
		} else {
			return false;
		}
	}
	public function prenom($prenom = NULL) {
		if (!is_null($prenom)) {
			$this->prenom = (string) $prenom;
		}
		if (isset($this->prenom)) {
			return $this->prenom;
		} else {
			return false;
		}
	}
	/*
	 * Ajoute un téléphone unique
	 */
	public function addPhone($row) {
		$row['uid'] = $this->uid();
		$phone = new Phone($row);
		$phoneid = $phone->insert();
		firePhpLog($phoneid, 'phoneid');
		$this->phone[$phoneid] = $phone;
		return $phoneid;
	}
	/*
	 * Ajoute les téléphones venant d'un tableau
	 * $array = ( 0 => ('numéro' => '1010101010'
	 * 		'description'	=> 'maison'
	 * 		'principal'	=> 'on'
	 * 		)
	 * 	      1 => (...)
	 * 	      )
	 */
	public function addPhoneTableau($array) {
		$valid = true;
		firePhpLog($array, 'addPhoneTableau(array)');
		foreach ($array as $arr) {
			if (is_array($arr)) {
				$this->addPhone($arr);
			} else {
				$valid = false;
				break;
			}
		}
		firePhpLog($valid, 'valid');
		if (!$valid) $this->addPhone($array);
		return sizeof($this->phone);
	}
	public function deletePhone($phoneid) {
		$this->phone[$phoneid]->delete();
		unset($this->phone[$phoneid]);
	}
	// Retourne la table des objets Phone
	// Si $index est passé, retourné l'objet Phone indexé par $index
	public function phone($param = NULL) {
		if (sizeof($this->phone) == 0) { // Si on n'a pas encore récupéré les téléphones dans la bdd
			$this->_retrievePhone($param);
		}
		if (is_int($param)) { // Si $param est l'index d'un téléphone, on retourne l'objet correspondant
			firePhpLog($param, 'is_int');
			return $this->phone[$param];
		}
		if (is_array($param)) { // Si $param est un tableau, il contient les infos d'un(e) nouveau(x) téléphone(s)
			firePhpLog($param, 'is_array');
			$this->addPhoneTableau($param);
		}
		return $this->phone;
	}
	/*
	 * Ajoute une adresse unique
	 */
	public function addAdresse($row) {
		$row['uid'] = $this->uid();
		$adresse = new Adresse($row);
		$adresseid = $adresse->insert();
		$this->adresse[$adresseid] = $adresse;
		return $adresseid;
	}
	/*
	 * Ajoute les adresses venant d'un tableau
	 * $array = ( 0 => ('adresse' => '10 rue des alouettes'
	 * 		'cp'	=> '70000'
	 * 		'ville'	=> 'ville de lumière'
	 * 		)
	 * 	      1 => (...)
	 * 	      )
	 */
	public function addAdresseTableau($array) {
		$valid = true;
		foreach ($array as $arr) {
			if (is_array($arr)) {
				$this->addAdresse($arr);
			} else {
				$valid = false;
				break;
			}
		}
		if (!$valid) $this->addAdresse($array);
		return sizeof($this->adresse);
	}
	public function deleteAdresse($adresseid) {
		$this->adresse[$adresseid]->delete();
		unset($this->adresse[$adresseid]);
	}
	public function adresse($param = NULL) {
		if (sizeof($this->adresse) == 0) $this->_retrieveAdresse($param);
		if (is_int($param)) return $this->adresse[$param]; // Si $param est l'index d'une adresse, on retourne l'objet correspondant
		if (is_array($param)) { // Si $param est un tableau, il contient les infos d'une(e) nouvelle(s) adresse(s)
			$this->addAdresseTableau($param);
		}
		return $this->adresse;
	}
	// $date est la date pour laquelle on veut obtenir les classes de l'utilisateur
	public function classe($date = NULL) {
		if (sizeof($this->classe) < 1) $this->_getClassesFromDb();
		if (is_null($date)) return $this->classe;
		if (!is_object($date)) $date = new Date($date);
		$classes = array();
		foreach ($this->classe as $classe => $array) {
			foreach ($array as $key => $value) {
				if ($date->compareDate($value['beginning']) >= 0 && $date->compareDate($value['end']) <= 0) $classes[] = $classe;
			}
		}
		return $classes;
	}
	protected function _getClassesFromDb() {
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
	// retourne les rôles (sous forme de tableau)
	public function roles() {
		if (sizeof($this->roles) < 1) $this->dbRetrRoles();
		return $this->roles;
	}
	// Retourne vrai si l'utilisateur a le rôle $role
	public function hasRole($role) {
		return in_array($role, $this->roles());
	}
	// Attribue les rôles en fonction de la base de données
	public function dbRetrRoles() {
		$sql = sprintf("
			SELECT role
			FROM `TBL_ROLES`
			WHERE uid = %d
			AND '%s' BETWEEN `beginning` AND `end`
			", $this->uid
			, date('Y-m-d')
		);
		/*$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("%s", "DEBUG", "dbRetrRoles", NULL, NULL)'
			, $sql)
		);*/ 
		$result = $_SESSION['db']->db_interroge($sql);
		while($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$this->roles[] = $row['role'];
		}
	}
	// Ajoute un rôle à l'utilisateur
	// $param est un tableau :
	// ('role' => role, 'beginning' => beginning, 'end' => end, 'centre' => centre, 'team' => team )
	// si beginning et end ne sont pas définis, beginning prend la valeur de la date courante et end est fixé à 2050-12-31
	// si centre et team ne sont pas définis, on utilise l'affectation courante de l'utilisateur
	public function addRole($param) {
		if (!is_array($param)) {
			$msg = sprintf("\$param devrait être un array (%s)", $param);
			$short = "wrong param";
			$context = $param;
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("%s", "DEBUG", "roles", "%s", "%s")'
				, $msg
				, $short
				, $context)
			); 
			return false;
		}
		if ($this->hasRole($param['role'])) {
			return true;
		}
		if (!isset($param['beginning'])) {
			$param['beginning'] = date("Y-m-d");
		}
		if (!isset($param['end'])) {
			$param['end'] = '2050-12-31';
		}
		if (!isset($param['centre'])) {
			$param['centre'] = $this->centre();
		}
		if (!isset($param['team'])) {
			$param['team'] = $this->team();
		}
		$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Ajout de rôle", "DEBUG", "addRole", "Ajout de rôle", "uid:%d;role:%s;appelant:%d")'
			, $this->uid()
			, $param['role']
			, $_SESSION['utilisateur']->uid()
			)
		); 
		if ( $_SESSION['utilisateur']->hasRole('admin') ) {
			$_SESSION['db']->db_interroge(sprintf("
				REPLACE INTO `TBL_ROLES`
				(`uid`, `role`, `centre`, `team`, `beginning`, `end`, `confirmed`)
				VALUES
				(%d, '%s', '%s', '%s', '%s', '%s', TRUE)
				", $this->uid
				, $param['role']
				, $param['centre']
				, $param['team']
				, $param['beginning']
				, $param['end']
			));
			// TODO réévaluer les privilèges de l'utilisateur sur la base de données
			// Un utilisateur lambda ne doit pas avoir accès en écriture à certaines tables
			$this->roles = array();
			$this->dbRetrRoles();
		} elseif ($_SESSION['utilisateur']->hasRole('editeurs')) {
			if ($param['role'] == 'editeurs' || $param['role'] == 'teamEdit' || $param['role'] == 'my_edit' || $param['role'] == 'heures') {
				$_SESSION['db']->db_interroge(sprintf("
					REPLACE INTO `TBL_ROLES`
					(`uid`, `role`, `centre`, `team`, `beginning`, `end`, `confirmed`)
					VALUES
					(%d, '%s', '%s', '%s', '%s', '%s', TRUE)
					", $this->uid
					, $param['role']
					, $param['centre']
					, $param['team']
					, $param['beginning']
					, $param['end']
				));
				// TODO réévaluer les privilèges de l'utilisateur sur la base de données
				// Un utilisateur lambda ne doit pas avoir accès en écriture à certaines tables
				$this->roles = array();
				$this->dbRetrRoles();
			}
		} elseif ($_SESSION['utilisateur']->hasRole('teamEdit')) {
			if ($param['role'] == 'teamEdit' || $param['role'] == 'my_edit' || $param['role'] == 'heures') {
				$_SESSION['db']->db_interroge(sprintf("
					REPLACE INTO `TBL_ROLES`
					(`uid`, `role`, `centre`, `team`, `beginning`, `end`, `confirmed`)
					VALUES
					(%d, '%s', '%s', '%s', '%s', '%s', TRUE)
					", $this->uid
					, $param['role']
					, $param['centre']
					, $param['team']
					, $param['beginning']
					, $param['end']
				));
				// TODO réévaluer les privilèges de l'utilisateur sur la base de données
				// Un utilisateur lambda ne doit pas avoir accès en écriture à certaines tables
				$this->roles = array();
				$this->dbRetrRoles();
			}
		} else {
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Refus ajout de rôle", "DEBUG", "addRole", "Ajout de rôle", "uid:%d;role:%s;admin:%d")'
				, $this->uid()
				, $param['role']
				, $_SESSION['utilisateur']->uid()
				)
			); 
		}
	}
	// Retire un rôle à l'utilsiateur
	public function dropRole($role) {
		if ( $_SESSION['utilisateur']->hasRole('admin') ) {
			$sql = sprintf("
				DELETE FROM `TBL_ROLES`
				WHERE `uid` = %d
				AND `role` = '%s'
				", $this->uid
				, $role
			);
			$_SESSION['db']->db_interroge($sql);
			// TODO réévaluer les privilèges de l'utilisateur sur la base de données
			$this->roles = array();
			$this->dbRetrRoles();
		} elseif ($_SESSION['utilisateur']->hasRole('editeurs')) {
			if ($role == 'editeurs' || $role == 'teamEdit' || $role == 'my_edit' || $role == 'heures') {
				$sql = sprintf("
					DELETE FROM `TBL_ROLES`
					WHERE `uid` = %d
					AND `role` = '%s'
					", $this->uid
					, $role
				);
				$_SESSION['db']->db_interroge($sql);
				// TODO réévaluer les privilèges de l'utilisateur sur la base de données
				$this->roles = array();
				$this->dbRetrRoles();
			}
		} elseif ($_SESSION['utilisateur']->hasRole('teamEdit')) {
			if ($role == 'teamEdit' || $role == 'my_edit' || $role == 'heures') {
				$sql = sprintf("
					DELETE FROM `TBL_ROLES`
					WHERE `uid` = %d
					AND `role` = '%s'
					", $this->uid
					, $role
				);
				$_SESSION['db']->db_interroge($sql);
				// TODO réévaluer les privilèges de l'utilisateur sur la base de données
				$this->roles = array();
				$this->dbRetrRoles();
			}
		}
	}
	public function vismed($vismed = NULL) {
		if (!is_null($vismed)) {
			$this->vismed = new Date($vismed);
		}
		return $this->vismed;
	}
	// Ajoute une affectation à l'utilisateur
	// à partir de $row = array('centre' => , 'team' => , 'beginning' => , 'end' => , 'grade' => );
	// la bdd est mise à jour
	public function addAffectation($row) {
		if (!is_array($row)) return false;
		$row['uid'] = $this->uid();
		$affectation = new Affectation($row);
		$aid = $affectation->insert();
		$this->retrieveAffectations();
		return true;
	}
	/*
	 * Ajoute les affectations venant d'un tableau
	 * $array = ( 0 => ('aid' => 5
	 * 		'centre'	=> 'athis'
	 * 		'team'	=> '9e'
	 * 		'beginning'	=> '1990-01-01'
	 * 		'end'	=> '2000-01-01'
	 * 		)
	 * 	      1 => (...)
	 * 	      )
	 */
	public function addAffectationsTableau($array) {
		$valid = true;
		foreach ($array as $arr) {
			if (is_array($arr)) {
				$this->addAffectation($arr);
			} else {
				$valid = false;
				break;
			}
		}
		if (!$valid) $this->addAffectation($array);
		return sizeof($this->affectations);
	}
	public function deleteAffectation($aid) {
		$this->affectations[$aid]->delete();
		unset($this->affectations[$aid]);
	}
	public function affectations($param = NULL) {
		if (sizeof($this->affectations) < 1) $this->retrieveAffectations();
		if (is_int($param)) return $this->affectations[$param];
		if (is_array($param)) { // Si $param est un tableau, il contient les infos d'une(e) nouvelle(s) affectation(s)
			$this->addAffectationsTableau($param);
		}
		return $this->affectations;
	}
	public function centre () {
		if (is_null($this->centre)) $this->getPresentAffectationFromDb();
		return $this->centre;
	}
	/*
	 * Retourne un tableau des affectations en ordre croissant
	 */
	public function orderedAffectations() {
		if (sizeof($this->orderedAffectations) < 1) $this->retrieveAffectations();
		return $this->orderedAffectations;
	}
	public function team () {
		if (is_null($this->team)) $this->getPresentAffectationFromDb();
		return $this->team;
	}
	public function grade() {
		if (is_null($this->grade)) $this->getPresentAffectationFromDb();
		return $this->grade;
	}
	public function poids($poids = NULL) {
		if (!is_null($poids)) {
			$this->poids = (int) $poids;
		}
		if (isset($this->poids)) {
			return $this->poids;
		} else {
			return -1;
		}
	}
	public function showtipoftheday($showtipoftheday = NULL) {
		if (!is_null($showtipoftheday)) {
			$this->showtipoftheday = ($showtipoftheday == 1 ? 1 : 0);
		}
		if (isset($this->showtipoftheday)) {
			return $this->showtipoftheday;
		} else {
			return false;
		}
	}
	public function dispos($dispos = NULL) {
		if (is_array($dispos)) {
			$this->dispos = $dispos;
		}
		if (isset($this->dispos)) {
			return $this->dispos;
		} else {
			return false;
		}
	}
	/*
	 * Retourne les pages disponibles après la connexion pour l'utilisateur
	 */
	public function availablePages($type = 'titre', $index = NULL) {
		firePhpLog("In availablePages");
		if ($type != 'titre' && $type != 'uri' && $type != 'index') {
			firePhpLog("Mauvais type pour les AvailablePages", $type);
			return NULL;
		}
		if (!is_null($index)) {
			$index = (int) $index;
			if (empty(self::$availablePages[$index][$type]) || self::$availablePages[$index]['gid'] < $this->gid()) $index = 1;
			return self::$availablePages[$index][$type];
		}
		$pages = array();
		foreach (self::$availablePages as $index => $array) {
			firePhpLog('gid', $this->gid());
			if ($array['gid'] >= $this->gid()) {
				if ($type == 'titre') {
					$pages[$index] = $array['titre'];
				} elseif ($type == 'uri') {
					$pages[$index] = $array['uri'];
				} else {
					firePhpWarn("Mauvais type pour les availablesPages", $type);
					return NULL;
				}
			}
		}
		return $pages;
	}
	public function indexPage($index = NULL) {
		if (!is_null($index)) {
			$this->index = (int) $index;

		}
		if (empty($this->indexPage)) {
			foreach (self::$availablePages as $index => $array) {
				if ($this->page() == $array['uri']) {
					$this->indexPage = $index;
					break;
				}
			}
		}
		return $this->indexPage;
	}
	public function messages() {
		return $this->messages;
	}
	public function retrMessages() {
		$result = $_SESSION['db']->db_interroge("SELECT *
			FROM `TBL_MESSAGES_SYSTEME`
			WHERE `catégorie` = 'USER'
			AND lu IS FALSE
			AND `utilisateur` = 'ttm." . $this->uid . "@localhost'");
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$this->messages[] = new message($row);
		}
		return $this->messages;
	}
	public function flushMessages() {
		$this->messages = array();
	}
// Méthodes relatives à la base de données
	public function setFromDb($uid) {
		$sql = sprintf("
			SELECT *
			FROM `TBL_USERS`
			WHERE `uid` = %d
			", $uid
		);
		$result = $_SESSION['db']->db_interroge($sql);
		$row = $_SESSION['db']->db_fetch_assoc($result);
		parent::__construct($row);
		$this->setFromRow($row);
		$this->dbRetrRoles();
		$this->_retrieveContact();
		$this->retrieveAffectations();
	}
	// Vérifie si l'utilisateur existe déjà dans la base de données
	// Pour cela, on vérifie si l'email est déjà présent dans la bdd
	public function emailAlreadyExistsInDb() {
		$result = $_SESSION['db']->db_interroge(sprintf("
			SELECT `nom`
			, `prenom`
			, `login`
			FROM `TBL_USERS`
			WHERE `email` = '%s'
			", $_SESSION['db']->db_real_escape_string($this->email())
		));
		$return = false;
		if (mysqli_num_rows($result) > 0) $return = $_SESSION['db']->db_fetch_assoc($result);
		mysqli_free_result($result);
		return $return;
	}
	// Vérifie si le login est déjà utilisé
	// Retourne true si un utilisateur existant utilise déjà le login
	public function loginAlreadyExistsInDb() {
		$result = $_SESSION['db']->db_interroge(sprintf("
			SELECT `nom`
			FROM `TBL_USERS`
			WHERE `login` = '%s'
			AND `nom` != '%s'
			AND `prenom` != '%s'
			AND `email` != '%s'
			", $_SESSION['db']->db_real_escape_string($this->login())
			, $_SESSION['db']->db_real_escape_string($this->nom())
			, $_SESSION['db']->db_real_escape_string($this->prenom())
			, $_SESSION['db']->db_real_escape_string($this->email())
		));
		$return = false;
		if (mysqli_num_rows($result) > 0) $return = $_SESSION['db']->db_fetch_assoc($result);
		mysqli_free_result($result);
		return $return;
	}
	// Remplit les champs centre, team et grade avec les valeurs actuelles
	public function getPresentAffectationFromDb() {
		if (is_null($this->centre) || is_null($this->team) || is_null($this->grade)) {
			$result = $_SESSION['db']->db_interroge(sprintf("
				SELECT *
				FROM `TBL_AFFECTATION`
				WHERE `uid` = %d
				AND `beginning` < DATE(NOW())
				AND `end` > DATE(NOW())"
				, $this->uid()
			));
			if (mysqli_num_rows($result) != 1) return false;
			$row = $_SESSION['db']->db_fetch_assoc($result);
			$this->centre = $row['centre'];
			$this->team = $row['team'];
			$this->grade = $row['grade'];
			$this->affectations[$row['aid']] = new Affectation($row);
			mysqli_free_result($result);
			return $this->affectations[$row['aid']];
		}
	}
	// Recherche l'historique des affectations
	public function retrieveAffectations($index = NULL) {
		$result = $_SESSION['db']->db_interroge(sprintf("
			SELECT *
		       	FROM `TBL_AFFECTATION`
			WHERE `uid` = %d
			ORDER BY `end` ASC
			", $this->uid()
		));
		$this->affectations = array();
		$this->orderedAffectations = array();
		$i = 0;
		$today = new Date('Y-m-d'); // La date du jour pour définir le centre et la team actuels
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$this->affectations[$row['aid']] = new Affectation($row);
			$this->orderedAffectations[$i] = $this->affectations[$row['aid']];
			$i++;
			// Rempli l'affectation du jour
			if ($this->affectations[$row['aid']]->beginning()->compareDate($today) > 0 && $this->affectations[$row['aid']]->end()->compareDate($today) < 0) {
				$this->centre($this->affectations[$row['aid']]->centre());
				$this->team($this->affectations[$row['aid']]->team());
				$this->grade($this->affectations[$row['aid']]->grade());
			}
		}
		mysqli_free_result($result);
		if (is_null($index)) return $this->affectations;
		if (isset($this->affectations[$index])) return $this->affectations[$index];
		return NULL;
	}
	/************************
	 * Gestion des contacts *
	 ************************/
	protected function _retrieveAdresse($index = NULL) {
		firePhpLog($index, '_retrieveAdresse');
		$sql = sprintf("
			SELECT *
			FROM `TBL_ADRESSES`
			WHERE `uid` = %d"
			, $this->uid
		);
		$result = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$this->adresse[$row['adresseid']] = new Adresse($row);
		}
		mysqli_free_result($result);
		if (is_null($index)) return $this->adresse;
		if (isset($this->adresse[$index])) return $this->adresse[$index];
		return NULL;
	}
	protected function _retrievePhone($index = NULL) {
		firePhpLog($index, '_retrievePhone');
		$sql = sprintf("
			SELECT *
			FROM `TBL_PHONE`
			WHERE `uid` = %d"
			, $this->uid
		);
		$result = $_SESSION['db']->db_interroge($sql);
		while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$this->phone[$row['phoneid']] = new Phone($row);
		}
		mysqli_free_result($result);
		if (is_null($index)) return $this->phone;
		if (isset($this->phone[$index])) return $this->phone[$index];
		return NULL;
	}
	protected function _retrieveContact() {
		$this->_retrieveAdresse();
		$this->_retrievePhone();
	}
	/*
	 * Mise à jour des informations
	 */
	protected function _updateUser() {
		return $_SESSION['db']->db_update('TBL_USERS', $this->userAsArray());
	}
	protected function _updatePhone() {
		foreach ($this->phone() as $phone) {
			$phone->update();
		}
	}
	protected function _updateAdresse() {
		foreach ($this->adresse() as $adresse) {
			$adresse->update();
		}
	}
	public function updateContact() {
		$this->_updatePhone();
		$this->_updateAdresse();
	}
	public function fullUpdateDB() {
		$this->_updateUser();
		$this->updateContact();
		//$this->_updateClasse();
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
	// Retourne un tableau dont la première ligne contient les noms des champs
	// et la seconde un tableau avec le contenu des champs accompagnés
	// d'informations nécesasires pour l'affichage html (id...)
	// $champs est un tableau contenant les champs à retourner
	public function displayUserInfos($champs) {
		$table = array();
		$index = 0;
		foreach ($champs as $champ) {
			$table[$champ] = array('Field'	=> $champ
				, 'content'		=> method_exists($champs, 'utilisateurGrille') ? $this->$champ() : 'unknown'
				, 'id'			=> $champ . $this->uid()
				, 'label'		=> _label($champ)
			);
		}
		return $table;
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
			SELECT *
			FROM `TBL_USERS`
			%s
			%s"
			, $cond
			, $order
		);
		return $this->retourneUsers($sql);
	}
	public function getActiveUsers($centre = NULL, $team = NULL, $condition = NULL, $order = "ORDER BY `poids` ASC") {
		$cond = array("`actif` = 1");
		if (!is_null($centre)) {
			if (is_string($centre)) {
				$cond[] = "`centre` = '$centre'";
			} elseif (is_array($centre)) {
				$co = "(";
				foreach ($centre as $cent) {
					$co .= "`centre` = '$cent' OR ";
				}
				$cond[] = substr($co, 0, -4) . ')';
			}
		}
		if (!is_null($team)) {
			if (is_string($team)) {
				$cond[] = "`team` = '$team'";
			} elseif (is_array($team)) {
				$co = "(";
				foreach ($team as $cent) {
					$co .= "`team` = '$cent' OR ";
				}
				$cond[] = substr($co, 0, -4) . ')';
			}
		}
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
			$sql = "SELECT DISTINCT `TU`.`uid`,
				`TU`.*,
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
			$sql = "SELECT DISTINCT `TU`.`uid`,
				`TU`.*,
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
			$sql = "SELECT DISTINCT `TU`.`uid`,
				`TU`.*,
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
							,'classe'	=> 'decompte'
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
									WHERE `sdid` = (SELECT `sdid`
								       			FROM `TBL_L_SHIFT_DISPO`
											WHERE `date` = '%s'
											AND `uid` = %d
											AND `did` IN (SELECT `did`
													FROM `TBL_DISPO`
													WHERE `type decompte` = 'conges')
										)
									", $dateVacation
									, $user['uid']
								));
								if (mysqli_num_rows($result) < 1) {
									$classe .= " erreur";
								} else {
									$row = $_SESSION['db']->db_fetch_row($result);
									if (1 == $row[0]) $classe .= " filed";
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
							,'title'	=> !empty($vacation[$user['uid']]) && isset($proprietesDispos[$vacation[$user['uid']]]['nom_long']) ? $proprietesDispos[$vacation[$user['uid']]]['nom_long'] : ''
						);
					}
					// La dernière colonne contient les décomptes horizontaux calculés
					// La date est celle de dateRef + durée du cycle
			/*$dateSuivante = clone $cycle[$i]->dateRef();
			$dateSuivante->addJours(Cycle::getCycleLength());*/
					$grille[$compteurLigne][] = array(
						'nom'		=> 0+$cycle[$i]->compteTypeUserFin($user['uid'], 'dispo')
						,'id'		=> sprintf("decDispou%sc%s", $user['uid'], $cycle[$i]->cycleId()+1)
						,'classe'	=> 'decompte'
					);
					if ($nbCycle == 1) {
						foreach (array_keys($evenSpec) as $even) {
							$grille[$compteurLigne][] = array(
								'nom'		=> empty($evenSpec[$even]['uid'][$user['uid']]['nom']) ? 0 : $evenSpec[$even]['uid'][$user['uid']]['nom']
								,'id'		=> empty($evenSpec[$even]['uid'][$user['uid']]['id']) ? "" : $evenSpec[$even]['uid'][$user['uid']]['id']
								,'title'	=> empty($evenSpec[$even]['uid'][$user['uid']]['title']) ? "" : $evenSpec[$even]['uid'][$user['uid']]['title']
								,'classe'	=> "decompte" . (empty($evenSpec[$even]['uid'][$user['uid']]['classe']) ? "" : $evenSpec[$even]['uid'][$user['uid']]['classe'])
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
