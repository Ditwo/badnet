<?php
/*****************************************************************************
 !   Module     : events
 !   File       : $Source: /cvsroot/aotb/badnet/src/events/events_V.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.21 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:25 $
 ******************************************************************************/
require_once "events.inc";
require_once "base_V.php";
require_once "utils/utdate.php";
require_once "utils/utimg.php";
require_once "subscript/subscript.inc";

/**
 * Module de gestion des tournois : classe visiteurs
 *
 * @author Gerard CANTEGRIL
 * @see to follow
 *
 */

class Events_V
{

	// {{{ properties

	/**
	 * Utils objet
	 *
	 * @var     object
	 * @access  private
	 */
	var $_ut;

	/**
	 * Database access object
	 *
	 * @var     object
	 * @access  private
	 */
	var $_dt;

	// }}}

	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function Events_V()
	{
		$this->_ut = new Utils();
		$this->_dt = new EventBase_V();
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
	  // Display main windows for the event
			case KID_SELECT:
				$this->_displayFormMainEvent();
				break;
			case WBS_ACT_EVENTS:
				$this->_displayFormEventList();
				break;
			case EVNT_STATS:
				$this->_displayStatsEvent();
				break;
			case EVNT_SUBSCRIBE:
				$this->_displaySubscriptEvent();
				break;
			case EVNT_SEND_EMAIL:
				$this->_sendEmail();
				break;
			case EVNT_CONTACTS:
				$this->_displayContactEvent();
				break;
			case EVNT_GET_LIST:
				$this->_sendXmlListEvents();
				break;
			default:
				echo "page $page demand�e depuis events_V<br>";
				exit();
		}
	}
	// }}}

	// {{{ _sendEmail()
	/**
	 * Send an email to the seect manager
	 * for a news about the event
	 *
	 * @access private
	 * @param array $event  attribs of the new event
	 * @return void
	 */
	function _sendEmail()
	{
		$dt = $this->_dt;

		// Prepare mailer
		require_once "utils/utmail.php";
		$mailer = new utmail();

		// Prepare cc
		if (utvars::getUserId() === -1)
		$toCc = kform::getInput('fromEmail');
		else
		{
	  $user = $dt->getUser(utvars::getUserId());
	  if (!isset($user['user_email']) || $user['user_email'] != '')
	  $toCc = $user['user_email'];
	  else
	  $toCc = '';
		}
		$mailer->subject(kform::getInput('subject'));
		$mailer->from(kform::getInput("from"));
		$mailer->body(kform::getInput('message'));
		$mailer->cc($toCc);

		// Send the message
		$user = $dt->getUser(kform::getInput('to'));
		if (!isset($user['user_email']) || $user['user_email'] == '')
		$this->_displayContactEvent('msgNoDest');
		$res =  $mailer->send($user['user_email']);
		if (PEAR::isError($res))
		{
	  $this->_displayContactEvent($res->getMessage());
		}
		$this->_displayContactEvent();
	}
	// }}}


	// {{{ _sendListEvents()
	/**
	 * Register the new
	 * for a news about the event
	 *
	 * @access private
	 * @param array $event  attribs of the new event
	 * @return void
	 */
	function _sendXmlListEvents()
	{
		$ut = new Utils();
		$dt = $this->_dt;
		$events = $dt->getEvents(utvars::getUserId());
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<badnet";
		echo " version=\"".$ut->getParam('version')."\"";
		$dataBaseId = $ut->getParam('databaseId', -1);
		if ($dataBaseId == -1)
		{
	  $dataBaseId = gmmktime();
	  $ut->setParam('databaseId', $dataBaseId);
		}
		$date = date(DATE_FMT);
		echo " date=\"$date\"\n";
		echo " database=\"{$dataBaseId}\">\n";
		foreach($events as $event)
		{
	  if ($event['evnt_right']==WBS_AUTH_MANAGER)
	  {
	  	echo "<event evnt_id=\"{$event['evnt_id']}\"";
	  	echo " evnt_name=\"{$event['evnt_name']}\">";
	  	echo "</event>\n";
	  }
		}
		echo "</badnet>";
		exit;
	}
	// }}}


	// {{{ _displayContactEvent()
	/**
	 * Create the form to contact a manager
	 *
	 * @access private
	 * @return void
	 */
	function _displayContactEvent($err='')
	{
		$dt = $this->_dt;
		 
		// Create a new page
		$div =& $this->_displayHead('itContacts');

		// Retrieve the contacts
		$utev = new utEvent();
		$contacts = $utev->getManagers();
		foreach($contacts as $contact)
		$its[$contact['user_id']] = $contact['user_pseudo'];
		if(!isset($its))
		{
	  $div->addWng('msgNoContact');
	  $this->_utpage->display();
	  exit;
		}

		$form =& $div->addForm('femail', 'events', EVNT_SEND_EMAIL);

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
		$subject    = kform::getInput("subject");
		$body       = kform::getInput("message");
		$fromEmail  = kform::getInput("fromEmail");

		// Display error message
		if ($err!='') $form->addWng($err);

		// Display fields for email
		if ($senderId != "")
		$form->addinfo("from", $infosSender['user_name']);
		else
		$form->addEdit("from", $infosSender['user_name'], 45);

		if ($infosSender["user_email"] == "" ||
		!strstr($infosSender["user_email"], '@'))
		$form->addEdit("fromEmail", $fromEmail, 45);

		$form->addCombo("to",      $its);
		$form->addEdit("subject", $subject, 45);
		$form->addArea("message", $body, 31, 8);
		$elts=array("from", "fromEmail", "to", "subject", "message");
		$form->addBlock("blkMail", $elts);

		// add bouton to send the mail
		$form->addDiv('breakBtn', 'blkNewPage');
		$form->addBtn("btnMail", KAF_SUBMIT);
		$elts = array("btnMail");
		$form->addBlock("blkBtn", $elts);
		$this->_utpage->display();
		exit;
	}
	// }}}
