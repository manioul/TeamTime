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
<li>
<input type="submit" name="recalc" value="Recalculer" />
</li>
</ul>
</form>
