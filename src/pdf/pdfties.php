<?php
/*****************************************************************************
 !   Module     : pdf
 !   File       : $File$
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.4 $
 !   Author     : D.BEUVELOT
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2006/07/25 21:06:22 $
 !   Mailto     : didier.beuvelot@silogic.fr
 ******************************************************************************
 !   License   : Licensed under GPL [http://www.gnu.org/copyleft/gpl.html]
 !      This program is free software; you can redistribute it and/or
 !      modify it under the terms of the GNU General Public License
 !      as published by the Free Software Foundation; either version 2
 !      of the License, or (at your option) any later version.
 !
 !      This program is distributed in the hope that it will be useful,
 !      but WITHOUT ANY WARRANTY; without even the implied warranty of
 !      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 !      GNU General Public License for more details.
 !
 !      You should have received a copy of the GNU General Public License
 !      along with this program; if not, write to the Free Software
 !      Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,
 !      USA.
 ******************************************************************************/

require_once "pdfbase.php";
require_once "base.php";
/*
 require_once "utils/utdraw.php";
 require_once "utils/utround.php";
 require_once "utils/utteam.php";
 require_once "utils/utmatch.php";
 */

class  pdfties extends pdfBase
{

	/**
	 * Affichage de la fiche de presence
	 *
	 * @return void
	 */
	function affichage_presence($tieId)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require $file;

		$utfpdf =  new baseFpdf();
		$tieDate = new utDate();
		$eventId = utvars::getEventId();

		$teams = $utfpdf->getTeams($tieId);
		$group = $utfpdf->getGroup($tieId);
		$players[] = $utfpdf->getPlayers($teams[0][0]);
		$players[] = $utfpdf->getPlayers($teams[1][0]);
		//print_r($players1);
		//print_r($players2);
		$tieDate = $utfpdf->getTieDate($tieId);

		$ute = new utevent();
		$event = $ute->getEvent($eventId);

