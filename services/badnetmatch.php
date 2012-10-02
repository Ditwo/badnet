<?php
/*****************************************************************************
 !   Module     : Matches
 !   File       : $Source: /cvsroot/aotb/badnet/services/badnetmatch.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.9 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/06 09:19:37 $
 ******************************************************************************/
require_once "dba.php";
require_once "../src/utils/utdate.php";
require_once "../src/utils/utscore.php";

/**
 * Acces to the dababase for matches
 */

class badnetMatch
{

	// {{{ properties
	// }}}
	function badnetMatch()
	{
		$this->_db = new dba();
	}

	// {{{ checkAuth
	/**
	 * Verifie les autorisations
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function checkAuth($login, $pwd, $eventId, $matchUniId)
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
				$res['errMsg'] = "Droit insuffisant sur le tournoi $eventId. Aucun droit";
				return $res;
			}

			// Statut du match
			$tables = array('matchs', 'ties', 'rounds', 'draws');
			$where = "mtch_uniId='$matchUniId'".
	    " AND mtch_tieId = tie_id".
	    " AND tie_roundId = rund_id".
	    " AND rund_drawId = draw_id".
	    " AND draw_eventId = $eventId";
			$matchStatus = $db->selectFirst($tables, 'mtch_status', $where);

			// Assistant mais match deja valide:on se casse
			if ($right == WBS_AUTH_ASSISTANT &&
			$res['mtch_status']> WBS_MATCH_ENDED)
			{
				$res['errMsg'] = "Droit insuffisant sur le tournoi $eventId. Match déjà validé";
				return $res;
			}

			// Autre droit que ceux autorises :on se casse
			if (($right != WBS_AUTH_MANAGER) &&
			($right != WBS_AUTH_ADMIN) &&
			($right != WBS_AUTH_ASSISTANT))
			{
				$res['errMsg'] = "Droit insuffisant sur le tournoi $eventId. Droit inconnu $right";
				return $res;
			}
		}
		return true;
	}
	// }}}

	//----------Pour le facteur  ----------------
	// {{{ getMatch
	/**
	 * Return a complete match
	 *
	 * @access public
	 * @param  string  $matchId  id of the match
	 * @return array   information of the match if any
	 */
	function getMatch($matchUniId)
	{
		// Retrieve informations of the match
		$fields = array('mtch_num', 'mtch_discipline', 'p2m_pairId',
		      'regi_id', 'regi_longName', 'regi_shortName', 
		      'mtch_score', 'team_stamp', 'team_uniid',  
		      'mtch_court', 'asso_logo', 'team_logo',
		      'mtch_id', 'rund_type', 'tie_posRound', 'pair_uniId');
		$tables = array('matchs', 'p2m', 'i2p', 'registration', 'teams',
		      'assocs', 'a2t', 'ties', 'rounds', 'draws', 'pairs');

		$where = "";

		$where .= " mtch_uniId = '$matchUniId'".
	" AND mtch_id=p2m_matchId".
	" AND p2m_pairId=pair_id".
	" AND pair_id=i2p_pairId".
	" AND i2p_regiId=regi_id".
	" AND regi_teamId=team_id".
	" AND asso_id=a2t_assoId".
	" AND team_id=a2t_teamId".
	" AND mtch_tieId=tie_id".
	" AND tie_roundId=rund_id".
	" AND rund_drawId=draw_id";

		$order = "p2m_pairId";
		$db =& $this->_db;
		$res = $db->select($tables, $fields, $where);

		$nb = 0;
		$pairId = -1;
		$players=array();
		while($data = $res->fetch(PDO::FETCH_ASSOC))
		{
			// Premiere iteration : initialisation des
			// information generales du  match
			if (!$nb)
			{
				$match['idMatch'] = $data['mtch_id'];
				$match['numMatch'] = $data['mtch_num'];
				$match['numCourt'] = $data['mtch_court'];
				$match['score'] = $data['mtch_score'];
				$match['discipline'] = $data['mtch_discipline'];
				if ($data['rund_type']== WBS_ROUND_QUALIF)
				$match['stade'] = "q";
				else if ($data['tie_posRound']==0)
				$match['stade'] = "1";
				else if ($data['tie_posRound']<3)
				$match['stade'] = "2";
				else if ($data['tie_posRound']<7)
				$match['stade'] = "4";
				else if ($data['tie_posRound']<15)
				$match['stade'] = "8";
				else if ($data['tie_posRound']<31)
				$match['stade'] = "16";
				else
				$match['stade'] = "32";
					
				$match['serie'] = "none";
				$match['nbGame'] = 3;
				$match['nbPoint'] = 21;
				$match['nbProl'] = 3;
				$match['isProlong'] = false;
			}
			// Nouvelle paire
			if( $pairId != $data['p2m_pairid'])
			{
				// Memorisation de la paire precedente
				if ($nb)
				{
					$pair['players'] = $players;
					$pair['impair'] = ($nb == 2) ? $player['id']:-1;
					$pairs["_{$pairId}"] = $pair;
					$nb = 0;
				}

				$pair['id'] = $data['p2m_pairid'];
				$pair['uniid'] = $data['pair_uniId'];
				$pair['teamuniid'] = $data['team_uniid'];
				$pair['sc1'] = '00';
				$pair['sc2'] = '00';
				$pair['sc3'] = '00';
				$pair['pair'] = $data['regi_id'];
				$pairId = $data['p2m_pairid'];
				$players = array();
			}

			$player['serve'] = 0;
			if ($data['team_logo'] != "")
			$player['flag'] = $data['team_logo'];
			else
			if ($data['asso_logo'] != "")
			$player['flag'] = $data['asso_logo'];
			else
			$player['flag'] = ''; //utimg::getIcon('empty');
			$player['name'] = $data['regi_longName'];
			$player['id'] = $data['regi_id'];
			$players["_{$data['regi_id']}"] = $player;
			$nb++;
		}
		$pair['players'] = $players;
		$pair['impair'] = ($nb == 2) ? $player['id']:-1;
		$pairs["_{$pairId}"] = $pair;


		$match['pairs'] = $pairs;
		$pair = reset($pairs);
		$match['pairG'] = "_{$pair['id']}";
		$pair = next($pairs);
		$match['pairD'] = "_{$pair['id']}";
		return $match;
	}
	// }}}

