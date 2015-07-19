<?php
// class_mail.inc.php
//
/**
 * Permet d'envoyer des emails aux utilisateurs.
 */

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

set_include_path(implode(PATH_SEPARATOR, array(realpath('.'), get_include_path())));

require_once 'class_debug.inc.php';
require_once 'config.inc.php';
require_once 'class_db.inc.php';

/**
 * Ce script nécessite Pear::Mail et Pear::Mail_Mime.
 *
 * pear install mail
 * pear install Mail_Mime
 */

require_once 'Mail.php';
require_once 'Mail/mime.php';

class Email {
	private $from;
	private $head_charset = 'utf-8';
	private $text_charset = 'utf-8';
	private $to;
	private $subject;
	private $content;
	private $err = NULL;
	private $crlf = "\n";
	/**
	 * Méthode statique pour envoyer rapidement un email.
	 *
	 * @param string $to adresse du destinataire du mail
	 * @param string $subject sujet du mail
	 * @param string $content contenu du mail
	 *
	 * @return boolean TRUE si l'envoi s'est bien passé, FALSE sinon
	 */
	public static function QuickMail($to, $subject, $content) {
		$hdrs = array(
			'From'		=> $GLOBALS['emailsender']
			,'Subject'	=> $subject
			,'Return-Path'	=> $GLOBALS['emailsender']
		);
		$mime = new Mail_mime(array(
			'eol'		=> "\n"
			,'head_charset'	=> 'utf-8'
			,'text_charset'	=> 'utf-8'
		));
		$mime->setTXTBody(utf8_decode($content));
		$body = $mime->get();
		$hdrs = $mime->headers($hdrs);

		$mail =& Mail::factory('mail');
		if (TRUE === $mail->send($to, $hdrs, $body)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	/**
	 * Méthode statique pour envoyer rapidement
	 * un email à partir d'un contenu provenant de la bdd.
	 *
	 * @param array string[] $array un tableau contenant les éléments nécessaires
	 * 			à la construction du contenu du message.
	 * - $array['description'] la description permettant de
	 *   retrouver le contenu du mail
	 * - $array['to'] si il existe est le destinataire du mail
	 * - ...
	 * Les autres paramètres inclus dans le tableau dépendent du type
	 * de mail à envoyer.
	 * Cf les méthodes privées correspondantes...
	 *
	 * @return boolean TRUE si l'envoi s'est bien passé, FALSE sinon
	 */
	public static function QuickMailFromArticle($array) {
		$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("paramètre de la méthode.", "TRACE", "%s", "param", "%s")'
			, __METHOD__
			, $_SESSION['db']->db_real_escape_string(json_encode($array)))
		);
		if (!array_key_exists('description', $array)) {
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("description manquante pour le mail.", "DEBUG", "%s", "param description expected", "%s")'
				, __METHOD__
				, $_SESSION['db']->db_real_escape_string(json_encode($array)))
			);
			return FALSE;
		}
		$array['proto'] = (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] != '' && $_SERVER['HTTPS'] != 'off') ? 's' : '';
		if ($array['description'] == 'account accepted') {
			$row = Email::accountAccepted($array);
		} elseif ($array['description'] == 'reset password') {
			$row = Email::resetPassword($array);
		} elseif ($array['description'] == 'password updated') {
			$row = Email::passwordUpdated($array);
		} elseif ($array['description'] == 'account updated') {
			$row = Email::accountUpdated($array);
		} elseif ($array['description'] == 'account created') {
			$row = Email::accountCreated($array);
		} else {
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Type de message inconnu", "DEBUG", "%s", "unknown message", "%s")'
				, __METHOD__
				, $_SESSION['db']->db_real_escape_string(json_encode($array)))
			);
			return FALSE;
		}
		$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Tentative d\'envoi d\'un mail.", "DEBUG", "%s", "sending mail", "%s")'
			, __METHOD__
			, $_SESSION['db']->db_real_escape_string(json_encode($row)))
		);
		if (array_key_exists('to', $row) && array_key_exists('subject', $row) && array_key_exists('content', $row)) {
			return Email::QuickMail($row['to'], $row['subject'], $row['content']);
		} else {
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Le message n\'a pas été envoyé car il manquait des valeurs dans le tableau array", "DEBUG", "%s", "short", "%s")'
				, __METHOD__
				, $_SESSION['db']->db_real_escape_string(json_encode($row)))
			);
			return FALSE;
		}	
	}
	/**
	 * account accepted : la demande de création de compte par un utilisateur a été acceptée.
	 *
	 * Ce message est envoyé à un utilisateur qui a demandé à créer un compte sur TeamTime
	 * via l'interface (bouton s'enregistrer), lorsque sa demande a été acceptée par un editeurs.
	 * Ce message demande à l'utilisateur de créer son login et son mot de passe de connexion
	 * pour TeamTime en lui fournissant un lien.
	 *
	 * @param array $array Le paramètre $array comprend :
	 * - id			=> l'identifiant (doit être int) référent l'utilisateur dans TBL_SIGNUP_ON_HOLD
	 *
	 * @return array 
	 * - to			=> destinataire du mail
	 * - subject		=> sujet du mail
	 * - content		=> texte du mail
	 *
	 */
	private static function accountAccepted($array) {
		if (!array_key_exists('id', $array) && is_int($array['id'])) {
			ob_start();
			var_dump($array);
			$array['dump'] = ob_get_contents();
			ob_end_clean();
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Le message n\'a pas été envoyé car il manquait des valeurs dans le tableau array", "DEBUG", "%s", "short", "%s")'
				, __METHOD__
				, $_SESSION['db']->db_real_escape_string(json_encode($array)))
			);
		}
		$id = $array['id'];
		$sql = "SELECT `email`, `url` AS `k`, `nom`, `prenom`, `titre` AS `subject`, `texte`
			FROM `TBL_SIGNUP_ON_HOLD`,
			`TBL_ARTICLES`
			WHERE `id` = $id
			AND `description` = 'account accepted'";
		$row = $_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($sql));
		return array(
			'to'		=> $row['email']
			, 'subject'	=> $row['subject']
			, 'content'	=> sprintf($row['texte']
			, ucfirst($row['prenom'])
			, $array['proto']
			, $_SERVER['SERVER_NAME']
			, $row['k']
			)
		);
	}
	/**
	 * reset password : un utilisateur a demandé à saisir un nouveau mot de passe.
	 *
	 * Ce message indique un lien à l'utilisateur pour qu'il puisse saisir un nouveau
	 * mot de passe de connexion à TeamTime.
	 *
	 * @param array $array Le paramètre $array comprend :
	 * - k			=> la clé passée en paramètre de l'url
	 * - email		=> le mail du destinataire
	 *
	 * @return array 
	 * - to			=> destinataire du mail
	 * - subject		=> sujet du mail
	 * - content		=> texte du mail
	 *
	 */
	private static function resetPassword($array) {
		if (!array_key_exists('k', $array) || !array_key_exists('email', $array)) {
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Le message n\'a pas été envoyé car il manquait des valeurs dans le tableau array", "DEBUG", "%s", "short", "%s")'
				, __METHOD__
				, $_SESSION['db']->db_real_escape_string(json_encode($array)))
			);
		}
		$sql = sprintf("SELECT `email`, `prenom`, `titre` AS `subject`, `texte`
			FROM `TBL_USERS`,
			`TBL_ARTICLES`
			WHERE `email` = '%s'
			AND `description` = 'reset password'
			", $_SESSION['db']->db_real_escape_string(filter_var(trim($array['email']), FILTER_SANITIZE_EMAIL))
		);
		$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Requête de construction du mail", "DEBUG", "%s", "short", "%s")'
			, __METHOD__
			, $_SESSION['db']->db_real_escape_string($sql))
		);
		$row = $_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($sql));
		return array(
			'to'		=> $row['email']
			, 'subject'	=> $row['subject']
			, 'content'	=> sprintf($row['texte']
			, ucfirst($row['prenom'])
			, $array['proto']
			, $_SERVER['SERVER_NAME']
			, $array['k']
			, $array['proto']
			, $_SERVER['SERVER_NAME']
			, $array['k']
			)
		);
	}
	/**
	 * password updated : Ce message confirme que l'utilisateur a mis à jour son mot de passe.
	 *
	 * @param array $array Le paramètre $array comprend :
	 * - to			=> l'adresse mail de l'utilisateur dans TBL_USERS
	 * l'adresse mail doit avoir été filtrée, car la présente méthode ne la filtre pas
	 *
	 * @return array 
	 * - to			=> destinataire du mail
	 * - subject		=> sujet du mail
	 * - content		=> texte du mail
	 *
	 */
	private static function passwordUpdated($array) {
		$sql = sprintf("
			SELECT `prenom`,
			`login`,
			`titre` AS `subject`,
			`texte`
			FROM `TBL_USERS`,
			`TBL_ARTICLES`
			WHERE `email` = '%s'
			AND `description` = 'password updated'
			", $array['to']
		);
		$row = $_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($sql));
		return array(
			'to'		=> $array['to']
			, 'subject'	=> $row['subject']
			, 'content'	=> sprintf($row['texte']
					, ucfirst($row['prenom'])
					, $row['login']
			)
		);
	}
	/**
	 * account updated
	 *
	 * Ce message devrait être évité dans la mesure où
	 * il contient le login et le mot de passe.
	 *
	 * @param array $array Le paramètre $array comprend :
	 * - uid		=> l'identifiant (doit être int) référent l'utilisateur dans TBL_USERS
	 *
	 * @return array 
	 * - to			=> destinataire du mail
	 * - subject		=> sujet du mail
	 * - content		=> texte du mail
	 *
	 */
	private static function accountUpdated($array) {
		if (!array_key_exists('uid', $array) && is_int($array['uid'])) {
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Le message n\'a pas été envoyé car il manquait des valeurs dans le tableau array", "DEBUG", "%s", "short", "%s")'
				, __METHOD__
				, $_SESSION['db']->db_real_escape_string(json_encode($array)))
			);
		}
		$uid = $array['uid'];
		$sql = "SELECT `email`, `prenom`, `login`, `titre` AS `subject`, `texte`
			FROM `TBL_USERS`,
			`TBL_ARTICLES`
			WHERE `uid` = $uid
			AND `description` = 'account updated'";
		$row = $_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($sql));
		return array(
			'to'		=> $row['email']
			, 'subject'	=> $row['subject']
			, 'content'	=> sprintf($row['texte']
					, ucfirst($row['prenom'])
					, $array['proto']
					, $_SERVER['SERVER_NAME']
					, $array['password']
					)
		);
	}
	/**
	 * account created : Ce message est envoyé à un utilisateur lorsqu'un editeur lui a créé un compte.
	 *
	 *
	 * @param array $array Le paramètre $array comprend :
	 * - id			=> l'identifiant (doit être int) référent l'utilisateur dans TBL_SIGNUP_ON_HOLD
	 *
	 * @return array 
	 * - to			=> destinataire du mail
	 * - subject		=> sujet du mail
	 * - content		=> texte du mail
	 *
	 */
	private static function accountCreated($array) {
		if (!array_key_exists('id', $array) && is_int($array['id'])) {
			$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Le message n\'a pas été envoyé car il manquait des valeurs dans le tableau array", "DEBUG", "%s", "short", "%s")'
				, __METHOD__
				, $_SESSION['db']->db_real_escape_string(json_encode($array)))
			);
		}
		$id = $array['id'];
		$sql = "SELECT `email`, `prenom`, `titre` AS `subject`, `texte`
			FROM `TBL_SIGNUP_ON_HOLD`,
			`TBL_ARTICLES`
			WHERE `id` = $id
			AND `description` = 'account created'";
		$row = $_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($sql));
		return array(
			'to'		=> $row['email']
			, 'subject'	=> $row['subject']
			, 'content'	=> sprintf($row['texte']
					, ucfirst($row['prenom'])
					, $array['proto']
					, $_SERVER['SERVER_NAME']
					, $k
					)
		);
	}
