<?php
/*****************************************************************************
 !   Module     : Matches
 !   File       : $Source: /cvsroot/aotb/badnet/src/matches/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.30 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/04/04 22:55:51 $
 ******************************************************************************/
require_once "utils/utbase.php";
require_once "utils/utteam.php";
require_once "utils/utmatch.php";
require_once "utils/utround.php";
require_once "utils/utservices.php";
require_once "draws/draws.inc";

/**
 * Acces to the dababase for events
 *
 * @author Didier BEUVELOT <didier.beuvelot@free.fr>
 * @see to follow
 *
 */

class matchBase_A extends utbase
{

	// {{{ properties
	// }}}


	// {{{ needDraw
	/**
	 * Teste s'il faut effectuer le tirage au sort
	 * des sorties de poule
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function needDraw($matchId)
	{
		// Getting the draw
		$fields = array('rund_drawId', 'rund_type', 'rund_id', 'rund_group');
		$tables = array('matchs', 'ties', 'rounds');
		$where = "mtch_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND mtch_id=$matchId";
		$res = $this->_select($tables, $fields, $where);
		$matchDef = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$drawId = $matchDef['rund_drawId'];
		$group = $matchDef['rund_group'];
		$oGroup = new objgroup();
		$group = $oGroup->getGroup($drawId, $group);
		$roundId = $group['mainroundid'];

		// Nombre de match de poule non termines
		$tables = array('matchs', 'ties', 'rounds');
		$where = "mtch_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND rund_drawId=$drawId".
	" AND rund_type = ".WBS_ROUND_GROUP.
	" AND mtch_status < ".WBS_MATCH_ENDED;
		$matchDef['matchGroup'] = $this->_selectFirst($tables, 'count(*)', $where);

		// Nombre de paire dans les matchs KO
		$matchDef['pairsKo'] = 0;
		if ($roundId > 0)
		{
			$tables = array('matchs', 'ties', 'rounds', 'p2m');
			$where = "mtch_tieId = tie_id".
		" AND tie_roundId = rund_id".
		" AND tie_isBye = 0".
		" AND rund_id=". $roundId.
		" AND p2m_matchId = mtch_id";
			$matchDef['pairsKo']  = $this->_selectFirst($tables, 'count(*)', $where);
		}

		// Nombre de match KO non termines
		$matchDef['matchKo'] = 0;
		if ($roundId > 0)
		{
			$tables = array('matchs', 'ties', 'rounds');
			$where = "mtch_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND tie_isBye = 0".
	" AND rund_id=$roundId".
	" AND mtch_status < ".WBS_MATCH_ENDED;
			$matchDef['matchKo'] = $this->_selectFirst($tables, 'count(*)', $where);
		}
		return $matchDef;
	}
	//}}}

	// {{{ getExportPairId
	/**
	 * Return the reference of the page to be deleted in the cache
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function getExportPairId($pairId)
	{
		// Updating the match
		$where = "pair_id=$pairId";
		return $this->_selectFirst('pairs', 'pair_externId', $where);
	}
	//}}}

	// {{{ getExportId
	/**
	 * Return the reference of the page to be deleted in the cache
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function getExportId($matchId)
	{
		// Updating the match
		$where = "mtch_id=$matchId";
		return $this->_selectFirst('matchs', 'mtch_externId', $where);
	}
	//}}}

	// {{{ getCacheRef
	/**
	 * Return the reference of the page to be deleted in the cache
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function getCacheRef($matchId)
	{
		// Searching the match
		$fields = array('regi_id', 'rund_type', 'rund_drawId', 'rund_id');
		$tables = array('matchs', 'ties', 'rounds',
		      'p2m', 'i2p', 'registration');
		$where = "mtch_id=$matchId".
	" AND regi_id = i2p_regiId".
	" AND i2p_pairId = p2m_pairId".
	" AND p2m_matchId = mtch_id".
	" AND mtch_tieId = tie_id".
	" AND tie_roundId = rund_id";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows()) return array();
		$refs = array();
		$ref['page'] = 'resu';
		$ref['act'] = KID_SELECT;
		while ( $player = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $rundType = $player['rund_type'];
	  $drawId   = $player['rund_drawId'];
	  $rundId   = $player['rund_id'];
	  $ref['data'] = $player['regi_id'];
	  $refs[] = $ref;
		}
		$ref['page'] = 'draws';
		if($rundType == WBS_ROUND_GROUP)
		{
	  $ref['act'] = DRAW_GROUPS_DISPLAY;
	  $ref['data'] = $drawId;
		}
		else
		{
	  $ref['act'] = DRAW_KO_DISPLAY;
	  $ref['data'] = $rundId;
		}
		$refs[] = $ref;
		return $refs;
	}
	//}}}


	// {{{ updateMatch
	/**
	 * Update the result of a match for a individual event
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function updateMatch($match, $winPairId, $loosPairId)
	{
		$user     = utvars::getUserLogin();
		$pwd      = utvars::getUserPwd();
		$eventId  = utvars::getEventId();
		$uts = new utServices('local', $user, $pwd);
		$res = $uts->updateMatchStatus($eventId, $match);
		$res = $uts->updateMatchResult($eventId, $match, $winPairId, $loosPairId);
		unset($uts);
		
		// Enregistrement du resultat sur le site distant
		$ut = new utils();
		$server = $ut->getParam('synchroUrl', false);
		if ($server)
		{
	  $user     = $ut->getParam('synchroUser');
	  $pwd      = md5($ut->getParam('synchroPwd'));
	  $eventId  = $ut->getParam('synchroEvent');
	  $uts = new utServices($server, $user, $pwd);
	  $uts->updateMatchResult($eventId, $match, $winPairId, $loosPairId);
	  unset($uts);
		}
		return $res;
	}
	//}}}

	// {{{ _updateGroupMatch
	/**
	 * Calculate the rank in the group and update number et schedule of matches
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function _updateGroupMatch($winPairId, $oldWinner, $data, $match)
	{
		$this->_updateGroupRank($data['tie_roundId']);

		// Pour une poule de trois, si c'est le premier match
		// mettre a jour les numeros et horaires des deux autres matches
		if ($data['rund_size'] == 3 &&
		$data['tie_posRound'] == 2)
		{
	  // Si le vainqueur est le troisieme joueur, il faut
	  // inverser numero et heure des deux autres matches
	  // Position du vainqueur
	  $fields = array('t2r_posRound');
	  $tables = array('t2r');
	  $where = "t2r_pairId=$winPairId";
	  $res = $this->_select($tables, $fields, $where);
	  $tmp = $res->fetchRow(DB_FETCHMODE_ASSOC);

	  //il est en troisieme position
	  if( ($oldWinner == -1 && $tmp['t2r_posRound'] == 3) ||
	  ($oldWinner != -1 && $oldWinner != $winPairId))
	  {
	  	// recherche des autres matchs de la poule
	  	$fields = array('tie_schedule', 'tie_place', 'tie_court',
			      'mtch_num', 'tie_id', 'mtch_id');
	  	$tables = array('ties', 'matchs');
	  	$where = "tie_id = mtch_tieId".
		" AND tie_roundId =". $data['tie_roundId'].
		" AND mtch_id !=". $match['mtch_id'];
	  	$res = $this->_select($tables, $fields, $where);

	  	// Il doit y avoir 2 match
	  	if ($res->numRows() == 2)
	  	{
	  		$tmp1 = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  		$tmp2 = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  		$cols = array();
	  		$cols['mtch_num'] = $tmp1['mtch_num'];
	  		$where = "mtch_id=".$tmp2['mtch_id'];
	  		$res = $this->_update('matchs', $cols, $where);
	  		$cols['mtch_num'] = $tmp2['mtch_num'];
	  		$where = "mtch_id=".$tmp1['mtch_id'];
	  		$res = $this->_update('matchs', $cols, $where);
	  		$cols = array();
	  		$cols['tie_schedule'] = $tmp1['tie_schedule'];
	  		$cols['tie_place'] = $tmp1['tie_place'];
	  		$cols['tie_court'] = $tmp1['tie_court'];
	  		$where = "tie_id=".$tmp2['tie_id'];
	  		$res = $this->_update('ties', $cols, $where);
	  		$cols['tie_schedule'] = $tmp2['tie_schedule'];
	  		$cols['tie_place'] = $tmp2['tie_place'];
	  		$cols['tie_court'] = $tmp2['tie_court'];
	  		$where = "tie_id=".$tmp1['tie_id'];
	  		$res = $this->_update('ties', $cols, $where);
	  	}
	  	else
	  	echo "__FILE__.' ('.__LINE__.') :".
		  "Ca etre bizarre!!!";
	  }
		}
	}
	//}}}

	// {{{ _updateGroupRank
	/**
	 * Calculate the ranking of each pairs in the given group
	 *
	 * @access private
	 * @param  integer  $groupId   Id of the group
	 * @return integer id of the team
	 */
	function _updateGroupRank($roundId)
	{
		// Get the point of the result of a tie
		$fields = array('rund_tieWin', 'rund_tieEqual', 'rund_tieLoose',
		      'rund_tieWO');
		$tables = array('rounds');
		$where = "rund_id = $roundId";
		$res = $this->_select($tables, $fields, $where);
		$points = $res->fetchRow(DB_FETCHMODE_ASSOC);

		// Calculer pour chaque paire les cumuls de
		// match gagnes, set gagnes et points marques
		$fields = array('p2m_pairId', 'p2m_result', 'mtch_score');
		$tables = array('p2m', 'matchs', 'ties');
		$where = "tie_roundId ='$roundId'".
	" AND tie_id = mtch_tieId".
	" AND mtch_id = p2m_matchId";
		$res = $this->_select($tables, $fields, $where);
		$pairs = array();

		$uts = new utscore();
		while ($pair = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $pairId = $pair['p2m_pairId'];
	  $uts->setScore($pair['mtch_score']);
	  if (isset($pairs[$pairId]))
	  $row = $pairs[$pairId];
	  else
	  {
	  	$row = array('pairId' => $pairId,
			   'matchW' => 0,
			   'matchL' => 0,
			   'gameW'  => 0,
			   'gameL'  => 0,
			   'pointW' => 0,
			   'pointL' => 0,
			   'points' => 0,
			   'point'  => 0,
			   'bonus'  => 0);
	  }

	  switch($pair['p2m_result'])
	  {
	  	case WBS_RES_WINAB;
	  	case WBS_RES_WINWO:
	  	case WBS_RES_WIN:
	  		$row['matchW']++;
	  		$row['gameW'] += $uts->getNbWinGames();
	  		$row['gameL'] += $uts->getNbLoosGames();
	  		$row['pointW']+= $uts->getNbWinPoints();
	  		$row['pointL']+= $uts->getNbLoosPoints();;
	  		$row['points']++;
	  		$row['point'] += 100;
	  		break;
	  	case WBS_RES_LOOSEWO:
	  		$row['points']--;
	  		$row['point']-= 100;
	  		// No break here !!!
	  	case WBS_RES_LOOSEAB;
	  	case WBS_RES_LOOSE:
	  		$row['gameW'] += $uts->getNbLoosGames();
	  		$row['gameL'] += $uts->getNbWinGames();
	  		$row['pointW']+= $uts->getNbLoosPoints();
	  		$row['pointL']+= $uts->getNbWinPoints();
	  		break;
	  	default:
	  		break;
	  }
	  $pairs[$pairId] = $row;
		}
		$nbPairs = count($pairs);
		foreach($pairs as $pair)
		$rows[] = $pair;

		// S'il y  a plus de deux equipes a egalites
		// on regarde le nombre de sets gagnes/perdus
		if ($this->_checkEqual($rows, $roundId))
		{
	  for ($i=0; $i<$nbPairs; $i++)
	  {
	  	$ti =$rows[$i];
	  	for ($j=$i+1; $j<$nbPairs; $j++)
	  	{
	  		$tj =$rows[$j];
	  		if ($ti['point'] == $tj['point'])
	  		{
	  			if ($ti['gameW'] - $ti['gameL'] >
	  			$tj['gameW'] - $tj['gameL'])
	  			{
	  				$ti['bonus'] += 10;
	  				$rows[$i] = $ti;
	  			}
	  			if ($ti['gameW'] - $ti['gameL'] <
	  			$tj['gameW'] - $tj['gameL'])
	  			{
	  				$tj['bonus'] += 10;
	  				$rows[$j] = $tj;
	  			}
	  		} //end if team equal
	  	} //next $j
	  } // next $i
		}

		// S'il y  a toujours plus de deux paires a egalites
		// on regarde le nombre de points gagnes/perdus
		if ($this->_checkEqual($rows, $roundId))
		{
	  for ($i=0; $i<$nbPairs; $i++)
	  {
	  	$ti =$rows[$i];
	  	for ($j=$i+1; $j<$nbPairs; $j++)
	  	{
	  		$tj =$rows[$j];
	  		if ($ti['point'] == $tj['point'])
	  		{
	  			if ($ti['pointW'] - $ti['pointL'] >
	  			$tj['pointW'] - $tj['pointL'])
	  			{
	  				$ti['point'] += 1;
	  				$rows[$i] = $ti;
	  			}
	  			if ($ti['pointW'] - $ti['pointL'] <
	  			$tj['pointW'] - $tj['pointL'])
	  			{
	  				$tj['point'] += 1;
	  				$rows[$j] = $tj;
	  			}
	  		} //end if team equal
	  	} //next $j
	  } // next $i
		}

		// Calcul du classement de chaque equipe
		for ($i=0; $i<$nbPairs; $i++)
		{
	  $ti =$rows[$i];
	  $ti['rank']=1;
	  for ($j=0; $j<$nbPairs; $j++)
	  {
	  	$tj =$rows[$j];
	  	if ($tj['point'] > $ti['point'])
	  	{
	  		$ti['rank'] ++;
	  	}
	  	$rows[$j] = $tj;
	  }
	  $rows[$i] = $ti;
		}

		// Mise a jour de la base
		$fields = array();
		for ($i=0; $i<$nbPairs; $i++)
		{
	  $ti =$rows[$i];
	  $fields['t2r_rank']   = $ti['rank'];
	  $fields['t2r_points'] = $ti['pointW'] - $ti['pointL'];
	  $fields['t2r_tieW']   = $ti['gameW']-  $ti['gameL'];
	  $fields['t2r_tieL']   = $ti['matchL'];
	  $where = "t2r_pairId=".$ti['pairId'].
	    " AND t2r_roundId =".$roundId;
	  $r = $this->_update('t2r', $fields, $where);
		}

		return;
	}
	// }}}

