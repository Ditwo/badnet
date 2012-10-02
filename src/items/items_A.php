<?php
/*****************************************************************************
 !   Module     : items
 !   File       : $Source: /cvsroot/aotb/badnet/src/items/items_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.9 $
 !   Author     : PMM
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/01/18 07:51:18 $
 !   Mailto     : philippe at midol-monnet.org
 ******************************************************************************/

require_once "items.inc";
require_once "base_A.php";
require_once "utils/utimg.php";
require_once "utils/utpage_A.php";
require_once "events/events.inc";
require_once "teams/teams.inc";

/**
 * Module de gestion des articles
 *
 * @author PMM
 * @see to follow
 *
 */

class items_A
{

	// {{{ properties

	/**
	 * Utils objet
	 *
	 * @var     object
	 * @access  private
	 */
	var $_ut;

	/**
	 * Database access object
	 *
	 * @var     object
	 * @access  private
	 */
	var $_dt;

	// }}}

	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function items_A()
	{
		$this->_ut = new Utils();
		$this->_dt = new ItemsBase_A();
	}
	// }}}

	// {{{ start()
	/**
	 * Start the connexion processus
	 *
	 * @access public
	 * @return void
	 */
	function start($page)
	{
		switch ($page)
		{
	  // Display the list of items
			case WBS_SECTOR_ADMIN:
				utvars::setSectorId(WBS_SECTOR_ADMIN);
			case WBS_ACT_ITEMS:
				$this->_displayFormFees();
				break;
				// Confirm the Deletion
			case KID_CONFIRM:
				$this->_displayFormConfirm();
				break;
				// Delete the selected items
			case KID_DELETE:
				$this->_delItems();
				break;
				// Creation of a new itew
			case KID_NEW :
				$this->_displayFormItem();
				break;
				// Modification of an existing item
			case KID_EDIT :
				$id = kform::getData();
				$this->_displayFormItem($id);
				break;
			case ITEM_LIST_PDF :
				require_once "pdf/pdfbarcod.php";
				$pdf = new pdfBarCod();
				$pdf->start();
				$pdf->list_items();
				$pdf->end();
				break;
			case ITEM_SPECIALCODE_PDF :
				require_once "pdf/pdfbarcod.php";
				$pdf = new pdfBarCod();
				$pdf->start();
				$pdf->special_codes();
				$pdf->end();
				break;
			case KID_SELECT:
				$this->_displayFormSelectItems();
				break;
			case ITEM_SELECTED:
				$this->_selectItems();
				break;

			case ITEM_HOTELS:
			case WBS_ACT_ACCOMODATION:
				$this->_displayFormHotels();
				break;
			case ITEM_HOTEL:
				$this->_displayFormHotel();
				break;
			case ITEM_NEW_HOTEL:
				$this->_displayFormEditHotel();
				break;
			case ITEM_EDIT_HOTEL:
				$id = kform::getData();
				$this->_displayFormEditHotel($id);
				break;
			case ITEM_UPDATE_HOTEL:
				$this->_updateHotel();
				break;
			case ITEM_OTHERS:
				$this->_displayFormList();
				break;
			case ITEM_COMMANDS:
				$id = kform::getData();
				$this->_itemsCommand($id);
				break;

			case KID_CONFIDENT:
				$this->_publItems(WBS_DATA_CONFIDENT);
				break;
			case KID_PRIVATE:
				$this->_publItems(WBS_DATA_PRIVATE);
				break;
			case KID_PUBLISHED:
				$this->_publItems(WBS_DATA_PUBLIC);
				break;
			case ITEM_STATS:
				$this-> _displayFormStats($err='');
				break;
				// Register the modification of the item
			case KID_UPDATE:
				$this->_updateItem();
				break;

			default:
				echo "page $page demand�e depuis items_A<br>";
				exit();
		}
	}
	// }}}


	// {{{ _selectItems()
	/**
	 * Selct the selected items in the database
	 *
	 * @access private
	 * @return void
	 */
	function _selectItems()
	{
		$dt = $this->_dt;

		// Get the id's of the items to select
		$ids = kform::getInput('rowsItems');

		// Select the items
		$res = $dt->selectItems($ids);
		if (is_array($res))
		{
			$err['errMsg'] = $res['errMsg'];
			$this->_displayFormSelectItems($err);
		}
		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}


