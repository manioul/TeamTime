{* Smarty *}
{* template d'affichage et de modification des informations d'un utilisateur *}
<div id="monCompte">
<form name="fContact" id="fContact" class="ng" method="POST" action="monCompte.php">
<fieldset><legend>Mon compte</legend>
<ul>
{if !empty($smarty.session.ADMIN)}
<li>
<label for="uid">uid : </label><input type="text" name="uid" id="uid" value="{$utilisateur->uid()}" />
</li>
{elseif !empty($smarty.session.EDITEURS)}
<input type="hidden" name="uid" value="{$utilisateur->uid()}" />
{/if}
<li>
<label for="nom">nom : </label><input type="text" name="nom" id="nom" value="{$utilisateur->nom()}" />
</li>
<li>
<label for="prenom">prénom : </label><input type="text" name="prenom" id="prenom" value="{$utilisateur->prenom()}" />
</li>
<li>
<label for="email">email : </label><input type="email" name="email" id="email" value="{$utilisateur->email()}" placeholder="monadresse@email.com" />
</li>
<li>
<fieldset><legend>Préférences utilisateur</legend>
<ul>
<li>
<label for="read">page d'accueil : </label>
<select name="read" id="read">
{foreach $utilisateur->availablePages('titre') as $k => $p}
<option value="{$k}"{if $utilisateur->indexPage() == $k} selected="selected"{/if}>{$p}</option>
{/foreach}
</select>
</li>
<li>
<label for="cpt" title="Les compteurs sont-ils visibles en affichage grilles multiples ?">Compteurs</label>
<input type="checkbox" name="cpt" id="cpt"{if isset($pref['cpt']) && $pref['cpt'] == 1} checked="checked"{/if} />
</li>
</ul>
</fieldset>
</li>

{* Affichage des téléphones *}
<li class="contact">
<fieldset>
<legend>Téléphone</legend>
<ul>
{foreach $utilisateur->phone() as $key => $tph}
<li id="phone{$tph->phoneid()}">
<fieldset>
<legend>Téléphone {$tph->description()}</legend><div class="imgwrapper12" onclick='supprInfo("phone", {$tph->phoneid()}, {$utilisateur->uid()});'><img class="cnl" alt="supprimer" src="{$image}" /></div>
<input type="hidden" name="phone[{$key}][phoneid]" value="{$tph->phoneid()}" />
<ul>
<li>
<label for="phone{$key}">Numéro : </label><input type="tel" pattern="\d\d\d\d\d\d\d\d\d\d" name="phone[{$key}][phone]" id="phone{$key}" value="{$tph->phone()}" />
</li>
<li>
<label for="desc{$key}">Description : </label><input type="text" name="phone[{$key}][description]" id="desc{$key}"{if is_string($tph->description())} value="{$tph->description()}"{/if} />
</li>
<li>
<label for="pal{$key}">Principal : </label><input type="checkbox" name="phone[{$key}][principal]" id="pal{$key}"{if $tph->principal()} checked="checked"{/if} />
</li>
</ul>
</fieldset>
</li>
{/foreach}
<li>
<input type="button" onclick="newPhone()" value="Ajouter un téléphone" class="bouton" id="iAddPhone" />
</li>
</ul>
</fieldset>
</li>
{* Fin de l'affichage des téléphones *}

{* Affichage des adresses *}
<li class="contact">
<fieldset>
<legend>Adresses</legend>
<ul>
{foreach $utilisateur->adresse() as $key => $add}
<li id="adresse{$add->adresseid()}">
<fieldset>
<legend></legend><div class="imgwrapper12" style="left:15px;" onclick='supprInfo("adresse", {$add->adresseid()}, {$utilisateur->uid()});'><img class="cnl" alt="supprimer" src="{$image}" /></div>
<input type="hidden" name="adresse[{$key}][adresseid]" value="{$add->adresseid()}" />
<ul>
<li>
<label for="adresse{$key}">adresse : </label><textarea name="adresse[{$key}][adresse]" id="adresse{$key}">{$add->adresse()}</textarea>
</li>
<li>
<label for="cp{$key}">code postal : </label><input type="text" id="cp{$key}" name="adresse[{$key}][cp]" value="{$add->cp()}" />
</li>
<li>
<label for="ville{$key}">ville : </label><input type="text" name="adresse[{$key}][ville]" id="ville{$key}" value="{$add->ville()}" />
</li>
</ul>
</fieldset>
</li>
{/foreach}
<li>
<input type="button" class="bouton" onclick="newAddress()" value="Ajouter une adresse" id="iAddAddress" />
</li>
</ul>
</fieldset>
{* Fin de l'affichage des adresses *}
</li>
</ul>
</fieldset>
{* Fin de la partie contact *}

<input type="submit" class="bouton" name="submitContact" value="Mettre à jour" />


{* Réservé à l'administrateur et aux editeurs *}
{if $smarty.session.EDITEURS || $smarty.session.ADMIN}
<fieldset><legend>Droits</legend>
<ul>
{if $smarty.session.EDITEURS}
<li>
<label for="login">login</label><input type="text" name="login" id="login" value="{$utilisateur->login()}" />
</li>
{/if}
{if $smarty.session.ADMIN}
<li>
<label for="locked">locked : </label><input type="checkbox" id="locked" name="locked"{if $locked} checked="checked"{/if} />
</li>
<li>
<label for="actif">actif : </label><input type="checkbox" id="actif" name="actif"{if $actif} checked="checked"{/if} />
</li>
<li>
<label for="totd">tip of the day : </label><input type="checkbox" id="totd" name="totd"{if $totd} checked="checked"{/if} />
</li>
{/if}
{if $smarty.session.EDITEURS}
<li>
<label for="poids">poids : </label><input type="text" name="poids" id="poids" value="{$utilisateur->poids()}" />
</li>
</ul>
</fieldset>

<input type="submit" class="bouton" id="submitContact" value="Mettre à jour" />
{/if}
{/if}

</form>


{include file="carriere_et_affectation.tpl"}
</div><!-- Fin div #monCompte -->
