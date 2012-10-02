<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Adm.inc';
require_once 'Badnet/Event/Event.inc';
require_once 'Object/Omatch.inc';

require_once 'Object/Locale/' . Bn::getLocale() . '/Opair.inc';
require_once 'Object/Locale/' . Bn::getLocale() . '/Omatch.inc';

/**
 * Module de gestion des tournois
 */
class Adm
{
	// {{{ properties
	public function __construct()
	{
		if( !Oaccount::isLoginAdmin() ) return false;
		$controller = Bn::getController();
		$controller->addAction(BADM_PAGE_CHOICE,    $this, 'pageChoice');
		$controller->addAction(BADM_PAGE_RANKING,   $this, 'pageRanking');
		$controller->addAction(BADM_FILL_RANKING,   $this, 'fillRanking');
		$controller->addAction(BADM_RAZ_RANKING,    $this, 'razRanking');
		$controller->addAction(BADM_UPDATE_RANKING, $this, 'updateRanking');
		$controller->addAction(BADM_PAGE_PAIRS,     $this, 'pagePairs');
		$controller->addAction(BADM_FILL_PAIRS,     $this, 'fillPairs');
		$controller->addAction(BADM_PAGE_PAIR,      $this, 'pagePair');
		$controller->addAction(BADM_FILL_REGIPAIRS, $this, 'fillRegipairs');
		$controller->addAction(BADM_CONFIRM_DELETE_PAIR,    $this, 'confirmDeletePair');
		$controller->addAction(BADM_DELETE_PAIR,    $this, 'deletePair');
		$controller->addAction(BADM_PAGE_WO,        $this, 'pageWo');
		$controller->addAction(BADM_FILL_WO,        $this, 'fillWo');
		$controller->addAction(BADM_UPDATE_WO,      $this, 'updateWo');
		$controller->addAction(BADM_UPDATE_DISCIPLINE,      $this, 'updateDiscipline');
		$controller->addAction(BADM_UPDATE_I2P,      $this, 'updateI2p');
		$controller->addAction(BADM_PAGE_MATCHS,     $this, 'pageMatchs');
		$controller->addAction(BADM_FILL_MATCHS,     $this, 'fillMatchs');
		$controller->addAction(BADM_PAGE_MATCH,      $this, 'pageMatch');
		$controller->addAction(BADM_FILL_MATCHPAIRS, $this, 'fillMatchPairs');
		$controller->addAction(BADM_CONFIRM_DELETE_P2M,    $this, 'confirmDeleteP2m');
		$controller->addAction(BADM_DELETE_P2M,    $this, 'deleteP2m');
		$controller->addAction(BADM_PAGE_PLAYERS,     $this, 'pagePlayers');
		$controller->addAction(BADM_FILL_PLAYERS,     $this, 'fillPlayers');
		$controller->addAction(BADM_PAGE_PLAYER,      $this, 'pagePlayer');
		$controller->addAction(BADM_UPDATE_UNIID,     $this, 'updateUniid');
		$controller->addAction(BADM_PAGE_REGISTRATIONS,     $this, 'pageRegistrations');
		$controller->addAction(BADM_FILL_REGISTRATIONS,     $this, 'fillRegistrations');
		$controller->addAction(BADM_PAGE_REGISTRATION,      $this, 'pageRegistration');
		$controller->addAction(BADM_UPDATE_REGISTRATION,     $this, 'updateRegistration');
		$controller->addAction(BADM_FILL_MEMBERS,            $this, 'fillMembers');

		$controller->addAction(BADM_PAGE_TEAMS,            $this, 'pageTeams');
		$controller->addAction(BADM_FILL_TEAMS,            $this, 'fillTeams');
		$controller->addAction(BADM_PAGE_TEAM,             $this, 'pageTeam');
		$controller->addAction(BADM_UPDATE_TEAM,           $this, 'updateTeam');
	}

	/**
	 * Confirmation de supression d'une relation p2m
	 */
	public function confirmDeleteP2m()
	{
		$body = new Body();
		$form = $body->addForm('frmDelete', BADM_DELETE_P2M, 'targetDlg');
		$form->addHidden('p2mId', Bn::getValue('p2mId'));
		$form->addP('', 'Vous allez supprimer la relation sélectionnée. Impossible de la récupérer.');
		$form->addButtonValid('', 'Supprimer');
		$b = $form->addButtonCancel('btnCancel', 'Abandonner');
		$body->display();
		return false;
	}

	/**
	 * Suppression d'une relation P2m 
	 *
	 * @return unknown
	 */
	public function deleteP2m()
	{
		// Verifier le niveau d'autorisation
		if ( Oaccount::isLoginAdmin() )
		{
			// Supprimer la paire si elle n'a pas de match
			$p2mId = Bn::getValue('p2mId');
			$q = new Bn_query('p2m');
			$q->deleteRow('p2m_id=' . $p2mId);
		}
		// Affichage du message de confirmation
		$body = new Body();
		$body->addP('', 'Relation supprimée');
		$b= $body->addButtonCancel('btnClose', 'Fermer');
		$body->addJQReady('deleteP2m();');
		$body->display();
		return false;
	}

	
	
	/**
	 * Envoi la liste des paires d'un match
	 */
	public function fillMatchPairs()
	{
		$matchId = Bn::getValue('matchId');

		// Joueur enregistre dans le tournoi
		$q = new Bn_query('p2m');
		$q->setFields('p2m_matchid, p2m_pairid, p2m_result, p2m_posmatch, p2m_id');
		$q->addWhere('p2m_matchid='. $matchId);

		$p2ms = $q->getGridRows(0, true);
		$gvRows = new GvRows($q);
		$param  = $q->getGridParam();
		$i = $param->first;

		// Requete pour chercher les classements des joueurs dans la base fede
		foreach ($p2ms as $p2m)
		{
			$row[0] = $i++;
			$row[1] =$p2m['p2m_matchid'];
			$row[2] =$p2m['p2m_pairid'];
			$row[3] =$p2m['p2m_result'];
			$row[4] =$p2m['p2m_posmatch'];
			$row[5] =$p2m['p2m_id'];
			
			$bal= new Bn_balise();
			$lnk = $bal->addLink('', BADM_CONFIRM_DELETE_P2M . '&p2mId=' . $p2m['p2m_id'], null, 'targetDlg');
			$lnk->addimage('', 'delete.png', 'loop');
			$lnk->completeAttribute('class', 'bn-delete');
			$row[6] = $bal->toHtml();
			$gvRows->addRow($row, $p2m['p2m_id']);
		}
		$gvRows->display();
		return false;
	}
	
