{* Smarty *}
<fieldset><legend>Credentials</legend>
<ul>
<li>
<label for="login">login</label><input type="text" name="login" id="login"{if is_object($utilisateur)} value="{$utilisateur->login()}"{/if} />
</li>
{if $smarty.session.ADMIN}
<li>
<label for="password">mot de passe</label><input type="password" name="password" id="password" />
</li>
{/if}
</ul>
</fieldset>
