<?php
// class_utilisateur.inc.php
//
// classe implémentant les informations et la gestion des utilisateurs

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

class groupe {
// Déclaration des variables
	private $groupe;
	private $description;
	private $canwrite = 0;
	private $actif = 0;

	public function __construct() {}
	public function __destruct() {}
// Attribution/lecture des valeurs de l'objet
	public function groupe($param = NULL) {
		if ($param == NULL) {
			$this->groupe = $param;
		}
		return $this->groupe;
	}
	public function description($param = NULL) {
		if ($param == NULL) {
			$this->description = $param;
		}
		return $this->description;
	}
	public function setCanwrite() {
		$this->canwrite = 1;
	}
	public function unsetCanwrite() {
		$this->canwrite = 0;
	}
	public function setactif() {
		$this->actif = 1;
	}
	public function unsetactif() {
		$this->actif = 0;
	}
// Fonctions de bdd
	private function _db_insertGroupe() {
		// Ajoute un groupe à la base
		$insert = sprintf ("INSERT INTO `TBL_GROUPS` (groupe, description, canwrite, actif) VALUES ('%s', '%s', '%s', '%s')", $this->groupe, $this->description, $this->canwrite, $this->actif);
		interroge_db($insert);
	}
	private function _db_updateGroupe() {
		// Met à jour la description pour le groupe dans la base
		$update = sprintf ("UPDATE `TBL_GROUPS` SET description = '%s', canwrite = '%s', actif = '%s' WHERE groupe = '%s'", $this->description, $this->canwrite, $this->actif, $this->groupe);
		interroge_db($insert);
	}
	public function _db_groupeExists() {
		// Retourne TRUE si $groupe existe dans la base
		$select = sprintf ("SELECT * FROM `TBL_GROUPS` WHERE groupe = '%s'", $this->groupe);
		if (mysqli_num_rows(interroge_db($select)) > 0) { return TRUE; }
		else { return FALSE; }
	}
	public function _db_s_insertGroupe() {
		// Ajoute un groupe à la base ou le met à jour
		if ($this->db_groupeExists()) {
			$this->db_updateGroupe();
		} else {
			$this->db_insertGroupe();
		}
	}
}

