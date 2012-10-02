<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Team.inc';
require_once 'Badnet/Event/Event.inc';
include_once 'Badnetclub/Caccount/Caccount.inc';
require_once 'Object/Oticknet.inc';
include_once 'Badnet/Locale/' . Bn::getLocale() . '/Commun.inc';
include_once 'Badnet/Locale/' . Bn::getLocale() . '/Team.inc';


/**
 * Module de gestion des equipes IC
 */
class Team
{
	// {{{ properties
	public function __construct()
	{
		$userId = Bn::getValue('user_id');
		if (empty($userId) ) return BNETADM_LOGOUT;
		$controller = Bn::getController();
		$controller->addAction(BTEAM_PAGE_TEAMS,         $this, 'pageTeams');
		$controller->addAction(BTEAM_FILL_TEAMS,         $this, 'fillTeams');
		$controller->addAction(BTEAM_PAGE_ADD_TEAM,      $this, 'pageAddTeam');
		$controller->addAction(BTEAM_ADD_TEAM,           $this, 'addTeam');
		$controller->addAction(BTEAM_PAGE_DEL_TEAM,      $this, 'pageDelTeam');
		$controller->addAction(BTEAM_DEL_TEAM,           $this, 'delTeam');
		$controller->addAction(BTEAM_SEARCH_ASSOC,       $this, 'searchAssoc');
		$controller->addAction(BTEAM_PAGE_VCARD,         $this, 'pageVcard');
		$controller->addAction(BTEAM_PDF_VCARD,          $this, 'pdfVcard');
		$controller->addAction(BTEAM_EMAIL_SENDED,       $this, 'emailSended');
		$controller->addAction(BTEAM_PAGE_EMAIL_CAPTAIN, $this, 'pageEmailCaptain');
		$controller->addAction(BTEAM_SEND_EMAIL_CAPTAIN, $this, 'sendEmailCaptain');
		$controller->addAction(BTEAM_PAGE_EMAIL_CAPTAINS, $this, 'pageEmailCaptains');
		$controller->addAction(BTEAM_SEND_EMAIL_CAPTAINS, $this, 'sendEmailCaptains');
		$controller->addAction(BTEAM_PAGE_TEAM,           $this, 'pageTeam');
		$controller->addAction(BTEAM_UPDATE_TEAM,         $this, 'updateTeam');
		$controller->addAction(BTEAM_PAGE_PLAYERS,        $this, 'pagePlayers');
		$controller->addAction(BTEAM_FILL_PLAYERS,        $this, 'fillPlayers');
	}

	//
	public function pagePlayers()
	{
		$eventId = Bn::getValue('event_id');
		$teamId = Bn::getValue('teamId');
		$_SESSION['wbs']['theme'] = 2;
		$_SESSION['wbs']['themeId'] = $eventId;

		$url = $_SERVER['PHP_SELF']
		. '?kpid=' . 'teams'
		. '&kaid=22&kSortrowsTeams=2'
		. '&kdata=' . $teamId;
		header("Location: $url");
		return false;
	}

	/**
	 * Modification d'une equipe
	 *
	 */
	public function updateTeam()
	{
		$teamId = Bn::getValue('teamId');
		$oTeam = new Oteam($teamId);
		$oTeam->setVal('name', Bn::getValue('teamname'));
		$oTeam->setVal('stamp', Bn::getValue('teamstamp'));
		$oTeam->setVal('noc', Bn::getValue('teamnoc'));
		$oTeam->setVal('date', Bn::getValue('teamdate'));
		$oTeam->setVal('assoid', Bn::getValue('assoId'));
		if ( Bn::getValue('delcaptain', -1) > 0)
		{
			$captainId = $oTeam->getVal('captainid');
			$oTeam->setVal('captainid', 0);
			$oTeam->setVal('captain', '');
			$oEvent = new Oevent(Bn::getValue('event_id'));
			$oEvent->deleteRight($captainId);
		}
		$oTeam->save();

		$res['act']=0;
		echo Bn::toJson($res);
		return false;
	}

