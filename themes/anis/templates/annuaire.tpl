{* Smarty *}
<div id="annuaire">
<table>
{foreach from=$entry key=lineNb item=id name=foo}
{if $smarty.foreach.foo.first}<thead>{elseif $smarty.foreach.foo.iteration == 2}<tbody>{/if}
<tr>
{foreach from=$id key=fieldNb item=champ name=bar}
{if $smarty.foreach.foo.first}<th>{else}<td>{/if}
{if $smarty.foreach.bar.iteration == 3 && $smarty.foreach.foo.iteration != 1}<a href="mailto:{$champ}">{$champ}</a>{else}{$champ}{/if}
{if $smarty.foreach.foo.first}</th>{else}</td>{/if}
{/foreach}
</tr>
{if $smarty.foreach.foo.first}</thead>{elseif $smarty.foreach.foo.last}</tbody>{/if}
{/foreach}
</table>
</div>
