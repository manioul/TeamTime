<?php
// class_date.inc.php
/*
   class de gestion de dates
 */

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

set_include_path(implode(PATH_SEPARATOR, array(realpath('.'), realpath('..'), get_include_path())));

require_once('config.inc.php');

/*
 * Constantes d'erreur
 */
define('DATE_ERR_INVALID_FORMAT', false);
define('DATE_ERR_UNKNOWN_FORMAT', false);
define('DATE_ERR_INVALID_DATE', false);

class Date {
	private $DEBUG = false;
	//private static $minYear = 1970; // L'année la plus petite acceptée par la classe
	public static $jourSemaineCourt = array (0	=> "di"
		,1	=> "lu"
		,2	=> "ma"
		,3	=> "me"
		,4	=> "je"
		,5	=> "ve"
		,6	=> "sa"
		,7	=> "di"
	);
	public static function genCalendrier($annee) {
		$jour_mois = array (
			'1' => array ('nom' => "janvier", 'nbJours' => "31", 'asHTML' => "Janvier"),
			'2' => array ('nom' => "fevrier", 'nbJours' => "28", 'asHTML' => "F&eacute;vrier"),
			'3' => array( 'nom' => "mars", 'nbJours' => "31", 'asHTML' => "Mars"),
			'4' => array ('nom' => "avril", 'nbJours' => "30", 'asHTML' => "Avril"),
			'5' => array('nom' => "mai", 'nbJours' => "31", 'asHTML' => "Mai"),
			'6' => array('nom' => "juin", 'nbJours' => "30", 'asHTML' => "Juin"),
			'7' => array ('nom' => "juillet", 'nbJours' => "31", 'asHTML' => "Juillet"),
			'8' => array ('nom' => "aout", 'nbJours' => "31", 'asHTML' => "Ao&ucirc;t"),
			'9' => array ('nom' => "septembre", 'nbJours' => "30", 'asHTML' => "Septembre"),
			'10' => array ('nom' => "octobre", 'nbJours' => "31", 'asHTML' => "Octobre"),
			'11' => array ('nom' => "novembre", 'nbJours' => "30", 'asHTML' => "Novembre"),
			'12' => array ('nom' => "decembre", 'nbJours' => "31", 'asHTML' => "D&eacute;cembre")
		);

		if (!( $annee % 4 ) && ( ($annee % 400) )) {
			$jour_mois[2]['nbJours'] = 29;
		}
		return $jour_mois;
	}
	// Méthodes relatives à la recherche de jour férié
	public static function dimanche_paques($annee)
	{
		return date("Y-m-d", strtotime("$annee-03-21 +" . easter_days($annee) . " days"));
	}
	public static function vendredi_saint($annee)
	{
		$dimanche_paques = self::dimanche_paques($annee);
		return date("Y-m-d", strtotime("$dimanche_paques -2 day"));
	}
	public static function lundi_paques($annee)
	{
		$dimanche_paques = self::dimanche_paques($annee);
		return date("Y-m-d", strtotime("$dimanche_paques +1 day"));
	}
	public static function jeudi_ascension($annee)
	{
		$dimanche_paques = self::dimanche_paques($annee);
		return date("Y-m-d", strtotime("$dimanche_paques +39 day"));
	}
	public static function lundi_pentecote($annee)
	{
		$dimanche_paques = self::dimanche_paques($annee);
		return date("Y-m-d", strtotime("$dimanche_paques +50 day"));
	}
	public static function jours_feries($annee, $alsacemoselle=false)
	{
		$jours_feries = array
			(    self::dimanche_paques($annee)
			,    self::lundi_paques($annee)
			,    self::jeudi_ascension($annee)
			,    self::lundi_pentecote($annee)
			,    "$annee-01-01"        //    Nouvel an
			,    "$annee-05-01"        //    Fête du travail
			,    "$annee-05-08"        //    Armistice 1945
			,    "$annee-07-14"        //    Fête nationale
			,    "$annee-08-15"        //    Assomption
			,    "$annee-11-11"        //    Armistice 1918
			,    "$annee-11-01"        //    Toussaint
			,    "$annee-12-25"        //    Noël
		);
		if($alsacemoselle)
		{
			$jours_feries[] = "$annee-12-26";
			$jours_feries[] = self::vendredi_saint($annee);
		}
		sort($jours_feries);
		return $jours_feries;
	}
	public static function est_ferie($jour, $alsacemoselle=false)
	{
		$jour = date("Y-m-d", strtotime($jour));
		$annee = substr($jour, 0, 4);
		return in_array($jour, self::jours_feries($annee, $alsacemoselle));
	}

