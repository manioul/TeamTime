{* Smarty *}
<div id="dMessages">
{section name=i loop=$messages}
{if $smarty.section.i.index == 0}<ul id="uMessages">{/if}
<li{if $messages[i].classe != ""} class="{$messages[i].classe}"{/if}>{if $messages[i].lien != ""}<a href="{$messages[i].lien}">{/if}{$messages[i].message}{if $messages[i].lien != ""}</a>{/if}</li>
{if $index == $smarty.section.i.last}</ul>{/if}
{/section}
</div>
