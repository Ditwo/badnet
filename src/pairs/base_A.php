<?php
/*****************************************************************************
 !   Module     : Pairs
 !   File       : $Source: /cvsroot/aotb/badnet/src/pairs/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.35 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/20 22:20:08 $
 ******************************************************************************/

require_once "utils/utbase.php";
require_once "utils/utdraw.php";

/**
 * Acces to the dababase for pairs
 *
 * @author Didier BEUVELOT <didier.beuvelot@free.fr>
 * @see to follow
 *
 */

class pairBase_A extends utBase
{

	/**
	 * Return the players of a round
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getRoundPairs($roundId)
	{
		$ut = new utils();

		// Find the id of the pairs
		$fields = array('t2r_pairId', 't2r_tds', 't2r_status', 'pair_natRank',
		      'pair_intRank', 'pair_disci', 'draw_disci', 't2r_rank',
		      'draw_id');
		$tables = array('t2r', 'pairs', 'draws', 'rounds');
		$where  = "t2r_roundId = $roundId".
        " AND t2r_pairId = pair_id".
        " AND t2r_roundId = rund_id".
        " AND pair_drawId = draw_id";
		$order = 't2r_posRound';
		$res = $this->_select($tables, $fields, $where, $order);
		$pairs = array();
		while($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $disci = $entry['draw_disci'];
	  $drawId = $entry['draw_id'];
	  $pairsId[] = $entry['t2r_pairId'];
	  $entry['seed'] = $ut->getLabel($entry['t2r_tds']);
	  if (($entry['t2r_status'] > WBS_PAIR_RESERVE &&
	  $entry['t2r_status'] != WBS_PAIR_NONE) ||
	  ($entry['t2r_status'] == WBS_PAIR_DNS) )
	  $entry['seed'] = $ut->getSmaLabel($entry['t2r_status']);
	  $pairs[$entry['t2r_pairId']] = $entry;
		}

		// Retrieve players name of the pairs
		if (isset($pairsId))
		{
	  $ids = implode(',', $pairsId);
	  // Retrieve group of pairs
	  $fields = array('t2r_pairId', 'rund_stamp', 't2r_rank');
	  $tables = array('t2r', 'rounds');
	  $where = "t2r_roundId = rund_id".
	    " AND t2r_pairId IN ($ids)".
	    " AND rund_drawId = $drawId".
	    " AND rund_type =".WBS_ROUND_GROUP;
	  $res = $this->_select($tables, $fields, $where, $order);
	  while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  $round[$entry['t2r_pairId']] = $entry;

	  // Retrieve players name of the pairs
	  $fields = array('i2p_pairId', 'regi_longName',
			  'asso_noc','team_noc', 'regi_noc',
			  'asso_logo', 'team_logo', 'regi_id',
			  'asso_id', 'team_id','mber_ibfnumber',
			  'rkdf_label', 'asso_stamp', 'i2p_cppp',
			  'rkdf_point', 'team_stamp');
	  $tables = array('members', 'registration', 'i2p', 'teams',
			  'a2t', 'assocs', 'rankdef');
	  $where = "mber_id = regi_memberId".
	    " AND regi_id = i2p_regiId".
	    " AND regi_teamId = team_id".
	    " AND team_id = a2t_teamId".
	    " AND asso_id = a2t_assoId".
	    " AND i2p_pairId IN ($ids)".
	    " AND i2p_rankdefId = rkdf_id";

	  $order = "i2p_pairId, mber_sexe, regi_longName";
	  $res = $this->_select($tables, $fields, $where, $order);
	  while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  {
	  	$pair = $pairs[$entry['i2p_pairId']];
	  	$name = $entry['regi_longName'];
	  	if (isset($pair['noc']))
	  	$noc = $pair['noc'].'-';
	  	else
	  	$noc = '';

	  	if ($entry['regi_noc'] != '')
	  	{
	  		$name .= ' ('.$entry['regi_noc'].')';
	  		$noc .= $entry['regi_noc'];
	  	}
	  	else if ($entry['team_noc'] != '')
	  	{
	  		$name .= ' ('.$entry['team_noc'].')';
	  		$noc .= $entry['team_noc'];
	  	}
	  	else
	  	{
	  		$name .= ' ('.$entry['asso_noc'].')';
	  		$noc .= $entry['asso_noc'];
	  	}
	  	$name .= $entry['rkdf_label']."/".$entry['team_stamp'] ;
	  	$pair['noc'] = $noc;
	  	$pair['teamId'] = $entry['team_id'];
	  	$pair['assoId'] = $entry['asso_id'];
	  	if (isset($pair['average']))
	  	{
	  		$pair['average'] -= $entry['i2p_cppp'];
	  		$pair['average'] /= 2;
	  	}
	  	else
		  $pair['average'] = -$entry['i2p_cppp'];
		  if (isset($pair['level']))
		  {
		  	$pair['level'] -= $entry['rkdf_point'];
		  	$pair['level'] /= 2;
		  }
		  else
		  $pair['level'] = -$entry['rkdf_point'];
		  if ($entry['team_logo'] != '')
		  $logo = utimg::getPathFlag($entry['team_logo']);
		  else if ($entry['asso_logo'] != '')
		  $logo = utimg::getPathFlag($entry['asso_logo']);
		  else
		  $logo = '';
		  $line = array();
		  $line['logo'] = $logo;
		  $line['name'] = $name;
		  $line['level'] = $entry['rkdf_label'];
		  $line['ibfn'] = $entry['mber_ibfnumber'];
		  $line['stamp'] = $entry['asso_stamp'];
		  $line['action'] = array(KAF_UPLOAD, 'regi',
		  KID_SELECT, $entry['regi_id']);
		  $pair['value'][] = $line;

		  if(isset($round[$entry['i2p_pairId']]))
		  {
		  	$pair['rund_stamp'] = $round[$entry['i2p_pairId']]['rund_stamp'];
		  	$pair['t2r_rank'] = $round[$entry['i2p_pairId']]['t2r_rank'];
		  }
		  else
		  {
		  	$pair['rund_stamp'] = 'M';
		  	$pair['t2r_rank'] = ''; $pair['level'];
		  }

		  $pairs[$entry['i2p_pairId']] = $pair;
	  }
		}
		return $pairs;
	}

	/**
	 * Update the international ranking of the pairs of the draw
	 *
	 * @access public
	 * @param  string  $info   column of the pair
	 * @return mixed
	 */
	function updateIntRank($drawId, $ranks)
	{
		// select the pairs of the draw.
		$ibfNums = array_keys($ranks);
		$fields = array('pair_id', 'pair_ibfNum');
		$tables = array('pairs');
		$where = "pair_ibfNum in (".implode(",", $ibfNums).")".
	" AND pair_drawId=$drawId";
		$res = $this->_select($tables, $fields, $where);
		// Update the pairs
		while ($pair = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $ibfNum = $pair['pair_ibfNum'];
	  if (isset($ranks[$ibfNum]))
	  {
	  	$column['pair_intRank'] = $ranks[$ibfNum];
	  	$where = "pair_id={$pair['pair_id']}";
	  	$res2 = $this->_update('pairs', $column, $where);
	  }
		}
		return true;
	}

