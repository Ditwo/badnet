<?php
/*****************************************************************************
!   Module     : pdf
!   File       : $File$
!   Version    : $Name:  $
!   Revision   : $Revision: 1.20 $
!   Author     : D.BEUVELOT
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
******************************************************************************/
require_once "utils/utbase.php";
require_once "utils/utscore.php";

class baseFpdf extends utBase
{

  // {{{ getRoundTies
  /**
   * Return the table with tie's data of a round
   *
   * @access public
   * @param  integer  $roundId  Id of the round
   * @return array   array of users
   */
  function getRoundTies($roundId, $all=1)
    {
      // Get the ties of the round
      $utr = new utRound();
      $ut = new utils();
      $ties = $utr->getTies($roundId, false);

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
	      if ($all || ($tie['tie_schedule'] ==''))
		{
		  $step = $utr->getTieStep($tie['tie_posRound']);
		  $tie['tie_step'] = $ut->getLabel($step);
		  $rows[] = $tie;
		}
	    }
	}
      else
	{
	  $rows = $ties;
	}
      return $rows;
    }
  // }}}

  // {{{ getKoTies
  /**
   * Return the ties of a KO draw
   *
   * @access public
   * @param  integer  $tieId  id of the tie
   * @return array   information of the matchs if any
   */
  function getKoTies($roundId)
    {
      $ut = new utils();
      // Select the matchs of the tie
      $fields = array('tie_id', 'tie_posRound', 'tie_schedule', 'tie_place' );
      $tables = array('ties');
      $where = "tie_roundId = $roundId";

      $res = $this->_select($tables, $fields, $where);

      // Construc a table with the ties
      $ties = array();
      $utd = new utDate();
      while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $data['score'] = '0-0';
	  $utd->setIsoDateTime($data['tie_schedule']);
	  $data['tie_schedule'] = $utd->getDateTime();
	  $ties[$data['tie_posRound']] = $data;
	}

      return $ties;

    }
  // }}}

  // {{{ getWinners
  /**
   * Return the winners teams of the ties
   *
   * @access public
   * @param  integer  $roundId  id of the round
   * @return array   information of the matchs if any
   */
  function getWinners($roundId)
    {
      $ut = new utils();
      // Select the matchs of the tie
      $fields = array('team_name',
		      'CONCAT_WS("-", t2t_scoreW, t2t_scoreL) as score',
		      'tie_id', 'tie_posRound', 'asso_logo', 't2t_result',
		      'team_logo', 'team_noc');
      $tables = array('teams', 't2t', 'ties', 'assocs', 'a2t');
      $where = "team_id=t2t_teamId".
	" AND t2t_tieId = tie_id".
	" AND a2t_teamId = team_id".
	" AND a2t_assoId = asso_id".
	" AND tie_roundId = $roundId".
	" AND (t2t_result =".WBS_TIE_WIN.
	" OR t2t_result =".WBS_TIE_WINWO.
	" OR t2t_result =".WBS_TIE_STEP.")";
      $res = $this->_select($tables, $fields, $where);

      // Construc a table with the teams
      $winners = array();
      while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  if ($data['t2t_result']==WBS_TIE_STEP)
	    $data['score'] = '';
	  if ($data['team_logo'] != '')
	    $data['asso_logo'] = $data['team_logo'];
	  unset($data['team_logo']);

	  $winners[$data['tie_posRound']] = $data;
	}

      return $winners;

    }
  // }}}


  // {{{ getMatchs
  /**
   * Return the match of a tie
   *
   * @access public
   * @param  integer  $tieId  id of the tie
   * @return array   information of the matchs if any
   */
  function getMatchs($tieId, $teamL, $teamR)
    {
      // Get the point of the result of a match
      $fields  = array('DISTINCT rund_matchWin', 'rund_matchLoose',
		       'rund_matchWO');
      $tables  = array('rounds', 'ties');
      $where = "tie_id = $tieId".
	" AND tie_roundId = rund_id";
      $res = $this->_select($tables, $fields, $where);
      $points = $res->fetchRow(DB_FETCHMODE_ASSOC);

      // Select the matchs of the tie
      $fields = array('mtch_id', 'mtch_discipline', 'mtch_begin',
		      'mtch_end', 'mtch_score', 'mtch_order', 'mtch_status');
      $tables = array('matchs');
      $where = "mtch_del != ".WBS_DATA_DELETE.
	" AND mtch_tieId = $tieId";
      $order = "2,6";
      $res = $this->_select($tables, $fields, $where, $order);

      // Construc a table with the match
      $ut = new utils();
      while ($match = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $match[1] = $ut->getLabel($match[1])." ".$match[5];
	  $match[2] = '';
	  $match[3] = '';
	  $match[5] = '';
	  $full = array_pad($match,16, "");
	  $full[13] = utimg::getIcon($match[6]);
	  $matches[$match[0]] = $full;
	}

      // Select the players of the matchs of the tie
      $fields = array('mtch_id', 'p2m_result', 'regi_longName',
		      'regi_teamId', 'i2p_pairId', 'rkdf_label');
      $tables = array('matchs', 'p2m', 'i2p', 'registration', 'ranks', 'rankdef');
      $where = "mtch_del != ".WBS_DATA_DELETE.
	" AND mtch_tieId = $tieId".
	" AND mtch_id = p2m_matchId".
	" AND p2m_pairId = i2p_pairId".
	" AND i2p_regiId = regi_id".
	" AND rank_regiId = regi_id".
	" AND rank_rankdefId = rkdf_id".
	" AND rank_disci = mtch_discipline";
      $order = "1, regi_teamId";
      $res = $this->_select($tables, $fields, $where, $order);

      // Construc a table with the match
      $uts = new utscore();
      $teamId=-1;
      $penalties = array(0=>0,"","","","Bonus/Malus",'0','0','','','','','','','','');
      $totals = array(0=>0,"","","","Total",'0','0','0','0','0','0','','','','');
      while ($player = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $match = $matches[$player[0]];
	  $uts->setScore($match[4]);
	  if ($player[3] == $teamL)
	    {
	      if ($teamId != $player[3])
		{
		  $teamId = $player[3];
		  $match[2] = "{$player[2]} - {$player[5]}";
		  $match[11] = $player[4];
		  $match[14] = utimg::getIcon($player[1]);
		  switch ($player[1])
		    {
		    case WBS_RES_WIN:
		    case WBS_RES_WINAB:
		      $match[5] = $points['rund_matchWin'];
		      $match[6] = $points['rund_matchLoose'];
		      $match[7] = $uts->getNbWinGames();
		      $match[8] = $uts->getNbLoosGames();
		      $match[9] = $uts->getNbWinPoints();
		      $match[10] = $uts->getNbLoospoints();
		      break;
		    case WBS_RES_LOOSE:
		    case WBS_RES_LOOSEAB:
		      $match[6] = $points['rund_matchWin'];
		      $match[5] = $points['rund_matchLoose'];
		      $match[7] = $uts->getNbLoosGames();
		      $match[8] = $uts->getNbWinGames();
		      $match[9] = $uts->getNbLoosPoints();
		      $match[10] = $uts->getNbWinPoints();
		      $match[4] = $uts->getLoosScore();
		      break;
		    case WBS_RES_WINWO:
		      $match[5] = $points['rund_matchWin'];
		      $match[6] = $points['rund_matchWO'];
		      $match[7] = $uts->getNbWinGames();
		      $match[8] = $uts->getNbLoosGames();
		      $match[9] = $uts->getNbWinPoints();
		      $match[10] = $uts->getNbLoospoints();
		      break;
		    case WBS_RES_LOOSEWO:
		      $match[6] = $points['rund_matchWin'];
		      $match[5] = $points['rund_matchWO'];
		      $match[7] = $uts->getNbLoosGames();
		      $match[8] = $uts->getNbWinGames();
		      $match[9] = $uts->getNbLoosPoints();
		      $match[10] = $uts->getNbWinPoints();
		      $match[4] = $uts->getLoosScore();
		      break;
		    default:
		      $match[5] = '0';
		      $match[6] = '0';
		      $match[7] = '0';
		      $match[8] = '0';
		      $match[9] = '0';
		      $match[10] = '0';
		      break;
		    }
		  $totals[5]+= $match[5];
		  $totals[6]+= $match[6];
		  $totals[7]+= $match[7];
		  $totals[8]+= $match[8];
		  $totals[9]+= $match[9];
		  $totals[10]+= $match[10];
		}
	      else
		{
		  $match[2] .= "<br />{$player[2]} - {$player[5]}";
		}
	    }
	  else
	    {
	      $teamId=-1;
	      $match[3] .= "{$player[2]} - {$player[5]}<br />";
	      $match[12] = $player[4];
	      $match[15] = utimg::getIcon($player[1]);
	    }
	  $matches[$player[0]] = $match;
	}

      // Calculate total of each column
      foreach ($matches as $matchId=>$match)
	{
	  $matchsList[] = $match;
	}

      // Update penalties
      $fields = array('t2t_penalties');
      $tables = array('t2t');
      $where = "t2t_tieId = $tieId".
	" AND t2t_teamId = $teamL";
      $res = $this->_select($tables, $fields, $where);
      $peno = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $penalties[5] = $peno[0];
      $totals[5] += $peno[0];

      $where = "t2t_tieId = $tieId".
	" AND t2t_teamId = $teamR";
      $res = $this->_select($tables, $fields, $where);
      $peno = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $penalties[6] = $peno[0];
      $totals[6] += $peno[0];
      $totals[11] = $totals[12] = KAF_NONE;
      $penalties[11] = $penalties[12] = KAF_NONE;
      $matchsList[] = $penalties;
      $matchsList[] = $totals;

      return $matchsList;

    }
  // }}}

  // {{{ getMatches
  /**
   * Return the match of a tie for PDF
   *
   * @access public
   * @param  integer  $tieId  id of the tie
   * @return array   information of the matchs if any
   */
  function getMatches($tieId, $teamL, $teamR)
    {

    $ut = new Utils();

    // Select the definition of the group
    $fields = array('tie_nbms', 'tie_nbws', 'tie_nbmd',
		    'tie_nbwd', 'tie_nbxd', 'tie_nbas', 'tie_nbad');
    $tables[] = 'ties';
    $where = "tie_id=$tieId";
    $res = $this->_select($tables, $fields, $where);
    $data = $res->fetchRow(DB_FETCHMODE_ORDERED);
    $defTie[WBS_MS] = $data[0];
    $defTie[WBS_LS] = $data[1];
    $defTie[WBS_MD] = $data[2];
    $defTie[WBS_LD] = $data[3];
    $defTie[WBS_MX] = $data[4];
    $defTie[WBS_AS] = $data[5];
    $defTie[WBS_AD] = $data[6];
    

      $fields = array("COUNT(*)");
      $tables = array("matchs");
      $where  = "mtch_del != ".WBS_DATA_DELETE." AND mtch_tieId = $tieId";

      $res = $this->_select($tables,$fields,$where);

      $match = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $n_matches = $match[0];

      // Get the point of the result of a match
      $fields  = array('DISTINCT rund_matchWin', 'rund_matchLoose',
		       'rund_matchWO', 'rund_matchrtd');
      $tables  = array('rounds', 'ties');
      $where = "tie_id = $tieId".
	" AND tie_roundId = rund_id";
      $res = $this->_select($tables, $fields, $where);
      $points = $res->fetchRow(DB_FETCHMODE_ASSOC);


      // Select the matchs of the tie
      $fields = array("mtch_id","mtch_discipline","mtch_begin","mtch_end",
		      "mtch_score","mtch_order","mtch_status", "mtch_rank",
		      'tie_court', 'mtch_court');
      $tables = array("matchs", 'ties');
      $where  = "mtch_del != ".WBS_DATA_DELETE." AND mtch_tieId = $tieId".
	" AND tie_id = mtch_tieId";
      $order  = "mtch_rank";
      $res = $this->_select($tables,$fields,$where,$order);
      $utd = new utDate();
      // Construc a table with the match
      while ($match = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  //$match[0] = $this->getLabel($match[1])." ".$match[5];
	  $discipline = $match[1];
	  if ($discipline < 6) $label_match = $ut->getSmaLabel($discipline);
	  else $label_match = 'SP';
	  if ($defTie[$discipline]>1)  $label_match .= " ".$match[5];
	    
	  $match[1] = $n_matches." @@ ".$label_match;
	  $utd->setIsoDateTime($match[2]);
	  $duree = $utd->getDiff($match[3]);
	  //$duree = '';
	  $match[2] = '';
	  $match[3] = '';
	  $match[5] = '';
	  $full = array_pad($match,16, "");
	  $full[16] = $label_match;
	  $full[13] = utimg::getIcon($match[6]);
	  $full[6] = '';
	  $full[6] = '';
	  $full[17] = $match[7];
	  $full[18] = $match[8];
	  $full[19] = $match[9];
	  $full[20] = $duree;
	  $matches[$match[0]] = $full;

	  // on passe le label du match à la position zéro
	  //$match[0] = '';
	}

      // Select the players of the matchs of the tie
      $fields = array("mtch_id","p2m_result","regi_longName","regi_teamId",
		      "i2p_pairId","rkdf_label", 'regi_catage', 'regi_numcatage', 'regi_surclasse');
      $tables = array("matchs","p2m","i2p","registration","rankdef", //"ranks",
		      "members");
      $where  = "mtch_del != ".WBS_DATA_DELETE;
      $where .= " AND mtch_tieId = $tieId";
      $where .= " AND mtch_id = p2m_matchId";
      $where .= " AND p2m_pairId = i2p_pairId";
      $where .= " AND i2p_regiId = regi_id";
	  $where .= " AND i2p_rankdefid = rkdf_id";
      //$where .= " AND rank_regiId = regi_id";
      //$where .= " AND rank_rankdefId = rkdf_id";
      $where .= " AND  regi_memberId = mber_id ";
      //$where .= " AND rank_discipline = mtch_disci";
      $order  = "1, regi_teamId, mber_sexe,mber_secondname,mber_firstname";
      $res = $this->_select($tables,$fields,$where,$order);

      // Construc a table with the match
      $uts = new utscore();
      $teamId=-1;
      $totals = array(0=>-1,"","","","Total",'0','0','0','0','0','0','','','','');
      while ($player = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $match = $matches[$player[0]];
	  $uts->setScore($match[4]);
	  if ($player[3] == $teamL)
	    {
	      if ($teamId != $player[3])
		{
		  $teamId = $player[3];
		  $match[2] = "{$player[2]} - {$player[5]} - " . $ut->getSmaLabel($player[6]);
		  if ($player[7] ) $match[2] .= $player[7];
		  if ($player[8] != 350) $match[2] .= ' (' . $ut->getSmaLabel($player[8]) . ')';
		  $match[2] .= "@@";
		  $match[11] = $player[4];
		  $match[14] = utimg::getIcon($player[1]);
		  switch ($player[1])
		    {
		    case WBS_RES_WIN:
		      $match[5] = $points['rund_matchWin'];
		      $match[6] = $points['rund_matchLoose'];
		      $match[7] = $uts->getNbWinGames();
		      $match[8] = $uts->getNbLoosGames();
		      $match[9] = $uts->getNbWinPoints();
		      $match[10] = $uts->getNbLoospoints();
		      break;
		    	case WBS_RES_WINAB:
		      $match[5] = $points['rund_matchWin'];
		      $match[6] = $points['rund_matchrtd'];
		      $match[7] = $uts->getNbWinGames();
		      $match[8] = $uts->getNbLoosGames();
		      $match[9] = $uts->getNbWinPoints();
		      $match[10] = $uts->getNbLoospoints();
		      break;
		    case WBS_RES_LOOSE:
		      $match[5] = $points['rund_matchLoose'];
		      $match[6] = $points['rund_matchWin'];
		      $match[7] = $uts->getNbLoosGames();
		      $match[8] = $uts->getNbWinGames();
		      $match[9] = $uts->getNbLoosPoints();
		      $match[10] = $uts->getNbWinPoints();
		      $match[4] = $uts->getLoosScore();
		      break;
		    case WBS_RES_LOOSEAB:
		      $match[5] = $points['rund_matchrtd'];
		      $match[6] = $points['rund_matchWin'];
		      $match[7] = $uts->getNbLoosGames();
		      $match[8] = $uts->getNbWinGames();
		      $match[9] = $uts->getNbLoosPoints();
		      $match[10] = $uts->getNbWinPoints();
		      $match[4] = $uts->getLoosScore();
		      break;
		    case WBS_RES_WINWO:
		      $match[5] = $points['rund_matchWin'];
		      $match[6] = $points['rund_matchWO'];
		      $match[7] = $uts->getNbWinGames();
		      $match[8] = $uts->getNbLoosGames();
		      $match[9] = $uts->getNbWinPoints();
		      $match[10] = $uts->getNbLoospoints();
		      break;
		    case WBS_RES_LOOSEWO:
		      $match[5] = $points['rund_matchWO'];
		      $match[6] = $points['rund_matchWin'];
		      $match[7] = $uts->getNbLoosGames();
		      $match[8] = $uts->getNbWinGames();
		      $match[9] = $uts->getNbLoosPoints();
		      $match[10] = $uts->getNbWinPoints();
		      $match[4] = $uts->getLoosScore();
		      break;
		    default:
		      $match[5] = '0';
		      $match[6] = '0';
		      $match[7] = '0';
		      $match[8] = '0';
		      $match[9] = '0';
		      $match[10] = '0';
		      break;
		    }
		  $totals[5]+= $match[5];
		  $totals[6]+= $match[6];
		  $totals[7]+= $match[7];
		  $totals[8]+= $match[8];
		  $totals[9]+= $match[9];
		  $totals[10]+= $match[10];
		}
	      else
		{
		  //$match[2] .= "{$player[2]} - {$player[5]} @@";
		  $match[2] .= "{$player[2]} - {$player[5]} - " . $ut->getSmaLabel($player[6]);
		  if ($player[7] ) $match[2] .= $player[7];
		  if ($player[8] != 350) $match[2] .= ' (' . $ut->getSmaLabel($player[8]) . ')';
		  $match[2] .= ' @@';
		}
	    }
	  else
	    {
	      $teamId=-1;
	      //$match[3] .= "{$player[2]} - {$player[5]} @@";
		  $match[3] .= "{$player[2]} - {$player[5]} - " . $ut->getSmaLabel($player[6]);
		  if ($player[7] ) $match[2] .= $player[7];
		  if ($player[8] != 350) $match[2] .= ' (' . $ut->getSmaLabel($player[8]) . ')';
		  $match[3] .= ' @@';
		  
		  $match[12] = $player[4];
	      $match[15] = utimg::getIcon($player[1]);
	    }
	  $matches[$player[0]] = $match;
	}

      // Calculate total of each column
      foreach ($matches as $matchId=>$match)
	{
	  $matchsList[] = $match;
	}
      $totals[11] = $totals[12] = KAF_NONE;
      $matchsList[] = $totals;

      return $matchsList;

    }
  // }}}
