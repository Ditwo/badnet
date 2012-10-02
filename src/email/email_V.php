<?php
/*****************************************************************************
!   Module     : email
!   File       : $Source: /cvsroot/aotb/badnet/src/email/email_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2006/03/02 17:55:16 $
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
require_once "email.inc";
require_once "base_V.php";

/**
* Module de login d'un utilisateur
*
* @author Gerard CANTEGRIL <cage@aotb.org>
* @see to follow
*
*/

class Email_V
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
  function Email_V()
    {
    }
  // }}}
  
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
	  // Modification of an existing user
	case WBS_ACT_EMAIL :
	  $this->_displayFormMail();
	  break;
	  
	  // Update the database for a user
	case EMAIL_SEND :
	  $this->_sendMail();
	  break;
	  
	default:
	  echo "mail_V->start: page interdite. page=$page";
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
      $dt = new emailBase_V();
      
      require_once dirname(__FILE__).'/../utils/utmail.php';

      // Prepare mailer
      $mailer = new utmail();
      
      // Retrieve users informations
      $userId = utvars::getUserId();
      if ($userId != '')
	$infosUser  = $dt->getUser($userId);

      $fromEmail  = kform::getInput("fromEmail");
      if ($fromEmail == '' && isset($infosUser["user_name"]))
	$fromEmail = $infosUser["user_email"];

      $admiId  = kform::getInput("did");
      $infosAdmi  = $dt->getUser($admiId);
      
      // Prepare message
      $mailer->subject('[BadNet]'.stripslashes(kform::getInput("subject")));
      $mailer->organization("Badnet");
      if (isset($infosUser["user_name"]))
	$from = $infosUser["user_name"];
      else
	$from = kform::getInput("from");
      $from .="<$fromEmail>";
      $mailer->from ("$from");
      $mailer->cc($fromEmail);
      $mailer->body(stripslashes(kform::getInput("message")));

      // Send the message
      $res =  $mailer->send($infosAdmi["user_email"]);
      if (!PEAR::isError($res))
	  $this->_displayFormMail('msgSend');
      else
	  $this->_displayFormMail($res->getMessage());
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
      $dt = new emailBase_V();
      
      $utpage = new utPage_V('email', true, -1);
      $content =& $utpage->getContentDiv();

      $form =& $content->addForm('femail');

      // Retrieve sender's informations
      $senderId = utvars::getUserId();
      if ($senderId != '')
	$infosSender  = $dt->getUser($senderId);
      else
	{
	  $infosSender['user_name'] = kform::getInput("from");
	  $infosSender["user_email"]= kform::getInput("fromEmail");
	}

      // Retrieve to informations
      $destId = kform::getInput("did");
      if ($destId == "")
	$destId  = kform::getData();
      $infosDest  = $dt->getUser($destId);
      $subject = kform::getInput("subject");
      $body    = kform::getInput("message");
      $fromEmail  = kform::getInput("fromEmail");

      // Display error message
      if ($err!="") $form->addWng($err);

      // Display fields for email
      $form->addhide("did",  $destId);
      if ($senderId != "")
	$form->addinfo("from", $infosSender['user_name']);
      else
	$form->addEdit("from", $infosSender['user_name'], 45);

      if ($infosSender["user_email"] == "" ||
	  !strstr($infosSender["user_email"], '@'))
	$form->addEdit("fromEmail", $fromEmail, 45);

      $form->addInfo("to",      $infosDest['user_name']);
      $form->addEdit("subject", $subject, 45);
      $form->addArea("message", $body, 31, 8);
      $elts=array("from", "fromEmail", "to", "subject", "message");
      $form->addBlock("blkMail", $elts); 

	  
      // add bouton to send the mail
      $form->addBtn("btnMail", KAF_UPLOAD, 'email', EMAIL_SEND);
      //      $form->addBtn("btnCancel");
      $elts = array("btnMail");
      $form->addBlock("blkBtn", $elts);
 
      $utpage->display();
      exit; 
    }
  // }}}
  
}

?>