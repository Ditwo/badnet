<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Oteam.inc';
require_once 'Omatch.inc';
require_once 'Otie.inc';
require_once 'Oregi.inc';

class Oteam extends Object
{
	/**
	 * retourne la position de l'equipes dans un round
	 *
	 * @param unknown_type $aroundId
	 */
	public function getPosRound($aRoundId)
	{
		$q =new Bn_query('t2r');
		$q->setFields('t2r_posround');
		$q->addWhere('t2r_teamid=' . $this->getVal('id', -1));
		$q->addWhere('t2r_roundid=' . $aRoundId);
		return $q->getFirst();
	}
	
	
	/**
	 * Constructeur
	 */
	Public function __construct($aTeamId=-1)
	{
		if ( $aTeamId > 0)
		{
			if (strpos($aTeamId, ':') !== false) $where = "team_uniid='" . $aTeamUniId . ";'";
			else $where = "team_id=" . $aTeamId;
			$this->load('teams', $where);
		}
	}

	/**
	 * Calcul du poids de l'equipe
	 */
	public function weight()
	{
		// nom du fichier utilise pourle calcul du poid de l'équipe
		$oEvent = new Oevent($this->getVal('eventid'));
		$fileName = '../Conf/Teams/'. $oEvent->getVal('teamweight');

		// Chargement du fichier
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->validateOnParse = true;
		$doc->load($fileName);

		$xpoids = $doc->getElementsByTagName('poid');
		foreach($xpoids as $xpoid)
		{
			$poid['points'] = $xpoid->getAttribute('points');
			$poid['clt'] = $this->nodeValue($xpoid, 'classement');
			$poid['rankmin'] = $this->nodeValue($xpoid, 'rang_min');
			$poid['rankmax'] = $this->nodeValue($xpoid, 'rang_max');
			$poids[] = $poid;
		}

		$xrules = $doc->getElementsByTagName('rules');
		foreach($xrules  as $xrule)
		{
			$discipline = $this->nodeValue($xrule, 'discipline');
		}
		$xjoueurs = $doc->getElementsByTagName('joueur');
		foreach($xjoueurs  as $xjoueur)
		{
			$nbHommes = $this->nodeValue($xjoueur, 'homme');
			$nbFemmes = $this->nodeValue($xjoueur, 'femme');
			$nbMixtes = $this->nodeValue($xjoueur, 'mixte');
		}

		// Selection des joueurs a prendre en compte
		if($nbMixte > 0)
		{
			$q = new Bn_query('members');
			$q->addTable('registration', 'regi_memberid=mber_id');
			$q->addTable('ranks', 'rank_regiid=regi_id');
			$q->addTable('rankdef', 'rkdf_id=rank_rankdefid');
			$q->addWhere('rank_discipline=' . $discipline);
			$q->addWhere('regi_teamid=' . $this->getVal('id'));
			$q->addWhere('regi_memberid > 0');
			$q->addField('regi_longname', 'name');
			$q->addField('rank_rank', 'rank');
			$q->addField('rkdf_label', 'clt');
			$q->setOrder('rank_rank');
			$q->setLimit(1, $nbMixtes);
			$players = $q->getRows();
		}
		else
		{
			$q = new Bn_query('members');
			$q->addTable('registration', 'regi_memberid=mber_id');
			$q->addTable('ranks', 'rank_regiid=regi_id');
			$q->addTable('rankdef', 'rkdf_id=rank_rankdefid');
			$q->addWhere('rank_discipline=' . $discipline);
			$q->addWhere('regi_teamid=' . $this->getVal('id'));
			$q->addWhere('regi_memberid > 0');
			$q->addWhere('mber_sexe=' . OMEMBER_GENDER_MALE);
			$q->addField('regi_longname', 'name');
			$q->addField('rank_rank', 'rank');
			$q->addField('rkdf_label', 'clt');
			$q->setOrder('rank_rank');
			$q->setLimit(1, $nbHommes);
			$players = $q->getRows();
			
			$q = new Bn_query('members');
			$q->addTable('registration', 'regi_memberid=mber_id');
			$q->addTable('ranks', 'rank_regiid=regi_id');
			$q->addTable('rankdef', 'rkdf_id=rank_rankdefid');
			$q->addWhere('rank_discipline=' . $discipline);
			$q->addWhere('regi_teamid=' . $this->getVal('id'));
			$q->addWhere('regi_memberid > 0');
			$q->addWhere('mber_sexe=' . OMEMBER_GENDER_FEMALE);
			$q->addField('regi_longname', 'name');
			$q->addField('rank_rank', 'rank');
			$q->addField('rkdf_label', 'clt');
			$q->setOrder('rank_rank');
			$q->setLimit(1, $nbFemmes);
			$players += $q->getRows();
		}
		// Calcul des points de l'equipe
		$points = 0;
		foreach($players as $player)
		{
			foreach($poids as $poid)
			{
				if ( ($poid['clt'] == $player['clt']) AND
				($poid['rankmin'] == 0 OR
				($player['rank'] >= $poid['rankmin'] AND
				$player['rank'] <= $poid['rankmax'] )))
				{
					$points += $poid['points'];
					break;
				}
			}
		}

		// Sauvegarde des points
		$this->setValue('poids', $points);
		$this->save();
		return $points;
	}

