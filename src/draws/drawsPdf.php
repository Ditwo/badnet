<?php
/*****************************************************************************
 !   Module     : Draws
 !   File       : $Source: /cvsroot/aotb/badnet/src/draws/drawsPdf.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.25 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:24 $
 ******************************************************************************/

require_once "draws.inc";
require_once "base_A.php";
require_once "utils/utpage_A.php";
require_once "utils/utdraw.php";
require_once "utils/utround.php";
require_once "pairs/pairs.inc";

/**
 * Module de gestion des tournois
 *
 * @author Gerard CANTEGRIL <cage@aotb.org>
 * @see to follow
 *
 */

class DrawsPdf
{

	// {{{ properties

	/**
	 * Utils objet
	 *
	 * @var     object
	 * @access  private
	 */
	var $_ut;

	/**
	 * Database access object
	 *
	 * @var     object
	 * @access  private
	 */
	var $_dt;

	// }}}

	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function drawsPdf()
	{
		$this->_ut = new Utils();
		$this->_dt = new DrawsBase_A();
		$this->_utd = new utdraw();
	}
	// }}}

	// {{{ start()
	/**
	 * Start the connexion processus
	 *
	 * @access public
	 * @return void
	 */
	function start($page)
	{
		$ut = $this->_ut;
		$id = kform::getData();

		switch ($page)
		{
	  // Display definition and players of a draw in PDF document
			case DRAW_PDF_PAIRSIBF:
				$this->_pdfDrawPairsIbf();
				break;
				// Display definition and players of a draw in PDF document
			case DRAW_PDF_PAIRS:
				$this->_pdfDrawPairs();
				break;

			case DRAW_PDF_GROUP_KO:
				list($drawId, $groupName) = explode(';', kform::getData());
				$ids = array();
				$utr = new utround();

				$rounds = $utr->getRounds($drawId, WBS_ROUND_MAINDRAW, null, $groupName);
				foreach($rounds as $cur) $ids[] = $cur['rund_id'];
				$rounds = $utr->getRounds($drawId, WBS_ROUND_THIRD, null, $groupName);
				foreach($rounds as $cur) $ids[] = $cur['rund_id'];
				$rounds = $utr->getRounds($drawId, WBS_ROUND_QUALIF, null, $groupName);
				foreach($rounds as $cur) $ids[] = $cur['rund_id'];
				$rounds = $utr->getRounds($drawId, WBS_ROUND_CONSOL, 'rund_rge', $groupName);
				foreach($rounds as $cur) $ids[] = $cur['rund_id'];
				$rounds = $utr->getRounds($drawId, WBS_ROUND_PLATEAU, 'rund_rge', $groupName);
				foreach($rounds as $cur) $ids[] = $cur['rund_id'];

				require_once "pdf/pdfdraws.php";
				$pdf = new pdfDraws();
				$pdf->start();
				$pdf->orientation = 'P';
				$pdf->AddPage('P');
				
				foreach($ids as $id)
				{
					$this->_pdfDrawKo(0, $id, $pdf);
				}
				$pdf->end();
				exit;
				break;

			case DRAW_PDF_ALL_KO:
				$roundIds = kform::getInput("rowsDraws", array());
				$ids = array();
				$oGroup = new objgroup();
				$utr = new utround();
				foreach($roundIds as $roundId)
				{
					$round = $utr->getRoundDef($roundId);
					if ($round['rund_type'] != WBS_ROUND_GROUP ) $ids[] = $roundId;
				}
				if (empty($ids))
				{
					$utpage = new utPage('draws');
					$content =& $utpage->getPage();
					$content->addWng('msgNeedGroups');
					$content->addBtn('btnCancel');
					$elts = array('btnCancel');
					$utpage->display();
					exit;
				}

				require_once "pdf/pdfdraws.php";
				$pdf = new pdfDraws();
				$pdf->start();
				$pdf->orientation = 'P';
				$pdf->AddPage('P');
				
				foreach($ids as $id)
				{
					$this->_pdfDrawKo(0, $id, $pdf);
				}
				$pdf->end();
				exit;

			case DRAW_PDF_STATS:
				$this->_pdfStats();
				break;

			case DRAW_PDF_KO:
				require_once "pdf/pdfdraws.php";
				$pdf = new pdfDraws();
				$pdf->start();
				$roundId = kform::getData();
				$pdf->orientation = 'P';
				$pdf->AddPage('P');
				$this->_pdfDrawKo(0, $roundId, $pdf);
				$pdf->end();
				exit;
				break;

			case DRAW_PDF_ALL_GROUPS:
				$roundIds = kform::getInput("rowsDraws", array());
				$ids = array();
				$oGroup = new objgroup();
				$utr = new utround();
				foreach($roundIds as $roundId)
				{
					$round = $utr->getRoundDef($roundId);
					$id = $round['rund_drawId'] . ';' . $round['rund_group'];
					if (! in_array($id, $ids) ) $ids[] = $id;
				}
				if (empty($ids))
				{
					$utpage = new utPage('draws');
					$content =& $utpage->getPage();
					$content->addWng('msgNeedGroups');
					$content->addBtn('btnCancel');
					$elts = array('btnCancel');
					$utpage->display();
					exit;
				}

				require_once "pdf/pdfgroups.php";
				require_once "pdf/pdfdraws.php";
				$pdf = new pdfGroups();
				$pdf->start('P');
				$isFirst = true;
				foreach($ids as $id)
				{
					// Imprimer les poules
					$this->_pdfDrawGroupNew($id, $pdf, $isFirst);
						
					// DBBN - Supprime l'impression du tableau apres les poules
					/*
					// Imprimer le tableau
					list($drawId, $group) = explode(';', $id);
					$dids = $utr->getRounds($drawId, WBS_ROUND_MAINDRAW, null, $group);
					if ( !empty($dids) )
					{
						$did = reset($dids);
						$this->_pdfDrawKo(0, $did['rund_id'], $pdf);
					}
					*/
					$isFirst = false;
				}
				$pdf->end();
				exit;
				break;

			case DRAW_PDF_GROUPS:
				require_once "pdf/pdfgroups.php";
				$pdf = new pdfGroups();
				$pdf->start('P');
				$drawId = kform::getData();
				$this->_pdfDrawGroupNew($drawId, $pdf, true);
				// Imprimer le tableau
				list($drawId, $group) = explode(';', $id);
				
				// DBBN - Supprime l'impression du tableau apres les poules
				/*
				$utr = new utround();
				$dids = $utr->getRounds($drawId, WBS_ROUND_MAINDRAW, null, $group);
				if ( !empty($dids) )
				{
					$did = reset($dids);
					$this->_pdfDrawKo(0, $did['rund_id'], $pdf);
				}
				*/

				$pdf->end();
				exit;
				break;

			case DRAW_PDF_KO_IBF:
				require_once "pdf/pdfdraws.php";
				$pdf = new pdfDraws();
				$roundId = kform::getData();
				$this->_pdfDrawKo(1, $roundId, $pdf);
				$pdf->end();
				break;

			case DRAW_XLS_STATS:
				$roundIds = kform::getInput("rowsDraws", array());
				$ids = array();
				$oGroup = new objgroup();
				$utr = new utround();
				foreach($roundIds as $roundId)
				{
					$round = $utr->getRoundDef($roundId);
					if ($round['rund_type'] != WBS_ROUND_GROUP ) $ids[] = $roundId;
				}
				if (empty($ids))
				{
					$utpage = new utPage('draws');
					$content =& $utpage->getPage();
					$content->addWng('msgNeedGroups');
					$content->addBtn('btnCancel');
					$elts = array('btnCancel');
					$utpage->display();
					exit;
				}

				require_once 'Spreadsheet/Writer.php';
					
				// Creation d'un manuel de travail
				$workbook = new Spreadsheet_Excel_Writer();

				// Envoi des en-tetes HTTP
				$workbook->send('draws.xls');

				foreach($ids as $id)
				{
					$this->_xlsDrawStat($id, $workbook);
				}
				// Envoi du fichier
				$workbook->close();
				exit;
				break;

			case DRAW_XLS_DRAWS:
				$roundIds = kform::getInput("rowsDraws", array());
				$ids = array();
				$oGroup = new objgroup();
				$utr = new utround();
				foreach($roundIds as $roundId)
				{
					$round = $utr->getRoundDef($roundId);
					if ($round['rund_type'] != WBS_ROUND_GROUP ) $ids[] = $roundId;
				}
				if (empty($ids))
				{
					$utpage = new utPage('draws');
					$content =& $utpage->getPage();
					$content->addWng('msgNeedGroups');
					$content->addBtn('btnCancel');
					$elts = array('btnCancel');
					$utpage->display();
					exit;
				}

				require_once 'Spreadsheet/Writer.php';
					
				// Creation d'un manuel de travail
				$workbook = new Spreadsheet_Excel_Writer();
				// Envoi des en-tetes HTTP
				$workbook->send('draws.xls');

				foreach($ids as $id)
				{
					$this->_xlsDrawKo($id, $workbook);
				}

				// Envoi du fichier
				$workbook->close();
				exit;
				break;

			case DRAW_XLS_PAIRS:
				$roundIds = kform::getInput("rowsDraws", array());
				$drawIds = array();
				$oGroup = new objgroup();
				$utr = new utround();
				foreach($roundIds as $roundId)
				{
					$round = $utr->getRoundDef($roundId);
					$drawId  = $round['rund_drawId'];
					if (!in_array($drawId, $drawIds) ) $drawIds[] = $drawId;
				}

				if (empty($drawIds))
				{
					$drawId = kform::getData();
					if (!empty($drawId)) $drawIds[] = $drawId;
				}

				if (empty($drawIds))
				{
					$utpage = new utPage('draws');
					$content =& $utpage->getPage();
					$content->addWng('msgNeedGroups');
					$content->addBtn('btnCancel');
					$elts = array('btnCancel');
					$utpage->display();
					exit;
				}

				require_once 'Spreadsheet/Writer.php';
					
				// Creation d'un manuel de travail
				$workbook = new Spreadsheet_Excel_Writer();
				//$workbook = '';
				// Envoi des en-tetes HTTP
				$workbook->send('draws.xls');

				foreach($drawIds as $drawId)
				{
					$this->_xlsPairs($drawId, $workbook);
				}
				// Envoi du fichier
				$workbook->close();
				exit;
				break;

			case DRAW_XLS_PAIRS2:
				$roundIds = kform::getInput("rowsDraws", array());
				$drawIds = array();
				$oGroup = new objgroup();
				$utr = new utround();
				foreach($roundIds as $roundId)
				{
					$round = $utr->getRoundDef($roundId);
					$drawId  = $round['rund_drawId'];
					if (!in_array($drawId, $drawIds) ) $drawIds[] = $drawId;
				}

				if (empty($drawIds))
				{
					$utpage = new utPage('draws');
					$content =& $utpage->getPage();
					$content->addWng('msgNeedGroups');
					$content->addBtn('btnCancel');
					$elts = array('btnCancel');
					$utpage->display();
					exit;
				}

				require_once 'Spreadsheet/Writer.php';
					
				// Creation d'un manuel de travail
				$workbook = new Spreadsheet_Excel_Writer();
				//$workbook = '';
				// Envoi des en-tetes HTTP
				$workbook->send('draws.xls');

				foreach($drawIds as $drawId)
				{
					$this->_xlsPairs2($drawId, $workbook);
				}
				// Envoi du fichier
				$workbook->close();
				exit;
				break;

			default:
				echo "page $page demand Ã¸e depuis drawsPdf<br>";
				exit();
		}
	}
	// }}}