	// {{{ updateMatchResult
	/**
	 * Update the result of a match for a individual event
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function updateMatchResult($eventId, $match, $winPairUniId, $loosPairUniId)
	{
		$db =& $this->_db;
		// Identifiant du match a mettre a jour
		$tables = array('matchs', 'ties', 'rounds', 'draws');
		$where = "mtch_uniId='{$match['mtch_uniId']}'".
	" AND mtch_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId";
		$res = $db->selectFirst($tables, array('mtch_id', 'tie_place'), $where);
		$match['mtch_id'] = $res['mtch_id'];
		$place = $res['tie_place'];

		// Identifiants des paires
		$tables = array('pairs', 'i2p', 'registration');
		$where = "pair_uniId='{$winPairUniId}'".
	" AND pair_id = i2p_pairId".
	" AND i2p_regiId = regi_id".
	" AND regi_eventId = $eventId";
		$winPairId = $db->selectFirst($tables, 'pair_id', $where);
		$where = "pair_uniId='{$loosPairUniId}'".
	" AND pair_id = i2p_pairId".
	" AND i2p_regiId = regi_id".
	" AND regi_eventId = $eventId";
		$loosPairId = $db->selectFirst($tables, 'pair_id', $where);

		// Updating the match
		$utd = new utdate();
		$utd->setFrDateTime($match['mtch_begin']);
		$match['mtch_begin'] = $utd->getIsoDateTime();
		$utd->setFrDateTime($match['mtch_end']);
		$match['mtch_end'] = $utd->getIsoDateTime();

		$where = "mtch_id={$match['mtch_id']}";
		$db->update('matchs', $match, $where);

		// Remember pevious winner of the match
		$where = "p2m_matchId=".$match['mtch_id'].
	" AND (p2m_result =". WBS_RES_WINWO .
	" OR p2m_result =". WBS_RES_WINAB .
	" OR p2m_result =". WBS_RES_WIN .")";
		$oldWinner = $db->selectFirst('p2m', 'p2m_pairId', $where);
		if (empty($oldWinner)) $oldWinner = -1;

		// Updating relation between pairs and match for winner
		$uts = new utscore();
		$sco = $uts->setScore($match['mtch_score']);

		$fields = array();
		if ($match["mtch_status"] == WBS_MATCH_READY) $fields['p2m_result'] = WBS_RES_NOPLAY;
		else if ($uts->isWO()) $fields['p2m_result'] = WBS_RES_WINWO;
		else if ($uts->isAbort()) $fields['p2m_result'] = WBS_RES_WINAB;
		else $fields['p2m_result'] = WBS_RES_WIN;
		$where = "p2m_matchId=".$match['mtch_id'].
	" AND p2m_pairId= $winPairId";
		$db->update('p2m', $fields, $where);

		// Relation between match and pair for looser
		$fields = array();
		if ($match["mtch_status"] == WBS_MATCH_READY) $fields['p2m_result'] = WBS_RES_NOPLAY;
		else if ($uts->isWO()) $fields['p2m_result'] = WBS_RES_LOOSEWO;
		else if ($uts->isAbort()) $fields['p2m_result'] = WBS_RES_LOOSEAB;
		else $fields['p2m_result'] = WBS_RES_LOOSE;
		$where = "p2m_matchId=".$match['mtch_id'].
	" AND p2m_pairId= $loosPairId";
		$db->update('p2m', $fields, $where);

		// -------- Now updating the match of the next tie ---------
		// find tie info of the match
		$fields = array('tie_posRound', 'tie_roundId', 'rund_type', 'tie_looserdrawid',
		      'rund_size', 'rund_drawId','tie_isBye');
		$tables = array('matchs', 'ties', 'rounds');
		$where = "mtch_tieId = tie_id".
	" AND mtch_id=".$match['mtch_id'].
	" AND tie_roundId=rund_id";
		$data = $db->selectFirst($tables, $fields, $where);

		// Match in ko round
		if ($data['rund_type'] != WBS_ROUND_GROUP && $data['tie_posRound'])
		{
			if($match['mtch_luckylooser'] == WBS_YES)
			{
				$this->_updateNextKoTie($data, $loosPairId);
				$this->_updateOtherKoTie($data, $winPairId);
			}
			else
			{
				$this->_updateNextKoTie($data, $winPairId);
				$this->_updateOtherKoTie($data, $loosPairId);
			}
		}

		// Match in group round
		if ($data['rund_type'] == WBS_ROUND_GROUP)
		$this->_updateGroupMatch($winPairId, $oldWinner, $data, $match);

		// Updating the rest of the players
		if ($match["mtch_status"] >= WBS_MATCH_ENDED && !$uts->isWO())
		{
			// Updating rest time of players
			$err = $this->updateRestTime($match['mtch_id'], $match['mtch_end']);
		}

		// Updating status of other matches
		$this->updateStatusMatch($eventId, $place);
		return true;
	}
	// }}}

	// {{{ _updateGroupMatch
	/**
	 * Calculate the rank in the group and update number et schedule of matches
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function _updateGroupMatch($winPairId, $oldWinner, $data, $match)
	{
		$db =& $this->_db;
		$this->_updateGroupRank($data['tie_roundId']);
		
		// Pour une poule de trois, si c'est le premier match
		// mettre a jour les numeros et horaires des deux autres matches
		if ($data['rund_size'] == 3 &&
		$data['tie_posRound'] == 2)
		{
			// Si le vainqueur est le troisieme joueur, il faut
			// inverser numero et heure des deux autres matches
			// Position du vainqueur
			$where = "t2r_pairId=$winPairId";
			$res = $db->select('t2r', 't2r_posRound', $where);
			$tmp = $res->fetch(PDO::FETCH_ASSOC);

			//il est en troisieme position
			if( ($oldWinner == -1 && $tmp['t2r_posRound'] == 3) ||
			($oldWinner != -1 && $oldWinner != $winPairId))
			{
				// recherche des autres matchs de la poule
				$fields = array('tie_schedule', 'tie_place', 'tie_court',
			      'mtch_num', 'tie_id', 'mtch_id');
				$tables = array('ties', 'matchs');
				$where = "tie_id = mtch_tieId".
		" AND tie_roundId =". $data['tie_roundId'].
		" AND mtch_id !=". $match['mtch_id'];
				$res = $db->select($tables, $fields, $where);

				// Il doit y avoir 2 matchs
				if ($db->numRows($res) == 2)
				{
					$res = $db->select($tables, $fields, $where);
					$tmp1 = $res->fetch(PDO::FETCH_ASSOC);
					$tmp2 = $res->fetch(PDO::FETCH_ASSOC);
					$cols = array();
					$cols['mtch_num'] = $tmp1['mtch_num'];
					$where = "mtch_id=".$tmp2['mtch_id'];
					$db->update('matchs', $cols, $where);
					$cols['mtch_num'] = $tmp2['mtch_num'];
					$where = "mtch_id=".$tmp1['mtch_id'];
					$db->update('matchs', $cols, $where);
					$cols = array();
					$cols['tie_schedule'] = $tmp1['tie_schedule'];
					$cols['tie_place'] = $tmp1['tie_place'];
					$cols['tie_court'] = $tmp1['tie_court'];
					$where = "tie_id=".$tmp2['tie_id'];
					$db->update('ties', $cols, $where);
					$cols['tie_schedule'] = $tmp2['tie_schedule'];
					$cols['tie_place'] = $tmp2['tie_place'];
					$cols['tie_court'] = $tmp2['tie_court'];
					$where = "tie_id=".$tmp1['tie_id'];
					$db->update('ties', $cols, $where);
				}
				else echo "__FILE__.' ('.__LINE__.') : Ca etre bizarre!!!";
			}
		}

		// Pour une poule de quatre, si c'est le premier match du premier tour
		// mettre a jour les numeros et horaires des quatres autres matches
		if ($data['rund_size'] == 4 &&
		$data['tie_posRound'] == 1)
		{
			// Si le vainqueur est le troisieme joueur, il faut
			// inverser numero et heure des quatres autres matches
			// Position du vainqueur
			$where = "t2r_pairId=$winPairId";
			$res = $db->select('t2r', 't2r_posRound', $where);
			$tmp = $res->fetch(PDO::FETCH_ASSOC);

			//il est en troisieme position
			if( ($oldWinner == -1 && $tmp['t2r_posRound'] == 3) ||
			($oldWinner != -1 && $oldWinner != $winPairId))
			{
				// recherche des autres matchs de la poule
				$fields = array('tie_schedule', 'tie_place', 'tie_court',
			      'mtch_num', 'tie_id', 'mtch_id');
				$tables = array('ties', 'matchs');
				$where = "tie_id = mtch_tieId".
		" AND tie_roundId =". $data['tie_roundId'].
		" AND tie_posRound in (0,2,3,5)";
				$order = 'tie_posRound';
				$res = $db->select($tables, $fields, $where, $order);
				// Il doit y avoir 4 match
				if ($db->numRows($res) == 4)
				{
					$res = $db->select($tables, $fields, $where, $order);
					$tmp0 = $res->fetch(PDO::FETCH_ASSOC);
					$tmp2 = $res->fetch(PDO::FETCH_ASSOC);
					$tmp3 = $res->fetch(PDO::FETCH_ASSOC);
					$tmp5 = $res->fetch(PDO::FETCH_ASSOC);
					$cols = array();
					$cols['mtch_num'] = $tmp0['mtch_num'];
					$where = "mtch_id=".$tmp2['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols['mtch_num'] = $tmp2['mtch_num'];
					$where = "mtch_id=".$tmp0['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols['mtch_num'] = $tmp3['mtch_num'];
					$where = "mtch_id=".$tmp5['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols['mtch_num'] = $tmp5['mtch_num'];
					$where = "mtch_id=".$tmp3['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols = array();
					$cols['tie_schedule'] = $tmp0['tie_schedule'];
					$cols['tie_place'] = $tmp0['tie_place'];
					$cols['tie_court'] = $tmp0['tie_court'];
					$where = "tie_id=".$tmp2['tie_id'];
					$db->update('ties', $cols, $where);

					$cols['tie_schedule'] = $tmp2['tie_schedule'];
					$cols['tie_place'] = $tmp2['tie_place'];
					$cols['tie_court'] = $tmp2['tie_court'];
					$where = "tie_id=".$tmp0['tie_id'];
					$db->update('ties', $cols, $where);

					$cols['tie_schedule'] = $tmp3['tie_schedule'];
					$cols['tie_place'] = $tmp3['tie_place'];
					$cols['tie_court'] = $tmp3['tie_court'];
					$where = "tie_id=".$tmp5['tie_id'];
					$db->update('ties', $cols, $where);

					$cols['tie_schedule'] = $tmp5['tie_schedule'];
					$cols['tie_place'] = $tmp5['tie_place'];
					$cols['tie_court'] = $tmp5['tie_court'];
					$where = "tie_id=".$tmp3['tie_id'];
					$db->update('ties', $cols, $where);
				}
				else
				echo "__FILE__.' ('.__LINE__.') :".
		  "Ca etre bizarre!!!";
			}
		}

		// Pour une poule de quatre, si c'est le deuxieme match du premier tour
		// mettre a jour les numeros et horaires des quatres autres matches
		if ($data['rund_size'] == 4 &&
		$data['tie_posRound'] == 4)
		{
			// Si le vainqueur est la quatrieme paire, il faut
			// inverser numero et heure des quatres autres matches
			// Position du vainqueur
			$where = "t2r_pairId=$winPairId";
			$res = $db->select('t2r', 't2r_posRound', $where);
			$tmp = $res->fetch(PDO::FETCH_ASSOC);

			//il est en troisieme position
			if( ($oldWinner == -1 && $tmp['t2r_posRound'] == 4) ||
			($oldWinner != -1 && $oldWinner != $winPairId))
			{
				// recherche des autres matchs de la poule
				$fields = array('tie_schedule', 'tie_place', 'tie_court',
			      'mtch_num', 'tie_id', 'mtch_id');
				$tables = array('ties', 'matchs');
				$where = "tie_id = mtch_tieId".
		" AND tie_roundId =". $data['tie_roundId'].
		" AND tie_posRound in (0,2,3,5)";
				$order = 'tie_posRound';
				$res = $db->select($tables, $fields, $where, $order);

				// Il doit y avoir 4 match
				if ($db->numRows($res) == 4)
				{
					$res = $db->select($tables, $fields, $where, $order);
					$tmp0 = $res->fetch(PDO::FETCH_ASSOC);
					$tmp2 = $res->fetch(PDO::FETCH_ASSOC);
					$tmp3 = $res->fetch(PDO::FETCH_ASSOC);
					$tmp5 = $res->fetch(PDO::FETCH_ASSOC);
					$cols = array();
					$cols['mtch_num'] = $tmp5['mtch_num'];
					$where = "mtch_id=".$tmp2['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols['mtch_num'] = $tmp2['mtch_num'];
					$where = "mtch_id=".$tmp5['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols['mtch_num'] = $tmp3['mtch_num'];
					$where = "mtch_id=".$tmp0['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols['mtch_num'] = $tmp0['mtch_num'];
					$where = "mtch_id=".$tmp3['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols = array();
					$cols['tie_schedule'] = $tmp5['tie_schedule'];
					$cols['tie_place'] = $tmp5['tie_place'];
					$cols['tie_court'] = $tmp5['tie_court'];
					$where = "tie_id=".$tmp2['tie_id'];
					$db->update('ties', $cols, $where);

					$cols['tie_schedule'] = $tmp2['tie_schedule'];
					$cols['tie_place'] = $tmp2['tie_place'];
					$cols['tie_court'] = $tmp2['tie_court'];
					$where = "tie_id=".$tmp5['tie_id'];
					$db->update('ties', $cols, $where);

					$cols['tie_schedule'] = $tmp3['tie_schedule'];
					$cols['tie_place'] = $tmp3['tie_place'];
					$cols['tie_court'] = $tmp3['tie_court'];
					$where = "tie_id=".$tmp0['tie_id'];
					$db->update('ties', $cols, $where);

					$cols['tie_schedule'] = $tmp0['tie_schedule'];
					$cols['tie_place'] = $tmp0['tie_place'];
					$cols['tie_court'] = $tmp0['tie_court'];
					$where = "tie_id=".$tmp3['tie_id'];
					$db->update('ties', $cols, $where);
				}
				else
				echo "__FILE__.' ('.__LINE__.') :".
		  "Ca etre bizarre!!!";
			}
		}

	}
	//}}}

	// {{{ _updateGroupRank
	/**
	 * Calculate the ranking of each pairs in the given group
	 *
	 * @access private
	 * @param  integer  $groupId   Id of the group
	 * @return integer id of the team
	 */
	function _updateGroupRank($roundId)
	{
		$db =& $this->_db;

		// Get the point of the result of a tie
		$fields = array('rund_tieWin', 'rund_tieEqual', 'rund_tieLoose', 'rund_tieWO');
		$tables = array('rounds');
		$where = "rund_id = $roundId";
		$points = $db->selectFirst($tables, $fields, $where);

		// Calculer pour chaque paire les cumuls de
		// match gagnes, set gagnes et points marques
		$fields = array('p2m_pairId', 'p2m_result', 'mtch_score');
		$tables = array('p2m', 'matchs', 'ties');
		$where = "tie_roundId ='$roundId'".
	" AND tie_id = mtch_tieId".
	" AND mtch_id = p2m_matchId";
		$res = $db->select($tables, $fields, $where);
		$pairs = array();

		$uts = new utscore();
		while ($pair = $res->fetch(PDO::FETCH_ASSOC))
		{
			$pairId = $pair['p2m_pairId'];
			$uts->setScore($pair['mtch_score']);
			if (isset($pairs[$pairId])) $row = $pairs[$pairId];
			else
			{
				$row = array('pairId' => $pairId,
			   'matchW' => 0,
			   'matchL' => 0,
			   'gameW'  => 0,
			   'gameL'  => 0,
			   'pointW' => 0,
			   'pointL' => 0,
			   'points' => 0,
			   'point'  => 0,
			   'bonus'  => 0);
			}

			switch($pair['p2m_result'])
			{
				case WBS_RES_WINAB;
				case WBS_RES_WINWO:
				case WBS_RES_WIN:
					$row['matchW']++;
					$row['gameW'] += $uts->getNbWinGames();
					$row['gameL'] += $uts->getNbLoosGames();
					$row['pointW']+= $uts->getNbWinPoints();
					$row['pointL']+= $uts->getNbLoosPoints();;
					$row['points']++;
					$row['point'] += 100;
					break;
				case WBS_RES_LOOSEWO:
					$row['points']--;
					$row['point']-= 100;
					// No break here !!!
				case WBS_RES_LOOSEAB;
				case WBS_RES_LOOSE:
					$row['gameW'] += $uts->getNbLoosGames();
					$row['gameL'] += $uts->getNbWinGames();
					$row['pointW']+= $uts->getNbLoosPoints();
					$row['pointL']+= $uts->getNbWinPoints();
					break;
				default:
					break;
			}
			$pairs[$pairId] = $row;
		}
		$nbPairs = count($pairs);
		foreach($pairs as $pair) $rows[] = $pair;

		// S'il y  a plus de deux equipes a egalites
		// on regarde le nombre de sets gagnes/perdus
		if ($this->_checkEqual($rows, $roundId))
		{
			for ($i=0; $i<$nbPairs; $i++)
			{
				$ti =$rows[$i];
				for ($j=$i+1; $j<$nbPairs; $j++)
				{
					$tj =$rows[$j];
					if ($ti['point'] == $tj['point'])
					{
						if ($ti['gameW'] - $ti['gameL'] >
						$tj['gameW'] - $tj['gameL'])
						{
							$ti['bonus'] += 10;
							$rows[$i] = $ti;
						}
						if ($ti['gameW'] - $ti['gameL'] <
						$tj['gameW'] - $tj['gameL'])
						{
							$tj['bonus'] += 10;
							$rows[$j] = $tj;
						}
					} //end if team equal
				} //next $j
			} // next $i
		}

		// S'il y  a toujours plus de deux paires a egalites
		// on regarde le nombre de points gagnes/perdus
		if ($this->_checkEqual($rows, $roundId))
		{
			for ($i=0; $i<$nbPairs; $i++)
			{
				$ti =$rows[$i];
				for ($j=$i+1; $j<$nbPairs; $j++)
				{
					$tj =$rows[$j];
					if ($ti['point'] == $tj['point'])
					{
						if ($ti['pointW'] - $ti['pointL'] >
						$tj['pointW'] - $tj['pointL'])
						{
							$ti['bonus'] += 1;
							$rows[$i] = $ti;
						}
						if ($ti['pointW'] - $ti['pointL'] <
						$tj['pointW'] - $tj['pointL'])
						{
							$tj['bonus'] += 1;
							$rows[$j] = $tj;
						}
					} //end if team equal
				} //next $j
			} // next $i
		}
		$this->_checkEqual($rows, $roundId);
		// Calcul du classement de chaque equipe
		for ($i=0; $i<$nbPairs; $i++)
		{
			$ti =$rows[$i];
			$ti['rank']=1;
			for ($j=0; $j<$nbPairs; $j++)
			{
				$tj =$rows[$j];
				if ($tj['point'] > $ti['point'])
				{
					$ti['rank'] ++;
				}
				$rows[$j] = $tj;
			}
			$rows[$i] = $ti;
		}

		// Mise a jour de la base
		$fields = array();
		for ($i=0; $i<$nbPairs; $i++)
		{
			$ti =$rows[$i];
			$fields['t2r_rank']   = $ti['rank'];
			$fields['t2r_points'] = $ti['pointW'] - $ti['pointL'];
			$fields['t2r_tieW']   = $ti['gameW']-  $ti['gameL'];
			$fields['t2r_tieL']   = $ti['matchL'];
			$where = "t2r_pairId=".$ti['pairId'] . " AND t2r_roundId =".$roundId;
			$db->update('t2r', $fields, $where);
		}

		return;
	}
	// }}}