	// {{{ _checkEqual
	/**
	 * Check if some team have the same score
	 *
	 * @access private
	 * @param  integer  $groupId   Id of the group
	 * @return integer id of the team
	 */
	function _checkEqual(&$rows, $roundId)
	{
		// Ajouter leur bonus aux paires
		$nbPairs = count($rows);
		for ($j=0; $j<$nbPairs; $j++)
		{
	  $tj = $rows[$j];
	  $tj['point'] += $tj['bonus'];
	  $tj['bonus'] = 0;
	  $rows[$j] = $tj;
		}


		$isEqual = false;
		// Pour chaque paire
		for ($i=0; $i<$nbPairs; $i++)
		{
	  $ti =$rows[$i];
	  $pairEqual = 0;
	  $nbEqual   = 0;
	  // Calculer les paires a egalite
	  for ($j=0; $j<$nbPairs; $j++)
	  {
	  	if ($i == $j) continue;
	  	$tj = $rows[$j];
	  	$rows[$j] = $tj;
	  	if ($ti['point'] == $tj['point'])
	  	{
	  		$pairEqual = $tj['pairId'];
	  		$nbEqual++;
	  	}
	  }
	  // S'il y en a une seule, trouver la gagnante
	  if ($nbEqual == 1)
	  {
	  	$winner = $this->_getWinPair($roundId, $ti['pairId'],
	  	$pairEqual);
	  	if (is_array($winner)) return $winner;
	  	if ($winner == $ti['pairId']) $ti['point'] += 0.1;
	  	if ($winner != $pairEqual) $isEqual = true;
	  	$rows[$i] = $ti;
	  }
	  if ($nbEqual > 1) $isEqual = true;
		}
		return $isEqual;
	}