	// {{{ _xlsPairs2()
	/**
	 * Display a xls document with the pairs
	 *
	 * @access private
	 * @return void
	 */
	function _xlsPairs2($drawId, &$wb)
	{
		$ut = $this->_ut;
		$utd = $this->_utd;
		$dt = $this->_dt;
		$utr = new utround();

		$draw = $utd->getDrawById($drawId);
		$pairs = $this->_dt->getDrawPairs($drawId, 9);
		//      print_r($pairs);
		//exit;

		// Creation d'une feuille de travail
		$worksheet =& $wb->addWorksheet($draw['draw_stamp']);
		$worksheet->hideGridlines();
		$format_title =& $wb->addFormat(array('top' => 1, 'left' => 1, 'right' => 1,
					    'bold' => 1, 'align'=> 'center' ));
		$format_tlr   =& $wb->addFormat(array('top' => 1, 'left' => 1, 'right' => 1));
		$format_blr   =& $wb->addFormat(array('left' => 1, 'bottom' => 1, 'right' => 1));

		$format_tlrm   =& $wb->addFormat(array('top' => 1, 'left' => 1,
					     'right' => 1,'fgcolor' => 3));
		$format_blrm   =& $wb->addFormat(array('left' => 1, 'bottom' => 1,
					     'right' => 1, 'fgcolor' => 3));
		$format_tlrq   =& $wb->addFormat(array('top' => 1, 'left' => 1,
					     'right' => 1, 'fgcolor' => 7));
		$format_blrq   =& $wb->addFormat(array('left' => 1, 'bottom' => 1,
					     'right' => 1, 'fgcolor' => 7));

		$format_t     =& $wb->addFormat(array('top' => 1));
		$format_name  =& $wb->addFormat(array('bold' => 1));

		// Nom du tableau
		$worksheet->write(0, 0, $draw['draw_name'], $format_name);
		$line = 2;
		$worksheet->setcolumn(0, 0, 5);
		$worksheet->write($line, 0, '#',        $format_title);
		$worksheet->setcolumn(1, 1, 10);
		$worksheet->write($line, 1, "Number",   $format_title);
		$worksheet->setcolumn(2, 2, 5);
		$worksheet->write($line, 2, "NOC",      $format_title);
		$worksheet->setcolumn(3, 3, 10);
		$worksheet->write($line, 3, "Nat rank", $format_title);
		$worksheet->setcolumn(4, 4, 12);
		$worksheet->write($line, 4, "IBF Num",  $format_title);
		$worksheet->setcolumn(5, 5, 40);
		$worksheet->write($line, 5, "Players",  $format_title);
		$worksheet->setcolumn(6, 6, 10);
		$worksheet->write($line, 6, "Pos",      $format_title);
		$worksheet->setcolumn(7, 7, 10);
		$worksheet->write($line, 7, "Evol",     $format_title);

		$line++;
		$numPairs = 1;
		$pairNoc = '';
		foreach($pairs as $pair)
		{
	  if (!isset($pair['regi_longName']))
	  continue;

	  if ($pair['pair_noc'] !=  $pairNoc)
	  {
	  	$pairNoc = $pair['pair_noc'];
	  	$num = 0;
	  }
	  $num++;
	  $players = $pair['regi_longName'];
	  $newPair = true;
	  foreach($players as $player)
	  {
	  	$row=array();
	  	if ($newPair==true)
	  	{
	  		$worksheet->write($line, 0, $numPairs++, $format_tlr);
	  		$worksheet->write($line, 1, $num, $format_tlr);
	  		$worksheet->write($line, 2, $player['noc'], $format_tlr);
	  		$worksheet->write($line, 3, $pair['pair_natRank'], $format_tlr);
	  		$worksheet->write($line, 4, $player['ibfNum'], $format_tlr);
	  		$worksheet->write($line, 5, $player['value'], $format_tlr);
	  		if ($pair['pair_status'][0] == 'M')
	  		{
	  			$worksheet->write($line, 6, $pair['pair_status'], $format_tlrm);
	  		}
	  		else
	  		{
	  			$worksheet->write($line, 6, $pair['pair_status'], $format_tlrq);
	  		}
	  		$worksheet->write($line, 7, $pair['pair_wo'], $format_tlr);
	  		$noc = $player['noc'];
	  	}
	  	else
	  	{
	  		$worksheet->write($line, 0, '', $format_blr);
	  		$worksheet->write($line, 1, '', $format_blr);
	  		if ($player['noc'] != $noc)
	  		$worksheet->write($line, 2, $player['noc'], $format_blr);
	  		else
	  		$worksheet->write($line, 2, '', $format_blr);
	  		$worksheet->write($line, 3, '', $format_blr);
	  		$worksheet->write($line, 4, $player['ibfNum'], $format_blr);
	  		$worksheet->write($line, 5, $player['value'], $format_blr);
	  		if ($pair['pair_status'][0] == 'M')
	  		{
	  			$worksheet->write($line, 6, '', $format_blrm);
	  		}
	  		else
	  		{
	  			$worksheet->write($line, 6, '', $format_blrq);
	  		}
	  		$worksheet->write($line, 7, '', $format_blr);
	  	}
	  	$newPair = false;
	  	$line++;
	  }
	  $worksheet->write($line, 0, '', $format_t);
	  $worksheet->write($line, 1, '', $format_t);
	  $worksheet->write($line, 2, '', $format_t);
	  $worksheet->write($line, 3, '', $format_t);
	  $worksheet->write($line, 4, '', $format_t);
	  $worksheet->write($line, 5, '', $format_t);
	  $worksheet->write($line, 6, '', $format_t);
	  $worksheet->write($line, 7, '', $format_t);
		}
		$worksheet->write($line+1, 0, 'Document generated by BadNet (http://www.badnet.org)');
		return;
	}
	// }}}


