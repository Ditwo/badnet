<?php
/*****************************************************************************
!   Module     : pdf
!   File       : $File$
!   Version    : $Name:  $
!   Revision   : $Revision: 1.9 $
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

require_once "pdf_match.php";
require_once "utils/utround.php";

class  PDF_Result extends PDF_Match
{

  // {{{ start()
  /**
   * Display the group of the division with the schedule
   *
   * @return void
   */
  function start()
    {
      // Cr�ation du document
      $titre='Badnet Division';
      $this->SetTitle($titre);
      $this->SetFillColor(255,255,255);
      $this->SetAuthor('BadNet Team');
      $this->AliasNbPages();
      $this->_started = true;
    }
  // }}}

  // {{{ end()
  /**
   * Display the group of the division with the schedule
   *
   * @return void
   */
  function end()
    {
      // Dossier des fichiers temporaires
      $dir = getcwd()."/../cache";

      // Purge des fichiers pdf trop ancien
      $t = time();
      $h = opendir($dir);
      while($file=readdir($h))
	{
	  if(substr($file,0,3) == 'tmp')
	    {
	      $path = $dir.'/'.$file;
	      if($t-filemtime($dir)>3600)
		@unlink($path);
	    }
	}
      closedir($h);

      // Nom du fichier temporaire
      $file = tempnam($dir, 'tmp');
      rename($file, $file.'.pdf');
      $file .= '.pdf';
      chmod($file, 0777);

      // Generer le pdf dans le fichier
      $this->Output($file);

      // Afficher le pdf
      $file = basename($file);
      $url = dirname($_SERVER['PHP_SELF'])."/../cache/$file";
      //echo "url=$url";
      header("Location: $url");
      exit;
    }
  // }}}



  // {{{ affichage_result_div($groupId)
  /**
   * Display the declaration for a tie
   *
   * @return void
   */
  function affichage_resultDiv($groupId)
    {
      $this->AliasNbPages();

      // Cr�ation du document PDF
      $titre='Badnet';
      $this->SetFont('Arial','B',12);
      $this->SetFillColor(255,255,255);

      $this->orientation = 'L';
      $this->SetTitle($titre);
      $this->SetAuthor('BadNet Team');
      $utt = new utRound();
      $ties = $utt->getTies($groupId);
      foreach($ties as $tie)
	{
	  //print_r($tie);
	  $this->_affichage_tie($tie['tie_id']);
	}
      $this->Output();
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
      $this->AliasNbPages();

      // Cr�ation du document PDF
      $titre='Badnet';
      $this->SetFont('Arial','B',12);
      $this->SetFillColor(255,255,255);

      $this->orientation = 'L';
      $this->SetTitle($titre);
      $this->SetAuthor('BadNet Team');
      $this->_affichage_tie($tieId);
      $this->Output();
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
      //$this->AddPage('L');
      $this->SetY(45);

      $this->SetFont('Arial','B',16);
      $this->Cell(40,10,$tieDate->getDateWithDayWithoutYear(),0,0,'L',0);
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
      $this->Cell(20,8,''.$PDF_MATCHES,'TLR',0,'C',0);
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

      $hauteur_ligne = 55/$nb_matches;

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
      elseif($totalB > $totalA)
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
	//	$this->sign_cell("res");

    }

  //}}}


  // {{{ _tab_simple()
  /**
   * Display the draw for a single event
   *
   * @return void
   */
  function _tab_simple( $tableau, $offset=0, $offsetPosition = 0,$titre = '',$npage = '', $typeDraw = '')
  {
    $ut = new Utils();

    $this->orientation = 'L';
    $this->AddPage('L');


    //print_r($tableau);
    //    echo "<hr>";
    $xpos = 10;
    $ypos = 58;
    $ypos_start = $ypos;
    $nbLignes = count($tableau[0][0]);
    $taille = (210 - 80) / $nbLignes;
    //    print_r($tableau);
    $nbCols = count($tableau);
    $firstCol = 60;
    $margeDroite = 20;

    for ($i=0; $i < $nbCols; $i++){
      $title[$nbCols-$i -1 ] = $ut->getLabel(WBS_WINNER + $i + $offset);
      if(!isset($tableau[$i][0][0][0]))
	$nbCols--;
    }
    //print_r($title);
    $this->SetFillColor(200,200,200);
    $this->SetFont('Arial','B',10);

    for ($i=0; $i < $nbCols; $i++){
      $largeur = $i ? (297-$firstCol-$margeDroite) / ($nbCols-1) :$firstCol;
      $this->Cell($largeur,10,''.$title[$i] ,1,0,'C',1);
    }
    $this->SetFillColor(255,255,255);

    $this->SetXY($xpos,$ypos);

    for($i=0;$i<$nbCols;$i++)
      {
	$largeur = $i ? (297-$firstCol-$margeDroite) / ($nbCols-1) :$firstCol;
	if($i==0){
	  $bords2 = '';
	  $this->SetFont('Arial','',6);
	}
	elseif($i==($nbCols-1)){
	  $bords2 = 'L';
	  $this->SetFont('Arial','B',12);
	}
	else{
	  $bords2 = 'L';
	  $this->SetFont('Arial','',8);
	}
	$ypos -= $taille/2;
	$nbLignes = count($tableau[$i][0]);
	for($j=0;$j<$nbLignes;$j++)
	  {
	    //print_r($tableau[$i][0][$j]);
	    //echo "<hr>";
	    $this->SetXY($xpos,$ypos+$taille*3/4);
	    // pour les tableaux de qualifs
	    if(!isset($tableau[$i][0][$j][0]))
	      $bords2 = '';
	    $this->Cell($largeur, $taille/2, '', $bords2 ,0,'C',0);
	    $this->SetXY($xpos,$ypos+$taille-3);
	    $position = "";
	    if($i==0)
	      {
		$position = $j+1+$offsetPosition;
		$this->Cell(5,3,''.$position ,'B' ,0,'L',0);

		$this->Cell(5,3,''.$tableau[$i][2][$j]  ,'B' ,0,'L',0);
 		$image = $tableau[$i][3][$j][0];
		if (is_file($image))
		    {
		      $size = @getImagesize($image);
		      $few = 6/$size[0];
		      $feh = 3/$size[1];
		      $fe = min(1, $feh, $few);
		      $widthLogo = $size[0] * $fe;
		      $heightLogo = $size[1] * $fe;

		      $xLogo = 22;
		      $yLogo = $ypos+$taille-$heightLogo-0.5;
		      $this->Image($image, $xLogo, $yLogo,
				   $widthLogo, $heightLogo);
		      $ibfn = '';
		    }
		else
		  {
		    $ibfn = $image;
		  }
		$this->Cell(8,3,''.$ibfn,'B' ,0,'L',0);
		//print_r($tableau[$i][0][$j]);
		$this->Cell($largeur-18,3,''.$tableau[$i][0][$j][0],'B' ,0,'L',0);
	      }
	    else
	      {
		//print_r($tableau[$i]);
		if(($tableau[$i][4][$j]!='')||(isset($tableau[$i][0][$j][0]))){
		  $this->Cell(5,3,''.$tableau[$i][4][$j]  ,'B' ,0,'L',0);
		  $this->Cell($largeur-5,3,''.$tableau[$i][0][$j][0]  ,'B' ,0,'L',0);
		}
	      }
	    if((($tableau[$i][1][$j])!='')&&($i!=0)){
	      $this->SetXY($xpos,$ypos+$taille);
	      $this->SetFont('Arial','',6);
	      $this->Cell($largeur-5,3,''.$tableau[$i][1][$j]  ,'' ,0,'C',0);
	      $this->SetFont('Arial','',8);
	    }
	    $ypos += $taille ;
	  }
	//Cell( w,  h,  txt, border, ln, align, fill, link)
	$xpos += $largeur ;
	$ypos = $ypos_start ;
	$taille = $taille * 2;
      }
    $xpos -= $largeur;
    $this->SetFillColor(0,0,0);
    $this->SetTextColor(255);
    $this->setXY($xpos,60);
    $this->SetFont('Arial','UI',16);
    $this->Cell($largeur,10,''.$titre,0,0,'C',1);
    $this->setXY($xpos,70);
    $this->SetFont('Arial','I',14);
    if($npage != '')
      $this->Cell($largeur,10,''.$npage,0,0,'C',1);
    $this->setXY($xpos,175);
    $this->SetFont('Arial','I',14);
    $this->Cell($largeur,10,''.$typeDraw,0,0,'C',1);
    $this->SetTextColor(0);

  }
  //}}}


  // {{{ singleKO()
  /**
   * Display a model for a 64-entries tie for a single draw
   *
   * @return void
   */
  function singleKO($valeurs_depart, $colonnes, $titre = '', $typeDraw='',$ibf=0)
    {
      $file = "lang/".utvars::getLanguage()."/pdf.inc";
      require_once $file;

      $utfpdf =  new baseFpdf();

      $eventId = utvars::getEventId();

      $ute = new utevent();
      $eventId = utvars::getEventId();
      $event = $ute->getEvent($eventId);

      foreach($valeurs_depart as $tour => $valeurs)
	{
	  //echo "$tour <br>";print_r($valeurs);echo "<hr>";
	  $nom = array();
	  $logo = array();
	  if(is_array($valeurs))
	    {
	      //print_r($valeurs);
	      $nom[]  = $valeurs['value'][0]['name'];
	      if(($valeurs['value'][0]['ibfn']!='')&&($ibf))
		$logo[]   = $valeurs['value'][0]['ibfn'];
	      else
		$logo[]   = $valeurs['value'][0]['logo'];
	    }
	  else
	    {
	      $nom[] = "vacant";
	      $logo[] = "";
	    }

	  $joueurs[] = $nom ;
	  $seeds[] = $valeurs['seed'];
	  $flags[] = $logo;
	  $scores[] = "" ;
	  $match_num[] = "";
	}

      $tableau[] = array($joueurs, $scores, $seeds, $flags,$match_num);
      // print_r($tableau);

      // print_r($colonnes);

  foreach($colonnes as $tour => $valeurs)
  {
    $joueurs = array();
    $scores = array();
    $match_num = array();
    $flags = array();
    $match_num = array();
    $seeds = array();

    foreach($valeurs as $cle => $vals)
      {
	$nom = array();
	$logo = array();
	//print_r($vals);
	if (is_array($vals))
	  {
	    //echo "<$cle> <br>";print_r($vals);echo "<br>";
	    if(is_array($vals['value']))
	      {
		$nom[] = $vals['value'][0]['name'];
		//$logo = $value['logo'];
		$logo[] = '';
	      }
	    else
	      {
		$nom[] = '';
		$logo[] = '';
	      }

	    //$nom = $vals['value'][0]['name'];
	    if(isset($vals['score']))
	      $score = $vals['score'];
	    else
	      $score = '';
	  }
	else{
	  if(isset($vals['value']))
	    $nom[] = $vals['value'];
	  //	  else
	  //  $nom[] = '';
	  $score = "";
	  $logo[] = "";
	}
	//echo " $nom ($score) <br> ";
	$joueurs[] = $nom ;
	//print_r($vals);
	if(isset($vals['match_num']))
	  $match_num[] = $vals['match_num'];
	else
	  $match_num[]='';
	$flags[] = $logo;
	$scores[] = $score;
	$seeds[] = '';
      }

    $tableau[] = array($joueurs, $scores, $seeds, $flags,$match_num);
  }

  // Le tableau est pr�t. Il faut l'afficher
  $nbLignes = count($tableau[0][0]);
  $nbCols = count($tableau);
  //print_r($tableau);
  if($nbLignes <= 32){
    $this->_tab_simple($tableau,0,0,$titre,'',$typeDraw);
  }
  // TABLEAU 64 //
  elseif(( $nbLignes > 32) && ($nbLignes <= 64)){
    foreach($tableau as $colonnes => $lignes){
      //print_r($lignes);echo "<hr>";
        $tab[0] = array_slice($lignes[0], 0, count($lignes[0]) / 2);
        $tab[1] = array_slice($lignes[1], 0, count($lignes[0]) / 2);
	if (isset($lignes[2]))
	  {
	    $tab[2] = array_slice($lignes[2], 0, count($lignes[0]) / 2);
	    $tab[3] = array_slice($lignes[3], 0, count($lignes[0]) / 2);
	    $tab[4] = array_slice($lignes[4], 0, count($lignes[0]) / 2);
	  }
        $tableau_1[] = $tab;

        $tab[0] = array_slice($lignes[0], count($lignes[0]) / 2);
        $tab[1] = array_slice($lignes[1], count($lignes[0]) / 2);
	if (isset($lignes[2]))
	  {
	    $tab[2] = array_slice($lignes[2], count($lignes[0]) / 2);
	    $tab[3] = array_slice($lignes[3], count($lignes[0]) / 2);
	    $tab[4] = array_slice($lignes[4], count($lignes[0]) / 2);
	  }
        $tableau_2[] = $tab;

        if($colonnes!=0)
          $tableau_3[] = $lignes;
    }
    $tab[0] = array_pop($tableau_1);
    $tab[1] = array_pop($tableau_2);
    $finale = $tab;
    //echo "<br>TABLEAU 1 <br>";
    //print_r($tableau_1); echo "<hr>";
    $this->_tab_simple($tableau_1,1,0,$titre,'(1/2)',$typeDraw);
    //echo "<br>TABLEAU 2 <br>";
    //print_r($tableau_2); echo "<hr>";
    $this->_tab_simple($tableau_2,1,32,$titre,'(2/2)',$typeDraw);


    $this->_tab_simple($tableau_3,0,0,$titre,'',$typeDraw);
    }
  // TABLEAU 128 //
    else
    {
    foreach($tableau as $colonnes => $lignes){
      //print_r($lignes);echo "<hr>";
        $tab[0] = array_slice($lignes[0], 0, count($lignes[0]) / 4);
        $tab[1] = array_slice($lignes[1], 0, count($lignes[0]) / 4);
        $tab[2] = array_slice($lignes[2], 0, count($lignes[0]) / 4);
        $tab[3] = array_slice($lignes[3], 0, count($lignes[0]) / 4);
        $tab[4] = array_slice($lignes[4], 0, count($lignes[0]) / 4);
        $tableau_1[] = $tab;

        $tab[0] = array_slice($lignes[0], count($lignes[0]) / 4, count($lignes[0]) / 4);
        $tab[1] = array_slice($lignes[1], count($lignes[0]) / 4, count($lignes[0]) / 4);
        $tab[2] = array_slice($lignes[2], count($lignes[0]) / 4, count($lignes[0]) / 4);
        $tab[3] = array_slice($lignes[3], count($lignes[0]) / 4, count($lignes[0]) / 4);
        $tab[4] = array_slice($lignes[4], count($lignes[0]) / 4, count($lignes[0]) / 4);
        $tableau_2[] = $tab;

        $tab[0] = array_slice($lignes[0], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
        $tab[1] = array_slice($lignes[1], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
        $tab[2] = array_slice($lignes[2], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
        $tab[3] = array_slice($lignes[3], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
        $tab[4] = array_slice($lignes[4], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
        $tableau_3[] = $tab;

        $tab[0] = array_slice($lignes[0], 3*count($lignes[0]) / 4);
        $tab[1] = array_slice($lignes[1], 3*count($lignes[0]) / 4);
        $tab[2] = array_slice($lignes[2], 3*count($lignes[0]) / 4);
        $tab[3] = array_slice($lignes[3], 3*count($lignes[0]) / 4);
        $tab[4] = array_slice($lignes[4], 3*count($lignes[0]) / 4);
        $tableau_4[] = $tab;

        if($colonnes<2)
          $tableau_5[] = $lignes;
    }
    $tab[0] = array_pop($tableau_1);
    $tab[0] = array_pop($tableau_1);
    $tab[1] = array_pop($tableau_2);
    $tab[1] = array_pop($tableau_2);
    $tab[2] = array_pop($tableau_3);
    $tab[2] = array_pop($tableau_3);
    $tab[3] = array_pop($tableau_4);
    $tab[3] = array_pop($tableau_4);
    $finale = $tab;
    //echo "<br>TABLEAU 1 <br>";
    //print_r($tableau_1); echo "<hr>";
    $this->_tab_simple($tableau_1,2,0,$titre,'(1/4)',$typeDraw);
    //echo "<br>TABLEAU 2 <br>";
    //print_r($tableau_2); echo "<hr>";
    $this->_tab_simple($tableau_2,2,32,$titre,'(2/4)',$typeDraw);
    $this->_tab_simple($tableau_3,2,64,$titre,'(3/4)',$typeDraw);
    $this->_tab_simple($tableau_4,2,96,$titre,'(4/4)',$typeDraw);


    $this->_tab_simple($tableau_5,1,0,$titre,'',$typeDraw);
    }

  //$this->Output();
  //exit;
  //$this->end();

}


  // {{{ doubleKO()
  /**
   * Display a model for a 64-entries tie for a double draw
   *
   * @return void
   */
  function doubleKO($valeurs_depart, $colonnes, $titre='', $typeDraw='', $ibf=0)
  {
    $file = "lang/".utvars::getLanguage()."/pdf.inc";
    require_once $file;

    $utfpdf =  new baseFpdf();

    $eventId = utvars::getEventId();

    //$this->version = "db";

    $ute = new utevent();
    $eventId = utvars::getEventId();
    $event = $ute->getEvent($eventId);

    foreach($valeurs_depart as $tour => $valeurs)
      {
	//echo "$tour <br>";print_r($valeurs);echo "<hr>";
	$nom = array();
	$logo = array();
	if(is_array($valeurs))
	  {
	    foreach($valeurs['value'] as $value)
	      {
		$nom[]= $value['name'];
		if(($value['ibfn']) && ($ibf))
		  $logo[] = $value['ibfn'];
		else
		  $logo[] = $value['logo'];
	      }
	  }
	else
	  {
	    $nom[] = "bye";$nom[]= '';
	    $logo[] = "";
	  }

	$joueurs[] = $nom ;
	$seeds[] = $valeurs['seed'];
	$flags[] = $logo;
	$scores[] = "" ;
	$match_num[] = '';
      }

    $tableau[] = array($joueurs, $scores, $seeds, $flags,$match_num);
    //print_r($tableau);
    //print_r($colonnes);

    foreach($colonnes as $tour => $valeurs)
      {
	$joueurs = array();
	$scores = array();
	$seeds = array();
	$match_num = array();
	$flags = array();

	foreach($valeurs as $cle => $vals)
	  {
	    $nom = array();
	    $logo = array();
	    if (is_array($vals))
	      {
		//echo "<$cle> <br>";print_r($vals);echo "<br>";
		if(is_array($vals['value'])){
		  foreach($vals['value'] as $value)
		    {
		      $nom[] = $value['name'];
		      //$logo = $value['logo'];
		      $logo[] = '';
		    }
		  //$nom = $vals['value'][0]['name'];
		  $score = $vals['score'];
		}
		else{
		  $nom[] = $vals['value'];$nom[] = '';
		  $score = "";
		  $logo[] = "";
		}
		//echo " $nom ($score) <br> ";
		$joueurs[] = $nom ;
		$seeds[] = '';//$valeurs['seed'];
		$flags[] = $logo;
		$scores[] = $score;
		if(isset($vals['match_num']))
		  $match_num[] = $vals['match_num'];
		else
		  $match_num[] = '';
	      }
	  }

	$tableau[] = array($joueurs, $scores, $seeds, $flags,$match_num);

      }

    // Le tableau est pr�t. Il faut l'afficher
    $nbLignes = count($tableau[0][0]);
    $nbCols = count($tableau);

    if($nbLignes <=  16 ){
      $this->_tab_double($tableau,0,0,$titre,'',$typeDraw);
    }
    // TABLEAU 32 //
    elseif(( $nbLignes > 16) && ($nbLignes <= 32)){
      foreach($tableau as $colonnes => $lignes)
	{
	  //print_r($lignes);echo "<hr>";
	  $tab[0] = array_slice($lignes[0], 0, count($lignes[0]) / 2);
	  $tab[1] = array_slice($lignes[1], 0, count($lignes[0]) / 2);
	  if (isset($lignes[2]))
	    {
	      $tab[2] = array_slice($lignes[2], 0, count($lignes[0]) / 2);
	      $tab[3] = array_slice($lignes[3], 0, count($lignes[0]) / 2);
	      $tab[4] = array_slice($lignes[4], 0, count($lignes[0]) / 2);
	    }
	  $tableau_1[] = $tab;

	  $tab[0] = array_slice($lignes[0], count($lignes[0]) / 2);
	  $tab[1] = array_slice($lignes[1], count($lignes[0]) / 2);
	  if (isset($lignes[2]))
	    {
	      $tab[2] = array_slice($lignes[2], count($lignes[0]) / 2);
	      $tab[3] = array_slice($lignes[3], count($lignes[0]) / 2);
	      $tab[4] = array_slice($lignes[4], count($lignes[0]) / 2);
	    }
	  $tableau_2[] = $tab;

	  if($colonnes!=0)
	    $tableau_3[] = $lignes;
	}
      $tab[0] = array_pop($tableau_1);
      $tab[1] = array_pop($tableau_2);
      $finale = $tab;
      //echo "<br>TABLEAU 1 <br>";
      //print_r($tableau_1); echo "<hr>";
      $this->_tab_double($tableau_1,1,0,$titre,'(1/2)',$typeDraw);
      //echo "<br>TABLEAU 2 <br>";
      //print_r($tableau_2); echo "<hr>";
      $this->_tab_double($tableau_2,1,32,$titre,'(2/2)',$typeDraw);

      $this->_tab_double($tableau_3,0,0,$titre,'',$typeDraw);
    }
    // TABLEAU 64 //
    else
      {
	foreach($tableau as $colonnes => $lignes){
	  //print_r($lignes);echo "<hr>";
	  $tab[0] = array_slice($lignes[0], 0, count($lignes[0]) / 4);
	  $tab[1] = array_slice($lignes[1], 0, count($lignes[0]) / 4);
	  $tab[2] = array_slice($lignes[2], 0, count($lignes[0]) / 4);
	  $tab[3] = array_slice($lignes[3], 0, count($lignes[0]) / 4);
	  $tab[4] = array_slice($lignes[4], 0, count($lignes[0]) / 4);
	  $tableau_1[] = $tab;

	  $tab[0] = array_slice($lignes[0], count($lignes[0]) / 4, count($lignes[0]) / 4);
	  $tab[1] = array_slice($lignes[1], count($lignes[0]) / 4, count($lignes[0]) / 4);
	  $tab[2] = array_slice($lignes[2], count($lignes[0]) / 4, count($lignes[0]) / 4);
	  $tab[3] = array_slice($lignes[3], count($lignes[0]) / 4, count($lignes[0]) / 4);
	  $tab[4] = array_slice($lignes[4], count($lignes[0]) / 4, count($lignes[0]) / 4);
	  $tableau_2[] = $tab;

	  $tab[0] = array_slice($lignes[0], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
	  $tab[1] = array_slice($lignes[1], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
	  $tab[2] = array_slice($lignes[2], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
	  $tab[3] = array_slice($lignes[3], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
	  $tab[4] = array_slice($lignes[4], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
	  $tableau_3[] = $tab;

	  $tab[0] = array_slice($lignes[0], 3*count($lignes[0]) / 4);
	  $tab[1] = array_slice($lignes[1], 3*count($lignes[0]) / 4);
	  $tab[2] = array_slice($lignes[2], 3*count($lignes[0]) / 4);
	  $tab[3] = array_slice($lignes[3], 3*count($lignes[0]) / 4);
	  $tab[4] = array_slice($lignes[4], 3*count($lignes[0]) / 4);
	  $tableau_4[] = $tab;

	  if($colonnes<2)
	    $tableau_5[] = $lignes;
	}
	$tab[0] = array_pop($tableau_1);
	$tab[0] = array_pop($tableau_1);
	$tab[1] = array_pop($tableau_2);
	$tab[1] = array_pop($tableau_2);
	$tab[2] = array_pop($tableau_3);
	$tab[2] = array_pop($tableau_3);
	$tab[3] = array_pop($tableau_4);
	$tab[3] = array_pop($tableau_4);
	$finale = $tab;
	//echo "<br>TABLEAU 1 <br>";
	//print_r($tableau_1); echo "<hr>";
	$this->_tab_double($tableau_1,2,0,$titre,'(1/4)',$typeDraw);
	//echo "<br>TABLEAU 2 <br>";
	//print_r($tableau_2); echo "<hr>";
	$this->_tab_double($tableau_2,2,16,$titre,'(2/4)',$typeDraw);
	$this->_tab_double($tableau_3,2,32,$titre,'(3/4)',$typeDraw);
	$this->_tab_double($tableau_4,2,48,$titre,'(4/4)',$typeDraw);


	$this->_tab_double($tableau_5,1,0,$titre,'',$typeDraw);
      }

    //$this->Output();
    //exit;

  }


  // {{{ _tab_double()
  /**
   * Display the draw for a double event
   *
   * @return void
   */
  function _tab_double( $tableau, $offset=0, $offsetPosition = 0,$titre = '',$npage = '', $typeDraw = '')
  {
    $ut = new Utils();

    $this->orientation = 'L';
    $this->AddPage('L');


    //		print_r($tableau);
    //    echo "<hr>";
    $xpos = 10;
    $ypos = 58;
    $ypos_start = $ypos;
    $nbLignes = count($tableau[0][0]);
    $taille = (210 - 80) / $nbLignes;
    //    print_r($tableau);
    $nbCols = count($tableau);
    $firstCol = 60;
    $margeDroite = 20;

    for ($i=0; $i < $nbCols; $i++){
      $title[$nbCols-$i -1 ] = $ut->getLabel(WBS_WINNER + $i + $offset);
    }
    //print_r($title);
    $this->SetFillColor(200,200,200);
    $this->SetFont('Arial','B',10);

    for ($i=0; $i < $nbCols; $i++){
      $largeur = $i ? (297-$firstCol-$margeDroite) / ($nbCols-1) :$firstCol;
      //      if(!isset($tableau[$i][0][0][0]))
	$this->Cell($largeur,10,''.$title[$i] ,1,0,'C',1);
    }
    $this->SetFillColor(255,255,255);

    $this->SetXY($xpos,$ypos);

    for($i=0;$i<$nbCols;$i++)
      {
	$largeur = $i ? (297-$firstCol-$margeDroite) / ($nbCols-1) :$firstCol;
	if($i==0){
	  $bords2 = '';
	  $this->SetFont('Arial','',6);
	}
	elseif($i==($nbCols-1)){
	  $bords2 = 'L';
	  $this->SetFont('Arial','B',12);
	}
	else{
	  $bords2 = 'L';
	  $this->SetFont('Arial','',8);
	}
	$ypos -= $taille/2;
	$nbLignes = count($tableau[$i][0]);
	for($j=0;$j<$nbLignes;$j++)
	  {
	    //		    print_r($tableau[$i][0][$j]);
	    //		    echo "<hr>";
	    $this->SetXY($xpos,$ypos+$taille*3/4);
	    // pour les tableaux de qualifs
	    if(!isset($tableau[$i][0][$j][0]))
	      $bords2 = '';
	    $this->Cell($largeur, $taille/2, '', $bords2 ,0,'C',0);
	    $this->SetXY($xpos,$ypos+$taille-3);
	    $position = "";
	    if($i==0)
	      {
		$ibfn = '';
		$ibfn2 = '';
		$position = $j+1+$offsetPosition;
		$this->Cell(5,3,''.$position ,'B' ,0,'L',0);
		//print_r($tableau[$i]);
		$this->Cell(5,3,''.$tableau[$i][2][$j]  ,'B' ,0,'L',0);
		if(isset($tableau[$i][3][$j][1]))
		  {
		    $image = $tableau[$i][3][$j][1];
		    if (is_file($image))
		      {
			$size = @getImagesize($image);
			$few = 6/$size[0];
			$feh = 3/$size[1];
			$fe = min(1, $feh, $few);
			$widthLogo = $size[0] * $fe;
			$heightLogo = $size[1] * $fe;

			$xLogo = 22;
			$yLogo = $ypos+$taille-3-$heightLogo-0.75;
			$this->Image($image, $xLogo, $yLogo,
				     $widthLogo, $heightLogo);
			$ibfn = '';
		      }
		    else
		      {
			$ibfn = $image ;
		      }
		  }
 		$image = $tableau[$i][3][$j][0];
		if (is_file($image))
		    {
		      $size = @getImagesize($image);
		      $few = 6/$size[0];
		      $feh = 3/$size[1];
		      $fe = min(1, $feh, $few);
		      $widthLogo = $size[0] * $fe;
		      $heightLogo = $size[1] * $fe;

		      $xLogo = 22;
		      $yLogo = $ypos+$taille-$heightLogo-0.5;
		      $this->Image($image, $xLogo, $yLogo,
				   $widthLogo, $heightLogo);
		      $ibfn2 = '';
		    }
		else
		  {
		    $ibfn2 = $image ;
		  }
		$joueurs = $tableau[$i][0][$j];
		//print_r($joueurs);
		$this->Cell(8,3,''.$ibfn2,'B' ,0,'L',0);
		$this->setXY($xpos+10,$ypos+2);
		$this->Cell(8,3,''.$ibfn,'' ,0,'L',0);
		$ibf=''; $ibf2 = '';
		if(isset($joueurs[1]))
		  {
		    $this->setXY($xpos+18,$ypos+$taille-6);
		    $this->Cell($largeur-18,3,''.$joueurs[1],'' ,0,'L',0);
		  }
		$this->setXY($xpos+18,$ypos+$taille-3);
		$this->Cell($largeur-18,3,''.$joueurs[0],'B' ,0,'L',0);
	      }
	    else
	      {
		$this->setXY($xpos,$ypos+$taille-3);
		$this->Cell(10,3,''.$tableau[$i][4][$j],'B',0,'L',0);
		$joueurs = $tableau[$i][0][$j];
		//print_r($joueurs);
		if(isset($joueurs[1]))
		  {
		    $this->setXY($xpos+10,$ypos+$taille-6);
		    $this->Cell($largeur-10,3,''.$joueurs[1]  ,'' ,0,'L',0);
		  }
		$this->setXY($xpos+10,$ypos+$taille-3);
		$this->Cell($largeur-10,3,''.$joueurs[0]  ,'B' ,0,'L',0);
	      }
	    if((($tableau[$i][1][$j])!='')&&($i!=0)){
	      $this->SetXY($xpos,$ypos+$taille);
	      $this->SetFont('Arial','',6);
	      $this->Cell($largeur-5,3,''.$tableau[$i][1][$j]  ,'' ,0,'C',0);
	      $this->SetFont('Arial','',8);
	      //	      $this->SetXY($xpos,$ypos+$taille+1);
	      //	      $this->Cell($largeur,3,''.$tableau[$i][1][$j]  ,'' ,0,'C',0);
	    }
	    $ypos += $taille ;
	  }
	//Cell( w,  h,  txt, border, ln, align, fill, link)
	$xpos += $largeur ;
	$ypos = $ypos_start ;
	$taille = $taille * 2;
      }

    $xpos -= $largeur;
    $this->SetFillColor(0,0,0);
    $this->SetTextColor(255);
    $this->setXY($xpos,60);
    $this->SetFont('Arial','UI',16);
    $this->Cell($largeur,10,''.$titre,0,0,'C',1);
    $this->setXY($xpos,70);
    $this->SetFont('Arial','I',14);
    if($npage != '')
      $this->Cell($largeur,10,''.$npage,0,0,'C',1);
    $this->setXY($xpos,175);
    $this->SetFont('Arial','I',14);
    $this->Cell($largeur,10,''.$typeDraw,0,0,'C',1);
    $this->SetTextColor(0);
  }
  //}}}


}

?>
