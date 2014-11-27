<?php
/*
 *
 *  grille2.js.php
 *
*/

// Require authenticated user
// L'utilisateur doit être logué pour accéder à cette page
$requireAuthenticatedUser = true;

ob_start();

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
// tableau des dispos
$sql = sprintf("
	SELECT `dispo`
	FROM `TBL_DISPO`
	WHERE `type decompte` = 'dispo'
	AND `actif` IS TRUE
	AND (`centre` = '%s' OR `centre` = 'all')
	AND (`team` = '%s' OR `team` = 'all')"
	, $_SESSION['utilisateur']->centre()
	, $_SESSION['utilisateur']->team()
	);
$dispos = "";
$result = $_SESSION['db']->db_interroge($sql);
while ($x = $_SESSION['db']->db_fetch_assoc($result)) {
	$dispos .= sprintf("'%s',", $x['dispo']);
}
mysqli_free_result($result);
$sDispos = substr($dispos, 0, -1);

$affectation = $_SESSION['utilisateur']->affectationOnDate(date('Y-m-d'));

//***********************
// Fonctions principales
//***********************
// Recherche les dispo pour une journée oThis
?>
function getAvailableOccupations(oThis) {
	var aArray = infosFromId(oThis.id);
	if (!aArray) {
	       	alert("Impossible d'obtenir les infos");
		return false;
       	}
<?php
	$find_in_set = "";
	if (!array_key_exists('ADMIN', $_SESSION)) { // Les non admins ont des restrictions sur les dispo qu'ils peuvent poser
		foreach (array_flip(array_flip(array_merge(array('all', $_SESSION['utilisateur']->login(), $affectation['grade']), $_SESSION['utilisateur']->roles()))) as $set) {
			$find_in_set .= sprintf("FIND_IN_SET('%s', `peut poser`) OR ", $_SESSION['db']->db_real_escape_string($set));
		}
		$find_in_set = " AND (" . substr($find_in_set, 0, -4) . ")";
	}
	$sqlDispo = sprintf("
		SELECT `dispo`
		, `jours possibles`
		FROM `TBL_DISPO`
		WHERE `actif` IS TRUE
		AND (`centre` = '%s' OR `centre` = 'all')
		AND (`team` = '%s' OR `team` = 'all')
		%s
		ORDER BY `poids`"
		, $_SESSION['utilisateur']->centre()
		, $_SESSION['utilisateur']->team()
		, $find_in_set
	);
	$resDispo = $_SESSION['db']->db_interroge($sqlDispo);
	$sqlCycle = sprintf("SELECT `vacation` FROM `TBL_CYCLE` WHERE `vacation` != '%s'", REPOS);
	$result = $_SESSION['db']->db_interroge($sqlCycle);

	while ($x = $_SESSION['db']->db_fetch_row($result)) {
		$vacations[] = $x[0];
	}
	mysqli_free_result($result);
	$aDispo = "";
	$dispo = array();
	foreach($vacations as $vacation) { // On initialise le tableau des dispos possibles
		$dispo[$vacation] = '';
	}
	while ($row = $_SESSION['db']->db_fetch_row($resDispo)) {
		foreach ($vacations as $vacation) {
			if ($row[1] == 'all' || preg_match("/$vacation/", $row[1]) > 0) { // On ajoute les dispo valides par vacation
				$dispo[$vacation] .= sprintf("'%s',", $row[0]);
			}
		}
	}
	mysqli_free_result($resDispo);
	?>
	switch (aArray["Vacation"]) {<?php
	foreach($vacations as $vacation) {?>
		case "<?=$vacation?>":
			var aDispo = new Array(<?=substr($dispo[$vacation], 0, -1)?>);
			break;
	<?php
	}?>
			default:
				var aDispoExt = new Array();
			break;
	}
	// Suppression des jours week end où il ne devrait pas y en avoir
	if (!aArray["isFerie"]) {
		var x = aDispo.indexOf("W");
		var aSecondSlice = aDispo.splice(x+1,aDispo.length);
		aDispo = aDispo.splice(0,x).concat(aSecondSlice);
	}
	var sStartString = '<div id="sandbox"><ul>';
	var sMidString = "<li>&nbsp;</li>";
	for (sDispo in aDispo) {
		sMidString += "<li>"+aDispo[sDispo]+"</li>";
	}
	var sEndString = '</ul></div>';
	var sString = sStartString + sMidString + sEndString;
	return sString;
}
// Ajoute une dispo sDispo à l'objet oThis
function addDispo(oThis, sDispo)
{
	// Si sDispo est vide
	if (escape(sDispo) == "%A0")
	{
		sDispo = "";
	}
	// Màj de la page
	var sOldDispo = $(oThis).text();
	changeValue(oThis, sDispo);

	var aArray = infosFromId(oThis.id);

	// Si l'ancienne ou la nouvelle valeur nécessite un recalcul des dispos
	var aDispos = new Array(<?=$sDispos?>);
	for (var iInner in aDispos)
	{
		if (aDispos[iInner] == sDispo || aDispos[iInner] == sOldDispo)
		{
			decompteDispo(aArray["uid"], aArray["cycleId"]);
		}
	}

	// Si l'ancienne valeur était un 'V', on doit propager
	debug("oldDispo: "+sOldDispo);
	switch (sOldDispo) {
	       case "V":
		var aNewArray = new Array();
		// pour les V la valeur est répétée iX fois à gauche
		var iX = 2;
		var oTempThis = oThis;
		for (var i=1; i<=iX; i++) {
			var oSibling = $(oTempThis).prev();
			aNewArray = infosFromId(oSibling[0].id);
			if (!aNewArray) {
				debug("aNewArray n'existe pas.");
				break;
			}
			// Si on est sur le jour d'avant
			debug(parseInt(aArray["Day"]) - aNewArray["Day"]);
			if ((parseInt(aArray["Day"]) - aNewArray["Day"]) <= iX && (parseInt(aArray["Day"]) - aNewArray["Day"]) > 0) {
				changeValue(oSibling[0], "");
			}
			oTempThis = oSibling[0];
		}
		// et à droite
		oTempThis = oThis;
		for (var i=1; i<=iX; i++) {
			var oSibling = $(oTempThis).next();
			aNewArray = infosFromId(oSibling[0].id);
			if (!aNewArray) {
				break;
			}
			//alert(aArray["Day"]+" - "+aNewArray["Day"]);
			// Si on est sur le jour d'avant
			if ((aNewArray["Day"] - parseInt(aArray["Day"])) <= iX && (aNewArray["Day"] - parseInt(aArray["Day"])) > 0) {
				changeValue(oSibling[0], "");
			}
			oTempThis = oSibling[0];
		}
		break;
		case "Rempla":
			$('#'+oThis.id).attr('title', '');
		break;
	}
	// Cas nécessitant des actions supplémentaires
	switch (sDispo) {
		case "V":
			var aNewArray = new Array();
			// pour les V la valeur est répétée iX fois à gauche
			var iX = 2;
			var oTempThis = oThis;
			for (var i=1; i<=iX; i++) {
				var oSibling = $(oTempThis).prev();
				aNewArray = infosFromId(oSibling[0].id);
				if (!aNewArray) {
					break;
				}
				// Si on est sur le jour d'avant
				//alert(aArray["Day"]+" - "+aNewArray["Day"]);
				if ((parseInt(aArray["Day"]) - aNewArray["Day"]) <= iX && (parseInt(aArray["Day"]) - aNewArray["Day"]) > 0) {
					changeValue(oSibling[0], sDispo);
				}
				oTempThis = oSibling[0];
			}
			// et à droite
			oTempThis = oThis;
			for (var i=1; i<=iX; i++) {
				var oSibling = $(oTempThis).next();
				aNewArray = infosFromId(oSibling[0].id);
				if (!aNewArray) {
					break;
				}
				//alert(aArray["Day"]+" - "+aNewArray["Day"]);
				// Si on est sur le jour d'avant
				if ((aNewArray["Day"] - parseInt(aArray["Day"])) <= iX && (aNewArray["Day"] - parseInt(aArray["Day"])) > 0) {
					changeValue(oSibling[0], sDispo);
				}
				oTempThis = oSibling[0];
			}
			break;
		case "Rempla":
			// Placement de la boîte de choix
			var p = $('#'+oThis.id).position();
			$("#dFormRemplacement").css({"left" : p.left + 10 , "top" : p.top + 20});
			$("#dFormRemplacement").show('slow');
			$("#remplaUid").val(aArray['uid']);
			$("#remplaYear").val(aArray['Year']);
			$("#remplaMonth").val(aArray['Month']);
			$("#remplaDay").val(aArray['Day']);
			break;
		default:
			debug(sDispo);
		break;
	}
	return true;
}
// Modifie la valeur d'une case et effectue les actions s'y rapportant (recalcul notamment)
function changeValue(oThis, sDispo)
{
	var sAjaxRequest = prepareAjaxRequest(oThis, sDispo);
	submitRequest(sAjaxRequest, 'update_grille.php');
	$(oThis).text(sDispo);
	$(oThis).addClass('emphasize');
	comptePresents(oThis.id);
}
<?
if (array_key_exists('TEAMEDIT', $_SESSION)) {
	// Ces fonctions ne doivent pas être accessible à tous
	// Seuls les éditeurs peuvent (dé)protéger la grille
// Effectue les actions nécessaires à la (dé)protection de la grille
// Envoi d'une requête ajax
// mise à jour des données de la grille (affichage...)
?>
function lock(sS)
{
	// Extrait les informations permettant de protéger la grille
	var aArray = infosFromId(sS);
	var sDate = aArray['Year'] + aArray['Month'] + aArray['Day'];
	var sIdExtrait = aArray[1];
	// Prépare la requête ajax
	var sReq = 'date='+sDate;
	// Recherche tous les éléments td dont l'id finit par sIdExtrait
	$('td[id$='+sIdExtrait+']').each(function () {
		// Si la classe decompte est présente, on doit verrouiller la case
		if ( $(this).hasClass('decompte') ) {
			if ( $(this).hasClass('protected') ) {
				$(this).removeClass('protected');
			} else {
				$(this).addClass('protected');
				// On doit également updater la requête
				var iText = parseInt($(this).text());
				if (iText != 0 && !isNaN(iText)) {
					sExtReq += this.id+'='+iText+'&';
				}
			}
		}
	});
	// Envoi de la requête ajax
	submitRequest(sReq, 'lock.php');
	// énumére les cases de décompte correspondantes à la grille à (dé)protéger
	var aObjSep = $('td[id*='+sIdExtrait+']');
	// itère à travers les cases de décompte
	for(var iCpt=0; iCpt<aObjSep.length;iCpt++)
	{
		// Le précédent "sibling" correspond à la dernière case de la grille à (dé)protéger
		var oObjSep = aObjSep[iCpt].previousSibling;
		while(!$('#'+oObjSep.id).hasClass('decompte') && !$('#'+oObjSep.id).hasClass('nom'))
		{
				// regarde si la case est protégée ou non
				if ($('#'+oObjSep.id).hasClass('protected'))
				{ // la case est protégée, on déprotège
					attribUnprotect(oObjSep);
					comptePresents(oObjSep.id);
				}
				else
				{ // la case n'est pas protégée, on protège
					attribProtect(oObjSep);
					comptePresents(oObjSep.id);
				}
				// itération sur les précédents "siblings"
				oObjSep = oObjSep.previousSibling;
				if (!oObjSep)
				{
					break;
				}
		}
	}
	event.preventDefault(); // Le lien ne doit pas être suivi
}
// Effectue les actions pour la protection d'une case
function attribProtect(oThis)
{
	$('#'+oThis.id).unbind();
	$('#'+oThis.id).addClass('protected');
}
// Effectue les actions pour la déprotection d'une case
function attribUnprotect(oThis)
{
	$('#'+oThis.id).removeClass('protected');
	$('#'+oThis.id).toggle(function() {
		// l'élément cliqué est mis en valeur
		$(this).addClass("emphasize");
		// La première case (contenant le nom) est mise en évidence
		var oParent = $(this).parent();
		var oChildren = $(oParent[0]).children();
		$(oChildren[0]).addClass("emphasize");
		// Recherche des informations de l'élément afin de déterminer la tête de colonne correspondante
		var aArray = infosFromId(this.id);
		var sVacationId = "#a"+aArray["Year"]+"m"+aArray["Month"]+"j"+aArray["Day"]+"s"+aArray["Vacation"];
		// la tête de colonne est mise en évidence
		$(sVacationId).addClass("emphasize");
		// Recherche des possibles occupations pour remplir la boîte de choix
		var sString = getAvailableOccupations(this);
		$("#sandbox").replaceWith(sString);
		// Placement de la boîte de choix
		var p = $(this).position();
		$("#sandbox").css({"left" : p.left + 10 , "top" : p.top + 20});
		$("#sandbox").show('slow');
		// Action du clic dans la boîte de choix
		var oThis = this;
		$("#sandbox li").click(function() {
			$("#sandbox").hide('slow');
			addDispo(oThis, $(this).text());
			$("td.nom").removeClass("emphasize");
			$(sVacationId).removeClass("emphasize");
		});
		},function() {
			$(this).removeClass("emphasize");
			$("#sandbox").hide();
			$("td.nom").removeClass("emphasize");
			// Recherche la tête de colonne correspondante
			var aArray = infosFromId(this.id);
			var sVacationId = "#a"+aArray["Year"]+"m"+aArray["Month"]+"j"+aArray["Day"]+"s"+aArray["Vacation"];
			// mise en évidence de la tête de colonne supprimée
			$(sVacationId).removeClass("emphasize");
		});
}
// Ajoute un texte pour le verrouillage de la grille
function definitVerrouillage()
{
	return; // TODO on reçoit une erreur 500 : dépassement de mémoire autorisé dans class_date.php sur la méthode calendrier()
	var aDecSep = $('td[id*="lock"]');
	for (var iCpt=0; iCpt<aDecSep.length; iCpt++)
	{
		var sS =  aDecSep[iCpt].id;
		var aLinks = $('#'+sS).find('a');
		var sUri = aLinks[0];
		var aMatches = sUri.href.match(/lock=(.+)&/);
		var sLockOrNot = aMatches[1];
		$('#'+sS).children('a').remove(); // On supprime le lien (il ne doit pas être suivi si js est activé
	       	// Sauvegarde du lien pour récupérer l'état de lock (ouvre ou bloque)
		$('#'+sS).click(lock(sS));
		$('#'+sS).addClass('pointer');
	}
}
// Fonction init pour les éditeurs
$(function() {
		definitVerrouillage();
});
$(function() {
	$('td[style]').removeClass();
}
);
<?
}
// Compte le nombre de présent pour la colonne à laquelle sId appartient
?>
function comptePresents(sId)
{
	if (sId == undefined) { return false; }
	var aArray =sId.match(/.*(a\d+m\d+j\d+s\w+)/);
	if (aArray instanceof Array)
	{
		var sDate = aArray[1];
		var aAffDate = sId.match(/.*a(\d+)m(\d+)j(\d+)s(\w+)c(\d+)/);
		var sAffDate = aAffDate[3]+"/"+aAffDate[2]+"/"+aAffDate[1]+" en "+aAffDate[4];
	}
	else
	{
		return false;
	}
	<?
	// Recherche les dispo qui correspondent à des absences pour les enlever du décompte des présents
	$sqlDispo = sprintf("SELECT `dispo` FROM `TBL_DISPO` WHERE `actif` = '1' AND `absence` = '1' ORDER BY `poids`");
	$sqlDispo = sprintf("
		SELECT `dispo`
		, 1 - `absence`
		, `type decompte`
		FROM `TBL_DISPO`
		WHERE `actif` IS TRUE
		AND (`centre` = '%s' OR `centre` = 'all')
		AND (`team` = '%s' OR `team` = 'all')
		ORDER BY `poids`"
		, $_SESSION['utilisateur']->centre()
		, $_SESSION['utilisateur']->team()
	);
	$resDispo = $_SESSION['db']->db_interroge($sqlDispo);
	$absences = "";
	$dispo = "";
	while ($abs = $_SESSION['db']->db_fetch_row($resDispo)) {
		$absences .= sprintf("'%s':%u, ", $abs[0], $abs[1] + .5);
		if ($abs[2] == "dispo") {
			$dispo .= sprintf("'%s':0, ", $abs[0]);
		} else {
			$dispo .= sprintf("'%s':%0.1f, ", $abs[0], $abs[1]);
		}
	}
	mysqli_free_result($resDispo);
	?>
	var aAbsent = {<?=substr($absences, 0, -2)?>};
	var aD = {<?=substr($dispo, 0, -2)?>};
	var aPresents = {'cds': 0, 'ce': 0, 'fmp' : 0, 'dtch': 0, 'pc': 0, 'c': 0, 'aD':0};
	var aListeUid = listeUid();
	for (var iUid in aListeUid)
	{
		var sTemp = '#u'+iUid+sDate;
		if (!$(sTemp).hasClass('absent'))
		{
			if ($(sTemp).text() == " ") {
				aPresents[aListeUid[iUid]]++;
				if (aListeUid[iUid] != 'c') {
					aPresents['aD']++;
				}
			} else {
				aPresents[aListeUid[iUid]] += aAbsent[$(sTemp).text()] || 0;
				if (aListeUid[iUid] != 'c') {
					aPresents['aD'] += aD[$(sTemp).text()] || 0;
				}
			}
		}
	}
	aPresents['pc'] += aPresents['ce'] + aPresents['cds'] + aPresents['dtch'] + aPresents['fmp'];
	$('#dec'+sDate).text(aPresents['aD']+"/"+aPresents['pc']+"/"+aPresents['c']);
	if (aPresents['pc'] < <?=get_sql_globals_constant('effectif_mini')?> && !$('#dec'+sDate).hasClass('protected'))
	{
		$('#dec'+sDate).addClass('emphasize');
		attention("Trop de personnels absents: "+sAffDate+".");
	}
	else
	{
		$('#dec'+sDate).removeClass('emphasize');
	}
}
<?
// Décompte l'ensemble des présents de la grille
?>
function decomptePresents()
{
	var aDecompte = $('.dcpt');
	for (var iCpt=0; iCpt<aDecompte.length; iCpt++)
	{
		comptePresents(aDecompte[iCpt].id);
	}
}
<?
	/*
	* Les paramètres sont numériques :
	* Id utilisateur et Id du cycle
 	*/