	// {{{ _xlsPairs()
	/**
	 * Display a xls document with the pairs
	 *
	 * @access private
	 * @return void
	 */
	function _xlsPairs($drawId, &$wb)
	{
		$ut = $this->_ut;
		$utd = $this->_utd;
		$dt = $this->_dt;
		$utr = new utround();

		$draw = $utd->getDrawById($drawId);
		$sort = kform::getSort("rowsPairs", 2);
		$pairs = $this->_dt->getDrawPairsForPdf($drawId, $sort);
		//print_r($pairs);

		// Creation d'une feuille de travail
		$worksheet =& $wb->addWorksheet($draw['draw_stamp']);
		$worksheet->hideGridlines();
		$format_title =& $wb->addFormat(array('top' => 1, 'left' => 1, 'right' => 1,
					    'bold' => 1, 'align'=> 'center' ));
		$format_tlr   =& $wb->addFormat(array('top' => 1, 'left' => 1, 'right' => 1));
		$format_blr   =& $wb->addFormat(array('left' => 1, 'bottom' => 1, 'right' => 1));
		$format_t     =& $wb->addFormat(array('top' => 1));
		$format_name  =& $wb->addFormat(array('bold' => 1));

		// Nom du tableau
		$worksheet->write(0, 0, $draw['draw_name'], $format_name);
		$line = 2;
		$worksheet->setcolumn(0, 0, 5);
		$worksheet->write($line, 0, '#',        $format_title);
		$worksheet->setcolumn(1, 1, 5);
		$worksheet->write($line, 1, "N°",   $format_title);
		$worksheet->setcolumn(2, 2, 10);
		$worksheet->write($line, 2, "Licence", $format_title);
		$worksheet->setcolumn(3, 3, 30);
		$worksheet->write($line, 3, "Joueurs",  $format_title);
		$worksheet->setcolumn(4, 4, 10);
		$worksheet->write($line, 4, "Catégorie", $format_title);
		$worksheet->setcolumn(5, 5, 10);
		$worksheet->write($line, 5, "Classement", $format_title);
		$worksheet->setcolumn(6, 6, 10);
		$worksheet->write($line, 6, "Points", $format_title);
		$worksheet->setcolumn(7, 7, 10);
		$worksheet->write($line, 7, "Rang", $format_title);

		$line++;
		$numPairs = 1;
		$pairNoc = '';
		foreach($pairs as $pair)
		{
	  if ($pair['pair_state'] == WBS_PAIRSTATE_NOK)
	  continue;
	  if ($pair['pair_noc'] !=  $pairNoc)
	  {
	  	$pairNoc = $pair['pair_noc'];
	  	$num = 0;
	  }
	  $num++;
	  $players = $pair['regi_longName'];
	  $newPair = true;
	  foreach($players as $player)
	  {
	  	$row=array();
	  	if ($newPair==true)
	  	{
	  		$worksheet->write($line, 0, $numPairs++, $format_tlr);
	  		$worksheet->write($line, 1, $num, $format_tlr);
	  		$worksheet->write($line, 2, $player['licence'], $format_tlr);
	  		$worksheet->write($line, 3, $player['player'], $format_tlr);
	  		$worksheet->write($line, 4, $player['catage'], $format_tlr);
	  		$worksheet->write($line, 5, $player['level'], $format_tlr);
	  		$worksheet->write($line, 6, sprintf('%.02f', $player['points']), $format_tlr);
	  		$worksheet->write($line, 7, $player['rank'], $format_tlr);
	  		$noc = $player['noc'];
	  	}
	  	else
	  	{
	  		$worksheet->write($line, 0, '', $format_blr);
	  		$worksheet->write($line, 1, '', $format_blr);
	  		$worksheet->write($line, 2, $player['licence'], $format_blr);
	  		$worksheet->write($line, 3, $player['player'], $format_blr);
	  		$worksheet->write($line, 4, $player['catage'], $format_blr);
	  		$worksheet->write($line, 5, $player['level'], $format_blr);
	  		$worksheet->write($line, 6, sprintf('%.02f', $player['points']), $format_blr);
	  		$worksheet->write($line, 7, $player['rank'], $format_blr);
	  	}
	  	$newPair = false;
	  	$line++;
	  }
	  $worksheet->write($line, 0, '', $format_t);
	  $worksheet->write($line, 1, '', $format_t);
	  $worksheet->write($line, 2, '', $format_t);
	  $worksheet->write($line, 3, '', $format_t);
	  $worksheet->write($line, 4, '', $format_t);
	  $worksheet->write($line, 5, '', $format_t);
		}
		$worksheet->write($line+1, 0, 'Document generated by BadNet (http://www.badnet.org)');
		return;
	}
	// }}}


