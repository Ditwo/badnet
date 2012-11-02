<?php
/*****************************************************************************
 !   Module     : Teams
 !   File       : $Source: /cvsroot/aotb/badnet/src/teams/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.34 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:25 $
 ******************************************************************************/
require_once "utils/utbase.php";

/**
 * Acces to the dababase for teams for administrator
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class teamsBase_A extends utbase
{

	// {{{ properties
	// }}}
	
		function getDraws($aRegiId)
	{
		$ut = new utils();
		$isSquash = $ut->getParam('issquash', false);
		$fields = array('i2p_regiId',  'pair_drawId', 'pair_disci',
			  'draw_stamp', 'count(p2m_id) as nbMatch');
	    $tables = array('i2p LEFT JOIN p2m ON p2m_pairId = i2p_pairId',
			  'pairs LEFT JOIN draws ON pair_drawId = draw_id');
	    $where  = "i2p_regiId = ".$aRegiId.
	    " AND i2p_pairId = pair_id".
	    " GROUP BY pair_disci";
	  //" AND pair_drawId = draw_id";
	  $order = "pair_disci";
	  $tmp = '';
	  $res = $this->_select($tables, $fields, $where, $order);
	  if ($res->numRows())
	  {
	  	$trans = array('draw_stamp');
	  	$glue= '';
	  	while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  	{
	  		$entry = $this->_getTranslate('draws', $trans, $entry['pair_drawId'], $entry);
	  		if (!$isSquash || empty($glue) )
	  		{
	  			if ($entry['draw_stamp'] == '' || $entry['nbMatch'] == 0) $tmp .= "$glue--";
	  			else $tmp .= $glue.$entry['draw_stamp'];
	  		}
	  		$glue = ',';
	  	}
	  }
	  return $tmp;
		
	}
	

	// {{{ getEntries
	/**
	 * Return the entries of a team
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getEntries($teamId)
	{
		$ut = new utils();
		// Select the players of the pairs
		$fields = array('mber_licence', 'regi_longName',
		      'mber_sexe',
		      'regi_catage', 'regi_surclasse', 'regi_id',
		      'rkdf_label', 'rank_disci',
		      'pair_id', 'pair_drawId', 'draw_stamp', 'regi_numcatage'
		      );
		      $tables = array('members', 'registration', 'ranks', 'rankdef',
		      'i2p', 'pairs LEFT JOIN draws ON pair_drawId=draw_id');
		      $where  = "regi_teamId = $teamId".
	 " AND mber_id=regi_memberId".
	 " AND rank_regiId = regi_id".
	 " AND rank_rankdefId = rkdf_id".
	 " AND regi_id = i2p_regiId".
	 " AND pair_id = i2p_pairId".
	 " AND pair_disci = rank_discipline";

		      $order = "regi_longName, rank_disci";
		      $res = $this->_select($tables, $fields, $where, $order);
		      if (!$res->numRows())
		      {
		      	$infos['errMsg'] = "msgNoPairs";
		      	return $infos;
		      }

		      $fields = array('team_id', 'team_name', 'regi_longName');
		      $tables = array('registration', 'teams', 'i2p');
		      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		      {
		      	$entry['mber_sexe'] = $ut->getSmaLabel($entry['mber_sexe']);
		      	$entry['regi_catage'] = $ut->getLabel($entry['regi_catage']);
		      	if ($entry['regi_numcatage'])
		      	$entry['regi_catage'] .= ' ' . $entry['regi_numcatage'];
		      	$entry['regi_surclasse'] = $ut->getLabel($entry['regi_surclasse']);
		      	// Chercher le partenaire
		      	if ($entry['pair_drawId'] != -1)
		      	{
		      		$where  = "i2p_pairId = {$entry['pair_id']}".
		" AND regi_id != {$entry['regi_id']}".
		" AND regi_teamId = team_id".
		" AND regi_id = i2p_regiId";
		      		$tmp = $this->_selectFirst($tables, $fields, $where);
		      		if (!is_null($tmp))
		      		{
		      			$entry['partner'] = $tmp['regi_longName'];
		      			if($tmp['team_id'] != $teamId)
		      			$entry['partner'] .= " ({$tmp['team_name']})";
		      		}
		      		else
		      		$entry['partner'] = "Recherche";
		      	}
		      	else
		      	{
		      		$entry['partner'] = "Non";
		      		$entry['draw_stamp'] = "Non";
		      	}
		      	$rows[] = $entry;
		      }

		      return $rows;
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
		$order = "rund_name, tie_schedule";
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

	// {{{ getAssos
	/**
	 * Renvoi la liste des associations inscrities
	 *
	 * @access public
	 * @return mixed
	 */
	function getAssos()
	{
		$fields = array('DISTINCT asso_id', 'asso_name');
		$where = 'asso_id = a2t_assoId'.
	' AND a2t_teamId = team_id'.
	' AND team_eventId='.utvars::getEventId();
		$tables = array('assocs', 'a2t', 'teams');
		$order = 'asso_name';
		$res = $this->_select($tables, $fields, $where, $order);
		$assos = array();
		while ($asso = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$assos[$asso['asso_id']] = $asso['asso_name'];

		return $assos;
	}
	// }}}


	// {{{ getPairs
	/**
	 * Return the plairs of a team
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getPairs($teamId, $sort)
	{
		$ut = new utils();
		// Select the pairs  of the team
		$fields = array('i2p_pairId');
		$tables = array('registration', 'i2p', 'pairs', 'draws');
		$where  = "regi_id = i2p_regiId".
	" AND i2p_pairId = pair_id".
	" AND pair_drawId = draw_id".
	" AND regi_teamId = $teamId";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoPairs";
	  return $infos;
		}
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$ids[] = $entry['i2p_pairId'];
		$pairsIds = implode(',', $ids);

		// Select the players of the pairs
		$fields = array('pair_id', 'regi_longName', 'draw_name', 'rkdf_label',
		      'i2p_cppp', 'i2p_classe', 'pair_intRank',
		      'pair_natRank', 'pair_status', 'pair_wo', 'pair_order',
		      'regi_id', 'team_id', 'team_name', 'draw_disci', 
		      'draw_id', 'mber_ibfnumber', 'pair_state'
		      );
		      $tables = array('pairs', 'i2p', 'registration', 'draws',
		      'members', 'teams', 'rankdef');
		      $where  = "regi_id = i2p_regiId".
	" AND i2p_pairId = pair_id".
	" AND draw_id=pair_drawId".
	" AND mber_id=regi_memberId".
	" AND regi_teamId=team_id".
	" AND pair_id IN ($pairsIds)".
	" AND i2p_rankdefId = rkdf_id";

		      $order = "pair_id, mber_sexe, regi_longName";
		      $res = $this->_select($tables, $fields, $where, $order);
		      if (!$res->numRows())
		      {
		      	$infos['errMsg'] = "msgNoPairs";
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
		      			$teamSort[] = "{$row['draw_disci']};{$row['pair_state']}";
		      			$row['regi_longName'] = $cells['name'];
		      			$row['rkdf_label'] = $cells['level'];
		      			$row['i2p_cppp'] = $average/$nbPlayers;
		      			if ($row['pair_state'] == WBS_PAIRSTATE_COM)
		      			$row['pair_stateLogo'] = $uti->getIcon(WBS_PAIRSTATE_COM);
		      			else
		      			$row['pair_stateLogo'] = "";
		      			$rows[] = $row;
		      		}
		      		$cells['name']  = array();
		      		$cells['level']  = array();
		      		$average = 0;
		      		$nbPlayers = 0;
		      		$row = $entry;
		      		if ($entry['pair_wo'])
					   $row['class'] = 'classWo';
		      		$drawName= array();
		      		$drawName[] = array('value' => $row['draw_name'],
				  'action' => array(KAF_UPLOAD, 'draws',
						    KID_SELECT,
						    $entry['draw_id']));
						    $row['draw_name'] = $drawName;

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
						    $tmp = array_values($row);
						    $tri[] = $tmp[abs($sort)-1];
		      	}
		      	$lines = $cells['name'];
		      	$value = $entry['regi_longName'];
		      	if ($entry['team_id'] != $teamId)
		      	$value .= ' ('.$entry['team_name'].')';
		      	$lines[] = array('value' => $value,
			   'ibfNum' => $entry['mber_ibfnumber'],
			   'level'  => $entry['rkdf_label']
		      	//,'logo' => $logo
		      	//,'class' => 'classTie',
		      	//,'action' => array(KAF_UPLOAD, 'resu',
		      	//	      KID_SELECT, $entry['regi_id'])
		      	);
		      	$cells['name'] = $lines;

		      	$lines = $cells['level'];
		      	$lines[] = array('value' => $entry['rkdf_label']);
		      	$cells['level'] = $lines;

		      	$average += $entry['i2p_cppp'];
		      	$nbPlayers++;
		      }
		      //$teamSort[] = $row['team_name'];
		      $teamSort[] = "{$row['draw_disci']};{$row['pair_state']}";
		      $row['regi_longName'] = $cells['name'];
		      $row['rkdf_label'] = $cells['level'];
		      $row['i2p_cppp'] = $average/$nbPlayers;
		      if ($row['pair_state'] == WBS_PAIRSTATE_COM)
		      $row['pair_stateLogo'] = $uti->getIcon(WBS_PAIRSTATE_COM);
		      else
		      $row['pair_stateLogo'] = "";
		      $rows[] = $row;
		      if ($sort > 0) array_multisort($teamSort, $tri, $rows);
		      else array_multisort($teamSort, $tri, SORT_DESC, $rows);
		      return $rows;
	}
	// }}}

	// {{{ updateTeam
	/**
	 * Add or update the team for an association into the database
	 *
	 * @access public
	 * @param  string  $info   column of the event
	 * @return mixed
	 */
	function updateTeam($infos)
	{
		// Team already exist
		if (isset($infos['team_id']))
		{
	  $trans = array('team_name','team_stamp', 'team_cmt');
	  $infos = $this->_updtTranslate('teams', $trans,
	  $infos['team_id'], $infos);

	  $where = "team_id=".$infos['team_id'];
	  $res = $this->_update('teams', $infos, $where);
		}
		return true;
	}
	// }}}

	// {{{ mergeTeams
	/**
	 * Merge several teams
	 *
	 * @access public
	 * @param  string  $info   column of the event
	 * @return mixed
	 */
	function mergeTeams($teamsId)
	{
		require_once "lib/client.php";
		$cl = new svc('http://www.badnet.org/badnet30/src/server', 'secure', 'inline');
		$fields['login']   = utvars::getUserLogin();
		$fields['pwd']     = utvars::getUserPwd();
		$fields['event_id'] = utvars::getEventId();

		$teamTo = array_shift($teamsId);
		foreach ($teamsId as $teamId)
		{
	  // Only team which are not in round or tie can be merge with another
	  $where = "t2t_teamId=$teamId";
	  if ($this->_selectFirst('t2t', 't2t_id', $where) != NULL)
	  continue;
	  $where = "t2r_teamId=$teamId";
	  if ($this->_selectFirst('t2r', 't2r_id', $where) != NULL)
	  continue;

	  // Update entries
	  $where = "regi_teamId=$teamId";
	  $cols = array('regi_teamId' => $teamTo);
	  $res = $this->_update('registration', $cols, $where);

	  // Mettre a jour les inscriptions en ligne
	  $where = "rght_themeId=$teamId".
	    " AND rght_theme = ".WBS_THEME_ASSOS;
	  $cols = array('rght_themeId' => $teamTo);
	  $res = $this->_update('rights', $cols, $where);

	  $fields['teamTo'] = $teamTo;
	  $fields['teamFrom'] = $teamId;
	  $res = $cl->call('mergeTeams', $fields);

	   
	  // Delete Team and relation with assoc
	  $where = "a2t_teamId=$teamId";
	  $res = $this->_delete('a2t', $where);

	  $where = "regi_id=$teamId";
	  $res = $this->_delete('teams', $where);
		}
		return true;
	}
	// }}}

	// {{{ mergePlayers
	/**
	 * Merge two players
	 *
	 * @access public
	 * @param  string  $info   column of the event
	 * @return mixed
	 */
	function mergePlayers($playersId)
	{
		$playerTo = array_shift($playersId);
		$playerFrom = array_shift($playersId);

		// Verifier que les joueurs ont le meme
		// numero de licence
		$tables = array('members', 'registration');
		$where = "regi_memberId=mber_id".
	" AND regi_id = $playerFrom";
		$licenceFrom = $this->_selectFirst($tables, 'mber_licence', $where);
		$where = "regi_memberId=mber_id".
	" AND regi_id = $playerTo";
		$licenceTo = $this->_selectFirst($tables, 'mber_licence', $where);
		if ($licenceFrom != $licenceTo)
		{
	  $err['errMsg'] = 'msgLicenceDiffer';
	  return $err;
		}
		//Les commandes
		$where = "cmd_regiId=$playerFrom";
		$fields = array('cmd_regiId' => $playerTo);
		$this->_update('commands', $fields, $where);

		//Les arbitres
		$where = "umpi_regiId=$playerFrom";
		$fields = array('umpi_regiId' => $playerTo);
		$this->_update('umpire', $fields, $where);

		$where = "mtch_serviceId=$playerFrom";
		$fields = array('mtch_serviceId' => $playerTo);
		$this->_update('matchs', $fields, $where);

		$where = "mtch_umpireId=$playerFrom";
		$fields = array('mtch_umpireId' => $playerTo);
		$this->_update('matchs', $fields, $where);

		//les paires (tournoi par equipe)
		if (utvars::isTeamEvent())
		{
	  $where = "i2p_regiId=$playerFrom";
	  $fields = array('i2p_regiId' => $playerTo);
	  $this->_update('i2p', $fields, $where);
		}
		else
		{
		}

		//les classements
		$where = "ranks_regiId=$playerFrom";
		$this->_delete('ranks', $where);

		//l'inscription
		$where = "regi_id=$playerFrom";
		$this->_delete('registration', $where);

		return true;
	}
	// }}}

	// {{{ getTeams
	/**
	 * Return the list of the teams
	 *
	 * @access public
	 * @param  integer  $sort   criteria to sort users
	 * @param  integer  $first  first event to retrieve
	 * @param  integer  $step   number of event to retrieve

	 * @return array   array of users
	 */
	function getTeams($sort, $assoId=false)
	{
		$eventId = utvars::getEventId();
		$fields = array('team_id', 'asso_pseudo', 'asso_name', 'team_stamp',
		      'team_date', 'count(regi_teamId)',
		      'team_del', 'team_pbl', 'asso_id', 'team_name');
		$tables = array('a2t', 'assocs',
		      'teams LEFT JOIN registration ON regi_teamId = team_id AND regi_memberId >0 ');
		$where = "team_eventId = $eventId".
	" AND team_id=a2t_teamId".
	" AND asso_id=a2t_assoId";
		if ($assoId!=false)
		$where .= " AND asso_id=$assoId";
		$where .= " GROUP BY team_id";
		if ($sort < 0)
		$order = abs($sort)." desc";
		else
		$order = $sort;
		$order .= ",team_name";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = 'msgNoTeams';
	  return $err;
		}
		$rows = array();
		//require_once "utils/utimg.php";
		$uti = new utimg();
		$utd = new utdate();
		$trans = array('team_name','team_stamp', 'asso_name', 'asso_pseudo');
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $entry['iconPbl'] = $uti->getPubliIcon($entry['team_del'],
	  $entry['team_pbl']);
	  $entry = $this->_getTranslate('teams', $trans,
	  $entry['team_id'], $entry);
	  $entry = $this->_getTranslate('assos', $trans,
	  $entry['asso_id'], $entry);
	  if ($entry['team_name'] != $entry['asso_name'])
	  $entry['asso_name'] .= "&nbsp;--&nbsp;{$entry['team_name']}";
	  $utd->setIsoDateTime($entry['team_date']);
	  $entry['team_date'] = $utd->getDate();
	  $rows[] = $entry;
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
		$fields = array('team_id', 'team_name', 'team_stamp', 'team_accountId',
		      'team_captain', 'team_cmt', 'a2t_assoId as asso_id', 
		      'asso_logo', 'team_del', 'team_pbl', 'team_logo', 
		      'team_url', 'team_photo', 'team_noc', 'asso_noc',
		      'asso_stamp', 'asso_pseudo', 'team_date', 'team_textconvoc');
		$tables = array('teams', 'a2t');
		$tables = array('teams', 'a2t', 'assocs');
		$where = "team_id = $teamId".
	" AND team_id=a2t_teamId".
	" AND asso_id=a2t_assoId";
		$res = $this->_select($tables, $fields, $where);

		$infos = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$trans = array('team_name','team_stamp', 'team_cmt');
		$infos = $this->_getTranslate('teams', $trans,
		$infos['team_id'], $infos);
		$utd = new utdate();
		$utd->setIsoDateTime($infos['team_date']);
		$infos['team_date'] = $utd->getDate();
		return $infos;
	}

	// }}}

	// {{{ delTeams
	/**
	 * Logical delete some teams
	 *
	 * @access public
	 * @param  arrays  $teams   id's of the associations to delete
	 * @param  integer $status    new status of the event
	 * @return mixed
	 */
	function delTeams($teams, $status)
	{
		$tables[] = 'teams';
		$fields[] = 'team_name';
		foreach ( $teams as $team => $id)
		{
	  $where = "team_id=$id ";
	  $res = $this->_select($tables, $fields, $where);
	  $theTeam = $res->fetchRow(DB_FETCHMODE_ORDERED);
	  if ($status == WBS_DATA_DELETE)
	  $name = "!".$theTeam[0]."!";
	  else
	  $name = $theTeam[0];
	   
	  $fields2['team_name'] = $name;
	  $fields2['team_del'] = $status;
	  $where = "team_id=$id";
	  $res = $this->_update('teams', $fields2, $where);
		}
		return true;

	}
	// }}}

	// {{{ eraseTeams
	/**
	 * Delete some teams
	 *
	 * @access public
	 * @param  arrays  $assos   id's of the associations to delete
	 * @return mixed
	 */
	function eraseTeams($ids)
	{
		foreach ($ids as $teamId)
		{
	  // Search if some members are in this team
	  $fields = array('regi_id');
	  $tables = array('registration');
	  $where = "regi_teamId=$teamId".
	    " AND regi_memberId > -1";
	  $res = $this->_select($tables, $fields, $where);
	  if ($res->numRows())
	  {
	  	$err['errMsg'] = 'msgTeamWithMember';
	  	return $err;
	  }

	  // Search the team account
	  $fields = array('team_accountId', 'team_name');
	  $tables = array('teams');
	  $where =  "team_id = $teamId";
	  $res = $this->_select($tables, $fields, $where);
	  if (!$res->numRows()) continue;
	  $team = $res->fetchRow(DB_FETCHMODE_ORDERED);
	  $cuntId = $team[0];

	  // Supress team
	  $where = "team_id=$teamId";
	  $res = $this->_delete('teams', $where);

	  // Supress relation with assoc
	  $where = "a2t_teamId=$teamId";
	  $res = $this->_delete('a2t', $where);

	  // Supress special WO players
	  $where = "regi_teamId=$teamId";
	  $res = $this->_delete('registration', $where);
	   
	  // Supress account if nobody on it
	  $fields = array('regi_id');
	  $tables = array('registration');
	  $where = "regi_accountId= $cuntId";
	  $res = $this->_select($tables, $fields, $where);
	  if ($res->numRows()) continue;

	  $where = "cunt_id= $cuntId";
	  $res = $this->_delete('accounts', $where);
		}
		return true;
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
		$convocation = $ute->getConvoc();
		$isSquash = $ut->getParam('issquash', false);
		// Select the players of the team
		$eventId = utvars::getEventId();
		$fields = array('regi_id',  'regi_longName', 'mber_sexe', 'regi_catage',
		      'rkdf_label', 'rank_rank', 'regi_status', 'mber_licence', 
		      'regi_noc', 'regi_noc', 'mber_id', 'regi_del', 
		      'regi_pbl', 'rank_isFede', 'rank_DateFede','asso_noc',
		      'mber_firstname', 'mber_secondname', 'regi_dateauto',
		      'regi_surclasse', 'regi_datesurclasse', 'regi_wo', 'regi_datewo',
			   'regi_numcatage');
		$tables = array('members', 'registration', 'ranks', 'rankdef',
		      'assocs', 'a2t', 'teams');
		$where  = "regi_del != ".WBS_DATA_DELETE.
	" AND asso_id = a2t_assoId".
	" AND a2t_teamId = team_id".
	" AND team_id = regi_teamId".
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
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
			if ($id != $entry[0])
			{
				if($id > 0)
				{
					if ($tmp[13] == 3 || ($isSquash && $tmp[13] == 1))
					{
						$utd->setIsoDate($tmp[14]);
						$tmp[4] .= " (Fede ".$utd->getDate().")";
					}
					$tmp[13] = $uti->getPubliIcon($tmp[11], $tmp[12]);
					if ($tmp[18] == '0000-00-00' || empty($tmp[18])) $tmp[18] = $uti->getIcon(WBS_NO);
					else $tmp[18] = '';
					$tmp[2] = $ut->getSmaLabel($tmp[2]);
					$tmp[3] = $ut->getLabel($tmp[3]);
					if ($tmp[23]) $tmp[3] .= ' ' . $tmp[23];
					if ($tmp[19] != WBS_SURCLASSE_NONE)	$tmp['3'] .= " (".$ut->getLabel($tmp[19]).")";

					//if (trim($tmp[4]) == "")$tmp[4]=KAF_NONE;
					$tmp[6] = $ut->getLabel(($tmp[6]==WBS_REGI_TITULAIRE) ? WBS_YES:WBS_NO);
					$tmp['10'] = '';
					$tmp['11'] = '';
					$tmp['12'] = '';

					$sw[] = $tmp[abs($sort)];
					$rows[$id] = $tmp;
				}
				if ($entry[8] == '') $entry[8]=$entry[15];
				$tmp = $entry;
				if ($entry[21]==WBS_YES) $tmp['class'] = 'classWo';
				$id = $entry[0];
			}
			else if (!$isSquash)
			{
				$tmp[4] .= ",{$entry[4]}";
				$tmp[13] += $entry[13];
			}
		}
		$tmp[2] = $ut->getSmaLabel($tmp[2]);
		$tmp[3] = $ut->getLabel($tmp[3]);
		if ($tmp[23]) $tmp[3] .= ' ' . $tmp[23];
		if ($tmp[19] != WBS_SURCLASSE_NONE)
		$tmp['3'] .= " (".$ut->getLabel($tmp[19]).")";
		if ($tmp[13] == 3 || ($isSquash && $tmp[13] == 1))
		{
	 		$utd->setIsoDate($tmp[14]);
	  		$tmp[4] .= " (Fede ".$utd->getDate().")";
		}
		$tmp[13] = $uti->getPubliIcon($tmp[11],
		$tmp[12]);
		if ($tmp[18] == '0000-00-00') $tmp[18] = $uti->getIcon(WBS_NO);
		else $tmp[18] = '';
		$tmp[6] = $ut->getLabel(($tmp[6]==WBS_REGI_TITULAIRE) ?	WBS_YES:WBS_NO);
		$tmp['9'] = '';
		$tmp['10'] = '';
		$tmp['11'] = '';
		$sw[] = $tmp[abs($sort)];
		$rows[$id] = $tmp;

		$utev = new utEvent();
		// Pour un tournoi individuel, recuperer les inscriptions
		// Et les heures de premier match
		if ($utev->getEventType($eventId) == WBS_EVENT_INDIVIDUAL)
		{
	  $fields = array('i2p_regiId',  'pair_drawId', 'pair_disci',
			  'draw_stamp', 'count(p2m_id) as nbMatch', 'i2p_classe');
	  $tables = array('registration', 'i2p LEFT JOIN p2m ON p2m_pairId = i2p_pairId',
			  'pairs LEFT JOIN draws ON pair_drawId = draw_id');
	  $where  = "regi_teamId = $teamId".
	    " AND regi_eventId = $eventId".
	    " AND regi_id = i2p_regiId".
	    " AND i2p_pairId = pair_id".
	    " AND regi_type = ".WBS_PLAYER.
	    " GROUP BY regi_id, pair_disci";
	  //" AND pair_drawId = draw_id";
	  $order = "1, pair_disci";
	  $res = $this->_select($tables, $fields, $where, $order);
	  if ($res->numRows())
	  {
	  	$id = -1;
	  	$trans = array('draw_stamp');
	  	while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  	{
	  		$entry = $this->_getTranslate('draws', $trans, $entry['pair_drawId'], $entry);
	  		if ($id != $entry['i2p_regiId'])
	  		{
	  			if ($id > 0)
	  			{
	  				$rows[$id][5] = $rank;
	  				$rows[$id][6] = $tmp;
	  			}
	  			if ($entry['draw_stamp'] == '' || $entry['nbMatch'] == 0) $tmp = '--';
	  			else $tmp = $entry['draw_stamp'];
	  			$rank = $entry['i2p_classe'];
	  			$id = $entry['i2p_regiId'];
	  		}
	  		else
	  		if (!$isSquash)
	  		{
	  		if ($entry['draw_stamp'] == '' || $entry['nbMatch'] == 0) $tmp .= ",--";
	  		else $tmp .= ",".$entry['draw_stamp'];
	  		}
	  	}
	  	$rows[$id][5] = $rank;
	  	$rows[$id][6] = $tmp;
	  }

	  // Heure de premier match
	  $fields = array('regi_id', 'tie_schedule', 'tie_place');
	  $order = "1, tie_schedule";
	  if ($convocation['evnt_convoc'] == WBS_CONVOC_MATCH)
	  {
	  	$tables = array('registration', 'i2p',
			      'p2m', 'matchs', 'ties');
	  	$where  = "regi_teamId = $teamId".
		" AND regi_eventId = $eventId".
		" AND regi_id = i2p_regiId".
		" AND i2p_pairId = p2m_pairId".
		" AND p2m_matchId = mtch_id".
		" AND tie_schedule != ''".
		" AND tie_schedule != '0000-00-00 00:00:00'".
	  	" AND mtch_status <".WBS_MATCH_LIVE.
	  	" AND mtch_tieId = tie_id";
	  }
	  // Heure de debut de tableau
	  else
	  {
	  	$tables = array('registration', 'i2p', 'p2m',
			      'pairs', 'rounds', 'ties', 'matchs');
	  	$where  = "regi_teamId = $teamId".
		" AND regi_eventId = $eventId".
		" AND regi_id = i2p_regiId".
		" AND pair_id = p2m_pairId".
		" AND i2p_pairId = pair_id".
		" AND pair_drawId = rund_drawId".
		" AND tie_schedule != ''".
		" AND tie_schedule != '0000-00-00 00:00:00'".
	  	" AND mtch_status <".WBS_MATCH_LIVE.
	  	" AND mtch_tieId = tie_id".
	  	" AND rund_id = tie_roundId";
	  }
	  $res = $this->_select($tables, $fields, $where, $order, 'tie');
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
				//DBBN - debut
				//$rows[$id]['9'] = $utd->getDateTime();
	  			$rows[$id]['9'] = $utd->getDateTime();
				//DBBN - fin
	  			else
	  			$rows[$id]['9'] = '--';
	  			$utd->addMinute(-$convocation['evnt_delay']);
				//DBBN - debut
				//$rows[$id]['10'] = $utd->getDateTime();
	  			$rows[$id]['10'] = $utd->getDateTime();
				//DBBN - fin
	  			if ($convocation['evnt_lieuconvoc'] == '')
	  			$rows[$id]['11'] = $entry['tie_place'];
	  			else
	  			$rows[$id]['11'] = $convocation['evnt_lieuconvoc'];
	  		}
	  	}
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

	// {{{ getContact
	/**
	 * Return the column of contact of an association
	 *
	 * @access public
	 * @param  string  $assoId  id of the member
	 * @return array   information of the member if any
	 */
	function getContact($ctacId)
	{
		$fields = array('ctac_value', 'ctac_contact');
		$where = "ctac_id=$ctacId";
		$res = $this->_select('contacts', $fields, $where);
		return $res->fetchRow(DB_FETCHMODE_ASSOC);
	}
	// }}}

	// {{{ getMatchPlayer
	/**
	 * Return the matchs of a player
	 *
	 * @return array   information of the member if any
	 */
	function getMatchPlayer($regiId)
	{
		$fields = array('mtch_id', 'rund_id', 'p2m_result');
		$tables = array('matchs', 'p2m', 'i2p',
		      'ties', 'rounds', 'draws'); 
		$where = "mtch_id=p2m_matchId".
	" AND p2m_pairId=i2p_pairId".
	" AND mtch_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND rund_drawId=draw_id".
	" AND i2p_regiId = $regiId".
	" AND mtch_status >".WBS_MATCH_LIVE;
		$order = "draw_disci, draw_id, rund_id, tie_posRound DESC";
		$res = $this->_select($tables, $fields, $where, $order);
		$matchs = array();
		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$matchs[] = $match;
		return $matchs;
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
	function getAssoPlayers($assoId)
	{
		$ut = new utils();
		// Select the players of the team
		$eventId = utvars::getEventId();
		$fields = array('regi_id',  'regi_longName');
		$tables = array('members', 'registration', 'a2t', 'teams');
		$where  = "a2t_assoId = $assoId".
	" AND a2t_teamId = team_id".
	" AND team_id = regi_teamId".
	" AND regi_memberId = mber_id ".
	" AND regi_eventId = $eventId".
	" AND regi_type = ".WBS_PLAYER.
	" AND mber_id >= 0";
		$order = 'regi_longName';
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoPlayers";
	  return $infos;
		}

		$rows = array();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$rows[] = $entry;
		return $rows;
	}
	// }}}


}

?>