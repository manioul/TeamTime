{* Smarty *}
<table id="listeHeures">
<caption>Listing des heures du {$dateDebut} au {$dateFin}</caption>
<thead>
<tr>
<th>Nom</th>
<th>Heures normales</th>
<th>Heures instruction</th>
<th>Heures simulateur</th>
<th>Heures double</th>
</tr>
</thead>
<tbody>
{foreach $mTotaux as $totaux}
<tr>
<td><a href="mesHeures.php?uid={$totaux.uid}&amp;d={$dateDebut}&amp;f={$dateFin}&amp;nom={$totaux.nom}">{$totaux.nom|upper}</a></td>
<td>{$totaux.normales}</td>
<td>{$totaux.instruction}</td>
<td>{$totaux.simulateur}</td>
<td>{$totaux.double}</td>
</tr>
{/foreach}
</tbody>
</table>
