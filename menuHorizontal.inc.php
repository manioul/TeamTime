<?php
// menuHorizontal.inc.php
//
// Création du menu horizontal

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

// L'index du menu principal
$index = 1;

/*
 * Création du sous-menu des années pour les congés
 * et les évènements
 */
// La date courante
$now = new Date(date('Y-m-d'));
// L'année courante
$thisYear = date('Y');
// Si la date courante est avant le 30 avril, on affiche les congés de l'année précédente et ceux de l'année en cours
if ($now->compareDate("$thisYear-04-30") > 0) {
	$firstYear = $thisYear;
} else {
	$firstYear = $thisYear - 1;
}

// Congés
// Les entrées de menu sont insérées dans la base de données
// Les index 13 et 14 sont réservés à ces entrées
for ($idx = 13; $idx <=14; $idx++, $firstYear++) {
	$sql = "REPLACE `TBL_ELEMS_MENUS` (`idx`, `titre`, `lien`, `actif`) VALUES ($idx, '$firstYear', 'tableauxCong.php?year=$firstYear', TRUE)";
	$_SESSION['db']->db_interroge($sql);
}


// Évènements
// Les entrées de menu sont insérées dans la base de données
// Les index 15 et 16 sont réservés à ces entrées
$firstYear -= 2;
for ($idx = 15; $idx <=16; $idx++, $firstYear++) {
	$sql = "REPLACE `TBL_ELEMS_MENUS` (`idx`, `titre`, `lien`, `actif`) VALUES ($idx, '$firstYear', 'tableauxEvenSpec.php?year=$firstYear', TRUE)";
	$_SESSION['db']->db_interroge($sql);
}
/*
 * Fin de la préparation du sous-menu congés
 */


$menuHorizontal = new menu($index);
if (is_null($menuHorizontal)) {
	firePhpWarn("Le menu $index est NULL !");
	return NULL;
}
firePhpLog($menuHorizontal->arbre(), 'menu');
$smarty->assign('menu', $menuHorizontal);
$smarty->assign('class', 'menuHor');
$smarty->display('menu.tpl');
?>
