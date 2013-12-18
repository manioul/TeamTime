{* Smarty *}
<form id="fPereq" class="ng" method="POST" action="">
<fieldset>
<legend>Péréquations</legend>

<ul>
<li><label for="uid">Nom</label>
<select name="uid" id='sCcnom' onchange="">
{foreach $users as $user}
<option value="{$user->uid()}">{$user->nom()}</option>
{/foreach}
</select></li>
<li><label for="did">Dispo</label>
<select name="did" id='sCdispo' onchange="">
{foreach $dispos as $dispo}
<option value="{$dispo@key}">{$dispo}</option>
{/foreach}
</select></li>
<li><label for="date">Date</label>
<input type="date" name="date" placeholder="jj-mm-aaaa" />
</li>
<li><label for="year">Année</label>
<select name="year" id='sCyear' onchange="">
{foreach $years as $year}
<option value="{$year}">{$year}</option>
{/foreach}
</select></li>
<li><label for="nb">Nombre</label>
<input name="nb" />
</li>
<li><label for="suppr">Supprimer</label>
<input type="checkbox" name="suppr" />
</li>
</ul>
<fieldset>
<button type="submit" class="bouton">Mettre à jour</button>
</fieldset>
</fieldset>

</form>
<div id="helpPereq" class="help">
</div>
