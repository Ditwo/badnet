<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';

define("ITEM_RUBRIK_OTHERS", -1);
define("ITEM_RUBRIK_FEES",   -2);
define("ITEM_RUBRIK_HOTEL",  -3);

define("ITEM_ROOM_SINGLE",  340);
define("ITEM_ROOM_TWIN",    341);
define("ITEM_ROOM_TRIPLEX", 342);
define("ITEM_ROOM_OTHER",   343);
define("ITEM_PRICE_WEEK",   345);
define("ITEM_PRICE_WE",     346);

class OFees extends Object
{
	// {{{ properties
	private $_eventId   = 0;       // identifiant du tournoi
	// }}}

	/**
	 * Constructeur
	 * @param	integer		$aEventId	Identifiant du tournoi
	 * @return OFees
	 */
	public function __construct($aEventId)
	{
		$this->_eventId = $aEventId;
	}

	/**
	 * Return the values of the entry fees
	 * @return array   information of the fees if any
	 */
	public function getFees()
	{
		$fees = array( 'IS' => '0.00',
		     'ID' => '0.00',
		     'IM' => '0.00',
		     'I1' => '0.00',
		     'I2' => '0.00',
		     'I3' => '0.00');
		
		$q = new Bn_query('items');
		$q->setFields('item_id, item_value, item_code,item_name');
		$q->addWhere('item_eventid=' . $this->_eventId);
		$q->addWhere('item_rubrikid=' . ITEM_RUBRIK_FEES);
		$items = $q->getRows(); 
		foreach($items as $item)
		{
	  		$fees[$item['item_code']] = sprintf('%.2f', $item['item_value']);
		}
		return $fees;
		/*
		$fields = array('item_id', 'item_value', 'item_code',
		      'item_name');
		$tables[] = 'items';
		$where = "item_eventId=".utvars::getEventId().
	" AND item_rubrikId=". WBS_RUBRIK_FEES;
		$res = $this->_select($tables, $fields, $where);
		while($item = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $fees[$item['item_code']] = $item;
		}
*/
	}
	// }}}

	
	
	
	
	
	
	
	 
	// {{{ getCmds
	/**
	 * Return the cmds of fees
	 *
	 * @access public
	 * @return array   information of the fees if any
	 */
	function getCmds()
	{
		$fees = array();
		$fields = array('item_id', 'item_name',
		      'count(item_id) as nb',
		      'sum(cmd_value) as value',
		      'sum(cmd_discount) as discount',
		      'sum(cmd_payed) as payed');

		$tables = array('items', 'commands');
		$where = "item_eventId=".utvars::getEventId().
	" AND item_id=cmd_itemId".
	" AND item_rubrikId=". WBS_RUBRIK_FEES.
	" GROUP BY item_id";
		$res = $this->_select($tables, $fields, $where);
		$cumul['item_id'] = 0;
		$cumul['item_name'] = "Total";
		$cumul['nb'] = '';
		$cumul['value'] = 0;
		$cumul['discount'] = 0;
		$cumul['payed'] = 0;
		$cumul['du'] = 0;
		while($item = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $item['du'] = $item['value']-$item['discount']-$item['payed'];
	  $fees[] = $item;
	  $cumul['value'] += $item['value'];
	  $cumul['discount'] += $item['discount'];
	  $cumul['payed'] += $item['payed'];
	  $cumul['du'] += $item['du'];
		}
		$fees[] = $cumul;
		return $fees;
	}
	// }}}


	// {{{ updateFees
	/**
	 * update the values of the entry fees
	 *
	 * @access public
	 * @return array   information of the fees if any
	 */
	function updateFees($fees)
	{
		$eventId = utvars::getEventId();
		foreach($fees as $fee)
		{
	  $fee['item_rubrikId'] = WBS_RUBRIK_FEES;
	  $fee['item_eventId'] = $eventId;

	  $fields = array('item_id');
	  $where = "item_eventId=".utvars::getEventId().
	    " AND item_rubrikId=". WBS_RUBRIK_FEES.
	    " AND item_code='{$fee['item_code']}'";
	  $res = $this->_select('items', $fields, $where);
	  if ($res->numRows())
	  {
	  	$this->_update('items', $fee, $where);
	  	$item = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	$where = "cmd_itemId={$item['item_id']}";
	  	$cols['cmd_value'] = $fee['item_value'];
	  	$this->_update('commands', $cols, $where);
	  }
	  else
	  $this->_insert('items', $fee);

	   
		}
		return $fees;
	}
	// }}}

	// {{{ updateRegisFees
	/**
	 * update the fees for the all player of the current event
	 * @return array   information of the fees if any
	 */
	function updateRegisFees()
	{
		$eventId = utvars::getEventId();
		// get the fees
		$fees = $this->getFees();

		//Get the players
		$where = "regi_eventId=$eventId".
	" AND regi_type=".WBS_PLAYER;
		$res = $this->_select('registration', 'regi_id', $where);

		while ($regi = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $this->updateRegiFees($regi['regi_id'], $fees);
		}
		return;
	}
	// }}}

