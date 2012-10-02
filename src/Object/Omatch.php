<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Omatch.inc';
require_once 'Opair.php';

$locale = Bn::GetLocale();
require_once 'Locale/' . $locale . '/Omatch.inc';

class Omatch extends Object
{
	private $_pairs = array();// Pairs of the match
	private $_roundType = '';  // Type du tour
	private $_drawType = '';  // Type de tableau
	private $_drawName = '';  // Ex: Simple Homme Elite
	public 	$_drawStamp = ''; // Ex: SH El
	private $_stageName = ''; // Ex: Tableau final | Poule A
	private $_stageStamp = '';// Ex: TF | A
	private $_stepName = '';  // Ex : Demi-finale
	private $_stepStamp = ''; // Ex : 1/2
	private $_posStep = '';   // Ex : 2 | 2/3
	private $_posRound = 0;
	private $_order = 1; // Numero de match dans la discipline pour une rencontre par equipe SH 1 , SH 2, DD 1,
	private $_rank = 1;  // Ordre de match pour une rencontre par equipe 1,2,3, 4
	static $_matchs = array();
	private $_teams = array();

	// pour le live scoring
	private $_numGame = 0;


	public function delete()
	{
		// Supprimer les relations avec les paires
		$q = new Bn_query('p2m');
		$q->deleteRow('p2m_matchid=' . $this->getVal('id'));
		
		// Supprimer le match
		$q->setTables('matchs');
		$q->deleteRow('mtch_id=' . $this->getVal('id'));
		
	}
	
	/**
	 * Constructeur
	 */
	Public function __construct($aMatchId)
	{
		$q = new Bn_query('matchs, p2m, ties, draws, rounds');
		$q->addField('mtch_id',         'id');
		$q->addField('mtch_uniid',      'uniid');
		$q->addField('mtch_score',      'score');
		$q->addField('mtch_begin',      'begin');
		$q->addField('mtch_end',        'end');
		$q->addField('mtch_discipline', 'disci');
		$q->addField('mtch_court',     'court');
		$q->addField('mtch_num',       'num');
		$q->addField('mtch_status',    'status');
		$q->addField('mtch_umpireid',  'umpireid');
		$q->addField('mtch_serviceid', 'serviceid');
		$q->addField('mtch_order',     'ordered');
		$q->addField('mtch_rank',      'rank');
		$q->addField('tie_id',         'tieid');
		$q->addField('tie_schedule',   'schedule');
		$q->addField('tie_place',      'place');
		$q->addField('tie_posround',   'posround');
		$q->addField('draw_id',        'drawid');
		$q->addField('draw_uniid',     'drawuniid');
		$q->addField('draw_serial',    'serial');
		$q->addField('draw_name',      'drawname');
		$q->addField('draw_stamp',     'drawstamp');
		$q->addField('draw_type',      'drawtype');
		$q->addField('draw_eventid',   'eventid');
		$q->addField('rund_name',      'roundname');
		$q->addField('rund_stamp',     'roundstamp');
		$q->addField('rund_type',      'roundtype');
		$q->addField('rund_id',        'roundid');
		$q->addField('rund_size',      'size');
		$q->addField('p2m_pairid',     'pairid');
		$q->addField('p2m_posmatch',   'posmatch');
		$q->addWhere ('mtch_tieId = tie_id');
		$q->addWhere ('tie_roundId = rund_id');
		$q->addWhere ('rund_drawId = draw_id');
		$q->addWhere ('mtch_id = p2m_matchId');

		if (strpos($aMatchId, ':') !== false)
		{
			if (strpos($aMatchId, ';') !== false) $q->addWhere ("mtch_uniid = '" . $aMatchId . "'");
			else $q->addWhere ("mtch_uniid = '" . $aMatchId . ";'");
		}
		else $q->addWhere ('mtch_id =' . $aMatchId );
		$match = $q->getRow();
		//print_r($q);
		$this->setVal('id', $aMatchId);
		if ( empty($match) ) return false;
		$this->setValues($match);
		
		$this->setVal('time', substr($match['schedule'], 11, 5));
		$this->setVal('date', substr($match['schedule'], 0, 10));

		if ($match['disci'] < OMATCH_DISCI_MS || $match['disci'] == OMATCH_DISCI_AS) $discipline = OMATCH_DISCIPLINE_SINGLE;
		else if ($match['disci'] == OMATCH_DISCI_XD) $discipline = OMATCH_DISCIPLINE_MIXED;
		else $discipline = OMATCH_DISCIPLINE_DOUBLE;
		$this->setVal('discipline', $discipline);

		$label = $this->getTieStep($match['posround'], $match['roundtype']);
			
		if ($match['roundtype'] == OMATCH_ROUND_GROUP)//si poule->concat�nation rund name et label
		{
			$this->_stepName  = $this->_stageStamp . ' (' . $label . ')';
			$this->_stepStamp = $this->_stageStamp . ' (' . $label . ')';
		}
		else
		{
			$this->_stepName   = constant ("LABEL_".$label);
			$this->_stepStamp  = constant ("SMA_LABEL_".$label);
		}
		$this->_posStep = $label;    // Ex : 2 | 2/3
			
		// Initialisation des paires
		if (is_numeric($match['pairid']))
		{
			$specialName = null;
			// Match de poule de 3, deuxieme ou troisieme tour
			if ($match['roundtype'] == OMATCH_ROUND_GROUP &&
			$match['size'] == 3 &&
			$match['posmatch'] == OMATCH_PAIR_BOTTOM &&
			$match['posround'] < 2)
			{
				// Resultats du premier match
				$q->setTables('matchs');
				$q->addTable('ties', 'tie_id=mtch_tieid');
				$q->addTable('rounds', 'rund_id=tie_roundid');
				$q->setFields ('mtch_num, mtch_status');
				$q->addWhere ('rund_id='.$match['roundid']);
				$q->addWhere ('tie_posround=2');
				$result = $q->getRow();
				// match joue
				if ($result['mtch_status'] <= OMATCH_STATUS_LIVE)
				{
					$tmp = $match['tie_posround'] ? "Perdant":"Vainqueur";
					$specialName = "{$tmp} match {$result['mtch_num']}";
				}
			}
			// Match de poule de 4, a traiter
			if ($match['roundtype'] == OMATCH_ROUND_GROUP &&
			$match['size'] == 4 &&
			$match['posround'] != 1 &&
			$match['posround'] != 4)
			{
				// Resultats des deux premiers matchs
				$q->setTables('matchs');
				$q->addTable('ties', 'tie_id=mtch_tieId');
				$q->addTable('rounds', 'rund_id=tie_roundid');
				$q->setFields ('mtch_num, mtch_status');
				$q->addWhere ('rund_id='.$match['roundid']);
				$q->addWhere ('tie_posround in (1, 4)');
				$q->setOrder ('tie_posround');
				$res1 = $q->getRow();
				$res4 = $q->getRow();
					
				// match joue
				if (($match['posround']==3 || $match['posround']==0) &&
				$match['posmatch'] == OMATCH_PAIR_TOP &&
				$res1['mtch_status'] <= OMATCH_STATUS_LIVE)
				$specialName = OMATCH_VAINQUEUR.$res1['mtch_num'];
				else if (($match['posround']==3 || $match['posround']==5) &&
				$match['posmatch'] == OMATCH_PAIR_BOTTOM &&
				$res4['mtch_status'] <= OMATCH_STATUS_LIVE)
				$specialName = OMATCH_PERDANT.$res4['mtch_num'];
				else if ($match['posround']==2 &&
				$match['posmatch'] == OMATCH_PAIR_TOP &&
				$res4['mtch_status'] <= OMATCH_STATUS_LIVE)
				$specialName = OMATCH_VAINQUEUR.$res4['mtch_num'];
				else if ($match['posround']==2 &&
				$match['posmatch'] == OMATCH_PAIR_BOTTOM &&
				$res1['mtch_status'] <= OMATCH_STATUS_LIVE)
				$specialName = OMATCH_PERDANT.$res1['mtch_num'];
				else if ($match['posround']==0 &&
				$match['posmatch'] == OMATCH_PAIR_BOTTOM &&
				$res4['mtch_status'] <= OMATCH_STATUS_LIVE)
				$specialName = OMATCH_VAINQUEUR.$res4['mtch_num'];
				else if ($match['posround']==5 &&
				$match['posmatch'] == OMATCH_PAIR_TOP &&
				$res1['mtch_status'] <= OMATCH_STATUS_LIVE)
				$specialName = OMATCH_PERDANT.$res1['mtch_num'];
			}
			foreach ($q->getRows() as $match)
			{
				$this->_pairs[$match['posmatch']]  = 
				new pair($match['pairid'], $this->getVal('id'), $specialName);
				//new pair($match['pairid'], -1, $specialName);
			}
			//print_r($this);
			/*	
			foreach ($q->getRows() as $matchr)
			{
				print_r($matchr);
				$pair = new pair($matchr['pairid'], $matchr['tieid']);
				$this->_pairs[$matchr['posmatch']]  = $pair;
			}
*/
		}

		unset($match);
	}

