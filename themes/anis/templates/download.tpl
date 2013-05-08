{* Smarty *}
		<ul class="lien"><li><div><h1>Télécharger</h1>
			<form id="fDl" name="fDl" method="post" action="dl.php">
				<div id="dDl" class="desc boite">
				{html_radios name='v' values=$content.val output=$content.nam selected=$content.sel separator='<br />'}
				<input type="submit" class="button" value="charger" />
				</div>
			</form></div>
			</li>
		</ul>
	
