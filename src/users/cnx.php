<?php
/*****************************************************************************
 !   Module     : Connexion (cnx)
 !   File       : $Source: /cvsroot/aotb/badnet/src/users/cnx.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.18 $
 !   Author     : G.CANTEGRIL
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/01/18 07:51:21 $
 !   Mailto     : cage@free.fr
 ******************************************************************************/

require_once "base_V.php";
require_once "cnx.inc";
require_once "utils/utdate.php";
require_once "utils/utimg.php";
require_once "utils/utpage_V.php";


/**
 * Module de gestion des connexion
 *
 * @author Gerard CANTEGRIL <cage@aotb.org>
 * @see to follow
 *
 */

// {{{ displayFormLogin()
/**
 * Display the form to login.
 * This function is call by Auth.
 *
 * @access public
 * @return void
 */
function displayWrongLogin($username="", $obj="")
{
	$utpage = new utPage_V('cnx');
	$content =& $utpage->getContentDiv();
	cnx::_rightColumn($content);
	cnx::_leftColumn($content);
	$kdiv =& $content->addDiv('divDialog');

	$form =& $kdiv->addForm('fNewPwd', 'cnx', CNX_SENDPWD);
	if (is_array($obj))
	{
		$status = $obj->getStatus();
		if ($status == AUTH_WRONG_LOGIN)
		$content->addWng('msgCnxRefused');
		else if ($status == AUTH_IDLED)
		$content->addWng('msgCnxIdled');
		else if ($status == AUTH_EXPIRED)
		$content->addWng('msgCnxExpired');
		$form->addErr('msgCnxRefused');
	}
	$form->addMsg('msgLostPwd');
	$form->addMsg('msgNewPwd');
	$form->addEdit("userInfo", "", 40 );
	$form->setMaxLength("userInfo", 100);

	$elts = array('userInfo');
	$form->addBlock('blkInfog', $elts);

	$form->addBtn("btnSend", KAF_SUBMIT);
	$form->addBtn("btnCancel", KAF_UPLOAD, 'users',  KID_DEFAULT);
	$elts = array('btnSend', 'btnCancel');
	$form->addBlock('blkBtn', $elts);
	$utpage->display();
	exit;
}
// }}}


// {{{ connectUser()
/**
 * update the last connexion date of the user and keep some usual informations
 * This function is call by Auth.
 * @access public
 * @return void
 */
function connectUser($username)
{
	$dt = new UserBase();
	$dt->connectUser($username);
	return;
}
// }}}

// {{{ disconnectUser()
/**
 * update the last connexion date of the user and keep some usual informations
 * This function is call by Auth.
 * @access public
 * @return void
 */
function disconnectUser($username)
{
	$dt = new UserBase();
	$dt->disconnectUser($username);
	$lang = utvars::getLanguage();
	unset($_SESSION["wbs"]);
	utvars::setLanguage($lang);
	return;
}
// }}}


