<?php
// smarty_page.inc.php

// Initialise le moteur smarty et crée un objet smarty

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

require_once 'Smarty.class.php';

$smarty = new Smarty();

$smarty->template_dir = INSTALL_DIR . '/themes/' . $conf['theme']['current'] . '/templates';
$smarty->compile_dir = INSTALL_DIR . '/templates_c';
$smarty->config_dir = INSTALL_DIR . '/configs';
$smarty->cache_dir = INSTALL_DIR . '/cache';


// Gestion des styles
$smarty->assign('stylesheet', $stylesheet);

// Gestion du javascript
$smarty->assign('javascript', $javascript);


// Image `glue`
// Cette image contient tous les élements graphiques des pages du site rassemblées par glue
$smarty->assign('image', 'themes/' . $conf['theme']['current'] . '/images/glue.png');


// Attribution d'un titre à la page
$smarty->assign('titrePage', htmlentities($conf['page']['titre'], ENT_NOQUOTES, 'utf-8'));

// Définition du thème
$smarty->assign('theme', $conf['theme']['current']);

// Définition du langage
$smarty->assign('language', $GLOBALS['language']);

// Définition de la version
$smarty->assign('VERSION', VERSION);

// Définition des notes de version
$notesversion = "<strong>TeamTime v" . VERSION . "</strong>"; 
if ($DEBUG) {
	$notesversion .= " (Debug On";
	if ($GLOBALS['firePHP_OK']) {
		$notesversion .= " - firePHP On)";
	} else {
		$notesversion .= ")";
	}
} elseif ($GLOBALS['firePHP_OK']) {
		$notesversion .= " (firePHP On)";
}
$smarty->assign('notesversion', $notesversion); 

// Affichage des en-têtes de page
$smarty->display('header.tpl');

// Ajout du menu horizontal
if ($conf['page']['elements']['menuHorizontal']) include('menuHorizontal.inc.php');

// Ajout des messages
if ($conf['page']['elements']['messages']) include('messages.inc.php');

// Ajout du choix du thème
if ($conf['page']['elements']['choixTheme']) include('choixTheme.inc.php');

// Affichage du menu d'administration
if ($conf['page']['elements']['menuAdmin']) include('menuAdmin.inc.php');

?>