	// {{{ _getWinPair
	/**
	 * Return the winner team of a match
	 *
	 * @access private
	 * @param  integer  $groupId  Id of the group
	 * @param  integer  $team1    Id of the first teams
	 * @param  integer  $team1    Id of the second team
	 * @return integer id of the team
	 */
	function _getWinPair($roundId, $pair1, $pair2)
	{
		// Chercher les match des paires dans le groupes
		$fields = array('p2m_pairId', 'p2m_matchId', 'p2m_result');
		$tables = array('p2m', 'matchs', 'ties');
		$where = "tie_roundId=$roundId".
	" AND tie_id = mtch_tieId".
	" AND mtch_id = p2m_matchId".
	" AND p2m_result != ".WBS_RES_NOPLAY.
	" AND (p2m_pairId = $pair1 OR p2m_pairId = $pair2)";
		$order = 'mtch_id';
		$res = $this->_select($tables, $fields, $where);

		// Trouver le match commun aux deux paires
		$winner = 0;
		while ($tmp  = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  if (isset($matchs[$tmp['p2m_matchId']]))
	  {
	  	if ($tmp['p2m_result'] == WBS_RES_WIN ||
		  $tmp['p2m_result'] == WBS_RES_WINWO ||
		  $tmp['p2m_result'] == WBS_RES_WINAB)
		  $winner = $tmp['p2m_pairId'];
		  else
		  $winner = $matchs[$tmp['p2m_matchId']]['p2m_pairId'];

	  }
	  $matchs[$tmp['p2m_matchId']] = $tmp;
		}

		return $winner;
	}
	// }}}



