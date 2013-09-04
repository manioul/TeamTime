<?php
// class_affectations.inc.php
//
// Permet de gérer les affectations des utilisateurs
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

class Affectation {
	private $aid;
	private $uid;
	private $centre;
	private $team;
	private $grade;
	private $beginning;
	private $end;
	private $table = 'TBL_AFFECTATION'; // La table qui gère les affectations
	private $previousAid = NULL; // Index de la précédente affectation
	private $nextAid = NULL; // Index de l'affectation suivante
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
	public function __desctruct() {
		return true;
	}
// Accesseurs
	public function aid($aid = NULL) {
		if (!is_null($aid)) {
			$this->aid = $aid;
		}
		return $this->aid;
	}
	public function uid($uid = NULL) {
		if (!is_null($uid)) {
			$this->uid = $uid;
		}
		return $this->uid;
	}
	public function centre($centre = NULL) {
		if (!is_null($centre)) {
			$this->centre = $centre;
		}
		return $this->centre;
	}
	public function team($team = NULL) {
		if (!is_null($team)) {
			$this->team = $team;
		}
		return $this->team;
	}
	public function grade($grade = NULL) {
		if (!is_null($grade)) {
			$this->grade = $grade;
		}
		return $this->grade;
	}
	public function beginning($beginning = NULL) {
		if (!is_null($beginning)) {
			$this->beginning = new Date($beginning);
		}
		return $this->beginning;
	}
	public function end($end = NULL) {
		if (!is_null($end)) {
			$this->end = new Date($end);
		}
		return $this->end;
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
	 * Alias de getFromAid
	 */
	public function setFromDb($param) {
		return $this->getFromAid($param);
	}
	public function previousAid($previousAid = NULL) {
		if (!is_null($previousAid)) return $this->previousAid = $previousAid;
		if (is_null($this->previousAid)) {
			$this->_getPreviousAid();
		}
		return $this->previousAid;
	}
	public function nextAid($nextAid = NULL) {
		if (!is_null($nextAid)) return $this->nextAid = $nextAid;
		if (is_null($this->nextAid)) {
			$this->_getNextAid();
		}
		return $this->nextAid;
	}
	// Retourne l'objet sous forme de tableau
	public function asArray() {
		return array(
			'aid'		=> $this->aid
			, 'uid'		=> $this->uid
			, 'centre'	=> $this->centre
			, 'team'	=> $this->team
			, 'grade'	=> $this->grade
			, 'beginning'	=> $this->beginning
			, 'end'		=> $this->end
		);
	}
// Méthode de la bdd
	public function getFromAid($aid) {
		$sql = "SELECT *
		       	FROM `$this->table`
			WHERE
			`aid` = '$aid'";
		var_dump($sql);
		$result = $_SESSION['db']->db_interroge($sql);
		$row = $_SESSION['db']->db_fetch_assoc($result);
		mysqli_free_result($result);
		var_dump($row);
		$this->setFromRow($row);
		return true;
	}
	public function update() {
		return $_SESSION['db']->db_update($this->table, $this->asArray());
	}
	public function insert() {
		return $this->aid($_SESSION['db']->db_insert($this->table, $this->asArray()));
	}
	public function delete() {
		$sql = sprintf("
			DELETE FROM `%s`
			WHERE `aid` = %d"
			, $this->table
			, $this->aid()
		);
		$_SESSION['db']->db_interroge($sql);
		$this->__destruct();
		unset($this);
	}
	protected function _getPreviousAid() {
		$sql = sprintf("
			SELECT `aid`
			FROM `%s`
			WHERE
			`beginning` > '%s'
			ORDER BY `beginning` ASC
			LIMIT 1"
			, $this->table
			, $this->beginning()->date()
		);
		$result = $_SESSION['db']->db_interroge($sql);
		if (mysqli_num_rows($result) < 1) {
			$this->previousAid = NULL;
		} else {
			$row = $_SESSION['db']->db_fetch_row($result);
			$this->previousAid = $row[0];
		}
		mysqli_free_result($result);
		return $this->previousAid;
	}
	protected function _getNextAid() {
		$sql = sprintf("
			SELECT `aid`
			FROM `%s`
			WHERE
			`beginning` < '%s'
			ORDER BY `beginning` DESC
			LIMIT 1"
			, $this->table
			, $this->beginning()->date()
		);
		$result = $_SESSION['db']->db_interroge($sql);
		if (mysqli_num_rows($result) < 1) {
			$this->nextAid = NULL;
		} else {
			$row = $_SESSION['db']->db_fetch_row($result);
			$this->nextAid = $row[0];
		}
		mysqli_free_result($result);
		return $this->nextAid;
	}
}
