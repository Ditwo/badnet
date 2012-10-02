<?php
/*****************************************************************************
 !   Module     : Matches
 !   File       : $Source: /cvsroot/aotb/badnet/services/badnetdraw.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.1 $
 !   Author     : D.BEUVELOT
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2006/11/29 23:48:05 $
 !   Mailto     : didier.beuvelot@free.fr
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
require_once "../src/utils/utko.php";
require_once "dba.php";

/**
 * Acces to the dababase for draws
 *
 * @author Didier BEUVELOT <didier.beuvelot@free.fr>
 * @see to follow
 *
 */

class badnetDraw
{

	// {{{ properties
	// }}}
	function badnetDraw()
	{
		$this->_db = new dba();
	}

	/**
	 * Verifie les autorisations
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function checkAuth($login, $pwd, $eventId)
	{
		$db =& $this->_db;
		// Recherche des droits de l'utilisateur
		$fields = array('user_type', 'user_id');
		$where = "user_login='$login'".
	" AND user_pass='$pwd'";
		$res = $db->selectFirst('users', $fields, $where);

		// Utilisateur inconnu: on se casse
		if (is_null($res))
		{
	  $res['errMsg'] = "Utilisateur inconnu ou mot de passe erron�";
	  return $res;
		}

		// Ce n'est pas un administrateur,
		// A-t-il les droits suffisants sur le tournoi
		if ($res['user_type'] != WBS_AUTH_ADMIN)
		{
	  $userId = $res['user_id'];
	  // Droit sur le tournoi
	  $where = "rght_userId = $userId".
	    " AND rght_theme =".WBS_THEME_EVENT.
	    " AND rght_themeId = $eventId";
	  $right = $db->selectFirst('rights', 'rght_status', $where);

	  // Pas de droit: on se casse
	  if (is_null($right))
	  {
	  	$res['errMsg'] = "Droit insuffisant sur le tournoi $eventId";
	  	return $res;
	  }

	  // Autre droit que ceux autorises :on se casse
	  if ($right != WBS_AUTH_MANAGER &&
	  $rigth != WBS_AUTH_ADMIN &&
	  $rigth != WBS_AUTH_ASSISTANT)
	  {
	  	$res['errMsg'] = "Droit insuffisant sur le tournoi $eventId";
	  	return $res;
	  }
		}
		return true;
	}

	/**
	 * Mise a jour des paires dans un tableau KO
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function updateDraw($eventId, $roundUniId, $pairs)
	{
		$db =& $this->_db;

		// Identifiant du tableau
		$tables = array('draws', 'rounds');
		$where = "rund_uniId='$roundUniId'".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId";
		$roundId = $db->selectFirst($tables, 'rund_id', $where);
		if (is_null($roundId))
		{
	  $res['errMsg'] = "Tableau KO introuvable:rund_uniId={$roundUniId}";
	  return $res;
		}

		// Suppprimer les paires deja dans le tableau
		$where = "t2r_roundId = $roundId";
		$db->delete('t2r', $where);

		// Positionner les nouvelles paires dans le tableau
		$err = array();
		$tables = array('pairs', 'i2p', 'registration');
		foreach($pairs as $pair)
		{
	  $pair['t2r_roundId'] = $roundId;
	  $where = "regi_eventId = $eventId".
	    " AND regi_id=i2p_regiId".
	    " AND i2p_pairId = pair_id".
	    " AND pair_uniId = '{$pair['t2r_pairId']}'";
	  $pair['t2r_pairId'] = $db->selectFirst($tables, 'pair_id', $where);
	  if (is_null($pair['t2r_pairId']))
	  {
	  	$err['errMsg'] = "Paire introuvable";
	  }
	  $db->insert('t2r', $pair);
		}

		// Supprimer les anciennes paires des matchs
		$fields = array('mtch_id');
		$tables = array('p2m', 'matchs', 'ties');
		$where = "tie_id=mtch_tieId".
	" AND tie_roundId=$roundId".
	" AND p2m_matchId=mtch_id".
	" AND (p2m_result =".WBS_RES_NOPLAY.
	" OR tie_isBye = 1)";
		$order = 'mtch_num';
		$res = $db->select($tables, 'mtch_id', $where,$order);
		if ($res)
		{
			$matchIds = $res->fetch(PDO::FETCH_COLUMN);
			// Delete relation between pairs and match
			$ids = implode(',', $matchIds);
			$where = 'p2m_matchId IN ('.$ids.')';
			$db->delete('p2m', $where);
		}

		return $this->createPairToMatch($roundId);
	}


	// {{{ createPairToMatch
	/**
	 * Create the relation between the pairs and the matchs
	 * of a round
	 *
	 * @access public
	 * @param  string  $info   column of the pair
	 * @return mixed
	 */
	function createPairToMatch($roundId)
	{
		$db =& $this->_db;

		// Find the pair id in the round
		$fields = array('t2r_pairId', 't2r_posRound');
		$where = "t2r_roundId = $roundId";
		$order = " t2r_posRound";
		$res = $db->select('t2r', $fields, $where, $order);
		$pairs = array();
		if ($res) $pairs = $res->fetchAll(PDO::FETCH_ASSOC);

		if (!count($pairs)) return;

		// Use utKo to obtain real position of the pair
		// in the round and byes position
		$entries = $db->selectFirst('rounds', 'rund_entries', "rund_id = $roundId");
		$utk = new utko($pairs, $entries);
		$byes  = $utk->getByesTie();
		$start  = ($utk->getSize()/2)-1;

		// Find the matchs of the round (for KO)
		$fields = array('mtch_id', 'tie_posRound');
		$tables = array('matchs', 'ties');
		$where = "tie_id=mtch_tieId".
	" AND tie_roundId=$roundId";
		$order = 'tie_posRound DESC';
		$res = $db->select($tables, $fields, $where, $order);
		while ($match = $res->fetch(PDO::FETCH_ASSOC))
		$matchIds[$match['tie_posround']] = $match['mtch_id'];

		// Create relation between pair and match
		foreach($pairs as $pair)
		{
	  $pairId = $pair['t2r_pairid'];
	  $numTie = intval($start+($pair['t2r_posround']-1)/2);
	  $posInTie = ($pair['t2r_posround']%2) ? WBS_TEAM_TOP:WBS_TEAM_BOTTOM;

	  $fields = array();
	  $fields['p2m_pairId'] = $pairId;
	  $fields['p2m_result'] = WBS_RES_NOPLAY;
	  // There is bye, so create relation between
	  // pair and match for the next match
	  if(in_array($numTie, $byes))
	  {
	  	$nextTie = intval(($numTie-1)/2);
	  	$fields['p2m_posMatch'] = ($numTie%2) ? WBS_PAIR_TOP:WBS_PAIR_BOTTOM;
	  	$fields['p2m_matchId'] = $matchIds[$nextTie];
	  	$mids[] = $matchIds[$nextTie];
	  	$db->insert('p2m', $fields);
	  	$fields['p2m_result'] = WBS_RES_WIN;
	  }

	  $fields['p2m_posMatch'] = $posInTie;
	  $fields['p2m_matchId'] = $matchIds[$numTie];
	  $mids[] = $matchIds[$numTie];
	  $db->insert('p2m', $fields);
		}

		// Update the status of the matches
		// Count the pairs of the matches
		$fields = array( 'count(*) as nb', 'p2m_matchId');
		$tables = array('p2m');
		$where = 'p2m_matchId IN ('.implode(',', $mids).')'.
	" GROUP BY p2m_matchId";
		$res = $db->select($tables, $fields, $where);

		// Select match with 2 pairs
		$matchIds = array();
		while ($match = $res->fetch(PDO::FETCH_ASSOC))
		{
	  if ($match['nb'] == 2) $matchIds[] = $match['p2m_matchid'];
		}

		// Update the status of match with 2 pairs
		if (count($matchIds))
		{
	  $fields = array();
	  $fields['mtch_status'] = WBS_MATCH_READY;
	  $where = "mtch_id IN(".implode(',', $matchIds).')';
	  $db->update('matchs', $fields, $where);
		}
		return true;
	}
	// }}}

}
?>
