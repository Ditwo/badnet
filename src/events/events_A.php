<?php
/*****************************************************************************
 !   Module    :  Events
 !   File       : $Source: /cvsroot/aotb/badnet/src/events/events_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.34 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/06 07:59:33 $
 ******************************************************************************/
require_once "events.inc";
require_once "base_A.php";
require_once "utils/utdate.php";
require_once "utils/utpage_A.php";
require_once "utils/utfile.php";


/**
 * Module de gestion des tournois : classe administrateurs
 *
 * @author Gerard CANTEGRIL <cage@aotb.org>
 * @see to follow
 *
 */

class Events_A
{

	/**
	 * Utils objet
	 *
	 * @private
	 */
	var $_ut;

	/**
	 * Database access object
	 *
	 * @private
	 */
	var $_dt;


	/**
	 * Constructor.
	 *
	 * @private
	 * @return void
	 */
	function Events_A()
	{
		$this->_ut = new Utils();
		$this->_dt = new EventBase_A();
	}

	/**
	 * Start the connexion processus
	 *
	 */
	function start($page)
	{
		switch ($page)
		{
			case WBS_ACT_EVENTS:
				$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']
				. '?bnAction=196608';
				header("Location: $url");
				exit();
				//$this->_displayFormEventList();
				break;

			case KID_SELECT:
				$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']
				. '?bnAction=196609&eventId='.utvars::getEventId();
				header("Location: $url");
				exit();
				break;

			case EVNT_MAIN:
				$this->_displayFormMainEvent();
				break;

			case EVNT_PRINT_OPTIONS:
				$this->_displayFormPrintOptions();
				break;
					
			case EVNT_SELECT_IMAGE:
				utimg::selectPoster('events', kform::getData());
				break;

			case WBS_SECTOR_SPORT:
				utvars::setSectorId(WBS_SECTOR_SPORT);
				$this->_displayFormMainEvent();
				break;

				// Change the publication status of selected events
			case KID_CONFIDENT:
				$this->_publiEvent(WBS_DATA_CONFIDENT);
				break;
			case KID_PUBLISHED:
				$this->_publiEvent(WBS_DATA_PUBLIC);
				break;

				// Activation ou non de l'inscription en ligne
	  case EVNT_INLINE:
	  	$this->_inlineEvent(160);
	  	break;
	  case EVNT_OFFLINE:
	  	$this->_inlineEvent(161);
	  	break;
	  	 
	  	// Modification of event
	  case EVNT_SELECT_POSTER :
	  	$this->_savePoster();
	  	break;
	  case EVNT_SELECT_BADGE :
	  	$this->_saveImage('evmt_badgeLogo', $page);
	  	break;
	  case EVNT_SELECT_LOGO :
	  	$this->_saveImage('evmt_logo', $page);
	  	break;
	  case EVNT_SELECT_SPON :
	  	$this->_saveImage('evmt_badgeSpon', $page);
	  	break;
	  case KID_NEW :
	  	$this->_displayFormEvent();
	  	break;
	  case KID_UPDATE :
	  	$this->_updateEvent();
	  	break;
	  case KID_EDIT :
	  	$eventId = utvars::getEventId();
	  	if ( $eventId == -1 ) $eventId = kform::getData();
	  	$this->_displayFormEvent($eventId);
	  	break;
	  case KID_DELETE :
	  	$ute = new utevent();
	  	$events = kform::getInput('rowsEvents', array());
	  	$ute->deleteEvent($events);
	  	$this->_displayFormEventList();
	  	exit;
	  	break;
	  case EVNT_EMPTY :
	  	$ute = new utevent();
	  	$events = kform::getInput('rowsEvents', array());
	  	$ute->emptyEvent($events);
	  	$this->_displayFormEventList();
	  	exit;
	  	break;
	  case EVNT_EMPTY_CACHE :
	  	$this->_emptyEventCache();
	  	exit;
	  	break;
	  	 
	  	// Modification of news
	  case EVNT_NEWS :
	  	$this->_displayFormNews();
	  	break;
	  case EVNT_DELETE_NEWS :
	  	$this->_deleteNews();
	  	break;
	  case EVNT_EDIT_NEW :
	  	$this->_displayFormNew(kform::getData());
	  	break;
	  case EVNT_ADD_NEW :
	  	$this->_displayFormNew();
	  	break;
	  	// Confirm the Deletion of a new
	  case EVNT_CONFIRM_NEWS:
	  	$this->_displayFormConfirmNews();
	  	break;
	  case EVNT_UPDATE_NEW :
	  	$this->_updateNew();
	  	break;

	  case EVNT_EDIT_PRINTOPTIONS:
	  	$this->_displayPrintOptions();
	  	break;
	  case EVNT_UPDATE_PRINTOPTIONS:
	  	$this->_updatePrintOptions();
	  	break;

	  	// Modification of the right of the users on the event
	  case EVNT_RIGHTS:
	  	$this->_displayFormRights();
	  	break;
	  case EVNT_DELETE_RIGHTS :
	  	$this->_deleteRights();
	  	break;
	  case EVNT_CONFIRM_RIGHTS:
	  	$this->_displayFormConfirmRights();
	  	break;
	  case EVNT_EDIT_RIGHT :
	  	$this->_displayFormRight();
	  	break;
	  case EVNT_UPDATE_RIGHT :
	  	$this->_updateRight();
	  	break;
	  case EVNT_ADD_RIGHT:
	  	$this->_displayFormListUser();
	  	break;

	  case EVNT_FEES:
	  	$this->_displayFees();
	  	break;
	  case EVNT_UPDATE_FEES:
	  	$this->_updateFees();
	  	break;

	  case EVNT_NEW_POSTIT:
	  	$this->_displayEditPostit(-1);
	  	break;
	  case EVNT_EDIT_POSTIT:
	  	$this->_displayEditPostit(kform::getData());
	  	break;
	  case EVNT_UPDT_POSTIT:
	  	$this->_updatePostit();
	  	break;
	  case EVNT_CTL_POSTIT:
	  	$this->_controlNbMaxDraw();
	  	break;
	  case EVNT_DEL_POSTIT:
	  	$this->_deletePostit();
	  	break;

	  case EVNT_CONTACTS:
	  	$this->_displayContactEvent();
	  	break;

	  default:
	  	echo "page $page demand�e depuis events_A<br>";
	  	exit();

		}
		exit();
	}

	/**
	 * Display the page to edit a postit
	 * @return void
	 */
	function _updatePostit()
	{
		$dt =& $this->_dt;

		$post = array('psit_title'   => kform::getInput('psitTitle'),
		    'psit_texte'   => kform::getInput('psitTexte'),
		    'psit_id'      => kform::getInput('psitId'),
		    'psit_userId'  => kform::getInput('psitParentId')
		);

		$dt->updatePostit($post);

		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}