	/**
	 * Page de modification d'une equipe
	 *
	 */
	public function pageTeam()
	{
		$teamId = Bn::getValue('teamId', -1);
		$oTeam = new Oteam($teamId);

		// Constuction de la page
		$body = new Body();

		// Champs de saisi
		$form = $body->addForm('frmTeam', BTEAM_UPDATE_TEAM, 'targetDlg');
		$form->getForm()->addMetadata('success', "addTeam");
		$form->getForm()->addMetadata('dataType', "'json'");
		$form->addHidden('teamId', $teamId);

		$d=$form->addDiv('', 'bn-left-div');
		$edit = $d->addEdit('teamname', LOC_LABEL_NAME, $oTeam->getVal('name'), 50);
		$edit = $d->addEdit('teamstamp', LOC_LABEL_STAMP, $oTeam->getVal('stamp'), 30);
		$edit = $d->addEdit('teamnoc', LOC_LABEL_NOC, $oTeam->getVal('noc'), 15);
		$edit = $d->addEditDate('teamdate', LOC_LABEL_DATE, Bn::date($oTeam->getVal('date'), 'd-m-Y'));

		$oAsso = $oTeam->getAssociation();
		$oEvent = new Oevent(Bn::getValue('event_id'));
		$lov = $oEvent->getLovAssoc();
		$select = $d->addSelect('assoId', LOC_LABEL_ASSOC);
		$select->addOptions($lov, $oAsso->getVal('id'));

		$d->addCheckbox('delcaptain', LOC_LABEL_DEL_CAPTAIN, 1, false);

		// Boutons
		$d = $form->addDiv('', 'bn-div-btn');
		$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$d->addButtonValid('bntValid', LOC_BTN_UPDATE);

		$body->display();
		return false;

	}


	/**
	 * Supression d'une equipe
	 */
	public function delTeam()
	{
		$teamId = Bn::getValue('teamId');
		$oTeam = new Oteam($teamId);
		$del = $oTeam->delete();

		// fin
		if (!$del) $res['err'] = 1;
		else $res['err'] = 0;
		echo Bn::toJson($res);
		return false;
	}

	/**
	 * Confirmation de supression d'une equipe du tournoi
	 */
	public function pageDelTeam()
	{
		$teamId = Bn::getValue('teamId');
		$oTeam = new Oteam($teamId);

		$body = new Body();
		$form = $body->addForm('frmDelete', BTEAM_DEL_TEAM, 'targetDlg');
		$form->addHidden('teamId', $teamId);
		$form->addP('', $oTeam->getVal('name'), 'bn-title-4');
		$form->addP('', LOC_MSG_CONFIRM_TEAM, 'bn-p-info');
		$form->getForm()->addMetadata('success', "delTeam");
		$form->getForm()->addMetadata('dataType', "'json'");

		$d = $form->addDiv('divMsg', 'bn-div-hide');
		$d->addError('La suppression a échoué. Supprimer les joueurs de l\'équipe puis essayer à nouveau');

		$d = $form->addDiv('', 'bn-div-btn');
		$b = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$d->addButtonValid('btnValid', LOC_BTN_DELETE);
		$body->display();
		return false;
	}

