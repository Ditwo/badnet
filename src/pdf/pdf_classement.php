<?php
/*****************************************************************************
!   Module     : pdf
!   File       : $File$
!   Version    : $Name:  $
!   Revision   : $Revision: 1.6 $
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

define('FPDF_FONTPATH',dirname(__FILE__).'/../fpdf/font/');
require_once "fpdf/fpdf.php";
require_once "fpdf/rotation/rotation.php";

class  PDF_Classement extends PDF_Rotate
{

  function getBordureNom($position)
  {
    switch($position % 4){
      case 0 : $bordure = 'RB';break;
      case 1 : $bordure = '';break;
      case 2 : $bordure = 'B';break;
      case 3 : $bordure = 'R';break;
      default : $bordure = '';
      }
    return $bordure;
  }

  function getEntete($col,$total)
  {
    switch($total-$col){
      case 0 : $titre = 'Winner';break;
      case 1 : $titre = 'Final';break;
      case 2 : $titre = 'Semi Final';break;
      case 3 : $titre = 'Quarter Final';break;
      default : $titre = 'Round '.$col;
      }
    return $titre;
  }

  function getBordureKO($position,$finale=0)
  {
    switch($position % 8){
      case 0 : $bordure = '';break;
      case 1 : $bordure = '';break;
      case 2 : $bordure = 'B';break;
      case 3 : if(!$finale) { $bordure = 'TR';}else{$bordure = 'T';};break;
      case 4 : if(!$finale) { $bordure = 'R';}else{$bordure = '';};break;
      case 5 : $bordure = 'R';break;
      case 6 : $bordure = 'BR';break;
      case 7 : $bordure = 'T';break;
      default : $bordure = '';
      }
    return $bordure;
  }
  // {{{ tour
  /**
   * generate a PDF
   *
   * @access public
   * @param  string  $nb    nb de place dans le tableau
   * @param  string  $taille    hauteur du tableau
   * @param  string  $largeur    largeur du tableau
   * @param  string  $xpos    position verticale de debut
   * @param  string  $ypos    position horizontale de debut
   * @param  string  $joueurs    tableaux des noms des joueurs, paires ou equipes
   *                             5 champs : nomn, prenom, classement, club, departement
   * @param  string  $score  tableau des scores
   */

  function affiche_KO($valeurs, $names,$result=1){
    // $result = 1 => resultats
    // $result = 0 => schedule

    /*	 print_r($valeurs);
   echo "<hr>";
   print_r($names);
   echo "<hr>";*/

    $lesnoms = array();
    foreach($names as $name)
      {
	array_push($lesnoms,"");
	if(is_string($name))
	  $lesnoms[] = $name;
	else
	  $lesnoms[] = $name['value'];
      }

    $n_lignes = count($lesnoms);
    $n_col = 0;

    foreach($valeurs as $colonne)
      {
	$n_col++;
	$lesvaleurs[$n_col] = array();
	foreach($colonne as $valeur)
	  {
	    array_push($lesvaleurs[$n_col],"");

	    $lesvaleurs[$n_col][] = $valeur['value'];

	    if(($result)&&($valeur['score']!="0-0"))
	      $lesvaleurs[$n_col][] = $valeur['score'];
	    else
	      array_push($lesvaleurs[$n_col],"");

	    //$lesvaleurs[$n_col][] = $valeur['noc'];

	    array_push($lesvaleurs[$n_col],"");
	  }
      }

    $yinit = 70;
    $xinit = 10;
    $taille_bloc = 12; // fonction de $n_col
    $taille_texte = 6; // fonction de $n_col
    $largeur_bloc = 277/4; // fonction de $n_col
    $xpos = $xinit;
    $ypos = $yinit-20;
    $this->SetFillColor(192);

    $this->SetXY($xpos,$ypos);
    $this->SetFont('Arial','B',14);
    $n_val = count($lesvaleurs[$n_col]);
    $titre = $this->getEntete(0,$n_col);
    $this->Cell($largeur_bloc,$taille_bloc,"".$titre,1,0,'C','1');
    $xpos += $largeur_bloc;
    for($i=0;$i< $n_col; $i++)
      {
	$this->SetXY($xpos,$ypos);
	$n_val = count($lesvaleurs[$n_col]);
	$titre = $this->getEntete($i+1,$n_col);
	$this->Cell($largeur_bloc,$taille_bloc,"".$titre,1,0,'C','1');
	$xpos += $largeur_bloc;
      }

    //print_r($lesnoms);
    //echo "<hr>";
    //print_r($lesvaleurs);

    $xpos = $xinit;
    $ypos = $yinit;
    $this->SetFillColor(255);
    $this->SetFont('Arial','',10);
    //Cell( w,  h,  txt, border, ln, align, fill, link)
    for($i=0;$i< $n_lignes; $i++)
      {
	$this->SetXY($xpos,$ypos);
	// on veut $i de 1 � 4 et non de 0 � 3 !
	$bord = $this->getBordureNom($i+1);
	$this->Cell($largeur_bloc, $taille_bloc-$taille_texte,
		    "".$lesnoms[$i++],$bord,0,'C');
	$ypos += $taille_bloc-$taille_texte;
	$this->SetXY($xpos,$ypos);
	// on veut $i de 1 � 4 et non de 0 � 3 !
	$bord = $this->getBordureNom($i+1);
	$this->Cell($largeur_bloc,$taille_texte,"".$lesnoms[$i],$bord,0,'C');
	$ypos += $taille_texte;
      }

    $taille_bloc_init = $taille_bloc;
    //  $taille_bloc = 16;
    $xpos = $xinit+$largeur_bloc;
    $ypos = $yinit;
    $finale = 0;
    for($j=1;$j<=$n_col;$j++)
      {
	$n_lignes = count($lesvaleurs[$j]);
	if($n_lignes==4) $finale = 1 ;

	$this->SetXY($xpos,$ypos);
	$this->Cell($largeur_bloc,$taille_bloc_init / 2,"",0,0,'C');
	$ypos += $taille_bloc_init / 2 ;

	for($i=0;$i< $n_lignes; $i++)
	  {
	    $this->SetXY($xpos,$ypos);
	    // on veut $i de 1 � 4 et non de 0 � 3 !
	    $bord = $this->getBordureKO($i+1,$finale);
	    $this->Cell($largeur_bloc,$taille_bloc-$taille_texte,
			"".$lesvaleurs[$j][$i++],$bord,0,'C');
	    $ypos += $taille_bloc-$taille_texte;
	    $this->SetXY($xpos,$ypos);
	    // on veut $i de 1 � 4 et non de 0 � 3 !
	    $bord = $this->getBordureKO($i+1,$finale);
	    $this->Cell($largeur_bloc,$taille_texte,
			"".$lesvaleurs[$j][$i++],$bord,0,'C');
	    $ypos += $taille_texte;
	    $this->SetXY($xpos,$ypos);
	    // on veut $i de 1 � 4 et non de 0 � 3 !
	    $bord = $this->getBordureKO($i+1,$finale);
	    $this->Cell($largeur_bloc,$taille_texte,
			"".$lesvaleurs[$j][$i++],$bord,0,'C');
	    $ypos += $taille_texte;
	    $this->SetXY($xpos,$ypos);
	    // on veut $i de 1 � 4 et non de 0 � 3 !
	    $bord = $this->getBordureKO($i+1,$finale);
	    $this->Cell($largeur_bloc,$taille_bloc-$taille_texte,
			"".$lesvaleurs[$j][$i],$bord,0,'C');
	    $ypos += $taille_bloc-$taille_texte;
	  }
	$taille_bloc = $taille_bloc * 2 ;
	$ypos  = $yinit;
	$xpos += $largeur_bloc;
      }

  }
  // }}}

  // {{{ tour
  /**
   * generate a PDF
   *
   * @access public
   * @param  string  $nb    nb de place dans le tableau
   * @param  string  $taille    hauteur du tableau
   * @param  string  $largeur    largeur du tableau
   * @param  string  $xpos    position verticale de debut
   * @param  string  $ypos    position horizontale de debut
   * @param  string  $joueurs    tableaux des noms des joueurs, paires ou equipes
   *                             5 champs : nomn, prenom, classement, club, departement
   * @param  string  $score  tableau des scores
   */

	function tour($nb,$taille,$largeur,$xpos,$ypos,$joueurs,$score){
	//print_r($joueurs);
		$ypos -= $taille/2;
		if(1){
		for($i=0;$i<$nb;$i++){
			$this->SetXY($xpos,$ypos+$taille*3/4);
			$this->Cell($largeur,$taille/2,''    ,'L' ,0,'C',0);
			$this->SetXY($xpos,$ypos+$taille-5);
			if(($i%2)==0)
			$this->Cell($largeur,5,''.$joueurs[$i]    ,'L' ,0,'C',0);
			else
			$this->Cell($largeur,5,''.$score[$i]  ,'L' ,0,'C',0);

/*			$this->SetXY($xpos,$ypos+$taille);
			if(($i%2)!=0)
			$this->Cell($largeur,5,''.$score[$i]  ,'TL' ,0,'C',0);
			else
			$this->Cell($largeur,5,''.$joueurs[$i][0]    ,'TL' ,0,'C',0);
	*/
			$ypos = $ypos + $taille ;
		}
		}else{
		echo "joueurs ($nb): ";
		print_r($joueurs);
		echo "<br> scores : ";
		print_r($score);
		echo "<br>";
		for($i=0;$i<$nb;$i++){
  		  echo $joueurs[$i]."<br>";
  		  echo $score[$i];

		}
		echo "<hr>";
		}
	}
  // }}}

  // {{{ tie4()
  /**
   * Display a model for a 4-entries tie
   *
   * @return void
   */
	function tie4($joueurs, $scores,$debug=0)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require_once $file;

		$utfpdf =  new baseFpdf();

		$this->SetFont('Arial','',10);

		$this->AddPage('P');
      $joueurs4 = array_slice($joueurs,3,4);
      $scores4 = array_slice($scores,3,4);
      $joueurs2 = array_slice($joueurs,1,2);
      $scores2 = array_slice($scores,1,2);
      $joueurf = $joueurs;
      $scoref = $scores;
		  $this->tour(4, 11,30,50,10,$joueurs4,$scores4);
		  $this->tour(2, 22,30,80,10,$joueurs2,$scores2);
		  $this->tour(1, 44,30,110,10,$joueurf,$scoref);

		  if($debug){
		      print_r($joueurs4);
		      print_r($scores4);
		      print_r($joueurs2);
		      print_r($scores2);
		      print_r($joueurf);
		      print_r($scoref);
      }else{
    		$this->Output();
      }
      exit;
	}

}

?>
