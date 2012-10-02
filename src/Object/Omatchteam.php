<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Otie.inc';
require_once 'Omember.inc';
require_once 'Omatch.inc';
require_once 'Opair.inc';

$locale = BN::getLocale();
require_once 'Locale/' . $locale . '/Omatch.inc';

class Omatchteam extends Object
{
	// pour le live scoring
	private $_numGame = 0;

	/**
	 * Constructeur
	 */
	Public function __construct($aMatchId)
	{
		if($aMatchId > 0)
		{
			$this->load('matchs', 'mtch_id=' . $aMatchId);
			//$this->setVal('disci', $this->getVal('discipline'));

			$oTie   = new Otie($this->getVal('tieid'));
			$oTeamH = $oTie->getTeam(OTIE_TEAM_RECEIVER);
			$oTeamV = $oTie->getTeam(OTIE_TEAM_VISITOR);
			$this->setVal('tie', $oTie);
			$this->setVal('teamh', $oTeamH);
			$this->setVal('teamv', $oTeamV);

			// Joueurs du match
			$q = new Bn_query('members, registration, i2p, pairs, p2m, rankdef');
			$q->setFields('regi_id, regi_teamid, regi_longname, rkdf_label, p2m_result');
			$q->addWhere('mber_id = regi_memberid');
			$q->addWhere('regi_id = i2p_regiid');
			$q->addWhere('i2p_pairid = pair_id');
			$q->addWhere('pair_id=p2m_pairid');
			$q->addWhere('i2p_rankdefid=rkdf_id');
			$q->addWhere('p2m_matchid=' . $this->getVal('id', -1));
			$q->setOrder('pair_id, mber_sexe, regi_longname');
			$players = $q->getRows();
			$teamH = $oTeamH->getVal('id');
			$teamV = $oTeamV->getVal('id');
			foreach($players as $player)
			{
				$playerh1 = $this->getVal('playerh1', '');
				if($player['regi_teamid'] == $teamH) if (empty($playerh1)) $this->setVal('playerh1', $player); else $this->setVal('playerh2', $player);
				$playerv1 = $this->getVal('playerv1', '');
				if($player['regi_teamid'] == $teamV) if (empty($playerv1)) $this->setVal('playerv1', $player); else $this->setVal('playerv2', $player);
			}
		}
	}