		// Une feuille de présence pour chaque équipe
		for($k=0;$k<2;$k++)
		{
			// Création du document
			$this->orientation = 'P';
			$this->AddPage();

			// Jour heure
			$this->SetFont('Arial','B',10);
			$this->SetXY(10, 48);
			//$this->Cell(23, 8, $PDF_DAY, 'LTRB' , 1, 'C', 0);
			$this->SetXY(122, 48);
			//$this->Cell(23, 8, $PDF_TEAM_TIME, 'LTRB' , 1, 'C', 0);

			// Rencontre
			$this->SetXY(10, 64);
			//$this->Cell(23, 8, $PDF_TIE, 'LTRB' , 1, 'C', 0);

			// Division groupe court
			$this->SetXY(10, 81);
			//$this->Cell(23, 8, $PDF_TEAM_DIVISION, 'LTRB' , 1, 'C', 0);
			$this->SetXY(73, 81);
			//if ($group['draw_type'] == WBS_GROUP)
			//$this->Cell(23, 8, $PDF_GROUP, 'LTRB' , 1, 'C', 0);
			//else
			//$this->Cell(23, 8, $PDF_STAGE, 'LTRB' , 1, 'C', 0);

			$this->SetXY(136, 81);
			//$this->Cell(23, 8, $PDF_TEAM_COURT, 'LTRB' , 1, 'C', 0);
			// Valeur
			$this->SetFont('Arial','B',12);
			// Date
			$this->SetXY(35, 50);
			//$this->Cell(70, 6, $tieDate->getDateWithDayWithoutYear(), 'B',0,'C',0);
			// Heure
			$this->SetFont('Arial','B',24);
			$this->SetXY(148, 50);
			//$this->Cell(52, 6, $tieDate->getTime(),'B',0,'C',0);

			// Equipe A
			$this->SetFont('Arial','B',18);
			$this->SetXY(35, 64);
			$width = min($this->GetStringWidth($teams[0][3])+1, 70);
			//$this->Cell($width, 8,$teams[0][3],'B',0,'C',0);

			$this->SetFont('Arial','',10);
			$this->SetXY(35+$width, 64);
			//$this->Cell(70-$width, 8,$teams[0][1],'B',0,'L',0);

			$this->SetFont('Arial','B',8);
			$this->SetXY(108, 64);
			//$this->Cell(10, 8,$PDF_VERSUS, '',0,'C',0);

			// Equipe B
			$this->SetFont('Arial','B',18);
			$this->SetXY(122, 64);
			$width = min($this->GetStringWidth($teams[1][3])+1, 70);
			//$this->Cell($width, 8,$teams[1][3],'B',0,'C',0);

			$this->SetFont('Arial','',10);
			$this->SetXY(122+$width, 64);
			//$this->Cell(78-$width, 8,$teams[1][1],'B',0,'L',0);
			// Division
			$this->SetFont('Arial','B',12);
			$this->SetXY(35, 83);
			//$this->Cell(32, 6, $group['draw_stamp'],'B',0,'C',0);

			// Group
			$this->SetFont('Arial','B',24);
			$this->SetXY(98, 81);
			if ($group['draw_type'] == WBS_GROUP) $this->Cell(32, 8, $group['rund_stamp'],'B',1,'C',0);
			else
			{
				$ut = new utils();
				$step = utround::getTieStep($group['tie_posRound']);
				$step = $ut->getSmaLabel($step);
				//$this->Cell(32, 8, $step, 'B', 1, 'C', 0);
			}
			// Terrain
			$this->SetXY(162, 81);
			//$this->Cell(38, 8, $group['tie_court'], 'B', 1, 'C', 0);
			// Ligne de separation
			$this->SetLineWidth(1);
			$this->SetDrawColor(56,168,92);
			$this->Line(10, 95, 200, 95);

			// Titre
			$this->SetXY(10, 100);
			$this->SetFont('swz921n','',12);
			$this->SetTextColor(0);
			$this->SetFillColor(255);
			$this->Cell(85,8, $PDF_PRESENCE,'',1,'C',0);
			// Equipe deposante
			$this->SetTextColor(255);
			$this->SetFillColor(0);
			$this->SetLineWidth(0.2);
			$this->SetDrawColor(0);
			$this->SetXY(100, 100);
			$this->SetFont('swz921n','',15);
			$this->Cell(0,8,$teams[$k][1].' ('.$teams[$k][3].')','',1,'C',1);

			$this->SetY(123);
			$this->SetLineWidth(0.5);
			$this->SetDrawColor(0,0,0);
			$this->SetTextColor(0);
				
			// Entête de colonne
			$this->SetFont('Arial','',10);
			$hauteur_ligne = 7;
			$this->Cell(80,  $hauteur_ligne, $PDF_NAME, 'TLBR',0,'C',0);
			$this->Cell(25,  $hauteur_ligne, $PDF_LICENSE, 'TLBR',0,'C',0);
			$this->Cell(15,  $hauteur_ligne, $PDF_NATION, 'TLBR',0,'C',0);
			$this->Cell(10,  $hauteur_ligne, $PDF_MUTE, 'TLBR',0,'C',0);
			$this->Cell(30,  $hauteur_ligne, $PDF_PARAPH, 'TLBR',0,'C',0);
			$this->Cell(30,  $hauteur_ligne, $PDF_COMMENT, 'TLBR',1,'C',0);
				
			// Lignes
			//print_r($players);
			$nb = max(15, count($players[$k]));
			for ($i=0; $i<$nb; $i++)
			{
				if (isset($players[$k][$i]))
				{
					$name = $players[$k][$i]['regi_longName'];
					$name .= '-' . $players[$k][$i]['rkdf_label'] . '-'  . $players[$k][$i]['regi_catage'];
					$this->Cell(80,$hauteur_ligne, $name, 'LBR',0,'C',0);
					$this->Cell(25,$hauteur_ligne, $players[$k][$i]['mber_licence'], 'LBR','0', 'C', '0');
					$this->Cell(15,$hauteur_ligne, '', 'LBR',0,'C',0);
					$this->Cell(10,$hauteur_ligne, '', 'LBR',0,'L',0);
					$this->Cell(30,$hauteur_ligne, '', 'LBR',0,'L',0);
					$this->Cell(30,$hauteur_ligne, '', 'LBR',1,'L',0);
				}
				else
				{
					$this->Cell(80,$hauteur_ligne, '', 'LBR',0,'C',0);
					$this->Cell(25,$hauteur_ligne, '', 'LBR',0,'C',0);
					$this->Cell(15,$hauteur_ligne, '', 'LBR',0,'C',0);
					$this->Cell(10,$hauteur_ligne, '', 'LBR',0,'L',0);
					$this->Cell(30,$hauteur_ligne, '', 'LBR',0,'L',0);
					$this->Cell(30,$hauteur_ligne, '', 'LBR',1,'L',0);
				}
			}
			$this->Ln(3);

			// Bas de page
			$this->sign_cell("dec");
		}

