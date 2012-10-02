<?php
/*****************************************************************************
 !   Module     : members
 !   File       : $Source: /cvsroot/aotb/badnet/src/mber/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.5 $
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

class memberBase_A extends utBase
{

	// {{{ getRegis
	/**
	 * Return the registered members
	 *
	 * @access public
	 * @param  integer  $type  Id of the team
	 * @return array   array of users
	 */
	function getRegis()
	{
		// Select the players of the team
		$eventId = utvars::getEventId();
		$fields = array('regi_id', 'regi_longName');
		$tables = array('registration', 'members');
		$where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_eventId = $eventId".
	" AND regi_memberId = mber_id".
	" AND mber_id >= 0";
		$order = "regi_longName";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoMembers";
	  return $infos;
		}
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		$rows[$entry[0]]=$entry[1];
		return $rows;
	}
	// }}}

	// {{{ getDeparr
	/**
	 * Return the list of the members in arrival or departure order
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort users
	 * @return array   array of users
	 */
	function getDeparrs($data, $cmt)
	{
		$fields = array('regi_id', 'mber_sexe', 'regi_longName', 'team_name',
		      'regi_type', $data, $cmt);
		$tables = array('members', 'registration', 'teams');
		$where = "regi_eventId = ".utvars::getEventId().
	" AND regi_memberId = mber_id".
	" AND regi_teamId = team_id".
	" AND $data >= 0".
	" AND $data != '00-00-0000 00:00:00'".
	" AND mber_id >= 0";
		$order = "$data, team_name, regi_longName";

		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = "noDepArr";
	  return $err;
		}

		$date = new utdate();
		$ut = new utils();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $entry[1] = $ut->getSmaLabel($entry[1]);
	  $entry[4] = $ut->getLabel($entry[4]);
	  $date->setIsoDateTime($entry[5]);
	  $entry[5] = $date->getTime();
	  $entry[7] = $date->getDateWithDay();
	  //$entry[7] = $date->getDate();
	  $rows[] = $entry;
		}
		return $rows;
	}
	// }}}

	// {{{ getRegiMembers
	/**
	 * Return the members registered
	 *
	 * @access public
	 * @param  integer  $type  Id of the team
	 * @return array   array of users
	 */
	function getRegiMembers($typeMin, $typeMax, $sort)
	{
		// Select the players of the team
		$eventId = utvars::getEventId();
		$fields = array('regi_id', 'mber_sexe', 'regi_longName',
		      'regi_type', 'cunt_name',
		      "sum(cmd_value) as value",
		      "sum(cmd_discount) as discount",
		      "sum(cmd_payed) as payed",
		      "sum(cmd_value - cmd_discount - cmd_payed) as solde",
		      'cunt_id');
		$tables = array('members',  'registration LEFT JOIN commands ON
          regi_id=cmd_regiId LEFT JOIN accounts ON
          regi_accountId = cunt_id');
		$where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_memberId = mber_id ".
	" AND regi_eventId = $eventId".
	" AND mber_id >= 0".
	" AND regi_type >= $typeMin".
	" AND regi_type <= $typeMax".
	" GROUP BY regi_id";
		$order = abs($sort);
		if ($sort < 0)
		$order .= " DESC";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoMembers";
	  return $infos;
		}
		$ut = new utils();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $entry[1] = $ut->getSmaLabel($entry[1]);
	  $entry[3] = $ut->getLabel($entry[3]);
	  $rows[]=$entry;
		}
		return $rows;
	}
	// }}}


	// {{{ getDeparr
	/**
	 * Return the list of the members
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort users
	 * @return array   array of users
	 */
	function getDeparr($regiId)
	{
		$fields = array('regi_arrival', 'regi_departure', 'regi_arrcmt',
		      'regi_depcmt', 'regi_longName');
		$tables[] = 'registration';
		$where = "regi_id = $regiId";
		$res = $this->_select($tables, $fields, $where);

		$utd = new utDate();
		$regi = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$utd->setIsoDate($regi['regi_arrival']);
		$regi['regi_arrival'] = $utd->getDateTime();
		$utd->setIsoDate($regi['regi_departure']);
		$regi['regi_departure'] = $utd->getDateTime();
		return $regi;
	}
	// }}}


	// {{{ getMembers
	/**
	 * Return the list of the members
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort users
	 * @return array   array of users
	 */
	function getMembers($sort=1)
	{
		$fields = array('mber_id', 'mber_sexe', 'mber_firstname',
		      'mber_secondname', 'mber_born', 'mber_ibfnumber',
		      'mber_licence', 'mber_cmt');
		$tables[] = 'members';
		$where = "mber_del != ".WBS_DATA_DELETE;
		if ($sort < 0)
		{
	  $sort *= -1;
	  $order = $sort." desc";
		}
		else
		$order = $sort;

		$res = $this->_select($tables, $fields, $where, $order);

		$date = new utdate();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  //$entry[1] = $this->_utils->getSexeId($entry[1]);
	  $date->setIsoDate($entry[4]);
	  $entry[4] = $date->getDate();
	  $rows[] = $entry;
		}
		return $rows;
	}
	// }}}


	// {{{ getRegi
	/**
	 * Return the column of a member
	 *
	 * @access public
	 * @param  string  $memberId  id of the member
	 * @return array   information of the member if any
	 */
	function getRegi($regiId)
	{
		$fields = array('regi_id', 'regi_longName', 'regi_teamId', 'regi_type',
		      'regi_accountId', 'cunt_name', 'team_name',
		      "sum(cmd_value - cmd_discount - cmd_payed) as soldeAccount");
		$tables = array('teams', 'registration LEFT JOIN accounts ON
            cunt_id = regi_accountId LEFT JOIN commands ON
            cmd_accountId = cunt_id');
		$where = "regi_id = $regiId".
	" AND regi_teamId = team_id".
	" GROUP BY regi_id";
		$res = $this->_select($tables, $fields, $where);
		$ut = new utils();
		$player =  $res->fetchRow(DB_FETCHMODE_ASSOC);
		$player['regi_type'] = $ut->getLabel($player['regi_type']);

		$fields = array("sum(cmd_value - cmd_discount - cmd_payed) as solde");
		$tables = array('registration LEFT JOIN commands ON
            cmd_regiId = regi_id');
		$where = "regi_id = $regiId".
	" GROUP BY regi_id";
		$res = $this->_select($tables, $fields, $where);
		$solde = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$player['solde'] = $solde['solde'];

		return $player;
	}
	// }}}


	// {{{ getMember
	/**
	 * Return the column of a member
	 *
	 * @access public
	 * @param  string  $memberId  id of the member
	 * @return array   information of the member if any
	 */
	function getMember($memberId)
	{
		$fields = array('mber_id', 'mber_sexe', 'mber_firstname',
		      'mber_secondname', 'mber_born', 'mber_ibfnumber',
		      'mber_licence', 'mber_cmt');
		$tables[] = 'members';
		$where = "mber_id = '$memberId'";
		$res = $this->_select($tables, $fields, $where);

		$player =  $res->fetchRow(DB_FETCHMODE_ASSOC);
		$date = new utdate();
		$date->setIsoDate($player['mber_born']);
		$player['mber_born'] = $date->getDate();
		return $player;
	}
	// }}}

	// {{{ updateMember
	/**
	 * Add or update a member into the database
	 *
	 * @access public
	 * @param  string  $info   column of the event
	 * @return mixed
	 */
	function updateMember($infos)
	{
		$date = new utdate();
		if (isset($infos['mber_born']))
		{
			$date->setFrDate($infos['mber_born']);
			$infos['mber_born'] = $date->getIsoDateTime();
		}

		if ($infos['mber_id'] != -1)
		{
			$memberId = $infos['mber_id'];
			$where = " mber_id=".$infos['mber_id'];
			$res = $this->_update('members', $infos, $where);
		}
		else
		{
			unset($infos['mber_id']);
			$res = $this->_insert('members', $infos);
			$memberId = $res;
		}
		return $memberId;
	}
	// }}}

	// {{{ updateDeparr
	/**
	 * Update a arrival and departure time of a registered member
	 *
	 * @access public
	 * @param  string  $info   column of the event
	 * @return mixed
	 */
	function updateDeparr($regi, $propa)
	{
		$utd = new utDate();
		$utd->setFrDateTime($regi['regi_arrival']);
		$regi['regi_arrival'] = $utd->getIsoDateTime();
		$utd->setFrDateTime($regi['regi_departure']);
		$regi['regi_departure'] = $utd->getIsoDateTime();

		if ($propa)
		{
	  $fields = array('regi_teamId');
	  $tables = array('registration');
	  $where = 'regi_id='.$regi['regi_id'];
	  $res = $this->_select($tables, $fields, $where);
	  $tmp = $res->fetchRow(DB_FETCHMODE_ORDERED);
	  $where = 'regi_eventId='.utvars::getEventId().
	    " AND regi_teamId=".$tmp[0];
	  unset($regi['regi_id']);
		}
		else
		$where = 'regi_id='.$regi['regi_id'];
		$res = $this->_update('registration', $regi, $where);
		return true;
	}
	// }}}


	// {{{ delMembers
	/**
	 * Logical delete some members
	 *
	 * @access public
	 * @param  arrays  $members   id's of the members to delete
	 * @return mixed
	 */
	function delMembers($members)
	{
		$fields[] = 'mber_secondname';
		$tables[] = 'members';
		foreach ( $members as $member => $id)
		{
	  $where = "mber_id=$id";
	  $res = $this->_select($tables, $fields, $where);
	  $mber = $res->fetchRow(DB_FETCHMODE_ORDERED);
	  $name = "!".$mber[0]."!";

	  //$infos['mber_secondname'] = $db->quote($name);
	  $infos['mber_secondname'] = $name;
	  $infos['mber_del'] = WBS_DATA_DELETE;
	  $res = $this->_update('members', $infos, $where);
		}
		return true;

	}
	// }}}
}


?>