	/**
	 * Affichage pour la consultation du match
	 *
	 * @param Bn_balise $aDiv  balise ou sera affiche le match
	 */
	Public function displayConsult(Bn_balise $aDiv)
	{
		// Calcul du genre des joueurs en fonction de la discipline
		$disci = $this->getVal('discipline');
		if ($disci == OMATCH_DISCI_MS || $disci == OMATCH_DISCI_MD || $disci == OMATCH_DISCI_XD ) $gender = OMEMBER_MALE ;
		else if ($disci == OMATCH_DISCI_WS || $disci == OMATCH_DISCI_WD ) $gender = OMEMBER_FEMALE;
		else $gender = null;

		// Donnees du match
		$nb = $this->getVal('order');
		$dMatch = $aDiv->addDiv('', 'dMatch');

		// Afficher la discipline du match
		$dDisci = $dMatch->addDiv('', 'dmDisci');
		$str = constant('SMA_LABEL_' . $disci) . ' ' . $nb;
		$dDisci->addP('', $str, 'cDisci');

		// Afficher le resultat  l'equipe hote
		$playerh1 = $this->getVal('playerh1');
		$theme = Bn::getValue('theme', 'Badnet');
		$dMatch->addDiv('', 'dmResult')->addImage('', '../Themes/' . $theme . '/Img/' . $playerh1['p2m_result'] . '.png', 'result');

		// Afficher les joueurs de l'equipe hote
		$dHote = $dMatch->addDiv('', 'dmPlayer');
		$str = $playerh1['regi_longname'] . ' - ' . $playerh1['rkdf_label'];
		$dHote->addP('h1_' . $disci . '_' . $nb,  $str, 'cPlayerhote');
		if ($disci == OMATCH_DISCI_MD || $disci == OMATCH_DISCI_WD || $disci == OMATCH_DISCI_XD)
		{
			$playerh2 = $this->getVal('playerh2');
			$str = $playerh2['regi_longname'] . ' - ' . $playerh2['rkdf_label'];
			$dHote->addP('h2_' . $disci . '_' . $nb,  $str, 'cPlayerhote');
		}

		// Afficher le score
		$oTeamH = $this->getVal('teamh');
		$oEvent = new Oevent($oTeamH->getVal('eventid', -1));
		$dScore = $dMatch->addDiv('', 'dmScore');
		$scosys = 'Oscore_' . $oEvent->getVal('scoringsystem');
		$oScore = new $scosys();
		$oScore->setScore($this->getVal('score'));
		if ($playerh1['p2m_result'] == OPAIR_RES_WINAB || 
		    $playerh1['p2m_result'] == OPAIR_RES_WINWO ||
		    $playerh1['p2m_result'] == OPAIR_RES_WIN ) 
		{
		 	$dScore->addP('s1_' . $disci . '_' . $nb,  $oScore->getWinScore(), 'cScore');
		}
		else $dScore->addP('s1_' . $disci . '_' . $nb,  $oScore->getLooseScore(), 'cScore');
		
		// Afficher le resultat  l'equipe hote
		$playerv1 = $this->getVal('playerv1');
		$dMatch->addDiv('', 'dmResult')->addImage('', '../Themes/' . $theme . '/Img/' . $playerv1['p2m_result'] . '.png', 'result');

		// Afficher la liste des joueurs de l'equipe visiteur
		$dVisit = $dMatch->addDiv('', 'dmPlayer');
		$str = $playerv1['regi_longname'] . ' - ' . $playerv1['rkdf_label'];
		$dVisit->addP('v1_' . $disci . '_' . $nb,  $str, 'cPlayervisit');
		if ($disci == OMATCH_DISCI_MD || $disci == OMATCH_DISCI_WD || $disci == OMATCH_DISCI_XD)
		{
			$playerv2 = $this->getVal('playerv2');
			$str = $playerv2['regi_longname'] . ' - ' . $playerv2['rkdf_label'];
			$dVisit->addP('v2_' . $disci . '_' . $nb,  $str, 'cPlayervisit');
		}
		$dMatch->addBreak();
	}

	/**
	 * Affichage pour la saisie du resultats du match
	 *
	 * @param Bn_balise $aDiv  balise ou sera affiche le match
	 */
	Public function display(Bn_balise $aDiv, $aTabIndex=0)
	{
		// Calcul du genre des joueurs en fonction de la discipline
		$disci = $this->getVal('discipline');
		if ($disci == OMATCH_DISCI_MS || $disci == OMATCH_DISCI_MD || $disci == OMATCH_DISCI_XD ) $gender = OMEMBER_MALE ;
		else if ($disci == OMATCH_DISCI_WS || $disci == OMATCH_DISCI_WD ) $gender = OMEMBER_FEMALE;
		else $gender = null;

		// Equipes du match
		$oTeamHote  = $this->getVal('teamh');
		$oTeamVisit = $this->getVal('teamv');
		$nb = $this->getVal('order');

		// Tournoi
		$oEvent = new Oevent($oTeamHote->getVal('eventid', -1));

		// Joueurs du match
		$player1 = $this->getVal('playerh1');
		$playerh1 = empty($player1) ? -1: $player1['regi_id'];
		$player = $this->getVal('playerh2');
		$playerh2 = empty($player) ? -1: $player['regi_id'];
		$player = $this->getVal('playerv1');
		$playerv1 = empty($player) ? -1: $player['regi_id'];
		$player = $this->getVal('playerv2');
		$playerv2 = empty($player) ? -1: $player['regi_id'];

		$dMatch = $aDiv->addDiv('', 'dMatch');

		// Afficher la discipline du match
		$dDisci = $dMatch->addDiv('', 'dmDisci');
		$str = constant('SMA_LABEL_' . $disci) . ' ' . $nb;
		$dDisci->addP('', $str, 'cDisci');

		// Afficher la liste des joueurs de l'equipe hote
		$matchId = $this->getVal('id', -1);
		$playersHote  = $oTeamHote->getLovPlayers($gender, $disci);
		$b = new Bn_balise();
		$lst = $b->addSelect('h1_' . $matchId,  '');
		$lst->addOptions($playersHote, $playerh1, true);
		$lst->completeAttribute('class', 'cPlayerhote');
		$test[] = $lst;

		// Afficher la liste des joueurs de l'equipe visiteur
		$playersVisit = $oTeamVisit->getLovPlayers($gender, $disci);
		$lst = $b->addSelect('v1_' . $matchId,  '');
		$lst->addOptions($playersVisit, $playerv1, true);
		$lst->completeAttribute('class', 'cPlayervisit');
		$test[] = $lst;

		// Match de double
		if ($disci == OMATCH_DISCI_MD || $disci == OMATCH_DISCI_WD )
		{
			// Equipe hote
			$lst = $b->addSelect('h2_' . $matchId,  '');
			$lst->addOptions($playersHote, $playerh2, true);
			$lst->completeAttribute('class', 'cPlayerhote');
			$test[] = $lst;
			// Equipe visiteur
			$lst = $b->addSelect('v2_' . $matchId,  '');
			$lst->addOptions($playersVisit, $playerv2, true);
			$lst->completeAttribute('class', 'cPlayervisit');
			$test[] = $lst;
				
		}

		// Match de mixte
		if ($disci == OMATCH_DISCI_XD )
		{
			// Equipe hote
			$playersHote  = $oTeamHote->getLovPlayers(OMEMBER_FEMALE, $disci);
			$lst = $b->addSelect('h2_' . $matchId,  '');
			$lst->addOptions($playersHote, $playerh2, true);
			$lst->completeAttribute('class', 'cPlayerhote');
			$test[] = $lst;
			// Equipe visiteur
			$playerVisit  = $oTeamVisit->getLovPlayers(OMEMBER_FEMALE, $disci);
			$lst = $b->addSelect('v2_' . $matchId,  '');
			$lst->addOptions($playerVisit, $playerv2, true);
			$lst->completeAttribute('class', 'cPlayervisit');
			$test[] = $lst;
		}

		// Afficher le score
		$scosys = 'Oscore_' . $oEvent->getVal('scoringsystem');
		$oScore = new $scosys();
		$winH = $player1['p2m_result'] == OPAIR_RES_LOOSEAB || $player1['p2m_result'] == OPAIR_RES_LOOSEWO;
		$tabIndex = $oScore->display($dMatch, $matchId, $this->getVal('score'), $winH, $test, $aTabIndex);
		return $tabIndex;
	}