	// {{{ _xlsDrawStat()
	/**
	 * Display a xls document with the stat of the matches
	 *
	 * @access private
	 * @return void
	 */
	function _xlsDrawStat($roundId, &$wb)
	{
		$ut = $this->_ut;
		$utd = $this->_utd;
		$dt = $this->_dt;
		$utr = new utround();

		$drawType =  $dt->getDrawType($roundId);

		// Crï¿½ation d'une feuille de travail
		$name =$drawType['rund_group'] . '-' . $drawType['draw_stamp'] . ' (' . $drawType['rund_stamp'] . ')';
		$worksheet =& $wb->addWorksheet($name);

		// Nom du tableau
		$worksheet->write(0, 0, $drawType['rund_group'] . '-' . $drawType['draw_name']);

		//--- First collumn  of the draw
		$pairs = $dt->getRoundPairs($roundId);
		$round = $utr->getRoundDef($roundId);
		require_once "utils/utko.php";
		$utko = new utKo($pairs, $round['rund_entries']);
		$vals = $utko->getExpandedValues();

		$i = 4;
		$size = $round['rund_size'];
		$winners = $dt->getWinners($roundId);

		$allTies = $utr->getMatchs($roundId);
		if (isset($ties['errMsg'])) return $ties;

		// Construc a table with the ties
		$ties = array();
		foreach($allTies as $data)
		$ties[$data['tie_posRound']] = $data;
		$numCol = 0 ;
		$start = 3;
		while( ($size/=2) >= 1)
		{
	  $numLin = $start;
	  $vals = array();
	  $firstTie = $size-1;
	  for($i=0; $i < $size; $i++)
	  {
	  	$score = '';
	  	if (isset($winners[$firstTie + $i]))
	  	{
	  		$winner = $winners[$firstTie + $i];
	  		$score = $winner['score'];
	  		if (preg_match("/.*\(([0-9]*) mn\)/", $score, $res))
	  		$worksheet->write($numLin++, $numCol, $res[1]);
	  	}
	  }
	  $numCol++;
		}
		for ($i=1; $i<=$numCol; $i++)
		{
	  $title = $ut->getLabel(WBS_WINNER + $i);
	  $worksheet->write(2,$numCol-$i, $title);
		}
		return;
	}
	// }}}


