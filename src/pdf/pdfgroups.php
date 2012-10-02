<?php
/*****************************************************************************
 !   Module     : pdf
 !   File       : $File$
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.21 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/06 07:59:33 $
 ******************************************************************************/
require_once "pdfbase.php";
require_once "base.php";
require_once "utils/utdraw.php";
require_once "utils/utround.php";
require_once "utils/utteam.php";
require_once "utils/utmatch.php";


class  pdfGroups extends pdfBase
{
	/**
	 * Display a model for a 64-entries tie for a double draw
	 *
	 * @return void
	 */
	function doubleKO($valeurs_depart, $colonnes, $titre='', $typeDraw='', $ibf=0)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require_once $file;
		//print_r($valeurs_depart);
		foreach($valeurs_depart as $tour => $valeurs)
		{
			$nom = array();
			$logo = array();
			$level = array();
			$club = array();
			if(is_array($valeurs))
			{
				foreach($valeurs['value'] as $value)
				{
					$nom[]= $value['longname'];
					if(($value['ibfn']) && ($ibf)) $logo[] = $value['ibfn'];
					else $logo[] = $value['logo'];
					if (!$ibf) $level[] = $value['level'];
					else $level[] = '';
					$club[] = $value['stamp'];
				}
			}
			else
			{
				$nom[] = "bye";$nom[]= '';
				$logo[] = "";
				$level[] = '';$level[] = '';
				$club[] = '';$club[] = '';
			}

			$joueurs[] = $nom ;
			$seeds[] = $valeurs['seed'];
			$flags[] = $logo;
			$scores[] = "" ;
			$match_num[] = '';
			$levels[] = $level;
			$clubs[] = $club;
		}

		$tableau[] = array($joueurs, $scores, $seeds, $flags, $match_num, $levels, $clubs);
		//print_r($colonnes);
		foreach($colonnes as $tour => $valeurs)
		{
			$joueurs = array();
			$scores = array();
			$seeds = array();
			$match_num = array();
			$flags = array();
			$levels = array();
			foreach($valeurs as $cle => $vals)
			{
				$nom = array();
				$logo = array();
				$level = array();
				$club = array();
				if (is_array($vals))
				{
					if(isset($vals['score'])) $score = $vals['score'];
					else $score = $vals['match_num'];
					if(is_array($vals['value']))
					{
						foreach($vals['value'] as $value)
						{
							$nom[] = $value['name'];
							//$logo = $value['logo'];
							$logo[] = '';
							if (!$ibf) $level[] = $value['level'];
							else $level[] = '';
							$club[] = $value['stamp'];
						}
					}
					else
					{
						$nom[] = $vals['value'];$nom[] = '';
						$score = "";
						$logo[] = "";
						$level[] = ''; $level[] = '';
						$club[] = ''; $club[] = '';
					}
					$joueurs[] = $nom ;
					$seeds[] = '';//$valeurs['seed'];
					$flags[] = $logo;
					$scores[] = $score;
					$levels[] = $level;
					$clubs[] = $club;
					if(isset($vals['match_num'])) $match_num[] = $vals['match_num'];
					else $match_num[] = '';
				}
			}

			$tableau[] = array($joueurs, $scores, $seeds, $flags, $match_num, $levels, $clubs);

		}

