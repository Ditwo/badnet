<?php
/*****************************************************************************
!   Module     : pdf
!   File       : $File$
!   Version    : $Name:  $
!   Revision   : $Revision: 1.8 $
!   Author     : D.BEUVELOT
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
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

require_once "pdf_classement.php";

class  PDF_Match extends PDF_Classement
{
  // {{{ affichage_match_indiv()
  /**
   * Select the informations for a match from an individual event
   *
   * @return void
   */

  function affichage_match_indiv($matchId)
    {
      $utfpdf =  new baseFpdf();
      $match = $utfpdf->getMatchIndiv($matchId);

      //print_r($match);
      $this->_affiche_match($matchId, $match);
      //$this->_affiche_match_lite($matchId, $match);
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
      $match = $utfpdf->getMatch($matchId);

      $this->_affiche_match($matchId, $match);
    }

  // {{{ _affiche_match()
  /**
   * Display the score sheet for a match
   *
   * @return void
   */

  function _affiche_match($matchId, $match)
    {
          $file = "lang/".utvars::getLanguage()."/pdf.inc";
      require $file;

      $utfpdf =  new baseFpdf();
      $tieDate = new utDate();
      $eventId = utvars::getEventId();
      $this->_isHeader = false;
      $this->AliasNbPages();

      //print_r($match);
      /*
Array (
[0] => Array ( [0] => CRUCHAUDEAU C?ne [1] => CRUCHAUDEAU C. [2] => 5 [3] => USRB )
[1] => Array ( [0] => DEMANZE Xavier [1] => DEMANZE X. [2] => 5 [3] => USRB )
[2] => Array ( [0] => FLEPP Romain [1] => FLEPP R. [2] => 5 [3] => BCK )
[3] => Array ( [0] => LAVENANT Lenaig [1] => LAVENANT L. [2] => 5 [3] => BCK )
)
      */
	//print_r($matchs);

	$titre='Badnet';

	// Cr?ion du document
	$this->orientation = 'L';
	$this->AddPage('L');
	$this->SetTitle($titre);
	$this->SetAuthor('BadNet Team');
	$this->SetFont('Arial','B',8);
	// Informations du tournoi
	$this->Tete();

	$nb_joueurs = count($match);
        $auj = new utDate();
	if($nb_joueurs > 0){
  	if($nb_joueurs==4){
  	 $joueur2 = $match[0][0]." (".$match[0][12].")";
  	 //$joueur2 = $match[2][0]." (".$match[2][12].")";
  	 $joueur3 = $match[3][0]." (".$match[3][12].")";
  	 $joueur1 = $match[1][0]." (".$match[1][12].")";
  	 //$joueur4 = $match[3][0]." (".$match[3][12].")";
	 $joueur4 = $match[2][0]." (".$match[2][12].")";
  	 //$joueurs = array($match[1][0],$match[2][0],$match[0][0],$match[3][0]);
  	 //$joueurs = array($match[1][0],$match[3][0],$match[0][0],$match[2][0]);
  	 $joueurs = array($match[1][0],$match[0][0],$match[3][0],$match[2][0]);
	}
	elseif($nb_joueurs==2){
	  $joueur3 = $match[0][0]." (".$match[0][12].")";
	  $joueur4 = "";
	  $joueur1 = $match[1][0]." (".$match[1][12].")";
	  $joueur2 = "";
	  $joueurs = array($match[1][0],"",$match[0][0],"");
	}
	elseif($nb_joueurs==1){
	  $joueur3 = $match[0][0]." (".$match[0][12].")";
	  $joueur4 = "";
	  $joueur1 = "";
	  $joueur2 = "";
	  $joueurs = array("","",$match[0][0],"");
	}
	else{
	  $joueur3 = "";
	  $joueur4 = "";
	  $joueur1 = "";
	  $joueur2 = "";
	  $joueurs = array("","","","");
	}

	$ut = new utils();

	$discipline = $match[0][2];
	//	$terrain    = $match[0]['tie_court'];
	$match_begin  = $match[0][5];
	$match_end    = $match[0][6];
	//	print_r($match[0]);
	$step = $utfpdf->getMatchStep($matchId);
	//print_r($step);
	$div = $ut->getLabel($step[0]);
	$mnum = $step[1];
	$mcourt = $step[2];
	$auj->setIsoDate($match[0][9]);
	}
	else{
	  $joueur1 = "";
	  $joueur2 = "";
	  $joueur3 = "";
	  $joueur4 = "";
	  $joueurs = array("","","","");

	  $discipline = "";
	  $terrain    = "";
	  //  $nb = preg_match_all("*-*-* *:*:*", $match[0][5], $match_begin);
	  //	  $match_begin    = $match[0][5];
	  //    $match_end    = $match[0][6];
	  $match_begin    = "";
	  $match_end    = "";
	  $div = "";
	}

	$hauteur_ligne = 5;
	$t_colA = 15;
	$t_colB = 25;
	$t_colC = 5;
	$t_colD = 70;
	$t_colE = 10;
	$t_colF = 5;
	$t_colG = 5;

	//Cell( w,  h,  txt, border, ln, align, fill, link)
	$this->SetFont('Arial','B',8);
	$this->SetFillColor(192);
        $this->Cell($t_colA,$hauteur_ligne, $PDF_DIVISION,'LT',0,'R',1,'');
        $this->Cell($t_colB,$hauteur_ligne, $div,'T',0,'C',1,'');
        $this->Cell(180,$hauteur_ligne, '','T',0,'C',1,'');
        //$this->Cell(180,$hauteur_ligne, ''.$mnum,'T',0,'C',1,'');
	$this->SetFont('Arial','B',96);
	$this->SetTextColor(210);
	if($mcourt != 0 )
	  $this->RotatedText(130,150,'court '.$mcourt,30);
	$this->SetTextColor(0);
	$this->SetFont('Arial','B',8);
        $this->Cell(20,$hauteur_ligne, $PDF_START,'T',0,'L',1,'');
	//        $this->Cell(0,8,$match_begin,'',1,'L',0,'');
        $this->Cell(0,$hauteur_ligne,'','TR',1,'L',1,'');

        $this->Cell($t_colA,$hauteur_ligne, $PDF_EVENT,'L',0,'R',1,'');
        $this->Cell($t_colB,$hauteur_ligne,$discipline,'',0,'C',1,'');
        $this->Cell($t_colC,$hauteur_ligne,'','TLR',0,'L',1,'');
	$this->SetFont('Arial','B',10);
        $this->Cell($t_colD,$hauteur_ligne,$joueur1,'TLR',0,'C',1,'');
	$this->SetFont('Arial','B',8);
        $this->Cell($t_colE,$hauteur_ligne,'','TLBR',0,'L',1,'');
        $this->Cell($t_colF,$hauteur_ligne,'','LR',0,'L',1,'');
        $this->Cell($t_colE,$hauteur_ligne,'','TLBR',0,'L',1,'');
	$this->SetFont('Arial','B',10);
        $this->Cell($t_colD,$hauteur_ligne,''.$joueur3,'TLR',0,'C',1,'');
	$this->SetFont('Arial','B',8);
        $this->Cell($t_colG,$hauteur_ligne,'','TLR',0,'L',1,'');
        $this->Cell($t_colG,$hauteur_ligne,'','L',0,'L',1,'');
        $this->Cell(20,$hauteur_ligne,$PDF_END_M,'',0,'L',1,'');
//         $this->Cell(0,8,$match_end,'',1,'L',0,'');
       $this->Cell(0,$hauteur_ligne,'','R',1,'L',1,'');

        $this->Cell($t_colA,$hauteur_ligne,$PDF_NUM,'L',0,'R',1,'');
        $this->Cell($t_colB,$hauteur_ligne,$mnum,'',0,'C',1,'');
	// $this->Cell($t_colB,$hauteur_ligne,$terrain,'',0,'C',1,'');
        $this->Cell($t_colC,$hauteur_ligne,'','LR',0,'L',1,'');
	$this->SetFont('Arial','B',10);
        $this->Cell($t_colD,$hauteur_ligne,$joueur2,'LR',0,'C',1,'');
	$this->SetFont('Arial','B',8);
        $this->Cell($t_colE,$hauteur_ligne,'','TLRB',0,'L',1,'');
        $this->Cell($t_colF,$hauteur_ligne,'','LR',0,'L',1,'');
        $this->Cell($t_colE,$hauteur_ligne,'','TLRB',0,'L',1,'');
	$this->SetFont('Arial','B',10);
        $this->Cell($t_colD,$hauteur_ligne,$joueur4,'LR',0,'C',1,'');
	$this->SetFont('Arial','B',8);
        $this->Cell($t_colG,$hauteur_ligne,'','LR',0,'L',1,'');
        $this->Cell($t_colG,$hauteur_ligne,'','L',0,'L',1,'');
        $this->Cell(0,$hauteur_ligne,$PDF_LENGTH_M,'R',1,'L',1,'');

        $this->Cell($t_colA,$hauteur_ligne,$PDF_DATE,'L',0,'R',1,'');
	//	print_r($auj);
        $this->Cell($t_colB,$hauteur_ligne,$auj->getDate('/'),'',0,'C',1,'');
        $this->Cell($t_colC,$hauteur_ligne,'','LRB',0,'L',1,'');
	$tmp = end($match);
	//	if (isset($match[1][8]))
	//$this->Cell($t_colD, $hauteur_ligne,$tmp[8],'LRB',0,'C',1,'');
	//else
	$this->Cell($t_colD, $hauteur_ligne,'','LRB',0,'C',1,'');
        $this->Cell($t_colE,$hauteur_ligne,'','TLRB',0,'L',1,'');
        $this->Cell($t_colF,$hauteur_ligne,'','LR',0,'L',1,'');
        $this->Cell($t_colE,$hauteur_ligne,'','TLRB',0,'L',1,'');
	//if (isset($match[0][8]))
	//$this->Cell($t_colD,$hauteur_ligne,$match[0][8],'LRB',0,'C',1,'');
	//else
	$this->Cell($t_colD,$hauteur_ligne,'','LRB',0,'C',1,'');
        $this->Cell($t_colG,$hauteur_ligne,'','LRB',0,'L',1,'');
        $this->Cell($t_colG,$hauteur_ligne,'','L',0,'L',1,'');
	$officials = $utfpdf->getOfficials($matchId);
	$width = $this->GetStringWidth($PDF_UMPIRE)+1;
        $this->Cell($width, $hauteur_ligne, $PDF_UMPIRE, '', 0, 'L', 1, '');
	$this->SetFont('Arial','B',10);
        $this->Cell(0,$hauteur_ligne,$officials['umpire'],'R',1,'L',1,'');
	$this->SetFont('Arial','B',8);


	//   	$this->Ln(0.25);
        $this->Cell($t_colA, $hauteur_ligne, $PDF_TIME,'LB',0,'R',1,'');
        $this->Cell($t_colB, $hauteur_ligne, $auj->getTime(),'B',0,'C',1,'');
        $this->Cell(85,$hauteur_ligne, '','TB',0,'L',1,'');
        $this->Cell(5,$hauteur_ligne, '','B',0,'L',1,'');
        $this->Cell(85,$hauteur_ligne, '','TB',0,'L',1,'');
        $this->Cell(5,$hauteur_ligne, '','B',0,'L',1,'');
	$width = $this->GetStringWidth($PDF_SERVICE)+1;
        $this->Cell($width, $hauteur_ligne, $PDF_SERVICE,'B',0,'L',1,'');
	$this->SetFont('Arial','B',10);
        $this->Cell(0,$hauteur_ligne,$officials['service'],'BR',1,'L',1,'');
	$this->SetFont('Arial','B',8);
	$this->SetFillColor(255);

   	$this->Ln(2);

if($nb_joueurs==4){
  $hauteur = 5;
  $bord = 'TRL';
}
elseif($nb_joueurs==2){
  $hauteur = 10;
  $bord = 'TBRL';
}
elseif($nb_joueurs==1){
  $hauteur = 10;
  $bord = 'TBRL';
}
else{
  $hauteur = 5;
  $bord = 'TRL';
}

 $nbCase = 32;
 $width = (277-65)/$nbCase;

 for($j=0;$j<6;$j++)
   {
     for($k=0;$k<2;$k++)
       {
	 $indice = $k*2;
	 $this->SetFont('Arial','B',10);
	 $nom = $joueurs[$indice];
	 $this->Cell(60,$hauteur,$nom,'TBRL',0,'L',0,'');
	 $this->Cell(5,$hauteur,'','TBRL',0,'L',0,'');
	 for($i=0; $i<$nbCase; $i++){
	   $this->Cell($width,$hauteur,'',$bord,0,'L',0,'');
	 }
	 $this->Ln();

	 if(($nb_joueurs==4)|| ($nb_joueurs==0))
	   {
	     $indice = ($k*2)+1;
	     $this->SetFont('Arial','B',10);
	     $nom = $joueurs[$indice];
	     $this->Cell(60,$hauteur,$nom,'TBRL',0,'L',0,'');
	     $this->Cell(5,$hauteur,'','TBRL',0,'L',0,'');
	     for($i=0; $i<$nbCase; $i++){
	     $this->Cell($width, $hauteur,'','BRL',0,'L',0,'');
	     }
	     $this->Ln();
	   }
       }
     $this->Ln(2);
   }

 $this->Cell(65, 5, '', 0, 0, 'L');
 $this->Cell(100, 5, $PDF_UMP_SIGNATURE, 0, 0, 'L');
 $this->Cell(0, 5, $PDF_REF_SIGNATURE, 0, 1, 'L');

 //echo " -- end -- ";

 $this->Output();
     exit;
    }


  // {{{ Tete($)
  /**
   * Header des pages PDF
   * en fonction de l'orientation de la page L ou P
   * on affiche Badnet en filigramme
   *
   * @return void
   */

  function Tete()
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

      // Affichage du lieu du tournoi
      /*
	  $this->SetXY(130, 23);
	  $this->SetFont('arial','',20);
	  $this->Cell(0, 8,  $event['evnt_place'],0 ,1 ,'R');
      */
      // Affichage de la date du tournoi
      /*
	  $this->SetXY(130, 33);
	  $this->Cell(0,5, $event['evnt_date'], 0, 1, 'R');
      */

      // Calcul de la position du logo
      /*
	  $logo = utimg::getPathPoster($meta['evmt_topRightLogo']);
	  $width = 0; $height = 0;
	  $top = 20;
	  $left = 10;
	  $size = @getImagesize($logo);

	  if ($size AND (strpos($logo,"gif") === FALSE))
	    {
	      $few = 70 / $size[0];
	      $feh = 21 / $size[1];
	      $fe = min(1, $feh, $few);
	      $width = $size[0] * $fe;
	      $height = $size[1] * $fe;
	      $top += (21-$height)/2;
	    }
      */
      // Affichage du logo de gauche
      /*
	  if ($width && $height)
	    $this->Image($logo, $left, $top, $width, $height);
      */
      // Ligne de separation
      $this->SetLineWidth(1);
      $this->SetDrawColor(56,168,92);
      $this->Line(10, 22, $maxWidth+10, 22);

      $this->SetLineWidth(0.2);
      $this->SetDrawColor(0);

      // Se positionner apres la ligne
      $this->SetY(24);
    }
// }}}


}

?>
