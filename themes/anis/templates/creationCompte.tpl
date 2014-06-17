{* Smarty *}
<div>
<form name="fCc" method="post" action="" onsubmit="subCc()">
<table class="genElem">
<tr>
<td><label for="sCcnom">nom</label></td>
<td><select name="nom" id='sCcnom' onchange="updDispFormCc()">
{section name=i loop=$infos}
<option value="{$infos[i]}">{$infos[i]}</option>
{/section}
</select>
</td>
</tr><tr>
<td><label for="iCclogin">login</label></td><td><input type="text" name="login" id="iCclogin" /></td>
</tr><tr>
<td><label for="iCcpassword">mot de passe</label></td><td><input type="text" name="password" id="iCcpassword" /></td>
</tr><tr>
<td><label for="iCcemail">adresse mail</label></td><td><input type="text" name="email" id="iCcemail" /></td>
</tr><tr>
<td><label for="sendmail">Envoyer un mail</label><input type="checkbox" checked="checked" name="sendmail" id="sendmail" value="svp" /></td>
<td><button class="bouton" value="Envoyer" onclick="subCc()">Envoyer</button></td>
</tr>
</table>
</form>
</div>