	/**
	 * Ajout d'une nouvelle equipe
	 *
	 */
	public function addTeam()
	{
		$eventId = Bn::getValue('event_id', -1);
		$oEvent = new Oevent($eventId);
		$fedeId  = Bn::getValue('fedeid');
		if ($fedeId < 0)
		{
			$res['act'] = -1;
			echo Bn::toJson($res);
			return false;
		}

		// Verifier s'il reste assez de ticknet pour inscrire une équipe
		$cost = Bn::getConfigValue('team', 'cost');
		$captainCost = Bn::getConfigValue('captain', 'cost');
		if ($cost + $captainCost > 0)
		{
			$oExtra = new Oeventextra($eventId);
			$oAdhe = new Oadhe($oEvent->getVal('ownerid'));
			if ($oExtra->getVal('captain') == YES) $cost += $captainCost;
			$nbTicknets = $oAdhe->getVal('ticknets');
			if ($nbTicknets < $cost)
			{
				return false;
			}
		}

		// Association
		$oAsso = new Oasso(-1, $fedeId);

		// Creation de l'equipe
		$oTeam = new Oteam();
		$oTeam->setVal('eventid', $eventId);
		$oTeam->setVal('name', Bn::getValue('teamname'));
		$oTeam->setVal('stamp', Bn::getValue('teamstamp'));
		$oTeam->setVal('date', Bn::getValue('teamdate'));
		$oTeam->setVal('assoid', $oAsso->getVal('id', -1));
		$oTeam->setVal('noc', 'FRA');
		$oTeam->create();

		// Mettre a jour le porte monnaie
		$str = $oEvent->getVal('name') . ':' . Bn::getValue('teamname') . ' - ' . Bn::getValue('teamstamp');
		if ($cost > 0) $oAdhe->addTicknets(-$cost, 'Inscription équipe ' . $str);

		// Verifier s'il reste assez de ticknet pour inscrire une autre équipe
		$nbTicknets -= $cost;
		if ($nbTicknets < $cost) $res['act'] = 0;

		// Nouvelle inscription apres enregistrement
		else if (Bn::getValue('onemore')) $res['act'] = 1;

		// Fin apres enregistrement
		else $res['act'] = 0;
		echo Bn::toJson($res);
		return false;
	}

	/**
	 * Recherche d'une association
	 *
	 */
	public function searchAssoc()
	{
		$txt = Bn::getValue('term');

		// Recherche des instances poona
		$o = Oexternal::factory();
		$instances = $o->searchInstance($txt);
		foreach($instances as &$instance) $instance['label'] = $instance['name'];
		echo Bn::toJson($instances);
		return false;
	}

	/**
	 * Page pour l'ajout d'une nouvelle equipe
	 *
	 */
	public function pageAddTeam()
	{
		// Constuction de la page
		$body = new Body();

		// Verifier s'il reste assez de ticknet pour inscrire une autre équipe
		$cost = Bn::getConfigValue('team', 'cost');
		$captainCost = Bn::getConfigValue('captain', 'cost');
		if ($cost + $captainCost > 0)
		{
			$eventId = Bn::getValue('event_id', -1);
			$oExtra = new Oeventextra($eventId);
			$oEvent = new Oevent($eventId);
			$oAdhe = new Oadhe($oEvent->getVal('ownerid'));
			if ($oExtra->getVal('captain') == YES) $cost += $captainCost;
			$nbTicknets = $oAdhe->getVal('ticknets');
			if ($nbTicknets < $cost)
			{
				$body->addWarning(LOC_MSG_NO_TICKNETS);
				$d = $body->addDiv('', 'bn-div-btn');
				$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
				return false;
			}
		}

		// Champs de saisis
		$form = $body->addForm('frmTeam', BTEAM_ADD_TEAM, 'targetDlg');
		$form->getForm()->addMetadata('success', "addTeam");
		$form->getForm()->addMetadata('dataType', "'json'");

		$form->addHidden('fedeid', -1);

		$form->addP('', LOC_MSG_ADD_TEAM, 'bn-p-info');
		$d = $form->addDiv('divMsg', 'bn-div-hide');
		$d->addError('Le club n\'existe pas.');

		$edit = $form->addEdit('assoc', LOC_LABEL_SEARCH_ASSO, '', 100);
		$edit->autoComplete(BTEAM_SEARCH_ASSOC, 'selectAsso');
		//<$edit->noMandatory();

		$dl=$form->addDiv('', 'bn-div-left');
		$edit = $dl->addEdit('teamname', LOC_LABEL_TEAM_NAME, '', 50);
		$edit = $dl->addEdit('teamstamp', LOC_LABEL_TEAM_STAMP, '', 30);
		$edit = $dl->addEditDate('teamdate', LOC_LABEL_DATE_REG, date('d-m-Y'));

		$dl=$form->addDiv('', 'bn-div-clear');
		$dl->addCheckbox('onemore', LOC_LABEL_ONE_MORE_ASSO, 1, true);

		// Boutons
		$d = $form->addDiv('', 'bn-div-btn');
		$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$d->addButtonValid('bntValid', LOC_BTN_UPDATE);

		$body->display();
		return false;

	}