	// {{{ _updateNextKoTie
	/**
	 * Update the next ko tie for and individual event
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function _updateNextKoTie($data, $winPairId)
	{
		$tiePosRound = intval(($data['tie_posRound']-1)/2);
		// Position of the pair in the next match
		$pairPos = ($data['tie_posRound']%2) ? WBS_PAIR_TOP:WBS_PAIR_BOTTOM;;

		// search the match of the next tie
		$fields = array('mtch_id', 'mtch_status');
		$tables = array('matchs', 'ties');
		$where = "tie_id=mtch_tieId".
	" AND tie_roundId=".$data['tie_roundId'].
	" AND tie_posRound=$tiePosRound";
		$res = $this->_select($tables, $fields, $where);

		if ($res->numRows())
		{
	  $buf = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $matchId = $buf['mtch_id'];
	  $matchStatus = $buf['mtch_status'];

	  // update the relation between pair and match
	  $fields = array('p2m_id');
	  $tables = array('p2m');
	  $where = "p2m_matchId=$matchId".
		" AND p2m_posMatch=$pairPos";
	  $res = $this->_select($tables, $fields, $where);

	  $column['p2m_pairId'] = $winPairId;
	  $column['p2m_matchId'] = $matchId;
	  $column['p2m_posMatch'] = $pairPos;
	  if ($res->numRows())
	  $res = $this->_update('p2m', $column, $where);
	  else
	  {
	  	$column['p2m_result'] = WBS_RES_NOPLAY;
	  	$res = $this->_insert('p2m', $column);
	  }

	  // update the status of the match
	  $fields = array('p2m_id');
	  $tables = array('p2m');
	  $where = "p2m_matchId=$matchId";
	  $res = $this->_select($tables, $fields, $where);
	  $where = "mtch_id=$matchId";
	  $column = array();
	  $column['mtch_status'] = $matchStatus;
	  if ($res->numRows()>1)
	  {
	  	if ($matchStatus < WBS_MATCH_LIVE)
	  	$column['mtch_status'] = WBS_MATCH_READY;
	  }
	  else
	  $column['mtch_status'] = WBS_MATCH_INCOMPLETE;
	  $res = $this->_update('matchs', $column, $where);
		}
	}
	//}}}

	/**
	* Update the result of a match for a team event
	*
	* @access public
	* @param  string  $info   column of the match
	* @return mixed
	*/
	function updateMatchTeam($match)
	{
		$user     = utvars::getUserLogin();
		$pwd      = utvars::getUserPwd();
		$eventId  = utvars::getEventId();
		$uts = new utServices('local', $user, $pwd);
		
		$res = $uts->updateMatchTeamResult($eventId, $match);
		unset($uts);

		// Enregistrement du resultat sur le site distant
		$ut = new utils();
		$server = $ut->getParam('synchroUrl', false);
		if ($server)
		{
	  		$user     = $ut->getParam('synchroUser');
	  		$pwd      = md5($ut->getParam('synchroPwd'));
	  		$eventId  = $ut->getParam('synchroEvent');
	  		$uts = new utServices($server, $user, $pwd);
	  		$uts->updateMatchTeamResult($eventId, $match);
	  		unset($uts);
		}
		return $res;
	}