	// {{{ _checkEqual
	/**
	 * Check if some team have the same score
	 *
	 * @access private
	 * @param  integer  $groupId   Id of the group
	 * @return integer id of the team
	 */
	function _checkEqual(&$rows, $roundId)
	{
		// Ajouter leur bonus aux paires
		$nbPairs = count($rows);
		for ($j=0; $j<$nbPairs; $j++)
		{
			$tj = $rows[$j];
			$tj['point'] += $tj['bonus'];
			$tj['bonus'] = 0;
			$rows[$j] = $tj;
		}


		$isEqual = false;
		// Pour chaque paire
		for ($i=0; $i<$nbPairs; $i++)
		{
			$ti =$rows[$i];
			$pairEqual = 0;
			$nbEqual   = 0;
			// Calculer les paires a egalite
			for ($j=0; $j<$nbPairs; $j++)
			{
				if ($i == $j) continue;
				$tj = $rows[$j];
				$rows[$j] = $tj;
				if ($ti['point'] == $tj['point'])
				{
					$pairEqual = $tj['pairId'];
					$nbEqual++;
				}
			}
			// S'il y en a une seule, trouver la gagnante
			if ($nbEqual == 1)
			{
				$winner = $this->_getWinPair($roundId, $ti['pairId'],
				$pairEqual);
				if (is_array($winner)) return $winner;
				if ($winner == $ti['pairId']) $ti['point'] += 0.1;
				if ($winner != $pairEqual) $isEqual = true;
				$rows[$i] = $ti;
			}
			if ($nbEqual > 1) $isEqual = true;
		}
		return $isEqual;
	}

	// {{{ _getWinPair
	/**
	 * Return the winner team of a match
	 *
	 * @access private
	 * @param  integer  $groupId  Id of the group
	 * @param  integer  $team1    Id of the first teams
	 * @param  integer  $team1    Id of the second team
	 * @return integer id of the team
	 */
	function _getWinPair($roundId, $pair1, $pair2)
	{
		$db =& $this->_db;

		// Chercher les match des paires dans le groupes
		$fields = array('p2m_pairId', 'p2m_matchId', 'p2m_result');
		$tables = array('p2m', 'matchs', 'ties');
		$where = "tie_roundId=$roundId".
	" AND tie_id = mtch_tieId".
	" AND mtch_id = p2m_matchId".
	" AND p2m_result != ".WBS_RES_NOPLAY.
	" AND (p2m_pairId = $pair1 OR p2m_pairId = $pair2)";
		$order = 'mtch_id';
		$res = $db->select($tables, $fields, $where);

		// Trouver le match commun aux deux paires
		$winner = 0;
		while ($tmp  = $res->fetch(PDO::FETCH_ASSOC))
		{
			if (isset($matchs[$tmp['p2m_matchId']]))
			{
				if ($tmp['p2m_result'] == WBS_RES_WIN ||
				$tmp['p2m_result'] == WBS_RES_WINWO ||
				$tmp['p2m_result'] == WBS_RES_WINAB)
				$winner = $tmp['p2m_pairId'];
				else
				$winner = $matchs[$tmp['p2m_matchId']]['p2m_pairId'];

			}
			$matchs[$tmp['p2m_matchId']] = $tmp;
		}

		return $winner;
	}
	// }}}

