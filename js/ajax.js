// ajax.js
// 
// Script de gestion des instances et requêtes ajax

function createInstance()
{
	var req = null;
	if (window.XMLHttpRequest)
	{
		req = new XMLHttpRequest();
	} 
	else if (window.ActiveXObject) 
	{
		try {
			req = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e)
		{
			try {
				req = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) 
			{
				alert("XHR not created");
			}
		}
	}
	return req;
};

function submitRequest(sRequest, dest)
{ 
	var req =  createInstance();

	req.onreadystatechange = function()
	{ 
		if(req.readyState == 4)
		{
			if(req.status == 200)
			{
				attention(req.responseText);	
			}	
			else	
			{
				alert("Error: returned status code " + req.status + " " + req.statusText);
			}	
		} 
	}; 

	req.open("POST", dest, true); 
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	req.send(sRequest);
} 
// Affiche un message à l'écran
function attention(sMessage)
{
	//alert(sMessage);
	$('#info').text(sMessage);
	$('#info').fadeIn('slow', function() {$(this).fadeOut(sMessage.length*100);});
}
