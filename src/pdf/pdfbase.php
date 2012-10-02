<?php
/*****************************************************************************
 !   Module     : pdf
 !   File       : $File$
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.12 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/06 07:59:33 $
 ******************************************************************************/

//require_once "fpdf/fpdf.php";
require_once "fpdf/rotation/rotation.php";
//require_once "fpdf_js.php";

define("MARGIN_TOP",     10);
define("MARGIN_BOTTOM",  10);
define("MARGIN_LEFT",    10);
define("MARGIN_RIGHT",   10);

class PDFBase extends PDF_Rotate
//class PDFBase extends PDF_Javascript
//class PDFBase extends FPDF
{
	var $orientation="L";
	var $_isFooter = true;
	var $_isHeader = true;
	var $_page     = 0;
	var $_title    = '';

	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @param string $name      Name of button
	 * @param string $attribs   Attribs of the button
	 * @access public
	 * @return void
	 */
	function pdfBase()
	{
		parent::pdf_rotate();
		//parent::fpdf();
		//parent::PDF_Javascript();

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
		{
			$this->AddFont($font, $style[$font]);
		}
	}

	// }}}


	function Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='')
	{
		if ($w>0)
		{
			$width = $this->GetStringWidth($txt);
			while($width>$w)
			{
				$txt = substr($txt, 0, strlen($txt)-1);
				$width = $this->GetStringWidth($txt);
			}
		}
		parent::cell($w,$h,$txt,$border,$ln,$align,$fill,$link);
	}


	// {{{ Header()
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
	  $maxWidth -= MARGIN_RIGHT;

	  $eventId = utvars::getEventId();
	  $event = $ute->getEvent($eventId);
	  $meta = $ute->getMetaEvent($eventId);
	  //$this->SetLineWidth(0.5);
	  //$this->SetDrawColor(128);

	  // Affichage du titre du tournoi
	 	$this->SetFont($meta['evmt_titleFont'], '',$meta['evmt_titleSize']);
	 	//$this->SetFont('helvetica', 'B', 16);
	 	$this->SetFillColor(255);
	 	$this->SetTextColor(0);
	 	$str = $event['evnt_name'];
	 	$this->Cell(0, 8, $str, 0, 0, 'L');
	 	$this->SetFont('helvetica', '', 14);
	 	$this->Ln(11);
	 	$str = $event['evnt_place'];
	 	if (!empty($event['evnt_date'])) $str .= ' - ' . $event['evnt_date'];
	 	$this->Cell(0, 0, $str, '', 1, 'L');
	 	$this->Ln(10);

	  // Calcul de la position du logo
	  $logo = utimg::getPathPoster($meta['evmt_logo']);
	  $width = 0; $height = 0;
	  $top = $meta['evmt_top'];
	  $left = $meta['evmt_left'];
	  $size = @getImagesize($logo);

	  if ($size AND (strpos($logo,"gif") === FALSE))
	  {
	  	$few = $meta['evmt_width'] / $size[0];
	  	//$feh = 21 / $size[1];
	  	$feh = $meta['evmt_height'] / $size[1];
	  	$fe = min(1, $feh, $few);
	  	$fe = min(1, $feh);
	  	$width = $size[0] * $fe;
	  	$height = $size[1] * $fe;
	  	if ($top<0)
	  	{
	  		$H = ($this->orientation=='L') ? 210:297;
	  		$top = $h + $top - $height;
	  	}
	  	if ($left<0)
	  	{
	  		$W = ($this->orientation=='L') ? 297:210;
	  		$left = $W + $left - $width;
	  	}
	  	$this->setXY(50,25);
	  }
	  
	  // Affichage du logo de gauche
	  if ($width && $height)
	  $this->Image($logo, $left, $top, $width, $height);

	  // Ligne de separation
	  //$this->SetLineWidth(0.7);
	  //$this->SetDrawColor(56,168,92);
	  //$this->Line(MARGIN_LEFT, 42, $maxWidth, 42);

	  // Se positionner apres la ligne
	  //$this->SetY(25);
		}
	}
	// }}}

	// {{{ Footer()
	/**
	 * Footer des documents PDF
	 * En fonction de l'orientation L ou P, on affiche la date de
	 * g�n�ration, le logo FPDF, le lien B@dNet et le num�ro de page
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

	  $this->SetXY($this->_getLeft(), $this->_getBottom()-1);
	  $this->SetFont('Arial','B',6);
	  $page = $this->PageNo();
	  $this->Cell(0, 3, "{$this->_title} -{$page}-", 0, 0, 'R', 0);

	  // Ligne de separation
	  $this->SetLineWidth(0.7);
	  $this->SetDrawColor(56,168,92);
	  $this->Line($this->_getLeft(), $maxHeight-8,
	  $this->_getRight(), $maxHeight-8);

	  //Texte techno
	  $this->SetTextColor(0);
	  $this->SetFont('arial', '', 6);
	  $this->SetXY(MARGIN_LEFT, $maxHeight-6);
	  $this->Cell(0, 0, "A technologie developped by", '', 0, 'L');

	  // date de g�n�ration du document
	  $genere = date("d/m/Y H:i:s");
	  $label = $this->_getLabel('generate');
	  $this->SetXY($maxWidth-60, $maxHeight-6);
	  $this->Cell(60, 0, "$label $genere", '', 0, 'R');

	  // Logo
	  $ut = new utils();
	  $logo = $ut->getParam('logofooter', 'badnetfull.jpg');
	  $logo = utimg::getLogo($logo);
	  if(is_file($logo))
	  {
	  	$size = @getImagesize($logo);
	  	$width  = $size[0];
	  	$height = $size[1];
	  	$few = ($maxWidth-120) / $size[0];
	  	$feh = 10 / $size[1];
	  	$fe = min(1, $feh, $few);
	  	$width  = intval($size[0] * $fe);
	  	$height = intval($size[1] * $fe);
	  	$this->Image($logo, ($maxWidth-$width)/2, $maxHeight-6, $width, $height, '');
	  }
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

	// {{{ _getTop()
	/**
	 * return the top position
	 *
	 * @return void
	 */
	function _getTop()
	{
		if ($this->_isHeader) $top = 25;
		else 	$top = 10;
		return $top;
	}
	// }}}

	// {{{ _getBottom()
	/**
	 * return the top position
	 *
	 * @return void
	 */
	function _getBottom()
	{
		$maxHeight = ($this->orientation=='L') ? 210:297;
		if ($this->_isFooter) return $maxHeight-20;
		else return $maxHeight-10;
	}
	// }}}

	// {{{ _getLeft()
	/**
	 * return the top position
	 *
	 * @return void
	 */
	function _getLeft()
	{
		return MARGIN_LEFT;
	}
	// }}}

	// {{{ _getRight()
	/**
	 * return the top position
	 *
	 * @return void
	 */
	function _getRight()
	{
		$maxWidth = ($this->orientation=='L') ? 297:210;
		return $maxWidth-MARGIN_RIGHT;
	}
	// }}}

	// {{{ _getAvailableHeight()
	/**
	 * return the availlable heigth
	 *
	 * @return void
	 */
	function _getAvailableHeight()
	{
		return $this->_getBottom() - $this->_getTop();
	}
	// }}}

	// {{{ _getAvailableWidth()
	/**
	 * return the availlable width
	 *
	 * @return void
	 */
	function _getAvailableWidth()
	{
		return $this->_getRight() - $this->_getLeft();
	}
	// }}}

	// {{{ printTitle()
	/**
	 * Add a page
	 *
	 * @return void
	 */
	function printTitle($title)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require $file;
		$top = $this->_getTop();
		// Affichage du titre du document
		$this->SetXY($this->_getLeft(), $top);
		$this->SetFont('arial', 'B', 12);
		if(isset($$title)) $title = $$title;
		$this->Cell(0, 20,  $title, 0 ,1 ,'L');
		$this->_title = $title;
		return $top + 14;
	}
	// }}}

	// {{{
	/**
	 * Fix the title of the document
	 *
	 * @return void
	 */
	function setDocTitle($title)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require $file;
		if(isset($$title))
		$this->_title = $$title;
		else
		$this->_title = $title;
	}
	// }}}

	// {{{ start()
	/**
	 * Display the group of the division with the schedule
	 *
	 * @return void
	 */
	function start($orientation = null, $title=null)
	{
		// Cr�ation du document
		$titre='Badnet Division';
		$this->SetTitle($titre);
		$this->SetFillColor(255,255,255);
		$this->SetAuthor('BadNet Team');
		$this->AliasNbPages();
		if (!is_null($orientation))
		{
	  $this->orientation = $orientation;
	  $this->AddPage($orientation);
		}
		//$this->SetAutoPageBreak(false);
		$top = $this->_getTop();
		if (!is_null($title))
		{
	  $top = $this->printTitle($title);
		}
		return $top;
	}
	// }}}

	// {{{ end()
	/**
	 * Display the group of the division with the schedule
	 *
	 * @return void
	 */
	function end($display=true, $aDirectPrint=false)
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
		if ($aDirectPrint){
			$script="print(true);";
			$this->IncludeJS($script);
			$this->Output();
		}
		else $this->Output($file);

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
	// }}}


	// {{{ genere_liste($titres, $tailles, $liste, $styles='')
	/**
	 * Genere une liste de donn�es
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
		require $file;

		$tableTitre = false;
		if (isset($liste['titre']))
		{
	  if (isset(${$liste['titre']}))
	  $tableTitre = ${$liste['titre']};
	  else
	  $tableTitre = $liste['titre'];
	  unset($liste['titre']);
		}
		$orientation= 'P';
		if (isset($liste['orientation']))
		{
	  $orientation= $liste['orientation'];
	  unset($liste['orientation']);
		}

		$msg = '';
		if (isset($liste['msg']))
		{
	  $msg = $liste['msg'];
	  unset($liste['msg']);
		}

		$this->orientation = $orientation;
		if (isset($liste['newPage']) and $liste['newPage'] === true) $this->AddPage($orientation);
		unset($liste['newPage']);

		$this->SetFont('helvetica', '', 7);
		$this->SetFillColor(255,255,255);
		$this->SetLineWidth(0.4);

		$top = $this->_getTop();
		if (isset($liste['top']))
		{
	  $top = $liste['top'];
	  unset($liste['top']);
		}
		$hauteur_ligne = 4;
		$this->SetXY(10, $top);

		$titres  = array_values($titres);
		$tailles = array_values($tailles);
		$liste   = array_values($liste);
		$styles   = array_values($styles);

		// Verifie que la taille des tableaux $titre, $taille
		// et $liste est la m�me
		if(count($titres)!=count($tailles) and
	 (count($titres!=count($liste[0])))){
	 	echo "erreur de taille de tableaux ".count($titres).
	  " / ".count($tailles)." / ".count($liste[0]);
	 	return -201;
	 }

	 // Verification de la place necessaire
	 // Saut de page si besoin
	 $height = count($liste) * $hauteur_ligne; // Ligne
	 $height += $hauteur_ligne; // Titre des colonnes
	 if ($tableTitre != false) $height += 2 * $hauteur_ligne; // Titre du tableau
	 if (($this->_getBottom() - $top) < $height && $this->_getAvailableHeight() > $height)
	 $this->AddPage($orientation);


	 // Affiche le titre du tableau
	 if ($tableTitre != false)
	 {
	 	if (isset($$tableTitre))
	 	$str = $$tableTitre;
	 	else
	 	$str = $tableTitre;
	 	$this->SetFont('Arial','I',12);
	 	$this->Cell(0,2*$hauteur_ligne, $str, 0, 0,'L',1,'');
	 	$this->Ln();
	 }

	 if ($msg != false)
	 {
	 	$this->SetFont('Arial','', 12);
	 	$this->MultiCell(0,$hauteur_ligne,$msg, 0, 'L', 0);
	 	$this->Ln();
	 }

	 // Genere le tableau
	 if (count($liste))
	 {
	  $compteur = 1;
	  $this->SetFont('Arial','B',8);
	  $this->SetFillColor(200,200,200);
	  $this->Cell(8,$hauteur_ligne,'', 1, 0,'L',1,'');
	  $taille = reset($tailles);
	  $style = reset($styles);
	  foreach($titres as $titre)
	  {
	  	if (isset($$titre))
	  	$elt = $$titre;
	  	else
	  	$elt = $titre;
	  	$this->Cell($taille,$hauteur_ligne,$elt, 1, 0,'L',1,'');
	  	$taille = next($tailles);
	  	//Cell( w,  h,  txt, border, ln, align, fill, link)
	  }
	  $this->Ln();
	  // Genere la liste
	  foreach($liste as $ligne)
	  {
	  	$border = 1;
	  	if (isset($ligne['border']))
	  	{
	  		$border = $ligne['border'];
	  		unset($ligne['border']);
	  	}
	  	$this->SetFont('Arial','B',7);
	  	if (($border==1) || strpos($border, 'T') !== false)
	  	$this->Cell(8,$hauteur_ligne,$compteur++, $border, 0,'L',1,'');
	  	else
	  	$this->Cell(8,$hauteur_ligne,'', $border, 0,'L',1,'');
	  	 
	  	$taille = reset($tailles);
	  	$style = reset($styles);
	  	foreach($ligne as $elt)
	  	{
	  		$this->SetFont('helvetica','',7);
	  		$this->Cell($taille,$hauteur_ligne,$elt, $border, 0,'L',0,'');
	  		$taille = next($tailles);
	  		$style = next($styles);
	  		//Cell( w,  h,  txt, border, ln, align, fill, link)
	  	}
	  	$this->Ln();
	  }
	 }
	 return $this->GetY();
	}
	//}}}

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


// {{{ entries
/**
 * Genere une liste de donn�es
 *
 * @param  array  $titres    Titres des colonnes
 * @param  array  $tailles   Taille des colonnes
 * @param  array  $liste     Tableau de valeurs
 * @param  array  $style     Style des colonnes : gras B, italic I, souligne U
 *
 */
