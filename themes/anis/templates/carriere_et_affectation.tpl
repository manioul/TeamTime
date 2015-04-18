{* Smarty *}
<form name="fAffectation" id="fAffectation" class="" method="POST" action="monCompte.php" onsubmit="return confirm('La modification des carrières influe sur le calcul des heures. Confirmez-vous cette modification ?')">
<fieldset id="affectation"><legend>Carrière et affectations</legend>
<input type="hidden" name="uid"{if is_object($utilisateur)} value="{$utilisateur->uid()}"{/if} />
<table class="altern-row">
<thead>
<tr>
<td>Centre</td><td>Équipe</td><td>Grade</td><td>Début</td><td>Fin</td><td>Poids</td><td></td>
</tr>
</thead>
<tbody>
<tr>
<td>{include file="html.form.select.tpl" select=$centres}</td>
<td>{include file="html.form.select.tpl" select=$teams}</td>
<td>{include file="html.form.select.tpl" select=$grades}</td>
<td><input type="date" name="dateD" id="dateD" /></td>
<td><input type="date" name="dateF" id="dateF" /></td>
<td><input type="text" name="poids" id="poids" /></td>
<td><input type="submit" class="bouton" name="submitAffect" value="Ajouter" /></td>
</tr>
{* La partie carrière et affectations peut être remplie par la fonction javascript fillUser (administration.js.php) *}
{foreach $datas as $carriere}
<tr id="affectation{$carriere->aid()}">
<td>{$carriere->centreDisplay()}</td>
<td>{$carriere->teamDisplay()}</td>
<td>{$carriere->gradeDisplay()}</td>
<td>{$carriere->beginning()->formatDate()}</td>
<td>{$carriere->end()->formatDate()}</td>
<td>{$carriere->poids()}</td>
<td><div class="imgwrapper12" style="left:5px;cursor:pointer;" onclick='supprInfo("affectation", {$carriere->aid()}, {$utilisateur->uid()});' title="Supprimer l'entrée"><img class="cnl" alt="supprimer" src="{$image}" /></div></td>
</tr>
{/foreach}
</tbody>
</table>
</fieldset>
</form>
