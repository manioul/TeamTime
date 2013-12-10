{* Smarty *}
<div id="dispatchSchema">
<table>
<thead>
<tr>
<th>cycles</th>
<th>grades</th>
<th>dispos</th>
<th>type</th>
<th>statut</th>
<th>heures</th>
<th>Supprimer</th>
</tr>
</thead>
<tbody>
{foreach $aSchemas as $schema}
<tr>
<td>{$schema.cycles}</td>
<td>{$schema.grades}</td>
<td>{$schema.dispos}</td>
<td>{$schema.type}</td>
<td>{$schema.statut}</td>
<td>{$schema.heures}</td>
<td><div class="imgwrapper12" onclick='supprInfo("dispatchSchema", {$schema.rid}, null);setTimeout(location.assign(location.href), 1000);'><img class="cnl" alt="supprimer" src="{$image}" /></div></td>
</tr>
{/foreach}
</tbody>
</table>
</div>
