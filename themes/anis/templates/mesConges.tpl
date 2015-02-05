{* Smarty *}
{foreach $conges as $year => $vacances}
<div class="bloc" style="width:472px;">
{include file="recapConges.tpl" decompte=$vacances.decompte year=$year}
{include file="detailConges.tpl" detail=$vacances.detail year=$year}
</div>
{/foreach}