	/**
	 * Enregistrement des paires et resultat du match
	 *
	 */
	Public function saveResult()
	{
		// Donnees du match
		$oTie   = new Otie($this->getVal('tieid'));
		$oTeamH = $oTie->getTeam(OTIE_TEAM_RECEIVER);
		$oTeamV = $oTie->getTeam(OTIE_TEAM_VISITOR);
		$oEvent = new Oevent($oTeamH->getVal('eventid', -1));
		$matchId = $this->getVal('id', -1);

		// Joueurs du match
		$q = new Bn_query('registration');
		$q->setFields('regi_memberid');
		$q->setWhere('regi_id = ' . $this->getVal('playerh1id', -1));
		$memberh1Id = $q->getFirst();
		$q->setWhere('regi_id = ' . $this->getVal('playerh2id', -1));
		$memberh2Id = $q->getFirst();
		$q->setWhere('regi_id = ' . $this->getVal('playerv1id', -1));
		$memberv1Id = $q->getFirst();
		$q->setWhere('regi_id = ' . $this->getVal('playerv2id', -1));
		$memberv2Id = $q->getFirst();

		// Tableau du match
		$q = new Bn_query('ties, rounds');
		$q->setFields('rund_drawid');
		$q->setWhere('tie_id=' . $this->getVal('tieid', -1));
		$q->addWhere('rund_id=tie_roundid');
		$drawId = $q->getFirst();

		// Traitement du score
		$scosys = 'Oscore_' . $oEvent->getVal('scoringsystem');
		$oScore = new $scosys();
		$score = $this->getVal('score');
		$oScore->setScore($score);
		if(empty($score)) $this->setVal('status', OMATCH_STATUS_READY);
		$win = 'playerv';
		$loose = 'playerh';
		if($this->getVal('resulth', -1) == -1)
		{
			if ( $oScore->isWinner() )
			{
				$win = 'playerh';
				$loose = 'playerv';
			}
		}
		else
		{
			if ( $this->getVal('resultv') > $this->getVal('resulth') )
			{
				$win = 'playerh';
				$loose = 'playerv';
			}
			
		}
		// Gestion des paires
		$regi1Id = $this->getVal( $win . '1id');
		$regi2Id = $this->getVal( $win . '2id');
		if ( !empty($regi1Id) )
		{
			if (empty($score)) $pairId = $this->_saveResult($drawId, $regi1Id, $regi2Id, OPAIR_POS_TOP, OPAIR_RES_NOPLAY);
			elseif ($oScore->isWo()) $pairId = $this->_saveResult($drawId, $regi1Id, $regi2Id, OPAIR_POS_TOP, OPAIR_RES_WINWO);
			elseif ($oScore->isAbort()) $pairId = $this->_saveResult($drawId, $regi1Id, $regi2Id, OPAIR_POS_TOP, OPAIR_RES_WINAB);
			else $pairId = $this->_saveResult($drawId, $regi1Id, $regi2Id, OPAIR_POS_TOP, OPAIR_RES_WIN);
		}
		else $this->setVal('status', OMATCH_STATUS_INCOMPLET);

		// Seconde paire :
		$regi1Id = $this->getVal($loose . '1id');
		$regi2Id = $this->getVal($loose . '2id');
		if ( !empty($regi1Id) )
		{
			if (empty($score))$pairId = $this->_saveResult($drawId, $regi1Id, $regi2Id, OPAIR_POS_BOTTOM, OPAIR_RES_NOPLAY);
			elseif ($oScore->isWo()) $pairId = $this->_saveResult($drawId, $regi1Id, $regi2Id, OPAIR_POS_BOTTOM, OPAIR_RES_LOOSEWO);
			elseif ($oScore->isAbort()) $pairId = $this->_saveResult($drawId, $regi1Id, $regi2Id, OPAIR_POS_BOTTOM, OPAIR_RES_LOOSEAB);
			else $pairId = $this->_saveResult($drawId, $regi1Id, $regi2Id, OPAIR_POS_BOTTOM, OPAIR_RES_LOOSE);
		}
		else $this->setVal('status', OMATCH_STATUS_INCOMPLET);

		// Joueur du match
		$q = new Bn_query('members, registration, i2p, pairs, p2m, rankdef');
		$q->setFields('regi_id, regi_teamid, regi_longname, rkdf_label, p2m_result, mber_id');
		$q->addWhere('mber_id = regi_memberid');
		$q->addWhere('regi_id = i2p_regiid');
		$q->addWhere('i2p_pairid = pair_id');
		$q->addWhere('pair_id=p2m_pairid');
		$q->addWhere('i2p_rankdefid=rkdf_id');
		$q->addWhere('p2m_matchid=' . $matchId);
		$q->setOrder('pair_id, mber_sexe, regi_longname');
		$players = $q->getRows();
		$teamHid = $oTeamH->getVal('id');
		$teamVid = $oTeamV->getVal('id');
		$this->setVal('playerh1', null);
		$this->setVal('playerh2', null);
		$this->setVal('playerv1', null);
		$this->setVal('playerv2', null);
		foreach($players as $player)
		{
			$playerh1 = $this->getVal('playerh1', null);
			$playerv1 = $this->getVal('playerv1', null);
			if($player['regi_teamid'] == $teamHid) if (empty($playerh1)) $this->setVal('playerh1', $player); else $this->setVal('playerh2', $player);
			if($player['regi_teamid'] == $teamVid) if (empty($playerv1)) $this->setVal('playerv1', $player); else $this->setVal('playerv2', $player);
		}

		$this->save();

	}