	/**
	 * Renvoi les groupes de l'equipe
	 *
	 */
	public function getRounds()
	{
		$q = new Bn_query('t2r');
		$q->setFields('t2r_roundid');
		$q->setWhere('t2r_teamid=' . $this->getVal('id', -1));
		$roundIds = $q->getCol();
		return $roundIds;
	}

	/**
	 * Renvoi l'association de l'equipe
	 *
	 * @return unknown
	 */
	public function getAssociation()
	{
		$q = new Bn_query('a2t');
		$q->setFields('a2t_assoid');
		$q->setWhere('a2t_teamid='. $this->getVal('id',-1));
		$assoId = $q->getFirst();
		$oAsso = new Oasso($assoId);
		unset($q);
		return $oAsso;
	}

	/**
	 * Sauvegarde des donnees de l'equipe
	 *
	 * @param string $aWhere
	 * @return integer id de l'equipe
	 */
	public function save($aWhere = null)
	{
		if ( empty($aWhere) ) $where = 'team_id=' . $this->getVal('id', -1);
		else $where = $aWhere;

		// Enregistrer les donnees
		$id = $this->update('teams', 'team_', $this->getValues(), $where);
		$uniId = $this->getVal('uniid');
		if (  empty($uniId) )
		{
			// Identifiant unique de l'association
			$where = 'team_id=' . $id;
			$uniId = Bn::getUniId($id);
			$this->setVal('uniid', $uniId);
			$id = $this->update('teams', 'team_', $this->getValues(), $where);
		}

		// Mettre a jour l'association
		$assoId = $this->getVal('assoid', -1);
		if ($assoId > 0)
		{
			$q = new Bn_query('a2t');
			$q->setValue('a2t_assoid', $assoId);
			$q->setWhere('a2t_teamid=' . $id);
			$q->replaceRow();
		}
		return $id;
	}

	/**
	 * Suppression d'une équipe avec controle des inscrits et engagement
	 * s'il y a des inscrits l'equipe n'est pas supprimée
	 *
	 */
	public function delete()
	{
		$teamId = $this->getVal('id', -1);
		$q = new Bn_query('registration');
		$q->addField('count(*)');
		$q->addWhere('regi_teamid=' . $teamId);
		$q->addWhere('regi_memberid>0');
		$nb = $q->getFirst();
		if ( $nb )
		{
			return false;
		}

		// Suppression de l'equipe
		$q->setTables('teams');
		$q->deleteRow('team_id='.$teamId);

		// Supression de la relation avec l'association
		$q->setTables('a2t');
		$q->deleteRow('a2t_teamid='.$teamId);

		// Suppression des relations dans les rencontres et tableaux
		// @todo mettre a jour les groupes et leur classement
		$q->setTables('t2r');
		$q->deleteRow('t2r_teamid='.$teamId);
		$q->setTables('t2t');
		$q->deleteRow('t2t_teamid='.$teamId);

		// Supression du compte s'il est vide
		// @todo verifier que le compte est vide. Utiliser l'objet compte (a creer)
		$q->setTables('accounts');
		$q->deleteRow('cunt_id='.$this->getVal('accounid', -1));

		return true;
	}

	/**
	 * envoi la liste des joueurs de l'equipe
	 *
	 * @param int $aGender  genre des joueurs (h ou F)
	 */
	public function getLovPlayers($aGender = null, $aDisci = OMATCH_DISCI_MS)
	{
		$q = new Bn_query('members, registration , ranks, rankdef');
		$q->setFields("regi_id, concat( regi_longname, ' - ', rkdf_label,' - ', rank_average,' - ', rank_rank)");
		$q->setWhere('regi_teamid=' . $this->getVal('id', -1));
		$q->addWhere('regi_memberid=mber_id');
		$q->addWhere('rank_regiid=regi_id');
		$q->addWhere('rank_rankdefid=rkdf_id');
		//$q->addWhere('mber_id > 0');
		if ($aDisci == OMATCH_DISCI_AS)
		{
			$q->addWhere('(rank_disci=' . OMATCH_DISCI_MS . ' OR rank_disci=' . OMATCH_DISCI_WS . ')');
		}
		else $q->addWhere('rank_disci=' . $aDisci);
		if ( !is_null($aGender)) $q->addWhere('mber_sexe=' . $aGender);
		$q->setOrder('rkdf_point DESC');
		$players = $q->getLov();
		$rows[-8] = '-----';
		$rows += $players;
		return $rows;
	}