	// {{{ _delItems()
	/**
	 * Delete the selected items in the database
	 *
	 * @access private
	 * @return void
	 */
	function _delItems()
	{
		$dt = $this->_dt;

		// Get the id's of the hotels to delete
		$ids = kform::getInput('rowsItems');
		// Delete the items
		$res = $dt->delItems($ids);
		if (is_array($res))
		{
			$err['errMsg'] = $res['errMsg'];
			$this->_displayFormConfirm($err);
		}

		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _publItems()
	/**
	 * Change the status of selected items
	 *
	 * @access private
	 * @param array $member  info of the member
	 * @return void
	 */
	function _publItems($status)
	{
		$dt = $this->_dt;

		// Initialize the field
		$itemsId = kform::getInput("rowsItems", -1);
		if ($itemsId == -1)
		$err = 'msgNeedItems';
		else
		$err = $dt->publItems($itemsId, $status);
		$this->_displayFormList($err);
	}
	// }}}


	// {{{ _updateItem()
	/**
	 * Update item details in the database for the current tournament
	 *
	 * @access private
	 * @return void
	 */
	function _updateItem()
	{
		$dt = $this->_dt;

		// Get the informations
		$infos = array('item_id'       => kform::getInput('itemId'),
		     'item_name'     => kform::getInput('itemName'),
		     'item_code'     => kform::getInput('itemCode'),
		     'item_ref'      => kform::getInput('itemRef'),
		     'item_count'    => kform::getInput('itemCount'),
		     'item_isFollowed' => kform::getInput('itemIsFollowed'),
		     'item_isCreditable' => kform::getInput('itemIsCreditable'),
		     'item_rubrikId'   => WBS_RUBRIK_OTHERS,
		     'item_value'    => kform::getInput('itemValue'),
		     'item_slt'      => kform::getInput('itemIsInGrid'),
		     'item_rge'      => kform::getInput('itemOrderGrid'),
		     'item_eventId'  => utvars::getEventId()
		);

		// Control the informations
		if ($infos['item_name'] == "")
		{
	  $infos['errMsg'] = 'msgitemName';
	  $this->_displayFormItem();
		}
		if ($infos['item_value'] == "")
		{
	  $infos['errMsg'] = 'msgitemValue';
	  $this->_displayFormItem();
		}

		// Add the item in the database
		$res = $dt->updateItem($infos);
		if (is_array($res))
		{
			$err['errMsg'] = $res['errMsg'];
			$this->_displayFormItem($err);
		}
		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _displayFormConfirm()
	/**
	 * Display the page for confirmation the destruction of items
	 *
	 * @access private
	 * @param array $member  info of the member
	 * @return void
	 */
	function _displayFormConfirm($err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('items');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tDelItems', 'items', KID_DELETE);

		// Initialize the field
		$itemsId = kform::getInput("rowsItems");
		if (($itemsId != '') &&
		!isset($err['errMsg']))
		{
	  foreach($itemsId as $id)
	  $form->addHide("rowsItems[]", $id);
	  $form->addMsg('msgConfirmDel');
	  $form->addBtn('btnDelete', KAF_SUBMIT);
		}
		else
		if ($itemsId === '')
		$form->addWng('msgNeedItems');
		else
		$form->addWng($err['errMsg']);

		$form->addBtn('btnCancel');
		$elts = array('btnDelete', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormSelectItems()
	/**
	 * Display a page with the list of the items
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayFormSelectItems($err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('items');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tSelectItem', 'items', ITEM_SELECTED);

		if (isset($err['errMsg']))
		$form->addWng($err['errMsg']);

		// List of items
		$sort = kform::getSort('rowsItems', 2);
		$rows = $dt->getItems($sort, WBS_RUBRIK_OTHERS);
		if (isset($rows['errMsg']))
		$form->addWng($rows['errMsg']);
		else
		{
			$krow =& $form->addRows('rowsItems', $rows);
	  $sizes = array(6=> 0,0,0,0,0,0,0);
	  $krow->setSize($sizes);

	  $img[1]='icon';
	  $krow->setLogo($img);

	  $itemsId = array();
	  foreach($rows as $item)
	  if ($item['item_slt']) $itemsId[] = $item['item_id'];
	  if (count($itemsId))
	  $krow->setSelect($itemsId);

	  $form->addBtn('btnRegister', KAF_SUBMIT);
		}
		$form->addBtn('btnCancel');
		$elts  = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormStats()
	/**
	 * Display a page with the list of the items
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayFormStats($err='')
	{
		$dt = $this->_dt;


		$kdiv =& $this->_displayHead('itDailyStats');
		$form =& $kdiv->addForm('fItems');

		$form->addMsg('tDailyStats');
		if ($err != '')
		{
			$form->addWng($err);
		}
		$resas = $dt->getPurchaseStats();
		if (isset($resas['errMsg']))
		{
	  $kdiv->addWng($resas['errMsg']);
	  unset($resas['errMsg']);
		}
		$items = $dt->getSelectItems();
		if (isset($items['errMsg']))
		{
			$kdiv->addWng($items['errMsg']);
			unset ($items['errMsg']);
		}
		// Construire la table d'affichage
		$form =& $kdiv->addForm('fregi');
		$title[1] = "Date";
		$i = 2;
		//$klgd =& $kdiv->addDiv('blkLegende');
		$provision = array('', 'Stock');
		if (count($items) )
		{
			foreach($items as $item)
			{
				$title[$i++] = $item['item_code'];
				$form->addInfo($item['item_code'].":", $item['item_name']);
				$elts[] = $item['item_code'].":";
				$provision[] = $item['item_count'];
			}
			$form->addBlock("blkLegende", $elts);
		}
		// Create a new menu
		$itsm['itSelectItems'] = array(KAF_NEWWIN, 'items', KID_SELECT,
		0, 650, 450);
		$form->addMenu('menuLeft', $itsm, -1);
		$form->addDiv('break', 'blkNewPage');

		$utd = new utdate();
		$nbItems = count($items);
		$date = '';
		$keys = array_keys($items);
		$lines=array();
		$total = array_fill(0, $nbItems+2, 0);
		foreach($resas as $resa)
		{
			if ($resa['cmd_date'] != $date)
			{
				if ($date != '')
				{
					$lines[] = $fulline;
				}
				$date = $resa['cmd_date'];
				$utd->setIsoDate($date);
				$line = array($date, $utd->getDateWithDay());
				$fulline  = array_pad($line, $nbItems+2, '');
			}
			$indx = array_keys($keys, $resa['item_id']);
			$fulline[$indx[0]+2] = $resa['nbCmd'];
			$total[$indx[0]+2] += $resa['nbCmd'];
		}
		if ($date != '')
		{
			$lines[] = $fulline;
		}
		$total[1]  = 'Consomm�';
		$lines[] = $total;
		$lines[] = $provision;
		$krow =& $form->addRows('stats', $lines);
		$krow->displaySelect(false);
		$krow->setTitles($title);
		$krow->setSort(0);


		//$krow =& $form->addRows('reservations', $resas);
		$this->_utPage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormFees()
	/**
	 * Display a page with the fees for entries
	 * @return void
	 */
	function _displayFormFees($err='')
	{
		$dt = $this->_dt;
		$content =& $this->_displayHead('itFees');

		require_once "utils/utfees.php";
		$utf = new utfees();
		$fees = $utf->getFees();
		$kdiv =& $content->addDiv('divFees', 'blkData');
		$kdiv->addMsg('tFees', '', 'kTitre');
		$kdiv->addInfo('IS',  sprintf('%.02f', $fees['IS']['item_value']));
		$isSquash = $this->_ut->getParam('issquash', false);
		if (!$isSquash)
		{
			$kdiv->addInfo('ID',  sprintf('%.02f', $fees['ID']['item_value']));
			$kdiv->addInfo('IM',  sprintf('%.02f', $fees['IM']['item_value']));
			$kdiv->addInfo('I1',  sprintf('%.02f', $fees['I1']['item_value']));
			$kdiv->addInfo('I2',  sprintf('%.02f', $fees['I2']['item_value']));
			$kdiv->addInfo('I3',  sprintf('%.02f', $fees['I3']['item_value']));
		}
		unset($items);
		$items['btnModify']  = array(KAF_NEWWIN, 'events', EVNT_FEES,
		0, 400, 300);
		$kdiv->addMenu("menuBtn", $items, -1, 'classMenuBtn');
			
		$kdiv =& $content->addDiv('divValues', 'blkData');
		$kdiv->addMsg('tValues', '', 'kTitre');
		$cmds = $utf->getCmds();
		$krow =& $kdiv->addRows('rowsFees', $cmds);
		$krow->displaySelect(false);
		$krow->displayNumber(false);
		$krow->setSort(0);


		$content->addDiv('break', 'blkNewPage');
		$this->_utPage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormHotels()
	/**
	 * Display a page with the list of the hotels
	 * @return void
	 */
	function _displayFormHotels($err='')
	{
		$dt = $this->_dt;
		$kdiv =& $this->_displayHeadHotel('itHotel');
		$form =& $kdiv->addForm('fItems');

		// Menu for management of hotel
		$items['itNew'] = array(KAF_NEWWIN, 'items',
		ITEM_NEW_HOTEL,  0, 500, 350);
		//$items['itErase'] = array(KAF_NEWWIN, 'items', KID_CONFIRM, 0,
		//				  300,150);
		$form->addMenu('menuAction', $items, -1);
		$form->addDiv('break', 'blkNewPage');

		if ($err != '')
		$form->addWng($err);

		// List of hotels
		$sort = kform::getSort('rowsItems', 2);
		$type = kform::getData();
		$rows = $dt->getItems($sort, WBS_RUBRIK_HOTEL);

		if (isset($rows['errMsg']))
		$form->addWng($rows['errMsg']);
		else
		{
	  $hotel = null;
	  $ut = new utils();
	  foreach($rows as $row)
	  {
	  	if($hotel != $row['item_name'])
	  	{
	  		$lines[] = array(KOD_BREAK, "title", $row['item_name'],'', $row['item_id'],
				   'action'=> array(KAF_UPLOAD, 'items', ITEM_HOTEL,$row['item_name']));
	  		$hotel = $row['item_name'];
	  	}
	  	$row['item_code'] = $ut->getLabel($row['item_code']);
	  	$row['item_ref'] = $ut->getLabel($row['item_ref']);
	  	$lines[] = $row;
	  }

	  $krow =& $form->addRows('rowsItems', $lines);
	  $sizes[1] = '0';
	  $sizes[5] = '0+';
	  $krow->setSize($sizes);
	  $krow->setSort(0);

	  $img[1]='icon';
	  $krow->setLogo($img);
	  //$krow->displaySelect(false);
	  $actions[1] = array(KAF_UPLOAD, 'items', ITEM_COMMANDS);
	  $krow->setActions($actions);

	  $acts = array();
	  $acts[] = array('link' => array(KAF_NEWWIN, 'items', KID_CONFIRM,
	  0, 300, 150),
			   'icon' => utimg::getIcon(WBS_ACT_DROP),
			   'title' => 'removeHotel');
	  $acts[] = array('link' => array(KAF_NEWWIN, 'items', ITEM_EDIT_HOTEL,
	  0, 500, 300),
			   'icon' => utimg::getIcon(WBS_ACT_EDIT),
			   'title' => 'editHotel');
	  $krow->setBreakActions($acts);

		}
		$this->_utPage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormHotel()
	/**
	 * Display a page with the room of an hotel
	 * @return void
	 */
	function _displayFormHotel($err='')
	{
		$dt = $this->_dt;
		$kdiv =& $this->_displayHeadHotel('itHotel');
		$form =& $kdiv->addForm('fItems');

		// List of hotels
		$hotels = $dt->getHotels();
		$hotel = kform::getData();
		$kcombo=& $form->addCombo('selectList', $hotels, $hotels[$hotel]);
		$acts[1] = array(KAF_UPLOAD, 'items', ITEM_HOTEL);
		$kcombo->setActions($acts);

		$rows = $dt->getRooms($hotel);
		if (isset($rows['errMsg']))
		$form->addWng($rows['errMsg']);
		else
		{
	  $date = null;
	  $ut = new utils();
	  foreach($rows as $row)
	  {
	  	if($date != $row['cmd_date'])
	  	{
	  		$lines[] = array(KOD_BREAK, "title", $row['cmd_date']);
	  		$date = $row['cmd_date'];
	  	}
	  	$lines[] = $row;
	  }

	  $krow =& $form->addRows('rowsRooms', $lines);
	  $sizes[5] = '0+';
	  $krow->setSize($sizes);
	  $krow->setSort(0);

	  $img[1]='icon';
	  //$krow->setLogo($img);
	  $krow->displaySelect(false);
	  $actions[1] = array(KAF_UPLOAD, 'items', ITEM_COMMANDS);
	  //$krow->setActions($actions);

	  $acts = array();
	  $acts[] = array('link' => array(KAF_NEWWIN, 'items', KID_CONFIRM,
	  0, 300, 150),
			   'icon' => utimg::getIcon(WBS_ACT_DROP),
			   'title' => 'removeHotel');
	  $acts[] = array('link' => array(KAF_NEWWIN, 'items', ITEM_EDIT_HOTEL,
	  0, 500, 300),
			   'icon' => utimg::getIcon(WBS_ACT_EDIT),
			   'title' => 'editHotel');
	  //$krow->setBreakActions($acts);

		}
		$this->_utPage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormList()
	/**
	 * Display a page with the list of the items
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayFormList($err='')
	{
		$dt = $this->_dt;
		$kdiv =& $this->_displayHead('itOthers');
		$form =& $kdiv->addForm('fItems');

		// Menu for management of hotel
		$items['itNew'] = array(KAF_NEWWIN, 'items',
		KID_NEW,  0, 450, 350);
		$items['itErase'] = array(KAF_NEWWIN, 'items', KID_CONFIRM, 0,
		300,150);
		$items['itListItem'] = array(KAF_NEWWIN, 'items', ITEM_LIST_PDF, 0,
		400,250);
		$items['itSpecialCode'] = array(KAF_NEWWIN, 'items',
		ITEM_SPECIALCODE_PDF, 0,
		400,250);
		$form->addMenu('menuLeft', $items, -1);
		$itsp['itPublic'] = array(KAF_VALID, 'items',
		KID_PUBLISHED);
		$itsp['itPrivate']   = array(KAF_VALID, 'items',
		KID_PRIVATE);
		$itsp['itConfident'] = array(KAF_VALID, 'items',
		KID_CONFIDENT);
		//$form->addMenu("menuRight", $itsp, -1);
		$form->addDiv('break', 'blkNewPage');

		if ($err != '')
		$form->addWng($err);

		// List of hotels
		$sort = kform::getSort('rowsItems', 2);
		$type = kform::getData();
		$rows = $dt->getItems($sort, WBS_RUBRIK_OTHERS);
		if (isset($rows['errMsg']))
		$form->addWng($rows['errMsg']);
		else
		{
			$krow =& $form->addRows('rowsItems', $rows);
	  $sizes = array(8=> 0,0,0,0);
	  $krow->setSize($sizes);

	  $img[1]='icon';
	  $krow->setLogo($img);
	  $actions[0] = array(KAF_NEWWIN, 'items', KID_EDIT, 0,
	  450, 350);
	  $actions[1] = array(KAF_UPLOAD, 'items', ITEM_COMMANDS);
	  $krow->setActions($actions);
		}

		// Legend
		$form->addImg('lgdConfident', utimg::getIcon(WBS_DATA_CONFIDENT));
		$form->addImg('lgdPrivate', utimg::getIcon(WBS_DATA_PRIVATE));
		$form->addImg('lgdPublic', utimg::getIcon(WBS_DATA_PUBLIC));
		$elts=array('lgdConfident', 'lgdPrivate', 'lgdPublic');
		$form->addBlock("blkLegende", $elts);
		$this->_utPage->display();
		exit;
	}
	// }}}

	// {{{ _itemsCommand()
	/**
	 * Display a page with the commands of the items
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _itemsCommand($itemId)
	{
		$dt = $this->_dt;

		$kdiv =& $this->_displayHead('itItems');
		$form =& $kdiv->addDiv('fItems');

		$item = $dt->getItemById($itemId);
		$form->addMsg('tItems', $item['item_name']);


		// List of commands
		$sort = kform::getSort('rowsCommands', 2);
		$rows = $dt->getCommands($itemId, $sort);
		if (isset($rows['errMsg']))
		$form->addWng($rows['errMsg']);
		else
		{
			$krow =& $form->addRows('rowsCommands', $rows);
	  $sizes = array(8=> 0,0,0,0);
	  //$krow->setSize($sizes);

	  $img[1]='icon';
	  //$krow->setLogo($img);
	  $actions = array( 0 => array(KAF_NEWWIN, 'items', KID_EDIT, 0,
	  450, 350));
	  //$krow->setActions($actions);
		}

		$this->_utPage->display();
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
	function & _displayHead($select)
	{
		$dt = $this->_dt;

		// Create a new page
		$this->_utPage = new utPage_A('items', true, 'itItems');
		$content =& $this->_utPage->getContentDiv();

		// Add a menu for different action
		$kdiv =& $content->addDiv('choix', 'onglet3');
		$items['itFees'] = array(KAF_UPLOAD, 'items', WBS_ACT_ITEMS);
		$items['itOthers'] = array(KAF_UPLOAD, 'items',ITEM_OTHERS);
		$items['itDailyStats'] = array(KAF_UPLOAD, 'items', ITEM_STATS);
		$kdiv->addMenu("menuType", $items, $select);
		$kdiv =& $content->addDiv('contType', 'cont3');
		return $kdiv;
	}
	// }}}

	// {{{ _displayHeadHotel()
	/**
	 * Display header of the page
	 *
	 * @access private
	 * @param array team  info of the team
	 * @return void
	 */
	function & _displayHeadHotel($select)
	{
		$dt = $this->_dt;

		// Create a new page
		$this->_utPage = new utPage_A('items', true, 'itAccomodation');
		$content =& $this->_utPage->getContentDiv();

		// Add a menu for different action
		$kdiv =& $content->addDiv('choix', 'onglet3');
		$items['itHotel'] = array(KAF_UPLOAD, 'items', WBS_ACT_ACCOMODATION);
		$items['itTeams'] = array(KAF_UPLOAD, 'teams', TEAM_ROOM);
		$kdiv->addMenu("menuType", $items, $select);
		$kdiv =& $content->addDiv('contType', 'cont3');
		return $kdiv;
	}
	// }}}

	// {{{ _displayFormItem()
	/**
	 * Display the form to create or modifiy an item
	 *
	 * @access private
	 * @param array $eventId  id of the event
	 * @return void
	 */
	function _displayFormItem($itemId='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('items');
		$content =& $utpage->getPage();

		// Initialize the field
		if (is_numeric($itemId))
		$infos = $dt->getItemById($itemId);
		else
		$infos = array('item_id'       => -1,
		       'item_name'     => kform::getInput('itemName'),
		       'item_code'     => kform::getInput('itemCode'),
		       'item_ref'      => kform::getInput('itemRef'),
		       'item_count'    => kform::getInput('itemCount'),
		       'item_isFollowed' => kform::getInput('itemIsFollowed'),
		       'item_isCreditable' => kform::getInput('itemIsCreditable'),
		       'item_rubrikId'   => WBS_RUBRIK_OTHERS,
		       'item_value'    => kform::getInput('itemValue'),
		       'item_slt'      => kform::getInput('itemIsInGrid'),
		       'item_rge'      => kform::getInput('itemOrderGrid')
		);

		if ($infos['item_id'] != -1)
		$form =& $content->addForm('tEditItem', 'items', KID_UPDATE);
		else
		$form =& $content->addForm('tNewItem', 'items', KID_UPDATE);

		// Display warning if exist
		if (isset($infos['errMsg']))
		$form->addWng($infos['errMsg']);

		// Display information of draws
		//      $form->addHide("serial_oldName",   $infos['serial_oldName']);
		// Initialize the field
		$form->addHide('itemId', $infos['item_id']);
		$kedit =& $form->addEdit('itemName',  $infos['item_name'], 25);
		$kedit->setMaxLength(30);
		$kedit =& $form->addEdit('itemValue', $infos['item_value'], 25);
		$kedit->setMaxLength(10);
		$kedit =& $form->addEdit('itemCode',  $infos['item_code'], 25);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('itemRef',   $infos['item_ref'], 25);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('itemCount', $infos['item_count'], 25);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('itemOrderGrid', $infos['item_rge'], 25);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kcheck =& $form->addCheck('itemIsFollowed',
		$infos['item_isFollowed']);
		$kcheck =& $form->addCheck('itemIsInGrid',
		$infos['item_slt']);
		$kcheck =& $form->addCheck('itemIsCreditable',
		$infos['item_isCreditable']);

		$elts  = array('itemName', 'itemValue', 'itemCode',
		     'itemRef', 'itemCount', 'itemOrderGrid',
		     'itemIsInGrid', 'itemIsFollowed', 'itemIsCreditable');
		$form->addBlock('blkItem', $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts  = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the form
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _updateHotel()
	/**
	 * Update hotel
	 *
	 * @return void
	 */
	function _updateHotel()
	{
		$dt = $this->_dt;
		$infos = array('item_name'     => kform::getInput('hotelName'),
		     'item_valueSW'  => kform::getInput('priceSW',0),
		     'item_valueDW'  => kform::getInput('priceDW',0),
		     'item_valueTW'  => kform::getInput('priceTW',0),
		     'item_valueOW'  => kform::getInput('priceOW',0),
		     'item_valueSWE'  => kform::getInput('priceSWE',0),
		     'item_valueDWE'  => kform::getInput('priceDWE',0),
		     'item_valueTWE'  => kform::getInput('priceTWE',0),
		     'item_valueOWE'  => kform::getInput('priceOWE',0),
		);
		// Add the item in the database
		$res = $dt->updateHotel($infos);
		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _displayFormHotels()
	/**
	 * Display the form to create or modifiy an hotel
	 *
	 * @return void
	 */
	function _displayFormEditHotel($itemId='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('items');
		$content =& $utpage->getPage();

		// Initialize the field
		if (is_numeric($itemId))
		$infos = $dt->getHotel($itemId);
		else
		$infos = array('item_id'       => -1,
		       'item_name'     => kform::getInput('hotelName'),
		       'item_valueSW'  => kform::getInput('priceSW',0),
		       'item_valueDW'  => kform::getInput('priceDW',0),
		       'item_valueTW'  => kform::getInput('priceTW',0),
		       'item_valueOW'  => kform::getInput('priceOW',0),
		       'item_valueSWE'  => kform::getInput('priceSWE',0),
		       'item_valueDWE'  => kform::getInput('priceDWE',0),
		       'item_valueTWE'  => kform::getInput('priceTWE',0),
		       'item_valueOWE'  => kform::getInput('priceOWE',0),
		);

		if ($infos['item_id'] != -1)
		$form =& $content->addForm('tEditHotel', 'items', ITEM_UPDATE_HOTEL);
		else
		$form =& $content->addForm('tNewHotel', 'items', ITEM_UPDATE_HOTEL);

		// Display warning if exist
		if (isset($infos['errMsg']))
		$form->addWng($infos['errMsg']);

		// Display information of draws
		// Initialize the field
		$form->addHide('itemId', $infos['item_id']);
		$kedit =& $form->addEdit('hotelName',  $infos['item_name'], 25);
		$kedit->setMaxLength(30);
		$kedit =& $form->addEdit('priceSW', $infos['item_valueSW'], 10);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('priceDW', $infos['item_valueDW'], 10);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('priceTW', $infos['item_valueTW'], 10);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('priceOW', $infos['item_valueOW'], 10);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('priceSWE', $infos['item_valueSWE'], 10);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('priceDWE', $infos['item_valueDWE'], 10);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('priceTWE', $infos['item_valueTWE'], 10);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('priceOWE', $infos['item_valueOWE'], 10);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();

		$elts  = array('itemName', 'itemValue', 'itemCode');
		$form->addBlock('blkHotel', $elts);
		$elts  = array('priceSW', 'priceDW', 'priceTW', 'priceOW');
		$form->addBlock('blkPriceW', $elts);
		$elts  = array('priceSWE', 'priceDWE', 'priceTWE', 'priceOWE');
		$form->addBlock('blkPriceWE', $elts);
		$form->addDiv('break', 'blkNewPage');

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts  = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the form
		$utpage->display();
		exit;
	}
	// }}}
}

?>