	/**
	 * Update the national ranking of the pairs of the draw
	 *
	 * @access public
	 * @param  string  $info   column of the pair
	 * @return mixed
	 */
	function updateNatRank($drawId, $ranks)
	{
		// select the pairs of the draw.
		$natNums = array_keys($ranks);
		$fields = array('pair_id', 'pair_natNum');
		$tables = array('pairs');
		$where = "pair_natNum in (".implode(",", $natNums).")".
	" AND pair_drawId=$drawId";
		$res = $this->_select($tables, $fields, $where);

		// Update the pairs
		while ($pair = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $natNum = sprintf("%08d", $pair['pair_natNum']);
	  if (isset($ranks[$natNum]))
	  {
	  	$column['pair_natRank'] = $ranks[$natNum];
	  	$where = "pair_id={$pair['pair_id']}";
	  	$res2 = $this->_update('pairs', $column, $where);
	  }
		}
		return true;
	}
	// }}}


	// {{{ updateStatusPairs
	/**
	 * Modify the status of the pairs
	 *
	 * @access public
	 * @param  arrays  $pairs   id's of the pairs
	 * @return mixed
	 */
	function updateStatusPairs($pairsId, $statuts)
	{
		$ids = implode(',', $pairsId);

		$fields = array();
		$fields['pair_status'] = $statuts;
		//$fields['pair_wo'] = false;
		$where = " pair_id in ($ids)";
		$res = $this->_update('pairs', $fields, $where);
		return true;
	}
	// }}}


	// {{{ publiPairs
	/**
	 * Modify the publication status of the pairs
	 *
	 * @access public
	 * @param  arrays  $pairs   id's of the pairs
	 * @return mixed
	 */
	function publiPairs($pairsId, $statuts)
	{
		$ids = implode(',', $pairsId);

		$fields = array();
		$fields['pair_pbl'] = $statuts;
		$where = " pair_id in ($ids)";
		$res = $this->_update('pairs', $fields, $where);
		
		$draws = $this->_getRows('pairs', 'pair_drawid', $where);

		foreach($draws as $draw) $drawIds[] = $draw['pair_drawid'];
		$ids = implode(',', $drawIds);
		$fields = array();
		$fields['draw_pbl'] = $statuts;
		$where = " draw_id in ($ids)";
		$res = $this->_update('draws', $fields, $where);
		
		return true;
	}
	// }}}

	// {{{ addPlayerToPair
	/**
	 * Return the column of the players in a draw
	 *
	 * @access public
	 * @param  string  $matchId  id of the match
	 * @return array   information of the match if any
	 */
	function addPlayerToPair($pairId, $i2pId)
	{
		// Retrieve the pairId of the selected player
		$where = "i2p_id = $i2pId";
		$oldPairId = $this->_selectFirst('i2p', 'i2p_pairId', $where);

		// Change the pair of the selected players
		$fields = array();
		$fields['i2p_pairId'] = $pairId;
		$where = "i2p_id = $i2pId";
		$this->_update('i2p', $fields, $where);

		// If there is no more players for the original pair, delete it
		$fields = array('pair_id');
		$tables = array('pairs', 'i2p');
		$where = "pair_id = $oldPairId".
	" AND pair_id = i2p_pairId";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		{
	  $where = "pair_id = $oldPairId";
	  $res = $this->_delete('pairs', $where);

	  $where = "p2m_pairId = $oldPairId";
	  $res = $this->_delete('p2m', $where);

	  $where = "t2r_pairId = $oldPairId";
	  $res = $this->_delete('t2r', $where);
		}

		return true;
	}
	// }}}

