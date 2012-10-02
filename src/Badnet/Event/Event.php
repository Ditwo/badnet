<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Event.inc';
require_once 'Badnetadm/Badnetadm.inc';
require_once 'Badnet/Adm/Adm.inc';
require_once 'Badnet/Preference/Preference.inc';
require_once 'Badnet/Team/Team.inc';

/**
 * Module de gestion des tournois
 */
class Event
{
	// {{{ properties
	public function __construct()
	{
		$userId = Bn::getValue('user_id');
		if (empty($userId) ) return BNETADM_LOGOUT;
		$controller = Bn::getController();

		$controller->addAction(BEVENT_PAGE_EVENTS,  $this, 'pageEvents');
		$controller->addAction(BEVENT_FILL_EVENTS,  $this, 'fillEvents');
		$controller->addAction(BEVENT_PAGE_DELETE_EVENT,  $this, 'pageDeleteEvent');
		$controller->addAction(BEVENT_DELETE_EVENT,       $this, 'deleteEvent');
		$controller->addAction(BEVENT_PAGE_NEW_EVENT,     $this, 'pageNewEvent');
		$controller->addAction(BEVENT_CREATE_EVENT,       $this, 'createEvent');
		$controller->addAction(BEVENT_FILL_NATURE,        $this, 'fillNature');

		$controller->addAction(BEVENT_EMPTY_CACHE,        $this, 'emptyCache');
		$controller->addAction(BEVENT_PUBLI_EVENT,        $this, 'publiEvent');

		$controller->addAction(BEVENT_FILL_POSTITS,       $this, 'fillPostits');
		$controller->addAction(BEVENT_PAGE_DELETE_POSTIT, $this, 'pageDeletePostit');
		$controller->addAction(BEVENT_DELETE_POSTIT,      $this, 'deletePostit');
		$controller->addAction(BEVENT_PAGE_POSTIT,        $this, 'pagePostit');
		$controller->addAction(BEVENT_UPDATE_POSTIT,      $this, 'updatePostit');

		$controller->addAction(BEVENT_DISPATCH,            $this, 'dispatch');
		$controller->addAction(BEVENT_PAGE_MANAGER_EVENT,  $this, 'pageManagerEvent');
		$controller->addAction(BEVENT_PAGE_MANAGER_IC,     $this, 'pageManagerIc');
		$controller->addAction(BEVENT_PAGE_REFEREE,        $this, 'pageReferee');
		$controller->addAction(BEVENT_PAGE_VISITOR,        $this, 'pageVisitor');

		$controller->addAction(BEVENT_GOTO_DIVISIONS, $this, 'gotoDivision');
		$controller->addAction(BEVENT_GOTO_TEAMS,     $this, 'gotoTeams');
		$controller->addAction(BEVENT_GOTO_PLAYERS,   $this, 'gotoPlayers');
		$controller->addAction(BEVENT_GOTO_TIES,      $this, 'gotoTies');
		$controller->addAction(BEVENT_GOTO_CALENDAR,  $this, 'gotoCalendar');
		$controller->addAction(BEVENT_GOTO_DRAWS,     $this, 'gotoDraws');
		$controller->addAction(BEVENT_GOTO_LIVE,      $this, 'gotoLive');
		$controller->addAction(BEVENT_GOTO_VISIT,     $this, 'gotoVisit');
	}


	// @todo provisoire: redirection vers l'ancien BadNet
	// tous les goto doivent disparaitre a terme
	private function _goto($aPage, $aAction)
	{
		$eventId = Bn::getValue('event_id');
            if ( empty($eventId) ) $eventId = Bn::getValue('eventId');
		$_SESSION['wbs']['theme'] = 2;
		$_SESSION['wbs']['themeId'] = $eventId;

		$url = $_SERVER['PHP_SELF']
		. '?kpid=' . $aPage
		. '&kaid=' . $aAction;
		//print_r($GLOBALS);
		header("Location: $url");
		return false;
	}

	public function gotoDivision() { $this->_goto('divs', 650);}
	public function gotoTeams()	   {$this->_goto('teams', 550);}
	public function gotoPlayers()  {$this->_goto('regi', 700);}
	public function gotoTies() 	   {$this->_goto('ties', 600);}
	public function gotoCalendar() {$this->_goto('schedu', 1100);}
	public function gotoDraws()    {$this->_goto('draws', 850);}
	public function gotoLive()     {$this->_goto('live', 1250);}
	
