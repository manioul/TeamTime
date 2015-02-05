{* Smarty *}
<div>
{if $affectations|@count > 0}
<form id="fFilter" name="fFilter" method="get" action="#">
</form>
{/if}
<table class="altern-row genElem">
{foreach from=$usersInfos key=lineNb item=infos name=lineBoucle}
{if $lineNb == 0}<thead>{elseif $lineNb == 1}</thead>
<tbody>{/if}
<tr id="line{$lineNb}">
{foreach from=$infos key=Field item=value}
{if $lineNb == 0}<th>{else}<td id="{$Field}{$infos.uid}">{/if}{if $Field == 'uid' && $lineNb != 0}<a href="monCompte.php?uid={$value}">{$value}</a>&nbsp;<a href="" id="suppr{$infos.uid}">-</a>{else}{$value}{/if}{if $lineNb == 0}</th>{else}</td>{/if}
{/foreach}
</tr>
{/foreach}
</tbody>
</table>
</div>
