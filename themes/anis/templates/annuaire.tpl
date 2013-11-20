{* Smarty *}
<div id="annuaire">
<table class="altern-row">
{foreach $entry as $id}
{if $id@first}<thead>{elseif $id@iteration == 2}<tbody>{/if}
<tr>
{foreach $id as $champ}
{if $id@first}<th>{else}<td>{/if}
{if $champ@iteration == $mailto && $id@iteration != 1}<a href="mailto:{$champ}">{$champ}</a>
{elseif is_array($champ)}
<ul>
{foreach $champ as $phone}
<li>{$phone->phone()}</li>
{/foreach}
</ul>
{else}{$champ}{/if}
{if $id@first}</th>{else}</td>{/if}
{/foreach}
</tr>
{if $id@first}</thead>{elseif $id@last}</tbody>{/if}
{/foreach}
</table>
</div>
