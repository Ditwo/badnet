<?php
/*****************************************************************************
!   Module     : Registrations
!   File       : $Source: /cvsroot/aotb/badnet/src/regi/base_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.10 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/05/19 09:56:25 $
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
	  $cell = array();
	  $line['value'] = $entry['asso_name'];
	  $line['logo'] = utimg::getPathFlag($entry['asso_logo']);
	  $line['action'] = array(KAF_UPLOAD, 'teams', KID_SELECT, $entry['asso_id']);
	  $cell[] = $line;
	  $entry['asso_name'] = $cell;
	  $rows[] = $entry;
        }
      return $rows;
    }
  // }}}

  // {{{ getTeams
  /**
   * Return the list of the teams
   *
   * @access public
   * @param  integer  $sort   criteria to sort teams
   * @return array   array of users
   */
  function getTeams($sort)
    {
      // Select teams in the database
      $eventId = utvars::getEventId();
      $fields = array('team_id', 'team_name', 'team_stamp', 'asso_name',
		      'team_captain', 'asso_logo', 'asso_url', 'team_logo',
		      'asso_id');

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

      // Prepare a table with the teams
      $rows = array();
      $trans = array('asso_name','asso_stamp', 'asso_pseudo');
      $trans1 = array('team_name','team_stamp');
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $entry = $this->_getTranslate('assos', $trans, 
					$entry['asso_id'], $entry);
	  $entry = $this->_getTranslate('teams', $trans1, 
					$entry['team_id'], $entry);
	  $cell = array();
	  $line['value'] = $entry['team_name'];
	  if ($entry['team_logo'] != '')
	    $line['logo'] = utimg::getPathFlag($entry['team_logo']);
	  else
	    $line['logo'] = utimg::getPathFlag($entry['asso_logo']);
	  $line['action'] = array(KAF_UPLOAD, 'teams', KID_SELECT, $entry['team_id']);
	  $cell[] = $line;
	  $entry['team_name'] = $cell;
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
   * @param  string  $sort   criteria to sort officials
   * @return array   array of users
   */
  function getOfficials($sort)
    {
      // Retrieve registered players
      $eventId = utvars::getEventId();
      $fields = array('regi_id', 'mber_sexe', 'regi_longName', 
		      'regi_noc', 'regi_type', 'team_noc',  
		      'mber_urlphoto');
      $tables = array('members', 'teams', 'registration');
      $where  = "regi_del != ".WBS_DATA_DELETE.
	" AND mber_Id = regi_memberId".
	" AND team_Id= regi_teamId".
	" AND regi_type >=".WBS_REFEREE.
	" AND regi_type <=". WBS_DELEGUE.
	" AND regi_type <>". WBS_COACH.
      " AND regi_eventId = $eventId".
	" AND mber_Id >= 0";
      $order = "regi_type,".abs($sort);
      if ($sort <0)
	$order .= " DESC";
      $res = $this->_select($tables, $fields, $where, $order, 'regi');
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgNoRegisteredOfficial';
	  return $infos;
	}

      // Prepare a table with the offcials
      $ut = new utils();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $offi[0] = $entry['regi_id'];
	  $offi[1] = $ut->getSmaLabel($entry['mber_sexe']);
	  $offi[2] = $entry['regi_longName'];
	  if ($entry['regi_noc'] != '')
	    $offi[3] = $entry['regi_noc'];
	  else
	    $offi[3] = $entry['team_noc'];
	  $offi[4] = '';
	  $offi[5] = utimg::getPathPhoto($entry['mber_urlphoto']);
	  $offi[6] = $ut->getLabel($entry['regi_type']);
	  $rows[] = $offi;
	}
      return $rows;
    }
  // }}}

  // {{{ getPlayers
  /**
   * Return the list of the registrations
   *
   * @access public
   * @param  string  $sort   criteria to sort players
   * @return array   array of users
   */
  function getPlayers($sort)
    {
      // Retrieve registered players
      $eventId = utvars::getEventId();
      $fields = array('regi_id', 'mber_sexe', 'regi_longName',
		      'mber_licence', 'team_name', 'asso_pseudo', 'team_id',
		      'team_logo', 'mber_urlphoto', 'team_noc', 
		      'asso_noc', 'asso_logo', 'asso_id', 'regi_wo');
      $tables = array('members', 'teams','registration', 'a2t', 'assocs');
      $where  = "regi_del != ".WBS_DATA_DELETE.
	" AND asso_id = a2t_assoId".
	" AND a2t_teamId = team_id".
	" AND mber_Id = regi_memberId".
	" AND team_Id= regi_teamId".
	" AND regi_eventId = $eventId".
	" AND regi_type = ".WBS_PLAYER.
	" AND mber_Id >= 0";
      $order = abs($sort);
      if ($sort < 0)
	$order .= " DESC";
      $res = $this->_select($tables, $fields, $where, $order, 'regi');
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgNoRegisteredPlayer';
	  return $infos;
	}

      // Prepare a table with the players
      $id = -1;
      $rows = array();
      $ut = new utils();
      $trans = array('team_name');
      $trans1 = array('asso_pseudo');
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $entry = $this->_getTranslate('assos', $trans1, $entry['asso_id'], $entry);
	  $entry = $this->_getTranslate('teams', $trans, $entry['team_id'], $entry);
	  $entry['mber_sexe'] = $ut->getSmaLabel($entry['mber_sexe']);
	  if ( $entry['regi_wo'] == WBS_YES)
	    {
	      $class = 'classWo';
	      $lines = array();
	      $lines[] = array('value' =>$entry['mber_sexe'], 'class' => $class);
	      $entry['mber_sexe'] = $lines;
	      $lines = array();
	      $lines[] = array('value' =>$entry['regi_longName'], 'class' => $class);
	      $entry['regi_longName'] = $lines;
	      $lines = array();
	      $lines[] = array('value' =>$entry['mber_licence'], 'class' => $class);
	      $entry['mber_licence'] = $lines;
	      $lines = array();
	      $lines[] = array('value' =>$entry['team_name'], 'class' => $class);
	      $entry['team_name'] = $lines;
	      $lines = array();
	      $lines [] = array('value' =>$entry['asso_pseudo'], 'class' => $class);
	      $entry['asso_pseudo'] = $lines ;
	    }

	  $entry['photo'] = utimg::getPathPhoto($entry['mber_urlphoto']);
	  if ($entry['team_logo'] != '' && $entry['regi_wo'] == WBS_NO)
	    $logo = utimg::getPathFlag($entry['team_logo']);
	  else if ($entry['asso_logo'] != ''&&  $entry['regi_wo'] == WBS_NO)
	    $logo = utimg::getPathFlag($entry['asso_logo']);
	  else  $logo = '';
	  $entry['logo'] = $logo;
	  $rows[] = $entry;
	}
      return $rows;
    }
  // }}}

}
?>