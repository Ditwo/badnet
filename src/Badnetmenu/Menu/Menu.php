<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

class Menu
{
	// {{{ properties
	// }}}

	/**
	 * Start the connexion processus
	 *
	 */

	public function __construct()
	{
		$controller = Bn::getController();
		$controller->addAction(MENU_VISITOR,    $this, 'menuVisitor');
		$controller->addAction(MENU_ADM,        $this, 'menuAdm');
		$controller->addAction(MENU_USER,       $this, 'menuUser');
		$controller->addAction(MENU_PLAYER,     $this, 'menuPlayer');
		$controller->addAction(MENU_ASSOC,      $this, 'menuAssoc');
		$controller->addAction(MENU_UMPIRE,     $this, 'menuUmpire');
		$controller->addAction(MENU_BADNET,     $this, 'menuBadnet');
		$controller->addAction(MENU_CAPTAIN,    $this, 'menuCaptain');
		$controller->addAction(MENU_TEAM,       $this, 'menuTeam');
		
		$controller->addAction(MENU_FILL_EMAIL, $this, 'fillEmail');
		$controller->addAction(MENU_SEND_EMAIL, $this, 'sendEmail');
		$controller->addAction(MENU_EMAIL_SENDED, $this, 'emailSended');
	}

	/**
	 * Menu pour la gestion du tournoi
	 */
	public function menuBadnet()
	{
		require_once "Badnetadm/Badnetadm.inc";
		require_once "Badnet/Badnet.inc";
		$menu = new Body();
		$texts = array(LOC_ITEM_MY_ACCOUNT,
		LOC_ITEM_EVENT,
		LOC_ITEM_TRANSFERT,
		//LOC_ITEM_PUBLICATION
		);
		$actions = array(BNETADM_DISPATCH,
		BADNET_EVENT,
		BADNET_TRANSFERT,
		//BADNET_PUBLICATION
		);

		if ( Oaccount::isLoginAdmin() )
		{
			$texts[] = LOC_ITEM_ADMINISTRATION;
			$actions[] = BADNET_ADM;
		}
		
		$lst = $menu->addList('lstMenuBadnet', $texts, $actions, Bn::getValue('item',0), 'targetBody');
		$lst->completeAttribute('class', 'badnetMenu');
		$menu->display();
		return false;
	}
	
	/**
	 * Menu d'un capitaine pour la gestion du tournois
	 */
	public function menuCaptain()
	{
		require_once "Badnetadm/Badnetadm.inc";
		require_once "Badnet/Event/Event.inc";
		$menu = new Body();
		$texts = array(LOC_ITEM_MY_ACCOUNT,
		LOC_ITEM_MY_TEAMS,
		);
		$actions = array(BNETADM_DISPATCH,
		BEVENT_DISPATCH,
		);
		
		if ( Oaccount::isLoginAdmin() )
		{
			$texts[] = LOC_ITEM_ADMINISTRATION;
			$actions[] = BADNET_ADM;
		}
		
		$lst = $menu->addList('lstMenuCaptain', $texts, $actions, Bn::getValue('item',0), 'targetBody');
		$lst->completeAttribute('class', 'badnetMenu');
		$menu->display();
		return false;
	}
	
	/**
	 * Menu pour les pages des visiteurs
	 */
	public function menuVisitor($aItem=0)
	{
 		require_once "Badnetplay/Badnetplay.inc";
		$menu = new Body();
		$actions = array(Bn::getConfigValue('accueil', 'params'), 
		PROJECT_BADNETRES, 
		BADNETPLAY_RESULTS, 
		BADNETPLAY_TOUR, 
		BADNETPLAY_INLINE);
		$texts = array('Accueil',
		LOC_ITEM_TOURNAMENTS,
		LOC_ITEM_RESULTS,
		LOC_ITEM_TOUR,
		LOC_ITEM_INLINE
		);

		$lnk = $menu->addLink('lnkEmail', MENU_FILL_EMAIL, null, 'targetDlg');
		$lnk->completeAttribute('class', 'bn-dlg');
		$lnk->addBalise('span');
		//$lnk->setTooltip(LOC_TIP_EMAIL);

		$lst = $menu->addList('lstMenuVisitor', $texts, $actions, $aItem, 'targetBody');
		$lst->completeAttribute('class', 'badnetMenu');
		
		$menu->display();
		return false;
	}
	
