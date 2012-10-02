<?php
/*****************************************************************************
 !   Module    : Matches
 !   File       : $Source: /cvsroot/aotb/badnet/src/matches/matches_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.32 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/04/04 22:55:51 $
 ******************************************************************************/
require_once "utils/utscore.php";
require_once "utils/utimg.php";
require_once "utils/utevent.php";
require_once "utils/objgroup.php";
require_once "base_A.php";
require_once "matches.inc";
require_once "regi/regi.inc";
require_once "pairs/pairs.inc";


/**
 * Module de gestion des matchs
 *
 * @author Didier BEUVELOT <didier.beuvelot@free.fr>
 * @see to follow
 *
 */

class matches_A
{
	/**
	 * Utils objet
	 */
	var $_ut;

	/**
	 * Database access object
	 */
	var $_dt;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function matches_A()
	{
		$this->_ut = new utils();
		$this->_dt = new matchBase_A();
	}

	/**
	 * Start the connexion processus
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function start($action)
	{

		$id = kform::getData();
		switch ($action)
		{
			case WBS_ACT_MATCHS:
				$this->_displayFormList();
				break;

			case MTCH_EDIT_PAIR_A:
				$this->_displayFormPair($id);
				break;

				// Edit match for a individual event
			case KID_EDIT:
				$utev = new utEvent();
					
				$eventId = utvars::getEventId();
				if ($utev->getEventType($eventId)== WBS_EVENT_INDIVIDUAL)
				$this->_displayFormMatch($id);
				else
				$this->_displayFormMatchTeam($id);
				break;
			case MTCH_UPDATE_PAIR0_A:
				$this->_updateMatch(0);
				break;
			case MTCH_UPDATE_PAIR1_A:
				$this->_updateMatch(1);
				break;
			case MTCH_RAZ_A:
				$this->_updateMatch(2);
				break;

				// Edit match in a tie for a team event
			case MTCH_UPDATE_TEAM0_A:
				$this->_updateMatchTeam(0);
				break;
			case MTCH_UPDATE_TEAM1_A:
				$this->_updateMatchTeam(1);
				break;
			case KID_UPDATE:
				$this->_updateMatchTeam(2);
				break;
			case MTCH_PDF_A:
				require_once "pdf/pdfscoresheet.php";
				$pdf = new pdfScoreSheet();
				$pdf->start();
				$pdf->affichage_match($id);
				$pdf->end();
				break;

			case MTCH_PDF_INDIV_A:
				require_once "pdf/pdfscoresheet.php";
				$pdf = new pdfScoreSheet();
				$pdf->start();
				$pdf->affichage_match_indiv($id);
				$pdf->end();
				break;
			case MTCH_PDF_LIST_A:
				require_once "pdf/pdfscoresheet.php";
				$ids = kform::getInput("rowsList", array());
				if ( ! count($ids)) $ids = array(-1);
				$pdf = new pdfScoreSheet();
				$pdf->start();
				foreach($ids as $id) $pdf->affichage_match_indiv($id);
				$pdf->end();
				break;
			case MTCH_PDF_LIST_LITE_A:
				require_once "pdf/pdfscoresheet.php";
				$pdf = new pdfScoreSheet();
				$pdf->start();

				$ids = kform::getInput("rowsList", array());
				if (count($ids))
				{
					$pdf = new pdfScoreSheet();
					$pdf->start();
					$pdf->affichage_match_lite($ids);
					$pdf->end();
				}
				break;

			default :
				echo "match_A->start($action) non autorise <br>";
				$this->_displayFormList();
		}
		exit;
	}
	// }}}

	// {{{ _updateMatch()
	/**
	 * Update the match in the database
	 *
	 * @access private
	 * @return void
	 */
	function _updateMatch($winPair)
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;
		$uts = new utscore();
		$ute = new utevent();
		$event = $ute->getEvent(utvars::getEventId());
		switch($event['evnt_scoringsystem'])
		{
			case WBS_SCORING_5X7:
				$scoreWo = "7-0 7-0 7-0 WO";
				$nbSet = 5;
				break;
			case WBS_SCORING_5X11:
				$scoreWo = "11-0 11-0 11-0 WO";
				$nbSet = 5;
				break;
			case WBS_SCORING_3X15:
				$scoreWo = "15-0 15-0 WO";
				$nbSet = 3;
				break;
			case WBS_SCORING_3X21:
				$scoreWo = "21-0 21-0 WO";
				$nbSet = 3;
				break;
			case WBS_SCORING_3X11:
				$scoreWo = "11-0 11-0 WO";
				$nbSet = 3;
				break;
			default:
				$scoreWo = "3-0";
				$nbSet=1;
				break;
		}