	Public function display(Bn_balise $aDiv)
	{
		$disci = $this->getVal('disci');
		if ($disci == OMATCH_DISCI_MS || $disci == OMATCH_DISCI_MD ) $gender = OMEMBER_MALE ;
		else if ($disci == OMATCH_DISCI_WS || $disci == OMATCH_DISCI_WD )$gender = OMEMBER_FEMALE;
		else $gender = null;
		$playersHote  = $oTeamHote->getLovPlayers($gender, $disci);
		$playersVisit = $oTeamVisit->getLovPlayers($gender, $disci);

		$d = $aDiv->addDiv('', 'cMatch');
		$str = constant('SMA_LABEL_' . $this->getVal('disci')) . ' ' . $this->getVal('order');
		$d->addP('', $str, 'cDisci');

		$lst = $d->addSelect('h_' . $disci . '_' . $i,  '');
		$lst->addOptions($playersHote, reset($playersHote));
		$lst->completeAttribute('class', 'cPlayerhote');

		$lst = $d->addSelect('s' . $disci . '_' . $i,  '');
		$lst->addOptions($scores, reset($scores));
		$lst->completeAttribute('class', 'cScore');

		$lst = $d->addSelect('v_' . $disci . '_' . $i,  '');
		$lst->addOptions($playersVisit, reset($playersVisit));
		$lst->completeAttribute('class', 'cPlayervisit');

	}

	Public function save($aData)
	{
		$this->update('matchs', 'mtch_', $aData, 'mtch_id=' . $this->getVal('id', -1));
	}

