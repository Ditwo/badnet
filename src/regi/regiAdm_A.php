<?php
/*****************************************************************************
 !   Module     : Registration
 !   File       : $Source: /cvsroot/aotb/badnet/src/regi/regiAdm_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.9 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/06 07:59:33 $
 ******************************************************************************/
require_once "utils/utpage_A.php";
require_once "baseAdm_A.php";

require_once "mber/mber.inc";
require_once "regi.inc";
require_once "purchase/purchase.inc";

/**
 * Module de gestion des inscriptions : partie administration
 *
 * @author Gerard CANTEGRIL
 * @see to follow
 *
 */

class regiAdm_A
{

	// {{{ properties

	// }}}

	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function regiAdm_A()
	{
		$this->_ut = new utils();
		$this->_dt = new regiAdmBase_A();
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
	function start($page  )
	{
		switch ($page)
		{

			case WBS_ACT_REGI:
				$mode['title'] = 'tPlayers';
				$mode['typeMin'] = WBS_PLAYER;
				$mode['typeMax'] = WBS_PLAYER;
				$mode['select'] ='itPlayers';
				$this->_displayAdmListMember($mode);
				break;

			case KID_SELECT:
				$id = kform::getData();
				$this->_displayFormAdmMember($id);
				break;

				// Display main windows for member
			case KID_EDIT:
				$ids = kform::getInput('rowsTransport', kform::getData());
				$this->_displayFormAccount($ids);
				break;
			case KID_UPDATE:
				$this->_updateAccount();
				break;

			case MBER_ADM_ALL:
				$mode['title'] = 'tMembers';
				$mode['typeMin'] = WBS_PLAYER;
				$mode['typeMax'] = WBS_EXHIBITOR;
				$mode['select'] ='itAllMembers';
				$this->_displayAdmListMember($mode);
				break;
			case MBER_ADM_OFFICIALS:
				$mode['title'] = 'tOfficials';
				$mode['typeMin'] = WBS_REFEREE;
				$mode['typeMax'] = WBS_DELEGUE;
				$mode['select'] ='itOfficials';
				$this->_displayAdmListMember($mode);
				break;
			case MBER_ADM_OTHERS:
				$mode['title'] = 'tOthers';
				$mode['typeMin'] = WBS_VOLUNTEER;
				$mode['typeMax'] = WBS_EXHIBITOR;
				$mode['select'] ='itOthers';
				$this->_displayAdmListMember($mode);
				break;

			case REGI_SELECT_FEES:
				$this->_displaySelectFees();
				break;
			case REGI_REGI_FEES:
				$this->_selectFees();
				break;

			case REGI_CREDITS:
				$id = kform::getData();
				$this->_displayFormCreditMember($id);
				break;

			case WBS_ACT_TRANSPORT:
				$this->_displayFormTransport();
				break;
			case MBER_ARRIVAL:
				$this->_displayFormDeparrs('regi_arrival', 'regi_arrcmt');
				break;
			case MBER_DEPARTURE:
				$this->_displayFormDeparrs('regi_departure', 'regi_depcmt');
				break;

			case REGI_PRINT:
				$regiId = kform::getData();
				$this->_print($regiId);
				break;

			default:
				echo "page $page demand�e depuis regiAdm_A<br>";
				exit();
		}
		exit;
	}
	// }}}


	// {{{ _print()
	/**
	 * List the purchase
	 * @return void
	 */
	function _print($regiId)
	{
		require_once "pdf/pdfbase.php";
		$dt =& $this->_dt;

		$regi = $dt->getRegi($regiId);

		$pdf = new pdfbase();
		$rows['top'] =  $pdf->start('P', $regi['regi_longName']);

		$cmds = $dt->getRegiPurchases($regiId);
		foreach($cmds as $cmd)
		{
			unset ($cmd['cmd_id']);
	  unset ($cmd['regi_id']);
	  $rows[] = $cmd;
		}
		$sums = $dt->getRegiSumPurchases($regiId);
		$rows[] = array('', '', 'Total') + $sums;

		$titres = array ('Date', 'Tournoi', 'Achat', 'Cout', 'Remise', 'Paye', 'Solde');
		$tailles = array (18, 78, 40, 12, 12, 12, 12);
		$styles = array ('B','','','','','','');
		$rows['orientation'] = 'P';
		$pdf->genere_liste($titres, $tailles, $rows, $styles);
		$pdf->end();
		return;
	}
	// }}}


	// {{{ _displayFromTransport()
	/**
	 * Display the page with the list of registered members
	 *
	 * @access private
	 * @param array $type   type of registrations
	 * @return void
	 */
	function _displayFormTransport()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
			
		// Create a new page
		$this->_utPage = new utPage_A('regi', true, 'itTransport');
		$content =& $this->_utPage->getContentDiv();

		// Add a menu for different type
		$kdiv =& $content->addDiv('choix', 'onglet3');
		$items['itEntries']   = array(KAF_UPLOAD, 'regi', WBS_ACT_TRANSPORT);
		$items['itArrivals']   = array(KAF_UPLOAD, 'regi', MBER_ARRIVAL);
		$items['itDepartures'] = array(KAF_UPLOAD, 'regi', MBER_DEPARTURE);
		$kdiv->addMenu("menuType", $items, 'itEntries');
		$kdiv =& $content->addDiv('contType', 'cont3');
		$kdiv =& $kdiv->addForm('frmTransport');

		// List of teams
		$accountId = kform::getInput('selectList', -1);
		$teams = $dt->getTeams();
		$kcombo=& $kdiv->addCombo('selectList', $teams, $teams[$accountId]);
		$acts[1] = array(KAF_UPLOAD, 'regi', WBS_ACT_TRANSPORT);
		$kcombo->setActions($acts);

		// Action sur les elements selectionnes de la liste
		$itsm['itTransport'] = array(KAF_NEWWIN, 'regi', KID_EDIT,
		0, 400, 400);
		$kdiv->addMenu('menuAction', $itsm, -1);
		$kdiv->addDiv('break', 'blkNewPage');

		// Display list of members
		$players = $dt->getTransport($accountId);
		if (isset($players['errMsg']))
		$kdiv->addWng($players['errMsg']);
		else
		{
	  $club = "";
	  foreach($players as $player)
	  {
	  	if($club != $player['team_name'])
	  	{
	 	  $club = $player['team_name'];
	 	  $rows[] = array(KOD_BREAK, "title", $club,
				  'action' => array(KAF_UPLOAD, 'teams', KID_SELECT, $player['team_id']));
	  	}
	  	$rows[] = $player;
	  }
	  $krows =& $kdiv->addRows('rowsTransport', $rows);

	  $sizes[8] = '0+';
	  $krows->setSize($sizes);

	  $img[2] = 'indic';
	  $krows->setLogo($img);
	  
	  $actions[0] = array(KAF_NEWWIN, 'regi', KID_EDIT, 0,
	  400, 400);
	  $actions[2] = array(KAF_UPLOAD, 'regi', KID_SELECT, 0);
	  $krows->setActions($actions);
		}

		//Display the page
		$this->_utPage->display();
		exit;
	}
	// }}}

