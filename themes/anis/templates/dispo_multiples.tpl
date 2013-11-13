{* Smarty *}
{* Affiche un tableau des dispo multiples pour un même jour et un même utilisateur *}
{* Permet également d'afficher la view VIEW_LIST_DISPO en passant le résultat dans un tableau de tableau : $results[0][] = $row *}
<div class="dispoMultiples">
<h1>{$titre}</h1>
{foreach $results as $result}
<table>
<thead>
<tr>
<th>sdid</th>
<th>Nom</th>
<th>Dispo</th>
<th>Date</th>
<th>Vacation</th>
<th>Year</th>
<th>Péréquation</th>
<th>Supprime</th>
</tr>
</thead>
<tbody>
{foreach $result as $row}
<tr>
<td>{$row.sdid}</td>
<td>{$row.nom}</td>
<td>{$row.dispo}</td>
<td>{$row.date}</td>
<td>{$row.vacation}</td>
<td>{if isset($row.year)}{$row.year}{else}-{/if}</td>
<td><a href='?id={$row.sdid}&amp;op=upd&amp;t=l&amp;field=pereq&amp;val={!$row.pereq}'>{$row.pereq}</a></td>
<td><a href='?id={$row.sdid}.&amp;op=del'>delete</a></td>
</tr>
{/foreach}
</tbody>
</table>
{/foreach}
<div><a href="?end=1">Finish</a></div>
</div>
