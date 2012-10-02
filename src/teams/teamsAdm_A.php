<?php
/*****************************************************************************
!   Module     : Registration
!   File       : $Source: /cvsroot/aotb/badnet/src/teams/teamsAdm_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.9 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/05/06 07:59:33 $
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

require_once "teams.inc";
require_once "baseAdm_A.php";
require_once "offo/offo.inc";
require_once "purchase/purchase.inc";
require_once "items/items.inc";
require_once "regi/regi.inc";
require_once "mber/mber.inc";

require_once "utils/utpage_A.php";


/**
* Module de gestion des associations : classe visiteurs
*
* @author Gerard CANTEGRIL
* @see to follow
*
*/

class teamsAdm_A
{
  
  // {{{ properties  
  /**
   * Tools access object
   *
   * @var     object
   * @access  private
   */
  var $_utPage;
  
  // }}}
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function teamsAdm_A()
    {
      $this->_ut = new utils();
      $this->_dt = new teamsAdmBase_A();
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

	case WBS_ACT_TEAMS:
	  $this->_displayFormList();
	  break;
	case KID_SELECT:
	  $id = kform::getData();        
	  $this->_displayAdmiDetail($id);
	  break;

	case KID_EDIT:
	  $id = kform::getData();
	  $this->_displayFormAccount($id);
	  break;
	case KID_UPDATE:
	  $this->_updateAccount();
	  break;

	case TEAM_PDF_BADGES:
	case TEAM_PDF_PURCHASE:
	  require_once "teamsPdf.php";
	  $pdf = new teamsPdf();
	  $pdf->start($page);
 	  break;

	case KID_CONFIRM:
	  $this->_displayFormConfirm();
	  break;
	case KID_DELETE:
	  $this->_delTeams();
	  break;

	case TEAM_DETAILS_DATE:
	  $this->_displayFormReservation(1);
	  break;
	case TEAM_DETAILS_ITEMS:
	  $this->_displayFormReservation(2);
	  break;
	case TEAM_DETAILS_PLAYERS:
	  $this->_displayFormReservation(3);
	  break;
	case TEAM_ROOM:
	  $id = kform::getData();        
	  $this->_displayFormRoom($id);
	  break;

	case TEAM_RESERVATION:
	  $id = kform::getData();        
	  $this->_displayReservation($id);
	  break;
	case TEAM_DISCOUNT:
	  $id = kform::getData();        
	  $this->_displayDiscount($id);
	  exit;
	  break;
	case TEAM_CREDITS:
	  $id = kform::getData();        
	  $this->_displayCredits($id);
	  break;


	default:
	  echo "page $page demand�e depuis teamsAdm_A<br>";
	  exit();
	}
    }
  // }}}

  // {{{ _displayFormRoom()
  /**
   * Display the page with the rooms of the selected team
   * @return void
   */
  function _displayFormRoom($teamId)
    {
      $dt = $this->_dt;
      // New page
      // Create a new page
      $this->_utPage = new utPage_A('teams', true, 'itAccomodation');
      $content =& $this->_utPage->getContentDiv();

      // Add a menu for different action
      $kdiv =& $content->addDiv('choix', 'onglet3');
      $items['itHotel'] = array(KAF_UPLOAD, 'items', WBS_ACT_ACCOMODATION);
      $items['itTeams'] = array(KAF_UPLOAD, 'teams', TEAM_ROOM);
      $kdiv->addMenu("menuType", $items, 'itTeams');
      $kdiv =& $content->addDiv('contType', 'cont3');

      // List of teams
      $teams = $dt->getTeams(3);
      foreach($teams as $team)
	  $list[$team['team_id']] = "{$team['asso_pseudo']} :: {$team['team_name']}";
      if (!isset($list[$teamId]))
	  $teamId = $team['team_id'];
      $kcombo=& $kdiv->addCombo('selectList', $list, $list[$teamId]);
      $acts[1] = array(KAF_UPLOAD, 'teams', TEAM_ROOM);
      $kcombo->setActions($acts);   


      // Get data
      $rooms = $dt->getPlayersRoom($teamId);
      if (isset($rooms['errMsg']))
	{
	  $kdiv->addWng($rooms['errMsg']);
	  unset($rooms['errMsg']);
	}
      
      $form =& $kdiv->addForm('fRooms'); 
      $form->addHide('teamId', $teamId);
      $krow =& $form->addRows('rowsRoom', $rooms);
      $krow->displaySelect(true);
      $krow->setSort(0);
      $krow->displayNumber(false);
      $sizes[8] = '0+';
      $krow->setSize($sizes);

      $actions[0] = array(KAF_NEWWIN, 'purchase', PURCHASE_EDIT_ROOM, 'cmd_id',
      			  350, 150);
      $actions[2] = array(KAF_UPLOAD, 'regi', KID_SELECT);
      $actions[3] = array(KAF_UPLOAD, 'items', ITEM_HOTEL, 'item_name');
      $krow->setActions($actions);

      //Display the page
      $this->_utPage->display();      
      exit;
    }
  // }}}


  // {{{ _displayFormReservation()
  /**
   * Display the page with the reservation of the selected team
   *
   * @access private
   * @param array team  info of the team
   * @return void
   */
  function _displayFormReservation($sort)
    {
      $dt = $this->_dt;

      // New page
      $teamId = kform::getData();
      $fields = array(1=>'cmd_date', 'cmd_name', 'regi_longName');
      $items  = array(1=>'itDetailD', 'itDetailI', 'itDetailM');
      $field = $fields[$sort];
      $kdiv =& $this->_displayHead($teamId, $items[$sort]);

      // Get data
      $resas = $dt->getPurchaseList($teamId, $sort);
      if (isset($resas['errMsg']))
	{
	  $kdiv->addWng($resas['errMsg']);
	  unset($resas['errMsg']);
	}
      array_pop($resas);
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
		$rows[] = array('cmd_id'=>0,'','','',$sub['value'], $sub['discount'],
				$sub['payed'],$sub['du'],'regi_id'=>0);
	      if (is_array($resa[$field]))
		{
		  $cell = reset($resa[$field]);
		  $title = $cell['value'];
		}
	      else
		$title = $resa[$field];

	      $rows[] = array(KOD_BREAK, "title", $title,'', $resa['regi_id']);
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
	}
      if (!is_null($id) && $nb >1)
	$rows[] = array('cmd_id'=>0,'','','',$sub['value'], $sub['discount'],
			$sub['payed'],$sub['du'],'regi_id'=>0);
      $rows[] = array(KOD_BREAK, "title", "Totaux",'','');
      $rows[] = array('cmd_id'=>0,'','','',$cumul['value'], $cumul['discount'],
		      $cumul['payed'],$cumul['du'],'regi_id'=>0);

      $itsm['itSolder'] = array(KAF_NEWWIN, 'purchase', PURCHASE_SOLDE_CLUB, 
			       $teamId, 100, 100);
      $itsm['itErase'] = array(KAF_NEWWIN, 'purchase', KID_CONFIRM, 
			       0, 300, 200);
      $itsm['itPrint'] = array(KAF_NEWWIN, 'teams', TEAM_PDF_PURCHASE, 
			       $sort, 400, 400);
      $kdiv->addMenu('menuAction', $itsm, -1);
      $kdiv->addDiv('break', 'blkNewPage');

      $form =& $kdiv->addForm('freservation'); 
      $form->addHide('teamId', $teamId);
      $krow =& $form->addRows('rowsPurchase', $rows);
      $krow->displaySelect(true);
      $krow->setSort(0);
      $krow->displayNumber(false);
      $sizes[$sort] = 0;
      $sizes[8] = '0+';
      $krow->setSize($sizes);

      $actions[0] = array(KAF_NEWWIN, 'purchase', KID_EDIT, 'cmd_id',
      			  400, 400);
      $actions[3] = array(KAF_UPLOAD, 'regi', KID_SELECT, 'regi_id');
      $krow->setActions($actions);

      if ($sort ==3)
	{
	  $acts = array();
	  $acts[] = array('link' => array(KAF_NEWWIN, 'purchase', PURCHASE_HOTEL, 
					  0, 400, 280),
			   'icon' => utimg::getIcon(PURCHASE_HOTEL),
			   'title' => 'addRoom');
	  $acts[] = array('link' => array(KAF_NEWWIN, 'purchase', KID_ADD,
					  0, 400, 400),
			   'icon' => utimg::getIcon(PURCHASE_RESERVE),
			   'title' => 'addCommand');
	  $acts[] = array('link' => array(KAF_NEWWIN, 'purchase', PURCHASE_SOLDE_MEMBER,
					  0, 400, 400),
			   'icon' => utimg::getIcon(PURCHASE_SOLDE_MEMBER),
			   'title' => 'payCommand');
	  $krow->setBreakActions($acts);	  
	}

      //Display the page
      $this->_utPage->display();      
      exit;
    }
  // }}}

  // {{{ _displayAdmiDetail()
  /**
   * Display the page with the detail of the selected team
   *
   * @access private
   * @param array team  info of the team
   * @return void
   */
  function _displayAdmiDetail($teamId, $err='')
    {
      $dt = $this->_dt;
      $kdiv =& $this->_displayHead($teamId, 'itMembers');

      // Display menu for register player
      $form =& $kdiv->addForm('fregi'); 
      $itsm['itSolder'] = array(KAF_NEWWIN, 'purchase', PURCHASE_SOLDE_CLUB, 
			       $teamId, 100, 100);
      $itsm['itBadge'] = array(KAF_NEWWIN, 'teams', TEAM_PDF_BADGES, 
			       0, 400, 400);
      $form->addMenu('menuAction', $itsm, -1);
      $form->addDiv('break', 'blkNewPage');
      if (isset($err['errMsg']))
	$form->addWng($err['errMsg']);

      $form->addHide('teamId', $teamId);
      // Display list of members
      $sort = kform::getSort("rowsRegis", 3);
      $players = $dt->getMembers($teamId, $sort);
      if (isset($players['errMsg']))
 	$form->addWng($players['errMsg']);
      else
 	{
	  $krows =& $form->addRows('rowsAdmRegis', $players);

	  $sizes[8] = '0+';
	  $krows->setSize($sizes);
	  
          $actions[0] = array(KAF_NEWWIN, 'regi', KID_EDIT, 0,
                              400, 400);
	  $actions[2] = array(KAF_UPLOAD, 'regi', KID_SELECT);
	  $krows->setActions($actions);
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
	$kdiv->addWng($items['errMsg']);
      
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
      $itsm['itSelectItems'] = array(KAF_NEWWIN, 'items', KID_SELECT, 
				     0, 650, 450);
      $form->addMenu('menuLeft', $itsm, -1);

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
	      $rows[] = array(KOD_BREAK, "title", $utd->getDateWithDay());
	      $lines=array();
	      foreach($regis as $regi)
		{
		  $line = array($regi[0], $regi[1], $regi[2], $regi[3]); 
		  $regi  = array_pad($line, 2*$nbItems+4, '');
		  $lines[$regi[0]] = $regi;
		}	      
	    }  
	  $line = $lines[$discount['regi_id']];
	  $indx = array_keys($keys, $discount['item_id']);
	  $line[$indx[0]+4] += $discount['cmd_discount'];
	  $line[$indx[0]+4+$nbItems] = $discount['cmd_id'];
	  $lines[$discount['regi_id']] = $line;
	}
      if ($date != '')
	{
	  $rows = array_merge($rows, $lines);
	  $krow =& $form->addRows('reservations', $rows);
	  $krow->displaySelect(false);
	  $krow->setTitles($title);
	  $krow->setSort(0);
	  for ($i=0; $i<$nbItems; $i++)
	    {
	      $acts[$i+4] = array(KAF_NEWWIN, 'purchase', KID_EDIT, $i+$nbItems+4,
				  400, 400);
	      $sizes[$i+$nbItems+4] = 0;
	    }
	  $krow->setActions($acts);
	  $krow->setSize($sizes);
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
	$kdiv->addWng($items['errMsg']);
      
      // Construire la table d'affichage
      $form =& $kdiv->addForm('fregi'); 
      $title[1] = "Sexe";
      $title[2] = "Identit�";
      $title[3] = "Fonction";
      $i = 4;
      //$klgd =& $kdiv->addDiv('blkLegende');
      foreach($items as $item=>$data)
	{
	  $title[$i++] = $data[1];
	  $form->addInfo($data[1].":", $data[2]);
	  $elts[] = $data[1].":";
	}

      // Create a new form
      $form->addBlock("blkLegende", $elts);
      $itsm['itSelectItems'] = array(KAF_NEWWIN, 'items', KID_SELECT, 
				     0, 650, 450);
      $itsm['itNewResa'] = array(KAF_NEWWIN, 'purchase', PURCHASE_ITEMS_GRID, 
				     $teamId, 650, 450);
      $form->addMenu('menuLeft', $itsm, -1);

      $date = kform::getInput('resaDate', date(DATE_DATE));
      $kedit =& $form->addEdit('resaDate', $date, 10);
      $kedit->setMaxLength(10);

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
	      $rows[] = array(KOD_BREAK, "title", $utd->getDateWithDay());
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
      
      $form =& $kdiv->addForm('fregi'); 
      $itsm['itDelete'] = array(KAF_NEWWIN, 'purchase', KID_CONFIRM, 
				0, 250, 100);     
      $form->addMenu('menuLeft', $itsm, -1);


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
  function & _displayHead($teamId, $select)
    {
      $dt = $this->_dt;
      
      // Create a new page
      $this->_utPage = new utPage_A('teams', true, 'itTeams');
      $kdiv =& $this->_utPage->getContentDiv();

      // List of teams
      $teams = $dt->getTeams(3);
      foreach($teams as $team)
	$list[$team['team_id']] = "{$team['asso_pseudo']} :: {$team['team_name']}";
      $kcombo=& $kdiv->addCombo('selectList', $list, $list[$teamId]);
      $acts[1] = array(KAF_UPLOAD, 'teams', KID_SELECT);
      $kcombo->setActions($acts);   

      // Get team's informations
      $team = $dt->getTeam($teamId);
      if (isset($team['errMsg']))
	$kdiv->addWng($team['errMsg']);
      $asso = $dt->getAsso($team['asso_id']);
      if (isset($asso['errMsg']))
	$kdiv->addWng($asso['errMsg']);

      // Display general informations
      $div =& $kdiv->addDiv('blkTeam', 'blkData');
      $div->addMsg($team['team_name'], '', 'kTitre');
      $div->addInfo("assoTeam",    $asso['asso_name']);
      $div->addInfo("teamStamp",     $team['team_stamp']);
      $div->addInfo("teamCaptain",   $team['team_captain']);
      $kinfo =& $div->addInfo("assoCount",  $team['cunt_name']);
      $act[1] = array(KAF_UPLOAD, 'account', KID_SELECT, $team['cunt_id']);
      $kinfo->setActions($act);
      $items = array();
      $items['btnModify'] = array(KAF_NEWWIN, 'teams', 
				  KID_EDIT,  $teamId, 350, 200);
      $div->addMenu('menuModif', $items, -1, 'classMenuBtn');

      // Display financial data
      $div =& $kdiv->addDiv('bilan', 'blkData');
      $div->addMsg('tBilan', '', 'kTitre');
      $team = $teams[$teamId];  
      $div->addInfo('costTeam',      $team['value']);
      $div->addInfo('discountTeam',  $team['discount']);
      $div->addInfo('payedTeam',     $team['payed']);
      $div->addInfo('duTeam',        $team['solde']);

      $kdiv->addDiv('breakH', 'blkNewPage');
      $div =& $kdiv->addDiv('choix', 'onglet3');
      $items = array();
      $items['itMembers'] = array(KAF_UPLOAD, 'teams', 
				  KID_SELECT, $teamId);
      $items['itDetailM'] = array(KAF_UPLOAD, 'teams', 
				TEAM_DETAILS_PLAYERS, $teamId);
      $items['itDetailD'] = array(KAF_UPLOAD, 'teams', 
				TEAM_DETAILS_DATE, $teamId);
      $items['itDetailI'] = array(KAF_UPLOAD, 'teams', 
				TEAM_DETAILS_ITEMS, $teamId);

      $items['itHotel'] = array(KAF_UPLOAD, 'teams', 
				TEAM_ROOM, $teamId);
      /*
      $items['itReservation'] = array(KAF_UPLOAD, 'teams', 
				      TEAM_RESERVATION, $teamId);
      
      $items['itDiscount'] = array(KAF_UPLOAD, 'teams', 
				   TEAM_DISCOUNT, $teamId);
      $items['itCredits'] = array(KAF_UPLOAD, 'teams', 
				  TEAM_CREDITS, $teamId);
      */
      $div->addMenu("menuType", $items, $select);
      $content =& $kdiv->addDiv('register', 'cont3');
      return $content;
    }
  // }}}

  // {{{ _displayFormList()
  /**
   * Display a page with the list of the teams
   *
   * @access private
   * @return void
   */
  function _displayFormList($err='')
    {
      $dt = $this->_dt;
      
      // Creating a new page
      $page = new utPage_A('teams', true, 'itTeams');
      $content =& $page->getContentDiv();
      $form =& $content->addForm('fasso'); 
      if ($err!= '')
	$form->addWng($err['errMsg']); 
      
      // List of teams with their account
      $sort = kform::getSort("rowsAdmTeams", 3);
      $rows = $dt->getTeams($sort);
      if (isset($rows['errMsg']))
	$form->addWng($rows['errMsg']);
      else
	{
	  $krow =& $form->addRows("rowsAdmTeams", $rows);
	  $sizes[8] = '0+';
	  $krow->setSize($sizes);

	  $img[1]='icon';
	  $krow->setLogo($img);

	  $actions[0] = array(KAF_NEWWIN, 'teams', 
			      KID_EDIT,  0, 350, 200);
	  $actions[3] = array(KAF_UPLOAD, 'teams', KID_SELECT);
	  //$actions[4] = array(KAF_UPLOAD, 'account', KID_SELECT, 'cunt_id');
	  $krow->setActions($actions);
	}

      $page->display();
      exit; 
    }
  // }}}
  
  // {{{ _displayFormConfirm()
  /**
   * Display the page for confirmation the destruction of purchase
   *
   * @access private
   * @param array $member  info of the member
   * @return void
   */
  function _displayFormConfirm($err='')
    {
      $dt = $this->_dt;
      
      $utPage = new utPage('asso');
      $content =& $utPage->getPage();
      $form =& $content->addForm('tDelAsso', 'asso', KID_DELETE);


      // Initialize the field
      $ids = kform::getInput("rowsAssocs");
      if ($ids != '' && $err == '')
	{
	  foreach($ids as $id)
	    $form->addHide("rowsAssocs[]", $id);
	  $form->addMsg('msgConfirmDel');
	  $form->addBtn('btnDelete', KAF_SUBMIT);
	}
      else
	{
	  if ($ids == '')
	    $form->addWng('msgNeedAssocs');
	  else
	    $form->addWng($err);
	}
      $form->addBtn('btnCancel');
      $elts = array('btnDelete', 'btnCancel');
      $form->addBlock('blkBtn', $elts);

      //Display the page
      $utPage->display();
      exit;
    }
  // }}}
   
  // {{{ _updateAccount()
  /**
   * Modify the account of a registered member in the database
   *
   * @access private
   * @return void
   */
  function _updateAccount()
    {
      $dt = $this->_dt;
      
      // Get the informations
      $teamId = kform::getInput('teamId');
      $accountId = kform::getInput('accountId');

      // Add the member
      $res = $dt->updateTeamAccount($teamId, $accountId);
      if (is_array($res))
	$this->_displayFormAccount($teamId, $res);

      $page = new kPage('none');
      $page->close();
      exit;
    }
  // }}}

  // {{{ _displayFormAccount()
  /**
   * Display the page for modifying account of a memeber
   *
   * @access private
   * @param array $regiId  registration id of the member
   * @return void
   */
  function _displayFormAccount($teamId, $err='')
    {
      $dt = $this->_dt;
      $utPage = new utPage('asso');
      $content =& $utPage->getPage();
      $form =& $content->addForm('tAccount', 'teams', KID_UPDATE);
      
      if (isset($err['errMsg']))
	$form->addWng($err['errMsg']);
      
      // Initialize the field
      $infos = $dt->getTeamAccount($teamId);
      if (isset($infos['errMsg']))
	$form->addWng($infos['errMsg']);

      $accounts = $dt->getAccounts();
      if (isset($infos['errMsg']))
	$form->addWng($infos['errMsg']);
      
      // Initialize the field
      $form->addHide('teamId', $infos['team_id']);
      $form->addInfo('teamName', $infos['team_name']);
      $form->addInfo('accountName', $infos['cunt_name']);
      $form->addCombo('accountId', $accounts, $infos['cunt_name']);
      $elts = array('teamName', 'accountName', 'accountId');
      $form->addBlock('blkAccount', $elts);
      $form->addBtn('btnRegister', KAF_SUBMIT);
      $form->addBtn('btnCancel');
      $elts = array('btnRegister', 'btnCancel');
      $form->addBlock('blkBtn', $elts);

      //Display the page
      $utPage->display();
      
      exit;
    }
  // }}}

  function change($infos){
    //print_r($infos);
    $sorties = array();
    $elt_out = array();
    foreach($infos as $elt){
      $elt_out['asso_id'] = $elt['idx'];
      $elt_out['asso_name'] = $elt['assoName'];
      $elt_out['asso_stamp'] = $elt['assoStamp'];
      $elt_out['asso_pseudo'] = $elt['assoPseudo'];
      $elt_out['asso_type'] = $elt['type'];
      $elt_out['asso_cmt'] = "";
      $elt_out['asso_url'] = "";
      $elt_out['asso_logo'] = "";
      //				  print_r($elt_out);
      //				  echo "<hr>";
      $sorties[] = $elt_out;
    }
    
    return $sorties;
  }
  
}

?>