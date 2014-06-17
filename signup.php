<?php
// signup.php
//
// Enregistrement sur TeamTime

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
	$conf['page']['include']['class_utilisateurGrille'] = 1; // Le sript utilise la classe utilisateurGrille
	$conf['page']['include']['class_affectation'] = 1; // Le sript utilise la classe Affectation
	$conf['page']['include']['class_cycle'] = NULL; // La classe cycle est nécessaire à ce script (remplace grille.inc.php
	$conf['page']['include']['class_menu'] = NULL; // La classe menu est nécessaire à ce script
	$conf['page']['include']['smarty'] = NULL; // Smarty sera utilisé sur cette page
	$conf['page']['compact'] = false; // Compactage des scripts javascript et css
	$conf['page']['include']['bibliothequeMaintenance'] = false; // La bibliothèque des fonctions de maintenance est nécessaire
/*
 * Fin de la définition des include
 */


/*
 * Configuration de la page
 */
        $conf['page']['titre'] = ""; // Le titre de la page
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
	$conf['page']['stylesheet']['general'] = false;
	$conf['page']['stylesheet']['grille'] = false;
	$conf['page']['stylesheet']['grilleUnique'] = false;
	$conf['page']['stylesheet']['utilisateur'] = false;
/*
 * Fin de la configuration de la page
 */

require 'required_files.inc.php';

$sql = "SELECT `nom`, `type`
	FROM `TBL_CONFIG_AFFECTATIONS`
	WHERE `type` = 'centre'
	OR `type` = 'team'";
$result = $_SESSION['db']->db_interroge($sql);
while($row = $_SESSION['db']->db_fetch_assoc($result)) {
	if ($row['type'] == 'centre') {
		$centres[] = $row['nom'];
	} else {
		$teams[] = $row['nom'];
	}
}
mysqli_free_result($result);

$row = array();
if (sizeof($_POST) > 0) {
	$traitement = TRUE;
	if (array_key_exists('iNom', $_POST)) {
		if (preg_match('/^[a-zA-Z]+$/', trim($_POST['iNom']))) {
			$row['nom'] = trim($_POST['iNom']);
		} else {
			$traitement = FALSE;
			printf("nom : '%s'", trim($_POST['iNom']));
		}
	} else {
		$traitement = FALSE;
	}
	if (array_key_exists('iPrenom', $_POST)) {
		if (preg_match('/^[a-zA-Z]+$/', trim($_POST['iNom']))) {
			$row['prenom'] = trim($_POST['iPrenom']);
		} else {
			print("prenom");
			$traitement = FALSE;
		}
	} else {
		$traitement = FALSE;
	}
	if (array_key_exists('iEmail', $_POST)) {
		if ($row['email'] = filter_var(trim($_POST['iEmail']), FILTER_SANITIZE_EMAIL)) {
		} else {
			$traitement = FALSE;
			print("email");
		}
	} else {
		$traitement = FALSE;
	}
	if (array_key_exists('centre', $_POST)) {
		if (in_array(trim($_POST['centre']), $centres)) {
			$centre = trim($_POST['centre']);
		} else {
			$traitement = FALSE;
		}
	} else {
		$traitement = FALSE;
	}
	if (array_key_exists('team', $_POST)) {
		if (in_array(trim($_POST['team']), $teams)) {
			$team = trim($_POST['team']);
		} else {
			$traitement = FALSE;
		}
	} else {
		$traitement = FALSE;
	}
}

$utilisateur = new utilisateurGrille($row);
if ($utilisateur->emailAlreadyExistsInDb) {
	die ("Un compte existe déjà avec cette adresse.");
}

if (false === $traitement) {
	die("Votre compte n'a pas été créé. Vérifiez les données saisies.");
}
$sql = sprintf("INSERT INTO TBL_SIGNUP_ON_HOLD
	(`nom`, `prenom`, `email`, `centre`, `team`)
	VALUES
	('%s', '%s', '%s', '%s', '%s')
	", $_SESSION['db']->db_real_escape_string($row['nom'])
	, $_SESSION['db']->db_real_escape_string($row['prenom'])
	, $_SESSION['db']->db_real_escape_string($row['email'])
	, $_SESSION['db']->db_real_escape_string($centre)
	, $_SESSION['db']->db_real_escape_string($team)
);
$_SESSION['db']->db_interroge($sql);
print("Votre compte a été créé. Vous recevrez un mail lorsque votre demande aura été traitée.");

?>
