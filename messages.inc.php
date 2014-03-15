<?php
// messages.inc.php
//
// Affichage de messages d'ordre général

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

$messages = array();
$index = 0;

if (empty($_SESSION['iAmVirtual'])) {
	if (!empty($_SESSION['ADMIN'])) {
		$messages[$index]['message'] = "Connecté en tant que " . $_SESSION['utilisateur']->login();
	}
} else {
	$messages[$index]['message'] = "Connecté en tant que " . $_SESSION['utilisateur']->login() . " (" . $_SESSION['iAmVirtual'] . ")";
}
$messages[$index]['lien'] = "";
$messages[$index]['classe'] = "warn";
$index++;
if (!empty($_SESSION['ADMIN']) && !get_sql_globals_constant('online')) {
	$messages[$index]['message'] = "Le site est actuellement hors-ligne.";
	$messages[$index]['lien'] = "administration.php";
	$messages[$index]['classe'] = "warn";
	$index++;
}
if (!empty($_SESSION['iAmVirtual'])) {// && empty($_SESSION['ADMIN'])) {
	$messages[$index]['message'] = sprintf("Vous vous faites passer pour %s %s. Cliquez ici pour retrouver votre vraie personnalité...", $_SESSION['utilisateur']->prenom(), $_SESSION['utilisateur']->nom());
	$messages[$index]['lien'] = "impersonate.php?iWantMyselfBack=1";
	$messages[$index]['classe'] = "warn";
	$index++;
}
foreach ($_SESSION['utilisateur']->retrMessages() as $message) {
	$messages[$index]['message'] = $message->message();
	$message->setRead();
	$index++;
}
$_SESSION['utilisateur']->flushMessages();

$smarty->assign('messages', $messages);
$smarty->display('messages.tpl');
?>
