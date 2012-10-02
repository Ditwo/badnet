<?php
/*****************************************************************************
 !   Module     : Utilitaires pour les traiter les donnees issues du site FFBA
 !   File       : $Source: /cvsroot/aotb/badnet/src/utils/utfees.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.5 $
 !   Author     : G.CANTEGRIL/D.BEUVELOT
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/01/18 07:51:21 $
 !   Mailto     : didier.beuvelot@free.fr/cage@free.fr
 ******************************************************************************
 !   License   : Licensed under GPL [http://www.gnu.org/copyleft/gpl.html]
 !      This program is free software; you can redistribute it and/or
 !      modify it under the terms of the GNU General Public License
 !      as published by the Free Software Foundation; either version 2
 !      of the License, or (at your option) any later version.
 !
 !      This program is distributed in the hope that it will be useful,
 !      but WITHOUT ANY WARRANTY; without even the implied warranty of
 !      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 !      GNU General Public License for more details.
 !
 !      You should have received a copy of the GNU General Public License
 !      along with this program; if not, write to the Free Software
 !      Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,
 !      USA.
 ******************************************************************************/


/**
 * Classe de base pour la gestion des images
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class Utfees extends utBase
{

	// {{{ properties
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
	" GROUP BY item_id".
	" ORDER BY item_name";
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
	  		$item['value'] = sprintf('%.02f', $item['value']);
	  		$item['discount'] = sprintf('%.02f', $item['discount']);
	  		$item['payed'] = sprintf('%.02f', $item['payed']);
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

	// {{{ getFees
	/**
	 * Return the values of the entry fees
	 *
	 * @access public
	 * @return array   information of the fees if any
	 */
	function getFees()
	{
		$fees = array( 'IS' => '0.00',
		     'ID' => '0.00',
		     'IM' => '0.00',
		     'I1' => '0.00',
		     'I2' => '0.00',
		     'I3' => '0.00');
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

	/**
	 * update the fees for the all player of the current event
	 * @return array   information of the fees if any
	 */
	function updateRegisFees($aMatch = false)
	{
		$eventId = utvars::getEventId();
		// get the fees
		$fees = $this->getFees();

		//Get the players
		$where = "regi_eventId=$eventId".
	" AND regi_type=".WBS_PLAYER.
	" ORDER BY regi_longname";
		$res = $this->_select('registration', 'regi_id', $where);

		while ($regi = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->updateRegiFees($regi['regi_id'], $fees, $aMatch);
		}
		return;
	}

	/**
	 * update the fees for the player selected
	 * @return array   information of the fees if any
	 */
	function updateRegiFees($regiId, $fees=NULL, $aMatch=false)
	{
		$eventId = utvars::getEventId();
		// get the fees
		if (is_null($fees)) $fees = $this->getFees();

		//Get the account of the player
		$where = "regi_id = $regiId";
		$accountId = $this->_selectFirst('registration',
				       'regi_accountId', $where);

		$reginame = $this->_selectFirst('registration', 'regi_longname', $where);

		// Suppression des frais precedents
		foreach($fees as $fee) $ids[] = $fee['item_name'];
		$feeIds = implode("','", $ids);
		$where = "cmd_regiId = $regiId".
	    		" AND cmd_name IN ('$feeIds')";
	    		" AND cmd_payed = 0";
		$this->_delete('commands', $where);
		
		//Get the draws of the player
		$fields = array('pair_disci, regi_longname');
		$tables = array('registration', 'i2p', 'pairs', 'draws');
		$where = "i2p_regiId = $regiId".
	" AND i2p_regiId = regi_id".
	" AND i2p_pairId = pair_id".
	" AND pair_drawId = draw_id";
		if ($aMatch)
		{
			$tables[] = 'p2m';
			$where .= ' AND p2m_pairid=pair_id';
			$where .= ' GROUP BY pair_disci';
		}

		$res = $this->_select($tables, $fields, $where);
		$nbDraw = $res->numRows();
		if (!$nbDraw) return;
		
		// Cas du tarif par nombre de tableau
		if (($fees['IS']['item_value'] +
		$fees['ID']['item_value'] +
		$fees['IM']['item_value']) == 0)
		{
			// Chercher la commande correspondante
			$where = "cmd_regiId = $regiId".
	    		" AND cmd_name IN ('{$fees['I1']['item_name']}',
			'{$fees['I2']['item_name']}',
			'{$fees['I3']['item_name']}')";
			$id = $this->_selectFirst('commands', 'cmd_id', $where);
			if ( $nbDraw == 1)
			{
				$feeId = $fees['I1']['item_id'];
				$value = $fees['I1']['item_value'];
				$name = $fees['I1']['item_name'];
				unset($fees['I1']);
			}
			else if ($nbDraw == 2 ||
			($nbDraw > 2 &&
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
			$res = $this->_select($tables, $fields, $where);
			while ($regi = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$where = "cmd_regiId = $regiId";
				if ($regi['pair_disci'] == WBS_SINGLE)
				{
					$where .= " AND cmd_name='{$fees['IS']['item_name']}'";
					$feeId = $fees['IS']['item_id'];
					$value = $fees['IS']['item_value'];
					$name = $fees['IS']['item_name'];
					unset($fees['IS']);
				}
				else if ($regi['pair_disci'] == WBS_DOUBLE)
				{
					$where .= " AND cmd_name='{$fees['ID']['item_name']}'";
					$feeId = $fees['ID']['item_id'];
					$value = $fees['ID']['item_value'];
					$name = $fees['ID']['item_name'];
					unset($fees['ID']);
				}
				else
				{
					$where .= " AND cmd_name='{$fees['IM']['item_name']}'";
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
		$ids = array();
		if(count($fees))
		{
			foreach($fees as $fee) $ids[] = $fee['item_name'];
			$feeIds = implode("','", $ids);
			$where = "cmd_regiId = $regiId".
				" AND cmd_name IN ('$feeIds')";
			$this->_delete('commands', $where);
		}
		return;
	}

}
?>