	// {{{ _updateNextKoTie
	/**
	 * Update the next ko tie for and individual event
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function _updateNextKoTie($data, $winPairId)
	{
		$db =& $this->_db;

		$tiePosRound = intval(($data['tie_posRound']-1)/2);
		// Position of the pair in the next match
		$pairPos = ($data['tie_posRound']%2) ? WBS_PAIR_TOP:WBS_PAIR_BOTTOM;;

		// search the match of the next tie
		$fields = array('mtch_id', 'mtch_status');
		$tables = array('matchs', 'ties');
		$where = "tie_id=mtch_tieId".
	" AND tie_roundId=".$data['tie_roundId'].
	" AND tie_posRound=$tiePosRound";
		$res = $db->select($tables, $fields, $where);

		if ( $res && ($buf = $res->fetch(PDO::FETCH_ASSOC)) )
		{
			$matchId = $buf['mtch_id'];
			$matchStatus = $buf['mtch_status'];

			// update the relation between pair and match
			$fields = array('p2m_id');
			$tables = array('p2m');
			$where = "p2m_matchId=$matchId AND p2m_posMatch=$pairPos";
			$res = $db->select($tables, $fields, $where);

			$column['p2m_pairId'] = $winPairId;
			$column['p2m_matchId'] = $matchId;
			$column['p2m_posMatch'] = $pairPos;
			if ( $db->numRows($res) )
			$db->update('p2m', $column, $where);
			else
			{
				$column['p2m_result'] = WBS_RES_NOPLAY;
				$res = $db->insert('p2m', $column);
			}

			// update the status of the match
			$fields = array('p2m_id');
			$tables = array('p2m');
			$where = "p2m_matchId=$matchId";
			$res = $db->select($tables, $fields, $where);
			$where = "mtch_id=$matchId";
			$column = array();
			$column['mtch_status'] = $matchStatus;
			if ($db->numRows($res) > 1)
			{
				if ($matchStatus < WBS_MATCH_LIVE)
				$column['mtch_status'] = WBS_MATCH_READY;
			}
			else
			$column['mtch_status'] = WBS_MATCH_INCOMPLETE;
			$db->update('matchs', $column, $where);
		}
	}
	//}}}

	// {{{ _updateOtherKoTie
	/**
	 * Met a jour le tableau pour le perdant s'il existe
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function _updateOtherKoTie($data, $pairId)
	{
		// Verifier que qu'il y a un tableau pour le perdant
		if ($data['tie_looserdrawid'] < 1 )	return;
//print_r($data);
		// Données du tour destinataire
		$db =& $this->_db;
		$roundId = $data['tie_looserdrawid'];
		$tables = array('rounds');
		$fields = array('rund_size');
		$where = 'rund_id=' . $roundId;
		$destRoundSize = $db->selectFirst($tables, $fields, $where);
//echo "<br>destRoundSize=$destRoundSize<br>";
		// Position du match dans le tour destinaire
		$limit = max(0, $destRoundSize -2);
//echo "<br>limit=$limit<br>";
		$tiePosRound = $data['tie_posRound'];
		$step = 0;
		do
		{
			$t2rPosRound = $tiePosRound;
			$tiePosRound = ceil($tiePosRound/2)-1;
			$step++;
		}
		while ($tiePosRound > $limit);
		
//echo "t2rPosRound=$t2rPosRound<br>";
//echo "tiePosRound=$tiePosRound<br>";
//echo "step=$step<br>";

		// Est-ce un bye
		$tables = array('rounds', 'ties');
		$fields = array('tie_isBye');
		$where = "tie_roundId = rund_id AND tie_posRound= " . $tiePosRound;
		$where .= ' AND rund_id=' . $roundId;
		$isBye = $db->selectFirst($tables, $fields, $where);

		$posRound = $t2rPosRound - 62;
		if ($posRound <= 0) $posRound = $t2rPosRound - 30;
		if ($posRound <= 0) $posRound = $t2rPosRound - 14;
		if ($posRound <= 0) $posRound = $t2rPosRound - 6;
		if ($posRound <= 0) $posRound = $t2rPosRound - 2;
		if ($posRound <= 0) $posRound = $t2rPosRound;
		
		if($isBye && $step ==1)
		{
		 if( ($posRound>$destRoundSize/2) && $tiePosRound) $posRound++;
		 else $posRound--;
		}
//echo "posRound=$posRound<br>";

		// Positionner la paire dans le tableau
		$fields = array('t2r_roundId'  => $roundId,
		      't2r_pairId'   => $pairId,
		      't2r_posRound' => $posRound,
		      't2r_status'   => WBS_PAIR_NONE,
		      't2r_tds'      => WBS_TDS_NONE
		);
		$where = "t2r_roundId = $roundId AND t2r_posRound= " . $posRound;
		$t2rId = $db->selectfirst('t2r', 't2r_id', $where);
		if (!empty($t2rId))	$db->update('t2r', $fields, $where);
		else $db->insert('t2r', $fields, $where);

		//$pairPos = ($t2rPosRound%2) ? WBS_PAIR_TOP:WBS_PAIR_BOTTOM;;
		$pairPos = ($posRound%2) ? WBS_PAIR_TOP:WBS_PAIR_BOTTOM;;
		// search the match
		$fields = array('mtch_id', 'mtch_status');
		$tables = array('matchs', 'ties');
		$where = "tie_id=mtch_tieId".
					" AND tie_roundId=" . $roundId.
					" AND tie_posRound=" . $tiePosRound;
		$res = $db->select($tables, $fields, $where);

		if ($res && ($buf = $res->fetch(PDO::FETCH_ASSOC)) )
		{
			$matchId = $buf['mtch_id'];
			$matchStatus = $buf['mtch_status'];

			// update the relation between pair and match
			$fields = array('p2m_id');
			$tables = array('p2m');
			$where = "p2m_matchId=$matchId".
				" AND p2m_posMatch=$pairPos";
			$res = $db->select($tables, $fields, $where);

			$column['p2m_pairId'] = $pairId;
			$column['p2m_matchId'] = $matchId;
			$column['p2m_posMatch'] = $pairPos;

			if ($db->numRows($res))
			{
				$db->update('p2m', $column, $where);
			}
			else
			{
				if ( $isBye ) $column['p2m_result'] = WBS_RES_WIN;
				else $column['p2m_result'] = WBS_RES_NOPLAY;
				$res = $db->insert('p2m', $column);
			}

			// update the status of the match
			$fields = array('p2m_id');
			$tables = array('p2m');
			$where = "p2m_matchId=$matchId";
			$res = $db->select($tables, $fields, $where);
			$where = "mtch_id=$matchId";
			$column = array();
			$column['mtch_status'] = $matchStatus;
			if ($tiePosRound == 0) $column['mtch_status'] = WBS_MATCH_ENDED;
			else if ($db->numRows($res)>1) 
			{
				if ($matchStatus < WBS_MATCH_LIVE)
				$column['mtch_status'] = WBS_MATCH_READY;
			}
			else $column['mtch_status'] = WBS_MATCH_INCOMPLETE;
			$db->update('matchs', $column, $where);
		}

		// En cas de bye, mettre a jour le match suivant
		if ($isBye == 1 && $tiePosRound>0)
		{
			$pairPos = ($tiePosRound%2) ? WBS_PAIR_TOP:WBS_PAIR_BOTTOM;;
			$tiePosRound = intval(($tiePosRound-1)/2);

			$fields = array('mtch_id', 'mtch_status');
			$tables = array('matchs', 'ties');
			$where = "tie_id=mtch_tieId".
					" AND tie_roundId=" . $roundId.
					" AND tie_posRound=" . $tiePosRound;
			$res = $db->select($tables, $fields, $where);

			if ($res && ($buf = $res->fetch(PDO::FETCH_ASSOC)) )
			{
				$matchId = $buf['mtch_id'];
				$matchStatus = $buf['mtch_status'];

				// update the relation between pair and match
				$fields = array('p2m_id');
				$tables = array('p2m');
				$where = "p2m_matchId=$matchId".
				" AND p2m_posMatch=$pairPos";
				$res = $db->select($tables, $fields, $where);

				$column = array();
				$column['p2m_pairId'] = $pairId;
				$column['p2m_matchId'] = $matchId;
				$column['p2m_posMatch'] = $pairPos;
					
				if ($db->numRows($res))
				{
					$db->update('p2m', $column, $where);
				}
				else
				{
					$column['p2m_result'] = WBS_RES_NOPLAY;
					$res = $db->insert('p2m', $column);
				}

				// update the status of the match
				$fields = array('p2m_id');
				$tables = array('p2m');
				$where = "p2m_matchId=$matchId";
				$res = $db->select($tables, $fields, $where);
				$where = "mtch_id=$matchId";
				$column = array();
				$column['mtch_status'] = $matchStatus;
				if ($db->numRows($res)>1)
				{
					if ($matchStatus < WBS_MATCH_LIVE)
					$column['mtch_status'] = WBS_MATCH_READY;
				}
				else $column['mtch_status'] = WBS_MATCH_INCOMPLETE;
				$db->update('matchs', $column, $where);
			}
		}
	}
	//}}}

	// {{{ updateMatchTeamResult
	/**
	 * Add or update a match of a team event into the database
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function updateMatchTeamResult($eventId, $match)
	{

		$db = $this->_db;
		// Identifiant du match a mettre a jour
		$tables = array('matchs');//, 'ties', 'rounds', 'draws');
		$where = "mtch_uniId='{$match['mtch_uniId']}'";//.
		//" AND mtch_tieId = tie_id".
		//" AND tie_roundId = rund_id".
		//" AND rund_drawId = draw_id".
		//" AND draw_eventId = $eventId";
		$match['mtch_id'] = $db->selectFirst($tables, 'mtch_id', $where);
		// Identifiants des paires
		if( !empty($match['mtch_winId0']))
		$match["mtch_winId0"] = $db->selectFirst('registration', 'regi_id',
					 "regi_uniid='" . $match['mtch_winId0'] . "'");
		if( !empty($match['mtch_winId1']))
		$match["mtch_winId1"] = $db->selectFirst('registration', 'regi_id',
					 "regi_uniid='" . $match['mtch_winId1'] . "'");
		if( !empty($match['mtch_loosId0']))
		$match["mtch_loosId0"] = $db->selectFirst('registration', 'regi_id',
					 "regi_uniid='" . $match['mtch_loosId0']. "'");
		if( !empty($match['mtch_loosId1']))
		$match["mtch_loosId1"] = $db->selectFirst('registration', 'regi_id',
					 "regi_uniid='" . $match['mtch_loosId1'] ."'");
			
		if( empty($match['mtch_winId0'])) $match['mtch_winId0']=-10;
		if( empty($match['mtch_loosId0'])) $match['mtch_loosId0']=-10;
		if( empty($match['mtch_winId1'])) $match['mtch_winId1']=-10;
		if( empty($match['mtch_loosId1'])) $match['mtch_loosId1']=-10;

		$winPairId = $this->_getPairId($match['mtch_winId0'], $match['mtch_winId1'], $match['mtch_disci']);
		$loosPairId = $this->_getPairId($match['mtch_loosId0'], $match['mtch_loosId1'], $match['mtch_disci']);

		// Identifiant des equipes
		$where = "team_uniid='". $match['teamuniid1'] . "'".
      		' AND team_eventid=' . $eventId;
		$teamId1 = $db->selectFirst('teams', 'team_id', $where);
		unset($match['teamuniid1']);
		$where = "team_uniid='". $match['teamuniid2'] . "'".
      		' AND team_eventid=' . $eventId;
		$teamId2 = $db->selectFirst('teams', 'team_id', $where);
		unset($match['teamuniid2']);

		// Updating the match
		$utd = new utdate();
		if (!empty($match['mtch_begin']))
		{
			$utd->setFrDateTime($match['mtch_begin']);
			$fields['mtch_begin'] = $utd->getIsoDateTime();
		}
		if (!empty($match['mtch_end']))
		{
			$utd->setFrDateTime($match['mtch_end']);
			$fields['mtch_end'] = $utd->getIsoDateTime();
			$match['mtch_end'] = $fields['mtch_end'];
		}
		if (($match['mtch_status'] != WBS_MATCH_LIVE) ||
		($match['mtch_status'] > WBS_MATCH_LIVE))
		{
			$fields['mtch_status'] = $match['mtch_status'];
		}
		$fields['mtch_score'] = $match['mtch_score'];

		$where = "mtch_id=" . $match['mtch_id'];
		$db->update('matchs', $fields, $where);

		// Updating relation between pairs and match for winner
		$uts = new utscore();
		$sco = $uts->setScore($match['mtch_score']);
		$fields = array('p2m_id');
		$tables = array('p2m');
		$where = "p2m_matchId='".$match['mtch_id']."'";
		$p2ms = $db->select($tables, $fields, $where);

		$p2m = $p2ms->fetch(PDO::FETCH_NUM);
		$fields = array();
		$fields['p2m_pairId'] = $winPairId;
		if ($match["mtch_status"] >= WBS_MATCH_ENDED)
		if ($uts->isWO()) $fields['p2m_result'] = WBS_RES_WINWO;
		else if ($uts->isAbort()) $fields['p2m_result'] = WBS_RES_WINAB;
		else $fields['p2m_result'] = WBS_RES_WIN;
		else $fields['p2m_result'] = WBS_RES_NOPLAY;

		if ($p2m)
		{
			$p2mId = $p2m[0];
			$where = "p2m_id = $p2mId";
			$db->update('p2m', $fields, $where);
		}
		else
		{
			$fields['p2m_matchId'] = $match['mtch_id'];
			$fields['p2m_posMatch'] = WBS_PAIR_TOP;
			$res = $db->insert('p2m', $fields);
			$p2mId = $res;
		}
		// Relation between match and pair for looser
		$fields = array('p2m_id');
		$tables = array('p2m');
		$where = "p2m_matchId='".$match['mtch_id']."'".
			" AND p2m_id != $p2mId";
		$p2ms = $db->select($tables, $fields, $where);

		$p2m = $p2ms->fetch(PDO::FETCH_NUM);
		$fields = array();
		$fields['p2m_pairId'] = $loosPairId;
		if ($match["mtch_status"] >= WBS_MATCH_ENDED)
		if ($uts->isWO()) $fields['p2m_result'] = WBS_RES_LOOSEWO;
		else if ($uts->isAbort()) $fields['p2m_result'] = WBS_RES_LOOSEAB;
		else $fields['p2m_result'] = WBS_RES_LOOSE;
		else $fields['p2m_result'] = WBS_RES_NOPLAY;

		if ($p2m)
		{
			$p2mId2 = $p2m[0];
			$where = "p2m_id = $p2mId2";
			$db->update('p2m', $fields, $where);
		}
		else
		{
			$fields['p2m_matchId'] = $match['mtch_id'];
			$fields['p2m_posMatch'] = WBS_PAIR_BOTTOM;
			$p2mId2 = $db->insert('p2m', $fields);
		}


		$ou = "p2m_matchId='".$match['mtch_id']."'".
	" AND p2m_id != $p2mId".
	" AND p2m_id != $p2mId2";
		$db->delete('p2m', $ou);

		// Now updating result of the tie !
		$fields = array('rund_id', 'tie_id');
		$tables = array('rounds', 'ties', 'matchs');
		$where = "mtch_id='".$match['mtch_id']."'".
	" AND rund_id = tie_roundId".
	" AND tie_id = mtch_tieId";
		$round = $db->select($tables, $fields, $where);

		$group = $round->fetch(PDO::FETCH_NUM);

		//echo "{$infos['mtch_teamId0']}, {$group[1]}<br>";
		if ($match['updateDraw'])
		{
			$res = $this->_updateTeamResult($teamId1, $group[1]);
			if (is_array($res)) return $res;
			$res = $this->_updateTeamResult($teamId2, $group[1]);
			if (is_array($res)) return $res;
			$res = $this->_updateGroupTeamRank($group[0]);
			if (is_array($res)) return $res;
			$res = $this->_updateKo($group[1]);
		}
		if ($match["mtch_status"] >= WBS_MATCH_ENDED && !$uts->isWO())
		{
			// Updating rest time of players
			$err = $this->updateRestTime($match['mtch_id'], $match['mtch_end']);
		}

		// Updating status of other matches
		return $this->updateStatusMatch($eventId);
	}
	// }}}

	/**
	 * Update the next tie for a KO draw
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function _updateKo($tieId)
	{
		$db = $this->_db;

		// search the winner of the tie
		$fields =  array('t2t_teamId', 'tie_posRound', 'rund_id');
		$tables = array('t2t', 'ties', 'rounds');
		$where = "t2t_tieId=$tieId".
	" AND t2t_tieId=tie_id".
	" AND tie_roundId=rund_id".
	" AND (t2t_result=".WBS_TIE_WIN.
	" OR t2t_result=".WBS_TIE_WINWO.")".
	" AND rund_type=".WBS_TEAM_KO;
		$res = $db->select($tables, $fields, $where);
		if (!$db->numRows($res)) return;
		$data = $res->fetch(PDO::FETCH_ASSOC);

		// Updating relation between pairs and match for winner
		// Pas de mise a jour si c'est la finale
		if (!$data['tie_posRound']) return;

		// Next tie
		$tiePosRound = intval(($data['tie_posRound']-1)/2);
		// Position of the team in the next tie
		$teamPosTie = ($data['tie_posRound']%2) ? WBS_TEAM_TOP:WBS_TEAM_BOTTOM;;
		$teamId = $data['t2t_teamId'];
		$roundId = $data['rund_id'];

		$fields =  array('t2t_id', 'tie_id');
		$tables = array('t2t', 'ties');
		$where = "t2t_tieId=tie_id".
	" AND tie_roundId = $roundId".
	" AND tie_posRound = $tiePosRound".
	" AND t2t_posTie=$teamPosTie";
		$res = $db->select($tables, $fields, $where);
		if ($db->numRows($res))
		{
			$data = $res->fetch(PDO::FETCH_ASSOC);
			$fields = array();
			$fields['t2t_teamId'] = $teamId;
			$where = "t2t_id = ".$data['t2t_id'];
			$res = $db->update('t2t', $fields, $where);
		}
		else
		{
			// Search the tie Id
			$fields =  array('tie_id');
			$tables = array('ties');
			$where = "tie_roundId = $roundId".
	    " AND tie_posRound = $tiePosRound";
			$res = $db->select($tables, $fields, $where);
			$data = $res->fetch(PDO::FETCH_ASSOC);
			$fields = array();
			$fields['t2t_teamId'] = $teamId;
			$fields['t2t_tieId'] = $data['tie_id'];
			$fields['t2t_posTie'] =$teamPosTie;
			$fields['t2t_result'] = WBS_TIE_NOTPLAY;

			$res = $db->insert('t2t', $fields);
		}
		return;
	}


	// {{{ updateStatusMatch
	/**
	 * Update the status of all the match of the competition
	 *
	 * @access private
	 * @return mixed
	 */
	function updateStatusMatch($eventId, $place = null)
	{
		$db =& $this->_db;
		$place = addslashes($place);

		// Updating the matches : first all busy and rest matchs
		// are setting to ready
		$fields = array('mtch_id');
		$tables = array('matchs', 'ties', 'rounds', 'draws');
		$where = " mtch_tieId=tie_id".
	" AND (mtch_status =".WBS_MATCH_BUSY.
	" OR mtch_status =".WBS_MATCH_REST.
	" ) AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId";
		$res = $db->select($tables, $fields, $where);
		if ($res)
		{
			$matchs = $res->fetchAll(PDO::FETCH_COLUMN);
			if (count($matchs))
			{
				$fields = array();
				$fields['mtch_status'] = WBS_MATCH_READY;
				$where = " mtch_id in (".implode(',', $matchs).")";
				$db->update('matchs', $fields, $where);
			}
		}
		// Updating the players : all court number are set to zero
		$fields = array();
		$fields['regi_court'] = 0;
		$where = " regi_eventId = $eventId";
		$db->update('registration', $fields, $where);

		// Now, look at the new status of match
		// Select all in live match
		$fields = array('mtch_id', 'mtch_court', 'mtch_umpireId', 'mtch_serviceId');
		$tables = array('matchs', 'ties', 'rounds', 'draws');
		$where = " mtch_tieId=tie_id".
	" AND mtch_status =".WBS_MATCH_LIVE.
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId";
		$res = $db->select($tables, $fields, $where);
		if ($res)
		{
			$regis = array();
			while ( $match = $res->fetch(PDO::FETCH_ASSOC))
			{
				// Select the players of the match
				$fields = array('i2p_regiId');
				$tables = array('matchs', 'p2m', 'i2p');
				$where = "i2p_pairId = p2m_pairId".
	    " AND p2m_matchId = mtch_id".
	    " AND mtch_id =". $match['mtch_id'];
				$res1 = $db->select($tables, $fields, $where);
				$regism = array();
				while ( $regi = $res1->fetch(PDO::FETCH_NUM))
				{
					$regism[] = $regi[0];
					$regis[] = $regi[0];
				}

				// Update the players' court number
				if (count($regism))
				{
					$fields = array();
					$fields['regi_court'] = $match['mtch_court'];
					$where = "regi_id in (". implode(',', $regism) .")";
					$db->update('registration', $fields, $where);
				}
				// Update the umpire court number
				$fields = array();
				$fields['regi_court'] = -$match['mtch_court'];
				$where = "regi_id =".$match['mtch_umpireId'].
	      " OR regi_id =".$match['mtch_serviceId'];
				$db->update('registration', $fields, $where);
			}
		}
		if (!count($regis)) return;

		// Select all completed and not ended matchs of the
		// oncourt players
		$fields = array('mtch_id');
		$tables = array('matchs', 'p2m', 'i2p', 'ties');
		$where = "i2p_pairId = p2m_pairId".
	" AND p2m_matchId = mtch_id".
	" AND mtch_tieId = tie_id".
	" AND mtch_status > ".WBS_MATCH_INCOMPLETE.
	" AND mtch_status < ".WBS_MATCH_LIVE.
	" AND i2p_regiId in (". implode(',', $regis) .")";
		if(!is_null($place))
		$where .= " AND tie_place = '$place'";
		$res = $db->select($tables, $fields, $where);

		if (!$res) return;
		$matchs = $res->fetchAll(PDO::FETCH_COLUMN);

		// All this match are busy
		if (count($matchs))
		{
			$fields = array();
			$fields['mtch_status'] = WBS_MATCH_BUSY;
			$where = " mtch_id IN (". implode(',', $matchs) .")";
			$db->update('matchs', $fields, $where);
		}
		return true;
	}
	// }}}

