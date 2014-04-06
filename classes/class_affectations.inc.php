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
	private $centreDisplay;
	private $teamDisplay;
	private $gradeDisplay;
	private $beginning;
	private $end;
	private $table = 'TBL_AFFECTATION'; // La table qui gère les affectations

	// Recherche les affectations possibles en fonction du type (centre, team, grade)
	// et utilise $selected pour définir la valeur par défaut
	// Ceci est directement utilisable avec html.form.select.tpl
	public static function listeAffectations($type, $selected = NULL) {
		$array = array('name' => $type);
		$index = 0;
		$sql = sprintf("SELECT `nom`, `description`
			FROM `TBL_CONFIG_AFFECTATIONS`
			WHERE `type` = '%s'
			", $_SESSION['db']->db_real_escape_string($type));
		$result = $_SESSION['db']->db_interroge($sql);
		while($row = $_SESSION['db']->db_fetch_assoc($result)) {
			$array['options'][$index]['content'] = $row['description'];
			$array['options'][$index]['value'] = $row['nom'];
			if (!is_null($selected) && $row['nom'] == $selected) {
				$array['options'][$index]['selected'] = "selected";
			}
			$index++;
		}
		mysqli_free_result($result);		
		return $array;
	}
// Constructeur
	public function __construct($param = NULL) {
		if (is_null($param)) return true;
		if (is_int($param)) {
			$this->setFromDb($param);
		} elseif (is_array($param)) {
			$this->setFromRow($param);
		}
	}
	public function __destruct() {
		return true;
	}
// Accesseurs
	public function aid($aid = NULL) {
		if (!is_null($aid)) {
			$this->aid = (int) $aid;
		}
		return $this->aid;
	}
	public function uid($uid = NULL) {
		if (!is_null($uid)) {
			$this->uid = (int) $uid;
		}
		return $this->uid;
	}
	public function centre($centre = NULL) {
		if (!is_null($centre)) {
			$this->centre = (string) $centre;
		}
		return $this->centre;
	}
	public function team($team = NULL) {
		if (!is_null($team)) {
			$this->team = (string) $team;
		}
		return $this->team;
	}
	public function grade($grade = NULL) {
		if (!is_null($grade)) {
			$this->grade = (string) $grade;
		}
		return $this->grade;
	}
	public function centreDisplay() {
		if (!isset($this->centreDisplay)) {
			$sql = sprintf("
				SELECT `description`
				FROM `TBL_CONFIG_AFFECTATIONS`
				WHERE `nom` = '%s'
				", $this->centre);
			$row = $_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($sql));
			$this->centreDisplay = $row['description'];
		}
		return $this->centreDisplay;
	}
	public function teamDisplay() {
		if (!isset($this->teamDisplay)) {
			$sql = sprintf("
				SELECT `description`
				FROM `TBL_CONFIG_AFFECTATIONS`
				WHERE `nom` = '%s'
				", $this->team);
			$row = $_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($sql));
			$this->teamDisplay = $row['description'];
		}
		return $this->teamDisplay;
	}
	public function gradeDisplay() {
		if (!isset($this->gradeDisplay)) {
			$sql = sprintf("
				SELECT `description`
				FROM `TBL_CONFIG_AFFECTATIONS`
				WHERE `nom` = '%s'
				", $this->grade);
			$row = $_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($sql));
			$this->gradeDisplay = $row['description'];
		}
		return $this->gradeDisplay;
	}
	/*
	 * Retourne le début de l'affectation comme une chaîne formattée pour la bdd
	 */
	public function beginning($beginning = NULL) {
		if (!is_null($beginning)) {
			if (is_a($beginning, 'Date')) {
				$this->beginning = $beginning;
			} else {
				$this->beginning = new Date($beginning);
			}
		}
		return $this->beginning;
	}
	// Alias pour la méthode beginning (utilisée pour la récupération des valeurs du formulaire)
	public function dateD($beginning = NULL) {
		return $this->beginning($beginning);
	}
	/*
	 * Retourne la fin de l'affectation comme une chaîne formattée pour la bdd
	 */
	public function end($end = NULL) {
		if (!is_null($end)) {
			if (is_a($end, 'Date')) {
				$this->end = $end;
			} else {
				$this->end = new Date($end);
			}
		}
		return $this->end;
	}
	// Alias pour la méthode end (utilisée pour la récupération des valeurs du formulaire)
	public function dateF($end = NULL) {
		return $this->end($end);
	}
	public function setFromRow($row) {
		foreach ($row as $key => $value) {
			if (method_exists($this, $key)) {
				$this->$key($value);
			} else {
				$this->$key = $value;
			}
		}
	}
	/*
	 * Alias de getFromAid
	 */
	public function setFromDb($param) {
		return $this->getFromAid($param);
	}
	// Retourne l'objet sous forme de tableau
	public function asArray() {
		return array(
			'aid'		=> $this->aid
			, 'uid'		=> $this->uid
			, 'centre'	=> $this->centre
			, 'team'	=> $this->team
			, 'grade'	=> $this->grade
			, 'beginning'	=> $this->beginning->date()
			, 'end'		=> $this->end->date()
		);
	}
// Méthode de la bdd
	public function getFromAid($aid) {
		$sql = "SELECT *
		       	FROM `$this->table`
			WHERE
			`aid` = '$aid'";
		$result = $_SESSION['db']->db_interroge($sql);
		$row = $_SESSION['db']->db_fetch_assoc($result);
		mysqli_free_result($result);
		$this->setFromRow($row);
		return true;
	}
	public function insert() {
		$sql = sprintf("
			CALL addAffectation( %d, '%s', '%s', '%s', '%s', '%s')
			", $this->uid
			, $this->centre
			, $this->team
			, $this->grade
			, $this->beginning->date()
			, $this->end->date()
		);
		$_SESSION['db']->db_interroge($sql);
		return $_SESSION['db']->db_insert_id();
	}
	public function delete() {
		$sql = sprintf("
			DELETE FROM `%s`
			WHERE `aid` = %d"
			, $this->table
			, $this->aid()
		);
		$_SESSION['db']->db_interroge($sql);
		/*
		 * Mise à jour de la liste chaînée
		 */
		$this->__destruct();
		unset($this);
	}
}
