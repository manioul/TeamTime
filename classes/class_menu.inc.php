<?php
// class_menu.inc.php
//

require_once('class_date.inc.php');

class elemMenu {
	const maxProf = 3; // Profondeur maximum de sous-menus (pour éviter une récursivité infinie)
	private $idx;
	private $titre;
	private $description;
	private $lien;
	private $sousmenu = NULL; // index du sous-menu éventuel
	private $submenu; // L'objet sous-menu éventuel
	private $creation;
	private $modification;
	private $allowed; // Chaîne contenant les groupes/utilisateurs autorisés à voir cet élément
	private $actif;
	private $position;
	private $profondeur = 1; // Profondeur actuelle du présent menu (pour éviter une récursivité infinie)
// Constructeur
	function __construct($param = NULL) { // $idx en param
		if (! is_null($param)) {
			$this->idx($param);
			if (is_null($this->db_setElem())) {
				$this->__destruct();
				return NULL;
			}
	       	}
	}
	function __destruct() {
		unset($this);
	}
// Accesseurs et attribution des valeurs
	public function idx($param = NULL) {
		if (! is_null($param)) { $this->idx = (int) $param; }
		return $this->idx;
	}
	public function titre($param = NULL) {
		if (! is_null($param)) { $this->titre = htmlspecialchars($param, ENT_NOQUOTES, $_SESSION['db']->encoding()); }
		return $this->titre;
	}
	public function description($param = NULL) {
		if (! is_null($param)) { $this->description = $param; }
		return $this->description;
	}
	public function lien($param = NULL) {
		if (! is_null($param)) { $this->lien = $param; }
			return $this->lien;
	}
	public function sousmenu($param = NULL) {
		if (! is_null($param)) {
			$this->sousmenu = (int) $param;
			$this->_build_sousmenu();
	       	}
		return $this->sousmenu;
	}
	private function _build_sousmenu() { // FIXME ATTENTION aux problèmes de récursivité dans les sous-menus...
		if (! is_object($this->submenu) && ! is_null($this->sousmenu) && $this->profondeur < self::maxProf) {
			$this->submenu = new menu($this->sousmenu);
			$this->submenu->profondeur($this->profondeur);
		}
	}
	public function submenu() {
		if (is_object($this->submenu)) {
			return $this->submenu;
		} else {
			return NULL;
		}
	}
	public function creation($param = NULL) {
		if (! is_null($param)) { $this->creation = new Date($param); }
			return $this->creation;
	}
	public function modification($param = NULL) {
		if (! is_null($param)) { $this->modification = new Date($param); }
			return $this->modification;
	}
	public function position($param = NULL) {
		if (! is_null($param)) { $this->position = $param; }
			return $this->position;
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
	public function setActive() {
		$this->actif = true;
	}
	public function unsetActive() {
		$this->actif = false;
	}
	public function allowed($param = NULL) {
		if (! is_null($param)) { $this->allowed = $param; }
		return $this->allowed;
	}
	public function profondeur($param = NULL) {
		if (! is_null($param)) { $this->profondeur = $param; }
		return $this->profondeur;
	}
// Actions sur l'objet
	private function _setFromElemMenu($param) {
		if (!is_array($param)) return NULL;
		$this->titre($param['titre']);
		$this->description($param['description']);
		$this->lien($param['lien']);
		$this->sousmenu($param['sousmenu']);
		$this->creation($param['creation']);
		$this->modification($param['modification']);
		$this->allowed($param['allowed']);
		$this->actif($param['actif']);
		return true;
	}
// méthodes de bdd
	public function db_setElem() {
		$find_in_set = "";
		foreach (array_flip(array_flip(array_merge(array('all'), $_SESSION['utilisateur']->roles()))) as $set) {
			$find_in_set .= sprintf("FIND_IN_SET('%s', `allowed`) OR ", $_SESSION['db']->db_real_escape_string($set));
		}
		$requete = sprintf("
			SELECT * FROM `TBL_ELEMS_MENUS`
			WHERE `idx` = %d
			AND `actif` = TRUE
			AND (%s)"
			, $this->idx
			, substr($find_in_set, 0, -4)
		);
		if (!empty($TRACE) && true === $TRACE) {
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("%s", "DEBUG", "db_setElem", NULL, NULL)'
				, $requete)
			);
		}
		$this->_setFromElemMenu($_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($requete)));
	}
	private function _db_insertDB() {
		$requete = sprintf("
			INSERT INTO `TBL_ELEMS_MENUS`
			(`idx`, `titre`, `description`, `lien`, `sousmenu`, `creation`, `allowed`, `actif`)
			VALUES (NULL, '%s', '%s', '%s', %d, NOW(), '%s', %d)"
			, $this->titre
			, $this->description
			, $this->lien
			, $this->sousmenu
			, $this->allowed
			, $this->actif
		);
	}
	private function _db_updateDB() {
		$requete = sprintf("
			UPDATE `TBL_ELEMS_MENUS`
			SET `titre` = '%s'
			, `description` = '%s'
			, `lien` = '%s'
			, `sousmenu` = %d
			, `modification` = NOW()
			, `allowed` = '%s'
			, `actif` = %d
			WHERE `idx` = %d"
			, $this->titre
			, $this->description
			, $this->lien
			, $this->sousmenu
			, $this->allowed
			, $this->actif
			, $this->idx
		);
	}
