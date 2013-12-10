{* Smarty *}
<form id="fDistribHeures" method="POST" action="distribHeures.php">
<fieldset>
<ul>
<li>
<fieldset>
<ul>
<li>{* Dispos de l'agent *}
	<fieldset>
	<legend>Occupation de l'agent</legend>
	<ul>
	{foreach $aDispos as $dispo => $nom_long}
	<li>
	<label for="{$dispo}">{$nom_long}</label>
	<input type="checkbox" name="dispo[{$dispo}]" id="dispo{$dispo}" value="{$dispo}" />
	{/foreach}
	</ul>
	</fieldset>
</li>
<li>{* Jour du cycle *}
	<fieldset id="joursDuCycle">
	<legend>Jour du cycle</legend>
	<ul>
	{foreach $aCycle as $cyc}
	<li>
	<label for="{$cyc}">{$cyc}</label>
	<input type="checkbox" name="cycle[{$cyc}]" id="cycle{$cyc}" value="{$cyc}" />
	{/foreach}
	</ul>
	</fieldset>
</li>
</ul>
</fieldset>
</li>
<li>
<fieldset>
<ul>
<li>{* Allocation des heures *}
	<fieldset>
	<legend>Allocation des heures</legend>
	<ul>
	<li>
	<input type="radio" name="fixed" id="shared" value="shared" onFocus="showDispatch();" />
	<label for="shared">Heures partagées</label>
	</li>
	<li>
	<input type="radio" name="fixed" id="fixed" value="fixed" checked="checked" onFocus="showDispatch();" />
	<label for="fixed">Heures fixes</label>
	</li>
	<li> {* Saisie du nombre d'heures *}
	<label for="nbHeures">Heures allouées</label>
	<input type="text" name="nbHeures" id="nbHeures" placeholder="Nombre d'heures" class="hInput" />
	</li>
	</ul>
	</fieldset>
</li>
<li>{* Type d'heures *}
	<fieldset>
	<legend>Type d'heures</legend>
	<ul>
	{foreach $aType as $type}
	<li>
	<input type="radio" name="type" id="type{$type}" value="{$type}"{if $type=='norm'}checked="checked"{/if} />
	<label for="{$type}">{$type}</label>
	{/foreach}
	</ul>
	</fieldset>
</li>
</ul>
</fieldset>
</li>
<li>
<fieldset>
<ul>
<li>{* Grade *}
	<fieldset id="grades">
	<legend>Grades</legend>
	<ul>
	{foreach $aEnum as $grade}
	<li><label for="grade{$grade}">{$grade}</label>
	<input type="checkbox" name="grade[{$grade}]" id="grade{$grade}" value="{$grade}"{if !empty($checked.$grade)} checked="checked"{/if} />
	</li>
	{/foreach}
	</ul>
	</fieldset>
</li>
</ul>
</fieldset>
</li>
<li>
<input type="submit" class="bouton" />
</li>
</ul>
</fieldset>
</form>
