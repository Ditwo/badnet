<?php
/*****************************************************************************
!   Module     : Utilitaires
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utmail.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.6 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2006/10/27 11:04:56 $
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

require_once 'Mail.php';
require_once 'mime.php';

/**
* Module de gestion des mails :
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class utmail
{

  // {{{ properties
  
  /**
   * Proritiy
   *
   * @var     array
   * @access  private
   */
  var $_priorities= array( '1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)' );

  /**
   * Mail mime
   *
   * @var     array
   * @access  private
   */
  var $_mailMime = NULL;

  /**
   * Receipt
   *
   * @var     array
   * @access  private
   */
  var $_receipt = FALSE;

  /**
   * xtra_headers
   *
   * @var     array
   * @access  private
   */
  var $_xtra_headers = array();


  // }}}


  // {{{ Mail()
  /**
   * Constructor
   *
   * @access public
   * @param  integer $action  what to do
   * @return void
   */
  function utmail()
    {
      $this->_boundary= "--" . md5( uniqid("myboundary") );

      $this->_ut = new utils();
      $type = $this->_ut->getParam("email_type", 'smtp');
      if ($type=='mail')
	$this->_mailMime = new Mail_mime("\n");
      else
	$this->_mailMime = new Mail_mime();

      $this->_xtra_headers['Organization'] = 'BadNet';
      $this->_xtra_headers['X-Priority'] = $this->_priorities[2];
      $this->_xtra_headers['X-Mailer'] = "Php/Badnet";
   }
  
  // }}}
  

  // {{{ subject()
  /**
   * Define the subject line of the email
   *
   * @access public
   * @param  string $subject  any valid mono-line string
   * @return void
   */
  function Subject( $subject )
    {
      $this->_mailMime->setSubject($subject);
    }
  // }}}
  

  // {{{ from()
  /**
   * Set the sender of the mail
   *
   * @access public
   * @param  string $from should be an email address
   * @return void
   */
  function From($email)
    {
      $this->_mailMime->setFrom($email);
      if (!isset($this->_xtra_headers['Reply-To']))
	$this->_xtra_headers["Reply-To"] = $email;
    }
  // }}}

  // {{{ Cc()
  /**
   * Set the CC headers ( carbon copy )
   *
   * @access public
   * @param  string $cc email address(es), accept both array and string
   * @return void
   */
  function Cc( $emails )
    {
      if ( ! is_array($emails) )
	{
	  if (strpos($emails, ';')) 
	    $glue =";";
	  else
	    $glue =",";
	  $tos = explode($glue, $emails);
	}
      else
	$tos = $emails;
      foreach($tos as $to) 
	$this->_mailMime->addCc($to);
    }
  // }}}
  

  // {{{ Bcc()
  /**
   * set the Bcc headers ( blank carbon copy ).
   * 
   * @access public
   * @param  string $bcc email address(es), accept both array and string
   * @return void
   */
  function Bcc( $emails )
    {
      if (is_array($emails))
	$tos = $emails;
      else
	{
	  if (strpos($emails, ';')) 
	    $glue =";";
	  else
	    $glue =",";
	  $tos = explode($glue, $emails);
	}
      foreach($tos as $to) 
	$this->_mailMime->addBcc($to);
    }
  // }}}



  // {{{ ReplyTo()
  /**
   * Set the Reply-to header 
   *
   * @access public
   * @param  string $email should be an email address
   * @return void
   */
  function ReplyTo( $email)
    {
      $this->_xtra_headers['Reply-To']= $address;
    }
  // }}}


  // {{{ Body()
  /**
   * set the Body of the mail (message)
   * Define the charset if the message contains extended characters
   * (accents) default to us-ascii
   *  $mail->Body( "m�l en fran�ais avec des accents", "iso-8859-1" );
   * 
   * @access public
   * @param  string $body  message
   * @return void
   */
  function Body($body)
    {
      $this->_mailMime->setTXTBody($body);
    }
  // }}}



  // {{{ addZip
  /**
   * Attach a zip file to the mail
   * 
   * @access public
   * @param  string $filename : path of the file to attach
   * @param  string $filetype : MIME-type of the file. 
   *                            Default to 'application/x-unknown-content-type'
   * @param  string $disposition : Instruct the Mailclient to display the file if possible 
   *                               ("inline") or always as a link ("attachment")
   *                                 possible values are "inline", "attachment"
   * @return void
   */
  function addZip($filename)
    {
      $this->_mailMime->addAttachment($filename, 
				      "application/zip");
    }
  // }}}

  // {{{ addPdf
  /**
   * Attach a pdf file to the mail
   * 
   * @access public
   * @param  string $filename : path of the file to attach
   * @param  string $filetype : MIME-type of the file. 
   *                            Default to 'application/x-unknown-content-type'
   * @param  string $disposition : Instruct the Mailclient to display the file if possible 
   *                               ("inline") or always as a link ("attachment")
   *                                 possible values are "inline", "attachment"
   * @return void
   */
  function addPdf($filename)
    {
      $this->_mailMime->addAttachment($filename, 
				      "application/pdf");
    }
  // }}}

  // {{{ Organization()
  /**
   * set the Organisation header
   * 
   * @access public
   * @param  string $org  organisation
   * @return void
   */
  function Organization( $org )
    {
      if( trim( $org != "" )  )
	$this->_xtra_headers['Organization'] = $org;
    } 
  // }}}

  // {{{ Priority
  /**
   * set the mail priority
   * 
   * @access public
   * @param  integer $priority  integer taken between 1 (highest) and 5 ( lowest )
   * @return void
   */
  function Priority( $priority )
    {
      
      if( intval( $priority ) && 
	  isset( $this->_priorities[$priority-1]) )
	$this->_xtra_headers["X-Priority"] = $this->_priorities[$priority-1];
    }
  // }}}
  
  // {{{ Receipt()
  /**
   * Add a receipt to the mail ie.  A confirmation is returned to 
   *  the "From" address (or "ReplyTo" if defined) 
   *  when the receiver opens the message.Set the Reply-to header 
   *
   * @access public
   * @return void
   */
  function Receipt( )
    {
      $this->_receipt = True;
    }
  // }}}
  

  // {{{ Send()
  /**
   * fornat and send the mail
   * 
   * @access public
   * @return void
   */
  function Send($emails)
    {
      $ut = new utils();
      //$this->Bcc('cage@free.fr');
      
      $version = '';
      if(preg_match("/V[\d]+_[\d]+r[\d]+.*/", $ut->getParam('version'), 
		    $curVersion))
	$version = ereg_replace('_', '.', $curVersion[0]);

      $foot = "\n\n-----------------\n";
      $foot .= "Email envoy� par BadNet $version.\n";
      $foot .= "Pour plus d'information, consultez le site\n";
      $foot .= "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
      $this->_mailMime->setTXTBody($foot, false, true);

      // envoi du mail aux destinataires principaux
      $type = $ut->getParam("email_type", 'smtp');
      if ($type=='smtp')
	{
	  $param= array("host" => $ut->getParam("smtp_server"),
			"port" => $ut->getParam("smtp_port", 25),
			"auth" => $ut->getParam("smtp_auth")==1?true:false,
			"username" => $ut->getParam("smtp_user"),
			"password" => $ut->getParam("smtp_password")
			);
	  if ($param['host']=='' ||
	      $param['username']=='') 
            return PEAR::raiseError('Host ou user non renseign�');
	  $mailer = Mail::factory($type, $param);
	}
      else
	$mailer = Mail::factory($type);

      //$img = utimg::getLogo('badnet.jpg');
      //$res = $this->_mailMime->addHTMLImage($img, 'image/jpg');
      //if (PEAR::isError($res))
      //	echo $res->getMessage();

      // Corps du message
      $body = $this->_mailMime->get();

      // En tete supplementaires
      if( $this->_receipt ) 
	$this->_xtra_headers["Disposition-Notification-To"] = $this->_xtra_headers["Reply-To"];
      $headers = $this->_mailMime->headers($this->_xtra_headers);

      // Destinataire
      if (strpos($emails, ';')) 
	$glue =";";
      else
	$glue =",";
      $tos = explode($glue, $emails);
      $this->_to['To'] = implode(',', $tos);
      $res =  $mailer->send($this->_to, $headers, $body);
      unset($body);
      unset($headers);
      return $res;
    }
  // }}}


  // {{{ trace
  /**
   * Return the whole e-mail , headers + message
   *  can be used for displaying the message in plain text or logging it
   * 
   * @access public
   * @return void
   */
  function trace()
    {

      // Corps du message
      $headers = $this->_mailMime->headers($this->_xtra_headers);
      print_r($headers);
      
      $body = $this->_mailMime->get();
      print_r($body);

    }
  
  // }}}
  
}
?>