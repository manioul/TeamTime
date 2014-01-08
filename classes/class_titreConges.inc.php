<?php
// class_titreCong.inc.php
//
// Classe permettant d'éditer les titres de congés des agents
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

require_once ('tfpdf/tfpdf.php');
require_once ('class_jourTravail.inc.php');

class TitreConges extends tFPDF {
	// var $nom = iconv('UTF-8', 'ISO-8859-1', 'Forest Agnès'); // Si la police n'est pas utf8
	// $maxlength; Longueur maximale du motif de congé
	private $imageFond = array(
		1	=> 'tfpdf/titreConges.png' // V
		,2	=> 'tfpdf/titreConges.png' // F
		,3	=> 'tfpdf/titreConges.png' // W
		,8	=> 'tfpdf/titreConges.png' // Rcup
		,30	=> 'tfpdf/titreVro.png' // Vro
		,32	=> 'tfpdf/titreConges.png' // Vex
	);
	private $imageAnnulation = 'tfpdf/annulation.png';
	private $cteam = array(
		1	=> array( 1 => array(123, 60), 2 => array(245, 115))
		,2	=> array( 1 => array(123, 60), 2 => array(245, 115))
		,3	=> array( 1 => array(123, 60), 2 => array(245, 115))
		,8	=> array( 1 => array(123, 60), 2 => array(245, 115))
		,30	=> array( 1 => array(121, 96), 2 => array(262, 83))
		,32	=> array( 1 => array(123, 60), 2 => array(245, 115))
	);
	private $cnom = array(
		1	=> array( 1 => array(40, 60.5), 2 => array(174,115))
		,2	=> array( 1 => array(40.5, 60), 2 => array(174,115))
		,3	=> array( 1 => array(40, 60.5), 2 => array(174,115))
		,8	=> array( 1 => array(40, 60.5), 2 => array(174,115))
		,30	=> array( 1 => array(45, 96), 2 => array(188,83))
		,32	=> array( 1 => array(40, 60.5), 2 => array(174,115))
	);
	private $cnbConges = array(
		1	=> array( 1 => array(12, 80), 2 => array(148, 67))
		,2	=> array( 1 => array(13, 88), 2 => array(150, 75))
		,3	=> array( 1 => array(13, 96), 2 => array(150, 83))
		,8	=> array( 1 => array(13, 104), 2 => array(150, 90))
		//,30	=> array( 1 => array(17, 67), 2 => array(155, 58))
		,32	=> array( 1 => array(13, 112), 2 => array(150, 98))
	);
	private $cdateDebut = array(
		1	=> array( 1 => array(78, 79), 2 => array(220, 66))
		,2	=> array( 1 => array(88, 88), 2 => array(225, 74.5))
		,3	=> array( 1 => array(88, 96), 2 => array(225, 82))
		,8	=> array( 1 => array(88, 104), 2 => array(225, 90))
		,30	=> array( 1 => array(80, 110), 2 => array(202, 96.5))
		,32	=> array( 1 => array(88, 112), 2 => array(225, 98))
	);
	private $cdateFin = array(
		1	=> array( 1 => array(107, 79), 2 => array(244.5, 66))
		,2	=> array( 1 => array(107, 88), 2 => array(244, 74.5))
		,3	=> array( 1 => array(107, 96), 2 => array(244, 82))
		,8	=> array( 1 => array(107, 104), 2 => array(244, 90))
		,30	=> array( 1 => array(100, 110), 2 => array(223, 96.5))
		,32	=> array( 1 => array(107, 112), 2 => array(244, 98))
	);
	private $cdateReprise = array(
		1	=> array( 1 => array(180, 123))
		,2	=> array( 1 => array(180, 123))
		,3	=> array( 1 => array(180, 123))
		,8	=> array( 1 => array(180, 123))
		//,30	=> array( 1 => array(90, 127.5), 2 => array(192, 127.5))
		,32	=> array( 1 => array(180, 123))
	);
	private $cdateTitre = array(
		1	=> array( 1 => array(66, 129), 2 => array(225, 131))
		,2	=> array( 1 => array(66, 129), 2 => array(225, 131))
		,3	=> array( 1 => array(66, 129), 2 => array(225, 131))
		,8	=> array( 1 => array(66, 129), 2 => array(225, 131))
		,30	=> array( 1 => array(79, 142))
		,32	=> array( 1 => array(66, 129), 2 => array(225, 131))
	);
	private $cMotifVex = array(
		'naissance'	=> array( 1 => array(64, 146.5))
		,'Paternité'	=> array( 1 => array(64, 146.5))
		,'Décès'	=> array( 1 => array(64, 146.5))
		,'Mariage'	=> array( 1 => array(64, 146.5))
		,'DAS'		=> array( 1 => array(64, 146.5))
		,'Autres'	=> array( 1 => array(64, 146.5))
	);
	private $cCommentaire = array(
		1	=> array( 1 => array(64, 146.5))
		,2	=> array( 1 => array(64, 146.5))
		,3	=> array( 1 => array(64, 146.5))
		,8	=> array( 1 => array(64, 146.5))
		,30	=> array( 1 => array(64, 146.5))
		,32	=> array( 1 => array(64, 146.5))
	);
	private $annulation = false; // Positionné si il s'agit d'un titre d'annulation de congé

