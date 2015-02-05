{* Smarty *}
<form id="{$form.id}" method="{$form.method}" action="{$form.action}">
<table{if isset($table.class)} class="{$table.class}"{/if}>
<thead>
<tr>
{foreach $heads as $head}
<th>{$head.content}</th>
{/foreach}
</tr>
</thead>
<tbody>
{foreach $tbody as $row}
<tr>
{foreach $row as $elem}
{if $elem.type == 'content'}<td{if isset($elem.class)} class="{$elem.class}"{/if}{if isset($elem.id)} id="{$elem.id}"{/if}>{if isset($elem.content)}{$elem.content}{/if}</td>
{elseif $elem.type == 'input'}<td><input type="{$elem.type}" name="{$elem.name}" /></td>
{elseif $elem.type == 'checkbox'}<td><input type="checkbox" name="{$elem.name}"{if isset($elem.checked)} checked="checked"{/if} /></td>
{elseif $elem.type == 'select'}<td>{include file='html.form.select.tpl' select=$elem}</td>{/if}
{/foreach}
</tr>
{/foreach}
<tr><td colspan="{sizeof($row)}"><input type="submit" value="{$submit.value}" {if isset($submit.class)}class="{$submit.class}" {/if}/></td></tr>
</tbody>
</table>
</form>