// Display
	public function debug_display() {
		printf ("%s (%s) [%s] @ %s => %s<br />", $this->titre, $this->description, $this->idx, $this->position, $this->lien);
		if (!is_null($this->sousmenu)) {
			$this->submenu->debug_display();
		}
		print "_______________<br />";
	}
}
class menu {
	private $idx;
	private $titre;
	private $description;
	private $parent = 0;
	private $creation; // Date de création du menu
	private $modification; // Date de modification du menu
	private $allowed;
	private $actif;
	private $vertical; // 0 si le menu est horizontal, 1 si il est vertical
	private $profondeur = 1; // Profondeur actuelle du présent menu (pour éviter une récursivité infinie)
	private $arbre = array(); // tableau contenant l'arborescence des objets éléments
       				  // de menu indexés par leur position dans le menu
	private $arbre_actif = array(); // tableau contenant l'arborescence des objets éléments
       				  // de menu _actifs_ indexés par leur position dans le menu
// Constructeur
	function __construct($param = NULL) { // $param est l'idx du menu
		if (! is_null($param)) {
			$this->idx($param);
			$this->_build_menu();
		}
	}
	function __destruct() {
		unset($this);
	}
// Accesseurs et attribution des valeurs
	function idx($param = NULL) {
		if (! is_null($param)) { $this->idx = (int) $param; }
		return $this->idx;
	}
	function titre($param = NULL) {
		if (! is_null($param)) { $this->titre = $param; }
		return $this->titre;
	}
	function titreAsId() { // Le titre du menu peut comporter des espaces, ce qui n'est pas compatible avec un id html
				// Il faut donc utiliser titreAsId pour les id html
		return str_replace(' ', '_', $this->titre());
	}
	function description($param = NULL) {
		if (! is_null($param)) { $this->description = $param; }
		return $this->description;
	}
	function parent($param = NULL) {
		if (! is_null($param)) { $this->parent = $param; }
		return $this->parent;
	}
	function actif() {
		return $this->actif;
	}
	function setActive() {
		$this->actif = 1;
	}
	function unsetActive() {
		$this->actif = 0;
	}
	public function allowed($param = NULL) {
		if (! is_null($param)) { $this->allowed = $param; }
		return $this->allowed;
	}
	function creation($param = NULL) {
		if (! is_null($param)) { $this->creation = $param; }
		return $this->creation;
	}
	function modification($param = NULL) {
		if (! is_null($param)) { $this->modification = $param; }
		return $this->modification;
	}
	function profondeur($param = NULL) {
		if (! is_null($param)) { $this->profondeur = $param; }
		return $this->profondeur;
	}
	function vertical($param = NULL) {
		if (! is_null($param)) { $this->vertical = $param; }
		return $this->vertical;
	}
	function arbre($all = NULL) { 
		// Retourne l'arbre de menu.
		// les entrées de menu inactives ne sont retournées que si $all est positionné
		if (is_null($all)) {
			return $this->arbre_actif;
		} else {
			return $this->arbre;
		}
	}
// gestion de l'objet
	private function _addElem($param) { // ajoute un élément de menu au menu. $param est un objet elemMenu
		if (! is_a($param, 'elemMenu')) return ERR_BAD_PARAM; // $param est bien un objet?
		if (isset($this->arbre[$param->position()]) && $this->arbre[$param->position()] != $param) { // Si la place de l'élément de menu est déjà occupée, on tente d'ajouter l'élément à position+1
			firePHPInfo($this->arbre, 'position doublement occupée');
			$param->position($param->position()+1);
			$this->_addElem($param);
			return NULL;
		}
		$this->arbre[$param->position()] = $param;
		if ($param->actif()) $this->arbre_actif[$param->position()] = $param;
		return 1;
	}
	private function _suppressElem($param) { // $param est l'index dans arbre de l'élément à supprimer
		if (isset($this->arbre[$param])) {
			$this->arbre[$param] = array();
		} else {
			return NONEXISTENT;
		}
		return SUCCESS;
	}
	private function _reOrderArbre() { // Rééchelonne les entrées du menu par pas de $compteur
		$compteur = 2; // L'incrément entre les éléments de menu
		$array = array();
		foreach ($this->arbre as $cle => $feuille) {
			if ($cle != $compteur) { // Si la bdd doit être mise à jour avec les positions des éléments
				$requete = sprintf("
					UPDATE `TBL_MENUS_ELEMS_MENUS`
					SET `position` = %d
					WHERE `idxm` = %s
					AND `idxem` = %d"
					, $feuille->position()
					, $this->idx
					, $feuille->idx()
				);
				$_SESSION['db']->db_interroge($requete);
			}
			$array[$compteur] = $feuille;
			$feuille->position($compteur);
			$compteur += 2;
		}
		$this->arbre = $array;
	}
	function remonteElem($param) { // $param est l'index dans arbre de l'élément à déplacer
	}
	function descendElem($param) { // $param est l'index dans arbre de l'élément à déplacer
	}
	function s_addElem($param) { // $param est un objet elemMenu
		if (! is_a($param, 'elemMenu')) { return ERR_BAD_PARAM; } // $param est bien un objet?
		if (is_null($this->_addElem($param))) {
			$this->_reOrderArbre(); // Redéfinit la position des éléments de menus lorsque deux entrées se chevauchent
		}
	}
	private function _build_menu() {
		if (is_null($this->_db_setFromDB())) {
			$this->__destruct();
			return NULL;
		}
		$this->_db_getElems();
	}
// gestion bdd
	private function _db_setFromRow($param) {
		if (is_null($param)) return NULL;
		$this->titre($param['titre']);
		$this->description($param['description']);
		$this->parent($param['parent']);
		$this->creation($param['creation']);
		$this->modification($param['modification']);
		$this->allowed($param['allowed']);
		if ($param['actif']) {
			$this->setActive();
		} else {
			$this->unsetActive();
		}
		if (isset($param['vertical'])) $this->vertical($param['vertical']);
		return TRUE;
	}
	private function _db_setFromDB() {
		$find_in_set = "";
		foreach (array_flip(array_flip(array_merge(array('all'), $_SESSION['utilisateur']->roles()))) as $set) {
			$find_in_set .= sprintf("FIND_IN_SET('%s', `allowed`) OR ", $_SESSION['db']->db_real_escape_string($set));
		}
		$requete = sprintf("
			SELECT * FROM `TBL_MENUS`
			WHERE `idx` = %d
			AND `actif` = TRUE
			AND (%s)"
			, $this->idx
			, substr($find_in_set, 0, -4)
		);
		if (isset($TRACE) && true === $TRACE) {
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("%s", "DEBUG", "_db_setFromRow", NULL, NULL)'
				, $requete)
			); 
		}
		return ($this->_db_setFromRow($_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($requete))));
	}
	private function _db_getElems() {
		$requete = sprintf("
			SELECT `idxem`
			, `position`
			FROM `TBL_MENUS_ELEMS_MENUS`
			WHERE `idxm` = %d
			ORDER BY `position` ASC"
			, $this->idx
		);
		$result = $_SESSION['db']->db_interroge($requete);
		while ($row = $_SESSION['db']->db_fetch_array($result)) {
			$elemMenu = new elemMenu($row[0]);
			if (!is_a($elemMenu, 'elemMenu')) continue;
			$elemMenu->position($row[1]);
			$elemMenu->profondeur($this->profondeur + 1);
			if (ERR_BAD_PARAM === $this->s_addElem($elemMenu)) $elemMenu->__destruct();
		}
		mysqli_free_result($result);
	}
	private function _db_createMenu() {
		$requete = sprintf("
			INSERT INTO `TBL_MENUS`
			(`idx`, `titre`, `description`, `parent`, `creation`, `modification`, `restricted`, `actif`, `vertical`)
			VALUES (NULL, '%s', '%s', %d, NOW(), '0000-00-00 00:00:00', %d, %d, %d)"
			, $this->titre
			, $this->description
			, $this->parent
			, $this->restricted
			, $this->actif
			, $this->vertical
		);
		$_SESSION['db']->db_interroge($requete);
	}
// Display
	function debug_display() {
		printf ("<h1>MENU %d</h1><br /><h3>%s (%s)</h3><br /><br />", $this->idx, $this->titre, $this->description);
		foreach($this->arbre as $key => $feuille) {
			print"$key<br>";
			$feuille->debug_display();
		}
	}
}

?>
