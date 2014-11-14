<?php
// ajax.php
//
/**
 * Traitement des requêtes ajax.
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


ob_start(); // Obligatoire pour firePHP

/*
 * Configuration de la page
 * Définition des include nécessaires
 */
	$conf['page']['include']['constantes'] = 1; // Ce script nécessite la définition des constantes
	$conf['page']['include']['errors'] = NULL; // le script gère les erreurs avec errors.inc.php
	$conf['page']['include']['class_debug'] = 1; // La classe debug est nécessaire à ce script
	$conf['page']['include']['globalConfig'] = 1; // Ce script nécessite config.inc.php
	$conf['page']['include']['init'] = 1; // la session est initialisée par init.inc.php
	$conf['page']['include']['globals_db'] = 1; // Le DSN de la connexion bdd est stockée dans globals_db.inc.php
	$conf['page']['include']['class_db'] = 1; // Le script utilise class_db.inc.php
	$conf['page']['include']['session'] = 1; // Le script utilise les sessions par session.imc
	$conf['page']['include']['classUtilisateur'] = NULL; // Le sript utilise uniquement la classe utilisateur (auquel cas, le fichier class_utilisateur.inc.php
	$conf['page']['include']['class_utilisateurGrille'] = 1; // Le sript utilise la classe utilisateurGrille
	$conf['page']['include']['class_cycle'] = 1; // La classe cycle est nécessaire à ce script (remplace grille.inc.php
	$conf['page']['include']['class_email'] = 1; // La classe Email est nécessaire à ce script (remplace grille.inc.php
	$conf['page']['include']['class_menu'] = NULL; // La classe menu est nécessaire à ce script
	$conf['page']['include']['smarty'] = NULL; // Smarty sera utilisé sur cette page
	$conf['page']['compact'] = NULL; // Compactage des scripts javascript et css
	$conf['page']['include']['bibliothequeMaintenance'] = NULL; // La bibliothèque des fonctions de maintenance est nécessaire
/*
 * Fin de la définition des include
 */


/*
 * Configuration de la page
 */
// Définit la valeur de $DEBUG pour le script
// on peut activer le debug sur des parties de script et/ou sur certains scripts :
// $DEBUG peut être activer dans certains scripts de required et désactivé dans d'autres
	$DEBUG = false;

	/*
	 * Choix des éléments à afficher
	 */
	
	// Affichage du menu horizontal
	$conf['page']['elements']['menuHorizontal'] = false;
	// Affichage messages
	$conf['page']['elements']['messages'] = false;
	// Affichage du choix du thème
	$conf['page']['elements']['choixTheme'] = false;
	// Affichage du menu d'administration
	$conf['page']['elements']['menuAdmin'] = false;
	
	// éléments de debug
	
	// FirePHP
	$conf['page']['elements']['firePHP'] = true;
	// Affichage des timeInfos
	$conf['page']['elements']['timeInfo'] = $DEBUG;
	// Affichage de l'utilisation mémoire
	$conf['page']['elements']['memUsage'] = $DEBUG;
	// Affichage des WherewereU
	$conf['page']['elements']['whereWereU'] = $DEBUG;
	// Affichage du lastError
	$conf['page']['elements']['lastError'] = $DEBUG;
	// Affichage du lastErrorMessage
	$conf['page']['elements']['lastErrorMessage'] = $DEBUG;
	// Affichage des messages de debug
		$conf['page']['elements']['debugMessages'] = $DEBUG;



	// Utilisation de jquery
	$conf['page']['javascript']['jquery'] = false;
	// Utilisation de ajax
	$conf['page']['javascript']['ajax'] = false;
	// Utilisation de grille2.js.php
	$conf['page']['javascript']['grille2'] = false;
	// Utilisation de utilisateur.js
	$conf['page']['javascript']['utilisateur'] = false;

	// Feuilles de styles
	// Utilisation de la feuille de style general.css
	$conf['page']['stylesheet']['general'] = true;
	$conf['page']['stylesheet']['grille'] = false;
	$conf['page']['stylesheet']['grilleUnique'] = false;
	$conf['page']['stylesheet']['utilisateur'] = false;

	// Compactage des pages
	$conf['page']['compact'] = false;
	
/*
 * Fin de la configuration de la page
 */

require 'required_files.inc.php';


