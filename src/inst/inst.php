<?php
/*****************************************************************************
 !   Module    : Installation
 !   File       : $Source: /cvsroot/aotb/badnet/src/inst/inst.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.11 $
 ******************************************************************************/

require_once "base_A.php";
require_once "utils/utimg.php";
require_once "utils/utpage_V.php";

define("INST_NEWLANG",       WBS_INST);
define("INST_NEWEXIST",      WBS_INST+1);
define("INST_SERVER",        WBS_INST+2);
define("INST_DATABASE",      WBS_INST+3);
define("INST_MANAGER",       WBS_INST+4);
define("INST_PARAMETERS",    WBS_INST+5);

/**
 * Installation class of the soft
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class Inst
{

	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function inst()
	{
	}

	/**
	 * Start installation
	 *
	 * @access public
	 * @param  string  $lang Language use for the installation
	 * @param  string  $version Version number of the database
	 * @return void
	 */
	function start($aVersion)
	{
		$this->_checkDir();
		$page = kform::getActId();
		switch ($page)
		{
			case INST_NEWLANG:
				utvars::setLanguage(kform::getData());
				$infos['server_db'] = kform::getInput('serverDb');
				$infos['login_db']  = kform::getInput('loginDb');
				$infos['pass_db']   = kform::getInput('passDb');
				$infos['type_db']   = kform::getInput('typeDb');
				$this->_displayFormServer($infos);
				break;
			case INST_NEWEXIST:
				$infos =array("choix_db" => kform::getInput("choixDb"),
			"new_db"   => kform::getInput("newDb"),
			"list_db"  => kform::getInput("listDb"),
			"prefx_db"  => kform::getInput("prefxDb")
				);
				$this->_displayFormDatabase($infos);
				break;
			case INST_SERVER:
				$this->_checkServer();
				break;
			case INST_DATABASE:
				$this->_checkDatabase($aVersion);
				break;
			case INST_MANAGER:
				$this->_checkAdmin();
				break;
			case INST_PARAMETERS:
				$this->_updateParam();
			default :
				$this->_initConnect($aVersion);
				break;
		}
		return true;
	}

	/**
	 * Control the existance of directories
	 *
	 * @access private
	 * @return boolean  True if all is correct
	 */
	function _checkDir()
	{
		$list = array('archive', 'Conf', 'data', 'export', 'img', 'img/poster',
		    'img/logo', 'img/photo', 'img/logo_asso', 
		    'img/photo_mber', 'pdf', 'sessions', 'tmp',
		    'Temp', 'Temp/Log', 'Temp/Pdf', 'Temp/Sessions', 'Temp/Tmp');

		foreach($list as $rep)
		{
			$path = "../$rep";
			if (!is_dir($path)) $err[] = $rep;
			else
			{
				$fd = @fopen("{$path}/test", "w");
				if ($fd === FALSE) $err[] = $rep;
				else
				{
					@fclose($fd);
					@unlink("{$path}/test");
				}
			}
		}
		if (isset($err))
		{
			$err['errMsg'] = "msgErrDir";
			$this->_displayError($err);
		}
		return;
	}

	/**
	 * Control if the administrator exist in the database
	 *
	 * @access private
	 * @return boolean  True if all is correct
	 */
	function _checkAdmin()
	{
		$infos = array("user_name"   => kform::getInput("userName"),
		     "user_login"  => kform::getInput("userLogin"),
		     "user_pseudo" => kform::getInput("userPseudo"),
		     "user_pass"   => kform::getInput("userPass"),
		     "user_email"  => kform::getInput("userEmail")
		);

		// Verify if the administrator user exist
		$dt = new instBase_A();
		if ($dt->existAdmin()) return true;

		// Verify the data
		if ($infos['user_name'] == "")
		{
			$infos['errMsg'] = 'msguser_name';
			$this->_displayFormAdmin($infos);
		}

		if ($infos["user_login"] == "")
		{
			$infos['errMsg'] = 'msguser_login';
			$this->_displayFormAdmin($infos);
		}

		// Create the administrator
		$res = $dt->createAdmin($infos, utvars::getLanguage());
		if (is_array ($res))
		{
			$infos['errMsg'] = $res['errMsg'];
			$this->_displayFormAdmin($infos);
		}
		return true;
	}

	/**
	 * Control the information to establish a connection with database server
	 *
	 * @access private
	 * @return boolean  True if all is correct
	 */
	function _initConnect($aVersion)
	{
		$dt = new instBase_A();

		// Tester la connexion au serveur
		if (! $dt->isConnected()) $this->_displayFormServer();

		// Mettre a jour la base de donnees
		$baseName = utVars::getBaseName();
		$dt->updateDatabase($baseName);
		return true;
	}

	/**
	 * Control the information to establish a connection with database server
	 *
	 * @access private
	 * @return boolean  True if all is correct
	 */
	function _checkServer()
	{
		// Retrieve informations
		$server_db = kform::getInput("serverDb");
		$login_db  = kform::getInput("loginDb");
		$pass_db   = kform::getInput("passDb");
		$type_db   = kform::getInput("typeDb");
		$lang_db   = utvars::getLanguage();

		// Test de la connexion
		if ($type_db != 'sqlite')
		{
			$dt = new instBase_A();
			$ret = $dt->testConnect($server_db, $login_db, $pass_db, $type_db);
			if (is_array($ret)) $this->_displayFormServer($ret);
		}

		// Register information about connexion in session for later use
		utvars::setSessionVar("server_db", $server_db);
		utvars::setSessionVar("login_db",  $login_db);
		utvars::setSessionVar("pass_db",   $pass_db);
		utvars::setSessionVar("type_db",   $type_db);

		// Display the form for database information
		$this->_displayFormDatabase();
	}


	/**
	 * Control the if the accessibilite of the datatbase
	 * Create and update table.
	 *
	 * @access private
	 * @param  integer  New version of the soft
	 * @return boolean  True if all is correct
	 */
	function _checkDatabase($numVersion)
	{
		// Retrieve informations
		$infos =array("choix_db" => kform::getInput("choixDb"),
		    "new_db"   => kform::getInput("newDb"),
		    "list_db"  => kform::getInput("listDb"),
		    "prefx_db"  => kform::getInput("prefxDb")
		);
		$server_db = utvars::_getSessionVar('server_db');
		$login_db  = utvars::_getSessionVar('login_db');
		$pass_db   = utvars::_getSessionVar('pass_db');
		$type_db   = utvars::_getSessionVar('type_db');
		$prefx_db  = kform::getInput("prefxDb");
		$lang_db   = utvars::getLanguage();
		$dt = new instBase_A();

		// Test the connection
		$ret = $dt->testConnect($server_db, $login_db, $pass_db, $type_db);
		if (is_array($ret)) $this->_displayFormDatabase($ret);

		// Create the new database if necessary
		if ($infos['choix_db'] == 'new')
		{
			if ($infos['new_db'] == '')
			{
				$infos["errMsg"] = 'msgnewDb';
				$this->_displayFormDatabase($infos);
			}
			$err = $dt->newDatabase($server_db, $login_db, $pass_db, $type_db, $infos['new_db']);
			if (is_array($err))
			{
				$infos['errMsg'] = $err['errMsg'];
				$this->_displayFormDatabase($infos);
			}
			$baseName = $infos['new_db'];
		}
		else
		{
			if ($infos['list_db'] == "")
			{
				$infos['errMsg'] = 'msglistDb';
				$this->_displayFormDatabase($infos);
			}
			$baseName = $infos['list_db'];
		}
		if ($type_db == 'sqlite')
		{
			$baseName = '../data/' . $baseName;
		}

		// Register the informations
		$fp = fopen("../data/cnx.dat", "w");
		if ($fp)
		{
			fwrite($fp, "$type_db\t");
			fwrite($fp, "$login_db\t");
			fwrite($fp, "$server_db\t");
			fwrite($fp, "$baseName\t");
			fwrite($fp, "$lang_db\t");
			fwrite($fp, "$pass_db\t");
			fwrite($fp, "$prefx_db\t");
			fclose($fp);
		}
		else
		{
			$infos['errMsg'] = 'msgFileCreation';
			$this->_displayFormDatabase($infos);
		}

		// Initialisation of the table
		$dt = new instBase_A();
		$dt->updateDatabase($baseName, true);
		$ret = $dt->initTables();
		if (is_array($ret))
		{
			unlink('../data/cnx.dat');
			$infos['errMsg'] = $ret['errMsg'];
			$this->_displayFormDatabase($infos);
		}

		// Display form for admnistrator creation
		if (!$dt->existAdmin()) $this->_displayFormAdmin();
	}


	/**
	 * Display the first installation page to get information
	 * about database server
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormServer($err="")
	{
		$page = new utPage_V('inst', false, 0);
		$file = "skins/badnet_A.css";
		$page->_page->addStyleFile($file);
		$file = "inst/inst.css";
		$page->_page->addStyleFile($file);

		$content =& $page->getContentDiv();

		$handle=opendir('./lang');
		while ($file = readdir($handle))
		{
			if ($file != '.' && $file != '..' && $file != 'CVS')
			$itsl[$file] = array(KAF_VALID, 'inst', INST_NEWLANG, $file);
		}
		closedir($handle);

		$form =& $content->addForm('fInst', 'inst', INST_SERVER);

		$kmenu =& $form->addMenu('lang', $itsl, -1);
		$kmenu->setImgPath('img/menu/');
		$kmenu->setDisplay(KMN_DISPLAY_IMG);

		// Set the informations to display
		$infos = array("server_db" => kform::getInput('serverDb','localhost'),
		     "login_db"  => kform::getInput('loginDb','root'),
		     "pass_db"   => kform::getInput('passDb',''),
		     "type_db"   => kform::getInput('typeDb', 'mysql'),
		     "lang_db"   => utvars::getLanguage()
		);


		$form->addMsg("TutServer");

		if (isset($err['errMsg']))
		{
			$form->addWng('msgConnectNOK');
			$form->addWng($err['errMsg']);
		}

		$form->addEdit("serverDb", $infos["server_db"]);
		$form->addEdit("loginDb",  $infos["login_db"]);
		$kedit =& $form->addPwd("passDb",    $infos["pass_db"]);
		$kedit->noMandatory();

		$typeDb = array('mysql' => 'mysql',
		      'sqlite' => 'sqlite'
		      //'Sybase' => 'sybase',
		//'ODBC' => 'odbc',
		//'PostgreSQL' => 'pgsql'
		);
		$form->addCombo("typeDb", $typeDb, $infos["type_db"]);

		$elts = array("serverDb", "loginDb", "passDb", "typeDb");
		$form->addBlock("blkServer", $elts);

		$form->addBtn("btnNext", KAF_SUBMIT );
		$form->addBlock("blkBtn", "btnNext");
		$page->display();
		exit;
	}

	/**
	 * Display the second installation page to get information about
	 * database name
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormDatabase($dt="")
	{
		$page = new utPage_V('inst', false, 0);
		$file = "skins/badnet_A.css";
		$page->_page->addStyleFile($file);
		$file = "inst/inst.css";
		$page->_page->addStyleFile($file);

		$content =& $page->getContentDiv();

		$server_db = utvars::_getSessionVar('server_db');
		$login_db  = utvars::_getSessionVar('login_db');
		$pass_db   = utvars::_getSessionVar('pass_db');
		$type_db   = utvars::_getSessionVar('type_db');
		$prefx_db   = utvars::_getSessionVar('prefx_db');

		$form =& $content->addForm('fInst', 'inst', INST_DATABASE);
		/*$kdiv =& $form->addDiv('divImg', 'cartouche');
		 $img = "img/logo/badnet.png";
		 $kimg =& $kdiv->addImg("badnet", $img);
		 $kimg->setUrl("http://www.badnet.org");
		 */
		$form->addMsg("TutDatabase");

		// Set the informations to display
		if (is_array($dt)) $infos = $dt;
		else
		{
			$infos = array("choix_db" => "new",
			 "new_db"   => "badnet",
			 "list_db"  => '',
			 "prefx_db"  => 'bdnet'
			 );
		}

		if (isset($infos['errMsg'])) $form->addWng($infos['errMsg']);
		if ($type_db == 'sqlite')
		{
			$infos['new_db'] = 'badnet.sqli';
			$kedit =& $form->addEdit("newDb", $infos['new_db'], 10);
			$form->addHide('choixDb', 'new');
			$form->addHide('prefxDb', 'bdnet');
			$form->addHide('listDb', '');
		}
		else
		{
			$isExist = ($infos['choix_db'] == "exist");

			$form->addRadio("choixDb", !$isExist, "new");
			$kedit =& $form->addEdit("newDb", $infos['new_db'], 10);
			$kedit->noMandatory();
			$kedit =& $form->addEdit("prefxDb", $infos['prefx_db'], 5);

			$form->addRadio("choixDb", $isExist, "exist");

			$dt = new instBase_A();
			$bases = $dt->getDatabases($server_db, $login_db, $pass_db, $type_db);
			if ($bases)
			{
				$kcombo =& $form->addCombo("listDb", $bases, $infos['list_db']);
				$kcombo->noMandatory();
			}
			else
			{
				$kedit =& $form->addEdit("listDb", $login_db, 20);
				$kedit->noMandatory();
			}
		}
		$elts = array('choixDb1', 'newDb', 'choixDb2', 'listDb', 'prefxDb');
		$form->addBlock("blkBase", $elts);

		$form->addBtn("btnNext", KAF_SUBMIT);
		$form->addBlock("blkBtn", "btnNext");
		$page->display();
		exit();
	}

	/**
	 * Display the page to get information about
	 * the administrator
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormAdmin($dt="")
	{
		$page = new utPage_V('inst', false, 0);
		$file = "skins/badnet_A.css";
		$page->_page->addStyleFile($file);
		$file = "inst/inst.css";
		$page->_page->addStyleFile($file);

		$content =& $page->getContentDiv();
		$form =& $content->addForm('fInst', 'inst', INST_MANAGER);

		/*$kdiv =& $form->addDiv('divImg', 'cartouche');
		 $img = "img/logo/badnet.png";
		 $kimg =& $kdiv->addImg("badnet", $img);
		 $kimg->setUrl("http://www.badnet.org");
		 */

		$form->addMsg('TutAdmin');

		// Set the informations to display
		if (is_array($dt)) $infos = $dt;
		else
		{
			$infos = array('user_name'   => "",
			 'user_login'  => "",
			 'user_pass'   => '',
			 'user_email'  => '',
			 'user_pseudo' => '',
			);
		}

		if (isset( $infos['errMsg']))
		{
			$form->addWng($infos['errMsg']);
		}

		$kedit =& $form->addEdit('userName', $infos['user_name']);
		$kedit->setMaxLength(50);
		$kedit =& $form->addEdit('userLogin', $infos['user_login']);
		$kedit->setMaxLength(20);
		$kedit =& $form->addEdit('userPseudo', $infos["user_pseudo"]);
		$kedit->setMaxLength(20);
		$kedit =& $form->addPwd('userPass', $infos['user_pass']);
		$kedit->setMaxLength(15);
		$kedit =& $form->addEdit('userEmail', $infos['user_email']);
		$kedit->setMaxLength(50);
		$kedit->noMandatory();
		$elts = array('userName', 'userLogin', 'userPseudo', 'userPass', 'userEmail');
		$form->addBlock('blkAdmi', $elts);

		$form->addBtn('btnNext', KAF_SUBMIT);
		$form->addBlock('blkBtn', 'btnNext');

		$page->display();
		exit();
	}

	/**
	 * Display the page to get general parameters
	 * the administrator
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormParam($params='')
	{
		$page = new utPage_V('inst', false, 0);
		$file = "skins/badnet_A.css";
		$page->_page->addStyleFile($file);
		$file = "inst/inst.css";
		$page->_page->addStyleFile($file);

		$content =& $page->getContentDiv();
		$kform =& $content->addForm('fParam', 'inst', INST_PARAMETERS);

		$kedit =& $kform->addEdit('mainTitle',  'Badminton Netware');
		$kedit =& $kform->addEdit('subTitle',   '...');
		$elts= array('mainTitle', 'subTitle');
		$kform->addBlock('blkTitle', $elts);

		$kedit =& $kform->addEdit('host', '');
		$kedit->noMandatory();
		$kedit =& $kform->addEdit('port', '25');
		$kedit->noMandatory();
		$kedit =& $kform->addEdit('userLog', '');
		$kedit->noMandatory();
		$kedit =& $kform->addPwd('password', '');
		$kedit->noMandatory();
		$elts = array('host', 'port', 'userLog', 'password');
		$kform->addBlock('blkEmail', $elts);

		/*
		$kedit =& $kform->addEdit('ffbaEmail',  'poona@ffba.org',40);
		$kedit->noMandatory();
		$kedit =& $kform->addEdit('ffbaUrl',  '', 40);
		$kedit->noMandatory();
		$kedit =& $kform->addEdit('ibfUrl', '', 40);
		$kedit->noMandatory();
		$elts= array('ffbaEmail', 'ffbaUrl', 'ibfUrl');
		$kform->addBlock('blkUrl', $elts);
		*/
		$kform->addBtn('btnRegister', KAF_SUBMIT);
		$kform->addBlock('blkBtn', 'btnRegister');

		//Display the form
		$page->display();
		exit();
	}

	/**
	 * Display an error
	 * about database server
	 *
	 * @access private
	 * @return void
	 */
	function _displayError($err)
	{
		$page = new utPage_V('inst', false, 0);
		$content =& $page->getContentDiv();
		$file = "skins/badnet_A.css";
		$page->_page->addStyleFile($file);

		$content->addWng($err['errMsg']);
		unset($err['errMsg']);
		foreach($err as $msg)
		$content->addMsg($msg);

		$page->display();
		exit;
	}

	/**
	 * Set the informations for email in the database
	 *
	 * @access public
	 * @return array   array of associations
	 */
	function _updateParam()
	{
		$ut = new Utils();


		$ut->setParam('smtp_server', kform::getInput('host'));
		$ut->setParam('smtp_port', kform::getInput('port'));
		$ut->setParam('smtp_user', kform::getInput('userLog'));
		$ut->setParam('smtp_password', kform::getInput('password'));
		//$ut->setParam('ffba_email', kform::getInput('ffbaEmail'));
		//$ut->setParam('ffba_url', kform::getInput('ffbaUrl'));
		//$ut->setParam('ibf_url', kform::getInput('ibfUrl'));
		$ut->setParam('mainTitle', kform::getInput('mainTitle'));
		$ut->setParam('subTitle', kform::getInput('subTitle'));
		$localDBId = gmmktime();
		$ut->setParam('databaseId', $localDBId);
		return true;
	}
}
?>
