{* Smarty *}
{if isset($select.label)}<label for="{$select.name}">{$select.label}</label>{/if}
<select name="{$select.name}"{if isset($select.id)} id="{$select.id}"{else} id="{$select.name}"{/if}{if isset($select.multiple)} multiple="multiple"{/if}{if isset($select.onchange)} onchange="{$select.onchange}"{/if}>
{foreach $select.options as $option}
<option value="{$option.value}"{if isset($option.selected)} selected="selected"{/if}{if isset($option.disabled)} disabled="disabled"{/if}>{$option.content}</option>
{/foreach}
</select>
