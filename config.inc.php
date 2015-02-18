<?php
// config.inc.php

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

// Définit le language utilisé
$GLOBALS['language'] = 'fr';

// Définit la valeur de $DEBUG pour le script
// on peut activer le debug sur des parties de script et/ou sur certains scripts :
// $DEBUG peut être activer dans certains scripts de required et désactivé dans d'autres
$DEBUG = false;

// Cookie de session
	$conf['session_cookie']['name'] = 'TeamTimeMe';
	$conf['session_cookie']['lifetime'] = 0;
	$conf['session_cookie']['path'] = '/';
	$conf['session_cookie']['domain'] = 'dev.web'; // Le domaine hébergeant TeamTime
	$conf['session_cookie']['secure'] = FALSE;

// Configurations relatives au thème
	$conf['theme']['default']	 	= 'anis'; // Nom du thème par défaut
	$conf['theme']['current']		= $conf['theme']['default'];
	$conf['theme']['cookieLifeTime'] 	= time() + 60 * 60 * 24 * 365; // La durée du cookie pour le thème est de 365 jours
	// Gestion et enregistrement du thème dans un cookie
		// Les noms de thèmes sont uniquement composés de lettres, de chiffres ou du caractère underscore
		if (array_key_exists('theme', $_COOKIE) && preg_match("/^[a-zA-Z0-9_]+$/", $_COOKIE['theme'])) {
			$conf['theme']['current'] = $_COOKIE['theme'];
		}
		if (array_key_exists('theme', $_REQUEST) && preg_match("/^[a-zA-Z0-9_]+$/", $_REQUEST['theme'])) {
			$conf['theme']['current'] = $_REQUEST['theme'];
		}
		setcookie('theme', $conf['theme']['current'], $conf['theme']['cookieLifeTime'], $conf['session_cookie']['path'], $conf['session_cookie']['domain'], $conf['session_cookie']['secure']);

// Gestion des emails
	// adresse mail d'expédition des emails
	$GLOBALS['emailsender'] = 'noreply@mail.com';

