<?php
/*****************************************************************************
 !   Module     : pdf
 !   File       : $File$
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.5 $
 !   Author     : D.BEUVELOT
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2006/07/18 21:57:40 $
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
if (!defined('FPDF_FONTPATH'))
define('FPDF_FONTPATH',dirname(__FILE__).'/../fpdf/font/');
 
/*
 require_once "fpdf/fpdf.php";
 require_once "fpdf/rotation/rotation.php";
 require_once "utils/utbarcod.php";
 */

require_once "Tcpdf/tcpdf.php";
require_once "pdfbarcod.php";
 
require_once "base.php";

//class badgesPdf extends pdfbarcod //PDF_Rotate
class badgesPdf extends tcpdf
{
	var $orientation="L";

	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @param string $name      Name of button
	 * @param string $attribs   Attribs of the button
	 * @access public
	 * @return void
	 */
	function badgesPdf()
	{
		parent::__construct();
		 
		$ute = new utevent();
		$eventId = utvars::getEventId();
		$meta = $ute->getMetaEvent($eventId);
		$badge = $ute->getBadgeDef($meta['evmt_badgeId']);

		if (isset($badge['elts']))
		{
	  $elts = $badge['elts'];
	  $fonts = array();
	  foreach($elts as $elt)
	  {
	  	if(is_file("./fpdf/font/{$elt['eltb_font']}.z"))
	  	{
	  		$fonts[$elt['eltb_font']] = $elt['eltb_font'];
	  		$style[$elt['eltb_font']] = '';
	  		if($elt['eltb_bold']) $style[$elt['eltb_font']] .= 'B';
	  		if($elt['eltb_italic']) $style[$elt['eltb_font']] .= 'I';
	  	}
	  }
	  foreach($fonts as $font)
	  {
	  	$this->AddFont($font, $style[$font]);
	  }
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
		//Rotation de l'image autour du coin sup�rieur gauche
		$this->Rotate($angle,$x,$y);
		$this->Image($file,$x,$y,$w,$h);
		$this->Rotate(0);
	}

	// {{{ badges()
	/**
	 * Display the badge of a register member
	 *
	 * @return void
	 */
	function badges($ids, $aType=WBS_PLAYER)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require_once $file;

		$utfpdf =  new baseFpdf();
		$utcode =  new utBarCod();
		$ute = new utevent();

		$this->_isFooter = false;
		$this->_isHeader = false;
		$this->setPrintHeader(false);
		$this->setPrintFooter(false);
		
		$eventId = utvars::getEventId();
		$event = $ute->getEvent($eventId);
		$meta = $ute->getMetaEvent($eventId);
		$badge = $ute->getBadgeDef($meta['evmt_badgeId']);
		if (isset($badge['err_msg']))
		return $badge;

		$players = $utfpdf->getMembers($eventId, $ids, $aType);

		$titre='Badnet Division';
		 

		$this->orientation = 'P';
		$this->SetMargins(0,0);
		$this->SetAutoPageBreak(false);
		$this->AddPage('P');
		$this->SetTitle($titre);
		$this->SetAuthor('BadNet Team');

		$this->SetFillColor(255,255,255);
		$this->SetLineWidth(0.4);

		// Espace dispo
		$width = 210;
		$height = 297;

		$top = $badge['bdge_topmargin'];
		$left = $badge['bdge_leftmargin'];

		$data[WBS_FIELD_TITLE] = $event['evnt_name'];
		if ($meta['evmt_badgeLogo'] == '')
		$eventLogo = utimg::getPathPoster($event['evnt_poster']);
		else
		$eventLogo = utimg::getPathPoster($meta['evmt_badgeLogo']);
		$data[WBS_FIELD_LOGO] = $eventLogo;
		$data[WBS_FIELD_SPONSOR] = utimg::getPathPoster($meta['evmt_badgeSpon']);

		$ut = new utils();
		$regiType = -1;
		foreach($players as $id => $infos)
		{
			if ($regiType != $infos['regi_type'])
			{
				$zones = $utfpdf->getZones($infos['regi_type']);
				$regiType = $infos['regi_type'];
			}
	  $data[WBS_FIELD_NAME]       = $infos['regi_longName'];
	  $data[WBS_FIELD_FIRSTNAME]  = $infos['mber_firstname'];
	  $data[WBS_FIELD_SECONDNAME] = $infos['mber_secondname'];
	  $data[WBS_FIELD_TEAMNAME]   = $infos['team_name'];
	  $data[WBS_FIELD_TEAMPSEUDO] = $infos['asso_pseudo'];
	   
	  if ($infos['mber_urlphoto'] != "")
	  $data[WBS_FIELD_PHOTO] = utimg::getPathPhoto($infos['mber_urlphoto']);
	  else if ($infos['team_logo'] != "")
	  $data[WBS_FIELD_PHOTO] = utimg::getPathTeamLogo($infos['team_logo']);
	  else
	  $data[WBS_FIELD_PHOTO] = utimg::getPathFlag($infos['asso_logo']);

	  if ($infos['team_logo'] != "")
	  $data[WBS_FIELD_TEAMLOGO] = utimg::getPathTeamLogo($infos['team_logo']);
	  else
	  $data[WBS_FIELD_TEAMLOGO] = utimg::getPathFlag($infos['asso_logo']);
	  //print_r($data);
	  $data[WBS_FIELD_BARCODE] = $infos['regi_id'];
	  	
	  if ($infos['regi_function']==='')
	  $data[WBS_FIELD_STATUS] = $ut->getLabel($infos['regi_type']);
	  else
	  $data[WBS_FIELD_STATUS] = $infos['regi_function'];

	  if ($infos['regi_noc'] != '')
	  $data[WBS_FIELD_NOC] = $infos['regi_noc'];
	  else if ($infos['team_noc'] != '')
	  $data[WBS_FIELD_NOC] = $infos['team_noc'];
	  else
	  $data[WBS_FIELD_NOC] = $infos['asso_noc'];

	   
	  switch($infos['regi_type']){
	  	
	  	case WBS_PLAYER  : $cr = 0x07; $cv = 0xb0; $cb = 0xff;break;
	  	case WBS_REFEREE : $cr = 0xc1; $cv = 0x00; $cb = 0x33;break;
	  	case WBS_DEPUTY  : $cr = 0xc1; $cv = 0x00; $cb = 0x33;break;
	  	case WBS_UMPIRE  : $cr = 0x00; $cv = 0x70; $cb = 0x0b;break;
	  	case WBS_LINEJUDGE  : $cr = 0x00; $cv = 0xff; $cb = 0x19;break;
	  	case WBS_DELEGUE    : $cr = 0x00; $cv = 0xff; $cb = 0x19;break;
	  	case WBS_CONSEILLER : $cr = 0x00; $cv = 0xff; $cb = 0x19;break;
	  	case WBS_COACH   :  $cr = 0x00; $cv = 0x33; $cb = 0xff;break;
	  	case WBS_VOLUNTEER :  $cr = 0xff; $cv = 0xff; $cb = 0x00;break;
	  	case WBS_ORGANISATION :  $cr = 0xff; $cv = 0x00; $cb = 0x00;break;
	  	case WBS_VIP       :  $cr = 0xf2; $cv = 0x07; $cb = 0xff;break;
	  	case WBS_PRESS     :  $cr = 0xf9; $cv = 0x87; $cb = 0x04;;break;
	  	case WBS_GUEST     :  $cr = 0x00; $cv = 0x00; $cb = 0x00;break;
	  	case WBS_MEDICAL   :  $cr = 0xf9; $cv = 0x61; $cb = 0x87;break;
	  	case WBS_EXHIBITOR :  $cr = 0xc0; $cv = 0xc0; $cb = 0xc0;break;
	  	default : $cr = 255; $cv = 255; $cb = 255;
		
	  	/*
	  	 * 
	  	 * France jeune 2009
	  	 
	  	//jaune
	  	case WBS_PLAYER  : $cr = 0xff; $cv = 0xff; $cb = 0x00;break;
	  	case WBS_COACH   :  $cr = 0xff; $cv = 0xff; $cb = 0x00;break;
	  	// Vert
	  	case WBS_REFEREE : $cr = 0x19; $cv = 0xdb; $cb = 0x1d;break;
	  	case WBS_DEPUTY  : $cr = 0x19; $cv = 0xdb; $cb = 0x1d;break;
	  	case WBS_UMPIRE  : $cr = 0x19; $cv = 0xdb; $cb = 0x1d;break;
	  	case WBS_LINEJUDGE  : $cr = 0x19; $cv = 0xdb; $cb = 0x1d;break;
	  	case WBS_DELEGUE    : $cr = 0x19; $cv = 0xdb; $cb = 0x1d;break;
	  	case WBS_CONSEILLER : $cr = 0x19; $cv = 0xdb; $cb = 0x1d;break;

	  	// rouge
	  	case WBS_ORGANISATION :  $cr = 0xff; $cv = 0x00; $cb = 0x00;break;
	  	case WBS_MEDICAL   :  $cr = 0xff; $cv = 0x00; $cb = 0x00;break;
	  	
	  	// Violet
	  	case WBS_VOLUNTEER :  $cr = 0xe7; $cv = 0x69; $cb = 0xcb;break;
	  	
	  	// bleu
	  	case WBS_PRESS     :  $cr = 0x3d; $cv = 0x7d; $cb = 0xff;break;
	  	
	  	// blanc
	  	case WBS_PLATEAU   :  $cr = 0xff; $cv = 0xff; $cb = 0xff;break;
	  	
	  	case WBS_EXHIBITOR :  $cr = 0xed; $cv = 0Xf4; $cb = 0xe9;break;
	  	case WBS_VIP       :  $cr = 0xed; $cv = 0Xf4; $cb = 0xe9;break;
	  	case WBS_GUEST     :  $cr = 0xed; $cv = 0Xf4; $cb = 0xe9;break;
	  	case WBS_SECURITE  :  $cr = 0xed; $cv = 0Xf4; $cb = 0xe9;break;
	  	case WBS_OTHERB    :  $cr = 0xed; $cv = 0Xf4; $cb = 0xe9;break;
		*/
	  	default : $cr = 0xed; $cv = 0Xf4; $cb = 0xe9;
	  }
	  $data['cr'] = $cr;
	  $data['cv'] = $cv;
	  $data['cb'] = $cb;

	  //print_r($data);
	  $this->_badge($top, $left, $badge, $data, $zones);

	  // Calcul des positions pour le prochain badge
	  $left += $badge['bdge_width']+$badge['bdge_deltawidth'];
	  if($left+$badge['bdge_width'] > $width)
	  {
	  	$left = $badge['bdge_leftmargin'];
	  	$top += $badge['bdge_height']+$badge['bdge_deltaheight'];
	  	if($top+$badge['bdge_height'] > $height)
	  	{
	  		$this->orientation = 'P';
	  		$this->AddPage('P');
	  		$top = $badge['bdge_topmargin'];
	  	}
	  }

		}

		//$this->Output();
		$this->end();
		exit;
	}
	// }}}

