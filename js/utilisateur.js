function display(id) {
	$('#'+id).show('slow');
}
function newPhone() {
	alert("Attention : vous ne pouvez ajouter qu'un téléphone à la fois");

	var objFieldset = document.createElement('fieldset');
	var objLegend = document.createElement('legend');
	var objTextLegend = document.createTextNode('Nouveau téléphone');
	objLegend.appendChild(objTextLegend);
	objFieldset.appendChild(objLegend);


	var objDivRow1 = document.createElement('div');
	objDivRow1.setAttribute('class', 'row');
	var objDivCell1 = document.createElement('div');
	objDivCell1.setAttribute('class', 'cell');
	var objDivCell2 = document.createElement('div');
	objDivCell2.setAttribute('class', 'cell');
	
	var objDivRow2 = document.createElement('div');
	objDivRow2.setAttribute('class', 'row');
	var objDivCell3 = document.createElement('div');
	objDivCell3.setAttribute('class', 'cell');
	var objDivCell4 = document.createElement('div');
	objDivCell4.setAttribute('class', 'cell');
	var objDivRow3 = document.createElement('div');
	objDivRow3.setAttribute('class', 'row');
	var objDivCell5 = document.createElement('div');
	objDivCell5.setAttribute('class', 'cell');
	var objDivCell6 = document.createElement('div');
	objDivCell6.setAttribute('class', 'cell');
	
	var objLabelNb = document.createElement('label');
	var objTextLabelNb  = document.createTextNode('Numéro : ');
	objLabelNb.setAttribute('for', 'newnb');
	objLabelNb.setAttribute('class', 'cell');
	objLabelNb.appendChild(objTextLabelNb);

	var objInputNb = document.createElement('input');
	objInputNb.setAttribute('name', 'newnb');
	objInputNb.setAttribute('type', 'tel');
	objInputNb.setAttribute('pattern', '\\d\\d\\d\\d\\d\\d\\d\\d\\d\\d');
	objInputNb.setAttribute('placeholder', '0011223344');
	objInputNb.setAttribute('class', 'cell');
	

	var objLabelDesc = document.createElement('label');
	var objTextLabelDesc  = document.createTextNode('Description : ');
	objLabelDesc.setAttribute('for', 'newdesc');
	objLabelDesc.setAttribute('class', 'cell');
	objLabelDesc.appendChild(objTextLabelDesc);
	
	var objInputDesc = document.createElement('input');
	objInputDesc.setAttribute('name', 'newdesc');
	objInputDesc.setAttribute('type', 'text');
	objInputDesc.setAttribute('class', 'cell');
	objInputDesc.setAttribute('placeholder', 'maison');
	

	var objLabelPal = document.createElement('label');
	var objTextLabelPal  = document.createTextNode('Principal : ');
	objLabelPal.setAttribute('for', 'newpal');
	objLabelPal.setAttribute('class', 'cell');
	objLabelPal.appendChild(objTextLabelPal);
	
	var objInputPal = document.createElement('input');
	objInputPal.setAttribute('name', 'newpal');
	objInputPal.setAttribute('type', 'checkbox');
	objInputPal.setAttribute('class', 'cell');

	objDivCell1.appendChild(objLabelNb);
	objDivCell2.appendChild(objInputNb);
	objDivCell3.appendChild(objLabelDesc);
	objDivCell4.appendChild(objInputDesc);
	objDivCell5.appendChild(objLabelPal);
	objDivCell6.appendChild(objInputPal);

	objDivRow1.appendChild(objDivCell1);
	objDivRow1.appendChild(objDivCell2);
	objDivRow2.appendChild(objDivCell3);
	objDivRow2.appendChild(objDivCell4);
	objDivRow3.appendChild(objDivCell5);
	objDivRow3.appendChild(objDivCell6);

	objFieldset.appendChild(objDivRow1);
	objFieldset.appendChild(objDivRow2);
	objFieldset.appendChild(objDivRow3);
	$('#dAddPhone').parent().before(objFieldset);
}
function newAddress() {
	alert("Attention : vous ne pouvez ajouter qu'une adresse à la fois");
	
	var objFieldset = document.createElement('fieldset');
	var objLegend = document.createElement('legend');
	var objTextLegend = document.createTextNode('Nouvelle adresse');
	objLegend.appendChild(objTextLegend);
	objFieldset.appendChild(objLegend);


	var objDivRow1 = document.createElement('div');
	objDivRow1.setAttribute('class', 'row');
	var objDivCell1 = document.createElement('div');
	objDivCell1.setAttribute('class', 'cell');
	var objDivCell2 = document.createElement('div');
	objDivCell2.setAttribute('class', 'cell');
	
	var objDivRow2 = document.createElement('div');
	objDivRow2.setAttribute('class', 'row');
	var objDivCell3 = document.createElement('div');
	objDivCell3.setAttribute('class', 'cell');
	var objDivCell4 = document.createElement('div');
	objDivCell4.setAttribute('class', 'cell');
	var objDivRow3 = document.createElement('div');
	objDivRow3.setAttribute('class', 'row');
	var objDivCell5 = document.createElement('div');
	objDivCell5.setAttribute('class', 'cell');
	var objDivCell6 = document.createElement('div');
	objDivCell6.setAttribute('class', 'cell');
	
	var objLabelNb = document.createElement('label');
	var objTextLabelNb  = document.createTextNode('Adresse : ');
	objLabelNb.setAttribute('for', 'newadresse');
	objLabelNb.setAttribute('class', 'cell');
	objLabelNb.appendChild(objTextLabelNb);

	var objInputNb = document.createElement('textarea');
	objInputNb.setAttribute('name', 'newadresse');
	objInputNb.setAttribute('class', 'cell');
	

	var objLabelDesc = document.createElement('label');
	var objTextLabelDesc  = document.createTextNode('Code postal : ');
	objLabelDesc.setAttribute('for', 'newcp');
	objLabelDesc.setAttribute('class', 'cell');
	objLabelDesc.appendChild(objTextLabelDesc);
	
	var objInputDesc = document.createElement('input');
	objInputDesc.setAttribute('name', 'newcp');
	objInputDesc.setAttribute('type', 'text');
	objInputDesc.setAttribute('class', 'cell');
	

	var objLabelPal = document.createElement('label');
	var objTextLabelPal  = document.createTextNode('Ville : ');
	objLabelPal.setAttribute('for', 'newville');
	objLabelPal.setAttribute('class', 'cell');
	objLabelPal.appendChild(objTextLabelPal);
	
	var objInputPal = document.createElement('input');
	objInputPal.setAttribute('name', 'newville');
	objInputPal.setAttribute('type', 'text');
	objInputPal.setAttribute('class', 'cell');

	objDivCell1.appendChild(objLabelNb);
	objDivCell2.appendChild(objInputNb);
	objDivCell3.appendChild(objLabelDesc);
	objDivCell4.appendChild(objInputDesc);
	objDivCell5.appendChild(objLabelPal);
	objDivCell6.appendChild(objInputPal);

	objDivRow1.appendChild(objDivCell1);
	objDivRow1.appendChild(objDivCell2);
	objDivRow2.appendChild(objDivCell3);
	objDivRow2.appendChild(objDivCell4);
	objDivRow3.appendChild(objDivCell5);
	objDivRow3.appendChild(objDivCell6);

	objFieldset.appendChild(objDivRow1);
	objFieldset.appendChild(objDivRow2);
	objFieldset.appendChild(objDivRow3);
	$('#dAddAddress').parent().before(objFieldset);
}
