{* Smarty *}
{foreach $fieldsets as $fieldset}
{if !isset($fieldset.display) || $fieldset.display != 'none'}<fieldset>{if isset($fieldset.legend)}<legend>{$fieldset.legend}</legend>{/if}{/if}
<ul>
{foreach $fieldset.row as $elem}
<li>
{if $elem.type == 'fieldset'}{include file='html.form.fieldset.tpl' fieldset=$elem}
{elseif $elem.type == 'select'}{include file='html.form.select.tpl' select=$elem}
{else}{include file='html.form.input.tpl' input=$elem}
{/if}
</li>
{/foreach}
</ul>
{if !isset($fieldset.display) || $fieldset.display != 'none'}</fieldset>{/if}
{/foreach}
