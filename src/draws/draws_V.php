<?php
/*****************************************************************************
 !   Module     : Draws
 !   File       : $Source: /cvsroot/aotb/badnet/src/draws/draws_V.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.39 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:24 $
 ******************************************************************************/

require_once "draws.inc";
require_once "base_V.php";
require_once "utils/utdraw.php";
require_once "utils/utround.php";
require_once "utils/objgroup.php";


/**
 * Module de gestion des tableaux
 *
 * @author Gerard CANTEGRIL <cage@aotb.org>
 * @see to follow
 *
 */

class Draws_V
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
	function Draws_V()
	{
		$this->_ut = new utils();
		$this->_dt = new drawsBase_V();
	}
	// }}}

	// {{{ start()
	/**
	 * Start the connexion processus
	 *
	 * @access public
	 * @return void
	 */
	function start($action)
	{
		switch ($action)
		{
	  // Display players/pairs of a draw
			case WBS_ACT_DRAWS:
			case KID_SELECT:
			case DRAW_DISPLAY:
				$drawId = kform::getData();
				$drawId = kform::getInput('selectList', $drawId);
				$this->_displayForm(DRAW_DISPLAY, $drawId);
				break;
			case DRAW_GROUPS_DISPLAY:
			case DRAW_FINAL_DISPLAY:
				$drawId = kform::getData();
				$this->_displayForm($action, $drawId);
				break;
			case DRAW_KO_DISPLAY:
				$roundId = kform::getData();
				$this->_displayFormKo($roundId);
				break;
			case DRAW_PDF_PAIRSIBF:
			case DRAW_PDF_PAIRS:
			case DRAW_PDF_ALL_KO:
			case DRAW_PDF_ALL_GROUPS:
			case DRAW_PDF_STATS:
			case DRAW_PDF_GROUPS:
			case DRAW_PDF_KO:
			case DRAW_PDF_KO_IBF:
				require_once "drawsPdf.php";
				$pdf = new drawsPdf();
				$pdf->start($action);
				break;

			default:
				echo "page $action demand�e depuis draws_V<br>";
				break;
		}
		exit;
	}
	// }}}

	// {{{ _displayForm()
	/**
	 * Select the page to display according the draw
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayForm($action, $aDraw)
	{
		$utd = new utDraw();
		if ( empty($aDraw) )
		{
			$draws = $utd->getDraws();
			$drawId = -1;
			if (!isset($draws['errMsg']))
			{
				$draw = reset($draws);
				$drawId = $draw['draw_id'];
			}
		}
		else @list($drawId, $groupname)=explode(';', $aDraw);
		if ($drawId === -1)
		{
			$this->_displayHead(-1, 'itPairs', DRAW_DISPLAY);
			$this->_utpage->display();
			exit;
		}

		$draw = $utd->getDrawById($drawId);
		if ($action == DRAW_DISPLAY)
		{
			$this->_displayFormDrawPairs($draw);
		}

		$oGroup = new objgroup();
		$utr = new utround();
		$utr->getRounds($drawId);
		if ($action == DRAW_GROUPS_DISPLAY)
		{
			$groups = $oGroup->getListGroups($drawId, WBS_ROUND_GROUP);
			if (!empty($groups)) $this->_displayFormGroups($draw, $groupname);
		}
		$nbQualif = $draw['draw_nbp3'] * $draw['draw_nbs3'] +
		$draw['draw_nbp4'] * $draw['draw_nbs4'] +
		$draw['draw_nbp5'] * $draw['draw_nbs5'];
		if ( $draw['draw_type'] == WBS_GROUP && !$nbQualif)
		$this->_displayFormGroups($draw);

		$roundId = $this->_dt->getRoundId($drawId, WBS_ROUND_MAINDRAW);
		$this->_displayFormKo($roundId, $drawId);

		exit;
	}

	// {{{ _displayFormDrawPairs()
	/**
	 * Display a page with the list of the pairs of the current draw
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayFormDrawPairs($draw)
	{
		$ut = $this->_ut;
		$utd = new utDraw();

		//--- Details of the draw
		$drawId = $draw['draw_id'];
		$kdiv =& $this->_displayHead($draw, 'itPairs', DRAW_DISPLAY);

		$div =& $kdiv->addDiv('lgndInit', 'blkLegende');
		$div->addImg('lgdMaindraw', utimg::getIcon(WBS_PAIR_MAINDRAW));
		$div->addImg('lgdQualif',   utimg::getIcon(WBS_PAIR_QUALIF));
		$div->addImg('lgdReserve',  utimg::getIcon(WBS_PAIR_RESERVE));
		$div =& $kdiv->addDiv('lgndCur', 'blkLegende');
		$div->addImg('lgdWdn',      utimg::getIcon(WBS_PAIR_WDN));
		$div->addImg('lgdPtq',      utimg::getIcon(WBS_PAIR_PTQ));
		$div->addImg('lgdPtm',      utimg::getIcon(WBS_PAIR_PTM));
		// Display the pairs of the draws
		$sort = kform::getSort("rowsPairs", 2);
		$pairs = $this->_dt->getDrawPairs($drawId, $sort);
		if (isset($pairs['errMsg']))
		{
	  $kdiv->addWng($pairs['errMsg']);
	  unset($pairs['errMsg']);
		}
		if (count($pairs))
		{
	  $assoId = '';
	  foreach($pairs as $pair)
	  {
	  	if ($assoId != $pair['asso_id'])
	  	{
	  		$teams = $pair['asso_pseudo'];
	  		$value = '';
	  		$title = '';
	  		$glue = '';
	  		foreach($teams as $team)
	  		{
	  			if($team['value']!= $value)
	  			{
	  				$value = $team['value'];
	  				$title .= $glue.$value;
	  				$glue = '-';
	  			}
	  		}
	  		$rows[] = array(KOD_BREAK, "title", $title);
	  		$assoId = $pair['asso_id'];
	  	}
	  	$rows[] = $pair;
	  }
	  $krows =& $kdiv->addRows('rowsPairs', $rows);
	  $sizes[4] = "0";
	  $sizes[5] = "0";
	  $sizes[9] = "0+";
	  $krows->setSize($sizes);
		}

		$cache = "draws_".DRAW_DISPLAY;
		$cache .= "_{$drawId}";
		$sort = kform::getSort("", "");
		if ($sort!="")
		$cache .= "_{$sort}";
		$cache .= ".htm";
		$this->_utpage->display($cache);
		exit;
	}
	// }}}

	// {{{ _displayFormGroups()
	/**
	 * Display a page with the list of the groups of the draw
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayFormGroups($draw, $groupname)
	{
		$ut = $this->_ut;
		$utd = new utdraw();
		$utr = new utround();
		$dt = $this->_dt;

		//--- Details of the draw
		$drawId = $draw['draw_id'];
		$kdiv =& $this->_displayHead($draw, 'itGroup', DRAW_GROUPS_DISPLAY);

		// Liste deroulante des groupes
		$oGroup = new objgroup();
		$groups = $oGroup->getListGroups($drawId);
		$utr = new utround();
		$nb = 0;
		foreach($groups as $group)
		{
			$rounds = $utr->getRounds($drawId, WBS_ROUND_GROUP, null, $group);
			if (!empty($rounds))
			{
				$url = $drawId . ';' . $group;
				$list[$url] = $group;
				$nb++;
			}
		}
		if (empty($groupname)) $groupname = $group;
		if ($nb > 1)
		{
			$form =& $kdiv->addForm('formGroup');
			$kcombo=& $form->addCombo('roundList', $list, $drawId . ';' . $groupname);
			$acts[1] = array(KAF_UPLOAD, 'draws', DRAW_GROUPS_DISPLAY);
			$kcombo->setActions($acts);
		}
		$group = $oGroup->getGroup($drawId, $groupname);

		// Display error msg if any
		$rounds = $utr->getRounds($drawId, WBS_ROUND_GROUP, null, $groupname);
		if (!count($rounds))
		{
			$kdiv->addWng("msgNoPublic");
			$this->_utpage->display();
			exit;
		}

		$kdiv2 = & $kdiv->addDiv('divBtn', 'divBtn');
		$kdiv2->addBtn('itPdf', KAF_NEWWIN, 'draws',  DRAW_PDF_GROUPS, $drawId.';'.$groupname, 500, 400);

		// Displaying the groups
		$roundId = -1;
		$rows = array();
		$nbCols = 0;
		$break[] = array(KOD_BREAK, "title", '&nbsp;', 'classGroupBreak', '');

		foreach ( $rounds as $round)
		{
			$group = $dt->getGroup($round['rund_id']);
			if (isset($group['errMsg'])) continue;

			if ($nbCols != $round['rund_size'])
			{
				if ($nbCols)
				{
					$kdiv2 = & $kdiv->addDiv("divDraw{$nbCols}", 'group');
					$krow =& $kdiv2->addRows("rowsTies{$nbCols}", $rows);
					$krow->setSort(0);
					$krow->setNumber(false);
					$krow->displayTitle(false);
					$size[1] = "100";
					$krow->setSize($size);

					$class[1] = 'classPair';
					//$krow->setColClass($class);
				}
				$nbCols = $round['rund_size'];
				$rows = array();
			}
			$rows = array_merge($rows, $break, $group);
		}
		if ($nbCols)
		{
			$kdiv2 = & $kdiv->addDiv("divDraw{$nbCols}", 'group');
			$krow =& $kdiv2->addRows("rowsTies{$nbCols}", $rows);
			$krow->setSort(0);
			$krow->setNumber(false);
			$krow->displayTitle(false);
			$class[1] = 'classPair';
			$krow->setColClass($class);
			$nbCols = $round['rund_size'];
		}

		$cache = "draws_".DRAW_GROUPS_DISPLAY;
		$cache .= "_{$drawId}";
		$sort = kform::getSort("", "");
		if ($sort!="") $cache .= "_{$sort}";
		$cache .= ".htm";
		$this->_utpage->display($cache);
		exit;
	}

	/**
	 * Display a page with the list of the KO draw
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayFormKo($roundId, $drawId=-1)
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;
		$utd = new utdraw();
		$utr = new utround();

		//--- Details of the draw
		if ($roundId == '')
		{
			$kdiv =& $this->_displayHead($drawId, 'itMainDraw', DRAW_FINAL_DISPLAY );
			if ($drawId != -1) $kdiv->addWng("msgNoAvailable");
			$this->_utpage->display();
			exit;
		}

		$round = $utr->getRoundDef($roundId);
		$drawId = $round['rund_drawId'];
		$draw = $utd->getDrawById($drawId);
		if ($draw['draw_id'] == -1)
		{
			$kdiv =& $this->_displayHead($drawId, 'itMainDraw', DRAW_FINAL_DISPLAY );
			$kdiv->addWng("msgNoAvailable");
			$this->_utpage->display();
			exit;
		}

		$kdiv =& $this->_displayHead($draw, 'itMainDraw', DRAW_FINAL_DISPLAY );

		// Liste deroulante des tours par KO
		$list = array();
		$rounds = $utr->getRounds($drawId, WBS_ROUND_MAINDRAW);
		foreach($rounds as $cur)
		$list[$cur['rund_id']] = $cur['rund_name'];
		$rounds = $utr->getRounds($drawId, WBS_ROUND_THIRD);
		foreach($rounds as $cur)
		$list[$cur['rund_id']] = $cur['rund_name'];
		$rounds = $utr->getRounds($drawId, WBS_ROUND_QUALIF);
		foreach($rounds as $cur) $list[$cur['rund_id']] = $cur['rund_name'];
		$rounds = $utr->getRounds($drawId, WBS_ROUND_CONSOL);
		foreach($rounds as $cur) $list[$cur['rund_id']] = $cur['rund_name'];
		$rounds = $utr->getRounds($drawId, WBS_ROUND_PLATEAU);
		foreach($rounds as $cur) $list[$cur['rund_id']] = $cur['rund_name'];
		
		if (count($list)>1)
		{
	  $kcombo=& $kdiv->addCombo('roundList', $list, $roundId);
	  $acts[1] = array(KAF_UPLOAD, 'draws', DRAW_KO_DISPLAY);
	  $kcombo->setActions($acts);
		}

		// Display the draw if public
		$pairs = $dt->getRoundPairs($roundId);
		$round = $utr->getRoundDef($roundId);
		$kdiv2 = & $kdiv->addDiv('divBtn', 'divBtn');
		$kdiv2->addBtn('itPdf', KAF_NEWWIN, 'draws',  DRAW_PDF_KO,
		$roundId, 600, 460);

		require_once "utils/utko.php";
		$utko = new utKo($pairs, $round['rund_entries']);
		$vals = $utko->getExpandedValues();
		$kdiv2 = & $kdiv->addDiv('divDraw', 'draw');
		$kdraw = & $kdiv2->addDraw('draw', $round['rund_qual']);

		$kdraw->setValues(1, $vals);
		$size = $round['rund_size'];
		$winners = $dt->getWinners($roundId);
		$allTies = $utr->getMatchs($roundId);
		if (isset($ties['errMsg'])) return $ties;
		// Construc a table with the ties
		$ties = array();
		foreach($allTies as $data)
		$ties[$data['tie_posRound']] = $data;

		$numCol = 2;
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
	  		if ($winner['score']!= '0-0')
	  		$val['score'] = $winner['score'];
	  	}
	  	else if (isset($ties[$firstTie + $i]))
	  	{
	  		$tie = $ties[$firstTie + $i];
	  		//$val['value'] = $tie['tie_schedule'];
	  		$val['value'] = $tie['mtch_num'];
	  	}
	  	$vals[] = $val;
	  }
	  $kdraw->setValues($numCol++, $vals);
		}

		for ($i=0; $i<$numCol; $i++)
		$title[$numCol-$i-2] = $ut->getLabel(WBS_WINNER + $i);
		$kdraw->setTitles($title);


		$cache = "draws_".DRAW_KO_DISPLAY;
		$cache .= "_{$roundId}";
		$cache .= ".htm";
		$this->_utpage->display($cache);
		exit;
	}

	// {{{ _displayHead()
	/**
	 * Display header of the page
	 *
	 * @access private
	 * @param array team  info of the team
	 * @return void
	 */
	function & _displayHead($draw, $select, $act)
	{
		$dt = $this->_dt;
		$utr = new utround();
		
		// Create a new page
		$this->_utpage = new utPage_V('draws', true, 'itDraws');
		$this->_utpage->_page->addStyleFile("draws/draws_V.css");
		$content =& $this->_utpage->getContentDiv();
		if ($draw === -1)
		{
			$kdiv =& $content->addDiv('choix', 'onglet3');
			$kdiv =& $content->addDiv('register', 'cont3');
			$kdiv->addDiv('headCont3', 'headCont3');
			$kdiv->addWng("msgNoAvailable");
			return $kdiv;
		}
		if (is_array($draw)) $drawId = $draw['draw_id'];
		else $drawId = $draw;
		//      $drawId = ($draw === -1) ? $draw : $draw['draw_id'];

		$itemSel = $select;
		$items['itPairs']  = array(KAF_UPLOAD, 'draws',	DRAW_DISPLAY, $drawId);
		
		$rounds = $utr->getRounds($drawId, WBS_ROUND_GROUP);
		if (!empty($rounds) )
		{
			$items['itGroup']  = array(KAF_UPLOAD, 'draws',	DRAW_GROUPS_DISPLAY, $drawId);
			$nbQualif = $draw['draw_nbp3'] * $draw['draw_nbs3'] +
			$draw['draw_nbp4'] * $draw['draw_nbs4'] +
			$draw['draw_nbp5'] * $draw['draw_nbs5'];
			if ($nbQualif) $items['itMainDraw']  = array(KAF_UPLOAD, 'draws', DRAW_FINAL_DISPLAY, $drawId);
		}
		$items['itMainDraw']  = array(KAF_UPLOAD, 'draws', DRAW_FINAL_DISPLAY, $drawId);

		$kdiv =& $content->addDiv('choix', 'onglet3');
		$kdiv->addMenu("menuType", $items, $itemSel);
		$kdiv =& $content->addDiv('register', 'cont3');
		$khead =& $kdiv->addDiv('headCont3', 'headCont3');

		// Add a list of available draws
		//$kdiv->addDiv('break', 'blkNewPage');
		$utd = new utDraw();
		$sort = array(5,4);
		$draws = $utd->getDraws($sort);
		if (!isset($draws['errMsg']))
		{
			foreach($draws as $theDraw) $list[$theDraw['draw_id']] = $theDraw['draw_name'];
			if (isset($list))
			{
				$theDiv =& $khead->addForm('theDiv', 'draws', $act);
				if (isset($list[$drawId])) $kcombo=& $theDiv->addCombo('selectList', $list, $list[$drawId]);
				else $kcombo=& $theDiv->addCombo('selectList', $list);
				$acts[1] = array(KAF_UPLOAD, 'draws', $act);
				$kcombo->setActions($acts);
				$theDiv->addBtn('Go', KAF_SUBMIT);
			}
			else
			{
				$content->addMsg("msgNoDraw");
				$content->addDiv('recentBreak', 'blkNewPage');
				return $content;
			}
		}
		$kdiv =& $kdiv->addDiv('corp3', 'corpCont3');
		return $kdiv;
	}
	// }}}

}

?>