	Public function getGame() { return $this->getVal('numGame', 0);}
	Public function setGame($aNumGame) { $this->setVal('numGame', $aNumGame);}
	Public function getId() { return $this->getVal('id', -1);}
	Public function getPairs() { return $this->_pairs;}
	Public function getNum() { return $this->getVal('num');}
	Public function getDisci() { return $this->getVal('disci');}
	Public function getDisciLabel() { return constant('SMA_LABEL_' . $this->getVal('disci'));}
	Public function getDiscipline() { return $this->getVal('discipline');}
	Public function getDisciplineLabel() { return constant('SMA_LABEL_' . $this->getVal('discipline'));}
	Public function getCourt() { return $this->getVal('court');}
	Public function setCourt($aCourt) { return $this->setVal('court', $aCourt);}
	Public function getScore() { return $this->getVal('score');}
	Public function getSerial() { return $this->getVal('serial');}
	Public function getDrawName() { return $this->getVal('drawname');}
	Public function getDrawStamp() { return $this->getVal('drawstamp');}
	Public function getDraw() { return new Odraw($this->getVal('drawuniid', -1));}
	Public function getStageName() { return $this->getVal('roundname');}
	Public function getStageStamp() { return $this->getVal('roundstamp');}
	Public function getEnd() { return $this->getVal('end');}
	Public function getStart() { return $this->getVal('begin');}
	Public function setStart($aStart) { $this->setVal('begin', $aStart);}
	Public function getSchedule() { return $this->getVal('schedule', -1);}
	Public function getVenue() { return $this->getVal('place');}
	Public function getTime() { return $this->getVal('time');}
	Public function getDate() { return $this->getVal('date');}
	Public function getOrder() { return $this->getVal('order', 1);}
	Public function getRank() { return $this->getVal('rank', 1);}
	Public function getDrawType() { return $this->getVal('drawtype');}
	Public function getEventId() { return $this->getVal('eventid', -1);}
	//Public function isTeam() { return $this->getVal('drawtype') == OMATCH_DRAW_TEAM_GROUP || $this->getVal('drawtype') == OMATCH_DRAW_TEAM_BACK || $this->getVal('drawtype') == OMATCH_DRAW_TEAM_KO;}
	// @todo a eclaircir....
	Public function isTeam() { return $this->getVal('drawtype') == 71;}
	Public function getTeams() { return $this->_teams;}
	Public function getDay()
	{
		return strftime('%a', mktime(substr($this->getVal('schedule', -1),11,2),
		substr($this->getVal('schedule', -1),14,2),
		substr($this->getVal('schedule', -1),17,4),
		substr($this->getVal('schedule', -1),5,2),
		substr($this->getVal('schedule', -1),8,2),
		substr($this->getVal('schedule', -1),0,4)
		));
	}
	Public function getStepName() { return $this->_stepName;}
	Public function getStepStamp() { return $this->_stepStamp;}
	Public function getPosition() { return $this->_posStep;}
	Public function isDouble() { return ($this->getVal('disci') > OMATCH_DISCI_WS) && ($this->getVal('disci') != OMATCH_DISCI_AS);}
	Public function isEnded() { return $this->getVal('status') > OMATCH_STATUS_LIVE;}
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

	function getFirstWinName($long=true, $specialName=false)
	{
		if ($this->isEnded())
		foreach($this->_pairs as $pair)
		{
			if($pair->isWinner())
			return $pair->getFirstName($long);
		}
		else
		return $this->getFirstTopName($long, $specialName);
		return '';
	}

	function getFirstLosName($long=true, $specialName=false)
	{
		if ($this->isEnded())
		foreach($this->_pairs as $pair)
		{
			if(is_object($pair) &&
			$pair->isLooser())
			return $pair->getFirstName($long);
		}
		else
		return $this->getFirstBottomName($long, $specialName);
		return '';
	}

	function getSecondWinName($long=true)
	{
		if ($this->isEnded())
		foreach($this->_pairs as $pair)
		{
			if($pair->isWinner())
			return $pair->getSecondName($long);
		}
		else
		return $this->getSecondTopName($long);
		return '';
	}

	function getSecondLosName($long=true)
	{
		if ($this->isEnded())
		foreach($this->_pairs as $pair)
		{
			if($pair->isLooser())
			return $pair->getSecondName($long);
		}
		else
		return $this->getSecondBottomName($long);
		return '';
	}

	function getWinName($long=true)
	{
		foreach($this->_pairs as $pair)
		{
			if($pair->isWinner())
			return $pair->getName($long);
		}
		return '';
	}

	function getLosName($long=true)
	{
		foreach($this->_pairs as $pair)
		{
			if($pair->isLooser())
			return $pair->getName($long);
		}
		return '';
	}

	Public function getTieStep($posTie, $roundType)
	{
		if($roundType != OMATCH_ROUND_GROUP)
		{
			switch ($posTie)
			{
				case 0:
					return OMATCH_STAGE_FINAL;
				case 1:
				case 2:
					return OMATCH_STAGE_SEMI;
				case 3:
				case 4:
				case 5:
				case 6:
					return OMATCH_STAGE_QUATER;
			}
			if (($posTie > 6) && ($posTie < 15))
			return OMATCH_STAGE_HEIGHT;
			if (($posTie >= 15) && ($posTie < 31))
			return OMATCH_STAGE_16;
			if (($posTie >= 31) && ($posTie < 63))
			return OMATCH_STAGE_32;
			return OMATCH_STAGE_64;
		}
		else
		{
			switch ($posTie)
			{
				case 0:
					return "1/2";
				case 1:
					return "1/3";
				case 2:
					return "2/3";
				case 3:
					return "1/4";
				case 4:
					return "2/4";
				case 5:
					return "3/4";
				case 6:
					return "1/5";
				case 7:
					return "2/5";
				case 8:
					return "3/5";
				case 9:
					return "4/5";
				default :
					return "--";
			}
		}
	}

	function getFirstTopName($long=true, $specialName=false)
	{
		if (isset($this->_pairs[OMATCH_PAIR_TOP]))
		return $this->_pairs[OMATCH_PAIR_TOP]->getFirstName($long);
		else return '';
	}

	function getFirstBottomName($long=true, $specialName=false)
	{
		if (isset($this->_pairs[OMATCH_PAIR_BOTTOM]))
		return $this->_pairs[OMATCH_PAIR_BOTTOM]->getFirstName($long);
		else return '';
	}

	function getSecondTopName($long=true, $specialName=false)
	{
		if (isset($this->_pairs[OMATCH_PAIR_TOP]))
		return $this->_pairs[OMATCH_PAIR_TOP]->getSecondName($long);
		else return '';
	}

	function getSecondBottomName($long=true, $specialName=false)
	{
		if (isset($this->_pairs[OMATCH_PAIR_BOTTOM]))
		return $this->_pairs[OMATCH_PAIR_BOTTOM]->getSecondName($long);
		else return '';
	}

