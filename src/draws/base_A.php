<?php
/*****************************************************************************
 !   Module     : Registrations
 !   File       : $Source: /cvsroot/aotb/badnet/src/draws/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.48 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:24 $
 ******************************************************************************/
require_once "utils/utbase.php";
require_once "utils/utround.php";
require_once "utils/utscore.php";
require_once "utils/objgroup.php";
/**
 * Acces to the dababase for draws
 */

class drawsBase_A extends utBase
{
	/**
	 * Return the table with group results
	 *
	 * @access public
	 * @param  integer  $groupId  Id of the group
	 * @return array   array of users
	 */
	function getGroup($roundId)
	{
		$matchNum = $this->_getMatchNum($roundId);

		// for each pair of the group, get the players names
		$fields = array('i2p_pairId', 'regi_shortName','regi_longName',
		      'asso_noc','team_noc', 'regi_noc',
		      'asso_logo', 'team_logo', 'regi_id',
		      'rund_name', 'team_stamp', 't2r_tds',
		      'rkdf_label', 'i2p_classe', 'i2p_cppp', 'rund_del', 'rund_pbl');
		$tables = array('members', 'registration', 'i2p', 'teams',
		      'a2t', 'assocs', 'pairs', 't2r', 'rounds',
		      'draws', 'rankdef');
		$where = "mber_id = regi_memberId".
	" AND regi_id = i2p_regiId".
	" AND regi_teamId = team_id".
	" AND team_id = a2t_teamId".
	" AND asso_id = a2t_assoId".
	" AND i2p_pairId = pair_id".
	" AND t2r_pairId = pair_id".
	" AND t2r_roundId = rund_id".
	" AND rund_id = $roundId".
	" AND rkdf_id = i2p_rankdefid".
	" AND draw_id = rund_drawId";

		$order = "t2r_posRound, i2p_pairId, mber_sexe, regi_shortName";
		$res = $this->_select($tables, $fields, $where, $order);

		$pairId = -1;
		$pairs  = array();
		$rows   = array();
		$cells  = array();
		if ($res->numRows())
		{
			$ut  = new utils();
			// for each pair of the group
			while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				// 	New pair
				$tds = $entry['t2r_tds'];
				//unset($entry['t2r_tds']);
				if ($entry['i2p_pairId'] != $pairId)
				{
					if ($pairId == -1)
					{
						$cells = array();
						$cells['pair_id'] = 0;
						$lines=array();
						$lines[] = array('value' => 'Tds');
						$lines['class'] = 'classGroupTitle';
						$cells['t2r_tds'] = $lines;
						$lines=array();
						$lines[] = array('value' => $entry['rund_name']);
						$lines['class'] = 'classGroupTitle';
						$lines['colspan'] = 5;
						$cells['names'] = $lines;
					}
					else
					{
						$pairs[] = $pair;
						$cells[] = $lines;
					}
					$pairId =$entry['i2p_pairId'];
					$pair=array();
					$lines=array();

					// Premiere colonne : id de la paire
					$pair['pair_id'] = $entry['i2p_pairId'];
					// Deuxieme  colonne : tds
					$pair['pair_tds'][0] = array('value' => $ut->getLabel($entry['t2r_tds']));
					$pair['pair_tds']['class'] = 'classGroupTds';
					// troisime colonne : nom des joueurs
					$pair['names'] = array();
					$pair['names']['class'] = 'classGroupPairL';
					// club des joueurs
					$pair['team'] = array();
					$pair['team']['class'] = 'classGroupPairL';
					// classement des joueurs
					$pair['level'] = array();
					$pair['level']['class'] = 'classGroupPairL';
					// Rang
					$pair['rank'] = array();
					$pair['rank']['class'] = 'classGroupPairL';
					// point
					$pair['point'] = array();
					$pair['point']['class'] = 'classGroupPairL';
				}
				// 	Troisieme colonne : nom des joueurs
				$pair['names'][] = array('value'=>$entry['regi_longName'],
				       'stamp' => $entry['team_stamp'],
				       'action' => array(KAF_UPLOAD, 'resu', 
				KID_SELECT, $entry['regi_id']));

				$pair['team'][] = array('value' => $entry['team_stamp']);
				$pair['level'][] = array('value' => $entry['rkdf_label']);
				$pair['rank'][] = array('value' => $entry['i2p_classe']);
				$pair['point'][] = array('value' => sprintf('%.02f', $entry['i2p_cppp']));
				
				$lines[] = array('value' => $entry['regi_shortName']);
				$lines['class'] = 'classGroupPair';
				//$lines['colspan'] = 2;
				//$imgPbl = utimg::getPubliIcon($entry['rund_del'], $entry['rund_pbl']);
			}
			$pairs[] = $pair;
			$cells[] = $lines;
			$rows[] = $cells;
		}

