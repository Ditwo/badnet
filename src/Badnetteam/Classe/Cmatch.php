<?php
require_once 'Object/Omatch.inc';
require_once 'Object/Opair.inc';
require_once 'Object/Locale/Fr/Oplayer.inc';
/**
 * Classe match
 */
class Cmatch
{
	/**
	 * Retourne la liste des macth en cours
	 *
	 */
	static public function getLiveIds()
	{
		$q = new Bn_query('match', '_team');
		$q->setFields('mtch_id');
		$q->setWhere('mtch_status=' . OMATCH_STATUS_LIVE);
		$ids = $q->getCol();
		return $ids;
	}

	/**
	 * Retourne la liste des joueurs du macth
	 *
	 */
	public function getPlayers()
	{
		if ($this->getVal('playh1id', -1) > 0) $playerIds[] = $this->getVal('playh1id', -1);
		if ($this->getVal('playh2id', -1) > 0) $playerIds[] = $this->getVal('playh2id', -1);
		if ($this->getVal('playv1id', -1) > 0) $playerIds[] = $this->getVal('playv1id', -1);
		if ($this->getVal('playv2id', -1) > 0) $playerIds[] = $this->getVal('playv2id', -1);
		return $playerIds;
	}
	/**
	* Pdf de la feuille de score du match
	*
	*/
	public function pdfScoresheetLite($aPdf)
	{
		$locale = Bn::GetLocale();
		require_once 'Badnetteam/Locale/' .$locale . '/Tevent.inc';

		// Donnees du match
		$cEvent = new Cevent();

		$pdf = $aPdf;

		// Nom du tournoi
		$pdf->SetFont('helvetica','B',26);
		$pdf->SetFillColor(255);
		$str = $cEvent->getVal('name');
		$pdf->Cell(0, 0, $str, '', 0, 'C', 1, '');
		$pdf->Ln();

		// Informations du tournoi
		$pdf->SetFont('helvetica','B',8);
		$joueur1 = $this->getVal('famh1') . ' ' . $this->getVal('firsth1');
		$joueur2 = $this->getVal('famh2') . ' ' . $this->getVal('firsth2');
		$joueur3 = $this->getVal('famv1') . ' ' . $this->getVal('firstv1');
		$joueur4 = $this->getVal('famv2') . ' ' . $this->getVal('firstv2');
		$joueurs = array($joueur1, $joueur2, $joueur3, $joueur4);
		$joueursh = array($this->getVal('famh1'), $this->getVal('famh2'),
		$this->getVal('famv1'), $this->getVal('famv2'));
		$joueur1 .= ' - ' . $this->getVal('labcatageh1') . ' - ' . $this->getVal('levelh1');
		if (trim($joueur2) != '')
		$joueur2 .= ' - ' . $this->getVal('labcatageh2') . ' - ' . $this->getVal('levelh2');
		$joueur3 .= ' - ' . $this->getVal('labcatagev1') . ' - ' . $this->getVal('levelv1');
		if (trim($joueur4) != '')
		$joueur4 .= ' - ' . $this->getVal('labcatagev2') . ' - ' . $this->getVal('levelv2');

		$mcourt = $this->getVal('court');
		$mnum   = $this->getVal('num');

		$pdf->SetFont('helvetica','B',8);

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

		// Le numero de match
		$pdf->Cell($t_colH, $hauteur_ligne, '', '', 0, 'R', 1, '');
		$pdf->SetFont('helvetica','B',10);
		$pdf->Cell($t_colF, $hauteur_ligne, '', '', 0, 'C', 1, '');

		// Le numÃ©ro de terrain
		$pdf->SetFont('helvetica','',8);
		$pdf->Cell($t_colE, $hauteur_ligne, '', '', 0, 'R', 1, '');
		$pdf->Cell($t_colC, $hauteur_ligne, '', '', 0, 'R', 1, '');
		$pdf->Cell($t_colD, $hauteur_ligne, '', '', 0, 'L', 1, '');
		$pdf->Ln();

		// Le tableau
		$pdf->Cell($t_colH, $hauteur_ligne, '', '', 0, 'R', 1, '');
		$pdf->SetFont('helvetica','B',10);
		$str = $this->getVal('division') . ' ' . $this->getVal('group');
		$pdf->Cell($t_colF, $hauteur_ligne, $str, '', 0, 'C', 1,'');

		// Vide
		$pdf->SetFont('helvetica','',8);
		$pdf->Cell($t_colE, $hauteur_ligne, '', '', 0, 'R', 1, '');
		$pdf->Cell($t_colC, $hauteur_ligne, 'Terrain :', '', 0, 'R', 1, '');
		if ($mcourt > 0) $pdf->Cell($t_colD, $hauteur_ligne, $mcourt, '', 0, 'L', 1, '');
		else $pdf->Cell($t_colD, $hauteur_ligne, '', '', 0, 'L', 1, '');
		$pdf->Ln();

		// le stade de la competition
		$pdf->Cell($t_colH,$hauteur_ligne, '', '', 0, 'R', 1, '');
		$pdf->SetFont('helvetica', 'B', 10);
		$str = $this->getVal('labdiscir') . ' Match n°' . $this->getVal('rank');
		$pdf->Cell($t_colF, $hauteur_ligne, $str, '', 0, 'C', 1, '');

		// L'heure de dÃ©but
		$pdf->SetFont('helvetica', '', 8);
		$pdf->Cell($t_colE, $hauteur_ligne, '', '', 0, 'R', 1, '');
		$pdf->Cell($t_colC, $hauteur_ligne, 'Début :', '', 0, 'R', 1, '');
		$pdf->Ln();

		// Le tour
		$pdf->Cell($t_colH,$hauteur_ligne, '', '', 0, 'R', 1, '');
		$pdf->SetFont('helvetica', 'B', 10);
		$pdf->Cell($t_colF, $hauteur_ligne, '', '', 0, 'C', 1, '');

		// L'heure de fin
		$pdf->Cell($t_colE,$hauteur_ligne, '', '', 0, 'R', 1, '');
		$pdf->SetFont('helvetica', '', 8);
		$pdf->Cell($t_colC, $hauteur_ligne, 'Fin :', '', 0, 'R', 1, '');
		$pdf->Ln();

		// Les noms des joueurs
		$pdf->Cell($t_colY,$hauteur_ligne, '','',0,'R',1,'');
		$pdf->SetFont('helvetica','B',10);
		$pdf->Cell($t_colF,$hauteur_ligne,$joueur1,'TLR',0,'C',1,'');
		$pdf->SetFont('helvetica','B',8);
		$pdf->Cell($t_colH,$hauteur_ligne,'','LR',0,'L',1,'');
		$pdf->SetFont('helvetica','B',10);
		$pdf->Cell($t_colF,$hauteur_ligne,''.$joueur3,'TLR',0,'C',1,'');
		$pdf->SetFont('helvetica','B',8);
		$pdf->Cell(0,$hauteur_ligne,'','L',1,'L',1,'');

		$pdf->Cell($t_colY, $hauteur_ligne, '', '', 0, 'R', 1, '');
		$pdf->SetFont('helvetica', 'B', 10);
		$pdf->Cell($t_colF, $hauteur_ligne, $joueur2, 'LR', 0, 'C', 1, '');
		$pdf->SetFont('helvetica', 'B', 8);
		$pdf->Cell($t_colH, $hauteur_ligne, '', 'LR', 0, 'L', 1, '');
		$pdf->SetFont('helvetica', 'B', 10);
		$pdf->Cell($t_colF, $hauteur_ligne, $joueur4, 'LR', 0, 'C', 1, '');
		$pdf->SetFont('helvetica', 'B', 8);
		$pdf->Cell(0,$hauteur_ligne, '', 'L', 1, 'L', 1, '');

		$pdf->Cell($t_colY,$hauteur_ligne, '', '', 0, 'R', 1, '');
		$pdf->Cell($t_colF,$hauteur_ligne, $this->getVal('teamh'), 'BLR', 0, 'C', 1, '');
		$pdf->Cell($t_colH,$hauteur_ligne, '', 'LR', 0, 'L', 1, '');
		$pdf->Cell($t_colF,$hauteur_ligne, $this->getVal('teamv'), 'BLR', 0, 'C', 1, '');
		$pdf->Cell(0,$hauteur_ligne, '', 'L', 1, 'L', 1, '');
		$pdf->Ln();

		$pdf->SetFillColor(255);
		$pdf->Ln(2);

		$span = ($t_colF-$t_colZ)/2;
		$hauteur = 17;

		for($j=1;$j<4;$j++)
		{
		$pdf->Cell($t_colY, $hauteur, '',      '',  0, 'L', 1, '');
		$pdf->Cell($span,   $hauteur, "Set $j",'',  0, 'C', 1, '');
		$pdf->Cell($t_colZ, $hauteur, '',      1,   0, 'L', 1,'');
		$pdf->Cell($span,   $hauteur, '',      'L',  0, 'R', 1, '');
		$pdf->Cell($t_colH, $hauteur, '',      '', 0, 'L', 1,'');
		$pdf->Cell($span,   $hauteur, '',      '' , 0, 'R', 1, '');
		$pdf->Cell($t_colZ, $hauteur, '',      1  , 0, 'L', 1,'');
		$pdf->Ln();
		$pdf->Ln(1);
		}

		$pdf->Ln(10);
		$posy = $pdf->GetY();
		$maxWidth = ($pdf->orientation=='L') ? 297:210;
		$maxWidth -= 20;

		// Ligne de separation
		$pdf->Ln(20);
		$pdf->SetLineWidth(1);
		$pdf->SetDrawColor(56,168,92);
		$pdf->Line(10, $posy, $maxWidth+10, $posy);
		$pdf->SetLineWidth(0.2);
		$pdf->SetDrawColor(0);
		$pdf->Ln(10);
		return false;
	}

