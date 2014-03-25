{* Smarty *}
<div id="dDatePicker">
<form id='fDepotCong' method='post' action='conges.php'>
<label for='datePicker'>Éditer les titres de congés jusqu'au</label>
<input type="text" name='datePicker' id='datePicker' />
<input type='submit' />
</form>
{if isset($annulation)}<div class="bouton"><a href="annulationConges.php">Annulation congés</a></div>{/if}
</div>