	/**
	 * Affichage de la page d'accueil d'un tournoi
	 */
	public function gotoVisit()
	{
		// Pour le moment redirection vers l'ancien badnet
		$url = $_SERVER['PHP_SELF']
		. '?kpid=events'
		. '&kaid=98'
		. '&eventId=' . Bn::getValue('eventId');
		header("Location: $url");
		return false;
	}

	/**
	 * Creation modification d'un postit
	 */
	public function pagePostit()
	{
		// Controle de l'autorisation
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

		$postitId = Bn::getValue('postitId', -1);
		if ($postitId > 0)
		{
			$q = new Bn_query('postits');
			$q->addField('psit_title', 'title');
			$q->addField('psit_texte', 'text');
			$q->addWhere('psit_id=' . $postitId);
			$postit = $q->getRow();
		}
		else
		{
			$postit = array('title'=>'', 'text'=>'');
		}
		
		$body = new Body();
		$form = $body->addForm('frmPostit', BEVENT_UPDATE_POSTIT, 'targetDlg');
		$form->addHidden('postitId', $postitId);
		$form->addEdit('title', LOC_LABEL_OBJECT, $postit['title'], 30);
		$form->addArea('text', LOC_LABEL_TEXT, $postit['text']);
		
		$div = $form->addDiv('', 'bn-div-btn');
		$div ->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$div ->addButtonValid('btnValid', LOC_BTN_CONFIRM);
		$body->display();
		return false;
	}
	
	
	/**
	 * Confirmation de supression d'un postit
	 */
	public function pageDeletePostit()
	{
		// Controle de l'autorisation
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

		$postitId = Bn::getValue('postitId');
		$body = new Body();
		$form = $body->addForm('frmDelete', BEVENT_DELETE_POSTIT, 'targetDlg');
		$form->addHidden('postitId', $postitId);
		$form->addP('', LOC_MSG_CONFIRM_DELETE_POSTIT);

		$div = $form->addDiv('', 'bn-div-btn');
		$div ->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$div ->addButtonValid('btnValid', LOC_BTN_CONFIRM);
		$body->display();
		return false;
	}

	/**
	 * Supprime le postit selectionne
	 */
	public function deletePostit()
	{
		// Controle de l'autorisation
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

		// Suppression du postit
		$postitId = Bn::getValue('postitId');
		$q = new Bn_query('postits');
		$q->deleteRow('psit_id=' . $postitId);
		
		// Affichage du message de confirmation
		$body = new Body();
		$body->addP('', LOC_MSG_POSTIT_DELETED);
		$b= $body->addButtonCancel('btnClose', LOC_BTN_CLOSE);
		$body->addJQReady('updatePostits();');
		$body->display();
		return false;
	}

	/**
	 * Envoi de la liste des postits
	 *
	 */
	public function fillPostits()
	{
		// Controle de l'autorisation
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

		// Affichage des postitd
		$q = new Bn_query('postits');
		$q->setFields('psit_id, psit_cre, psit_title, psit_texte');
		$q->addWhere('psit_eventid=' . $eventId);
		$posts = $q->getGridRows(0, true);

		$param  = $q->getGridParam();
		$gvRows = new GvRows($q);
		$i = $param->first;
		foreach ($posts as $post)
		{
			$row[0] = $i++;
			$row[1] = Bn::date($post['psit_cre'], 'd-m-Y');
			$row[2] = $post['psit_title'];
			$row[3] = $post['psit_texte'];

			$bal= new Bn_balise();
			$lnk = $bal->addLink('', BEVENT_PAGE_DELETE_POSTIT . '&postitId=' . $post['psit_id'], null, 'targetDlg');
			$lnk->addMetaData('title', '"' . LOC_TITLE_DELETE_POSTIT . '"');
			$lnk->addMetaData('width',  300);
			$lnk->addMetaData('height', 180);
			$lnk->addimage('', 'delete.png', 'loop');
			$lnk->completeAttribute('class', 'bn-dlg');

			$lnk = $bal->addLink('', BEVENT_PAGE_POSTIT . '&postitId=' . $post['psit_id'], null, 'targetDlg');
			$lnk->addMetaData('title', '"' . LOC_TITLE_MODIF_POSTIT . '"');
			$lnk->addMetaData('width',  300);
			$lnk->addMetaData('height', 180);
			$lnk->addimage('', 'edit.png', 'loop');
			$lnk->completeAttribute('class', 'bn-dlg');
			$row[4] = $bal->tohtml();
			unset($bal);
			
			$gvRows->addRow($row, $post['psit_id']);
		}
		$gvRows->display();
		return false;
	}

