{* Smarty *}
<table id="heuresJour">
<caption>Distribution des heures le <a href="affiche_grille.php?dateDebut={$date}&nbCycle=1" title="Afficher la grille du {$date}">{$date}</a></caption>
<thead>
<tr>
<th>Nom</th>
<th>Heures normales</th>
<th>Heures instruction</th>
<th>Heures simulateur</th>
</tr>
</thead>
<tbody>
{foreach $tableau as $row}
<tr>
<td>{$row.nom|upper}</td>
<td>{$row.normales}</td>
<td>{$row.instruction}</td>
<td>{$row.simulateur}</td>
</tr>
{/foreach}
</tbody>
</table>