	/**
	 * Pdf de la feuille de score du match
	 *
	 */
	public function pdfScoresheet($aPdf)
	{
		$locale = Bn::GetLocale();
		require_once 'Badnetteam/Locale/' .$locale . '/Tevent.inc';

		// Donnees du match
		$cEvent = new Cevent();

		$pdf = $aPdf;
		$pdf->AddPage('L');

		$pdf->SetFont('helvetica','B',26);
		$pdf->SetFillColor(255);
		$str = $cEvent->getVal('name');
		$pdf->Cell(0, 0, $str, '', 0, 'C', 1, '');

		$pdf->setY(25);
		$pdf->SetFont('helvetica','B',8);
		// Informations du tournoi
		$joueur1 = $this->getVal('famh1') . ' ' . $this->getVal('firsth1');
		$joueur2 = $this->getVal('famh2') . ' ' . $this->getVal('firsth2');
		$joueur3 = $this->getVal('famv1') . ' ' . $this->getVal('firstv1');
		$joueur4 = $this->getVal('famv2') . ' ' . $this->getVal('firstv2');
		$joueurs = array($joueur1, $joueur2, $joueur3, $joueur4);
		$joueursh = array($this->getVal('famh1'), $this->getVal('famh2'),
		$this->getVal('famv1'), $this->getVal('famv2'));
		$joueur1 .= ' - ' . $this->getVal('labcatageh1') . ' - ' . $this->getVal('levelh1');
		if (trim($joueur2) != '')
		$joueur2 .= ' - ' . $this->getVal('labcatageh2') . ' - ' . $this->getVal('levelh2');
		$joueur3 .= ' - ' . $this->getVal('labcatagev1') . ' - ' . $this->getVal('levelv1');
		if (trim($joueur4) != '')
		$joueur4 .= ' - ' . $this->getVal('labcatagev2') . ' - ' . $this->getVal('levelv2');

		$mcourt = $this->getVal('court');
		$mnum   = $this->getVal('num');
		$schedule = Bn::date($this->getVal('schedule'), 'd-m-Y H:i');
		$hauteur_ligne = 5;
		$t_colA = 15;
		$t_colB = 25;
		$t_colC = 5;
		$t_colD = 70;
		$t_colE = 10;
		$t_colF = 5;
		$t_colG = 5;
		$t_colH = 12;
		$pdf->SetFont('helvetica','B',12);
		$pdf->SetFillColor(255);
		$str = $this->getVal('labdiscir') . ' Match n°' . $this->getVal('rank');
		$pdf->Cell($t_colA+$t_colB, $hauteur_ligne, $str, 'LT', 0, 'C', 1, '');
		$pdf->Cell(180, $hauteur_ligne, '', 'T', 0, 'C', 1, '');
		$pdf->SetFont('helvetica','B',96);
		$pdf->SetTextColor(210);
		$pdf->SetTextColor(0);
		$pdf->SetFont('helvetica','B',8);
		$pdf->Cell($t_colH, $hauteur_ligne, LOC_LABEL_UMPIRE, 'T', 0, 'R', 1, '');
		$pdf->Cell(0,$hauteur_ligne, '','TR',1,'L',1,'');

		$pdf->Cell($t_colA+$t_colB, $hauteur_ligne, $this->getVal('division'), 'L', 0, 'C', 1, '');
		$pdf->Cell($t_colC, $hauteur_ligne, '', 'TLR', 0, 'L', 1, '');
		$pdf->SetFont('helvetica','B',10);
		$pdf->Cell($t_colD,$hauteur_ligne,$joueur1,'TLR',0,'C',1,'');
		$pdf->SetFont('helvetica','B',8);
		$pdf->Cell($t_colE,$hauteur_ligne, '', 'TLBR', 0, 'L', 1, '');
		$pdf->Cell($t_colF,$hauteur_ligne,'','LR',0,'L',1,'');
		$pdf->SetFillColor(220);
		$pdf->Cell($t_colE,$hauteur_ligne, '', 'TLBR', 0, 'L', 1, '');
		$pdf->SetFont('helvetica','B',10);
		$pdf->Cell($t_colD,$hauteur_ligne,''.$joueur3,'TLR',0,'C',1,'');
		$pdf->SetFont('helvetica','B',8);
		$pdf->Cell($t_colG,$hauteur_ligne,'','TLR',0,'L',1,'');
		$pdf->SetFillColor(255);
		$pdf->Cell($t_colG,$hauteur_ligne,'','L',0,'L',1,'');
		$width = $pdf->GetStringWidth(LOC_LABEL_SERVICE)+1;
		$pdf->Cell($t_colH, $hauteur_ligne, LOC_LABEL_SERVICE,'',0,'R',1,'');
		$pdf->Cell(0,$hauteur_ligne, '', 'R', 1, 'L', 1, '');
		$pdf->SetFillColor(255);

		$pdf->Cell($t_colA+$t_colB, $hauteur_ligne, $this->getVal('group'), 'L', 0, 'C', 1, '');
		$pdf->Cell($t_colC, $hauteur_ligne, '', 'TR', 0, 'L', 1, '');
		$pdf->SetFont('helvetica','B',10);
		$pdf->Cell($t_colD,$hauteur_ligne,$joueur2,'LR',0,'C',1,'');
		$pdf->SetFont('helvetica','B',8);
		$pdf->Cell($t_colE,$hauteur_ligne, '', 'TLRB', 0, 'L', 1, '');
		$pdf->Cell($t_colF,$hauteur_ligne,'','LR',0,'L',1,'');
		$pdf->SetFillColor(220);
		$pdf->Cell($t_colE,$hauteur_ligne, '', 'TLRB', 0, 'L', 1, '');
		$pdf->SetFont('helvetica','B',10);
		$pdf->Cell($t_colD,$hauteur_ligne,$joueur4,'LR',0,'C',1,'');
		$pdf->SetFont('helvetica','B',8);
		$pdf->SetFillColor(255);
		$pdf->Cell($t_colG,$hauteur_ligne,'','LT',0,'L',1,'');
		$pdf->Cell($t_colG,$hauteur_ligne,'','',0,'L',1,'');
		$pdf->Cell($t_colH, $hauteur_ligne, LOC_LABEL_SC_START, '', 0, 'R', 1, '');
		$str = substr($this->getVal('begin'), 11, 5);
		if ($str=='00:00') $str = '';
		$pdf->Cell(0,$hauteur_ligne, $str, 'R', 1, 'L', 1, '');

		$pdf->Cell($t_colA+$t_colB,$hauteur_ligne, '', 'L', 0, 'C', 1, '');
		$pdf->Cell($t_colC,$hauteur_ligne,'','R',0,'L',1,'');
		$pdf->Cell($t_colD, $hauteur_ligne, $this->getVal('teamh'), 'LRB', 0, 'C', 1, '');
		$pdf->Cell($t_colE,$hauteur_ligne, '', 'TLRB', 0, 'L', 1, '');
		$pdf->Cell($t_colF,$hauteur_ligne,'','LR',0,'L',1,'');
		$pdf->SetFillColor(220);
		$pdf->Cell($t_colE,$hauteur_ligne, '', 'TLRB', 0, 'L', 1, '');
		$pdf->Cell($t_colD, $hauteur_ligne, $this->getVal('teamv'), 'LB', 0, 'C', 1, '');
		$pdf->SetFillColor(255);
		$pdf->Cell($t_colG,$hauteur_ligne,'','L',0,'L',1,'');
		$pdf->Cell($t_colG,$hauteur_ligne,'','',0,'L',1,'');
		$pdf->Cell($t_colH, $hauteur_ligne, LOC_LABEL_SC_END, '', 0, 'R', 1, '');
		$str = substr($this->getVal('end'), 11, 5);
		if ($str=='00:00') $str = '';
		$pdf->Cell(0,$hauteur_ligne, $str, 'R', 1, 'L', 1, '');

		$pdf->Cell($t_colA+$t_colB, $hauteur_ligne, $schedule, 'LB', 0, 'C', 1, '');
		$pdf->Cell($t_colC,$hauteur_ligne,'','B',0,'L',1,'');
		$pdf->Cell($t_colD,$hauteur_ligne,'','TB',0,'L',1,'');
		$pdf->Cell($t_colE,$hauteur_ligne,'','TB',0,'L',1,'');
		$pdf->Cell($t_colF,$hauteur_ligne,'','B',0,'L',1,'');
		$pdf->Cell($t_colE,$hauteur_ligne,'','TB',0,'L',1,'');
		$pdf->Cell($t_colD,$hauteur_ligne,'','TB',0,'L',1,'');
		$pdf->Cell($t_colC,$hauteur_ligne,'','B',0,'L',1,'');
		$pdf->Cell($t_colC,$hauteur_ligne,'','B',0,'L',1,'');
		$pdf->Cell($t_colH, $hauteur_ligne, LOC_LABEL_LENGTH, 'B', 0, 'R', 1, '');
		$pdf->Cell(0, $hauteur_ligne, '', 'BR', 1, 'L', 1, '');
		$pdf->Ln(1);

		$bord = 'TBRL';
		if ($this->isDouble())	$hauteur = 4.5;
		else $hauteur = 9;
		$nbCase = 30;
		$width = (277-80)/$nbCase;

		$pdf->SetFont('helvetica','B',8);
		for($j=0;$j<6;$j++)
		{
			for($k=0;$k<2;$k++)
			{
				if ($k) $pdf->SetFillColor(220);
				else  $pdf->SetFillColor(255);
				$indice = $k*2;
				$nom = $joueurs[$indice];
				$nomsh = $joueursh[$indice];
				$pdf->Cell(45,$hauteur,$nom,'TBRL',0,'L',1,'');
				$lw = $pdf->SetLineWidth(0.5);
				$pdf->Cell(5,$hauteur,'','TBRL',0,'L',1,'');
				$pdf->SetLineWidth($lw);
				for($i=0; $i<$nbCase; $i++)
				{
					$ty = $i +30*$j;
					$pdf->Cell($width,$hauteur,'',$bord,0,'L',1,'');
				}
				$pdf->Cell(30,$hauteur,$nomsh,'TBRL',0,'R',1,'');
				$pdf->Ln();
				if ($this->isDouble())
				{
					$indice = ($k*2)+1;
					$nom = $joueurs[$indice];
					$nomsh = $joueursh[$indice];
					$pdf->Cell(45,$hauteur,$nom,'TBRL',0,'L',1,'');
					$lw = $pdf->SetLineWidth(0.5);
					$pdf->Cell(5,$hauteur,'','TBRL',0,'L',1,'');
					$pdf->SetLineWidth($lw);
					for($i=0; $i<$nbCase; $i++)
					{
						$ty = $i +30*$j;
						$pdf->Cell($width, $hauteur,'','TBRL',0,'L',1,'');
					}
					$pdf->Cell(30,$hauteur,$nomsh,'TBRL',0,'R',1,'');
					$pdf->Ln();
				}
			}
			$pdf->Ln(2);
		}

		$pdf->Cell(65, 5, '', 0, 0, 'L');
		$pdf->Cell(100, 5, LOC_LABEL_UMP_SIGNATURE, 0, 0, 'L');
		$pdf->Cell(0, 5, LOC_LABEL_REF_SIGNATURE, 0, 1, 'L');
		if($mcourt != 0 )
		{
			$pdf->SetFont('helvetica','B',25);
			$pdf->setXY(10,10);
			$pdf->Cell(10, 10, $mcourt, '', 0, 'C', 1, '');
			$pdf->setXY(277,10);
			$pdf->Cell(10, 10, $mcourt, '', 0, 'C', 1, '');
		}

		return false;
	}

