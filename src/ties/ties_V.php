<?php
/*****************************************************************************
 !   Module     : ties
 !   File       : $Source: /cvsroot/aotb/badnet/src/ties/ties_V.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.15 $
 !   Author     : G.CANTEGRIL
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/01/27 22:41:48 $
 !   Mailto     : cage@free.fr
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

require_once "ties.inc";
require_once "base_V.php";
require_once "utils/utscore.php";
require_once "utils/utteam.php";

/**
 * Module de gestion des rencontres : classe visiteurs
 *
 * @author Gerard CANTEGRL <cage@free.fr>
 * @see to follow
 *
 */

class ties_V
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
	function ties_V()
	{
		$this->_ut = new utils();
		$this->_dt = new tiesBase_V();
	}
	// }}}

	// {{{ start()
	/**
	 * Start the connexion processus
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function start($page)
	{
		$id = kform::getData();
		switch ($page)
		{
			case TIES_PDF_TIES:
				require_once "pdf/pdfties.php";
				$pdf = new pdfties();
				$pdf->affichage_resultDiv($id);
				break;
			case TIES_PDF_RESULTS:
				require_once "pdf/pdfties.php";
				$pdf = new pdfties();
				$pdf->affichage_result($id);
				break;
			case TIES_PDF_RANKING:
				require_once "pdf/pdfgroups.php";
				$pdf = new pdfGroups();
				$pdf->affichage_class($id);
				break;
			case TIES_PDF_SCHEDULE:
				require_once "pdf/pdfschedu.php";
				$pdf = new pdfschedu();
				$pdf->affichage_schedu($id);
				break;
			case TIES_SELECT_TIE:
				$this->_displayFormTie($id);
				break;
			case WBS_ACT_TIES:
				$this->_displayResults($id);
				break;
			case KID_SELECT:
				$this->_displayRanking($id);
				break;
			default:
				echo "page $page demand�e depuis ties_V<br>";
				exit();
		}
	}
	// }}}


	// {{{ _displayResults()
	/**
	 * Display a page with the list of the divisions of the current event
	 * and all groups of the current division
	 *
	 * @access private
	 * @return void
	 */
	function _displayResults($divId='')
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$utt =  new utteam();;

		// Create a new page
		$kdiv =& $this->_displayHead($divId, WBS_ACT_TIES, 'itDivision');

		//--- List of the groups (ie round in the database)
		//of the selected division
		$groups = $utt->getGroups($this->_divSel);
		if (isset($groups['errMsg']))
		$kdiv->addWng($groups['errMsg']);

		if (!count($groups))
		$kdiv->addWng('msgNoGroups');
		else
		{
	  $kdiv2 =& $kdiv->addDiv('divPdf', 'blkBtn');
	  $kdiv2->addBtn('btnPdfRanking', KAF_NEWWIN, 'ties', TIES_PDF_RANKING,
	  $this->_divSel,600,450);
	  $kdiv2->addBtn('btnPdfSchedule', KAF_NEWWIN, 'ties', TIES_PDF_SCHEDULE,
	  $this->_divSel,600,450);

	  // Display the results for each group
	  $kdiv =& $kdiv->addForm('test');
	  $numKrow = 0;
	  $nbDisplay = 0;
	  foreach ( $groups as $groupSel=>$group)
	  {
	  	if ($group['rund_type'] == WBS_TEAM_GROUP ||
		  $group['rund_type'] == WBS_TEAM_BACK)
		  {

		  	$sizes=array();
		  	$title = array();
		  	$acts = array();
		  	$first = array();
		  	$first[] = array(KOD_BREAK, "title", $group['rund_name'],
				   '', $group['rund_id']);
		  	$rows = $dt->getTies($groupSel);
		  	if (!isset($rows['errMsg']))
		  	{
		  		$kdiv2 = & $kdiv->addDiv("divDraw{$group['rund_id']}",
					       'group');
		  		//$kdiv2->addMsg($group['rund_name'], '', 'titre');
		  		$krow =& $kdiv2->addRows("rowsTies$numKrow",
		  		array_merge($first, $rows));
		  		$krow->setSort(0);
		  		$krow->setNumber(false);

		  		$nbTeams = count($rows);
		  		for ($i=0; $i< $nbTeams;$i++)
		  		{
		  			$sizes[$nbTeams+$i+2] = 0;
		  			$acts[$i+2] =  array(KAF_UPLOAD, 'ties',
		  			TIES_SELECT_TIE, $nbTeams+$i+2);
		  			$team= $rows[$i];
		  			$title[$i+2] = $team[2*$nbTeams+2];
		  			//$title[$i+2] = $i+1;
		  		}
		  		$title[1]=$group['rund_name'];
		  		$krow->setTitles($title);
		  		$sizes[2*$nbTeams+1] = 0;
		  		$sizes[2*$nbTeams+2] = 0;
		  		$sizes[2*$nbTeams+3] = 0;
		  		$acts[1] = array(KAF_UPLOAD, 'teams', KID_SELECT);
		  		$krow->setSize($sizes);
		  		$krow->setActions($acts);

		  		$img[1] = 2*$nbTeams+3;
		  		$krow->setLogo($img);

		  		$nbDisplay++;
		  		$acts = array();
		  		$acts[] = array( 'link' => array(KAF_NEWWIN, 'ties',
		  		TIES_PDF_TIES, $group['rund_id'], 600, 450),
				       'icon' => utimg::getIcon(TIES_PDF_TIES),
				       'title' => 'tieResults');
		  		$krow->setBreakActions($acts);
		  	}
		  	$numKrow++;
		  	if (!$nbDisplay)
		  	$kdiv->addWng("msgNoTeams");
		  }
		  else
		  {
		  	$actions[0] = array(KAF_UPLOAD, 'teams', KID_SELECT);
		  	$actions[1] = array(KAF_UPLOAD, 'ties', TIES_SELECT_TIE);

		  	$teams = $utt->getTeamsGroup($group['rund_id']);
		  	$names= array();
		  	foreach($teams as $team)
		  	{
		  		$logo = utimg::getPathFlag($team['asso_logo']);
		  		$names[] = array('logo' => $logo,
				       'value' => $team['team_name'], 
				       'link' => $team['team_id'],
				   't2r_posRound' => $team['t2r_posRound']);
		  	}
		  	require_once "utils/utko.php";
		  	$utko = new utKo($names, count($names));
		  	$vals = $utko->getExpandedValues();
		  	$kdiv2 = & $kdiv->addDiv("divDraw{$group['rund_id']}", 'group');
		  	$kdiv2->addMsg($group['rund_name'], '', 'titre');
		  	$kdiv2->addBtn("btnPdfAllTies", KAF_NEWWIN, 'ties', TIES_PDF_TIES, $group['rund_id'],600,450);
		  	$kdraw = & $kdiv2->addDraw("draw{$group['rund_id']}");
		  	$kdraw->setValues(1, $vals);

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
		  			$val = array();
		  			if (isset($teams[$firstTie + $i]))
			    {
			    	$team = $teams[$firstTie + $i];
			    	$val = array('value' => $team['team_name'],
					   'score' => '',
					   'link' => $team['tie_id']) ;
			    	if ($team['score']!= '0-0')
			    	$val['score'] = $team['score'];
			    }
			    else if (isset($ties[$firstTie + $i]))
			    {
			    	$tie = $ties[$firstTie + $i];
			    	$val = array('value' => $tie['tie_schedule'],
					   'score' => '',
					   'link' => $tie['tie_id']) ;
			    	if ($tie['score']!= '0-0')
			    	$val['score'] = $tie['score'];
			    }
			    $vals[] = $val;
		  		}
		  		$kdraw->setValues($numCol++, $vals);
		  	}
		  	$kdraw->setActions($actions);
		  	for ($k=0;$k<$numCol; $k++)
		  	$title3[$k] = "titleDraw$k";
		  	for ($k=$numCol-1;$k>0; $k--)
		  	$title3[$k] = "drawTitle".($numCol-$k-1);
		  	$kdraw->setTitles($title3);
		  }
	  }
		}


		$cache = "ties_".WBS_ACT_TIES;
		if ($this->_cache != '')
		$cache .= "_{$this->_cache}";
		$cache .= ".htm";
		$this->_utpage->display($cache);
		exit;
	}
	// }}}


	// {{{ _displayRanking()
	/**
	 * Display a page with the list of the divisions of the current event
	 * and all groups of the current division
	 *
	 * @access private
	 * @return void
	 */
	function _displayRanking($divId='')
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$utt =  new utteam();

		// Create a new page
		$kdiv =& $this->_displayHead($divId, KID_SELECT, 'itRanking', false);

		//--- List of the groups (ie round in the database)
		//of the selected division
		$groups = $utt->getGroups($this->_divSel);

		// Display the ranking
		$ute = new Utevent();
		$event = $ute->getEvent(utvars::getEventId());
		$kdiv2 =& $kdiv->addDiv('divPdf', 'blkBtn');
		$kdiv2->addBtn('btnPdf', KAF_NEWWIN, 'ties', TIES_PDF_RANKING, $this->_divSel,600,450);
		for ($k=1;$k<19; $k++) $title2[$k] = "rowsRanking$k";
		foreach ( $groups as $groupSel=>$group)
		{

			if ($group['rund_type'] == WBS_TEAM_GROUP ||
			$group['rund_type'] == WBS_TEAM_BACK)
			{
				if ($group['rund_rankType'] == WBS_CALC_RANK) $msg = 'msgRank';
				else $msg = 'msgResult';
					
				$sizes = array(2=>0);
				if($group['rund_tieranktype'] == WBS_CALC_EQUAL)
				{
					$sizes[6] = 0;
					$sizes[8] = 0;
				}
				else $sizes[7] = 0;

				if ($event['evnt_scoringsystem'] == WBS_SCORING_1X5) $sizes[16] = '0+';
				else $sizes[19] = '0+';

				$teams = $dt->getRankGroup($groupSel);
				if (!isset($teams['errMsg']) &&  count($teams))
				{
					$first = array(array(KOD_BREAK, "title", $group['rund_name']));
					$rows = array_merge($first, $teams);
				}

				//$kdiv->addWng('msgRules', $msg);
				$krow =& $kdiv->addRows("rowsRanking_".$groupSel, $rows);
				$krow->setSort(0);

				$krow->setSize($sizes);

				$class[3] = "diff";
				$class[10] = "diff";
				$class[13] = "diff";
				$class[16] = "diff";
				$krow->setColClass($class);

				$krow->displaySelect(false);
				$acts = array( 1 => array(KAF_UPLOAD, 'teams', KID_SELECT, 19));
				$krow->setActions($acts);

				$img[1] = 20;
				$krow->setLogo($img);
				$krow->setTitles($title2);
			}
		}
		//else $kdiv->addWng("msgNoGroups");

		$cache = "ties_".KID_SELECT;
		if ($this->_cache != '')
		$cache .= "_{$this->_cache}";
		$cache .= ".htm";
		$this->_utpage->display($cache);
		exit;
	}
	// }}}


	// {{{ _displayFormTie()
	/**
	 * Display a page with the matches of a tie
	 *
	 * @access private
	 * @param  integer $tieId  Id of the tie
	 * @return void
	 */
	function _displayFormTie($tieId)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$utt =  new utteam();;

		// Create a new page
		$utpage = new utPage_V('ties', true, 'itResults');
		$content =& $utpage->getContentDiv();
		$div =& $content->addDiv('choix', 'onglet3');
		$div =& $content->addDiv('contenu', 'cont3');

		$kdiv =& $div->addDiv('infos');

		//--- List of the matches of the selected tie
		if ($tieId != "")
		{
	  $teams = $dt->getTeams($tieId);
	  $group = $dt->getGroup($tieId);

	  // set the tittle
	  if (isset($teams[0][1]) &&
	  isset($teams[1][1]))
	  {
	  	$titre = $teams[0][1].' ('.$teams[0][3].')';
	  	$titre .= "&nbsp;-&nbsp;";
	  	$titre .= $teams[1][1].' ('.$teams[1][3].')';
	  	$kdiv->addInfo('selectList', $titre);
	  }

	  $div =& $kdiv->addDiv('divInfo', 'blkInfo');
	  $kinfo =& $div->addInfo("draw_name", $group['draw_name']);
	  $act[1] = array(KAF_UPLOAD, 'ties', WBS_ACT_TIES,
	  $group['draw_id']);
	  $kinfo->setActions($act);
	  $kinfo =& $div->addInfo("rund_name", $group['rund_name']);
	  $kinfo =& $div->addInfo("tie_step", $group['tie_step']);
	  $kinfo =& $div->addInfo("tie_place", $group['tie_place']);
	  $kinfo =& $div->addInfo("tie_schedule", $group['tie_schedule']);

	  $div->addBtn("btnPdf", KAF_NEWWIN, 'ties',
	  TIES_PDF_RESULTS, $tieId,400,300);
	  // Get the matches
	  $matchs = $dt->getMatchs($tieId, $teams[0][0], $teams[1][0]);

	  $kdiv->addDiv('break', 'blkNewPage');
	  // Display the matches
	  if (isset($matchs['errMsg']))
	  $kdiv->addWng($matchs['errMsg']);
	  else
	  {
	  	$krow =& $kdiv->addRows("rowsMatchs", $matchs);
	  	 
	  	$titles = array(2=>"A&nbsp;:&nbsp;".$teams[0][3],
			      "B&nbsp;:&nbsp;".$teams[1][3]);
	  	$krow->setTitles($titles);
	  	 
	  	$krow->displaySelect(false);
	  	$krow->setSort(false);
	  	$sizes = array(7=> 0,0,0,0,0,0,0,0,0);
	  	$krow->setSize($sizes);
	  	$img[1]= 13;
	  	$img[2]= 14;
	  	$img[3]= 15;
	  	$krow->setLogo($img);
	  }
		}

		// Legend
		$div =& $content->addDiv('blkLegende');
		$div->addImg('lgdIncomplete', utimg::getIcon(WBS_MATCH_INCOMPLETE));
		$div->addImg('lgdBusy',       utimg::getIcon(WBS_MATCH_BUSY));
		$div->addImg('lgdRest',       utimg::getIcon(WBS_MATCH_REST));
		$div->addImg('lgdReady',      utimg::getIcon(WBS_MATCH_READY));
		$div->addImg('lgdLive',       utimg::getIcon(WBS_MATCH_LIVE));
		$div->addImg('lgdEnded',      utimg::getIcon(WBS_MATCH_ENDED));
		$div->addImg('lgdClosed',     utimg::getIcon(WBS_MATCH_CLOSED));
		$div->addImg('lgdSend',       utimg::getIcon(WBS_MATCH_SEND));


		$cache = "ties_".TIES_SELECT_TIE;
		$cache .= "_{$tieId}";
		$cache .= ".htm";
		$utpage->display($cache);
		exit;
	}
	// }}}


	// {{{ _displayHead()
	/**
	 * Display header of the page
	 *
	 * @access private
	 * @param array team  info of the team
	 * @return void
	 */
	function & _displayHead($select, $action, $menu, $all=true)
	{
		$dt = $this->_dt;
		$utt =  new utteam();

		// List of divisions
		$divs = $utt->getDivs();
		$this->_divSel = 0;
		$this->_cache = "";
		if (isset($divs['errMsg']))
		$kdiv->addWng($divs['errMsg']);
		else
		{
	  $items = array();
	  $divsF = array();
	  foreach ($divs as $idDiv=>$nameDiv)
	  {
	  	// Only divisions with not KO groups
	  	if (!$all)
	  	{
	  		$groups = $utt->getGroups($idDiv);
	  		foreach ( $groups as $groupSel=>$group)
	  		{
	  			if ($group['rund_type'] == WBS_TEAM_GROUP ||
	  			$group['rund_type'] == WBS_TEAM_BACK)
	  			{
	  				$divsF[$idDiv] = $nameDiv;
	  				break;
	  			}
	  		}
	  	}
	  	else
	  	$divsF[$idDiv] = $nameDiv;
	  }

	  $divSel = $select;
	  if ( ($divSel == '' || !isset($divsF[$divSel])) &&
	  sizeof($divsF))
	  {
	  	reset($divsF);
	  	$first = each($divsF);
	  	$divSel = $first[0];
	  }
	  $this->_divSel = $divSel;
	  $this->_cache = $select;
		}

		// Create a new page
		$this->_utpage = new utPage_V('ties', true, 'itResults');
		$content =& $this->_utpage->getContentDiv();
		$kdiv =& $content->addDiv('choix', 'onglet3');

		// Add a menu for different action
		$items['itDivision']   = array(KAF_UPLOAD, 'ties', WBS_ACT_TIES);
		$items['itRanking']   = array(KAF_UPLOAD, 'ties', KID_SELECT);
		$kdiv->addMenu("menuType", $items, $menu);

		$div =& $content->addDiv('contenu', 'cont3');
		$khead =& $div->addDiv('headCont3', 'headCont3');
		$kdiv =& $div->addDiv('corp3', 'corpCont3');

		if (count($divsF))
		{
	  $kcombo=& $kdiv->addCombo('selectList', $divsF, $divsF[$divSel]);
	  $acts[1] = array(KAF_UPLOAD, 'ties', $action);
	  $kcombo->setActions($acts);
		}

		return $kdiv;
	}
	// }}}

}

?>