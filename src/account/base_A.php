<?php
/*****************************************************************************
 !   Module     : Account
 !   File       : $Source: /cvsroot/aotb/badnet/src/account/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.6 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/06 07:59:33 $
 ******************************************************************************/

require_once "utils/utbase.php";

/**
 * Acces to the dababase for events
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class accountBase_A extends utbase
{

	// {{{ properties
	// }}}

	// {{{ getMembers
	/**
	 * Return the members of a account
	 *
	 * @access public
	 * @param  integer  $accountId  Id of the account
	 * @return array   array of users
	 */
	function getMembers($accountId, $sort)
	{
		// Select the players of the team
		$eventId = utvars::getEventId();
		$fields = array('regi_id', 'mber_sexe', 'mber_secondname',
		      'mber_firstname', 'regi_type',
 		      "sum(cmd_value) as value",
 		      "sum(cmd_discount) as discount",
 		      "sum(cmd_payed) as payed",
 		      "sum(cmd_value - cmd_discount - cmd_payed) as solde");
		$tables = array('members',  "registration LEFT JOIN commands ON
		regi_id = cmd_regiId AND cmd_accountId = $accountId
		AND cmd_itemId > -1");
		$where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_accountId = $accountId ".
	" AND regi_memberId = mber_id ".
	" AND regi_eventId = $eventId".
	" AND mber_id >= 0".
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
		$res = $this->_select($tables, $fields, $where, $order);
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $entry[1] = $ut->getSmaLabel($entry[1]);
	  $entry[4] = $ut->getLabel($entry[4]);
	  $entry[5] = sprintf('%02.2f',$entry[5]);
	  $entry[6] = sprintf('%02.2f',$entry[6]);
	  $entry[7] = sprintf('%02.2f',$entry[7]);
	  $entry[8] = sprintf('%02.2f',$entry[8]);
	  $rows[]=$entry;
		}
		return $rows;
	}
	// }}}


	// {{{ getAccounts
	/**
	 * Return the list of the accounts
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort associations
	 * @return array   array of users
	 */
	function getAccounts($sort=1)
	{

		$fields = array('cunt_id', 'cunt_name', 'cunt_code', 'cunt_status',
		      'sum(cmd_value) as value', 'sum(cmd_discount) as discount', 'sum(cmd_payed) as payed', 
		      'sum(cmd_value - cmd_discount - cmd_payed) as solde'
		      );
		      $tables = array('accounts LEFT JOIN commands ON
	    cmd_accountId = cunt_id AND cmd_itemId > -1');
		      $where = "cunt_eventId =".utvars::getEventId(). " GROUP BY cunt_id";
		      $order = abs($sort);
		      if ($sort < 0)
		      $order .= ' DESC';
		      $res = $this->_select($tables, $fields, $where, $order);
		      if (!$res->numRows())
		      {
		      	$err['errMsg'] = "msgNoAccounts";
		      	return $err;
		      }

		      $res = $this->_select($tables, $fields, $where, $order);
		      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		      {
	  			$entry['value'] = sprintf('%02.2f',$entry['value']);
	  			$entry['discount'] = sprintf('%02.2f',$entry['discount']);
	  			$entry['payed'] = sprintf('%02.2f',$entry['payed']);
	  			$entry['solde'] = sprintf('%02.2f',$entry['solde']);
		      	$rows[] = $entry;
		      }
		      return $rows;
	}
	// }}}

	// {{{ getCredits
	/**
	 * Return the list of the credits for an account
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort associations
	 * @return array   array of users
	 */
	function getCredits($accountId, $sort=1)
	{

		$fields = array('cmd_id', 'cmd_date', 'cmd_name', 'regi_longName',
		      'cmd_payed', 'cmd_discount', 'cmd_value-cmd_payed',
		      'regi_id');
		$tables = array('accounts', 'commands LEFT JOIN registration ON
	    cmd_regiId = regi_id');
		$where = "cunt_id = $accountId".
	" AND cmd_itemId = -1".
	" AND cmd_accountId = cunt_id";
		$order = abs($sort);
		if ($sort < 0)
		$order .= ' DESC';
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = "msgNoCredits";
	  return $err;
		}

		$utd = new utdate();
		$ut = new utils();
		$res = $this->_select($tables, $fields, $where, $order);
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
			$utd->setIsoDate($entry[1]);
	  $entry[1] = $utd->getDate();
	  if ($entry[6] == 0)  $entry[6] = $ut->getLabel(WBS_YES);
	  else if ($entry[6] == $entry[5])  $entry[6] = $ut->getLabel(WBS_NO);
	   
	  $rows[] = $entry;
		}
		return $rows;
	}
	// }}}


	// {{{ getPurchases
	/**
	 * Return the list of the purchases for an account
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort associations
	 * @return array   array of users
	 */
	function getPurchases($accountId, $sort=1, $date)
	{

		$fields = array('cmd_id', 'cmd_date', 'cmd_name', 'regi_longName',
		      'cmd_value', 'cmd_discount', 'cmd_payed', 
		      'cmd_value - cmd_discount - cmd_payed as solde',
		      'regi_id');
		$tables = array('accounts', 'commands LEFT JOIN registration ON
	    cmd_regiId = regi_id');
		$where = "cunt_id = $accountId".
	" AND cmd_itemId > -1".
	" AND cmd_accountId = cunt_id";
		if ($date!= -1)
		{
	  $utd = new utdate();
	  $utd->setFrDate($date);
	  $where .= " AND cmd_date ='".$utd->getIsoDateTime()."'";
		}
		$order = abs($sort);
		if ($sort < 0)
		$order .= ' DESC';
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = "msgNoPurchases";
	  return $err;
		}

		$utd = new utdate();
		$res = $this->_select($tables, $fields, $where, $order);
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$utd->setIsoDate($entry['cmd_date']);
	  $entry['cmd_date'] = $utd->getDate();
	  $entry['cmd_value'] = sprintf('%02.2f',$entry['cmd_value']);
	  $entry['cmd_discount'] = sprintf('%02.2f',$entry['cmd_discount']);
	  $entry['cmd_payed'] = sprintf('%02.2f',$entry['cmd_payed']);
	  $entry['solde'] = sprintf('%02.2f',$entry['solde']);
	   
	  $rows[] = $entry;
		}
		return $rows;
	}
	// }}}

	// {{{ getSumPurchases
	/**
	 * Return the list of the purchases for an account
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort associations
	 * @return array   array of users
	 */
	function getSumPurchases($accountId, $sort=1, $date)
	{

		$fields = array('SUM(cmd_value) as value',
		      'SUM(cmd_discount) as discount', 
		      'SUM(cmd_payed) as payed', 
		      'SUM(cmd_value - cmd_discount - cmd_payed) as solde'
		      );
		      $tables = array('accounts', 'commands LEFT JOIN registration ON
	    cmd_regiId = regi_id');
		      $where = "cunt_id = $accountId".
	" AND cmd_itemId > -1".
	" AND cmd_accountId = cunt_id";
		      if ($date!= -1)
		      {
		      	$utd = new utdate();
		      	$utd->setFrDate($date);
		      	$where .= " AND cmd_date ='".$utd->getIsoDateTime()."'";
		      }
		      $where .= " GROUP BY cmd_accountId";
		      $order = abs($sort);
		      if ($sort < 0)
		      $order .= ' DESC';
		      $res = $this->_select($tables, $fields, $where, $order);
		      if (!$res->numRows())
		      {
		      	$err['errMsg'] = "msgNoPurchases";
		      	return $err;
		      }

		      $res = $this->_select($tables, $fields, $where, $order);
		      $sums = $res->fetchRow(DB_FETCHMODE_ASSOC);
		      $sums['value'] = sprintf('%02.2f',$sums['value']);
		      $sums['discount'] = sprintf('%02.2f',$sums['discount']);
		      $sums['payed'] = sprintf('%02.2f',$sums['payed']);
		      $sums['solde'] = sprintf('%02.2f',$sums['solde']);

		      return $sums;
	}
	// }}}


	// {{{ getAccount
	/**
	 * Return the column of an account
	 *
	 * @access public
	 * @param  string  $assoId  id of the member
	 * @return array   information of the member if any
	 */
	function getAccount($accountId)
	{
		$fields = array('cunt_id', 'cunt_name', 'cunt_code', 'cunt_status',
		      'cunt_cmt', 'team_name', 'team_id');
		$tables = array('accounts LEFT JOIN teams ON
                       cunt_id=team_accountId');
		$where = "cunt_id = '$accountId'";
		$res = $this->_select($tables, $fields, $where);
		$data = $res->fetchRow(DB_FETCHMODE_ASSOC);

		$fields = array('SUM(cmd_value - cmd_discount - cmd_payed) as solde');
		$tables = array('accounts LEFT JOIN commands ON
                       cunt_id=cmd_accountId');
		$where = "cunt_id = '$accountId'";
		$res = $this->_select($tables, $fields, $where);
		$value = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$data['solde'] = $value['solde'];
		return $data;
	}
	// }}}

	// {{{ updateAccount
	/**
	 * Add or update an account into the database
	 *
	 * @access public
	 * @param  string  $info   column of the event
	 * @return mixed
	 */
	function updateAccount($infos)
	{
		if ($infos['cunt_id'] != -1)
		{
	  $where = "cunt_id=".$infos['cunt_id'];
	  $res = $this->_update('accounts', $infos, $where);
		}
		else
		{
	  unset($infos['cunt_id']);
	  $infos['cunt_id'] = $this->_insert('accounts', $infos);
		}
		return $infos['cunt_id'];
	}
	// }}}

	// {{{ delAccounts
	/**
	 * Delete some accounts
	 *
	 * @access public
	 * @param  arrays  $assos   id's of the associations to delete
	 * @return mixed
	 */
	function delAccounts($ids)
	{
		foreach ($ids as $id)
		{
	  // Search the purchase associate to the account
	  $fields = array('*');
	  $tables = array('commands');
	  $where = "cmd_accountId =$id";
	  $res = $this->_select($tables, $fields, $where);
	  if ($res->numRows())
	  {
	  	$err['errMsg'] = 'msgPurchaseExist';
	  	return $err;
	  }

	  // Search the team associate to the account
	  $fields = array('*');
	  $tables = array('teams');
	  $where = "team_accountId =$id";
	  $res = $this->_select($tables, $fields, $where);
	  if ($res->numRows())
	  {
	  	$err['errMsg'] = 'msgTeamExist';
	  	return $err;
	  }

	  // Search the members associate to the account
	  $fields = array('*');
	  $tables = array('registration');
	  $where = "regi_accountId =$id";
	  $res = $this->_select($tables, $fields, $where);
	  if ($res->numRows())
	  {
	  	$err['errMsg'] = 'msgMemberExist';
	  	return $err;
	  }

	  // Delete the account
	  $where = "cunt_id =$id";
	  $res = $this->_delete('accounts', $where);
		}
		return true;
	}
	// }}}
}
?>