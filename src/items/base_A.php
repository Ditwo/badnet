<?php
/*****************************************************************************
!   Module     : items
!   File       : $Source: /cvsroot/aotb/badnet/src/items/base_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.11 $
!   Author     : PMM
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
!   Mailto     : philippe at midol-monnet.org
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
require_once "utils/utbase.php";
require_once "teams/teams.inc";


/**
* Acces to the dababase for hotel
*
* @author Philippe Midol-Monnet
* @see to follow
*
*/

class itemsBase_A  extends utbase
{

  // {{{ properties
  // }}}

  // {{{ getCommands
  /**
   * Return the list of the commands of the item
   *
   * @access public
   * @return array   array of users
   */
  function getCommands($itemId, $sort=1)
    {
      $fields = array('cmd_id',  'cunt_name', 'regi_longName', 
		       'team_name', 'cmd_date');
      $tables = array('accounts', 
      		'commands LEFT JOIN registration ON cmd_regiId = regi_id
		LEFT JOIN teams ON team_id = regi_teamId');
      $where = "cmd_itemId = $itemId";
      $where .= " AND cmd_accountId = cunt_id";
      $order = 'cmd_date,'.abs($sort);
      if ($sort < 0)
	$order .= ' DESC';

      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows()) 
	{
           $infos['errMsg'] = 'msgNoCommands';
 	   return $infos;
        } 

      $date='';
      $utd = new utDate();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
	{
	  if ($date != $entry[4])
	    {
	      $utd->setIsoDateTime($entry[4]);
	      $rows[] = array(KOD_BREAK, "title", $utd->getDateWithDay());
	      $date = $entry[4];
	    }
	  unset($entry[4]);
	  $rows[] = $entry;
	}
      return $rows;
    }
  // }}}
    
  // {{{ getItems
  /**
   * Return the list of the items
   *
   * @access public
   * @return array   array of users
   */
  function getItems($sort=1, $type=null)
    {
      $fields = array('item_id', 'item_name', 'item_code', 'item_ref',
		      'item_value', 'item_count', 'item_isFollowed',
		      'item_isCreditable', 'item_pbl', 'item_slt', 'item_rge');
      $tables = array('items');
      $where = "item_eventId = ".utvars::getEventId();
      if (!empty($type))
      {
	     $where .= " AND item_rubrikId = $type";
      }   
      if ( $type == WBS_RUBRIK_HOTEL)
      {
       $order = 'item_name,item_code, item_ref'; 
      }
      else
      {
      $order = abs($sort);
      if ($sort < 0)
		$order .= ' DESC';
      }
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows()) 
	{
           $infos['errMsg'] = 'msgNoItems';
 	   return $infos;
        } 

      $uti = new utimg();
      $ut = new utils();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  if ($entry['item_isFollowed'])
	    $entry['item_isFollowed'] = $ut->getLabel(WBS_YES);
	  else
	    $entry['item_isFollowed'] = $ut->getLabel(WBS_NO);
	  if ($entry['item_isCreditable'])
	    $entry['item_isCreditable'] = $ut->getLabel(WBS_YES);
	  else
	    $entry['item_isCreditable'] = $ut->getLabel(WBS_NO);
	  $entry['icon'] = $uti->getIcon($entry['item_pbl']); 

