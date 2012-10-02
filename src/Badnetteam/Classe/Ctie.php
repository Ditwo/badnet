<?php
require_once 'Object/Omatch.inc';
require_once 'Object/Opair.inc';

/**
 * Classe tie
 */
class Ctie
{
	/**
	 * Met a jour le score de la rencontre en fonction des resulats des matchs
	 *
	 */
	public function updateScore()
	{
		$matchIds = $this->getMatchIds();
		$scoreh = 0;
		$scorev = 0;
		foreach($matchIds as $matchId)
		{
			$cMatch = new Cmatch($matchId);
			$resulth = $cMatch->getVal('resulth');
			$resultv = $cMatch->getVal('resultv');
			if ($cMatch->getVal('status') <= OMATCH_STATUS_CLOSED)
			{
				if ($resulth == OPAIR_RES_WIN) $scoreh++;
				else if ($resulth == OPAIR_RES_WINWO) $scoreh++;
				else if ($resulth == OPAIR_RES_WINAB) $scoreh++;
				else if ($resulth == OPAIR_RES_LOOSEWO) $scoreh--;
				if ($resultv == OPAIR_RES_WIN) $scorev++;
				else if ($resultv == OPAIR_RES_WINWO) $scorev++;
				else if ($resultv == OPAIR_RES_WINAB) $scorev++;
				else if ($resultv == OPAIR_RES_LOOSEWO) $scorev--;
			}
			unset($cMatch);
		}
		$this->setVal('pointh', $scoreh);
		$this->setVal('pointv', $scorev);
		$this->unSetVal('division');
		$this->unSetVal('group');
		$this->unSetVal('sporthall');
		$this->unSetVal('step');
		$this->save();
	}

