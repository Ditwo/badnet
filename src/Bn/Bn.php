<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once "PEAR.php";
require_once "Bn/Bn.inc";
require_once "Bn/Db.php";
require_once "Bn/GvRows.php";

define('BN_CONFIG_FILE', '../Conf/Conf.ini');

function __autoload($aClassObject)
{
	if ($aClassObject[0] == 'O')
	{
		require_once 'Object/' . $aClassObject . '.php';
	}
}


class BN
{
	static private $_pathImg = '';
	static private $_project = '';
	static private $_module = '';
	static private $_traceMsg  = array(); // Messages de trace
	static private $_userMsg  = array(); // Messages d'erreur pour l'utlisateur
	static private $_dlgTrace  = null; // Dialogue pour afficher les traces
	static private $_startprofile = null;
	static private $_prevprofile = null;
	static private $_startmemory = null;
	static private $_prevmemory = null;

	static public function stripAccent($aStr)
	{
		return strtr($aStr,
 "\xe1\xc1\xe0\xc0\xe2\xc2\xe4\xc4\xe3\xc3\xe5\xc5".
 "\xaa\xe7\xc7\xe9\xc9\xe8\xc8\xea\xca\xeb\xcb\xed".
 "\xcd\xec\xcc\xee\xce\xef\xcf\xf1\xd1\xf3\xd3\xf2".
 "\xd2\xf4\xd4\xf6\xd6\xf5\xd5\x8\xd8\xba\xf0\xfa\xda".
 "\xf9\xd9\xfb\xdb\xfc\xdc\xfd\xdd\xff\xe6\xc6\xdf\xf8",
 "aAaAaAaAaAaAacCeEeEeEeEiIiIiIiInNoOoOoOoOoOoOoouUuUuUuUyYyaAso");
		//return strtr($aStr,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ',
//'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
		
	}
	
	static public function profile($aTexte=null)
	{
		if ( empty(self::$_startprofile) )
		{
			$now = Bn::getMicrotime();
			self::$_startprofile = $now;
			self::$_prevprofile = $now;
			$memory = memory_get_usage();
			self::$_startmemory = $memory;
			self::$_prevmemory = $memory;
		}
		else
		{
			$now = Bn::getMicroTime();
			echo $aTexte . ' : ';
			echo sprintf('%.04f', ($now - self::$_prevprofile));
			echo ' ; ';
			echo sprintf('%.04f', ($now - self::$_startprofile));
			echo ' ; ';
			$memory = memory_get_usage();
			echo $memory - self::$_prevmemory;
			echo ' ; ';
			echo $memory - self::$_startmemory;
			echo '<br>';
			self::$_prevmemory = $memory;
			self::$_prevprofile = $now;
		}
	}

	/**
	 * Ajoute un message d'erreur destine a etre affiche
	 *
	 * @param string $aMsg  Message a afficher
	 */
	static public function setUserMsg($aMsg) {self::$_userMsg = $aMsg;}

	/**
	 * Renvoi le message utilisateur
	 *
	 * @return unknown
	 */
	static public function getUserMsg() {
		$msg = self::$_userMsg;
		self::$_userMsg = null;
		return $msg;
	}

	/**
	 * Renvoi le code captcha utilise
	 */
	static public function getCaptcha()
	{
		return Bn::getValue('phrase');
	}
	/**
	 * Renvoi une image captcha
	 */
	static public function getCaptchaImage()
	{
		// Set CAPTCHA options (font must exist!)
		require_once 'Text/CAPTCHA.php';
		$options = array(
		       'font_size' => 24,
		       'font_path' => dirname(__FILE__).'/',
		       'font_file' => 'cour.ttf',
		       'text_color' => '#3DA558',
		       'line_r' => 0x3D,
		       'line_v' => 0xA5,
		       'line_b' => 0x58
		);
		$text = array('length' => 8,
		    'type'   => 'unpronounceable',
		    'chars'  => 'numeric');

		// Generate a new Text_CAPTCHA object, Image driver
		$c = Text_CAPTCHA::factory('Image');
		$retval = $c->init(200, 80, $text, $options);
		if (PEAR::isError($retval)) {
			Bn::error($retval);
			Bn::log($retval);
			exit;
		}

		// Get CAPTCHA secret passphrase
		//$_SESSION['phrase'] = $c->getPhrase();
		Bn::setValue('phrase', $c->getPhrase());

		// Get CAPTCHA image (as PNG)
		$png = $c->getCAPTCHAAsPNG();
		if (PEAR::isError($png)) {
			Bn::error('Error generating CAPTCHA!');
			Bn::log($png);
			exit;
		}

		// Nom du fichier de l'image a generer
		$path = realpath('../Temp/Tmp/');
		$filename = tempnam($path.'/', 'bdnet');

		// Purge des anciennes images
		$t = time();
		$h = opendir($path);
		while( $file = readdir($h) )
		{
			$lastmodif = filemtime($path.'/'.$file);
			if( substr($file, 0, 5 ) == 'bdnet' && ($t-$lastmodif > 3600) )
			{
				unlink($path.'/'.$file);
			}
		}
		closedir($h);

		// Creation du nouveau code
		$fd = fopen($filename, 'w');
		fwrite($fd, $png);
		fclose($fd);
		chmod($filename, 0777);
		return $filename;
	}