	// Permet de générer un titre d'annulation
	public function annulation() {
		$this->annulation = true;
	}

	public function __construct() {
		parent::__construct($orientation='l', $unit='mm', $size='A4');
		$this->SetMargins(0, 0);
		$this->SetAutoPageBreak(false, 0);
		$this->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
		$this->SetFont('DejaVu', '', 10);
	}

	public function editTitreConges($nom, $typeCong, $nbCong, $dateDebut, $dateFin, $dateReprise, $dateTitre, $team = '9E', $motifVex = NULL, $commentaire = NULL, $maxlength = 28) {
		$this->AddPage();

		if ($typeCong != 1) {
			if ($nbCong == 1) {
				unset($this->cdateFin[2]);
				unset($this->cdateFin[3]);
				unset($this->cdateFin[8]);
				unset($this->cdateFin[30]);
				unset($this->cdateFin[32]);
			} else {
				$this->cdateFin = array(
		1	=> array( 1 => array(107, 79), 2 => array(244.5, 66))
		,2	=> array( 1 => array(107, 88), 2 => array(244, 74.5))
		,3	=> array( 1 => array(107, 96), 2 => array(244, 82))
		,8	=> array( 1 => array(107, 104), 2 => array(244, 90))
		,30	=> array( 1 => array(100, 110), 2 => array(223, 96.5))
		,32	=> array( 1 => array(107, 112), 2 => array(244, 98))
	);
				$dateFin = "au " . $dateFin;
			}
		}
		// Image de fond
		if (isset($this->imageFond[$typeCong])) {
			$this->Image($this->imageFond[$typeCong]);
		}
		// N° équipe
		if (isset($this->cteam[$typeCong])) {
			foreach ($this->cteam[$typeCong] as $coord) {
				$this->SetXY($coord[0], $coord[1]);
				$this->Cell(0, 0, $team);
			}
		}
		// Nom
		if (isset($this->cnom[$typeCong])) {
			foreach ($this->cnom[$typeCong] as $coord) {
				$this->SetXY($coord[0], $coord[1]);
				$this->Cell(0, 0, $nom);
			}
		}
		// Nb de congés
		if (isset($this->cnbConges[$typeCong])) {
			foreach ($this->cnbConges[$typeCong] as $coord) {
				$this->SetXY($coord[0], $coord[1]);
				$this->Cell(0, 0, $nbCong);
			}
		}
		// Date de début du congé
		if (isset($this->cdateDebut[$typeCong])) {
			foreach ($this->cdateDebut[$typeCong] as $coord) {
				$this->SetXY($coord[0], $coord[1]);
				$this->Cell(0, 0, $dateDebut);
			}
		}
		// Date de fin du congé
		if (isset($this->cdateFin[$typeCong])) {
			foreach ($this->cdateFin[$typeCong] as $coord) {
				$this->SetXY($coord[0], $coord[1]);
				$this->Cell(0, 0, $dateFin);
			}
		}
		// Date de reprise du service
		if (isset($this->cdateReprise[$typeCong])) {
			foreach ($this->cdateReprise[$typeCong] as $coord) {
				$this->SetXY($coord[0], $coord[1]);
				$this->Cell(0, 0, $dateReprise);
			}
		}
		// Date du titre
		if (isset($this->cdateTitre[$typeCong])) {
			foreach ($this->cdateTitre[$typeCong] as $coord) {
				$this->SetXY($coord[0], $coord[1]);
				$this->Cell(0, 0, $dateTitre);
			}
		}
		// Motif du congé exceptionnel (case à cocher)
		if (isset($this->cMotifVex[$motifVex])) {
			foreach ($this->cMotifVex[$motifVex] as $coord) {
				$this->SetXY($coord[0], $coord[1]);
				$this->Cell(0, 0, 'X');
			}
		}
		// Commentaires congés exceptionnel
		if (isset($this->cCommentaire[$typeCong])) {
			foreach ($this->cCommentaire[$typeCong] as $coord) {
				$this->SetXY($coord[0], $coord[1]);
				$this->Cell(0, 0, substr($commentaire, 0, $maxlength));
				// $this->Cell(0, 0, substr(iconv('UTF-8', 'ISO-8859-1', $commentaire), 0, $maxlength)); // Si la police n'est pas utf8
			}
		}
		if ($this->annulation) {
			$this->Image($this->imageAnnulation);
		}
	}

	public function editTitres() {
		$titre = $_SERVER['DOCUMENT_ROOT'] . "/titresConges/" . date('YmdHis') . '.pdf';
		$this->Output($titre, 'F');
		$this->Output('titres.pdf', 'D');
	}
}

?>