/**/

  // {{{ getTieDate
  /**
   * Return the date of a tie
   *
   * @access public
   * @param  integer  $tieId  id of the tie
   * @return array   information of the matchs if any
   */
  function getTieDate($tieId)
    {
      $tieDate = new utDate();
      // Select the teams of the tie
		$fields = array('tie_schedule');
		$tables = array("ties");
		$where  = "tie_id = $tieId ";
		$order  = " 1 ";
		$res = $this->_select($tables,$fields,$where,$order);

    $resD = $res->fetchRow(DB_FETCHMODE_ORDERED);
    $tieDate->setIsoDateTime($resD[0]);

      return $tieDate;
    }
  // }}}

  // {{{ getTeams
  /**
   * Return the teams of a tie
   *
   * @access public
   * @param  integer  $tieId  id of the tie
   * @return array   information of the matchs if any
   */
  function getTeams($tieId)
    {
      // Select the teams of the tie
      $fields = array("team_id","team_name","t2t_penalties","team_stamp");
      $tables = array("t2t","teams");
      $where  = "t2t_teamId = team_id AND t2t_tieId = $tieId ";
      $order  = "t2t_posTie";
      $res = $this->_select($tables,$fields,$where,$order);
      $teams =array();
      while ($team = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $teams[]=$team;
	}
      return $teams;
    }
  // }}}

  /**
   * Return the players of a team
   *
   * @access public
   * @param  integer  $tieId  id of the tie
   * @return array   information of the matchs if any
   */
  function getPlayers($aTeamId)
    {
		// Retrieve registered players
		$eventId = utvars::getEventId();
		$fields = array('regi_id', 'regi_date', 'mber_id', 'mber_sexe',
		      'regi_longName', 'regi_catage', 'rkdf_label', 'mber_licence', 
		      'mber_ibfnumber', 'team_name', 'team_id', 
		      'regi_memberId', 'rank_isFede', 'rank_dateFede',
		      'regi_pbl', 'regi_del', 'team_del', 'team_pbl',
		      'mber_urlphoto', 'mber_born', 'team_noc', 'asso_noc',
		      'regi_dateauto', 'regi_surclasse', 'regi_datesurclasse',
				'regi_numcatage');
		//      $tables  = array('registration', 'teams', 'ranks', 'rankdef',
		//	       'members', 'a2t', 'assocs');
		$tables  = array('registration LEFT JOIN teams ON regi_teamId = team_id
 LEFT JOIN a2t ON team_id = a2t_teamId LEFT JOIN assocs ON a2t_assoId = asso_id', 
		       'ranks', 'rankdef', 'members',);
		$where = "regi_del != ".WBS_DATA_DELETE.
		//" AND regi_teamId = team_id".
	" AND regi_eventId = $eventId".
	" AND regi_memberId >= 0".
	" AND regi_id = rank_regiId".
	" AND rank_rankdefId = rkdf_id".
	" AND regi_memberId = mber_id".
		" AND regi_present=". WBS_YES .
		" AND regi_teamid=$aTeamId";
		//" AND team_id = a2t_teamId".
		//" AND a2t_assoId = asso_id";
		$order = "mber_sexe, regi_longName, regi_id, rank_disci";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $rows['errMsg'] = 'msgNoRegisteredPlayer';
	  return $rows;
		}
		$id = -1;
		$tmp = array();
		$utd = new utdate();
		$ut = new utils();
		$uti = new utimg();
		// $tmp[10] contient le nombre de classement issu du
		// site de la fede. Si les trois classements sont issus
		// de la fede, on rajoute la source et la date apres le
		// classement
		$trans = array('team_name');
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{

			if ($id != $entry['regi_id'])
			{
				if($id > 0)
				{
					/*
					if ($tmp['rank_isFede'] == 3)
					{
						$utd->setIsoDate($tmp['rank_dateFede']);
						$tmp['rkdf_label'] .= " (FFba ".$utd->getDate().")";
					}
*/
					$tmp = $this->_getTranslate('teams', $trans,
					$tmp['team_id'], $tmp);
					$rows[] = $tmp;
				}
				$tmp = $entry;
				$tmp['mber_id'] = '';
				$tmp['mber_urlphoto'] = utimg::getPathPhoto($entry['mber_urlphoto']);

				$tmp['regi_pbl'] = $uti->getPubliIcon($entry['regi_del'],
				$entry['regi_pbl']);
				$tmp['team_pbl'] = $uti->getPubliIcon($entry['team_del'],
				$entry['team_pbl']);
				if ($tmp['regi_dateauto'] == '0000-00-00')
				$tmp['regi_dateauto'] = $uti->getIcon(WBS_NO);
				else
				$tmp['regi_dateauto'] = '';
				$utd->setIsoDate($entry['regi_date']);
				$tmp['regi_date'] = $utd->getDate();
				$utd->setIsoDate($entry['mber_born']);
				$tmp['mber_born'] = $utd->getDate();
				$tmp['mber_sexe'] = $ut->getSmaLabel($tmp['mber_sexe']);
				$tmp['regi_catage'] = $ut->getSmaLabel($tmp['regi_catage']);
				if ($tmp['regi_numcatage'])
					$tmp['regi_catage'] .= $tmp['regi_numcatage'];
				if ($tmp['regi_surclasse'] != WBS_SURCLASSE_NONE)
					$tmp['regi_catage'] .= " (".$ut->getSmaLabel($tmp['regi_surclasse']).")";
				if ($tmp['team_noc'] == '')
				$tmp['team_noc'] = $tmp['asso_noc'];


				$id = $entry['regi_id'];
			}
			else
			{
				$tmp['rkdf_label'] .= ",{$entry['rkdf_label']}";
				$tmp['rank_isFede'] += $entry['rank_isFede'];
			}
		}
		/*
		if ($tmp['rank_isFede'] == 3)
		{
	  $utd->setIsoDate($tmp['rank_dateFede']);
	  $tmp['rkdf_label'] .= " (FFba ".$utd->getDate().")";
		}
*/
		$tmp = $this->_getTranslate('teams', $trans,
		$tmp['team_id'], $tmp);
		$rows[] = $tmp;

		return $rows;
	}
    
    
  // {{{ getTie
  /**
   * Return the teams of a tie
   *
   * @access public
   * @param  integer  $tieId  id of the tie
   * @return array   information of the matchs if any
   */
  function getTie($tieId)
    {
      // Select the teams of the tie
		$fields = array('DATE(tie_schedule)','tie_place','tie_step');
		$tables = array("ties");
		$where  = " tie_id = $tieId ";
		$res = $this->_select($tables,$fields,$where);
		$tie = $res->fetchRow(DB_FETCHMODE_ORDERED);
		return $tie;
    }
  // }}}


  // {{{ getGroup
  /**
   * Return the group
   *
   * @access public
   * @param  integer  $tieId  id of the tie
   * @return array   information of the group
   */
  function getGroup($tieId)
    {
      // Retreive the data from the database
      $fields = array("draw_name", "rund_name", "rund_stamp",
		      "draw_stamp", "rund_id", "draw_id", 'tie_court',
		      'tie_posRound', 'draw_type', 'rund_type', 'rund_name');
      $tables = array("rounds","draws","ties");
      $where  = "tie_Id = $tieId";
      $where .= " AND tie_roundId = rund_id";
      $where .= " AND rund_drawId = draw_Id";

      $res = $this->_select($tables,$fields,$where);
      $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

      $fields = array('rund_name', 'rund_stamp');
      $row = $this->_getTranslate('rounds', $fields,
				  $row['rund_id'], $row);
      $fields = array('draw_name', 'draw_stamp');
      $row = $this->_getTranslate('draws', $fields,
				  $row['draw_id'], $row);
      $fields = array('tie_court');
      $row = $this->_getTranslate('ties', $fields, $tieId, $row);

      return $row;
    }
  // }}}


  // {{{ getGroup
  /**
   * Return the group
   *
   * @access public
   * @param  integer  $tieId  id of the tie
   * @return array   information of the group
   */
  function getGroupIndiv($tieId)
    {
      // Retreive the data from the database
      $fields = array("draw_name", "rund_name", "rund_stamp",
		      "draw_stamp", "rund_id", "draw_id", 'tie_court');
      $tables = array("rounds","draws","ties");
      $where  = "tie_Id = $tieId";
      $where .= " AND tie_roundId = rund_id";
      $where .= " AND rund_drawId = draw_Id";

      $res = $this->_select($tables,$fields,$where);
      $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

      $fields = array('rund_name', 'rund_stamp');
      $row = $this->_getTranslate('rounds', $fields,
				  $row['rund_id'], $row);
      $fields = array('draw_name', 'draw_stamp');
      $row = $this->_getTranslate('draws', $fields,
				  $row['draw_id'], $row);
      $fields = array('tie_court');
      $row = $this->_getTranslate('ties', $fields, $tieId, $row);

      return $row;
    }
  // }}}


  // {{{ getJoueurs
  /**
   * Return les noms des joueurs dans un tableau
   *
   *
   * @access public
   * @param  integer  $tieId  id of the tie
   * @return array   information of the group
   */
  function getJoueurs($matchs)
    {
      $ja = substr($matchs[2],0,strpos($matchs[2],'@@'));

      $restea = substr(
		       $matchs[2],
		       strpos($matchs[2],'@@')+2,
		       strlen($matchs[2]));
      //strlen($matchs[$cpt][2])-strpos($matchs[$cpt][2],'@@')-5);

      $jb = substr($restea,0,strpos($restea,'@@'));

      $jc = substr($matchs[3],0,strpos($matchs[3],'@@'));

      $restec = substr(
		       $matchs[3],
		       strpos($matchs[3],'@@')+2,
		       strlen($matchs[3]));

      $jd = substr($restec,0,strpos($restec,'@@'));


      return array(0=>$ja, 1=>$jc, 2=>$jb, 3=>$jd);
    }
  // }}}


  // {{{ getLabel()
  /**
   * return the label of a standard information
   *
   * @access public
   * @param  integer $numLabel  Position of the label
   * @return void
   */
