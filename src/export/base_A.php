<?php
/*****************************************************************************
 !   Module     : Export
 !   File       : $Source: /cvsroot/aotb/badnet/src/export/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.26 $
 !   Author     : G.CANTEGRIL
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/01/27 22:41:47 $
 ******************************************************************************/

require_once "utils/utbase.php";

/**
 * Exportation au format dbf pour la FFba
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 *
 */

class exportBase extends utbase
{
	function updatei2p()
	{ 
		$eventId = utvars::getEventId();
		// mise a jour des infos de discipline dans pairs
		$cols = array('pair_disci' => WBS_SINGLE);
		$where = 'pair_disci < 3';
		$this->_update('pairs', $cols, $where);
		
		$cols = array('pair_disci' => WBS_MIXED);
		$where = 'pair_disci = 5';
		$this->_update('pairs', $cols, $where);
		
		$cols = array('pair_disci' => WBS_DOUBLE);
		$where = 'pair_disci < 5';
		$this->_update('pairs', $cols, $where);
		
		// mise a jour des infos de classement dans i2p si non renseignes
		$tables = array('registration', 'ranks', 'i2p', 'pairs');
		$where = 'rank_regiid=regi_id AND i2p_regiid=regi_id AND i2p_pairid=pair_id';
		$where .= ' AND pair_disci=rank_discipline AND regi_eventid='.$eventId;
		$fields = array('rank_rank', 'rank_average', 'rank_rankdefId', 'i2p_id');
		$i2ps = $this->_getRows($tables, $fields, $where);
		foreach($i2ps as $i2p)
		{
			$cols = array('i2p_classe' => $i2p['rank_rank'],
			'i2p_cppp' => $i2p['rank_average'],
			'i2p_rankdefid' => $i2p['rank_rankdefId']);
			$where = 'i2p_id=' . $i2p['i2p_id'];
			$this->_update('i2p', $cols, $where);
		}
	}

	/**
	 * Verifie s'il y des matches non terminé
	 */
	function checkMatch()
	{
		// compter les match
		$eventId = utvars::getEventId();
			
		$tables = array('matchs', 'ties', 'rounds', 'draws');
		$where = "draw_eventId = $eventId".
	" AND draw_id = rund_drawid".
	" AND rund_id = tie_roundid".
	" AND tie_id = mtch_tieid".
	" AND tie_isBye = 0".
      " AND mtch_status < ". WBS_MATCH_CLOSED;
		$fields = array('count(*)');
		//$fields = array('mtch_num', 'draw_name', 'rund_name');
		$nb =  $this->_selectFirst($tables, $fields, $where);
		return $nb;
	}


	// {{{ updatePair
	/**
	 * Update one entrie
	 *
	 * @access public
	 * @return array   array of match
	 */
	function updatePair(&$pair, $localId, $exportId, $eventId, $date)
	{
		// Search the pair
		if ($localId === -1)
		$pairId = $this->findLocalPairId($eventId, $exportId);
		else
		$pairId = $localId;

		// Not found, create a new one
		if ($pairId < 0)// && $pair['pair_cre'] >= $date)
		{
	  // Complete the extern_id field
	  $this->_mergeExternIds($pair['pair_externId'],
	  $exportId);

	  $pairId  = $this->_insert('pairs', $pair);
		}
		// update it
		else if ($pairId >= 0)
		{
	  // Complete the extern_id field
	  $this->_mergeExternIds($pair['pair_externId'],
	  $exportId);

	  // Update the match only if most recently modified
	  $where = "pair_id = $pairId".
	    " AND pair_updt <= '{$pair['pair_updt']}'";
	  $res = $this->_update('pairs', $pair, $where);

	  // Always update the extern_id field
	  $where = "pair_id = $pairId";
	  $fields = array('pair_externId' => $pair['pair_externId'],
			  'pair_updt' => $pair['pair_updt']);
	  $res = $this->_update('pairs', $fields, $where);
		}
		return $pairId;
	}
	// }}}

	// {{{ updateMatch
	/**
	 * Update one entrie
	 *
	 * @access public
	 * @return array   array of match
	 */
	function updateMatch(&$match, $date)
	{
		// Search the match
		$fields = array('mtch_id', 'mtch_externId');
		$where = "mtch_tieId=".$match['mtch_tieId'].
	" AND mtch_rank=".$match['mtch_rank'];
		$res = $this->_select('matchs', $fields, $where);
		$matchId = -1;
		if ($res->numRows())
		{
	  $buf = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $matchId = $buf['mtch_id'];
	  $externId = $buf['mtch_externId'];
		}

		// Les matchs importes et termines sont a valider
		if ($match['mtch_status'] > WBS_MATCH_ENDED)
		$match['mtch_status'] = WBS_MATCH_ENDED;

		// Not found, create a new one
		if ($matchId===-1 && $match['mtch_cre'] > $date)
		$matchId  = $this->_insert('matchs', $match);
		// update it
		else
		{
	  // Complete the extern_id field
	  $this->_mergeExternIds($match['mtch_externId'],
	  $externId);

	  // Update the match only if not closed
	  $where =  "mtch_id = $matchId".
	    " AND mtch_status < ".WBS_MATCH_CLOSED;
	  $res = $this->_update('matchs', $match, $where);

	  // Always update the extern_id field
	  $where = "mtch_id = $matchId";
	  $fields = array('mtch_externId' => $match['mtch_externId'],
			  'mtch_updt' => $match['mtch_updt']);
	  $res = $this->_update('matchs', $fields, $where);
		}
		return $matchId;
	}
	// }}}

