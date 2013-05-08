{* Smarty *}
<div id="timeInfo">
La cr&eacute;ation de cette page a n&eacute;cessit&eacute; <span class='temps'>{$constructTime}</span> secondes.<br />
<table>
<thead>
<tr>
<th>Nom</th><th>instantann&eacute;</th><th>cumul&eacute;</th><th>fonction chrono</th><th>% temps total</th>
</tr>
</thead>
<tbody>
{foreach $debugTimes as $key => $place}
<tr>
<td>{$key}</td><td><span class='temps'>{$place.instant}</span></td><td><span class='temps'>{$place.cumule}</span></td><td>{$place.chrono} (chrono fonct.)</td><td class="temps">{math equation="x*100/y" x=$place.instant y=$constructTime format="%.2f%%"}</td>
</tr>
{/foreach}
</tbody>
</table>
</div>