	/**
	 * Affichage des traces
	 *
	 * @param BN_balise $aDiv Balise qui contiendra les messages de trace
	 */
	static public function displayTrace(BN_balise $aDiv)
	{
		// Affichage des messages de traces
		require_once 'Bn/Balise/Image.php';

		$nbMsg = 0;
		$action = Bn::getValue('bnAction', 'none');
		$div = new Bn_balise('div', 'bnDebug'.$action);
		$div->setAttribute('style', "display:none;position:absolute; top:10px;left:10px;width:600px;height:550px;background:#fff;");
		$img = new Image('imgDebugClose'.$action, 'Bn/Img/bug.png', 'Traces');
		$img->setAttribute('class', 'imgDebug');
		$img->addJQReady('$("#imgDebugClose'. $action . '").click(function(){$("#bnDebug'.  $action .'").hide();});');
		$div->addContent($img);

		// Ajouter l'action aux traces
		array_unshift(self::$_traceMsg,
		array(BN_LEVEL_TRACE,
		$action . ';' . sprintf('0x%04X', $action),
			  'Action')); 
		$levels = array( BN_LEVEL_ERROR => Bn::getConfigValue('error', 'information'),
		BN_LEVEL_WARNING => Bn::getConfigValue('warning', 'information'),
		BN_LEVEL_TRACE => Bn::getConfigValue('trace', 'information'));
		foreach (self::$_traceMsg as $trace)
		{
			$level = $trace[0];
			if ( $level == BN_LEVEL_CRITICAL ||
			$levels[$level] == 1)
			{
				$label = empty($trace[2]) ? '' : $trace[2] . ' :';
				$info = $div->addInfo('trace'. $action . $nbMsg, $label, $trace[1]);
				$info->setAttribute('class', 'bnTrace' . $level);
				$div->addBreak();
				$nbMsg++;
			}
			if ($nbMsg > 20) break;
		}

		$div->addBreak();
		if ($nbMsg)
		{
			$aDiv->addContent($div);
			$img = new Image('imgDebug'.$action, 'Bn/Img/bug.png', 'Traces');
			$img->setAttribute('class', 'imgDebug');
			$img->addJQReady('$("#imgDebug'. $action . '").click(function(){$("#bnDebug'.  $action .'").show();});');
			$aDiv->addContent($img);
		}
	}

	/**
	 * Set the image path
	 */
	static public function setImagePath($aPath)
	{
		self::$_pathImg = $aPath;
	}

	/**
	 * Return the image path
	 */
	static public function getImagePath()
	{
		if (empty(self::$_pathImg))
		{
			$theme = Bn::getValue('theme', 'Badnet');
			$path = '../Themes/' . $theme . '/Img/';
		}
		else $path=self::$_pathImg;
		return $path;

	}

	/**
	 * Return the Project
	 */
	static public function getProject()
	{
		return self::$_project;
	}
	/**
	 * Set the project
	 */
	static public function setProject($aProject)
	{
		self::$_project = $aProject;
	}

	/**
	 * Return the module
	 */
	static public function getModule()
	{
		return self::$_module;
	}
	/**
	 * Set the module
	 */
	static public function setModule($aModule)
	{
		self::$_module = $aModule;
	}

	/**
	 * return the time in millisecond
	 */
	static public function getMicroTime()
	{
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}