	/**
	 * Envoi la liste des inscrits de l'equipe
	 *
	 * @param int $aType type d'inscription
	 */
	public function getRegis($aType = null)
	{
		$q = new Bn_query('registration');
		$q->setFields('regi_id');
		$q->setWhere('regi_teamid=' . $this->getVal('id', -1));
		if ( !empty($aType) )
		{
			$q->addWhere('regi_type=' .$aType);
		}
		$playerIds = $q->getCol();
		return $playerIds;
	}

	/**
	 * Importe une  equipe
	 */
	public function import()
	{
		$where = 'team_eventid=' . $this->getVal('eventid');
		$where .= " AND team_name='" . $this->getVal('name') . "'";
		$id = $this->save($where);
		$this->addWoPlayers();

		// Relation avec le club
		$q = new Bn_query('a2t');
		$q->addValue('a2t_assoid', $this->getVal('assoid'));
		$q->addValue('a2t_teamid', $id);
		$q->setWhere('a2t_assoid = ' . $this->getVal('assoid'));
		$q->addWhere('a2t_teamid = ' . $id);
		$q->replaceRow();
		unset($q);

		// Compte
		$q = new Bn_query('teams');
		$q->setFields('team_accountid');
		$q->setWhere('team_id='.$id);
		$accountId = $q->getFirst();
		if ( $accountId < 1 )
		{
			$qa = new Bn_query('accounts');
			$qa->addValue('cunt_name', $this->getVal('name'));
			$qa->addValue('cunt_eventid', $this->getVal('eventid', -1));
			$qa->addValue('cunt_status', 0);
			$qa->addValue('cunt_code', 'NONE');
			$accountId = $qa->addRow();

			$uniId = Bn::getUniId($accountId);
			$qa->addValue('cunt_uniid', $uniId);
			$qa->updateRow('cunt_id='.$accountId);

			$this->setVal('accountid', $accountId);
			$this->save();
		}
		return $id;
	}