/* 
 * Chargement des valeurs par défaut
 * Les options peuvent être surchargées dans les scripts
 */
	// Définition du titre de la page
	if (empty($conf['page']['titre'])) $conf['page']['titre'] = 'TeamTime';
	// Affichage du menu administration
	if (empty($conf['page']['elements']['menuAdmin'])) $conf['page']['elements']['menuAdmin'] = false;
	// Affichage messages
	if (empty($conf['page']['elements']['messages'])) $conf['page']['elements']['messages'] = false;
	// Ajout des tips of the day
	if (empty($conf['page']['elements']['tipoftheday'])) $conf['page']['elements']['tipoftheday'] = false;
	// Affichage du menu horizontal
	if (empty($conf['page']['elements']['menuHorizontal'])) $conf['page']['elements']['menuHorizontal'] = false;
	// Affichage de l'élément administration
	if (empty($conf['page']['elements']['administration'])) $conf['page']['elements']['administration'] = false;
	// Affichage de l'élément permettant de choisir le thème
	if (empty($conf['page']['elements']['choixTheme'])) $conf['page']['elements']['choixTheme'] = false;
	// Affichage des messages de débogage
	if (empty($conf['page']['elements']['debugMessages'])) $conf['page']['elements']['debugMessages'] = false;
	// Utilisation de firePHP
	if (empty($conf['page']['elements']['firePHP'])) $conf['page']['elements']['firePHP'] = $DEBUG;
	// Affichage du numéro de la dernière erreur
	if (empty($conf['page']['elements']['lastError'])) $conf['page']['elements']['lastError'] = false;
	// Affichage du dernier message d'erreur
	if (empty($conf['page']['elements']['lastErrorMessage'])) $conf['page']['elements']['lastErrorMessage'] = false;
	// Affichage de l'utilisation mémoire du script
	if (empty($conf['page']['elements']['memUsage'])) $conf['page']['elements']['memUsage'] = false;
	// Affichage du décompte temps 
	if (empty($conf['page']['elements']['timeInfo'])) $conf['page']['elements']['timeInfo'] = false;
	// Affichage des infos de débogage whereWhereU
	if (empty($conf['page']['elements']['whereWereU'])) $conf['page']['elements']['whereWereU'] = false;

	// chargement du script de gestion ajax
	if (empty($conf['page']['javascript']['ajax'])) $conf['page']['javascript']['ajax'] = false;
	// chargement du script de gestion grille2
	if (empty($conf['page']['javascript']['grille2'])) $conf['page']['javascript']['grille2'] = false;
	// chargement du script de gestion administration
	if (empty($conf['page']['javascript']['administration'])) $conf['page']['javascript']['administration'] = false;
	// chargement du script de gestion d'équipe
	if (empty($conf['page']['javascript']['gestionTeam'])) $conf['page']['javascript']['gestionTeam'] = false;
	// chargement du script de gestion jquery
	if (empty($conf['page']['javascript']['jquery'])) $conf['page']['javascript']['jquery'] = false;
	// chargement du script de gestion jquery-ui
	if (empty($conf['page']['javascript']['jquery-ui'])) $conf['page']['javascript']['jquery-ui'] = false;
	// chargement du script de gestion conG
	if (empty($conf['page']['javascript']['conG'])) $conf['page']['javascript']['conG'] = false;
	// chargement du script de gestion online
	if (empty($conf['page']['javascript']['online'])) $conf['page']['javascript']['online'] = false;
	// chargement du script de gestion index
	if (empty($conf['page']['javascript']['index'])) $conf['page']['javascript']['index'] = false;
	// Chargemenet du script pour la gestion des utilisateurs
	if (empty($conf['page']['javascript']['gestionUtilisateurs'])) $conf['page']['javascript']['gestionUtilisateurs'] = false;
	// chargement du script de gestion utilisateur
	if (empty($conf['page']['javascript']['utilisateur'])) $conf['page']['javascript']['utilisateur'] = false;
	// chargement du script de gestion tipoftheday
	if (empty($conf['page']['elements']['tipoftheday'])) $conf['page']['javascript']['tipoftheday'] = false;
	// Chargemenet du script pour les formulaires de choix d'intervalle de dates
	if (empty($conf['page']['elements']['intervalDate'])) $conf['page']['elements']['intervalDate'] = false;

	// Chargement de la feuille de style 'index'
	if (empty($conf['page']['stylesheet']['index'])) $conf['page']['stylesheet']['index'] = false;
	// Chargement de la feuille de style 'general'
	if (empty($conf['page']['stylesheet']['general'])) $conf['page']['stylesheet']['general'] = false;
	// Chargement de la feuille de style 'menu' (chargée par défaut si on utilise menuHorizontal)
	if ($conf['page']['elements']['menuHorizontal'] == true) $conf['page']['stylesheet']['menu'] = true;
	if (empty($conf['page']['stylesheet']['menu'])) $conf['page']['stylesheet']['menu'] = false;
	// Chargement de la feuille de style 'jquery-ui'
	if (empty($conf['page']['stylesheet']['jquery-ui'])) $conf['page']['stylesheet']['jquery-ui'] = false;
	// Chargement de la feuille de style 'grille'
	if (empty($conf['page']['stylesheet']['grille'])) $conf['page']['stylesheet']['grille'] = false;
	// Chargement de la feuille de style 'annuaire'
	if (empty($conf['page']['stylesheet']['annuaire'])) $conf['page']['stylesheet']['annuaire'] = false;
	// Chargement de la feuille de style 'grilleUnique'
	if (empty($conf['page']['stylesheet']['grilleUnique'])) $conf['page']['stylesheet']['grilleUnique'] = false;
	// Chargement de la feuille de style 'online'
	if (empty($conf['page']['stylesheet']['online'])) $conf['page']['stylesheet']['online'] = false;
	if (empty($conf['page']['elements']['tipoftheday'])) $conf['page']['stylesheet']['tipoftheday'] = false;
	// Chargement de la feuille de style 'utilisateur'
	if (empty($conf['page']['stylesheet']['utilisateur'])) $conf['page']['stylesheet']['utilisateur'] = false;
	
	// Gestion du compactage des scripts javascript et css
	if (empty($conf['page']['compact'])) $conf['page']['compact'] = false;

	/*
	 *  Gestion des dépendances
	 */
	if (true === $conf['page']['elements']['intervalDate']) {
		$conf['page']['javascript']['jquery'] = true;
		$conf['page']['javascript']['jquery-ui'] = true;
		$conf['page']['stylesheet']['jquery-ui'] = true;
	}
	if (true === $conf['page']['elements']['menuHorizontal']) {
		$conf['page']['include']['class_menu'] = 1; // La classe menu est nécessaire à ce script
	}
	// Gestion des dépendances javascript
	if (true === $conf['page']['javascript']['grille2'] || true === $conf['page']['javascript']['administration'] || true === $conf['page']['javascript']['gestionTeam']) {
		$conf['page']['javascript']['jquery'] = true;
		$conf['page']['javascript']['ajax'] = true;
	}
	if (true === $conf['page']['javascript']['conG']) {
		$conf['page']['javascript']['jquery'] = true;
		$conf['page']['javascript']['jquery-ui'] = true;
	}
	if (true === $conf['page']['javascript']['jquery-ui']) {
		$conf['page']['javascript']['jquery'] = true;
	}
	
