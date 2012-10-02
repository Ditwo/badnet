<?php
/*****************************************************************************
 !   Module     : Teams
 !   File       : $Source: /cvsroot/aotb/badnet/src/teams/base_V.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.15 $
 !   Author     : G.CANTEGRIL
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:25 $
 !   Mailto     : cage@free.fr
 ******************************************************************************/
require_once "utils/utbase.php";
require_once "utils/utdate.php";


/**
 * Acces to the dababase for events for visitors
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class teamsBase_V extends utbase
{

	// {{{ properties
	// }}}

	// {{{ getTeams
	/**
	 * Return the list of the registered teams
	 *
	 * @access public
	 * @return array   array of users
	 */
	function getTeams()
	{
		// Select associations in the database
		$eventId = utvars::getEventId();
		$fields = array('team_id', 'team_name', 'team_stamp');
		$tables = array('teams', 'assocs', 'a2t');
		$where = "team_eventId = $eventId";
		$order = 'team_name,team_stamp';
		$res = $this->_select('teams', $fields, $where, $order, 'team');
		if (!$res->numRows())
		{
	  $infos['errMsg'] = 'msgNoTeams';
	  return $infos;
		}

		// Prepare a table with the assocation
		$rows = array();
		$trans = array('team_name');
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $entry = $this->_getTranslate('teams', $trans,
	  $entry['team_id'], $entry);
	  $rows[$entry['team_id']] = "{$entry['team_name']} ({$entry['team_stamp']})";
		}
		return $rows;
	}
	// }}}

	// {{{ getAssos
	/**
	 * Return the list of the registered association
	 *
	 * @access public
	 * @param  integer  $sort   criteria to sort assos
	 * @return array   array of users
	 */
	function getAssos($sort)
	{
		// Select associations in the database
		$eventId = utvars::getEventId();
		$fields = array('DISTINCT asso_id', 'asso_name', 'asso_pseudo',
		      'asso_url', 'asso_logo',);

		$tables = array('teams', 'assocs', 'a2t');
		$where = "team_eventId = $eventId ".
	" AND a2t_assoId = asso_id ".
	" AND a2t_teamId = team_id ";
		$order = abs($sort);
		if ($sort < 0)
		$order .= " DESC";
		$res = $this->_select($tables, $fields, $where, $order, 'team');
		if (!$res->numRows())
		{
	  $infos['errMsg'] = 'msgNoTeams';
	  return $infos;
		}

		// Prepare a table with the assocation
		$rows = array();
		$trans = array('asso_name','asso_stamp', 'asso_pseudo');
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $entry = $this->_getTranslate('assos', $trans,
	  $entry['asso_id'], $entry);
	  $rows[$entry['asso_id']] = $entry['asso_name'];
		}
		return $rows;
	}
	// }}}

	// {{{ getTeam
	/**
	 * Return the column of a team
	 *
	 * @access public
	 * @param  string  $teamId  id of the member
	 * @return array   information of the member if any
	 */
	function getTeam($teamId)
	{
		$fields = array('team_id', 'team_name', 'team_stamp', 'team_captain',
		      'team_cmt', 'asso_logo', 'asso_name', 'asso_url',
		      'team_logo', 'team_url', 'team_photo', 'asso_id');
		$tables = array('teams', 'assocs', 'a2t');
		$where = "team_id = '$teamId'".
	" AND a2t_assoId = asso_id ".
	" AND a2t_teamId = team_id ";
		$res = $this->_select($tables, $fields, $where, false, 'team');
		if (!$res->numRows())
		{
	  $err['errMsg'] = "msgNoAutorisation";
	  return $err;
		}
		$trans = array('team_name','team_stamp', 'team_cmt');
		$trans1 = array('asso_name');
		$entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$entry = $this->_getTranslate('teams', $trans,
		$entry['team_id'], $entry);
		$entry = $this->_getTranslate('assos', $trans1,
		$entry['asso_id'], $entry);
		return $entry;
	}
	// }}}

	// {{{ getAsso
	/**
	 * Return the column of a association
	 *
	 * @access public
	 * @param  string  $teamId  id of the member
	 * @return array   information of the member if any
	 */
	function getAsso($assoId)
	{
		$fields = array('asso_id', 'asso_name', 'asso_pseudo', 'asso_stamp',
		      'asso_logo', 'asso_noc', 'asso_url');
		$tables = array('assocs', 'a2t', 'teams');
		$where = "asso_id = '$assoId'".
	" AND a2t_assoId = asso_id ".
	" AND a2t_teamId = team_id ".
	" AND team_eventId =".utvars::getEventId();
		$res = $this->_select($tables, $fields, $where, false, 'team');
		if (!$res->numRows())
		{
	  $err['errMsg'] = "msgNoAutorisation";
	  return $err;
		}
		$trans = array('asso_name','asso_stamp', 'asso_pseudo');
		$entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$entry = $this->_getTranslate('assos', $trans,
		$entry['asso_id'], $entry);
		return $entry;
	}
	// }}}

	// {{{ getTies
	/**
	 * Return the ties of a team
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getTies($teamId)
	{
		// Firt select the ties of the team
		$eventId = utvars::getEventId();
		$fields = array('tie_id', "CONCAT_WS(' ', draw_name,rund_name) as name",
		      't2t_result', 't2t_scoreW', 't2t_scoreL', 'tie_schedule',
		      'tie_place', 'tie_step', 'rund_id');
		$tables = array('teams', 't2t', 'ties', 'rounds', 'draws');
		$where = "team_id = $teamId".
	" AND team_eventId = $eventId ".
	" AND team_id = t2t_teamId ".
	" AND t2t_tieId = tie_id ".
	" AND t2t_result !=".WBS_TIE_STEP.
	" AND tie_roundId = rund_id ".
	" AND rund_drawId = draw_id ";
		$order = "rund_name, tie_schedule, tie_step";
		$res = $this->_select($tables, $fields, $where, $order, 'draw');
		$rows = array();

		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoTies";
	  return $infos;
		}

		/* Now, for each tie, find the other team */
		$place="";

		$utd = new utdate();
		$ut = new utils();
		$fields = array('team_name', 'team_id', 'asso_logo', 'team_logo');
		$tables = array('teams', 't2t', 'assocs', 'a2t');
		$ou = "t2t_teamId = team_id".
	" AND team_id <> $teamId".
	" AND a2t_assoId = asso_id ".
	" AND a2t_teamId = team_id ";
		while ($curTie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $where = "$ou AND t2t_tieId =". $curTie['tie_id'];
	  $resTie = $this->_select($tables, $fields, $where);
	  if ($resTie->numRows())
	  $opp = $resTie->fetchRow(DB_FETCHMODE_ASSOC);
	  else
	  $opp = array('team_name'  => '--',
			 'team_id'    => 0, 
			 'asso_logo'  => '',
			 'team_logo'  => '');
	  $tie = array();
	  $tie[] = $curTie['tie_id'];
	  $tie[] = $curTie['name'];
	  $tie[] = $curTie['tie_place'];

	  $utd->setIsoDate($curTie['tie_schedule']);
	  $tie[]= $utd->getDateTime();
	  $tie[]= $curTie['tie_step'];
	  $tie[] = $opp['team_name'];
	  $tie[] = $ut->getLabel($curTie['t2t_result']);
	  $tie[] = $curTie['t2t_scoreW']."/".$curTie['t2t_scoreL'];
	  $tie[] = $opp['team_id'];
	  $tie[] = $curTie['rund_id'];
	  if ($opp['team_logo'] == '')
	  $tie[] = utimg::getPathFlag($opp['asso_logo']);
	  else
	  $tie[] = utimg::getPathFlag($opp['team_logo']);
	  $rows[] = $tie;
		}
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
		$ute = new utevent();

		$isSquash = $ut->getParam('issquash', false);
		
		// Select the players of the team
		$eventId = utvars::getEventId();
		$fields = array('regi_id', 'mber_sexe', 'mber_firstname', 'mber_licence',
		      'rkdf_label', 'rank_rank', 
		      'mber_secondname', 'mber_urlphoto', 'regi_wo', 'mber_ibfnumber');
		$tables = array('members', 'registration', 'ranks', 'rankdef');
		$where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_teamId = $teamId".
	" AND regi_memberId = mber_id ".
	" AND regi_eventId = $eventId".
	" AND regi_id = rank_regiId".
	" AND rank_rankdefId = rkdf_id".
	" AND mber_id >= 0";
		$order = "1, rank_disci";
		$res = $this->_select($tables, $fields, $where, $order, 'regi');
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoPlayers";
	  return $infos;
		}
		$id = -1;
		$rows = array();
		if (abs($sort) == 2) $crit = 'mber_sexe';
		if (abs($sort) == 3) $crit = 'mber_firstname';
		if (abs($sort) == 4) $crit = 'mber_licence';
		if (abs($sort) == 5) $crit = 'rkdf_label';
		if (abs($sort) == 6) $crit = 'rank_rank';
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($id != $entry['regi_id'])
			{
				if($id > 0)
				{
					if($entry['regi_wo'] == WBS_YES)
					{
						$class = 'classWo';
						$lines[] = array('value' => $entry['rdkf_label'],
					'class' => $class);
						$entry['rdkf_label'] = $lines;
					}
					$rowSort[] = $tmp[$crit];
					$rows[] = $tmp;
				}
				$tmp = $entry;
				$tmp['mber_sexe'] = $ut->getSmaLabel($tmp['mber_sexe']);
				$tmp['mber_firstname'] = "{$tmp['mber_secondname']} {$tmp['mber_firstname']}";
				$tmp['mber_urlphoto'] = utimg::getPathPhoto($tmp['mber_urlphoto']);
				if (trim($tmp['mber_licence']) == "") $tmp['mber_licence']=KAF_NONE;
				if (trim($tmp['mber_ibfnumber']) == "")	$tmp['mber_ibfnumber']=KAF_NONE;
				$id = $entry['regi_id'];
				if($entry['regi_wo'] == WBS_YES)
				{
					$class = 'classWo';
					$lines0[] = array('value' => $tmp['mber_sexe'],
				    'class' => $class);
					$tmp['mber_sexe'] = $lines0;
					
					$lines1[] = array('value' =>$tmp['mber_firstname'],
				    'class' => $class);
					$tmp['mber_firstname'] = $lines1;
					
					$lines3[] = array('value' =>$tmp['mber_licence'],
				    'class' => $class);
					$tmp['mber_licence'] = $lines3;
					
					$lines4[] = array('value' =>$tmp['rank_rank'],
				    'class' => $class);
					$tmp['rank_rank'] = $lines4;
				}
			}
			else if (!$isSquash)
			{
				$tmp['rkdf_label'] .= ",{$entry['rkdf_label']}";
			}
		}
		$rowSort[] = $tmp[$crit];
		$rows[] = $tmp;
		if ($sort < 0) array_multisort($rowSort, SORT_DESC,$rows);
		else array_multisort($rowSort, $rows);
		
		return $rows;
	}
	// }}}

	// {{{ getPairs
	/**
	 * Return the plairs of an association
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getPairs($assoId)
	{
		$ut = new utils();
		// Select the pairs  of the team
		$fields = array('i2p_pairId');
		$tables = array('registration', 'i2p', 'pairs', 'draws',
		      'teams', 'a2t', 'assocs');
		$where  = "regi_id = i2p_regiId".
	" AND i2p_pairId = pair_id".
	" AND pair_drawId = draw_id".
	" AND regi_teamId = team_id".
	" AND team_id = a2t_teamId".
	" AND a2t_assoId = asso_id".
	" AND asso_id = $assoId".
	" AND team_eventId = ".utvars::getEventId();
		$res = $this->_select($tables, $fields, $where, false, array('regi', 'team'));
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoPlayers";
	  return $infos;
		}
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$ids[] = $entry['i2p_pairId'];
		$pairsIds = implode(',', $ids);

		// Select the players of the pairs
		$fields = array('pair_id', 'regi_longName', 'draw_name','pair_intRank',
		      'pair_natRank', 'rkdf_label', 'pair_status', 'pair_wo', 'pair_order',
		      'regi_id', 'team_id', 'team_name', 'draw_disci', 'draw_id',
		      'asso_id', 'asso_pseudo'
		      );
		      $tables = array('pairs', 'i2p', 'registration', 'draws',
		      'members', 'teams', 'a2t', 'assocs', 'ranks', 'rankdef');
		      $where  = "regi_id = i2p_regiId".
	" AND i2p_pairId = pair_id".
	" AND draw_id=pair_drawId".
	" AND mber_id=regi_memberId".
	" AND regi_teamId=team_id".
	" AND pair_id IN ($pairsIds)".
	" AND team_id = a2t_teamId".
	" AND a2t_assoId = asso_id".
	" AND rank_regiId = regi_id".
	" AND rank_rankdefId = rkdf_id".
        " AND rank_disci = draw_disci";

		      $order = "pair_id, mber_sexe, regi_longName";
		      $res = $this->_select($tables, $fields, $where, $order, array('regi', 'team'));
		      if (!$res->numRows())
		      {
		      	$infos['errMsg'] = "msgNoPlayers";
		      	return $infos;
		      }

		      $uti = new utimg();
		      $utd = new utdate();

		      // Construction des paires: fusion des lignes d'une meme paire
		      $pairId = -1;
		      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		      {
		      	if ($pairId != $entry['pair_id'])
		      	{
		      		if ($pairId > -1)
		      		{
		      			$teamSort[] = $row['draw_disci'];
		      			$row['regi_longName'] = $cells['name'];
		      			$row['rkdf_label'] = $cells['level'];
		      			$rows[] = $row;
		      		}
		      		$cells['name']  = array();
		      		$cells['level']  = array();
		      		$row = $entry;
		      		if ($entry['pair_wo']) $class="classWo";
		      		else $class="";
		      		$row['pair_status'] = $ut->getSmaLabel($entry['pair_status']);
		      		if ((($entry['pair_status'] == WBS_PAIR_RESERVE) ||
		      		($entry['pair_status'] == WBS_PAIR_QUALIF))&&
		      		($entry['pair_order']>0))
		      		$row['pair_status'] = sprintf("%s %02d",$row['pair_status'],
		      		$entry['pair_order']);
		      		if($entry['pair_intRank'] <= 0)
		      		$row['pair_intRank'] = '';
		      		if($entry['pair_natRank'] <= 0)
		      		$row['pair_natRank'] = '';
		      		$pairId = $entry['pair_id'];
		      	}
		      	$lines = $cells['name'];
		      	$value = $entry['regi_longName'];
		      	if ($entry['asso_id'] != $assoId)
		      	$value .= ' ('.$entry['asso_pseudo'].')';
		      	$lines[] = array('value' => $value
		      	//,'logo' => $logo
		      	,'class' => $class
		      	,'action' => array(KAF_UPLOAD, 'resu',
		      	KID_SELECT, $entry['regi_id'])
		      	);
		      	$cells['name'] = $lines;

		      	$lines = $cells['level'];
		      	$value = $entry['rkdf_label'];
		      	$lines[] = array('value' => $value
		      	//,'logo' => $logo
		      	,'class' => $class
		      	);
		      	$cells['level'] = $lines;
		      }
		      $teamSort[] = $row['draw_disci'];
		      $row['regi_longName'] = $cells['name'];
		      $row['rkdf_label'] = $cells['level'];
		      $rows[] = $row;
		      array_multisort($teamSort, $rows);
		      return $rows;
	}
	// }}}

	// {{{ getAssoPlayers
	/**
	 * Return the players of an association
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getAssoPlayers($assoId, $sort)
	{
		$ut = new utils();
		// Select the players of the team
		$eventId = utvars::getEventId();
		$fields = array('regi_id',  'mber_sexe', 'regi_longName'
		);
		$tables = array('members', 'registration', 'a2t', 'teams');
		$where  = "a2t_assoId = $assoId".
	" AND a2t_teamId = team_id".
	" AND team_id = regi_teamId".
	" AND regi_memberId = mber_id ".
	" AND regi_eventId = $eventId".
	" AND regi_type = ".WBS_PLAYER.
	" AND mber_id >= 0";
		$res = $this->_select($tables, $fields, $where, false, array('regi', 'team'));
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoPlayers";
	  return $infos;
		}

		$rows = array();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $entry['mber_sexe'] = $ut->getSmaLabel($entry['mber_sexe']);
	  $entry['draws'] = '';
	  $entry['firstMatch'] = '';
	  $entry['convocation'] = '';
	  $entry['place'] = '';
	  $rows[$entry['regi_id']] = $entry;
		}

		$utev = new utEvent();
		$convocation = $utev->getConvoc();

		// Pour un tournoi individuel, recuperer les inscriptions
		// Et les heures de premier match
		if ($utev->getEventType($eventId) == WBS_EVENT_INDIVIDUAL)
		{
	  $fields = array('i2p_regiId',  'pair_drawId', 'pair_disci',
			  'draw_stamp', 'count(p2m_id) as nbMatch');
	  $tables = array('registration', 'i2p LEFT JOIN p2m ON p2m_pairId = i2p_pairId',
			  'pairs LEFT JOIN draws ON pair_drawId = draw_id',
			  'teams', 'a2t');
	  $where  = "regi_teamId = team_id".
	    " AND team_id = a2t_teamId".
	    " AND a2t_assoId = $assoId".
	    " AND regi_eventId = $eventId".
	    " AND regi_id = i2p_regiId".
	    " AND i2p_pairId = pair_id".
	    " AND regi_type = ".WBS_PLAYER.
	    " GROUP BY regi_id, pair_disci";
	  //" AND pair_drawId = draw_id";
	  $order = "1, pair_disci";
	  $res = $this->_select($tables, $fields, $where, $order, array('regi', 'team'));
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
	  			if ($id > 0) $rows[$id]['draws'] = $tmp;
	  			if ($entry['draw_stamp'] == '' ||
	  			$entry['nbMatch'] == 0)
	  			$tmp = '--';
	  			else
	  			$tmp = $entry['draw_stamp'];
	  			$id = $entry['i2p_regiId'];
	  		}
	  		else
	  		if ($entry['draw_stamp'] == '' ||
	  		$entry['nbMatch'] == 0)
	  		$tmp .= ",--";
	  		else
	  		$tmp .= ",".$entry['draw_stamp'];
	  	}
	  	$rows[$id]['draws'] = $tmp;
	  }

	  // Heure de premier match
	  $fields = array('regi_id', 'tie_schedule', 'tie_place');
	  $order = "1, tie_schedule";
	  if ($convocation['evnt_convoc'] == WBS_CONVOC_MATCH)
	  {
	  	$tables = array('registration', 'i2p', 'teams', 'a2t',
			      'p2m', 'matchs', 'ties');
	  	$where  = "regi_teamId = team_id".
		" AND team_id = a2t_teamId".
		" AND a2t_assoId = $assoId".
		" AND regi_eventId = $eventId".
		" AND regi_id = i2p_regiId".
		" AND i2p_pairId = p2m_pairId".
		" AND p2m_matchId = mtch_id".
		" AND tie_schedule != ''".
		" AND tie_schedule != '0000-00-00 00:00:00'".
		" AND tie_isBye = 0".
		" AND mtch_tieId = tie_id";
	  }
	  // Heure de debut de tableau
	  else
	  {
	  	$tables = array('registration', 'i2p', 'a2t', 'p2m',
			      'teams', 'pairs', 'rounds', 'ties');
	  	$where  = "regi_teamId = team_id".
		" AND team_id = a2t_teamId".
		" AND a2t_assoId = $assoId".
		" AND pair_id = p2m_pairId".
		" AND regi_eventId = $eventId".
		" AND regi_id = i2p_regiId".
		" AND i2p_pairId = pair_id".
		" AND pair_drawId = rund_drawId".
		" AND tie_schedule != ''".
		" AND tie_schedule != '0000-00-00 00:00:00'".
		" AND rund_id = tie_roundId";
	  }

	  $res = $this->_select($tables, $fields, $where,
	  $order, array('regi', 'team', 'tie'));
	  if ($res->numRows())
	  {
	  	$id = -1;
	  	$utd = new utdate();
	  	while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  	{
	  		if ($id != $entry['regi_id'])
	  		{
	  			$id = $entry['regi_id'];
	  			$utd->setIsoDateTime($entry['tie_schedule']);
	  			if ($convocation['evnt_convoc'] == WBS_CONVOC_MATCH)
	  			$rows[$id]['firstMatch'] = $utd->getDateTime();
	  			else
	  			$rows[$id]['firstMatch'] = '--';

	  			$utd->addMinute(-$convocation['evnt_delay']);
	  			$rows[$id]['convocation'] = $utd->getDateTime();
	  			if ($convocation['evnt_lieuconvoc']  == '')
	  			$rows[$id]['place'] = $entry['tie_place'];
	  			else
	  			$rows[$id]['place'] = $convocation['evnt_lieuconvoc'];
	  		}
	  	}
	  }

		}
		foreach($rows as $row)
		{
	  $values = array_values($row);
	  $sw[] = $values[abs($sort)-1];
		}
		if ($sort < 0)
		array_multisort($sw, SORT_DESC,$rows);
		else
		array_multisort($sw, $rows);

		return $rows;
	}
	// }}}

}

?>