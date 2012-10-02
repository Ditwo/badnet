<?php
/*****************************************************************************
 !   Module     : pdf
 !   File       : $File$
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.8 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/04/03 06:49:03 $
 ******************************************************************************/

require_once "pdfbase.php";
require_once "base.php";
require_once "utils/utdraw.php";
require_once "utils/utround.php";
require_once "utils/utteam.php";
require_once "utils/utmatch.php";


class  pdfScoreSheet extends pdfBase
{
	// {{{ affichage_match_indiv()
	/**
	 * Select the informations for a match from an individual event
	 *
	 * @return void
	 */

	function affichage_match_indiv($matchId)
	{
		require_once "utils/objmatch.php";
		$match = new objMatch($matchId);
		$ut = new utils();
		$isSquash = $ut->getParam('issquash', false);
		if ($isSquash) $this->_affiche_match_squash($match, $matchId);
		else $this->_affiche_match($match, $matchId);
	}

	// {{{ affichage_match()
	/**
	 * Select the informations for a match from a team event
	 *
	 * @return void
	 */
	function affichage_match($matchId)
	{

		$utfpdf =  new baseFpdf();
		require_once "utils/objmatch.php";
		$match = new objMatch($matchId);
		$ut = new utils();
		$isSquash = $ut->getParam('issquash', false);
		if ($isSquash) $this->_affiche_match_squash($match, $matchId);
		else $this->_affiche_match($match, $matchId);

		unset($fullmatch);
	}

