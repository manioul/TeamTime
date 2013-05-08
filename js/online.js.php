<?php
/*
 * online.js.php
 */

// Require authenticated user
// L'utilisateur doit être logué/admin pour accéder à cette page
// $requireAuthenticatedUser = true;
$requireAdmin = true;

header('Content-Type: application/javascript');
//ob_start();
$conf['page']['elements']['firePHP'] = true;

require_once('../firePHP.inc.php');
require_once('../classes/class_utilisateur.inc.php');
require_once('../classes/class_utilisateurGrille.inc.php');
require_once('../classes/class_debug.inc.php');
require_once('../config.inc.php');
require_once('../init.inc.php');
require_once('../constantes.inc.php');
require_once('../globals_db.inc.php');
require_once('../classes/class_db.inc.php');
require_once('../session.inc.php');
?>
$(function() {
	$("#ionoff").click(function() {
		if ($(this).hasClass('offline')) {
			$(this).removeClass('offline');
			$(this).addClass('online');
			submitRequest('r=on', 'online.php');
		} else if ($(this).hasClass('online')) {
			$(this).removeClass('online');
			$(this).addClass('offline');
			submitRequest('r=off', 'online.php');
		}
	});
});
<?
//ob_end_flush();
?>
