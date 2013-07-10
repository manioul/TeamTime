{* Smarty *}
<div id="impersonate">
<form name="fImpersonate" id="fImpersonate" method="post" action="#">
<label for="uid">Choisissez un utilisateur et prenez sa place : </label>
{html_options name=uid options=$users}
<input type="submit" />
</form>
</div>