	/**
	 * Creation d'une  equipe
	 */
	public function create()
	{
		$id = $this->save();
		$this->addWoPlayers();

		// Relation avec le club
		$q = new Bn_query('a2t');
		$q->addValue('a2t_assoid', $this->getVal('assoid'));
		$q->addValue('a2t_teamid', $id);
		$q->setWhere('a2t_assoid = ' . $this->getVal('assoid'));
		$q->addWhere('a2t_teamid = ' . $id);
		$q->replaceRow();
		unset($q);

		// Compte
		$q = new Bn_query('teams');
		$q->setFields('team_accountid');
		$q->setWhere('team_id='.$id);
		$accountId = $q->getFirst();
		if ( $accountId < 1 )
		{
			$qa = new Bn_query('accounts');
			$qa->addValue('cunt_name', $this->getVal('name'));
			$qa->addValue('cunt_eventid', $this->getVal('eventid', -1));
			$qa->addValue('cunt_status', 0);
			$qa->addValue('cunt_code', 'NONE');
			$accountId = $qa->addRow();

			$uniId = Bn::getUniId($accountId);
			$qa->addValue('cunt_uniid', $uniId);
			$qa->updateRow('cunt_id='.$accountId);

			$this->setVal('accountid', $accountId);
			$this->save();
		}
		return $id;
	}
	
	
	public function addWoPlayers()
	{
		$eventId = $this->getVal('eventid');
		$oEvent = new Oevent($eventId);
		if (!$oEvent->isIc() ) return;
		$teamId = $this->getVal('id');

		// Recuperer le classement NC
		$q = new bn_Query('events, rankdef');
		$q->setFields('rkdf_id');
		$q->setWhere("rkdf_label='NC'");
		$q->addWhere('evnt_id = ' . $eventId);
		$q->addWhere('evnt_ranksystem = rkdf_system');
		$rankNcId = $q->getFirst();

		// joueur WO homme
		$q->setTables('registration');
		$q->setValue('regi_eventid', $eventId);
		$q->addValue('regi_teamid', $teamId);
		$q->addValue('regi_memberid', -1);
		$q->addValue('regi_longname', '---WO---');
		$q->addValue('regi_shortname', '-WO-');
		$q->addValue('regi_type', OREGI_TYPE_PLAYER);
		$q->addValue('regi_date', date('Y-m-d'));
		$q->setWhere('regi_eventid=' . $eventId);
		$q->addWhere('regi_teamid=' . $teamId);
		$q->addWhere('regi_memberid=-1');
		$regiId = $q->replaceRow();
		$uniId = Bn::getUniId($regiId);
		$q->addValue('regi_uniid', $uniId);
		$q->updateRow();

		// Ajout des classements
		$q->setTables('ranks');
		$q->setValue('rank_regiid', $regiId);
		$q->addValue('rank_rankdefid', $rankNcId);
		$q->addValue('rank_disci', OMATCH_DISCI_MS);
		$q->addValue('rank_discipline', OMATCH_DISCIPLINE_SINGLE);
		$q->setWhere('rank_regiid=' . $regiId);
		$q->addWhere('rank_disci=' . OMATCH_DISCI_MS);
		$q->replaceRow();
		$q->setValue('rank_regiid', $regiId);
		$q->addValue('rank_rankdefid', $rankNcId);
		$q->addValue('rank_disci', OMATCH_DISCI_MD);
		$q->addValue('rank_discipline', OMATCH_DISCIPLINE_DOUBLE);
		$q->setWhere('rank_regiid=' . $regiId);
		$q->addWhere('rank_disci=' . OMATCH_DISCI_MD);
		$q->replaceRow();
		$q->setValue('rank_regiid', $regiId);
		$q->addValue('rank_rankdefid', $rankNcId);
		$q->addValue('rank_disci', OMATCH_DISCI_XD);
		$q->addValue('rank_discipline', OMATCH_DISCIPLINE_MIXED);
		$q->setWhere('rank_regiid=' . $regiId);
		$q->addWhere('rank_disci=' . OMATCH_DISCI_XD);
		$q->replaceRow();

		// joueur WO femme
		$q->setTables('registration');
		$q->setValue('regi_eventid', $eventId);
		$q->addValue('regi_teamid', $teamId);
		$q->addValue('regi_memberid', -1);
		$q->addValue('regi_longname', '---WO---');
		$q->addValue('regi_shortname', '-WO-');
		$q->addValue('regi_date', date('Y-m-d'));
		$q->addValue('regi_memberid', -2);
		$q->addValue('regi_type', OREGI_TYPE_PLAYER);
		$q->setWhere('regi_eventid=' . $eventId);
		$q->addWhere('regi_teamid=' . $teamId);
		$q->addWhere('regi_memberid=-2');
		$regiId = $q->replaceRow();
		$uniId = Bn::getUniId($regiId);
		$q->addValue('regi_uniid', $uniId);
		$q->updateRow();

		// Ajout des classements
		$q->setTables('ranks');
		$q->setValue('rank_regiid', $regiId);
		$q->addValue('rank_rankdefid', $rankNcId);
		$q->addValue('rank_disci', OMATCH_DISCI_WS);
		$q->addValue('rank_discipline', OMATCH_DISCIPLINE_SINGLE);
		$q->setWhere('rank_regiid=' . $regiId);
		$q->addWhere('rank_disci=' . OMATCH_DISCI_WS);
		$q->replaceRow();
		$q->setValue('rank_regiid', $regiId);
		$q->addValue('rank_rankdefid', $rankNcId);
		$q->addValue('rank_disci', OMATCH_DISCI_WD);
		$q->addValue('rank_discipline', OMATCH_DISCIPLINE_DOUBLE);
		$q->setWhere('rank_regiid=' . $regiId);
		$q->addWhere('rank_disci=' . OMATCH_DISCI_WD);
		$q->replaceRow();
		$q->setValue('rank_regiid', $regiId);
		$q->addValue('rank_rankdefid', $rankNcId);
		$q->addValue('rank_disci', OMATCH_DISCI_XD);
		$q->addValue('rank_discipline', OMATCH_DISCIPLINE_MIXED);
		$q->setWhere('rank_regiid=' . $regiId);
		$q->addWhere('rank_disci=' . OMATCH_DISCI_XD);
		$q->replaceRow();
	}