	function Ellipse($x, $y, $rx, $ry, $style='D')
	{
		if($style=='F')
		$op='f';
		elseif($style=='FD' || $style=='DF')
		$op='B';
		else
		$op='S';
		$lx=4/3*(M_SQRT2-1)*$rx;
		$ly=4/3*(M_SQRT2-1)*$ry;
		$k=$this->k;
		$h=$this->h;
		$this->_out(sprintf('%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
		($x+$rx)*$k,($h-$y)*$k,
		($x+$rx)*$k,($h-($y-$ly))*$k,
		($x+$lx)*$k,($h-($y-$ry))*$k,
		$x*$k,($h-($y-$ry))*$k));
		$this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
		($x-$lx)*$k,($h-($y-$ry))*$k,
		($x-$rx)*$k,($h-($y-$ly))*$k,
		($x-$rx)*$k,($h-$y)*$k));
		$this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
		($x-$rx)*$k,($h-($y+$ly))*$k,
		($x-$lx)*$k,($h-($y+$ry))*$k,
		$x*$k,($h-($y+$ry))*$k));
		$this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',
		($x+$lx)*$k,($h-($y+$ry))*$k,
		($x+$rx)*$k,($h-($y+$ly))*$k,
		($x+$rx)*$k,($h-$y)*$k,
		$op));
	}


	/**
	 * Select the informations for a match from an individual event
	 *
	 * @return void
	 */
	function affichage_match_lite($matchIds)
	{
		$utfpdf =  new baseFpdf();
		$cpt = 0;
		$this->orientation = 'P';
		$this->_isHeader = false;
		$this->_isFooter = false;
		$this->SetAutoPageBreak(false);

		require_once "utils/objmatch.php";
		foreach($matchIds as $matchId)
		{
	  $match = new objMatch($matchId);
	  if( (($cpt++) % 2) == 0)
	  $this->AddPage('P');
	  else
	  $this->setXY(10,160);

	  // Ligne de separation
	  $this->SetLineWidth(0.5);
	  $this->SetDrawColor(156,168,92);
	  $this->Line(10,149 ,200 , 149);
	  $this->SetLineWidth(0.2);
	  $this->SetDrawColor(0);
	  $this->_affiche_match_lite($match);
	  unset($match);
		}
	}

	/**
	 * Display the score sheet for a match
	 *
	 * @return void
	 */
	function _affiche_match($fullmatch, $matchId)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require $file;
		$utfpdf =  new baseFpdf();
		$tieDate = new utDate();
		$this->_isHeader = false;
		$ut = new utils();

		// Recuperer les echanges pour remplir la feuille de match
		//print_r($fullmatch);
		$rallies = $this->analyse($fullmatch, 31, true);
		//print_r($rallies);

		// Creation du document
		$this->orientation = 'L';
		$this->AddPage('L');
		$this->SetFont('Arial','B',8);
		// Informations du tournoi
		$this->tete();
		$auj = new utDate();
		$joueur1 = $fullmatch->getFirstTopName();
		$joueur2 = $fullmatch->getSecondTopName();
		$joueur3 = $fullmatch->getFirstBottomName();
		$joueur4 = $fullmatch->getSecondBottomName();
		$joueurids = array($fullmatch->getFirstTopId(),
		$fullmatch->getSecondTopId(),
		$fullmatch->getFirstBottomId(),
		$fullmatch->getSecondBottomId());

		$score = $rallies['score'];
		$scg = array('','','');
		$scd = array('','','');
		if (isset($score[$joueurids[0]])) $scg = $score[$joueurids[0]];
		if (isset($score[$joueurids[1]])) $scg = $score[$joueurids[1]];
		if (isset($score[$joueurids[2]])) $scd = $score[$joueurids[2]];
		if (isset($score[$joueurids[3]])) $scd = $score[$joueurids[3]];

		$joueurs = array($joueur1,$joueur2,$joueur3,$joueur4);
		$joueursh = array(reset(explode(" ",$joueur1)),
		reset(explode(" ",$joueur2)),
		reset(explode(" ",$joueur3)),
		reset(explode(" ",$joueur4)));
		$mcourt = $fullmatch->getCourt();
		$mnum = $fullmatch->getNum();
		$draw = $fullmatch->getDrawName();
		$stage = $fullmatch->getStageName();
		$step = $fullmatch->getStepName();
		$auj->setIsoDate($fullmatch->getSchedule());

		$hauteur_ligne = 5;
		$t_colA = 15;
		$t_colB = 25;
		$t_colC = 5;
		$t_colD = 70;
		$t_colE = 10;
		$t_colF = 5;
		$t_colG = 5;
		$t_colH = 12;

		$this->SetFont('Arial','B',12);
		$this->SetFillColor(255);
		$this->Cell($t_colA+$t_colB,$hauteur_ligne,"$PDF_NUM $mnum",'LT',0,'C',1,'');

		$this->Cell(180,$hauteur_ligne, '','T',0,'C',1,'');
		$this->SetFont('Arial','B',96);
		$this->SetTextColor(210);
		$this->SetTextColor(0);
		$this->SetFont('Arial','B',8);
		$officials = $utfpdf->getOfficials($matchId);
		$this->Cell($t_colH, $hauteur_ligne, $PDF_UMPIRE, 'T', 0, 'R', 1, '');
		$this->Cell(0,$hauteur_ligne,$officials['umpire'],'TR',1,'L',1,'');

		$this->Cell($t_colA+$t_colB,$hauteur_ligne, $draw,'L',0,'C',1,'');
		$this->Cell($t_colC,$hauteur_ligne,'','TLR',0,'L',1,'');
		$this->SetFont('Arial','B',10);
		$this->Cell($t_colD,$hauteur_ligne, $joueur1,'TLR',0,'C',1,'');
		$this->SetFont('Arial','B',8);
		$this->Cell($t_colE,$hauteur_ligne, $scg[0],'TLBR',0,'L',1,'');
		$this->Cell($t_colF,$hauteur_ligne,'','LR',0,'L',1,'');
		$this->SetFillColor(220);
		$this->Cell($t_colE,$hauteur_ligne, $scd[0],'TLBR',0,'L',1,'');
		$this->SetFont('Arial','B',10);
		$this->Cell($t_colD,$hauteur_ligne,''.$joueur3,'TLR',0,'C',1,'');
		$this->SetFont('Arial','B',8);
		$this->Cell($t_colG,$hauteur_ligne,'','TLR',0,'L',1,'');
		$this->SetFillColor(255);
		$this->Cell($t_colG,$hauteur_ligne,'','L',0,'L',1,'');
		$width = $this->GetStringWidth($PDF_SERVICE)+1;
		$this->Cell($t_colH, $hauteur_ligne, $PDF_SERVICE,'',0,'R',1,'');
		$this->Cell(0,$hauteur_ligne,$officials['service'],'R',1,'L',1,'');
		$this->SetFillColor(255);

		$this->Cell($t_colA+$t_colB, $hauteur_ligne, $stage, 'L', 0, 'C', 1, '');
		$this->Cell($t_colC,$hauteur_ligne,'','TR',0,'L',1,'');
		$this->SetFont('Arial','B',10);
		$this->Cell($t_colD,$hauteur_ligne,$joueur2,'LR',0,'C',1,'');
		$this->SetFont('Arial','B',8);
		$this->Cell($t_colE,$hauteur_ligne, $scg[1], 'TLRB',0,'L',1,'');
		$this->Cell($t_colF,$hauteur_ligne,'','LR',0,'L',1,'');
		$this->SetFillColor(220);
		$this->Cell($t_colE,$hauteur_ligne, $scd[1], 'TLRB',0,'L',1,'');
		$this->SetFont('Arial','B',10);
		$this->Cell($t_colD,$hauteur_ligne,$joueur4,'LR',0,'C',1,'');
		$this->SetFont('Arial','B',8);
		$this->SetFillColor(255);
		$this->Cell($t_colG,$hauteur_ligne,'','LT',0,'L',1,'');
		$this->Cell($t_colG,$hauteur_ligne,'','',0,'L',1,'');
		$this->Cell($t_colH, $hauteur_ligne, $PDF_START,'',0,'',1,'');
		$this->Cell(0,$hauteur_ligne, $rallies['start'],'R',1,'L',1,'');

		$this->Cell($t_colA+$t_colB,$hauteur_ligne, $step, 'L', 0, 'C', 1, '');
		$this->Cell($t_colC,$hauteur_ligne,'','R',0,'L',1,'');
		$this->Cell($t_colD, $hauteur_ligne,'','LRB',0,'C',1,'');
		$this->Cell($t_colE,$hauteur_ligne, $scg[2], 'TLRB',0,'L',1,'');
		$this->Cell($t_colF,$hauteur_ligne,'','LR',0,'L',1,'');
		$this->SetFillColor(220);
		$this->Cell($t_colE,$hauteur_ligne, $scd[2], 'TLRB',0,'L',1,'');
		$this->Cell($t_colD,$hauteur_ligne,'','LB',0,'C',1,'');
		$this->SetFillColor(255);
		$this->Cell($t_colG,$hauteur_ligne,'','L',0,'L',1,'');
		$this->Cell($t_colG,$hauteur_ligne,'','',0,'L',1,'');
		$this->Cell($t_colH, $hauteur_ligne, $PDF_END_M, '', 0, 'R', 1, '');
		$this->Cell(0,$hauteur_ligne,$rallies['end'],'R',1,'L',1,'');

		$this->Cell($t_colA+$t_colB, $hauteur_ligne, $auj->getDateTime(),'LB',0,'C',1,'');
		$this->Cell($t_colC,$hauteur_ligne,'','B',0,'L',1,'');
		$this->Cell($t_colD,$hauteur_ligne,'','TB',0,'L',1,'');
		$this->Cell($t_colE,$hauteur_ligne,'','TB',0,'L',1,'');
		$this->Cell($t_colF,$hauteur_ligne,'','B',0,'L',1,'');
		$this->Cell($t_colE,$hauteur_ligne,'','TB',0,'L',1,'');
		$this->Cell($t_colD,$hauteur_ligne,'','TB',0,'L',1,'');
		$this->Cell($t_colC,$hauteur_ligne,'','B',0,'L',1,'');
		$this->Cell($t_colC,$hauteur_ligne,'','B',0,'L',1,'');
		$this->Cell($t_colH, $hauteur_ligne, $PDF_LENGTH_M, 'B', 0, 'R', 1, '');
		$this->Cell(0, $hauteur_ligne, $rallies['length'], 'BR', 1, 'L', 1, '');
		$this->Ln(1);

		if ($fullmatch->isTopWin() ) $this->Ellipse(91,45,35,10);
		if ($fullmatch->isBottomWin() ) $this->Ellipse(185,45,35,10);
		$bord = 'TBRL';

		$isSquash = $ut->getParam('issquash', false);
		if ($fullmatch->isDouble() || $isSquash) $hauteur = 4.5;
		else $hauteur = 9;
		$nbCase = 31;
		$width = (282-80)/$nbCase;

		$this->SetFont('Arial','B',8);
		$scs = $rallies['sc'];
		$endSets = $rallies['endSet'];
		$endSet = reset($endSets);
		//print_r($scs);
		for($j=0;$j<6;$j++)
		{
			for($k=0;$k<2;$k++)
			{
				if ($k) $this->SetFillColor(220);
				else  $this->SetFillColor(255);
				$indice = $k*2;
				$nom = $joueurs[$indice];
				$nomsh = $joueursh[$indice];
				$jid = $joueurids[$indice];
				$this->Cell(45,$hauteur,$nom,'TBRL',0,'L',1,'');
				$lw = $this->SetLineWidth(0.5);
				$this->SetLineWidth($lw);
				for($i=0; $i<$nbCase; $i++)
				{
					if ($i==0) $lw = $this->SetLineWidth(0.5);
					else $this->SetLineWidth($lw);
					$ty = $i +$nbCase*$j;
					if (isset($scs[$ty]) )
					{
						if (isset($scs[$ty][$jid])) $sc = $scs[$ty][$jid];
						else $sc ='';
						$this->Cell($width,$hauteur, $sc, $bord, 0, 'L', 1,'');
						if ($ty == $endSet)
						{
							$endSet = next($endSets);
							$x[] = $this->GetX();
							$y[] = $this->GetY();
						}
					}
					else $this->Cell($width,$hauteur,'',$bord,0,'L',1,'');
				}
				$this->Cell(30,$hauteur,$nomsh,'TBRL',0,'R',1,'');
				$this->Ln();
				if ($fullmatch->isDouble() || $isSquash)
				{
					$indice = ($k*2)+1;
					$nom = $joueurs[$indice];
					$nomsh = $joueursh[$indice];
					$jid = $joueurids[$indice];
					$this->Cell(45,$hauteur,$nom,'TBRL',0,'L',1,'');
					for($i=0; $i<$nbCase; $i++)
					{
						if ($i==0) $lw = $this->SetLineWidth(0.5);
						else $this->SetLineWidth($lw);
						$ty = $i +$nbCase*$j;
						if (isset($scs[$ty]) )
						{
							if (isset($scs[$ty][$jid])) $sc = $scs[$ty][$jid];
							else $sc ='';
							$this->Cell($width,$hauteur, $sc, 'TBRL', 0, 'L', 1,'');
						}
						else $this->Cell($width, $hauteur,'','TBRL',0,'L',1,'');
					}
					$this->Cell(30,$hauteur,$nomsh,'TBRL',0,'R',1,'');
					$this->Ln();
				}
			}
			$this->Ln(2);
		}
		$this->SetFont('Arial','B',18);
		for($z=0; $z<count($endSets); $z++ )
		{
			$this->text($x[$z], $y[$z]+7, $scg[$z]);
			$this->text($x[$z]+10, $y[$z]+15, $scd[$z]);
			$this->Ellipse($x[$z]+8, $y[$z]+9, 11, 9);
			$this->line($x[$z], $y[$z]+15, $x[$z]+14, $y[$z]+3);
		}
		$this->SetFont('Arial','B',8);

		$this->Cell(65, 5, '', 0, 0, 'L');
		$this->Cell(100, 5, $PDF_UMP_SIGNATURE, 0, 0, 'L');
		$this->Cell(0, 5, $PDF_REF_SIGNATURE, 0, 1, 'L');
		if($mcourt != 0 )
		{
	  $this->SetFont('Arial','B',25);
	  $this->setXY(10,10);
	  $this->Cell(10, 10, $mcourt, '', 0, 'C', 1, '');
	  $this->setXY(277,10);
	  $this->Cell(10, 10, $mcourt, '', 0, 'C', 1, '');
		}

	}

	/**
	 * Display the score sheet for a match
	 *
	 * @return void
	 */
	function _affiche_match_squash($fullmatch, $matchId)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require $file;
		$utfpdf =  new baseFpdf();
		$tieDate = new utDate();
		$this->_isHeader = false;
		$ut = new utils();

		// Tournoi
		$ute = new utevent();
		$eventId = utvars::getEventId();
		$event = $ute->getEvent($eventId);
		$meta = $ute->getMetaEvent($eventId);

		// Recuperer les echanges pour remplir la feuille de match
		//print_r($fullmatch);
		$rallies = $this->analyse($fullmatch, 40, false);
		//print_r($rallies);

		// Creation du document
		$this->orientation = 'L';
		$this->AddPage('L');
		$this->SetFont('Arial','B',8);
		// Informations du tournoi
		//$this->tete();
		$auj = new utDate();
		$joueur1 = $fullmatch->getFirstTopName();
		$joueur2 = $fullmatch->getSecondTopName();
		$joueur3 = $fullmatch->getFirstBottomName();
		$joueur4 = $fullmatch->getSecondBottomName();
		$joueurids = array($fullmatch->getFirstTopId(),
		$fullmatch->getSecondTopId(),
		$fullmatch->getFirstBottomId(),
		$fullmatch->getSecondBottomId());

		$score = $rallies['score'];
		$scg = array('','','','','');
		$scd = array('','','','','');
		if (isset($score[$joueurids[0]])) $scg = $score[$joueurids[0]];
		if (isset($score[$joueurids[1]])) $scg = $score[$joueurids[1]];
		if (isset($score[$joueurids[2]])) $scd = $score[$joueurids[2]];
		if (isset($score[$joueurids[3]])) $scd = $score[$joueurids[3]];

		$joueurs = array($joueur1,$joueur2,$joueur3,$joueur4);
		$joueursh = array(reset(explode(" ",$joueur1)),
		reset(explode(" ",$joueur2)),
		reset(explode(" ",$joueur3)),
		reset(explode(" ",$joueur4)));
		$mcourt = $fullmatch->getCourt();
		$mnum = $fullmatch->getNum();
		$draw = $fullmatch->getDrawName();
		$stage = $fullmatch->getStageName();
		$step = $fullmatch->getStepName();
		$auj->setIsoDate($fullmatch->getSchedule());
		$officials = $utfpdf->getOfficials($matchId);

			  // Logo 
	  $logo = 'ffs.jpg';
	  $logo = utimg::getLogo($logo);
	  
	  if(is_file($logo))
	  {
	  		$size = @getImagesize($logo);
			$width  = $size[0];
			$height = $size[1];
			$few = 40 / $size[0];
			$feh = 40 / $size[1];
			$fe = min(1, $feh, $few);
			$width  = intval($size[0] * $fe);
			$height = intval($size[1] * $fe);
	    	$this->Image($logo, 5, 5, $width, $height, '');
	  }
		
		
		$hauteur_ligne = 5;
		$this->SetFont('Arial','B',12);
		$this->Cell(0, $hauteur_ligne, 'FEUILLE DE MATCH', 0, 1, 'C');
		$this->SetFont('Arial','',10);
		$this->setX(40);
		$this->Cell(30, $hauteur_ligne, 'COMPETITION :', 0, 0, 'R');
		$this->Cell(180, $hauteur_ligne, $event['evnt_name'], 1, 1, 'L');
		$this->ln();
		$this->setX(40);
		$this->Cell(30, $hauteur_ligne, 'JOUEUR 1 :', 0, 0, 'R');
		$this->Cell(75, $hauteur_ligne, $joueur1, 'LRB', 0, 'L');
		$this->Cell(30, $hauteur_ligne, 'JOUEUR 2 :', 0, 0, 'R');
		$this->Cell(75, $hauteur_ligne, $joueur3, 'LRB', 1, 'L');
		$this->ln();
		$this->setX(40);
		$this->Cell(30, $hauteur_ligne, 'ARBITRE :', 0, 0, 'R');
		$this->Cell(45, $hauteur_ligne, $officials['umpire'], 'B', 0, 'L');
		$this->Cell(5, $hauteur_ligne, '', '', 0, 'L');
		$this->Cell(25, $hauteur_ligne, 'MARQUEUR :', 0, 0, 'R');
		$this->Cell(45, $hauteur_ligne, $officials['service'], 'B', 0, 'L');
		$this->Cell(5, $hauteur_ligne, '', '', 0, 'L');
		$this->Cell(55, $hauteur_ligne, 'Vainqueur :', 1, 1, 'L');

		$this->ln();
		$this->SetFont('Arial','',8);
		$this->Cell(30, $hauteur_ligne, '', 0, 0, 'C');
		$this->Cell(20, $hauteur_ligne, '1er jeu', 0, 0, 'C');
		$this->Cell(15, $hauteur_ligne, 'temps', 0, 0, 'C');
		$this->Cell(20, $hauteur_ligne, '2ème jeu', 0, 0, 'C');
		$this->Cell(15, $hauteur_ligne, 'temps', 0, 0, 'C');
		$this->Cell(20, $hauteur_ligne, '3ème jeu', 0, 0, 'C');
		$this->Cell(15, $hauteur_ligne, 'temps', 0, 0, 'C');
		$this->Cell(20, $hauteur_ligne, '4ème jeu', 0, 0, 'C');
		$this->Cell(15, $hauteur_ligne, 'temps', 0, 0, 'C');
		$this->Cell(20, $hauteur_ligne, '5ème jeu', 0, 0, 'C');
		$this->Cell(15, $hauteur_ligne, 'temps', 0, 0, 'C');
		$this->Cell(10, $hauteur_ligne, '', 0, 0, 'C');
		$this->SetFont('Arial', '', 10);
		$this->Cell(30, $hauteur_ligne, 'Début du match :', 0, 0, 'R');
		$this->Cell(30, $hauteur_ligne, $rallies['start'], 1, 1, 'C');

		$this->Cell(30, 2*$hauteur_ligne, 'SCORE :', 0, 0, 'L');
		$this->Cell(20, 2*$hauteur_ligne, $scg[0].'/'.$scd[0], 1, 0, 'C');
		$this->Cell(15, 2*$hauteur_ligne, '', 1, 0, 'C');
		$this->Cell(20, 2*$hauteur_ligne, $scg[1].'/'.$scd[1], 1, 0, 'C');
		$this->Cell(15, 2*$hauteur_ligne, '', 1, 0, 'C');
		$this->Cell(20, 2*$hauteur_ligne, $scg[2].'/'.$scd[2], 1, 0, 'C');
		$this->Cell(15, 2*$hauteur_ligne, '', 1, 0, 'C');
		$this->Cell(20, 2*$hauteur_ligne, $scg[3].'/'.$scd[3], 1, 0, 'C');
		$this->Cell(15, 2*$hauteur_ligne, '', 1, 0, 'C');
		$this->Cell(20, 2*$hauteur_ligne, $scg[4].'/'.$scd[4], 1, 0, 'C');
		$this->Cell(15, 2*$hauteur_ligne, '', 1, 0, 'C');
		$this->Cell(10, 2*$hauteur_ligne, '', 0, 0, 'C');
		$this->Cell(30, $hauteur_ligne, 'Fin du match :', 0, 0, 'R');
		$this->Cell(30, $hauteur_ligne, $rallies['end'], 1, 1, 'C');
		$this->setX(225);
		$this->Cell(30, $hauteur_ligne, 'Durée du match :', 0, 0, 'R');
		$this->Cell(30, $hauteur_ligne, $rallies['length'], 1, 1, 'C');
		$this->ln();

		/*
		 if ($fullmatch->isTopWin() ) $this->Ellipse(91,45,35,10);
		 if ($fullmatch->isBottomWin() ) $this->Ellipse(185,45,35,10);
		 */
		$bord = 'TBRL';
		$hauteur = 5;
		$nbCase = 40;
		$largeurNom = 60;
		$width = (275-$largeurNom)/$nbCase;

		$this->SetFont('Arial','B',8);
		$scs = $rallies['sc'];
		$endSets = $rallies['endSet'];
		$endSet = reset($endSets);
		//print_r($scs);
		for($j=0;$j<5;$j++)
		{
			for($k=0;$k<2;$k++)
			{
				if ($k) $this->SetFillColor(220);
				else  $this->SetFillColor(255);
				$indice = $k*2;
				$nom = $joueurs[$indice];
				$jid = $joueurids[$indice];
				$this->Cell($largeurNom, 2*$hauteur, $nom, 'TBRL', 0, 'L', 1, '');
				for($i=0; $i<$nbCase; $i++)
				{
					$ty = $i +$nbCase*$j;
					if (isset($scs[$ty]) )
					{
						if (isset($scs[$ty][$jid])) $sc = $scs[$ty][$jid];
						else $sc ='';
						$this->Cell($width,$hauteur, $sc, $bord, 0, 'L', 1,'');
						if ($ty == $endSet)
						{
							$endSet = next($endSets);
							$x[] = $this->GetX();
							$y[] = $this->GetY();
						}
					}
					else $this->Cell($width,$hauteur,'',$bord,0,'L',1,'');
				}
				$this->Ln();

				$indice = ($k*2)+1;
				$abs = $this->getX();
				$this->setX($abs+$largeurNom);
				$jid = $joueurids[$indice];
				for($i=0; $i<$nbCase; $i++)
				{
					$ty = $i +$nbCase*$j;
					if (isset($scs[$ty]) )
					{
						if (isset($scs[$ty][$jid])) $sc = $scs[$ty][$jid];
						else $sc ='';
						$this->Cell($width,$hauteur, $sc, 'TBRL', 0, 'L', 1,'');
					}
					else $this->Cell($width, $hauteur,'','TBRL',0,'L',1,'');
				}
				$this->Ln();
			}
			$this->Ln(5);
		}
		$this->SetFont('Arial','B',18);
		for($z=0; $z<count($endSets); $z++ )
		{
			$this->text($x[$z], $y[$z]+7, $scg[$z]);
			$this->text($x[$z]+10, $y[$z]+15, $scd[$z]);
			$this->Ellipse($x[$z]+8, $y[$z]+9, 11, 9);
			$this->line($x[$z], $y[$z]+15, $x[$z]+14, $y[$z]+3);
		}
	}

	function analyse($aMatch, $aNbCase, $aRs)
	{
		$uniId = reset(explode(';', $aMatch->getUniId()));
		$eventId = utvars::getEventId();
		//$path = dirname(__FILE__) . '/../../Live/' . $eventId . '/' . $uniId . '/set';
		$path = '/home/badnet/Public_html/BadmintonNetware/Live/'. $eventId . '/' . $uniId . '/set';
		//$path = '/home/badnet/Public_html/Squash_ffs/Live/'. $eventId . '/' . $uniId . '/set';
		//echo $path;
		$court = 0;
		$start = '';
		$end = '';
		$length= '';
		$sc = array();
		$score = array();
		$num = 0;
		$scg = '';
		$scd = '';
		$jdp = 0;
		$jdi = 0;
		$endSet = array();
		$ids = array(0,0,0,0);
		for ($i = 1; $i<4; $i++)
		{

			$file = $path . $i;
			if ( !file_exists($file) ) continue;
			//echo $file;
			$fd = fopen($file, 'r');
			$numMsg = -1;
			$nbRally = 0;
			while ( $str = fgets($fd, 4096) )
			{
				if($nbRally == $aNbCase)
				{
					foreach($ids as $id) $sc[$num][$id] = '';
					$num++;
				}
				//echo "$str\n";
				$toks = explode(';', $str);
				$action = $toks[1];
				//echo $action.';';
				// Ne pas traiter les message en double
				if ($toks[0] == $numMsg) continue;
				$numMsg = $toks[0];

				// suppression d'echange
				// Apres suppression, le score precedent est renvoye.
				// Il faut donc supprimer l'echange et le score precedent
				if ($action == 10)
				{
					$num-=2;
					if($num < 0) {$num = 0;$nbRally=0;}
					continue;
				}
				// Debut de jeu
				if ($action == 3)
				{
					// Recuperer l'heure de debut de match hh:mm:ss
					// et les id des joueurs
					if ($i ==1 )
					{
						$start = $toks[13];
						$ids = array($toks[7], $toks[8], $toks[10], $toks[11]);
					}
					// Serveur/receveur
					$serveurId = $toks[12];
					if ( $serveurId == $toks[7])$receiveId = $toks[10];
					else $receiveId = $toks[7];
					if ($aRs)
					{
							
						foreach($ids as $id)
						{
							if ($id == $serveurId)
							{
								$sc[$num][$id] = 'S';
								$sc[$num+1][$id] = '0';
							}
							else if ($id == $receiveId)
							{
								$sc[$num][$id] = 'R';
								$sc[$num+1][$id] = '0';
							}
							else
							{
								$sc[$num][$id] = '';
								$sc[$num+1][$id] = '';
							}
						}
						$num +=2;
						$nbRally+=2;
					}
					continue;
				}
				// Avertissement
				if ($action == 11)
				{
					// A = avertissement
					// F = faute
					// JA = appel JA
					// S = suspension de jeu
					// B = blessure
					// DisqualifiÃ© = disqualification par le JA (noir)
					// Abandon = Abandon
					// E = Erreur de zone de service
					if ($toks['5'] == 1) $type = 'A';
					else if ($toks['5'] == 2) $type = 'F';
					else if ($toks['5'] == 3) $type = 'DisqualifiÃ©';
					else $type = 'B';
					foreach($ids as $id) if ($id == $toks['6']) $sc[$num][$id] = $type;
					else $sc[$num][$id] = '';
					$num++;
					$nbRally++;
					continue;
				}
				// On ne traite que les fin d'echange
				if ($action != 4)
				{
					continue;
				}
				$court = $toks[2];
				foreach($ids as $id)
				{
					if($id == $toks[12])
					{
						if ($id == $toks[7] || $id == $toks[8]) $sc[$num][$id] = $toks[14];
						else $sc[$num][$id] = $toks[13];
					}
					else $sc[$num][$id] = '';
				}
				$jdp = $toks[7];
				$jdi = $toks[8];
				$scd = $toks[13];
				$scg = $toks[14];
				$num++;
				$nbRally++;
				$end = $toks[15];
			}
			fclose($fd);

			// memoriser le score final
			if ($ids[0] == $jdp || $ids[0] == $jdi )
			{
				$score[$ids[0]][] = $scg;
				$score[$ids[2]][] = $scd;
			}
			else
			{
				$score[$ids[0]][] = $scd;
				$score[$ids[2]][] = $scg;
			}

			// fin de set : completer la fin de ligne avec du vide
			$endSet[] = $num;
			$nb = $aNbCase - ($num%$aNbCase);
			for($j=0;$j<$nb;$j++)
			{
				foreach($ids as $id) $sc[$num][$id] = '';
				$num++;
			}

		}
		// Duree du match
		$sh = $sm = $ss = 0;
		if (!empty($start)) list($sh, $sm, $ss) = explode(':', $start);
		$eh = $em = $es = 0;
		if (!empty($end)) list($eh, $em, $es) = explode(':', $end);
		$length = intval((($eh*3600 + $em *60 + $es) - ($sh*3600 + $sm *60 + $ss))/60);
		if ($length > 0) $length .= ' mn';
		else $length = '';

		// rajouter des scores pour etre sur d'avoir les donnees pour 5 set
		if (isset($ids[0]))
		{
			$score[$ids[0]][] = '';
			$score[$ids[0]][] = '';
			$score[$ids[0]][] = '';
			$score[$ids[0]][] = '';
		}
		if (isset($ids[2]))
		{
			$score[$ids[2]][] = '';
			$score[$ids[2]][] = '';
			$score[$ids[2]][] = '';
			$score[$ids[2]][] = '';
		}

		$rallies = array('court' => $court,
						 'start' => $start,
						 'end' => $end,
						 'length' => $length,
						 'sc' => $sc,
						 'score' => $score,
						 'endSet' => $endSet );
		//print_r($rallies);
		return $rallies;

	}


	// {{{ tete($)
	/**
	 * Header des pages PDF
	 * en fonction de l'orientation de la page L ou P
	 * on affiche Badnet en filigramme
	 *
	 * @return void
	 */

	function tete()
	{

		$ute = new utevent();

		$maxWidth = ($this->orientation=='L') ? 297:210;
		$maxHeight = ($this->orientation=='L') ? 210:297;
		$maxWidth -= 20;
		$maxHeight -= 20;
		$utfpdf =  new baseFpdf();
		$eventId = utvars::getEventId();
		$event = $ute->getEvent($eventId);
		$meta = $ute->getMetaEvent($eventId);
		$this->SetLineWidth(0.5);
		$this->SetDrawColor(128);

		// Affichage du titre du tournoi
		//$this->SetFont($meta['evmt_titleFont'], '',
		//		 );
		$this->SetFont('swz921n', '', $meta['evmt_titleSize']);
		$this->Cell(0, 10, $event['evnt_name'], 0, 1, 'C');

		// Ligne de separation
		$this->SetLineWidth(1);
		$this->SetDrawColor(56,168,92);
		$this->SetFont('arial', '', 8);
		$this->Ln();
		//$this->Ln(1);
		$posx = $this->GetX();
		$posy = $this->GetY();
		$this->Line($posx, $posy, $maxWidth+$posx, $posy);

		$this->SetLineWidth(0.2);
		$this->SetDrawColor(0);

		// Se positionner apres la ligne
		$this->setXY($posx, $posy+2);
	}
	// }}}

	// {{{ _affiche_match_lite()
	/**
	 * Display the score sheet for a match
	 *
	 * @return void
	 */
	function _affiche_match_lite($match)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require $file;

		$utfpdf =  new baseFpdf();
		$tieDate = new utDate();

		$this->SetFont('Arial','B',8);
		// Informations du tournoi
		$this->tete();

		// On prÃ©pare les donnÃ©es
		$auj = new utDate();

		// hauteur standard
		$hauteur_ligne = 5;
		$t_colZ = 40;//45;
		$t_colY = 5;

		$t_colA = 20;//30;
		$t_colB = 40;//25;
		$t_colC = 20; //30;
		$t_colD = 40;//25;
		$t_colE = 15;
		$t_colF = 90;
		$t_colG = 10;
		$t_colH = 5;
		$t_colI = 5;

		// Le numÃ©ro de match
		$this->Cell($t_colH,$hauteur_ligne, '','',0,'R',1,'');
		$this->SetFont('Arial','B',10);
		$this->Cell($t_colF,$hauteur_ligne, "$PDF_NUM ".$match->getNum(),'',0,'C',1,'');

		// Le numÃ©ro de terrain
		$this->SetFont('Arial','',8);
		$this->Cell($t_colE,$hauteur_ligne, '','',0,'R',1,'');
		$this->Cell($t_colC,$hauteur_ligne, $PDF_COURT,'',0,'R',1,'');
		$this->Cell($t_colD,$hauteur_ligne, $match->getCourt(),'',0,'L',1,'');
		$this->Ln();

		// Le tableau
		$this->Cell($t_colH,$hauteur_ligne, '','',0,'R',1,'');
		$this->SetFont('Arial','B',10);
		$this->Cell($t_colF,$hauteur_ligne,$match->getDrawName(),'',0,'C',1,'');

		// Vide
		$this->SetFont('Arial','',8);
		$this->Cell($t_colE,$hauteur_ligne, '','',0,'R',1,'');
		$this->Cell($t_colC,$hauteur_ligne,'','',0,'R',1,'');
		$this->Cell($t_colD,$hauteur_ligne, '','',0,'L',1,'');
		$this->Ln();

		// le stade de la compÃ©tition
		$this->Cell($t_colH,$hauteur_ligne, '','',0,'R',1,'');
		$this->SetFont('Arial','B',10);
		$this->Cell($t_colF,$hauteur_ligne, $match->getStageName(),'',0,'C',1,'');

		// L'heure de dÃ©but
		$this->SetFont('Arial','',8);
		$this->Cell($t_colE,$hauteur_ligne, '','',0,'R',1,'');
		$this->Cell($t_colC,$hauteur_ligne, $PDF_START,'',0,'R',1,'');
		$this->Ln();

		// Le tour
		$this->Cell($t_colH,$hauteur_ligne, '','',0,'R',1,'');
		$this->SetFont('Arial','B',10);
		$this->Cell($t_colF,$hauteur_ligne,$match->getStepName(),'',0,'C',1,'');

		// L'heure de fin
		$this->Cell($t_colE,$hauteur_ligne, '','',0,'R',1,'');
		$this->SetFont('Arial','',8);
		$this->Cell($t_colC,$hauteur_ligne,$PDF_END_M,'',0,'R',1,'');
		$this->Ln();

		$joueur1 = $match->getFirstTopName();
		$joueur2 = $match->getSecondTopName();
		$joueur3 = $match->getFirstBottomName();
		$joueur4 = $match->getSecondBottomName();

		// Les noms des joueurs
		$this->Cell($t_colY,$hauteur_ligne, '','',0,'R',1,'');
		$this->SetFont('Arial','B',10);
		$this->Cell($t_colF,$hauteur_ligne,$joueur1,'TLR',0,'C',1,'');
		$this->SetFont('Arial','B',8);
		$this->Cell($t_colH,$hauteur_ligne,'','LR',0,'L',1,'');
		$this->SetFont('Arial','B',10);
		$this->Cell($t_colF,$hauteur_ligne,''.$joueur3,'TLR',0,'C',1,'');
		$this->SetFont('Arial','B',8);
		$this->Cell(0,$hauteur_ligne,'','L',1,'L',1,'');

		$this->Cell($t_colY,$hauteur_ligne, '','',0,'R',1,'');
		$this->SetFont('Arial','B',10);
		$this->Cell($t_colF,$hauteur_ligne,$joueur2,'LR',0,'C',1,'');
		$this->SetFont('Arial','B',8);
		$this->Cell($t_colH,$hauteur_ligne,'','LR',0,'L',1,'');
		$this->SetFont('Arial','B',10);
		$this->Cell($t_colF,$hauteur_ligne,$joueur4,'LR',0,'C',1,'');
		$this->SetFont('Arial','B',8);
		$this->Cell(0,$hauteur_ligne,'','L',1,'L',1,'');

		$this->Cell($t_colY,$hauteur_ligne, '','',0,'R',1,'');
		$this->Cell($t_colF,$hauteur_ligne,'','BLR',0,'C',1,'');
		$this->Cell($t_colH,$hauteur_ligne,'','LR',0,'L',1,'');
		$this->Cell($t_colF,$hauteur_ligne,'','BLR',0,'C',1,'');
		$this->Cell(0,$hauteur_ligne,'','L',1,'L',1,'');
		$this->Ln();

		$this->SetFillColor(255);
		$this->Ln(2);

		$span = ($t_colF-$t_colZ)/2;
		$hauteur = 17;

		for($j=1;$j<4;$j++)
		{
			$this->Cell($t_colY, $hauteur, '',      '',  0, 'L', 1, '');
			$this->Cell($span,   $hauteur, "Set $j",'',  0, 'C', 1, '');
			$this->Cell($t_colZ, $hauteur, '',      1,   0, 'L', 1,'');
			$this->Cell($span,   $hauteur, '',      'L',  0, 'R', 1, '');
			$this->Cell($t_colH, $hauteur, '',      '', 0, 'L', 1,'');
			$this->Cell($span,   $hauteur, '',      '' , 0, 'R', 1, '');
			$this->Cell($t_colZ, $hauteur, '',      1  , 0, 'L', 1,'');
			$this->Ln();
			$this->Ln(1);
		}

		$this->Ln(4);
		$posy = $this->GetY();
		$maxWidth = ($this->orientation=='L') ? 297:210;
		$maxWidth -= 20;

		// Ligne de separation
		$this->SetLineWidth(1);
		$this->SetDrawColor(56,168,92);
		$this->Line(10, $posy, $maxWidth+10, $posy);
		$this->SetLineWidth(0.2);
		$this->SetDrawColor(0);
		$this->Ln(10);

	}
	//}}}

	// {{{ activite()
	/**
	 * Display the activity forms of umpire
	 *
	 * @return void
	 */
	function activite($acts, $role)
	{
		$this->orientation = 'P';
		foreach($acts as $act)
		{
			//print_r($act);
			$this->_activite($act, $role);
		}
		return;
	}
	//}}}

	// {{{ _activite()
	/**
	 * Display the activity forms of umpire
	 *
	 * @return void
	 */
	function _activite($matches, $role)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require $file;

		// Informations du tournoi : nom, date, groupes, lieu
		$this->AddPage('P');
		$this->SetY(45);

		$match = reset($matches);
		$nbMatch = 0;
		for($i=WBS_MS; $i<=WBS_MX; $i++)
		$nbType[$i] = 0;
		$this->SetFont('Arial','B',26);
		$this->Cell(10, 15, $match['regi_longName'], 0, 0, 'L', 0);
		$this->Cell(0, 8, $role, 0, 0, 'R', 0);
		$this->Ln(1);


		// Compter les matchs par discipline
		foreach($matches as $match)
		{
			$nbType[$match['mtch_discipline']]++;
			$nbMatch++;
		}

		$top = 60;
		$left = 48;
		$ut = new utils();
		$this->SetFillColor(192);
		$this->SetTextColor(0);
		$this->SetFont('Arial','',12);
		foreach($nbType as $type=>$nb)
		{
			$this->setXY($left, $top);
			$this->Cell(15, 8, $ut->getSmaLabel($type), 1, 0, 'C', 1);
			$this->setXY($left, $top+8);
			$this->Cell(15, 8, $nb, 1, 0, 'C', 0);
			$left += 15;
		}
		$this->setXY($left, $top);
		$this->Cell(20, 8, $PDF_TOTAL, 1, 0, 'C', 1);
		$this->setXY($left, $top+8);
		$this->Cell(20, 8, $nbMatch, 1, 0, 'C', 0);

		// Afficher le detail des matchs
		$top = 80;
		$hauteur = 8;
		$discipline = -1;
		$utr = new utround();
		$utd = new utdate();

		foreach($matches as $match)
		{
			if ($discipline != $match['mtch_discipline'])
			{
				$top += $hauteur;
				if ($top >240)
				{
					$top=70;
					$this->AddPage('P');
				}
				$this->setY($top);

				$discipline = $match['mtch_discipline'];
				$this->SetFont('Arial','',12);
				$buf = $ut->getLabel($discipline);
				$width = $this->GetStringWidth($buf)+2;
				$this->SetFillColor(0);
				$this->SetTextColor(255);
				$this->Cell($width, $hauteur, $buf, 0, 0, 'L', 1);

				$top += $hauteur;
				$this->SetY($top);

				$this->SetFillColor(192);
				$this->SetTextColor(0);
				$this->Cell(15, $hauteur, $PDF_MATCH, 1, 0, 'L', 1);
				$this->Cell(30, $hauteur, $PDF_ROUND, 1, 0, 'L', 1);
				$this->Cell(35, $hauteur, $PDF_STEP, 1, 0, 'L', 1);
				$this->Cell(40, $hauteur, $PDF_BEGIN, 1, 0, 'L', 1);
				$this->Cell(40, $hauteur, $PDF_END, 1, 0, 'L', 1);
				$this->Cell(20, $hauteur, $PDF_LENGTH, 1, 0, 'L', 1);
				$top += $hauteur;
				$this->SetY($top);
			}
			// Une ligne par match
			$this->SetFont('Arial','',10);
			$this->Cell(15, $hauteur, $match['mtch_num'], 1, 0, 'L', 0);
			$this->Cell(30, $hauteur, $match['rund_name'], 1, 0, 'L', 0);
			$step = $utr->getTieStep($match['tie_posRound']);
			$buf = $ut->getLabel($step);
			$this->Cell(35, $hauteur, $buf, 1, 0, 'L', 0);
			$utd->setIsoDateTime($match['mtch_begin']);
			$this->Cell(40, $hauteur, $utd->getDatetime(), 1, 0, 'L', 0);
			$length = $utd->elaps($match['mtch_end']).' mn';
			$utd->setIsoDateTime($match['mtch_end']);
			$this->Cell(40, $hauteur, $utd->getDatetime(), 1, 0, 'L', 0);
			$this->Cell(20, $hauteur, $length, 1, 0, 'L', 0);
			$this->Ln(1);

			// ligne suivante
			$top += $hauteur;
			if ($top >260)
			{
				$top=70;
				$this->AddPage('P');
			}
			$this->setY($top);

		}
		$this->Ln(3);
	}
	//}}}


}

?>