	function end($display=true)
	{
		// Dossier des fichiers temporaires
		$dir = getcwd()."/../pdf";

		// Purge des fichiers pdf trop ancien
		$t = time();
		$h = opendir($dir);
		while($file=readdir($h))
		{
	  $path = $dir.'/'.$file;
	  if($t-filemtime($path)>1800)
	  @unlink($path);
		}
		closedir($h);

		// Nom du fichier temporaire
		$file = tempnam($dir, 'badnet');
		rename($file, $file.'.pdf');
		$file .= '.pdf';
		chmod($file, 0777);

		// Generer le pdf dans le fichier
		$this->Output($file);

		// Afficher le pdf
		if ($display)
		{
	  $file = basename($file);
	  $url = dirname($_SERVER['PHP_SELF'])."/../pdf/$file";
	  header("Location: $url");
	  exit;
		}
		else
		return $file;

	}


	// {{{ _badge()
	/**
	 * Display one badge of a register member
	 *
	 * @return void
	 */
	function _badge($top, $left, $def, $data, $zones)
	{
		// Cadre
		if ($def['bdge_border'])
		{
	  $this->SetLineWidth($def['bdge_borderSize']);
	  $this->SetDrawColor(0,0,0);
	  $this->rect($left, $top, $def['bdge_width'], $def['bdge_height']);
		}

		$elts =& $def['elts'];
		foreach($elts as $elt)
		{
	  switch ($elt['eltb_field'])
	  {
	  	case WBS_FIELD_ZONE :
	  		if ( in_array($elt['eltb_zoneId'], $zones) )
	  		{
	  			$this->_printTexte($top, $left, $elt, '');
	  		}
	  		break;
	  	case WBS_FIELD_TITLE:
	  	case WBS_FIELD_NAME:
	  	case WBS_FIELD_FIRSTNAME:
	  	case WBS_FIELD_SECONDNAME:
	  	case WBS_FIELD_TEAMNAME:
	  	case WBS_FIELD_STATUS:
	  	case WBS_FIELD_NOC:
	  	case WBS_FIELD_TEAMPSEUDO:
	  		$this->_printTexte($top, $left, $elt, $data[$elt['eltb_field']]);
	  		break;
	  	case WBS_FIELD_LOGO :
	  	case WBS_FIELD_TEAMLOGO :
	  	case WBS_FIELD_PHOTO :
	  	case WBS_FIELD_SPONSOR :
	  		$this->_printImg($top, $left, $elt, $data[$elt['eltb_field']]);
	  		break;
	  	case WBS_FIELD_BAND :
	  		$this->_printBand($top, $left, $elt, $data);
	  		break;
	  	case WBS_FIELD_BARCODE :
	  		$this->_printBarCode($top, $left, $elt, $data[$elt['eltb_field']]);
	  		break;
	  	case WBS_FIELD_FIXED:
	  		//echo "$top,$left,{$elt['eltb_value']}<br>";
	  		$this->_printTexte($top, $left, $elt, $elt['eltb_value']);
	  		break;
	  }
		}
	}
	// }}}