	/**
	 * Menu de l'administrateur
	 */
	public function menuAdm()
	{
		require_once "Badnetadm/Events/Events.inc";
		require_once "Badnetadm/Badnetadm.inc";
		$menu = new Body();
		$texts = array(
		LOC_ITEM_TOURNAMENTS,
		LOC_ITEM_ACCOUNTS,
		LOC_ITEM_MEMBERS,
		LOC_ITEM_ASSOCS,
		'Statistiques',
		LOC_ITEM_USER
		);
		$actions = array(
		EVENTS_PAGE_EVENTS,
		BNETADM_USERS,
		BNETADM_PLAYERS,
		BNETADM_ASSOCS,
		BNETADM_STATS,
		BNETADM_DISPATCH
		);
		$lst = $menu->addList('lstMenuAdm', $texts, $actions, Bn::getValue('item',0), 'targetBody');
		$lst->completeAttribute('class', 'badnetMenu');
		$menu->display();
		return false;
	}
	
	/**
	 * Menu d'un compte utilisateur
	 */
	public function menuUser()
	{
		require_once "Badnetadm/Badnetadm.inc";
		require_once "Badnetuser/Badnetuser.inc";
		$menu = new Body();
		$texts = array('',
		LOC_ITEM_MY_ACCOUNT,
		LOC_ITEM_MY_TOURNAMENTS,
		);
		$actions = array(Bn::getConfigValue('accueil', 'params'),
		BNETUSER_ACCOUNT,
		BNETUSER_EVENTS,
		);
		if ( Oaccount::isLoginAdmin() )
		{
			$texts[] = LOC_ITEM_ADMINISTRATION;
			$actions[] = BNETADM_EVENTS;
		}
		$lnk = $menu->addLink('lnkLogout', BNETADM_LOGOUT, null, 'targetBody');
		$lnk->addBalise('span', 'spaLogout');
		$lst = $menu->addList('lstMenuUser', $texts, $actions, Bn::getValue('item',0), 'targetBody');
		$lst->completeAttribute('class', 'badnetMenu');
		$menu->display();
		return false;
	}
	
	/**
	 * Menu d'un compte de type joueur
	 */
	public function menuPlayer()
	{
		require_once "Badnetadm/Badnetadm.inc";
		require_once "Badnetplayer/Badnetplayer.inc";
		$menu = new Body();
		$texts = array('',
		LOC_ITEM_MY_ACCOUNT,
		//LOC_ITEM_MY_TOURNAMENTS,
		LOC_ITEM_MY_RESULTS,
		LOC_ITEM_INLINE
		);
		$actions = array(Bn::getConfigValue('accueil', 'params'),
		BNETPLAYER_ACCOUNT,
		//BNETPLAYER_EVENTS,
		BNETPLAYER_RESULTS,
		BNETPLAYER_INLINE
		);
		if ( Oaccount::isLoginAdmin() )
		{
			$texts[] = LOC_ITEM_ADMINISTRATION;
			$actions[] = BNETADM_EVENTS;
		}
		$lnk = $menu->addLink('lnkLogout', BNETADM_LOGOUT, null, 'targetBody');
		$lnk->addBalise('span', 'spaLogout');
		$lst = $menu->addList('lstMenuPlayer', $texts, $actions, Bn::getValue('item',0), 'targetBody');
		$lst->completeAttribute('class', 'badnetMenu');
		$menu->display();
		return false;
	}
	
