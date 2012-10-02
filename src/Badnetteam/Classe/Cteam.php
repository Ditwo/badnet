<?php
require_once 'Object/Omember.inc';

/**
 * Classe team
 */
class Cteam
{
	/**
	 * Pdf de la feuille de declaration de la composition
	 *
	 */
	public function pdfTeam($aPdf)
	{
		$locale = Bn::GetLocale();
		require_once 'Badnetteam/Locale/' .$locale . '/Tevent.inc';

		// Donnees
		$cEvent = new Cevent();
		$tieIds = Ctie::getTieIds();
		$cTie   = new Ctie(reset($tieIds));

		$pdf=$aPdf();
		$pdf->AddPage('P');

		$pdf->SetFont('helvetica', 'B', 16);
		$pdf->SetFillColor(255);
		$pdf->SetTextColor(0);
		$str = $cEvent->getVal('name');
		$pdf->Cell(0, 0, $str, '', 1, 'L');
		$pdf->SetFont('helvetica', 'B', 14);
		$str = LOC_PDF_TEAM_FORM;
		$pdf->Cell(0, 0, $str, '', 1, 'L');
		$pdf->Ln(10);

		$pdf->setY(25);
		$pdf->SetFont('helvetica','',8);
		$pdf->MultiCell(0, 0, LOC_PDF_TEAM_INFO, 0, 'L');
		$pdf->ln(8);

		// Informations de la rencontre
		$pdf->SetFont('helvetica','B', 9);
		$pdf->Cell(20, 20, LOC_PDF_TIE, 1, 0, 'L', 0);
		$pdf->SetFont('helvetica','', 9);
		$pdf->Cell(40, 20, '', 1, 1, 'L', 0);

		// Date et equipe
		$pdf->SetFont('helvetica','B', 9);
		$pdf->Cell(50, 20, LOC_PDF_DATE, 'LTB', 0, 'L', 0);
		$pdf->SetFont('helvetica','', 9);
		$pdf->Cell(140, 20, $cTie->getVal('date'), 'TB', 0, 'L', 0);
		$pdf->SetFont('helvetica','B', 9);
		$pdf->Cell(20, 20, LOC_PDF_TEAM, 'LB', 0, 'L', 0);
		$pdf->SetFont('helvetica','', 9);
		$pdf->Cell(40, 20, $this->getVal('name'), 'TBR', 1, 'L', 0);

		/*
		 $pdf->SetFont('helvetica','B',9);
		 $pdf->Cell(30, 0, LOC_PDF_DIVISION, 0, 0, 'R', 0);
		 $pdf->SetFont('helvetica','', 9);
		 $pdf->Cell(30, 0, $cTie->getVal('division'), 0, 0, 'L', 0);

		 $pdf->SetFont('helvetica','B',9);
		 $pdf->Cell(30, 0, LOC_PDF_GROUP, 0, 0, 'R', 0);
		 $pdf->SetFont('helvetica','', 9);
		 $pdf->Cell(30, 0, $cTie->getVal('groupe'), 0, 1, 'L', 0);

		 // Informations du tournoi : Date lieu
		 $pdf->SetFont('helvetica','B',9);
		 $pdf->Cell(20, 0, LOC_PDF_PLACE, 0, 0, 'R', 0);
		 $pdf->SetFont('helvetica','', 9);
		 $pdf->Cell(40, 0, $cEvent->getVal('place'), 0, 0, 'L', 0);

		 $pdf->SetFont('helvetica','B',9);
		 $pdf->Cell(30, 0, LOC_PDF_DATE, 0, 0, 'R', 0);
		 $pdf->SetFont('helvetica','', 9);
		 $pdf->Cell(30, 0, Bn::date($cEvent->getVal('date'), 'd-m-Y'), 0, 1, 'L', 0);

		 // Joueurs
		 $pdf->SetFont('helvetica','B',10);
		 $pdf->Cell(0, 10, LOC_PDF_MENS, 0, 1, 'L', 0);
		 $pdf->SetFont('helvetica','B',8);
		 $pdf->Cell(60, 10, LOC_PDF_NAME, 1, 0, 'C');
		 $pdf->Cell(20, 10, LOC_PDF_LICENSE, 1, 0, 'C');
		 $pdf->multiCell(15, 10, LOC_PDF_COUNTRY, 1, 'C', 0, 0);
		 $pdf->multiCell(10, 10, LOC_PDF_MUTE, 1, 'C', 0, 0);
		 $pdf->Cell(40, 10, LOC_PDF_PARAPH, 1, 0, 'C');
		 $pdf->Cell(45, 10, LOC_PDF_REF_OBS, 1, 1, 'C');
		 $height = 8;
		 for($i=0; $i<8;$i++)
		 {
			$pdf->Cell(60, $height, '', 1, 0);
			$pdf->Cell(20, $height, '', 1, 0);
			$pdf->Cell(15, $height, '', 1, 0);
			$pdf->Cell(10, $height, '', 1, 0);
			$pdf->Cell(40, $height, '', 1, 0);
			$pdf->Cell(45, $height, '', 1, 1);
			}

			// Joueuses
			$pdf->SetFont('helvetica','B',10);
			$pdf->Cell(0, 10, LOC_PDF_WOMENS, 0, 1, 'L', 0);
			$pdf->SetFont('helvetica','B',8);
			$pdf->Cell(60, 10, LOC_PDF_NAME, 1, 0, 'C');
			$pdf->Cell(20, 10, LOC_PDF_LICENSE, 1, 0, 'C');
			$pdf->multiCell(15, 10, LOC_PDF_COUNTRY, 1, 'C', 0, 0);
			$pdf->multiCell(10, 10, LOC_PDF_MUTE, 1, 'C', 0, 0);
			$pdf->Cell(40, 10, LOC_PDF_PARAPH, 1, 0, 'C');
			$pdf->Cell(45, 10, LOC_PDF_REF_OBS, 1, 1, 'C');
			for($i=0; $i<7;$i++)
			{
			$pdf->Cell(60, $height, '', 1, 0);
			$pdf->Cell(20, $height, '', 1, 0);
			$pdf->Cell(15, $height, '', 1, 0);
			$pdf->Cell(10, $height, '', 1, 0);
			$pdf->Cell(40, $height, '', 1, 0);
			$pdf->Cell(45, $height, '', 1, 1);
			}

			$pdf->Cell(0, 10, LOC_PDF_REPORT, 0, 1, 'L');

			// Arbitres
			$pdf->SetFont('helvetica','B',10);
			$pdf->Cell(0, 10, LOC_PDF_UMPIRE, 0, 1, 'L', 0);

			$pdf->SetFont('helvetica','B',8);
			$pdf->Cell(60, $height, LOC_PDF_NAME, 1, 0, 'C');
			$pdf->Cell(20, $height, LOC_PDF_LICENSE, 1, 0, 'C');
			$pdf->Cell(25, $height, LOC_PDF_GRADE, 1, 0, 'C');
			$pdf->Cell(40, $height, LOC_PDF_PARAPH, 1, 0, 'C');
			$pdf->Cell(45, $height, LOC_PDF_REF_OBS, 1, 1, 'C');

			$pdf->Cell(60, $height, '', 1, 0);
			$pdf->Cell(20, $height, '', 1, 0);
			$pdf->Cell(25, $height, '', 1, 0);
			$pdf->Cell(40, $height, '', 1, 0);
			$pdf->Cell(45, $height, '', 1, 1);

			$pdf->Cell(110, 10, LOC_PDF_VISA_TEAM, 0, 0, 'L', 0);
			$pdf->Cell(50, 10, LOC_PDF_VISA_REFEREE, 0, 1, 'L', 0);
			*/
		return false;
	}

