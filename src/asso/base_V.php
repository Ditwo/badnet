<?php
/*****************************************************************************
 !   Module     : Associations
 !   File       : $Source: /cvsroot/aotb/badnet/src/asso/base_V.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.4 $
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
require_once "utils/utbase.php";

/**
 * Acces to the dababase for events
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class assoBase_V extends utbase
{

	// {{{ properties
	// }}}

	// {{{ getPurchaseList
	/**
	 * Return the list of the reservation
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort associations
	 * @return array   array of users
	 */
	function getPurchaseList($eventId, $assoId, $sort)
	{
		$fields = array('regi_id', 'cmd_date', 'cmd_name', 'regi_longName',
		      'cmd_value', 'cmd_discount', 'cmd_payed', 
		      'cmd_value-cmd_discount-cmd_payed as cmd_du', 
		       'mber_id');
		$tables = array('commands',  'items', 'registration', 'a2t', 'members');
		$where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_teamId = a2t_teamId".
	" AND a2t_assoId = $assoId".
	" AND regi_id = cmd_regiId".
	" AND regi_eventId = $eventId".
	" AND cmd_itemId = item_id".
	" AND regi_memberId = mber_id".
	" AND mber_id >= 0";
		if ($sort == 3)
		$order = "regi_id, cmd_date, cmd_name";
		else if ($sort == 2)
		$order = "cmd_name, cmd_date,  regi_longName";
		else
		$order = "cmd_date, cmd_name,  regi_longName";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = "msgNoReservations";
	  return $err;
		}

		$utd = new utdate();
		$res = $this->_select($tables, $fields, $where, $order);
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $utd->setIsoDateTime($entry['cmd_date']);
	  $entry['cmd_date'] = $utd->getDate();
	  $rows[] = $entry;
		}
		return $rows;
	}
	// }}}



	// {{{ getPlayers
	/**
	 * Return the players of an association
	 *
	 * @access public
	 * @return array   array of players
	 */
	function getPlayers($eventId, $assoId, $sort)
	{
		$ut = new utils();
		// Select the players of the team
		$fields = array('regi_id',  'mber_sexe', 'regi_longName',
		      'mber_licence', 'mber_ibfnumber'  
		      );
		      $tables = array('a2t', 'members', 'registration');
		      $where  = "regi_del != ".WBS_DATA_DELETE.
	" AND a2t_assoId=$assoId".
	" AND a2t_teamId = regi_teamId".
	" AND regi_memberId = mber_id ".
	" AND regi_eventId = $eventId".
	" AND regi_type = ".WBS_PLAYER.
	" AND mber_id >= 0";

		      $order = abs($sort);
		      $order = min($order, 4);
		      if ($sort < 0) $order .= " DESC";
		      $res = $this->_select($tables, $fields, $where, $order);
		      if (!$res->numRows())
		      {
		      	$infos['errMsg'] = "msgNoPlayers";
		      	return $infos;
		      }

		      // Boucle sur les joueurs
		      $regiId = -1;
		      $res = $this->_select($tables, $fields, $where, $order);
		      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		      {
		      	$entry['mber_sexe'] = $ut->getSmaLabel($entry['mber_sexe']);
		      	$regiId = $entry['regi_id'];
		      	 
		      	$draws = $this->_getDraws($regiId);
		      	if (is_array($draws))
		      	{
		      		$cell = array();
		      		foreach($draws as $draw)
		      		{
		      			$line['value'] = $draw;
		      			$cell[] = $line;
		      		}
		      		$entry[] = $cell;
		      	}
		      	$rows[] = $entry;
		      }

		      return $rows;
	}
	// }}}

	// {{{ _getDraws
	/**
	 * Return the draws of a player with martner name and club
	 *
	 * @access public
	 * @param  string  $assoId  id of the member
	 * @return array   information of the member if any
	 */
	function _getDraws($regiId)
	{
		// get the draws
		$fields = array('i2p_regiId',  'pair_drawId', 'pair_disci', 'draw_name',
		      'draw_stamp', 'pair_id');
		$tables = array('i2p', 'pairs LEFT JOIN draws ON pair_drawId = draw_id');
		$where = "i2p_regiId = $regiId".
	" AND i2p_pairId = pair_id".
	" AND pair_drawId = draw_id";
		$order = 'pair_disci';
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows()) return false;

		$trans = array('draw_stamp', 'draw_name');
		$res = $this->_select($tables, $fields, $where, $order);
		while ($draw = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $draw = $this->_getTranslate('draws', $trans,
	  $draw['pair_drawId'], $draw);
	  // Search the partnair
	  $cols = array('regi_longName');
	  $tabs = array('registration', 'i2p');
	  $ou = "i2p_pairId = {$draw['pair_id']}".
	    " AND i2p_regiId = regi_id".
	    " AND regi_id != {$draw['i2p_regiId']}";
	  $res2 = $this->_select($tabs, $cols, $ou);
	  $name = $draw['draw_stamp'];
	  if ($res2->numRows())
	  {
	  	$res2 = $this->_select($tabs, $cols, $ou);
	  	$regi = $res2->fetchRow(DB_FETCHMODE_ASSOC);
	  	$name .= " {$regi['regi_longName']}";
	  }
	  $draws[$draw['pair_disci']] = $name;
		}
		return $draws;
	}
	// }}}

	// {{{ getAsso
	/**
	 * Return the column of an association
	 *
	 * @access public
	 * @param  string  $assoId  id of the member
	 * @return array   information of the member if any
	 */
	function getAsso($assoId)
	{
		$where = "asso_id = $assoId";
		$res = $this->_select('assocs', '*', $where);
		return $res->fetchRow(DB_FETCHMODE_ASSOC);
	}
	// }}}
}
?>