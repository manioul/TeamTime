function display(id)
{
	$('#'+id).show('slow');
}
function newPhone()
{
	var objFieldset = document.createElement('fieldset');
	var objLegend = document.createElement('legend');
	var objTextLegend = document.createTextNode('Nouveau téléphone');
	objLegend.appendChild(objTextLegend);
	objFieldset.appendChild(objLegend);

	var objUl = document.createElement('ul');
	var objLiNb = document.createElement('li');
	var objLiDesc = document.createElement('li');
	var objLiPal = document.createElement('li');

	var objLabelNb = document.createElement('label');
	var objTextLabelNb  = document.createTextNode('Numéro : ');
	objLabelNb.setAttribute('for', 'newnb');
	objLabelNb.appendChild(objTextLabelNb);

	var objInputNb = document.createElement('input');
	objInputNb.setAttribute('name', 'newnb');
	objInputNb.setAttribute('type', 'tel');
	objInputNb.setAttribute('pattern', '\\d\\d\\d\\d\\d\\d\\d\\d\\d\\d');
	objInputNb.setAttribute('placeholder', '0011223344');
	
	objLiNb.appendChild(objLabelNb);
	objLiNb.appendChild(objInputNb);


	var objLabelDesc = document.createElement('label');
	var objTextLabelDesc  = document.createTextNode('Description : ');
	objLabelDesc.setAttribute('for', 'newdesc');
	objLabelDesc.appendChild(objTextLabelDesc);
	
	var objInputDesc = document.createElement('input');
	objInputDesc.setAttribute('name', 'newdesc');
	objInputDesc.setAttribute('type', 'text');
	objInputDesc.setAttribute('placeholder', 'maison');
	
	objLiDesc.appendChild(objLabelDesc);
	objLiDesc.appendChild(objInputDesc);
	

	var objLabelPal = document.createElement('label');
	var objTextLabelPal  = document.createTextNode('Principal : ');
	objLabelPal.setAttribute('for', 'newpal');
	objLabelPal.appendChild(objTextLabelPal);
	
	var objInputPal = document.createElement('input');
	objInputPal.setAttribute('name', 'newpal');
	objInputPal.setAttribute('type', 'checkbox');
	
	objLiPal.appendChild(objLabelPal);
	objLiPal.appendChild(objInputPal);


	objUl.appendChild(objLiNb);
	objUl.appendChild(objLiDesc);
	objUl.appendChild(objLiPal);

	objFieldset.appendChild(objUl);

	$('#iAddPhone').parent().before(objFieldset);
	$('#iAddPhone').parent().hide();
}
function newAddress()
{
	var objFieldset = document.createElement('fieldset');
	var objLegend = document.createElement('legend');
	var objTextLegend = document.createTextNode('Nouvelle adresse');
	objLegend.appendChild(objTextLegend);
	objFieldset.appendChild(objLegend);

	var objUl = document.createElement('ul');
	var objLiNb = document.createElement('li');
	var objLiDesc = document.createElement('li');
	var objLiPal = document.createElement('li');

	var objLabelNb = document.createElement('label');
	var objTextLabelNb  = document.createTextNode('Adresse : ');
	objLabelNb.setAttribute('for', 'newadresse');
	objLabelNb.appendChild(objTextLabelNb);

	var objInputNb = document.createElement('textarea');
	objInputNb.setAttribute('name', 'newadresse');
	
	objLiNb.appendChild(objLabelNb);
	objLiNb.appendChild(objInputNb);
	

	var objLabelDesc = document.createElement('label');
	var objTextLabelDesc  = document.createTextNode('Code postal : ');
	objLabelDesc.setAttribute('for', 'newcp');
	objLabelDesc.appendChild(objTextLabelDesc);
	
	var objInputDesc = document.createElement('input');
	objInputDesc.setAttribute('name', 'newcp');
	objInputDesc.setAttribute('type', 'text');
	
	objLiDesc.appendChild(objLabelDesc);
	objLiDesc.appendChild(objInputDesc);
	

	var objLabelPal = document.createElement('label');
	var objTextLabelPal  = document.createTextNode('Ville : ');
	objLabelPal.setAttribute('for', 'newville');
	objLabelPal.appendChild(objTextLabelPal);
	
	var objInputPal = document.createElement('input');
	objInputPal.setAttribute('name', 'newville');
	objInputPal.setAttribute('type', 'text');
	
	objLiPal.appendChild(objLabelPal);
	objLiPal.appendChild(objInputPal);

	
	objLiPal.appendChild(objLabelPal);
	objLiPal.appendChild(objInputPal);


	objUl.appendChild(objLiNb);
	objUl.appendChild(objLiDesc);
	objUl.appendChild(objLiPal);

	objFieldset.appendChild(objUl);

	$('#iAddAddress').parent().before(objFieldset);
	$('#iAddAddress').parent().hide();
}
function supprInfo(q, id, uid)
{
	var sRequest = "q="+q+"&id="+id+"&uid="+uid;
	submitRequest(sRequest, 'suppress.php');
	$('#'+q+id).hide('slow');
	return false;
}
/*
 * Cette fonction est appelée par des onsubmit de formulaire
 * elle soumet le formulaire via une requête ajax
 * et cache le formulaire après soumission
 * si un champ du formulaire s'appellant cachemoi est égal à 1
 *
 * Le formulaire à traiter est passé en argument (this)
 * La destination du formulaire est utilisée comme destination de la requête ajax
 */
