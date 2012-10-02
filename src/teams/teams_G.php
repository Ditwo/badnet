<?php
/*****************************************************************************
!   Module     : Teams
!   File       : $Source: /cvsroot/aotb/badnet/src/teams/teams_G.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
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
require_once "base_G.php";
require_once "teams.inc";
require_once "utils/utimg.php";

/**
* Module de gestion des equipes : classe invites
*
* @author Gerard CANTEGRL <cage@free.fr>
* @see to follow
*
*/

class teams_G
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
  function teams_G()
    {
      $this->_dt = new teamsBase_G();
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
      switch($page)
	{
	case KID_SELECT:
	  $id = kform::getData();
	  $this->_displayForm($id);
	  break;

	case TEAM_RESERVATION:
	  $id = kform::getData();
	  $this->_displayReservation($id);
	  break;

	case TEAM_DISCOUNT:
	  $id = kform::getData();
	  $this->_displayDiscount($id);
	  break;

	case TEAM_CREDITS:
	  $id = kform::getData();
	  $this->_displayCredits($id);
	  break;

	default:
	  echo "vas-y jeannot: $page";
	  break;
	}
      exit;      
    }
  // }}}

  // {{{ _displayForm()
  /**
   * Display a page with the player/pairs of the selected team
   *
   * @access private
   * @return void
   */
  function _displayForm($id)
    {
      if (utvars::IsTeamEvent())
	$this->_displayFormTeam($id);
      else
	$this->_displayFormIndiv($id);
    }
  //}}}


  // {{{ _displayFormIndiv()
  /**
   * Display the page with the detail of the selected assos
   *
   * @access private
   * @param array team  info of the team
   * @return void
   */
  function _displayFormIndiv($assoId, $err='')
    {
      $dt = $this->_dt;
      $kdiv =& $this->_displayHead($assoId, 'itPlayers');

      // Display error
      if (isset($err['errMsg']))
	$kdiv->addWng($err['errMsg']);

     // Display list of members
      $teams = $dt->getTeams($assoId);
      $sort = kform::getSort("rowsRegis", 3);
      $rows = array();
      foreach($teams as $team)
	{
	  $first = $rows;
	  $first[] = array(KOD_BREAK, "title", $team['team_name'], '', $team['team_id']);
      	  $players = $dt->getPlayers($team['team_id'], $sort);
	  if (!isset($players['errMsg']))
	    $rows = array_merge($first, $players);
	}
      $krows =& $kdiv->addRows('rowsRegis', $rows);
      $sizes[5] = "0";
      $krows->setSize($sizes);
      
      $actions[2] = array(KAF_UPLOAD, 'resu', KID_SELECT);
      $krows->setActions($actions);
      unset($rows);

      // Display list the pairs
      $ut = new utils();
      $pairs = $dt->getPairs($assoId);
      if (isset($pairs['errMsg']))
	$kdiv->addWng($pairs['errMsg']);
      else
	{
	  $disci = -1;
	  foreach($pairs as $pair)
	    {
	      if ($disci != $pair['draw_disci'])
		{
		  $title = $ut->getLabel($pair['draw_disci']);
		  $rows[] = array(KOD_BREAK, "title", $title);
		  $disci = $pair['draw_disci'];
		}
	      $rows[] = $pair;
	    }
	  $krows =& $kdiv->addRows('rowsPairs_V', $rows);
	  $sizes[6] = "0+";
	  $krows->setSize($sizes);
	  $krows->setSort(0);
	  $acts[2] = array(KAF_UPLOAD, 'draws', KID_SELECT, 'draw_id');
	  $krows->setActions($acts);
	}
    

      //Display the page
      $this->_utPage->display();      
      exit;
    }
  // }}}

  // {{{ _displayDiscount()
  /**
   * Display the page with the discount of the selected team
   *
   * @access private
   * @param array team  info of the team
   * @return void
   */
  function _displayDiscount($teamId, $err='')
    {
      $dt = $this->_dt;
      $kdiv =& $this->_displayHead($teamId, 'itDiscount');
      
      // Display menu for register reservation
      $kdiv->addMsg('tDiscounts'); 

      // Get data
      $discounts = $dt->getPurchaseList($teamId);
      if (isset($discounts['errMsg']))
	{
	  $kdiv->addWng($discounts['errMsg']);
	  unset($discounts['errMsg']);
	}
      $regis = $dt->getMembers($teamId, 3);
      if (isset($regis['errMsg']))
	$kdiv->addWng($regis['errMsg']);
      $items = $dt->getSelectItems();
      if (isset($items['errMsg']))
	{
	  $kdiv->addWng($items['errMsg']);
	  $this->_utPage->display();
	  exit;
	}
      
      // Construire la table d'affichage
      $form =& $kdiv->addForm('fregi'); 
      $title[1] = "Sexe";
      $title[2] = "Identit�";
      $title[3] = "Fonction";
      $i = 4;
      foreach($items as $item=>$data)
	{
	  $title[$i++] = $data[1];
	  $form->addInfo($data[1].":", $data[2]);
	  $elts[] = $data[1].":";
	}

      // Create a new form
      $form->addBlock("blkLegende", $elts);
      $utd = new utdate();
      $nbItems = count($items);
      $date = '';
      $keys = array_keys($items);
      foreach($discounts as $discount)
	{
	  if ($discount['cmd_date'] != $date)
	    {
	      if ($date != '')
		$rows = array_merge($rows, $lines);
	      $date = $discount['cmd_date'];
	      $utd->setIsoDate($date);
	      $rows[] = array(KOD_BREAK, "title", $utd->getDate());
	      $lines=array();
	      foreach($regis as $regi)
		{
		  $line = array($regi[0], $regi[1], $regi[2], $regi[3]); 
		  $regi  = array_pad($line, $nbItems+4, '');
		  $lines[$regi[0]] = $regi;
		}	      
	    }  
	  $line = $lines[$discount['regi_id']];
	  $indx = array_keys($keys, $discount['item_id']);
	  $line[$indx[0]+4] += $discount['cmd_discount'];
	  $lines[$discount['regi_id']] = $line;
	}
      if ($date != '')
	{
	  $rows = array_merge($rows, $lines);
	  $krow =& $form->addRows('reservations', $rows);
	  $krow->displaySelect(false);
	  $krow->setTitles($title);
	  $krow->setSort(0);
	}
      //Display the page
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
  function _displayReservation($teamId, $err='')
    {
      $dt = $this->_dt;
      $kdiv =& $this->_displayHead($teamId, 'itReservation');
      
      // Display menu for register reservation
      $kdiv->addMsg('tReservations'); 

      // Get data
      $resas = $dt->getPurchaseList($teamId);
      if (isset($resas['errMsg']))
	{
	  $kdiv->addWng($resas['errMsg']);
	  unset($resas['errMsg']);
	}
      $regis = $dt->getMembers($teamId, 3);
      if (isset($regis['errMsg']))
	$kdiv->addWng($regis['errMsg']);
      $items = $dt->getSelectItems();
      if (isset($items['errMsg']))
	{
	  $kdiv->addWng($items['errMsg']);
	  $this->_utPage->display();
	  exit();
	}
      
      // Construire la table d'affichage
      //$form =& $kdiv->addForm('fregi'); 
      $title[1] = "Sexe";
      $title[2] = "Identit�";
      $title[3] = "Fonction";
      $i = 4;
      $klgd =& $kdiv->addDiv('blkLegende');
      foreach($items as $item=>$data)
	{
	  $title[$i++] = $data[1];
	  $klgd->addInfo($data[1].":", $data[2]);
	}

      $utd = new utdate();
      $nbItems = count($items);
      $date = '';
      $keys = array_keys($items);
      foreach($resas as $resa)
	{
	  if ($resa['cmd_date'] != $date)
	    {
	      if ($date != '')
		$rows = array_merge($rows, $lines);
	      $date = $resa['cmd_date'];
	      $utd->setIsoDate($date);
	      $rows[] = array(KOD_BREAK, "title", $utd->getDate());
	      $lines=array();
	      foreach($regis as $regi)
		{
		  $line = array($regi[0], $regi[1], $regi[2], $regi[3]);
		  $regi  = array_pad($line, $nbItems+4, '');
		  $lines[$regi[0]] = $regi;
		}	      
	    }  
	  $line = $lines[$resa['regi_id']];
	  $indx = array_keys($keys, $resa['item_id']);
	  $line[$indx[0]+4]++;
	  $lines[$resa['regi_id']] = $line;
	}
      if ($date != '')
	{
	  $rows = array_merge($rows, $lines);
	  $krow =& $kdiv->addRows('reservations', $rows);
	  $krow->displaySelect(false);
	  $krow->setTitles($title);
	  $krow->setSort(0);
	}
      //Display the page
      $this->_utPage->display();      
      exit;
    }
  // }}}

  // {{{ _displayCredits()
  /**
   * Display the page with the discount of the selected team
   *
   * @access private
   * @param array team  info of the team
   * @return void
   */
  function _displayCredits($teamId, $err='')
    {
      $dt = $this->_dt;
      $kdiv =& $this->_displayHead($teamId, 'itCredits');
      
      $kdiv->addMsg('tCredits'); 
      $form =& $kdiv->addForm('fregi'); 

      $sort = kform::getSort("rowsPurchase", 3);
      $credits = $dt->getCredits($teamId, $sort);
      if (isset($credits['errMsg']))
 	$form->addWng($credits['errMsg']);
      else
 	{
	  $krows =& $form->addRows('rowsCredits', $credits);

	  $sizes = array(10=>'0');
	  $krows->setSize($sizes);
	  
          $actions[0] = array(KAF_NEWWIN, 'regi', KID_EDIT, 0,
                              650, 450);
	  //$krows->setActions($actions);
	}

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
  function & _displayHead($assoId, $select)
    {
      $dt = $this->_dt;
      
      // Create a new page
      $this->_utPage = new utPage_V('teams', true, 1);
      $content =& $this->_utPage->getContentDiv();

      $asso = $dt->getAsso($assoId);
      if (isset($asso['errMsg']))
	$content->addWng($asso['errMsg']);


      $assos = $dt->getAssos(2);
      if (!isset($assos['errMsg']))
	{
	  $kcombo=& $content->addCombo('assoList', $assos, $assos[$assoId]);
	  $acts[1] = array(KAF_UPLOAD, 'teams', KID_SELECT);
	  $kcombo->setActions($acts);
	}
      else
	{
	  $div =& $content->addDiv('teamNameV');
	  $size['maxWidth'] = 30;
	  $size['maxHeight'] = 30;
	  $logo = utimg::getPathFlag($asso['asso_logo']);
	  $kimg =& $div->addImg("assoLogo", $logo, $size);
	  $kimg->setText($asso['asso_name']);
	}

      
      /*$team = $dt->getTeam($teamId);
      if (isset($team['errMsg']))
	$content->addWng($team['errMsg']);

      */
      //$content->addInfo("teamNameV", $asso['asso_name']);


      // Display general informations
      $div =& $content->addDiv('blkTeam', 'blkInfo');
      $div->addInfo("assoPseudo",     $asso['asso_pseudo']);
      $div->addInfo("assoStamp",     $asso['asso_stamp']);
      $div->addInfo("assoNoc",     $asso['asso_noc']);
      $kinfo =& $div->addInfo("assoUrl", $asso['asso_url']);
      $kinfo->setUrl($asso['asso_url']);

      // Add a menu for different action
      $kdiv =& $content->addDiv('choix', 'onglet2');
      $items['itPlayers'] = array(KAF_UPLOAD, 'teams', 
				  KID_SELECT, $assoId);
      $items['itReservation'] = array(KAF_UPLOAD, 'teams', 
				      TEAM_RESERVATION, $assoId);
      $items['itDiscount'] = array(KAF_UPLOAD, 'teams', 
				    TEAM_DISCOUNT, $assoId);
      $items['itCredits'] = array(KAF_UPLOAD, 'teams', 
				  TEAM_CREDITS, $assoId);
      $kdiv->addMenu("menuType", $items, $select);
      $kdiv =& $content->addDiv('teamDiv', 'cont2');
      return $kdiv;
    }
  // }}}

}
?>