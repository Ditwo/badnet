<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once "Events.inc";
require_once "Badnet/Event/Event.inc";
require_once 'Object/Oplayer.inc';
require_once 'Object/Oasso.inc';

/**
 * Module de gestion des tournois : classe administrateurs
 *
 * @author Gerard CANTEGRIL <cage@aotb.org>
 * @see to follow
 *
 */

class Events
{
	// {{{ properties
	// }}}

	/**
	 * Constructeur: initialisation du controller
	 *
	 */
	public function __construct()
	{
		if ( !Oaccount::isLoginAdmin() ) return Bn::getConfigValue('accueil', 'params');
		$controller = Bn::getController();
		$controller->addAction(EVENTS_FILL_EVENTS,       $this, 'fillEvents');
		$controller->addAction(EVENTS_PAGE_EVENTS,       $this, 'pageEvents');
		$controller->addAction(EVENTS_PAGE_MY_EVENTS,    $this, 'pageEvents');
		$controller->addAction(EVENTS_PAGE_EDIT_EVENT,   $this, 'pageEditEvent');
		$controller->addAction(EVENTS_UPDATE_EVENT,      $this, 'updateEvent');
		$controller->addAction(EVENTS_CONFIRM_DELETE_EVENT,      $this, 'confirmDeleteEvent');
		$controller->addAction(EVENTS_DELETE_EVENT,      $this, 'deleteEvent');
	}