	// {{{ _selectFees()
	/**
	 * Select item for automatic generation of entrie fees
	 *
	 * @access private
	 * @param  integer $publi  new publication state of the event
	 * @return void
	 */
	function _selectFees()
	{
		$dt = $this->_dt;

		$insc[] = kform::getInput('inscOne', -1);
		$insc[] = kform::getInput('inscTwo', -1);
		$insc[] = kform::getInput('inscThree', -1);
		$regis = kform::getInput('rowsAdmRegis', array());

		if (count($regis))
		$res = $dt->registerFees($insc, $regis);
		else
		$res['errMsg'] = 'msgNeedSelect';
		if (is_array($res))
		$this->_displaySelectFees($res['errMsg']);

		//Close the page
		$page = new utPage('regi');
		$page->close(false);
		exit();
	}
	// }}}


	// {{{ _displaySelectFees()
	/**
	 * Select item for automatic generation of entrie fees
	 *
	 * @access private
	 * @param  integer $publi  new publication state of the event
	 * @return void
	 */
	function _displaySelectFees($err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('regi');
		$content =& $utpage->getPage();
		$kform =& $content->addForm('tSelectFees', 'regi', REGI_REGI_FEES);
		//$form->setTitle('tPubliDraws');

		// Display erro message if any
		if ($err != '')
		$kform->addWng($err);

		$regis = kform::getInput('rowsAdmRegis', array());
		foreach($regis as $regi)
		$kform->addHide('rowsAdmRegis[]', $regi);

		$items = $dt->getAllItems();
		$defaultSelect = reset($items);
		$kform->addCombo('inscOne',  $items, kform::getInput('inscOne', $defaultSelect));
		$kform->addCombo('inscTwo',  $items, kform::getInput('inscTwo', $defaultSelect));
		$kform->addCombo('inscThree', $items, kform::getInput('inscThree', $defaultSelect));
		$elts = array('importFile', 'inscOne', 'inscTwo', 'inscThree');
		$kform->addBlock('blkSelect', $elts);

		// Display command button
		$kform->addBtn('btnRegister', KAF_SUBMIT);
		$kform->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$kform->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormDeparrs()
	/**
	 * Display the page with the list of arrivals
	 *
	 * @access private
	 * @param array $type   type of registrations
	 * @return void
	 */
	function _displayFormDeparrs($data, $cmt)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		// Create a new page
		$utpage = new utPage_A('mber', true, 'itTransport');
		$content =& $utpage->getContentDiv();

		// Add a menu for different type
		$kdiv =& $content->addDiv('choix', 'onglet3');
		$items['itEntries']   = array(KAF_UPLOAD, 'regi', WBS_ACT_TRANSPORT);
		$items['itArrivals']   = array(KAF_UPLOAD, 'regi', MBER_ARRIVAL);
		$items['itDepartures'] = array(KAF_UPLOAD, 'regi', MBER_DEPARTURE);
		if ($data == 'regi_arrival')
		{
	  $kdiv->addMenu("menuType", $items, 'itArrivals');
	  $kdiv =& $content->addDiv('contType', 'cont3');
	  $kdiv->addMsg('tMembers', 'tArrivals');
		}
		else
		{
	  $kdiv->addMenu("menuType", $items, 'itDepartures');
	  $kdiv =& $content->addDiv('contType', 'cont3');
	  $kdiv->addMsg('tMembers', 'tDepartures');
		}

		$form =& $kdiv->addForm('fdeparr');
		// Display list of members
		$sort = kform::getSort("rowsDeparr", 3);
		$players = $dt->getDeparrs($data, $cmt);
		if (isset($players['errMsg']))
		$form->addWng($players['errMsg']);
		else
		{
	  $date='';
	  foreach($players as $player)
	  {
	  	if ($date != $player['date'])
	  	{
	  		$rows[] = array(KOD_BREAK, "title", $player['date']);
	  		$date = $player['date'];
	  	}
	  	$rows[] = $player;
	  }
	  $krows =& $form->addRows('rowsDeparr', $rows);

		if ($data == 'regi_arrival') $sizes[8] = '0+';
		else $sizes[9] = '0+';
	  $krows->setSize($sizes);
	  
	  $img[2] = 'indic';
	  $krows->setLogo($img);
	  
	  $actions[0] = array(KAF_NEWWIN, 'regi', KID_EDIT, 0,
	  400, 400);
	  $actions[2] = array(KAF_UPLOAD, 'regi', KID_SELECT, 0);
	  $actions[3] = array(KAF_UPLOAD, 'teams', KID_SELECT, 'team_id');
	  $krows->setActions($actions);
		}

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormCreditMember()
	/**
	 * Display the page for creating or updating a credit
	 *
	 * @access private
	 * @param array $regiId  registration id of the member
	 * @return void
	 */
	function _displayFormCreditMember($regiId)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$kdiv =& $this->_displayHead($regiId, 'itCredits');

		// Display menu for credits
		for ($i=1; $i<7;$i++) $title[$i] = "rowsCredit$i";

		$form =& $kdiv->addForm('fpurchase');
		$itsm['itNew'] = array(KAF_NEWWIN, 'purchase', PURCHASE_NEW_CREDIT,	$regiId, 300, 250);
		$itsm['itErase'] = array(KAF_NEWWIN, 'purchase', PURCHASE_CONFIRM, 0, 250, 100);
		$form->addMenu('menuLeft', $itsm, -1);

		// Add list of credit of this member
		$sort = kform::getSort("rowsPurchase", 1);
		$purchases = $dt->getCredits($regiId, $sort);
		if (isset($purchases['errMsg']))
		$form->addWng($purchases['errMsg']);
		else
		{
	  $krows =& $form->addRows('rowsPurchase', $purchases);
	  $krows->setTitles($title);
	  $actions[0] = array(KAF_NEWWIN, 'purchase', KID_EDIT,  0, 650,450);
	  //$krows->setActions($actions);
		}

		//Display the page
		$this->_utPage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormAdmMember()
	/**
	 * Display the page for creating or updating a credit
	 *
	 * @access private
	 * @param array $regiId  registration id of the member
	 * @return void
	 */
	function _displayFormAdmMember($regiId)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$kdiv =& $this->_displayHead($regiId, 'itPurchases');

		// Display menu for purchase
		$form =& $kdiv->addForm('fpurchase');
		//$itsm['itFees'] = array(KAF_NEWWIN, 'purchase', PURCHASE_FEES,
		//		     $regiId, 400, 400);
		$itsm['itHotel'] = array(KAF_NEWWIN, 'purchase', PURCHASE_HOTEL, $regiId, 400, 280);
		$itsm['itItems'] = array(KAF_NEWWIN, 'purchase', KID_ADD, $regiId, 400, 400);
		$itsm['itErase'] = array(KAF_NEWWIN, 'purchase', KID_CONFIRM, 0, 250, 150);
		$itsm['itSolder'] = array(KAF_NEWWIN, 'purchase', PURCHASE_SOLDE_MEMBER, $regiId, 100, 100);

		$itsm['itPrint'] = array(KAF_NEWWIN, 'regi', REGI_PRINT, $regiId, 400, 400);
		$form->addMenu('menuAction', $itsm, -1);
		$form->addDiv('break', 'blkNewPage');

		// Add list of credit of this member
		$sort = kform::getSort("rowsPurchase", 2);
		$purchases = $dt->getPurchases($regiId, $sort);
		if (isset($purchases['errMsg'])) $form->addWng($purchases['errMsg']);
		else
		{
	  $krows =& $form->addRows("rowsPurchase", $purchases);
	  $actions[0] = array(KAF_NEWWIN, 'purchase', KID_EDIT,  0, 400,400);
	  $krows->setActions($actions);
	  $sizes[8] = '0+';
	  $krows->setSize($sizes);
		}

		//Display the page
		$this->_utPage->display();
		exit;
	}
	// }}}