	/**
	 * Formate une date iso (yyyy-mm-dd hh:mm:ss) generalement issue de mysql
	 * @param string $aDate	    Date a formater
	 * @param string $aFormat	Format de sortie
	 */
	static public function date($aDate, $aFormat)
	{
		if ($aDate == '0000-00-00 00:00:00' || empty($aDate) ) return '';
		list($y, $m, $d, $h, $mn, $s) =
		sscanf($aDate, "%04u-%02u-%02u %02u:%02u:%02u");
		$timeStamp = mktime ( $h, $mn, $s, $m, $d, $y);
		return date($aFormat, $timeStamp);
	}

	/**
	 * Formate une date iso (yyyy-mm-dd hh:mm:ss) generalement issue de mysql
	 * @param string $aDate	    Date a formater
	 * @param string $aFormat	Format de sortie
	 */
	static public function strdate($aDate, $aFormat)
	{
		list($y, $m, $d, $h, $mn, $s) =
		sscanf($aDate, "%04u-%02u-%02u %02u:%02u:%02u");
		$timeStamp = mktime ( $h, $mn, $s, $m, $d, $y);
		return strftime($aFormat, $timeStamp);
	}

	/**
	 * Return uniqu id for database record
	 */
	static public function getUniId($aId)
	{
		// Identifiant unique de l'enregistrement
		$q = new BN_query('meta');
		$q->addField('meta_value');
		$q->addWhere("meta_name = 'databaseId'");
		$localDBId = $q->getOne();
		if ( $q->isError() )
		{
			return $q->getMsg();
		}
		if ( empty($localDBId) )
		{
			$localDBId = gmmktime();
			$q->addValue('meta_name', 'databaseId');
			$q->addValue('meta_value', $localDBId);
			$q->replaceRow();
			if ( $q->isError() )
			{
				return $q->getMsg();
			}
		}
		$uniId =  $localDBId .':' . $aId . ';';
		return $uniId;
	}

	/**
	 * Fonction d'appel aux Web services
	 * @param string  $aFunction 	Nom de la fonction
	 * @param array   $aArgs		Tableau des parametres
	 * @param string  $aService		Nom du service
	 * @return 	true
	 */
	public function client($aFunction, $aArgs, $aService='Poona', $aUrl = null)
	{
		require_once 'SOAP/Client.php';

		if ( is_null($aUrl) ) $url = Bn::getConfigValue($aService, 'services');
		else $url = $aUrl;
		$soapclient = new SOAP_Client($url . '/Src/index.php?ajax=true&bnService=' . $aService);

		$options = array();

		/* This namespace is the same as declared in server.php. */
		$options['namespace'] = 'urn:' . $aService . '_server';

		/* Trace the communication for debugging purposes, so we can later inspect the
		 * data with getWire(). */
		$options['trace'] = true;

		/* Uncomment the following lines if you want to use Basic HTTP
		 * Authentication. */
		// $options['user'] = 'username';
		// $options['pass'] = 'password';
		header('Content-Type: text/plain');

		/* Calling the fonction. */
		$ret = $soapclient->call($aFunction, $aArgs, $options);
		$res = new Bn_error();
		if (PEAR::isError($ret))
		{
			$res->_error("Erreur d'accès au serveur ". $aService);
			Bn::log($ret->getMessage());
			Bn::log('----------------------');
			//Bn::log($ret);
			//Bn::log('----------------------');
			Bn::log($soapclient->getWire());
		}
		else
		{
			$res->setData($ret);
		}
		return ($res);
	}

	/**
	 * Fonction de traitement des erreurs PHP
	 * @param 	int		$aErrno		Numero de l'erreur
	 * @param 	string	$aErrstr	Message d'erreur
	 * @param	string	$aErrfile	Fichier d'ou vient l'erreur
	 * @param 	int		$aErrline	Numero de ligne
	 * @return 	true
	 */
	static public function errorHandler($aErrno, $aErrstr, $aErrfile, $aErrline)
	{
		$msg = "Ligne $aErrline; fichier $aErrfile".
        	"; PHP " . PHP_VERSION . " (" . PHP_OS . ")";
		switch ($aErrno) {
			case E_USER_ERROR:
				Bn::critical("[$aErrno] $aErrstr", "Error");
				Bn::critical($msg);
				break;

			case E_USER_WARNING:
				Bn::error("[$aErrno] $aErrstr", "Warning");
				Bn::error($msg);
				break;

			case E_USER_NOTICE:
				Bn::warning("[$aErrno] $aErrstr", "Notice");
				Bn::warning($msg);
				break;

			default:
				Bn::trace("[$aErrno] $aErrstr", "Type d'erreur inconnu");
				Bn::trace($msg);
				break;
		}

		/* Ne pas executer le gestionnaire interne de PHP */
		return true;
	}

