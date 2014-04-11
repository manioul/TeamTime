{* Smarty *}
<table class="altern-row">
{foreach $activites as $activite}
{if $activite@first}<thead>{elseif $activite@iteration == 2}</thead><tbody>{/if}
<tr>
{foreach $activite as $field}
{if $field@iteration > 1}
{if $field@last && $activite@iteration > 1}
<td><a href="?did={$activite.did}&amp;a={!$field}">{$field}</a></td>
{else}
<td>{$field}</td>
{/if}
{/if}
{/foreach}
{if $activite@first}
<td></td>
<td></td>
<td></td>
<td></td>
{else}
<td><a href="suppress.php?q=dispo&amp;did={$activite.did}&amp;s=1">Supprimer</a></td>
<td>{*<a href="?did={$activite.did}&amp;e=1">*}Éditer{*</a>*}</td>
<td>{if $activite@iteration > 2}<a href="?did={$activite.did}&amp;u=1&amp;poids={$activite.poids}">Remonter</a>{/if}</td>
<td>{if !$activite@last}<a href="?did={$activite.did}&amp;d=1&amp;poids={$activite.poids}">Descendre</a>{/if}</td>
{/if}
</tr>
{/foreach}
</tbody>
</table>
