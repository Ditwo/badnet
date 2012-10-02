<?php
/*****************************************************************************
 !   Module     : utils
 !   Version    : $Name:  $
 !   Author     : G.CANTEGRIL
 ******************************************************************************/
require_once "utbase.php";
require_once "utils/utround.php";
require_once "utils/utdraw.php";
require_once "utils/utimg.php";

class objgroup
{

	// {{{ properties
	// }}}
	function objgroup()
	{
	}

	function deleteGroup($aDrawId, $aGroupName)
	{
		$utbase = new utbase();

		$fields = array('rund_id');
		$where = "rund_group = '" . addSlashes($aGroupName) ."'";
		$where .= " AND rund_drawid = " . $aDrawId;
		$res = $utbase->_select('rounds', $fields, $where);
		$utr = new utround();
		while ($round = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$utr->delRound($round['rund_id']);
		}
		return true;
	}

	function getListGroups($aDrawId, $aType=null)
	{
		$utbase = new utbase();

		$tables = array('rounds');
		$fields = array('distinct rund_group');
		$tables = array('rounds');
		$where = " rund_drawId = " . $aDrawId;
		if (!empty($aType) ) $where .= ' AND rund_type=' . $aType;
		$order = "rund_group";
		$res = $utbase->_select($tables, $fields, $where, $order);
		$groups = array();
		while ($round = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$groups[] = $round['rund_group'];
		}
		return $groups;
	}


	function getGroup($aDrawId, $aGroup)
	{
		$utbase = new utbase();
		$utd = new utdraw();
		$uti = new utimg();
		$draw = $utd->getDrawById($aDrawId);

		$tables = array('rounds');
		$fields = array('rund_id', 'rund_name', 'rund_stamp', 'rund_type',
		      'rund_drawId', 'rund_size', 'rund_entries', 
		      'rund_qual', 'rund_group', 'rund_qualPlace', 'rund_rge');
		$tables = array('rounds');
		$where = " rund_drawId = $aDrawId AND rund_type =" . WBS_ROUND_GROUP;
		$where .= " AND rund_group='" . $aGroup . "'";
		$order = "rund_name";

		$res = $utbase->_select($tables, $fields, $where, $order);
		$trans = array('rund_name', 'rund_stamp');
		$row['drawId'] = $aDrawId;
		$row['group'] = $aGroup;
		$row['nb'] = 0;
		$row['nb3'] = 0;
		$row['nb4'] = 0;
		$row['nb5'] = 0;
		$row['nb6'] = 0;
		$row['nb7'] = 0;
		$row['nbs3'] = 0;
		$row['nbs4'] = 0;
		$row['nbs5'] = 0;
		$row['nbs6'] = 0;
		$row['nbs7'] = 0;
		$row['nbsecond'] = 0;
		while ($round = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$round = $utbase->_getTranslate('rounds', $trans, $round["rund_id"], $round);
			$row['nb']++;
			$row['nbsecond'] = $round['rund_qualPlace'];
			$row['rund_rge'] = $round['rund_rge'];
			switch($round['rund_size'])
			{
				case 3:
					$row['nb3']++;
					$row['nbs3'] = $round['rund_qual'];
					break;
				case 4:
					$row['nb4']++;
					$row['nbs4'] = $round['rund_qual'];
					break;
				case 5:
					$row['nb5']++;
					$row['nbs5'] = $round['rund_qual'];
					break;
				case 6:
					$row['nb6']++;
					$row['nbs6'] = $round['rund_qual'];
					break;
				case 7:
					$row['nb7']++;
					$row['nbs7'] = $round['rund_qual'];
					break;
			}
		}
		$row['nbPlace'] = $row['nb3'] * 3 +
		$row['nb4'] * 4 +
		$row['nb5'] * 5 +
		$row['nb6'] * 6 +
		$row['nb7'] * 7;
		$row['nbQual'] = $row['nb3'] * $row['nbs3'] +
		$row['nb4'] * $row['nbs4'] +
		$row['nb5'] * $row['nbs5'] +
		$row['nb6'] * $row['nbs6'] +
		$row['nb7'] * $row['nbs7'] +
		$row['nbsecond'];
		$row['warning'] = '';
		$row['nbQ'] = 0;
		if ( $row['nbPlace'])
		{
			$row['type'] = WBS_GROUP;
			if ($draw['draw_nbPairs'] > $row['nbPlace']) $row['warning'] = $uti->getIcon('w1');
			if ($draw['draw_nbPairs'] < $row['nbPlace']) $row['warning'] = $uti->getIcon('w3');
		}
		
		// tableau Ko du groupe
		$where = " rund_drawId = $aDrawId AND rund_type =" . WBS_ROUND_MAINDRAW;
		$where .= " AND rund_group='" . $aGroup . "'";
		$order = "rund_name";
		$row['mainroundid'] = 0;
		$res = $utbase->_select($tables, $fields, $where, $order);
		while ($round = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$row['mainroundid'] = $round['rund_id'];
			$row['rund_rge'] = $round['rund_rge'];
			if ( !$row['nbPlace'])
			{
				$row['type'] = WBS_KO;
				$row['nbPlace'] = $round['rund_entries'];
				$row['nbQ'] = $round['rund_qualPlace'];
				if ($draw['draw_nbPairs'] < $round['rund_entries']) $row['warning'] = $uti->getIcon('w3');
				if ($draw['draw_nbPairs'] > $round['rund_entries']) $row['warning'] = $uti->getIcon('w1');
			}
		}

		// tableau consolante du groupe
		$where = " rund_drawId = $aDrawId AND (rund_type =" . WBS_ROUND_CONSOL . " OR rund_type =" . WBS_ROUND_PLATEAU .")";
		$where .= " AND rund_group='" . $aGroup . "'";
		$order = "rund_name";
		$res = $utbase->_select($tables, $fields, $where, $order);
		while ($round = $res->fetchRow(DB_FETCHMODE_ASSOC)) $row['type'] = WBS_CONSOL;
		if (empty($round['rund_rge'])) $round['rund_rge']=0;

		// tableau troisieme place du groupe
		$where = " rund_drawId = $aDrawId AND rund_type =" . WBS_ROUND_THIRD;
		$where .= " AND rund_group='" . $aGroup . "'";
		$order = "rund_name";
		$res = $utbase->_select($tables, $fields, $where, $order);
		$row['third'] = false;
		while ($round = $res->fetchRow(DB_FETCHMODE_ASSOC)) $row['third'] = true;
		
		return $row;
	}


