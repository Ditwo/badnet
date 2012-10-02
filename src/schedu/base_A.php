<?php
/*****************************************************************************
 !   Module     : Schedu
 !   File       : $Source: /cvsroot/aotb/badnet/src/schedu/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.38 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/03/06 18:02:11 $
 ******************************************************************************/
require_once "utils/utdate.php";
require_once "utils/utbase.php";
require_once "utils/objmatch.php";
require_once "utils/objgroup.php";

/**
 * Acces to the dababase for ties
 *
 */

class scheduBase_A extends utbase
{
	/**
	 * retourne le nombre de match par joueur
	 *
	 * @access public
	 * @return mixed
	 */
	function getMatchWithNbMatch($aDate)
	{
		$utd = new utDate();
		$utd->setFrDate($aDate);
		// Nombre de match par joueur
		$fields = array('regi_id', "count(*) as nb");

		$tables = array('matchs', 'ties', 'rounds', 'draws', 'p2m', 'i2p',
		      'registration');
		$where = "tie_isBye = 0".
	" AND i2p_pairId =  p2m_pairId".
	" AND p2m_matchId = mtch_id".
	" AND rund_drawId = draw_id".
	" AND rund_id = tie_roundId".
	" AND tie_id = mtch_tieId".
	" AND regi_id = i2p_regiId".
    " AND mtch_status < " . WBS_MATCH_LIVE.
	" AND draw_eventId = " . utvars::getEventId();
		$where .= " AND date(tie_schedule) ='". $utd->getIsoDate()."'";
		$where .= " GROUP BY i2p_regiId";
		$res = $this->_select($tables, $fields, $where);
		$nbMatch = array();
		while ($regi = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$nbMatch[$regi['regi_id']] = $regi['nb'];
		}

		// Liste des match de la journee
		$fields = array('mtch_id', 'i2p_pairid', 'regi_id', 'regi_longName', 'draw_name',
		'rund_name', 'tie_schedule', 'tie_place');
		$where = "tie_isBye = 0".
	" AND i2p_pairId =  p2m_pairId".
	" AND p2m_matchId = mtch_id".
	" AND rund_drawId = draw_id".
	" AND rund_id = tie_roundId".
	" AND tie_id = mtch_tieId".
	" AND regi_id = i2p_regiId".
	" AND draw_eventId = ".utvars::getEventId();
		//$where .= " AND date(tie_schedule) ='". $utd->getIsoDate()."'";
		$order = 'mtch_id, i2p_pairid';
		$res = $this->_select($tables, $fields, $where, $order);
		$matchs = array();
		$sort = array();
		$matchId = 0;
		$pairId = 0;
		$nb = 0;
		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($matchId != $match['mtch_id'])
			{
				if ($matchId)
				{
					$tmp[] = $pair1;
					$tmp[] = $pair2;
					$tmp[] = $nb;
					$matchs[] = $tmp;
					$sort[] = $nb;
				}
				$matchId = $match['mtch_id'];
				$pairId  = $match['i2p_pairid'];
				$nb = 0;
				$pair1 = array();
				$pair2 = array();
				if ($match['tie_schedule'] == '0000-00-00 00:00:00') $schedu = '';
				else $schedu = $match['tie_schedule'];
				$tmp = array($matchId, $match['draw_name'], $match['rund_name'],
				$schedu, $match['tie_place']);
			}
			if (isset($nbMatch[$match['regi_id']]) )
			{
				$name = $nbMatch[$match['regi_id']] . ' ' . $match['regi_longName'];
				$nb += $nbMatch[$match['regi_id']];
			}
			else $name = $match['regi_longName'];
			if ($pairId  == $match['i2p_pairid'])
			$pair1[] = array('value' => $name);
			else
			$pair2[] = array('value' => $name);
		}
		array_multisort($sort, SORT_DESC,  $matchs);
		return $matchs;
	}

	/**
	 * retourne le nombre de match par joueur
	 *
	 * @access public
	 * @return mixed
	 */
	function nbMatchPlayer($aDate)
	{
		$utd = new utDate();
		$utd->setFrDate($aDate);
		$fields = array('regi_id', 'regi_longName', "count(*) as nb");

		$tables = array('matchs', 'ties', 'rounds', 'draws', 'p2m', 'i2p',
		      'registration');
		$where = "tie_isBye = 0".
	" AND i2p_pairId =  p2m_pairId".
	" AND p2m_matchId = mtch_id".
	" AND rund_drawId = draw_id".
	" AND rund_id = tie_roundId".
	" AND tie_id = mtch_tieId".
	" AND regi_id = i2p_regiId".
	" AND draw_eventId = ".utvars::getEventId();
		$where .= " AND date(tie_schedule) ='". $utd->getIsoDate()."'";
		$where .= " GROUP BY i2p_regiId";
		$order = "3 DESC";
		$res = $this->_select($tables, $fields, $where, $order);
		$tmp = array();
		while ($regi = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$tmp[] = $regi;
		}
		echo $aDate;
		return $tmp;
	}

	// {{{ getVenuePlayer
	/**
	* Liste des joueurs par salle
	*
	* @access public
	* @return array   array of users
	*/
	function getVenuePlayer()
	{
		// Recuperer les matchs de chaque joueur
		$fields = array('regi_id', 'regi_longName', 'draw_stamp',
		    'mtch_num', 'tie_place', 'tie_schedule', 'regi_arrival');
		$tables = array('registration', 'i2p', 'p2m', 'matchs',
		    'ties', 'rounds', 'draws');
		$where = "regi_id = i2p_regiId".
      " AND i2p_pairId = p2m_pairId".
      " AND p2M_matchId = mtch_id".
      " AND mtch_tieId = tie_id".
      " AND mtch_status < ".WBS_MATCH_LIVE.
      " AND tie_roundId = rund_id".
      " AND tie_isBye = 0".
      " AND rund_drawId = draw_id".
      " AND tie_schedule != ''".
      " AND tie_schedule != '0000-00-00 00:00:00'".
      " AND regi_eventId=".utvars::getEventId();
		$order = 'regi_longName, regi_id, tie_schedule';
		$res = $this->_select($tables, $fields, $where, $order);

		$venues  = array();
		$players = array();
		$regiId  = null;
		$keep    = false;
		while ($regi = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// Nouveau joueur
			if ($regiId != $regi['regi_id'])
			{
				if ($keep)
				{
					$players[$longName] = $matchs;
				}
				$regiId = $regi['regi_id'];
				$longName = $regi['regi_longName'];
				$matchs = array();
				$venue = $regi['tie_place'];
				$keep = false;
			}
			if (!isset($venues[$regi['tie_place']][$regi['regi_id']]))
			$venues[$regi['tie_place']][$regi['regi_id']] = $regi['regi_longName'];
			if ($venue != $regi['tie_place']) $keep = true;
			$matchs[] = "{$regi['tie_place']} ({$regi['tie_schedule']})";
		}
		$venues['pbs'] = $players;
		return $venues;
	}
	// }}}


	// {{{ scanSchedule
	/**
	* Analyse un echeancier
	*
	* @access public
	* @return array   array of users
	*/
	function scanSchedule()
	{
		// Recuperer les matchs de chaque joueur
		$fields = array('regi_id', 'regi_longName', 'draw_stamp',
		    'mtch_num', 'tie_place', 'tie_schedule', 'regi_arrival');
		$tables = array('registration', 'i2p', 'p2m', 'matchs',
		    'ties', 'rounds', 'draws');
		$where = "regi_id = i2p_regiId".
      " AND i2p_pairId = p2m_pairId".
      " AND p2M_matchId = mtch_id".
      " AND mtch_tieId = tie_id".
      " AND mtch_status <= ".WBS_MATCH_LIVE.
      " AND tie_roundId = rund_id".
      " AND tie_isBye = 0".
      " AND rund_drawId = draw_id".
      " AND tie_schedule != ''".
      " AND tie_schedule != '0000-00-00 00:00:00'".
      " AND regi_eventId=".utvars::getEventId();
		$order = 'regi_longName, regi_id, tie_schedule';
		$res = $this->_select($tables, $fields, $where, $order);

		$utft = new utdate();
		$regiId = false;
		$regiPrec = '';
		$rows = array();
		while ($regi = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// Nouveau joueur
			if ($regi['regi_id'] != $regiId)
	  {
	  	$utft->setIsoDateTime( $regi['tie_schedule']);
	  	$regiId = $regi['regi_id'];
	  	$venue  = $regi['tie_place'];
	  	$regiPrec = $regi;

	  	if ($regi['regi_arrival'] > $regi['tie_schedule'])
	  	{
	  		$regi[] = '';
	  		$regi[] = 'Arrivï¿½e';
	  		$regi[] = $regi['regi_arrival'];
	  		$regi[] = '';
	  		unset($regi['regi_arrival']);
	  		$rows[] = $regi;
	  	}
	  	continue;
	  }
	  $delta = $utft->elaps($regi['tie_schedule']);
	  if ($delta < 90 || $regi['tie_place'] != $venue)
	  {
	  	$regiNew = $regi;
	  	$regiNew[] = $regiPrec['draw_stamp'];
	  	$regiNew[] = $regiPrec['mtch_num'];
	  	if ($regi['tie_place'] != $venue)
	  	{
	  		$cell  = array();
	  		$cell[] = array('value' => $regi['tie_place']);
	  		if ($delta < 60) $cell['class'] = "classAlert";
	  		else if ($delta < 90) $cell['class'] = "classWarning";
	  		else $cell['class'] = "classInfo";
	  		$regiNew['tie_place'] = $cell;
	  		$cell  = array();
	  		$cell[] = array('value' => $regiPrec['tie_place']);
	  		$cell['class'] = "classWarning";
	  		$regiNew[] = $cell;
	  		$venue = $regi['tie_place'];
	  	}
	  	else
	  	$regiNew[] = $regiPrec['tie_place'];
	  	if ($delta < 90)
	  	{
	  		if (!$delta) $class = "classAlert";
	  		else if ($delta<=40) $class = "classWarning";
	  		else $class = "classInfo";
	  		$cell  = array();
	  		$cell[] = array('value' => $regi['tie_schedule']);
	  		$cell['class'] = $class;
	  		$regiNew['tie_schedule'] = $cell;
	  		$cell  = array();
	  		$cell[] = array('value' => $regiPrec['tie_schedule']);
	  		$cell['class'] = $class;
	  		$regiNew[] = $cell;
	  		$cell  = array();
	  		$cell[] = array('value' => $delta);
	  		$cell['class'] = $class;
	  		$regiNew[] = $cell;
	  	}
	  	else
	  	{
	  		$regiNew[] = $regiPrec['tie_schedule'];
	  		$regiNew[] = $delta;
	  	}
	  	unset($regiNew['regi_arrival']);
	  	$rows[] = $regiNew;
	  }
	  $regiPrec = $regi;
	  $utft->setIsoDateTime($regi['tie_schedule']);
		}
		return $rows;
	}
	// }}}


	// {{{ updateContact
	/**
	* Sauvegarde le contact email  d'une assoc
	*
	* @access public
	* @return array   array of users
	*/
	function updateContact($contact, $team)
	{
		$where = "team_id = {$team['team_id']}";
		$this->_update('teams', $team, $where);

		if ($contact['ctac_id'] != -1)
		{
			$where = "ctac_id = {$contact['ctac_id']}";
			$this->_update('contacts', $contact, $where);
		}
		else
		{
			$tables = array('assocs', 'a2t');
			$where = 'a2t_assoId = asso_id'.
	  " AND a2t_teamId = {$team['team_id']}";
			$assoId = $this->_selectFirst($tables, 'asso_id', $where);
			$contact['ctac_type'] = WBS_EMAIL;
			$contact['ctac_assocId'] = $assoId;
			unset($contact['ctac_id']);
			$this->_insert('contacts', $contact);
		}
	}
	// }}}

	// {{{ getContact
	/**
	* Retourne le contact email  d'une assoc
	*
	* @access public
	* @return array   array of users
	*/
	function getContact($teamId, $ctacId)
	{
		if ($ctacId != -1)
		{
			$fields = array('ctac_id', 'ctac_contact', 'ctac_value');
			$where = "ctac_id = $ctacId";
			$res = $this->_select('contacts', $fields, $where);
			$data = $res->fetchRow(DB_FETCHMODE_ASSOC);
		}
		else
		$data = array('ctac_id'      => -1,
		      'ctac_contact' => '', 
		      'ctac_value'   => '');

		$fields = array('team_textconvoc');
		$where = "team_id = $teamId";
		$res = $this->_select('teams', $fields, $where);
		$team = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$data['asso_convoc'] = $team['team_textconvoc'];
		$data['team_id'] = $teamId;
		return $data;
	}
	// }}}

	// {{{ updateNum
	/**
	* Change the number of the matches
	*
	* @access public
	* @return array   array of users
	*/
	function updateNum($date, $place, $firstNum,
	$byVenue, $byDay)
	{
		$utd = new utDate();
		$utd->setFrDate($date);
		// Numeroter les match dans le calendrieir
		$eventId = utvars::getEventId();
		$fields = array('mtch_id', 'tie_place', 'tie_schedule', 'draw_id',
		    'rund_stamp', 'tie_posRound', 'tie_court');
		$tables = array('matchs', 'ties', 'rounds', 'draws');
		$where = 'mtch_tieId = tie_id'.
      ' AND tie_roundId=rund_id'.
      ' AND tie_isBye=0'.
      ' AND tie_court!=0'.
      ' AND rund_drawId=draw_id'.
      " AND draw_eventId=$eventId";
		if ($place != -1)
		{
			$place = addslashes($place);
			$where .= " AND tie_place = '$place'";
		}
		if ($date != -1)
		$where .= " AND date(tie_schedule) ='". $utd->getIsoDate()."'";
		$order = 'tie_place, tie_schedule, 0+tie_court, draw_id, rund_stamp, tie_posRound';
		$res = $this->_select($tables, $fields, $where, $order);
		$num = $firstNum;
		$salle  = -1;
		$date   = -1;
		$heure  = -1;
		$utd = new utdate();
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$utd->setIsoDateTime($tie['tie_schedule']);
			$matchDate = $utd->getDate();
			// Initialiser quand on change de salle ou de jour
			if ($salle != -1 && ($salle != $tie['tie_place'] ||
			$date != $matchDate))
	  {
	  	$heure = -1;
	  	// Calculer le nouveau numero de depart
	  	if($byVenue && $byDay &&
	  	( $salle != $tie['tie_place'] ||
	  	$date != $matchDate))
	  	{
	  		$salle = $tie['tie_place'];
	  		$date = $matchDate;
	  		$firstNum = intval($num/100)+1;
	  		$firstNum *= 100;
	  		$num = $firstNum+1;
	  	}
	  	else if($byVenue && $salle != $tie['tie_place'])
	  	{
	  		$salle = $tie['tie_place'];
	  		$firstNum = intval($num/100)+1;
	  		$firstNum *= 100;
	  		$num = $firstNum+1;
	  	}
	  	else if($byDay && $date != $matchDate)
	  	{
	  		$date = $matchDate;
	  		$firstNum = intval($num/100)+1;
	  		$firstNum *= 100;
	  		$num = $firstNum+1;
	  	}
	  }

	  // Premiere rencontre de la journee dans la salle
	  if ($heure == -1)
	  {
	  	$heure = $utd->getTime();
	  	$salle = $tie['tie_place'];
	  	$date = $matchDate;
	  }

	  $where = 'mtch_id='.$tie['mtch_id'];
	  $cols['mtch_num'] = $num;
	  $this->_update('matchs', $cols, $where);
	  $num++;
		}

		// Numeroter les matchs hors le calendrieir
		$eventId = utvars::getEventId();
		$fields = array('mtch_id', 'tie_place', 'tie_schedule', 'draw_id',
		    'rund_stamp', 'tie_posRound');
		$tables = array('matchs', 'ties', 'rounds', 'draws');
		$where = 'mtch_tieId = tie_id'.
      ' AND tie_roundId=rund_id'.
      ' AND tie_isBye=0'.
      ' AND tie_court=0'.
      ' AND rund_drawId=draw_id'.
      " AND draw_eventId=$eventId";
		$order = 'tie_place, tie_schedule, 0+tie_court, draw_id, rund_stamp, tie_posRound';
		$res = $this->_select($tables, $fields, $where, $order);
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$where = 'mtch_id='.$tie['mtch_id'];
			$cols['mtch_num'] = $num;
			$this->_update('matchs', $cols, $where);
			$num++;
		}

		// Mettre a zero les numero des matchs bye
		$fields = array('mtch_id');
		$tables = array('matchs', 'ties');
		$where = 'mtch_tieId = tie_id'.
      ' AND tie_isBye=1';
		$res = $this->_select($tables, $fields, $where);
		if ($res->numRows())
		{
			while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  $ids[] = $match['mtch_id'];
	  $fields = array();
	  $fields['mtch_num'] = 0;
	  $where = "mtch_id IN (".implode(',', $ids).")";
	  $this->_update('matchs', $fields, $where);
		}
	}
	//}}}

	// {{{ getTiesDate
	/**
	* Return the table with date of the ties
	*
	* @access public
	* @return array   array of users
	*/
	function getTiesDate()
	{
		$date = new utdate();
		// Firt select the ties of the day
		$eventId = utvars::getEventId();

		$fields = array("tie_schedule");
		$tables = array('ties', 'draws', 'rounds');
		$where = "tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId";
		$order = "tie_schedule";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoDate";
	  return $infos;
		}

		$dates[-1] = '-----';
		$utd = new utDate();
		while ($tmp = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
			$utd->setIsoDatetime($tmp[0]);
			$date = $utd->getDate();
	  $dates[$date] = $date;
		}
		return $dates;
	}
	// }}}


	// {{{ getTiesInfo
	/**
	* Return the table with place of the ties
	*
	* @access public
	* @return array   array of users
	*/
	function getTiesInfo($field)
	{
		// Firt select the ties of the day
		$eventId = utvars::getEventId();

		$fields = array($field);
		$tables = array('ties', 'draws', 'rounds');
		$where = "tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId";
		$order = "1";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNo$field";
	  return $infos;
		}

		$places[-1] = '-----';
		while ($tmp = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $places[$tmp[0]] = $tmp[0];
		}
		return $places;
	}
	// }}}

	// {{{ getRoundTies
	/**
	* Return the table with tie's data of a round
	*
	* @access public
	* @param  integer  $roundId  Id of the round
	* @return array   array of users
	*/
	function getRoundTies($roundId, $all)
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
		if ($round['rund_type'] != WBS_ROUND_GROUP)
		{
	  for($i=0; $i<count($ties); $i++)
	  {
	  	$tie = $ties[$i];
	  	if ($all || ($tie['tie_schedule'] =='') || ($tie['tie_schedule'] =='0000-00-00 00:00:00'))
	  	{
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
		}
		else
		{
	  for($i=0; $i<count($ties); $i++)
	  {
	  	$tie = $ties[$i];
	  	if ($all || ($tie['tie_schedule'] ==''))
	  	{
	  		$pos = $tie['tie_posRound'];
	  		$tie['tie_posRound'] = $tie['tie_step'];
	  		$tie['tie_step']= $utr->getTieStep($pos, WBS_ROUND_GROUP);
	  		$tie['tie_pbl'] = $uti->getPubliIcon($tie['tie_del'],
	  		$tie['tie_pbl']);
	  		$rows[] = $tie;
	  	}
	  }
		}
		return $rows;
	}
	// }}}



	// {{{ getTiesSchedu
	/**
	* Return the table with tie results
	*
	* @access public
	* @param  integer  $groupId  Id of the group
	* @return array   array of users
	*/
	function getTiesSchedu($groupId)
	{
		$uti = new utimg();
		$where = "rund_id = $groupId";
		$type = $this->_selectFirst('rounds', 'rund_type', $where);

		$where = "tie_roundId = $groupId";
		$nb = $this->_selectFirst('ties', 'count(*)', $where);

		// Firt select the ties of the group
		$fields = array('tie_id', 'tie_step', 'tie_schedule', 'tie_place',
		      'tie_court', 'tie_posRound', 'team_name', 'team_stamp', 
		      't2t_scoreW', 't2t_result',  'tie_pbl', 'tie_del');
		$tables = array('ties LEFT JOIN t2t ON t2t_tieId = tie_id
    LEFT JOIN teams ON t2t_teamId = team_Id');
		$where = " tie_roundId = '$groupId'";
		$order = "tie_step, tie_schedule, tie_place, tie_id, t2t_posTie";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
			$infos['errMsg'] = "msgNoTies";
			return $infos;
		}

		$ties = array();
		$tieId=-1;
		$utd = new utdate();
		$trans = array('tie_place', 'tie_step', 'tie_court');
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($tie['t2t_result'] == WBS_TIE_STEP) continue;
			unset($tie['t2t_result']);
			if ($tieId != $tie['tie_id'])
			{
				if ($tieId>=0)
				{
					$tmp = $this->_getTranslate('ties', $trans, $tieId, $tmp);
					$ties[] = $tmp;
				}
				$tmp = $tie;
				$utd->setIsoDate($tmp['tie_schedule']);
				$tmp['team_name'] .= " (".$tmp['team_stamp'].")";
				unset($tmp['team_stamp']);
				$tmp['tie_schedule']= $utd->getDateTime();
				$tmp['opponent'] = '';
				$tmp['score'] = $tmp['t2t_scoreW'];
				$pbl = $uti->getPubliIcon($tmp['tie_del'],
				$tmp['tie_pbl']);
				unset($tmp['t2t_scoreW']);
				unset($tmp['tie_del']);
				unset($tmp['tie_pbl']);
				if ($type == WBS_TEAM_BACK && $tmp['tie_posRound'] >= $nb/2)
				$tmp['tie_posRound'] = 'R';
				else
				$tmp['tie_posRound'] = 'A';
				$tmp['pbl'] = $pbl;
				$tieId = $tie['tie_id'];
			}
			else
			{
				$tmp['opponent'] = $tie['team_name'].
				" (".$tie['team_stamp'].")";
				$tmp['score'] .= '/'.$tie['t2t_scoreW'];
			}
		}
		$ties[] = $tmp;
		return $ties;
	}
	// }}}

	// {{{ updateSchedule
	/**
	* Register the schedule information for a tie
	*
	* @access public
	* @param  integer  $tieId    Id of the tie
	* @param  string   $schedule Date  of the tie
	* @param  string   $place    Place of the tie
	* @param  string   $step     Step  of the tie
	* @return array   array of users
	*/
	function updateSchedule($tiesId, $schedule, $place, $step, $court,
	$matchNum=-1)
	{
		// Update the ties of the group
		$in = implode(',', $tiesId);

		$fields = array();
		if (!is_null($schedule))
		$fields['tie_schedule'] = $schedule;
		if (!is_null($place))
		$fields['tie_place'] = $place;
		if (utvars::isTeamEvent() &&
		!is_null($step))
		$fields['tie_step'] = $step;
		if (!is_null($court))
		$fields['tie_court'] = $court;

		if (!count($fields)) return true;

		$trans = array('tie_place', 'tie_step', 'tie_court');
		foreach($tiesId as $tieId)
		{
	  $tmp = $fields;
	  $tmp = $this->_updtTranslate('ties', $trans, $tieId, $tmp);
	  $where = "tie_id = $tieId";
	  $res = $this->_update('ties', $tmp, $where);
	  if ($matchNum >=0)
	  {
	  	$cols['mtch_num'] = $matchNum;
	  	$where = "mtch_tieId = $tieId";
	  	$res = $this->_update('matchs', $cols, $where);
	  }
		}
		return true;
	}
	// }}}

	// {{{ reverseTeam
	/**
	* Reverse the team of a tie
	*
	* @access public
	* @param  integer  $tieId    Id of the ties
	* @return array   array of users
	*/
	function reverseTeam($tiesId)
	{
		// Update the ties of the group
		$in = implode(',', $tiesId);
		$fields = array('t2t_id', 't2t_posTie');
		$tables = array('t2t', 'rounds', 'ties');
		$where = "t2t_tieId in ($in)".
	" AND t2t_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND rund_type != ".WBS_TEAM_KO;

		$order = "t2t_tieId, 2";
		$res = $this->_select($tables, $fields, $where, $order);

		$fields = array();
		$status = WBS_TEAM_RECEIVER;
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  if ($tie['t2t_posTie'] == WBS_TEAM_RECEIVER)
	  $fields['t2t_posTie'] = WBS_TEAM_VISITOR;
	  else if ($tie['t2t_posTie'] == WBS_TEAM_VISITOR)
	  $fields['t2t_posTie'] = WBS_TEAM_RECEIVER;
	  else
	  {
	  	$fields['t2t_posTie'] = $status;
	  	$status = 1- $status + 2*WBS_TEAM_RECEIVER;
	  }
	  $where = "t2t_id =".$tie['t2t_id'];
	  $res2 = $this->_update('t2t', $fields, $where);
		}
		return true;
	}
	// }}}


	// {{{ getTieSchedule
	/**
	* Return the table with tie's data
	*
	* @access public
	* @param  integer  $groupId  Id of the group
	* @return array   array of users
	*/
	function getTieSchedule($tieId)
	{
		// Firt select the ties of the group
		$fields = array('tie_id', 'tie_step', 'tie_schedule',
		      'tie_place', 'tie_court', 'mtch_num');
		$tables = array('ties', 'matchs');
		$where = "tie_id = $tieId".
	" AND mtch_tieId = tie_id";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoTies";
	  return $infos;
		}

		$utd = new utdate();
		$tmp = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$utd->setIsoDate($tmp['tie_schedule']);
		$tmp['tie_schedule']= $utd->getDateTime();

		$trans = array('tie_place', 'tie_step', 'tie_court');
		$tmp = $this->_getTranslate('ties', $trans, $tieId, $tmp);

		return $tmp;
	}
	// }}}


	// {{{ getDateTies
	/**
	* Return the table with date of the ties of a division
	*
	* @access public
	* @param integer divId Id of the division
	* @return array   array of users
	*/
	function getDateTies($divId='', $place='')
	{
		// Firt select the ties of the day
		$eventId = utvars::getEventId();

		$fields = array('tie_schedule');
		$tables = array('ties', 'draws', 'rounds');
		$where = "tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId";
		if ($divId != '')
		$where .= " AND draw_id = $divId";
		if ($place != '')
		{
	  $place = addslashes($place);
	  $where .= " AND tie_place = '$place'";
		}
		$order = "1";

		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoCalendar";
	  return $infos;
		}
		$dates = array();
		$utd = new utDate();
		while ($tmp = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $utd->setIsoDate($tmp['tie_schedule']);
	  $fullDate = $utd->getDateTimeWithDay();
	  $dates[$utd->getDate()] = $fullDate;
		}
		return $dates;
	}
	// }}}


	// {{{ getTiesIndiv
	/**
	* Return the table with tie results
	*
	* @access public
	* @param  integer  $divId  Id of the division
	* @param  integer  $date   date of the ties
	* @param  integer  $sort     Sorting column
	* @return array   array of users
	*/
	function getTiesIndiv($date, $place)
	{
		$utd = new utDate();
		$utd->setFrDate($date);
		// Firt select the ties of the group
		$eventId = utvars::getEventId();
		$place = addslashes($place);
		$fields = array('tie_id', 'rund_name', 'tie_step', 'tie_place',
		      "TIME(tie_schedule) as time",
		      'rund_id', 'rund_stamp', 'draw_id', 'mtch_num',
      		      'tie_posRound', 'draw_name', 'rund_type', 
		      'tie_court', 'mtch_id', 'tie_court');
		$tables = array('rounds', 'draws', 'ties', 'matchs');
		$where = "draw_eventId = $eventId".
	" AND mtch_tieId = tie_id".
	" AND rund_drawId = draw_id".
	" AND tie_roundId = rund_id".
	" AND tie_place = '$place'".
	" AND tie_isBye = 0".
	" AND date(tie_schedule) ='". $utd->getIsoDate()."'";
		$order = "time, mtch_num, rund_stamp, tie_posRound";

		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoTies";
	  return $infos;
		}

		$time = "00:00:00";
		$utr = new utround();
		$ut = new utils();
		$fields = array('rund_name');
		$fields2 = array('tie_place','tie_step');
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $tie = $this->_getTranslate('rounds', $fields, $tie['rund_id'], $tie);
	  $tie = $this->_getTranslate('ties', $fields2, $tie['tie_id'], $tie);
	  if ($time != $tie['time'])
	  {
	  	if ($time!="00:00:00")
	  	{
	  		$cells[] = $lines;
	  		$rows[] = $cells;
	  	}
	  	$cells = array();
	  	$cells[] = $tie['tie_id'];
	  	$lines = array();
	  	$lines[0] = array('value' => substr($tie['time'],0,5),
				'class' => 'classTime');
	  	$cells[] = $lines;
	  	$lines = array();
	  	$tieId=-1;
	  	$time = $tie['time'];
	  }
	  if ($tieId != $tie['tie_id'])
	  {
	  	if ($tieId>=0) $cells[] = $lines;
	  	$lines = array();
	  	$lines[0] = array('value' => $tie['draw_name'],
				'class' => 'classStep');
	  	$step = $utr->getTieStep($tie['tie_posRound'], $tie['rund_type']);
	  	if ($tie['rund_type'] != WBS_ROUND_GROUP)
	  	$step = $ut->getLabel($step);

	  	$lines[1] = array('value' => $tie['rund_name']." ($step)",
				'class' => 'classGroup',
				'action' =>array(KAF_NEWWIN, 'schedu', 
	  	SCHEDU_EDIT, $tie['tie_id'], 620,350),
				);
				$lines[2] = array('value' => "NÂ° {$tie['mtch_num']}",
				'class' => 'classTie',
				'action' =>array(KAF_NEWWIN, 'matches', 
				KID_EDIT, $tie['mtch_id'], 620,350));
				$tieId = $tie['tie_id'];
	  }
	  else
	  {
	  	$line = $lines[1];
	  	$line['value'] .= " v ".$tie['tie_stamp'];
	  	$lines[1] = $line;
	  }
		}
		$cells[] = $lines;
		$rows[] = $cells;
		return $rows;
	}
	// }}}


	// {{{ getPlaces
	/**
	* Return the table with place of the ties
	*
	* @access public
	* @return array   array of users
	*/
	function getPlaces()
	{
		// Firt select the ties of the day
		$eventId = utvars::getEventId();

		$fields = array('tie_place');
		$tables = array('ties', 'draws', 'rounds');
		$where = "tie_roundId = rund_id".
	" AND tie_place != ''".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId";
		$order = "1";
		$res = $this->_select($tables, $fields, $where, $order);
		$places = array();
		/*      if (!$res->numRows())
		 {
	  $infos['errMsg'] = "msgNoPlaces";
	  return $infos;
	  }
	  */
		while ($tmp = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $places[$tmp[0]] = $tmp[0];
		}
		return $places;
	}
	// }}}


	// {{{ getTiesIndivPdf
	/**
	* Return the table with schedule of the tie for pdf
	*
	* @access public
	* @param  integer  $divId  Id of the division
	* @param  integer  $date   date of the ties
	* @param  integer  $sort     Sorting column
	* @return array   array of users
	*/
	function getTiesIndivPdf($date, $place)
	{
		$utd = new utDate();
		$utd->setFrDate($date);
		// Firt select the ties of the group
		$place = addslashes($place);
		$eventId = utvars::getEventId();
		$fields = array('tie_id', 'rund_name', 'tie_step', 'tie_place',
		      "time(tie_schedule) as time",
		      'rund_id', 'rund_stamp', 'draw_id', 'mtch_num',
      		      'tie_posRound', 'draw_name', 'rund_type', 'tie_court',
		      'draw_stamp', 'mtch_discipline', 'draw_serial', 'mtch_id');
		$tables = array('rounds', 'draws', 'ties', 'matchs');
		$where = "draw_eventId = $eventId".
	" AND mtch_tieId = tie_id".
	" AND rund_drawId = draw_id".
	" AND tie_roundId = rund_id".
	" AND tie_place = '$place'".
	" AND tie_isBye = 0".
	" AND date(tie_schedule) ='". $utd->getIsoDate()."'";
		$order = "time, mtch_num, rund_stamp, tie_posRound";

		$res = $this->_select($tables, $fields, $where, $order, 'tie');
		if (!$res->numRows())
		{
			$infos['errMsg'] = "msgNoTies";
			return $infos;
		}

		$time = "00:00:00";
		$utr = new utround();
		$ut = new utils();
		$fields = array('rund_name');
		$fields2 = array('tie_place','tie_step');
		$fields3 = array('draw_name','draw_stamp');
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$tie = $this->_getTranslate('rounds', $fields, $tie['rund_id'], $tie);
			$tie = $this->_getTranslate('ties', $fields2, $tie['tie_id'], $tie);
			$tie = $this->_getTranslate('draws', $fields3, $tie['draw_id'], $tie);
			if ($time != $tie['time'])
			{
				if ($time!="00:00:00")$rows[] = $cells;
				$cells = array();
				$cells['time'] = substr($tie['time'], 0, 5);
				$time = $tie['time'];
			}
			$oMatch = new objMatch($tie['mtch_id']);
			$lines['player1'] = $oMatch->getFirstTopName(false);
			$lines['player2'] = $oMatch->getSecondTopName(false);
			$lines['player3'] = $oMatch->getFirstBottomName(false);
			$lines['player4'] = $oMatch->getSecondBottomName(false);
			unset($oMatch);
			$lines['round'] = $tie['draw_stamp'];
			$lines['num']   = $tie['mtch_num'];
			$lines['disci'] = $tie['mtch_discipline'];
			$lines['serial'] = $tie['draw_serial'];
			$lines['drawId'] = $tie['draw_id'];
			$step = $utr->getTieStep($tie['tie_posRound'], $tie['rund_type']);
			if ($tie['rund_type'] != WBS_ROUND_GROUP)
			{
				$pos = $utr->getTiePos($tie['tie_posRound']);
				$lines['step']  = $tie['rund_name'];
				$lines['value'] = $ut->getLabel($step)." {$pos}";
			}
			else
			{
				$lines['step'] = $tie['rund_name'];
				$lines['value'] = 'Tour ' . $tie['tie_step']; //$step;
			}
			$cells[] = $lines;
		}
		$rows[] = $cells;
		return $rows;
	}
	// }}}


	/**
	 * Initialise les tableaux pour un echeancier automatique
	 *
	 * @access public
	 */
	function initAutoSchedu($infos)
	{
		//Liste des tableaux
		$eventId = utvars::getEventId();
		$fields = array('count(*)');
		$tables = array('draws');
		$where = "draw_eventId = $eventId";
		if( !empty($infos['drawIds']) )
		$where .= ' AND draw_id IN (' . implode(',', $infos['drawIds']) .')';
		$res = $this->_select('draws', $fields, $where);
		if (!$res->numRows()) return false;

		// Pour chaque rencontre Ko, calculer le nombre de tour
		$tables = array('draws', 'rounds', 'ties');
		$where = "draw_eventId = $eventId"
		. ' AND draw_id=rund_drawid'
		. ' AND rund_id=tie_roundid'
		. ' AND rund_type !=' . WBS_ROUND_GROUP
		;
		$res = $this->_select($tables, 'tie_id', $where);
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC)) $ids[] = $tie['tie_id'];
		if ( !empty($ids) )
		{
			$cols['tie_rge'] = '#CASE WHEN tie_posround=0 THEN 1
		WHEN tie_posround<3 THEN 2
		WHEN tie_posround<7 THEN 3
		WHEN tie_posround<15 THEN 4
		WHEN tie_posround<31 THEN 5
		WHEN tie_posround<63 THEN 6
		ELSE 7 END';
			$where = "tie_id IN (" . implode(',', $ids) . ')';
			$this->_update('ties', $cols, $where);
		}

		// Pour chaque rencontre de poule, calculer le nombre de tour
		$tables = array('draws', 'rounds', 'ties');
		$where = "draw_eventId = $eventId"
		. ' AND draw_id=rund_drawid'
		. ' AND rund_id=tie_roundid'
		. ' AND rund_type =' . WBS_ROUND_GROUP
		;
		$cols = array('tie_id', 'tie_step', 'draw_id', 'rund_group', 'rund_size');
		$res = $this->_select($tables, $cols, $where);
		$size[3] = array(1=>3,2,1);
		$size[4] = array(1=>3,2,1);
		$size[5] = array(1=>5,4,3,2,1);
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if (empty($groups[$tie['draw_id']][$tie['rund_group']]))
			$groups[$tie['draw_id']][$tie['rund_group']] = objgroup::getGroup($tie['draw_id'], $tie['rund_group']);
			$group = $groups[$tie['draw_id']][$tie['rund_group']];

			$tieId = $tie['tie_id'];
			$ucols['tie_rge'] = $size[$tie['rund_size']][$tie['tie_step']] + $group['nbQual'];
			$where = "tie_id = " . $tieId;
			$this->_update('ties', $ucols, $where);
		}


		// initialisation des variables
		$start = "{$infos['firstDay']} {$infos['start']}";
		$end = "{$infos['firstDay']} {$infos['break']}";
		$nbCourt = $infos['nbCourt'];
		$venue = $infos['sporthall'];
		$curTime = $start;
		$elaps = $infos['length'];

		$utCur = new utdate();
		$utCur->setFrDateTime($end);
		$end = $utCur->getIsoDateTime();
		$utCur->setFrDateTime($start);

		$utNoBefore = new utdate();
		$utNoBefore->setFrDateTime($start);
		$utNoBefore->addMinute($infos['length'] + $infos['rest']);

		// Remettre a zero l'heure des rencontres
		if($infos['raz'] == true) $this->razSchedu($venue, $infos['firstDay']);

		// Calcul de l'echeancier
		$numMatch = 1;
		$breakNobefore = 0;
		//print_r($infos);
		while (true)
		{
			if ($breakNobefore>10) break;

			// Passage au jour suivant
			if ($utCur->getDiff($end) < 0)
			{
				$infos['nbday']--;
				if( $infos['nbday'] <= 0 ) break;
				$utCur->setFrDate($infos['firstDay']);
				$utCur->addMinute(60*24);
				$nextDay = $utCur->getDate();
				if($infos['raz'] == true) $this->razSchedu($venue, $nextDay);
				$start = "$nextDay {$infos['start']}";
				$end = "$nextDay 19:00";
				$curTime = $start;

				$utCur->setFrDateTime($end);
				$end = $utCur->getIsoDateTime();
				$utCur->setFrDateTime($start);

				$utNoBefore->setFrDateTime($start);
				$utNoBefore->addMinute($infos['length'] + $infos['rest']);
			}

			// Liste des rencontre (match) dans l'ordre prioritaire
			// On va positionner les matchs pour le creneau courant uniquement.
			// On prend 10 fois plus de match que de terrains au cas ou un des 
			// macth serait repousse suite au placement d'un match precedent
			$fields = array('draw_nbMaxStep', 'rund_type',
			  'draw_nbGroupStep', 'draw_nbKoStep','draw_disci',
			  'draw_id', 'tie_step', 'rund_stamp', 'rund_group',
			  'draw_serial', 'tie_posround', 'tie_looserdrawid',
			  'draw_nbMatchStep', 'tie_nobefore',
			  'draw_catage','tie_id', 'rund_size', 'rund_entries', 
			  'draw_rge', 'tie_rge', 'rund_rge');
			$tables = array('draws', 'rounds', 'ties');
			$where = 'draw_id=rund_drawId'.
	    		' AND draw_eventId ='.utvars::getEventId().
	    		' AND rund_id = tie_roundId'.
	    		" AND (tie_schedule = '' ".
	    		" OR tie_schedule = '0000-00-00 00:00:00')".
	    		" AND tie_isBye = 0"
	    		. " AND tie_noBefore <= '".$utCur->getIsoDateTime()."'";
	    	if( !empty($infos['drawIds']) ) $where .= ' AND draw_id IN (' . implode(',', $infos['drawIds']) .')';
	    		//	. " AND draw_nbMaxStep > 0"
	    		//.	" AND rund_type=" . WBS_ROUND_MAINDRAW
	    		;
	    		//$order = "draw_rge, tie_rge DESC, rund_size DESC, tie_step, rund_rge DESC, draw_id, rund_group,  rund_stamp, tie_posround";
	    		$order = "draw_rge, tie_rge DESC, rund_rge DESC, draw_id, rund_group,  rund_stamp, tie_posround";
	    		$order .= ' LIMIT 0,' . 10*$nbCourt;
	    		$res = $this->_select($tables, $fields, $where, $order);
	    		//while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
	    		//{
	    		//print_r($match);
	    		//}
	    		//return;
	    		
	    		// Aucun match placable, verifier s'il reste des matchs a placer
	    		if(!$res->numRows())
	    		{
	    			$where = 'draw_id=rund_drawId'.
	    		' AND draw_eventId ='.utvars::getEventId().
	    		' AND rund_id = tie_roundId'.
	    		" AND (tie_schedule = '' ".
	    		" OR tie_schedule = '0000-00-00 00:00:00')".
	    		" AND tie_isBye = 0";
	    			$order = "tie_noBefore";
	    			$res = $this->_select($tables, $fields, $where, $order);
	    			// plus de match, c'est termine
	    			if(!$res->numRows()) break;
	    			// Il reste de match, fixe l'heure a plus tot
	    			$match = $res->fetchRow(DB_FETCHMODE_ASSOC);
	    			$utCur->setIsoDateTime($match['tie_nobefore']);
	    			$utNoBefore->setIsoDateTime($match['tie_nobefore']);
	    			$utNoBefore->addMinute($infos['length'] + $infos['rest']);
	    			$breakNobefore++;
	    			continue;
	    		}
	    		$numCourt  = 1;
	    		$stop = 0;
	    		$tieDelayedIds = array(); // Liste des rencontres repoussees

	    		// Traiter chaque match : les placer dans le creneau
	    		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
	    		{
	    			//print_r($match);
	    			if($stop++>20) {echo "stop"; break;}

	    			// La rencontre a ete repousse, on ne la traite pas
	    			// Par exemple la liste a donnée deux demi finale et finale,
	    			// Le traitement des demi repousse la finale.
	    			if ( in_array($match['tie_id'], $tieDelayedIds) ) continue;

	    			// La rencontre a ete repoussee a une heure ulterieure
	    			// On re initialise l'heure prevue a cette heure repoussee
	    			if ($match['tie_nobefore'] > $utCur->getIsoDateTime())
	    			{
	    				$utCur->setIsoDateTime($match['tie_nobefore']);
	    				$utNoBefore->setIsoDateTime($match['tie_nobefore']);
	    				$utNoBefore->addMinute($infos['length'] + $infos['rest']);
	    				$numCourt = 1;
	    			}

	    			// Mettre a jour l'heure de la rencontre
	    			$tie =array('tie_schedule' => $utCur->getIsoDateTime(),
							'tie_place' => $venue,
							'tie_court' => $numCourt++);
	    			$where = 'tie_id=' . $match['tie_id'];
	    			$this->_update('ties', $tie, $where);

	    			// Mettre a jour le numero du match
	    			$num['mtch_num'] = $numMatch++;
	    			$where = 'mtch_tieId=' . $match['tie_id'];
	    			$this->_update('matchs', $num, $where);

	    			// Mettre a jour l'heure au plus tot des rencontres 
	    			// concernees par celle traitee
	    			$tieIds = array($match['tie_id']); // Listes des rencontres a traiter; initialisée avec celle courante
	    			$drawId = $match['draw_id'];
	    			$group  = $match['rund_group'];
	    			$break = 0;
	    			while (true)
	    			{
	    				// Securité pour sortir de la boucle infinie
	    				if ($break++>100) {echo "break";break;}
	    				$tieId = array_shift($tieIds);

	    				// Plus de rencontre a traiter: fin
	    				if ( empty($tieId) ) break;

	    				// Traiter la premiere rencontre de la liste.
	    				// La rencontre initiale n'est  pas traitee. 
	    				// Sa date nobefore est anterieure a la date nobefore.
	    				$cols = array('tie_nobefore' => $utNoBefore->getIsoDateTime());
	    				$where = 'tie_id = ' . $tieId;
	    				$where .= " AND tie_noBefore <= '".$utNoBefore->getIsoDateTime()."'";
	    				$this->_update('ties', $cols, $where);

	    				// Infos concernant la rencontre
	    				$cols = array('tie_id', 'tie_roundid', 'tie_looserdrawid', 'tie_posround');
	    				$tie = $this->_selectFirst('ties', $cols, $where);

	    				// Chercher la rencontre du tour suivant et
	    				// la rajouter a la liste des rencontres a traiter
	    				// et la liste des rencontres repousees
	    				if ($tie['tie_posround'] > 0)
	    				{
	    					$posRound = ceil($tie['tie_posround']/2)-1;
	    					$tables = array('ties');
	    					$where = 'tie_roundid = ' . $tie['tie_roundid']
	    					. ' AND tie_posround =' . $posRound
	    					;
	    					$tieId = $this->_selectFirst($tables, 'tie_id', $where);
	    					if (!empty($tieId))
	    					{
	    						array_push($tieIds, $tieId);
	    						array_push($tieDelayedIds, $tieId);
	    					}
	    				}

	    				// Chercher la rencontre du plateau suivant et
	    				// le rajouter a la liste de rencontres a traiter
	    				if ($tie['tie_looserdrawid'] > 0)
	    				{
	    					// Calculer la position de la rencontre dans le plateau
	    					$roundId = $tie['tie_looserdrawid'];
	    					$destRoundSize = $this->_selectFirst('rounds', 'rund_size', 'rund_id='.$roundId);
	    					$limit = max(0, $destRoundSize -2);
	    					$posRound = $tie['tie_posround'];
	    					do
	    					{
	    						$posRound = ceil($posRound/2)-1;
	    					}
	    					while ($posRound > $limit);
	    						
	    					// Recuperer l'id de la rencontre
	    					$where = 'tie_roundid =' . $roundId
	    					. ' AND tie_posround =' . $posRound
	    					;
	    					$tieId = $this->_selectFirst($tables, 'tie_id', $where);
	    					if (!empty($tieId) && !in_array($tieId, $tieIds))
	    					{
	    						array_push($tieIds, $tieId);
	    						array_push($tieDelayedIds, $tieId);
	    					}
	    				}
	    				//print_r($tieIds);
	    				// Traiter les autre matchs des joueurs
						if ($match['rund_type'] == WBS_ROUND_GROUP)
						{
							// Joueurs du match
							$tm = array('matchs', 'p2m');
							$where = 'mtch_tieId=' . $match['tie_id'].
							' AND p2m_matchid=mtch_id';
	    					$pairIds = $this->_getRows($tm, 'p2m_pairid', $where);
	    					
	    					// Traiter chaque joueur
	    					foreach($pairIds as $pairId)
	    					{
								$tm = array('p2m', 'matchs');
								$where = 'p2m_matchid=mtch_id AND p2m_pairid=' . $pairId['p2m_pairid'];
	    						$tids = $this->_getRows($tm, 'mtch_tieid', $where);
	    						foreach($tids as $tieId)
	    						{
	    							if (!in_array($tieId['mtch_tieid'], $tieIds))
	    							{
	    								array_push($tieIds, $tieId['mtch_tieid']);
	    								array_push($tieDelayedIds, $tieId['mtch_tieid']);
	    							}
	    						}
	    					}
						}
						//print_r($tieIds);
	    			}

	    			// Fin du creneau horaire
	    			if($numCourt > $nbCourt) break;
	    		} // Fin while sur les match pour le creneau courant
	    		$utCur->addMinute($elaps);
	    		$utNoBefore->addMinute($elaps);
		}
		return true;
	}




	/**
	 * Initialise les tableaux pour un echeancier automatique
	 *
	 * @access public
	 */
	function initAutoScheduOld($infos)
	{
		//Liste des tableaux
		$eventId = utvars::getEventId();
		$fields = array('draw_id');
		$tables = array('draws');
		$where = "draw_eventId = $eventId";
		$order = "draw_serial, draw_disci";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows()) return false;


		$utd = new utdraw();
		while ($id = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$draw = $utd->getDrawById($id['draw_id']);
			$data['draw_id'] = $id['draw_id'];
			$data['draw_serial'] = $draw['draw_serial'];
			$data['draw_disci'] = $draw['draw_disci'];
			if ($draw['draw_nbpl'] < 2)
			{
				$data['draw_nbKoStep'] = 0;
				$data['draw_nbMatchStep'] = 0;
			}
			else if ($draw['draw_nbpl'] == 2)
			{
				$data['draw_nbKoStep'] = 1;
				$data['draw_nbMatchStep'] = 1;
			}
			else if ($draw['draw_nbpl'] <= 4)
			{
				$data['draw_nbKoStep'] = 2;
				$data['draw_nbMatchStep'] = $draw['draw_nbpl']-2;
				$data['draw_nbMatchStep'] = 2;
			}
			else if ($draw['draw_nbpl'] <= 8)
			{
				$data['draw_nbKoStep'] = 3;
				$data['draw_nbMatchStep'] = $draw['draw_nbpl']-4;
				$data['draw_nbMatchStep'] = 4;
			}
			else if ($draw['draw_nbpl'] <= 16)
			{
				$data['draw_nbKoStep'] = 4;
				$data['draw_nbMatchStep'] = $draw['draw_nbpl']-8;
				$data['draw_nbMatchStep'] = 8;
			}
			else if ($draw['draw_nbpl'] <= 32)
			{
				$data['draw_nbKoStep'] = 5;
				$data['draw_nbMatchStep'] = $draw['draw_nbpl']-16;
				$data['draw_nbMatchStep'] = 16;
			}
			else if ($draw['draw_nbpl'] <= 64)
			{
				$data['draw_nbKoStep'] = 6;
				$data['draw_nbMatchStep'] = $draw['draw_nbpl']-32;
				$data['draw_nbMatchStep'] = 32;
			}
			else if ($draw['draw_nbpl'] > 64)
			{
				$data['draw_nbKoStep'] = 7;
				$data['draw_nbMatchStep'] = $draw['draw_nbpl']-64;
				$data['draw_nbMatchStep'] = 64;
			}

			if($draw['draw_nbp5']>0)
			{
				$data['draw_nbGroupStep'] = 5;
				$data['draw_nbMatchStep'] = $draw['draw_nbp3'] +
				($draw['draw_nbp4'] + $draw['draw_nbp5'])*2;
			}
			else if($draw['draw_nbp3']+$draw['draw_nbp4']>0)
			{
				$data['draw_nbGroupStep'] = 3;
				$data['draw_nbMatchStep'] = $draw['draw_nbp3'] +
				($draw['draw_nbp4'] + $draw['draw_nbp5'])*2;
			}
			else $data['draw_nbGroupStep'] = 0;
			$data['draw_nbMaxStep'] = $data['draw_nbGroupStep'] + $data['draw_nbKoStep'];
			$draws[] = $data;
		}
		$nbDraws = count($draws);
		for($i=0; $i<$nbDraws; $i++)
		{
			$draw = $draws[$i];
			for($j=0; $j<$nbDraws; $j++)
			{
				if($i==$j) continue;
				if ($draw['draw_serial'] != $draws[$j]['draw_serial'])
				continue;
				if ($draw['draw_disci'] == WBS_XD)
				{
					$draw['draw_nbMaxStep'] += ($draws[$j]['draw_nbGroupStep']+$draws[$j]['draw_nbKoStep'])/2;
					//$draw['draw_nbMaxStep'] += ($draws[$j]['draw_nbGroupStep']+$draws[$j]['draw_nbKoStep']);
				}
				else
				{
					if ($draws[$j]['draw_disci'] == WBS_XD)
					{
						$draw['draw_nbMaxStep'] += ($draws[$j]['draw_nbGroupStep']+$draws[$j]['draw_nbKoStep']);
					}
					else
					if (($draw['draw_disci']== WBS_MS &&  $draws[$j]['draw_disci'] == WBS_MD) ||
					($draw['draw_disci']== WBS_MD &&  $draws[$j]['draw_disci'] == WBS_MS) ||
					($draw['draw_disci']== WBS_WS &&  $draws[$j]['draw_disci'] == WBS_WD) ||
					($draw['draw_disci']== WBS_WD &&  $draws[$j]['draw_disci'] == WBS_WS))
					{
						$draw['draw_nbMaxStep'] += ($draws[$j]['draw_nbGroupStep']+$draws[$j]['draw_nbKoStep']);
					}
				}
			}
			$draw['draw_noBefore'] = '';
			$where = "draw_id={$draw['draw_id']}";
			$this->_update('draws', $draw, $where);
			$draws[$i] = $draw;
		}

			
		$start = "{$infos['firstDay']} {$infos['start']}";
		$end = "{$infos['firstDay']} {$infos['break']}";
		$nbCourt = $infos['nbCourt'];
		$venue = $infos['sporthall'];
		$nbFreePos = $nbCourt;
		$curTime = $start;
		$elaps = $infos['length'];
		$utCur = new utdate();
		$utNoBefore = new utdate();
		$utCur->setFrDateTime($end);
		$end = $utCur->getIsoDateTime();
		$utCur->setFrDateTime($start);
		$utNoBefore->setFrDateTime($start);
		$utNoBefore->addMinute(2*$elaps);
		$numMatch = 1;
		$nbBreak = 0;

		// Remettre a zero l'heure des rencontres
		$tables = array('draws', 'rounds', 'ties');
		$where = 'draw_id=rund_drawId'.
	' AND draw_eventId ='.utvars::getEventId().
	' AND rund_id = tie_roundId';
		$res = $this->_select($tables, 'tie_id', $where);
		if ($res->numRows())
		{
			while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC)) $ids[] = $tie['tie_id'];

			$fields = array();
			$fields['tie_schedule'] = '';
			$fields['tie_place'] = '';
			$where = 'tie_id IN ('.implode(',', $ids).')';
			$this->_update('ties', $fields, $where);
		}
		//echo "<hr>".$utCur->getDateTime()."<br>";
		while (true)
		{
			if ($nbBreak > 3) break;
			if ($utCur->getDiff($end) < 0)
			{
				$utCur->setFrDate($infos['firstDay']);
				$utCur->addMinute(60*24);
				$nextDay = $utCur->getDate();
				$start = "$nextDay {$infos['again']}";
				$end = "$nextDay 19:00";
				$nbFreePos = $nbCourt;
				$curTime = $start;
				$utCur = new utdate();
				$utNoBefore = new utdate();
				$utCur->setFrDateTime($end);
				$end = $utCur->getIsoDateTime();
				$utCur->setFrDateTime($start);
				$utNoBefore->setFrDateTime($start);
				$utNoBefore->addMinute($elaps);
				$nbBreak = 0;
			}

	  // Liste des rencontre (match) dans l'ordre prioritaire
			$fields = array('draw_nbMaxStep', 'rund_type',
			  'draw_nbGroupStep', 'draw_nbKoStep','draw_disci',
			  'draw_id', 'tie_step', 'rund_stamp',
			  'draw_serial', 
			  'draw_nbMatchStep', 
			  'draw_catage','tie_id', 'rund_size', 'rund_entries');
			$tables = array('draws', 'rounds', 'ties');
			$where = 'draw_id=rund_drawId'.
	    		' AND draw_eventId ='.utvars::getEventId().
	    		' AND rund_id = tie_roundId'.
	    		" AND (tie_schedule = '' ".
	    		" OR tie_schedule = '0000-00-00 00:00:00')".
	    		" AND tie_isBye = 0".
	    		" AND draw_nbMaxStep > 0".
	    		" AND draw_noBefore <= '".$utCur->getIsoDateTime()."'";
			$order = "draw_nbMaxStep DESC, draw_nbKoStep+draw_nbGroupStep DESC, draw_nbGroupStep DESC, draw_disci DESC, rund_type, draw_id, tie_step, rund_stamp";
			$res = $this->_select($tables, $fields, $where, $order);

			// Aucun match: passer a la plage horaire suivante
			if(!$res->numRows())
			{
				$utCur->addMinute($elaps);
				$utNoBefore->addMinute($elaps);
				$nbFreePos = $nbCourt;
				$nbBreak++;
				continue;
			}

	  // Premier match : recuperer les infos du tableau
			$match = $res->fetchRow(DB_FETCHMODE_ASSOC);
			$elaps = $infos['length'];
			$draw = array();
			$draw['draw_id'] = $match['draw_id'];
			$draw['draw_disci'] = $match['draw_disci'];
			$draw['draw_serial'] = $match['draw_serial'];
			$draw['draw_nbMaxStep'] = $match['draw_nbMaxStep']-1;
			if ($match['draw_nbGroupStep'])
			{
				$draw['draw_nbGroupStep'] = $match['draw_nbGroupStep']-1;
				$nbMatch = $match['draw_nbMatchStep'];
			}
			else if ($match['draw_nbKoStep'])
			{
				$draw['draw_nbKoStep'] = $match['draw_nbKoStep']-1;
				if ($match['tie_step']==0)
				{
					$nbMatch = $match['rund_entries'] - ($match['rund_size']/2);
					$draw['draw_nbMatchStep'] = $match['rund_size']/4;
				}
				else
				{
					$nbMatch = $match['draw_nbMatchStep'];
					$draw['draw_nbMatchStep'] = $match['draw_nbMatchStep']/2;
				}
			}

	  // Mettre a jour l'heure de chaque rencontre
	  // et le numero du match
			while ($match && $nbMatch > 0)
			{
				$tie['tie_schedule'] = $utCur->getIsoDateTime();
				$tie['tie_place'] = $venue;
				$tie['tie_court'] = $nbCourt - $nbFreePos + 1;
				$where = "tie_id={$match['tie_id']}";
				$this->_update('ties', $tie, $where);

				$num['mtch_num'] = $numMatch;
				$where = "mtch_tieId={$match['tie_id']}";
				$this->_update('matchs', $num, $where);

				$numMatch++;
				$nbFreePos--;
				$nbMatch--;
				if (!$nbFreePos)
				{
					$nbFreePos = $nbCourt;
					$utCur->addMinute($elaps);
					$utNoBefore->addMinute($elaps);
				}
				$match = $res->fetchRow(DB_FETCHMODE_ASSOC);
			}

	  // Mettre a jour le tableau
			$draw['draw_noBefore'] = $utNoBefore->getIsoDateTime();
			$where = "draw_id={$draw['draw_id']}";
			$this->_update('draws', $draw, $where);

	  // Mettre a jour les tableaux de la meme serie
			$cols['draw_noBefore'] = $utNoBefore->getIsoDateTime();
			if ($draw['draw_disci'] == WBS_MS)
			{
				$where = "draw_serial='{$draw['draw_serial']}'".
					" AND draw_disci =".WBS_MD.
					" AND draw_eventId=".utvars::getEventId();
				$cols['draw_nbMaxStep'] = 'eval(draw_nbMaxStep - 1)';
				$this->_update('draws', $cols, $where);
				$where = "draw_serial='{$draw['draw_serial']}'".
					" AND draw_disci =".WBS_XD.
					" AND draw_eventId=".utvars::getEventId();
				$cols['draw_nbMaxStep'] = 'eval(draw_nbMaxStep - 0.5)';
				$this->_update('draws', $cols, $where);
			}
			if ($draw['draw_disci'] == WBS_MD)
			{
				$where = "draw_serial='{$draw['draw_serial']}'".
					" AND draw_disci =".WBS_MS.
					" AND draw_eventId=".utvars::getEventId();
				$cols['draw_nbMaxStep'] = 'eval(draw_nbMaxStep - 1)';
				$this->_update('draws', $cols, $where);
				$where = "draw_serial='{$draw['draw_serial']}'".
					" AND draw_disci =".WBS_XD.
					" AND draw_eventId=".utvars::getEventId();
				$cols['draw_nbMaxStep'] = 'eval(draw_nbMaxStep - 0.5)';
				$this->_update('draws', $cols, $where);
			}
			if ($draw['draw_disci'] == WBS_WS)
			{
				$where = "draw_serial='{$draw['draw_serial']}'".
						" AND draw_disci =".WBS_WD.
						" AND draw_eventId=".utvars::getEventId();
				$cols['draw_nbMaxStep'] = 'eval(draw_nbMaxStep - 1)';
				$this->_update('draws', $cols, $where);
				$where = "draw_serial='{$draw['draw_serial']}'".
					" AND draw_disci =".WBS_XD.
					" AND draw_eventId=".utvars::getEventId();
				$cols['draw_nbMaxStep'] = 'eval(draw_nbMaxStep - 0.5)';
				$this->_update('draws', $cols, $where);
			}
			if ($draw['draw_disci'] == WBS_WD)
			{
				$where = "draw_serial='{$draw['draw_serial']}'".
					" AND draw_disci =".WBS_WS.
					" AND draw_eventId=".utvars::getEventId();
				$cols['draw_nbMaxStep'] = 'eval(draw_nbMaxStep - 1)';
				$this->_update('draws', $cols, $where);
				$where = "draw_serial='{$draw['draw_serial']}'".
					" AND draw_disci =".WBS_XD.
					" AND draw_eventId=".utvars::getEventId();
				$cols['draw_nbMaxStep'] = 'eval(draw_nbMaxStep - 0.5)';
				$this->_update('draws', $cols, $where);
			}
			if ($draw['draw_disci'] == WBS_XD)
			{
				$where = "draw_serial='{$draw['draw_serial']}'".
					" AND draw_disci !=".WBS_XD.
					" AND draw_eventId=".utvars::getEventId();
				$cols['draw_nbMaxStep'] = 'eval(draw_nbMaxStep - 1)';
				$this->_update('draws', $cols, $where);
			}
		}
		return true;
	}

	/**
	 * insere des matchs dans l"echancier
	 *
	 * @access public
	 */
	function insertMatch($tiesList, $position, $nbCourt, $aLength)
	{
		$utd = new utdate();
		$utd->setFrDateTime($position['frDateTime']);
		$court = $position['court'];
		$venue = addslashes($position['venue']);
		$date  = $utd->getDate();
		$time  = $utd->getTime();

		if (is_array($tiesList)) $tieIds = $tiesList;
		else $tieIds[] = $tiesList;

		// Recuperer les rencontres correspondantes aux matchs
		$ids = implode(',', $tieIds);
		$fields = array('tie_id', 'tie_schedule', 'tie_court', 'tie_place');
		$tables = array('ties');
		$where = "tie_id IN ($ids)";
		$res = $this->_select($tables, $fields, $where);
		$newTies = array();
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$tie['tie_court'] = 0;
			$tie['tie_schedule'] = '';
			$tie['tie_convoc'] = '';
			$newTies[] = $tie;
		}
		$nbTies = count($newTies);
		// Ajout d'une pause
		if (!is_array($tiesList) && $tiesList==-1) $nbTies = 1;

		// Recuperer les rencontres deja dans l'echeancier
		$fields = array('tie_id', 'tie_schedule', 'tie_court', 'tie_place', 'tie_convoc');
		$tables = array('ties', 'draws', 'rounds');
		$where = "draw_id = rund_drawId".
				" AND rund_id = tie_roundId".
				" AND draw_eventId = ".utvars::getEventId().
				" AND DATE(tie_schedule) = '" . $utd->getIsoDate() . "'".
				" AND ((TIME(tie_schedule) = '$time:00'".
				" AND tie_court >= $court)".
				" OR TIME(tie_schedule) > '$time:00')".
				" AND tie_id not IN ($ids)".
				" AND tie_place = '$venue'";
		$order = 'tie_schedule, 0+tie_court';
		$res = $this->_select($tables, $fields, $where, $order);
		$times = array();
		$ties = array();
		$courtTrv = $court;
		$schedule = $utd->getIsoDateTime();
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($tie['tie_court'] > $nbCourt) $nbCourt = $tie['tie_court'];
			// ajouter les positions vides s'il y en a
			while($courtTrv != $tie['tie_court'])
			{
				if (empty($schedule)) $schedule = $tie['tie_schedule'];
				$tmp = array('tie_court' => $courtTrv++,
				'tie_schedule' => $schedule,
				'tie_convoc' => null,
				'tie_id' => -1);
				$ties[] = $tmp;
				if($courtTrv > $nbCourt)
				{
					$courtTrv = 1;
					$schedule = '';
				}
			}
			// Memoriser la plage horaire
			$schedule = $tie['tie_schedule'];
			$utd->setIsoDateTime($tie['tie_schedule']);
			$tmp = $utd->getTime();
			if (!in_array($tmp, $times)) $times[] = $tmp;

			// Memoriser la rencontre
			$ties[] = $tie;
			$courtTrv++;
			if($courtTrv > $nbCourt)
			{
				$courtTrv = 1;
				$schedule = '';
			}

		}
		// Traiter toutes les rencontres a inserer
		// Calculer l'heure de depart
		if (reset($times) == $time) array_shift($times);
		$utc = new utDate();
		$utd->setFrDateTime("$date $time");
		$utc->setFrDateTime("$date $time");
		$utc->addMinute('45');

		// Positionner les nouveaux matchs
		foreach($newTies as $tie)
		{
			$tie['tie_place'] = $venue;
			$tie['tie_court'] = $court;
			$tie['tie_schedule'] = $utd->getIsoDateTime();
			$tie['tie_convoc'] = $utc->getIsoDateTime();
			$where = "tie_id={$tie['tie_id']}";
			$this->_update('ties', $tie, $where);

			$court++;
			if($court > $nbCourt)
			{
				$court = 1;
				$time = array_shift($times);
				if ( !empty($time))
				{
					$utd->setFrDateTime("$date $time");
					$utc->setFrDateTime("$date $time");
					$utc->addMinute(45);
				}
				else
				{
					$utd->addMinute($aLength);
					$utc->addMinute($aLength);
				}
			}

		}

		// Deplacer les anciens matchs
		$ind = $nbTies;
		$select = 0;
		foreach($ties as $tie)
		{
			if ( isset($ties[$ind]) )
			{

				$court = $ties[$ind]['tie_court'];
				$schedule = $ties[$ind]['tie_schedule'];
				$convoc = $ties[$ind]['tie_convoc'];
				$ind++;
			}
			else
			{
				$court++;
				if($court > $nbCourt)
				{
					$court = 1;
					$utd->setIsoDateTime($schedule);
					$utd->addMinute($aLength);
					$schedule = $utd->getIsoDateTime();
					$utd->addMinute(45);
					$convoc = $utd->getIsoDateTime();
				}
			}

			if ($select == 0)	
			$select = substr($schedule, 11, 5)  . ";" . $court . ";" . $tie['tie_id'] . ";-1";
			if($tie['tie_id'] != -1)
			{
				$tie['tie_court'] = $court;
				$tie['tie_schedule'] = $schedule;
				$tie['tie_convoc'] = $convoc;
				$where = "tie_id={$tie['tie_id']}";
				$this->_update('ties', $tie, $where);
			}
		}
		if ($select==0)
		{
			$schedule = $utd->getIsoDateTime();
			$select = substr($schedule, 11, 5)  . ";" . $court . ";-1;-1";
		}
		return $select;
	}

	/**
	 * remplace des matchs dans l"echancier
	 *
	 * @access public
	 */
	function replaceMatch($tiesList, $position, $nbCourt)
	{
		$utd = new utdate();
		$utd->setFrDateTime($position['frDateTime']);
		$court = $position['court'];
		$venue = addslashes($position['venue']);
		$date  = $utd->getDate();
		$time  = $utd->getTime();

		if (is_array($tiesList)) $tieIds = $tiesList;
		else $tieIds[] = $tiesList;

		// Recuperer les rencontres correspondantes aux matchs a placer
		$ids = implode(',', $tieIds);
		$fields = array('tie_id', 'tie_schedule', 'tie_court', 'tie_place');
		$tables = array('ties');
		$where = "tie_id IN ($ids)";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows()) return;
		$newTies = array();
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$tie['tie_court'] = 0;
			$tie['tie_schedule'] = '';
			$newTies[] = $tie;
		}
		$nbNewTies = count($newTies);

		// Recuperer les rencontres deja dans l'echeancier
		$fields = array('tie_id', 'tie_schedule', 'tie_court',
		      'tie_place');
		$tables = array('ties', 'draws', 'rounds');
		$where = "draw_id = rund_drawId".
				" AND rund_id = tie_roundId".
				" AND draw_eventId = ".utvars::getEventId().
				" AND DATE(tie_schedule) = '" . $utd->getIsoDate() . "'".
				" AND ((TIME(tie_schedule) = '$time:00'".
				" AND tie_court >= $court)".
				" OR TIME(tie_schedule) > '$time:00')".
				" AND tie_id not IN ($ids)".
				" AND tie_place = '$venue'";
		$order = 'tie_schedule, 0+tie_court';
		$res = $this->_select($tables, $fields, $where, $order);
		$times = array();
		$ties = array();
		$courtTrv = $court;
		$schedule = $utd->getIsoDateTime();
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($tie['tie_court'] > $nbCourt) $nbCourt = $tie['tie_court'];
			// ajouter les positions vides s'il y en a
			while($courtTrv != $tie['tie_court'])
			{
				if (empty($schedule)) $schedule = $tie['tie_schedule'];
				$tmp = array('tie_court' => $courtTrv++,
				'tie_schedule' => $schedule,
				'tie_id' => -1);
				$ties[] = $tmp;
				if($courtTrv > $nbCourt)
				{
					$courtTrv = 1;
					$schedule = '';
				}
			}
			// Memoriser la plage horaire
			$schedule = $tie['tie_schedule'];
			$utd->setIsoDateTime($tie['tie_schedule']);
			$tmp = $utd->getTime();
			if (!in_array($tmp, $times)) $times[] = $tmp;

			// Memoriser la rencontre
			$ties[] = $tie;
			$courtTrv++;
			if($courtTrv > $nbCourt)
			{
				$courtTrv = 1;
				$schedule = '';
			}
		}
		// Enlever les anciens matches
		for( $i=0; $i<$nbNewTies; $i++)
		{
			if(!empty($ties[$i]))
			{
				$tie = $ties[$i];
				if($tie['tie_id'] > 0)
				{
					$tie['tie_place'] = '';
					$tie['tie_court'] = '';
					$tie['tie_schedule'] = NULL;
					$where = "tie_id={$tie['tie_id']}";
					$this->_update('ties', $tie, $where);
				}
			}
		}

		// Traiter toutes les rencontres
		// Calculer l'heure de depart
		if (reset($times) == $time) array_shift($times);
		$utd->setFrDateTime("$date $time");
		foreach($newTies as $tie)
		{
			// Placer le nouveau macth
			$tie['tie_place'] = $venue;
			$tie['tie_court'] = $court;
			$tie['tie_schedule'] = $utd->getIsoDateTime();
			$where = "tie_id={$tie['tie_id']}";
			$this->_update('ties', $tie, $where);
			$select = substr($tie['tie_schedule'], 11, 5)  . ";" . $court . ";" . $tie['tie_id'] . ";-1";

			// Terrain suivant
			$court++;
			if($court > $nbCourt)
			{
				$court = 1;
				if (count($times))
				{
					$time = array_shift($times);
					$utd->setFrDateTime("$date $time");
				}
				else $utd->addMinute(30);
			}
		}
		return $select;
	}

	/**
	 * Inverse deux match
	 *
	 * @access public
	 */
	function invertMatch($aMatchsDef)
	{
		$def = $aMatchsDef[0];
		list($time1, $court1, $tie1) = explode(';', $def);
		$def = $aMatchsDef[1];
		list($time2, $court2, $tie2) = explode(';', $def);
		if ($court1 == -1 || $court2==-1) return; // Une heure choisie
		if ($tie1 == -1 && $tie2==-1) return; // Deux pauses choisies

		$select = $time2  . ";" . $court2 . ";" . $tie1 . ";-1";

		// Recuperer la date
		$fields = array('tie_schedule');
		$tables = array('ties');
		if ($tie1>0) $where = "tie_id = " . $tie1;
		else $where = "tie_id = " . $tie2;
		$schedule = $this->_selectFirst($tables, $fields, $where);
		$date = substr($schedule, 0, 10);

		if ($tie1>0)
		{
			$tie['tie_court'] = $court2;
			$tie['tie_schedule'] = "$date $time2";
			$where = "tie_id=" . $tie1;
			$this->_update('ties', $tie, $where);
		}
		if ($tie2>0)
		{
			$tie['tie_court'] = $court1;
			$tie['tie_schedule'] = "$date $time1";
			$where = "tie_id=" . $tie2;
			$this->_update('ties', $tie, $where);
		}

		return $select;
	}


	/**
	 * enleve les match de l"echeancier sans decalage
	 *
	 * @access public
	 */
	function deleteMatch($aTieId, $position, $nbCourt)
	{
		// Recuperer les rencontres correspondantes aux matchs
		if($aTieId > 0)
		{
			$select = substr($position['frDateTime'], 11, 5)  . ";" . $position['court'] . ";-1;-1";
			$tie['tie_place'] = '';
			$tie['tie_court'] = '';
			$tie['tie_schedule'] = NULL;
			$tie['tie_convoc'] = NULL;
			$where = "tie_id=$aTieId";
			$this->_update('ties', $tie, $where);
		}
		else
		{
			$utd = new utDate();
			$utd->setFrDateTime($position['frDateTime']);
			$court = $position['court'];
			if($court==-1) return;
			$venue = addslashes($position['venue']);
			$time  = $utd->getTime();

			// Recuperer les rencontres deja dans l'echeancier
			$fields = array('tie_id', 'tie_schedule', 'tie_court', 'tie_place', 'tie_convoc');
			$tables = array('ties', 'draws', 'rounds');
			$where = "draw_id = rund_drawId".
				" AND rund_id = tie_roundId".
				" AND draw_eventId = ".utvars::getEventId().
				" AND DATE(tie_schedule) = '" . $utd->getIsoDate() . "'".
				" AND ((TIME(tie_schedule) = '$time:00'".
				" AND tie_court >= $court)".
				" OR TIME(tie_schedule) > '$time:00')".
				" AND tie_place = '$venue'";
			$order = 'tie_schedule, 0+tie_court';
			$res = $this->_select($tables, $fields, $where, $order);
			$ties = array();
			$courtTrv = $court;
			$schedule = $utd->getIsoDateTime();
			while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if ($tie['tie_court'] > $nbCourt) $nbCourt = $tie['tie_court'];
				// ajouter les positions vides s'il y en a
				while($courtTrv != $tie['tie_court'])
				{
					if (empty($schedule)) $schedule = $tie['tie_schedule'];
					$tmp = array('tie_court' => $courtTrv++,
						'tie_schedule' => $schedule,
						'tie_id' => -1,
						'tie_convoc' => null);
					$ties[] = $tmp;
					if($courtTrv > $nbCourt)
					{
						$courtTrv = 1;
						$schedule = '';
					}
				}

				// Memoriser la rencontre
				$schedule = $tie['tie_schedule'];
				$ties[] = $tie;
				$courtTrv++;
				if($courtTrv > $nbCourt)
				{
					$courtTrv = 1;
					$schedule = '';
				}
			}

			// Decaler les rencontres
			$ind = -1;
			$tieSelect = 0;
			foreach($ties as $tie)
			{
				if ( isset($ties[$ind]) )
				{
					if ($tieSelect == 0) $tieSelect=$tie['tie_id'];
					if ($tie['tie_id'] != -1)
					{
						$tie['tie_court'] = $ties[$ind]['tie_court'];
						$tie['tie_schedule'] = $ties[$ind]['tie_schedule'];
						$tie['tie_convoc'] = $ties[$ind]['tie_convoc'];
						$where = "tie_id={$tie['tie_id']}";
						$this->_update('ties', $tie, $where);
					}
				}
				$ind++;
			}
			$select = substr($position['frDateTime'], 11, 5)  . ";" . $position['court'] . ";$tieSelect;-1";

		}
		return $select;
	}

	// {{{ _trace
	/**
	* Trace de debug
	*
	* @access public
	*/
	function _trace($serial)
	{
		echo "trace $serial<br>\n";
		//if ($serial != 'cadet') return;
		// Liste des rencontre (match) dans l'ordre prioritaire
		$fields = array('draw_id', 'draw_serial', 'draw_disci',
		      'draw_nbGroupStep', 'draw_nbKoStep',
		      'draw_nbMaxStep', 'draw_nbMatchStep', 'draw_noBefore');
		$tables = array('draws');
		$where = "draw_serial='$serial'".
	' AND draw_eventId ='.utvars::getEventId();
		$res = $this->_select($tables, $fields, $where);

		while ($draw = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			print_r($draw);
			echo "<br>";
		}
	}
	// }}}


	// {{{ razSchedu
	/**
	* Return the list of the matches of a draw
	*
	* @access public
	* @param  integer  $roundId  Id of the round
	* @return array   array of users
	*/
	function razSchedu($venue, $day)
	{
		$utd = new utDate();
		$utd->setFrDate($day);

		// Selectionner les matches concernes
		$fields = array('tie_id', 'mtch_id');
		$venue = addslashes($venue);
		$where = "draw_id = rund_drawId".
	" AND tie_place='$venue'".
	" AND DATE(tie_schedule) = '". $utd->getIsoDate() . "'".
	" AND rund_id = tie_roundId".
	" AND tie_id = mtch_tieId".
	" AND draw_eventId = ".utvars::getEventId();
		$tables = array('draws', 'rounds', 'ties', 'matchs');
		$res = $this->_select($tables, $fields, $where);
		if ($res->numRows())
		{
			$num = 0;
			$fields = array();
			$fields['tie_schedule'] = '0000-00-00 00:00:00';
			$fields['tie_convoc'] = '0000-00-00 00:00:00';
			$fields['tie_nobefore'] = '0000-00-00 00:00:00';
			$fields['tie_place'] = '';
			$fields['tie_court'] = 0;

			$fieldsm = array();
			$fieldsm['mtch_num'] = 0;

			while (($tie = $res->fetchRow(DB_FETCHMODE_ASSOC)))
			{
				if ($num == 50)
				{
					// Mise a jour de l'heure des rencontres
					$where = "tie_id IN(".implode(',', $ties).')';
					$this->_update('ties', $fields, $where);

					// Mise a jour du numero des matches
					$where = "mtch_id IN(".implode(',', $matches).')';
					$this->_update('matchs', $fieldsm, $where);
					$num = 0;
				}
				$ties[$num] = $tie['tie_id'];
				$matches[$num] = $tie['mtch_id'];
				$num++;
			}
			// Mise a jour de l'heure des rencontres
			$where = "tie_id IN(".implode(',', $ties).')';
			$this->_update('ties', $fields, $where);

			// Mise a jour du numero des matches
			$where = "mtch_id IN(".implode(',', $matches).')';
			$this->_update('matchs', $fieldsm, $where);
		}
	}

	// {{{ getMatches
	/**
	* Return the list of the matches of a draw
	*
	* @access public
	* @param  integer  $roundId  Id of the round
	* @return array   array of users
	*/
	function getMatches($drawId)
	{
		if ($drawId == '') return array();
		// Select the matches of the draw
		$fields = array('mtch_id', 'mtch_num',
		      'tie_id', 'tie_place', 'tie_schedule',
		      'tie_step', 'tie_court', 'tie_posRound',
		      'rund_type', 'rund_size', 'rund_stamp',
		      'rund_id', 'draw_stamp', 'draw_id', 'rund_group', 'tie_convoc');
		$tables = array('matchs', 'ties', 'rounds', 'draws' );
		$where = "mtch_tieId = tie_id".
	" AND tie_isBye=0".
	" AND tie_roundId=rund_id".
	" AND rund_drawId=draw_id".
	" AND (tie_court = 0 OR".
	" tie_court IS NULL OR".
	" tie_place = '')";
		if ($drawId != -1) $where .= " AND draw_id=$drawId";

		// Match de poule
		$ou = "$where AND rund_type =".WBS_ROUND_GROUP;
		$order = 'draw_id, rund_group, rund_type, tie_step, rund_stamp, tie_posRound';
		$res = $this->_select($tables, $fields, $ou, $order);

		// Update step data
		$matchs = array();
		$step    = -1;
		$drawId  = -1;
		$roundId = -1;
		$group   = '';
		$ut = new utils();
		$utr = new utround();
		$label = '';
		require_once 'utils/objmatch.php';


		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$objMatch = new objmatch($match['mtch_id']);
			$tmp = $objMatch->getFirstTopName(false, true) . "-" . $objMatch->getSecondTopName(false, true) .' / '
			.  $objMatch->getFirstBottomName(false, true) . "-" . $objMatch->getSecondBottomName(false, true);
			if ($match['draw_id'] != $drawId)
			{
				$matchs["{$match['draw_id']};{$match['rund_group']};-1;-1;-1"] =
				array('value'=>$match['draw_stamp'], 'class'=>'draw');
				$drawId = $match['draw_id'];
			}

			if ($step != $match['tie_step'])
			{
				$step = $match['tie_step'];
				if ($match['rund_group'] == 'Principal') $value = " Tour $step";
				else $value = $match['rund_group'] . " - Tour $step";
				$matchs["{$match['draw_id']};{$match['rund_group']};$step;-1;{$match['rund_id']}"] =
				array('value'=>$value, 'class'=>'step');
			}
			$label = $utr->getTieStep($match['tie_posRound'], $match['rund_type']);
			$matchs["{$match['draw_id']};{$match['rund_group']};{$match['tie_step']};{$match['tie_id']};{$match['rund_id']}"] =
			array('value' => "{$match['rund_stamp']} $label",
		      'class' => 'match',
	          'title' => $tmp);
			unset($objMatch);
		}

		// Match de KO
		$ou = "$where AND rund_type !=".WBS_ROUND_GROUP;
		$order = 'draw_id, rund_group, rund_id, rund_type, tie_step, rund_stamp, tie_posRound';
		$res = $this->_select($tables, $fields, $ou, $order);
		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$objMatch = new objmatch($match['mtch_id']);
			$tmp = $objMatch->getFirstTopName(false, true) . "-" . $objMatch->getSecondTopName(false, true) .' / '
			.  $objMatch->getFirstBottomName(false, true) . "-" . $objMatch->getSecondBottomName(false, true);
			if ($match['draw_id'] != $drawId)
			{
				$matchs["{$match['draw_id']};{$match['rund_group']};-1;-1;-1"] =
				array('value'=>$match['draw_stamp'], 'class'=>'draw');
				$drawId = $match['draw_id'];
			}
			if ($step != $match['tie_step'] || $match['rund_id'] != $roundId)
			{
				$step = $match['tie_step'];
				$roundId = $match['rund_id'];
				$phase = $utr->getTieStep($match['tie_posRound'], $match['rund_type']);

				if ($match['rund_group'] == 'Principal') $value = $match['rund_stamp'] . ' - ' . $ut->getLabel($phase);
				else $value = $match['rund_group'] . ' - ' . $match['rund_stamp'] . ' - ' . $ut->getLabel($phase);

				$matchs["{$match['draw_id']};{$match['rund_group']};$step;-1;{$match['rund_id']}"] =
				array('value'=>$value, 'class'=>'step');
			}
			$label = $match['tie_posRound'];
			if(!$match['tie_posRound'])	$label = 1;
			else if($match['tie_posRound']<3)  $label = $match['tie_posRound'];
			else if($match['tie_posRound']<7)  $label -= 2;
			else if($match['tie_posRound']<15) $label -= 6;
			else if($match['tie_posRound']<31) $label -= 14;
			else if($match['tie_posRound']<63) $label -= 30;

			//$label = $utr->getTieStep($match['tie_posRound'], $match['rund_type']);
			$matchs["{$match['draw_id']};{$match['rund_group']};{$match['tie_step']};{$match['tie_id']};{$match['rund_id']}"] =
			array('value' => "{$match['rund_stamp']} $label ",
		      'class' => 'match',
	          'title' => $tmp);   
			unset($objMatch);

		}
		return $matchs;
	}
	// }}}

	// {{{ getDraws
	/**
	* Retourne la liste des tableaux
	*
	* @access public
	* @return array   array of users
	*/
	function getDraws()
	{
		// Select the draws
		$fields = array('draw_id', 'draw_stamp', 'draw_nbMaxStep',
		      "count('draw_id') as nbMatch");

		$tables = array('draws LEFT JOIN rounds ON draw_id = rund_drawId LEFT JOIN ties ON rund_id = tie_roundId');
		$where = " draw_eventId =".utvars::getEventId().
	" AND tie_isBye = 0".
	" AND (tie_court = 0 OR".
	" tie_court IS NULL OR".
	" tie_place = '')".
	" GROUP BY draw_id";
		$order = 'nbMatch DESC, draw_stamp';
		$res = $this->_select($tables, $fields, $where, $order);
		$draws = array();
		while ($draw = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$draws[$draw['draw_id']] = "{$draw['nbMatch']}-{$draw['draw_stamp']}";
		}
		return $draws;
	}
	// }}}

	// {{{ getSchedule
	/**
	* Retourne la liste des matchs dans l'ordre de planification
	*
	* @access public
	* @return array   array of users
	*/
	function getSchedule($venue, $date, $start, $end, $length, $nbCourtMax)
	{
		// Select the ties
		$venue = addslashes($venue);
		$utd = new utDate();
		$utd->setFrDate($date);

		// Recuperer les rencontres deja dans l'echeancier
		$fields = array('tie_id', 'tie_schedule', 'tie_court', 'tie_step',
		      'tie_place', 'tie_posRound', 'mtch_id', 'mtch_num',
		      'rund_type', 'rund_stamp', 'rund_group',
		      'draw_stamp', "TIME(tie_schedule) as time", "TIME(tie_convoc) as convoc");
		$tables = array('ties', 'draws', 'rounds', 'matchs');
		$where = "draw_id = rund_drawId".
			" AND rund_id = tie_roundId".
			" AND mtch_tieId = tie_id".
			" AND tie_place = '$venue'".
			" AND tie_isBye=0".
			" AND draw_eventId = ".utvars::getEventId().
			" AND DATE(tie_schedule) = '". $utd->getIsoDate() . "'";

		$order = 'tie_schedule, 0+tie_court';
		$res = $this->_select($tables, $fields, $where, $order);

		$matchs = array();
		$time = null;
		$utr = new utround();
		$ut = new utils();
		$nbCourt = 1;
		$firstFree = -1;
		$utd = new utdate();
		require_once 'utils/objmatch.php';

		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$objMatch = new objmatch($match['mtch_id']);
			$tmp = $objMatch->getFirstTopName(false, true) . "-" . $objMatch->getSecondTopName(false, true) .' / '
			.  $objMatch->getFirstBottomName(false, true) . "-" . $objMatch->getSecondBottomName(false, true);
			if ($time != $match['time'])
			{
				if ($time)
				{
					while($nbCourt <= $nbCourtMax)
					{
						$tmpMatchs["$shortTime;$nbCourt;-1;-1"] = array('value'=>"{$nbCourt}--...",
							   'class'=>'match');
						if ($firstFree == -1) $firstFree ="$shortTime;$nbCourt;-1;-1";
						$nbCourt++;
					}
					$utd->setTime($match['time']);
					$isoTime = $utd->getIsoDateTime();
					$utd->setTime($time);
					$duree = $utd->elaps($isoTime);
					$matchs["$shortTime;-1;-1;$duree"] = array('value' => $shortTime . " ($duree mn) ", // . $convoc,
							'class'=>'step');
					$matchs = array_merge($matchs, $tmpMatchs);
				}
				$nbCourt = 1;
				$time = $match['time'];
				$convoc = $match['convoc'];
				$shortTime = substr($match['time'], 0, 5);
				$tmpMatchs =array();
			}
			while($nbCourt < $match['tie_court'])
			{
				$tmpMatchs["$shortTime;$nbCourt;-1;-1"] = array('value'=>"{$nbCourt}-...",
						   'class'=>'match');
				if ($firstFree == -1) $firstFree ="$shortTime;$nbCourt;-1;-1";
				$nbCourt++;
			}
			if ($match['rund_type'] != WBS_ROUND_GROUP)
			{
				$phase = $utr->getTieStep($match['tie_posRound'], $match['rund_type']);
				$pos = $utr->getTiePos($match['tie_posRound']);
				$label =  "--{$match['rund_stamp']}-- ".$ut->getLabel($phase). " $pos";
			}
			else
			{
				$label = " - Group {$match['rund_stamp']} - Tour {$match['tie_step']} (".
				$utr->getTieStep($match['tie_posRound'], $match['rund_type']).")";
			}
			$value = $match['tie_court'] . ' - ' . $match['draw_stamp'];
			if ($match['rund_group'] != 'Principal') $value .= ' '. $match['rund_group'];
			//$value .= $label . ' - ' . $match['mtch_num'] . ' -' . $match['tie_court'] . '-' . $tmp;
			$value .= $label . ' - ' . $match['mtch_num'] . ' - ' . $tmp;
			$tmpMatchs["$shortTime;$nbCourt;{$match['tie_id']};-1"] =
			array('value'=> $value, 'class'=>'match');

			unset($objmatch);

			$nbCourt++;
		}
		if (isset($tmpMatchs))
		{
			$matchs["$shortTime;-1;-1;$length"] = array('value'=>"$shortTime ({$length} mn) ", // . $convoc,
					    'class'=>'step');
			$matchs = array_merge($matchs, $tmpMatchs);
		}

		if ($time == NULL) $time= $start;
		$utd->setTime($time);
		while ($time < $end)
		{
			if ($nbCourt == 1)
			{
				$time = substr($utd->getTime(), 0, 5);
				$matchs["$time;-1;-1;-1"] = array('value'=>"$time",
					     'class'=>'step');
			}
			$shortTime = substr($time, 0, 5);
			while($nbCourt <= $nbCourtMax)
			{
				$matchs["$shortTime;$nbCourt;-1;-1"] = array('value'=>"{$nbCourt}-...",
						   'class'=>'match');
				if ($firstFree == -1) $firstFree ="$time;$nbCourt;-1;-1";
				$nbCourt++;
			}
			$nbCourt = 1;
			$utd->addMinute($length);
		}

		$matchs[] = $firstFree;
		unset($objMatch);
		return $matchs;
	}
	// }}}

	// {{{ getDate
	/**
	* Return the table with date of the selected day
	*
	* @access public
	* @param integer divId Id of the division
	* @return array   array of users
	*/
	function getDates($place)
	{
		// Firt select the ties of the day
		$eventId = utvars::getEventId();
		$place = addslashes($place);
		$fields = array('tie_schedule');
		$tables = array('ties', 'draws', 'rounds');
		$where = "tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId".
	" AND tie_isBye=0".
	" AND tie_place = '$place'";
		$order = "1";

		$res = $this->_select($tables, $fields, $where, $order);
		$dates = array();
		$utd = new utDate();
		while ($tmp = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$utd->setIsoDate($tmp['tie_schedule']);
			$fullDate = $utd->getDateTimeWithDay();
			$dates[$utd->getDate()] = $fullDate;
		}
		return $dates;
	}
	// }}}

	// {{{ getFirstTime
	/**
	* Return the first time for the selected day and sporthall
	*
	* @access public
	* @param integer divId Id of the division
	* @return array   array of users
	*/
	function getFirstTime($place, $day)
	{
		$utd = new utDate();
		$utd->setFrDate($day);
		// Firt select the ties of the day
		$eventId = utvars::getEventId();
		$place = addslashes($place);

		$fields = array("TIME(tie_schedule) as time");
		$tables = array('ties', 'draws', 'rounds');
		$where = "tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId".
	" AND tie_place = '$place'".
	" AND tie_isBye=0".
	" AND DATE(tie_schedule) = '". $utd->getIsoDate() . "'";
		$order = "1";

		$time = $this->_selectFirst($tables, $fields, $where, $order);
		if (is_null($time)) $time = "09:00:00";
		return substr($time,0,5);
	}
	// }}}

	// {{{ getMatchesStep
	/**
	* Return the list of the matches of a step
	*
	* @access public
	* @param  integer  $roundId  Id of the round
	* @return array   array of users
	*/
	function getMatchesStep($drawId, $groupname, $roundId, $step)
	{
		// Select the type of the round
		$where = "rund_id=$roundId";
		$type = $this->_selectFirst('rounds', 'rund_type', $where);

		// Select the ties
		$fields = array('tie_id');
		$tables = array('ties', 'rounds');
		if ($type != WBS_ROUND_GROUP)
		{
			$where = "tie_step = $step".
	    " AND tie_roundId = rund_id".
	    " AND tie_roundId = $roundId".
	    " AND tie_isBye = 0";
			$order = 'tie_posRound';
		}
		else
		{
			$where = "tie_step = $step".
	    " AND tie_roundId = rund_id".
	    " AND rund_drawId = $drawId".
	    " AND rund_group = '" . addslashes($groupname) . "'" .
	  	" AND rund_type = $type";
			$order = 'rund_stamp';
		}
		//echo $where;
		$res = $this->_select($tables, $fields, $where, $order);
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$ties[] = $tie['tie_id'];
		//print_r($ties);
		//exit;

		return $ties;
	}
	// }}}

	/**
	 * Met a jour l'heure des matches
	 *
	 * @access public
	 * @return array   array of users
	 */
	function updateTime($oldVenue, $venue, $oldDay, $day,
	$oldTime, $newTime, $length, $propa, $convoc, $propaconvoc)
	{
		// Select the ties
		$oldVenue = addslashes($oldVenue);
		$utOldTime = new utdate();
		$utOldTime->setFrDateTime("$oldDay $oldTime");
		$utNewTime = new utdate();
		$utNewTime->setFrDateTime("$day $newTime");
		$utConvoc = new utdate();
		$utConvoc->setFrDateTime("$day $newTime");
		$utConvoc->addMinute(-$convoc);

		// Recuperer les rencontres deja dans l'echeancier
		$fields = array('tie_id', 'tie_schedule', 'tie_court',
		      'tie_place', 
		      "TIME(tie_schedule) as time");
		$tables = array('ties', 'draws', 'rounds');
		$where = "draw_id = rund_drawId".
				" AND rund_id = tie_roundId".
				" AND tie_place = '$oldVenue'".
				" AND draw_eventId = ".utvars::getEventId().
				" AND DATE(tie_schedule) = '". $utOldTime->getIsoDate() . "'".
				" AND TIME(tie_schedule) >= '" . $utOldTime->getTime() . "'";
		$order = 'tie_schedule, 0+tie_court';
		$res = $this->_select($tables, $fields, $where, $order);

		$first = true;
		$venue = addslashes($venue);
		$oldTime .= ':00';
		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($match['time'] != $oldTime)
			{
				if (!$propa && !$first)
				{
					$isoTime = $utOldTime->getIsoDateTime();
					$utOldTime->setTime($match['time']);
					$length = abs($utOldTime->elaps($isoTime));
				}
				else $utOldTime->setTime($match['time']);
				$first = false;
				$oldTime = $match['time'];
				$utNewTime->addMinute($length);
				$utConvoc->addMinute($convoc);
			}
			$cols['tie_schedule'] = $utNewTime->getIsoDateTime();
			$cols['tie_convoc'] = $utConvoc->getIsoDateTime();
			$cols['tie_place'] = $venue;
			$where = "tie_id={$match['tie_id']}";
			$this->_update('ties', $cols, $where);
		}
	}

	// {{{ isStarted
	/**
	* La competition a-t-elle debutee?
	*
	* @access public
	* @return array   array of users
	*/
	function isStarted()
	{
		// Un match en cours ou termine ?
		$tables = array('draws', 'rounds', 'ties', 'matchs');
		$where = "mtch_status > ".WBS_MATCH_READY.
	" AND draw_id = rund_drawId".
	" AND rund_id = tie_roundId".
	" AND tie_id = mtch_tieId".
	" AND tie_isBye = 0".
	" AND draw_eventId=".utvars::getEventId();

		$res = $this->_selectFirst($tables, 'mtch_id', $where);
		return !is_null($res);
	}
	// }}}

	// {{{ getNbCourt
	/**
	* Nombre de terrain
	*
	* @access public
	* @return array   array of users
	*/
	function getNbCourt($venue, $aDay)
	{
		if ($venue == -1 || $aDay == -1) $nbCourt = 7;
		else
		{
			list($d,$m,$y) = explode('-', $aDay);
			$day = "$y-$m-$d";

			$fields[] = 'count(*)';
			$tables = array('draws', 'rounds', 'ties', 'matchs');
			$venue = addslashes($venue);
			$where =
	    " draw_id = rund_drawId".
	    " AND rund_id = tie_roundId".
	    " AND tie_id = mtch_tieId".
	    " AND tie_court != 0".
	    " AND tie_isBye = 0".
	    " AND tie_place = '$venue'".
	    " AND date(tie_schedule) = '$day'".
			" AND draw_eventId=".utvars::getEventId().
	    " GROUP BY tie_schedule";
			$order = '1 DESC';

			$nbCourt = $this->_selectFirst($tables, $fields, $where, $order);
		}
		return $nbCourt;
	}
	// }}}

	// {{{ getTeams
	/**
	* Liste des equipes
	*
	* @access public
	* @return array   array of users
	*/
	function getTeamsold()
	{
		$eventId = utvars::getEventId();
		$fields = array('ctac_id', 'asso_pseudo', 'team_name', 'team_stamp', 'ctac_contact',
		      'team_textConvoc', 'team_id');
		$tables = array('teams', 'a2t', 'assocs LEFT JOIN contacts ON asso_id=ctac_assocId');
		$where = "team_eventId = $eventId".
	" AND team_id=a2t_teamId".
	" AND asso_id=a2t_assoId".
	" AND (ctac_type=".WBS_EMAIL.
	" OR ctac_type =''".
	" OR ctac_type IS NULL)";
		$order = "2,3";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
			$err['errMsg'] = 'msgNoTeams';
			return $err;
		}
		$teams = array();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$entry['ctac_id'] = "{$entry['team_id']};{$entry['ctac_id']}";
			$teams[] = $entry;
		}
		return $teams;
	}
	// }}}

	// {{{ getTeams
	/**
	* Liste des equipes
	*
	* @access public
	* @return array   array of users
	*/
	function getTeams()
	{
		$eventId = utvars::getEventId();
		$fields = array('team_id', 'asso_pseudo', 'team_name', 'team_stamp',
		      'team_stamp as contact', 'team_textConvoc', 'asso_id');
		$tables = array('teams', 'a2t', 'assocs');
		$where = "team_eventId = $eventId".
	" AND team_id=a2t_teamId".
	" AND asso_id=a2t_assoId";
		$order = "2";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
			$err['errMsg'] = 'msgNoTeams';
			return $err;
		}
		$teams = array();

		while ($assoc = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$fields = array('ctac_id', 'ctac_contact', 'ctac_value');
			$tables = array('contacts');
			$where = "ctac_assocId=".$assoc['asso_id'].
	    " AND (ctac_type=".WBS_EMAIL.
	    " OR ctac_type =''".
	    " OR ctac_type IS NULL)";
			$res2 = $this->_select($tables, $fields, $where, $order);
			$select = array();
			$select[] = array('key'   => $assoc['team_id'] . ';-1',
			    'value' => '--');
			$isSel = false;
			while ($contact = $res2->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$option = array('key'   => $assoc['team_id'] .';' .$contact['ctac_id'],
			      'value' => $contact['ctac_contact'] . ' (' . $contact['ctac_value'] .')');
				if (! $isSel )
				{
					$option['select'] = true;
					$isSel = true;
				}
				$select[] = $option;
			}
			$listContact['select'] = $select;
			$listContact['name'] = 'contacts';
			$assoc['contact'] = $listContact;
			$teams[] = $assoc;
		}
		return $teams;
	}
	// }}}



	// {{{ updateLive
	/**
	* Positionne le flag de presence des joueurs a absent jusqu'a l'heure de convocation
	*
	* @access public
	* @return array   array of users
	*/
	function updateLive($convocation)
	{
		$eventId = utvars::getEventId();
		// Heure de premier match
		$fields = array('regi_id', 'tie_schedule');
		$tables = array('registration', 'i2p',
		      'p2m', 'matchs', 'ties');
		$where  =
	"regi_eventId = $eventId".
	" AND regi_id = i2p_regiId".
	" AND i2p_pairId = p2m_pairId".
	" AND p2m_matchId = mtch_id".
	" AND tie_schedule != ''".
	" AND tie_schedule != '0000-00-00 00:00:00'".
	" AND mtch_tieId = tie_id".
	" AND tie_isBye=0".
	" AND mtch_status <".WBS_MATCH_LIVE;
		$order = "1, tie_schedule";
		$res = $this->_select($tables, $fields, $where, $order);
		$id = -1;
		$utd = new utdate();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($id != $entry['regi_id'])
			{
				$id = $entry['regi_id'];
				$utd->setIsoDateTime($entry['tie_schedule']);
				$utd->addMinute(-$convocation);
				$col['regi_present'] = WBS_NO;
				$col['regi_delay'] = $utd->getIsoDateTime();
				$where = "regi_id=$id";
				$this->_update('registration', $col, $where);
			}
		}
		return;
	}
	// }}}
}
?>
