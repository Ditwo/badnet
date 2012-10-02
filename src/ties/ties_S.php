<?php
/*****************************************************************************
!   Module     : Ties
!   File       : $Source: /cvsroot/aotb/badnet/src/ties/ties_S.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.5 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:21 $
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
require_once "base_V.php";
require_once "matches/matches.inc";
require_once "utils/utscore.php";
require_once "utils/utteam.php";
require_once "utils/utpage_V.php";
require_once "utils/utimg.php";
require_once "utils/utevent.php";
require_once "live/live.inc";
require_once "ties.inc";

/**
* Module de gestion des rencontres : classe administrateur
*
* @author Gerard CANTEGRL <cage@free.fr>
* @see to follow
*
*/

class ties_S
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
  function ties_S()
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
      $ute = new utevent();
      $tieId = kform::getData();
      switch ($page)
	{
	  //	    case WBS_ACT_TIES:
	  //case KID_DEFAULT:
	  //case TIES_PDF_RESULTS:
	  //case TIES_PDF_RANKING:
	  
	case TIES_UPDATE_RESULTS:
	  $tieId = kform::getInput('tie_id', -1);
	  if (! $ute->isTieAuto($tieId))
	    {
	      require_once "ties_V.php";
	      $a = new ties_V();
	      $a->start($page);
	    }
	  else
	    {
	      require_once "ties_A.php";
	      $a = new ties_A();
	      $a->start($page);
	    }
	  break;
	  
	case TIES_SELECT_RESULTS:
	  $tieId = kform::getData();
	  if (! $ute->isTieAuto($tieId))
	    {
	      require_once "ties_V.php";
	      $a = new ties_V();
	      $a->start(WBS_ACT_TIES);
	    }
	  else
	    {
	      require_once "ties_A.php";
	      $a = new ties_A();
	      $a->start($page);
	    }
	  break;
	case TIES_SELECT_TIE:
	  $tieId = kform::getData();
	  if (! $ute->isTieAuto($tieId))
	    {
	      require_once "ties_V.php";
	      $a = new ties_V();
	      $a->start($page);
	    }
	  else
	    {
	      $this->_displayFormTie($tieId);
	    }
	  break;
	  
	default:
	  require_once "ties_V.php";
	  $a = new ties_V();
	  $a->start($page);
	  break;
	  exit;
	}
    }
  // }}}
  
  // {{{ _displayFormTie()
  /**
   * Display a page with the list of the Matchs
   *
   * @access private
   * @param  integer $tieId  Id of the tie
   * @return void
   */
  function _displayFormTie($tieId)
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      $utpage = new utPage_V('ties', true, 3);
      $content =& $utpage->getContentDiv();
      $div =& $content->addDiv('choix', 'onglet3');
      $div =& $content->addDiv('contenu', 'cont3');

      //--- List of the matches of the selected tie
      if ($tieId != "")
	{
	  $teams = $dt->getTeams($tieId);
	  $group = $dt->getGroup($tieId);
	  // Titre : Equipe 1 - Equipe 2
	  if (isset($teams[0][1]) &&
	      isset($teams[1][1]))
	    {
	      $titre = $teams[0][1].'('.$teams[0][3].')'."-".
		$teams[1][1].'('.$teams[1][3].')';
	    }
	  else
	    $titre = '';

	  $kdiv =& $div->addDiv('divInfo', 'cartouche');
	  $kinfo =& $kdiv->addInfo("draw_name", $group['draw_name']);
	  $act[1] = array(KAF_UPLOAD, 'ties', WBS_ACT_TIES, 
			  $group['draw_id']);
	  $kinfo->setActions($act);
	  $kinfo =& $kdiv->addInfo("rund_name", $group['rund_name']);
	  $kinfo =& $kdiv->addInfo("tie_step", $group['tie_step']);
	  $kinfo =& $kdiv->addInfo("tie_place", $group['tie_place']);
	  $kinfo =& $kdiv->addInfo("tie_schedule", $group['tie_schedule']);

	  $div->addDiv('break4', 'blkNewPage');

	  // Get the matchs
	  if (isset($teams[0][0]) &&
	      isset($teams[1][0]))
	    $matchs = $dt->getMatchs($tieId, $teams[0][0], $teams[1][0]);
	  else
	    $matchs['errMsg'] = 'msgIncompletedTie';
	  if (isset($matchs['errMsg']))
	    $div->addWng($matchs['errMsg']);
	  else
	    {
	      $break = array(KOD_BREAK, "title", $titre, '', $tieId);
	      array_unshift($matchs, $break);

	      $krow =& $div->addRows("rowsMatchs", $matchs);  
	      
	      $titles = array(2=>"A&nbsp;:&nbsp;".$teams[0][1], 
			      "B&nbsp;:&nbsp;".$teams[1][1]);
	      $krow->setTitles($titles);
	      
	      $krow->displaySelect(false);
	      $krow->setSort(false);
	      $sizes = array(11=> 0, 0, 0, 0, 0);
	      $krow->setSize($sizes);

	      $img[1]= 13;
	      $img[2]= 14;
	      $img[3]= 15;
	      $krow->setLogo($img);
	      $actions[1]=  array(KAF_NEWWIN, 'matches', KID_EDIT,
				  0, 620,350);
	      $krow->setActions($actions);
	      $acts = array();
	      $acts[] = array( 'link' => array(KAF_NEWWIN, 'live', 
					       LIVE_TIE_PDF,  0, 400, 300),
			       'icon' => utimg::getIcon(LIVE_TIE_PDF),
			       'title' => 'teamDeclaration');
	      $acts[] = array( 'link' => array(KAF_NEWWIN, 'ties', 
					       TIES_SELECT_RESULTS,  0, 750, 500),
			       'icon' => utimg::getIcon(WBS_ACT_EDIT),
			       'title' => 'teamEdit');
	      $acts[] = array( 'link' => array(KAF_NEWWIN, 'ties', 
					       TIES_PDF_RESULTS, 0, 400, 300),
			       'icon' => utimg::getIcon(WBS_ACT_PRINT),
			       'title' => 'teamResults');
	      $krow->setBreakActions($acts);	  
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

      $utpage->display();
      exit; 
    }
  // }}}
}
?>