		// Le tableau est pr�t. Il faut l'afficher
		$nbLignes = count($tableau[0][0]);
		$nbCols = count($tableau);
		if($nbLignes <=  16 )
		{
			$this->_tab_doublenew($tableau,0,0,$titre,'',$typeDraw);
		}
		// TABLEAU 32 //
		elseif(( $nbLignes > 16) && ($nbLignes <= 32))
		{
			foreach($tableau as $colonnes => $lignes)
			{
				$tab[0] = array_slice($lignes[0], 0, count($lignes[0]) / 2);
				$tab[1] = array_slice($lignes[1], 0, count($lignes[0]) / 2);
				if (isset($lignes[2]))
				{
					$tab[2] = array_slice($lignes[2], 0, count($lignes[0]) / 2);
					$tab[3] = array_slice($lignes[3], 0, count($lignes[0]) / 2);
					$tab[4] = array_slice($lignes[4], 0, count($lignes[0]) / 2);
					$tab[5] = array_slice($lignes[5], 0, count($lignes[0]) / 2);
					$tab[6] = array_slice($lignes[6], 0, count($lignes[0]) / 2);
				}
				$tableau_1[] = $tab;

				$tab[0] = array_slice($lignes[0], count($lignes[0]) / 2);
				$tab[1] = array_slice($lignes[1], count($lignes[0]) / 2);
				if (isset($lignes[2]))
				{
					$tab[2] = array_slice($lignes[2], count($lignes[0]) / 2);
					$tab[3] = array_slice($lignes[3], count($lignes[0]) / 2);
					$tab[4] = array_slice($lignes[4], count($lignes[0]) / 2);
					$tab[5] = array_slice($lignes[5], count($lignes[0]) / 2);
					$tab[6] = array_slice($lignes[6], count($lignes[0]) / 2);
				}
				$tableau_2[] = $tab;
					
				if($colonnes!=0) $tableau_3[] = $lignes;
			}
			$tab[0] = array_pop($tableau_1);
			$tab[1] = array_pop($tableau_2);
			$this->_tab_doublenew($tableau_1,1,0,$titre,'(1/2)',$typeDraw);
			$this->_tab_doublenew($tableau_2,1,16,$titre,'(2/2)',$typeDraw);
			$this->_tab_doublenew($tableau_3,0,0,$titre,'',$typeDraw);
		}
		// TABLEAU 64 //
		else
		{
			foreach($tableau as $colonnes => $lignes)
			{
				$tab[0] = array_slice($lignes[0], 0, count($lignes[0]) / 4);
				$tab[1] = array_slice($lignes[1], 0, count($lignes[0]) / 4);
				$tab[2] = array_slice($lignes[2], 0, count($lignes[0]) / 4);
				$tab[3] = array_slice($lignes[3], 0, count($lignes[0]) / 4);
				$tab[4] = array_slice($lignes[4], 0, count($lignes[0]) / 4);
				$tab[5] = array_slice($lignes[5], 0, count($lignes[0]) / 4);
				$tab[6] = array_slice($lignes[6], 0, count($lignes[0]) / 4);
				$tableau_1[] = $tab;

				$tab[0] = array_slice($lignes[0], count($lignes[0]) / 4, count($lignes[0]) / 4);
				$tab[1] = array_slice($lignes[1], count($lignes[0]) / 4, count($lignes[0]) / 4);
				$tab[2] = array_slice($lignes[2], count($lignes[0]) / 4, count($lignes[0]) / 4);
				$tab[3] = array_slice($lignes[3], count($lignes[0]) / 4, count($lignes[0]) / 4);
				$tab[4] = array_slice($lignes[4], count($lignes[0]) / 4, count($lignes[0]) / 4);
				$tab[5] = array_slice($lignes[5], count($lignes[0]) / 4, count($lignes[0]) / 4);
				$tab[6] = array_slice($lignes[6], count($lignes[0]) / 4, count($lignes[0]) / 4);
				$tableau_2[] = $tab;
					
				$tab[0] = array_slice($lignes[0], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
				$tab[1] = array_slice($lignes[1], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
				$tab[2] = array_slice($lignes[2], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
				$tab[3] = array_slice($lignes[3], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
				$tab[4] = array_slice($lignes[4], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
				$tab[5] = array_slice($lignes[5], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
				$tab[6] = array_slice($lignes[6], 2*count($lignes[0]) / 4, count($lignes[0]) / 4);
				$tableau_3[] = $tab;
					
				$tab[0] = array_slice($lignes[0], 3*count($lignes[0]) / 4);
				$tab[1] = array_slice($lignes[1], 3*count($lignes[0]) / 4);
				$tab[2] = array_slice($lignes[2], 3*count($lignes[0]) / 4);
				$tab[3] = array_slice($lignes[3], 3*count($lignes[0]) / 4);
				$tab[4] = array_slice($lignes[4], 3*count($lignes[0]) / 4);
				$tab[5] = array_slice($lignes[5], 3*count($lignes[0]) / 4);
				$tab[6] = array_slice($lignes[6], 3*count($lignes[0]) / 4);
				$tableau_4[] = $tab;
					
				if($colonnes>2) $tableau_5[] = $lignes;
			}
			$tab[0] = array_pop($tableau_1);
			$tab[0] = array_pop($tableau_1);
			$tab[1] = array_pop($tableau_2);
			$tab[1] = array_pop($tableau_2);
			$tab[2] = array_pop($tableau_3);
			$tab[2] = array_pop($tableau_3);
			$tab[3] = array_pop($tableau_4);
			$tab[3] = array_pop($tableau_4);
			$this->_tab_doublenew($tableau_1,2,0,$titre,'(1/4)',$typeDraw);
			$this->_tab_doublenew($tableau_2,2,16,$titre,'(2/4)',$typeDraw);
			$this->_tab_doublenew($tableau_3,2,32,$titre,'(3/4)',$typeDraw);
			$this->_tab_doublenew($tableau_4,2,48,$titre,'(4/4)',$typeDraw);
			$this->_tab_doublenew($tableau_5,1,0,$titre,'',$typeDraw);
		}
		return;
	}


	/**
	 * Display the draw for a double event
	 *
	 * @return void
	 */
	function _tab_doublenew( $tableau, $offset=0, $offsetPosition = 0,
	$aTitre = '',$aNpage = '', $aTypeDraw = '')
	{
		$ut = new Utils();
		$xpos = 10;//$this->getX();
		$ypos = $this->getY();
		$nbLignes = count($tableau[0][0]);
		$taille = 8.5;
		$nbCols = count($tableau);
		$firstCol = 67;
		$lgCol = 25;
		$margeDroite = 20;
		
		// Calcul de la hauteur
		$fullHeight =($nbLignes+1)*$taille;
		if($ypos+$fullHeight > $this->_getBottom())
		{
			$ypos = $this->_getTop();
			$this->AddPage('P');
		}

		// Nom du tableau		
		$this->SetFont('Arial','I',12);
		$this->SetTextColor(0);
		$this->setXY($xpos,$ypos);
		$str = $aTitre;
		if (!empty($aTypeDraw)) $str .= ' ' . $aTypeDraw;
		if (!empty($aNpage)) $str .= ' - ' . $aNpage;
		
		$this->Cell(0, 8, $str, 0, 0, 'L', 0);
		$ypos += 7;

		// En tete du tableau
		for ($i=0; $i < $nbCols; $i++)
		{
			//if(!isset($tableau[$i][0][0][0])) $nbCols--;
			$title[$nbCols-$i -1 ] = $ut->getLabel(WBS_WINNER + $i + $offset);
		}
		
		$this->SetXY($xpos,$ypos);
		$this->SetFillColor(235);
		$this->SetFont('Arial','B',8);
		$this->Cell($firstCol, 6, $title[0], 0, 0, 'C', 1);
		for ($i=1; $i < $nbCols; $i++)
		{
			$this->Cell($lgCol, 6, $title[$i], 0, 0, 'C', 1);
		}
		$ypos += 9;
		$xpos_start = $xpos;
		$ypos_start = $ypos;
		
		$this->SetXY($xpos,$ypos);
		$this->SetFillColor(255,255,255);
		
		for($i=0;$i<$nbCols;$i++)
		{
			$largeur = $i ? $lgCol :$firstCol;
			if($i==0){
				$bords2 = '';
				$this->SetFont('Arial','',7);
				$this->SetTextColor(0);
			}
			elseif($i==($nbCols-1)){
				$bords2 = 'L';
				$this->SetFont('Helvetica','B',8);
				$this->SetTextColor(0);
			}
			else{
				$bords2 = 'L';
				$this->SetFont('Helvetica','',7);
				$this->SetTextColor(90);
			}
			$ypos -= $taille/2;
			$nbLignes = count($tableau[$i][0]);
			for($j=0;$j<$nbLignes;$j++)
			{
				$this->SetXY($xpos,$ypos+$taille*3/4);
				// pour les tableaux de qualifs
				if(!isset($tableau[$i][0][$j][0]))	$bords2 = '';
				$this->Cell($largeur, $taille/2, '', $bords2 ,0,'C',0);
				$this->SetXY($xpos,$ypos+$taille-3);
				$position = "";
				// Premiere colone
				if($i==0)
				{
					$ibfn = '';
					$ibfn2 = '';
					$position = $j+1+$offsetPosition;
					// Numero de ligne
					$this->Cell(5, 3, ''.$position, 'B', 0, 'L', 0);
					// Tete de serie
					$this->Cell(5, 3, ''.$tableau[$i][2][$j], 'B', 0, 'L', 0);
					// Drapeau
					if(isset($tableau[$i][3][$j][1]))
					{
						$image = $tableau[$i][3][$j][1];
						if (is_file($image) || substr($image, 0, 5) == 'http:')
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
					if (is_file($image) || substr($image, 0, 5) == 'http:')
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
					$this->Cell(8, 3, ''.$ibfn2, 'B' , 0, 'L', 0);
						
					$this->setXY($xpos+10,$ypos+2);
					$this->Cell(8, 3, ''.$ibfn, '' , 0, 'L', 0);
					$ibf=''; $ibf2 = '';
						
					// Nom des joueurs
					$joueurs = $tableau[$i][0][$j];
					if(isset($joueurs[1]))
					{
						$this->setXY($xpos+18, $ypos+$taille-6);
						$this->Cell($largeur-33, 3, ''.$joueurs[1], '', 0, 'L', 0);
					}
					$this->setXY($xpos+18, $ypos+$taille-3);
					$this->Cell($largeur-33, 3, ''.$joueurs[0], 'B', 0, 'L', 0);
					// Classement
					$this->setXY($xpos+18 +$largeur-33, $ypos+$taille-6);
					if (!empty($tableau[$i][5][$j][1]) )
						$this->Cell(5, 3, $tableau[$i][5][$j][1], 'LR', 0, 'C', 0);
					else
						$this->Cell(5, 3, '', '', 0, 'C', 0);
					$this->setXY($xpos+18+$largeur-33, $ypos+$taille-3);
					if (!empty($tableau[$i][5][$j][0]) )
						$this->Cell(5, 3, $tableau[$i][5][$j][0], 'LRB', 0, 'C', 0);
					else
						$this->Cell(5, 3, '', 'B', 0, 'C', 0);
					// Club
					$this->setXY($xpos+23+$largeur-33, $ypos+$taille-6);
					if (!empty($tableau[$i][6][$j][1]) )
						$this->Cell(10, 3, $tableau[$i][6][$j][1], 'LR', 0, 'C', 0);
					else $this->Cell(10, 3, '', '', 0, 'C', 0);
					
					$this->setXY($xpos+23+$largeur-33, $ypos+$taille-3);
					if (!empty($tableau[$i][6][$j][0]) )
						$this->Cell(10, 3, $tableau[$i][6][$j][0], 'LRB', 0, 'C', 0);
					else
						$this->Cell(10, 3, '', 'B', 0, 'C', 0);
				}
				else
				{
					$this->setXY($xpos, $ypos+$taille-3);
					$joueurs = $tableau[$i][0][$j];
					if(isset($joueurs[1]))
					{
						$this->setXY($xpos, $ypos+$taille-6);
						$this->Cell($largeur, 3, $joueurs[1], '', 0, 'L', 0);
					}
					$this->setXY($xpos, $ypos+$taille-3);
					$this->Cell($largeur, 3, $joueurs[0], 'B', 0, 'L', 0);
				}
				if((($tableau[$i][1][$j])!='')&&($i!=0))
				{
					$this->SetXY($xpos, $ypos+$taille);
					$this->SetFont('Helvetica', '', 5);
					$this->Cell($largeur, 3, $tableau[$i][1][$j], '' , 0, 'C', 0);
					$this->SetFont('Helvetica', '', 7);
				}
				
				$ypos += $taille ;
			}
			$xpos += $largeur ;
			$ypos = $ypos_start ;
			$taille = $taille * 2;
		}
		$this->setXy($xpos_start,$ypos_start + $fullHeight);
	}
	
	// {{{ _tab_simple()
	/**
	 * Display the draw for a single event
	 *
	 * @return void
	 */
	function _tab_simplenew($tableau, $offset=0, $offsetPosition = 0, $aTitre = '',
	$aNpage = '', $aTypeDraw = '')
	{
		$ut = new Utils();
		
		$xpos = $this->getX();
		$ypos = $this->getY();
		$nbLignes = count($tableau[0][0]);
		$taille = 4.4;
		$nbCols = count($tableau);
		$firstCol = 67;
		$lgCol = 25;
		$margeDroite = 20;
		
		// Calcul de la hauteur
		$fullHeight =($nbLignes+1)*$taille;
		if($ypos+$fullHeight > $this->_getBottom())
		{
			$ypos = $this->_getTop();
			$this->AddPage('P');
		}

		// Nom du tableau		
		$this->SetFont('Arial','I',12);
		$this->SetTextColor(0);
		$this->setXY($xpos,$ypos);
		$str = $aTitre;
		if (!empty($aTypeDraw)) $str .= ' ' . $aTypeDraw;
		if (!empty($aNpage)) $str .= ' - ' . $aNpage;
		
		$this->Cell(0, 8, $str, 0, 0, 'L', 0);
		$ypos += 7;

		// En tete du tableau
		for ($i=0; $i < $nbCols; $i++)
		{
			//if(!isset($tableau[$i][0][0][0])) $nbCols--;
			$title[$nbCols-$i -1 ] = $ut->getLabel(WBS_WINNER + $i + $offset);
		}
		
		$this->SetXY($xpos,$ypos);
		$this->SetFillColor(235);
		$this->SetFont('Arial','B',8);
		$this->Cell($firstCol, 6, $title[0], 0, 0, 'C', 1);
		for ($i=1; $i < $nbCols; $i++)
		{
			$this->Cell($lgCol, 6, $title[$i], 0, 0, 'C', 1);
		}
		$ypos += 9;
		$xpos_start = $xpos;
		$ypos_start = $ypos;
		
		$this->SetXY($xpos,$ypos);
		$this->SetFillColor(255,255,255);
		
		for($i=0;$i<$nbCols;$i++)
		{
			$largeur = $i ? $lgCol :$firstCol;
			if($i==0)
			{
				$bords2 = '';
				$this->SetFont('Arial', '', 7);
				$this->SetTextColor(0);
			}
			elseif($i==($nbCols-1))
			{
				$bords2 = 'L';
				$this->SetFont('Helvetica', 'B', 8);
				$this->SetTextColor(0);
			}
			else
			{
				$bords2 = 'L';
				$this->SetFont('Helvetica','',7);
				$this->SetTextColor(90);
			}
			$ypos -= $taille/2;
			$nbLignes = count($tableau[$i][0]);
			for($j=0;$j<$nbLignes;$j++)
			{
				$this->SetXY($xpos,$ypos+$taille*3/4);
				// pour les tableaux de qualifs
				if(!isset($tableau[$i][0][$j][0])) $bords2 = '';
				$this->Cell($largeur, $taille/2, '', $bords2 ,0,'C',0);
				$this->SetXY($xpos,$ypos+$taille-3);
				$position = "";
				if($i==0)
				{
					$position = $j+1+$offsetPosition;
					// Numero de ligne
					$this->Cell(5,3,''.$position ,'B' ,0,'L',0);
					// Tete de serie
					$this->Cell(10, 3,''.$tableau[$i][2][$j]  ,'B' ,0,'L',0);
					// Drapeau
					$image = $tableau[$i][3][$j][0];
					if (is_file($image) || substr($image, 0, 5) == 'http:')
					{
						$size = @getImagesize($image);
						$few = 6/$size[0];
						$feh = 3/$size[1];
						$fe = min(1, $feh, $few);
						$widthLogo = $size[0] * $fe;
						$heightLogo = $size[1] * $fe;
						$xLogo = 22;
						$yLogo = $ypos+$taille-$heightLogo-0.5;
						$this->Image($image, $xLogo, $yLogo, $widthLogo, $heightLogo);
						$this->Cell($widthLogo,3, '','B' ,0,'L',0);
						$ibfn = '';
					}
					else
					{
						$widthLogo = 0;
						$ibfn = $image;
					}
					// Nom du joueur
					$this->Cell($largeur-38-$widthLogo, 3, ''.$tableau[$i][0][$j][0], 'B', 0, 'L', 0);
					// Classement
					if (!empty($tableau[$i][5][$j])) $this->Cell(8, 3, $tableau[$i][5][$j], 'BLR', 0, 'C', 0);
					else $this->Cell(8, 3, '', 'B', 0, 'C', 0);
					// Club
					if (!empty($tableau[$i][6][$j])) $this->Cell(15, 3, $tableau[$i][6][$j], 'BLR', 0, 'C', 0);
					else $this->Cell(15, 3, '', 'B', 0, 'C', 0);
				}
				else
				{
					if(($tableau[$i][4][$j]!='')||(isset($tableau[$i][0][$j][0])))
					{
						//$this->Cell(5,3,''.$tableau[$i][4][$j]  ,'B' ,0,'L',0);
						$this->Cell($largeur, 3, $tableau[$i][0][$j][0], 'B', 0, 'L', 0);
					}
				}
				if((($tableau[$i][1][$j])!='')&&($i!=0))
				{
					$this->SetXY($xpos,$ypos+$taille);
					$this->SetFont('Helvetica', '', 5);
					$this->Cell($largeur,3, $tableau[$i][1][$j], 0, 0, 'C', 0);
					$this->SetFont('Helvetica','',7);
				}
				$ypos += $taille ;
			}
			//Cell( w,  h,  txt, border, ln, align, fill, link)
			$xpos += $largeur ;
			$ypos = $ypos_start;
			$taille = $taille * 2;
		}
		$this->setXy($xpos_start,$ypos_start + $fullHeight);
		return true;
	}
	
	/**
	 * Display a model for a 64-entries tie for a single draw
	 *
	 * @return void
	 */
	function singleKO($valeurs_depart, $colonnes, $titre = '', $typeDraw='',$ibf=0)
	{
		$ut = new utils();
		$isSquash = $ut->getParam('issquash', false);
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require_once $file;
		//print_r($valeurs_depart);
		foreach($valeurs_depart as $tour => $valeurs)
		{
			$nom = array();
			$logo = array();
			if(is_array($valeurs))
			{
				$nom[]  = $valeurs['value'][0]['longname'];
				if(($valeurs['value'][0]['ibfn']!='')&&($ibf)) $logo[]   = $valeurs['value'][0]['ibfn'];
				else $logo[]   = $valeurs['value'][0]['logo'];
				if (!$ibf) $levels[] = $valeurs['value'][0]['level'];
				else $levels[] = '';
				if ($isSquash) $clubs[] = $valeurs['value'][0]['rank'];
				else $clubs[] = $valeurs['value'][0]['stamp'];
			}
			else
			{
				$nom[] = $valeurs;
				$logo[] = "";
				$levels[] = "";
				$clubs[] = "";
			}

			$joueurs[] = $nom ;
			$seeds[] = $valeurs['seed'];
			$flags[] = $logo;
			$scores[] = "" ;
			$match_num[] = "";
		}

		$tableau[] = array($joueurs, $scores, $seeds, $flags, $match_num, $levels, $clubs);

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
				if (is_array($vals))
				{
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
					if(isset($vals['score'])) $score = $vals['score'];
					else $score = $score = $vals['match_num'];
				}
				else
				{
					if(isset($vals['value'])) $nom[] = $vals['value'];
					$score = "";
					$logo[] = "";
				}
				$joueurs[] = $nom ;
				if(isset($vals['match_num'])) $match_num[] = $vals['match_num'];
				else $match_num[]='';
				$flags[] = $logo;
				$scores[] = $score;
				$seeds[] = '';
			}

			$tableau[] = array($joueurs, $scores, $seeds, $flags, $match_num);
		}

		// Le tableau est pret. Il faut l'afficher
		$nbLignes = count($tableau[0][0]);
		$nbCols = count($tableau);
		if($nbLignes <= 32)
		{
			$this->_tab_simplenew($tableau, 0, 0, $titre, '', $typeDraw);
		}
		// TABLEAU 64 //
		elseif(( $nbLignes > 32) && ($nbLignes <= 64))
		{
			foreach($tableau as $colonnes => $lignes)
			{
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

				if($colonnes>3) $tableau_3[] = $lignes;
			}
			$tab[0] = array_pop($tableau_1);
			$tab[1] = array_pop($tableau_2);
			$this->_tab_simplenew($tableau_1,1,0,$titre,'(1/2)',$typeDraw);
			$this->_tab_simplenew($tableau_2,1,32,$titre,'(2/2)',$typeDraw);
			$this->_tab_simplenew($tableau_3,0,0,$titre,'Fin',$typeDraw);
		}
		// TABLEAU 128 //
		else
		{
			foreach($tableau as $colonnes => $lignes)
			{
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
					
				if($colonnes>4) $tableau_5[] = $lignes;
			}
			$tab[0] = array_pop($tableau_1);
			$tab[0] = array_pop($tableau_1);
			$tab[1] = array_pop($tableau_2);
			$tab[1] = array_pop($tableau_2);
			$tab[2] = array_pop($tableau_3);
			$tab[2] = array_pop($tableau_3);
			$tab[3] = array_pop($tableau_4);
			$tab[3] = array_pop($tableau_4);
			$this->_tab_simplenew($tableau_1,2,0,$titre,'(1/4)',$typeDraw);
			$this->_tab_simplenew($tableau_2,2,32,$titre,'(2/4)',$typeDraw);
			$this->_tab_simplenew($tableau_3,2,64,$titre,'(3/4)',$typeDraw);
			$this->_tab_simplenew($tableau_4,2,96,$titre,'(4/4)',$typeDraw);
			$this->_tab_simplenew($tableau_5,1,0,$titre,'',$typeDraw);
		}
		return;
	}
	
	
	
	/**
	 * Affichage d'une poule nouvelle formule :
	 *     - affichage des joueurs de la poule avec club , classemment et point
	 *     - des matchs avec numero, heure prevue, joueurs, resultats
	 * $top    la position de depart sur la page
	 * $group  la composition du group :
	 *          'draw_name', 'rund_name'....
	 * $pairs  la composition de la poule :
	 *         pour chaque joueur/paire/equipe : 'tds', 'name', 'club', 'flag', 'level', 'rank',
	 * $match  les matchs de la poule
	 *         pour chaque match : 'numero', 'datetime', 'venue', 'winner', 'looser', 'score'
	 * $rank   classement
	 *         pour chaque paire/equipe : 'rank', 'name', 'points', 'games', 'rallies'
	 * @return void
	 */
	function displayGroup($top, $group, $pairs, $matchs, $ranks)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require $file;

		$utfpdf =  new baseFpdf();

		// Creation du document
		// premier appel: top=null
		// Affichage du titre
		$left = $this->_getLeft();
		$rowHeight = 5;

		if ($top == null)
		{
	  		$this->SetAutoPageBreak(false);

	  		$top = $this->_getTop();
	  		$right = $this->_getRight();

		  // Tableau
	  		$title = $group['draw_name'];
	  		$this->SetXY($left, $top);
	  		$this->SetFont('arial','I',12);
	  		$this->SetTextColor(0);
	  		$this->SetFillColor(0);
	  		$width = $this->GetStringWidth($title)+5;
	  		$this->Cell($width, 10, $title, '', 0, 'L', 0);

	  		$this->setDocTitle($PDF_POULES." $title");
	  		$this->SetFont('Arial','B',10);
	  		$buf = '';
	  		if ($group['nb3'] && $group['nbs3']) $buf .= "{$PDF_DEFGROUP_3} {$group['nbs3']} ";
	  		if ($group['nb4'] && $group['nbs4']) $buf .= "{$PDF_DEFGROUP_4} {$group['nbs4']} ";
	  		if ($group['nb5'] && $group['nbs5']) $buf .= "{$PDF_DEFGROUP_5} {$group['nbs5']} ";
	  		if ($group['nbsecond']) $buf .= "$PDF_DEFGROUP_SECOND".($group['nbsecond']);
	  		//$top += 8;
	  		//$this->SetXY($left, $top);
	  		$this->Cell(0, 10, $buf, '', 0, 'R', 0);
	  		$top += $rowHeight+3;
		}

		// Calcul de la hauteur d'affichage
		$nbPairs  = count($pairs);
		$nbMatchs = count($matchs);
		$height = ($nbPairs + $nbMatchs)* $rowHeight;
		$height += $rowHeight;
		$match = reset($matchs);
		if ($match->isDouble())
		$height += ($nbMatchs * $rowHeight);

		// Verifier si la poule tient sur la page
		if($top+$height > $this->_getBottom())
		{
	  $this->_isHeader = false;
	  $page = $this->AddPage('P');
	  $top = $this->_getTop();
		}
		// Affichage du group
		$this->SetXY($left, $top);
		$this->SetFont('Arial','',10);
		$this->SetFillColor(192);
		$this->SetFillColor(255,255,255);
		$this->SetLineWidth(0.3);
		$this->SetDrawColor(0);

		$posX = $left;
		$posY = $top;
		//$this->SetFont('Arial','B',10);
		//$this->Cell(40, $rowHeight, "$PDF_GROUP {$group['rund_name']}", 0, 0, 'L');

		$this->SetFont('swz921n','',8);
		$num = 1;
		$pairId = -1;
		$this->SetFillColor(235);
		$this->SetXY($posX, $posY);
		$this->SetFont('Arial','B',15);
		$this->Cell(5, $rowHeight,  $group['rund_name'], 'RB', 0, 'C', 0);
		$this->SetFont('Arial','B',9);
		$this->Cell(7, $rowHeight, $PDF_DEFGROUP_TDS,    'LTRB', 0, 'C', 1);
		$this->Cell(15, $rowHeight,$PDF_DEFGROUP_LICENCE, 'LTRB', 0, 'C', 1);
		$this->Cell(60, $rowHeight,$PDF_DEFGROUP_PLAYER, 'LTRB', 0, 'C', 1);
		$this->Cell(20, $rowHeight,$PDF_DEFGROUP_CLUB,   'LTRB', 0, 'C', 1);
		$this->Cell(13, $rowHeight,$PDF_DEFGROUP_LEVEL,  'LTRB', 0, 'C', 1);
		$this->Cell(10, $rowHeight,$PDF_DEFGROUP_RANK,   'LTRB', 0, 'C', 1);
		$this->Cell(18, $rowHeight,$PDF_DEFGROUP_AVERAGE,'LTRB', 0, 'C', 1);
		$this->Cell(2, $rowHeight, '',  0, 0, 'C', 0);
		$this->Cell(10, $rowHeight, $PDF_DEFGROUP_MATCHS,'LTRB', 0, 'C', 1);
		$this->Cell(10, $rowHeight, $PDF_DEFGROUP_GAMES, 'LTRB', 0, 'C', 1);
		$this->Cell(10, $rowHeight, $PDF_DEFGROUP_POINTS,'LTRB', 0, 'C', 1);
		$this->Cell(10, $rowHeight, $PDF_DEFGROUP_CLT,  'LTRB', 0, 'C', 1);
		$posY += $rowHeight;
		$this->SetFont('helvetica','',8);
		$nbRows = count($pairs);
		foreach($pairs as $pair)
		{
	  $this->SetXY($posX, $posY);
	  $cadre = (!(--$nbPairs)) ? 'B':'';
	  if ($pairId != $pair['pairId'])
	  {
	  	$cadre .= 'LTR';
	  	$this->Cell(5, $rowHeight, $num++, $cadre, 0, 'C', 1);
	  	$this->Cell(7, $rowHeight, $pair['tds'],   $cadre, 0, 'L', 0);
	  }
	  else
	  {
	  	$cadre .= 'LR';
	  	$this->Cell(5, $rowHeight, '', $cadre, 0, 'L', 1);
	  	$this->Cell(7, $rowHeight,'', $cadre, 0, 'L', 0);
	  }
	  $this->Cell(15, $rowHeight, $pair['licence'],  $cadre, 0, 'C', 0);
	  $this->Cell(60, $rowHeight, $pair['name'],  $cadre, 0, 'C', 0);
	  $this->Cell(20, $rowHeight, $pair['club'],  $cadre, 0, 'C', 0);
	  $this->Cell(13, $rowHeight, $pair['level'], $cadre, 0, 'C', 0);
	  $this->Cell(10, $rowHeight, $pair['rank'],  $cadre, 0, 'C', 0);
	  $this->Cell(18, $rowHeight, sprintf('%.02f', $pair['points']),  $cadre, 0, 'C', 0);
	  $this->Cell(2, $rowHeight, '',  0, 0, 'C', 0);
	  $posY += $rowHeight;
	  if ($pairId != $pair['pairId'] &&
	  isset($ranks[$pair['pairId']]))
	  {
	  	$this->Cell(10, $rowHeight, $ranks[$pair['pairId']]['matchs'],   $cadre, 0, 'C', 0);
	  	$this->Cell(10, $rowHeight, $ranks[$pair['pairId']]['games'],    $cadre, 0, 'R', 0);
	  	$this->Cell(10, $rowHeight, $ranks[$pair['pairId']]['rallies'],  $cadre, 0, 'R', 0);
	  	$this->Cell(10, $rowHeight, $ranks[$pair['pairId']]['t2r_rank'], $cadre, 0, 'C', 0);
	  }
	  else
	  {
	  	$this->Cell(10, $rowHeight, '', $cadre, 0, 'C', 0);
	  	$this->Cell(10, $rowHeight, '', $cadre, 0, 'L', 0);
	  	$this->Cell(10, $rowHeight, '', $cadre, 0, 'L', 0);
	  	$this->Cell(10, $rowHeight, '', $cadre, 0, 'L', 0);
	  }
	  $pairId = $pair['pairId'];
		}
		$posY += 2;

		// Affichage des matchs
		$this->SetXY($posX, $posY);
		$this->SetFont('Arial','B',8);
		$this->Cell(35, $rowHeight, $PDF_DEFGROUP_MATCHES, 0, 0, 'R');
		$posX = $left+38;
		$this->SetXY($posX, $posY);
		$this->Cell(8, $rowHeight,  $PDF_DEFGROUP_NUM,  'TLRB', 0, 'C', 0);
		$this->Cell(9, $rowHeight,  $PDF_DEFGROUP_DAY,  'LTRB', 0, 'C', 0);
		$this->Cell(9, $rowHeight,  $PDF_DEFGROUP_HOUR, 'LTRB', 0, 'C', 0);
		$this->Cell(23, $rowHeight, $PDF_DEFGROUP_VENUE,'LTRB', 0, 'C', 0);
		$this->Cell(83, $rowHeight, $PDF_DEFGROUP_MATCH,'LTRB', 0, 'C', 0);
		$this->Cell(20, $rowHeight, $PDF_DEFGROUP_SCORE,'LTRB', 0, 'C', 0);
		$posY += $rowHeight;
		foreach($matchs as $match)
		{
	  $this->SetFont('helvetica','',7);
	  $this->SetXY($posX, $posY);
	  $cadre = $match->isDouble() ? 'T': 'TB';
	  $this->Cell(8, $rowHeight, $match->getNum(),  "{$cadre}LR", 0, 'C', 0);
	  $this->Cell(9, $rowHeight, $match->getDay(),  "{$cadre}LR", 0, 'C', 0);
	  $this->Cell(9, $rowHeight, $match->getTime(),  "{$cadre}LR", 0, 'C', 0);
	  $this->Cell(23, $rowHeight, $match->getVenue(),  "{$cadre}LR", 0, 'C', 0);
	  $this->Cell(39, $rowHeight, $match->getFirstWinName(false, true),  "{$cadre}L", 0, 'C', 0);
	  $this->Cell(5,  $rowHeight, $PDF_DEFGROUP_VERSUS,  $cadre, 0, 'C', 0);
	  $this->Cell(39, $rowHeight, $match->getFirstLosName(false, true),  "{$cadre}R", 0, 'C', 0);
	  $this->SetFont('helvetica','',6);
	  $this->Cell(20, $rowHeight, $match->getScore(),  "{$cadre}LR", 0, 'C', 0);
	  $posY += $rowHeight;
	  if ($match->isDouble())
	  {
	  	$cadre = 'B';
	  	$this->SetFont('helvetica','',7);
	  	$this->SetXY($posX, $posY);
	  	$this->Cell(8, $rowHeight,  '', "{$cadre}LR", 0, 'C', 0);
	  	$this->Cell(9, $rowHeight,  '', "{$cadre}LR", 0, 'C', 0);
	  	$this->Cell(9, $rowHeight,  '', "{$cadre}LR", 0, 'C', 0);
	  	$this->Cell(23, $rowHeight, '', "{$cadre}LR", 0, 'C', 0);
	  	$this->Cell(39, $rowHeight, $match->getSecondWinName(false),  "{$cadre}L", 0, 'C', 0);
	  	$this->Cell(5, $rowHeight, '',  $cadre, 0, 'C', 0);
	  	$this->Cell(39, $rowHeight, $match->getSecondLosName(false), "{$cadre}R", 0, 'C', 0);
	  	$this->Cell(20, $rowHeight, '', "{$cadre}LR", 0, 'C', 0);
	  	$this->SetFont('helvetica','',6);
	  	$posY += $rowHeight;
	  }
		}
		$top = $posY+3;
		$this->SetLineWidth(0.4);
		//$this->SetDrawColor(56,168,92);
		$this->SetDrawColor(230);
		$this->Line($this->_getLeft(), $top, $this->_getRight(), $top);
		$top = $posY+5;
		$this->setXy($left, $top);
		return $top;
	}

	/**
	 * Display the definiton of the group of the division
	 *
	 * @return void
	 */
	function affichage_div($divId)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require $file;
		$utfpdf =  new baseFpdf();

		// Création du document
		$this->orientation = 'P';
		$this->SetFillColor(255,255,255);

		//--- List of divisions (ie the draws in the database)
		$utt =  new utteam();
		$div = $utt->getDiv($divId);
		$groups = $utt->getGroups($divId);
		$hauteur = 10;
		$top = 300;
		foreach($groups as $groupId=>$group)
		{
	  		if ($group['rund_type'] != WBS_TEAM_GROUP &&
	  			$group['rund_type'] != WBS_TEAM_BACK)
	  			continue;
	  		// Equipes du groupe
	  		$teams  = $utt->getTeamsGroup($groupId);
	  		$nbTeams = count($teams);

		    //Calcul de la hauteur du groupe
		    // S'il depasse, sauter une page
		  	if (($top + ($nbTeams+1)*$hauteur) > 267)
	  		{
	  			$this->AddPage('P');
	  			// Division
	  			$this->SetFont('arial', 'I', 12);
	  			$this->SetTextColor(0);
	  			$str = $PDF_GROUPS . ' - ' . $div['draw_name']; 
	  			$this->Cell(0, 10, $str, '', 1, 'L', 1);
	  			$this->SetLineWidth(0.3);
	  			$this->SetDrawColor(0);
				$top = $this->getY();
				$left = $this->getX();
	  			//$top = 72;
	  			//$left = 10;
	  		}
		    $this->SetXY($left, $top);
	  		$this->SetFont('Arial','B',12);
	  		$this->SetFillColor(230);
	  		$this->Cell(90, $hauteur, $group['rund_name'], 1, 1, 'C', 1);

	  		$this->SetFont('Arial','',10);
	  		$posX = $left;
	  		$posY = $top+$hauteur;
	  		foreach($teams as $team)
	  		{
	  			$this->setXY($posX, $posY);
	  			$imgFile = utimg::getPathTeamLogo($team['team_logo']);
	  			if (!is_file($imgFile)) $imgFile = utimg::getPathFlag($team['asso_logo']);
	  			$this->Cell(10, $hauteur, '', '1', 0);
	  			if (is_file($imgFile) || substr($imgFile, 0, 5) == 'http:')
			  	{
	  				$size = @getImagesize($imgFile);
	  				$few = 8/$size[0];
	  				$feh = 6/$size[1];
	  				$fe = min(1, $feh, $few);
	  				$widthLogo = $size[0] * $fe;
	  				$heightLogo = $size[1] * $fe;
	
	  				$xpos = $posX + (10-$widthLogo)/2;
	  				$ypos = $posY + ($hauteur-$heightLogo)/2;
	  				$this->Image($imgFile, $xpos, $ypos, $widthLogo, $heightLogo);
	  			}
			  	//$this->SetXY($left, $posY);
	  			$val = $team['team_name'] . ' - ' . $team['team_stamp'];
	  			$this->Cell(80, $hauteur, $val, 'LTBR', 1, 'L', 0);
	  			$posY+=$hauteur;
	  		}
	  		$left += 100;
	  		if ($left > 120)
	  		{
			  	$top = $posY + $hauteur;
	  			$left = 10;
	  		}
		}

		return;
	}

	/**
	 * Display the ranking of a event
	 *
	 * @return void
	 */
	function affichage_class($divId)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require $file;

		$utfpdf =  new baseFpdf();

		// recup�re l'id de l'event et les infos associ�es
		$eventId = utvars::getEventId();
		$ute = new utevent();
		$event = $ute->getEvent($eventId);
		$this->AliasNbPages();


		// Cr�ation du document
		$titre='Badnet Division';
		$this->SetFont('Arial','B',14);
		$this->SetFillColor(255,255,255);
		$this->SetTitle($titre);
		$this->SetAuthor('BadNet Team');
		$this->SetY(50);

		//--- List of divisions (ie the draws in the database)
		require_once "utils/utteam.php";
		$utt =  new utteam();
		$divs = $utt->getDivs();
		$div = array_keys($divs);

		// 	Constante
		$widthLogo = 10;
	  	$widthTeamName  = 50;
	  	$widthTeamStamp = 20;
	  	$widthGroupName = $widthLogo+$widthTeamStamp+$widthTeamName;
	  	$height = 8;
		
		//------ Tie results
		$groups = $utt->getGroups($divId);
		foreach($groups as $groupId=>$group)
		{
	  		if ($group['rund_type'] == WBS_TEAM_GROUP ||
	  			$group['rund_type'] == WBS_TEAM_BACK)
	  		{
	  			$rows = $utfpdf->getTies($groupId);

	  			$nbTeams = count($rows);
				$widthResult = (210-10-10-$widthGroupName)/$nbTeams;	  	
	  			$this->orientation = 'P';
	  			$this->AddPage('P');
	  			$this->SetFont('arial', 'I', 10);
	  			$this->Cell(0, $height, $PDF_RESULTS, '', 1, 'L', 0);

	  			$this->SetDrawColor(0);
	  			$this->SetFillColor(230);
	  			$this->SetFont('arial', 'B', 10);
	  			$this->Cell($widthGroupName, $height, $group['rund_name'], 1, 0, 'L', 1);
	  			$this->Cell(1, $height, '', '', 0);

	  			$this->SetFont('helvetica','',8);

	  			for ($i=0; $i< $nbTeams;$i++)
	  			{
	  				$this->Cell($widthResult, $height, $rows[$i]['stamp'], 1, 0, 'C');
	  			}

	  			$this->Ln();
	  			$this->Ln(1);
	  			$top = $this->getY();
	  			for ($i=0; $i< $nbTeams;$i++)
	  			{
	  				$this->Cell($widthLogo, $height, '', 1);
	  				$this->Cell($widthTeamName, $height, $rows[$i][1], 1, 0, 'L');
	  				$this->Cell($widthTeamStamp, $height, $rows[$i]['stamp'], 1, 0, 'C');
	  				$image = $rows[$i]['flag'];
	  				if (is_file($image) || substr($image, 0, 5) == 'http:')
	  				{
	  					$size = @getImagesize($rows[$i]['flag']);
	  					$few = ($widthLogo-2)/$size[0];
	  					$feh = ($height -2)/$size[1];
	  					$fe = min(1, $feh, $few);
	  					$dwidth  = $size[0] * $fe;
	  					$dheight = $size[1] * $fe;

		  				$xpos = 10 + ($widthLogo-$dwidth)/2;
	  					$ypos = $top + ($height-$dheight)/2;
	  					$this->Image($rows[$i]['flag'], $xpos, $ypos, $dwidth, $dheight);
	  				}
					
					$top += $height;
	  				$this->Cell(1, $height, '', '', 0);
	  				for($j=2; $j<$nbTeams+2; $j++)
	  				{
	  					if(isset($rows[$i][$j])!=0) $this->Cell($widthResult, $height,''.$rows[$i][$j],1,0,'C',0);
	  					else $this->Cell($widthResult, $height, "$i,$j", 1, 0, 'C', 0);
	  				}
	  				$this->Ln();
	  			}
	  			$this->Ln($height);

	  			// L�gende des colonnes
	  			$commentaires  = $PDF_RANK_COMMENTS;
	  			$this->SetFont('Arial','',8);
	  			if( $group['rund_tieranktype'] == WBS_CALC_EQUAL) $this->Cell(0, 3, $PDF_RANG_LEG1, 0, 1, 'L', 0);
	  			else $this->Cell(0, 3, $PDF_RANG_LEG11, 0, 1, 'L', 0);
	  			$this->Cell(0, 3, $PDF_RANG_LEG2, 0, 1, 'L', 0);
	  			//	$this->Cell(0,3,''.$commentaires,0,1,'L',0);
	  			$this->Ln();

	  			// Display the ranking
	  			$numKrow = 0;
	  			$nbDisplay = 0;
	  			$title = array();
	
	  			// Valeurs par defaut : hauteur, largeur,
	  			// position x et y de depart pour l'affichage des logos
	  			$hauteur_ligne =  10 ;
	  			$largeur_case  = 9 ;
	  			$taille_nom    = 68; //75 ;

	  			$rows = $utfpdf->getRankGroup($groupId);

	  			$this->SetFont('Arial','B', 10);
	  			$this->SetFillColor(230);
	  			if( $group['rund_tieranktype'] == WBS_CALC_EQUAL)
	  			{
	  				$this->Cell($taille_nom+5, $height, $PDF_RANKING, 1, 0, 'L', 1);
	  				$this->Cell(1, 10, '', '', 0);
	  				$this->SetFont('arial', '', 8);

	  				$this->Cell($largeur_case-2, $height,'Pts',1,0,'C',0);
	  				$this->Cell($largeur_case-2, $height, substr(strtoupper($PDF_PENALTIES),0,1).' ',1,0,'C',0);
	  				$this->Cell($largeur_case-2, $height, substr(strtoupper($PDF_VICTORIES),0,1).' ',1,0,'C',0);
	  				$this->Cell($largeur_case-2, $height, substr(strtoupper($PDF_MATCH_NUL),0,1).' ',1,0,'C',0);
	  			}
	  			else
	  			{
	  				$this->Cell($taille_nom, $height, $PDF_RANKING, 1, 0, 'L', 1);
	  				$this->Cell(1, 10, '', '', 0);
	  				$this->SetFont('arial', '', 8);

	  				$this->Cell($largeur_case-2, $height,'Pts',1,0,'C',0);
	  				$this->Cell($largeur_case-2, $height, substr(strtoupper($PDF_PENALTIES),0,1).' ',1,0,'C',0);
	  				$this->Cell($largeur_case-2, $height, substr(strtoupper($PDF_VICTORIES),0,1).' ',1,0,'C',0);
	  				$this->Cell($largeur_case-2, $height, 'EV', 1, 0, 'C', 0);
	  				$this->Cell($largeur_case-2, $height, 'ED', 1, 0, 'C', 0);
	  			}
	  	$this->Cell($largeur_case-2, $height, substr(strtoupper($PDF_LOSTS),0,1).' ',1,0,'C',0);
	  	$this->Cell($largeur_case-1, $height, substr(strtoupper($PDF_MATCHES),0,1).'+ ','LTB',0,'C',0);
	  	$this->Cell($largeur_case-1, $height, substr(strtoupper($PDF_MATCHES),0,1).'- ','TB',0,'C',0);
	  	$this->Cell($largeur_case-1, $height, substr(strtoupper($PDF_MATCHES),0,1).substr(strtoupper($PDF_AVERAGE),0,1).' ','RTB',0,'C',0);
	  	$this->Cell($largeur_case-1, $height, "$PDF_SMALL_GAME +",'LTB',0,'C',0);
	  	$this->Cell($largeur_case-1, $height, "$PDF_SMALL_GAME - ",'TB',0,'C',0);
	  	if ($event['evnt_scoringsystem'] != WBS_SCORING_1X5)
	  	{
	  	$this->Cell($largeur_case-1, $height, $PDF_SMALL_GAME.substr(strtoupper($PDF_AVERAGE),0,1),'RTB',0,'C',0);
	  		$this->Cell($largeur_case+2, $height, substr(strtoupper($PDF_POINTS),0,1).'+ ','LTB',0,'C',0);
	  		$this->Cell($largeur_case+2, $height, substr(strtoupper($PDF_POINTS),0,1).'- ','TB',0,'C',0);
	  		$this->Cell($largeur_case+2, $height, substr(strtoupper($PDF_POINTS),0,1).substr(strtoupper($PDF_AVERAGE),0,1).' ','RTB',1,'C',0);
	  	}
	  	else
	  	 $this->Cell($largeur_case-1, $height, $PDF_SMALL_GAME.substr(strtoupper($PDF_AVERAGE),0,1), 'RTB', 1, 'C', 0);
	  	$this->Ln(1);

	  	//Cell( w,  h,  txt, border, ln, align, fill, link)
	  	$class = 0 ;
	  	//$top = 136;
	  	$top += 24;
	  	//print_r($rows);
	  	foreach($rows as $val => $row)
	  	{
	  		$top += $hauteur_ligne;
	  		$class++;
	  		$this->SetFont('Arial', 'B', 8);
	  		$this->Cell(5, $height, $class, 1, 0, 'C', 0);               // classement
	  		$this->SetFont('helvetica', '', 8);
	  		if( $group['rund_tieranktype'] == WBS_CALC_EQUAL)
	  		{
	  			$this->Cell($taille_nom, $height, $row[1], 1, 0, 'L', 0);    // Nom de l'�quipe
	  			$this->Cell(1, 10, '', '', 0);
	  			$this->Cell($largeur_case-2, $height, $row[3],1,0,'C',0);   // Points
	  			$this->Cell($largeur_case-2, $height, $row[19],1,0,'C',0);  // Penalit�s
	  			$this->Cell($largeur_case-2, $height, $row[4],1,0,'C',0);   // Victoires
	  			$this->Cell($largeur_case-2, $height, $row[17],1,0,'C',0);  // Egalites
	  		}
	  		else
	  		{
	  			$this->Cell($taille_nom-$largeur_case+4, $height, $row[1], 1, 0, 'L', 0);    // Nom de l'�quipe
	  			$this->Cell(1, 10, '', '', 0);
	  			$this->Cell($largeur_case-2, $height, $row[3],1,0,'C',0);   // Points
	  			$this->Cell($largeur_case-2, $height, $row[19],1,0,'C',0);  // Penalit�s
	  			$this->Cell($largeur_case-2, $height, $row[4],1,0,'C',0);   // Victoires
	  			$this->Cell($largeur_case-2, $height, $row[20],1,0,'C',0);  // Nul gagnant
	  			$this->Cell($largeur_case-2, $height, $row[21],1,0,'C',0);  // Nul perdant
	  		}
	  		$this->Cell($largeur_case-2, $height, $row[5],1,0,'C',0);   // D�faites
	  		$this->Cell($largeur_case-1, $height, $row[6],1,0,'C',0);  // Matches gagn�s
	  		$this->Cell($largeur_case-1, $height, $row[7],1,0,'C',0);  // Matches perdus
	  		$this->Cell($largeur_case-1, $height, $row[8],1,0,'C',0);  // Matches diff�rence
	  		$this->Cell($largeur_case-1, $height, $row[9],1,0,'C',0);  // Sets gagn�s
	  		$this->Cell($largeur_case-1, $height, $row[10],1,0,'C',0); // Sets perdus
	  		if ($event['evnt_scoringsystem'] != WBS_SCORING_1X5)
	  		{
	  			$this->Cell($largeur_case-1, $height, $row[11], 1, 0, 'C', 0); // Sets diff�rence
	  			$this->Cell($largeur_case+2, $height, $row[12], 1, 0, 'C', 0); // Points gagn�s
	  			$this->Cell($largeur_case+2, $height, $row[13], 1, 0, 'C', 0); // Points perdus
	  			$this->Cell($largeur_case+2, $height, $row[14], 1, 1, 'C', 0); // Points diff�rence
		  	}
		  	else $this->Cell($largeur_case-1, $height, $row[11], 1, 1, 'C', 0); // Sets diff�rence
		  	
	  		if (is_file($row[16]))
	  		{
	  			$size = @getImagesize($row[16]);
	  			$few = 8/$size[0];
	  			$feh = 6/$size[1];
	  			$fe = min(1, $feh, $few);
	  			$widthLogo = $size[0] * $fe;
	  			$heightLogo = $size[1] * $fe;

	  			$xpos = 77 + (8-$widthLogo)/2;
	  			$ypos = $top + 8 + (6-$heightLogo)/2;
	  			$this->Image($row[16], $xpos, $ypos, $widthLogo, $heightLogo);
	  		}
	  	}
	  	$this->Ln();
	  }
	  else // WBS_TEAM_KO
	  {
	  	$this->orientation = 'L';
	  	$this->AddPage('L');
	  	$dt = new baseFpdf();
	  	$valeurs = array();

	  	$teams = $utt->getTeamsGroup($group['rund_id']);
	  	$names= array();
	  	foreach($teams as $team)
	  	{
	  		$logo = utimg::getPathFlag($team['asso_logo']);
	  		$names[] = array('logo' => $logo,
'value' => $team['team_noc'].
' - '.$team['team_name'],
'noc' => $team['team_noc'],
'link' => $team['team_id'],
't2r_posRound' => $team['t2r_posRound'],
	  		) ;
	  	}
	  	//	      print_r($names);
	  	require_once "utils/utko.php";
	  	$utko = new utKo($names);
	  	$debug = $utko->getExpandedValues();
	  	//    print_r($vals);
	  	//	      $kdiv2 = & $kdiv->addDiv("divDraw{$group['rund_id']}",
	  	//				       'blkGroup');
	  	//	      $kdiv2->addMsg($group['rund_name'], '', 'titre');
	  	//	      $kdraw = & $kdiv2->addDraw("draw{$group['rund_id']}");
	  	//	      $kdraw->setValues(1, $vals);
	  	$size = count($debug);
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
'score' => $team['score'],
'noc' => $team['team_noc'],
'logo' => $logo,
'link' => $team['tie_id']) ;
	  			}
	  			else
	  			{
	  				$tie = $ties[$firstTie + $i];
	  				//			  $vals[] = array('value' => $tie['tie_schedule'],
	  				$vals[] = array('value' => "",
'score' => $tie['score'],
'noc'   => "",
'logo'  => "",
'link'  => $tie['tie_id']) ;
	  			}
	  		}
	  		$valeurs[] = $vals ;
	  	}
	  	$this->affiche_KO($valeurs,$debug);
	  	//print_r($valeurs);
	  	//      $this->orientation = 'P';
	  } // end WBS_TEAM_KO

		} // end LOOP foreach($groups as $groupId=>$group)
		/**/  //           $this->Cell(0,10,'fin classments',1,1,'L',0);
		//------
		$this->Output();
		exit;
	}



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

	function affiche_KO($valeurs, $names,$result=1)
	{
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
		$yinit = 55;
		switch ($n_col)
		{
			case 5:
				$taille_bloc = 4; // fonction de $n_col
				$taille_texte = 4; // fonction de $n_col
				$largeur_bloc = 277/($n_col+1);
				break;
			case 4 :
				$taille_bloc = 8; // fonction de $n_col
				$taille_texte = 6; // fonction de $n_col
				$largeur_bloc = 277/($n_col+1);
				break;
			default:
				$taille_bloc = 12;
				$taille_texte = 6; // fonction de $n_col
				$largeur_bloc = 277/4;
				break;
		}
		$xinit = (297-(($n_col+1) * $largeur_bloc))/2;
		$xpos = $xinit;
		$ypos = 45;
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
			case 0 : $titre = 'Vainqueur';break;
			case 1 : $titre = 'Finale';break;
			case 2 : $titre = 'Demi-finale';break;
			case 3 : $titre = 'Quart de finale';break;
			default : $titre = 'Tour '.($col+1);
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

}

?>
