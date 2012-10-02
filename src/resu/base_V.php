<?php
/*****************************************************************************
!   Module     : resu
!   File       : $Source: /cvsroot/aotb/badnet/src/resu/base_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.20 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/04/03 06:49:03 $
******************************************************************************/
require_once "utils/utbase.php";
require_once "utils/utscore.php";
require_once "utils/utround.php";
require_once "draws/draws.inc";

/**
* Acces to the dababase for resu
*
* @author Romain JUBERT <romain.jubert@ifrance.com>
* @see to follow
*
*/
class resuBase_V  extends utbase
{

  // {{{ properties
  var $_tri=0;
  // }}}

  // {{{ getfirstMatch
  /**
   * Heure du premier macth du joueur
   */
  function getFirstMatch($regiId)
    {
      $utev = new utEvent();
      $convocation = $utev->getConvoc();

      // Pour un tournoi individuel, recuperer les inscriptions
      // Et les heures de premier match
      $row['firstMatch'] = '';
      $row['convocation'] = '';
      $row['place'] = '';
      if (!utvars::IsTeamEvent())
	{
	  // Heure de premier match
	  $fields = array('tie_schedule', 'tie_place');
	  $order = "tie_schedule";
	  if ($convocation['evnt_convoc'] == WBS_CONVOC_MATCH)
	    {
	      $tables = array('i2p', 'p2m', 'matchs', 'ties');
	      $where  = "i2p_regiId = $regiId".
		" AND i2p_pairId = p2m_pairId".
		" AND p2m_matchId = mtch_id".
		" AND tie_schedule != ''".
		" AND tie_isBye = 0".
		" AND tie_schedule != '0000-00-00 00:00:00'".
		" AND mtch_tieId = tie_id".
		" AND mtch_status <=".WBS_MATCH_LIVE;
	    }
	  // Heure de debut de tableau
	  else
	    {
	      $tables = array('i2p', 'p2m', 'pairs', 'rounds', 'ties');
	      $where  = "i2p_regiId=$regiId".
		" AND i2p_pairId = pair_id".
		" AND pair_drawId = rund_drawId".
		" AND tie_schedule != ''".
		" AND tie_schedule != '0000-00-00 00:00:00'".
		" AND rund_id = tie_roundId";
	    }

	  $res = $this->_select($tables, $fields, $where, 
				$order, array('tie'));
	  if ($res->numRows())
	    {
	      $utd = new utdate();
	      $entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
	      $utd->setIsoDateTime($entry['tie_schedule']);
	      if ($convocation['evnt_convoc'] == WBS_CONVOC_MATCH)
		$row['firstMatch'] = $utd->getDateTime();
	      else
		$row['firstMatch'] = '--';	
	      $utd->addMinute(-$convocation['evnt_delay']);   
	      $row['convocation'] = $utd->getDateTime();	
	      if ($convocation['evnt_lieuconvoc']  == '')     
		$row['place'] = $entry['tie_place'];	
	      else
		$row['place'] = $convocation['evnt_lieuconvoc'];		    
	    }
	}
      return $row;
    }
  //}}}

  // {{{ getPlayers
  /**
   * Return the list of the players
   *
   * @access public
   * @param  integer $regiId registration's id of player 
   * @return array   array of matchs
   */
  function getPlayers()
    {
      // list of the matchs of the player
      $fields = array('regi_id', 'regi_longName');
      $where = "regi_eventId =".utvars::getEventId().
	" AND regi_type=".WBS_PLAYER;
      $order = "2";
      $res = $this->_select('registration', $fields, $where, $order, 'regi');
      $regis=array();
      while ($regi = $res->fetchRow(DB_FETCHMODE_ASSOC))
	$regis[$regi['regi_id']]  = $regi['regi_longName'];

      return $regis;
    }
  // }}} 