	// {{{ _xlsDrawKo()
	/**
	 * Display a xls document with the current round
	 *
	 * @access private
	 * @return void
	 */
	function _xlsDrawKo($roundId, &$wb)
	{
		$ut = $this->_ut;
		$utd = $this->_utd;
		$dt = $this->_dt;
		$utr = new utround();

		$drawType =  $dt->getDrawType($roundId);

		// Creation d'une feuille de travail
		$name = $drawType['rund_group'] . '-' . $drawType['draw_stamp'] . ' ' . $drawType['rund_stamp'];
		$worksheet =& $wb->addWorksheet($name);

		// Nom du tableau
		$worksheet->write(0, 0, $drawType['rund_group'] . '-' . $drawType['draw_name']);

		//--- First collumn  of the draw
		$pairs = $dt->getRoundPairs($roundId);
		$round = $utr->getRoundDef($roundId);
		require_once "utils/utko.php";
		$utko = new utKo($pairs, $round['rund_entries']);
		$vals = $utko->getExpandedValues();

		$i = 4;
		foreach($vals as $val)
		{
	  if (!is_array($val))
	  {
	  	$tds = '';
	  	$status = '';
	  	$value = $val;
	  	$ibfn = '';
	  }
	  else
	  {
	  	$tds = $ut->getLabel($val['t2r_tds']);
	  	$status = $ut->getSmaLabel($val['t2r_status']);
	  	$players = $val['value'];
	  	$value = '';
	  	foreach($players as $player)
	  	$value .= "{$player['ibfn']} {$player['name']}";
	  }
	  $worksheet->write($i, 0, $value);
	  if ($val['t2r_status'])
	  $worksheet->write($i, 1, $status);
	  else
	  $worksheet->write($i, 1, $tds);
	  $i += 4;
		}

		$size = $round['rund_size'];
		$winners = $dt->getWinners($roundId);

		$allTies = $utr->getMatchs($roundId);
		if (isset($ties['errMsg'])) return $ties;

		// Construc a table with the ties
		$ties = array();
		foreach($allTies as $data)
		$ties[$data['tie_posRound']] = $data;
		$numCol = 2;
		$start = 6;
		$step = 8;
		while( ($size/=2) >= 1)
		{
	  $numLin = $start;
	  $vals = array();
	  $firstTie = $size-1;
	  for($i=0; $i < $size; $i++)
	  {
	  	$value = '';
	  	$score = '';
	  	if (isset($winners[$firstTie + $i]))
	  	{
	  		$winner = $winners[$firstTie + $i];
	  		$players = $winner['value'];
	  		$value = '';
	  		foreach($players as $player)
	  		$value .= "{$player['name']}";
	  		if ($winner['score']!= '0-0')
	  		$score = $winner['score'];
	  	}
	  	else if (isset($ties[$firstTie + $i]))
	  	{
	  		$tie = $ties[$firstTie + $i];
	  		$value = $tie['mtch_num'];
	  	}
	  	$worksheet->write($numLin,$numCol, $value);
	  	$worksheet->write($numLin+1,$numCol, $score);
	  	$numLin += $step;
	  }
	  $start += $step/2;
	  $step *= 2;
	  $numCol++;
		}
		$worksheet->write(3, 0, "First round");
		for ($i=0; $i<$numCol-2; $i++)
		{
	  $title = $ut->getLabel(WBS_WINNER + $i);
	  $worksheet->write(3,$numCol-$i-1, $title);
		}
		return;
	}
	// }}}

