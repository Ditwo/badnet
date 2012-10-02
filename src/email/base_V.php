<?php
/*****************************************************************************
!   Module     : email
!   File       : $Source: /cvsroot/aotb/badnet/src/email/base_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.2 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2006/03/29 19:36:02 $
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
* Acces to the dababase for users
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class emailBase_V extends utbase
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
		       'user_lastvisit', 'user_cmt', 'user_nbcnx' ,'user_updt');
      $tables[] = 'users';
      $where = "user_id=$userId";
      $res = $this->_select($tables, $fields, $where);
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
  // }}}
  
}
?>