/*  function getLabel($numLabel)
    {
      $file = "lang/".utvars::getLanguage()."/database.inc";
      require $file;

      $id = "WBS_LABEL_".$numLabel;
      if (isset(${$id}))
	return ${$id};
      else
	return $id;
    }*/
  // }}}

  // {{{ getRankGroup
  /**
   * Return the list of the teams in a group with their ranking
   *
   * @access public
   * @param  integer  $groupId  Id of the group
   * @return array   array of users
   */
  function getRankGroup($groupId)
    {
		$fields = array("t2r_rank","team_name","team_stamp",
		 "t2r_points", "t2r_tieW", "t2r_tieL",
		 "sum(t2t_matchW + t2t_penalties)",
		 " sum(t2t_matchL + t2t_penaltiesO)",
                 " sum(t2t_matchW + t2t_penalties - t2t_matchL - t2t_penaltiesO)",
		 "sum(t2t_setW)", "sum(t2t_setL)", "sum(t2t_setW - t2t_setL)",
		 "sum(t2t_pointW)", "sum(t2t_pointL)","sum(t2t_pointW - t2t_pointL)",
		 "team_id", "asso_logo", "t2r_tieE", "t2r_tieWO", "t2r_penalties", 
		 't2r_tieEP', 't2r_tieEM');
		$tables = array("teams","t2t","ties","t2r","assocs","a2t");
		$where  = "tie_roundId ='$groupId'";
		$where .= " AND team_id = t2t_teamId";
		$where .= " AND t2t_tieId = tie_id";
		$where .= " AND t2r_teamId = team_id";
		$where .= " AND t2r_roundId = '$groupId'";
		$where .= " AND a2t_assoId = asso_id ";
		$where .= " AND a2t_teamId = team_id  GROUP BY team_id";
		$order = "1,9 DESC,12 DESC,15 DESC";
		$res = $this->_select($tables,$fields,$where,$order);
      $rows = array();
      while ($team = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $team[16]= utimg::getPathFlag($team[16]);
	  $rows[] = $team;
        }
      return $rows;
    }
  // }}}


  // {{{ getInfosPlayer
  /**
   * Return the infos of a player for his badge
   *
   * @access public
   * @param  integer  $Id  regi_Id of the group
   * @return array   array of users
   */
  function getInfosPlayer($Id)
    {
		$fields = array("regi_longName","regi_memberId","regi_type",
		 "regi_teamId", "mber_firstname","mber_secondname",
		 "mber_sexe","team_name","team_stamp");
		$tables = array("members","registration","teams");
		$where  = " mber_id = regi_memberId";
		$where .= " AND regi_id = $Id  ";
		$where .= " AND team_id = regi_teamId  ";
		$order = "1";
		$res = $this->_select($tables,$fields,$where,$order);
      $rows = $res->fetchRow(DB_FETCHMODE_ORDERED);

      return $rows;
    }
  // }}}

  // {{{ getClassement
  /**
   * Return the ranking of a player
   *
   * @access public
   * @param  integer  $Id  regi_Id of the group
   * @return array   array of users
   */
  function getClassement($Id)
    {
		$fields = array("rkdf_label");
		$tables = array("ranks","rankdef");
		$where  = " rank_regiId = $Id AND ";
		$where .= " rkdf_id = rank_rankdefId ";
		$order = "rank_disci";
		$res = $this->_select($tables,$fields,$where,$order);
      $class = array();

      while($rows  = $res->fetchRow(DB_FETCHMODE_ORDERED))
          $class[] = $rows[0] ;

      return $class;
    }
  // }}}

  // {{{ getZones
  /**
   */
  function getZones($typeRegi)
    {
    	$fields = array('zone_id');
    	$tables = array('zone', 'z2r');
    	$where = 'zone_id = z2r_zoneid'.
    		' AND z2r_typeregi='.$typeRegi;
	    $res = $this->_select($tables, $fields, $where);
	    $zones = array();
	    while ($zone = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
		  $zones[] = $zone['zone_id'];
		}
    	return $zones;
    }
  //}}}
    
  // {{{ getMembers
  /**
   * Return the infos of a player for his badge
   *
   * @access public
   * @param  integer  $Id  regi_Id of the group
   * @return array   array of users
   */
  function getMembers($eventId,$ids, $aType=WBS_PLAYER)
    {
      if(is_array($ids) and count($ids))
	{
	  foreach ( $ids as $regi => $id)
	    {
	      $fields = array("regi_longName","regi_memberId","regi_type",
			      "regi_teamId", "mber_firstname",
			      "mber_secondname", "mber_sexe","team_name",
			      "team_stamp","regi_id", "asso_pseudo",
			      "asso_logo", 'regi_function', 'asso_url',
			      "mber_urlphoto", 'regi_longName', 'team_logo',
			      'regi_noc', 'team_noc', 'asso_noc');
	      $tables = array("members","registration","teams", "a2t", "assocs");
	      $where  = " mber_id = regi_memberId";
	      $where .= " AND regi_eventId = $eventId  ";
	      $where .= " AND regi_id = $id  ";
	      $where .= " AND team_id = regi_teamId ";
	      $where .= " AND mber_id >= 0 ";
	      $where .= " AND team_id = a2t_teamId";
	      $where .= " AND asso_id = a2t_assoId";
	      $order = " regi_type, team_stamp, regi_longName ";
	      $res = $this->_select($tables,$fields,$where,$order);
	      while ($player = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
		  $rows[] = $player;
		}
	    }
	}
      // badges vierge
      else
	{
	  $fields = array("regi_longName" => '',
	                  "regi_memberId" => -1,
	                  "regi_type"     => $aType,
			          "regi_teamId"   => -1, 
			          "mber_firstname" => '',
			          "mber_secondname" => '',
			  		  "mber_sexe" => WBS_MALE,
			  		  "team_name" => '',
			  		  "team_stamp" => '',
			  		  "regi_id" => -1,
			          "asso_pseudo" =>'', 
			          "asso_logo" => '', 
			          "regi_function" => '',
			          'asso_url' => '', 
			          'mber_urlphoto' => '', 
			          'team_logo' => '', 
			          'regi_noc' => '',
	                  'team_noc' => '', 
	                  'asso_noc' => '');
	  for ($i=0; $i<8; $i++)
	      $rows[] = $fields;
	}
      return $rows;
    }
  // }}}


  // {{{ getItems
  /**
   * Return the list of the items
   *
   * @access public
   * @return array   array of users
   */
  function getItems($sort=1)
    {
      $fields = array('item_id', 'item_name', 'item_code', 'item_ref',
		      'item_value', 'item_count', 'item_isFollowed',
		      'item_isCreditable', 'item_slt', 'item_rge');
      $tables = array('items');
      $where = "item_eventId = ".utvars::getEventId();
      $where .= " AND item_isCreditable =1";
      $order = 'item_rge,'.abs($sort);
      if ($sort < 0)
	$order .= ' DESC';

      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
           $infos['errMsg'] = 'msgNoItems';
 	   return $infos;
        }

      $ut = new utils();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  if ($entry['item_isFollowed'])
	    $entry['item_isFollowed'] = $ut->getLabel(WBS_YES);
	  else
	    $entry['item_isFollowed'] = $ut->getLabel(WBS_NO);
	  if ($entry['item_isCreditable'])
	    $entry['item_isCreditable'] = $ut->getLabel(WBS_YES);
	  else
	    $entry['item_isCreditable'] = $ut->getLabel(WBS_NO);
          $rows[] = $entry;
        }
      return $rows;

    }
  // }}}

  // {{{ getMatchIndiv
  /**
   * Return the information of a match
   *
   * @access public
   * @param  integer  $tieId  id of the tie
   * @return array   information of the matchs if any
   */
  function getMatchIndiv($matchId)
  {
    // Select the players of the matchs of the tie
    $fields = array('regi_longName','regi_shortName',
		    'draw_stamp as discipline', 'rund_name',
		    'tie_court','mtch_begin as begin',
		    'mtch_end as end',
		    'team_name', 'tie_schedule as estimated', 'tie_id',
		    'draw_id','asso_noc', 'team_stamp',
		    'rund_type', 'tie_posRound');
    $tables = array('p2m','i2p','registration','matchs','teams', 'ties',
		    'rounds', 'draws','members','a2t','assocs');
    $where = "p2m_del != ".WBS_DATA_DELETE.
      " AND  p2m_matchId = ".$matchId.
      " AND  mtch_id = ".$matchId.
      " AND  i2p_pairId = p2m_pairId ".
      " AND  team_id = regi_teamId ".
      " AND  mtch_tieId = tie_id ".
      " AND  tie_roundId = rund_id ".
      " AND  rund_drawId = draw_id ".
      " AND  regi_memberId = mber_id ".
      " AND a2t_teamId = team_id ".
      " AND asso_id = a2t_assoId ".
      " AND  regi_Id = i2p_regiId " ;
    $order = " p2m_id,  mber_sexe, mber_secondname, mber_firstname";
    $res = $this->_select($tables, $fields, $where, $order);
    $matchInfos = array();
    $champs = array('tie_nbms', 'tie_nbws', 'tie_nbmd',
		    'tie_nbwd', 'tie_nbxd');
    $column = array('ties');
    while($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
      {
$match['begin'] = substr($match['begin'], 11,5);
$match['end'] = substr($match['end'], 11,5);
// Select the definition of the group
	$where = "tie_id=".$match['draw_id'];
	$res2 = $this->_select($column, $champs, $where);
	$data = $res2->fetchRow(DB_FETCHMODE_ORDERED);
	$defTie[WBS_MS] = $data[0];
	$defTie[WBS_LS] = $data[1];
	$defTie[WBS_MD] = $data[2];
	$defTie[WBS_LD] = $data[3];
	$defTie[WBS_MX] = $data[4];

	if ($match['rund_type'] == WBS_ROUND_MAINDRAW ||
	    $match['rund_type'] == WBS_ROUND_QUALIF)
	  {
	    $ut = new utils();
	    $utr = new utround();
	    $step = $utr->getTieStep($match['tie_posRound'],
				     $match['rund_type']);
	    $step = $ut->getLabel($step);
	  }
	else
	  $step = $match['rund_name'];
	$match['step']  = $step;


	$fields = array('tie_court');
	$match = $this->_getTranslate('ties', $fields, $match['tie_id'], $match);
	$fields = array('draw_stamp');

	$match = $this->_getTranslate('draws', $fields, $match['draw_id'], $match);
        $matchInfos[] = $match;
      }
     return $matchInfos;
    }
  //}}}

  // {{{ getOfficials
  /**
   * Return the information of a match
   *
   * @access public
   * @param  integer  $tieId  id of the tie
   * @return array   information of the matchs if any
   */
  function getOfficials($matchId)
    {

    // Select the umpires of the matchs of the tie
    $fields = array('regi_longName','regi_shortName');
    $tables = array('registration','matchs');
    $where = "mtch_id = ".$matchId.
      " AND  regi_Id = mtch_umpireId " ;
    $res = $this->_select($tables, $fields, $where);
    if ($res->numRows())
      {
	$match = $res->fetchRow(DB_FETCHMODE_ASSOC);
	$official['umpire'] = $match['regi_longName'];
      }
    else
	$official['umpire'] = "";

    $where = "mtch_id = ".$matchId.
      " AND  regi_Id = mtch_serviceId" ;
    $res = $this->_select($tables, $fields, $where);
    if ($res->numRows())
      {
	$match = $res->fetchRow(DB_FETCHMODE_ASSOC);
	$official['service'] = $match['regi_longName'];
      }
    else
      $official['service'] = "";
    return($official);
    }
  // }}}

  // {{{ getTies
  /**
   * Return the table with tie results
   *
   * @access public
   * @param  integer  $groupId  Id of the group
   * @return array   array of users
   */
  function getTies($groupId)
    {
      // First select the teams of the group
      $fields = array('team_id', 'team_name', 'team_stamp', 'asso_logo');
      $tables = array('teams', 't2r', 'assocs','a2t');
      $where = "t2r_teamId = team_id".
	" AND t2r_roundId = '$groupId'".
	" AND a2t_assoId = asso_id ".
	" AND a2t_teamId = team_id ";
      $order = "t2r_posRound";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = "msgNoTeam";
	  return $infos;
        }

      // for each team
      $rows = array();
      $nbTeams = $res->numRows();
      while ($team = $res->fetchRow(DB_FETCHMODE_ORDERED))
      {
            $stamp[] = $team[2];
            $flag[] = utimg::getPathFlag($team[3]);
            $rows[] = $team;
      }

      $numCol2 = 0;
      for($numLine=0; $numLine<$nbTeams; $numLine++)
	{
	  $line = $rows[$numLine];
	  // Select the ties of the team
	  $fields = array('t2t_scoreW', 't2t_scoreL', 'tie_id',
			  'tie_posRound');
	  $tables = array('t2t', 'ties');
	  $where = "t2t_teamId =". $line[0].
	    " AND t2t_tieId = tie_id".
	    " AND tie_roundId = '$groupId'";
	  $order = "tie_posRound";
	  $resTie = $this->_select($tables, $fields, $where, $order);
	  $numCol=0;
	  $nbTies = $resTie->numRows();
          // Pour chaque rencontre de l'equipe
	  while (($numCol < $nbTeams-1) &&
		 ($tie = $resTie->fetchRow(DB_FETCHMODE_ORDERED)))
	    {
          // Si la colonne et la ligne sont identiques,
	      // il n'y a rien a marquer (une equipe ne joue
	      // pas contre elle meme)
	      if ($numCol == $numLine)
		{
		  $line[$numCol+2]="------";
		  $line[$numCol+2+$nbTeams]=KAF_NONE;
		}
	      // La colonne est superieure a la ligne,
	      // on affiche le resultat, avec le score de l'equipe de la
	      //ligne en premier dans la partie haute du tableau
	      if ($numCol >= $numLine)
		{
		  $line[$numCol+3]= $tie[0]."-".$tie[1];
		  $line[$numCol+3+$nbTeams]=$tie[2];
		}
	      // Si le nombre de rencontre est egal au nombre d'equipe -1
	      // la poule se joue en une seule rencontre, pas en aller/retour
	      // On affiche le score (inverse) dans la partie basse du tableau
	      else if($nbTies == $nbTeams-1)
		{
		  $line[$numCol+2] = "{$tie[0]}-{$tie[1]}";
		  $line[$numCol+2+$nbTeams]=$tie[2];
		}
	      $numCol++;
	    }
       $line[]= $stamp[$numLine];
       //$line[]= $flag[$numLine];
       $line['flag']= $flag[$numLine];
       ksort($line);
//       $line['stamp']=$line[1];
       $line['stamp']=$stamp[$numLine];
//       print_r($line);
       $rows[$numLine] = $line;
       $numLine2 = 0;
	  // S'il reste des rencontres pour la ligne traites
	  // cela se  joue en aller/retour. Le resultat s'affiche
          // dans la partie basse du tableau
	  while ($tie = $resTie->fetchRow(DB_FETCHMODE_ORDERED))
	    {
	      if ($numLine2 >= $numCol2)
		{
		  $line = $rows[$numLine2+1];
		  $line[$numCol2+2] = "{$tie[1]}-{$tie[0]}";
		  $line[$numCol2+2+$nbTeams]=$tie[2];
		  ksort($line);
		  $rows[$numLine2+1] = $line;
		}
	      $numLine2++;
	    }
	  $numCol2++;
	}

      $line = $rows[$numCol];
      $line[$numCol+2]="------";
      $line[$numCol+2+$nbTeams]=KAF_NONE;
      $line[$numCol+3+$nbTeams]=$stamp[$numLine-1];
      //$line[$numCol+4+$nbTeams]=$flag[$numLine-1];
      $line['flag']=$flag[$numLine-1];
      ksort($line);
      $line['stamp']=$stamp[$numLine-1];
      //print_r($line);echo "<br>";
      $rows[$numCol] = $line;
      return $rows;
    }
  // }}}


  // {{{ getTiesIndiv
  /**
   * Return the table with tie results
   *
   * @access public
   * @param  integer  $groupId  Id of the group
   * @return array   array of users
   */
   function getTiesIndiv($groupId)
   {
      // First select the teams of the group
      $fields = array('t2r_pairId','regi_longName', 'team_stamp', 'asso_logo');
      $tables = array('t2r','i2p','registration','teams', 'assocs','a2t');
      $where = "t2r_roundId = '$groupId'".
      " AND i2p_pairId = t2r_pairId ".
      " AND regi_id = i2p_regiId ".
      " AND team_id = regi_teamId ".
      " AND asso_id = a2t_assoId ".
      " AND a2t_teamId = team_id ";
      $order = "t2r_posRound";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
      {
         $infos['errMsg'] = "msgNoTeam";
         return $infos;
      }
      // for each team
      $rows = array();
      $nbTeams = $res->numRows();
      while ($team = $res->fetchRow(DB_FETCHMODE_ORDERED))
      {
            $stamp[] = $team[2];
            $flag[] = utimg::getPathFlag($team[3]);
            $rows[] = $team;
      }
      $numCol2 = 0;
      for($numLine=0; $numLine<$nbTeams; $numLine++)
      {
           $line = $rows[$numLine]; 	  // Select the ties of the team
//       print_r($line);
           $fields = array('mtch_score', 't2r_posRound', 'mtch_num','mtch_id');
           $tables = array('t2r', 'p2m','matchs','ties');
           $where = "t2r_pairId =". $line[0].
            	    " AND p2m_pairId =". $line[0].
                  " AND mtch_id = p2m_matchId ".
                  " AND tie_id = mtch_tieId ".
                  " AND tie_roundId = '$groupId'".
                  " GROUP BY mtch_id ";
           $order = "t2r_posRound";
           $resTie = $this->_select($tables, $fields, $where, $order);
           $numCol=0;
           $nbTies = $resTie->numRows();
           // Pour chaque rencontre de l'equipe
           while (($numCol < $nbTeams-1)
            && 		 ($tie = $resTie->  fetchRow(DB_FETCHMODE_ORDERED)))
            {
            // Si la colonne et la ligne sont identiques,
            // il n'y a rien a marquer (une equipe ne joue
            // pas contre elle meme)
               if ($numCol == $numLine)
               {
                  $line[$numCol+2]="------";
                  $line[$numCol+2+$nbTeams]=KAF_NONE;
               }
               // La colonne est superieure a la ligne,
               // on affiche le resultat, avec le score de l'equipe de la
               //ligne en premier dans la partie haute du tableau
               if ($numCol >= $numLine)
               {
                  $line[$numCol+3]= $tie[0];
                  $line[$numCol+3+$nbTeams]=$tie[2];
               }
               // Si le nombre de rencontre est egal au nombre d'equipe -1
               // la poule se joue en une seule rencontre, pas en aller/retour
               // On affiche le score (inverse) dans la partie basse du tableau
               else if($nbTies == $nbTeams-1)
               {
                    $line[$numCol+2] = $tie[0];
                    $line[$numCol+2+$nbTeams]=$tie[2];
               }
               $numCol++;
            }
       $line[]= $stamp[$numLine];
       //$line[]= $flag[$numLine];
       $line['flag']= $flag[$numLine];
       ksort($line);
//       $line['stamp']=$line[1];
       $line['stamp']=$stamp[$numLine];
//       print_r($line);
       $rows[$numLine] = $line;
       $numLine2 = 0;
       // S'il reste des rencontres pour la ligne traites
       // cela se  joue en aller/retour. Le resultat s'affiche
       // dans la partie basse du tableau
       while ($tie = $resTie->fetchRow(DB_FETCHMODE_ORDERED))
       {
             if ($numLine2 >= $numCol2)
             {
                $line = $rows[$numLine2+1];
                $line[$numCol2+2] = "{$tie[1]}-{$tie[0]}";
                $line[$numCol2+2+$nbTeams]=$tie[2];
                ksort($line);
                $rows[$numLine2+1] = $line;
             }
             $numLine2++;
       }
       $numCol2++;
   }
   $line = $rows[$numCol];
   $line[$numCol+2]="------";
   $line[$numCol+2+$nbTeams]=KAF_NONE;
   $line[$numCol+3+$nbTeams]=$stamp[$numLine-1];
   //$line[$numCol+4+$nbTeams]=$flag[$numLine-1];
   $line['flag']=$flag[$numLine-1];
   ksort($line);
   $line['stamp']=$stamp[$numLine-1];
   //print_r($line);echo "   <br>    ";
    $rows[$numCol] = $line;
    //print_r($rows);
    return $rows;
  }
  // }}}



  // {{{ getTiesSchedu
  /**
   * Return the table with tie schedule
   *
   * @access public
   * @param  integer  $groupId  Id of the group
   * @return array   array of users
   */
  function getTiesSchedu($groupId)
    {
      // First select the teams of the group
      $fields = array('team_id', 'team_logo', 'team_name', 'team_stamp',
		      'asso_logo');
      $tables = array('teams', 't2r', 'assocs','a2t');
      $where = "t2r_teamId = team_id".
	" AND t2r_roundId = '$groupId'".
	" AND a2t_assoId = asso_id ".
	" AND a2t_teamId = team_id ";
      $order = "t2r_posRound";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = "msgNoTeam";
	  return $infos;
        }

      // for each team
      $nbTeams = $res->numRows();
      while ($team = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  if ($team['team_logo'] != '')
	    $team['team_logo'] = utimg::getPathTeamLogo($team['team_logo']);
	  else
	    $team['team_logo'] = utimg::getPathFlag($team['asso_logo']);
	  unset($team['asso_logo']);
	  $teams[] = $team;
	}

      for($numLine=0; $numLine<$nbTeams; $numLine++)
	{
	  $team = $teams[$numLine];
	  // Select the ties of the team
	  $fields = array('tie_id', 'tie_posRound', 'tie_schedule',
			  'tie_place', 'tie_step');
	  $tables = array('t2t', 'ties');
	  $where = "t2t_teamId =". $team['team_id'].
	    " AND t2t_tieId = tie_id".
	    " AND tie_roundId = '$groupId'";
	  $order = "tie_posRound";
	  $resTie = $this->_select($tables, $fields, $where, $order);
	  $numCol=0;
	  $nbTies = $resTie->numRows();
	  $utd = new utDate();
          // Pour chaque rencontre de l'equipe
	  while ($tie = $resTie->fetchRow(DB_FETCHMODE_ASSOC))
	    {
	      $utd->setIsoDateTime($tie['tie_schedule']);
	      $team[] = $utd->getShortDate();
	      $team[] = $utd->getTime();
	      $team[] = $tie['tie_place'];
	      $team[] = $tie['tie_step'];
	    }
	  $teams[$numLine] = $team;

	}
      return $teams;
    }
  // }}}


  // {{{ getProgram
  /**
   * Return the table with the program of the division
   *
   * @access public
   * @param  integer  $divId  Id of the division
   * @param  integer  $date   date of the ties
   * @param  integer  $sort     Sorting column
   * @return array   array of users
   */
  function getProgram($divId, $date)
    {
    
      $utd = new utdate();
      $utd->setFrDate($date);
      // Firt select the ties of the group
      $eventId = utvars::getEventId();
      $fields = array('tie_id', 'rund_name', 'tie_step', 'tie_place',
		      "time(tie_schedule) as time",
		      'team_name', 'team_id', 't2t_matchW', 'team_id',
		      'team_stamp', 'asso_logo', 'team_logo', 'rund_id',
		      'tie_court');
      $tables = array('rounds', 'draws',
		      'ties LEFT JOIN t2t ON t2t_tieId = tie_id
		      LEFT JOIN teams ON t2t_teamId = team_id
		      LEFT JOIN a2t ON a2t_teamId = team_id
		      LEFT JOIN assocs ON a2t_assoId = asso_id',
		      //'a2t', 'assocs',
		      );
      $where = //"t2t_teamId = team_Id".
	//" AND t2t_tieId = tie_id".
	" date(tie_schedule) ='". $utd->getIsoDate() ."'".
	" AND draw_id = '$divId'".
	//" AND a2t_assoId = asso_id ".
	//" AND a2t_teamId = team_id ".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId";
      $order = "time, tie_court, tie_id, t2t_posTie";

      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = "msgNoTies";
	  return $infos;
        }

	      $tieId=-1;
      $time = "";
      $fields = array('rund_name');
      $fields2 = array('tie_place','tie_step', 'tie_court');
      while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $tie = $this->_getTranslate('rounds', $fields, $tie['rund_id'], $tie);
	  $tie = $this->_getTranslate('ties', $fields2, $tie['tie_id'], $tie);
	  if ($time != $tie['time'])
	    {
	      if ($time!="")
		{
		  $cells[] = $lines;
		  $rows[] = $cells;
		}
	      $cells = array();
	      $cells[] = $tie['time'];
	      $tieId=-1;
	      $time = $tie['time'];
	    }
	  if ($tieId != $tie['tie_id'])
	    {
	      if ($tieId>=0) $cells[] = $lines;
	      $lines = array();
	      $lines['name'] = $tie['rund_name'];
	      $lines['stamp'] = $tie['team_stamp'];
	      $lines['step'] = $tie['tie_step'];
	      $lines['court'] = $tie['tie_court'];
	      $lines['venue'] = $tie['tie_place'];
	      $tieId = $tie['tie_id'];
	    }
	  else
	    $lines['stamp'] .= " - ".$tie['team_stamp'];
	}
      $cells[] = $lines;
      $rows[] = $cells;
      return $rows;
    }
  // }}}

  // {{{ getProgram
  /**
   * Return the table with the program of the division
   *
   * @access public
   * @param  integer  $divId  Id of the division
   * @param  integer  $date   date of the ties
   * @param  integer  $sort     Sorting column
   * @return array   array of users
   */
  function getProgram2($divId, $date)
    {
    $utd = new utDate();
    $utd->setFrDate($date);
      // Firt select the ties of the group
      $eventId = utvars::getEventId();
      $fields = array('tie_id', 'rund_name', 'tie_step', 'tie_place',
		      "time(tie_schedule) as time",
		      'team_name', 'team_id', 't2t_matchW', 'team_id',
		      'team_stamp', 'asso_logo', 'team_logo', 'rund_id');
      $tables = array('rounds', 'draws',
		      'ties LEFT JOIN t2t ON t2t_tieId = tie_id
		      LEFT JOIN teams ON t2t_teamId = team_id
		      LEFT JOIN a2t ON a2t_teamId = team_id
		      LEFT JOIN assocs ON a2t_assoId = asso_id',
		      //'a2t', 'assocs',
		      );
      $where = //"t2t_teamId = team_Id".
	//" AND t2t_tieId = tie_id".
	" date(tie_schedule) ='". $utDate->getIsoDate() . "'".
	" AND draw_id = '$divId'".
	//" AND a2t_assoId = asso_id ".
	//" AND a2t_teamId = team_id ".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId";
      $order = "tie_place, time, tie_court, tie_id, t2t_posTie";

      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = "msgNoTies";
	  return $infos;
        }

      $time = "00:00:00";
      $utd = new utdate();
      $fields = array('rund_name');
      $fields2 = array('tie_place','tie_step');
      $place = "";
      while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $tie = $this->_getTranslate('rounds', $fields, $tie['rund_id'], $tie);
	  $tie = $this->_getTranslate('ties', $fields2, $tie['tie_id'], $tie);

	  if ($tie['tie_place'] != $place)
	    {
	      if ($place != "")
		$places[$place] = $rows;
	      $place = $tie['tie_place'];
	      $rows = array();
	    }
	  if ($time != $tie['time'])
	    {
	      if ($time!="00:00:00")
		{
		  $cells[] = $lines;
		  $rows[$time] = $cells;
		}
	      $cells = array();
	      $tieId=-1;
	      $time = $tie['time'];
	    }
	  if ($tieId != $tie['tie_id'])
	    {
	      if ($tieId>=0) $cells[] = $lines;
	      $lines = array();
	      $lines[0] = $tie['rund_name'];
	      $lines[1] = $tie['team_stamp'];
	      $lines[2] = $tie['tie_step'];
	      $tieId = $tie['tie_id'];
	    }
	  else
	    $lines[1] .= " v ".$tie['team_stamp'];
	}
      $cells[] = $lines;
      $rows[$time] = $cells;
      $places[$place] = $rows;
      return $rows;
    }
  // }}}


  // {{{ getProgramIndiv
  /**
   * Return the table with the program of the division
   *
   * @access public
   * @param  integer  $divId  Id of the division
   * @param  integer  $date   date of the ties
   * @param  integer  $sort     Sorting column
   * @return array   array of users
   */
  function getProgramIndiv($date, $heure)
    {
      $heure = $heure ? $heure : "00:00";
      $date = $date ? $date : "00-00-0000";
      $utd = new utDate();
      $utd->setIsoDateTime("$date $heure");
      //echo "<hr> $date / $heure";

      // Firt select the ties of the group
      $eventId = utvars::getEventId();
      $fields = array('tie_id', 'rund_name', 'tie_step', 'tie_place',
		      "time(tie_schedule) as time",
		      "tie_schedule",
		      "date(tie_schedule) as horaire",'mtch_num',
		      'rund_id', 'tie_posRound','tie_court','draw_name','draw_id',
          "(UNIX_TIMESTAMP( mtch_end) - UNIX_TIMESTAMP(mtch_begin)) as duree");
      $tables = array('rounds', 'draws','ties', 'matchs');
      $where = " tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND mtch_tieId = tie_id".
	" AND time(tie_schedule)  = '". $utd->getTime() . "'".
	" AND date(tie_schedule)  = '" . $utd->getIsoDate() . "'".
	" AND draw_eventId = $eventId";
      $order = "tie_schedule, mtch_num ASC,tie_place, tie_court ASC , time, tie_id";

      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = "msgNoTies";
	  return $infos;
        }

      $fields = array('rund_name');
      $fields2 = array('tie_place','tie_step');
      $fields3 = array('draw_name');
      $place = "";
      while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $tie = $this->_getTranslate('rounds', $fields, $tie['rund_id'], $tie);
	  $tie = $this->_getTranslate('ties', $fields2, $tie['tie_id'], $tie);
	  $tie = $this->_getTranslate('draws', $fields3, $tie['draw_id'], $tie);

    $rows[] = $tie ;

        }

    return $rows;
  }
  // }}}

  // {{{ getProgramRounds
  /**
   * Return the table with the program of the division
   *
   * @access public
   * @param  integer  $divId  Id of the division
   * @param  integer  $date   date of the ties
   * @param  integer  $sort     Sorting column
   * @return array   array of users
   */
  function getDateTies()
    {
      // Firt select the ties of the group
      $eventId = utvars::getEventId();
      $fields = array("tie_schedule");
      $tables = array('rounds', 'draws','ties');
      $where = " tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId GROUP BY time";
      $order = "tie_schedule";

      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = "msgNoTies";
	  return $infos;
        }

    $utd = new utdate();

      while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
          $utd->setIsoDateTime($tie['time']);
        $rows[$utd->getDate()][] = $utd->getTime();

        }