	// {{{ getPair
	/**
	 * Return the column of a pair
	 *
	 * @access public
	 * @param  string  $matchId  id of the match
	 * @return array   information of the match if any
	 */
	function getPair($pairId)
	{

		$fields = array('i2p_id', 'mber_sexe','regi_longName',
		      'regi_noc', 'mber_ibfnumber','mber_licence',
		      'pair_ibfNum','pair_natRank', 'pair_intRank',
		      'pair_status', 'pair_id', 'pair_drawId',
		      'pair_cmt', 'pair_disci', 'pair_order','pair_natNum',
		      'pair_wo', 'pair_datewo', 'pair_state', 'pair_cmt');
		$tables = array('pairs', 'i2p', 'registration','members');
		$where = "pair_id = $pairId".
	" AND pair_id = i2p_pairId".
	" AND mber_id = regi_memberId".
	" AND i2p_regiId = regi_id";
		$res = $this->_select($tables, $fields, $where);
		$ut = new utils();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $entry['mber_sexe'] = $ut->getSmaLabel($entry['mber_sexe']);
	  $rows[] = $entry;
		}
		return $rows;
	}

	// }}}

	// {{{ changePairDraw
	/**
	 * Add or update a pair into the database
	 *
	 * @access public
	 * @param  string  $info   column of the pair
	 * @return mixed
	 */
	function changePairDraw($drawId, $pairs)
	{
		if (is_array($pairs))
		$pairIds = $pairs;
		else
		$pairIds[] = $pairs;

		foreach($pairIds as $pairId)
		{
	  // Verifier que la paire n'est pas dans un match termine
	  $fields = array('p2m_id');
	  $tables = array('p2m', 'matchs');
	  $where = "p2m_pairId = $pairId".
	    " AND p2m_matchId = mtch_id".
	    " AND mtch_status >".WBS_MATCH_LIVE;
	  $res = $this->_select($tables, $fields, $where);
	  if ($res->numRows())
	  $err['errMsg'] = 'msgPairInMatch';
	  else
	  {
	  	// Changer le tableau de la paire
	  	$fields = array();
	  	$fields['pair_drawId'] = $drawId;
	  	$where = "pair_id = $pairId";
	  	$res = $this->_update('pairs', $fields, $where);

	  	// Supprimer sa position dans l'ancien tableau
	  	$where = "t2r_pairId = $pairId";
	  	$this->_delete('t2r', $where);

	  	$where = "p2m_pairId = $pairId";
	  	$this->_delete('p2m', $where);
	  }
		}
		if (isset($err))
		return $err;
		else
		return true;

	}
	// }}}

	// {{{ updatePair
	/**
	 * Add or update a pair into the database
	 *
	 * @access public
	 * @param  string  $info   column of the pair
	 * @return mixed
	 */
	function updatePair($infos)
	{
		$utd = new utdate();
		$utd->setFrDate($infos['pair_datewo']);
		$infos['pair_datewo'] = $utd->getIsoDate();

		// Update the pairs
		$where = "pair_id=".$infos['pair_id'];
		$this->_update('pairs', $infos, $where);
		return true;
	}
	// }}}

	/**
	 * Return the list of the players wich are not in a draw
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort users
	 * @param  integer $first  first user to retrieve
	 * @param  integer $step   number of user to retrieve
	 * @return array   array of users
	 */
	function getOtherPairs($sort, $drawId)
	{
		// Retrieve registered players
		$utd = new utdraw();
		$draw = $utd->getDrawById($drawId);
		switch($draw['draw_disci'])
		{
			case WBS_MS:
				$sexe = WBS_MALE;
				$disci = WBS_SINGLE;
				break;
			case WBS_WS:
				$disci = WBS_SINGLE;
				$sexe = WBS_FEMALE;
				break;
			case WBS_AS:
				$disci = WBS_SINGLE;
				break;
			case WBS_MD:
				$sexe = WBS_MALE;
				$disci = WBS_DOUBLE;
				break;
			case WBS_WD:
				$sexe = WBS_FEMALE;
				$disci = WBS_DOUBLE;
				break;
			case WBS_AD:
				$disci = WBS_DOUBLE;
				break;
			default:
				$disci = WBS_MIXED;
				break;
		}

		//$disci = $draw['draw_disci'];

		// Retrieve registered players
		$eventId = utvars::getEventId();
		$fields = array('pair_id', 'mber_sexe', 'regi_longName', 'regi_noc',
		      'rkdf_label', 'i2p_classe', 'i2p_cppp', 'draw_stamp', );
		$tables = array('members', 'registration', 'i2p', 'rankdef',
		      'pairs LEFT JOIN draws ON pair_drawId = draw_id');
		$where = "mber_id = regi_memberId".
	" AND regi_id = i2p_regiId".
	" AND i2p_pairId = pair_id".
	" AND pair_drawId != $drawId".
	" AND pair_disci = $disci".
	" AND i2p_rankdefId = rkdf_id".
	" AND regi_eventId = $eventId".
	" AND (draw_disci =". $draw['draw_disci'].
	" OR draw_disci IS NULL)";
		if (isset($sexe)) $where .= " AND mber_sexe=$sexe";
		$order = abs($sort);
		if ($sort < 0)
		$order .= " DESC";
		$res = $this->_select($tables, $fields, $where, $order);
		$ut = new utils();
		$rows = array();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  		$entry['mber_sexe'] = $ut->getSmaLabel($entry['mber_sexe']);
	  		$entry['i2p_cppp'] = sprintf('4%.02f', $entry['i2p_cppp']);
	  		$rows[] = $entry;
		}
		return $rows;
	}

	// {{{ updatePairsPos
	/**
	 * Update the positions of the pairs in a round
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function updatePairPos($roundId, $pairs)
	{
		// Update the pairs positions
		foreach($pairs as $pair)
		{
	  $column['t2r_posRound'] = $pair['place'];
	  $where = "t2r_pairId=".$pair['id'].
	    " AND t2r_roundId=$roundId";
	  $res = $this->_update('t2r', $column,  $where);
		}
		return true;
	}
	// }}}

	/**
	 * Return the list of the pairs wich are in a round or
	 * can be added to the round
	 *
	 * @access public
	 * @return array   array of users
	 */
	function getPairsForGroup2Ko($drawId, $aGroupName)
	{
		$ut = new utils();

		// Retrieve pairs
		$fields = array('t2r_pairId', 't2r_tds', 'rund_stamp',
		      't2r_tieW', 't2r_points', 't2r_rank', 'rund_qual');
		$tables = array('t2r', 'rounds');
		$where = "t2r_roundId = rund_id".
    	" AND rund_group='" . addslashes($aGroupName) . "'".
		" AND rund_drawId=$drawId".
		" AND rund_type=".WBS_ROUND_GROUP;
		$order = "t2r_rank, rund_stamp";
		$res = $this->_select($tables, $fields, $where, $order);
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($entry['t2r_tds'] != WBS_TDS_NONE)
			{
				$tdsPair[$entry['rund_stamp']] = $entry['t2r_tds'];
				$entry['t2r_tds'] = WBS_TDS_NONE;
			}
			$pairs[$entry['t2r_pairId']] = $entry;
			$pairsId[] = $entry['t2r_pairId'];
		}

		if (!isset($pairsId))
		{
			$err['errMsg'] = "msgNoPairs";
			return $err;
		}

		// Retrieve position of the pairs in final draw
		$fields = array('t2r_pairId', 't2r_tds', 't2r_posRound');
		$tables = array('t2r', 'rounds');
		$where = "t2r_roundId = rund_id".
    	" AND rund_group='" . addslashes($aGroupName) . "'".
		" AND rund_drawId = $drawId".
		" AND rund_type =". WBS_ROUND_MAINDRAW;
		$res = $this->_select($tables, $fields, $where);
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$pairsKo[$entry['t2r_pairId']] = $entry;

		// Retrieve players name of the pairs
		$ids = implode(',', $pairsId);
		$fields = array('i2p_pairId', 'regi_longName', 'team_stamp');
		$tables = array('members', 'registration', 'i2p', 'teams');
		$where = "mber_id = regi_memberId".