	// {{{ _printTexte()
	/**
	 * Display a string into the badge
	 *
	 * @return void
	 */
	function _printTexte($top, $left, $def, $data)
	{
		$attrib = '';
		list($r,$v,$b) = explode(';', $def['eltb_textcolor']);
		$this->setTextColor($r, $v, $b);

		list($r,$v,$b) = explode(';', $def['eltb_fillcolor']);
		$this->setFillColor($r, $v, $b);

		$this->SetFont($def['eltb_font'], $attrib, $def['eltb_size']);
		$xpos = $left + $def['eltb_left'];
		$ypos = $top + $def['eltb_top'];
		//echo "    --> $xpos,$ypos<br>";
		$this->SetXY($xpos,$ypos);
		$background = 1;
		//if ($def['eltb_width'] == 0) $background = 0;
		$this->MultiCell($def['eltb_width'], $def['eltb_height'],
		utf8_encode($data), $def['eltb_border'], $def['eltb_align'], $background);
	}
	// }}}


	// {{{ _printImg()
	/**
	 * Display an image into the badge
	 *
	 * @return void
	 */
	function _printImg($top, $left, $def, $data)
	{

		// Logo du tournoi
		if (is_file($data) &&
		(strpos($data,".gif") === false))
		{
	  $size = @getImagesize($data);
	  $few = $def['eltb_width']/$size[0];
	  $feh = $def['eltb_height']/$size[1];
	  $fe = min(1, $feh, $few);
	  $width = $size[0] * $fe;
	  $height = $size[1] * $fe;

	  $xpos = $left + $def['eltb_left'] +
	  ($def['eltb_width']-$width)/2;
	  $ypos = $top + $def['eltb_top'] +
	  ($def['eltb_height']-$height)/2;
	  $this->Image($data, $xpos, $ypos, $width, $height);
		}

	}
	// }}}


	// {{{ _printBarCode()
	/**
	 * Display a barcoade into the badge
	 *
	 * @return void
	 */
	function _printBarCode($top, $left, $def, $data)
	{

		$utcode =  new utBarCod();
		$code = $utcode->getCodRegi($data);
		$this->SetFont('','',10);
		$this->SetFillColor(0);
		$xpos = $left + $def['eltb_left'];
		$ypos = $top + $def['eltb_top'];
		$this->write1DBarcode($code, 'EAN13', $xpos, $ypos);
	}
	// }}}


	// {{{ _printBand()
	/**
	 * Display a color band  into the badge
	 *
	 * @return void
	 */
	function _printBand($top, $left, $def, $data)
	{
		$this->setalpha(0.80);
		$cr = $data['cr'];
		$cv = $data['cv'];
		$cb = $data['cb'];
		$this->SetDrawColor($cr,$cv,$cb);
		$this->SetFillColor($cr,$cv,$cb);
		$xpos = $left + $def['eltb_left'];
		$ypos = $top + $def['eltb_top'];
		$this->rect($xpos, $ypos, $def['eltb_width'], $def['eltb_height'], 'F');
		$this->setalpha(1);
	}
	// }}}
}
?>