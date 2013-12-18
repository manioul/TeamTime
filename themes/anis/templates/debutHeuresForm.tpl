{* Smarty *}
<form id="fIntervalHeures" class="ng" method="POST" action="">
<ul>
<li>
<fieldset>
<ul>
<li>
<label for="dateDebut">Début des heures</label>
<input type="date" id="dateD" name="dateD" {if !empty($defaultD)}value="{$defaultD}" {/if}/>
</li>
<li>
<label for="dateFin">Fin des heures</label>
<input type="date" id="dateF" name="dateF" />
</li>
</fieldset>
</li>
<li>{* Dispos de l'agent *}
	<fieldset>
	<legend>Exclusions</legend>
	<ul>
	{foreach $aDispos as $dispo => $nom_long}
	<li>
	<label for="{$dispo}">{$nom_long}</label>
	<input type="checkbox" name="dispo[{$dispo}]" id="dispo{$dispo}" value="{$dispo}" {if !empty($checked.$dispo)}checked="checked" {/if}/>
	{/foreach}
	</ul>
	</fieldset>
</li>
<li>
<input type="submit" />
</li>
</ul>
</form>