	/**
	 * Fonction de traitement des exception
	 * @param 	mixed	$aE		Execption
	 * @return 	true
	 */
	static function exceptionHandler($aE)
	{
		Bn::log($aE->getMessage());
		Bn::log($aE->getTraceAsString());
		Bn::trace($aE->getTraceAsString());
		$page = Bn::getPage();
		$page->display();
	}

	/**
	 * Renvoi le controller
	 * @return 	string
	 */
	static public function getController()
	{
		static $_controller = null;
		if ( is_null($_controller) )
		{
			require_once 'Controller.php';
			$_controller = new Controller();
		}
		return $_controller;
	}

	/**
	 * Uploader un fichier sur le serveur
	 * @param 	string		$aFile		Nom du fichier a tranferer
	 * @param 	string		$aDirectory	Nom du dossier cible sur le serveur
	 * @return 	void
	 */
	static public function upload($aFile, $aDirectory)
	{
		require_once "Bn/Upload.php";
		return Bn_upload::upload($aFile, $aDirectory);
	}

	/**
	 * Acces a un parametre du fichier de configuration
	 * @param	string		$aName		Nom du parametre
	 * @param	string		$aSection	Nom de la section
	 * @param 	string		$aFile		Nom du fichier de configuration dans le dossier Conf
	 * @return 	string
	 */
	static public function getConfigValue($aName, $aSection=null, $aFile = null)
	{
		if ( empty($aFile) )
		{
			$file = BN_CONFIG_FILE;
		}
		else
		{
			$file = '../Conf/' . $aFile;
		}
		$config = @parse_ini_file($file, true);
		if ( $config == null)
		{
			Bn::warning('Fichier  <'. $file . '> inaccessible ', 'Config');
			return null;
		}
		if ( !isset($aSection) )
		{
			if ( isset($config[$aName]) )
			{
				return $config[$aName];
			}
			else
			{
				Bn::warning('Mot clef <' . $aName . '> introuvable dans le fichier ' . $file, 'Config');
				return null;
			}
		}
		else
		{
			if ( isset($config[$aSection][$aName]) )
			{
				return $config[$aSection][$aName];
			}
			else if ( !isset($config[$aSection]) )
			{
				Bn::Warning('Section <'. $aSection . '> introuvable dans le fichier ' .$file, 'Config');
				return null;
			}
			else
			{
				Bn::Warning('Mot clef <'. $aName . '> introuvable dans la section <' . $aSection . '> du fichier ' .$file, 'Config');
				return null;
			}
		}
	}

	/**
	 * Acces au donnees de configuration pour le contrle des login
	 *
	 */
	static public function getAuth()
	{
		static $auth = null;

		if ( empty($auth) )
		{
			$config = parse_ini_file(BN_CONFIG_FILE, true);
			if( isset($config['auth']) ) $auth = $config['auth'];
		}
		return $auth;
	}

	/**
	 * Renvoi la langue
	 * @return 	string
	 */
	static public function getLocale($aDefault = 'Fr')
	{
		$locale = BN::getValue('locale', $aDefault);
		if ( empty($locale) ) $locale = $aDefault;
		//@todo provisoire a supprimer
		if ($locale=='fra') $locale = 'Fr';
		Bn::setValue('locale', $locale);
		return $locale;	
	}
	
	
	/**
	 * Renvoi la page
	 * @return 	Bn_page
	 */
	static public function getPage()
	{
		require_once "Bn/Page.php";
		return Bn_page::getPage();
	}

	/**
	 * Renvoi la mailer pour envoyer un email
	 * @return 	Bn_mail
	 */
	static public function getMailer()
	{
		require_once "Bn/Mail.php";
		return new BN_Mail();
	}

	/**
	 * Acces a une donnee $_GET, $_POST ou $_SESSION
	 * @param	string		$aName		Nom de la donnee
	 * @param	string		$aDefault	Valeur par defaut
	 * @return 	string
	 */
	static public function getValue($aName, $aDefault='', $aHtml=false)
	{
		$res = Bn::getValueHtml($aName, $aDefault);
		if ( is_array($res) )
		{
			return $res;
		}

		if ( $aHtml)
		{
			return $res;
		}
		else
		{
			return htmlspecialchars($res);
		}
	}