	/**
	 * Page d'un match avec ses paires
	 *
	 */
	public function pageMatch()
	{
		$matchId = Bn::getValue('matchId');
		$body = new Body();

		$btn = $body->addButton('btnBack', 'Back', BADM_PAGE_MATCHS, '', 'targetBody');

		// Liste des paires
		$body->addBreak();
		$url = BADM_FILL_MATCHPAIRS . '&matchId=' . $matchId;
		$grid = $body->addGridview('gridP2m', $url, 50);
		$col = $grid->addColumn('#',   'num');
		$col->setLook(30, 'left', false);
		$col = $grid->addColumn('MatchId',  'disci');
		$col->setLook(120, 'left', false);
		$col = $grid->addColumn('PairId',   'state');
		$col->setLook(120, 'left', false);
		$col = $grid->addColumn('Result', 'status');
		$col->setLook(120, 'left', false);
		$col = $grid->addColumn('Posmatch', 'draw');
		$col->setLook(160, 'left', false);
		$col = $grid->addColumn('Id',  'nb');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('Action',  'act');
		$col->setLook(70, 'center', false);
		$grid->setLook('Paires du match', 0, "'auto'");

		$body->addBreak();
		//$body->addJQready('pagePairs()');

		$body->display(MENU_BADNET, 4);
	}
	
	
	/**
	 * Affichage de la page d'une equipe
	 */
	public function updateTeam()
	{
		$teamId  = Bn::getValue('teamid');
		$oTeam = new Oteam($teamId);
		$oTeam->setVal('captainid', Bn::getValue('captainid'));
		$oTeam->save();
		return BADM_PAGE_TEAMS;
	}
	/**
	 * Affichage de la page d'une equipe
	 */
	public function pageTeam()
	{
		require_once 'Object/Omember.inc';
		include_once 'Object/Locale/' . Bn::getLocale() . '/Omember.inc';
		$eventId = Bn::getValue('event_id');
		$teamId  = Bn::getValue('teamId');

		$oTeam = new Oteam($teamId);
		$body = new Body();
		$t = $body->addP('', $oTeam->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', $oTeam->getVal('stamp'));

		$t = $body->addP('', '', 'bn-title-3');
		$t->addBalise('span', '', "Modification de l'équipes");

		// Liste des capitaines
		$q = new Bn_query('users');
		$q->addTable('rights', 'rght_userid=user_id');
		$q->addWhere('rght_theme='. THEME_EVENT);
		$q->addWhere('rght_themeid=' . $eventId);
		//$q->addValue('rght_status', $aRight);
		$q->addField('user_id');
		$q->addField("concat(user_name, '-',user_login)");
		$q->setOrder('user_name');
		$users = $q->getLov();
		//print_r($users);
		//print_r($q);
		
		$form = $body->addForm('frmTeam', BADM_UPDATE_TEAM, 'targetBody');
		$form->addHidden('teamid', $teamId);
		$form->addEdit('name', 'Nom', $oTeam->getVal('name'));
		$form->addEdit('captain', 'Capitaine', $oTeam->getVal('captain'));
		$form->addInfo('capt', 'Id capitaine', $oTeam->getVal('captainid'));
		$lst = $form->addSelect('captainid', 'Id capitaine');
		$lst->addoptions($users, $oTeam->getVal('captainid'));
		
		$d = $form->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', 'Abandonner', BADM_PAGE_TEAMS, 'targetBody');
		$d->addButtonValid('', 'Enregistrer');
		
		$body->display();
		return false;
	}
	
	/**
	 * Envoi la liste des equipes avec leur capitaine
	 */
	public function fillTeams()
	{
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);

		$q = new Bn_query('draws');
		$q->addTable('rounds', 'rund_drawid=draw_id');
		$q->addTable('t2r', 't2r_roundid=rund_id');
		$q->addTable('teams', 't2r_teamid=team_id');
		$q->leftJoin('users', 'user_id=team_captainid');
		$q->addWhere('team_eventid=' . $eventId);
		$drawId = Bn::getValue('division', -1);

		if ( $drawId > 0 ) $q->addWhere('draw_id=' . $drawId);
		$q->setFields('team_id, draw_name, rund_name, team_name, team_captainid, user_name, user_id');

		$teams = $q->getGridRows(0, true);
		//print_r($q);
		$param  = $q->getGridParam();
		$i = $param->first;
		$gvRows = new GvRows($q);
		foreach ($teams as $team)
		{
			$teamId = $team['team_id'];
			$row[0] = $i++;
			$row[1] = $team['draw_name'];
			$row[2] = $team['rund_name'];
			$row[3] = $team['team_name'];
			$row[4] = $team['user_name'];

			$bal= new Bn_balise();
			$lnk = $bal->addLink('', BADM_PAGE_TEAM . '&teamId=' . $teamId, null, 'targetBody');
			$lnk->addimage('', 'edit.png', 'del');
			$row[5] = $bal->toHtml();
			$gvRows->addRow($row, $team['team_id']);
		}
		$gvRows->display();
		return false;
	}

