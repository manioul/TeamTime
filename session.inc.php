<?php
// session.inc.php
// Gère les sessions utilisateurs

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

function session_begin($session_var, $back) {
	session_start();
	if (!isset($_SESSION[$session_var])) {
		$back = basename($back);
		header('Location:index.php' . !empty($back) ? "?back=$back" : '');
	}
}

// Insère les constantes de TBL_CONSTANTS dans une variable de session
function sql_globals_constants() {
	$sql = 'SELECT * FROM `TBL_CONSTANTS`';
	$result = $_SESSION['db']->db_interroge($sql);
	while ($row = $_SESSION['db']->db_fetch_assoc($result)) {
		if (isset($GLOBALS[$row['nom']])) {
			if ($DEBUG) debug::getInstance()->triggerError("\$GLOBALS['".$row['nom']."'] est déjà défini");
		} else {
			switch ($row['type']) {
				case 'int':
					$GLOBALS[$row['nom']] = (int) $row['valeur'];
					break;
				case 'bool':
					if ('false' == $row['valeur'] || 'FALSE' == $row['valeur']) {
						$GLOBALS[$row['nom']] = FALSE;
					} else {
						$GLOBALS[$row['nom']] = (bool) $row['valeur'];
					}
					break;
				case 'float':
					$GLOBALS[$row['nom']] = 0 + $row['valeur'];
					break;
				default:
					$GLOBALS[$row['nom']] = $row['valeur'];
			}
		}
	}
	mysqli_free_result($result);
}

function get_sql_globals_constant($constant_name) {
	if (isset($GLOBALS[$constant_name])) return $GLOBALS[$constant_name];
	$sql = "SELECT * FROM `TBL_CONSTANTS` WHERE `nom` = '$constant_name'";
	$result = $_SESSION['db']->db_interroge($sql);
	if (mysqli_num_rows($result) == 1) {
		$row = $_SESSION['db']->db_fetch_assoc($result);
		switch ($row['type']) {
			case 'int':
				$GLOBALS[$row['nom']] = (int) $row['valeur'];
				break;
			case 'bool':
				if ('false' == $row['valeur'] || 'FALSE' == $row['valeur']) {
					$GLOBALS[$row['nom']] = FALSE;
				} else {
					$GLOBALS[$row['nom']] = (bool) $row['valeur'];
				}
				break;
			case 'float':
				$GLOBALS[$row['nom']] = 0 + $row['valeur'];
				break;
			default:
				$GLOBALS[$row['nom']] = $row['valeur'];
		}
		return $GLOBALS[$row['nom']];
	} else {
		if ($DEBUG) debug::getInstance()->triggerError("Plusieurs ou aucun résultat à la requête sql '$sql'");
		return FALSE;
	}
	mysqli_free_result($result);
}


// Si la page nécessite que l'utilisateur soit logué, et qu'il ne l'est pas, on redirige vers la page de login
if (!empty($requireAuthenticatedUser) && empty($_SESSION['AUTHENTICATED'])) {
	header('Location:index.php?norights=1&back=' . htmlspecialchars($_SERVER['REQUEST_URI']));
	exit;
}
if (!empty($requireEditeur) && empty($_SESSION['EDITEURS'])) {
	header('Location:index.php?norights=1&back=' . htmlspecialchars($_SERVER['REQUEST_URI']));
	exit;
}
if (!empty($requireVirtualAdmin) && empty($_SESSION['iAmVirtual']) && empty($_SESSION['ADMIN'])) {
	header('Location:index.php?norights=1&back=' . htmlspecialchars($_SERVER['REQUEST_URI']));
	exit;
}
if (!empty($requireAdmin) && empty($_SESSION['ADMIN'])) {
	header('Location:index.php?norights=1&back=' . htmlspecialchars($_SERVER['REQUEST_URI']));
	exit;
}


# S'il n'y a pas d'objet base de données défini dans la session, on en définit un
if (empty($_SESSION['db']) || !is_object($_SESSION['db'])) {
	$_SESSION['db'] = new database($GLOBALS['DSN']['admin']);
}

// Vérifie si le site est en maintenance
if (FALSE === get_sql_globals_constant('online') && empty($_SESSION['ADMIN'])) header('Location:offline.html');

?>