	// {{{ updateTie
	/**
	 * Update one entrie
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function updateTie(&$tie, $date)
	{
		// Search the tie
		$fields = array('tie_id');
		$where = "tie_roundId=".$tie['tie_roundId'].
	" AND tie_posRound=".$tie['tie_posRound'];
		$tieId = $this->_selectFirst('ties', $fields, $where);

		// Not found, create a new one
		if (is_null($tieId) && $tie['tie_cre'] > $date)
		$tieId  = $this->_insert('ties', $tie);
		// update it
		else
		{
	  $where .= " AND tie_updt <= '{$tie['tie_updt']}'";
	  $res = $this->_update('ties', $tie, $where);
		}
		return $tieId;
	}
	// }}}

	// {{{ updateEnreg
	/**
	 * Update one round in the database
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function updateEnreg($tableDef, $columns, $options)
	{
		$table = $tableDef['table'];
		$prefx = $tableDef['prefx'];
		foreach($options as $key=>$value)
		$$key = $value;

		// The entrie is already in this database
		if ($search !== -1 || $localId !== -1)
		{
	  // Find it
	  if ($localId != -1)
	  $where = "{$prefx}_id = $localId";
	  else
	  {
	  	$where = "{$prefx}_externId LIKE '%{$search}%'";
	  	if (isset($limit))
	  	$where .= " AND $limit";
	  }
	  $fields = array("{$prefx}_id", "{$prefx}_externId");
	  $res = $this->_select($table, $fields, $where);
	  // Not found, create a new one only if it as been created
	  // after first importation
	  if (!$res->numRows())
	  {
	  	if ($columns["{$prefx}_cre"] > $date)
	  	$localId = -1;
	  	else
	  	return -1;
	  }
	  else
	  {
	  	$buf = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	$localId = $buf["{$prefx}_id"];
	  	$localExternId = $buf["{$prefx}_externId"];
	  }
		}
		// Create a new entrie
		if ($localId === -1)
		$localId  = $this->_insert($table, $columns);
		// Entrie found, update it
		else
		{
	  // Complete the extern_id field
	  $this->_mergeExternIds($columns["{$prefx}_externId"],
	  $localExternId);

	  // Update the entrie only if most recently modified
	  $where = "{$prefx}_id = $localId";
	  $where .= " AND {$prefx}_updt <= '{$columns["{$prefx}_updt"]}'";
	  $res = $this->_update($table, $columns, $where);

	  // Always update the extern_id field
	  $where = "{$prefx}_id = $localId";
	  $fields = array("{$prefx}_externId" => $columns["{$prefx}_externId"],
			  "{$prefx}_updt" => $columns["{$prefx}_updt"]);
	  $res = $this->_update($table, $fields, $where);
		}
		return $localId;
	}
	// }}}

	// {{{ updateMember
	/**
	 * Update one assoc in the database
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function updateMember(&$member, $memberId, $search)
	{
		// The member is already in this database
		if ($memberId !== -1 || $search !== -1)
		{
	  if ($memberId != -1)
	  $where = "mber_id = $memberId";
	  else
	  $where = "mber_externId LIKE '%{$search}%'";
	  $fields = array('mber_id', 'mber_externId');
	  $res = $this->_select('members', $fields, $where);
	  // Not found, we 'll search member with the same name
	  if (!$res->numRows())
	  $memberId = -1;
	  else
	  {
	  	$buf = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	$mmeberId = $buf['mber_id'];
	  	$localExternId = $buf['mber_externId'];
	  }
		}

		// Is there an member with the same name and gender
		if ($memberId === -1)
		{
	  $fields = array('mber_id', 'mber_externId');
	  $sname = addslashes($member['mber_secondname']);
	  $fname = addslashes($member['mber_firstname']);
	  $where = "mber_secondname = '$sname'".
	    " AND mber_firstname = '$fname'".
	    " AND mber_sexe =".$member['mber_sexe'];
	  $res = $this->_select('members', $fields, $where);
	  if ($res->numRows())
	  {
	  	$buf = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	$memberId = $buf['mber_id'];
	  	$localExternId = $buf['mber_externId'];
	  }
		}

		// Member found, update it
		if ($memberId !== -1)
		{
	  // Complete the extern_id field
	  $this->_mergeExternIds($member['mber_externId'], $localExternId);

	  // Update the member only if most recently modified
	  $where = "mber_id = $memberId";
	  $where .= " AND mber_updt <= '{$member['mber_updt']}'";
	  $this->_update('members', $member, $where);

	  // Always update the extern_id field
	  $where = "mber_id = $memberId";
	  $fields = array('mber_externId'=> $member['mber_externId'],
			  'mber_updt'=> $member['mber_updt']);
	  $res = $this->_update('members', $fields, $where);
		}
		// Create a new member
		else
		{
	  $memberId  = $this->_insert('members', $member);
		}
		return $memberId;
	}
	// }}}

	// {{{ updateAssoc
	/**
	 * Update one assoc in the database
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function updateAssoc(&$data, $assoId, $search)
	{
		// The association is already in this database
		if ($search !== -1 || $assoId !== -1)
		{
	  // Find it
	  if ($assoId != -1)
	  $where = "asso_id = $assoId";
	  else
	  $where = "asso_externId LIKE '%{$search}%'";
	  $fields = array('asso_id', 'asso_externId');
	  $res = $this->_select('assocs', $fields, $where);
	  // Not found, we 'll search assos with the same name
	  if (!$res->numRows())
	  $assoId = -1;
	  else
	  {
	  	$buf = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	$assoId = $buf['asso_id'];
	  	$localExternId = $buf['asso_externId'];
	  }
		}

		// Is there an association with the same name
		if ($assoId === -1)
		{
	  $fields = array('asso_id', 'asso_externId');
	  $name = addslashes($data['asso_name']);
	  $where = "asso_name = '$name'";
	  $res = $this->_select('assocs', $fields, $where);
	  if ($res->numRows())
	  {
	  	$buf = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	$assoId = $buf['asso_id'];
	  	$localExternId = $buf['asso_externId'];
	  }
		}

		// No association found, create a new one
		if ($assoId === -1)
		$assoId  = $this->_insert('assocs', $data);
		// Update it
		else
		{
	  // Complete the extern_id field
	  $this->_mergeExternIds($data['asso_externId'], $localExternId);

	  // Update the association only if most recently modified
	  $where = "asso_id = $assoId";
	  $where .= " AND asso_updt <= '{$data['asso_updt']}'";
	  $this->_update('assocs', $data, $where);

	  // Always update the extern_id field
	  $where = "asso_id = $assoId";
	  $fields = array('asso_externId'=> $data['asso_externId'],
			  'asso_updt'=> $data['asso_updt']);
	  $res = $this->_update('assocs', $fields, $where);
		}
		return $assoId;
	}
	// }}}

	// {{{ updateEvent
	/**
	 * Update the data of the event
	 *
	 * @access public
	 * @return array   array of match
	 */
	function updateEvent($event, $force=false)
	{
		$eventId = utvars::getEventId();
		
		// DBBN - Modif pour retours a la ligne dans les convocs
		$event["evnt_textconvoc"] = str_replace("<br />","\n",$event["evnt_textconvoc"]);
		
		$where = "evnt_id = $eventId";
		if (!$force)
		$where .= " AND evnt_updt <= '{$event['evnt_updt']}'";
		$res = $this->_update('events', $event, $where);
		return true;
	}
	// }}}


	// {{{ getAccounts
	/**
	 * Return the postits
	 *
	 * @access public
	 * @return array   array
	 */
	function getAccounts()
	{
		$eventId = utvars::getEventId();
		$fields  = array('cunt_name', 'cunt_code', 'cunt_status',
		       'cunt_dateClosed', 'cunt_id', 'cunt_uniId',
		       'cunt_cre', 'cunt_updt', 'cunt_cmt');
		$tables = array('accounts');
		$where = 	"cunt_eventId=".utvars::getEventId();
		$res = $this->_select($tables, $fields, $where);
		return $res;
	}
	// }}}

	// {{{ getAccountsTeams
	/**
	 * Return account of teams
	 *
	 * @access public
	 * @return array   array
	 */
	function getAccountsTeams()
	{
		$eventId = utvars::getEventId();
		$fields  = array('cunt_id', 'cunt_uniId', 'team_id', 'team_uniId');
		$tables = array('accounts', 'teams');
		$where = 	"cunt_eventId=".utvars::getEventId().
	" AND team_accountId = cunt_id";
		$res = $this->_select($tables, $fields, $where);
		return $res;
	}
	// }}}

	// {{{ getAccountsRegis
	/**
	 * Return the account of members
	 *
	 * @access public
	 * @return array   array
	 */
	function getAccountsRegis()
	{
		$eventId = utvars::getEventId();
		$fields  = array('cunt_id', 'cunt_uniId', 'regi_id', 'regi_uniId');
		$tables = array('accounts', 'registration');
		$where = 	"cunt_eventId=".utvars::getEventId().
	" AND regi_accountId = cunt_id";
		$res = $this->_select($tables, $fields, $where);
		return $res;
	}
	// }}}