	/**
	 * Liste des equipes
	 *
	 * @return unknown
	 */
	public static function getLov()
	{
		$q = new Bn_query('team', '_team');
		$q->setFields('team_id, team_name');
		$q->setOrder('team_name');
		$teams = $q->getLov();
		unset($q);
		return $teams;
	}


	/**
	 * Liste des equipes
	 *
	 * @return unknown
	 */
	public static function getTeams()
	{
		$q = new Bn_query('team', '_team');
		$q->setFields('team_id');
		$q->setOrder('team_name');
		$teamIds = $q->getCol();
		unset($q);
		return $teamIds;
	}

	/**
	 * Pdf de la feuille de presence
	 *
	 */
	public function pdfCheck($aPdf)
	{
		$locale = Bn::GetLocale();
		require_once 'Badnetteam/Locale/' .$locale . '/Tevent.inc';

		// Donnees
		$cEvent = new Cevent();
		$tieIds = Ctie::getTieIds();
		$cTie   = new Ctie(reset($tieIds));

		$pdf = $aPdf;
		$pdf->AddPage('P');

		$pdf->SetFont('helvetica', 'B', 16);
		$pdf->SetFillColor(255);
		$pdf->SetTextColor(0);
		$str = $cEvent->getVal('name');
		$pdf->Cell(0, 0, $str, '', 1, 'L');
		$pdf->SetFont('helvetica', 'B', 14);
		$str = LOC_PDF_CHECK_FORM;
		$pdf->Cell(0, 0, $str, '', 1, 'L');
		$pdf->Ln(10);

		$pdf->setY(25);
		$pdf->SetFont('helvetica','',8);
		$pdf->MultiCell(0, 0, LOC_PDF_CHECK_INFO, 0, 'L');
		$pdf->ln(8);

		// Informations du tournoi : nom, division, groupes
		$pdf->SetFont('helvetica','B', 9);
		$pdf->Cell(20, 0, LOC_PDF_CLUB, 0, 0, 'R', 0);
		$pdf->SetFont('helvetica','', 9);
		$pdf->Cell(40, 0, $this->getVal('name'), 0, 0, 'L', 0);

		$pdf->SetFont('helvetica','B',9);
		$pdf->Cell(30, 0, LOC_PDF_DIVISION, 0, 0, 'R', 0);
		$pdf->SetFont('helvetica','', 9);
		$pdf->Cell(30, 0, $cTie->getVal('division'), 0, 0, 'L', 0);

		$pdf->SetFont('helvetica','B',9);
		$pdf->Cell(30, 0, LOC_PDF_GROUP, 0, 0, 'R', 0);
		$pdf->SetFont('helvetica','', 9);
		$pdf->Cell(30, 0, $cTie->getVal('groupe'), 0, 1, 'L', 0);

		// Informations du tournoi : Date lieu
		$pdf->SetFont('helvetica','B',9);
		$pdf->Cell(20, 0, LOC_PDF_PLACE, 0, 0, 'R', 0);
		$pdf->SetFont('helvetica','', 9);
		$pdf->Cell(40, 0, $cEvent->getVal('place'), 0, 0, 'L', 0);

		$pdf->SetFont('helvetica','B',9);
		$pdf->Cell(30, 0, LOC_PDF_DATE, 0, 0, 'R', 0);
		$pdf->SetFont('helvetica','', 9);
		$pdf->Cell(30, 0, Bn::date($cEvent->getVal('date'), 'd-m-Y'), 0, 1, 'L', 0);

		// Joueurs
		$ids = $this->getplayers(true, OMEMBER_GENDER_MALE, false);
		$pdf->SetFont('helvetica','B',10);
		$pdf->Cell(0, 10, LOC_PDF_MENS, 0, 1, 'L', 0);
		$pdf->SetFont('helvetica','B',8);
		$pdf->Cell(52, 10, LOC_PDF_NAME, 1, 0, 'C');
		$pdf->Cell(18, 10, LOC_PDF_LEVEL, 1, 0, 'C');
		$pdf->Cell(20, 10, LOC_PDF_LICENSE, 1, 0, 'C');
		$pdf->multiCell(15, 10, LOC_PDF_COUNTRY, 1, 'C', 0, 0);
		$pdf->multiCell(10, 10, LOC_PDF_MUTE, 1, 'C', 0, 0);
		$pdf->Cell(35, 10, LOC_PDF_PARAPH, 1, 0, 'C');
		$pdf->Cell(40, 10, LOC_PDF_REF_OBS, 1, 1, 'C');
		$height = 8;
		$pdf->SetFont('helvetica','',8);
		for($i=0; $i<8;$i++)
		{
			if( !empty($ids[$i]))
			{
				$cPlayer = new Cplayer($ids[$i]);
				$pdf->Cell(52, $height, $cPlayer->getVal('famname') . ' ' . $cPlayer->getVal('firstname'), 1, 0);
				$pdf->Cell(18, $height, $cPlayer->getVal('levels').','.$cPlayer->getVal('leveld').','.$cPlayer->getVal('levelm'), 1, 0);
				$pdf->Cell(20, $height, $cPlayer->getVal('license'), 1, 0);
				$pdf->Cell(15, $height, '', 1, 0);
				$pdf->Cell(10, $height, '', 1, 0);
				$pdf->Cell(35, $height, '', 1, 0);
				$pdf->Cell(40, $height, '', 1, 1);
				unset ($cPlayer);
			}
			else
			{
				$pdf->Cell(52, $height, '', 1, 0);
				$pdf->Cell(18, $height, '', 1, 0);
				$pdf->Cell(20, $height, '', 1, 0);
				$pdf->Cell(15, $height, '', 1, 0);
				$pdf->Cell(10, $height, '', 1, 0);
				$pdf->Cell(35, $height, '', 1, 0);
				$pdf->Cell(40, $height, '', 1, 1);
			}
		}

		// Joueuses
		$ids = $this->getplayers(true, OMEMBER_GENDER_FEMALE, false);
		$pdf->SetFont('helvetica','B',10);
		$pdf->Cell(0, 10, LOC_PDF_WOMENS, 0, 1, 'L', 0);
		$pdf->SetFont('helvetica','B',8);
		$pdf->Cell(52, 10, LOC_PDF_NAME, 1, 0, 'C');
		$pdf->Cell(18, 10, LOC_PDF_LEVEL, 1, 0, 'C');
		$pdf->Cell(20, 10, LOC_PDF_LICENSE, 1, 0, 'C');
		$pdf->multiCell(15, 10, LOC_PDF_COUNTRY, 1, 'C', 0, 0);
		$pdf->multiCell(10, 10, LOC_PDF_MUTE, 1, 'C', 0, 0);
		$pdf->Cell(35, 10, LOC_PDF_PARAPH, 1, 0, 'C');
		$pdf->Cell(40, 10, LOC_PDF_REF_OBS, 1, 1, 'C');
		$pdf->SetFont('helvetica','',8);
		for($i=0; $i<7;$i++)
		{
			if( !empty($ids[$i]))
			{
				$cPlayer = new Cplayer($ids[$i]);
				$pdf->Cell(52, $height, $cPlayer->getVal('famname') . ' ' . $cPlayer->getVal('firstname'), 1, 0);
				$pdf->Cell(18, $height, $cPlayer->getVal('levels').','.$cPlayer->getVal('leveld').','.$cPlayer->getVal('levelm'), 1, 0);
				$pdf->Cell(20, $height, $cPlayer->getVal('license'), 1, 0);
				$pdf->Cell(15, $height, '', 1, 0);
				$pdf->Cell(10, $height, '', 1, 0);
				$pdf->Cell(35, $height, '', 1, 0);
				$pdf->Cell(40, $height, '', 1, 1);
				unset ($cPlayer);
			}
			else
			{
				$pdf->Cell(52, $height, '', 1, 0);
				$pdf->Cell(18, $height, '', 1, 0);
				$pdf->Cell(20, $height, '', 1, 0);
				$pdf->Cell(15, $height, '', 1, 0);
				$pdf->Cell(10, $height, '', 1, 0);
				$pdf->Cell(35, $height, '', 1, 0);
				$pdf->Cell(40, $height, '', 1, 1);
			}
		}

		$pdf->Cell(0, 10, LOC_PDF_REPORT, 0, 1, 'L');

		// Arbitres
		$pdf->SetFont('helvetica','B',10);
		$pdf->Cell(0, 10, LOC_PDF_UMPIRE, 0, 1, 'L', 0);

		$pdf->SetFont('helvetica','B',8);
		$pdf->Cell(60, $height, LOC_PDF_NAME, 1, 0, 'C');
		$pdf->Cell(20, $height, LOC_PDF_LICENSE, 1, 0, 'C');
		$pdf->Cell(25, $height, LOC_PDF_GRADE, 1, 0, 'C');
		$pdf->Cell(40, $height, LOC_PDF_PARAPH, 1, 0, 'C');
		$pdf->Cell(45, $height, LOC_PDF_REF_OBS, 1, 1, 'C');

		$pdf->Cell(60, $height, '', 1, 0);
		$pdf->Cell(20, $height, '', 1, 0);
		$pdf->Cell(25, $height, '', 1, 0);
		$pdf->Cell(40, $height, '', 1, 0);
		$pdf->Cell(45, $height, '', 1, 1);

		$pdf->Cell(110, 10, LOC_PDF_VISA_TEAM, 0, 0, 'L', 0);
		$pdf->Cell(50, 10, LOC_PDF_VISA_REFEREE, 0, 1, 'L', 0);

		return false;
	}

