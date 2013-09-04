{* Smarty *}
{* template d'affichage et de modification des informations d'un utilisateur *}
<div id="utilisateur">
<form name="fContact" id="fContact" class="" method="POST" action="updateContact.php">
<div class="table" style="width:80em;">
<div class="row">
<div class="cell">
<div class="cell">
<div id="contact">
<fieldset><legend>Contact</legend>
<div class="table table-contact">
<div class="row">
<label for="uid" class="cell">uid : </label><input type="text" name="uid" id="uid" value="{$uid}" class="cell" />
</div><!-- fin div row -->
<div class="row">
<label for="nom" class="cell">nom : </label><input type="text" name="nom" id="nom" value="{$nom}" class="cell" />
</div><!-- fin div row -->
<div class="row">
<label for="prenom" class="cell">prénom : </label><input type="text" name="prenom" id="prenom" value="{$prenom}" class="cell" />
</div><!-- fin div row -->
<div class="row">
<label for="email" class="cell">email : </label><input type="email" name="email" id="email" value="{$email}" class="cell" placeholder="monadresse@email.com" />
</div><!-- fin div row -->
</div><!-- fin div table table-contact -->
{foreach from=$phone key=k item=tph}
{if $tph@first}
<div class="table table-contact">
{/if}
<div class="row">
<div class="cell">
<fieldset class="cell">
<legend>Téléphone {$tph->description()}</legend>
<div class="row">
<input type="hidden" name="phone[{$k}][phoneid]" value="{$tph->phoneid()}" />
<label for="phone{$k}" class="cell">Numéro : </label><div class="cell"><div class="cell"><input type="tel" pattern="\d\d\d\d\d\d\d\d\d\d" name="phone[{$k}][phone]" id="phone{$k}" value="{$tph->phone()}" /></div></div>
</div><!-- fin div row -->
<div class="row">
<label for="desc{$k}" class="cell">Description : </label><input class="cell" type="text" name="phone[{$k}][description]" id="desc{$k}"{if is_string($tph->description())} value="{$tph->description()}"{/if} />
</div><!-- fin div row -->
<div class="row">
<label for="pal{$k}" class="cell">Principal : </label><input class="cell" type="checkbox" name="phone[{$k}][principal]" id="pal{$k}"{if $tph->principal()} checked="checked"{/if} />
</div><!-- fin div row -->
</fieldset>
</div><!-- fin div cell -->
<div class="cell middle"><div class="imgwrapper12" style="left:15px;"><a href="suppress.php?q=phone&amp;id={$tph->phoneid()}&amp;uid={$uid}"><img class="cnl" alt="supprimer" src="{$image}" /></a></div></div>
</div><!-- fin div row -->
{if $tph@last}
</div><!-- fin div table table-contact -->
{/if}
{/foreach}
<div class="table table-contact">
<div id="dAddPhone" class="row">
<div class="label cell">Ajouter un téléphone : </div><div class="cell"><a href="#" onclick="newPhone()"><div class="bouton" id="addPhone">+</div></a></div>
</div><!-- fin #dAddPhone -->
{foreach from=$adresse key=k item=add}
{if $add@first}
<div class="table table-contact">
{/if}
<div class="row">
<div class="cell">
<fieldset class="cell">
<legend>Adresse</legend>
<div class="row">
<input type="hidden" name="adresse[{$k}][adresseid]" value="{$add->adresseid()}" />
<label for="adresse{$k}" class="cell">adresse : </label><textarea name="adresse[{$k}][adresse]" id="adresse{$k}" class="cell">{$add->adresse()}</textarea>
</div><!-- fin div row -->
<div class="row">
<label for="cp{$k}" class="cell">code postal : </label><input type="text" id="cp{$k}" name="adresse[{$k}][cp]" value="{$add->cp()}" class="cell" />
</div><!-- fin div row -->
<div class="row">
<label for="ville{$k}" class="cell">ville : </label><input type="text" name="adresse[{$k}][ville]" id="ville{$k}" value="{$add->ville()}" class="cell" />
</div><!-- fin div row -->
</fieldset>
</div><!-- fin div cell -->
<div class="cell middle"><div class="imgwrapper12" style="left:15px;"><a href="suppress.php?q=adresse&amp;id={$add->adresseid()}&amp;uid={$uid}"><img class="cnl" alt="supprimer" src="{$image}" /></a></div></div>
</div><!-- fin div row -->
{if $add@last}
</div><!-- fin div table table-contact -->
{/if}
{/foreach}
</div><!-- fin div table table-contact -->
<div class="table table-contact">
<div id="dAddAddress" class="row">
<div class="label cell">Ajouter une adresse : </div><div class="cell"><a href="#" onclick="newAddress()"><div class="bouton" id="addAddress">+</div></a></div>
</div><!-- Fin #dAddAddress -->
</div><!-- Fin div table table-contact -->
</fieldset>
</div><!-- fin div contact -->
</div><!-- fin div cell -->
<div class="cell">
<div id="droits">
<fieldset><legend>Droits</legend>
<div class="table">
<div class="row">
<label for="login" class="cell">login</label><input type="text" name="login" id="login" value="{$login}" class="cell" />
</div><!-- fin div row -->
<div class="row">
<label for="gid" class="cell">gid : </label><input type="text" name="gid" id="gid" value="{$gid}" class="cell" />
</div><!-- fin div row -->
<div class="row">
<label for="locked" class="cell">locked : </label><input type="checkbox" id="locked" name="locked"{if (!empty($locked))} checked="checked"{/if} class="cell" />
</div><!-- fin div row -->
<div class="row">
<label for="actif" class="cell">actif : </label><input type="checkbox" id="actif" name="actif"{if (!empty($actif))} checked="checked"{/if} class="cell" />
</div><!-- fin div row -->
<div class="row">
<label for="totd" class="cell">tip of the day : </label><input type="checkbox" id="totd" name="totd"{if (!empty($totd))} checked="checked"{/if} class="cell" />
</div><!-- fin div row -->
<div class="row">
<label for="poids" class="cell">poids : </label><input type="text" name="poids" id="poids" value="{$poids}" class="cell" />
</div><!-- fin div row -->
<div class="row">
<label for="read" class="cell">page : </label>
<select name="read" id="read" class="cell">
{foreach from=$pages key=k item=p}
<option value="{$k}"{if $indexPage == $k} selected="selected"{/if}>{$p}</option>
{/foreach}
</select>
</div><!-- fin div row -->
</div><!-- fin div table -->
</fieldset>
</div><!-- fin div droits -->
</div><!-- fin div cell -->
</div><!-- fin div cell -->
</div><!-- fin div row -->
<div class="row">
<div class="cell">
<input type="submit" class="bouton" id="submitcontact" value="Mettre à jour" />
</div><!-- fin div cell -->
</div><!-- fin div row -->
</div><!-- fin div table -->
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
<td><div class="cell"><input type="date" name="newend" id="dateF" /></div></td>
</tr>
{foreach from=$datas key=k item=carriere name=data}
<tr>
<td>
<input type="text" name="centre" value="{$carriere->centre()}" /></td>
<td><input type="text" name="team" value="{$carriere->team()}" /></td>
<td><input type="text" name="grade" value="{$carriere->grade()}" /></td>
<td><input type="date" name="beginning" value="{$carriere->beginning()->date()}"></td>
<td><div class="cell"><input type="date" name="end" value="{$carriere->end()->date()}" /></div><div class="cell middle"><div class="imgwrapper12" style="left:5px;"><a href="suppress.php?q=affectation&amp;id={$carriere->aid()}&amp;uid={$uid}"><img class="cnl" alt="supprimer" src="{$image}" /></a></div></div></td>
</tr>
{/foreach}
</tbody>
</table>
</div><!-- #carriere -->
<input type="submit" class="bouton" id="submitcontact" value="Mettre à jour" />
</form>
</div><!-- Fin div #utilisateur -->