          $rows[] = $entry;
        }
      return $rows;

    }
  // }}}
  
  // {{{ getItemlById
  /**
   * Return the column of a draw
   *
   * @access public
   * @param  integer $id   id of the hotel
   * @return array   information of the user if any
   */
  function getItemById($id)
    {
      $fields = array('item_id', 'item_name', 'item_code',
		      'item_ref', 'item_count', 'item_isFollowed', 'item_isCreditable',
		      'item_rubrikId', 'item_value', 'item_rge', 'item_slt');
      $tables[] = 'items';
      $where = "item_id = '$id'";
      $res = $this->_select($tables, $fields, $where);
      return $res->fetchRow(DB_FETCHMODE_ASSOC);	
    }
  // }}}

  // {{{ getHotel
  /**
   * Return the column of an hotel
   * @return array   information of the user if any
   */
  function getHotel($id)
    {
      $fields = array('item_id', 'item_name');
      $where = "item_id = $id";
      $res = $this->_select('items', $fields, $where);
      $hotel = $res->fetchRow(DB_FETCHMODE_ASSOC);


      // Item_code contient le type de la chambre : simple, double, triple, autre
      // Item_ref contient le type de la facturation : semaine ou WE
      $fields = array('item_id', 'item_name', 'item_code', 'item_ref', 'item_value');
      $where = "item_name = '{$hotel['item_name']}'";
      $order = 'item_code, item_ref';
      $res = $this->_select('items', $fields, $where, $order);
      while( $price = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $prices[$price['item_code']][$price['item_ref']] = 
	    $price['item_value'];
	}

      $hotel['item_valueSW'] = $prices[WBS_ROOM_SINGLE][WBS_PRICE_WEEK];
      $hotel['item_valueDW'] = $prices[WBS_ROOM_TWIN][WBS_PRICE_WEEK];
      $hotel['item_valueTW'] = $prices[WBS_ROOM_TRIPLEX][WBS_PRICE_WEEK];
      $hotel['item_valueOW'] = $prices[WBS_ROOM_OTHER][WBS_PRICE_WEEK];
      $hotel['item_valueSWE'] = $prices[WBS_ROOM_SINGLE][WBS_PRICE_WE];
      $hotel['item_valueDWE'] = $prices[WBS_ROOM_TWIN][WBS_PRICE_WE];
      $hotel['item_valueTWE'] = $prices[WBS_ROOM_TRIPLEX][WBS_PRICE_WE];
      $hotel['item_valueOWE'] = $prices[WBS_ROOM_OTHER][WBS_PRICE_WE];
      return $hotel;	
    }
  // }}}

  // {{{ updateHotel
  /**
   * Update room of an hotel
   * @return none  
   */
  function updateHotel($hotel)
    {
      
      $prices[WBS_ROOM_SINGLE][WBS_PRICE_WEEK] = $hotel['item_valueSW'];
      $prices[WBS_ROOM_TWIN][WBS_PRICE_WEEK] = $hotel['item_valueDW'];
      $prices[WBS_ROOM_TRIPLEX][WBS_PRICE_WEEK] = $hotel['item_valueTW'];
      $prices[WBS_ROOM_OTHER][WBS_PRICE_WEEK] = $hotel['item_valueOW'];
      $prices[WBS_ROOM_SINGLE][WBS_PRICE_WE] = $hotel['item_valueSWE'];
      $prices[WBS_ROOM_TWIN][WBS_PRICE_WE] = $hotel['item_valueDWE'];
      $prices[WBS_ROOM_TRIPLEX][WBS_PRICE_WE] = $hotel['item_valueTWE'] ;
      $prices[WBS_ROOM_OTHER][WBS_PRICE_WE] = $hotel['item_valueOWE'];
      $eventId = utvars::getEventId();
      foreach($prices as $code=>$room)
	{
	  foreach($room as $ref=>$price)
	    {
	      $cols['item_name'] = $hotel['item_name'];
	      $cols['item_code'] = $code;
	      $cols['item_ref'] = $ref;
	      $cols['item_eventId'] = $eventId;
	      $cols['item_rubrikId'] = WBS_RUBRIK_HOTEL;
	      $cols['item_value'] = $price;
	      $where = "item_name='{$hotel['item_name']}'".
		" AND item_code = $code".
		" AND item_ref = $ref";
		" AND item_eventId = $eventId";
		$res = $this->_select('items', 'item_id', $where);
		if ($res->numRows())
		  $this->_update('items', $cols, $where);
		else
		  $this->_insert('items', $cols);
		    
	    }
	}
    }
  // }}}

  // {{{ updateItem
  /**
   * Update the draw with the informations
   *
   * @access public
   * @param  string  $info   column of the event
   * @return mixed
   */
  function updateItem($infos)
    {
      // Update existing item
      if ($infos['item_id'] != -1)
	{
	  // Retrieve last values of the item
	  $tables = array('items');
	  $fields = array('item_name', 'item_value');
	  $where = "item_id = '".$infos['item_id']."'";

	  $res = $this->_select($tables, $fields, $where);
	  $item = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  // update all non modified purchase of this item 
	  $fields = array();
	  $fields['cmd_name'] = $infos['item_name'];
	  $fields['cmd_value'] = $infos['item_value'];
	  $where = "cmd_itemId = ".$infos['item_id'].
	    " AND cmd_name = '".$item['item_name']."'".
	    " AND cmd_value = ".$item['item_value'];
	  $res = $this->_update('commands', $fields, $where);

	  // finally, update the item
	  $where = "item_id = '".$infos['item_id']."'";
	  $res = $this->_update('items', $infos, $where);
	}
      // Create the item
      else
	{
	  unset($infos['item_id']);
	  $res = $this->_insert('items', $infos);
	}
      return $res; 
    }
  // }}}
  

  // {{{ delItems
  /**
   * Logical delete the items
   *
   * @access public
   * @param  table  $infos   Infos of the draw to delete
   * @return mixed
   */
  function delItems($itemsId)
    {
      $listId = implode(',', $itemsId);

      // Verification qu'aucun achat ne reference cet item
      $fields = array('cmd_id');
      $tables[] = 'commands';
      $where = "cmd_itemId in (".$listId.')';
      $res = $this->_select($tables, $fields, $where);
      if ($res->numRows())
	{
	  $err['errMsg'] = 'msgExistCommand';
	  return $err;
	}

      // Suppression des items
      $where = "item_id in (".$listId.')';
      $res = $this->_delete('items', $where);
      return '';  
    }
  // }}}

  // {{{ publItems
  /**
   * Change the status of selected items
   *
   * @access public
   * @param  table  $infos   Infos of the draw to delete
   * @return mixed
   */
  function publItems($itemsId, $status)
    {
      $listId = implode(',', $itemsId);

      // Modification items
      $fields['item_pbl'] = $status;
      $where = "item_id in (".$listId.')';
      $res = $this->_update('items', $fields, $where);
      return '';  
    }
  // }}}


  // {{{ selectItems
  /**
   * Selcts the  items
   *
   * @access public
   * @param  table  $infos   Infos of the draw to delete
   * @return mixed
   */
  function selectItems($itemsId)
    {
      $fields['item_slt'] = false;
      $res = $this->_update('items', $fields, false);
      if ($itemsId != '')
	{
	  $fields['item_slt'] = true;
	  $listId = implode(',', $itemsId);
	  $where = "item_id in (".$listId.')';
	  $res = $this->_update('items', $fields, $where);
	}
      return '';  
    }
  // }}}

  // {{{ getRooms
  /**
   * Return the list of rooms of an hotel
   *
   * @access public
   * @return array   
   */
  function getRooms($hotel)
    {
      $fields = array('regi_id', 'cmd_cmt', 'item_code', 'regi_longName', 
		      'team_name', 'cmd_date', 'team_id');
      $tables = array('registration', 'teams', 'items', 'commands');
      $where = "item_rubrikId=".WBS_RUBRIK_HOTEL.
	" AND item_name='$hotel'".
	" AND team_id = regi_teamId".
	" AND regi_id = cmd_regiId".
	" AND item_id = cmd_itemId".
	" AND regi_eventId=".utvars::getEventId();
      $order = "cmd_date, cmd_cmt, regi_longName";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $err['errMsg'] = 'msgNoItems';
	  return $err;
        } 
      $date = null;
      $num = null;
      $ut = new utils();
      $utd = new utdate();
      while ($room = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  if ($date!=$room['cmd_date'] || $num != $room['cmd_cmt'])
	    {
	      if(!is_null($date))
		{
		  $row['regi_longName'] = $name;
		  $row['team_name'] = $team;
		  $rooms[] = $row;
		}
	      $date = $room['cmd_date'];
	      $num = $room['cmd_cmt'];
	      $room['item_code'] = $ut->getLabel($room['item_code']);
	      $utd->setIsoDateTime($room['cmd_date']);
	      $room['cmd_date'] = $utd->getDateWithDay();
	      $row = $room;
	      $name = array();
	      $team = array();
	    }
	  $name[] = array('value'=>$room['regi_longName'],
			  'action'=>array(KAF_UPLOAD, 'regi', KID_SELECT, $room['regi_id']));
	  $team[] = array('value'=>$room['team_name'],
			  'action'=>array(KAF_UPLOAD, 'teams', TEAM_ROOM, $room['team_id']));
	  
	}
      $row['regi_longName'] = $name;
      $row['team_name'] = $team;
      $rooms[] = $row;
      return $rooms;
    }
  // }}}


  // {{{ getHotels
  /**
   * Return the list of hotels
   *
   * @access public
   * @return array   
   */
  function getHotels()
    {
    	      $eventId = utvars::getEventId();
    	
      $where = "item_rubrikId=".WBS_RUBRIK_HOTEL
      . " AND item_eventId=$eventId";
      $order = "item_name";
      
      $res = $this->_select('items', 'DISTINCT item_name', $where, $order);
      if (!$res->numRows())
	{
	  $err['errMsg'] = 'msgNoItems';
	  return $err;
        } 
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $hotels[$entry['item_name']] = $entry['item_name'];
	}
      return $hotels;
    }
  // }}}

  // {{{ getSelectItems
  /**
   * Return the list of the items' code
   *
   * @access public
   * @param  none
   * @return array   array of codes
   */
  function getSelectItems()
    {

      $fields = array('item_id, item_code', 'item_name', 'item_count',
      	'item_value');
      $tables = array('items');
      $where = "item_slt = 1";
      $res = $this->_select($tables, $fields, $where);
      if (!$res->numRows()) 
	{
	  $err['errMsg'] = "msgNoSelectItems";
	  return $err;
        } 

      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  $rows[$entry['item_id']] = $entry;
      return $rows;
    }
  // }}}
  
  // {{{ getPurchaseStats
  /**
   * Return the purchase stats
   *
   * @access public
   * @param  none
   * @return array   array of users
   */
  function getPurchaseStats()
    {
      $eventId = utvars::getEventId();
      $fields = array('item_id', 'item_name', 'cmd_date', 
		      'count(cmd_id) as nbCmd');
      $tables = array('commands',  'items');
      $where  = "item_eventId=$eventId".
	" AND cmd_itemId = item_id".
	" AND item_slt = 1".
	" GROUP BY cmd_date, cmd_itemId";
      $order = "cmd_date";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows()) 
	{
	  $err['errMsg'] = "msgNoReservations";
	  return $err;
        } 

      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	$rows[] = $entry;
      return $rows;
    }
  // }}}


}

?>