?>
function decompteDispo(iUid, iCycleId)
{
	if ((iUid == undefined) || (iCycleId == undefined))
	{
		return false;
	}
	var aDispos = new Array(<?=$sDispos?>);
	var aPresence=$('.presence[id^="u'+iUid+'a"]');
	var aConcerned=$('.presence[id^="u'+iUid+'a"][id$="c'+iCycleId+'"]');
	var sIniDecompte=$('#decDispou'+iUid+'c'+iCycleId).text();
	for (var iIndex=0; iIndex<aConcerned.length; iIndex++)
	{
		for (var iInner in aDispos)
		{
			if (aDispos[iInner] == $(aConcerned[iIndex]).text())
			{
				sIniDecompte++;
			}
		}
	}
	var iNextCycleId=parseInt(iCycleId)+1;
	$('*[id="decDispou'+iUid+'c'+iNextCycleId+'"]').text(sIniDecompte);
}
<?
	// Fonction qui prépare la chaîne pour la requête ajax
	// de mise à jour d'une dispo
?>
function prepareAjaxRequest(oThis, sDispo)
{
	var aArray = infosFromId(oThis.id);
	var sRequest = "";
	var aRequest = new Array();
	aRequest['oldDispo'] = escape($(oThis).text());
	aRequest['dispo'] = escape(sDispo);
	aRequest['uid'] = escape(aArray['uid']);
	aRequest['Year'] = escape(aArray["Year"]);
	aRequest["Month"] = escape(aArray["Month"]);
	aRequest["Day"] = escape(aArray["Day"]);
	var compteur = 0;
	for (var cle in aRequest)
	{
		sRequest += cle+"="+aRequest[cle];
		sRequest += '&';
	}
	sRequest = sRequest.substr(0, sRequest.length-1);
	debug(sRequest);
	return sRequest;
}
<?
	// Envoie le formulaire de remplacement
