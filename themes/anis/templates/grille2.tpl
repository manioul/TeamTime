{* Smarty *}
<div id="tgrille">
	<table id="grille">
{foreach $grille as $lines}{* Les deux premières lignes sont dans un thead, les suivantes dans le tbody *}
	{if $lines@first}<thead>{elseif $lines@iteration == 3}</thead>
	<tbody>{/if}
	<tr id="line{$lines@iteration}">
{foreach $lines as $value}
	{if $lines@iteration < 3}<th{else}<td{/if}{if isset($value.id)} id="{$value.id}"{/if}{if isset($value.classe)} class="{$value.classe}"{/if}{if isset($value.title)} title="{$value.title}"{/if}{if isset($value.style)} style="{$value.style}"{/if}{if isset($value.colspan)} colspan="{$value.colspan}"{/if}>{if isset($value.navigateur)}{* Construction d'un navigateur entre les cycles pour la case contenant l'année *}
	<div class="nav-prev"><a href="?dateDebut={$previousCycle}&amp;nbCycle={$nbCycle}" title="Reculer d'un cycle"><img src="{$image}" class="nav-prev" alt="&lt;" /></a></div>
	<div class="nav-present"><a href="?dateDebut={$presentCycle}&amp;nbCycle={$nbCycle}">{if isset($value.nom)}{$value.nom}{/if}</a></div>
	<div class="nav-next"><a href="?dateDebut={$nextCycle}&amp;nbCycle={$nbCycle}" title="Avancer d'un cycle"><img src="{$image}" class="nav-next" alt="&lt;" /></a></div>{elseif isset($value.vacation)}
	<div class="{$value.vacances}"></div>
	<div class="{$value.periodeCharge}"></div>
	<div class="{if !$value.briefing}no{/if}brief"{if isset($value.briefing)} title="{$value.briefing}"{/if}></div>
	<div class="dateGrille">{$value.jds}</div>
	<div class="dateGrille">{if $nbCycle > 1}<a href="affiche_grille.php?dateDebut={$value.date}&amp;nbCycle={$nbCycle}" title="Débuter l'affichage par ce cyle">{$value.jdm}</a>{else}{$value.jdm}{/if}</div>
	<div class="shift">{$value.vacation}</div>{elseif isset($value.nom)}{$value.nom}{/if}{if $lines@iteration < 3}</th>{else}</td>{/if}{/foreach}
</tr>
{if $lines@last}</tbody>{/if}{/foreach}
	</table>
</div>
<div class="panel" style="max-width:150px;margin-top:15px;"><button id="bHeures" class="bouton" onclick="addHeures();">Afficher les heures</button></div>
{*
	div qui contiendra la liste de valeurs à attribuer à une case de la grille
		Le contenu est construit dynamiquement, en fonction de la case à modifier
		*} 
		<div id="sandbox"></div>
{*	div qui contient un formulaire pour les remplacements *}
		<form id="fFormRemplacement" method="post" action="set_rempla.php">
		<div id="dFormRemplacement">
				<input type="hidden" name="uid" id="remplaUid" />
				<input type="hidden" name="Year" id="remplaYear" />
				<input type="hidden" name="Month" id="remplaMonth" />
				<input type="hidden" name="Day" id="remplaDay" />
			<table>
			<tr>
			<td>
				<label for="remplaNom">Nom&nbsp;:</label>
			</td><td>
				<input name="nom" id="remplaNom" type="text" />
			</td></tr><tr><td>
				<label for="remplaPhone">Téléphone&nbsp;:</label>
			</td><td>
				<input name="phone" id="remplaPhone" type="text" />
			</td></tr><tr><td>
				<label for="remplaEmail">remplaEmail&nbsp;</label>
			</td><td>
				<input name="email" id="remplaEmail" type="text" />
			</td></tr><tr><td>
				<label for="remplaAlert">Envoyer un mail&nbsp;:</label>
			</td><td>
				<input name="alert" id="remplaAlert" type="checkbox" />
			</td></tr><tr><td>
				<input type="reset" value="Effacer" />
			</td><td>
				<input type="submit" />
			</td></tr>
			</table>
		</div>
		</form>
{*	div qui contient un formulaire pour les infos supplémentaires *}
		<form id="fFormInfoSup" method="post" action="ajax.php" style="display:none;">
		<div id="dFormInfoSup">
				<input type="hidden" name="q" value="IS" />
				<input type="hidden" name="ajax" value="true" />
				<input type="hidden" name="cachemoi" value="1" />
				<input type="hidden" name="uid" id="infoSupUid" />
				<input type="hidden" name="Year" id="infoSupYear" />
				<input type="hidden" name="Month" id="infoSupMonth" />
				<input type="hidden" name="Day" id="infoSupDay" />
				<input name="info" id="infoSupNom" type="text" placeholder="Description" />
				<input type="reset" value="Effacer" class="bouton" />
				<input type="submit" />
		</div>
		</form>
