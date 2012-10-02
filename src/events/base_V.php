<?php
/*****************************************************************************
!   Module     : events
!   File       : $Source: /cvsroot/aotb/badnet/src/events/base_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.13 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/27 22:41:47 $
******************************************************************************/
/**
* Acces to the dababase for events
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class eventBase_V extends utbase
{

  // {{{ properties
  // }}}

  // {{{ getAssosList
  /**
   * Return the list of the registered association
   *
   * @access public
   * @param  integer  $sort   criteria to sort assos
   * @return array   array of users
   */
  function getAssosList()
    {
      // Select associations in the database
      $eventId = utvars::getEventId();
      $fields = array('DISTINCT asso_id', 'asso_name', 'asso_pseudo',  
		      'asso_url', 'asso_logo',);

      $tables = array('teams', 'assocs', 'a2t');
      $where = "team_eventId = $eventId ".
	" AND a2t_assoId = asso_id ".
	" AND a2t_teamId = team_id ";
      $order = 'asso_name';
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
	  $rows[$entry['asso_id']] = $entry['asso_name'];
        }
      return $rows;
    }
  // }}}

  // {{{ getTeamsList
  /**
   * Return the list of the registered teams
   *
   * @access public
   * @return array   array of users
   */
  function getTeamsList()
    {
      // Select associations in the database
      $eventId = utvars::getEventId();
      $fields = array('team_id', 'team_name', 'team_stamp');
      $tables = array('teams', 'assocs', 'a2t');
      $where = "team_eventId = $eventId";
      $order = 'team_name,team_stamp';
      $res = $this->_select('teams', $fields, $where, $order, 'team');
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgNoTeams';
	  return $infos;
	}

      // Prepare a table with the assocation 
      $rows = array();
      $trans = array('team_name');
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $entry = $this->_getTranslate('teams', $trans, 
					$entry['team_id'], $entry);
	  $rows[$entry['team_id']] = "{$entry['team_name']} ({$entry['team_stamp']})";
        }
      return $rows;
    }
  // }}}

  /**
   * Return the list of the players
   *
   * @access public
   * @param  integer $regiId registration's id of player 
   * @return array   array of matchs
   */
  function getPlayersList()
    {
      // list of the matchs of the player
      $fields = array('regi_id', 'regi_longName');
      $where = "regi_eventId =".utvars::getEventId().
	  " AND regi_type=".WBS_PLAYER.
      " AND regi_memberid > 0";
      $order = "2";
      $res = $this->_select('registration', $fields, $where, $order, 'regi');
      $regis=array();
      while ($regi = $res->fetchRow(DB_FETCHMODE_ASSOC))
	$regis[$regi['regi_id']]  = $regi['regi_longName'];
      return $regis;
    }

  // {{{ getNews
  /**
   * Return the column of the news of an event
   *
   * @access public
   * @param  string  $eventId   id of the event
   * @return array   information of the user if any
   */
  function getNews()
    {
      $fields = array('news_id', 'news_cre', 'news_text', 
		      'news_page', 'evnt_id');
      $tables = array('news', 'events');
      $where = "news_eventId = evnt_id".
	" AND evnt_id =".utvars::getEventId();
      $order = "news_cre DESC";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows()) 
	{
	  $infos['errMsg'] = 'msgNoNews';
	  return;
	} 

      $utd = new utdate();
      $fields = array('news_text');
      while($new = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $utd->setIsoDate($new['news_cre']);
	  $new['news_cre'] = $utd->getDate();

	  $news[] = $this->_getTranslate('news', $fields, 
					 $new['news_id'], $new);

	}
      return $news;
    }
  // }}}

  
  // {{{ getEvents
  /**
   * Return the list of the events
   *
   * @access public
   * @param  string  $sort   criteria to sort users
   * @return array   array of users
   */
  function getEvents($userId, $season)
    {
      $fields = array('evnt_id', 'evnt_name', 'evnt_date', 
		      'evnt_nbvisited', 'evnt_del',
		      'evnt_pbl', 'evnt_level',);
      $where = "evnt_del !=". WBS_DATA_DELETE;
      $where .= " AND evnt_season=$season";
      $order = "evnt_level DESC, evnt_name";
      $res = $this->_select('events', $fields, $where, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgNoEvents';
	  return $infos;
	}
      $fields = array('rght_themeId', 'rght_status');
      $where = " rght_theme =".WBS_THEME_EVENT.
	" AND rght_userId = $userId".
	" AND rght_del !=". WBS_DATA_DELETE;

      $ris = $this->_select('rights', $fields, $where);
      $rights = array();
      while ($right = $ris->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $rights[$right[0]] = $right[1];
	}

      $rows = array();
      require_once "utils/utimg.php";
      $uti = new utimg();
      $fields = array('evnt_name');
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  if ( isset($rights[$entry['evnt_id']]))
	    $right = $rights[$entry['evnt_id']];
	  else
	    $right = WBS_AUTH_VISITOR;
	  if ($entry['evnt_pbl'] == WBS_DATA_PUBLIC ||
	      $right != WBS_AUTH_VISITOR)
	    {
	      $entry['publiIcon'] = $uti->getPubliIcon($entry['evnt_del'], $entry['evnt_pbl']);
	      $entry['evnt_del'] = ' ';
	      $entry['publiRight'] = $uti->getIcon($right);
	      $entry['evnt_right'] = $right;
	      // Recherche des donnees dans la langue 
	      $rows[] = $this->_getTranslate('events', $fields, 
					     $entry['evnt_id'], $entry);
	    }
	}
      return $rows;
    }
  // }}} 

  // {{{ getEventStats
  /**
   * Return general informations about events
   *
   * @return array   array of informations
   *
   * @private
   */
  function getEventStats()
    {
      $eventId = utvars::getEventId();
      $where = "team_del !=".WBS_DATA_DELETE.
	" AND team_eventId=$eventId".
	" AND team_pbl=".WBS_DATA_PUBLIC;
      $res = $this->_select('teams', 'COUNT(*)', $where);

      $entry = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $infos['nbTeams'] = $entry[0];

      $where = "regi_del !=".WBS_DATA_DELETE.
	" AND regi_type =".WBS_PLAYER.
	" AND regi_eventId=$eventId".
	" AND regi_pbl=".WBS_DATA_PUBLIC;
      $res = $this->_select('registration', 'COUNT(*)', $where);

      $entry = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $infos['nbPlayers'] = $entry[0];

      $tables = array('matchs','ties', 'rounds', 'draws');
      $where = "mtch_del !=".WBS_DATA_DELETE.
	" AND mtch_tieId=tie_id".
	" AND tie_roundId=rund_id".
	" AND tie_isBye=0".
	" AND rund_drawId=draw_id".
	" AND draw_eventId=$eventId";
      $res = $this->_select($tables, 'COUNT(*)', $where);

      $entry = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $infos['nbMatches'] = $entry[0];

      $fields = array('subs_email');
      $tables = array('subscriptions');
      $where  = "subs_eventId = $eventId".
        " GROUP BY subs_email";
      $res = $this->_select($tables, $fields, $where);
      $infos['nbSubscribe'] = $res->numRows();

      return $infos;
    }
  // }}}

  // {{{ getUser
  /**
   * Return the column of a user 
   *
   * @access public
   * @param  string  $userId   id of the user
   * @return array   information of the user if any
   */
  function getUser($userId)
    {
      $fields = array('user_id', 'user_name', 'user_login',
		      'user_email', 'user_type', 'user_lang', 'user_cre',
		      'user_lastvisit', 'user_cmt', 'user_nbcnx', 'user_updt');
      $where = "user_id=$userId";
      $res = $this->_select('users', $fields, $where);
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
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
      $fields = array('team_id', 'team_name', 'team_stamp', 'team_logo');
      $where = "team_eventId =". utvars::getEventId();
      $order = abs($sort);
      if ($sort < 0)
	  $order .= " desc";
      
      $res = $this->_select('teams', $fields, $where, $order, 'team');
      $rows = array();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $cell = array();
	  $line['value'] = $entry['team_name'];
	  $line['logo'] = utimg::getPathFlag($entry['team_logo']);
	  $line['action'] = array(KAF_UPLOAD, 'teams', KID_SELECT, $entry['team_id']);
	  $cell[] = $line;
	  $entry['team_name'] = $cell;

	  $rows[] = $entry;
	}
      return $rows;
    }
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


  // {{{ getSubscripts
  /**
   * Return subscript teams list
   *
   * @access private
   * @param  integer $subsId   Id of the subsciption
   * @return integer list of email subscribers
   */
  function getSubscripts($email, $userId)
    {
      // Search the subsciptions for ths user and email
      $fields[] = 'subs_subId';
      $tables[] = 'subscriptions';
      $where = "subs_userid = $userId".
	" AND subs_type =".WBS_SUBS_TEAM.
	" AND subs_email = '$email'";

      $res = $this->_select($tables, $fields, $where);
      $subs = array();
      while($sub = $res->fetchRow(DB_FETCHMODE_ASSOC))
	$subs[] = $sub['subs_subId'];
      return $subs;
    }
  // }}}

  // {{{ isNewsSubs
  /**
   * Is the users subscribe to the news
   *
   * @access private
   * @param  integer $subsId   Id of the subsciption
   * @return integer list of email subscribers
   */
  function isNewsSubs($email, $userId)
    {
      // Search the subsciptions for ths user and email
      $where = "subs_userid = $userId".
	" AND subs_type =".WBS_SUBS_NEWS.
	" AND subs_email = '$email'";

      $res = $this->_select('subscriptions', 'subs_subId', $where);
      return ($res->numRows());
    }
  // }}}
}
?>