	private $date	= false; // 'Y-m-d'
	private $jour	= false; // le jour du mois (1..31)
	private $jourSemaine	= false; // le jour de la semaine généralement (0..6), mais supporterait aussi (1..7)
	private $annee	= false; // l'année sur 4 chiffres
	private $mois	= false; // le mois sur 2 chiffres
	private $timestamp	= false; // le timestamp de la date
	private $weekend; // true si le jour est un jour weekend (samedi ou dimanche)
	private $ferie; // true si le jour est férié, false sinon
// Constructeur
	/* l'argument $row à _construct est une chaîne de caractère
	 * au format "YYYY-m-d" ou un tableau décrivant la date
	 */
	function __construct($row=NULL) {
		if (isset($TRACE) && true === $TRACE) {
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("%s", "DEBUG", "%s;%s:%s", "%s", "%s")'
				, $msg
				, __FUNCTION__
				, __CLASS__
				, __METHOD__
				, $short
				, $context)
			);
		} 
		if (!is_null($row)) {
			if ($this->date($row) === false) {
				if ($this->DEBUG) {
					ob_start();
					print("__construct:\nErreur: Format de données inconnu pour Date: \n");
					$string = ob_get_contents();
					var_dump($row);
					$string .= ob_get_contents();
					print("\n");
					$string .= ob_get_clean();
					debug::getInstance()->postMessage($string);
					debug::getInstance()->lastError(ERR_BAD_PARAM);
				}
				return false;
			} else {
				return $this->date();
			}
		} else {
			//print("Aucune données passée lors de la création de la date...<br />");
			return false;
		}
	}
	function __destruct() {
		if ($this->DEBUG) {
			ob_start();
			print ("__destruct:\nDestruction de l'objet:\n");
			$string = ob_get_contents();
			var_dump($this->date);
			$string .= ob_get_contents();
			print("\n");
			$string .= ob_get_clean();
			debug::getInstance()->postMessage($string);
		}
	}
