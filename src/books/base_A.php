<?php
/*****************************************************************************
!   Module     : Books
!   File       : $Source: /cvsroot/aotb/badnet/src/books/base_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.5 $
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
class BooksBase_A extends BooksBase
{
  // {{{ properties
  // }}}

  // {{{ importContacts
  /**
   * Create the contact for the associations 
   *
   * @access public
   * @param  string  $sort   criteria to sort users
   * @return array   array of users
   */
  function importContacts($contacts)
    {
      
      foreach($contacts as $contact)
	{
	  // Chercher l'association
	  $fields = array('asso_id');
	  $tables = array('assocs', 'a2t', 'teams');
	  $where = "asso_id = a2t_assoId".
	    " AND a2t_teamId = team_id".
	    " AND team_externId =".$contact[CTACT_CLUB_ID];
	  $res = $this->_select($tables, $fields, $where);

	  // Asso non trouve, passage a la suivante
	  if( !$res->numRows()) continue;
	  $res = $this->_select($tables, $fields, $where);
	  $assoc = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $assocId = $assoc['asso_id'];

	  // Ajouter les contacts
	  $this->_updtContact($assocId, $contact, $contact[CTACT_EMAIL]);
	  $this->_updtContact($assocId, $contact, $contact[CTACT_PHONE1]);
	  $this->_updtContact($assocId, $contact, $contact[CTACT_PHONE2]);
	}
    }
  // }}}
  
  // {{{ _updtContact
  /**
   * Update a contact for an assos
   *
   * @access public
   * @param  string  $sort    criteria to sort users
   * @param  integer $bookId  id of the book
   * @return array   array of users
   */
  function _updtContact($assocId, $contact, $value)
    {

      $fields = array('ctac_id');
      $tables = array('contacts');
      $where = "ctac_assocId =".$assocId.
	" AND ctac_value = '$value'".
	" AND ctac_contact ='".$contact[CTACT_NAME]."'";
      $res = $this->_select($tables, $fields, $where);

      // Create it
      if( !$res->numRows())
	{
	  $cols['ctac_assocId'] = $assocId;
	  if ($value == $contact[CTACT_EMAIL])
	    $cols['ctac_type'] = WBS_EMAIL;
	  else if (substr($value,0,2) == '06')
	    $cols['ctac_type'] = WBS_MOBILE;
	  else
	    $cols['ctac_type'] = WBS_PHONE;
	  $cols['ctac_value'] = $value;
	  $cols['ctac_contact'] = $contact[CTACT_NAME];
	  $res = $this->_insert('contacts', $cols);

	  // Add to the current addres book
	  if ($cols['ctac_type'] == WBS_EMAIL)
	    {
	      $cols = array();
	      $cols['c2b_contactId'] = $res;
	      $cols['c2b_bookId'] = utvars::getBookId();
	      $res = $this->_insert('c2b', $cols);
	    }
	}
      return true;
    }
  // }}}
  
  
  // {{{ getBooks
  /**
   * Return the list of the address books
   *
   * @access public
   * @param  string  $sort   criteria to sort users
   * @return array   array of users
   */
  function getBooks($sort)
    {
      $fields = array('adbk_id', 'adbk_name', 'adbk_cmt', 'user_name',
		      'adbk_ownerId', 'adbk_del', 'adbk_pbl');
      $tables = array('addressbook', 'users');
      $where = "adbk_ownerId = user_id ";
      if ($sort < 0)
	{
	  $sort *= -1;
	  $order = $sort." desc";
	}
      else
	$order = $sort;
      
      $res = $this->_select($tables, $fields, $where, $order);
      if( !$res->numRows())
	{
	  $err['errMsg'] = "msgNoBook";
	  return $err;
	} 
      
      require_once "utils/utimg.php";
      $uti = new utimg();
      $res = $this->_select($tables, $fields, $where, $order);
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $entry[7] = $uti->getPubliIcon($entry[5], $entry[6]); 
	  $rows[] = $entry;
        }
      return $rows;
    }
  // }}}
  
  // {{{ getBookMembersContacts
  /**
   * Return the list of the contacts of a book
   *
   * @access public
   * @param  string  $sort    criteria to sort users
   * @param  integer $bookId  id of the book
   * @return array   array of users
   */
  function getBookMembersContacts($sort, $bookId=-1)
    {
      $ut = new Utils();

      $fields = array('c2b_id', 'mber_sexe',
		      "concat(mber_secondname, ' ',mber_firstname) as nom",
		      'ctac_value', 'ctac_cmt', 'ctac_updt', 'ctac_pbl', 
		      'ctac_id', 'mber_id');
      $tables = array('c2b', 'members', 'contacts');
      $where  = "c2b_bookId = $bookId ".
	" AND c2b_contactId = ctac_id ".
	" AND ctac_memberId  = mber_id ";
      $order = abs($sort);
      if ($sort < 0)
	$order .= " DESC";

      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $err['errMsg'] = "msgNoContact";
	  return $err;
	}

      //require_once "utils/utimg.php";
      $res = $this->_select($tables, $fields, $where, $order);
	while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
          if ($entry[1] != "")
	    $entry[1] = $ut->getSmaLabel($entry[1]);
          $entry[9] = utimg::getIcon($entry[6]);
          $rows[] = $entry;
        }
      return $rows;
    }
  // }}}
  
  // {{{ getBookAssosContacts
  /**
   * Return the list of the contacts of a book
   *
   * @access public
   * @param  string  $sort    criteria to sort users
   * @param  integer $bookId  id of the book
   * @return array   array of users
   */
  function getBookAssosContacts($sort, $bookId=-1)
    {
      $fields = array('ctac_id', 'asso_name', 'asso_stamp', 'ctac_contact',
		      'ctac_value', 'asso_id', 'ctac_pbl');
      $tables = array('c2b', 'assocs', 'contacts');
      $where  = "c2b_bookId = $bookId ".
	" AND c2b_contactId = ctac_id ".
	" AND ctac_assocId  = asso_id ";
      $order = abs($sort);
      if ($sort < 0)
	$order .= " DESC";

      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $err['errMsg'] = "msgNoContact";
	  return $err;
	}
      
      $res = $this->_select($tables, $fields, $where, $order);
	while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
          $rows[] = $entry;
        }
      
      return $rows;
    }
  // }}}
  
  // {{{ getContact
  /**
   * Return the contact
   *
   * @access public
   * @param  integer $contactId id of the contact
   * @return array   data of the contact
   */
  function getContact($contactId)
    {
      $fields = array('ctac_id', 'ctac_memberId',
		      'ctac_assocId', 'ctac_value',
		      'ctac_cmt', 'ctac_type', 'ctac_contact');
      $tables = array('contacts');
      $where = "ctac_id = $contactId";

      $res = $this->_select($tables, $fields, $where);
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
  // }}}
  
  // {{{ getContactsMembers
  /**
   * Return the list of the all contacts of all members
   *
   * @access public
   * @param  string  $sort   criteria to sort users
   * @return array   array of users
   */
  function getContactsMembers($sort)
    {
      $ut = new Utils();
      $bookId = utvars::getBookId();
      
      // Retrieve the rows
      $fields = array('ctac_id', 'mber_sexe', 'mber_secondname',
		      'mber_firstname', 'ctac_value', 'ctac_type',
		      'ctac_cmt', 'ctac_updt', 'mber_id');
      $tables = array('members', 'contacts');
      $where = "ctac_memberId = mber_Id";
      if ($sort < 0)
	{
	  $sort *= -1;
	  $order = $sort." desc";
	}
      else
	$order = $sort;

      $res = $this->_select($tables, $fields, $where, $order);
      $nb=0;
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $entry[1] = $ut->getSmaLabel($entry[1]);
	  if ($entry[5] != "")
	    $entry[5] = $ut->getLabel($entry[5]);
	  $rows[$nb++] = $entry;
        }
      
      return $rows;
    }
  // }}}
  

  // {{{ getContactsAssocs
  /**
   * Return the list of the all contacts of all association
   *
   * @access public
   * @param  string  $sort   criteria to sort users
   * @return array   array of users
   */
  function getContactsAssocs($sort)
    {
      $ut = new Utils();
      $bookId = utvars::getBookId();
      
      // Retrieve the rows
      $fields = array('ctac_id', 'asso_name', 'asso_stamp', 'ctac_contact',
		      'ctac_value', 'ctac_type', 'ctac_cmt', 
		      'ctac_updt', 'asso_id');
      $tables = array('assocs LEFT JOIN contacts ON ctac_assocId = asso_Id');
      $order = abs($sort);
      if ($sort < 0)
	  $order .= " DESC";
      $res = $this->_select($tables, $fields, false, $order);
      if (!$res->numRows()) 
	{
	  $err['errMsg'] = "msgNoContact";
	  return $err;
        } 


      $res = $this->_select($tables, $fields, false, $order);
        while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $entry['ctac_id'] = "{$entry['asso_id']};{$entry['ctac_id']}";
	  if ($entry['ctac_type'] != "")
	    $entry[5] = $ut->getLabel($entry['ctac_type']);
           $rows[] = $entry;
        }
      return $rows;
    }
  // }}}
  
  
  // {{{ getAssoBookContacts
  /**
   * Retrieve the list of associaton contact registered in the current book
   *
   * @access public
   * @return array   array of users
   */
  function getAssoBookContacts()
    {
      $bookId = utvars::getBookId();
      $fields = array('ctac_id');
      $tables = array('c2b', 'contacts');
      $where  = "c2b_bookId = $bookId " .
	" AND c2b_contactId = ctac_id ".
	" AND ctac_memberId  = -1 ";
      
      $res = $this->_select($tables, $fields, $where);
      $rows[] = -1;
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $rows[] = $entry[0];
        }
      return $rows;
    }
  // }}}
  
  // {{{ getMemberBookContacts
  /**
   * Return the list of the contacts of a book
   *
   * @access public
   * @return array   array of users
   */
  function getMemberBookContacts()
    {
      $bookId = utvars::getBookId();
      
      $fields = array('ctac_id');
      $tables = array('c2b', 'contacts');
      $where  = "c2b_bookId = $bookId".
	" AND c2b_contactId = ctac_id ".
	" AND ctac_assocId  = -1 ";

      $res = $this->_select($tables, $fields, $where);
      $rows[] = -1;
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $rows[] = $entry[0];
        }
      return $rows;
      
    }
  // }}}


}

?>