	/**
	 * Enregistrement des modifications d'un tournoi par un administrateur
	 *
	 */
	public function updateEvent()
	{
		//Verifier l'administration
		$eventId = Bn::getValue('eventId');
		$test = Osecure::isEventAdmin(Bn::getValue('user_id') , $eventId);
		if (  $test != true ) return false;

		// Recuperer les donnees
		$oEvent = new Oevent($eventId);
		$name = Bn::getValue('txtName');
		$oEvent->setVal('type', Bn::getValue('cboType')); //OEVENT_TYPE_INDIVIDUAL;
		$oEvent->setVal('nature', Bn::getValue('cboNature'));
		$oEvent->setVal('level', Bn::getValue('cboLevel'));
		$oEvent->setVal('name', $name);
		$oEvent->setVal('date', Bn::getValue('txtDate'));
		$oEvent->setVal('place', Bn::getValue('txtPlace'));
		$oEvent->setVal('organizer', Bn::getValue('txtOrganizer'));
		$oEvent->setVal('datedraw', Bn::getValue('txtDatedraw',  null));
		$oEvent->setVal('deadline', Bn::getValue('txtDeadline', null));
		$oEvent->setVal('ownerid', Bn::getValue('lstOwner'));
		$oEvent->setVal('ranksystem', Bn::getValue('lstRank'));
		$oEvent->setVal('numauto', Bn::getValue('txtNumauto'));
		$oEvent->setVal('firstday', Bn::getValue('txtFirstday'));
		$oEvent->setVal('lastday', Bn::getValue('txtLastday'));
		$oEvent->setVal('url', Bn::getValue('txtUrl'));
		$oEvent->setVal('scoringsystem', Bn::getValue('lstScoring'));
		$oEvent->setVal('liveentries', Bn::getValue('chkLiveentries', '') =='' ? NO:YES);
		$oEvent->setVal('season', Bn::getValue('lstSeason'));
		$oEvent->setVal('nbvisited', Bn::getValue('txtVisited'));
		$oEvent->setVal('dpt', Bn::getValue('txtDpt'));
		$oEvent->setVal('zone', Bn::getValue('txtZone'));
		$oEvent->setVal('poster', Bn::getValue('txtPoster'));
		$oEvent->setVal('textconvoc', Bn::getValue('txtTextConvoc'));
		$oEvent->setVal('lieuconvoc', Bn::getValue('txtLieuConvoc'));
		$oEvent->setVal('convoc', Bn::getValue('rdoConvoc'));
		$oEvent->setVal('archived', Bn::getValue('chkArchived', '') == '' ? NO:YES);
		$oEvent->setVal('nbdrawmax', Bn::getValue('txtNbDraw', null));

		// Mise a jour du tournoi
		$oEvent->saveEvent();
		if ( $oEvent->isError() )
		{
			Bn::setUserMsg($oEvent->getMsg());
			return EVENTS_PAGE_EDIT_EVENT;
		}

		//------ Sauvegarde des donnees extra
		// Categorie d'age
		$catages = array();
		for ($i=OPLAYER_CATAGE_POU; $i <= OPLAYER_CATAGE_VET; $i++)
		{
			if ( Bn::getValue('chk'.$i, '') != '' ) $catages[] = $i;
		}

		// Discipline
		$disci = array();
		for ($i=OPLAYER_DISCIPLINE_MS; $i <= OPLAYER_DISCIPLINE_MX; $i++)
		{
			if ( Bn::getValue('chk'.$i, '') != '') $disci[] = $i;
		}

		// Series
		$serials = array();
		if ( Bn::getValue('chkElite', '') != '') $serials[] = 'Elite,';
		if ( Bn::getValue('chkA', '') != '') $serials[] = 'A';
		if ( Bn::getValue('chkB', '') != '') $serials[] = 'B';
		if ( Bn::getValue('chkC', '') != '') $serials[] = 'C';
		if ( Bn::getValue('chkD', '') != '') $serials[] = 'D';
		if ( Bn::getValue('chkNC', '') != '') $serials[] = 'NC';

		$oExtra = new Oeventextra($eventId);
		$oExtra->setVal('catage', implode(',', $catages));
		$oExtra->setVal('disci',    implode(',', $disci));
		$oExtra->setVal('serial',   implode(',', $serials));
		$oExtra->setVal('deptid',   Bn::getValue('cboDpt'));
		$oExtra->setVal('regionid', Bn::getValue('cboRegion'));
		$oExtra->setVal('eventid',  $eventId);
		$oExtra->setVal('fedeid',   Bn::getValue('txtFedeid'));
		$oExtra->setVal('promoted', Bn::getValue('chkPromoted', '')=='' ? NO:YES);
		$oExtra->setVal('liveupdate', Bn::getValue('chkLiveupdate', '')=='' ? NO:YES);
		$oExtra->setVal('livescoring', Bn::getValue('chkLivescoring', '')=='' ? NO:YES);
		$oExtra->setVal('promoimg',  Bn::getValue('txtPromoimg'));
		$oExtra->save();

		//------ Sauvegarde des frais
		$data = array('IS' => Bn::getValue('txtFeeS', 0),
		'ID' => Bn::getValue('txtFeeD', 0),
		'IM' => Bn::getValue('txtFeeM', 0),
		'I1' => Bn::getValue('txtFee1', 0),
		'I2' => Bn::getValue('txtFee2', 0),
		'I3' => Bn::getValue('txtFee3', 0)
		);
		$oEvent->saveFees($data);
		if ( $oEvent->isError() )
		{
			Bn::setUserMsg($oEvent->getMsg());
			return EVENTS_PAGE_EDIT_EVENT;
		}

		//@todo Sauvegarde des donnes meta
		//$data = array('fees' => Bn::getValue('txtFees', 0));
		//$o->saveMeta($data);
		if ( $oEvent->isError() )
		{
			Bn::setUserMsg($oEvent->getMsg());
			return EVENTS_PAGE_EDIT_EVENT;
		}

		return EVENTS_PAGE_EVENTS;
	}
	/**
	 * Page de modifications d'un tournoi par un administrateur
	 *
	 */
	public function pageEditEvent()
	{
		require_once 'Object/Locale/' . Bn::getLocale() . '/Oplayer.inc';

		//Verifier l'administration
		$eventId = Bn::getValue('eventId');
		$test = Osecure::isEventAdmin(Bn::getValue('user_id') , $eventId);
		if (  $test != true ) return false;

		// Donnees du tournoi
		$oEvent = new Oevent($eventId);
		$fees = $oEvent->getFees();
		$meta = $oEvent->getMeta();

		// Affichage
		$body = new Body();
		$t = $body->addP('', LOC_TITLE_MODIFICATION, 'bn-title-1');
		//$t->addBalise('span', '', LOC_TITLE_INDIVIDUAL);

		$form = $body->addForm('frmEditEvent', EVENTS_UPDATE_EVENT, 'targetBody');
		$form->addHidden('eventId', $eventId);
		$div = $form->addDiv('divGeneralIndiv', 'bn-div-left');
		$t = $div->addP('','','bn-title-3');
		$t->addBalise('span','', LOC_TITLE_INFOS);
		/* Type */
		$div1 = $div->addDiv('divType');
		$cbo = $div1->addSelect('cboType', LOC_LABEL_TYPE);
		$types = Oevent::getLovType(OEVENT_TYPE_INDIVIDUAL);
		$cbo->addOptions($types, $oEvent->getVal('type'));

		// Nature
		$cbo = $div1->addSelect('cboNature', LOC_LABEL_NATURE);
		$natures = Oevent::getLovNature();
		$cbo->addOptions($natures, $oEvent->getVal('nature'));

		/* Niveau */
		$cbo = $div1->addSelect('cboLevel', LOC_LABEL_LEVEL);
		$levels = Oevent::getLovLevel();
		$cbo->addOptions($levels, $oEvent->getVal('level'));

		/* Region */
		$div1 = $div->addDiv('divRegion');
		$cbo = $div1->addSelect('cboRegion',  LOC_LABEL_REGION);
		$regs = Ogeo::getRegions(-1, LOC_LABEL_SELECT_REGION);
		$cbo->addOptions($regs, $oEvent->getVal('regionid'));

		/* Departement */
		$cbo = $div1->addSelect('cboDpt',  LOC_LABEL_DEPT);
		$codeps = Ogeo::getDepts(-1, LOC_LABEL_SELECT_DEPT);
		$cbo->addOptions($codeps, $oEvent->getVal('deptid'));

		// Info generale
		$div1 = $div->addDiv('divName');
		$div1->addEdit('txtName', LOC_LABEL_NAME, $oEvent->getVal('name'), 255);
		$div1->addEdit('txtOrganizer', LOC_LABEL_ORGANIZER, $oEvent->getVal('organizer'), 75);
		$div1->addEdit('txtPlace', LOC_LABEL_PLACE, $oEvent->getVal('place'), 50);
		$div1->addEdit('txtDate', LOC_LABEL_DATE, $oEvent->getVal('date'), 25);
		$txt = $div1->addEdit('txtNumauto', LOC_LABEL_NUMAUTO, $oEvent->getVal('numauto'), 150);
		$txt->noMandatory();
		$txt = $div1->addEditUrl('txtUrl', LOC_LABEL_URL, $oEvent->getVal('url'), 200);
		$txt->noMandatory();

		// Categorie d'age
		$d = $form->addDiv('divChecks', 'bn-div-left');
		$t = $d->addP('','','bn-title-3');
		$t->addBalise('span','', LOC_TITLE_SPORT);

		$div = $d->addDiv('divCatage');
		$catages = preg_split("/[,; ]+/", $oEvent->getVal('catage'));
		for ($i=OPLAYER_CATAGE_POU; $i <= OPLAYER_CATAGE_VET; $i++)
		{
			$div->addCheckbox('chk'.$i, constant('LABEL_'.$i), $i, in_array($i, $catages));
		}
		// Discipline
		$div = $d->addDiv('divDisci');
		$disciplines = preg_split("/[,; ]+/", $oEvent->getVal('disci'));
		for ($i=OPLAYER_DISCIPLINE_MS; $i <= OPLAYER_DISCIPLINE_MX; $i++)
		{
			$div->addCheckbox('chk'.$i, constant('LABEL_'.$i), $i, in_array($i, $disciplines));
		}

		// Series
		$div = $d->addDiv('divSerial');
		$serials = explode(',',  $oEvent->getVal('serial'));
		$div->addCheckbox('chkElite', 'Elite', 'Elite', in_array('Elite', $serials));
		$div->addCheckbox('chkA', 'A', 'A', in_array('A', $serials));
		$div->addCheckbox('chkB', 'B', 'B', in_array('B', $serials));
		$div->addCheckbox('chkC', 'C', 'C', in_array('C', $serials));
		$div->addCheckbox('chkD', 'D', 'D', in_array('D', $serials));
		$div->addCheckbox('chkNC', 'Nc', 'NC', in_array('NC', $serials));

		$txt = $d->addEdit('txtNbDraw', LOC_LABEL_NBDRAW, $oEvent->getVal('nbdrawmax'), 1);
		$txt->noMandatory();
		$systems = Oevent::getLovRankSystem(true);
		$select = $d->addSelect('lstRank', 'LOC_LABEL_RANK_SYSTEM');
		$select->addOptions($systems, $oEvent->getVal('ranksystem'));

		$systems = Oevent::getLovScoringSystem(true);
		$select = $d->addSelect('lstScoring', 'LOC_LABEL_SCORING_SYSTEM');
		$select->addOptions($systems, $oEvent->getVal('scoringsystem'));


		$form->addBreak();
		$div = $form->addDiv('divDates', 'bn-div-left');
		$t = $div->addP('','','bn-title-3');
		$t->addBalise('span','', LOC_TITLE_DATES);

		$div1 = $div->addDiv('divDateGene');
		$oDate = $div1->addEditDate('txtDeadline', LOC_LABEL_DEADLINE, Bn::date($oEvent->getVal('deadline'), 'd-m-Y'), 25);
		$oDate->addOption('onSelect', 'changeDeadLine');
		$oDate->noMandatory();
		$oDate = $div1->addEditDate('txtDatedraw', LOC_LABEL_DATEDRAW, Bn::date($oEvent->getVal('datedraw'), 'd-m-Y'), 25);
		$oDate->addOption('onSelect', 'changeDateDraw');
		$oDate->noMandatory();
		
		$div1 = $div->addDiv('divDateDays');
		$oDate = $div1->addEditDate('txtFirstday', LOC_LABEL_FIRSTDAY, Bn::date($oEvent->getVal('firstday'), 'd-m-Y'), 25);
		$oDate->addOption('onSelect', 'changeFirstDay');
		$oDate->noMandatory();
		$oDate = $div1->addEditDate('txtLastday', LOC_LABEL_LASTDAY, Bn::date($oEvent->getVal('lastday'), 'd-m-Y'), 25);
		$oDate->noMandatory();
		
		// Frais d'inscription
		$div1 = $form->addDiv('divFees', 'bn-div-left');
		$t = $div1->addP('','','bn-title-3');
		$t->addBalise('span','', LOC_TITLE_FEES);
		$d = $div1->addDiv('divFees1');
		$txt = $d->addEdit('txtFeeS', LOC_LABEL_FEES, $fees['IS'], 10);
		$txt->noMandatory();
		$txt = $d->addEdit('txtFeeD', LOC_LABEL_FEED, $fees['ID'], 10);
		$txt->noMandatory();
		$txt = $d->addEdit('txtFeeM', LOC_LABEL_FEEM, $fees['IM'], 10);
		$txt->noMandatory();
		$d = $div1->addDiv('divFees2');
		$txt = $d->addEdit('txtFee1', LOC_LABEL_FEE1, $fees['I1'], 10);
		$txt->noMandatory();
		$txt = $d->addEdit('txtFee2', LOC_LABEL_FEE2, $fees['I2'], 10);
		$txt->noMandatory();
		$txt = $d->addEdit('txtFee3', LOC_LABEL_FEE3, $fees['I3'], 10);
		$txt->noMandatory();

		// Autre infos
		$div1 = $form->addDiv('divConvoc', 'bn-div-left');
		$t = $div1->addP('','','bn-title-3');
		$t->addBalise('span','', LOC_TITLE_CONVOCATION);
		$div1->addRadio('rdoConvoc', 'rdoConvocMatch', constant('LABEL_' . OEVENT_CONVOC_MATCH), OEVENT_CONVOC_MATCH, $oEvent->getVal('convoc')==OEVENT_CONVOC_MATCH);
		$div1->addRadio('rdoConvoc', 'rdoConvocDraw', constant('LABEL_' . OEVENT_CONVOC_DRAW), OEVENT_CONVOC_DRAW, $oEvent->getVal('convoc')==OEVENT_CONVOC_DRAW);
		$e = $div1->addEdit('txtLieuConvoc', LOC_LABEL_LIEUCONVOC, $oEvent->getVal('lieuconvoc'), 50);
		$e->noMandatory();
		$e = $div1->addArea('txtTextConvoc', LOC_LABEL_TEXTCONVOC, $oEvent->getVal('textconvoc'));
		$e->noMandatory();

		$div1 = $form->addDiv('divGene', 'bn-div-left');
		$t = $div1->addP('','','bn-title-3');
		$t->addBalise('span','', LOC_TITLE_OPTION);
		$select = $div1->addSelect('lstSeason', LOC_LABEL_SEASON);
		$select->addOptions(Oseason::get(), $oEvent->getVal('season'));
		$e = $div1->addEdit('txtFedeid', LOC_LABEL_POONA, $oEvent->getVal('fedeid', -1), 6);
		$e->noMandatory();
		$div1->addEdit('txtDpt', LOC_LABEL_DEPT, $oEvent->getVal('dpt'), 6);
		$e->noMandatory();
		$div1->addEdit('txtVisited', LOC_LABEL_VISITED, $oEvent->getVal('nbvisited'), 15);

		//$div1->addEdit('txtStatus', LOC_LABEL_STATUS, $event['evnt_status'], 6);
		$div1 = $form->addDiv('divOption', 'bn-div-left');
		$t = $div1->addP('','','bn-title-3');
		$t->addBalise('span','', LOC_TITLE_OPTION);
		$div1->addCheckbox('chkArchived',    LOC_LABEL_ARCHIVED, NO, $oEvent->getVal('archived')==YES);
		$div1->addCheckbox('chkLiveentries', LOC_LABEL_LIVE, NO, $oEvent->getVal('liveentries')==YES);
		$div1->addCheckbox('chkPromoted',    LOC_LABEL_PROMOTED, NO, $oEvent->getVal('promoted') ==YES);
		$div1->addCheckbox('chkLivescoring', LOC_LABEL_LIVESCORING, NO, $oEvent->getVal('livescoring')==YES);
		$div1->addCheckbox('chkLiveupdate',  LOC_LABEL_LIVEUPDATE, NO, $oEvent->getVal('liveupdate') ==YES);

		$div1 = $form->addDiv('divGene2', 'bn-div-left');
		$t = $div1->addP('','','bn-title-3');
		$t->addBalise('span','', LOC_TITLE_OPTION);
		$q = new Bn_query('users');
		$q->setFields('user_id, user_name');
		$q->setOrder('user_name');
		$users = $q->getLov();
		$cbo = $div1->addSelect('lstOwner',  LOC_LABEL_OWNER);
		$cbo->addOptions($users, $oEvent->getVal('ownerid'));
		$e = $div1->addEdit('txtZone', LOC_LABEL_ZONE, $oEvent->getVal('zone'), 10);
		$e->noMandatory();
		$e = $div1->addEdit('txtPoster', LOC_LABEL_POSTER, $oEvent->getVal('poster'), 200);
		$e->noMandatory();
		$e = $div1->addEdit('txtPromoimg', LOC_LABEL_PROMOIMG, $oEvent->getVal('promoimg'), 200);
		$e->noMandatory();

		// Boutons de commande : enregistrer, suite, abandonner
		$div2 = $form->addDiv('', 'bn-div-btn');
		$div2->addButtonValid('btnUpdate', LOC_BTN_UPDATE);
		$div2->addButtonCancel('btnBack', LOC_BTN_CANCEL, EVENTS_PAGE_EVENTS, 'targetBody');

		//$body->addJQready('pageIndividualEvent();');
		$body->display(MENU_ADM, 1);
		return false;
	}