// Liste des scripts javascript à charger dans l'ordre de chargement
	$javascript = array();
	if (true === $conf['page']['javascript']['jquery']) $javascript[] = 'jquery.js';
	if (true === $conf['page']['javascript']['jquery-ui']) {
		$javascript[] = 'jquery-ui.js';
		$javascript[] = 'jquery.ui.datepicker-fr.js';
	}
	if (true === $conf['page']['javascript']['ajax']) $javascript[] = 'ajax.js';
	if (true === $conf['page']['javascript']['grille2']) $javascript[] = 'grille2.js.php';
	if (true === $conf['page']['javascript']['administration']) $javascript[] = 'administration.js.php';
	if (true === $conf['page']['javascript']['gestionTeam']) $javascript[] = 'gestionTeam.js.php';
	if (true === $conf['page']['javascript']['gestionUtilisateurs']) $javascript[] = 'gestionUtilisateurs.js.php';
	if (true === $conf['page']['javascript']['conG']) $javascript[] = 'tableauxCong.js.php'; // Gestion des tableaux de congés
	if (true === $conf['page']['javascript']['online']) $javascript[] = 'online.js.php';
	if (true === $conf['page']['elements']['tipoftheday']) $javascript[] = 'tipoftheday.js';
	if (true === $conf['page']['elements']['intervalDate']) $javascript[] = 'intervalDate.js';
	if (true === $conf['page']['javascript']['index']) $javascript[] = 'index.js';
	if (true === $conf['page']['javascript']['utilisateur']) $javascript[] = 'utilisateur.js';

