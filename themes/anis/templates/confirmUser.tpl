{* Smarty *}
<div class="panel">
<h1>Validation des utilisateurs</h1>
{if sizeof($users) > 0}
<p>Les utilisateurs suivant ont demandé à créer un compte :</p>
<ul>
{foreach $users as $user}
<li id="cf{$user.id}">
<ul class="ng w24">
<form name="user{$user.id}" action="ajax.php" method="POST">
<li>
{$user.prenom|capitalize} <strong>{$user.nom|capitalize}</strong> ({$user.email})
vient-il dans votre équipe ?
</li><li>
<input type="hidden" name="form" value="CU" />
<input type="hidden" name="ajax" value="true" />
<input type="hidden" name="cachemoi" value="1" />
<input type="hidden" name="submit" value="confirm" id="subValue" />
<input type="hidden" name="id" value="{$user.id}" />
<label for="dateD">À partir du </label><input type="date" name="dateD" value="{$user.dateD|date_format:"%d-%m-%Y"}" />
</li><li>
<label for="dateF">jusqu'au </label><input type="date" name="dateF" value="{$user.dateF|date_format:"%d-%m-%Y"}" />
</li><li>
{include file='html.form.select.tpl' select=$grades}
<input type="submit" name="inf" value="Utilisateur inconnu" onclick="$('#subValue').val('infirm')" />
<input type="submit" name="conf" value="Confirmer le compte" />
</li>
</form>
</ul>
</li>
{/foreach}
</ul>
{/if}
</div>
