{* Smarty *}
<ul class="{$class}">
{foreach from=$arbre key=k item=element}
{include file='elem_menu.tpl' elem=$element id=$menu->titreAsId() key=$k}
{/foreach}
</ul>