	public function __construct($aMatchId)
	{
		$matchId = $aMatchId;
		$q = new Bn_query('match', '_team');
		$q->addField('mtch_id',    'id');
		$q->addField('mtch_score', 'score');
		$q->addField('mtch_begin', 'begin');
		$q->addField('mtch_end',   'end');
		$q->addField('mtch_disci', 'disci');
		$q->addField('mtch_discipline', 'discipline');
		$q->addField('mtch_tieid', 'tieid');
		$q->addField('mtch_rank',  'rank');
		$q->addField('mtch_order', 'morder');
		$q->addField('mtch_court', 'court');
		$q->addField('mtch_resulth', 'resulth');
		$q->addField('mtch_resultv', 'resultv');
		$q->addField('mtch_status',  'status');
		$q->addField('mtch_numid',   'numid');

		$q->addField('mtch_playh1id', 'playh1id');
		$q->leftJoin('player h1', 'h1.play_id=mtch_playh1id');
		$q->addField('h1.play_famname', 'famh1');
		$q->addField('h1.play_firstname', 'firsth1');
		$q->addField('h1.play_catage', 'catageh1');
		$q->addField('h1.play_numcatage', 'numcatageh1');
		$q->addField('h1.play_surclasse', 'surclasseh1');
		$q->addField('h1.play_levels', 'levelsh1');
		$q->addField('h1.play_leveld', 'leveldh1');
		$q->addField('h1.play_levelm', 'levelmh1');
		$q->addField('h1.play_court',  'courth1');
		$q->addField('h1.play_rest',   'resth1');
		$q->addField('h1.play_numid',   'playh1numid');

		$q->addField('mtch_playh2id', 'playh2id');
		$q->leftJoin('player h2', 'h2.play_id=mtch_playh2id');
		$q->addField('h2.play_famname', 'famh2');
		$q->addField('h2.play_firstname', 'firsth2');
		$q->addField('h2.play_catage', 'catageh2');
		$q->addField('h2.play_numcatage', 'numcatageh2');
		$q->addField('h2.play_surclasse', 'surclasseh2');
		$q->addField('h2.play_levels', 'levelsh2');
		$q->addField('h2.play_leveld', 'leveldh2');
		$q->addField('h2.play_levelm', 'levelmh2');
		$q->addField('h2.play_court',  'courth2');
		$q->addField('h2.play_rest',   'resth2');
		$q->addField('h2.play_numid',   'playh2numid');

		$q->addField('mtch_playv1id', 'playv1id');
		$q->leftJoin('player v1', 'v1.play_id=mtch_playv1id');
		$q->addField('v1.play_famname', 'famv1');
		$q->addField('v1.play_firstname', 'firstv1');
		$q->addField('v1.play_catage', 'catagev1');
		$q->addField('v1.play_numcatage', 'numcatagev1');
		$q->addField('v1.play_surclasse', 'surclassev1');
		$q->addField('v1.play_levels', 'levelsv1');
		$q->addField('v1.play_leveld', 'leveldv1');
		$q->addField('v1.play_levelm', 'levelmv1');
		$q->addField('v1.play_court',  'courtv1');
		$q->addField('v1.play_rest',   'restv1');
		$q->addField('v1.play_numid',   'playv1numid');

		$q->addField('mtch_playv2id', 'playv2id');
		$q->leftJoin('player v2', 'v2.play_id=mtch_playv2id');
		$q->addField('v2.play_famname', 'famv2');
		$q->addField('v2.play_firstname', 'firstv2');
		$q->addField('v2.play_catage', 'catagev2');
		$q->addField('v2.play_numcatage', 'numcatagev2');
		$q->addField('v2.play_surclasse', 'surclassev2');
		$q->addField('v2.play_levels', 'levelsv2');
		$q->addField('v2.play_leveld', 'leveldv2');
		$q->addField('v2.play_levelm', 'levelmv2');
		$q->addField('v2.play_court',  'courtv2');
		$q->addField('v2.play_rest',   'restv2');
		$q->addField('v2.play_numid',   'playv2numid');

		$q->addWhere('mtch_id=' . $matchId);
		$this->_fields = $q->getRow();

		$catage = $this->getVal('catageh1');
		if (!empty($catage))
		{
			$labcatage = constant('SMA_LABEL_' . $catage);
			$num = $this->getVal('numcatageh1');
			if( !empty($num) ) $labcatage .= $num;
			$this->setVal('labcatageh1', $labcatage);
			$labsurclasse = constant('SMA_LABEL_' . $this->getVal('surclasseh1'));
			$this->setVal('labsurclasseh1', $labsurclasse);
		}

		$catage = $this->getVal('catageh2');
		if (!empty($catage))
		{
			$labcatage = constant('SMA_LABEL_' . $catage);
			$num = $this->getVal('numcatageh2');
			if( !empty($num) ) $labcatage .= $num;
			$this->setVal('labcatageh2', $labcatage);
			$labsurclasse = constant('SMA_LABEL_' . $this->getVal('surclasseh2'));
			$this->setVal('labsurclasseh2', $labsurclasse);
		}

		$catage = $this->getVal('catagev1');
		if (!empty($catage))
		{
			$labcatage = constant('SMA_LABEL_' . $catage);
			$num = $this->getVal('numcatagev1');
			if( !empty($num) ) $labcatage .= $num;
			$this->setVal('labcatagev1', $labcatage);
			$labsurclasse = constant('SMA_LABEL_' . $this->getVal('surclassev1'));
			$this->setVal('labsurclassev1', $labsurclasse);
		}

		$catage = $this->getVal('catagev2');
		if (!empty($catage))
		{
			$labcatage = constant('SMA_LABEL_' . $catage);
			$num = $this->getVal('numcatagev2');
			if( !empty($num) ) $labcatage .= $num;
			$this->setVal('labcatagev2', $labcatage);
			$labsurclasse = constant('SMA_LABEL_' . $this->getVal('surclassev2'));
			$this->setVal('labsurclassev2', $labsurclasse);
		}

		$this->setVal('labdisci', constant('SMA_LABEL_' . $this->getVal('disci')));
		$str = constant('SMA_LABEL_' . $this->getVal('disci'));
		$order = $this->getVal('morder');
		if ($order > 0) $str .= " $order";
		$this->setVal('labdiscir',  $str);

		$discipline = $this->getVal('discipline');
		if ($discipline == OMATCH_DISCIPLINE_SINGLE)
		{
			$this->setVal('levelh1', $this->getVal('levelsh1'));
			$this->setVal('levelh2', $this->getVal('levelsh2'));
			$this->setVal('levelv1', $this->getVal('levelsv1'));
			$this->setVal('levelv2', $this->getVal('levelsv2'));
		}
		elseif ($discipline == OMATCH_DISCIPLINE_DOUBLE)
		{
			$this->setVal('levelh1', $this->getVal('leveldh1'));
			$this->setVal('levelh2', $this->getVal('leveldh2'));
			$this->setVal('levelv1', $this->getVal('leveldv1'));
			$this->setVal('levelv2', $this->getVal('leveldv2'));
		}
		else
		{
			$this->setVal('levelh1', $this->getVal('levelmh1'));
			$this->setVal('levelh2', $this->getVal('levelmh2'));
			$this->setVal('levelv1', $this->getVal('levelmv1'));
			$this->setVal('levelv2', $this->getVal('levelmv2'));
		}

		// Donnees de la rencontre
		$q->setTables('tie');
		$q->setFields('tie_id, tie_teamhid, tie_teamvid, tie_division, tie_group, tie_schedule');
		$q->leftJoin('team th', 'th.team_id=tie_teamhid');
		$q->addField('th.team_name teamh');
		$q->leftJoin('team tv', 'tv.team_id=tie_teamvid');
		$q->addField('tv.team_name teamv');
		$q->addWhere('tie_id=' . $this->getVal('tieid'));
		$team = $q->getRow();
		$this->setVal('teamh', $team['teamh']);
		$this->setVal('teamhid', $team['tie_teamhid']);
		$this->setVal('teamv', $team['teamv']);
		$this->setVal('teamvid', $team['tie_teamvid']);
		$this->setVal('tieid', $team['tie_id']);
		$this->setVal('division', $team['tie_division']);
		$this->setVal('group', $team['tie_group']);
		$this->setVal('schedule', $team['tie_schedule']);

	}

	public function getOscore()
	{
		$scosys = 'Oscore_333'; // . $oEvent->getVal('scoringsystem');
		$oScore = new $scosys();
		$oScore->setScore($this->getVal('score'));
		return $oScore;
	}

	public function isDouble()
	{
		$disci = $this->getVal('disci');
		return ($disci == OMATCH_DISCI_MD || $disci == OMATCH_DISCI_WD || $disci == OMATCH_DISCI_XD);
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

	public function save()
	{
		// Recuperer les listes des champs de la table
		$q = new BN_query('match', '_team');
		$cols = $q->getColumnDef();

		// Filtrer les valeurs a mettre a jour
		foreach( $this->_fields as $key => $value)
		{
			if ( in_array('mtch_'.$key, $cols) )
			{
				$q->addValue('mtch_'.$key, $value);
			}
		}

		$q->setWhere('mtch_id=' . $this->getVal('id', -1));
		$id = $q->updateRow();
		return $id;
	}

}
?>
