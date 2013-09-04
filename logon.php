<?php
// logon.php
//
// Script de gestion de la connexion

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

if (empty($_POST['login']) || empty($_POST['pwd'])) {
	header("Location:index.php?norights=1");
}

$login = $_POST['login'];
$pwd = $_POST['pwd'];

$conf['page']['include']['constantes'] = NULL; // Ce script nécessite la définition des constantes
$conf['page']['include']['errors'] = NULL; // le script gère les erreurs avec errors.inc.php
$conf['page']['include']['class_debug'] = NULL; // La classe debug est nécessaire à ce script
$conf['page']['include']['globalConfig'] = 1; // Ce script nécessite config.inc.php
$conf['page']['include']['init'] = 1; // la session est initialisée par init.inc.php
$conf['page']['include']['globals_db'] = 1; // Le DSN de la connexion bdd est stockée dans globals_db.inc.php
$conf['page']['include']['class_db'] = 1; // Le script utilise class_db.inc.php
$conf['page']['include']['session'] = 1; // Le script utilise les sessions par session.imc
$conf['page']['include']['classUtilisateur'] = NULL; // Le sript utilise uniquement la classe utilisateur (auquel cas, le fichier class_utilisateur.inc.php
$conf['page']['include']['class_utilisateurGrille'] = 1; // Le sript utilise la classe utilisateurGrille
$conf['page']['include']['class_cycle'] = NULL; // La classe cycle est nécessaire à ce script (remplace grille.inc.php

require 'required_files.inc.php';

$sql = sprintf("
	SELECT * FROM `TBL_USERS` AS `TU`
	, `TBL_AFFECTATION` AS `TA`
	, `TBL_CLASSE` AS `TC`
	WHERE `TA`.`uid` = `TU`.`uid`
	AND `TC`.`uid` = `TU`.`uid`
	AND `TU`.`login` = '%s'
	AND `TU`.`sha1` = SHA1('%s')
	", $_SESSION['db']->db_real_escape_string($login)
	, $_SESSION['db']->db_real_escape_string($login . $pwd)
);
$result = $_SESSION['db']->db_interroge($sql);
if (mysqli_num_rows($result) > 0) {
	session_regenerate_id(); // Éviter les attaques par fixation de session
	$row = $_SESSION['db']->db_fetch_assoc($result);
	$_SESSION['utilisateur'] = new utilisateurGrille($row);
	$_SESSION['AUTHENTICATED'] = true;
	$_SESSION['centre'] = $_SESSION['utilisateur']->centre();
	$_SESSION['team'] = $_SESSION['utilisateur']->team();
	// Mise à jour des informations de connexion
	$upd = sprintf("UPDATE `TBL_USERS` SET `lastlogin` = NOW(), `nblogin` = %s WHERE `login` = '%s'", $row['nblogin'] + 1, $row['login']);
	$_SESSION['db']->db_interroge($upd);
	$sql = sprintf("SELECT `groupe` FROM `TBL_GROUPS` WHERE `gid` >= '%s'", $row['gid']);
	$result2 = $_SESSION['db']->db_interroge($sql);
	while ($row = $_SESSION['db']->db_fetch_array($result2)) {
		$_SESSION[strtoupper($row[0])] = true;
	}
	mysqli_free_result($result2);
}
mysqli_free_result($result);
header('Location:index.php');


?>
