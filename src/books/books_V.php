<?php
/*****************************************************************************
!   Module     : Books
!   File       : $Source: /cvsroot/aotb/badnet/src/books/books_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.1 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2005/09/07 20:50:56 $
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

require_once "base_V.php";
require_once "books.php";
require_once "books.inc";

/**
* Module de gestion des carnets d'adresses : classe visiteurs
*
* @author Gerard CANTEGRIL
* @see to follow
*
*/

class Books_V extends Books
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
  function Books_V()
    {
      $this->_ut = new Utils();
      $this->_dt = new BooksBase_V();
    }
  // }}}
  
  // {{{ start()
  /**
   * Start the connexion processus
   *
   * @access public
   * @param  integer $action  what to do
   * @return void
   */
  function start($page)
    {
      switch ($page)
        {
	case BOOKS_MAIL_V : 
	  $this->_displayFormMail();
	  break;
	case BOOKS_SENDMAIL_V :
	  $this->_sendMail();
	  break;
	case KID_SELECT:
	  $this->_displayFormBook(kform::getDataId());
	  break;
	default:
	  $this->_ut->log("boks_V->start: page interdite. page=$page");
	  echo "books_V->start: page interdite.";
	  exit;
	}
    }
  // }}}
  

  // {{{ _sendMail()
  /**
   * Create the form to display
   *
   * @access private
   * @return void
   */
  function _sendMail()
    {
      // Get users name
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      @require_once 'Mail.php';
      
      $param= array("host" => $ut->getParam("smtp_server"),
		    "port" => $ut->getParam("smtp_port", 25),
		    "auth" => true,
		    "username" => $ut->getParam("smtp_user"),
		    "password" => $ut->getParam("smtp_password")
		    );
      // Retrieve users informations
      $userId = $ut->getUserId();
      $infosUser  = $dt->getUser($userId);
      
      $destId  = kform::getInput("did");
      $infosDest  = $dt->getMail($destId);

      if (kform::getInput('from') == "")
	{
	  $this->_displayFormMail("msgfrom");
	}

      // Prepare mailer
      $mailer = Mail::factory("smtp", $param);
      
      // Prepare message
      $recipient = $infosDest['ctac_value'];

      $header = array("Subject"      => kform::getInput("subject"),
 	              "From"         => kform::getInput("from"),
		      "X-Mailer"     => "Badnet",
		      "X-Priority"   => 1,
		      "Return-Path"  =>  "<".$infosUser["user_email"].">",
		      "Content-Type" => "text/html; charset=iso-8859-1\n",
		      );
      if ($infosUser['user_login'] != "demo")
	{
	  $header["From"] .= "<";
	  $header["From"] .= $infosUser['user_email'];
	  $header["From"] .= ">";
	}

      $body = kform::getInput("message");

      // Send the message
      $res =  $mailer->send($recipient, $header, $body);
      if (!PEAR::isError($res))
	{
	  $form = $ut->newForm_V("main", KID_NONE);
	  $form->close();
	  exit;
	}
      else
	{
	  $this->_displayFormMail($res->getMessage());
	}
    }
  // }}}
      

  // {{{ _displayFormMail()
  /**
   * Create the form to display
   *
   * @access private
   * @return void
   */
  function _displayFormMail($err="")
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      $form = $ut->newForm_V("books", BOOKS_MAIL_V);
      // Set the title
      $form->setTitle("");
      $form->setSubTitle("");
      $form->addMsg("tSendMail");

      // Get users name
      $userId = $ut->getUserId();
      $infosUser  = $dt->getUser($userId);


      $destId  = kform::getInput("did");
      if ($destId == "")
	$destId  = kform::getDataId();
 
      $infosDest  = $dt->getMail($destId);

      $subject  = kform::getInput("subject");
      $body     = kform::getInput("message");


      if ($err!="")
	$form->addWng($err);
	
      // Display  user's informations but not for 'demo' user
      $form->addhide("did",     $destId);
      if ($infosUser['user_login'] != "demo")
	$form->addInfo("from",    $infosUser['user_name']);
      else
	$form->addEdit("from",    kform::getInput("from"));
      $form->addinfo("to",      $infosDest['nom']);
      $form->addEdit("subject", $subject, 45);
      $form->addArea("message", $body, 31, 8);
      $elts=array("from", "to", "subject", "message");
      $form->addBloc("blkMail", $elts); 

	  
      // add bouton to send the mail
      $form->addBtn("btnMail", KAF_CHECKFIELD, BOOKS_SENDMAIL_V);
      $form->addBtn("btnCancel");
      $elts = array("btnMail", "btnCancel");
      $form->addBloc("blkBtn", $elts);
      $form->display(false);
      exit; 
    }
  // }}}

  
  // {{{ _displayFormBook()
  /**
   * Display the main page for the address book
   *
   * @access private
   * @param array $bookId  id of the address book
   * @return void
   */
  function _displayFormBook($bookId="")
    {
      $ut = $this->_ut;
      $data = $this->_dt;
      
      if ($bookId=="") $bookId=$ut->getThemeId();
      $form = $ut->newForm_V("books", KID_SELECT, false);

      $items = array('Reload'  => array(KAF_UPDATE,'books'),
		     'Separator'   => '');
      $items['Home'] = array(KAF_UPLOAD, 'main', KID_HOME);
      $items['Exit'] = array(KAF_UPLOAD, 'main', KID_LOGOUT);
      $form->addMenu("menuBooks", $items);


      // Initialize the field
      $form->setTitle("tBook");
      
      // Get the data of the book  
      $infos = $data->getBook($bookId);
      if (isset($infos['errMsg']))
	$form->addWng($infos['errMsg']);
      
     $form->setSubTitle($infos['adbk_name']);
     
     $sort = $form->getSort("rowsContacts");
     
     // Get the contacts of the book
     $rows = $data->getBookContacts($sort, $bookId);
     
     if (isset($rows['errMsg']))
       {
	 $form->addWng($rows['errMsg']);
       }
     else
       {
	 $nb = count($rows);
	 if ($nb)
	   {
	     $perms = array();
	     foreach( $rows as $row=>$values)
	       { 
		 if ($values[5] != WBS_EMAIL)
		   $perms[$values[0]] = KOD_NONE;
		 else
		   $perms[$values[0]] = KOD_READ;
		 
	       } 
	     $form->addRows("rowsContacts", $rows);

	     $sizes = array(5=> '0', '0');
	     $form->setLength("rowsContacts", $sizes);
	     
	     $form->setPerms("rowsContacts", $perms);
	     $actions = array( 3 => array(KAF_NEWWIN, 'books', 
					  BOOKS_MAIL_V, 0, 
					  400, 300)
			       );
	     $form->setActions("rowsContacts", $actions);
	     
	     $form->addBloc("blkBooks", "rowsContacts");
	   }
       }
     
     //Display the form
     $form->display(true);
     
     exit;
    }
  // }}}

}

?>