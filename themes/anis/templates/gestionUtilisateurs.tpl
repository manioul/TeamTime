{* Smarty *}
<div>
{if $affectations|@count > 0}
<form id="fFilter" method="get" target="#">
<select name="filter" onchange="submit()">
{foreach from=$affectations key=centre item=team}
{foreach from=$team key=equipe item=one}
<option label="{$centre}-{$equipe}" value="{$centre}-{$equipe}">{$centre} - {$equipe}</option>
{/foreach}
{/foreach}
</select>
</form>
{/if}
<table>
{foreach from=$usersInfos key=lineNb item=infos name=lineBoucle}
{if $lineNb == 0}<thead>{elseif $lineNb == 1}</thead>
<tbody>{/if}
<tr id="line{$lineNb}">
{foreach from=$infos item=value}
{if $lineNb == 0}<th>{else}<td>{/if}{$value}{if $lineNb == 0}</th>{else}</td>{/if}
{/foreach}
</tr>
{/foreach}
</tbody>
</table>
</div>
