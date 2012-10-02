<?php
/*****************************************************************************
 !   Module     : Registration
 !   File       : $Source: /cvsroot/aotb/badnet/src/teams/baseAdm_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.11 $
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

class teamsAdmBase_A extends utbase
{

	// {{{ properties
	// }}}


	// {{{ getPlayersRoom
	/**
	 * Return the list of the plyers with room
	 * @return array
	 */
	function getPlayersRoom($teamId)
	{
		$fields = array('regi_id', 'mber_sexe', 'regi_longName', 'cmd_name',
'item_code', 'cmd_cmt', 'cmd_date', 'item_ref', 'cmd_id',
'item_rubrikId', 'item_name');
		$tables = array('members',  'registration LEFT JOIN commands ON regi_id = cmd_regiId LEFT JOIN items ON cmd_itemId = item_id');
		$where  = "regi_del != ".WBS_DATA_DELETE.
" AND regi_teamId = $teamId".
		//" AND regi_id = cmd_regiId".
		//" AND regi_eventId = ".utvars::getEventId().
		//	" AND cmd_itemId = item_id".
" AND regi_memberId = mber_id".
		" AND item_rubrikId =" . WBS_RUBRIK_HOTEL;
		//" OR item_rubrikId ='')".
" AND mber_id >= 0";
		$order = "regi_longName, 'item_rubrikId', cmd_date, cmd_name";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
			$err['errMsg'] = "msgNoReservations";
			return $err;
		}

		$utd = new utdate();
		$regiId = null;
		$ut = new utils();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// Passage a un nouvel insrit
			if ($regiId != $entry['regi_id'])
			{
				if (!is_null($regiId))
				{
					$row['item_ref'] = $nbNight;
					$rows[] = $row;
				}
				$regiId = $entry['regi_id'];
				$row = $entry;
				$row['mber_sexe'] = $ut->getSmaLabel($row['mber_sexe']);
				//$row['cmd_date'] = '';
				$utd->setIsoDateTime($entry['cmd_date']);
				$row['cmd_date'] = $utd->getDate();
				$row['cmd_name'] = '';
				$row['item_code'] = '';
				$nbNight = 0;
				$room = null;
				$code = null;
			}
			// Saute les commandes non hotel
			if ($entry['item_rubrikId'] != WBS_RUBRIK_HOTEL) continue;
			// Passage a une nouvelle chambre ou type de chambre
			if($room != $entry['cmd_cmt'] ||
			$code != $entry['item_code'])
			{
				if (!is_null($room))
				{
					$row['item_ref'] = $nbNight;
					$rows[] = $row;
					$utd->setIsoDateTime($entry['cmd_date']);
					$row['cmd_date'] = $utd->getDate();
				}
				$room = $entry['cmd_cmt'];
				$code = $entry['item_code'];
				$nbNight = 0;
			}
			
			$row['item_code'] = $ut->getSmaLabel($entry['item_code']);
			$row['cmd_name'] = $entry['cmd_name'];
			$nbNight++;
		}
		$row['item_ref'] = $nbNight;
		$rows[] = $row;
		return $rows;
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
	function getCredits($teamId, $sort=1)
	{

		$fields = array('cmd_id', 'cmd_date', 'regi_longName', 'regi_type',
		      'cmd_payed', 'cmd_discount', 'cmd_value-cmd_payed');
		$tables = array('commands', 'registration');
		$where = "regi_teamId = $teamId".
	" AND cmd_regiId = regi_id".
	" AND cmd_itemId = -1";
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
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
			$utd->setIsoDate($entry[1]);
	  $entry[1] = $utd->getDate();
	  $entry[3] = $ut->getLabel($entry[3]);
	  if ($entry[6] == 0)
	  $entry[6] = $ut->getLabel(WBS_YES);
	  else if ($entry[6] == $entry[5])
	  $entry[6] = $ut->getLabel(WBS_NO);
	  $rows[] = $entry;
		}
		return $rows;
	}
	// }}}

	// {{{ getMembers
	/**
	 * Return the members of a team
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getMembers($teamId, $sort)
	{
		// Select the players of the team
		$eventId = utvars::getEventId();
		$fields = array('regi_id', 'mber_sexe', 'regi_longName',
		      'regi_type',
		      "sum(cmd_value) as value",
 		      "sum(cmd_discount) as discount",
 		      "sum(cmd_payed) as payed",
 		      "sum(cmd_value - cmd_discount - cmd_payed) as solde",
		      'team_accountId',
		      'regi_accountId', 'regi_wo'
		      );
		      //		      'regi_arrival', 'regi_arrcmt', 'regi_departure',
		      //		      'regi_depcmt', 'cunt_id');
		      $tables = array('teams', 'members',  'registration LEFT JOIN commands ON (regi_id = cmd_regiId AND regi_accountId = cmd_accountId)');
		      $where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_teamId = $teamId".
	" AND regi_memberId = mber_id ".
	" AND regi_eventId = $eventId".
	" AND mber_id >= 0".
	" AND team_id = regi_teamId".
        " GROUP BY regi_id";
		      $order = abs($sort);
		      if ($sort < 0)
		      $order .= " DESC";
		      $res = $this->_select($tables, $fields, $where, $order);
		      if (!$res->numRows())
		      {
		      	$infos['errMsg'] = "msgNoPlayers";
		      	return $infos;
		      }
		      $utd = new utdate();
		      $ut = new utils();
		      $total['value'] = 0;
		      $total['discount'] = 0;
		      $total['payed'] = 0;
		      $total['solde'] = 0;
		      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		      {
		      	$entry['mber_sexe'] = $ut->getSmaLabel($entry['mber_sexe']);
		      	$entry['regi_type'] = $ut->getLabel($entry['regi_type']);

		      	if ($entry['regi_accountId'] != $entry['team_accountId'])
		      	{
		      		$cell = array();
		      		$line['value'] = $entry['solde'];
		      		$line['class'] = 'classAdmWarning';
		      		$cell[] = $line;
		      		$entry['solde'] = $cell;
		      	}
		      	//$utd->setIsoDateTime($entry[5]);
		      	//$entry[5] = $utd->getDateTime()." ".$entry[6];
		      	//$utd->setIsoDateTime($entry[7]);
		      	//$entry[6] = $utd->getDateTime()." ".$entry[8];
		      	//$entry[7] = $entry[9];
		      	if ($entry['regi_wo'] == WBS_YES)
		      	$entry['class'] = 'classWo';
		      	$rows[]=$entry;
		      	$total['value'] += $entry['value'];
		      	$total['discount'] += $entry['discount'];
		      	$total['payed'] += $entry['payed'];
		      }
		      $total['solde'] = $total['value'] - $total['discount'] - $total['payed'];
		      $rows[] = array('regi_id'=>'','','','',$total['value'], $total['discount'],
		      $total['payed'],$total['solde']);
		      return $rows;
	}
	// }}}

	// {{{ getPlayers
	/**
	 * Return the players of a team
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getPlayers($teamId, $sort)
	{
		$ut = new utils();
		// Select the players of the team
		$eventId = utvars::getEventId();
		$fields = array('regi_id',  'regi_longName', 'mber_sexe',
		      'rkdf_label', 'regi_status', 'mber_licence', 
		      'mber_ibfnumber', 'regi_noc', 'mber_id', 'regi_del', 
		      'regi_pbl', 'rank_isFede', 'rank_DateFede',);
		$tables = array('members', 'registration', 'ranks', 'rankdef');
		$where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_teamId = $teamId".
	" AND regi_memberId = mber_id ".
	" AND regi_eventId = $eventId".
	" AND regi_id = rank_regiId".
	" AND rank_rankdefId = rkdf_id".
	" AND regi_type = ".WBS_PLAYER.
	" AND mber_id >= 0";
		$order = "1, rank_disci";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoPlayers";
	  return $infos;
		}
		$id = -1;
		$rows = array();
		if ($sort < 0) $sort++;
		else $sort--;
		$uti = new utimg();
		$utd = new utdate();

		// $tmp[10] contient le nombre de classement issu du
		// site de la fede. Si les trois classements sont issus
		// de la fede, on rajoute la source et la date apres le
		// classement
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
			if ($id != $entry[0])
			{
				if($id > 0)
				{
					$tmp[2] = $ut->getSmaLabel($tmp[2]);
					//if (trim($tmp[4]) == "")$tmp[4]=KAF_NONE;
					$tmp[9] = $uti->getPubliIcon($tmp[9],
					$tmp[10]);
					$tmp[4] = $ut->getLabel(($tmp[4]==WBS_REGI_TITULAIRE) ?
					WBS_YES:WBS_NO);
					if ($tmp[11] == 3)
					{
						$utd->setIsoDate($tmp[12]);
						$tmp[3] .= " (FFba ".$utd->getDate().")";
					}
					$sw[] = $tmp[abs($sort)];
					$rows[$id] = $tmp;
				}
				$tmp = $entry;
				$id = $entry[0];
			}
			else
			{
				$tmp[3] .= ",{$entry[3]}";
				$tmp[11] += $entry[11];
			}
		}
		$tmp[2] = $ut->getSmaLabel($tmp[2]);
		//if (trim($tmp[4]) == "")$tmp[4]=KAF_NONE;
		$tmp[9] = $uti->getPubliIcon($tmp[9],
		$tmp[10]);
		$tmp[4] = $ut->getLabel(($tmp[4]==WBS_REGI_TITULAIRE) ?
		WBS_YES:WBS_NO);
		if ($tmp[10] == 3)
		{
	  $utd->setIsoDate($tmp[11]);
	  $tmp[3] .= " (FFba ".$utd->getDate().")";
		}
		$sw[] = $tmp[abs($sort)];
		$rows[$id] = $tmp;


		$utev = new utEvent();
		// Pour un tournoi individuel, recuperer les inscriptions
		if ($utev->getEventType($eventId) == WBS_EVENT_INDIVIDUAL)
		{
	  $fields = array('i2p_regiId',  'pair_drawId', 'pair_disci',
			  'draw_stamp');
	  $tables = array('registration', 'i2p', 'pairs', 'draws');
	  $where  = "regi_teamId = $teamId".
	    " AND regi_eventId = $eventId".
	    " AND regi_id = i2p_regiId".
	    " AND i2p_pairId = pair_id".
	    " AND regi_type = ".WBS_PLAYER.
	    " AND pair_drawId = draw_id";
	  $order = "1, pair_disci";
	  $res = $this->_select($tables, $fields, $where, $order);
	  if ($res->numRows())
	  {
	  	$id = -1;
	  	$trans = array('draw_stamp');
	  	while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  	{
	  		$entry = $this->_getTranslate('draws', $trans,
	  		$entry['pair_drawId'], $entry);
	  		if ($id != $entry['i2p_regiId'])
	  		{
	  			if ($id > 0) $rows[$id][4] = $tmp;
	  			$tmp = $entry['draw_stamp'];
	  			$id = $entry['i2p_regiId'];
	  		}
	  		else
	  		$tmp .= ",".$entry['draw_stamp'];
	  	}
	  	$rows[$id][4] = $tmp;
	  }
		}
		if ($sort < 0)
		array_multisort($sw, SORT_DESC,$rows);
		else
		array_multisort($sw, $rows);
		return $rows;
	}
	// }}}

	// {{{ getOfficials
	/**
	 * Return the officials
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getOfficials($teamId, $sort)
	{
		$ut = new utils();
		// Select the other registered members of the team
		$fields = array('regi_id', 'regi_longName', 'mber_sexe',
		      'regi_type', 'regi_function', 'regi_noc',
		      'mber_id', 'regi_pbl', 'regi_del');
		$tables = array('members', 'registration');
		$where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_teamId = $teamId".
	" AND regi_memberId = mber_id ".
	" AND regi_type != ".WBS_PLAYER.
		//" AND regi_type > ".WBS_PLAYER.
		//" AND regi_type < ".WBS_COACH.
	" AND mber_id >= 0";
		$order = "regi_type, ".abs($sort);
		if ($sort < 0)
		$order .= " DESC";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoOffos";
	  return $infos;
		}
		$ut = new utils();
		$uti = new utimg();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $entry['mber_sexe'] = $ut->getSmaLabel($entry['mber_sexe']);
	  $entry['regi_type'] = $ut->getLabel($entry['regi_type']);
	  $entry['iconPbl'] = $uti->getPubliIcon($entry['regi_del'],
	  $entry['regi_pbl']);
	  $rows[] = $entry;
		}
		return $rows;
	}
	// }}}


	// {{{ getTeams
	/**
	 * Return the list of the associatons for administration
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort associations
	 * @return array   array of users
	 */
	function getTeams($sort=1)
	{

		$fields = array('team_id', 'team_date', 'asso_pseudo','team_name',
 		      "sum(cmd_value) as value",
 		      "sum(cmd_discount) as discount",
 		      "sum(cmd_payed) as payed",
 		      "sum(cmd_value - cmd_discount - cmd_payed) as solde",
		      'cunt_id');
		$tables = array("teams LEFT JOIN accounts ON team_accountId=cunt_id LEFT JOIN commands ON
                      cmd_accountId=cunt_id", 
		      'assocs', 'a2t');
		$where = 	'team_eventId ='. utvars::getEventId().
	' AND team_del !='. WBS_DATA_DELETE.
	' AND team_id = a2t_teamId'.
	' AND asso_id = a2t_assoId'.
	' GROUP BY team_id';
		$order = "$sort ";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = "msgNoAssocs";
	  return $err;
		}

		$utd = new utdate();
		$ut = new utils();
		$trans = array('team_name');
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $utd->setIsoDateTime($entry['team_date']);
	  $entry['team_date'] = $utd->getDate();
	  $entry = $this->_getTranslate('teams', $trans,
	  $entry['team_id'], $entry);
	  $rows[$entry['team_id']] = $entry;
		}
		return $rows;
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
		$fields = array('asso_id', 'asso_name', 'asso_stamp', 'asso_pseudo',
		      'asso_type', 'asso_cmt', 'asso_url', 'asso_logo',
		      'asso_noc');
		$tables[] = 'assocs';
		$where = "asso_id = '$assoId'";
		$res = $this->_select($tables, $fields, $where);
		$asso = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$trans = array('asso_name','asso_pseudo');
		$asso = $this->_getTranslate('assos', $trans,
		$asso['asso_id'], $asso);
		return $asso;

	}
	// }}}

	// {{{ getTeam
	/**
	 * Return the column of a team and his association
	 *
	 * @access public
	 * @param  string  $teamId  id of the team
	 * @return array   information of the member if any
	 */
	function getTeam($teamId)
	{
		$fields = array('team_id', 'team_date', 'team_cmt', 'team_captain',
		      'team_name', 'team_stamp', 'cunt_id', 'cunt_name', 
		      'a2t_assoId as asso_id', 'team_url', 'team_noc',
		      'team_logo');
		$tables = array('a2t',
		      'teams LEFT JOIN accounts ON team_accountId = cunt_id');
		$where = "team_id = '$teamId'".
	" AND a2t_teamId = team_id".
	' AND team_eventId ='. utvars::getEventId();
		//	" AND team_accountId = cunt_id";
		$res = $this->_select($tables, $fields, $where);
		$data = $res->fetchRow(DB_FETCHMODE_ASSOC);

		$utd = new utdate();
		$utd->setIsoDateTime($data['team_date']);
		$data['team_date'] = $utd->getDate();

		$trans = array('team_name','team_stamp', 'team_cmt');
		$data = $this->_getTranslate('teams', $trans,
		$data['team_id'], $data);

		return $data;
	}
	// }}}

	// {{{ updateAsso
	/**
	 * Add or update a massociation into the database
	 *
	 * @access public
	 * @param  string  $info   column of the event
	 * @return mixed
	 */
	function updateAsso($infos)
	{
		if ($infos['asso_id'] == -1)
		{
	  $res = $this->_insert('assocs', $infos);
	  $infos['asso_id'] = $res;
		}

		$where = "asso_id=".$infos['asso_id'];
		$trans = array('asso_name','asso_stamp', 'asso_pseudo');
		$infos = $this->_updtTranslate('assos', $trans,
		$infos['asso_id'], $infos);

		$res = $this->_update('assocs', $infos, $where);
		return $infos['asso_id'];
	}
	// }}}


	// {{{ getTeamAccount
	/**
	 * Return the column of a member
	 *
	 * @access public
	 * @param  string  $memberId  id of the member
	 * @return array   information of the member if any
	 */
	function getTeamAccount($teamId)
	{
		$fields = array('team_id', 'team_accountId', 'team_name',
		      'cunt_id', 'cunt_name');
		$tables = array('teams LEFT JOIN accounts ON
           team_accountId = cunt_id');
		$where = "team_id = '$teamId'";
		$res = $this->_select($tables, $fields, $where);
		$regi =  $res->fetchRow(DB_FETCHMODE_ASSOC);
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

	// {{{ updateTeamAccount
	/**
	 * Add or update a team account into the database
	 *
	 * @access public
	 * @param  string  $info   column of the event
	 * @return mixed
	 */
	function updateTeamAccount($teamId, $accountId)
	{
		// Update registered team
		$fields = array();
		$fields['team_accountId'] = $accountId;
		$where = " team_id=$teamId";
		$res = $this->_update('teams', $fields, $where);
		return true;
	}
	// }}}


	// {{{ getPurchaseList
	/**
	 * Return the list of the reservation
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort associations
	 * @return array   array of users
	 */
	function getPurchaseList($teamId, $sort)
	{
		$fields = array('cmd_id', 'cmd_date', 'regi_longName','cmd_name',
		      'cmd_value', 'cmd_discount', 'cmd_payed', 
		      'cmd_value-cmd_discount-cmd_payed as cmd_du', 
		      'regi_id', 'item_name', 'item_rubrikId', 'cmd_cmt', 'regi_wo');
		$tables = array('commands',  'items', 'registration', 'members');
		$where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_teamId = $teamId".
	" AND regi_id = cmd_regiId".
	" AND regi_eventId = ".utvars::getEventId().
	" AND cmd_itemId = item_id".
	" AND regi_memberId = mber_id".
	" AND mber_id >= 0";
		if ($sort == 3)
		$order = "regi_longName, cmd_date, cmd_name";
		else if ($sort == 2)
		$order = "cmd_name, cmd_date, regi_longName";
		else
		$order = "cmd_date, cmd_name, regi_longName";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = "msgNoReservations";
	  return $err;
		}

		$utd = new utdate();
		$sum = 0;
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $utd->setIsoDateTime($entry['cmd_date']);
	  $entry['cmd_date'] = $utd->getDate();
	  if ($entry['item_rubrikId']== WBS_RUBRIK_HOTEL)
	  {
	  	$cell = array();
	  	$cell[] = array('value'=>"{$entry['cmd_name']}",
			      'action'=>array(KAF_UPLOAD, 'items', ITEM_HOTEL, $entry['item_name']));
	  	$entry['cmd_name'] = $cell;
	  }
	  $sum += $entry['cmd_value'];
	  if ($entry['regi_wo'] == WBS_YES)
	  $entry['class'] = 'classWo';
	  $rows[] = $entry;
		}
		$rows[] = array('', '', '', 'Total', $sum);
		return $rows;
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

		$fields = array('item_id, item_code', 'item_name');
		$tables = array('items');
		$where = "item_slt = 1";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		{
	  $err['errMsg'] = "msgNoSelectItems";
	  return $err;
		}

		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		$rows[$entry[0]] = $entry;
		return $rows;
	}
	// }}}


	// {{{ storeClubsTmp
	/**
	 * Add or update an association into the database
	 *
	 * @access public
	 * @param  string  $info   column of the event
	 * @return mixed
	 */
	function storeClubsTmp($elt)
	{
		$where = " 1 ";
		$res = $this->_delete('assocs_tmp', $where);
		// Update le numero de sequence
		$res = $this->_delete('assocs_tmp', " 1 ");
		$fields['id'] = "0";
		$where = "1";
		$res = $this->_update('assocs_tmp_seq', $fields, $where);
		foreach($elt as $infos){
			//     print_r($infos);
			//     echo "<hr>";
			//     if ($infos['asso_id'] != -1)
			//	{
			//	  $where = "asso_id=".$infos['asso_id'];
			//	  $res = $this->_update('assocs_tmp', $infos, $where);
			//	}
			//      else
			//	{
	  $res = $this->_insert('assocs_tmp', $infos);
	  $infos['asso_id'] = $res;
	  //	}
		}
		return $infos['asso_id'];
	}
	// }}}
	
	function getDraws($aRegiId)
	{
		$fields = array('i2p_regiId',  'pair_drawId', 'pair_disci',
			  'draw_stamp', 'count(p2m_id) as nbMatch');
	    $tables = array('i2p LEFT JOIN p2m ON p2m_pairId = i2p_pairId',
			  'pairs LEFT JOIN draws ON pair_drawId = draw_id');
	    $where  = "i2p_regiId = ".$aRegiId.
	    " AND i2p_pairId = pair_id".
	    " AND regi_type = ".WBS_PLAYER.
	    " GROUP BY pair_disci";
	  //" AND pair_drawId = draw_id";
	  $order = "pair_disci";
	  $tmp = '';
	  $res = $this->_select($tables, $fields, $where, $order);
	  if ($res->numRows())
	  {
	  	$trans = array('draw_stamp');
	  	while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  	{
	  		$entry = $this->_getTranslate('draws', $trans, $entry['pair_drawId'], $entry);
	  		if ($entry['draw_stamp'] == '' ||$entry['nbMatch'] == 0) $tmp .= ",--";
	  		else $tmp .= ",".$entry['draw_stamp'];
	  	}
	  	$rows[$id][5] = $tmp;
	  }
	  return $tmp;
		
	}

}
?>