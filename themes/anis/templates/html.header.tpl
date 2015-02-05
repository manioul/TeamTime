{* Smarty *}
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title>{if $titrePage}{$titrePage}{else}Grille{/if}</title>
	<!--<meta http-equiv="Content-Language"    content="{$language}" />>
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type"  content="text/css" />-->
	<meta http-equiv="Content-Type"        content="text/html; charset=utf-8" />
	<link rel="icon" type="image/ico" href="favicon.ico" />
{foreach from=$stylesheet item=sheet}
	<link rel="stylesheet" type="text/css" href="themes/{$theme}/style/{$sheet.href|escape:"javascript"}" media="{$sheet.media}" />
{/foreach}
{foreach from=$javascript item=js}
	<script type="text/javascript" src="js/{$js|escape:"javascript"}"></script>
{/foreach}
	<link rel="icon" type="image/png" href="favicon.png" />
</head>
<body>
<div id="container">
<div id="content">
