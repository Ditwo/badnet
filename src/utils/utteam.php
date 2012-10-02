<?php
/*****************************************************************************
 !   Module     : Utils
 !   File       : $Source: /cvsroot/aotb/badnet/src/utils/utteam.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.12 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/04/04 22:55:51 $
 ******************************************************************************/
require_once "utvars.php";
require_once "utbase.php";

/**
 * Classe utilitaire pour la gestion des tournois par equipes
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class utTeam extends utBase
{

	// {{{ properties
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
		$a2t['a2t_assoId'] = $infos['team_assoId'];
		unset($infos['team_assoId']);

		$postit = '';
		if (isset($infos['psit_texte']))
		{
	  $postit = $infos['psit_texte'];
	  unset($infos['psit_texte']);
		}
			
		$utd = new utdate();
		$utd->setFrDate($infos['team_date']);
		$infos['team_date'] = $utd->getIsoDateTime();
		// Team already exist
		if ($infos['team_id'] == -1)
		// New team
		{
			// Create an account for the team
	  $account['cunt_name'] = $infos['team_name'];
	  $account['cunt_eventId'] = utvars::getEventId();
	  $account['cunt_status'] = WBS_ACCOUNT_OPEN;
	  $account['cunt_code'] = 'NONE';
	  $res = $this->_insert('accounts', $account);

	  // Create the team
	  unset($infos['team_id']);
	  $infos['team_accountId'] = $res;
	  $infos['team_eventId'] = utvars::getEventId();
	  $res = $this->_insert('teams', $infos);

	  // Link the team  to the association
	  $teamId = $res;
	  $infos['team_id'] = $teamId;
	  $a2t['a2t_teamId'] = $res;
	  $res = $this->_insert('a2t', $a2t);

	  // Register the specials WO player for the team
	  if (utvars::isTeamEvent())
	  {
	  	$fields = array('regi_id');
	  	$tables = array('registration');
	  	$where = "regi_memberId=-1".
		" AND regi_teamId=$teamId";
	  	$res = $this->_select($tables, $fields, $where);
	  	if (!$res->numRows())
	  	{
	  		$fields = array();
	  		$fields['regi_memberId'] = -1;
	  		$fields['regi_teamId'] = $teamId;
	  		$fields['regi_eventId'] = $infos['team_eventId'];
	  		$fields['regi_date'] = date(DATE_FMT);
	  		$fields['regi_longName'] = '---WO---';
	  		$fields['regi_shortName'] = '-WO-';
	  		$res = $this->_insert('registration', $fields);
	  		$this->_addRank($res, WBS_MS, WBS_SINGLE);
	  		$this->_addRank($res, WBS_MD, WBS_DOUBLE);
	  		$this->_addRank($res, WBS_MX, WBS_MIXED);
	  	}
	  	 
	  	$fields = array('regi_id');
	  	$tables = array('registration');
	  	$where = "regi_memberId=-2".
		" AND regi_teamId=$teamId";
	  	$res = $this->_select($tables, $fields, $where);
	  	if (!$res->numRows())
	  	{
	  		$fields = array();
	  		$fields['regi_memberId'] = -2;
	  		$fields['regi_teamId'] = $teamId;
	  		$fields['regi_eventId'] = $infos['team_eventId'];
	  		$fields['regi_date'] = date(DATE_FMT);
	  		$fields['regi_longName'] = '---WO---';
	  		$fields['regi_shortName'] = '-WO-';
	  		$res = $this->_insert('registration', $fields);
	  		$this->_addRank($res, WBS_LS, WBS_SINGLE);
	  		$this->_addRank($res, WBS_LD, WBS_DOUBLE);
	  		$this->_addRank($res, WBS_MX, WBS_MIXED);
	  	}
	  }
		}

		$trans = array('team_name','team_stamp', 'team_cmt');
		$infos = $this->_updtTranslate('teams', $trans,
		$infos['team_id'], $infos);

		$where = "team_id=".$infos['team_id'];
		$res = $this->_update('teams', $infos, $where);
		return $infos['team_id'];
	}
	// }}}

	// {{{ _addRank
	/**
	 * Add or update a rank for a player
	 *
	 * @access private
	 * @param  integer $regiID  Register id of the player
	 * @param  integer $rank    Rank defintion Id
	 * @param  integer $average Point of the player
	 * @return mixed
	 */
	function _addRank($regiId, $disci, $aDiscipline)
	{
		// Update the ranking of registration
		$fields[] = 'rank_id';
		$tables[] = 'ranks';
		$where = "rank_disci=$disci".
	" AND rank_regiId=$regiId";
		$res = $this->_select($tables, $fields, $where);
		if ($res->numRows()) return;

		$fields = array('rkdf_id');
		$tables = array('rankdef');
		$order  = "rkdf_point";
		$res = $this->_select($tables, $fields, false, $order);
		$theRank = $res->fetchRow(DB_FETCHMODE_ORDERED);

		$rank = $theRank[0];
		$average = 0;
		$fields = array();
		$fields['rank_rankdefId'] = $rank;
		$fields['rank_average'] = $average;
		$fields['rank_disci'] = $disci;
		$fields['rank_discipline'] = $aDiscipline;
		$fields['rank_regiId'] = $regiId;
		$res = $this->_insert('ranks', $fields);
		return true;
	}
	// }}}

	// {{{ getTeamsGroup
	/**
	 * Return the list of the teams in a group
	 *
	 * @access public
	 * @param  integer  $groupId  Id of the group
	 * @return array   array of users
	 */
	function getTeamsGroup($groupId)
	{
		$fields = array('team_id', 'team_name', 'team_stamp',
		      'asso_name', 'team_captain', 'team_cmt', 
		      'asso_id', 'asso_logo', 'team_del', 'team_pbl', 
		      'team_noc', 'team_logo', 't2r_posRound');
		$tables = array('teams', 't2r', 'assocs', 'a2t');
		$where = "t2r_teamId = team_id".
	" AND t2r_roundId = $groupId".
	" AND a2t_assoId = asso_id ".
	" AND a2t_teamId = team_id ";
		$order = "t2r_posRound";
		$res = $this->_select($tables, $fields, $where, $order);
		$rows = array();
		$uti = new utimg();
		while ($team = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $team['iconPbl'] = $uti->getPubliIcon($team['team_del'],
	  $team['team_pbl']);
	  $rows[] = $team;
		}
		return $rows;
	}
	// }}}


	// {{{ getDiv
	/**
	 * Return the field of a division
	 *
	 * @access public
	 * @return array   array of users
	 */
	function getDiv($divId)
	{
		$fields = array('*');
		$tables[] = 'draws';
		$where = "draw_id = $divId";
		$res = $this->_select($tables, $fields, $where);
		$entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$fields = array('draw_name', 'draw_stramp');
		$entry = $this->_getTranslate('draws', $fields,
		$entry['draw_id'], $entry);
		return $entry;
	}
	// }}}


	// {{{ getDivs
	/**
	 * Return the list of the division
	 *
	 * @access public
	 * @return array   array of users
	 */
	function getDivs()
	{
		$eventId = utvars::getEventId();
		$fields = array('draw_id', 'draw_name');
		$where = "draw_eventId = $eventId ".
	" AND draw_del !=".WBS_DATA_DELETE.
	" AND draw_type =".WBS_EVENT_IC;
		$order = "2";
		$res = $this->_select('draws', $fields, $where, $order, 'draw');
		$rows = array();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $fields = array('draw_name');
	  $entry = $this->_getTranslate('draws', $fields,
	  $entry['draw_id'], $entry);
	  $rows[$entry['draw_id']] = $entry['draw_name'];
		}
		return $rows;
	}
	// }}}

	// {{{ getGroups
	/**
	 * Return the groups of a division
	 *
	 * @access public
	 * @param  string  division Id
	 * @return array   array of users
	 */
	function getGroups($divId)
	{
		$eventId = utvars::getEventId();
		$fields = array('rund_id', 'rund_name', 'rund_rankType', 'rund_type', 'rund_stamp',
		'rund_tieranktype', 'rund_nbms+rund_nbws+rund_nbmd+rund_nbwd+rund_nbas+rund_nbad +rund_nbxd as nb');
		$tables[] = 'rounds';
		$where = "rund_drawId = '$divId' ".
	" AND rund_del !=".WBS_DATA_DELETE;
		$order = "2";
		$res = $this->_select($tables, $fields, $where, $order);
		$rows=array();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $fields = array('rund_name', 'rund_stamp');
	  $entry = $this->_getTranslate('rounds', $fields,
	  $entry['rund_id'], $entry);
	  $rows[$entry['rund_id']] = $entry;
		}
		return $rows;
	}
	// }}}

	// {{{ getFullGroups
	/**
	 * Return the groups of a division
	 *
	 * @access public
	 * @param  string  division Id
	 * @return array   array of users
	 */
	function getFullGroups($divId)
	{
		$eventId = utvars::getEventId();
		$fields = array('DISTINCT rund_id', 'rund_name', 'rund_size',
		      'rund_type as rund_typeName', 'tie_nbms', 
		      'tie_nbws', 'tie_nbmd', 
		      'tie_nbwd', 'tie_nbxd', 'rund_type');
		$tables = array('rounds LEFT JOIN ties ON tie_roundId = rund_id');
		$where = "rund_drawId = '$divId' ".
	" AND  rund_del !=".WBS_DATA_DELETE;
		$order = "2";
		$res = $this->_select($tables, $fields, $where, $order);
		$rows=array();
		$ut = new utils();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $fields = array('rund_name');
	  $entry = $this->_getTranslate('rounds', $fields,
	  $entry['rund_id'], $entry);

	  $entry['rund_typeName'] = $ut->getSmaLabel($entry['rund_type']);
	  $rows[] = $entry;
		}
		return $rows;
	}
	// }}}

	// {{{ updateTiesResult
	/**
	 * Update the result of the teams for each ties of the designed divs
	 *
	 * @access private
	 * @param  integer  $pairId   Id of the pair
	 * @param  integer  $matchId  Id of the match
	 * @return integer id of the team
	 */
	function updateTiesResult($divId)
	{
		$eventId = utvars::getEventId();
		$fields = array('tie_id', 't2t_teamId', 't2t_penalties',
		      't2t_penaltiesO');
		$tables = array('rounds', 'ties', 't2t');
		$where = "rund_drawId = $divId ".
	" AND (rund_type =".WBS_TEAM_GROUP.
	" OR rund_type =".WBS_TEAM_BACK.
	" ) AND tie_roundId = rund_id".
	" AND tie_roundId = rund_id".
	" AND t2t_tieId = tie_id".
	" AND t2t_result!=".WBS_TIE_NOTPLAY;
		$res = $this->_select($tables, $fields, $where);
		while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$this->updateTeamResult($data['t2t_teamId'], $data['tie_id'],
		$data['t2t_penalties'], $data['t2t_penaltiesO']);
	}

	// {{{ updateTeamResult
	/**
	 * Update the result of the teams for the designed tie
	 *
	 * @access private
	 * @param  integer  $pairId   Id of the pair
	 * @param  integer  $matchId  Id of the match
	 * @return integer id of the team
	 */
	function updateTeamResult($teamId, $tieId, $penaltiesTeam=NULL,
	$penaltiesOpponent=NULL, $woTeam=NULL,
	$woOpponent=NULL)
	{
		if ($penaltiesTeam==NULL &&
		$penaltiesOpponent==NULL)
		{
	  $fields = array('t2t_penalties', 't2t_penaltiesO');
	  $where = "t2t_teamId=$teamId".
	    " AND t2t_tieId=$tieId";
	  $res  = $this->_select('t2t', $fields, $where);
	  $data = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $penaltiesTeam = $data['t2t_penalties'];
	  $penaltiesOpponent = $data['t2t_penaltiesO'];
		}

		// Get the point of the result of a match
		$fields = array('rund_matchWin', 'rund_matchLoose', 'rund_matchWO',
		      'rund_matchRtd', 'rund_tieranktype', 'rund_tiematchdecisif', 'rund_tiematchnum');
		$tables = array('rounds', 'ties');
		$where  = "tie_id = $tieId".
	" AND tie_roundId = rund_id";
		$res = $this->_select($tables, $fields, $where);

		$points = $res->fetchRow(DB_FETCHMODE_ASSOC);
		//print_r($points);echo "<br>";

		// Search all match of the team in the concerned tie
		$fields = array('DISTINCT mtch_id', 'p2m_result', 'mtch_score', 'mtch_discipline', 'mtch_rank', 'mtch_order');
		$tables = array('matchs', 'ties', 'p2m', 'i2p', 'registration');
		$where = "regi_teamId=$teamId".
	" AND tie_id= $tieId".
	" AND mtch_tieId = tie_id".
	" AND mtch_id = p2m_matchId".
	" AND p2m_pairId = i2p_pairId".
	" AND i2p_regiId = regi_id".
	" AND mtch_status >=".WBS_MATCH_ENDED;
		$res = $this->_select($tables, $fields, $where);

		// Calculate the points of the team
		$matchW=0;
		$matchL=0;
		$gameW=0;
		$gameL=0;
		$pointW=0;
		$pointL=0;
		$nbPointW=0;
		$nbPointL=0;
		$uts = new utscore();
		while ($matchRes = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($matchRes['mtch_discipline'] == $points['rund_tiematchdecisif'] &&
				$matchRes['mtch_order'] == $points['rund_tiematchnum'])
				{
				$resultmatchdecisif = $matchRes["p2m_result"];
				}
			$uts->setScore($matchRes['mtch_score']);
	  //echo "resultat=".$matchRes["p2m_result"];
	  switch ($matchRes["p2m_result"])
	  {
	  	case WBS_RES_WINWO:
	  	case WBS_RES_WIN:
	  	case WBS_RES_WINAB:
	  		$matchW++;
	  		$gameW += (int)$uts->getNbWinGames();
	  		$gameL += (int)$uts->getNbLoosGames();
	  		$pointW += (int)$uts->getNbWinPoints();
	  		$pointL += (int)$uts->getNbLoosPoints();
	  		$nbPointW += $points['rund_matchWin'];
	  		if ($matchRes["p2m_result"] == WBS_RES_WIN)
	  		$nbPointL += $points['rund_matchLoose'];
	  		else if ($matchRes["p2m_result"] == WBS_RES_WINAB)
	  		$nbPointL += $points['rund_matchRtd'];
	  		else
	  		$nbPointL += $points['rund_matchWO'];
	  		break;

	  	case WBS_RES_LOOSEWO:
	  	case WBS_RES_LOOSE:
	  	case WBS_RES_LOOSEAB:
	  		$matchL++;
	  		$gameL += (int)$uts->getNbWinGames();
	  		$gameW += (int)$uts->getNbLoosGames();
	  		$pointL += (int)$uts->getNbWinPoints();
	  		$pointW += (int)$uts->getNbLoosPoints();
	  		$nbPointL += $points['rund_matchWin'];
	  		if ($matchRes["p2m_result"] == WBS_RES_LOOSE) $nbPointW += $points['rund_matchLoose'];
	  		else if ($matchRes["p2m_result"] == WBS_RES_LOOSEAB) $nbPointW += $points['rund_matchRtd'];
	  		else $nbPointW += $points['rund_matchWO'];
	  		break;
	  }
	  //echo "---score=".$nbPointW.'/'.$nbPointL."<br>";
		}
		// Calculate the results of the tie
		$result= WBS_TIE_WIN;
		if ($penaltiesTeam != NULL) $nbPointW += $penaltiesTeam;
		if ($penaltiesOpponent != NULL) $nbPointL += $penaltiesOpponent;

		if (!is_null($woTeam) && is_null($woOpponent)) $result = WBS_TIE_LOOSEWO;
		else if (is_null($woTeam) && !is_null($woOpponent)) $result = WBS_TIE_WINWO;
		else if (!is_null($woTeam) && !is_null($woOpponent)) $result = WBS_TIE_EQUAL;
		else if (!$nbPointW && !$nbPointL) $result = WBS_TIE_NOTPLAY;
		else if ($nbPointW < $nbPointL) $result=WBS_TIE_LOOSE;
		else if ($nbPointW == $nbPointL)
		{
			// En cas d'egalite, voir l'option choisie
			// Egalite autorisee
			if ($points['rund_tieranktype'] == WBS_CALC_EQUAL) $result = WBS_TIE_EQUAL;
			// Resultat fonction de la difference de match, jeu, points gagne
			else if ($points['rund_tieranktype'] == WBS_CALC_RANK)
			{
	  			$result=WBS_TIE_EQUAL_PLUS;
	  			if ($matchW < $matchL) $result=WBS_TIE_EQUAL_MINUS;
	  			else if ($matchW == $matchL)
				{
	  				if ($gameW < $gameL) $result=WBS_TIE_EQUAL_MINUS;
	  				else if ($gameW == $gameL)
	 				{
	  					if ($pointW < $pointL) $result=WBS_TIE_EQUAL_MINUS;
	  					else if ($pointW == $pointL) $result=WBS_TIE_EQUAL;
	  				}
	  			}
			}
			// Resultat fonction d'un match particulier
			else
			{
				switch( $resultmatchdecisif )
				{
				  	case WBS_RES_WINWO:
	  				case WBS_RES_WIN:
	  				case WBS_RES_WINAB:
	  					$result=WBS_TIE_EQUAL_PLUS;
						break;
	  				default:
	  					$result=WBS_TIE_EQUAL_MINUS;
	  					break;
				}
			}
		}
		
		// Updating database
		$fields = array();
		$fields['t2t_matchW']  = $matchW;
		$fields['t2t_matchL']  = $matchL;
		$fields['t2t_setW']    = $gameW;
		$fields['t2t_setL']    = $gameL;
		$fields['t2t_pointW']  = $pointW;
		$fields['t2t_pointL']  = $pointL;
		$fields['t2t_scoreW']  = $nbPointW;
		$fields['t2t_scoreL']  = $nbPointL;
		$fields['t2t_result']  = $result;
		if (!is_null($penaltiesTeam))
		$fields['t2t_penalties']  = $penaltiesTeam;
		if (!is_null($penaltiesOpponent))
		$fields['t2t_penaltiesO']  = $penaltiesOpponent;
		$where = "t2t_teamId=$teamId".
	" AND t2t_tieId=$tieId";
		$res = $this->_update('t2t', $fields, $where);
	}
	// }}}

	// {{{ updateGroupRank
	/**
	 * Calculate the ranking of each team in the given group
	 *
	 * @access private
	 * @param  integer  $groupId   Id of the group
	 * @return integer id of the team
	 */
	function updateGroupRank($groupId)
	{
		// Get the point of the result of a tie
		$fields = array('rund_tieWin', 'rund_tieEqualPlus', 'rund_tieEqual', 'rund_tieEqualMinus', 'rund_tieLoose',
		      'rund_tieWO', 'rund_rankType');
		$tables = array('rounds');
		$where = "rund_id = $groupId";
		$res = $this->_select($tables, $fields, $where);

		$points = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$rankType = $points['rund_rankType'];

		// Calculer pour chaque equipe les cumuls de
		// match gagnes, set gagnes et points marques
		$fields = array('t2t_teamId as teamId',
		      'sum(t2t_matchW) - sum(t2t_matchL) as deltaMatch',
		      'sum(t2t_setW) -  sum(t2t_setL) as deltaGame',
		      'sum(t2t_pointW) - sum(t2t_pointL) as deltaPoint',
		      't2r_penalties');
		$tables = array('t2t', 'ties', 't2r');
		$where = "tie_roundId ='$groupId'".
	" AND t2t_tieId = tie_id".
	" AND t2r_teamId = t2t_teamId".
	" AND t2r_roundId = '$groupId'".
	" GROUP BY t2t_teamId";
		$order = '1';
		$res = $this->_select($tables, $fields, $where, $order);
		$rows = array();

		// Calculer pour chaque equipe le nombre de rencontre gagnees
		// et le nombre de rencontre perdues
		$fields = array('count(*)');
		$tables = array('t2t', 'ties');
		$ou = "tie_roundId ='$groupId'".
	" AND t2t_tieId = tie_id";
		while ($team = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $where = "$ou AND t2t_teamId=".$team['teamId'].
	    " AND (t2t_result =".WBS_TIE_WIN.
	    " OR t2t_result =".WBS_TIE_WINWO.')';
	  $r = $this->_select($tables, $fields, $where);
	  $tmp = $r->fetchRow(DB_FETCHMODE_ORDERED);
	  $team['tieW']=$tmp[0];

	  $where = "$ou AND t2t_teamId=".$team['teamId'].
	    " AND t2t_result =".WBS_TIE_LOOSE;
	  $r = $this->_select($tables, $fields, $where);
	  $tmp = $r->fetchRow(DB_FETCHMODE_ORDERED);
	  $team['tieL']=$tmp[0];

	  $where = "$ou AND t2t_teamId=".$team['teamId'].
	    " AND t2t_result =".WBS_TIE_EQUAL;
	  $r = $this->_select($tables, $fields, $where);
	  $tmp = $r->fetchRow(DB_FETCHMODE_ORDERED);
	  $team['tieE']=$tmp[0];

	  $where = "$ou AND t2t_teamId=".$team['teamId'].
	    " AND t2t_result =".WBS_TIE_EQUAL_MINUS;
	  $r = $this->_select($tables, $fields, $where);
	  $tmp = $r->fetchRow(DB_FETCHMODE_ORDERED);
	  $team['tieEM']=$tmp[0];

	  $where = "$ou AND t2t_teamId=".$team['teamId'].
	    " AND t2t_result =".WBS_TIE_EQUAL_PLUS;
	  $r = $this->_select($tables, $fields, $where);
	  $tmp = $r->fetchRow(DB_FETCHMODE_ORDERED);
	  $team['tieEP']=$tmp[0];

	  $where = "$ou AND t2t_teamId=".$team['teamId'].
	    " AND t2t_result =".WBS_TIE_LOOSEWO;
	  $r = $this->_select($tables, $fields, $where);
	  $tmp = $r->fetchRow(DB_FETCHMODE_ORDERED);
	  $team['tieWO']=$tmp[0];

	  $team['points'] = $team['tieW']*$points['rund_tieWin'];
	  $team['points'] += $team['tieEP']*$points['rund_tieEqualPlus'];
	  $team['points'] += $team['tieL']*$points['rund_tieLoose'];
	  $team['points'] += $team['tieEM']*$points['rund_tieEqualMinus'];
	  $team['points'] += $team['tieWO']*$points['rund_tieWO'];
	  $team['points'] += $team['tieE']*$points['rund_tieEqual'];
	  $team['points'] += $team['t2r_penalties'];
	  $team['point'] = $team['points']*100;
	  $team['bonus'] = 0;

	  $team['tieL'] += $team['tieWO'];
	  $rows[] = $team;
		}
		$nbTeams = count($rows);

		//$this->debug($rows);
		// S'il y  a plus de deux equipes a egalites
		// on regarde le nombre de matchs gagnes/perdus
		if ($this->_checkEqual($rows, $groupId, $rankType))
		{
	  for ($i=0; $i<$nbTeams; $i++)
	  {
	  	$ti =$rows[$i];
	  	for ($j=$i+1; $j<$nbTeams; $j++)
	  	{
	  		$tj =$rows[$j];
	  		if ($ti['point'] == $tj['point'])
	  		{
	  			if ($ti['deltaMatch'] > $tj['deltaMatch'])
	  			{
	  				$ti['bonus'] += 10;
	  				$rows[$i] = $ti;
	  			}
	  			if ($ti['deltaMatch'] < $tj['deltaMatch'])
	  			{
	  				$tj['bonus'] += 10;
	  				$rows[$j] = $tj;
	  			}
	  		} //end if team equal
	  	} //next $j
	  } // next $i
		}

		//$this->debug($rows);
		// S'il y  a toujours plus de deux equipes a egalites
		// on regarde le nombre de sets gagnes/perdus
		if ($this->_checkEqual($rows, $groupId, $rankType))
		{
	  for ($i=0; $i<$nbTeams; $i++)
	  {
	  	$ti =$rows[$i];
	  	for ($j=$i+1; $j<$nbTeams; $j++)
	  	{
	  		$tj =$rows[$j];
	  		if ($ti['point'] == $tj['point'])
	  		{
	  			if ($ti['deltaGame'] > $tj['deltaGame'])
	  			{
	  				$ti['bonus'] += 1;
	  				$rows[$i] = $ti;
	  			}
	  			if ($ti['deltaGame'] < $tj['deltaGame'])
	  			{
	  				$tj['bonus'] += 1;
	  				$rows[$j] = $tj;
	  			}
	  		} //end if team equal
	  	} //next $j
	  } // next $i
		}

		//$this->debug($rows);
		// S'il y  a toujours plus de deux equipes a egalites
		// on regarde le nombre de points gagnes/perdus
		if ($this->_checkEqual($rows, $groupId, $rankType))
		{
	  for ($i=0; $i<$nbTeams; $i++)
	  {
	  	$ti =$rows[$i];
	  	for ($j=$i+1; $j<$nbTeams; $j++)
	  	{
	  		$tj =$rows[$j];
	  		if ($ti['point'] == $tj['point'])
	  		{
	  			if ($ti['deltaPoint'] > $tj['deltaPoint'])
	  			{
	  				$ti['bonus'] += 0.1;
	  				$rows[$i] = $ti;
	  			}
	  			if ($ti['deltaPoint'] < $tj['deltaPoint'])
	  			{
	  				$tj['bonus'] += 0.1;
	  				$rows[$j] = $tj;
	  			}
	  		} //end if team equal
	  	} //next $j
	  } // next $i
		}


		//$this->debug($rows);
		// Calcul du classement de chaque equipe
		for ($i=0; $i<$nbTeams; $i++)
		{
	  $ti =$rows[$i];
	  $ti['rank']=0;
	  for ($j=0; $j<$nbTeams; $j++)
	  {
	  	$tj =$rows[$j];
	  	if ($tj['point']+$tj['bonus'] > $ti['point']+$ti['bonus'])
	  	{
	  		$ti['rank'] ++;
	  	}
	  	$rows[$j] = $tj;
	  }
	  $rows[$i] = $ti;
		}

		//$this->debug($rows);
		// Mise a jour de la base
		$fields = array();
		for ($i=0; $i<$nbTeams; $i++)
		{
	  $ti =$rows[$i];
	  $fields['t2r_rank']   = $ti['rank'];
	  $fields['t2r_points'] = $ti['points'];
	  $fields['t2r_tieW']   = $ti['tieW'];
	  $fields['t2r_tieL']   = $ti['tieL'];
	  $fields['t2r_tieEP']  = $ti['tieEP'];
	  $fields['t2r_tieEM']  = $ti['tieEM'];
	  $fields['t2r_tieE']   = $ti['tieE'];
	  $fields['t2r_tieWO']  = $ti['tieWO'];
	  $where = "t2r_teamId=".$ti['teamId'].
	    " AND t2r_roundId =".$groupId;
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
	function _checkEqual(&$rows, $groupId, $rankType)
	{
		$nbTeams = count($rows);
		for ($j=0; $j<$nbTeams; $j++)
		{
	  $tj = $rows[$j];
	  $tj['point'] += $tj['bonus'];
	  $tj['bonus'] = 0;
	  $rows[$j] = $tj;
		}
		if ($rankType == WBS_CALC_RANK) return true;

		$isEqual = false;
		for ($i=0; $i<$nbTeams; $i++)
		{
	  $ti =$rows[$i];
	  $teamEqual = 0;
	  $nbEqual   = 0;
	  for ($j=0; $j<$nbTeams; $j++)
	  {
	  	if ($i == $j) continue;
	  	$tj = $rows[$j];
	  	$tj['point'] += $tj['bonus'];
	  	$tj['bonus'] = 0;
	  	$rows[$j] = $tj;
	  	if ($ti['point'] == $tj['point'])
	  	{
	  		$teamEqual = $tj['teamId'];
	  		$nbEqual++;
	  	}
	  }
	  if ($nbEqual == 1)
	  {
	  	$winner = $this->_getWinTeam($groupId, $ti['teamId'],
	  	$teamEqual);
	  	if (is_array($winner)) return $winner;
	  	if ($winner == $ti['teamId']) $ti['point'] += 0.01;
	  	$rows[$i] = $ti;
	  }
	  if ($nbEqual > 1) $isEqual = true;
		}
		return $isEqual;
	}

	// {{{ _getWinTeam
	/**
	 * Return the winner team of a tie
	 *
	 * @access private
	 * @param  integer  $groupId  Id of the group
	 * @param  integer  $team1    Id of the first teams
	 * @param  integer  $team1    Id of the second team
	 * @return integer id of the team
	 */
	function _getWinTeam($groupId, $team1, $team2)
	{
		// Search the ties of the first team
		$fields = array('tie_id', 't2t_result',
        't2t_matchW', 't2t_matchL',
        't2t_setW', 't2t_setL',
        't2t_pointW', 't2t_pointL'
        );
        $tables = array('ties', 't2t');
        $where = "tie_roundId = $groupId"
        . " AND t2t_teamId = $team1"
        . " AND t2t_tieId = tie_id";
        $res = $this->_select($tables, $fields, $where);
        while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
        	$tieIds[] = $tie;
        }
        // Search the tie of the second team
        $where = "tie_roundId = $groupId"
        . " AND t2t_teamId = $team2"
        . " AND t2t_tieId = tie_id";
        $res = $this->_select($tables, $fields, $where);
        $nbTies = count($tieIds);

        $nbTie = 0;
        $deltaTie   = 0;
        $deltaMatch = 0;
        $deltaGame  = 0;
        $deltaPoint = 0;

        while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
        	for ($i=0; $i<$nbTies;$i++)
        	{
        		$tieId = $tieIds[$i];
        		if ($tie['tie_id'] == $tieId['tie_id'])
        		{
        			$nbTie++;
        			if ($tieId['t2t_result'] == WBS_TIE_WIN) $deltaTie++;
        			if ($tieId['t2t_result'] == WBS_TIE_LOOSE) $deltaTie--;
        			$deltaMatch += ($tieId['t2t_matchW'] - $tieId['t2t_matchL']);
        			$deltaGame += ($tieId['t2t_setW'] - $tieId['t2t_setL']);
        			$deltaPoint += ($tieId['t2t_pointW'] - $tieId['t2t_pointL']);
        		}
        	}
        }
        if ( $deltaTie > 0 ) return $team1;
        else if ($deltaTie < 0) return $team2;
        else if ($deltaMatch > 0) return $team1;
        else if ($deltaMatch < 0) return $team2;
        else if ($deltaGame > 0) return $team1;
        else if ($deltaGame < 0) return $team2;
        else if ($deltaPoint > 0) return $team1;
        else if ($deltaPoint < 0) return $team2;
        else return 0;
	}
	// }}}

	function debug($rows)
	{
		if (!is_array($rows))
		{
	  echo "${$rows}=$rows<br>";
	  return;
	}

	$www = "<table border=\"1\">";
	$header = '';
	$lines  = '';
	foreach($rows as $row)
	{
		if (!is_array($row))
		$lines .= "<tr><td>${$row}</td><td>$row</td></tr>";
		else
		{
			$lines .= "<tr>";
			$header = "<tr>";
			foreach($row as $col=>$value)
			{
		  $header .= "<td>$col</td>";
		  $lines .= "<td>$value</td>";
			}
			$lines .= "</tr>";
			$header .= "</tr>";
		}
}
$www .= $header;
$www .= $lines;
$www .= "</table>";
echo $www;
}
}
?>