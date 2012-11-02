<?php
/*****************************************************************************
 !   Module     : pdf
 !   File       : $File$
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.13 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/06 07:59:33 $
 ******************************************************************************/
require_once "pdfbase.php";
require_once "base.php";
require_once "utils/utdraw.php";

class pdfSchedu extends pdfBase
{

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

		// Cr�ation du document
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
			// Ne traiter que les groupe de type KO
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
				$utko = new utKo($names);
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
					$this->SetFont('Arial','I',12);
					$str = $PDF_SCHEDULE . ' - ' . $div['draw_name'];
					$this->Cell(0, $hauteur, $str, '', 1, 'L', 0);
					$top = $this->getY();
				}

				// Nom du groupe
				$this->SetY($top);
				$this->SetFillColor(192);
				$this->SetFont('Arial', 'B', 9);
				$this->Cell(79, $hauteur, $group['rund_name'], 1, 0, 'C', 1);
				$this->Cell(1, $hauteur, '', '', 0);
					
				// Sigle des equipes en entete de colonne
				$this->SetFont('Arial', '', 8);
				$width = $fullWidth/$nbTeams;
				for ($i=0; $i< $nbTeams;$i++)
				$this->Cell($width, $hauteur,''.$rows[$i]['team_stamp'],1,0,'C',0);
					
				$this->Ln();
					
				$top += $hauteur+1;
				$this->SetFont('helvetica', '', 8);
				for ($i=0; $i< $nbTeams;$i++)
				{
					$this->SetY($top);
					$imgFile = $rows[$i]['team_logo'];
					if (is_file($imgFile) || substr($imgFile, 0, 5) == 'http:')
					{
						$size = @getImagesize($imgFile);
						$few = 8/$size[0];
						$feh = 6/$size[1];
						$fe = min(1, $feh, $few);
						$widthLogo = $size[0] * $fe;
						$heightLogo = $size[1] * $fe;

						$xpos = 10 + (10-$widthLogo)/2;
						$ypos = $top + (10-$heightLogo)/2;
						$this->Image($rows[$i]['team_logo'], $xpos, $ypos, $widthLogo, $heightLogo);
					}
					$this->Cell(10, $hauteur, '', 'LTBR', 0, 'L', 0);
					$this->Cell(56, $hauteur, $rows[$i]['team_name'], 'LTBR', 0, 'L', 0);
					$this->Cell(13, $hauteur, $rows[$i]['team_stamp'], 'LTBR', 0, 'C', 0);
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
							if ($j>$i) $ind = $j-1;
							else
							{
								$ind = $nbTeams+$j-1;
								if(!isset($rows[$i][4*$ind])) $ind = $j;
							}
							$this->SetXY($left, $top);
							$this->Cell($width,$hauteur/2, $rows[$i][4*$ind], 'LTR',0,'C',0);
							$this->SetXY($left, $top+$hauteur/2);
							$this->Cell($width, $hauteur/2, $rows[$i][(4*$ind)+1], 'LBR',0,'C',0);
						}
						$left += $width;
					}

					$this->Ln();
					$top += $hauteur;
				}
				$top += $hauteur;
			}
		}
		$this->Output();
		exit;
	}
	// }}}

	// {{{ displayProgramTeam($divId)
	/**
	 * Display the program of the division
	 *
	 * @return void
	 */
	function displayProgramTeam($divId)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require $file;

		$utfpdf =  new baseFpdf();

		// Cr�ation du document
		$this->orientation = 'P';
		$this->SetFillColor(255,255,255);

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

			$nbCells = 1;
			foreach($times as $time) $nbCells = max(count($time), $nbCells);
			$nbCellsLimited = min($nbCells-1, 5);
			if (!$nbCellsLimited) $nbCellsLimited = 1;
				
			$nbCellsLimited = 5;
			$witdh = 173/($nbCellsLimited);

			$nbLines = 0;
				
			foreach($times as $time)  $nbLines += ceil(((count($time)-1)/$nbCellsLimited));
			//Calcul de la hauteur du groupe
			// S'il depasse, sauter une page
			$left = 10;
			if (($top + $nbLines*$hauteur) > 270)
			{
				$this->AddPage('P');

				$ypos = $this->_getTop();
				// Nom du tableau
				$this->SetFont('Arial','I',12);
				$this->SetTextColor(0);
				$this->setXY($left,$ypos);
				$str = $PDF_PROGRAM . ' ' . $div['draw_name'];
				$this->Cell(0, 8, $str, 0, 0, 'L', 0);
				$ypos += 7;
				$top = $ypos;
			}
			$this->SetXY($left, $top);
			$this->SetFont('Arial','B',14);
			$this->SetFillColor(192);
			$this->Cell(100, 8, $fullDate . '-'. $nbLines, 1, 0, 'C', 1);

			$posY = $top+11;
			$hauteur = 18;
			//	  print_r($times);echo "<br>";
			// 	  $nbCells = 1;
			// 	  foreach($times as $time)
			// 	    $nbCells = max(count($time), $nbCells);
			// 	  $nbCellsLimited = ($nbCells > 5) ? 5 : $nbCells ;
			// 	  $witdh = 173/($nbCellsLimited-1);

			$startY = $posY;
			//print_r($times);
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
				$this->Line($left, $startY, $left+17, $startY);
				$this->Line($left, $startY+$hauteur,  $left+17, $startY+$hauteur);

				$posX=$left+17;
				$nbCells = count($time);
				for($i=1; $i<$nbCells; $i++)
				{
					if(($i%6)==0)
					{
						$posY += $hauteur;
						$posX = $left + 17;
					}

					$this->SetXY($posX, $posY);
					if (isset($time[$i]))
					{
						$lines = $time[$i];
						$this->SetFont('Arial','',8);
						$this->Cell($witdh-8, $hauteur/3, $lines['name'], 'LTRB', 0, 'C', 0);

						$this->SetFont('Arial','B',8);
						$this->Cell(8, $hauteur/3, $lines['court'], 'LTRB', 0, 'C', 0);
						$this->SetXY($posX, $posY+($hauteur/3));
						$this->SetFont('Arial','B',10);
						$this->Cell($witdh, $hauteur/3, $lines['stamp'], 'LTR', 0, 'C', 0);
						$this->SetFont('Arial','',8);
						$this->SetXY($posX, $posY+ 2*($hauteur/3));
						$this->Cell($witdh, $hauteur/3, $lines['venue'], 'LBR', 0, 'C', 0);
						$posX += $witdh;

					}
					else $this->Cell($witdh, $hauteur, '', 'LTBR', 0, 'C', 0);
				}
				//if($posY > 297-50-$hauteur)
				//{
				//  $this->AddPage('P');
				//  $posY = 48;
				//}
				//else
				$posY += $hauteur+4;
			}
			$top = $posY+8;
		}

		return;
	}
	// }}}


	// {{{ displayProgram()
	/**
	 * Display a program

	 * @access public
	 * @param  string  $title
	 * @param  array   $dates
	 *      $date = $dates[0]
	 *      $date['date']  = "01-05-06"
	 *      $times = $date['times']
	 *      $time = $times[0]
	 *      $time['time'] = "13:00"
	 *
	 *      $lines = $time[0];
	 *      $lines['num'] = "5";
	 *      $lines['round'] = "DIV A" | "SH NC" ;
	 *      $lines['step']  = "Qualification" | "Consolante" | "Poule A";
	 *      $lines['value'] = "VCT-USRB" | "FRA-DEN" | "Huitieme" | "1/3";
	 *
	 *      ---------------------
	 *      |  round   |  num   |
	 *      ---------------------
	 *      |       step        |
	 *      |      value        |
	 *      ---------------------
	 * @return void
	 * Data are
	 *
	 * @return void
	 */
	function displayProgram($title, $dates)
	{
		$file = "lang/".utvars::getLanguage()."/pdf.inc";
		require $file;

		$utfpdf =  new baseFpdf();

		// Creation du document
		$this->SetFillColor(255,255,255);
		$this->SetAutoPageBreak(false);

		// DBBN - Modifs des couleurs pour les echeanciers => passage en niveaux de gris

		// Couleurs homme
		//$r[] = array(0xfa, 0xfa, 0xe1, 0x62, 0x3c, 0x3C, 0x85, 0xEA, 0xCF);
		//$g[] = array(0x3c, 0xca, 0xfa, 0xfa, 0xfa, 0x77, 0x3c, 0x3c, 0xCA);
		//$b[] = array(0x3c, 0x3c, 0x3c, 0x3c, 0xdd, 0xfa, 0xfa, 0xfa, 0xCA);
		// Couleurs femmes
		//$r[] = array(0xfa, 0xfa, 0xe8, 0x94, 0x7a, 0x7a, 0xab, 0xf0, 0xCF);
		//$g[] = array(0x7a, 0xda, 0xfa, 0xfa, 0xfa, 0xa2, 0x7a, 0x7a, 0xEA);
		//$b[] = array(0x7a, 0x7a, 0x7a, 0x7a, 0xe7, 0x7a, 0xfa, 0xfa, 0xEA);
		$r[] = array(204, 204, 204, 204, 204, 204, 204, 204, 204);
		$g[] = array(204, 204, 204, 204, 204, 204, 204, 204, 204);
		$b[] = array(204, 204, 204, 204, 204, 204, 204, 204, 204);
		// Couleurs femmes
		$r[] = array(204, 204, 204, 204, 204, 204, 204, 204, 204);
		$g[] = array(204, 204, 204, 204, 204, 204, 204, 204, 204);
		$b[] = array(204, 204, 204, 204, 204, 204, 204, 204, 204);
		$ff = array(1=>0,1,0,1,1);

		$numSerie = 0;
		$utd = new utdraw();
		$serials = $utd->getSerialsList();
		foreach($serials as $serial)
		{
			$serie[$serial] = $numSerie++;
		}
		$nbColor = count($r[0]);
		//echo "========$nbColor=========";
		
		foreach($dates as $date)
		{
			$page = 1;
			$times = $date['times'];

			// Calcule de la largeur des colonnes
			// Compter le nombre de colonne max sur une ligne
			$nbCells = 1;
			$timeWidth = 15;
			foreach($times as $time)
			$nbCells = max(count($time)-1, $nbCells);

			// DBBN - Modif pagination en paysage pour 8 terrains
			//if ($nbCells > 8)
			if ($nbCells > 7)
			{
				$this->AddPage('L');
				$this->orientation = 'L';
			}
			else
			{
				$this->AddPage('P');
				$this->orientation = 'P';
			}
			$width = ($this->_getAvailableWidth()-$timeWidth)/$nbCells;

			// Titre
			$top = $this->_getTop();
			$left = $this->_getLeft();
			$this->SetXY($left, $top);
			$this->SetFont('arial','I', 12);
			$this->SetTextColor(0);
			$this->SetFillColor(0);
			$fullDate = $date['fulldate'];
			$str = $fullDate . ' - ' . $title;
			$this->Cell(0, 10, $str, 0, 1, 'L', 0);

			$this->setDocTitle("$title $fullDate");

			$warning = $PDF_PROTECT;
			$this->SetFont('arial','',10);
			$this->SetTextColor(255,0,0);
			$this->Cell(0, 10, $warning, '', 1, 'C', 0);
			$this->SetTextColor(0);

			//Calcul de la hauteur d'une rangee :
			// Une rangee est composee de trois lignes.
			// Il y a une rangee par creneau horaire
			$nbRows = count($times);
			$maxHeight = $this->_getAvailableHeight()-12-12;
			$lineHeight = intval($maxHeight /(3*$nbRows));
			if ($lineHeight > 8) $lineHeight = 8;
			if ($lineHeight < 4) $lineHeight =4;
			if ($nbCells > 4) $rowHeight = 3*$lineHeight;
			else $rowHeight = 5*$lineHeight;

			$posX = $left;
			$top += 20;
			$posY = $top;
			// Traiter chaque rangee
			foreach($times as $time)
			{
				$posX = $left;
				$this->SetXY($posX, $posY);
				$this->SetFont('Arial','B',14);
				$this->SetTextColor(255);
				$this->SetDrawColor(255);
				$this->SetFillColor(0);

				//  Affichage de l'heure
				$this->Cell($timeWidth, $rowHeight,
				$time['time'], 'TB', 1, 'C', 1);
				$this->SetTextColor(0);
				$this->SetDrawColor(0);

				$posX += $timeWidth;
				for($i=0; $i<$nbCells; $i++)
				{
					$this->SetXY($posX, $posY);
					if (isset($time[$i]))
					{
						$lines = $time[$i];
						$numColor = $serie[$lines['serial']] % $nbColor;
						$ton      = $ff[$lines['disci']];
						$this->SetFont('Arial','',8);
						$this->SetFillColor($r[$ton][$numColor],
						$g[$ton][$numColor],
						$b[$ton][$numColor]);

						$this->Cell($width-9, $lineHeight, $lines['round'],
				  'LTRB', 0, 'C', 1);

						$this->SetFont('Arial','B',8);
						if ($lines['disci'] == WBS_MX)
						{
							$this->Cell(9, $lineHeight, $lines['num'],
      				  'LTRB', 0, 'C', 1);
						}
						else
						{
							$this->Cell(9, $lineHeight, $lines['num'],
      				  'LTRB', 0, 'C', 0);
						}

						$this->SetXY($posX, $posY + $lineHeight);
						$this->SetFont('Arial','I',8);
						$this->Cell($width, $lineHeight, $lines['step'],
				  'LTR', 0, 'C', 0);

						$this->SetXY($posX, $posY+ 2*$lineHeight);
						$this->SetFont('Arial','I',8);
						$this->Cell($width, $lineHeight, $lines['value'],
				  'LRB', 0, 'C', 0);		      

						if ($nbCells < 5)
						{
							$this->SetXY($posX, $posY+ 3*$lineHeight);
							$this->SetFont('Arial','I',6);
							$str = $lines['player1'];
							if (!empty($lines['player2']) ) $str .= ' - ' . $lines['player2'];
							$this->Cell($width, $lineHeight, $str, 'LR', 0, 'C', 0);

							$this->SetXY($posX, $posY+ 4*$lineHeight);
							$this->SetFont('Arial','I',6);
							$str = $lines['player3'];
							if (!empty($lines['player4']) ) $str .= ' - ' . $lines['player4'];
							$this->Cell($width, $lineHeight, $str, 'LBR', 0, 'C', 0);
						}
					}
					$posX += $width;
				}
				$posY += $rowHeight;
				// DBBN - Modif pour changement de page
				// Origine : if($posY+$rowHeight > $this->_getBottom())
				if($posY+$rowHeight-5 > $this->_getBottom())
				{
					$this->Line($left, $top, $left+$timeWidth, $top);
					$this->Line($left, $posY, $left+$timeWidth, $posY);
					//		  $this->SetXY($left, $this->_getBottom()-3);
					//$this->SetFont('Arial','B',6);
					//$this->Cell(0, 3, "$title $fullDate -$page-",
					//	      0, 0, 'R', 0);
					//$page++;
					
					// DBBN - Modif pagination en paysage pour 8 terrains
					//if ($nbCells > 8)
					if ($nbCells > 7)
					{
						// DBBN - Modif pour placer footer avec la mise en page de la page a fermer
						//$this->orientation = 'L';
						$this->AddPage('L');
						$this->orientation = 'L';
					}
					else
					{
						// DBBN - Modif pour placer footer avec la mise en page de la page a fermer
						// $this->orientation = 'P';
						$this->AddPage('P');
						$this->orientation = 'P';
					}
					$top = $this->_getTop();
					$posY = $top;
					$this->Line($left, $posY, $left+$timeWidth, $posY);
				}
			}
			$this->Line($left, $top, $left+$timeWidth, $top);
			$this->Line($left, $posY, $left+$timeWidth, $posY);
		}
		return;
	}
	// }}}


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

		$xinit = (297-(($n_col+1) * $largeur_bloc))/2;
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
			default : $titre = 'Tour '.$col;
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