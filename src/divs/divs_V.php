<?php
/*****************************************************************************
!   Module    : Divisions
!   File       : $Source: /cvsroot/aotb/badnet/src/divs/divs_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
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

require_once "divs.inc";
require_once "base_V.php";
require_once "utils/utteam.php";
require_once "utils/utimg.php";
require_once "ties/ties.inc";


/**
* Module de gestion des tableaux
*
* @author Gerard CANTEGRIL <cage@aotb.org>
* @see to follow
*
*/

class Divs_V
{

  // {{{ properties
  
  /**
   * Utils objet
   *
   * @private
   */
  var $_ut;
  
  /**
   * Database access object
   *
   * @private
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
  function Divs_V()
    {
      $this->_ut = new Utils();
      $this->_dt = new DivsBase_V();
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
	  // Display details of the team
	case KID_SELECT:
	  $id = kform::getData();        
	  $this->_displayFormList($id);
	  break;

	case WBS_ACT_DIVS:
	  $this->_displayFormList();
	  break;

	default:
	  echo "page $action demand�e depuis divs_V<br>";
	  exit();
	}
    }
  // }}}
    

  // {{{ _displayFormList()
  /**
   * Display a page with the list of the divisions of the current event
   *
   * @access private
   * @return void
   */
  function _displayFormList($divId='')
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      $utt =  new utteam();;
      
      // Create a new page
      $utpage = new utPage_V('divs', true, 'itDivisions');
      $content =& $utpage->getContentDiv();
      // $kdiv =& $content->addDiv('infos');
      $kdiv =& $content->addDiv('choix', 'onglet2');

      //--- List of divisions (ie the draws in the database)
      $divs = $utt->getDivs();
      if (isset($divs['errMsg']))
	$kdiv->addWng($divs['errMsg']);

      $divSel = $divId;
      if (!count($divs))
	$kdiv->addWng('msgNoDivs');
      else
	{
	  $divSel = $divId;
	  if ( ($divSel == '' || !isset($divs[$divSel])) && 
	       sizeof($divs))
	    {
	      $first = each($divs); 
	      $divSel = $first[0]; 
	    }

	  if (count($divs) > 1)
	    {
	      foreach ($divs as $idDiv=>$nameDiv)
		{
		  $items[$nameDiv] = array(KAF_UPLOAD, 'divs', 
					   KID_SELECT, $idDiv);
		}
	      // Ajout du menu sup�rieur
	      $kdiv->addMenu("menuDivs", $items, $divs[$divSel]);
	    }
	}
      
      $kdiv =& $content->addDiv('groups', 'cont2');
      
      // Display the groups of the selected division
      $nothingDisplay = true;
      $groups = $utt->getGroups($divSel);
      if (isset($groups['errMsg']))
	$kdiv->addWng($groups['errMsg']);
      if (!count($groups))
	{
	  $kdiv->addWng('msgNoGroups');
	  $utpage->display();
	  exit; 
	}

      // Displaying the groups 
      $numGroup=0;
      $nbDisplay = 0;
      $rows = array();
      foreach ( $groups as $groupSel=>$group)
	{
	  
	  if ( $group['rund_type'] == WBS_TEAM_GROUP ||
	       $group['rund_type'] == WBS_TEAM_BACK)
	    {
	      $first = $rows;
	      $first[] = array(KOD_BREAK, "title", $group['rund_name']);
	      $teams = $dt->getGroupTeams($groupSel);
	      if (!isset($teams['errMsg']) &&
		  count($teams))
		$rows = array_merge($first, $teams);
	    }
	}
      if (count($rows))
	{
	  $nothingDisplay = false;

	  $kdiv2 = & $kdiv->addDiv("divGroups", 'cartouche');
	  $kdiv2->addMsg('tGroups', '', 'titre');
	  $krow =& $kdiv2->addRows("rowsGroup", $rows);
	  $krow->setSort(0);
	  
	  $img[1]=4;
	  $krow->setLogo($img);
	  $size[4]=0;
	  $krow->setSize($size);
	  
	  $acts = array( 1 => array(KAF_UPLOAD, 'teams', KID_SELECT));
	  $krow->setActions($acts);
	}	  

      // Displaying the KO
      $rows = array();
      foreach($groups as $group)
	{
	  $title = array('firstRound', 'secondRound');
	  $actions[0] = array(KAF_UPLOAD, 'teams', KID_SELECT);
	  if ( $group['rund_type'] == WBS_TEAM_KO)
	    {
	      $nothingDisplay = false;
	      $groupId = $group['rund_id'];
	      $teams = $utt->getTeamsGroup($groupId);
	      $names= array();
	      foreach($teams as $team)
		$names[] = array('value' => $team['team_name'], 
				 'link' => $team['team_id']) ;
	      require_once "utils/utko.php";
	      $utko = new utKo($names);
	      $vals = $utko->getExpandedValues();

	      $kdiv2 = & $kdiv->addDiv("divDraw$groupId", 'cartouche');
	      $kdiv2->addMsg($group['rund_name'], '', 'titre');
	      $kdraw = & $kdiv2->addDraw("draw$groupId");
	      $kdraw->setNumCol(2);
	      $kdraw->setValues(1, $vals);

	      $posTies = $utko->getNumTies(2);
	      $vals = $dt->getDrawTeams($groupId, $posTies);
	      $kdraw->setValues(2, $vals);

	      $kdraw->setActions($actions);

	      $kdraw->setTitles($title);

	    }
	}

// 	  $sort = kform::getSort("rowsTies$numGroup", 3);
// 	  $ties = $dt->getTiesSchedu($groupSel, $sort);
// 	  if (!isset($ties['errMsg']))
// 	    {
// 	      $krow =& $kdiv->addRows("rowsTies$numGroup", $ties);
	      
// 	      $nbTies = count($ties);
// 	      $titleSche[0]=$group;
// 	      $krow->setTitles($titleSche);

// 	      $acts =  array(4=> array(KAF_UPLOAD, 'teams', 
// 				       KID_SELECT, 7),
// 			     array(KAF_UPLOAD, 'teams', 
// 				   KID_SELECT, 9),
// 			     array(KAF_UPLOAD, 'ties', 
// 				   TIES_SELECT_TIE));
// 	      $krow->setActions($acts);

// 	      $sizes = array(7=>0,0,0,0);
// 	      $krow->setSize($sizes);

// 	      $img[4] = 8;
// 	      $img[5]= 10;
// 	      $krow->setLogo($img);

// 	      $column[4] = 0;
// 	      $column[5] = 0;
// 	      $column[6] = 0;
// 	      $krow->setSortAuth($column);
// 	    }
//       if (!$numGroup)
// 	$kdiv->addWng("msgNoGroups");
//       if (!$nbDisplay)
// 	$kdiv->addWng("msgNoTeams");

      if ($nothingDisplay)
	$kdiv->addWng('msgNoGroups');

      $utpage->display();
      exit; 
    }
  // }}}
}

?>