		$nbPairs = count($pairs);
		$numCol2 = 0;
		for($numLine=0; $numLine<$nbPairs; $numLine++)
		{
			$line = $pairs[$numLine];
			// Select the match of the pair
			$fields = array('mtch_id', 'mtch_score', 'p2m_result',
			  'tie_posRound', 'tie_schedule', 'mtch_num',
			  'rund_size', 'mtch_begin', 'mtch_end');
			$tables = array('p2m', 'matchs', 'ties', 'rounds');
			$where = "p2m_pairId =". $line['pair_id'].
	    		" AND p2m_matchId = mtch_id".
	    		" AND mtch_tieId = tie_id".
	    		" AND tie_roundId = $roundId".
	    		" AND tie_roundId = rund_id";
			$order = "tie_posRound";
			$res = $this->_select($tables, $fields, $where, $order);
			$numCol=0;
			$nbMatchs = $res->numRows();
			$utd = new utDate();
			$uts = new utscore();
			// Pour chaque match de la paire
			while (($numCol < $nbPairs-1) &&
			($match = $res->fetchRow(DB_FETCHMODE_ASSOC)))
			{
				// Mettre en forme l'heure du match
				$cells = array();
				if ($match['tie_schedule'])
				{
					$utd->setIsoDateTime($match['tie_schedule']);
					//$cells[] = array('value' => $utd->getShortDate());
					$cells[] = array('value' => $utd->getTime());
				}
				else $cells[] = array('value' => '--');
				$cells['class'] = 'classGroupTime';
				$time = $cells;

				// Mettre en forme le resultats
				$lines = array();
				$lines[] = array('value' => $match['mtch_num'],
				   		'class' => 'classGroupNum',
				       'action' => array(KAF_NEWWIN, 'matches', 
				KID_EDIT, $match['mtch_id'], 600, 350));
				$cells = array();
				$cells['class'] = 'classGroupData';
				if ($match['p2m_result'] >= WBS_RES_LOOSE)
				{
					$utd->setIsoDateTime($match['mtch_begin']);
					$duree = $utd->elaps($match['mtch_end']);
					$uts->setScore($match['mtch_score']);
					$score = $uts->getLoosScore();
					$lines[] = array('value' => $score,
					   'class' => 'classGroupScoreLoos');
					if ($duree >0)
					$lines[] = array('value' => "$duree mn",
				     		'class' => 'classGroupScoreLoos');
				}
				else if($match['p2m_result'] != WBS_RES_NOPLAY)
				{
					$utd->setIsoDateTime($match['mtch_begin']);
					$duree = $utd->elaps($match['mtch_end']);
					$uts->setScore($match['mtch_score']);
					$score = $uts->getWinScore();
					$lines[] = array('value' => $score,
				   			'class' => 'classGroupScoreWin');
					if ($duree >0)
					$lines[] = array('value' => "$duree mn",
				     		'class' => 'classGroupScoreLoos');
				}
				$cells = $lines;
				$content = $cells;

				// Si la colonne et la ligne sont identiques,
				// il n'y a rien a marquer (une paire ne joue
				// 	pas contre elle meme)
				if ($numCol == $numLine)
				{
					$cells = array();
					$cells[] = array('value' => "-dd--");
					$cells['class'] = 'classGroupNone';
					//$cells['colspan'] = 2;
					$line[$numCol]= $cells;
				}
				// La colonne est superieure a la ligne,
				// on affiche dans la partie haute du tableau
				if ($numCol >= $numLine)
				{
					$line[$numCol+1]= $content;
				}
				// Si le nombre de match est egal au nombre de paire -1
				// 	la poule se joue en un seul match, pas en aller/retour
				// On affiche dans la partie basse du tableau
				else if($nbMatchs == $nbPairs-1)
				{
					$line[$numCol]= $content;
				}

				$numCol++;
			}
			//$line[]= $stamp[$numLine];
			//$line[]= $flag[$numLine];
			$cells = array();
			$cells[] = array('value' => "---");
			$cells['class'] = 'classGroupNone';
			//$cells['colspan'] = 2;
			$line[$numLine]= $cells;
			$rows[] = $line;
		}
		//print_r($rows);
		return $rows;
	}

	/**
	 * Return the numero of the match in a group
	 *
	 * @access public
	 * @param  integer  $groupId  Id of the group
	 * @return array   array of users
	 */
	function _getMatchNum($roundId)
	{
		// Find the match of the group
		$fields = array('mtch_id', 'mtch_num', 'rund_size',
		      'mtch_status', 'tie_posRound');
		$tables = array('matchs', 'ties', 'rounds');
		$where  = "rund_id=$roundId".
	" AND mtch_tieId=tie_id".
	" AND tie_roundId=rund_id";
		$order = 'tie_posRound';
		$res = $this->_select($tables, $fields, $where, $order);
		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$matchs[$match['tie_posRound']] = $match;
			$matchNum[$match['tie_posRound']] = $match['mtch_num'];
		}

		if (isset($matchs[2]) &&
		$matchs[2]['rund_size'] == 3 &&
		$matchs[2]['mtch_status'] <= WBS_MATCH_LIVE)
		{
			$num = $matchNum[0];
			$matchNum[0] .= " | ".$matchNum[1];
			$matchNum[1] .= " | ".$num;
		}
		return $matchNum;
	}



	// {{{ getRound
	/**
	 * Return the table with tie's data of a round
	 *
	 * @access public
	 * @param  integer  $roundId  Id of the round
	 * @return array   array of users
	 */
	function getRound($roundId)
	{
		// Get the ties of the round
		$utr = new utRound();
		$ut = new utils();
		$ties = $utr->getMatchs($roundId, false);
		$uti = new utimg();

		// Get the round definition
		$round = $utr->getRoundDef($roundId);

		// Update step data
		$rows = array();
		if (($round['rund_type'] == WBS_ROUND_MAINDRAW) ||
		($round['rund_type'] == WBS_ROUND_QUALIF))
		{
	  for($i=0; $i<count($ties); $i++)
	  {
	  	$tie = $ties[$i];
	  	$step = $utr->getTieStep($tie['tie_posRound']);
	  	$tie['tie_step'] = $ut->getLabel($step);
	  	if(!$tie['tie_posRound'])
	  	$tie['tie_posRound'] = 1;
	  	else if($tie['tie_posRound']<3)
	  	$tie['tie_posRound'] = $tie['tie_posRound'];
	  	else if($tie['tie_posRound']<7)
	  	$tie['tie_posRound'] -= 2;
	  	else if($tie['tie_posRound']<15)
	  	$tie['tie_posRound'] -= 6;
	  	else if($tie['tie_posRound']<31)
	  	$tie['tie_posRound'] -= 14;
	  	else if($tie['tie_posRound']<63)
	  	$tie['tie_posRound'] -= 30;
	  	 
	  	$tie['tie_pbl'] = $uti->getPubliIcon($tie['tie_del'],
	  	$tie['tie_pbl']);
	  	$rows[] = $tie;
	  }
		}
		else
		{
	  for($i=0; $i<count($ties); $i++)
	  {
	  	$tie = $ties[$i];
	  	$pos = $tie['tie_posRound'];
	  	$tie['tie_posRound'] = $tie['tie_step'];
	  	$tie['tie_step']= $utr->getTieStep($pos, WBS_ROUND_GROUP);
	  	$tie['tie_pbl'] = $uti->getPubliIcon($tie['tie_del'],
	  	$tie['tie_pbl']);
	  	$rows[] = $tie;
	  }
		}
		return $rows;
	}
	// }}}

	// {{{ completeDraw
	/**
	* Update line of a round
	*
	* @access public
	* @param array   $ids  Ids to keep
	* @return none
	*/
	function completeRound($aDraw, $aRound)
	{
		$uti = new utimg();

			
		/*$fields = array('rund_id', 'rund_name', 'rund_stamp', 'rund_type',
		 'rund_drawId', 'rund_size', 'rund_entries',
		 'rund_qual', 'draw_name', 'rund_del', 'rund_pbl', 'draw_stamp');*/
		$line = array(
		$aRound['rund_id'],
		$aRound['rund_group'],
		$aRound['rund_name'],
		$aRound['rund_stamp'],
		$aRound['rund_entries']);
		switch($aRound['rund_type'])
		{
			case WBS_ROUND_GROUP:
				$nb=0;
				for($i=1;$i<$aRound['rund_size'];$i++) $nb+=$i;
				$line[] = $nb;
				break;
			default:
				$line[] = $aRound['rund_entries'] -1;
				break;
		}
		$line[] = $aRound['rund_qual'];
		$line['draw_id'] = $aRound['rund_drawId'];
		$line['iconPbl'] = $uti->getIcon($aRound['rund_pbl']);

		// Nombre de paire dans le groupe
		$line['pairsKo'] = '';
		$tables = array('matchs', 'ties', 'rounds', 'p2m');
		$where = "mtch_tieId = tie_id".
			" AND tie_roundId = rund_id".
			" AND tie_isBye = 0".
			" AND rund_id=" . $aRound['rund_id'].
			" AND p2m_matchId = mtch_id";
		$nb = $this->_selectFirst($tables, 'count(*)', $where);
		if (!$nb) $line['pairsKo'] = $uti->getIcon(WBS_NO);

		// Nombre de match non termines
		$line['endMatch'] = '';
		$tables = array('matchs', 'ties', 'rounds');
		$where = "mtch_tieId = tie_id".
		 " AND tie_roundId = rund_id".
		 " AND tie_isBye = 0".
		 " AND rund_id=". $aRound['rund_id'].
		 " AND mtch_status < ".WBS_MATCH_ENDED;
		$nb = $this->_selectFirst($tables, 'count(*)', $where);
		if ($nb) $line['endMatch'] = $uti->getIcon(80);

		return $line;
	}
	// }}}

	// {{{ completeDraw
	/**
	 * Update line of a draw
	 *
	 * @access public
	 * @param array   $ids  Ids to keep
	 * @return none
	 */
	function completeDraw(&$draw)
	{
		$uti = new utimg();

		// Nombre de match de poule non termines
		$draw['endGroup'] = '';
		if ($draw['Groups'] != '')
		{
	  $tables = array('matchs', 'ties', 'rounds');
	  $where = "mtch_tieId = tie_id".
	    " AND tie_roundId = rund_id".
	    " AND rund_drawId={$draw['draw_id']}".
	    " AND rund_type = ".WBS_ROUND_GROUP.
	    " AND mtch_status < ".WBS_MATCH_ENDED;
	  $nb = $this->_selectFirst($tables, 'count(*)', $where);
	  if ($nb)
	  $draw['endGroup'] = $uti->getIcon(WBS_RES_NOPLAY);
		}
		// Nombre de match KO non termines
		$draw['endKo'] = '';
		/*
		 $tables = array('matchs', 'ties', 'rounds');
		 $where = "mtch_tieId = tie_id".
		 " AND tie_roundId = rund_id".
		 " AND tie_isBye = 0".
		 " AND rund_drawId={$draw['draw_id']}".
		 " AND rund_type = ".WBS_ROUND_MAINDRAW.
		 " AND mtch_status < ".WBS_MATCH_ENDED;
		 $nb = $this->_selectFirst($tables, 'count(*)', $where);
		 if ($nb)
		 $draw['endKo'] = $uti->getIcon(WBS_NO);
		 else
		 $draw['endKo'] = $uti->getIcon(WBS_YES);
		 */
		// Equipe dans les match KO
		$draw['pairsKo'] = '';
		if ($draw['Main'] != '')
		{
	  $tables = array('matchs', 'ties', 'rounds', 'p2m');
	  $where = "mtch_tieId = tie_id".
	    " AND tie_roundId = rund_id".
	    " AND tie_isBye = 0".
	    " AND rund_drawId={$draw['draw_id']}".
	    " AND rund_type = ".WBS_ROUND_MAINDRAW.
	    " AND p2m_matchId = mtch_id";
	  $nb = $this->_selectFirst($tables, 'count(*)', $where);
	  if (!$nb)
	  $draw['pairsKo'] = $uti->getIcon(WBS_NO);
		}
		return $draw;
	}
	// }}}

	// {{{ updateRankFromFede
	/**
	 * Update rank for player
	 *
	 * @access public
	 * @param array   $ids  Ids to keep
	 * @return none
	 */
	function updateRankFromFede($drawId)
	{
		// Recuperer les donnees des joueurs
		// du tableau
		$tables = array('members', 'registration', 'i2p', 'pairs');
		$fields = array('mber_id', 'mber_licence', 'mber_fedeid', 'regi_id', 'mber_sexe');
		$where = "regi_memberId = mber_id".
	" AND regi_eventId=".utvars::getEventId().
	" AND regi_type=".WBS_PLAYER.
	" AND regi_id=i2p_regiId".
	" AND i2p_pairId=pair_id".
	" AND pair_drawId=$drawId".
	" AND mber_id>0";
		$order = 'mber_secondname, mber_firstname';
		$res = $this->_select($tables, $fields,  $where, $order);
		if ($res->numRows())
		{
	  		while ($player = $res->fetchRow(DB_FETCHMODE_ASSOC)) $players[] = $player;
		}

		// Recuperer les donnees depuis poona
		require_once "import/imppoona.php";
		$poona = new ImpPoona();
		$res = $poona->updateRanks($players);
		return;
	}
	// }}}


	// {{{ getEntriesStats
	/**
	 * Get number of pairs per draw
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getEntriesStats()
	{
		// Update the pairs positions
		$fields = array('count(pair_id) as nb', 'draw_id' , 'draw_disci',
		      'draw_serial', 'mber_sexe');
		$tables = array('draws LEFT JOIN pairs ON pair_drawId= draw_id'
		,'i2p', 'registration', 'members'
		);
		$where = 	"draw_eventId=".utvars::getEventId().
	" AND i2p_pairId = pair_id".
	" AND i2p_regiId = regi_id".
	" AND regi_memberId = mber_id".
        " GROUP BY draw_id,mber_sexe";
		$order = "draw_serial, draw_disci";
		$res = $this->_select($tables, $fields,  $where, $order);
		$stats = array();
		while($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$stats[]=$entry;
		return $stats;
	}
	// }}}

	// {{{ getMatchsStats
	/**
	 * Get number of patch per draws
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getMatchsStats()
	{
		// Update the pairs positions
		$fields = array('count(tie_id) as nb', 'draw_id' , 'draw_disci',
		      'draw_serial');
		$tables = array('draws LEFT JOIN rounds ON rund_drawId=draw_id
                 LEFT JOIN ties ON tie_roundId=rund_id');
		$where = 	"draw_eventId=".utvars::getEventId().
	" AND tie_isBye=0".
        " GROUP BY draw_id";
		$order = "draw_serial, draw_disci";
		$res = $this->_select($tables, $fields,  $where, $order);
		$stats = array();
		while($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$stats[]=$entry;
		return $stats;
	}
	// }}}

	// {{{ getWinners
	/**
	 * Return the pairs who win their match and the score
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getWinners($roundId)
	{
		$ut = new utils();
		// Find the id of the pairs
		$fields = array('p2m_pairId', 'mtch_score', 'tie_posRound',
		      'mtch_begin', 'mtch_end');
		$tables = array('p2m', 'matchs', 'ties');
		$where  = "p2m_matchId = mtch_id".
	" AND mtch_tieId = tie_id".
	" AND tie_roundId = $roundId".
	" AND ( ((p2m_result = ".WBS_RES_WIN.
	" OR p2m_result = ".WBS_RES_WINAB.
	" OR p2m_result = ".WBS_RES_WINWO.') AND mtch_luckylooser ='.WBS_NO .')'.
	" OR ((p2m_result = ".WBS_RES_LOOSE.
	" OR p2m_result = ".WBS_RES_LOOSEAB.
	" OR p2m_result = ".WBS_RES_LOOSEWO.') AND mtch_luckylooser ='.WBS_YES . "))";
		$res = $this->_select($tables, $fields, $where);
		$pairs = array();
		while($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $pairsId[] = $entry['p2m_pairId'];
	  $entry['value'] = array();
	  $entry['score'] = "";
	  $pairs[] = $entry;
		}
		// Retrieve players name of the pairs
		$rows = array();
		if (isset($pairsId))
		{
	  $ids = implode(',', $pairsId);
	  $fields = array('i2p_pairId', 'regi_shortName',
			  'asso_noc','team_noc', 'regi_noc',
			  'asso_logo', 'team_logo', 'regi_id', 'team_stamp');//, 'mber_ibfnumber');
	  $tables = array('members', 'registration', 'i2p', 'teams',
			  'a2t', 'assocs');
	  $where = "mber_id = regi_memberId".
	    " AND regi_id = i2p_regiId".
	    " AND regi_teamId = team_id".
	    " AND team_id = a2t_teamId".
	    " AND asso_id = a2t_assoId".
	    " AND i2p_pairId IN ($ids)";

	  $order = "i2p_pairId, mber_sexe, regi_shortName";
	  $res = $this->_select($tables, $fields, $where, $order);
	  $uts = new utscore();
	  $utd = new utdate();
	  while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  {
	  	foreach($pairs as $idx => $pair)
	  	{
	  		if ($entry['i2p_pairId'] == $pair['p2m_pairId'])
	  		{
	  			$name = $entry['regi_shortName'];
	  			$line = array();
	  			$line['name'] = $name;
	  			$line['stamp'] = $entry['team_stamp'];
	  			$line['level'] = '';
	  			$pair['value'][] = $line;
	  			if ($uts->setScore($pair['mtch_score'])!=-1)
	  			{
	  				if ($uts->isWo())
			    $pair['score'] = "WO";
			    else
			    {
			    	$utd->setIsoDateTime($pair['mtch_begin']);
			    	$duree = $utd->elaps($pair['mtch_end']);
			    	$pair['score'] = $uts->getWinScore();
			    	if ($duree >0)
			    	$pair['score'] .= " ($duree mn)";
			    }
	  			}
	  			else
	  			$pair['score'] = '';
	  			$pairs[$idx] = $pair;
	  			$rows[$pair['tie_posRound']] = $pair;
	  		}
	  	}
	  }
		}
		return $rows;
	}
	// }}}

	/**
	 * Return the pairs of a draw
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getDrawPairs($drawId, $sort)
	{
		$ut = new utils();

		$utd = new utdraw();
		$draw = $utd->getDrawById($drawId);
		$disci = $draw['draw_disci'];

		// Select all the pairs of the draw, ordered with satus and position
		$fields = array('pair_id', 'pair_status', 'pair_order', 'pair_wo');
		$tables = array('pairs');
		$where  = "pair_drawId=$drawId";
		$order = "pair_status, pair_order";
		$res = $this->_select($tables, $fields, $where, $order);
		// For each pair, calculate his new position according WO
		$nbPTM = 0;
		$nbPTQ = 0;
		$nbWo = 0;
		while($pair = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($pair['pair_wo']) $evolution[$pair['pair_id']] = $ut->getSmaLabel(WBS_PAIR_WDN);
			else $evolution[$pair['pair_id']] = '';
			if (($pair['pair_status'] == WBS_PAIR_MAINDRAW) && $pair['pair_wo'])
			{
				$nbWo++; $nbPTM++; $nbPTQ++;
				$evolution[$pair['pair_id']] = $ut->getSmaLabel(WBS_PAIR_WDN);
			}
			if ($pair['pair_status'] == WBS_PAIR_QUALIF)
			{
				if ($pair['pair_wo'])
				{
					$nbWo++; $nbPTQ++;
					$evolution[$pair['pair_id']] = $ut->getSmaLabel(WBS_PAIR_WDN);
				}
				else
				{
					if ($nbPTM)
					{
						$evolution[$pair['pair_id']] = $ut->getSmaLabel(WBS_PAIR_PTM);
						$nbPTM--;
					}
					else if ($nbWo)
					{
						$evolution[$pair['pair_id']] = $ut->getSmaLabel(WBS_PAIR_QUALIF);
						if ($pair['pair_order'] >0) $evolution[$pair['pair_id']] .=	($pair['pair_order']-$nbWo);
					}
				}
			}
			if ($pair['pair_status'] == WBS_PAIR_RESERVE)
			{
				if ($pair['pair_wo']) $evolution[$pair['pair_id']] = $ut->getSmaLabel(WBS_PAIR_WDN);
				else
				{
					if ($nbPTQ)
					{
						if ($draw['draw_type'] == WBS_QUALIF) $evolution[$pair['pair_id']] = $ut->getSmaLabel(WBS_PAIR_PTQ);
						else $evolution[$pair['pair_id']] = $ut->getSmaLabel(WBS_PAIR_PTM);
						$nbPTQ--;
					}
					else if ($nbWo)
					$evolution[$pair['pair_id']] = $ut->getSmaLabel(WBS_PAIR_RESERVE).($pair['pair_order']-$nbWo);
				}
			}
		}

		// Select the players of the pairs
		$eventId = utvars::getEventId();
		$fields = array('pair_id', 'regi_longName', 'regi_catage', 'regi_surclasse',
		      'rkdf_label', 'i2p_cppp', 'i2p_classe',
		      'regi_date', 'pair_status', 'pair_wo',
		      'team_name', 'pair_datewo', 'pair_pbl', 'team_stamp',
		      'pair_del', 'regi_id', 'team_id',
		      'pair_order', 'mber_ibfnumber', 'mber_licence',
		      'team_noc', 'asso_noc', 'pair_state', 'regi_dateauto', 'regi_numcatage');
		$tables = array('pairs', 'i2p', 'registration', 'draws',
		      'members', 'teams', 'a2t', 'assocs', 'rankdef');
		$where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_type = ".WBS_PLAYER.
	" AND regi_id = i2p_regiId".
	" AND i2p_pairId = pair_id".
	" AND pair_drawId=$drawId".
	" AND draw_id=pair_drawId".
	" AND mber_id=regi_memberId".
	" AND regi_teamId=team_id".
	" AND regi_eventId=$eventId".
	" AND team_id = a2t_teamId".
	" AND asso_id = a2t_assoId".
	" AND mber_id >= 0".
	" AND i2p_rankdefId = rkdf_id";

		$order = "pair_state, pair_id, mber_sexe,  regi_longName";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
			$infos['errMsg'] = "msgNoPlayers";
			return $infos;
		}

		$uti = new utimg();
		$pairId = -1;
		$rows = array();
		$rowsnok = array();
		$triok = array();
		$trinok = array();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($pairId != $entry['pair_id'])
			{
				if ($pairId > -1)
				{
					$row['regi_longName'] = $cells['name'];
					$row['regi_catage']   = $cells['catage'];
					$row['regi_surclasse'] = $cells['surclasse'];
					$row['team_name'] = $cells['team'];
					$row['rkdf_label'] = $cells['level'];
					$row['i2p_cppp'] = $cells['average'];
					$row['i2p_classe'] = $cells['classe'];
					$row['pair_average'] /= $nbPlayers;
					$row['regi_date'] = $cells['date'];
					$tmp = array_values($row);
					if ($row['pair_state'] == WBS_PAIRSTATE_COM) $row['pair_stateLogo'] = $uti->getIcon(WBS_PAIRSTATE_COM);
					else $row['pair_stateLogo'] = "";
					if ($row['pair_state'] == WBS_PAIRSTATE_NOK)
					{
						$rowsnok[] = $row;
						$trinok[] = $tmp[abs($sort)-1];
					}
					else
					{
						$rows[] = $row;
						$triok[] = $tmp[abs($sort)-1];
					}
				}
				$cells['name']  = array();
				$cells['team']  = array();
				$cells['level']  = array();
				$cells['average']  = array();
				$cells['classe']  = array();
				$cells['catage']  = array();
				$cells['surclasse']  = array();
				$cells['date']  = array();
				$row = $entry;
				if ( $row['pair_wo']) $row['class'] = 'classWo';
				$row['pair_wo'] = $evolution[$row['pair_id']];
				$row['pair_pbl'] = $uti->getPubliIcon($entry['pair_del'],
				$entry['pair_pbl']);
				$row['pair_status'] = $ut->getSmaLabel($entry['pair_status']);
				$row['regi_catage'] = $ut->getLabel($entry['regi_catage']);
				if ($entry['regi_numcatage']) $row['regi_catage'] .= ' ' . $entry['regi_numcatage'];
				$row['regi_surclasse'] = $ut->getLabel($entry['regi_surclasse']);
				$row['pair_noc'] = '';
				$row['pair_average'] = 0;
				$nbPlayers = 0;
				if ($entry['pair_order']) $row['pair_status'] = sprintf("%s %02d",$row['pair_status'],
				$entry['pair_order']);
				$pairId = $entry['pair_id'];
				$tmp = array_values($row);
				$tri[] = $tmp[abs($sort)-1];
			}
			$row['pair_noc'] .= $entry['team_noc']==''?$entry['asso_noc']:
			$entry['team_noc'];
			$row['pair_average'] += $entry['i2p_cppp'];
			$nbPlayers++;
			$lines = $cells['name'];
			$lines[] = array('value'   =>$entry['regi_longName'],
			   'stamp'   => $entry['team_stamp'],
			   'ibfNum'  => $entry['mber_ibfnumber'],
			   'licence' => $entry['mber_licence'],
			   'level'   => $entry['rkdf_label'],
			   'average' => $entry['i2p_cppp'],
			   'classe' => $entry['i2p_classe'],
				'noc'     => $entry['team_noc']==''?$entry['asso_noc']: $entry['team_noc'],
			   'action'  => array(KAF_UPLOAD, 'resu', KID_SELECT, $entry['regi_id'])
			);
			$cells['name'] = $lines;
			$lines = $cells['team'];
			$lines[] = array('value' =>$entry['team_name']
			,'action' => array(KAF_UPLOAD, 'teams', KID_SELECT, $entry['team_id']));
			$cells['team'] = $lines;

			$lines = $cells['level'];
			if ($entry['regi_dateauto'] == '0000-00-00')
			$lines[] = array('value' =>$entry['rkdf_label'], 'logo' => $uti->getIcon(WBS_NO));
			else $lines[] = array('value' =>$entry['rkdf_label']);
			$cells['level'] = $lines;

			$lines = $cells['catage'];
			if ($entry['regi_numcatage']) $lines[] = array('value' =>$ut->getLabel($entry['regi_catage']) . ' ' . $entry['regi_numcatage']);
			else $lines[] = array('value' =>$ut->getLabel($entry['regi_catage']));
			$cells['catage'] = $lines;

			$lines = $cells['surclasse'];
			$lines[] = array('value' =>$ut->getLabel($entry['regi_surclasse']));
			$cells['surclasse'] = $lines;

			$lines = $cells['average'];
			$lines[] = array('value' =>sprintf('%.02f', $entry['i2p_cppp']) );
			$cells['average'] = $lines;

			$lines = $cells['classe'];
			$lines[] = array('value' =>$entry['i2p_classe']);
			$cells['classe'] = $lines;

			$lines = $cells['date'];
			$lines[] = array('value' =>$entry['regi_date']);
			$cells['date'] = $lines;
		}
		$row['regi_longName'] = $cells['name'];
		$row['regi_catage']   = $cells['catage'];
		$row['regi_surclasse'] = $cells['surclasse'];
		$row['team_name'] = $cells['team'];
		$row['regi_date'] = $cells['date'];
		$row['rkdf_label'] = $cells['level'];
		$row['i2p_cppp'] = $cells['average'];
		$row['i2p_classe'] = $cells['classe'];
		$row['pair_average'] /= $nbPlayers;
		$tmp = array_values($row);
		if ($row['pair_state'] == WBS_PAIRSTATE_COM) $row['pair_stateLogo'] = $uti->getIcon(WBS_PAIRSTATE_COM);
		else $row['pair_stateLogo'] = "";

		if ($row['pair_state'] == WBS_PAIRSTATE_NOK)
		{
			$rowsnok[] = $row;
			$trinok[] = $tmp[abs($sort)-1];
		}
		else
		{
			$rows[] = $row;
			$triok[] = $tmp[abs($sort)-1];
		}
		if ($sort > 0)
		{
			array_multisort($trinok, $rowsnok);
			array_multisort($triok, $rows);
		}
		else
		{
			array_multisort($trinok, SORT_DESC, $rowsnok);
			array_multisort($triok, SORT_DESC, $rows);
		}
		if ($disci != WBS_MS &&
		$disci != WBS_WS &&
		$disci != WBS_AS )
		array_unshift($rows,
		array(KOD_BREAK, "title", "Paires complètes",'',$disci, 'listAct' => array(1)));
		if (count($rowsnok))
		{
			$rows[] = array(KOD_BREAK, "title", "Joueurs seuls",'',$disci, 'listAct' => array(0));
			$rows = array_merge($rows, $rowsnok);
		}
		return $rows;
	}

	// {{{ getRoundPairs
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
		      'rund_stamp', 't2r_posRound', 't2r_id');
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
	  $fields = array('i2p_pairId', 'regi_longName',
			  'asso_noc','team_noc', 'regi_noc',
			  'asso_logo', 'team_logo', 'regi_id',
			  'asso_id', 'team_id','mber_ibfnumber',
			  'rkdf_label', 'asso_stamp', 'i2p_cppp',
			  'rkdf_point', 'team_stamp', 'i2p_classe');
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
	  $isSquash = $ut->getParam('issquash', false);
	  while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  {
	  	$pair = $pairs[$entry['i2p_pairId']];
	  	$name = $entry['regi_longName'];
	  	if (isset($pair['noc'])) $noc = $pair['noc'].'-';
	  	else $noc = '';

	  	if ($entry['regi_noc'] != '')
	  	{
	  		//$name .= ' ('.$entry['regi_noc'].')';
	  		$noc .= $entry['regi_noc'];
	  	}
	  	else if ($entry['team_noc'] != '')
	  	{
	  		//$name .= ' ('.$entry['team_noc'].')';
	  		$noc .= $entry['team_noc'];
	  	}
	  	else
	  	{
	  		//$name .= ' ('.$entry['asso_noc'].')';
	  		$noc .= $entry['asso_noc'];
	  	}
	  	if ($isSquash) $name .= " (" . $entry['rkdf_label'] . '-' . $entry['i2p_classe'] . ")";
	  	else $name .= " (" . $entry['rkdf_label'] . '-' . $entry['team_stamp'] . ")";
	  	//$name .= " ({$entry['team_stamp']})";
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
		  if ($entry['team_logo'] != '') $logo = utimg::getPathTeamLogo($entry['team_logo']);
		  else if ($entry['asso_logo'] != '') $logo = utimg::getPathFlag($entry['asso_logo']);
		  else $logo = '';
		  $line = array();
		  $line['logo'] = $logo;
		  $line['rank'] = $entry['i2p_classe'];
		  $line['name'] = $name;
		  $line['longname'] = $entry['regi_longName'];
		  $line['level'] = $entry['rkdf_label'];
		  $line['ibfn'] = $entry['mber_ibfnumber'];
		  $line['stamp'] = $entry['team_stamp'];
		  $line['action'] = array(KAF_UPLOAD, 'resu',
		  KID_SELECT, $entry['regi_id']);

		  $line['action'] = array(KAF_NEWWIN, 'pairs', PAIR_ASK_T2RSTATUS, $pair['t2r_id'], 400,200);
		  $pair['value'][] = $line;
		  $pairs[$entry['i2p_pairId']] = $pair;
	  }
		}
		return $pairs;
	}
	// }}}

	// {{{ getRoundId
	/**
	 * Return the players of a round
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getRoundId($drawId, $roundType)
	{
		$ut = new utils();
		// Find the id of the round
		$fields = array('rund_id');
		$tables = array('rounds');
		$where  = "rund_drawId=$drawId".
	" AND rund_type=$roundType";
		$res = $this->_select($tables, $fields, $where);
		$list = array();
		if ($roundType == WBS_ROUND_MAINDRAW)
		{
	  $entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  return $entry['rund_id'];
		}
		else
		{
	  while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  $list[] = $entry['rund_id'];
	  return $list;
		}
	}
	// }}}


	// {{{ getRoundIdIndiv
	/**
	 * Return the players of a round
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getRoundIdIndiv($drawId, $roundType)
	{
		$ut = new utils();
		// Find the id of the round
		$fields = array('rund_id');
		$tables = array('rounds');
		$where  = "rund_id=$drawId".
	" AND rund_type=$roundType";
		$res = $this->_select($tables, $fields, $where);
		$entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $entry['rund_id'];
	}
	// }}}


	// {{{ getSerial
	/**
	 * Return the specification of a serial
	 *
	 * @access public
	 * @param  string  $drawID    Id of the draw
	 * @return array   information of the user if any
	 */
	function getSerial($drawId)
	{
		$eventId = utvars::getEventId();
		// Search the serial of the draw
		$fields = array('draw_serial', 'draw_name');
		$tables[] = 'draws';
		$where = "draw_id = '$drawId'".
	" AND draw_eventId = $eventId ".
	" AND draw_del =".WBS_DATA_UNDELETE;
		$res = $this->_select($tables, $fields, $where);

		$entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$name = $entry['draw_name'];
		// Now select all the draws of the serial
		$fields= array('draw_disci');
		$tables = array('draws');
		$where = "draw_serial = '$name'".
	" AND draw_eventId = $eventId ".
	" AND draw_del =".WBS_DATA_UNDELETE;
		$res = $this->_select($tables, $fields, $where);

		$infos['draw_name'] = $name;
		$infos['draw_oldName'] = $name;
		$infos['tie_nbms'] = "";
		$infos['tie_nbws'] = "";
		$infos['tie_nbmd'] = "";
		$infos['tie_nbwd'] = "";
		$infos['tie_nbxd'] = "";
		$infos['tie_nbas'] = "";
		$infos['tie_nbad'] = "";
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  switch ($entry['draw_disci'])
	  {
	  	case WBS_MS :
	  		$infos['tie_nbms'] = 1;
	  		break;
	  	case WBS_LS :
	  		$infos['tie_nbws'] = 1;
	  		break;
	  	case WBS_MD :
	  		$infos['tie_nbmd'] = 1;
	  		break;
	  	case WBS_LD :
	  		$infos['tie_nbwd'] = 1;
	  		break;
	  	case WBS_MX :
	  		$infos['tie_nbxd'] = 1;
	  		break;
	  	case WBS_AS :
	  		$infos['tie_nbas'] = 1;
	  		break;
	  	case WBS_AD :
	  		$infos['tie_nbad'] = 1;
	  		break;
	  }
		}

		$trans = array('draw_serial', 'draw_name','draw_stamp');
		$infos = $this->_getTranslate('draws', $trans, $drawId, $infos);
		return $infos;
	}
	// }}}

	/**
	 * Update the draw with the informations
	 *
	 * @access public
	 * @param  string  $info   column of the event
	 * @return mixed
	 */
	function updateDraw($infos)
	{

		/* Mettre a jour le tableau */
		$draw['draw_name']  = $infos['draw_name'];
		$draw['draw_stamp'] = $infos['draw_stamp'];
		$draw['draw_type']  = $infos['draw_type'];
		$draw['draw_id']    = $infos['draw_id'];
		$draw['draw_serial']= $infos['draw_serial'];
		$draw['draw_disci'] = $infos['draw_disci'];
		switch($infos['draw_disci'])
		{
			case WBS_MS:
			case WBS_WS:
			case WBS_AS:
				$draw['draw_discipline'] = WBS_SINGLE;
				break;
			case WBS_MD:
			case WBS_WD:
			case WBS_AD:
				$draw['draw_discipline'] = WBS_DOUBLE;
				break;
			case WBS_XD:
				$draw['draw_discipline'] = WBS_MIXED;
				break;
		}
		$draw['draw_catage'] = $infos['draw_catage'];
		$draw['draw_numcatage'] = $infos['draw_numcatage'];
		$draw['draw_rankdefId'] = $infos['draw_rankdefId'];
		$utd = new utdraw();
		$utd->updateDrawDef($draw);
		return;
	}

	/**
	 * Update the draw with the informations
	 *
	 * @access public
	 * @param  string  $info   column of the event
	 * @return mixed
	 */
	function updateGroups($infos, $aVerif = true)
	{
		$drawId = $infos['draw_id'];
		$utd = new utdraw();
		$draw = $utd->getDrawById($drawId);
		// Verification que le groupe existe
		if ( $aVerif )
		{
			$where = "rund_drawId=" . $drawId .
				" AND rund_group='".$infos['group'] . "'";
			$res = $this->_select('rounds', 'rund_id', $where);
			$rounds = $this->_getRows('rounds', 'rund_id', $where);
			if ( !empty($rounds) )
			{
				$err['errMsg'] = 'msgGroupExist';
				return $err;
			}
		}

		$rund['rund_drawId'] = $drawId;
		$rund['rund_byes'] = 0;
		$rund['rund_qualPlace'] = 0;
		$rund['rund_type'] = WBS_ROUND_GROUP;
		$rund['rund_rankType'] = WBS_CALC_RESULT;
		$rund['rund_matchWin'] = 1;
		$rund['rund_matchLoose'] = 0;
		$rund['rund_matchRtd'] = 0;
		$rund['rund_matchWO'] = -1;
		$rund['rund_tieWin'] = 1;
		$rund['rund_tieEqual'] = 1;
		$rund['rund_tieLoose'] = 0;
		$rund['rund_tieWO'] = 0;
		$tie['tie_nbms'] = 0;
		$tie['tie_nbws'] = 0;
		$tie['tie_nbmd'] = 0;
		$tie['tie_nbwd'] = 0;
		$tie['tie_nbxd'] = 0;
		$tie['tie_nbas'] = 0;
		$tie['tie_nbad'] = 0;
		switch ($draw['draw_disci'])
		{
			case WBS_MS :
				$tie['tie_nbms'] = 1;
				break;
			case WBS_LS :
				$tie['tie_nbws'] = 1;
				break;
			case WBS_MD :
				$tie['tie_nbmd'] = 1;
				break;
			case WBS_LD :
				$tie['tie_nbwd'] = 1;
				break;
			case WBS_MX :
				$tie['tie_nbxd'] = 1;
				break;
			case WBS_AS :
				$tie['tie_nbas'] = 1;
				break;
			case WBS_AD :
				$tie['tie_nbad'] = 1;
				break;
		}

		$utr = new utround();

		// Traiter les groupes quelque soit le type de
		// tableau : les groupes superflus seront supprimes
		$nbGroup = $infos['draw_nbp3'] + $infos['draw_nbp4'] + $infos['draw_nbp5'];
		$rund['rund_rge'] = $infos['rund_rge'];
		$ut= new utils();
		$rund['rund_group'] = $infos['group'];
		$rund['rund_name'] = $ut->getLabel(WBS_ROUND_GROUP);
		//$rund['rund_stamp'] = 'A';
		$firstGroup = ord('A');

		$rund['rund_qual'] = $infos['draw_nbs3'];
		$rund['rund_qualPlace'] = $infos['draw_nbSecond'];
		$rund['rund_size'] = 3;
		$rund['rund_entries'] = 3;
		$res= $this->_treatGroups($rund, $tie,  $infos['draw_nbp3'], $firstGroup);
		if (isset( $res['errMsg'])) echo $res['errMsg'];

		$rund['rund_qual'] = $infos['draw_nbs4'];
		$rund['rund_size'] = 4;
		$rund['rund_entries'] = 4;
		//$rund['rund_stamp']= chr(ord($rund['rund_stamp'])+$infos['draw_nbp3']);
		$res= $this->_treatGroups($rund, $tie, $infos['draw_nbp4'], $firstGroup + $infos['draw_nbp3']);
		if (isset( $res['errMsg'])) echo $res['errMsg'];

		$rund['rund_qual'] = $infos['draw_nbs5'];
		$rund['rund_size'] = 5;
		$rund['rund_entries'] = 5;
		//$rund['rund_stamp']= chr(ord($rund['rund_stamp'])+$infos['draw_nbp4']);
		$res= $this->_treatGroups($rund, $tie, $infos['draw_nbp5'], $firstGroup + $infos['draw_nbp3']+ $infos['draw_nbp4']);
		if (isset( $res['errMsg'])) echo $res['errMsg'];

		/* Delete additionnal rounds */
		$last = $firstGroup + $infos['draw_nbp3']+ $infos['draw_nbp4'] + $infos['draw_nbp5'];
		if ($last > ord('Z'))  $rund['rund_stamp'] = 'Z' . chr($last-ord('Z')+ord('A'));
		else $rund['rund_stamp'] = chr($last);
		$fields = array('rund_id');
		$tables = array('rounds');
		$where = "rund_drawId=".$rund['rund_drawId'].
			" AND rund_type=".WBS_ROUND_GROUP.
			" AND rund_stamp >='".$rund['rund_stamp']."'".
			" AND rund_group='".$rund['rund_group']."'";
		$res = $this->_select($tables, $fields, $where);
		while ($rd = $res->fetchRow(DB_FETCHMODE_ASSOC)) $utr->delRound($rd['rund_id']);

		// Traiter le tableau KO
		$rund['rund_type'] = WBS_ROUND_MAINDRAW;
		$rund['rund_name'] = $ut->getLabel(WBS_ROUND_MAINDRAW);
		$rund['rund_stamp'] = $ut->getSmaLabel(WBS_ROUND_MAINDRAW);
		$rund['rund_entries'] = $infos['draw_nbpl'];
		$rund['rund_size'] = $infos['draw_nbpl'];
		$rund['rund_byes'] = 0;
		$rund['rund_qualPlace'] = $infos['draw_nbq'];
		$rund['rund_qual'] = 1;
		$rund['rund_rge']  = $infos['rund_rge'];
		$res= $this->_treatKO($rund, $tie);
		$rund['rund_id'] = $res;
		$ancestors[$rund['rund_rge']] = $rund;

		// Traiter le tableau pour la petite finale
		$rund['rund_type'] = WBS_ROUND_THIRD;
		$rund['rund_name'] = $ut->getLabel(WBS_ROUND_THIRD);
		$rund['rund_stamp'] = $ut->getSmaLabel(WBS_ROUND_THIRD);
		$rund['rund_entries'] = 0;
		if ($infos['draw_third'] == 1)	$rund['rund_entries'] = 2;
		$rund['rund_size'] = $rund['rund_entries'];
		$rund['rund_byes'] = 0;
		$rund['rund_qualPlace'] = 0;
		$rund['rund_qual'] = 1;
		$rund['rund_rge']  = $infos['rund_rge']+2;
		$res= $this->_treatKO($rund, $tie);
		if (isset( $res['errMsg'])) echo $res['errMsg'];
		// Mettre a jour le tableau des perdant des rencontres
		$tie['tie_looserdrawid'] = $res;
		$where = 'tie_roundid =' . $rund['rund_id'];
		$where .= ' AND tie_posround IN (1,2)';
		$this->_update('ties', $tie, $where);

		// Traiter les tableaux de qualif
		$rund['rund_type'] = WBS_ROUND_QUALIF;
		$rund['rund_byes'] = 0;
		$rund['rund_qualPlace'] = 0;
		$rund['rund_qual'] = 1;
		$rund['rund_rge']  = 0;

		$where = "rund_drawId=".$rund['rund_drawId'].
			" AND rund_type=".WBS_ROUND_QUALIF .
			" AND rund_group='".$rund['rund_group']."'";
		$order = 'rund_entries';
		$res = $this->_select('rounds', 'rund_id', $where, $order);
		if ($infos['draw_type'] == WBS_QUALIF)
		{
			$nbPlayer = $infos['draw_nbplq']/$infos['draw_nbq'];
			$nbMore = $infos['draw_nbq'] - ($infos['draw_nbplq']%$infos['draw_nbq']) + 1;
			for ($i = 1; $i <= $infos['draw_nbq']; $i++)
			{
				$rund['rund_name'] = $ut->getLabel(WBS_ROUND_QUALIF). " $i";
				$rund['rund_stamp'] = $ut->getSmaLabel(WBS_ROUND_QUALIF) . "$i";
				$rund['rund_entries'] = $nbPlayer;
				if ($i >= $nbMore) $rund['rund_entries']++;
				$rund['rund_size'] = $rund['rund_entries'];
				$data = $res->fetchRow(DB_FETCHMODE_ASSOC);
				if (is_null($data))	$rund['rund_id'] = -1;
				else $rund['rund_id'] = $data['rund_id'];
				$utr->updateRound($rund, $tie);
			}
		}
		// Supprimer les qualif en trop
		while ($round = $res->fetchRow(DB_FETCHMODE_ASSOC))	$utr->delRound($round['rund_id']);

		// Traiter les tableaux de plateaux
		$rund['rund_type'] = WBS_ROUND_PLATEAU;
		$rund['rund_byes'] = 0;
		$rund['rund_qualPlace'] = 0;
		$rund['rund_qual'] = 1;

		$nbPlayer = $infos['draw_nbpl'];
		if ($nbPlayer > 128) $size = 256;
		else if ($nbPlayer > 64) $size = 128;
		else if ($nbPlayer > 32) $size = 64;
		else if ($nbPlayer > 16) $size = 32;
		else if ($nbPlayer > 8) $size = 16;
		else if ($nbPlayer > 4) $size = 8;
		else if ($nbPlayer > 2) $size = 4;
		else if ($nbPlayer > 1) $size = 2;
		else $size = 0;
		$ancestor = reset($ancestors);
		$firstRge = $ancestor['rund_rge'] -1;
		if ($infos['draw_type'] == WBS_CONSOL)
		{
			$step = $size;
			$places = array(1=>array(-1),
			2=>range(1,2),
			4=>range(3,6),
			8=>range(7,14),
			16=>range(15,30),
			32=>range(31,62),
			64=>range(63,126));
			while ($step > 2)
			{
				$step /= 2;
				$isPair = 0;
				$ancestor = reset($ancestors);
				for ($i=1; $i<$size; $i+=$step)
				{
					if($isPair)
					{
						$nbPlayers = $ancestor['rund_entries'] - $step;
						if ($nbPlayers > 128) $nbSlot = 256;
						else if ($nbPlayers > 64) $nbSlot = 128;
						else if ($nbPlayers > 32) $nbSlot = 64;
						else if ($nbPlayers > 16) $nbSlot = 32;
						else if ($nbPlayers > 8) $nbSlot = 16;
						else if ($nbPlayers > 4) $nbSlot = 8;
						else if ($nbPlayers > 2) $nbSlot = 4;
						else if ($nbPlayers > 1) $nbSlot = 2;
						else if ($nbPlayers == 1) $nbSlot = 1;
						else $nbSlot = 0;
						if ($ancestor['rund_entries'] >= $step)
						{
							$ancestor['rund_entries'] = $step;
							$ancestors[$ancestor['rund_rge']] = $ancestor;
						}

						// Creer le plateau
						if ($nbSlot > 0)
						{
							$plateau = ($i+ $firstRge). ' - ' . ($i+ $firstRge+$nbPlayers-1);
							$rund['rund_name']    = $ut->getLabel(WBS_ROUND_PLATEAU) . ' ' . $plateau;
							$rund['rund_stamp']   = $plateau;
							$rund['rund_rge']     = $i + $firstRge;
							$rund['rund_entries'] = $nbPlayers;
							$rund['rund_size']    = $nbSlot;
							$roundId = $this->_treatKo($rund, $tie);
							$rund['rund_id'] = $roundId;
							$plateauIds[] = $roundId;
							// 	Mettre a jour le tableau des perdant des rencontres
							$tie['tie_looserdrawid'] = $roundId;
							//print_r($tie);
							$where = 'tie_roundid =' . $ancestor['rund_id'];
							$where .= ' AND tie_posround IN (' . implode(',', $places[$step]) . ')';
							$this->_update('ties', $tie, $where);
							$tie['tie_looserdrawid'] = -1;
							$ancestors[$i+ $firstRge] = $rund;
						}
						$ancestor = next($ancestors);
					}
					$isPair = 1 - $isPair;
				}
				ksort($ancestors);
			}
		}
		// Supprimer les plateaux en trop
		$where = "rund_drawId=" . $rund['rund_drawId'] . " AND rund_type=" . WBS_ROUND_PLATEAU . " AND rund_group='" . $rund['rund_group'] . "'";
		if (!empty($plateauIds)) $where .= " AND rund_id NOT In (" . implode(',', $plateauIds) . ')';
		$rows = $this->_getRows('rounds', 'rund_id', $where);
		foreach($rows as $row) $utr->delRound($row['rund_id']);
		return;
	}

	// {{{ _treatKO
	/**
	 * Update the KO rounds of the draw with the informations
	 *
	 * @access public
	 * @param  string  $info   column of the event
	 * @return mixed
	 */
	function _treatKO($round, $tie)
	{
		$utr = new utRound();

		// Chercher les rounds correspondants
		$where = "rund_drawId=".$round['rund_drawId'].
			" AND rund_type=".$round['rund_type'] .
			" AND rund_group='".$round['rund_group']."'";
		if($round['rund_type'] == WBS_ROUND_PLATEAU)
		$where .= 	" AND rund_name='".$round['rund_name']."'";

		$roundId = $this->_selectfirst('rounds', 'rund_id', $where);

		// Creer le nombre de round necessaires
		if (is_null($roundId)) $round['rund_id'] = -1;
		else $round['rund_id'] = $roundId;

		// Delete the round
		if (!$round['rund_entries']) $utr->delRound($round['rund_id']);
		else $round['rund_id'] = $utr->updateRound($round, $tie);

		// Supprimer les rounds en trop
		$where = "rund_drawId=".$round['rund_drawId'].
			" AND rund_type=".$round['rund_type'].
			" AND rund_id != {$round['rund_id']}".
			" AND rund_group='".$round['rund_group'] . "'";
		if($round['rund_type'] == WBS_ROUND_PLATEAU)
		$where .= 	" AND rund_name='".$round['rund_name']."'";
		$res = $this->_select('rounds', 'rund_id', $where);
		while ($tmpround = $res->fetchRow(DB_FETCHMODE_ASSOC)) $utr->delRound($tmpround['rund_id']);

		return $round['rund_id'];
	}
	// }}}


	// {{{ _treatGroups
	/**
	 * Update the groups of the draw with the informations
	 *
	 * @access public
	 * @param  string  $info   column of the event
	 * @return mixed
	 */
	function _treatGroups($round, $tie, $nbGroup, $firstGroup)
	{
		$utr = new utRound();
		$name = $round['rund_name'];
		// Traiter tous les groupes
		for ($i=0; $i<$nbGroup;$i++)
		{
			if ($firstGroup + $i > ord('Z')) $round['rund_stamp'] = 'Z' . chr($firstGroup + $i - ord('Z') + ord ('A') -1);
			else $round['rund_stamp']= chr($firstGroup + $i);
			// Search the round
			$fields = array('rund_id');
			$tables = array('rounds');
			$where = "rund_drawId=".$round['rund_drawId'].
	    		" AND rund_type=".$round['rund_type'].
	    		" AND rund_stamp='".$round['rund_stamp']."'".
	    		" AND rund_group='".$round['rund_group']."'";
			$res = $this->_select($tables, $fields, $where);
			$round['rund_id'] = -1;
			if ($res->numRows()>0)
			{
				$rund = $res->fetchRow(DB_FETCHMODE_ASSOC);
				$round['rund_id'] = $rund['rund_id'];
			}
			$round['rund_name'] = $name .' '.$round['rund_stamp'];
			$utr->updateRound($round, $tie);
		}
		return true;

	}
	// }}}

	// {{{ addSerial
	/**
	 * Add  new draws
	 *
	 * @access public
	 * @param  string  $info   column of the draws
	 * @return mixed
	 */
	function addSerial($infos)
	{
		$ut = new utils();
		$stamp = $infos['draw_stamp'];
		$infos['draw_disci']= WBS_MS;
		$infos['draw_discipline']= WBS_SINGLE;
		$infos['draw_name']= $ut->getLabel($infos['draw_disci']). ' '.$infos['draw_serialName'];
		$infos['draw_stamp']= $ut->getSmaLabel($infos['draw_disci']). " $stamp";
		if ($infos['tie_nbms'])
		{
			$res = $this->_addDraw($infos);
			//else $res = $this->_delDraw($infos);
			if (is_array($res)) return $res;
		}

		$infos['draw_disci']=WBS_WS;
		$infos['draw_discipline']= WBS_SINGLE;
		$infos['draw_name']= $ut->getLabel($infos['draw_disci']). ' '.$infos['draw_serialName'];
		$infos['draw_stamp']= $ut->getSmaLabel($infos['draw_disci']). " $stamp";
		if ($infos['tie_nbws'])
		{
			$res = $this->_addDraw($infos);
			//else $res = $this->_delDraw($infos);
			if (is_array($res)) return $res;
		}
		$infos['draw_disci']=WBS_MD;
		$infos['draw_discipline']= WBS_DOUBLE;
		$infos['draw_name']= $ut->getLabel($infos['draw_disci']). ' '.$infos['draw_serialName'];
		$infos['draw_stamp']= $ut->getSmaLabel($infos['draw_disci']). " $stamp";
		if ($infos['tie_nbmd'])
		{
			$res = $this->_addDraw($infos);
			//else $res = $this->_delDraw($infos);
			if (is_array($res)) return $res;
		}

		$infos['draw_disci']=WBS_WD;
		$infos['draw_discipline']= WBS_DOUBLE;
		$infos['draw_name']= $ut->getLabel($infos['draw_disci']). ' '.$infos['draw_serialName'];
		$infos['draw_stamp']= $ut->getSmaLabel($infos['draw_disci']). " $stamp";
		if ($infos['tie_nbwd'])
		{
			$res = $this->_addDraw($infos);
			//else $res = $this->_delDraw($infos);
			if (is_array($res)) return $res;
		}

		$infos['draw_disci']=WBS_XD;
		$infos['draw_discipline']= WBS_MIXED;
		$infos['draw_name']= $ut->getLabel($infos['draw_disci']). ' '.$infos['draw_serialName'];
		$infos['draw_stamp']= $ut->getSmaLabel($infos['draw_disci']).  " $stamp";
		if ($infos['tie_nbxd'])
		{
			$res = $this->_addDraw($infos);
			//else $res = $this->_delDraw($infos);
			if (is_array($res)) return $res;
		}
	}
	// }}}


	// {{{ isSerialExist
	/**
	 * Test if a serial already exist
	 *
	 * @access private
	 * @param  string  $name name  of the serial
	 * @return mixed
	 */
	function isSerialExist($name)
	{
		$ut = new Utils();

		// Search if the draw already exist
		$eventId = utvars::getEventId();
		$fields[] = "draw_id";
		$tables[] = "draws";
		$where = "draw_eventId=".$eventId.
        " AND draw_serial = '". addslashes($name) . "'";
		$res = $this->_select($tables, $fields, $where);

		// Return the number of rows
		return $res->numRows();
	}

	// {{{ _addDraw
	/**
	 * Add a new draw
	 *
	 * @access private
	 * @param  string  $info   column of the draws
	 * @return mixed
	 */
	function _addDraw($infos)
	{
		// Search if the draw already exist
		$fields[] = 'draw_id';
		$tables[] = 'draws';
		$where = "draw_eventId=".$infos['draw_eventId'].
	" AND draw_serial = '". addslashes($infos['draw_oldName'])."'".
	" AND draw_disci = '".$infos['draw_disci']."'";
		$res = $this->_select($tables, $fields, $where);

		// If the draw already exist, update it
		if ($res->numRows())
		{
	  $fields = array();
	  $fields['draw_del'] = WBS_DATA_UNDELETE;
	  $fields['draw_serial'] = $infos['draw_serialName'];
	  $fields['draw_catage'] = $infos['draw_catage'];
	  $fields['draw_numcatage'] = $infos['draw_numcatage'];
	  $fields['draw_rankdefId'] = $infos['draw_rankdefId'];
	  $res = $this->_update("draws", $fields, $where);
	  return true;
		}

		// Not found, so add a new draw
		$fields = array();
		$fields['draw_name'] = $infos['draw_name'];
		$fields['draw_disci'] = $infos['draw_disci'];
		$fields['draw_discipline'] = $infos['draw_discipline'];
		$fields['draw_eventId'] = $infos['draw_eventId'];
		$fields['draw_stamp'] = $infos['draw_stamp'];
		$fields['draw_serial'] = $infos['draw_serialName'];
		$fields['draw_catage'] = $infos['draw_catage'];
		$fields['draw_numcatage'] = $infos['draw_numcatage'];
		$fields['draw_rankdefId'] = $infos['draw_rankdefId'];
		$fields['draw_type'] = WBS_KO;
		$res = $this->_insert('draws', $fields);
		return true;
	}
	// }}}

	// {{{ updateSerial
	/**
	 * Update serial name, categorie and rank of draws
	 *
	 * @access private
	 * @param  string  $info   column of the draws
	 * @return mixed
	 */
	function updateSerial($infos)
	{
		$where = "draw_eventId=".utvars::getEventId().
	" AND draw_serial='". addslashes($infos['draw_oldName']) . "'";
		unset($infos['draw_oldName']);
		$res = $this->_update("draws", $infos, $where);
		return true;
	}
	// }}}

	// {{{ _delDraw
	/**
	 * Logical delete the draw
	 *
	 * @access public
	 * @param  table  $infos   Infos of the draw to delete
	 * @return mixed
	 */
	function _delDraw($infos)
	{
		$fields = array('draw_id');
		$tables = array('draws');
		$where = "draw_eventId=".$infos['draw_eventId'].
	" AND draw_serial = '". addslashes($infos['draw_oldName']) . "'".
	" AND draw_disci = '".$infos['draw_disci']."'";
		$res = $this->_select($tables, $fields, $where);

		// Delete the draw
		$utd = new utDraw();
		while ($draw = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $utd->delDraw($draw['draw_id']);
		}

		return true;
	}
	// }}}

	/**
	 * Return the plairs of a draw
	 *
	 * @access public
	 * @param  integer  $teamId  Id of the team
	 * @return array   array of users
	 */
	function getDrawPairsForPdf($drawId, $sort)
	{
		$ut = new utils();
		// Select the players of the pairs
		$eventId = utvars::getEventId();
		$fields = array('pair_id', 'regi_longName', 'pair_intRank',
		      'pair_natRank', 'pair_status', 'pair_wo',
		      'team_name', 'pair_datewo', 'regi_id', 'team_id',
		      'pair_order', 'mber_ibfnumber', 'mber_licence',
		      'team_noc', 'asso_noc', 'pair_state', 'regi_catage', 'regi_numcatage',
		      'i2p_cppp', 'i2p_classe', 'rkdf_label');
		$tables = array('pairs', 'i2p', 'registration', 'draws',
		      'members', 'teams', 'a2t', 'assocs', 'rankdef');
		$where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_type = ".WBS_PLAYER.
	" AND regi_id = i2p_regiId".
	" AND i2p_pairId = pair_id".
	" AND pair_drawId=$drawId".
	" AND draw_id=pair_drawId".
	" AND mber_id=regi_memberId".
	" AND regi_teamId=team_id".
	" AND regi_eventId=$eventId".
	" AND team_id = a2t_teamId".
	" AND asso_id = a2t_assoId".
	" AND i2p_rankdefid = rkdf_id".
	" AND mber_id >= 0";
		$order = "pair_id, mber_sexe,  regi_longName";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
			$infos['errMsg'] = "msgNoPlayers";
			return $infos;
		}

		$uti = new utimg();
		$pairId = -1;
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($pairId != $entry['pair_id'])
			{
				if ($pairId > -1)
				{
					$row['regi_longName'] = $cells['name'];
					$rows[] = $row;
					$tri[] = $row['pair_noc'];
					$rank[] = $row['pair_natRank'];
				}
				$cells['name']  = array();
				$row = $entry;
				$row['pair_status'] = $ut->getSmaLabel($entry['pair_status']);
				$row['pair_noc'] = '';
				if ($entry['pair_order']) $row['pair_status'] = sprintf("%s %02d",$row['pair_status'], $entry['pair_order']);
				$pairId = $entry['pair_id'];
			}
			$row['pair_noc'] .= $entry['team_noc']==''?$entry['asso_noc'] : $entry['team_noc'];
			$catage = $ut->getSmaLabel($entry['regi_catage']);
			if ($entry['regi_numcatage'] > 0 ) $catage .= $entry['regi_numcatage'];
			$lines = $cells['name'];

			$lines[] = array('player' =>$entry['regi_longName'],
			   'ibfNum' => $entry['mber_ibfnumber'],
			   'licence' => $entry['mber_licence'],
	  		   'catage' => $catage,
			   'noc' => $entry['team_noc']==''?$entry['asso_noc']:$entry['team_noc'],
			   'level' => $entry['rkdf_label'],
			   'rank' => $entry['i2p_classe'],
			   'points' => $entry['i2p_cppp'],
			);
			$cells['name'] = $lines;
		}
		$row['regi_longName'] = $cells['name'];
		$rows[] = $row;
		$tri[] = $row['pair_noc'];
		$rank[] = $row['pair_natRank'];
		array_multisort($tri, $rank, $rows);
		return $rows;
	}


	function getDrawType($roundId)
	{
		$ut = new utils();
		// Find the id of the round
		$fields = array('draw_disci', 'draw_name', 'draw_id', 'rund_name',
		      'rund_id', 'draw_stamp', 'rund_stamp', 'rund_group');
		$tables = array('rounds', 'draws');
		$where  = "rund_id=$roundId".
      " AND draw_id=rund_drawId ";
		$res = $this->_select($tables, $fields, $where);

		$entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$trans = array( 'draw_name', 'draw_stamp');
		$entry = $this->_getTranslate('draws', $trans, $entry['draw_id'], $entry);
		$trans = array( 'rund_name');
		$entry = $this->_getTranslate('rounds', $trans, $entry['rund_id'], $entry);
		return $entry;

	}
	// }}}

	// {{{ getRanks
	/**
	 * Retrieve the list of rankings
	 *
	 * @access public
	 * @return array   array of rankings
	 */
	function getRanks()
	{
		// Retreive all defined ranking
		$fields = array('rkdf_id', 'rkdf_label', 'rkdf_point');
		$tables = array('rankdef', 'events');
		$where = 'evnt_id='.utvars::getEventId().
	' AND  evnt_rankSystem=rkdf_system';
		$order  = "rkdf_point";
		$res = $this->_select($tables, $fields, $where, $order);
		$ranks=array();
		while ($rank = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $ranks[$rank[0]] = $rank[1];
		}
		return $ranks;
	}
	// }}}

	// {{{ getMatch
	/**
	 * Return match number
	 *
	 * @access public
	 * @return array   array of rankings
	 */
	function getMatch($data)
	{
		// Retreive all defined ranking
		$fields = array('mtch_num', 'mtch_id');
		$where = "mtch_tieId = ".$data['tie_id'];
		$res = $this->_select('matchs', $fields, $where);
		$match = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return array_merge($data, $match);

	}
	// }}}
}
?>