		// Get the informations
		if ($winPair != 2)
		{
			$infos = array('mtch_id'         => kform::getInput("mtchId"),
			 'mtch_begin'      => kform::getInput("mtchBegin"),
			 'mtch_end'        => kform::getInput("mtchEnd"),
			 'mtch_status'     => kform::getInput("mtchStatus"));
			if(kform::getinput('luckyLooser', false)) $infos['mtch_luckylooser'] = WBS_YES;
			else $infos['mtch_luckylooser'] = WBS_NO;
			 

			// Get winner pair id
			$winPairId = kform::getInput("mtchPairId0");
			$loosPairId  = kform::getInput("mtchPairId1");
			if ($winPair == 1)
			{
				$tmp = $winPairId;
				$winPairId   = $loosPairId;
				$loosPairId  = $tmp;
			}

			// Get  score
			$retired = false;
			if ( kform::getInput("mtchWo", false) )
			{
				$score = $scoreWo;
			}
			else
			{
				$score = "";
				$glue  = "";
				for ($i=0; $i<$nbSet; $i++)
				{
					$sc0 = kform::getInput("mtchSc0$i");
					$sc1 = kform::getInput("mtchSc1$i");
					if ($sc0 != "" &&  $sc1 != "")
					{
						$score .= $glue.$sc0.'-'.$sc1;
						$glue  = " ";
					}
				}
				if (kform::getInput("mtchAbort", false))
				{
					$score .= " Ab";
					$retired = true;
				}
			}
			$res = $uts->setScore($score, $retired);
			// Update match status
			$infos["mtch_status"] = $ute->getMatchStatus();
			if ($infos['mtch_begin']=='') $infos['mtch_begin'] = date(DATE_TIME);
			if ($infos['mtch_end']=='') $infos['mtch_end'] = date(DATE_TIME);

			$infos["mtch_score"] = $uts->getWinScore();
		}
		else
		{
			$infos['mtch_id']     = kform::getInput("mtchId");
			$infos['mtch_end']    = '';
			$infos['mtch_begin']  = '';
			$infos["mtch_status"] = WBS_MATCH_READY;
			$infos["mtch_score"]  = '';
			$infos["mtch_court"]  = 0;
			$infos["mtch_luckylooser"]  = WBS_NO;
			$winPairId = kform::getInput("mtchPairId0");
			$loosPairId  = kform::getInput("mtchPairId1");
		}

		// Enregistrement du resultat
		$res = $dt->updateMatch($infos, $winPairId, $loosPairId);
		if (is_array($res))
		{
			$infos['errMsg'] = $res["errMsg"];
			for ($i=0; $i<$nbSet; $i++)
			{
				$infos["mtch_sc0$i"] = kform::getInput("mtchSc0$i");
				$infos["mtch_sc1$i"] = kform::getInput("mtchSc1$i");
			}
			$infos["mtch_abort"] = kform::getInput("mtchAbort", false);
			$infos["mtch_wo"]    = kform::getInput("mtchWo", false);
			$this->_displayFormMatch($infos);
		}

		// Send the results to subscribers
		if ($infos["mtch_status"] ==  WBS_MATCH_CLOSED)
		{
			require_once "subscript/subscript_V.php";
			$subscript = new subscript_V();
			$subscript->sendMatchResult($infos['mtch_id']);
		}

		//Close the windows
		$page = new utPage('none');
		$cacheRefs = $dt->getCacheRef($infos['mtch_id']);
		foreach($cacheRefs as $cacheRef)
		$page->clearCache($cacheRef['page'], $cacheRef['act'], $cacheRef['data']);

