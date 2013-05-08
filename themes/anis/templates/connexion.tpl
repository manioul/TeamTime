{* Smarty *}
		<ul class="lien"><li><div><h1>Connexion</h1><br />
			<form id="fConnexion" method="post" action="logon.php">
				<div id="dConnexion" class="boite">
					<input class="" type="text" id="login" name="login" />
					<input class="" type="password" id="pwd" name="pwd" />
					<input class="button" type="submit" id="connex" name="connex" value="Connexion" />
					<input type="hidden" name="salt" value="{$content.salt}" />
				{* <a href="#">S'inscrire</a> *}
				</div>
			</form></div>
		</li></ul>
