<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Preference.inc';
require_once 'Badnetadm/Badnetadm.inc';
require_once 'Badnet/Adm/Adm.inc';
require_once 'Badnet/Event/Event.inc';

/**
 * Module de gestion des tournois
 */
class Preference
{
	// {{{ properties
	public function __construct()
	{
		$userId = Bn::getValue('user_id');
		if (empty($userId) ) return BNETADM_LOGOUT;
		$controller = Bn::getController();
		$controller->addAction(BPREF_PAGE_PREFERENCES, $this, 'pagePreferences');
		$controller->addAction(BPREF_PAGE_ADMIN,       $this, 'pageAdmin');
		$controller->addAction(BPREF_UPDATE_ADMIN,     $this, 'updateAdmin');
		$controller->addAction(BPREF_PAGE_SPORTIVE,    $this, 'pageSportive');
		$controller->addAction(BPREF_UPDATE_SPORTIVE,  $this, 'updateSportive');
		$controller->addAction(BPREF_PAGE_CAPTAIN,     $this, 'pageCaptain');
		$controller->addAction(BPREF_UPDATE_CAPTAIN,   $this, 'updateCaptain');
		$controller->addAction(BPREF_PAGE_INLINEIC,    $this, 'pageInlineic');
		$controller->addAction(BPREF_UPDATE_INLINEIC,  $this, 'updateInlineic');
		$controller->addAction(BPREF_PAGE_PRINT,       $this, 'pagePrint');
		$controller->addAction(BPREF_UPDATE_PRINT,     $this, 'updatePrint');
		$controller->addAction(BPREF_VISU_PRINT,       $this, 'visuPrint');
		$controller->addAction(BPREF_DELETE_LOGO,      $this, 'deleteLogo');
		$controller->addAction(BPREF_PAGE_PRIVILEGES,  $this, 'pagePrivileges');
		$controller->addAction(BPREF_PAGE_NEW_PRIVILEGE,    $this, 'pageNewPrivilege');
		$controller->addAction(BPREF_UPDATE_PRIVILEGE,      $this, 'updatePrivilege');
		$controller->addAction(BPREF_PAGE_DELETE_PRIVILEGE, $this, 'pageDeletePrivilege');
		$controller->addAction(BPREF_DELETE_PRIVILEGE,    $this, 'deletePrivilege');
		$controller->addAction(BPREF_FILL_PRIVILEGE,      $this, 'fillPrivilege');
		$controller->addAction(BPREF_SEARCH_USERS,        $this, 'searchUsers');
		$controller->addAction(BPREF_PAGE_PRESENTATION,   $this, 'pagePresentation');
		$controller->addAction(BPREF_UPDATE_PRESENTATION, $this, 'updatePresentation');
		$controller->addAction(BPREF_DELETE_POSTER,       $this, 'deletePoster');
		$controller->addAction(BPREF_PAGE_WEIGHT,         $this, 'pageWeight');

	}

	/**
	 * Charge les regles de calcul du poid des equipes
	 *
	 */
	public function pageWeight()
	{
		$weight = Bn::getValue('weight');
		$fileName = '../Conf/Teams/'. $weight;

		// Chargement du fichier
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->validateOnParse = true;
		$doc->load($fileName);

		$bal = new Bn_Balise();
		$xjoueurs = $doc->getElementsByTagName('joueur');
		foreach($xjoueurs  as $xjoueur)
		{
			$nbHommes = Object::nodeValue($xjoueur, 'homme');
			$nbFemmes = Object::nodeValue($xjoueur, 'femme');
			$nbMixtes = Object::nodeValue($xjoueur, 'mixte');
		}
		if ($nbMixtes) $bal->addP('', $nbMixtes . ' joueurs');
		else
		{
			$p = $bal->addP('', $nbHommes . ' homme(s)' . '; ' . $nbFemmes . ' femme(s)');
		}
		
		$xrules = $doc->getElementsByTagName('rules');
		foreach($xrules  as $xrule)
		{
			$discipline = Object::nodeValue($xrule, 'discipline');
		}
		
		
		$xpoids = $doc->getElementsByTagName('poid');
		$t = $bal->addBalise('table');
		$tr = $t->addBalise('tr');
		$td = $tr->addBalise('td', '', 'Classement');
		$td = $tr->addBalise('td', '', 'Rang min.');
		$td = $tr->addBalise('td', '', 'Rang max.');
		$td = $tr->addBalise('td', '', 'Points');
		foreach($xpoids as $xpoid)
		{
			$tr = $t->addBalise('tr');
			$td = $tr->addBalise('td', '', Object::nodeValue($xpoid, 'classement'));
			$td = $tr->addBalise('td', '', Object::nodeValue($xpoid, 'rang_min'));
			$td = $tr->addBalise('td', '', Object::nodeValue($xpoid, 'rang_max'));
			$td = $tr->addBalise('td', '', $xpoid->getAttribute('points'));
		}
		
		echo $bal->toHtml();
		return false;
	}

