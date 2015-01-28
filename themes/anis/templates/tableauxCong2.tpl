{* Smarty *}
{if sizeof($onglets) > 0}
<dl class='onglets bloc' style="width:480px;">
{foreach $onglets as $centre => $array}
<dt>{$centre}</dt>
<dd>
<dl class='sous-onglets'>
{foreach $array as $team => $arr}
<dt class="clic" onclick="$('#{$centre}-{$team}').slideToggle('slow');">{$team}</dt>
<dd id="{$centre}-{$team}">
{foreach $arr as $year => $vacances}
{include file="recapConges.tpl" decompte=$vacances year=$year}
{/foreach}
</dd>
{/foreach}
</dl>
</dd>
{/foreach}
</dl>
{/if}