function subAutoForm(form) {
	// Traitement des cas particuliers... Hum... :s
	if (form.name = "") {
	}
	var sAjaxRequest = "";
	var hideme = false;
	for (i=0; i<form.length; i++)
	{
		if (form[i].type != 'submit' && form[i].type != 'fieldset') { // No need to send submit's value
			if (form[i].name == 'noaj') {
				// Si le formulaire comporte un input noaj (noAjax) le formulaire n'est pas soumis via ajax (par exemple fConn).
				return true;
			}
			else if (form[i].name == 'cachemoi' && form[i].value == 1) {
				hideme = true;
			} else {
				sAjaxRequest += form[i].name+"="+form[i].value+"&";
			}
		}
	}
	submitRequest(sAjaxRequest.slice(0,-1), form.action);
	if (hideme == true) {
		$(form).hide('slow');
	}
	return false;
}
/*
 * Returns true if the content of both fields'content
 * whose ids are given are equal
 */
function checkSameContent(sId1, sId2)
{
	return $('#'+sId1).value() == $('#'+sId2).value();
}
function checkPwdComplexity(sPwd)
{
}
function checkLoginExists(sLogin)
{
}
function validateCrtAcct() {
	if (!checkSameContent('pwd', 'pwdchk')) {
		alert("Les mots de passe ne correspondent pas...")
		return false;
	}
	return true;
}
/*
 * Ajoute de l'info à des champs dont l'id peut être recréé à partir de la date et l'uid
 * par exemple dans des cases de la grille
 */
function addTextToGrille(aArray,info)
{
	var sId = 'u'+aArray["uid"]+'a'+aArray["Year"]+'m'+aArray["Month"]+'j'+aArray["Day"];
	$("<p>"+info+"</p>").appendTo($("*[id*="+sId+"]"));
}
/*
 * Affiche les heures attribuées aux utilisateurs sur chaque jour de travail
 */
function addHeures()
{
	$("th[id^='a']").each(
			function()
			{
				var aArray = infosFromId($(this).attr("id"));
				if (aArray instanceof Array) {
					$.post('ajax.php', {q:"GH",d:aArray['Day'],m:aArray['Month'],y:aArray['Year']}).done(function(x){
						var aArr = JSON.parse(x);
						for (var i in aArr)
					{
						var theDate = new Date(aArr[i]["date"]);
						var month = theDate.getMonth() + 1;
						var sId = 'u'+aArr[i]["uid"]+'a'+theDate.getFullYear()+'m'+month+'j'+theDate.getDate()+"s";
						$("<ul class='heures' title='"+aArr[i]['date']+"|"+aArr[i]["uid"]+"'><li>"+aArr[i]['normales']+"</li><li>"+aArr[i]['instruction']+"</li><li>"+aArr[i]['simulateur']+"</li><li>"+aArr[i]['double']+"</li></ul>").appendTo($("td[id^="+sId+"]"));
					}
					});
				}
			}
			);
}
$(function() {
	$("form").has("input[name='ajax'][value='true']").attr('onsubmit', 'return subAutoForm(this)');
	// Formulaire de création de compte TeamTime ou récupération de mot de passe fcrtAcct
	$("#fcrtAcct").attr('onsubmit', 'return validateCrtAcct()');
	// Crée un champ hidden nommé w et de valeur le nom du formulaire
	$("form").prepend("<input type='hidden' name='w' value='" + this.name + "' />");
});