//*******************
// Classe utilisateur
//*******************
class utilisateur {
// Définition des variables utilisées dans l'objet
	private $login;
	private $sha1 = NULL;
	private $email;
	private $creation;
	private $modification;
	private $actif; // TODO modifié pour grille => vérifier que portail a été correctement mis à jour
	private $locked;
	private $nblogin;
	private $lastlogin;
	private $groupes = array();
	public $dbs = array(); /* Un tableau d'objet db auxquels l'utilisateur a accès
			 Certains objets db peuvent être NULL si l'utilisateur
			 n'a pas les droits suffisants
		      */
	private $page = NULL; // La page qui est affichée après la connexion de l'utilisateur
	protected static $fieldsDefinition = NULL;
	// Retourne une définition des champs pour être utlisé par un tableau html
	// $correspondances est un tableau contenant des correspondances
	// entre les champs de la table et une étiquette à afficher dans le tableau html
	// $regen permet de régénérer la définition si positionné
	protected static function _fieldsDefinition($correspondances, $regen = NULL) {
		if (is_null($regen) || is_null($this->fieldsDefinition)) {
			// Recherche les champs de la table des utilisateurs
			foreach ($_SESSION['db']->db_getColumnsTable("TBL_USERS") as $row) {
				self::$fieldsDefinition[$row['Field']]['Field'] = isset($label[$row['Field']]) ? $label[$row['Field']] : $row['Field'];
				if ($row['Extra'] == 'auto_increment' || $row['Field'] == 'nblogin' || $row['Field'] == 'lastlogin') {
					// Ce champ ne sera pas saisi par l'utilisateur
				} else {
					self::$fieldsDefinition[$row['Field']]['width'] = -1;
					if (preg_match('/\((\d*)\)/', $row['Type'], $match) == 1) {
						if ($match[1] > 1) {
							self::$fieldsDefinition[$row['Field']]['width'] = ($match[1] < 10) ? $match[1] : 10;
							self::$fieldsDefinition[$row['Field']]['maxlength'] = $match[1];
						}
					}
					if (preg_match('/int\((\d*)\)/', $row['Type'], $match)) {
						if ($match[1] == 1) {
							self::$fieldsDefinition[$row['Field']]['type'] = "checkbox";
							self::$fieldsDefinition[$row['Field']]['value'] = 1;
						} else {
							self::$fieldsDefinition[$row['Field']]['type'] = "text";
						}
					} elseif ($row['Field'] == 'email') {
						self::$fieldsDefinition[$row['Field']]['type'] = 'email';
					} elseif ($row['Field'] == 'password') {
						self::$fieldsDefinition[$row['Field']]['type'] = 'password';
					} elseif ($row['Type'] == 'date') {
						self::$fieldsDefinition[$row['Field']]['type'] = 'date';
						self::$fieldsDefinition[$row['Field']]['maxlength'] = 10;
						self::$fieldsDefinition[$row['Field']]['width'] = 6;
					} else {
						self::$fieldsDefinition[$row['Field']]['type'] = 'text';
					}
				}
			}
		}
		return self::$fieldsDefinition;
	}
// Constructeur
	function __construct($row = NULL) {
		$this->login = '';
		if (NULL !== $row) {
			if (is_array($row)) {
				$this->_setFromRow($row);
			}
		}
		//$this->_db_setGroupes();
		//$this->_attribDB();
	}
	function __destruct() {
		$this->logout();
		unset($this);
	}
	// Accesseurs
	private function _setFromRow($row) {
		foreach ($row as $cle => $valeur) {
			if (method_exists($this, $cle)) {
				$this->$cle($valeur);
			} else {
				// $cle/$valeur ignorée TODO
			}
		}
	}
	public function login ($param = NULL) {
		if (!is_null($param)) {
			$this->login = $param;
		}
		return $this->login;
	}
	public function sha1 ($param = NULL) {
		if (!is_null($param)) {
			$this->sha1 = $param;
		}
		return $this->sha1;
	}
	public function password($param = NULL) {
		if (is_null($param)) return false;
		$this->sha1(sha1($this->login . $param));
		return true;
	}
	public function email ($param = NULL) {
		if (!is_null($param)) {
			$this->email = $param;
		}
		return $this->email;
	}
	public function creation ($param = NULL) {
		if (!is_null($param)) {
			$this->creation = $param;
		}
		return $this->creation;
	}
	public function modification ($param = NULL) {
		if (!is_null($param)) {
			$this->modification = $param;
		}
		return $this->modification;
	}
	public function actif ($param = NULL) {
		if (!is_null($param)) {
			$this->actif = ($param == 1 ? 1 : 0);
		}
		return $this->actif;
	}
	public function nblogin($param = NULL) {
		if (!is_null($param)) {
			$this->nblogin = $param;
		}
		return $this->nblogin;
	}
	public function lastlogin ($param = NULL) {
		if (!is_null($param)) {
			$this->lastlogin = $param;
		}
		return $this->lastlogin;
	}
	public function locked($param = NULL) {
		if (!is_null($param)) {
			$this->locked = ($param == 1 ? 1 : 0);
		}
		return $this->locked;
	}
	public function page($param = NULL) {
		if (!is_null($param)) {
			$this->page = $param;
		}
		return $this->page;
	}
// Opérations sur l'objet
	private function _attribDB() {
		// Attribue une nouvelle connexion à la base de données
		$this->dbs['nobody'] = new db($GLOBALS['DSN']['nobody']);
		if ($this->isInGroupe('logged')) {
			$this->dbs['logged'] = new db($GLOBALS['DSN']['logged']);
		} else {
			$this->dbs['logged'] = NULL;
		}
		if ($this->isInGroupe('operateur')) {
			$this->dbs['operateur'] = new db($GLOBALS['DSN']['operateur']);
		} else {
			$this->dbs['operateur'] = NULL;
		}
		if ($this->isInGroupe('webmaster')) {
			$this->dbs['webmaster'] = new db($GLOBALS['DSN']['webmaster']);
		} else {
			$this->dbs['webmaster'] = NULL;
		}
		if ($this->isInGroupe('admin')) {
			$this->dbs['admin'] = new db($GLOBALS['DSN']['admin']);
		} else {
			$this->dbs['admin'] = NULL;
		}
	}
	public function isInGroupe($groupe) { // retourne TRUE si l'utilisateur est dans le groupe $groupe
		if (is_array($this->groupes) && in_array($groupe, $this->groupes)) { return TRUE; }
		return FALSE;
	}
	private function _updateNbLogin() {
		$this->nblogin++;
	}
	public function logout() {
		$this->__construct();
		if (!empty($conf['session_cookie']['name']) && !empty($_COOKIE[$conf['session_cookie']['name']])) {
			$_SESSION = array ();
			unset ($_COOKIE[$conf['session_cookie']['name']]);
			session_destroy ();
		}
	}
	public function lockAccount() {
		$this->locked = true;
	}
	public function unlockAccount() {
		$this->locked = false;
	}
// Opérations sur la bdd
	private function _db_setGroupes() {
		if ($this->login == '') { $this->groupes = array('nogroup'); }
		else {
			$select = sprintf ("SELECT login,groupe FROM `TBL_USERS_GROUPS` WHERE login = '%s';", $this->login);
			$result = $this->dbs['logged']->db_interroge($select);
			if (mysqli_num_rows($result)) {
				$this->groupes = array('logged');
				while ($row = $_SESSION['db']->db_fetch_array($result)) {
					$this->groupes[] = $row[1];
				}
			} else { $this->groupes = array('nogroup'); }
			mysqli_free_result($result);
		}
	}
	private function __db_insertUserRequete() {
		return sprintf("INSERT INTO `TBL_USERS` login, sha1, email, actif VALUES ('%s', '%s', '%s', '%i')", $this->login, $this->sha1, $this->email, $this->actif);
	}
	private function _db_insertUser() {
		$this->dbs['admin']->db_interroge($this->__db_insertUserRequete());
	}
	private function _db_updateNbLogin() {
		$requete = sprintf("UPDATE `TBL_LAST_LOGIN` SET nblogin = '%i' WHERE login = '%s'", $this->nblogin, $this->login);
		$_SESSION['utilisateur']->dbs['logged']->db_interroge($requete);
	}
	private function _db_checkPassword() {
		if ($this->sha1 == NULL) { return FALSE; }
		$requete = sprintf("SELECT sha1 FROM `TBL_USERS` WHERE login = '%s'", $this->login);
		// Une connexion à la base est créée spécialement pour la lecture du mot de passe (l'utilisateur n'est pas encore logué)
		$base = new db($GLOBALS['DSN']['logged']);
		$result = $base->db_interroge($requete);
		$row = $_SESSION['db']->db_fetch_array($result);
		mysqli_free_result($result);
		$hash = $row[0];
		$base->__destruct(); // la connexion à la base est supprimée
		$hash = sha1($hash . $_SESSION['timestamp']);
		if ($hash == sha1($_REQUEST['password'] . $_SESSION['timestamp'])) { // login réussi
			$_SESSION['logged'] = TRUE;
			$this->_attribDB(); // Réattribue les connexions aux bdd
			$requete = sprintf("SELECT * FROM `TBL_USERS` WHERE login = '%s'", $this->login);
			$result = $this->dbs['logged']->_db_interroge($requete);
			$this->_setFromRow($_SESSION['db']->db_fetch_assoc($result));
			mysqli_free_result($result);
			$this->_updateNbLogin(); // màj de nblogin
			$this->_db_updateNbLogin(); // màj de nblogin dans la bdd
			$this->_db_setGroupes(); // attribue les groupes
			return TRUE;
		}
		return FALSE;
	}