	// {{{ _displayAdmListMember()
	/**
	 * Display the page with the list of registered members
	 *
	 * @access private
	 * @param array $type   type of registrations
	 * @return void
	 */
	function _displayAdmListMember($mode)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		// Create a new page
		$this->_utPage = new utPage_A('regi', true, 'itEntries');
		$kdiv =& $this->_utPage->getContentDiv();

		// Display menu for badges et fees
		$itsm['itFees'] = array(KAF_NEWWIN, 'regi', REGI_SELECT_FEES, 0, 300, 200);
		$form =& $kdiv->addForm('fmber');
		$form->addMenu('menuAction', $itsm, -1);
		if (isset($err['errMsg']))	$form->addWng($err['errMsg']);
		$form->addDiv('break', 'blkNewPage');

		// Display list of members
		$sort = kform::getSort("rowsAdmRegis", 3);
		$players = $dt->getRegiMembers($mode['typeMin'], $mode['typeMax'], $sort);
		if (isset($players['errMsg'])) $form->addWng($players['errMsg']);
		else
		{
	  $krows =& $form->addRows('rowsAdmRegis', $players);

	  $sizes = array(9=>'0+');
	  $krows->setSize($sizes);

	  $actions[0] = array(KAF_NEWWIN, 'regi', KID_EDIT, 0, 650,450);
	  $actions[2] = array(KAF_UPLOAD, 'regi', KID_SELECT, 0);
	  $actions[4] = array(KAF_UPLOAD, 'account', KID_SELECT, 9);
	  $krows->setActions($actions);
		}