  // {{{ getMatchsIndiv
  /**
   * Return the list of the matchs of a player during a team event
   *
   * @access public
   * @param  integer $regiId registration's id of player 
   * @return array   array of matchs
   */
  function getMatchsIndiv($regiId)
    {
      $utr = new utround();
      $ut = new utils();
      $utd = new utdate();

      // list of the matchs of the player
      $fields = array('mtch_id', 'rund_name',
		      'tie_posRound', 'p2m_pairId', 'p2m_result AS result', 
		      'mtch_score', 'mtch_begin AS duree', 'mtch_end', 
		      'draw_name', 'p2m_result', 
		      'mtch_status', 'draw_id', 'rund_type',
		      'draw_disci', 'tie_schedule');
      $tables = array('i2p', 'p2m', 'matchs', 'ties', 'rounds',
		      'draws');
      $where = "i2p_regiId = $regiId".
	" AND i2p_pairId = p2m_pairId".
	" AND p2m_matchId = mtch_id".
	" AND mtch_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND tie_isBye = 0". 
	" AND rund_drawId = draw_id";
      $order = "draw_id, rund_id, tie_posRound";
      $res = $this->_select($tables, $fields, $where, $order, 'rund');
      if (!$res->numRows())
	{
	  $err['errMsg'] = 'msgNoMatch';
	  return $err;
	} 

      while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $step = $utr->getTieStep($match['tie_posRound']);
	  $match['tie_posRound'] = $ut->getLabel($step);
	  if ($match['mtch_status'] > WBS_MATCH_LIVE)
	    {
	      $utd->setIsoDateTime($match['duree']);
	      $duree  = $utd->elaps($match['mtch_end']).' mn';
	      if ($duree > 5 && $duree < 100)
		$match['duree'] = $duree;
	      else
		$match['duree'] = '--';		  
	    }
	  else
	    {
	      $utd->setIsoDateTime($match['tie_schedule']);
	      $match['duree'] = $utd->getDateTime();
	    }
	  $match['result'] = $ut->getLabel($match['p2m_result']);
	  $match['p2m_result'] = utimg::getIcon($match['p2m_result']);

	  $line['value'] = $match['rund_name'];
	  switch($match['rund_type'])
	    {
	    case WBS_ROUND_GROUP:
	      $action = DRAW_GROUPS_DISPLAY;
	      $match['tie_posRound'] = '';
	      break;
	    case WBS_ROUND_QUALIF:
	      $action = DRAW_QUALIF_DISPLAY;
	      break;
	    case WBS_ROUND_MAINDRAW:
	      $action = DRAW_FINAL_DISPLAY;
	      break;
	    default:
	      $action = DRAW_DISPLAY;
	      break;
	    }
	  $line['action'] = array(KAF_UPLOAD, 'draws', $action, 
				  $match['draw_id']);
	  $lines = array($line);
	  $match['rund_name'] = $lines;

	  // For double, search partnair
	  if ($match['draw_disci'] > WBS_WS)
	    {
	      $fields = array('regi_longName');
	      $tables = array('registration', 'i2p');
	      $where = "i2p_regiId = regi_id".
		" AND i2p_pairId = ".$match['p2m_pairId'].
		" AND regi_id != $regiId";
	      $res1 = $this->_select($tables, $fields, $where);
	      if ($res1->numRows())
		{
		  $buf = $res1->fetchRow(DB_FETCHMODE_ASSOC);
		  $match['draw_name'] .= ' - '.$buf['regi_longName'];
		}
	    }
	  
	  // Find player(s) of second pair
	  $fields = array('regi_longName', 'regi_id');
	  $tables = array('registration', 'i2p', 'p2m', 'members');
	  $where = "i2p_regiId = regi_id".
	    " AND i2p_pairId = p2m_pairId".
	    " AND mber_id = regi_memberId".
	    " AND i2p_pairId != ".$match['p2m_pairId'].
	    " AND p2m_matchId = ".$match['mtch_id'];
	  $order = 'mber_sexe, regi_longName';
	  $res1 = $this->_select($tables, $fields, $where, $order);
	  if ($res1->numRows())
	    {
	      $lines = array();
	      while($buf = $res1->fetchRow(DB_FETCHMODE_ASSOC))
		{
		  $line['value'] = $buf['regi_longName'];
		  $line['action'] = array(KAF_UPLOAD, 'resu', KID_SELECT, 
					  $buf['regi_id']);
		  $lines[] = $line;
		}
	      $match['p2m_pairId'] = $lines;
	    }
	  else
	    $match['p2m_pairId'] = "";


	  $rows[] = $match;
	} 
      return $rows;
    }
  // }}} 
 

  // {{{ getPlayer
  /**
   * Return the information of a player
   *
   * @access public
   * @param  string  $regiId  registration's id of the player
   * @return array   information of the member if any
   */
  function getPlayer($regiId)
    {
      $fields = array('mber_id', 'regi_longName', 'mber_firstname',
		      'mber_secondname', 'mber_born', 'mber_ibfnumber', 
		      'mber_licence', 'mber_urlphoto', 'asso_name', 
		      'rkdf_label', 'asso_url', 'asso_id', 'team_id', 'team_name');
      $tables = array('members', 'registration', 'teams', 'a2t', 
		      'assocs', 'ranks', 'rankdef');
      $where = " mber_id = regi_memberId".
	" AND regi_teamId = team_id".
	" AND team_id = a2t_teamId".
	" AND a2t_assoId = asso_id".
	" AND rank_regiId = regi_id".
	" AND rank_rankdefId = rkdf_id".
	" AND regi_id = ".$regiId;
      $res = $this->_select($tables, $fields, $where, false, 'regi');
      if (!$res->numRows())
	{
	  $err['errMsg'] = "msgNoAutorisation";
	  return $err;
	} 

      $infos = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $double = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $mixte = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $infos['rkdf_label'].=','.$double['rkdf_label'].','.$mixte['rkdf_label'];
      
      //modif date
      $utdat = new utdate();
      $utdat->setIsoDate($infos['mber_born']);
      $infos['mber_born'] = $utdat->getDate();
      return $infos;
    }
  // }}}
  
  // {{{ getMatchs
  /**
   * Return the list of the matchs of a player during a team event
   *
   * @access public
   * @param  string  $sort   criteria to sort matchs
   * @param  integer $regi_id registration's id of player 
   * @return array   array of matchs
   */
  function getMatchs($regiId)
    {

      $fields = array('regi_memberId');
      $tables = array('registration');
      $where = "regi_id = $regiId";
      $res = $this->_select($tables, $fields, $where);
      $member = $res->fetchRow(DB_FETCHMODE_ORDERED);

      // list of the matchs
      $fields = array('mtch_id', 'pair_id', 'mtch_tieId', 'p2m_result',
		      'mtch_begin', 'mtch_score', 'mtch_discipline',
		      'mtch_status', 'i2p_classe', 'rkdf_label');
      $tables = array('registration', 'i2p', 'pairs', 'p2m', 
		      'matchs', 'ties', 'rounds',
		      'draws', 'rankdef');
      $where = "regi_id = i2p_regiId".
	" AND i2p_pairId = pair_id".
	" AND pair_id = p2m_pairId".
	" AND p2m_matchId = mtch_id".
	" AND mtch_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND p2m_matchId = mtch_id".
	" AND rkdf_id = i2p_rankdefid".
    " AND regi_eventId =".utvars::getEventId().
	" AND regi_memberId = $member[0]";
      $order = "regi_id, draw_serial, rund_id, tie_id, tie_schedule";

      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $err['errMsg'] = 'msgNoMatch';
	  return $err;
	} 

      while ($matchs = $res->fetchRow(DB_FETCHMODE_ORDERED))
	{
	  $entry = NULL;
	  $entry[0] = $matchs[0]; // id match
	  
	  //pair_id de l'adversaire
	  $fields = array('p2m_pairId');
	  $tables = array('p2m');
	  $where = "p2m_pairId != ".$matchs[1].
	    " AND p2m_matchId = ".$matchs[0];
	  $res1 = $this->_select($tables, $fields, $where);
	  $pair_id_adv = $res1->fetchRow(DB_FETCHMODE_ORDERED);

	  // division, groupe et rencontre
	  $fields = array("CONCAT_WS(' ', draw_name,rund_name)", 
			  "concat(team1.team_name,\" - \",team2.team_name) as rencontre",
			  'tie_schedule', 'draw_id');
	  $tables = array('draws', 'rounds', 'ties', 't2t t2t_1', 
			  't2t t2t_2', 'teams team1', 'teams team2');
	  $where = "draw_id = rund_drawId".
	    " AND rund_id = tie_roundId".
	    " AND tie_id = t2t_1.t2t_tieId".
	    " AND tie_id = t2t_2.t2t_tieId".
	    " AND t2t_1.t2t_teamId = team1.team_id".
	    " AND t2t_2.t2t_teamId = team2.team_id".
	    " AND team1.team_id != team2.team_id".
	    " AND tie_id = ".$matchs[2];
	  $res2 = $this->_select($tables, $fields, $where);
	  $tie = $res2->fetchRow(DB_FETCHMODE_ORDERED);
  
	  //nom du partenaire
	  $fields = array('regi_longName', 'regi_id');
	  $tables = array('registration', 'i2p');
	  $where = "regi_id = i2p_regiId".
	    " AND i2p_pairId = ".$matchs[1].
	    " AND regi_id !=".$regiId ;
	  $res3 = $this->_select($tables, $fields, $where);
	  $partenaire = $res3->fetchRow(DB_FETCHMODE_ORDERED);
	  
	  $entry[1]=$tie[0];
	  $entry[2]=$tie[1]. ' (' . $matchs[9] . '-' . $matchs[8] . ')'; //rencontre
	  	  
	  $utdat = new utdate();
	  $ut = new utils();
	  if($matchs[3]!=WBS_RES_NOPLAY)
	    $utdat->setIsoDate($matchs[4]); // date effective du match     
	  else
	    $utdat->setIsoDate($tie[2]);   // date prevu du match 	    

	  $utdat->setIsoDate($tie[2]);   // date prevu du match 	    
	  $entry[3] = $utdat->getDate();   //date

	  $line = array();
	  $line['value'] = $ut->getLabel($matchs[6]);
	  $entry[4][] = $line;       //discipline
	  if($partenaire[0]!="")
	    {
	      $line = array();
	      $line['value'] = "({$partenaire[0]})";
	      $line['action'] = array(KAF_UPLOAD, 'resu', KID_SELECT, $partenaire[1]);
	      $entry[4][] = $line; 
	    }
	  $entry[5] = $this->_getPairName($pair_id_adv[0]);  //adversaire
	  $entry[6] = $ut->getLabel($matchs[3]);      // non jou�/gagn�/perdu...

	  //score
	  if($matchs[3]!=WBS_RES_NOPLAY)
	    {
	      $score = new Utscore(); 
	      $score->setScore($matchs[5]);
	      if( $matchs[3]==WBS_RES_WIN || 
		  $matchs[3]==WBS_RES_WINAB  ||
		  $matchs[3]==WBS_RES_WINWO )
		{
		  $entry[7]=$score->getWinScore();
		}
	      else
		{
		  $entry[7]=$score->getLoosScore();
		}
	    }
	  else
	    $entry[7]= "";

	  // id division, rencontre et partenaire pour les liens
	  $entry[11] = $tie[3];                              //draw_id
	  $entry[12] = $matchs[2];                           //tie_id
	  $entry[13] = utimg::getIcon($matchs[7]);
	  $entry[14] = utimg::getIcon($matchs[3]);
	  $rows[] = $entry;
	} 
    
      return $rows;
    }
  // }}} 


// {{{ _getPairName
  /**
   * Return the players of a pair
   *
   * @access private
   * @param  integer  $pairId  id of the pair
   * @return array   information of the pair
   */
  function _getPairName($pairId)
    {
      $name = "";
      if ($pairId =='')	return "";
      $fields = array('regi_longName', 'regi_id', 'i2p_classe', 'rkdf_label');
      $tables = array('registration', 'i2p', 'rankdef');
      $where = "regi_id = i2p_regiId".
	" AND i2p_pairId = $pairId".
      " AND i2p_rankdefid=rkdf_id";
      $res = $this->_select($tables, $fields, $where);

      $line = array();
      while ($player  = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $line['value'] = $player[0] . ' - ' . $player[3] . ' - ' . $player[2];
	  $line['action'] = array(KAF_UPLOAD, 'resu', KID_SELECT, $player[1]);
	  $name[] = $line;
	}
      if (!isset($name)) $name = "to follow";
      return $name;
    }
  // }}}

}

?>