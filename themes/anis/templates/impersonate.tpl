{* Smarty *}
<div id="dBriefing">
<form name="fImpersonate" id="fImpersonate" class="ng" method="POST" action="#">
<label for="uid">Choisissez un utilisateur et prenez sa place : </label>
{html_options name=uid options=$users}
<input type="submit" />
</form>
</div>
