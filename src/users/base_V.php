<?php
/*****************************************************************************
!   Module     : users
!   File       : $Source: /cvsroot/aotb/badnet/src/users/base_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.7 $
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

require_once "base.php";


/**
* Acces to the dababase for users
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class userBase_V extends userBase
{

  // {{{ properties
  // }}}
  

  // {{{ getManageEvents
  /**
   * Return the events with the managementright
   *
   * @access public
   * @param  string  $eventId   id of the event
   * @return array   information of the user if any
   */
  function getManageEvents($status = WBS_AUTH_MANAGER)
    {
      $fields = array('evnt_id', 'evnt_season', 'evnt_name');
      $tables = array('events', 'rights');
      $where = 'rght_theme='. WBS_THEME_EVENT.
	' AND rght_userId='.utvars::getUserId().
	" AND rght_status='$status'".
	' AND evnt_id=rght_themeId';
    $order = 'evnt_season DESC';
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	$events['errMsg'] = 'msgNoManageEvents';

      while ($event = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $key = "{$event['evnt_id']}";
	  //$events[$key] = $event['evnt_name'];
	  $events[] = $event;
	  
	}
      return $events;
    }
  // }}}

  // {{{ getFriendEvents
  /**
   * Return the events with the assos id with the friend right
   *
   * @access public
   * @param  string  $eventId   id of the event
   * @return array   information of the user if any
   */
  function getFriendEvents()
    {
      $fields = array('evnt_id', 'evnt_name', 'a2t_assoId');
      $tables = array('events', 'teams', 'rights', 'a2t');
      $where = 'rght_theme='. WBS_THEME_ASSOS.
	' AND rght_userId='.utvars::getUserId().
	' AND a2t_teamId = team_id'.
	' AND a2t_assoId = rght_themeId'.
	' AND evnt_type !='.WBS_EVENT_TEAM.
	' AND team_eventId = evnt_id';
      $res = $this->_select($tables, $fields, $where, false);
      if (!$res->numRows())
	$events['errMsg'] = 'msgNoFriendEvents';

      while ($event = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $events[$event['evnt_id']] = $event;
	}
      return $events;
    }
  // }}}

  // {{{ getFriendAsso
  /**
   * Return the association of the curent member
   *
   * @access public
   * @return array   name of the association if any
   */
  function getFriendAsso()
    {
      $fields = array('asso_name', 'asso_stamp');
      $tables = array('rights', 'assocs');
      $where = 'rght_theme='. WBS_THEME_ASSOS.
	' AND rght_userId='.utvars::getUserId().
	' AND asso_id = rght_themeId';
      $res = $this->_select($tables, $fields, $where);
      if (!$res->numRows())
	{
	  $asso['asso_name'] = 'msgNoFriendAsso';
	  return $asso;
	}
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
  // }}}



  // {{{ setUser
  /**
   * Update the database with the information of a user 
   *
   * @access public
   * @param  string  $info   column of the user
   * @return boolean 
   */
  function setUser($infos)
    {
      $ut = new utils();
      $ut->setPref('skin', $infos['user_skin']);
      unset($infos['user_skin']);
      
      $where = "user_id=".$infos['user_id'];
      $res = $this->_update('users', $infos, $where);
      utvars::setLanguage($infos['user_lang']);
      return true;
        
    }
  // }}}
  
  // {{{ getUserFromEmail
  /**
   * Retrieve login of user from an email
   *
   * @access public
   * @param  string  $info   column of the user
   * @return boolean 
   */
  function getUserFromEmail($email)
    {
      $fields = array('user_id', 'user_login');
      $tables = array('users');
      $where = "user_email='$email'";
      $res = $this->_select($tables, $fields, $where);
      $rows = array();
      while($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $data['user_email'] = $email;
	  $rows[] = $data;
	}
      return $rows;
    }
    // }}}

    // {{{ getUserFromLogin
    /**
     * Retrieve email of user from his login
     *
     * @access public
     * @param  string  $info   column of the user
     * @return boolean 
     */
    function getUserFromLogin($login)
    {
      $fields = array('user_id', 'user_email');
      $tables = array('users');
      $where = "user_login='$login'";
      $res = $this->_select($tables, $fields, $where);
      $rows = array();
      while($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $data['user_login'] = $login;
	  $rows[] = $data;
	}
      return $rows;
    }
    // }}}


}
?>