	// {{{ updateRegiFees
	/**
	 * update the fees for the player selected
	 * @return array   information of the fees if any
	 */
	function updateRegiFees($regiId, $fees=NULL)
	{
		$eventId = utvars::getEventId();
		// get the fees
		if (is_null($fees))
		$fees = $this->getFees();

		//Get the account of the player
		$where = "regi_id = $regiId";
		$accountId = $this->_selectFirst('registration',
				       'regi_accountId', $where);

		//Get the draws of the player
		$fields = array('pair_disci');
		$tables = array('registration', 'i2p', 'pairs', 'draws');
		$where = "i2p_regiId = $regiId".
	" AND i2p_regiId = regi_id".
	" AND i2p_pairId = pair_id".
	" AND pair_drawId = draw_id";
		$res = $this->_select($tables, $fields, $where);

		// Aucune inscription ! Suppression des frais
		if (!$res->numRows())
		{
	  foreach($fees as $fee)
	  $ids[] = $fee['item_id'];
	  $feeIds = implode(',', $ids);
	  $where = "cmd_regiId = $regiId".
	    " AND cmd_itemId IN ($feeIds)";
	  $this->_delete('commands', $where);
	  return;
		}

		// Cas du tarif par nombre de tableau
		if (($fees['IS']['item_value'] +
		$fees['ID']['item_value'] +
		$fees['IM']['item_value']) == 0)
		{

	  $regi = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  // Chercher la commande correspondante
	  $where = "cmd_regiId = $regiId".
	    " AND cmd_itemId IN ({$fees['I1']['item_id']},
	  {$fees['I2']['item_id']},
	  {$fees['I3']['item_id']})";
	  $id = $this->_selectFirst('commands', 'cmd_id', $where);

	  if ($res->numRows() == 1)
	  {
	  	$feeId = $fees['I1']['item_id'];
	  	$value = $fees['I1']['item_value'];
	  	$name = $fees['I1']['item_name'];
	  	unset($fees['I1']);
	  }
	  else if ($res->numRows() == 2 ||
	  ($res->numRows() > 2 &&
	  $fees['I3']['item_value'] == 0))
	  {
	  	$feeId = $fees['I2']['item_id'];
	  	$value = $fees['I2']['item_value'];
	  	$name = $fees['I2']['item_name'];
	  	unset($fees['I2']);
	  }
	  else
	  {
	  	$feeId = $fees['I3']['item_id'];
	  	$value = $fees['I3']['item_value'];
	  	$name = $fees['I3']['item_name'];
	  	unset($fees['I3']);
	  }

	  // Nouvelle commande
	  if (is_null($id))
	  {
	  	$regi = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	$cols['cmd_regiId'] = $regiId;
	  	$cols['cmd_itemId'] = $feeId;
	  	$cols['cmd_accountId'] = $accountId;
	  	$cols['cmd_date'] = date(DATE_FMT);
	  	$cols['cmd_value'] = $value;
	  	$cols['cmd_name'] = $name;
	  	$this->_insert('commands', $cols);
	  }
	  else
	  {
	  	$cols['cmd_itemId'] = $feeId;
	  	$cols['cmd_value'] = $value;
	  	$cols['cmd_name'] = $name;
	  	$cols['cmd_accountId'] = $accountId;
	  	$this->_update('commands', $cols, $where);
	  }
		}
		// Cas du tarif par tableau
		else
		{
	  while ($regi = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  {
	  	$where = "cmd_regiId = $regiId";
	  	if ($regi['pair_disci'] == WBS_SINGLE)
	  	{
	  		$where .= " AND cmd_itemId={$fees['IS']['item_id']}";
	  		$feeId = $fees['IS']['item_id'];
	  		$value = $fees['IS']['item_value'];
	  		$name = $fees['IS']['item_name'];
	  		unset($fees['IS']);
	  	}
	  	else if ($regi['pair_disci'] == WBS_DOUBLE)
	  	{
	  		$where .= " AND cmd_itemId={$fees['ID']['item_id']}";
	  		$feeId = $fees['ID']['item_id'];
	  		$value = $fees['ID']['item_value'];
	  		$name = $fees['ID']['item_name'];
	  		unset($fees['ID']);
	  	}
	  	else
	  	{
	  		$where .= " AND cmd_itemId={$fees['IM']['item_id']}";
	  		$feeId = $fees['IM']['item_id'];
	  		$value = $fees['IM']['item_value'];
	  		$name = $fees['IM']['item_name'];
	  		unset($fees['IM']);
	  	}

	  	$id = $this->_selectFirst('commands', 'cmd_id', $where);
	  	// Nouvelle commande
	  	if (is_null($id))
	  	{
	  		$cols['cmd_regiId'] = $regiId;
	  		$cols['cmd_itemId'] = $feeId;
	  		$cols['cmd_accountId'] = $accountId;
	  		$cols['cmd_date'] = date(DATE_FMT);
	  		$cols['cmd_value'] = $value;
	  		$cols['cmd_name'] = $name;
	  		$this->_insert('commands', $cols);
	  	}
	  	else
	  	{
	  		$cols['cmd_itemId'] = $feeId;
	  		$cols['cmd_value'] = $value;
	  		$cols['cmd_accountId'] = $accountId;
	  		$cols['cmd_name'] = $name;
	  		$this->_update('commands', $cols, $where);
	  	}
	  }
		}
		// Supprimer les commandes en trop
		foreach($fees as $fee)
		{
	  foreach($fees as $fee)
	  $ids[] = $fee['item_id'];
	  $feeIds = implode(',', $ids);
	  $where = "cmd_regiId = $regiId".
		" AND cmd_itemId IN ($feeIds)";
	  $this->_delete('commands', $where);
		}
		return;
	}
	// }}}

}
?>