// Liste des feuilles de styles à charger dans l'ordre de chargement
	// Dépendances

	$stylesheet = array();
	$compteur = 0;
	if (true === $conf['page']['stylesheet']['general']) {
		$stylesheet[$compteur]['href'] = 'general.css';
		$stylesheet[$compteur]['media'] = 'screen';
		$compteur++;
		$stylesheet[$compteur]['href'] = 'generalP.css';
		$stylesheet[$compteur]['media'] = 'print';
		$compteur++;
	}
	if (true === $conf['page']['stylesheet']['index']) {
		$stylesheet[$compteur]['href'] = 'index.css';
		$stylesheet[$compteur]['media'] = 'screen';
		$compteur++;
	}
	if (true === $conf['page']['stylesheet']['menu']) {
		$stylesheet[$compteur]['href'] = 'menuHor.css';
		$stylesheet[$compteur]['media'] = 'screen';
		$compteur++;
		$stylesheet[$compteur]['href'] = 'menu.css';
		$stylesheet[$compteur]['media'] = 'screen';
		$compteur++;
		$stylesheet[$compteur]['href'] = 'menuP.css';
		$stylesheet[$compteur]['media'] = 'print';
		$compteur++;
		$stylesheet[$compteur]['href'] = 'menuHorP.css';
		$stylesheet[$compteur]['media'] = 'print';
		$compteur++;
	}
	if (true === $conf['page']['stylesheet']['grille']) {
		$stylesheet[$compteur]['href'] = 'grille.css';
		$stylesheet[$compteur]['media'] = 'screen';
		$compteur++;
		$stylesheet[$compteur]['href'] = 'grilleP.css';
		$stylesheet[$compteur]['media'] = 'print';
		$compteur++;
	}
	if (true === $conf['page']['stylesheet']['grilleUnique']) {
		$stylesheet[$compteur]['href'] = 'grilleUnique.css';
		$stylesheet[$compteur]['media'] = 'screen';
		$compteur++;
	}
	if (true === $conf['page']['stylesheet']['annuaire']) {
		$stylesheet[$compteur]['href'] = 'annuaire.css';
		$stylesheet[$compteur]['media'] = 'screen';
		$compteur++;
	//	$stylesheet[$compteur]['href'] = 'annuaireP.css';
	//	$stylesheet[$compteur]['media'] = 'print';
	//	$compteur++;
	}
	if (true === $conf['page']['stylesheet']['jquery-ui']) {
		$stylesheet[$compteur]['href'] = 'jquery-ui.css';
		$stylesheet[$compteur]['media'] = 'screen';
		$compteur++;
	}
	if (true === $conf['page']['stylesheet']['online']) {
		$stylesheet[$compteur]['href'] = 'online.css';
		$stylesheet[$compteur]['media'] = 'screen';
		$compteur++;
	}
	if (true === $conf['page']['elements']['tipoftheday']) {
		$stylesheet[$compteur]['href'] = 'tipoftheday.css';
		$stylesheet[$compteur]['media'] = 'screen';
		$compteur++;
		$stylesheet[$compteur]['href'] = 'tipofthedayP.css';
		$stylesheet[$compteur]['media'] = 'print';
		$compteur++;
	}
	if (true === $conf['page']['stylesheet']['utilisateur']) {
		$stylesheet[$compteur]['href'] = 'utilisateur.css';
		$stylesheet[$compteur]['media'] = 'screen';
		$compteur++;
		/* TODO 
		$stylesheet[$compteur]['href'] = 'utilisateurP.css';
		$stylesheet[$compteur]['media'] = 'print';
		$compteur++;
		 */
	}
	if (isset($DEBUG) && true === $DEBUG) {
		$stylesheet[$compteur]['href'] = 'debug.css';
		$stylesheet[$compteur]['media'] = 'screen';
		$compteur++;
		$stylesheet[$compteur]['href'] = 'debugP.css';
		$stylesheet[$compteur]['media'] = 'print';
		$compteur++;
	}


	// gestion du compactage des scripts et du css
	// Permet de rassembler tous les scripts javascript en un seul fichier
	// De même pour les fichiers css
	if (true === $conf['page']['compact']) {
		$url = sprintf("compact.php?type=js&amp;random=%s%s", rand(), rand());
		foreach ($javascript as $script) {
			$url .= "&amp;s[]=$script";
		}
		$javascript = array();
		$javascript[] = $url;
		/*
		 * compact.php ne fonctionne pas avec les css... :S
		 *
		$url = sprintf("compact.php?type=js&amp;random=%s%s", rand(), rand());
		foreach ($stylesheet as $css) {
			$url .= "&amp;s[]=$css";
		}
		$stylesheet = array();
		$stylesheet[] = $url;
		 */
	}

?>
