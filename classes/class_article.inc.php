<?php
// class_article.inc.php

require_once('class_texteMisEnForme.inc.php');
require_once('class_date.inc.php');


class article {
	private $idx = NULL; // Les articles d'idx NULL sont des nouveaux articles à insérer dans la bdd
	private $titre;
	private $description;
	private $texte;
	private $analyse;
	private $creation;
	private $modification;
	private $restricted;
	private $actif;
	private $rubriqueIdx; // Un tableau des indexes des rubriques associées au présent article
// Constructeurs
	public function __construct($param = NULL) { // $param est l'idx pour un article existant
		if ($param != NULL) {
			if (is_int($param)) {
				$this->idx = $param;
			} elseif (is_string($param)) {
				$this->description = $param;
			}
			$this->db_setFromDB();
		}
	}
	public function __destruct() {
	}
// Accesseurs
	public function idx($param = NULL) {
		if (! is_null($param)) { $this->idx = $param; }
		return $this->idx;
	}
	public function titre($param = NULL) {
		if (! is_null($param)) { $this->titre = $param; }
		return $this->titre;
	}
	public function description($param = NULL) {
		if (! is_null($param)) { $this->description = $param; }
		return $this->description;
	}
	public function texte($param = NULL) {
		if (! is_null($param)) {
			$texte = new texteMisEnForme(array($param, $this->analyse));
			$this->texte = $texte->texte();
	       	}
		return $this->texte;
	}
	public function creation($param = NULL) {
		if (! is_null($param)) { $this->creation = new Date($param); }
			return $this->creation;
	}
	public function modification($param = NULL) {
		if (! is_null($param)) { $this->modification = new Date($param); }
			return $this->modification;
	}
	public function restricted($param = NULL) {
		if (! is_null($param)) {
			if ($param) {
				$this->setRestricted();
			} else {
				$this->unsetRestricted();
			}
		}
		return $this->restricted;
	}
	public function actif($param = NULL) {
		if (! is_null($param)) {
			if ($param) {
				$this->setActive();
			} else {
				$this->unsetActive();
			}
		}
		return $this->actif;
	}
	public function analyse($param = NULL) {
		if (! is_null($param)) {
			if ($param) {
				$this->setAnalyse();
			} else {
				$this->unsetAnalyse();
			}
		}
		return $this->analyse;
	}
	public function setRestricted() {
		$this->restricted = true;
	}
	public function unsetRestricted() {
		$this->restricted = false;
	}
	public function setActive() {
		$this->actif = true;
	}
	public function unsetActive() {
		$this->actif = false;
	}
	public function setAnalyse() {
		$this->analyse = true;
	}
	public function unsetAnalyse() {
		$this->analyse = false;
	}
// Méthodes travaillant avec la bdd
	public function db_setFromDB() {
		$where = 'WHERE ';
		if (!is_null($this->idx) && is_int($this->idx)) {
			$where .= "`idx` = " . $this->idx;
		} elseif (!is_null($this->description)) {
			$where .= "`description` = '" . $_SESSION['db']->db_real_escape_string($this->description) . "'";
		}
		$sql = "SELECT *
			FROM `TBL_ARTICLES`
			$where
			LIMIT 1";
		$result = $_SESSION['db']->db_interroge($sql);
		if ($result->num_rows != 1) return false;
		$row = $_SESSION['db']->db_fetch_assoc($result);
		mysqli_free_result($result);
		$this->idx = (int) $row['idx'];
		$this->analyse($row['analyse']);
		$this->titre($row['titre']);
		$this->description($row['description']);
		$this->texte($row['texte']);
		$this->creation($row['creation']);
		$this->modification($row['modification']);
		$this->restricted($row['restricted']);
		$this->actif($row['actif']);
		return true;
	}
	private function _db_insert() {
		$titre = $_SESSION['db']->db_real_escape_string($this->titre);
		$description = $_SESSION['db']->db_real_escape_string($this->description);
		$texte = $_SESSION['db']->db_real_escape_string($this->texteRaw);
		if ($this->analyse) {
			$analyse = 'FALSE';
		} else {
			$analyse = 'TRUE';
		}
		if ($this->restricted) {
			$restricted = 'FALSE';
		} else {
			$restricted = 'TRUE';
		}
		if ($this->actif) {
			$actif = 'FALSE';
		} else {
			$actif = 'TRUE';
		}
		$requete = sprintf ("INSERT INTO `TBL_ARTICLES` (`titre`, `description`, `texte`, `analyse`, `creation`, `restricted`, `actif`) VALUES ('%s', '%s', '%s', '%s', NOW(), '%s', '%s')", $titre, $description, $texte, $analyse, $restricted, $actif);
		$_SESSION['db']->db_interroge($requete);
	}
	private function _db_updateLinkRubriques() {
		$requete = sprintf("SELECT * FROM `TBL_ARTICLES_RUBRIQUES` WHERE idxa = %d", $this->idx);
		$result = $_SESSION['db']->db_interroge($requete);
		foreach ($_SESSION['db']->db_fetch_array($result) as $value) {
			if (! in_array($value[1], $this->rubriqueIdx)) {
				$_SESSION['db']->db_interroge(sprintf("DELETE FROM `TBL_ARTICLES_RUBRIQUES` WHERE `idxa` = %d AND `idxu` = %d", $this->idx, $value[1]));
			}
		}
		mysqli_free_result($result);
		foreach ($this->rubriqueIdx as $idxu) {
			$_SESSION['db']->db_interroge(sprintf ("REPLACE INTO `TBL_ARTICLES_RUBRIQUES` (`idxa`, `idxu`) VALUES (%d, %d)", $this->idx, $idxu));
		}
	}
	private function _db_updateArticle() {
		$titre = $_SESSION['db']->db_real_escape_string($this->titre);
		$description = $_SESSION['db']->db_real_escape_string($this->description);
		$texte = $_SESSION['db']->db_real_escape_string($this->texteRaw);
		if ($this->analyse) {
			$analyse = 'FALSE';
		} else {
			$analyse = 'TRUE';
		}
		if ($this->restricted) {
			$restricted = 'FALSE';
		} else {
			$restricted = 'TRUE';
		}
		if ($this->actif) {
			$actif = 'FALSE';
		} else {
			$actif = 'TRUE';
		}
		$requete = sprintf ("UPDATE `TBL_ARTICLES` SET `titre` = '%s', `description` = '%s', `texte` = '%s', `analyse` = '%s', `restricted` = '%s', `actif` = '%s' WHERE `idx` = %d", $titre, $description, $texte, $analyse, $restricted, $actif, $this->idx);
		$_SESSION['db']->db_interroge($requete);
	}
	public function db_createArticle() {
		$this->_db_insert();
		$this->_db_updateLinkRubriques();
	}
}

?>
