{* Smarty *}
{* template d'affichage et de modification des informations d'un utilisateur *}
<div id="utilisateur">
<form name="fContact" id="fContact" class="" method="POST" action="updateContact.php">
<fieldset><legend>Mon compte</legend>
<ul>
<li>
<label for="uid">uid : </label><input type="text" name="uid" id="uid" value="{$utilisateur->uid()}" />
</li>
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
<label for="read">page d'accueil : </label>
<select name="read" id="read">
{foreach $utilisateur->availablePages('titre') as $k => $p}
<option value="{$key}"{if $indexPage == $key} selected="selected"{/if}>{$p}</option>
{/foreach}
</select>
</li>

{* Affichage des téléphones *}
<li class="contact">
<fieldset>
<legend>Téléphone</legend>
<ul>
{foreach $utilisateur->phone() as $key => $tph}
<li>
<fieldset>
<legend>Téléphone {$tph->description()}</legend><div class="imgwrapper12"><a href="suppress.php?q=phone&amp;id={$tph->phoneid()}&amp;uid={$uid}"><img class="cnl" alt="supprimer" src="{$image}" /></a></div>
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
<label for="addPhone">Ajouter un téléphone : </label><input type="button" onclick="newPhone()" value="+" class="bouton" id="iAddPhone" />
</li>
</ul>
</fieldset>
{* Fin de l'affichage des téléphones *}

{* Affichage des adresses *}
<li class="contact">
<fieldset>
<legend>Adresses</legend>
<ul>
{foreach $utilisateur->adresse() as $key => $add}
<li>
<fieldset>
<legend></legend><div class="imgwrapper12" style="left:15px;"><a href="suppress.php?q=adresse&amp;id={$add->adresseid()}&amp;uid={$uid}"><img class="cnl" alt="supprimer" src="{$image}" /></a></div>
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
<label for="addAddress">Ajouter une adresse : </label><input type="button" class="bouton" onclick="newAddress()" value="+" id="iAddAddress" />
</li>
</ul>
</fieldset>
{* Fin de l'affichage des adresses *}
</fieldset>
{* Fin de la partie contact *}

<input type="submit" class="bouton" id="submitcontact" value="Mettre à jour" />


{* Réservé à l'administrateur *}
{if $smarty.session.ADMIN}
<fieldset><legend>Droits</legend>
<ul>
<li>
<label for="login">login</label><input type="text" name="login" id="login" value="{$utilisateur->login()}" />
</li>
<li>
<label for="gid">gid : </label><input type="text" name="gid" id="gid" value="{$utilisateur->gid()}" />
</li>
<li>
<label for="locked">locked : </label><input type="checkbox" id="locked" name="locked"{if $locked} checked="checked"{/if} />
</li>
<li>
<label for="actif">actif : </label><input type="checkbox" id="actif" name="actif"{if $actif} checked="checked"{/if} />
</li>
<li>
<label for="totd">tip of the day : </label><input type="checkbox" id="totd" name="totd"{if $totd} checked="checked"{/if} />
</li>
<li>
<label for="poids">poids : </label><input type="text" name="poids" id="poids" value="{$utilisateur->poids()}" />
</li>
</ul>
</fieldset>

<input type="submit" class="bouton" id="submitcontact" value="Mettre à jour" />
{/if}

</form>



<div id="carriere">
<h2>Carrière et affectations</h2>
<table class="altern-row">
<thead>
<tr>
<td>Centre</td><td>Équipe</td><td>grade</td><td>Début</td><td>Fin</td>
</tr>
</thead>
<tbody>
<tr>
<td>
<input type="text" name="newcentre" /></td>
<td><input type="text" name="newteam" /></td>
<td><select name="newgrade"><option value="c">C</option><option value="theo">Théorique</option><option value="pc">PC</option><option value="ce">CE</option><option value="cds">CDS</option></select></td>
<td><input type="date" name="newbeginning" id="dateD" /></td>
<td><div><input type="date" name="newend" id="dateF" /></div></td>
</tr>
{foreach from=$datas key=k item=carriere name=data}
<tr>
<td>
<input type="text" name="centre" value="{$carriere->centre()}" /></td>
<td><input type="text" name="team" value="{$carriere->team()}" /></td>
<td><input type="text" name="grade" value="{$carriere->grade()}" /></td>
<td><input type="date" name="beginning" value="{$carriere->beginning()->date()}"></td>
<td><div><input type="date" name="end" value="{$carriere->end()->date()}" /></div><div class="cell middle"><div class="imgwrapper12" style="left:5px;"><a href="suppress.php?q=affectation&amp;id={$carriere->aid()}&amp;uid={$uid}"><img class="cnl" alt="supprimer" src="{$image}" /></a></div></div></td>
</tr>
{/foreach}
</tbody>
</table>
</div><!-- #carriere -->
<input type="submit" class="bouton" id="submitcontact" value="Mettre à jour" />
</form>
</div><!-- Fin div #utilisateur -->
