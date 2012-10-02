<?php
/*****************************************************************************
!   Module     : Registrations
!   File       : $Source: /cvsroot/aotb/badnet/src/offo/offo_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.2 $
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
require_once "regi/regi.inc";
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
	case KID_SELECT:
	case WBS_ACT_REGI:
	case REGI_TEAM:
	  $this->_displayFormTeams();
	  break;
	default:
	  echo "regi_V->start($action) non autorise <br>";
	  exit;
	}
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

      $sort = kform::getSort("rowsTeams", 2);
      $rows = $dt->getTeams($sort);
      if (isset($rows['errMsg']))
	$div->addWng($rows['errMsg']);
      else
	{
	  $krow =& $div->addRows("rowsTeams", $rows);
	  $sizes = array(5=>0,0);
	  $krow->setSize($sizes);
	  $img[1]=5;
	  $krow->setLogo($img);
	  $actions = array( 1 => array(KAF_UPLOAD, 'teams', KID_SELECT));
	  $krow->setActions($actions);
	  $urls[3] = array('', 6);
	  $krow->setUrl($urls);
	}

      //Display the page
      $this->_utpage->display();
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
      $sort = kform::getSort("rowsRegiV", 3);
      $rows = $dt->getOfficials($sort);     
      if (isset($rows['errMsg']))
	$div->addWng($rows['errMsg']);
      else
	{
	  /*$kmsg = $div->addMsg("[a-t]");
	  $actions[1] = array(KAF_UPLOAD, 'regi', REGI_OFFICIAL, 'at');
	  $kmsg->setActions($actions);
	  $kmsg = $div->addMsg("[t-z]");
	  $actions[1] = array(KAF_UPLOAD, 'regi', REGI_OFFICIAL, 'tz');
	  $kmsg->setActions($actions);*/
	  $krow =& $div->addRows("rowsOffiV", $rows);  
	  $krow->displaySelect(false);

	  $sizes = array(6 => 0,0);
	  $krow->setSize($sizes);
	}
      
      $this->_utpage->display();
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
      
      // Create a new page
      $div =& $this->_displayHead('itPlayers');
      $sort = kform::getSort("rowsRegiV", 3);

      $rows = $dt->getPlayers($sort);     
      if (isset($rows['errMsg']))
	$div->addWng($rows['errMsg']);
      else
	{
	  $krow =& $div->addRows("rowsRegiV", $rows);  
	  $krow->displaySelect(false);

	  $sizes = array(6 => 0,0,0);
	  $krow->setSize($sizes);

	  $acts[5] = array(KAF_UPLOAD, 'teams', KID_SELECT, 6);
	  $acts[2] = array(KAF_UPLOAD, 'resu', KID_SELECT);
	  $acts[3] = array(KAF_UPLOAD, 'resu', KID_SELECT);
	  $krow->setActions($acts);

	  $urls[4] = array($ut->getParam("ffba_url"), 7);
	  $krow->setUrl($urls);
	}
      
      $this->_utpage->display();
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
      $kdiv =& $content->addDiv('choix', 'onglet2');
      $items['itTeams']     = array(KAF_UPLOAD, 'regi', REGI_TEAM);
      $items['itPlayers']   = array(KAF_UPLOAD, 'regi', REGI_PLAYER);
      $items['itOfficials'] = array(KAF_UPLOAD, 'regi', REGI_OFFICIAL);
      $kdiv->addMenu("menuType", $items, $select);
      $kdiv =& $content->addDiv('register', 'cont2');
      return $kdiv;
    }
  // }}}


  
}
?>