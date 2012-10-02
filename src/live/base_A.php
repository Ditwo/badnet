<?php
/*****************************************************************************
 !   Module     : live
 !   File       : $Source: /cvsroot/aotb/badnet/src/live/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.37 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/20 22:20:08 $
 ******************************************************************************/
include_once dirname(__FILE__)."/../utils/utbase.php";
include_once dirname(__FILE__)."/../utils/utscore.php";
include_once dirname(__FILE__)."/../utils/utround.php";
include_once dirname(__FILE__)."/../draws/draws.inc";
include_once dirname(__FILE__)."/../utils/objplayer.php";
include_once dirname(__FILE__)."/../utils/objgroup.php";


class liveBase_A extends utbase
{
	function getNbMatch($aDate, $aPlace)
	{
		$utd = new utDate();
		$utd->setFrDate($aDate);
		// Select the matchs of the event
		$eventId = utvars::getEventId();
		$fields = array('count(*)');
		$tables = array('matchs', 'ties', 'rounds', 'draws');
		$where = 	"mtch_tieId = tie_id".
	" AND tie_roundId=rund_id".
	" AND rund_drawId=draw_id".
	" AND tie_isBye=0".
	" AND draw_eventId=$eventId";
		if ($aPlace != -1)
		{
			$place = addslashes($aPlace);
			$where .= " AND tie_place = '$place'";
		}
		if ($aDate != -1) $where .= " AND date(tie_schedule) ='". $utd->getIsoDate()."'";
		//if (! $hide) $where .= " AND mtch_status <=".WBS_MATCH_ENDED;
		$nb = $this->_selectFirst($tables, $fields, $where);
		return ceil($nb/100);
	}

	/**
	 * Renvoie le lassement complet de chaque tableau (Squash uniquement)
	 */
	function getClasse()
	{
		include_once dirname(__FILE__)."/../utils/objmatch.php";
		$utd = new utdraw();
		$ut = new utils();

		//Liste des tableaux
		$fields = array('draw_serial', 'draw_name', 'draw_disci', 'draw_id', 'draw_type');
		$tables = array('draws');
		$where = "draw_eventId = ".utvars::getEventId();
		$order = 'draw_serial, draw_disci';
		$res = $this->_select($tables, $fields, $where, $order);

		$serial = '';
		$titres = array ('Joueur', 'Club', 'Classement');
		$tailles = array (60, 60, 40);
		$styles = array ('B','','');

		include_once dirname(__FILE__)."/../pdf/pdfbase.php";
		$pdf = new pdfbase();
		$top = $pdf->start('P', 'Classement');
		$palmes = array();
		while($draw = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($serial != $draw['draw_serial'])
			{
				if($serial != '')
				{
					foreach($playerIds as $regiId)
					{
						$player = new objPlayer($regiId);
						$row = array();
						$row[] = $player->getName();
						$row[] = $player->getTeamName();
						$row[] = $player->getRank() . '-' . $player->getRange();
						$row['border'] = "TBLR";
						$palmes[] = $row;
						unset($player);

					}
					$top = $pdf->genere_liste($titres, $tailles, $palmes, $styles);
					$palmes = array();
					$playerIds = array();
					$palmes['newPage'] = false;
				}
				$palmes['top'] = $top;
				$serial = $draw['draw_name'];
				$palmes['titre'] = $serial;
				$palmes['orientation'] = 'P';
			}
				
			// Liste des poules et classement
			$fields = array('i2p_regiid', 'rund_stamp', 't2r_rank', 'rund_rge');
			$tables = array('i2p', 't2r', 'rounds');
			$where = "t2r_roundId = rund_id".
		" AND rund_drawId=". $draw['draw_id'] .
		" AND rund_type=".WBS_ROUND_GROUP.
		" AND rund_rge > 0".
			" AND i2p_pairid=t2r_pairid";
			$order = "rund_stamp, t2r_rank";
			$res2 = $this->_select($tables, $fields, $where, $order);
			$place = -1;
			while ($entry = $res2->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if ($place==-1) $place = $entry['rund_rge'];
				$playerIds[$place++] = $entry['i2p_regiid'];
			}

			// Liste des finales du tableau
			$fields = array('mtch_id', 'rund_name', 'rund_rge');
			$tables = array('matchs', 'ties', 'rounds');
			$where = "mtch_status >".WBS_MATCH_LIVE.
               " AND tie_posRound = 0".
               " AND (rund_type =".WBS_ROUND_MAINDRAW.
               "  OR rund_type =".WBS_ROUND_THIRD.
               "  OR rund_type =".WBS_ROUND_CONSOL.
               "  OR rund_type =".WBS_ROUND_PLATEAU.
			") AND rund_id = tie_roundId".
               " AND tie_id = mtch_tieId".
               " AND rund_drawId = ". $draw['draw_id'];
			$order = 'rund_type, rund_rge';
			$res2 = $this->_select($tables, $fields, $where, $order);
			$i=1;
			while ($match = $res2->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$fullmatch = new objMatch($match['mtch_id']);
				if ( $fullmatch->isTopWin() ) $regiId = $fullmatch->getFirstTopId();
				else $regiId = $fullmatch->getFirstBottomId();
				if($regiId > 0)	$playerIds[$match['rund_rge']] = $regiId;

				if ( $fullmatch->isTopWin() ) $regiId = $fullmatch->getFirstBottomId();
				else $regiId = $fullmatch->getFirstTopId();
				if($regiId > 0) $playerIds[$match['rund_rge']+1] = $regiId;
			}
		}
		ksort($playerIds);
		foreach($playerIds as $regiId)
		{
			$player = new objPlayer($regiId);
			$row = array();
			$row[] = $player->getName();
			$row[] = $player->getTeamName();
			$row[] = $player->getRank() . '-' . $player->getRange();
			$row['border'] = "TBLR";
			$palmes[] = $row;
			unset($player);
		}
		$pdf->genere_liste($titres, $tailles, $palmes, $styles);
		$pdf->end();
	}

