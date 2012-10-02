<?php
/*****************************************************************************
 !   $Id$
******************************************************************************/
require_once 'Badnetteam/Classe/Cevent.php';
require_once 'Badnetteam/Classe/Cmatch.php';
require_once 'Badnetteam/Classe/Ctie.php';
require_once 'Badnetteam/Classe/Cteam.php';
require_once 'Badnetteam/Classe/Cplayer.php';
require_once 'Tevent.inc';
require_once 'Badnetteam/Ttransfert/Ttransfert.inc';
require_once 'Object/Omatch.inc';
require_once 'Object/Opair.inc';
require_once 'Object/Omember.inc';
require_once 'Object/Oplayer.inc';
require_once 'Object/Oeventextra.inc';

/**
 * Module de gestion des tournois
 */
class Tevent
{
	// {{{ properties
	public function __construct()
	{
		$controller = Bn::getController();
		$controller->addAction(TEVENT_PAGE_TIES,        $this, 'pageTies');
		$controller->addAction(TEVENT_FILL_TIES,        $this, 'fillTies');
		$controller->addAction(TEVENT_PAGE_TEAM,        $this, 'pageTeam');
		$controller->addAction(TEVENT_UPDATE_TEAM,      $this, 'updateTeam');
		$controller->addAction(TEVENT_PAGE_CHECK,       $this, 'pageCheck');
		$controller->addAction(TEVENT_FILL_PLAYERS,     $this, 'fillPlayers');
		$controller->addAction(TEVENT_CHECK_PLAYERS,    $this, 'checkPlayers');
		$controller->addAction(TEVENT_PAGE_PLAYER,      $this, 'pagePlayer');
		$controller->addAction(TEVENT_ADD_PLAYER,       $this, 'addPlayer');
		$controller->addAction(TEVENT_PAGE_SCORE,       $this, 'pageScore');
		$controller->addAction(TEVENT_UPDATE_SCORE,     $this, 'updateScore');

		$controller->addAction(TEVENT_PDF_SCORESHEET,   $this, 'pdfScoresheet');
		$controller->addAction(TEVENT_PDF_TIE,          $this, 'pdfTie');
		$controller->addAction(TEVENT_PDF_TEAM,         $this, 'pdfTeam');
		$controller->addAction(TEVENT_PDF_CHECK,        $this, 'pdfCheck');
		$controller->addAction(TEVENT_PDF_ALLCHECK,     $this, 'pdfAllCheck');
		$controller->addAction(TEVENT_PDF_TIEALL,       $this, 'pdfTieAll');

		$controller->addAction(TEVENT_PROPOSE_ORDER,    $this, 'proposeOrder');
		$controller->addAction(TEVENT_PAGE_PARAM,       $this, 'pageParam');
		$controller->addAction(TEVENT_UPDATE_PARAM,     $this, 'updateparam');
		$controller->addAction(TEVENT_START_MATCH,      $this, 'startMatch');
		$controller->addAction(TEVENT_STOP_MATCH,       $this, 'stopMatch');
		$controller->addAction(TEVENT_FILL_TIE,         $this, 'fillTie');
		$controller->addAction(TEVENT_PAGE_TIE,         $this, 'pageTie');
		$controller->addAction(TEVENT_UPDATE_TIE,       $this, 'updateTie');
		$controller->addAction(TEVENT_FILL_MATCHS,      $this, 'fillMatchs');
		$controller->addAction(TEVENT_MATCH_UPDOWN,      $this, 'matchUpdown');
	}

	public function matchUpdown()
	{
		$matchTopId = Bn::getValue('matchTopId');
		$cMatchTop = new Cmatch($matchTopId);
		$matchBottomId = Bn::getValue('matchBottomId');
		$cMatchBottom = new Cmatch($matchBottomId);

		$rank = $cMatchTop->getVal('rank');
        $cMatchTop->setVal('rank', $cMatchBottom->getVal('rank'));
        $cMatchBottom->setVal('rank', $rank);
        $cMatchTop->save();
        $cMatchBottom->save();
        return false;
	}

	public function updateTie()
	{
		$tieId = Bn::getValue('tieId');
		$cTie = new Ctie($tieId);
		$cTie->setVal('teamhid', Bn::getvalue('teamhid'));
		$cTie->setVal('teamvid', Bn::getvalue('teamvid'));
		$schedule = substr($cTie->getVal('schedule'), 0, 11) .  Bn::getvalue('schedule');
		$cTie->setVal('schedule', $schedule);
		$cTie->save();

		$res = array('bnAction'  => TEVENT_PAGE_TIES,
					 'ajax' => 1);
		echo Bn::toJson($res);
		return false;
	}

	/**
	* Page de modification d'une rencontre
	*
	*/
	public function pageTie()
	{
		// Les donnees
		$tieId = Bn::getValue('tieId');
		$cTie = new Ctie($tieId);
		$numId = $cTie->getVal('numid');

		//
		$body = new Body();
		$t = $body->addP('', LOC_TITLE_JOURNEY, 'bn-title-1');
		$t->addBalise('span', '', 'Modification de rencontre');

		$str = $cTie->getVal('teamnameh') . ' - ' . $cTie->getVal('teamnamev');
		$body->addTitle('', $str, 3);
		$form = $body->addForm('frmTie', TEVENT_UPDATE_TIE, 'targetBody');
		$form->getForm()->addMetadata('success', "submitTie");
		$form->getForm()->addMetadata('dataType', "'json'");
		$form->addHidden('tieId', $tieId);

		$lov = Cteam::getlov();
		if ($numId < 0)
		{
			$slct = $form->addSelect('teamhid', 'Equipe hote');
			$slct->addOptions($lov, $cTie->getVal('teamhid'));
			$slct = $form->addSelect('teamvid', 'Equipe visiteur');
			$slct->addOptions($lov, $cTie->getVal('teamvid'));
		}
		else
		{
			$form->addHidden('teamhid', $cTie->getVal('teamhid'));
			$form->addHidden('teamvid', $cTie->getVal('teamvid'));
		}
		$form->addEdit('schedule', 'Heure', Bn::date($cTie->getVal('schedule'), 'H:i'));

		// Boutons
		$d = $form->addDiv('', 'bn-div-btn');
		$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$d->addButtonValid('btnValid', LOC_BTN_UPDATE);

		// Envoi au navigateur
		$body->display();
		return false;

	}

	/**
	 *
	 * Arret d'un match
	 */
	public function stopMatch()
	{
		$matchId = Bn::getValue('matchId');
		$oMatch = new Cmatch($matchId);

		if ($oMatch->getVal('status') == OMATCH_STATUS_LIVE)
		{
			$oMatch->setVal('court', 0);
			$oMatch->setVal('status', OMATCH_STATUS_READY);
			$oMatch->setVal('begin', 'OOOO-00-00 00:00:00');
			$oMatch->save();

			$res[] = array('matchId' => $matchId,
				'status'=>OMATCH_STATUS_READY,
				'begin' => '0000-00-00 00:00:00');

			// Matches en cours
			$matchIds = Cmatch::getLiveIds();

			// Mise a jour des joueurs
			$q = new Bn_query('player', '_team');
			$q->setValue('play_court', 0);
			$q->updateRow();
			foreach($matchIds as $matchId)
			{
				$cMatch = new Cmatch($matchId);
				$where = 'play_id IN (' . $cMatch->getVal('playh1id');
				$tmp = $cMatch->getVal('playh2id');
				if (!empty($tmp)) $where .= ',' . $tmp;
				$where .= ',' . $cMatch->getVal('playv1id');
				$tmp = $cMatch->getVal('playv2id');
				if (!empty($tmp)) $where .= ',' . $match['mtch_playv2id'];
				$where .= ')';
				$q->setValue('play_court', $cMatch->getVal('court'));
				$q->setWhere($where);
				$q->updateRow();
				unset($cMatch);
			}
		}
		unset ($oMatch);

		echo Bn::toJson($res);
		return;
	}

	/**
	 *
	 * Demarrage d'un match
	 */
	public function startMatch()
	{
		$liveMatchId = Bn::getValue('liveMatchId');
		$matchId = Bn::getValue('matchId');
		$court = Bn::getValue('numcourt');

		// Terminer le match precedent
		$q = new Bn_query('match', '_team');
		if ($liveMatchId > 0)
		{
			$q->setFields('mtch_begin');
			$q->addWhere('mtch_id=' . $liveMatchId);
			$begin = $q->getFirst();
			$start = Bn::date($begin, 'U');
			$now = time();
			if ($now > $start + 5*60)
			{
				$q->setValue('mtch_end', date ('Y-m-d H:i:s'));
				$q->addValue('mtch_status', OMATCH_STATUS_ENDED);
				$res[] = array('matchId' => $liveMatchId,
					'status' => OMATCH_STATUS_ENDED,
					'begin' => $begin);
			}
			else
			{
				$q->setValue('mtch_begin', date ('0000-00-00 00:00:00'));
				$q->addValue('mtch_status', OMATCH_STATUS_READY);
				$res[] = array('matchId' => $liveMatchId,
					'status' => OMATCH_STATUS_READY,
					'begin' => '0000-00-00 00:00:00');
			}
			$q->updateRow();
		}

		// lancer le match
		$q->setWhere('mtch_id=' . $matchId);
		$q->setFields('mtch_status, mtch_begin');
		$match = $q->getRow();
		$q->setValue('mtch_court', $court);
		$q->addValue('mtch_status', OMATCH_STATUS_LIVE);
		if ($match['mtch_status'] < OMATCH_STATUS_LIVE)
		{
			$begin = date ('Y-m-d H:i:s');
		 	$q->addValue('mtch_begin', $begin);
		 }
		 else $begin = $match['mtch_begin'];
		$q->updateRow();
		$res = array('matchId' => $matchId,
			'status' => OMATCH_STATUS_LIVE,
			'begin'  => $begin);

		// Matches en cours
		$q->setFields('mtch_court, mtch_playh1id, mtch_playh2id, mtch_playv1id, mtch_playv2id');
		$q->setWhere('mtch_status=' . OMATCH_STATUS_LIVE);
		$matchs = $q->getRows();

		// Mise a jour des joueurs
		$q->setTables('player');
		$q->setValue('play_court', 0);
		$q->updateRow();
		foreach($matchs as $match)
		{
			$where = 'play_id IN (' . $match['mtch_playh1id'];
			if (!empty($match['mtch_playh2id'])) $where .= ',' . $match['mtch_playh2id'];
			$where .= ',' . $match['mtch_playv1id'];
			if (!empty($match['mtch_playv2id'])) $where .= ',' . $match['mtch_playv2id'];
			$where .= ')';
			$q->setValue('play_court', $match['mtch_court']);
			$q->setWhere($where);
			$q->updateRow();
		}

		echo Bn::toJson($res);
		return;
	}


