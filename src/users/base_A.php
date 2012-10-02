<?php
/*****************************************************************************
!   Module     : Users
!   File       : $Source: /cvsroot/aotb/badnet/src/users/base_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2006/04/06 21:23:42 $
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

class userBase_A extends userBase
{

  // {{{ properties
  // }}}

  // {{{ getUsersManager
  /**
   * Return the list of the manager
   *
   * @access public
   * @param  integer $sort   criteria to sort users
   * @return array   array of users
   */
  function getUsersManager($type, $sort)
    {
      $fields = array('user_id', 'user_name', 'user_login' , 'user_pseudo',
		      'user_email', 'user_lang', 'user_nbcnx',
		      'user_lastvisit', 'evnt_name');
      $tables = array('users', 'rights', 'events');
      $where = 	"user_id = rght_userId".
	" AND rght_themeId = evnt_id".
	" AND rght_status ='$type'".
	" AND rght_theme = ".WBS_THEME_EVENT.
	" AND user_del !=".WBS_DATA_DELETE;
      $order = abs($sort);
      if ($sort < 0)
	$order .= " DESC";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $err['errMsg'] = 'msgNoManager';
	  return $err;
	}
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
	$rows[] = $entry;
      return $rows;
    }
  // }}}
  

  
  // {{{ getUsers
  /**
   * Return the list of the users 
   *
   * @access public
   * @param  integer $sort   criteria to sort users
   * @return array   array of users
   */
  function getUsers($sort)
    {
      $fields = array('user_id', 'user_name', 'user_login' , 'user_pseudo',
		      'user_email', 'user_lang', 
		      'user_cre', 'user_lastvisit',
		      'user_nbcnx', 'user_type');
      $tables[] = 'users';
      $where = "user_login != 'demo'".
	" AND user_id !=". utvars::getUserId().
	" AND user_del !=".WBS_DATA_DELETE;
      $order = abs($sort);
      if ($sort < 0)
	$order .= " DESC";
      $res = $this->_select($tables, $fields, $where, $order);
      if( !$res->numRows())
	{
	  $err['errMsg'] = "msgNoMember";
	  return $err;
	} 

      require_once "utils/utimg.php";
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
	{
	  $entry[9] = utimg::getIcon($entry[9]); 
	  $rows[] = $entry;
	}
      return $rows;
    }
  // }}}
  
  // {{{ delUsers
  /**
   * Delete some users
   *
   * @access public
   * @param  string  $users   id's of the user to delete
   * @return boolean 
   */
  function delUsers($usersId)
    {
      $users = implode(',', $usersId);
      // Suppression des utilisateurs
      $where = "user_id in ($users)";
      $res = $this->_delete('users', $where);

      // Suppression des droits
      $where = "rght_userId in ($users)";
      $res = $this->_delete('rights', $where);

      // Suppression des abonnements
      $where = "subs_userId in ($users)";
      $res = $this->_delete('subscriptions', $where);
      
      return true;
    }
  // }}}

  // {{{ getEvents
  /**
   * Return the list of the events
   *
   * @access public
   * @param  integer $userId   User concerned
   * @return array   array of users
   */
  function getEvents($userId)
    {
      // Retrieve all the tournament 
      $fields = array('evnt_id', 'evnt_name');
      $tables = array('events');
      $where  = "evnt_del !=". WBS_DATA_DELETE;
      $order  = "2";
      $res = $this->_select($tables, $fields, $where, $order);
      
      // Retrieve the right of the user
      $fields = array('rght_status', 'rght_themeId');
      $tables = array('rights');
      $where  = "rght_theme =". WBS_THEME_EVENT.
	" AND rght_userId = $userId ".
	" AND rght_del !=". WBS_DATA_DELETE;
      $rs = $this->_select($tables, $fields, $where);
      $rights = array();
      while ($right = $rs->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $rights[$right[1]] = $right[0];
        }

      // Fusion the right with the events
      require_once "utils/utimg.php";
      $fields = array('evnt_name');
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  if ( isset($rights[$entry[0]]))
	    $right = $rights[$entry[0]];
	  else
	    $right = WBS_AUTH_VISITOR;
          $entry[] = '';
          $entry[] = utimg::getIcon($right); 
	  $events[] = $this->_getTranslate('events', $fields, 
					   $entry['evnt_id'], $entry);
        }
      return $events;
    }
  // }}}
  
  // {{{ getAssos
  /**
   * Return the list of the assos
   *
   * @access public
   * @param  integer $userId   User concerned
   * @return array   array of users
   */
  function getAssos()
    {
      // Retrieve all the tournament 
      $fields = array('asso_id', 'asso_name');
      $order  = "2";
      $res = $this->_select('assocs', $fields, false, $order);
      $assos= array();
      while ($asso = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $assos[$asso[0]] = $asso[1];
        }
      return $assos;
    }
  // }}}

}
?>