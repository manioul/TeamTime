<?php
// class_debug.inc.php
//
// Une classe permettant l'accès à diverses infos de débogage
// Pour fonctionner correctement, ce script doit être appelé en premier
// à cause de l'initialisation de $GLOBALS['initialTime']
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

$GLOBALS['initialTime'] = microtime(true);

class debug {
	private $script; // le nom du script dans lequel on entre (ceci permet d'attribuer les messages à un script en particulier)
	private $messages = array(); // un tableau contenant des chaînes de caractères
	private $iWasHere = array(); // un tableau permettant d'indiquer le passage dans un script, une fonction...
	private $format; // Le format sous lequel sont délivrés les messages d'erreur
	private $knownFormats = array('text', 'html'); // Les formats reconnus pour afficher les messages d'erreur
	private $lastError; // La dernière erreur survenue
	private $errorStack = array(); // Un tableau des dernières erreurs
	private $chronos; // Un tableau des chronos
	private static $_instance = null;

	private function __construct() {
	}
	// Ajoute un message à la pile de messages de l'objet
	// Permet à un script de sauver un message qui peut être retrouvée plus tard (valeur d'une variable...)
	public function postMessage($message) {
		$this->messages[] = $message;
	}
	// Ajoute une marque de passage dans un script, une fonction
	public function iWasHere($info=false) {
		$this->iWasHere[] = empty($info) ? 1 : $info;
	}
	public function convertMB($size) {
		$unit=array('B','KB','MB','GB','TB','PB');
		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}
	// Accesseurs
	public function retrieveMessages($index=false) { // $format est le format de sortie souhaité pour le texte
		if (count($this->messages) < 1) {
			return false;
		}
		$msg = "";
		if (false !== $index) {
			$msg = $this->messages[$index];
		} else {
			foreach ($this->messages as $key => $val) {
				$msg .= $key . " => " . $val . "\n";
			}
		}
		switch ($this->format()) {
		case 'text':
			break;
			;;
		case 'html':
			$msg = nl2br(htmlspecialchars($msg));
			break;
			;;
		}
		return $msg;
	}
	// Retourne les marques de passage
	public function whereWereU() {
		if (count($this->iWasHere) < 1) {
			return false;
		} else {
			return $this->iWasHere;
		}
	}
	// Définit ou retourne le format d'affichage des messages
	public function format($format=false) {
		if (false === $format && empty($this->format)) {
			$this->format = 'text';
		} else if (in_array($format, $this->knownFormats)) {
			$this->format = $format; // FIXME $format se voit toujours attribuer la valeur par défaut
		} else {
			$this->format = 'text';
		}
		return $this->format;
	}
	public function lastError($err=false) {
		if (false !== $err) {
			if (!empty($this->lastError)) {
				$this->errorStack[] = $this->lastError;
			}
			$this->lastError = $err;
		}
		return $this->lastError;
	}
	public function errorMessage($err=false) {
		if (false === $err) {
			if (!empty($this->lastError)) {
				$err = $this->lastError;
			} else {
				$err = SUCCESS;
			}
		}
		if (!empty($GLOBALS['ERREURS'][$GLOBALS['language']][$err])) {
			$msg = $GLOBALS['ERREURS'][$GLOBALS['language']][$err];
		} else {
			$msg = $GLOBALS['ERREURS'][$GLOBALS['language']][WARN_NO_ERR_MSG] . $err;
		}
		switch ($this->format()) { // FIXME même forcée à 'html', la valeur de format n'est pas correctement traitée
		case 'text':
			break;
			;;
		case 'html':
			$msg = nl2br(htmlspecialchars($msg));
			break;
			;;
		}
		return $msg;
	}
	public function lastErrorMessage() {
		return $this->errorMessage();
	}
	public function clean($index=false) {
		if (false === $index) {
			$this->lastError(ERR_MISSING_PARAM);
		}
	}
	public function startChrono($nom=false) {
		if (false === $nom) {
			$nom = 0;
		}
		$nom = htmlentities($nom, ENT_NOQUOTES, 'utf-8');
		$this->chronos[$nom] = microtime(true);
	}
	public function stopChrono($nom=false) {
		$now = microtime(true);
		if (false === $nom) {
			$nom = 0;
		}
		$nom = htmlentities($nom, ENT_NOQUOTES, 'utf-8');
		if (empty($this->chronos[$nom])) {
			$this->lastError(ERR_BAD_PARAM);
			return false;
		}
		$this->chronos[$nom] = array(
			'instant'	=> $now - $this->chronos[$nom]
			,'cumule'	=> $now - $GLOBALS['initialTime']
			,'chrono'	=> $now - microtime(true)
		);
	}
	public function getChronos() {
		return $this->chronos;
	}
	public function peakMemory($realUsage = true) {
		return $this->convertMB(memory_get_usage($realUsage));
	}
	public function triggerError($message, $level=E_USER_NOTICE) {
		$array = debug_backtrace();
		$caller = next($array);
		trigger_error($message.' in <strong>'.$caller['function'].'</strong> called from <strong>'.$caller['file'].'</strong> on line <strong>'.$caller['line'].'</strong><br />Error handler ', $level);
		return true;
	}
	public function triggerErrorN($err, $level=E_USER_NOTICE) {
		$array = debug_backtrace();
		$caller = next($array);
		trigger_error(errorMessage($err).' in <strong>'.$caller['function'].'</strong> called from <strong>'.$caller['file'].'</strong> on line <strong>'.$caller['line'].'</strong><br />Error handler ', $level);
		return true;
	}
	public static function getInstance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new debug();
		}
		return self::$_instance;
	}
}
