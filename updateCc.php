<?php
// updateCc.php
//
// update la base de données à partir de modifications d'infos utilisateurs
// Les valeurs sont passées en POST de l'url

/*
	TeamTime is a software to manage people working in team on a cyclic shift.
	Copyright (C) 2012 Manioul - webmaster@teamtime.me

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as
	published by the Free Software Foundation, either version 3 of the
	License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Require authenticated user
// L'utilisateur doit être admin pour accéder à cette page
$requireAdmin = true;

/*
 * Configuration de la page
 * Définition des include nécessaires
 */
	$conf['page']['include']['constantes'] = 1; // Ce script nécessite la définition des constantes
	$conf['page']['include']['errors'] = 1; // le script gère les erreurs avec errors.inc.php
	$conf['page']['include']['class_debug'] = 1; // La classe debug est nécessaire à ce script
	$conf['page']['include']['globalConfig'] = 1; // Ce script nécessite config.inc.php
	$conf['page']['include']['init'] = 1; // la session est initialisée par init.inc.php
	$conf['page']['include']['globals_db'] = 1; // Le DSN de la connexion bdd est stockée dans globals_db.inc.php
	$conf['page']['include']['class_db'] = 1; // Le script utilise class_db.inc.php
	$conf['page']['include']['session'] = 1; // Le script utilise les sessions par session.imc
	$conf['page']['include']['classUtilisateur'] = NULL; // Le sript utilise uniquement la classe utilisateur (auquel cas, le fichier class_utilisateur.inc.php
	$conf['page']['include']['class_utilisateurGrille'] = NULL; // Le sript utilise la classe utilisateurGrille
	$conf['page']['include']['class_cycle'] = NULL; // La classe cycle est nécessaire à ce script (remplace grille.inc.php
	$conf['page']['include']['smarty'] = NULL; // Smarty sera utilisé sur cette page
/*
 * Fin de la définition des include
 */

$conf['page']['elements']['firePHP'] = true;

require 'required_files.inc.php';

firePhpLog($_POST, 'POST');

// Ce script nécessite Pear::Mail et Pear::Mail_Mime
// pear install mail
// pear install Mail_Mime
//
require_once 'Mail.php';
require_once 'Mail/mime.php';


$err = '';

// Vérifie si un formulaire a été posté
if (array_key_exists('login', $_POST) && array_key_exists('nom', $_POST) && array_key_exists('email', $_POST)) {
	$to = $_POST['email'];
	$login = $_POST['login'];
	$passwd = $_POST['password'];
	$nom = $_POST['nom'];
	if (isset($_POST['sendmail']) && $_POST['sendmail'] == "svp") {
		$crlf = "\n";
		$message = sprintf("Bonjour %s,

Votre compte pour utiliser TeamTime a été créé.
Vous pouvez désormais y accéder sur :
%s/
à l'aide des identifiants suivants (gare aux majuscules/minuscules) :
login : %s
mot de passe : %s

Grâce à TeamTime, vous pouvez déposer vos congés, récup, stages où que
vous soyez et quand vous le souhaitez.
Vous pouvez également vérifier les prochaines vacations, et voir qui sera
présent.
Vous visualisez, aisément, les briefings à venir, la période de charge,
les vacances scolaires.
Vous suivez votre décompte de congés et de récup, à tout moment.
Vous accédez, également, à votre décompte d'heures très facilement.

Pour toute question, n'hésitez pas à contater le webmaster.
Mail : webmaster@teamtime.me
XMPP : manioul@teamtime.me
Friendica : https://titoux.info/profile/teamtime


Bonne utilisation.

++ ;)

--"
			, ucfirst($login)
			, dirname($_SERVER['HTTP_REFERER'])
			, $login
			, $passwd
		);
		$hdrs = array(
			'From'		=> "noreply@teamtime.me"
			,'Subject'	=> 'Création de votre compte TeamTime'
		);
		$mime = new Mail_mime(array(
			'eol'		=> $crlf
			,'head_charset'	=> 'utf-8'
			,'text_charset'	=> 'utf-8'
		));
		$mime->setTXTBody($message);
		$body = $mime->get();
		$hdrs = $mime->headers($hdrs);

		$mail =& Mail::factory('mail');
		if (TRUE === $mail->send($to, $hdrs, $body)) {
			$sql = sprintf("UPDATE `TBL_USERS` SET `login` = '%s', `email` = '%s', `sha1` = SHA1('%s') WHERE `nom` = '%s'", $_SESSION[db]->db_real_escape_string($login), $_SESSION[db]->db_real_escape_string($to), $_SESSION[db]->db_real_escape_string($login . $passwd), $_SESSION[db]->db_real_escape_string($nom));
			$_SESSION['db']->db_interroge($sql);
			$err = mysql_error();
			firePhpLog($sql, '$sql');
		} else {
			$err = "Échec : le mail n'a pas été envoyé...";
		}
	} else {
		$sql = sprintf("UPDATE `TBL_USERS` SET `login` = '%s', `email` = '%s', `sha1` = SHA1('%s') WHERE `nom` = '%s'", $_SESSION[db]->db_real_escape_string($login), $_SESSION[db]->db_real_escape_string($to), $_SESSION[db]->db_real_escape_string($login . $passwd), $_SESSION[db]->db_real_escape_string($nom));
		$_SESSION['db']->db_interroge($sql);
		$err = mysql_error();
		firePhpLog($sql, '$sql');
	}
}


if ($err != "") {
	print(nl2br(htmlspecialchars($err)));
} else {
	print htmlspecialchars("Mise à jour effectuée.");
}
?>