	/**
	 * Confirmation de supression d'un tournoi
	 */
	public function confirmDeleteEvent()
	{
		$body = new Body();
		$form = $body->addForm('frmDelete', EVENTS_DELETE_EVENT, 'targetDlg');
		$form->addHidden('eventId', Bn::getValue('eventId'));
		$form->addP('', LOC_MSG_CONFIRM_DELETE_EVENT);
		$form->addButtonValid('', LOC_BTN_CONFIRM);
		$b = $form->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$body->display();
		return false;
	}

	/**
	 * Supprime le tournoi selectionne
	 */
	public function deleteEvent()
	{
		// Verifier le niveau d'autorisation
		if ( Oaccount::isLoginAdmin() )
		{
			// Supprimer le tournoi
			$eventId = Bn::getValue('eventId');
			$o = new Oevent($eventId);
			$o->deleteEvent();
		}
		// Affichage du message de confirmation
		$body = new Body();
		$body->addP('', LOC_MSG_EVENT_DELETED);
		$b= $body->addButtonCancel('btnClose', LOC_BTN_CLOSE);
		$body->addJQReady('deleteEvent();');
		$body->display();
		return false;
	}

	/**
	 * Envoi la liste des tournois
	 */
	public function fillEvents($s)
	{
		$locale = Bn::getLocale();
		require_once "Object/Locale/$locale/Oevent.inc";
		require_once "Badnetlocal/Badnetlocal.inc";

		$season = Bn::getValue('lstSeason', Oseason::getCurrent());
		$text   = Bn::getValue('txtSearch');
		$allevent = Bn::getValue('allevent', NO);
		
		// Affichage des tournois
		$q = new Bn_query('events');
		$q->leftJoin('eventsextra', 'evxt_eventid=evnt_id');
		$q->setFields('evnt_id, evnt_firstday, evnt_name, evnt_zone, evnt_type, evnt_nbvisited, evxt_fedeid, evnt_pbl');
		$q->addField("DATE_FORMAT(evnt_firstday, '%Y%m')");
		$q->addWhere('evnt_season=' . $season);
		if (!empty($text) )$q->addWhere("evnt_name LIKE '%" . addSlashes($text) . "%'");
		if ($allevent != YES )
		{
			$q->leftJoin('users', 'evnt_ownerid=user_id');
		}

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
			$img->addLink('', BADNET_EVENT . '&eventId=' . $event['evnt_id'], $event['evnt_name'], 'targetBody');
			$row[2] = $img->toHtml();
			$row[3] = $event['evnt_zone'];
			$row[4] = constant('LABEL_' . $event['evnt_type']);
			$row[5] = $event['evnt_nbvisited'];
			$row[6] = $event['evxt_fedeid'];
			
			$bal= new Bn_balise();
			$lnk = $bal->addLink('',EVENTS_PAGE_EDIT_EVENT . '&eventId=' . $event['evnt_id'], null,  'targetBody');
			$lnk->addimage('', 'Bn/Img/magnifier.png', 'loop');
				
			$lnk = $bal->addLink('',BADNETLOCAL_UMPIRE . '&eventId=' . $event['evnt_id'], null,  'targetBody');
			$lnk->addimage('', 'new.png', 'live');

			$lnk = $bal->addLink('',EVENTS_CONFIRM_DELETE_EVENT . '&eventId=' . $event['evnt_id'], null, 'targetDlg');
			$lnk->addimage('', 'delete.png', 'loop');
			$lnk->completeAttribute('class', 'bn-delete');
				
			$row[7] = $bal->toHtml();
			$row[8] = strftime('%B %Y', mktime(substr($event['evnt_firstday'],11,2),
						substr($event['evnt_firstday'],14,2),
						substr($event['evnt_firstday'],17,4),
						substr($event['evnt_firstday'],5,2),
						substr($event['evnt_firstday'],8,2),
						substr($event['evnt_firstday'],0,4)));
			
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
		$body = new Body();

		// Liste des saisons
		$form = $body->addForm('frmSearch', EVENTS_FILL_EVENTS, null, 'searchEvents');
		$d = $form->addDiv('dAction', 'bn-div-criteria bn-div-line bn-div-auto');
		
		$season = Bn::getValue('lstSeason', Oseason::getCurrent());
		$select = $d->addSelect('lstSeason', 'Saison', EVENTS_FILL_EVENTS);
		$select->addOptions(Oseason::get(), $season);

		$rdo = $d->addCheckbox('allevent', 'Afficher tous les tournois', YES, false);
		$rdo->noMandatory();
		
		$edit = $d->addEdit('txtSearch', 'Chercher', '', 20);
		$edit->noMandatory();
		$btn = $d->addButtonValid('lnkSearch', 'Go', 'search');
		$d->addBreak();
		$div = $form->addDiv('divEvents');

		// Liste des tournois
		$grid = $div->addGridview('gridEvents', EVENTS_FILL_EVENTS, 100);
		$col = $grid->addColumn('#',   'num', false);
		$col->setLook(40, 'left', false);
		$col = $grid->addColumn('Date', 'date', false);
		$col->setLook(70, 'left', false);
		$col->initsort();
		$col = $grid->addColumn('Nom',   'name');
		$col->setLook(310, 'left', false);
		$col = $grid->addColumn('Zone',   'zone');
		$col->setLook(80, 'left', false);
		$col = $grid->addColumn('Type',   'type');
		$col->setLook(60, 'left', false);
		$col = $grid->addColumn('Visites', 'visit');
		$col->setLook(60, 'right', false);
		$col = $grid->addColumn('PoonaId', 'visit');
		$col->setLook(50, 'right', false);
		$col = $grid->addColumn('Action', 'act', false);
		$col->setLook(90, 'center', false);
		
		$col = $grid->addColumn('Mois', 'month', false);
		$col->setLook(40, 'left', false);
		
		$col = $grid->addColumn('id', 'id', false, false, true);

		$grid->setLook('Compétitions', 0, "'auto'");
		$grid->setGroup(9, 'desc');
		
		$body->addBreak();
		$body->addJQready('pageEvents()');

		$body->display(MENU_ADM, 1);
		return false;
	}

}
?>