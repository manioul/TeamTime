{* Smarty *}
<ul>
{if !empty($smarty.session.ADMIN)}
<li style="display:none;" id="lUid">
<label for="uid">uid : </label><input type="text" name="uid" id="uid"{if is_object($utilisateur)} value="{$utilisateur->uid()}"{/if} />
</li>
{elseif !empty($smarty.session.EDITEURS)}
<input type="hidden" name="uid"{if is_object($utilisateur)} value="{$utilisateur->uid()}"{/if} />
{/if}
<li id="lNom">
<label for="nom">nom : </label><input type="text" name="nom" id="nom"{if is_object($utilisateur)} value="{$utilisateur->nom()}"{/if} onkeyup="showUsers(this)" />
<div id="dropdownbox" style="display:none;"><ul></ul></div>
<button name="iANU" id="iANU" style="display:none;" class="bouton" onclick="return ANU();">Ajouter un nouvel utilisateur</button>
</li>
<li style="display:none;" id="lPrenom">
<label for="prenom">prénom : </label><input type="text" name="prenom" id="prenom"{if is_object($utilisateur)} value="{$utilisateur->prenom()}"{/if} />
</li>
<li style="display:none;" id="lEmail">
<label for="email">email : </label><input type="email" name="email" id="email"{if is_object($utilisateur)} value="{$utilisateur->email()}"{/if} placeholder="monadresse@email.com" />
</li>
</ul>
