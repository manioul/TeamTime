{* Smarty *}
<table id="listeHeures">
<caption>Listing des heures du {$dateDebut} au {$dateFin}</caption>
<thead>
<tr>
<th>Nom</th>
<th>Date</th>
<th>Heures normales</th>
<th>Heures instruction</th>
<th>Heures simulateur</th>
<th>Heures double</th>
<th>Hide</th>
<th>Suppr</th>
</tr>
</thead>
<tbody>
{foreach $heures as $aHeure}
<tr>
<td>{$nom|upper}</td>
<td><a href="heuresJour.php?d={$aHeure.date}">{$aHeure.date}</a></td>
<td>{$aHeure.normales}</td>
<td>{$aHeure.instruction}</td>
<td>{$aHeure.simulateur}</td>
<td>{$aHeure.double}</td>
<td></td>
<td></td>
</tr>
{/foreach}
<tr>
<td>{$nom|upper}</td>
<td></td>
<td>{$totaux.normales}</td>
<td>{$totaux.instruction}</td>
<td>{$totaux.simulateur}</td>
<td>{$totaux.double}</td>
<td colspan="2"></td>
</tbody>
</table>