	// {{{ updateRestTime
	/**
	 * Update the rest time of the players of the macth
	 *
	 * @access private
	 * @param integer $matchId Id of the match
	 * @param sring   $time    Hour of the ned of the match
	 * @param sring   $court   Number of the court
	 * @return mixed
	 */
	function updateRestTime($matchId, $time)
	{
		$db =& $this->_db;
		// Select all players of the match
		$fields[] = 'i2p_regiId';
		$tables = array('matchs', 'p2m', 'i2p');
		$where = "i2p_pairId = p2m_pairId".
	" AND p2m_matchId = mtch_id".
	" AND mtch_id = '$matchId'";
		$res = $db->select($tables, $fields, $where);
		if (!$res) return;
		$regis = $res->fetchAll(PDO::FETCH_COLUMN);
		$fields =  array('regi_rest' => $time);
		$where = "regi_id in (". implode(',', $regis) .")".
	" AND  (regi_rest < '".$fields['regi_rest']."' OR regi_rest IS NULL OR regi_rest='0000-00-00 00:00:00')";
		$db->update('registration', $fields, $where);
		return true;
	}
	// }}}


	// {{{ updateMatchStatus
	/**
	 * Update the status of a match
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function updateMatchStatus($eventId, $infos)
	{
		$db =& $this->_db;
		// Identifiant du match a mettre a jour
		$fields = array('mtch_id', 'tie_place');
		$tables = array('matchs', 'ties', 'rounds', 'draws');
		$where = "mtch_uniId='{$infos['mtch_uniId']}'".
	" AND mtch_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId";
		$req = $db->selectFirst($tables, $fields, $where);
		if (is_null($req))
		{
			$res['errMsg'] = "Match introuvable:{$infos['mtch_uniId']}";
			return $res;
		}
		$infos['mtch_id'] = $req['mtch_id'];
		$place = $req['tie_place'];

		// Identifiant des arbitres du match
		if(isset($infos['mtch_umpireId']))
		{
			$where = "regi_eventId = $eventId".
	    " AND regi_uniId='{$infos['mtch_umpireId']}'";
			$infos['mtch_umpireId'] = $db->selectFirst('registration', 'regi_id', $where);
		}
		if(isset($infos['mtch_serviceId']))
		{
			$where = "regi_eventId = $eventId".
	    " AND regi_uniId='{$infos['mtch_serviceId']}'";
			$infos['mtch_serviceId'] = $db->selectFirst('registration', 'regi_id', $where);
		}

		// Search  the match
		$fields = array('mtch_status', 'mtch_court', 'mtch_umpireId',
		      'mtch_serviceId');
		$where = "mtch_id=".$infos['mtch_id'];
		$match = $db->selectFirst('matchs', $fields, $where);
		if (is_null($match))
		{
			$res['errMsg'] = "Match introuvable:{$where}";
			return $res;
		}
		if ($match['mtch_status'] == $infos['mtch_status'])
		unset($infos['mtch_begin']);

		// Updating the match
		$where = "mtch_id=".$infos['mtch_id'];
		$db->update('matchs', $infos, $where);

		//Updating the status of umpires'
		if( !isset($infos['mtch_umpireId']) ) $infos['mtch_umpireId'] = -1;
		if( !isset($infos['mtch_serviceId'])) $infos['mtch_serviceId'] = -1;
		$this->_updateStatusUmpire($match, $infos);

		//Updating the status of all players and matches
		$this->updateStatusMatch($eventId, $place);

		if ($db->isError())
		{
			$res['errMsg']=$db->_callTree;
			return $res;
		}
		return true;
	}
	// }}}

	// {{{ _updateStatusUmpire
	/**
	 * Update the status of the umpire
	 *
	 * @access private
	 * @return mixed
	 */
	function _updateStatusUmpire($oldData, $newData)
	{
		// Le match se termine: rotation des arbitres
		if ( $oldData['mtch_status'] == WBS_MATCH_LIVE &&
		$newData['mtch_status'] > WBS_MATCH_LIVE)
		{
			$this->_pushUmpire($oldData['mtch_umpireId'], WBS_UMPIRE_UMPIRE);
			$this->_pushUmpire($oldData['mtch_serviceId'], WBS_UMPIRE_SERVICE);
			// mise a jjour du terrain des arbitres
			$col = array('umpi_currentcourt' => 0);
			$where = "umpi_regiId = '" . $oldData['mtch_umpireId'] ."'"
			. " OR umpi_regiId = '" . $oldData['mtch_serviceId'] . "'";
			$db =& $this->_db;
			$db->update('umpire', $col, $where);
		}
		// Le match se lance : mise a jour du terrain des arbitres
		if ( $oldData['mtch_status'] < WBS_MATCH_LIVE &&
		$newData['mtch_status'] == WBS_MATCH_LIVE)
		{
			// mise a jjour du terrain des arbitres
			$col = array('umpi_currentcourt' => $newData['mtch_court']);
			$where = "umpi_regiId = '" . $newData['mtch_umpireId'] ."'"
			. " OR umpi_regiId = '" . $newData['mtch_serviceId'] . "'";
			$db =& $this->_db;
			$db->update('umpire', $col, $where);
		}
		// Le match s'annule : mise a 0 du terrain des arbitres
		if ( $oldData['mtch_status'] == WBS_MATCH_LIVE &&
		$newData['mtch_status'] < WBS_MATCH_LIVE)
		{
			$col = array('umpi_currentcourt' => 0);
			$where = "umpi_regiId = '" . $oldData['mtch_umpireId'] ."'"
			. " OR umpi_regiId = '" . $oldData['mtch_serviceId'] . "'";
			$db =& $this->_db;
			$db->update('umpire', $col, $where);
		}
		//temporaire a supprimer
		// Le match est resaisie : mise a 0 du terrain des arbitres
		/*
		if ( $oldData['mtch_status'] > WBS_MATCH_LIVE &&
		$newData['mtch_status'] > WBS_MATCH_LIVE)
		{
		//$this->_pushUmpire($oldData['mtch_umpireId'], WBS_UMPIRE_UMPIRE);
		//$this->_pushUmpire($oldData['mtch_serviceId'], WBS_UMPIRE_SERVICE);
		$col = array('umpi_currentcourt' => 0);
		$where = "umpi_regiId = '" . $oldData['mtch_umpireId'] ."'"
		. " OR umpi_regiId = '" . $oldData['mtch_serviceId'] . "'";
		$db =& $this->_db;
		$db->update('umpire', $col, $where);
		}
		*/
	}
	// }}}