// Méthodes d'attribution et d'accès à l'objet
	// Attribue éventuellement la date et retourne une date au format YYYY-MM-dd
	public function date($date=NULL) {
		if (!is_null($date)) {
			if (is_string($date)) {
				if (isset($TRACE) && true === $TRACE) {
					$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Calling method _initiateDateFromString", "DEBUG", "%s:%s", "", "date:%s")'
						, __CLASS__
						, __METHOD__
						, $date)
					);
				}
				return $this->_initiateDateFromString($date);
			} else if (is_array($date)) {
				if (isset($TRACE) && true === $TRACE) {
					$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Calling method _initiateDateFromArray", "DEBUG", "%s:%s", "", "date:%s")'
						, __CLASS__
						, __METHOD__
						, $date)
					);
				}
				return $this->_initiateDateFromArray($date);
			} else {
				if (isset($TRACE) && true === $TRACE) {
					$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Unable to construct Date object", "DEBUG", "%s:%s", "", "date:%s")'
						, __CLASS__
						, __METHOD__
						, $date)
					);
				}
				if ($this->DEBUG) {
					ob_start();
					print("date:\nErreur: Format de données inconnu pour Date: \n");
					$string = ob_get_contents();
					var_dump($date);
					$string .= ob_get_contents();
					print("\n");
					$string .= ob_get_clean();
					debug::getInstance()->postMessage($string);
					debug::getInstance()->lastError(DATE_ERR_UNKNOWN_FORMAT);
				}
				return false;
			}
		}
		if (!$this->date) $this->setDate();
		return $this->date;
	}
	private function _initiateDateFromString($date) {
		if (isset($TRACE) && true === $TRACE) {
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("", "DEBUG", "%s:%s", "", "date:%s")'
				, __CLASS__
				, __METHOD__
				, $date)
			);
		} 
		// La bdd attribue '0000-00-00' aux dates vides
		// On attribue, quand même, la valeur à date, mais l'on retourne false
		// et on ne remplit aucun attribut supplémentaire
		if ('0000-00-00' == $date) {
			$this->date = "0000-00-00";
			return false;
		}
		// vérification du format de la date et remplissage des champs
		if (preg_match('/^([12][0-9]{3,3})[-\/]' . // YYYY[-/]   Les années sont comprises entre 1000 et 2999
			'(0?[1-9]|1[0-2])[-\/]' . 	       // m[-/]
			'(0?[1-9]|[12][0-9]|3[01])$/i', // d
			$date, $det_date)) {
				$this->date = sprintf("%04d-%02d-%02d", $det_date[1], $det_date[2], $det_date[3]);
				$this->annee = (int)$det_date[1];
				$this->mois = (int)$det_date[2];
				$this->jour = (int)$det_date[3];
				// On vérifie que la date est valide
				if (!checkdate($this->mois, $this->jour, $this->annee)) {
					if ($this->DEBUG) {
						debug::getInstance()->postMessage("checkdate invalide");
						debug::getInstance()->lastError(DATE_ERR_INVALID_DATE);
					}
					$this->__destruct();
					return false;
				}
				$this->timestamp((int)mktime (0, 0, 0, $this->mois, $this->jour, $this->annee));
				// est-ce un jour du weekend?
				if ($this->jourSemaine() === false) {
					if ($this->DEBUG) {
						debug::getInstance()->postMessage("Err jourSemaine");
					}
					return false;
				}
				return $this->date;
			} elseif (preg_match('/^(0?[1-9]|[12][0-9]|3[01])[-\/]' . // d[-/]
			'(0?[1-9]|1[0-2])[-\/]' . 	       // m[-/]
			'([12][0-9]{3,3})$/i', // YYYY   Les années sont comprises entre 1000 et 2999
			$date, $det_date)) {
				//printf("<pre>det_date: %s</pre>\\<br />", var_dump($det_date));
				$this->date = sprintf("%04d-%02d-%02d", $det_date[3], $det_date[2], $det_date[1]);
				$this->annee = (int)$det_date[3];
				$this->mois = (int)$det_date[2];
				$this->jour = (int)$det_date[1];
				// On vérifie que la date est valide
				if (!checkdate($this->mois, $this->jour, $this->annee)) {
					if ($this->DEBUG) {
						debug::getInstance()->postMessage("checkdate invalide");
						debug::getInstance()->lastError(DATE_ERR_INVALID_DATE);
					}
					$this->__destruct();
					return false;
				}
				$this->timestamp((int)mktime (0, 0, 0, $this->mois, $this->jour, $this->annee));
				// est-ce un jour du weekend?
				if ($this->jourSemaine() === false) {
					if ($this->DEBUG) {
						debug::getInstance()->postMessage("Err jourSemaine");
					}
					return false;
				}
				return $this->date;
			} else {
				if ($this->DEBUG) {
					debug::getInstance()->postMessage("Chaine Date invalide");
					debug::getInstance()->lastError(DATE_ERR_INVALID_FORMAT);
				}
				$this->__destruct();
				unset($this);
				return false;
			}
	}
	// Le tableau est de la forme (annee, mois, jour)
	private function _initiateDateFromArray($date) { // TODO
		if ($this->annee($date[0]) && $this->mois($date[1]) && $this->jour($date[2])) {
			return true;
		} else {
			return false;
		}
	}
	/*
		Construit la chaîne date à partir de annee, mois, jour
		et renvoie la chaîne ou false si la date n'est pas valable
	 */
	private function setDate() {
		if ($this->annee && $this->mois && $this->jour) {
			$this->date = sprintf("%04d-%02d-%02d", $this->annee, $this->mois, $this->jour);
			// Met à jour le jour de la semaine et définit si l'on est en weekend
			if (!$this->jourSemaine()) {
				debug::getInstance()->lastError(DATE_ERR_INVALID_DATE);
				return false;
			}
			return $this->date;
		} else {
			debug::getInstance()->lastError(DATE_ERR_INVALID_DATE);
			return false;
		}
	}
	// Retourne une date formattée (fr...)
	public function formatDate($format = NULL) {
		if (is_null($format) && isset($GLOBALS['language'])) {
			$format = $GLOBALS['language'];
		} else {
			$format = 'fr';
		}
		switch ($format) {
		case 'fr':
			if ($this->annee && $this->mois && $this->jour) return sprintf("%02d-%02d-%04d", $this->jour, $this->mois, $this->annee);
			// On ne break pas pour continuer sur la valeur défaut
			// afin de prendre en compte les dates 0000-00-00
		default:
			return $this->date;
		}
	}
	public function annee($annee=false) {
		if ($annee > 0) { // On n'accepte que les années positives
			// Si l'année est modifiée, il faudra mettre à jour la propriété date
			if ($this->annee != $annee) {
				$this->annee = (int)$annee;
				$this->setDate(); // La chaîne date est reconstruite
				$this->calendrier(true);
			}
		}
		if (!$this->annee && $this->date) {
			if (!$this->_initiateDateFromString($this->date)) {
				return false;
			}
		}
		return (int) $this->annee;
	}
	public function mois($mois=false) {
		// Si une valeur est passée pour mois
		if ($mois) {
			// mais la valeur n'est pas correcte, l'objet est détruit
			if ($mois > 12 || $mois < 1) {
				if ($this->DEBUG) {
					debug::getInstance()->postMessage(sprintf("Erreur pour le mois: %s.\n", $mois));
					debug::getInstance()->lastError(DATE_ERR_INVALID_DATE);
				}
				$this->__destruct();
				return false;
			}
			// si la valeur est différente de la précédente valeur attribuée à la propriété mois
			// cette dernière est mise à jour
			if ($mois != $this->mois) {
				$this->mois = (int)$mois;
				$this->setDate(); // La chaîne date est ensuite reconstruite
			}
			// Si la propriété mois n'existe pas, mais que la propriété date existe, la propriété mois
			// sera mise à jour à partir de la propriété date
		} elseif (!$this->mois && $this->date) {
			if (!$this->_initiateDateFromString($this->date)) {
				return false;
			}
		}
		return (int) $this->mois;
	}
	public function jour($jour=false) {
		if ($jour) {
			if ($jour > 31 || $jour < 1) {
				if ($this->DEBUG) {
					debug::getInstance()->postMessage(sprintf("Erreur pour le jour: %s.\n", $jour));
					debug::getInstance()->lastError(DATE_ERR_INVALID_DATE);
				}
				$this->__destruct();
				return false;
			}
			if ($this->mois()) {
				if ($jour > $this->nbJoursMois()) {
					if ($this->DEBUG) {
						debug::getInstance()->postMessage(sprintf("Erreur pour le jour: %s.\n", $jour));
						debug::getInstance()->lastError(DATE_ERR_INVALID_DATE);
					}
					$this->__destruct();
					return false;
				}
			}
			$this->jour = (int)$jour;
			$this->setDate(); // La chaîne date est reconstruite
		} elseif (!$this->jour && $this->date) {
			if (!$this->_initiateDateFromString($this->date)) {
				return false;
			}
		}
		return (int) $this->jour;
	}
	public function ferie() {
		if (!isset($this->ferie)) {
			$this->ferie = $this->est_ferie($this->date);
		}	
		return $this->ferie;
	}
	public function set_ferie() {
		$this->ferie = true;
	}
	public function unset_ferie() {
		$this->ferie = false;
	}
	// Retourne le jour de la semaine au format numérique (0..6) 0=di
	// Le dimanche pourrait aussi bien être le jour 0 que le jour 7,
	// cela permet de commencer la semaine le dimanche (0) ou le lundi (1)
	// Cependant, tm_wday est prévu de 0 à 6
	public function jourSemaine() {
		if (($decomp = strptime($this->date(), '%Y-%m-%d')) != false) {
			$this->jourSemaine = $decomp['tm_wday'];
			if ($this->jourSemaine == 0 || $this->jourSemaine >= 6) {
				$this->weekend = true;
			} else {
				$this->weekend = false;
			}
			$this->ferie();
			return $this->jourSemaine;
		} else {
			/*
			print("<pre>jourSemaine is false for:\n");
			var_dump($this);
			print("</pre><br />");
			 */
			return false;
		}
	}
	// true si on est un weekend, false sinon
	public function isWeekend() {
		if (isset($this->weekend)) {
			$this->jourSemaine();
		}
		return $this->weekend;
	}
	public function timestamp($timestamp=false) {
		if ($timestamp > 0) {
			$this->timestamp = (int) $timestamp;
			$localtime = localtime($this->timestamp);
			$this->date = date("Y-m-d", $this->timestamp);
			$this->annee = (int)date("Y", $this->timestamp);
			$this->mois = (int)date("m", $this->timestamp);
			$this->jour = (int)date("d", $this->timestamp);
		}
		return (int) $this->timestamp;
	}
	public function calendrier($recalc = false) { // le paramètre à true force le recalcul du calendrier
		return self::genCalendrier($this->annee);
	}
	public function nbJoursMois() {
		$cal = $this->calendrier();
		/*print("<pre>");
		print_r($cal[$this->mois()]);
		print("</pre>");
		printf("nbJours pour le mois %s: %s<br />", $this->mois(), $cal[$this->mois()]['nbJours']);*/
		return (int)$cal[$this->mois()]['nbJours'];
	}
	// Méthodes
	// Retourne le jour de la semaine au format court (2 caractères)
	public function jourSemaineCourt() {
		return self::$jourSemaineCourt[$this->jourSemaine()];
	}
	// Retourne le mois en version html
	public function moisAsHTML() {
		$cal = $this->calendrier();
		return $cal[$this->mois()]['asHTML'];
	}
	// Retourne la date dans un format utilisable comme id d'une balise html
	public function dateAsId() {
		return sprintf("a%sm%sj%s", $this->annee(), $this->mois(), $this->jour());
	}
	// retourne le nombre de jour qui sépare la date du 1er janvier précédent
	public function debutAnnee() {
		$nombreDeJours = $this->jour;
		$month = $this->mois() - 1;
		$cal = $this->calendrier();
		while ($month >= 1) {
			$nombreDeJours += $cal[$month--]['nbJours'];
		}
		return $nombreDeJours;
	}
	// Retourne le nombre de jour qui sépare la date du 31 décembre suivant
	public function finAnnee() {
		$nombreDeJours = $this->nbJoursMois() - $this->jour();
		$month = $this->mois() + 1;
		$cal = $this->calendrier();
		while ($month <= 12) {
			$nombreDeJours += $cal[$month++]['nbJours'];
		}
		return $nombreDeJours;
	}
	public function addJours($nbJours) { // Retourne la date augmentée du nombre de jours $nbJours
		if (!is_int($nbJours)) { $nbJours = (int) $nbJours; }
			//printf("addJours %s + %s<br />", $this->date(), $nbJours);
			if ($nbJours == 0) { return new Date($this->date); } // Si l'incrément est nul, l'objet date est cloné
			if ($nbJours < 0) { return $this->subJours(-$nbJours); }
			if ($this->nbJoursMois() < $this->jour + $nbJours) { // On doit changer de mois
				//printf("chgt mois: %s < %s + %s<br />", $this->nbJoursMois(), $this->jour, $nbJours);
				$tempoNbJoursMois = $this->nbJoursMois();
				if ($this->mois == 12) { // On doit changer d'année et revenir au mois de janvier
					$this->mois(1);
					$this->annee($this->annee()+1);
				} else { $this->mois++; }
					$newNbJours = $nbJours - ($tempoNbJoursMois - $this->jour) - 1;
				$this->jour = 1;
				if ($newNbJours > 0) { return $this->addJours($newNbJours); }
			} else {
				$this->jour += $nbJours;
			}
		$this->date = false; // on force la date à être redéfinie
		$this->date();
		return $this;
	}
	public function incDate() {
		return $this->addJours(1);
	}
	public function subJours($nbJours) { // Retourne l'objet Date résultat de la date diminuée du nombre de jours $nbJours
		if (!is_int($nbJours)) { $nbJours = (int) $nbJours; }
			if ($nbJours == 0) { return $this; } // Si l'incrément est nul, l'objet date est retourné
				if ($nbJours < 0) { return $this->addJours(-$nbJours); }
					if ($this->jour - $nbJours <= 0) { // On doit changer de mois
						if ($this->mois == 1) { // On doit changer d'année
							$this->annee($this->annee()-1);
							$this->mois = 12;
						} else { $this->mois--; }
							$newNbJours = $nbJours - $this->jour;
						$this->jour = $this->nbJoursMois();
						if ($newNbJours > 0) { return $this->subJours($newNbJours); }
					} else {
						$this->jour -= $nbJours;
					}
		$this->date = false; // on force la date à être redéfinie
		$this->date();
		return $this;
	}
	public function decDate() {
		return $this->subJours(1);
	}
	// Compare la date de l'objet courant avec la date passé en paramètre
	// (le paramètre est soit un objet date soit une chaîne)
	// Retourne un nombre positif si l'objet est postérieur au paramètre
	//         un nombre négatif si l'objet est antérieur au paramètre
	//          0 si les dates sont identiques
	public function compareDate($date) {
		if (is_string($date)) {
			$date = new Date($date);
		}
		if (!is_object($date)) {
			firePhpWarn('Erreur de date...');
			return false;
		}
		if ($this->annee() == $date->annee()) {
			if ($this->mois() == $date->mois()) {
				return $this->jour() - $date->jour();
			} else {
				return $this->mois() - $date->mois();
			}
		} else {
			return $this->annee() - $date->annee();
		}
	}
	// Soustrait la date contenue dans l'objet à la date (de l'objet ou de la chaîne) passé en argument
	// $date2 peut être une chaîne au format "Y-m-d"
	public function soustrait($date) {
		if (is_string($date)) {
			$date = new Date($date);
		}
		if (!is_object($date)) { return false; }
			$cmp = $this->compareDate($date);
		if ($cmp == 0) { return 0; }
			if ($cmp < 0) { return -$date->soustrait($this); }
				if ($this->annee() > $date->annee()) {
					$res = $date->finAnnee() + 1;
					$date->annee($date->annee()+1);
					$date->mois(1);
					$date->jour(1);
					return $res + $this->soustrait($date);
				} else {
					return $this->debutAnnee() - $date->debutAnnee();
				}
	}
}
?>
