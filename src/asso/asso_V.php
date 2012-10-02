<?php
/*****************************************************************************
!   Module     : Associations
!   File       : $Source: /cvsroot/aotb/badnet/src/asso/asso_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.7 $
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

require_once "base_V.php";
require_once "asso.inc";


/**
* Module de gestion des associations : classe visiteurs
*
* @author Gerard CANTEGRIL
* @see to follow
*
*/

class Asso_V
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
  function asso_V()
    {
      $this->_ut = new utils();
      $this->_dt = new assoBase_V();
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
      switch ($page)
        {
	  // Display main windows for member
	case KID_SELECT:
	  $this->_displayForm();
	  break;

	case ASSOC_CMD_PLAYERS:
	  $this->_displayReservation(3);
	  break;

	case ASSOC_CMD_ITEMS:
	  $this->_displayReservation(2);
	  break;

	case ASSOC_CMD_DATE:
	  $this->_displayReservation(1);
	  break;

	default:
	  echo "page $page demand�e depuis asso_A<br>";
	  exit();
	}
    }
  // }}}

  // {{{ _displayForm()
  /**
   * Display a page with the player/pairs of the selected team
   *
   * @access private
   * @return void
   */
  function _displayForm()
    {
      $ute = new utevent();
      $assoId = kform::getData();
      if (!$ute->isTeamAuto($assoId))
	{
	  echo "non autorise $assoId";
	  exit;
	}
      $eventId = kform::getInput('eventId');
      $event = $ute->getEvent($eventId);
      if ($event['evnt_type'] == WBS_EVENT_INDIVIDUAL)
	$this->_displayFormIndiv($event, $assoId);
      else
	$this->_displayFormTeam($event, $assoId);
    }
  //}}}


  // {{{ _displayFormTeam()
  /**
   * Display the page with the detail of the selected assos
   *
   * @access private
   * @param array team  info of the team
   * @return void
   */
  function _displayFormTeam($event, $assoId, $err='')
    {
      $dt = $this->_dt;

      //$teamId = $dt->getAssoTeam($assoId);

      $kdiv =& $this->_displayHead($event, $assoId, 0);

      // Display list of players
      $sort = kform::getSort("rowsPlayers", 3);
      $players = $dt->getPlayers($event['evnt_id'], $assoId, $sort);
      if (isset($players['errMsg']))
	{
	  $kdiv->addWng($players['errMsg']);
	  unset($players['errMsg']);
	}
      if (count($players))
	{
	  $krows =& $kdiv->addRows('rowsPlayers', $players);
	  $sizes[5] = "0";
	  $krows->setSize($sizes);
	  
	  $actions[2] = array(KAF_UPLOAD, 'resu', KID_SELECT);
	  //$krows->setActions($actions);
	}

      $this->_utPage->display();      
      exit;
    }
  // }}}


  // {{{ _displayFormIndiv()
  /**
   * Display the page with the detail of the selected assos
   *
   * @access private
   * @param array team  info of the team
   * @return void
   */
  function _displayFormIndiv($event, $assoId, $err='')
    {
      $dt = $this->_dt;
      $kdiv =& $this->_displayHead($event, $assoId, 0);

      // Display list of players
      $sort = kform::getSort("rowsPlayers", 3);
      $players = $dt->getPlayers($event['evnt_id'], $assoId, $sort);
      if (isset($players['errMsg']))
	{
	  $kdiv->addWng($players['errMsg']);
	  unset($players['errMsg']);
	}
      if (count($players))
	{
	  $krows =& $kdiv->addRows('rowsPlayers', $players);
	  $sizes[5] = "0";
	  //$krows->setSize($sizes);
	  
	  $actions[2] = array(KAF_UPLOAD, 'resu', KID_SELECT);
	  //$krows->setActions($actions);
	}

      $this->_utPage->display();      
      exit;
    }
  // }}}

  // {{{ _displayReservation()
  /**
   * Display the page with the reservation of the selected team
   *
   * @access private
   * @param array team  info of the team
   * @return void
   */
  function _displayReservation($sort)
    {
      $dt = $this->_dt;

      $ute = new utevent();
      $assoId = kform::getData();
      if (!$ute->isTeamAuto($assoId))
	{
	  echo "non autorise $assoId";
	  exit;
	}
      $eventId = kform::getInput('eventId');
      $event = $ute->getEvent($eventId);
      $kdiv =& $this->_displayHead($event, $assoId, $sort);
      
      $fields = array(1=>'cmd_date', 'cmd_name', 'regi_longName');
      $field = $fields[$sort];

      // Get data
      $resas = $dt->getPurchaseList($event['evnt_id'], $assoId, $sort);
      if (isset($resas['errMsg']))
	{
	  $kdiv->addWng($resas['errMsg']);
	  unset($resas['errMsg']);
	}
      $id = NULL;
      $cumul = array('value' =>0.0,
		     'discount' => 0.0,
		     'payed' => 0.0,
		     'du' => 0.0);
      $sub = array('value' =>0.0,
		   'discount' => 0.0,
		   'payed' => 0.0,
		   'du' => 0.0);
      foreach($resas as $resa)
	{
	  if ($resa[$field] != $id)
	    {
	      if (!is_null($id) && $nb >1)
		$rows[] = array(0,'','','',$sub['value'], $sub['discount'],
				$sub['payed'],$sub['du'],'','');
	      $rows[] = array(KOD_BREAK, "title", $resa[$field]);
	      $id = $resa[$field];
	      $sub = array('value' =>0.0,
			   'discount' => 0.0,
			   'payed' => 0.0,
			   'du' => 0.0);
	      $nb = 0;
	    }
	  $nb++;
	  $rows[] = $resa;
	  $sub['value'] += $resa['cmd_value'];
	  $sub['discount'] += $resa['cmd_discount'];
	  $sub['payed'] += $resa['cmd_payed'];
	  $sub['du'] += $resa['cmd_du'];
	  $cumul['value'] += $resa['cmd_value'];
	  $cumul['discount'] += $resa['cmd_discount'];
	  $cumul['payed'] += $resa['cmd_payed'];
	  $cumul['du'] += $resa['cmd_du'];
	  //	      $date = $resa['cmd_date'];
	  //    $utd->setIsoDate($date);
	}
      if (!is_null($id) && $nb >1)
	$rows[] = array(0,'','','',$sub['value'], $sub['discount'],
			$sub['payed'],$sub['du'],'','');
      $rows[] = array(KOD_BREAK, "title", "Totaux");
      $rows[] = array(0,'','','',$cumul['value'], $cumul['discount'],
		      $cumul['payed'],$cumul['du'],'','');
      $krow =& $kdiv->addRows('reservations', $rows);
      $krow->displaySelect(false);
      $krow->setSort(0);
      $krow->displayNumber(false);
      $sizes[$sort] = 0;
      $sizes[8] = '0+';
      $krow->setSize($sizes);
	
      //Display the page
      $this->_utPage->display();      
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
  function & _displayHead(&$event, $assoId, $select)
    {
      $dt = $this->_dt;
      
      // Create a new page
      $this->_utPage = new utPage_V('asso', true, 0);
      $content =& $this->_utPage->getContentDiv();

      $head =& $content->addDiv('headEvent');
      $head->addMsg('eventTitle', $event['evnt_name']);
      $head->addMsg('dateTitle', $event['evnt_date']);
      $head->addDiv('break', 'blkNewPage');

      // Add a menu for different action
      $kdiv =& $content->addDiv('choix', 'onglet2');
      $items['itEntries'] = array(KAF_UPLOAD, 'asso',  KID_SELECT,
				  "{$assoId}&eventId={$event['evnt_id']}");

      $items['itReservation'] = array(KAF_UPLOAD, 'asso', ASSOC_CMD_DATE,
				  "{$assoId}&eventId={$event['evnt_id']}");
      $items['itItems'] = array(KAF_UPLOAD, 'asso', ASSOC_CMD_ITEMS,
				  "{$assoId}&eventId={$event['evnt_id']}");
      $items['itPlayers'] = array(KAF_UPLOAD, 'asso',  ASSOC_CMD_PLAYERS,
				  "{$assoId}&eventId={$event['evnt_id']}");      
      $items['itCompetition'] = array(KAF_UPLOAD, 'cnx', WBS_ACT_SELECT_EVENT, 
				      "{$assoId}&eventId={$event['evnt_id']}&pageId=teams");
      
      $keys = array_keys($items);
      $kdiv->addMenu("menuType", $items, $keys[$select]);
      $kdiv =& $content->addDiv('teamDiv', 'cont2');
      //$kdiv->addMsg($event['evnt_name'], '', 'titre');
      return $kdiv;
    }
  // }}}
  
}

?>