	// {{{ updateMatchTeam
	/**
	 * Add or update a match of a team event into the database
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function updateMatchTeamOld($infos)
	{
		// Connect to the database
		$match = $this->getMatchTeam($infos['mtch_id']);
		$uts = new utscore();
		$uts->setScore($infos['mtch_score']);

		// Updating the match
		$utd = new utdate();
		$utd->setFrDateTime($infos['mtch_begin']);
		$fields['mtch_begin'] = $utd->getIsoDateTime();
		$utd->setFrDateTime($infos['mtch_end']);
		$fields['mtch_end'] = $utd->getIsoDateTime();
		if (($match['mtch_status'] != WBS_MATCH_LIVE) ||
		($infos['mtch_status'] > WBS_MATCH_LIVE))
		{
			$fields['mtch_status'] = $infos['mtch_status'];
		}
		$fields['mtch_score'] = $infos['mtch_score'];
		$where = "mtch_id=".$infos['mtch_id'];
		$res = $this->_update('matchs', $fields, $where);

		// Updating relation between pairs and match for winner
		$winPairId = $this->_getPairId($infos['mtch_winId0'], $infos['mtch_winId1'], $match['mtch_disci']);
		if (is_array($winPairId)) return $winPairId;

		$fields = array('p2m_id');
		$tables = array('p2m');
		$where = "p2m_matchId='".$infos['mtch_id']."'";
		$p2ms = $this->_select($tables, $fields, $where);

		$p2m = $p2ms->fetchRow(DB_FETCHMODE_ORDERED);
		$fields = array();
		$fields['p2m_pairId'] = $winPairId;
		if ($infos["mtch_status"] >= WBS_MATCH_ENDED)
		if ($uts->isWO()) $fields['p2m_result'] = WBS_RES_WINWO;
		else if ($uts->isAbort()) $fields['p2m_result'] = WBS_RES_WINAB;
		else $fields['p2m_result'] = WBS_RES_WIN;
		else $fields['p2m_result'] = WBS_RES_NOPLAY;

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
		$loosPairId = $this->_getPairId($infos['mtch_loosId0'], $infos['mtch_loosId1'], $match['mtch_disci']);
		if (is_array($loosPairId)) return $loosPairId;

		$fields = array('p2m_id');
		$tables = array('p2m');
		$where = "p2m_matchId='".$infos['mtch_id']."'".
	" AND p2m_id != $p2mId";
		$p2ms = $this->_select($tables, $fields, $where);

		$p2m = $p2ms->fetchRow(DB_FETCHMODE_ORDERED);
		$fields = array();
		$fields['p2m_pairId'] = $loosPairId;
		if ($infos["mtch_status"] >= WBS_MATCH_ENDED)
		if ($uts->isWO())
		$fields['p2m_result'] = WBS_RES_LOOSEWO;
		else if ($uts->isAbort())
		$fields['p2m_result'] = WBS_RES_LOOSEAB;
		else
		$fields['p2m_result'] = WBS_RES_LOOSE;
		else
		$fields['p2m_result'] = WBS_RES_NOPLAY;

		if ($p2m)
		{
	  $p2mId2 = $p2m[0];
	  $where = "p2m_id = $p2mId2";
	  $res = $this->_update('p2m', $fields, $where);
		}
		else
		{
	  $fields['p2m_matchId'] = $infos['mtch_id'];
	  $fields['p2m_posMatch'] = WBS_PAIR_BOTTOM;
	  $p2mId2 = $this->_insert('p2m', $fields);
		}


		$ou = "p2m_matchId='".$infos['mtch_id']."'".
	" AND p2m_id != $p2mId".
	" AND p2m_id != $p2mId2";
		$this->_delete('p2m', $ou);

		// Now updating result of the tie !

		$fields = array('rund_id', 'tie_id');
		$tables = array('rounds', 'ties', 'matchs');
		$where = "mtch_id='".$infos['mtch_id']."'".
	" AND rund_id = tie_roundId".
	" AND tie_id = mtch_tieId";
		$round = $this->_select($tables, $fields, $where);

		$group = $round->fetchRow(DB_FETCHMODE_ORDERED);

		$utt = new utteam();
		$res = $utt->updateTeamResult($infos['mtch_teamId0'], $group[1]);
		if (is_array($res)) return $res;
		$res = $utt->updateTeamResult($infos['mtch_teamId1'], $group[1]);
		if (is_array($res)) return $res;
		$res = $utt->updateGroupRank($group[0]);
		if (is_array($res)) return $res;

		$utm = new utmatch();
		if ($infos["mtch_status"] >= WBS_MATCH_ENDED)
		{
	  // Updating rest time of players
	  $err = $utm->updateRestTime($infos['mtch_id'], $utd->getIsoDateTime());
	  if (is_array($err))
	  return $err;
		}

		// Updating status of other matches
		return $utm->updateStatusMatch();
	}
	// }}}


	// {{{ getMatchTeam
	/**
	 * Return a match for an team event
	 *
	 * @access public
	 * @param  string  $matchId  id of the match
	 * @return array   information of the match if any
	 */
	function getMatchTeam($matchId)
	{
		// Retrieve informations of the match
		$fields = array('mtch_id', 'mtch_num', 'mtch_status',
		      'mtch_discipline', 'mtch_score', 'mtch_cmt',
		      'mtch_order', 'mtch_begin', 'mtch_end', 'mtch_disci', 'rund_type', 'tie_id', 'rund_id');
		$tables = array('matchs', 'ties', 'rounds');
		$where = "mtch_id = $matchId AND mtch_tieid=tie_id AND tie_roundid=rund_id";
		$res = $this->_select($tables, $fields, $where);
		$match = $res->fetchRow(DB_FETCHMODE_ASSOC);

		$utd = new utdate();
		$time = $utd->getDateTime();
		if (!is_null($match['mtch_begin']))	$utd->setIsoDateTime($match['mtch_begin']);
		$match['mtch_begin'] = $utd->getDateTime();
		if ($match['mtch_begin']=='')$match['mtch_begin']=$time;
		if ($match['mtch_status'] < WBS_MATCH_ENDED) $utd = new utdate();
		else $utd->setIsoDateTime($match['mtch_end']);
		$match['mtch_end'] = $utd->getDateTime();

		// Retrieve the concerned teams
		$fields = array('team_id', 'team_name', 'team_stamp');
		$tables = array('teams', 't2t', 'matchs');
		$where = "mtch_id=$matchId".
				" AND mtch_tieId=t2t_tieId".
				" AND t2t_teamId=team_id";
		$order  = "t2t_posTie";
		$res = $this->_select($tables, $fields, $where, $order);
		$nbTeams = $res->numRows();
		for($i=0; $i < $nbTeams; $i++)
		{
			$team = $res->fetchRow(DB_FETCHMODE_ORDERED);
			$match["mtch_teamId$i"]=$team[0];
			$match["mtch_team$i"]=$team[1];
			$match["mtch_stamp$i"]=$team[2];
			$match["mtch_regiId$i"."0"]="";
			$match["mtch_regiId$i"."1"]="";
			$match["mtch_result$i"."0"]= WBS_RES_NOPLAY;
			$match["mtch_result$i"."1"]= WBS_RES_NOPLAY;

	  		// Retrieve the players name
	  		$fields = array('regi_id', 'p2m_result');
	  		$tables = array('registration', 'p2m', 'i2p', 'members');
	  		$where = "p2m_matchId = '$matchId'".
	    		" AND p2m_pairId = i2p_pairId".
	    		" AND i2p_regiId = regi_id".
	    		" AND mber_id = regi_memberId".
	    		" AND regi_teamId =". $team[0];
	  		$order = "mber_sexe, regi_longName";
	  		$rq = $this->_select($tables, $fields, $where, $order);
	  		$nb = $rq->numRows();
	  		for($j=0; $j < $nb; $j++)
	  		{
	  			$player = $rq->fetchRow(DB_FETCHMODE_ORDERED);
	  			$match["mtch_regiId$i$j"]=$player[0];
	  			$match["mtch_result$i$j"]=$player[1];
	  		}
		}
		return $match;
	}

