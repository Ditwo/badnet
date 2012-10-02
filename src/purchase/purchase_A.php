<?php
/*****************************************************************************
 !   Module     : Purchase
 !   File       : $Source: /cvsroot/aotb/badnet/src/purchase/purchase_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.8 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:25 $
 ******************************************************************************/

require_once "utils/utpage_A.php";
require_once "base_A.php";
require_once "purchase.inc";

/**
 * Module de gestion des achats : classe administrateur
 *
 * @author Gerard CANTEGRIL
 * @see to follow
 *
 */

class purchase_A
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
	function purchase_A()
	{
		$this->_ut = new utils();
		$this->_dt = new purchaseBase_A();
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
	function start($page)
	{
		switch ($page)
		{
	  // Display main windows for account
			case KID_ADD:
				$data['regiId'] = kform::getData();
				$this->_displayFormPurchase($data);
				break;
			case KID_NEW:
				$data['accountId'] = kform::getData();
				$this->_displayFormPurchase($data);
				break;
			case KID_EDIT:
				$cmdId = kform::getData();
				$this->_selectDisplayCmd($cmdId);
				break;
			case PURCHASE_HOTEL:
				$regiId = kform::getData();
				$this->_displayFormHotel($regiId);
				break;
			case PURCHASE_RESERVE:
				$this->_reserveHotel();
				break;
			case PURCHASE_EDIT_ROOM:
				$this->_displayFormEditRoom();
				break;
			case PURCHASE_UPDATE_ROOM:
				$this->_updateRoom();
				break;
			case PURCHASE_UPDATE_ROOMNUM:
				$this->_updateRoomNum();
				break;
			case PURCHASE_SEL_ITEM:
				$data['itemId'] = kform::getData();
				$this->_displayFormPurchase($data);
				break;
			case PURCHASE_SEL_ROOM:
				$this->_selectRoom();
				break;

			case PURCHASE_NEW_CREDIT:
				$data['regiId'] = kform::getData();
				$this->_displayFormCredit($data);
				break;
			case PURCHASE_ADD_CREDIT:
				$data['accountId'] = kform::getData();
				$this->_displayFormCredit($data);
				break;
			case PURCHASE_UPDATE_CREDIT:
				$this->_updateCredit();
				break;
			case KID_UPDATE:
				$this->_updatePurchase();
				break;
			case KID_CONFIRM:
				$this->_displayFormConfirm();
				break;
			case PURCHASE_CONFIRM:
				$this->_displayFormConfirmCredit();
				break;
			case PURCHASE_DEL_CREDIT:
				$this->_delCredits();
				break;
			case KID_DELETE:
				$this->_delPurchases();
				break;
			case WBS_ACT_PURCHASE:
				$this->_displayFormRegiChoice();
				break;
			case PURCHASE_REGI_BARCOD:
				$this->_decodeRegiBarCod();
				break;
			case PURCHASE_REGI_SELECT:
				$data['regiId'] = kform::getData();
				$this->_displayFormRegiSelect($data);
				break;
			case PURCHASE_ITEM_SELECT:
				$data['itemId'] = kform::getData();
				$this->_displayFormRegiSelect($data);
				break;
			case KID_PRINT:
				$this->_displayFormRegiSelect(null);
				break;
			case PURCHASE_ITEM_BARCOD:
				$this->_decodeItemBarCod();
				break;
			case PURCHASE_ITEM_CANCEL:
				$data['itemId'] = -1;
				$this->_displayFormRegiSelect($data);
				break;
			case PURCHASE_ITEMS_GRID:
				$id = kform::getData();
				$this->_displayItemsGrid($id);
				break;
			case PURCHASE_ITEMS_UPDATE:
				$id = kform::getData();
				$this->_updatePurchaseGrid($id);
				break;
			case PURCHASE_COMMAND_VALID:
				$this->_updateCommand();
				break;
			case PURCHASE_COMMAND_PAYEDM:
				$this->_updateCommand(WBS_PAYED_MONEY);
				break;
			case PURCHASE_COMMAND_PAYEDC:
				$this->_updateCommand(WBS_PAYED_CHECK);
				break;
			case PURCHASE_SOLDE_MONEY:
				$this->_soldeCommand(WBS_PAYED_MONEY);
				break;
			case PURCHASE_SOLDE_CHECK:
				$this->_soldeCommand(WBS_PAYED_CHECK);
				break;
			case PURCHASE_SOLDE_CLUB:
				$this->_soldeClub();
				break;
			case PURCHASE_SOLDE_MEMBER:
				$this->_soldeMember();
				break;
			default:
				echo "page $page demand�e depuis purchase_A<br>";
				exit();
		}
	}
	// }}}

	// {{{ _soldeClub()
	/**
	 * @access private
	 * @return void
	 */
	function _soldeClub()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$teamId = kform::getData();
		$res = $dt->soldeTeam($teamId);

		//Close the page
		$page = new utPage('regi');
		$page->close(false);
		exit();
		exit;
	}
	// }}}

	// {{{ _soldeMember()
	/**
	 * @access private
	 * @return void
	 */
	function _soldeMember()
	{
		$dt = $this->_dt;

		$regiId = kform::getData();
		$dt->soldePurchases($regiId, WBS_PAYED_CHECK,
		date(DATE_DATE));

		//Close the page
		$page = new utPage('regi');
		$page->close(false);
		exit();
	}
	// }}}

	// {{{ _soldeCommand()
	/**
	 *
	 *
	 * @access private
	 * @return void
	 */
	function _soldeCommand($payed)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$regiId = kform::getInput('regiId');
		$date = kform::getInput('cmdDate');

		$res = $dt->soldePurchases($regiId, $payed, $date);
		if (isset($res['errMsg']))
		echo($res['errMsg']);

		$this->_displayFormRegiChoice();
		exit;
	}
	// }}}


	// {{{ _updateCommand()
	/**
	 * Register a command
	 * @return void
	 */
	function _updateCommand($payed=false)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$regiId = kform::getInput('regiId');
		$itemIds = kform::getInput('itemIds');
		$date = kform::getInput('cmdDate');
		$value = kform::getInput('du', 0);
		if (is_array($itemIds))
		{
	  foreach($itemIds as $itemId)
	  {
	  	$item = $dt->getItem($itemId);
	  	$command['cmd_id'] = -1;
	  	$command['cmd_name'] = $item['item_name'];
	  	$command['cmd_itemId'] = $item['item_id'];
	  	$command['cmd_regiId'] = $regiId;
	  	$command['cmd_date'] = $date;
	  	$command['cmd_discount'] = 0;
	  	$command['cmd_value'] = $item['item_value'];
	  	$command['cmd_payed'] = min($command['cmd_value'], $value);
	  	$value -= $command['cmd_payed'];
	  	$command['cmd_type'] = $payed;
	  	if ($payed == false)
	  	$command['cmd_payed'] = 0;

	  	$dt->updatePurchase($command);
	  }
		}
		$this->_displayFormRegiChoice();
		exit;
	}
	// }}}

	// {{{ _updateRoomNum()
	/**
	 * Modification of a room number
	 * @return void
	 */
	function _updateRoomNum()
	{
		$dt = $this->_dt;

		$dt->updateRoomNum(kform::getInput('cmdId'),
		kform::getInput('room'));
		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _updateRoom()
	/**
	 * Modification of a reservation for hotel
	 * @return void
	 */
	function _updateRoom()
	{
		$dt = $this->_dt;

		$command['cmd_id']   = kform::getInput('cmdId');
		$command['cmd_name'] = kform::getInput('hotel');
		$command['cmd_code'] = kform::getInput('itemCode');
		$command['cmd_date'] = kform::getInput('arrival');
		$command['cmd_cmt']  = kform::getInput('room');
		$command['cmd_value']  = kform::getInput('cmdValue');
		$command['cmd_payed']  = kform::getInput('cmdPayed');
		$command['cmd_discount']  = kform::getInput('cmdDiscount');
		$command['cmd_type']  = kform::getInput('cmdType');
		$dt->updateRoom($command);

		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}


	// {{{ _reserveHotel()
	/**
	 * Register a reservation for hotel
	 * @return void
	 */
	function _reserveHotel()
	{
		$dt = $this->_dt;

		$command['regiId']   = kform::getInput('regiId');
		$command['hotel']    = kform::getInput('hotel');
		$command['itemCode'] = kform::getInput('itemCode');
		$command['arrival']  = kform::getInput('arrival');
		$command['departure']  = kform::getInput('departure');
		$command['nbNight']  = intval(kform::getInput('nbNight'));
		$command['room']     = kform::getInput('room');
		$command['isfree']     = kform::getInput('isfree', null);
		$utd = new utdate();
		$utd->setFrDate($command['arrival']);
		if (empty($command['departure']) && $command['nbNight']<= 0  )
		   $this->_displayFormHotel($command['regiId'], 'erreur');

		if (!empty($command['departure']) )
		{
			$command['nbNight'] = $utd->getDayDiff($command['departure']);
		}		
		else
		{
			$utd->addMinute(24*60*$command['nbNight']);
			$command['departure'] = $utd->getDate();
		}
		$dt->reserveHotel($command);

		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}


	// {{{ _displayFormRegiSelect()
	/**
	 * Display a page to select item with barcod
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormRegiSelect($data, $err='')
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		// Creating a new page
		$utpage = new utPage_A('purchase', true, 'itPurchases');
		$content =& $utpage->getContentDiv();
		if (isset($data['print']) )
		{
			$utpage->_page->addAction('onload', array('print'));
		}
		$form =& $content->addForm('fpurchase','purchase',PURCHASE_ITEM_BARCOD);

		$form->addMsg('tPurchase');

		if ($err!= '')
		$form->addWng($err);


		$date = kform::getInput('cmdDate');
		if (!isset($data['regiId']))
		$data['regiId'] = kform::getInput('regiId');
		$infos = $dt->getRegiData($data['regiId'], $date);

		$form->addHide('regiId', $data['regiId']);
		$form->addInfo('cmdDate', $date);
		$form->addInfo('regiName', $infos['regi_longName']);
		$form->addInfo('regiTeam', $infos['team_name']);
		$form->addInfo('regiSolde', $infos['regiSolde']);
		$form->addInfo('regiRest', $infos['regiRest']);
		$form->addInfo('cuntSolde', $infos['cuntSolde']);
		$form->addInfo('cuntRest', $infos['cuntRest']);
		$elts = array('regiName','regiSolde','regiRest');
		$form->addBlock('blkRegi', $elts);
		$elts = array('regiTeam', 'cuntSolde', 'cuntRest');
		$form->addBlock('blkCunt', $elts);

		//Liste des articles deja selectionnes
		$itemIds = kform::getInput('itemIds');
		// Mise a jour de la liste
		if (isset($data['itemId']))
		{
	  // Ajout du nouvel article selectionne a la liste
	  if ($data['itemId']!=-1)
	  $itemIds[]=$data['itemId'];
	  // Suppression du dernier
	  else
	  if(is_array($itemIds)) array_pop($itemIds);
		}

		// Limitation de la saisie a 8 articles max
		//if (count($itemIds) < 8)

		$kedit =& $form->addEdit('itemBarCod', '', 30);
		$kedit->noMandatory();
		$form->addBtn('btnValid', KAF_SUBMIT);
		$form->addDiv('break', 'blkNewPage');
		$elts = array('itemBarCod', 'btnValid');
		$form->addBlock('blkBarCod', $elts);

		// Limitation de la saisie a 8 articles max
		if (count($itemIds) == 8)
		{
	  $form->addWng("msgNbMaxItems");
		}
		// List of items
		$rows = $dt->getItems(true);
		$rows[-1] = "---------";
		if (isset($rows['errMsg']))
	 $form->addWng($rows['errMsg']);
	 else
	 {
	 	$krow =& $form->addCombo("items", $rows, $rows[-1]);
	 	$actions[1] = array(KAF_UPLOAD, 'purchase',
	 	PURCHASE_ITEM_SELECT);
	 	$krow->setActions($actions);
	 }
	 //    }


	 // Calcul des remises et du total
	 $rest = $infos['cuntRest']+$infos['regiRest'];
	 $total = 0;
	 $discount = 0;
	 $du = 0;
	 if(is_array($itemIds) && count($itemIds))
	 {
	  $command = $dt->getCmdItems($itemIds);
	  if (isset($command['errMsg']))
	  $form->addWng($command['errMsg']);
	  foreach($itemIds as $itemId)
	  {
	  	$form->addHide('itemIds[]', $itemId);
	  	$cmd = $command[$itemId];
	  	$itemDiscount = 0;
	  	if ($cmd['item_isCreditable']==1)
	  	{
	  		$itemDiscount = min($rest, $cmd['item_value']);
	  		$rest -= $itemDiscount;
	  	}
	  	$cmd[] = $itemDiscount;
	  	$discount += $itemDiscount;
	  	$cmd[] = $cmd['item_value']-$itemDiscount;
	  	$total += $cmd['item_value'];
	  	unset($cmd['item_isCreditable']);
	  	$allCmd[] = $cmd;
	  }
	  $form->addInfo('rest', $rest);
	  $form->addHide('total', $total);
	  $form->addHide('discount', $discount);
	  $form->addInfo('du', $total-$discount);
	  $krows =& $form->addRows('itemCmd', $allCmd);
	  $krows->displaySelect(false);

	  $form->addBtn('btnCancel',  KAF_UPLOAD, 'purchase',
			WBS_ACT_PURCHASE);
	  $form->addBtn('btnAbort', KAF_UPLOAD, 'purchase',
			PURCHASE_ITEM_CANCEL);
	  $form->addBtn('btnNotPay', KAF_UPLOAD, 'purchase',
			PURCHASE_COMMAND_VALID);
	  $form->addBtn('btnPayedC', KAF_UPLOAD, 'purchase',
			PURCHASE_COMMAND_PAYEDC);
	  $form->addBtn('btnPayedM', KAF_UPLOAD, 'purchase',
			PURCHASE_COMMAND_PAYEDM);
				
	  $form->addBtn('btnPrint', 'print', 'purchase',
			KID_PRINT);
			$elts = array('btnCancel', 'btnAbort','btnNotPay', 'btnPayedC',
			'btnPayedM', 'btnPrint');
	  $form->addBlock('blkBtn', $elts);
	 }
	 else
	 {
	  $form->addBtn('btnCancel',  KAF_UPLOAD, 'purchase',
			WBS_ACT_PURCHASE);
	  $form->addBtn('btnSoldeC', KAF_UPLOAD, 'purchase',
			PURCHASE_SOLDE_CHECK);
	  $form->addBtn('btnSoldeM', KAF_UPLOAD, 'purchase',
			PURCHASE_SOLDE_MONEY);
	  $elts = array('btnCancel', 'btnSoldeC', 'btnSoldeM');
	  $form->addBlock('blkBtn', $elts);
	 }
	 $utpage->display();
	 exit;
	}
	// }}}

	// {{{ _decodeRegiBarCod()
	/**
	 * Display a page with the list of the events
	 *
	 * @access private
	 * @return void
	 */
	function _decodeRegiBarCod($err='')
	{
		$dt = $this->_dt;
		$barCod = kform::getInput('regiBarCod');

		require_once "utils/utbarcod.php";
		$utbr = new utBarCod();

		$regiId = $utbr->getRegiId($barCod);
		if ($regiId != -1)
		{
	  if ($dt->existRegi($regiId) > 0)
	  {
	  	$data['regiId']  = $regiId;
	  	$this->_displayFormRegiSelect($data);
	  }
		}
		if ($utbr->isStock($barCod))
		$this->_displayFormStock();
		 
		$this->_displayFormRegiChoice('msgNoValidBarCod');
	}
	// }}}

	// {{{ _decodeItemBarCod()
	/**
	 * Decode l'action de la douchette
	 *
	 * @access private
	 * @return void
	 */
	function _decodeItemBarCod($err='')
	{
		$dt = $this->_dt;

		$barCod = kform::getInput('itemBarCod');

		require_once "utils/utbarcod.php";
		$utbr = new utBarCod();

		$itemId = $utbr->getItemId($barCod);
		if ($itemId != -1)
		{
	  if ($dt->existItem($itemId) > 0)
	  {
	  	$data['itemId'] = $itemId;
	  	$this->_displayFormRegiSelect($data);
	  }
		}

		if ($utbr->isValid($barCod) )
		$this->_updateCommand();

		if ($utbr->isPayedMoney($barCod) )
		$this->_updateCommand(WBS_PAYED_MONEY);

		if ($utbr->isPayedCheck($barCod) )
		$this->_updateCommand(WBS_PAYED_CHECK);

		if ($utbr->isPayedCb($barCod) )
		$this->_updateCommand(WBS_PAYED_CB);

		if ($utbr->isSoldeIndiMoney($barCod) )
		$this->_soldeCommand(WBS_PAYED_MONEY);

		if ($utbr->isSoldeIndiCheck($barCod) )
		$this->_soldeCommand(WBS_PAYED_CHECK);

		if ($utbr->isAbort($barCod) )
		$this->_displayFormRegiChoice();

		if ($utbr->isPrint($barCod) )
		{
			$data['print']  = true;
			$this->_displayFormRegiSelect($data);
		}
		if ($utbr->isCancel($barCod) )
		{
	  $data['itemId'] = -1;
	  $this->_displayFormRegiSelect($data);
		}
		 
		$data['regiId'] = kform::getInput('regiId');
		$this->_displayFormRegiSelect($data, 'msgNoValidBarCod');
	}
	// }}}

	// {{{ _displayFormRegiChoice()
	/**
	 * Display a page to select members with barcod
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormRegiChoice($err='')
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Creating a new page
		$utpage = new utPage_A('purchase', true, 'itPurchases');
		$content =& $utpage->getContentDiv();

		$form =& $content->addForm('fpurchase','purchase',PURCHASE_REGI_BARCOD);

		$form->addMsg('tPurchase');

		if ($err!= '')
		$form->addWng($err);

		$kedit =& $form->addEdit('regiBarCod', '', 30);
		$kedit->noMandatory();
		$form->addBtn('btnValid', KAF_SUBMIT);
		$elts = array('regiBarCod', 'btnValid');
		$form->addBlock('blkBarCod', $elts);

		// List of members
		$rows = $dt->getMembers();
		$rows[-1] = "---------";
		if (isset($rows['errMsg']))
		$form->addWng($rows['errMsg']);
		else
		{
			$krow =& $form->addCombo("members", $rows, $rows[-1]);
			$actions[1] = array(KAF_UPLOAD, 'purchase', PURCHASE_REGI_SELECT);
			$krow->setActions($actions);
		}
		$kedit =& $form->addEdit('cmdDate', date(DATE_DATE), 10);


		// Display previous command
		$form->addMsg('tLastCommand');
		$itemIds = kform::getInput('itemIds');
		if (isset($data['itemId']))
		{
	  if ($data['itemId']!=-1)
	  $itemIds[]=$data['itemId'];
	  else
	  if(is_array($itemIds)) array_pop($itemIds);
		}
		$total = kform::getInput('total', 0);
		$discount = kform::getInput('discount', 0);
		$member = kform::getInput('regiName');
		if(is_array($itemIds) && count($itemIds))
		{
	  $command = $dt->getCmdItems($itemIds);
	  if (isset($command['errMsg']))
	  $form->addWng($command['errMsg']);
	  foreach($itemIds as $itemId)
	  {
	  	$cmd = $command[$itemId];
	  	unset($cmd['item_isCreditable']);
	  	$allCmd[] = $cmd;
	  }
	  $form->addInfo('qui', $member);
	  $form->addInfo('total', $total);
	  $form->addInfo('discount', $discount);
	  $form->addInfo('combien', $total-$discount);
	  $krows =& $form->addRows('itemCmd', $allCmd);
	  $krows->displaySelect(false);
		}


		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormHotel()
	/**
	 * Display the page for reserving hotel
	 * @return void
	 */
	function _displayFormHotel($regiId, $err=null)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage('purchase');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tHotelreserve', 'purchase', PURCHASE_RESERVE);

		if ( !is_null($err) )
		$content->addWng($err);

		// Initialize the field
		$form->addHide("regiId", $regiId);
		$hotels = $dt->getHotels();
		if (isset($hotels['errMsg']))
		$form->addWng($hotels['errMsg']);
		else
		{
	  $itemSel = reset($hotels);
	  $kcombo =& $form->addCombo('hotel', $hotels, $itemSel);
		}

		$form->addRadio('itemCode', false, WBS_ROOM_SINGLE);
		$form->addRadio('itemCode', true, WBS_ROOM_TWIN);
		$form->addRadio('itemCode', false, WBS_ROOM_TRIPLEX);
		$form->addRadio('itemCode', false, WBS_ROOM_OTHER);
		$elts = array('itemCode1', 'itemCode2', 'itemCode3', 'itemCode4');
		$form->addBlock('blkCode', $elts);
		 
		$kedit =& $form->addEdit('arrival', $dt->getArrival($regiId));
		$kedit->setMaxLength(16);
		$kedit =& $form->addEdit('departure', $dt->getDeparture($regiId));
		$kedit->setMaxLength(16);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('nbNight', '1');
		$kedit->setMaxLength(2);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('room', '');
		$kedit->setMaxLength(2);
		$kedit->noMandatory();
		$form->addCheck('isfree', false);
		$elts = array('hotel', 'blkCode', 'arrival', 'departure', 'nbNight', 'room', 'isfree');
		$form->addBlock('blkReserveForm', $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormRoom()
	/**
	 * Display the page for chaging a room
	 * @return void
	 */
	function _displayFormRoom($cmd)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage('purchase');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tHotelreserve', 'purchase', PURCHASE_UPDATE_ROOM);

		// Initialize the field
		$hotels = $dt->getHotels();
		$kcombo =& $form->addCombo('hotel', $hotels, $cmd['item_name']);
		$act[1] = array(KAF_UPLOAD, 'purchase', PURCHASE_SEL_ROOM);
		$kcombo->setActions($act);

		$form->addHide('cmdId', $cmd['cmd_id']);
		$radio =& $form->addRadio('itemCode', $cmd['item_code']==WBS_ROOM_SINGLE, WBS_ROOM_SINGLE);
		$radio->setActions($act);
		$radio =& $form->addRadio('itemCode', $cmd['item_code']==WBS_ROOM_TWIN, WBS_ROOM_TWIN);
		$radio->setActions($act);
		$radio =& $form->addRadio('itemCode', $cmd['item_code']==WBS_ROOM_TRIPLEX, WBS_ROOM_TRIPLEX);
		$radio->setActions($act);
		$radio =& $form->addRadio('itemCode', $cmd['item_code']==WBS_ROOM_OTHER, WBS_ROOM_OTHER);
		$radio->setActions($act);
		$elts = array('itemCode1', 'itemCode2', 'itemCode3', 'itemCode4');
		$form->addBlock('blkCode', $elts);
		 
		$kedit =& $form->addEdit('arrival', $cmd['cmd_date']);
		$kedit->setMaxLength(10);
		$kedit =& $form->addEdit('room', $cmd['cmd_cmt']);
		$kedit->setMaxLength(2);
		$kedit->noMandatory();

		$kedit =& $form->addEdit('cmdValue', $cmd['cmd_value']);
		$kedit->setMaxLength(10);
		$kedit =& $form->addEdit('cmdDiscount', $cmd['cmd_discount']);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('cmdPayed', $cmd['cmd_payed']);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$form->addRadio('cmdType', $cmd['cmd_type']==WBS_PAYED_NONE,
		WBS_PAYED_NONE);
		$form->addRadio('cmdType', $cmd['cmd_type']==WBS_PAYED_CREDIT,
		WBS_PAYED_CREDIT);
		$form->addRadio('cmdType', $cmd['cmd_type']==WBS_PAYED_MONEY,
		WBS_PAYED_MONEY);
		$form->addRadio('cmdType', $cmd['cmd_type']==WBS_PAYED_CHECK,
		WBS_PAYED_CHECK);
		$form->addRadio('cmdType', $cmd['cmd_type']==WBS_PAYED_CB,
		WBS_PAYED_CB);
		$elts = array('hotel', 'blkCode', 'arrival', 'room',
		    'cmdValue', 'cmdDiscount', 'cmdPayed', 'cmdType1',
		     'cmdType2', 'cmdType3', 'cmdType4','cmdType5',);
		$form->addBlock('blkReserveForm', $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _selectDisplayCmd()
	/**
	 * Display the page for creating or updating a purchase
	 *
	 * @return void
	 */
	function _selectDisplayCmd($cmdId)
	{
		$dt = $this->_dt;

		$cmd = $dt->getPurchase($cmdId);
		if ($cmd['item_rubrikId'] == WBS_RUBRIK_HOTEL)
		$this->_displayFormRoom($cmd);
		else
		{
	  $data['purchaseId'] = kform::getData();
	  $this->_displayFormPurchase($data);
		}
	}
	// }}}

	// {{{ _selectRoom()
	/**
	 * Modify the room
	 *
	 * @return void
	 */
	function _selectRoom()
	{
		$dt = $this->_dt;
		$cmdId = kform::getinput('cmdId');
		$cmd = $dt->getPurchase($cmdId);
		$cmd['item_code']= kform::getinput('itemCode');
		$hotels = $dt->getHotels();
		$cmd['item_name']= $hotels[kform::getinput('hotel')];
		$val = $dt->getPriceRoom($cmd['item_name'], $cmd['item_code'],
		kform::getinput('arrival'));
		$cmd['cmd_value']= $val['item_value'];
		$this->_displayFormRoom($cmd);
	}
	// }}}

	// {{{ _displayFormEditRoom()
	/**
	 * Display the page for editing a room
	 * @return void
	 */
	function _displayFormEditRoom()
	{
		$dt = $this->_dt;

		// New page
		$utpage = new utPage('purchase');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tRoomEdit', 'purchase', PURCHASE_UPDATE_ROOMNUM);

		// Initialize the field
		$cmdId = kform::getData();
		$cmd = $dt->getPurchase($cmdId);
		if ($cmd['item_rubrikId'] != WBS_RUBRIK_HOTEL)
		{
	  $page = new kPage('none');
	  $page->close(false);
		}
		$form->addInfo('hotel', $cmd['item_name']);
		$form->addHide('cmdId', $cmd['cmd_id']);
		$ut = new utils();
		$form->addInfo('itemCode', $ut->getLabel($cmd['item_code']));
		 
		$kedit =& $form->addEdit('room', $cmd['cmd_cmt']);
		$kedit->setMaxLength(2);
		$kedit->noMandatory();
		$elts = array('hotel', 'itemCode', 'room');
		$form->addBlock('blkRoomForm', $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormPurchase()
	/**
	 * Display the page for creating or updating a purchase
	 *
	 * @access private
	 * @param array $member  info of the member
	 * @return void
	 */
	function _displayFormPurchase($data)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage('purchase');
		$content =& $utpage->getPage();

		// Display the error if any
		if (isset($data['errMsg']))
		$content->addWng($data['errMsg']);

		// Initialize the field
		if (isset($data['purchaseId']))
		$infos = $dt->getPurchase($data['purchaseId']);
		else
		$infos = array('cmd_id'    => kform::getInput('cmdId', -1),
		       'cmd_name'  => kform::getInput('cmdName'),
		       'cmd_regiId'   => kform::getInput('cmdRegiId'),
		       'cmd_itemId'   => kform::getInput('cmdItemId'),
		       'cmd_date'     => kform::getInput('cmdDate'),
		       'cmd_discount' => kform::getInput('cmdDiscount'),
		       'cmd_payed'    => kform::getInput('cmdPayed'),
		       'cmd_value'    => kform::getInput('cmdValue'),
		       'cmd_accountId' => kform::getInput('cmdAccountId'),
		       'cmd_cmt'       => kform::getInput('cmdCmt'),
		       'cmd_type'  => kform::getInput('cmdType', 
		WBS_PAYED_NONE)
		);
		if (isset($data['accountId']))
		$infos['cmd_accountId'] = $data['accountId'];
		if (isset($data['regiId']))
		$infos['cmd_regiId'] = $data['regiId'];

		if (isset($data['itemId']))
		{
	  $item = $dt->getItem($data['itemId']);
	  $infos['cmd_itemId'] = $data['itemId'];
	  $infos['cmd_name'] = $item['item_name'];
	  $infos['cmd_value'] = $item['item_value'];
		}
		if ($infos['cmd_date'] == '')
		$infos['cmd_date'] = date(DATE_DATE);

		if ($infos['cmd_id'] != -1)
		$form =& $content->addForm('tEditPurchase', 'purchase', KID_UPDATE);
		else
		$form =& $content->addForm('tNewPurchase', 'purchase', KID_UPDATE);

		// Display the error if any
		if (isset($infos['errMsg']))
		$form->addWng($infos['errMsg']);

		// Initialize the field
		$form->addHide("cmdId", $infos['cmd_id']);
		$form->addHide("cmdRegiId", $infos['cmd_regiId']);
		$form->addHide("cmdAccountId", $infos['cmd_accountId']);

		$items = $dt->getItems();
		$items[-1] = "------";
		if (isset($items['errMsg']))
		$form->addWng($items['errMsg']);

		if (isset($items[$infos['cmd_itemId']]))
		$itemSel  =$items[$infos['cmd_itemId']];
		else
		{
	  $first = each($items);
	  $itemSel = $items[$first[0]];
	  $itemSel = $items[-1];
		}
		$kcombo =& $form->addCombo('cmdItemId', $items, $itemSel);
		$act[1] = array(KAF_UPLOAD, 'purchase', PURCHASE_SEL_ITEM);
		$kcombo->setActions($act);

		$kedit =& $form->addEdit('cmdName',  $infos['cmd_name']);
		$kedit->setMaxLength(30);
		$kedit =& $form->addEdit('cmdDate', $infos['cmd_date']);
		$kedit->setMaxLength(10);
		$kedit =& $form->addEdit('cmdValue', $infos['cmd_value']);
		$kedit->setMaxLength(10);
		$kedit =& $form->addEdit('cmdDiscount', $infos['cmd_discount']);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('cmdPayed', $infos['cmd_payed']);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$form->addRadio('cmdType', $infos['cmd_type']==WBS_PAYED_NONE,
		WBS_PAYED_NONE);
		$form->addRadio('cmdType', $infos['cmd_type']==WBS_PAYED_CREDIT,
		WBS_PAYED_CREDIT);
		$form->addRadio('cmdType', $infos['cmd_type']==WBS_PAYED_MONEY,
		WBS_PAYED_MONEY);
		$form->addRadio('cmdType', $infos['cmd_type']==WBS_PAYED_CHECK,
		WBS_PAYED_CHECK);
		$form->addRadio('cmdType', $infos['cmd_type']==WBS_PAYED_CB,
		WBS_PAYED_CB);
		$karea =& $form->addArea('cmdCmt', $infos['cmd_cmt']);
		$karea->noMandatory();
		$elts = array('cmdItemId', 'cmdName', 'cmdDate', 'cmdValue',
		    'cmdDiscount', 'cmdPayed', 'cmdType1',
		     'cmdType2', 'cmdType3', 'cmdType4','cmdType5',
		    'cmdCmt');
		$form->addBlock('blkAccountForm', $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormCredit()
	/**
	 * Display the page for creating or updating a credit
	 *
	 * @access private
	 * @param array $member  info of the member
	 * @return void
	 */
	function _displayFormCredit($data)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage('purchase');
		$content =& $utpage->getPage();

		// Display the error if any
		if (isset($data['errMsg'])) $content->addWng($data['errMsg']);
 		$regis = kform::getInput('rowsMembers', array());

 		// Initialize the field
		if (isset($data['purchaseId']))	$infos = $dt->getPurchase($data['purchaseId']);
		else
		$infos = array('cmd_id'    => kform::getInput('cmdId', -1),
		       'cmd_name'   => kform::getInput('cmdName'),
		       'cmd_regiId'   => kform::getInput('cmdRegiId'),
		       'cmd_date'     => kform::getInput('cmdDate'),
		       'cmd_payed'    => kform::getInput('cmdCredit'),
		       'cmd_value'    => kform::getInput('cmdValue'),
		       'cmd_accountId' => kform::getInput('cmdAccountId')
		);
		if (isset($data['accountId'])) $infos['cmd_accountId'] = $data['accountId'];
		if (isset($data['regiId']))	$infos['cmd_regiId'] = $data['regiId'];
		if ($infos['cmd_date'] == '') $infos['cmd_date'] = date(DATE_DATE);
		$refund = ($infos['cmd_payed']==$infos['cmd_value']);

		if ($infos['cmd_id'] != -1)	$form =& $content->addForm('tEditCredit', 'purchase', PURCHASE_UPDATE_CREDIT);
		else $form =& $content->addForm('tNewCredit', 'purchase', PURCHASE_UPDATE_CREDIT);

		// Display the error if any
		if (isset($infos['errMsg']))
		$form->addWng($infos['errMsg']);

		// Initialize the field
		$form->addHide("cmdId", $infos['cmd_id']);
		$form->addHide("cmdRegiId", $infos['cmd_regiId']);
		$form->addHide("cmdAccountId", $infos['cmd_accountId']);
		foreach($regis as $regiId) $form->addhide('regiIds[]', $regiId);
		
		$kedit =& $form->addEdit('cmdName', $infos['cmd_name'], 15);
		$kedit->setMaxLength(30);
		$kedit =& $form->addEdit('cmdDateCredit', $infos['cmd_date'], 10);
		$kedit->setMaxLength(10);
		//$kedit =& $form->addEdit('cmdNbJour', 1, 10);
		//$kedit->setMaxLength(1);
		$kedit =& $form->addEdit('cmdCredit', $infos['cmd_payed'], 10);
		$kedit->setMaxLength(10);
		$kcheck =& $form->addCheck('isRefund', $refund);
		$elts = array('cmdItemId', 'cmdName', 'cmdDateCredit', 'cmdNbJour', 'cmdCredit',
		    'isRefund');
		$form->addBlock('blkAccountForm', $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _updateCredit()
	/**
	 * Create a credit purchase in the database
	 *
	 * @access private
	 * @return void
	 */
	function _updateCredit()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Get the informations
		$infos = array('cmd_id'    => kform::getInput('cmdId', -1),
		     'cmd_name'   => kform::getInput('cmdName'),
		     'cmd_regiId'   => kform::getInput('cmdRegiId', -1),
		     'cmd_date'     => kform::getInput('cmdDateCredit'),
		     'cmd_payed'    => kform::getInput('cmdCredit'),
		     'cmd_accountId' => kform::getInput('cmdAccountId', -1),
		     'cmd_type' => WBS_PAYED_NONE
		);
		$nbJour = kform::getInput('cmdNbJour', 1);
		if ($nbJour < 1) $nbJour = 1;
		$regids = kform::getInput('regiIds', array());
		
		$infos['cmd_itemId'] = -1;
		// A la creation
		$infos['cmd_discount'] = $infos['cmd_payed'];
		if ( kform::getInput('isRefund', -1) != -1)	$infos['cmd_value'] = $infos['cmd_discount'];
		else $infos['cmd_value'] = $infos['cmd_discount'] + $infos['cmd_payed'];

		// Control the informations
		if ($infos['cmd_date'] == "")
		{
			$err['errMsg'] = 'msgcmdDate';
			$this->_displayFormPurchase($err);
		}
		if ($infos['cmd_payed'] == "")
		{
			$err['errMsg'] = 'msgcmdCredit';
			$this->_displayFormPurchase($err);
		}

		// Add the purchase
		$res= $dt->updateCredit($infos, $nbJour, $regids);
		if (is_array($res))
		{
			$this->_displayFormCredit($res);
		}
		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _displayFormConfirm()
	/**
	 * Display the page for confirmation the destruction of purchase
	 *
	 * @access private
	 * @param array $member  info of the member
	 * @return void
	 */
	function _displayFormConfirm()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage('purchase');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tDelPurchase', 'purchase', KID_DELETE);

		// Initialize the field
		$purchasesId = kform::getInput("rowsPurchase");
		if ($purchasesId != '')
		{
			 
	  foreach($purchasesId as $id)
	  $form->addHide("rowsPurchase[]", $id);
	  $form->addMsg('msgConfirmDel');
	  $form->addBtn('btnDelete', KAF_SUBMIT);
		}
		else
		$form->addWng('msgNeedPurchase');
		$form->addBtn('btnCancel');
		$elts = array('btnDelete', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormConfirmCredit()
	/**
	 * Display the page for confirmation the destruction of purchase
	 *
	 * @access private
	 * @param array $member  info of the member
	 * @return void
	 */
	function _displayFormConfirmCredit()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage('purchase');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tDelCredit', 'purchase',
		PURCHASE_DEL_CREDIT);

		// Initialize the field
		$purchasesId = kform::getInput("rowsPurchase");
		if ($purchasesId != '')
		{
			 
	  foreach($purchasesId as $id)
	  $form->addHide("rowsPurchase[]", $id);
	  $form->addMsg('msgConfirmDelCredit');
	  $form->addBtn('btnDelete', KAF_SUBMIT);
		}
		else
		$form->addWng('msgNeedCredit');
		$form->addBtn('btnCancel');
		$elts = array('btnDelete', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _updatePurchase()
	/**
	 * Create the member in the database
	 *
	 * @access private
	 * @return void
	 */
	function _updatePurchase()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Get the informations
		$infos = array('cmd_id'    => kform::getInput('cmdId', -1),
		     'cmd_name'  => kform::getInput('cmdName'),
		     'cmd_regiId'   => kform::getInput('cmdRegiId', -1),
		     'cmd_itemId'   => kform::getInput('cmdItemId', -1),
		     'cmd_date'     => kform::getInput('cmdDate'),
		     'cmd_discount' => kform::getInput('cmdDiscount'),
		     'cmd_payed'    => kform::getInput('cmdPayed'),
		     'cmd_value'    => kform::getInput('cmdValue'),
		     'cmd_cmt'      => kform::getInput('cmdCmt'),
		     'cmd_accountId' => kform::getInput('cmdAccountId', -1),
		     'cmd_type' => kform::getInput('cmdType', WBS_PAYED_NONE)
		);

		// Control the informations
		if ($infos['cmd_name'] == "")
		{
			$err['errMsg'] = 'msgcmdName';
			$this->_displayFormPurchase($err);
		}

		if ($infos['cmd_date'] == "")
		{
			$err['errMsg'] = 'msgcmdDate';
			$this->_displayFormPurchase($err);
		}
		if ($infos['cmd_itemId'] == -1)
		{
			$err['errMsg'] = 'msgcmdItemId';
			$this->_displayFormPurchase($err);
		}
		if ($infos['cmd_value'] == "")
		{
			$err['errMsg'] = 'msgcmdValue';
			$this->_displayFormPurchase($err);
		}

		// Add the account
		$res= $dt->updatePurchase($infos);

		if (is_array($res))
		{
			$this->_displayFormPurchase($res);
		}
		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _delPurchases()
	/**
	 * Delete the selected purchase in the database
	 *
	 * @access private
	 * @param  integer $isDelete  new status of the event
	 * @return void
	 */
	function _delPurchases()
	{
		$dt = $this->_dt;

		// Get the id's of the teams to delete
		$purchasesId = kform::getInput("rowsPurchase");

		// Delete the teams
		if ($purchasesId != '')
		$res = $dt->delPurchases($purchasesId);

		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}


	// {{{ _displayItemsGrid()
	/**
	 * Display the grid to select purchase for each account
	 *
	 * @access private
	 * @param array $member  info of the member
	 * @return void
	 */
	function _displayItemsGrid($assoc='')
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage('purchase');
		$content =& $utpage->getPage();

		// Initialize the field

		// grid columns
		// columns = hotel
		$c_title = $dt->getItemsCode();

		// grid row
		// row = assoc members
		$r_title = $dt->getMembersNameId($assoc);

		//grid preselected item
		$date = kform::getInput('resaDate', date(DATE_DATE));
		$select = $dt->getPurchaseList($assoc, $date);

		// grid attribute
		$attrib = array ('c_titles' =>  $c_title,
		       'r_titles' =>  $r_title, 
		       'select' => $select);

		// form definition
		$form =& $content->addForm('tEditAssocResa', 'purchase', PURCHASE_ITEMS_UPDATE);


		// Display the error if any
		if (isset($infos['errMsg']))
		$form->addWng($infos['errMsg']);

		// Initialize the field
		$form->addHide("assoId", $assoc);
		$form->addInfo('resaDate', $date);

		$kgrid =& $form->addGrid("resaGrid", $attrib);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}



	// {{{ _updateGrid($infos="")
	/**
	 * Display the page for creating or updating a resa
	 *
	 * @access private
	 * @param array resa  info of the resa
	 * @return void
	 */
	function _updatePurchaseGrid()
	{
		$ut = $this->_ut;
		$data = $this->_dt;
		 
		// Get informations from the grid
		$grid_select = kform::getInput("resaGrid") ;
		$assoc = kform::getInput("assoId");

		// Get old informations from the database
		$date = kform::getInput('resaDate', date(DATE_DATE));
		$select = $data->getPurchaseList($assoc, $date);

		//$infos['resa_eventId'] = utvars::getEventId();
		$infos['cmd_date'] = kform::getInput('resaDate', date(DATE_DATE));

		foreach ( $grid_select as $line)
		{
			// recup id mber et id hotel
	  list($mber_id, $hotel_id) = explode(" ", $line);

	  // no need to create a purchase if already done
	  if (isset($select[$mber_id][$hotel_id]))
	  {
	  	// no need to create a purchase if already done
	  	unset($select[$mber_id][$hotel_id]);
	  }
	  else
	  {
	  	//its a new reservation

	  	// set informations
	  	$info_hotel = $data->getItem($hotel_id);

	  	$infos = array('cmd_id'     => -1,
                             'cmd_name'   => $info_hotel['item_name'],
                             'cmd_regiId' => $mber_id,
                             'cmd_itemId' => $hotel_id,
                             'cmd_value'  => $info_hotel['item_value'],
			     'cmd_date'   => kform::getInput('resaDate', date(DATE_DATE)),
			     'cmd_payed'  => 0,
			     'cmd_discount' => 0,
                             'cmd_accountId' => -1
	  	);
	  	$data->updatePurchase($infos);
	  }
		}

		// maintenant $select contient les r�servations supprim�es par l'utilisateur
		foreach ($select as $mber_id => $lst_items)
		{
	  if (is_array($lst_items)){
	  	foreach ($lst_items as $items_id => $value)
	  	{
	  		// delete this reservation
	  		// set informations
	  		$infos = array('cmd_regiId' => $mber_id,
                             'cmd_itemId' => $items_id,
			     'cmd_date'   => kform::getInput('resaDate', date(DATE_DATE)),
	  		);
	  		$purchasesids = $data->getPurchasesIds($infos);
	  		$data->delPurchases($purchasesids);
	  	}
	  }
		}


		$page = new kPage('none');
		$page->close();
		exit;

	}
	// }}}

	// {{{ _delCredits()
	/**
	 * Delete the selected credits in the database
	 *
	 * @access private
	 * @param  integer $isDelete  new status of the event
	 * @return void
	 */
	function _delCredits()
	{
		$dt = $this->_dt;

		// Get the id's of the teams to delete
		$purchasesId = kform::getInput("rowsPurchase");

		// Delete the teams
		if ($purchasesId != '')
		$res = $dt->delCredits($purchasesId);

		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

}

?>