	// {{{ _displayEditPostit()
	/**
	 * Display the page to edit a postit
	 * @return void
	 */
	function _displayEditPostit($positId)
	{
		$dt =& $this->_dt;

		$utpage = new utPage('events');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fEditPostit', 'events', EVNT_UPDT_POSTIT);

		// Get the data of the postit
		$postit = $dt->getPostit($positId);

		$form->addHide('psitId',     $postit['psit_id']);
		$form->addHide('psitUserId', $postit['psit_userId']);
		$form->addEdit('psitTitle',  $postit['psit_title'], 35);
		$form->addArea('psitTexte',  $postit['psit_texte'], 32);
		$elts = array('psitTitle', 'psitTexte');
		$form->addBlock('blkData', $elts);

		$form->addBtn('btnRegister',  KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		$utpage->display();
		exit();
	}
	// }}}

	// {{{ _deletePostit()
	/**
	 * Display the page with the postit of the event
	 * @return void
	 */
	function _deletePostit()
	{
		$dt =& $this->_dt;
		$dt->deletePostit(kform::getData());
		$this->_displayFormPostit();
		exit;
	}
	// }}}

	// {{{ _controlNbMaxDraw()
	/**
	 * Display the page with the postit of the event
	 * @return void
	 */
	function _controlNbMaxDraw()
	{
		$dt =& $this->_dt;
		$dt->controlNbMaxDraw(utvars::getEventId());
		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _displayFormPostit()
	/**
	 * Display the page with the postit of the event
	 * @return void
	 */
	function _displayFormPostit()
	{
		$dt =& $this->_dt;
		// Create a new form
		$content =& $this->_displayHead('itPostit');
		$eventId = utvars::getEventId();

		// add the news list of the event
		$kdiv =& $content->addDiv('divNews', 'blkModel');
		$form =& $kdiv->addForm('fNews');

		$itms['itNew'] = array(KAF_NEWWIN, 'events',
		EVNT_NEW_POSTIT,  $eventId, 450, 270);
		$itms['itCtl'] = array(KAF_NEWWIN, 'events',
		EVNT_CTL_POSTIT,  $eventId, 450, 270);
		$form->addMenu("menuAction", $itms, -1);
		$form->addDiv('page', 'blkNewPage');

		$postits = $dt->getPostits($eventId);
		if (isset($postits['errMsg']))
		{
	  $form->addWng($postits['errMsg']);
	  unset($postits['errMsg']);
		}
		$i = 0;
		foreach($postits as $postit)
		{
	  $i++;
	  $kdiv =& $form->addDiv("postit$i", 'classPostIt');

	  $kmsg =& $kdiv->addMsg("edit$i", 'E', 'editPost');
	  $action= array();
	  $action[] = array(KAF_NEWWIN, 'events',  EVNT_EDIT_POSTIT, $postit['psit_id'],
	  450, 270);
	  $kmsg->setActions($action);

	  $kmsg =& $kdiv->addMsg("close$i", 'X', 'closePost');
	  $action= array();
	  $action[] = array(KAF_UPLOAD, 'events',  EVNT_DEL_POSTIT, $postit['psit_id']);
	  $kmsg->setActions($action);

	  $kdiv->addMsg("title$i", $postit['psit_title'], 'titrePost');

	  $kmsg =& $kdiv->addMsg("texte$i", $postit['psit_texte'], 'textePost');
	  if ($postit['psit_action'] != 0)
	  {
	  	$action= array();
	  	$action[] = array($postit['psit_function'], $postit['psit_page'],
				$postit['psit_action'], $postit['psit_data'],
				$postit['psit_width'], $postit['psit_height']);
				$kmsg->setActions($action);
	  }

		}

		$form->addDiv('break', 'blkNewPage');

		//Display the form
		$this->_utpage->display();
		exit;
	}
	// }}}

	// {{{ _updatePrintOptions()
	/**
	 * Register the new
	 * for a news about the event
	 *
	 * @access private
	 * @param array $event  attribs of the new event
	 * @return void
	 */
	function _updatePrintOptions()
	{
		$dt =& $this->_dt;
		$eventId = utvars::getEventId();

		$infos['evmt_eventId'] = $eventId;
		$infos['evmt_titleFont']  = kform::getInput('titleFont', 'mtcorsva');
		$infos['evmt_titleSize']  = kform::getInput('titleSize', 22);
		$infos['evmt_badgeId']    = kform::getInput('badgeId', 1);
		$infos['evmt_top']    = kform::getInput('logoTop', 28);
		$infos['evmt_left']    = kform::getInput('logoLeft', 10);
		$infos['evmt_width']    = kform::getInput('logoWidth', 70);
		$infos['evmt_height']    = kform::getInput('logoHeight', 20);

		$infos['evmt_skin']       = kform::getInput('skin', 'badnet');
		$infos['evmt_urlStream']  = kform::getInput('urlStream', '');
		$infos['evmt_urlLiveScore']  = kform::getInput('urlLiveScore', '');

		// Update the event
		$res = $dt->updateEventMeta($infos);
		if (is_array($res))
		{
	  print_r($res);
	  $infos['errMsg'] = $res['errMsg'];
		}
		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}


	// {{{ _displayPrintOptions()
	/**
	 * Display the page to get information
	 * for a news about the event
	 *
	 * @access private
	 * @param array $event  attribs of the new event
	 * @return void
	 */
	function _displayPrintOptions()
	{
		$ute = new utevent();
		$dt =& $this->_dt;

		$utpage = new utPage('events');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fPrintOptions', 'events', EVNT_UPDATE_PRINTOPTIONS);

		// Get the data of the event
		$eventId = utvars::getEventId();
		$meta = $ute->getMetaEvent($eventId);
		if (isset($infos['errMsg']))
		$content->addWng($infos['errMsg']);

		$infos['evmt_eventId']    = $eventId;
		$infos['evmt_titleFont']  = kform::getInput('titleFont',$meta['evmt_titleFont']);
		$infos['evmt_titleSize']  = kform::getInput('titleSize',$meta['evmt_titleSize']);
		$infos['evmt_top']  = kform::getInput('logoTop',$meta['evmt_top']);
		$infos['evmt_left']  = kform::getInput('logoLeft',$meta['evmt_left']);
		$infos['evmt_width']  = kform::getInput('logoWidth',$meta['evmt_width']);
		$infos['evmt_height']  = kform::getInput('logoHeight',$meta['evmt_height']);
		$infos['evmt_badgeId']    = kform::getInput('badgeId',$meta['evmt_badgeId']);
		$infos['evmt_skin']       = kform::getInput('skin', $meta['evmt_skin']);
		$infos['evmt_urlStream']  = kform::getInput('skin', $meta['evmt_urlStream']);
		$infos['evmt_urlLiveScore']  = kform::getInput('skin', $meta['evmt_urlLiveScore']);

		$handle=@opendir("fpdf/font");
		$fonts=array('helvetica' => 'helvetica',
		   'courier' => 'courier',
		   'symbol' => 'symbol',
		   'times' => 'times',
		   'zapfdingbats' =>'zapfdingbats');
		if ($handle)
		{
	  $masque = "|(.*).z|";
	  while ($file = readdir($handle))
	  {
	  	if (preg_match($masque, $file, $match))
	  	{
	  		$fonts[$match[1]] = $match[1];
	  	}
	  }
	  closedir($handle);
		}
		$form->addCombo('titleFont', $fonts, $infos['evmt_titleFont']);
		$form->addEdit('titleSize', $infos['evmt_titleSize'], 3);
		$form->addEdit('logoTop', $infos['evmt_top'], 3);
		$form->addEdit('logoLeft', $infos['evmt_left'], 3);
		$form->addEdit('logoWidth', $infos['evmt_width'], 3);
		$form->addEdit('logoHeight', $infos['evmt_height'], 3);

		$badges = $dt->getBadges();
		if (isset($badges[$infos['evmt_badgeId']]))
		$select = $badges[$infos['evmt_badgeId']];
		else
		$select = reset($badges);
		$form->addCombo('badgeId', $badges, $select);

		$handle=opendir('../skins');
		while ($file = readdir($handle))
		{
			if ($file != "." && $file != ".." && $file != "CVS")
			$skins[$file] = $file;
		}
		closedir($handle);
		$form->addCombo('skin', $skins, $infos['evmt_skin']);
		$kedit =& $form->addEdit('urlStream', $infos['evmt_urlStream'], 20);
		$kedit->setMaxLength(200);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('urlLiveScore', $infos['evmt_urlLiveScore'], 20);
		$kedit->setMaxLength(200);
		$kedit->noMandatory();

		$elts = array('titleFont', 'titleSize', 'logoTop', 'logoLeft', 'logoWidth', 'logoHeight', 'badgeId', 'skin', 'urlStream', 'urlLiveScore');
		$form->addBlock('blkNew', $elts);

		$form->addBtn('btnRegister',  KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		$utpage->display();
		exit();
	}
	// }}}


	// {{{ _savePoster()
	/**
	 * Delete the image
	 *
	 * @access private
	 * @return void
	 */
	function _savePoster()
	{
		$dt =& $this->_dt;

		$infos['evnt_id'] = utvars::getEventId();
		$infos['evnt_poster'] = '';

		// Verify if the image is local
		$fileObj = kform::getInput('image', NULL);
		$fileName = $fileObj['name'];
		if ($fileName == NULL)
		$fileName = kform::getData();

		if ($fileName != NULL)
		{
	  $res =utimg::getPathPoster($fileName);
	  if ($res===false)
	  {
	  	$up = new  utFile();
	  	$res = $up->upload("image", utimg::getPathPoster());
	  	if (isset($res['errMsg']))
	  	{
	  		print_r($res);
	  		utimg::selectPoster('events', EVNT_SELECT_POSTER);
	  	}
	  	else
	  	$fileName = $res['file'];
	  }
	  if (utimg::getPathPoster($fileName) != false)
	  $infos['evnt_poster'] = $fileName;
		}
		// Update the event
		$res = $dt->updateEvent($infos);
		if (is_array($res))
		{
			$infos['errMsg'] = $res['errMsg'];
		}
		$page = new utPage('none');
		$page->clearCache();
		$page->close();
		exit;
	}
	// }}}

	// {{{ _saveImage()
	/**
	 * Delete the image
	 *
	 * @access private
	 * @return void
	 */
	function _saveImage($field, $type)
	{
		$dt = $this->_dt;

		$infos['evmt_eventId'] = utvars::getEventId();
		$infos[$field] = '';

		// Verify if the image is local
		$fileObj = kform::getInput('image', NULL);
		$fileName = $fileObj['name'];
		if ($fileName == NULL)
		$fileName = kform::getData();
		if ($fileName != NULL)
		{
	  if (utimg::getPathPoster($fileName)===false)
	  {
	  	$up = new  utFile();
	  	$res = $up->upload("image", utimg::getPathPoster());
	  	if (isset($res['errMsg']))
	  	{
	  		print_r($res);
	  		utimg::selectPoster('events', $type);
	  	}
	  	else
	  	$fileName = $res['file'];
	  }
	  if (utimg::getPathPoster($fileName)!=false)
	  $infos[$field] = $fileName;
		}
		// Update the event
		$res = $dt->updateEventMeta($infos);
		if (is_array($res))
		{
	  print_r($res);
	  $infos['errMsg'] = $res['errMsg'];
		}
		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}


	// {{{ _deleteNews()
	/**
	 * Delete the selected news in the database
	 *
	 * @access private
	 * @return void
	 */
	function _deleteNews()
	{
		$dt = $this->_dt;

		// Get the id's of the news to delete
		$ids = kform::getInput('rowsNews');
		// Delete the items
		$res = $dt->delNews($ids);
		if (is_array($res))
		{
			$err['errMsg'] = $res['errMsg'];
			$this->_displayFormConfirmNews($err);
		}

		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _deleteRights()
	/**
	 * Delete the selected rights in the database
	 *
	 * @access private
	 * @return void
	 */
	function _deleteRights()

	{
		$dt = $this->_dt;

		// Get the id's of the rights to delete
		$ids = kform::getInput('rowsRights');
		// Delete the rights
		$res = $dt->delRights($ids);
		if (is_array($res))
		{
			$err['errMsg'] = $res['errMsg'];
			$this->_displayFormConfirmRights($err);
		}

		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _displayFormConfirmNews()
	/**
	 * Display the page for confirmation the destruction of a news
	 *
	 * @access private
	 * @param array $member  info of the member
	 * @return void
	 */
	function _displayFormConfirmNews($err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('events');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tDelNews', 'events', EVNT_DELETE_NEWS);

		// Initialize the field
		$newsId = kform::getInput("rowsNews");
		if (($newsId != '') &&
		!isset($err['errMsg']))
		{

	  foreach($newsId as $id)
	  $form->addHide("rowsNews[]", $id);
	  $form->addMsg('msgConfirmDelNews');
	  $form->addBtn('btnDelete', KAF_SUBMIT);
		}
		else
		if ($newsId === '')
		$form->addWng('msgNeedNews');
		else
		$form->addWng($err['errMsg']);

		$form->addBtn('btnCancel');
		$elts = array('btnDelete', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormConfirmRights()
	/**
	 * Display the page for confirmation the destruction of rights
	 *
	 * @access private
	 * @param array $member  info of the member
	 * @return void
	 */
	function _displayFormConfirmRights($err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('events');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tDelRight', 'events', EVNT_DELETE_RIGHTS);

		// Initialize the field
		$rightsId = kform::getInput("rowsRights");
		if (($rightsId != '') &&
		!isset($err['errMsg']))
		{

	  foreach($rightsId as $id)
	  $form->addHide("rowsRights[]", $id);
	  $form->addMsg('msgConfirmDelRights');
	  $form->addBtn('btnDelete', KAF_SUBMIT);
		}
		else
		if ($rightsId === '')
		$form->addWng('msgNeedRights');
		else
		$form->addWng($err['errMsg']);

		$form->addBtn('btnCancel');
		$elts = array('btnDelete', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
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
		for($i=1; $i<10; $i++)
		{
			$start = $year + $i;
			$end   = $year + 1 + $i;
			$sea = array('value' => "{$link}{$i}",
		   'text'  => "{$start}-{$end}");
			if($select == $i) $sea['selected'] = 'selected';
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
		$ut =& $this->_ut;
		$dt = $this->_dt;
		$date = getdate();
		$curSeason = $date['year']-2006;
		if ($date['mon'] >= 8) $curSeason++;

		$season = kform::getInput('season', $curSeason);
		utvars::setSessionVar('theme', WBS_THEME_NONE);

		$utPage = new utPage_A('events', true, 0);
		$utPage->_page->addJavaFile('events/events.js');
		$content =& $utPage->getContentDiv();

		$form =& $content->addForm('formEvents');

		/*
		 require_once "lib/client.php";
		 $cl = new svc('http://www.badnet.org/badnet30/src/server', 'public', 'lov');
		 $seas = $cl->call('getSeasons', $fields);
	  */
		$fields = array('select' => $season,
		      'link'   => 'kpid=events&kaid='.WBS_ACT_EVENTS."&season=",
		);
		$seas = $this->getSeasons($fields);
		$combo =& $form->addCombo('selectList', $seas, $season);
		$actions[0] = array('loadComboPage', 'events',
		WBS_ACT_EVENTS);
		$combo->setActions($actions);

		// Display  the list of published events
		$itms['itNew'] = array(KAF_NEWWIN, 'events', KID_NEW,  0, 570, 470);
		$itms['itErase'] = array('confirmdel', 'events', KID_DELETE);
		$itms['itEmpty'] = array('confirmempty', 'events', EVNT_EMPTY);
		$form->addMenu("menuAction", $itms, -1);
		$form->addDiv('break', 'blkNewPage');

		$rows = $dt->getEvents($season);
		if (isset($rows['errMsg']))
		$form->addWng($rows['errMsg']);
		else
		{
			if (count($rows))
			{
				$mois = '';
				//$type='';
				foreach($rows as $event)
				{
					$month = substr($event['evnt_firstday'], 0, 7);
					if ($month != $mois)
					{
						$mois = $month;
						$month = strftime('%B %Y', mktime(substr($event['evnt_firstday'],11,2),
						substr($event['evnt_firstday'],14,2),
						substr($event['evnt_firstday'],17,4),
						substr($event['evnt_firstday'],5,2),
						substr($event['evnt_firstday'],8,2),
						substr($event['evnt_firstday'],0,4)));
						$events[]= array(KOD_BREAK, "title", utf8_decode($month));
					}
					$events[]=$event;
				}

				$krow =& $form->addRows("rowsEvents", $events);
				//$krow->displaySelect(false);
				$krow->setSort(0);
				$sizes[6] = '0+';
				$krow->setSize($sizes);
					
				$img[1]='logo';
				$krow->setLogo($img);
				$actions[0]  = array(KAF_NEWWIN, 'events', KID_EDIT, 0, 500, 500);
					
				$actions[2] = array(KAF_UPLOAD, 'cnx', WBS_ACT_SELECT_EVENT);
				$krow->setActions($actions);
			}
		}

		// Display the legende
		$kdiv = &$form->addDiv('lgd');
		$kdiv->addImg('lgdConfident', utimg::getIcon(WBS_DATA_CONFIDENT));
		$kdiv->addImg('lgdPrivate', utimg::getIcon(WBS_DATA_PRIVATE));
		$kdiv->addImg('lgdPublic', utimg::getIcon(WBS_DATA_PUBLIC));

		$utPage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormNews()
	/**
	 * Display the page for the news of the event
	 * @return void
	 */
	function _displayFormNews()
	{
		$dt = $this->_dt;
		$ute = new utevent();

		// Create a new form
		$content =& $this->_displayHead('itNews');
		$eventId = utvars::getEventId();

		// add the news list of the event
		$kdiv =& $content->addDiv('divNews', 'blkModel');
		$form =& $kdiv->addForm('fNews');

		$itms['itNew'] = array(KAF_NEWWIN, 'events',
		EVNT_ADD_NEW,  $eventId, 450, 270);
		$itms['itDelete'] = array(KAF_NEWWIN, 'events', EVNT_CONFIRM_NEWS, 0, 300, 150);
		$form->addMenu("menuAction", $itms, -1);
		$form->addDiv('page', 'blkNewPage');

		$news = $dt->getNews($eventId);
		if (isset($news['errMsg']))
		{
	  $form->addWng($news['errMsg']);
	  unset($news['errMsg']);
		}
		if (count($news))
		{
	  $krow =& $form->addRows('rowsNews', $news);

	  //$krow->setSort(0);

	  $act[0] = array(KAF_NEWWIN, 'events', EVNT_EDIT_NEW,
	  0, 450, 270);
	  $krow->setActions($act);
		}

		//Display the form
		$this->_utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormRights()
	/**
	 * Display the page for the rights of the event
	 * @return void
	 */
	function _displayFormRights()
	{
		$dt = $this->_dt;
		$ute = new utevent();

		// Create a new form
		$content =& $this->_displayHead('itRights');
		$eventId = utvars::getEventId();

		// add the user'list for authorization of the event
		$kform =& $content->addForm('fPrivilege');

		$itms['itNew'] = array(KAF_NEWWIN, 'events',
		EVNT_ADD_RIGHT,  $eventId, 550, 350);
		$itms['itDelete'] = array(KAF_NEWWIN, 'events', EVNT_CONFIRM_RIGHTS, 0, 300, 150);
		$kform->addMenu("menuAction", $itms, -1);
		$kform->addDiv('page', 'blkNewPage');

		$autos = $dt->getRights($eventId);
		if (isset($autos['errMsg']))
		{
	  $kform->addWng($autos['errMsg']);
	  unset($autos['errMsg']);
		}
		if (count($autos))
		{
	  $krow =& $kform->addRows('rowsRights', $autos);
	  $act[0] = array(KAF_NEWWIN, 'events', EVNT_EDIT_RIGHT,
	  0, 550, 170);
	  $krow->setActions($act);
	  $krow->setSort(0);
	  $sizes = array(4=> 0,0);
	  $krow->setSize($sizes);

	  $img[1]=5;
	  $krow->setLogo($img);
		}
		$kdiv2 = &$content->addDiv('lgd', 'blkLegende');
		$kdiv2->addImg('lgdManager', utimg::getIcon(WBS_AUTH_MANAGER));
		//$kdiv2->addImg('lgdReferee', utimg::getIcon(WBS_AUTH_REFEREE));
		$kdiv2->addImg('lgdAssist', utimg::getIcon(WBS_AUTH_ASSISTANT));
		$kdiv2->addImg('lgdCaptain', utimg::getIcon(WBS_AUTH_CAPTAIN));
		$kdiv2->addImg('lgdFriend', utimg::getIcon(WBS_AUTH_FRIEND));
		$kdiv2->addImg('lgdGuest', utimg::getIcon(WBS_AUTH_GUEST));
		$kdiv2->addImg('lgdVisitor', utimg::getIcon(WBS_AUTH_VISITOR));

		//Display the form
		$this->_utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormPrintOption()
	/**
	 * Display the main page for the event
	 *
	 * @access private
	 * @param array $eventId  id of the event
	 * @return void
	 */
	function _displayFormPrintOptions()
	{
		$dt = $this->_dt;
		$ute = new utevent();

		// Create a new form
		$content =& $this->_displayHead('itParam');

		// Get the data of the event
		$eventId = utvars::getEventId();
		$infos = $ute->getMetaEvent($eventId);
		if (isset($infos['errMsg']))
		$content->addWng($infos['errMsg']);

		$size['maxHeight'] = 50;
		$size['maxWidth'] = 100;

		//---------- Document pdf --------
		$kdiv =& $content->addDiv('divPdf', 'blkData');
		$kdiv->addMsg('tPdf', '', 'kTitre');
		$kdiv->addInfo('titleFont',  $infos['evmt_titleFont']);
		$kdiv->addInfo('titleSize',  $infos['evmt_titleSize']);
		$kdiv->addInfo('logoTop',  $infos['evmt_top']);
		$kdiv->addInfo('logoLeft',  $infos['evmt_left']);
		$kdiv->addInfo('logoWidth',  $infos['evmt_width']);
		$kdiv->addInfo('logoHeight',  $infos['evmt_height']);
		// Top left logo
		$kdivImg =& $kdiv->addDiv('divTopLeft', 'blkImg');
		if ($infos['evmt_logo'] != '')
		{
	  $img = utimg::getPathPoster($infos['evmt_logo']);
	  $kdivImg->addImg('leftLogo', $img, $size);
		}
		$items = array();
		$items['btnLogo'] = array(KAF_NEWWIN, 'events', EVNT_SELECT_IMAGE,
		EVNT_SELECT_LOGO, 650, 400);
		$items['btnModify'] = array(KAF_NEWWIN, 'events', EVNT_EDIT_PRINTOPTIONS,
		1, 400, 320);
		$kdiv->addMenu('menuPdf', $items, -1, 'classMenuBtn');


		//---------- Badges --------
		$kdiv =& $content->addDiv('divBadges', 'blkData');
		$kdiv->addMsg('tBadges', '', 'kTitre');
		$badges = $dt->getBadges();
		if (isset($badges[$infos['evmt_badgeId']]))
		$select = $badges[$infos['evmt_badgeId']];
		else
		$select = reset($badges);
		$kdiv->addInfo('badgeId',    $select);
		// Sponsor logo
		$kdivImg =& $kdiv->addDiv('divSponLogo', 'blkImg');
		if ($infos['evmt_badgeSpon'] != '')
		{
	  $img = utimg::getPathPoster($infos['evmt_badgeSpon']);
	  $kdivImg->addImg('badgeSpon', $img, $size);
		}
		$kdivImg =& $kdiv->addDiv('divBadgeLogo', 'blkImg');
		if ($infos['evmt_badgeLogo'] != '')
		{
	  $img = utimg::getPathPoster($infos['evmt_badgeLogo']);
	  $kdivImg->addImg('badgeLogo', $img, $size);
		}
		$items = array();
		$items['btnBadgeSpon'] = array(KAF_NEWWIN, 'events', EVNT_SELECT_IMAGE,
		EVNT_SELECT_SPON, 650, 400);
		$items['btnBadgeLogo'] = array(KAF_NEWWIN, 'events', EVNT_SELECT_IMAGE,
		EVNT_SELECT_BADGE, 650, 400);
		$items['btnModify'] = array(KAF_NEWWIN, 'events', EVNT_EDIT_PRINTOPTIONS,
		2, 320, 200);
		$kdiv->addMenu('menuBadger', $items, -1, 'classMenuBtn');

		//---------- Presentation --------
		$kdiv =& $content->addDiv('divMain', 'blkData');
		$kdiv->addMsg('tMain', '', 'kTitre');
		$kdiv->addInfo('skin',       $infos['evmt_skin']);
		$kdiv->addInfo('urlStream',  $infos['evmt_urlStream']);
		$kdiv->addInfo('urlLiveScore',  $infos['evmt_urlLiveScore']);
		$items = array();
		$items['btnModify'] = array(KAF_NEWWIN, 'events', EVNT_EDIT_PRINTOPTIONS,
		3, 320, 200);
		$kdiv->addMenu('menuModify', $items, -1, 'classMenuBtn');

		$content->addDiv('page', 'blkNewPage');

		//Display the form
		$this->_utpage->display();
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
		$ut =& $this->_ut;
		$dt =& $this->_dt;

		// Create a new form
		$eventId = utvars::getEventId();

		$content =& $this->_displayHead('itGeneral');

		// Get the data of the event
		$ute = new utevent();
		$infos = $ute->getEvent($eventId);
		if (isset($infos['errMsg']))
		$content->addWng($infos['errMsg']);

		$kdiv =& $content->addDiv('infos', 'blkData');
		$kdiv->addMsg('tGeneral', '', 'kTitre');
		$kdiv->addInfo('evntName',      $infos['evnt_name']);
		$kdiv->addInfo('evntDate',      $infos['evnt_date']);
		$kdiv->addInfo('evntPlace',     $infos['evnt_place']);
		$kdiv->addInfo('evntOrganizer', $infos['evnt_organizer']);
		$kdiv->addInfo('evntNumauto',   $infos['evnt_numauto']);

		$utd = new utdate();
		$utd->setIsoDate($infos['evnt_deadline']);
		$kdiv->addInfo("evntDeadline",  $utd->getDate());
		$utd->setIsoDate($infos['evnt_datedraw']);
		$kdiv->addInfo("evntDatedraw",  $utd->getDate());

		$kdiv->addInfo('evntNature',      $ut->getLabel($infos['evnt_nature']));
		$kdiv->addInfo('evntLevel',      $ut->getLabel($infos['evnt_level']));
		$kdiv->addInfo('evntNbvisited', $infos['evnt_nbvisited']);
		$kdiv->addInfo('evntId',        $infos['evnt_id']);

		$kdivb =& $kdiv->addDiv('btn', 'blkMenuBtn');
		$items['btnModify']  = array(KAF_NEWWIN, 'events', KID_EDIT,
		$eventId, 500, 500);


		if ($infos['evnt_pbl'] == WBS_DATA_CONFIDENT)
		$items['btnPubli']  = array(KAF_UPLOAD, 'events', KID_PUBLISHED,
		$eventId);
		else
		$items['btnPrivate']  = array(KAF_UPLOAD, 'events', KID_CONFIDENT,
		$eventId);
		$items['btnAddPoster'] = array(KAF_NEWWIN, 'events', EVNT_SELECT_IMAGE,
		EVNT_SELECT_POSTER, 650, 400);

		if ($infos['evnt_liveentries'] == 160)
		$items2['btnOffline']  = array(KAF_UPLOAD, 'events', EVNT_OFFLINE,
		$eventId);
		else
		$items2['btnInline']  = array(KAF_UPLOAD, 'events', EVNT_INLINE,
		$eventId);
			
		$items2['btnEmptyCache']  = array(KAF_NEWWIN, 'events', EVNT_EMPTY_CACHE,
		$eventId);
			
		$kdivb->addMenu('menuBtn', $items, -1, 'classMenuBtn');
		$kdivb->addMenu('menuBtn2', $items2, -1, 'classMenuBtn');


		// add the fees of the tournament
		if (!utvars::IsTeamEvent())
		{
			$isSquash = $ut->getParam('issquash', false);

			require_once "utils/utfees.php";
			$utf = new utfees();
			$fees = $utf->getFees();
			$kdiv =& $content->addDiv('divFees', 'blkData');
			$kdiv->addMsg('tFees', '', 'kTitre');
			$kdiv->addInfo('IS',  sprintf('%.02f', $fees['IS']['item_value']));
			if (!$isSquash)
			{
				$kdiv->addInfo('ID',  sprintf('%.02f', $fees['ID']['item_value']));
				$kdiv->addInfo('IM',  sprintf('%.02f', $fees['IM']['item_value']));
				$kdiv->addInfo('I1',  sprintf('%.02f', $fees['I1']['item_value']));
				$kdiv->addInfo('I2',  sprintf('%.02f', $fees['I2']['item_value']));
				$kdiv->addInfo('I3',  sprintf('%.02f', $fees['I3']['item_value']));
			}
			$divb =& $kdiv->addDiv('btn', 'blkMenuBtn');
			unset($items);
			$items['btnModify']  = array(KAF_NEWWIN, 'events', EVNT_FEES, 0, 400, 300);
			$divb->addMenu("menuBtn", $items, -1, 'classMenuBtn');
		}
		// add the poster of the tournament
		$img = utimg::getPathPoster($infos['evnt_poster']);
		$size['maxWidth'] = 200;
		$content->addImg('poster', $img, $size);
		$content->addDiv('page', 'blkNewPage');
		//Display the form
		$this->_utpage->display();
		exit;
	}
	// }}}

	/**
	 * Display the page to get information about
	 * an event
	 *
	 * @access private
	 * @param array $event  attribs of the new event
	 * @return void
	 */
	function _displayFormEvent($event='')
	{
		$ut =& $this->_ut;
		$isSquash = $ut->getParam('issquash', false);
		$utpage = new utPage('events');
		$utpage->_page->addAction('onload', array('resize', 500, 800));
		$content =& $utpage->getPage();
		$form =& $content->addForm('fevents', 'events', KID_UPDATE);

		$date = getdate();
		$curSeason = $date['year']-2006;
		if ($date['mon'] >= 8) $curSeason++;
		$infos = array('evnt_name'      => '',
		     'evnt_date'      => '',
		     'evnt_place'     => '',
		     'evnt_organizer' => '',
		     'evnt_cmt'       => '',
		     'evnt_datedraw'  => '',
		     'evnt_deadline'  => '',
		     'evnt_type'      => WBS_EVENT_INDIVIDUAL,
		     'evnt_nature'    => WBS_NATURE_TOURN,
		     'evnt_level'     => WBS_LEVEL_CLUB,
		     'evnt_scoringsystem' => WBS_SCORING_3X21,
		     'evnt_ranksystem' => WBS_RANK_FR2,
		     'evnt_numauto'   => '',
		     'evnt_url'       => '',
		     'evnt_firstday'  => '',
		     'evnt_lastday'   => '',
		     'evnt_zone'      => '',
		     'evnt_urlrank'   => $ut->getParam('ffba_url'),
		     'evnt_nbdrawmax' => 3,
		     'evnt_id'        => -1,
		     'evnt_ownerid'   => utvars::getUserId(),
		     'evnt_season'    => $curSeason,
		     'evnt_catage'    => WBS_EVENT_CATAGE_SENIOR);
		if ($isSquash)
		{
			$infos['evnt_scoringsystem'] = WBS_SCORING_5X11;
			$infos['evnt_ranksystem'] = WBS_RANK_SQUASH;
		}

		$ute = new utevent();
		if (is_array($event)) $infos = $event;
		else if ($event != '') $infos = $ute->getEvent($event);

		if ($infos['evnt_id'] != -1) $form->setTitle('tEditEvent');
		else $form->setTitle('tNewEvent');

		$utd = new utdate();
		if (isset($infos['errMsg'])) $form->addWng($infos['errMsg']);
		$form->addHide('evntId',     $infos['evnt_id']);
		$kedit =& $form->addEdit('evntName', $infos['evnt_name'], 45);
		$kedit->setMaxLength(200);
		$kedit =& $form->addEdit('evntDate',     $infos['evnt_date'], 45);
		$kedit->setMaxLength(25);
		$kedit->noMandatory();
		$utd->setIsoDate($infos['evnt_firstday']);
		$kedit =& $form->addEdit('evntFirstday', $utd->getdate(), 15);
		$kedit->setMaxLength(15);
		$utd->setIsoDate($infos['evnt_lastday']);
		$kedit =& $form->addEdit('evntLastday', $utd->getdate(), 15);
		$kedit->setMaxLength(15);
		$kedit =& $form->addEdit('evntPlace',    $infos['evnt_place'], 45);
		$kedit->setMaxLength(25);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('evntZone',    $infos['evnt_zone'], 45);
		$kedit->setMaxLength(25);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('evntOrganizer', $infos['evnt_organizer'], 45);
		$kedit->setMaxLength(75);
		$kedit->noMandatory();
		if ($isSquash) $form->addHide('evntNbDrawMax', 1);
		else
		{
			$kedit =& $form->addEdit('evntNbDrawMax', $infos['evnt_nbdrawmax'], 45);
			$kedit->setMaxLength(1);
			$kedit->noMandatory();
		}
		$kedit =& $form->addEdit('evntNumauto', $infos['evnt_numauto'], 45);
		$kedit->setMaxLength(150);
		$kedit->noMandatory();
		$kedit =& $form->addHide('evntUrl', $infos['evnt_url']);
		$kedit =& $form->addEdit('evntUrlRank', $infos['evnt_urlrank'], 45);
		$kedit->setMaxLength(200);
		$kedit->noMandatory();
		$kedit =& $form->addArea('evntCmt', $infos['evnt_cmt'] , 30 );
		$kedit->noMandatory();
		$utd->setIsoDate($infos['evnt_datedraw']);
		$kedit =& $form->addEdit('evntDatedraw', $utd->getDate(), 10 );
		$kedit->noMandatory();
		$utd->setIsoDate($infos['evnt_deadline']);
		$kedit =& $form->addEdit('evntDeadline', $utd->getDate(), 10 );
		$kedit->noMandatory();
		if ($isSquash)
		{
			$scoring[WBS_SCORING_NONE] = $ut->getLabel(WBS_SCORING_NONE);
			for ($i=WBS_SCORING_1X5; $i <=WBS_SCORING_5X11; $i++) $scoring[$i] = $ut->getLabel($i);
			$combo =& $form->addCombo('evntScoring', $scoring, $ut->getLabel($infos['evnt_scoringsystem']) );
			$kedit->noMandatory();
			$form->addHide('evntRanking', WBS_RANK_SQUASH);
		}
		else
		{
			for ($i=WBS_SCORING_NONE; $i < WBS_SCORING_1X5; $i++) $scoring[$i] = $ut->getLabel($i);
			$combo =& $form->addCombo('evntScoring', $scoring, $ut->getLabel($infos['evnt_scoringsystem']) );
			$kedit->noMandatory();

			for ($i=WBS_RANK_FR1; $i < WBS_RANK_SQUASH; $i++)	$ranking[$i] = $ut->getLabel($i);
			$combo =& $form->addCombo('evntRanking', $ranking, $ut->getLabel($infos['evnt_ranksystem']) );
			$kedit->noMandatory();
		}
		$form->addRadio('evntNature', $infos['evnt_nature']== WBS_NATURE_TOURN,
		WBS_NATURE_TOURN);
		$form->addRadio('evntNature', $infos['evnt_nature']== WBS_NATURE_EQUIP,
		WBS_NATURE_EQUIP);
		$form->addRadio('evntNature', $infos['evnt_nature']== WBS_NATURE_INDIV,
		WBS_NATURE_INDIV);
		if ($isSquash)
		{
			$elts = array('evntNature3', 'evntNature1', 'evntNature2');
			$form->addBlock('blkType', $elts);
		}
		else
		{
			$form->addRadio('evntNature', $infos['evnt_nature']== WBS_NATURE_TROPH,
			WBS_NATURE_TROPH);
			$form->addRadio('evntNature', $infos['evnt_nature']== WBS_NATURE_INTERCODEP,
			WBS_NATURE_INTERCODEP);
			$form->addRadio('evntNature', $infos['evnt_nature']== WBS_NATURE_OTHER,
			WBS_NATURE_OTHER);
			$elts = array('evntNature3', 'evntNature4', 'evntNature1');
			$form->addBlock('blkType1', $elts);
			$elts = array('evntNature2', 'evntNature5', 'evntNature6');
			$form->addBlock('blkType2', $elts);
			$elts = array('blkType1', 'blkType2');
			$form->addBlock('blkType', $elts);
		}
		$form->addRadio('evntLevel', $infos['evnt_level']==WBS_LEVEL_CLUB,
		WBS_LEVEL_CLUB);
		$form->addRadio('evntLevel', $infos['evnt_level']==WBS_LEVEL_REG,
		WBS_LEVEL_REG);
		$form->addRadio('evntLevel', $infos['evnt_level']==WBS_LEVEL_NAT,
		WBS_LEVEL_NAT);
		if(!$isSquash)
		{
		$form->addRadio('evntLevel', $infos['evnt_level']==WBS_LEVEL_DEP,
		WBS_LEVEL_DEP);
		$form->addRadio('evntLevel', $infos['evnt_level']==WBS_LEVEL_INTER_DEPT,
		WBS_LEVEL_INTER_DEPT);
		$form->addRadio('evntLevel', $infos['evnt_level']==WBS_LEVEL_INTER_REGION,
		WBS_LEVEL_INTER_REGION);
		$form->addRadio('evntLevel', $infos['evnt_level']==WBS_LEVEL_INT,
		WBS_LEVEL_INT);
		}	
		$elts = array('evntLevel1', 'evntLevel4', 'evntLevel2', 'evntLevel3'); 
		$form->addBlock('blkLevel2', $elts);
			
		$elts = array('evntLevel5', 'evntLevel6', 'evntLevel7');
		$form->addBlock('blkLevel3', $elts);

		$elts = array('blkLevel2', 'blkLevel3');
		$form->addBlock('blkLevel', $elts);

		$form->addRadio('evntCatage', $infos['evnt_catage']== WBS_EVENT_CATAGE_SENIOR,
			WBS_EVENT_CATAGE_SENIOR);
		$form->addRadio('evntCatage', $infos['evnt_catage']== WBS_EVENT_CATAGE_YOUNG,
			WBS_EVENT_CATAGE_YOUNG);
		if (!$isSquash)
		{
			$form->addRadio('evntCatage', $infos['evnt_catage']== WBS_EVENT_CATAGE_BOTH,
			WBS_EVENT_CATAGE_BOTH);
		}
		$elts = array('evntCatage1','evntCatage2', 'evntCatage3');
		$form->addBlock('blkCatage', $elts);
		
		if (utvars::_getSessionVar('userAuth') == WBS_AUTH_ADMIN)
		{
			$list = $this->_dt->getSelUsers();
			$combo =& $form->addCombo('evntOwnerId', $list, $infos['evnt_ownerid']);
			$fields = array('select' => $infos['evnt_season'],
		      'link'   => '');
			$seas = $this->getSeasons($fields);
			$combo =& $form->addCombo('evntSeason', $seas);
	  //$form->addEdit('evntSeason', $infos['evnt_season']);
		}
		else
		{
			$form->addHide('evntOwnerId', $infos['evnt_ownerid']);
			$form->addHide('evntSeason',  $infos['evnt_season']);
		}
		$elts = array('evntName', 'evntDate', 'evntFirstday','evntLastday',
		    'evntPlace', 'evntZone', 'evntOrganizer', 
		    'evntNumauto', 'evntUrl', 'evntUrlRank', 'evntCmt', 
		    'evntNbDrawMax', 'evntDeadline', 'evntDatedraw', 
		    'evntScoring', 'evntRanking', 'evntOwnerId',
		    'evntSeason');
		$form->addBlock('blkEvent', $elts);

		$form->addBtn('btnRegister',  KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', "btnCancel");
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit();
	}
	// }}}

	// {{{ _displayFormNew()
	/**
	 * Display the page to get information
	 * for a news about the event
	 *
	 * @access private
	 * @param array $event  attribs of the new event
	 * @return void
	 */
	function _displayFormNew($new='')
	{
		$utpage = new utPage('events');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fevents', 'events', EVNT_UPDATE_NEW);

		$infos = array('news_text'      => '',
		     'news_page'      => '',
		     'news_eventId'   => -1,
		     'news_id'   => -1);
		if (is_array($new))
		$infos = $new;
		else
		if ($new != '') $infos = $this->_dt->getNew($new);

		if ($infos['news_id'] != -1)
		$form->setTitle("tEditNew");
		else
		$form->setTitle("tNewNew");

		if (isset($infos['errMsg']))
		$form->addWng($infos['errMsg']);

		$form->addHide('newsId',     $infos['news_id']);
		$kedit =& $form->addArea('newsText', $infos['news_text'] , 30 );
		$types = array( 'events'    => 'itAttribs',
		      'regi'      => 'itPlayers',
		      'schedu'    => 'itCalendar',
                      'live'      => 'itLive');
		if (utvars::isTeamEvent())
		{
	  $types['divs'] = 'itDivisions';
	  $types['ties'] = 'itResults';
		}
		else
		{
	  $types['draws'] = 'itDraws';
		}
		$typeSel = $infos['news_page'];
		if ($typeSel == '') $typeSel = 'events';
		$form->addCombo('newsPage', $types, $types[$typeSel]);
		$form->addCheck('sendNew', ($infos['news_id'] === -1));

		$elts = array('newsText', 'newsPage', 'sendNew');
		$form->addBlock('blkNew', $elts);

		$form->addBtn('btnRegister',  KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		$utpage->display();
		exit();
	}
	// }}}

	// {{{ _updateNew()
	/**
	 * Register the new
	 * for a news about the event
	 *
	 * @access private
	 * @param array $event  attribs of the new event
	 * @return void
	 */
	function _updateNew()
	{
		$dt = $this->_dt;
		$infos = array('news_text'      => kform::getInput('newsText'),
		     'news_page'      => kform::getInput('newsPage'),
		     'news_eventId'   => utvars::getEventId(),
		     'news_pbl'       => WBS_DATA_PUBLIC,
		     'news_id'        => kform::getInput('newsId'));

		// Update the new
		$res = $dt->updateNew($infos);
		if (is_array($res))
		{
			$infos['errMsg'] = $res['errMsg'];
			$this->_displayFormNew($infos);
		}
		// Send the news to subscibers
		$sendIt = kform::getInput('sendNew', 0);
		if ($sendIt==1)
		{
	  require_once "subscript/subscript_V.php";
	  $sub = new subscript_V();
	  $sub->sendNews();
		}
		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _publiEvent()
	/**
	 * Change the publication state of the selected event in the database
	 *
	 * @access private
	 * @param  integer $publi  new publication state of the event
	 * @return void
	 */
	function _publiEvent($publi)
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;

		// Get the id's of the events to delete
		$eventId = kform::getData();

		// Publishing the event
		$res = $dt->publiEvent($eventId, $publi);

		// Display the list of events
		$this->_displayFormMainEvent();
		exit;
	}
	// }}}

	function _inlineEvent($publi)
	{
		$dt =& $this->_dt;

		// Get the id's of the events to delete
		$eventId = kform::getData();

		// Publishing the event
		$res = $dt->inlineEvent($eventId, $publi);

		// Display the list of events
		$this->_displayFormMainEvent();
		exit;
	}

	function _emptyEventCache()
	{
		// Get the id's of the events
		$eventId = kform::getData();

		// delete the files
		$dir = '../cache';
		$file = '_' . $eventId . '_';
		if(!$dh = opendir($dir)) return;
		while (false !== ($obj = readdir($dh)))
		{
			if(strpos($obj, $file) === false) continue;
			unlink($dir . '/' . $obj);
		}
		closedir($dh);
		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}


	// {{{ _updateEvent()
	/**
	 * Create a new event or update an existing event in the database.
	 *
	 * @access private
	 * @return void
	 */
	function _updateEvent()
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;

		// Get the informations
		$infos = array('evnt_name'      =>kform::getInput("evntName"),
                     'evnt_date'      =>kform::getInput("evntDate"),
                     'evnt_place'     =>kform::getInput("evntPlace"),
                     'evnt_organizer' =>kform::getInput("evntOrganizer"),
                     'evnt_cmt'       =>kform::getInput("evntCmt"),
                     'evnt_datedraw'  =>kform::getInput("evntDatedraw"),
                     'evnt_deadline'  =>kform::getInput("evntDeadline"),
                     'evnt_nature'    =>kform::getInput("evntNature"),
                     'evnt_level'     =>kform::getInput("evntLevel"),
                     'evnt_numauto'   =>kform::getInput("evntNumauto"),
                     'evnt_id'        =>kform::getInput("evntId"),
                     'evnt_url'       =>kform::getInput("evntUrl"),
                     'evnt_urlrank'   =>kform::getInput("evntUrlRank"),
                     'evnt_nbdrawmax' =>kform::getInput("evntNbDrawMax"),
                     'evnt_zone'      =>kform::getInput("evntZone"),
                     'evnt_firstday'  =>kform::getInput("evntFirstday"),
                     'evnt_lastday'   =>kform::getInput("evntLastday"),
                     'evnt_ownerId'   =>kform::getInput("evntOwnerId"),
                     'evnt_season'    =>kform::getInput("evntSeason"),
                     'evnt_catage'    =>kform::getInput("evntCatage"),
		     'evnt_ranksystem' => kform::getInput('evntRanking', WBS_RANK_FR2),
		     'evnt_scoringsystem' =>kform::getInput('evntScoring', WBS_SCORING_3X21));

		$isSquash = $ut->getParam('issquash', false);
		if ($isSquash)
		{
			if ($infos['evnt_catage'] == WBS_EVENT_CATAGE_SENIOR)
			$infos['evnt_ranksystem'] = WBS_RANK_SQUASH;
			else $infos['evnt_ranksystem'] = WBS_RANK_SQUASH_YOUNG;
		}

		if ($infos['evnt_nature'] == WBS_NATURE_EQUIP ||
		$infos['evnt_nature'] == WBS_NATURE_INTERCODEP) $infos['evnt_type'] = WBS_EVENT_TEAM;
		else $infos['evnt_type'] = WBS_EVENT_INDIVIDUAL;

		// Control the informations
		if ($infos['evnt_name'] == "")
		{
	  $infos['errMsg'] = 'msgevntName';
	  $this->_displayFormEvent($infos);
		}

		$utd = new utdate();
		$utd->setFrDate($infos['evnt_datedraw']);
		$infos['evnt_datedraw'] = $utd->getIsoDateTime();
		$utd->setFrDate($infos['evnt_deadline']);
		$infos['evnt_deadline'] = $utd->getIsoDateTime();
		$utd->setFrDate($infos['evnt_firstday']);
		$infos['evnt_firstday'] = $utd->getIsoDateTime();
		$utd->setFrDate($infos['evnt_lastday']);
		$infos['evnt_lastday'] = $utd->getIsoDateTime();

		// Update the event
		$res = $dt->updateEvent($infos);
		if (is_array($res))
		{
			$infos['errMsg'] = $res['errMsg'];
			$this->_displayFormEvent($infos);
		}
		$page = new utPage('none');
		$page->clearCache();
		$page->close();
		exit;

	}
	// }}}

	// {{{ _updateRight
	/**
	 * Add the right fo the select user
	 *
	 * @access private
	 * @return void
	 */
	function _updateRight()
	{
		$dt = $this->_dt;

		// Initialise the informations
		$infos = array('rght_eventId'  =>utvars::getEventId(),
                     'rght_userId'   =>kform::getInput("users"),
                     'rght_status'   =>kform::getInput("right"));

		// Control the informations
		if ($infos['rght_userId'] == '')
		{
	  $infos['errMsg'] = 'msgusers';
	  $this->_displayFormListUser($infos);
		}

		if ($infos['rght_status'] == "")
		{
	  $res['errMsg'] = 'msgStatus';
	  $this->_displayFormListUser($infos);
		}

		// Add the right
		$res = $dt->updateRight($infos);
		if (isset($res['errMsg']))
		{
	  $infos['errMsg'] = $res['errMsg'];
	  $this->_displayFormListUser($infos);
		}

		// Send notification to the user
		$sendIt = kform::getInput('sendRight', 0);
		if ($sendIt==1)
		{
	  require_once "subscript/subscript_V.php";
	  $sub = new subscript_V();
	  $res = $sub->sendRight($res);
		}
		// All is ok. Close the window
		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _displayFormRight()
	/**
	 * Display a form to modify the righth of the users
	 *
	 * @access private
	 * @param integer $err     Error message
	 * @return void
	 */
	function _displayFormRight($err="")
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;

		$utpage = new utPage('events');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fevents', 'events', EVNT_UPDATE_RIGHT);
		$form->setTitle("tEditRight");

		$rightId = kform::getData();
		$right= $dt->getRight($rightId);
		$rightSel=$right['rght_status'];

		$form->addHide('users', $right['user_id']);
		$form->addMsg('usersName', $right['user_name']);
		$form->addRadio('right', $rightSel==WBS_AUTH_VISITOR, WBS_AUTH_VISITOR);
		$form->addRadio('right', $rightSel==WBS_AUTH_GUEST, WBS_AUTH_GUEST);
		$form->addRadio('right', $rightSel==WBS_AUTH_FRIEND, WBS_AUTH_FRIEND);
		$form->addRadio('right', $rightSel==WBS_AUTH_ASSISTANT, WBS_AUTH_ASSISTANT);
		$form->addRadio('right', $rightSel==WBS_AUTH_MANAGER, WBS_AUTH_MANAGER);
		//$form->addRadio('right', $rightSel==WBS_AUTH_REFEREE, WBS_AUTH_REFEREE);
		$elts = array('users', 'right5', 'right6', 'right4', 'right3',
		    'right2','right1'); 
		$form->addBlock('blkUser', $elts);

		$form->addCheck('sendRight', 1);
		$form->addBlock('blkSendRight', 'sendRight');

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormListUser()
	/**
	 * Display a form to select the users to add
	 *
	 * @access private
	 * @param char   $type  Status off the user
	 * @param array  $err   Additional error informations
	 * @return void
	 */
	function _displayFormListUser($err='')
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;

		$utpage = new utPage('events');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fevents', 'events', EVNT_UPDATE_RIGHT);
		$form->setTitle("tAddRight");

		$rightSel = WBS_AUTH_VISITOR;
		$userSel = -1;
		if (is_array($err))
		{
	  if (isset($err['errMsg']))
	  $form->addWng($err['errMsg']);
	  $userSel = $err['rght_userId'];
	  $rightSel = $err['rght_status'];
		}

		// Get the user of the data base
		$users = $dt->getSelUsers();

		// Display a warning if an error occured
		if (isset($users['errMsg']))
		{
			$form->addWng($users['errMsg']);
			unset($users['errMsg']);
		}
		// Initialize the field
		$form->addCombo('users', $users);
		$form->setLength('users', 10);
		$form->addRadio('right', $rightSel==WBS_AUTH_VISITOR, WBS_AUTH_VISITOR);
		$form->addRadio('right', $rightSel==WBS_AUTH_GUEST, WBS_AUTH_GUEST);
		$form->addRadio('right', $rightSel==WBS_AUTH_FRIEND, WBS_AUTH_FRIEND);
		$form->addRadio('right', $rightSel==WBS_AUTH_ASSISTANT, WBS_AUTH_ASSISTANT);
		$form->addRadio('right', $rightSel==WBS_AUTH_MANAGER, WBS_AUTH_MANAGER);
		$form->addRadio('right', $rightSel==WBS_AUTH_REFEREE, WBS_AUTH_REFEREE);

		$elts = array('users', 'right5', 'right6', 'right4', 'right3',
		    'right2','right1'); 
		$form->addBlock('blkRights', $elts);

		$form->addCheck('sendRight', 1);
		$form->addBlock('blkSendRight', 'sendRight');

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
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
		$form->addBtn("btnMail", KAF_SUBMIT);
		$elts = array("btnMail");
		$form->addBlock("blkBtn", $elts);
		$this->_utpage->display();
		exit;
	}
	// }}}

	/**
	 * Display the page with details of entry fees
	 *
	 * @access private
	 * @return void
	 */
	function _displayFees($err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('events');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tFees', 'events', EVNT_UPDATE_FEES);
		$ut = new utils();
		$isSquash = $ut->getParam('issquash', false);
		require_once "utils/utfees.php";
		$utf = new utfees();
		$fees = $utf->getFees();
		$kedit =& $form->addEdit('IS', sprintf('%.02f', $fees['IS']['item_value']), 10);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		if ($isSquash)
		{
			$form->addHide('ID', $fees['ID']['item_value']);
			$form->addHide('IM', $fees['IM']['item_value']);
			$form->addHide('I1', $fees['I1']['item_value']);
			$form->addHide('I2', $fees['I2']['item_value']);
			$form->addHide('I3', $fees['I3']['item_value']);
		}
		else
		{
			$kedit =& $form->addEdit('ID', sprintf('%.02f', $fees['ID']['item_value']), 10);
			$kedit->setMaxLength(10);
			$kedit->noMandatory();
			$kedit =& $form->addEdit('IM', sprintf('%.02f', $fees['IM']['item_value']), 10);
			$kedit->setMaxLength(10);
			$kedit->noMandatory();
			$kedit =& $form->addEdit('I1', sprintf('%.02f', $fees['I1']['item_value']), 10);
			$kedit->setMaxLength(10);
			$kedit->noMandatory();
			$kedit =& $form->addEdit('I2', sprintf('%.02f', $fees['I2']['item_value']), 10);
			$kedit->setMaxLength(10);
			$kedit->noMandatory();
			$kedit =& $form->addEdit('I3', sprintf('%.02f', $fees['I3']['item_value']), 10);
			$kedit->setMaxLength(10);
			$kedit->noMandatory();
		}
		$form->addCheck('updateAll');
		$elts = array('updateAll');
		$form->addBlock('blkupdateAll', $elts);
		
		$form->addCheck('matchOnly');
		$elts = array('matchOnly');
		$form->addBlock('blkmatchOnly', $elts);
		
		$elts = array('IS','ID', 'IM', 'I1', 'I2', 'I3', 'blkupdateAll', "blkmatchOnly");
		$form->addBlock('blkFees', $elts);

		$form->addBtn('btnRegister',  KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', "btnCancel");
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit();
	}
	// }}}

	// {{{ _updateFees()
	/**
	 * Read the fees and updtae
	 *
	 * @access private
	 * @return void
	 */
	function _updateFees()
	{
		require_once "utils/utfees.php";
		$utf = new utfees();

		// Get the informations
		$fee['item_code'] = 'IS';
		$fee['item_value'] = kform::getInput('IS');
		$fee['item_name'] = 'Inscription simple';
		$fees[] = $fee;
		$fee['item_code'] = 'ID';
		$fee['item_value'] = kform::getInput('ID');
		$fee['item_name'] = 'Inscription double';
		$fees[] = $fee;
		$fee['item_code'] = 'IM';
		$fee['item_value'] = kform::getInput('IM');
		$fee['item_name'] = 'Inscription mixte';
		$fees[] = $fee;
		$fee['item_code'] = 'I1';
		$fee['item_value'] = kform::getInput('I1');
		$fee['item_name'] = 'Inscription 1 tableau';
		$fees[] = $fee;
		$fee['item_code'] = 'I2';
		$fee['item_value'] = kform::getInput('I2');
		$fee['item_name'] = 'Inscription 2 tableaux';
		$fees[] = $fee;
		$fee['item_code'] = 'I3';
		$fee['item_value'] = kform::getInput('I3');
		$fee['item_name'] = 'Inscription 3 tableaux';
		$fees[] = $fee;

		// Update the definition of the fees
		$utf->updateFees($fees);

		if (kform::getInput('updateAll',-1) != -1)
		$utf->updateRegisFees(kform::getInput('matchOnly',0));

		$page = new utPage('none');
		$page->close();
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
		$dt =& $this->_dt;

		// Create a new page
		$this->_utpage = new utPage_A('events', true, 0);
		$content =& $this->_utpage->getContentDiv();

		// Add a menu for different action
		$kdiv =& $content->addDiv('choix', 'onglet3');
		$items['itPostit'] = array(KAF_UPLOAD, 'events', KID_SELECT);
		$items['itGeneral'] = array(KAF_UPLOAD, 'events', EVNT_MAIN);
		$items['itParam']   = array(KAF_UPLOAD, 'events', EVNT_PRINT_OPTIONS);
		$items['itNews']    = array(KAF_UPLOAD, 'events', EVNT_NEWS);
		$items['itRights']  = array(KAF_UPLOAD, 'events', EVNT_RIGHTS);
		//$items['itContacts'] = array(KAF_UPLOAD, 'events', EVNT_CONTACTS);
		$kdiv->addMenu("menuType", $items, $select);
		$kdiv =& $content->addDiv('register', 'cont3');
		return $kdiv;
	}
	// }}}



}
?>