	/**
	 * Affichage de la page de gestion des equipes
	 */
	public function pageTeams()
	{
		require_once 'Object/Omember.inc';
		include_once 'Object/Locale/' . Bn::getLocale() . '/Omember.inc';
		$eventId = Bn::getValue('event_id');
		$teamId  = Bn::getValue('teamId');

		$oEvent = new Oevent($eventId);
		$body = new Body();
		$t = $body->addP('', $oEvent->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', $oEvent->getVal('date'));

		$t = $body->addP('', '', 'bn-title-3');
		$t->addBalise('span', '', 'Gestion des équipes');

		// Liste des equipes
		$url = BADM_FILL_TEAMS;
		$grid = $body->addGridview('gridTeams', $url, 50);
		$col = $grid->addColumn('#',   '#', false);
		$col->setLook(30, 'left', false);
		$col = $grid->addColumn('Division', 'division');
		$col->setLook(150, 'left', false);
		$col = $grid->addColumn('Groupe', 'group');
		$col->setLook(150, 'left', false);
		$col = $grid->addColumn('Equipe', 'team');
		$col->setLook(200, 'left', false);
		$col->initSort('asc');
		$col = $grid->addColumn('Capitaine', 'captain');
		$col->setLook(150, 'left', false);
		$col = $grid->addColumn('Action', 'action');
		$col->setLook(50, 'center', false);
		$grid->setLook('Equipes', 0, "'auto'");

		$body->display();
		return false;
	}

	/**
	 * Envoi la liste des membres
	 */
	public function fillMembers()
	{
		$locale = Bn::getLocale();
		require_once "Object/Locale/$locale/Omember.inc";

		// Joueur enregistre dans le tournoi
		$q = new Bn_query('members');
		$q->setFields('mber_id, mber_id, mber_sexe, mber_secondname, mber_firstname, mber_licence, mber_uniid');

		$search = Bn::getValue('txtSearch');
		if ( !empty($search) )
		{
			$where = "(mber_secondname LIKE '%" . $search . "%'" .
			" OR mber_firstname LIKE '%" . $search . "%')";
			$q->addWhere($where);
		}

		$regis = $q->getGridRows(0, true);
		//print_r($q);
		$gvRows = new GvRows($q);
		$param  = $q->getGridParam();
		$i = $param->first;

		// Requete pour chercher les classements des joueurs dans la base fede
		foreach ($regis as $regi)
		{
			$row[0] = $i++;
			$row[1] = $regi['mber_id'];
			$row[2] = $regi['mber_sexe'];
			$row[3] = $regi['mber_secondname'];
			$row[4] = $regi['mber_firstname'];
			$row[5] = $regi['mber_licence'];
			$row[6] = $regi['mber_uniid'];
			$gvRows->addRow($row);
		}
		$gvRows->display();
		return false;
	}

	public function updateRegistration()
	{
		$regiId = Bn::getValue('regiId');
		$q = new Bn_Query('registration');
		$q->addValue('regi_memberid', Bn::getValue('mberid'));
		$q->addValue('regi_longname', Bn::getValue('longname'));
		$q->addValue('regi_shortname', Bn::getValue('shortname'));

		$q->setWhere('regi_id=' . $regiId);
		$q->updateRow();
		return BADM_PAGE_REGISTRATIONS;
	}

	public function pageRegistration()
	{
		$regiId = Bn::getValue('regiId');
		$q = new Bn_Query('registration');
		$q->setFields('*');
		$q->setWhere('regi_id=' . $regiId);
		$row = $q->getRow();
		$body = new Body();
		$form = $body->addForm('frmRegi', BADM_UPDATE_REGISTRATION, 'targetBody');
		$form->addHidden('regiId', $regiId);
		$form->addEdit('mberid', 'Member id', $row['regi_memberid']);
		$form->addEdit('longname', 'Nom long', $row['regi_longname']);
		$form->addEdit('shortname', 'Nom court', $row['regi_shortname']);

		$d = $form->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancelAccount', 'Abandonner', BADM_PAGE_REGISTRATIONS, 'targetBody');
		$d->addButtonValid('', 'Enregistrer');

		// Liste des membres
		$d->addEdit('txtSearch', 'Chercher', '', 20);
		$btn = $d->addButton('btnSearch', 'Go');

		// Liste des inscrits
		$body->addBreak();
		$grid = $body->addGridview('gridMembers', BADM_FILL_MEMBERS, 50);
		$col = $grid->addColumn('#',   'num');
		$col->setLook(30, 'left', false);
		$col = $grid->addColumn('mberid',   'memberid');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('Genre',   'gender');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('Nom',   'famname');
		$col->setLook(80, 'left', false);
		$col = $grid->addColumn('Prenom',   'firstname');
		$col->setLook(80, 'left', false);
		$col = $grid->addColumn('Licence',   'licence');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('uniid',   'uniid');
		$col->setLook(140, 'left', false);

		$col = $grid->addColumn('id', 'id', false, false, true);

		$grid->setLook('Membres', 0, "'auto'");

		$body->addBreak();
		$body->addJQready('pageRegistration();');

		$body->display(MENU_BADNET, 4);
	}

	/**
	 * Envoi la liste des joueurs inscrits
	 */
	public function fillRegistrations()
	{
		$eventId = Bn::getValue('event_id');
		$locale = Bn::getLocale();
		require_once "Object/Locale/$locale/Omember.inc";

		// Joueur enregistre dans le tournoi
		$q = new Bn_query('members, registration');
		$q->setFields('regi_id,regi_id, mber_id, regi_longname, mber_licence, mber_secondname, mber_firstname, regi_uniid');
		$q->addWhere('regi_eventid=' . $eventId);
		$q->addWhere('regi_memberid=mber_id');
		$q->addWhere('mber_id > 0');

		$search = Bn::getValue('txtSearch');
		if ( !empty($search) )
		{
			$where = "(regi_longname LIKE '%" . $search . "%'" .
			" OR mber_secondname LIKE '%" . $search . "%')";
			$q->addWhere($where);
		}
		$scan = Bn::getValue('scan');
		if ( !empty($scan) )
		{
			$where = "regi_longname NOT LIKE concat(mber_secondname, '%')";
			$q->addWhere($where);
		}

		$regis = $q->getGridRows(0, true);
		$gvRows = new GvRows($q);
		$param  = $q->getGridParam();
		$i = $param->first;

		// Requete pour chercher les classements des joueurs dans la base fede
		foreach ($regis as $regi)
		{
			$row[0] = $i++;
			$row[1] = $regi['regi_id'];
			$row[2] = $regi['mber_id'];
			$row[3] = $regi['regi_longname'];
			$row[4] = $regi['mber_licence'];
			$row[5] = $regi['mber_secondname'];
			$row[6] = $regi['mber_firstname'];
			$row[7] = $regi['regi_uniid'];

			$bal= new Bn_balise();
			$lnk = $bal->addLink('', BADM_PAGE_REGISTRATION . '&regiId=' . $regi['regi_id'], null, 'targetBody');
			$lnk->addimage('', 'magnifier.png', 'loop');
			$row[8] = $bal->toHtml();

			$gvRows->addRow($row);
		}
		$gvRows->display();
		return false;
	}

	/**
	 * Page avec la liste des joueurs inscrits
	 *
	 */
	public function pageRegistrations()
	{
		$body = new Body();

		$d = $body->addDiv('pAction', 'bn-div-line bn-div-auto');
		$d->addEdit('txtSearch', 'Chercher', '', 20);
		$d->addCheckbox('scan', 'Membre != regi', 1);
		$btn = $d->addButton('btnSearch', 'Go');

		// Liste des inscrits
		$body->addBreak();
		$grid = $body->addGridview('gridRegis', BADM_FILL_REGISTRATIONS, 50);
		$col = $grid->addColumn('#',   'num');
		$col->setLook(30, 'left', false);
		$col = $grid->addColumn('regiid',   'regiid');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('mberid',   'memberid');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('longname', 'longname');
		$col->setLook(110, 'left', false);
		$col = $grid->addColumn('Licence',   'licence');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('Nom (mber)',   'famname');
		$col->setLook(80, 'left', false);
		$col = $grid->addColumn('Prenom (mber)',   'firstname');
		$col->setLook(80, 'left', false);
		$col = $grid->addColumn('uniid',   'uniid');
		$col->setLook(140, 'left', false);
		$col = $grid->addColumn('Action', 'act', false);
		$col->setLook(70, 'center', false);

		$col = $grid->addColumn('id', 'id', false, false, true);

		$grid->setLook('Joueurs par paire', 0, "'auto'");

		$body->addBreak();
		$body->addJQready('pageRegistrations();');

		$body->display(MENU_BADNET, 4);
	}

	public function updateUniid()
	{
		$eventId = Bn::getValue('event_id');

		// Id de la base
		$q = new BN_query('meta');
		$q->addField('meta_value');
		$q->addWhere("meta_name = 'databaseId'");
		$localDBId = $q->getOne();

		// Mise a jour des inscriptions
		//$q->setTables('registration');
		//$q->setWhere('regi_eventid=' . $eventId);
		//$q->setValue('regi_uniid', "concat('" . $localDBId . ":',lreg_id, ';')");
		//$q->updateRow();

		// Mise a jour des paires
		$q->setTables('registration, i2p');
		$q->setFields('i2p_pairid');
		$q->setWhere('regi_id=i2p_regiid');
		$q->addWhere('regi_eventid=' . $eventId);
		$pairIds  = $q->getCol();
		$q->setTables('pairs');
		$q->setWhere('pair_id IN (' . implode(',', $pairIds) . ')');
		$q->setValue('pair_uniid', "concat('" . $localDBId . ":',pair_id, ';')");
		$q->updateRow();

		return BADM_PAGE_CHOICE;
	}

	/**
	 * Envoi la liste des match et le nombre de paire assosciees
	 */
	public function fillPlayers()
	{
		$eventId = Bn::getValue('event_id');
		$locale = Bn::getLocale();
		require_once "Object/Locale/$locale/Omember.inc";

		// Joueur enregistre dans le tournoi
		$q = new Bn_query('pairs');
		$q->leftJoin('i2p', 'i2p_pairid=pair_id');
		$q->leftJoin('registration', 'regi_id=i2p_regiid');
		$q->setFields('pair_id, pair_drawid, pair_disci, pair_status, pair_state, pair_id, pair_uniid');
		$q->addField('count(i2p_id)', 'nb');
		$q->addWhere('regi_eventid=' . $eventId);
		$q->group('pair_id');

		$pairs = $q->getGridRows(0, true);
		$gvRows = new GvRows($q);
		$param  = $q->getGridParam();
		$i = $param->first;

		// Requete pour chercher les classements des joueurs dans la base fede
		foreach ($pairs as $pair)
		{
			$row[0] = $i++;
			$row[1] = $pair['pair_drawid'];
			$row[2] = $pair['pair_disci'];
			$row[3] = $pair['pair_status'];
			$row[4] = $pair['pair_state'];
			$row[5] = $pair['pair_id'];
			$row[6] = $pair['pair_uniid'];
			$row[7] = $pair['nb'];

			$bal= new Bn_balise();
			$lnk = $bal->addLink('', BADM_PAGE_PLAYER . '&pairId=' . $pair['pair_id'], null, 'targetBody');
			$lnk->addimage('', 'Bn/Img/magnifier.png', 'loop');
			$row[8] = $bal->toHtml();

			$gvRows->addRow($row);
		}
		$gvRows->display();
		return false;
	}

	/**
	 * Page avec la liste des joueurs par paire
	 *
	 */
	public function pagePlayers()
	{
		$body = new Body();

		$d = $body->addDiv('pAction', 'bn-div-line bn-div-auto');
		$d->addEdit('txtSearch', 'Chercher', '', 20);
		$btn = $d->addButton('btnSearch', 'Go');

		// Liste des inscrits
		$body->addBreak();
		$grid = $body->addGridview('gridPairs', BADM_FILL_PLAYERS, 50);
		$col = $grid->addColumn('#',   'num');
		$col->setLook(30, 'left', false);
		$col = $grid->addColumn('drawid',   'drawid');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('disci',   'disci');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('status',   'status');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('state',   'state');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('pairid',   'pairid');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('uniid',   'uniid');
		$col->setLook(140, 'left', false);
		$col = $grid->addColumn('Nb joueur',  'nb');
		$col->setLook(140, 'left', false);
		$col->initsort('desc');
		$col = $grid->addColumn('Action', 'act', false);
		$col->setLook(70, 'center', false);

		$col = $grid->addColumn('id', 'id', false, false, true);

		$grid->setLook('Joueurs par paire', 0, "'auto'");

		$body->addBreak();
		//$body->addJQready('pagePairs()');

		$body->display(MENU_BADNET, 4);
	}

	/**
	 * Envoi la liste des match et le nombre de paire assosciees
	 */
	public function fillMatchs()
	{
		$eventId = Bn::getValue('event_id');
		$locale = Bn::getLocale();
		require_once "Object/Locale/$locale/Omember.inc";

		// Joueur enregistre dans le tournoi
		$q = new Bn_query('draws, rounds, ties, matchs, p2m');
		$q->setFields('mtch_id, mtch_tieid, mtch_disci, mtch_discipline,  mtch_id, mtch_uniid');
		$q->addField('count(p2m_id)', 'nb');
		$q->addWhere('draw_eventid=' . $eventId);
		$q->addWhere('draw_id=rund_drawid');
		$q->addWhere('rund_id=tie_roundid');
		$q->addWhere('tie_id=mtch_tieid');
		$q->addWhere('mtch_id=p2m_matchid');
		$q->group('mtch_id');

		$matchs = $q->getGridRows(0, true);
		$gvRows = new GvRows($q);
		$param  = $q->getGridParam();
		$i = $param->first;

		// Requete pour chercher les classements des joueurs dans la base fede
		foreach ($matchs as $match)
		{
			$row[0] = $i++;
			$row[1] = $match['mtch_tieid'];
			$row[2] = $match['mtch_disci'];
			$row[3] = $match['mtch_discipline'];
			$row[4] = $match['mtch_id'];
			$row[5] = $match['mtch_uniid'];
			$row[6] = $match['nb'];

			$bal= new Bn_balise();
			$lnk = $bal->addLink('', BADM_PAGE_MATCH . '&matchId=' . $match['mtch_id'], null, 'targetBody');
			$lnk->addimage('', 'Bn/Img/magnifier.png', 'loop');
			$row[7] = $bal->toHtml();

			$gvRows->addRow($row);
		}
		$gvRows->display();
		return false;
	}

	/**
	 * Page avec la liste des matchs et les paires
	 *
	 */
	public function pageMatchs()
	{
		$body = new Body();

		$d = $body->addDiv('pAction', 'bn-div-line bn-div-auto');
		$d->addEdit('txtSearch', 'Chercher', '', 20);
		$btn = $d->addButton('btnSearch', 'Go');

		// Liste des inscrits
		$body->addBreak();
		$grid = $body->addGridview('gridPlayers', BADM_FILL_MATCHS, 50);
		$col = $grid->addColumn('#',   'num');
		$col->setLook(30, 'left', false);
		$col = $grid->addColumn('Tieid',   'tieid');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('disci',   'disci');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('discipline',   'discipline');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('matchid',   'matchid');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('uniid',   'uniid');
		$col->setLook(140, 'left', false);
		$col = $grid->addColumn('Nb de paires',  'nb');
		$col->setLook(70, 'left', false);
		$col->initsort('desc');
		$col = $grid->addColumn('Action', 'act', false);
		$col->setLook(70, 'center', false);

		$col = $grid->addColumn('id', 'id', false, false, true);

		$grid->setLook('Match et nombres de paires', 0, "'auto'");

		$body->addBreak();
		//$body->addJQready('pagePairs()');

		$body->display(MENU_BADNET, 4);
	}

	public function maint()
	{
		$q = new Bn_query('members, registration');
		$q->setFields('regi_longname, regi_shortname, mber_id');
		$q->setWhere('mber_id=regi_memberid');
		$q->addWhere("mber_secondname LIKE 'DUMONT'");
		$mems = $q->getRows();
		//print_r($mems);

		$q->setTables('members');
		foreach($mems as $mem)
		{
			$q->setValue('mber_licence', '');

			$famname = substr($mem['regi_shortname'], 0, strlen($mem['regi_shortname']) -3);
			$q->addValue('mber_secondname', $famname);

			$name = substr($mem['regi_longname'], strlen($famname)+1);
			$q->addValue('mber_firstname', $name);

			$q->setWhere('mber_id=' . $mem['mber_id']);
			$q->updateRow();
			//print_r($q);
		}
	}

	/**
	 * Mise a jour des champ de classement dans i2p
	 */
	public function updateI2p()
	{
		// mise a jour des infos discipline pairs (simple double ou mixte)
		$q = new Bn_query('pairs');
		$q->addField('pair_id');
		$q->addField('pair_disci');
		$q->addWhere('pair_disci<7');
		$pairs = $q->getRows();
		$q->setTables('pairs');
		foreach($pairs as $pair)
		{
			switch($pair['pair_disci'])
			{
				case 1:
				case 2:
				case 6:
					$q->setValue('pair_disci', 110);
					break;
				case 5:
					$q->setValue('pair_disci', 112);
					break;
				default :
					$q->setValue('pair_disci', 111);
					break;
			}
			$q->updateRow('pair_id=' . $pair['pair_id']);
		}

		// mise a jour des infos de classement dans i2p si non renseignes
		$q->setTables('events');
		$q->addField('evnt_id');
		$eventIds = $q->getCol();
		$eventIds[] = Bn::getValue('event_id');
		
		foreach ($eventIds as $eventId)
		{
			echo "eventId=$eventId";
			$q->setTables('registration');
			$q->addTable('ranks', 'rank_regiid=regi_id');
			$q->addTable('i2p', 'i2p_regiid=regi_id');
			$q->addTable('pairs', 'i2p_pairid=pair_id');
			$q->addField('rank_rank', 'rang');
			$q->addField('rank_average', 'average');
			$q->addField('rank_rankdefid', 'rankdefid');
			$q->addField('i2p_id');
			$q->addWhere('pair_disci=rank_discipline');
			//$q->addWhere('i2p_rankdefid=0');
			$q->addWhere('regi_eventid=' .$eventId);
			$i2ps = $q->getRows();
			$q->setTables('i2p');
			foreach($i2ps as $i2p)
			{
				$q->setValue('i2p_classe', $i2p['rang']);
				$q->addValue('i2p_cppp', $i2p['average']);
				$q->addValue('i2p_rankdefid', $i2p['rankdefid']);
				$q->updateRow('i2p_id=' . $i2p['i2p_id']);
			}
		}

	}

	/**
	 * Mise a jour du nouveau champ discipline dans Ranks et matchs
	 */
	public function updateDiscipline()
	{
		$eventId = Bn::getValue('event_id');
		$q = new Bn_query('ranks');
		$q->setValue('rank_discipline', OMATCH_DISCIPLINE_SINGLE);
		$q->setWhere('(rank_disci='.OMATCH_DISCI_MS . ' OR rank_disci=' . OMATCH_DISCI_WS . ')');
		$q->updateRow();
		$q->setValue('rank_discipline', OMATCH_DISCIPLINE_DOUBLE);
		$q->setWhere('(rank_disci='.OMATCH_DISCI_MD . ' OR rank_disci=' . OMATCH_DISCI_WD . ')');
		$q->updateRow();
		$q->setValue('rank_discipline', OMATCH_DISCIPLINE_MIXED);
		$q->setWhere('rank_disci='.OMATCH_DISCI_XD);
		$q->updateRow();
		$q->setTables('matchs');
		$q->setValue('mtch_disci', OMATCH_DISCIPLINE_SINGLE);
		$q->setWhere('(mtch_discipline='.OMATCH_DISCI_MS . ' OR mtch_discipline=' . OMATCH_DISCI_WS . ')');
		$q->updateRow();
		$q->setValue('mtch_disci', OMATCH_DISCIPLINE_DOUBLE);
		$q->setWhere('(mtch_discipline='.OMATCH_DISCI_MD . ' OR mtch_discipline=' . OMATCH_DISCI_WD . ')');
		$q->updateRow();
		$q->setValue('mtch_disci', OMATCH_DISCIPLINE_MIXED);
		$q->setWhere('mtch_discipline='.OMATCH_DISCI_XD);
		$q->updateRow();

		/*
		 $q->setTables('rounds, draws');
		 $q->setFields('rund_id');
		 $q->setWhere('rund_drawid=draw_id');
		 $q->addWhere('draw_eventid=' . $eventId);
		 $roundIds = $q->getCol();
		 foreach($roundIds as $roundId)
		 {
			Oteam::updateGroupRank($roundId);
			}
			$dbName = Bn::getConfigValue('base', 'database_poona');
			$qp = new Bn_query("$dbName.JOUEUR", '_poona');
			$qp->addField('distinct jou_ins_id');
			$qp->addWhere('jou_ins_id > 0');
			$instances = $qp->getCol();
			$qp->setTables('INSTANCE');
			foreach($instances as $instance)
			{
			$qp->setValue('ins_nom', $instance);
			$qp->addValue('ins_id',  $instance);
			$qp->addValue('ins_is_active', 1);
			$qp->setWhere("ins_id='" . $instance ."'");
			$qp->replaceRow(false);
			}
			*/
	}

	/**
	 * Envoi la liste des inscrits speciaux WO
	 */
	public function fillWo()
	{
		$eventId = Bn::getValue('event_id');
		$locale = Bn::getLocale();
		require_once "Object/Locale/$locale/Omember.inc";

		// Joueur WO par equipes
		$q = new Bn_query('members, teams, registration');
		$q->setFields('mber_id, team_name, mber_sexe, mber_firstname, mber_secondname, regi_longname, regi_id');
		$q->leftJoin('ranks s', 's.rank_regiid=regi_id AND s.rank_discipline=' . OMATCH_DISCIPLINE_SINGLE);
		$q->leftJoin('rankdef ds', 's.rank_rankdefid=ds.rkdf_id');
		$q->addField('ds.rkdf_label', 'ranks');
		$q->leftJoin('ranks d', 'd.rank_regiid=regi_id AND d.rank_discipline=' . OMATCH_DISCIPLINE_DOUBLE);
		$q->leftJoin('rankdef dd', 'd.rank_rankdefid=dd.rkdf_id');
		$q->addField('dd.rkdf_label', 'rankd');
		$q->leftJoin('ranks m', 'm.rank_regiid=regi_id AND m.rank_discipline=' . OMATCH_DISCIPLINE_MIXED);
		$q->leftJoin('rankdef dm', 'm.rank_rankdefid=dm.rkdf_id');
		$q->addField('dm.rkdf_label', 'rankm');

		$q->addWhere('mber_id=regi_memberid');
		$q->addWhere('regi_teamid = team_id');
		$q->addWhere('regi_eventid=' . $eventId);
		$q->addWhere('mber_id < 0');
		$search = Bn::getValue('txtSearch');
		if ( !empty($search) )
		{
			$where = "team_name LIKE '%" . $search . "%'";
			$q->addWhere($where);
		}
		$players = $q->getGridRows(0, true);
		$gvRows = new GvRows($q);
		$param  = $q->getGridParam();
		$i = $param->first;

		// Requete pour chercher les classements des joueurs dans la base fede
		foreach ($players as $player)
		{
			$row[0] = $i++;
			$row[1] = $player['team_name'];
			$row[2] = constant('LABEL_' . $player['mber_sexe']);
			$row[3] = $player['mber_secondname'];
			$row[4] = $player['mber_firstname'];
			$row[5] = $player['regi_longname'];
			$row[6] = $player['regi_id'];
			$row[7] = $player['ranks'] . ',' . $player['rankd'] . ',' . $player['rankm'];
			$gvRows->addRow($row);
		}
		$gvRows->display();
		return false;
	}

	/**
	 * Page avec la liste les joueurs WO de chaque equipe
	 *
	 */
	public function pageWo()
	{
		$body = new Body();

		$d = $body->addDiv('pAction', 'bn-div-line bn-div-auto');
		$d->addEdit('txtSearch', 'Chercher', '', 20);
		$btn = $d->addButton('btnSearch', 'Go');

		$btn = $d->addButton('btnUpdt', 'Créer WO', BADM_UPDATE_WO);

		// Liste des inscrits
		$body->addBreak();
		$grid = $body->addGridview('gridPlayers', BADM_FILL_WO, 50);
		$col = $grid->addColumn('#',   'num');
		$col->setLook(10, 'left', false);
		$col = $grid->addColumn('Equipe',   'team');
		$col->setLook(200, 'left', false);
		$col = $grid->addColumn('Genre',   'gender');
		$col->setLook(50, 'left', false);
		$col = $grid->addColumn('Nom',   'name');
		$col->setLook(100, 'left', false);
		$col = $grid->addColumn('Prénom',   'prenom');
		$col->setLook(100, 'left', false);
		$col = $grid->addColumn('Nom long',   'logname');
		$col->setLook(95, 'left', false);
		$col = $grid->addColumn('Regi_id',   'regiid');
		$col->setLook(90, 'left', false);
		$col = $grid->addColumn('Rangs',   'ranks');
		$col->setLook(100, 'left', false);

		$grid->setLook('Joueurs WO des équipes', 0, "'auto'");

		$body->addJqready('pageWo();');

		$body->display(MENU_BADNET, 4);
	}

	/**
	 * Mise a jour des joueurs WO pour les equipes IC
	 */
	public function updateWo()
	{
		$eventId = Bn::getValue('event_id');

		//recuperer les equipes du tournoi
		$q = new Bn_query('teams');
		$q->setFields('team_id');
		$q->addWhere('team_eventid=' . $eventId);
		$teamsId = $q->getCol();

		// Traiter chaque equipe
		foreach($teamsId as $teamId)
		{
			$oTeam = new Oteam($teamId);
			$oTeam->addWoPlayers();
		}
		return BADM_PAGE_WO;
	}

	
	/**
	 * Confirmation de supression d'une paire
	 */
	public function confirmDeletePair()
	{
		$body = new Body();
		$form = $body->addForm('frmDelete', BADM_DELETE_PAIR, 'targetDlg');
		$form->addHidden('pairId', Bn::getValue('pairId'));
		$form->addP('', 'Vous allez supprimer la paire sélectionnée. Impossible de la récupérer.');
		$form->addButtonValid('', 'Supprimer');
		$b = $form->addButtonCancel('btnCancel', 'Abandonner');
		$body->display();
		return false;
	}

	/**
	 * Suppression d'une paire
	 *
	 * @return unknown
	 */
	public function deletePair()
	{
		// Verifier le niveau d'autorisation
		if ( Oaccount::isLoginAdmin() )
		{
			// Supprimer la paire si elle n'a pas de match
			$pairId = Bn::getValue('pairId');
			$q = new Bn_query('p2m, matchs');
			$q->setFields('count(*)');
			$q->addWhere('p2m_matchid = mtch_id');
			$q->addWhere('p2m_pairid=' . $pairId);
			$nb = $q->getFirst();
			if ( $nb == 0)
			{
				$q->setTables('i2p');
				$q->deleteRow('i2p_pairid=' . $pairId);
				$q->setTables('pairs');
				$q->deleteRow('pair_id=' . $pairId);
				$q->setTables('p2m');
				$q->deleteRow('p2m_pairid=' . $pairId);
			}
		}
		// Affichage du message de confirmation
		$body = new Body();
		$body->addP('', 'Paire supprimée');
		$b= $body->addButtonCancel('btnClose', 'Fermer');
		$body->addJQReady('deletePair();');
		$body->display();
		return false;
	}

	/**
	 * Envoi la liste des paires d'un inscrit
	 */
	public function fillRegipairs()
	{
		$regiId  = Bn::getValue('regiId');
		$eventId = Bn::getValue('event_id');

		// Paires enregistre dans le tournoi
		$q = new Bn_query('i2p, pairs');
		$q->leftJoin('p2m', 'pair_id=p2m_pairid');
		$q->leftJoin('draws', 'pair_drawid=draw_id');
		$q->setFields('pair_id, pair_disci, pair_state, pair_status, draw_name');
		$q->addField('count(p2m_id)', 'nb');
		$q->addWhere('i2p_regiid=' . $regiId);
		$q->addWhere('i2p_pairid=pair_id');
		$q->group('pair_id');

		$pairs = $q->getGridRows(0, true);
		$gvRows = new GvRows($q);
		$param  = $q->getGridParam();
		$i = $param->first;

		// Requete pour chercher les classements des joueurs dans la base fede
		foreach ($pairs as $pair)
		{
			$row[0] = $i++;
			$row[1] = constant('LABEL_' . $pair['pair_disci']);
			$row[2] = constant('LABEL_' . $pair['pair_state']);
			$row[3] = constant('LABEL_' . $pair['pair_status']);
			$row[4] = $pair['draw_name'];
			$row[5] = $pair['nb'];

			if ($pair['nb'] == 0)
			{
				$bal= new Bn_balise();
				$lnk = $bal->addLink('', BADM_CONFIRM_DELETE_PAIR . '&pairId=' . $pair['pair_id'], null, 'targetDlg');
				$lnk->addimage('', 'delete.png', 'loop');
				$lnk->completeAttribute('class', 'bn-delete');
				$row[6] = $bal->toHtml();
			}
			else $row[6] = '';

			$gvRows->addRow($row);
		}
		$gvRows->display();
		return false;
	}

	/**
	 * Page d'inscrits avec ses paires
	 *
	 */
	public function pagePair()
	{
		$regiId = Bn::getValue('regiId');
		$body = new Body();
		$oRegi = new Oregi($regiId);

		$btn = $body->addButton('btnBack', 'Back', BADM_PAGE_PAIRS, '', 'targetBody');

		$body->addInfo('', 'Nom', $oRegi->getVal('longname'));

		// Liste des paires
		$body->addBreak();
		$url = BADM_FILL_REGIPAIRS . '&regiId=' . $regiId;
		$grid = $body->addGridview('gridPairs', $url, 50);
		$col = $grid->addColumn('#',   'num');
		$col->setLook(30, 'left', false);
		$col = $grid->addColumn('Discipline',  'disci');
		$col->setLook(120, 'left', false);
		$col = $grid->addColumn('State',   'state');
		$col->setLook(120, 'left', false);
		$col = $grid->addColumn('Status', 'status');
		$col->setLook(120, 'left', false);
		$col = $grid->addColumn('Tableau', 'draw');
		$col->setLook(160, 'left', false);
		$col = $grid->addColumn('Nb',  'nb');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('Action',  'act');
		$col->setLook(70, 'center', false);
		$grid->setLook('Joueurs et paires', 0, "'auto'");

		$body->addBreak();
		//$body->addJQready('pagePairs()');

		$body->display(MENU_BADNET, 4);
	}

	/**
	 * Envoi la liste des inscrits avec leur classement
	 */
	public function fillPairs()
	{
		$eventId = Bn::getValue('event_id');
		$locale = Bn::getLocale();
		require_once "Object/Locale/$locale/Omember.inc";

		// Joueur enregistre dans le tournoi
		$q = new Bn_query('members, registration, i2p');
		$q->setFields('mber_id, mber_sexe, mber_firstname, mber_secondname, regi_id');
		$q->addField('count(i2p_id)', 'nb');
		$q->addWhere('mber_id=regi_memberid');
		$q->addWhere('regi_eventid=' . $eventId);
		$q->addWhere('regi_id=i2p_regiid');
		$q->group('regi_id');

		$search = Bn::getValue('txtSearch');
		if ( !empty($search) )
		{
			$where = "(mber_secondname LIKE '%" . $search . "%'";
			$where .= " OR mber_firstname LIKE '%" . $search . "%'";
			$where .= " OR mber_licence LIKE '%" . $search . "%')";
			$q->addWhere($where);
		}
		$players = $q->getGridRows(0, true);
		$gvRows = new GvRows($q);
		$param  = $q->getGridParam();
		$i = $param->first;

		// Requete pour chercher les classements des joueurs dans la base fede
		foreach ($players as $player)
		{
			$row[0] = $i++;
			$row[1] = constant('LABEL_' . $player['mber_sexe']);
			$row[2] = $player['mber_secondname'];
			$row[3] = $player['mber_firstname'];
			$row[4] = $player['regi_id'];
			$row[5] = $player['nb'];

			$bal= new Bn_balise();
			$lnk = $bal->addLink('', BADM_PAGE_PAIR . '&regiId=' . $player['regi_id'], null, 'targetBody');
			$lnk->addimage('', 'Bn/Img/magnifier.png', 'loop');
			$row[6] = $bal->toHtml();

			$gvRows->addRow($row);
		}
		$gvRows->display();
		return false;
	}

	/**
	 * Page avec la liste des inscits et leur classement
	 *
	 */
	public function pagePairs()
	{
		$body = new Body();

		$d = $body->addDiv('pAction', 'bn-div-line bn-div-auto');
		$d->addEdit('txtSearch', 'Chercher', '', 20);
		$btn = $d->addButton('btnSearch', 'Go');

		// Liste des inscrits
		$body->addBreak();
		$grid = $body->addGridview('gridPlayers', BADM_FILL_PAIRS, 50);
		$col = $grid->addColumn('#',   'num');
		$col->setLook(30, 'left', false);
		$col = $grid->addColumn('Genre',   'gender');
		$col->setLook(50, 'left', false);
		$col = $grid->addColumn('Nom',   'name');
		$col->setLook(120, 'left', false);
		$col = $grid->addColumn('Prénom',   'prenom');
		$col->setLook(120, 'left', false);
		$col = $grid->addColumn('Regi_id',   'regiid');
		$col->setLook(120, 'left', false);
		$col = $grid->addColumn('Nb',  'nb');
		$col->setLook(70, 'left', false);
		$col->initsort('desc');
		$col = $grid->addColumn('Action', 'act', false);
		$col->setLook(70, 'center', false);

		$col = $grid->addColumn('id', 'id', false, false, true);

		$grid->setLook('Joueurs et paires', 0, "'auto'");

		$body->addBreak();
		//$body->addJQready('pagePairs()');

		$body->display(MENU_BADNET, 4);
	}

	/**
	 * Mise a jour des classements des inscrits a partir du classement federal
	 *
	 */
	public function updateRanking()
	{
		$eventId = Bn::getValue('event_id');
		// Liste des classements
		$q = new Bn_query('rankdef,events');
		$q->setFields('rkdf_label, rkdf_id');
		$q->addWhere('rkdf_system=evnt_ranksystem');
		$q->addWhere('evnt_id=' . $eventId);
		$q->setOrder('rkdf_point');
		$ranks = $q->getLov();

		// Liste des incrits et leur classements
		$q = new Bn_query('members, registration, ranks');
		$q->setFields('rank_id, mber_licence, mber_fedeid, rank_discipline, regi_id');
		$q->addWhere('mber_id=regi_memberid');
		$q->addWhere('regi_id=rank_regiid');
		$q->addWhere('mber_fedeid>0');
		$q->addWhere('regi_eventid=' . $eventId);
		$regis = $q->getRows();

		$dbName = Opoona::getDbName();
		$qp = new Bn_query("`$dbName`.JOUEUR", '_poona',  $dbName);

		$dbName = Bn::getConfigValue('base', 'database_poona');
		$isSquash = Bn::getConfigValue('squash', 'params');
		if ($isSquash) $qp->addField('jou_rank', 'rank');
		else $qp->addField(999999, 'rank');
		$qp->addField('jou_moyenne_montee_simple', 'avs');
		$qp->addField('jou_moyenne_montee_double', 'avd');
		$qp->addField('jou_moyenne_montee_mixte',  'avm');
		$qp->leftJoin("$dbName.TYPE_CLASSEMENT s", 'JOU_TCL_ID_SIMPLE_OFF=s.TCL_ID');
		$qp->addField('s.TCL_NOM', 'levels');
		$qp->leftJoin("$dbName.TYPE_CLASSEMENT d", 'JOU_TCL_ID_DOUBLE_OFF=d.TCL_ID');
		$qp->addField('d.TCL_NOM', 'leveld');
		$qp->leftJoin("$dbName.TYPE_CLASSEMENT m", 'JOU_TCL_ID_MIXTE_OFF=m.TCL_ID');
		$qp->addField('m.TCL_NOM', 'levelm');

		$q->setTables('ranks');
		foreach($regis as $regi)
		{
			if (empty($player[$regi['regi_id']]) )
			{
				$qp->setWhere('JOU_PER_ID=' . $regi['mber_fedeid']);
				$player[$regi['regi_id']] = $qp->getRow();
			}
			if ($regi['rank_discipline'] == OMATCH_DISCIPLINE_SINGLE)
			{
				$q->setValue('rank_rankdefid', $ranks[$player[$regi['regi_id']]['levels']]);
				$q->addValue('rank_average', $player[$regi['regi_id']]['avs']);
			}
			if ($regi['rank_discipline'] == OMATCH_DISCIPLINE_DOUBLE)
			{
				$q->setValue('rank_rankdefid', $ranks[$player[$regi['regi_id']]['leveld']]);
				$q->addValue('rank_average', $player[$regi['regi_id']]['avd']);
			}
			if ($regi['rank_discipline'] == OMATCH_DISCIPLINE_MIXED)
			{
				$q->setValue('rank_rankdefid', $ranks[$player[$regi['regi_id']]['levelm']]);
				$q->addValue('rank_average', $player[$regi['regi_id']]['avm']);
			}
			$q->addValue('rank_rank', $player[$regi['regi_id']]['rank']);
			$q->setWhere('rank_id=' . $regi['rank_id']);
			$q->updateRow();
		}
	}

	/**
	 * Mise a NC des classemement des joueurs inscrits
	 */
	public function razRanking()
	{
		$eventId = Bn::getValue('event_id');

		// Recuperer le classement NC
		$q = new bn_Query('events, rankdef');
		$q->setFields('rkdf_id');
		$q->setWhere("rkdf_label='NC'");
		$q->addWhere('evnt_id = ' . $eventId);
		$q->addWhere('evnt_ranksystem = rkdf_system');
		$rankNc = $q->getFirst();

		$q = new Bn_query('registration');
		$q->setFields('regi_id');
		$q->addWhere('regi_eventid=' . $eventId);
		$str = $q->getQuery();

		$q->setTables('ranks');
		$q->addValue('rank_rankdefid', $rankNc);
		$q->addValue('rank_average', 0);
		$q->addValue('rank_rank', 999999);
		$q->setWhere('rank_regiid IN (' . $str . ')');
		$q->updateRow();
		return BADM_PAGE_RANKING;
	}

	/**
	 * Envoi la liste des inscrits avec leur classement
	 */
	public function fillRanking()
	{
		$eventId = Bn::getValue('event_id');
		$locale = Bn::getLocale();
		require_once "Object/Locale/$locale/Omember.inc";

		// Joueur enregistre dans le tournoi
		$q = new Bn_query('members, registration');
		$fields = array('mber_id','mber_sexe', 'mber_secondname', 'mber_firstname', 'mber_licence', 'mber_fedeid', 'mber_id');
		$q->setFields($fields);
		$q->leftJoin('ranks s', 'regi_id=s.rank_regiid AND s.rank_discipline = ' . OMATCH_DISCIPLINE_SINGLE);
		$q->leftJoin('rankdef rs', 's.rank_rankdefid = rs.rkdf_id');
		$q->addField('rs.rkdf_label', 'levels');
		$q->addField('s.rank_rank', 'ranks');

		$q->leftJoin('ranks d', 'regi_id=d.rank_regiid AND d.rank_discipline = ' . OMATCH_DISCIPLINE_DOUBLE);
		$q->leftJoin('rankdef rd', 'd.rank_rankdefid = rd.rkdf_id');
		$q->addField('rd.rkdf_label', 'leveld');

		$q->leftJoin('ranks m', 'regi_id=m.rank_regiid AND m.rank_discipline = ' . OMATCH_DISCIPLINE_MIXED);
		$q->leftJoin('rankdef rm', 'm.rank_rankdefid = rm.rkdf_id');
		$q->addField('rm.rkdf_label', 'levelm');

		$q->addWhere('mber_id=regi_memberid');
		$q->addWhere('regi_eventid=' . $eventId);
		$search = Bn::getValue('txtSearch');
		if ( !empty($search) )
		{
			$where = "(mber_secondname LIKE '%" . $search . "%'";
			$where .= " OR mber_firstname LIKE '%" . $search . "%'";
			$where .= " OR mber_licence LIKE '%" . $search . "%')";
			$q->addWhere($where);
		}
		$players = $q->getGridRows(0, true);
		$gvRows = new GvRows($q);
		$i = $param->first;

		// Requete pour chercher les classements des joueurs dans la base fede
		$dbName = Opoona::getDbName();
		$qp = new Bn_query("`$dbName`.JOUEUR", '_poona',  $dbName);

		$dbName = Bn::getConfigValue('base', 'database_poona');
		$qp->leftJoin("$dbName.TYPE_CLASSEMENT s", 'JOU_TCL_ID_SIMPLE_OFF=s.TCL_ID');
		$qp->addField('s.TCL_NOM', 'levels');
		$qp->addField('JOU_RANK', 'ranks');
		$qp->leftJoin("$dbName.TYPE_CLASSEMENT d", 'JOU_TCL_ID_DOUBLE_OFF=d.TCL_ID');
		$qp->addField('d.TCL_NOM', 'leveld');
		$qp->leftJoin("$dbName.TYPE_CLASSEMENT m", 'JOU_TCL_ID_MIXTE_OFF=m.TCL_ID');
		$qp->addField('m.TCL_NOM', 'levelm');

		foreach ($players as $player)
		{

			$row[0] = $i++;
			$row[1] = constant('LABEL_' . $player['mber_sexe']);
			$row[2] = $player['mber_secondname'];
			$row[3] = $player['mber_firstname'];
			$row[4] = $player['mber_licence'];
			$row[5] = $player['mber_fedeid'];
			$row[6] = $player['mber_id'];
			if ($player['mber_fedeid'] > 0)
			{
				$qp->setWhere('JOU_PER_ID=' . $player['mber_fedeid']);
				$fede = $qp->getRow();
				$row[7] = $fede['levels'] . ',' . $fede['leveld'] . ',' . $fede['levelm'] . '-' . $fede['ranks'];
			}
			else $row[7] = '';

			$row[8] = $player['levels'] . ',' . $player['leveld'] . ',' . $player['levelm'] . '-' . $player['ranks'];
			//if($row[7] != $row[8])
			$gvRows->addRow($row);
		}
		$gvRows->display();
		return false;
	}

	/**
	 * Page avec la liste des inscits et leur classement
	 *
	 */
	public function pageRanking()
	{
		$body = new Body();

		$d = $body->addDiv('pAction', 'bn-div-line bn-div-auto');
		$d->addEdit('txtSearch', 'Chercher', '', 20);
		$btn = $d->addButton('btnSearch', 'Go');

		$btn = $d->addButton('btnRaz', 'Raz clt.', BADM_RAZ_RANKING);

		$btn = $d->addButton('btnMaj', 'Maj clt.', BADM_UPDATE_RANKING);

		// Liste des inscrits
		$body->addBreak();
		$grid = $body->addGridview('gridPlayers', BADM_FILL_RANKING, 50);
		$col = $grid->addColumn('#',   'num');
		$col->setLook(30, 'left', false);
		$col = $grid->addColumn('Genre',   'gender');
		$col->setLook(50, 'left', false);
		$col = $grid->addColumn('Nom',   'name');
		$col->setLook(120, 'left', false);
		$col->initsort();
		$col = $grid->addColumn('Prénom',   'prenom');
		$col->setLook(120, 'left', false);
		$col = $grid->addColumn('License',  'license');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('Id fede', 'poona');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('mber_id', 'mberid');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn('Clt fede', 'cltfede');
		$col->setLook(100, 'center', false);
		$col = $grid->addColumn('Clt BadNet', 'cltbadnet');
		$col->setLook(100, 'center', false);
		$col->addOption('search', "'false'");

		$col = $grid->addColumn('id', 'id', false, false, true);

		$grid->setLook('Joueurs inscrits au tournoi', 0, "'auto'");

		$body->addBreak();
		$body->addJQready('pageRanking()');

		$body->display(MENU_BADNET, 4);


	}

	/**
	 * Affichage de la page de choix
	 */
	public function pageChoice()
	{
		$eventId = Bn::getValue('event_id');

		$oEvent = new Oevent($eventId);

		$body = new Body();
		// Commandes generiques
		$div = $body->addDiv('', 'bn-menu-right');
		$p = $div->addP();
		$lnk = $p->addLink('lnkHome', BNETADM_DISPATCH, LOC_LABEL_ACCOUNT, 'targetBody');
		$lnk->addIcon('home');
		$p = $div->addP();
		$lnk = $p->addLink('lnkMain', BEVENT_DISPATCH, LOC_LABEL_MAIN_PAGE, 'targetBody');
		$lnk->addIcon('arrowreturnthick-1-w');
		
		$t = $body->addP('', $oEvent->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', $oEvent->getVal('date'));

		$div = $body->addRichDiv('divEvent', 'bn-left-div');
		$t = $div->addP('tltCivil', '', 'bn-title-3');
		$t->addBalise('span', '',  'Infos générales de la compétition');
		$div->addInfo('name',  'Nom',  $oEvent->getVal('name'));
		$div->addInfo('date',  'Date',  $oEvent->getVal('date'));
		$div->addInfo('place', 'lieu', $oEvent->getVal('place'));
		$div->addInfo('organizer', 'Organisateur',  $oEvent->getVal('organizer'));
		$div->addInfo('firstday', 'Premier jour', Bn::date($oEvent->getVal('firstday'), 'd-m-Y'));
		$div->addInfo('lastday', 'Dernier jour',  Bn::date($oEvent->getVal('lastday'), 'd-m-Y'));

		$div = $body->addRichDiv('divGoto', 'bn-right-div');
		$div->addP('', 'Maintenance de la compétition', 'bn-p-info');
		$div->addP()->addLink('', BADM_PAGE_REGISTRATIONS, 'Gestions des incrits', 'targetBody');
		$div->addP()->addLink('', BADM_PAGE_RANKING, 'Mise à jour des classements', 'targetBody');
		$div->addP()->addLink('', BADM_PAGE_PAIRS,   'Gestions des nombres de paires par joueur', 'targetBody');
		$div->addP()->addLink('', BADM_PAGE_PLAYERS, 'Gestions des nombres de joueurs par paire', 'targetBody');
		$div->addP()->addLink('', BADM_PAGE_MATCHS,  'Gestions des nombres des paires par match', 'targetBody');
		$div->addP()->addLink('', BADM_PAGE_WO, 'Ajout joueur WO aux équipes d\'un IC', 'targetBody');
		$div->addP()->addLink('lnkDiscipline', BADM_UPDATE_DISCIPLINE, 'Maj nouveau champ discipline de ranks et matchs');
		$div->addP()->addLink('lnkI2p', BADM_UPDATE_I2P, 'Mise ajour info classement dans i2p');
		$div->addP()->addLink('lnkUniid', BADM_UPDATE_UNIID, 'Mise à jour des uniid');
		$div->addP()->addLink('lnkTeams', BADM_PAGE_TEAMS, 'Mise à jour des équipes', 'targetBody');
		//$this->maint();
		$body->addJQReady('pageChoice();');
		$body->display(MENU_BADNET, 4);
		return false;
	}


}
?>
