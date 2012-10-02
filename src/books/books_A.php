<?php
/*****************************************************************************
!   Module     : Books
!   File       : $Source: /cvsroot/aotb/badnet/src/books/books_A.php,v $
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
require_once "base_A.php";
require_once "books.php";
require_once "utils/utpage_A.php";


/**
* Module de gestion des tournois : classe administrateurs
*
* @author Gerard CANTEGRIL
* @see to follow
*
*/

class books_A extends books
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
  function Books_A()
    {
      $this->_ut = new Utils();
      $this->_dt = new BooksBase_A();
    }
  
    
  // {{{ start()
  /**
   * Start the connexion processus
   *
   * @access public
   * @return void
   */
  function start($page)
    {
      switch ($page)
	{
	  // Display the list of books
	case WBS_ACT_BOOKS:
	  $this->_displayFormList();
	  break;
	  
	  // Creation of a new book
	case KID_NEW : 
	  $this->_displayFormBook();
	  break;
	  // Delete one or more book
	case KID_DELETE:
	  $this->_delBooks(WBS_DATA_DELETE);
	  break;
	  // Restore one or more book
	case KID_UNDELETE:
	  $this->_delBooks(WBS_DATA_UNDELETE);
	  break;
	  // Change the publication status of selected books
	case KID_CONFIDENT:
	  $this->_publiBooks(WBS_DATA_CONFIDENT);
	  break;
	  // Change the publication status of selected books
	case KID_PRIVATE:
	  $this->_publiBooks(WBS_DATA_PRIVATE);
	  break;
	  // Change the publication status of selected books
	case KID_PUBLISHED:
	  $this->_publiBooks(WBS_DATA_PUBLIC);
	  break;
	  
	  // Select an address book
	case KID_SELECT:
	  $this->_displayFormMainBook(kform::getData());
	  break;
	  
	  // Modification of an existing book
	case KID_EDIT : 
	  $this->_displayFormBook(kform::getData());
	  break;
	  
	  // Update the modification of an existing book
	case KID_UPDATE :
	  $this->_updateBook();
	  break;
	  
	  // Display a form to add a new contact for a member
	case BOOKS_NEWMEMBER_A :
	  $this->_displayFormNewMember();
	  break;
	  // Register the new contact in the database
	case BOOKS_ADDMEMBER_A :
	  $this->_addNewMember();
	  break;
	  // Display a form to add a new contact for a member
	case BOOKS_NEWMBERCTAC_A :
	  $infos = array( "ctac_memberId" => kform::getDataId(),
			  "ctac_assocId" => -1);
	  $this->_displayFormContact($infos);
	  break;
	  
	case BOOKS_PRIVATECTAC_A:
	  $this->_publiContacts(WBS_DATA_CONFIDENT);
	  break;
	  // Change the publication status of selected books
	case BOOKS_HIDDENCTAC_A:
	  $this->_publiContacts(WBS_DATA_PRIVATE);
	  break;
	  // Change the publication status of selected books
	case BOOKS_PUBLISHEDCTAC_A:
	  $this->_publiContacts(WBS_DATA_PUBLIC);
	  break;
	  
	  
	  // Display a form to add a new contact for an association
	case BOOKS_NEWASSOC_A :
	  $this->_displayFormNewAssoc();
	  break;
	  // Register the new contact in the database
	case BOOKS_ADDASSOC_A :
	  $this->_addNewAssoc();
	  break;
	  // Display a form to add a new contact for an association
	case BOOKS_NEWASSOCCTAC_A :
	  $data = explode(';', kform::getData());
	  if ($data[1]=='')
	    {
	      $infos = array( 'ctac_memberId' => -1,
			      'ctac_assocId' => $data[0]);
	    }
	  else
	    $infos['ctac_id'] = $data[1];
	  $this->_displayFormContact($infos);
	  break;
	  
	  
	  // Display a form to edit an existing contact
	case BOOKS_EDITCONTACT_A :
	  $infos = array( "ctac_id" => kform::getData());
	  $this->_displayFormContact($infos);
	  break;
	  
	  // Delete a contact from the database
	case BOOKS_DELASSOCONTACT_A :
	  $this->_delAssoContacts();
	  break;
	  
	  // Delete a contact from the database
	case BOOKS_DELMBERCONTACT_A :
	  $this->_delMberContacts();
	  break;
	  
	  // Register the new contact in the database
	case BOOKS_UPDTCONTACT_A :
	  $this->_updtContact();
	  break;
	  
	  // Add the selected contact to the book
	case BOOKS_REGMBERCONTACT_A :
	  $this->_regMberContacts();
	  break;
	  
	  // Add the selected contact to the book
	case BOOKS_REGASSOCONTACT_A :
	  $this->_regAssoContacts();
	  break;
	  
	  // Remove contact from the book
	case BOOKS_REMCONTACT_A :
	  $this->_remContacts();
	  break;

	  // Display a form to select users form right
	case BOOKS_RIGHT_A :
	  $this->_displayFormRight(kform::getDataId());
	  break;
	  // Updating right indatabse
	case BOOKS_UPDATE_RIGHT_A :
	  $this->_updateRight();
	  break;

	  // Select asso for a new contat
	case BOOKS_SELASSOCONTACT_A:
	  $this->_selectAsso();
	  break;

	case BOOKS_VALIDASSOCONTACT_A:
	  $infos = array( 'ctac_memberId' => -1,
			  'ctac_assocId' => kform::getInput('assos'));
	  $this->_displayFormContact($infos);
	  break;

	case BOOKS_SELECT_FILE_A:
	  $this->_displayFormSelectFile();
	  break;
	case BOOKS_IMPORT_FILE_A:
	  $this->_importFile();
	  break;
	case BOOKS_READ_FILE_A:
	  $this->_readFile();
	  break;

	default:	
	  echo "page $page demand�e depuis books_A<br>";
	  exit();
	}
    }
  // }}}

  // {{{ _readFile()
  /**
   * display the export form
   *
   * @access public
   * @param string $err Error message
   * @return void
   */
  function _readFile($err="")
    {
      $dt = $this->_dt;

      require_once "import/impt3f.php";
      $parser = new impt3f();
      $contacts = $parser->parseContacts();
      $nbPlayers = count($contacts);
      $dt->importContacts($contacts);
      exit;
    }
  //}}}    

  // {{{ _importFile()
  /**
   * display the export form
   *
   * @access public
   * @param string $err Error message
   * @return void
   */
  function _importFile($err="")
    {
      require_once "import/impt3f.php";
      $fields['kpid']   = 'books';
      $fields['kaid']   = BOOKS_READ_FILE_A;
      //$fields['bookId'] = kform::getInput('inscOne');

      $fileObj = kform::getInput('importFile', NULL);
      //print_r($fileObj);
      if (is_array($fileObj))
	$fileName = $fileObj['tmp_name'];
      else
	$fileName = $fileObj;
      if ($fileName == NULL)
	$this->displayImport('msgSelectFile');
      //$fileName = "http://www.v3f-badminton.org/template/tournois/csv.php";

      $parser = new impt3f();
      $parser->loadFile($fields, $fileName);
      exit;
    }
  //}}}    

  // {{{ _displayFormSelectFile()
  /**
   * Display a form to modify an address book
   *
   * @access private
   * @return void
   */
  function _displayFormSelectFile()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;

      $utpage = new utPage('books');
      $content =& $utpage->getPage();
      $kform =& $content->addForm('fbooks', 'books', BOOKS_IMPORT_FILE_A);
      $kform->setTitle("tAddContact");

      $kedit =& $kform->addFile('importFile', '', 30);
      $kedit->setMaxLength(200);
      $kedit->noMandatory();
      
      $kform->addBtn('btnRegister', KAF_SUBMIT);
      $kform->addBtn('btnCancel');
      $elts = array('btnRegister', 'btnCancel');
      $kform->addBlock("blkBtn", $elts);
      
      //Display the form
      $utpage->display(false);
      exit; 
    }
  // }}}


  // {{{ _updateRight()
  /**
   * Update the right for selected users
   *
   * @access private
   * @return void
   */
  function _updateRight()
    {
      $ut = $this->_ut;
      
      // Initialize the field
      $usersId = kform::getInput("rowsUsers");

      $infos=array("rght_theme"   => WBS_THEME_BOOK,
		   "rght_themeId" => kform::getInput("adbk_id"),
		   "rght_status"  => kform::getInput("type"));

      while ($id = @array_pop($usersId))
	{
	  $infos["rght_userId"] = $id;
	  $ut->addRight($infos);
	}
      //Display the form
      $this->_displayFormRight();
      exit; 
    }
  // }}}

  
  // {{{ _displayFormRight()
  /**
   * Display a form to fixe the right of the user to the current book
   *
   * @access private
   * @param mixed  $book  Id or data of the adress book to modify.
   * @return void
   */
  function _displayFormRight($book="")
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      $form = $ut->newForm_A("books", BOOKS_RIGHT_A, false); 
      $form->setTitle("");
      
      
      // Initialize the field
      $infos = array('adbk_id'      => -1,
		     'adbk_name'    => "",
		     'adbk_ownerId' =>$ut->getUserId(),
		     'adbk_cmt'     => ""
		     );
      
      // Get the data of the book
      if (is_array($book))
	$infos = $book;
      else
	$infos = $dt->getBook($ut->getThemeId());
      
      $form->setTitle('tRightBook');
            
      // Initialize the field
      $form->addHide("adbk_id", $infos['adbk_id']);
      $form->addInfo("bookName", $infos['adbk_name']);

      // Display a warning if an error occured
      if (isset($infos['errMsg']))
	$form->addWng($infos['errMsg']);

      // Fix the pager attributes
      $sort = $form->getSort("rowsUsers");

      // Retrieve the list of user with the right on the book
      $users = $dt->getUsers($infos['adbk_id'], $sort);
      if (isset($users['errMsg']))
	$form->addWng($users['errMsg']);

      $form->addRows("rowsUsers", $users);
      
      $select =  kform::getInput("type");
      if ($select=="") $select = WBS_AUTH_VISITOR;
      $form->addRadio("type", $select==WBS_AUTH_MANAGER,
		      WBS_AUTH_MANAGER);
      $form->addRadio("type", $select==WBS_AUTH_ASSISTANT,
		      WBS_AUTH_ASSISTANT);
      $form->addRadio("type", $select==WBS_AUTH_FRIEND,
		      WBS_AUTH_FRIEND);
      $form->addRadio("type", $select==WBS_AUTH_GUEST,
		      WBS_AUTH_GUEST);
      $form->addRadio("type", $select==WBS_AUTH_VISITOR,
		      WBS_AUTH_VISITOR);

      $form->addBloc("blkRight", "type");

      
      $form->addBtn("btnRight", KAF_CHECKFIELD, BOOKS_UPDATE_RIGHT_A);
      $form->addBtn("btnEnd");

      $form->addImg("manage");
      $form->addImg("assistant");
      $form->addImg("friend");
      $form->addImg("guest");
      $form->addImg("user");
      $elts = array("manage", "assistant","friend","guest","user");
      $form->addBloc("blkLegend", $elts);
      
      //Display the form
      $form->display(false);
      exit; 
    }
  // }}}



  // {{{ _displayFormList()
  /**
   * Display a page with the list of the address books
   *
   * @access private
   * @return void
   */
  function _displayFormList()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;

      utvars::setSessionVar('theme', WBS_THEME_BOOK);
      
      $utPage = new utPage_A('books', true, 'itBooks');
      $content =& $utPage->getContentDiv();

      $items = array( 'itNew'     => array(KAF_NEWWIN,    'books', KID_NEW, 
					0, 400, 150),
		     'itDelete'  => array(KAF_UPLOAD, 'books',
					KID_DELETE, "rowsBooks"),
		     'itRestore' => array(KAF_UPLOAD, 'books',
					KID_UNDELETE, "rowsBooks"));
      $kmenu =& $content->addMenu("menuAction", $items, -1);
      $content->addDiv('break', 'blkNewPage');
      
      // Fix the pager attributes
      $form =& $content->addForm('fbooks'); 
      $sort = $form->getSort("rowsBooks", 2);
      $rows = $dt->getBooks($sort);
      if (isset($rows['errMsg']))
	$form->addWng($rows['errMsg']);
      else
	{
	  if (count($rows))
	    {
	      $krow =& $form->addRows("rowsBooks", $rows);
	      
	      // Do not display column 4 and up
	      $sizes[4] = '0+';
	      $krow->setSize($sizes);
	      
	      $img[1]=7;
	      $krow->setLogo($img);

	      $actions[0]= array(KAF_NEWWIN, 'books', KID_EDIT, 0, 
				   400, 150);
	      $actions[1] = array(KAF_UPLOAD, 'books', KID_SELECT);
	      $krow->setActions($actions);
	    }
	}

      // Legende
      $kdiv = &$content->addDiv('lgd', 'blkLegende');
      $kdiv->addImg('lgdConfident', utimg::getIcon(WBS_DATA_CONFIDENT));
      $kdiv->addImg('lgdPrivate', utimg::getIcon(WBS_DATA_PRIVATE));
      $kdiv->addImg('lgdPublic', utimg::getIcon(WBS_DATA_PUBLIC));

      $utPage->display();
      exit; 
    }
  // }}}
  
  // {{{ _delBook()
  /**
   * Delete the selected address book in the database
   *
   * @access private
   * @param  integer $isDelete new status of the book
   * @return void
   */
  function _delBooks($isDelete)
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Get the id's of the books to delete
      $id = kform::getInput("rowsBooks", array());
      if (!count($id))
	{
	  $this->_displayFormList();
	  exit; 
	}
      echo "delBooks?";
      // Delete the books
      $res = $dt->delBooks($id, $isDelete);
      
      // Display the list of books
      $this->_displayFormList();
      exit; 
    }
  // }}}
  
  // {{{ _publiBooks()
  /**
   * Change the status of the address book in the database
   *
   * @access private
   * @param  integer $status new status of the book
   * @return void
   */
  function _publiBooks($status)
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Get the id's of the books to modify
      $id = kform::getInput("rowsBooks");
      if (!count($id))
	{
	  $this->_displayFormList();
	  exit; 
	}
      
      // Change the status of selected books
      $res = $dt->publiBooks($id, $status);
      $this->_displayFormList();
      exit; 
    }
  // }}}
  
  
  // {{{ _publiContacts()
  /**
   * Change the status of the contacts in the database
   *
   * @access private
   * @param  integer $status new status of the contact
   * @return void
   */
  function _publiContacts($status)
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Get the id's of the members contacts to modify
      /*
      $id = kform::getInput("rowsContacts");
      if ($id != "")
	{
	  $res = $dt->publiContacts($id, $status);
	}
      */
      // Get the id's of the assos contacts to modify
      $id = kform::getInput("rowsContactsA");
      if ( $id != "")
	{
	  print_r($id);
	  $res = $dt->publiContacts($id, $status);
	}
      
      // Refresh the list
      $this->_displayFormMainBook();
      exit; 
    }
  // }}}
  
  // {{{ _updateBook()
  /**
   * Update the address book in the database
   *
   * @access private
   * @return void
   */
  function _updateBook()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Retieve the informations
      $infos = array('adbk_name'      =>kform::getInput("adbkName"),
		     'adbk_cmt'       =>kform::getInput("adbkCmt"),
		     'adbk_ownerId'   =>kform::getInput("adbkOwnerId"),
		     'adbk_id'        =>kform::getInput("adbkId"));
      
      // Control the informations
      if ($infos['adbk_name'] == "")
	{
	  $infos['errMsg'] = 'msgadbkName';
	  $this->_displayFormBook($infos);
	} 
      // register the modification in the database
      $res = $dt->updateBook($infos);
      if (is_array($res))
	{
	  // An error occured. Display the error
	  $this->_displayFormBook($res);
	}
      else
	{
	  // All is OK. Close the window
	  $page = new utPage('none');
	  $page->close();
	  exit;
	}
      exit; 
    }
  // }}}
  
  
  // {{{ _displayFormBook()
  /**
   * Display a form to modify an address book
   *
   * @access private
   * @param mixed  $book  Id or data of the adress book to modify.
   * @return void
   */
  function _displayFormBook($book="")
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      $utpage = new utPage('books');
      $content =& $utpage->getPage();

      $form =& $content->addForm('fbooks', 'books', KID_UPDATE);

      // Initialize the field
      $infos = array('adbk_id'      => -1,
		     'adbk_name'    => "",
		     'adbk_ownerId' =>utvars::getUserId(),
		     'adbk_cmt'     => ""
		     );
      
      // Get the data of the book
      if (is_array($book))
	$infos = $book;
      else
	if ($book != "") $infos = $dt->getBook($book);
      
      if ($infos['adbk_id'] != -1)
	$form->setTitle('tEditBook');
      else
	$form->setTitle('tNewBook');
      
      // Display a warning if an error occured
      if (isset($infos['errMsg']))
	$form->addWng($infos['errMsg']);
      
      // Initialize the field
      $form->addHide('adbkId', $infos['adbk_id']);
      $form->addHide('adbkOwnerId', $infos['adbk_ownerId']);
      $kedit =& $form->addEdit('adbkName', $infos['adbk_name'],30);
      $kedit->setMaxLength(30);
      $kedit =& $form->addEdit('adbkCmt', $infos['adbk_cmt'] , 30 );
      $kedit->noMandatory();
      //	$form->addInfo("adbk_owner", $infos['adbk_owner'] , 1);
      $elts = array('adbkName', 'adbkCmt', 'adbkOwner');
      $form->addBlock('blkBook', $elts);
      
      $form->addBtn('btnRegister', KAF_SUBMIT);
      $form->addBtn('btnCancel');
      $elts = array('btnRegister', 'btnCancel');
      $form->addBlock("blkBtn", $elts);
      
      //Display the form
      $utpage->display(false);
      exit; 
    }
  // }}}
  
  
  // {{{ _displayFormMainBook()
  /**
   * Display the main page for the adress book
   *
   * @access private
   * @param array $bookId  id of the address book
   * @return void
   */
  function _displayFormMainBook($bookId="")
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Create a new form
      if ($bookId=="") $bookId = utVars::getBookId();
      else utvars::setBookId($bookId);

      $utPage = new utPage_A('books', true, 'itBooks');
      $content =& $utPage->getContentDiv();

      // Get the data of the book  
      $book = $dt->getBook($bookId);
      if (isset($book['errMsg']))
	$form->addWng($book['errMsg']);
      
      $kdiv =& $content->addDiv('divMain', 'blkInfo'); 
      $kdiv->addInfo("bookName", $book['adbk_name']);
      $kdiv->addBtn('btnModify', KAF_NEWWIN, 'books', KID_EDIT,
			 $bookId, 350, 200);

      $content->addDiv('break', 'blkNewPage');
      $form =& $content->addForm('fbooks'); 

      // Add the menu to modify data of the book
      $items = array(
		     //'edit'   => array(KAF_NEWWIN, 'books', KID_EDIT, 
		     //	       $bookId, 400, 200),
		     //'AddMemberContact' => array(KAF_NEWWIN, 'books', 
		     //				 BOOKS_NEWMEMBER_A, 
		     //			 $bookId, 750, 550),
		     'itNewAssocContact'  => array(KAF_NEWWIN, 'books', 
						 BOOKS_NEWASSOC_A, 
						 $bookId, 750, 550),
		     'itDelete'           => array(KAF_UPLOAD, 'books', 
						 BOOKS_REMCONTACT_A),
		     //'Right'   => array(KAF_NEWWIN,    'books', BOOKS_RIGHT_A,
		     //	       $bookId, 450, 400)
		     'itImport'  => array(KAF_NEWWIN, 'books', BOOKS_SELECT_FILE_A,
					  0, 400, 200)
		     );
      $itsp['itPublic'] = array(KAF_NEWWIN, 'books', BOOKS_PUBLISHEDCTAC_A,
				0, 250, 150);
      $itsp['itPrivate']   = array(KAF_NEWWIN, 'books', BOOKS_HIDDENCTAC_A,
				   0, 250, 150);
      $itsp['itConfident'] = array(KAF_NEWWIN, 'draws', BOOKS_PRIVATECTAC_A,
				   0, 250, 150);
      $form->addMenu('menuLeft', $items, -1);
      //$form->addMenu('menuRight', $itsp, -1);
      $form->addDiv('break2', 'blkNewPage');

  
      // Fix the pager attributes
      /*
      $sort = $form->getSort("rowsContacts");
      $rows = $dt->getBookMembersContacts($sort, $bookId);     
      if (isset($rows['errMsg']))
	{
	  $form->addWng($rows['errMsg']);
	}
      else
	{
	  $userPerm = array();
	  
	  $krow =& $form->addRows("rowsContacts", $rows);
	  $form->setPerms("rowsContacts", $userPerm);
	  
	  $img[1]=9;
	  $krow->setLogo($img);

	  $sizes = array(6=> 0, 0, 0, 0);
	  $form->setLength("rowsContacts", $sizes);
	  
	  $actions = array( 2 => array(KAF_NEWWIN, 'mber', 
				       KID_EDIT, 8, 450, 300),
			    3 => array(KAF_NEWWIN, 'books', 
				       BOOKS_EDITCONTACT_A, 7, 400, 180)
			    );
	  $form->setActions("rowsContacts", $actions);
	  $form->addBlock("blkBooks", "rowsContacts");
	}
      */
      // Select associaiton contacts
      $sort = $form->getSort("rowsContactsA",1);
      $rows = $dt->getBookAssosContacts($sort, $bookId);      
      if (isset($rows['errMsg']))
	{
	  $form->addWng($rows['errMsg']);
	}
      else
	{
	  $userPerm = array();
	  
	  $krow =& $form->addRows("rowsContactsA", $rows);
	  $form->setPerms("rowsContactsA", $userPerm);

	  $img[1]=9;
	  //$krow->setLogo($img);
	  
	  $sizes[5] = '0+';
	  $krow->setSize($sizes);
	  
	  $actions[0]= array(KAF_NEWWIN, 'books', 
			     BOOKS_EDITCONTACT_A, 0, 400, 200);
	  /*	  $actions[1] = array(KAF_NEWWIN, 'asso', 
			       KID_EDIT, 'asso_id', 450, 200);*/
	  $krow->setActions($actions);
	  //$form->addBlock("blkBooksA", "rowsContactsA");	  
	}


      // Legende
      $kdiv = &$content->addDiv('lgd', 'blkLegende');
      $kdiv->addImg('lgdConfident', utimg::getIcon(WBS_DATA_CONFIDENT));
      $kdiv->addImg('lgdPrivate', utimg::getIcon(WBS_DATA_PRIVATE));
      $kdiv->addImg('lgdPublic', utimg::getIcon(WBS_DATA_PUBLIC));

      //Display the form
      $utPage->display();
      
      exit;
    }
  // }}}
  
  
  // {{{ _displayFormNewMember()
  /**
   * Display a form to add member contact to the book
   *
   * @access private
   * @return void
   */
  function _displayFormNewMember()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      $form = $ut->newForm_A("books", BOOKS_NEWMEMBER_A);

      $form->setTitle('tAddContact');
      
      // Fix the pager attributes
      $sort = $form->getSort("rowsMberContacts");

      // Retrieve the list of contact of members 
      $rows = $dt->getContactsMembers($sort, $first, $step);
      if (isset($rows['errMsg']))
	$form->addWng($rows['errMsg']);
      else
	{
	  $userPerm = array();
	  if (count($rows))
	    {
	      $contacts = $dt->getMemberBookContacts();
	      foreach( $rows as $row=>$values)
		{
		  if (in_array($values[0], $contacts))
		    $userPerm[$values[0]] = KOD_WRITE;
		  else
		    $userPerm[$values[0]] = KOD_SELECT | KOD_WRITE;
		}
	    }
	  $form->addRows("rowsMberContacts", $rows);
	  
	  $form->setPerms("rowsMberContacts", $userPerm);
	  $sizes = array(8=> '0','0');
	  $form->setLength("rowsMberContacts", $sizes);
	  
	  $actions = array( 2 => array(KAF_NEWWIN, 'mber', 
				       KID_EDIT, 8, 450, 300),
			    3 => array(KAF_NEWWIN, 'books', 
				       BOOKS_NEWMBERCTAC_A, 8, 400, 200),
			    4 => array(KAF_NEWWIN, 'books', 
					 BOOKS_EDITCONTACT_A, 0, 400, 200)
			    );
	  $form->setActions("rowsMberContacts", $actions);
	  $form->addBloc("blkBooks", "rowsMberContacts");
	}
      
      $form->addBtn("btnAddMember");
      $actions = array( 1 => array(KAF_NEWWIN, 'mber', KID_NEW, 0, 
				   400, 280));
      $form->setActions("btnAddMember", $actions);
      $form->addBtn("btnDelContact", KAF_CHECKROWS, BOOKS_DELMBERCONTACT_A);
      $form->addBtn("btnRegister", KAF_CHECKROWS, BOOKS_REGMBERCONTACT_A);
      $form->addBtn("btnCancel");
      $elts = array("btnAddMember", "btnDelContact", "btnRegister", 
		    "btnCancel");
      $form->addBloc("blkBtn", $elts);
      
      
      //Display the form
      $form->display(false);
      exit; 
    }
  // }}}
  
  // {{{ _displayFormNewAssoc()
  /**
   * Display a form to modify an address book
   *
   * @access private
   * @return void
   */
  function _selectAsso()
    {
      $dt = $this->_dt;

      $utpage = new utPage('books');
      $content =& $utpage->getPage();
      $form =& $content->addForm('fbooks', 'books', BOOKS_VALIDASSOCONTACT_A);
      $form->setTitle("tAddContact");

      $assos = $dt->getContactsAssocs(2);   
      if (isset($assos['errMsg']))
	$form->addWng($assos['errMsg']);
      else
	{
	  foreach($assos as $asso)
	    {
	      $rows[$asso['asso_id']] = $asso['asso_name'];
	    }
	  $kombo = & $form->addCombo('assos', $rows, 0);
	  $kombo->setLength(15);
	}

      $form->addBtn('btnRegister', KAF_SUBMIT);
      $form->addBtn('btnCancel');
      $elts = array('btnAddAssoc', 'btnRegister', 'btnCancel');
      $form->addBlock("blkBtn", $elts);

      //Display the form
      $utpage->display(false);
      exit; 
    }
  // }}}


  // {{{ _displayFormNewAssoc()
  /**
   * Display a form to modify an address book
   *
   * @access private
   * @return void
   */
  function _displayFormNewAssoc()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;

      $utpage = new utPage('books');
      $content =& $utpage->getPage();
      $form =& $content->addForm('fbooks', 'books', BOOKS_REGASSOCONTACT_A);
      $form->setTitle("tAddContact");

      $itsm['itNew'] = array(KAF_NEWWIN, 'books', BOOKS_SELASSOCONTACT_A, 0,
			     450,400);
      $itsm['itDelete'] = array(KAF_UPLOAD, 'books', BOOKS_DELASSOCONTACT_A);
      $form->addMenu('menuLeft', $itsm, -1);
      $form->addDiv('break', 'blkNewPage');

      // Fix the pager attributes
      $sort = $form->getSort("rowsAssoContacts", 2);
      
      // Retrieve the list of contact of association
      $rows = $dt->getContactsAssocs($sort);   
      if (isset($rows['errMsg']))
	$form->addWng($rows['errMsg']);
      else
	{
	  $krows =& $form->addRows("rowsAssoContacts", $rows);
	  $sizes = array(5=> '0+');
	  $krows->setSize($sizes);
	  
	  $actions[0] = array(KAF_NEWWIN, 'books', 
			      BOOKS_NEWASSOCCTAC_A, 0, 400, 250);
	  $krows->setActions($actions);
	}
      
      $form->addBtn('btnAddAssoc', KAF_NEWWIN, 'asso', KID_NEW, 0, 
      		    400, 200);
      $form->addBtn('btnRegister', KAF_SUBMIT);
      $form->addBtn('btnCancel');
      $elts = array('btnAddAssoc', 'btnRegister', 'btnCancel');
      $form->addBlock("blkBtn", $elts);
      
      //Display the form
      $utpage->display(false);
      exit; 
    }
  // }}}
  
  // {{{ _displayFormContact()
  /**
   * Display a form to add or edit a contact
   *
   * @access private
   * @param mixed  $infos  data of the new contact.
   * @return void
   */
  function _displayFormContact($contact)
    {
      $ut = $this->_ut;
      $dt = $this->_dt;

      $utpage = new utPage('books');
      $content =& $utpage->getPage();
      $form =& $content->addForm('fbooks', 'books', BOOKS_UPDTCONTACT_A);

      // Initialize the field
      if (!isset($contact['ctac_id']))
	{
	  $infos = $contact;
	  $infos['ctac_id'] = -1;
	  $infos['ctac_value'] = "";
	  $infos['ctac_contact'] = "";
	  $infos['ctac_type'] = WBS_EMAIL;
	  $infos['ctac_cmt']  = "";
	}
      else
	{
	  if ($contact['ctac_id']!= -1)
	    $infos = $dt->getContact($contact['ctac_id']);
	  else 
	    $infos = $contact;
	}
      if ($infos['ctac_id'] != -1)
	$form->setTitle('tEditContact');
      else
	$form->setTitle('tNewContact');
      
      $form->addHide("ctacId", $infos['ctac_id']);
      $form->addHide("ctacMemberId",   $infos['ctac_memberId']);
      $form->addHide("ctacAssocId",    $infos['ctac_assocId']);
      
      // Display a warning if an error occured
      if (isset($infos['errMsg']))
	$form->addWng($infos['errMsg']);
      
      $kedit =& $form->addEdit("ctacContact", $infos['ctac_contact'] , 30 );
      $kedit->setMaxLength(50);

      $kedit =& $form->addEdit("ctacValue", $infos['ctac_value'] , 30 );
      $kedit->setMaxLength(50);
      
      $types = array( WBS_EMAIL    => $ut->getLabel(WBS_EMAIL),
		      WBS_PHONE    => $ut->getLabel(WBS_PHONE),
		      WBS_MOBILE   => $ut->getLabel(WBS_MOBILE),
		      WBS_FAX      => $ut->getLabel(WBS_FAX));
      $typeSel = $infos['ctac_type'];
      $form->addCombo("ctacType", $types, $types[$typeSel]);
      
      
      $kedit =& $form->addEdit("ctacCmt", $infos['ctac_cmt'] , 30 );
      $kedit->noMandatory();
      
      $elts = array('ctacContact', 'ctacValue', 'ctacType', 'ctacCmt');
      $form->addBlock('blkContact', $elts);
      
      $form->addBtn('btnRegister', KAF_SUBMIT);
      $form->addBtn('btnCancel');
      $elts = array('btnRegister', 'btnCancel');
      $form->addBlock('blkBtn', $elts);
      
      //Display the form
      $utpage->display(false);
      exit; 
    }
  // }}}
  
  // {{{ _updtContact()
  /**
   * Update the new contact book in the database
   *
   * @access private
   * @return void
   */
  function _updtContact()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Retrieve the informations
      $infos = array('ctac_memberId' =>kform::getInput("ctacMemberId"),
		     'ctac_assocId'  =>kform::getInput("ctacAssocId"),
		     'ctac_id'       =>kform::getInput("ctacId"),
		     'ctac_cmt'      =>kform::getInput("ctacCmt"),
		     'ctac_value'    =>kform::getInput("ctacValue"),
		     'ctac_contact'  =>kform::getInput("ctacContact"),
		     'ctac_type'     =>kform::getInput("ctacType"));
	
      // Control the informations
      if ($infos['ctac_memberId'] == "")
	{
	  $infos['errMsg'] = 'msgctacMemberId';
	  $this->_displayFormContact($infos);
	} 
      if ($infos['ctac_assocId'] == "")
	{
	  $infos['errMsg'] = 'msgctacAssocId';
	  $this->_displayFormContact($infos);
	} 
      
      if ($infos['ctac_value'] == "")
	{
	  $infos['errMsg'] = 'msgValue';
	  $this->_displayFormContact($infos);
	} 
      
      if ($infos['ctac_type'] == "")
	{
	  $infos['errMsg'] = 'msgctacType';
	  $this->_displayFormContact($infos);
	} 
      
      // register the modification in the database
      $res = $dt->updtContact($infos);
      if (is_array($res))
	{
	  // An error occured. Display the error
	  $this->_displayFormContact($res);
	}
      else
	{
	  // All is OK. Close the window
	  $page = new utPage('none');
	  $page->close();
	}
      exit; 
    }
  // }}}
  
  
  // {{{ _delMberContacts()
  /**
   * Delete the selected contact in the database
   *
   * @access private
   * @return void
   */
  function _delMberContacts()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Get the id's of the events to delete
      $contactsId = kform::getInput("rowsMberContacts");
      if (is_array($contactsId))
	$res = $dt->delContacts($contactsId);
      $this->_displayFormNewMember();
      exit; 
    }
  // }}}
  
  // {{{ _delAssoContacts()
  /**
   * Delete the selected contact in the database
   *
   * @access private
   * @return void
   */
  function _delAssoContacts()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Get the id's of the events to delete
      $contactsId = kform::getInput("rowsAssoContacts");
      if (is_array($contactsId))
	$res = $dt->delContacts($contactsId);
      $this->_displayFormNewAssoc();
      exit; 
    }
  // }}}
  
  // {{{ _regMberContacts()
  /**
   * Add the selected contact to the current book
   *
   * @access private
   * @return void
   */
  function _regMberContacts()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Get the id's of the events to delete
      $contactsId = kform::getInput("rowsMberContacts");
      if (is_array($contactsId))
	$res = $dt->regContacts($contactsId);
      
      if (is_array($res))
	{
	  // An error occured. Display the error
	  $infos['errMsg'] = $res['errMsg'];
	  $this->_displayFormContact($infos);
	}
      else
	{
	  // All is OK. Close the window
	  $page = new utPage('none');
	  $page->close();
	}
      exit; 
    }
  // }}}
  
  // {{{ _regAssoContacts()
  /**
   * Add the selected contact to the current book
   *
   * @access private
   * @return void
   */
  function _regAssoContacts()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Get the id's of the contact to add
      $contactsId = kform::getInput("rowsAssoContacts");
      if (is_array($contactsId))
	{
	  $res = $dt->regContacts($contactsId);
	}
      if (is_array($res))
	{
	  // An error occured. Display the error
	  $infos['errMsg'] = $res['errMsg'];
	  $this->_displayFormNewAssoc();
	}
      else
	{
	  // All is OK. Close the window
	  $page = new utPage('none');
	  $page->close();
	}
      exit; 
    }
  // }}}
  
  
  // {{{ _remContacts()
  /**
   * Remove the selected contact from the current book
   *
   * @access private
   * @return void
   */
  function _remContacts()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Get the id's of the members contacts to delete form the book
      $res['errMsg'] = "No row selected !!!";
      $contactsId = kform::getInput("rowsContacts");
      if (is_array($contactsId))
	$res = $dt->remContacts($contactsId);
      if (is_array($res))
	{
	  // An error occured. Display the error
	  $infos['errMsg'] = $res['errMsg'];
	}
      
      // Get the id's of the assoc contacts to delete form the book
      $contactsId = kform::getInput("rowsContactsA");
      if (is_array($contactsId))
	$res = $dt->remContacts($contactsId);
      
      if (is_array($res))
	{
	  // An error occured. Display the error
	  $infos['errMsg'] = $res['errMsg'];
	}
      $this->_displayFormMainBook("");
    }
  // }}}
  
}
?>