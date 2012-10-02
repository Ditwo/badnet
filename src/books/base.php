<?php
/*****************************************************************************
!   Module     : Books
!   File       : $Source: /cvsroot/aotb/badnet/src/books/base.php,v $
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

require_once "utils/utbase.php";

/**
* Acces to the dababase for address book
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class BooksBase extends utbase
{

  // {{{ properties
  // }}}
  
  // {{{ getBook
  /**
   * Return the column of an book
   *
   * @access public
   * @param  string  $bookId   id of the address book
   * @return array   information of the user if any
   */
  function getbook($bookId)
    {
      $tables = array('addressbook', 'users');
      $fields = array('adbk_id', 'adbk_ownerId', 'adbk_name', 'adbk_cmt',
		      'user_name as adbk_owner');
      $where  = "adbk_ownerId = user_id ".
	" AND adbk_id = '$bookId'";
      $res = $this->_select($tables, $fields, $where);
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
  // }}}
  
  // {{{ updateBook
  /**
   * Add or update the adress book with the informations
   *
   * @access public
   * @param  string  $info   column of the event
   * @return mixed
   */
  function updateBook($infos)
    {
      if ($infos["adbk_id"] != -1)
	{
	  $where = "adbk_id=".$infos['adbk_id'];
	  $res = $this->_update('addressbook', $infos, $where);
	}
      else
	{
	  $res = $this->_insert('addressbook', $infos);
	}
      return true;
      
    }
  // }}}
  
  
  // {{{ publiBooks
  /**
   * Change the publication status of some address books
   *
   * @access public
   * @param  arrays  $books   id's of the books to modify
   * @param  integer $status   new status of the books
   * @return mixed
   */
  function publiBooks($books, $status)
    {
      foreach ( $books as $book => $id)
        {
	  // Update the publication flag of the book
          $infos['adbk_pbl'] = $status;
	  $where = "adbk_id=$id ";
	  $res = $this->_update('addressbook', $infos, $where);
	  
	  // Update the publication flag of the contact
	  $tables = array('contacts', 'c2b');
	  $fields = array('ctac_id'); 
	  $where  = "c2b_bookId = $id" .
	    " AND c2b_contactId = ctac_id";
	  $res = $this->_select($tables, $fields, $where);
	  while ($id = $res->fetchRow(DB_FETCHMODE_ASSOC))
	    {
	      $data['ctac_pbl'] = $status;
	      $where = "ctac_id =". $id['ctac_id'];
	      $res = $this->_update('contacts', $data, $where);
	    }
        } 
      return true;
      
    }
  // }}}
  
  
  // {{{ publiContacts
  /**
   * Change the publication status of some contacts
   *
   * @access public
   * @param  arrays  $contacts   id's of the contacts
   * @param  integer $status   new status of the books
   * @return mixed
   */
  function publiContacts($contacts, $status)
    {
      foreach ( $contacts as $contact)
        {
	  // Update the publication flag of the contact
	  $infos['ctac_pbl'] = $status;
	  $where = "ctac_id=$contact ";
	  $res = $this->_update('contacts', $infos, $where);
        }
      return true;
    }
  // }}}
  
  // {{{ delBooks
  /**
   * Logical delete some address books
   *
   * @access public
   * @param  arrays  $events   id's of the event to delete
   * @return mixed
   */
  function delBooks($books, $status, $user="")
    {
      foreach ( $books as $book => $id)
        {
	  $fields = array('adbk_name');
	  $tables = array('addressbook');
	  $where  = "adbk_id=$id";
          $res = $this->_select($tables, $fields, $where);	  
       	   $book = $res->fetchRow(DB_FETCHMODE_ORDERED);
           if ($status == WBS_DATA_DELETE)
             $name = "!".$book[0]."!";
           else
             $name = $book[0];
	   
           $data['adbk_name'] = $name;
           $data['adbk_del']  = $status;
           $where = "adbk_id=$id";
           if ($user != '') $where .= " AND owner_id=$user";
	   
           $res = $this->_update('addressbook', $data, $where);
        } 
      return true;
        
    }
  // }}}
  
  
  // {{{ updtContact
  /**
   * Add a new contact or update the existing contact into the database
   *
   * @access public
   * @param  string  $infos  new contact informations
   * @return mixed
   */
  function updtContact($infos)
    {
      if ($infos["ctac_id"] != -1)
	{
	  // Update the contact
	  $where = "ctac_id=".$infos['ctac_id'];
	  $res = $this->_update('contacts', $infos, $where);
	}
      
      // Add a new contact
      else
	{
	  unset($infos["ctac_id"]);
	  $res = $this->_insert('contacts', $infos);
	}
      return true;
    }
  // }}}
  
  // {{{ delContacts
  /**
   * Delete some contacts
   *
   * @access public
   * @param  string  $contacts   id's of the contact to delete
   * @return boolean 
   */
  function delContacts($contacts)
    {
      foreach ( $contacts as $ctac)
	{
	  $data = explode(';', $ctac);
	  if ($data[1]=='') continue;
	  $id = $data[1];

	  $where = "c2b_contactId = '$id'";
	  $res = $this->_delete('c2b', $where);
	  
	  $where = "ctac_id = '$id'";
	  $res = $this->_delete('contacts', $where);
	} 
      return true;
    }
  // }}}
  
  // {{{ regContacts
  /**
   * Add a new contact for address book
   *
   * @access public
   * @param  string  $infos  new contact informations
   * @return mixed
   */
  function regContacts($contactIds)
    {
      // Add the relation between contact and book
      $bookId = utvars::getBookId();
      foreach ( $contactIds as $ctac)
	{
	  $data = explode(';', $ctac);
	  if ($data[1]=='') continue;
	  $id = $data[1];
	  $fields = array('c2b_id');
	  $tables = array('c2b');
	  $where  = "c2b_contactId = $id" .
	    " AND c2b_bookId = $bookId";
	  $res = $this->_select($tables, $fields, $where);
	  
	  if (!$res->numRows())
	    {
	      $cols['c2b_contactId'] = $id;
	      $cols['c2b_bookId'] = $bookId;
	      $res = $this->_insert('c2b', $cols);
	    }
	}
      return true;
    }
  // }}}
  
  // {{{ remContacts
  /**
   * remove the selected contact from the current book
   *
   * @access public
   * @param  string  $infos  new contact informations
   * @return mixed
   */
  function remContacts($contactIds)
    {
      // Delete the relation between contact and book
      $bookId = utvars::getBookId();
      
      if (is_array($contactIds))
	{
	  foreach ( $contactIds as $ctac => $id)
	    {
	      $where = "c2b_id = '$id' ";
	      $res = $this->_delete('c2b', $where);
	    }
	  }
      return true;
    }
  // }}}
  
}

?>