<?php
// firePHP.inc.php
//
// Intégration de firePHP dans les scripts php
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

if (!empty($conf['page']['elements']['firePHP']) && true === $conf['page']['elements']['firePHP']) { // && in_array($_SERVER['REMOTE_ADDR'], $conf['security']['allowed_firePHP'])) {
	require_once('FirePHPCore/FirePHP.class.php');
	$GLOBALS['firephp'] = FirePHP::getInstance(true);
	$GLOBALS['firephp']->setEnabled(true);
	$GLOBALS['firePHP_OK'] = true;
} else {
	$GLOBALS['firePHP_OK'] = false;
}

// firePhpLog est un wrapper vers FP::log()
// Ceci permet de vérifier que FP est bien chargé et peut être utilisé
function firePhpLog($var, $legende=false) {
	if (true === $GLOBALS['firePHP_OK']) {
		if (false !== $legende) {
			$GLOBALS['firephp']->log($var, $legende);
		} else {
			$GLOBALS['firephp']->log($var);
		}
	} else {
		return true;
	}
}
// firePhpInfo est un wrapper vers FP::info()
// Ceci permet de vérifier que FP est bien chargé et peut être utilisé
function firePhpInfo($var, $legende=false) {
	if (true === $GLOBALS['firePHP_OK']) {
		if (false !== $legende) {
			$GLOBALS['firephp']->info($var, $legende);
		} else {
			$GLOBALS['firephp']->info($var);
		}
	} else {
		return true;
	}
}
// firePhpWarn est un wrapper vers FP::warn()
// Ceci permet de vérifier que FP est bien chargé et peut être utilisé
function firePhpWarn($var, $legende=false) {
	if (true === $GLOBALS['firePHP_OK']) {
		if (false !== $legende) {
			$GLOBALS['firephp']->warn($var, $legende);
		} else {
			$GLOBALS['firephp']->warn($var);
		}
	} else {
		return true;
	}
}
// firePhpError est un wrapper vers FP::error()
// Ceci permet de vérifier que FP est bien chargé et peut être utilisé
function firePhpError($var, $legende=false) {
	if (true === $GLOBALS['firePHP_OK']) {
		if (false !== $legende) {
			$GLOBALS['firephp']->error($var, $legende);
		} else {
			$GLOBALS['firephp']->error($var);
		}
	} else {
		return true;
	}
}
// firePhpTable est un wrapper vers FP::table()
// Ceci permet de vérifier que FP est bien chargé et peut être utilisé
function firePhpTable($var, $legende=false) {
	if (true === $GLOBALS['firePHP_OK']) {
		if (false !== $legende) {
			$GLOBALS['firephp']->table($var, $legende);
		} else {
			$GLOBALS['firephp']->table($var);
		}
	} else {
		return true;
	}
}
// firePhpTrace est un wrapper vers FP::trace()
// Ceci permet de vérifier que FP est bien chargé et peut être utilisé
function firePhpTrace($var) {
	if (true === $GLOBALS['firePHP_OK']) {
		$GLOBALS['firephp']->trace($var);
	} else {
		return true;
	}
}
// firePhpGroup est un wrapper vers FP::group()
// Ceci permet de vérifier que FP est bien chargé et peut être utilisé
function firePhpGroup($var) {
	if (true === $GLOBALS['firePHP_OK']) {
		$GLOBALS['firephp']->group($var);
	} else {
		return true;
	}
}
// firePhpGroupEnd est un wrapper vers FP::groupEnd()
// Ceci permet de vérifier que FP est bien chargé et peut être utilisé
function firePhpGroupEnd() {
	if (true === $GLOBALS['firePHP_OK']) {
		$GLOBALS['firephp']->groupEnd();
	} else {
		return true;
	}
}

?>
