<?php
/*****************************************************************************
 !   Module     : live
 !   File       : $Source: /cvsroot/aotb/badnet/src/live/base_V.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.12 $
 !   Author     : G.CANTEGRIL
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/01/18 07:51:18 $
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
include_once dirname(__FILE__)."/../utils/utbase.php";
include_once dirname(__FILE__)."/../utils/utimg.php";
include_once dirname(__FILE__)."/../utils/utscore.php";
include_once dirname(__FILE__)."/../draws/draws.inc";
include_once dirname(__FILE__)."/../utils/objmatch.php";


/**
 * Access database function for visitor  for live
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class liveBase_V extends utbase
{

	// {{{ properties
	// }}}


	// {{{ getMatches
	/**
	 * Return the matchs for an individual event
	 *
	 * @access public
	 * @param  integer  $tieId  id of the tie
	 * @return array   information of the matchs if any
	 */
	function getMatches()
	{
		$date = new utdate();
		// Select the matchs of the event for the current day
		$eventId = utvars::getEventId();
		$fields = array('mtch_id', 'draw_stamp','rund_name',
		      'tie_posRound', 'tie_posRound as pair1',
		      'tie_posRound as pair2',
		      'mtch_court', 'mtch_begin',
		      'tie_schedule', 'rund_stamp', 
		      'rund_drawId', 'rund_type','mtch_status', 'tie_place');
		$tables = array('matchs', 'ties', 'rounds', 'draws');
		$where = 	"mtch_tieId = tie_id".
	" AND tie_roundId=rund_id".
	" AND rund_drawId=draw_id".
	" AND tie_isBye=0".
	" AND draw_eventId=$eventId".
	" AND mtch_status <= ".WBS_MATCH_LIVE.
	" AND date(tie_schedule) ='". $date->getIsoDate()."'";
		$order = "tie_schedule, mtch_num";
		$res = $this->_select($tables, $fields, $where, $order);

		// Construc a table with the match
		$utd = new utdate();
		$ut = new utils();
		$matches = array();
		$uts = new utscore();
		$utr = new utround();
		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $utd->setIsoDateTime($match['mtch_begin']);
	  $match['mtch_begin']= $utd->getTime();
	  $line[0]['value'] = $match['draw_stamp'];
	  $step = $utr->getTieStep($match['tie_posRound'],
	  $match['rund_type']);
	  $match['tie_posRound'] = $ut->getLabel($step);
	  switch($match['rund_type'])
	  {
	  	case WBS_ROUND_GROUP:
	  		$match['tie_posRound'] = $step;
	  		$action = DRAW_GROUPS_DISPLAY;
	  		break;
	  	case WBS_ROUND_QUALIF:
	  		$action = DRAW_QUALIF_DISPLAY;
	  		break;
	  	case WBS_ROUND_MAINDRAW:
	  		$action = DRAW_FINAL_DISPLAY;
	  		break;
	  	default :
	  		$action=DRAW_FINAL_DISPLAY;
	  		break;
	  }
	  $line[0]['action'] = array(KAF_UPLOAD, 'draws', $action,
	  $match['rund_drawId']);
	  $match['draw_stamp'] = $line;
	  $match['pair1'] = '';
	  $match['pair2'] = '';
	  if ($match['mtch_status'] != WBS_MATCH_LIVE)
	  $match['mtch_court'] = '';
	  $matches[$match['mtch_id']] = $match;
	  $matchIds[] = $match['mtch_id'];
		}

		if (isset($matchIds))
		{
			/*
	  // Find the id of the pairs of the matches
	  $ids = implode(',', $matchIds);
	  $fields = array('p2m_pairId', 'p2m_matchId', 'p2m_result');
	  $tables = array('p2m');
	  $where  = "p2m_matchId IN (".$ids.')';
	  $order = 'p2m_matchId, p2m_pairId';
	  $res = $this->_select($tables, $fields, $where, $order);
	  $pairs = array();
	  while($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  {
	  	$pairsId[] = $entry['p2m_pairId'];
	  	$pairs[$entry['p2m_pairId']] = $entry;
	  }

	  // Retrieve players name of the pairs
	  $rows = array();
	  if (isset($pairsId))
	  {
	  	$ids = implode(',', $pairsId);
	  	$fields = array('i2p_pairId', 'regi_longName',
			      'asso_noc','team_noc', 'regi_noc',
			      'regi_id', 'regi_rest', 'regi_court');
	  	$tables = array('members', 'registration', 'i2p', 'teams',
			      'a2t', 'assocs');
	  	$where = "mber_id = regi_memberId".
		" AND regi_id = i2p_regiId".
		" AND regi_teamId = team_id".
		" AND team_id = a2t_teamId".
		" AND asso_id = a2t_assoId".
		" AND i2p_pairId IN ($ids)";

	  	$order = "i2p_pairId, mber_sexe, regi_shortName";
	  	$res = $this->_select($tables, $fields, $where, $order);

	  	// For each player
	  	while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  	{
	  		// Get his pair
	  		$pair = $pairs[$entry['i2p_pairId']];
	  		$match = $matches[$pair['p2m_matchId']];

	  		// Calcul the name to display
	  		// and the status of the player
	  		$value = $entry['regi_longName'];
	  		$line = array();
	  		$line['value'] = $value;
	  		$line['action'] = array(KAF_UPLOAD, 'resu', KID_SELECT, $entry['regi_id']);
	  		$pair['players'][] = $line;
	  		$pairs[$entry['i2p_pairId']] = $pair;
	  	}
	  }
*/
	foreach ($matchIds as $matchId)
	{
		$oMatch = new objmatch($matchId);
		$match = $matches[$matchId];

		$players = array();
	  	$line = array();
	  	$line['value'] = $oMatch->getFirstTopName(true, true);
	  	$id = $oMatch->getFirstTopId();
	  	if ($id>0) $line['action'] = array(KAF_UPLOAD, 'resu', KID_SELECT, $id);
	  	$players[] = $line;
	  	$line['value'] = $oMatch->getSecondTopName(true, true);
	  	$id = $oMatch->getSecondTopId();
	  	if ($id>0) $line['action'] = array(KAF_UPLOAD, 'resu', KID_SELECT, $id);
	  	$players[] = $line;
		$match['pair1'] = $players;
		
		$players = array();
	  	$line['value'] = $oMatch->getFirstBottomName(true, true);
	  	$id = $oMatch->getSecondBottomId();
	  	if ($id>0) $line['action'] = array(KAF_UPLOAD, 'resu', KID_SELECT, $id);
	  	$players[] = $line;
	  	$line['value'] = $oMatch->getSecondBottomName(true, true);
	  	$id = $oMatch->getSecondBottomId();
	  	if ($id>0) $line['action'] = array(KAF_UPLOAD, 'resu', KID_SELECT, $id);
	  	$players[] = $line;
		$match['pair2'] = $players;
		$matches[$matchId] = $match;
		unset ($oMatch);
	}

	  // Put pairs name to match def
	  // Update the status of the match
	  // according the status of the pairs
	  // Update the court of the match
	  /*
	  foreach ($pairs as $pair)
	  {
	  	if (isset($pair['players']))
	  	{
	  		// Get the match
	  		$match = $matches[$pair['p2m_matchId']];
	  		// Update the pair
	  		if (is_array($match['pair1'])) $match['pair2'] = $pair['players'];
	  		else $match['pair1'] = $pair['players'];
	  		// Register the match
	  		$matches[$pair['p2m_matchId']] = $match;
	  	}
	  }
*/
		}
		if (!count($matches))
		{
	  $matches['errMsg'] = "msgNoTies";
	  return $matches;
		}
		return $matches;
	}
	// }}}


	// {{{ getTies
	/**
	 * Return the table with ties of the current day
	 *
	 * @access public
	 * @return array   array of users
	 */
	function getTies($divId)
	{
		$date = new utdate();
		// Firt select the ties of the day
		$eventId = utvars::getEventId();
		$fields = array('tie_id', 'tie_schedule', 'team_id',
		      'team_name', 'asso_logo', 't2t_scoreW',
		      'rund_name', 'draw_name');
		$tables = array('teams', 't2t', 'ties', 'draws', 'rounds',
		      'assocs','a2t');
		$where = "t2t_teamId = team_id".
	" AND t2t_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND draw_id = '$divId'".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId".
	" AND asso_id = a2t_assoId".
	" AND a2t_teamId = team_id".
	" AND date(tie_schedule) ='". $date->getIsoDate()."'";
		$order = "tie_schedule, tie_id, t2t_posTie";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoTies";
	  return $infos;
		}

		$ties = array();
		$tieId=-1;
		while ($tmp = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $tmp['asso_logo'] = utimg::getPathFlag($tmp['asso_logo']);
	  if($tieId != $tmp['tie_id'])
	  {
	  	if($tieId != -1)
	  	$ties[] = $tie;
	  	$tie = $tmp;
	  	$tie['team_id2'] = -1;
	  	$tie['team_name2'] = '';
	  	$tie['asso_logo2'] = '';
	  	$tie['t2t_scoreW2'] = '';
	  	$tieId = $tmp['tie_id'];
	  }
	  else
	  {
	  	$tie['team_id2'] = $tmp['team_id'];
	  	$tie['team_name2'] = $tmp['team_name'];
	  	$tie['asso_logo2'] = $tmp['asso_logo'];
	  	$tie['t2t_scoreW2'] = $tmp['t2t_scoreW'];
	  }
		}
		$ties[] = $tie;
		return $ties;
	}
	// }}}


	// {{{ getMatchsTie
	/**
	 * Return the match of a tie
	 *
	 * @access public
	 * @param  integer  $tieId  id of the tie
	 * @return array   information of the matchs if any
	 */
	function getMatchsTie($tieId, $teamL, $teamR)
	{
		// Select the definition of the group
		$fields = array('tie_nbms', 'tie_nbws', 'tie_nbmd',
		      'tie_nbwd', 'tie_nbxd');
		$tables[] = 'ties';
		$where = "tie_id=$tieId";
		$res = $this->_select($tables, $fields, $where);

		$data = $res->fetchRow(DB_FETCHMODE_ORDERED);
		$defTie[WBS_MS] = $data[0];
		$defTie[WBS_LS] = $data[1];
		$defTie[WBS_MD] = $data[2];
		$defTie[WBS_LD] = $data[3];
		$defTie[WBS_MX] = $data[4];
		// Select the matchs of the tie
		$fields = array('mtch_id', 'mtch_discipline', 'mtch_order',
		      'mtch_tieId', 'mtch_status',  'mtch_court',
		      'mtch_score', 'mtch_begin', 'mtch_end' );
		$tables = array('matchs');
		$where = "mtch_del != ".WBS_DATA_DELETE.
	" AND mtch_tieId = $tieId";
		$order = "mtch_rank, mtch_discipline, mtch_order";
		$res = $this->_select($tables, $fields, $where, $order);

		// Construc a table with the match
		$utd = new utdate();
		$ut = new utils();
		while ($match = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $discipline = $match[1];
	  $match[1] = $ut->getLabel($discipline);
	  if ($defTie[$discipline]>1)
	  $match[1] .= " ".$match[2];
	  $utd->setIsoDateTime($match[7]);
	  $match[7]= $utd->getTime();
	  $start = $utd->getIsoDateTime();
	  $utd->setIsoDateTime($match[8]);
	  $match[8]= $utd->getTime();
	  $full = array_pad($match,11, "");
	  $full[9] = -$utd->getDiff($start);
	  if ($full[9] < 0) $full[9] = '';
	  $full[10] = utimg::getIcon($match[4]);
	  $full[2] = '';
	  $full[3] = '';
	  $full[4] = '';
	  //	  print_r($full);echo"<br>---------------------";
	  $matches[$match[0]] = $full;
		}

		// Select the players of the matchs of the tie
		$fields = array('mtch_id', 'p2m_result', 'regi_shortName',
		      'regi_teamId', 'i2p_pairId', 'rkdf_label');
		$tables = array('matchs', 'p2m', 'i2p', 'registration',
		      'ranks', 'rankdef');
		$where = "mtch_del != ".WBS_DATA_DELETE.
	" AND mtch_tieId = $tieId".
	" AND mtch_id = p2m_matchId".
	" AND p2m_pairId = i2p_pairId".
	" AND i2p_regiId = regi_id".
	" AND rank_regiId = regi_id".
	" AND rank_rankdefId = rkdf_id".
	" AND rank_disci = mtch_discipline";
		$order = "1, regi_teamId";

		$res = $this->_select($tables, $fields, $where, $order);

		// Construc a table with the match
		$uts = new utscore();
		$teamId=-1;
		while ($player = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $match = $matches[$player[0]];
	  $uts->setScore($match[6]);
	  if ($player[3] == $teamL)
	  {
	  	if ($teamId != $player[3])
	  	{
	  		$teamId = $player[3];
	  		$match[2] .= "{$player[2]} - {$player[5]}";
	  		$match[11] = utimg::getIcon($player[1]);
	  		switch ($player[1])
	  		{
		    case WBS_RES_LOOSE:
		    case WBS_RES_LOOSEAB:
		    case WBS_RES_LOOSEWO:
		    	$match[6] = $uts->getLoosScore();
		    	break;
		    default:
		    	break;
	  		}
	  	}
	  	else
	  	{
	  		$match[2] .= "<br />{$player[2]} - {$player[5]}";
	  	}
	  }
	  else
	  {
	  	$teamId=-1;
	  	$match[3] .= "{$player[2]} - {$player[5]}<br />";
	  	$match[12] = utimg::getIcon($player[1]);
	  }
	  $matches[$player[0]] = $match;
		}

		// Calculate total of each column
		foreach ($matches as $matchId=>$match)
		{
	  $matchsList[] = $match;
		}
		return $matchsList;

	}
	// }}}


	// {{{ getTeams
	/**
	 * Return the teams of a tie
	 *
	 * @access public
	 * @param  integer  $tieId  id of the tie
	 * @return array   information of the matchs if any
	 */
	function getTeams($tieId)
	{
		// Select the teams of the tie
		$fields = array('team_id', 'team_name');
		$tables = array('teams', 't2t');
		$where = "t2t_teamId = team_id".
	" AND t2t_tieId = $tieId";
		$order = "t2t_posTie";

		$res = $this->_select($tables, $fields, $where, $order);
		$teams =array();
		while ($team = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $teams[]=$team;
		}
		return $teams;
	}
	// }}}


	// {{{ getLastResults
	/**
	 * Return the last ended matches
	 *
	 * @access public
	 * @param  integer  $tieId  id of the tie
	 * @return array   information of the matchs if any
	 */
	function getLastResults()
	{
		// Select the definition of the group
		/*$fields = array('tie_nbms', 'tie_nbws', 'tie_nbmd',
		 'tie_nbwd', 'tie_nbxd');
		 $tables[] = 'ties';
		 $where = "tie_id=$tieId";
		 $res = $this->_select($tables, $fields, $where);
		 $defTie[WBS_MS] = $data[0];
		 $defTie[WBS_LS] = $data[1];
		 $defTie[WBS_MD] = $data[2];
		 $defTie[WBS_LD] = $data[3];
		 $defTie[WBS_MX] = $data[4];
		 */
		// Select the matchs ended
		$eventId = utvars::getEventId();
		$date = new utdate();

		$fields = array('mtch_id', 'draw_stamp', 'rund_name',
		      'tie_posRound', 'regi_longName',
		      'team_stamp', 'mtch_score', 'mtch_begin', 
		      'mtch_end',  'mtch_status',  'mtch_court',
		      'mtch_order', 'p2m_pairId', 'regi_id', 
		      'rund_type');
		$tables = array('matchs', 'ties', 'rounds', 'draws',
		      'p2m', 'i2p', 'registration', 'teams');
		$where = "mtch_del != ".WBS_DATA_DELETE.
	" AND mtch_tieid = tie_id".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND team_id = regi_teamId".
	" AND draw_eventId = $eventId".
	" AND mtch_status >=".WBS_MATCH_ENDED.
	" AND mtch_id = p2m_matchId".
	" AND p2m_pairId = i2p_pairId".
	" AND i2p_regiId = regi_id".
	" AND date(mtch_end) ='". $date->getIsoDate()."'";
		$order = "mtch_end DESC, mtch_id, p2m_result";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoTies";
	  return $infos;
		}

		// Construc a table with the match
		$utd = new utdate();
		$ut = new utils();
		$utr = new utround();
		$matchId = -1;
		$pairId = -1;
		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  if ($matchId != $match['mtch_id'])
	  {
	  	if ($matchId != -1)
	  	{
	  		$row['team_stamp'] = $cell;
	  		$matches[] = $row;
	  	}
	  	$matchId = $match['mtch_id'];
	  	$pairId = $match['p2m_pairId'];
	  	$cell = array();
	  	$utd->setIsoDateTime($match['mtch_end']);
	  	$match['mtch_end']= $utd->getTime();
	  	$end = $utd->getIsoDateTime();
	  	$utd->setIsoDateTime($match['mtch_begin']);
	  	$match['mtch_begin']= $utd->getTime();
	  	$match['mtch_status'] = $utd->getDiff($end);
	  	if ($match['mtch_status'] < 0) $match['mtch_status'] = '';
	  	$row = $match;
	  	 
	  	$step = $utr->getTieStep($row['tie_posRound'],
	  	$row['rund_type']);
	  	if ($row['rund_type'] == WBS_ROUND_QUALIF ||
		  $row['rund_type'] == WBS_ROUND_MAINDRAW)
		  $row['tie_posRound'] = $ut->getLabel($step);
		  else
		  $step;
	  }
	  if ($pairId != $match['p2m_pairId'])
	  {
	  	$row['regi_longName'] = $cell;
	  	$pairId = $match['p2m_pairId'];
	  	$cell = array();
	  }
	  $line = array( 'value' =>$match['regi_longName'] . ' ('.
	  $match['team_stamp'] .')');
	  $cell[$match['regi_id']] = $line;
		}
		$row['team_stamp'] = $cell;
		$matches[] = $row;
		return $matches;
	}
	// }}}


}
?>