	/**
	 * Get the attribut with the post or get
	 * @param	string		$aName		Nom de la donnee
	 * @param	string		$aDefault	Valeur par defaut
	 * @return 	string
	 */
	static public function getValueHtml($aName, $aDefault='')
	{
		$value = Bn::_stripslashes_deep($aDefault);

		if (isset($_GET[$aName]))
		{
			$value = Bn::_stripslashes_deep($_GET[$aName]);
		}
		else if (isset($_POST[$aName]))
		{
			$value = Bn::_stripslashes_deep($_POST[$aName]);
		}
		else if (isset($_COOKIE[$aName]))
		{
			$value = $_COOKIE[$aName];
		}
		else if (isset($_SESSION['bn'][$aName]))
		{
			$value = $_SESSION['bn'][$aName];
		}
		else
		{
			require_once "Bn/Auth.php";
			$auth = new Bn_Auth();
			$auth->check();
			$value = $auth->getValue($aName, $aDefault);
			unset($auth);
		}
		return $value;
	}

	/**
	 * Memorisation d'une valeur
	 * @param string 	$aName	Nom de la variable
	 * @param string	$aValue	Contenu de la variable
	 * @return void
	 */
	static public function setValue($aName, $aValue)
	{
		/*require_once "Bn/Auth.php";
		$auth = new Bn_Auth();
		$auth->check();
		$auth->setValue($aName, $aValue);
		unset($auth);
*/
		$_SESSION['bn'][$aName] = $aValue; 
	}

	/**
	 * Memorisation d'une valeur
	 * @param string 	$aName	Nom de la variable
	 * @param string	$aValue	Contenu de la variable
	 * @return void
	 */
	static public function setParam($aName, $aValue)
	{
		$_POST[$aName] = $aValue;
	}

	/**
	 * Supression d'une valeur
	 * @param string	$aName	Nom de la valeur
	 * @return 	void
	 */
	static public function unsetValue($aName)
	{
		$oldValue = empty($_SESSION['bn'][$aName]) ? null: $_SESSION['bn'][$aName];
		unset($_SESSION['bn'][$aName]);
		return $oldValue;
	}

	/**
	 * Ajoute un message dans le fichier de log
	 * @param	string	$aMasg		Message a ajouter
	 * @return 	void
	 */
	static public function log($aMsg, $aFile='Bn')
	{
		require_once 'Log.php';
		$conf = array('mode' => 0777, 'timeFormat' => '%X %x');
		$fileName = '../Temp/Log/' . date('Y-m-d-'). $aFile .'.log';
		$logger = Log::singleton('file', $fileName, 'ident', $conf);
		$str = 'user : ' . Bn::getValue('user_id') . ' - ' . Bn::getValue('user_name'). ' - ' . Bn::getValue('user_email') ;
		$str .= Bn::_stringMsg($aMsg);
		$logger->log($str);
	}

	/**
	 * Ajoute un message de trace dans la fenettre courante
	 * @param	string	$aMasg		Message a ajouter
	 * @param	string	$aLabel		Label a ajouter devant le message a ajouter
	 * @return 	void
	 */
	static public function trace($aMsg, $aLabel='')
	{
		require_once "Page.php";
		$page = Bn_Page::getPage();
		$page->addTrace(BN_LEVEL_TRACE, Bn::_stringMsg($aMsg), $aLabel);
		self::$_traceMsg[] = array(BN_LEVEL_TRACE, Bn::_stringMsg($aMsg), $aLabel);
	}

	/**
	 * Ajoute un message d'avertissememnt dans la fenettre courante
	 * @param	string	$aMasg		Message a ajouter
	 * @param	string	$aLabel		Label a ajouter devant le message a ajouter
	 * @return 	void
	 */
	static function warning($aMsg, $aLabel='')
	{
		require_once "Page.php";
		$page = Bn_Page::getPage();
		$page->addTrace(BN_LEVEL_WARNING, Bn::_stringMsg($aMsg), $aLabel);
		self::$_traceMsg[] = array(BN_LEVEL_WARNING, $aMsg, $aLabel);
	}