// Constructeur
	public function __construct($param = NULL) {
		$this->from = $conf['email']['sender'];
		if (is_array($param)) {
			foreach ($param as $key => $value) {
				if (method_exists($key)) {
					$this->$key($value);
				} else {
					$this->$key = $value;
				}
			}
		}
	}
	public function __destruct() {
		return true;
	}
// Accesseurs
	public function from($param = NULL) {
		if (!is_null($param)) {
			if (FALSE !== ($param = filter_var(trim($param), FILTER_SANITIZE_EMAIL))) {
				$this->from = $param;
			} else {
				// Erreur d'adresse mail
				$this->err = 1;
			}
		}
	}
	public function to($param = NULL) {
		if (!is_null($param)) {
			if (FALSE !== ($param = filter_var(trim($param), FILTER_SANITIZE_EMAIL))) {
				$this->to = $param;
			} else {
				// Erreur d'adresse mail
				$this->err = 2;
			}
		}
	}
	public function head_charset($param = NULL) {
		if (!is_null($param)) {
			$this->head_charset = $param;
		}
	}
	public function text_charset($param = NULL) {
		if (!is_null($param)) {
			$this->text_charset = $param;
		}
	}
	public function subject($param = NULL) {
		if (!is_null($param)) {
			$this->subject = $param;
		}
	}
	public function content($param = NULL) {
		if (!is_null($param)) {
			$this->content = $param;
		}
	}
// Actions
	/*
	 * Envoie le mail
	 */
	public function sendMail() {
		$mime = new Mail_mime(array(
			'eol'		=> $this->crlf
			,'head_charset'	=> $this->head_charset
			,'text_charset'	=> $this->text_charset
		));
		$hdrs = array(
			'From'		=> $this->from
			,'Subject'	=> $this->subject
		);
		$mime->setTXTBody($this->content);
		if (is_null($this->err)) {
			$mail =& Mail::factory('mail');
			if (TRUE === $mail->send($this->to, $mime->headers($hdrs), $mime->get())) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return $this->err;
		}
	}
}