	/**
	 * Display a pdf document with the current round
	 *
	 * @access private
	 * @return void
	 */
	function _pdfDrawGroupNew($aId, & $pdf, $isFirst)
	{
		$utr = new utround();
		list($drawId, $groupName) = explode(';', $aId);

		//--- Get the groups
		$utr = new utround();
		$roundsi = $utr->getRounds($drawId, WBS_GROUP, null, $groupName);
		$roundsg = $utr->getRounds($drawId, WBS_ROUND_GROUP, null, $groupName);
		//$rounds = $roundsi +$roundsg;
$rounds = $roundsg;
		require_once "utils/utdraw.php";
		$utd = new utdraw();
		$draw = $utd->getDrawById($drawId);
		$oGroup = new objgroup();
		$group = $oGroup->getGroup($drawId, $groupName);

		require_once "basePdf.php";
		$dt =  new drawsbasePdf();
		$ranks = array();
		$top = null;
		if(count($rounds))
		{
			if (!$isFirst)
			{
				$pdf->_isHeader = true;
				$pdf->addPage('P');
			}
			foreach($rounds as $rund)
			{
				if ($rund['rund_type'] == WBS_GROUP ||
				$rund['rund_type'] == WBS_ROUND_GROUP )
				{
					$group['draw_name'] = $draw['draw_name'];
					$group['rund_name'] = $rund['rund_stamp'];
					$pairs  = $dt->getGroupPairs($rund['rund_id']);
					$matchs = $dt->getGroupMatchs($rund['rund_id']);
					$ranks  = $dt->getGroupRank($rund['rund_id']);
					$top = $pdf->displayGroup($top, $group, $pairs, $matchs, $ranks);
				}
			}
		}
		return;
	}

