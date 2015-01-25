{* Smarty *}
{if !empty($detail)}
<div class="bloc" style="width:430px;margin:0 20px;"><h3>Détail {$year}</h3>
{foreach $detail as $dispo => $row}
<table style="margin:20px 40px 20px 0;" class="altern-row bloc">
<thead>
<tr>
<th>{$dispo}</th>
<th colspan="2">{$row[0]['nom']}</th>
</tr>
</thead>
<tbody>
{foreach $row as $index => $val}
{if $val['quantity'] <= $index}
<tr class="warning">
<td>{$index+1}/{$val['quantity']}</td><td>{$val['date']}</td><td onclick="ajr('CO', 'yt', '', '{$val.uid}', 'date', '{$val.date}')" title="Cliquez pour changer l'année" class="clic">{$val.year}</a></td>
{else}
<tr>
<td>{$index+1}/{$val['quantity']}</td><td>{$val['date']}</td><td onclick="ajr('CO', 'yt', '', '{$val.uid}', 'date', '{$val.date}')" title="Cliquez pour changer l'année" class="clic">{$val.year}</a></td>
{/if}
</tr>
{/foreach}
</table>
{/foreach}
</div>
{/if}
