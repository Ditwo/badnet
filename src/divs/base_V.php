<?php
/*****************************************************************************
!   Module    : Divisions
!   File       : $Source: /cvsroot/aotb/badnet/src/divs/base_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
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

require_once "utils/utbase.php";

/**
* Acces to the dababase for divisions
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class divsBase_V extends utbase
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
  function getDrawTeams($groupId, $tiesNum)
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
	      $data = $res->fetchRow(DB_FETCHMODE_ORDERED);
	      $rows[] = $data[0];
	    }
	  else
	      $rows[] = "";
	}
      return $rows;
    }
  // }}}

  // {{{ getGroupTeams
  /**
   * Return the teams of a group
   *
   * @access public
   * @param  integer  $groupId  id of the group
   * @return array   information of the matchs if any
   */
  function getGroupTeams($groupId)
    {
      // Select the teams of the group
      $fields = array('team_id', 'team_name', 'team_stamp', 
		      'team_captain', 'asso_logo', 'team_logo');
      $tables = array('teams', 't2r', 'assocs', 'a2t');
      $where = "t2r_teamId = team_id".
	" AND t2r_roundId = $groupId".
	" AND a2t_assoId = asso_id ".
	" AND a2t_teamId = team_id ";
      $order = "t2r_posRound";
      $res = $this->_select($tables, $fields, $where, $order);
      $teams = array();
      while ($team = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  if ($team[5] == '')
	    $team[4] = utimg::getPathFlag($team[4]);
	  else
	    $team[4] = utimg::getPathFlag($team[5]);
	  unset($team[5]);
	  $teams[]=$team;
	}
      return $teams;
    }
  // }}}


  // {{{ getTiesSchedu
  /**
   * Return the table with tie results
   *
   * @access public
   * @param  integer  $groupId  Id of the group
   * @param  integer  $sort     Sorting column
   * @return array   array of users
   */
  function getTiesSchedu($groupId, $sort)
    {
      // Firt select the ties of the group
      $fields = array('tie_id', 'tie_step', 'tie_schedule', 'tie_place',
		      'team_name', 'team_id', 't2t_matchW', 'team_id', 
		      'asso_logo');
      $tables = array('teams', 'ties', 't2t', 'a2t', 'assocs');
      $where = "t2t_teamId = team_Id".
	" AND t2t_tieId = tie_id".
	" AND tie_roundId = '$groupId'".
	" AND a2t_assoId = asso_id ".
	" AND a2t_teamId = team_id ";
      if ($sort > 0)
	$order = "$sort, tie_id, t2t_posTie";
      else
	$order = abs($sort) . " desc, tie_id, t2t_posTie";

      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = "msgNoTies";
	  return $infos;
        } 

      $ties = array();
      $tieId=-1;
      $utd = new utdate();
      while ($tie = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $tie[8] = utimg::getPathFlag($tie[8]);
	  if ($tieId != $tie[0])
	    {
	      if ($tieId>=0) $ties[] = $tmp;
	      $tmp = $tie;
	      $tieId = $tie[0];
	    }
	  else
	    {
	      $utd->setIsoDate($tmp[2]);
	      $tmp[2]= $utd->getDateTime();
	      $tmp[] = $tie[5];
	      $tmp[5] = $tie[4];
	      $tmp[6] .= "/{$tie[6]}";
	      $tmp[] = $tie[8];
	    }
	}
      $ties[] = $tmp;
      return $ties;
    }
  // }}}


}
?>