<?php
/*****************************************************************************
!   Module     : Offcials
!   File       : $Source: /cvsroot/aotb/badnet/src/offo/base_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.8 $
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
* Acces to the dababase for officials 
*
* @author Didier BEUVELOT <didier.beuvelot@free.fr>
* @see to follow
*
*/

class offoBase_A extends utBase
{

  // {{{ getActivity
  /**
   * Return the activity of selected umpires
   *
   * @access public
   * @param  integer $teamId  Id of the team
   * @return array   array of teams
   */
  function getActivity($umpireIds)
    {
      // select the matches of each umpire
      $activity = array();
      foreach($umpireIds as $regiId)
	{
	  $fields  =array('mtch_num', 'regi_longName', 'mtch_begin', 'mtch_end',
			  'tie_posRound', 'rund_name', 'mtch_discipline');
	  $tables = array('registration', 'matchs', 'ties', 'rounds', 'draws');
	  $where = "mtch_umpireId = regi_id".
	    " AND mtch_tieId = tie_id".
	    " AND tie_roundId = rund_id".
	    " AND rund_drawId = draw_id".
	    " AND draw_eventId =".utvars::getEventId().
	    " AND regi_id = $regiId";
	  $order = "mtch_discipline, rund_name, tie_posRound DESC";
	  $res = $this->_select($tables, $fields, $where, $order);
	  $rows = array();
	  while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	    {
	      $rows[] = $entry;
	    }
	  $activity[] = $rows;
	}
      return $activity;
    }
  // }}}


  // {{{ getServiceActivity
  /**
   * Return the activity of selected service judge
   *
   * @access public
   * @param  integer $teamId  Id of the team
   * @return array   array of teams
   */
  function getServiceActivity($umpireIds)
    {
      // select the matches of each umpire
      $activity = array();
      foreach($umpireIds as $regiId)
	{
	  $fields  =array('mtch_num', 'regi_longName', 'mtch_begin', 'mtch_end',
			  'tie_posRound', 'rund_name', 'mtch_discipline');
	  $tables = array('registration', 'matchs', 'ties', 'rounds', 'draws');
	  $where = "mtch_serviceId = regi_id".
	    " AND mtch_tieId = tie_id".
	    " AND tie_roundId = rund_id".
	    " AND rund_drawId = draw_id".
	    " AND draw_eventId =".utvars::getEventId().
	    " AND regi_id = $regiId";
	  $order = "mtch_discipline, rund_name, tie_posRound DESC";
	  $res = $this->_select($tables, $fields, $where, $order);
	  $rows = array();
	  while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	    {
	      $rows[] = $entry;
	    }
	  $activity[] = $rows;
	}
      return $activity;
    }
  // }}}


  // {{{ searchMember
  /**
   * Search a members in the local database
   *
   * @access public
   * @param string $name      Name of the player
   * @param string $licence   Licence number of the player
   * @param string $ibfnumber Ibf number of the player
   * @return array   array of players
   */
  function searchMember($criteria)
    {
      // Search the player in database
      $name = addslashes($criteria['name'])."%";
      $firstName = addslashes($criteria['firstName'])."%";
      $country   = addslashes($criteria['country'])."%";
      
      if ($name == '%' && $firstName ==  '%' &&
	  $country == '%')
	return;

      $fields = array('mber_id', 'mber_sexe', 'mber_secondname',
		      'mber_firstname');
      $tables[] = 'members';
      $where = "mber_id >= 0";
      $where .= " AND mber_secondname LIKE '$name'";
      $where .= " AND mber_firstname LIKE '$firstName'";
      //$where .= " AND mber_country = '$coutry'";
      $order = "mber_secondname, mber_firstname";
      $mbers = $this->_select($tables, $fields, $where, $order);

      // No members found
      if (!$mbers->numRows()) 
	{
	  $err['errMsg'] = 'msgNoMemberFound';
	  return $err;
	}
      // Search the ranking and insert the player in the temp table
      $date = new utdate();
      $ut = new utils;
      while (  $tmp = $mbers->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $tmp['mber_sexe'] = $ut->getSmaLabel($tmp['mber_sexe']);
	  $select[] = $tmp;
	}
      // Return the list of players
      return $select;
    }
  // }}}