$err = "Formulaire non traité";

	$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("msg", "TRACE", "", "", "%s")'
		, $_SESSION['db']->db_real_escape_string(json_encode($_REQUEST)))
	);

if (sizeof($_REQUEST) > 0) {
	if (array_key_exists('w', $_POST)) {
		if ($_POST['w'] == "resetPwd") {
			// La chaîne 'email' est filtrée par la méthode resetPwd
			die(utilisateurGrille::resetPwd($_POST['email']));
		}
	}
	if (array_key_exists('MY_EDIT', $_SESSION)) {
		if (array_key_exists('id', $_POST)) {
			if (preg_match('/u(.+)d(\d{2,4}-\d{2}-\d{2,4})/', $_POST['id'], $array)) { // La date doit respecter les formats fr et us
				$date = new Date($array[2]);
				// Changer l'année d'un congé
				if (array_key_exists('y', $_POST) && $_POST['y'] == 1) {
					if ($_SESSION['utilisateur']->hasRole('teamEdit') || $_SESSION['utilisateur']->uid() == $array[1]) {
						$_SESSION['db']->db_interroge(sprintf("
							CALL toggleAnneeConge(%d, '%s')
							", $array[1]
							, $date->date()));
					}
				// Changer le statut d'un congé
				} elseif (array_key_exists('f', $_POST) && $_SESSION['utilisateur']->hasRole('teamEdit')) {
					$etat = ($_POST['f'] >= 2 ? 2 : 1);
					$sql = sprintf("
						UPDATE `TBL_VACANCES`
						SET `etat` = %d
						WHERE `sdid` = (SELECT `sdid`
							FROM `TBL_L_SHIFT_DISPO`
							WHERE `date` = '%s'
							AND `uid` = %d
							LIMIT 1)
						"
						, $etat
						, $date->date()
						, $array[1]);
					$_SESSION['db']->db_interroge($sql);
				}
			} else {
				$err = "Date inconnue";
			}
		}
	}
	if (array_key_exists('TEAMEDIT', $_SESSION)) {
		// Gestion de la configuration sur la grille
		if (array_key_exists('conf', $_POST) && array_key_exists('id', $_POST)) {
			if ($_POST['conf'] != 'W' && $_POST['conf'] != 'E') {
				$err = 'Conf inconnue...';
			} else {
				if (preg_match('/confa(\d{4})m(\d*)j(\d*)/', $_POST['id'], $array)) {
					firePhpLog($array, 'arr');
					$date = new Date(sprintf("%04d-%02d-%02d", $array[1], $array[2], $array[3]));
					$affectation = $_SESSION['utilisateur']->affectationOnDate($date);
					$sql = sprintf("
						UPDATE `TBL_GRILLE`
						SET `conf` = '%s'
						WHERE `readonly` = FALSE
						AND `date` BETWEEN '%s' AND '%s'
						AND `centre` = '%s'
						AND `team` = '%s'
						", $_POST['conf']
						, $date->date()
						, $date->addJours(Cycle::getCycleLength($affectation['centre'], $affectation['team'])-1)->date()
						, $affectation['centre']
						, $affectation['team']
					);

					$_SESSION['db']->db_interroge($sql);
					if ($_SESSION['db']->db_affected_rows() < Cycle::getCycleLength($affectation['centre'], $affectation['team'])) { // Le verrouillage ne verrouille pas les jours de REPOS, d'où un nombre de données affectées même lorsque la grille n'est pas modifiable
						$err = "Modification impossible...";
						$_SESSION['db']->db_interroge(sprintf('
							CALL messageSystem("Modification de la configuration impossible.", "DEBUG", "updateConf.php", "mod failed", "affected_rows:%d;shouldBe:%d;POST:%s;SESSION:%s")'
							, $_SESSION['db']->db_affected_rows()
							, Cycle::getCycleLength($affectation['centre'], $affectation['team'])
							, $_SESSION['db']->db_real_escape_string(json_encode($_POST))
							, $_SESSION['db']->db_real_escape_string(json_encode($_SESSION))
							)
						);
					} else {
						$err = mysql_error();
					}
					firePhpLog($sql, 'SQL');
				} else {
					$err = "Date inconnue";
				}

			}
		}
	}
	/*
	 * Actions des éditeurs
	 */
	if (array_key_exists('EDITEURS', $_SESSION)) {
		if (array_key_exists('form', $_REQUEST)) {
			/*
			 * Validation des utilisateurs ayant créés un compte.
			 * Les editeurs peuvent valider ou invalider l'inscription
			 * d'un nouvel utilisateur.
			 */
			if ($_REQUEST['form'] === 'CU') {
				// Suppression des comptes créés par des inconnus (confirmUser.php)
				//
				// id contient l'identifiant de l'entrée dans la table TBL_SIGNUP_ON_HOLD
				if (array_key_exists('submit', $_REQUEST) && $_REQUEST['submit'] == "infirm") {
					if (array_key_exists('id', $_REQUEST)) {
						// On précise centre et équipe pour éviter
						// la suppression d'inscription dans
						// d'autres équipes que celle de l'utilisateur
						$_SESSION['db']->db_interroge(sprintf(
							"DELETE FROM `TBL_SIGNUP_ON_HOLD`
							WHERE `id` = %d
							AND `centre` = '%s'
							AND `team` = '%s'
							", $_REQUEST['id']
							, $_SESSION['utilisateur']->centre()
							, $_SESSION['utilisateur']->team()
						));
						die(htmlspecialchars("Utilisateur Supprimé"));
					}
				} elseif (array_key_exists('submit', $_REQUEST) && $_REQUEST['submit'] == "confirm" && array_key_exists('dateD', $_REQUEST) && array_key_exists('dateF', $_REQUEST) && array_key_exists('grade', $_REQUEST)) {
					// Confirme l'existence d'un utilisateur et création du compte utilisateur
					if (TRUE === utilisateurGrille::acceptUser($_REQUEST['id'], $_REQUEST['dateD'], $_REQUEST['dateF'], $_REQUEST['grade'])) {
						print(htmlspecialchars("Utilisateur accepté."));
					} else {
						print(htmlspecialchars("Le mail n'a pas été envoyé à l'utilisateur."));
					}
					exit;
				}
			}
			/*
			 * Recherche une liste des utilisateurs de TeamTime à partir des premières lettres du nom
			 */
			if ( $_REQUEST['form'] === 'LU' ) {
				$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Listage des utilisateurs dont le nom commence par...", "TRACE", "ajax.php", "listeUser", "%s")'
					, $_SESSION['db']->db_real_escape_string(json_encode($_REQUEST)))
				);
				$users = array();
				$sql = sprintf("
					SELECT `nom`, `prenom`, `uid`
					FROM `TBL_USERS`
					WHERE `nom` LIKE '%%%s%%'
					", $_SESSION['db']->db_real_escape_string($_REQUEST['nom'])
				);
				$result = $_SESSION['db']->db_interroge($sql);
				while($row = $_SESSION['db']->db_fetch_assoc($result)) {
					 $users[] = $row;
				}
				mysqli_free_result($result);
				if (sizeof($users) > 0) {
					die (json_encode($users));
				} else {
					die ('0');
				}
			}
			/*
			 * Remplit les données utilisateur
			 */
			if ( $_REQUEST['form'] === 'FU' ) {
				$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("Remplissage des champs de l\'utilisateur", "TRACE", "ajax.php", "fillUser", "%s")'
					, $_SESSION['db']->db_real_escape_string(json_encode($_REQUEST)))
				);
				/*$sql = sprintf("
					SELECT `uid`, `nom`, `prenom`, `email`
					FROM `TBL_USERS`
					WHERE `uid` = %d
					", (int) $_REQUEST['uid']
				);
				die(json_encode($_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($sql))));
				 */
				$user = new utilisateurGrille((int) $_REQUEST['uid']);
				$_SESSION['db']->db_interroge(sprintf('CALL messageSystem("FU objet utilisateurGrille", "TRACE", "", "", "%s")'
					, $_SESSION['db']->db_real_escape_string(json_encode(array($user->asJSON(), $_REQUEST['uid'])))
				));
				// FIXME impossible de retourner un utilisateurGrille avec json_encode... :@
				die($user->asJSON());
			}
		}
	}
	/*
	 * Actions des admins
	 */
	if (array_key_exists('ADMIN', $_SESSION)) {
		if (array_key_exists('id', $_POST)) {
			if (array_key_exists('op', $_POST)) {
				switch ($_POST['op']) { 
				case 'del':
					$err = $_SESSION['db']->db_interroge("
						DELETE FROM `TBL_L_SHIFT_DISPO`
						WHERE `sdid` = " . (int) $_POST['id']);
					$err .= $_SESSION['db']->db_interroge("
						DELETE FROM `TBL_VACANCES`
						WHERE `sdid` = " . (int) $_POST['id']);
					break;
				case 'upd':
					if ($_POST['t'] == 'l') {
						switch ($_POST['field']) {
						case 'pereq':
							$val = empty($_POST['val']) ? 'FALSE' : 'TRUE';
							// On met à jour le statut de péréquation à condition que la date ne soit pas nul
							// (lequel cas ne pourrait correspondre qu'à une péréq)
							$sql = sprintf("
								UPDATE `TBL_L_SHIFT_DISPO`
								SET `pereq` = %s
								WHERE `sdid` = %d
								AND `date` != '0000-00-00'
								", $val
								, $_POST['id']
							);
							$err = $_SESSION['db']->db_interroge($sql);
							break;
						}
					}
					break;
				}
				// Si les deux requêtes se sont bien passées, elles renvoient chacune 1
				// d'où $err === "11" si tout s'est bien passé
				if ($err === "11") die(nl2br(htmlspecialchars("Mise à jour effectuée")));
			}
		} elseif (array_key_exists('login', $_POST) && array_key_exists('nom', $_POST) && array_key_exists('email', $_POST)) {
		// Gestion du formulaire creationCompte.php
			// FIXME Les mails doivent être gérés par la classe Email
			$to = $_POST['email'];
			$login = $_POST['login'];
			$passwd = $_POST['password'];
			$nom = $_POST['nom'];
			if (isset($_POST['sendmail']) && $_POST['sendmail'] == "svp") {
				$sql = "SELECT `titre` AS `subject`
					, `texte`
					FROM `TBL_ARTICLES`
					WHERE `description` = 'account update'
					LIMIT 1";
				$row = $_SESSION['db']->db_fetch_assoc($_SESSION['db']->db_interroge($sql));
				$crlf = "\n";
				$message = sprintf($row['texte']
				, ucfirst($login)
				, dirname($_SERVER['HTTP_REFERER'])
				, $login
				, $passwd
				);
				$hdrs = array(
					'From'		=> "noreply@teamtime.me"
					,'Subject'	=> $row['subject']
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
					$sql = sprintf("
						UPDATE `TBL_USERS`
						SET `login` = '%s',
						`email` = '%s',
						`sha1` = SHA1('%s')
						WHERE `nom` = '%s'
						", $_SESSION['db']->db_real_escape_string($login)
						, $_SESSION['db']->db_real_escape_string($to)
						, $_SESSION['db']->db_real_escape_string($login . $passwd)
						, $_SESSION['db']->db_real_escape_string($nom)
				);
					$_SESSION['db']->db_interroge($sql);
					$err = mysql_error();
					firePhpLog($sql, '$sql');
				} else {
					$err = "Échec : le mail n'a pas été envoyé...";
				}
			} else {
				$sql = sprintf("
					UPDATE `TBL_USERS`
					SET `login` = '%s',
					`email` = '%s',
					`sha1` = SHA1('%s')
					WHERE `nom` = '%s'
					"
					, $_SESSION['db']->db_real_escape_string($login)
					, $_SESSION['db']->db_real_escape_string($to)
					, $_SESSION['db']->db_real_escape_string($login . $passwd)
					, $_SESSION['db']->db_real_escape_string($nom)
				);
				$_SESSION['db']->db_interroge($sql);
				$err = mysql_error();
				firePhpLog($sql, '$sql');
			}
			if ($err != "") {
				die((nl2br(htmlspecialchars($err))));
			} else {
				die(htmlspecialchars("Mise à jour effectuée."));
			}
		} else {
			exit;
		}
	}
}

if ($err != "") {
	print(nl2br(htmlspecialchars($err)));
} else {
	print htmlspecialchars("Mise à jour effectuée.");
}

/*
 * Informations de debug
 */
include 'debug.inc.php';
firePhpLog($conf, '$conf');
firePhpLog(debug::getInstance()->format(), 'format debug messages');
firePhpLog($javascript, '$javascript');
firePhpLog($stylesheet, '$stylesheet');


ob_end_flush(); // Obligatoire pour firePHP

?>
