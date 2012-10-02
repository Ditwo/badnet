<?php
/*****************************************************************************
!   Module     : pdf
!   File       : $File$
!   Version    : $Name:  $
!   Revision   : $Revision: 1.17 $
!   Author     : D.BEUVELOT
!   Revised by : $Author: cage $
!   Date       : $Date: 2006/02/06 22:17:40 $
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
//define('FPDF_FONTPATH',dirname(__FILE__).'/../fpdf/font/');
require_once "fpdf/fpdf.php";
require_once "fpdf/rotation/rotation.php";
require_once "utils/utbarcod.php";
//require_once "pdf_result.php";

require_once "base.php";

class PDF extends pdf_rotate //extends PDF_Result
{
  var $orientation="L";
  var $_isFooter = true;
  var $_isHeader = true;



  /*	function SetDash($black=false,$white=false)
	function EAN13($x,$y,$barcode,$h=16,$w=.35)
	function UPC_A($x,$y,$barcode,$h=16,$w=.35)
	function GetCheckDigit($barcode)
	function TestCheckDigit($barcode)
	function Barcode($x,$y,$barcode,$h,$w,$len)
	function Code39($xpos, $ypos, $code, $baseline=0.5, $height=5){
  */

  // {{{ constructor
  /**
   * Constructor.
   *
   * @param string $name      Name of button
   * @param string $attribs   Attribs of the button
   * @access public
   * @return void
   */
  function pdf()
    {
      parent::pdf_rotate();

      $ute = new utevent();
      $eventId = utvars::getEventId();
      $meta = $ute->getMetaEvent($eventId);
      if(is_file("./fpdf/font/{$meta['evmt_titleFont']}.z"))
	{
	  $fonts[$meta['evmt_titleFont']] = $meta['evmt_titleFont'];
	  $style[$meta['evmt_titleFont']] = '';
	}
      if (!isset($font['swz921n']))
	{
	  $fonts['swz921n'] = 'swz921n';
	  $style['swz921n'] = '';
	}
      foreach($fonts as $font)
      	$this->AddFont($font, $style[$font]);
    }

  // }}}


  // {{{ start()
  /**
   * Display the group of the division with the schedule
   *
   * @return void
   */
  function start()
    {
      // Création du document
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

  function SetDash($black=false,$white=false)
    {
      if($black and $white)
	$s=sprintf('[%.3f %.3f] 0 d',$black*$this->k,$white*$this->k);
      else
	$s='[] 0 d';
      $this->_out($s);
    }
  // }}}


  // {{{ EAN13($x,$y,$barcode,$h=16,$w=.35)
  /**
   *
   * Codage EAN13 pour un code barre
   *
   * @return void
   */

  function EAN13($x,$y,$barcode,$h=16,$w=.35)
    {
      $this->Barcode($x,$y,$barcode,$h,$w,13);
    }
  // }}}

  // {{{ UPC_A($x,$y,$barcode,$h=16,$w=.35)
  /**
   * Codage UPC_A pour un code barre
   *
   * @return void
   */

  function UPC_A($x,$y,$barcode,$h=16,$w=.35)
    {
      $this->Barcode($x,$y,$barcode,$h,$w,12);
    }
  // }}}

  // {{{ GetCheckDigit($barcode)
  /**
   * Récupère le digit de controle d'un code barre
   *
   * @return void
   */

  function GetCheckDigit($barcode)
    {
      //Calcule le chiffre de contrôle
      $sum=0;
      for($i=1;$i<=11;$i+=2)
	$sum+=3*$barcode{$i};
      for($i=0;$i<=10;$i+=2)
	$sum+=$barcode{$i};
      $r=$sum%10;
      if($r>0)
	$r=10-$r;
      return $r;
    }
  // }}}

  // {{{ TestCheckDigit($barcode)
  /**
   * Test le caractère de contrôle d'un code barre
   *
   * @return void
   */

  function TestCheckDigit($barcode)
    {
      //Vérifie le chiffre de contrôle
      $sum=0;
      for($i=1;$i<=11;$i+=2)
	$sum+=3*$barcode{$i};
      for($i=0;$i<=10;$i+=2)
	$sum+=$barcode{$i};
      return ($sum+$barcode{12})%10==0;
    }
  // }}}

  // {{{ Barcode($x,$y,$barcode,$h,$w,$len)
  /**
   * Affiche un code barre
   *
   * @return void
   */

  function Barcode($x,$y,$barcode,$h,$w,$len)
    {
      //Ajoute des 0 si nécessaire
      $barcode=str_pad($barcode,$len-1,'0',STR_PAD_LEFT);
      if($len==12)
	$barcode='0'.$barcode;
      //Ajoute ou teste le chiffre de contrôle
      if(strlen($barcode)==12)
	$barcode.=$this->GetCheckDigit($barcode);
      elseif(!$this->TestCheckDigit($barcode))
	$this->Error('Incorrect check digit');
      //Convertit les chiffres en barres
      $codes=array(
		   'A'=>array(
			      '0'=>'0001101','1'=>'0011001','2'=>'0010011','3'=>'0111101','4'=>'0100011',
			      '5'=>'0110001','6'=>'0101111','7'=>'0111011','8'=>'0110111','9'=>'0001011'),
		   'B'=>array(
			      '0'=>'0100111','1'=>'0110011','2'=>'0011011','3'=>'0100001','4'=>'0011101',
			      '5'=>'0111001','6'=>'0000101','7'=>'0010001','8'=>'0001001','9'=>'0010111'),
		   'C'=>array(
			      '0'=>'1110010','1'=>'1100110','2'=>'1101100','3'=>'1000010','4'=>'1011100',
			      '5'=>'1001110','6'=>'1010000','7'=>'1000100','8'=>'1001000','9'=>'1110100')
		   );
      $parities=array(
		      '0'=>array('A','A','A','A','A','A'),
		      '1'=>array('A','A','B','A','B','B'),
		      '2'=>array('A','A','B','B','A','B'),
		      '3'=>array('A','A','B','B','B','A'),
		      '4'=>array('A','B','A','A','B','B'),
		      '5'=>array('A','B','B','A','A','B'),
		      '6'=>array('A','B','B','B','A','A'),
		      '7'=>array('A','B','A','B','A','B'),
		      '8'=>array('A','B','A','B','B','A'),
		      '9'=>array('A','B','B','A','B','A')
		      );
      $code='101';
      $p=$parities[$barcode{0}];
      for($i=1;$i<=6;$i++)
	$code.=$codes[$p[$i-1]][$barcode{$i}];
      $code.='01010';
      for($i=7;$i<=12;$i++)
	$code.=$codes['C'][$barcode{$i}];
      $code.='101';
      //Dessine les barres
      for($i=0;$i<strlen($code);$i++)
	{
	  if($code{$i}=='1')
	    $this->Rect($x+$i*$w,$y,$w,$h,'F');
	}
      //Imprime le texte sous le code-barres
      $this->SetFont('Arial','',12);
      $this->Text($x,$y+$h+11/$this->k,substr($barcode,-$len));
    }
  // }}}

  // {{{ Code39($xpos, $ypos, $code, $baseline=0.5, $height=5)
  /**
   * Affiche un code barre en Code39
   *
   * @return void
   */

  function Code39($xpos, $ypos, $code, $baseline=0.5, $height=5){
    $wide = $baseline;
    $narrow = $baseline / 3 ;
    $gap = $narrow;

    $barChar['0'] = 'nnnwwnwnn';
    $barChar['1'] = 'wnnwnnnnw';
    $barChar['2'] = 'nnwwnnnnw';
    $barChar['3'] = 'wnwwnnnnn';
    $barChar['4'] = 'nnnwwnnnw';
    $barChar['5'] = 'wnnwwnnnn';
    $barChar['6'] = 'nnwwwnnnn';
    $barChar['7'] = 'nnnwnnwnw';
    $barChar['8'] = 'wnnwnnwnn';
    $barChar['9'] = 'nnwwnnwnn';
    $barChar['A'] = 'wnnnnwnnw';
    $barChar['B'] = 'nnwnnwnnw';
    $barChar['C'] = 'wnwnnwnnn';
    $barChar['D'] = 'nnnnwwnnw';
    $barChar['E'] = 'wnnnwwnnn';
    $barChar['F'] = 'nnwnwwnnn';
    $barChar['G'] = 'nnnnnwwnw';
    $barChar['H'] = 'wnnnnwwnn';
    $barChar['I'] = 'nnwnnwwnn';
    $barChar['J'] = 'nnnnwwwnn';
    $barChar['K'] = 'wnnnnnnww';
    $barChar['L'] = 'nnwnnnnww';
    $barChar['M'] = 'wnwnnnnwn';
    $barChar['N'] = 'nnnnwnnww';
    $barChar['O'] = 'wnnnwnnwn';
    $barChar['P'] = 'nnwnwnnwn';
    $barChar['Q'] = 'nnnnnnwww';
    $barChar['R'] = 'wnnnnnwwn';
    $barChar['S'] = 'nnwnnnwwn';
    $barChar['T'] = 'nnnnwnwwn';
    $barChar['U'] = 'wwnnnnnnw';
    $barChar['V'] = 'nwwnnnnnw';
    $barChar['W'] = 'wwwnnnnnn';
    $barChar['X'] = 'nwnnwnnnw';
    $barChar['Y'] = 'wwnnwnnnn';
    $barChar['Z'] = 'nwwnwnnnn';
    $barChar['-'] = 'nwnnnnwnw';
    $barChar['.'] = 'wwnnnnwnn';
    $barChar[' '] = 'nwwnnnwnn';
    $barChar['*'] = 'nwnnwnwnn';
    $barChar['$'] = 'nwnwnwnnn';
    $barChar['/'] = 'nwnwnnnwn';
    $barChar['+'] = 'nwnnnwnwn';
    $barChar['%'] = 'nnnwnwnwn';

    $this->SetFont('Arial','',10);
    //		$this->Text($xpos, $ypos + $height + 4, $code);
    $this->SetFillColor(0);

    $code = '*'.strtoupper($code).'*';
    for($i=0; $i<strlen($code); $i++){
      $char = $code{$i};
      if(!isset($barChar[$char])){
	$this->Error('Invalid character in barcode: '.$char);
      }
      $seq = $barChar[$char];
      for($bar=0; $bar<9; $bar++){
	if($seq{$bar} == 'n'){
	  $lineWidth = $narrow;
	}else{
	  $lineWidth = $wide;
	}
	if($bar % 2 == 0){
	  $this->Rect($xpos, $ypos, $lineWidth, $height, 'F');
	}
	$xpos += $lineWidth;
      }
      $xpos += $gap;
    }
  }
  // }}}

  // {{{ Header($)
  /**
   * Header des pages PDF
   * en fonction de l'orientation de la page L ou P
   * on affiche Badnet en filigramme
   *
   * @return void
   */

  function Header()
    {

      if($this->_isHeader)
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

	  // Affichage_declaration du titre du tournoi
	  //$this->SetFont($meta['evmt_titleFont'], '',
	  //		 );
	  $this->SetFont('swz921n', '', $meta['evmt_titleSize']);
	  $this->Cell(0, 10, $event['evnt_name'], 0, 1, 'C');

	  // Affichage du lieu du tournoi
	  $this->SetXY(130, 23);
	  $this->SetFont('arial','',20);
	  $this->Cell(0, 8,  $event['evnt_place'],0 ,1 ,'R');

	  // Affichage de la date du tournoi
	  $this->SetXY(130, 33);
	  $this->Cell(0,5, $event['evnt_date'], 0, 1, 'R');


	  // Calcul de la position du logo
	  $logo = utimg::getPathPoster($meta['evmt_logo']);
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
	  // Affichage du logo de gauche
	  if ($width && $height)
	    $this->Image($logo, $left, $top, $width, $height);

	  // Ligne de separation
	  $this->SetLineWidth(1);
	  $this->SetDrawColor(56,168,92);
	  $this->Line(10, 42, $maxWidth+10, 42);

	  // Se positionner apres la ligne
	  $this->SetY(45);
	}
    }
  // }}}

  // {{{ Footer()
  /**
   * Footer des documents PDF
   * En fonction de l'orientation L ou P, on affiche la date de
   * génération, le logo FPDF, le lien B@dNet et le numéro de page
   *
   * @return void
   */

  function Footer()
    {

      if($this->_isFooter)
	{
	  $maxWidth = ($this->orientation=='L') ? 297:210;
	  $maxHeight = ($this->orientation=='L') ? 210:297;
	  $maxWidth -= 10;
	  $maxHeight -= 10;

	  // Ligne de separation
	  $this->SetLineWidth(1);
	  $this->SetDrawColor(56,168,92);
	  $this->Line(10, $maxHeight-8, $maxWidth, $maxHeight-8);

	  //Texte techno
	  $this->SetTextColor(0);
	  $this->SetFont('arial', '', 6);
	  $this->SetXY(10, $maxHeight-6);
	  $this->Cell(0, 0, "a technologie developped by", '', 0, 'L');

	  // date de génération du document
	  $genere = date("d/m/Y H:i:s");
	  $label = $this->_getLabel('generate');
	  $this->SetXY($maxWidth-60, $maxHeight-6);
	  $this->Cell(60, 0, "$label $genere", '', 0, 'R');


	  // Logo badnet
	  $badnetLogo = utimg::getLogo('badnetfull.jpg');
	  if(is_file($badnetLogo))
	    $this->Image($badnetLogo, ($maxWidth/2)-10, $maxHeight-6, 0, 10,
			 '', 'http://www.badnet.org');

	  /*$this->SetTextColor(0);
	  $this->SetXY(5, $maxHeight-7);
	  $this->Cell(20, 0, $version, 0, 0, 'C');

	  $this->SetXY($maxWidth-30, -17);
	  $label = $this->_getLabel('page').$this->PageNo().'/{nb}';
	  $this->Cell(30, 7, $label, 0, 2, 'C');
	  */
	}
    }
  // }}}


  function RotatedText($x,$y,$txt,$angle)
    {
      //Rotation du texte autour de son origine
      $this->Rotate($angle,$x,$y);
      $this->Text($x,$y,$txt);
      $this->Rotate(0);
    }

  function RotatedImage($file,$x,$y,$w,$h,$angle)
    {
      //Rotation de l'image autour du coin supérieur gauche
      $this->Rotate($angle,$x,$y);
      $this->Image($file,$x,$y,$w,$h);
      $this->Rotate(0);
    }


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
      $this->AliasNbPages();

      $teams = $utfpdf->getTeams($tieId);
      $group = $utfpdf->getGroup($tieId);

      $matchs = $utfpdf->getMatches($tieId, $teams[0][0], $teams[1][0]);
      $tieDate = $utfpdf->getTieDate($tieId);

      $ute = new utevent();
      $event = $ute->getEvent($eventId);
      $titre='Badnet';

      // Une feuille de déclaration pour chaque équipe
      for($k=0;$k<2;$k++){
	// Création du document
	$this->orientation = 'P';
	$this->AddPage();
	$this->SetTitle($titre);
	$this->SetAuthor('BadNet Team');

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
	$this->Cell(23, 8, $PDF_GROUP, 'LTRB' , 1, 'C', 0);
	$this->SetXY(136, 81);
	$this->Cell(23, 8, $PDF_TEAM_COURT, 'LTRB' , 1, 'C', 0);

	// Valeur
	$this->SetFont('Arial','B',12);
	// Date
	$this->SetXY(35, 50);
	$this->Cell(57, 6, $tieDate->getDateWithDayWithoutYear(),
		    'B',0,'C',0);
	// Heure
	$this->SetFont('Arial','B',24);
	$this->SetXY(148, 50);
	$this->Cell(52, 6, $tieDate->getTime(),'B',0,'C',0);

	// Equipe A
	$this->SetFont('Arial','B',18);
	$this->SetXY(35, 64);
	$this->Cell(20, 8,$teams[0][3],'B',0,'C',0);

	$this->SetFont('Arial','',10);
	$this->SetXY(55, 64);
	$this->Cell(66, 8,$teams[0][1],'B',0,'L',0);

	$this->SetFont('Arial','B',8);
	$this->SetXY(112, 64);
	$this->Cell(10, 8,$PDF_VERSUS, 'B',0,'C',0);

	// Equipe B
	$this->SetFont('Arial','B',18);
	$this->SetXY(122, 64);
	$this->Cell(20, 8,$teams[1][3],'B',0,'C',0);

	$this->SetFont('Arial','',10);
	$this->SetXY(142, 64);
	$this->Cell(58, 8,$teams[1][1],'B',0,'L',0);

	// Division
	$this->SetFont('Arial','B',12);
	$this->SetXY(35, 83);
	$this->Cell(32, 6, $group['draw_stamp'],'B',0,'C',0);

	// Group
	$this->SetFont('Arial','B',24);
	$this->SetXY(98, 81);
	$this->Cell(32, 8, $group['rund_stamp'],'B',1,'C',0);

	// Terrain
	$this->SetXY(162, 81);
	$this->Cell(38, 8, $group['tie_court'], 'B', 1, 'C', 0);

	// Ligne de separation
	$this->SetLineWidth(1);
	$this->SetDrawColor(56,168,92);
	$this->Line(10, 95, 200, 95);

	// Titre
	$this->SetXY(10, 100);
	$this->SetFont('swz921n','',15);
	$this->SetTextColor(255);
	$this->SetFillColor(0);
	$this->Cell(85,8, $PDF_COMPO,'',1,'C',1);

	// Equipe deposante
	$this->SetTextColor(0);
	$this->SetFillColor(255);
	$this->SetLineWidth(0.2);
	$this->SetDrawColor(0);
	$this->SetXY(100, 100);
	$this->SetFont('swz921n','',12);
	$this->Cell(0,8,$teams[$k][1].' ('.$teams[$k][3].')','LTRB',1,'C',0);

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

      $this->Output();
      //exit;
    }
  // }}}





  // {{{ affichage_schedu($divId)
  /**
   * Display the group of the division with the schedule
   *
   * @return void
   */
  function affichage_schedu($divId)
    {
      $file = "lang/".utvars::getLanguage()."/pdf.inc";
      require $file;

      $utfpdf =  new baseFpdf();

      $this->AliasNbPages();

      // Création du document
      $titre='Badnet Division';
      $this->SetFillColor(255,255,255);
      $this->SetTitle($titre);
      $this->SetAuthor('BadNet Team');


      //--- List of divisions (ie the draws in the database)
      $utt =  new utteam();
      $div = $utt->getDiv($divId);
      $groups = $utt->getGroups($divId);
      $hauteur = 10;
      $top = 300;
      $fullHeight = 0;
      foreach($groups as $groupId=>$group)
	{
	  // Ne traiter que les groupe de type poule
	  if ($group['rund_type'] != WBS_TEAM_GROUP &&
	      $group['rund_type'] != WBS_TEAM_BACK)
	    {
	      //      continue;
	      $this->orientation = 'L';
	      $this->AddPage('L');
	      $dt = $utfpdf;
	      $valeurs = array();

	      $teams = $utt->getTeamsGroup($group['rund_id']);
	      $names= array();
	      foreach($teams as $team)
		{
		  $logo = utimg::getPathFlag($team['asso_logo']);
		  $names[] = array('logo' => $logo,
				   'value' => $team['team_noc'].' - '.
				   $team['team_name'],
				   'noc' => $team['team_noc'],
				   'link' => $team['team_id']) ;
		}
      //print_r($names);

	      require_once "utils/utko.php";
	      $utko = new utKo($names, 4);
	      $namesVals = $utko->getExpandedValues();
//	      $kdiv2 = & $kdiv->addDiv("divDraw{$group['rund_id']}",
//				       'blkGroup');
//	      $kdiv2->addMsg($group['rund_name'], '', 'titre');
//	      $kdraw = & $kdiv2->addDraw("draw{$group['rund_id']}");
//	      $kdraw->setValues(1, $vals);

	      $size = $utko->getSize();
	      $teams = $dt->getWinners($group['rund_id']);
	      $ties = $dt->getKoTies($group['rund_id']);
	      $numCol = 2;
	      while( ($size/=2) >= 1)
		{
		  $vals = array();
		  $firstTie = $size-1;
		  for($i=0; $i < $size; $i++)
		    {
		      if (isset($teams[$firstTie + $i]))
			{
			  $team = $teams[$firstTie + $i];
			  $logo = utimg::getPathFlag($team['asso_logo']);
			  $vals[] = array('value' => $team['team_noc'].
					  ' - '.$team['team_name'],
					  'noc' => $team['team_noc'],
					  'score' => $team['score'],
					  'logo' => $logo,
					  'link' => $team['tie_id']) ;
			}
		      else
			{
			  $tie = $ties[$firstTie + $i];
			  $vals[] = array('value' => $tie['tie_schedule'],
					  'score' => $tie['score'],
					  'noc' => '',
					  'logo' => '',
					  'link' => $tie['tie_id']) ;
			}
        }


      $valeurs[] = $vals ;
		}

      $this->affiche_KO($valeurs,$namesVals,0);
//print_r($valeurs);

      }
      else
      {

	  // Rencontres du groupe
	  $rows = $utfpdf->getTiesSchedu($groupId);
	  $nbTeams = count($rows);

	  //Calcul de la hauteur du groupe
	  // S'il depasse, sauter une page
	  if (($top + ($nbTeams*$hauteur)) > $fullHeight)
	    {
	      if ($nbTeams > 6)
		{
		  $this->orientation = 'L';
		  $this->AddPage('L');
		  $fullHeight = 180;
		  $fullWidth = 197;
		}
	      else
		{
		  $this->orientation = 'P';
		  $this->AddPage('P');
		  $fullHeight = 267;
		  $fullWidth = 110;
		}
	      // Titre
	      $this->SetXY(10, 51);
	      $this->SetFont('swz921n','',20);
	      $this->SetTextColor(255);
	      $this->SetFillColor(0);
	      $this->Cell(70,$hauteur, $PDF_SCHEDULE,'',1,'C',1);

	      // Division
	      $this->SetTextColor(0);
	      $this->SetFillColor(255);
	      $this->SetLineWidth(0.2);
	      $this->SetDrawColor(0);
	      $this->SetXY(82, 51);
	      $this->SetFont('swz921n','',20);
	      $this->Cell(0,$hauteur,$div['draw_name'], 'B', 1, 'C', 0);

	      $top = 70;
	    }
	  $this->SetY($top);
	  $this->SetFillColor(192);
	  $this->SetFont('Arial','B',18);
	  $this->Cell(79, $hauteur, $group['rund_name'], 1, 0, 'C', 1);
	  $this->Cell(1, $hauteur, '', '', 0);

	  $this->SetFont('Arial','',10);
	  $width = $fullWidth/$nbTeams;
	  for ($i=0; $i< $nbTeams;$i++)
            $this->Cell($width, $hauteur,''.$rows[$i]['team_stamp'],1,0,'C',0);

	  $this->Ln();

	  $top += $hauteur+1;
	  for ($i=0; $i< $nbTeams;$i++)
	    {
	      $this->SetY($top);
	      if (is_file($rows[$i]['team_logo']))
		{
		  $size = @getImagesize($rows[$i]['team_logo']);
		  $few = 8/$size[0];
		  $feh = 6/$size[1];
		  $fe = min(1, $feh, $few);
		  $widthLogo = $size[0] * $fe;
		  $heightLogo = $size[1] * $fe;

		  $xpos = 67 + (8-$widthLogo)/2;
		  $ypos = $top + 2 + (6-$heightLogo)/2;
		  $this->Image($rows[$i]['team_logo'], $xpos, $ypos,
			       $widthLogo, $heightLogo);
		}
	      $this->Cell(66, $hauteur, $rows[$i]['team_name'], 'LTBR',
			  0, 'L', 0);
	      $this->Cell(13, $hauteur, $rows[$i]['team_stamp'], 'LTBR',
			  0, 'C', 0);
	      $adjust = 0;
	      $left = 90;
	      for ($j=0; $j< $nbTeams;$j++)
		{
		  $this->SetXY($left, $top);
		  if ($i==$j)
		    {
		      $this->Cell($width,10, '----',1,0,'C',0);
		      $adjust = 1;
		    }
		  else
		    {
		      $this->SetXY($left, $top);
		      $this->Cell($width,$hauteur/2, $rows[$i][4*($j-$adjust)],
				  'LTR',0,'C',0);
		      $this->SetXY($left, $top+$hauteur/2);
		      $this->Cell($width, $hauteur/2, $rows[$i][(4*($j-$adjust))+1],
				  'LBR',0,'C',0);
		    }
		  $left += $width;
		}

	      $this->Ln();
	      $top += $hauteur;
	    }
	  $top += (2*$hauteur);
	}
      }
      $this->Output();
      exit;
    }
  // }}}


  // {{{ displayProgram($divId)
  /**
   * Display the program of the division
   *
   * @return void
   */
  function displayProgram($divId)
    {
      $file = "lang/".utvars::getLanguage()."/pdf.inc";
      require $file;

      $utfpdf =  new baseFpdf();

      $this->AliasNbPages();

      // Création du document
      $this->orientation = 'P';
      $titre='Badnet Division';
      $this->SetFillColor(255,255,255);
      $this->SetTitle($titre);
      $this->SetAuthor('BadNet Team');
      $this->SetAutoPageBreak(false);


      //--- List of divisions (ie the draws in the database)
      $utt =  new utteam();
      $div = $utt->getDiv($divId);
      require_once "schedu/base_V.php";
      $dt = new scheduBase_V();
      $dates = $dt->getDateTies($divId);
      $hauteur = 18;
      $top = 300;


      //Pour chaque date
      foreach($dates as $date=>$fullDate)
	{
	  // Recuperer le programme du jour
	  $times = $utfpdf->getProgram($divId, $date);

	  $nbDates = count($times);

	  //Calcul de la hauteur du groupe
	  // S'il depasse, sauter une page
	  if (($top + $nbDates*$hauteur) > 270)
	    {
	      $this->AddPage('P');
	      // Titre
	      $this->SetXY(10, 48);
	      $this->SetFont('swz921n','',20);
	      $this->SetTextColor(255);
	      $this->SetFillColor(0);
	      $this->Cell(70, 10, $PDF_PROGRAM,'',1,'C',1);

	      // Division
	      $this->SetTextColor(0);
	      $this->SetFillColor(255);
	      $this->SetLineWidth(0.3);
	      $this->SetDrawColor(0);
	      $this->SetXY(82, 48);
	      $this->SetFont('swz921n','',20);
	      $this->Cell(0,10,$div['draw_name'], 'B', 1, 'C', 0);

	      $top = 62;
	      $left = 10;
	    }
	  $this->SetXY($left, $top);
	  $this->SetFont('Arial','B',14);
	  $this->SetFillColor(192);
	  $this->Cell(70, 8, $fullDate, 1, 0, 'C', 1);

	  $posY = $top+11;
	  $hauteur = 18;
	  //	  print_r($times);echo "<br>";
	  $nbCells = 1;
	  foreach($times as $time)
	    $nbCells = max(count($time), $nbCells);
	  $nbCellsLimited = ($nbCells > 5) ? 5 : $nbCells ;
	  $witdh = 173/($nbCellsLimited-1);

	  $startY = $posY;
	  foreach($times as $time)
	    {
	      $this->SetXY($left, $posY);
	      $this->SetFont('Arial','B',14);
	      $this->SetTextColor(255);
	      $this->SetDrawColor(255);
	      $this->SetFillColor(0);
	      $this->Cell(17, $hauteur, $time[0], 'TB', 0, 'C', 1);
	      $this->SetTextColor(0);
	      $this->SetDrawColor(0);
	      $posX=$left+17;
	      for($i=1; $i<$nbCells; $i++)
		{
		  $this->SetXY($posX, $posY);
		  if (isset($time[$i]))
		    {
		      $lines = $time[$i];
		      $this->SetFont('Arial','',12);
		      $this->Cell($witdh-8, $hauteur/3, $lines[0],
				  'LTRB', 0, 'C', 0);

		      $this->SetFont('Arial','B',12);
		      $this->Cell(8, $hauteur/3, $lines[3],
				  'LTRB', 0, 'C', 0);
		      $this->SetXY($posX, $posY+($hauteur/3));
		      $this->SetFont('Arial','B',14);
		      $this->Cell($witdh, $hauteur*2/3, $lines[1],
				  'LTBR', 0, 'C', 0);
		      //$this->SetXY($posX, $posY+ 2*($hauteur/3));
		      //$this->Cell($witdh, $hauteur/3, $lines[2], 'LBR', 0, 'C', 0);
		      $mi = $i%5;
		      echo " $i $mi <br>";
		      if(($i%5)==0)
			{
			  $posY += $hauteur;
			  $posX = $left + 17;
			}
		    }
		  else
		    $this->Cell($witdh, $hauteur, '', 'LTBR', 0, 'C', 0);
		  $posX += $witdh;
		}
	      //if($posY > 297-50-$hauteur)
	      //{
	      //  $this->AddPage('P');
	      //  $posY = 48;
	      //}
	      //else
		$posY+=$hauteur;
	    }
	  $this->Line($left, $startY, $left+17, $startY);
	  $this->Line($left, $posY, $left+17, $posY);
	  $top = $posY+8;
	}

      $this->Output();
      exit;
    }
  // }}}


  // {{{ displayProgramIndiv($divId)
  /**
   * Display the program of the division
   *
   * @return void
   */
  function displayProgramIndiv($rndId)
    {
      $file = "lang/".utvars::getLanguage()."/pdf.inc";
      require $file;

      $utfpdf =  new baseFpdf();

      $this->AliasNbPages();

      // Création du document
      $this->orientation = 'P';
      $titre='Badnet Division';
      $this->SetFillColor(255,255,255);
      $this->SetTitle($titre);
      $this->SetAuthor('BadNet Team');
      $this->SetAutoPageBreak(false);


      //--- List of divisions (ie the draws in the database)
      $utt =  new utteam();
      $utr =  new utround();
      $ut = new utils();
      $rounds = $utr->getRounds();
      $rows = array();
//      print_r($rndId);

	  //Display the table with tie schedule
	  $dates = $utfpdf->getDateTies();

//print_r($dates);
      $hauteur = 18;
      $top = 300;

      foreach($dates as $date => $heures)
      {

	$times = array();
	if($date != ""){
          //print_r($heures);
          foreach($heures as $idx => $heure)
	    $times[] = $utfpdf->getProgramIndiv($date, $heure);
	  
	  
	  $nbDates = count($times);
	  
	  
	  //Calcul de la hauteur du groupe
	  // S'il depasse, sauter une page
	  // if (($top + $nbDates*$hauteur) > 270)
	    if(1)
	    {
	      $this->AddPage('P');
	      // Titre
	      $this->SetXY(10, 48);
	      $this->SetFont('swz921n','',20);
	      $this->SetTextColor(255);
	      $this->SetFillColor(0);
	      $this->Cell(70, 10, ''.$PDF_PROGRAM,'',1,'C',1);

	      // Division
	      $this->SetTextColor(0);
	      $this->SetFillColor(255);
	      $this->SetLineWidth(0.3);
	      $this->SetDrawColor(0);
	      $this->SetXY(82, 48);
	      $this->SetFont('swz921n','',20);
	      $this->Cell(0,10,'', 'B', 1, 'C', 0);

	      $top = 62;
	      $left = 10;
	      }
	  $this->SetXY($left, $top);
	  $this->SetFont('Arial','B',14);
	  $this->SetFillColor(192);
	  $fullDate = $date;
	  $this->Cell(70, 8, ''.$fullDate, 1, 0, 'C', 1);

	  $posY = $top+11;
	  $hauteur = 18;
	  //	  print_r($times);echo "<br>";
	  $nbCells = 1;
	  foreach($times as $time)
	    $nbCells = max(count($time), $nbCells);
	  //$nbCellsLimited = ($nbCells > 5) ? $nbCells : 5 ;
	  $nbCellsLimited = ($nbCells > 5) ? 5 : $nbCells ;
	  $width = 173/($nbCellsLimited);
	  //	  $witdh = 173/($nbCells);

	  $startY = $posY;

	  foreach($times as $time)
	    {

	      $this->SetXY($left, $posY);
	      $this->SetFont('Arial','B',14);
	      $this->SetTextColor(255);
	      $this->SetDrawColor(255);
	      $this->SetFillColor(0);
		      //print_r($time);
	      $this->Cell(17, $hauteur, ''.$time[0]['time'], 'TB', 0, 'C', 1);

	      $this->SetTextColor(0);
	      $this->SetDrawColor(0);
	      $posX=$left+17;
	      for($i=0; $i<$nbCells; $i++)
		{
		  $this->SetXY($posX, $posY);
		  if (isset($time[$i]))
		    {
		      $lines = $time[$i];
		      //print_r($lines);
		      $this->SetFont('Arial','',12);

		  $step = $utr->getTieStep($lines['tie_posRound']);
		  $labelStep = $ut->getLabel($step);

		      $this->Cell($width-12, $hauteur/3, ''.$labelStep,
           'LTRB', 0, 'C', 0);

		      $this->SetFont('Arial','B',12);
		      $this->Cell(12, $hauteur/3, ''.$lines['mtch_num'],
				  'LTRB', 0, 'C', 0);
		      $this->SetXY($posX, $posY+($hauteur/3));
		      $this->SetFont('Arial','I',10);
		      $this->Cell($width, $hauteur*2/3, ''.$lines['draw_name'],
				  'LTBR', 0, 'C', 0);
		      $duree = $lines['duree'] / 60 ;
		      if($duree > 1)
		      {
             $this->SetXY($posX, $posY+(5*$hauteur/6));
	           //$this->SetTextColor(255,0,0);
	           //$this->SetTextColor(0,150,0);
	           $this->SetTextColor(0,0,255);
		         $this->SetFont('Arial','BI',8);
		         $this->Cell($width, 2, ''.$duree.'\'',0, 0, 'C', 0);
           	 $this->SetTextColor(0);
		      }
		    }
		  //else
		    //$this->Cell($witdh, $hauteur, '', 'LTBR', 0, 'C', 0);
		  $posX += $width;
		  if(((($i+1)%5)==0) && (isset($time[$i+1])))
		    {
		      $posY += $hauteur;
		      $posX = $left+17;
		    }
		  
		}
	      if($posY > 297-50-$hauteur)
		{
		  $this->AddPage('P');
		  $posY = 48;
		}
	      else
		$posY+=$hauteur;
	    }
	  $this->Line($left, $startY, $left+17, $startY);
	  $this->Line($left, $posY, $left+17, $posY);
	  $top = $posY+8;
	  }
	//$this->AddPage('P');
	//$posY = 48;

      }
      $this->Output();
      exit;
    }
  // }}}

  // {{{ trace_cutlines()
  /**
   * Display the list of the items with associated barcode
   *
   * @return void
   */
  function trace_cutlines($hauteur_ligne)
    {
      $this->SetLineWidth(0.5);
      $this->SetDrawColor(220,220,220);
      $this->SetDash(1,2); //5 mm noir, 5 mm blanc
      $start = 100;
      $ystart = 2;
      $yend   = 285;
      $this->Line($start,$ystart,$start,$yend);
      $this->Line($start+102,$ystart,$start+102,$yend);

      $start = 68;
      $xstart = 1;
      $xend   = 208;
      $this->Line($xstart,$start,$xend,$start);
      $this->Line($xstart,$start+5*$hauteur_ligne,$xend,$start+5*$hauteur_ligne);
      $this->Line($xstart,$start+10*$hauteur_ligne,$xend,$start+10*$hauteur_ligne);
      $this->Line($xstart,$start+15*$hauteur_ligne,$xend,$start+15*$hauteur_ligne);

      $this->SetLineWidth(0.4);
      $this->SetDrawColor(0);
      $this->SetDash(); //5 mm noir, 5 mm blanc
    }


  function getTeteSerie($taille, $indice){
    switch($taille){
    case 16 :
      switch($indice){
      case 1  : $sortie = '1'   ;break;
      case 5  :
      case 12 : $sortie = '3/4' ;break;
      case 16 : $sortie = '2'   ;break;
      default : $sortie = ''    ;
      }
      break;
    case 32 :
      switch($indice){
      case 1  : $sortie = '1'   ;break;
      case 9  :
      case 24 : $sortie = '3/4' ;break;
      case 5  :
      case 13  :
      case 20  :
      case 28 : $sortie = '5/8' ;break;
      case 32 : $sortie = '2'   ;break;
      default : $sortie = ''    ;
      }
      break;
    case 64 :
      switch($indice){
      case 1  : $sortie = '1'   ;break;
      case 17  :
      case 48 : $sortie = '3/4' ;break;
      case 9  :
      case 25  :
      case 40  :
      case 56 : $sortie = '5/8' ;break;
      case 64 : $sortie = '2'   ;break;
      default : $sortie = ''    ;
      }
      break;
    default :   $sortie='';
    }
    return $sortie;
  }

  function getBordure( $indice,$taille){
    // Tableau 32ème
    if($taille==6){
      // Position des cases encadrées
      $complet = array (	1,7,19,25,37,43,55,61,73,79,91,97,106,109,115,
				127,133,145,151,163,169,181,187,199,205,217,223,
				235,241,253,259,271,277);
      // Position des cases noms
      $gauche_bas  = array (	2,20,38,56,74,92,110,128,146,164,182,200,218,236,254,272,
				15,51,87,123,159,195,231,267,
				34,106,178,250,
				71,215,
				144);
      // Position des montants
      $gauche  = array (	9,21,45,57,81,93,117,129,153,165,189,201,225,237,261,273,
				22,28,40,46,52,94,100,112,118,124,166,172,184,190,196,238,244,256,262,268,
				41,47,53,59,65,77,83,89,95,101,107,185,191,197,203,209,221,227,233,239,245,251,
				78,84,90,96,102,108,114,120,126,132,138,150,156,162,168,174,180,186,192,198,204,210,216);
    }

    // Tableau 16ème
    if($taille==5){
      // Position des cases encadrées
      $complet = array (1,6,16,21,31,36,46,51,61,66,76,81,91,96,106,111);
      // Position des cases noms
      $gauche_bas  = array (	2,17,32,47,62,77,92,107,
				13,43,73,103,29,89,60);
      // Position des montants
      $gauche  = array (	8,18,38,48,68,78,98,108,
				19,24,34,39,44,79,84,94,99,104,
				35,40,45,50,55,65,70,75,80,85,90);
    }

    // Tableau 8ème
    if($taille==4){
      // Position des cases encadrées
      $complet = array (1,5,13,17,25,29,37,41);
      // Position des cases noms
      $gauche_bas  = array (	2,14,26,38,
				11,35,24);
      // Position des montants
      $gauche  = array (	6,18,30,42,
				7,15,31,39,16,20,28,32,36);
    }

    $bordure = '0';

    if(in_array($indice,$complet)) $bordure= '1';
    if(in_array($indice,$gauche_bas)) $bordure= 'LB';
    if(in_array($indice,$gauche)) $bordure= 'L';

    return $bordure;
  }



  // {{{ tie16()
  /**
   * Display a model for a 16-entries tie
   *
   * @return void
   */
  function tie16()
    {
      $file = "lang/".utvars::getLanguage()."/pdf.inc";
      require_once $file;

      $utfpdf =  new baseFpdf();

      $eventId = utvars::getEventId();

      //	  if(preg_match("/V[\d]+_[\d]+r[\d]+/", utils::getParam('version'),
      //			$curVersion))
      //	    $this->version = $curVersion[0];
      $this->version = "db";

      $ute = new utevent();
      $eventId = utvars::getEventId();
      $event = $ute->getEvent($eventId);


      $titre='Badnet Division';

      //echo $titre;

      //Cell( w,  h,  txt, border, ln, align, fill, link)
      $this->AddPage('P');
      $this->SetFont('Arial','B',18);
      $this->SetXY(10,10);
      $this->Cell(130,20,'RAPPORT DU JUGE-ARBITRE',1,0,'C');
      $this->SetXY(55,20);
      $this->SetFont('Arial','',10);
      $this->Cell(30,10,'TOURNOI',0,0,'C');
      $this->SetXY(140,10);
      $this->SetFont('Arial','',8);
      $this->Cell(0,5,'adoption','TR',0,'L');
      $this->SetXY(140,15);
      $this->Cell(0,5,'entrée en vigueur','R',0,'L');
      $this->SetXY(140,20);
      $this->Cell(0,5,'validité','R',0,'L');
      $this->SetXY(140,25);
      $this->Cell(0,5,'remplace','BR',0,'L');

      $this->SetXY(10,32);
      $this->SetFont('Arial','',8);
      $this->Cell(140,10,'Nom de la compétition :','LTR',0,'L');

      $this->SetXY(10,35);
      $this->SetFont('Arial','',8);
      $this->Cell(140,10,'Lieu :',0,0,'L');

      $this->SetXY(10,40);
      $this->SetFont('Arial','',8);
      $this->Cell(140,10,'Club organisateur :','LR',0,'L');

      $this->SetXY(75,35);
      $this->SetFont('Arial','',8);
      $this->Cell(140,10,'Date :','',0,'L');

      $this->SetXY(75,45);
      $this->SetFont('Arial','',8);
      $this->Cell(140,10,'Ligue :','',0,'L');

      $this->SetXY(10,45);
      $this->SetFont('Arial','',6);
      $this->Cell(140,10,'(en toutes lettres)','LBR',0,'L');

      $this->SetXY(150,32);
      $this->SetFont('Arial','B',8);
      $this->Cell(50,10,'Autorisation de la compétition','LTR',0,'C');

      $this->SetXY(150,35);
      $this->SetFont('Arial','',8);
      $this->Cell(10,10,'par :','L',0,'L');
      $this->SetFont('Arial','B',8);
      $this->Cell(10,10,'ligue :','',0,'L');
      $this->SetXY(170,38);
      $this->Cell(4,4,'',1,0,'L');
      $this->SetXY(180,35);
      $this->Cell(30,10,'fédé :','R',0,'L');
      $this->SetXY(190,38);
      $this->Cell(4,4,'',1,0,'L');

      $this->SetXY(150,40);
      $this->SetFont('Arial','',8);
      $this->Cell(50,10,'n° :','LR',0,'L');

      $this->SetXY(150,45);
      $this->SetFont('Arial','',8);
      $this->Cell(50,10,'date :','LBR',0,'L');

      $this->SetXY(10,58);
      $this->SetFont('Arial','',8);
      $this->Cell(40,5,'',1,0,'L');
      $this->Cell(50,5,'Juge-Arbitre',1,0,'C');
      $this->Cell(50,5,'Juge-Arbitre adjoint',1,0,'C');
      $this->Cell(50,5,'Juge-Arbitre adjoint',1,1,'C');
      $this->Cell(40,5,'Nom et prénom','LR',0,'L');
      $this->Cell(50,5,'','LR',0,'C');
      $this->Cell(50,5,'','LR',0,'C');
      $this->Cell(50,5,'','LR',1,'C');
      $this->Cell(40,5,'Adresse','LR',0,'L');
      $this->Cell(50,5,'','LR',0,'C');
      $this->Cell(50,5,'','LR',0,'C');
      $this->Cell(50,5,'','LR',1,'C');
      $this->Cell(40,5,'','LR',0,'L');
      $this->Cell(50,5,'','LR',0,'C');
      $this->Cell(50,5,'','LR',0,'C');
      $this->Cell(50,5,'','LR',1,'C');
      $this->Cell(40,5,'Club et Ligue','LBR',0,'L');
      $this->Cell(50,5,'','LBR',0,'C');
      $this->Cell(50,5,'','LBR',0,'C');
      $this->Cell(50,5,'','LBR',1,'C');


      $this->orientation = 'A';
      $this->AddPage('A');
      $this->SetTitle($titre);
      $this->SetAuthor('BadNet Team');
      $this->SetFillColor(255,255,255);
      $this->SetLineWidth(0.4);

      $joueurs = array(
		       1 =>  array('LEFORT','Jean-Michel','A-2','AUCB','PACA'),
		       2=>  array('MORA','Maxime','A-2','RCF','IDF'),
		       3=> 	array('BERNABE','Fabrice','A-2','IMBC','IDF'),
		       4=> 	array('KEHLHOFFNER','Erwin','A-2','ASLR','ALS'),
		       5=> 	array('LASMARI','Nabil','A-2','AGSCR','NPC'),
		       6=>	array('LENGAGNE','Sydney','A-2','AUCB','PACA'),
		       7=> 	array('DUCLOS','Antoine','A-2','BCG/BDC','BRE'),
		       8=> 	array('VISEUR','Sébastien','A-2','BBC','NPC'),
		       9 => array('ENGRAND','Xavier','A-2','LUC','NPC'),
		       10 => array('BERNIER','Cédric','A-2','IMBC','IDF'),
		       11 => array('BLONDEAU','Thomas','A-2','GSBA','NPC'),
		       12 => array('BOURBON','Sébastien','A-2','ALDM','NOR'),
		       13 => array('LO YING PING','Mathieu','A-2','USB','AQU'),
		       14 => array('TCHORYK','Julien','A-2','RCF','IDF'),
		       15 => array('LEVERDEZ','Brice','A-2','SSSM','IDF'),
		       16 => array('FOSSY','Olivier','A-1/','AUCB','PACA'),
		       17=> 	array('DUCLOS','Antoine','A-2','BCG/BDC','BRE'),
		       18=> 	array('VISEUR','Sébastien','A-2','BBC','NPC'),
		       19 => array('ENGRAND','Xavier','A-2','LUC','NPC'),
		       20 => array('BERNIER','Cédric','A-2','IMBC','IDF'),
		       21 => array('BLONDEAU','Thomas','A-2','GSBA','NPC'),
		       22 => array('BOURBON','Sébastien','A-2','ALDM','NOR'),
		       23 => array('LO YING PING','Mathieu','A-2','USB','AQU'),
		       24 => array('TCHORYK','Julien','A-2','RCF','IDF'),
		       25 => array('LEVERDEZ','Brice','A-2','SSSM','IDF'),
		       26 => array('FOSSY','Olivier','A-1/','AUCB','PACA'),
		       27=> 	array('DUCLOS','Antoine','A-2','BCG/BDC','BRE'),
		       28=> 	array('VISEUR','Sébastien','A-2','BBC','NPC'),
		       29 => array('ENGRAND','Xavier','A-2','LUC','NPC'),
		       30 => array('BERNIER','Cédric','A-2','IMBC','IDF'),
		       31 => array('BLONDEAU','Thomas','A-2','GSBA','NPC'),
		       32 => array('BOURBON','Sébastien','A-2','ALDM','NOR')
		       );

      $scores = array(
		      1 =>  '15/10 15/06',
		      2 =>  '15/05 15/12',
		      3 =>  '15/17 15/06 15/11',
		      4 =>  '15/03 15/04',
		      5 =>  '15/10 07/15 17/14',
		      6 =>  '15/08 15/11',
		      7 =>  '15/12 15/04',
		      8 =>  '15/13 15/13',
		      9 =>  '15/05 15/07',
		      10 =>  '15/10 15/06',
		      11 =>  '15/05 15/12',
		      12 =>  '15/17 15/06 15/11',
		      13 =>  '15/03 15/04',
		      14 =>  '15/10 07/15 17/14',
		      15 =>  '15/08 15/11',
		      16 =>  '15/08 15/11',
		      17 =>  '15/10 15/06',
		      18 =>  '15/05 15/12',
		      19 =>  '15/17 15/06 15/11',
		      20 =>  '15/03 15/04',
		      21 =>  '15/10 07/15 17/14',
		      22 =>  '15/08 15/11',
		      23 =>  '15/12 15/04',
		      24 =>  '15/13 15/13',
		      25 =>  '15/05 15/07',
		      26 =>  '15/10 15/06',
		      27 =>  '15/05 15/12',
		      28 =>  '15/17 15/06 15/11',
		      29 =>  '15/03 15/04',
		      30 =>  '15/10 07/15 17/14',
		      31 =>  '15/08 15/11',
		      32 =>  '15/08 15/11'
		      );

      $this->SetFont('Arial','',8);

      $taille = 5;
      for($i=0;$i<=23;$i++){
	for($j=1;$j<=$taille;$j++){
	  $pos = $i*$taille+$j;
	  $bord = $this->getBordure($pos,$taille);
	  $this->Cell(40,7,''    ,$bord ,0,'C',0);
	}
	$this->Ln();
      }

      $this->AddPage('A');

      $taille = 4;
      for($i=0;$i<=11;$i++){
	for($j=1;$j<=$taille;$j++){
	  $pos = $i*$taille+$j;
	  $bord = $this->getBordure($pos,$taille);
	  $this->Cell(40,7,''    ,$bord ,0,'C',0);
	}
	$this->Ln();
      }

      $this->AddPage('A');

      $taille = 6;
      for($i=0;$i<=47;$i++){
	for($j=1;$j<=$taille;$j++){
	  $pos = $i*$taille+$j;
	  $bord = $this->getBordure($pos,$taille);
	  $this->Cell(40,3,''    ,$bord ,0,'C',0);
	}
	$this->Ln();
      }


      //		$this->AddPage('A');
      $this->AddPage('A');
      $hauteur_ligne = 10;
      $xpos = 10;
      $ypos = 0;

      //Cell( w,  h,  txt, border, ln, align, fill, link)
      for($i=1;$i<17;$i++){
	$this->SetXY($xpos,$i*$hauteur_ligne);
	$this->SetFont('Arial','B',12);
	$tds = $this->getTeteSerie(16,$i);
	$this->Cell(15,$hauteur_ligne,''.$tds    ,1 ,0,'C',0);
	$this->SetFont('Arial','',8);
	$this->Cell(10,$hauteur_ligne,''.$i    ,1 ,0,'C',0);
	$this->SetFont('Arial','',10);
	$this->Cell(50,$hauteur_ligne,''.$joueurs[$i][0].' '.$joueurs[$i][1]    ,1 ,0,'C',0);
	$this->Cell(15,$hauteur_ligne,''.$joueurs[$i][2]    ,1 ,0,'C',0);
	$this->Cell(20,$hauteur_ligne,''.$joueurs[$i][3]    ,1 ,0,'C',0);
	$this->Cell(15,$hauteur_ligne,''.$joueurs[$i][4]    ,1 ,0,'C',0);
      }
      //	function tour($nb,$taille,$largeur,$xpos,$ypos,$joueurs,$score){
      $this->AddPage('P');
      //		$this->tour(32, 5,30,40,10,$joueurs,$scores);
      $this->tour(16, 11,30,50,10,$joueurs,$scores);
      $this->tour(8, 22,30,80,10,$joueurs,$scores);
      $this->tour(4, 44,30,110,10,$joueurs,$scores);
      $this->tour(2, 88,30,140,10,$joueurs,$scores);
      $this->tour(1,176,30,170,10,$joueurs,$scores);



      $this->Output();
      exit;
    }


  // {{{ _getLabel()
  /**
   * return the label of a standard information
   *
   * @access public
   * @param  integer $numLabel  Position of the label
   * @return void
   */
  function _getLabel($labelId)
    {
      $file = "lang/".utvars::getLanguage().
	"/pdf.inc";

      if (is_file($file))
	require ($file);
      else
	$this->addErr("pdf->_getLabel : fichier introuvable :$file");

      if (isset(${$labelId}))
	return "${$labelId}";
      else
	return $labelId;
    }
  // }}}


  // {{{ genere_liste($titres, $tailles, $liste, $styles='')
  /**
   * Genere une liste de données
   *
   * @param  array  $titres    Titres des colonnes
   * @param  array  $tailles   Taille des colonnes
   * @param  array  $liste     Tableau de valeurs
   * @param  array  $style     Style des colonnes : gras B, italic I, souligne U
   *
   * @return void -201 si erreur
   */
  function genere_liste($titres, $tailles, $liste, $styles='')
    {
      $file = "lang/".utvars::getLanguage()."/pdf.inc";
      require_once $file;

      $ut = new utils();

      $ute = new utevent();
      $eventId = utvars::getEventId();
      $event = $ute->getEvent($eventId);

      $pageTitre = false;
      if (isset($liste['titre']))
	{
	  $pageTitre = $liste['titre'];
	  unset($liste['titre']);
	}
      if (isset($liste['orientation']))
	{
	  $orientation= $liste['orientation'];
	  unset($liste['orientation']);
	}
      else
	$orientation= 'P';

      $titres  = array_values($titres) ;
      $tailles = array_values($tailles) ;
      $liste   = array_values($liste)  ;
      $styles   = array_values($styles)  ;

      $this->orientation = $orientation;
      $this->AddPage($orientation);
	
      $this->SetFont('Arial','',8);
      $this->SetFillColor(255,255,255);
      $this->SetLineWidth(0.4);

      $hauteur_ligne = 5 ;
      $this->SetXY(10,50);

      // Verifie que la taille des tableaux $titre, $taille
      // et $liste est la même
      if(count($titres)!=count($tailles) and
	 (count($titres!=count($liste[0])))){
        echo "erreur de taille de tableaux ".count($titres).
	  " / ".count($tailles)." / ".count($liste[0]);
        return -201;
      }

      // Affiche le titre
      if ($pageTitre != false)
	  {
	    $this->SetFont('Arial','',18);
	    $this->Cell(0,2*$hauteur_ligne,$pageTitre, 0, 0,'L',1,'');
	    $this->Ln();
	    $this->Ln();
	  }


      // Genere l'entete
      $compteur = 1;
      $this->SetFont('Arial','',8);
      $this->SetFillColor(200,200,200);
      $this->Cell(8,$hauteur_ligne,'', 1, 0,'L',1,'');
      $taille = reset($tailles);
      $style = reset($styles);
      foreach($titres as $elt){
        $this->Cell($taille,$hauteur_ligne,$elt, 1, 0,'L',1,'');
        $taille = next($tailles);
	//Cell( w,  h,  txt, border, ln, align, fill, link)

      }
      $this->Ln();
      $this->SetFont('Arial','',10);

      // Genere la liste
      foreach($liste as $ligne)
	{
	  $border = 1;
	  if (isset($ligne['border']))
	    {
	      $border = $ligne['border'];
	      unset($ligne['border']);
	    }
	  $this->SetFont('Arial','',8);
	  if (($border==1) || ereg('T', $border))
	    $this->Cell(8,$hauteur_ligne,$compteur++, $border, 0,'L',1,'');
	  else
	    $this->Cell(8,$hauteur_ligne,'', $border, 0,'L',1,'');

	  $taille = reset($tailles);
	  $style = reset($styles);
	  foreach($ligne as $elt)
	    {
	      $this->SetFont('Arial',$style,10);
	      $this->Cell($taille,$hauteur_ligne,$elt, $border, 0,'L',0,'');
	      $taille = next($tailles);
	      $style = next($styles);
	      //Cell( w,  h,  txt, border, ln, align, fill, link)
	    }
	  $this->Ln();
	}
      $this->End();
      exit;
    }


  // {{{ activite()
  /**
   * Display the activity forms of umpire
   *
   * @return void
   */
  function activite($acts)
    {
      $this->AliasNbPages();

      // Création du document PDF
      $titre='Badnet';
      $this->SetFont('Arial','B',12);
      $this->SetFillColor(255,255,255);

      $this->orientation = 'P';
      $this->SetTitle($titre);
      $this->SetAuthor('BadNet Team');
      foreach($acts as $act)
	{
	  //print_r($act);
	  $this->_activite($act);
	}
      $this->Output();
    }
  //}}}



  // {{{ _activite()
  /**
   * Display the activity forms of umpire
   *
   * @return void
   */
  function _activite($matches)
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
      $this->Cell(0, 15, $match['regi_longName'], 0, 0, 'L', 0);
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
	      if ($top >240) $top=70;
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
	  if ($top >260) $top=70;
	  $this->setY($top);

	}
      $this->Ln(3);
    }
  //}}}

}
?>