  // {{{ getTeams
  /**
   * Return the list of the teams registered 
   *
   * @access public
   * @param  integer $teamId  Id of the team
   * @return array   array of teams
   */
  function getTeams($teamId)
    {
      if ($teamId != -1)
	{
	  $tables = array('teams');
	  $where = "team_del !=".WBS_DATA_DELETE.
	    " AND team_id = $teamId";
	}
      else
	{
	  $eventId = utvars::getEventId();
	  $tables = array('teams');
	  $where = "team_del !=".WBS_DATA_DELETE.
	    " AND team_eventId = $eventId";
	}
      $fields  =array('team_id', 'team_name');
      $order = "2";
      $res = $this->_select($tables, $fields, $where, $order);
      $rows = array();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $rows[$entry[0]] = $entry[1];
        }
      if (!count($rows))
	$rows['errMsg'] = 'msgNoTeams';
      return $rows;
    }
  // }}}


  // {{{ getAssocs
  /**
   * Return the list of the associations
   *
   * @access public
   * @return array   array of associations
   */
  function getAssocs()
    {
      $fields = array('asso_id', 'asso_name');
      $tables = array('assocs');
      $where  = "asso_del !=".WBS_DATA_DELETE;
      $order  = "2";
      $res = $this->_select($tables, $fields, $where, $order);
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $rows[$entry[0]] = $entry[1];
        }
      return $rows;
    }
  // }}}
  
  // {{{ getOfficials
  /**
   * Return the list of the officials registered for a team event
   *
   * @access public
   * @param  string  $sort   criteria to sort users
   * @return array   array of users
   */
  function getOfficials($sort, $type=0)
    {
      // Retrieve registered players
      $eventId = utvars::getEventId();
      $fields = array('regi_id', 'regi_date', 'mber_id', 'regi_longName', 
		      'mber_sexe', 'regi_type', 'regi_function', 'regi_noc',
		      'team_name', 'team_id', 
		      'regi_del', 'regi_pbl', 'team_del', 'team_pbl',
		      'mber_urlphoto');
      $tables  = array('registration', 'teams', 'members');
      $where = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_teamId = team_id".
	" AND regi_eventId = $eventId".
	" AND regi_memberId >= 0".
	" AND regi_memberId = mber_id";
      if ($type==OFFO_OTHER)
      {
		$where .= " AND (regi_type > ".WBS_DELEGUE . ' OR regi_type = ' .WBS_COACH .')';
      }
      else
	  {
	  	$where .= " AND regi_type > ".WBS_PLAYER;
	  	$where .= " AND regi_type < ".WBS_VOLUNTEER;
	  	$where .= " AND regi_type <> ".WBS_COACH;
	  }
      $order = abs($sort);
      if ($sort < 0)
	$order .= " DESC";
      
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $rows['errMsg'] = 'msgNoRegisteredOfficials';
	  return $rows;
	}

      $utd = new utdate();
      $ut = new utils();
      $uti = new utimg();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $utd->setIsoDate($entry['regi_date']);
	  $entry['regi_date'] = $utd->getDate();
	  $entry['mber_sexe'] = $ut->getSmaLabel($entry['mber_sexe']);
	  $entry['regi_type'] = $ut->getLabel($entry['regi_type']);
	  $entry['iconRegi'] = $uti->getPubliIcon($entry['regi_del'],
						 $entry['regi_pbl']);

	  $entry['iconTeam'] = $uti->getPubliIcon($entry['team_del'],
						 $entry['team_pbl']);
	  $entry['mber_id'] = '';
	  $entry['mber_urlphoto'] = 
	    utimg::getPathPhoto($entry['mber_urlphoto']);
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
  function getRegistrations($sort)
    {
      // Retrieve registered players
      $fields = array('regi_id', 'regi_date', 'regi_longName',
		      'asso_name');
      $tables = array('registration',  'assocs');
      $where = "regi_teamId = asso_Id".
	"AND regi_del != ".WBS_DATA_DELETE;
      if ($sort > 0)
	$order = "$sort ";
      else
	$order = abs($sort) . " desc";
      
      $res = $this->_select($tables, $fields, $where, $order);
      $utd = new utdate();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $utd->setIsoDate($entry[1]);
	  $entry[1] = $utd->getDate();
	  $rows[] = $entry;
        }
      return $rows;
    }
  // }}}
  

  // {{{ getMember
  /**
   * Return the column of a member
   *
   * @access public
   * @param  string  $registrationId  id of the registration
   * @return array   information of the registration if any
   */
  function getMember($memberId)
    {
      $fields = array('mber_id', 'mber_firstname',
		      'mber_secondname', 'mber_ibfnumber', 'mber_licence',
		      'mber_born', 'mber_sexe', 'mber_urlphoto' );
      $tables = array('members');
      $where = "mber_id = $memberId";
      $res = $this->_select($tables, $fields, $where);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgTeamNeedClub';
	  return $infos;
	} 
      
      $player = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $utd = new utdate();
      $utd->setIsoDate($player['mber_born']);
      $player['mber_born'] = $utd->getDate();
      return $player;
    }
  // }}}

  // {{{ getRegiMember
  /**
   * Return the column of a registered member
   *
   * @access public
   * @param  string  $registrationId  id of the registration
   * @return array   information of the registration if any
   */
  function getRegiMember($registrationId)
    {
      $fields = array('mber_id', 'mber_firstname',
		      'mber_secondname', 'mber_ibfnumber', 'mber_licence',
		      'mber_born', 'mber_sexe', 'mber_urlphoto');
      $tables = array('members', 'registration');
      $where = "regi_id = '$registrationId'".
	" AND mber_id = regi_memberId";
      $res = $this->_select($tables, $fields, $where);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgTeamNeedClub';
	  return $infos;
	} 
      
      $player = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $utd = new utdate();
      $utd->setIsoDate($player['mber_born']);
      $player['mber_born'] = $utd->getDate();
      return $player;
    }
  // }}}
  
  // {{{ getRegiRegis
  /**
   * Return the column of a registration	
   *
   * @access public
   * @param  string  $registrationId  id of the registration
   * @return array   information of the registration if any
   */
  function getRegiRegis($registrationId)
    {
      $fields = array('regi_id', 'regi_date', 'regi_longName',
		      'regi_shortName', 'regi_type', 'regi_eventId',
		      'regi_teamId', 'regi_cmt', 'regi_accountId',
		      'regi_function', 'regi_noc');
      $tables = array('registration');
      $where = "regi_id = '$registrationId'";
      $res = $this->_select($tables, $fields, $where);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgTeamNeedClub';
	  return $infos;
	} 
      
      $player = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $utd = new utdate();
      $utd->setIsoDate($player['regi_date']);
      $player['regi_date'] = $utd->getDate();
      return $player;
    }
  // }}}
  
  // {{{ updateRegistration
  /**
   * Add or update a registration into the database
   *
   * @access public
   * @param  string  $info   column of the registration
   * @return mixed
   */
  function updateRegistration($member, $regi)
    {
      // Create or updating a registration
      $utd = new utdate();
      $utd->setFrDate($regi['regi_date']);
      $regi['regi_date'] = $utd->getIsoDate();

      $regi['regi_memberId'] = $member['mber_id'];
      if ($regi['regi_id'] != -1)
	{
	  $where = " regi_id=".$regi['regi_id'];
	  $res = $this->_update('registration', $regi, $where);
	}
      else
	{
	  // Create a personnal account for the member
	  if ($regi['regi_accountId']==-2)
	    {
	      $fields = array();
	      $fields['cunt_name'] = $regi['regi_longName'];
	      $fields['cunt_code'] = 'PERSO';
	      $fields['cunt_eventId'] = utvars::getEventId();
	      $tables = array('account');
	      $res = $this->_insert('accounts', $fields);
	      $regi['regi_accountId'] = $res;
	    }
	  else
	    {
	      // Find the account Id of the team
	      $fields = array('team_accountId');
	      $tables = array('teams');
	      $where  = "team_id =".$regi['regi_teamId'];
	      $res = $this->_select($tables, $fields, $where);
	      $team = $res->fetchRow(DB_FETCHMODE_ASSOC);
	      $regi['regi_accountId'] = $team['team_accountId'];
	    }

	  unset($regi['regi_id']);
	  $res = $this->_insert('registration', $regi);
	  $regi['regi_id'] = $res;
	}

      return $regi['regi_id'];
    }
  // }}}

}
?>
