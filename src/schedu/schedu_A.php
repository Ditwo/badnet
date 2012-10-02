<?php
/*****************************************************************************
 !   Module     : Schedu
 !   File       : $Source: /cvsroot/aotb/badnet/src/schedu/schedu_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.35 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/03/06 18:02:11 $
 ******************************************************************************/
require_once "base_A.php";
require_once "schedu.inc";
require_once "books/books.inc";
require_once "teams/teams.inc";
require_once "draws/draws.inc";
require_once "utils/utteam.php";
require_once "utils/utdate.php";
require_once "utils/utpage_A.php";
require_once "utils/utdraw.php";

/**
 * Module de gestion des rencontres : classe administrateur
 *
 * @author Gerard CANTEGRL <cage@free.fr>
 * @see to follow
 *
 */

class schedu_A
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
	function schedu_A()
	{
		$this->_ut = new utils();
		$this->_dt = new scheduBase_A();
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
			case SCHEDU_UPDATE:
				$this->_updateSchedule();
				break;

			case SCHEDU_UPDATE_ONE:
				$this->_updateOneSchedule();
				break;

			case SCHEDU_REVERSE:
				$this->_reverseTeam();
				break;

			case SCHEDU_ANALYSE:
				$this->_displayAnalyse();
				break;

			case SCHEDU_NB_MATCH:
				$this->_displayNbMatch();
				break;
				 
			case WBS_ACT_SCHEDU:
				$this->_displayForm();
				break;

			case SCHEDU_EDIT:
				$this->_displayFormSchedu();
				break;

			case SCHEDU_SELECT:
				$id = kform::getData();
				$this->_displayForm($id);
				break;

			case SCHEDU_SELECT_AUTO:
				$this->_displayAuto();
				break;

			case SCHEDU_AUTO:
				$this->_calcSchedu();
				break;

			case KID_SELECT:
			case SCHEDU_PLACE:
				$this->_displayFormPlace();
				break;

				// Numerotation des matches
			case SCHEDU_SELECT_NUM:
				$this->_displayFormNum();
				break;
			case SCHEDU_UPDATE_NUM:
				$this->_updateNum();
				break;

				// Change the publication status of the calendar
			case KID_CONFIDENT:
				$this->_publiTies(WBS_DATA_CONFIDENT);
				break;
			case KID_PRIVATE:
				$this->_publiTies(WBS_DATA_PRIVATE);
				break;
			case KID_PUBLISHED:
				$this->_publiTies(WBS_DATA_PUBLIC);
				break;


			case SCHEDU_PDF_INDIV:
			case SCHEDU_PDF_ALLINDIV:
			case SCHEDU_PDF_PLAYER:
			case SCHEDU_PDF:
				require_once "scheduPdf.php";
				$pdf = new scheduPdf();
				$pdf->start($page);
				exit;
				break;

			case SCHEDU_CONFIRM:
				$this->_displayFormConfirm();
				break;

			case SCHEDU_RAZ:
				$this->_razSchedu();
				break;

			case SCHEDU_INSERTMATCH:
				$this->_insertMatch();
				exit;
				break;
			case SCHEDU_PAUSE:
				$this->_insertPause();
				exit;
				break;
			case SCHEDU_INVERT_MATCH:
				$this->_invertMatch();
				exit;
				break;
	  case SCHEDU_REPLACEMATCH:
	  	$this->_replaceMatch();
	  	exit;
	  	break;
	  case SCHEDU_DELETEMATCH:
	  	$this->_removeMatch(false);
	  	exit;
	  	break;
	  case SCHEDU_REMOVEMATCH:
	  	$this->_removeMatch(true);
	  	exit;
	  	break;
	  case SCHEDU_EDITTIME:
	  	$this->_displayFormTime();
	  	exit;
	  	break;
	  case SCHEDU_UPDATETIME:
	  	$this->_updateTime();
	  	exit;
	  	break;

	  case SCHEDU_CONVOC:
	  	$this->_displayFormConvoc();
	  	exit;
	  	break;

	  case SCHEDU_CONVOCMAIL:
	  	$this->_displayFormConvoc();
	  	exit;
	  	break;

	  case SCHEDU_CONVOCUPDATE:
	  	$this->_updateLive();
	  	exit;
	  	break;

	  case SCHEDU_PUBLICATION:
	  	$this->_displayFormPubli();
	  	exit;
	  	break;

	  case SCHEDU_EDIT_CONTACT:
	  	$this->_displayFormContact();
	  	exit;
	  	break;

	  case SCHEDU_UPDATE_CONTACT:
	  	$this->_updateContact();
	  	exit;
	  	break;
	  	 
	  case SCHEDU_DISPLAY_BLABLA:
	  	$this->_displayBlabla();
	  	exit;
	  	break;
	  	 
	  default:
	  	echo "page $page demand�e depuis schedu_A<br>";
	  	exit;
		}
	}
	// }}}

	/**
	 * Test : affichage des matchs avec le nombre de match par joueur
	 *
	 * @access private
	 * @return void
	 */
	function _displayBlabla($err='')
	{
		$dt =& $this->_dt;

		$utpage = new utPage('schedu');
		$content =& $utpage->getPage();
		$kdiv =& $content->addDiv('tNbMatch');

		// Match a probleme
		$day = kform::getData();
		$nbs = $dt->getMatchWithNbMatch($day);
		if (count($nbs)) 	$krows =& $kdiv->addRows('rowsBlabla', $nbs);
		else 	$kdiv->addMsg('msgNoProblems');
		$kdiv->addDiv('page', 'blkNewPage');
		$utpage->display();
		exit;
	}
	// }}}


	// {{{ _updateContact()
	/**
	 * Sauvegarde le conatc d'une assos
	 *
	 * @access private
	 * @return void
	 */
	function _updateContact()
	{
		$dt =& $this->_dt;
		$contact = array('ctac_id'      => kform::getInput('ctacId', -1),
		       'ctac_contact' => kform::getInput('ctacContact'),
		       'ctac_value'   => kform::getInput('ctacValue'));
		$team = array('team_id'         => kform::getInput('teamId', -1),
		    'team_textconvoc' => kform::getInput('assoConvoc'));
		$dt->updateContact($contact, $team);

		$page = new utPage('none');
		$page->close();
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
	function _displayFormContact($msg='')
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage('schedu');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fScheduContact', 'schedu', SCHEDU_UPDATE_CONTACT);

		// Initialize the field
		//list($teamId, $ctacId) = explode(';', kform::getData());
		$teamId = kform::getData();
		$ctacId = -1;
		$infos = $dt->getContact($teamId, $ctacId);

		// Display a warning if an error occured
		if (isset($infos['errMsg']))
		$form->addWng($infos['errMsg']);

		$form->addHide("ctacId",  $infos['ctac_id']);
		$form->addHide("teamId",  $infos['team_id']);

		$kedit =& $form->addEdit("ctacContact", $infos['ctac_contact'], 33);
		$kedit->setMaxLength(50);
		$kedit =& $form->addEdit("ctacValue", $infos['ctac_value'], 33);
		$kedit->setMaxLength(50);

		$kedit =& $form->addArea("assoConvoc", $infos['asso_convoc'], 30, 10 );
		$kedit->noMandatory();

		$elts = array('ctacContact', 'ctacValue', 'assoConvoc');
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

	// {{{ _updateLive()
	/**
	 * Positionne le flag d epresence des joueurs a absent jusqu'a l'heure de convocation
	 *
	 * @access private
	 * @return void
	 */
	function _updateLive()
	{
		$dt =& $this->_dt;
		$ute = new utevent();
		$convocation = $ute->getConvoc();
		$dt->updateLive($convocation['evnt_delay']);
		$page = new utPage('none');
		$page->close();
	}
	// }}}

	// {{{ _displayFormConvoc()
	/**
	 * Display a page for convocation
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormConvoc($err='')
	{
		$dt =& $this->_dt;

		$kdiv =& $this->_displayHead('itConvoc');

		$this->_utpage->_page->addAction('onload', array('initConvoc'));
		$this->_utpage->_page->addJavaFile('schedu/schedu.js');

		$form =& $kdiv->addForm('formConvoc', 'schedu');

		// Message d'erreur
		if ($err != '')
		$form->addErr($err);

		// Saisie du delai de convocation
		//$form->addMsg('msgConvoc');
		$ute = new utevent();

		$convoc = $ute->getConvoc();
		$form->addRadio('typeConvoc', $convoc['evnt_convoc'] == WBS_CONVOC_MATCH,
		WBS_CONVOC_MATCH);
		$form->addRadio('typeConvoc', $convoc['evnt_convoc'] == WBS_CONVOC_DRAW,
		WBS_CONVOC_DRAW);
		$elts = array('typeConvoc1', 'typeConvoc2');
		$form->addBlock('blkType', $elts);

		$kedit =& $form->addEdit('delayConvoc',
		kform::getInput('delayConvoc', $convoc['evnt_delay']), 3);

		$kedit =& $form->addEdit('lieuConvoc',
		kform::getInput('lieuConvoc', $convoc['evnt_lieuconvoc']));

		$kedit =& $form->addArea('textConvoc',
		kform::getInput('textConvoc', $convoc['evnt_textconvoc']),60);

		$form->addBtn('btnSave', KAF_AJAJ, 'endSubmit', WBS_ACT_SCHEDU);
		$form->addBtn('btnCancel', 'cancel', 'formConvoc');
		$elts = array('btnSave', 'btnCancel');
		$form->addBlock('divValid', $elts);

		$form->addBtn('btnModif', 'activate', 'formConvoc', 'delayConvoc');
		$form->addBtn('btnStatus', KAF_NEWWIN, 'schedu',
		SCHEDU_CONVOCUPDATE, 0, 600, 200);
		$elts = array('btnModif', 'btnStatus');
		$form->addBlock('divModif', $elts);

		$elts = array('divModif', 'divValid', 'btnStatus');
		$form->addBlock('blkBtn', $elts);

		$elts = array('blkType', 'delayConvoc','lieuConvoc');
		//$form->addBlock('blkData', $elts);

		//$form->addBlock('blkText', 'textConvoc');

		$elts = array('blkType', 'delayConvoc','lieuConvoc', 'textConvoc', 'blkBtn');
		$form->addBlock('blkConvoc', $elts);

		$form->addMsg('msgConvoc');
		$form->addBlock('blkMsg', 'msgConvoc');
		$form->addDiv('brkCmd', 'blkNewPage');

		$form =& $kdiv->addForm('formTeams');

		//$form->addCheck('copie', '1', true);

		$itsm['itConvocMail'] = array(KAF_NEWWIN, 'teams', TEAM_SEND_CONVOC,
		0, 400, 400);
		$form->addMenu("menuLeft", $itsm, -1);
		$form->addDiv('brkMenu', 'blkNewPage');
		$teams = $dt->getTeams();
		$krows =& $form->addRows('rowsTeams', $teams);
		$size[6] = '0+';
		$krows->setSize($size);

		$actions[2] = array(KAF_UPLOAD, 'teams', KID_SELECT, 'team_id');
		$krows->setActions($actions);
		$krows->displaySelect(true);

		$acts = array();
		$acts[] = array( 'link' => array(KAF_NEWWIN, 'teams',
		TEAM_PDF_PLAYERS, 'team_id', 400, 400),
		       'icon' => utimg::getIcon('print'),
		       'title' => 'matchEdit');
		$acts[] = array( 'link' => array(KAF_NEWWIN, 'schedu',
		SCHEDU_EDIT_CONTACT, 'ctac_id', 450, 280),
		       'icon' => utimg::getIcon('edit'),
		       'title' => 'scoresheetEdit');
		$krows->setRowActions($acts);

		$this->_utpage->display();
		exit;
	}
	// }}}

	// {{{ _removeMatch()
	/**
	 * Enleve les match de l'echeancier
	 *
	 * @access private
	 * @return void
	 */
	function _removeMatch($decal)
	{
		$dt =& $this->_dt;

		// Salle
		$position['venue'] = kform::getInput('venue', '');
		// jour
		$day = kform::getInput('day', '');
		$tmp = kform::getInput('scheduList', '');
		if (!is_array($tmp) ) $selected[] = $tmp;
		else $selected = $tmp;

		$msg = '';
		$schedule = reset($selected);
		$selected = array_reverse($selected);
		foreach($selected as $tmp )
		{
			list($time, $court, $tie) = explode(';', $tmp);
			$position['frDateTime'] = "$day $time";
			$position['court'] = $court;
			if ($court != -1)
			{
				$schedule = $dt->deleteMatch($tie, $position, kform::getInput('nbCourt', 5));
				if(!$dt->isStarted()) $dt->updateNum(-1, -1, 1, true, true);
				else $msg  = 'msgMatchEnded';
			}
		}
		$this->_displayFormIndiv($msg, $schedule);
	}
	// }}}

	// {{{ _updateTime()
	/**
	 * Update the time
	 *
	 * @access private
	 * @return void
	 */
	function _updateTime()
	{
		$dt =& $this->_dt;
		$oldVenue= kform::getInput('oldVenue');
		$venue   = kform::getInput('venue');
		$oldDay  = kform::getInput('oldDay');
		$day     = kform::getInput('day');
		$oldTime = kform::getInput('oldTime');
		$time    = kform::getInput('time');
		$length  = kform::getInput('length');
		$propa   = kform::getInput('propagate', false);
		$convoc  = kform::getInput('convoc', $time);
		$propaConvoc   = kform::getInput('propagateConvoc');
		$dt->updateTime($oldVenue, $venue, $oldDay, $day,
		$oldTime, $time, $length, $propa, $convoc, $propaConvoc);
		 
		$page = new utPage('none');
		$page->close(true, 'schedu', WBS_ACT_SCHEDU);
		exit;
	}
	// }}}

	// {{{ _displayFormTime()
	/**
	 * Display the page for changing date
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormTime($err='')
	{
		$dt =& $this->_dt;

		$utpage = new utPage('schedu');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tHour', 'schedu', SCHEDU_UPDATETIME);

		// Initialize the field
		$schedu = kform::getInput('scheduList', '');
		if ($schedu == '')
		{
	  $form->addWng('msgSelectTime');
	  $form->addBtn('btnCancel');
	  $form->addBlock('blkBtn', 'btnCancel');
	  $utpage->display();
	  exit;
		}

		list($time, $court, $match, $length) = explode(';', $schedu);
		if ($length==-1)
		{
	  $form->addWng('msgNotRealTime');
	  $form->addBtn('btnCancel');
	  $form->addBlock('blkBtn', 'btnCancel');
	  $utpage->display();
	  exit;
		}
		$form->addHide('oldVenue', kform::getInput('venue'));
		$form->addHide('oldDay', kform::getInput('day'));
		$form->addHide('oldTime', $time);
		$kedit =& $form->addEdit('venue', kform::getInput('venue'), 20);
		$kedit =& $form->addEdit('day', kform::getInput('day'), 20);
		$kedit =& $form->addEdit('time', $time, 20);
		$kedit =& $form->addEdit('length', $length, 20);
		$form->addCheck('propagate', false);
		$convoc = 45;
		//$kedit =& $form->addEdit('convoc', $convoc, 20);
		//$form->addCheck('propagateConvoc', false);
		$elts = array('venue', 'day', 'time', 'length', 'propagate', 'convoc', 'propagateConvoc');
		$form->addBlock('blkSchedu', $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	/**
	 * Insere les matches dans le calendrier
	 *
	 * @access private
	 * @return void
	 */
	function _insertMatch()
	{
		$dt =& $this->_dt;

		// Salle
		$position['venue'] = kform::getInput('venue', '');
		if ($position['venue'] == -1)	$position['venue'] = kform::getInput('newVenue');
		if ($position['venue'] == '') $this->_displayFormIndiv('msgNeedVenue');

		// jour
		$day = kform::getInput('day', '');
		if ($day == -1) $day = kform::getInput('newDay', '');
		if ($day == '') $this->_displayFormIndiv('msgNeedDay');

		// Match a placer
		$tmp = kform::getInput('matchList', '');
		if ($tmp == '') $this->_displayFormIndiv('msgNeedMatch');
		list($draw, $groupname, $step, $tie, $round) = explode(';', $tmp);
		if ($step != -1) if ($tie==-1) $tie = $dt->getMatchesStep($draw, $groupname, $round, $step);

		// Position
		$tmp = kform::getInput('scheduList', '');
		if ($tmp == '') $this->_displayFormIndiv('msgNeedTime');
		list($time, $court, $match) = explode(';', $tmp);
		if ($court==-1) $this->_displayFormIndiv('msgNeedCourt');
		$position['frDateTime'] = "$day $time";
		$position['court'] = $court;

		// Traiter les match
		$select = $dt->insertMatch($tie, $position, kform::getInput('nbCourt', 5), kform::getInput('length', 30));
		$res = '';
		if(!$dt->isStarted()) $dt->updateNum(-1, -1, 1, true, true);
		else $res  = "msgMatchEnded";

		$this->_displayFormIndiv($res, $select);
	}

	/**
	 * Insere une pause dans le calendrier
	 *
	 * @access private
	 * @return void
	 */
	function _insertPause()
	{
		$dt =& $this->_dt;

		// Salle
		$position['venue'] = kform::getInput('venue', '');
		if ($position['venue'] == -1)	$position['venue'] = kform::getInput('newVenue');
		if ($position['venue'] == '') $this->_displayFormIndiv('msgNeedVenue');

		// jour
		$day = kform::getInput('day', '');
		if ($day == -1) $day = kform::getInput('newDay', '');
		if ($day == '') $this->_displayFormIndiv('msgNeedDay');

		// Pause
		$tie = -1;

		// Position
		$tmp = kform::getInput('scheduList', '');
		if ($tmp == '') $this->_displayFormIndiv('msgNeedTime');
		list($time, $court, $match) = explode(';', $tmp);
		if ($court==-1) $this->_displayFormIndiv('msgNeedCourt');
		$position['frDateTime'] = "$day $time";
		$position['court'] = $court;

		// Traiter les match
		$dt->insertMatch($tie, $position, kform::getInput('nbCourt', 5), kform::getInput('length', 30));
		$res = '';
		if(!$dt->isStarted()) $dt->updateNum(-1, -1, 1, true, true);
		else $res  = "msgMatchEnded";

		$this->_displayFormIndiv($res);
	}

	/**
	 * Inverse deux matchs dans le calendrier
	 *
	 * @access private
	 * @return void
	 */
	function _invertMatch()
	{
		$dt =& $this->_dt;

		// Salle
		$position['venue'] = kform::getInput('venue', '');
		// jour
		$day = kform::getInput('day', '');
		$tmp = kform::getInput('scheduList', '');
		if (!is_array($tmp) ) $selected[] = $tmp;
		else $selected = $tmp;

		$res = '';
		$select = reset($selected);
		if (count($selected)==2)
		{
			$select = $dt->invertMatch($selected);
		}
		$res = '';
		if(!$dt->isStarted()) $dt->updateNum(-1, -1, 1, true, true);
		else $res = 'msgMatchEnded';
		$this->_displayFormIndiv($res, $select);
	}


	// {{{ _replaceMatch()
	/**
	 * Remplace les matches dans le calendrier
	 *
	 * @access private
	 * @return void
	 */
	function _replaceMatch()
	{
		$dt =& $this->_dt;

		// Salle
		$position['venue'] = kform::getInput('venue', '');
		if ($position['venue'] == -1) $position['venue'] = kform::getInput('newVenue');
		if ($position['venue'] == '') $this->_displayFormIndiv('msgNeedVenue');

		// jour
		$day = kform::getInput('day', '');
		if ($day == -1) $day = kform::getInput('newDay', '');
		if ($day == '') $this->_displayFormIndiv('msgNeedDay');

		// Match a placer
		$tmp = kform::getInput('matchList', '');
		if ($tmp == '') $this->_displayFormIndiv('msgNeedMatch');
		list($draw, $groupname, $step, $tie, $round) = explode(';', $tmp);
		if ($step == -1)	$this->_displayFormIndiv('msgNeedMatch');
		if ($tie==-1)	$tie = $dt->getMatchesStep($draw, $groupname, $round, $step);

		// Position
		$tmp = kform::getInput('scheduList', '');
		if ($tmp == '') $this->_displayFormIndiv('msgNeedTime');
		list($time, $court, $match) = explode(';', $tmp);
		if ($court==-1) $this->_displayFormIndiv('msgNeedCourt');
		$position['frDateTime'] = "$day $time";
		$position['court'] = $court;

		// Traiter les match
		$select = $dt->replaceMatch($tie, $position,
		kform::getInput('nbCourt', 5),
		kform::getInput('length', 30));

		$res = '';
		if(!$dt->isStarted()) $dt->updateNum(-1, -1, 1, true, true);
		else $res = 'msgMatchEnded';
		$this->_displayFormIndiv($res, $select);
	}
	// }}}

	// {{{ _displayHead()
	/**
	 * Display a page with the list of the ties
	 * and the field to update the schedule
	 * for an individual event
	 *
	 * @access private
	 * @return void
	 */
	function & _displayHead($select)
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;
		$utr =  new utround();;

		$this->_utpage = new utPage_A('schedu', true, 'itCalendar');
		$content =& $this->_utpage->getContentDiv();
		$kdiv =& $content->addDiv('choix', 'onglet3');

		$items = array();
		$items['itSchedu'] = array(KAF_UPLOAD, 'schedu', WBS_ACT_SCHEDU);
		$items['itConvoc'] = array(KAF_UPLOAD, 'schedu', SCHEDU_CONVOC);
		$items['itPubli'] = array(KAF_UPLOAD, 'schedu', SCHEDU_PUBLICATION);

		// Get dates and venues
		$places = $dt->getPlaces();
		unset($places['errMsg']);
		foreach ($places as $place)
		$items[$place] = array(KAF_UPLOAD, 'schedu',
		SCHEDU_PLACE, $place);

		$kdiv->addMenu("menuType", $items, $select);
		$kdiv =& $content->addDiv('groups', 'cont3');
		return $kdiv;
	}
	// }}}

	/**
	 * Affiche le nombre de match par joueur
	 *
	 * @access private
	 * @return void
	 */
	function _displayNbMatch($err='')
	{
		$dt =& $this->_dt;

		$utpage = new utPage('schedu');
		$content =& $utpage->getPage();
		$kdiv =& $content->addDiv('tNbMatch');

		// Match a probleme
		$day = kform::getData();
		$nbs = $dt->nbMatchPlayer($day);
		if (count($nbs)) 	$krows =& $kdiv->addRows('rowsNbs', $nbs);
		else 	$kdiv->addMsg('msgNoProblems');
		$kdiv->addDiv('page', 'blkNewPage');
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayAnalyse()
	/**
	 * Affiche l'analyse de l'echeancier
	 *
	 * @access private
	 * @return void
	 */
	function _displayAnalyse($err='')
	{
		$dt =& $this->_dt;

		$utpage = new utPage('schedu');
		$content =& $utpage->getPage();
		$kdiv =& $content->addDiv('tAnalyse');

		// Match a probleme
		$pbs = $dt->scanSchedule();
		if (count($pbs)) $krows =& $kdiv->addRows('rowsPbs', $pbs);
		else 	$kdiv->addMsg('msgNoProblems');
		$kdiv->addDiv('page', 'blkNewPage');
		$utpage->display();
		exit;
	}
	// }}}

	/**
	 * Display a page with the list of the ties
	 * and the field to update the schedule
	 * for an individual event
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormIndiv($err='', $aSelect=null)
	{
		$dt =& $this->_dt;
		$utev = new utEvent();
		$eventId = utvars::getEventId();
		$event = $utev->getEvent($eventId);

		$kdiv =& $this->_displayHead('itSchedu');
		$form =& $kdiv->addForm('tScheduNew', 'schedu', SCHEDU_AUTO);

		// Saisie de la salle
		$places = $dt->getPlaces();
		$items = array();
		foreach ($places as $place) $items[$place] = $place;
		$items[-1] = "Nouvelle salle";
		$newVenue = kform::getInput('newVenue', '');
		if ($newVenue != '')
		if (in_array($newVenue, $places))
		{
			$venue = $newVenue;
			$newVenue = '';
		}
		else  $venue = -1;
		else
		{
	  $venue = kform::getInput('venue');
	  if (!array_key_exists($venue, $items)) $venue = key($items);
		}

		$kcombo =& $form->addCombo('venue', $items, $items[$venue]);
		$acts[1] = array(KAF_UPLOAD, 'schedu',  WBS_ACT_SCHEDU);
		$kcombo->setActions($acts);
		$kedit =& $form->addEdit('newVenue', $newVenue, 20);
		$kedit->noMandatory();
		$elts = array('venue', 'newVenue');
		$form->addBlock('scheduVenue', $elts);

		// Saisie du jour
		$days = $dt->getDates($venue);
		$kdays = array_keys($days);
		$days[-1] = "Nouvelle date";
		$newDay = kform::getInput('newDay', '');
		if ($newDay != '' || $venue == -1)
		{
			$utd = new utDate();
			if (empty($newDay)) $utd->setIsoDate($event['evnt_firstday']);
			else $utd->setFrDate($newDay);
			$newDay = $utd->getIsoDate();
			list($y,$m,$d) = explode('-', $newDay);
			$newDay = "$d-$m-$y";
			if (in_array($newDay, $kdays))
			{
				$day = $newDay;
				$newDay = '';
			}
			else $day = -1;
		}
		else
		{
	  		$day = kform::getInput('day', key($days));
	  		if (!array_key_exists($day, $days)) $day = key($days);
		}
		$kcombo =& $form->addCombo('day', $days, $days[$day]);
		$acts[1] = array(KAF_UPLOAD, 'schedu',  WBS_ACT_SCHEDU);
		$kcombo->setActions($acts);
		$kedit =& $form->addEdit('newDay', $newDay, 10);
		$kedit->noMandatory();
		$elts = array('day', 'newDay');
		$form->addBlock('scheduDay', $elts);

		$elts = array('scheduVenue', 'scheduDay');
		$form->addBlock('blkVenueDay', $elts);

		if ($day == -1) $start = "09:00";
		else $start = $dt->getFirstTime($venue, $day);
		$nbCourt = kform::getInput('nbCourt', $dt->getNbCourt($venue, $day));
		//$nbCourt = $dt->getNbCourt($venue, $day);
		$kedit =& $form->addEdit('nbCourt', $nbCourt, 2);
		$kedit->noMandatory();
		$elts = array('nbCourt');
		$form->addBlock('scheduInfo', $elts);
		$form->addDiv('page2', 'blkNewPage');

		// Message d'erreur
		if ($err != '') $form->addErr($err);

		// Liste des tableaux
		$draws = $dt->getDraws();
		$drawId = kform::getInput('drawList', key($draws));
		if (!array_key_exists($drawId, $draws)) $drawId = key($draws);
		if ($drawId=='')	$select = '';
		else	$select = $draws[$drawId];
		$length = max(count($draws), 15);
		$kcombo=& $form->addCombo('drawList', $draws, $select);
		$kcombo->setLength($length);
		$acts[1] = array(KAF_UPLOAD, 'schedu',  WBS_ACT_SCHEDU);
		$kcombo->setActions($acts);

		// Liste des matches pour le tableau selectionne
		$matchs = $dt->getMatches($drawId, $day);
		$match = kform::getInput('matchList');
		if (!isset($matchs[$match]))
		{
	  		reset($matchs);
	  		$match = next($matchs);
	  		$select = $match['value'];
		}
		else $select = $match;
		$kcombo=& $form->addCombo('matchList', $matchs, $select);
		$kcombo->setLength($length);

		// Boutons de commandes
		$items = array();
		$items['btnInsert']  = array(KAF_UPLOAD, 'schedu', SCHEDU_INSERTMATCH);
		$items['btnPause']   = array(KAF_UPLOAD, 'schedu', SCHEDU_PAUSE);
		$items['btnReplace'] = array(KAF_UPLOAD, 'schedu', SCHEDU_REPLACEMATCH);
		$items['btnErase']   = array(KAF_UPLOAD, 'schedu', SCHEDU_DELETEMATCH);
		$items['btnInvert']   = array(KAF_UPLOAD, 'schedu', SCHEDU_INVERT_MATCH);
		$items['btnRaz']     = array(KAF_NEWWIN, 'schedu', SCHEDU_CONFIRM, SCHEDU_RAZ, 350, 100);
		//$items['btnRemove']  = array(KAF_UPLOAD, 'schedu', SCHEDU_REMOVEMATCH);
		$items['btnTime']    = array(KAF_NEWWIN, 'schedu', SCHEDU_EDITTIME, 0, 400, 300);
		$items['btnAuto']    = array(KAF_NEWWIN, 'schedu', SCHEDU_SELECT_AUTO, 0, 650, 300);

		$items['itPdf'] = array(KAF_NEWWIN, 'schedu', SCHEDU_PDF_INDIV,
		$venue, 500, 400);
		$items['itAnalyse'] = array(KAF_NEWWIN, 'schedu', SCHEDU_ANALYSE, $venue, 500, 400);
		$items['itNbMatch'] = array(KAF_NEWWIN, 'schedu', SCHEDU_NB_MATCH, $day, 500, 400);
		$items['itBlabla'] = array(KAF_NEWWIN, 'schedu', SCHEDU_DISPLAY_BLABLA, $day, 500, 400);
		if (count($places)>1)
		$items['itPlayerPdf'] = array(KAF_NEWWIN, 'schedu', SCHEDU_PDF_PLAYER, 0, 500, 400);
		$items['btnNumber'] = array(KAF_NEWWIN, 'schedu', SCHEDU_SELECT_NUM, 0, 600, 200);
		$form->addMenu('menuSchedu', $items, -1, 'classMenuBtn');

		// Echeancier
		$matchs = $dt->getSchedule($venue, $day, $start,"23:00", 30, $nbCourt);
		//print_r($matchs);
		$firstFreePos = array_pop($matchs);
		$select = empty($aSelect) ? kform::getInput('scheduSelect', $firstFreePos) : $aSelect;
		$form->addHide('scheduSelect', $select);
		$kcombo=& $form->addCombo('scheduList', $matchs, $select);
		$kcombo->setLength(30);
		$kcombo->setMultiple();

		$form->addDiv('page', 'blkNewPage');
		$this->_utpage->display();
		exit;
	}

	/**
	 * Display the page for confirmation the destruction
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormConfirm($err='')
	{
		$dt =& $this->_dt;

		$utpage = new utPage('schedu');
		$content =& $utpage->getPage();
		$act = kform::getData();
		$form =& $content->addForm('tConfirmation', 'schedu', $act);

		$kedit =& $form->addHide('venue', kform::getInput('venue', ''));
		$kedit =& $form->addHide('day', kform::getInput('day', ''));
		$kedit =& $form->addHide('start', kform::getInput('start', ''));
		$kedit =& $form->addHide('end', kform::getInput('end', ''));
		$kedit =& $form->addHide('length', kform::getInput('length', 30));
		$kedit =& $form->addHide('nbCourt', kform::getInput('nbCourt', 5));
		$kedit =& $form->addHide('matchList', kform::getInput('matchList', ''));
		$kedit =& $form->addHide('scheduList', kform::getInput('scheduList', ''));
		// Initialize the field
		if ($err =='')
		{
	  $form->addMsg("msgConfirm$act");
	  $form->addBtn('btnDelete', KAF_SUBMIT);
		}
		else
		{
	  $form->addWng($err);
		}
		$form->addBtn('btnCancel');
		$elts = array('btnDelete', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _razSchedu()
	/**
	 * Effacer le calendrier du jour
	 *
	 * @access private
	 * @return void
	 */
	function _razSchedu()
	{
		$dt =& $this->_dt;

		$dt->razSchedu(kform::getInput('venue', ''),
		kform::getInput('day', ''));
		 
		$page = new utPage('none');
		$page->close(true, 'schedu', WBS_ACT_SCHEDU);
		exit;
	}
	// }}}

	// {{{ _calcSchedu()
	/**
	 * Calcule automatiquement le planning
	 */
	function _calcSchedu()
	{
		$infos['nbCourt'] = kform::getInput('nbCourt');
		$infos['length'] = kform::getInput('length');
		$infos['sporthall'] = kform::getInput('tiesPlace');
		$infos['firstDay'] = kform::getInput('firstDay');
		$infos['start'] = kform::getInput('startOne');
		$infos['break'] = kform::getInput('endOne');
		$infos['nbday'] = kform::getInput('nbday');
		$infos['rest'] = kform::getInput('rest');
		$infos['raz'] = kform::getInput('raz', false);
		$infos['drawIds'] = kform::getInput('drawList');
		$rows = $this->_dt->initAutoSchedu($infos);
		$this->_dt->updateNum(-1, -1, 1, true, true);
		$page = new utPage('none');
		$page->close(true, 'schedu', WBS_ACT_SCHEDU);
		exit;
	}
	// }}}

	// {{{ _DisplayAuto()
	/**
	 * Affiche la page de saisie de info pour le plannig automatique
	 */
	function _displayAuto()
	{
		$dt =& $this->_dt;
		
		$utpage = new utPage('schedu');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tScheduAuto', 'schedu', SCHEDU_AUTO);

		// Liste des tableaux
		$draws = $dt->getDraws();
		$kcombo=& $form->addCombo('drawList[]', $draws);
		$kcombo->setLength(14);
		$kcombo->setMultiple();
		$kcombo->noMandatory();
		
		// Displaying fields
		$places = $this->_dt->getPlaces();
		if (count($places)) $sporthall = reset($places);
		else $sporthall = "";
		$kedit =& $form->addEdit('tiesPlace', $sporthall);
		$kedit =& $form->addEdit('nbCourt', kform::getInput('nbCourt',5));
		$kedit =& $form->addEdit('length', 30);
		$kedit =& $form->addEdit('rest', 20);
		
		$dates = $this->_dt->getDateTies('', $sporthall);
		if (count($dates)) $date = key($dates);
		
		else $date = "";

		$kedit =& $form->addEdit('firstDay', $date);
		$kedit->setMaxLength(10);
		$kedit =& $form->addEdit('startOne', "09:00");
		$kedit->setMaxLength(5);
		$kedit =& $form->addEdit('endOne', "19:00");
		$kedit->setMaxLength(5);
		$kedit =& $form->addEdit('nbday', "2");
		$kedit->setMaxLength(5);
		$form->addCheck('raz', true, true);
		
		$elts= array('tiesPlace', 'nbCourt', 'length', 'rest', 'firstDay',
		   'startOne', 'endOne', 'nbday', 'raz');
		$form->addBlock('blkSchedu', $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _publiTies()
	/**
	 * Modifie le status de publicaton des rencontre pour le calendrier
	 */
	function _publiTies($status)
	{
		require_once "utils/utpubli.php";
		$utp = new utpubli();

		$tiesId = kform::getInput('tiesPubli', array());
		$utp->publiTies($tiesId, $status);
		$page = new utPage('none');
		if (utvars::isTeamEvent())
		$page->close(true, 'schedu', SCHEDU_SELECT, kform::getInput('selectList'));
		else
		$page->close();
		exit();
	}
	//}}}

	// {{{ _updateNum()
	/**
	 * Update the numbers of the match
	 *
	 * @access private
	 * @param  integer $tieId  Id of the tie
	 * @return void
	 */
	function _updateNum()
	{
		$ut = $this->_ut;
		$dt =& $this->_dt;

		// Obtaining informations
		$date = kform::getInput('tiesDate');
		$place = kform::getInput('tiesPlace');
		$firstNum = kform::getInput('firstNum');
		$option  =  kform::getInput('numOption',  false);
		$byVenue =  false;
		$byDay   =  false;
		if ($option == 2)
		$byVenue =  true;
		if ($option == 3)
		{
	  $byVenue =  true;
	  $byDay = true;
		}
		$dt->updateNum($date, $place, $firstNum,
		$byVenue, $byDay);

		//Close the windows
		$page = new utPage('none');
		$page->close(true, 'schedu', WBS_ACT_SCHEDU);
		exit;
	}
	// }}}

	// {{{ _displayFormNum()
	/**
	 * Display a page to select num order of the matches
	 *
	 * @access private
	 * @param  integer $tieId  Id of the tie
	 * @return void
	 */
	function _displayFormNum()
	{
		$ut = $this->_ut;
		$dt =& $this->_dt;

		// Creating a new page
		$utpage = new utPage('schedu');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tScheduNum', 'schedu', SCHEDU_UPDATE_NUM);

		if($this->_dt->isStarted())
		$form->addWng('msgMatchEnded');

		// Obtaining informations
		$dates = $dt->getTiesDate();
		$form->addCombo('tiesDate', $dates, $dates[-1]);
		$places = $dt->getTiesInfo('tie_place');
		$form->addCombo('tiesPlace', $places, $places[-1]);

		$kedit =& $form->addEdit('firstNum', 1, 4);
		$kedit->setMaxLength(4);
		$form->addRadio('numOption', false, 1);
		$form->addRadio('numOption', false, 2);
		$form->addRadio('numOption', true, 3);

		$elts= array('tiesDate', 'tiesPlace', 'firstNum', 'numOption1',
      		   'numOption2', 'numOption3');
		$form->addBlock('blkSchedu', $elts);


		$form->addBtn('btnNumber', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnNumber', 'btnCancel');
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _reverseTeam()
	/**
	 * Rverse the order of team (VISITOR, RECEIVER) the selected ties
	 *
	 * @access private
	 * @return void
	 */
	function _reverseTeam()
	{
		$dt =& $this->_dt;
		$utt =  new utteam();

		$tiesId = kform::getInput("tiesSchedu");
		if (is_array($tiesId))
		{
	  $dt->reverseTeam($tiesId);
		}

		$this->_displayForm();
	}
	// }}}

	// {{{ _updateSchedule()
	/**
	 * Display a page with the list of the ties
	 * and the field to update the schedule
	 *
	 * @access private
	 * @return void
	 */
	function _updateSchedule()
	{
		$dt =& $this->_dt;
		$utt =  new utteam();;

		if (kform::getInput("selectSchedule",-1) != -1)
		{
	  $schedule = kform::getInput("tieSchedule");
	  $utd =  new utdate();
	  $utd->setFrDate($schedule);
	  $date = $utd->getIsoDateTime();
		}
		else
		$date = null;

		if (kform::getInput("selectPlace",-1) != -1)
		$place  = kform::getInput("tiePlace");
		else
		$place =null;

		if (kform::getInput("selectStep",-1) != -1)
		$step   = kform::getInput("tieStep");
		else
		$step = null;

		if (kform::getInput("selectCourt",-1) != -1)
		$court  = kform::getInput("tieCourt");
		else
		$court = null;

		$tiesId = kform::getInput("tiesSchedu");
		if (is_array($tiesId))
		{
	  $dt->updateSchedule($tiesId, $date,
	  $place, $step, $court);
		}

		$page = new utPage('none');
		$page->clearCache();
		$this->_displayForm();
	}
	// }}}

	// {{{ _updateOneSchedule()
	/**
	 * Register schedule of a tie
	 *
	 * @access private
	 * @return void
	 */
	function _updateOneSchedule()
	{
		$dt =& $this->_dt;
		$utt =  new utteam();;

		$schedule = kform::getInput("tieSchedule");
		$utd =  new utdate();
		$utd->setFrDate($schedule);
		$place  = kform::getInput("tiePlace");
		$step   = kform::getInput("tieStep");
		$court  = kform::getInput("tieCourt");
		$matchNum  = kform::getInput("matchNum");

		$tiesId[] = kform::getInput('tieId');
		$dt->updateSchedule($tiesId, $utd->getIsoDateTime(),
		$place, $step, $court, $matchNum);

		//Close the windows
		$page = new utPage('none');
		if (utvars::isTeamEvent())
		$page->close(true, 'schedu', WBS_ACT_SCHEDU);
		else
		$page->close();
		exit;
	}
	// }}}

	/**
	 * Display a page with the list of the ties
	 * and the field to update the schedule
	 *
	 * @access private
	 * @return void
	 */
	function _displayForm($divSel='')
	{
		if (utvars::isTeamEvent()) $this->_displayFormTeam($divSel);
		else $this->_displayFormIndiv($divSel);
	}

	/**
	 * Display a page with the list of the ties
	 * and the field to update the schedule
	 * for an individual event
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormPlace()
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;
		$utr =  new utround();;

		$place = kform::getData();
		$kdiv =& $this->_displayHead($place);

		$itsm['itPdf'] = array(KAF_NEWWIN, 'schedu', SCHEDU_PDF_INDIV,
		$place, 500, 400);
		$kdiv->addMenu('menuLeft', $itsm, -1);
		$kdiv->addDiv('page', 'blkNewPage');

		// Display the schedule for each time
		$rows = array();
		$dates = $dt->getDateTies('', $place);
		$i = 1;
		foreach ($dates as $date=>$fullDate)
		{
	  $rows[]= array(KOD_BREAK, "title", $fullDate);
	  $ties = $dt->getTiesIndiv($date, $place);
	  foreach($ties as $tie)
	  {
	  	if (count($tie)<8)
	  	$rows[] = $tie;
	  	else
	  	{
	  		$cpt = 0;
	  		$time = $tie[1];
	  		foreach($tie as $cell)
	  		{
	  			if ($cpt==8)
	  			{
	  				$rows[] = $tmp;
	  				$tmp = array();
	  				$tmp[]="0";
	  				$tmp[]="";//$time;
	  				$cpt = 2;
	  			}
	  			$cpt++;
	  			$tmp[] = $cell;
	  		}
	  		$rows[] = $tmp;
	  		$tmp = array();
	  	}
	  }
		}

		if (count($rows) <= count($dates))
		$kdiv->addWng("msgNoCalendar");
		else
		{
	  //$kdiv2 =& $kdiv->addDiv('divDetail', 'blkDatas');
	  //$kdiv2->addMsg('tDrawRecap', '', 'kTitre');
	  $krow =& $kdiv->addRows("scheList", $rows);
	  $krow->displayTitle(false);
	  $krow->displayNumber(false);
		}

		$this->_utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormPubli()
	/**
	 * Display a page with the list of the ties
	 * for publication for an individual event
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormPubli($drawId=false)
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;
		$utr =  new utround();;
		$kdiv =& $this->_displayHead('itPubli');

		$itsp['itConfident'] = array(KAF_NEWWIN, 'schedu',
		KID_CONFIDENT, 0, 350, 180);
		$itsp['itPrivate']   = array(KAF_NEWWIN, 'schedu',
		KID_PRIVATE, 0, 350, 180);
		$itsp['itPublic'] = array(KAF_NEWWIN, 'schedu',
		KID_PUBLISHED, 0, 350, 180);

		//$kdiv->addMenu('menuLeft', $itsp, -1);

		$form =& $kdiv->addForm('fties', 'schedu', SCHEDU_UPDATE);

		$utd = new utDraw();
		$sort = array(5,4);
		$draws = $utd->getDraws($sort);
		unset($draws['errMsg']);
		foreach($draws as $theDraw)
		$list[$theDraw['draw_id']] = $theDraw['draw_name'];
		$list[-1] = '------';

		if ($drawId == false)
		$drawId = kform::getInput('drawList', key($list));

		//--- List of the groups (ie round in the database)
		//of the selected division
		$displayAll = true;
		if ($drawId == -1)
		$rounds = $utr->getRounds();
		else
		{
	  $rounds = $utr->getRounds($drawId);
		}
		$rows = array();
		foreach($rounds as $round)
		{
	  //Display the table with tie schedule
	  $ties = $dt->getRoundTies($round['rund_id'], $displayAll);
	  if (count($ties) &&
	  !isset($ties['errMsg']))
	  {
	  	$first = $rows;
	  	$first[] = array(KOD_BREAK, "title",
	  	$round['draw_name'].' '.$round['rund_name']);
	  	$rows = array_merge($first, $ties);
	  }
		}
		// Add the list of the draws
		$kcombo=& $form->addCombo('drawList', $list, $list[$drawId]);
		$acts[1] = array(KAF_UPLOAD, 'schedu', SCHEDU_PUBLICATION);
		$kcombo->setActions($acts);

		if (count($rows))
		{
	  $itspi['itPublic'] = array(KAF_NEWWIN, 'schedu',
	  KID_PUBLISHED, 0, 350, 180);
	  $itspi['itPrivate']   = array(KAF_NEWWIN, 'schedu',
	  KID_PRIVATE, 0, 350, 180);
	  $itspi['itConfident'] = array(KAF_NEWWIN, 'schedu',
	  KID_CONFIDENT, 0, 350, 180);
	  $form->addMenu('menuRight', $itspi, -1);
	  $form->addDiv('brkMenu', 'blkNewPage');
	  $krows =& $form->addRows("tiesPubli", $rows);
	  $krows->setSort(0);

	  $img[1]='tie_pbl';
	  $krows->setLogo($img);

	  $size[6] = '0';
	  $size[8] = '0+';
	  $krows->setSize($size);
	  $actions[0] = array(KAF_NEWWIN, 'schedu',
	  SCHEDU_EDIT, 0, 400, 180);
	  $actions[7] = array(KAF_NEWWIN, 'matches',
	  KID_EDIT, 'mtch_id', 620, 350);
	  $krows->setActions($actions);
	  $krows->displaySelect(true);

		}
		$form->addDiv('page', 'blkNewPage');
		$this->_utpage->display();
		exit;
	}
	// }}}


	// {{{ _displayFormTeam()
	/**
	 * Display a page with the list of the ties
	 * and the field to update the schedule
	 * for an team event
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormTeam($divSel='')
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;
		$utt =  new utteam();;
		$utpage = new utPage_A('schedu', true, 'itCalendar');
		$kcontent =& $utpage->getContentDiv();
		$kcontent->addDiv('choix', 'onglet3');
		$content =& $kcontent->addDiv('register', 'cont3');

		$itsp['itConfident'] = array(KAF_NEWWIN, 'schedu',
		KID_CONFIDENT, 0, 350, 180);
		$itsp['itPrivate']   = array(KAF_NEWWIN, 'schedu',
		KID_PRIVATE, 0, 350, 180);
		$itsp['itPublic'] = array(KAF_NEWWIN, 'schedu',
		KID_PUBLISHED, 0, 350, 180);
		$content->addMenu('menuLeft', $itsp, -1);

		// Adding a menu with the list of divisions
		// (ie the draws in the database)
		$divs = $utt->getDivs();
		if (isset($divs['errMsg']))
		{
	  $content->addWng($divs['errMsg']);
	  unset($divs['errMsg']);
		}
		if (count($divs))
		{
	  if ( $divSel == '' || !isset($divs[$divSel]))
	  {
	  	$first = each($divs);
	  	$divSel = kform::getInput('divId',$first[0]);
	  }
	   
	  foreach ($divs as $idDiv=>$nameDiv) $list[$idDiv] = $nameDiv;
	  $kcombo=& $content->addCombo('selectList', $list, $divs[$divSel]);
	  $acts[1] = array(KAF_UPLOAD, 'schedu', SCHEDU_SELECT);
	  $kcombo->setActions($acts);
		}
		else
		{
	  $content->addWng('msgNoDivs');
	  $utpage->display();
	  exit;
		}

		$kdiv =& $content;

		$form =& $kdiv->addForm('fties', 'schedu', SCHEDU_UPDATE);
		$form->addHide('divId', $divSel);
		//--- List of the groups (ie round in the database)
		//of the selected division
		$groups = $utt->getGroups($divSel);
		$rows = array();
		foreach($groups as $groupId=>$group)
		{
	  		//Display the table with tie schedule
	  		$teams = $dt->getTiesSchedu($groupId);
	  		if (count($teams) && !isset($teams['errMsg']))
	  		{
	  			$first = $rows;
	  			$first[] = array(KOD_BREAK, "title", $group['rund_name']);
	  			$rows = array_merge($first, $teams);
	  		}
		}
		if (count($rows))
		{
	  $schedule = kform::getInput('tieSchedule');
	  $place  = kform::getInput('tiePlace');
	  $step   = kform::getInput('tieStep');
	  $court  = kform::getInput('tieCourt');
	  $form->addCheck('selectSchedule');
	  $kedit =& $form->addEdit('tieSchedule', $schedule, 25);
	  $kedit->setMaxLength(30);
	  $kedit->noMandatory();
	  $elts = array('tieSchedule', 'selectSchedule');
	  $form->addBlock("blkSchedule", $elts, "classSchedu");

	  $form->addCheck('selectStep');
	  $kedit =& $form->addEdit("tieStep", $step, 25);
	  $kedit->setMaxLength(30);
	  $kedit->noMandatory();
	  $elts = array('tieStep', 'selectStep');
	  $form->addBlock("blkStep", $elts, "classSchedu");

	  $form->addCheck('selectPlace');
	  $kedit =& $form->addEdit('tiePlace', $place, 25);
	  $kedit->setMaxLength(30);
	  $kedit->noMandatory();
	  $elts = array('tiePlace', 'selectPlace');
	  $form->addBlock("blkPlace", $elts, "classSchedu");

	  $form->addCheck('selectCourt');
	  $kedit =& $form->addEdit('tieCourt', $court, 25);
	  $kedit->setMaxLength(10);
	  $kedit->noMandatory();
	  $elts = array('tieCourt', 'selectCourt');
	  $form->addBlock("blkCourt", $elts, "classSchedu");

	  $form->addBtn('btnRegister', KAF_SUBMIT);
	  $form->addBtn('btnReverse', KAF_VALID, 'schedu', SCHEDU_REVERSE);
	  $form->addBtn('btnPdf', KAF_NEWWIN, 'schedu', SCHEDU_PDF, $divSel,
			600, 600);
	  $form->addBtn('btnNumber', KAF_NEWWIN, 'schedu',
			SCHEDU_SELECT_NUM, 0, 600, 200);
			 
	  $elts = array('blkSchedule', 'blkStep', 'blkPlace', 'blkCourt');
	  $form->addBlock("blkSchedu", $elts);
	  $elts = array('selectSchedule', 'selectStep', 'selectPlace',
			'selectCourt');
	  $form->addBlock("blkSelect", $elts);
	  $elts = array('btnRegister', 'btnReverse', 'btnNumber', 'btnPdf');
	  $form->addBlock("blkBtn", $elts);

	  $its['itPublic'] = array(KAF_NEWWIN, 'schedu',
	  KID_PUBLISHED, 0, 350, 180);
	  $its['itPrivate']   = array(KAF_NEWWIN, 'schedu',
	  KID_PRIVATE, 0, 350, 180);
	  $its['itConfident'] = array(KAF_NEWWIN, 'schedu',
	  KID_CONFIDENT, 0, 350, 180);
	  $form->addMenu('menuRight', $its, -1);
	  $form->addDiv('page', 'blkNewPage');

	  //$kdiv2 =& $form->addDiv('blkTieList');
	  $krows =& $form->addRows("tiesSchedu", $rows);
	  $krows->setSort(0);

	  $size[9] = '0+';
	  $krows->setSize($size);

	  $actions[0] = array(KAF_NEWWIN, 'schedu',
	  SCHEDU_EDIT, 0, 400, 180);
	  $actions[8] = array(KAF_UPLOAD, 'ties',
	  KID_SELECT);
	  $krows->setActions($actions);
	  $krows->displaySelect(true);

	  $img[1]='pbl';
	  $krows->setLogo($img);


		}
		else
		{
	  $form->addWng("msgNoGroups");
		}
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormSchedu()
	/**
	 * Display a page to set the schedule of a tie
	 *
	 * @access private
	 * @param  integer $tieId  Id of the tie
	 * @return void
	 */
	function _displayFormSchedu()
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;
		$utev = new utEvent();

		$eventId = utvars::getEventId();
		// Creating a new page
		$utpage = new utPage('schedu');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tScheduEdit', 'schedu', SCHEDU_UPDATE_ONE);

		// Obtaining informations
		$tieId = kform::getData();
		$form->addHide('tieId', $tieId);
		$data = $dt->getTieSchedule($tieId);

		if ($data['tie_schedule'] == '')
		$schedule = kform::getInput('tieSchedule', $data['tie_schedule']);
		else
		$schedule = $data['tie_schedule'];
		if ($data['tie_place'] == '')
		$place  = kform::getInput('tiePlace', $data['tie_place']);
		else
		$place = $data['tie_place'];
		if ($data['tie_step'] == '')
		$step   = kform::getInput('tieStep', $data['tie_step']);
		else
		$step = $data['tie_step'];
		if ($data['tie_court'] == '')
		$court  = kform::getInput('tieCourt', $data['tie_court']);
		else
		$court  = $data['tie_court'];

		$kedit =& $form->addEdit('tieSchedule', $schedule, 25);
		$kedit->setMaxLength(30);
		$kedit->noMandatory();
		if ($utev->getEventType($eventId)!= WBS_EVENT_INDIVIDUAL)
		{
	  $kedit =& $form->addEdit("tieStep", $step, 25);
	  $kedit->setMaxLength(30);
		}
		$kedit =& $form->addEdit('tiePlace', $place, 25);
		$kedit->setMaxLength(30);
		$kedit =& $form->addEdit('tieCourt', $court, 25);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('matchNum', $data['mtch_num'], 5);
		$kedit->setMaxLength(5);
		$kedit->noMandatory();
		$elts= array('tieSchedule', 'tieStep', 'tiePlace', 'tieCourt',
		   'matchNum');
		$form->addBlock('blkSchedu', $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);
		//$form->addBtn('btnReverse', KAF_VALID, 'schedu', SCHEDU_REVERSE);

		$utpage->display();
		exit;
	}
	// }}}
}

?>
