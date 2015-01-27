{* Smarty *}
<form id="fPereq" class="ng" method="POST" action="">
<fieldset>
<legend>Ajout de péréquations</legend>

<ul>
<li><label for="uid">Nom</label>
<select name="uid" id='sCcnom' onchange="">
{foreach $users as $user}
<option value="{$user->uid()}">{$user->nom()}</option>
{/foreach}
</select></li>
<li>{include file="html.form.select.tpl" select=$dispos}
</li>
<li><label for="dateD">Date</label>
<input type="date" name="dateD" id="dateD" placeholder="jj-mm-aaaa" value="{date('d-m-Y')}"/>
</li>
<li>{include file="html.form.select.tpl" select=$years}
</li>
<li><label for="nb">Nombre</label>
<input name="nb" />
</li>
</ul>
<fieldset>
<button type="submit" class="bouton">Mettre à jour</button>
</fieldset>
</fieldset>

</form>
