<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Badnetadm/Badnetadm.inc';
require_once 'Badnet/Adm/Adm.inc';
require_once 'Captain.inc';
require_once 'Object/Oregi.inc';
require_once 'Object/Omatch.inc';
require_once 'Object/Omember.inc';
require_once 'Object/Oplayer.inc';
include_once 'Badnet/Locale/' . Bn::getLocale() . '/Captain.inc';
include_once 'Object/Locale/' . Bn::getLocale() . '/Omember.inc';

/**
 * Module de gestion des tournois
 */
class Captain
{
	// {{{ properties
	public function __construct()
	{
		$userId = Bn::getValue('user_id');
		if (empty($userId) ) return BNETADM_LOGOUT;
		$controller = Bn::getController();
		$controller->addAction(BCAPTAIN_PAGE_CAPTAIN,        $this, 'pageCaptain');

		$controller->addAction(BCAPTAIN_PAGE_PLAYERS,        $this, 'pagePlayers');
		$controller->addAction(BCAPTAIN_FILL_PLAYERS,        $this, 'fillPlayers');
		$controller->addAction(BCAPTAIN_PAGE_REGIS,          $this, 'pageRegis');
		$controller->addAction(BCAPTAIN_FILL_REGIS,          $this, 'fillRegis');
		$controller->addAction(BCAPTAIN_FILL_TIES,           $this, 'fillTies');

		$controller->addAction(BCAPTAIN_PAGE_ADD_PLAYER,     $this, 'pageAddPlayer');
		$controller->addAction(BCAPTAIN_ADD_PLAYER,          $this, 'addPlayer');
		$controller->addAction(BCAPTAIN_PAGE_NEW_PLAYER,     $this, 'pageNewPlayer');
		$controller->addAction(BCAPTAIN_NEW_PLAYER,          $this, 'newPlayer');
		$controller->addAction(BCAPTAIN_SEARCH_PLAYER,       $this, 'searchPlayer');
		$controller->addAction(BCAPTAIN_PAGE_PLAYER,         $this, 'pagePlayer');

		$controller->addAction(BCAPTAIN_PAGE_TIE,            $this, 'pageTie');
		$controller->addAction(BCAPTAIN_UPDATE_TIE,          $this, 'updateTie');
		$controller->addAction(BCAPTAIN_SEND_TIE,            $this, 'sendTie');
		$controller->addAction(BCAPTAIN_CONFIRM_TIE,         $this, 'confirmTie');
		$controller->addAction(BCAPTAIN_PAGE_RESULT,         $this, 'pageResult');

		$controller->addAction(BCAPTAIN_CONFIRM_DEL_PLAYER,  $this, 'confirmDelPlayer');
		$controller->addAction(BCAPTAIN_DEL_PLAYER,          $this, 'delPlayer');

		$controller->addAction(BCAPTAIN_PAGE_RESULTS,        $this, 'pageResults');

		$controller->addAction(BCAPTAIN_PAGE_EMAIL,          $this, 'pageEmail');
		$controller->addAction(BCAPTAIN_SEND_EMAIL,          $this, 'sendEmail');
		$controller->addAction(BCAPTAIN_PAGE_EMAIL_MANAGER,  $this, 'pageEmailManager');
		$controller->addAction(BCAPTAIN_SEND_EMAIL_MANAGER,  $this, 'sendEmailManager');
		$controller->addAction(BCAPTAIN_PAGE_EMAIL_PLAYER,   $this, 'pageEmailPlayer');
		$controller->addAction(BCAPTAIN_SEND_EMAIL_PLAYER,   $this, 'sendEmailPlayer');
		$controller->addAction(BCAPTAIN_PAGE_EMAIL_PLAYERS,  $this, 'pageEmailPlayers');
		$controller->addAction(BCAPTAIN_SEND_EMAIL_PLAYERS,  $this, 'sendEmailPlayers');
		$controller->addAction(BCAPTAIN_EMAIL_SENDED,        $this, 'emailSended');

		$controller->addAction(BCAPTAIN_PAGE_VCARD,          $this, 'pageVcard');
		$controller->addAction(BCAPTAIN_PAGE_GROUP,          $this, 'pageGroup');

		$controller->addAction(BCAPTAIN_PDF_VCARD,     $this, 'pdfVcard');
		$controller->addAction(BCAPTAIN_PDF_CALENDAR,  $this, 'pdfCalendar');
	}

	/**
	 * Calendrier des rencontres du groupe au format pdf
	 *
	 */
	public function pdfCalendar()
	{
		include 'Badnet/Locale/' . Bn::getLocale() . '/Commun.inc';
		// Creation du document
		include_once 'Bn/Bnpdf.php';
		$pdf = new Bnpdf();
		$pdf->setAutoPageBreak(true, 30);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);
		$pdf->start();

		//Donnee generale
		$oEvent = new Oevent(Bn::getValue('event_id'));
		$eventName = $oEvent->getVal('name');
		$groupId = Bn::getValue('roundId');
		$teamId  = Bn::getValue('teamId');
		$oGroup  = new Ogroup($groupId);
		$oDiv    = $oGroup->getDivision();

		// Nouvelle page
		$pdf->AddPage('P');
		$pdf->SetFont('helvetica', 'B', 16);
		$pdf->SetFillColor(255);
		$pdf->SetTextColor(0);
		$pdf->Cell(0, 0, $eventName, '', 1, 'L');
		$pdf->SetFont('helvetica', 'B', 14);
		$str = LOC_PDF_CALENDAR . $oDiv->getVal('name');
		$str .= ', ' . LOC_PDF_GROUP . $oGroup->getVal('name');
		$pdf->Cell(0, 0, $str, '', 1, 'L');
		$pdf->Ln(10);

		// Nombre de renocntre par tour
		$nbTeams = $oGroup->getVal('size');
		$nbTies = (int)($nbTeams/2);

		// Traiter chaque rencontre
		$pdf->SetFont('helvetica', 'B', 12);
		$tieIds  = $oGroup->getTies();
		$oGroup->getVal('type');
		$step = null;
		$i = 10;
		$journee = 0;
		foreach($tieIds as $tieId)
		{
			$i++;
			$oTie = new Otie($tieId);
			$oTeamh = $oTie->getTeam();
			$oTeamv = $oTie->getTeam(OTIE_TEAM_VISITOR);

			if ( $step != $oTie->getVal('step') || $i>=$nbTies)
			{
				$step = $oTie->getVal('step');
				$i = 0;
				$journee++;
				if( empty($step) ) $str = 'Journée ' . $journee;
				else $str = 'Journee ' . $step;
				$pdf->SetFont('helvetica', 'B', 10);
				$pdf->SetFillColor(200);
				$pdf->SetTextColor(255);
				$pdf->ln();
				$pdf->Cell(0, 0, $str, '', 1, 'L', 1);
			}

			// Date
			$pdf->SetFont('helvetica', '', 8);
			$pdf->SetTextColor(0);
			$pdf->Cell(30, 0, Bn::date($oTie->getVal('schedule'), 'd-m-Y H:i'), '', 0, 'L');

			// Equipe hote
			$str = $oTeamh->getVal('name');
			if ($oTeamh->getVal('id') == $teamId) $pdf->SetFont('helvetica', 'B', 8);
			else $pdf->SetFont('helvetica', '', 8);
			$pdf->Cell(60, 0, $str, '', 0, 'L');

			// Equipe visiteuse
			$str = $oTeamv->getVal('name');
			if ($oTeamv->getVal('id') == $teamId) $pdf->SetFont('helvetica', 'B', 8);
			else $pdf->SetFont('helvetica', '', 8);
			$pdf->Cell(60, 0, $str, '', 0, 'L');

			// Lieu
			$pdf->SetFont('helvetica', '', 8);
			$str = $oTie->getVal('place');
			$pdf->Cell(60, 0, $str, '', 1, 'L');
			//$pdf->ln();

			unset($oTeamh);
			unset($oTeamv);
			unset($oTie);
		}
			
