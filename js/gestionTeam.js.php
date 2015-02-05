<?php
/*
 * gestionTeam.js.php
 */

// Require authenticated user
// L'utilisateur doit être logué/admin pour accéder à cette page
// $requireAuthenticatedUser = true;
$requireEditeur = true;

header('Content-Type: application/javascript');
//ob_start();
$conf['page']['elements']['firePHP'] = true;

require_once('../firePHP.inc.php');
require_once('../classes/class_debug.inc.php');
require_once('../config.inc.php');
require_once('../init.inc.php');
require_once('../constantes.inc.php');
require_once('../globals_db.inc.php');
require_once('../classes/class_db.inc.php');
require_once('../session.inc.php');

?>
// Ajouter une valeur d'un champ à un autre champ
function displayVals(v)
{
	var mul = $('#'+v).val() || [];
	$('#'+v+'p').val(mul.join(","));
}
// Formulaire d'ajout d'activité
// masquage et affichage des champs pour les compteurs
$(function() {
	$('#namCpt').hide();
	$('#needCpt').on("change", (function() {
		if (this.checked == false) {
			elem = document.getElementById('dp');
			elem.value = "";
			$('#namCpt').hide();
		} else {
			$('#namCpt').show();
		}
	}));
	$('#isd').on("change", (function() {
		if (this.checked == true) {
			elem = document.getElementById('needCpt');
			elem.checked = false;
			elem = document.getElementById('dp');
			elem.value = "";
			$('#namCpt').hide();
			$('#neeCpt').hide();
		} else {
			$('#neeCpt').show();
		}
	}));
	$('#compteur').on("change", (function() {
		$('#dp').val(this.value);
	}));
	$('#dp').on('focus', (function() {
		this.value = "";
	}));
});

