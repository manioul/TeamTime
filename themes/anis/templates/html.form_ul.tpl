{* Smarty *}
<form name="{$form.name}"{if isset($form.id)} id="{$form.id}"{/if}{if isset($form.classe)} class="{$form.classe}"{/if} method="{$form.method}" action="{$form.action}">
{include file='html.form.fieldset_ul.tpl' fieldsets=$form.fieldsets}
</form>
