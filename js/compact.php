<?php
// compact.php
// Rassemble des fichiers en un unique fichier
// utile pour n'avoir qu'un seul fichier javascript ou une seule feuille de style à partir de plusieurs
//
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// Semble ne pas vouloir suppoter les css
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

switch ($_GET['type']) {
case 'js':
	header('Content-Type: application/javascript');
	break;
case 'css':
	header('Content-Type: text/css');
	break;
}

foreach ($_GET['s'] as $s) {
	if (strpos($s, ".js") - strlen($s) == -3 || strpos($s, ".css") - strlen($s) == -4)
	{
		echo file_get_contents($s);
	} else {
	}
}
?>
