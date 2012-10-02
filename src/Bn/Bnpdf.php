<?php
/*****************************************************************************
 ******************************************************************************/

require_once "Tcpdf/tcpdf.php";

define("MARGIN_TOP",     10);
define("MARGIN_BOTTOM",  10);
define("MARGIN_LEFT",    10);
define("MARGIN_RIGHT",   10);

class BnPdf extends TCPDF
{
	var $orientation = "P";
	var $_isFooter   = true;
	var $_isHeader   = false;
	var $_page       = 0;
	var $_title      = '';

	
	/**
	 * Constructor.
	 *
	 * @param string $name      Name of button
	 * @param string $attribs   Attribs of the button
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		/*
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
	  */
	}

	/**
	 * @todo entete pour les tournois a mettre ailleur
	 * Entete des pages PDF : non du tournoi a revoir
	 * en fonction de l'orientation de la page L ou P
	 * on affiche Badnet en filigramme
	 *
	 * @return void
	 */

	public function Header()
	{
		//if(!$this->_isHeader) return;
		$maxWidth = ($this->orientation=='L') ? 297:210;
		$maxWidth -= MARGIN_RIGHT;

		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$oMeta = new Oeventmeta($eventId);

		// Affichage du titre du tournoi
	  	$this->setXY(10, 10);
		$this->SetFillColor(255);
		$this->SetTextColor(0);
		$str = $oEvent->getVal('name');
		$font = $oMeta->getVal('titlefont', 'helvetica');
		$taille = $oMeta->getVal('titlesize', 14);
		$this->SetFont($font, '', $taille);
		$this->Cell(0, 8, $str, 0, 0, 'L');
		$this->Ln(11);
		
		// Affichage du lieu et de la date du tournoi
		$str = $oEvent->getVal('place');
		$date = $oEvent->getVal('date');
		if (!empty($date)) $str .= ' - ' . $date;
		$this->SetfontSize($taille-4);
		$this->Cell(0, 0, $str, '', 1, 'L');
		$this->Ln(10);

		// Calcul de la position du logo
		$logo = $oMeta->getLogo();
		$width = 0; $height = 0;
		$top = $oMeta->getVal('top');
		$left = $oMeta->getVal('left');
		$size = @getImagesize($logo);

		if ($size AND (strpos($logo,"gif") === FALSE))
		{
			$few = $oMeta->getVal('width') / $size[0];
			//$feh = 21 / $size[1];
			$feh = $oMeta->getVal('height') / $size[1];
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
		if ($width && $height) $this->Image($logo, $left, $top, $width, $height);
	}

	/**
	 * Pied de page des documents PDF
	 * En fonction de l'orientation L ou P, on affiche la date de
	 * generation, le logo FPDF, le lien B@dNet et le numero de page
	 *
	 * @return void
	 */

	public function Footer()
	{
		$maxWidth = ($this->orientation=='L') ? 297:210;
		$maxHeight = ($this->orientation=='L') ? 210:297;
		//$maxWidth -= 10;
		//$maxHeight -= 10;
		$maxWidth -= 20;
		$maxHeight -= 20;
		$top = 10;
		$left = 10;
		/*
		 $this->SetXY($this->_getLeft(), $this->_getBottom()-1);
		 $this->SetFont('helvetica','B',6);
		 $page = $this->PageNo();
		 $this->Cell(0, 3, "{$this->_title} -{$page}-", 0, 0, 'R', 0);
		 */
		// Ligne de separation
		$this->SetLineWidth(0.7);
		$this->SetDrawColor(56,168,92);
		$this->Line($left, $maxHeight-5, $left+$maxWidth, $maxHeight-5);
		//old : $this->Line($this->_getLeft(), $maxHeight-8, $this->_getRight(), $maxHeight-8);
			
		//Texte techno
		$this->SetTextColor(0);
		$this->SetFont('helvetica', '', 6);
		$this->SetXY($left, $maxHeight-3);
		$this->Cell(0, 0, "A technology developped by ", '', 1, 'L');
		$this->Cell(0, 0, 'BadNet v3.0.0', 0, 0, 'L');
		//Old $this->SetXY(MARGIN_LEFT, $maxHeight-6);
		//Old $this->Cell(60, 0, "A technology developped by", '', 1, 'L');
		//Old$this->Cell(60, 0, 'BadNet v3.0.0', 0, 0, 'L');
			
		//Date de generation du document
		$genere = date("d/m/Y H:i");
		$label = "Généré le ";
		$this->SetXY($maxWidth-60, $maxHeight-3);
		$this->Cell(0, 0, "$label $genere", 0, 2, 'R');
		$label = 'Page '.$this->PageNo().'/{nb}';
		$this->Cell(0, 0, trim($label), 0, 0, 'R');

		// Logo badnet
		$badnetLogo = 'Bn/Img/Badnet_3d.png';
		if(is_file($badnetLogo))
		//old $this->Image($badnetLogo, ($maxWidth/2)-10, $maxHeight-6, 0, 10,'', 'http://www.badnet.org');
		$this->Image($badnetLogo, ($maxWidth/2)-10, $maxHeight, 0, 10, '', 'http://www.badnet.org');
	}

	/**
	 * return the top position
	 *
	 * @return void
	 */
	function _getTop()
	{
		if ($this->_isHeader) $top = 45;
		else $top = 10;
		return $top;
	}

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

	/**
	 * return the left position
	 *
	 * @return void
	 */
	function _getLeft()
	{
		return MARGIN_LEFT;
	}

	/**
	 * return the right position
	 *
	 * @return void
	 */
	function _getRight()
	{
		$maxWidth = ($this->orientation=='L') ? 297:210;
		return $maxWidth-MARGIN_RIGHT;
	}

	/**
	 * return the availlable heigth
	 *
	 * @return void
	 */
	function _getAvailableHeight()
	{
		return $this->_getBottom() - $this->_getTop();
	}

	/**
	 * return the availlable width
	 *
	 * @return void
	 */
	function _getAvailableWidth()
	{
		return $this->_getRight() - $this->_getLeft();
	}

	/**
	 * Affiche le titre du document
	 *
	 * @return void
	 */
	function printTitle($aTitle)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require $file;
		$top = $this->_getTop();
		// Affichage du titre du document
		$this->SetXY($this->_getLeft(), $top);
		$this->SetFont('swz921n', '', 30);
		if ( isset($$aTitle) )	$title = $$aTitle;
		else $title = $aTitle;
		$this->Cell(0, 20,  $title, 0 ,1 ,'C');
		$this->_title = $title;
		return $top + 20;
	}

	/**
	 * Fixe le titre du document
	 *
	 * @return void
	 */
	function setDocTitle($aTitle)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require $file;
		if(isset($$aTitle))	$this->_title = $$aTitle;
		else $this->_title = $aTitle;
	}

	/**
	 * Debut du document
	 *
	 * @return void
	 */
	function start($aOrientation = 'P', $aTitle=null)
	{
		// Creation du document
		$titre='Badnet Division';
		$this->SetTitle($titre);
		$this->SetFillColor(255,255,255);
		$this->SetAuthor('BadNet Team');
		$this->AliasNbPages();
		if ($aOrientation == 'L' || $aOrientation == 'P') $this->orientation = $aOrientation;
		///$this->AddPage($this->orientation);

		// Affichage du titre du document
		$top = $this->_getTop();
		if (!is_null($aTitle))
		{
			//$top = $this->printTitle($aTitle);
		}
		return $top;
	}

	/**
	 * Fin d'affichage du document
	 *
	 * @return void
	 */
	function end($aFile, $aDisplay=true)
	{
		// Dossier des fichiers temporaires
		$dir = '../Temp/Pdf';

		// Purge des fichiers pdf trop ancien
		$t = time();
		$h = opendir($dir);
		while($file=readdir($h))
		{
			$path = $dir.'/'.$file;
			if(is_file($file) && $t-filemtime($path)>1800)
			@unlink($path);
		}
		closedir($h);

		// Nom du fichier temporaire
		$file = tempnam($dir, 'badnet');
		rename($file, $file.'.pdf');
		$file .= '.pdf';
		chmod($file, 0777);

		$dest = 'I';
		rename($file, $aFile);
		$file = $aFile;
		if ($aDisplay) $dest = 'I';
		else $dest = 'F';

		// Generer le pdf dans le fichier
		$this->Output($file, $dest);
	}


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
		if (isset($liste['newPage']) and
		$liste['newPage'] === true)
		$this->AddPage($orientation);
		unset($liste['newPage']);

		$this->SetFont('helvetica','',8);
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
		if ($tableTitre != false)
		$height += 2 * $hauteur_ligne; // Titre du tableau
		if (($this->_getBottom() - $top) < $height &&
		$this->_getAvailableHeight() > $height)
		$this->AddPage($orientation);


		// Affiche le titre du tableau
		if ($tableTitre != false)
		{
			if (isset($$tableTitre))
			$str = $$tableTitre;
			else
			$str = $tableTitre;
			$this->SetFont('helvetica','B',18);
			$this->Cell(0,2*$hauteur_ligne, $str, 0, 0,'L',1,'');
			$this->Ln();
		}

		if ($msg != false)
		{
			$this->SetFont('helvetica','', 12);
			$this->MultiCell(0,$hauteur_ligne,$msg, 0, 'L', 0);
			$this->Ln();
		}

		// Genere le tableau
		if (count($liste))
		{
			$compteur = 1;
			$this->SetFont('helvetica','B',8);
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
				$this->SetFont('helvetica','B',8);
				if (($border==1) || strpos($border, 'T'))
				$this->Cell(8,$hauteur_ligne,$compteur++, $border, 0,'L',1,'');
				else
				$this->Cell(8,$hauteur_ligne,'', $border, 0,'L',1,'');

				$taille = reset($tailles);
				$style = reset($styles);
				foreach($ligne as $elt)
				{
					$this->SetFont('helvetica','',8);
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

	function RotatedText($x,$y,$txt,$angle)
	{
		//Rotation du texte autour de son origine
		$this->StartTransform();
		$this->Rotate($angle,$x,$y);
		//$this->Text($x,$y,$txt);
		//$this->Write(5, $txt, '', 0, 'L');
		$this->Cell(15, 10, $txt, 'TLR', 0, 'L', 0);
		$this->StopTransform();
	}

	function RotatedImage($file,$x,$y,$w,$h,$angle)
	{
		//Rotation de l'image autour du coin sup�rieur gauche
		$this->Rotate($angle,$x,$y);
		$this->Image($file,$x,$y,$w,$h);
		$this->Rotate(0);
	}

}
?>