{* Smarty *}
<div id="dMessages">
{foreach $messages as $message}
{if $message@first}<ul id="uMessages">{/if}
<li{if !empty($message.classe)} class="{$message.classe}"{/if}>{if !empty($message.lien)}<a href="{$message.lien}">{/if}{$message.message}{if !empty($message.lien)}</a>{/if}</li>
{if $message@last}</ul>{/if}
{/foreach}
</div>
