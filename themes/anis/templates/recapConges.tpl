{* Smarty *}
<div class="bloc" style="margin:0 20px;min-height:308px;width:430px;z-index:100;"><h3>Récapitulatif {$year}</h3>
<table style="margin:20px 0;" class="altern-row">
<thead>
<tr>
<th>Nom</th><th>Congé</th><th>Congé (long)</th><th>Année</th><th>Nombre posé</th><th>Reliquat</th><th>Quota</th>
</tr>
</thead>
<tbody>
{foreach $decompte as $vacances}
{if $vacances['reliquat'] < 0}
<tr class="warning">
<td><a href="?uid={$vacances['uid']}">{$vacances['nom']}</a></td><td>{$vacances['dispo']}</td><td>{$vacances['nom_long']}</td><td>{$vacances['year']}</td><td>{$vacances['déposé']}</td><td>{$vacances['reliquat']}</td><td>{$vacances['quantity']}</td>
{else}
<tr>
<td><a href="?uid={$vacances['uid']}">{$vacances['nom']}</a></td><td>{$vacances['dispo']}</td><td>{$vacances['nom_long']}</td><td>{$vacances['year']}</td><td>{$vacances['déposé']}</td><td>{$vacances['reliquat']}</td><td>{$vacances['quantity']}</td>
{/if}
</tr>
{/foreach}
</tbody>
</table>
</div>