	/**
	 * Update the result of the teams for the designed tie
	 *
	 * @access private
	 * @param  integer  $aTieId   Id de la rencontre
	 */
	public function updateResult($aTieId, $woTeam=NULL, $woOpponent=NULL)
	{
		require_once 'Otie.inc';
		require_once 'Omatch.inc';
		require_once 'Opair.inc';
		$teamId = $this->getVal('id', -1);
		$tieId = $aTieId;
		$q = new Bn_query();

		// Recuperer les valeurs des penalites
		$q->setTables('t2t');
		$q->setFields('t2t_penalties, t2t_penaltieso');
		$q->setWhere('t2t_teamId=' .$teamId);
		$q->addWhere('t2t_tieId=' . $tieId);

		$data = $q->getRow();
		$penaltiesTeam = $data['t2t_penalties'];
		$penaltiesOpponent = $data['t2t_penaltieso'];

		// Recuperer les points de resultat de match
		$q->setTables('rounds, ties');
		$q->setFields('rund_matchwin, rund_matchloose, rund_matchwo, rund_matchrtd, rund_tieranktype, rund_tiematchdecisif, rund_tiematchnum');
		$q->setWhere('tie_id = ' . $tieId);
		$q->addWhere('tie_roundId = rund_id');
		$points = $q->getRow();

		// chercher tous les match de l'equipe dans cette rencontre
		$q->setTables('matchs, ties, p2m, i2p, registration');
		$q->setFields('DISTINCT mtch_id, p2m_result, mtch_score, mtch_discipline, mtch_rank, mtch_order');
		$q->setWhere('regi_teamId=' . $teamId);
		$q->addWhere('tie_id=' . $tieId);
		$q->addWhere('mtch_tieId = tie_id');
		$q->addWhere('mtch_id = p2m_matchId');
		$q->addWhere('p2m_pairId = i2p_pairId');
		$q->addWhere('i2p_regiId = regi_id');
		$q->addWhere('mtch_status >=' . OMATCH_STATUS_ENDED);
		$matchs = $q->getRows();
		//print_r($matchs);
		//print_r($points);

		// Calculate the points of the team
		$matchW=0;
		$matchL=0;
		$gameW=0;
		$gameL=0;
		$pointW=0;
		$pointL=0;
		$nbPointW=0;
		$nbPointL=0;
		$uts = new Oscore();
		foreach($matchs as $matchRes)
		{
			if ($matchRes['mtch_discipline'] == $points['rund_tiematchdecisif'] &&
			$matchRes['mtch_order'] == $points['rund_tiematchnum'])
			{
				$resultmatchdecisif = $matchRes["p2m_result"];
			}
			$uts->setScore($matchRes['mtch_score']);
			switch ($matchRes["p2m_result"])
			{
				case OPAIR_RES_WINWO:
				case OPAIR_RES_WIN:
				case OPAIR_RES_WINAB:
					$matchW++;
					$gameW += (int)$uts->getNbWinGames();
					$gameL += (int)$uts->getNbLoosGames();
					$pointW += (int)$uts->getNbWinPoints();
					$pointL += (int)$uts->getNbLoosPoints();
					$nbPointW += $points['rund_matchwin'];
					if ($matchRes["p2m_result"] == OPAIR_RES_WIN)
					$nbPointL += $points['rund_matchloose'];
					else if ($matchRes["p2m_result"] == OPAIR_RES_WINAB)
					$nbPointL += $points['rund_matchrtd'];
					else $nbPointL += $points['rund_matchwo'];
					break;

				case OPAIR_RES_LOOSEWO:
				case OPAIR_RES_LOOSE:
				case OPAIR_RES_LOOSEAB:
					$matchL++;
					$gameL += (int)$uts->getNbWinGames();
					$gameW += (int)$uts->getNbLoosGames();
					$pointL += (int)$uts->getNbWinPoints();
					$pointW += (int)$uts->getNbLoosPoints();
					$nbPointL += $points['rund_matchwin'];
					if ($matchRes["p2m_result"] == OPAIR_RES_LOOSE)
					$nbPointW += $points['rund_matchloose'];
					else if ($matchRes["p2m_result"] == OPAIR_RES_LOOSEAB)
					$nbPointW += $points['rund_matchrtd'];
					else $nbPointW += $points['rund_matchwo'];
					break;
			}
		}
		// Calculate the results of the tie
		$result= OTIE_RESULT_WIN;
		if ($penaltiesTeam != NULL)	$nbPointW += $penaltiesTeam;
		if ($penaltiesOpponent != NULL)	$nbPointL += $penaltiesOpponent;

		if (!is_null($woTeam) && is_null($woOpponent)) $result = OTIE_RESULT_LOOSEWO;
		else if (is_null($woTeam) && !is_null($woOpponent)) $result = OTIE_RESULT_WINWO;
		else if (!is_null($woTeam) && !is_null($woOpponent)) $result = OTIE_RESULT_EQUAL;
		else if (!$nbPointW && !$nbPointL) $result = OTIE_RESULT_NOTPLAY;
		else if ($nbPointW < $nbPointL) $result = OTIE_RESULT_LOOSE;
		else if ($nbPointW == $nbPointL)
		{
			// En cas d'egalite, voir l'option choisie
			// Egalite autorisee
			if ($points['rund_tieranktype'] == OTIE_CALC_EQUAL) $result = OTIE_RESULT_EQUAL;
			// Resultat fonction de la difference de match, jeu, points gagne
			else if ($points['rund_tieranktype'] == OTIE_CALC_RANK)
			{
				$result=OTIE_RESULT_EQUAL_PLUS;
				if ($matchW < $matchL) $result=OTIE_RESULT_EQUAL_MINUS;
				else if ($matchW == $matchL)
				{
					if ($gameW < $gameL) $result=OTIE_RESULT_EQUAL_MINUS;
					else if ($gameW == $gameL)
					{
						if ($pointW < $pointL) $result=OTIE_RESULT_EQUAL_MINUS;
						else if ($pointW == $pointL) $result=OTIE_RESULT_EQUAL;
					}
				}
			}
			// Resultat fonction d'un match particulier
			else
			{
				switch( $resultmatchdecisif )
				{
					case OMATCH_RESULT_WINWO:
					case OMATCH_RESULT_WIN:
					case OMATCH_RESULT_WINAB:
						$result=OTIE_RESULT_EQUAL_PLUS;
						break;
					default:
						$result=OTIE_RESULT_EQUAL_MINUS;
						break;
				}
			}
		}



		// Updating database
		$q->setTables('t2t');
		$q->setValue('t2t_matchW', $matchW);
		$q->addValue('t2t_matchL', $matchL);
		$q->addValue('t2t_setW', $gameW);
		$q->addValue('t2t_setL', $gameL);
		$q->addValue('t2t_pointW', $pointW);
		$q->addValue('t2t_pointL', $pointL);
		$q->addValue('t2t_scoreW', $nbPointW);
		$q->addValue('t2t_scoreL', $nbPointL);
		$q->addValue('t2t_result', $result);
		if (!is_null($penaltiesTeam)) $q->addValue('t2t_penalties', $penaltiesTeam);
		if (!is_null($penaltiesOpponent)) $q->addValue('t2t_penaltieso', $penaltiesOpponent);
		$q->setWhere('t2t_teamid=' . $teamId);
		$q->addWhere('t2t_tieid=' . $tieId);
		$q->updateRow();
		unset($q);
	}