function getSeasons($param) 
{
  $select = $param['select'];
  $link   = $param['link'];

  if ($select <= 0)
    {
      $date = getdate();
      $select = $date['year']-2006;
      if ($date['mon'] >= 8) $select++;
    }

  $year = 2005;
  for($i=1; $i<6; $i++)
    {
      $start = $year + $i;
      $end   = $year + 1 + $i;
      $sea = array('value' => "{$link}{$i}",
		   'text'  => "{$start}-{$end}");
      if($select == $i)
	$sea['selected'] = 'selected';
      $seas[] = $sea;
    }
  return $seas;
}
	
	// {{{ _displayFormEventList()
	/**
	 * Create the form to display the list of events
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormEventList()
	{
		$ut = new Utils();
		$dt = $this->_dt;
		utvars::setSessionVar('theme', WBS_THEME_NONE);
		utvars::setEventId(-1);
		if ( $ut->getParam('limitedUsage') )
		{
			$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']
			. '?bnAction=656384';//196357';
			header("Location: $url");
		}
		
		
		$utPage = new utPage_V('events', true, 1);
		$content =& $utPage->getContentDiv();

      $date = getdate();
      $curSeason = $date['year']-2006;
      if ($date['mon'] >= 8) $curSeason++;

		// Display  the list of published events
		$rows = $dt->getEvents(utvars::getUserId(), $curSeason);
		if (isset($rows['errMsg']))
		{
	  $content->addWng($rows['errMsg']);
	  unset($rows['errMsg']);
		}

		if (count($rows))
		{
	  $type='';
	  foreach($rows as $event)
	  {
	  	if($type != $event['evnt_level'])
	  	{
	  		$type = $event['evnt_level'];
	  		$events[]= array(KOD_BREAK, "title", $ut->getLabel($type));
	  	}
	  	$events[]=$event;
	  }

	  $krow =& $content->addRows("rowsEvents", $events);
	  $krow->displaySelect(false);
	  $krow->setSort(0);
	  $sizes[4] = '0+';
	  $krow->setSize($sizes);
	   
	  $img[1]='publiIcon';
	  $img[2]='publiRight';
	  $krow->setLogo($img);
	   
	  $actions[1] = array(KAF_UPLOAD, 'cnx',
	  WBS_ACT_SELECT_EVENT);
	  $krow->setActions($actions);
		}


		// Display the legende
		$kdiv1 = &$content->addDiv('lgdRight', 'classLegende');
		$kdiv1->addImg('lgdManager', utimg::getIcon(WBS_AUTH_MANAGER));
		$kdiv1->addImg('lgdAssist', utimg::getIcon(WBS_AUTH_ASSISTANT));
		$kdiv1->addImg('lgdFriend', utimg::getIcon(WBS_AUTH_FRIEND));
		$kdiv1->addImg('lgdGuest', utimg::getIcon(WBS_AUTH_GUEST));
		$kdiv1->addImg('lgdVisitor', utimg::getIcon(WBS_AUTH_VISITOR));

		$kdiv = &$content->addDiv('lgdPubli', 'blkLegende');
		$kdiv->addImg('lgdConfident', utimg::getIcon(WBS_DATA_CONFIDENT));
		$kdiv->addImg('lgdPrivate', utimg::getIcon(WBS_DATA_PRIVATE));
		$kdiv->addImg('lgdPublic', utimg::getIcon(WBS_DATA_PUBLIC));

		$utPage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormMainEvent()
	/**
	 * Display the main page for the event
	 *
	 * @access private
	 * @param array $eventId  id of the event
	 * @return void
	 */
	function _displayFormMainEvent()
	{
		$dt = $this->_dt;
		 
		// Create a new page
		$div =& $this->_displayHead('itPractical');

		// Display the news
		$news = $dt->getNews();
		$rightCol =& $div->addDiv('rightCol');
		if (count($news))
		{
	  $kdiv =& $rightCol->addDiv('divBreves');
	  $kdiv->addMsg('tNews', '', 'titre');

	  $blk =& $kdiv->addDiv('blkBreves', 'liste');
	  $i=0;
	  foreach ($news as $new)
	  {
	  	$kdiv =& $blk->addDiv("news$i", 'breve');
	  	$kdiv->addMsg("date$i", $new['news_cre'], 'stitre');
	  	$kdiv->addMsg("brief$i", $new['news_text']);
	  	$i++;
	  }
		}

		// Get the data of the event
		$ute = new utevent();
		$eventId = utvars::getEventId();
		$infos = $ute->getEvent($eventId);
		$kdiv =& $div->addDiv('infos', 'blkInfo');
		$kdiv->addMsg('tInfoGene', '', 'titre');
		if (isset($infos['errMsg']))
		$kdiv->addWng($infos['errMsg']);

		$kdiv->addInfo("evntDate",      $infos['evnt_date']);
		$kdiv->addInfo("evntPlace",     $infos['evnt_place']);
		$kdiv->addInfo("evntOrganizer", $infos['evnt_organizer']);
		if (utvars::isTeamEvent())
		{
	  $teams = $dt->getTeamsList();
		}
		else
		{
	  $utd = new utdate();
	  $utd->setIsoDate($infos['evnt_deadline']);
	  $kdiv->addInfo("evntDeadline",  $utd->getDate());
	  $utd->setIsoDate($infos['evnt_datedraw']);
	  $kdiv->addInfo("evntDatedraw",  $utd->getDate());
	  $teams = $dt->getAssosList();
		}
		if ($infos['evnt_url'] != "")
		{
	  $kinfo =& $kdiv->addInfo('evntUrl', $infos['evnt_url']);
	  $kinfo->setUrl($infos['evnt_url']);
		}


		// Acces a l'inscription en ligne
		//if(utvars::isLineRegi())
		if(0)
		{
	  $kdiv =& $div->addDiv('lineRegistration', 'blkInfo');
	  $kdiv->addMsg('tLineRegi', '', 'titre');
	  $form =& $kdiv->addForm('fLineRegistration');
	  $form->setAction("http://www.badnet.org/badnet30/src/index.php");
	  $form->addHide('event', utvars::getEventId());
	  $form->addHide('mod', 'asso');
	  $form->addHide('kpid', 'adhr');
	  $form->addHide('kaid', 2000);
	  $kedit =& $form->addEdit('login','', 15);
	  $kedit =& $form->addPwd('pwd', '', 15);
	  $form->addBtn('btnLogin', KAF_SUBMIT);
	  $msg =& $kdiv->addInfo('msgNoAccess', 'CIci');
	  $url = "http://www.badnet.org/badnet30/src/index.php";
	  $url .= "?mod=public&kpid=adhe&kaid=1001&event="
	  .utvars::getEventId();
	  $msg->setUrl($url);
		}
		// Liste des joueurs pour un acces direct
		$players = $dt->getPlayersList();
		if (count($players) ||( count($teams) && !isset($teams['errMsg'])))
		{
	  $kdiv =& $div->addDiv('directLink', 'blkInfo');
	  $kdiv->addMsg('tDirectAccess', '', 'titre');
	  if (count($players))
	  {
	  	$players[-1] = '---';
	  	$kcombo =& $kdiv->addCombo('playersList', $players, $players[-1]);
	  	$acts[1] = array(KAF_UPLOAD, 'resu', KID_SELECT);
	  	$kcombo->setActions($acts);
	  }
	  if (count($teams))
	  {
	  	$teams[-1] = '---';
	  	$kcombo=& $kdiv->addCombo('teamsList', $teams, $teams[-1]);
	  	$acts[1] = array(KAF_UPLOAD, 'teams', KID_SELECT);
	  	$kcombo->setActions($acts);
	  }
		}


		if (!empty($infos['evnt_poster']))
		{
	  $img = utimg::getPathPoster($infos['evnt_poster']);
	  $size['maxWidth'] = 400;
	  $kdiv =& $div->addDiv('infosPoster', 'blkInfo');
	  $kdiv->addImg('poster', $img, $size);
	  $kdiv->addDiv('break', 'blkNewPage');
		}
		$div->addDiv('break2', 'blkNewPage');
		//Display the form
		$cache = "events_".KID_SELECT."_{$eventId}.htm";
		$this->_utpage->display($cache);
		exit;
	}
	// }}}

	// {{{ _displayStatEvent()
	/**
	 * Display the statistical page for the event
	 *
	 * @access private
	 * @param array $eventId  id of the event
	 * @return void
	 */
	function _displayStatsEvent()
	{
		$dt = $this->_dt;
		 
		// Create a new page
		$div =& $this->_displayHead('itStats');

		// Get the data of the event
		$ute = new utevent();
		$eventId = utvars::getEventId();
		$infos = $ute->getEvent($eventId);
		if (isset($infos['errMsg']))
		$kdiv->addWng($infos['errMsg']);

		$stats = $dt->getEventStats();

		$kdiv =& $div->addDiv('divEventsStat', 'blkInfo');
		$kdiv->addMsg('tCompetStat', '', 'titre');
		$kdiv->addInfo('nbTeams', $stats['nbTeams']);
		$kdiv->addInfo('nbPlayers', $stats['nbPlayers']);
		$kdiv->addInfo('nbMatchs', $stats['nbMatches']);

		$kdiv =& $div->addDiv('divVisitsStat', 'blkInfo');
		$kdiv->addMsg('tVisitsStat', '', 'titre');
		$kdiv->addInfo("nbVisite", $infos['evnt_nbvisited']);
		$kdiv->addInfo("nbSubscribe", $stats['nbSubscribe']);

		$div->addDiv('break', 'blkNewPage');

		//Display the form
		$cache = "events_".EVNT_STATS."_{$eventId}.htm";
		$this->_utpage->display($cache);
		exit;
	}
	// }}}


	// {{{ _displaySubscriptEvent()
	/**
	 * Display a page with the list of the teams
	 *
	 * @access private
	 * @return void
	 */
	function _displaySubscriptEvent()
	{
		$utev = new utEvent();

		$eventId = utvars::getEventId();
		if ($utev->getEventType($eventId)== WBS_EVENT_INDIVIDUAL)
		//$this->_displaySubscribeIndiv();
		$this->_displaySubscriptTeam();
		else
		$this->_displaySubscriptTeam();
	}
	// }}}

	// {{{ _displaySubscriptTeam()
	/**
	 * Display a page with the list of the teams
	 *
	 * @access private
	 * @return void
	 */
	function _displaySubscriptTeam($err='')
	{
		$dt = $this->_dt;

		$action = kform::getData();

		$content =& $this->_displayHead('itSubscribe');

		$content->addWng('msgSubscription');

		$form =& $content->addForm('fsubscript', 'events', SUBS_TEAM_REG);

		if ($err != '')
		$form->addWng($err);

		// Get user's data
		$userId = utvars::getUserId();
		if ( !empty($userId) )	$user = $dt->getUser($userId);
		else
		{
			$user =array('user_email' => 'info@badnet.org',
			'user_login' => 'demo');
		}
		$email = kform::getInput('email');
		if ($email == '') $email = $user['user_email'];

		// Add the list of the teams
		$sort = $form->getSort('rowsTeams', 2);
		if (utvars::isTeamEvent()) $rows = $dt->getTeams($sort);
		else $rows = $dt->getAssos($sort);
		$subs = $dt->getSubscripts($email, $userId);
		if (isset($rows['errMsg'])) 	$form->addWng($rows['errMsg']);
		else
		{
	  $krow =& $form->addRows('rowsTeams', $rows);
	  $krow->setSelect($subs);
	  $sizes[3] = "0+";
	  $krow->setSize($sizes);
		}
		// Add field for email
		$kedit =& $form->addEdit('email', $email);
		$kedit->setMaxLength(50);
		$form->addCheck('news', $dt->isNewsSubs($email, $userId));
		$elts = array('email', 'news');
		$form->addBlock('blkNews', $elts);

		// Add command bouton
		$form->addBtn('btnSubscribe', KAF_NEWWIN, 'subscript',
		SUBS_TEAM_REG, 0, 300, 200);
		$form->addBtn('btnUnsubscribe', KAF_NEWWIN, 'subscript',
		SUBS_DEL_REG, 0, 300, 200);
		if ($user['user_login']==='demo') $form->addBtn('btnInfo', KAF_UPLOAD, 'events',	EVNT_SUBSCRIBE);
		$elts = array('btnSubscribe', 'btnUnsubscribe', 'btnInfo');
		$form->addDiv('breakBtn', 'blkNewPage');
		$form->addBlock("blkBtn", $elts, 'blkBtn');

		$content->addDiv('break', 'blkNewPage');

		$this->_utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayHead()
	/**
	 * Display header of the page
	 *
	 * @access private
	 * @param array team  info of the team
	 * @return void
	 */
	function & _displayHead($select)
	{
		$dt = $this->_dt;

		// Create a new page
		$this->_utpage = new utPage_V('events', true, 'itGeneral');
		$content =& $this->_utpage->getContentDiv();

		// Add a menu for different action
		$kdiv =& $content->addDiv('choix', 'onglet3');
		$items['itPractical']   = array(KAF_UPLOAD, 'events', KID_SELECT);
		$items['itStats']       = array(KAF_UPLOAD, 'events', EVNT_STATS);
		$items['itSubscribe']   = array(KAF_UPLOAD, 'events', EVNT_SUBSCRIBE);
		$items['itContacts']    = array(KAF_UPLOAD, 'events', EVNT_CONTACTS);
		$kdiv->addMenu("menuType", $items, $select);
		$kdiv->addDiv('breakOnglet3', 'blkNewPage');
		$kdiv =& $content->addDiv('register', 'cont3');
		$kdiv->addDiv('headCont3', 'headCont3');
		$kdiv =& $kdiv->addDiv('corp3', 'corpCont3');
		return $kdiv;
	}
	// }}}

}

?>