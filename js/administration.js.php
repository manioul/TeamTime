<?php
/*
 * administration.js.php
 */

// Require authenticated user
// L'utilisateur doit être logué/admin pour accéder à cette page
// $requireAuthenticatedUser = true;
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

firePHPLog($_SESSION, 'SESSION');
$sql = "SHOW COLUMNS FROM `TBL_USERS`";
$result = $_SESSION['db']->db_interroge($sql);
$colonnes = array();
while ($row = $_SESSION['db']->db_fetch_array($result)) {
       $colonnes[] = $row[0];
}
$sql = "SELECT `nom`, `prenom`, `classe`, `date arrivee`, `date theorique`, `date pc`, `date ce`, `date cds`, `date vismed`, `login`, `email`, `poids` FROM `TBL_USERS` WHERE `gid` > 0 AND `actif` = TRUE";
$result = $_SESSION['db']->db_interroge($sql);
// Fonction qui met à jour l'affichage dans le formulaire
// en fonction du nom choisi
?>
function updDispFormCc()
{
	var aUsers = new Array();
	var aColonnes = new Array();
	<?
foreach ($colonnes as $col) { ?>
	aColonnes[aColonnes.length] = '<?=str_replace(' ', '_', $col)?>';
	<?
while ($row = $_SESSION['db']->db_fetch_assoc($result)) { ?>
	aUsers['<?=$row['nom']?>'] = new Object();
	<?
	foreach ($colonnes as $col) {
		if (!empty($row[$col])) {
?>
	aUsers['<?=$row['nom']?>'].<?=str_replace(' ', '_', $col)?> = '<?=$row[$col]?>';
<? 		}
	}
} ?>
<? } ?>
	$("#iCclogin").val(aUsers[$('#sCcnom').val()].login);
	$("#iCcemail").val(aUsers[$('#sCcnom').val()].email);
}
function subCc()
{
	var sRequest = "";
	var sAmpersand = '&';
	var oForm = document.forms.fCc;
	for (var i=0;i<oForm.length-1;i++)
	{
		if (oForm[i].type != 'checkbox' || oForm[i].checked == true)
		{
			sRequest += oForm[i].name + "=" + oForm[i].value + sAmpersand;
		}
	}
	sRequest = sRequest.substr(0, sRequest.length-sAmpersand.length);
	submitRequest(sRequest, 'updateCc.php');
	return true;
}

$(function() {
	updDispFormCc();
});
<?
//ob_end_flush();
?>