	/**
	 * Supression du poster du tournoi
	 *
	 */
	public function deletePoster()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$oEvent->deletePoster();
		return BPREF_PAGE_PRESENTATION;
	}

	/**
	 * Enregistrement des preferences de presentation
	 */
	public function updatePresentation()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$oEventmeta = new Oeventmeta($eventId);
		// Controle de l'autorisation
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

		// Donnees du tournoi
		$oEventmeta->setVal('eventid', $eventId);
		$oEventmeta->setVal('skin', Bn::getValue('skin'));
		$oEventmeta->save();

		$postFiles = $_FILES;
		$value = reset($postFiles);
		$tempFilename = $value['tmp_name'];
		if (!empty($value['tmp_name']) )
		{
			$file = $value['name'];
			$oEvent->addPoster($value['tmp_name'], $file);
		}

		// Message de fin
		$body = new Body();
		$body->addWarning(LOC_LABEL_PREF_REGISTERED);
		$d = $body->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CLOSE);

		return BPREF_PAGE_PRESENTATION;
	}

	/**
	 * Page de modification des preference de presentation
	 */
	public function pagePresentation()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$oEventmeta = new Oeventmeta($eventId);

		// Controle de l'autorisation
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

		// Preparer les champs de saisie
		$body = new Body();

		// Titres
		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '', LOC_ITEM_PRESENTATION);

		// Infos generales
		$form = $body->addForm('frmPresentation', BPREF_UPDATE_PRESENTATION, 'divPreferences_content');
		//$form->getForm()->addMetadata('success', "updated");
		//$form->getForm()->addMetadata('dataType', "'json'");


		$dl = $form->addDiv('', 'bn-div-left');
		$edt = $dl->addEditFile('poster', LOC_LABEL_EVENT_POSTER, '');
		$edt->noMandatory();
		$poster = $oEvent->getVal('poster');
		if (!empty($poster) )
		{
			$poster = '../img/poster/'.$poster;
			$form->addImage('poster', $poster, 'poster', array('height'=>70, 'width'=>155));
		}

		$d = $form->addDiv('', 'bn-div-line bn-div-clear');
		$handle=opendir('../skins');
		while ($file = readdir($handle))
		{
			if ($file != "." && $file != ".." && $file != "CVS")
			$skins[$file] = $file;
		}
		closedir($handle);
		$cbo = $form->addSelect('skin', LOC_LABEL_EVENT_SKIN);
		$cbo->addOptions($skins, $oEventmeta->getVal('skin'));
		$d->addBreak();

		$d = $form->addDiv('', 'bn-div-btn');
		if (!empty($poster) )
		{
			$d->addButton('btnDelposter', LOC_BTN_DELETE_POSTER, BPREF_DELETE_POSTER, 'trash', 'divPreferences_content');
		}
		$d->addButtonValid('btnValid', LOC_BTN_UPDATE);

		// Envoi au navigateur
		$body->display();
		return false;
	}


	public function searchUsers()
	{
		$str = Bn::getValue('term');

		$q = new Bn_query('users');
		$q->addField('user_id', 'id');
		$q->addField('user_login', 'login');
		$q->addField('user_name', 'name');
		$q->addField('user_pseudo', 'pseudo');
		$where = "((user_login LIKE '%"  . $str . "%')"
		. " OR (user_name LIKE '%"  . $str . "%')"
		. " OR (user_pseudo LIKE '%"  . $str . "%'))";
		$q->addWhere($where);
		$q->addWhere("user_type != 'P'" );

		$rows = array();
		$users = $q->getRows();
		foreach($users as $user)
		{
			$user['label'] = $user['login'] . ', ' . $user['name'] .', ' . $user['pseudo'];
			$rows[] = $user;
		}
		echo Bn::toJson($rows);
		return false;
	}

	/**
	 * Page pour l'ajout d'un privilege
	 */
	public function pageNewPrivilege()
	{
		$body = new Body();
		$form = $body->addForm('frmNewprivilege', BPREF_UPDATE_PRIVILEGE, 'targetDlg');
		$form->getForm()->addMetadata('success', "refreshPrivilege");
		$form->getForm()->addMetadata('dataType', "'json'");

		$form->addHidden('userId', '');
		$form->addP('', LOC_MSG_NEW_PRIVILEGE, 'bn-p-info');
		$edt = $form->addEdit('search', LOC_LABEL_USER, '', 100);
		$edt->autoComplete(BPREF_SEARCH_USERS, 'selectUser');

		$rights = Oevent::getLovRight();
		$cbo = $form->addSelect('right', LOC_LABEL_RIGHT);
		$cbo->addOptions($rights, reset($rights));

		$d = $form->addDiv('', 'bn-div-btn');
		$b = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$d->addButtonValid('bntValid', LOC_BTN_VALID);
		$body->display();
		return false;

	}

	/**
	 * Liste des  privileges
	 */
	public function fillPrivilege()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		// Action
		$oEvent = new Oevent($eventId);
		$oEvent->getManagers(null);

		$q = new Bn_query('rights, users');
		$q->setFields('user_id, user_login, user_name, user_pseudo, user_email, rght_status');
		$q->addWhere('rght_themeid=' . $eventId);
		$q->addWhere('rght_theme=' . THEME_EVENT);
		$q->addWhere('rght_userid=user_id');

		$users = $q->getGridRows(0, true);
		$param  = $q->getGridParam();
		$gvRows = new GvRows($q);
		$i = $param->first;

		// Affichage des utilisateurs
		foreach ($users as $user)
		{
			$row[0] = $i++;

			$b = new Bn_Balise();
			$file = strtolower($user['rght_status']) . '.png';
			$img = $b->addImage('', $file, $user['rght_status']);
			$img->addContent($user['user_login']);
			$row[1] = $b->toHtml();
			unset($b);
			$row[2] = $user['user_name'];
			$row[3] = $user['user_pseudo'];

			$bal= new Bn_balise();
			$lnk = $bal->addLink('', BPREF_PAGE_DELETE_PRIVILEGE . '&userId=' . $user['user_id'], null, 'targetDlg');
			$lnk->addMetaData('title', '"' .  $user['user_login'] . ', ' . $user['user_name'] . '"');
			$lnk->addMetaData('width',  300);
			$lnk->addMetaData('height', 200);
			$lnk->addimage('', 'delete.png', 'loop');
			$lnk->completeAttribute('class', 'bn-dlg');

			$row[4] = $bal->toHtml();

			$gvRows->addRow($row, $user['user_id']);
		}
		$gvRows->display();
		return false;
	}


	/**
	 * Suppression des  privileges
	 */
	public function deletePrivilege()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$userId = Bn::getValue('userId');

		// Action
		$oEvent = new Oevent($eventId);
		$oEvent->deleteRight($userId);

		// Fin
		$body = new Body();
		$body->addWarning(LOC_LABEL_PREF_REGISTERED);
		$d = $body->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CLOSE);

		// Envoi au navigateur
		echo Bn::toJson(1);
		return false;
	}

	/**
	 * Confirmation de supression d'un privilege
	 */
	public function pageDeletePrivilege()
	{
		$userId = Bn::getValue('userId');
		$body = new Body();
		$form = $body->addForm('frmDelete', BPREF_DELETE_PRIVILEGE, 'targetDlg');
		$form->addHidden('userId', $userId);
		$form->getForm()->addMetadata('success', "refreshPrivilege");
		$form->getForm()->addMetadata('dataType', "'json'");
		$form->addP('', LOC_MSG_CONFIRM_DEL_PRIVILEGE, 'bn-p-info');

		$d = $form->addDiv('', 'bn-div-btn');
		$b = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$d->addButtonValid('bntValid', LOC_BTN_DELETE);
		$body->display();
		return false;
	}

	/**
	 * Enregistrement des  privileges
	 */
	public function updatePrivilege()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$userId = Bn::getValue('userId');
		$right = Bn::getValue('right');
		$oEvent = new Oevent($eventId);

		// Action
		$oEvent->setRight($userId, $right);

		// Fin
		$body = new Body();
		$body->addWarning(LOC_LABEL_PREF_REGISTERED);
		$d = $body->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CLOSE);

		// Envoi au navigateur
		echo Bn::toJson(1);
		return false;
	}

	/**
	 * Page de modification des privileges
	 */
	public function pagePrivileges()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);

		// Controle de l'autorisation
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

		// Preparer les champs de saisie
		$body = new Body();

		// Titres
		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '', LOC_ITEM_PRIVILEGE);
		$div = $body->addDiv();
		$body->addLgdRight($div);
		$div->addBreak();

		$grid = $body->addGridview('gridUsers', BPREF_FILL_PRIVILEGE, 25);
		$col = $grid->addColumn('#',   '#', false);
		$col->setLook(25, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_LOGIN,  'login', true);
		$col->setLook(150, 'left', false);
		$col->initSort();
		$col = $grid->addColumn(LOC_COLUMN_NAME,  'name', true);
		$col->setLook(180, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_PSEUDO,  'pseudo', true);
		$col->setLook(150, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_ACTION,  'action', false);
		$col->setLook(60, 'center', false);
		$grid->setLook(LOC_TITLE_PRIVILEGE, 0, "'auto'");

		// Liste des utilisateurs avec privilege
		$d = $body->addDiv('', 'bn-div-btn');
		$btn = $d->addButton('btnPriv', LOC_BTN_ADD_PRIVILEGE, BPREF_PAGE_NEW_PRIVILEGE, '', 'targetDlg');
		$btn->completeAttribute('class', 'bn-dlg');
		$btn->addMetaData('title', '"' .  LOC_TITLE_NEW_PRIVILEGE . '"');
		$btn->addMetaData('width', 350);
		$btn->addMetaData('height', 200);

		// Envoi au navigateur
		$body->display();
		return false;
	}


	/**
	 * Supression du logo pdf
	 *
	 */
	public function deleteLogo()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$oEventmeta = new Oeventmeta($eventId);
		$oEventmeta->deleteLogo();
		return BPREF_PAGE_PRINT;
	}

	/**
	 * Affichage d'un document pdf pour appercu
	 *
	 */
	public function visuPrint()
	{
		// Creation du document
		include_once 'Bn/Bnpdf.php';
		$pdf = new Bnpdf();
		$pdf->setAutoPageBreak(false);
		$pdf->setPrintHeader(true);
		$pdf->setPrintFooter(true);
		$pdf->start();
		//$pdf->eventHeader();
		$filename = '../Temp/Pdf/visu_' . '.pdf';
		$pdf->end($filename);
		return false;
	}

	/**
	 * Enregistrement des preferences d'impression
	 */
	public function updatePrint()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$oEventmeta = new Oeventmeta($eventId);
		// Controle de l'autorisation
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

		// Donnees du tournoi
		$oEventmeta->setVal('top', Bn::getValue('top'));
		$oEventmeta->setVal('left', Bn::getValue('left'));
		$oEventmeta->setVal('width', Bn::getValue('width'));
		$oEventmeta->setVal('height', Bn::getValue('height'));
		$oEventmeta->setVal('titlefont', Bn::getValue('titlefont'));
		$oEventmeta->setVal('titlesize', Bn::getValue('titlesize'));
		$oEventmeta->save();

		$postFiles = $_FILES;
		$value = reset($postFiles);
		$tempFilename = $value['tmp_name'];
		if (!empty($value['tmp_name']) )
		{
			$file = $value['name'];
			$oEventmeta->addLogo($value['tmp_name'], $file);
		}

		// Message de fin
		$body = new Body();
		$body->addWarning(LOC_LABEL_PREF_REGISTERED);
		$d = $body->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CLOSE);

		return BPREF_PAGE_PRINT;
	}

	/**
	 * Page de modification des preferences d'impression
	 */
	public function pagePrint()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$oEventmeta = new Oeventmeta($eventId);

		// Controle de l'autorisation
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

		// Preparer les champs de saisie
		$body = new Body();

		// Titres
		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '', LOC_ITEM_PRINT);

		// Infos generales
		$form = $body->addForm('frmPrint', BPREF_UPDATE_PRINT, 'divPreferences_content');
		//$form->getForm()->addMetadata('success', "updated");
		//$form->getForm()->addMetadata('dataType', "'json'");

		$div = $form->addDiv('', 'bn-div-left');
		// Liste des fonts
		$fonts=array(
		   'courier' => 'courier',
		   'helvetica' => 'helvetica',
		   'times' => 'times'
		   );

		   /*
		    $handle=@opendir("fpdf/font");
		    if ($handle)
		    {
		    $masque = "|(.*).z|";
		    while ($file = readdir($handle))
		    {
		    if (preg_match($masque, $file, $match)) $fonts[$match[1]] = $match[1];
		    }
		    closedir($handle);
		    }
		    */
		   $d = $div->addDiv('', 'bn-div-line');
		   $cbo = $d->addSelect('titlefont', LOC_LABEL_EVENT_FONT);
		   $cbo->addOptions($fonts, $oEventmeta->getVal('titlefont'));
		   $edt = $d->addEdit('titlesize', LOC_LABEL_EVENT_SIZE, $oEventmeta->getVal('titlesize'));

		   $d = $div->addDiv('', 'bn-div-clear');
		   $edt = $d->addP('', LOC_LABEL_EVENT_POSITION, 'bn-p-info');

		   $dl = $div->addDiv('', 'bn-div-left');
		   $edt = $dl->addEditFile('logo', LOC_LABEL_EVENT_LOGO, '');
		   $edt->noMandatory();
		   $logo = $oEventmeta->getVal('logo');
		   if (!empty($logo) )
		   {
		   	$logo = '../img/poster/'.$logo;
		   	$div->addImage('logo', $logo, 'logo', array('height'=>70, 'width'=>155));
		   }

		   $d = $div->addDiv('', 'bn-div-line bn-div-clear');
		   $edt = $d->addEdit('top', LOC_LABEL_EVENT_TOP, $oEventmeta->getVal('top'));
		   $edt = $d->addEdit('left', LOC_LABEL_EVENT_LEFT, $oEventmeta->getVal('left'));
		   $d = $div->addDiv('', 'bn-div-line bn-div-clear');
		   $edt = $d->addEdit('width', LOC_LABEL_EVENT_WIDTH, $oEventmeta->getVal('width'));
		   $edt = $d->addEdit('height', LOC_LABEL_EVENT_HEIGHT, $oEventmeta->getVal('height'));
		   $d->addBreak();

		   $d = $form->addDiv('', 'bn-div-btn');
		   if (!empty($logo) )
		   {
		   	$d->addButton('btnDellogo', LOC_BTN_DELETE_LOGO, BPREF_DELETE_LOGO, 'trash', 'divPreferences_content');
		   }
		   $btn = $d->addButton('btnVisu', LOC_BTN_VISU, BPREF_VISU_PRINT, 'document');
		   $btn->completeAttribute('class', 'bn-popup');
		   $d->addButtonValid('btnValid', LOC_BTN_UPDATE);

		   // Envoi au navigateur
		   $body->addJqready('pagePrint();');
		   $body->display();
		   return false;
	}



	/**
	 * Enregistrement du parametre des interclubs
	 */
	public function updateCaptain()
	{
		$eventId = Bn::getValue('event_id');
		$oExtra = new Oeventextra($eventId);

		// Controle de l'autorisation
		$oEvent = new Oevent($eventId);
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

		// Controle des ticknets
		$captain = Bn::getValue('captain', NO);
		if ($captain == YES && $oExtra->getVal('captain', NO)==NO)
		{
			$teams = $oEvent->getTeams();
			$nbTeams = count($teams);
			$captainCost = Bn::getConfigValue('captain', 'cost');
			$cost = $captainCost * $nbTeams;
			if ($cost > 0)
			{
				$oAdhe = new Oadhe(Bn::getValue('user_id'));
				$nbTicknets = $oAdhe->getVal('ticknets');
				if ($nbTicknets >= $cost) $oAdhe->addTicknets(-$cost, $oEvent->getVal('name') ." :" . "Gestion des capitaines pour $nbTeams équipes"); 
				else return BEVENT_PAGE_MANAGER_IC;
			}
		}
		// Donnees du compte
		$oExtra->setVal('eventid', $eventId);
		$oExtra->setVal('captain', $captain);
		$oExtra->setVal('allowaddplayer', Bn::getValue('allowaddplayer', OEVENTEXTRA_DELAY_NEVER));
		$oExtra->setVal('delayaddplayer', Bn::getValue('delayaddplayer', 0));
		$oExtra->setVal('licenseonly', Bn::getValue('licenseonly', NO));
		$licenceType = array();
		$type = Bn::getValue('squashpass', null);
		if (!empty($type)) $licenceType[] = $type;
		$type = Bn::getValue('jeune', null);
		if (!empty($type)) $licenceType[] = $type;
		$type = Bn::getValue('scolaire', null);
		if (!empty($type)) $licenceType[] = $type;
		$type = Bn::getValue('corpo', null);
		if (!empty($type)) $licenceType[] = $type;
		$type = Bn::getValue('federale', null);
		if (!empty($type)) $licenceType[] = $type;
		$oExtra->setVal('licensetype', implode(';', $licenceType));

		$licenceCatage = array();
		$type = Bn::getValue('male', null);
		if (!empty($type)) $licenceCatage[] = $type;
		$type = Bn::getValue('female', null);
		if (!empty($type)) $licenceCatage[] = $type;
		$type = Bn::getValue('vet', null);
		if (!empty($type)) $licenceCatage[] = $type;
		$type = Bn::getValue('sen', null);
		if (!empty($type)) $licenceCatage[] = $type;
		$type = Bn::getValue('u19', null);
		if (!empty($type)) $licenceCatage[] = $type;
		$type = Bn::getValue('u17', null);
		if (!empty($type)) $licenceCatage[] = $type;
		$type = Bn::getValue('u15', null);
		if (!empty($type)) $licenceCatage[] = $type;
		$type = Bn::getValue('u13', null);
		if (!empty($type)) $licenceCatage[] = $type;
		$type = Bn::getValue('u11', null);
		if (!empty($type)) $licenceCatage[] = $type;
		$oExtra->setVal('licensecatage', implode(';', $licenceCatage));
		$oExtra->setVal('multiteamplayer', Bn::getValue('multiteamplayer', NO));
		$oExtra->setVal('multiassoteam', Bn::getValue('multiassoteam', NO));
		$oExtra->setVal('delaycaptain', Bn::getValue('delaycaptain', 0));
		$oExtra->setVal('eventid', Bn::getValue('event_id'));
		$oExtra->save();

		// Message de fin
		$body = new Body();
		$body->addWarning(LOC_LABEL_PREF_REGISTERED);
		$d = $body->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CLOSE);

		// Envoi au navigateur
		$res = array('content' => $body->toHtml(),
					'title' => LOC_ITEM_CAPTAIN);
		echo Bn::toJson($res);
		return false;
	}

	/**
	 * Page de modification des parametres des interclubs
	 */
	public function pageCaptain()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$oExtras = new Oeventextra($eventId);

		// Controle de l'autorisation
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;
			
		// Preparer les champs de saisie
		$body = new Body();

		// Titres
		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '', LOC_ITEM_CAPTAIN);

		// Infos generales
		$p = $body->addP('msgParam', LOC_MSG_PREF_CAPTAIN, 'bn-p-info');
		$captainCost = Bn::getConfigValue('captain', 'cost');
		$p->addMetaContent($captainCost);

		$form = $body->addForm('frmCaptain', BPREF_UPDATE_CAPTAIN, 'targetDlg');
		$form->getForm()->addMetadata('success', "updated");
		$form->getForm()->addMetadata('dataType', "'json'");

		//------- Gestion des capitaines
		$div = $form->addDiv('divCaptain', 'bn-div-left');
		// Verifier s'il a des equipes inscrites
		$teams = $oEvent->getTeams();
		$nbTeams = count($teams);
		$cost = $captainCost * $nbTeams;
		if ($cost>0)
		{
			if ( $oExtras->getVal('captain')==NO )
			{
				$oAdhe = new Oadhe(Bn::getValue('user_id'));
				$nbTicknets = $oAdhe->getVal('ticknets');
				if ($nbTicknets >= $cost) $msg = new Bn_balise('', '', LOC_MSG_ACTIVATION_CAPTAIN);
				else
				{
					$lnk = $div->addP()->addLink('btnBuy', CACCOUNT_PAGE_BUY, LOC_LNK_BUY_TICKNETS, 'targetBody');
					$lnk->addIcon('cart');
					$lnk->addMetaData('nbTicknets', $cost-$nbTicknets);
					$msg = new Bn_balise('', '', LOC_MSG_NOACTIVATION_CAPTAIN);
				}
				$msg->addMetaContent($nbTeams);
				$msg->addMetaContent($nbTicknets);
				$msg->addMetaContent($cost);
				$div->addWarning($msg);
			}
			else
			{
				$msg = new Bn_balise('', '', LOC_MSG_DEACTIVATION_CAPTAIN);
				$msg->addMetaContent($nbTeams);
				$msg->addMetaContent($cost);
				$div->addWarning($msg);
			}
		}
		$chk = $div->addCheckbox('captain', LOC_LABEL_ALLOW_CAPTAIN, YES, $oExtras->getVal('captain')==YES);
		if ($oExtras->getVal('captain')==NO  && $nbTicknets < $cost) $chk->getInput()->setAttribute('disabled');
		$div = $div->addDiv('divCaptain2');
		$div->setAttribute('style', 'margin-left:15px;');
		$div->addP('', LOC_MSG_CAPTAIN, 'bn-p-info');
		$d = $div->addDiv('', 'bn-div-line');
		$d->addRadio('allowaddplayer', 'nerver', LOC_LABEL_NEVER, OEVENTEXTRA_DELAY_NEVER, $oExtras->getVal('allowaddplayer')==OEVENTEXTRA_DELAY_NEVER);
		$d->addRadio('allowaddplayer', 'always', LOC_LABEL_ALWAYS, OEVENTEXTRA_DELAY_ALWAYS, $oExtras->getVal('allowaddplayer')==OEVENTEXTRA_DELAY_ALWAYS);
		$bal = new Bn_balise();
		$slt = $bal->addSelect('delayaddplayer');
		$nbJours = range(0,5);
		$slt->addOptions($nbJours, $oExtras->getVal('delayaddplayer'));
		$slt->addContent(LOC_LABEL_DAYS);
		$d->addRadio('allowaddplayer', 'days', $slt, OEVENTEXTRA_DELAY_PARAM, $oExtras->getVal('allowaddplayer')==OEVENTEXTRA_DELAY_PARAM);
		$d->addBreak();
		$div->addP('', LOC_MSG_BADNETTEAM, 'bn-p-info');

		$d = $div->addDiv('', 'bn-div-clear');
		$d->addCheckbox('licenseonly', LOC_LABEL_ALLOW_LICENSE, YES, $oExtras->getVal('licenseonly')==YES);
		if (Bn::getConfigValue('squash', 'params'))
		{
			$d2 = $d->addDiv('divCaptain2');
			$d2->setAttribute('style', 'margin-left:15px;');
			$d = $d2->addDiv('', 'bn-div-left');
			$licenseType = explode(';', $oExtras->getVal('licensetype'));
			$d->addCheckbox('federale', LOC_LABEL_FEDERALE, 'FEDERALE', in_array('FEDERALE', $licenseType));
			$d->addCheckbox('corpo', LOC_LABEL_CORPO, 'CORPORATIVE', in_array('CORPORATIVE', $licenseType));
			$d = $d2->addDiv('', 'bn-div-left');
			$d->addCheckbox('jeune', LOC_LABEL_JEUNE, 'JEUNE', in_array('JEUNE', $licenseType));
			$d->addCheckbox('scolaire', LOC_LABEL_SCOLAIRE, 'SCOLAIRE', in_array('SCOLAIRE', $licenseType));
			$d = $d2->addDiv('', 'bn-div-left');
			$d->addCheckbox('squashpass', LOC_LABEL_PASS, 'SQUASH PASS', in_array('SQUASH PASS', $licenseType));
		}
		$d = $div->addDiv('', 'bn-div-clear');
		$licenseCatage = explode(';', $oExtras->getVal('licensecatage'));
		$d2 = $d->addDiv('', 'bn-div-left');
		$d2->addCheckbox('male', LOC_LABEL_MALE,     OPLAYER_GENDER_MALE, in_array(OPLAYER_GENDER_MALE, $licenseCatage));
		$d2->addCheckbox('female', LOC_LABEL_FEMALE, OPLAYER_GENDER_FEMALE, in_array(OPLAYER_GENDER_FEMALE, $licenseCatage));
		$d2 = $d->addDiv('', 'bn-div-left');
		$d2->addCheckbox('vet', LOC_LABEL_VETERAN, OPLAYER_CATAGE_VET, in_array(OPLAYER_CATAGE_VET, $licenseCatage));
		$d2->addCheckbox('sen', LOC_LABEL_SENIOR,  OPLAYER_CATAGE_SEN, in_array(OPLAYER_CATAGE_SEN, $licenseCatage));
		$d2 = $d->addDiv('', 'bn-div-left');
		$d2->addCheckbox('u19', LOC_LABEL_U19, OPLAYER_CATAGE_U19, in_array(OPLAYER_CATAGE_U19, $licenseCatage));
		$d2->addCheckbox('u17', LOC_LABEL_U17, OPLAYER_CATAGE_U17, in_array(OPLAYER_CATAGE_U17, $licenseCatage));
		$d2 = $d->addDiv('', 'bn-div-left');
		$d2->addCheckbox('u15', LOC_LABEL_U15, OPLAYER_CATAGE_U15, in_array(OPLAYER_CATAGE_U15, $licenseCatage));
		$d2->addCheckbox('u13', LOC_LABEL_U13, OPLAYER_CATAGE_U13, in_array(OPLAYER_CATAGE_U13, $licenseCatage));
		$d2 = $d->addDiv('', 'bn-div-left');
		$d2->addCheckbox('u11', LOC_LABEL_U11, OPLAYER_CATAGE_U11, in_array(OPLAYER_CATAGE_U11, $licenseCatage));

		$d = $div->addDiv('', 'bn-div-clear');
		$d->addCheckbox('multiteamplayer', LOC_LABEL_MULTITEAMPLAYER, YES, $oExtras->getVal('multiteamplayer')==YES);
		$d = $div->addDiv('', 'bn-div-clear');
		$d->addCheckbox('multiassoteam', LOC_LABEL_MULTIASSOTEAM, YES, $oExtras->getVal('multiassoteam')==YES);
		$d = $div->addDiv('', 'bn-div-clear');
		$d->addEdit('delaycaptain', LOC_LABEL_DELAY_CAPTAIN, $oExtras->getVal('delaycaptain'), 5);

		$d = $form->addDiv('', 'bn-div-btn');
		$d->addButtonValid('', LOC_BTN_UPDATE);

		// Envoi au navigateur
		$body->addJQReady('pageCaptain();');
		$body->display();
		return false;
	}

	/**
	 * Enregistrement des preferences inscription en ligne interclub
	 */
	public function updateInlineic()
	{
		$eventId = Bn::getValue('event_id');
		$oExtra = new Oeventextra($eventId);

		// Controle de l'autorisation
		$oEvent = new Oevent($eventId);
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

		$oExtra->setVal('reginline', Bn::getValue('reginline', NO));
		$oExtra->setVal('paybadnet', Bn::getValue('paybadnet'));
		$oExtra->setVal('feesteam', Bn::getValue('feesteam'));
		$oExtra->setVal('eventid', Bn::getValue('event_id'));
		$oExtra->save();

		// Message de fin
		$body = new Body();
		$body->addWarning(LOC_LABEL_PREF_REGISTERED);
		$d = $body->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CLOSE);

		// Envoi au navigateur
		$res = array('content' => $body->toHtml(),
					'title' => LOC_TITLE_PREF_INLINE);
		echo Bn::toJson($res);
		return false;
	}


	/**
	 * Page de modification des preferences inscription en ligne par equipe
	 */
	public function pageInlineIc()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$oExtras = new Oeventextra($eventId);

		// Controle de l'autorisation
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;
			
		// Preparer les champs de saisie
		$body = new Body();

		// Titres
		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '', LOC_TITLE_PREF_INLINE);

		// Infos generales
		$p = $body->addP('msgParam', LOC_MSG_PREF_INLINE, 'bn-p-info');
		$captainCost = Bn::getConfigValue('captain', 'cost');
		$p->addMetaContent($captainCost);

		$form = $body->addForm('frmInlineic', BPREF_UPDATE_INLINEIC, 'targetDlg');
		$form->getForm()->addMetadata('success', "updated");
		$form->getForm()->addMetadata('dataType', "'json'");

		//------- Inscription en ligne
		$div = $form->addDiv('divReginline', 'bn-div-left');
		$div->addCheckbox('reginline', LOC_LABEL_ALLOW_REGINLINE, YES, $oExtras->getVal('reginline')==YES);
		$div = $div->addDiv('divFees');
		$div->setAttribute('style', 'margin-left:15px;');
		$div->addEdit('feesteam', LOC_LABEL_FEES_TEAM, $oExtras->getVal('feesteam'), 5);

		$rdo = $div->addRadio('paybadnet', 'badnet', LOC_LABEL_BADNET_MANAGE, YES, $oExtras->getVal('paybadnet')==YES);
		$rdo->mandatory();
		$d = $div->addDiv('');
		$d->setAttribute('style', 'margin-left:15px;');
		$d->addP('', LOC_MSG_BADNET_MANAGE, 'bn-p-info');

		$div->addRadio('paybadnet', 'manage', LOC_LABEL_MANAGE, NO, $oExtras->getVal('paybadnet')==NO);
		$d = $div->addDiv('');
		$d->setAttribute('style', 'margin-left:15px;');
		$d->addP('', LOC_MSG_MANAGE, 'bn-p-info');

		$d = $form->addDiv('', 'bn-div-btn');
		$d->addButtonValid('', LOC_BTN_UPDATE);

		// Envoi au navigateur
		$body->addJQReady('pageInlineic();');
		$body->display();
		return false;
	}

	/**
	 * Enregistrement des donnees sportives du tournoi
	 */
	public function updateSportive()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		// Controle de l'autorisation
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

		// Donnees du tournoi
		$oEvent->setVal('scoringsystem', Bn::getValue('scoringsystem'));
		$oEvent->setVal('ranksystem', Bn::getValue('ranksystem'));
		$oEvent->setVal('catage', Bn::getValue('catage'));
		$oEvent->setVal('nature', Bn::getValue('nature'));
		$oEvent->setVal('level', Bn::getValue('level'));
		$type = $oEvent->getVal('type');
		if (!$oEvent->isIc())
		{
			$oEvent->setVal('nbdrawmax', Bn::getValue('nbdrawmax'));
			$oEvent->save();
		}
		else
		{
			$weight = Bn::getValue('teamweight');
			if ($weight != $oEvent->getVal('teamweight'))
			{
				$oEvent->setVal('teamweight', Bn::getValue('teamweight'));
				$oEvent->save();
				$teamIds = $oEvent->getTeams();
				foreach($teamIds as $teamId)
				{
					$oTeam = new Oteam($teamId);
					$points = $oTeam->weight();
					unset($oTeam);
				}
			}
				
		}

		// Message de fin
		$body = new Body();
		$body->addWarning(LOC_LABEL_PREF_REGISTERED);
		$d = $body->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CLOSE);

		// Envoi au navigateur
		$res = array('content' => $body->toHtml(),
					'title' => LOC_ITEM_SPORTIVE);
		echo Bn::toJson($res);
	}

	/**
	 * Page de modification des donnees sportives du tournoi
	 */
	public function pageSportive()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$oExtras = new Oeventextra($eventId);
		$type = $oEvent->getVal('type');

		// Controle de l'autorisation
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;
		// Preparer les champs de saisie
		$body = new Body();

		// Titres
		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '', LOC_ITEM_SPORTIVE);
		if ( $oEvent->isIc() ) $body->addP('', LOC_EVENT_TYPE_IC, 'bn-title-4');
		else  $body->addP('', LOC_EVENT_TYPE_INDIVIDUAL, 'bn-title-4');

		// Formulaire
		$form = $body->addForm('frmSportive', BPREF_UPDATE_SPORTIVE, 'targetDlg');
		$form->getForm()->addMetadata('success', "updated");
		$form->getForm()->addMetadata('dataType', "'json'");

		//------- Zones de saisies
		$div = $form->addDiv('', 'bn-div-line');
		$cbo = $div->addSelect('nature', LOC_LABEL_EVENT_NATURE);
		$types = Oevent::getLovNature($type);
		$cbo->addOptions($types, $oEvent->getVal('nature', OEVENT_NATURE_OTHER) );

		$cbo = $div->addSelect('level', LOC_LABEL_EVENT_LEVEL);
		$levels = Oevent::getLovLevel();
		$cbo->addOptions($levels, $oEvent->getVal('level', OEVENT_LEVEL_REG));

		$div = $form->addDiv('', 'bn-div-line');
		$scorings = Oevent::getLovScoringSystem();
		$select = $div->addSelect('scoringsystem', LOC_LABEL_EVENT_SCORING);
		$select->addOptions($scorings, $oEvent->getVal('scoringsystem'));
			
		$systems = Oevent::getLovRankSystem();
		$select = $div->addSelect('ranksystem', LOC_LABEL_EVENT_RANKSYSTEM);
		$select->addOptions($systems, $oEvent->getVal('ranksystem'));


		$isSquash = Bn::getConfigValue('squash', 'params');
		if ( $isSquash ) 
		{
			$div = $form->addDiv('', 'bn-div-line');
			$catage = $oEvent->getVal('evntcatage');
			$div = $form->addDiv('', 'bn-div-line bn-div-clear');
			$div->addRadio('catage', 'catageadult', 'Adulte', OEVENT_CATAGE_ADULT, $catage==OEVENT_CATAGE_ADULT);
			$div->addRadio('catage', 'catageyoung', 'Jeune', OEVENT_CATAGE_YOUNG, $catage==OEVENT_CATAGE_YOUNG);
		}
		else
		{
			$div->addHidden('catage', OEVENT_CATAGE_BOTH);
			if ( !$oEvent->isIc() ) $div->addEdit('nbdrawmax', LOC_LABEL_EVENT_NBDRAW, $oEvent->getVal('nbdrawmax'));
		}
		
		if ( !$oEvent->isIc() )
		{
			$weight = array('---' => '---') + Oevent::getLovWeights();
			$select = $div->addSelect('teamweight', LOC_LABEL_EVENT_TEAMWEIGHT);
			$select->addOptions($weight, $oEvent->getVal('teamweight'));
			$div->addDiv('divWeight', 'bn-div-clear', BPREF_PAGE_WEIGHT);
		}
		
		$div->addBreak();

		$d = $form->addDiv('', 'bn-div-btn');
		$d->addButtonValid('', LOC_BTN_UPDATE);

		// Envoi au navigateur
		$body->addJqReady('pageSportive();');
		$body->display();
		return false;
	}

	/**
	 * Enregistrement des donnees generales du tournoi
	 */
	public function updateAdmin()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		// Controle de l'autorisation
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

		// Donnees du tournoi
		$oEvent->setVal('name', Bn::getValue('nameevent'));
		$oEvent->setVal('date', Bn::getValue('dateevent'));
		$oEvent->setVal('organizer', Bn::getValue('organizer'));
		$oEvent->setVal('place', Bn::getValue('place'));
		$oEvent->setVal('numauto', Bn::getValue('numauto'));
		$oEvent->setVal('firstday', Bn::getValue('firstday'));
		$oEvent->setVal('lastday', Bn::getValue('lastday'));
		$season = Oseason::getSeason(Bn::getValue('firstday'));
		$oEvent->setVal('season', $season);
		$oEvent->save();		

		$oExtras = new Oeventextra($eventId);
		$oExtras->setVal('regionid', Bn::getValue('regionid'));
		$oExtras->setVal('deptid', Bn::getValue('deptid'));
		$oExtras->save();

		// Message de fin
		// Preparer les champs de saisie
		$body = new Body();
		$body->addWarning(LOC_LABEL_PREF_REGISTERED);
		$d = $body->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CLOSE);

		// Envoi au navigateur
		$res = array('content' => $body->toHtml(),
					'title' => LOC_ITEM_GENERAL);
		echo Bn::toJson($res);
		return false;
	}

	/**
	 * Page de modification des donnees generales du tournoi
	 */
	public function pageAdmin()
	{
		// Donnees
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$oExtras = new Oeventextra($eventId);

		// Controle de l'autorisation
		if ( $oEvent->getVal('ownerid') != Bn::getValue('user_id') && !Oaccount::isLoginAdmin() ) return false;

		// Preparer les champs de saisie
		$body = new Body();

		// Titres
		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '', LOC_ITEM_GENERAL);

		// Infos generales
		$form = $body->addForm('frmAdmin', BPREF_UPDATE_ADMIN, 'targetDlg');
		$form->getForm()->addMetadata('success', "updated");
		$form->getForm()->addMetadata('dataType', "'json'");

		$div = $form->addDiv('', 'bn-div-left');
		$edt = $div->addEdit('nameevent', LOC_LABEL_EVENT_NAME, $oEvent->getVal('name'), 255);
		$edt->tooltip(LOC_TOOLTIP_EVENT_NAME);

		$edt = $div->addEdit('place', LOC_LABEL_EVENT_PLACE, $oEvent->getVal('place'), 25);
		$edt->tooltip(LOC_TOOLTIP_EVENT_PLACE);

		$edt = $div->addEdit('dateevent', LOC_LABEL_EVENT_DATE, $oEvent->getVal('date'), 25);
		$edt->tooltip(LOC_TOOLTIP_EVENT_DATE);

		$edt = $div->addEdit('numauto', LOC_LABEL_EVENT_NUMAUTO, $oEvent->getVal('numauto'), 50);

		$edt = $div->addEdit('organizer', LOC_LABEL_EVENT_ORGANIZER, $oEvent->getVal('organizer'), 75);
		$edt->noMandatory();
		//$edt->tooltip(LOC_TOOLTIP_EVENT_ORGANIZER);

		/* Region */
		$cbo = $div->addSelect('regionid',  LOC_LABEL_EVENT_REGION);
		//$cbo->tooltip(LOC_TOOLTIP_EVENT_REGION);
		$regs = Ogeo::getRegions(-1, LOC_LABEL_SELECT_REGION);
		$cbo->addOptions($regs, $oExtras->getVal('regionid'));

		/* Departement */
		$cbo = $div->addSelect('deptid',  LOC_LABEL_EVENT_DPT);
		//$cbo->tooltip(LOC_TOOLTIP_EVENT_DPT);
		$codeps = Ogeo::getDepts(-1, LOC_LABEL_SELECT_DPT);
		$cbo->addOptions($codeps, $oExtras->getVal('deptid'));

		$d = $div->addDiv('', 'bn-div-line');
		$edt = $d->addEditDate('firstday', LOC_LABEL_EVENT_FIRSTDAY, Bn::date($oEvent->getVal('firstday'), 'd-m-Y'));
		$edt->addOption('onSelect', 'selectFirstDay');
		$edt->addOption('maxDate', 'new Date(' . Bn::date($oEvent->getVal('lastday'), 'Y,m-1,d') .')');
		$edt = $d->addEditDate('lastday', LOC_LABEL_EVENT_LASTDAY, Bn::date($oEvent->getVal('lastday'), 'd-m-Y'));
		$edt->addOption('onSelect', 'selectLastDay');
		$edt->addOption('minDate', 'new Date(' . Bn::date($oEvent->getVal('firstday'), 'Y,m-1,d') .')');

		$d->addBreak();

		$d = $form->addDiv('', 'bn-div-btn');
		$d->addButtonValid('', LOC_BTN_UPDATE);

		// Envoi au navigateur
		$body->display();
		return false;
	}

	/**
	 * Affichage de la page des preferences du tournoi
	 */
	public function pagePreferences()
	{
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);

		$body = new Body();

		$oEvent->header($body);

		$dyn = $body->addDynpref('divPreferences');
		$dyn->addItem(BPREF_PAGE_ADMIN, LOC_ITEM_GENERAL, true);
		$dyn->addItem(BPREF_PAGE_SPORTIVE, LOC_ITEM_SPORTIVE);
		$dyn->addItem(BPREF_PAGE_PRIVILEGES, LOC_ITEM_PRIVILEGE);
		if($oEvent->getVal('type') == OEVENT_TYPE_INDIVIDUAL)
		{
			//$dyn->addItem(BPREF_PAGE_INLINE, LOC_ITEM_INLINE);
		}
		else
		{
			//$dyn->addItem(BPREF_PAGE_CAPTAIN, LOC_ITEM_CAPTAIN);
			//$dyn->addItem(BPREF_PAGE_INLINEIC, LOC_ITEM_INLINE);
		}
		$dyn->addItem(BPREF_PAGE_PRESENTATION, LOC_ITEM_PRESENTATION);
		$dyn->addItem(BPREF_PAGE_PRINT, LOC_ITEM_PRINT);

		$body->addBreak();
		$body->display();
		return false;
	}

}
?>