	/**
	 * Pdf de la feuille de declaration de la composition
	 *
	 */
	public function pdfTeam()
	{
		$locale = Bn::GetLocale();
		require_once 'Badnetteam/Locale/' .$locale . '/Tevent.inc';

		// Creation du document
		include_once 'Bn/Bnpdf.php';
		$pdf = new Bnpdf();
		$pdf->setAutoPageBreak(false);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);
		$pdf->start();
		$cTeamh  = new Cteam($this->getVal('teamhid'));
		$cTeamv  = new Cteam($this->getVal('teamvid'));
		$this->_pdfTeam($pdf, $cTeamh);
		$this->_pdfTeam($pdf, $cTeamv);
		$filename = '../Temp/Pdf/check_' . $this->getVal('teamhid') . '-' . $this->getVal('teamvid') . '.pdf';
		$pdf->end($filename);
		return false;
	}

	public function _pdfTeam($aPdf, $aTeam)
	{
		// Donnees
		$cTeamh  = new Cteam($this->getVal('teamhid'));
		$cTeamv  = new Cteam($this->getVal('teamvid'));
		$cEvent = new Cevent();
		$pdf = $aPdf;

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
		$height = 15;
		$pdf->SetFont('helvetica','', 9);
		$pdf->Cell(30, $height, LOC_PDF_TIE, 'TLB', 0, 'L', 0);
		$pdf->SetFont('helvetica','', 14);
		$str = $cTeamh->getVal('name') . ' - ' . $cTeamv->getVal('name');
		$pdf->Cell(0, $height, $str, 'TRB', 1, 'C', 0);

		// Date et equipe
		$height = 11;
		$pdf->SetFont('helvetica','', 9);
		$pdf->Cell(33, $height, LOC_PDF_DATE, 'LTB', 0, 'L', 0);
		$pdf->SetFont('helvetica','B', 10);
		$pdf->Cell(22, $height, BN::date($this->getVal('schedule'), 'd-m-Y'), 'TBR', 0, 'C', 0);
		$pdf->SetFont('helvetica','', 9);
		$pdf->MultiCell(30, $height, LOC_PDF_TEAM, 'TB', 'C', 0, 0);
		$pdf->SetFont('helvetica','B', 14);
		$pdf->Cell(0, $height, $aTeam->getVal('name'), 'TBR', 1, 'C', 0);

		// Heure prevue de la rencontre et de remise au JA
		$pdf->SetFont('helvetica','', 9);
		$pdf->MultiCell(33, $height, LOC_PDF_START_TIME, 'LTB', 'L', 0, 0);
		$pdf->SetFont('helvetica','B', 10);
		$pdf->Cell(22, $height, BN::date($this->getVal('schedule'), 'H:i'), 'TB', 0, 'C', 0);
		$pdf->SetFont('helvetica','', 9);
		$pdf->MultiCell(40, $height, LOC_PDF_DELAY, 'LB', 'L', 0, 0);
		$pdf->SetFont('helvetica','', 9);
		$pdf->Cell(0, $height, '', 'TBR', 1, 'L', 0);

		// Message indiquant qu'il n'y a pas d'ordre predefini
		$pdf->ln(5);
		$pdf->SetFont('helvetica','B', 10);
		$pdf->Cell(0, 0, LOC_PDF_NO_ORDER, 0, 1, 'C', 0);

		// Liste de matches
		$matchIds  = $this->getMatchIds();
		$height = 12;
		$pdf->multiCell(30, $height, LOC_PDF_ORDER_PROPOSED, 1, 'C', 0, 0);
		$pdf->Cell(25, $height, LOC_PDF_MATCH, 1, 0, 'C', 0);
		$pdf->Cell(0, $height, LOC_PDF_TEAM_COMPOSITION, 1, 1, 'C', 0);
		$height = 110 / count($matchIds);
		$pdf->SetFont('helvetica','', 10);
		foreach($matchIds as $matchId)
		{
			// Première ligne
			$cMatch = new Cmatch($matchId);
			if ($aTeam->getVal('id') == $cMatch->getVal('teamhid'))
			{
				$str = $cMatch->getVal('famh1') . ' ' . $cMatch->getVal('firsth1').
					' - ' . $cMatch->getVal('labcatageh1') . ' - ' . $cMatch->getVal('levelh1');
				if (trim($str) == '-  -') $str = '';
				$player1 = trim($str);
				$str = $cMatch->getVal('famh2') . ' ' . $cMatch->getVal('firsth2').
					' - ' . $cMatch->getVal('labcatageh2') . ' - ' . $cMatch->getVal('levelh2');
				if (trim($str) == '-  -') $str = '';
				$player2 = trim($str);
			}
			else if ($aTeam->getVal('id') == $cMatch->getVal('teamvid'))
			{
				$str = $cMatch->getVal('famv1') . ' ' . $cMatch->getVal('firstv1').
					' - ' . $cMatch->getVal('labcatagev1') . ' - ' . $cMatch->getVal('levelv1');
				if (trim($str) == '-  -') $str = '';
				$player1 = trim($str);
				$str = $cMatch->getVal('famv2') . ' ' . $cMatch->getVal('firstv2').
					' - ' . $cMatch->getVal('labcatagev2') . ' - ' . $cMatch->getVal('levelv2');
				if (trim($str) == '-  -') $str = '';
				$player2 = trim($str);
			}
			else
			{
				$player1=$player2='';
			}

			$pdf->Cell(30, $height/2, '', 'TLR', 0, 'L', 0);
			$str = $cMatch->getVal('labdiscir');
			$pdf->Cell(25, $height/2, $str, 'TLR', 0, 'C', 0);
			$pdf->Cell(0, $height/2, $player1, 'TLR', 1, 'L', 0);

			// Deuxime ligne
			$pdf->Cell(30, $height/2, '', 'LBR', 0, 'L', 0);
			$pdf->Cell(25, $height/2, '', 'LBR', 0, 'C', 0);
			$pdf->Cell(0, $height/2, $player2, 'LBR', 1, 'L', 0);
		}

		// Visa et remarques
		$pdf->ln(5);
		$pdf->SetFont('helvetica','', 8);
		$pdf->Cell(95, 10, 'Nom et signature du capitaine', 'LTR', 0, 'L');
		$pdf->Cell(95, 10, 'Heure de dépot', 1, 1, 'L');
		$pdf->Cell(95, 10, '', 'LR', 0);
		$pdf->Cell(95, 10, 'Observation du JA', 'TLR', 1, 'L');
		$pdf->Cell(95, 30, '', 'LRB', 0, 'L', 0);
		$pdf->Cell(95, 30, '', 'LRB', 1, 'L', 0);

		return;
	}
	
	public function freeCourt($aNumCourt)
	{
		$q = new Bn_query('tie', '_team');
		$q->setValue('tie_pbl', -1);
		$q->updateRow('tie_pbl=' . $aNumCourt);
		unset($q);
	}

	/**
	 * Retourne la liste des rencontres
	 *
	 */
	public static function getTieIds()
	{
		$q = new Bn_query('tie', '_team');
		$q->setFields('tie_id');
		$q->setOrder('tie_schedule');
		$tieIds = $q->getCol();
		unset($q);
		return $tieIds;
	}

	/**
	 * Pdf de la feuille de rencontre
	 *
	 */
	public function pdfTie($aPdf = null)
	{
		$locale = Bn::GetLocale();
		require_once 'Badnetteam/Locale/' .$locale . '/Tevent.inc';

		// Donnees
		$cEvent = new Cevent();
		$matchIds = $this->getMatchIds();

		// Creation du document
		if (empty($aPdf))
		{
		include_once 'Bn/Bnpdf.php';
		$pdf = new Bnpdf();
		$pdf->setAutoPageBreak(false);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);
		$pdf->start('L');
		}
		else $pdf = $aPdf;
		
		$pdf->AddPage('L');
		$pdf->SetFont('helvetica', 'B', 16);
		$pdf->SetFillColor(255);
		$pdf->SetTextColor(0);
		$str = $cEvent->getVal('name');
		$pdf->Cell(0, 0, $str, '', 1, 'L');
		$pdf->SetFont('helvetica', 'B', 14);
		$str = LOC_PDF_TIEFORM;
		$pdf->Cell(0, 0, $str, '', 1, 'L');
		$pdf->Ln(10);

		$pdf->setY(25);
		$pdf->SetFont('helvetica','B',8);

		// Informations du tournoi : nom, date, groupes, lieu
		$pdf->SetFont('helvetica', 'B', 16);
		$pdf->Cell(40, 10, Bn::date($this->getVal('schedule'), 'd-m-Y'), 0, 0, 'L', 0);
		$pdf->SetX(0);
		$pdf->Cell(0, 10, Bn::date($this->getVal('schedule'), 'H:i'), 0, 0, 'C', 0);
		//      $pdf->Cell(0,5,$PDF_LIEU.' :  '.$event['evnt_place'],1,1,'C',0);
		$pdf->Cell(0, 10, $this->getVal('division'), 0, 1, 'R', 0);
		$pdf->Ln(1);

		// Entete de colonne : premiere ligne
		$pdf->SetFont('helvetica', '', 9);
		$pdf->Cell(10, 8, LOC_PDF_ORDER, 'TLR', 0, 'C', 0);
		$pdf->Cell(10, 8, LOC_PDF_COURT, 'TLR', 0, 'C', 0);
		$pdf->Cell(15, 8, LOC_PDF_MATCHES, 'TLR', 0, 'C', 0);
		$pdf->SetFont('helvetica', 'B', 9);
		$pdf->Cell(16, 8, LOC_PDF_TEAMA, 'TL', 0, 'L', 0);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->multiCell(56, 16, $this->getVal('teamnameh'), 'TR', 'R', 0, 0);
		$pdf->SetFont('helvetica', 'B', 9);
		$pdf->Cell(16, 8, LOC_PDF_TEAMB, 'TL', 0, 'L', 0);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->multiCell(56, 16, $this->getVal('teamnamev'), 'TR', 'R', 0, 0);

		$pdf->Cell(38, 8, LOC_PDF_SCORES, 'TLR', 0, 'C', 0);
		$pdf->Cell(20, 8, LOC_PDF_VICTOIRES, 'TLR', 0, 'C', 0);
		$pdf->Cell(20, 8, LOC_PDF_GAMES, 'TLR', 0, 'C', 0);
		$pdf->Cell(0, 8, LOC_PDF_POINTS, 'TLR', 1, 'C', 0);

		// Entete de colonne : seconde ligne
		$pdf->Cell(10, 5, '', 'BLR', 0, 'L', 0);
		$pdf->Cell(10, 5, '', 'BLR', 0, 'L', 0);
		$pdf->Cell(15, 5, '', 'BLR', 0, 'L', 0);
		$pdf->SetFont('helvetica', 'B', 11);
		$pdf->Cell(72, 5, $this->getVal('teamstamph') ,'BLR',0,'L',0);
		$pdf->Cell(72, 5, $this->getVal('teamstampv') ,'BLR',0,'L',0);

		$pdf->SetFont('helvetica','',10);
		$pdf->Cell(38, 5, '', 'BLR', 0, 'L', 0);
		$pdf->Cell(10, 5, 'A', 'BLR', 0, 'C', 0);
		$pdf->Cell(10, 5, 'B', 'BLR', 0, 'C', 0);
		$pdf->Cell(10, 5, 'A', 'BLR', 0, 'C', 0);
		$pdf->Cell(10, 5, 'B', 'BLR', 0, 'C', 0);
		$pdf->Cell(10, 5, 'A', 'BLR', 0, 'C', 0);
		$pdf->Cell(10, 5, 'B', 'BLR', 1, 'C', 0);

		// Hauteur de chaque ligne
		// Calcul du nombre de matches de la rencontre
		$nb_matches = count($matchIds);
		$hauteur_ligne = 45/$nb_matches;

		// Initialiser les totaux
		$totResultH = 0;
		$totResultV = 0;
		$totGamesH = 0;
		$totGamesV = 0;
		$totPointsH = 0;
		$totPointsV = 0;

		// Pour chaque match on affiche
		foreach($matchIds as $matchId)
		{
			$cMatch = new Cmatch($matchId);
			$oScore = $cMatch->getOscore();

			$resultH = $resultV = '';
			$gamesH = $gamesV = '';
			$pointsH = $pointsV = '';

			switch($cMatch->getVal('resulth'))
			{
				case OPAIR_RES_WIN:
				case OPAIR_RES_WINWO:
					$resultH = 1;
					$resultV = 0;
					$gamesH = $oScore->getNbWinGames();
					$gamesV = $oScore->getNbLoosGames();
					$pointsH = $oScore->getNbWinPoints();
					$pointsV = $oScore->getNbLoosPoints();
					break;
				case OPAIR_RES_WINAB:
					$resultH = 1;
					$resultV = 0;
					$gamesH = $oScore->getNbWinGames();
					$gamesV = $oScore->getNbLoosGames();
					$pointsH = $oScore->getNbWinPoints();
					$pointsV = $oScore->getNbLoosPoints();
					break;
				case OPAIR_RES_LOOSE:
				case OPAIR_RES_LOOSEWO:
					$resultH = 0;
					$resultV = 1;
					$gamesH = $oScore->getNbLoosGames();
					$gamesV = $oScore->getNbWinGames();
					$pointsH = $oScore->getNbLoosPoints();
					$pointsV = $oScore->getNbWinPoints();
					break;
				case OPAIR_RES_LOOSEAB:
					$resultH = 0;
					$resultV = 1;
					$gamesH = $oScore->getNbLoosGames();
					$gamesV = $oScore->getNbWinGames();
					$pointsH = $oScore->getNbWinPoints();
					$pointsV = $oScore->getNbLoosPoints();
					break;
			}
			$score = $cMatch->getVal('score');

			// Ordre du match
			$pdf->Cell(10, $hauteur_ligne, $cMatch->getVal('rank'), 'TLR', 0, 'C', 0);
			// Terrain
			$court = $cMatch->getVal('court');
			if ($court==0) $court='';
			$pdf->Cell(10, $hauteur_ligne, $court, 'TLR', 0, 'C', 0);
			// Type du match
			$pdf->Cell(15, $hauteur_ligne, $cMatch->getVal('labdiscir'), 'TLR', 0, 'C', 0);
			// Noms des joueurs
			$str = $cMatch->getVal('famh1') . ' ' . $cMatch->getVal('firsth1').
			' - ' . $cMatch->getVal('labcatageh1') . ' - ' . $cMatch->getVal('levelh1');
			if (trim($str) == '-  -') $str = '';
			$str = trim($str);
			$pdf->Cell(72, $hauteur_ligne, $str, 'TLR', 0, 'L', 0);
			$str = $cMatch->getVal('famv1') . ' ' . $cMatch->getVal('firstv1').
			' - ' . $cMatch->getVal('labcatagev1') . ' - ' . $cMatch->getVal('levelv1');
			if (trim($str) == '-  -') $str = '';
			$str = trim($str);
			$pdf->Cell(72, $hauteur_ligne, $str, 'TLR', 0, 'L', 0);
			$pdf->Cell(38, $hauteur_ligne, $score ,'TLR', 0, 'L', 0); // score
			$pdf->Cell(10, $hauteur_ligne, $resultH, 'TLR',0,'C',0); // Victoire A
			$pdf->Cell(10, $hauteur_ligne, $resultV, 'TLR',0,'C',0); // Victoire B
			$pdf->Cell(10, $hauteur_ligne, $gamesH, 'TLR',0,'C',0); // Sets A
			$pdf->Cell(10, $hauteur_ligne, $gamesV, 'TLR',0,'C',0); // Sets B
			$pdf->Cell(10, $hauteur_ligne, $pointsH, 'TLR',0,'C',0); // Points A
			$pdf->Cell(10, $hauteur_ligne, $pointsV, 'TLR', 1, 'C', 0); // Points B

			// Totaux
			$totResultH += $resultH;
			$totResultV += $resultV;
			$totGamesH += $gamesH;
			$totGamesV += $gamesV;
			$totPointsH += $pointsH;
			$totPointsV += $pointsV;

			// Seconde Ligne
			$pdf->Cell(10, $hauteur_ligne, '', 'BLR', 0, 'L', 0);
			$pdf->Cell(10, $hauteur_ligne, '', 'BLR', 0, 'L', 0);
			$pdf->Cell(15, $hauteur_ligne, '', 'BLR', 0, 'L', 0);
			$str = $cMatch->getVal('famh2') . ' ' . $cMatch->getVal('firsth2').
			' - ' . $cMatch->getVal('labcatageh2') . ' - ' . $cMatch->getVal('levelh2');
			if (trim($str) == '-  -') $str = '';
			$pdf->Cell(72, $hauteur_ligne, $str, 'BLR', 0, 'L', 0); // partenaire de double Equipe A
			$str = $cMatch->getVal('famv2') . ' ' . $cMatch->getVal('firstv2').
			' - ' . $cMatch->getVal('labcatagev2') . ' - ' . $cMatch->getVal('levelv2');
			if (trim($str) == '-  -') $str = '';
			$str = trim($str);
			$pdf->Cell(72, $hauteur_ligne, $str, 'BLR', 0, 'L', 0); // partenaire de double Equipe B
			$pdf->Cell(38,$hauteur_ligne, $cMatch->getVal('length'), 'BLR', 0, 'C', 0); // Duree
			$pdf->Cell(10,$hauteur_ligne,''    ,'BLR',0,'L',0);
			$pdf->Cell(10,$hauteur_ligne,''    ,'BLR',0,'L',0);
			$pdf->Cell(10,$hauteur_ligne,''    ,'BLR',0,'L',0);
			$pdf->Cell(10,$hauteur_ligne,''    ,'BLR',0,'L',0);
			$pdf->Cell(10,$hauteur_ligne,''    ,'BLR',0,'L',0);
			$pdf->Cell(10,$hauteur_ligne,''    ,'BLR',1,'L',0);

			unset($cMatch);
			unset($cScore);
		}

		$pdf->Cell(179, $hauteur_ligne, '', '', 0, '', 0); // score
		$pdf->Cell(38, $hauteur_ligne, LOC_PDF_TOTAUX, 'R', 0, 'R', 0);
		$pdf->Cell(10, $hauteur_ligne, $totResultH, 1, 0, 'C', 0); // Match A
		$pdf->Cell(10, $hauteur_ligne, $totResultV, 1, 0, 'C', 0); // Mathc B
		$pdf->Cell(10, $hauteur_ligne, $totGamesH, 1, 0, 'C', 0); // Sets A
		$pdf->Cell(10, $hauteur_ligne, $totGamesV, 1, 0, 'C', 0); // Sets B
		$pdf->Cell(10, $hauteur_ligne, $totPointsH, 1, 0, 'C', 0); // Points A
		$pdf->Cell(10, $hauteur_ligne, $totPointsV, 1, 1, 'C', 0); // Points B
		$pdf->Ln(1);

		// Qui est le vainqueur ?
		if(($totResultH == 0) && ($totResultV ==0))	$winner = "";
		elseif( $totResultH > $totResultV )	$winner = $this->getVal('teamnameh');
		elseif( $totResultV > $totResultH )	$winner = $this->getVal('teamnamev');
		else $winner = LOC_PDF_EQUAL;

		// Recapitulatif  : Vainqueur, Score et Totaux Victoires, Sets, Points
		$pdf->Cell(119, 8, LOC_PDF_WINNER . strtoupper($winner), '', 0, 'L', 0);

		if( ($totResultH == 0) && ($totResultV==0) ) $pdf->Cell(68, 8, LOC_PDF_SCORE, '', 0, 'L', 0);
		elseif( $totResultH > $totResultV ) $pdf->Cell(68, 8, LOC_PDF_SCORE . $totResultH . ' / ' . $totResultV, '', 0, 'L', 0);
		else $pdf->Cell(68, 8, LOC_PDF_SCORE . $totResultV . ' / ' . $totResultH, '', 0, 'L', 0);
		$pdf->Ln(8);

		$pdf->Cell(50, 8, '', 0, 0, 'l', 0);
		$pdf->Cell(10, 8, '', 0, 0, 'l', 0);
		$pdf->Cell(50, 8, LOC_PDF_VISA_CAPTAIN.' A : ', 'B', 0, 'l', 0);
		$pdf->Cell(10, 8, '',0,0,'l',0);
		$pdf->Cell(49, 8, LOC_PDF_VISA_CAPTAIN.' B : ' , 'B', 0, 'l', 0);
		$pdf->Cell(10, 8, '', 0, 0, 'l', 0);
		$pdf->Cell(0, 8, LOC_PDF_VISA_REFEREE, 'B', 1, 'C', 0);

		if (empty($aPdf))
		{
		$filename = '../Temp/Pdf/tie_' . $this->getVal('id') . '.pdf';
		$pdf->end($filename);
		}
		return false;
	}

	public function getMatchIds($aOrders = array('rank'))
	{
		$q = new Bn_query('match', '_team');
		$q->setFields('mtch_id');
		$q->addWhere('mtch_tieid=' . $this->getVal('id'));
		$order = implode(', mtch_', $aOrders);
		$q->setOrder('mtch_'. $order);
		$ids = $q->getCol();
		return $ids;
	}

	public function __construct($aTieId)
	{
		$q = new Bn_query('tie', '_team');
		$q->setFields('tie_division');
		$q->addField('tie_id', 'id');
		$q->addField('tie_division',  'division');
		$q->addField('tie_group',     'groupe');
		$q->addField('tie_schedule',  'schedule');
		$q->addField('tie_step',      'step');
		$q->addField('tie_sporthall', 'sporthall');
		$q->addField('tie_pos',    'pos');
		$q->addField('tie_numid',  'numid');
		$q->addField('tie_pointh', 'pointh');
		$q->addField('tie_pointv', 'pointv');
		$q->addField('tie_pbl', 'pbl');
		$q->leftJoin('team th', 'th.team_id = tie_teamhid');
		$q->addField('th.team_name',  'teamnameh');
		$q->addField('th.team_stamp', 'teamstamph');
		$q->addField('th.team_id',    'teamhid');
		$q->addField('th.team_numid', 'teamhnumid');
		$q->leftJoin('team tv', 'tv.team_id = tie_teamvid');
		$q->addField('tv.team_name',  'teamnamev');
		$q->addField('tv.team_stamp', 'teamstampv');
		$q->addField('tv.team_id',    'teamvid');
		$q->addField('tv.team_numid', 'teamvnumid');
		if ( !empty($aTieId) ) $q->setWhere('tie_id=' . $aTieId);
		$this->_fields = $q->getRow();
	}

	public function setVal($aName, $aValue)
	{
		$this->_fields[$aName] = $aValue;
		return $aValue;
	}

	public function unSetVal($aName)
	{
		unset($this->_fields[$aName]);
	}

	public function getVal($aName, $aDefault = null)
	{
		if ( isset($this->_fields[$aName]) ) $val = $this->_fields[$aName];
		else $val = $aDefault;
		return $val;
	}

	public function save()
	{
		// Recuperer les listes des champs de la table
		$q = new BN_query('tie', '_team');
		$cols = $q->getColumnDef();

		// Filtrer les valeurs a mettre a jour
		foreach( $this->_fields as $key => $value)
		{
			if ( in_array('tie_'.$key, $cols) )
			{
				$q->addValue('tie_'.$key, $value);
			}
		}

		$q->setWhere('tie_id=' . $this->getVal('id', -1));
		$id = $q->updateRow();
		return $id;
	}
}
?>