	/**
	 * Menu d'un compte lié a une association
	 */
	public function menuAssoc()
	{
		require_once "Badnetadm/Badnetadm.inc";
		require_once "Badnetclub/Badnetclub.inc";
		$menu = new Body();
		$texts = array('',
		LOC_ITEM_MY_ACCOUNT,
		LOC_ITEM_MY_CLUB,
		LOC_ITEM_MY_PLAYERS,
		LOC_ITEM_MY_TOURNAMENTS,
		LOC_ITEM_INLINE
		);
		$actions = array(Bn::getConfigValue('accueil', 'params'),
		BNETCLUB_ACCOUNT,
		BNETCLUB_CLUB,
		BNETCLUB_PLAYERS,
		BNETCLUB_EVENTS,
		BNETCLUB_INLINE
		);
		if ( Oaccount::isLoginAdmin() )
		{
			$texts[] = LOC_ITEM_ADMINISTRATION;
			$actions[] = BNETADM_EVENTS;
		}
		$lnk = $menu->addLink('lnkLogout', BNETADM_LOGOUT, null, 'targetBody');
		$lnk->addBalise('span', 'spaLogout');
		$lst = $menu->addList('lstMenuAssoc', $texts, $actions, Bn::getValue('item',0), 'targetBody');
		$lst->completeAttribute('class', 'badnetMenu');
		$menu->display();
		return false;
	}
	
	/**
	 * Menu pour les pages d'arbitrage
	 */
	public function menuUmpire($aItem=0)
	{
		require_once 'Badnet/Badnet.inc';
		require_once 'Badnetlocal/Badnetlocal.inc';
		$menu = new Body();
		$actions = array(BADNETLOCAL_UMPIRE, BADNETLOCAL_LIVE);
		$texts = array(LOC_ITEM_UMPIRE,	LOC_ITEM_LIVE);
		
		if ( Oaccount::isLoginAdmin() )
		{
			$texts[] = LOC_ITEM_ADMINISTRATION;
			$actions[] = BNETADM_EVENTS;
		}
		
		$lnk = $menu->addLink('lnkLogout', BADNETLOCAL_LOGOUT, null, 'targetBody');
		$lnk->addBalise('span', 'spaLogout');
		
		$lst = $menu->addList('lstMenuUmpire', $texts, $actions, $aItem, 'targetBody');
		$lst->completeAttribute('class', 'badnetMenu');
		$menu->display();
		return false;
	}
	

	

	/**
	 * Remplissage du dialogue email enovyé
	 *
	 * @return false
	 */
	public function emailSended()
	{
		// Preparer les champs de saisie
		$body = new Body();

		$t = $body->addP('', '', 'bn-title-3');
		$t->addBalise('span', '', LOC_TITLE_EMAIL);
		$body->addP('', LOC_MSG_EMAIL_SEND);
		$body->addButtonCancel('btnClose', LOC_BTN_CLOSE);
		$body->display();
		return false;
	}

	/**
	 * Envoi d'un email
	 *
	 * @return false
	 */
	public function sendEmail()
	{
		$mailer = BN::getMailer();
		$mailer->subject(Bn::getValue('txtObject'));
		$mailer->from('no-reply@badnet.org');
		$mailer->ReplyTo(Bn::getValue('txtFrom'));
		$mailer->cc(Bn::getValue('txtFrom'));
		$mailer->body(Bn::getValue('txtBody'));
		$mailer->bcc(Bn::getConfigValue('mailadmin', 'email'));
		$mailer->send('no-reply@badnet.org', false);
		if ( $mailer->isError() )
		{
			Bn::setUserMsg($mailer->getMsg());
			return MENU_FILL_EMAIL;
		}
		return MENU_EMAIL_SENDED;
	}

	/**
	 * Remplissage du dialogue d'envoi d'email
	 *
	 * @return false
	 */
	public function fillEmail()
	{
		// Preparer les champs de saisie
		$body = new Body();
		$form = $body->addForm('frmEmail', MENU_SEND_EMAIL, 'targetDlg');

		$t = $form->addP('', '', 'bn-title-3');
		$t->addBalise('span', '', LOC_TITLE_EMAIL);

		$form->addEdit('txtObject', LOC_LABEL_OBJECT, null, 50);
		$form->addEditEmail('txtFrom', LOC_LABEL_FROM, null, 50);
		$form->addArea('txtBody', LOC_LABEL_BODY);

		$div = $form->addDiv('', 'bn-div-btn');
		$div->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$div->addButtonValid('btnSend', LOC_BTN_VALID);
		$body->display();
		return false;
	}


	
}