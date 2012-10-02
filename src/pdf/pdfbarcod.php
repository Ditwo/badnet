<?php
/*****************************************************************************
!   Module     : pdf
!   File       : $File$
!   Version    : $Name:  $
!   Revision   : $Revision: 1.1 $
!   Author     : D.BEUVELOT
!   Revised by : $Author: cage $
!   Date       : $Date: 2006/02/11 15:43:46 $
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
require_once "utils/utbarcod.php";

class pdfBarCod extends pdfBase
{

  // {{{ affichage_list_items()
  /**
   * Display the group of the division with the schedule
   *
   * @return void
   */
  function list_items()
    {
      $file = "lang/".utvars::getLanguage()."/pdf.inc";
      require $file;

      $utfpdf =  new baseFpdf();
      $utcode =  new utBarCod();

      $ute = new utevent();
      $eventId = utvars::getEventId();
      $event = $ute->getEvent($eventId);

      $items = $utfpdf->getItems();
      $this->_isHeader = false;
      $this->_isFooter = false;
      $this->orientation = 'P';
      $this->AddPage('P');

      $this->SetFont('Arial','',14);
      $this->SetFillColor(255,255,255);
      $this->SetLineWidth(0.4);

      $xpos = 102;
      $hauteur_ligne = 15 ;
      $ypos = $hauteur_ligne + 11;
      $offset = 0;

      $this->Cell(0,$hauteur_ligne,''.$event['evnt_name']    ,1 ,1,'C',0);
      $offset = 0;
      foreach($items as $id => $infos)
	{
	  $xpos = 105 + 55*$offset;
	  $offset = 1-$offset;
	  //Cell( w,  h,  txt, border, ln, align, fill, link)
	  $this->SetFont('Arial','',14);
	  //$this->Cell(10,$hauteur_ligne,''.$infos['item_id']      ,1 ,0,'L',0);
	  $this->Cell(60,$hauteur_ligne,''.$infos['item_name']    ,1 ,0,'L',0);
	  $this->Cell(30,$hauteur_ligne,''.number_format($infos['item_value'],2,',',' ').' ',1 ,0,'L',0);
	  $this->Cell(0,$hauteur_ligne ,'' ,1   ,1,'L',0);
	  $code = $utcode->getCodItem($infos['item_id']);
	  //$this->Code39($xpos,$ypos,$code,.75,8);
	  //$this->SetFont('Arial','',10);
	  $this->SetFillColor(0);
	  $this->EAN13($xpos,$ypos,$code, 8);
	  $ypos +=  $hauteur_ligne;
	  if($ypos>260)
	    //	$this->Cell(0,$hauteur_ligne,''.$event['evnt_name']    ,1 ,1,'C',0);
	    $ypos=11;
	}
      return;

    }
  // }}}

  // {{{ special_codes()
  /**
   * Display the list of specials barcode
   *
   * @return void
   */
  function special_codes()
    {
      $file = "lang/".utvars::getLanguage()."/pdf.inc";
      require_once $file;

      $utfpdf =  new baseFpdf();
      $utcode =  new utBarCod();

      $ute = new utevent();
      $eventId = utvars::getEventId();
      $event = $ute->getEvent($eventId);

      $codes = $utcode->getSpecialCode();
      $this->_isHeader = false;
      $this->_isFooter = false;

      $this->orientation = 'P';
      $this->AddPage('P');

      $this->SetFont('Arial','',14);
      $this->SetFillColor(255,255,255);
      $this->SetLineWidth(0.4);

      $xpos = 102;
      $hauteur_ligne = 29 ;
      $ypos = $hauteur_ligne + 11;
      $offset = 0;

      $this->Cell(0,$hauteur_ligne,''.$event['evnt_name']    ,1 ,1,'C',0);
      $offset = 0;
      foreach($codes as $code)
	{
	  $xpos = 105 + 55*$offset;
	  $offset = 1-$offset;
	  //Cell( w,  h,  txt, border, ln, align, fill, link)
	  $this->SetFont('Arial','',14);

	  $this->Cell(60,$hauteur_ligne,''.$code['name']    ,1 ,0,'L',0);
	  $this->Cell(30,$hauteur_ligne,'',1 ,0,'L',0);
	  $this->Cell(0,$hauteur_ligne ,'' ,1   ,1,'L',0);
	  $this->SetFillColor(0);
	  $this->EAN13($xpos,$ypos,$code['barcod']);
	  //$this->write1DBarcode($code, 'EAN13', $xpos, $ypos);
	  $ypos +=  $hauteur_ligne;
	  if($ypos>260)
	    $ypos=11;
	}

      return;
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


}
?>