		//Display the page
		$this->_utPage->display();
		exit;
	}
	// }}}

	// {{{ _updateAccount()
	/**
	 * Modify the account of a registered member in the database
	 *
	 * @access private
	 * @return void
	 */
	function _updateAccount()
	{
		$dt = $this->_dt;

		// Get the informations
		$regiId = kform::getInput('regiId');
		$accountId = kform::getInput('accountId');

		// Update the member
		if (count($regiId) == 1)
		{
			$res = $dt->updateMemberAccount(reset($regiId), $accountId);
			if (is_array($res))
				$this->_displayFormAccount($regiId, $res);
		}

		// Get the informations for arrival and departure
		$infos = array('regi_id'        =>kform::getInput("regiId"),
                     'regi_arrival'   =>kform::getInput("regiArrival"),
                     'regi_departure' =>kform::getInput("regiDeparture"),
                     'regi_arrcmt'    =>kform::getInput("regiArrcmt"),
                     'regi_depcmt'    =>kform::getInput("regiDepcmt"),
                     'regi_transportcmt'    =>kform::getInput("regiTransportcmt"),
                     'regi_prise'    =>kform::getInput("regiPrise")
		);
		$propag = kform::getInput("propagate");

		// Update data base
		$res = $dt->updateDeparr($infos, $propag);
		if (is_array($res))
		$this->_displayFormAccount($regiId, $res);

		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _displayFormAccount()
	/**
	 * Display the page for modifying account of a member
	 *
	 * @access private
	 * @param array $regiId  registration id of the member
	 * @return void
	 */
	function _displayFormAccount($aRegiIds, $err='')
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage('mber');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tAccount', 'regi', KID_UPDATE);

		if (isset($err['errMsg']))
		$form->addWng($err['errMsg']);

		// Initialize the field
		if ( ! is_array($aRegiIds) )$regiIds = array($aRegiIds);
		else $regiIds = $aRegiIds;
		if ( count($regiIds) == 1)
		{
			$regiId = reset($regiIds);
			$infos = $dt->getMemberAdm($regiId);
			if (isset($infos['errMsg'])) $form->addWng($infos['errMsg']);

			$accounts = $dt->getAccounts();
			if (isset($infos['errMsg'])) $form->addWng($infos['errMsg']);

			// Initialize the field
			$form->addHide('regiId[]', $infos['regi_id']);
			$form->addInfo('regiName', $infos['regi_longName']);
			$form->addInfo('accountName', $infos['cunt_name']);
			$form->addCombo('accountId', $accounts, $infos['cunt_name']);
		}
		else
		{
			foreach($regiIds as $id)
			$form->addHide('regiId[]', $id);
			$infos['regi_arrival'] = '';
			$infos['regi_arrcmt'] = '';
			$infos['regi_departure'] = '';
			$infos['regi_prise'] = '';
			$infos['regi_transportcmt'] = '';
		}
		$kedit =& $form->addEdit('regiArrival', $infos['regi_arrival'],20);
		$kedit->setMaxLength(16);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('regiArrcmt', $infos['regi_arrcmt'],30);
		$kedit->setMaxLength(250);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('regiDeparture', $infos['regi_departure'],20);
		$kedit->setMaxLength(16);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('regiDepcmt', $infos['regi_depcmt'],30);
		$kedit->setMaxLength(250);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('regiPrise', $infos['regi_prise'],30);
		$kedit->setMaxLength(5);
		$kedit->noMandatory();
		$kedit =& $form->addArea('regiTransportcmt', $infos['regi_transportcmt'],30);
		$kedit->noMandatory();
		$form->addCheck('propagate', false);

		$elts = array('regiName', 'accountName', 'accountId',
		    'regiName', 'regiArrival', 'regiArrcmt',
		    'regiDeparture', 'regiDepcmt', 'regiCmt', 'regiPrise', 'regiTransportcmt', 'propagate');
		$form->addBlock('blkAccount', $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();

		exit;
	}
	// }}}

	// {{{ _displayHead()
	/**
	 * Display header of the page
	 *
	 * @access private
	 * @param array team  info of the team
	 * @return void
	 */
	function & _displayHead($regiId, $select)
	{
		$dt = $this->_dt;

		// Create a new page
		$this->_utPage = new utPage_A('regi', true, 'itEntries');
		$content =& $this->_utPage->getContentDiv();

		// Display the error if any
		if (isset($infos['errMsg']))
		$form->addWng($infos['errMsg']);

		$regi = $dt->getRegi($regiId);
		// List of teams
		$regis = $dt->getRegis($regi['regi_teamId']);
		$kcombo=& $content->addCombo('selectList', $regis, $regis[$regiId]);
		$acts[1] = array(KAF_UPLOAD, 'regi', KID_SELECT);
		$kcombo->setActions($acts);

		// Display general informations
		$div =& $content->addDiv('blkMember', 'blkData');
		$div->addMsg($regi['regi_longName'], '', 'kTitre');
		$kinfo =& $div->addInfo("regiAccount", $regi['cunt_name']);
		if ($regi['cunt_name'] != '')
		{
	  $act[1] = array(KAF_UPLOAD, 'account', KID_SELECT, $regi['regi_accountId']);
	  $kinfo->setActions($act);
		}

		$kinfo =& $div->addInfo("regiTeam", $regi['team_name']);
		$act[1] = array(KAF_UPLOAD, 'teams', KID_SELECT,$regi['regi_teamId']);
		$kinfo->setActions($act);
		$div->addInfo('memberSolde', sprintf('%02.2f', $regi['solde']) );
		$div->addInfo('accountSolde', sprintf('%02.2f', $regi['soldeAccount']) );

		$div =& $content->addDiv('blkDepart', 'blkData');
		$div->addMsg('Infos transport', '', 'kTitre');
		$kinfo =& $div->addInfo("regiArrival", $regi['regi_arrival']);
		$kinfo =& $div->addInfo("regiArrCmt", $regi['regi_arrcmt']);
		$kinfo =& $div->addInfo("regiDeparture", $regi['regi_departure']);
		$kinfo =& $div->addInfo("regiDepCmt", $regi['regi_depcmt']);

		$items = array();
		$items['btnModify'] = array(KAF_NEWWIN, 'regi',
		KID_EDIT,  $regiId, 450, 300);
		$div->addMenu('menuModif', $items, -1, 'classMenuBtn');

		$content->addDiv('breakH', 'blkNewPage');
		// Add a menu for different action
		$kdiv =& $content->addDiv('choix', 'onglet3');
		$items = array();
		$items['itPurchases'] = array(KAF_UPLOAD, 'regi', KID_SELECT, $regiId);
		$items['itCredits'] = array(KAF_UPLOAD, 'regi',	REGI_CREDITS, $regiId);
		$kdiv->addMenu("menuAdmi", $items, $select);
		$kdiv =& $content->addDiv('contType', 'cont3');
		if ($regi['cunt_name'] == '') $kdiv->addWng('msgNoAccount');

		return $kdiv;
	}

}
?>