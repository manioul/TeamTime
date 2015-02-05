<?php
// class_contacts.inc.php
//
// Deux classes (Phone et Adresse) pour gérer les contacts des utilisateurs
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
require_once 'config.inc.php';

class Phone {
	private $phone; // Le numéro de téléphone
	private $phoneid = NULL; // L'identifiant dans la bdd
	private $uid; // L'utilisateur à qui appartient le numéro
	private $description; // la description du numéro
	private $principal = false; // Est-ce le numéro principal
	private $table = 'TBL_PHONE'; // La table qui gère les affectations
// Consctucteur
	public function __construct($param = NULL) {
		if (is_null($param)) return true;
		if (is_int($param)) {
			$this->setFromDb($param);
		}
		if (is_array($param)) {
			$this->setFromRow($param);
		}
	}
	public function __destruct() {
		return true;
	}
// Accesseurs
	public function phone($string = NULL) {
		if (!is_null($string)) {
			$this->phone = $string;
		}
		return $this->phone;
	}
	public function phoneid($string = NULL) {
		if (!is_null($string)) {
			$this->phoneid = (int) $string;
		}
		return $this->phoneid;
	}
	public function uid($string = NULL) {
		if (!is_null($string)) {
			$this->uid = (int) $string;
		}
		return $this->uid;
	}
	public function description($string = NULL) {
		if (!is_null($string)) {
			$this->description = $string;
		}
		return $this->description;
	}
	public function principal($string = false) {
		if (false !== $string) {
			if (!empty($string)) {
				$this->principal = true;
			} else {
				$this->principal = false;
			}
		}
		return $this->principal;
	}
	public function setFromRow($row) {
		foreach ($row as $key => $value) {
			if (method_exists($this, $key)) {
				$this->$key($value);
			} else {
				$this->key = $value;
			}
		}
	}
	/*
	 * alias de getFromPhoneid
	 */
	public function setFromDb($param) {
		return $this->getFromPhoneid($param);
	}
	// Retourne l'objet sous forme de tableau
	public function asArray() {
		return array(
			'phone'		=> $this->phone()
			, 'phoneid'	=> $this->phoneid()
			, 'uid'		=> $this->uid()
			, 'description'	=> $this->description()
			, 'principal'	=> $this->principal()
		);
	}
// Méthode de la bdd
	/*
	 * Création de l'objet à partir d'un phoneid
	 */
	public function getFromPhoneid($phoneid) {
		$sql = sprintf("SELECT *
		       	FROM `%s`
			WHERE
			`phoneid` = %d"
			, $this->table
			, $phoneid
		);
		$row = $_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($sql));
		$this->phone($row['phone']);
		$this->phoneid($row['phoneid']);
		$this->uid($row['uid']);
		$this->description($row['description']);
		$this->principal($row['principal']);
		return true;
	}
	/*
	 * Vérifie si le numéro existe déjà dans la base
	 * et définit le phoneid le cas échéant
	 */
	protected function numberExists() {
		$exists = NULL;
		$sql = sprintf("
			SELECT `phoneid`
			FROM `%s`
			WHERE `phone` = '%s'"
			, $this->table
			, $this->phone
		);
		$result = $_SESSION['db']->db_interroge($sql);
		if (mysqli_num_rows($result) > 0) {
			$row = $_SESSION['db']->db_fetch_assoc($result);
			$this->phoneid = $row['phoneid'];
			$exists = $row['phoneid'];
		}
		mysqli_free_result($result);
		return $exists;
	}
	public function update() {
		return $_SESSION['db']->db_update($this->table, $this->asArray());
	}
	public function insert() {
		// Si un phoneid existe déjà, il s'agit d'une mise à jour et non d'une insertion
		if (!empty($this->phoneid)) {
			$this->update();
			return $this->phoneid;
		} else {
			$this->phoneid = NULL;
			return $this->phoneid($_SESSION['db']->db_insert($this->table, $this->asArray()));
		}
	}
	public function delete() {
		$sql = sprintf("
			DELETE FROM `%s`
			WHERE `phoneid` = %d"
			, $this->table
			, $this->phoneid()
		);
		$_SESSION['db']->db_interroge($sql);
		$this->__destruct();
		unset($this);
	}
}

class Adresse {
	private $adresseid;
	private $uid;
	private $adresse;
	private $cp;
	private $ville;
	private $table = 'TBL_ADRESSES'; // La table qui gère les affectations
// Consctucteur
	public function __construct($param = NULL) {
		if (is_null($param)) return true;
		if (is_int($param)) {
			$this->setFromDb($param);
		}
		if (is_array($param)) {
			$this->setFromRow($param);
		}
	}
	public function __destruct() {
		return true;
	}
// Accesseurs
	public function adresseid($string = NULL) {
		if (!is_null($string)) {
			$this->adresseid = (int) $string;
		}
		return $this->adresseid;
	}
	public function uid($string = NULL) {
		if (!is_null($string)) {
			$this->uid = (int) $string;
		}
		return $this->uid;
	}
	public function adresse($string = NULL) {
		if (!is_null($string)) {
			$this->adresse = $string;
		}
		return $this->adresse;
	}
	public function cp($string = NULL) {
		if (!is_null($string)) {
			$this->cp = $string;
		}
		return $this->cp;
	}
	public function ville($string = NULL) {
		if (!is_null($string)) {
			$this->ville = $string;
		}
		return $this->ville;
	}
	public function setFromRow($row) {
		foreach ($row as $key => $value) {
			if (method_exists($this, $key)) {
				$this->$key($value);
			} else {
				$this->key = $value;
			}
		}
	}
	/*
	 * Alias de getFromAdresseid
	 */
	public function setFromDb($param) {
		return $this->getFromAdresseid($param);
	}
	public function asArray() {
		return array(
			'adresseid'	=> $this->adresseid()
			,'uid'		=> $this->uid()
			,'adresse'	=> $this->adresse()
			,'cp'		=> $this->cp()
			,'ville'	=> $this->ville()
		);
	}
// Méthodes de la bdd
	public function getFromAdresseid($adresseid) {
		$sql = sprintf("SELECT *
			FROM `%s`
			WHERE `adresseid` = %d"
			, $this->table
			, $adresseid
		);
		$row = $_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($sql));
		$this->adresseid($row['adresseid']);
		$this->uid($row['uid']);
		$this->adresse($row['adresse']);
		$this->cp($row['cp']);
		$this->ville($row['ville']);
		return true;
	}
	public function update() {
		return $_SESSION['db']->db_update($this->table, $this->asArray());
	}
	public function insert() {
		// Si un adresseid existe déjà, il s'agit d'une mise à jour et non d'une insertion
		if (!empty($this->adresseid)) {
			$this->update();
			return $this->adresseid;
		} else {
			firePhpLog($this, 'Insertion de adresse dans la base');
			$this->adresseid = NULL;
			return $this->adresseid($_SESSION['db']->db_insert($this->table, $this->asArray()));
		}
	}
	public function delete() {
		$sql = sprintf("
			DELETE FROM `%s`
			WHERE `adresseid` = %d"
			, $this->table
			, $this->adresseid()
		);
		$_SESSION['db']->db_interroge($sql);
		$this->__destruct();
		unset($this);
	}
}
