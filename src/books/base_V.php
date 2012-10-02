<?php
/*****************************************************************************
!   Module     : Books
!   File       : $Source: /cvsroot/aotb/badnet/src/books/base_V.php,v $
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

require_once "base.php";



/**
* Acces to the dababase for address book
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/
class BooksBase_V extends BooksBase
{
  
  // {{{ properties
  // }}}

  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  //function BooksBase_V()
  // }}}
  
  // {{{ getBookContacts
  /**
   * Return the list of the contacts of a book
   *
   * @access public
   * @param  string  $sort    criteria to sort users
   * @param  integer $bookId  id of the book
   * @return array   array of users
   */
  function getBookContacts($sort,$bookId=-1)
    {
      $ut = new Utils();
      $indx =abs($sort)-1;

      // Retrive the autorization of the current user
      // for the cuurent book
      if ($bookId==-1) $bookId=$ut->getThemeId();
      $userId = $ut->getUserId();
      $query = "SELECT rght_status";
      $query .= " FROM aotb_rights";
      $query .= " WHERE rght_theme=". WBS_THEME_BOOK;
      $query .= " and rght_themeId=$bookId";
      $query .= " and rght_userId  = $userId ";
      $res = $db->query($query);
      $right = WBS_AUTH_VISITOR;
      if ($res->numRows())
	{
	  $entry =$res->fetchRow(DB_FETCHMODE_ASSOC); 
	  $right=$entry['rght_status'];
	}
      $rightDef = array(WBS_AUTH_VISITOR => WBS_DATA_PUBLIC, 
			WBS_AUTH_GUEST   => WBS_DATA_PRIVATE,
			WBS_AUTH_FRIEND  => WBS_DATA_CONFIDENT);

      $query = "SELECT ctac_id, mber_sexe, ";
      $query .= " concat(mber_secondname, ' ',mber_firstname) as nom, ";
      $query .= "ctac_value, ctac_cmt, ctac_type, ctac_memberId ";
      $query .= "FROM aotb_rel_adbk_ctac, aotb_members, ";
      $query .= "aotb_contacts, aotb_addressbook ";
      $query .= "WHERE bkct_bookId = $bookId";
      $query .= " and bkct_bookId = adbk_id";
      //$query .= " and ctac_pbl = ".WBS_DATA_PUBLIC;
      //$query .= " and adbk_pbl = ".WBS_DATA_PUBLIC;
      $query .= " and ctac_pbl <= ".$rightDef[$right];
      $query .= " and adbk_del = ".WBS_DATA_UNDELETE;
      $query .= " and ctac_del = ".WBS_DATA_UNDELETE;
      $query .= " and bkct_contactId = ctac_id ";
      $query .= " and ctac_memberId  = mber_id ";

      $res = $db->query($query);
      $nb = 0;
      $rows=array();
      $key=array();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
          $nb++;
          if ($entry[1] != "")
	    $entry[1] = $ut->getSmaLabel($entry[1]);
	  $rows[] = $entry;
          $key[] = $entry[$indx];
        }
      
      $query = "SELECT -ctac_id, asso_stamp, ";
      $query .= " asso_name, ctac_value, ctac_cmt, ";
      $query .= " ctac_type, ctac_assocId ";
      $query .= "FROM aotb_rel_adbk_ctac, aotb_assocs, ";
      $query .= "aotb_contacts, aotb_addressbook ";
      $query .= "WHERE bkct_bookId = $bookId ";
      $query .= " and bkct_bookId = adbk_id";
      //$query .= " and ctac_pbl = ".WBS_DATA_PUBLIC;
      $query .= " and ctac_del = ".WBS_DATA_UNDELETE;
      //$query .= " and adbk_pbl = ".WBS_DATA_PUBLIC;
      $query .= " and adbk_del = ".WBS_DATA_UNDELETE;
      $query .= " and ctac_pbl <= ".$rightDef[$right];
      $query .= " and bkct_contactId = ctac_id ";
      $query .= " and ctac_assocId  = asso_id ";
      $query .= "LIMIT 0,". ($first+$step);    
      
      $res = $db->query($query);
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $nb++;
	  $rows[] = $entry;
          $key[] = $entry[abs($indx)];
        }
      if (count($key))
	{
	  if ($sort < 0)
	    arsort($key);
	  else
	    asort($key);
	  $keys=array_keys($key);
	  $end = ($nb < $first+$step)? $nb : $first+$step;
	  for ( $i = $first; $i< $end; $i++)
	    {
	      $tri[] = $rows[$keys[$i]];
	    }   
	  $tri["nbMax"] = $nb;
	}
      else
	{
	  $tri["nbMax"] = 0;
	}
      return $tri;
    }
  // }}}
  

  // {{{ getMail
  /**
   * Return the name and mail
   *
   * @access public
   * @param  integer $ctactId  Id of the contact
   * @return array   array of users
   */
  function getMail($ctacId)
    {
      $ut = new Utils();
      if ($ctacId > 0)
	{
	  $fields('mber_sexe', 'ctac_value',
		  "concat(mber_secondname, ' ',mber_firstname) as nom");
	  $tables = array('members', 'contacts');
	  $where = "ctac_id = $ctacId ";
	  $where .= " AND ctac_memberId = mber_id ";
	}
      else
	{
	  $ctacId *= -1;
	  $fields = array('asso_name as nom', 'ctac_value');
	  $tables = array('assocs', 'contacts');
	  $where = "ctac_id = $ctacId ";
	  $where .= " AND ctac_assocId  = asso_id ";
	}
      $res = $this->_select($tables, $fields, $where);
      return ($res->fetchRow(DB_FETCHMODE_ASSOC));

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
      $fields = array('user_id', 'user_name', 'user_login', 'user_pass',
		      'user_email', 'user_type', 'user_lang', 'user_cre',
		      'user_lastvisit', 'user_cmt', 'user_nbcnx', 'user_updt');
      $tables = array('users');
      $where = "user_id=$userId";
      $res = $this->_select($tables, $fields, $where);
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
  // }}}

}

?>