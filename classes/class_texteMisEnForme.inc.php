<?php
// class_texteMisEnForme.inc.php

class texteMisEnForme {
	private $texteRaw; // texte brut
	private $texte; // texte pouvant être affiché sur une page web
	private $analyse;

// Constructeur/destructeur
	function __construct($param = NULL) {
		if ($param != NULL) {
			if (is_array($param)) {
				/*
				 * !!!!!!!! TRÈS IMPORTANT !!!!!!!
				 * La valeur d'analyse doit être rentrée avant le texte pour que celui-ci soit éventuellement analysé
				 */
				$this->analyse($param[1]);
				$this->texteRaw($param[0]);
			} else {
				$this->texteRaw($param);
			}
		}
	}
	function __destruct() {
	}

// Accesseurs
	function texteRaw($param = NULL) {
		if ($param != NULL) {
			$this->texteRaw = $param;
			$this->_texte();
		}
		return $this->texteRaw;
	}
	private function _texte() {
		$this->texte = htmlspecialchars($this->texteRaw, ENT_NOQUOTES, $_SESSION['db']->encoding());
		if (!$this->analyse) {
			$this->_analyseTexte();
		}
	}
	function texte() {
		return $this->texte;
	}
	private function _analyse($param = NULL) {
		if ($param != NULL) {
			$this->analyse = $param;
		}
		return $this->analyse;
	}
	function analyse() {
		return $this->analyse;
	}
	function setAnalyse() {
		$this->analyse = 1;
	}
	function unsetAnalyse() {
		$this->analyse = 0;
	}

// Analyse et mise en forme du texte
	private function _analyseTexte() {
		// Remplacement des liens
		// Les liens sont de la forme:
		// {lien:http://localhost|local}
		$pattern = '/{lien:([^}]*)\|([^}]*)}/';
		$replacement = '<a href="\1">\2</a>';
		$this->texte = preg_replace($pattern, $replacement, $this->texte);

		// Les sauts de ligne {br}
		$pattern = '/{br}/';
		$replacement = '<br />';
		$this->texte = preg_replace($pattern, $replacement, $this->texte);

		// {strong}{/strong}
		$pattern = '/{([\/]*)strong}/';
		$replacement = '<\1strong>';
		$this->texte = preg_replace($pattern, $replacement, $this->texte);
	}
}

?>