	/**
	 * Display a pdf documemnt with the registered repartition in draws
	 * of the current draw
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _pdfStats()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		require_once "pdf/pdfbase.php";
		$pdf = new pdfBase();
		$rows['top'] = $pdf->start('P', 'Statistiques');

		//--- Stats of the draws
		$titres = array ('Serial', 'MS', 'WS', 'MD', 'WD', 'XD');
		$tailles = array (50, 20, 20, 20, 20, 20);
		$styles = array ('B','','','','','');
		$rows['titre'] = 'tEntriesStats';
		$line['serial'] = "";

		$stats = $dt->getEntriesStats();
		foreach($stats as $stat)
		{
	  if ($line['serial'] != $stat['draw_serial'])
	  {
	  	if ($line['serial'] != '')
	  	$rows[] = $line;
	  	$line['serial'] = $stat['draw_serial'];
	  	$line[WBS_MS] = 0;
	  	$line[WBS_WS] = 0;
	  	$line[WBS_MD] = 0;
	  	$line[WBS_WD] = 0;
	  	$line[WBS_XD] = 0;
	  	$men   = 0;
	  	$women = 0;
	  }

	  if ($stat['draw_disci'] == WBS_XD)
	  {
	  	if ($stat['mber_sexe'] == WBS_MALE)
	  	$men = $stat['nb'];
	  	else
	  	$women = $stat['nb'];

	  	$alone = $men  - $women;
	  	if ($alone > 0)
	  	$line[$stat['draw_disci']] += $women;
	  	else
	  	$line[$stat['draw_disci']] += $men;
	  	/*if ($alone > 0)
	  	 $line[$stat['draw_disci']] .= abs($alone).' M';
	  	 else if ($alone < 0)
	  	 $line[$stat['draw_disci']] .= abs($alone).' W';
	  	 */
	  }
	  else if ($stat['draw_disci'] > WBS_WS)
	  {
	  	$line[$stat['draw_disci']] += intval($stat['nb']/2);
	  	if ($stat['nb']%2)
	  	$line[$stat['draw_disci']] .= ' + 1';
	  }
	  else
	  $line[$stat['draw_disci']] += $stat['nb'];
		}
		$rows[] = $line;
		$bottom = $pdf->genere_liste($titres, $tailles, $rows, $styles);

		// Tableau du nombre de match
		$stats = $dt->getMatchsStats();
		$titres[] = 'Total';
		$tailles[] = 20;
		$styles[] = 'B';
		$rows = array();
		$line['serial'] = '';
		$total['serial'] = "Total";
		$total[WBS_MS] = 0;
		$total[WBS_WS] = 0;
		$total[WBS_MD] = 0;
		$total[WBS_WD] = 0;
		$total[WBS_XD] = 0;
		$total[WBS_XD] = 0;
		$total['Total'] = 0;
		//print_r($stats);
		foreach($stats as $stat)
		{
	  if ($line['serial'] != $stat['draw_serial'])
	  {
	  	if ($line['serial'] != '')
	  	$rows[] = $line;
	  	$line['serial'] = $stat['draw_serial'];
	  	$line['Total'] = 0;
	  	$line[WBS_MS] = 0;
	  	$line[WBS_WS] = 0;
	  	$line[WBS_MD] = 0;
	  	$line[WBS_WD] = 0;
	  	$line[WBS_XD] = 0;
	  }
	  $line[$stat['draw_disci']] += $stat['nb'];
	  $line['Total'] += $stat['nb'];
	  $total[$stat['draw_disci']] += $stat['nb'];
	  $total['Total'] += $stat['nb'];
		}
		$rows[] = $line;
		$rows[] = $total;
		$rows['newPage'] = false;
		$rows['top'] = $bottom+10;
		$rows["titre"] = 'tMatchsStats';
		$bottom = $pdf->genere_liste($titres, $tailles, $rows, $styles);

		// Repartition par tableau par tour
		$utd = new utDraw();
		$draws = $utd->getDraws(array(4,5));
		$rows = array();
		$line = array();
		$utr = new utround();
		$oGroup = new objGroup();
		foreach($draws as $draw)
		{
			$drawId = $draw['draw_id'];
			$groupNames = $oGroup->getlistGroups($drawId);
			foreach($groupNames as $groupName)
			{
				$group = $oGroup->getGroup($drawId, $groupName);
				if ($group['nbPlace'] && !$group['nbQual'])
				{
					$round['rund_group'] = $groupName;
					$round['rund_name']  = $groupName;
					$round['rund_entries']  = 0;
					$rows[] = $this->_getLine($draw, $round, $group);
				}
				$rounds = $utr->getRounds($drawId, WBS_ROUND_MAINDRAW, null, $groupName);
				foreach($rounds as $round) $rows[] = $this->_getLine($draw, $round, $group);

				$rounds = $utr->getRounds($drawId, WBS_ROUND_THIRD, null, $groupName);
				foreach($rounds as $round) $rows[] = $this->_getLine($draw, $round, $group);

				$rounds = $utr->getRounds($drawId, WBS_ROUND_QUALIF, null, $groupName);
				foreach($rounds as $round) $rows[] = $this->_getLine($draw, $round, $group);

				$rounds = $utr->getRounds($drawId, WBS_ROUND_CONSOL, null, $groupName);
				foreach($rounds as $round) $rows[] = $this->_getLine($draw, $round, $group);

				$rounds = $utr->getRounds($drawId, WBS_ROUND_PLATEAU, null, $groupName);
				foreach($rounds as $round) $rows[] = $this->_getLine($draw, $round, $group);
			}
		}
		$titres = array ('Tableau', 'Tour 1', 'Tour 2', 'Tour 3', '1/64', '1/32', '1/16', '1/8', '1/4', '1/2',  'Finale', 'Total');
		$tailles = array (70, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10 , 10);
		$styles = array ('B','','','','','','','','','','','', 'B');
		$rows['newPage'] = false;
		$rows['top'] = $bottom+10;
		$rows["titre"] = 'tStaTours';

		$pdf->genere_liste($titres, $tailles, $rows, $styles);
		$pdf->end();
		exit;
	}

	private function _getLine($aDraw, $aRound, $aGroup)
	{
		$group = $aGroup;

		if ($aRound['rund_group'] == 'Principal') $line['name'] = $aDraw['draw_stamp'] . ' ' ;
		else $line['name'] = $aDraw['draw_stamp'] . ' ' . $aRound['rund_group']. '-';
		$line['name'] .= $aRound['rund_name'];
		$nb = $group['nb3'] + $group['nb4']*2 + $group['nb5']*2;
		$line['t1'] = $nb > 0 ? $nb:'';
		$line['t2'] = $nb > 0 ? $nb:'';
		$line['t3'] = $nb > 0 ? $nb:'';
		$line['64'] = $group['nb5']>0 ? $group['nb5']*2 : '';
		$line['32'] = $group['nb5']>0 ? $group['nb5']*2 : '';
		if( $aRound['rund_entries'] >= 128) $line['64'] = 64;
		else if( $aRound['rund_entries'] > 64) $line['64'] = $aRound['rund_entries']-64;
			
		if( $aRound['rund_entries'] >= 64) $line['32'] = 32;
		else if( $aRound['rund_entries'] > 32) $line['32'] = $aRound['rund_entries']-32;

		if( $aRound['rund_entries'] >= 32) $line['16'] = 16;
		else if( $aRound['rund_entries'] > 16) $line['16'] = $aRound['rund_entries']-16;
		else $line[16] = '';

		if( $aRound['rund_entries'] >= 16) $line['8'] = 8;
		else if( $aRound['rund_entries'] > 8) $line['8'] = $aRound['rund_entries']-8;
		else $line[8] = '';

		if( $aRound['rund_entries'] >= 8) $line['4'] = 4;
		else if( $aRound['rund_entries'] > 4) $line['4'] = $aRound['rund_entries']-4;
		else $line[4] = '';

		if( $aRound['rund_entries'] >= 4) $line['2'] = 2;
		else if( $aRound['rund_entries'] == 3) $line['2'] = 1;
		else $line[2] = '';

		if( $aRound['rund_entries'] >= 2) $line['F'] = 1;
		else $line['F'] = '';
		$line['total'] = $line['t1'] + $line['t2'] + $line['t3'] + $line['64']
		+ $line['32'] + $line['16'] + $line['8'] + $line['4']
		+ $line['2'] + $line['F'];
		return $line;
	}

	// {{{ _pdfDrawPairs()
	/**
	 * Display a pdf document with the list of the pairs
	 * of the current draw
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _pdfDrawPairs()
	{
		$ut = $this->_ut;
		$utd = $this->_utd;

		//--- Details of the draw
		$drawId = kform::getData();
		$draw = $utd->getDrawById($drawId);

		$sort = kform::getSort("rowsPairs", 2);
		$pairs = $this->_dt->getDrawPairs($drawId, $sort);
		if (count($pairs))
		{
	  $titres = array ('Joueurs', 'Licence', 'Association', 'NOC',
			   'Classement', 'Points', 'Rang', 'Moyenne',
			    'Phase', 'Evolution');
	  $tailles = array (50, 20, 50, 15, 25, 25, 15, 25, 15, 15);
	  $styles = array ('B', '', '', '', 'B', '', 'B', '', '','');
	  $rows["titre"] = $draw['draw_name'];
	  foreach($pairs as $pair)
	  {
	  	if(!isset($pair['regi_longName']))continue;
	  	$players = $pair['regi_longName'];
	  	$teams = $pair['team_name'];
	  	$newPair = true;
	  	$team = reset($teams);//print_r($players);
	  	foreach($players as $player)
	  	{
	  		$row=array();
	  		$row[] = $player['value'];
	  		$row[] = $player['licence'];
	  		$row[] = $player['stamp'];
	  		$row[] = $player['noc'];
	  		$row[] = $player['level'];
	  		$row[] = sprintf("%8.2f", $player['average']);
	  		$row[] = $player['classe'];
	  		if ($newPair==true)
	  		{
	  			$row['border'] = "TLR";
	  			$row[] = sprintf("%8.2f", $pair['pair_average']);
	  			$row[] = $pair['pair_status'];
	  			$row[] = $pair['pair_wo'];
	  		}
	  		else
	  		{
	  			$row['border'] = "BLR";
	  			$row[] = ' ';
	  			$row[] = ' ';
	  			$row[] = ' ';
	  		}
	  		$rows[] = $row;
	  		$team = next($teams);
	  		$newPair = false;
	  	}
	  }
	  $row = array_pop($rows);
	  $row['border'] .= "B";
	  $rows[] = $row;

	  require_once "pdf/pdfbase.php";
	  $rows['orientation'] = 'L';
	  $pdf = new pdfBase();
	  $rows['top'] = $pdf->start('L', 'Paires par tableau');
	  $pdf->genere_liste($titres, $tailles, $rows, $styles);
	  $pdf->end();
		}
		exit;
	}
	// }}}


	// {{{ _pdfDrawKo()
	/**
	 * Display a pdf document with the current round
	 *
	 * @access private
	 * @return void
	 */
	function _pdfDrawKo($ibf=0, $roundId, & $pdf)
	{
		$ut = $this->_ut;
		$utd = $this->_utd;
		$dt = $this->_dt;
		$utr = new utround();

		//--- Details of the draw
		$pairs = $dt->getRoundPairs($roundId);

		//print_r($pairs);
		$round = $utr->getRoundDef($roundId);
		require_once "utils/utko.php";
		$utko = new utKo($pairs, $round['rund_entries']);
		$valeurs_depart = $utko->getExpandedValues();

		$size = $round['rund_size'];
		$winners = $dt->getWinners($roundId);

		$allTies = $utr->getMatchs($roundId);
		if (isset($ties['errMsg'])) return $ties;

		// Construc a table with the ties
		$ties = array();
		foreach($allTies as $data)
		$ties[$data['tie_posRound']] = $data;
		$numCol = 0;
		$colonnes = array();
		while( ($size/=2) >= 1)
		{
			$vals = array();
			$firstTie = $size-1;
			for($i=0; $i < $size; $i++)
			{
				$val = '';
				if (isset($winners[$firstTie + $i]))
				{
					$winner = $winners[$firstTie + $i];
					$val['value'] = $winner['value'];
					if ($winner['score']!= '0-0') $val['score'] = $winner['score'];
				}
				else if (isset($ties[$firstTie + $i]))
				{
					$tie = $ties[$firstTie + $i];
					 
					if (!empty($tie['mtch_num']) )
					{
						$val['match_num'] = $tie['mtch_num'];
						$glue = ' - ';
					}
					else
					{
						$val['match_num'] = '';
						$glue = '';
					}
					if (!empty($tie['tie_schedule']) ) $val['match_num'] .=  $glue . $tie['tie_schedule'];
					$val['value'] = '';
				}
				$vals[] = $val;
			}
			$colonnes[$numCol++] = $vals;
		}
		$drawType =  $dt->getDrawType($roundId);

		if($drawType['draw_disci'] < 3)
		{
			$pdf->singleKO($valeurs_depart, $colonnes, $drawType['draw_name'], $drawType['rund_name'],$ibf);
		}
		else
		{
			$pdf->doubleKO($valeurs_depart, $colonnes, $drawType['draw_name'], $drawType['rund_name'],$ibf);
		}
		return;
	}
	// }}}



	// {{{ _pdfDrawPairsIbf()
	/**
	 * Display a pdf documemnt with the list of the pairs
	 * of the current draw
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _pdfDrawPairsIbf()
	{
		$ut = $this->_ut;
		$utd = $this->_utd;

		//--- Details of the draw
		$drawId = kform::getData();
		$draw = $utd->getDrawById($drawId);

		$pairs = $this->_dt->getDrawPairsForPdf($drawId);
		if (count($pairs))
		{
	  $titres = array ('Number', 'Nat rank', 'NOC', 'IBF Num', 'Players');
	  $tailles = array (15,15,15,15,120);
	  $styles = array ('B','','I','','');
	  $num = 0;
	  $pairNoc = "";
	  $rows["titre"] = $draw['draw_name'];
	  foreach($pairs as $pair)
	  {
	  	if ($pair['pair_noc'] !=  $pairNoc)
	  	{
	  		$pairNoc = $pair['pair_noc'];
	  		$num = 0;
	  	}
	  	$num++;
	  	$players = $pair['regi_longName'];
	  	$newPair = true;
	  	foreach($players as $player)
	  	{
	  		$row=array();
	  		if ($newPair==true)
	  		{
	  			$row['border'] = "TLR";
	  			$row[] = $num;
	  			$row[] = '';//$pair['pair_natRank'];
	  		}
	  		else
	  		{
	  			$row['border'] = "BLR";
	  			$row[] = ' ';
	  			$row[] = ' ';
	  		}
	  		$newPair = false;
	  		$row[] = $player['noc'];
	  		$row[] = $player['ibfNum'];
	  		$row[] = $player['player'];
	  		$rows[] = $row;
	  	}
	  }
	  $row = array_pop($rows);
	  $row['border'] .= "B";
	  $rows[] = $row;

	  require_once "pdf/pdfbase.php";
	  $pdf = new pdfBase();
	  $rows['top'] = $pdf->start('P', 'Pairs');
	  $pdf->genere_liste($titres, $tailles, $rows, $styles);
	  $pdf->end();
		}
		exit;
	}
	// }}}

}

?>