	/**
	 * Enregistre les parametres
	 *
	 */
	public function updateParam()
	{
		$cEvent = new Cevent();
		$cEvent->setVal('rest', Bn::getValue('rest'));
		$cEvent->setVal('nbcourt', Bn::getValue('nbcourt'));
		$cEvent->setVal('nbmatchmax', Bn::getValue('nbmatchmax'));
		$cEvent->setVal('warming', Bn::getValue('warming'));
		$cEvent->save();
		Bn::setValue('autoarbitrage', Bn::getValue('auto', NO));
		$res = array('bnAction'  => TEVENT_PAGE_TIES,
					 'ajax' => 1);
		echo Bn::toJson($res);
		return false;
	}

	/**
	 * Page pour la saisie des paramtres
	 *
	 */
	public function pageParam()
	{
		// Les donnees
		$cEvent = new Cevent();
		//
		$body = new Body();

		$form = $body->addForm('frmParam', TEVENT_UPDATE_PARAM, 'targetBody');
		$form->getForm()->addMetadata('success', "submitParam");
		$form->getForm()->addMetadata('dataType', "'json'");
		$form->addEdit('warming', LOC_LABEL_WARMING, $cEvent->getVal('warming', 5));
		$form->addEdit('rest', LOC_LABEL_REST, $cEvent->getVal('rest'));
		$form->addEdit('nbcourt', LOC_LABEL_NBCOURT, $cEvent->getVal('nbcourt'));
		$form->addEdit('nbmatchmax', LOC_LABEL_NBMATCHMAX, $cEvent->getVal('nbmatchmax', 2));
		$form->addCheckbox('auto', 'Auto-arbitrage', YES, Bn::getValue('autoarbitrage', YES)==YES);

		// Boutons
		$d = $form->addDiv('', 'bn-div-btn');
		$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$d->addButtonValid('btnValid', LOC_BTN_UPDATE);

		// Envoi au navigateur
		$body->display();
		return false;
	}

	/**
	 * Calcul d'un ordre de match
	 *
	 * @param unknown_type $aTieId
	 * @return unknown
	 */
	function proposeOrder()
	{
		$tieId = Bn::getValue('tieId', $aTieId);
		$cTie = new Ctie($tieId);
		$matchIds = $cTie->getMatchIds();

		// Pour chaque match, trouver les match liés
		foreach($matchIds as $matchId)
		{
			// Joueurs du match
			$cMatch = new Cmatch($matchId);
			$playerIds = $cMatch->getPlayers();
			$tmps = array();
			foreach($playerIds as $playerId)
			{
				// Matches du jouer
				$cPlayer = new Cplayer($playerId);
				$matchpIds = $cPlayer->getMatchIds($tieId);
				$tmps = array_merge($tmps, $matchpIds);
				unset($cPlayer);
			}
			unset($cMatch);
			$graphs[$matchId] = array_unique($tmps);
		}

		// Table des matchs non disponible
		$notAvailable = array();
		// Table des matchs ordonnes
		$matchOrder = array();
		$matchs = array_combine($matchIds, $matchIds);
		while(count($matchs))
		{
			// Match retenu
			$matchSel = -1;
			// Nombre de dependance max
			$max = -1;
			// Saut dans la table des match non disponible
			$step = 0;
			// Recherche du premier match jouable
			while($matchSel == -1)
			{
				// Boucle sur les match restant
				foreach($matchs as $matchId)
				{
					// Le nombre de match dependant est plus grand
					if (count($graphs[$matchId])>$max )
					{
						// Le match est-il disponible
						$matchDispo = true;
						foreach($notAvailable as $not)
						{
							if(in_array($matchId, $not)) $matchDispo = false;
						}
						if($matchDispo)
						{
							$max = count($graphs[$matchId]);
							$matchSel = $matchId;
						}
					}
				}
				if ($matchSel == -1)
				array_shift($notAvailable);
			}
			// Memoriser l'ordre du match
			$matchOrder[] = $matchSel;
			$notAvailable[] = $graphs[$matchSel];
			unset($graphs[$matchSel]);
			unset($matchs[$matchSel]);
			// Suprimer les match des listes
			foreach ($graphs as $matchId=>$graph)
			{
				if ( $key = array_search($matchSel, $graph))
				unset($graphs[$matchId][$key]);
			}
		}

		// Enregistrer dans les matchs
		$i = 1;
		foreach($matchOrder as $matchId)
		{
			$cMatch = new Cmatch($matchId);
			$cMatch->setVal('rank', $i++);
			$cMatch->save();
			unset($cMatch);
		}
		echo 'done';
		return false;
	}

	/**
	 * Pdf de la feuille de composition
	 *
	 */
	public function pdfTeam()
	{
		// Donnees
		$cTie = new cTie(Bn::getValue('tieId'));
		$cTie->pdfTeam();
		return false;
	}

	/**
	 * Pdf de la feuille de presence de toutes les equipes
	 *
	 */
	public function pdfAllCheck()
	{
		// Creation du document
		include_once 'Bn/Bnpdf.php';
		$pdf = new Bnpdf();
		$pdf->setAutoPageBreak(false);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);
		$pdf->start();

		// Donnees
		$teamIds = Cteam::getTeams();
		foreach($teamIds as $teamId)
		{
			$cTeam = new Cteam($teamId);
			$cTeam->pdfCheck($pdf);
			unset($cTeam);
		}
		// Impression
		$filename = '../Temp/Pdf/check_' . $teamId . '.pdf';
		$pdf->end($filename);

