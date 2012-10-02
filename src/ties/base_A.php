<?php
/*****************************************************************************
 !   Module     : Ties
 !   File       : $Source: /cvsroot/aotb/badnet/src/ties/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.9 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/06 07:59:33 $
 ******************************************************************************/
require_once "base.php";

/**
 * Acces to the dababase for ties
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class tiesBase_A extends tiesBase
{

	function razValid($aTieId)
	{
		// Select the matchs of the tie
		$fields = array('tie_validdate' => '0000-00-00 00:00:00',
		'tie_validid' => -1);
		$where = "tie_id = $aTieId";
		$this->_update("ties", $fields, $where);
		return false;
	}
	
	function razSaisie($aTieId)
	{
		// Select the matchs of the tie
		$fields = array('tie_validdate' => '0000-00-00 00:00:00',
		'tie_validid' => -1,
		'tie_entrydate' => '0000-00-00 00:00:00',
		'tie_entryid' => -1);
		$where = "tie_id = $aTieId";
		$this->_update("ties", $fields, $where);
		return false;
	}
	
	/**
	 * Return the match of a tie
	 *
	 * @access public
	 * @param  integer  $tieId  id of the tie
	 * @return array   information of the matchs if any
	 */
	function getTiesList($roundId, $tieId, $type)
	{
		$ut = new utils();
		// Compter le nombre de match a valider par rencontre
		$fields = array('tie_id', "count('mtch_id') as nb");
		$tables = array('ties', 'matchs');
		$where = "tie_roundId = $roundId".
	" AND tie_id = mtch_tieId".
	" AND tie_id != $tieId";
		if ($type == 0)
		$where .=" AND mtch_status =".WBS_MATCH_ENDED;
		if ($type == 1)
		$where .=" AND mtch_status <".WBS_MATCH_ENDED;
		$where .=	" GROUP BY tie_id";
		$res = $this->_select($tables, $fields, $where);

		// Trouver les equipes de chaque rencontre
		$ties = array();
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  if (!$tie['nb'] && $type!=2) continue;
	  if ($tie['nb'] && $type==2) continue;
	  $fields = array('team_name', 'team_stamp');
	  $tables = array('teams', 't2t');
	  $where = "t2t_tieId = {$tie['tie_id']}".
	    " AND t2t_teamId = team_id";
	  $order = 't2t_posTie';
	  $res2 = $this->_select($tables, $fields, $where, $order);
	  $name = '';
	  while ($team = $res2->fetchRow(DB_FETCHMODE_ASSOC))
	  $name .= "{$team['team_name']} ({$team['team_stamp']}) -- ";
	  $ties[$tie['tie_id']] = $name;
		}
		//print_r($ties);
		return $ties;
	}

	/**
	 * Return the match of a tie
	 *
	 * @access public
	 * @param  integer  $tieId  id of the tie
	 * @return array   information of the matchs if any
	 */
	function updateMatchStatus($tieId, $status)
	{
		$ut = new utils();
		// Select the matchs of the tie
		$fields = array('mtch_status' => $status);
		$where = "mtch_tieId = $tieId".
       " AND mtch_status >=".WBS_MATCH_ENDED;
		$this->_update("matchs", $fields, $where);

		// Recalculate the ranking
		$utt =  new utteam();
		$where = "tie_id=$tieId";
		$roundId = $this->_selectFirst('ties', 'tie_roundId', $where);
		$utt->updateGroupRank($roundId);

		// Mettre a jour date et auteur de la validation
		if ($status == WBS_MATCH_CLOSED)
		{
			$fields = array('tie_controldate' => date('Y-m-d H:i:s'),
      					'tie_controlid' => utvars::getUserId());
		}
		else
		{
			$fields = array('tie_controldate' => '0000-00-00 00:00:00',
      					'tie_controlid' => null);
		}
		$this->_update("ties", $fields, $where);


	}

	/**
	 * Return the match of a tie
	 *
	 * @access public
	 * @param  integer  $tieId  id of the tie
	 * @return array   information of the matchs if any
	 */
	function getMatchsForRes($tieId, $teamL, $teamR)
	{
		$ut = new utils();
		// Select the matchs of the tie
		$fields = array('mtch_id', 'mtch_discipline', 'mtch_score',
		      'mtch_order', 'mtch_status', 'mtch_disci');
		$tables[] = 'matchs';
		$where = "mtch_del != ".WBS_DATA_DELETE.
	" AND mtch_tieId = $tieId";
		$order = "mtch_rank";
		$res = $this->_select($tables, $fields, $where, $order);

		// Construc a table with the match
		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $match['mtch_regiId00'] = -10;
	  $match['mtch_regiId01'] = -10;
	  $match['mtch_regiId10'] = -10;
	  $match['mtch_regiId11'] = -10;
	  $match['mtch_result0'] = WBS_RES_NOPLAY;
	  $match['mtch_result1'] = WBS_RES_NOPLAY;
	  $match['mtch_teamId0'] = $teamL;
	  $match['mtch_teamId1'] = $teamR;
	  if ($match['mtch_discipline'] < 6)  $match['mtch_num'] = $ut->getSmaLabel($match['mtch_discipline']);
	  else $match['mtch_num'] = 'SP';
	  $match['mtch_num'] .= ' ' . $match['mtch_order'];
	  $matches[$match['mtch_id']] = $match;
		}

		// Select the players of the matchs of the tie
		$fields = array('mtch_id', 'regi_id', 'regi_teamId', 'i2p_pairId',
		      'rkdf_label', 'p2m_result');
		$tables = array('matchs', 'p2m', 'i2p', 'registration',
		      'ranks', 'rankdef', 'members');
		$where = " mtch_del != ".WBS_DATA_DELETE.
	" AND mtch_tieId = $tieId".
	" AND mtch_id = p2m_matchId".
	" AND p2m_pairId = i2p_pairId".
	" AND i2p_regiId = regi_id".
	" AND rank_regiId = regi_id".
	" AND rank_rankdefId = rkdf_id".
	" AND rank_discipline = mtch_disci".
	" AND regi_memberId = mber_id";
		$order = "mtch_id, regi_teamId, mber_sexe";
		$res = $this->_select($tables, $fields, $where, $order);

		// Coomplete the table with player's match
		$uts = new utscore();
		$pairId=-1;
		while ($player = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $match = $matches[$player['mtch_id']];
	  $uts->setScore($match['mtch_score']);
	  if ($player['regi_teamId'] == $teamL)
	  {
	  	if ($pairId != $player['i2p_pairId'])
	  	{
	  		$match['mtch_regiId00'] = $player['regi_id'];
	  		$match['mtch_result0'] = $player['p2m_result'];
	  		$pairId = $player['i2p_pairId'];
	  	}
	  	else
		  $match['mtch_regiId01' ] = $player['regi_id'];
	  }
	  else
	  {
	  	if ($pairId != $player['i2p_pairId'])
	  	{
	  		$match['mtch_regiId10'] = $player['regi_id'];
	  		$match['mtch_result1'] = $player['p2m_result'];
	  		$pairId = $player['i2p_pairId'];
	  	}
	  	else
		  $match['mtch_regiId11' ] = $player['regi_id'];

	  }
	  $matches[$player['mtch_id']] = $match;
		}

		return $matches;

	}

	// {{{ getRegiPlayers
	/**
	 * Return all registered players of a team
	 *
	 * @access public
	 * @param  integer $teamId  id of the team
	 * @param  integer $sexe    do you want man or woman
	 * @param  integer $disicpline sigle, double or mixed ?
	 * @return array   list of the players
	 */
	function getRegiPlayers($teamId, $discipline)
	{
		$ut = new utils();
			
		// Retrieve the players name
		$eventId = utvars::getEventId();
		$fields = array('regi_id', 'regi_longName', 'rkdf_label',
		      'rank_average', 'regi_catage', 'rank_rank');
		$tables = array('registration', 'members', 'ranks', 'rankdef');
		$where = "regi_teamId =". $teamId.
	" AND  mber_id = regi_memberId".
	" AND  rank_regiId = regi_id".
	" AND  rank_rankdefId = rkdf_id".
		//" AND  mber_sexe = $sexe".
	" AND regi_eventId = $eventId".
	" AND rank_disci = $discipline";
		$order = "rkdf_point DESC, rank_rank";
		$res = $this->_select($tables, $fields, $where, $order);
		$players["-10;-10"] = '----';
		while ( $player = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $pair =  $player['rkdf_label'] . ' - ';
	  $pair .=  sprintf('%05d', $player['rank_rank']) . ' - ';
	  $pair .=  $player['regi_longName']. ' - ';
	  $pair .=  $ut->getSmaLabel($player['regi_catage']) .  ' - '; 
	  $pair .=  sprintf('%.02f', $player['rank_average']);
	  $players["{$player['regi_id']};-10"] = $pair;
		}
		return $players;
	}
	// }}}

	// {{{ getRegiPairsDouble
	/**
	 * Return all possible pairs
	 *
	 * @access public
	 * @param  integer $teamId  id of the team
	 * @param  integer $sexe    do you want man or woman
	 * @param  integer $disicpline sigle, double or mixed ?
	 * @return array   list of the players
	 */
	function getRegiPairsDouble($teamId, $discipline)
	{
		$ut = new utils();
			
		// Retrieve the players name
		$eventId = utvars::getEventId();
		$fields = array('regi_id', 'regi_longName', 'rkdf_label',
		      'rank_average', 'rkdf_point', 'mber_id', 'regi_catage');
		$tables = array('registration', 'members', 'ranks', 'rankdef');
		$where = "regi_teamId =". $teamId.
	" AND  mber_id = regi_memberId".
	" AND  rank_regiId = regi_id".
	" AND  rank_rankdefId = rkdf_id".
	" AND regi_eventId = $eventId".
	" AND rank_disci = $discipline";
		$order = "regi_longName";
		$res = $this->_select($tables, $fields, $where, $order);
		$pairs["-10;-10"] = '----';
		if (!$res->numrows()) return $pairs;

		while ( $player = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$players[] = $player;
		$nbPlayers = count($players);
		$rank = array();
		$average = array();
		$pairsTab = array();
		for ($i=0; $i<$nbPlayers; $i++)
		{
	  $player1 = $players[$i];
	  for ($j=$i; $j<$nbPlayers; $j++)
	  {
	  	if ($i==$j && ($player1['mber_id'] > 0 OR
	  	$player1['mber_id'] == -10))
		  continue;

		  $player2 = $players[$j];
		  $pair['name'] = $player1['regi_longName'] . '('.
		  $ut->getSmaLabel($player1['regi_catage']) .'-'.
		  $player1['rkdf_label'].')-'.
		  $player2['regi_longName'] . '('.
		  $ut->getSmaLabel($player2['regi_catage']) .'-'.
		  $player2['rkdf_label'].')';
		  $pair['average'] =
		  ($player1['rank_average'] + $player2['rank_average'])/2;
		  $pair['point'] =
		  ($player1['rkdf_point'] + $player2['rkdf_point'])/2;
		  $pair['pairId'] = $player1['regi_id'] . ";" .
		  $player2['regi_id'];
		  $rank[] = $pair['point'];
		  $average[] = $pair['average'];
		  $pairsTab[] = $pair;
		  $name[] = $player1['regi_longName'];
	  }
		}
		//array_multisort($rank, SORT_DESC, $average, SORT_DESC, $pairsTab);
		if (isset($name))
		array_multisort($name, $pairsTab);
		foreach($pairsTab as $pair)
		{
	  $pairId = $pair['pairId'];
	  $value = $pair['name']." - ".$pair['average'];
	  $pairs[$pairId] = $value;
		}
		return $pairs;
	}
	// }}}

	// {{{ getRegiPairsMixed
	/**
	 * Return all possible pairs
	 *
	 * @access public
	 * @param  integer $teamId  id of the team
	 * @param  integer $sexe    do you want man or woman
	 * @param  integer $disicpline sigle, double or mixed ?
	 * @return array   list of the players
	 */
	function getRegiPairsMixed($teamId)
	{
		$ut = new utils();
			
		// Retrieve the men players name
		$discipline = WBS_MX;
		$sexe = WBS_MALE;
		$eventId = utvars::getEventId();
		$fields = array('regi_id', 'regi_longName', 'rkdf_label',
		      'rank_average', 'rkdf_point', 'regi_catage');
		$tables = array('registration', 'members', 'ranks', 'rankdef');
		$where = "regi_teamId =". $teamId.
	" AND  mber_id = regi_memberId".
	" AND  rank_regiId = regi_id".
	" AND  rank_rankdefId = rkdf_id".
	" AND regi_eventId = $eventId".
	" AND  mber_sexe = $sexe".
	" AND rank_disci = $discipline";
		$order = "regi_longName";
		$res = $this->_select($tables, $fields, $where, $order);
		$men = array();
		while ( $man = $res->fetchRow(DB_FETCHMODE_ASSOC)) $men[] = $man;

		// Retrieve the women players name
		$sexe = WBS_FEMALE;
		$eventId = utvars::getEventId();
		$fields = array('regi_id', 'regi_longName', 'rkdf_label',
		      'rank_average', 'rkdf_point', 'regi_catage');
		$tables = array('registration', 'members', 'ranks', 'rankdef');
		$where = "regi_teamId =". $teamId.
	" AND  mber_id = regi_memberId".
	" AND  rank_regiId = regi_id".
	" AND  rank_rankdefId = rkdf_id".
	" AND regi_eventId = $eventId".
	" AND  mber_sexe = $sexe".
	" AND rank_disci = $discipline";
		$order = "regi_longName";
		$res = $this->_select($tables, $fields, $where, $order);

		$women = array();
		while ( $woman = $res->fetchRow(DB_FETCHMODE_ASSOC)) $women[] = $woman;
		$pairs["-10;-10"] = '----';

		//if (!$res->numrows()) return $pairs;

		$nbMen = count($men);
		$nbWomen = count($women);
		$names = array();
		$pairsTab = array();
		for ($i=0; $i<$nbMen; $i++)
		{
	  $man  = $men[$i];
	  for ($j=0; $j<$nbWomen; $j++)
	  {
	  	$woman  = $women[$j];
	  	$pair['name'] = $man['regi_longName'] . '('.
	  	$ut->getSmaLabel($man['regi_catage']) .'-'.
	  	$man['rkdf_label'].')-'.
	  	$woman['regi_longName'] . '('.
	  	$ut->getSmaLabel($woman['regi_catage']) .'-'.
	  	$woman['rkdf_label'].')';
	  	$pair['average'] =
	  	($man['rank_average'] + $woman['rank_average'])/2;
	  	$pair['point'] =
	  	($man['rkdf_point'] + $woman['rkdf_point'])/2;
	  	$pair['pairId'] = $man['regi_id'] . ";" . $woman['regi_id'];
	  	$rank[] = $pair['point'];
	  	$average[] = $pair['average'];
	  	$pairsTab[] = $pair;
	  	$names[] = $man['regi_longName'];
	  }
		}

		//array_multisort($rank, SORT_DESC, $average, SORT_DESC, $pairsTab);
		array_multisort($names, $pairsTab);

		foreach($pairsTab as $pair)
		{
	  $pairId = $pair['pairId'];
	  $value = $pair['name']." - ".$pair['average'];
	  $pairs[$pairId] = $value;
		}

		return $pairs;
	}
	// }}}

	// {{{ getPenalties
	/**
	 * Return all penalties and name of team
	 *
	 * @access public
	 * @param  integer $teamId  id of the team
	 * @param  integer $sexe    do you want man or woman
	 * @param  integer $disicpline sigle, double or mixed ?
	 * @return array   list of the players
	 */
	function getPenalties($t2rId)
	{
		$fields = array('t2r_penalties', 'team_name');
		$tables = array('teams', 't2r');
		$where = "t2r_id =$t2rId".
	" AND  t2r_teamId = team_id";
		$res = $this->_select($tables, $fields, $where);
		return  $res->fetchRow(DB_FETCHMODE_ASSOC);
	}
	// }}}

	// {{{ getMatch
	/**
	 * Return a match for an team event
	 *
	 * @access public
	 * @param  string  $matchId  id of the match
	 * @return array   information of the match if any
	 */
	function getMatch($matchId)
	{
		// Retrieve informations of the match
		$fields[] = 'mtch_discipline';
		$fields[] = 'mtch_disci';
		//      $query .= " mtch_score, mtch_cmt, mtch_order, mtch_begin, mtch_end";
		$tables[] = 'matchs';
		$where = "mtch_id = '$matchId'";
		$res = $this->_select($tables, $fields, $where);
		$match = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $match;
	}
	// }}}

	// {{{ isRegiWo
	/**
	 * Return true if the registered playe is WO player
	 *
	 * @access public
	 * @param  integer $regi  Registration index of theplayer
	 * @return boolean
	 */
	function isRegiWO($regiId)
	{
		$fields[] = 'regi_memberId';
		$tables[] = 'registration';
		$where = "regi_Id = '$regiId'";
		$res = $this->_select($tables, $fields, $where);
		$player = $res->fetchRow(DB_FETCHMODE_ORDERED);
		return ($player[0] < 0 and $player[0]!= -10);
	}
	// }}}

	// {{{ updateMatchTeam
	/**
	 * Add or update a match of a team event into the database
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function updateMatchTeam($infos)
	{
		$uts = new utscore();
		$uts->setScore($infos['mtch_score']);

		// Updating the match
		$fields['mtch_status'] = $infos['mtch_status'];
		$fields['mtch_score'] = $infos['mtch_score'];
		$where = "mtch_id=".$infos['mtch_id'];
		$res = $this->_update('matchs', $fields, $where);
			
		// Updating relation between pairs and match for winner
		$winPairId = $this->_getPairId($infos['mtch_winId0'], $infos['mtch_winId1'], $infos['mtch_disci']);
		if (is_array($winPairId)) return $winPairId;
echo $infos['mtch_winId0'] .";" . $infos['mtch_winId1'] . ";" . "$winPairId<br>";
		$fields = array('p2m_id');
		$tables = array('p2m');
		$where = "p2m_matchId='".$infos['mtch_id']."'";
		$p2ms = $this->_select($tables, $fields, $where);

		$p2m = $p2ms->fetchRow(DB_FETCHMODE_ORDERED);
		$fields = array();
		if ($infos["mtch_status"] >= WBS_MATCH_ENDED)
		if ($uts->isWO()) $fields['p2m_result'] = WBS_RES_WINWO;
		else if ($uts->isAbort()) $fields['p2m_result'] = WBS_RES_WINAB;
		else $fields['p2m_result'] = WBS_RES_WIN;
		else $fields['p2m_result'] = WBS_RES_NOPLAY;
		$fields['p2m_pairId'] = $winPairId;
		if ($p2m)
		{
	  $p2mId = $p2m[0];
	  $where = "p2m_id = $p2mId";
	  $res = $this->_update('p2m', $fields, $where);
		}
		else
		{
	  $fields['p2m_matchId'] = $infos['mtch_id'];
	  $fields['p2m_posMatch'] = WBS_PAIR_TOP;
	  $res = $this->_insert('p2m', $fields);
	  $p2mId = $res;
		}
		// Relation between match and pair for looser
		$loosPairId = $this->_getPairId($infos['mtch_loosId0'],
		$infos['mtch_loosId1'],
		$infos['mtch_disci']);
		if (is_array($loosPairId)) return $loosPairId;

		$fields  = array('p2m_id');
		$tables = array('p2m');
		$where = "p2m_matchId='".$infos['mtch_id']."'".
	" AND p2m_id != $p2mId";
		$p2ms = $this->_select($tables, $fields, $where);

		$p2m = $p2ms->fetchRow(DB_FETCHMODE_ORDERED);
		$fields = array();
		if ($infos["mtch_status"] >= WBS_MATCH_ENDED)
		if ($uts->isWO())
		$fields['p2m_result'] = WBS_RES_LOOSEWO;
		else if ($uts->isAbort())
		$fields['p2m_result'] = WBS_RES_LOOSEAB;
		else
		$fields['p2m_result'] = WBS_RES_LOOSE;
		else
		$fields['p2m_result'] = WBS_RES_NOPLAY;
		$fields['p2m_pairId'] = $loosPairId;
		if ($p2m)
		{
	  $p2mId = $p2m[0];
	  $where = "p2m_id = $p2mId";
	  $res = $this->_update('p2m', $fields, $where);
		}
		else
		{
	  $fields['p2m_matchId'] = $infos['mtch_id'];
	  $fields['p2m_posMatch'] = WBS_PAIR_BOTTOM;
	  $res = $this->_insert('p2m', $fields);
	  $p2mId = $res;
		}
		return true;

	}
	// }}}

	// {{{ _getPairId
	/**
	* Retrieve the pair Id of two players
	*
	* @access private
	* @param  integer  $regiId1  Id of the first player of the pair
	* @param  integer  $regiId2  Id of the second player of the pair -1 if none
	* @param  integer  $disci    Discipline to search
	* @return integer id of the pair
	*/
	function _getPairId($regiId1, $regiId2, $aDiscipline)
	{
		// Classement actuel des joueurs
		$fields  = array('rank_rankdefid', 'rank_rank');
		$tables  = array('ranks');
		$where = "rank_regiId = $regiId1 AND rank_discipline=$aDiscipline";
		$rank1 = $this->_selectFirst($tables, $fields, $where);
	
		// Search the pair of the first player
		$fields  = array('pair_id');
		$tables  = array('pairs', 'i2p p1');
		$where = "p1.i2p_regiId = $regiId1".
			" AND p1.i2p_pairId = pair_id".
			" AND pair_disci = $aDiscipline";
		if (!empty($rank1))
		{
		    $where .= " AND p1.i2p_rankdefid=" .  $rank1['rank_rankdefid'].
		    " AND p1.i2p_classe=" . $rank1['rank_rank'];
		}
		
		if ($aDiscipline != WBS_SINGLE)
		{
			// Classement actuel du joueur
			$cols  = array('rank_rankdefid', 'rank_rank');
			$oudonc = "rank_regiId = $regiId2 AND rank_discipline=$aDiscipline";
			$rank2 = $this->_selectFirst('ranks', $cols, $oudonc);
			$tables[]  = 'i2p p2';
			$where .= " AND p2.i2p_regiId = $regiId2".
			" AND p2.i2p_id != p1.i2p_id".
			" AND p2.i2p_pairId = pair_id";
			if (!empty($rank2))
			{
		    	$where .=  " AND p2.i2p_rankdefid=" .  $rank2['rank_rankdefid'].
		    " AND p2.i2p_classe=" . $rank2['rank_rank'];
			}
		}
		$pairId = $this->_selectfirst($tables, $fields, $where);
		
		// No pair found, create a new one
		if (empty($pairId) )
		{
			// Create the pair
			$fields = array();
			$fields['pair_status'] = WBS_PAIR_MAINDRAW;
			$fields['pair_disci'] = $aDiscipline;
			$res = $this->_insert('pairs', $fields);
			$pairId = $res;
			// Create the relation between pair and player one
			// Recuperer les info classement du joueur
			$fields = array('rank_rankdefid, rank_rank, rank_average');
			$where = 'rank_regiid =' . $regiId1;
			$where .= ' AND rank_discipline =' . $aDiscipline;
			$rank = $this->_getRow('ranks', $fields, $where);
			
			$fields = array();
			$fields['i2p_pairId'] = $pairId;
			$fields['i2p_regiId'] = $regiId1;
			$fields['i2p_rankdefid'] = $rank['rank_rankdefid'];
			$fields['i2p_cppp'] = $rank['rank_average'];
			$fields['i2p_classe'] = $rank['rank_rank'];
			$res = $this->_insert('i2p', $fields);

			// Create the relation between pair and player two
			if ($aDiscipline != WBS_SINGLE)
			{
	  		// Recuperer les info classement du joueur
	  		$fields = array('rank_rankdefid, rank_rank, rank_average');
	  		$where = 'rank_regiid =' . $regiId2;
	  		$where .= ' AND rank_discipline =' . $aDiscipline;
	  		$rank = $this->_getRow('ranks', $fields, $where);
				
	  		$fields = array();
				$fields['i2p_pairId'] = $pairId;
				$fields['i2p_regiId'] = $regiId2;
		  		$fields['i2p_rankdefid'] = $rank['rank_rankdefid'];
		  		$fields['i2p_cppp'] = $rank['rank_average'];
		  		$fields['i2p_classe'] = $rank['rank_rank'];
				$res = $this->_insert('i2p', $fields);
			}
		}
		return $pairId;
			}
	// }}}

	// {{{ updatePenalties
	/**
	 * Update the penalties of a team and the ranking
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function updatePenalties($t2rId, $penalties)
	{
		// Updating the penalties
		$fields['t2r_penalties'] = $penalties;
		$where = "t2r_id=$t2rId";
		$res = $this->_update('t2r', $fields, $where);

		// Update the rank
		$fields = array('t2r_roundId');
		$tables = array('t2r');
		$where = "t2r_id=$t2rId";
		$res = $this->_select($tables, $fields, $where);

		$data = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$utt = new utTeam();
		return $utt->updateGroupRank($data['t2r_roundId']);

	}
	// }}}


	// {{{ updateKo
	/**
	 * Update the next tie for a KO draw
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function updateKo($tieId)
	{
		// search the winner of the tie
		$fields =  array('t2t_teamId', 'tie_posRound', 'rund_id');
		$tables = array('t2t', 'ties', 'rounds');
		$where = "t2t_tieId=$tieId".
	" AND t2t_tieId=tie_id".
	" AND tie_roundId=rund_id".
	" AND (t2t_result=".WBS_TIE_WIN.
	" OR t2t_result=".WBS_TIE_WINWO.")".
	" AND rund_type=".WBS_TEAM_KO;
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		return;
		$data = $res->fetchRow(DB_FETCHMODE_ASSOC);

		// Updating relation between pairs and match for winner
		// Pas de mise a jour si c'est la finale
		if (!$data['tie_posRound']) return;

		// Next tie
		$tiePosRound = intval(($data['tie_posRound']-1)/2);
		// Position of the team in the next tie
		$teamPosTie = ($data['tie_posRound']%2) ? WBS_TEAM_TOP:WBS_TEAM_BOTTOM;;
		$teamId = $data['t2t_teamId'];
		$roundId = $data['rund_id'];

		$fields =  array('t2t_id', 'tie_id');
		$tables = array('t2t', 'ties');
		$where = "t2t_tieId=tie_id".
	" AND tie_roundId = $roundId".
	" AND tie_posRound = $tiePosRound".
	" AND t2t_posTie=$teamPosTie";
		$res = $this->_select($tables, $fields, $where);
		if ($res->numRows())
		{
	  $data = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $fields = array();
	  $fields['t2t_teamId'] = $teamId;
	  $where = "t2t_id = ".$data['t2t_id'];
	  $res = $this->_update('t2t', $fields, $where);
		}
		else
		{
	  // Search the tie Id
	  $fields =  array('tie_id');
	  $tables = array('ties');
	  $where = "tie_roundId = $roundId".
	    " AND tie_posRound = $tiePosRound";
	  $res = $this->_select($tables, $fields, $where);
	  $data = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $fields = array();
	  $fields['t2t_teamId'] = $teamId;
	  $fields['t2t_tieId'] = $data['tie_id'];
	  $fields['t2t_posTie'] =$teamPosTie;
	  $fields['t2t_result'] = WBS_TIE_NOTPLAY;

	  $res = $this->_insert('t2t', $fields);
		}
		return;
	}
	// }}}



}
?>