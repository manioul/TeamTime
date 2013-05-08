{* Smarty *}
<div id='container'>
	<div id="horlogeFond"></div>
	{if $nav[1] != ""}<div id="nav1"></div>{/if}
	{if $nav[2] != ""}<div id="nav2"></div>{/if}
	{if $nav[3] != ""}<div id="nav3"></div>{/if}
	{if $nav[4] != ""}<div id="nav4"></div>{/if}
	{if $nav[1] != ""}
	<div id="nav1-text">
		{include file="`$nav[1]`.tpl" content=$content1}
	</div>
	{/if}
	{if $nav[2] != ""}
	<div id="nav2-text">
		{include file="`$nav[2]`.tpl" content=$content2}
	</div>
	{/if}
	{if $nav[3] != ""}
	<div id="nav3-text">
		{include file="`$nav[3]`.tpl" content=$content3}
	</div>
	{/if}
	{if $nav[4] != ""}
	<div id="nav4-text">
		{include file="`$nav[4]`.tpl" content=$content4}
	</div>
	{/if}
	{if is_array($contenu)}
	<div id="content" class="boite">
		{foreach from=$contenu key=id item=v}
		<div id="$id">
			<h2>{$v.titre}</h2>
			<p>{$v.texte}</p>
		</div>
		{/foreach}
	</div>
	{/if}
	<div id="version">v{$VERSION}</div>
</div>