	/*
	public function add_groupe($groupe) {
		$this->groupes[] = $groupe;
	}
	public function get_groupes_listes()	{
		$array = array();
		if ($_SESSION['utilisateur']->is_in_groupe('admin')) {
			$select = "SELECT * FROM `TBL_GROUPS`";
			$result = interroge_db($select);
			while ($row = $_SESSION['db']->db_fetch_array($result)) {
				$array[$row[0]] = $row[0];
			}
			mysqli_free_result($result);
			// On rajoute les groupes virtuels
			$array['logged'] = 'logged';
			$array['nogroup'] = 'nogroup';
		} elseif (is_array($this->groupes)) {
			foreach ($this->groupes as $groupe) {
				$array[$groupe] = $groupe;
			}
		}
		return $array;
	}

	// Modification des valeurs
	public function incnblogin() {
		// Incrémente de nblogin. A invoquer lors d'une nouvelle connexion
		$this->nblogin++;
	}
	public function updtlastlogin () {
		// update le lastlogin avec la date courante
		$this->setlastlogin(date ("Y-m-d H:i:s"));
	}
	// gestion des connexions de l'utilisateur
	public function check_pwd($hash) {
		if (DEBUG) { echo 'checking password...<br />'; }
		$select = sprintf ("SELECT * FROM `TBL_USERS` WHERE login = '%s' AND efface = '0'", substr($_SESSION['db']->escape_string($this->login), 0, SZ_LOGIN));
		$row = $_SESSION['db']->db_fetch_assoc (interroge_db ($select));
		$md5 = hash ($row['pwd'] . $_SESSION['ts']);
		if ($hash == $md5) {
			$this->set_from_row($row);
			$this->new_connexion(); // On crée une nouvelle connexion
			// On réinitialise la variable aléatoire de connexion 
			unset ($_SESSION['ts']);
			return TRUE;
		} else {
			return FALSE;
		}
	}
	public function new_connexion() {
		// Effectue toutes les actions qui doivent etre faites lors d'une nouvelle connexion
		if (DEBUG) { echo 'nouvelle connexion<br />'; }
		$_SESSION['logged'] = TRUE;
		$this->set_groupes();
		$this->set_db_link();
		$this->incnblogin();
		$this->updtlastlogin();
		$this->maj_db();
	}
	public function deconnecte() {
		// Effectue les actions nécessaires à la déconnexion de l'utilisateur
		$this->set_db_link();
		$this->vide();
	}
	public function db_update_user() {
		$update	= sprintf ("UPDATE `TBL_USERS` SET sha1 = '%s', actif = '%i' WHERE login = '%s'", $_SESSION['db']->escape_string($this->sha1()), $_SESSION['db']->escape_string($this->actif()), mysql_escape_string($this->login()));
		return (interroge_db($update));
	}
	// Crée un nouvel utilisateur
	public function db_create_user() {
		// Ajoute l'utilisateur dans TBL_USERS
		$insert	= sprintf ("INSERT INTO `TBL_USERS` (login, sha1, email, actif) VALUES ('%s', '%s', '%s', '%s')", $_SESSION['db']->escape_string($this->login()), mysql_escape_string($this->sha1()), mysql_escape_string($this->_email()), $this->actif());
		interroge_db ($insert);
		// Crée une entrée dans TBL_LAST_LOGIN
		$insert = sprintf ("INSERT INTO `TBL_LAST_LOGIN` (login, lastlogin) VALUES ('%s', NOW())", $this->login());
		interroge_db ($insert);
	}
	// Crée une condition sql pour sélectionner les gid auxquels appartient l'utilisateur
	// Si $field est non nul alors c'est le nom du champ comparé aux gids
	public function condition_groupe ($field = 'groupe') {
		if (in_array('admin', $this->groupes)) {
			// Retourne une condition toujours valide pour l'admin
			return '1 = 1';
		}
		$condition = "( " . $field . " = 'nogroup' OR ";
		foreach ($this->groupes as $groupe) {
			if ($groupe == 'nogroup') { continue; }
			$condition .= sprintf ("%s = '%s' OR ", $field, $groupe);
		}
		$condition = substr ($condition, 0, -4);
		$condition .= " )";
		return $condition;
	}
	// Retourne le lastlogin sous une forme francisée
	public function lastlogin_fr() {
		return date_sql2fr($this->lastlogin);
	}
	*/
}

?>
