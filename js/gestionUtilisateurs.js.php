<?php
/*
 * gestionUtilisateurs.js.php
 */

// Require authenticated user
// L'utilisateur doit être logué/admin pour accéder à cette page
// $requireAuthenticatedUser = true;
$requireEditeur = true;

header('Content-Type: application/javascript');
//ob_start();
$conf['page']['elements']['firePHP'] = true;

require_once('../firePHP.inc.php');
require_once('../classes/class_utilisateur.inc.php');
require_once('../classes/class_utilisateurGrille.inc.php');
require_once('../classes/class_debug.inc.php');
require_once('../config.inc.php');
require_once('../init.inc.php');
require_once('../constantes.inc.php');
require_once('../globals_db.inc.php');
require_once('../classes/class_db.inc.php');
require_once('../session.inc.php');

if ($_SESSION['ADMIN']) {
	$affectations = array();
?>
affectations = new Array();
<?php
	$result = $_SESSION['db']->db_interroge("
		SELECT `centre`,
		`team`
		FROM `TBL_AFFECTATION`
		GROUP BY `centre`, `team`");
	while ($row = $_SESSION['db']->db_fetch_row($result)) {
		$affectations[$row[0]][] = $row[1];
	}
	mysqli_free_result($result);
	foreach ($affectations as $centre => $teams) {
		$index = 0;
?>
affectations["<?=$centre?>"] = new Array();
<?php
		foreach ($teams as $team) {
?>
affectations["<?=$centre?>"][<?=$index++?>] = "<?=$team?>";
<?php
		}
	}
} else {
}
