{* Smarty *}
<div class="panel">
<form name="{$form.name}"{if isset($form.id)} id="{$form.id}"{/if}{if isset($form.classe)} class="{$form.classe}"{/if} method="{$form.method}" action="{$form.action}">
<fieldset><legend>{$form.legend}</legend>
<ul>
<li title="Nom qui apparaîtra sur la grille">
<label for="nc">Nom court</label>
<input type="text" name="nc" id="nc" />
</li>
<li>
<label for="nl">Description</label>
<input type="text" name="nl" id="nl" />
</li>
<li>
{include file="html.form.select.tpl" select=$form.jp}
<input type="hidden" name="jp" id="jp" value="all" />
</li>
<li>
{include file="html.form.select.tpl" select=$form.cp}
<input type="hidden" name="cp" id="cp" value="all" />
</li>
<li>
{include file="html.form.select.tpl" select=$form.pp}
<input type="hidden" name="pp" id="pp" value="all" />
</li>
<li>
{include file="html.form.select.tpl" select=$form.absence}
</li>
<li>
<fieldset><legend>Comptabilisation</legend>
<ul>
<li>
{include file="html.form.input.tpl" input=$form.isDispo}
</li>
<li id="neeCpt">
{include file="html.form.input.tpl" input=$form.needCompteur}
</li>
<li id="namCpt">
{include file="html.form.select.tpl" select=$form.typeDecompte}
<input type="text" name="dp" id="dp" value="" />
</li>
</ul>
</fieldset>
</li>
<li>
{include file="html.form.input.tpl" input=$form.validation}
</li>
</ul>
</fieldset>
</form>
</div>
