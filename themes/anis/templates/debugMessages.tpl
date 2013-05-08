{* Smarty *}
<div id="dbgMsg">
<ul>
{if $debugMessages !== false}
{foreach from=$debugMessages item=message name=dbgmsg}
<li><pre>{$message}</pre></li>
{/foreach}
{/if}
</ul>
</div>