		return;
	}
	// }}}



	// {{{ affichage_declaration()
	/**
	 * Display the two declaration sheets for a tie
	 *
	 * @return void
	 */
	function affichage_declaration($tieId)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require $file;

		$utfpdf =  new baseFpdf();
		$tieDate = new utDate();
		$eventId = utvars::getEventId();

		$teams = $utfpdf->getTeams($tieId);
		$group = $utfpdf->getGroup($tieId);

		$matchs = $utfpdf->getMatches($tieId, $teams[0][0], $teams[1][0]);
		$tieDate = $utfpdf->getTieDate($tieId);

		$ute = new utevent();
		$event = $ute->getEvent($eventId);

		// Une feuille de déclaration pour chaque équipe
		for($k=0;$k<2;$k++){
			// Création du document
			$this->orientation = 'P';
			$this->AddPage();

			// Jour heure
			$this->SetFont('Arial','B',10);
			$this->SetXY(10, 48);
			$this->Cell(23, 8, $PDF_DAY, 'LTRB' , 1, 'C', 0);

			$this->SetXY(122, 48);
			$this->Cell(23, 8, $PDF_TEAM_TIME, 'LTRB' , 1, 'C', 0);

			// Rencontre
			$this->SetXY(10, 64);
			$this->Cell(23, 8, $PDF_TIE, 'LTRB' , 1, 'C', 0);

			// Division groupe court
			$this->SetXY(10, 81);
			$this->Cell(23, 8, $PDF_TEAM_DIVISION, 'LTRB' , 1, 'C', 0);
			$this->SetXY(73, 81);
			if ($group['rund_type'] == WBS_TEAM_GROUP || $group['rund_type'] == WBS_TEAM_BACK)
	  		    $this->Cell(23, 8, $PDF_GROUP, 'LTRB' , 1, 'C', 0);
	  		else
	  		     $this->Cell(23, 8, $PDF_STAGE, 'LTRB' , 1, 'C', 0);

	  $this->SetXY(136, 81);
	  $this->Cell(23, 8, $PDF_TEAM_COURT, 'LTRB' , 1, 'C', 0);

	  // Valeur
	  $this->SetFont('Arial','B',12);
	  // Date
	  $this->SetXY(35, 50);
	  $this->Cell(70, 6, $tieDate->getDateWithDayWithoutYear(),
		    'B',0,'C',0);
	  // Heure
	  $this->SetFont('Arial','B',24);
	  $this->SetXY(148, 50);
	  $this->Cell(52, 6, $tieDate->getTime(),'B',0,'C',0);

	  // Equipe A
	  $this->SetFont('Arial','B',18);
	  $this->SetXY(35, 64);
	  $width = min($this->GetStringWidth($teams[0][3])+1, 70);
	  $this->Cell($width, 8,$teams[0][3],'B',0,'C',0);

	  $this->SetFont('Arial','',10);
	  $this->SetXY(35+$width, 64);
	  $this->Cell(70-$width, 8,$teams[0][1],'B',0,'L',0);

	  $this->SetFont('Arial','B',8);
	  $this->SetXY(108, 64);
	  $this->Cell(10, 8,$PDF_VERSUS, '',0,'C',0);

	  // Equipe B
	  $this->SetFont('Arial','B',18);
	  $this->SetXY(122, 64);
	  $width = min($this->GetStringWidth($teams[1][3])+1, 70);
	  $this->Cell($width, 8,$teams[1][3],'B',0,'C',0);

	  $this->SetFont('Arial','',10);
	  $this->SetXY(122+$width, 64);
	  $this->Cell(78-$width, 8,$teams[1][1],'B',0,'L',0);

	  // Division
	  $this->SetFont('Arial','B',12);
	  $this->SetXY(35, 83);
	  $this->Cell(32, 6, $group['draw_stamp'],'B',0,'C',0);

	  // Group
	  $this->SetFont('Arial','B',24);
	  $this->SetXY(98, 81);
	  if ($group['rund_type'] == WBS_TEAM_GROUP || $group['rund_type'] == WBS_TEAM_BACK)
	  $this->Cell(32, 8, $group['rund_stamp'],'B',1,'C',0);
	  else
	  {
	  	$ut = new utils();
	  	$step = utround::getTieStep($group['tie_posRound']);
	  	$step = $ut->getSmaLabel($step);
	  	$this->Cell(32, 8, $step, 'B', 1, 'C', 0);
	  }
	  // Terrain
	  $this->SetXY(162, 81);
	  $this->Cell(38, 8, $group['tie_court'], 'B', 1, 'C', 0);

	  // Ligne de separation
	  $this->SetLineWidth(1);
	  $this->SetDrawColor(56,168,92);
	  $this->Line(10, 95, 200, 95);

	  // Titre
	  $this->SetXY(10, 100);
	  $this->SetFont('swz921n','',12);
	  $this->SetTextColor(0);
	  $this->SetFillColor(255);
	  $this->Cell(85,8, $PDF_COMPO,'',1,'C',0);

	  // Equipe deposante
	  $this->SetTextColor(255);
	  $this->SetFillColor(0);
	  $this->SetLineWidth(0.2);
	  $this->SetDrawColor(0);
	  $this->SetXY(100, 100);
	  $this->SetFont('swz921n','',15);
	  $this->Cell(0,8,$teams[$k][1].' ('.$teams[$k][3].')','',1,'C',1);

	  // Heure limite
	  $this->SetXY(10, 113);
	  $this->SetTextColor(255,0,0);
	  $this->SetFont('Arial','',10);
	  $this->Cell(0,5,$PDF_DEPOSE,'',1,'L',0);

	  $this->SetTextColor(0);

	  $this->SetY(123);
	  $this->SetLineWidth(0.5);
	  $this->SetDrawColor(0,0,0);
	  // Entête de colonne
	  $this->SetFont('Arial','B',12);
	  $hauteur_ligne = 7;
	  $this->Cell(40,$hauteur_ligne, $PDF_MATCHES_ORDER,'LTRB',0,'C',0);
	  $this->Cell(20,2*$hauteur_ligne, $PDF_DISCIPLINE,'LTR',0,'C',0);
	  $this->Cell(0,$hauteur_ligne, $PDF_TEAM_COMPOSITION,'LTRB',1,'C',0);

	  $this->SetFont('Arial','',10);
	  $this->Cell(13,$hauteur_ligne, $PDF_ORDER_PROPOSED,'LBR',0,'C',0);
	  $this->Cell(13,$hauteur_ligne, $PDF_ORDER_OPPENENT,'LBR',0,'C',0);
	  $this->Cell(14,$hauteur_ligne, $PDF_ORDER_FINAL,'LBR',0,'C',0);
	  $this->Cell(20,$hauteur_ligne,'','LBR',0,'C',0);
	  $this->Cell(65,$hauteur_ligne,$PDF_NOM,'LB',0,'L',0);
	  $this->Cell(65,$hauteur_ligne,$PDF_PRENOM,'BR',1,'L',0);

	  // Calcul du nombre de matches
	  $nb_matches = intval(substr($matchs[0][1], 0,
	  strpos($matchs[0][1],'@@')));
	  $hauteur_ligne = 95/$nb_matches;

	  // Pour chaque match
	  for($cpt=0;$cpt<$nb_matches;$cpt++){
	  	// Si c'est un simple hauteur 8, sinon (double) hauteur 12 !
	  	/**if(strpos($matchs[$cpt][1],'Simple')!=0)
	    $hauteur_ligne = 8;
	    elseif(strpos($matchs[$cpt][1],'Singles')!=0)
	    $hauteur_ligne = 8;
	    else
	    $hauteur_ligne = 12;
	    */
	  	$this->Cell(13,$hauteur_ligne,'' ,1,0,'l',0);
	  	$this->Cell(13,$hauteur_ligne,'' ,1,0,'l',0);
	  	$this->Cell(14,$hauteur_ligne,'' ,1,0,'l',0);
	  	$this->Cell(20,$hauteur_ligne,substr($matchs[$cpt][1],
	  	strpos($matchs[$cpt][1],'@@')+3,
	  	20
	  	)   ,1,0,'C',0);
	  	$this->Cell(0,$hauteur_ligne,''    ,1,1,'l',0);
	  }
	  $this->Ln(3);

	  // Bas de page
	  $this->sign_cell("dec");
		}

		return;
	}
	// }}}


	// {{{ sign_cell()
	/**
	 * Display the two declaration sheets for a tie
	 *
	 * @return void
	 */
	function sign_cell($type='dec'){
		/*$file = "lang/".utvars::getLanguage()."/pdf.inc";
		 require_once $file;
		 */
		if(utvars::getLanguage()=='fra'){
			$PDF_SIGN_CAPTAIN = "Signature du capitaine";
			$PDF_HEURE_DEPOT = "Heure de d�pot : ";
			$PDF_REFEREE_OBSERVATIONS = "Observations du Juge-Arbitre";
		}else{
			$PDF_SIGN_CAPTAIN = "Team Captain Signature";
			$PDF_HEURE_DEPOT = "";
			$PDF_REFEREE_OBSERVATIONS = "Referee observations";
		}

		if($type=='dec'){
			$this->Cell(100,8,$PDF_REFEREE_OBSERVATIONS, 'TLR',0,'l',0);
			$this->Cell(0,8,$PDF_HEURE_DEPOT    ,1,1,'l',0);
			$this->Cell(100,8,''    ,'LR',0,'R',0);
			$this->Cell(0,8,$PDF_SIGN_CAPTAIN, 'TLR',1,'C',0);
			$this->Cell(100,22,''    ,'BLR',0,'R',0);
			$this->Cell(0,22,''    ,'BLR',1,'C',0);
		}
		else{
			$this->Cell(50,8,'',0,0,'l',0);
			$this->Cell(10,8,'',0,0,'l',0);
			$this->Cell(50,8,$PDF_SIGN_CAPTAIN.' A : ' ,'B',0,'l',0);
			$this->Cell(10,8,'',0,0,'l',0);
			$this->Cell(49,8,$PDF_SIGN_CAPTAIN.' B : ' ,'B',0,'l',0);
			$this->Cell(10,8,'',0,0,'l',0);
			$this->Cell(0,8,$PDF_REFEREE_OBSERVATIONS    ,'B',1,'C',0);
		}

	}
	// }}}

	// {{{ affichage_resultDiv($groupId)
	/**
	 * Display the declaration for a tie
	 *
	 * @return void
	 */
	function affichage_resultDiv($groupId)
	{
		$this->start();
		$this->SetFont('Arial','B',12);
		$this->SetFillColor(255,255,255);
		$this->orientation = 'L';
		$utt = new utRound();
		$ties = $utt->getTies($groupId);
		foreach($ties as $tie)
		$this->_affichage_tie($tie['tie_id']);
		$this->end();
	}
	//}}}

	// {{{ affichage_result($tieId)
	/**
	 * Display the declaration for a tie
	 *
	 * @return void
	 */
	function affichage_result($tieId)
	{
		$this->start();
		$this->SetFont('Arial','B',12);
		$this->SetFillColor(255,255,255);
		$this->orientation = 'L';
		$this->_affichage_tie($tieId);
		$this->end();
	}
	//}}}

	// {{{ _affichage_tie($tieId)
	/**
	 * Display the declaration for a tie
	 *
	 * @return void
	 */
	function _affichage_tie($tieId)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		include $file;

		$utfpdf =  new baseFpdf();
		$tieDate = new utDate();
		$ute = new utevent();

		// recup�re l'id de l'event
		$eventId = utvars::getEventId();
		// recup�re les infos de l'event
		$event = $ute->getEvent($eventId);
		// r�cup�re les infos des �quipes
		$teams = $utfpdf->getTeams($tieId);
		if (count($teams) < 2) return;
		//$teams = $dt->getTeams($tieId);
		// r�cup�re les infos du group
		$group = $utfpdf->getGroup($tieId);
		// r�cup�re les matches de la rencontre
		$matchs = $utfpdf->getMatches($tieId, $teams[0][0], $teams[1][0]);

		$tieDate = $utfpdf->getTieDate($tieId);

		// Informations du tournoi : nom, date, groupes, lieu
		$this->AddPage('L');
		$this->SetY(45);

		$this->SetFont('Arial','B',16);
		$this->Cell(80,10,$tieDate->getDateWithDayWithoutYear(),0,0,'L',0);
		$this->SetX(0);
		$this->Cell(0,10,$tieDate->getTime(),0,0,'C',0);
		//      $this->Cell(0,5,$PDF_LIEU.' :  '.$event['evnt_place'],1,1,'C',0);
		$this->Cell(0,10,$group['rund_name'],0,1,'R',0);
		$this->Ln(1);

		// Ent�te de colonne : premi�re ligne
		$this->SetFont('Arial','',10);
		$this->RotatedText(15,58,''.$PDF_ORDRE,-90);
		$this->Cell(10,8,''    ,'TLR',0,'L',0);
		$this->RotatedText(25,57,''.$PDF_TERRAIN,-90);
		$this->Cell(10,8,'' ,'TLR',0,'L',0);
		$this->Cell(15,8,''.$PDF_MATCHES  ,'TLR',0,'C',0);
		$this->Cell(16,8,''.$PDF_TEAMA.' : ' ,'TL',0,'L',0);
		$this->Cell(56, 8, $teams[0][1] ,'TR', 0, 'R', 0);
		$this->Cell(16, 8,''.$PDF_TEAMB.' : ' ,'TL',0,'L',0);
		$this->Cell(56, 8, $teams[1][1] , 'TR', 0, 'R', 0);

		$this->Cell(38,8,''.$PDF_SCORES ,'TLR',0,'L',0);
		$this->Cell(20,8,''.$PDF_VICTOIRES,'TLR',0,'C',0);
		$this->Cell(20,8,''.$PDF_SETS   ,'TLR',0,'C',0);
		$this->Cell(0,8,''.$PDF_POINTS    ,'TLR',1,'C',0);

		// Ent�te de colonne : seconde ligne
		$this->Cell(10,5,'' ,'BLR',0,'L',0);
		$this->Cell(10,5,'' ,'BLR',0,'L',0);
		$this->Cell(15,5,''  ,'BLR',0,'L',0);
		$this->SetFont('Arial','B',18);
		$this->Cell(72, 5, $teams[0][3] ,'BLR',0,'C',0);
		$this->Cell(72, 5, $teams[1][3] ,'BLR',0,'C',0);

		$this->SetFont('Arial','',10);
		$this->Cell(38,5,''   ,'BLR',0,'L',0);
		$this->Cell(10,5,'A'    ,'BLR',0,'C',0);
		$this->Cell(10,5,'B'    ,'BLR',0,'C',0);
		$this->Cell(10,5,'A'    ,'BLR',0,'C',0);
		$this->Cell(10,5,'B'    ,'BLR',0,'C',0);
		$this->Cell(10,5,'A'    ,'BLR',0,'C',0);
		$this->Cell(10,5,'B'    ,'BLR',1,'C',0);

		// Hauteur de chaque ligne
		// Calcul du nombre de matches de la rencontre
		$nb_matches = intval(substr($matchs[0][1],0,strpos($matchs[0][1],'@@')));

		$hauteur_ligne = 45/$nb_matches;

		// Pour chaque matche on affiche
		for($cpt=0;$cpt<$nb_matches;$cpt++){
			// Orde du match
			$this->Cell(10,$hauteur_ligne,$matchs[$cpt][17] ,'TLR',0,'C',0);
			// Terrain
			if ($matchs[$cpt][19] == '' or $matchs[$cpt][19] == 0 )
	  $this->Cell(10,$hauteur_ligne,$matchs[$cpt][18],'TLR',0,'C',0);
	  else
	  $this->Cell(10,$hauteur_ligne,$matchs[$cpt][19] ,'TLR',0,'C',0);
	  // Type du match
	  $this->Cell(15,$hauteur_ligne,substr($matchs[$cpt][1],
	  strpos($matchs[$cpt][1],'@@')+3,
	  20
	  )   ,'TLR',0,'C',0);
	  // Noms des joueurs
	  $joueurs = $utfpdf->getJoueurs($matchs[$cpt]);
	  $this->Cell(72,$hauteur_ligne,''.$joueurs[0]    ,'TLR',0,'L',0);
	  $this->Cell(72,$hauteur_ligne,''.$joueurs[1]    ,'TLR',0,'L',0);
	  if(($matchs[$cpt][5] != 0) || ($matchs[$cpt][6]!=0)){
	  	$this->Cell(38,$hauteur_ligne,''.$matchs[$cpt][4] ,'TLR',0,'L',0); // score
	  	$this->Cell(10,$hauteur_ligne,''.$matchs[$cpt][5] ,'TLR',0,'C',0); // Victoire A
	  	$this->Cell(10,$hauteur_ligne,''.$matchs[$cpt][6] ,'TLR',0,'C',0); // Victoire B
	  	$this->Cell(10,$hauteur_ligne,''.$matchs[$cpt][7] ,'TLR',0,'C',0); // Sets A
	  	$this->Cell(10,$hauteur_ligne,''.$matchs[$cpt][8] ,'TLR',0,'C',0); // Sets B
	  	$this->Cell(10,$hauteur_ligne,''.$matchs[$cpt][9] ,'TLR',0,'C',0); // Points A
	  	$this->Cell(10,$hauteur_ligne,''.$matchs[$cpt][10],'TLR',1,'C',0); // Points B
	  }else{
	  	$this->Cell(38,$hauteur_ligne,'','TLR',0,'L',0); // score
	  	$this->Cell(10,$hauteur_ligne,'','TLR',0,'C',0); // Victoire A
	  	$this->Cell(10,$hauteur_ligne,'','TLR',0,'C',0); // Victoire B
	  	$this->Cell(10,$hauteur_ligne,'','TLR',0,'C',0); // Sets A
	  	$this->Cell(10,$hauteur_ligne,'','TLR',0,'C',0); // Sets B
	  	$this->Cell(10,$hauteur_ligne,'','TLR',0,'C',0); // Points A
	  	$this->Cell(10,$hauteur_ligne,'','TLR',1,'C',0); // Points B
	  }
	  // Seconde Ligne
	  $this->Cell(10,$hauteur_ligne,''    ,'BLR',0,'L',0);
	  $this->Cell(10,$hauteur_ligne,''    ,'BLR',0,'L',0);
	  $this->Cell(15,$hauteur_ligne,''    ,'BLR',0,'L',0);
	  $this->Cell(72,$hauteur_ligne,''.$joueurs[2]   ,'BLR',0,'L',0); // partenaire de double Equipe A
	  $this->Cell(72,$hauteur_ligne,''.$joueurs[3]   ,'BLR',0,'L',0); // partenaire de double Equipe B

	  if ($matchs[$cpt][20]> 0)
	  $this->Cell(38,$hauteur_ligne,$matchs[$cpt][20] . ' mn',
		      'BLR',0,'C',0); // Duree
	  else
	  $this->Cell(38,$hauteur_ligne, '','BLR',0,'C',0); // Duree
	  $this->Cell(10,$hauteur_ligne,''    ,'BLR',0,'L',0);
	  $this->Cell(10,$hauteur_ligne,''    ,'BLR',0,'L',0);
	  $this->Cell(10,$hauteur_ligne,''    ,'BLR',0,'L',0);
	  $this->Cell(10,$hauteur_ligne,''    ,'BLR',0,'L',0);
	  $this->Cell(10,$hauteur_ligne,''    ,'BLR',0,'L',0);
	  $this->Cell(10,$hauteur_ligne,''    ,'BLR',1,'L',0);
		}

		//	  $this->Cell(179,$hauteur_ligne,'' ,'',0,'L',0); // score
		//	  $this->Cell(38,$hauteur_ligne,' BONUS/MALUS' ,1,0,'L',0); // score
		//
		//	  $teams[0][2]?$bmA=$teams[0][2]:$bmA='';
		//	  $teams[1][2]?$bmB=$teams[0][2]:$bmB='';

		//	  $this->Cell(10,$hauteur_ligne,''.$bmA ,1,0,'C',0); // Bonus/Malus A
		//	  $this->Cell(10,$hauteur_ligne,''.$bmB ,1,0,'C',0); // Bonus/Malus B
		//	  $this->Cell(10,$hauteur_ligne,'',1,0,'C',0); // Sets A
		//	  $this->Cell(10,$hauteur_ligne,'',1,0,'C',0); // Sets B
		//	  $this->Cell(10,$hauteur_ligne,'',1,0,'C',0); // Points A
		//	  $this->Cell(10,$hauteur_ligne,'',1,1,'C',0); // Points B


		$this->Ln(1);

		// Qui est le vainqueur ?
		$totalA = ($matchs[$cpt][5] + $teams[0][2] ) ;
		$totalB = ($matchs[$cpt][6] + $teams[1][2] ) ;
		if(($totalA == 0) && ($totalB ==0))
		$winner = "";
		elseif( $totalA > $totalB )
		$winner = $teams[0][1];
		elseif( $totalB > $totalA )
		$winner = $teams[1][1];
		else
		$winner = $PDF_MATCH_NUL ;

		// R�capitulatif  : Vainqueur, Score et Totaux Victoires, Sets, Points
		$this->Cell(119,8,''.$PDF_VAINQUEUR.' : '.strtoupper($winner) ,'TLB',0,'L',0);

		if(($totalA == 0) && ($totalB==0))
		$this->Cell(68,8,''.$PDF_SCORE.' : ','TB',0,'L',0);
		elseif($totalA > $totalB)
		$this->Cell(68,8,''.$PDF_SCORE.' : '.$totalA.' / '.$totalB    ,'TB',0,'L',0);
		elseif($totalB >= $totalA)
		$this->Cell(68,8,''.$PDF_SCORE.' : '.$totalB.' / '.$totalA    ,'TB',0,'L',0);

		$this->Cell(30,8, $PDF_TOTAUX, 'TBR', 0, 'R', 0);
		if(($totalA != 0) || ($matchs[$cpt][6]!=0)){
			$this->Cell(10,8,''.$totalA    ,1,0,'C',0);
			$this->Cell(10,8,''.$totalB    ,1,0,'C',0);
			$this->Cell(10,8,''.$matchs[$cpt][7]    ,1,0,'C',0);
			$this->Cell(10,8,''.$matchs[$cpt][8]    ,1,0,'C',0);
			$this->Cell(10,8,''.$matchs[$cpt][9]    ,1,0,'C',0);
			$this->Cell(10,8,''.$matchs[$cpt][10]    ,1,1,'C',0);
		}
		else
		{
	  $this->Cell(10,8,'',1,0,'C',0);
	  $this->Cell(10,8,'',1,0,'C',0);
	  $this->Cell(10,8,'',1,0,'C',0);
	  $this->Cell(10,8,'',1,0,'C',0);
	  $this->Cell(10,8,'',1,0,'C',0);
	  $this->Cell(10,8,'',1,1,'C',0);
		}
		$this->Ln(3);
		$this->sign_cell("res");

	}

	//}}}

}

?>