	private function _saveResult($aDrawId, $aRegi1Id, $aRegi2Id, $aPos, $aRes)
	{
		if($aRegi1Id < 0 || $aRegi2Id < 0) return;
		// Classement actuel des joueurs
		$q = new Bn_query('registration, ranks');
		$q->setFields('rank_rankdefid, rank_average, rank_rank');
		$q->setWhere('regi_id = ' . $aRegi1Id);
		$q->addWhere('rank_regiid=regi_id');
		$q->addWhere('rank_discipline=' . $this->getVal('disci'));
		$regi1 = $q->getRow();
		
		if ( $this->getVal('disci') != OMATCH_DISCIPLINE_SINGLE)
		{
			$q->setWhere('regi_id = ' . $aRegi2Id);
			$q->addWhere('rank_regiid=regi_id');
			$q->addWhere('rank_discipline=' . $this->getVal('disci'));
			$regi2 = $q->getRow();
		}
		// Recherche de la paire des joueurs avec le meme classement
		$q->setTables('i2p, pairs');
		$q->addTable('i2p', 'r1.i2p_pairid=pair_id', 'r1');
		$q->setFields('pair_id');
		$q->addWhere('r1.i2p_regiid=' . $aRegi1Id);
		$q->addWhere('pair_disci=' . $this->getVal('disci'));
		$q->addWhere('r1.i2p_rankdefid=' . $regi1['rank_rankdefid']);
		$q->addWhere('r1.i2p_classe=' . $regi1['rank_rank']);
		if ( $this->getVal('disci') != OMATCH_DISCIPLINE_SINGLE)
		{
			$q->addTable('i2p', 'r2.i2p_pairid=pair_id', 'r2');
			$q->addWhere('r2.i2p_regiid=' . $aRegi2Id);
			$q->addWhere('r2.i2p_rankdefid=' . $regi2['rank_rankdefid']);
			$q->addWhere('r2.i2p_classe=' . $regi2['rank_rank']);
		}
		$pairId = $q->getFirst();

		// Paire non trouvee : creation de la paire et de la relation avec l'inscrit
		if (empty($pairId))
		{
			// Creation de la paire
			$q->setTables('pairs');
			$q->setValue('pair_disci', $this->getVal('disci'));
			$q->addValue('pair_drawid', $aDrawId);
			$q->addValue('pair_status', OPAIR_STATUS_NONE);
			$q->addValue('pair_order', '');
			$q->addValue('pair_state', OPAIR_STATE_REG);
			$pairId = $q->addRow();
			$uniId = Bn::getUniId($pairId);
			$q->addValue('pair_uniid', $uniId);
			$q->updateRow('pair_id=' . $pairId);

			// Relation avec le joueur 1
			$q->setTables('i2p');
			$q->setValue('i2p_regiid', $aRegi1Id);
			$q->addValue('i2p_pairid',   $pairId);
			$q->addValue('i2p_cppp', $regi1['rank_average']);
			$q->addValue('i2p_rankdefid', $regi1['rank_rankdefid']);
			$q->addValue('i2p_classe', $regi1['rank_rank']);
			$q->addRow();

			// Relation avec le joueur 2
			if ( $this->getVal('disci') != OMATCH_DISCIPLINE_SINGLE)
			{
				$q->setTables('i2p');
				$q->setValue('i2p_regiid', $aRegi2Id);
				$q->addValue('i2p_pairid',   $pairId);
				$q->addValue('i2p_cppp', $regi2['rank_average']);
				$q->addValue('i2p_rankdefid', $regi2['rank_rankdefid']);
				$q->addValue('i2p_classe', $regi2['rank_rank']);
				$q->addRow();
			}
		}

		// Relation avec le match
		$q->setTables('p2m');
		$q->setValue('p2m_pairid',   $pairId);
		$q->addValue('p2m_matchid',  $this->getVal('id', -1));
		$q->addValue('p2m_result',   $aRes);
		$q->addValue('p2m_posmatch', $aPos);
		$q->setWhere('p2m_matchid=' .  $this->getVal('id', -1));
		$q->addWhere('p2m_posmatch=' . $aPos);
		$q->replaceRow();
		return $pairId;
	}

	/**
	 * Enregistre la definition d'un match
	 *
	 * @param string $aWhere  clause where
	 * @return unknown
	 */
	Public function save($aWhere = null)
	{
		if ( empty($aWhere) ) $where = 'mtch_id=' . $this->getVal('id', -1);
		else $where = $aWhere;

		$id = $this->update('matchs', 'mtch_', $this->getValues(), $where);
		return $id;
	}

	Public function getLength()
	{
		$utd = new utDate();
		$utd->setIsoDateTime($this->getVal('begin'));
		$duree  = $utd->getDiff($this->getVal('end'));
		if ($duree < 5 && $duree > 120)
		$duree = '--';
		else
		$duree .= ' mn';
		return $duree;
	}

	Public function getUmpire()	{ return new Oumpire($this->getVal('umpireid', -1));	}
	Public function getServiceJudge()	{ return new Oumpire($this->getVal('serviceid', -1));	}

}
?>
