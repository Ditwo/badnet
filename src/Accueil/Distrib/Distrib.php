<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Distrib.inc';
require_once 'Badnetadm/Login/Login.inc';
require_once 'Badnet/Event/Event.inc';

class Distrib
{
	public function __construct()
	{
		$controller = Bn::getController();
		$controller->addAction(ACCUEIL_DISTRIB,      $this, 'pageAccueil');
	}


	/**
	 * Affichage du contenu de la colonne de droite
	 *
	 * @return false
	 */
	public function displayColright()
	{
		require_once 'Object/Oevent.inc';
		// Container principal
		$container = new Body();

	}

	/**
	 * Affichage de la page d'accueil du site
	 *
	 * @return false
	 */
	public function pageAccueil()
	{
	
		// Connnexion automatique
		$user = Bn::getConfigValue('user', 'autolog');
		$pwd = Bn::getConfigValue('pwd', 'autolog');
		if (!empty($user))
		{
			$_POST['txtLogin'] = $user;
			$_POST['txtPwd']  = $pwd;
			return BNETADM_LOG;
		}
	
		require_once 'Object/Opublic.inc';
		require_once 'Object/Oevent.inc';
		$body = new Body();
		
		$divLeft = $body->addDiv('divLeft');
		// Tournois a l'affiche
		$q = new Bn_query('events');
		$q->setTables('events, eventsextra');
		$q->addField('evnt_id');
		$q->addWhere('evnt_id=evxt_eventid');
		$q->addWhere('evxt_promoted=' . YES);
		$q->setOrder('evnt_firstday ASC');
		$events = $q->getCol();
		// Si aucun tournoi a l'affiche, en choisir 1 au hasard
		if ( ! count($events))
		{
			$q->setWhere('evnt_season='. Oseason::getCurrent());
			$q->addWhere('evnt_pbl='. DATA_PUBLIC);
			$q->addWhere("evnt_lastday >'" . date('Y-m-d') . "'");
			$res = $q->getCol();
			$num = rand(0, count($res));
			$events[] = $res[$num];
		}
		/*
		foreach($events as $eventId)
		{
			$oevent = new Oevent($eventId);
			$oevent->display($divLeft);
			unset ($oevent);
		}
		*/
		$q->setTables('events');
		$q->setFields('evnt_id, evnt_date, evnt_name, evnt_nbvisited, evnt_level, evnt_nature, evnt_dpt, evnt_place, evnt_deadline');

		//Tournois individuel
		$div = $divLeft->addDiv('divIndividual');
		$t = $div->addP('', '', 'bn-title-3');
		$t->addBalise('span', '',  'Tournois individuels');
		
		// Prochains tournois
		$div2 = $div->addDiv('divNextTournament');
		$div2->addP('', 'Prochains tournois','bn-title-4');
		$q->setWhere('evnt_type=' . OEVENT_TYPE_INDIVIDUAL);
		$q->addWhere("evnt_firstday >='" .date('Y-m-d') . "'");
		$q->addWhere('evnt_pbl=' . OPUBLIC_DATA_PUBLIC);
		$q->setOrder('evnt_firstday');
		$events = $q->getRows();
		$ul = $div2->addBalise('ul');
		$ul->setAttribute('class', 'bn-list-1');
		foreach($events as $event)
		{
			$eventId = $event['evnt_id'];
			$li = $ul->addBalise('li');
			$lnk = $li->addLink('lnk'.$eventId, BEVENT_GOTO_VISIT . '&eventId='. $event['evnt_id'], $event['evnt_name']);
			//$lnk->setAttribute('href', $event['evnt_date']);
			$str = $event['evnt_name'] . ' - ';
			$str .=  'Date :'. $event['evnt_date'] . "\n";
			$str .=  'Lieu :'. $event['evnt_place'] . "\n";
			$str .=  'Inscrition :'. Bn::date($event['evnt_deadline'], 'd-m-Y') . "\n";
			$lnk->setTooltip($str);
		}


		$div2 = $div->addDiv('divLastResults');
		$div2->addP('', 'Derniers résultats', 'bn-title-4');

		$q->setWhere('evnt_type='.OEVENT_TYPE_INDIVIDUAL);
		$q->addWhere("evnt_firstday < '" . date('Y-m-d') ."'");
		$q->addWhere('evnt_pbl=' . OPUBLIC_DATA_PUBLIC);
		$q->setOrder('evnt_firstday DESC');
		$q->setLimit(1, 15);
		$events = $q->getRows();

		// Derniers resultats
		$ul = $div2->addBalise('ul');
		$ul->setAttribute('class', 'bn-list-1');
		foreach($events as $event)
		{
			$eventId = $event['evnt_id'];
			$li = $ul->addBalise('li');
			$lnk = $li->addLink('lnk'.$eventId, BEVENT_GOTO_VISIT . '&eventId='. $eventId, $event['evnt_name']);
			$str = $event['evnt_name'] . ' - ';
			$str .=  'Date :'. $event['evnt_date'] . "\n";
			$str .=  'Lieu :'. $event['evnt_place'] . "\n";
			$str .=  'Visite :'. $event['evnt_nbvisited'] . "\n";
			$lnk->setTooltip($str);
		}

		//Tournoi par equipes
		$div = $divLeft->addDiv('divInterclub');
		$t = $div->addP('', '', 'bn-title-3');
		$t->addBalise('span', '',  'Interclubs');

		$q->setWhere('evnt_type='. OEVENT_TYPE_TEAM);
		$q->addWhere('evnt_season='.Oseason::getCurrent());
		$q->setLimit(1, 150);
		$q->setorder('evnt_dpt');
		$events = $q->getRows();

		$div->addP('', 'Intercodep','bn-title-4');
		$ulc = $div->addBalise('ul');
		$ulc->setAttribute('class', 'bn-list-1');

		$div->addP('', 'National','bn-title-4');
		$uln = $div->addBalise('ul');
		$uln->setAttribute('class', 'bn-list-1');

		$div->addP('', 'Régionnal','bn-title-4');
		$ulr = $div->addBalise('ul');
		$ulr->setAttribute('class', 'bn-list-1');

		$div->addP('', 'Départemental','bn-title-4');
		$uld = $div->addBalise('ul');
		$uld->setAttribute('class', 'bn-list-1');
		foreach($events as $event)
		{
			$eventId = $event['evnt_id'];
			if ($event['evnt_nature'] == OEVENT_NATURE_INTERCODEP)
			{
				$li = $ulc->addBalise('li', 'eventsLinks');
				$lnk = $li->addLink('lnk'.$eventId, BEVENT_GOTO_VISIT . '&eventId='. $eventId, $event['evnt_name']);
			}
			elseif ($event['evnt_level'] == OEVENT_LEVEL_DEP)
			{
				$li = $uld->addBalise('li', 'eventsLinks');
				$lnk = $li->addLink('lnk'.$eventId, BEVENT_GOTO_VISIT . '&eventId='. $eventId, '[' . $event['evnt_dpt']. '] ' . $event['evnt_name']);
			}
			else if ($event['evnt_level'] == OEVENT_LEVEL_REG)
			{
				$li = $ulr->addBalise('li', 'eventsLinks');
				$lnk = $li->addLink('lnk'.$eventId, BEVENT_GOTO_VISIT . '&eventId='. $eventId, $event['evnt_name']);
			}
			else
			{
				$li = $uln->addBalise('li', 'eventsLinks');
				$lnk = $li->addLink('lnk'.$eventId, BEVENT_GOTO_VISIT . '&eventId='. $eventId, $event['evnt_name']);
			}
			$str = $event['evnt_name'] . ' - ';
			$str .=  'Visite :'. $event['evnt_nbvisited'] . "\n";
			$lnk->setTooltip($str);
		}
		
		// Coonne de droite
		$divRight = $body->addDiv('divRight');
		
		// Pour le calendrier
		$d = $divRight->addDiv('divCalendar');
		$d->addDiv('calendar');
		$d->addBreak();

		// Connexion au site
		$divf = $divRight->addDiv('divConnect');
		$t = $divf->addP('', '', 'bn-title-3');
		$t->addBalise('span', '',  'Connexion');

		$userId = Bn::getValue('user_id', null);
		if ( empty($userId) )
		{
				
			$form = $divf->addForm('frmLogin', BNETADM_LOG, 'targetBody');

			$form->addEdit('txtLogin',  'Identifiant', null, 20);
			$form->addEditPwd('txtPwd', 'Mot de passe', 20);
			$block = $form->addDiv('', 'blkBtnLogin');
			$block->addButtonValid('btnLogin', "S'identifier");
			/*
			$block = $form->addDiv('', 'divLinks');
			$block->addP()->addLink('lnkAccount', LOGIN_PAGE_ACCOUNT, "Nouveau compte", 'targetBody');
			$lnk = $block->addP()->addLink('lnkPwd', LOGIN_FILL_PASSWORD, 'Mot de passe oublié', 'targetDlg');
			$lnk->completeAttribute('class', 'bn-dlg');
			include_once 'Badnetadm/Locale/' . Bn::getLocale() . '/Login.inc';
			$lnk->addMetaData('title', "'Nouveau mot de passe'");
			$lnk->addMetaData('width', 355);
			$lnk->addMetaData('height', 180);
			$form->addBreak();
			*/
		}
		else
		{
			$p = $divf->addP('pDirect', LOC_P_DIRECT_ACCESS, 'bn-p-info');
			$p->insertcontent(Bn::getValue('user_name') . ', ');
			$block = $divf->addDiv('', 'divLinks');
			$action = BEVENT_PAGE_EVENTS; 
			$block->addP()->addLink('lnkDirect', $action , 'Accés direct', 'targetBody');
		}
		$divRight->addBreak();

		$body->addBreak();

		$body->addJQReady('pageAccueil();');
		$body->display();
		return false;
	}

}
?>