	function getGroups()
	{
		$tables = array('rounds');
		$fields = array('rund_id', 'rund_name', 'rund_stamp', 'rund_type',
		      'rund_drawId', 'rund_size', 'rund_entries', 
		      'rund_qual', 'rund_group');
		$tables = array('rounds');
		$where = " rund_drawId = $aDrawId AND rund_type =" . WBS_ROUND_GROUP;
		$order = "rund_group, rund_name";

		$res = $this->_select($tables, $fields, $where, $order);
		$trans = array('rund_name', 'rund_stamp');
		$rounds = array();
		$row = array('group' => '');
		$rounds = array();
		while ($round = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$round = $this->_getTranslate('rounds', $trans,	$round["rund_id"], $round);
			if( $row['group'] != $round['rund_group'] )
			{
				if ($row['group'] != '') $rounds[] = $row;
				$row['group'] = $round['rund_group'];
				$row['nb'] = 0;
				$row['nb3'] = 0;
				$row['nb4'] = 0;
				$row['nb5'] = 0;
				$row['nb6'] = 0;
				$row['nb7'] = 0;
				$row['nbs3'] = 0;
				$row['nbs4'] = 0;
				$row['nbs5'] = 0;
				$row['nbs6'] = 0;
				$row['nbs7'] = 0;
			}

			$row['nb']++;
			switch($round['rund_size'])
			{
				case 3:
					$row['nb3']++;
					$row['nbs3'] = $round['rund_qual'];
					break;
				case 4:
					$row['nb4']++;
					$row['nbs4'] = $round['rund_qual'];
					break;
				case 5:
					$row['nb5']++;
					$row['nbs5'] = $round['rund_qual'];
					break;
				case 6:
					$row['nb6']++;
					$row['nbs6'] = $round['rund_qual'];
					break;
				case 7:
					$row['nb7']++;
					$row['nbs7'] = $round['rund_qual'];
					break;
			}
		}
		if ($row['group'] != '') $rounds[] = $row;
		return $rounds;
	}

}

?>
