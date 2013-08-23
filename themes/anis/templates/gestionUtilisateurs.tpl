{* Smarty *}
<div>
{if $affectations|@count > 0}
<form id="fFilter" name="fFilter" method="get" action="#">
<select name="filter" onchange="submit()">
{foreach from=$affectations key=centre item=team}
{foreach from=$team key=equipe item=one}
<option label="{$centre}-{$equipe}" value="{$centre}-{$equipe}">{$centre} - {$equipe}</option>
{/foreach}
{/foreach}
</select>
</form>
{/if}
<table class="altern-row">
{foreach from=$usersInfos key=lineNb item=infos name=lineBoucle}
{if $lineNb == 0}<thead>{elseif $lineNb == 1}</thead>
<tbody>{/if}
<tr id="line{$lineNb}">
{foreach from=$infos key=Field item=value}
{if $lineNb == 0}<th>{else}<td id="{$Field}{$infos.uid}">{/if}{$value}{if $Field == 'uid' && $lineNb != 0}&nbsp;<a href="" id="suppr{$infos.uid}">-</a>{/if}{if $lineNb == 0}</th>{else}</td>{/if}
{/foreach}
</tr>
{/foreach}
</tbody>
</table>
<table>
<form id="fNewUser" name="fNewUser" method="post" target="gestionUtilisateur.php">
<thead>
<tr>
{foreach from=$header item=value}
<th>{$value}</th>
{/foreach}
<th></th>
</tr>
</thead>
<tbody>
<tr>
{foreach from=$form key=idx item=value}
<td><input type="{$value.type}" name="{$value.Field}"{if $value.type != 'checkbox'} placeholder="{$value.Type}"{/if}{if $value.width > 0} maxlength="{$value.maxlength}" style="width:{$value.width}em;"{/if}{if $value.value != ""} value="{$value.value}"{/if} /></td>
{/foreach}
<td><input type="submit" value="Ajouter" /></td>
</tr>
</tbody>
</form>
</table>
</div>
