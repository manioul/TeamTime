{* Smarty *}
<div class="ng w24">
<h1>Validation des utilisateurs</h1>
{if sizeof($users) > 0}
<ul>
{/if}
{foreach $users as $user}
<li id="cf{$user.id}">
<ul>
<form name="user{$user.id}" action="post.php" method="POST">
<li>
{$user.prenom|capitalize} {$user.nom|capitalize} vient-il dans votre équipe ?
</li><li>
<input type="hidden" name="id" value="{$user.id}" />
<label for="dateD">À partir du </label><input type="text" name="dateD" value="{$user.dateD|date_format:"%d-%m-%Y"}" />
<label for="dateF">jusqu'au </label><input type="text" name="dateF" value="{$user.dateF|date_format:"%d-%m-%Y"}" />
{include file='html.form.select.tpl' select=$grades}
{include file='html.form.select.tpl' select=$classes}
<input type="submit" name="infirm" value="Utilisateur inconnu" />
<input type="submit" name="confirm" value="Confirmer le compte" />
</li>
</form>
</ul>
</li>
{/foreach}
{if sizeof($users) > 0}
</ul>
{/if}
</div>
