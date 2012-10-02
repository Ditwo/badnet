<?php
/*****************************************************************************
!   Module     : Registrations
!   File       : $Source: /cvsroot/aotb/badnet/src/regi/regi_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.11 $
!   Author     : D.BEUVELOT
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
!   Mailto     : didier.beuvelot@free.fr
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
require_once "regi.inc";
require_once "utils/utimg.php";

/**
* Module de gestion des inscriptions
*
* @author Didier BEUVELOT <didier.beuvelot@free.fr>
* @see to follow
*
*/

class regi_V
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
  function regi_V()
    {
      $this->_ut = new utils();
      $this->_dt = new registrationBase_V();
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
  function start($action)
    {
      switch ($action)
        {
	case REGI_PLAYER:
	  $this->_displayFormPlayers();
	  break;

	case REGI_OFFICIAL:
	  $this->_displayFormOfficials();
	  break;

	case WBS_ACT_REGI:
	  $this->_displayForm();
	  break;
	default:
	  echo "regi_V->start($action) non autorise <br>";
	  exit;
	}
    }
  // }}}

  // {{{ _displayForm()
  /**
   * Display a page with the list of registered team/assos
   *
   * @access private
   * @return void
   */
  function _displayForm()
    {
      if (utvars::isTeamEvent())
	$this->_displayFormTeams();
      else
	$this->_displayFormAssos();
    }
  //}}}

  // {{{ _displayFormAssos()
  /**
   * Display a page with the list of the registered assos
   *
   * @access private
   * @param  integer $sort  Column selected for sort
   * @return void
   */
  function _displayFormAssos()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Create a new page
      $div =& $this->_displayHead('itTeams');
	  $khead =& $div->addDiv('headCont3', 'headCont3');
      $div =& $div->addDiv('corp3', 'corpCont3');
	  
      $sort = kform::getSort("rowsAssos", 2);
      $rows = $dt->getAssos($sort);
      if (isset($rows['errMsg']))
	$div->addWng($rows['errMsg']);
      else
	{
	  $krow =& $div->addRows("rowsAssos", $rows);
	  $sizes[4] = '0+';
	  $krow->setSize($sizes);
	  $img[1]=5;
	  $krow->setLogo($img);
	  $urls[3] = array('', 'asso_url');
	  $krow->setUrl($urls);
	}
      $div->addDiv('break', 'blkNewPage');

      //Display the page
      $cache = "regi_".WBS_ACT_REGI;
      $sort = kform::getSort("", "");
      if ($sort!="")
	$cache .= "_{$sort}";
      $cache .= ".htm";
      $this->_utpage->display($cache);
      exit; 
    }
  // }}}

  // {{{ _displayFormTeams()
  /**
   * Display a page with the list of the registered teams
   *
   * @access private
   * @param  integer $sort  Column selected for sort
   * @return void
   */
  function _displayFormTeams()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Create a new page
      $div =& $this->_displayHead('itTeams');
	  $khead =& $div->addDiv('headCont3', 'headCont3');
      $div =& $div->addDiv('corp3', 'corpCont3');
      
      $sort = kform::getSort("rowsTeams", 2);
      $rows = $dt->getTeams($sort);
      if (isset($rows['errMsg']))
	$div->addWng($rows['errMsg']);
      else
	{
	  $krow =& $div->addRows("rowsTeams", $rows);
	  $sizes[5] = '0+';
	  $krow->setSize($sizes);
	  $img[1]=5;
	  $krow->setLogo($img);
	  $actions = array( 1 => array(KAF_UPLOAD, 'teams', KID_SELECT));
	  $krow->setActions($actions);
	  $urls[3] = array('', 'asso_url');
	  $krow->setUrl($urls);
	}

      $div->addDiv('break', 'blkNewPage');
      //Display the page
      $cache = "regi_".WBS_ACT_REGI;
      $sort = kform::getSort("", "");
      if ($sort!="")
	$cache .= "_{$sort}";
      $cache .= ".htm";
      $this->_utpage->display($cache);
      exit; 
    }
  // }}}

  // {{{ _displayFormOfficials()
  /**
   * Display a page with the list of the registrations
   *
   * @access private
   * @param  integer $sort  Column selected for sort
   * @return void
   */
  function _displayFormOfficials()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Create a new page
      $div =& $this->_displayHead('itOfficials');
      $khead =& $div->addDiv('headCont3', 'headCont3');
      $div =& $div->addDiv('corp3', 'corpCont3');
      
      $sort = kform::getSort("rowsOffiV", 3);

      $rows = $dt->getOfficials($sort);     
      if (isset($rows['errMsg']))
	$div->addWng($rows['errMsg']);
      else
	{
	  $type = '';
	  foreach ($rows as $offi)
	    {
	      if ($type != $offi[6])
		{
		  $type = $offi[6];
		  $offis[] = array(KOD_BREAK, "title", $type);
		}
	      $offis[] = $offi;
	    }

	  $krow =& $div->addRows("rowsOffiV", $offis);  
	  $krow->displaySelect(false);

	  $sizes = array(4 => 0,0,0);
	  $krow->setSize($sizes);
	  $img[3]=5;
	  $krow->setLogo($img);
	}
      $div->addDiv('offoBreak', 'blkNewPage');
      
      $cache = "regi_".REGI_OFFICIAL;
      $sort = kform::getSort("", "");
      if ($sort!="")
	$cache .= "_{$sort}";
      $cache .= ".htm";
      $this->_utpage->display($cache);
      exit; 
    }
  // }}}

  // {{{ _displayFormPlayers()
  /**
   * Display a page with the list of the registrations
   *
   * @access private
   * @param  integer $sort  Column selected for sort
   * @return void
   */
  function _displayFormPlayers()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      $utev = new utEvent();
      
      // Create a new page
      $div =& $this->_displayHead('itPlayers');
      $khead =& $div->addDiv('headCont3', 'headCont3');
      $div =& $div->addDiv('corp3', 'corpCont3');
      
      $sort = kform::getSort("rowsRegiV", 3);

      $rows = $dt->getPlayers($sort);     
      if (isset($rows['errMsg']))
	$div->addWng($rows['errMsg']);
      else
	{
// 	  $kmsg = $div->addMsg("[a-t]");
// 	  $actions[1] = array(KAF_UPLOAD, 'regi', REGI_OFFICIAL, 'at');
// 	  $kmsg->setActions($actions);
// 	  $kmsg = $div->addMsg("[t-z]");
// 	  $actions[1] = array(KAF_UPLOAD, 'regi', REGI_OFFICIAL, 'tz');
// 	  $kmsg->setActions($actions);

	  $krow =& $div->addRows("rowsRegiV", $rows);  
	  $krow->displaySelect(false);

	  $img[2]='photo';
	  $img[3]='logo';
	  $krow->setLogo($img);

	  $sizes[6] = '0+';
	  $eventId = utvars::getEventId();
	  if ($utev->getEventType($eventId)== WBS_EVENT_INDIVIDUAL)
	    $sizes[4] = '0';
	  else
	    $sizes[5] = '0';
	  $krow->setSize($sizes);


	  $acts[2] = array(KAF_UPLOAD, 'resu', KID_SELECT);
	  $acts[4] = array(KAF_UPLOAD, 'teams', KID_SELECT, 'team_id');
	  $acts[5] = array(KAF_UPLOAD, 'teams', KID_SELECT, 'asso_id');
	  $krow->setActions($acts);
	}
      $div->addDiv('playerBreak', 'blkNewPage');
      
      $cache = "regi_".REGI_PLAYER;
      $sort = kform::getSort("", "");
      if ($sort!="")
	$cache .= "_{$sort}";
      $cache .= ".htm";
      $this->_utpage->display($cache);
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
  function & _displayHead($select)
    {
      $dt = $this->_dt;
      
      // Create a new page
      $this->_utpage = new utPage_V('regi', true, 'itRegister');
      $content =& $this->_utpage->getContentDiv();

      // Add a menu for different action
      $kdiv =& $content->addDiv('choix', 'onglet3');
      $items['itTeams']     = array(KAF_UPLOAD, 'regi', WBS_ACT_REGI);
      $items['itPlayers']   = array(KAF_UPLOAD, 'regi', REGI_PLAYER);
      $items['itOfficials'] = array(KAF_UPLOAD, 'regi', REGI_OFFICIAL);
      $kdiv->addMenu("menuType", $items, $select);
      $kdiv =& $content->addDiv('register', 'cont3');
      return $kdiv;
    }
  // }}} 
}
?>