	/**
	 * Enregistrement du match termine
	 *
	 */
	public function end($aData, $aWinPairId, $aLoosePairId)
	{
		// Enregistrement des donnees du match
		$where = "mtch_uniid='" . $this->getVal('uniid') ."'";
		$this->update('matchs', 'mtch_', $aData, $where);

		// Mise a jour des relations paires match
		$winPairId  = -1;
		$loosePairId  = -1;
		$pairs = $this->_pairs;
		//print_r($pairs);
		/*
		foreach($pairs as $pair)
		{
			if ($pair->getuniId() == $aWinUniPairId) $winPairId = $pair->getId();
			if ($pair->getuniId() == $aLooseUniPairId) $loosePairId = $pair->getId();
		}*/
		$winPairId = $aWinPairId;
		$loosePairId = $aLoosePairId;
		if ($winPairId == -1 || $loosePairId == -1)
		{
			Bn::Log('----------- Omatch::end: Erreur recuperation paires du match');
			Bn::Log($aData);
			Bn::Log('winPairId=' . $aWinUniPairId);
			Bn::Log('loosePairId=' . $aLooseUniPairId);
			Bn::Log('----------- Omatch::end: Fin ');
			return;
		}
		// Paire perdante
		$os = new Oscore();
		$os->setScore($aData['score']);
		$q = new Bn_query('p2m');
		if ($os->isAbort())	$q->setValue('p2m_result', OPAIR_RES_LOOSEAB);
		if ($os->isWo())	$q->setValue('p2m_result', OPAIR_RES_LOOSEWO);
		else  $q->setValue('p2m_result', OPAIR_RES_LOOSE);
		$q->setWhere('p2m_pairid=' . $loosePairId );
		$q->addWhere('p2m_matchid=' . $this->getVal('id', -1));
		$q->updateRow();

		// Paire gagnante
		if ($os->isAbort())	$q->setValue('p2m_result', OPAIR_RES_WINAB);
		if ($os->isWo())	$q->setValue('p2m_result', OPAIR_RES_WINWO);
		else  $q->setValue('p2m_result', OPAIR_RES_WIN);
		$q->setWhere('p2m_pairid=' . $winPairId );
		$q->addWhere('p2m_matchid=' . $this->getVal('id', -1));
		$q->updateRow();

		// Mise a jour des temps de repos des joueurs
		foreach( $pairs as $pair)
		{
			$players = $pair->getPlayers();
			foreach($players as $player)
			{
				$regis[] = $player->getId();
			}
		}
		$q->setTables('registration');
		$q->setValue('regi_rest', $aData['end']);
		$q->setWhere('regi_id IN (' . implode(',', $regis) . ')');
		$q->addWhere("(regi_rest < '" . $aData['end'] . "' OR regi_rest IS NULL)");
		$q->updateRow();

		// Mise a jour des arbitres
		$this->getUmpire()->push();
		$this->getServiceJudge()->push();

		// Mise a jour de l'etat de tous les matchs
		//$this->updateMatchesStatus();

		// Match dans un tableau Ko :mise a jour du tour suivant
		if ($this->getVal('roundtype') > OMATCH_ROUND_GROUP && $this->getVal('posround', 0))
		{
			// Postion du match dans le prochain tour
			$posRound = intval(($this->getVal('posround')-1)/2);

			// Position de la paire dans le match du prochain tour
			$pairPos = ($this->getVal('posround')%2) ? OPAIR_POS_TOP:OPAIR_POS_BOTTOM;


			$this->updatePairToko($winPairId, $posRound, $pairPos, $this->getVal('roundid', -1));
			if ($this->getVal('roundtype') == OMATCH_ROUND_MAINDRAW) $this->_updateOtherKoTie($loosePairId);
		}

		//@todo  mise a jour des poules
		// Match in group round
		//if ($this->getVal('roundtype') == OMATCH_ROUND_GROUP) $this->_updateGroupMatch($winPairId, $oldWinner, $match);

		// Tournoi par equipe : mise a jour des rencontres
		$q->setTables('t2t, ties, matchs');
		$q->setFields('t2t_teamid');
		$q->setWhere('mtch_id=' . $this->getVal('id', -1));
		$q->addWhere('t2t_tieid = tie_id');
		$q->addWhere('tie_id = mtch_tieId');
		$teams = $q->getCol();
		if (count($teams))
		{
			$oteam = new Oteam($teams[0]);
			$oteam->updateResult($this->getVal('tieid', -1));
			unset($oteam);
			$oteam = new Oteam($teams[1]);
			$oteam->updateResult($this->getVal('tieid', -1));
			$oteam->updateGroupRank($this->getVal('roundid', -1));
			unset($oteam);
		}
		unset($q);
	}

