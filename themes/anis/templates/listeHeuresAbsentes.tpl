{* Smarty *}
<div class="ng w24 bloc">
<table>
<caption>Dates sans heure attribuée</caption>
<thead>
<tr>
<th>Date</th><th>Vacation</th>
</tr>
</thead>
<tbody>
{foreach $unput as $date => $vacation}
<tr>
<td>{$date}</td><td>{$vacation}</td>
</tr>
{/foreach}
</tbody>
</table>
</div>
