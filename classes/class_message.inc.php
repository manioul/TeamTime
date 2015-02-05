<?php
// class_message.inc.php
//
// Permet de gérer des messages à destination des utilisateurs
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


class message {
	private $mid;
	private $categorie;
	private $appelant;
	private $short;
	private $message;
	private $contexte;
	private $timestamp;
	private $lu;
// Constructeur
	public function __construct($row = NULL) {
		if (is_null($row)) return true;
		if (is_array($row)) {
			$this->setFromRow($row);
		}
	}
// Accesseurs
	public function mid($param = NULL) {
		if (!is_null($param)) {
			$this->mid = (int) $param;
		}
		return $this->mid;
	}
	public function categorie($param = NULL) {
		if (!is_null($param)) {
			$this->categorie = $param;
		}
		return $this->categorie;
	}
	public function appelant($param = NULL) {
		if (!is_null($param)) {
			$this->appelant = $param;
		}
		return $this->appelant;
	}
	public function short($param = NULL) {
		if (!is_null($param)) {
			$this->short = $param;
		}
		return $this->short;
	}
	public function message($param = NULL) {
		if (!is_null($param)) {
			$this->message = $param;
		}
		return $this->message;
	}
	public function contexte($param = NULL) {
		if (!is_null($param)) {
			$this->contexte = $param;
		}
		return $this->contexte;
	}
	public function timestamp($param = NULL) {
		if (!is_null($param)) {
			$this->timestamp = $param;
		}
		return $this->timestamp;
	}
	public function lu($param = NULL) {
		if (!is_null($param)) {
			$this->lu = $param;
		}
		return $this->lu;
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
	public function asArray() {
		return array(
			'mid'		=> $this->mid
			,'categorie'	=> $this->categorie
			,'appelant'	=> $this->appelant
			,'short'	=> $this->short
			,'message'	=> $this->message
			,'contexte'	=> $this->contexte
			,'timestamp'	=> $this->timestamp
			,'lu'		=> $this->lu
		);
	}
// Méthodes relatives à la base données
	public function setRead() {
		$_SESSION['db']->db_interroge("
			UPDATE TBL_MESSAGES_SYSTEME
			SET lu = TRUE
			WHERE mid = " . $this->mid);
	}
}

?>
