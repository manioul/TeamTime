<?php
// class_utilisateurGrille.inc.php
//
// étend la classe utilisateur aux utilisateurs de la grille
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

require_once('class_utilisateur.inc.php');


class utilisateurGrille extends utilisateur {
	private $uid;
	private $nom;
	private $gid;
	private $prenom;
	private $classe = array(); // array('c', 'pc', 'ce', 'cds', 'dtch')
	private $dateArrivee;
	private $dateTheorique;
	private $datePC;
	private $dateCE;
	private $dateCDS;
	private $dateVisMed; // Date de la prochaine visite médicale
	private $poids; // La position d'affichage dans la grille (du plus faible au plus gros)
	private $showtipoftheday; // L'utilisateur veut-il voir les tips of the day
	private $dispos; /* un tableau contenant un tableau des dispos indexées par les dates:
			* $dispos[date] = array('dispo1', 'dispo2',... 'dispoN'); */
// Constructeur
	public function __construct ($row = NULL) {
		if (NULL !== $row) {
			parent::__construct($row);
			$valid = true;
			foreach ($row as $cle => $valeur) {
				if (method_exists($this, $cle)) {
					$this->$cle($valeur);
				} else {
					switch($cle) { // les espaces sont mal supportés dans les noms de champ ! :/
					case 'date arrivee':
						$this->dateArrivee($valeur);
						break;
					case 'date theorique':
						$this->dateTheorique($valeur);
						break;
					case 'date pc':
						$this->datePC($valeur);
						break;
					case 'date ce':
						$this->dateCE($valeur);
						break;
					case 'date cds':
						$this->dateCDS($valeur);
						break;
					case 'date vismed':
						$this->dateVisMed($valeur);
						break;
					default:
						debug::getInstance()->triggerError('Valeur inconnue' . $cle . " => " . $valeur);
						debug::getInstance()->lastError(ERR_BAD_PARAM);
						$valid = false;
					}
				}
			}
			return $valid; // Retourne true si l'affectation s'est bien passée, false sinon
		}
		return true;
	}
	public function __destruct() {
		unset($this);
		parent::__destruct();
	}
// Accesseurs
	public function uid($uid=false) {
		if (false !== $uid) {
			$this->uid = (int) $uid;
		}
		if (isset($this->uid)) {
			return $this->uid;
		} else {
			return false;
		}
	}
	public function gid($gid=false) {
		if (false !== $gid) {
			$this->gid = (int) $gid;
		}
		if (isset($this->gid)) {
			return $this->gid;
		} else {
			return false;
		}
	}
	public function nom($nom=false) {
		if (false !== $nom) {
			$this->nom = (string) $nom;
		}
		if (isset($this->nom)) {
			return $this->nom;
		} else {
			return false;
		}
	}
	public function prenom($prenom=false) {
		if (false !== $prenom) {
			$this->prenom = (string) $prenom;
		}
		if (isset($this->prenom)) {
			return $this->prenom;
		} else {
			return false;
		}
	}
	public function classe($classe = false) {
		if (false === $classe) return $this->classe;
		$arr = explode(',', $classe);
		foreach ($arr as $class) {
			$this->addClasse($class);
		}
		return $this->classe;
	}
	public function addClasse($classe = false) {
		if (false === $classe) return false;
		if (in_array($classe, $this->classe)) {
			// La classe est déjà attribuée
			return 1; // TODO atribuer une valeur de retour
		} else {
			$this->classe[] = $classe;
			return true;
		}
	}
	public function delClasse($classe = false) {
		if (false === $classe) return false;
		if (in_array($classe, $this->classe)) {
			$arr = array();
			foreach($this->classe as $class) {
				if ($class != $classe) {
					$arr[] = $class;
				}
			}
			$this->classe = $arr;
		} else {
			return false; // TODO attribuer une valeur de retour : la classe n'était pas attribuée
		}
	}
	public function db_condition_like_classe($champ) { // Retourne une condition LIKE sur les classe de l'utilisateur pour le champ $champ
		$condition = sprintf("`$champ` = 'all' OR `$champ` LIKE '%%%s%%' OR ", $this->login());
		foreach ($this->classe as $classe) {
			$condition .= sprintf("`%s` LIKE '%%%s%%' OR ", $champ, $classe);
		}
		return substr($condition, 0, -4);
	}
	public function dateArrivee($dateArrivee=false) {
		if (false !== $dateArrivee) {
			$this->dateArrivee = (string) $dateArrivee;
		}
		if (isset($this->dateArrivee)) {
			return $this->dateArrivee;
		} else {
			return false;
		}
	}
	public function dateTheorique($dateTheorique=false) {
		if (false !== $dateTheorique) {
			$this->dateTheorique = (string) $dateTheorique;
		}
		if (isset($this->dateTheorique)) {
			return $this->dateTheorique;
		} else {
			return false;
		}
	}
	public function datePC($datePC=false) {
		if (false !== $datePC) {
			$this->datePC = (string) $datePC;
		}
		if (isset($this->datePC)) {
			return $this->datePC;
		} else {
			return false;
		}
	}
	public function dateCE($dateCE=false) {
		if (false !== $dateCE) {
			$this->dateCE = (string) $dateCE;
		}
		if (isset($this->dateCE)) {
			return $this->dateCE;
		} else {
			return false;
		}
	}
	public function dateCDS($dateCDS=false) {
		if (false !== $dateCDS) {
			$this->dateCDS = (string) $dateCDS;
		}
		if (isset($this->dateCDS)) {
			return $this->dateCDS;
		} else {
			return false;
		}
	}
	public function dateVisMed($dateVisMed=false) {
		if (false !== $dateVisMed) {
			$this->dateVisMed = (string) $dateVisMed;
		}
		if (isset($this->dateVisMed)) {
			return $this->dateVisMed;
		} else {
			return false;
		}
	}
	public function poids($poids=false) {
		if (false !== $poids) {
			$this->poids = (int) $poids;
		}
		if (isset($this->poids)) {
			return $this->poids;
		} else {
			return false;
		}
	}
	public function showtipoftheday($showtipoftheday=false) {
		if (false !== $showtipoftheday) {
			$this->showtipoftheday = (int) $showtipoftheday;
		}
		if (isset($this->showtipoftheday)) {
			return $this->showtipoftheday;
		} else {
			return false;
		}
	}
	public function dispos($dispos=false) {
		if (is_array($dispos)) {
			$this->dispos = $dispos;
		}
		if (isset($this->dispos)) {
			return $this->dispos;
		} else {
			return false;
		}
	}
// Manipulation des propriétés
	public function addDispo($dispo=false) {
		if (is_string($dispo)) {
			$this->dispos[] = $dispo;
		} else if (is_array($dispo)) {
			$this->dispos = array_merge($this->dispos, $dispo);
		} else {
			return false;
		}
	}
	public function delDispo($dispo=false) {
		if (is_string($dispo)) {
			$arr = $this->dispos();
			$this->dispos = array();
			foreach ($arr as $disp) {
				if ($disp !== $dispo) {
					$this->dispos[] = $disp;
				}
			}
			return true;
		} else {
			debug::getInstance()->lastError(ERR_MISSING_PARAM);
			return false;
		}
	}
}
?>
