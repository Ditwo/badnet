<?php
/*****************************************************************************
 !   Module     : Utils
 !   File       : $Source: /cvsroot/aotb/badnet/src/utils/utpubli.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.1 $
 !   Author     : G.CANTEGRIL
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2006/04/06 21:23:42 $
 !   Mailto     : cage@free.fr
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
 * Classe utilitaire pour la publication des donnees
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class utPubli extends utBase
{

	// {{{ properties
	// }}}

	// {{{ publiTeam
	/**
	 * Change le statuts de publication des equipes.
	 * Si propa vaut true, change aussi le statuts des incrits des equipes
	 */
	function publiTeam($teams, $status, $propa=false)
	{
		if (is_array($teams))
		$teamIds = $teams;
		else
		$teamIds[] = $teams;

		// Treat all the teams
		$teamFields['team_pbl'] = $status;
		foreach ( $teamIds as $teamId)
		{
	  // Change the status of the team
	  $where = "team_id=$teamId";
	  $res = $this->_update('teams', $teamFields, $where);

	  // Select members and pairs to be updated
	  $where = "regi_teamId=$teamId";
	  if (!$propa)
	  $where .= " AND regi_pbl < $status";
	  $fields = array('regi_id', 'i2p_pairId');
	  $tables = array('registration LEFT JOIN i2p ON i2p_regiId=regi_id');
	  $res = $this->_select($tables, $fields, $where);
	  $regis = array();
	  $pairs = array();
	  while ($regi = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  {
	  	if ($regi['i2p_pairId'] != '')
	  	$pairs[] = $regi['i2p_pairId'];
	  	$regis[] = $regi['regi_id'];
	  }

	  // Update regi
	  if (count($regis))
	  {
	  	$regiCol['regi_pbl'] = $status;
	  	$where = "regi_id IN (".implode(',', $regis).")";
	  	$res = $this->_update('registration', $regiCol, $where);
	  }
	   
	  // Update pairs
	  if (count($pairs))
	  {
	  	$pairCol['pair_pbl'] = $status;
	  	$where = "pair_id IN (".implode(',', $pairs).")";
	  	$res = $this->_update('pairs', $pairCol, $where);
	  }
		}
		return true;

	}
	// }}}

	// {{{ publiRegi
	/**
	 * Change le statuts de publication d'un membre.
	 *
	 */
	function publiRegi($regis, $status)
	{
		if (is_array($regis))
		$regiIds = $regis;
		else
		$regiIds[] = $regis;

		$fields['regi_pbl'] = $status;
		$cols['pair_pbl'] = $status;
		foreach ( $regiIds as $regiId)
		{
	  // Publication de l'inscrit
	  $where = "regi_id=$regiId";
	  $res = $this->_update('registration', $fields, $where);

	  // Publication de ses paires
	  $where = "i2p_regiId=$regiId";
	  $res = $this->_select('i2p', 'i2p_pairId', $where);
	  $ids = array();
	  while ($pair = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  $ids[] = $pair['i2p_pairId'];
	  if (count($ids))
	  {
	  	$where = "pair_id IN (".implode(',', $ids).")";
	  	$res = $this->_update('pairs', $cols, $where);
	  }
		}
		return true;
	}
	// }}}

	// {{{ publiDraw
	/**
	 * Modify the publication status of the draws
	 */
	function publiDraw($drawsId, $status, $publiSchedu)
	{
		$ids = implode(',', $drawsId);

		// Publication des tableaux
		$fields = array();
		$fields['draw_pbl'] = $status;
		$where = " draw_id in ($ids)";
		$res = $this->_update('draws', $fields, $where);

		// Publication des round
		$fields = array();
		$fields['rund_pbl'] = $status;
		$where = " rund_drawId IN ($ids)";
		$res = $this->_update('rounds', $fields, $where);

		// Publication des paires
		$fields = array();
		$fields['pair_pbl'] = $status;
		$where = " pair_drawId IN ($ids)";
		$res = $this->_update('pairs', $fields, $where);

		// Publication des rencontres pour l'echeancier
		if ($publiSchedu)
		{
	  $where = " rund_drawId IN ($ids)".
	    " AND tie_roundId=rund_id";
	  $tables = array('ties', 'rounds');
	  $res = $this->_select($tables, 'tie_id', $where);

	  $ids = array();
	  while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  $ids[] = $tie['tie_id'];
	  if (count($ids))
	  {
	  	$fields = array();
	  	$fields['tie_pbl'] = $status;
	  	$where = "tie_id IN (".implode(',', $ids).")";
	  	$res = $this->_update('ties', $fields, $where);
	  }
		}
		return true;
	}
	// }}}

	// {{{ publiRound
	/**
	 * Modify the publication status of a round
	 *
	 * @access public
	 * @return mixed
	 */
	function publiRound($roundId, $status, $publiSchedu)
	{
		$fields = array();
		$fields['rund_pbl'] = $status;
		$where = " rund_id = $roundId";
		$res = $this->_update('rounds', $fields, $where);

		// Selection du tableau
		$cols = array('rund_drawId');
		$tables = array('rounds');
		$res = $this->_select($tables, $cols, $where);
		$draw = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$drawId = $draw['rund_drawId'];

		// Mise a jour du tableau
		$fields = array();
		$fields['draw_pbl'] = $status;
		$where = " draw_id = $drawId".
	" AND draw_pbl > $status";
		$res = $this->_update('draws', $fields, $where);

		// Mise a jour des paires
		$fields = array();
		$fields['pair_pbl'] = $status;
		$where = " pair_drawId = $drawId".
	" AND pair_pbl > $status";
		$res = $this->_update('pairs', $fields, $where);

		// Publication des rencontres pour l'echeancier
		if ($publiSchedu)
		{
			$where = "tie_roundId=". $roundId;
			$tables = array('ties');
			$res = $this->_select($tables, 'tie_id', $where);

			$ids = array();
			while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC)) $ids[] = $tie['tie_id'];
			if (count($ids))
			{
				$fields = array();
				$fields['tie_pbl'] = $status;
				$where = "tie_id IN (".implode(',', $ids).")";
				$res = $this->_update('ties', $fields, $where);
			}
		}

		return true;
	}
	// }}}

	// {{{ publiGroups
	/**
	 * Modify the publication status of groups
	 *
	 * @access public
	 * @return mixed
	 */
	function publiGroups($drawId, $status)
	{
		$fields = array();
		$fields['rund_pbl'] = $status;
		$where = " rund_drawId = $drawId".
	" AND rund_type=".WBS_ROUND_GROUP;
		$res = $this->_update('rounds', $fields, $where);

		// Mise a jour du tableau
		$fields = array();
		$fields['draw_pbl'] = $status;
		$where = " draw_id = $drawId".
	" AND draw_pbl > $status";
		$res = $this->_update('draws', $fields, $where);

		// Mise a jour des paires
		$fields = array();
		$fields['pair_pbl'] = $status;
		$where = " pair_drawId = $drawId".
	" AND pair_pbl > $status";
		$res = $this->_update('pairs', $fields, $where);
		return true;
	}
	// }}}

	// {{{ publiTies
	/**
	 * Modify the publication status of the ties
	 */
	function publiTies($tiesId, $status)
	{
		$tables = array('draws', 'rounds', 'ties');
		$where = " draw_eventId=".utvars::getEventId().
	" AND draw_id=rund_drawId".
	" AND tie_roundId=rund_id".
	" AND tie_isBye = 0".
	" AND tie_schedule != ''";
		if (count ($tiesId))
		{
	  $where .= "AND tie_id IN (".implode(',', $tiesId).")";
		}
		$res = $this->_select($tables, 'tie_id', $where);
		$ids = array();
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$ids[] = $tie['tie_id'];
		if (count($ids))
		{
	  $fields = array();
	  $fields['tie_pbl'] = $status;
	  $where = "tie_id IN (".implode(',', $ids).")";
	  $res = $this->_update('ties', $fields, $where);
		}
		return true;
	}
	// }}}
}
?>