	/**
	 * Return all registered players of a team
	 *
	 * @access public
	 * @param  integer $teamId  id of the team
	 * @param  integer $sexe    do you want man or woman
	 * @param  integer $disicpline sigle, double or mixed ?
	 * @return array   list of the players
	 */
	function getRegiPlayers($teamId, $sexe, $discipline)
	{
		$ut = new utils();
		// Retrieve the players name
		$eventId = utvars::getEventId();
		$fields = array('regi_id', 'regi_shortName', 'rkdf_label',
		      'rank_average', 'regi_catage', 'rank_rank');
		$tables = array('registration', 'members', 'ranks', 'rankdef');
		$where ="regi_teamId =". $teamId.
	" AND  mber_id = regi_memberId".
	" AND  rank_regiId = regi_id".
	" AND  rank_rankdefId = rkdf_id".
	" AND regi_eventId = $eventId";
		if ($sexe != -1) $where .= " AND  mber_sexe = $sexe";
		if ($discipline != -1) $where .= " AND  rank_disci = $discipline";
		else $where .= " AND  (rank_disci = " . WBS_MS . " OR rank_disci = " . WBS_WS . ")";
		$order = "rkdf_point DESC, rank_rank";
		$rq = $this->_select($tables, $fields, $where, $order);
		$players= array();
		while ( $player = $rq->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $players[$player['regi_id']] = $player['rkdf_label'] . ' - '
	  . sprintf('%05d', $player['rank_rank']) . ' - '
	  . $player['regi_shortName'] . ' - '
	  . $ut->getSmaLabel($player['regi_catage']) . ' - '
	  . sprintf('%.02f', $player['rank_average']);
		}
		return $players;
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
		return $player[0] < 0;
	}
	// }}}