		return false;
	}

	/**
	 * Pdf de la feuille de presence
	 *
	 */
	public function pdfCheck()
	{
		// Creation du document
		include_once 'Bn/Bnpdf.php';
		$pdf = new Bnpdf();
		$pdf->setAutoPageBreak(false);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);
		$pdf->start();

		// Donnees
        $cTie = new Ctie(Bn::getValue('tieId'));

		$teamId = $cTie->getVal('teamhid');
		$cTeamh = new Cteam($teamId);
		$cTeamh->pdfCheck($pdf);

		$teamId = $cTie->getVal('teamvid');
		$cTeamv = new Cteam($teamId);
		$cTeamv->pdfCheck($pdf);

		// Impression
		$filename = '../Temp/Pdf/check_' . $teamId . '.pdf';
		$pdf->end($filename);

		return false;
	}

	/**
	 * Pdf de tous les documents d'une recontres
	 *
	 */
	public function pdfTie()
	{
		$cTie = new Ctie(Bn::getValue('tieId'));
		$cTie->pdfTie();
		return false;
	}


	/**
	 * Pdf de la feuille de rencontre
	 *
	 */
	public function pdfTieAll()
	{
		// Donnees
		$tieId = Bn::getValue('tieId');
		$cTie = new Ctie($tieId);

		// Creation du document
		include_once 'Bn/Bnpdf.php';
		$pdf = new Bnpdf();
		$pdf->setAutoPageBreak(true);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->start();

		// Feuilles de présence
		$teamId = $cTie->getVal('teamhid');
		$cTeamh = new Cteam($teamId);
		$cTeamh->pdfCheck($pdf);
		$teamId = $cTie->getVal('teamvid');
		$cTeamv = new Cteam($teamId);
		$cTeamv->pdfCheck($pdf);

		// Feuille de composition
		$cTie->_pdfTeam($pdf, $cTeamh);
		$cTie->_pdfTeam($pdf, $cTeamv);

		// Rencontre
		$cTie->pdfTie($pdf);

		// Feuille de match
		$auto = Bn::getValue('autoarbitrage', YES);
		if ($auto==YES)
		{
			$pdf->AddPage('P');
		}
		else
		{
			$pdf->setAutoPageBreak(false);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(true);
		}

		$matchIds = $cTie->getMatchIds();
		foreach($matchIds as $matchId)
		{
			$cMatch = new Cmatch($matchId);
			if ($auto==YES) $cMatch->pdfScoresheetLite($pdf);
			else $cMatch->pdfScoresheet($pdf);
			unset($cMatch);
		}

		// Fin du document
		$filename = '../Temp/Pdf/alltie_' . $tieId . '.pdf';
		$pdf->end($filename);
		return false;
	}

	/**
	 * Pdf des feuille de score
	 *
	 */
	public function pdfScoresheet()
	{

		// Creation du document
		include_once 'Bn/Bnpdf.php';
		$pdf = new Bnpdf();
		$auto = Bn::getValue('autoarbitrage', YES);
		if ($auto==YES)
		{
			$pdf->setAutoPageBreak(true);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->start();
			$pdf->AddPage();
		}
		else
		{
			$pdf->setAutoPageBreak(false);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(true);
			$pdf->start('L');
		}

		// Donnees des matchs
		$tieId = Bn::getValue('tieId');
		if (!empty($tieId))
		{
			$cTie = new Ctie($tieId);
			$matchIds = $cTie->getMatchIds();
		}
		else $matchIds[] = Bn::getValue('matchId');
		foreach($matchIds as $matchId)
		{
			$cMatch = new Cmatch($matchId);
			if ($auto==YES) $cMatch->pdfScoresheetLite($pdf);
			else $cMatch->pdfScoresheet($pdf);
			unset($cMatch);
		}

		// Fin du document
		$filename = '../Temp/Pdf/scoresheet_' . $tieId . '.pdf';
		$pdf->end($filename);

		return false;
	}

	/**
	 * Mise a jour du score d'un match
	 *
	 */
	public function updateScore()
	{
		$cEvent = new Cevent();

		// Mise a jour du match
		$matchId = Bn::getValue('matchId');
		$cMatch = new Cmatch($matchId);
		$status = $cMatch->getVal('status');

		$q = new Bn_query('match', '_team');
		$begin = Bn::getValue('begin');
		list($date, $time) = explode(' ', $begin);
		list($d, $m, $y) = explode('-', $date);
		$begin = "$y-$m-$d $time";
		$q->addValue('mtch_begin', $begin);

		$end = Bn::getValue('end');
		list($date, $time) = explode(' ', $end);
		list($d, $m, $y) = explode('-', $date);
		$end = "$y-$m-$d $time";
		$q->addValue('mtch_end', $end);

		$scosys = 'Oscore_' . $cEvent->getVal('scoringsystem');
		$oScore = new $scosys();
		$score = $oScore->getValue($matchId);
		$q->addValue('mtch_score', $score);
		$q->addValue('mtch_status', OMATCH_STATUS_CLOSED);
		if ($oScore->isWinner())
		{
			if ($oScore->isAbort())
			{
				$q->addValue('mtch_resulth', OPAIR_RES_WINAB);
				$q->addValue('mtch_resultv', OPAIR_RES_LOOSEAB);
			}
			else if ($oScore->isWo())
			{
				$q->addValue('mtch_resulth', OPAIR_RES_WINWO);
				$q->addValue('mtch_resultv', OPAIR_RES_LOOSEWO);
			}
			else
			{
				$q->addValue('mtch_resulth', OPAIR_RES_WIN);
				$q->addValue('mtch_resultv', OPAIR_RES_LOOSE);
			}
		}
		else
		{
			if ($oScore->isAbort())
			{
				$q->addValue('mtch_resulth', OPAIR_RES_LOOSEAB);
				$q->addValue('mtch_resultv', OPAIR_RES_WINAB);
			}
			else if ($oScore->isWo())
			{
				$q->addValue('mtch_resulth', OPAIR_RES_LOOSEWO);
				$q->addValue('mtch_resultv', OPAIR_RES_WINWO);
			}
			else
			{
				$q->addValue('mtch_resulth', OPAIR_RES_LOOSE);
				$q->addValue('mtch_resultv', OPAIR_RES_WIN);
			}
		}
		$q->addWhere('mtch_id=' . Bn::getValue('matchId'));
		$q->updateRow();

		$q->setFields('mtch_playh1id, mtch_playh2id, mtch_playv1id, mtch_playv2id');
		$match = $q->getRow();

		// Mise a jour des joueurs
		$playerIds = $cMatch->getPlayers();
		$delay = $cEvent->getVal('rest')*60;
		$rest = Bn::date($end, 'U') + $delay;
		foreach($playerIds as $playerId)
		{
			$cPlayer = new Cplayer($playerId);
			$cPlayer->setVal('court', 0);
			$cPlayer->setVal('rest', date('Y-m-d H:i', $rest));
			$cPlayer->save();
			unset($cPlayer);
		}

		// Mise a jour du score de la rencontre
		$cTie = new Ctie($cMatch->getVal('tieid'));
		$cTie->updateScore();
		$str = array('numCol'=> $cTie->getVal('pbl'),
					'tieId'=>$cMatch->getVal('tieid'),
					'status'=>$status,
					'numCourt'=>$cMatch->getVal('court')
					);
		echo Bn::tojson($str);
		return false;
	}

	/**
	 * Affichage de la saisie d'un score
	 *
	 * @return unknown
	 */
	public function pageScore()
	{
		$cEvent = new Cevent();

		// Donnees du match
		$matchId = Bn::getValue('matchId');
		$cMatch = new Cmatch($matchId);

		$body = new Body();
		$form = $body->addForm('frmPlayer', TEVENT_UPDATE_SCORE, 'targetBody', 'submitScore');
		$form->addHidden('matchId', $matchId);
		$form->addHidden('tieId', $cMatch->getVal('tieid'));
		$dMatch = $form->addDiv('', 'dMatch');

		// Afficher les joueurs de l'equipe hote
		//$dHote = $dMatch->addDiv('', 'dmPlayerH');
		//$dHote->addP('titleh', $cMatch->getVal('teamh'));
		$str[] = $cMatch->getVal('famh1') . ' ' . $cMatch->getVal('firsth1');

		// Afficher les joueurs de l'equipe visiteur
		//$dVisit = $dMatch->addDiv('', 'dmPlayerV');
		//$dVisit->addP('titlev', $cMatch->getVal('teamv'));
		$str[] = $cMatch->getVal('famv1') . ' ' . $cMatch->getVal('firstv1');

		if ( $cMatch->isDouble() )
		{
			$str[] = $cMatch->getVal('famh2') . ' ' . $cMatch->getVal('firsth2');
			$str[] = $cMatch->getVal('famv2') . ' ' . $cMatch->getVal('firstv2');
		}

		// Afficher le score
		$scosys = 'Oscore_' . $cEvent->getVal('scoringsystem');
		$oScore = new $scosys();
		$oScore->display($dMatch, $matchId, $cMatch->getVal('score'), $cMatch->getVal('resulth') != OPAIR_RES_WINAB, $str);

		// Saisie des heures
		$form->addBreak();
		$date = Bn::date($cMatch->getVal('begin'), 'd-m-Y H:i');
		if ( empty($date) ) $date = date('d-m-Y H:i');
		$form->addEdit('begin', LOC_LABEL_START, $date, 20);
		$date = Bn::date($cMatch->getVal('end'), 'd-m-Y H:i');
		if ( empty($date) ) $date = date('d-m-Y H:i');
		$form->addEdit('end', LOC_LABEL_END, $date, 20);

		// Boutons
		$d = $form->addDiv('', 'bn-div-btn');
		$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$d->addButtonValid('btnValid', LOC_BTN_UPDATE);

		// Envoi au navigateur
		$body->addJQready('pageScore();');
		$body->display();
		return false;
	}

	/**
	 * Enregistrement d'un joueur
	 */
	public function addPlayer()
	{
		// Ajout du joueur
		$q = new Bn_query('player', '_team');
		$q->addValue('play_license', Bn::getValue('license'));
		$q->addValue('play_famname', strtoupper(Bn::getValue('famname')));
		$q->addValue('play_firstname',  ucfirst(strtolower(Bn::getValue('firstname'))));
		$q->addValue('play_gender',     Bn::getValue('gender', OMEMBER_GENDER_MALE));
		$q->addValue('play_license',    Bn::getValue('license'));
		$q->addValue('play_catage',     Bn::getValue('catage'));
		$q->addValue('play_numcatage',  Bn::getValue('numcatage'));
		$q->addValue('play_surclasse',  Bn::getValue('surclasse'));
		$q->addValue('play_mute', NO);
		$q->addValue('play_stranger', NO);
		$q->addValue('play_born',   Bn::getValue('born'));
		$q->addValue('play_teamid', Bn::getValue('teamId'));

		$tmp = Bn::getValue('levels');
		list($level, $range) = explode(';', $tmp);
		$q->addValue('play_levels', $level);
		$q->addValue('play_ranges', $range);

		$tmp = Bn::getValue('leveld');
		list($level, $range) = explode(';', $tmp);
		$q->addValue('play_leveld', $level);
		$q->addValue('play_ranged', $range);

		$tmp = Bn::getValue('levelm');
		list($level, $range) = explode(';', $tmp);
		$q->addValue('play_levelm', $level);
		$q->addValue('play_rangem', $range);

		$q->addValue('play_ranks', Bn::getValue('ranks'));
		$q->addValue('play_rankd', Bn::getValue('rankd'));
		$q->addValue('play_rankm', Bn::getValue('rankm'));
		$id = Bn::getValue('playerId', -1);
		if ($id> 0)
		{
			$q->addWhere('play_id = ' .$id);
			$q->replaceRow();
		}
		else
		{
			$q->addValue('play_ispresent', YES);
			$id = $q->addRow();
			$q->addValue('play_numid', -$id);
			$q->updateRow('play_id=' . $id);
        }
		if (Bn::getValue('newplayer', -1) != -1) return TEVENT_PAGE_PLAYER;
		else return TEVENT_PAGE_CHECK;
	}

	/**
	 * Formulaire de création d'un nouveau joueur
	 */
	public function pagePlayer()
	{
		$body = new Body();
		$playerId = Bn::getValue('playerId', -1);
		$oPlayer = new Cplayer($playerId);

		// Commandes generiques
		$div = $body->addDiv('', 'bn-menu-right');
		$p = $div->addP();
		$lnk = $p->addLink('lnkHome', TEVENT_PAGE_TIES, LOC_ITEM_MAIN_PAGE, 'targetBody');
		$lnk->addicon('arrowreturnthick-1-w');

		$t = $body->addP('', LOC_TITLE_JOURNEY, 'bn-title-1');
		$t->addBalise('span', '', LOC_TITLE_ADD_PLAYER);

		$form = $body->addForm('frmPlayer', TEVENT_ADD_PLAYER, 'targetBody');
		$form->addHidden('playerId', $playerId);

		// Preparer les champs de saisie
		$d = $form->addRichDiv('divCivil', 'bn-left-div');
		$t = $d->addP('', '', 'bn-title-3');
		$t->addBalise('span', '',  LOC_TITLE_CIVIL);

		$div = $d->addDiv('dGender');
		$radio = $div->addRadio('gender', 'genderM',   'Homme', OMEMBER_MALE, $oPlayer->getVal('gender') == OMEMBER_MALE);
		$radio = $div->addRadio('gender', 'genderF',   'Femme', OMEMBER_FEMALE, $oPlayer->getVal('gender') == OMEMBER_FEMALE);
		$edit = $d->addEdit('famname',   LOC_LABEL_FAMNAME, $oPlayer->getVal('famname'), 30);
		$d->addEdit('firstname', LOC_LABEL_FIRSTNAME,  $oPlayer->getVal('firstname'), 30);
		$edit = $d->addEditDate('born', LOC_LABEL_BORN, Bn::date($oPlayer->getVal('born'), 'd-m-Y'));
		$edit->noMandatory();
		$edit = $d->addEdit('license',    LOC_LABEL_LICENSE, $oPlayer->getVal('license'), 10);
		$edit->noMandatory();

		$d = $form->addRichDiv('divSport', 'bn-left-div');
		$t = $d->addP('', '', 'bn-title-3');
		$t->addBalise('span', '',  LOC_TITLE_SPORT);

		$q = new Bn_query('team', '_team');
		$q->setFields('team_id, team_name');
		$q->setOrder('team_name');
		$teams = $q->getLov();
		$lst = $d->addSelect('teamId',  LOC_LABEL_TEAM);
		$lst->addOptions($teams, Bn::getValue('teamId', $oPlayer->getVal('teamid')));

		$catage = Oplayer::getLovCatage();
		$lst = $d->addSelect('catage',  LOC_LABEL_CATAGE);
		$lst->addOptions($catage, $oPlayer->getVal('catage', OPLAYER_CATAGE_SEN));

		$numcatage = Array(1=>1, 2, 3, 4 ,5);
		$lst = $lst->addSelect('numcatage',  '');
		$lst->addOptions($numcatage, $oPlayer->getVal('numcatage', 1));

		$surclasse = Oplayer::getLovSurclasse();
		$lst = $d->addSelect('surclasse',  LOC_LABEL_SURCLASSE);
		$lst->addOptions($surclasse, $oPlayer->getVal('surclasse', reset($surclasse)));

		$ranks = Cevent::getLovRanking();
		$d2 = $d->addDiv('', 'bn-div-line');
		$lst = $d2->addSelect('levels',  LOC_LABEL_SINGLE);
		$val =  $oPlayer->getVal('levels') . ';' .  $oPlayer->getVal('ranges');
		$lst->addOptions($ranks, $val);
		//$lst = $d->addEdit('levels',    LOC_LABEL_SINGLE, '', 5);
		$edit = $d2->addEdit('ranks',    LOC_LABEL_RANK, $oPlayer->getVal('ranks'), 5);
		$edit->noMandatory();

		$d2 = $d->addDiv('', 'bn-div-line');
		$lst = $d2->addSelect('leveld',  LOC_LABEL_DOUBLE);
		$val =  $oPlayer->getVal('leveld') . ';' .  $oPlayer->getVal('ranged');
		$lst->addOptions($ranks, $val);
		//$lst = $d->addEdit('leveld',    LOC_LABEL_DOUBLE, '', 5);
		$edit = $d2->addEdit('rankd',    LOC_LABEL_RANK,  $oPlayer->getVal('rankd'), 5);
		$edit->noMandatory();

		$d2 = $d->addDiv('', 'bn-div-line');
		$lst = $d2->addSelect('levelm',  LOC_LABEL_MIXED);
		$val =  $oPlayer->getVal('levelm') . ';' .  $oPlayer->getVal('rangem');
		$lst->addOptions($ranks, $val);
		//$lst = $d->addEdit('levelm',    LOC_LABEL_MIXED, '', 5);
		$edit = $d2->addEdit('rankm',    LOC_LABEL_RANK,  $oPlayer->getVal('rankm'), 5);
		$edit->noMandatory();

		// Bouttons
		$d = $form->addDiv('', 'bn-div-btn');
		if ($playerId == -1) $d->addCheckbox('newplayer', LOC_LABEL_NEW_PLAYER, 1, true);
		$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL, TEVENT_PAGE_CHECK, 'targetBody');
		$d->addButtonValid('btnValid', LOC_BTN_UPDATE);

		// Envoi au navigateur
		$body->display();
		return false;
	}

	/**
	 * Pointe les joueurs presents
	 *
	 */
	public function checkPlayers()
	{
		// Joueurs selectionnées
		$values = $_POST;
		foreach($values as $key=>$value)
		{
			$stroke = explode('_', $key);
			if ($stroke[0] != 'jqg') continue;
			$players[] = $stroke[2];
		}
		if (!empty($players) )
		{
			$present = Bn::getValue('check');
			$q = new Bn_query('player', '_team');
			if ($present) $q->setValue('play_ispresent', YES);
			else $q->setValue('play_ispresent', NO);
			$q->addWhere('play_id IN (' . implode(',', $players) . ')');
			$q->updateRow();
		}
		return false;
	}

	/**
	 * Envoi de la liste des joueurs
	 *
	 */
	public function fillPlayers()
	{
		$q = new Bn_query('player', '_team');
		$q->setFields('play_id, play_gender, play_famname, play_firstname, play_license, play_catage, play_surclasse');
		$q->addField('play_levels');
		$q->leftJoin('team', 'play_teamid=team_id');
		$q->addField('team_name');
		$q->addField('play_leveld');
		$q->addField('play_levelm');
		$q->addField('play_ispresent');
		$q->addField('play_numcatage');
		$q->addField('play_numid');
		$q->addWhere("play_famname != 'WO'");

		// Critere de recherche genre
		$cgender = Bn::getValue('gender');
		$gender = explode(':', $cgender);
		if ( strlen($cgender) && count($gender) == 1)
		$q->addWhere("play_gender = " . reset($gender));

		// Critere de recherche nom, prenom, licence
		$str = trim(Bn::getValue('search'));
		if ( !empty($str) )
		{
			$where = "(play_famname LIKE '%" . $str ."%'";
			$where .= " OR play_firstname LIKE '%" . $str ."%'";
			$where .= " OR play_license LIKE '%" . $str ."%')";
			$q->addWhere($where);
		}

		// Critere de recherche club
		$teamId = Bn::getValue('teamId', Bn::getValue('firstTeamId', -1));
		if ( $teamId > 0 )
		{
			$q->addWhere("play_teamid = " . $teamId);
		}

		$players = $q->getGridRows(0, true);
		$param  = $q->getGridParam();
		$i = $param->first;
		$gvRows = new GvRows($q);
		foreach ($players as $player)
		{
			$row[0] = $i++;
			if ($player['play_gender'] == OMEMBER_GENDER_MALE) $row[1] = 'Homme';
			else if ($player['play_gender'] == OMEMBER_GENDER_FEMALE) $row[1] = 'Femme';
			else $row[1] = '';
			if ($player['play_ispresent'] == YES)
			{
				$b = new Bn_Balise();
				$file = 'v.png';
				$img = $b->addImage('', $file, '');
				$img->addContent($player['play_famname'] .' '. $player['play_firstname']);
				$row[2] = $b->toHtml();
				unset($b);
			}
			else $row[2] = $player['play_famname'] . ' ' . $player['play_firstname'];
			$row[3] = $player['play_license'];
			$row[4] = constant('SMA_LABEL_' . $player['play_catage']);
			if ($player['play_numcatage'] > 0) $row[4] .= ' ' . $player['play_numcatage'];
			$row[5] = constant('SMA_LABEL_' . $player['play_surclasse']);
			$row[6] = $player['play_levels'] . ','. $player['play_leveld'] . ',' . $player['play_levelm'];
			$row[7] = $player['team_name'];
			if ($player['play_numid'] < 0)
			{
				$bal= new Bn_balise();
				$lnk = $bal->addLink('', TEVENT_PAGE_PLAYER . '&playerId=' . $player['play_id'], null, 'targetBody');
				$lnk->addimage('', 'edit.png', 'edit');
				$row[8] = $bal->toHtml();
				unset($bal);
			}
			else $row[8] = '';

			$gvRows->addRow($row, $player['play_id']);
		}
		$gvRows->display();
		return false;

	}

	/**
	 * Page pour le pointage des joueurs
	 */
	public function pageCheck()
	{
		$body = new Body();

		// Commandes generiques
		$div = $body->addDiv('', 'bn-menu-right');
		$p = $div->addP();
		$lnk = $p->addLink('lnkHome', TEVENT_PAGE_TIES, LOC_ITEM_MAIN_PAGE, 'targetBody');
		$lnk->addicon('arrowreturnthick-1-w');

		$t = $body->addP('', LOC_TITLE_JOURNEY, 'bn-title-1');
		$t->addBalise('span', '', LOC_TITLE_CHECK_PLAYERS);

		$body->addP('', LOC_P_CHECK, 'bn-p-info');

		$form = $body->addForm('frmPlayers', TEVENT_CHECK_PLAYERS, 'targetBody');
		$form->addHidden('check', true);

		$d = $form->addDiv('', 'bn-div-criteria bn-div-line bn-div-auto');
		$d1 = $d->addDiv('divGender');
		$d1->addCheckbox('chk'.OMEMBER_GENDER_MALE, 'Homme', OMEMBER_GENDER_MALE, true);
		$d1->addCheckbox('chk'.OMEMBER_GENDER_FEMALE, 'Femme', OMEMBER_GENDER_FEMALE, true);

		$edit = $d->addEdit('search', LOC_LABEL_SEARCH, '', 20);
		$edit->noMandatory();

		$q = new Bn_query('team', '_team');
		$q->setFields('team_id, team_name');
		$q->setOrder('team_name');
		$teams = $q->getLov();
		$teams[-1] = '-----';
		$slt = $d->addSelect('teamId', LOC_LABEL_ASSO);
		$slt->addOptions($teams, Bn::getValue('teamId', -1));

		$btn = $d->addButton('btnFilter', LOC_BTN_FILTER, null, 'search');

		$d = $form->addDiv('', 'bn-div-line bn-div-auto');
		$btn = $d->addButton('btnCheck', LOC_BTN_PRESENT, TEVENT_CHECK_PLAYERS, 'check');
		$btn = $d->addButton('btnUncheck', LOC_BTN_ABSENT, TEVENT_CHECK_PLAYERS, 'close');

		$cEvent = new Cevent();
		if($cEvent->getVal('allowaddplayer') == OEVENTEXTRA_DELAY_ALWAYS)
		{
			$btn = $d->addButton('btnNew', LOC_LINK_PAGE_PLAYER, TEVENT_PAGE_PLAYER, 'plus', 'targetBody');
			$btn->addMetaData('teamId', Bn::getValue('teamId', -1));
		}

		$lgd = $d->adddiv('', 'bn-right-div bn-lgd');
		$p = $lgd->addP('');
		$p->addImage('', 'v.png', LOC_LGD_PRESENT);
		$p->addBalise('span', '', LOC_LGD_PRESENT);

		// Liste des joueurs
		$url = TEVENT_FILL_PLAYERS . '&firstTeamId=' . Bn::getValue('teamId', -1);
		$grid = $form->addGridview('gridPlayers', $url, 50);
		$col = $grid->addColumn('#',   '#', false);
		$col->setLook(25, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_GENDER, 'gender');
		$col->setLook(55, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_FAMNAME,  'famname');
		$col->initSort();
		$col->setLook(160, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_LICENSE,  'license');
		$col->setLook(80, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_CATAGE,  'catage');
		$col->setLook(80, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_SURCLASSE,  'surclasse');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_LEVEL,  'level');
		$col->setLook(70, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_TEAM,  'team');
		$col->setLook(200, 'left', false);
		$col = $grid->addColumn('Action',  'act');
		$col->setLook(40, 'center', false);
		$str = LOC_TITLE_PLAYERS;
		$grid->setLook($str, 0, "'auto'");
		$grid->addOption('multiselect', "'true'");

		$body->addJQReady('pageCheck();');
		$body->display();
		return false;

	}

	/**
	 * Enregistrement de la composition d'une equipe
	 *
	 */
	public function updateTeam()
	{
		if (Bn::getValue('check', 0)) return $this->_checkTeamCompo();
		$teamId = Bn::getValue('teamId');
		$tieId = Bn::getValue('tieId');

		// Rencontre
		$cTie = new Ctie($tieId);
		if( $cTie->getVal('teamhid') == $teamId )
		{
			$field1 = 'playh1id';
			$field2 = 'playh2id';
			$fieldresl = 'resulth';
			$fieldresw = 'resultv';
		}
		else
		{
			$field1 = 'playv1id';
			$field2 = 'playv2id';
			$fieldresl = 'resultv';
			$fieldresw = 'resulth';
		}

		// Matchs de la rencontre
		$matchIds  = $cTie->getMatchIds(array('disci', 'order'));
		foreach($matchIds as $matchId)
		{
			$cPlayer1 = new Cplayer(Bn::getValue('play1id_'.$matchId, -1));
			$cPlayer2 = new Cplayer(Bn::getValue('play2id_'.$matchId, -1));
			$cMatch = new Cmatch($matchId);
			$cMatch->setVal($field1, $cPlayer1->getVal('id'));
			$cMatch->setVal($field2, $cPlayer2->getVal('id'));
			if ($cPlayer1->getVal('famname') == 'WO' || $cPlayer2->getVal('famname') == 'WO' )
			{
				$cMatch->setVal('status', OMATCH_STATUS_CLOSED);
				$cMatch->setVal($fieldresw, OPAIR_RES_WINWO);
				$cMatch->setVal($fieldresl, OPAIR_RES_LOOSEWO);
			}
			else
			{
				$cMatch->setVal('status', OMATCH_STATUS_READY);
			}
			$cMatch->save();
			unset($cPlayer1);
			unset($cPlayer2);
			unset($cMatch);
		}

		// Mise a jour des scores des equipe pour prendre en compte les eventuels WO
		$cTie->updateScore();

		$res = array('tieId'=> $tieId,
		    'numCol' => $cTie->getVal('pbl'));
		echo Bn::tojson($res);
		return false;
	}

	private function _checkTeamCompo()
	{
		$locale = Bn::GetLocale();
		require_once 'Badnetteam/Locale/' .$locale . '/Tevent.inc';

		$tieId = Bn::getValue('tieId');
		$cTie = new Ctie($tieId);
		$cEvent = new Cevent();

		// Matchs de la rencontre
		$matchIds  = $cTie->getMatchIds(array('disci', 'order'));
		$disci = 0;
		$range1 = 0;
		$range2 = 0;
		foreach($matchIds as $matchId)
		{
			$cPlayer1 = new Cplayer(Bn::getValue('play1id_'.$matchId, -1));
			$cPlayer2 = new Cplayer(Bn::getValue('play2id_'.$matchId, -1));
			$cMatch = new Cmatch($matchId);
			$discipline = $cMatch->getVal('discipline');

			if ( empty($playersMatch[$cPlayer1->getVal('id')]) ) $playersMatch[$cPlayer1->getVal('id')] = 1;
			else $playersMatch[$cPlayer1->getVal('id')]++;
			if ( empty($playersMatch[$cPlayer2->getVal('id')]) ) $playersMatch[$cPlayer2->getVal('id')] = 1;
			else $playersMatch[$cPlayer2->getVal('id')]++;

			if ( empty($playersDisci[$cPlayer1->getVal('id')][$discipline]) ) $playersDisci[$cPlayer1->getVal('id')][$discipline] = 1;
			else $playersDisci[$cPlayer1->getVal('id')][$discipline]++;
			if ( empty($playersDisci[$cPlayer2->getVal('id')][$discipline]) ) $playersDisci[$cPlayer2->getVal('id')][$discipline] = 1;
			else $playersDisci[$cPlayer2->getVal('id')][$discipline]++;

			if ($discipline == OMATCH_DISCIPLINE_SINGLE) $stroke = 'ranges';
			else if ($discipline == OMATCH_DISCIPLINE_DOUBLE) $stroke = 'ranged';
			else $stroke = 'rangem';

			$msg[$matchId] = "";
			if ($disci != $cMatch->getVal('disci') ) $disci = $cMatch->getVal('disci');
			else
			{
				if ($range1 + $range2 < $cPlayer1->getVal($stroke, 0) + $cPlayer2->getVal($stroke, 0))
				$msg[$matchId] = LOC_MSG_TOO_STRONG;
			}
			$range1 = $cPlayer1->getVal($stroke, 0);
			$range2 = $cPlayer2->getVal($stroke, 0);

			// Verification que les partenaires sont differents
			if ( $discipline == OMATCH_DISCIPLINE_DOUBLE )
			{
				if (Bn::getValue('play1id_'.$matchId, -1) == Bn::getValue('play2id_'.$matchId, -1))
				$msg[$matchId] = LOC_MSG_DIFFERENT_PLAYER;
			}

			unset($cPlayer1);
			unset($cPlayer2);
			unset($cMatch);
		}
		$nbMax = $cEvent->getVal('nbmatchmax', 2);
		foreach($playersMatch as $id => $nb)
		{
			if ($nb>$nbMax && $id>0)
			{
				$cPlayer = new Cplayer($id);
				$msg[$matchId] .= "<br>" . LOC_MSG_MORE_2 . ' ' . $cPlayer->getVal('famname') . ' ' . $cPlayer->getVal('firstname');
				unset($cPlayer);
			}
		}
		foreach($playersDisci as $id => $discis)
		{
			foreach($discis as $disci)
			{
				if ($disci > 1 && $id>0)
				{
					$cPlayer = new Cplayer($id);
					$msg[$matchId] .= "<br>" . LOC_MSG_MORE_DISCI . ' ' . $cPlayer->getVal('famname') . ' ' . $cPlayer->getVal('firstname');
					unset($cPlayer);
				}
			}
		}

		echo Bn::toJson($msg);
		return false;
	}

	/**
	 * Page de saisi des compositions d'equipes
	 *
	 */
	public function pageTeam()
	{
		$body = new Body();

		$tieId = Bn::getValue('tieId');
		$teamId = Bn::getValue('teamId');
		$cTie = new Ctie($tieId);
		$cTeam = new Cteam($teamId);
		$t = $body->addP('', $cTeam->getVal('name'), 'bn-title-1');

		$body->addP('', LOC_P_TEAM_FORM, 'bn-p-info');
		// Joueurs de l'equipes
		$q = new Bn_query('player', '_team');
		$q->setFields('play_id');
		$strs = array('play_levels', "' - '", 'play_famname', "' '", 'play_firstname', "' - '",  'play_points');
		$q->addField($q->getConcat($strs));
		$q->setWhere('play_teamid=' . $teamId);
		$q->addWhere('play_gender=' . OMEMBER_GENDER_MALE);
		$q->addWhere('(play_ispresent=' . YES . " OR play_famname = 'WO')");
		$q->setOrder('play_ranges DESC, play_points DESC');
		$playerhs = $q->getLov();

		$q->setFields('play_id');
		$strs = array('play_leveld', "' - '", 'play_famname', "' '", 'play_firstname', "' - '",  'play_pointd');
		$q->addField($q->getConcat($strs));
		$q->setWhere('play_teamid=' . $teamId);
		$q->addWhere('play_gender=' . OMEMBER_GENDER_MALE);
		$q->addWhere('(play_ispresent=' . YES . " OR play_famname = 'WO')");
		$q->setOrder('play_ranged DESC, play_pointd DESC');
		$playerhd = $q->getLov();

		$q->setFields('play_id');
		$strs = array('play_levelm', "' - '", 'play_famname', "' '", 'play_firstname', "' - '",  'play_pointm');
		$q->addField($q->getConcat($strs));
		$q->setWhere('play_teamid=' . $teamId);
		$q->addWhere('play_gender=' . OMEMBER_GENDER_MALE);
		$q->addWhere('(play_ispresent=' . YES . " OR play_famname = 'WO')");
		$q->setOrder('play_rangem DESC, play_pointm DESC');
		$playerhm = $q->getLov();

		$q->setFields('play_id');
		$strs = array('play_levels', "' - '", 'play_famname', "' '", 'play_firstname', "' - '",  'play_points');
		$q->addField($q->getConcat($strs));
		$q->setWhere('play_teamid=' . $teamId);
		$q->addWhere('play_gender=' . OMEMBER_GENDER_FEMALE);
		$q->addWhere('(play_ispresent=' . YES . " OR play_famname = 'WO')");
		$q->setOrder('play_ranges DESC, play_points DESC');
		$playerfs = $q->getLov();

		$q->setFields('play_id');
		$strs = array('play_leveld', "' - '", 'play_famname', "' '", 'play_firstname', "' - '",  'play_pointd');
		$q->addField($q->getConcat($strs));
		$q->setWhere('play_teamid=' . $teamId);
		$q->addWhere('play_gender=' . OMEMBER_GENDER_FEMALE);
		$q->addWhere('(play_ispresent=' . YES . " OR play_famname = 'WO')");
		$q->setOrder('play_ranged DESC, play_pointd DESC');
		$playerfd = $q->getLov();

		$q->setFields('play_id');
		$strs = array('play_levelm', "' - '", 'play_famname', "' '", 'play_firstname', "' - '",  'play_pointm');
		$q->addField($q->getConcat($strs));
		$q->setWhere('play_teamid=' . $teamId);
		$q->addWhere('play_gender=' . OMEMBER_GENDER_FEMALE);
		$q->addWhere('(play_ispresent=' . YES . " OR play_famname = 'WO')");
		$q->setOrder('play_rangem DESC, play_pointm DESC');
		$playerfm = $q->getLov();

		// Rencontre
		if( $cTie->getVal('teamhid') == $teamId)
		{
			$field1 = 'playh1id';
			$field2 = 'playh2id';
		}
		else
		{
			$field1 = 'playv1id';
			$field2 = 'playv2id';
		}

		// Matchs de la rencontre
		$body->completeAttribute('class', 'Event');
		$form = $body->addForm('frmPlayers', TEVENT_UPDATE_TEAM, 'targetBody', 'submitTeam');
		$form->addHidden('teamId', $teamId);
		$form->addHidden('tieId', $tieId);
		$matchIds = $cTie->getMatchIds(array('disci', 'order'));
		foreach($matchIds as $matchId)
		{
			$cMatch = new Cmatch($matchId);
			$div = $form->addDiv('', 'dMatch');
			$d = $div->addDiv('', 'dmDisci');
			$d->addP('', $cMatch->getVal('labdiscir'));

			$d = $div->addDiv('', 'dmPlayer');
			$d->addP('msg_'.$matchId, '', 'bn-error');
			switch($cMatch->getVal('disci'))
			{
				case OMATCH_DISCI_MS:
					$lst = $d->addSelect('play1id_' . $matchId);
					$lst->addOptions($playerhs, $cMatch->getVal($field1));
					break;
				case OMATCH_DISCI_WS:
					$lst = $d->addSelect('play1id_' . $matchId);
					$lst->addOptions($playerfs, $cMatch->getVal($field1));
					break;
				case OMATCH_DISCI_MD:
					$lst = $d->addSelect('play1id_' . $matchId);
					$lst->addOptions($playerhd, $cMatch->getVal($field1));
					$lst = $d->addSelect('play2id_' . $matchId);
					$lst->addOptions($playerhd, $cMatch->getVal($field2));
					break;
				case OMATCH_DISCI_WD:
					$lst = $d->addSelect('play1id_' . $matchId);
					$lst->addOptions($playerfd, $cMatch->getVal($field1));
					$lst = $d->addSelect('play2id_' . $matchId);
					$lst->addOptions($playerfd, $cMatch->getVal($field2));
					break;
				case OMATCH_DISCI_XD:
					$lst = $d->addSelect('play1id_' . $matchId);
					$lst->addOptions($playerhm, $cMatch->getVal($field1));
					$lst = $d->addSelect('play2id_' . $matchId);
					$lst->addOptions($playerfm, $cMatch->getVal($field2));
					break;
			}
			$div->addBreak();
			unset($cMatch);
		}
		$d = $form->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$d->addButtonValid('btnValid', LOC_BTN_VALID);
		$body->addJQready('pageTeam();');
		$body->display();
	}

	public function fillTie()
	{
		$tieId = Bn::getValue('tieId');
		$numCol = Bn::getValue('numCol');
		Ctie::freeCourt($numCol);
		if ($tieId == -1) return false;
		$oTie = new Ctie($tieId);
		$oTie->setVal('pbl', $numCol);
		$oTie->save();
		$body = new Body();

		// icone pour proposer un ordre
		$p = $body->addP('', '', 'bteam-tie-order ui-icon ui-icon-lightbulb');
		$p->setAction(TEVENT_PROPOSE_ORDER);
		$p->addMetaData('tieId', $tieId);
		$p->addMetaData('numCol', $numCol);
		$p->setAttribute('title', 'Ordre automatique des matches');

		// icone pour modifier la rencontre (les equipes)
		$p = $body->addP('', '', 'bteam-tie-edit ui-icon ui-icon-pencil');
		$p->setAction(TEVENT_PAGE_TIE);
		$p->addMetaData('tieId', $tieId);
		$p->addMetaData('width', 440);
		$p->addMetaData('height', 250);
		$p->addMetaData('title', "'Modification de la rencontre'");
		$p->addMetaData('target', "'targetDlg'");
		$p->completeAttribute('class', 'bn-dlg');
		$p->setAttribute('title', 'Modifier la rencontre');

		// icone pour impression document rencontre
		$p = $body->addP('', '', 'bteam-tie-print ui-icon ui-icon-print');
		$p->setAttribute('title', 'Imprimer les documents de la rencontre');
        $menu = $body->addDiv('printMenu', 'print-menu');
        $menu->setAttribute('style', 'display:none;');
        $lnk = $menu->addP('', 'Tous les documents', 'bteam-item-print');
		$lnk->setAction(TEVENT_PDF_TIEALL);
		$lnk->addMetaData('tieId', $tieId);
        $lnk = $menu->addP('', 'Présence', 'bteam-item-print');
		$lnk->setAction(TEVENT_PDF_CHECK);
		$lnk->addMetaData('tieId', $tieId);
        $lnk = $menu->addP('', 'Composition', 'bteam-item-print');
		$lnk->setAction(TEVENT_PDF_TEAM);
		$lnk->addMetaData('tieId', $tieId);
        $lnk = $menu->addP('', 'Rencontre', 'bteam-item-print');
		$lnk->setAction(TEVENT_PDF_TIE);
		$lnk->addMetaData('tieId', $tieId);
        $lnk = $menu->addP('', 'Feuilles de match', 'bteam-item-print');
		$lnk->setAction(TEVENT_PDF_SCORESHEET);
		$lnk->addMetaData('tieId', $tieId);

        /*
		$controller->addAction(TEVENT_PDF_SCORESHEET,   $this, 'pdfScoresheet');
		$controller->addAction(TEVENT_PDF_TIE,          $this, 'pdfTie');
		$controller->addAction(TEVENT_PDF_TEAM,         $this, 'pdfTeam');
		$controller->addAction(TEVENT_PDF_CHECK,        $this, 'pdfCheck');
		$controller->addAction(TEVENT_PDF_ALLCHECK,     $this, 'pdfAllCheck');
		$controller->addAction(TEVENT_PDF_TIEALL,       $this, 'pdfTieAll');
        */

		// Masquer/montrer les matchs terminés
		$p = $body->addP('', '', 'bteam-match-hide ui-icon ui-icon-shuffle');
		$body->addHidden('isHide-' . $numCol, 0);
		$p->setAttribute('title', 'Montrer/masquer les matchs terminés');

		// Heure de la rencontre
		$p = $body->addP('', Bn::date($oTie->getVal('schedule'), 'H:i'), 'bteam-tie-schedule');

		// Equipe hote
		$teamId = $oTie->getVal('teamhid', -1);
		$div = $body->addDiv('', 'bn-div-clear');

		// Composition de l'équipe
		$p = $div->addP('', '', 'bteam-team-compo ui-icon ui-icon-person');
		$p->addMetaData('tieId', $tieId);
		$p->addMetaData('teamId', $teamId);
		$p->addMetaData('title', "'Composition de l\'équipe'");
		$p->addMetaData('width', 450);
		$p->addMetaData('height', 450);
		$p->setAction(TEVENT_PAGE_TEAM);
		$p->setAttribute('title', 'Composition de l\'équipe');

		// Nom de l'équipe (lien)
		$url = TEVENT_PAGE_CHECK . '&teamId=' . $teamId;
		$str = $oTie->getVal('teamstamph');
		$p = $div->addp('', '', 'bteam-team-name');
		$p->addLink('lnk2_' . $teamId, $url, $str, 'targetBody');
		$p->setAttribute('title', $oTie->getVal('teamnameh'));

		// Points de l'equipe
		$div->addP('', $oTie->getVal('pointh'), 'bteam-team-score');

		// Equipe Visiteuse
		$teamId = $oTie->getVal('teamvid', -1);
		$div = $body->addDiv('', 'bn-div-clear');

		// Composition de l'équipe
		$p = $div->addP('', '', 'bteam-team-compo ui-icon ui-icon-person');
		$p->addMetaData('tieId', $tieId);
		$p->addMetaData('teamId', $teamId);
		$p->addMetaData('title', "'Composition de l\'équipe'");
		$p->addMetaData('width', 450);
		$p->addMetaData('height', 450);
		$p->setAction(TEVENT_PAGE_TEAM);
		$p->setAttribute('title', 'Composition de l\'équipe');

		// Nom de l'équipe (lien)
		$url = TEVENT_PAGE_CHECK . '&teamId=' . $teamId;
		$str = $oTie->getVal('teamstampv');
		$p = $div->addp('', '', 'bteam-team-name');
		$p->addLink('lnk2_' . $teamId, $url, $str, 'targetBody');
		$p->setAttribute('title', $oTie->getVal('teamnameh'));

		// Points de l'equipe
		$div->addP('', $oTie->getVal('pointv'), 'bteam-team-score');

		// Affichage
		$body->addJqready('fillTie('.$numCol.',' . $tieId . ');');
		$body->display();
		return false;
	}

	public function fillMatchs()
	{
		$tieId = Bn::getValue('tieId', -1);
		$numCol = Bn::getValue('numCol');
		if ($tieId == -1) return false;

		// Rencontre a traiter
		$oTie = new Ctie($tieId);
		$schedule = $oTie->getVal('schedule');

		// Afficher les matchs dans la colonne
		$body = new Body();
		$matchIds = $oTie->getMatchIds();
		foreach($matchIds as $matchId)
		{
			$cMatch = new Cmatch($matchId);

			// Cellule du match
			if($cMatch->getVal('status') == OMATCH_STATUS_LIVE) continue;
			else $cell = $body->addDiv('match-'.$matchId, 'bteam-match bteam-match-'.$cMatch->getVal('tieid') . ' bteam-status-' . $cMatch->getVal('status'));

			$cell->addMetaData('matchId', $matchId);
			$cell->addMetaData('tieId', $tieId);
			$cell->addMetaData('numCol', $oTie->getVal('pbl'));
			$cell->addHidden('start-'.$matchId, $cMatch->getVal('begin', '0000-00-00 00:00:00'));
			$cell->addHidden('status-'.$matchId, $cMatch->getVal('status'));

			// Numero de match
			$cell->addP('', 'N° ' . $cMatch->getVal('rank'), 'bteam-match-rank bteam-match-status-' . $cMatch->getVal('status'));

			// Type du match
			$cell->addP('', $cMatch->getVal('labdiscir'), 'bteam-match-disci'); //

			// icones pour le changement d'ordre
			$p = $cell->addP('', '', 'bteam-match-up ui-icon ui-icon-arrowthick-1-n');
			$p->setAttribute('title', 'Monter le match');
			$p->setAction(TEVENT_MATCH_UPDOWN);
			$p = $cell->addP('', '', 'bteam-match-down ui-icon ui-icon-arrowthick-1-s');
			$p->setAttribute('title', 'Descendre le match');
			$p->setAction(TEVENT_MATCH_UPDOWN);

			// icone pour saisie du score
			$p = $cell->addP('', '', 'bteam-match-edit ui-icon ui-icon-pencil');
			$p->setAction(TEVENT_PAGE_SCORE);
			$p->addMetaData('matchId', $matchId);
			$p->addMetaData('width', 630);
			$p->addMetaData('height', 320);
			$p->addMetaData('title', "'". LOC_TITLE_SCORE . "'");
			$p->addMetaData('target', "'targetDlg'");
			$p->completeAttribute('class', 'bn-dlg');
			$p->setAttribute('title', 'Saisir le résultat');

			// icone pour le deplacement
			$p = $cell->addP('', '', 'bteam-match-move ui-icon ui-icon-arrow-4');
			$p->setAttribute('title', 'Déplacer le match');

			// Rencontre
			$str = $oTie->getVal('teamstamph') . '-' . $oTie->getVal('teamstampv');
			$p = $cell->addP('', $str, 'bteam-match-tie');

			// Joueur paire hote
			$div = $cell->addDiv('', 'bteam-match-pair bteam-result-' . $cMatch->getVal('resulth'));
			$this->_formatPlayer($div, $cMatch, 'h', 1);
			if ( $cMatch->getVal('discipline') != OMATCH_DISCIPLINE_SINGLE )
			{
				$this->_formatPlayer($div, $cMatch, 'h', 2);
			}
			$div->addBreak();

			// Joueur paire visiteuse
			$div = $cell->addDiv('', 'bteam-match-pair bteam-result-' . $cMatch->getVal('resultv'));
			$this->_formatPlayer($div, $cMatch, 'v', 1);
			if ( $cMatch->getVal('discipline') != OMATCH_DISCIPLINE_SINGLE )
			{
				$this->_formatPlayer($div, $cMatch, 'v', 2);
			}
			$div->addBreak();

			// score
			$cell->addP('', $cMatch->getVal('score'), 'bteam-match-score');
			unset($cMatch);
		}
			unset($oTie);

		$body->addJqready('fillMatchs('.$numCol.',' . $tieId . ');');
		$body->display();
		return false;
	}

	/**
	 * Affichage de la page d'accueil
	 */
	public function pageTies()
	{

        // Controle d ela base de donne. rustine
		$q = new Bn_query();
		if ( !$q->existsDatabase('_team') )
        {
            $q->createDatabase('badnetteam');
            unset($q);
            $q = new Bn_query('event', '_team');
            $q->loadFile('_team');
        }

		$cEvent = new Cevent();
		$body = new Body();
		// Commandes generiques
		$div = $body->addDiv('', 'bn-menu-right');
		$p = $div->addP();
		if (!$cEvent->isEmpty())
		{
			$btn = $p->addButtonGoto('btnFile', LOC_ITEM_SAVE, TTRANSFERT_OUTPUT_FILE, 'disk');
		}
		$btn = $p->addButton('btnLoad', LOC_ITEM_LOAD, TTRANSFERT_PAGE_FILE, 'document', 'targetDlg');
		$btn->completeAttribute('class', 'bn-dlg');
		$btn->addMetaData('title', "'". LOC_TITLE_FILE . "'");
		$btn->addMetaData('width', 465);
		$btn->addMetaData('height', 220);
		if (!$cEvent->isEmpty())
		{
			$btn = $p->addButton('btnParam', LOC_ITEM_PARAM, TEVENT_PAGE_PARAM, 'wrench', 'targetDlg');
			$btn->completeAttribute('class', 'bn-dlg');
			$btn->addMetaData('title', "'". LOC_TITLE_PARAM . "'");
			$btn->addMetaData('width', 465);
			$btn->addMetaData('height', 220);
		}
		// Titre
		$t = $body->addP('', $cEvent->getVal('name'), 'bn-title-1');
		$t->addBalise('span', '', $cEvent->getVal('place'));
		$body->addHidden('warming', $cEvent->getVal('warming')*60);

		$str = "Sélectionner la rencontre dans la liste déroulante au-dessus du terrain dédié à son déroulement. Utiliser les icones pour la gestion des rencontres. Des info-bulles sont renseignées pour chacune d'elles. Cliquer sur le nom des équipes pour afficher la liste des joueurs. Les terrains sont déplaçables : cliquer dans le terrain avec le bouton gauche, laisser le bouton enfoncé, déplacer le terrain, relacher le bouton.";
		$d = $body->addWarning($str);

		// Div des rencontres : 5 possibles a la fois. Ca devrait suffire
		// Liste des rencontres
		$tieIds = Ctie::getTieIds();
		$lov[-1] = '----';
		foreach ($tieIds as $tieId)
		{
			$oTie = new Ctie($tieId);
			$str = $oTie->getVal('teamstamph') . '-' . $oTie->getVal('teamstampv');
			if ( $oTie->getVal('pbl') > 0 ) $display[$oTie->getVal('pbl')] = $oTie->getVal('id');
			$lov[$tieId] = $str;
			unset($oTie);
		}
		$div = $body->addDiv('', 'bn-div-clear');
		$nbTies = 5;
		for ($i=1; $i<=$nbTies; $i++)
		{
			$d = $div->addDiv('tie-'.$i, 'bteam-tie');
			$slct = $d->addSelect('slcttie-' . $i, '', TEVENT_FILL_TIE);
			$tieId = empty($display[$i]) ? -1 : $display[$i];
			$slct->addOptions($lov, $tieId);
			$slct->getSelect()->addMetadata('numCol', $i);
			$d->addDiv('bteam-tie-data-'.$i);
		}
		$div->addBreak();

		// Terrains des matchs
		$nbTerrain = $cEvent->getVal('nbcourt');
		$line = $body->addDiv('div-courts', 'bn-div-clear');
		for ($i=1; $i<=$nbTerrain; $i++)
		{
			$court = $line->addDiv('court-'.$i, 'bteam-court');
			$d = $court->addDiv();
			$d->addP('', 'Court '.$i, 'bteam-court-name');
			$d->addP('', '--:--', 'bteam-court-start');
			$d->addP('', '00:00', 'bteam-court-chrono');
			$slot = $court->addDiv('slot-'.$i, 'bteam-slot');
			$slot->addMetaData('numcourt', $i);
			$slot->setAction(TEVENT_START_MATCH);
			$slots[$i] = $slot;
		}

		// Affichage des match en cours
		$matchIds = Cmatch::getLiveIds();
		foreach($matchIds as $matchId)
		{
			$cMatch = new Cmatch($matchId);

			// Cellule du match
		    $cell = $slots[$cMatch->getVal('court')]->addDiv('match-'.$matchId, 'bteam-match bteam-match-'.$cMatch->getVal('tieid'));

			$oTie = new Ctie($cMatch->getVal('tieid'));
			$cell->addMetaData('matchId', $matchId);
			$cell->addMetaData('tieId', $cMatch->getVal('tieid'));
			$cell->addMetaData('numCol', $oTie->getVal('pbl'));
			$cell->addHidden('start-'.$matchId, $cMatch->getVal('begin', '0000-00-00 00:00:00'));
			$cell->addHidden('status-'.$matchId, $cMatch->getVal('status'));

			// Numero de match
			$cell->addP('', 'N° ' . $cMatch->getVal('rank'), 'bteam-match-rank bteam-match-status-' . $cMatch->getVal('status'));

			// Type du match
			$cell->addP('', $cMatch->getVal('labdiscir'), 'bteam-match-disci');

			// icones pour le changement d'ordre
			$p = $cell->addP('', '', 'bteam-match-up ui-icon ui-icon-arrowthick-1-n');
			$p->setAttribute('title', 'Monter le match');
			$p->setAction(TEVENT_MATCH_UPDOWN);
			$p = $cell->addP('', '', 'bteam-match-down ui-icon ui-icon-arrowthick-1-s');
			$p->setAttribute('title', 'Descendre le match');
			$p->setAction(TEVENT_MATCH_UPDOWN);

			// icone pour saisie du score
			$p = $cell->addP('', '', 'bteam-match-edit ui-icon ui-icon-pencil');
			$p->setAction(TEVENT_PAGE_SCORE);
			$p->addMetaData('matchId', $matchId);
			$p->addMetaData('width', 630);
			$p->addMetaData('height', 320);
			$p->addMetaData('title', "'". LOC_TITLE_SCORE . "'");
			$p->addMetaData('target', "'targetDlg'");
			$p->completeAttribute('class', 'bn-dlg');
			$p->setAttribute('title', 'Saisir le résultat');

			// icone pour le deplacement
			$p = $cell->addP('', '', 'bteam-match-move ui-icon ui-icon-arrow-4');
			$p->setAttribute('title', 'Déplacer le match');

			// Rencontre
			$str = $oTie->getVal('teamstamph') . '-' . $oTie->getVal('teamstampv');
			$p = $cell->addP('', $str, 'bteam-match-tie');
			unset($oTie);

			// Joueur paire hote
			$div = $cell->addDiv('', 'bteam-match-pair');
			$this->_formatPlayer($div, $cMatch, 'h', 1);
			if ( $cMatch->getVal('discipline') != OMATCH_DISCIPLINE_SINGLE )
			{
				$this->_formatPlayer($div, $cMatch, 'h', 2);
			}
			$div->addBreak();

			// Joueur paire visiteuse
			$div = $cell->addDiv('', 'bteam-match-pair');
			$this->_formatPlayer($div, $cMatch, 'v', 1);
			if ( $cMatch->getVal('discipline') != OMATCH_DISCIPLINE_SINGLE )
			{
				$this->_formatPlayer($div, $cMatch, 'v', 2);
			}
			$div->addBreak();

			// score
			$cell->addP('', $cMatch->getVal('score'), 'bteam-match-score');
			unset($cMatch);
			unset($oTie);
		}

		// Div des matchs une colonne par rencontres
		$div = $body->addDiv('divCols', 'bn-div-clear');
		$div->setAction(TEVENT_STOP_MATCH);
		$nbCourt = 5;
		for ($i=1; $i<=$nbCourt; $i++)
		{
			$divCourt = $div->addDiv('bteam-col-' . $i, 'bn-div-left bteam-col-court');
			$divCourt->setAction(TEVENT_FILL_MATCHS);
			$divCourt->addMetaData('numCol', $i);
		}
		$div->addBreak();

		if ( $cEvent->isEmpty() ) $body->addJQReady('pageTies(1);');
		else $body->addJQReady('pageTies(0);');
		$body->display();
		return false;

	}

	private function _formatPlayer($aDiv, $aMatch, $aTeam, $aNum)
	{
		$court  = $aMatch->getVal('court' . $aTeam . $aNum);
		$rest  = $aMatch->getVal('rest' . $aTeam . $aNum);
		$status = $aMatch->getVal('status');
		$str = $aMatch->getVal('fam' . $aTeam . $aNum);
		$str .= ' ' . $aMatch->getVal('first' . $aTeam . $aNum);
		if (empty($str)) return $str;
		$discipline = $aMatch->getVal('discipline');
		if ($discipline == OMATCH_DISCIPLINE_SINGLE) $str .= ' (' . $aMatch->getVal('levels' . $aTeam . $aNum);
		else if ($discipline == OMATCH_DISCIPLINE_DOUBLE) $str .= ' (' . $aMatch->getVal('leveld' . $aTeam . $aNum);
		else if ($discipline == OMATCH_DISCIPLINE_MIXED) $str .= ' (' . $aMatch->getVal('levelm' . $aTeam . $aNum);
		$str .= '-' . $aMatch->getVal('labcatage' . $aTeam . $aNum);
		$surclasse = $aMatch->getVal('labsurclasse' . $aTeam . $aNum);
		if ( !empty($surclasse) ) $str .= '-' . $aMatch->getVal('labsurclasse' . $aTeam . $aNum);
		$str .=  ')';

		$now = date('U');
		$bal = new Bn_balise();
		$div = $aDiv;
		$numId = $aMatch->getVal('play' . $aTeam . $aNum . 'numid');
		if ($status > OMATCH_STATUS_LIVE )
		{
			$str = $str;
			$p = $div->addP('', $str, 'bteam-match-player player-' . $numId);
		}
		else if ($status == OMATCH_STATUS_LIVE )
		{
			$str = '[' . $court . ']' . $str;
			$p = $div->addP('', $str, 'bteam-match-player live player-' . $numId);
		}
		else if ($court > 0 )
		{
			$str = '[' . $court . ']' . $str;
			$p = $div->addP('', $str, 'bteam-match-player busy player-' . $numId);
		}
		else if( Bn::date($rest, 'U') > $now)
		{
			$str = '[' . Bn::date($rest, 'H:i') . ']' . $str;
			$p = $div->addP('', $str, 'bteam-match-player rest player-' . $numId);
		}
		else $p = $div->addP('', $str, 'bteam-match-player player-' . $numId);
		$p->setAttribute('title', $str);
		$p->addMetaData('playerId', $numId);
		return $bal;
	}

}
?>
