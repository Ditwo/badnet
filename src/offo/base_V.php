<?php
/*****************************************************************************
!   Module     : Registrations
!   File       : $Source: /cvsroot/aotb/badnet/src/offo/base_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
!   Author     : D.BEUVELOT
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
!   Mailto     : didier.beuvelot@free.fr
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
* Acces to the dababase for events
*
* @author Didier BEUVELOT <didier.beuvelot@free.fr>
* @see to follow
*
*/

class registrationBase_V extends utbase
{

  // {{{ properties
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
      $eventId = utvars::getEventId();
      $fields = array('team_id', 'team_name', 'team_stamp', 'asso_name',
		      'team_captain', 'asso_logo', 'asso_url');
      $tables = array('teams', 'assocs', 'a2t');
      $where = "team_eventId = $eventId ".
	" AND a2t_assoId = asso_id ".
	" AND a2t_teamId = team_id ";
      $order = abs($sort);
      if ($sort < 0)
	  $order .= " DESC";
      
      $res = $this->_select($tables, $fields, $where, $order);
      $rows = array();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $entry[5] = utimg::getPathFlag($entry[5]);
	  $rows[] = $entry;
        }
      return $rows;
    }
  // }}}


  // {{{ getOfficials
  /**
   * Return the list of the officials
   *
   * @access public
   * @param  string  $sort   criteria to sort users
   * @return array   array of users
   */
  function getOfficials($sort)
    {
      // Retrieve registered players
      $eventId = utvars::getEventId();
      $fields = array('regi_id', 'mber_sexe', 'mber_secondname', 
		      'mber_firstname', 'team_name', 'regi_type');
      $tables = array('members', 'teams','registration');
      $where  = "regi_del != ".WBS_DATA_DELETE.
	" AND mber_Id = regi_memberId".
	" AND team_Id= regi_teamId".
	" AND regi_type >=".WBS_REFEREE.
	" AND regi_type <=". WBS_DELEGUE.
	" AND regi_type <>". WBS_COACH.
      " AND regi_eventId = $eventId".
	" AND mber_Id >= 0";
      $order = 'regi_type,' . abs($sort);
      if ($sort <0)
	$order .= " DESC";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgNoRegisteredOfficial';
	  return $infos;
	}

      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $entry[1] = $this->_ut->getSmaLabel($entry[1]);
	  $entry[5] = $this->_ut->getLabel($entry[5]);
	  $rows[] = $entry;
	}
      return $rows;
    }
  // }}}

  // {{{ getRegistrations
  /**
   * Return the list of the registrations
   *
   * @access public
   * @param  string  $sort   criteria to sort users
   * @return array   array of users
   */
  function getPlayers($sort)
    {
      // Retrieve registered players
      $eventId = utvars::getEventId();
      $fields = array('regi_id', 'mber_sexe', 'mber_secondname', 
		      'mber_firstname', 'rkdf_label', 'team_name', 
		      'team_id', 'mber_licence', 'regi_type');
      $tables = array('members', 'teams','registration LEFT JOIN ranks ON 
		      rank_regiId = regi_id LEFT JOIN rankdef ON 
                      rank_rankdefId = rkdf_id');
      $where  = "regi_del != ".WBS_DATA_DELETE.
	" AND mber_Id = regi_memberId".
	" AND team_Id= regi_teamId".
	" AND regi_eventId = $eventId".
	" AND regi_type = ".WBS_PLAYER.
	" AND mber_Id >= 0";
      $order = "1, rank_disci";
      
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgNoRegisteredPlayer';
	  return $infos;
	}

      $id = -1;
      $rows = array();
      if ($sort < 0) $sort++;
      else $sort--;
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  if ($id != $entry[0])
 	    {
 	      if($id > 0)
		{
		  $tmp[1] = $this->_ut->getSmaLabel($tmp[1]);
		  // Put the sort column in first position
		  $sw = $tmp[0];
		  $tmp[0] = $tmp[abs($sort)];
		  $tmp[abs($sort)] =  $sw;
		  if (trim($tmp[7]) == "")$tmp[7]=KAF_NONE;
		  $rows[] = $tmp;
		}
 	      $tmp = $entry;
 	      $id = $entry[0];
 	    }
 	  else
 	      $tmp[4] .= ",{$entry[4]}";
        }
      $tmp[1] = $this->_ut->getSmaLabel($tmp[1]);
      if (trim($tmp[7]) == "")$tmp[7]=KAF_NONE;
      $sw = $tmp[0];
      $tmp[0] = $tmp[abs($sort)];
      $tmp[abs($sort)] =  $sw;
      $rows[] = $tmp;

      if ($sort < 0)
	array_multisort($rows, SORT_DESC);
      else
	array_multisort($rows);
      foreach ($rows as $i=>$line)
	{

	  $sw = $line[0];
	  $line[0] = $line[abs($sort)];
	  $line[abs($sort)] =  $sw;
	  $rowsend[] = $line;
	}
      return $rowsend;
    }
  // }}}

}
?>