	/**
	 * Envoi d'un email a tous les capitaines
	 *
	 * @return false
	 */
	public function sendEmailCaptains()
	{
		// Recherche des destinataire caches: les gestionnaires du tournoi
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$managers = $oEvent->getManagers();
		foreach($managers as $manager)
		{
			$bccs[]=$manager['user_email'];
		}
		$bccs[] = Bn::getConfigValue('mailadmin', 'email');

		// Destinataire : limitations aux capitaines des equipes filtrees
		$drawId = Bn::getValue('division', -1);
		if ($drawId > 0)
		{
			$oDiv = new Odiv($drawId);
			$teamIds = $oDiv->getTeams();
		}
		else $teamIds = $oEvent->getTeams();
		foreach($teamIds as $teamId)
		{
			$oTeam = new Oteam($teamId);
			if ($oTeam->getVal('captainid') > 0)
			{
				$oAdhe = new Oadhe($oTeam->getVal('captainid'));
				$to[] = $oAdhe->getVal('email');
				unset($oAdhe);
			}
			unset($oTeam);
		}

		// Expediteurs
		$from = Bn::getValue('user_email');

		// Envoyer l'email
		$mailer = BN::getMailer();
		$mailer->subject(Bn::getValue('txtObject'));
		$mailer->from('no-reply@badnet.org');
		$mailer->ReplyTo($from);
		$mailer->cc($from);
		$mailer->body(Bn::getValue('txtBody'));
		$mailer->bcc(implode(',', $bccs));
		$mailer->send(implode(',', $to), false);
		if ( $mailer->isError() )
		{
			Bn::setUserMsg($mailer->getMsg());
			return BTEAM_PAGE_EMAIL_CAPTAIN;
		}
		return BTEAM_EMAIL_SENDED;
	}

	/**
	 * Envoi d'un email
	 *
	 * @return false
	 */
	public function sendEmailCaptain()
	{
		// Recherche des destinataire caches: les gestionnaires du tournoi
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$managers = $oEvent->getManagers();
		foreach($managers as $manager)
		{
			$bccs[]=$manager['user_email'];
		}
		$bccs[] = Bn::getConfigValue('mailadmin', 'email');

		// Destinataire et expediteurs
		$teamId = Bn::getValue('teamId');
		$oTeam = new Oteam($teamId);
		$oAdhe = new Oadhe($oTeam->getVal('captainid'));
		$to = $oAdhe->getVal('email');
		$from = Bn::getValue('user_email');

		// Envoyer l'email
		$mailer = BN::getMailer();
		$mailer->subject(Bn::getValue('txtObject'));
		$mailer->from('no-reply@badnet.org');
		$mailer->ReplyTo($from);
		$mailer->cc($from);
		$mailer->body(Bn::getValue('txtBody'));
		$mailer->bcc(implode(',', $bccs));
		$mailer->send($to, false);
		if ( $mailer->isError() )
		{
			Bn::setUserMsg($mailer->getMsg());
			return BTEAM_PAGE_EMAIL_CAPTAIN;
		}
		return BTEAM_EMAIL_SENDED;
	}

