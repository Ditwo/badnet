<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

require_once 'Mail.php';
require_once 'mime.php';

class BN_Mail extends BN_error
{

	private $_priorities= array( '1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)' );
	private $_mailMime = NULL;
	private $_receipt = FALSE;
	private $_xtra_headers = array();

	/**
	 * Constructor
	 * @return BN_mail
	 */
	public function __construct()
	{
		$this->_boundary= "--" . md5( uniqid("myboundary") );
		$type = 'mail'; //$this->_ut->getParam("email_type", 'smtp');
		if ($type=='mail')
		{
			$this->_mailMime = new Mail_mime("\n");
		}
		else
		{
			$this->_mailMime = new Mail_mime();
		}
		$this->_xtra_headers['Organization'] = Bn::getConfigValue('prefix', 'email');
		$this->_xtra_headers['X-Priority'] = $this->_priorities[2];
		$this->_xtra_headers['X-Mailer'] = "Php/Badnet";
		$this->_xtra_headers['X-Mailer'] = "Php/Badnet";
	}

	/**
	 * Define the subject line of the email
	 * @param  string $subject  any valid mono-line string
	 * @return void
	 */
	public function Subject( $aSubject )
	{
		$prefix = Bn::getConfigValue('prefix', 'email');
		if (!empty($prefix)) $prefix = '[' . $prefix . '] ';
		$this->_mailMime->setSubject($prefix . $aSubject);
	}

	/**
	 * Set the sender of the mail
	 * @param  string $from should be an email address
	 * @return void
	 */
	public function From($aEmail)
	{
		$this->_mailMime->setFrom($aEmail);
		if (!isset($this->_xtra_headers['Reply-To']))
		$this->_xtra_headers["Reply-To"] = $aEmail;
	}

	/**
	 * Set the CC headers ( carbon copy )
	 * @param  string $cc email address(es), accept both array and string
	 * @return void
	 */
	public function Cc( $aEmails )
	{
		if ( ! is_array($aEmails) )
		{
			$tos = preg_split("/[,; ]+/", $aEmails);
		}
		else
		{
			$tos = $aEmails;
		}
		foreach($tos as $to)
		{
			$this->_mailMime->addCc($to);
		}
	}

	/**
	 * set the Bcc headers ( blank carbon copy ).
	 * @param  string $bcc email address(es), accept both array and string
	 * @return void
	 */
	public function Bcc( $aEmails )
	{
		if (is_array($aEmails))
		{
			$tos = $aEmails;
		}
		else
		{
			$tos = preg_split("/[,; ]+/", $aEmails);
		}
		foreach($tos as $to)
		{
			$this->_mailMime->addBcc($to);
		}
	}

	/**
	 * Set the Reply-to header
	 * @param  string $email should be an email address
	 * @return void
	 */
	public function ReplyTo( $aEmail)
	{
		$this->_xtra_headers['Reply-To']= $aEmail;
	}

	/**
	 * set the Body of the mail (message)
	 * @param  string $body  message
	 * @return void
	 */
	public function Body($aBody)
	{
		$this->_mailMime->setTXTBody($aBody);
	}

	/**
	 * Attach a zip file to the mail
	 * @param  string $filename : path of the file to attach
	 * @return void
	 */
	public function addZip($aFilename, $aName)
	{
		$this->_mailMime->addAttachment($aFilename,
				      "application/zip", $aName);
	}

	/**
	 * Attach a pdf file to the mail
	 *
	 * @access public
	 * @param  string $filename : path of the file to attach
	 * @return void
	 */
	public function addPdf($aFilename, $aName='')
	{
		$this->_mailMime->addAttachment($aFilename,
				      "application/pdf", $aName);
	}

	/**
	 * set the Organisation header
	 * @param  string $org  organisation
	 * @return void
	 */
	public function Organization( $aOrg )
	{
		if( trim( $aOrg != "" )  )
		{
			$this->_xtra_headers['Organization'] = $aOrg;
		}
	}

	/**
	 * set the mail priority
	 * @param  integer $priority  integer taken between 1 (highest) and 5 ( lowest )
	 * @return void
	 */
	public function Priority( $aPriority )
	{

		if( intval( $aPriority ) &&
		isset( $this->_priorities[$aPriority-1]) )
		{
			$this->_xtra_headers["X-Priority"] = $this->_priorities[$aPriority-1];
		}
	}

	/**
	 * Add a receipt to the mail ie.  A confirmation is returned to
	 *  the "From" address (or "ReplyTo" if defined)
	 *  when the receiver opens the message.Set the Reply-to header
	 * @return void
	 */
	public function Receipt( )
	{
		$this->_receipt = True;
	}

	/**
	 * Format and send the mail
	 * @param $aEmails   destinataires
	 * @param $aFooter   Insertion pied de mail
	 * @return void
	 */
	public function Send($aEmails, $aFooter = true)
	{
		//$this->Bcc('cage@free.fr');
		if ( $aFooter )
		{
			$version = 'v3.0r0';
			$prefix = Bn::getConfigValue('prefix', 'email');
			if ( empty($prefix) ) $prefix = 'BadNet';
			$foot = "\n\nCordialement\nL'équipe $prefix\n\n-----------------\n";
			$foot .= "Email envoyé par BadNet $version.\n";
			$foot .= "Pour plus d'information, consultez le site\n";
			$foot .= "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
			$this->_mailMime->setTXTBody($foot, false, true);
		}
		// envoi du mail aux destinataires principaux
		$type = BN::getConfigValue('type', 'email');
		if ($type=='smtp')
		{
	  		$param= array("host" => BN::getConfigValue('host', 'email'),
			"port" => BN::getConfigValue('port', 'email'),
			"auth" => BN::getConfigValue('auth', 'email'),
			"username" => BN::getConfigValue('username', 'email'),
			"password" => BN::getConfigValue('password', 'email')
			);
	  if ($param['host']=='' ||
	  $param['username']=='')
	  {
	  	$this->setError('Host ou user non renseign�');
		  return false;
	  }
	  $mailer = Mail::factory($type, $param);
		}
		else
		{
			$mailer = Mail::factory($type);
		}
		//$img = utimg::getLogo('badnet.jpg');
		//$res = $this->_mailMime->addHTMLImage($img, 'image/jpg');
		//if (PEAR::isError($res))
		//	echo $res->getMessage();

		// Corps du message
		$params = array('text_charset' => 'utf-8',
						'head_charset' => 'utf-8');
		$body = $this->_mailMime->get($params);

		// En tete supplementaires
		if( $this->_receipt )
		{
			$this->_xtra_headers["Disposition-Notification-To"] = $this->_xtra_headers["Reply-To"];
		}
		$headers = $this->_mailMime->headers($this->_xtra_headers);

		// Destinataire
		if (is_array($aEmails)) $tos = $aEmails;
		else $tos = preg_split("/[,; ]+/", $aEmails);
		$this->_to['To'] = implode(',', $tos);
		
		$res =  $mailer->send($this->_to, $headers, $body);
		Bn::log($this->_to, 'Email');
		Bn::log($headers, 'Email');
		Bn::log($body, 'Email');
		unset($body);
		unset($headers);
		if ( PEAR::isError($res) )
		{
			$this->_error($res->getMessage());
			return false;
		}
		return true;
	}

	/**
	 * Return the whole e-mail , headers + message
	 *  can be used for displaying the message in plain text or logging it
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
}
?>