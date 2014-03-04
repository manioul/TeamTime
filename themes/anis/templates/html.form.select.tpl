{* Smarty *}
{if isset($select.label)}<label for="{$select.name}">{$select.label}</label>{/if}
<select name="{$select.name}"{if isset($select.onchange)} onchange="{$select.onchange}"{/if}>
{foreach $select.options as $option}
<option value="{$option.value}"{if isset($option.selected)} selected="selected"{/if}>{$option.content}</option>
{/foreach}
</select>
