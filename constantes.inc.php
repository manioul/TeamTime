<?php
// constantes.inc.php

// Définition des constantes.
//

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

// Répertoire d'installation
define('INSTALL_DIR', "/var/www/TeamTime");

// Numéro de version de l'application
define('VERSION', "1.3c");

// Les constantes relatives à des erreurs critiques sont préfixées par ERR_
// Les constantes relatives à des erreurs fatales sont préfixées par FAT_
// Les constantes relatives aux bdd sont constituées d'un éventuel préfixe d'erreur suivi de DB_

// Valeurs de retour
	define('SUCCESS', 1);

// Erreurs de fonctions internes (-1 à -99)
	define('ERR_BAD_PARAM', -1); // Le paramètre passé n'est pas correct
	define('ERR_NONEXISTENT', -2); // La valeur n'existe pas
	define('ERR_MISSING_PARAM', -3); // Aucun paramètre
	define('ERR_TYPE_MISMATCH', -4); // La valeur n'est pas du type attendu
// Erreurs sur un objet (-100 à -199)
	define('ERR_OBJ_DOESNT_EXIST', -102);
	define('ERR_RSRC_EXPECTED', -120);
// Erreurs de Base de données (-200 à -299)
	define('ERR_DB_CONN', -200);
	define('ERR_DB_SEL', -201);
	define('ERR_DB_CLOSE', -202);
	define('ERR_DB_NORESULT', -203);
	define('ERR_DB_SQL', -250);
	define('WARN_DB_EMPTY_RESULT', -251);
// Erreurs fatales (-300 à )
// Erreurs relatives à la création de la grille (-400 à -499)
	define('FAT_END_OF_CYCLE_REACHED', -400);


// Warnings
	define('WARN_UNSUITABLE_PARAM', -500); // paramètre temporairement incorrect pour une fonction 
	define('WARN_NO_ERR_MSG', -501); // Pas de message d'erreur connu pour l'erreur rencontrée


// Constantes internes au fonctionnement du programme
	define('REPOS', 'Repos'); // Dénommination des jours de repos du cycle dans la bdd (TBL_CYCLE)

?>
