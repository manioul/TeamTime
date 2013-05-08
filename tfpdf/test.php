<?php
require ('tfpdf.php');

$fonte = 'Times'; // Police pour remplir le titre
$team = '9E'; // Numéro de l'équipe
$nom = 'Forest Agnès'; // Nom de la personne
// $nom = iconv('UTF-8', 'ISO-8859-1', 'Forest Agnès'); // Si la police n'est pas utf8
$typeCong = 'Vacances à ski et plein de choses à raconter mais pas forcément la place suffisante pour...'; // Type de congé : 'cycle', 'F', 'W', 'Récup', 'Naissance', 'Paternité', 'Décès', 'Mariage', 'Motif détaillé'
$nbCong = 1; // Quantité de congé
$dateDebut = '05/07/2012'; // date de début de congé
$dateFin = '06/07/2012'; // Date de fin de congé
$dateDepart = '02/07/2012'; // Date de départ du service de l'agent
$dateRetour = '07/07/2012'; // Date de reprise de service de l'agent
$dateAvis = '28/06/2012'; // Date de l'avis
$maxlength = 28; // Longueur maximale du motif de congé

$pdf = new tFPDF('l');

$pdf->SetMargins(0, 0);

$pdf->SetAutoPageBreak(false, 0);

$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
$pdf->SetFont('DejaVu', '', 12);


for ($i=0; $i<3; $i++) {
	$pdf->AddPage();

	$pdf->Image('titre.png');

	// N° équipe (1)
	$pdf->SetXY(125, 22);
	$pdf->Cell(0, 0, $team);

	// N° équipe (2)
	$pdf->SetXY(265, 22);
	$pdf->Cell(0, 0, $team);

	// Nom (1)
	$pdf->SetXY(65, 49);
	$pdf->Cell(0, 0, $nom);

	// Nom (2)
	$pdf->SetXY(192, 108);
	$pdf->Cell(0, 0, $nom);

	switch ($typeCong) {
	case 'cycle':
		// Nb Cycle (1)
		$pdf->SetXY(17, 67);
		$pdf->Cell(0, 0, $nbCong);

		// date début cycle (1)
		$pdf->SetXY(87, 67);
		$pdf->Cell(0, 0, $dateDebut);

		// date fin cycle (1)
		$pdf->SetXY(115, 67);
		$pdf->Cell(0, 0, $dateFin);

		// Nb Cycle (2)
		$pdf->SetXY(155, 58);
		$pdf->Cell(0, 0, $nbCong);

		// date début cycle (2)
		$pdf->SetXY(222, 58);
		$pdf->Cell(0, 0, $dateDebut);

		// date fin cycle (2)
		$pdf->SetXY(250, 58);
		$pdf->Cell(0, 0, $dateFin);
		break;
	case 'F':
		// Nb Fractionnés (1)
		$pdf->SetXY(17, 77);
		$pdf->Cell(0, 0, $nbCong);

		// date début Fractionnés (1)
		$pdf->SetXY(87, 77);
		$pdf->Cell(0, 0, $dateDebut);

		// date fin Fractionnés (1)
		$pdf->SetXY(115, 77);
		$pdf->Cell(0, 0, $dateFin);

		// Nb Fractionnés (2)
		$pdf->SetXY(155, 67);
		$pdf->Cell(0, 0, $nbCong);

		// date début Fractionnés (2)
		$pdf->SetXY(222, 67);
		$pdf->Cell(0, 0, $dateDebut);

		// date fin Fractionnés (2)
		$pdf->SetXY(250, 67);
		$pdf->Cell(0, 0, $dateFin);
		break;
	case 'W':
		// Nb W (1)
		$pdf->SetXY(17, 87);
		$pdf->Cell(0, 0, $nbCong);

		// date début W (1)
		$pdf->SetXY(87, 87);
		$pdf->Cell(0, 0, $dateDebut);

		// date fin W (1)
		$pdf->SetXY(115, 87);
		$pdf->Cell(0, 0, $dateFin);

		// Nb W (2)
		$pdf->SetXY(155, 77);
		$pdf->Cell(0, 0, $nbCong);

		// date début W (2)
		$pdf->SetXY(222, 77);
		$pdf->Cell(0, 0, $dateDebut);

		// date fin W (2)
		$pdf->SetXY(250, 77);
		$pdf->Cell(0, 0, $dateFin);
		break;
	default:
		// Nb Vex (1)
		$pdf->SetXY(17, 97);
		$pdf->Cell(0, 0, $nbCong);

		// date début Vex (1)
		$pdf->SetXY(87, 97);
		$pdf->Cell(0, 0, $dateDebut);

		// date fin Vex (1)
		$pdf->SetXY(115, 97);
		$pdf->Cell(0, 0, $dateFin);

		// Nb Vex (2)
		$pdf->SetXY(155, 87);
		$pdf->Cell(0, 0, $nbCong);

		// date début Vex (2)
		$pdf->SetXY(222, 87);
		$pdf->Cell(0, 0, $dateDebut);

		// date fin Vex (2)
		$pdf->SetXY(250, 87);
		$pdf->Cell(0, 0, $dateFin);

		switch ($typeCong) {
		case 'Récup':
			// Récup
			$pdf->setXY(26.5, 168.5);
			$pdf->Cell(0, 0, 'X');
			break;
		case 'Naissance':
			// Naissance
			$pdf->setXY(26.5, 174.5);
			$pdf->Cell(0, 0, 'X');
			break;
		case 'Paternité':
			// Paternité
			$pdf->setXY(26.5, 180.5);
			$pdf->Cell(0, 0, 'X');
			break;
		case 'Décès':
			// Décès
			$pdf->setXY(26.5, 186);
			$pdf->Cell(0, 0, 'X');
			break;
		case 'Mariage':
			// Mariage
			$pdf->setXY(26.5, 192);
			$pdf->Cell(0, 0, 'X');
			break;
		default:
			// Autres
			$pdf->setXY(26.5, 200.5);
			$pdf->Cell(0, 0, 'X');

			// Motif
			$pdf->setXY(87, 186);
			$pdf->Cell(40, 0, substr($typeCong, 0, $maxlength));
			// $pdf->Cell(0, 0, substr(iconv('UTF-8', 'ISO-8859-1', $typeCong), 0, $maxlength)); // Si la police n'est pas utf8
		}
		break;
	}
	// du (1)
	$pdf->SetXY(25, 127.5);
	$pdf->Cell(0, 0, $dateDepart);

	// du (2)
	$pdf->SetXY(192, 118);
	$pdf->Cell(0, 0, $dateDepart);

	// au (1)
	$pdf->SetXY(90, 127.5);
	$pdf->Cell(0, 0, $dateRetour);

	// au (2)
	$pdf->SetXY(192, 127.5);
	$pdf->Cell(0, 0, $dateRetour);

	// Date avis (1)
	$pdf->SetXY(64, 146.5);
	$pdf->Cell(0, 0, $dateAvis);

	// Date avis (2)
	$pdf->SetXY(240, 136.5);
	$pdf->Cell(0, 0, $dateAvis);
}

$pdf->Output('titre.pdf', 'D');

?>