?>
function remplaUpdate() {
	var sRequest = "";
	var aRequest = new Array();
	aRequest['uid'] = $('#remplaUid').val();
	aRequest['Year'] = $('#remplaYear').val();
	aRequest['Month'] = $('#remplaMonth').val();
	aRequest['Day'] = $('#remplaDay').val();
	aRequest['nom'] = $('#remplaNom').val();
	aRequest['phone'] = $('#remplaPhone').val();
	aRequest['email'] = $('#remplaEmail').val();
	for (var cle in aRequest)
	{
		sRequest += cle+"="+aRequest[cle];
		sRequest += '&';
	}
	sRequest = sRequest.substr(0, sRequest.length-1);
	var dest = $('#fFormRemplacement').attr('action');
	submitRequest(sRequest, dest);

}
$(function() {
	$('#fFormRemplacement').submit(function(event) {
		$('#dFormRemplacement').hide('slow');
		remplaUpdate();
		event.preventDefault();
	});
});
<?
// permet de retrouver les infos relatives à l'élément dont l'id est passé en paramètre
// retourne une Array avec tous ces éléments
?>
function infosFromId(sId) {
	var aId = sId.match(/u(\d+)a(\d+)m(\d+)j(\d+)s(\w+)c(\d+)/);
	if (aId instanceof Array) {
		var aArray = new Array();
		aArray["uid"] = aId[1];
		aArray["Year"] = aId[2];
		aArray["Month"] = aId[3];
		aArray["Day"] = aId[4];
		aArray["Vacation"] = aId[5];
		aArray["cycleId"] = aId[6];
		aArray["isFerie"] = $("#a"+aArray["Year"]+"m"+aArray["Month"]+"j"+aArray["Day"]+"s"+aArray["Vacation"]).hasClass('ferie');
		return aArray;
	}
	else
	{
		aId = sId.match(/decsepA(\d+)M(\d+)J(\d+)/);
		if (aId instanceof Array) {
			var aArray = new Array();
			aArray["Year"] = aId[1];
			aArray["Month"] = aId[2];
			aArray["Day"] = aId[3];
			return aArray;
		}
		else
		{
			aId = sId.match(/locka(\d+)m(\d+)j(\d+)c(\d+)/);
			if (aId instanceof Array) {
				var aArray = new Array();
				aArray["Year"] = aId[1];
				aArray["Month"] = aId[2];
				aArray["Day"] = aId[3];
				aArray["cycleId"] = aId[4];
				return aArray;
			}
			else
			{
				aId = sId.match(/deca(\d+)m(\d+)j(\d+)s(.+)c(\d+)/);
				if (aId instanceof Array) {
					var aArray = new Array();
					aArray["Year"] = aId[1];
					aArray["Month"] = aId[2];
					aArray["Day"] = aId[3];
					aArray["Vacation"] = aId[4];
					aArray["cycleId"] = aId[5];
					return aArray;
				}
				else
				{
					return false;
				}
			}
		}
	}
}
<?
// Retourne un tableau avec les uid présents sur le tableau
?>
function listeUid() {
	var aClasses = new Array('cds', 'ce', 'pc', 'c', 'dtch', 'fmp');
	var aEffectif = new Array();
	var aClass = new Array();
	for (var iClass in aClasses)
	{
		aClass = $('.'+aClasses[iClass]);
		for (var iCpt=0; iCpt<aClass.length; iCpt++)
		{
			var sId = aClass[iCpt].id;
			var aId = sId.match(/u(\d+)$/);
			if (aId instanceof Array)
			{
				aEffectif[aId[1]] = aClasses[iClass];
			}
			else
			{
				debug('Une erreur s\'est produite dans le décompte à '+sId);
			}
		}
	}
	return aEffectif;
}
<?
//***********************
// Fonctions de debugage
//***********************
?>
function debug(sMsg) {
	var oDate = new Date();
	var sTime = (oDate.getHours()>9) ? "" : "0";
	sTime += oDate.getHours()+":";
	sTime += (oDate.getMinutes()>9) ? "" : "0";
	sTime += oDate.getMinutes()+":";
	sTime += (oDate.getSeconds()>9) ? "" : "0";
	sTime += oDate.getSeconds();
	var sMtime = oDate.getTime();
	var sString = "<dt class=\""+sMtime+"\">"+sTime+":</dt><dd class=\""+sMtime+"\">&quot;"+sMsg+"&quot;<br /></dd>"
	$("#debugListe").prepend(sString);
	if (!$("#debugMessages").hasClass("redBorder")) {
		$("#debugMessages").addClass("redBorder");
	}
	$("#debugMessages").show();
	$("#debugListe").show("slow");
	$("."+sMtime).dblclick(function() {
			$("."+sMtime).remove();
			});
	$("#debugMessages a").toggle(function(event) {
			$("#debugListe").hide("slow");
			$(this).text('Show');
			$("#debugMessages").removeClass("redBorder");
			event.preventDefault();
			},function() {
			$("#debugMessages").addClass("redBorder");
			$("#debugListe").show("slow");
			$(this).text('Hide');
			event.preventDefault();
			});
}
function initDebug() {
	var sString = '<dl id="debugListe"></dl>';
	$("#debugMessages").append(sString);
	$("#debugListe").hide();
	$("#debugMessages").hide();
	$("#debugMessages").addClass("redBorder");
	$("#debugMessages").append("<button onclick='comptePresents();'>listeUid()</button>");
}
<?
//****************
// Initialisation
//****************
?>
$(function() {
		initDebug();
		$('#dFormRemplacement').hide();
			$(<?if (!empty($_SESSION['TEAMEDIT'])) {
				print "'.presence'";
			} else {
				printf ("'.presence[id*=u%sa]'", $_SESSION['utilisateur']->uid()); // Les utilisateurs sans droits étendus ne peuvent modifier que leur ligne
			}?>).toggle(function() {
<?			// l'élément cliqué est mis en valeur ?>
			$(this).addClass("emphasize");
<?			// La première case (contenant le nom) est mise en évidence ?>
			var oParent = $(this).parent();
			var oChildren = $(oParent[0]).children();
			$(oChildren[0]).addClass("emphasize");
<?			// Recherche des informations de l'élément afin de déterminer la tête de colonne correspondante ?>
			var aArray = infosFromId(this.id);
			var sVacationId = "#a"+aArray["Year"]+"m"+aArray["Month"]+"j"+aArray["Day"]+"s"+aArray["Vacation"];
<?			// la tête de colonne est mise en évidence ?>
			$(sVacationId).addClass("emphasize");
<?			// Recherche des possibles occupations pour remplir la boîte de choix ?>
			var sString = getAvailableOccupations(this);
			$("#sandbox").replaceWith(sString);
<?			// Placement de la boîte de choix ?>
			var p = $(this).position();
			$("#sandbox").css({"left" : p.left + 10 , "top" : p.top + 20});
			$("#sandbox").show('slow');
<?			// Action du clic dans la boîte de choix ?>
			var oThis = this;
			$("#sandbox li").click(function() {
				$("#sandbox").hide('slow');
				addDispo(oThis, $(this).text());
				$("td.nom").removeClass("emphasize");
				$(sVacationId).removeClass("emphasize");
			});
			},function() {
				$(this).removeClass("emphasize");
				$("#sandbox").hide();
				$("td.nom").removeClass("emphasize");
<?				// Recherche la tête de colonne correspondante ?>
				var aArray = infosFromId(this.id);
				var sVacationId = "#a"+aArray["Year"]+"m"+aArray["Month"]+"j"+aArray["Day"]+"s"+aArray["Vacation"];
<?				// mise en évidence de la tête de colonne supprimée ?>
				$(sVacationId).removeClass("emphasize");
			});
<?		/* Supprime le menu pour modifier les dispo sur les cases protected */
?>		$('.protected').unbind();
<?		/* Permet de n'afficher qu'une seule ligne en cliquant sur le nom */
?>		$(".nom").toggle(function() {
				var sId = "#" + this.id;
				collapseRow(sId);
				},function() {
				var sId = "#" + this.id;
				uncollapseRow(sId);
				});
		decomptePresents();
});
<?
	// Gestion de la configuration des cycles (E/W)
	// TODO interdire l'édition lorsque la grille n'est pas éditable.
	if (!empty($_SESSION['TEAMEDIT'])) {
?>
$(function() {
	$('.conf').click(function() {
		var a = {'W':'E','E':'W'};
		var id = this.id;
		var sreq = 'id='+id+'&conf='+a[$(this).text()];
		submitRequest(sreq, 'ajax.php');
		$(this).contents().replaceWith(a[$(this).text()]);
	});
});
<?
	}

//*******************
// Fonctions annexes
//*******************
// Masque les rangs nom sauf l'id sId
?>
function collapseRow(sId) {
	var oParent = $(sId).parent();
	var sRow = "#"+oParent[0].id;
	var aSiblings = $(sRow).siblings();
	var sTest = "";
	for (var i=0; i<aSiblings.length - 1; i++) {
		var sTempId = "#" + aSiblings[i].id;
		if (sId != sTempId) {
			$(sTempId).hide();
		}
	}
}
<?
// Montre le rang qui contient l'id sId
?>
function uncollapseRow(sId) {
	var oParent = $(sId).parent();
	var sRow = oParent[0];
	var aSiblings = $(sRow).siblings();
	for (var i=0; i<aSiblings.length; i++) {
		var sTempId = "#" + aSiblings[i].id;
		if (sId != sTempId) {
			$(sTempId).show();
		}
	}
}

<?
ob_end_flush();
?>
