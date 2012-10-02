<?php
/*****************************************************************************
!   Module     : Registrations
!   File       : $Source: /cvsroot/aotb/badnet/src/draws/base_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.30 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/05/19 09:56:24 $
******************************************************************************/

require_once "utils/utbase.php";
require_once "utils/utround.php";
require_once "utils/utscore.php";


/**
* Acces to the dababase for events
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class drawsBase_V extends utBase
{

  // {{{ getGroupRank
  /**
   * Return the table with group results
   *
   * @access public
   * @param  integer  $groupId  Id of the group
   * @return array   array of users
   */
  function getGroupRank($roundId)
    {
      // Calculer pour chaque paire les cumuls de 
      // match gagnes, set gagnes et points marques
      $fields = array('p2m_pairId', 'p2m_result', 'mtch_score');
      $tables = array('p2m', 'matchs', 'ties');
      $where = "tie_roundId ='$roundId'".
	" AND tie_id = mtch_tieId".
	" AND mtch_id = p2m_matchId".
	" AND p2m_result != ".WBS_RES_NOPLAY;
      $res = $this->_select($tables, $fields, $where);
      $pairs = array();
      $rows = array();

      $uts = new utscore();
      while ($pair = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $pairId = $pair['p2m_pairId'];
	  $uts->setScore($pair['mtch_score']);
	  if (isset($pairs[$pairId])){
	    $row = $pairs[$pairId];
	    }
	  else
	    {
 	
	      $row = array('pairId' => $pairId,
			   'matchW' => 0,
			   'matchL' => 0,
			   'gameW'  => 0,
			   'gameL'  => 0,
			   'point' => 0,
			   'pointW' => 0,
			   'pointL' => 0);
	    }

	  switch($pair['p2m_result'])
	    {
	    case WBS_RES_WINAB;
	    case WBS_RES_WINWO:
	    case WBS_RES_WIN:
	      $row['point']++;
	      $row['matchW']++;
	      $row['gameW'] += $uts->getNbWinGames();
	      $row['gameL'] += $uts->getNbLoosGames();
	      $row['pointW']+= $uts->getNbWinPoints();
	      $row['pointL']+= $uts->getNbLoosPoints();;
	      break;
	    case WBS_RES_LOOSEWO:
	      $row['point']--;
	      // No break here
	    case WBS_RES_LOOSEAB;
	    case WBS_RES_LOOSE:
	      $row['gameW'] += $uts->getNbLoosGames();
	      $row['gameL'] += $uts->getNbWinGames();
	      $row['pointW']+= $uts->getNbLoosPoints();
	      $row['pointL']+= $uts->getNbWinPoints();
	      break;
	    }
	  $pairs[$pairId] = $row;
	}

      $fields = array('t2r_pairId', 't2r_rank','rund_name');
      $tables = array('t2r', 'rounds');
      $where = "rund_id ='$roundId'".
	" AND t2r_roundId = rund_id";
      $order = 't2r_rank';
      $res = $this->_select($tables, $fields, $where, $order);
      while ($pair = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $rows['name'] = $pair['rund_name'];
	  //print_r($pair['t2r_pairId']);
	  $fields = array('regi_longName as name','team_stamp as stamp');
	  $tables = array('i2p', 'registration','teams');
	  $where = "i2p_pairId = ".$pair['t2r_pairId'].
	    " AND regi_id = i2p_regiId ".
	    " AND team_id = regi_teamId ";
	  $order = 'i2p_pairId';
	  $resNom = $this->_select($tables, $fields, $where, $order);
	  
	  $name = array();
	  while($nom = $resNom->fetchRow(DB_FETCHMODE_ASSOC))
	    {
	      $name[] = $nom['name']." (".$nom['stamp'].")" ;
	    }
	  
	  $pair['name'] = $name ;
	  if (isset($pairs[$pair['t2r_pairId']]))
	    {
	      $rows[] = array_merge($pair, $pairs[$pair['t2r_pairId']]);
	    }
	  else
	    {
	      $pair['point']  = 0;
	      $pair['matchW'] = 0;
	      $pair['matchL'] = 0;
	      $pair['gameW']  = 0;
	      $pair['gameL']  = 0;
	      $pair['pointW'] = 0;
	      $pair['pointL'] = 0;
	      $rows[] = $pair;
	    }
	}
      //print_r($rows);exit;
      return $rows;
    }
  //}}}

  // {{{ getGroup
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
		      'rkdf_label');
      $tables = array('members', 'registration', 'i2p', 'teams',
		      'a2t', 'assocs', 'pairs', 't2r', 'rounds',
		      'draws', 'ranks', 'rankdef');
      $where = "mber_id = regi_memberId".
	" AND regi_id = i2p_regiId".
	" AND regi_teamId = team_id".
	" AND team_id = a2t_teamId".
	" AND asso_id = a2t_assoId".
	" AND i2p_pairId = pair_id".
	" AND t2r_pairId = pair_id".
	" AND t2r_roundId = rund_id".
	" AND rund_id = $roundId".
	" AND rkdf_id = rank_rankdefid".
	" AND rank_disci = draw_disci".
	" AND rank_regiId = regi_id".
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
	      // New pair
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
		}
	      // Troisieme colonne : nom des joueurs
	      $pair['names'][] = array('value'=>"{$entry['regi_longName']} ({$entry['rkdf_label']})",
				       'stamp' => $entry['team_stamp'],
				       'action' => array(KAF_UPLOAD, 'resu', 
							 KID_SELECT, $entry['regi_id'])
				       );
	      
	      $lines[] = array('value' => $entry['regi_shortName']);
	      $lines['class'] = 'classGroupPair';
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
	      else 
		$cells[] = array('value' => '--');
	      $cells['class'] = 'classGroupTime';
	      $time = $cells; 	      

	      // Mettre en forme le resultats
	      $cells = array();
	      $cells['class'] = 'classGroupData';
	      if ($match['p2m_result'] == WBS_RES_NOPLAY)
		{
		  $cells[] = array('value' => $matchNum[$match['tie_posRound']],
				   'class' => 'classGroupNum');
		}
	      else if ($match['p2m_result'] >= WBS_RES_LOOSE)
		{
		  $utd->setIsoDateTime($match['mtch_begin']);
		  $duree = $utd->elaps($match['mtch_end']);
		  $uts->setScore($match['mtch_score']);
		  $score = $uts->getLoosScore();
		  $lines = array();
		  $lines[] = array('value' => $score,
				   'class' => 'classGroupScoreLoos');
		  if ($duree >0)
		    $lines[] = array('value' => "$duree mn",
				     'class' => 'classGroupScoreLoos');
		  $cells = $lines;
		}
	      else 
		{
		  $utd->setIsoDateTime($match['mtch_begin']);
		  $duree = $utd->elaps($match['mtch_end']);
		  $uts->setScore($match['mtch_score']);
		  $score = $uts->getWinScore();
		  $lines = array();
		  $lines[] = array('value' => $score,
				   'class' => 'classGroupScoreWin');
		  if ($duree >0)
		    $lines[] = array('value' => "$duree mn",
				     'class' => 'classGroupScoreLoos');
		  $cells = $lines;
		}
	      $content = $cells;

	      // Si la colonne et la ligne sont identiques,
	      // il n'y a rien a marquer (une paire ne joue 
	      // pas contre elle meme)
	      if ($numCol == $numLine)
		{
		  $cells = array();
		  $cells[] = array('value' => "---");
		  $cells['class'] = 'classGroupNone';
		  $line[$numCol]= $cells;
		}
	      // La colonne est superieure a la ligne,
	      // on affiche dans la partie haute du tableau
	      if ($numCol >= $numLine)
		$line[$numCol+1]= $content;
	      // Si le nombre de match est egal au nombre de paire -1 
	      // la poule se joue en un seul match, pas en aller/retour
	      // On affiche dans la partie basse du tableau
	      else if($nbMatchs == $nbPairs-1)
		$line[$numCol]= $content;

	      $numCol++;
	    }
	  //$line[]= $stamp[$numLine];
	  //$line[]= $flag[$numLine];
	  $cells = array();
	  $cells[] = array('value' => "---");
	  $cells['class'] = 'classGroupNone';
	  $line[$numLine]= $cells;
	  $rows[] = $line;
	}
      //print_r($rows);
      return $rows;
    }
  // }}}

  // {{{ _getMatchNum
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
  //}}}

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
	
	//print_r($where);
	
      $res = $this->_select($tables, $fields, $where);
      $entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
      return $entry['rund_id'];
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
		      'mtch_end', 'mtch_begin');
      $tables = array('p2m', 'matchs', 'ties');
      $where  = "p2m_matchId = mtch_id".
	" AND mtch_tieId = tie_id".
	" AND tie_roundId = $roundId".
	" AND (p2m_result = ".WBS_RES_WIN.
	" OR p2m_result = ".WBS_RES_WINAB.
	" OR p2m_result = ".WBS_RES_WINWO.')';
      $res = $this->_select($tables, $fields, $where);
      $pairs = array();
      while($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $pairsId[] = $entry['p2m_pairId'];
	  $pairs[] = $entry;
	}

      // Retrieve players name of the pairs
      $rows = array();
      if (isset($pairsId))
	{
	  $ids = implode(',', $pairsId);
	  $fields = array('i2p_pairId', 'regi_shortName', 
			  'asso_noc','team_noc', 'regi_noc',
			  'asso_logo', 'team_logo', 'regi_id');
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
		  if($entry['i2p_pairId'] == $pair['p2m_pairId'])
		    {
		      $name = $entry['regi_shortName'];
		      $line = array();
		      $line['name'] = $name;
		      $pair['value'][] = $line;
		      //print_r($pair);

		      if( $uts->setScore($pair['mtch_score']) != -1)
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

  // {{{ getDraws
  /**
   * Return the players of a draw
   *
   * @access public
   * @param  integer  $teamId  Id of the team
   * @return array   array of users
   */
  function getDraws($sort)
    {
      $utd = new utdraw();
      $ut  = new utils();
      $draws= $utd->getDraws($sort);
      if (isset($draws['errMsg']))
	return $draws;

      for($i=0; $i<count($draws); $i++)
	{
	  $draw = $draws[$i];
	  $order[$i] = $draw['draw_serial'];
	  $drawSpec = $utd->getDrawById($draw['draw_id']);
	  $qual = $drawSpec['draw_nbq'];
	  if ($qual == 1)
	    $qual .= $ut->getLabel('STRING_QUALIF');
	  else if ($qual > 1)
	    $qual .= $ut->getLabel('STRING_QUALIFS');

	  $groups = $drawSpec['draw_nbp3'] +
	    $drawSpec['draw_nbp4']+
	    $drawSpec['draw_nbp5'];
	  if ($groups > 0)
	    {
	      if ($groups == 1)
		$groups .= $ut->getLabel('STRING_GROUP');
	      else
		$groups .= $ut->getLabel('STRING_GROUPS');
	      $draw['Groups'] = array();
	      $draw['Groups'][] = array('value'=>$groups);
	      if ($drawSpec['draw_nbq'])
		$draw['Groups'][] = array('value'=> $qual);
	    }
	  else
	    $draw['Groups'] = '';
	  $qualif = $drawSpec['draw_nbplq'];
	  if ($qualif > 0)
	    {
	      if ($qualif == 1)
		$qualif .= $ut->getLabel('STRING_PLACE');
	      else
		$qualif .= $ut->getLabel('STRING_PLACES');
	      $draw['Qualif'] = array();
	      $draw['Qualif'][] = array('value'=>$qualif);
	      if ($drawSpec['draw_nbq'])
		$draw['Qualif'][] = array('value'=>$qual);
	    }
	  else
	    $draw['Qualif'] = '';
	  $draw['Main'] = $drawSpec['draw_nbpl'];
	  if ($draw['Main']>0)
	    {
	      if ($draw['Main'] == 1)
		$draw['Main'] .= $ut->getLabel('STRING_PLACE');
	      else
		$draw['Main'] .= $ut->getLabel('STRING_PLACES');
	    }
	  else
	    $draw['Main'] = '';
	  $draws[$i] = $draw;
	}

      array_multisort($order, $draws);
      return $draws;
    }
  //}}}

  // {{{ getDrawPairs
  /**
   * Return the players of a draw
   *
   * @access public
   * @param  integer  $teamId  Id of the team
   * @return array   array of users
   */
  function getDrawPairs($drawId, $sort)
    {
      $utd = new utdraw();
      $draw = $utd->getDrawById($drawId);
      $disci = $draw['draw_disci'];
      $ut = new utils();
      // Select all the pairs of the draw
      $fields = array('pair_id', 'pair_status', 'pair_order', 'pair_wo', 't2r_tds','pair_cmt');
      $tables = array('pairs LEFT JOIN t2r ON t2r_pairId = pair_id');
      $where  = "pair_drawId=$drawId";
      //$order = "pair_id, t2r_tds DESC ";
      $order = "pair_status, pair_order";
      
      $res = $this->_select($tables, $fields, $where, $order, 'pair');
      // For each pair, calculate his new position according WO
      $nbPTM = 0;
      $nbPTQ = 0;
      $nbWo = 0;
      while($pair = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  if ($pair['t2r_tds'] == '')
	    $tds[$pair['pair_id']] = '';
	  else
	    $tds[$pair['pair_id']] = $ut->getLabel($pair['t2r_tds']);
	  if ($pair['pair_wo'])
	    $evolution[$pair['pair_id']] = $ut->getSmaLabel(WBS_PAIR_WDN);
	  else
	    $evolution[$pair['pair_id']] = '';
	  if (($pair['pair_status'] == WBS_PAIR_MAINDRAW)&&
	      $pair['pair_wo']) 
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
		    $evolution[$pair['pair_id']] = 
		      sprintf("%s %02d", $ut->getSmaLabel(WBS_PAIR_QUALIF),
			      $pair['pair_order']-$nbWo);
		}
	    }	  
	  if ($pair['pair_status'] == WBS_PAIR_RESERVE)
	    {	      
	      if ($pair['pair_wo'])
		$evolution[$pair['pair_id']] = $ut->getSmaLabel(WBS_PAIR_WDN);
	      else
		{
		  if ($nbPTQ)
		    {
		      if ($draw['draw_type'] == WBS_QUALIF)			
			$evolution[$pair['pair_id']] = 
			  $ut->getSmaLabel(WBS_PAIR_PTQ);
		      else
			$evolution[$pair['pair_id']] = 
			  $ut->getSmaLabel(WBS_PAIR_PTM);
		      $nbPTQ--;
		    }
		  else if ($nbWo)
		    $evolution[$pair['pair_id']] = 
		      sprintf("%s %02d", $ut->getSmaLabel(WBS_PAIR_RESERVE),
			      $pair['pair_order']-$nbWo);
		}
	    }	  
	}
      // Select the players of the pairs
      $eventId = utvars::getEventId();

      $fields = array('pair_id', 'regi_longName', 'rkdf_label',
		      'i2p_cppp', 'pair_intRank', 'pair_natRank', 'pair_status', 
		      'pair_wo', 'null as tds',
		      'pair_cmt', 'asso_pseudo', 'regi_id', 'asso_id', 
		      'pair_order', 'team_logo', 'asso_logo');
      $tables = array('pairs', 'i2p', 'registration', 'draws',
		      'members', 'teams', 'a2t', 'assocs',
		      'rankdef');
      $where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_type = ".WBS_PLAYER.
	" AND regi_id = i2p_regiId".
	" AND i2p_pairId = pair_id".
	" AND pair_drawId=$drawId".
	" AND draw_id=pair_drawId".
	" AND mber_id=regi_memberId".
	" AND regi_teamId=team_id".
	" AND asso_id=a2t_assoId".
	" AND a2t_teamId=team_id".
	" AND mber_id >= 0".
	" AND i2p_rankdefId = rkdf_id";

      $order = "pair_id, mber_sexe, regi_longName";
      $res = $this->_select($tables, $fields, $where, $order, 'pair');
      if (!$res->numRows()) 
	{
	  $infos['errMsg'] = "msgNoPlayers";
	  return $infos;
	}

      $uti = new utimg();
      $utd = new utdate();
      // Construction des paires: fusion des lignes d'une meme paire
      $pairId = -1;
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  if ($pairId != $entry['pair_id'])
	    {
	      if ($pairId > -1)
		{
		  $teamSort[] = $row['asso_pseudo'];
		  $row['regi_longName'] = $cells['name'];
		  $row['asso_pseudo'] = $cells['team'];
		  $row['asso_id'] = $assoId;
		  $row['rkdf_label'] = $cells['level'];
		  $row['i2p_cppp'] = $cells['average'];
		  $rows[] = $row;
		}
	      $cells['name']  = array();
	      $cells['team']  = array();
	      $cells['level']  = array();
	      $cells['average']  = array();
	      $row = $entry;
	      if ($row['pair_wo']) $class = "classWo";
	      else $class = "";
	      $row['pair_wo'] = $evolution[$row['pair_id']];
	      $row['pair_status'] = $ut->getSmaLabel($entry['pair_status']);
	      if ((($entry['pair_status'] == WBS_PAIR_RESERVE) ||
		   ($entry['pair_status'] == WBS_PAIR_QUALIF))&& 
		  ($entry['pair_order']>0))
		$row['pair_status'] = sprintf("%s %02d",$row['pair_status'],
					      $entry['pair_order']);
	      $row['tds'] = $tds[$row['pair_id']];
	      if($entry['pair_intRank'] <= 0)
		$row['pair_intRank'] = '';
	      if($entry['pair_natRank'] <= 0)
		$row['pair_natRank'] = '';
	      $pairId = $entry['pair_id'];
	      $assoId = $entry['asso_id'];
	      $tmp = array_values($row);
	      $tri[] = $tmp[abs($sort)-1];
	    }
	  if( $assoId != $entry['asso_id'])
	    $assoId .= '-'.$entry['asso_id'];
	  $lines = $cells['name'];
	  if ($entry['team_logo']!= '')
	    $logo = utimg::getPathTeamLogo($entry['team_logo']);
	  else if ($entry['asso_logo']!= '')
	    $logo = utimg::getPathFlag($entry['asso_logo']);
	  else
	    $logo = '';
	  $lines[] = array('value' =>$entry['regi_longName']
			   ,'logo' => $logo
			   ,'class' => $class
			   ,'action' => array(KAF_UPLOAD, 'resu', 
					      KID_SELECT, $entry['regi_id'])
			   );
	  $cells['name'] = $lines;
	  $lines = $cells['team'];
	  $lines[] = array('value' =>$entry['asso_pseudo'],
			   'class' => $class);
	  $cells['team'] = $lines;
	  $lines = $cells['level'];
	  $lines[] = array('value' =>$entry['rkdf_label']
			   ,'class' => $class);
	  $cells['level'] = $lines;
	  
	  $lines = $cells['average'];
	  $lines[] = array('value' =>$entry['i2p_cppp']
			   ,'class' => $class);
	  $cells['average'] = $lines;
        }
      $teamSort[] = $row['asso_pseudo'];
      $row['regi_longName'] = $cells['name'];
      $row['asso_pseudo'] = $cells['team'];
      $row['asso_id']   = $assoId;
      $row['rkdf_label'] = $cells['level'];
      $row['i2p_cppp'] = $cells['average'];
      $rows[] = $row;
      if ($sort > 0)
	array_multisort($teamSort, $tri, $rows);
      else
	array_multisort($teamSort, $tri, SORT_DESC, $rows);
      return $rows;
    }
  // }}}


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
      $fields = array('t2r_pairId', 't2r_tds', 't2r_status', 't2r_posRound');
      $tables = array('t2r');
      $where  = "t2r_roundId = $roundId";
      $order = 't2r_posRound';
      $res = $this->_select($tables, $fields, $where, $order);
      $pairs = array();
      while($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $pairsId[] = $entry['t2r_pairId'];
	  if ($entry['t2r_tds'] != -1)
	    $entry['seed'] = $ut->getLabel($entry['t2r_tds']);
	  if (($entry['t2r_status'] > WBS_PAIR_RESERVE &&
	       $entry['t2r_status'] != WBS_PAIR_NONE) ||
	      ($entry['t2r_status'] != WBS_PAIR_DNS) &&
	      $entry['t2r_status'])
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
                    'asso_id','team_id','mber_ibfnumber');
	  $tables = array('members', 'registration', 'i2p', 'teams',
			  'a2t', 'assocs');
	  $where = "mber_id = regi_memberId".
	    " AND regi_id = i2p_regiId".
	    " AND regi_teamId = team_id".
	    " AND team_id = a2t_teamId".
	    " AND asso_id = a2t_assoId".
	    " AND i2p_pairId IN ($ids)";

	  $order = "i2p_pairId, mber_sexe,  regi_longName";
	  $res = $this->_select($tables, $fields, $where, $order);
	  while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	    {
	      $pair = $pairs[$entry['i2p_pairId']];
	      $name = $entry['regi_longName'];
	      if ($entry['regi_noc'] != '')
		$name .= ' ('.$entry['regi_noc'].')';
	      else if ($entry['team_noc'] != '')
		$name .= ' ('.$entry['team_noc'].')';
	      else
		$name .= ' ('.$entry['asso_noc'].')';

	      if ($entry['team_logo'] != '')
		$logo = utimg::getPathTeamLogo($entry['team_logo']);
	      else if ($entry['asso_logo'] != '')
		$logo = utimg::getPathFlag($entry['asso_logo']);
	      else
		$logo = '';

	      $line = array();
	      $line['logo'] = $logo;
	      $line['name'] = $name;
	      $line['ibfn'] = $entry['mber_ibfnumber'];
	      $line['action'] = array(KAF_UPLOAD, 'resu', 
				      KID_SELECT, $entry['regi_id']);
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
      $res = $this->_select($tables, $fields, $where, false, 'rund');
      $entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
      return $entry['rund_id'];
    }
  // }}}


  function getDrawType($roundId)
  {
      $ut = new utils();
      // Find the id of the round
      $fields = array('draw_disci', 'draw_name', 'draw_id', 'rund_name', 'rund_id');
      $tables = array('rounds', 'draws');
      $where  = "rund_id=$roundId".
      " AND draw_id=rund_drawId ";
      $res = $this->_select($tables, $fields, $where);
      $entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $trans = array( 'draw_name');
      $entry = $this->_getTranslate('draws', $trans, $entry['draw_id'], $entry);
      $trans = array( 'rund_name');
      $entry = $this->_getTranslate('rounds', $trans, $entry['rund_id'], $entry);
      //print_r($entry);
      return $entry;
    
  }

}
?>
