<?php
/*****************************************************************************
 !   Module     : Purchase
 !   File       : $Source: /cvsroot/aotb/badnet/src/purchase/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.10 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:25 $
 ******************************************************************************/

require_once "utils/utbase.php";

/**
 * Acces to the dababase for purchase
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class purchaseBase_A extends utbase
{

	// {{{ properties
	// }}}

	// {{{ GetDeparture
	/**
	 * Date de depart
	 *
	 */
	function getDeparture($regiId)
	{
		$where = "regi_id = $regiId";
		$date = $this->_selectFirst('registration', 'regi_departure', $where);
		if (!is_null($date))
		{
	  $utd = new utdate();
	  $utd->setIsoDateTime($date);
	  return $utd->getDateTime();
		}
		else
		return '';
	}
	// }}}

	// {{{ GetArrival
	/**
	* Verify the item
	*
	* @access public
	* @param  string  $memberId  id of the member
	* @return array   information of the member if any
	*/
	function getArrival($regiId)
	{
		$where = "regi_id = $regiId";
		$date = $this->_selectFirst('registration', 'regi_arrival', $where);
		if (!is_null($date))
		{
	  $utd = new utdate();
	  $utd->setIsoDateTime($date);
	  return $utd->getDateTime();
		}
		else
		return '';
	}
	// }}}


	// {{{ existItem
	/**
	 * Verify the item
	 *
	 * @access public
	 * @param  string  $memberId  id of the member
	 * @return array   information of the member if any
	 */
	function existItem($itemId)
	{
		$fields = array('item_id');
		$tables = array('items');
		$where = "item_id = $itemId";
		$res = $this->_select($tables, $fields, $where);
		return $res->numRows();
	}
	// }}}

	// {{{ existRegi
	/**
	 * Verify the registration
	 *
	 * @access public
	 * @param  string  $memberId  id of the member
	 * @return array   information of the member if any
	 */
	function existRegi($regiId)
	{
		$fields = array('regi_id');
		$tables = array('registration');
		$where = "regi_id = $regiId";
		$res = $this->_select($tables, $fields, $where);
		return $res->numRows();
	}
	// }}}


	// {{{ getCmdItems
	/**
	 * Return the column of a member
	 *
	 * @access public
	 * @param  string  $memberId  id of the member
	 * @return array   information of the member if any
	 */
	function getCmdItems($itemIds)
	{
		$ids = implode(',', $itemIds);
		$fields = array('item_id', 'item_name', 'item_value',
		      'item_isCreditable');
		$tables = array('items');
		$where = "item_id IN (".$ids.")";
		$res = $this->_select($tables, $fields, $where);

		while ($item =  $res->fetchRow(DB_FETCHMODE_ASSOC))
		$row[$item['item_id']]=$item;
		return $row;
	}
	// }}}

	// {{{ getRegiData
	/**
	 * Return the column of a member
	 *
	 * @access public
	 * @param  string  $memberId  id of the member
	 * @return array   information of the member if any
	 */
	function getRegiData($regiId, $date)
	{
		$ut = new utils();
		$utd = new utdate();
		$utd->setFrDate($date);

		$fields = array('regi_id', 'regi_longName', 'regi_teamId', 'regi_type',
		      'regi_accountId', 'cunt_name', 'team_name',
		      "sum(cmd_value - cmd_discount - cmd_payed) as cuntSolde");
		$tables = array('teams', 'registration LEFT JOIN accounts ON
            cunt_id = regi_accountId LEFT JOIN commands ON
            cmd_accountId = cunt_id');
		$where = "regi_id = $regiId".
	" AND regi_teamId = team_id".
	" GROUP BY regi_id";
		$res = $this->_select($tables, $fields, $where);
		$player =  $res->fetchRow(DB_FETCHMODE_ASSOC);
		$player['regi_type'] = $ut->getLabel($player['regi_type']);

		// Solde de l'inscrit
		$fields = array("sum(cmd_value - cmd_discount - cmd_payed) as solde");
		$tables = array('registration LEFT JOIN commands ON
            cmd_regiId = regi_id');
		$where = "regi_id = $regiId".
	" GROUP BY regi_id";
		$res = $this->_select($tables, $fields, $where);
		$solde = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$player['regiSolde'] = $solde['solde'];

		// Provisions de l'inscrit
		$fields = array("sum(cmd_payed) as prov", "sum(cmd_discount) as rest");
		$tables = array('commands');
		$where = "cmd_regiId = $regiId".
        " AND cmd_itemId < 0".
        " AND (cmd_date ='".$utd->getIsoDateTime()."'".
        " OR cmd_value = cmd_payed)";
	" GROUP BY regi_id";
		$res = $this->_select($tables, $fields, $where);
		$solde = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$player['regiProv'] = 0+$solde['prov'];
		$player['regiRest'] = 0+$solde['rest'];

		// Provisions du compte
		$fields = array("sum(cmd_discount) as rest",
		      "sum(cmd_payed) as prov");
		$table = 'registration LEFT JOIN accounts ON'.
	' cunt_id = regi_accountId LEFT JOIN commands ON'.
	' cmd_accountId = cunt_id AND cmd_itemId < 0'.
	' AND cmd_regiId = -1 '.
	" AND (cmd_date ='".$utd->getIsoDateTime()."'".
	' OR cmd_value = cmd_payed)';
		$tables = array($table);
		$where = "regi_id = $regiId".
	" GROUP BY regi_id";
		$res = $this->_select($tables, $fields, $where);
		$solde = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$player['cuntProv'] = $solde['prov'];
		$player['cuntRest'] = $solde['rest'];


		return $player;
	}
	// }}}

	// {{{ getMembers
	/**
	 * Return the field of one purchase
	 *
	 * @access public
	 * @param  string  $itemId  id of the item
	 * @return array   information of the item if any
	 */
	function getMembers()
	{
		$fields = array('regi_id', 'regi_longName');
		$tables = array('registration', 'members');
		$where = "mber_id = regi_memberId".
	" AND mber_id >= 0".
	" AND regi_eventId =".utvars::getEventId();
		$order = 'regi_longName';
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = 'msgNoMembers';
	  return $err;
		}
		while ($infos = $res->fetchRow(DB_FETCHMODE_ORDERED))
		$rows[$infos[0]] = $infos[1];
		return $rows;
	}
	// }}}

	// {{{ getPurchase
	/**
	 * Return the field of one purchase
	 *
	 * @access public
	 * @param  string  $itemId  id of the item
	 * @return array   information of the item if any
	 */
	function getPurchase($purchaseId)
	{
		$fields = array('cmd_id', 'cmd_name', 'cmd_value', 'cmd_date',
		      'cmd_regiId', 'cmd_accountId', 'cmd_itemId', 
		      'cmd_discount', 'cmd_payed', 'cmd_cmt', 'cmd_type',
		      'item_name', 'item_code', 'item_ref', 'item_rubrikId');
		$tables = array('commands', 'items');
		$where = "cmd_id=$purchaseId".
	" AND cmd_itemId=item_id";

		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		{
	  $err['errMsg'] = 'msgNoPurchase';
	  return $err;
		}

		$infos = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$utd = new utdate();
		$utd->setIsoDate($infos['cmd_date']);
		$infos['cmd_date'] = $utd->getDate();
		return $infos;
	}
	// }}}

	// {{{ getItem
	/**
	 * Return the field of one item
	 *
	 * @access public
	 * @param  string  $itemId  id of the item
	 * @return array   information of the item if any
	 */
	function getItem($itemId)
	{
		$fields = array('item_id', 'item_name', 'item_value');
		$tables = array('items');
		$where = "item_id=$itemId";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		{
	  $err['errMsg'] = 'msgNoItem';
	  return $err;
		}

		return $res->fetchRow(DB_FETCHMODE_ASSOC);
	}
	// }}}

	// {{{ getItems
	/**
	 * Return the list of items
	 *
	 * @access public
	 * @param  string  $assoId  id of the member
	 * @return array   information of the member if any
	 */
	function getItems($isCredit=false)
	{
		$fields = array('item_id', 'item_name', 'item_code', 'item_ref',
       'item_rubrikId');
		$tables = array('items');
		$where = "item_eventId=".utvars::getEventId().
                 " AND item_rubrikId!=".WBS_RUBRIK_HOTEL .
				' AND (item_count>0 OR item_isFollowed = 0)';
		if ($isCredit)
		{
			$where .=" AND item_isCreditable=1";
		}
		$order = "item_name, item_code, item_ref";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = 'msgNoItems';
	  return $err;
		}
		$ut = new utils();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  if ($entry['item_rubrikId'] == WBS_RUBRIK_HOTEL)
	  $rows[$entry['item_id']] = $entry['item_name'].'-'.
	  $ut->getSmaLabel($entry['item_code']).'-'.
	  $ut->getSmaLabel($entry['item_ref']);
	  else
	  $rows[$entry['item_id']] = $entry['item_name'];
		}
		return $rows;
	}
	// }}}

	// {{{ getHotels
	/**
	 * Return the list of hotels
	 *
	 * @access public
	 * @return array
	 */
	function getHotels()
	{
		$eventId = utvars::getEventId();
		$where = "item_rubrikId=".WBS_RUBRIK_HOTEL
		. ' AND item_eventid='. $eventId;
		$order = "item_name";
		$res = $this->_select('items', 'DISTINCT item_name', $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = 'msgNoItems';
	  return $err;
		}
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $hotels[$entry['item_name']] = $entry['item_name'];
		}
		return $hotels;
	}
	// }}}

	// {{{ reserveHotel
	/**
	 * Create command for hotel reservation
	 *
	 * @access public
	 * @return array
	 */
	function reserveHotel($data)
	{

		$where = "regi_id={$data['regiId']}";
		$cmd['cmd_accountId'] = $this->_selectFirst('registration', 'regi_accountId', $where);
		$cmd['cmd_regiId'] = $data['regiId'];

		$ut = new utils();
		$utd = new utdate();
		$utd->setFrDate($data['arrival']);

		$fields = array('item_id', 'item_name', 'item_code',
		      'item_ref', 'item_value');
		for($i=0; $i<$data['nbNight']; $i++)
		{

	  $where = "item_code={$data['itemCode']}".
	    " AND item_name='{$data['hotel']}'";
	  if($utd->isWeekEnd())
	  $where .= " AND item_ref=".WBS_PRICE_WE;
	  else
	  $where .= " AND item_ref=".WBS_PRICE_WEEK;
	  $res = $this->_select('items', $fields, $where);
	  $item = $res->fetchRow(DB_FETCHMODE_ASSOC);

	  $cmd['cmd_date'] = $utd->getIsoDateTime();
	  $cmd['cmd_itemId'] = $item['item_id'];
	  $cmd['cmd_name'] = $item['item_name'].'-'.
	  $ut->getSmaLabel($item['item_code']).'-'.
	  $ut->getSmaLabel($item['item_ref']);
	  $cmd['cmd_value'] = $item['item_value'];
	  if ($data['isfree'])
	  $cmd['cmd_discount'] = $item['item_value'];
	  $cmd['cmd_cmt'] = $data['room'];
	  $this->_insert('commands', $cmd);
	  $utd->addMinute(60*24);
		}
		// Mettre a jour la date d'arrivee et de depart
		$where = 'regi_id='.$data['regiId'];
		$utd->setFrDate($data['arrival']);
		$regi['regi_arrival'] = $utd->getIsoDateTime();
		$utd->setFrDate($data['departure']);
		$regi['regi_departure'] = $utd->getIsoDateTime();
		$res = $this->_update('registration', $regi, $where);

		return;
	}
	// }}}

	// {{{ getPriceRoom
	/**
	 * Return the price of a room
	 *
	 * @access public
	 * @return array
	 */
	function getPriceRoom($hotel, $room, $day)
	{
		$hotel = addslashes($hotel);
		$utd = new utdate();
		$utd->setFrDate($day);

		$fields = array('item_id', 'item_value');
		$where = "item_code={$room}".
	    " AND item_name='{$hotel}'";
		if($utd->isWeekEnd())
		$where .= " AND item_ref=".WBS_PRICE_WE;
		else
		$where .= " AND item_ref=".WBS_PRICE_WEEK;
		$res = $this->_select('items', $fields, $where);
		return $res->fetchRow(DB_FETCHMODE_ASSOC);
	}
	// }}}

	// {{{ updateRoom
	/**
	 * Modify room reservation
	 *
	 * @access public
	 * @return array
	 */
	function updateRoom($data)
	{
		$ut = new utils();
		$utd = new utdate();
		$utd->setFrDate($data['cmd_date']);

		$fields = array('item_id', 'item_name', 'item_code',
		      'item_ref', 'item_value');
		$where = "item_code={$data['cmd_code']}".
	" AND item_name='{$data['cmd_name']}'";
		if($utd->isWeekEnd())
		$where .= " AND item_ref=".WBS_PRICE_WE;
		else
		$where .= " AND item_ref=".WBS_PRICE_WEEK;
		$res = $this->_select('items', $fields, $where);
		$item = $res->fetchRow(DB_FETCHMODE_ASSOC);

		$cmd['cmd_date'] = $utd->getIsoDateTime();
		$cmd['cmd_itemId'] = $item['item_id'];
		$cmd['cmd_name'] = $item['item_name'].'-'.
		$ut->getSmaLabel($item['item_code']).'-'.
		$ut->getSmaLabel($item['item_ref']);
		$cmd['cmd_value'] = $data['cmd_value'];
		$cmd['cmd_discount'] = $data['cmd_discount'];
		$cmd['cmd_payed'] = $data['cmd_payed'];
		$cmd['cmd_type'] = $data['cmd_type'];
		$cmd['cmd_cmt'] = $data['cmd_cmt'];
		$where = "cmd_id={$data['cmd_id']}";
		$this->_update('commands', $cmd, $where);
		return;
	}
	// }}}

	// {{{ updateRoomNum
	/**
	 * Modify room number
	 *
	 * @access public
	 * @return array
	 */
	function updateRoomNum($cmdId, $numRoom)
	{
		// Get registration number
		$fields = array('cmd_regiId', 'item_code', 'item_name');
		$tables = array('items', 'commands');
		$where = "cmd_id=$cmdId".
	" AND cmd_itemId = item_id";
		$res = $this->_select($tables, $fields, $where);
		$regi = $res->fetchRow(DB_FETCHMODE_ASSOC);

		// Get commands to update
		$tables = array('items', 'commands');
		$where = "cmd_regiId={$regi['cmd_regiId']}".
	" AND cmd_itemId = item_id".
	" AND item_code = {$regi['item_code']}".
	" AND item_name = '{$regi['item_name']}'";
		$res = $this->_select($tables, 'cmd_id', $where);

		// Update commands
		if ($res->numRows())
		{
	  while($cmd = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  $ids[] = $cmd['cmd_id'];
	  $cmdIds = implode(',', $ids);

	  // Update rooms number
	  $cmd['cmd_cmt'] = $numRoom;
	  $where = "cmd_id IN ($cmdIds)";
	  $this->_update('commands', $cmd, $where);
		}
		return;
	}
	// }}}

	// {{{ updatePurchase
	/**
	 * Add or update an purchase  into the database
	 *
	 * @access public
	 * @param  string  $info   column of the purchase
	 * @return mixed
	 */
	function updatePurchase($infos)
	{
		$utd = new utdate();
		$utd->setFrDate($infos['cmd_date']);
		$infos['cmd_date'] = $utd->getIsoDateTime();

		// Mettre a jour le compte avec celui du joueur
		if ($infos['cmd_regiId']!=-1)
		{
			$fields = array('regi_accountId');
			$tables = array('registration');
			$where = "regi_id=".$infos['cmd_regiId'];
			$res = $this->_select($tables, $fields, $where);
			if ($res->numRows())
			{
				$regi = $res->fetchRow(DB_FETCHMODE_ORDERED);
				$infos['cmd_accountId'] = $regi[0];
			}
		}
		if ($infos['cmd_accountId']==-1)
		{
			$err['errMsg'] = "msgnoAccount";
			return $err;
		}

		// Recherche si l'inscrit a des provisions
		// si l'article peut etre achete a credit !!!
		$due = $infos['cmd_value']- $infos['cmd_discount']-
		$infos['cmd_payed'];
		$fields = array('item_isCreditable');
		$tables = array('items');
		$where = "item_id=".$infos['cmd_itemId'];
		$res = $this->_select($tables, $fields, $where);
		$isCreditable = false;
		if ( $res->numRows() )
		{
			$tmp = $res->fetchRow(DB_FETCHMODE_ORDERED);
			$isCreditable  = $tmp[0];
		}
		if ( $due > 0 && $isCreditable)
		{
			$fields = array('cmd_id', 'cmd_discount', 'cmd_value', 'cmd_payed');
			$tables = array('commands');
			$where = "cmd_itemId = -1".
    " AND cmd_discount > 0".
	    " AND cmd_date='".$infos['cmd_date']."'".
	    " AND cmd_accountId=".$infos['cmd_accountId'].
	    " AND (cmd_regiId=".$infos['cmd_regiId'].
	    " OR cmd_regiId=-1)";
			$order = "cmd_regiId DESC";
			$res = $this->_select($tables, $fields, $where, $order);
			while (($credit = $res->fetchRow(DB_FETCHMODE_ASSOC)) && ($due > 0))
			{
				$discount = min($credit['cmd_discount'], $due);
				$due -= $discount;
				$infos['cmd_discount'] += $discount;
				$credit['cmd_discount'] -= $discount;
				if ($credit['cmd_value'] != $credit['cmd_payed']) $credit['cmd_value'] -= $discount;
				$where = "cmd_id=".$credit['cmd_id'];
				$res2 = $this->_update('commands', $credit, $where);
			}
		}

		// Mettre a jour le stock du nouvel article
		if ($infos['cmd_itemId'] != -1)
		{
			$fields = array('item_count');
			$tables = array('items');
			$where = "item_id =".$infos['cmd_itemId'];
			$res = $this->_select($tables, $fields, $where);
			$item = $res->fetchRow(DB_FETCHMODE_ASSOC);
			$nb     = $item['item_count']-1;

			$fields=array();
			$fields['item_count'] = $nb;
			$where = "item_id =".$infos['cmd_itemId'].
            " AND item_isFollowed = 1".
	        " AND item_count > 0";	    
			$res = $this->_update('items', $fields, $where);
		}
		// Mise a jour de l'achat
		if ($infos['cmd_id'] != -1)
		{
			$where = "cmd_id=".$infos['cmd_id'];
			$res = $this->_update('commands', $infos, $where);
		}
		else
		{
			//print_r($infos);return;
			unset($infos['cmd_id']);
			$res = $this->_insert('commands', $infos);
			$infos['cmd_id'] = $res;
		}
		return $infos['cmd_id'];
	}
	// }}}


	// {{{ updateCredit
	/**
	 * Add or update an purchase  into the database
	 *
	 * @access public
	 * @param  string  $info   column of the purchase
	 * @return mixed
	 */
	function updateCredit($infos, $aNbjour, $aRegiIds)
	{
		$utd = new utdate();
		$utd->setFrDate($infos['cmd_date']);
		$infos['cmd_date'] = $utd->getIsoDateTime();

		if(!count($aRegiIds)) $aRegiIds[] = $infos['cmd_regiId'];
		foreach ($aRegiIds as $regiId)
		{
			// Mettre a jour le compte avec celui du joueur
			if ($regiId > 0)
			{
				$infos['cmd_regiId'] = $regiId;
				$fields = array('regi_accountId');
				$tables = array('registration');
				$where = "regi_id=".$infos['cmd_regiId'];
				$res = $this->_select($tables, $fields, $where);
				if ($res->numRows())
				{
					$regi = $res->fetchRow(DB_FETCHMODE_ORDERED);
					$infos['cmd_accountId'] = $regi[0];
				}
			}
			if ($infos['cmd_accountId']==-1)
			{
				$err['errMsg'] = "msgnoAccount";
				return $err;
			}

			// Recherche des achats effectue et
			// pouvant etre achete a credit pour mettre a jour la remise
			// Les achats non regles sont mis a jour en premier
			$fields = array('cmd_id', 'cmd_value', 'cmd_discount', 'cmd_payed');
			$tables = array('items', 'commands');
			$where = "item_id=cmd_itemId".
	" AND item_isCreditable = 1".
	" AND cmd_date='".$infos['cmd_date']."'";
			if ($regiId > 0) $where .= " AND cmd_regiId=" . $regiId;
			else $where .= " AND cmd_accountId=".$infos['cmd_accountId'];
			$order = 'cmd_payed';
			$res = $this->_select($tables, $fields, $where, $order);
			while (($purch = $res->fetchRow(DB_FETCHMODE_ASSOC)) &&	($infos['cmd_discount'] > 0))
			{
				$due = $purch['cmd_value'] - $purch['cmd_discount'];
				$discount = min($due, $infos['cmd_discount']);
				$purch['cmd_discount'] += $discount;
				$infos['cmd_discount'] -= $discount;
				if ($infos['cmd_value'] > $infos['cmd_payed']) $infos['cmd_value'] -= $discount;

				$where = "cmd_id=".$purch['cmd_id'];
				$res2 = $this->_update('commands', $purch, $where);
			}

			unset($infos['cmd_id']);
			$res = $this->_insert('commands', $infos);
			$infos['cmd_id'] = $res;
		}
		return $infos['cmd_id'];
	}
	// }}}

	// {{{ delPurchases
	/**
	 * Delete some accounts
	 *
	 * @access public
	 * @param  arrays  $assos   id's of the associations to delete
	 * @return mixed
	 */
	function delPurchases($ids)
	{

		foreach ($ids as $cmdId)
		{
	  // chercher l'achat
	  $fields = array('cmd_id', 'cmd_value', 'cmd_discount',
			  'cmd_payed', 'cmd_regiId', 'cmd_accountId',
			  'cmd_date', 'cmd_itemId');
	  $tables = array('commands');
	  $where = "cmd_id=$cmdId";
	  $res = $this->_select($tables, $fields, $where);
	  if (!$res->numRows()) continue;
	  $cmd = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $due =$cmd['cmd_discount'];

	  // Chercher les provisions
	  $fields = array('cmd_id', 'cmd_discount', 'cmd_value', 'cmd_payed');
	  $tables = array('commands');
	  $where = "cmd_itemId = -1".
	    " AND cmd_discount < cmd_payed".
	    " AND cmd_date='".$cmd['cmd_date']."'".
	    " AND (cmd_regiId=".$cmd['cmd_regiId'].
	    " OR cmd_regiId=-1)";
	  $order = "cmd_regiId DESC";
	  $res = $this->_select($tables, $fields, $where, $order);

	  while (($prov = $res->fetchRow(DB_FETCHMODE_ASSOC)) &&
		 $due )
		 {
		 	$discount = min($due,
		 	$prov['cmd_payed']-$prov['cmd_discount']);
		 	$due -= $discount;
		 	$prov['cmd_discount'] += $discount;
		 	if ($prov['cmd_value']!=$prov['cmd_payed'])
		 	$prov['cmd_value'] += $discount;
		 	$where = "cmd_id =".$prov['cmd_id'];
		 	$res2 = $this->_update('commands', $prov, $where);
		 	 
		 }

	  // Mettre a jour le stock de l'article
	  if ($cmd['cmd_itemId'] != -1)
	  {
	  	$fields = array('item_count');
	  	$tables = array('commands', 'items');
	  	$where = "cmd_id =$cmdId".
		" AND cmd_itemId = item_id";
	  	$res = $this->_select($tables, $fields, $where);

	  	$tmp = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	$nb     = $tmp['item_count']+1;
	  	 
	  	$fields=array();
	  	$fields['item_count'] = $nb;
	  	$where = "item_id =".$cmd['cmd_itemId'].
		" AND item_isFollowed=1";
	  	 
	  	$res = $this->_update('items', $fields, $where);
	  }

	  // Supprimer l'achat
	  $where = "cmd_id =$cmdId";
	  $res = $this->_delete('commands', $where);
		}
		return true;
	}
	// }}}

	// {{{ delCredits
	/**
	 * Delete some credits
	 *
	 * @access public
	 * @param  arrays  $assos   id's of the associations to delete
	 * @return mixed
	 */
	function delCredits($ids)
	{

		foreach ($ids as $cmdId)
		{
	  // chercher le credit
	  $fields = array('cmd_id', 'cmd_value', 'cmd_discount',
			  'cmd_payed', 'cmd_regiId', 'cmd_accountId',
			  'cmd_date');
	  $tables = array('commands');
	  $where = "cmd_id=$cmdId";
	  $res = $this->_select($tables, $fields, $where);
	  if (!$res->numRows()) continue;
	  $cmd = $res->fetchRow(DB_FETCHMODE_ASSOC);

	  // Recherche des achats effectue et
	  // pouvant etre achete a credit pour mettre a jour la remise
	  // Les achats non regles sont mis a jour en premier
	  $fields = array('cmd_id', 'cmd_value', 'cmd_discount',
			  'cmd_payed');
	  $tables = array('items', 'commands');
	  $where = "item_id=cmd_itemId".
	    " AND item_isCreditable = 1".
	    " AND cmd_date='".$cmd['cmd_date']."'".
	    " AND cmd_discount > 0";
	  $order = 'cmd_payed';
	  if ($cmd['cmd_regiId']!=-1)
	  $where .=  " AND cmd_regiId=".$cmd['cmd_regiId'];
	  else
	  {
	  	$where .=  " AND cmd_accountId=".$cmd['cmd_accountId'];
	  	$order = 'cmd_regiId, cmd_payed';
	  }
	  $res = $this->_select($tables, $fields, $where, $order);
	  $due = $cmd['cmd_payed'] - $cmd['cmd_discount'];
	  while (($purch = $res->fetchRow(DB_FETCHMODE_ASSOC)) &&
		 ($due > 0))
		 {
		 	$discount = min($due, $purch['cmd_discount']);
		 	$purch['cmd_discount'] -= $discount;
		 	$due -= $discount;

		 	$where = "cmd_id=".$purch['cmd_id'];
		 	$res2 = $this->_update('commands', $purch, $where);
		 }

	  $where = "cmd_id = $cmdId";
	  $res = $this->_delete('commands', $where);
		}
		return true;
	}
	// }}}

	// {{{ deletePurchase
	/**
	 * Delete some purchase
	 *
	 * @access public
	 * @param  arrays  $assos   id's of the associations to delete
	 * @return mixed
	 */
	function getPurchasesIds($infos, $sort=1)
	{

		$utd = new utdate();
		$utd->setFrDate($infos['cmd_date']);

		$eventId = utvars::getEventId();
		$order = abs($sort);
		if ($sort < 0)
		$order .= ' DESC';
		$fields = array('cmd_id');
		$tables = array('commands',  'items', 'registration');
		$where  = "cmd_del != ".WBS_DATA_DELETE.
	" AND cmd_regiId = ".$infos['cmd_regiId'].
	" AND regi_eventId = $eventId".
	" AND cmd_itemId = ".$infos['cmd_itemId'].
	" AND cmd_date  = '".$utd->getIsoDateTime()."'".
	" AND item_slt = 1";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = "msgNoMembers";
	  return $err;
		}

		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED)){
	  $rows[] = $entry[0];
		}
		return $rows;
	}
	// }}}

	// {{{ getPurchaseList
	/**
	 * Return the list of the reservation
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort associations
	 * @return array   array of users
	 */
	function getPurchaseList($teamId, $date, $sort=1)
	{
		$utd = new utdate();
		$utd->setFrDate($date);

		$eventId = utvars::getEventId();
		$order = abs($sort);
		if ($sort < 0)
		$order .= ' DESC';
		$fields = array('regi_id', 'item_id');
		$tables = array('commands',  'items', 'registration');
		$where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_teamId = $teamId".
	" AND regi_Id = cmd_regiId".
	" AND regi_eventId = $eventId".
	" AND cmd_itemId = item_id".
	" AND cmd_date  = '".$utd->getIsoDateTime()."'".
	" AND item_slt = 1";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = "msgNoMembers";
	  return $err;
		}

		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED)){
	  $rows[$entry[0]][$entry[1]] = 1;
		}
		return $rows;
	}
	// }}}

	// {{{ getMembersNameId
	/**
	 * Return the list of the accounts
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort associations
	 * @return array   array of users
	 */
	function getMembersNameId($teamId, $sort=1)
	{
		$eventId = utvars::getEventId();
		$fields = array('regi_id', 'regi_longName', );
		$tables = array('members',  'registration');
		$where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_teamId = $teamId".
	" AND regi_memberId = mber_id ".
	" AND regi_eventId = $eventId".
	" AND mber_id >= 0";
		$order = 'mber_secondname';
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = "msgNoMembers";
	  return $err;
		}

		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED)){
	  $rows[$entry[0]] = $entry[1];
		}
		return $rows;
	}

	// {{{ getItemsCodes
	/**
	 * Return the list of the items' code
	 *
	 * @access public
	 * @param  none
	 * @return array   array of codes
	 */
	function getItemsCode($sort=1)
	{

		$fields = array('item_id, item_code'
		);
		$tables = array('items');
		$where = "item_slt = 1";
		$order = abs($sort);
		if ($sort < 0)
		$order .= ' DESC';
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $err['errMsg'] = "msgNoAccounts";
	  return $err;
		}

		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		$rows[$entry[0]] = $entry[1];
		return $rows;
	}
	// }}}


	// {{{ soldePurchases
	/**
	 * solde tous les achats de l'incrit
	 *
	 * @access public
	 * @param  string  $info   column of the purchase
	 * @return mixed
	 */
	function soldePurchases($regiId, $type, $date)
	{
		$utd = new utdate();
		$utd->setFrDate($date);
		$infos['cmd_date'] = $utd->getIsoDateTime();

		// Recuperer les achats de l'inscrit
		$fields = Array('cmd_id', 'cmd_value', 'cmd_discount');
		$tables = array('commands');
		$where = "cmd_regiId=$regiId".
	" AND cmd_itemId >=0".
	" AND cmd_payed < cmd_value-cmd_discount";
		$res = $this->_select($tables, $fields, $where);
		$fields = array();
		while ($cmd = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $fields['cmd_payed'] = $cmd['cmd_value']-$cmd['cmd_discount'];
	  $fields['cmd_type'] = $type;
	  $where = "cmd_id=".$cmd['cmd_id'];
	  $this->_update('commands', $fields, $where);
		}
		return true;
	}
	// }}}

	// {{{ soldeTeam
	/**
	 * solde tous les achats de l'equipe
	 *
	 * @access public
	 * @param  string  $info   column of the purchase
	 * @return mixed
	 */
	function soldeTeam($teamId)
	{
		// Recuperer les achats de l'inscrit
		$where = "regi_teamId=$teamId";
		$res = $this->_select('registration', 'regi_id', $where);

		while ($regi = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $this->soldePurchases($regi['regi_id'], WBS_PAYED_CHECK,
	  date(DATE_DATE));
		}
		return true;
	}
	// }}}

}
?>