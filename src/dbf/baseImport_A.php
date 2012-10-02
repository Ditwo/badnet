<?php
/*****************************************************************************
 !   Module     : Export Dbf
 !   File       : $Source: /cvsroot/aotb/badnet/src/dbf/baseImport_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.8 $
 !   Author     : G.CANTEGRIL
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2005/11/24 17:36:52 $
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
 * Importation des inscriptions depuis site T3F
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 *
 */

class baseImport extends utbase
{

	// {{{ properties
	// }}}

	// {{{ searchTeam
	/**
	 * Return the team id of the searched team
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function searchTeam($teamDef)
	{
		$externId = $teamDef['externId'];
		$stamp = $teamDef['stamp'];
		$pseudo = $teamDef['pseudo'];
		$noc = $teamDef['noc'];

		// chercher <club_id> dans team_externId
		$fields = array('team_id');
		$tables = array('teams');
		$where = "team_externId = '$externId'";
		$res = $this->_select($tables, $fields, $where);
		if (empty($res))
		{
			$err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
			return $err;
		}

		// Si trouve retour team_id
		if ($res->numRows())
		{
			$res = $this->_select($tables, $fields, $where);
			$team = $res->fetchRow(DB_FETCHMODE_ASSOC);
			return $team['team_id'];
		}

		// Chercher <club_code><club_city> dans asso
		//            <asso_stamp>, <asso_pseudo>
		$rows = array();
		$fields = array('asso_id');
		$tables = array('assocs');
		$where = "asso_stamp = '".addslashes($stamp)."'".
	" AND asso_pseudo = '".addslashes($pseudo)."'";
		$res = $this->_select($tables, $fields, $where);
		if (empty($res))
		{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inacessible';
	  return $err;
		}

		// Non trouve -> creation de l'association
		if (!$res->numRows())
		{
	  $cols = array();
	  $cols['asso_name'] = $pseudo;
	  $cols['asso_pseudo'] = $pseudo;
	  $cols['asso_stamp'] = $stamp;
	  $cols['asso_noc'] = $noc;
	  $cols['asso_type'] = WBS_CLUB;
	  $res = $this->_insert('assocs', $cols);
	  if (empty($res))
	  {
	  	$err['errMsg'] = __FILE__.' ('.__LINE__.'):base ineccessible';
	  	return $err;
	  }
	  $assoId = $res;
		}
		else
		{
			$res = $this->_select($tables, $fields, $where);
			$asso = $res->fetchRow(DB_FETCHMODE_ASSOC);
			$assoId = $asso['asso_id'];
		}

		// Recherche de l'equipe
		$fields = array('team_id');
		$tables = array('a2t', 'teams');
		$where = "a2t_teamId = team_id".
	" AND a2t_assoId = $assoId";
		$res = $this->_select($tables, $fields, $where);
		if (empty($res))
		{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base ineccessible';
	  return $err;
		}

		// Non trouve -> creation de l'equipe
		if (!$res->numRows())
		{
	  // creation d'un compte pour l'equipe
	  $cols = array();
	  $cols['cunt_eventId'] = utvars::getEventId();
	  $cols['cunt_name'] = $pseudo;
	  $cols['cunt_code'] = $assoId;
	  $cols['cunt_status'] = WBS_ACCOUNT_OPEN;
	  $res = $this->_insert('accounts', $cols);
	  if (empty($res))
	  {
	  	$err['errMsg'] = __FILE__.' ('.__LINE__.'):base ineccessible';
	  	return $err;
	  }
	  $accountId = $res;

	  // creation de l'equipe
	  $cols = array();
	  $cols['team_eventId'] = utvars::getEventId();
	  $cols['team_name'] = $pseudo;
	  $cols['team_externId'] = $externId;
	  $cols['team_stamp'] = $stamp;
	  $cols['team_accountId'] = $accountId;
	  $cols['team_date'] = date(DATE_FMT);
	  $res = $this->_insert('teams', $cols);
	  if (empty($res))
	  {
	  	$err['errMsg'] = __FILE__.' ('.__LINE__.'):base ineccessible';
	  	return $err;
	  }
	  $teamId = $res;

	  // creation de la relation assoc,equipe
	  $cols = array();
	  $cols['a2t_teamId'] = $teamId;
	  $cols['a2t_assoId'] = $assoId;
	  $res = $this->_insert('a2t', $cols);
	  if (empty($res))
	  {
	  	$err['errMsg'] = __FILE__.' ('.__LINE__.'):base ineccessible';
	  	return $err;
	  }

		}
		else
		{
	  $team = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $teamId = $team['team_id'];

	  $cols['team_externId'] = $externId;
	  $where = "team_id=$teamId";
	  $res = $this->_update('teams', $cols, $where);
		}
		return $teamId;
	}
	// }}}

	// {{{ searchRegi
	/**
	 * Return the regi Id of the searched registration
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function searchRegi($regiDef, $teamId, $item)
	{

		$externId = $regiDef['externId'];
		$gender  = $regiDef['gender'];
		$license = $regiDef['license'];
		$famName = strtoupper($regiDef['famName']);
		$firstName = ucwords(strtolower($regiDef['firstName']));

		// chercher <externId> dans regi_externId
		$fields = array('regi_id', 'regi_accountId');
		$tables = array('registration');
		$where = "regi_externId = '$externId'".
	" AND regi_eventId =".utvars::getEventId();
		$res = $this->_select($tables, $fields, $where);
		if (empty($res))
		{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base ineccessible';
	  return $err;
		}

		// Si trouve retour regi_id
		if ($res->numRows())
		{
			$res = $this->_select($tables, $fields, $where);
			$regi = $res->fetchRow(DB_FETCHMODE_ASSOC);
			$this->_updateItem($regi['regi_id'], $regi['regi_accountId'], $item);
			return $regi['regi_id'];
		}

		// Chercher <license> dans member
		if ($license != '')
		{
	  $fields = array('mber_id');
	  $tables = array('members');
	  $where = "mber_licence = '$license'";
	  $res = $this->_select($tables, $fields, $where);
	  if (empty($res))
	  {
	  	$err['errMsg'] = __FILE__.' ('.__LINE__.'):base ineccessible';
	  	return $err;
	  }
		}
		// Non trouve -> chercher <nom,prenom> dans member
		if (!$res->numRows())
		{
	  $rows = array();
	  $fields = array('mber_id');
	  $tables = array('members');
	  $where = "mber_firstname = '".addslashes($firstName)."'".
	    " AND mber_secondname = '".addslashes($famName)."'";
	  $res = $this->_select($tables, $fields, $where);
	  if (empty($res))
	  {
	  	$err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  	return $err;
	  }
		}

		// Non trouve -> Creation du membre
		if (!$res->numRows())
		{
	  $cols = array();
	  $cols['mber_firstname'] = $firstName;
	  $cols['mber_secondname'] = $famName;
	  $cols['mber_sexe'] = $gender == 'M' ? WBS_MALE:WBS_FEMALE;
	  $cols['mber_licence'] = $license;
	  $res = $this->_insert('members', $cols);
	  if (empty($res))
	  {
	  	$err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  	return $err;
	  }
	  $memberId = $res;
		}
		else
		{
			$res = $this->_select($tables, $fields, $where);
			$member = $res->fetchRow(DB_FETCHMODE_ASSOC);
			$memberId = $member['mber_id'];
		}

		// Recherche de l'inscription
		$fields = array('regi_id', 'regi_accountId');
		$tables = array('registration');
		$where = "regi_memberId = $memberId".
	" AND regi_eventId =".utvars::getEventId();
		$res = $this->_select($tables, $fields, $where);
		if (empty($res))
		{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
		}

		// Non trouve -> Creation de l'inscription
		if (!$res->numRows())
		{
	  // Recherche du compte de l'equipe
	  $fields = array('team_accountId');
	  $tables = array('teams');
	  $where = "team_id = $teamId";
	  $res = $this->_select($tables, $fields, $where);
	  if (empty($res))
	  {
	  	$err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  	return $err;
	  }
	  $team = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $accountId = $team['team_accountId'];

	  $cols = array();
	  $cols['regi_eventId'] = utvars::getEventId();
	  $cols['regi_memberId'] = $memberId;
	  $cols['regi_teamId'] = $teamId;
	  $cols['regi_date'] = date(DATE_FMT);
	  $cols['regi_shortName'] = "$famName ".
	  substr($firstName, 0, 1).".";
	  $cols['regi_type'] = WBS_PLAYER;
	  $cols['regi_accountId'] = $accountId;
	  $cols['regi_externId'] = $externId;
	  $res = $this->_insert('registration', $cols);
	  if (empty($res))
	  {
	  	$err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  	return $err;
	  }
	  $regiId = $res;
		}
		else
		{
			$res = $this->_select($tables, $fields, $where);
			$regi = $res->fetchRow(DB_FETCHMODE_ASSOC);
			$regiId = $regi['regi_id'];
			$accountId = $regi['regi_accountId'];
		}

		// Recherche de l'ecriture pour inscription
		// si elle est definie
		$this->_updateItem($regiId, $accountId, $item);
		return $regiId;
	}
	// }}}

	// {{{ _updateItem
	/**
	 * Updtate the adminstrative registration
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function _updateItem($regiId, $accountId, $item)
	{
		if (count($item))
		{
	  $fields = array('cmd_id');
	  $tables = array('commands');
	  $where = "cmd_regiId = $regiId".
	    " AND cmd_accountId = $accountId";
	  $order = "cmd_id";
	  $res = $this->_select($tables, $fields, $where);
	  if (empty($res))
	  {
	  	$err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  	return $err;
	  }
	  // Non trouve -> creation
	  if (!$res->numRows())
	  {
	  	$cols = array();
	  	$cols['cmd_name'] = $item['item_name'];
	  	$cols['cmd_itemId'] = $item['item_id'];
	  	$cols['cmd_regiId'] = $regiId;
	  	$cols['cmd_accountId'] = $accountId;
	  	$cols['cmd_date'] = date(DATE_FMT);
	  	$cols['cmd_value'] = $item['item_value'];
	  	//$cols['cmd_type'] = "???";
	  	$res = $this->_insert('commands', $cols);
	  	if (empty($res))
	  	{
	  		$err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  		return $err;
	  	}
	  }
	  // Trouve -> mise a jour
	  else
	  {
	  	$res = $this->_select($tables, $fields, $where);
	  	$command = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	$cmdId = $command['cmd_id'];
	  	$cols = array();
	  	$cols['cmd_name'] = $item['item_name'];
	  	$cols['cmd_itemId'] = $item['item_id'];
	  	$cols['cmd_value'] = $item['item_value'];
	  	$where = "cmd_id=$cmdId";
	  	$res = $this->_update('commands', $cols, $where);
	  	if (empty($res))
	  	{
	  		$err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  		return $err;
	  	}
	  }
		}
		return;
	}
	// }}}

	// {{{ updateRegi
	/**
	 * Return the regi Id of the searched registration
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function updateRegi($regiId, $data)
	{
		$err = array();
		// chercher le classement
		$fields = array('rank_id');
		$tables = array('ranks');
		$where = "rank_regiId = $regiId".
	" AND rank_disci =". $data['discipline'];
		$res = $this->_select($tables, $fields, $where);
		if (empty($res))
		{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
		}
		// Non trouve _> creation du classement
		if (!$res->numRows())
		{
	  $rank['rank_rankdefId'] = $data['rankDef'];
	  $rank['rank_average'] = 0;
	  $rank['rank_disci'] = $data['discipline'];
	  $rank['rank_regiId'] = $regiId;
	  $res = $this->_insert('ranks', $rank);
	  if (empty($res))
	  {
	  	$err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  	return $err;
	  }
		}

		// Chercher la paire du partenaire
		$pairId_p = 0;
		if (isset($data['partner']))
		{
	  $fields = array('pair_id', 'pair_drawId');
	  $tables = array('registration', 'i2p', 'pairs');
	  $where = "regi_externId = ".$data['partner'].
	    " AND regi_id = i2p_regiId".
	    " AND i2p_pairId = pair_id".
	    " AND pair_disci =".$data['disci'];
	  $res = $this->_select($tables, $fields, $where);
	  if (empty($res))
	  {
	  	$err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  	return $err;
	  }
	  if ($res->numRows())
	  {
	  	$res = $this->_select($tables, $fields, $where);
	  	$pair = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	if ($pair['pair_drawId'] != $data['serial'])
	  	$err[] = "msgPartnerAnotherPair";
	  	else
	  	$pairId_p = $pair['pair_id'];
	  }
		}

		// Chercher sa paire
		$pairId = 0;
		$fields = array('pair_id', 'pair_drawId');
		$tables = array('i2p', 'pairs');
		$where = "i2p_regiId = $regiId".
	    " AND i2p_pairId = pair_id".
	    " AND pair_disci =".$data['disci'];
		$res = $this->_select($tables, $fields, $where);
		if (empty($res))
		{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
		}
		if ($res->numRows())
		{
	  $pair = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $pairId = $pair['pair_id'];
		}

		// Le joueur n'a pas de paire
		if (!$pairId)
		{
	  // Partenaire sans paire, en creer une
	  if (!$pairId_p)
	  {
	  	$cols = array();
	  	$cols['pair_disci'] = $data['disci'];
	  	$cols['pair_drawId'] = $data['serial'];
	  	$cols['pair_status'] = WBS_PAIR_NONE;
	  	$cols['pair_natNum'] = $data['license'];
	  	$res = $this->_insert('pairs', $cols);
	  	if (empty($res))
	  	{
	  		$err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  		return $err;
	  	}
	  	$pairId = $res;
	  }
	  // utiliser la paire du partenaire
	  else
	  $pairId = $pairId_p;

	  $cols = array();
	  $cols['i2p_regiId'] = $regiId;
	  $cols['i2p_pairId'] = $pairId;
	  $res = $this->_insert('i2p', $cols);
	  if (empty($res))
	  {
	  	$err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  	return $err;
	  }
		}
		else
		{
	  if ($pairId_p && $pairId_p != $pairId)
	  $err[] = "msgPartnerUnknowed";
		}
		return $err;

	}
	// }}}

	// {{{ getRankDef
	/**
	 * Return the list of the rank definition
	 *
	 * @access public
	 * @return array   array of match
	 */
	function getRankDefs()
	{
		// chercher le classement
		$fields = array('rkdf_id', 'rkdf_label');
		$tables = array('rankdef');
		$order = "rkdf_label";
		$res = $this->_select($tables, $fields, false, $order);
		if (empty($res))
		{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
		}
		while ($rankDef = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$rankDefs[$rankDef['rkdf_label']] = $rankDef['rkdf_id'];

		return $rankDefs;
	}
	// }}}

	// {{{ getDraws
	/**
	 * Return the list of the draws (discipline + serial)
	 *
	 * @access public
	 * @return array   array of match
	 */
	function getDraws()
	{
		// chercher le classement
		$fields = array('draw_id', 'draw_serial', 'draw_disci');
		$tables = array('draws');
		$where = "draw_eventId=".utvars::getEventId();
		$order = "draw_serial, draw_disci";
		$res = $this->_select($tables, $fields, $where, $order);
		if (empty($res))
		{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
		}
		$draws = array();
		while ($draw = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$draws[$draw['draw_disci']][$draw['draw_serial']] = $draw['draw_id'];
		return $draws;
	}
	// }}}

	// {{{ getItems
	/**
	 * Return the list of the items for registration
	 *
	 * @access public
	 * @return array   array of match
	 */
	function getItems($itemIds)
	{
		// chercher les articles
		$i = 1;
		foreach($itemIds as $itemId)
		{
	  		$fields = array('item_id', 'item_name', 'item_value');
	  		$tables = array('items');
	  		$where = "item_id=$itemId";
	  		$res = $this->_select($tables, $fields, $where);
	  		if (empty($res))
	  		{
	  			$err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  			return $err;
	  		}
	  		if ($res->numRows())
	  		{
	  			$res = $this->_select($tables, $fields, $where);
	  			$items[$i++] = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  		}
	  		else $items[$i++] = array();
		}
		return $items;
	}
	// }}}

	// {{{ getAllItems
	/**
	 * Return the list of the items
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getAllItems()
	{
		$eventId = utvars::getEventId();
		$fields = array('item_id', 'item_name');
		$tables = array('items');
		$where = "item_eventId=$eventId";
		$order = "item_name";
		$res = $this->_select($tables, $fields, $where, $order);
		if (empty($res))
		{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
		}


		$items[-1] = '----';
		while ($it = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$items[$it['item_id']] = $it['item_name'];
		}
		return $items;
	}
	// }}}


}
?>