	/**
	 * Affichage de la page d'envoi d'un email a tous les capitaines
	 *
	 */
	public function pageEmailCaptains()
	{
		// Preparer les champs de saisie
		$body = new Body();
		$form = $body->addForm('frmEmail', BTEAM_SEND_EMAIL_CAPTAINS, 'targetDlg');

		$form->addHidden('division', Bn::getValue('division', -1));

		$form->addEdit('txtObject', LOC_LABEL_OBJECT, null, 50);
		$form->addArea('txtBody', LOC_LABEL_BODY);

		$div = $form->addDiv('', 'bn-div-btn');
		$div->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$div->addButtonValid('btnSend', LOC_BTN_VALID);
		$body->display();
		return false;
	}

	/**
	 * Affichage de la page d'envoi d'un email a un capitaine
	 *
	 */
	public function pageEmailCaptain()
	{
		$teamId = Bn::getValue('teamId');
		$oTeam = new Oteam($teamId);

		// Preparer les champs de saisie
		$body = new Body();
		$form = $body->addForm('frmEmail', BTEAM_SEND_EMAIL_CAPTAIN, 'targetDlg');
		$form->addHidden('teamId', $teamId);

		$t = $form->addP('', $oTeam->getVal('name'), 'bn-title-4');

		$form->addEdit('txtObject', LOC_LABEL_OBJECT, null, 50);
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
		$body->addP('', $str, 'bn-title-4');

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
	 * Annuaires des capitaines au format pdf
	 *
	 */
	public function pdfVcard()
	{
		include 'Badnet/Locale/' . Bn::getLocale() . '/Commun.inc';
		// Creation du document
		include_once 'Bn/Bnpdf.php';
		$pdf = new Bnpdf();
		$pdf->setAutoPageBreak(true, 30);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);
		$pdf->start();

		//Traiter les  division
		$oEvent = new Oevent(Bn::getValue('event_id'));
		$eventName = $oEvent->getVal('name');
		$divIds = $oEvent->getDivisions();
		foreach($divIds as $divId)
		{
			$oDiv = new Odiv($divId);
			// Nouvelle page
			$pdf->AddPage('P');
			$pdf->SetFont('helvetica', 'B', 16);
			$pdf->SetFillColor(255);
			$pdf->SetTextColor(0);
			$pdf->Cell(0, 0, $eventName, '', 1, 'L');
			$pdf->SetFont('helvetica', 'B', 14);
			$str = LOC_PDF_ANNUAIRE . $oDiv->getVal('name');
			$pdf->Cell(0, 0, $str, '', 1, 'L');
			$pdf->Ln(10);

			//Traiter chaque poule
			$groupIds = $oDiv->getGroups();
			foreach($groupIds as $groupId)
			{
				// Traiter chaque equipe
				$oGroup = new Ogroup($groupId);
				$pdf->SetFont('helvetica', 'B', 12);
				//$pdf->SetFillColor(200);
				//$pdf->SetTextColor(255);
				$str = LOC_PDF_GROUP . $oGroup->getVal('name');
				$pdf->Cell(90, 0, $str, '', 1, 'L', 1);
				$teamIds = $oGroup->getTeams();
				foreach($teamIds as $teamId)
				{
					$oTeam = new Oteam($teamId);
					$str = $oTeam->getVal('name') . ' (' . $oTeam->getVal('stamp') . ')';
					$pdf->SetFont('helvetica', 'B', 10);
					$pdf->SetFillColor(200);
					$pdf->SetTextColor(255);
					$pdf->Cell(0, 0, $str, '', 1, 'L', 1);
					$pdf->SetFont('helvetica', 'B', 9);
					$pdf->SetTextColor(0);
					$captainId = $oTeam->getVal('captainid');
					$pdf->Cell(40, 0, LOC_PDF_CAPTAIN, '', 0, 'L');
					$pdf->SetFont('helvetica', '', 9);
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
				unset($oGroup);
			}
			unset($oDiv);
		}
		$filename = '../Temp/Pdf/test.pdf';
		$pdf->end($filename);
		return false;
	}

	/**
	 * Envoi la liste des equipes avec leur capitaine
	 */
	public function fillTeams()
	{
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);

		$q = new Bn_query('assocs');
		$q->addTable('a2t', 'a2t_assoid=asso_id');
		$q->addTable('teams', 'a2t_teamid=team_id');
		$q->leftJoin('users', 'user_id=team_captainid');
		$q->addWhere('team_eventid=' . $eventId);
		$search = Bn::getValue('search');
		if ( !empty($search) )
		{
			$where = "(asso_name LIKE '%" . $search . "%'";
			$where .= " OR team_name LIKE '%" . $search . "%'";
			$where .= " OR user_name LIKE '%" . $search . "%')";
			$q->addWhere($where);
		}
		$q->setFields('team_id, asso_name, team_name, team_stamp, team_poids, user_name, team_captainid, team_pbl');

		$teams = $q->getGridRows(0, true);
		$param  = $q->getGridParam();
		$i = $param->first;
		$gvRows = new GvRows($q);
		foreach ($teams as $team)
		{
			$teamId = $team['team_id'];
			$row[0] = $i++;
			$b = new Bn_Balise();
			$file = $team['team_pbl'] . '.png';
			$img = $b->addImage('', $file, $team['team_pbl']);
			$img->addLink('', BTEAM_PAGE_PLAYERS . '&teamId=' . $teamId, $team['asso_name']);//, 'targetBody');
			$row[1] = $img->toHtml();
			$row[2] = $team['team_name'];
			$row[3] = $team['team_stamp'];
			$row[4] = sprintf('%.2f', $team['team_poids']);
			
			if ( !empty($team['team_captainid']))
			{
				$bal= new Bn_balise();
				$lnk = $bal->addLink('', BTEAM_PAGE_VCARD . '&teamId=' . $teamId, null, 'targetDlg');
				$lnk->addimage('', 'vcard.png', 'vcard');
				$lnk->completeAttribute('class', 'bn-dlg');
				$lnk->addMetaData('title', '"' . LOC_TITLE_VCARD . '"');
				$lnk->addMetaData('width', 380);
				$lnk->addMetaData('height', 250);

				$lnk = $bal->addLink('', BTEAM_PAGE_EMAIL_CAPTAIN . '&teamId=' . $teamId, null, 'targetDlg');
				$lnk->addimage('', 'email.png', 'email');
				$lnk->completeAttribute('class', 'bn-dlg');
				$lnk->addMetaData('title', '"' . LOC_TITLE_EMAIL_CAPTAIN . '"');

				$bal->addContent($team['user_name']);
				$row[5] = $bal->toHtml();
				unset($bal);
			}
			else
			{
				$row[5] = '';
			}

			$bal= new Bn_balise();
			$lnk = $bal->addLink('', BTEAM_PAGE_TEAM . '&teamId=' . $teamId, null, 'targetDlg');
			$lnk->addimage('', 'edit.png', 'edit');
			$lnk->addMetaData('title', '"' . LOC_TITLE_UPDATE_TEAM . '"');
			$lnk->addMetaData('width', 500);
			$lnk->addMetaData('height', 300);
			$lnk->completeAttribute('class', 'bn-dlg');
				
			$lnk = $bal->addLink('', BTEAM_PAGE_DEL_TEAM . '&teamId=' . $teamId, null, 'targetDlg');
			$lnk->addimage('', 'delete.png', 'del');
			$lnk->completeAttribute('class', 'bn-delete');
			$lnk->addMetaData('width', 330);
			$lnk->addMetaData('height', 230);
			$row[6] = $bal->toHtml();
			unset($bal);
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
		$oEvent = new Oevent($eventId);
		$oExtra = new Oeventextra($eventId);
		$body = new Body();

		// Commandes generiques
		$oEvent->header($body);

		// Titres
		$t = $body->addP('', '', 'bn-title-3');
		$t->addBalise('span', '', LOC_TITLE_TEAMS_MANAGEMENT);
		$body->addP('', LOC_MSG_TEAMS_CAPTAIN, 'bn-p-info');
		
		// Cout d'ajout d'une equipe
		$teamCost = Bn::getConfigValue('team', 'cost');
		$oAdhe = new Oadhe($oEvent->getVal('ownerid'));
		if ($oExtra->getVal('captain') == YES) $teamCost += Bn::getConfigValue('captain', 'cost');
		
		// Verification du porte-monnaie et des options choisies
		if ($teamCost > 0)
		{
			$p = $body->addP('', LOC_MSG_TEAMS_MANAGEMENT, 'bn-p-info');
			$p->addMetaContent($teamCost);
		}
		$nbTicknets = $oAdhe->getVal('ticknets');
		if ($nbTicknets < $teamCost) $body->addWarning(LOC_MSG_NO_TICKNETS);

		// Criteres de filtrage
		$body->addBreak();
		$d = $body->addDiv('', 'bn-div-criteria bn-div-line bn-div-auto');
		$form = $d->addForm('frmSearch', 0, 'targetBody', 'filterTeams');
		$edit = $form->addEdit('search', LOC_LABEL_FIND, '', 20);
		$edit->noMandatory();
		$btn = $form->addButtonValid('btnFilter', LOC_BTN_SEARCH, 'search', 'search');

		if ($nbTicknets < $teamCost)
		{
			$lnk = $form->addLink('btnBuy', CACCOUNT_PAGE_BUY, LOC_LNK_BUY_TICKNETS, 'targetBody');
			$lnk->addIcon('cart');
		}
		else
		{
			$btn = $form->addButton('btnNew', LOC_BTN_NEW_TEAM, BTEAM_PAGE_ADD_TEAM, 'plus', 'targetDlg');
			$btn->addMetaData('title', '"' . LOC_TITLE_ADD_TEAM . '"');
			$btn->addMetaData('width',  620);
			$btn->addMetaData('height', 335);
			$btn->completeAttribute('class', 'bn-dlg');
		}

		// Liste des equipes
		$url = BTEAM_FILL_TEAMS;
		$grid = $body->addGridview('gridTeams', $url, 50);
		$col = $grid->addColumn('#',   '#', false);
		$col->setLook(30, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_CLUB, 'club');
		$col->setLook(220, 'left', false);
		$col->initSort('asc');
		$col = $grid->addColumn(LOC_COLUMN_TEAM, 'team');
		$col->setLook(220, 'left', false);
		$col->initSort('asc');
		$col = $grid->addColumn(LOC_COLUMN_STAMP, 'stamp');
		$col->setLook(100, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_WEIGHT, 'poid');
		$col->setLook(50, 'right', false);
		$col = $grid->addColumn(LOC_COLUMN_CAPTAIN, 'captain');
		$col->setLook(130, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_ACTION, 'action', false);
		$col->setLook(50, 'center', false);

		$bal= new Bn_balise();
		$lnk = $bal->addLink('lnkVcards', BTEAM_PDF_VCARD);
		$lnk->addimage('', 'vcard.png', 'vcard');
		$lnk->completeAttribute('class', 'bn-popup');
		$lnk->setTooltip(LOC_TOOLTIP_VCARD, $body);

		$lnk = $bal->addLink('lnkEmails', BTEAM_PAGE_EMAIL_CAPTAINS, null, 'targetDlg');
		$lnk->addimage('', 'email.png', 'emails');
		$lnk->completeAttribute('class', 'bn-dlg');
		$lnk->setTooltip(LOC_TOOLTIP_EMAILS, $body);
		$lnk->addMetaData('title', '"' . LOC_TITLE_EMAIL_CAPTAINS . '"');

		$bal->addContent(LOC_TITLE_CAPTAINS);
		$str = $bal->toHtml();
		unset($bal);
		$grid->setLook($str, 0, "'auto'");

		$body->display();
		return false;
	}

}
?>
