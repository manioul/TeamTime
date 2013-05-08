<?php
// debug.inc.php
//
// Ajoute les informations de débogage à une page
// en fonction de la variable $DEBUG
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

if ($DEBUG) {
	debug::getInstance()->format('html');
	//firePhpLog(debug::getInstance()->format(), 'format');
	// Information de temps de création de la page
	if ($conf['page']['elements']['timeInfo']) {
		$smarty->assign('debugTimes', debug::getInstance()->getChronos());
		$smarty->assign('constructTime', microtime(true) - $GLOBALS['initialTime']);
		$smarty->display('timeInfo.tpl');
	}
	if ($conf['page']['elements']['memUsage']) {
		$smarty->assign('memUsage', debug::getInstance()->peakMemory());
		$smarty->display('memUsage.tpl');
	}
	if ($conf['page']['elements']['whereWereU']) {
		$smarty->assign('whereWereU', debug::getInstance()->whereWereU());
		$smarty->display('whereWereU.tpl');
	}
	if ($conf['page']['elements']['lastError']) {
		$smarty->assign('lastError', debug::getInstance()->lastError());
		$smarty->display('lastError.tpl');
	}
	if ($conf['page']['elements']['lastErrorMessage']) {
		$smarty->assign('lastErrorMessage', debug::getInstance()->lastErrorMessage());
		$smarty->display('lastErrorMessage.tpl');
	}
	if ($conf['page']['elements']['debugMessages']) {
		$smarty->assign('debugMessages', debug::getInstance()->retrieveMessages());
		$smarty->display('debugMessages.tpl');
	}
}
?>