	/**
	 * Imprime la liste des matches
	 */
	function printMatchList($date, $place, $ended)
	{
		include_once dirname(__FILE__)."/../utils/objmatch.php";
		$place = addslashes($place);
		$utd = new utDate();
		$utd->setFrDate($date);

		// Select the matchs
		$fields = array('mtch_id');
		$tables = array('ties', 'matchs');
		$where = "mtch_tieId = tie_id".
	" AND date(tie_schedule) ='". $utd->getIsoDate()."'".
	" AND tie_place = '$place'".
	" AND tie_isBye = 0";
		$order = 'tie_schedule, mtch_num';
		if (!$ended)
		$where .= " AND mtch_status <".WBS_MATCH_ENDED;

		$res = $this->_select($tables, $fields, $where, $order);
		$matchs = array();
		if ($res->numRows())
		{
	  while($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  {
	  	$fullmatch = new objMatch($match['mtch_id']);
	  	$row = array();
	  	$row[] = $fullmatch->getTime();
	  	$row[] = $fullmatch->getNum();
	  	$row[] = $fullmatch->getDrawStamp();
	  	$row[] = $fullmatch->getStepName();
	  	$row[] = $fullmatch->getFirstTopName();
	  	$row[] = $fullmatch->getFirstBottomName();
	  	if ($fullmatch->isDouble())
	  	{
	  		$row['border'] = "TLR";
	  		$matchs[] = $row;
	  		$row = array();
	  		$row['border'] = "BLR";
	  		$row[] = '';
	  		$row[] = '';
	  		$row[] = '';
	  		$row[] = '';
	  		$row[] = $fullmatch->getSecondTopName();
	  		$row[] = $fullmatch->getSecondBottomName();
	  		$matchs[] = $row;
	  	}
	  	else
	  	{
	  		$row['border'] = "TBLR";
	  		$matchs[] = $row;
	  	}
	  	unset($fullmatch);
	  }

	  $row = array_pop($matchs);
	  $row['border'] .= "B";
	  $matchs[] = $row;
	  $matchs["titre"] = "$place $date";
		}
		$titres = array ('cHour', 'cNum', 'cDraw', 'cStage', 'cPairA', 'cPairB');
		$tailles = array (15, 10, 20, 20, 60, 60);
		$styles = array ('', 'B','','', '', '');

		include_once dirname(__FILE__)."/../pdf/pdfbase.php";
		$pdf = new pdfbase();
		$pdf->start('P');
		$pdf->genere_liste($titres, $tailles, $matchs, $styles);
		$pdf->end();
	}

	/**
	 * Valide les matchs
	 */
	function validate($date, $place)
	{
		$utd = new utDate();
		$utd->setFrDate($date);
		$place = addslashes($place);

		// Select the matchs
		$fields = array('mtch_id', 'p2m_result');
		$tables = array('ties', 'matchs', 'p2m');
		$where = "mtch_tieId = tie_id".
	" AND mtch_id = p2m_matchId".
	" AND mtch_status =".WBS_MATCH_ENDED.
	" AND date(tie_schedule) ='". $utd->getIsoDate()."'".
	" AND tie_place = '$place'";
		$order = 'mtch_id, p2m_result';
		$res = $this->_select($tables, $fields, $where, $order);
		$matchId = -1;
		$matchOk = true;
		$nbPair = 0;
		while($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  if ($matchId != $match['mtch_id'])
	  {
	  	if ($matchId != -1 && $matchOk && $nbPair==2)
	  	$matchs[] = $matchId;
	  	$matchId = $match['mtch_id'];
	  	$matchOk = true;
	  	$nbPair = 0;
	  }
	  if ($match['p2m_result'] == WBS_RES_NOPLAY)
	  $matchOk = false;
	  $nbPair++;
		}
		if ($matchId != -1 && $matchOk && $nbPair==2)
		$matchs[] = $matchId;

		// Mettre a jour le status des matchs valides
		if (isset($matchs))
		{
	  $fields = array('mtch_status' => WBS_MATCH_CLOSED);
	  $where = "mtch_id IN (".implode(',', $matchs).')';
	  $this->_update("matchs", $fields, $where);
		}

	}

	/**
	 * Retourne le nombre de match max par joueur
	 */
	function nbMatchPlayer($date=-1, $place=-1)
	{
		$utd = new utDate();
		$utd->setFrDate($date);
		// Updating the match
		$fields = array("count(*) as nb", 'regi_longName');

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
		if ($date != -1)
		$where .= " AND date(tie_schedule) ='". $utd->getIsoDate()."'";
		$where .= " GROUP BY i2p_regiId";
		$order = "1 DESC";
		$res = $this->_select($tables, $fields, $where, $order);
		$tmp = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return "{$tmp['nb']} {$tmp['regi_longName']}";
	}

	/**
	 * Retourne la duree moyenne des matchs
	 */
	function dureeMoyenne($date=-1, $place=-1)
	{
		$utd = new utDate();
		$utd->setFrDate($date);
		$place = addslashes($place);

		// Updating the match
		$fields = array("avg((unix_timestamp(mtch_end) - unix_timestamp(mtch_begin))/60) as avg",
		);

		$tables = array('matchs', 'ties', 'rounds', 'draws');
		$where = "mtch_status >".WBS_MATCH_LIVE.
	" AND rund_drawId = draw_id".
	" AND rund_id = tie_roundId".
	" AND tie_id = mtch_tieId".
	" AND draw_eventId = ".utvars::getEventId().
	" AND ((unix_timestamp(mtch_end) - unix_timestamp(mtch_begin))/60)>5".
	" AND ((unix_timestamp(mtch_end) - unix_timestamp(mtch_begin))/60)<90";
		if ($place != -1)
		{
	  $place = addslashes($place);
	  $where .= " AND tie_place = '$place'";
		}
		if ($date != -1)
		$where .= " AND date(tie_schedule) ='". $utd->getIsoDate()."'";

		$res = $this->_selectFirst($tables, $fields, $where);
		return $res;
	}

	/**
	 * Renvoie le palmares de chaque tableau
	 */
	function getPalmares()
	{
		include_once dirname(__FILE__)."/../utils/objmatch.php";
		$utd = new utdraw();
		$ut = new utils();

		//Liste des tableaux
		// Updating the match
		$fields = array('draw_serial', 'draw_name', 'draw_disci', 'draw_id', 'draw_type');
		$tables = array('draws');
		$where = "draw_eventId = ".utvars::getEventId();
		$order = 'draw_serial, draw_disci';
		$res = $this->_select($tables, $fields, $where, $order);

		$serial = '';
		$titres = array ('cDraw', 'cStage', 'cWinner', 'cSecond', 'cThird');
		$tailles = array (25, 25, 73, 73, 73);
		$styles = array ('B','','', '', '');

		include_once dirname(__FILE__)."/../pdf/pdfbase.php";
		$pdf = new pdfbase();
		$top = $pdf->start('L', 'tPalmares');
		$palmes = array();
		while($draw = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$drawId=$draw['draw_id'];
			if ($serial != $draw['draw_serial'])
			{
				if($serial != '')
				{
					$top = $pdf->genere_liste($titres, $tailles, $palmes, $styles);
					$palmes = array();
					$palmes['newPage'] = false;
				}
				$palmes['top'] = $top;
				$serial = $draw['draw_serial'];
				$palmes['titre'] = $serial;
				$palmes['orientation'] = 'L';
			}

			// Tableau en poule
			$groups =  Objgroup::getListGroups($drawId);
			foreach($groups as $group)
			{
				$oGroup = Objgroup::getGroup($drawId, $group);
				if($oGroup['type'] == WBS_GROUP && $oGroup['nb'] == 1)
				{
					$fields = array('t2r_pairId', 't2r_rank','rund_name');
					$tables = array('t2r', 'rounds');
					$where = "rund_drawId =" . $draw['draw_id'] . " AND t2r_roundId = rund_id";
					$order = 't2r_rank';
					$resPair = $this->_select($tables, $fields, $where, $order);
					$row = array();
					$row2 = array();
					//$row[] = $draw['draw_serial'];
					//$row[] = $draw['draw_name'];
					$row[] = $ut->getLabel($draw['draw_disci']);
					$row2[] = '';
					$row2[] = '';
					$i=0;
					while ($pair = $resPair->fetchRow(DB_FETCHMODE_ASSOC))
					{
						if ($i == 0) $row[] = $pair['rund_name'];
						if ($i++ > 2) break;
						$fields = array('regi_longName as name','team_stamp as stamp');
						$tables = array('i2p', 'registration','teams');
						$where = "i2p_pairId = ".$pair['t2r_pairId'].
								" AND regi_id = i2p_regiId ".
								" AND team_id = regi_teamId ";
						$order = 'i2p_pairId';
						$resNom = $this->_select($tables, $fields, $where, $order);
						$nom = $resNom->fetchRow(DB_FETCHMODE_ASSOC);
						$name = $nom['name']." (".$nom['stamp'].") " ;
						$row[] = $name;
						$nom = $resNom->fetchRow(DB_FETCHMODE_ASSOC);
						if ($nom)
						{
							$name = $nom['name']." (".$nom['stamp'].") " ;
							$row2[] = $name;
						}
					}
					// Match de double
					if (count($row2) >2)
					{
						$row['border'] = "TLR";
						$palmes[] = $row;
						$row2['border'] = "BLR";
						$palmes[] = $row2;
					}
					else $palmes[] = $row;
					continue;
				}
			}
			unset($row);
			unset($row2);
			// Liste des finales et demi KO du tableau
			$fields = array('mtch_id', 'rund_name');
			$tables = array('matchs', 'ties', 'rounds');
			$where = "mtch_status >".WBS_MATCH_LIVE.
						" AND tie_posRound IN (0,1,2)".
						" AND rund_type =".WBS_ROUND_MAINDRAW.
			//"  OR rund_type =".WBS_ROUND_THIRD.
			//"  OR rund_type =".WBS_ROUND_CONSOL.
			//"  OR rund_type =".WBS_ROUND_PLATEAU.
			" AND rund_id = tie_roundId".
						" AND tie_id = mtch_tieId".
						" AND rund_drawId = {$draw['draw_id']}";
			$order = 'rund_name, rund_type, tie_posRound';
			$res2 = $this->_select($tables, $fields, $where, $order);
			// la finale pour les deux premiers
			$match = $res2->fetchRow(DB_FETCHMODE_ASSOC);
			if ($match)
			{
				$fullmatch = new objMatch($match['mtch_id']);
				$row = array();
				//$row[] = $draw['draw_serial'];
				//$row[] = $fullmatch->getDrawName();
				$row[] = $ut->getLabel($draw['draw_disci']);
				$row[] = $fullmatch->getStageName();
				$row[] = $fullmatch->getFirstWinName();
				$row[] = $fullmatch->getFirstLosName();
				if ($fullmatch->isDouble())
				{
					$row['border'] = "TLR";
					$row2 = array();
					$row2['border'] = "BLR";
					$row2[] = '';
					$row2[] = '';
					$row2[] = $fullmatch->getSecondWinName();
					$row2[] = $fullmatch->getSecondLosName();
				}
				else
				{
					$row['border'] = "TBLR";
				}
			}
			// Demi finales pour 3 et 4eme place
			$match = $res2->fetchRow(DB_FETCHMODE_ASSOC);
			if ($match)
			{
				$fullmatch = new objMatch($match['mtch_id']);
				$row[] = $fullmatch->getFirstLosName();
				if ($fullmatch->isDouble())
				{
					$row2[] = $fullmatch->getSecondLosName();
				}
				unset($fullmatch);
				unset($match);
			}
			else
			{
				if (isset($row)) $row[] = '';
				if (isset($row2)) $row2[] = '';
			}
			$match = $res2->fetchRow(DB_FETCHMODE_ASSOC);
			if ($match)
			{
				$fullmatch = new objMatch($match['mtch_id']);
				if ($fullmatch->isDouble())
				{
					$row2['border'] = "LR";
					$row3 = array('','','','');
					$row3['border'] = "LR";
					$row3[] = $fullmatch->getFirstLosName();
					$row4 = array('','','','');
					$row4['border'] = "BLR";
					$row4[] = $fullmatch->getSecondLosName();
				}
				else
				{
					$row['border'] = 'TLR';
					$row2 = array('','','','');
					$row2['border'] = "BLR";
					$row2[] = $fullmatch->getFirstLosName();
				}
				unset($fullmatch);
				unset($match);
			}

			if (isset($row)) $palmes[] = $row;
			if (isset($row2)) $palmes[] = $row2;
			if (isset($row3)) $palmes[] = $row3;
			if (isset($row4)) $palmes[] = $row4;
			unset ($row);
			unset($row2);
			unset($row3);
			unset($row4);
		}
		$pdf->genere_liste($titres, $tailles, $palmes, $styles);
		$pdf->end();
	}

	/**
	 * Return the number of the first avalaible court
	 */
	function getFirstCourt($date = 1, $place = -1)
	{
		$ut = new utils();

		// Construct the list of free court
		$nbCourt = $ut->getPref('cur_nbcourt', 5);
		$courts = range(1, $nbCourt);
		$busy = $this->getCourtBusy($date, $place);
		//foreach($busy as $match) unset($courts[$match['mtch_court']]);
		foreach($busy as $match) unset($courts[$match->getCourt()]);
		if (!isset($courts)) $courts[0] = 1;
		unset($busy);

		// Retrieve informations available courts
		// with the end time of the last match
		$fields = array('mtch_court', 'MAX(mtch_end)');
		$tables = array('matchs', 'ties', 'rounds', 'draws');
		$eventId = utvars::getEventId();

		$where = "mtch_tieId=tie_id".
	" AND tie_roundId=rund_id".
	" AND rund_drawId=draw_id".
	" AND draw_eventId=$eventId".
	" AND mtch_status >".WBS_MATCH_LIVE.
	" AND mtch_court IN (".implode(',', $courts).")".      
	" GROUP BY mtch_court";      
		$order = "MAX(mtch_end) DESC";
		$res = $this->_select($tables, $fields, $where);
		if ($res->numRows())
		{
			$match = $res->fetchRow(DB_FETCHMODE_ASSOC);
			$court = $match['mtch_court'];
		}
		else
		{
			if (count($courts))
			$court = reset($courts);
			else
			$court = 1;
		}
		return $court;
	}

	/**
	 * Return the list of court with macth
	 */
	function getCourtBusy($date = -1, $place = -1)
	{
		$place = addslashes($place);
		$utd = new utDate();
		$utd->setFrDate($date);

		include_once 'utils/objmatch.php';
		// Retrieve informations of the match
		$fields = array('mtch_id', 'mtch_court', 'mtch_num', 'mtch_begin');
		$tables = array('matchs', 'ties', 'rounds', 'draws');

		$eventId = utvars::getEventId();

		$where = "mtch_tieId=tie_id".
       " AND tie_roundId=rund_id".
       " AND rund_drawId=draw_id".
       " AND draw_eventId=$eventId".
       " AND mtch_status =".WBS_MATCH_LIVE;
		if ($place != -1)
		{
	  $place = addslashes($place);
	  $where .= " AND tie_place = '$place'";
		}
		if ($date != -1)
		$where .= " AND date(tie_schedule) ='". $utd->getIsoDate()."'";

		$order = "mtch_court";
		$res = $this->_select($tables, $fields, $where);

		$courts = array();
		$matchs = array();
		$utd = new utdate();
		while($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$utd->setIsoDateTime($match['mtch_begin']);
			$match['mtch_begin']= $utd->getFullTime();
			$courts[$match['mtch_id']] = $match;

			$matchs[$match['mtch_id']] = new objmatch($match['mtch_id']);
		}
		return $matchs;
	}

	/**
	 * Liste des matches suivants
	 */
	function getNextMatchNum($date, $place, $nbMatch)
	{
		$utd = new utDate();
		$utd->setFrDate($date);
		// Select the num of the next match

		$fields = array('mtch_num');
		$tables = array('matchs', 'ties', 'rounds', 'draws');
		$where = "mtch_status =". WBS_MATCH_READY.
	" AND mtch_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId =".utvars::getEventId();
	" AND tie_isBye = 0";
		$place = addslashes($place);
		if ($place != -1)
		$where .= " AND tie_place = '$place'";
		if ($date != -1)
		$where .= " AND date(tie_schedule) ='". $utd->getIsoDate()."'";
		$order = "tie_schedule, mtch_num LIMIT 0, $nbMatch";

		$res = $this->_select($tables, $fields, $where, $order);

		$eventId = utvars::getEventId();
		$teams =array();
		$path = dirname(__FILE__)."/../../../../T3f/Nextmatches.html";
		if($handle = fopen($path, 'w'))
		{
	  $buf = "<html><head><title>Prochain matches</title>\n";
	  $buf .= "<META HTTP-EQUIV=\"refresh\" content=\"100;url=Accueil.html\">";
	  $buf .= '<link rel="stylesheet" type="text/css" href="Nextmatches.css" media="screen" />';

	  $buf .= "</head>\n";
	  $buf .= "<body background=\"Img/Nextmatchs.jpg\">\n";
	  fwrite($handle, $buf);
	  $nb=1;

	  while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  {
	  	$buf = "<div id=\"match$nb\"><p>";
	  	$buf .= $match['mtch_num'];
	  	$buf .= "</p></div>";
	  	$nb++;
	  	fwrite($handle, $buf);
	  }
	  $buf = "</body></html>";
	  @fclose($handle);
	  chmod($path, 0777);
		}
		else
		echo $path;
		return true;
	}

	/**
	 * Liste des matches suivants
	 */
	function getLastMatchNum($date, $place, $nbMatch)
	{
		$utd = new utDate();
		$utd->setFrDate($date);
		// Select the num of the last match

		$fields = array('mtch_num');
		$tables = array('matchs', 'ties', 'rounds', 'draws');
		$where = "mtch_status =". WBS_MATCH_LIVE.
	" AND mtch_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId =".utvars::getEventId();
	" AND tie_isBye = 0";
		$place = addslashes($place);
		if ($place != -1)
		$where .= " AND tie_place = '$place'";
		if ($date != -1)
		$where .= " AND date(tie_schedule) ='". $utd->getIsoDate()."'";
		$order = "mtch_num DESC LIMIT 0, $nbMatch";

		$res = $this->_select($tables, $fields, $where, $order);
		$match = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$path = "/home/badnet/Public_html/France jeunes 2009/num.html";
		if($handle = fopen($path, 'w'))
		{
			$buf = "<body><html><div><p style='font-weight:bold;color:yellow;font-size:30px;text-align:center;margin:0;>Dernier match <br>lanc&eacute;<br>";
			$buf .= $match['mtch_num'];
			$buf .= "</p></div>";
			$buf .= "</body></html>";
			fwrite($handle, $buf);
			@fclose($handle);
			chmod($path, 0777);
		}
		return true;
	}

	/**
	 * Return the list of spcial pair name
	 */
	function getSpecialPairName()
	{
		// Pour les poules de trois
		$eventId = utvars::getEventId();
		$fields = array('p2m_pairId', 'mtch_num', 't2r_posRound');
		$tables = array('t2r', 'p2m', 'matchs', 'ties', 'rounds', 'draws');
		$where = 	"t2r_pairId=p2m_pairId".
	" AND p2m_matchId= mtch_id".
	" AND mtch_tieId = tie_id".
	" AND tie_roundId=rund_id".
	" AND rund_drawId=draw_id".
	" AND draw_eventId=".utvars::getEventId().
	" AND rund_type =".WBS_ROUND_GROUP.
	" AND rund_size = 3".
	" AND tie_posRound= 2".
	" AND p2m_result=".WBS_RES_NOPLAY;
		$res = $this->_select($tables, $fields, $where);

		$pairs = array();
		while ($tmp = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  if ($tmp['t2r_posRound'] == 3)
	  $pairs[$tmp['p2m_pairId']] = "Perdant match ".$tmp['mtch_num'];
	  else if ($tmp['t2r_posRound'] == 2 )
	  $pairs[$tmp['p2m_pairId']] = "Vainqueur match ".$tmp['mtch_num'];
		}

		// Pour les poules de 4, premier match du premier tour
		$fields = array('p2m_pairId', 'mtch_num', 't2r_posRound');
		$tables = array('t2r', 'p2m', 'matchs', 'ties', 'rounds', 'draws');
		$where = 	"t2r_pairId=p2m_pairId".
	" AND p2m_matchId= mtch_id".
	" AND mtch_tieId = tie_id".
	" AND tie_roundId=rund_id".
	" AND rund_drawId=draw_id".
	" AND draw_eventId=".utvars::getEventId().
	" AND rund_type =".WBS_ROUND_GROUP.
	" AND rund_size = 4".
	" AND tie_posRound = 1".
	" AND p2m_result=".WBS_RES_NOPLAY;
		$res = $this->_select($tables, $fields, $where);

		while ($tmp = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  if ($tmp['t2r_posRound'] == 3)
	  $pairs[$tmp['p2m_pairId']] = "Perdant match ".$tmp['mtch_num'];
	  else if ($tmp['t2r_posRound'] == 1 )
	  $pairs[$tmp['p2m_pairId']] = "Vainqueur match ".$tmp['mtch_num'];
		}

		// Pour les poules de 4, deuxieme match du premier tour
		$fields = array('p2m_pairId', 'mtch_num', 't2r_posRound');
		$tables = array('t2r', 'p2m', 'matchs', 'ties', 'rounds', 'draws');
		$where = 	"t2r_pairId=p2m_pairId".
	" AND p2m_matchId= mtch_id".
	" AND mtch_tieId = tie_id".
	" AND tie_roundId=rund_id".
	" AND rund_drawId=draw_id".
	" AND draw_eventId=".utvars::getEventId().
	" AND rund_type =".WBS_ROUND_GROUP.
	" AND rund_size = 4".
	" AND tie_posRound = 4".
	" AND p2m_result=".WBS_RES_NOPLAY;
		$res = $this->_select($tables, $fields, $where);

		while ($tmp = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  if ($tmp['t2r_posRound'] == 4)
	  $pairs[$tmp['p2m_pairId']] = "Perdant match ".$tmp['mtch_num'];
	  else if ($tmp['t2r_posRound'] == 2 )
	  $pairs[$tmp['p2m_pairId']] = "Vainqueur match ".$tmp['mtch_num'];
		}

		return $pairs;
	}

	/**
	 * Return the match of for an individual event
	 */
	function getMatches($date, $place, $step, $hide, $aPage=1, $aOrder=false)
	{
		$utd = new utdate();
		$utd->setFrDate($date);

		// Select the matchs of the event
		$eventId = utvars::getEventId();
		$fields = array('mtch_id', 'mtch_num', 'draw_stamp',
		      'tie_posRound as step', 'tie_posRound as pair1',
		      'tie_posRound as pair2',
		      'mtch_court', 'mtch_begin', 'mtch_end',
		      'mtch_score', 'tie_schedule', 'rund_stamp', 
		      'rund_drawId', 'rund_type','mtch_status', 
		      'tie_place', 'rund_name', 'rund_size', 'tie_posRound', 
		//'tie_court', "IF(mtch_court, mtch_court, tie_court) as tosort");
		      'tie_court', "CASE mtch_court WHEN 0 THEN tie_court ELSE mtch_court END AS tosort");
		$tables = array('matchs', 'ties', 'rounds', 'draws');
		$where = 	"mtch_tieId = tie_id".
			" AND tie_roundId=rund_id".
			" AND rund_drawId=draw_id".
			" AND tie_isBye=0".
			" AND draw_eventId=$eventId";
		//if ($step != -1)
		// 	$where .= " AND tie_step = '$step'";
		if ($place != -1)
		{
			$place = addslashes($place);
			$where .= " AND tie_place = '$place'";
		}
		if ($date != -1) $where .= " AND date(tie_schedule) ='". $utd->getIsoDate()."'";
		if (! $hide) $where .= " AND mtch_status <=".WBS_MATCH_ENDED;
		//      echo $where;
		$start = 100 * ($aPage-1);
		if ($aOrder) $order = "tosort, tie_schedule, mtch_num LIMIT $start, 100";
		else $order = "tie_schedule, mtch_num LIMIT $start, 100";
		$res = $this->_select($tables, $fields, $where, $order);

		// Construc a table with the match
		$utd = new utdate();
		$utr = new utround();
		$ut = new utils();
		$matches = array();
		$uts = new utscore();
		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			//print_r($match);
	  $utd->setIsoDateTime($match['mtch_begin']);
	  $match['mtch_begin']= $utd->getTime();

	  if (empty($match['mtch_begin']))
	  {
	  	$utd->setIsoDateTime($match['tie_schedule']);
	  	$match['mtch_begin']= $utd->getTime();
	  }

	  $utd->setIsoDateTime($match['mtch_end']);
	  $match['mtch_end']= $utd->getTime();

	  $uts->setScore($match['mtch_score']);
	  $match['score'] = $uts->getWinScore();
	  $line[0]['value'] = $match['draw_stamp'];
	  if ($match['rund_type'] == WBS_ROUND_GROUP)
	  {
	  	$match['step'] = $match['rund_stamp']. '('.
	  	$utr->getTieStep($match['tie_posRound'], WBS_ROUND_GROUP).')';
	  	$action = DRAW_GROUPS_DISPLAY;
	  }
	  else
	  {
	  	$step = $utr->getTieStep($match['tie_posRound']);
	  	$match['step'] = "{$match['rund_stamp']} ".$ut->getLabel($step);
	  	$action = DRAW_FINAL_DISPLAY;
	  }
	  $line[0]['action'] = array(KAF_UPLOAD, 'draws', $action,
	  $match['rund_drawId']);
	  $match['draw_stamp'] = $line;
	  $match['logoStatus'] = utimg::getIcon($match['mtch_status']);
	  $match['pair1'] = '';
	  $match['pair2'] = '';
	  if ($match['mtch_status'] == WBS_MATCH_INCOMPLETE)
	  $match['mtch_court'] = '';

	  $matches[$match['mtch_id']] = $match;
	  $matchIds[] = $match['mtch_id'];
		}

		if (isset($matchIds))
		{
	  $specialNames = $this->getSpecialPairName();
	  // Find the id of the pairs of the matches
	  $ids = implode(',', $matchIds);
	  $fields = array('p2m_pairId', 'p2m_matchId', 'p2m_result', 'mtch_discipline');
	  $tables = array('p2m', 'matchs');
	  $where  = "p2m_matchId IN (".$ids.')';
	  $where  .= " AND p2m_matchId = mtch_id";
	  $order = 'p2m_matchId, p2m_posmatch';
	  $res = $this->_select($tables, $fields, $where, $order);

	  $pairs = array();
	  $p2ms  = array();
	  while($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  {
	  	$pairsId[] = $entry['p2m_pairId'];
	  	$pairs[$entry['p2m_pairId']] = $entry;
	  	$p2ms[] = $entry;
	  }
	  // Retrieve players name of the pairs
	  $rows = array();
	  if (isset($pairsId))
	  {
	  	$ids = implode(',', $pairsId);
	  	$fields = array('i2p_pairId', 'regi_longName',
			      'asso_noc','team_noc', 'regi_noc',
			      'regi_id', 'regi_rest', 'regi_court',
			      'regi_delay', 'regi_present', 'regi_wo',
			      'team_stamp' , 'rkdf_label', 'i2p_classe'
			      );
			      $tables = array('members', 'registration', 'i2p', 'teams',
			      'a2t', 'assocs', 'rankdef'
			      );
			      $where = "mber_id = regi_memberId".
		" AND regi_id = i2p_regiId".
		" AND regi_teamId = team_id".
		" AND team_id = a2t_teamId".
		" AND asso_id = a2t_assoId".
	  	" AND i2p_rankdefid = rkdf_id" .
			      //" AND rank_id = rank_regiid" .
			      //" AND rank_disci = " . $match['mtch_discipline'].
	  	" AND i2p_pairId IN ($ids)";

			      $order = "i2p_pairId, mber_sexe, regi_longName";
			      $res = $this->_select($tables, $fields, $where, $order);

			      // For each player
			      $delay =  $ut->getPref('cur_rest', 20);
			      $isSquash = $ut->getParam('issquash', false);
			      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
			      {
			      	// Get his pair
			      	$pair = $pairs[$entry['i2p_pairId']];

			      	// Calcul the name to display
			      	// and the status of the player
			      	$value = '';
			      	$class = '';
			      	$status = WBS_MATCH_READY;
			      	$rest = '';
			      	$utd->setIsoDateTime($entry['regi_rest']);
			      	$rest = $utd->getTime();
			      	if ($entry['regi_wo'] == WBS_YES)
			      	{
			      		$class = 'wo';
			      		$value .= "[WO] -- ";
			      	}
			      	else if ($entry['regi_present'] == WBS_NO)
			      	{
			      		$class = 'absent';
			      		$status = WBS_MATCH_BUSY;
			      		if ($entry['regi_delay'] != '')
			      		{
			      			$utd->setIsoDateTime($entry['regi_delay']);
			      			$lala = $utd->getTime();
			      			$value .= "[$lala] -- ";
			      		}
			      	}
			      	else if ($entry['regi_court'] != 0)
			      	{
			      		$status = WBS_MATCH_BUSY;
			      		$class = $entry['regi_court'] > 0 ? 'busy':'umpire';
			      		$value .= "[".abs($entry['regi_court'])."] -- ";
			      	}
			      	else if ($rest != '')
			      	{
			      		if ($utd->elaps() > 0 &&
			      		$utd->elaps() < $delay)
			      		{
			      			$tmp = $utd->getDateTime();
			      			$utd->addMinute($delay);
			      			$rest = $utd->getTime();
			      			$status = WBS_MATCH_REST;
			      			$class = 'rest';
			      			$value .= "[$rest] -- ";
			      		}
			      	}
			      	$line = array();
			      	if ($isSquash)
			      	{
			      		$value .= $entry['regi_longName'] . ' (' . $entry['rkdf_label'] . '-' . $entry['i2p_classe'] . ")";
			      		$line['name'] = $entry['regi_longName'] . '(' . $entry['rkdf_label'] . '-' . $entry['i2p_classe'] . ')';
			      	}
			      	else
			      	{
			      		$value .= $entry['regi_longName'] . ' (' . $entry['rkdf_label'] . '-' . $entry['team_stamp'] . ")";
			      		$line['name'] = $entry['regi_longName'] . '(' . $entry['rkdf_label'] . '-' . $entry['team_stamp'] . ')';
			      	}
			      	if ($entry['regi_present'] == WBS_WARMUP || $entry['regi_present'] == WBS_CALL)
			      	$line['value'] = '<img src="img/icon/' . $entry['regi_present'] . '.png">'. $value;
			      	else $line['value'] = $value;
			      	$line['class'] = $class;
			      	$line['action'] = array(KAF_NEWWIN, 'resu', KID_EDIT, $entry['regi_id'], 500,200);
			      	$pair['players'][] = $line;
			      	// Update the status of the pair
			      	if (!isset($pair['status']) ||
			      	$pair['status'] > $status)
			      	$pair['status'] = $status;
			      	$pairs[$entry['i2p_pairId']] = $pair;
			      }
	  }

	  // Put pairs name to match def
	  // Update the status of the match
	  // according the status of the pairs
	  // Update the court of the match
	  foreach ($p2ms as $p2m)
	  {
	  	// Get the pair
	  	$pair = $pairs[$p2m['p2m_pairId']];
	  	// Get the match
	  	$match = $matches[$p2m['p2m_matchId']];

	  	// Update the status of the match
	  	if (isset($pair['status']) &&
		  $match['mtch_status'] <  WBS_MATCH_LIVE &&
		  $match['mtch_status'] > $pair['status'])
		  {
		  	$match['mtch_status'] = $pair['status'];
		  }

		  // Update the pair
		  if ($match['rund_type'] == WBS_ROUND_GROUP &&
		  $match['rund_size'] == 3 &&
		  $match['tie_posRound'] != 2 &&
		  isset($specialNames[$p2m['p2m_pairId']]))
		  {
		  	if ($match['pair1'] != '')
		  	$match['pair2'] = $specialNames[$p2m['p2m_pairId']];
		  	else
		  	$match['pair1'] = $specialNames[$p2m['p2m_pairId']];
		  }
		  elseif ($match['rund_type'] == WBS_ROUND_GROUP &&
		  $match['rund_size'] == 4 &&
		  $match['tie_posRound'] != 1 &&
		  $match['tie_posRound'] != 4 &&
		  isset($specialNames[$p2m['p2m_pairId']]))
		  {
		  	if ($match['pair1'] != '')
		  	$match['pair2'] = $specialNames[$p2m['p2m_pairId']];
		  	else
		  	$match['pair1'] = $specialNames[$p2m['p2m_pairId']];
		  }
		  else if (isset($pair['players']))
		  {
		  	$players = $pair['players'];
		  	if ($match['mtch_status'] >= WBS_MATCH_LIVE)
		  	{
		  		for($i=0; $i<count($players); $i++)
		  		{
		  			$player = $players[$i];
		  			if ($match['mtch_status'] == WBS_MATCH_LIVE)
			    $player['class'] = 'live';
			    else
			    {
			    	$player['value'] = $player['name'];
			    	$player['class'] = '';
			    }
			    $players[$i] = $player;
		  		}

		  	}
		  	$logoPair = '';
		  	if ($p2m['p2m_result'] != WBS_RES_NOPLAY)
		  	$logoPair = utimg::getIcon($p2m['p2m_result']);
		  	if ($match['pair1']!='')
		  	{
		  		$match['pair2'] = $players;
		  		$match['logoPairR'] = $logoPair;
		  	}
		  	else
		  	{
		  		$match['pair1'] = $players;
		  		$match['logoPairL'] = $logoPair;
		  	}
		  }
		  // update the court
		  if ($match['mtch_status'] >= WBS_MATCH_BUSY &&
		  $match['mtch_status'] < WBS_MATCH_ENDED)
		  {
		  	$listCourt['action'] =
		  	//array(KAF_UPLOAD, 'live', WBS_ACT_LIVE, $match[0]);
		  	array(KAF_NEWWIN, 'live', LIVE_DISPLAY_STATUS, 0, 400,200);
		  	$select = array();
		  	$nbCourt = $ut->getPref('cur_nbcourt', 5);
		  	for ($i = 0; $i <= $nbCourt; $i++)
		  	{
		  		$key = $match['mtch_id'].";$i";
		  		$option = array('key'=>$key, 'value'=>$i);
		  		if ($match['mtch_court'] == $i)
		  		$option['select'] = true;
		  		$select[] = $option;
		  	}
		  	$listCourt['select'] = $select;
		  	if (!is_array($match['mtch_court']))
		  	$match['mtch_court'] = $listCourt;

		  }
		  // Register the match
		  $matches[$p2m['p2m_matchId']] = $match;
	  }
		}
		return $matches;

	}

	/**
	 * Return the data of an official umpires registered
	 *
	 */
	function getUmpire($regiId)
	{
		// Firt select the ties of the day
		$fields = array('regi_longName', 'umpi_id', 'umpi_court', 'umpi_function', 'umpi_order', 'umpi_currentcourt');
		$tables = array('registration LEFT JOIN umpire ON regi_id=umpi_regiId');
		$where = "regi_id=$regiId";
		$res = $this->_select($tables, $fields, $where);
		$umpire =  $res->fetchRow(DB_FETCHMODE_ASSOC);
		if ($umpire['umpi_id'] == '')
		{
	  $umpire['umpi_id'] = -1;
	  $umpire['umpi_court'] = 0;
	  $umpire['umpi_function'] = WBS_UMPIRE_UMPIRE;
	  $umpire['umpi_order'] = 0;
		}
		return $umpire;
	}

	/**
	 * Return the list of official umpires registered
	 */
	function getOffUmpires($sort)
	{
		// Firt select the ties of the day
		$fields = array('regi_id', 'regi_longName', 'umpi_court',
		      'umpi_function', 'umpi_order', 'umpi_currentcourt');
		$tables = array('registration LEFT JOIN umpire ON umpi_regiId = regi_id');
		$where = "regi_eventId=".utvars::getEventId();
		$where .= " AND regi_type =".WBS_UMPIRE;
		$order = abs($sort);
		if ($sort < 0)
		$order .= " DESC";
		$res = $this->_select($tables, $fields, $where, $order);

		$umpires = array();
		$ut = new utils();
		while ($umpire = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  if ($umpire['umpi_function'] != "")
	  $umpire['umpi_function'] = $ut->getLabel($umpire['umpi_function']);
	  $umpires[] = $umpire;
		}
		return $umpires;
	}

	/**
	 * Return the umpires for a court
	 */
	function getUmpires($court, $status, $service=false)
	{
		// Firt select the ties of the day
		$fields = array('regi_id', 'regi_longName as regi_shortName');
		$where = "regi_eventId=".utvars::getEventId();
		if ($status == WBS_UMPIRE_OFFICIAL || $status == WBS_UMPIRE_OFFSERVICE)
		{
			$fields = array('regi_id', "concat(umpi_function, ':',umpi_order, ':', umpi_currentcourt, ':', regi_shortName) as regi_shortName");
			$tables = array('registration LEFT JOIN umpire ON umpi_regiId = regi_id');
			$where .= " AND regi_type=".WBS_UMPIRE;
			$where .= " AND umpi_function  != ".WBS_UMPIRE_REST;
			$where .= " AND (umpi_court = $court OR umpi_court =1)";
			//$where .= " AND regi_court=0";
			//$where .= " AND umpi_court = $court";
			if ($service)
			{
				$where .= " AND umpi_function =".WBS_UMPIRE_SERVICE;
				$order = "umpi_function DESC, umpi_currentcourt, umpi_order";
			}
			else
			{
				$where .= " AND umpi_function =".WBS_UMPIRE_UMPIRE;
				$order = "umpi_function, umpi_currentcourt, umpi_order";
			}
		}
		else
		{
			$tables = array('registration', 'i2p', 'p2m', 'matchs');
			$where .= " AND regi_type=".WBS_PLAYER;
			$where .= " AND regi_court=0";
			$where .= " AND i2p_regiId = regi_id";
			$where .= " AND p2m_pairId = i2p_pairId";
			$where .= " AND mtch_id = p2m_matchId";
			$where .= " AND mtch_court = $court";
			if ($status==WBS_UMPIRE_LOOSER)
			$where .= " AND p2m_result > ".WBS_RES_WINWO;
			else
			{
				$where .= " AND p2m_result > ".WBS_RES_NOPLAY;
				$where .= " AND p2m_result < ".WBS_RES_LOOSE;
			}
			$order = "mtch_end DESC";
		}
		$res = $this->_select($tables, $fields, $where, $order);
		while ($umpire = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$umpires[$umpire['regi_id']] = $umpire['regi_shortName'];
		$umpires[-1] = '----';
		return $umpires;
	}

	/**
	 * Return the tie id of a macth
	 */
	function getTieFromMatch($matchId)
	{
		// Firt select the ties of the day
		$fields[] = 'mtch_tieId';
		$tables[] = 'matchs';
		$where = "mtch_id=$matchId";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgUnknowMatch";
	  return $infos;
		}

		$match = $res->fetchRow(DB_FETCHMODE_ORDERED);
		return $match[0];
	}

	/**
	 * Return the table with ties of the current day
	 */
	function getTies($date, $place, $step)
	{
		$place = addslashes($place);

		$utd = new utdate();
		$utd->setFrDate($date);
		// Firt select the ties of the day
		$eventId = utvars::getEventId();
		$fields = array('tie_id', 'tie_schedule', 'team_id',
		      'team_name', 'asso_logo', 't2t_scoreW', 'team_stamp');
		$tables = array('teams', 't2t', 'ties', 'draws', 'rounds',
		      'assocs', 'a2t');
		$where = "t2t_teamId = team_id".
	" AND t2t_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId".
	" AND asso_id = a2t_assoId".
	" AND a2t_teamId = team_id";
		if ($date != -1)
		$where .= " AND date(tie_schedule) ='". $utd->getIsoDate() ."'";
		if ($place != -1)
		$where .= " AND tie_place='$place'";
		if ($step != -1)
		$where .= " AND tie_step='$step'";
		if ($date == '')
		$where .= " OR tie_schedule is null";
		$order = "tie_schedule, tie_id, t2t_posTie";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoTies";
	  return $infos;
		}

		$ties = array();
		$tieId=-1;
		while ($tmp = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $tmp['asso_logo'] = utimg::getPathFlag($tmp['asso_logo']);
	  if($tieId != $tmp['tie_id'])
	  {
	  	if($tieId != -1)
	  	$ties[] = $tie;
	  	$tie = $tmp;
	  	$tie['team_name'] .= "(".$tmp['team_stamp'].")";
	  	$tie['team_id2'] = -1;
	  	$tie['team_name2'] = '';
	  	$tie['team_stamp2'] = '';
	  	$tie['asso_logo2'] = '';
	  	$tie['t2t_scoreW2'] = 0;
	  	$tieId = $tmp['tie_id'];
	  }
	  else
	  {
	  	$tie['team_id2'] = $tmp['team_id'];
	  	$tie['team_name2'] = $tmp['team_name']. "(".
	  	$tmp['team_stamp'].")";
	  	$tie['team_stamp2'] = $tmp['team_stamp'];
	  	$tie['asso_logo2'] = $tmp['asso_logo'];
	  	$tie['t2t_scoreW2'] = $tmp['t2t_scoreW'];
	  }
		}
		$ties[] = $tie;
		return $ties;
	}