		$filename = '../Temp/Pdf/test.pdf';
		$pdf->end($filename);
		return false;
	}

	/**
	 * Annuaires des capitaines au format pdf
	 *
	 */
	public function pdfVcard()
	{
		include 'Badnet/Locale/' . Bn::getLocale() . '/Commun.inc';
		// Creation du document
		include_once 'Bn/Bnpdf.php';
		$pdf = new Bnpdf();
		$pdf->setAutoPageBreak(true,30);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);
		$pdf->start();

		//Traiter chaque division
		$oEvent = new Oevent(Bn::getValue('event_id'));
		$eventName = $oEvent->getVal('name');

		$groupId = Bn::getValue('roundId');
		$oGroup = new Ogroup($groupId);
		$oDiv = $oGroup->getDivision();

		// Nouvelle page
		$pdf->AddPage('P');
		$pdf->SetFont('helvetica', 'B', 16);
		$pdf->SetFillColor(255);
		$pdf->SetTextColor(0);
		$pdf->Cell(0, 0, $eventName, '', 1, 'L');
		$pdf->SetFont('helvetica', 'B', 14);
		$str = LOC_PDF_ANNUAIRE . $oDiv->getVal('name');
		$str .= ', ' . LOC_PDF_GROUP . $oGroup->getVal('name');
		$pdf->Cell(0, 0, $str, '', 1, 'L');
		$pdf->Ln(10);

		// Traiter chaque equipe
		$pdf->SetFont('helvetica', 'B', 12);
		$teamIds = $oGroup->getTeams();
		foreach($teamIds as $teamId)
		{
			$oTeam = new Oteam($teamId);
			$str = $oTeam->getVal('name') . ' (' . $oTeam->getVal('stamp') . ')';
			$pdf->SetFont('helvetica', 'B', 10);
			$pdf->SetFillColor(200);
			$pdf->SetTextColor(255);
			$pdf->Cell(0, 0, $str, '', 1, 'L', 1);
			$pdf->SetFont('helvetica', 'B', 8);
			$pdf->SetTextColor(0);
			$captainId = $oTeam->getVal('captainid');
			$pdf->Cell(40, 0, LOC_PDF_CAPTAIN, '', 0, 'L');
			$pdf->SetFont('helvetica', '', 8);
			if ( !empty($captainId) )
			{
				$oAdhe = new Oadhe($oTeam->getVal('captainid'));
				$str = $oAdhe->getVal('firstname') . ' ' . $oAdhe->getVal('name');
				$pdf->Cell(80, 0, $str, '', 0, 'L');

				$str = $oAdhe->getVal('fixe');
				$pdf->Cell(20, 0, LOC_PDF_PHONE, '', 0, 'R');
				$pdf->Cell(60, 0, $str, '', 1, 'L');

				$str = $oAdhe->getVal('email');
				$pdf->Cell(40, 0, '', '', 0, 'L');
				$pdf->Cell(80, 0, $str, '', 0, 'L');
				$str = $oAdhe->getVal('mobile');
				$pdf->Cell(20, 0, LOC_PDF_MOBILE, '', 0, 'R');
				$pdf->Cell(60, 0, $str, '', 1, 'L');
			}
			else
			{
				$pdf->SetTextColor(255, 0, 0);
				$pdf->Cell(90, 0, LOC_PDF_NO_CAPTAIN, '', 1, 'L');
				$pdf->SetTextColor(255);
			}
			$pdf->ln();
			unset($oTeam);
		}
			
		$filename = '../Temp/Pdf/test.pdf';
		$pdf->end($filename);
		return false;
	}

	/**
	 * Envoi d'un email a tous les joueuer
	 *
	 * @return false
	 */
	public function sendEmailPlayers()
	{
		// Recherche du destinataire: les gestionnaires du tournoi
		$teamId = Bn::getValue('teamId');
		$oTeam = new Oteam($teamId);
		$playerIds = $oTeam->getRegis(OREGI_TYPE_PLAYER);
		$emails = array();
		foreach($playerIds as $playerId)
		{
			$oPlayer = new Oplayer($playerId);
			$oMember = new Omember($oPlayer->getVal('memberid'));
			$email = $oMember->getVal('email');
			if( !empty($email) ) $emails[] = $email;
			unset($oMember);
			unset($oPlayer);
		}

		// Envoyer l'email
		if ( count($emails))
		{
			$mailer = BN::getMailer();
			$mailer->subject(Bn::getValue('txtObject'));
			$mailer->from('no-reply@badnet.org');
			$mailer->ReplyTo(Bn::getValue('txtFrom'));
			$mailer->cc(Bn::getValue('txtFrom'));
			$mailer->body(Bn::getValue('txtBody'));
			//$mailer->bcc(Bn::getConfigValue('mailadmin', 'email'));
			$mailer->send($emails, false);
			if ( $mailer->isError() )
			{
				Bn::setUserMsg($mailer->getMsg());
				return BCAPTAIN_PAGE_EMAIL_PLAYER;
			}
			return BCAPTAIN_EMAIL_SENDED;
		}
		else
		{
			Bn::setUserMsg('Aucun email renseigné. Envoi annulé.');
			return BCAPTAIN_PAGE_EMAIL_PLAYER;
		}
	}

	/**
	 * Affichage de la page d'envoi d'un email a tous les joueurs
	 *
	 */
	public function pageEmailPlayers()
	{
		$teamId = Bn::getValue('teamId');
		$oAdhe = new Oadhe(Bn::getValue('user_id'));

		// Preparer les champs de saisie
		$body = new Body();
		$form = $body->addForm('frmEmail', BCAPTAIN_SEND_EMAIL_PLAYERS, 'targetDlg');
		$form->addHidden('teamId', $teamId);
		$form->addEdit('txtObject', LOC_LABEL_OBJECT, null, 50);
		$form->addEditEmail('txtFrom', LOC_LABEL_FROM, $oAdhe->getVal('email'), 50);
		$form->addArea('txtBody', LOC_LABEL_BODY);

		$div = $form->addDiv('', 'bn-div-btn');
		$div->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$div->addButtonValid('btnSend', LOC_BTN_VALID);
		$body->display();
		return false;
	}


	/**
	 * Envoi d'un email a un joueur
	 *
	 * @return false
	 */
	public function sendEmailPlayer()
	{
		// Recherche du destinataire: les gestionnaires du tournoi
		$regiId = Bn::getValue('regiId');
		$oRegi = new Oregi($regiId);
		$oMember = new Omember($oRegi->getVal('memberid'));

		// Envoyer l'email
		$mailer = BN::getMailer();
		$mailer->subject(Bn::getValue('txtObject'));
		$mailer->from('no-reply@badnet.org');
		$mailer->ReplyTo(Bn::getValue('txtFrom'));
		$mailer->cc(Bn::getValue('txtFrom'));
		$mailer->body(Bn::getValue('txtBody'));
		//$mailer->bcc(Bn::getConfigValue('mailadmin', 'email'));
		$mailer->send($oMember->getVal('email'), false);
		if ( $mailer->isError() )
		{
			Bn::setUserMsg($mailer->getMsg());
			return BCAPTAIN_PAGE_EMAIL_PLAYER;
		}
		return BCAPTAIN_EMAIL_SENDED;
	}

	/**
	 * Affichage de la page d'envoi d'un email a un joueur
	 *
	 */
	public function pageEmailPlayer()
	{
		$regiId = Bn::getValue('regiId');
		$oPlayer = new Oplayer($regiId);
		$oAdhe = new Oadhe(Bn::getValue('user_id'));

		// Preparer les champs de saisie
		$body = new Body();
		$form = $body->addForm('frmEmail', BCAPTAIN_SEND_EMAIL_PLAYER, 'targetDlg');
		$form->addHidden('regiId', $regiId);
		$form->addP('', $oPlayer->getVal('longname'), 'bn-title-4');
		$form->addEdit('txtObject', LOC_LABEL_OBJECT, null, 50);
		$form->addEditEmail('txtFrom', LOC_LABEL_FROM, $oAdhe->getVal('email'), 50);
		$form->addArea('txtBody', LOC_LABEL_BODY);

		$div = $form->addDiv('', 'bn-div-btn');
		$div->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$div->addButtonValid('btnSend', LOC_BTN_VALID);
		$body->display();
		return false;
	}

	/**
	 * Envoi d'un email au manager
	 *
	 * @return false
	 */
	public function sendEmailManager()
	{
		// Recherche du destinataire: les gestionnaires du tournoi
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$managers = $oEvent->getManagers();
		foreach($managers as $manager)
		{
			$tos[]=$manager['user_email'];
		}

		// Envoyer l'email
		$mailer = BN::getMailer();
		$mailer->subject(Bn::getValue('txtObject'));
		$mailer->from('no-reply@badnet.org');
		$mailer->ReplyTo(Bn::getValue('txtFrom'));
		$mailer->cc(Bn::getValue('txtFrom'));
		$mailer->body(Bn::getValue('txtBody'));
		//$mailer->bcc(Bn::getConfigValue('mailadmin', 'email'));
		$mailer->send(implode(',', $tos), false);
		if ( $mailer->isError() )
		{
			Bn::setUserMsg($mailer->getMsg());
			return BCAPTAIN_PAGE_EMAIL_MANAGER;
		}
		return BCAPTAIN_EMAIL_SENDED;
	}

	/**
	 * Affichage de la page d'envoi d'un email aux gestionnaires
	 *
	 */
	public function pageEmailManager()
	{
		// Preparer les champs de saisie
		$body = new Body();
		$form = $body->addForm('frmEmail', BCAPTAIN_SEND_EMAIL_MANAGER, 'targetDlg');

		$oAdhe = new Oadhe(Bn::getValue('user_id'));
		$form->addEdit('txtObject', LOC_LABEL_OBJECT, null, 50);
		$form->addEditEmail('txtFrom', LOC_LABEL_FROM, $oAdhe->getVal('email'), 50);
		$form->addArea('txtBody', LOC_LABEL_BODY);

		$div = $form->addDiv('', 'bn-div-btn');
		$div->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$div->addButtonValid('btnSend', LOC_BTN_VALID);
		$body->display();
		return false;
	}

	/**
	 * Affichage de la carte de visite d'un capitaine
	 *
	 */
	public function pageVcard()
	{
		$teamId = Bn::getValue('teamId', -1);
		$oTeam = new Oteam($teamId);
		$oAdhe = new Oadhe($oTeam->getVal('captainid', -1));

		// Preparer les champs de saisie
		$body = new Body();

		$str = $oTeam->getVal('name');
		$t = $body->addP('', LOC_TITLE_VCARD, 'bn-title-1');
		$t->addBalise('span', '', $str);

		$str = $oAdhe->getVal('name') . ' ' . $oAdhe->getVal('firstname');
		$body->addP('', $str);
		$str = $oAdhe->getVal('code') . ' ' . $oAdhe->getVal('ville') . ' ' . $oAdhe->getVal('cedex');
		$body->addP('', $str);
		$str = $oAdhe->getVal('rue') . ' ' . $oAdhe->getVal('lieu') . ' ' . $oAdhe->getVal('appt');
		$body->addP('', $str);
		$body->addP('', $oAdhe->getVal('email'));
		$str = trim($oAdhe->getVal('mobile'));
		if ( !empty($str) ) $body->addP('', $str);
		$str = trim($oAdhe->getVal('fixxe'));
		if ( !empty($str) ) $body->addP('', $str);

		$div = $body->addDiv('', 'bn-div-btn');
		$div->addButtonCancel('btnCancel', LOC_BTN_CLOSE);
		$body->display();
		return false;
	}

	/**
	 * Affichage de la page avec le classement du groupe et les resultats des rencontres
	 */
	public function pageResults()
	{
		$eventId = Bn::getValue('event_id');
		$teamId = Bn::getValue('teamId');
		$oEvent = new Oevent($eventId);

		$body = new Body();
		// Commandes generiques
		$div = $body->addDiv('', 'bn-menu-right');
		$lnk = $div->addP()->addLink('', BCAPTAIN_PAGE_CAPTAIN . '&teamId=' . Bn::getValue('teamId'), LOC_LINK_BACK_CAPTAIN, 'targetBody');
		$lnk->addIcon('arrowreturnthick-1-w');

		// Titre
		$t = $body->addP('', $oEvent->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', $oEvent->getVal('date'));

		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '', LOC_TITLE_RESULTS);

		$div = $body->addRichDiv('', 'bn-div-left');
		$div = $div->addRichDiv('divEvent');
		$t = $div->addP('tltCivil', '', 'bn-title-3');
		$t->addBalise('span', '',  LOC_TITLE_EVENT);
		$div->addInfo('name',  LOC_LABEL_NAME,  $oEvent->getVal('name'));
		$div->addInfo('date',  LOC_LABEL_DATE,  $oEvent->getVal('date'));
		$div->addInfo('place', LOC_LABEL_PLACE, $oEvent->getVal('place'));
		$div->addInfo('organizer', LOC_LABEL_ORGANIZER,  $oEvent->getVal('organizer'));
		$div->addInfo('firstday', LOC_LABEL_FIRSTDAY, Bn::date($oEvent->getVal('firstday'), 'd-m-Y'));
		$div->addInfo('lastday', LOC_LABEL_LASTDAY,  Bn::date($oEvent->getVal('lastday'), 'd-m-Y'));

		// Recuperer les groupes de l'equipe
		$oTeam = new Oteam($teamId);
		$roundIds = $oTeam->getRounds();

		// Info pour le capitaine
		$div = $body->addRichDiv('', 'bn-div-left');
		$d = $div->addRichDiv('divP');
		$d->addP('', LOC_P_INFO_TEAM, 'bn-p-info');
		$t = $d->addP('tltCivil', '', 'bn-title-2');
		$t->addBalise('span', '',  $oTeam->getVal('name'));
		$body->addBreak();

		foreach($roundIds as $roundId)
		{
			$oRound = new Ogroup($roundId);
			// Afficher le classement du groupe
			$oRound->displayRanking($body);

			// Afficher les rencontres du groupe
			//$oRound->displayGroup($body);
			//$oRound->updateAr();
			unset($oRound);
		}
		$body->display();
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
		// Recherche du destinataire : le capitaine de l'autre equipe
		$tieId = Bn::getValue('tieId');
		$oTie = new Otie($tieId);
		$oTeamH = $oTie->getTeam(OTIE_TEAM_RECEIVER);
		$oTeamV = $oTie->getTeam(OTIE_TEAM_VISITOR);
		if ( $oTeamH->getVal('captainid', -1) == Bn::getValue('user_id') )
		{
			$oAccount = new Oaccount($oTeamV->getVal('captainid', -1));
		}
		else //if ( $oTeamV->getVal('id', -1) == $teamId )
		{
			$oAccount = new Oaccount($oTeamH->getVal('captainid', -1));
		}

		$mailer = BN::getMailer();
		$mailer->subject(Bn::getValue('txtObject'));
		$mailer->from('no-reply@badnet.org');
		$mailer->ReplyTo(Bn::getValue('txtFrom'));
		$mailer->cc(Bn::getValue('txtFrom'));
		$mailer->body(Bn::getValue('txtBody'));
		//$mailer->bcc(Bn::getConfigValue('mailadmin', 'email'));
		$mailer->send($oAccount->getVal('email'), false);
		if ( $mailer->isError() )
		{
			Bn::setUserMsg($mailer->getMsg());
			return BCAPTAIN_PAGE_EMAIL;
		}
		return BCAPTAIN_EMAIL_SENDED;
	}

	/**
	 * Affichage de la page d'envoi d'un email a un capitaine
	 *
	 */
	public function pageEmail()
	{
		$tieId = Bn::getValue('tieId');
		$oTie = new Otie($tieId);
		$oTeamH = $oTie->getTeam(OTIE_TEAM_RECEIVER);
		$oTeamV = $oTie->getTeam(OTIE_TEAM_VISITOR);

		// Preparer les champs de saisie
		$body = new Body();
		$form = $body->addForm('frmEmail', BCAPTAIN_SEND_EMAIL, 'targetDlg');
		$form->addHidden('tieId', $tieId);

		$str = $oTeamH->getVal('name') . ' - ' . $oTeamV->getVal('name');
		$oAdhe = new Oadhe(Bn::getValue('user_id'));
		$form->addEdit('txtObject', LOC_LABEL_OBJECT, $str, 50);
		$form->addEditEmail('txtFrom', LOC_LABEL_FROM, $oAdhe->getVal('email'), 50);
		$form->addArea('txtBody', LOC_LABEL_BODY);

		$div = $form->addDiv('', 'bn-div-btn');
		$div->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$div->addButtonValid('btnSend', LOC_BTN_VALID);
		$body->display();
		return false;
	}

	/**
	 * Affichage de la cartouche d'une rencontre
	 *
	 */
	private function _displayTie($aTie, $aDiv)
	{
		$oTeamHote  = $aTie->getTeam(OTIE_TEAM_RECEIVER);
		$oTeamVisit = $aTie->getTeam(OTIE_TEAM_VISITOR);
		$oDraw = $aTie->getDivision();
		$oGroup = $aTie->getGroup();

		$d = $aDiv->addRichDiv('divTie');
		$t = $d->addP('', '', 'bn-title-3');
		$t->addBalise('span', '',  $oTeamHote->getVal('name') . '-' . $oTeamVisit->getVal('name'));
		$d->addInfo('division', LOC_LABEL_DIVISION,  $oDraw->getVal('name'));
		$d->addInfo('group',    LOC_LABEL_GROUP,  $oGroup->getVal('name'));
		$d->addInfo('schedu', LOC_LABEL_DATE,  $aTie->getVal('schedule'));
		$d->addInfo('place',  LOC_LABEL_PLACE, $aTie->getVal('place'));
		$step = $aTie->getVal('step', '');
		if (!empty($step))	$d->addInfo('step',   LOC_LABEL_STEP,  $step);

		/*
		 $date = Bn::date($aTie->getVal('entrydate'), 'd-m-Y H:i');
		 if (!empty($date))
		 {
			$oAccount = new Oaccount($aTie->getVal('entryid', -1));
			$p = $d->addP('', LOC_LABEL_REGISTERED);
			$p->addMetaContent($oAccount->getVal('name'));
			$p->addMetaContent($date);
			}

			$date = Bn::date($aTie->getVal('validdate'), 'd-m-Y H:i');
			if (!empty($date))
			{
			$oAccount = new Oaccount($aTie->getVal('validid', -1));
			$p = $d->addP('', LOC_LABEL_VALIDATE);
			$p->addMetaContent($oAccount->getVal('name'));
			$p->addMetaContent($date);
			}
			*/
	}

	/**
	 * Affichage du resultat d'une rencontre
	 *
	 */
	public function pageResult()
	{
		// Donnees
		$tieId   = Bn::getValue('tieId');
		$eventId = Bn::getValue('event_id');
		$teamId  = Bn::getValue('teamId');
		$oTie    = new Otie($tieId);
		$oEvent  = new Oevent($eventId);
		$oTeam   = new Oteam($teamId);

		// Page et titre
		$body = new Body();
		// Commandes generiques
		$div = $body->addDiv('', 'bn-menu-right');
		$lnk = $div->addP()->addLink('', BCAPTAIN_PAGE_CAPTAIN . '&teamId=' . Bn::getValue('teamId'), LOC_LINK_BACK_CAPTAIN, 'targetBody');
		$lnk->addIcon('arrowreturnthick-1-w');

		// Titre
		$t = $body->addP('', $oEvent->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', $oEvent->getVal('date'));

		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '',  LOC_TITLE_TIE);

		// Infos de la rencontre
		$div = $body->addRichDiv('', 'bn-div-left');
		$this->_displayTie($oTie, $div);

		$div = $body->addRichDiv('', 'bn-div-left');
		$d = $div->addRichDiv('divP');
		$d->addP('', LOC_P_INFO_TEAM, 'bn-p-info');
		$t = $d->addP('tltCivil', '', 'bn-title-2');
		$t->addBalise('span', '',  $oTeam->getVal('name'));
		$date = Bn::date($oTie->getVal('entrydate'), 'd-m-Y H:i');
		if (!empty($date))
		{
			$oAccount = new Oaccount($oTie->getVal('entryid', -1));
			$p = $d->addP('', LOC_LABEL_REGISTERED);
			$p->addMetaContent($oAccount->getVal('name'));
			$p->addMetaContent($date);
		}

		$date = Bn::date($oTie->getVal('validdate'), 'd-m-Y H:i');
		if (!empty($date))
		{
			$oAccount = new Oaccount($oTie->getVal('validid', -1));
			$p = $d->addP('', LOC_LABEL_VALIDATE);
			$p->addMetaContent($oAccount->getVal('name'));
			$p->addMetaContent($date);
		}

		// Affichage des matches
		$oMatchTeams = $oTie->getMatchs();
		foreach ($oMatchTeams as $oMatchTeam){
			$oMatchTeam->displayConsult($body);
		}

		if ( $oTie->getVal('entryid') > 0 && $oTie->getVal('validid') <= 0 &&
			 $oTie->getVal('entryid') != Bn::getValue('user_id') )
		{
			$d->addP('', LOC_MSG_VALIDATION, 'bn-p-info');
			$d = $d->addDiv('', 'bn-div-btn');
			$btn = $d->addButton('btnAccept', LOC_BTN_ACCEPT, BCAPTAIN_CONFIRM_TIE, null, 'targetBody');
			$btn->addMetaData('teamId', $teamId);
			$btn->addMetaData('tieId', $tieId);
			$btn = $d->addButton('btnModif', LOC_BTN_MODIFY, BCAPTAIN_PAGE_TIE, null, 'targetBody');
			$btn->addMetaData('teamId', $teamId);
			$btn->addMetaData('tieId', $tieId);
		}


		// Envoi au navigateur
		$body->display();
		return false;
	}

	/**
	 * Confirmation du resultat par le capitaine adverse
	 *
	 */
	public function confirmTie()
	{
		// Donnees
		$tieId = Bn::getValue('tieId');
		$oTie = new Otie($tieId);

		// Verifier que la rencontre n'est pas valide ou controle
		// et que ce n'est pas le capitaine connecte qui a saisi
		// et que la date limite n'est pas depassee
		if ( $oTie->getVal('validid', -1) > 0 ) return BCAPTAIN_PAGE_RESULT;
		if ( $oTie->getVal('controlid', -1) > 0 ) return BCAPTAIN_PAGE_RESULT;
		if ( $oTie->getVal('entryid', -1) == Bn::getValue('user_id') ) return BCAPTAIN_PAGE_RESULT;
		$eventId = Bn::getValue('event_id');
		$oEvent  = new Oevent($eventId);
		$dateMax = $oEvent->getDelayCaptain($oTie->getVal('schedule'));
		$now = date('U');
		if ($now > $dateMax) return BCAPTAIN_PAGE_RESULT;

		// Verifier que tous les matchs sont renseignes
		$oMatchTeams = $oTie->getMatchs();
		foreach ($oMatchTeams as $oMatchTeam)
		{
			if ($oMatchTeam->getVal('status') < OMATCH_STATUS_ENDED) return BCAPTAIN_PAGE_TIE;
		}
		// Mise a jour de l'etat des matchs
		foreach ($oMatchTeams as $oMatchTeam)
		{
			$oMatchTeam->setVal('status', OMATCH_STATUS_CLOSED);
			$oMatchTeam->save();
		}

		// Enregistrement de la date et de l'auteur de la saisie
		$oTie->setVal('validdate', date('Y-m-d H:i:s'));
		$oTie->setVal('validid', Bn::getValue('user_id'));
		$oTie->save();

		// Envoi email au capitaine adverse et aux gestionnaires
		$eventId = $oTie->getDivision()->getval('eventid');
		$oEvent  = new Oevent($eventId);
		$managers = $oEvent->getManagers();
		$tos[] = Bn::getConfigValue('mailadmin', 'email');
		foreach($managers as $manager)
		{
			$tos[]=$manager['user_email'];
		}

		// Recherche du destinataire : le capitaine de l'autre equipe
		$oTeamH = $oTie->getTeam(OTIE_TEAM_RECEIVER);
		$oTeamV = $oTie->getTeam(OTIE_TEAM_VISITOR);
		if ( $oTeamH->getVal('captainid', -1) == Bn::getValue('user_id') )
		{
			$oAccount = new Oaccount($oTeamV->getVal('captainid', -1));
			$teamId = $oTeamH->getVal('id');
		}
		else //if ( $oTeamV->getVal('id', -1) == $teamId )
		{
			$oAccount = new Oaccount($oTeamH->getVal('captainid', -1));
			$teamId = $oTeamV->getVal('id');
		}

		$mailer = BN::getMailer();
		$mailer->subject('Resultat - ' . $oEvent->getVal('name'));
		$expediteur = Bn::getValue('user_email');
		$mailer->from($expediteur);
		$mailer->ReplyTo($expediteur);

		$str = $oTeamH->getVal('name') . ' - ' . $oTeamV->getVal('name') . "\n\n";
		$str .= "Le résultat de cette rencontre est validé.";
		$mailer->body($str);
		$mailer->bcc($tos);
		$mailer->send($oAccount->getVal('email'), false);
		if ( $mailer->isError() )
		{
			Bn::setUserMsg($mailer->getMsg());
		}

		// Affichage du message de fin
		$body = new Body();

		// Titre
		$t = $body->addP('', $oEvent->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', $oEvent->getVal('date'));

		$body->addWarning(LOC_MSG_RESULT_CONFIRMED);

		// Retour a la page d'accueil
		$div = $body->addDiv();
		$lnk = $div->addP()->addLink('', BCAPTAIN_PAGE_CAPTAIN . '&teamId=' . $teamId, LOC_LINK_BACK_CAPTAIN, 'targetBody');
		$lnk->addIcon('arrowreturnthick-1-w');

		$body->display();
		return false;
	}

	/**
	 * Envoie du resultat au capitaine de l'equipe adverse
	 *
	 */
	public function sendTie()
	{
		include_once 'Badnet/Locale/' . Bn::getLocale() . '/Captain.inc';
		// Donnees
		$eventId = Bn::getValue('event_id');
		$tieId   = Bn::getValue('tieId');
		$teamId  = Bn::getValue('teamId');
		$oEvent  = new Oevent($eventId);
		$oTie    = new Otie($tieId);
		$oMatchTeams = $oTie->getMatchs();

		// Recherche du destinataire : le capitaine de l'autre equipe
		$oTeamH = $oTie->getTeam(OTIE_TEAM_RECEIVER);
		$oTeamV = $oTie->getTeam(OTIE_TEAM_VISITOR);
		if ( $oTeamH->getVal('id', -1) == $teamId )
		{
			$oAccount = new Oaccount($oTeamV->getVal('captainid', -1));
			$args = 'teamId=' . $oTeamV->getVal('id', -1);
		}
		else //if ( $oTeamV->getVal('id', -1) == $teamId )
		{
			$oAccount = new Oaccount($oTeamH->getVal('captainid', -1));
			$args = 'teamId=' . $oTeamH->getVal('id', -1);
		}

		// Enregistrement de la date et de l'auteur de la saisie
		$oTie->setVal('entrydate', date('Y-m-d H:i:s'));
		$oTie->setVal('entryid', Bn::getValue('user_id'));
		$comment = Bn::getValue('msg');
		$oTie->setVal('entrycomment', $comment);
		$oTie->save();

		// Enregistrer la demande
		$args .= '&eventId=' . $eventId;
		$args .= '&tieId=' . $tieId;

		// Envoi email au capitaine adverse et aux gestionnaires
		$managers = $oEvent->getManagers();
		$tos[] = Bn::getConfigValue('mailadmin', 'email');
		foreach($managers as $manager)
		{
			$tos[]=$manager['user_email'];
		}

		$mailer = BN::getMailer();
		$mailer->subject('Résultat -' . $oEvent->getVal('name'));
		$expediteur = Bn::getValue('user_email');
		$mailer->from($expediteur);
		$mailer->ReplyTo($expediteur);

		$lnkok = Orequest::getLink($oAccount->getVal('id'), $args, BCAPTAIN_CONFIRM_TIE);
		$lnknok = Orequest::getLink($oAccount->getVal('id'), $args, BCAPTAIN_PAGE_TIE);

		$result = $oTie->getDivision()->getVal('name') . "\n";
		$result .= $oTie->getGroup()->getVal('name') . "\n";
		$result .= $oTeamH->getVal('name') . ' - ' . $oTeamV->getVal('name') . "\n\n";
		foreach($oMatchTeams as $oMatchTeam)
		{
			$player = $oMatchTeam->getVal('playerh1');
			$result .= $player['regi_longname'] . ' ';
			$player = $oMatchTeam->getVal('playerh2');
			$result .= $player['regi_longname'] . " \t";

			$result .= $oMatchTeam->getVal('score') . " \t" ;

			$player = $oMatchTeam->getVal('playerv1');
			$result .= $player['regi_longname'] . ' ';
			$player = $oMatchTeam->getVal('playerv2');
			$result .= $player['regi_longname'] . "\n";
		}
		//$result .= 'team 1 bat Team 2 : 3-0';

		$msg = new Bn_balise('', '', LOC_MSG_TIE_CONFIRM);
		$delay = $oEvent->getDelayCaptain($oTie->getVal('schedule'));
		$msg->addMetaContent(date('d-m-Y H:i',$delay));
		$msg->addMetaContent($result);
		$msg->addMetaContent($comment);
		$msg->addMetaContent($lnkok);
		$msg->addMetaContent($lnknok);
		$mailer->body($msg->toHtml());
		$mailer->bcc($tos);
		$mailer->send($oAccount->getVal('email'), false);
		if ( $mailer->isError() )
		{
			echo Bn::setUserMsg($mailer->getMsg());
			return BCAPTAIN_PAGE_CAPTAIN;
		}

		// Affichage du message de fin
		$body = new Body();

		// Titre
		$t = $body->addP('', $oEvent->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', $oEvent->getVal('date'));

		$body->addWarning(LOC_MSG_RESULT_SENDED);

		// Retour a la page d'accueil
		$div = $body->addDiv();
		$lnk = $div->addP()->addLink('', BCAPTAIN_PAGE_CAPTAIN . '&teamId=' . $teamId, LOC_LINK_BACK_CAPTAIN, 'targetBody');
		$lnk->addIcon('arrowreturnthick-1-w');

		$body->display();
		return false;
	}

	/**
	 * Enregistrement de la rencontre et affichage de la page de confirmation
	 *
	 */
	public function updateTie()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$tieId   = Bn::getValue('tieId');
		$teamId  = Bn::getValue('teamId');
		$oEvent  = new Oevent($eventId);
		$oTie    = new Otie($tieId);
		$oMatchTeams = $oTie->getMatchs();

		// Enregistrer les resultats des matchs
		$scosys = 'Oscore_' . $oEvent->getVal('scoringsystem');
		$oScore = new $scosys();
		foreach ($oMatchTeams as $oMatchTeam)
		{
			$matchId = $oMatchTeam->getVal('id', -1);
			$regih1Id = Bn::getValue('h1_' . $matchId, 0);
			$regih2Id = Bn::getValue('h2_' . $matchId, 0);
			$regiv1Id = Bn::getValue('v1_' . $matchId, 0);
			$regiv2Id = Bn::getValue('v2_' . $matchId, 0);
			$oMatchTeam->setval('score', $oScore->getValue($matchId));
			$oMatchTeam->setval('playerh1id', $regih1Id);
			$oMatchTeam->setval('playerh2id', $regih2Id);
			$oMatchTeam->setval('playerv1id', $regiv1Id);
			$oMatchTeam->setval('playerv2id', $regiv2Id);
			$oMatchTeam->setval('resultv', $oScore->getResultv($matchId));
			$oMatchTeam->setval('resulth', $oScore->getResulth($matchId));
			$oMatchTeam->setVal('status', OMATCH_STATUS_ENDED);
			$oMatchTeam->setVal('begin', date('Y-m-d H:i:s'));
			$oMatchTeam->setVal('end', date('Y-m-d H:i:s'));
			$oMatchTeam->saveResult();
		}

		// mettre jour les equipes
		$oTeamHote  = $oTie->getTeam(OTIE_TEAM_RECEIVER);
		$oTeamHote->updateResult($tieId);
		$oTeamVisit = $oTie->getTeam(OTIE_TEAM_VISITOR);
		$oTeamVisit->updateResult($tieId);
		$oGroup = $oTie->getGroup();
		$oTeamVisit->updateGroupRank($oGroup->getVal('id', -1));

		// Affichage de la page
		$body = new Body();
		$t = $body->addP('', $oEvent->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', $oEvent->getVal('date'));

		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '',  LOC_TITLE_TIE);

		// Affichage du detail de la rencontre
		$this->_displayTie($oTie, $body);
		$body->addP('', LOC_P_TIE_CONFIRM, 'bn-p-info');

		// 	Affichage de resultats des matchs
		foreach ($oMatchTeams as $oMatchTeam)
		{
			$oMatchTeam->displayConsult($body);
		}

		// Affichage de la zone de saisie de message
		$form = $body->addForm('frmMatchs', BCAPTAIN_SEND_TIE, 'targetBody');
		$form->addHidden('tieId', $tieId);
		$form->addHidden('teamId', $teamId);
		$area = $form->addArea('msg', LOC_LABEL_COMMENT, null);
		$area->noMandatory();

		// Boutons
		$d = $form->addDiv('', 'bn-div-btn');
		$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL, BCAPTAIN_PAGE_TIE, 'targetBody');
		$btn->addMetaData('teamId', $teamId);
		$btn->addMetaData('tieId', $tieId);
		$d->addButtonValid('', LOC_BTN_SEND);

		// Envoi au navigateur
		$body->display();
		return false;
	}

	/**
	 * Formulaire de saisie des resultats d'une rencontre
	 */
	public function pageTie()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$tieId   = Bn::getValue('tieId');
		$teamId  = Bn::getValue('teamId');
		$oEvent  = new Oevent($eventId);
		$oTie    = new Otie($tieId);
		$oTeam   = new Oteam($teamId);

		// Verifier que la rencontre n'est pas valide
		// et que ce n'est pas le capitaine connecte qui a saisi
		if ( $oTie->getVal('validid', -1) > 0 ) return BCAPTAIN_PAGE_RESULT;
		if ( $oTie->getVal('entryid', -1) == Bn::getValue('user_id') ) return BCAPTAIN_PAGE_RESULT;

		$body = new Body();
		$t = $body->addP('', $oEvent->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', $oEvent->getVal('date'));

		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '',  LOC_TITLE_TIE);

		// Affichage du detail de la rencontre
		$div = $body->addRichDiv('', 'bn-div-left');
		$this->_displayTie($oTie, $div);

		$div = $body->addRichDiv('', 'bn-div-left');
		$d = $div->addRichDiv('divP');
		$d->addP('', LOC_P_INFO_TEAM, 'bn-p-info');
		$t = $d->addP('tltCivil', '', 'bn-title-2');
		$t->addBalise('span', '',  $oTeam->getVal('name'));
		$d->addP('', LOC_P_TIE, 'bn-p-info');

		// Formulaire de saisie
		$form = $body->addForm('frmResults', BCAPTAIN_UPDATE_TIE, 'targetBody');
		$form->addHidden('tieId', $tieId);
		$form->addHidden('teamId', $teamId);

		// Preparer les champs de saisie pour les matches
		$oMatchTeams = $oTie->getMatchs();
		$tabIndex = 0;
		foreach($oMatchTeams as $oMatchTeam) $tabIndex = $oMatchTeam->display($form, $tabIndex);

		// Boutons
		$d = $form->addDiv('', 'bn-div-btn');
		$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL, BCAPTAIN_PAGE_CAPTAIN, 'targetBody');
		$btn->addMetaData('teamId', $teamId);
		$d->addButtonValid('', LOC_BTN_UPDATE);

		// Envoi au navigateur
		$body->display();
		return false;
	}

	/**
	 * Formulaire de création d'un nouveau joueur
	 */
	public function pageNewPlayer()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$teamId  = Bn::getValue('teamId');
		$oEvent  = new Oevent($eventId);

		$body = new Body();
		$t = $body->addP('', $oEvent->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', $oEvent->getVal('date'));

		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '',  LOC_TITLE_PLAYER);

		$form = $body->addForm('frmPlayer', BCAPTAIN_NEW_PLAYER, 'targetBody');

		// Preparer les champs de saisie
		$form->addHidden('teamId', $teamId);
		$form->addHidden('memberId', -3);

		$dl = $form->addRichDiv('', 'bn-div-left');
		$dr = $form->addRichDiv('', 'bn-div-left');
		$d = $dl->addRichDiv('divCivil');
		$t = $d->addP('', '', 'bn-title-3');
		$t->addBalise('span', '',  LOC_TITLE_CIVIL);

		$form->addHidden('valgender', OMEMBER_MALE);
		$radio = $d->addRadio('gender', 'genderM',   LOC_LABEL_MAN,   OMEMBER_MALE,   true);
		$radio = $d->addRadio('gender', 'genderF',   LOC_LABEL_WOMAN,   OMEMBER_FEMALE,   false);
		$edit = $d->addEdit('famname',   LOC_LABEL_FAMNAME,    null, 30);
		$edit->autoComplete(BCAPTAIN_SEARCH_PLAYER, 'formatItem', 'formatResult', 'selectMember');
		$d->addEdit('firstname', LOC_LABEL_FIRSTNAME,  '', 30);
		$edit = $d->addEditDate('born', LOC_LABEL_BORN, '');
		$edit->noMandatory();

		$d = $dr->addRichDiv('divSport');
		$t = $d->addP('', '', 'bn-title-3');
		$t->addBalise('span', '',  LOC_TITLE_SPORT);
		$edit = $d->addEdit('license',    LOC_LABEL_LICENSE, '', 10);
		$edit->noMandatory();

		$catage = Oplayer::getLovCatage();
		$lst = $d->addSelect('catage',  LOC_LABEL_CATAGE);
		$lst->addOptions($catage, OPLAYER_CATAGE_SEN);

		$numcatage = Array(1=>1, 2, 3, 4 ,5);
		$lst = $lst->addSelect('numcatage',  '');
		$lst->addOptions($numcatage, 1);

		$surclasse = Oplayer::getLovSurclasse();
		$lst = $d->addSelect('surclasse',  LOC_LABEL_SURCLASSE);
		$lst->addOptions($surclasse, reset($surclasse));

		$ranks = $oEvent->getLovRanking();
		$lst = $d->addSelect('levels',  LOC_LABEL_SINGLE);
		$lst->addOptions($ranks, reset($ranks));
		$edit = $lst->addEdit('ranks',    LOC_LABEL_RANK, '999999', 6);
		$edit->noMandatory();
		if ( !Bn::getConfigValue('squash', 'params'))
		{
			$lst = $d->addSelect('leveld',  LOC_LABEL_DOUBLE);
			$lst->addOptions($ranks, reset($ranks));
			$edit = $lst->addEdit('rankd',    LOC_LABEL_RANK, '999999', 6);
			$edit->noMandatory();
			$lst = $d->addSelect('levelm',  LOC_LABEL_MIXED);
			$lst->addOptions($ranks, reset($ranks));
			$edit = $lst->addEdit('rankm',    LOC_LABEL_RANK, '999999', 6);
			$edit->noMandatory();
		}

		// Bouttons
		$d = $form->addDiv('', 'bn-div-btn');
		$d->addCheckbox('newplayer', LOC_LABEL_NEW_PLAYER, 1, true);
		$btn = $d->addButtonCancel('btnCancelAccount', LOC_BTN_CANCEL, BCAPTAIN_PAGE_PLAYERS, 'targetBody');
		$btn->addMetaData('teamId', $teamId);
		$d->addButtonValid('', LOC_BTN_UPDATE);

		// Envoi au navigateur
		$body->display();
		return false;
	}

	/**
	 * Enregistrement d'un joueur
	 */
	public function newPlayer()
	{
		$fedeId = -1;
		// Recherche du membre dans la base federale a partir du numero de licence
		$license = Bn::getValue('license');
		$oPlayer = new Oplayer(-1, -1, $license);
		$fedeId = $oPlayer->getVal('poonaid', -1);

		if ($fedeId == -1)
		{
			$oPlayer->setVal('memberid', Bn::getValue('memberId', -3));
			$oPlayer->setVal('firstname', Bn::getValue('firstname'));
			$oPlayer->setVal('familyname', Bn::getValue('famname'));
			$oPlayer->setVal('gender', Bn::getValue('valgender', OMEMBER_GENDER_MALE));
			$oPlayer->setVal('born', Bn::getValue('born'));
			$oPlayer->setVal('license', Bn::getValue('license'));

			$oPlayer->setVal('levelsid', Bn::getValue('levels'));
			$rank = Bn::getValue('ranks');
			if ( empty($rank) ) $rank = 999999;
			$oPlayer->setVal('ranks', $rank);
			if ( !Bn::getConfigValue('squash', 'params'))
			{
				$oPlayer->setVal('leveldid', Bn::getValue('leveld'));
				$oPlayer->setVal('levelmid', Bn::getValue('levelm'));
				$rank = Bn::getValue('rankd');
				if (empty( $rank) ) $rank = 999999;
				$oPlayer->setVal('rankd', $rank);
				$ranks = Bn::getValue('rankm');
				if (empty( $rank) ) $rank = 999999;
				$oPlayer->setVal('rankm', $rank);
			}
			else
			{
				$oPlayer->setVal('leveldid', Bn::getValue('levels'));
				$oPlayer->setVal('levelmid', Bn::getValue('levels'));
				$oPlayer->setVal('rankd', $rank);
				$oPlayer->setVal('rankm', $rank);
			}
			$oPlayer->setVal('catage', Bn::getValue('catage'));
			$oPlayer->setVal('numcatage', Bn::getValue('numcatage'));
			$oPlayer->setVal('surclasse', Bn::getValue('surclasse'));
			$oPlayer->setVal('date',  date('Y-m-d'));
		}
		$oPlayer->setVal('eventid',  Bn::getValue('event_id'));
		$teamId = Bn::getValue('teamId');
		$oTeam = new Oteam($teamId);
		$oPlayer->setVal('teamid',  $teamId);
		$oPlayer->setVal('accountid',  $oTeam->getVal('accountid'));
		$oPlayer->save();
		if (Bn::getValue('newplayer', -1) != -1) return BCAPTAIN_PAGE_PLAYER;
		else return BCAPTAIN_PAGE_REGIS;
	}

	/**
	 * Recherche de joueur pour l'autocompletion a partir du nom
	 */
	public function searchPlayer()
	{
		// Donnees
		$eventId = Bn::getValue('event_id', -1);
		$teamId  = Bn::getValue('teamId', -1);
		$oEvent  = new Oevent($eventId);
		$oExtra  = new Oeventextra($eventId);

		$oExt = Oexternal::factory();

		// Critere de recherche : nom ou licence
		$criteria['licname'] = Bn::getValue('term');

		// Joueur du club uniquement
		if( $oExtra->getVal('multiassoteam') == NO )
		{
			$oTeam = new Oteam($teamId);
			$oAsso = $oTeam->getAssociation();
			$criteria['instanceId'] = $oAsso->getVal('fedeid');
		}
		// Filtre sur le genre
		$catage = $oExtra->getVal('licensecatage');
		$catages = explode(';', $catage);
		$male = array_search(OMEMBER_MALE, $catages);
		$female = array_search(OMEMBER_FEMALE, $catages);
		if ($male !== false && $female === false) $criteria['gender'] = OMEMBER_MALE;
		if ($male === false && $female !== false) $criteria['gender'] = OMEMBER_FEMALE;

		// Filtre sur la categorie
		unset($catages[$male]);
		unset($catages[$female]);
		$criteria['catage'] = implode(';', $catages);

		// Filtre sur le type de licence
		$criteria['licensetype'] = $oExtra->getVal('licensetype');
		// Recherche des joueurs
		$players = $oExt->searchPlayers($criteria);

		$rows = array();
		foreach($players as $player)
		{
			$fields = explode(';', $player);
			$name['famname']   = $player['familyname'];
			$name['firstname'] = $player['firstname'];
			$name['license']   = $player['license'];
			$name['gender']    = $player['gender'];
			$name['labgender'] = constant('LABEL_'.$player['gender']);
			$name['rank']      = $player['rank'];
			$name['level'] = $player['level'];
			$name['born']  = $player['born'];;
			$name['catage']  = $player['catage'];;
			$name['labcatage']  = $player['labcatage'];;
			$name['label']     = $name['famname'] . ' ' . $name['firstname'];
			$name['label'] .= ' - ' . $name['license'] ;
			$name['label'] .= ' - ' . $name['level'] ;
			$name['label'] .= ' - ' . $name['rank'] ;
			$rows[] = $name;
		}
		echo Bn::toJson($rows);
		return false;
	}

	/**
	 * Page pour modification d'un joueur (email)
	 *
	 */
	public function pagePlayer()
	{
		$regiId = Bn::getValue('regiId', -1);

		// Constuction de la page
		$body = new Body();

		// Titre
		$oPlayer = new Oplayer($regiId);
		$oMember = $oPlayer->getMember();
		//$t = $body->addP('tltCivil', $oTeam->getVal('name'), 'bn-title-4');

		// Champs de saisis
		$form = $body->addForm('frmPlayer', BCAPTAIN_ADD_PLAYER, 'targetDlg');
		$form->getForm()->addMetadata('success', "addPlayer");
		$form->getForm()->addMetadata('dataType', "'json'");
		$form->addHidden('teamId', $oPlayer->getVal('teamid'));
		$form->addHidden('gender', $oMember->getVal('sexe'));

		$form->addP('', LOC_MSG_EMAIL_ONLY, 'bn-p-info');
		$dl=$form->addDiv('', 'bn-div-left');
		$edit = $dl->addEdit('famname', LOC_LABEL_FAMNAME, $oMember->getVal('secondname'), 30);
		$edit->noModified();
		$edit->noMandatory();
		$edit = $dl->addEdit('firstname', LOC_LABEL_FIRSTNAME, $oMember->getVal('firstname'), 30);
		$edit->noModified();
		$edit->noMandatory();
		$edit = $dl->addEdit('license', LOC_LABEL_LICENSE, $oMember->getVal('licence'), 10);
		$edit->noModified();
		$edit->noMandatory();
		$dl=$form->addDiv('', 'bn-div-left');
		$edit = $dl->addEdit('born', LOC_LABEL_BORN, BN::date($oMember->getVal('born'), 'd-m-Y'));
		$edit->noModified();
		$edit->noMandatory();
		$edit = $dl->addEdit('ranking', LOC_LABEL_LEVEL, $oPlayer->getVal('level'), 20);
		$edit->noModified();
		$edit->noMandatory();
		$edit = $dl->addEdit('rank', LOC_LABEL_RANK, $oPlayer->getVal('rank'), 20);
		$edit->noModified();
		$edit->noMandatory();
		$dl=$form->addDiv('', 'bn-div-clear');
		$edit = $dl->addEdit('email', LOC_LABEL_EMAIL, $oMember->getVal('email'), 250);
		$edit->noMandatory();

		// Boutons
		$d = $form->addDiv('', 'bn-div-btn');
		$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$d->addButtonValid('bntValid', LOC_BTN_UPDATE);

		$body->display();
		return false;
	}

	/**
	 * Page pour enregistrement d'un joueur
	 *
	 */
	public function pageAddPlayer()
	{
		$teamId = Bn::getValue('teamId', -1);

		// Constuction de la page
		$body = new Body();

		// Titre
		$oTeam = new Oteam($teamId);
		$t = $body->addP('tltCivil', $oTeam->getVal('name'), 'bn-title-4');

		// Champs de saisis
		$form = $body->addForm('frmPlayer', BCAPTAIN_ADD_PLAYER, 'targetDlg');
		$form->getForm()->addMetadata('success', "addPlayer");
		$form->getForm()->addMetadata('dataType', "'json'");
		$form->addHidden('teamId', $teamId);
		$form->addHidden('gender', '');
		$form->addHidden('catage', '');
		$form->addP('', LOC_MSG_ADD_PLAYER, 'bn-p-info');
		$edit = $form->addEdit('player', LOC_LABEL_SEARCH, '', 40);
		$edit->autoComplete(BCAPTAIN_SEARCH_PLAYER, 'selectPlayer');
		$edit->getInput()->addMetaData('teamId', $teamId);
		$edit->noMandatory();

		$dd = $form->addDiv('divMsg');
		$dd->addError(LOC_MSG_PLAYER_TEAM);
		$dd->setAttribute('style', 'display:none;');
		$dl=$form->addDiv('', 'bn-div-left');
		$edit = $dl->addEdit('labgender', LOC_LABEL_GENDER, '');
		$edit->noModified();
		$edit->noMandatory();
		$edit = $dl->addEdit('famname', LOC_LABEL_FAMNAME, '', 30);
		$edit->noModified();
		$edit->noMandatory();
		$edit = $dl->addEdit('firstname', LOC_LABEL_FIRSTNAME, '', 30);
		$edit->noModified();
		$edit->noMandatory();
		$edit = $dl->addEdit('born', LOC_LABEL_BORN, '');
		$edit->noModified();
		$edit->noMandatory();

		$dl=$form->addDiv('', 'bn-div-left');
		$edit = $dl->addEdit('license', LOC_LABEL_LICENSE, '');
		$edit->noModified();
		$edit->noMandatory();
		$edit = $dl->addEdit('ranking', LOC_LABEL_LEVEL, '');
		$edit->noModified();
		$edit->noMandatory();
		$edit = $dl->addEdit('rank', LOC_LABEL_RANK, '');
		$edit->noModified();
		$edit->noMandatory();
		$edit = $dl->addEdit('labcatage', LOC_LABEL_CATAGE, '');
		$edit->noModified();
		$edit->noMandatory();

		$dl=$form->addDiv('', 'bn-div-clear');
		$edit = $dl->addEdit('email', LOC_LABEL_EMAIL, '', 250);
		$edit->noMandatory();

		$form->addBreak();
		$form->addCheckbox('onemore', LOC_LABEL_ONE_MORE, 1, true);

		// Boutons
		$d = $form->addDiv('', 'bn-div-btn');
		$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$d->addButtonValid('bntValid', LOC_BTN_UPDATE);

		$body->display();
		return false;
	}

	/**
	 * Enregistrement du joueur
	 *
	 */
	public function addPlayer()
	{
		$eventId = Bn::getValue('event_id');
		$teamId  = Bn::getValue('teamId');
		$oTeam = new Oteam($teamId);
		$oExt = Oexternal::factory();
		$forbid = false;

		// Recherche du joueur dans la base badnet
		$license = Bn::getValue('license');
		if (!empty($license))
		{
			$memberIds = Omember::search(array('license'=>"'$license'"));
			$memberId = reset($memberIds);
			$email = Bn::getvalue('email');

			// Creation du joueur si besoin
			if ( empty($memberId) )
			{
				$member = $oExt->getMember($license);
				$oMember = new Omember();
				$oMember->setVal('firstname', $member['firstname']);
				$oMember->setVal('secondname', $member['familyname']);
				$oMember->setVal('sexe', $member['gender']);
				$oMember->setVal('born', $member['born']);
				$oMember->setVal('licence', $member['license']);
				$oMember->setVal('fedeid', $member['fedeid']);
				if ( !empty($email) ) $oMember->setVal('email', $email);
				$memberId = $oMember->save();
			}
			else
			{
				$oMember = new Omember($memberId);
				$oMember->setVal('email', $email);
				$memberId = $oMember->save();
			}

			// Verifier si le joueur peut s'inscrire dans plusieurs equipes
			$oExtra = new Oeventextra($eventId);
			$forbid = false;
			if ( $oExtra->getVal('multiteamplayer') == NO)
			{
				$teams = $oMember->getRegis($eventId);
				if (!empty($teams))
				{
					$team = reset($teams);
					if (count($teams) > 1 || $team['regi_teamid'] != $teamId) $forbid = true;
				}
			}

			// Inscription du joueur
			if ( !$forbid )
			{
				$oRegi = new Oregi();
				$famname = $oMember->getVal('secondname');
				$firstname = $oMember->getVal('firstname');
				$oRegi->setVal('memberid',  $memberId);
				$oRegi->setVal('longname',  $famname . ' ' . $firstname);
				$oRegi->setVal('shortname', $famname . ' ' . $firstname[0] . '.');
				$oRegi->setVal('type',      OREGI_TYPE_PLAYER);
				$oRegi->setVal('teamid',    $teamId);
				$oRegi->setVal('date',      date('Y-m-d H:M'));
				$oRegi->setVal('eventid',   $eventId);
				$oRegi->setVal('accountid', $oTeam->getVal('accountid'));
				$id = $oRegi->register();

				// Mettre a jour les infos sportives et administrative
				$oRegi->refresh();
			}
		}
		// Erreur d'insciption
		if($forbid) $res['act'] = -1;
		// Nouvelle inscriptoin apres enregistrement
		else if (Bn::getValue('onemore')) $res['act'] = 1;
		// Fin apres enregistrement
		else $res['act'] = 0;
		echo Bn::toJson($res);
		return false;
	}

	/**
	 * Envoi la liste des rencontres d'une equipe
	 * @todo voir si l'equipe est dans plus d'un groupe
	 */
	public function fillTies()
	{
		include_once 'Object/Locale/' . Bn::getLocale() . '/Otie.inc';
		$teamId = Bn::getValue('teamId');
		$eventId = Bn::getValue('event_id');
		$roundId = Bn::getValue('roundId');

		$oEvent = new Oevent($eventId);
		$ranks = $oEvent->getLovRanking();

		$q = new Bn_query('t2t, ties');
		$q->setFields('tie_id, tie_schedule, tie_place, t2t_result, t2t_scorew, t2t_scorel, tie_validid, tie_entryid, tie_controlid');
		$q->addWhere('t2t_tieid = tie_id');
		$q->addWhere('t2t_teamid = ' . $teamId);
		$q->addWhere('tie_roundid = ' . $roundId);

		$ties = $q->getGridRows(0, true);
		$param  = $q->getGridParam();
		$i = $param->first;
		$gvRows = new GvRows($q);
		$q->setTables('t2t, teams');
		$q->setFields('team_id, team_name, team_captainid');
		$now = date('U');
		foreach ($ties as $tie)
		{
			$q->setWhere('t2t_tieid=' . $tie['tie_id']);
			$q->addWhere('t2t_teamid = team_id');
			$q->addWhere('team_id !=' . $teamId);
			$team = $q->getRow();

			$row[0] = $i++;
			$row[1] = Bn::date($tie['tie_schedule'], 'd-m-Y');
			$row[2] = $tie['tie_place'];

			$dateTie = Bn::date($tie['tie_schedule'], 'U');
			$bal= new Bn_balise();
			if ( !empty($team['team_captainid']))
			{
				$lnk = $bal->addLink('', BCAPTAIN_PAGE_VCARD . '&teamId=' . $team['team_id'], null, 'targetDlg');
				$lnk->addimage('', 'vcard.png', 'vcard');
				$lnk->completeAttribute('class', 'bn-dlg');
				$lnk = $bal->addLink('', BCAPTAIN_PAGE_EMAIL . '&tieId=' . $tie['tie_id'], null, 'targetDlg');
				$lnk->addimage('', 'email.png', 'email');
				$lnk->completeAttribute('class', 'bn-dlg');
				$lnk->addMetaData('title', '"' . LOC_TITLE_EMAIL . '"');
			}
			//	$lnk = $bal->addLink('', BCAPTAIN_PAGE_GROUP . '&tieId=' . $tie['tie_id'], null, 'targetDlg');
			//	$lnk->addimage('', 'group.png', 'players');
			//	$lnk->completeAttribute('class', 'bn-dlg');
			$bal->addContent($team['team_name']);
			$row[3] = $bal->toHtml();
			unset($bal);


			//if ( $tie['tie_validid'] > 0 || $tie['tie_controlid'] > 0 || $now > $dateMax)
			//$tie['tie_entryid'] > 0
			$bal= new Bn_balise();
			if ($tie['tie_controlid'] > 0) $bal->addimage('', 'lv.png', 'vcard');
			else if ($tie['tie_validid'] > 0) $bal->addimage('', 'ls.png', 'vcard');
			else if ($tie['tie_entryid'] > 0) $bal->addimage('', 'lm.png', 'vcard');
			else if($tie['t2t_result'] != OTIE_RESULT_NOTPLAY) $bal->addimage('', 'ls.png', 'vcard');

			$bal->addContent(constant('LABEL_'.$tie['t2t_result']));
			$row[4] = $bal->toHtml();;
			unset($bal);

			$row[5] = $tie['t2t_scorew'] . '-' . $tie['t2t_scorel'];


			$bal= new Bn_balise();
			// Rencontre valide ou controle ou date passe: consultation uniquement
			$dateMax = $oEvent->getDelayCaptain($tie['tie_schedule']);
			if ( $tie['tie_validid'] > 0 || $tie['tie_controlid'] > 0 || $now > $dateMax)
			{
				$url = BCAPTAIN_PAGE_RESULT . '&tieId=' . $tie['tie_id'] . '&teamId=' . $teamId;
				$lnk = $bal->addLink('', $url, null,  'targetBody');
				$lnk->addimage('', 'Bn/Img/magnifier.png', 'edit');
				$row[6] = $bal->toHtml();
			}
			else
			{
				// Rencontre saisie :modification si capitaine adverse, sinon consultation
				if ( $tie['tie_entryid'] > 0 )
				{
					$url = BCAPTAIN_PAGE_RESULT . '&tieId=' . $tie['tie_id'] . '&teamId=' . $teamId;
					$lnk = $bal->addLink('', $url, null,  'targetBody');
					if( $tie['tie_entryid'] == Bn::getValue('user_id'))
					{
						$lnk->addimage('', 'Bn/Img/magnifier.png', 'edit');
					}
					else
					{
						$lnk->addimage('', 'Bn/Img/edit.png', 'consult');
					}
					$row[6] = $bal->toHtml();
				}
				// Rencontre non saisie
				else
				{
					if ($now > $dateTie)
					{
						$url = BCAPTAIN_PAGE_TIE . '&tieId=' . $tie['tie_id'] . '&teamId=' . $teamId;
						$lnk = $bal->addLink('', $url, null,  'targetBody');
						$lnk->addimage('', 'Bn/Img/edit.png', 'edit');
						$row[6] = $bal->toHtml();
					}
					else $row[6] = '';
				}
			}
			unset($bal);
			$gvRows->addRow($row);
		}
		$gvRows->display();
		return false;
	}

	/**
	 * Envoi la liste des joueurs d'une equipe
	 */
	public function fillRegis()
	{
		include_once 'Object/Locale/' . Bn::getLocale() . '/Oplayer.inc';
		include_once 'Object/Locale/' . Bn::getLocale() . '/Omember.inc';
		$teamId = Bn::getValue('teamId');
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$ranks = $oEvent->getLovRanking();

		$q = new Bn_query('members, registration');
		$q->setFields('regi_id, regi_date, mber_sexe, regi_longname, mber_licence, regi_catage, regi_surclasse');
		$q->leftJoin('ranks s', 's.rank_regiid=regi_id AND s.rank_discipline=' . OMATCH_DISCIPLINE_SINGLE);
		$q->addField('s.rank_rankdefid', 'ids');
		$q->addField('s.rank_rank', 'ranks');
		$q->leftJoin('ranks d', 'd.rank_regiid=regi_id AND d.rank_discipline=' . OMATCH_DISCIPLINE_DOUBLE);
		$q->addField('d.rank_rankdefid', 'idd');
		$q->addField('d.rank_rank', 'rankd');
		$q->leftJoin('ranks m', 'm.rank_regiid=regi_id AND m.rank_discipline=' . OMATCH_DISCIPLINE_MIXED);
		$q->addField('m.rank_rankdefid', 'idm');
		$q->addField('m.rank_rank', 'rankm');
		$q->addWhere('regi_memberid = mber_id');
		$q->addWhere('regi_teamId = ' . $teamId);
		$q->addWhere('regi_type =' . OREGI_TYPE_PLAYER);
		$q->addWhere('mber_id > 0');

		$players = $q->getGridRows(0, true);
		$param  = $q->getGridParam();
		$i = $param->first;
		$gvRows = new GvRows($q);
		$isSquash = Bn::getConfigValue('squash', 'params');
		foreach ($players as $player)
		{
			$row[0] = $i++;
			$row[1] = Bn::date($player['regi_date'], 'd-m-Y');
			$row[2] = constant('LABEL_'.$player['mber_sexe']);
			$row[3] = $player['regi_longname'];
			$row[4] = $player['mber_licence'];
			$row[5] = constant('LABEL_'.$player['regi_catage']);
			$row[6] = constant('LABEL_'.$player['regi_surclasse']);
			if ($isSquash)	$row[7] = $ranks[$player['ids']];
			else $row[7] = $ranks[$player['ids']] .',' . $ranks[$player['idd']] . ',' . $ranks[$player['idm']];
			$row[8] = $player['ranks'];
			$gvRows->addRow($row);
		}
		$gvRows->display();
		return false;
	}

	/**
	 * Affichage de la page avec la liste des joueurs de chaque equipe du groupe
	 */
	public function pageRegis()
	{
		$eventId = Bn::getValue('event_id');
		$teamId = Bn::getValue('teamId');
		$oEvent = new Oevent($eventId);

		$body = new Body();
		// Commandes generiques
		$div = $body->addDiv('', 'bn-menu-right');
		$lnk = $div->addP()->addLink('', BCAPTAIN_PAGE_CAPTAIN . '&teamId=' . Bn::getValue('teamId'), LOC_LINK_BACK_CAPTAIN, 'targetBody');
		$lnk->addIcon('arrowreturnthick-1-w');

		// Titre
		$t = $body->addP('', $oEvent->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', $oEvent->getVal('date'));

		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '', LOC_TITLE_REGIS_PLAYERS);

		$div = $body->addRichDiv('', 'bn-div-left');
		$div = $div->addRichDiv('divEvent');
		$t = $div->addP('tltCivil', '', 'bn-title-3');
		$t->addBalise('span', '',  LOC_TITLE_EVENT);
		$div->addInfo('name',  LOC_LABEL_NAME,  $oEvent->getVal('name'));
		$div->addInfo('date',  LOC_LABEL_DATE,  $oEvent->getVal('date'));
		$div->addInfo('place', LOC_LABEL_PLACE, $oEvent->getVal('place'));
		$div->addInfo('organizer', LOC_LABEL_ORGANIZER,  $oEvent->getVal('organizer'));
		$div->addInfo('firstday', LOC_LABEL_FIRSTDAY, Bn::date($oEvent->getVal('firstday'), 'd-m-Y'));
		$div->addInfo('lastday', LOC_LABEL_LASTDAY,  Bn::date($oEvent->getVal('lastday'), 'd-m-Y'));

		// Recuperer le groupe de l'equipe
		$oTeam = new Oteam($teamId);
		$roundIds = $oTeam->getRounds();

		// Info pour le capitaine
		$div = $body->addRichDiv('', 'bn-div-left');
		$d = $div->addRichDiv('divP');
		$d->addP('', LOC_P_INFO_TEAM, 'bn-p-info');
		$t = $d->addP('tltCivil', '', 'bn-title-2');
		$t->addBalise('span', '',  $oTeam->getVal('name'));
		$body->addBreak();

		foreach($roundIds as $roundId)
		{
			$oRound = new Oround($roundId);
			$oDiv = $oRound->getDivision();
			$d->addP('', $oDiv->getVal('name') . ' - ' . $oRound->getVal('name'), 'bn-p-info');
			unset($oDiv);
			// Recuperer toutes les equipes du groupe
			$q = new Bn_query('t2r');
			$q->setFields('t2r_teamid');
			$q->setWhere('t2r_roundid=' . $oRound->getVal('id', -1));
			$teamIds = $q->getCol();

			// Liste des joueurs de chaque equipe
			$i=1;
			foreach ($teamIds as $teamId)
			{
				$oTeam = new Oteam($teamId);
				$url = BCAPTAIN_FILL_REGIS .  '&teamId=' . $teamId;
				$grid = $body->addGridview('gridRegis' . $i++, $url, 50, false);
				$col = $grid->addColumn('#',   '#', false);
				$col->setLook(30, 'left', false);
				$col = $grid->addColumn(LOC_COLUMN_DATE, 'date');
				$col->setLook(70, 'left', false);
				$col = $grid->addColumn(LOC_COLUMN_GENDER, 'gender');
				$col->setLook(60, 'left', false);
				$col = $grid->addColumn(LOC_COLUMN_NAME, 'name');
				$col->setLook(210, 'left', false);
				$col->initSort('asc');
				$col = $grid->addColumn(LOC_COLUMN_LICENSE,  'license');
				$col->setLook(70, 'left', false);
				$col = $grid->addColumn(LOC_COLUMN_CATAGE,  'catage');
				$col->setLook(85, 'left', false);
				$col = $grid->addColumn(LOC_COLUMN_SURCLASSE,  'surclasse');
				$col->setLook(110, 'left', false);
				$col = $grid->addColumn(LOC_COLUMN_LEVEL,  'level');
				$col->setLook(90, 'left', false);
				$col = $grid->addColumn(LOC_COLUMN_RANK,  'rank');
				$col->setLook(80, 'left', false);
				$grid->setLook($oTeam->getVal('name'), 0, "'auto'");
				unset($oTeam);
			}
			unset($oRound);
		}
		$d->addP('', LOC_P_INFO_GROUPS, 'bn-p-info');

		$body->display();
		return false;
	}

	/**
	 * Supression d'un joueur de l'equipe
	 */
	public function delPlayer()
	{
		$regiId = Bn::getValue('regiId');

		// Supprimer les classements
		$q = new Bn_query('ranks');
		$where = 'rank_regiid=' . $regiId;
		$q->addWhere($where);
		$q->deleteRow();

		// Supression des paires
		$q->setTables('pairs');
		$q->addTable('i2p', 'i2p_pairid=pair_id');
		$q->addWhere('i2p_regiid=' . $regiId);
		$q->setFields('pair_id');
		$pairIds = $q->getCol();

		if (!empty($pairIds))
		{
			$q->setTables('pairs');
			$q->deleteRow('pair_id In (' . implode(',', $pairIds) . ')');
		}

		$q->setTables('i2p');
		$q->deleteRow('i2p_regiid=' . $regiId);

		// Supression de l'inscription
		$q->setTables('registration');
		$q->deleteRow('regi_id=' . $regiId);

		// fin
		echo Bn::toJson(1);
		return false;
	}

	/**
	 * Confirmation de supression d'un joueur de l'equipe
	 */
	public function confirmDelPlayer()
	{
		$regiId = Bn::getValue('regiId');
		$oRegi = new Oregi($regiId);
		$body = new Body();
		$form = $body->addForm('frmDelete', BCAPTAIN_DEL_PLAYER, 'targetDlg');
		$form->addHidden('regiId', $regiId);
		$form->addP('', $oRegi->getVal('longname'), 'bn-title-4');
		$form->addP('', LOC_MSG_CONFIRM_DEL_PLAYER, 'bn-p-info');
		$form->getForm()->addMetadata('success', "delPlayer");
		$form->getForm()->addMetadata('dataType', "'json'");

		$d = $form->addDiv('', 'bn-div-btn');
		$b = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$d->addButtonValid('btnValid', LOC_BTN_DELETE);
		$body->display();
		return false;
	}

	/**
	 * Envoi la liste des joueurs
	 */
	public function fillPlayers()
	{
		// Joueur inscrits dans l'equipe
		$teamId  = Bn::getValue('teamId');
		$q = new Bn_query('members');
		$q->addTable('registration', 'regi_memberid=mber_id');
		$q->setFields('regi_id, mber_sexe,mber_secondname, mber_firstname, mber_licence, regi_catage, regi_surclasse');
		$q->leftJoin('ranks s', 's.rank_regiid=regi_id AND s.rank_discipline=' . OMATCH_DISCIPLINE_SINGLE);
		$q->leftJoin('rankdef', 's.rank_rankdefid=rkdf_id');
		$q->addField('rkdf_label');
		$q->addField('s.rank_rank', 'ranks');
		$q->addField('mber_email');
		
		$q->addWhere('regi_teamid='.$teamId);
		$q->addWhere('regi_type='. OREGI_TYPE_PLAYER);
		$q->addWhere('mber_id>0');
		$players = $q->getGridRows(0, true);

		//print_r($q);
		$param  = $q->getGridParam();
		$i = $param->first;
		$gvRows = new GvRows($q);
		foreach ($players as $player)
		{
			$oPlayer = new Oplayer($player['regi_id']);
			$row[0] = $i++;
			$row[1] = constant('LABEL_'.$player['mber_sexe']);
			$bal= new Bn_balise();
			if (!empty($player['mber_email']))
			{
				$lnk = $bal->addLink('', BCAPTAIN_PAGE_EMAIL_PLAYER . '&regiId=' . $player['regi_id'], null, 'targetDlg');
				$lnk->addimage('', 'email.png', 'email');
				$lnk->completeAttribute('class', 'bn-dlg');
				$lnk->addMetaData('title', '"' . LOC_TITLE_EMAIL_PLAYER . '"');
			}
			$bal->addContent($player['mber_secondname']);
			$row[2] = $bal->toHtml();
			unset($bal);
			$row[3] = $player['mber_firstname'];
			$row[4] = $player['mber_licence'];
			$row[5] = $oPlayer->getVal('labcatage');
			$row[6] = $oPlayer->getVal('labsurclasse');
			$row[7] = $oPlayer->getVal('level');
			$row[8] = $oPlayer->getVal('rank');

			$bal= new Bn_balise();
			$lnk = $bal->addLink('', BCAPTAIN_PAGE_PLAYER . '&regiId=' . $player['regi_id'], null,  'targetDlg');
			$lnk->completeAttribute('class', 'bn-dlg');
			$lnk->addimage('', 'edit.png', 'edit');
			$lnk->addMetaData('title', '"' . LOC_TITLE_EDIT_PLAYER . '"');
			$lnk->addMetaData('width',  620);
			$lnk->addMetaData('height', 300);
			if ($oPlayer->getNbMatch() == 0)
			{
				$lnk = $bal->addLink('', BCAPTAIN_CONFIRM_DEL_PLAYER . '&regiId=' . $player['regi_id'], null,  'targetDlg');
				$lnk->addimage('', 'delete.png', 'delete');
				$lnk->completeAttribute('class', 'bn-delete');
				$lnk->addMetaData('width',  350);
				$lnk->addMetaData('height', 200);
			}
			$row[9] = $bal->toHtml();
			unset($bal);
			$gvRows->addRow($row, $player['regi_id']);
		}
		$gvRows->display();
		return false;
	}

	/**
	 * Affichage de la page des joueurs de l'equipe
	 */
	public function pagePlayers()
	{
		require_once 'Object/Omember.inc';
		include_once 'Object/Locale/' . Bn::getLocale() . '/Omember.inc';
		$eventId = Bn::getValue('event_id');
		$teamId  = Bn::getValue('teamId');

		$oEvent = new Oevent($eventId);
		$oExtras = new Oeventextra($eventId);
		$oTeam = new Oteam($teamId);

		$body = new Body();
		// Titre
		$t = $body->addP('', $oEvent->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', LOC_TITLE_TEAM_PLAYERS);

		// Commandes generiques
		$div = $body->addDiv('', 'bn-menu-right');
		$p = $div->addP();
		$lnk = $p->addLink('lnkHome', BCAPTAIN_PAGE_CAPTAIN, LOC_LINK_BACK_CAPTAIN, 'targetBody');
		$lnk->addicon('arrowreturnthick-1-w');
		$lnk->addMetaData('eventId', $eventId);

		//$form->addBreak();
		$form = $body->addForm('frmplayers', BCAPTAIN_REGI_PLAYERS, 'targetBody');
		$form->addHidden('teamId', $teamId);

		// Instructions
		if ($oExtras->getVal('allowaddplayer') == OEVENTEXTRA_DELAY_NEVER)
		{
			$form->addWarning(LOC_MSG_NEVER);
		}
		else
		{
			$allowAdd = true;
			if ($oExtras->getVal('allowaddplayer') == OEVENTEXTRA_DELAY_PARAM)
			{
				// Trouver les rencontres passees non valides
				$q = new bn_query('ties');
				$q->addTable('t2t', 't2t_tieid=tie_id');
				$q->addWhere('t2t_teamid='.$teamId);
				$q->addWhere("tie_schedule < '" . date('Y-m-d') . "'");
				$q->addWhere('tie_validid <= 0');
				$q->addWhere('tie_controlid <= 0');
				$q->setFields('tie_schedule');
				$q->setOrder('tie_schedule');
				$nbTies = $q->getCount();

				// Trouver la prochaine rencontre de l'equipe
				$q = new bn_query('ties');
				$q->addTable('t2t', 't2t_tieid=tie_id');
				$q->addWhere('t2t_teamid='.$teamId);
				$q->addWhere("tie_schedule > '" . date('Y-m-d') . "'");
				$q->addWhere('tie_validid <= 0');
				$q->addWhere('tie_controlid <= 0');
				$q->setFields('tie_schedule');
				$q->setOrder('tie_schedule');
				$nexttie = $q->getFirst();

				// Nombres de jour avant la rencontre
				$nbDays = $oExtras->getVal('delayaddplayer');

				// Ajout de joueur autorise si
				// rencontre valide ou controle
				// ou si date limite non depasse
				if (!empty($nexttie) || $nbTies)
				{
					$dateTie = Bn::date($nexttie['tie_schedule'], 'U');
					$dateMax = $dateTie - $nbDays*24*3600;
					$allowAdd = (date('U') < $dateMax ) && !$nbTies;
				}

				$p = $form->addP('', LOC_MSG_DELAY, 'bn-p-info');
				$p->addMetaContent($nbDays);
			}
			if ($oExtras->getVal('licenseonly') == YES) $form->addP('', LOC_MSG_LICENSE_ONLY, 'bn-p-info');
			if ($oExtras->getVal('multiteamplayer') == YES) $form->addP('', LOC_MSG_MULTITEAM_YES, 'bn-p-info');
			else $form->addP('', LOC_MSG_MULTITEAM_NO, 'bn-p-info');
			if ($oExtras->getVal('multiassoteam') == YES) $form->addP('', LOC_MSG_MULTIASSO_YES, 'bn-p-info');
			else $form->addP('', LOC_MSG_MULTIASSO_NO, 'bn-p-info');

			// Bouton de commande
			if ($allowAdd)
			{
				$dbtn = $form->addDiv('',  'bn-div-line bn-div-auto');
				$btn = $dbtn->addButton('btnNew', LOC_BTN_NEW, BCAPTAIN_PAGE_ADD_PLAYER, 'plus', 'targetDlg');
				$btn->addMetaData('teamId', $teamId);
				$btn->addMetaData('title', '"' . LOC_TITLE_ADD_PLAYER . '"');
				$btn->addMetaData('width',  555);
				$btn->addMetaData('height', 415);
				$btn->completeAttribute('class', 'bn-dlg');
				$dbtn->addBreak();
			}
			else
			{
				if (!empty($nbDays))
				{
					$p = new Bn_Balise('p', '', LOC_MSG_DELAY);
					$p->addMetaContent($nbDays);
					$form->addWarning($p);
				}
			}
		}

		// Liste des joueurs de l'equipe
		$url = BCAPTAIN_FILL_PLAYERS . '&teamId=' . $teamId;
		$grid = $form->addGridview('gridPlayers', $url, 50);
		$col = $grid->addColumn('#',   '#', false);
		$col->setLook(30, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_GENDER, 'gender');
		$col->setLook(60, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_FAMNAME, 'famname');
		$col->setLook(107, 'left', false);
		$col->initSort('asc');
		$col = $grid->addColumn(LOC_COLUMN_FIRSTNAME, 'name');
		$col->setLook(80, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_LICENSE,  'license');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_CATAGE,  'catage');
		$col->setLook(80, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_SURCLASSE,  'surclasse');
		$col->setLook(110, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_LEVEL,  'level');
		$col->setLook(90, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_RANK,  'rank');
		$col->setLook(103, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_ACTION,  'action');
		$col->setLook(50, 'center', false);

		$bal= new Bn_balise();
		$lnk = $bal->addLink('email', BCAPTAIN_PAGE_EMAIL_PLAYERS. '&teamId=' . $teamId, null,  'targetDlg');
		$lnk->addimage('', 'email.png', 'email');
		$lnk->completeAttribute('class', 'bn-dlg');
		$lnk->addMetaData('title', '"' . LOC_TITLE_EMAIL_PLAYERS . '"');
		$lnk->setTooltip('Email -- Contacter tous les joueurs en leur envoyant un email', $body);
		$bal->addContent(LOC_TITLE_PLAYERS);
		$str = $bal->toHtml();
		unset($bal);

		$grid->setLook($str, 0, "'auto'");
		$grid->addOption('multiselect', "'true'");
		$grid->setSelectRow('selectGridMember');

		$body->addJQReady('pagePlayers();');
		$body->display();
		return false;
	}

	/**
	 * Affichage de la page d'accueil d'un capitaine
	 */
	public function pageCaptain()
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

		// Titre
		$t = $body->addP('', $oEvent->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', $oEvent->getVal('date'));

		$div = $body->addRichDiv('', 'bn-div-left');
		$div = $div->addRichDiv('divEvent');
		$t = $div->addP('tltCivil', '', 'bn-title-3');
		$t->addBalise('span', '',  LOC_TITLE_EVENT);
		$div->addInfo('name',  LOC_LABEL_NAME,  $oEvent->getVal('name'));
		$div->addInfo('date',  LOC_LABEL_DATE,  $oEvent->getVal('date'));
		$div->addInfo('place', LOC_LABEL_PLACE, $oEvent->getVal('place'));
		$bal = new Bn_balise();
		$lnk = $bal->addLink('', BCAPTAIN_PAGE_EMAIL_MANAGER, null, 'targetDlg');
		$lnk->addimage('', 'email.png', 'email');
		$lnk->completeAttribute('class', 'bn-dlg');
		$lnk->addMetaData('title', '"' . LOC_TITLE_EMAIL_MANAGER . '"');
		$bal->addContent($oEvent->getVal('organizer'));
		$info = $div->addInfo('organizer', LOC_LABEL_ORGANIZER, $bal);
		$div->addInfo('firstday', LOC_LABEL_FIRSTDAY, Bn::date($oEvent->getVal('firstday'), 'd-m-Y'));
		$div->addInfo('lastday', LOC_LABEL_LASTDAY,  Bn::date($oEvent->getVal('lastday'), 'd-m-Y'));

		$oTeams = $oEvent->getCaptainTeams(Bn::getValue('user_id'));
		$oTeam = reset($oTeams);
		$teamId = Bn::getValue('teamId', $oTeam->getVal('id'));
		$oTeam = $oTeams[$teamId];

		$div = $body->addRichDiv('', 'bn-div-left');
		$d = $div->addRichDiv('divP');
		$d->addP('', LOC_P_INFO_TEAM, 'bn-p-info');
		$t = $d->addP('tltCivil', '', 'bn-title-2');
		$t->addBalise('span', '',  $oTeam->getVal('name'));
		$d->addP('pinfo', LOC_P_INFO_CAPTAIN, 'bn-p-info');
		$d->addP()->addLink('', BCAPTAIN_PAGE_PLAYERS . '&teamId=' . $teamId, LOC_LINK_ADD_PLAYER, 'targetBody');
		$d->addP()->addLink('', BCAPTAIN_PAGE_REGIS . '&teamId=' . $teamId, LOC_LINK_CONSULT_PLAYERS, 'targetBody');
		$d->addP()->addLink('', BCAPTAIN_PAGE_RESULTS . '&teamId=' . $teamId, LOC_LINK_CONSULT_RESULTS, 'targetBody');
		if ( count($oTeams) > 1 )
		{
			foreach($oTeams as $team) $lov[$team->getVal('id', -1)] = $team->getVal('name');
			$lst = $d->addSelect('teamId', LOC_LABEL_TEAM, BCAPTAIN_PAGE_CAPTAIN,'targetBody');
			$lst->addOptions($lov, $teamId, true);
		}

		$body->addBreak();
		$p = $body->addP('', LOC_MSG_CAPTAIN_TIES, 'bn-p-info');
		$p->addMetaContent($oEvent->getVal('delaycaptain'));

		$roundIds = $oTeam->getRounds();
		foreach($roundIds as $roundId)
		{
			$oRound = new Oround($roundId);
			$d = $body->addDiv('', 'bn-div-clear');
			$dl = $d->addDiv('divTiesTeam', 'bn-div-left');
			$oDiv = $oRound->getDivision();
			$roundId = $oRound->getVal('id', -1);
			// Liste des rencontres
			$url = BCAPTAIN_FILL_TIES .  '&teamId=' . $teamId;
			$url .= '&roundId=' . $roundId;
			$grid = $dl->addGridview('gridRegis'.$roundId, $url, 50, false);
			$col = $grid->addColumn('#',   '#', false);
			$col->setLook(30, 'left', false);
			$col = $grid->addColumn(LOC_COLUMN_DATE, 'date', false);
			$col->setLook(70, 'left', false);
			$col->initSort();
			$col = $grid->addColumn(LOC_COLUMN_PLACE, 'place', false);
			$col->setLook(140, 'left', false);
			$col = $grid->addColumn(LOC_COLUMN_VERSUS,  'versus', false);
			$col->setLook(200, 'left', false);
			$col = $grid->addColumn(LOC_COLUMN_RESULT,  'result', false);
			$col->setLook(80, 'left', false);
			$col = $grid->addColumn(LOC_COLUMN_SCORE,  'score', false);
			$col->setLook(50, 'left', false);
			$col = $grid->addColumn(LOC_COLUMN_ACTION,  'action', false);
			$col->setLook(60, 'center', false);

			$bal= new Bn_balise();
			$lnk = $bal->addLink('', BCAPTAIN_PDF_VCARD . '&roundId=' . $roundId);
			$lnk->addimage('', 'vcard.png', 'vcard');
			$lnk->completeAttribute('class', 'bn-popup');
			$lnk = $bal->addLink('', BCAPTAIN_PDF_CALENDAR . '&roundId=' . $roundId. '&teamId=' . $teamId);
			$lnk->addimage('', 'calendar.png', 'email');
			$lnk->completeAttribute('class', 'bn-popup');
			$str = LOC_TITLE_TIES . ' - ' . $oDiv->getVal('name') . ' - ' . $oRound->getVal('name');
			$bal->addContent($str);
			$str = $bal->toHtml();
			unset($bal);

			$grid->setLook($str, 0, "'auto'");
			unset($oDiv);

			// Derniers resultas du groupe
			$dl = $d->addDiv('divResults', 'bn-div-left');
			$dl->addP('', 'Derniers résultats', 'bn-title-3');
			$q = new Bn_query('ties');
			$q->addTable('t2t', 't2t_tieid=tie_id');
			$q->addTable('teams', 't2t_teamid=team_id');
			$q->addWhere('team_eventid=' . $eventId);
			$q->addWhere('t2t_result !=' . OTIE_RESULT_NOTPLAY);
			$q->addWhere('tie_roundid =' . $roundId);
			$q->setOrder('tie_schedule DESC, tie_id');
			$q->setFields('tie_id, team_stamp, t2t_scorew, t2t_scorel, tie_schedule');
			$q->setLimit(1, 30);
			$teams = $q->getRows();

			$team = reset($teams);
			$schedule = -1;
			$id = -1;
			foreach($teams as $team)
			{
				$date = Bn::date($team['tie_schedule'], 'd-m-Y');
				if ($schedule !=  $date)
				{
					$dl->addP('', $date, 'bn-title-4');
					$schedule = $date;
					$ul = $dl->addBalise('ul');
					$ul->setAttribute('class', 'bn-list-2');
				}
				if ($id != $team['tie_id'])
				{
					$str = $team['team_stamp'] . ' - ';
					$id = $team['tie_id'];
				}
				else
				{
					$str .= $team['team_stamp'] . ' : ' . $team['t2t_scorel'] . ' - ' . $team['t2t_scorew'] ;
					$ul->addBalise('li', '', $str);
				}
					
			}
			unset($q);
			unset($oRound);
		}

		// Legende
		$d = $body->addDiv('', 'bn-div-clear');
		$lgd = $d->addBalise('ul', 'ldg');
		$lgd->setAttribute('class', 'bn-lgd');
		$items[] = array('En attente de validation par capitaine', 'lm.png');
		$items[] = array('En attente de controle par gestionnaire', 'ls.png');
		$items[] = array('Résultat validé et controlé', 'lv.png');
		foreach($items as $item)
		{
			$li = $lgd->addBalise('li');
			$li->addImage('', $item[1], $item[0]);
			$li->addBalise('span', '', $item[0]);
		}

		$body->addBreak();
		$body->display();
		return false;
	}

}
?>
