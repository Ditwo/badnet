<?php
/*****************************************************************************
 !   Module     : Badges
 !   File       : $Source: /cvsroot/aotb/badnet/src/badges/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.3 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:24 $
 ******************************************************************************/
require_once "utils/utbase.php";

/**
 * Acces to the dababase for ties
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class badgesBase_A extends utbase
{

	// {{{ properties
	// }}}

	// {{{ printed
	/**
	 * marque les badges imprimes
	 */
	function printed($ids, $isPrinted)
	{
		if ($isPrinted) $state['regi_badgeprinted'] = WBS_YES;
		else $state['regi_badgeprinted'] = WBS_NO;
		
		$where = "regi_id IN (" . implode(',', $ids). ')';
		$this->_update('registration', $state, $where);
		
		return;
	}
	// }}}
	
	
	// {{{ removeZone
	/**
	* Return the zone definition
	*/
	function removeZone($zoneId, $id)
	{

		$fields = array('z2r_typeregi' => $id,
                    'z2r_zoneid' => $zoneId);
		$where = "z2r_zoneid = $zoneId".
    	" AND z2r_typeregi = $id";
		$res = $this->_delete('z2r', $where);
	}
	//}}}


	// {{{ addZone
	/**
	* Return the zone definition
	*/
	function addZone($zoneId, $id)
	{

		$fields = array('z2r_typeregi' => $id,
                    'z2r_zoneid' => $zoneId);
		$where = "z2r_zoneid = $zoneId".
    	" AND z2r_typeregi = $id";
		$res = $this->_select('z2r', 'z2r_typeregi', $where);
		if (!$res->numRows() )
		{
			$this->_insert('z2r', $fields);
		}
	}
	//}}}


	// {{{ getZoneDetail
	/**
	* Return the zone definition
	*/
	function getZoneDetail($zoneId)
	{

		$fields = array('zone_id', 'zone_name');
		$where = "z2r_zoneid = $zoneId";
		$res = $this->_select('z2r', 'z2r_typeregi', $where);
		$z2rs = array();
		while (	$z2r = $res->fetchRow(DB_FETCHMODE_ASSOC) )
		{
			$z2rs[] = $z2r['z2r_typeregi'];
		}
		return $z2rs;
	}
	//}}}

	// {{{ deleteZone
	/**
	* Supprime les zones
	*/
	function deleteZone($ids)
	{
		foreach($ids as $id)
		{
			$where = "zone_id={$id}";
			$this->_delete('zone', $where);
		}
		return;
	}
	// }}}


	// {{{ updateZone
	/**
	* Update the element definiton
	*
	* @access public
	* @return array   array of users
	*/
	function updateZone($data)
	{
		if ($data['zone_id'] == -1)
		{
			unset($data['zone_id']);
			$this->_insert('zone', $data);
		}
		else
		{
			$where = "zone_id={$data['zone_id']}";
			$this->_update('zone', $data, $where);
		}
		return;
	}
	// }}}

	// {{{ getZones
	/**
	* Retunr all the areas
	* @return array   array of areas
	*/
	function getZones()
	{
		// Select all concerned match
		// Ordered according the selectd option
		$eventId = utvars::getEventId();
		$fields = array('zone_id', 'zone_name');
		$where = 'zone_eventId=' . $eventId;
		$order = 'zone_name';
		$res = $this->_select('zone', $fields, $where, $order);
		$zones = array();
		while ($zone = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$zones[] = $zone;
		return $zones;
	}
	//}}}
	// {{{ getZone
	/**
	* Return the zone definition
	*/
	function getZone($zoneId)
	{

		$fields = array('zone_id', 'zone_name');
		$where = "zone_id = $zoneId";
		$res = $this->_select('zone', $fields, $where);
		if($res->numRows())
		{
			$res = $this->_select('zone', $fields, $where);
			$zone = $res->fetchRow(DB_FETCHMODE_ASSOC);
		}
		else
		{
			$zone = array('zone_id'   => -1,
		   'zone_name' => '', 
			);
		}
		return $zone;
	}



	// {{{ deleteEltbs
	/**
	 * Suprime les composants d'un badge
	 */
	function deleteEltbs($ids)
	{
		foreach($ids as $eltId)
		{
			$vals = explode(';', $eltId);
			$where = "eltb_id={$vals[1]}";
			$this->_delete('eltbadges', $where);
		}
		return;
	}
	// }}}

	// {{{ deleteBagdes
	/**
	 * Suprime les badges et leur composant
	 */
	function deleteBadges($ids)
	{
		foreach($ids as $badgeId)
		{
			$where = "eltb_badgeId=$badgeId";
			$this->_delete('eltbadges', $where);

			$where = "bdge_id=$badgeId";
			$this->_delete('badges', $where);
		}
		return;
	}
	// }}}

	// {{{ getMembers
	/**
	 * Return the list of members
	 *
	 * @access public
	 * @return array   array of users
	 */
	function getMembers($first, $last, $select)
	{
		$fields = array('regi_id', 'mber_sexe', 'mber_secondname',
		    'mber_firstname', 'regi_type', 'regi_noc',
		    'team_name', 'team_logo', 'regi_badgeprinted', 'team_noc', 
		    'asso_noc', 'regi_function', 'asso_logo');
		if ($select == -1)
		{
			$where = "regi_type >= $first".
      " AND regi_type <= $last";
		}
		else
		{
			$where = 'regi_type='.$select;
		}
		$where .= " AND regi_teamId = team_id".
      " AND team_id = a2t_teamId".
      " AND a2t_assoId = asso_id".
      " AND regi_memberId = mber_id".
      " AND regi_eventId = ".utvars::getEventId();
		$tables = array('assocs', 'a2t', 'registration', 'members', 'teams');
		$order = 'regi_type, mber_secondname, mber_firstname';
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
			$tmp['errMsg'] = "msgNoEntry";
			return $tmp;
		}

		$ut = new utils();
		$res = $this->_select($tables, $fields, $where, $order);
		while ($mber = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$mber['mber_sexe'] = $ut->getSmaLabel($mber['mber_sexe']);
			if ($mber['regi_function']==='')
	  			$mber['regi_type'] = $ut->getLabel($mber['regi_type']);
	  		else
	  			$mber['regi_type'] = $mber['regi_function'];

	  if ($mber['regi_noc'] != '')
	  	$mber['regi_noc'] = $mber['regi_noc'];
	  else if ($mber['team_noc'] != '')
	  	$mber['regi_noc'] = $mber['team_noc'];
	  else
	  	$mber['regi_noc'] = $mber['asso_noc'];

	  if ($mber['team_logo'] != "")
	  	$mber['asso_logo'] = utimg::getPathTeamLogo($mber['team_logo']);
	  else
	  	$mber['asso_logo'] = utimg::getPathFlag($mber['asso_logo']);
	  $mber['team_logo'] = '';
	  if ($mber['regi_badgeprinted'] == 160 ) $mber['regi_badgeprinted'] = 'Oui';
	  else $mber['regi_badgeprinted'] = 'Non';
	  $mbers[] = $mber;
		}
		return $mbers;
	}
	// }}}

	// {{{ updateEltb
	/**
	 * Update the element definiton
	 *
	 * @access public
	 * @return array   array of users
	 */
	function updateEltb($data)
	{
		if ($data['eltb_id'] == -1)
		{
			unset($data['eltb_id']);
			$this->_insert('eltbadges', $data);
		}
		else
		{
			$where = "eltb_id={$data['eltb_id']}";
			$this->_update('eltbadges', $data, $where);
		}
		return;
	}
	// }}}

	// {{{ getEltb
	/**
	 * Return the badge definition
	 * @return array   array of users
	 */
	function getEltb($eltId)
	{

		$fields = array('eltb_id', 'eltb_font', 'eltb_size',  'eltb_bold', 'eltb_align',
		    'eltb_top', 'eltb_left', 'eltb_width', 'eltb_height', 
		    'eltb_border', 'eltb_borderSize', 'eltb_field',
		    'eltb_value', 'eltb_textColor', 'eltb_fillColor',
		    'eltb_drawColor', 'eltb_zoneid');
		$where = "eltb_id=$eltId";
		$res = $this->_select('eltbadges', $fields, $where);
		if($res->numRows())
		{
			$ut = new utils();
			$res = $this->_select('eltbadges', $fields, $where);
			$elt = $res->fetchRow(DB_FETCHMODE_ASSOC);
			if ($elt['eltb_field'] != -1) $elt['eltb_field'] = $ut->getLabel($elt['eltb_field']);
	  		else $elt['eltb_field'] = "---Aucun---";
		}
		else
		$elt = array('eltb_id'   => -1,
		   'eltb_font' => 'helvetica', 
		   'eltb_size' => 20, 
		   'eltb_align' => 'L', 
		   'eltb_bold' => 1,
		   'eltb_top'  => 10, 
		   'eltb_left'   => 10, 
		   'eltb_width'  => 10, 
		   'eltb_height' => 10, 
		   'eltb_border' => 0, 
		   'eltb_borderSize' => 0, 
		   'eltb_field' => '---Aucun---', 
		   'eltb_zoneid' => 0,
		   'eltb_value' => '', 
		   'eltb_textColor' => '0;0;0', 
		   'eltb_fillColor' => '255;255;255', 
		   'eltb_drawColor' => '0;0;0');
		return $elt;
	}

	// {{{ getElts
	/**
	 * Return the badge definition
	 * @return array   array of users
	 */
	function getElts($badgeId)
	{

		$fields = array('eltb_id', 'eltb_font', 'eltb_size',  'eltb_bold', 'eltb_align',
		    'eltb_top', 'eltb_left', 'eltb_width', 'eltb_height', 
		    'eltb_border', 'eltb_borderSize', 'eltb_field',
		    'eltb_value', 'eltb_textColor', 'eltb_fillColor',
		    'eltb_drawColor');
		$where = "eltb_badgeId=$badgeId";
		$res = $this->_select('eltbadges', $fields, $where);
		if($res->numRows())
		{
			$ut = new utils();
			$res = $this->_select('eltbadges', $fields, $where);
			while ($elt = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  		{
	  			$elt['eltb_field'] = $ut->getLabel($elt['eltb_field']);
	  			$elt['eltb_id'] = "{$badgeId};{$elt['eltb_id']}";
	  			$elts[] = $elt;
	  		}
		}
		else
		$elts['errMsg'] = "msgNoElement";
		return $elts;
	}


	// {{{ getBadges
	/**
	 * Retunr all the badges
	 * @return array   array of users
	 */
	function getBadges()
	{
		// Select all concerned match
		// Ordered according the selectd option
		$eventId = utvars::getEventId();
		$fields = array('bdge_id', 'bdge_name', 'bdge_width', 'bdge_height',
		    'bdge_border', 'bdge_borderSize', 'bdge_topMargin',
		    'bdge_leftMargin', 'bdge_deltaWidth', 'bdge_deltaHeight');
		$order = 'bdge_name';
		//echo $order;
		$res = $this->_select('badges', $fields, false, $order);
		while ($badge = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$badges[] = $badge;
		return $badges;
	}

	//}}}


	// {{{ getBadge
	/**
	 * Return the badge definition
	 * @return array   array of users
	 */
	function getBadge($badgeId)
	{

		$fields = array('bdge_id', 'bdge_name', 'bdge_width', 'bdge_height',
		    'bdge_border', 'bdge_borderSize', 'bdge_topMargin',
		    'bdge_leftMargin', 'bdge_deltaWidth', 'bdge_deltaHeight');
		$where = "bdge_id=$badgeId";
		$order = 'bdge_name';
		$res = $this->_select('badges', $fields, $where);
		if ($res->numRows())
		{
			$res = $this->_select('badges', $fields, $where);
			$badge = $res->fetchRow(DB_FETCHMODE_ASSOC);
		}
		else
		$badge = array('bdge_id'     => -1,
		     'bdge_name'   => 'nouveau', 
		     'bdge_width'  => 90, 
		     'bdge_height' => 60,
		     'bdge_border' => 1, 
		     'bdge_borderSize'  =>1, 
		     'bdge_topMargin'   => 15,
		     'bdge_leftMargin'  => 15, 
		     'bdge_deltaWidth'  => 10,
		     'bdge_deltaHeight' => 10);
		return $badge;
	}

	// {{{ updateBadge
	/**
	 * Updtae the badge definiton
	 *
	 * @access public
	 * @return array   array of users
	 */
	function updateBadge($data)
	{
		if ($data['bdge_id'] == -1)
		{
			unset($data['bdge_id']);
			$this->_insert('badges', $data);
		}
		else
		{
			$where = "bdge_id={$data['bdge_id']}";
			$this->_update('badges', $data, $where);
		}
		return;
	}
	// }}}

}
?>