class Cnx
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
	function Cnx()
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
	function start($form, $action)
	{
		$dt = new UserBase();
		$dsn = utVars::getDsn();
		$prefx = utVars::getPrfx();
		$err ='';
		$options = array('dsn'    => $dsn,
                'db_login'      =>  utVars::getDbLogin(),
                'db_pwd'        =>  utVars::getDbPwd(),
      			'table'         => "{$prefx}_users",
		       	'usernamecol'   => 'user_login',
		       	'passwordcol'   => 'user_pass'
		       	);

		       	//$a = new Auth("DB", $options, 'displayFormLogin', false);
		       	require_once "Auth/Auth.php";
		       	$a = new Auth("PDO", $options, '', false);

		       	$a->setLoginCallback('connectUser');
		       	$a->setLogoutCallback('disconnectUser');
		       	$a->setFailedLoginCallback('displayWrongLogin');
		       	$a->start();

		       	// The user already login and don't logout
		       	if (($a->getAuth() && $action != KID_LOGOUT &&
		       	$action != CNX_VALIDLOG &&
		       	$action != WBS_CNX_DIST)
		       	|| $form == 'email')
		       	{
		       		if ($form !='cnx' || $action != CNX_ANONYM)
		       		{
		       			switch ($action)
		       			{
		       				case WBS_ACT_SELECT_EVENT :
		       					$eventId = kform::getInput('eventId', -1);
		       					if ($eventId == -1) $eventId = kform::getData();
		       					$lang = kform::getInput('langu', utvars::getLanguage());
		       					//$data = explode(";", ));
		       					//$page = 'events';
		       					//if (isset($data[1]))
		       					//  $page = $data[1];
		       					utvars::setSessionVar("theme", WBS_THEME_EVENT);
		       					utvars::setEventId($eventId);
								utvars::init();
		       					if(utvars::isTeamEvent())
		       					{
		       						$page = kform::getInput('pageId', 'events');
		       						$act  = kform::getInput('actionId', KID_SELECT);
		       					}
		       					else
		       					{
		       						$page = kform::getInput('pageId', 'draws');
		       						$act  = kform::getInput('actionId', WBS_ACT_DRAWS);
		       					}
		       					kform::setPageId($page);
		       					kform::setActId($act);
		       					kform::setData(kform::getData());
		       					break;
		       				case WBS_ACT_USERS :
		       				case WBS_ACT_EVENTS :
		       				case WBS_ACT_BOOKS :
		       				case WBS_ACT_PREFERENCES :
		       				case WBS_ACT_PARAMETERS:
		       				case WBS_ACT_MAINT:
		       					utvars::setSessionVar("theme", WBS_THEME_NONE);
		       					utvars::setEventId(-1);
		       					break;
		       			}
		       			return;
		       		}
		       		 
		       	}
		       	// The user is not connected
		       	switch ($action)
		       	{
		       		// Creation of a new user
		       		case CNX_CREATEUSER :
		       			$login = $this->_createUser();
		       			$a->setAuth($login);
		       			connectUser($login);
		       			$a->start();
		       			break;
		       		case CNX_VALIDLOG :
		       			$a->logout();
		       			$a->start();
		       			break;
		       		case CNX_NEWLANG :
		       			$lang = kform::getData();
		       			utvars::setLanguage($lang);
		       			$this->_displayFormInit();
		       			break;
		       		case CNX_NEWUSER :
		       			$this->_displayFormNewUser();
		       			break;
		       		case CNX_ANONYM :
		       			//$eventId = kform::getData();
		       			$page = kform::getInput('pageId', 'events');
		       			$eventId = kform::getInput('eventId', -1);
		       			$act  = kform::getInput('actionId', KID_SELECT);
		       			$lang = kform::getInput('langu', utvars::getLanguage());

		       			utvars::setLanguage($lang);
		       			$ute = new utevent();
		       			if (!$ute->updtEventVisited($eventId))
		       			$this->_displayFormInit();
		       			$a->setAuth("demo");
		       			connectUser("demo");
		       			utvars::setEventId($eventId);
		       			utvars::setSessionVar("theme", WBS_THEME_EVENT);
		       			kform::setPageId($page);
		       			kform::setActId($act);
		       			//kform::setData($eventId);
		       			kform::setData(kform::getData());
		       			$a->start();
		       			break;
		       		case WBS_CNX_DIST :
		       			@$a->logout();
		       			$user = kform::getInput('uid', 'demo');
		       			$pwd  = kform::getInput('puid', 'demo');
		       			$page = kform::getInput('pageId', 'events');
		       			$eventId = kform::getInput('eventId', -1);
		       			$act  = kform::getInput('actionId', KID_SELECT);
		       			$lang = kform::getInput('langu', utvars::getLanguage());
		       			utvars::setLanguage($lang);
		       			if ($user == 'demo')
		       			{
		       				$ute = new utevent();
		       				if (!$ute->updtEventVisited($eventId))
		       				$this->_displayFormInit();
		       			}
		       			if (!$dt->checkUserRight($user, $pwd))
		       			$this->_displayFormInit();
		       			$a->setAuth($user);
		       			connectUser($user);
		       			utvars::setEventId($eventId);
		       			utvars::setSessionVar("theme", WBS_THEME_EVENT);
		       			kform::setPageId($page);
		       			kform::setActId($act);
		       			kform::setData($eventId);

		       			$a->start();
		       			break;
		       		case CNX_LOOSEPWD:
		       			displayWrongLogin();
		       		case CNX_SENDPWD:
		       			$err = $this->_newPwd();
		       		case  KID_LOGOUT :
		       			@$a->logout();
		       			$this->_displayFormInit($err);
		       			break;
		       		case WBS_ACT_EMAIL:
		       			$err = $this->_displayFormMail();
		       			break;
		       		case CNX_SENDEMAIL:
		       			$this->_sendMail();
		       			break;
		       		default :
		       			$this->_displayFormInit();
		       	}
	}
	// }}}

	// {{{ _displayFormInit()
	/**
	 * Display the first login screen
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormInit($err='')
	{
		$ut = new utils();
		// Recuperer le modele demande
		$skin = $ut->getParam('default_skin', 'base');
		$tpl  = kform::getInput('tpl', 'default.tpl');
		if ($tpl != 'none')
		{
	  $template = "../skins/$skin/$tpl";
	  // Recuperer le modele par defaut
	  if (!is_file($template))
	  $template = "../skins/$skin/home.tpl";

	  // Utiliser le modele
	  if (is_file($template))
	  {
	  	require_once "utils/uttemplate.php";
	  	$t = new uttemplate();
	  	$t->setFile($template);
	  	$res = $t->parse();
	  	print_r($res);
	  	exit;
	  }
		}
		$this->_displayFormInitDist($err);
	}
	// }}}

	// {{{ _displayFormInitDist()
	/**
	 * Display the standard login screen
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormInitDist($err='')
	{
		$dt = new UserBase();
		$ute = new utevent();

		$utpage = new utPage_V('cnx');
		$content =& $utpage->getContentDiv();

		$this->_rightColumn($content);
		$this->_leftColumn($content);
		$kdiv =& $content->addDiv('divContent');

		if ($err != '')
		$kdiv->addWng($err);

		// Display the event of the day
		$ties = $dt->getTodayTies();
		if (count($ties))
		{
	  $div =& $kdiv->addDiv('divDialog');
	  $div->addMsg('tToday');
	  $cells[] = 0;
	  foreach ($ties as $tie)
	  {
	  	$img = utimg::getPathPoster($tie['evnt_poster']);
	  	$size['maxWidth'] = 100;
	  	$size['maxHeight'] = 100;

	  	$lines = array();
	  	$lines[] = array('value' => $tie['evnt_name'],
	      		       'class' => 'lineName');
	  	$lines[] = array('value' => $tie['tie_place'],
	      		       'class' => 'linePlace',
	      		       'action' => array(KAF_UPLOAD, 'cnx', CNX_ANONYM, 
				    "&eventId={$tie['evnt_id']}&pageId=live"));
	  	$lines[] = array('value' => '',
	      		       'class' => 'lineImg',
	      		       'img'   => $img,
	      		       'imgSize'   => $size);

	  	$cells[] = $lines;
	  }
	  $row[] = $cells;
	  $krow = & $div->addRows("rowToday", $row);
	  $krow->displayTitle(false);
	  $krow->displaySelect(false);
	  $krow->displayNumber(false);
	   
	  $div->addDiv('break0', 'blkNewPage');
		}

		// Display the most visited
		$events = $ute->getLimitedEvents(-3);
		if (count($events))
		{
	  $div2 =& $kdiv->addDiv('divMostVisited', 'cartouche');
	  $div2->addMsg('tMostVisited', '', 'titre');

	  $blk =& $div2->addDiv('blkMostVisited', 'shortList');
	  foreach($events as $event)
	  {
	  	$name=$event['evnt_name']."&nbsp;(".$event['evnt_nbvisited'].")";
	  	$itms[$name] = array(KAF_UPLOAD, 'cnx', CNX_ANONYM,
				   "&eventId={$event['evnt_id']}");
	  }
	  $blk->addMenu('eventsVisited', $itms, -1);
		}

		// Display the most recent
		$events = $ute->getLimitedEvents(-4);
		if (count($events))
		{
	  $div2 =& $kdiv->addDiv('divMostRecent', 'cartouche');
	  $div2->addMsg('tLastCreated', '', 'titre');

	  $blk =& $div2->addDiv('blkMostRecent', 'shortList');
	  foreach($events as $event)
	  $itme[$event['evnt_name']] = array(KAF_UPLOAD, 'cnx',
	  CNX_ANONYM,
					       "&eventId={$event['evnt_id']}");
	  $blk->addMenu('eventsRecents', $itme, -1);
		}
		//$kdiv->addDiv('break', 'blkNewPage');

		// Display the list of team events
		$div =& $kdiv->addDiv('blkEventList2', 'cartouche');
		$div->addMsg('tEventList', '', 'titre');
		$blk =& $div->addDiv('blkEvent', 'liste');
		$rows = $ute->getEvents(WBS_EVENT_TEAM, WBS_LEVEL_INT);
		if (isset($rows['errMsg']))
		$blk->addWng($rows['errMsg']);
		else
		{
	  if (count($rows))
	  {
	  	$its=array();
	  	$blk->addMsg('tEventInt', '', 'stitre');
	  	foreach($rows as $id=>$login)
	  	$its[$login] = array(KAF_UPLOAD, 'cnx',
	  	CNX_ANONYM, "&eventId=$id");
	  	$blk->addMenu('eventsInt', $its, -1);
	  }
		}
		$rows = $ute->getEvents(WBS_EVENT_TEAM, WBS_LEVEL_NAT);
		if (isset($rows['errMsg']))
		$blk->addWng($rows['errMsg']);
		else
		{
	  if (count($rows))
	  {
	  	$its=array();
	  	$blk->addMsg('tEventNat', '', 'stitre');
	  	foreach($rows as $id=>$login)
	  	$its[$login] = array(KAF_UPLOAD, 'cnx',
	  	CNX_ANONYM, "&eventId=$id");
	  	$blk->addMenu('eventsNat', $its, -1);
	  }
		}
		$rows = $ute->getEvents(WBS_EVENT_TEAM, WBS_LEVEL_REG);
		if (isset($rows['errMsg']))
		$blk->addWng($rows['errMsg']);
		else
		{
	  if (count($rows))
	  {
	  	$its=array();
	  	$blk->addMsg('tEventReg', '', 'stitre');
	  	foreach($rows as $id=>$login)
	  	$its[$login] = array(KAF_UPLOAD, 'cnx',
	  	CNX_ANONYM, "&eventId=$id");
	  	$blk->addMenu('eventsReg', $its, -1);
	  }
		}
		$rows = $ute->getEvents(WBS_EVENT_TEAM, WBS_LEVEL_DEP);
		if (isset($rows['errMsg']))
		$blk->addWng($rows['errMsg']);
		else
		{
	  if (count($rows))
	  {
	  	$its=array();
	  	$blk->addMsg('tEventDep', '', 'stitre');
	  	foreach($rows as $id=>$login)
	  	$its[$login] = array(KAF_UPLOAD, 'cnx',
	  	CNX_ANONYM, "&eventId=$id");
	  	$blk->addMenu('eventsDep', $its, -1);
	  }
		}


		// Display the list of individuals events
		$div =& $kdiv->addDiv('blkIndiEventList3', 'cartouche');
		$div->addMsg('tIndiEventList', '', 'titre');
		$blk =& $div->addDiv('blkIndiEvent', 'liste');
		$rows = $ute->getEvents(WBS_EVENT_INDIVIDUAL, WBS_LEVEL_INT);
		if (isset($rows['errMsg']))
		$blk->addWng($rows['errMsg']);
		else
		{
	  if (count($rows))
	  {
	  	$its=array();
	  	$blk->addMsg('tEventIndiInt', '', 'stitre');
	  	foreach($rows as $id=>$login)
	  	$its[$login] = array(KAF_UPLOAD, 'cnx',
	  	CNX_ANONYM, "&eventId=$id");
	  	$blk->addMenu('eventsIndiInt', $its, -1);
	  }
		}
		$rows = $ute->getEvents(WBS_EVENT_INDIVIDUAL, WBS_LEVEL_NAT);
		if (isset($rows['errMsg']))
		$blk->addWng($rows['errMsg']);
		else
		{
	  if (count($rows))
	  {
	  	$its=array();
	  	$blk->addMsg('tEventIndiNat', '', 'stitre');
	  	foreach($rows as $id=>$login)
	  	$its[$login] = array(KAF_UPLOAD, 'cnx',
	  	CNX_ANONYM, "&eventId=$id");
	  	$blk->addMenu('eventsIndiNat', $its, -1);
	  }
		}
		$rows = $ute->getEvents(WBS_EVENT_INDIVIDUAL, WBS_LEVEL_REG);
		if (isset($rows['errMsg']))
		$blk->addWng($rows['errMsg']);
		else
		{
	  if (count($rows))
	  {
	  	$its=array();
	  	$blk->addMsg('tEventIndiReg', '', 'stitre');
	  	foreach($rows as $id=>$login)
	  	$its[$login] = array(KAF_UPLOAD, 'cnx',
	  	CNX_ANONYM, "&eventId=$id");
	  	$blk->addMenu('eventsIndiReg', $its, -1);
	  }
		}
		$rows = $ute->getEvents(WBS_EVENT_INDIVIDUAL, WBS_LEVEL_DEP);
		if (isset($rows['errMsg']))
		$blk->addWng($rows['errMsg']);
		else
		{
	  if (count($rows))
	  {
	  	$its=array();
	  	$blk->addMsg('tEventIndiDep', '', 'stitre');
	  	foreach($rows as $id=>$login)
	  	$its[$login] = array(KAF_UPLOAD, 'cnx',
	  	CNX_ANONYM, "&eventId=$id");
	  	$blk->addMenu('eventsIndiDep', $its, -1);
	  }
		}

		$kdiv->addDiv('breakFoot', 'blkNewPage');
		$utpage->display();
		exit();
	}

	// }}}

	// {{{ _displayFormNewUser()
	/**
	 * Display the page to get information about
	 * a new user
	 *
	 * @access private
	 * @param array $infos  attribs of the new user
	 * @return void
	 */
	function _displayFormNewUser($infos="")
	{
		$utpage = new utPage_V('cnx');
		$content =& $utpage->getContentDiv();
		$this->_rightColumn($content);
		$this->_leftColumn($content);
		$kdiv =& $content->addDiv('divDialog');

		if ($infos == '')
		$infos = array( "user_name"  => '',
			"user_login" => '',
			"user_pseudo" => '',
			"user_pass"  => '', 
			"user_email" => '', 
			"user_lang"  => '');
		if (isset($infos['errMsg']))
		{
	  $content->addWng($infos['errMsg']);
		}

		$form =& $kdiv->addForm('fNewUser', 'cnx', CNX_CREATEUSER);
		$kedit =& $form->addEdit("userName",     $infos['user_name']);
		$kedit->setMaxLength(50);
		$kedit =& $form->addEdit("userPseudo",     $infos['user_pseudo']);
		$kedit->setMaxLength(20);
		$kedit =& $form->addEdit("userLogin", $infos['user_login']);
		$kedit->setMaxLength(20);
		$kedit =& $form->addPwd("userPass",  $infos['user_pass']);
		$kedit->setMaxLength("userPass", 15 );
		$kedit =& $form->addEdit("userEmail",    $infos['user_email']);
		$kedit->setMaxLength(50);
		$kedit->noMandatory("userEmail");
		$handle=opendir('./lang');
		while ($file = readdir($handle))
		{
			if ($file != "." && $file != "..")
			$language[$file] = $file;
		}
		closedir($handle);
		$form->addCombo("userLang", $language, $infos['user_lang'] );

		$elts = array("userName", "userPseudo", "userLogin", "userPass",
		    "userEmail", "userLang");
		$form->addBlock("blkNewUser", $elts);


		$form->addBtn("btnRegister", KAF_SUBMIT);
		$form->addBtn("btnCancel",  KAF_UPLOAD, 'users', KID_DEFAULT);
		$elts = array("btnRegister", "btnCancel");
		$form->addBlock("blkBtn", $elts);
		$utpage->display();
		exit();
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
		$dt = new UserBase();

		$utpage = new utPage_V('cnx');
		$content =& $utpage->getContentDiv();
		$this->_rightColumn($content);
		$this->_leftColumn($content);
		$kdiv =& $content->addDiv('divDialog');

		$form =& $kdiv->addForm('femail');

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
		$form->addBtn('btnSend', KAF_UPLOAD, 'cnx', CNX_SENDEMAIL);
		$form->addBtn('btnCancel',  KAF_UPLOAD, 'users', KID_DEFAULT);
		$elts = array('btnSend', 'btnCancel');
		$form->addBlock("blkBtn", $elts);
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _rightColumn()
	/**
	 * Add data bloc into the right column
	 */
	function _rightColumn(& $content)
	{
		$dt = new UserBase();

		// Display the news
		$news = $dt->getNews();
		$rightCol =& $content->addDiv('rightCol');

		if (count($news))
		{
	  $div =& $rightCol->addDiv('divBreves');
	  $div->addMsg('tNews', '', 'titre');

	  $blk =& $div->addDiv('blkBreves', 'liste');
	  $i=0;
	  foreach ($news as $new)
	  {
	  	//$kdiv =& $blk->addDiv("news$i");
	  	$blk->addMsg("date$i", $new['news_cre'], 'stitre');
	  	$kmsg =& $blk->addWng("evnt$i", $new['evnt_name']);
	  	$blk->addMsg("brief$i", $new['news_text']);
	  	if (utvars::_getSessionVar('userAuth') == WBS_AUTH_USER)
	  	$act[1] = array(KAF_UPLOAD, 'cnx', WBS_ACT_SELECT_EVENT,
				//"{$new['evnt_id']};{$new['news_page']}");
		    "&eventId={$new['evnt_id']}&pageId={$new['news_page']}");
	  	else
	  	$act[1] = array(KAF_UPLOAD, 'cnx', CNX_ANONYM,
		    "&eventId={$new['evnt_id']}&pageId={$new['news_page']}");
	  	//	"{$new['evnt_id']};{$new['news_page']}");
	  	$kmsg->setActions($act);
	  	$i++;
	  }
		}

		// Display the next ties
		$ties = $dt->getNextTies();
		if (count($ties))
		{
	  $div =& $rightCol->addDiv('divNextTies');
	  $div->addMsg('tNextTies', '', 'titre');

	  $blk =& $div->addDiv('blkNextTies', 'liste');
	  $i=0;
	  foreach ($ties as $tie)
	  {
	  	//$kdiv =& $blk->addDiv("news$i");
	  	$blk->addMsg("dateTie$i", $tie['tie_schedule'], 'stitre');
	  	$kwng =& $blk->addWng("evnt$i", $tie['evnt_name']);
	  	$kmsg =& $blk->addMsg("briefTie$i", $tie['tie_place']);
	  	$act[1] = array(KAF_UPLOAD, 'cnx', CNX_ANONYM,
			    "&eventId={$tie['evnt_id']}&pageId=ties");
	  	$kwng->setActions($act);
	  	$i++;
	  }
		}
		return;
	}
	// }}}


	// {{{ _leftColumn()
	/**
	 * Add an element to the left column
	 *
	 * @access public
	 * @param object $form   pointer to the form
	 * @param string $elt    element to add to the form
	 * @return string name of the form
	 */
	function _leftColumn(&$content)
	{
		$utev = new utEvent();

		$leftCol =& $content->addDiv('leftCol');

		$div =& $leftCol->addDiv('divDate');
		$utd = new utdate();
		$date = $utd->getDate();
		$div->addMsg("date", $date);

		$handle=opendir('./lang');
		while ($file = readdir($handle))
		{
			if ($file != '.' && $file != '..' && $file != 'CVS')
			$itsl[$file] = array(KAF_UPLOAD, 'cnx',
			CNX_NEWLANG, $file);
		}
		closedir($handle);
		$kmenu =& $div->addMenu('lang', $itsl, -1);
		$kmenu->setImgPath('img/menu/');
		$kmenu->setDisplay(KMN_DISPLAY_IMG);

		$div =& $leftCol->addDiv('divConnexion');
		$form =& $div->addForm('formLogin', 'cnx', CNX_VALIDLOG);
		$form->addEdit("username", "", 13);
		$form->setMaxLength("username", 20);
		$form->addPwd("password", "", 13);
		$form->setMaxLength("password", 15 );
		//$form->addBlock('blkBtn', 'btnConnect');
		//$elts = array('username', 'password', 'blkBtn');
		$elts = array('username', 'password', 'blkBtn');
		$form->addBlock('blkLogin', $elts);
		$form->addBtn("btnConnect", KAF_SUBMIT);

		$kmsg =& $div->addMsg('btnNew');
		$actions[1] = array(KAF_UPLOAD, 'users', CNX_NEWUSER);
		$kmsg->setActions($actions);

		$kmsg =& $form->addMsg('btnLoose');
		$actions[1] = array(KAF_UPLOAD, 'cnx', CNX_LOOSEPWD);
		$kmsg->setActions($actions);


		// Display  the list of administrators
		$administrators = $utev->getAdministrators();
		if (count($administrators))
		{
	  $kdiv =& $leftCol->addDiv('divAdminList');
	  $kdiv->addMsg('tAdminList', '', 'titre');
	  $kdiv->addMsg('msgAskEvent');
	  foreach($administrators as $administrator)
	  $its[$administrator['user_pseudo']] = array(KAF_UPLOAD, 'cnx',
	  WBS_ACT_EMAIL, $administrator['user_id']);
	  $kdiv->addMenu("ctactAdmin", $its, -1);
		}

		// Display users stats
		$dt = new UserBase();
		$stats = $dt->getUsersStats();

		$kdiv =& $leftCol->addDiv('divUsersStat');
		$kdiv->addMsg('tUsersStat', '', 'titre');
		$kdiv->addInfo('nbUsers', $stats['nbUsers']);
		$kdiv->addInfo('lastUser', $stats['lastUser']);
		$kdiv->addInfo('nbSubscribe', $stats['nbSubscribe']);

		// Display events stats
		$stats = $dt->getEventsStats();

		$kdiv =& $leftCol->addDiv('divEventsStat');
		$kdiv->addMsg('tEventsStat', '', 'titre');
		$kdiv->addInfo('nbEvents', $stats['nbEvents']);
		$kdiv->addInfo('nbTeams', $stats['nbTeams']);
		$kdiv->addInfo('nbMatchs', $stats['nbMatches']);
		$kdiv->addInfo('nbPlayers', $stats['nbPlayers']);

		// Display visits stats
		$stats = $dt->getVisits();

		$kdiv =& $leftCol->addDiv('divVisitsStat');
		$kdiv->addMsg('tVisitsStat', '', 'titre');
		$kdiv->addInfo('nbAnonymousVisit', $stats['nbAnonymous']);
		$kdiv->addInfo('nbUsersVisit', $stats['nbUsers']);
		$kdiv->addInfo('nbAdminVisit', $stats['nbAdministrators']);
	}
	// }}}

	// {{{ _newPwd()
	/**
	 * Create a new password and send it to the user
	 *
	 * @private
	 * @return string login of the new user
	 */
	function _newPwd()
	{
		$ut = new Utils();
		$dt = new UserBase_V();

		$info = kform::getInput("userInfo");
		if ( empty($info) )
		return "msgInvalidData";
		require_once "utils/utmail.php";
		$utm = new utmail();
		$users = $dt->getUserFromEmail($info);
		//if (!count($users))
		//$users = $dt->getUserFromLogin($info);
		if (!count($users))
		return "msgInvalidData";

		require_once "utils/Password.php";
		$pwd = Text_Password::create();

		foreach($users as $user)
		{
	  $subject = "[BadNet]Mot de passe";
	  $utm->subject($subject);
	  $utm->organization('Badnet');
	  $from = "no-reply@badnet.org";
	  $utm->from($from);
	  $body = "Votre login:".$user['user_login']."\n";
	  $body .= "Votre mot de passe:$pwd";
	  $utm->body( $body);

	  // Send the message
	  $res =  $utm->send($user['user_email']);
	  if (PEAR::isError($res))
	  {
	  	echo $res->getMessage();
	  	return 'msgPwdNotSend';
	  }
	  else
	  {
	  	$user['user_pass'] = md5($pwd);
	  	$dt->updateUser($user);
	  }
		}
		return 'msgPwdSend';
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
		$dt = new UserBase();

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

	// {{{ _createUser()
	/**
	 * Update the user in the database
	 *
	 * @private
	 * @return string login of the new user
	 */
	function _createUser()
	{
		$dt = new UserBase();

		$infos = array('user_name'   =>kform::getInput("userName"),
                     'user_pseudo' =>kform::getInput("userPseudo"),
                     'user_email'  =>kform::getInput("userEmail"),
                     'user_login'  =>kform::getInput("userLogin"),
                     'user_pass'   =>kform::getInput("userPass"),
                     'user_type'   => WBS_AUTH_USER,
                     'user_id'     => -1,
                     'user_lang'   =>kform::getInput("userLang"),
                     'user_cmt'    =>'Cr�ation par login');


		if ($infos['user_name'] == "")
		{
	  $infos['errMsg'] = 'msg_user_name';
	  $this->_displayFormNewUser($infos);
		}

		if ($infos['user_login'] == "")
		{
	  $infos['errMsg'] = 'msg_user_login';
	  $this->_displayFormNewUser($infos);
		}

		if ($infos['user_pass'] == "")
		{
	  $infos['errMsg'] = 'msg_user_pass';
	  $this->_displayFormNewUser($infos);
		}

		if ($dt->existLogin($infos['user_login'], -1))
		{
	  $infos['errMsg'] = 'msgUserExist';
	  $this->_displayFormNewUser($infos);
		}

		$res = $dt->updateUser($infos);
		if (is_array($res))
		{
			$infos['errMsg'] = $res['errMsg'];
			$this->_displayFormNewUser($infos);
		}
		else
		{
	  return $infos['user_login'];
		}
	}
	// }}}

}

?>