<?php
/*
 * formulaireBriefing.js.php
 *
 */

$requireEditeur = true;

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
	$('#dateD').datepicker({
		defaultDate:0,
		changeMonth:true,
		onSelect:function(selectedDate){
		$("#dateF").datepicker("option","minDate",selectedDate);}
		});
	$('#dateF').datepicker({
		defaultDate:12,
		changeMonth:true,
		onSelect:function(selectedDate){
			$("#dateD").datepicker("option","maxDate",selectedDate);
			}
		});
	$.datepicker.setDefaults($.datepicker.regional['fr']);
});