//print_r($rows);

    return $rows;
  }
  // }}}

  // {{{ getMatchStep
  /**
   * Return the step of a match
   *
   * @access public
   * @param  integer  $divId  Id of the division
   * @param  integer  $date   date of the ties
   * @param  integer  $sort     Sorting column
   * @return array   array of users
   */
  function getMatchStep($matchId)
    {
      // Firt select the ties of the group
      $eventId = utvars::getEventId();
      $fields = array('tie_posRound','mtch_num', 'mtch_court',
		      'rund_type');
      $tables = array('matchs','ties', 'rounds');
      $where = " tie_id = mtch_tieId".
	" AND mtch_id = $matchId";
	" AND tie_roundId = rund_id";
      $order = "";

      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = "msgNoTies";
	  return $infos;
        }

      $utr = new utround();
      $ut = new utils();
      $tiePosRound = $res->fetchRow(DB_FETCHMODE_ASSOC);
      //print_r($tiePosRound);

      $step = $utr->getTieStep($tiePosRound['tie_posRound'],
				$tiePosRound['rund_type']);
      if ($tiePosRound['rund_type'] == WBS_ROUND_MAINDRAW ||
	  $tiePosRound['rund_type'] == WBS_ROUND_QUALIF)
	$step = $ut->getLabel($step);

      $row[] = $step;
      $row[] = $tiePosRound['mtch_num'] ;
      $row[] = $tiePosRound['mtch_court'];
      //print_r($row);exit;
      return $row ;
  }
  // }}}

}


?>
