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
if (array_key_exists('ADMIN', $_SESSION)) {
	$colonnes[] = 'centre';
	$colonnes[] = 'team';
	$sql = sprintf("
		SELECT
		`nom`,
		`prenom`,
		`vismed`,
		`login`,
		`email`,
		`poids`,
		`centre`,
		`team`
		FROM `TBL_USERS`,
		`TBL_AFFECTATION`
		WHERE `actif` = TRUE
		AND `TBL_USERS`.`uid` = `TBL_AFFECTATION`.`uid`
		AND `beginning` <= '%s'
		AND `end` >= '%s'
		ORDER BY `nom`
		", date('Y-m-d')
		, date('Y-m-d')
	);
	// Création des champs pour l'affectation
?>
$(function() {
	var objLabelCentre = document.createElement('label');
	objLabelCentre.setAttribute('for', 'Centre');
	objLabelCentre.appendChild(document.createTextNode('Centre'));
	var objTdLabelCentre = document.createElement('td');
	objTdLabelCentre.appendChild(objLabelCentre);

	var objInputCentre = document.createElement('input');
	objInputCentre.setAttribute('type', 'text');
	objInputCentre.setAttribute('name', 'centre');
	objInputCentre.setAttribute('id', 'iCccentre');
	var objTdInputCentre = document.createElement('td');
	objTdInputCentre.appendChild(objInputCentre);
	var objFirstRow = document.createElement('tr');
	objFirstRow.appendChild(objTdLabelCentre);
	objFirstRow.appendChild(objTdInputCentre);

	var objLabelTeam = document.createElement('label');
	objLabelTeam.setAttribute('for', 'Team');
	objLabelTeam.appendChild(document.createTextNode('Team'));
	var objTdLabelTeam = document.createElement('td');
	objTdLabelTeam.appendChild(objLabelTeam);

	var objInputTeam = document.createElement('input');
	objInputTeam.setAttribute('type', 'text');
	objInputTeam.setAttribute('name', 'team');
	objInputTeam.setAttribute('id', 'iCcteam');
	var objTdInputTeam = document.createElement('td');
	objTdInputTeam.appendChild(objInputTeam);
	var objSecondRow = document.createElement('tr');
	objSecondRow.appendChild(objTdLabelTeam);
	objSecondRow.appendChild(objTdInputTeam);

	$('#iCcemail').parent().parent().after(objSecondRow);
	$('#iCcemail').parent().parent().after(objFirstRow);
});
<?	
} else {
	$affectation = $_SESSION['utilisateur']->affectationOnDate(date('Y-m-d'));
	$sql = sprintf("
		SELECT
		`nom`,
		`prenom`,
		`vismed`,
		`login`,
		`email`,
		`poids`
		FROM `TBL_USERS`,
		`TBL_AFFECTATION`
		WHERE `actif` = TRUE
		AND `TBL_USERS`.`uid` = `TBL_AFFECTATION`.`uid`
		AND `beginning` <= '%s'
		AND `end` >= '%s'
		AND (`centre` = '%s' OR `centre` = 'all')
		AND (`team` = '%s' OR `team` = 'all')
		ORDER BY `nom`
		", date('Y-m-d')
		, date('Y-m-d')
		, $affectation['centre']
		, $affectation['team']
	);
}
$result = $_SESSION['db']->db_interroge($sql);
// Fonction qui met à jour l'affichage dans le formulaire
// en fonction du nom choisi
?>
function updDispFormCc()
{
	var aUsers = new Array();
	var aColonnes = new Array();
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
	$("#iCclogin").val(aUsers[$('#sCcnom').val()].login);
	$("#iCcemail").val(aUsers[$('#sCcnom').val()].email);
	$("#iCccentre").val(aUsers[$('#sCcnom').val()].centre);
	$("#iCcteam").val(aUsers[$('#sCcnom').val()].team);
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
	submitRequest(sRequest, 'ajax.php');
}

$(function() {
	updDispFormCc();
});
// Une fonction pour interragir avec la base de données
// Mise à jour et suppression d'enregistrements
// Utilisée par pereq.php
function opDb(op, table, id, field, val)
{
	var sRequest = "op="+op+"&t="+table+"&id="+id+"&field="+field+"&val="+val;
	submitRequest(sRequest, "ajax.php");
}
<?
//ob_end_flush();
?>
