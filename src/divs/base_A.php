<?php
/*****************************************************************************
 !   Module     : Divisions
 !   File       : $Source: /cvsroot/aotb/badnet/src/divs/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.8 $
 !   Author     : G.CANTEGRIL
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2006/12/08 17:40:08 $
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

require_once "utils/utbase.php";
require_once "utils/utdraw.php";
require_once "utils/utko.php";

/**
 * Acces to the dababase for divisions
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class divsBase_A extends utbase
{

	// {{{ properties
	// }}}


	// {{{ getDrawTeam
	/**
	 * Return the list of the ties of a group
	 *
	 * @access public
	 * @param integr $groupId  Id of the group
	 * @return array   array of users
	 */
	function getDrawTeam($groupId, $tiesNum)
	{
		$fields = array('team_name');
		$tables = array('ties', 't2t', 'teams');
		$where = "tie_roundId = '$groupId'".
	" AND t2t_tieId = tie_id".
	" AND t2t_teamId = team_id";
		foreach($tiesNum as $tieNum)
		{
	  $ou = $where." AND tie_posRound=$tieNum".
	    " AND t2t_posTie=".WBS_TEAM_TOP;
	  $res = $this->_select($tables, $fields, $ou);
	  if ($res->numRows())
	  {
	  	$res = $this->_select($tables, $fields, $ou);
	  	$data = $res->fetchRow(DB_FETCHMODE_ORDERED);
	  	$rows[] = $data[0];
	  }
	  else
	  $rows[] = "";

	  $ou = $where." AND tie_posRound=$tieNum".
	    " AND t2t_posTie=".WBS_TEAM_BOTTOM;
	  $res = $this->_select($tables, $fields, $ou);
	  if ($res->numRows())
	  {
	  	$res = $this->_select($tables, $fields, $ou);
	  	$data = $res->fetchRow(DB_FETCHMODE_ORDERED);
	  	$rows[] = $data[0];
	  }
	  else
	  $rows[] = "";
		}
		return $rows;
	}
	// }}}

	// {{{ getTies
	/**
	 * Return the list of the ties of a group
	 *
	 * @access public
	 * @param integr $groupId  Id of the group
	 * @return array   array of users
	 */
	function getTies($groupId)
	{
		$fields = array('tie_id', 'tie_posRound', 'team_name');
		$tables = array('ties', 'teams', 't2t');
		$where = "tie_roundId = '$groupId'".
	" AND t2t_tieId = tie_id".
	" AND t2t_teamId = team_id";
		$order = "tie_posRound ";
		$res = $this->_select($tables, $fields, $where, $order);

		$rows = array();
		$pos=-1;
		$i = -1;
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  if ($entry[1] != $pos)
	  {
	  	$i++;
	  	$rows[$i] = $entry;
	  }
	  else
	  {
	  	$rows[$i][] = $entry[2];
	  }
	  $pos = $entry[1];
		}
		return $rows;
	}
	// }}}

	// {{{ getGroup
	/**
	 * Return the informations of a group
	 *
	 * @access public
	 * @param  integer  $groupId   id of the group
	 * @return array   information of the user if any
	 */
	function getGroup($groupId)
	{
		$ut = new utils();
		$eventId = utvars::getEventId();
		$fields = array('rund_id', 'rund_name', 'rund_size', 'rund_rankType',
		      'rund_type', 'rund_matchWin', 'rund_matchLoose', 
		      'rund_matchRtd', 'rund_matchWO', 'rund_tieWin',  
		      'rund_tieEqualPlus', 'rund_tieEqual', 'rund_tieEqualMinus',
		      'rund_tieLoose', 'rund_tieWO', 'rund_tieranktype',
			  'rund_tiematchdecisif', 'rund_tiematchnum',
		      'rund_stamp', 'rund_nbms', 'rund_nbws', 'rund_nbmd',
		      'rund_nbwd', 'rund_nbxd','rund_nbas', 'rund_nbad'
		      );
		      $tables[] = 'rounds';
		      $where = "rund_id = '$groupId'";
		      $res = $this->_select($tables, $fields, $where);
		      if ($res->numRows())
		      {
		      	$group = $res->fetchRow(DB_FETCHMODE_ASSOC);
		      	$fields = array('rund_name','rund_stamp');
		      	$group = $this->_getTranslate('rounds', $fields,
		      	$group['rund_id'], $group);
		      }
		      else
		      {
		      	$group = array('rund_id'        => -1,
			 'rund_name'      => '',
			 'rund_stamp'      => '',
			 'rund_size'      => 0,
			 'rund_type'      => WBS_TEAM_GROUP,
			 'rund_rankType'  => WBS_CALC_RESULT,
			 'rund_matchWin'  => 1,
			 'rund_matchLoose'=> 0,
			 'rund_matchRtd'  => 0,
			 'rund_matchWO'   => -1,
			 'rund_tieWin'    => 3,
			 'rund_tieEqualPlus'  => 0,
		     'rund_tieEqual'  => 2,
			 'rund_tieEqualMinus'  => 0,
		     'rund_tieLoose'  => 1,
		     'rund_tieranktype'  => WBS_CALC_EQUAL,
		     'rund_tiematchdecisif'  => WBS_MS,
		     'rund_tiematchnum'  => 1,
		      'rund_nbms'  => 1,
			 'rund_nbws'  => 1,
			 'rund_nbas'  => 0,
			 'rund_nbmd'  => 1,
			 'rund_nbwd'  => 1,
			 'rund_nbad'  => 0,
			 'rund_nbwd'  => 1,
			 'rund_nbxd'  => 1,
			 'rund_tieWO' => 0);
		      }

		      // For compatibility with version < badnet 2.3
		      if ( $group['rund_nbms'] + $group['rund_nbws'] +
		      $group['rund_nbmd'] + $group['rund_nbwd'] +
		      $group['rund_nbwd'] == 0)
		      {
		      	$fields = array('tie_nbms', 'tie_nbws', 'tie_nbmd', 'tie_nbwd',
			  'tie_nbxd', 'tie_nbas', 'tie_nbad');
		      	$tables[] = "ties ";
		      	$where = "tie_roundId = '$groupId'";
		      	$res = $this->_select($tables, $fields, $where);

		      	if ($res->numRows())
		      	{
		      		$tie = $res->fetchRow(DB_FETCHMODE_ASSOC);
		      		$group['rund_nbms']  = $tie['tie_nbms'];
		      		$group['rund_nbws']  = $tie['tie_nbws'];
		      		$group['rund_nbmd']  = $tie['tie_nbmd'];
		      		$group['rund_nbwd']  = $tie['tie_nbwd'];
		      		$group['rund_nbxd']  = $tie['tie_nbxd'];
		      		$group['rund_nbas']  = $tie['tie_nbas'];
		      		$group['rund_nbad']  = $tie['tie_nbad'];
		      	}
		      	else
		      	{
		      		$group['rund_nbms']  = 1;
		      		$group['rund_nbws']  = 1;
		      		$group['rund_nbmd']  = 1;
		      		$group['rund_nbwd']  = 1;
		      		$group['rund_nbxd']  = 1;
		      		$group['rund_nbad']  = 0;
		      		$group['rund_nbas']  = 0;
		      	}
		      }
		      return $group;
	}
	// }}}

	// {{{ getDiv
	/**
	 * Return the specification of a division
	 *
	 * @access public
	 * @param  integer  $divID    Id of the division
	 * @return array   information of the user if any
	 */
	function getDiv($divId)
	{
		$utd = new utdraw();
		$draw = $utd->getDrawById($divId);
		$infos['div_id']    = $draw['draw_id'];
		$infos['div_name']  = $draw['draw_name'];
		$infos['div_stamp'] = $draw['draw_stamp'];
		$infos['div_del'] = $draw['draw_del'];
		$infos['div_pbl'] = $draw['draw_pbl'];
		return $infos;
	}
	// }}}

	// {{{ publiDiv
	/**
	 * Publication d'une division
	 *
	 * @access private
	 * @param  table  $infos  column of the draws
	 * @return mixed
	 */
	function publiDiv($drawId, $pbl, $propa=false)
	{
		// Update publication status of the groups
		$column = array();
		$column['rund_pbl'] = $pbl;
		$where = "rund_drawId=$drawId";
		$res  = $this->_update('rounds', $column, $where);

		// Update publication status of the teams
		$column = array('DISTINCT t2r_teamId');
		$tables = array('t2r', 'rounds');
		$where = "t2r_roundId = rund_id".
         " AND rund_drawId = $drawId";
		$res  = $this->_select($tables, $column, $where);
		if ($res->numRows())
		{
	  while ($id = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  $teamIds[] = $id['t2r_teamId'];

	  $list = implode(',', $teamIds);
	  $column = array();
	  $column['team_pbl'] = $pbl;
	  $where = "team_id IN ($list)";
	  $res  = $this->_update('teams', $column, $where);

	  // Update the status of the registered members
	  $columns = array();
	  $columns['regi_pbl'] = $pbl;
	  $ou = "regi_teamId IN ($list)";
	  if (!$propa)
	  $ou .= " AND regi_pbl < $pbl";
	  $res = $this->_update('registration', $columns, $ou);
		}

		return true;
	}
	// }}}

	// {{{ delDiv
	/**
	 * Logical delete the divisions
	 *
	 * @access public
	 * @param  integer  $divId   id's of the division to delete
	 * @return mixed
	 */
	function delDiv($divId)
	{
		//$fields['draw_del'] = WBS_DATA_DELETE;
		$where = "draw_id=$divId";
		//$res = $this->_update('draws', $fields, $where);
		$res = $this->_delete('draws', $where);

		// Delete the groups
		$fields[] = 'rund_id';
		$tables[] = 'rounds';
		$where = "rund_drawId='$divId'";
		$res = $this->_select($tables, $fields, $where);
		$utr = new utround();
		while ($round = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $res1 = $utr->delRound($round['rund_id']);
	  if (is_array($res1)) return $res1;
		}

		return true;
	}
	// }}}

	// {{{ updateGroup
	/**
	 * Update a group
	 *
	 * @access public
	 * @param  string  $infos   infos of the round
	 * @return mixed
	 */
	function updateGroup($rund, $tie)
	{
		$utr = new utround();

		// Recuperer le nombre d'equipe du groupe
		$fields = array('count(*)');
		$tables = array('t2r');
		$where = " t2r_roundId =". $rund['rund_id'];
		$res = $this->_select($tables, $fields, $where);

		$data = $res->fetchRow(DB_FETCHMODE_ORDERED);
		$rund['rund_size'] = $data[0];
		$rund['rund_entries'] = $data[0];

		// Mettre a jour les rencontres
		$rundId = $utr->updateRound($rund, $tie);
		if(is_array($rundId))	return $rundId;
	}
	// }}}

	// {{{ getTeams
	/**
	 * Return the list of the teams
	 *
	 * @access public
	 * @param  integer  $sort   criteria to sort users
	 * @return array   array of users
	 */
	function getTeams($sort)
	{
		$ut = new utils();
		$eventId = utvars::getEventId();

		$fields = array('team_id', 'team_name', 'team_stamp',
		      'team_captain', 'team_cmt');
		$tables[] = 'teams';
		$where = "team_eventId = $eventId".
	" AND team_del!=".WBS_DATA_DELETE;
		if ($sort < 0)
		$order = abs($sort)." desc";
		else
		$order = $sort;
		$res = $this->_select($tables, $fields, $where, $order);

		$rows = array();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $rows[] = $entry;
		}
		return $rows;
	}
	// }}}


	// {{{ regTeams
	/**
	 * Add the teams to the group
	 *
	 * @access public
	 * @param  table  $teamsId Id's of the teams to add
	 * @param  integer $groupId Id of the group
	 * @return mixed
	 */
	function regTeams($teamsId, $groupId)
	{
		$ut = new utils();

		if ($groupId == -1) return;

		// For all required team
		$eventId = utvars::getEventId();
		foreach ( $teamsId as $team => $id)
		{

			// Register the team for the event
			$fields = array();
			$fields['team_eventId'] = $eventId;
			$where = "team_id = $id";
			$res = $this->_update('teams', $fields, $where);

			// Register the position of the team in the group
			$where = "t2r_roundId = $groupId ".
	  " AND t2r_teamId = $id ";
			$t2rId = $this->_selectFirst('t2r', 't2r_id', $where);

			$fields = array();
			$fields['t2r_teamId']=$id;
			$fields['t2r_roundId']=$groupId;
			if (!is_null($t2rId))
	  {
	  	$where = "t2r_id = $t2rId";
	  	$this->_update('t2r', $fields, $where);
	  }
	  else
	  {
	  	// Rajouter les equipes plutot a la fin
	  	$fields['t2r_posRound']=1000;
	  	$t2rId = $this->_insert('t2r', $fields);
	  }
		}

		// Set the position of the team in the group
		$this->_setPosRound($groupId);

		// Update the group (and ties and match)
		$fields = array('*');
		$where = "rund_id = $groupId";
		$res = $this->_select('rounds', $fields, $where);
		$rund = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$tie['tie_nbms'] = $rund['rund_nbms'];
		$tie['tie_nbws'] = $rund['rund_nbws'];
		$tie['tie_nbmd'] = $rund['rund_nbmd'];
		$tie['tie_nbwd'] = $rund['rund_nbwd'];
		$tie['tie_nbxd'] = $rund['rund_nbxd'];
		$tie['tie_nbas'] = $rund['rund_nbas'];
		$tie['tie_nbad'] = $rund['rund_nbad'];
		$this->updateGroup($rund, $tie);

		// Positionner les equipes dans les rencontres
		return $this->updateT2T($groupId);

	}
	// }}}


	// {{{ _setPosRound
	/**
	 * Initialize the position of team in a group
	 *
	 * @access public
	 * @param  integer $groupId Id of the group
	 * @return mixed
	 */
	function _setPosRound($groupId)
	{
		// Update posRound
		$fields[] = 't2r_id';
		$tables[] = 't2r';
		$where = "t2r_roundId = $groupId ";
		$order = "t2r_posRound";
		$t2rs = $this->_select($tables, $fields, $where, $order);

		$posTeamRound=1;
		$fields = array();
		while ($t2r = $t2rs->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $fields['t2r_posRound'] = $posTeamRound;
	  $where = "t2r_id =". $t2r[0];
	  $res = $this->_update('t2r', $fields, $where);
	  $posTeamRound++;
		}
	}
	// }}}


	// {{{ delTeams
	/**
	 * Remove the teams of the group
	 *
	 * @access public
	 * @param  table  $teamsId Id's of the teams to remove
	 * @param  integer $groupId Id of the group
	 * @return mixed
	 */
	function delTeams($teamsId, $groupId)
	{
		foreach ( $teamsId as $team => $id)
		{
	  // Delete relation between team and group
	  $where = "t2r_teamId = $id ".
	    " AND t2r_roundId = $groupId ";
	  $res = $this->_delete('t2r', $where);

	  // Delete relation between team and ties
	  // Search the ties Id
	  $fields = array('tie_Id');
	  $tables = array('ties');
	  $where = "tie_roundId = $groupId";
	  $ties = $this->_select($tables, $fields, $where);
	  while ($tie = $ties->fetchRow(DB_FETCHMODE_ORDERED))
	  {
	  	$tieId = $tie[0];
	  	$where = "t2t_teamId = $id ".
		" AND t2t_tieId = $tieId ";
	  	$res = $this->_delete('t2t', $where);
	  }
		}

		// Set the position of the team in the group
		$this->_setPosRound($groupId);


		// Update the size of the group
		$utt = new utTeam();
		$teams = $utt->getTeamsGroup($groupId);
		$fields = array();
		$fields['rund_size'] = count($teams);
		$fields['rund_entries'] = count($teams);
		$where = "rund_id = $groupId";
		$res = $this->_update('rounds', $fields, $where);


		// Update the group (and ties and match)
		$fields = array('*');
		$where = "rund_id = $groupId";
		$res = $this->_select('rounds', $fields, $where);
		$rund = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$tie['tie_nbms'] = $rund['rund_nbms'];
		$tie['tie_nbws'] = $rund['rund_nbws'];
		$tie['tie_nbmd'] = $rund['rund_nbmd'];
		$tie['tie_nbwd'] = $rund['rund_nbwd'];
		$tie['tie_nbxd'] = $rund['rund_nbxd'];
		$tie['tie_nbas'] = $rund['rund_nbas'];
		$tie['tie_nbad'] = $rund['rund_nbad'];
		$this->updateGroup($rund, $tie);

		// Positionner les equipes dans les rencontres
		return $this->updateT2T($groupId);

	}
	// }}}

	// {{{ updateT2T
	/**
	 * Establish the relation between ties and teams
	 *
	 * @access public
	 * @param  integer $groupId Id of the group
	 * @return mixed
	 */
	function updateT2T($groupId)
	{
		$fields = array('rund_type');
		$tables = array('rounds');
		$where = "rund_id = $groupId";
		$ties = $this->_select($tables, $fields, $where);
		$group = $ties->fetchRow(DB_FETCHMODE_ASSOC);
		switch( $group['rund_type'])
		{
			case WBS_TEAM_GROUP:
				$res = $this->_updateGroupT2T($groupId, 0,0);
				break;
			case WBS_TEAM_BACK:
				$res = $this->_updateGroupT2T($groupId, 0);
				$res = $this->_updateGroupT2T($groupId, $res);
				break;
			case WBS_TEAM_KO:
				$res = $this->_updateKoT2T($groupId);
				break;
			default:
				$res['errMsg'] = 'msgUnknowRundType:'.$group['rund_type'].
	    ";groupId=$groupId";
				break;
		}
		return $res;
	}
	// }}}

	// {{{ _updateKoT2T
	/**
	 * Establish the relation between ties and teams in a KO draw
	 *
	 * @access public
	 * @param  integer $groupId Id of the group
	 * @return mixed
	 */
	function _updateKoT2T($groupId)
	{
		// Search the teams of the groupe
		$utt = new utTeam();
		$teams = $utt->getTeamsGroup($groupId);
		if (!count($teams)) return;

		foreach($teams as $team)
		$teamIds[] = $team['team_id'];

		// Determine the tie and the position in the tie
		// for each team
		$utk = new utKo($teamIds);
		$ties = $utk->getExpandedTies();

		// Create the relation for each team in the first column
		foreach($teamIds as $teamId)
		{
	  $tie = $ties[$teamId];
	  // Search the tie id
	  $fields = array('tie_id');
	  $tables = array('ties');
	  $where = "tie_roundId = $groupId".
	    " AND tie_posRound=".$tie['numTie'];
	  $res = $this->_select($tables, $fields, $where);
	  if (!$res->numRows())
	  {
	  	$err['errMsg'] = " Rencontre non trouvee! Ca pas etre normal!";
	  	return $err;
	  }
	  $data = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $tieId = $data['tie_id'];

	  // Search the relation between team and tie
	  $fields = array('t2t_id');
	  $tables = array('t2t');
	  $where = "t2t_tieId=$tieId".
	    " AND t2t_posTie=".$tie['posInTie'];
	  $res = $this->_select($tables, $fields, $where);
	  if (!$res->numRows())
	  {
	  	$fields = array();
	  	$fields['t2t_teamId'] = $teamId;
	  	$fields['t2t_tieId'] = $tieId;
	  	$fields['t2t_posTie'] = $tie['posInTie'];
	  	$fields['t2t_result'] = WBS_TIE_NOTPLAY;
	  	$res = $this->_insert('t2t',$fields);
	  	$t2tId = $res;
	  }
	  else
	  {
	  	$data = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	$t2tId = $data['t2t_id'];
	  	$fields = array();
	  	$fields['t2t_teamId'] = $teamId;
	  	$fields['t2t_result'] = WBS_TIE_NOTPLAY;
	  	$res = $this->_update('t2t',$fields, $where);
	  }

	  // Remember all t2t record concerned
	  $t2tIds[] = $t2tId;
		}

		// Create relation with team in the second
		// column
		$bies = $utk->getExpandedByes();
		foreach($bies as $by)
		{
	  // Search the tie id
	  $fields = array('tie_id');
	  $tables = array('ties');
	  $where = "tie_roundId = $groupId".
	    " AND tie_posRound=".$by['numTie'];
	  $res = $this->_select($tables, $fields, $where);
	  if (!$res->numRows())
	  {
	  	$err['errMsg'] = " Rencontre non trouvee! Ca pas etre normal!";
	  	return $err;
	  }
	  $data = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $tieId = $data['tie_id'];

	  // Search the second team ot the tie
	  $fields = array('t2t_teamId');
	  $tables = array('t2t');
	  $where = "t2t_tieId = $tieId";
	  $res = $this->_select($tables, $fields, $where);
	  $data = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $teamId = $data['t2t_teamId'];
	  $nextTiePos = intval(($by['numTie']-1)/2);
	  $posInTie = ($by['numTie']%2) ? WBS_TEAM_TOP:WBS_TEAM_BOTTOM;

	  // Update the result of the tie
	  $fields = array();
	  $fields['t2t_result'] = WBS_TIE_STEP;
	  $where = "t2t_tieId = $tieId".
	   " AND t2t_teamId = $teamId";
	  $res = $this->_update('t2t', $fields, $where);

	  // search the id of the next tie
	  $fields = array('tie_id');
	  $tables = array('ties');
	  $where = "tie_roundId = $groupId".
	    " AND tie_posRound=$nextTiePos";
	  $res = $this->_select($tables, $fields, $where);
	  if (!$res->numRows())
	  {
	  	$err['errMsg'] = " Rencontre non trouvee! Ca pas etre normal!";
	  	return $err;
	  }
	  $data = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $nextTieId = $data['tie_id'];

	  // Search and update the relation between team and tie
	  $fields = array('t2t_id');
	  $tables = array('t2t');
	  $where = "t2t_tieId=$nextTieId".
	    " AND t2t_posTie=$posInTie";
	  $res = $this->_select($tables, $fields, $where);
	  if (!$res->numRows())
	  {
	  	$fields = array();
	  	$fields['t2t_teamId'] = $teamId;
	  	$fields['t2t_tieId'] = $nextTieId;
	  	$fields['t2t_posTie'] = $posInTie;
	  	$fields['t2t_result'] = WBS_TIE_NOTPLAY;
	  	$res = $this->_insert('t2t',$fields);
	  	$t2tId = $res;
	  }
	  else
	  {
	  	$data = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	$t2tId = $data['t2t_id'];
	  	$fields = array();
	  	$fields['t2t_result'] = WBS_TIE_NOTPLAY;
	  	$fields['t2t_teamId'] = $teamId;
	  	$res = $this->_update('t2t',$fields, $where);
	  }

	  // Remember all t2t record concerned
	  $t2tIds[] = $t2tId;
		}

		// Delete the relation between team and tie not concerned
		$list = implode(',', $t2tIds);
		$fields = array('t2t_id');
		$tables = array('t2t', 'ties');
		$where = "t2t_tieId=tie_id".
	" AND tie_roundId = $groupId".
	" AND t2t_id NOT IN($list) ".
      	" AND (t2t_result=".WBS_TIE_NOTPLAY.
	" OR t2t_result=".WBS_TIE_STEP.")";
		$res = $this->_select($tables, $fields, $where);
		if ($res->numRows())
		{
	  $t2tIds = array();
	  while ($t2t = $res->fetchRow(DB_FETCHMODE_ORDERED))
	  $t2tIds[] = $t2t[0];
	  $list = implode(',', $t2tIds);
	  $where = "t2t_id IN($list)";
	  $res = $this->_delete('t2t', $where);
		}
		return true;
	}
	// }}}

	// {{{ _updateGroupT2T
	/**
	 * Establish the relation between ties and teams
	 *
	 * @access public
	 * @param  integer $groupId Id of the group
	 * @return mixed
	 */
	function _updateGroupT2T($groupId, $start)
	{
		// How many team already in this group ?
		$utt = new utTeam();
		$teams = $utt->getTeamsGroup($groupId);
		$nbTeam = count($teams);
		$posTieRound = $start;

		// For all required team
		for( $i=0; $i<$nbTeam; $i++)
		{

			$curTeam = $teams[$i];
			// for each team already in the group,
			// add the teams to the ties
			for ($j=0; $j<$i; $j++)
	  		{
	  			$team = $teams[$j];
	  			// Search the tie Id
	  			$fields = array('tie_Id');
	  			$tables = array('ties');
	  			$where = "tie_roundId = $groupId".
	      			" AND tie_posround=$posTieRound" ;
	  			$res = $this->_select($tables, $fields, $where);
	  			$val = $res->fetchRow(DB_FETCHMODE_ORDERED);
	  			$tieId = $val[0];

			  	// Search the t2t for this tie
		  		$fields = array('t2t_id, t2t_result');
	  			$tables = array('t2t');
	  			$where = "t2t_tieId = $tieId";
	  			$order = "t2t_posTie";
	  			$t2ts = $this->_select($tables, $fields, $where, $order);

			  	$fields = array();
			  	$fields['t2t_teamId'] = $team['team_id'];
			  	$fields['t2t_posTie'] = $start ? WBS_TEAM_VISITOR:WBS_TEAM_RECEIVER;
			  	if ($t2t = $t2ts->fetchRow(DB_FETCHMODE_ORDERED))
			  	{
			  		// Update the relation between tie an first team
	  				$t2tId  = $t2t[0];
			  		if ($t2t[1] == WBS_TIE_STEP) $fields['t2t_result'] = WBS_TIE_NOTPLAY;
	  				$where = "t2t_id = $t2tId";
	  				$res = $this->_update('t2t', $fields, $where);
	  			}
	  			else
	  			{
			  		// 	Add a relation between tie an first team
	  				$fields['t2t_tieId'] = $tieId;
			  		$fields['t2t_result'] = WBS_TIE_NOTPLAY;
	  				$fields['t2t_cre'] = date(DATE_FMT);
	  				$res = $this->_insert('t2t', $fields);
	  			}

			  	$fields = array();
	  			$fields['t2t_teamId'] = $curTeam['team_id'];
	  			$fields['t2t_posTie'] = $start ? WBS_TEAM_RECEIVER:WBS_TEAM_VISITOR;
	  			if ($t2t = $t2ts->fetchRow(DB_FETCHMODE_ORDERED))
	  			{
			  		// Update the relation between tie an current team
	  				$t2tId  = $t2t[0];
			  		if ($t2t[1] == WBS_TIE_STEP) $fields['t2t_result'] = WBS_TIE_NOTPLAY;
	  				$where = "t2t_id = $t2tId";
	  				$res = $this->_update('t2t', $fields, $where);
	  			}
	  			else
	  			{
			  		// Add a relation between tie an current team
	  				$fields['t2t_tieId'] = $tieId;
	  				$fields['t2t_result'] = WBS_TIE_NOTPLAY;
	  				$fields['t2t_cre'] = date(DATE_FMT);
	  				$res = $this->_insert('t2t', $fields);
	  			}
	  			$posTieRound++;
	  		}
		}
			
		return $posTieRound;
	}

	/**
	 * Move a team in a group
	 *
	 * @access public
	 * @param  integer $teamId Id of the teams to move
	 * @param  integer $groupId Id of the group
	 * @param  integer $direction  Direction of the move (up=1 or down=-1)
	 * @return mixed
	 */
	function moveTeam($teamId, $groupId, $direc)
	{
		// Select the position of the team in the group
		$fields[] = 't2r_posRound';
		$tables[] = 't2r';
		$where = "t2r_roundId = $groupId AND t2r_teamId = ".$teamId;
		$res = $this->_select($tables, $fields, $where);

		$data = $res->fetchRow(DB_FETCHMODE_ORDERED);
		$posTeam = $data[0]+$direc;

		// Search the second team
		$fields = array('t2r_teamId');
		$where = "t2r_roundId = $groupId AND t2r_posRound = $posTeam";
		$res = $this->_select($tables, $fields, $where);

		if ($res->numRows())
		{
			$data = $res->fetchRow(DB_FETCHMODE_ORDERED);
			$teamsId[] = $teamId;
			$teamsId[] = $data[0];
			$res = $this->_invertTeams($teamsId, $groupId);
			if (is_array($res)) return $res;

			// Mettre a jour les rencontres dans un tableau Ko
			$fields = array('rund_type');
			$tables = array('rounds');
			$where = "rund_id = $groupId";
			$ties = $this->_select($tables, $fields, $where);
			$group = $ties->fetchRow(DB_FETCHMODE_ASSOC);
		    if ( $group['rund_type'] == WBS_TEAM_KO ) $res = $this->_updateKoT2T($groupId);
		}
		return true;
	}

	/**
	 * Invert two consecutive team in the group
	 *
	 * @access private
	 * @param  table  $teamsId Id's of the teams to invert
	 * @param  integer $groupId Id of the group
	 * @return mixed
	 */
	function _invertTeams($teamsId, $groupId)
	{
		// Select the relation of teams with the group
		$fields = array('t2r_teamId', 't2r_posRound');
		$tables[] = 't2r';
		$where = " t2r_roundId = $groupId".
	" AND (t2r_teamId = ".$teamsId[0].
	" OR t2r_teamId = ".$teamsId[1].
	" )";

		$teams = $this->_select($tables, $fields, $where);

		// Invert the positions of the teams
		$team1 = $teams->fetchRow(DB_FETCHMODE_ASSOC);
		$team2 = $teams->fetchRow(DB_FETCHMODE_ASSOC);

		$fields = array();
		$fields['t2r_posRound'] = $team1['t2r_posRound'];
		$where = "t2r_roundId = $groupId" .
	" AND t2r_teamId =".$team2['t2r_teamId'];
		$res = $this->_update('t2r', $fields, $where);

		$fields['t2r_posRound'] = $team2['t2r_posRound'];
		$where = "t2r_roundId = $groupId" .
	" AND t2r_teamId =".$team1['t2r_teamId'];
		$res = $this->_update('t2r', $fields, $where);

		$fields = array('tie_id', 'tie_posRound', 'rund_type');
		$tables = array('ties', 't2t', 'rounds');
		$order = "tie_posRound";
		// Select all ties of the first team
		$where = "tie_roundId = $groupId".
	" AND tie_roundId = rund_id".
	" AND t2t_teamId = ".$teamsId[0].
	" AND t2t_tieId = tie_id"
	. " AND (rund_type = ".WBS_TEAM_GROUP
	. " OR rund_type = ".WBS_TEAM_BACK.")"
	;
	$ties1 = $this->_select($tables, $fields, $where, $order);

	// Select all ties of the second team
	$where = "tie_roundId = $groupId".
	" AND tie_roundId = rund_id".
	" AND t2t_teamId = ".$teamsId[1].
	" AND t2t_tieId = tie_id"
	. " AND (rund_type = ".WBS_TEAM_GROUP
	. " OR rund_type =". WBS_TEAM_BACK.")"
	;
	$ties2 = $this->_select($tables, $fields, $where, $order);

	// For each tie, invert the positon in the group
	$fields = array();
	while ($tie1 = $ties1->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$tie2 = $ties2->fetchRow(DB_FETCHMODE_ASSOC);
		$fields['tie_posRound'] = $tie2['tie_posRound'];
		$where = "tie_id =". $tie1['tie_id'];
		$res = $this->_update('ties', $fields, $where);
		$fields['tie_posRound'] = $tie1['tie_posRound'];
		$where = "tie_id =". $tie2['tie_id'];
		$res = $this->_update('ties', $fields, $where);
	}
	return true;
	}

}
?>