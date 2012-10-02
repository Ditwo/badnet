<?php
/*****************************************************************************
 !   Module     : members
 !   File       : $Source: /cvsroot/aotb/badnet/src/regi/baseAdm_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.8 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/06 07:59:33 $
 ******************************************************************************/
require_once "utils/utbase.php";
require_once "items/items.inc";

/**
 * Acces to the dababase for events
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class regiAdmBase_A extends utBase
{
	// {{{ getTeams
	/**
	*/
	function getTeams()
	{

		$fields = array('team_id', 'asso_pseudo','team_name', 'cunt_id');
		$tables = array("teams LEFT JOIN accounts ON team_accountId=cunt_id",
		      'assocs', 'a2t');
		$where = 	'team_eventId ='. utvars::getEventId().
	' AND team_del !='. WBS_DATA_DELETE.
	' AND team_id = a2t_teamId'.
	' AND asso_id = a2t_assoId'.
	' GROUP BY team_id';
		$order = "asso_pseudo ";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = "msgNoAssocs";
	  return $err;
		}

		$trans = array('team_name');
		$rows[-1] = '----';
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $entry = $this->_getTranslate('teams', $trans,
	  $entry['team_id'], $entry);
	  $rows[$entry['cunt_id']] = "{$entry['asso_pseudo']} :: {$entry['team_name']}";
		}
		return $rows;
	}
	// }}}


	// {{{ getRegiPurchases
	/**
	 * Return the list of ALL purchases for a player
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort associations
	 * @return array   array of users
	 */
	function getRegiPurchases($regiId)
	{

		$eventId = utvars::getEventId();
		$fields = array('mber_id');
		$where = "regi_memberId= mber_id".
	" AND regi_id=$regiId";
		$tables = array('members', 'registration');
		$memberId = $this->_selectFirst($tables, $fields, $where);

		$fields = array('cmd_date', 'evnt_name', 'cmd_name',
		      'cmd_value', 'cmd_discount', 'cmd_payed', 
		      'cmd_value - cmd_discount - cmd_payed as solde');
		$tables = array('commands', 'registration', 'events');
		$where = "cmd_itemId > -1".
	" AND cmd_regiId = regi_id".
	" AND regi_eventId = evnt_id".
	" AND evnt_id =".$eventId .
	" AND regi_memberId=$memberId";
		$order = 'cmd_date';
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = "msgNoPurchases";
	  return $err;
		}

		$utd = new utdate();
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

	// {{{ getRegiSumPurchases
	/**
	 * Return the list of ALL purchases for an account
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort associations
	 * @return array   array of users
	 */
	function getRegiSumPurchases($regiId)
	{
		$eventId = utvars::getEventId();
		
		$fields = array('mber_id');
		$where = "regi_memberId= mber_id".
	" AND regi_id=$regiId";
		$tables = array('members', 'registration');
		$memberId = $this->_selectFirst($tables, $fields, $where);

		$fields = array('SUM(cmd_value) as value',
		      'SUM(cmd_discount) as discount', 
		      'SUM(cmd_payed) as payed', 
		      'SUM(cmd_value - cmd_discount - cmd_payed) as solde'
		      );
		      $tables = array('commands', 'registration');
		      $where = "cmd_regiId = regi_id".
	" AND regi_memberId = $memberId".
	" AND regi_eventid =".$eventId .
		      " AND cmd_itemId > -1";
		      $where .= " GROUP BY regi_memberId";
		      $res = $this->_select($tables, $fields, $where);
		      if (!$res->numRows())
		      {
		      	$err['errMsg'] = "msgNoPurchases";
		      	return $err;
		      }

		      $sums = $res->fetchRow(DB_FETCHMODE_ASSOC);
		      $sums['value'] = sprintf('%02.2f',$sums['value']);
		      $sums['discount'] = sprintf('%02.2f',$sums['discount']);
		      $sums['payed'] = sprintf('%02.2f',$sums['payed']);
		      $sums['solde'] = sprintf('%02.2f',$sums['solde']);
		      return $sums;
	}
	// }}}


	// {{{ getRegis
	/**
	 * Return the registered members
	 *
	 * @access public
	 * @param  integer  $type  Id of the team
	 * @return array   array of users
	 */
	function getRegis($teamId)
	{
		// Select the players of the team
		$eventId = utvars::getEventId();
		$fields = array('regi_id', 'regi_longName');
		$tables = array('registration', 'members');
		$where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_eventId = $eventId".
	" AND regi_memberId = mber_id".
	" AND regi_teamId = $teamId".
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

	// {{{ getTransport
	/**
	 * Return the list of the members in arrival or departure order
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort users
	 * @return array   array of users
	 */
	function getTransport($accountId)
	{
		$fields = array('regi_id', 'mber_sexe', 'regi_longName', 'regi_type',
		      'regi_arrival', 'regi_arrcmt', 'regi_departure', 
		      'regi_depcmt', 'team_name', 'team_id', 'regi_wo', 'regi_transportcmt');
		$tables = array('members', 'registration',
      'teams LEFT JOIN accounts ON team_accountId=cunt_id');
		$where = "regi_eventId = ".utvars::getEventId().
	" AND regi_memberId = mber_id".
	" AND regi_teamId = team_id".
	" AND mber_id >= 0";
		if ($accountId > 0)
		$where .= ' AND cunt_id=' . $accountId;
		$order = "team_id, regi_longName";

		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = "noDepArr";
	  return $err;
		}

		$date = new utdate();
		$ut = new utils();
		$uti = new utimg();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $entry['mber_sexe'] = $ut->getSmaLabel($entry['mber_sexe']);
	  $entry['regi_type'] = $ut->getLabel($entry['regi_type']);
	  $date->setIsoDateTime($entry['regi_arrival']);
	  $entry['regi_arrival'] = $date->getDateTime();
	  $date->setIsoDateTime($entry['regi_departure']);
	  $entry['regi_departure'] = $date->getDateTime();
	  if ( $entry['regi_wo'] == WBS_YES ) $entry['class'] = 'classWo';
			if (!empty($entry['regi_transportcmt']))
			{
	  			$entry['title'] = $entry['regi_transportcmt'];
				$entry['indic'] = $uti->getIcon(1265);
			}
			else $entry['indic'] = '';
	  $rows[] = $entry;
		}
		return $rows;
	}
	// }}}

	// {{{ getDeparrs
	/**
	 * Return the list of the members in arrival or departure order
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort users
	 * @return array   array of users
	 */
	function getDeparrs($fieldDate, $fieldCmt)
	{
		$fields = array('regi_id', 'mber_sexe', 'regi_longName', 'team_name',
		      'regi_type', $fieldDate, $fieldCmt, 'regi_id AS hotel', 'regi_prise', 'team_id', 'regi_wo','regi_transportcmt');
		$tables = array('members', 'teams', 'registration');
		$where = "regi_eventId = ".utvars::getEventId().
	" AND regi_memberId = mber_id".
	" AND regi_teamId = team_id".
	" AND $fieldDate >= 0".
	" AND $fieldDate != '00-00-0000 00:00:00'".
	" AND mber_id >= 0";
		$order = "$fieldDate, team_name, regi_longName";

		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
			$err['errMsg'] = "noDepArr";
			return $err;
		}

		$date = new utdate();
		$ut = new utils();
		$uti = new utimg();
		$tables = array('items', 'commands');
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$entry['mber_sexe'] = $ut->getSmaLabel($entry['mber_sexe']);
			$entry['regi_type'] = $ut->getLabel($entry['regi_type']);
			$date->setIsoDateTime($entry[$fieldDate]);
			$entry[$fieldDate] = $date->getTime();
			$entry['date'] = $date->getDateWithDay();
			// Recherche hotel
			$where = 'cmd_regiId =' . $entry['regi_id']
			. ' AND item_id=cmd_itemId '
			. ' AND item_rubrikId ='.WBS_RUBRIK_HOTEL
			. " AND date(cmd_date) = '" . $date->getIsoDate()."'";
			$entry['hotel'] = $this->_selectFirst($tables, 'cmd_name', $where);
			if ( $entry['regi_wo'] == WBS_YES ) $entry['class'] = 'classWo';
			
			$date->setTime($entry['regi_prise']);
			$entry['regi_prise'] = $date->getTime();
			if (!empty($entry['regi_transportcmt']))
			{
	  			$entry['title'] = $entry['regi_transportcmt'];
				$entry['indic'] = $uti->getIcon(1265);
			}
			else $entry['indic'] = '';
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
		      'cunt_id', 'regi_wo');
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
	  $entry['S'] = '--';
	  $entry['D'] = '--';
	  $entry['M'] = '--';
      $entry[5] = sprintf('%02.2f', $entry[5]);
      $entry[6] = sprintf('%02.2f', $entry[6]);
      $entry[7] = sprintf('%02.2f', $entry[7]);
      $entry[8] = sprintf('%02.2f', $entry[8]);
      if ( $entry[10] == WBS_YES)  $entry['class'] = 'classWo';
	  unset($entry[10]);
	  $rows[$entry[0]]=$entry;
		}

		// Pour un tournoi individuel, recuperer les inscriptions
		// Et les heures de premier match
		$utev = new utevent();
		if ($utev->getEventType($eventId) == WBS_EVENT_INDIVIDUAL)
		{
	  $fields = array('i2p_regiId',  'pair_drawId', 'pair_disci',
			  'draw_stamp');
	  $tables = array('registration', 'i2p', 'pairs', 'draws');
	  $where  = " draw_id = pair_drawId".
	    " AND regi_eventId = $eventId".
	    " AND regi_id = i2p_regiId".
	    " AND i2p_pairId = pair_id".
	    " AND regi_type = ".WBS_PLAYER;
	  $order = "1, pair_disci";
	  $res = $this->_select($tables, $fields, $where, $order);
	  if ($res->numRows())
	  {
	  	$trans = array('draw_stamp');
	  	while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  	{
	  		$entry = $this->_getTranslate('draws', $trans,
	  		$entry['pair_drawId'], $entry);
	  		$id = $entry['i2p_regiId'];
	  		if ($entry['pair_disci']== WBS_SINGLE)
	  		$rows[$id]['S'] = $entry['draw_stamp'];
	  		else if ($entry['pair_disci'] == WBS_DOUBLE)
	  		$rows[$id]['D'] = $entry['draw_stamp'];
	  		else
	  		$rows[$id]['M'] = $entry['draw_stamp'];
	  	}
	  }
		}
		return $rows;
	}
	// }}}

	// {{{ getRegiTeams
	/**
	 * Return the list of the players registered for a team event
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort users
	 * @return array   array of users
	 */
	function getRegiTeams($sort)
	{
		// Retrieve registered players
		$eventId = utvars::getEventId();
		$fields = array('regi_id', 'regi_date', 'mber_id', 'mber_sexe',
		      'regi_longName', 'rkdf_label', 'mber_licence', 
		      'mber_ibfnumber', 'team_name', 'team_id', 
		      'regi_memberId', 'rank_isFede', 'rank_dateFede',
		      'regi_pbl', 'regi_del', 'team_del', 'team_pbl',
		      'mber_urlphoto');
		$tables  = array('registration', 'teams', 'ranks', 'rankdef', 'members');
		$where = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_teamId = team_id".
	" AND regi_eventId = $eventId".
	" AND regi_memberId >= 0".
	" AND regi_id = rank_regiId".
	" AND rank_rankdefId = rkdf_id".
	" AND regi_memberId = mber_id";
		if ($sort > 0)
		$order = "$sort, regi_id, rank_disci ";
		else
		$order = abs($sort) . " desc, regi_id, rank_disci";

		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $rows['errMsg'] = 'msgNoRegisteredPlayer';
	  return $rows;
		}
		$id = -1;
		$tmp = array();
		$utd = new utdate();
		$ut = new utils();
		$uti = new utimg();
		// $tmp[10] contient le nombre de classement issu du
		// site de la fede. Si les trois classements sont issus
		// de la fede, on rajoute la source et la date apres le
		// classement
		$trans = array('team_name');
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{

			if ($id != $entry['regi_id'])
			{
				if($id > 0)
				{
					if ($tmp['rank_isFede'] == 3)
					{
						$utd->setIsoDate($tmp['rank_dateFede']);
						$tmp['rkdf_label'] .= " (FFba ".$utd->getDate().")";
					}
					$tmp = $this->_getTranslate('teams', $trans,
					$tmp['team_id'], $tmp);
					$rows[] = $tmp;
				}
				$tmp = $entry;
				$tmp['mber_id'] = '';
				$tmp['mber_urlphoto'] = utimg::getPathPhoto($entry['mber_urlphoto']);

				$tmp['regi_pbl'] = $uti->getPubliIcon($entry['regi_del'],
				$entry['regi_pbl']);
				$tmp['team_pbl'] = $uti->getPubliIcon($entry['team_del'],
				$entry['team_pbl']);
				$utd->setIsoDate($entry['regi_date']);
				$tmp['regi_date'] = $utd->getDate();
				$tmp['mber_sexe'] = $ut->getSmaLabel($tmp['mber_sexe']);
				$id = $entry['regi_id'];
			}
			else
			{
				$tmp['rkdf_label'] .= ",{$entry['rkdf_label']}";
				$tmp['rank_isFede'] += $entry['rank_isFede'];
			}
		}
		if ($tmp['rank_isFede'] == 3)
		{
	  $utd->setIsoDate($tmp['rank_dateFede']);
	  $tmp['rkdf_label'] .= " (FFba ".$utd->getDate().")";
		}
		$tmp = $this->_getTranslate('teams', $trans,
		$tmp['team_id'], $tmp);
		$rows[] = $tmp;

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
		      'regi_departure', 'regi_arrival', 'regi_depcmt','regi_arrcmt',
		      "sum(cmd_value - cmd_discount - cmd_payed) as soldeAccount",
		      'mber_urlphoto');
		$tables = array('teams', 'members', 'registration LEFT JOIN accounts ON
            cunt_id = regi_accountId LEFT JOIN commands ON
            cmd_accountId = cunt_id');
		$where = "regi_id = $regiId".
	" AND regi_teamId = team_id".
	" AND regi_memberId = mber_id".
	" GROUP BY regi_id";
		$res = $this->_select($tables, $fields, $where);
		$ut = new utils();
		$player =  $res->fetchRow(DB_FETCHMODE_ASSOC);
		$player['regi_type'] = $ut->getLabel($player['regi_type']);

		$utd = new utdate();
		$utd->setIsoDate($player['regi_departure']);
		$player['regi_departure'] = $utd->getDateTime();
		$utd->setIsoDate($player['regi_arrival']);
		$player['regi_arrival'] = $utd->getDateTime();;

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

	// {{{ getCredits
	/**
	 * Return the list of the purchases for an account
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort associations
	 * @return array   array of users
	 */
	function getCredits($regiId, $sort=1)
	{

		$fields = array('cmd_id', 'cmd_date', 'cmd_name', 'cunt_name',
		      'cmd_payed', 'cmd_discount', 'cmd_value');
		$tables = array('accounts', 'registration LEFT JOIN commands ON
	    cmd_regiId = regi_id');
		$where = "regi_id = $regiId".
	" AND cmd_accountId = cunt_id".
	" AND cmd_itemId < 0";
		$order = abs($sort);
		if ($sort < 0)
		$order .= ' DESC';
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = "msgNoCredit";
	  return $err;
		}

		$utd = new utdate();
		$ut = new utils();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
			$utd->setIsoDate($entry[1]);
	  $entry[1] = $utd->getDate();
	  // Credit remboursable si valeur = paye
	  if ($entry[6] == $entry[4])  $entry[6] = $ut->getLabel(WBS_YES);
	  else $entry[6] = $ut->getLabel(WBS_NO);
      
      $entry[4] = sprintf('%02.2f', $entry[4]);
	  $entry[5] = sprintf('%02.2f', $entry[5]);
      $entry[6] = sprintf('%02.2f', $entry[6]);
	  
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
	function getPurchases($regiId, $sort=1)
	{

		$fields = array('cmd_id', 'cmd_date', 'cmd_name', 'cunt_name',
		      'cmd_value', 'cmd_discount', 'cmd_payed', 
		      'cmd_value - cmd_discount - cmd_payed as solde',
		      'item_rubrikId', 'item_name', 'cmd_cmt'
		      );
		      $tables = array('accounts', 'registration LEFT JOIN commands ON
	    cmd_regiId = regi_id LEFT JOIN items ON item_id=cmd_itemId');
		      $where = "regi_id = $regiId".
	" AND cmd_accountId = cunt_id".
	" AND cmd_itemId >=0";
		      $order = abs($sort);
		      if ($sort < 0) $order .= ' DESC';
		      $res = $this->_select($tables, $fields, $where, $order);
		      if (!$res->numRows())
		      {
		      	$err['errMsg'] = "msgNoPurchases";
		      	return $err;
		      }

		      $utd = new utdate();
		      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		      {
		      	$utd->setIsoDate($entry['cmd_date']);
		      	$entry['cmd_date'] = $utd->getDate();
		      	$entry['cmd_value'] = sprintf('%02.2f', $entry['cmd_value']);
		      	$entry['cmd_discount'] = sprintf('%02.2f', $entry['cmd_discount']);
		      	$entry['cmd_payed'] = sprintf('%02.2f', $entry['cmd_payed']);
		      	$entry['solde'] = sprintf('%02.2f', $entry['solde']);
		      	
		      	if ($entry['item_rubrikId']== WBS_RUBRIK_HOTEL)
		      	{
		      		$cell = array();
		      		$cell[] = array('value'=>"{$entry['cmd_name']}-{$entry['cmd_cmt']}",
			      'action'=>array(KAF_UPLOAD, 'items', ITEM_HOTEL, $entry['item_name']));
		      		$entry['cmd_name'] = $cell;
		      	}
		      	$rows[] = $entry;
		      }
		      return $rows;
	}
	// }}}

	// {{{ getMemberAdm
	/**
	 * Return the admin column of a member
	 *
	 * @access public
	 * @param  string  $memberId  id of the member
	 * @return array   information of the member if any
	 */
	function getMemberAdm($regiId)
	{
		$fields = array('regi_id', 'regi_accountId', 'regi_longName',
		      'regi_arrival', 'regi_arrcmt', 'regi_departure',
		      'regi_depcmt', 'cunt_id', 'cunt_name', 'regi_transportcmt', 'regi_prise');
		$tables = array('registration LEFT JOIN accounts ON
           regi_accountId = cunt_id');
		$where = "regi_id = '$regiId'";
		$res = $this->_select($tables, $fields, $where);
		$regi =  $res->fetchRow(DB_FETCHMODE_ASSOC);
		$date = new utdate();
		$date->setIsoDateTime($regi['regi_arrival']);
		$regi['regi_arrival'] = $date->getDateTime();
		$date->setIsoDateTime($regi['regi_departure']);
		$regi['regi_departure'] = $date->getDateTime();
		$date->setTime($regi['regi_prise']);
		$regi['regi_prise'] = $date->getTime();

		return $regi;
	}
	// }}}

	// {{{ getAccounts
	/**
	 * Return the list of accounts
	 *
	 * @access public
	 * @return array   information of the member if any
	 */
	function getAccounts()
	{
		$fields = array('cunt_id', 'cunt_name');
		$tables = array('accounts');
		$where = "cunt_eventId=".utvars::getEventId();
		$order  = "cunt_name";
		$res = $this->_select($tables, $fields, $where, $order);
		while ($account =  $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $rows[$account['cunt_id']] = $account['cunt_name'];
		}
		return $rows;
	}
	// }}}

	// {{{ updateMemberAccount
	/**
	 * Add or update a member into the database
	 *
	 * @access public
	 * @param  string  $info   column of the event
	 * @return mixed
	 */
	function updateMemberAccount($regiId, $accountId)
	{
		// Search old account of registered member
		$fields = array('regi_accountId');
		$tables = array('registration');
		$where = " regi_id=$regiId";
		$res = $this->_select($tables, $fields, $where);
		$regi = $res->fetchRow(DB_FETCHMODE_ORDERED);
		$oldRegiId = $regi[0];

		// Update purchases
		$fields = array();
		$fields['cmd_accountId'] = $accountId;
		$tables = array('commands');
		$where = " cmd_regiId=$regiId";
		$res = $this->_update('commands', $fields, $where);

		// Update registered member
		$fields = array();
		$fields['regi_accountId'] = $accountId;
		$where = " regi_id=$regiId";
		$res = $this->_update('registration', $fields, $where);

		return true;
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

		$ids = $regi['regi_id'];
		unset($regi['regi_id']);
		$where = 'regi_id IN(' . implode(',', $ids) . ')';
		$res = $this->_update('registration', $regi, $where);

		if ($propa)
		{
	  $where = 'regi_id='.reset($ids);
	  $res = $this->_select('registration', 'regi_teamId', $where);
	  $tmp = $res->fetchRow(DB_FETCHMODE_ORDERED);
	  $where = 'regi_eventId='.utvars::getEventId().
	    " AND regi_teamId=".$tmp[0].
	  	" AND regi_wo=" . WBS_NO;
	  unset($regi['regi_id']);
	  $res = $this->_update('registration', $regi, $where);
		}
		return true;
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

		$items[-1] = '----';
		while ($it = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$items[$it['item_id']] = $it['item_name'];
		}
		return $items;
	}
	// }}}

	// {{{ getFullItems
	/**
	 * Return the list of the items
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getFullItems($items)
	{
		$itemList = implode(',', $items);
		$eventId = utvars::getEventId();
		$fields = array('*');
		$tables = array('items');
		$where = "item_id IN ($itemList)";
		$res = $this->_select($tables, $fields, $where);

		while ($it = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$items[$it['item_id']] = $it;
		return $items;
	}
	// }}}


	// {{{ registerFees
	/**
	 * Automatic creation of purchase for entries of selected members
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function registerFees($items, $regis)
	{

		$regiList = implode(',', $regis);
		$itemList = implode(',', $items);
		$eventId = utvars::getEventId();
		// Supprimer les anciennes commandes
		$where = "cmd_regiId IN ($regiList)".
	" AND cmd_itemId IN ($itemList)".
	" AND cmd_payed =0";
		$res = $this->_delete('commands', $where);

		// Traiter chaque inscrit selectionne
		$itemsVals = $this->getFullItems($items);
		foreach( $regis as $regiId)
		{
	  // Compter son nombre de tableau
	  $fields = array('regi_accountId');
	  $tables = array('registration', 'i2p', 'pairs', 'draws');
	  $where = "regi_id = i2p_regiId".
	    " AND i2p_pairId = pair_id".
	    " AND pair_drawId = draw_id".
	    " AND i2p_regiId = $regiId"; 
	  $res = $this->_select($tables, $fields, $where);

	  // Genrerer la commande correspondante
	  $nbTab = $res->numRows();
	  if ($nbTab)
	  {
	  	$val = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	$nbTab--;
	  	if ($nbTab > 2) $nbTab = 2;
	  	$item = $itemsVals[$items[$nbTab]];
	  	$cols['cmd_name'] = $item['item_name'];
	  	$cols['cmd_itemId'] = $item['item_id'];
	  	$cols['cmd_regiId'] = $regiId;
	  	$cols['cmd_accountId'] = $val['regi_accountId'];
	  	$cols['cmd_date'] = date(DATE_DATE);
	  	$cols['cmd_value'] = $item['item_value'];

	  	$res = $this->_insert('commands', $cols);
	  }
		}
		return true;
	}
	// }}}

}
?>