{* Smarty *}
{if isset($help)}
<h3 class="round-button round-button_medium" id="helpButton" onclick="$('#help').slideToggle('slow');">?</h3>
<article id="help" onclick="$(this).slideToggle('slow');">
{$help}
</article>
{/if}