	// {{{ getMatches
	/**
	 * Return the list of the matches
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort users
	 * @param  integer $first  first user to retrieve
	 * @param  integer $step   number of user to retrieve
	 * @return array   array of users
	 */
	function getMatches($sort)
	{
		// Retrieve matches
		$fields = array('mtch_id', 'mtch_num', 'mtch_score');
		$tables[] = 'matchs';
		$where = "mtch_del != ".WBS_DATA_DELETE;
		if ($sort > 0)
		$order = "$sort ";
		else
		$order = abs($sort) . " desc";

		$res = $this->_select($tables, $fields, $where, $order);
		$fields = array('pair_ibfNum', 'pair_id');
		$tables = array('p2m', 'pairs');
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $where = "pair_id=p2m_pairId AND p2m_matchId='".$entry['0']."'";
	  $rq = $this->_select($tables, $fields, $where);
	  $nb = $rq->numRows();
	  if($nb==0)
	  {
	  	$entry[] = "Team 1";
	  	$entry[] = "";
	  	$entry[] = "Team 2";
	  	$entry[] = "";
	  }
	  elseif($nb==1)
	  {
	  	$entry[] = "Team 1";
	  	$entry[] = "";
	  }

	  while ($er = $rq->fetchRow(DB_FETCHMODE_ORDERED)){
	  	//on r�cup�re d'abord pair_ibfNumber puis pair_id
	  	$entry[] = $er[0];$entry[] = $er[1];
	  }
	  $rows[] = $entry;
		}
		return $rows;
	}
	// }}}


	// {{{ getMatch
	/**
	 * Return a match
	 *
	 * @access public
	 * @param  string  $matchId  id of the match
	 * @return array   information of the match if any
	 */
	function getMatch($matchId, $matchNum = 0)
	{

		// Search the match
		$fields = array('mtch_num', 'mtch_status', 'mtch_discipline',
		      'mtch_score', 'mtch_id', 'mtch_court',
		      'mtch_begin', 'mtch_end', 'mtch_umpireId',
		      'regi_longName', 'mtch_luckylooser',
		      'draw_name', 'rund_name', 'tie_posRound');
		$tables = array('ties', 'rounds', 'draws',
		      'matchs LEFT JOIN registration ON mtch_umpireId = regi_id'
		      );
		      $where = "mtch_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND tie_isBye = 0";
		      if ($matchNum != 0)
		      $where .= " AND match_num=$matchNum";
		      else
		      $where .= " AND mtch_id = $matchId";

		      $res = $this->_select($tables, $fields, $where);
		      $match = $res->fetchRow(DB_FETCHMODE_ASSOC);

		      $utd = new utdate();
		      $time = $utd->getDateTime();
		      if (!is_null($match['mtch_begin']))
		      $utd->setIsoDateTime($match['mtch_begin']);
		      $match['mtch_begin'] = $utd->getDateTime();
		      if ($match['mtch_begin']=='')$match['mtch_begin']=$time;

		      if ($match['mtch_status'] < WBS_MATCH_ENDED)
		      $utd = new utdate();
		      else
		      $utd->setIsoDateTime($match['mtch_end']);
		      $match['mtch_end'] = $utd->getDateTime();

		      // Search players of the match
		      $fields = array('regi_longName', 'regi_id', 'p2m_pairId',
		      'p2m_result');
		      $tables = array('p2m', 'i2p', 'registration');
		      $where = "regi_id = i2p_regiId".
	" AND i2p_pairId = p2m_pairId ".
	" AND p2m_matchId=$matchId";
		      $order = "p2m_posmatch";
		      $res = $this->_select($tables, $fields, $where, $order);

		      $i = -1;
		      $pairId = -1;
		      $match['mtch_pairId0']='';
		      $match['mtch_pairId1']='';
		      $match['mtch_result0']='';
		      $match['mtch_result1']='';
		      $match['mtch_player00']='';
		      $match['mtch_player01']='';
		      $match['mtch_player10']='';
		      $match['mtch_player11']='';
		      while ($er = $res->fetchRow(DB_FETCHMODE_ASSOC))
		      {
		      	//print_r($er);
		      	if ( $pairId != $er['p2m_pairId'])
		      	{
		      		$pairId = $er['p2m_pairId'];
		      		$i++;
		      		$j = 0;
		      	}
		      	else $j++;
		      	$match["mtch_pairId$i"] = $er['p2m_pairId'];
		      	$match["mtch_result$i"] = $er['p2m_result'];
		      	$match["mtch_player$i$j"] =$er['regi_longName'];
		      }
		      $utr =new utRound();
		      $match['mtch_step'] = $utr->getTieStep($match['tie_posRound']);
		      return $match;
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
			" AND pair_disci = $aDiscipline".
		    " AND p1.i2p_rankdefid=" .  $rank1['rank_rankdefid'].
		    " AND p1.i2p_classe=" . $rank1['rank_rank'];


		if ($aDiscipline != WBS_SINGLE)
		{
			// Classement actuel du joueur
			$cols  = array('rank_rankdefid', 'rank_rank');
			$oudonc = "rank_regiId = $regiId2 AND rank_discipline=$aDiscipline";
			$rank2 = $this->_selectFirst('ranks', $cols, $oudonc);
			$tables[]  = 'i2p p2';
			$where .= " AND p2.i2p_regiId = $regiId2".
			" AND p2.i2p_id != p1.i2p_id".
			" AND p2.i2p_pairId = pair_id".
		    " AND p2.i2p_rankdefid=" .  $rank2['rank_rankdefid'].
		    " AND p2.i2p_classe=" . $rank2['rank_rank'];
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

	// {{{ getMatchId
	/**
	 * Retrieve the pair Id of two players
	 *
	 * @access private
	 * @param  integer  $regiId1  Id of the first player of the pair
	 * @param  integer  $regiId2  Id of the second player of the pair -1 if none
	 * @param  integer  $disci    Discipline to search
	 * @return integer id of the pair
	 */
	function getMatchId($eventId, $matchNum)
	{

		// Search the match
		$fields = array('mtch_id');
		$tables = array('ties', 'rounds', 'draws',
 		      'matchs' );

		$where = "mtch_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId=$eventId".
	" AND mtch_num=$matchNum";

		$matchId = $this->_selectFirst($tables, $fields, $where);
		return $matchId;
	}
	// }}}
}
?>
