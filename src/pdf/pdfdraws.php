<?php
/*****************************************************************************
 !   Module     : pdf
 !   File       : $File$
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.8 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/04/03 06:49:03 $
 ******************************************************************************/
require_once "pdfbase.php";
require_once "base.php";
require_once "utils/utround.php";

class  pdfDraws extends pdfBase
{

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
		
		$xpos = 10;//$this->getX();
		$ypos = $this->getY();
		$nbLignes = count($tableau[0][0]);
		
		// DBBN - Modif taille des tableaux
		// Origine : $taille = 4.4;
		if ($nbLignes <= 16)
			$taille = 14;
		else if ($nbLignes <= 31)
			$taille = 10;
		else
			$taille = 7;

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
			
			// DBBN - modif taille nom
			$this->SetFont('Helvetica', 'B', 8);
			// DBBN - Fin
			
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
					// DBBN - modif taille score
					$this->SetFont('Helvetica', 'B', 6);
					// DBBN - Fin
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
				$nom[] = $valeurs;$nom[]= '';
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
		
		// DBBN - Modif taille des tableaux
		// Origine : $taille = 8.5;
		if ($nbLignes <= 16)
			$taille = 14;
		else if ($nbLignes <= 31)
			$taille = 10;
		else
			$taille = 5;
		
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

			// DBBN - modif taille nom
			$this->SetFont('Helvetica', 'B', 8);
			// DBBN - Fin

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
					// DBBN - modif taille score
					$this->SetFont('Helvetica', 'B', 6);
					// DBBN - Fin
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
}

?>