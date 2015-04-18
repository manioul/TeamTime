{* Smarty *}
{* template d'affichage et de modification des informations d'un utilisateur *}
<div id="monCompte">
<form name="fContact" id="fContact" class="ng" method="POST" action="ajoutUtilisateur.php">
<fieldset name=""><legend>Compte utilisateur</legend>
{include file="searchUser.tpl"}
<li id="lCredentials" style="display:none;">
{include file="credentials.tpl"}
</li>
</ul>
</fieldset>

<input type="submit" class="bouton" id="submitContact1" style="display:none;" value="Mettre à jour" />

{if $smarty.session.ADMIN}
<fieldset id="administration" style="display:none;"><legend>Administration</legend>
<ul>
<li>
{include file="html.form.select.tpl" select=$availablePages}
</li>
<li>
<label for="locked">locked : </label><input type="checkbox" id="locked" name="locked" />
</li>
<li>
<label for="actif">actif : </label><input type="checkbox" id="actif" name="actif" checked="checked" />
</li>
<li>
<label for="totd">tip of the day : </label><input type="checkbox" id="totd" name="totd" />
</li>
<li>
<label for="sendmail" title="Informe l'utilisateur sur le moyen de se connecter à TeamTime">Envoyer un mail</label><input type="checkbox" checked="checked" name="sendmail" />
</li>
</ul>
</fieldset>

<input type="submit" class="bouton" id="submitContact2" style="display:none;" value="Mettre à jour" />
{/if}

</form>

{include file="carriere_et_affectation.tpl"}

</div><!-- Fin div #monCompte -->

