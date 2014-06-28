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