	// {{{ _pushUmpire
	/**
	 * Update the status of the umpire
	 *
	 * @access private
	 * @return mixed
	 */
	function _pushUmpire($regiId, $function)
	{
		$db =& $this->_db;

		// Select the umpire
		$fields = array('umpi_id', 'umpi_court', 'umpi_function', 'regi_eventid');
		$tables = array('umpire', 'registration');
		$where = "umpi_regiId = $regiId";
		$where .= " AND umpi_regiid = regi_id";
		$res = $db->select($tables, $fields, $where);
		if (!$res) return;

		$umpire = $res->fetch(PDO::FETCH_ASSOC);
		$eventId = $umpire['regi_eventid'];
		unset($umpire['regi_eventid']);

		// Get all umpires of the groups
		if ($umpire['umpi_court'] != '')
		{
			$fields = array('umpi_id', 'umpi_function', 'umpi_order');
			$tables = array('umpire', 'registration');
			$where = "umpi_court =".$umpire['umpi_court'];
			$where .= " AND umpi_regiId != $regiId";
			$where .= " AND regi_id = umpi_regiid";
			$where .= " AND regi_eventid = ". $eventId;
			$order = "umpi_function, umpi_order";
			$res = $db->select($tables, $fields, $where, $order);
			$umpires = array();
			$services = array();
			while ($data = $res->fetch(PDO::FETCH_ASSOC))
			{
				if ($data['umpi_function'] == WBS_UMPIRE_UMPIRE) $umpires[] = $data;
				else if ($data['umpi_function'] == WBS_UMPIRE_SERVICE) $services[] = $data;
			}
			// Update the umpire status
			if ($function == WBS_UMPIRE_UMPIRE)
			{
				$umpire['umpi_function'] = WBS_UMPIRE_SERVICE;
				$umpire['umpi_order'] = count($services)+1;
			}
			else
			{
				$umpire['umpi_function'] = WBS_UMPIRE_UMPIRE;
				$umpire['umpi_order'] = count($umpires)+1;
			}
			$umpire['umpi_currentcourt'] = 0;
			$where = "umpi_regiId = $regiId";
			$db->update('umpire', $umpire, $where);

			// Update the order of umpires and services judge
			$this->_orderedOfficials($umpires, $services);
		}
		return true;
	}
	//}}}

	// {{{ _popUmpire
	/**
	 * Update the status of the umpire
	 *
	 * @access private
	 * @return mixed
	 */
	function _popUmpire($regiId)
	{
		$db =& $this->_db;

		// Select the umpire
		$fields = array('umpi_id', 'umpi_court', 'umpi_function', 'regi_eventid');
		$tables = array('umpire', 'registration');
		$where = "umpi_regiId = $regiId";
		$where .= " AND umpi_regiid = regi_id";
		$res = $db->select($tables, $fields, $where);
		if (!$res) return;
		$umpire = $res->fetch(PDO::FETCH_ASSOC);
		$eventId = $umpire['regi_eventid'];
		unset($umpire['regi_eventid']);

		// Update the umpire status
		if ($umpire['umpi_court'] != '')
		{
			if ($umpire['umpi_function'] == WBS_UMPIRE_UMPIRE) $umpire['umpi_function'] = WBS_UMPIRE_SERVICE;
			else $umpire['umpi_function'] = WBS_UMPIRE_UMPIRE;
			$umpire['umpi_order'] = 0;
			$umpire['umpi_currentcourt'] = 0;
			$where = "umpi_regiId = $regiId";
			$db->update('umpire', $umpire, $where);

			// Get all umpires of the groups
			$fields = array('umpi_id', 'umpi_function', 'umpi_order');
			$tables = array('umpire', 'registration');
			$where = "umpi_court =".$umpire['umpi_court'];
			$where .= " AND regi_id = umpi_regiid";
			$where .= " AND regi_eventid = ". $eventId;
			$order = "umpi_function, umpi_order";
			$res = $db->select($tables, $fields, $where, $order);
			$umpires = array();
			$services = array();
			while ($data = $res->fetch(PDO::FETCH_ASSOC))
			{
				if ($data['umpi_function'] == WBS_UMPIRE_UMPIRE)
				$umpires[] = $data;
				else if ($data['umpi_function'] == WBS_UMPIRE_SERVICE)
				$services[] = $data;
			}
			// Update the order of umpires and services judge
			$this->_orderedOfficials($umpires, $services);
		}
		return true;
	}
	//}}}

	// {{{ _orderedOfficials
	/**
	 * Update the order of the officials
	 *
	 * @access private
	 * @return mixed
	 */
	function _orderedOfficials($umpires, $services)
	{
		$db =& $this->_db;

		// Update the umpire order
		$order = 1;
		foreach($umpires as $umpire)
		{
			$column['umpi_order'] = $order++;
			$where = "umpi_id=".$umpire['umpi_id'];
			$db->update('umpire', $column, $where);
		}

		// Update the service judge order
		$order = 1;
		foreach($services as $service)
		{
			$column['umpi_order'] = $order++;
			$where = "umpi_id =". $service['umpi_id'];
			$db->update('umpire', $column, $where);
		}
		return true;
	}
	// }}}

	function _getPairId($regiId1, $regiId2, $aDiscipline)
	{
		$db = $this->_db;
		// Classement actuel des joueurs
		$fields  = array('rank_rankdefid', 'rank_rank');
		$tables  = array('ranks');
		$where = "rank_regiId = $regiId1 AND rank_discipline=$aDiscipline";
		$rank1 = $db->selectFirst($tables, $fields, $where);

		// Search the pair of the first player
		$fields  = array('pair_id');
		$tables  = array('pairs', 'i2p p1');
		$where = "p1.i2p_regiId = $regiId1".
			" AND p1.i2p_pairId = pair_id".
			" AND pair_disci = $aDiscipline";
		if (!empty($rank1))
		{
			$where .= " AND p1.i2p_rankdefid=" .  $rank1['rank_rankdefid'].
		    " AND p1.i2p_classe=" . $rank1['rank_rank'];
		}

		if ($aDiscipline != WBS_SINGLE)
		{
			// Classement actuel du joueur
			$cols  = array('rank_rankdefid', 'rank_rank');
			$oudonc = "rank_regiId = $regiId2 AND rank_discipline=$aDiscipline";
			$rank2 = $db->selectFirst('ranks', $cols, $oudonc);
			$tables[]  = 'i2p p2';
			$where .= " AND p2.i2p_regiId = $regiId2".
			" AND p2.i2p_id != p1.i2p_id".
			" AND p2.i2p_pairId = pair_id";
			if (!empty($rank2))
			{
			" AND p2.i2p_rankdefid=" .  $rank2['rank_rankdefid'].
		    " AND p2.i2p_classe=" . $rank2['rank_rank'];
			}
		}
		$pairId = $db->selectfirst($tables, $fields, $where);

		// No pair found, create a new one
		if (empty($pairId) )
		{
			// Create the pair
			$fields = array();
			$fields['pair_status'] = WBS_PAIR_MAINDRAW;
			$fields['pair_disci'] = $aDiscipline;
			$res = $db->insert('pairs', $fields);
			$pairId = $res;
			// Create the relation between pair and player one
			// Recuperer les info classement du joueur
			$fields = array('rank_rankdefid, rank_rank, rank_average');
			$where = 'rank_regiid =' . $regiId1;
			$where .= ' AND rank_discipline =' . $aDiscipline;
			$rank = $db->selectFirst('ranks', $fields, $where);

			$fields = array();
			$fields['i2p_pairId'] = $pairId;
			$fields['i2p_regiId'] = $regiId1;
			$fields['i2p_rankdefid'] = $rank['rank_rankdefid'];
			$fields['i2p_cppp'] = $rank['rank_average'];
			$fields['i2p_classe'] = $rank['rank_rank'];
			$res = $db->insert('i2p', $fields);

			// Create the relation between pair and player two
			if ($aDiscipline != WBS_SINGLE)
			{
				// Recuperer les info classement du joueur
				$fields = array('rank_rankdefid, rank_rank, rank_average');
				$where = 'rank_regiid =' . $regiId2;
				$where .= ' AND rank_discipline =' . $aDiscipline;
				$rank = $db->selectFirst('ranks', $fields, $where);

				$fields = array();
				$fields['i2p_pairId'] = $pairId;
				$fields['i2p_regiId'] = $regiId2;
				$fields['i2p_rankdefid'] = $rank['rank_rankdefid'];
				$fields['i2p_cppp'] = $rank['rank_average'];
				$fields['i2p_classe'] = $rank['rank_rank'];
				$res = $db->insert('i2p', $fields);
			}
		}
		return $pairId;
	}

