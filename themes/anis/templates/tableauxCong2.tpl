{* Smarty *}
{if !empty($detail)}
{foreach $detail as $dispo => $row}
<table style="float:right;margin:0 30px;">
<thead>
<tr>
<th>{$dispo}</th>
<th colspan="2">{$row[0]['nom']}</th>
</tr>
</thead>
<tbody>
{foreach $row as $index => $val}
<tr>
<td>{$index+1}/{$val['quantity']}</td><td>{$val['date']}</td><td><a href="ajax.php?q=YT&amp;uid={$val['uid']}&amp;d={$val['date']}">{$val['year']}</a></td>
</tr>
{/foreach}
</table>
{/foreach}
{/if}
<dl>
{foreach $onglets as $centre => $array}
<dt>{$centre}</dt>
<dd>
<dl>
{foreach $array as $team => $arr}
<dt>{$team}</dt>
<dd>
<table>
<thead>
<tr>
<th>Nom</th><th>Congé</th><th>Congé (long)</th><th>Année</th><th>Nombre posé</th><th>Reliquat</th><th>Quota</th>
</tr>
</thead>
<tbody>
{foreach $arr as $vacances}
<tr>
<td><a href="?uid={$vacances['uid']}">{$vacances['nom']}</a></td><td>{$vacances['dispo']}</td><td>{$vacances['nom_long']}</td><td>{$vacances['year']}</td><td>{$vacances['déposé']}</td><td>{$vacances['reliquat']}</td><td>{$vacances['quantity']}</td>
</tr>
{/foreach}
</tbody>
</table>
</dd>
{/foreach}
</dl>
</dd>
{/foreach}
</dl>
