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

utilisateurGrille::logon($_POST['login'], $_POST['pwd']);

header('Location:index.php');


?>