	/**
	 * Return the table with date of the ties
	 */
	function getDateTies()
	{
		$date = new utdate();
		// Firt select the ties of the day
		$eventId = utvars::getEventId();

		$fields = array('tie_schedule');
		$tables = array('ties', 'draws', 'rounds');
		$where = "tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND tie_schedule != ''".
	" AND tie_isBye = 0".
	" AND draw_eventId = $eventId";
		$order = "tie_schedule";
		$res = $this->_select($tables, $fields, $where, $order);

		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoDate";
	  return $infos;
		}

		$utd = new utdate();
		while ($tmp = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
			$utd->setIsoDateTime($tmp[0]);
			$date = $utd->getDate();
			$dates[$date] = $date;
		}
		$dates[-1] = '-----';
		return $dates;
	}

	/**
	 * Return the table with place of the ties
	 */
	function getTiesInfo($field)
	{
		// Firt select the ties of the day
		$eventId = utvars::getEventId();

		$fields = array($field);
		$tables = array('ties', 'draws', 'rounds');
		$where = "tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND $field != ''".
	" AND draw_eventId = $eventId";
		$order = "1";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNo$field";
	  return $infos;
		}

		while ($tmp = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $places[$tmp[0]] = $tmp[0];
		}
		$places[-1] = '-----';
		return $places;
	}

	/**
	 * Return the match of a tie
	 */
	function getMatchsTie($tieId, $teamL, $teamR, $hide, $aOrder=false)
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

		// Select the matchs of the tie
		$fields = array('mtch_id', 'mtch_rank', 'mtch_discipline', 'mtch_order',
		      'mtch_tieId', 'mtch_status', 'mtch_court', 'mtch_begin',
		      'mtch_end', 'mtch_score');
		$tables = array('matchs');
		$where = "mtch_del != ".WBS_DATA_DELETE.
	" AND mtch_tieId = $tieId";
		if (! $hide)
		$where .= " AND mtch_status <".WBS_MATCH_CLOSED;
		if ($aOrder) $order = "mtch_rank, mtch_discipline, mtch_order";
		else $order = "mtch_rank, mtch_discipline, mtch_order";
		$order = "mtch_rank, mtch_discipline, mtch_order";
		$res = $this->_select($tables, $fields, $where, $order);

		// Construc a table with the match
		$utd = new utdate();
		$ut = new utils();
		$matches = array();
		while ($match = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $discipline = $match[2];
	  if ($discipline < 6) $match[2] = $ut->getSmaLabel($discipline);
	  else $match[2] = 'SP';
	  if ($defTie[$discipline]>1)  $match[2] .= " ".$match[3];

	  $utd->setIsoDateTime($match[7]);
	  $match[7]= $utd->getTime();
	  $utd->setIsoDateTime($match[8]);
	  $match[8]= $utd->getTime();
	  $full = array_pad($match,13, "");
	  $full[12] = $match[5];
	  $full[3] = '';
	  $full[4] = '';
	  $full[5] = '';
	  //	  print_r($full);echo"<br>---------------------";
	  $matches[$match[0]] = $full;
	  $status[$match[0]] = $match[5];
		}

		// Select the players of the matchs of the tie
		$fields = array('mtch_id', 'p2m_result', 'regi_longName',
		      'regi_teamId', 'i2p_pairId', 'rkdf_label', 
		      'regi_rest', 'regi_court', 'regi_present',
		      'regi_delay', 'regi_wo', 'regi_id');
		$tables = array('matchs', 'p2m', 'i2p', 'registration',
		      'ranks', 'rankdef', 'members');
		$where = "mtch_del != ".WBS_DATA_DELETE.
	" AND mtch_tieId = $tieId".
	" AND mtch_id = p2m_matchId".
	" AND p2m_pairId = i2p_pairId".
	" AND i2p_regiId = regi_id".
	" AND rank_regiId = regi_id".
	" AND rank_rankdefId = rkdf_id".
	" AND rank_discipline = mtch_disci".
	" AND mber_id = regi_memberId";
		if (! $hide) $where .= " AND mtch_status <".WBS_MATCH_CLOSED;
		$order = "1, regi_teamId, mber_sexe, regi_longName";
		$res = $this->_select($tables, $fields, $where, $order);

		// Construc a table with the match
		$uts = new utscore();
		$teamId=-1;
		$delay =  $ut->getPref('cur_rest', 20);
		while ($player = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $match = $matches[$player['mtch_id']];
	  $uts->setScore($match[9]);
	  $rest = '';
	  $utd->setIsoDateTime($player['regi_rest']);
	  if ($utd->elaps() > 0 && $utd->elaps() < $delay)
	  {
	  	$utd->addMinute($delay);
	  	$rest = $utd->getTime();
	  }
	  // Prepare the name of the player
	  $value = "";
	  $class = "";
	  $oldStatus = $status[$player['mtch_id']];
	  $newStatus = $oldStatus;
	  if ($oldStatus < WBS_MATCH_LIVE)
	  {
	  	$newStatus = WBS_MATCH_READY;
	  	if ($player['regi_wo'] == WBS_YES)
	  	{
	  		$class = 'wo';
	  		$value .= "[WO] ";
	  	}
	  	else if ($player['regi_present'] == WBS_NO)
	  	{
	  		$newStatus = WBS_MATCH_BUSY;
	  		$class = 'absent';
	  		if ($player['regi_delay'] != '')
	  		{
	  			$utd->setIsoDateTime($player['regi_delay']);
	  			$delay = $utd->getTime();
	  			$value .= "[$delay] ";
	  		}
	  	}
	  	else if ($player['regi_court'] != 0)
	  	{
	  		$value .= "[".abs($player['regi_court'])."]";
	  		$class = $player['regi_court'] > 0 ? 'busy':'umpire';
	  		$newStatus = WBS_MATCH_BUSY;
	  	}
	  	else if ($rest != '')
	  	{
	  		$value .= "[$rest] ";
	  		$class = "rest";
	  		$newStatus = WBS_MATCH_REST;
	  	}
	  }
	  else if ($oldStatus == WBS_MATCH_LIVE)
	  {
	  	$class = "live";
	  }

	  $line['class'] = $class;
	  $value .= $player['regi_longName']. "-".
	  $player['rkdf_label'];
	  $line['value'] = $value;
	  $line['action'] = array(KAF_NEWWIN, 'resu', KID_EDIT,
	  $player['regi_id'], 400,200);

	  // Left team
	  if ($player['regi_teamId'] == $teamL)
	  {
	  	$teamId = $player['regi_teamId'];
	  	$match[4][] = $line;
	  	if ($newStatus > WBS_MATCH_LIVE)
	  	$match[10] = utimg::getIcon($player['p2m_result']);
	  	switch ($player['p2m_result'])
	  	{
	  		case WBS_RES_LOOSE:
	  		case WBS_RES_LOOSEAB:
	  		case WBS_RES_LOOSEWO:
	  			$match[9] = $uts->getLoosScore();
	  			break;
	  		default:
	  			break;
	  	}
	  }
	  // right team
	  else
	  {
	  	$teamId=-1;
	  	$match[5][] = $line;
	  	if ($newStatus > WBS_MATCH_LIVE)
	  	$match[11] = utimg::getIcon($player['p2m_result']);
	  }

	  $match[12] = utimg::getIcon($oldStatus);
	  if ($newStatus < $oldStatus)
	  {
	  	$status[$player['mtch_id']] = $newStatus;
	  	$match[12] = utimg::getIcon($newStatus);
	  }

	  // Court of the match
	  if ($status[$player['mtch_id']] > WBS_MATCH_BUSY &&
	  $status[$player['mtch_id']] < WBS_MATCH_ENDED)
	  {
	  	$listCourt['action'] = array(KAF_NEWWIN, 'live', LIVE_DISPLAY_STATUS,0,400,200);
	  	//array(KAF_UPLOAD, 'live', WBS_ACT_LIVE, $match[0]);
	  	$select = array();
	  	$nbCourt = $ut->getPref('cur_nbcourt', 5);
	  	for ($i = 0; $i <= $nbCourt; $i++)
	  	{
	  		$key = $match[0].";$i";
	  		$option = array('key'=>$key, 'value'=>$i);
	  		if ($match[6] == $i)
	  		$option['select'] = true;
	  		$select[] = $option;
	  	}
	  	$listCourt['select'] = $select;
	  	if (!is_array($match[6]))
	  	$match[6] = $listCourt;
	  }

	  $matches[$player['mtch_id']] = $match;
		}

		// prepare the list
		/*      $matchsList = array();
		 foreach ($matches as $matchId=>$match)
		 {
	  $match[12] = utimg::getIcon($match[12]);
	  $matchsList[] = $match;
	  }*/
		return $matches;

	}

	/**
	 * Return the match of a tie
	 */
	function getDefMatchsTie($tieId)
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

		// Select the matchs of the tie
		$fields = array('mtch_id', 'mtch_rank', 'mtch_discipline', 'mtch_order');
		$tables = array('matchs');
		$where = "mtch_del != ".WBS_DATA_DELETE.
	" AND mtch_tieId = $tieId";
		$order = "mtch_rank, mtch_discipline, mtch_order";
		$res = $this->_select($tables, $fields, $where, $order);

		$ut = new utils();
		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $discipline = $match['mtch_discipline'];
	  if ($discipline <6 ) $match['mtch_discipline'] = $ut->getSmaLabel($discipline);
	  else $match['mtch_discipline'] = 'SP';
	  if ($defTie[$discipline]>1) $match['mtch_discipline'] .= " ".$match['mtch_order'];
	  $matches[] = $match;
		}
		return $matches;
	}

	/**
	 * Fix umpire and service judge for a match
	 */
	function setUmpire($infos)
	{
		$where = "mtch_id=".$infos['mtch_id'];
		$res = $this->_update('matchs', $infos, $where);
	}

	/**
	 * Registered the affectation of an umpire
	 */
	function updateUmpire($regiId, $umpire)
	{
		if($umpire['umpi_id'] == -1)
		{
	  unset($umpire['umpi_id']);
	  $umpire['umpi_regiId'] = $regiId;
	  $res = $this->_insert('umpire', $umpire);
		}
		else
		{
	  $where = "umpi_id=".$umpire['umpi_id'];
	  $res = $this->_update('umpire', $umpire, $where);
		}
	}

	/**
	 * deplace the match in a tie
	 */
	function moveMatch($matchId, $sens)
	{
		// Select the rank of the match
		$fields = array('mtch_rank', 'mtch_tieId');
		$tables[] = 'matchs';
		$where = "mtch_id= $matchId";
		$res = $this->_select($tables, $fields, $where);

		$match = $res->fetchRow(DB_FETCHMODE_ORDERED);
		$rank = $match[0];
		$tieId = $match[1];
		$newrank = $rank + $sens;

		$fields = array("COUNT(*)");
		$where = "mtch_tieid= $tieId";
		$res = $this->_select($tables, $fields, $where);
		$nb = $res->fetchRow(DB_FETCHMODE_ORDERED);
		$rankmax = $nb[0];

		if (($newrank > $rankmax) ||
		($newrank < 1))
		return;

		$fields = array();
		$fields['mtch_rank'] = $rank;
		$where = "mtch_tieid= $tieId".
	" AND  mtch_rank = $newrank";      
		$res = $this->_update('matchs', $fields, $where);

		$fields['mtch_rank'] = $newrank;
		$where = "mtch_id= $matchId";
		$res = $this->_update('matchs', $fields, $where);
		return;
	}

	/**
	 * Update the rank of the match of a tie
	 */
	function updateOrderMatch($matchIds)
	{

		$i = 1;
		foreach($matchIds as $matchId)
		{
	  // Select the rank of the match
	  $fields['mtch_rank'] = $i++;
	  $where = "mtch_id= $matchId";
	  $res = $this->_update('matchs', $fields, $where);
		}
		return;
	}

	/**
	 * Return the table with place of the ties
	 */
	function getMatchDependGraph($tieId)
	{
		// Firt select the ties of the day
		$eventId = utvars::getEventId();

		$fields = array('mtch_id', 'i2p_regiId');
		$tables = array('matchs LEFT JOIN p2m ON mtch_id = p2m_matchId
                LEFT JOIN i2p ON  p2m_pairId = i2p_pairId');   
		$where = "mtch_tieId = $tieId";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = "msgNoPlayers";
	  return $infos;
		}
		// Construction de la liste des joueurs par match $players
		// et initalisation du graphe de chaque match
		while ($tmp = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $players[$tmp['mtch_id']][] = $tmp['i2p_regiId'];
	  $graphs[$tmp['mtch_id']] = array();
		}
		// Pour chaque match de la rencontre
		foreach($players as $matchId=>$playersId)
		{
	  // pour chaque joueur du match
	  foreach($playersId as $playerId)
	  // Pour chaque match du graphe
	  foreach($graphs as $graphMatchId => $graphMatchs)
	  {
	  	// Le joueur joue dans le match du graphe
	  	// le match de la rencontre n'est pas dans
	  	// la liste du match du graphe
	  	if (in_array($playerId, $players[$graphMatchId]) &&
		  !in_array($matchId, $graphs[$graphMatchId]))
		  $graphs[$graphMatchId][] = $matchId;
	  }

		}
		return $graphs;
	}

	/**
	 * Return the uniId of a match
	 */
	function getMatchUniId($matchId)
	{
		return $this->_selectFirst('matchs', 'mtch_uniId',
				 "mtch_id={$matchId}");
	}
}
?>
