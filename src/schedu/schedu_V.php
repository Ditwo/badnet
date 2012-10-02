<?php
/*****************************************************************************
 !   Module     : Schedu
 !   File       : $Source: /cvsroot/aotb/badnet/src/schedu/schedu_V.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.21 $
 !   Author     : G.CANTEGRIL
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2006/10/10 11:38:16 $
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

require_once "schedu.inc";
require_once "base_V.php";
require_once "utils/utteam.php";
require_once "utils/utimg.php";
require_once "ties/ties.inc";

/**
 * Module de gestion des calendriers : classe visiteurs
 *
 * @author Gerard CANTEGRL <cage@free.fr>
 * @see to follow
 *
 */

class schedu_V
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
	function schedu_V()
	{
		$this->_ut = new utils();
		$this->_dt = new scheduBase_V();
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
			case WBS_ACT_SCHEDU:
			case KID_SELECT:
				$this->_displaySchedu($id);
				break;
			case SCHEDU_PDF:
			case SCHEDU_PDF_INDIV:
			case SCHEDU_PDF_ALLINDIV:
				require_once "scheduPdf.php";
				$pdf = new scheduPdf();
				$pdf->start($page);
				exit;
			default:
				echo "page $page demandée depuis schedu_V<br>";
				exit();
		}
	}
	// }}}

	// {{{ _displaySchedu()
	/**
	 * Display a page with the list of the ties
	 * and the field to update the schedule
	 *
	 * @access private
	 * @return void
	 */
	function _displaySchedu($divId='')
	{
		$utev = new utEvent();

		$eventId = utvars::getEventId();
		if ($utev->getEventType($eventId)== WBS_EVENT_INDIVIDUAL)
		$this->_displayScheduIndiv($divId);
		else
		$this->_displayScheduTeam($divId);
	}
	//}}}

	// {{{ _displayScheduIndiv()
	/**
	 * Display a page with the programme of the day for an
	 * individual event
	 *
	 * @access private
	 * @return void
	 */
	function _displayScheduIndiv($place='')
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$utt =  new utteam();;

		// Create a new page
		$utpage = new utPage_V('schedu', true, 'itCalendar');
		$utpage->_page->addStyleFile("schedu/schedu_V.css");

		$content =& $utpage->getContentDiv();

		$kdiv =& $content->addDiv('choix', 'onglet3' );
		$items['itVenue']  = array(KAF_UPLOAD, 'schedu', WBS_ACT_SCHEDU);
		//$items['itDraw']  = array(KAF_UPLOAD, 'schedu', WBS_ACT_SCHEDU);
		$kdiv->addMenu("menuType", $items, 'itVenue');
		$kdiv =& $content->addDiv('groups', 'cont3');
		$khead =& $kdiv->addDiv('headCont3', 'headCont3');
		
		// Get dates and venues
		$places = $dt->getPlaces();
		if (isset($places['errMsg']))
		{
	  $kdiv->addWng($places['errMsg']);
	  $kdiv->addDiv('recentBreak', 'blkNewPage');
	  $utpage->display();
	  exit;
		}
		$place = kform::getData();
		$placeSel = kform::getInput('selectList', $place);
		if ( empty($placeSel) )
		{
			$placeSel = reset($places);
		}

		if (count($places)>1)
		{
	  foreach($places as $place)
	  {
	  	$list[$place] = $place;
	  }
	  $theDiv = $khead->addForm('theDiv', 'schedu', WBS_ACT_SCHEDU);

	  $kcombo=& $theDiv->addCombo('selectList', $list, $list[$placeSel]);
	  $acts[1] = array(KAF_UPLOAD, 'schedu', WBS_ACT_SCHEDU);
	  $kcombo->setActions($acts);
	  $theDiv->addBtn('Go', KAF_SUBMIT);

		}
		else
		$kdiv->addMsg('selectList', $placeSel);

		// Display the schedule for each date
		$rows = array();
		$dates = $dt->getDateTies('', $placeSel);
		$lines = array();
		$lines['class'] = 'classScheduMatch';

		// Pour chaque date,recuperer les rangees
		$times = array();
		foreach ($dates as $date=>$fullDate)
		{
	  $time = $dt->getTiesIndiv($date, $placeSel);
	  if (isset($time['errMsg'])) continue;
	  $times[$fullDate] = $time;
		}

		// Calculer le nombre max de match par rangee
		$nbMax = 1;
		$limit = 10;
		foreach($times as $ties)
		{
	  foreach($ties as $tie)
	  $nbMax = max($nbMax, count($tie));
		}
		$nbMax = min($nbMax, $limit);
		foreach ($times as $fullDate=>$ties)
		{
	  $rows[]= array(KOD_BREAK, "title", $fullDate, 'classScheduBreak', '');
	  //$ties = $dt->getTiesIndiv($date, $placeSel);
	  foreach($ties as $tie)
	  {
	  	if (count($tie)<$limit)
	  	$row = $tie;
	  	else
	  	{
	  		$cpt = 0;
	  		$time = $tie[1];
	  		$row = array();
	  		foreach($tie as $cell)
	  		{
	  			if ($cpt==$limit)
	  			{
	  				$rows[] = $row;
	  				$row = array();
	  				$row[]="0";
	  				$row[]= $lines;
	  				$cpt = 2;
	  			}
	  			$cpt++;
	  			$row[] = $cell;
	  		}
	  	}
	  	// Completer la rangee
	  	$size = count($row);
	  	for($i = $size; $i<$nbMax; $i++)
	  	$row[] = $lines;
	  	$rows[] = $row;
	  }

		}

		if (count($rows) <= count($dates))
		$kdiv->addWng("msgNoCalendar");
		else
		{
	  	$kdiv2 = & $kdiv->addDiv('divBtn', 'divBtn');
		$kdiv2->addBtn('itPdf', KAF_NEWWIN, 'schedu',  SCHEDU_PDF_ALLINDIV,
		$placeSel, 500, 400);
		
	  $kdiv =& $kdiv->addDiv('corp3', 'corpCont3');
	  $krow =& $kdiv->addRows("scheList", $rows);
	  $krow->displayTitle(false);
	  $krow->displayNumber(false);
		}

		$cache = "schedu_".WBS_ACT_SCHEDU;
		if ($placeSel != '')
		$cache .= "_{$placeSel}";
		$cache .= ".htm";
		$utpage->display($cache);
		exit;
	}
	// }}}

	// {{{ _displayScheduTeam()
	/**
	 * Display a page with the list of the divisions of the current event
	 * and all groups of the current division
	 *
	 * @access private
	 * @return void
	 */
	function _displayScheduTeam($divId='')
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$utt =  new utteam();;

		// Create a new page
		$utpage = new utPage_V('schedu', true, 'itCalendar');
		$content =& $utpage->getContentDiv();
		$kdiv =& $content->addDiv('choix', 'onglet3' );
		$div =& $content->addDiv('calendarDiv', 'cont3');
	  	$khead =& $div->addDiv('headCont3', 'headCont3');
      	$kdiv =& $div->addDiv('corp3', 'corpCont3');
      
		//--- List of divisions (ie the draws in the database)
		$divs = $utt->getDivs();
		if (isset($divs['errMsg']))
		$kdiv->addWng($divs['errMsg']);

		$divSel = $divId;
		if (!count($divs))
		$kdiv->addWng('msgNoDivs');
		else
		{
	  // Search the div to select
	  if ( ($divSel == '' || !isset($divs[$divSel])) &&
	  sizeof($divs))
	  {
	  	$first = each($divs);
	  	$divSel = $first[0];
	  }
	  if (count($divs) > 1)
	  {
	  	$kcombo=& $kdiv->addCombo('selectList', $divs, $divs[$divSel]);
	  	$acts[1] = array(KAF_UPLOAD, 'schedu', WBS_ACT_SCHEDU);
	  	$kcombo->setActions($acts);
	  }
		}


		//--- List of the groups (ie round in the database)
		//of the selected division
		$dates = $dt->getDateTies($divSel);
		if (isset($dates['errMsg']))
		{
	  $kdiv->addWng($dates['errMsg']);
	  unset($dates['errMsg']);
		}

		// Display the schedule for each group
		$times = array();
		// Pour chaque date,recuperer les rangees
		foreach ($dates as $date=>$fullDate)
		{
	  $ties = $dt->getTiesSchedu2($divSel, $date);
	  if (!isset($ties['errMsg']))
	  $times[$fullDate] = $ties;
		}
		// Calculer le nombre max de match par rangee
		$nbMax = 1;
		foreach($times as $ties)
		{
	  foreach($ties as $tie)
	  $nbMax = max($nbMax, count($tie));
		}
		$nbMaxCol = 6;
		$nbMax = min($nbMax, $nbMaxCol);

		$rows = array();
		$lines = array();
		$lines['class'] = 'classScheduMatch';
		foreach ($times as $fullDate=>$ties)
		{
	  $rows[] = array(KOD_BREAK, "title", $fullDate, '', $divSel);
	  //$first[] = array(KOD_BREAK, "title", $title, '', $tie['tie_id']);

	  foreach($ties as $tie)
	  {
	  	if (count($tie)<$nbMaxCol)
	  	$row = $tie;
	  	else
	  	{
	  		$cpt = 0;
	  		$time = $tie[1];
	  		$row = array();
	  		foreach($tie as $cell)
	  		{
	  			if ($cpt==$nbMaxCol)
	  			{
	  				$rows[] = $row;
	  				$row = array();
	  				$row[]="0";
	  				$row[]=" ";// $lines;
	  				$cpt = 2;
	  			}
	  			$cpt++;
	  			$row[] = $cell;
	  		}
	  	}
	  	// Completer la rangee
	  	$size = count($row);
	  	for($i = $size; $i<$nbMax; $i++)
	  	$row[] = "";//$lines;
	  	$rows[] = $row;
	  }
		}

		//if (count($rows) <= count($dates))
		//	$kdiv->addWng("msgNoCalendars");
		//else
		{
	  $krow =& $kdiv->addRows("scheList", $rows);

	  $krow->displayTitle(false);
	  $krow->displayNumber(false);

	  $column[4] = 0;
	  $column[5] = 0;
	  $column[6] = 0;
	  $krow->setSortAuth($column);


	  $acts = array();
	  $acts[] = array( 'link' => array(KAF_NEWWIN, 'schedu',
	  SCHEDU_PDF,  'draw_id', 600, 600),
			   'icon' => utimg::getIcon(WBS_ACT_PRINT),
			   'title' => 'print');
	  $krow->setBreakActions($acts);
		}

		$cache = "schedu_".WBS_ACT_SCHEDU;
		if ($divId != '')
		$cache .= "_{$divId}";
		$cache .= ".htm";
		$utpage->display($cache);
		exit;
	}
	// }}}

}

?>