	// {{{ getItems
	/**
	 * Return the postits
	 *
	 * @access public
	 * @return array   array
	 */
	function getItems()
	{
		$eventId = utvars::getEventId();
		$fields= array('item_id', 'item_name');
		$where = 	"item_eventId=".utvars::getEventId();
		$res = $this->_select('items', $fields, $where);
		$items = array();
		while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$items[] = $data;
		return $items;
	}
	// }}}

	// {{{ getCommands
	/**
	 * Return the postits
	 *
	 * @access public
	 * @return array   array
	 */
	function getCommands($itemId)
	{
		$fields = array('item_id', 'item_uniId', 'item_name',
		      'item_code', 'item_ref', 'item_rubrikId',
		      'item_value', 'item_cre', 'item_updt', 
		      'item_cmt', 'item_count', 'item_isFollowed',
		      'item_iscreditable',
		      'cmd_id', 'cmd_name',
		      'regi_uniId as cmd_regiUniId', 
		      'cunt_uniId as cmd_accountUniId',
		      'cmd_date', 'cmd_discount', 'cmd_payed',
		      'cmd_value', 'cmd_cre', 'cmd_updt',
		      'cmd_type', 'cmd_cmt'		      
		      );
		      $tables = array('items', 'accounts',
		      'commands LEFT JOIN registration ON regi_id = cmd_regiId',
		      );
		      $where = 	"item_id=cmd_itemId".
	" AND item_id = $itemId".
	" AND cunt_id = cmd_accountId";
		      $order = 'item_id';
		      $res = $this->_select($tables, $fields, $where, $order);
		      return $res;
	}
	// }}}

	// {{{ getPostits
	/**
	 * Return the postits
	 *
	 * @access public
	 * @return array   array
	 */
	function getPostits()
	{
		$eventId = utvars::getEventId();
		$fields  = array('*');
		$tables = array('postits');
		$where = 	"psit_eventId=".utvars::getEventId();
		$res = $this->_select($tables, $fields, $where);
		return $res;
	}
	// }}}

	// {{{ getP2m
	/**
	 * Return the relation betwwen pairs and match
	 *
	 * @access public
	 * @return array   array
	 */
	function getP2m($drawId)
	{
		$eventId = utvars::getEventId();
		$this->updateP2mPos($eventId);

		$fields  = array('mtch_id', 'p2m_pairId', 'p2m_result',
		       'p2m_posmatch', 'p2m_updt', 'p2m_id', 
		       'p2m_cre'
		       );
		       $fields[] = 'mtch_uniId';
		       $fields[] = 'pair_uniId as p2m_pairUniId';

		       $tables = array('rounds', 'ties', 'matchs', 'p2m', 'pairs');
		       $where = 	"rund_drawId = $drawId".
	" AND rund_id = tie_roundId".
	" AND tie_id = mtch_tieId".
	" AND mtch_id = p2m_matchId".
	" AND pair_id = p2m_pairId";
		       $order = "mtch_id, p2m_posmatch";

		       $res = $this->_select($tables, $fields, $where, $order);
		       return $res;
	}
	// }}}