" AND regi_id = i2p_regiId".
" AND i2p_pairId IN ($ids)".
" AND team_id = regi_teamId";
		$order = "i2p_pairId, mber_sexe, regi_longName";
		$res = $this->_select($tables, $fields, $where, $order);

		// Liste des tete de serie
		$tdsList[WBS_TDS_NONE] = $ut->getLabel(WBS_TDS_NONE);
		$option = array('key'=>WBS_TDS_NONE,
'value'=>$ut->getLabel(WBS_TDS_NONE));
		$listTds[WBS_TDS_NONE] = $option;

		for ($i=WBS_TDS_1; $i < WBS_TDS_3; $i++)
		{
			$option = array('key'=>$i, 'value'=>$ut->getLabel($i));
			$listTds[$i] = $option;
		}
		for ($i=WBS_TDS_3_4; $i <= WBS_TDS_9_16; $i++)
		{
			$option = array('key'=>$i, 'value'=>$ut->getLabel($i));
			$listTds[$i] = $option;
		}

		// Prepare the pair to be dispayed in a row
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$pair = $pairs[$entry['i2p_pairId']];
			if (isset($pair['players']))
			$lines = $pair['players'];
			else
			$lines = array();
			$lines[] = array('value' =>$entry['regi_longName'],
			//'class' => $class,
			);
			$cells['name'] = $lines;
			$pair['players'] = $lines;

			if (isset($pair['stamps'])) $lines = $pair['stamps'];
			else $lines = array();
			$lines[] = array('value' => $entry['team_stamp'],
			//'class' => $class
			);
			$pair['stamps'] = $lines;
			$pairs[$entry['i2p_pairId']] = $pair;
		}
			
		foreach($pairs as $pair)
		{
			// Mise a jour de la tds de la paire
			// Si les paires sont deja dans le tableau final
			// utiliser la tds deja affecte
			$pos = '';
			$posSort = 100;
			if (isset($pairsKo[$pair['t2r_pairId']]))
			{
				$tds = $pairsKo[$pair['t2r_pairId']]['t2r_tds'];
				$pos = $pairsKo[$pair['t2r_pairId']]['t2r_posRound'];
				$posSort = $pos;
				if (isset($tdsPair[$pair['rund_stamp']]))
				unset($tdsPair[$pair['rund_stamp']]);
			}
			// sinon mettre la tds theorique (1 premier poule A,
			// 2 premier poule B, ....
			else if (isset($tdsPair[$pair['rund_stamp']]))
			{
				$tds = $tdsPair[$pair['rund_stamp']];
				unset($tdsPair[$pair['rund_stamp']]);
			}
			else $tds = WBS_TDS_NONE;
			$selectTds = $listTds;
			$option = $selectTds[$tds];
			$option['select'] = true;
			$selectTds[$tds] = $option;
			$value['select'] = $selectTds;
			$value['name']   = "pairTds";
			$pair['t2r_tds'] = $value;

			// Critere de tri des paires
			// Les paires sont tries suivant leur position
			// dans la tableau final
			$sort1[] = $posSort;
			$sort2[] = $pair['t2r_rank'];
			if (isset($pairsKo))
			{
				$sort3[] = $pair['rund_stamp'];
				$sort4[] = '';
				$sort5[] = '';
			}
			// Les paires sont tries suivant leur classement
			// dans les poules: les premieres de chaque poule
			// puis les seconde des poules avec deux sortant
			// puis le meilleur second
			else
			{
				if ($pair['t2r_rank'] == 2)
				{
					$sort3[] = -$pair['rund_qual'];
					$sort4[] = -$pair['t2r_tieW'];
					$sort5[] = -$pair['t2r_points'];
				}
				else
				{
					$sort3[] = $pair['rund_qual'];
					$sort4[] = $pair['rund_stamp'];
					$sort5[] = '';
				}
			}

			// Saisie de la position de la paire
			$input['input'] = $pos;
			$input['name'] = 'pairPos';
			$input['size'] = 2;
			$row = array();
			$row[] = $pair['t2r_pairId'];
			$row[] = $pair['t2r_tds'];
			$row[] = $input;
			$row[] = $pair['players'];
			$row[] = $pair['rund_stamp'] . $pair['t2r_rank'];
			$row[] = $pair['t2r_tieW'];
			$row[] = $pair['t2r_points'];
			$rows[] = $row;
		}
		array_multisort($sort1, $sort2, $sort3, $sort4, $sort5, $rows);
		return $rows;
	}


	/**
	 * Return the list of the pairs wich are in a round or
	 * can be added to the round
	 *
	 * @access public
	 * @return array   array of users
	 */
	function getPairsForKo($roundId, $nbPlace)
	{
		$ut = new utils();

		// Retrieve pairs already in the round
		$fields = array('t2r_pairId', 't2r_tds', 't2r_posRound',
		      'pair_wo', 'pair_state', 'pair_intRank', 'pair_status', 'pair_order');
		$tables = array('t2r', 'pairs');
		$where = "t2r_roundId = $roundId AND pair_id=t2r_pairId";
		$order = "t2r_posRound";
		$res = $this->_select($tables, $fields, $where, $order);
		$nbPairsInGroup = $res->numRows();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$pairs[$entry['t2r_pairId']] = $entry;
			$pairsId[] = $entry['t2r_pairId'];
		}
		// Retrieve drawId and discipline of the round
		$fields = array('rund_drawId', 'rund_type', 'rund_qualPlace',
		      'draw_disci', 'draw_serial');
		$tables = array('rounds', 'draws');
		$where = "rund_id = $roundId AND rund_drawId = draw_id";
		$res = $this->_select($tables, $fields, $where);

		$entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$drawId = $entry['rund_drawId'];
		$roundType = $entry['rund_type'];
		$nbQ = $entry['rund_qualPlace'];
		$disci = $entry['draw_disci'];
		$serial = $entry['draw_serial'];

		// Retrieve pairs who are not in the round
		$fields = array('pair_id', 'pair_status', 'pair_order',
		      'pair_state', 'pair_intRank', 'pair_wo', 'pair_status', 'pair_order');
		//$tables = array('pairs', 'draws', 'i2p');
		//$where = "pair_drawId = $drawId".
		//	" AND i2p_pairId=pair_id";
		$tables = array('pairs', 'draws');
		$where = "pair_drawId = draw_id".
	" AND draw_disci=$disci".
	" AND draw_serial='$serial'";
		
		$tables = array('pairs');
		$where = "pair_drawId = $drawId";
		if (isset($pairsId))
		{
			$ids = implode(',', $pairsId);
			$where .= " AND pair_id NOT IN ($ids)";
		}

		$order = "pair_status, pair_state, pair_order";
		$res = $this->_select($tables, $fields, $where, $order);
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$col['t2r_pairId'] = $entry['pair_id'];
			$col['t2r_tds'] =  WBS_TDS_NONE;
			$col['t2r_posRound'] = "";
			$col['rund_stamp'] = "";
			$col['t2r_posRound'] = "";
			$col['pair_wo'] = $entry['pair_wo'];
			$col['pair_state'] = $entry['pair_state'];
			$col['pair_intRank'] = $entry['pair_intRank'];
			$col['pair_status'] = $entry['pair_status'];
			$col['pair_order'] = $entry['pair_order'];
			//$col['average'] = 0;
			//$col['nbPlayers'] = 1;
			//$col['rank'] = 1;
			$pairsId[] = $entry['pair_id'];
			$pairs[$entry['pair_id']] = $col;
		}

		if (empty($pairsId)) 
		{
			$err['errMsg'] = 'msgNoPairs';
			return $err;
		}
		
		// Retrieve players name of the pairs
		$ids = implode(',', $pairsId);
		$fields = array('i2p_pairId', 'regi_longName',
		      'rkdf_label', 'team_stamp',
			  'i2p_classe', 'i2p_cppp'
		);
		$tables = array('members', 'registration', 'i2p',
		      'rankdef', 'teams');
		$where = "mber_id = regi_memberId".
	" AND regi_id = i2p_regiId".
	" AND i2p_pairId IN ($ids)".
	" AND i2p_rankdefId = rkdf_id".
		" AND team_id = regi_teamId";
		$order = "i2p_pairId, mber_sexe, regi_longName";
		$res = $this->_select($tables, $fields, $where, $order);

		// Prepare the pair to be dispayed in a row
		$tdsList[WBS_TDS_NONE] = $ut->getLabel(WBS_TDS_NONE);
		$option = array('key'=>WBS_TDS_NONE,
		      'value'=>$ut->getLabel(WBS_TDS_NONE));
		$listTds[WBS_TDS_NONE] = $option;

		for ($i=WBS_TDS_1; $i < WBS_TDS_3; $i++)
		{
			$option = array('key'=>$i, 'value'=>$ut->getLabel($i));
			$listTds[$i] = $option;
		}
		for ($i=WBS_TDS_3_4; $i < WBS_TDS_Q1+$nbQ; $i++)
		{
			$option = array('key'=>$i, 'value'=>$ut->getLabel($i));
			$listTds[$i] = $option;
		}
		for ($i=WBS_TDS_Q1; $i < WBS_TDS_Q5; $i++)
		{
			$option = array('key'=>$i, 'value'=>$ut->getLabel($i));
			$listTds[$i] = $option;
		}

		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$pair = $pairs[$entry['i2p_pairId']];
			if ($pair['pair_wo']) $class = "classWo";
			else  $class = "";

			if (isset($pair['players']))
			{
				$lines = $pair['players'];
				$str = $entry['regi_longName'];
			}
			else
			{
				$lines = array();
				if ($pair['pair_status'] != WBS_PAIR_NONE)
				$str = '('. $ut->getSmaLabel($pair['pair_status']) . $pair['pair_order'] . ') - ' . $entry['regi_longName'];
				else $str = $entry['regi_longName'];
			}
			$lines[] = array('value' =>$str,
			   'class' => $class);
			$cells['name'] = $lines;
			$pair['players'] = $lines;

			if (isset($pair['stamps'])) $lines = $pair['stamps'];
			else $lines = array();
			$lines[] = array('value' => $entry['team_stamp'],
			   'class' => $class
			);
			$pair['stamps'] = $lines;

			if (isset($pair['levels'])) $lines = $pair['levels'];
			else $lines = array();
			if ($pair['pair_intRank'])
			$lines[] = array('value' => "{$pair['pair_intRank']}:{$entry['rkdf_label']}",
    							'class' => $class);
			else
			$lines[] = array('value' => "{$entry['rkdf_label']}",
  							'class' => $class);
			$pair['levels'] = $lines;

			if (isset($pair['rank'])) $pair['rank'] += $entry['i2p_classe'];
			else $pair['rank'] = $entry['i2p_classe'];
			
			if (isset($pair['average'])) $pair['average'] += $entry['i2p_cppp'];
			else $pair['average'] = $entry['i2p_cppp'];
			
			if (isset($pair['nbPlayers'])) $pair['nbPlayers']++;
			else $pair['nbPlayers'] = 1;

			$pairs[$entry['i2p_pairId']] = $pair;
		}
			
		foreach($pairs as $pair)
		{
			// Mise a jour de la tds de la paire
			$selectTds = $listTds;
			if (isset($selectTds[$pair['t2r_tds']]))
			{
				$option = $selectTds[$pair['t2r_tds']];
				$option['select'] = true;
				$selectTds[$pair['t2r_tds']] = $option;
			}
			$value['select'] = $selectTds;
			$value['name']   = "pairTds";
			$pair['t2r_tds'] = $value;
			// Saisie de la position de la paire
			$sortRound[]  = $pair['t2r_posRound'] > 0 ? $pair['t2r_posRound']:1000;
			$sortState[]  = !($pair['pair_state'] > WBS_PAIRSTATE_NOK);
			$input['input'] = $pair['t2r_posRound'];
			$input['name'] = 'pairPos';
			$input['size'] = 2;
			$pair['t2r_posRound'] = $input;

			// Moyenne de la paire
			$pair['average'] /= $pair['nbPlayers'];
			$pair['average'] = sprintf('%.02f', $pair['average']);
			$pair['rank'] /= $pair['nbPlayers'];
			$sortWo[] = $pair['pair_wo'];
			$sortAverage[] = $pair['average'];
			$sortRank[] = $pair['rank'];
			if($pair['pair_status'] != WBS_PAIR_NONE)
			{
				$sortStatus[] = $pair['pair_status'] . $pair['pair_order'];
			}
			else $sortStatus[] = WBS_PAIR_MAINDRAW;

			//if ($pair['pair_intRank']) $sortRank[] = $pair['pair_intRank'];
			//else  $sortRank[] = 9999;
			if ( $pair['pair_wo'] == 1 ) $pair['class'] = 'classWo';
			unset($pair['pair_wo']);
			unset($pair['pair_intRank']);
			unset($pair['pair_status']);
			unset($pair['pair_order']);
			unset($pair['rund_stamp']);
			unset($pair['draw_disci']);
			unset($pair['nbPlayers']);
			unset($pair['pair_state']);
			$rows[] = $pair;
		}
		array_multisort($sortRound, $sortState, $sortWo, $sortStatus, $sortRank, $sortAverage, SORT_DESC, $rows);

		// Tds par defaut si c'est la premiere fois
		if (!$nbPairsInGroup)
		{
			$tds = array(WBS_TDS_1, WBS_TDS_2, WBS_TDS_3_4, WBS_TDS_3_4,
			WBS_TDS_5_8, WBS_TDS_5_8, WBS_TDS_5_8, WBS_TDS_5_8,
			WBS_TDS_9_16, WBS_TDS_9_16, WBS_TDS_9_16,
			WBS_TDS_9_16, WBS_TDS_9_16, WBS_TDS_9_16,
			WBS_TDS_9_16, WBS_TDS_9_16);
			if ($nbPlace < 3) $nbTdsMax = 0;
			else if ($nbPlace < 16) $nbTdsMax = 2;
			else if ($nbPlace < 32) $nbTdsMax = 4;
			else if ($nbPlace < 64) $nbTdsMax = 8;
			else $nbTdsMax = 16;

			for ($i=0; $i< $nbTdsMax; $i++)
			{
				$pair =& $rows[$i];
				$selectTds = $listTds;
				if (isset($selectTds[$tds[$i]]))
				{
					$option = $selectTds[$tds[$i]];
					$option['select'] = true;
					$selectTds[$tds[$i]] = $option;
				}
				$value['select'] = $selectTds;
				$value['name']   = "pairTds";
				$pair['t2r_tds'] = $value;
				$rows[$i] = $pair;
			}
		}

		return $rows;
	}
	// }}}

	// {{{ getPairsForGroup
	/**
	 * Return the list of the pairs wich are in a round or
	 * can be added to the round
	 *
	 * @access public
	 * @return array   array of users
	 */
	function getPairsForGroup($drawId, $nbGroup, $aGroupName)
	{
		$ut = new utils();
		
		// Retrieve pairs of the draws already in group
		$fields = array('pair_id', 't2r_tds',
		      't2r_posRound', 'rund_stamp', 'pair_wo',
		      'draw_disci', 'pair_state', 'pair_status', 'pair_order');
		$tables = array('pairs', 't2r', 'rounds', 'draws');
		$where = "draw_id = $drawId".
		" AND pair_drawId=draw_id".
    	" AND rund_group='" . addslashes($aGroupName) . "'".
		" AND pair_id=t2r_pairId".
		" AND t2r_roundId=rund_id".
		" AND rund_type=".WBS_ROUND_GROUP;
		$order = " rund_stamp, t2r_posRound";
		$res = $this->_select($tables, $fields, $where, $order);
		$nbPairsInGroup = $res->numRows();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$disci = $entry['draw_disci'];
			$pairsId[] = $entry['pair_id'];
			$pairs[$entry['pair_id']] = $entry;
		}

		// Retrieve pairs of the draws not in group
		$fields = array('pair_id', 'pair_wo', 'draw_disci', 'pair_state', 'pair_status', 'pair_order');
		$tables = array('pairs', 'draws', 'i2p');
		$where = "draw_id = $drawId".
	" AND pair_drawId=draw_id".
	" AND i2p_pairId=pair_id";
		if (isset($pairsId))
		{
			$ids = implode(",", $pairsId);
			$where .= " AND pair_id NOT IN ($ids)";
		}
		$res = $this->_select($tables, $fields, $where);
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$disci = $entry['draw_disci'];
			$entry['t2r_tds'] =  WBS_TDS_NONE;
			$entry['t2r_posRound'] = "";
			$entry['rund_stamp'] = "";
			$pairsId[] = $entry['pair_id'];
			$pairs[$entry['pair_id']] = $entry;
		}
		if (empty($pairsId))
		{
			$err['errMsg'] = 'msgNoPairs'; 
			return $err;
		}

		// Retrieve players name of the pairs
		$ids = implode(',', $pairsId);
		$fields = array('i2p_pairId', 'regi_longName',
		      'rkdf_label', 'team_stamp',
		      'i2p_cppp', 'i2p_classe');
		$tables = array('members', 'registration', 'i2p', 'rankdef', 'teams');
		$where = "mber_id = regi_memberId".
	" AND regi_id = i2p_regiId".
	" AND i2p_pairId IN ($ids)".
	" AND i2p_rankdefId = rkdf_id".
        " AND team_id = regi_teamId";

		$order = "i2p_pairId, mber_sexe, regi_longName";
		$res = $this->_select($tables, $fields, $where, $order);

		// Prepare the pair to be dispayed in a row
		$tdsList[WBS_TDS_NONE] = $ut->getLabel(WBS_TDS_NONE);
		$option = array('key'=>WBS_TDS_NONE,
		      'value'=>$ut->getLabel(WBS_TDS_NONE));
		$listTds[WBS_TDS_NONE] = $option;

		for ($i=WBS_TDS_1; $i < WBS_TDS_3; $i++)
		{
			$option = array('key'=>$i, 'value'=>$ut->getLabel($i));
			$listTds[$i] = $option;
		}
		for ($i=WBS_TDS_3_4; $i < WBS_TDS_Q1; $i++)
		{
			$option = array('key'=>$i, 'value'=>$ut->getLabel($i));
			$listTds[$i] = $option;
		}
		for ($i=WBS_TDS_Q1; $i < WBS_TDS_Q5; $i++)
		{
			$option = array('key'=>$i, 'value'=>$ut->getLabel($i));
			$listTds[$i] = $option;
		}

		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$pair = $pairs[$entry['i2p_pairId']];
			if ($pair['pair_wo']) $class = "classWo";
			else  $class = "";

			if (isset($pair['players']))
			{
				$lines = $pair['players'];
				$str = $entry['regi_longName'];
			}
			else
			{
				$lines = array();
				if ($pair['pair_status'] != WBS_PAIR_NONE)
				$str = '('. $ut->getSmaLabel($pair['pair_status']) . $pair['pair_order'] . ') - ' . $entry['regi_longName'];
				else $str = $entry['regi_longName'];
			}
			$lines[] = array('value' => $str,
			   'class' => $class
			);
			$pair['players'] = $lines;

			if (isset($pair['stamps'])) $lines = $pair['stamps'];
			else $lines = array();
			$lines[] = array('value' => $entry['team_stamp'],
			   'class' => $class
			);
			$pair['stamps'] = $lines;

			if (isset($pair['levels'])) $lines = $pair['levels'];
			else $lines = array();
			$lines[] = array('value' => $entry['rkdf_label'],
			   'class' => $class
			);
			$pair['levels'] = $lines;

			if (isset($pair['rank'])) $pair['rank'] += $entry['i2p_classe'];
			else $pair['rank'] = $entry['i2p_classe'];
			if (isset($pair['average'])) $pair['average'] += $entry['i2p_cppp'];
			else $pair['average'] = $entry['i2p_cppp'];
			if (isset($pair['nbPlayers'])) $pair['nbPlayers']++;
			else $pair['nbPlayers'] = 1;

			$pairs[$entry['i2p_pairId']] = $pair;
		}
			
		foreach($pairs as $pair)
		{
			// Mise a jour de la tds de la paire
			$selectTds = $listTds;
			if (isset($selectTds[$pair['t2r_tds']]))
			{
				$option = $selectTds[$pair['t2r_tds']];
				$option['select'] = true;
				$selectTds[$pair['t2r_tds']] = $option;
			}
			$value['select'] = $selectTds;
			$value['name']   = "pairTds";
			$pair['t2r_tds'] = $value;

			// Saisie de la position de la paire
			$input['input'] =  $pair['rund_stamp'].$pair['t2r_posRound'];
			$input['name'] = 'pairPos';
			$input['size'] = 2;
			$tmp = $pair['rund_stamp'].$pair['t2r_posRound'];
			if ($tmp == '') $tmp = 'ZZ'; // pour forcer en derniere position les paires qui ne sont dans les poules
			$sortRound[]  = $tmp;
			$sortState[]  = !($pair['pair_state'] > WBS_PAIRSTATE_NOK);
			if($pair['pair_status'] != WBS_PAIR_NONE)
			{
				$sortStatus[] = $pair['pair_status'] . $pair['pair_order'];
			}
			else $sortStatus[] = WBS_PAIR_MAINDRAW;
			$pair['t2r_posRound'] = $input;

			// Moyenne de la paire
			$pair['average'] /= $pair['nbPlayers'];
			$pair['average'] = sprintf('%.02f', $pair['average']);
			$pair['rank'] /= $pair['nbPlayers'];
			
			// Label du status courant de la paire
			if ($pair['pair_wo']) $label = $ut->getSmaLabel(WBS_PAIR_WDN);
			else $label = ''; //$ut->getSmaLabel($pair['pair_status']);
			//$pair['pair_status'] = $label;
			$sortAverage[] = $pair['average'];
			$sortRank[] = $pair['rank'];
			unset($pair['pair_wo']);
			unset($pair['pair_state']);
			unset($pair['pair_status']);
			unset($pair['pair_order']);
			unset($pair['rund_stamp']);
			unset($pair['draw_disci']);
			unset($pair['nbPlayers']);
			$rows[] = $pair;
		}
		array_multisort($sortRound, $sortState, $sortStatus, $sortRank, $sortAverage, SORT_DESC, $rows);

		// Tds par defaut si c'est la premiere fois
		if (!$nbPairsInGroup)
		{
			$tds = array(WBS_TDS_1, WBS_TDS_2, WBS_TDS_3_4, WBS_TDS_3_4,
			WBS_TDS_5_8, WBS_TDS_5_8, WBS_TDS_5_8, WBS_TDS_5_8,
			WBS_TDS_9_16, WBS_TDS_9_16, WBS_TDS_9_16,
			WBS_TDS_9_16, WBS_TDS_9_16, WBS_TDS_9_16,
			WBS_TDS_9_16, WBS_TDS_9_16);
			if ($nbGroup == 1) $nbTdsMax = 0;
			else if ($nbGroup < 6) $nbTdsMax = 2;
			else if ($nbGroup < 11) $nbTdsMax = 4;
			else if ($nbGroup < 17) $nbTdsMax = 8;
			else $nbTdsMax = 16;

			for ($i=0; $i< $nbTdsMax; $i++)
			{
				$pair =& $rows[$i];
				$selectTds = $listTds;
				if (isset($selectTds[$tds[$i]]))
				{
					$option = $selectTds[$tds[$i]];
					$option['select'] = true;
					$selectTds[$tds[$i]] = $option;
				}
				$value['select'] = $selectTds;
				$value['name']   = "pairTds";
				$pair['t2r_tds'] = $value;
				$rows[$i] = $pair;
			}
		}
		return $rows;
	}
	// }}}


	// {{{ updatePairToRound
	/**
	 * Update the relation of a pair in a round
	 *
	 * @access public
	 * @param  string  $info   column of the pair
	 * @return mixed
	 */
	function updatePairToRound($roundId, $pairId, $tds, $pos, $status)
	{
		// Find the relations of the pair to the round
		$fields = array('t2r_id');
		$tables = array('t2r');
		$where = "t2r_pairId=$pairId".
	" AND t2r_roundId=$roundId";
		$res = $this->_select($tables, $fields, $where);

		// The pair add to be in the round
		if (is_numeric($pos) && ($pos>0))
		{
	  $column['t2r_pairId'] = $pairId;
	  $column['t2r_roundId'] = $roundId;
	  $column['t2r_posRound'] = $pos;
	  $column['t2r_tds'] = $tds;
	  $column['t2r_status'] = $status;
	  // Update existing relation
	  if ($res->numRows())
	  $res = $this->_update('t2r', $column, $where);
	  // Create a new relation
	  else
	  $res = $this->_insert('t2r', $column);
		}
		// The pair is not in the round
		else
		if ($res->numRows())
		$res = $this->_delete('t2r', $where);

		return true;
	}
	// }}}


	// {{{ updatePairToRoundStatus
	/**
	 * Update the relation of a pair in a round
	 *
	 * @access public
	 * @param  string  $info   column of the pair
	 * @return mixed
	 */
	function updatePairToRoundStatus($t2rId, $status)
	{
		// Find the relations of the pair to the round
		$column['t2r_status'] = $status;
		$where = "t2r_id=$t2rId";
		$res = $this->_update('t2r', $column, $where);
		return true;
	}
	// }}}

}
?>