	/**
	 * Calcule le classement d'un groupe
	 * @param  integer  $groupId   Id of the group
	 */
	public function updateGroupRank($aGroupId)
	{
		$groupId = $aGroupId;
		$q = new Bn_query();

		// Get the point of the result of a tie
		$q->setTables('rounds');
		$q->setFields('rund_tiewin, rund_tieequalplus, rund_tieequal,
		rund_tieequalminus, rund_tieloose, rund_tiewo, rund_ranktype');
		$q->setWhere('rund_id =' . $groupId);
		$points = $q->getRow();
		$rankType = $points['rund_ranktype'];

		// Calculer pour chaque equipe les cumuls de
		// match gagnes, set gagnes et points marques
		$q->setTables('t2t, ties, t2r');
		$q->setFields('t2r_penalties');
		$q->addField('t2t_teamId', 'teamid');
		$q->addField('sum(t2t_matchw) - sum(t2t_matchl)', 'deltamatch');
		$q->addField('sum(t2t_setw) -  sum(t2t_setl)', 'deltagame');
		$q->addField('sum(t2t_pointw) - sum(t2t_pointl)', 'deltapoint');
		$q->setWhere('tie_roundId =' . $groupId);
		$q->addWhere('t2t_tieId = tie_id');
		$q->addWhere('t2r_teamId = t2t_teamId');
		$q->addWhere('t2r_roundId =' . $groupId);
		$q->group('t2t_teamId');
		$q->setOrder('2');
		$teams = $q->getRows();

		// Calculer pour chaque equipe le nombre de rencontre gagnees
		// et le nombre de rencontre perdues
		$q->setTables('t2t, ties');
		$q->setFields('count(*)');
		$rows = array();
		foreach($teams as $team)
		{
			// Victoires
			$q->setWhere('tie_roundid =' . $groupId);
			$q->addWhere('t2t_tieid = tie_id');
			$q->addWhere('t2t_teamId=' . $team['teamid']);
			$q->addWhere('(t2t_result =' . OTIE_RESULT_WIN . ' OR t2t_result =' . OTIE_RESULT_WINWO .')');
			$team['tiew'] = $q->getFirst();

			// Defaites
			$q->setWhere('tie_roundid =' . $groupId);
			$q->addWhere('t2t_tieid = tie_id');
			$q->addWhere('t2t_teamId=' . $team['teamid']);
			$q->addWhere('t2t_result =' . OTIE_RESULT_LOOSE);
			$team['tiel'] = $q->getFirst();

			// Egalites
			$q->setWhere('tie_roundid =' . $groupId);
			$q->addWhere('t2t_tieid = tie_id');
			$q->addWhere('t2t_teamId=' . $team['teamid']);
			$q->addWhere('t2t_result =' . OTIE_RESULT_EQUAL);
			$team['tiee'] = $q->getFirst();

			// Defaite bonifie
			$q->setWhere('tie_roundid =' . $groupId);
			$q->addWhere('t2t_tieid = tie_id');
			$q->addWhere('t2t_teamId=' . $team['teamid']);
			$q->addWhere('t2t_result =' . OTIE_RESULT_EQUAL_MINUS);
			$team['tieem'] = $q->getFirst();

			// Victoire bonifiee
			$q->setWhere('tie_roundid =' . $groupId);
			$q->addWhere('t2t_tieid = tie_id');
			$q->addWhere('t2t_teamId=' . $team['teamid']);
			$q->addWhere('t2t_result =' . OTIE_RESULT_EQUAL_PLUS);
			$team['tieep'] = $q->getFirst();

			// Forfaits
			$q->setWhere('tie_roundid =' . $groupId);
			$q->addWhere('t2t_tieid = tie_id');
			$q->addWhere('t2t_teamId=' . $team['teamid']);
			$q->addWhere('t2t_result =' . OTIE_RESULT_LOOSEWO);
			$team['tiewo'] = $q->getFirst();

			$team['points'] = $team['tiew']*$points['rund_tiewin'];
			$team['points'] += $team['tieep']*$points['rund_tieequalplus'];
			$team['points'] += $team['tiee']*$points['rund_tieequal'];
			$team['points'] += $team['tieem']*$points['rund_tieequalminus'];
			$team['points'] += $team['tiel']*$points['rund_tieloose'];
			$team['points'] += $team['tiewo']*$points['rund_tiewo'];
			$team['points'] += $team['t2r_penalties'];
			$team['point'] = $team['points']*100;
			$team['bonus'] = 0;

			$team['tiel'] += $team['tiewo'];
			$rows[] = $team;
		}
		$nbTeams = count($rows);

		//$this->debug($rows);
		// S'il y  a plus de deux equipes a egalites
		// on regarde le nombre de matchs gagnes/perdus
		if (Oteam::_checkEqual($rows, $groupId, $rankType))
		{
			for ($i=0; $i<$nbTeams; $i++)
			{
				$ti =$rows[$i];
				for ($j=$i+1; $j<$nbTeams; $j++)
				{
					$tj =$rows[$j];
					if ($ti['point'] == $tj['point'])
					{
						if ($ti['deltamatch'] > $tj['deltamatch'])
						{
							$ti['bonus'] += 10;
							$rows[$i] = $ti;
						}
						if ($ti['deltamatch'] < $tj['deltamatch'])
						{
							$tj['bonus'] += 10;
							$rows[$j] = $tj;
						}
					} //end if team equal
				} //next $j
			} // next $i
		}

		//$this->debug($rows);
		// S'il y  a toujours plus de deux equipes a egalites
		// on regarde le nombre de sets gagnes/perdus
		if (Oteam::_checkEqual($rows, $groupId, $rankType))
		{
			for ($i=0; $i<$nbTeams; $i++)
			{
				$ti =$rows[$i];
				for ($j=$i+1; $j<$nbTeams; $j++)
				{
					$tj =$rows[$j];
					if ($ti['point'] == $tj['point'])
					{
						if ($ti['deltagame'] > $tj['deltagame'])
						{
							$ti['bonus'] += 1;
							$rows[$i] = $ti;
						}
						if ($ti['deltagame'] < $tj['deltagame'])
						{
							$tj['bonus'] += 1;
							$rows[$j] = $tj;
						}
					} //end if team equal
				} //next $j
			} // next $i
		}

		//$this->debug($rows);
		// S'il y  a toujours plus de deux equipes a egalites
		// on regarde le nombre de points gagnes/perdus
		if (Oteam::_checkEqual($rows, $groupId, $rankType))
		{
			for ($i=0; $i<$nbTeams; $i++)
			{
				$ti =$rows[$i];
				for ($j=$i+1; $j<$nbTeams; $j++)
				{
					$tj =$rows[$j];
					if ($ti['point'] == $tj['point'])
					{
						if ($ti['deltapoint'] > $tj['deltapoint'])
						{
							$ti['bonus'] += 0.1;
							$rows[$i] = $ti;
						}
						if ($ti['deltapoint'] < $tj['deltapoint'])
						{
							$tj['bonus'] += 0.1;
							$rows[$j] = $tj;
						}
					} //end if team equal
				} //next $j
			} // next $i
		}


		//$this->debug($rows);
		// Calcul du classement de chaque equipe
		for ($i=0; $i<$nbTeams; $i++)
		{
			$ti =$rows[$i];
			$ti['rank']=1;
			for ($j=0; $j<$nbTeams; $j++)
			{
				$tj =$rows[$j];
				if ($tj['point']+$tj['bonus'] > $ti['point']+$ti['bonus'])
				{
					$ti['rank'] ++;
				}
				$rows[$j] = $tj;
			}
			$rows[$i] = $ti;
		}

		//$this->debug($rows);
		// Mise a jour de la base
		$q->setTables('t2r');
		for ($i=0; $i<$nbTeams; $i++)
		{
			$ti =$rows[$i];
			$q->setValue('t2r_rank', $ti['rank']);
			$q->addValue('t2r_points', $ti['points']);
			$q->addValue('t2r_tieW', $ti['tiew']);
			$q->addValue('t2r_tieL', $ti['tiel']);
			$q->addValue('t2r_tieE', $ti['tiee']);
			$q->addValue('t2r_tieEP', $ti['tieep']);
			$q->addValue('t2r_tieEM', $ti['tieem']);
			$q->addValue('t2r_tieWO', $ti['tiewo']);
			$q->setWhere('t2r_teamId=' . $ti['teamid']);
			$q->addWhere('t2r_roundId =' .$groupId);
			$q->updateRow();
		}
		return;
	}

	/**
	 * Controle si deux  equipes  sont a egalite
	 *
	 * @param  integer  $groupId   Id of the group
	 * @return integer id of the team
	 */
	private function _checkEqual(&$rows, $groupId, $rankType)
	{
		$nbTeams = count($rows);
		for ($j=0; $j<$nbTeams; $j++)
		{
			$tj = $rows[$j];
			$tj['point'] += $tj['bonus'];
			$tj['bonus'] = 0;
			$rows[$j] = $tj;
		}
		if ($rankType == OTIE_CALC_RANK) return true;

		$isEqual = false;
		for ($i=0; $i<$nbTeams; $i++)
		{
			$ti =$rows[$i];
			$teamEqual = 0;
			$nbEqual   = 0;
			for ($j=0; $j<$nbTeams; $j++)
			{
				if ($i == $j) continue;
				$tj = $rows[$j];
				$tj['point'] += $tj['bonus'];
				$tj['bonus'] = 0;
				$rows[$j] = $tj;
				if ($ti['point'] == $tj['point'])
				{
					$teamEqual = $tj['teamid'];
					$nbEqual++;
				}
			}
			if ($nbEqual == 1)
			{
				$winner = $this->_getWinTeam($groupId, $ti['teamid'],
				$teamEqual);
				if (is_array($winner)) return $winner;
				if ($winner == $ti['teamid']) $ti['point'] += 0.01;
				$rows[$i] = $ti;
			}
			if ($nbEqual > 1) $isEqual = true;
		}
		return $isEqual;
	}

	/**
	 * Calcule le vainqueur d'une rencontre
	 *
	 * @param  integer  $groupId  Id of the group
	 * @param  integer  $team1    Id of the first teams
	 * @param  integer  $team1    Id of the second team
	 * @return integer id of the team
	 */
	private function _getWinTeam($groupId, $team1, $team2)
	{
		$q = new Bn_query('ties, t2t');
		// Search the ties of the first team
		$q->setFields('tie_id, t2t_result, t2t_matchW, t2t_matchL, t2t_setW, t2t_setL,
        t2t_pointW, t2t_pointL');
		$q->setWhere('tie_roundId =' . $groupId);
		$q->addWhere('t2t_teamId =' . $team1);
		$q->addWhere('t2t_tieId = tie_id');
		$tieIds = $q->getRows();
		$nbTies = count($tieIds);

		// Search the tie of the second team
		$q->setWhere('tie_roundId =' . $groupId);
		$q->addWhere('t2t_teamId =' . $team2);
		$q->addWhere('t2t_tieId = tie_id');
		$ties = $q->getRows();

		$nbTie = 0;
		$deltaTie   = 0;
		$deltaMatch = 0;
		$deltaGame  = 0;
		$deltaPoint = 0;

		foreach($ties as $tie)
		{
			for ($i=0; $i<$nbTies;$i++)
			{
				$tieId = $tieIds[$i];
				if ($tie['tie_id'] == $tieId['tie_id'])
				{
					$nbTie++;
					if ($tieId['t2t_result'] == OTIE_RESULT_WIN) $deltaTie++;
					if ($tieId['t2t_result'] == OTIE_RESULT_LOOSE) $deltaTie--;
					$deltaMatch += ($tieId['t2t_matchw'] - $tieId['t2t_matchl']);
					$deltaGame += ($tieId['t2t_setw'] - $tieId['t2t_setl']);
					$deltaPoint += ($tieId['t2t_pointw'] - $tieId['t2t_pointl']);
				}
			}
		}
		if ( $deltaTie > 0 ) return $team1;
		else if ($deltaTie < 0) return $team2;
		else if ($deltaMatch > 0) return $team1;
		else if ($deltaMatch < 0) return $team2;
		else if ($deltaGame > 0) return $team1;
		else if ($deltaGame < 0) return $team2;
		else if ($deltaPoint > 0) return $team1;
		else if ($deltaPoint < 0) return $team2;
		else return 0;
	}

	private function _debug($rows)
	{
		if (!is_array($rows))
		{
			echo "${$rows}=$rows<br>";
			return false;
	}

	$www = "<table border=\"1\">";
	$header = '';
	$lines  = '';
	foreach($rows as $row)
	{
		if (!is_array($row))
		$lines .= "<tr><td>${$row}</td><td>$row</td></tr>";
		else
		{
			$lines .= "<tr>";
			$header = "<tr>";
			foreach($row as $col=>$value)
			{
				$header .= "<td>$col</td>";
				$lines .= "<td>$value</td>";
			}
			$lines .= "</tr>";
			$header .= "</tr>";
		}
}
$www .= $header;
$www .= $lines;
$www .= "</table>";
echo $www;
}


}
?>
