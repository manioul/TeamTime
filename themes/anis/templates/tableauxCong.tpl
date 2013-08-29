{* Smarty *}
{* Affichage de l'année des congés *}
<h1 class="">{$titre} {$year}</h1>
{* Menu horizontal permettant de naviguer entre les différents types de congés *}
<div class="menu menuHorizontal">
<ul>
{foreach from=$onglets item=onglet}
<li class="elemMenu" id="chcong{$onglet.param}"><a href="?param={$onglet.param}">{$onglet.nom}</a></li>
{/foreach}
</ul>
</div>
{foreach from=$onglets item=onglet}
{if empty($smarty.get.param) || $onglet.param == $smarty.get.param}
<div id="d_{$onglet.param}" class="tabCong">
<table id="t_{$onglet.param}" class="genElem">
{foreach from=$users item=user}
<tr>
<td id="u{$user->uid()}" class="user">{$user->nom()}</td>
{section name=vac loop=$onglet.quantity}
{* Le lien suivant ne sert pas. Il n'est là que pour prévoir une utilisation sans javascript, mais est-ce nécessaire ? *}
<td class="date{if (!empty($tab[$onglet.param][$user->uid()][$smarty.section.vac.index]['classe']))}{$tab[$onglet.param][$user->uid()][$smarty.section.vac.index]['classe']}{/if}" id="u{$user->uid()}d{if (!empty($tab[$onglet.param][$user->uid()][$smarty.section.vac.index]['classe']))}{$tab[$onglet.param][$user->uid()][$smarty.section.vac.index]['date']}{/if}"><a href="?uid={$user->uid()}&amp;param={$tab[$onglet.param][$user->uid()][$smarty.section.vac.index]['date']}">{$tab[$onglet.param][$user->uid()][$smarty.section.vac.index]['date']}</a></td>
{/section}
</tr>
{/foreach}
</table>
</div>
{/if}
{/foreach}