	public function getPlayers($aIsPresent = null, $aGender = null, $aWo=true)
	{
		$q = new Bn_query('player', '_team');
		$q->setFields('play_id');
		$q->addWhere('play_teamid=' . $this->getVal('id'));
		if ( !is_null($aIsPresent) ) $q->addWhere('play_ispresent=' . YES);
		if ( !is_null($aGender) ) $q->addWhere('play_gender=' . $aGender);
		if ( !$aWo) $q->addWhere("play_famname != 'WO'");
		$q->setOrder('play_famname, play_firstname');
		$ids = $q->getCol();
		return $ids;
	}

	public function __construct($aTeamId)
	{
		$q = new Bn_query('team', '_team');
		$q->addField('team_id',    'id');
		$q->addField('team_name',  'name');
		$q->addField('team_stamp', 'stamp');
		$q->addField('team_pos',   'pos');
		$q->addField('team_numid', 'numid');
		$q->setWhere('team_id=' . $aTeamId);
		$this->_fields = $q->getRow();
	}

	public function setVal($aName, $aValue)
	{
		$this->_fields[$aName] = $aValue;
		return $aValue;
	}

	public function getVal($aName, $aDefault = null)
	{
		if ( isset($this->_fields[$aName]) ) $val = $this->_fields[$aName];
		else $val = $aDefault;
		return $val;
	}
}
?>
