{* Smarty *}
<div>
<form name="fCc" method="post" action="" onsubmit="">
<table class="genElem">
<tr>
<td><label for="uid">Nom</label></td>
<td><select name="uid" id='sCcnom' onchange="">
{foreach $users as $user}
<option value="{$user->uid()}">{$user->nom()}</option>
{/foreach}
</select>
</td>
</tr><tr>
<td><label for="did">Dispo</label></td>
<td><select name="did" id='sCcnom' onchange="">
{foreach $dispos as $dispo}
<option value="{$dispo@key}">{$dispo}</option>
{/foreach}
</select>
</td>
</tr><tr>
<td><label for="dateD">Date de début des {$titre.intitule}</label></td>
<td><input type="text" name="dateD" id="dateD" /></td>
</tr><tr>
<td><label for="dateF">Date de fin des {$titre.intitule}</label></td>
<td><input type="text" name="dateF" id="dateF" /></td>
</tr><tr>
<td colspan="2"><input type="submit" value="Envoyer" /></td>
</tr>
</table>
</form>
</div>
