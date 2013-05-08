<?php
// class_db.inc.php

// Librairie de fonctions pour mysql

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

class database {
	private $link;
	private $DSN;
// Constructeur
	function __construct($dsn = NULL) {
		if (isset($dsn)) {
			if (is_array($dsn)) {
				$this->DSN = $dsn;
			}
		} else {
			$this->DSN = $GLOBALS['DSN']['nobody'];
		}
		$this->_db_connect();
		if ( !mysqli_set_charset($this->link, $this->DSN['NAMES']) ) {
			// Erreur du passage en utf8 du dialogue avec la base de données
			firePHPInfo(sprintf('Erreur du passage en %s du dialogue avec la base.', $this->DSN['NAMES']));
		} else {
			firePHPInfo(sprintf('Passage en %s du dialogue avec la base.', $this->character_set()));
		}
	}
	function __destruct() {
		$this->_db_ferme();
	}
	function __sleep() {
		return array( 'DSN' );
	}
	function __wakeup() {
		$this->_db_connect();
	}
// Accesseurs
	// Renvoie l'encoding du dialogue avec la bdd pour être utiliser par htmlspecialchars
	public function encoding() {
		// Tableau des équivalences entre les noms d'encodage pour la base de données
		// (tels que saisis dans le DSN) et les noms d'encodages compris par htmlspecialchars
		$encodings = array(
			'utf8'		=> "UTF-8"
		);
		return $encodings[$this->DSN['NAMES']];
	}
	public function character_set() {
		return mysqli_character_set_name($this->link);
	}
// Gestion de la connexion
	private function _uns_db_connect() {
		$this->link = @mysqli_connect( $this->DSN['hostname'], $this->DSN['username'], $this->DSN['password'], $this->DSN['dbname'] );
		if (mysqli_connect_errno())
		{ // Erreur de connexion
			debug::getInstance()->lastError(ERR_DB_CONN);
			$this->link = ERR_DB_CONN;
		}
	}
	private function _db_connect() {
		if (! is_array($this->DSN)) { $this->DSN = $GLOBALS['DSN']['nobody']; }
		if ( ERR_DB_CONN === $this->_uns_db_connect() ) {
			die ( 'Erreur de connexion (' . mysqli_connect_errno() . ') ' . mysqli_connect_error() );
		}
		$this->_uns_db_set_NAMES();
	}

	private function _uns_db_ferme() {
		if (is_resource($this->link)) {
			mysqli_close($this->link);
		} else {
			debug::getInstance()->lastError(ERR_RSRC_EXPECTED);
		}
	}
	private function _db_ferme() {
		$this->_uns_db_ferme();
	}
	private function _uns_db_set_NAMES() {
		if (!empty($this->DSN['NAMES'])) {
			mysqli_set_charset($this->link, $this->DSN['NAMES']);
			//$this->db_interroge( sprintf("SET NAMES '%s'", $this->DSN['NAMES']) ); // Plante la bdd avec mysqli
		}
	}
// Interrogation
	public function db_interroge ($query) {
		if (! (is_resource($this->link)) ) {
			$this->_db_connect();
		}
		if ( !(	$result = mysqli_query ( $this->link, $query ) ))
		{ // Erreur de requête
			debug::getInstance()->lastError(ERR_DB_SQL);
			debug::getInstance()->triggerError(sprintf("Erreur de requête (%s): %s\n", $query, mysqli_error($this->link)));
		}
		return $result;
	}
	public function db_insert_id() {
		return mysqli_insert_id($this->link);
	}
	public function db_affected_rows() {
		return mysqli_affected_rows($this->link);
	}
	public function db_fetch_row($result) {
		return mysqli_fetch_row($result);
	}
	public function db_fetch_assoc($result) {
		return mysqli_fetch_assoc($result);
	}
	public function db_fetch_array($result) {
		return mysqli_fetch_array($result);
	}
	// Création d'une requête à partir d'un tableau passé en argument
	public function db_requeteFromArray($array) {
		$requete = '';
		foreach ($array as $query) {
			$requete .= $query . ";\n";
		}
		print("$requete");
		return $requete;
	}
	public function db_interrogeArray($array) {
		foreach ($array as $query) {
			$this->db_interroge($query);
		}
	}
	// Création d'une requête employant une transcation
	public function db_transactionArray($array) {
		return "SET AUTOCOMMIT = 0; START TRANSACTION;" . $this->db_requeteFromArray($array) . "COMMIT;SET AUTOCOMMIT = 1;";
	}
	// Interrogation de la bdd en utilisant une transaction
	public function db_interrogeTransactionArray($array) {
		print $this->db_transactionArray($array);
		$this->db_interroge($this->db_transactionArray($array));
	}
	// Retourne un tableau contenant les caractéristiques d'une table dont le nom est passé en argument
	//           champ   type  null  key  default  extra
	// colonne 1  ..      ..    ..    ..     ..     ..
	// colonne 2  ..      ..    ..    ..     ..     ..
	// ...
	public function db_getColumnsTable($table) {
		$query = "SHOW COLUMNS FROM `$table`";
		$result = $_SESSION['db']->db_interroge($query);
		$fields = array();
		while ($row = mysqli_fetch_assoc($result)) {
			$fields[] = $row;
		}
		mysqli_free_result($result);
		//print (nl2br(print_r ($fields, TRUE)));
		return $fields;
	}
	// Retourne un tableau exploitable pour créer un formulaire
	// à partir des caractéristiques d'une table dont le nom est passé en paramètre
	public function db_columnToForm($table) {
		$fields = $this->db_getColumnsTable($table);
		$fieldtype = array( 0 => 
			array('name'		=> 'boolean'
			,'pattern'	=> '/^tinyint\(1\)$/i'
			,'formtype'	=> 'checkbox')
			,1 =>
			array('name'		=> 'integer'
			,'pattern'	=> '/^(tiny|small|medium|big)*int(\(([^1]|[1-9][0-9][0-9]*)\))$/i'
			,'formtype'	=> 'text')
			,2 =>
			array('name'		=> 'text'
			,'pattern'	=> '/^((var)*char|(tiny|medium|long)*text)\(([0-9]+)\)$/i'
			,'formtype'	=> 'text')
			,3 =>
			array('name'		=> 'date'
			,'pattern'	=> '/date/i'
			,'formtype'	=> 'text')
			,4 =>
			array('name'		=> 'liste'
			,'pattern'	=> "/^enum(\(.+\))$/i"
			,'formtype'	=> 'select')
			,5 =>
			array('name'		=> 'multiple'
			,'pattern'	=> "/^set(\(.+\))$/i"
			,'formtype'	=> 'select')
		);
		for ($i=0; $i < count($fields); $i++) {
			// Détection du type d'élément INPUT à attribuer
			foreach ($fieldtype as $ft) {
				if (preg_match($ft['pattern'], $fields[$i]['Type'], $matches)) {
					$fields[$i]['Input'] = $ft['formtype'];
					if ($ft['formtype'] === 'text') {
						if ($ft['name'] === 'date') {
							$fields[$i]['Length'] = 10;
						} else {
							$fields[$i]['Length'] = $matches[count($matches)-1];
						}
					}
					if ($ft['formtype'] === 'select') {
						if (preg_match_all("/'([^()']+)'/Ui", $matches[1], $moui)) {
							$fields[$i]['Select'] = $moui[1];
						}
					}
					break;
				}
			}
		}
		return $fields;
	}
	// Protection de chaînes
	// Retourne une chaîne protégée qui peut être intégrée dans une requête mysql
	public function db_real_escape_string($string) {
		return mysqli_real_escape_string($this->link, $string);
	}
}

?>