	/**
	 * Update the result of the teams for the designed tie
	 *
	 * @access private
	 * @param  integer  $pairId   Id of the pair
	 * @param  integer  $matchId  Id of the match
	 * @return integer id of the team
	 */
	function _updateTeamResult($teamId, $tieId, $penaltiesTeam=NULL,
	$penaltiesOpponent=NULL, $woTeam=NULL,
	$woOpponent=NULL)
	{
		$db =& $this->_db;

		if ($penaltiesTeam==NULL &&
		$penaltiesOpponent==NULL)
		{
	  $fields = array('t2t_penalties', 't2t_penaltiesO');
	  $where = "t2t_teamId=$teamId".
	    " AND t2t_tieId=$tieId";
	  $res  = $db->select('t2t', $fields, $where);
	  $data = $res->fetch(PDO::FETCH_ASSOC);
	  $penaltiesTeam = $data['t2t_penalties'];
	  $penaltiesOpponent = $data['t2t_penaltiesO'];
		}

		// Get the point of the result of a match
		$fields = array('rund_matchWin', 'rund_matchLoose', 'rund_matchWO',
		      'rund_matchRtd', 'rund_tieranktype', 'rund_tiematchdecisif', 'rund_tiematchnum');
		$tables = array('rounds', 'ties');
		$where  = "tie_id = $tieId".
	" AND tie_roundId = rund_id";
		$res = $db->select($tables, $fields, $where);

		$points = $res->fetch(PDO::FETCH_ASSOC);
		//print_r($points);echo "<br>";

		// Search all match of the team in the concerned tie
		$fields = array('DISTINCT mtch_id', 'p2m_result', 'mtch_score', 'mtch_discipline', 'mtch_rank', 'mtch_order');
		$tables = array('matchs', 'ties', 'p2m', 'i2p', 'registration');
		$where = "regi_teamId=$teamId".
	" AND tie_id= $tieId".
	" AND mtch_tieId = tie_id".
	" AND mtch_id = p2m_matchId".
	" AND p2m_pairId = i2p_pairId".
	" AND i2p_regiId = regi_id".
	" AND mtch_status >=".WBS_MATCH_ENDED;
		$res = $db->select($tables, $fields, $where);

		// Calculate the points of the team
		$matchW=0;
		$matchL=0;
		$gameW=0;
		$gameL=0;
		$pointW=0;
		$pointL=0;
		$nbPointW=0;
		$nbPointL=0;
		$uts = new utscore();
		while ($matchRes = $res->fetch(PDO::FETCH_ASSOC))
		{
			if ($matchRes['mtch_discipline'] == $points['rund_tiematchdecisif'] &&
			$matchRes['mtch_order'] == $points['rund_tiematchnum'])
			{
				$resultmatchdecisif = $matchRes["p2m_result"];
			}
	  $uts->setScore($matchRes['mtch_score']);
	  //echo "resultat=".$matchRes["p2m_result"];
	  switch ($matchRes["p2m_result"])
	  {
	  	case WBS_RES_WINWO:
	  	case WBS_RES_WIN:
	  	case WBS_RES_WINAB:
	  		$matchW++;
	  		$gameW += (int)$uts->getNbWinGames();
	  		$gameL += (int)$uts->getNbLoosGames();
	  		$pointW += (int)$uts->getNbWinPoints();
	  		$pointL += (int)$uts->getNbLoosPoints();
	  		$nbPointW += $points['rund_matchWin'];
	  		if ($matchRes["p2m_result"] == WBS_RES_WIN)
	  		$nbPointL += $points['rund_matchLoose'];
	  		else if ($matchRes["p2m_result"] == WBS_RES_WINAB)
	  		$nbPointL += $points['rund_matchRtd'];
	  		else
	  		$nbPointL += $points['rund_matchWO'];
	  		break;

	  	case WBS_RES_LOOSEWO:
	  	case WBS_RES_LOOSE:
	  	case WBS_RES_LOOSEAB:
	  		$matchL++;
	  		$gameL += (int)$uts->getNbWinGames();
	  		$gameW += (int)$uts->getNbLoosGames();
	  		$pointL += (int)$uts->getNbWinPoints();
	  		$pointW += (int)$uts->getNbLoosPoints();
	  		$nbPointL += $points['rund_matchWin'];
	  		if ($matchRes["p2m_result"] == WBS_RES_LOOSE)
	  		$nbPointW += $points['rund_matchLoose'];
	  		else if ($matchRes["p2m_result"] == WBS_RES_LOOSEAB)
	  		$nbPointW += $points['rund_matchRtd'];
	  		else
	  		$nbPointW += $points['rund_matchWO'];
	  		break;
	  }
	  //echo "---score=".$nbPointW.'/'.$nbPointL."<br>";
		}
		// Calculate the results of the tie
		$result= WBS_TIE_WIN;
		if ($penaltiesTeam != NULL)
		$nbPointW += $penaltiesTeam;
		if ($penaltiesOpponent != NULL)
		$nbPointL += $penaltiesOpponent;

		if (!is_null($woTeam) && is_null($woOpponent)) $result = WBS_TIE_LOOSEWO;
		else if (is_null($woTeam) && !is_null($woOpponent)) $result = WBS_TIE_WINWO;
		else if (!is_null($woTeam) && !is_null($woOpponent)) $result = WBS_TIE_EQUAL;
		else if (!$nbPointW && !$nbPointL) $result = WBS_TIE_NOTPLAY;
		else if ($nbPointW < $nbPointL) $result=WBS_TIE_LOOSE;
		else if ($nbPointW == $nbPointL)
		{
			// En cas d'egalite, voir l'option choisie
			// Egalite autorisee
			if ($points['rund_tieranktype'] == WBS_CALC_EQUAL) $result = WBS_TIE_EQUAL;
			// Resultat fonction de la difference de match, jeu, points gagne
			else if ($points['rund_tieranktype'] == WBS_CALC_RANK)
			{
				$result=WBS_TIE_EQUAL_PLUS;
				if ($matchW < $matchL) $result=WBS_TIE_EQUAL_MINUS;
				else if ($matchW == $matchL)
				{
					if ($gameW < $gameL) $result=WBS_TIE_EQUAL_MINUS;
					else if ($gameW == $gameL)
					{
						if ($pointW < $pointL) $result=WBS_TIE_EQUAL_MINUS;
						else if ($pointW == $pointL) $result=WBS_TIE_EQUAL;
					}
				}
			}
			// Resultat fonction d'un match particulier
			else
			{
				switch( $resultmatchdecisif )
				{
					case WBS_RES_WINWO:
					case WBS_RES_WIN:
					case WBS_RES_WINAB:
						$result=WBS_TIE_EQUAL_PLUS;
						break;
					default:
						$result=WBS_TIE_EQUAL_MINUS;
						break;
				}
			}
		}

		// Updating database
		$fields = array();
		$fields['t2t_matchW']  = $matchW;
		$fields['t2t_matchL']  = $matchL;
		$fields['t2t_setW']    = $gameW;
		$fields['t2t_setL']    = $gameL;
		$fields['t2t_pointW']  = $pointW;
		$fields['t2t_pointL']  = $pointL;
		$fields['t2t_scoreW']  = $nbPointW;
		$fields['t2t_scoreL']  = $nbPointL;
		$fields['t2t_result']  = $result;
		if (!is_null($penaltiesTeam)) $fields['t2t_penalties']  = $penaltiesTeam;
		if (!is_null($penaltiesOpponent)) $fields['t2t_penaltiesO']  = $penaltiesOpponent;
		$where = "t2t_teamId=$teamId".
	" AND t2t_tieId=$tieId";
		$res = $db->update('t2t', $fields, $where);
	}


