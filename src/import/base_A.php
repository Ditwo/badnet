<?php
/*****************************************************************************
 !   Module     : Import
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.4 $
 !   Date       : $Date: 2007/01/18 07:51:18 $
 ******************************************************************************/

require_once "utils/utbase.php";

/**
 * Acces to the dababase for importation
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class importBase_A extends utBase
{

	/**
	 * Update rank for player
	 *
	 * @access public
	 * @param array   $ids  Ids to keep
	 * @return none
	 */
	function updateRankFromFede($ranks)
	{
		// Traiter chaque joueur
		$rank['rank_isFede'] = 1;
		$ranksId = $this->_getC2i();
		foreach($ranks as $player)
		{
			$member['mber_licence']  = $player['mber_licence'];
			if ( !empty($player['mber_fedeid']) ) $member['mber_fedeid']  = $player['mber_fedeid'];
			if ( !empty($player['mber_born']) ) $member['mber_born']  = $player['mber_born'];
			$where = "mber_id=".$player['mber_id'];
			$res = $this->_update('members', $member, $where);

			$upt = false;
			if ( !empty($player['regi_catage']) )
			{
				$regi['regi_catage']    = $player['regi_catage'];
				$upt = true;
			}
			if ( !empty($player['regi_numcatage']) )
			{
				$regi['regi_numcatage'] = $player['regi_numcatage'];
				$upt = true;
			}
			$regi['regi_dateauto']  = $player['regi_dateauto'];
			if ( !empty($player['regi_surclasse']) )
			{
				$regi['regi_surclasse'] = $player['regi_surclasse'];
				$upt = true;
			}
			if ( !empty($player['regi_datesurclasse']) )
			{
				$regi['regi_datesurclasse'] = $player['regi_datesurclasse'];
				$upt = true;
			}
			$where = "regi_id=".$player['regi_id'];
			$res = $this->_update('registration', $regi, $where);
			if (isset($ranksId[$player['rank_simple']]))
			{
				$rank['rank_rankdefId'] = $ranksId[$player['rank_simple']];
				$rank['rank_average'] = $player['rank_cpppsimple'];
				$rank['rank_dateFede'] = $player['rank_date'];
				$rank['rank_regiId'] = $player['regi_id'];
				$rank['rank_disci'] = $player['mber_sexe']==WBS_MALE ? WBS_MS:WBS_LS;
				$rank['rank_rank'] = $player['rank_ranks'];
				$rank['rank_discipline'] = WBS_SINGLE;
				$this->_updateRank($rank, true);
				// Mise a jour des relations avec les paires
				if (!utvars::isTeamEvent())
				{
					$tables = array('i2p', 'pairs');
					$where = 'i2p_pairid=pair_id';
					$where .= ' AND i2p_regiid=' . $rank['rank_regiId'];
					$where .= ' AND pair_disci=' . $rank['rank_discipline'];
					$i2pId = $this->_selectFirst($tables, 'i2p_id', $where);

					$where = 'i2p_id=' . $i2pId;
					$cols['i2p_rankdefid'] = $rank['rank_rankdefId'];
					$cols['i2p_cppp'] = $rank['rank_average'];
					$cols['i2p_classe'] = $rank['rank_rank'];
					$this->_update('i2p', $cols, $where);
				}
			}

			if (isset($ranksId[$player['rank_double']]))
			{
				$rank['rank_rankdefId'] = $ranksId[$player['rank_double']];
				$rank['rank_average'] = $player['rank_cpppdouble'];
				$rank['rank_disci'] = $player['mber_sexe']==WBS_MALE ? WBS_MD:WBS_LD;
				$rank['rank_rank'] = $player['rank_rankd'];
				$rank['rank_discipline'] = WBS_DOUBLE;
				$this->_updateRank($rank, true);
				// Mise a jour des relations avec les paires
				if (!utvars::isTeamEvent())
				{
					$tables = array('i2p', 'pairs');
					$where = 'i2p_pairid=pair_id';
					$where .= ' AND i2p_regiid=' . $rank['rank_regiId'];
					$where .= ' AND pair_disci=' . $rank['rank_discipline'];
					$i2pId = $this->_selectFirst($tables, 'i2p_id', $where);

					$where = 'i2p_id=' . $i2pId;
					$cols['i2p_rankdefid'] = $rank['rank_rankdefId'];
					$cols['i2p_cppp'] = $rank['rank_average'];
					$cols['i2p_classe'] = $rank['rank_rank'];
					$this->_update('i2p', $cols, $where);
				}
			}
			if (isset($ranksId[$player['rank_mixte']]))
			{
				$rank['rank_rankdefId'] = $ranksId[$player['rank_mixte']];
				$rank['rank_average'] = $player['rank_cpppmixte'];
				$rank['rank_disci'] = WBS_MX;
				$rank['rank_rank'] = $player['rank_rankm'];
				$rank['rank_discipline'] = WBS_MIXED;
				$this->_updateRank($rank, true);
				// Mise a jour des relations avec les paires
				if (!utvars::isTeamEvent())
				{
					$tables = array('i2p', 'pairs');
					$where = 'i2p_pairid=pair_id';
					$where .= ' AND i2p_regiid=' . $rank['rank_regiId'];
					$where .= ' AND pair_disci=' . $rank['rank_discipline'];
					$i2pId = $this->_selectFirst($tables, 'i2p_id', $where);

					$where = 'i2p_id=' . $i2pId;
					$cols['i2p_rankdefid'] = $rank['rank_rankdefId'];
					$cols['i2p_cppp'] = $rank['rank_average'];
					$cols['i2p_classe'] = $rank['rank_rank'];
					$this->_update('i2p', $cols, $where);
				}
			}
		}
	}

	// Classement to Index
	function _getC2i()
	{
		$fields = array('rkdf_id', 'rkdf_label');
		$tables = array('events', 'rankdef');
		$where = "evnt_rankSystem=rkdf_system".
	" AND evnt_id=".utvars::getEventId().
      " ORDER BY rkdf_point";
		$res = $this->_select($tables, $fields, $where);
		while($infos = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$ranks[$infos['rkdf_label']] = $infos['rkdf_id'];
		}
		$ranks[''] = $infos['rkdf_id'];
		return $ranks;
	}
	// }}}

	// {{{ _updateRank
	/**
	 * Add or update a rank for a player
	 *
	 * @access private
	 * @param  array   $infos Rankings data
	 * @return mixed
	 */
	function _updateRank($infos, $isFromFede = false)
	{

		// Select the ranking of registration
		$fields = array('rank_id', 'rank_average', 'rank_rankdefId',
		      'rank_isFede', 'rank_disci', 'rank_discipline');
		$tables = array('ranks');
		$where  = "rank_discipline=".$infos['rank_discipline'];
		$where .= " AND rank_regiId=".$infos['rank_regiId'];
		$res = $this->_select($tables, $fields, $where);
		// Create or Update the rank
		if ($res->numRows())
		{
			$data = $res->fetchrow(DB_FETCHMODE_ASSOC);
			$where = "rank_id=". $data['rank_id'];
			if ($isFromFede)
			$res = $this->_update('ranks', $infos, $where);
			else
			{
				if ($data['rank_average'] != $infos['rank_average'] ||
				$data['rank_rankdefId'] != $infos['rank_rankdefId'])
				$res = $this->_update('ranks', $infos, $where);
			}
		}
		else $res = $this->_insert('ranks', $infos);

		if (!utvars::isTeamEvent())
		{
			$fields = array('i2p_id');
			$tables = array('i2p', 'pairs', 'registration');
			$where  = "regi_id=i2p_regiid";
			$where  .= " AND i2p_pairid=pair_id";
			$where  .= " AND pair_disci=".$infos['rank_discipline'];
			$where  .= " AND regi_id=".$infos['rank_regiId'];
			$i2pId = $this->_selectFirst($tables, $fields, $where);
			$infos = array('i2p_rankdefid' => $infos['rank_rankdefId'],
      						'i2p_cppp' => $infos['rank_average'],
      						'i2p_classe' => $infos['rank_rank']);
			$res = $this->_update('i2p', $infos, 'i2p_id='.$i2pId);
		}
		return true;
	}
	// }}}
}

?>