	/**
	 * Mise a jour du status de tous les matches de la competition
	 */
	public function updateMatchesStatus()
	{
		$place = addslashes($this->getVal('place'));

		// Mettre tous les matches 'occupe' ou 'repos' a 'pret
		$q = new Bn_query('matchs, ties, rounds, draws');
		$q->setFields('mtch_id');
		$q->setWhere('mtch_tieid=tie_id');
		$q->addWhere('rund_drawid = draw_id');
		$q->addWhere('tie_roundid = rund_id');
		$q->addWhere('draw_eventid =' . $this->getVal('eventid', -1));
		$q->addWhere('(mtch_status =' . OMATCH_STATUS_BUSY . ' OR mtch_status=' . OMATCH_STATUS_BUSY .')');
		$matchs = $q->getCol();

		if (count($matchs))
		{
			$q->setValue('mtch_status', OMATCH_STATUS_READY);
			$q->setWhere('mtch_id IN (' . implode(',', $matchs) . ')');
			$q->updateRow();
		}
		unset($matchs);

		// Mise a jour des joueurs: tous les courts a 0
		$q->setTables('registration');
		$q->setValue('regi_court', 0);
		$q->setWhere('regi_eventid=' . $this->getVal('eventid', -1));
		$q->updateRow();

		// Recuperer tous les match en cours
		$q->setTables('matchs, ties, rounds, draws');
		$q->setFields('mtch_id, mtch_court, mtch_umpireId, mtch_serviceId');
		$q->setWhere('mtch_tieId=tie_id');
		$q->addWhere('mtch_status =' . OMATCH_STATUS_LIVE);
		$q->addWhere('tie_roundId = rund_id');
		$q->addWhere('rund_drawId = draw_id');
		$q->addWhere('draw_eventId =' . $this->getVal('eventid', -1));
		$matchs = $q->getRows();

		$regis = array();
		foreach($matchs as $match)
		{
			// Selectionner les joueurs du match
			$q->setFields('i2p_regiId');
			$q->setTables('matchs, p2m, i2p');
			$q->setWhere('i2p_pairId = p2m_pairId');
			$q->addWhere('p2m_matchId = mtch_id');
			$q->addWhere('mtch_id ='. $match['mtch_id']);
			$regism = $q->getCol();
			$regis = array_merge($regis, $regism);

			// Mettre a jour leur numero de terrain
			$q->setTables('registration');
			if (count($regism))
			{
				$q->setValue('regi_court', $match['mtch_court']);
				$q->setWhere('regi_id IN (' . implode(',', $regism) .')');
				$q->updateRow();
			}

			// Mettre a jour le numero de terrain de l'arbitre
			$q->setValue('regi_court', -$match['mtch_court']);
			$q->setWhere( 'regi_id =' . $match['mtch_umpireid'] . ' OR regi_id =' . $match['mtch_serviceid']);
			$q->updateRow();
		}
		if (!count($regis)) return;

		// Selectionner tous les matches 'complet' et non 'termine'
		// des joueurs sur les terrains, dans la meme salle
		$q->setTables('matchs, p2m, i2p, ties');
		$q->setFields('mtch_id');
		$q->setWhere('i2p_pairId = p2m_pairId');
		$q->addWhere('p2m_matchId = mtch_id');
		$q->addWhere('mtch_tieId = tie_id');
		$q->addWhere('mtch_status > ' . OMATCH_STATUS_INCOMPLET);
		$q->addWhere('mtch_status < ' . OMATCH_STATUS_LIVE);
		$q->addWhere('i2p_regiId IN (' . implode(',', $regis) . ')');
		if(!is_null($place)) $q->addWhere("tie_place = '$place'");
		$matchs = $q->getcol();

		// Tous ces matchs sont occupes
		if ( count($matchs) )
		{
			$q->setTables('matchs');
			$q->setValue('mtch_status', OMATCH_STATUS_BUSY);
			$q->setWhere('mtch_id IN ('. implode(',', $matchs) .')');
			$q->updateRow();
		}
		unset($q);
		return true;
	}