	// {{{ getIndivPairs
	/**
	 * Return the list of pairs
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getIndivPairs($drawId, $version)
	{
		$eventId = utvars::getEventId();
		$fields  = array('pair_ibfNum', 'pair_NatRank', 'pair_intRank',
		       'pair_status', 'pair_id', 'pair_updt', 'pair_pbl', 
		       'pair_drawId','pair_disci','pair_natNum', 'pair_order',
		       'pair_wo','pair_datewo','pair_rankId','pair_average',
		       'pair_cre', 
		       'i2p_regiId', 'i2p_updt', 'i2p_cppp', 'i2p_classe',
		       'i2p_id', 'i2p_cre', 'i2p_rankdefid'
		       );
		       if ($version == '2.5r0')
		       {
		       	$fields[] = 'pair_state';
		       	$fields[] = 'pair_uniId';
		       	$fields[] = 'draw_uniId as pair_drawUniId';
		       	$fields[] = 'regi_uniId as i2p_regiUniId';
		       }
		       $tables = array( 'i2p', 'registration', 'pairs LEFT JOIN draws ON pair_drawId=draw_id');
		       $where = "regi_eventId = $eventId".
	" AND regi_id = i2p_regiId".
	" AND draw_id = $drawId".
	" AND i2p_pairId = pair_id";
		       $order = "pair_id, regi_id";

		       $res = $this->_select($tables, $fields, $where, $order);
		       return $res;
	}
	// }}}

	// {{{ getTeamPairs
	/**
	 * Return the list of pairs
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getTeamPairs($drawId, $version)
	{
		$eventId = utvars::getEventId();
		$fields  = array('pair_ibfNum', 'pair_NatRank', 'pair_intRank',
		       'pair_status', 'pair_id', 'pair_updt', 'pair_pbl', 
		       'pair_drawId','pair_disci','pair_natNum', 'pair_order',
		       'pair_wo','pair_datewo','pair_rankId','pair_average',
		       'pair_cre', 
		       'i2p_regiId', 'i2p_updt', 'i2p_cppp', 'i2p_classe',
		       'i2p_id', 'i2p_cre'
		       );
		       if ($version == '2.5r0')
		       {
		       	$fields[] = 'pair_state';
		       	$fields[] = 'pair_uniId';
		       	$fields[] = 'draw_uniId as pair_drawUniId';
		       }
		       $tables = array('pairs', 'i2p', 'registration',
		      'p2m', 'matchs', 'ties', 'rounds');
		       $where = "regi_eventId = $eventId".
	" AND regi_id = i2p_regiId".
	" AND i2p_pairId = pair_id".
	" AND pair_id=p2m_pairId".
	" AND p2m_matchId=mtch_id".
	" AND mtch_tieId=tie_id".
	" AND tie_roundId=rund_id".
	" AND rund_drawId=$drawId";
		       $order = "pair_id, regi_id";

		       $res = $this->_select($tables, $fields, $where, $order);
		       return $res;
	}
	// }}}

	// {{{ getT2t
	/**
	 * Return the list of t2t table
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getT2t()
	{
		$eventId = utvars::getEventId();
		$fields  = array('tie_id', 'tie_roundId', 'tie_posRound',
		       't2t_teamId', 
		       't2t_tieId', 't2t_posRound', 't2t_cre',
		       't2t_matchW','t2t_matchL','t2t_setW','t2t_setL',
		       't2t_pointW','t2t_pointL','t2t_scoreW','t2t_scoreL',
		       't2t_id', 't2t_updt', 't2t_pbl','t2t_result', 
		       't2t_posTie', 't2t_penalties'
		       );
		       $fields[] = 'rund_uniId as tie_roundUniId';
		       $fields[] = 'team_uniId as t2t_teamUniId';

		       $tables = array('t2t', 'draws', 'rounds', 'ties', 'teams');
		       $where = "draw_eventId = $eventId".
	" AND draw_id = rund_drawId".
	" AND rund_id = tie_roundId".
	" AND t2t_teamId = team_id".
	" AND tie_id = t2t_tieId";
		       $order = "tie_id, t2t_teamId";

		       $res = $this->_select($tables, $fields, $where, $order);
		       return $res;
	}
	// }}}

	// {{{ getTeamT2r
	/**
	 * Return the list of t2r table
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getTeamT2r()
	{
		$eventId = utvars::getEventId();
		$fields  = array('rund_id',
		       't2r_teamId', 't2r_pairId', 't2r_posRound', 't2r_id',
		       't2r_updt', 't2r_pbl', 't2r_cre',
		       't2r_rank', 't2r_tieW', 't2r_tieL', 't2r_tieE',
		't2r_tieEP','t2r_tieEM',
		       't2r_tieWO', 't2r_points', 't2r_penalties', 
		       't2r_tds', 't2r_status'
		       );
		       $fields[] = 'rund_uniId';
		       $fields[] = 'team_uniId as t2r_teamUniId';
		       $fields[] = '-1 as t2r_pairUniId';

		       $tables = array('t2r LEFT JOIN teams ON t2r_teamId=team_id', 'draws', 'rounds');
		       $where = "draw_eventId = $eventId".
	" AND draw_id = rund_drawId".
	" AND rund_id = t2r_roundId";
		       $order = "rund_id, t2r_posRound";

		       $res = $this->_select($tables, $fields, $where, $order);
		       return $res;
	}
	// }}}

	// {{{ getIndivT2r
	/**
	 * Return the list of t2r table
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getIndivT2r()
	{
		$eventId = utvars::getEventId();
		$fields  = array('rund_id',
		       't2r_teamId', 't2r_pairId', 't2r_posRound', 't2r_id',
		       't2r_updt', 't2r_pbl', 't2r_cre',
		       't2r_rank', 't2r_tieW', 't2r_tieL', 't2r_tieE',
		       't2r_tieWO', 't2r_points', 't2r_penalties', 
		       't2r_tds', 't2r_status'
		       );
		       $fields[] = 'rund_uniId';
		       $fields[] = '-1 as t2r_teamUniId';
		       $fields[] = 'pair_uniId as t2r_pairUniId';

		       $tables = array('t2r LEFT JOIN pairs ON t2r_pairId=pair_id', 'draws', 'rounds');
		       $where = "draw_eventId = $eventId".
	" AND draw_id = rund_drawId".
	" AND rund_id = t2r_roundId";
		       $order = "rund_id, t2r_posRound";

		       $res = $this->_select($tables, $fields, $where, $order);
		       return $res;
	}
	// }}}


	// {{{ getDraws
	/**
	 * Return the list of draws
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getDraws()
	{
		$eventId = utvars::getEventId();
		$fields  = array('draw_id', 'draw_name');
		$tables  = array('draws');
		$where = 	"draw_eventId = $eventId";
		$res = $this->_select($tables, $fields, $where);
		return $res;
	}
	// }}}

	// {{{ getRounds
	/**
	 * Return the list of draws
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getRounds()
	{
		$eventId = utvars::getEventId();
		$fields  = array('draw_id', 'draw_name', 'rund_id', 'rund_name');
		$tables  = array('draws', 'rounds');
		$where = 	"draw_eventId = $eventId".
          " AND rund_drawId = draw_id";
		$res = $this->_select($tables, $fields, $where);
		return $res;
	}
	// }}}

	// {{{ getMatchesIndiv
	/**
	 * Return the list of mathes for a draw
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getMatchesIndiv($drawId)
	{
		$eventId = utvars::getEventId();
		$fields  = array('draw_id', 'draw_name', 'draw_serial', 'draw_disci',
		       'draw_updt', 'draw_cmt', 'draw_pbl', 'draw_type',
		       'draw_stamp', 'draw_del', 'draw_cre', 'draw_discipline',
		       'rund_name', 'rund_size', 'rund_entries', 'rund_byes', 
		       'rund_qualPlace', 'rund_qual', 'rund_id', 'rund_updt',
		       'rund_cmt', 'rund_pbl', 'rund_type', 'rund_rankType',
		       'rund_tieWin', 'rund_tieEqual', 'rund_tieLoose', 
		       'rund_tieWO','rund_matchWin', 'rund_matchLoose',
			   'rund_tieEqualPlus', 'rund_tieEqualMinus', 'rund_tiematchnum', 
			   'rund_tiematchdecisif','rund_tieranktype', 
		       'rund_matchWO','rund_matchRtd', 'rund_stamp', 'rund_id',
		       'rund_cre', 'rund_rge', 'rund_group',
		       'tie_updt', 'tie_schedule', 'tie_place', 'tie_step',
		       'tie_court', 'tie_id', 'tie_posRound',
		       'tie_nbms', 'tie_nbws','tie_nbas','tie_nbmd',
		       'tie_nbwd','tie_nbad','tie_nbxd', 'tie_isBye',
		       'tie_cre', 'tie_pbl', 'tie_looserdrawid',
		       'mtch_num', 'mtch_begin', 'mtch_end', 'mtch_score',
		       'mtch_updt', 'mtch_status', 'mtch_court', 'mtch_order',
		       'mtch_umpireId', 'mtch_type', 'mtch_rank',
		       'mtch_serviceId', 'mtch_id', 'mtch_cre',
		       'mtch_tieId', 'mtch_discipline', 'mtch_disci','mtch_catage'
		);
		$fields[] = 'draw_catage';
		$fields[] = 'draw_uniId';
		$fields[] = 'mtch_uniId';
		$fields[] = 'rund_uniId';
		$fields[] = 'rkdf_label as draw_ranklabel';
		//      $tables = array('draws', 'rounds', 'ties LEFT JOIN matchs ON tie_id = mtch_tieId',
		//		      'rankdef');
		$tables = array('draws LEFT JOIN rounds ON draw_id = rund_drawId LEFT JOIN ties ON rund_id = tie_roundId  LEFT JOIN matchs ON tie_id = mtch_tieId',
      		      'rankdef');
		$where = 	"draw_eventId = $eventId".
	" AND draw_id = $drawId". 
		//	" AND draw_id = rund_drawId".
		//" AND rund_id = tie_roundId".
	" AND draw_rankdefId = rkdf_id";
		$order = "draw_id, rund_id, tie_posRound, tie_id, mtch_rank";

		$res = $this->_select($tables, $fields, $where, $order);
		return $res;

	}
	// }}}

	// {{{ getMatchesTeam
	/**
	 * Return the list of mathes for a draw
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getMatchesTeam($drawId)
	{
		$eventId = utvars::getEventId();
		$fields  = array('draw_id', 'draw_name', 'draw_serial', 'draw_disci',
		       'draw_updt', 'draw_cmt', 'draw_pbl', 'draw_type', 'draw_discipline',
		       'draw_stamp', 'draw_del', 'draw_cre',
		       'rund_name', 'rund_size', 'rund_entries', 'rund_byes', 
		       'rund_qualPlace', 'rund_qual', 'rund_id', 'rund_updt',
		       'rund_cmt', 'rund_pbl', 'rund_type', 'rund_rankType',
		       'rund_tieWin', 'rund_tieEqual', 'rund_tieLoose', 
		       'rund_tieWO','rund_matchWin', 'rund_matchLoose', 
		       'rund_matchWO','rund_matchRtd', 'rund_stamp', 'rund_id',
			   'rund_tieEqualPlus', 'rund_tieEqualMinus', 'rund_tiematchnum', 
			   'rund_tiematchdecisif','rund_tieranktype', 
		       'rund_cre', 'rund_rge', 'rund_group',
		       'rund_nbms', 'rund_nbws', 'rund_nbas', 'rund_nbmd', 
		       'rund_nbwd', 'rund_nbad', 'rund_nbxd', 
		       'tie_updt', 'tie_schedule', 'tie_place', 'tie_step',
		       'tie_court', 'tie_id', 'tie_posRound',
		       'tie_nbms', 'tie_nbws','tie_nbas','tie_nbmd',
		       'tie_nbwd','tie_nbad','tie_nbxd', 'tie_isBye',
		       'tie_cre', 'tie_pbl', 'tie_entrydate', 'tie_validdate', 'tie_controldate',
				'tie_entryid', 'tie_validid', 'tie_controlid', 'tie_looserdrawid',
		       'mtch_num', 'mtch_begin', 'mtch_end', 'mtch_score',
		       'mtch_updt', 'mtch_status', 'mtch_court', 'mtch_order',
		       'mtch_umpireId', 'mtch_type', 'mtch_rank',
		       'mtch_serviceId', 'mtch_id', 'mtch_cre', 
		       'mtch_tieId', 'mtch_discipline','mtch_disci','mtch_catage', 
		);
		$fields[] = 'draw_catage';
		$fields[] = 'draw_uniId';
		$fields[] = 'mtch_uniId';
		$fields[] = 'rund_uniId';
		$fields[] = '-1 as draw_ranklabel';
		//      $tables = array('draws', 'rounds', 'ties LEFT JOIN matchs ON tie_id = mtch_tieId',
		//		      'rankdef');
		$tables = array('draws LEFT JOIN rounds ON draw_id = rund_drawId LEFT JOIN ties ON rund_id = tie_roundId  LEFT JOIN matchs ON tie_id = mtch_tieId');
		$where = 	"draw_eventId = $eventId".
	" AND draw_id = $drawId";
		//	" AND draw_id = rund_drawId".
		//" AND rund_id = tie_roundId".
		//" AND draw_rankdefId = rkdf_id";
		$order = "draw_id, rund_id, tie_posRound, tie_id, mtch_rank";

		$res = $this->_select($tables, $fields, $where, $order);
		return $res;
	}
	// }}}

	// {{{ getRankdef
	/**
	 * Return the list ranks definition
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getRankDef()
	{
		$fields  = array('rkdf_id', 'rkdf_label');
		$tables = array('rankdef');
		$res = $this->_select($tables, $fields);
		while ( $rk = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$rkdf[$rk['rkdf_label']] = $rk['rkdf_id'];

		return $rkdf;
	}
	// }}}

	// {{{ getDbs
	/**
	 * Return the list of databse knowed by the event
	 *
	 * @access public
	 * @param integer  $eventId   event id
	 * @return array   array of match
	 */
	function getDbs($eventId)
	{
		$fields  = array('db_baseId', 'db_externEventId', 'db_date');
		$where = "db_eventId=$eventId";
		$res = $this->_select('database', $fields, $where);
		if ($res->numRows())
		while ( $db = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$dbs[] = $db;
		else
		$dbs = array();
		return $dbs;
	}
	// }}}

	// {{{ setDbs
	/**
	 * Register the list of databse knowed by the event
	 *
	 * @access public
	 * @param integer  $dbs   list of database id
	 * @param integer  $eventId   event id
	 * @return array   array of match
	 */
	function setDbs($dbs, $eventId)
	{
		foreach ($dbs as $db)
		{
	  if ($db['db_externEventId']==$eventId)
	  continue;
	  $where = "db_eventId=$eventId".
	    " AND db_baseId='{$db['db_baseId']}'".
	    " AND db_externEventId='{$db['db_externEventId']}'";
	  $res = $this->_selectfirst('database', 'db_id', $where);
	  if (!is_null($res))
	  $this->_update('database', $db, $where);
	  else
	  {
	  	$db['db_eventId'] = $eventId;
	  	$this->_insert('database', $db);
	  }
		}
	}
	// }}}

	// {{{ getPlayers
	/**
	 * Return the list of the entries
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getPlayers($version)
	{
		$eventId = utvars::getEventId();
		$fields  = array('mber_id', 'mber_firstname', 'mber_secondname',
		       'mber_sexe', 'mber_born', 'mber_ibfnumber', 
		       'mber_licence', 'mber_updt', 'mber_cmt', 'mber_pbl', 
		       'mber_del', 'mber_urlphoto',  
		       'mber_cre', 'mber_fedeid',
		       'regi_date', 'regi_longName', 
		       'regi_shortName', 'regi_type', 'regi_id', 'regi_updt', 
		       'regi_cmt', 'regi_pbl', 'regi_teamId', 'regi_status',
		       'regi_arrival', 'regi_departure', 'regi_arrcmt',
		       'regi_depcmt', 'regi_function', 'regi_noc', 
		        'regi_cre', 'regi_wo', 'regi_datewo',
		       'rank_rankdefid', 'rank_disci', 'rank_average',
		       'rank_updt', 'rank_isFede', 'rank_dateFede', 'rank_id',
		       'rkdf_label as rank_label', 'rank_cre', 'rank_discipline', 'rank_rank'
		       );
		       if ($version == '2.5r0')
		       {
		       	$fields[] = 'team_uniId as regi_teamUniId';
		       	$fields[] = 'mber_uniId';
		       	$fields[] = 'regi_uniId';
		       	$fields[] = 'regi_catage';
		       	$fields[] = 'regi_surclasse';
		       	$fields[] = 'regi_datesurclasse';
		       	$fields[] = 'regi_dateauto';
		       	$fields[] = 'regi_rest';
		       	$fields[] = 'regi_present';
		       	$fields[] = 'regi_wo';
		       	$fields[] = 'regi_delay';
		       }

		       $tables = array('members', 'registration', 'ranks', 'rankdef', 'teams');
		       $where = "mber_id = regi_memberId".
	" AND regi_id = rank_regiId".
	" AND regi_eventId = $eventId".
	" AND rkdf_id = rank_rankdefId".
	" AND team_id = regi_teamId".
	" AND regi_type =".WBS_PLAYER;
		       $order = "mber_id, regi_longName, regi_id, rank_disci";

		       $res = $this->_select($tables, $fields, $where, $order);
		       return $res;
	}
	// }}}

	// {{{ getRegis
	/**
	 * Return the list of the entries
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getRegis($version)
	{
		$eventId = utvars::getEventId();
		$fields  = array('mber_id', 'mber_firstname', 'mber_secondname',
		       'mber_sexe', 'mber_born', 'mber_ibfnumber', 
		       'mber_licence', 'mber_updt', 'mber_cmt', 'mber_pbl', 
		       'mber_del', 'mber_urlphoto',  
		       'mber_cre', 'mber_fedeid',
		       'regi_date', 'regi_longName', 
		       'regi_shortName', 'regi_type', 'regi_id', 'regi_updt', 
		       'regi_cmt', 'regi_pbl', 'regi_teamId', 'regi_status',
		       'regi_arrival', 'regi_departure', 'regi_arrcmt',
		       'regi_depcmt', 'regi_function', 'regi_noc', 
		        'regi_cre'
		        );
		        if ($version == '2.5r0')
		        {
		        	$fields[] = 'team_uniId as regi_teamUniId';
		        	$fields[] = 'mber_uniId';
		        	$fields[] = 'regi_uniId';
		        }
		        $tables = array('members', 'registration', 'teams');
		        $where = "mber_id = regi_memberId".
	" AND regi_eventId = $eventId".
	" AND team_id = regi_teamId".
	" AND regi_type !=".WBS_PLAYER;
		        $order = "mber_id, regi_longName, regi_id";

		        $res = $this->_select($tables, $fields, $where, $order);
		        return $res;
	}
	// }}}

	// {{{ getTeamsId
	/**
	 * Return the list of the teams
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getTeamsId()
	{
		$eventId = utvars::getEventId();
		$fields  = array('team_id', 'team_name');
		$where   = "team_eventId = $eventId";
		$order   = "team_name";
		$res = $this->_select('teams', $fields, $where, $order);
		return $res;
	}
	// }}}

	// {{{ getTeam
	/**
	 * Return the list of the players of a teams
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getTeam($teamId)
	{
		$eventId = utvars::getEventId();
		$fields  = array('asso_id', 'asso_name', 'asso_pseudo', 'asso_stamp',
		       'asso_type', 'asso_updt',
		       'asso_url', 'asso_number', 'asso_updt', 'asso_noc',
		       'asso_cre', 'asso_uniId', 'asso_fedeid',
		       'team_id', 'team_updt', 'team_name',
		       'team_captain', 'team_stamp', 'team_url', 'team_date',
		       'team_noc', 'team_pbl', 
		       'team_cre','team_uniId',
		       'asso_id as team_assoId',
		       'mber_id', 'mber_firstname', 'mber_secondname', 
		       'mber_sexe', 'mber_born', 'mber_ibfnumber', 
		       'mber_licence', 'mber_updt', 'mber_cmt', 'mber_pbl', 
		       'mber_del', 'mber_urlphoto', 'mber_uniId', 
		       'mber_cre', 'mber_fedeid',
		       'regi_date', 'regi_longname as regi_longName', 
		       'regi_shortname', 'regi_type', 'regi_id', 'regi_updt', 
		       'regi_cmt', 'regi_pbl', 'regi_teamId', 'regi_status',
		       'regi_arrival', 'regi_departure', 'regi_arrcmt',
		       'regi_depcmt', 'regi_function', 'regi_noc', 'regi_uniId',
		       'regi_cre', 'team_uniId as regi_teamUniId','regi_delay',
		       'regi_catage', 'regi_surclasse', 'regi_datesurclasse',
		       'regi_dateauto', 'regi_rest', 'regi_present', 'regi_wo',	 'regi_datewo',	       
		       'rank_rankdefid', 'rank_disci', 'rank_average',
		       'rank_updt', 'rank_isFede', 'rank_dateFede', 'rank_id',
		       'rkdf_label as rank_label', 'rank_cre', 'rank_discipline', 'rank_rank',
		       'pair_ibfNum', 'pair_NatRank', 'pair_intRank',
		       'pair_status', 'pair_id', 'pair_updt', 'pair_pbl', 
		       'pair_drawId','pair_disci','pair_natNum', 'pair_order',
		       'pair_wo','pair_datewo','pair_rankId','pair_average',
		       'pair_uniId', 'pair_cre', 'pair_state', 
		       'i2p_regiId', 'i2p_updt', 'i2p_cppp', 'i2p_classe',
		       'i2p_id', 'i2p_cre',
		);
		if (!utvars::isTeamEvent())
		{
	  $fields[] = 'draw_uniId as pair_drawUniId';
	  $tables = array(
			  '(((((((assocs INNER JOIN a2t ON asso_id = a2t_assoId) INNER JOIN teams ON a2t_teamId = team_id) LEFT JOIN registration ON team_id = regi_teamId) LEFT JOIN members ON regi_memberId = mber_id) LEFT JOIN ranks ON regi_id = rank_regiId LEFT JOIN rankdef ON rkdf_id = rank_rankdefId) LEFT JOIN i2p ON regi_id = i2p_regiId) LEFT JOIN pairs ON i2p_pairId = pair_id) LEFT JOIN draws ON pair_drawId = draw_id');
	  $where = " team_id = $teamId".
	    " AND (pair_disci = rank_discipline".
	    " OR pair_disci IS NULL)";
		}
		else
		{
	  // Bug !!!!
	  // pour les tournois par equipe pair_disci vaut 1,2,3,4 ou 5
	  // pour les tournois individuel pair_disci vaut 110,111,112
	  // C'est mal il faut homogeneiser
	  // En plus, lors de l'inscription d'un joueur dans un tournoi
	  // par equipe, 3 paires etaient creees (Simple double et mixte) avec
	  // 110,111 et 112.
	  // A partir de la v2.5, ces paires ne sont plus creees
	  $fields[] = '-1 as pair_drawUniId';
	  $tables = array(
			  '((((((assocs INNER JOIN a2t ON asso_id = a2t_assoId) 
			  INNER JOIN teams ON a2t_teamId = team_id) 
			  LEFT JOIN registration ON team_id = regi_teamId) 
			  LEFT JOIN members ON regi_memberId = mber_id) 
			  LEFT JOIN ranks ON regi_id = rank_regiId 
			  LEFT JOIN rankdef ON rkdf_id = rank_rankdefId) 
			  LEFT JOIN i2p ON regi_id = i2p_regiId) 
			  LEFT JOIN pairs ON ( i2p_pairId = pair_id
	  	           AND pair_disci = rank_discipline)'
	               );
	               $where = "team_id = $teamId";
		}
		$order = "regi_longName, regi_id, rank_disci";
		$res = $this->_select($tables, $fields, $where, $order);
		return $res;
	}
	// }}}

	// {{{ getTeams
	/**
	 * Return the list of the teams
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function getTeams($version)
	{
		$eventId = utvars::getEventId();
		$fields  = array('asso_id', 'asso_name', 'asso_pseudo', 'asso_stamp',
		       'asso_type', 'asso_updt', 
		       'asso_url', 'asso_number', 'asso_updt', 'asso_noc',
		       'asso_cre', 'asso_fedeid',
		       'team_id',  'team_updt', 'team_name',
		       'team_captain', 'team_stamp', 'team_url', 'team_date',
		       'team_logo', 'team_photo', 'team_noc', 'team_pbl', 
		       'team_cre',
		       'asso_id as team_assoId'
		       );
		       if ($version == '2.5r0')
		       {
		       	$fields[] = 'asso_uniId';
		       	$fields[] = 'team_uniId';
		       }
		       $tables = array('assocs', 'a2t', 'teams');
		       $where = "asso_id = a2t_assoId".
	" AND a2t_teamId = team_id".
	" AND team_eventId = $eventId";
		       $order = "asso_id, team_name";

		       $res = $this->_select($tables, $fields, $where, $order);
		       return $res;
	}
	// }}}

	// {{{ updateRelation
	/**
	 * Find the local reg for an import reg
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function updateRelation($table, $fields, $criteria, $updt=NULL)
	{
		foreach($criteria as $key=>$value)
		$list[]= "$key=$value";
		$where = implode( " AND ", $list);

		$columns = array("{$table}_id");
		$id = $this->_selectFirst($table, $columns, $where);
		if (is_null($id))
		{
	  foreach($criteria as $key=>$value)
	  $fields[$key]= $value;
	  $id = $this->_insert($table, $fields);
		}
		else
		{
	  if (!is_null($updt)) $where .= " AND $updt";
	  $res = $this->_update($table, $fields, $where);
		}
		return $id;
	}
	// }}}

	// {{{ findLocalPairId
	/**
	 * Find the local matchId for a match
	 *
	 * @access public
	 * @param string   $table  table
	 * @param string   $prefx  prefix of the table
	 * @param string   $externId extern id to find: <databse id>:<enrg Id>
	 * @return array   array of match
	 */
	function findLocalPairId($eventId, $externId)
	{
		// Verifier la clef externId
		$tmp = explode(':', $externId);
		if (count($tmp) != 3) return -3;

		// Search the key
		$fields = array("pair_id");
		$tables = array('pairs', 'i2p', 'registration');
		$where = "pair_externId LIKE '%{$externId}%'".
	" AND regi_eventId=$eventId".
	" AND regi_id=i2p_regiId".
	" AND i2p_pairId=pair_id";
		$pairId = $this->_selectFirst($tables, $fields, $where);
		if (is_null($pairId))
		$pairId = -1;
		return $pairId;
	}
	// }}}

	// {{{ findLocalMatchId
	/**
	 * Find the local matchId for a match
	 *
	 * @access public
	 * @param string   $table  table
	 * @param string   $prefx  prefix of the table
	 * @param string   $externId extern id to find: <databse id>:<enrg Id>
	 * @return array   array of match
	 */
	function findLocalMatchId($eventId, $externId)
	{
		// Verifier la clef externId
		$tmp = explode(':', $externId);
		if (count($tmp) != 3) return -3;

		// Search the key
		$fields = array("mtch_id");
		$tables = array('draws', 'rounds', 'ties', 'matchs');
		$where = "mtch_externId LIKE '%{$externId}%'".
	" AND draw_eventId=$eventId".
	" AND draw_id=rund_drawId".
	" AND rund_id=tie_roundId".
	" AND mtch_status <=". WBS_MATCH_ENDED.
	" AND tie_id=mtch_tieId";
		$matchId = $this->_selectFirst($tables, $fields, $where);
		if (is_null($matchId))
		$matchId = -1;
		return $matchId;
	}
	// }}}

	// {{{ findLocalTieId
	/**
	 * Find the local tieId for a tie
	 *
	 * @access public
	 * @param string   $table  table
	 * @param string   $prefx  prefix of the table
	 * @param string   $externId extern id to find: <databse id>:<enrg Id>
	 * @return array   array of match
	 */
	function findLocalTieId($eventId, $externId, $posRound)
	{
		// Verifier la clef externId
		$tmp = explode(':', $externId);
		if (count($tmp) != 3) return -3;

		// Search the key
		$fields = array("tie_id");
		$tables = array('draws', 'rounds', 'ties');
		$where = "rund_externId LIKE '%{$externId}%'".
	" AND draw_eventId=$eventId".
	" AND draw_id=rund_drawId".
	" AND rund_id=tie_roundId".
	" AND tie_posRound = $posRound";
		$tieId = $this->_selectFirst($tables, $fields, $where);
		if (is_null($tieId))
		$tieId = -1;
		return $tieId;
	}
	// }}}

	// {{{ findLocalRoundId
	/**
	 * Find the local roun_id for a round
	 *
	 * @access public
	 * @param string   $table  table
	 * @param string   $prefx  prefix of the table
	 * @param string   $externId extern id to find: <databse id>:<enrg Id>
	 * @return array   array of match
	 */
	function findLocalRoundId($eventId, $externId)
	{
		// Verifier la clef externId
		$tmp = explode(':', $externId);
		if (count($tmp) != 3) return -3;

		// Search the key
		$fields = array("rund_id");
		$tables = array('draws', 'rounds');
		$where = "rund_externId LIKE '%{$externId}%'".
	" AND draw_eventId=$eventId".
	" AND draw_id=rund_drawId";
		$roundId = $this->_selectFirst($tables, $fields, $where);
		if (is_null($roundId))
		$roundId = -1;
		return $roundId;
	}
	// }}}

	// {{{ findLocalId
	/**
	 * Find the local reg for an import reg
	 *
	 * @access public
	 * @param string   $table  table
	 * @param string   $prefx  prefix of the table
	 * @param string   $externId extern id to find: <databse id>:<enrg Id>
	 * @return array   array of match
	 */
	function findLocalId($table, $prefx, $externId, $search = false)
	{
		// Verifier la clef externId
		$tmp = explode(':', $externId);
		if (count($tmp) != 3) return -3;

		// Search the key
		$fields = array("{$prefx}_id");
		$where = "{$prefx}_externId LIKE '%{$externId}%'";
		if ($search != false)
		$where .= " AND $search";
		$localId = $this->_selectFirst($table, $fields, $where);
		if (is_null($localId))
		{
	  // If the key had to be here, means the reg was deleted
	  if (false)
	  $localId = -2;
	  else
	  $localId = -1;
		}
		return $localId;
	}
	// }}}

	// {{{ updateP2mPos
	/**
	 * Pour compatibilite : Mets a jour le champ p2m_posMatch
	 * lorsqu'il n'est pas renseigne
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function updateP2mPos($eventId)
	{
		$fields = array('p2m_id');
		$tables = array('registration', 'i2p', 'p2m');
		$where = "regi_eventId =".$eventId.
	" AND regi_id = i2p_regiId".
	" AND i2p_pairId = p2m_pairId".
	" AND p2m_posMatch = 0".
	" AND p2m_result >=".WBS_RES_WIN.
	" AND p2m_result <=".WBS_RES_WINWO;
		$res = $this->_select($tables, $fields, $where);
		if ($res->numRows())
		{
	  while ( $id = $res->fetchRow(DB_FETCHMODE_ORDERED))
	  $ids[] = $id[0];
	  $fields = array();
	  $fields['p2m_posMatch'] = WBS_PAIR_TOP;
	  $where = 'p2m_id IN ('.implode( ',', $ids).')';
	  $res = $this->_update('p2m', $fields, $where);
		}

		$fields = array('p2m_id');
		$tables = array('registration', 'i2p', 'p2m');
		$where = "regi_eventId =".$eventId.
	" AND regi_id = i2p_regiId".
	" AND i2p_pairId = p2m_pairId".
	" AND p2m_posMatch = 0".
	" AND p2m_result >=".WBS_RES_LOOSE;
		$res = $this->_select($tables, $fields, $where);
		if ($res->numRows())
		{
	  $ids = array();
	  while ( $id = $res->fetchRow(DB_FETCHMODE_ORDERED))
	  $ids[] = $id[0];
	  $fields = array();
	  $fields['p2m_posMatch'] = WBS_PAIR_BOTTOM;
	  $where = 'p2m_id IN ('.implode( ',', $ids).')';
	  $res = $this->_update('p2m', $fields, $where);
		}

		$fields = array('p2m_id', 'p2m_matchId');
		$tables = array('registration', 'i2p', 'p2m');
		$where = "regi_eventId =".$eventId.
	" AND regi_id = i2p_regiId".
	" AND i2p_pairId = p2m_pairId".
	" AND p2m_posMatch = 0".
	" AND p2m_result =".WBS_RES_NOPLAY;
		$order = 'p2m_matchId';
		$res = $this->_select($tables, $fields, $where, $order);
		if ($res->numRows())
		{
	  $matchId = -1;
	  while ( $id = $res->fetchRow(DB_FETCHMODE_ORDERED))
	  {
	  	if ($matchId == $id[1])
	  	$ids2[] = $id[0];
	  	else
	  	{
	  		$ids1[] = $id[0];
	  		$matchId = $id[1];
	  	}
	  }
	  $fields = array();
	  $fields['p2m_posMatch'] = WBS_PAIR_TOP;
	  $where = 'p2m_id IN ('.implode( ',', $ids1).')';
	  $res = $this->_update('p2m', $fields, $where);

	  $fields['p2m_posMatch'] = WBS_PAIR_BOTTOM;
	  $where = 'p2m_id IN ('.implode( ',', $ids2).')';
	  $res = $this->_update('p2m', $fields, $where);
		}
	}
	// }}}

	// {{{ _mergeExternIds
	/**
	 * Merge two list of extern Ids
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function _mergeExternIds(&$el1, $el2)
	{
		$externIds = explode(';', $el2);
		foreach($externIds as $externId)
		{
	  if (!preg_match("/.*{$externId}.*/", $el1))
	  $el1 .= $externId.";";
		}
		return $el1;
	}
	// }}}


	// {{{ getInfosUser
	/**
	 * Return the column of a user
	 *
	 * @access public
	 * @param  string  $userId   id of the user
	 * @return array   information of the user if any
	 */
	function getInfosUser($userId)
	{
		$fields = array('user_id', 'user_name', 'user_login', 'user_pass',
		      'user_email', 'user_type', 'user_lang', 'user_cre',
		      'user_lastvisit', 'user_cmt', 'user_nbcnx', 'user_updt');
		$tables[] = 'users';
		$where =  "user_id=$userId";
		$res = $this->_select($tables, $fields, $where);
		return $res->fetchRow(DB_FETCHMODE_ASSOC);
	}
	// }}}


	// {{{ searchDb
	/**
	 */
	function searchDb($db, $eventId)
	{
		$where =  "db_baseId='$db' AND db_externEventId=$eventId".
	" AND db_eventId=".utvars::getEventId();
		$res = $this->_selectFirst('database', 'db_date', $where);
		return $res;
	}
	// }}}

	// {{{ getDateRef
	/**
	 * Date de derniere importation du tournoi
	 */
	function getDateRef()
	{
		$where = "db_eventId=".utvars::getEventId().
	" AND db_baseId=-1";
		$res = $this->_selectFirst('database', 'db_date', $where);
		return $res;
	}
	// }}}

	// {{{ setDateRef
	/**
	 * Date de derniere importation du tournoi
	 */
	function setDateRef($date)
	{
		$fields = array('db_eventId' => utvars::getEventId(),
		      'db_baseId'  => -1,
		      'db_date'    => $date);
		$where = "db_eventId=".utvars::getEventId().
	" AND db_baseId=-1";
		$dbId = $this->_selectFirst('database', 'db_id', $where);
		if (is_null($dbId))
		$dbId = $this->_insert('database', $fields);
		else
		$this->_update('database', $fields, $where);
		return $dbId;
	}
	// }}}

	/**
	 * Mise a jour unidid des paires et des nouveaux inscrits
	 */
	function updateUniId()
	{
		$eventId = utvars::getEventId();
		$ut = new Utils();
		$localDBId = $ut->getParam('databaseId', -1);
		//UPDATE `badnet`.`bdnet_registration` SET `regi_uniId` = concat('1148751448:', regi_id, ';') WHERE `bdnet_registration`.`regi_uniid` >= '1233075444:450;';
		$fields = array('regi_uniid' => "#concat('$localDBId:', regi_id, ';')");
		$where = "regi_uniid >= '1233075444:450;'";
		$where = null;
		$this->_update('registration', $fields, $where, true);
		//UPDATE `badnet`.`bdnet_pairs` SET `pair_uniId` = concat('1148751448:', pair_id, ';');
	  	$fields = array('pair_uniid' => "#concat('$localDBId:', pair_id, ';')");
	  	$this->_update('pairs', $fields, null, true);

		/*		$eventId = Bn::getValue('event_id');
		 $q = new Bn_query('ranks');
		 $q->setValue('rank_discipline', OMATCH_DISCIPLINE_SINGLE);
		 $q->setWhere('(rank_disci='.OMATCH_DISCI_MS . ' OR rank_disci=' . OMATCH_DISCI_WS . ')');
		 $q->updateRow();*/
		$fields = array('rank_discipline' => 110);
		$where = "rank_disci=1 OR rank_disci=2 OR rank_disci=6";
		$this->_update('ranks', $fields, $where);

		/*		$q->setValue('rank_discipline', OMATCH_DISCIPLINE_DOUBLE);
		 $q->setWhere('(rank_disci='.OMATCH_DISCI_MD . ' OR rank_disci=' . OMATCH_DISCI_WD . ')');
		 $q->updateRow();*/
		$fields = array('rank_discipline' => 111);
		$where = "rank_disci=3 OR rank_disci = 4 OR rank_disci = 7";
		$this->_update('ranks', $fields, $where);

		/*$q->setValue('rank_discipline', OMATCH_DISCIPLINE_MIXED);
		 $q->setWhere('rank_disci='.OMATCH_DISCI_XD);
		 $q->updateRow();
		 */
		$fields = array('rank_discipline' => 112);
		$where = "rank_disci=5";
		$this->_update('ranks', $fields, $where);

		/*$q->setTables('matchs');
		 $q->setValue('mtch_disci', OMATCH_DISCIPLINE_SINGLE);
		 $q->setWhere('(mtch_discipline='.OMATCH_DISCI_MS . ' OR mtch_discipline=' . OMATCH_DISCI_WS . ')');
		 $q->updateRow();*/
		$fields = array('mtch_disci' => 110);
		$where = "mtch_discipline=1 OR mtch_discipline=2 OR mtch_discipline=6";
		$this->_update('matchs', $fields, $where);

		/*$q->setValue('mtch_disci', OMATCH_DISCIPLINE_DOUBLE);
		 $q->setWhere('(mtch_discipline='.OMATCH_DISCI_MD . ' OR mtch_discipline=' . OMATCH_DISCI_WD . ')');
		 $q->updateRow();*/
		$fields = array('mtch_disci' => 111);
		$where = "mtch_discipline=3 OR mtch_discipline=4 OR mtch_discipline=7";
		$this->_update('matchs', $fields, $where);

		/*
		 $q->setValue('mtch_disci', OMATCH_DISCIPLINE_MIXED);
		 $q->setWhere('mtch_discipline='.OMATCH_DISCI_XD);
		 $q->updateRow();
		 */
		$fields = array('mtch_disci' => 112);
		$where = "mtch_discipline=5";
		$this->_update('matchs', $fields, $where);
	}

}
?>