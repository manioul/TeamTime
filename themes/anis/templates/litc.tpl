{* Smarty *}
<div id="tce">
<h3>Liste des titres de congés déjà édités</h3>
<ul>
{foreach $titres as $titre}
<li><a href="litc.php?f={$titre.file}">Titre(s) de congés édité(s) {$titre.date} ({$titre.filesize})</a></li>
{/foreach}
</ul>
</div>
