{* Smarty *}
{* template d'affichage et de modification des informations d'un utilisateur *}
<div id="monCompte">
<form name="fContact" id="fContact" class="ng" method="POST" action="ajoutUtilisateur.php">
<fieldset><legend>Ajout d'un utilisateur</legend>
<ul>
<li>
<label for="nom">nom : </label><input type="text" name="nom" id="nom" />
</li>
<li>
<label for="prenom">prénom : </label><input type="text" name="prenom" id="prenom" />
</li>
<li>
<label for="email">email : </label><input type="email" name="email" id="email" placeholder="monadresse@email.com" />
</li>
<li>
<fieldset><legend>Credentials</legend>
<ul>
<li>
<label for="login">login</label><input type="text" name="login" id="login" />
</li>
<li>
<label for="password">mot de passe</label><input type="password" name="password" id="password" />
</li>
</ul>
</fieldset>
</li>
<li>
<fieldset><legend>Affectation</legend>
<ul>
<li>
{include file="html.form.select.tpl" select=$centres}
</li>
<li>
{include file="html.form.select.tpl" select=$teams}
</li>
<li>
{include file="html.form.select.tpl" select=$grades}
</li>
<li>
<label for="dateD">Date d'arrivée</label><input type="date" name="dateD" id="dateD" />
</li>
<li>
<label for="dateF">Date de fin</label><input type="date" name="dateF" id="dateF" />
</li>
</ul>
</fieldset>
</li>
</ul>
</fieldset>

<input type="submit" class="bouton" id="submitContact" value="Mettre à jour" />

<fieldset><legend>Administration</legend>
<ul>
<li>
{include file="html.form.select.tpl" select=$availablePages}
</li>
<li>
<label for="locked">locked : </label><input type="checkbox" id="locked" name="locked" />
</li>
<li>
<label for="actif">actif : </label><input type="checkbox" id="actif" name="actif" checked="checked" />
</li>
<li>
<label for="totd">tip of the day : </label><input type="checkbox" id="totd" name="totd" />
</li>
<li>
<label for="poids">poids : </label><input type="text" name="poids" id="poids" />
</li>
<li>
<label for="sendmail" title="Informe l'utilisateur sur le moyen de se connecter à TeamTime">Envoyer un mail</label><input type="checkbox" checked="checked" name="sendmail" />
</li>
</ul>
</fieldset>

<input type="submit" class="bouton" id="submitContact" value="Mettre à jour" />

</form>

</div><!-- Fin div #utilisateur -->
