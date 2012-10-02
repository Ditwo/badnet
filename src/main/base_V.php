<?php
/*****************************************************************************
!   Module     : main
!   File       : $Source: /cvsroot/aotb/badnet/src/main/base_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
!   Author     : G.CANTEGRIL
!   Mailto     : cage@free.fr
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
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
* Acces to the dababase for users
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class mainBase_V extends utbase
{

  // {{{ properties
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
      $fields = array('user_id', 'user_name', 'user_login', 'user_pass',
		      'user_email', 'user_type', 'user_lang', 'user_cre',
		      'user_lastvisit', 'user_cmt', 'user_nbcnx', 'user_updt',
		      'user_pseudo');
      $tables[] = 'users';
      $where = "user_id=$userId";
      $res = $this->_select($tables, $fields, $where);
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
  // }}}
    

  // {{{ getBooks
  /**
   * Return the list of the address books
   *
   * @access public
   * @param  string  $userId   id of the users
   * @return array   array of books
   */
  function getBooks($userId)
    {
      $fields  = array('adbk_id', 'adbk_name', 'adbk_del', 'adbk_pbl');
      $tables[] = 'addressbook';
      $where = "adbk_del !=". WBS_DATA_DELETE;
      $order = "2 ";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgNoBooks';
	  return $infos;
	}

      $fields = array('rght_themeId', 'rght_status');
      $tables[] = 'rights';
      $where = "rght_theme =".WBS_THEME_BOOK.
	" AND rght_userId = $userId".
	" AND rght_del !=". WBS_DATA_DELETE;
      $ris = $this->_select($tables, $fields, $where);

      $rights = array();
      while ($right = $ris->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $rights[$right[0]] = $right[1];
	}

      $rows = array();
      require_once "utils/utimg.php";
      $uti = new utimg();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  if ( isset($rights[$entry[0]]))
	    $right = $rights[$entry[0]];
	  else
	    $right = WBS_AUTH_VISITOR;
	  if ($entry[3] == WBS_DATA_PUBLIC ||
	      $right != WBS_AUTH_VISITOR)
	    {
	      $entry[4] = $uti->getPubliIcon($entry[2], $entry[3]);
	      $entry[2] = '';
	      $entry[5] = $uti->getIcon($right);
	      $rows[] = $entry;
	    }
	}

      return $rows;
    }
  // }}}


  
  // {{{ getSubscriptions
  /**
   * Return the list of subscription
   *
   * @access public
   * @return array   array of users
   */
  function getSubscriptions($userId)
    {
      $fields = array('evnt_id', 'evnt_name', 'team_name', 'subs_email');
      $tables = array('subscriptions', 'events', 'teams');
      $where = "subs_userId = $userId".
	" AND evnt_id = subs_eventId".
	" AND subs_type = ".WBS_SUBS_TEAM.
	" AND team_id = subs_subId"; 
      $order = "1";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgNoSubscribe';
	  return $infos;
        } 

      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
	  $rows[] = $entry;
      $fields = array('evnt_id', 'evnt_name', 'evnt_name', 'subs_email');
      $tables = array('subscriptions', 'events');
      $where = "subs_userId = $userId".
	" AND evnt_id = subs_eventId".
	" AND subs_type = ".WBS_SUBS_NEWS;
      $order = "1";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgNoSubscribe';
	  return $infos;
        } 

      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
	{
	  $entry[2] = '--News--';
	  $rows[] = $entry;
	}


      return $rows;
    }
  // }}}
  
  
}
?>