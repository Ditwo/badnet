<?php
/*****************************************************************************
!   Module     : ties
!   File       : $Source: /cvsroot/aotb/badnet/src/ties/base.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.13 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/04/04 22:55:51 $
******************************************************************************/
require_once "utils/utbase.php";
require_once "utils/utimg.php";
require_once "utils/utround.php";


/**
* Common fonction for admi and visitor  for ties
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class tiesBase extends utbase
{

  // {{{ properties
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
      $utr = new utround();
      $allTies = $utr->getTies($roundId);
      if (isset($ties['errMsg'])) return $ties;

      // Construc a table with the ties
      $ties = array();
      foreach($allTies as $data)
	{
	  $data['score'] = '0-0';
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
      // Select the matchs of the tie
      $fields = array('team_name', 
		      'CONCAT_WS("-", t2t_scoreW, t2t_scoreL) as score', 
		      'tie_id', 'tie_posRound', 'asso_logo', 't2t_result',
		      'team_logo');
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
      $fields = array('draw_name', 'rund_name', 'rund_id', 'draw_id', 'tie_id',
		      'tie_schedule', 'tie_place', 'tie_place', 'tie_step',
      		  'tie_entrydate', 'tie_validdate', 'tie_entryid', 'tie_validid', 'tie_controlid', 'tie_controldate');
      $tables = array('rounds', 'draws', 'ties');
      $where  = "tie_Id = $tieId".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_Id";
      $res = $this->_select($tables, $fields, $where);

      $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $utd = new utdate();
      $utd->setIsoDateTime($row['tie_schedule']);
      $row['tie_schedule'] = $utd->getDateTime();
      
      $utd->setIsoDateTime($row['tie_entrydate']);
      $row['tie_entrydate'] = $utd->getDateTime();
      
      $utd->setIsoDateTime($row['tie_validdate']);
      $row['tie_validdate'] = $utd->getDateTime();

      $utd->setIsoDateTime($row['tie_controldate']);
      $row['tie_controldate'] = $utd->getDateTime();
      
      $fields = array('draw_name');
      $row = $this->_getTranslate('draws', $fields, $row['draw_id'], $row);
      $fields = array('rund_name');
      $row = $this->_getTranslate('rounds', $fields, $row['rund_id'], $row);
      $fields = array('tie_place','tie_step');
      $row = $this->_getTranslate('ties', $fields, $row['tie_id'], $row);

      $cols = array('user_name');
      $tables = array('users');
      $where  = "user_id = '" . $row['tie_entryid'] . "'";
      $res = $this->_select($tables, $cols, $where);
      $user = $res->fetchRow(DB_FETCHMODE_ASSOC);
      if ( !empty($user) ) $row['entryname'] = $user['user_name'];
      else $row['entryname'] = '';
  
      $where  = "user_id = '" . $row['tie_validid'] . "'";
      $res = $this->_select($tables, $cols, $where);
      $user = $res->fetchRow(DB_FETCHMODE_ASSOC);
      if ( !empty($user) ) $row['validname'] = $user['user_name'];
      else $row['validname'] = '';
      
      $where  = "user_id = '" . $row['tie_controlid'] . "'";
      $res = $this->_select($tables, $cols, $where);
      $user = $res->fetchRow(DB_FETCHMODE_ASSOC);
      if ( !empty($user) ) $row['controlname'] = $user['user_name'];
      else $row['controlname'] = '';
      return $row;
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
      $fields = array('team_id', 'team_name', 'team_stamp', 'asso_logo', 
		      'team_logo');
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
	  if ($team[4] != '')
	    $flag[] = utimg::getPathTeamLogo($team[4]);
	  else
	    $flag[] = utimg::getPathFlag($team[3]);
	  $team[1] = $team[1]." (".$team[2].")";
	  $rows[] = $team;
	}

      $uti = new utimg();
      $numCol2 = 0;
      for($numLine=0; $numLine<$nbTeams; $numLine++)
	{
	  $line = $rows[$numLine];
	  // Select the ties of the team
	  $fields = array('t2t_scoreW', 't2t_scoreL', 'tie_id', 
			  'tie_posRound', 'tie_schedule', 'tie_validid', 'tie_controlid');
	  $tables = array('t2t', 'ties');
	  $where = "t2t_teamId =". $line[0].
	    " AND t2t_tieId = tie_id".
	    " AND tie_roundId = '$groupId'";
	  $order = "tie_posRound";
	  $resTie = $this->_select($tables, $fields, $where, $order);
	 
	  $numCol=0;
	  $nbTies = $resTie->numRows();
	  $utd = new utDate();
          // Pour chaque rencontre de l'equipe
	  while (($numCol < $nbTeams-1) &&
		 ($tie = $resTie->fetchRow(DB_FETCHMODE_ASSOC)))
	    {
	      $ou = "mtch_tieId = {$tie['tie_id']}".
		" AND mtch_status =".WBS_MATCH_ENDED;
	      $nbMatch = $this->_selectfirst('matchs', 'count(*)', $ou);
	      $lines = array();
	      if ($tie['tie_schedule'] && ($tie['tie_schedule'] != '0000-00-00 00:00:00') )
		{
		  $utd->setIsoDateTime($tie['tie_schedule']);
		  if ($nbTeams > 6)
		    {
		      $lines[] = array('value' => $utd->getShortDate(),
				       'class' => 'lineDate');
		    }
		  else
		    $lines[] = array('value' => $utd->getShortDate(),
				     'class' => 'lineDate');
		  $lines[] = array('value' => $utd->getTime(),
				   'class' => 'lineTime');
		}
	      else 
		  $lines[] = array('value' => '--',
				   'class' => 'lineTime');
	      $time = $lines;

	      // Si la colonne et la ligne sont identiques,
	      // il n'y a rien a marquer (une equipe ne joue 
	      // pas contre elle meme)
	      if ($numCol == $numLine)
		{
		  $lines = array();
		  $lines[] = array('value' => '&nbsp;---&nbsp;', 'class' => 'lineDate');
		  $line[$numCol+2] = $lines;
		  $line[$numCol+2+$nbTeams]=KAF_NONE;
		}
	      // La colonne est superieure a la ligne,
	      // on affiche le resultat, avec le score de l'equipe de la 
	      //ligne en premier dans la partie haute du tableau
	      if ($numCol >= $numLine)
		{
		  $score = $tie['t2t_scoreW']."/".$tie['t2t_scoreL'];
		  if ($score === "0/0")
		    {
		      $line[$numCol+3]= $time;
		      $line[$numCol+3+$nbTeams]=$tie['tie_id'];
		    }
		  else
		    {
			  $lines = array();
			  if ($tie['tie_controlid'] > 0)
			    $lines[] = array('value' => $score, 'class' => 'lineScore');
			  elseif ($tie['tie_validid'] > 0)
			    $lines[] = array('value' => $score, 'class' => 'lineScore',
					     'logo'  => $uti->getIcon(WBS_MATCH_CLOSED));
			  else
			    $lines[] = array('value' => $score, 'class' => 'lineScore',
					     'logo'  => $uti->getIcon(WBS_MATCH_ENDED));
			  $line[$numCol+3] = $lines;
			  $line[$numCol+3+$nbTeams]=$tie['tie_id'];
		    }
		}
	      // Si le nombre de rencontre est egal au nombre d'equipe -1
	      // la poule se joue en une seule rencontre, pas en aller/retour
	      // On affiche le score (inverse) dans la partie basse du tableau
	      else if($nbTies == $nbTeams-1)
		{
		  $score = $tie['t2t_scoreW']."/".$tie['t2t_scoreL'];
		  if ($score === "0/0")
		    {
		      $line[$numCol+2]= $time;
		      $line[$numCol+2+$nbTeams]=KAF_NONE;
		      $line[$numCol+2+$nbTeams]=$tie['tie_id'];
		    }
		  else
		    {
			  $lines = array();
			  $lines[] = array('value' => $score, 'class' => 'lineScore');
			  $line[$numCol+2] = $lines;
			  $line[$numCol+2+$nbTeams]=$tie['tie_id'];
		    }
		}

	      $numCol++;
	    }
	  $line[]= $stamp[$numLine];
	  $line[]= $flag[$numLine];
	  ksort($line);
	  $rows[$numLine] = $line;
	  $numLine2 = 0;
	  // S'il reste des rencontres pour la ligne traites
	  // cela se  joue en aller/retour. Le resultat s'affiche
          // dans la partie basse du tableau
	  while ($tie = $resTie->fetchRow(DB_FETCHMODE_ASSOC))
	    {      
	      $ou = "mtch_tieId = {$tie['tie_id']}".
		" AND mtch_status =".WBS_MATCH_ENDED;
	      $nbMatch = $this->_selectfirst('matchs', 'count(*)', $ou);
	      $lines = array();
	      if ($tie['tie_schedule'])
		{
		  $utd->setIsoDateTime($tie['tie_schedule']);
		  if ($nbTeams > 6)
		    {
		      $lines[] = array('value' => $utd->getShortDate(),
				       'class' => 'lineDate');
		    }
		  else
		    $lines[] = array('value' => $utd->getShortDate(),
				     'class' => 'lineDate');
		  $lines[] = array('value' => $utd->getTime(),
				   'class' => 'lineTime');
		}
	      else 
		  $lines[] = array('value' => '--',
				   'class' => 'lineTime');
	      $time = $lines;
	      if ($numLine2 >= $numCol2)
		{
		  $line = $rows[$numLine2+1];
		  $score = $tie['t2t_scoreL']."/".$tie['t2t_scoreW'];
		  if ($score === "0/0")
		    {
		      $line[$numCol2+2]= $time;
		      $line[$numCol2+2+$nbTeams]=$tie['tie_id'];
		    }
		  else
		    {
			  $lines = array();
			  if ($tie['tie_controlid'] > 0)
			    $lines[] = array('value' => $score, 'class' => 'lineScore');
			  elseif ($tie['tie_validid'] > 0)
			    $lines[] = array('value' => $score, 'class' => 'lineScore',
					     'logo'  => $uti->getIcon(WBS_MATCH_CLOSED));
			  else
			    $lines[] = array('value' => $score, 'class' => 'lineScore',
					     'logo'  => $uti->getIcon(WBS_MATCH_ENDED));
		    	
			  $line[$numCol2+2] = $lines;
			  $line[$numCol2+2+$nbTeams]=$tie['tie_id'];
		    }
		  ksort($line);
		  $rows[$numLine2+1] = $line;
		}
	      $numLine2++;
	    }
	  $numCol2++;
	}
      $line = $rows[$numCol];
	  $lines = array();
	  $lines[] = array('value' => '&nbsp;---&nbsp;', 'class' => 'lineDate');
	  $line[$numCol+2] = $lines;
	  $line[$numCol+2+$nbTeams]=KAF_NONE;
      $line[$numCol+3+$nbTeams]=$stamp[$numLine-1];
      $line[$numCol+4+$nbTeams]=$flag[$numLine-1];
      ksort($line);
      $rows[$numCol] = $line;
      return $rows;
    }
  // }}}


  /**
   * Return the match of a tie
   *
   * @access public
   * @param  integer  $tieId  id of the tie
   * @return array   information of the matchs if any
   */
  function getMatchs($tieId, $teamL, $teamR)
    {
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
      
      // Get the point of the result of a match
      $fields  = array('DISTINCT rund_matchWin', 'rund_matchLoose', 
		       'rund_matchWO', 'rund_matchrtd');
      $tables  = array('rounds', 'ties');
      $where = "tie_id = $tieId".
	" AND tie_roundId = rund_id";
      $res = $this->_select($tables, $fields, $where);
      $points = $res->fetchRow(DB_FETCHMODE_ASSOC);

      // Select the matchs of the tie
      $fields = array('mtch_id', 'mtch_discipline', 'mtch_begin', 
		      'mtch_end', 'mtch_score', 'mtch_order', 
		      'mtch_status', 'mtch_rank');
      $tables = array('matchs');
      $where = "mtch_del != ".WBS_DATA_DELETE.
	" AND mtch_tieId = $tieId";
      $order = 'mtch_rank';
      $res = $this->_select($tables, $fields, $where, $order);

      // Construc a table with the match
      $matches = array();
      $ut = new utils();
      while ($match = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $discipline = $match[1];
	  if ($discipline < 6) $match[1] = $ut->getSmaLabel($discipline);
	  else $match[1] = 'SP';
	  if ($defTie[$discipline]>1) $match[1] .= " ".$match[5];
	  $match[2] = array();
	  $match[3] = array();
	  $match[5] = '';
	  $match[7] = '';
	  $full = array_pad($match,16, "");
	  $full[13] = utimg::getIcon($match[6]);
	  $full[6] = '';
	  $matches[$match[0]] = $full;
	}
     // Select the players of the matchs of the tie
      $fields = array('mtch_id', 'p2m_result', 'regi_longName', 
		      'regi_teamId', 'i2p_pairId', 'rkdf_label', 'regi_id', 'regi_catage');
      $tables = array('matchs', 'p2m', 'i2p', 'registration', //'ranks',  
		      'rankdef', 'members');
      $where = "mtch_del != ".WBS_DATA_DELETE.
	" AND mtch_tieId = $tieId".
	" AND mtch_id = p2m_matchId".
	" AND p2m_pairId = i2p_pairId".
	" AND i2p_regiId = regi_id".
	" AND i2p_rankdefid = rkdf_id".
	" AND regi_memberId = mber_id";
	//" AND rank_regiId = regi_id".
	//" AND rank_rankdefId = rkdf_id".
	//" AND rank_discipline = mtch_disci";
      $order = "mtch_id, regi_teamId, mber_sexe, regi_longName";
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
		  $line = array();
		  $line['value'] = $player[2] . ' - ' . $ut->getSmaLabel($player[7]) . ' - ' . $player[5];
		  $line['action'] = array(KAF_UPLOAD, 'resu', KID_SELECT, $player[6]);
		  $match[2][] = $line;		  

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
		  $line = array();
		  $line['value'] = $player[2] . ' - ' . $ut->getSmaLabel($player[7]) . ' - ' . $player[5];
		  $line['action'] = array(KAF_UPLOAD, 'resu', KID_SELECT, $player[6]);
		  $match[2][]= $line;
		  //$match[2] .= "<br />{$player[2]} - {$player[5]}";
		}
	    }
	  else
	    {
	      $teamId=-1;
	      $line = array();
		  $line['value'] = $player[2] . ' - ' . $ut->getSmaLabel($player[7]) . ' - ' . $player[5];
	      $line['action'] = array(KAF_UPLOAD, 'resu', KID_SELECT, $player[6]);
	      $match[3][]= $line;
	      //$match[3] .= "{$player[2]} - {$player[5]}<br />";
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
      $fields = array('team_id', 'team_name', 'tie_roundId', 'team_stamp',
		      't2t_penalties', 't2t_result');
      $tables = array('teams', 't2t', 'ties');
      $where  = "t2t_teamId = team_id".
	" AND t2t_tieId = tie_id".
	" AND tie_id = $tieId";
      $order = "t2t_posTie";
      $res = $this->_select($tables, $fields, $where, $order);
      $teams =array();
      while ($team = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $teams[]=$team;
	}
      return $teams;
    }
  // }}}


  /**
   * Return the list of the teams in a group with their ranking
   *
   * @access public
   * @param  integer  $groupId  Id of the group
   * @return array   array of users
   */
  function getRankGroup($groupId)
    {
      $fields = array('t2r_rank', 'team_name', 'team_stamp',
		      't2r_points', 't2r_penalties', 't2r_tieW', 't2r_tieEP', 
		      't2r_tieE', 't2r_tieEM', 't2r_tieL',
		      //'sum(t2t_matchW + t2t_penalties)', 
		      //'sum(t2t_matchL + t2t_penaltiesO)', 
		      //'sum(t2t_matchW + t2t_penalties - t2t_matchL - t2t_penaltiesO)',
		      'sum(t2t_matchW)', 
		      'sum(t2t_matchL)', 
		      'sum(t2t_matchW - t2t_matchL)',
      		  'sum(t2t_setW)', 'sum(t2t_setL)', 'sum(t2t_setW - t2t_setL)',
		      'sum(t2t_pointW)', 'sum(t2t_pointL)', 'sum(t2t_pointW - t2t_pointL)', 
		      'team_id', 'asso_logo', 't2r_id', 'team_logo');
      $tables = array('teams', 't2t', 'ties', 't2r', 'assocs', 'a2t');
      $where = "tie_roundId ='$groupId'".
	" AND team_id = t2t_teamId".
	" AND t2t_tieId = tie_id".
	" AND t2r_teamId = team_id".
	" AND t2r_roundId = '$groupId'".
	" AND a2t_assoId = asso_id ".
	" AND a2t_teamId = team_id ".
	" GROUP BY team_id";
      $order = "1,13 DESC,16 DESC,19 DESC";
      $res = $this->_select($tables, $fields, $where, $order);
      $rows = array();
      while ($team = $res->fetchRow(DB_FETCHMODE_ORDERED))
      {
	  	if ($team[22] != '') $team[20]= utimg::getPathFlag($team[22]);
	  	else $team[20]= utimg::getPathFlag($team[20]);
	  	unset($team[22]);
	  	$rows[] = $team;
      }
      return $rows;
    }

}
?>