	/**
	 * Vidage du cache
	 *
	 * @return unknown
	 */
	public function emptyCache()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);

		// Controle de l'autorisation
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

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
		return BEVENT_DISPATCH;
	}

	/**
	 * Modification de la visibilité du tournoi
	 *
	 * @return unknown
	 */
	public function publiEvent()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);

		// Controle de l'autorisation
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

		if ($oEvent->getVal('pbl') == DATA_PUBLIC) $oEvent->setVal('pbl', DATA_CONFIDENT);
		else $oEvent->setVal('pbl', DATA_PUBLIC);
		$oEvent->save();
		return BEVENT_DISPATCH;
	}

	/**
	 * Enregistrement d'un tournoi individuel
	 *
	 */
	public function createEvent()
	{
		// Controle du profil
		if ( !Oaccount::isLoginAdmin() ) return false;

		// Saison du tournoi
		$season = Oseason::getSeason(Bn::getValue('firstday'));

		// Initialiser le tournoi
		$oEvent = new Oevent();
		$oEvent->setVal('type', Bn::getValue('eventtype'));
		$oEvent->setVal('nature', Bn::getValue('nature'));
		$oEvent->setVal('level', Bn::getValue('level'));

		$oEvent->setVal('name', Bn::getValue('eventname'));
		$oEvent->setVal('date', Bn::getValue('date'));
		$oEvent->setVal('place', Bn::getValue('place'));
		$oEvent->setVal('organizer', Bn::getValue('organizer'));
		$oEvent->setVal('numauto', Bn::getValue('numauto'));
		$oEvent->setVal('url', Bn::getValue('url'));
		$oEvent->setVal('firstday', Bn::getValue('firstday'));
		$oEvent->setVal('lastday', Bn::getValue('lastday'));

		$oEvent->setVal('ownerid', Bn::getValue('user_id'));
		$oEvent->setVal('ranksystem', Bn::getConfigValue('ranking', 'params'));
		$oEvent->setVal('scoringsystem', Bn::getConfigValue('scoring', 'params'));
		$oEvent->setVal('liveentries', NO);
		$oEvent->setVal('catage', OEVENT_CATAGE_ADULT);
		$oEvent->setVal('season', $season);

		// Creation du tournoi
		$eventId = $oEvent->save();

		// Ajout des données meta
		$oEventmeta = new Oeventmeta();
		$oEventmeta->setVal('eventid', $eventId);
		$oEventmeta->setVal('skin', Bn::getConfigValue('theme', 'params'));
		$oEventmeta->setVal('titlefont', 'helvetica');
		$oEventmeta->setVal('titlesize', '20');
		$oEventmeta->setVal('top', 10);
		$oEventmeta->setVal('left', -10);
		$oEventmeta->setVal('width', 70);
		$oEventmeta->setVal('height', 20);
		$oEventmeta->save();

		// Ajout des données extra
		$oEventextra = new Oeventextra();
		$oEventextra->setVal('eventid', $eventId);
		$oEventextra->save();

		// Ajout des droits
		$oEvent->setRight(Bn::getValue('user_id'), OEVENT_RIGHT_MANAGER);

		echo Bn::toJson($eventId);
		return false;
	}

	/**
	 * Modification du type du tournoi
	 *
	 * @return false
	 */
	public function fillNature()
	{
		$types = Oevent::getLovNature(Bn::getValue('type'));
		echo Bn::toJson($types);
		return false;
	}


	/**
	 * Page pour la création d'un tournoi
	 *
	 */
	public function pageNewEvent()
	{
		// Controle du profil
		if ( !Oaccount::isLoginAdmin() ) return false;

		require_once 'Object/Locale/Fr/Oevent.inc';
		$body = new Body();

		$form = $body->addForm('frmNewEvent', BEVENT_CREATE_EVENT, 'targetDlg');
		$form->getForm()->addMetadata('success', "createEvent");
		$form->getForm()->addMetadata('dataType', "'json'");
		$form->setAction(BEVENT_DISPATCH);

		/* Type */
		$div = $form->addDiv('divType', 'bn-div-line bn-div-auto');
		$div->addRadio('eventtype', 'rdoIndiv', LOC_EVENT_TYPE_INDIVIDUAL, OEVENT_TYPE_INDIVIDUAL, true);
		$div->addRadio('eventtype', 'rdoIc', LOC_EVENT_TYPE_IC, OEVENT_TYPE_TEAM, false);
		$div->addBreak();

		/* Nature */
		$div = $form->addDiv('', 'bn-div-clear bn-div-line');
		$cbo = $div->addSelect('nature', LOC_LABEL_EVENT_NATURE, BEVENT_FILL_NATURE);
		$types = Oevent::getLovNature(OEVENT_TYPE_INDIVIDUAL);
		$cbo->addOptions($types, OEVENT_NATURE_OTHER );

		/* Niveau */
		$cbo = $div->addSelect('level', LOC_LABEL_EVENT_LEVEL);
		$levels = Oevent::getLovLevel();
		$cbo->addOptions($levels, OEVENT_LEVEL_REG);

		/* Info generale */
		$div = $form->addDiv('', 'bn-div-clear');
		$edt = $div->addEdit('eventname', LOC_LABEL_EVENT_NAME, '', 255);
		$edt->tooltip(LOC_TOOLTIP_EVENT_NAME);
		$edt = $div->addEdit('place', LOC_LABEL_EVENT_PLACE, '', 50);
		$edt->tooltip(LOC_TOOLTIP_EVENT_PLACE);
		$edt = $div->addEdit('date', LOC_LABEL_EVENT_DATE, '', 25);
		$edt->tooltip(LOC_TOOLTIP_EVENT_DATE);
		$edt = $div->addEdit('numauto', LOC_LABEL_EVENT_NUMAUTO, '', 150);
		$edt = $div->addEdit('organizer', LOC_LABEL_EVENT_ORGANIZER, '', 75);
		$edt->noMandatory();
		$edt = $div->addEditUrl('url', LOC_LABEL_EVENT_URL, '', 200);
		$edt->noMandatory();

		/* Dates */
		$div = $form->addDiv('divDateDays', 'bn-div-line');
		$edt = $div->addEditDate('firstday', LOC_LABEL_FIRSTDAY, date('d-m-Y'), 25);
		$edt->addOption('onSelect', 'selectFirstDay');
		$edt = $div->addEditDate('lastday', LOC_LABEL_LASTDAY, date('d-m-Y'), 25);
		$edt->addOption('onSelect', 'selectLastDay');
		$div->addBreak();

		// Boutons
		$d = $form->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$d->addButtonValid('btnValid', LOC_BTN_UPDATE);

		$body->addJqready('pageNewEvent();');
		$body->display();
		return false;

	}


	/**
	 * Confirmation de supression d'un tournoi
	 */
	public function pageDeleteEvent()
	{
		// Controle du profil
		if ( !Oaccount::isLoginAdmin() ) return false;

		$eventId = Bn::getValue('eventId');
		$oEvent = new Oevent($eventId);
		$body = new Body();
		$form = $body->addForm('frmDelete', BEVENT_DELETE_EVENT, 'targetDlg');
		$form->addP('', $oEvent->getVal('name'), 'bn-title-4');
		$form->addHidden('eventId', $eventId);
		$form->addP('', LOC_MSG_CONFIRM_DELETE_EVENT);

		$div = $form->addDiv('', 'bn-div-btn');
		$div ->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$div ->addButtonValid('btnValid', LOC_BTN_CONFIRM);
		$body->display();
		return false;
	}

	/**
	 * Supprime le tournoi selectionne
	 */
	public function deleteEvent()
	{
		// Controle du profil
		if ( !Oaccount::isLoginAdmin() ) return false;

		// Supprimer le tournoi
		$eventId = Bn::getValue('eventId');
		$o = new Oevent($eventId);
		$o->deleteEvent();

		// Affichage du message de confirmation
		$body = new Body();
		$body->addP('', LOC_MSG_EVENT_DELETED);
		$b= $body->addButtonCancel('btnClose', LOC_BTN_CLOSE);
		$body->addJQReady('searchEvents();');
		$body->display();
		return false;
	}

	/**
	 * Envoi de la liste des tournois
	 *
	 */

	/**
	 * Envoi la liste des tournois
	 */
	public function fillEvents()
	{
		// Controle du profil
		if ( !Oaccount::isLoginAdmin() ) return false;
		
		$locale = Bn::getLocale();
		require_once "Object/Locale/$locale/Oevent.inc";
		require_once "Badnet/Locale/$locale/Event.inc";

		$season = Bn::getValue('lstSeason', Oseason::getCurrent());
		$text   = Bn::getValue('txtSearch');

		// Affichage des tournois
		$q = new Bn_query('events');
		$q->setFields('evnt_id, evnt_firstday, evnt_name, evnt_zone, evnt_type, evnt_nbvisited, evnt_pbl');
		$q->addField("DATE_FORMAT(evnt_firstday, '%Y%m')");
		$q->addWhere('evnt_season=' . $season);
		if (!empty($text) )$q->addWhere("evnt_name LIKE '%" . addSlashes($text) . "%'");

		$events = $q->getGridRows(0, true);
		$param  = $q->getGridParam();
		$gvRows = new GvRows($q);
		$theme = Bn::getValue('theme', 'Badnet');
		$i = $param->first;
		foreach ($events as $event)
		{
			$row[0] = $i++;
			$row[1] = Bn::date($event['evnt_firstday'], 'd-m-Y');
			$b = new Bn_Balise();
			$file = $event['evnt_pbl'] . '.png';
			$img = $b->addImage('', $file, $event['evnt_pbl']);
			if ($event['evnt_type'] == OEVENT_TYPE_INDIVIDUAL) $img->addLink('', BEVENT_GOTO_DRAWS . '&eventId=' . $event['evnt_id'], $event['evnt_name']);
			else $img->addLink('', BEVENT_DISPATCH . '&eventId=' . $event['evnt_id'], $event['evnt_name'], 'targetBody');
			$row[2] = $img->toHtml();
			$row[3] = constant('LABEL_' . $event['evnt_type']);
			$row[4] = $event['evnt_nbvisited'];

			$bal= new Bn_balise();
			$lnk = $bal->addLink('',BEVENT_PAGE_DELETE_EVENT . '&eventId=' . $event['evnt_id'], null, 'targetDlg');
			$lnk->addMetaData('title', '"' . LOC_TITLE_DELETE_EVENT . '"');
			$lnk->addMetaData('width',  300);
			$lnk->addMetaData('height', 180);
			$lnk->addimage('', 'delete.png', 'loop');
			$lnk->completeAttribute('class', 'bn-dlg');

			$row[5] = $bal->toHtml();
			$str = strtoupper(strftime('%B %Y', mktime(substr($event['evnt_firstday'],11,2),
			substr($event['evnt_firstday'],14,2),
			substr($event['evnt_firstday'],17,4),
			substr($event['evnt_firstday'],5,2),
			substr($event['evnt_firstday'],8,2),
			substr($event['evnt_firstday'],0,4))));
                  $row[6] = $str;
			$gvRows->addRow($row, $event['evnt_id']);
		}
		$gvRows->display();
		return false;
	}

	/**
	 * Affichage de la page des tournois pour un administrateur
	 */
	public function pageEvents()
	{
		// Controle du profil
		if ( !Oaccount::isLoginAdmin() ) return false;
		$body = new Body();

		// Menu
		$content = $this->_menu($body);

		// Liste des saisons
		$form = $content->addForm('frmSearch', BEVENT_FILL_EVENTS, null, 'searchEvents');
		$d = $form->addDiv('dAction', 'bn-div-criteria bn-div-line bn-div-auto');

		$season = Bn::getValue('lstSeason', Oseason::getCurrent());
		$select = $d->addSelect('lstSeason', 'Saison', BEVENT_FILL_EVENTS);
		$select->addOptions(Oseason::get(), $season);

		$edit = $d->addEdit('txtSearch', LOC_LABEL_SEARCH_EVENT, '', 20);
		$edit->noMandatory();
		$btn = $d->addButtonValid('btnSearch', LOC_BTN_SEARCH, 'search');

		$btn = $d->addButton('btnNewEvent', LOC_BTN_NEW_EVENT, BEVENT_PAGE_NEW_EVENT, 'plus', 'targetDlg');
		$btn->addMetaData('title', '"' . LOC_TITLE_NEW_EVENT . '"');
		$btn->addMetaData('width',  520);
		$btn->addMetaData('height', 350);
		$btn->completeAttribute('class', 'bn-dlg');
		$d->addBreak();

		// Liste des tournois
		$div = $content->addDiv('divEvents');
		$grid = $div->addGridview('gridEvents', BEVENT_FILL_EVENTS, 100);
		$col = $grid->addColumn('#',   'num', false);
		$col->setLook(40, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_FIRST_DAY, 'date', false);
		$col->setLook(70, 'left', false);
		$col->initsort();
		$col = $grid->addColumn(LOC_COLUMN_EVENT_NAME,   'name');
		$col->setLook(480, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_TYPE,   'type');
		$col->setLook(60, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_VISITED, 'visit');
		$col->setLook(60, 'right', false);
		$col = $grid->addColumn(LOC_COLUMN_ACTION, 'act', false);
		$col->setLook(50, 'center', false);

		$col = $grid->addColumn('Mois', 'month', false);
		$col->setLook(40, 'left', false);

		$col = $grid->addColumn('id', 'id', false, false, true);

		$grid->setLook(LOC_TITLE_EVENTS, 0, "'auto'");
		$grid->setGroup(7, 'desc');

		$body->addBreak();
		$body->addJQready('pageEvents()');

		$body->display();
		return false;
	}


	/**
	 * Affichage de la page d'accueil d'un manager tournoi individuel
	 */
	public function pageManagerEvent()
	{
		$eventId = Bn::getValue('event_id');

		$oEvent = new Oevent($eventId);
		$oExtra = new Oeventextra($eventId);

		$body = new Body();

		// Menu
		$content = $this->_menu($body);
		$oEvent->header($content);
		
		$div = $content->addDiv('', 'bn-div-left bn-div-criteria div-info');
		$t = $div->addP('tltCivil', '', 'bn-title-2');
		$t->addBalise('span', '',  LOC_TITLE_EVENT);
		$div->addInfo('name',  LOC_LABEL_NAME,  $oEvent->getVal('name'));
		$div->addInfo('date',  LOC_LABEL_DATE,  $oEvent->getVal('date'));
		$div->addInfo('place', LOC_LABEL_PLACE, $oEvent->getVal('place'));
		$div->addInfo('organizer', LOC_LABEL_ORGANIZER,  $oEvent->getVal('organizer'));
		$div->addInfo('firstday', LOC_LABEL_FIRSTDAY, Bn::date($oEvent->getVal('firstday'), 'd-m-Y'));
		$div->addInfo('lastday', LOC_LABEL_LASTDAY,  Bn::date($oEvent->getVal('lastday'), 'd-m-Y'));
		$d = $div->addDiv('', 'bn-div-btn');
		$d->addButton('btnPubli', LOC_BTN_PUBLIC, BEVENT_PUBLI_EVENT, 'shuffle', 'targetBody');
		$btn = $d->addButton('btnVisu', LOC_BTN_CACHE, BEVENT_EMPTY_CACHE, 'minus', 'targetBody');


		$div = $content->addDiv('', 'bn-div-left div-info');
		$div->addP('', LOC_P_INFO, 'bn-p-info');

		// Controle de l'autorisation
		if ( $oEvent->getVal('ownerid') == Bn::getValue('user_id') ||  Oaccount::isLoginAdmin() )
		$div->addP()->addLink('', BPREF_PAGE_PREFERENCES, 'Préférences', 'targetBody');

		$div->addP()->addLink('', BEVENT_GOTO_DRAWS, 'Ajouter ou modifier un tableau');
		$div->addP()->addLink('', BEVENT_GOTO_TEAMS, 'Enregistrer ou modifier une équipe');
		$div->addP()->addLink('', BEVENT_GOTO_PLAYERS, 'Gérer les joueurs');
		$div->addP()->addLink('', BEVENT_GOTO_CALENDAR, 'Rectifier l\'échéancier');

		if ($oExtra->getVal('liveupdate') == YES)
		$div->addP()->addLink('', BEVENT_GOTO_LIVE, 'Saisir les résultats des matchs');

		$div = $content->addDiv('', 'bn-div-clear');
		$grid = $div->addGridview('gridPostits', BEVENT_FILL_POSTITS, 25);
		$col = $grid->addColumn('#', '#', false);
		$col->setLook(25, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_DATE,  'date', true);
		$col->setLook(65, 'left', false);
		$col->initSort();
		$col = $grid->addColumn(LOC_COLUMN_OBJECT,  'title', true);
		$col->setLook(200, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_CONTENT,  'content', true);
		$col->setLook(450, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_ACTION,  'action', false);
		$col->setLook(60, 'center', false);
		$grid->setLook(LOC_TITLE_POSTITS, 0, "'auto'");
		
		
		$body->display();
		return false;
	}

	/**
	 * Affichage de la page d'accueil d'un manager interclub
	 */
	public function pageManagerIc()
	{
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);

		$body = new Body();

		// Menu
		$content = $this->_menu($body);
		$oEvent->header($content);

		$div = $content->addDiv('', 'bn-div-left bn-div-criteria div-info');
		$t = $div->addP('tltCivil', '', 'bn-title-2');
		$t->addBalise('span', '',  LOC_TITLE_EVENT);
		$div->addInfo('name',  LOC_LABEL_NAME,  $oEvent->getVal('name'));
		$div->addInfo('date',  LOC_LABEL_DATE,  $oEvent->getVal('date'));
		$div->addInfo('place', LOC_LABEL_PLACE, $oEvent->getVal('place'));
		$div->addInfo('organizer', LOC_LABEL_ORGANIZER,  $oEvent->getVal('organizer'));
		$div->addInfo('firstday', LOC_LABEL_FIRSTDAY, Bn::date($oEvent->getVal('firstday'), 'd-m-Y'));
		$div->addInfo('lastday', LOC_LABEL_LASTDAY,  Bn::date($oEvent->getVal('lastday'), 'd-m-Y'));
		$d = $div->addDiv('', 'bn-div-btn');
		$d->addButton('btnPubli', LOC_BTN_PUBLIC, BEVENT_PUBLI_EVENT, 'shuffle', 'targetBody');
		$btn = $d->addButton('btnVisu', LOC_BTN_CACHE, BEVENT_EMPTY_CACHE, 'minus', 'targetBody');

		$div = $content->addDiv('', 'bn-div-left div-info');
		$div->addP('', LOC_P_INFO, 'bn-p-info');

		// Controle de l'autorisation
		if ( $oEvent->getVal('ownerid') == Bn::getValue('user_id') ||  Oaccount::isLoginAdmin() )
		{
			$div->addP()->addLink('', BPREF_PAGE_PREFERENCES, 'Préférences', 'targetBody');
		} 
			
		$div->addP()->addLink('', BEVENT_GOTO_PLAYERS, 'Gérer les joueurs');
		$div->addP()->addLink('', BEVENT_GOTO_TIES, 'Saisir les résultats des rencontres');
		$div->addP()->addLink('', BEVENT_GOTO_CALENDAR, 'Rectifier le calendrier');

		$div = $content->addDiv('', 'bn-div-clear');
		$grid = $div->addGridview('gridPostits', BEVENT_FILL_POSTITS, 25);
		$col = $grid->addColumn('#', '#', false);
		$col->setLook(25, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_DATE,  'date', true);
		$col->setLook(65, 'left', false);
		$col->initSort();
		$col = $grid->addColumn(LOC_COLUMN_OBJECT,  'title', true);
		$col->setLook(200, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_CONTENT,  'content', true);
		$col->setLook(450, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_ACTION,  'action', false);
		$col->setLook(60, 'center', false);
		$grid->setLook(LOC_TITLE_POSTITS, 0, "'auto'");

		$body->display();
		return false;
	}

	/**
	 * Affichage de la page d'accueil d'un visiteur
	 */
	public function pageVisitor()
	{
		$eventId = Bn::getValue('event_id');

		$oEvent = new Oevent($eventId);

		$body = new Body();
		// Commandes generiques
		$div = $body->addDiv('', 'bn-menu-right');
		$p = $div->addP();
		$lnk = $p->addLink('lnkHome', BNETADM_DISPATCH, LOC_LABEL_ACCOUNT, 'targetBody');
		$lnk->addicon('home');
		if ( Oaccount::isLoginAdmin() )
		{
			$lnk = $p->addLink('lnkAdmin', BADM_PAGE_CHOICE, 'Administration', 'targetBody');
			$lnk->addIcon();
		}

		$t = $body->addP('', $oEvent->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', $oEvent->getVal('date'));

		$div = $body->addRichDiv('', 'bn-left-div');
		$div = $div->addRichDiv('divEvent');
		$t = $div->addP('tltCivil', '', 'bn-title-3');
		$t->addBalise('span', '',  LOC_TITLE_EVENT);
		$div->addInfo('name',  LOC_LABEL_NAME,  $oEvent->getVal('name'));
		$div->addInfo('date',  LOC_LABEL_DATE,  $oEvent->getVal('date'));
		$div->addInfo('place', LOC_LABEL_PLACE, $oEvent->getVal('place'));
		$div->addInfo('organizer', LOC_LABEL_ORGANIZER,  $oEvent->getVal('organizer'));
		$div->addInfo('firstday', LOC_LABEL_FIRSTDAY, Bn::date($oEvent->getVal('firstday'), 'd-m-Y'));
		$div->addInfo('lastday', LOC_LABEL_LASTDAY,  Bn::date($oEvent->getVal('lastday'), 'd-m-Y'));

		$div = $body->addRichDiv('', 'bn-right-div');
		$div = $div->addRichDiv('divGoto');
		$div->addP('', LOC_P_INFO, 'bn-p-info');
		$div->addP()->addLink('', BEVENT_GOTO_DRAWS, 'Voir les tableaux');
		$div->addP()->addLink('', BEVENT_GOTO_PLAYERS, 'Afficher les clubs');
		$div->addP()->addLink('', BEVENT_GOTO_CALENDAR, 'Découvrir l\'échéancier');

		$body->display();
		return false;
	}


	public function dispatch()
	{
		// dispatcher suivant la nature du tournoi
		//   -individuel
		//   -interclub
		// suivant le profil du l'utilisateur pour le tournoi
		//   - gestionnaire
		//   - assistant
		//   - capitaine
		//   - juge arbitre
		$eventId = Bn::getValue('event_id');

		$oEvent = new Oevent($eventId);
		$oAccount = new Oaccount(Bn::getValue('user_id'));

		if ( $oEvent->isIc() )
		{
			if ($oAccount->isEventManager($eventId)) return BEVENT_PAGE_MANAGER_IC;
			else if ( $oAccount->isAssistant($eventId) ) return BEVENT_GOTO_TIES;
			else if ( $oAccount->isCaptain($eventId) ) return BADNET_CAPTAIN;
			else if ( $oAccount->isLoginAdmin() ) return BEVENT_PAGE_MANAGER_IC;
		}
		else
		{
			if ($oAccount->isEventManager($eventId))	return BEVENT_PAGE_MANAGER_EVENT;
			else if ( $oAccount->isLoginAdmin() ) return BEVENT_PAGE_MANAGER_EVENT;
		}
		return BEVENT_PAGE_VISITOR;
	}

	private function _menu($aBody)
	{
		/*
		$d = $aBody->addDiv('', 'bn-menu');
		$div = $d->addDiv('', 'bn-menu-left');
		$p = $div->addP();
		$lnk = $p->addLink('lnkHome', BNETADM_DISPATCH, LOC_LABEL_ACCOUNT, 'targetBody');
		$lnk->addicon('person');
		if ( Oaccount::isLoginAdmin() )
		{
			$lnk = $p->addLink('lnkEvents', BEVENT_PAGE_EVENTS, 'Tous les tournois', 'targetBody');
			$lnk->addIcon();
		}

		$div = $d->addDiv('', 'bn-menu-right');
		$p = $div->addP();
		$lnk = $p->addLink('lnkHome', Bn::getConfigValue('accueil', 'params'), LOC_LABEL_HOME, 'targetBody');
		$lnk->addIcon('home');
		$lnk = $p->addLink('lnkLogout', BNETADM_LOGOUT, LOC_LABEL_LOGOUT, 'targetBody');
		$lnk->addIcon('power');
		if ( Oaccount::isLoginAdmin() )
		{
			$lnk = $p->addLink('lnkAdmin', BADM_PAGE_CHOICE, 'Administration', 'targetBody');
			$lnk->addIcon();
		}
		$d->addBreak();
*/
		$d = $aBody->addDiv('', 'bn-div-clear');
		return $d;
	}
}
?>