	/**
	 * Calculate the ranking of each team in the given group
	 *
	 * @access private
	 * @param  integer  $groupId   Id of the group
	 * @return integer id of the team
	 */
	function _updateGroupTeamRank($groupId)
	{
		$db =& $this->_db;

		// Get the point of the result of a tie
		$fields = array('rund_tieWin', 'rund_tieEqualPlus', 'rund_tieEqual', 'rund_tieEqualMinus', 'rund_tieLoose',
		      'rund_tieWO', 'rund_rankType');
		$tables = array('rounds');
		$where = "rund_id = $groupId";
		$res = $db->select($tables, $fields, $where);

		$points = $res->fetch(PDO::FETCH_ASSOC);
		$rankType = $points['rund_rankType'];

		// Calculer pour chaque equipe les cumuls de
		// match gagnes, set gagnes et points marques
		$fields = array('t2t_teamId as teamId',
		      'sum(t2t_matchW) - sum(t2t_matchL) as deltaMatch',
		      'sum(t2t_setW) -  sum(t2t_setL) as deltaGame',
		      'sum(t2t_pointW) - sum(t2t_pointL) as deltaPoint',
		      't2r_penalties');
		$tables = array('t2t', 'ties', 't2r');
		$where = "tie_roundId ='$groupId'".
	" AND t2t_tieId = tie_id".
	" AND t2r_teamId = t2t_teamId".
	" AND t2r_roundId = '$groupId'".
	" GROUP BY t2t_teamId";
		$order = '1';
		$res = $db->select($tables, $fields, $where, $order);
		$rows = array();

		// Calculer pour chaque equipe le nombre de rencontre gagnees
		// et le nombre de rencontre perdues
		$fields = array('count(*)');
		$tables = array('t2t', 'ties');
		$ou = "tie_roundId ='$groupId'".
	" AND t2t_tieId = tie_id";
		while ($team = $res->fetch(PDO::FETCH_ASSOC))
		{
	  $where = "$ou AND t2t_teamId=".$team['teamId'].
	    " AND (t2t_result =".WBS_TIE_WIN.
	    " OR t2t_result =".WBS_TIE_WINWO.')';
	  $r = $db->select($tables, $fields, $where);
	  $tmp = $r->fetch(PDO::FETCH_NUM);
	  $team['tieW']=$tmp[0];

	  $where = "$ou AND t2t_teamId=".$team['teamId'].
	    " AND t2t_result =".WBS_TIE_LOOSE;
	  $r = $db->select($tables, $fields, $where);
	  $tmp = $r->fetch(PDO::FETCH_NUM);
	  $team['tieL']=$tmp[0];

	  $where = "$ou AND t2t_teamId=".$team['teamId'].
	    " AND t2t_result =".WBS_TIE_EQUAL;
	  $r = $db->select($tables, $fields, $where);
	  $tmp = $r->fetch(PDO::FETCH_NUM);
	  $team['tieE']=$tmp[0];

	  $where = "$ou AND t2t_teamId=".$team['teamId'].
	    " AND t2t_result =".WBS_TIE_EQUAL_MINUS;
	  $r = $db->select($tables, $fields, $where);
	  $tmp = $r->fetch(PDO::FETCH_NUM);
	  $team['tieEM']=$tmp[0];

	  $where = "$ou AND t2t_teamId=".$team['teamId'].
	    " AND t2t_result =".WBS_TIE_EQUAL_PLUS;
	  $r = $db->select($tables, $fields, $where);
	  $tmp = $r->fetch(PDO::FETCH_NUM);
	  $team['tieEP']=$tmp[0];

	  $where = "$ou AND t2t_teamId=".$team['teamId'].
	    " AND t2t_result =".WBS_TIE_LOOSEWO;
	  $r = $db->select($tables, $fields, $where);
	  $tmp = $r->fetch(PDO::FETCH_NUM);
	  $team['tieWO']=$tmp[0];

	  $team['points'] = $team['tieW']*$points['rund_tieWin'];
	  $team['points'] += $team['tieEP']*$points['rund_tieEqualPlus'];
	  $team['points'] += $team['tieE']*$points['rund_tieEqual'];
	  $team['points'] += $team['tieEM']*$points['rund_tieEqualMinus'];
	  $team['points'] += $team['tieL']*$points['rund_tieLoose'];
	  $team['points'] += $team['tieWO']*$points['rund_tieWO'];
	  $team['points'] += $team['t2r_penalties'];
	  $team['point'] = $team['points']*100;
	  $team['bonus'] = 0;

	  $team['tieL'] += $team['tieWO'];
	  $rows[] = $team;
		}
		$nbTeams = count($rows);

		//$this->debug($rows);
		// S'il y  a plus de deux equipes a egalites
		// on regarde le nombre de matchs gagnes/perdus
		if ($this->_checkTeamEqual($rows, $groupId, $rankType))
		{
	  for ($i=0; $i<$nbTeams; $i++)
	  {
	  	$ti =$rows[$i];
	  	for ($j=$i+1; $j<$nbTeams; $j++)
	  	{
	  		$tj =$rows[$j];
	  		if ($ti['point'] == $tj['point'])
	  		{
	  			if ($ti['deltaMatch'] > $tj['deltaMatch'])
	  			{
	  				$ti['bonus'] += 10;
	  				$rows[$i] = $ti;
	  			}
	  			if ($ti['deltaMatch'] < $tj['deltaMatch'])
	  			{
	  				$tj['bonus'] += 10;
	  				$rows[$j] = $tj;
	  			}
	  		} //end if team equal
	  	} //next $j
	  } // next $i
		}

		//$this->debug($rows);
		// S'il y  a toujours plus de deux equipes a egalites
		// on regarde le nombre de sets gagnes/perdus
		if ($this->_checkTeamEqual($rows, $groupId, $rankType))
		{
	  for ($i=0; $i<$nbTeams; $i++)
	  {
	  	$ti =$rows[$i];
	  	for ($j=$i+1; $j<$nbTeams; $j++)
	  	{
	  		$tj =$rows[$j];
	  		if ($ti['point'] == $tj['point'])
	  		{
	  			if ($ti['deltaGame'] > $tj['deltaGame'])
	  			{
	  				$ti['bonus'] += 1;
	  				$rows[$i] = $ti;
	  			}
	  			if ($ti['deltaGame'] < $tj['deltaGame'])
	  			{
	  				$tj['bonus'] += 1;
	  				$rows[$j] = $tj;
	  			}
	  		} //end if team equal
	  	} //next $j
	  } // next $i
		}

		//$this->debug($rows);
		// S'il y  a toujours plus de deux equipes a egalites
		// on regarde le nombre de points gagnes/perdus
		if ($this->_checkTeamEqual($rows, $groupId, $rankType))
		{
	  for ($i=0; $i<$nbTeams; $i++)
	  {
	  	$ti =$rows[$i];
	  	for ($j=$i+1; $j<$nbTeams; $j++)
	  	{
	  		$tj =$rows[$j];
	  		if ($ti['point'] == $tj['point'])
	  		{
	  			if ($ti['deltaPoint'] > $tj['deltaPoint'])
	  			{
	  				$ti['bonus'] += 0.1;
	  				$rows[$i] = $ti;
	  			}
	  			if ($ti['deltaPoint'] < $tj['deltaPoint'])
	  			{
	  				$tj['bonus'] += 0.1;
	  				$rows[$j] = $tj;
	  			}
	  		} //end if team equal
	  	} //next $j
	  } // next $i
		}


		//$this->debug($rows);
		// Calcul du classement de chaque equipe
		for ($i=0; $i<$nbTeams; $i++)
		{
	  $ti =$rows[$i];
	  $ti['rank']=0;
	  for ($j=0; $j<$nbTeams; $j++)
	  {
	  	$tj =$rows[$j];
	  	if ($tj['point']+$tj['bonus'] > $ti['point']+$ti['bonus'])
	  	{
	  		$ti['rank'] ++;
	  	}
	  	$rows[$j] = $tj;
	  }
	  $rows[$i] = $ti;
		}

		//$this->debug($rows);
		// Mise a jour de la base
		$fields = array();
		for ($i=0; $i<$nbTeams; $i++)
		{
	  $ti =$rows[$i];
	  $fields['t2r_rank']   = $ti['rank'];
	  $fields['t2r_points'] = $ti['points'];
	  $fields['t2r_tieW']   = $ti['tieW'];
	  $fields['t2r_tieL']   = $ti['tieL'];
	  $fields['t2r_tieE']   = $ti['tieE'];
	  $fields['t2r_tieWO']  = $ti['tieWO'];
	  $where = "t2r_teamId=".$ti['teamId'].
	    " AND t2r_roundId =".$groupId;
	  $r = $db->update('t2r', $fields, $where);
		}

		return;
	}

	/**
	 * Check if some team have the same score
	 *
	 * @access private
	 * @param  integer  $groupId   Id of the group
	 * @return integer id of the team
	 */
	function _checkTeamEqual(&$rows, $groupId, $rankType)
	{
		$nbTeams = count($rows);
		for ($j=0; $j<$nbTeams; $j++)
		{
	  $tj = $rows[$j];
	  $tj['point'] += $tj['bonus'];
	  $tj['bonus'] = 0;
	  $rows[$j] = $tj;
		}
		if ($rankType == WBS_CALC_RANK) return true;

		$isEqual = false;
		for ($i=0; $i<$nbTeams; $i++)
		{
	  $ti =$rows[$i];
	  $teamEqual = 0;
	  $nbEqual   = 0;
	  for ($j=0; $j<$nbTeams; $j++)
	  {
	  	if ($i == $j) continue;
	  	$tj = $rows[$j];
	  	$tj['point'] += $tj['bonus'];
	  	$tj['bonus'] = 0;
	  	$rows[$j] = $tj;
	  	if ($ti['point'] == $tj['point'])
	  	{
	  		$teamEqual = $tj['teamId'];
	  		$nbEqual++;
	  	}
	  }
	  if ($nbEqual == 1)
	  {
	  	$winner = $this->_getWinTeam($groupId, $ti['teamId'],
	  	$teamEqual);
	  	if (is_array($winner)) return $winner;
	  	if ($winner == $ti['teamId']) $ti['point'] += 0.01;
	  	$rows[$i] = $ti;
	  }
	  if ($nbEqual > 1) $isEqual = true;
		}
		return $isEqual;
	}

	// {{{ _getWinTeam
	/**
	* Return the winner team of a tie
	*
	* @access private
	* @param  integer  $groupId  Id of the group
	* @param  integer  $team1    Id of the first teams
	* @param  integer  $team1    Id of the second team
	* @return integer id of the team
	*/
	function _getWinTeam($groupId, $team1, $team2)
	{
		$db =& $this->_db;

		// Search the ties of the first team
		$fields = array('tie_id', 't2t_result',
        't2t_matchW', 't2t_matchL',
        't2t_setW', 't2t_setL',
        't2t_pointW', 't2t_pointL'
        );
        $tables = array('ties', 't2t');
        $where = "tie_roundId = $groupId"
        . " AND t2t_teamId = $team1"
        . " AND t2t_tieId = tie_id";
        $res = $db->select($tables, $fields, $where);
        while ($tie = $res->fetch(PDO::FETCH_ASSOC))
        {
        	$tieIds[] = $tie;
        }
        // Search the tie of the second team
        $where = "tie_roundId = $groupId"
        . " AND t2t_teamId = $team2"
        . " AND t2t_tieId = tie_id";
        $res = $db->select($tables, $fields, $where);
        $nbTies = count($tieIds);

        $nbTie = 0;
        $deltaTie   = 0;
        $deltaMatch = 0;
        $deltaGame  = 0;
        $deltaPoint = 0;

        while ($tie = $res->fetch(PDO::FETCH_ASSOC))
        {
        	for ($i=0; $i<$nbTies;$i++)
        	{
        		$tieId = $tieIds[$i];
        		if ($tie['tie_id'] == $tieId['tie_id'])
        		{
        			$nbTie++;
        			if ($tieId['t2t_result'] == WBS_TIE_WIN) $deltaTie++;
        			if ($tieId['t2t_result'] == WBS_TIE_LOOSE) $deltaTie--;
        			$deltaMatch += ($tieId['t2t_matchW'] - $tieId['t2t_matchL']);
        			$deltaGame += ($tieId['t2t_setW'] - $tieId['t2t_setL']);
        			$deltaPoint += ($tieId['t2t_pointW'] - $tieId['t2t_pointL']);
        		}
        	}
        }
        if ( $deltaTie > 0 ) return $team1;
        else if ($deltaTie < 0) return $team2;
        else if ($deltaMatch > 0) return $team1;
        else if ($deltaMatch < 0) return $team2;
        else if ($deltaGame > 0) return $team1;
        else if ($deltaGame < 0) return $team2;
        else if ($deltaPoint > 0) return $team1;
        else if ($deltaPoint < 0) return $team2;
        else return 0;
	}


}
?>
