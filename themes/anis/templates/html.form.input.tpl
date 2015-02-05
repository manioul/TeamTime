{* Smarty *}
{if isset($input.label)}<label for="{$input.name}">{$input.label}</label>{/if}
<input type="{$input.type}"{if isset($input.name)} name="{$input.name}"{/if}{if isset($input.id)} id="{$input.id}"{/if}{if isset($input.checked)} checked="checked"{/if}{if isset($input.placeholder)} placeholder="{$input.placeholder}"{/if}{if isset($input.value)} value="{$input.value}"{/if}{if isset($input.classe)} class="{$input.classe}"{/if}{if isset($input.title)} title="{$input.title}"{/if} />
