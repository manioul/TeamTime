<?php
/*
 * tableauCong.js.php
 *
 * Aide à la gestion des tableaux de congés
 */
// Require authenticated user
// L'utilisateur doit être logué pour accéder à cette page
$requireAuthenticatedUser = true;

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
function hideTableaux()
{
	$('.tabCong').hide();
}
function showTableau(sId)
{
	$('#'+sId).show();
}
function desemphasize()
{
	$('li[id^="chcong"]').removeClass('selected');
}
$(function() {
		hideTableaux();
		// On retire le lien du menu pour le gérer avec jquery
		$('li[id^="chcong"]').children('a').replaceWith(function() { return $(this).contents(); });
		$('li[id^="chcong"]').click(function() {
			var aId = this.id.match(/chcong(\d+)/);
			hideTableaux();
			desemphasize();
			$(this).addClass('selected');
			$('#d_'+aId[1]).show();
			});
		// On retire les liens sur les dates de congés dans les tableaux pour les gérer avec jquery
		$('td.date').children('a').replaceWith(function() { return $(this).contents(); });
		<?php
		if (!empty($_SESSION['EDITEURS'])) { ?>
		$('#datePicker').datepicker($.datepicker.regional['fr']);
		<? } ?>
		<?php
		if (!empty($_SESSION['EDITEURS'])) { ?>
		$('td.filed').addClass('pointer');
		$('td.filed').click(function() {
			var type;
			if ($(this).hasClass('filed'))
			{
				$(this).addClass('confirmed');
				$(this).removeClass('pointer');
			}
			var sRequest = 'f=2&id='+this.id;
			submitRequest(sRequest,'updateCong.php');
			});
		<? } ?>
});