	/**
	 * Ajoute un message d'erreur dans la fenettre courante
	 * @param	string	$aMasg		Message a ajouter
	 * @param	string	$aLabel		Label a ajouter devant le message a ajouter
	 * @return 	void
	 */
	static function error($aMsg, $aLabel='')
	{
		require_once "Page.php";
		$page = Bn_Page::getPage();
		$page->addTrace(BN_LEVEL_ERROR, Bn::_stringMsg($aMsg), $aLabel);
		self::$_traceMsg[] = array(BN_LEVEL_ERROR, $aMsg, $aLabel);
	}

	/**
	 * Ajoute un message critique dans la fenettre courante
	 * @param	string	$aMasg		Message a ajouter
	 * @param	string	$aLabel		Label a ajouter devant le message a ajouter
	 * @return 	void
	 */
	static public function critical($aMsg, $aLabel='Trace')
	{
		require_once "Page.php";
		$page = Bn_Page::getPage();
		$page->addTrace(BN_LEVEL_CRITICAL, Bn::_stringMsg($aMsg), $aLabel);
		self::$_traceMsg[] = array(BN_LEVEL_CRITICAL, $aMsg, $aLabel);
	}

	/**
	 * Encode une donne au format Json
	 * @param 	mixed	$aVar	Variable a encoder
	 * @return 	string
	 */
	static public function toJson($aVar)
	{
		static $json = null;
		if ( is_null($json) )
		{
			require_once 'Bn/Json.php';
			$json = new Services_JSON();
		}
		return $json->encode($aVar);
	}

	/**
	 * Transforme un objet en chaine de caractere
	 */
	static private function _stringMsg($aMsg)
	{
		if (is_object($aMsg))
		{
			//			if ( method_exists($aMsg, 'toString') )
			//			{
			//				echo "objet avec toString";
			//				$msg = $aMsg->toString();
			//			}
			//			else
			{
				ob_start();
				print_r($aMsg);
				$msg = ob_get_contents();
				ob_end_clean();
			}
		}
		else if ( is_array($aMsg) )
		{
			ob_start();
			print_r($aMsg);
			$msg = ob_get_contents();
			ob_end_clean();
		}
		else
		{
			$msg = $aMsg;
		}
		return $msg;
	}

	static private function _stripslashes_deep($aValue)
	{
		if ( get_magic_quotes_gpc() )
		$aValue = is_array($aValue) ?
		array_map(array('BN' ,'_stripslashes_deep'), $aValue) :
		stripslashes($aValue);

		return $aValue;
	}
}


class BN_error
{
	protected $_isError = false;
	protected $_errMsg  = array();
	private   $_data    = array();

	public function isError()
	{
		return $this->_isError;
	}

	/**
	 * Renvoi le premier message
	 * @return	string
	 */
	public function getMsg()
	{
		return reset($this->_errMsg);
	}

	/**
	 * Renvoi les donnees
	 * @return	mixed
	 */
	public function getData()
	{
		return $this->_data;
	}


	/**
	 * Ajoute un message sans lever d'erreur
	 * @return	string
	 */
	public function addMsg($aMsg)
	{
		$this->_errMsg[] = $aMsg;
	}

	/**
	 * Ajoute une donnees
	 * @return	string
	 */
	public function addData($aData)
	{
		$this->_data[] = $aData;
	}

	/**
	 * Remplace les donnees
	 * @return	string
	 */
	public function setData($aData)
	{
		$this->_data = $aData;
	}


	/**
	 * Retourne au format Json pour ajax
	 * @return	string
	 */
	public function toJson()
	{
		$res['msg']   = reset($this->_errMsg);
		$res['error'] = $this->_isError;
		$res['data']  = $this->_data;
		return json_encode($res);
	}

	/**
	 * Renvoi tous les messages concatenes
	 * @return	string
	 */
	public function toString()
	{
		return implode('\n\r', $this->_errMsg);
	}

		/**
	 * Rajoute une erreur
	 * @param	string	$aMsg	Message d'erreur
	 * @return	integer		nombre de messages
	 */
	public function error($aMsg)
	{
		$this->_isError = true;
		$this->_errMsg[] = $aMsg;
		Bn::log($aMsg);
		return count($this->_errMsg);
	}
	
	/**
	 * Rajoute une erreur
	 * @param	string	$aMsg	Message d'erreur
	 * @return	integer		nombre de messages
	 */
	public function _error($aMsg)
	{
		$this->_isError = true;
		$this->_errMsg[] = $aMsg;
		Bn::log($aMsg);
		return count($this->_errMsg);
	}

}
?>