		$draw = $dt->needDraw($infos['mtch_id']);
		// Match de poule
		if ( $draw['rund_type'] == WBS_ROUND_GROUP)
		{
			$oGroup = new objgroup();
			$group = $oGroup->getGroup($draw['rund_drawId'], $draw['rund_group']);
			// Il reste des macth dans la poule,on quitte
			if ($draw['matchGroup'])
			{
				$page->close(false);
				exit;
			}
			
			$roundId = $group['mainroundid'];
			// Il n'y a pas de phase finale 			
			if ($roundId == 0)
			{
				$page->close(false);
				exit;
				//$ut->loadPage('draws', DRAW_PDF_GROUPS, $draw['rund_drawId'].';'.$draw['rund_group']);
			}
			// Il n' y a as de joueur dans le tableau final : tirage au sort 
			else if (!$draw['pairsKo'])
			{
				$ut->loadPage('pairs', PAIR_UPDATE_GROUP2KO, $draw['rund_drawId'].';'.$draw['rund_group']);
			}
			// Le tirage au sort a deja été fait : affaiche de la fenetrede sortie de poule
			else
			{
				$ut->loadPage('pairs', PAIR_GROUP2KO, $draw['rund_drawId'].';'.$draw['rund_group'].';-1');
			}
			//if (!$draw['matchKo']) 
			// Sinon tirage au sort
			//else $ut->loadPage('pairs', PAIR_GROUP2KO, $draw['rund_drawId']);
		}
		// C'est un match par Ko
		else
		{
			// Il n'y a pas plus de match: pdf du tableau
			//if (!$draw['matchKo']) $ut->loadPage('draws', DRAW_PDF_KO, $draw['rund_id']);
			// Sinon on ferme
			//else
			{
				$page->close(false);
			}
		}
		exit;
	}
	// }}}

	// {{{ _updateMatchTeam()
	/**
	 * Update the match in the database
	 *
	 * @access private
	 * @return void
	 */
	function _updateMatchTeam($winTeamId)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$uts = new utscore();
		$ute = new utevent();
		$event = $ute->getEvent(utvars::getEventId());
		switch($event['evnt_scoringsystem'])
		{
			case WBS_SCORING_5X7:
			case WBS_SCORING_5X11:
				$nbSet = 5;
				break;
			case WBS_SCORING_3X15:
			case WBS_SCORING_3X21:
			case WBS_SCORING_3X11:
				$nbSet = 3;
				break;
			default:
				$nbSet=1;
		}

		// Get the informations
		$data = array('mtch_id'     =>kform::getInput("mtchId"),
		    "mtch_sc00"   =>kform::getInput("mtchSc00"),
		    "mtch_sc01"   =>kform::getInput("mtchSc01"),
		    "mtch_sc02"   =>kform::getInput("mtchSc02"),
		    "mtch_sc10"   =>kform::getInput("mtchSc10"),
		    "mtch_sc11"   =>kform::getInput("mtchSc11"),
		    "mtch_sc12"   =>kform::getInput("mtchSc12"),
		    "mtch_regiId00"   =>kform::getInput("mtchRegiId00"),
		    "mtch_regiId01"   =>kform::getInput("mtchRegiId01"),
		    "mtch_regiId10"   =>kform::getInput("mtchRegiId10"),
		    "mtch_regiId11"   =>kform::getInput("mtchRegiId11"),
		    "mtch_wo"      =>kform::getInput("mtchWo"),
		    "mtch_abort"   =>kform::getInput("mtchAbort"),
		    "mtch_status"  =>$ute->getMatchStatus(),
		    "mtch_begin"   =>kform::getInput("mtchBegin"),
		    "mtch_end"     =>kform::getInput("mtchEnd")
		);
		$match = $dt->getMatchTeam($data['mtch_id']);
		$infos= array_merge($match, $data);

		if ($winTeamId != 2)
		{
	  // Calculate score and winners
	  $score = "";
	  $glue  = "";
	  for ($i=0; $i<$nbSet; $i++)
	  {
	  	$sc0 = kform::getInput("mtchSc0$i");
	  	$sc1 = kform::getInput("mtchSc1$i");
	  	if ($sc0 != "" &&  $sc1 != "")
	  	{
	  		$score .= $glue.$sc0.'-'.$sc1;
	  		$glue  = " ";
	  	}
	  }
	  $retired = false;
	  if ($infos["mtch_wo"] != "")
	  $score .= " WO";
	  else if ($infos["mtch_abort"] != "")
	  {
	  	$score .= " Ab";
	  	$retired = true;
	  }
	  if ($winTeamId == 0)
	  {
	  	$infos["mtch_winId0"] = $infos["mtch_regiId00"];
	  	$infos["mtch_winId1"] = $infos["mtch_regiId01"];
	  	$infos["mtch_loosId0"] = $infos["mtch_regiId10"];
	  	$infos["mtch_loosId1"] = $infos["mtch_regiId11"];
	  }
	  else if ($winTeamId == 1)
	  {
	  	$infos["mtch_winId0"] = $infos["mtch_regiId10"];
	  	$infos["mtch_winId1"] = $infos["mtch_regiId11"];
	  	$infos["mtch_loosId0"] = $infos["mtch_regiId00"];
	  	$infos["mtch_loosId1"] = $infos["mtch_regiId01"];
	  }
	  else
	  {
	  	$infos['errMsg'] = "Erreur: equipe gagnante erronee!!!$winTeamId<br>";
	  	print_r($match);
	  	$this->_displayFormMatchTeam($infos);
	  	exit;
	  }

	  // Control the informations
	  /* If WO, force the score to WO */
	  if ( (($dt->isRegiWO($infos["mtch_loosId0"]) ||
		 $dt->isRegiWO($infos["mtch_loosId1"])) &&
		 $infos["mtch_wo"] == "" &&
		 $infos["mtch_abort"] == "") ||
		 $infos["mtch_wo"] != "" )
		 {
		 	if( $event['evnt_scoringsystem'] == WBS_SCORING_5X7 )
		 	$score = "7-0 7-0 7-0 WO";
		 	else if( $event['evnt_scoringsystem'] == WBS_SCORING_3X15 )
		 	if($infos["mtch_discipline"] != WBS_LS)
		  $score = "15-0 15-0 WO";
		  else
		  $score = "11-0 11-0 WO";
		  else if( $event['evnt_scoringsystem'] == WBS_SCORING_3X21 )
		  $score = "21-0 21-0 WO";
		  else
		  $score = "WO";
		 }

	  /* The winner can't be WO */
	  if ($dt->isRegiWO($infos["mtch_winId0"]) ||
	  $dt->isRegiWO($infos["mtch_winId1"]))
	  {
	  	$infos['errMsg'] = "msgWinNotWO";
	  	$this->_displayFormMatchTeam($infos);
	  }

	  /* We can't have the same player in a team */
	  if (($infos["mtch_winId0"] == $infos["mtch_winId1"] &&
	  !$dt->isRegiWO($infos["mtch_winId0"])) ||
	  ($infos["mtch_loosId0"] == $infos["mtch_loosId1"] &&
	  !$dt->isRegiWO($infos["mtch_loosId0"])))
	  {
	  	$infos['errMsg'] = "msgNotSamePlayer";
	  	$this->_displayFormMatchTeam($infos);
	  }

	  $res = $uts->setScore($score, $retired);
	  if ($res == -1)
	  $infos["mtch_status"] = WBS_MATCH_READY;
	  else
	  {
	  	if ($infos['mtch_begin']=='')
	  	$infos['mtch_begin'] = date(DATE_TIME);
	  	if ($infos['mtch_end']=='')
	  	$infos['mtch_end'] = date(DATE_TIME);
	  }
	  $infos["mtch_score"] = $uts->getWinScore();
		}
		else
		{
			$infos["mtch_status"]  = WBS_MATCH_READY;
			$infos["mtch_status"]  = WBS_MATCH_READY;
			$infos["mtch_winId0"]  = $infos["mtch_regiId00"];
			$infos["mtch_winId1"]  = $infos["mtch_regiId01"];
			$infos["mtch_loosId0"] = $infos["mtch_regiId10"];
			$infos["mtch_loosId1"] = $infos["mtch_regiId11"];
			$infos['mtch_begin']   = '';
			$infos['mtch_end']     = '';
			$infos["mtch_score"]   = '';
		}
		// Add the registration
		//      print_r($infos);
		$infos['updateDraw'] = kform::getInput("updateDraw");
		$res = $dt->updateMatchTeam($infos);
		if (is_array($res))
		{
	  		$infos['errMsg'] = $res["errMsg"];
	  		$this->_displayFormMatchTeam($infos);
		}

		// Send the results to subscribers
		if ($infos["mtch_status"] ==  WBS_MATCH_CLOSED)
		{
	  require_once "subscript/subscript_V.php";
	  $subscript = new subscript_V();
	  $subscript->sendMatchResult($infos['mtch_id']);
		}
		//Close the windows
		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _updateMatchTeam()
	/**
	* Update the match in the database
	*
	* @access private
	* @return void
	*/
	function _updateMatchTeamOld($winTeamId)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$uts = new utscore();
		$ute = new utevent();
		$event = $ute->getEvent(utvars::getEventId());
		switch($event['evnt_scoringsystem'])
		{
			case WBS_SCORING_5X7:
				$scoreWo = "7-0 7-0 7-0 WO";
				$nbSet = 5;
				break;
			case WBS_SCORING_5X11:
				$scoreWo = "11-0 11-0 11-0 WO";
				$nbSet = 5;
				break;
			case WBS_SCORING_3X15:
				$scoreWo = "15-0 15-0 WO";
				$nbSet = 3;
				break;
			case WBS_SCORING_3X21:
				$scoreWo = "21-0 21-0 WO";
				$nbSet = 3;
				break;
			case WBS_SCORING_3X11:
				$scoreWo = "11-0 11-0 WO";
				$nbSet = 3;
				break;
			default:
				$scoreWo = "3-0";
				$nbSet=1;
				break;
		}

		// Get the informations
		$data = array('mtch_id'     =>kform::getInput("mtchId"),
		    "mtch_sc00"   =>kform::getInput("mtchSc00"),
		    "mtch_sc01"   =>kform::getInput("mtchSc01"),
		    "mtch_sc02"   =>kform::getInput("mtchSc02"),
		    "mtch_sc10"   =>kform::getInput("mtchSc10"),
		    "mtch_sc11"   =>kform::getInput("mtchSc11"),
		    "mtch_sc12"   =>kform::getInput("mtchSc12"),
		    "mtch_regiId00"   =>kform::getInput("mtchRegiId00"),
		    "mtch_regiId01"   =>kform::getInput("mtchRegiId01"),
		    "mtch_regiId10"   =>kform::getInput("mtchRegiId10"),
		    "mtch_regiId11"   =>kform::getInput("mtchRegiId11"),
		    "mtch_wo"      =>kform::getInput("mtchWo"),
		    "mtch_abort"   =>kform::getInput("mtchAbort"),
		    "mtch_status"  =>$ute->getMatchStatus(),
		    "mtch_begin"   =>kform::getInput("mtchBegin"),
		    "mtch_end"     =>kform::getInput("mtchEnd")
		);
		$match = $dt->getMatchTeam($data['mtch_id']);
		$infos= array_merge($match, $data);

		if ($winTeamId != 2)
		{
	  // Calculate score and winners
	  $score = "";
	  $glue  = "";
	  for ($i=0; $i<$nbSet; $i++)
	  {
	  	$sc0 = kform::getInput("mtchSc0$i");
	  	$sc1 = kform::getInput("mtchSc1$i");
	  	if ($sc0 != "" &&  $sc1 != "")
	  	{
	  		$score .= $glue.$sc0.'-'.$sc1;
	  		$glue  = " ";
	  	}
	  }
	  $retired = false;
	  if ($infos["mtch_wo"] != "") $score .= " WO";
	  else if ($infos["mtch_abort"] != "")
	  {
	  	$score .= " Ab";
	  	$retired = true;
	  }
	  if ($winTeamId == 0)
	  {
	  	$infos["mtch_winId0"] = $infos["mtch_regiId00"];
	  	$infos["mtch_winId1"] = $infos["mtch_regiId01"];
	  	$infos["mtch_loosId0"] = $infos["mtch_regiId10"];
	  	$infos["mtch_loosId1"] = $infos["mtch_regiId11"];
	  }
	  else if ($winTeamId == 1)
	  {
	  	$infos["mtch_winId0"] = $infos["mtch_regiId10"];
	  	$infos["mtch_winId1"] = $infos["mtch_regiId11"];
	  	$infos["mtch_loosId0"] = $infos["mtch_regiId00"];
	  	$infos["mtch_loosId1"] = $infos["mtch_regiId01"];
	  }
	  else
	  {
	  	$infos['errMsg'] = "Erreur: equipe gagnante erronee!!!$winTeamId<br>";
	  	print_r($match);
	  	$this->_displayFormMatchTeam($infos);
	  	exit;
	  }

	  // Control the informations
	  /* If WO, force the score to WO */
	  if ( (($dt->isRegiWO($infos["mtch_loosId0"]) ||
		 $dt->isRegiWO($infos["mtch_loosId1"])) &&
		 $infos["mtch_wo"] == "" &&
		 $infos["mtch_abort"] == "") ||
		 $infos["mtch_wo"] != "" )
		 {
		 	$score = $scoreWo;
		 }

	  /* The winner can't be WO */
	  if ($dt->isRegiWO($infos["mtch_winId0"]) ||
	  $dt->isRegiWO($infos["mtch_winId1"]))
	  {
	  	$infos['errMsg'] = "msgWinNotWO";
	  	$this->_displayFormMatchTeam($infos);
	  }

	  /* We can't have the same player in a team */
	  if (($infos["mtch_winId0"] == $infos["mtch_winId1"] &&
	  !$dt->isRegiWO($infos["mtch_winId0"])) ||
	  ($infos["mtch_loosId0"] == $infos["mtch_loosId1"] &&
	  !$dt->isRegiWO($infos["mtch_loosId0"])))
	  {
	  	$infos['errMsg'] = "msgNotSamePlayer";
	  	$this->_displayFormMatchTeam($infos);
	  }

	  $res = $uts->setScore($score, $retired);
	  if ($res == -1)
	  $infos["mtch_status"] = WBS_MATCH_READY;
	  else
	  {
	  	if ($infos['mtch_begin']=='')
	  	$infos['mtch_begin'] = date(DATE_TIME);
	  	if ($infos['mtch_end']=='')
	  	$infos['mtch_end'] = date(DATE_TIME);
	  }
	  $infos["mtch_score"] = $uts->getWinScore();
		}
		else
		{
			$infos["mtch_status"]  = WBS_MATCH_READY;
			$infos["mtch_winId0"]  = $infos["mtch_regiId00"];
			$infos["mtch_winId1"]  = $infos["mtch_regiId01"];
			$infos["mtch_loosId0"] = $infos["mtch_regiId10"];
			$infos["mtch_loosId1"] = $infos["mtch_regiId11"];
			$infos['mtch_begin']   = '';
			$infos['mtch_end']     = '';
			$infos["mtch_score"]   = '';
		}
		// Add the registration
		//      print_r($infos);
		$res = $dt->updateMatchTeam($infos);
		if (is_array($res))
		{
	  $infos['errMsg'] = $res["errMsg"];
	  $this->_displayFormMatchTeam($infos);
		}

		// Send the results to subscribers
		if ($infos["mtch_status"] ==  WBS_MATCH_CLOSED)
		{
	  require_once "subscript/subscript_V.php";
	  $subscript = new subscript_V();
	  $subscript->sendMatchResult($infos['mtch_id']);
		}
		//Close the windows
		$page = new kPage('none');
		//$page->close();
		exit;
	}

	/**
	 * Display the page for editing result of a match for an team event
	 *
	 * @access private
	 * @param array $matchId  info of the Match
	 * @return void
	 */
	function _displayFormMatchTeam($matchId)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$uts = new utscore();

		$utpage = new utPage('matches');
		$content =& $utpage->getPage();
		$utpage->_page->addJavaFile('matches/matches.js');
		$form =& $content->addForm('fmatches', 'matches', KID_UPDATE);
		$form->setTitle('tEditMatch');

		$ute = new utevent();
		$event = $ute->getEvent(utvars::getEventId());
		switch($event['evnt_scoringsystem'])
		{
			case WBS_SCORING_5X7:
			case WBS_SCORING_5X11:
				$nbSet = 5;
				break;
			case WBS_SCORING_3X15:
			case WBS_SCORING_3X21:
			case WBS_SCORING_3X11:
				$nbSet = 3;
				break;
			default:
				$nbSet=1;
		}

		// Initialize the field
		if ( is_array($matchId))
		{
	  		$match = $dt->getMatchTeam($matchId['mtch_id']);
	  		$infos= array_merge($match, $matchId);
		}
		else
		{
	  		$infos = $dt->getMatchTeam($matchId);
	  		for ($i=0; $i<$nbSet; $i++)
	  		{
	  			$infos["mtch_sc0$i"] = "";
	  			$infos["mtch_sc1$i"] = "";
	  		}
	  		$res = $uts->setScore($infos['mtch_score']);
	  		$gamesW = $uts->getWinGames();
	  		$gamesL = $uts->getLoosGames();
	  		$infos["mtch_wo"] = $uts->isWO();
	  		$infos["mtch_abort"] = $uts->isAbort();
	  		if (is_array($gamesW))
	  		for ($i=0; $i<count($gamesW); $i++)
	  		{
	  			$infos["mtch_sc0$i"] = $gamesW[$i];
	  			$infos["mtch_sc1$i"] = $gamesL[$i];
	  		}
		}
		$infos["mtch_labdisci"] = $ut->getLabel($infos["mtch_discipline"]);
		$infos["mtch_statusName"] = $ut->getLabel($infos["mtch_status"]);
		if ($infos['mtch_regiId00']=='')
		$infos['mtch_regiId00']  = kform::getInput("mtchRegiId00");
		if ($infos['mtch_regiId01']=='')
		$infos['mtch_regiId01']  = kform::getInput("mtchRegiId01");
		if ($infos['mtch_regiId10']=='')
		$infos['mtch_regiId10']  = kform::getInput("mtchRegiId10");
		if ($infos['mtch_regiId11']=='')
		$infos['mtch_regiId11']  = kform::getInput("mtchRegiId11");

		// Display the error if any
		if (isset($infos['errMsg']))
		$form->addWng($infos['errMsg']);

		// Initialize the field
		$form->addHide('mtchId',         $infos['mtch_id']);

		$form->addInfo('mtchNum',    $infos['mtch_num']);
		$form->addInfo('mtchDisci',  $infos['mtch_labdisci']);
		$form->addInfo('mtchOrder',  $infos['mtch_order']);
		$form->addInfo('mtchStatus', $infos['mtch_statusName']);

		for($i = 0; $i<$nbSet; $i++)
		{
	  for($j = 0; $j<2; $j++)
	  {
	  	$kedit =& $form->addEdit("mtchSc$j$i", $infos["mtch_sc$j$i"], 2);
	  	$kedit->noMandatory();
	  	if ($event['evnt_scoringsystem'] != WBS_SCORING_NONE)
	  	{
	  		$kedit->setMaxLength(2);
	  		$nj = ($j+1)%2;
	  		$ni = $i+ $j;
	  		$kedit->autoNextField("mtchSc$nj$ni");
	  	}
	  }
	  $elts = array("mtchSc0$i", "mtchSc1$i");
	  $kblk =& $form->addBlock("blkSet$i", $elts, 'classSet');
	  $eltscore[] = "blkSet$i";
		}
		$kedit->autoNextField("mtchEnd");

		$form->addCheck('mtchWo', $infos['mtch_wo']);
		$form->addCheck('mtchAbort', $infos['mtch_abort']);
		
		//if ($infos['rund_type'] == WBS_TEAM_KO)	
		$form->addCheck('updateDraw', true);
		//else $form->addHide('updateDraw', false);
		
		//$form->addArea("mtchCmt", $infos['mtch_cmt'], 35 );
		//$form->noMandatory();

		switch($infos["mtch_discipline"])
		{
			case WBS_AS:
				$players = $dt->getRegiPlayers($infos['mtch_teamId0'], -1, -1);
				$sel = "";
				if (isset($players[$infos["mtch_regiId00"]]))
				$sel=$players[$infos["mtch_regiId00"]];
				$form->addCombo("mtchRegiId00", $players,$sel);

				$players = $dt->getRegiPlayers($infos['mtch_teamId1'], -1, -1);
				$sel = "";
				if (isset($players[$infos["mtch_regiId10"]]))
				$sel=$players[$infos["mtch_regiId10"]];
				$form->addCombo("mtchRegiId10", $players, $sel);
				break;

			case WBS_MS:
				$players = $dt->getRegiPlayers($infos['mtch_teamId0'], WBS_MALE,
				$infos["mtch_discipline"]);
				$sel = "";
				if (isset($players[$infos["mtch_regiId00"]]))
				$sel=$players[$infos["mtch_regiId00"]];
				$form->addCombo("mtchRegiId00", $players,$sel);

				$players = $dt->getRegiPlayers($infos['mtch_teamId1'], WBS_MALE,
				$infos["mtch_discipline"]);
				$sel = "";
				if (isset($players[$infos["mtch_regiId10"]]))
				$sel=$players[$infos["mtch_regiId10"]];
				$form->addCombo("mtchRegiId10", $players, $sel);
				break;

			case WBS_LS:
				$players = $dt->getRegiPlayers($infos['mtch_teamId0'], WBS_FEMALE,
				$infos["mtch_discipline"]);
				$sel = "";
				if (isset($players[$infos["mtch_regiId00"]]))
				$sel=$players[$infos["mtch_regiId00"]];
				$form->addCombo("mtchRegiId00", $players, $sel);
				$players = $dt->getRegiPlayers($infos['mtch_teamId1'], WBS_FEMALE,
				$infos["mtch_discipline"]);
				$sel = "";
				if (isset($players[$infos["mtch_regiId10"]]))
				$sel=$players[$infos["mtch_regiId10"]];
				$form->addCombo("mtchRegiId10", $players, $sel);
				break;

			case WBS_MD:
				$players = $dt->getRegiPlayers($infos['mtch_teamId0'], WBS_MALE,
				$infos["mtch_discipline"]);
				$sel = "";
				if (isset($players[$infos["mtch_regiId00"]]))
				$sel=$players[$infos["mtch_regiId00"]];
				$form->addCombo("mtchRegiId00", $players, $sel);

				$sel = "";
				if (isset($players[$infos["mtch_regiId01"]]))
				$sel=$players[$infos["mtch_regiId01"]];
				$form->addCombo("mtchRegiId01", $players, $sel);

				$players = $dt->getRegiPlayers($infos['mtch_teamId1'], WBS_MALE,
				$infos["mtch_discipline"]);
				$sel = "";
				if (isset($players[$infos["mtch_regiId10"]]))
				$sel=$players[$infos["mtch_regiId10"]];
				$form->addCombo("mtchRegiId10", $players, $sel);

				$sel = "";
				if (isset($players[$infos["mtch_regiId11"]]))
				$sel=$players[$infos["mtch_regiId11"]];
				$form->addCombo("mtchRegiId11", $players, $sel);
				break;

			case WBS_LD:
				$players = $dt->getRegiPlayers($infos['mtch_teamId0'], WBS_FEMALE,
				$infos["mtch_discipline"]);
				$sel = "";
				if (isset($players[$infos["mtch_regiId00"]]))
				$sel=$players[$infos["mtch_regiId00"]];
				$form->addCombo("mtchRegiId00", $players, $sel);

				$sel = "";
				if (isset($players[$infos["mtch_regiId01"]]))
				$sel=$players[$infos["mtch_regiId01"]];
				$form->addCombo("mtchRegiId01", $players, $sel);

				$players = $dt->getRegiPlayers($infos['mtch_teamId1'], WBS_FEMALE,
				$infos["mtch_discipline"]);

				$sel = "";
				if (isset($players[$infos["mtch_regiId10"]]))
				$sel=$players[$infos["mtch_regiId10"]];
				$form->addCombo("mtchRegiId10", $players, $sel);

				$sel = "";
				if (isset($players[$infos["mtch_regiId11"]]))
				$sel=$players[$infos["mtch_regiId11"]];
				$form->addCombo("mtchRegiId11", $players, $sel);
				break;

			case WBS_MX:
				$players = $dt->getRegiPlayers($infos['mtch_teamId0'], WBS_MALE,
				$infos["mtch_discipline"]);

				$sel = "";
				if (isset($players[$infos["mtch_regiId00"]]))
				$sel=$players[$infos["mtch_regiId00"]];
				$form->addCombo("mtchRegiId00", $players, $sel);

				$players = $dt->getRegiPlayers($infos['mtch_teamId0'], WBS_FEMALE,
				$infos["mtch_discipline"]);
				$sel = "";
				if (isset($players[$infos["mtch_regiId01"]]))
				$sel=$players[$infos["mtch_regiId01"]];
				$form->addCombo("mtchRegiId01", $players, $sel);

				$players = $dt->getRegiPlayers($infos['mtch_teamId1'], WBS_MALE,
				$infos["mtch_discipline"]);
				$sel = "";
				if (isset($players[$infos["mtch_regiId10"]]))
				$sel=$players[$infos["mtch_regiId10"]];
				$form->addCombo("mtchRegiId10", $players, $sel);

				$players = $dt->getRegiPlayers($infos['mtch_teamId1'], WBS_FEMALE,
				$infos["mtch_discipline"]);
				$sel = "";
				if (isset($players[$infos["mtch_regiId11"]]))
				$sel=$players[$infos["mtch_regiId11"]];
				$form->addCombo("mtchRegiId11", $players, $sel);
				break;

			default:
				$form->addWng("Erreur interne: Type de match inconnu:".
				$infos["mtch_discipline"]);
				break;
		}

		$form->addBlock('blkNum', 'mtchNum');
		$form->addBlock('blkDisci', 'mtchDisci');
		$form->addBlock('blkOrder', 'mtchOrder');
		$form->addBlock('blkStatus', 'mtchStatus');
		$elts = array('blkNum', 'blkDisci', 'blkOrder', 'blkStatus');
		$form->addBlock('blkInfo', $elts);


		$kbtn =& $form->addBtn('btnAddPlayer0', KAF_NEWWIN, 'regi',
		REGI_SEARCH_TEAM, $infos['mtch_teamId0']);

		$elts = array('mtchRegiId00', 'mtchRegiId01', 'btnAddPlayer0');
		$kblk =& $form->addBlock('blkTeam0', $elts, 'classTeam');
		$kblk->setTitle("{$infos['mtch_team0']} ({$infos['mtch_stamp0']})");

		$kbtn =& $form->addBtn('btnAddPlayer1', KAF_NEWWIN, 'regi', REGI_SEARCH_TEAM, $infos['mtch_teamId1']);
		$elts = array('mtchRegiId10', 'mtchRegiId11','btnAddPlayer1');
		$kblk =& $form->addBlock('blkTeam1', $elts, 'classTeam');
		$kblk->setTitle("{$infos['mtch_team1']} ({$infos['mtch_stamp1']})");

		$elts = array('mtchWo', 'mtchAbort');
		$kblk =& $form->addBlock('blkExtra', $elts);
		
		$elts = array('updateDraw');
		$kblk =& $form->addBlock('blkUpdate', $elts);
		
		$form->addImg('img0', utimg::getIcon($infos['mtch_result00']));
		$kbtn =& $form->addBtn('btnTeam0', 'controlscore', $event['evnt_scoringsystem'],
		MTCH_UPDATE_TEAM0_A);
		$kbtn->setLabel($infos['mtch_stamp0']);

		$form->addImg('img1', utimg::getIcon($infos['mtch_result10']));
		$kbtn =& $form->addBtn('btnTeam1', 'controlscore', $event['evnt_scoringsystem'],
		MTCH_UPDATE_TEAM1_A);
		$kbtn->setLabel($infos['mtch_stamp1']);

		$kedit =& $form->addEdit('mtchBegin', $infos['mtch_begin'], 14);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('mtchEnd', $infos['mtch_end'], 14);
		$kedit->noMandatory();

		$elts = array('mtchBegin', 'mtchEnd');
		$form->addBlock('blkTime', $elts);

		$elts = array('img0', 'btnTeam0', 'btnTeam1', 'img1');
		$form->addBlock('blkRegister', $elts);

		$form->addDiv('break', 'blkNewPage');
		$form->addDiv('break2', 'blkNewPage');
		
		$eltscore[] = 'blkExtra';
		$eltscore[] = 'blkTime';
		$eltscore[] = 'break';
		$eltscore[] = 'blkUpdate';
		$eltscore[] = 'break2';
		$eltscore[] = 'blkRegister';
		$form->addBlock('blkScore', $eltscore);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);
		//Display the page
		$utpage->display();

		exit;
	}

	/**
	 * Display a page with the list of the Matchs
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayFormList($sort=1)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage_A('matches', true, 4);
		$content =& $utpage->getContentDiv();

		$items['itDelete'] = array(KAF_VALID, 'matches', KID_DELETE);
		$content->addMenu('menuMatchs', $items);

		$sort = $form->getSort("rowsMatchs");
		$rows = $dt->getMatches($sort);

		if (isset($rows['errMsg']))
		$content->addWng($rows['errMsg']);
		else
		{
	  $krow = &$content->addRows("rowsMatchs", $rows);
	  $sizes = array(4=> '0', 6 =>'0', '0');
	  $krow->setSize($sizes);

	  $actions[1] = array(KAF_NEWWIN, 'matches',KID_EDIT, 0, 500,450);
	  $actions[3] = array(KAF_NEWWIN, 'pairs',KID_EDIT, 4, 500,350);
	  $actions[5] = array(KAF_NEWWIN, 'pairs',KID_EDIT, 6, 500,350);

	  $krow->setActions($actions);
	  $form->addBlock('blkMatchs', 'rowsMatchs');
		}

		$utpage->display();
		exit;
	}

	/**
	 * Display the page for creating a new Match
	 *
	 * @access private
	 * @param array $Match  info of the Match
	 * @return void
	 */
	function _displayFormMatch($matchId="")
	{
		$ut = $this->_ut;
		$uts = new utscore();
		$dt = $this->_dt;

		$ute = new utevent();
		$event = $ute->getEvent(utvars::getEventId());
		switch($event['evnt_scoringsystem'])
		{
			case WBS_SCORING_5X7:
			case WBS_SCORING_5X11:
				$nbSet = 5;
				break;
			case WBS_SCORING_3X15:
			case WBS_SCORING_3X21:
			case WBS_SCORING_3X11:
				$nbSet = 3;
				break;
			default:
				$nbSet=1;
		}

		// Initialize the page
		$utpage = new utPage('matches');
		$utpage->_page->addJavaFile('matches/matches.js');
		//$utpage->_page->addAction('onunload', array('refresh'));
		$content =& $utpage->getPage();
		$form =& $content->addForm('formMatches', 'matches', MTCH_UPDATE_PAIR1_A);
		$form->setTitle('tEditMatch');

		// Initialize the field
		if ( is_array($matchId))
		{
	  $match = $dt->getMatch($matchId['mtch_id']);
	  $infos= array_merge($match, $matchId);
		}
		else
		{
	  $infos = $dt->getMatch($matchId);
	  for ($i=0; $i<$nbSet; $i++)
	  {
	  	$infos["mtch_sc0$i"] = "";
	  	$infos["mtch_sc1$i"] = "";
	  }
	  $res = $uts->setScore($infos['mtch_score']);
	  $gamesW = $uts->getWinGames();
	  $gamesL = $uts->getLoosGames();
	  $infos["mtch_wo"] = $uts->isWO();
	  $infos["mtch_abort"] = $uts->isAbort();
	  if (is_array($gamesW))
	  for ($i=0; $i<count($gamesW); $i++)
	  {
	  	$infos["mtch_sc0$i"] = $gamesW[$i];
	  	$infos["mtch_sc1$i"] = $gamesL[$i];
	  }
		}
		$infos["mtch_labdisci"] = $ut->getLabel($infos["mtch_discipline"]);
		$infos["mtch_statusName"] = $ut->getLabel($infos["mtch_status"]);

		// Display the error if any
		if (isset($infos['errMsg']))
		$form->addWng($infos['errMsg']);

		// Initialize the field
		$form->addHide('mtchId',         $infos['mtch_id']);
		$form->addHide('mtchDiscipline', $infos['mtch_discipline']);
		$form->addHide('mtchStatus',     $infos['mtch_status']);
		$form->addHide('mtchPairId0',    $infos['mtch_pairId0']);
		$form->addHide('mtchPairId1',    $infos['mtch_pairId1']);

		$form->addInfo('mtchNum',    $infos['mtch_num']);
		$form->addInfo('mtchDisci',  $infos['mtch_labdisci']);
		$form->addInfo('mtchStatusName', $infos['mtch_statusName']);
		$form->addInfo('mtchCourt',  $infos['mtch_court']);
		$form->addInfo('mtchDraw',   $infos['draw_name']);
		$form->addInfo('mtchRound',  $infos['rund_name']);
		$form->addInfo('mtchStep',   $ut->getLabel($infos['mtch_step']));
		$form->addInfo('mtchUmpire', $infos['regi_longName']);

		for($i = 0; $i<$nbSet; $i++)
		{
	  for($j = 0; $j<2; $j++)
	  {
	  	$kedit =& $form->addEdit("mtchSc$j$i", $infos["mtch_sc$j$i"], 2);
	  	$kedit->noMandatory();
	  	if ($event['evnt_scoringsystem'] != WBS_SCORING_NONE)
	  	{
	  		$kedit->setMaxLength(2);
	  		$nj = ($j+1)%2;
	  		$ni = $i+ $j;
	  		$kedit->autoNextField("mtchSc$nj$ni");
	  	}
	  }
	  $elts = array("mtchSc0$i", "mtchSc1$i");
	  $kblk =& $form->addBlock("blkSet$i", $elts, 'classSet');
	  $eltscore[] = "blkSet$i";
		}
		$kedit->autoNextField("mtchEnd");

		$form->addCheck('mtchWo', $infos['mtch_wo']);
		$form->addCheck('mtchAbort', $infos['mtch_abort']);
		$form->addCheck('luckyLooser', $infos['mtch_luckylooser'] == WBS_YES);
		
		$form->addInfo("mtchRegiId00", $infos['mtch_player00']);
		$form->addInfo("mtchRegiId01", $infos['mtch_player01']);
		$form->addInfo("mtchRegiId10", $infos['mtch_player10']);
		$form->addInfo("mtchRegiId11", $infos['mtch_player11']);

		$form->addBlock('blkNum', 'mtchNum');
		$form->addBlock('blkDisci', 'mtchDisci');
		$form->addBlock('blkStatus', 'mtchStatusName');
		$form->addBlock('blkCourt', 'mtchCourt');
		$elts = array('blkNum', 'blkDisci', 'blkStatus', 'blkCourt');
		$form->addBlock('blkInfo', $elts);

		$form->addBlock('blkDraw', 'mtchDraw');
		$form->addBlock('blkRound', 'mtchRound');
		$form->addBlock('blkUmpire', 'mtchUmpire');
		$form->addBlock('blkStep', 'mtchStep');
		$elts = array('blkDraw', 'blkRound', 'blkStep', 'blkUmpire');
		$form->addBlock('blkInfo2', $elts);

		$elts = array('mtchRegiId00', 'mtchRegiId01');
		$kblk =& $form->addBlock('blkTeam0', $elts, 'classTeam');

		$elts = array('mtchRegiId10', 'mtchRegiId11');
		$kblk =& $form->addBlock('blkTeam1', $elts, 'classTeam');

		$elts = array('mtchWo', 'mtchAbort', 'luckyLooser');
		$kblk =& $form->addBlock('blkExtra', $elts);

		if($infos['mtch_status'] != WBS_MATCH_INCOMPLETE)
		{
	  $form->addImg('img0', utimg::getIcon($infos['mtch_result0']));
	  $kbtn =& $form->addBtn('btnTeam0', 'controlscore',
	  $event['evnt_scoringsystem'],
	  MTCH_UPDATE_PAIR0_A);
	  $kbtn->setLabel($infos['mtch_player00']);

	  $form->addImg('img1', utimg::getIcon($infos['mtch_result1']));
	  $kbtn =& $form->addBtn('btnTeam1', 'controlscore',
	  $event['evnt_scoringsystem'],
	  MTCH_UPDATE_PAIR1_A);
	  $kbtn->setLabel($infos['mtch_player10']);
		}
		$form->addBtn('btnRaz', KAF_UPLOAD, 'matches',
		MTCH_RAZ_A);
		$form->addBtn('btnCancel');


		$kedit =& $form->addEdit('mtchBegin', $infos['mtch_begin'], 18);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('mtchEnd', $infos['mtch_end'], 18);
		$kedit->noMandatory();

		$elts = array('mtchBegin', 'mtchEnd');
		$form->addBlock('blkTime', $elts);

		$elts = array('img0', 'btnTeam0', 'btnRaz',
		    'btnTeam1', 'img1');
		$form->addBlock('blkRegister', $elts);

		$eltscore[] = 'blkExtra';
		$eltscore[] = 'blkTime';
		$eltscore[] = 'blkRegister';
		$form->addBlock('blkScore', $eltscore);

		$elts = array('btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();

		exit;
	}

}

?>
