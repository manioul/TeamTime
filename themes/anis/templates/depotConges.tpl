{* Smarty *}
<div class="ng w24">
<form id='fDepotCong' method='POST' action='conges.php'>
<label for='datePicker'>Éditer les titres de congés jusqu'au</label>
<input type="date" name='datePicker' id='datePicker' />
<input type='submit' />
</form>
{if isset($annulation)}<form method="POST" action="annulationConges.php"><input type="submit" value="Annulation congés" /></form>{/if}
</div>
