<?php
/*****************************************************************************
!   Module     : subscript
!   File       : $Source: /cvsroot/aotb/badnet/src/subscript/base_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.5 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:21 $
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
* Acces to the dababase for subscription
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class subsBase_V extends utbase
{

  // {{{ properties
  // }}}

  // {{{ delSubsTeams
  /**
   * Delete subscription
   *
   * @access private
   * @param  integer $eventId   Id of the event
   * @param  integer $teamId    Id of the team
   * @param  char    $email     Email
   * @return integer list of email subscribers
   */
  function delSubsTeams($eventId, $teamId, $email)
    {
      // Search the users concerned by this match
      $where = "subs_eventId = $eventId".
	" AND subs_subId = $teamId".
	" AND subs_type =".WBS_SUBS_TEAM.
	" AND subs_email = '$email'".
	" AND subs_userid =". utvars::getUserId();
      $res = $this->_delete('subscriptions', $where);
      return;
    }
  // }}}

  // {{{ delSubsNews
  /**
   * Delete subscription
   *
   * @access private
   * @param  integer $eventId   Id of the event
   * @param  char    $email     Email
   * @return integer list of email subscribers
   */
  function delSubsNews($eventId, $email)
    {
      // Search the users concerned by this match
      $where = "subs_eventId = $eventId".
	" AND subs_type =".WBS_SUBS_NEWS.
	" AND subs_email = '$email'".
	" AND subs_userid =". utvars::getUserId();
      $res = $this->_delete('subscriptions', $where);
      return;
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
      $fields[] = 'subs_subId';
      $tables[] = 'subscriptions';
      $where = "subs_userid = $userId".
	" AND subs_type =".WBS_SUBS_NEWS.
	" AND subs_email = '$email'";

      $res = $this->_select($tables, $fields, $where);
      return ($res->numRows());
    }
  // }}}


  // {{{ addSubsTeam
  /**
   * Return the list of subscribers 
   *
   * @access private
   * @param  integer $eventId   Id of the event
   * @param  integer $teamId    Id of the team
   * @param  char    $email     Email
   * @return integer list of email subscribers
   */
  function addSubsTeam($eventId, $teamId, $email)
    {
      // Search the users concerned by this match
      $fields[] = '*';
      $tables[] = 'subscriptions';
      $where = "subs_eventId = $eventId".
	" AND subs_subId = $teamId".
	" AND subs_type =".WBS_SUBS_TEAM.
	" AND subs_email = '$email'";
      $res = $this->_select($tables, $fields, $where);

      // Subscription already done
      if($res->numRows()) {return;}

      // Add new subscritpion
      $fields = array();
      $fields['subs_userId'] = utvars::getUserId();
      $fields['subs_eventId'] = $eventId;
      $fields['subs_email'] = $email;
      $fields['subs_subId'] = $teamId;
      $fields['subs_type'] = WBS_SUBS_TEAM;
      $res = $this->_insert('subscriptions', $fields);
    }
  // }}}

  // {{{ addSubsNews
  /**
   * Add subscribe to the news of the event
   *
   * @access private
   * @param  integer $eventId   Id of the event
   * @param  char    $email     Email
   * @return integer list of email subscribers
   */
  function addSubsNews($eventId, $email)
    {
      // Search the users concerned by this match
      $fields[] = '*';
      $tables[] = 'subscriptions';
      $where = "subs_eventId = $eventId".
	" AND subs_type =".WBS_SUBS_NEWS.
	" AND subs_email = '$email'";
      $res = $this->_select($tables, $fields, $where);

      // Subscription already done
      if($res->numRows()) {return;}

      // Add new subscritpion
      $fields = array();
      $fields['subs_userId'] = utvars::getUserId();
      $fields['subs_eventId'] = $eventId;
      $fields['subs_email'] = $email;
      $fields['subs_subId'] = 0;
      $fields['subs_type'] = WBS_SUBS_NEWS;
      $res = $this->_insert('subscriptions', $fields);
    }
  // }}}


  // {{{ getNewsSubscribers
  /**
   * Return the list of subscribers 
   *
   * @access private
   * @param  integer $eventId   Id of the event
   * @return integer list of email subscribers
   */
  function getNewsSubscribers($eventId)
    {
      $subscribers = array();

      // Search the users concerned by this match
      $fields[] = 'subs_email';
      $tables[] = 'subscriptions';
      $where = "subs_type =".WBS_SUBS_NEWS.
	" AND subs_eventId=$eventId";
      $res = $this->_select($tables, $fields, $where);

      while ($subcriber = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  $subscribers[] = $subcriber['subs_email'];

      return $subscribers;
    }
  // }}}

  // {{{ getLastNews
  /**
   * Return the last news of the event
   *
   * @access private
   * @param  integer $eventId   Id of the event
   * @return integer list of email subscribers
   */
  function getLastNews($eventId)
    {
      // Search the users concerned by this match
      $fields[] = 'news_text';
      $tables[] = 'news';
      $where = "news_eventId=$eventId";
      $order = "news_cre DESC";
      $res = $this->_select($tables, $fields, $where, $order);

      $news = $res->fetchRow(DB_FETCHMODE_ASSOC);
      return $news['news_text'];
    }
  // }}}

  
  // {{{ getMatchSubscribers
  /**
   * Return the list of subscribers for a match
   *
   * @access private
   * @param  integer $matchId   Id of the match
   * @return integer list of email subscribers
   */
  function getMatchSubscribers($matchId)
    {
      $subscribers = array();

      // Search the users concerned by this match
      $fields[] = 'subs_email';
      $tables[] = 'subscriptions';
      $where = "subs_subId = $matchId".
	" AND subs_type =".WBS_SUBS_MATCH;
      $res = $this->_select($tables, $fields, $where);
      while ($subcriber = $res->fetchRow(DB_FETCHMODE_ORDERED))
	  $subscribers[] = $subcriber['subs_email'];

      // Treat the teams of the matchs
      $teams = $this->getMatchTeams($matchId);     


      // Search the users concerned by this team
      if (count($teams))
	{
	  $where = "(subs_subId = {$teams[0]['team_id']} OR subs_subId = {$teams[1]['team_id']})".
	    " AND subs_type =".WBS_SUBS_TEAM;
	  $res = $this->_select($tables, $fields, $where);
	  while ($subs = $res->fetchRow(DB_FETCHMODE_ASSOC))
	    $subscribers[] = $subs['subs_email'];
	}
      
      // Treat the players of the matchs
      $players = $this->getMatchPlayers($matchId);     
      foreach($players as $player)
	{
	  // Search the users concerned by this team
	  $where = "subs_subId = {$player['regi_id']}".
	    " AND subs_type =".WBS_SUBS_PLAYER;
	  $res = $this->_select($tables, $fields, $where);
	  while ($subs = $res->fetchRow(DB_FETCHMODE_ASSOC))
	    $subscribers[] = $subs['subs_email'];
	}

      // Return subscribers
      if (count($subscribers))
	return $subscribers;
      else
	return 0;
    }
  // }}}

  // {{{ getMatchGroup
  /**
   * Return the divisin and group concerned by the match
   *
   * @access private
   * @param  integer $matchId   Id of the match
   * @return integer list of email subscribers
   */
  function getMatchGroup($matchId)
    {
      $teams = array();

      // Search the teams by this match
      $fields = array('rund_name', 'draw_name');
      $tables = array('rounds', 'draws', 'matchs', 'ties');
      $where = "mtch_id = $matchId".
	" AND mtch_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id";
      $res = $this->_select($tables, $fields, $where);
      return $res->fetchRow(DB_FETCHMODE_ASSOC);

    }
  // }}}

  // {{{ getMatchTeams
  /**
   * Return the teams concerned by the match
   *
   * @access private
   * @param  integer $matchId   Id of the match
   * @return integer list of email subscribers
   */
  function getMatchTeams($matchId)
    {
      $teams = array();

      // Search the teams concern by this match
      $fields = array('team_id', 'team_name', 't2t_matchW');
      $tables = array('matchs', 't2t', 'teams');
      $where = " mtch_id =$matchId".
	" AND mtch_tieId = t2t_tieId".
	" AND t2t_teamId = team_id";
      $res = $this->_select($tables, $fields, $where);
      while ($team = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $teams[] = $team;
	}
      return $teams;
    }
  // }}}

  // {{{ getTieResult
  /**
   * Return the result of a tie
   *
   * @access private
   * @param  integer $matchId   Id of the match
   * @return integer list of email subscribers
   */
  function getTieResult($tieId)
    {
      $teams = array();

      // Search the teams by this match
      $fields = array('team_id', 'team_name', 't2t_matchW');
      $tables = array('t2t', 'teams');
      $where = "t2t_tieId = $tieId".
	" AND t2t_teamId = team_id";
      $res = $this->_select($tables, $fields, $where);
      while ($team = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $teams[] = $team;
	}
      return $teams;
    }
  // }}}

  // {{{ getMatchPlayers
  /**
   * Return the players concerned by the match
   *
   * @access private
   * @param  integer $matchId   Id of the match
   * @return integer list of email subscribers
   */
  function getMatchPlayers($matchId)
    {
      $players = array();

      // Search the teams by this match
      $fields  = array('mtch_score', 'mtch_discipline', 'mtch_begin','mtch_end',
		       'p2m_result', 'regi_longName', 'team_stamp', 'regi_id');
      $tables = array('matchs', 'p2m', 'i2p', 'registration', 'teams');
      $where = "mtch_id =$matchId".
	" AND mtch_id = p2m_matchId".
	" AND p2m_pairId = i2p_pairId".
	" AND i2p_regiId = regi_id".
	" AND regi_teamId = team_id".
	" ORDER BY p2m_result";
      $res = $this->_select($tables, $fields, $where);
      while ($player = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $players[] = $player;
	}
      return $players;
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
      $fields = array('team_id', 'team_name', 'team_stamp');
      $tables[] = 'teams';
      $where = "team_eventId =". utvars::getEventId();
      $order = abs($sort);
      if ($sort < 0)
	  $order .= " desc";
      
      $res = $this->_select($tables, $fields, $where, $order);
      $rows = array();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $rows[] = $entry;
        }
      return $rows;
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
      $tables[] = 'users';
      $where = "user_id=$userId";
      $res = $this->_select($tables, $fields, $where);
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
  // }}}
  
  // {{{ getEventInfo
  /**
   * Return the general information of the event
   *
   * @return array   array of users
   * @private
   */
  function getEventInfo()
    {
      $fields[] = '*';
      $tables[] = 'events';
      $where = "evnt_id =". utvars::getEventId();
      $res = $this->_select($tables, $fields, $where);
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
  // }}}

  // {{{ getTiesSubscribers
  /**
   * Return the list of subscribers for a tie
   *
   * @access private
   * @param  integer $tieId   Id of the tie
   * @return integer list of email subscribers
   */
  function getTiesSubscribers($tieId)
    {
      $subscribers = array();

      // Search the subscribers
      $fields[] = 'subs_email';
      $tables = array('subscriptions', 't2t');
      $where = "subs_subId = t2t_teamid".
	" AND t2t_tieId = $tieId".
	" AND subs_type =".WBS_SUBS_TEAM;
      $res = $this->_select($tables, $fields, $where);
      if ($res->numRows())
	{
	  while ($subs = $res->fetchRow(DB_FETCHMODE_ASSOC))
	    $subscribers[] = $subs['subs_email'];
	return $subscribers;
	}
      return 0;
    }
  // }}}


  // {{{ getAdminEmail
  /**
   * Return the list of subscribers 
   *
   * @access private
   * @return integer list of administrators email 
   */
  function getAdminEmail()
    {
      $subscribers = array();

      // Search the users concerned by this match
      $fields[] = 'user_email';
      $tables[] = 'users';
      $where = "user_type =".WBS_AUTH_ADMIN;
      $res = $this->_select($tables, $fields, $where);
      while ($subcriber = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  $subscribers[] = $subcriber['user_email'];

      return $subscribers;
    }
  // }}}

}
?>