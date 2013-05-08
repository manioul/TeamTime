{* Smarty *}
{* Affichage des éléments de menu *}
	<li id="{$id}_{$key}" class="">
		<a href="{$elem->lien()}">{$elem->titre()}</a>
		{if ! is_null($elem->sousmenu())}{include file='menuHor.tpl' arbre=$elem->submenu()->arbre() menu=$elem->submenu() class=''}{/if}
	</li>
