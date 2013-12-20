{* Smarty *}
<table id="listeHeures">
<caption>Listing des heures du {$dateDebut} au {$dateFin}</caption>
<thead>
<tr>
<th>Nom</th>
<th>Heures normales</th>
<th>Heures instruction</th>
<th>Heures simulateur</th>
</tr>
</thead>
<tbody>
{foreach $mTotaux as $totaux}
<tr>
<td>{$totaux.nom|upper}</td>
<td>{$totaux.normales}</td>
<td>{$totaux.instruction}</td>
<td>{$totaux.simulateur}</td>
</tr>
{/foreach}
</tbody>
</table>
