<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

require_once 'Object.php';
require_once 'Oevent.inc';
require_once 'Object/Omember.inc';
require_once 'Object/Omatch.inc';
 
$locale = BN::getLocale();
require_once 'Locale/' . $locale . '/Oevent.inc';

require_once 'Badnet/Event/Event.inc';
require_once 'Badnet/Team/Team.inc';
require_once 'Badnet/Adm/Adm.inc';
require_once 'Badnet/Div/Div.inc';
include_once 'Badnetclub/Caccount/Caccount.inc';

class OEvent extends Object
{
    var $_pathPoster = '../img/poster/'; 
	
	/**
	 * En tete du tournoi
	 */
	public function header($aDiv)
	{
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$div = $aDiv->addDiv('', 'bn-div-clear');
		
		$d = $div->addDiv('', 'bn-menu-right');
		$p = $d->addP();
		$lnk = $p->addLink('lnkMain', BEVENT_PAGE_EVENTS, 'Tournois', 'targetBody');
		$lnk->addIcon('person');
		
		$lnk = $p->addLink('lnkLogout', BNETADM_LOGOUT, LOC_LABEL_LOGOUT, 'targetBody');
		$lnk->addIcon('power');
		
		
		$t = $div->addP('', $this->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', $this->getVal('date'));
		$b = new Bn_balise();
		$b->addimage('', $this->getVal('pbl'). '.png', $this->getVal('pbl'));
		$t->insertContent($b);
		
		$div = $aDiv->addDiv('', 'bn-div-clear bn-menu');
	
		$d = $div->addDiv('', 'bn-menu-left');
		$p = $d->addP();
		$lnk = $p->addLink('lnkHome', BEVENT_DISPATCH, 'Tournoi', 'targetBody');
		$lnk->addicon();

		if ( $this->isIc() )
		{
			$lnk = $p->addLink('', BDIV_PAGE_GROUPS, 'Groupes', 'targetBody');
			$lnk->addIcon();
			$lnk = $p->addLink('lnkTeams', BTEAM_PAGE_TEAMS, 'Equipes', 'targetBody');
			$lnk->addIcon();
		}
		/*
		$lnk = $p->addLink('lnkTransfert', BADNET_TRANSFERT, 'Transfert', 'targetBody');
		$lnk->addicon('transfer-e-w');
		
		if ( Oaccount::isLoginAdmin() )
		{
			$lnk = $p->addLink('lnkAdmin', BADM_PAGE_CHOICE, 'Administration', 'targetBody');
			$lnk->addIcon();
		}
		*/
		
		$div->addBreak();
	}
    
	public function getPoster()
	{
		return $this->_pathPoster . $this->getVal('poster');
	}
	
	
	/**
	 * Ajout du poster
	 */
	public function addPoster($aTmpImgFile, $aName)
	{
		$this->deletePoster();
		$name = $this->getVal('id') . '_' . $aName;
		$dest = $this->_pathPoster . $name;
		move_uploaded_file($aTmpImgFile, $dest);
	    chmod($dest, 0777);	      
		$this->setVal('poster', $name);
		$this->save();
		return false;
	}
	
	/**
	 * Supression du poster
	 */
	public function deletePoster()
	{
		$poster = $this->getVal('poster');
		unlink( $this->_pathPoster . $poster);
		$this->setVal('poster', '');
		$this->save();
		return false;
	}
	
	
	/**
	 * Renvoi la liste des association
	 *
	 */
	public function getLovAssoc()
	{
		$q = new Bn_query('teams');
		$q->addTable('a2t', 'a2t_teamid=team_id');
		$q->addTable('assocs', 'a2t_assoid=asso_id');
		$q->setFields('asso_id, asso_name');
		$q->addWhere('team_eventid=' . $this->getVal('id', -1));
		$q->setOrder('asso_name');
		$lov = $q->getLov();
		unset($q);
		return($lov);
	}
	
	
	/**
	 * calcule et retourne la date max de saisie d'une rencontre
	 *
	 */
	public function getDelayCaptain($aSchedule)
	{
		$delay = $this->getVal('delaycaptain');
		if ($delay > 0)
		{
			$dateTie = Bn::date($aSchedule, 'U');
			$dateMax = $dateTie + $delay*24*3600;
		}
		else
		{
			// Pas de delai: le jour meme a minuit (lendemain O heure)
			$schedule = Bn::date($aSchedule, 'Y-m-d');
			$dateTie = Bn::date($schedule, 'U');
			$dateMax = $dateTie + 24*3600;
		}
		return $dateMax;

	}

	/**
	 * Renvoi la liste des id des equipes du tournois
	 *
	 */
	public function getTeams($aSort=null)
	{

		$q = new Bn_query('teams');
		$q->setFields('team_id');
		$q->setWhere('team_eventid=' . $this->getVal('id', -1));
		$order = empty($aSort) ? 'team_name' : 'team_'.$aSort;
		$q->setOrder($order);
		$teamIds = $q->getCol();
		unset($q);
		return($teamIds);
	}

	/**
	 * Renvoi la liste des id des divisions (ou tableau) du tournois
	 *
	 */
	public function getDivisions()
	{
		$q = new Bn_query('draws');
		$q->setFields('draw_id');
		$q->setWhere('draw_eventid=' . $this->getVal('id', -1));
		$q->setOrder('draw_name');
		$drawIds = $q->getCol();
		unset($q);
		return($drawIds);
	}

	/**
	 * Constructeur
	 * @param	integer	$aEventId	Identifiant du tournoi
	 * @return OEvent
	 */
	public function __construct($aEventId=-1, $aPoonaId=-1, $aSeason=null)
	{
		if ($aEventId>0) $this->_loadBadnetEvent($aEventId);
		else if ($aPoonaId>0) $this->_loadPoonaEvent($aPoonaId, $aSeason);
	}

	/**
	 * Le tournoi est-il un interclub
	 *
	 * @return boolean
	 */
	public function isIc()
	{
		return ($this->getVal('type') == OEVENT_TYPE_IC);
	}

	/**
	 * Renvoi les equipes pour lesquelles l'utilisateur est capitaine
	 *
	 * @param int $userId identifiant de l'utilisateur
	 */
	public function getCaptainTeams($aUserId)
	{
		$q = new Bn_query('teams');
		$q->setFields('team_id');
		$q->addWhere('team_eventid=' . $this->getVal('id', -1));
		$q->addWhere('team_captainid=' . $aUserId);
		$teamsId = $q->getCol();
		$oTeams = array();
		foreach($teamsId as $id)
		{
			$oTeams[$id] = new Oteam($id);
		}
		return $oTeams;
	}

	/**
	 * Charger le tournoi depuis la base Poona
	 * @param	integer	$aEventId	Identifiant poona du tournoi
	 * @return OEvent
	 */
	private function _loadPoonaEvent($aPoonaId, $aSeason)
	{
		// Verification que le tournoi n'est pas deja dans badnet
		$q = new Bn_query('eventsextra');
		$q->addField('evxt_eventid');
		$q->addWhere('evxt_fedeid=' . $aPoonaId);
		$eventId = $q->getFirst();
		if ( ! empty($eventId) )
		{
			$this->_loadBadnetEvent($eventId);
			if ($this->getVal('id', -1) > 0 ) return;
		}

		unset ($q);
		// Il n'y est pas. Recuperer alors le tournoi dans poona
		$oPoona = new Opoona();
		$event = $oPoona->getEvent($aPoonaId, $aSeason);
		// Correspondance des categories
		$tmp  = explode(',', $event['catage']);
		for ($i=0; $i<count($tmp); $i++)
		{
			if ( !empty($tmp[$i]) ) $tmp[$i] = Opoona::getCatageBadnet($tmp[$i]);
		}
		$event['catage'] = implode(',', array_unique($tmp));

		// Correspondance des disciplines
		$tmp = explode(',', $event['disci']);
		for ($i=0; $i<count($tmp); $i++)
		{
			if (!empty($tmp[$i]) && $tmp[$i] != 'E') $tmp[$i] = Opoona::getDisciBadnet($tmp[$i]);
		}
		$event['disci'] = implode(',', $tmp);
		$event['liveentries']   = YES;
		$event['livescoring']   = NO;
		$event['type']   = OEVENT_TYPE_INDIVIDUAL;
		$event['nature'] = OEVENT_NATURE_OTHER;
		$event['level']  = OEVENT_LEVEL_REG;
		$event['scoringsystem']  = OEVENT_SCORING_3X21;
		$event['ranksystem']  = OEVENT_RANK_FR2;
		$event['labtype']   = constant('LABEL_' . $event['type']);
		$event['labnature'] = constant('LABEL_' . $event['nature']);
		$event['lablevel']  = constant('LABEL_' . $event['level']);
		$event['labscoringsystem']  = constant('LABEL_' . $event['scoringsystem']);
		$event['labranksystem']  = constant('LABEL_' . $event['ranksystem']);
		$event['date'] = $event['firstday'] . '  ' . $event['lastday'];
		$event['url'] = '';
		$event['dpt'] = '';
		$event['zone'] = '';
		$event['season'] = $aSeason;
		$event['pbl'] = DATA_CONFIDENT;
		$this->setValues($event);
		return;
	}

	/**
	 * Charger le tournoi depuis la base BadNet
	 * @param	integer	$aEventId	Identifiant du tournoi
	 * @return OEvent
	 */
	private function _loadBadnetEvent($aEventId)
	{
		require_once 'Opublic.inc';
		require_once 'Oplayer.inc';
		$q = new Bn_query('events');
		$q->leftjoin('rights', "evnt_id=rght_themeid AND rght_status='".AUTH_MANAGER."' AND rght_theme=" . THEME_EVENT);
		$q->leftjoin('users', 'user_id=rght_userid');
		$q->leftjoin('eventsextra', 'evnt_id=evxt_eventid');
		$q->addField('evnt_id', 'id');
		$q->addField('evnt_pbl', 'pbl');
		$q->addField('evnt_name', 'name');
		$q->addField('evnt_date', 'date');
		$q->addField('evnt_url', 'url');
		$q->addField('evnt_nbvisited', 'nbvisited');
		$q->addField('evnt_level', 'level');
		$q->addField('evnt_place', 'place');
		$q->addField('evnt_organizer', 'organizer');
		$q->addField('evnt_firstday', 'firstday');
		$q->addField('evnt_lastday', 'lastday');
		$q->addField('evnt_deadline', 'deadline');
		$q->addField('evnt_liveentries', 'liveentries');
		$q->addField('evnt_type', 'type');
		$q->addField('evnt_nature', 'nature');
		$q->addField('evnt_datedraw', 'datedraw');
		$q->addField('evnt_numauto', 'numauto');
		$q->addField('evnt_dpt', 'dpt');
		$q->addField('evnt_zone', 'zone');
		$q->addField('evnt_poster', 'poster');
		$q->addField('evnt_convoc', 'convoc');
		$q->addField('evnt_season', 'season');
		$q->addField('evnt_scoringsystem', 'scoringsystem');
		$q->addField('evnt_ranksystem', 'ranksystem');
		$q->addField('evnt_ownerid', 'ownerid');
		$q->addField('evnt_cmt', 'cmt');
		$q->addField('evnt_nbdrawmax', 'nbdrawmax');
		$q->addField('evnt_teamweight', 'teamweight');
		$q->addField('evnt_catage', 'evntcatage');
		$q->addField('evxt_serial', 'serial');
		$q->addField('evxt_catage', 'catage');
		$q->addField('evxt_disci', 'disci');
		$q->addField('evxt_fedeid', 'fedeid');
		$q->addField('evxt_livescoring', 'livescoring');
		$q->addField('evxt_promoted', 'promoted');
		$q->addField('evxt_liveupdate', 'liveupdate');
		$q->addField('evxt_promoimg', 'promoimg');
		$q->addField('evxt_regionid', 'regionid');
		$q->addField('evxt_deptid', 'deptid');
		$q->addField('evxt_email', 'email');
		$q->addField('evxt_delaycaptain', 'delaycaptain');
		//$q->addField('user_email', 'email');

		$q->addWhere('evnt_id=' . $aEventId);
		$event = $q->getRow();
		if ( $q->isError() )
		{
			echo 'erreur Oevent::_loadBadnetEvent';
		}
		unset ($q);
		$event['labtype']   = empty($event['type']) ? '' : constant('LABEL_' . $event['type']);
		$event['labnature'] = empty($event['nature']) ? '' : constant('LABEL_' . $event['nature']);
		$event['lablevel']  = empty($event['level']) ? '' : constant('LABEL_' . $event['level']);
		$event['labscoringsystem']  = empty($event['scoringsystem']) ? '' : constant('LABEL_' . $event['scoringsystem']);
		$event['labranksystem']  = empty($event['ranksystem']) ? '' : constant('LABEL_' . $event['ranksystem']);
		if (empty($event['nbdrawmax'])) $event['nbdrawmax'] = 3;
		$this->setValues($event);
		return;
	}

	/**
	 * Destructeur
	 */
	public function __destruct()
	{
		parent::__destruct();
	}

	/**
	 * Ajout des droits a un utilisateur
	 * @param integer  $aUserId   Identifiant de l'utilisateur
	 * @param integer  $aRight		Droit sur le tournoi
	 */
	public function setRight($aUserId, $aRight)
	{
		$q = new Bn_query('rights');
		$q->addValue('rght_userid', $aUserId);
		$q->addValue('rght_themeid', $this->getVal('id'));
		$q->addValue('rght_theme', THEME_EVENT);
		$q->addValue('rght_status', $aRight);
		$q->addWhere('rght_theme='. THEME_EVENT);
		$q->addWhere('rght_themeid=' . $this->getVal('id'));
		$q->addWhere('rght_userid=' . $aUserId);
		$q->replaceRow();
		if ( $q->isError() )
		{
			$this->_error($q->getMsg());
		}
		unset($q);
	}

	/**
	 * Renvoi les droits d'un utilisateur
	 * @param integer  $aUserId   Identifiant de l'utilisateur
	 */
	public function getRight($aUserId)
	{
		$q = new Bn_query('rights');
		$q->setFields('rght_status');
		$q->setWhere('rght_theme='. THEME_EVENT);
		$q->addWhere('rght_themeid=' . $this->getVal('id', -1));
		$q->addWhere('rght_userid=' . $aUserId);
		$right = $q->getFirst();
		if ( $q->isError() )
		{
			$this->_error($q->getMsg());
		}
		unset($q);
		if ( empty($right) ) $right = OEVENT_RIGHT_VISITOR;
		return $right;
	}

	/**
	 * Supprimer des droits a un utilisateur
	 * @param integer  $aUserId   Identifiant de l'utilisateur
	 */
	public function deleteRight($aUserId)
	{
		$q = new Bn_query('rights');
		$q->addWhere('rght_theme='.THEME_EVENT);
		$q->addWhere('rght_themeid=' . $this->getVal('id', -1));
		$q->addWhere('rght_userid=' . $aUserId);
		$q->deleteRow();
		unset ($q);
	}

	/**
	 * Supprime le tournoi
	 * @return void
	 */
	public function deleteEvent()
	{
		$this->emptyEvent();
		$eventId = $this->getVal('id', -1);
		$q = new Bn_query('events');
		$q->deleteRow('evnt_id='. $eventId);
		$q->setTables('eventsextra');
		$q->deleteRow('evxt_eventid='.$eventId);
		$q->setTables('eventsmeta');
		$q->deleteRow('evmt_eventid='.$eventId);
		unset ($q);
	}

	/**
	 * Vide le tournoi
	 * @return void
	 */
	public function emptyEvent()
	{
		$eventId = $this->getVal('id', -1);
		$q = new Bn_query('postits');
		$q->deleteRow('psit_eventId='. $eventId);
		$q->setTables('news');
		$q->deleteRow('news_eventId='. $eventId);
		$q->setTables('news');
		$q->deleteRow('news_eventId='. $eventId);
		$q->setTables('database');
		$q->deleteRow('db_eventId='. $eventId);
		//$q->setTables('cnx');
		//$q->deleteRow('cnx_eventId='. $eventId);
		$q->setTables('eventsmeta');
		$q->deleteRow('evmt_eventId='. $eventId);
		$q->setTables('eventsextra');
		$q->deleteRow('evxt_eventId='. $eventId);
		$q->setTables('items');
		$q->deleteRow('item_eventId='. $eventId);
		$q->setTables('prefs');
		$q->deleteRow('pref_eventId='. $eventId);
		$q->setTables('subscriptions');
		$q->deleteRow('subs_eventId='. $eventId);
			
		// Suppression des equipes
		$q->setTables('teams');
		$q->setWhere('team_eventId=' . $eventId);
		$q->addField('team_id');
		$teamIds = $q->getRows();
		foreach($teamIds as $teamId)
		{
			$q->setTables('a2t');
			$q->deleteRow('a2t_teamId=' . $teamId);

			$q->setTables('t2t');
			$q->deleteRow('t2t_teamId=' . $teamId);

			$q->setTables('t2r');
			$q->deleteRow('t2r_teamId=' . $teamId);

		}
		$q->setTables('teams');
		$q->deleteRow('team_eventId='. $eventId);
			
		// Suppression des tableaux (series)
		$q->setTables('draws');
		$q->setWhere('draw_eventId=' . $eventId);
		$q->setFields('draw_id');
		$drawIds = $q->getCol();
		if ( !empty($drawIds) )
		{
			$q->deleteRow('draw_eventId='. $eventId);

			$q->setTables('rounds');
			$q->setFields('rund_id');
			$ids = implode(',', $drawIds);
			$q->setWhere("rund_drawId IN ($ids)");
			$roundIds = $q->getCol();
		}
			
		// Suppression des tours (poule ko)
		if ( !empty($roundIds) )
		{
			$ids = implode(',', $roundIds);

			$q->setTables('rounds');
			$q->deleteRow('rund_id IN ('. $ids . ')');

			$q->setTables('t2r');
			$q->deleteRow('t2r_roundid IN ('. $ids . ')');

			$q->setTables('ties');
			$q->setFields('tie_id');
			$q->setWhere('tie_roundid IN (' .  $ids . ')');
			$tieIds = $q->getCol();
		}
			
		// Suppression des rencontres et match
		if ( !empty($tieIds) )
		{
			$ids = implode(',', $tieIds);

			$q->setTables('ties');
			$q->deleteRow('ties_id IN ('. $ids . ')');

			$q->setTables('t2t');
			$q->deleteRow('t2t_tieid IN ('. $ids . ')');

			$q->setTables('matchs');
			$q->setFields('mtch_id');
			$q->setWhere('mtch_tieid IN (' .  $ids . ')');
			$matchIds = $q->getCol();
			foreach($matchIds as $matchId)
			{
				$q->setTables('p2m');
				$q->deleteRow('p2m_mtchid =' .$matchId);
					
				$q->setTables('matchs');
				$q->deleteRow('mtch_id ='. $matchId);
			}
		}
			
		// Suppression des comptes et commandes (achats)
		$q->setTables('accounts');
		$q->setFields('cunt_id');
		$q->setWhere('cunt_eventid='.$eventId);
		$accountIds = $q->getCol();
		$q->deleteRow();
		$q->setTables('commands');
		foreach ($accountIds as $accountId)
		{
			$q->deleteRow('cmd_accountid=' . $accountId);
		}
			
		// Suppression des inscrits
		$q->setTables('registration');
		$q->setFields('regi_id');
		$q->setWhere('regi_eventid='.$eventId);
		$regiIds = $q->getCol();
		$q->deleteRow();
		$q->setTables('commands');
			
		if ( count($regiIds) )
		{
			$ids = implode(',', $regiIds);
			$q->setTables('umpire');
			$q->deleteRow('umpi_regiId IN (' . $ids . ')');
			$q->setTables('ranks');
			$q->deleteRow('rank_regiId IN (' . $ids . ')');

			$q->setTables('i2p, pairs');
			$q->setFields('pair_id');
			$q->setWhere("i2p_regiId IN ($ids)");
			$q->addWhere('i2p_pairId = pair_id');
			$pairIds = $q->getCol();

			$q->setTables('i2p');
			$q->deleteRow("i2p_regiId IN ($ids)");
			$q->setTables('pairs');
			$ids = implode(',', $pairId);
			$q->deleteRow("pair_id IN ($ids)");
		}
		unset ($q);
		return;
	}

	/**
	 * Acces aux managers du tournoi
	 * @return array
	 */
	public function getManagers()
	{
		// Recuperer les administrateurs du tournoi
		$q = new Bn_query('rights, users');
		$q->setFields('user_id, user_login, user_name, user_pseudo, user_email');
		$q->addWhere('rght_themeid=' . $this->getVal('id', -1));
		$q->addWhere("rght_status='" . AUTH_MANAGER . "'");
		$q->addWhere('rght_theme=' . THEME_EVENT);
		$q->addWhere('rght_userid=user_id');
		$q->addWhere("user_email!=''");
		$users = $q->getRows();
		unset ($q);
		return $users;
	}

	/**
	 * Acces aux contact du tournoi (gestionnaire)
	 * @return array
	 */
	public function getContacts()
	{
		// Recuperer les administrateurs du tournoi
		$q = new Bn_query('rights, users');
		$q->setFields('user_email, user_id');
		$q->addWhere('rght_themeid=' . $this->getVal('id', -1));
		$q->addWhere("rght_status='" . AUTH_MANAGER . "'");
		$q->addWhere('rght_theme=' . THEME_EVENT);
		$q->addWhere('rght_userid=user_id');
		$q->addWhere("user_email!=''");
		$contacts = $q->getCol();
		unset ($q);
		return $contacts;
	}


	/**
	 * Ajoute un postit au tournoi
	 * @return array
	 */
	public function addPostit($aAssoId, $aAssoName)
	{
		// Recuperer les administrateurs du tournoi
		$q = new Bn_query('postits');
		$q->addValue('psit_title', 'Inscriptions');
		$q->addValue('psit_page', 'line');
		$q->addValue('psit_function', 'upload');
		$q->addValue('psit_action', 22);
		$q->addValue('psit_type', 384); //BD_POSTIT_LINE
		$q->addValue('psit_eventid', $this->getVal('id', -1));
		$q->addValue('psit_texte', $aAssoName);
		$q->addValue('psit_data', $aAssoId);
		$id = $q->addRow();
		unset ($q);
		return $id;
	}

	/**
	 * Acces aux donnees extra du tournoi
	 * @return array
	 */
	public function getExtra()
	{
		$q = new Bn_query('eventsextra');
		$q->setFields('*');
		$q->addWhere('evxt_eventid=' . $this->getVal('id'), -1);
		$extra = $q->getRow();
		if ( is_null($extra) )
		{
			$extra = array('evxt_eventid' => $this->getVal('id', -1),
				'evxt_deptid' => -1,
				'evxt_regionid' => -1,
				'evxt_serial' => '',
				'evxt_catage' => '',
				'evxt_disci' => '',
				'evxt_fedeid' => -1,
				'evxt_promoted' => NO,
				'evxt_livescoring' => NO,
				'evxt_liveupdate' => NO,
				'evxt_promoimg' => '',
				'evxt_email' => '',
				'evxt_delaycaptain' => 5
			);
		}
		unset ($q);
		return $extra;
	}

	/**
	 * Enregistre les donnes extra d'un tournoi
	 * @todo a suprimer utiliser objet Eventextra
	 * 	 */
	public function saveExtra()
	{
		$eventId = $this->getVal('id', -1);
		$where = 'evxt_eventid=' . $eventId;
		$values = $this->getValues();
		unset($values['id']);
		unset($values['cre']);
		unset($values['pbl']);
		unset($values['del']);
		unset($values['cmt']);
		$value['eventid'] = $this->getVal('id', -1);
		$id = $this->update('eventsextra', 'evxt_', $values, $where);

		// Restaurer l'id du tournoi
		$this->setVal('id', $eventId);

		return $id;
	}

	/**
	 * Acces aux donnees meta du tournoi
	 * @return array
	 */
	public function getMeta()
	{
		$q = new Bn_query('eventsmeta');
		$q->setFields('*');
		$q->addWhere('evmt_eventid=' . $this->getVal('id', -1));
		$meta = $q->getRow();
		if ( is_null($meta) )
		{
			$meta = array('evmt_eventid' => $this->getVal('id', -1),
				'evmt_logo' => '',
				'evmt_titlefont' => '',
				'evmt_titlesize' => '',
				'evmt_badgeid' => '',
				'evmt_skin' => '',
				'evmt_badgelogo' => '',
				'evmt_badgespon' => '',
				'evmt_urlstream' => '',
				'evmt_urllivescore' => ''
				);
		}
		unset ($q);
		return $meta;
	}

	/**
	 * Enregistre les donnes meta d'un tournoi
	 * @todo a suprimer utiliser objet Eventmeta
	 */
	public function saveMeta()
	{
		$eventId = $this->getVal('id', -1);
		$where = 'evmt_eventid=' . $eventId;
		$values = $this->getValues();
		unset($values['id']);
		unset($values['cre']);
		unset($values['pbl']);
		unset($values['del']);
		unset($values['cmt']);
		$id = $this->update('eventsmeta', 'evmt_', $values, $where);

		// Restaurer l'id du tournoi
		$this->setVal('id', $eventId);

		return $id;
	}

	/**
	 * Enregistre les donnes d'un tournoi
	 * @return int id du tournoi
	 */
	public function save()
	{
		$where = 'evnt_id=' . $this->getVal('id', -1);
		$id = $this->update('events', 'evnt_', $this->getValues(), $where);
		return $id;
	}
	
	/**
	 * Enregistre les donnes d'un tournoi
	 * @todo a supprimer, remplace par save
	 * @return int id du tournoi
	 */
	public function saveEvent()
	{
		$where = 'evnt_id=' . $this->getVal('id', -1);
		$id = $this->update('events', 'evnt_', $this->getValues(), $where);
		return $id;
	}

	/**
	 * Liste des classements du tournoi
	 * @return array
	 */
	public function getRanking()
	{
		$q = new Bn_query('rankdef,events');
		$q->setFields('rkdf_label, rkdf_id');
		$q->addWhere('rkdf_system=evnt_ranksystem');
		$q->addWhere('evnt_id=' . $this->getVal('id'));
		$q->setOrder('rkdf_point');
		$lov = $q->getLov();
		unset ($q);
		return $lov;
	}

	/**
	 * Liste des classements du tournoi pour combobox
	 * @return array
	 */
	public function getLovRanking()
	{
		$q = new Bn_query('rankdef,events');
		$q->setFields('rkdf_id, rkdf_label');
		$q->addWhere('rkdf_system=evnt_ranksystem');
		$q->addWhere('evnt_id='.$this->getVal('id'));
		$q->setOrder('rkdf_point');
		$lov = $q->getLov();
		unset ($q);
		return $lov;
	}

	/**
	 * Liste des sytemes de score
	 * @param boolean  $aObsolete  renvoie aussi les systeme obsolete
	 * @return array
	 */
	public function getLovScoringSystem()
	{
		$isSquash = Bn::getConfigValue('squash', 'params');
		$systems[OEVENT_SCORING_NONE] = constant('LABEL_' . OEVENT_SCORING_NONE);
		if ($isSquash)
		{
			for ($i=OEVENT_SCORING_1X5; $i<=OEVENT_SCORING_5X11; $i++)
			{
				$systems[$i] = constant('LABEL_' . $i);
			}
		}
		else
		{
			for ($i=OEVENT_SCORING_3X15; $i<=OEVENT_SCORING_3X21; $i++)
			{
				$systems[$i] = constant('LABEL_' . $i);
			}
		}
		return $systems;
	}

	/**
	 * Liste des sytemes de classement
	 * @param boolean  $aObsolete  renvoie aussi les systeme obsolete
	 * @return array
	 */
	public function getLovRankSystem($aObsolete = false)
	{
		$isSquash = Bn::getConfigValue('squash', 'params');
		if ($isSquash)
		{
			$systems[OEVENT_RANK_SQUASH] = constant('LABEL_' . OEVENT_RANK_SQUASH);
		}
		else
		{
			$first = $aObsolete ? OEVENT_RANK_FR1 : OEVENT_RANK_FR2;
			for ($i = $first; $i < OEVENT_RANK_SQUASH; $i++)
			{
				$systems[$i] = constant('LABEL_' . $i);
			}
		}
		return $systems;
	}

	/**
	 * Liste des sytemes de classement des equipes d'un interclub
	 * @param boolean  $aObsolete  renvoie aussi les systeme obsolete
	 * @return array
	 */
	
	public function getLovWeights()
	{

		$cible = realpath('../Conf/Teams');
		$files = scandir($cible);
		foreach($files as $file)
		{
			$fullName = $cible . '/' . $file;
			// Seul les fichiers sont traites
			if (filetype($fullName) != 'file') continue;
			$weights[$file] = basename($file, '.xml');
		}
		return $weights;
	}
	
	
	/**
	 * Liste de valeur des niveaux des tournois
	 * @param string  $aValue   Valeur pour un item additionnel
	 * @param string  $aText   Texte pour un item additionnel
	 * @return array
	 */
	public function getLovLevel($aValue = null, $aText = null)
	{
		if ( !is_null($aValue) && !is_null($aText) )
		{
			$levels[$aValue] = $aText;
		}
		$isSquash = Bn::getConfigValue('squash', 'params');
		for ($i=OEVENT_LEVEL_DEP; $i<=OEVENT_LEVEL_INTER_REG; $i++)
		{
			$levels[$i] = constant('LABEL_' . $i);
		}
		$levels[OEVENT_LEVEL_OTHER] = constant('LABEL_' . OEVENT_LEVEL_OTHER);
		return $levels;
	}

	/**
	 * Liste de valeur des natures de tournoi suivant le type
	 * @return array
	 */
	public function getLovNature($aType=null)
	{
		$isSquash = Bn::getConfigValue('squash', 'params');
		if ( is_null($aType) )
		{
			for ($i=OEVENT_NATURE_PRIVATE; $i<=OEVENT_NATURE_INTERCODEP; $i++) $levels[$i] = constant('LABEL_' . $i);
			$levels[OEVENT_NATURE_OTHER] = constant('LABEL_' . OEVENT_NATURE_OTHER);
		}
		else if ($aType == OEVENT_TYPE_INDIVIDUAL)
		{
			$levels[OEVENT_NATURE_PRIVATE] = constant('LABEL_' . OEVENT_NATURE_PRIVATE);
			$levels[OEVENT_NATURE_CHAMPIONSHIP] = constant('LABEL_' . OEVENT_NATURE_CHAMPIONSHIP);
			$levels[OEVENT_NATURE_TROPHEE] = constant('LABEL_' . OEVENT_NATURE_TROPHEE);
			$levels[OEVENT_NATURE_OTHER] = constant('LABEL_' . OEVENT_NATURE_OTHER);
		}
		else
		{
			$levels[OEVENT_NATURE_IC] = constant('LABEL_' . OEVENT_NATURE_IC);
			$levels[OEVENT_NATURE_INTERCODEP] = constant('LABEL_' . OEVENT_NATURE_INTERCODEP);
			$levels[OEVENT_NATURE_OTHER] = constant('LABEL_' . OEVENT_NATURE_OTHER);
		}
		return $levels;
	}

	/**
	 * Liste de valeur des types de tournoi
	 * @return array
	 */
	public function getLovType()
	{
		$natures[OEVENT_TYPE_INDIVIDUAL] = 'Individuel';
		$natures[OEVENT_TYPE_TEAM] = 'Par équipe';
		return $natures;
	}

	/**
	 * Liste des types de droits sur les tournois
	 * @return array
	 */
	public function getLovRight()
	{
		$rights[OEVENT_RIGHT_MANAGER] = 'Gestionnaire';
		$rights[OEVENT_RIGHT_ASSISTANT] = 'Assistant';
		$rights[OEVENT_RIGHT_FRIEND] = 'Ami';
		$rights[OEVENT_RIGHT_GUEST] = 'Invité';
		$rights[OEVENT_RIGHT_VISITOR] = 'Visiteur';
		//$rights[OEVENT_RIGHT_REFEREE] = 'Juge arbitre';
		$rights[OEVENT_RIGHT_CAPTAIN] = 'Capitaine';
		//$rights[OEVENT_RIGHT_ORGANIZER] = 'Organisateur';
		return $rights;
	}
	
	
	/**
	 * Creation d'un tournoi a partir des donnees issue de Poona
	 * On suppose que l'objet a deja ete initialise a partir des donnees de poona
	 *
	 */
	public function createFromPoona()
	{

		// Enregistrement du tournoi
		$this->setVal('ownerid', -1);
		$this->setVal('status', OEVENT_STATUS_ASK);
		$this->setVal('pbl', DATA_CONFIDENT);
		$this->saveEvent();

		// Donnees extra
		$this->setVal('eventid', $this->getVal('id', -1));
		$this->saveExtra();

		//Creation des tableaux
		$this->createDraws();

		return $this->getVal('id');
	}

	/**
	 * Creation des tableaux en fonction des serie, discipline et categorie d'age du tournoi
	 *
	 */
	public function createDraws()
	{
		$locale = BN::getLocale();
		require_once 'Locale/' . $locale . '/Omatch.inc';
		require_once 'Locale/' . $locale . '/Oplayer.inc';

		$q = new Bn_query('rankdef');
		$q->addField('rkdf_id');
		$q->addWhere("rkdf_label='--'");
		$q->addWhere('rkdf_system=' . OEVENT_RANK_FR2);
		$rankdefId = $q->getFirst();
		$oDraw = new Odraw();
		$data = array( 'eventid'=> $this->getVal('id'),
		 'numcatage'=> 0,
		 'type'=>OMATCH_DRAW_KO);
		$serials = explode(',', $this->getVal('serial'));
		$discis  = explode(',', $this->getVal('disci'));
		$catages = explode(',', $this->getVal('catage'));
		// Tableau seniors
		if ( ($key = array_search(OPLAYER_CATAGE_SEN, $catages)) !== false )
		{
			unset ($catages[$key]);
			foreach($serials as $serial)
			{
				$data['serial'] = 'Série ' . $serial;
				$data['catage'] = OPLAYER_CATAGE_SEN;
				$data['rankdefid'] = $rankdefId;
				foreach($discis as $disci)
				{
					$data['disci'] = $disci;
					if ( ($disci <= OMATCH_DISCI_WS) OR ($disci==OMATCH_DISCI_AS) ) $data['discipline'] = OMATCH_DISCIPLINE_SINGLE;
					else if ($disci == OMATCH_DISCI_XD) $data['discipline'] = OMATCH_DISCIPLINE_MIXED;
					else $data['discipline'] = OMATCH_DISCIPLINE_DOUBLE;
					$data['name'] = constant('LABEL_'.$disci) . ' ' . $serial;
					$data['stamp'] = constant('SMA_LABEL_'.$disci) . ' ' . $serial;
					$oDraw->newDraw($data);
				}
			}
		}
		// Autres tableaux
		foreach( $catages as $catage )
		{
			$data['serial'] = constant('LABEL_'.$catage);
			$data['catage'] = $catage;
			$data['rankdefid'] = $rankdefId;
			foreach($discis as $disci)
			{
				$data['disci'] = $disci;
				if ($disci < OMATCH_DISCI_WS) $data['discipline'] = OMATCH_DISCIPLINE_SINGLE;
				else if ($disci == OMATCH_DISCI_XD) $data['discipline'] = OMATCH_DISCIPLINE_MIXED;
				else $data['discipline'] = OMATCH_DISCIPLINE_DOUBLE;
				$data['name'] = constant('LABEL_'.$disci) . ' ' . constant('LABEL_'.$catage);
				$data['stamp'] = constant('SMA_LABEL_'.$disci) . ' ' . constant('SMA_LABEL_'.$catage);
				$oDraw->newDraw($data);
			}
		}
	}

	/**
	 * Retourne les tarifs d'inscription du tournoi
	 * @return array
	 */
	public function getFees()
	{
		// Tarifs du tournoi
		$fees = array( 'IS' => '0.00',
		 'ID' => '0.00',
		 'IM' => '0.00',
		 'I0' => '0.00',
		 'I1' => '0.00',
		 'I2' => '0.00',
		 'I3' => '0.00');
		$q = new Bn_query('items');
		$q->addField('item_id');
		$q->addField('item_value');
		$q->addField('item_code');
		$q->addField('item_name');
		$q->addWhere('item_eventid=' . $this->getVal('id', -1));
		$q->addWhere('item_rubrikid=-2');
		$res = $q->getRows();
		foreach( $res as $item)
		{
			$fees[$item['item_code']] = sprintf('%.02f', $item['item_value']);
		}
		unset ($q);
		return $fees;
	}

	/**
	 * Renvoie les inscriptions d'une equipe
	 *
	 * @param int $assoId  identifiant de l'association
	 * @return unknown
	 */
	public function getEntries($assoId)
	{
		// Recuperer les id joueurs
		$q = new Bn_query('a2t, teams, registration');
		$q->setFields('regi_id');
		$q->addWhere('a2t_teamId = team_id');
		$q->addWhere('team_id=regi_teamid');
		$q->addWhere('team_eventId = ' . $this->getVal('id', -1));
		$q->addWhere('a2t_assoid =' . $assoId);
		$regis = $q->getRows();
		return $regis;
	}

	/**
	 * Enregistre les tarifs d'inscription du tournoi
	 * @return array
	 */
	public function saveFees($aFees)
	{
		$eventId = $this->getVal('id', -1);
		$data['rubrikid'] = -2;
		$data['eventid'] = $eventId;

		$data['value'] =  $aFees['IS'];
		$data['name'] ='Inscription simple';
		$data['code'] = 'IS';
		$where = 'item_eventid=' . $eventId;
		$where .= " AND item_code='IS'";
		$id = $this->update('items', 'item_', $data, $where);
		$data['uniid'] = Bn::getUniId($id);
		$id = $this->update('items', 'item_', $data, $where);

		$data['value'] =  $aFees['ID'];
		$data['name'] ='Inscription double';
		$data['code'] = 'ID';
		$where = 'item_eventid=' . $eventId;
		$where .= " AND item_code='ID'";
		$id = $this->update('items', 'item_', $data, $where);
		$data['uniid'] = Bn::getUniId($id);
		$id = $this->update('items', 'item_', $data, $where);

		$data['value'] =  $aFees['IM'];
		$data['name'] ='Inscription mixte';
		$data['code'] = 'IM';
		$where = 'item_eventid=' . $eventId;
		$where .= " AND item_code='IM'";
		$id = $this->update('items', 'item_', $data, $where);
		$data['uniid'] = Bn::getUniId($id);
		$id = $this->update('items', 'item_', $data, $where);

		$data['value'] =  $aFees['I1'];
		$data['name'] ='Inscription 1 tableaux';
		$data['code'] = 'I1';
		$where = 'item_eventid=' . $eventId;
		$where .= " AND item_code='I1'";
		$id = $this->update('items', 'item_', $data, $where);
		$data['uniid'] = Bn::getUniId($id);
		$id = $this->update('items', 'item_', $data, $where, false);

		$data['value'] =  $aFees['I2'];
		$data['name'] ='Inscription 2 tableaux';
		$data['code'] = 'I2';
		$where = 'item_eventid=' . $eventId;
		$where .= " AND item_code='I2'";
		$id = $this->update('items', 'item_', $data, $where);
		$data['uniid'] = Bn::getUniId($id);
		$id = $this->update('items', 'item_', $data, $where, false);

		$data['value'] =  $aFees['I3'];
		$data['name'] ='Inscription 3 tableaux';
		$data['code'] = 'I3';
		$where = 'item_eventid=' . $eventId;
		$where .= " AND item_code='I3'";
		$id = $this->update('items', 'item_', $data, $where);
		$data['uniid'] = Bn::getUniId($id);
		$id = $this->update('items', 'item_', $data, $where);

		// Restaurer l'id du tournoi
		$this->setVal('id', $eventId);
	}

	/**
	 * Renvoi les tableaux autorises pour un joueur en fonction de
	 * son classement et de sa categorie d'age
	 *
	 * @param Oonline $aPlayer
	 */
	public function getAuthDraws(Oonline $aPlayer)
	{

		$qs = new Bn_query('rankdef');
		$qs->setFields('rkdf_point');

		// Tableaux du tournoi
		$q = new Bn_query('draws, rankdef');
		$q->addField('draw_id');
		$q->addField('draw_stamp');
		$q->addField('draw_catage');
		$q->addField('draw_numcatage');
		$q->addField('rkdf_point');
		$q->setOrder('rkdf_point DESC');

		// Tableaux de simple
		$gender = $aPlayer->getVal('gender');
		$disci = ($gender == OMEMBER_GENDER_MALE) ? OMATCH_DISCI_MS : OMATCH_DISCI_WS;
		$q->setWhere('draw_eventid=' . $this->getVal('id', -1));
		$q->addWhere('draw_rankdefid=rkdf_id');
		$q->addWhere('draw_disci=' . $disci);
		$draws = $q->getRows();

		$catage    = $aPlayer->getValue('catage');
		$numcatage = $aPlayer->getValue('numcatage', 1);
		$surclasse = $aPlayer->getValue('surclasse');
		$qs->setWhere('rkdf_id=' . $aPlayer->getValue('singlelevelid'));
		$seuil = $qs->getFirst();
		$options = array();
		foreach($draws as $draw)
		{
			$select = array('text'  => $draw['draw_stamp'],
							'value' => $draw['draw_id']);
			if ($draw['rkdf_point'] && $draw['rkdf_point'] < $seuil) $select['disabled']='disabled';
			$options[] = $select;
		}
		$authDraws[OMATCH_DISCIPLINE_SINGLE]  = $options;

		// Tableaux de double
		$disci = ($gender == OMEMBER_GENDER_MALE) ? OMATCH_DISCI_MD : OMATCH_DISCI_WD;
		$q->setWhere('draw_eventid=' . $this->getVal('id'));
		$q->addWhere('draw_rankdefid=rkdf_id');
		$q->addWhere('draw_disci=' . $disci);
		$draws = $q->getRows();
		$qs->setWhere('rkdf_id=' . $aPlayer->getValue('doublelevelid'));
		$seuil = $qs->getFirst();

		$options = array();
		foreach($draws as $draw)
		{
			$select = array('text'  => $draw['draw_stamp'],
								'value' => $draw['draw_id']);
			if ($draw['rkdf_point'] && $draw['rkdf_point'] < $seuil
			)
			$select['disabled']='disabled';
			$options[] = $select;
		}
		$authDraws[OMATCH_DISCIPLINE_DOUBLE]  = $options;

		// Tableaux de mixte
		$disci = OMATCH_DISCI_XD;
		$q->setWhere('draw_eventid=' . $this->getVal('id'));
		$q->addWhere('draw_rankdefid=rkdf_id');
		$q->addWhere('draw_disci=' . $disci);
		$draws = $q->getRows();
		$qs->setWhere('rkdf_id=' . $aPlayer->getValue('mixedlevelid'));
		$seuil = $qs->getFirst();
		$options = array();
		foreach($draws as $draw)
		{
			$select = array('text'  => $draw['draw_stamp'],
								'value' => $draw['draw_id']);
			if ($draw['rkdf_point'] && $draw['rkdf_point'] < $seuil
			)
			$select['disabled']='disabled';
			$options[] = $select;
		}
		$authDraws[OMATCH_DISCIPLINE_MIXED]  = $options;
		return $authDraws;
	}

	/**
	 * Retourne les tableaux du tournoi
	 * @return array
	 */
	public function getDraws($aDisci=null, $aLimit=null)
	{
		// Tableaux du tournoi
		$q = new Bn_query('draws, rankdef');
		$q->addField('draw_id');
		$q->addField('draw_stamp');
		$q->addField('draw_catage');
		$q->addField('draw_numcatage');
		$q->addField('rkdf_point');
		$q->addWhere('draw_eventid=' . $this->getVal('id', -1));
		$q->addWhere('draw_rankdefid=rkdf_id');
		if ( !is_null($aDisci) ) $q->addWhere('draw_disci=' . $aDisci);
		$res = $q->getRows();

		if ( !empty($aLimit) && $aLimit > 0 )
		{
			$q2 = new Bn_query('rankdef');
			$q2->setFields('rkdf_point');
			$q2->addWhere('rkdf_id='.$aLimit);
			$nbPointPlayer = $q2->getFirst();
			unset($q2);
			$select = array('text'  => 'Non',
							'value' => -1);
			$draws[] = $select;
			foreach($res as $draw)
			{
				$select = array('text'  => $draw['draw_stamp'],
								'value' => $draw['draw_id']);
				if ($draw['rkdf_point'] < $nbPointPlayer) $select['disabled']='disabled';
				$draws[] = $select;
			}
		}
		else
		{
			$draws[-1] = 'Non';
			foreach($res as $draw)
			{
				$draws[$draw['draw_id']] = $draw['draw_stamp'];
			}
		}
		unset($q);

		return $draws;
	}

	/**
	 * Acces aux officels de terrain du tournoi
	 * @return array
	 */
	public function getReferee()
	{
		if ( empty($this->_referee) )
		{
			$q = new Bn_query('registration');
			$q->setFields('regi_id, regi_type,  regi_longname');
			$q->addWhere('regi_eventid=' . $this->getVal('id'));
			$q->addWhere('regi_type=' . OEVENT_OFFICIAL_REFEREE);
			$this->_referee = $q->getRow();
			if ($q->isError() )
			return $q->getMsg();
		}
		unset ($q);
		return $this->_referee;
	}

	/**
	 * Acces aux officels de terrain du tournoi
	 * @return array
	 */
	public function getDeputy()
	{
		if ( empty($this->_deputy) )
		{
			$q = new Bn_query('registration');
			$q->setFields('regi_id, regi_type,  regi_longname');
			$q->addWhere('regi_eventid=' . $this->getVal('id'));
			$q->addWhere('regi_type=' . OEVENT_OFFICIAL_DEPUTY);
			$this->_deputy = $q->getRows();
			if ($q->isError() )
			return $q->getMsg();
		}
		unset ($q);
		return $this->_deputy;
	}

	/**
	 * Affichage des informations d'inscription
	 *
	 * @param BN_Balise  $aBalise     Balise de destination
	 * 	 */
	public function displayRegInfos(BN_balise $aBalise)
	{
		$fees = $this->getFees();
		$div1 = $aBalise->addDiv('', 'bnEventReg');
		if ($fees['IS'] > 0) $div1->addInfo('txtFeeS', LOC_LIB_FEESS, $fees['IS']);
		if ($fees['ID'] > 0) $div1->addInfo('txtFeeD', LOC_LIB_FEESD, $fees['ID']);
		if ($fees['IM'] > 0) $div1->addInfo('txtFeeM', LOC_LIB_FEESM, $fees['IM']);
		if ($fees['I1'] > 0) $div1->addInfo('txtFee1', LOC_LIB_FEES1, $fees['I1']);
		if ($fees['I2'] > 0) $div1->addInfo('txtFee2', LOC_LIB_FEES2, $fees['I2']);
		if ($fees['I3'] > 0) $div1->addInfo('txtFee3', LOC_LIB_FEES3, $fees['I3']);
		$sum = $fees['IS'] + $fees['ID'] + $fees['IM'] + $fees['I1'] + $fees['I2'] + $fees['I3'];
		if ( $sum == 0) $div1->addInfo('', LOC_LIB_FEES, LOC_LIB_NOFEES);

		$div1->addInfo('nb', LOC_LIB_NB_DRAW, $this->getVal('nbdrawmax'));
		$div1->addP('cmt', $this->getVal('cmt'), 'bn-p-info');
	}

	/**
	 * Affichage du cartouche de presentation du tournoi
	 *
	 * @param BN_Balise  $aBalise     Balise de destination
	 * @param boolean $aLinks  Affichage ou non des liens
	 * 	 */
	public function display(BN_balise $aBalise, $aLinks = true)
	{
		require_once 'Badnetres/Events/Events.inc';
		require_once 'Badnetres/Live/Live.inc';
		require_once 'Badnetplay/Inline/Inline.inc';

		$div = $aBalise->addDiv(null, 'bnEvent');

		$event = $this->getValues();

		//------------- Liens
		$d = $div->addBalise('ul');
		$d->setAttribute('class', 'classLinks');
		// Site organisateur tournoi
		if ( !empty($event['url']) )
		{
			$li = $d->addBalise('li');
			$sp = $li->addLink('', $event['url'], 'www');
			$sp->setAttribute('class', 'lnk on');
		}
		else
		{
			$sp = $d->addBalise('li', '', 'www');
			$sp->setAttribute('class', 'off');
		}

		// Tournoi badnet
		if ($event['id'] > 0 && $event['pbl'] == DATA_PUBLIC)
		{
			$li = $d->addBalise('li');
			$sp = $li->addLink('', BNETRES_PAGE_EVENT . '&eventId='. $event['id'], 'BadNet');
			$sp->setAttribute('class', 'lnk on');
		}
		else
		{
			$sp = $d->addBalise('li', '' , 'BadNet');
			$sp->setAttribute('class', 'off');
		}

		// Inscription en ligne
		$date = Bn::date($event['deadline'], 'U') + 24*3600;
		if ($event['liveentries'] == YES && $aLinks && $date >= date('U'))
		{
			$li = $d->addBalise('li');
			$poonaId = empty($event['fedeid']) ? -1 : $event['fedeid'];
			if ( $event['id'] > 0 )	$action = INLINE_SELECT_EVENT .'&eventId=' . $event['id'];
			else $action = INLINE_SELECT_EVENT .'&poonaEventId=' . $poonaId . "&season=" . $event['season'];
			$sp = $li->addLink('', $action, 'Inscription', 'targetBody');
			$sp->completeAttribute('class', 'lnk on');
		}
		else
		{
			$sp = $d->addBalise('li', '', 'Inscription');
			$sp->setAttribute('class', 'off');
		}

		// Courrier
		if ( !empty($event['email']) )
		{
			$li = $d->addBalise('li');
			$poonaId = empty($event['fedeid']) ? -1 : $event['fedeid'];
			$action = BNETRES_FILL_EMAIL .'&poonaId=' . $poonaId . '&eventId=' . $event['id'];
			$sp = $li->addLink('', $action, '@', 'targetDlg');
			$sp->completeAttribute('class', 'lnk bn-dlg on');
		}
		else
		{
			$sp = $d->addBalise('li', '', '@');
			$sp->setAttribute('class', 'off');
		}

		// Lien vers le live scoring
		if ( $event['livescoring'] == YES)
		{
			$li = $d->addBalise('li');
			//$sp = $li->addLink('', LIVE_PAGE_SCORING . '&eventId='. $event['evnt_id'], 'Live', 'targetBody');
			$sp = $li->addLink('', BNETRES_PAGE_LIVE . '&eventId='. $event['id'], 'Live');
			$sp->completeAttribute('class', 'lnk lnkLive on');
		}
		// Nom du tournoi
		$d = $div->addDiv('', 'divName');
		$name =	$event['name'];
		$d->addP('', $name, 'className');
		$d->addBreak();

		//---------- Deuxieme ligne
		$d = $div->addDiv('', 'classLigne');
		// Date du tournoi
		$p = $d->addP('', 'Du ', 'pWe');
		$p->addBalise('span', '', Bn::strdate($event['firstday'], '%d %b %y'));
		$p->addBalise('span', '', ' au ');
		$p->addBalise('span', '', Bn::strdate($event['lastday'], '%d %b %y'));

		// Limite d'inscription
		$p = $d->addP('', "Limite d'inscription : ", 'pLimit');
		$p->addBalise('span', '', Bn::strdate($event['deadline'], '%d %b'));

		// Lieu
		$p = $d->addP('', "Lieu : ", 'pPlace');
		$p->addBalise('span', '', $event['place']);
		$d->addBreak();

		$locale = BN::getLocale();
		require_once "Object/Locale/$locale/Oplayer.inc";

		//---------- Troisieme ligne
		$d = $div->addDiv('', 'classLigne2');
		// Categorie
		$p = $d->addBalise('ul');
		$catages = preg_split("/[,; ]+/", $event['catage']);
		for ($i=OPLAYER_CATAGE_POU; $i <= OPLAYER_CATAGE_VET; $i++)
		{
			$sp = $p->addBalise('li', '', constant('LABEL_'.$i));
			if ( in_array($i, $catages) )
			$sp->setAttribute('class', 'catage on');
			else
			$sp->setAttribute('class', 'off');
		}
		// Discipline
		$p = $d->addBalise('ul');
		$p->setAttribute('class', 'classDisci');
		for ($i=OPLAYER_DISCIPLINE_MS; $i <= OPLAYER_DISCIPLINE_MX; $i++)
		{
			$sp = $p->addBalise('li', '', constant('SMA_LABEL_'.$i));
			if ( strstr($event['disci'], "$i") === false )	$sp->setAttribute('class', 'off');
			else $sp->setAttribute('class', 'disci on');
		}

		// Serie
		$def = array('Elite', 'A', 'B', 'C', 'D', 'NC');
		$p = $d->addBalise('ul');
		$serials = preg_split("/[,; ]+/", $event['serial']);
		foreach ($def as $serial)
		{
			$sp = $p->addBalise('li', '', $serial);
			if ( in_array($serial, $serials) )
			$sp->setAttribute('class', 'serial on');
			else
			$sp->setAttribute('class', 'off');
		}

		// Bandeau image
		if (isset($event['promoimg']) )
		{
			$dt = $d->addDiv('', 'divImg');
			if ( !empty($event['url']) ) $dt = $dt->addLink('', $event['url']);
			$dt->addImage('', $event['promoimg'], '', array('width'=>595));
		}

		$d->addBreak();
	}

}
?>
