{* Smarty *}
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head profile="http://www.w3.org/2005/10/profile">
	<title>{if $titrePage}{$titrePage}{else}Grille{/if}</title>
	<meta http-equiv="Content-Language"    content="{$language}" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type"  content="text/css" />
	<meta http-equiv="Content-Type"        content="application/xhtml+xml; charset=utf-8" />
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
<div id="contenu">