	/**
	 * Calculer le classement de la poule et mise a jour du numero et heure de match
	 *
	 * @access public
	 * @param  string  $info   column of the match
	 * @return mixed
	 */
	function _updateGroupMatch($winPairId, $oldWinner, $data, $match)
	{
		$db =& $this->_db;
		$this->_updateGroupRank($data['tie_roundid']);

		// Pour une poule de trois, si c'est le premier match
		// mettre a jour les numeros et horaires des deux autres matches
		if ($data['rund_size'] == 3 &&	$data['tie_posround'] == 2)
		{
			// Si le vainqueur est le troisieme joueur, il faut
			// inverser numero et heure des deux autres matches
			// Position du vainqueur
			$where = "t2r_pairId=$winPairId";
			$res = $db->select('t2r', 't2r_posRound', $where);
			$tmp = $res->fetchRow(MDB2_FETCHMODE_ASSOC);

			//il est en troisieme position
			if( ($oldWinner == -1 && $tmp['t2r_posround'] == 3) ||
			($oldWinner != -1 && $oldWinner != $winPairId))
			{
				// recherche des autres matchs de la poule
				$fields = array('tie_schedule', 'tie_place', 'tie_court',
			      'mtch_num', 'tie_id', 'mtch_id');
				$tables = array('ties', 'matchs');
				$where = "tie_id = mtch_tieId".
		" AND tie_roundId =". $data['tie_roundid'].
		" AND mtch_id !=". $match['mtch_id'];
				$res = $db->select($tables, $fields, $where);

				// Il doit y avoir 2 match
				if ($res->numRows() == 2)
				{
					$tmp1 = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
					$tmp2 = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
					$cols = array();
					$cols['mtch_num'] = $tmp1['mtch_num'];
					$where = "mtch_id=".$tmp2['mtch_id'];
					$db->update('matchs', $cols, $where);
					$cols['mtch_num'] = $tmp2['mtch_num'];
					$where = "mtch_id=".$tmp1['mtch_id'];
					$db->update('matchs', $cols, $where);
					$cols = array();
					$cols['tie_schedule'] = $tmp1['tie_schedule'];
					$cols['tie_place'] = $tmp1['tie_place'];
					$cols['tie_court'] = $tmp1['tie_court'];
					$where = "tie_id=".$tmp2['tie_id'];
					$db->update('ties', $cols, $where);
					$cols['tie_schedule'] = $tmp2['tie_schedule'];
					$cols['tie_place'] = $tmp2['tie_place'];
					$cols['tie_court'] = $tmp2['tie_court'];
					$where = "tie_id=".$tmp1['tie_id'];
					$db->update('ties', $cols, $where);
				}
				else
				echo "__FILE__.' ('.__LINE__.') :".
		  "Ca etre bizarre!!!";
			}
		}

		// Pour une poule de quatre, si c'est le premier match du premier tour
		// mettre a jour les numeros et horaires des quatres autres matches
		if ($data['rund_size'] == 4 &&
		$data['tie_posround'] == 1)
		{
			// Si le vainqueur est le troisieme joueur, il faut
			// inverser numero et heure des quatres autres matches
			// Position du vainqueur
			$where = "t2r_pairId=$winPairId";
			$res = $db->select('t2r', 't2r_posRound', $where);
			$tmp = $res->fetchRow(MDB2_FETCHMODE_ASSOC);

			//il est en troisieme position
			if( ($oldWinner == -1 && $tmp['t2r_posround'] == 3) ||
			($oldWinner != -1 && $oldWinner != $winPairId))
			{
				// recherche des autres matchs de la poule
				$fields = array('tie_schedule', 'tie_place', 'tie_court',
			      'mtch_num', 'tie_id', 'mtch_id');
				$tables = array('ties', 'matchs');
				$where = "tie_id = mtch_tieId".
		" AND tie_roundId =". $data['tie_roundid'].
		" AND tie_posround in (0,2,3,5)";
				$order = 'tie_posround';
				$res = $db->select($tables, $fields, $where, $order);

				// Il doit y avoir 4 match
				if ($res->numRows() == 4)
				{
					$tmp0 = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
					$tmp2 = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
					$tmp3 = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
					$tmp5 = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
					$cols = array();
					$cols['mtch_num'] = $tmp0['mtch_num'];
					$where = "mtch_id=".$tmp2['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols['mtch_num'] = $tmp2['mtch_num'];
					$where = "mtch_id=".$tmp0['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols['mtch_num'] = $tmp3['mtch_num'];
					$where = "mtch_id=".$tmp5['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols['mtch_num'] = $tmp5['mtch_num'];
					$where = "mtch_id=".$tmp3['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols = array();
					$cols['tie_schedule'] = $tmp0['tie_schedule'];
					$cols['tie_place'] = $tmp0['tie_place'];
					$cols['tie_court'] = $tmp0['tie_court'];
					$where = "tie_id=".$tmp2['tie_id'];
					$db->update('ties', $cols, $where);

					$cols['tie_schedule'] = $tmp2['tie_schedule'];
					$cols['tie_place'] = $tmp2['tie_place'];
					$cols['tie_court'] = $tmp2['tie_court'];
					$where = "tie_id=".$tmp0['tie_id'];
					$db->update('ties', $cols, $where);

					$cols['tie_schedule'] = $tmp3['tie_schedule'];
					$cols['tie_place'] = $tmp3['tie_place'];
					$cols['tie_court'] = $tmp3['tie_court'];
					$where = "tie_id=".$tmp5['tie_id'];
					$db->update('ties', $cols, $where);

					$cols['tie_schedule'] = $tmp5['tie_schedule'];
					$cols['tie_place'] = $tmp5['tie_place'];
					$cols['tie_court'] = $tmp5['tie_court'];
					$where = "tie_id=".$tmp3['tie_id'];
					$db->update('ties', $cols, $where);
				}
				else
				echo "__FILE__.' ('.__LINE__.') :".
		  "Ca etre bizarre!!!";
			}
		}

		// Pour une poule de quatre, si c'est le deuxieme match du premier tour
		// mettre a jour les numeros et horaires des quatres autres matches
		if ($data['rund_size'] == 4 &&
		$data['tie_posround'] == 4)
		{
			// Si le vainqueur est la quatrieme paire, il faut
			// inverser numero et heure des quatres autres matches
			// Position du vainqueur
			$where = "t2r_pairId=$winPairId";
			$res = $db->select('t2r', 't2r_posRound', $where);
			$tmp = $res->fetchRow(MDB2_FETCHMODE_ASSOC);

			//il est en troisieme position
			if( ($oldWinner == -1 && $tmp['t2r_posround'] == 4) ||
			($oldWinner != -1 && $oldWinner != $winPairId))
			{
				// recherche des autres matchs de la poule
				$fields = array('tie_schedule', 'tie_place', 'tie_court',
			      'mtch_num', 'tie_id', 'mtch_id');
				$tables = array('ties', 'matchs');
				$where = "tie_id = mtch_tieId".
		" AND tie_roundId =". $data['tie_roundid'].
		" AND tie_posround in (0,2,3,5)";
				$order = 'tie_posround';
				$res = $db->select($tables, $fields, $where, $order);

				// Il doit y avoir 4 match
				if ($res->numRows() == 4)
				{
					$tmp0 = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
					$tmp2 = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
					$tmp3 = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
					$tmp5 = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
					$cols = array();
					$cols['mtch_num'] = $tmp5['mtch_num'];
					$where = "mtch_id=".$tmp2['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols['mtch_num'] = $tmp2['mtch_num'];
					$where = "mtch_id=".$tmp5['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols['mtch_num'] = $tmp3['mtch_num'];
					$where = "mtch_id=".$tmp0['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols['mtch_num'] = $tmp0['mtch_num'];
					$where = "mtch_id=".$tmp3['mtch_id'];
					$db->update('matchs', $cols, $where);

					$cols = array();
					$cols['tie_schedule'] = $tmp5['tie_schedule'];
					$cols['tie_place'] = $tmp5['tie_place'];
					$cols['tie_court'] = $tmp5['tie_court'];
					$where = "tie_id=".$tmp2['tie_id'];
					$db->update('ties', $cols, $where);

					$cols['tie_schedule'] = $tmp2['tie_schedule'];
					$cols['tie_place'] = $tmp2['tie_place'];
					$cols['tie_court'] = $tmp2['tie_court'];
					$where = "tie_id=".$tmp5['tie_id'];
					$db->update('ties', $cols, $where);

					$cols['tie_schedule'] = $tmp3['tie_schedule'];
					$cols['tie_place'] = $tmp3['tie_place'];
					$cols['tie_court'] = $tmp3['tie_court'];
					$where = "tie_id=".$tmp0['tie_id'];
					$db->update('ties', $cols, $where);

					$cols['tie_schedule'] = $tmp0['tie_schedule'];
					$cols['tie_place'] = $tmp0['tie_place'];
					$cols['tie_court'] = $tmp0['tie_court'];
					$where = "tie_id=".$tmp3['tie_id'];
					$db->update('ties', $cols, $where);
				}
				else
				echo "__FILE__.' ('.__LINE__.') :".
		  "Ca etre bizarre!!!";
			}
		}

	}
	//}}}

	// {{{ _updateGroupRank
	/**
	* Calculate the ranking of each pairs in the given group
	*
	* @access private
	* @param  integer  $groupId   Id of the group
	* @return integer id of the team
	*/
	function _updateGroupRank($roundId)
	{
		$db =& $this->_db;

		// Get the point of the result of a tie
		$fields = array('rund_tieWin', 'rund_tieEqual', 'rund_tieLoose',
		      'rund_tieWO');
		$tables = array('rounds');
		$where = "rund_id = $roundId";
		$points = $db->selectFirst($tables, $fields, $where);

		// Calculer pour chaque paire les cumuls de
		// match gagnes, set gagnes et points marques
		$fields = array('p2m_pairId', 'p2m_result', 'mtch_score');
		$tables = array('p2m', 'matchs', 'ties');
		$where = "tie_roundId ='$roundId'".
	" AND tie_id = mtch_tieId".
	" AND mtch_id = p2m_matchId";
		$res = $db->select($tables, $fields, $where);
		$pairs = array();

		$uts = new utscore();
		while ($pair = $res->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			$pairId = $pair['p2m_pairid'];
			$uts->setScore($pair['mtch_score']);
			if (isset($pairs[$pairId]))
			$row = $pairs[$pairId];
			else
			{
				$row = array('pairId' => $pairId,
			   'matchW' => 0,
			   'matchL' => 0,
			   'gameW'  => 0,
			   'gameL'  => 0,
			   'pointW' => 0,
			   'pointL' => 0,
			   'points' => 0,
			   'point'  => 0,
			   'bonus'  => 0);
			}

			switch($pair['p2m_result'])
			{
				case WBS_RES_WINAB;
				case WBS_RES_WINWO:
				case WBS_RES_WIN:
					$row['matchW']++;
					$row['gameW'] += $uts->getNbWinGames();
					$row['gameL'] += $uts->getNbLoosGames();
					$row['pointW']+= $uts->getNbWinPoints();
					$row['pointL']+= $uts->getNbLoosPoints();;
					$row['points']++;
					$row['point'] += 100;
					break;
				case WBS_RES_LOOSEWO:
					$row['points']--;
					$row['point']-= 100;
					// No break here !!!
				case WBS_RES_LOOSEAB;
				case WBS_RES_LOOSE:
					$row['gameW'] += $uts->getNbLoosGames();
					$row['gameL'] += $uts->getNbWinGames();
					$row['pointW']+= $uts->getNbLoosPoints();
					$row['pointL']+= $uts->getNbWinPoints();
					break;
				default:
					break;
			}
			$pairs[$pairId] = $row;
		}
		$nbPairs = count($pairs);
		foreach($pairs as $pair)
		$rows[] = $pair;

		// S'il y  a plus de deux equipes a egalites
		// on regarde le nombre de sets gagnes/perdus
		if ($this->_checkEqual($rows, $roundId))
		{
			for ($i=0; $i<$nbPairs; $i++)
			{
				$ti =$rows[$i];
				for ($j=$i+1; $j<$nbPairs; $j++)
				{
					$tj =$rows[$j];
					if ($ti['point'] == $tj['point'])
					{
						if ($ti['gameW'] - $ti['gameL'] >
						$tj['gameW'] - $tj['gameL'])
						{
							$ti['bonus'] += 10;
							$rows[$i] = $ti;
						}
						if ($ti['gameW'] - $ti['gameL'] <
						$tj['gameW'] - $tj['gameL'])
						{
							$tj['bonus'] += 10;
							$rows[$j] = $tj;
						}
					} //end if team equal
				} //next $j
			} // next $i
		}

		// S'il y  a toujours plus de deux paires a egalites
		// on regarde le nombre de points gagnes/perdus
		if ($this->_checkEqual($rows, $roundId))
		{
			for ($i=0; $i<$nbPairs; $i++)
			{
				$ti =$rows[$i];
				for ($j=$i+1; $j<$nbPairs; $j++)
				{
					$tj =$rows[$j];
					if ($ti['point'] == $tj['point'])
					{
						if ($ti['pointW'] - $ti['pointL'] >
						$tj['pointW'] - $tj['pointL'])
						{
							$ti['bonus'] += 1;
							$rows[$i] = $ti;
						}
						if ($ti['pointW'] - $ti['pointL'] <
						$tj['pointW'] - $tj['pointL'])
						{
							$tj['bonus'] += 1;
							$rows[$j] = $tj;
						}
					} //end if team equal
				} //next $j
			} // next $i
		}
		$this->_checkEqual($rows, $roundId);
		// Calcul du classement de chaque equipe
		for ($i=0; $i<$nbPairs; $i++)
		{
			$ti =$rows[$i];
			$ti['rank']=1;
			for ($j=0; $j<$nbPairs; $j++)
			{
				$tj =$rows[$j];
				if ($tj['point'] > $ti['point'])
				{
					$ti['rank'] ++;
				}
				$rows[$j] = $tj;
			}
			$rows[$i] = $ti;
		}

		// Mise a jour de la base
		$fields = array();
		for ($i=0; $i<$nbPairs; $i++)
		{
			$ti =$rows[$i];
			$fields['t2r_rank']   = $ti['rank'];
			$fields['t2r_points'] = $ti['pointW'] - $ti['pointL'];
			$fields['t2r_tieW']   = $ti['gameW']-  $ti['gameL'];
			$fields['t2r_tieL']   = $ti['matchL'];
			$where = "t2r_pairId=".$ti['pairId'].
	    " AND t2r_roundId =".$roundId;
			$db->update('t2r', $fields, $where);
		}

		return;
	}
	// }}}

	// {{{ _checkEqual
	/**
	* Check if some team have the same score
	*
	* @access private
	* @param  integer  $groupId   Id of the group
	* @return integer id of the team
	*/
	function _checkEqual(&$rows, $roundId)
	{
		// Ajouter leur bonus aux paires
		$nbPairs = count($rows);
		for ($j=0; $j<$nbPairs; $j++)
		{
			$tj = $rows[$j];
			$tj['point'] += $tj['bonus'];
			$tj['bonus'] = 0;
			$rows[$j] = $tj;
		}


		$isEqual = false;
		// Pour chaque paire
		for ($i=0; $i<$nbPairs; $i++)
		{
			$ti =$rows[$i];
			$pairEqual = 0;
			$nbEqual   = 0;
			// Calculer les paires a egalite
			for ($j=0; $j<$nbPairs; $j++)
			{
				if ($i == $j) continue;
				$tj = $rows[$j];
				$rows[$j] = $tj;
				if ($ti['point'] == $tj['point'])
				{
					$pairEqual = $tj['pairId'];
					$nbEqual++;
				}
			}
			// S'il y en a une seule, trouver la gagnante
			if ($nbEqual == 1)
			{
				$winner = $this->_getWinPair($roundId, $ti['pairId'],
				$pairEqual);
				if (is_array($winner)) return $winner;
				if ($winner == $ti['pairId']) $ti['point'] += 0.1;
				if ($winner != $pairEqual) $isEqual = true;
				$rows[$i] = $ti;
			}
			if ($nbEqual > 1) $isEqual = true;
		}
		return $isEqual;
	}

	// {{{ _getWinPair
	/**
	* Return the winner team of a match
	*
	* @access private
	* @param  integer  $groupId  Id of the group
	* @param  integer  $team1    Id of the first teams
	* @param  integer  $team1    Id of the second team
	* @return integer id of the team
	*/
	function _getWinPair($roundId, $pair1, $pair2)
	{
		$db =& $this->_db;

		// Chercher les match des paires dans le groupes
		$fields = array('p2m_pairId', 'p2m_matchId', 'p2m_result');
		$tables = array('p2m', 'matchs', 'ties');
		$where = "tie_roundId=$roundId".
	" AND tie_id = mtch_tieId".
	" AND mtch_id = p2m_matchId".
	" AND p2m_result != ".WBS_RES_NOPLAY.
	" AND (p2m_pairId = $pair1 OR p2m_pairId = $pair2)";
		$order = 'mtch_id';
		$res = $db->select($tables, $fields, $where);

		// Trouver le match commun aux deux paires
		$winner = 0;
		while ($tmp  = $res->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			if (isset($matchs[$tmp['p2m_matchid']]))
			{
				if ($tmp['p2m_result'] == WBS_RES_WIN ||
				$tmp['p2m_result'] == WBS_RES_WINWO ||
				$tmp['p2m_result'] == WBS_RES_WINAB)
				$winner = $tmp['p2m_pairid'];
				else
				$winner = $matchs[$tmp['p2m_matchid']]['p2m_pairid'];

			}
			$matchs[$tmp['p2m_matchid']] = $tmp;
		}

		return $winner;
	}
	// }}}

	/**
	 * Met a jour le tableau pour la troisieme place s'il existe
	 */
	function _updateOtherKoTie($aPairId)
	{
		// Verifier que c'est une demi finale
		if ($this->getVal('posround') != 2 && $this->getVal('posround') != 1) return;

		// Le tableau pour la troisieme place existe-t-il
		$q = new Bn_query('rounds');
		$q->setFields('rund_id');
		$q->setWhere('rund_drawId =' . $this->getVal('drawid', -1));
		$q->addWhere('rund_type =' . OMATCH_ROUND_THIRD);
		$roundId = $q->getFirst();
		if (is_null($roundId)) return;

		// Position de la paire dans le match
		$pairPos = ($this->getVal('posRound')%2) ? OPAIR_POS_TOP:OPAIR_POS_BOTTOM;

		$this->updatePairToko($aPairId, 0, $pairPos, $roundId);
		unset($q);
		return;
	}

	/**
	 * Met une paire dans un tableau Ko
	 * @todo a deplacer dans l'objet round ou draw ?
	 * @param int   $aPairId	identifiant de la paire
	 * @param int	$aPosRound  position du match dans la tour (0=finale, 1 et 2 demi, 3 a 4 1/4..)
	 * @param int	$aPosPair  	position de la paire dans le match (OPAIR_POS_TOP ou OPAIR_POS_BOTTOM)
	 */
	public function updatePairToko($aPairId, $aPosRound, $aPosPair, $aRoundId)
	{

		// Chercher le match correspondant
		$q = new Bn_query('matchs, ties');
		$q->setFields('mtch_id, mtch_status');
		$q->setWhere('tie_id=mtch_tieId');
		$q->addWhere('tie_roundId=' . $aRoundId);
		$q->addWhere('tie_posRound=' . $aPosRound);
		$match = $q->getRow();

		if (!empty($match))
		{
			$matchId = $match['mtch_id'];
			$matchStatus = $match['mtch_status'];

			// Mettre jour la relation entre la paire et le match
			$q->setTables('p2m');
			$q->setFields('p2m_id');
			$q->setWhere('p2m_matchid=' . $matchId);
			$q->addWhere('p2m_posmatch=' . $aPosPair);
			$p2m = $q->getFirst();

			$q->addValue('p2m_pairId', $aPairId);
			$q->addValue('p2m_matchId', $matchId);
			$q->addValue('p2m_posMatch', $aPosPair);
			if (!empty($p2m)) $q->updateRow();
			else
			{
				$q->addValue('p2m_result', OPAIR_RES_NOPLAY);
				$q->addRow();
			}

			// Mettre a jour le status du match
			$q->setWhere('p2m_matchId=' . $matchId);
			$q->setFields('count(*)');
			$nb = $q->getFirst();

			$q->setTables('matchs');
			$q->setWhere('mtch_id=' . $matchId);
			// Le match est complet
			if ($nb == 2)
			{
				// Si le match n'est pas en cours ou termine, il est pret
				if ($matchStatus < OMATCH_STATUS_LIVE) $matchStatus = OMATCH_STATUS_READY;
			}
			// Une paire,le match est incomplet
			else if ($nb == 1) $matchStatus = OMATCH_STATUS_INCOMPLET;
			// Autre cas pas normal
			else Bn::trace('Omatch::_updateNextKoTie:0 ou plus de deux paires pour le match' . $matchId);
			$q->setValue('mtch_status', $matchStatus);
			$q->updateRow();
		}
	}


}
?>