function entries($team, $entries, $top)
{
	$file = "lang/".utvars::getLanguage()."/pdf.inc";
	require $file;

	$this->setY($top);
	$this->SetFont('Arial','B',18);
	$this->Cell(0, 10, $team['team_name'], 0, 0,'L',1,'');
	$this->Ln();


	// Genere le tableau
	$regiId = -1;
	$hauteur_ligne = 5;
	$nb = 1;
	$this->SetFont('helvetica','',10);
	$this->Cell(5, $hauteur_ligne, '#', 1, 0,'C',0,'');
	$this->Cell(50, $hauteur_ligne, 'NOM Prénom', 1, 0,'L',0,'');
	$this->Cell(18, $hauteur_ligne, 'licence', 1, 0,'C',0,'');
	$this->Cell(20, $hauteur_ligne, 'catégorie', 1, 0,'C',0,'');
	$this->Cell(27, $hauteur_ligne, 'Simple', 1, 0,'C',0,'');
	$this->Cell(77, $hauteur_ligne, 'Double', 1, 0,'C',0,'');
	$this->Cell(77, $hauteur_ligne, 'Mixe', 1, 0,'C',0,'');
	foreach($entries as $entrie)
	{
		if ($regiId != $entrie['regi_id'])
		{
			$regiId = $entrie['regi_id'];
			$this->Ln();
		}
		if ($entrie['rank_disci'] <= WBS_WS)
		{
			$this->Cell(5, $hauteur_ligne, $nb++, 1, 0,'C',0,'');
			$this->Cell(50, $hauteur_ligne, $entrie['regi_longName'], 1, 0,'L',0,'');
			$this->Cell(18, $hauteur_ligne, $entrie['mber_licence'], 1, 0,'L',0,'');
			$this->Cell(20, $hauteur_ligne, $entrie['regi_catage'], 1, 0,'L',0,'');
			$this->Cell(10, $hauteur_ligne, $entrie['rkdf_label'], 1, 0,'L',0,'');
			$this->Cell(17, $hauteur_ligne, $entrie['draw_stamp'], 1, 0,'L',0,'');
		}
		else
		{
			$this->Cell(10, $hauteur_ligne, $entrie['rkdf_label'], 1, 0,'L',0,'');
			$this->Cell(17, $hauteur_ligne, $entrie['draw_stamp'], 1, 0,'L',0,'');
			$this->Cell(50, $hauteur_ligne, $entrie['partner'], 1, 0,'L',0,'');
		}
	}
	return $this->GetY();
}
//}}}

}
?>