{* Smarty *}
<div id="listHS">
<form id="fHS" class="ng" method="POST" action="">
<ul>
<li>
<label for="dateFrom">Début</label>
<input type="date" name="dateFrom" id="dateFrom" value="{$smarty.post.dateFrom}" />
</li>
<li>
<label for="dateTo">Fin</label>
<input type="date" name="dateTo " id="dateTo" value="{$smarty.post.dateTo}" />
</li>
<li>
<input type="submit" value="Afficher" />
</li>
</ul>
</form>
<table>
<caption>Liste des heures à partager déjà saisies</caption>
<thead>
<tr>
<th>Date</th>
<th>Heures</th>
<th>Dispatched</th>
<th>Writable</th>
</tr>
</thead>
<tbody>
{foreach $aListe as $heures}
<tr>
<td>{$heures.date}</td>
<td>{$heures.heures}</td>
<td>{if $heures.dispatched}X{/if}</td>
<td>{if $heures.writable}X{/if}</td>
</tr>
{/foreach}
</tbody>
</table>
</div>
