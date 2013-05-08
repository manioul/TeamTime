<?php
// required_files.inc.php

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

require_once 'firePHP.inc.php';
// Gestion des dépendances
if (isset($conf['page']['include']['class_utilisateurGrille']))	$conf['page']['include']['class_debug'] = 1;
if (isset($conf['page']['include']['class_db']))		$conf['page']['include']['constantes'] = 1;

if (isset($conf['page']['include']['constantes']))		require_once 'constantes.inc.php';
if (isset($conf['page']['include']['errors']))			require_once 'errors.inc.php';
if (isset($conf['page']['include']['class_debug']))		require_once 'classes/class_debug.inc.php';
if (isset($conf['page']['include']['globalConfig']))		require_once 'config.inc.php';
if (isset($conf['page']['include']['globals_db']))		require_once 'globals_db.inc.php';
if (isset($conf['page']['include']['class_db']))		require_once 'classes/class_db.inc.php';
if (isset($conf['page']['include']['class_utilisateurGrille']))	require_once 'classes/class_utilisateurGrille.inc.php';
if (isset($conf['page']['include']['class_date']))		require_once 'classes/class_date.inc.php';
if (isset($conf['page']['include']['class_cycle']))		require_once 'classes/class_cycle.inc.php';
if (isset($conf['page']['include']['class_menu']))		require_once 'classes/class_menu.inc.php';
if (isset($conf['page']['include']['class_article']))		require_once 'classes/class_article.inc.php';
if (isset($conf['page']['include']['init']))			require_once 'init.inc.php';
if (isset($conf['page']['include']['session']))			require_once 'session.inc.php';
if (isset($conf['page']['include']['smarty']))			require_once 'smarty_page.inc.php';

?>
