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
?>
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

//
// Recherche les utilisateurs dont le nom commence par les lettres passées (au moins deux lettres)
//
function showUsers(field)
{
	$('#affectation').hide();
	$('#lCredentials').hide();
	$('#submitContact1').hide();
	$('#administration').hide();
	$('#prenom').val("");
	$('#email').val("");
	$('input[name="uid"]').val("");
	$('#login').val("");
<?php
	if (array_key_exists('ADMIN', $_SESSION)) {
		?>
	$('#submitContact2').hide();
<?php
	}
?>
	if (field.value.length >= 2)
	{
		var req =  createInstance();

		req.onreadystatechange = function()
		{ 
			if(req.readyState == 4)
			{
				if(req.status == 200)
				{
						$('#iANU').remove();
						$('#affectation').hide();
						$('tr[id^="affectation"]').remove();
						$('#dropdownbox').after('<button name="iANU" id="iANU" class="bouton" onclick="return ANU();">Ajouter un nouvel utilisateur</button>');
						$('#lUid').hide();
						$('#lPrenom').hide();
						$('#lEmail').hide();
						$('#lAffectation').hide();
						$('#lCredentials').hide();
					if(req.responseText == '0') // Aucun nom ne correspond aux caractères saisis, c'est donc une création
					{
						$('#dropdownbox').hide('slow');
					} else {
						$('#dropdownbox > ul').remove();
						$('#dropdownbox').append('<ul style="width:190px;max-height:200px;overflow-y:auto;margin-left:120px;padding:3px;background-color:rgba(255,255,255,.8);z-index:800;position:absolute;"></ul>');
						var aArray = jQuery.parseJSON(req.responseText);
						for (elem in aArray)
						{
							$('#dropdownbox > ul').append('<li onclick="fillUser('+aArray[elem].uid+')" style="border:none;margin:0;padding:0;cursor:pointer;">'+aArray[elem].nom+' '+aArray[elem].prenom+'</li>');
						}
						$('#dropdownbox').show('slow');
					}
				}	
				else	
				{
					alert("Error: returned status code " + req.status + " " + req.statusText);
				}	
			} 
		}; 

		req.open("POST", 'ajax.php', true); 
		req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		req.send('form=LU&nom='+field.value);
	}
}
//
// Remplit les champs avec les infos de l'utilisateur
//
function fillUser(uid)
{
	var req =  createInstance();

	req.onreadystatechange = function()
	{ 
		if(req.readyState == 4)
		{
			if(req.status == 200)
			{
				$('#iANU').remove();
				$('#dropdownbox').hide('slow');
				$('li').show('slow');
				$('#affectation').show('slow');
				var aUser = jQuery.parseJSON(req.responseText);
				$('#nom').val(aUser.nom);
				$('#prenom').val(aUser.prenom);
				$('#email').val(aUser.email);
				$('input[name="uid"]').val(aUser.uid);
				$('#login').val(aUser.login);
				$('#centre').val(aUser.centre);
				$('#team').val(aUser.team);
				$('#grade').val(aUser.grade);
				// Affiche les affectations de l'utilisateur
				for (var i=0;i < aUser.affectations.length; i++)
				{
					$('tbody').append("<tr id='affectation"+aUser.affectations[i].aid+"'><td>"+$('option[value="'+aUser.affectations[i].centre+'"]').text()+"</td><td>"+$('option[value="'+aUser.affectations[i].team+'"]').text()+"</td><td>"+$('option[value="'+aUser.affectations[i].grade+'"]').text()+"</td><td>"+aUser.affectations[i].beginning+"</td><td>"+aUser.affectations[i].end+"</td><td><div class='imgwrapper12' title="+'"Supprimer l'+"'"+'entrée" onclick="supprInfo('+"'affectation', "+aUser.affectations[i].aid+", "+aUser.uid+')" style="left:5px;cursor:pointer;"><img class="cnl" src="themes/<?=$conf['theme']['current']?>/images/glue.png" alt="supprimer" /></div></td></tr>');
				}
			}	
			else	
			{
				alert("Error: returned status code " + req.status + " " + req.statusText);
			}	
		} 
	}; 

	req.open("POST", "ajax.php", true); 
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	req.send("form=FU&uid="+uid);
}
//
// Crée les champs pour la création d'un nouvel utilisateur
//
function ANU()
{
	$('#lPrenom').show();
	$('#lEmail').show();
	$('button').remove();
	$('#lCredentials').show();
	$('#submitContact1').show();
	$('#administration').show();
	$('#affectation').show();
<?php
	if (array_key_exists('ADMIN', $_SESSION)) {
		?>
	$('#submitContact2').show();
<?php
	}
?>
	return false;
}
<?
//ob_end_flush();
?>
