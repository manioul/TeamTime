<?php
// errors.inc.php
//
// Liste des messages d'erreurs
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

$GLOBALS['ERREURS'] = array(
	'fr'	=> array(
		SUCCESS			=> "Succès !"
		,ERR_BAD_PARAM		=> "Le paraamètre passé n'est pas correct."
		,ERR_NONEXISTENT	=> "La valeur n'existe pas."
		,ERR_MISSING_PARAM	=> "Paramètre manquant : la fonction attend un paramètre, mais il est manquant."
		,ERR_TYPE_MISMATCH	=> "La valeur n'est pas du type attendu."
		,WARN_DB_EMPTY_RESULT	=> "La requête n'a pas renvoyé de résultat."
		,WARN_NO_ERR_MSG	=> "Pas de message connu pour l'erreur rencontrée : "
	)
	,'en'	=> array(
		SUCCESS			=> "Success!"
		,ERR_BAD_PARAM		=> "Wrong parameter given."
		,ERR_NONEXISTENT	=> "Value doesn't exist."
		,ERR_MISSING_PARAM	=> "Missing parameter: function is expecting a parameter but there is none."
		,ERR_TYPE_MISMATCH	=> "Type mismatch."
		,WARN_DB_EMPTY_RESULT	=> "Last query returned no result."
		,WARN_NO_ERR_MSG	=> "No specific message for this error: "
	)
);
