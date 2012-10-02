<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Oround.php';
require_once 'Object/Ogroup.inc';
require_once 'Object/Otie.inc';
$locale = BN::getLocale();
require_once 'Object/Locale/' . $locale . '/Ogroup.inc';

class Ogroup extends Oround
{
	/**
	 * Affichage du classement du groupe
	 *
	 * @param Bn_balise $aDiv div destination
	 */
	public function displayRanking(Bn_balise $aDiv)
	{
		$roundId =  $this->getVal('id', -1);
		$url = BADNETLIB_ROUND_FILL_RANKING .  '&roundId=' . $roundId;
		$grid = $aDiv->addGridview('gridRanking' . $roundId, $url, 50, false);
		$col = $grid->addColumn('#',   '#', false);
		$col->setLook(18, 'left', false);
		$col->initSort();
		$col = $grid->addColumn(LOC_ROUND_COLUMN_TEAM, 'team', false);
		$col->setLook(250, 'left', false);
		$col = $grid->addColumn(LOC_ROUND_COLUMN_POINTS, 'points', false);
		$col->setLook(30, 'left', false);
		$col->addOption('classes', "'bn-col'");
		$col = $grid->addColumn(LOC_ROUND_COLUMN_PENAL,  'p', false);
		$col->setLook(25, 'left', false);
		$col = $grid->addColumn(LOC_ROUND_COLUMN_TIEW,  'tiew', false);
		$col->setLook(25, 'left', false);
		if ($this->getVal('tieranktype') == OTIE_CALC_EQUAL)
		{
			$col = $grid->addColumn(LOC_ROUND_COLUMN_TIEE,  'tiee', false);
			$col->setLook(25, 'left', false);
		}
		else
		{
			$col = $grid->addColumn(LOC_ROUND_COLUMN_TIEEP,  'tiee', false);
			$col->setLook(25, 'left', false);
			$col = $grid->addColumn(LOC_ROUND_COLUMN_TIEEM,  'tiee', false);
			$col->setLook(25, 'left', false);
		}
		$col = $grid->addColumn(LOC_ROUND_COLUMN_TIEL,  'tiel', false);
		$col->setLook(25, 'center', false);
		$col = $grid->addColumn(LOC_ROUND_COLUMN_MATCHW,  'matchw', false);
		$col->setLook(50, 'center', false);
		$col = $grid->addColumn(LOC_ROUND_COLUMN_MATCHL,  'matchl', false);
		$col->setLook(50, 'center', false);
		$col = $grid->addColumn(LOC_ROUND_COLUMN_MATCHD,  'matchd', false);
		$col->setLook(52, 'center', false);
		$col->addOption('classes', "'bn-col'");
		$col = $grid->addColumn(LOC_ROUND_COLUMN_GAMEW,  'gamew', false);
		$col->setLook(42, 'center', false);
		$col = $grid->addColumn(LOC_ROUND_COLUMN_GAMEL,  'gamel', false);
		$col->setLook(42, 'center', false);
		$col = $grid->addColumn(LOC_ROUND_COLUMN_GAMED,  'gamed', false);
		$col->setLook(42, 'center', false);
		$col->addOption('classes', "'bn-col'");
		$oEvent = $this->getEvent();
		if ($oEvent->getVal('scoringsystem') != OEVENT_SCORING_1X5)
		{
			$col = $grid->addColumn(LOC_ROUND_COLUMN_POINTW,  'pointw', false);
			$col->setLook(40, 'center', false);
			$col = $grid->addColumn(LOC_ROUND_COLUMN_POINTL,  'pointl', false);
			$col->setLook(40, 'center', false);
			$col = $grid->addColumn(LOC_ROUND_COLUMN_POINTD,  'pointd', false);
			$col->setLook(40, 'center', false);
			$col->addOption('classes', "'bn-col'");
		}
		$Odiv = $this->getDivision();
		$str = $Odiv->getVal('name') . ' - ' . $this->getVal('name');
		$grid->setLook($str, 0, "'auto'");
		unset($Odiv);
	}

	/**
	 * Remplissage du classement du groupe
	 *
	 */
	public function fillRanking()
	{
		$q = new Bn_query('teams, t2t, ties, t2r');
		$q->setFields('t2r_rank, team_name, team_stamp, t2r_points, t2r_penalties, t2r_tiew, t2r_tiee, t2r_tiel, t2r_tieep, t2r_tieem');
		$q->addField('sum(t2t_matchw)', 'matchw');
		$q->addField('sum(t2t_matchl)',  'matchl');
		$q->addField('sum(t2t_matchw - t2t_matchl)',  'matchdelta');
		$q->addField('sum(t2t_setw)',  'gamew');
		$q->addField('sum(t2t_setl)',  'gamel');
		$q->addField('sum(t2t_setw - t2t_setL)', 'gamedelta');
		$q->addField('sum(t2t_pointw)',  'pointw');
		$q->addField('sum(t2t_pointl)',  'pointl');
		$q->addField('sum(t2t_pointw - t2t_pointl)', 'pointdelta');
		$q->addWhere('tie_roundId =' . $this->getVal('id', -1));
		$q->addWhere('team_id = t2t_teamId');
		$q->addWhere('t2t_tieId = tie_id');
		$q->addWhere('t2r_teamId = team_id');
		$q->addWhere('t2r_roundId =' . $this->getVal('id', -1));
		$q->group('team_id');
		$q->setOrder('t2r_rank');
		$teams = $q->getGridRows(0, true);
		$param  = $q->getGridParam();
		$i = $param->first;
		$gvRows = new GvRows($q);
		$oEvent = $this->getEvent();
		foreach($teams as $team)
		{
			$row = array();
			$row[] = $team['t2r_rank'];
			$row[] = $team['team_name'];
			$row[] = $team['t2r_points'];
			$row[] = $team['t2r_penalties'];
			$row[] = $team['t2r_tiew'];
			if ($this->getVal('tieranktype') == OTIE_CALC_EQUAL)
			{
				$row[] = $team['t2r_tiee'];
			}
			else
			{
				$row[] = $team['t2r_tieep'];
				$row[] = $team['t2r_tieem'];
			}
			$row[] = $team['t2r_tiel'];
			$row[] = $team['matchw'];
			$row[] = $team['matchl'];
			$row[] = $team['matchdelta'];
			if ($oEvent->getVal('scoringsystem') != OEVENT_SCORING_1X5)
			{
				$row[] = $team['gamew'];
				$row[] = $team['gamel'];
				$row[] = $team['gamedelta'];
				$row[] = $team['pointw'];
				$row[] = $team['pointl'];
				$row[] = $team['pointdelta'];
			}
			else
			{
				$row[] = $team['pointw'];
				$row[] = $team['pointl'];
				$row[] = $team['pointdelta'];
			}
			$gvRows->addRow($row);
		}

		$gvRows->display();
		return false;
	}

	/**
	 * Affichage du groupe
	 *
	 * @param Bn_balise $aDiv div destination
	 */
	public function displayGroup(Bn_balise $aDiv)
	{
		// Equipe du groupe
		$q = new Bn_query('t2r, teams');
		$q->setFields('team_stamp');
		$q->addWhere('t2r_teamid=team_id');
		$q->addWhere('t2r_roundid=' . $this->getVal('id', -1));
		$q->setOrder('t2r_posround');
		$teams = $q->getCol();
		// Calcul de la largeur disponible pour une equipe.
		// Pour une colonne rajouter 5 pixels pour tenir compte du padding et border
		$total = 775;
		$nbTeams = count($teams);
		$lgTeam = 210;
		$rest = 780 - (5*$nbTeams) - $lgTeam;
		$lgCol = $rest/$nbTeams;
		$url = BADNETLIB_ROUND_FILL_GROUP .  '&roundId=' . $this->getVal('id', -1);
		$grid = $aDiv->addGridview('gridGroup', $url, 50, false);
		$col = $grid->addColumn(LOC_ROUND_COLUMN_TEAM, 'team', false);
		$col->setLook($lgTeam, 'left', false);
		foreach($teams as $team)
		{
			$col = $grid->addColumn($team, $team, false);
			$col->setLook($lgCol, 'center', false);
		}
		$grid->setLook($this->getVal('name'), 0, "'auto'");
	}

	/**
	 * Remplissage du groupe
	 *
	 */
	public function fillGroup()
	{
		// Nombre d'equipe du groupe
		$q = new Bn_query('teams, t2r');
		$q->setFields('count(*)');
		$q->addWhere('team_id = t2r_teamid');
		$q->addWhere('t2r_roundid =' . $this->getVal('id', -1));
		$nbTeam = $q->getFirst();

		$q->setTables('teams, t2t, ties, t2r');
		$q->setFields("team_id, t2r_posround, tie_posround, team_name, team_stamp, t2t_scorew, t2t_scorel, t2t_result, tie_schedule");
		$q->addField("tie_posround % $nbTeam", 'sort');
		$q->addWhere('tie_roundId =' . $this->getVal('id', -1));
		$q->addWhere('team_id = t2t_teamid');
		$q->addWhere('t2t_tieid = tie_id');
		$q->addWhere('t2r_teamid = team_id');
		$q->setOrder('t2r_posround, sort');
		$teams = $q->getRows();
		$gvRows = new GvRows();
		$teamId = -1;
		$nb = 0;
		//echo "nb tie=" . count($teams) ."<br>";
		print_r($teams);
		foreach($teams as $team)
		{
			if ( $team['team_id'] != $teamId )
			{
				if( $teamId > 0) $gvRows->addRow($row);
				$teamId = $team['team_id'];
				$row = array();
				$nb=0;
				$row[] = $team['team_name'];
				//$row[] = '--';
			}
			if ($nb > $nbTeam) continue;
			if ( $team['t2t_result'] == OTIE_RESULT_NOTPLAY)
			$row[] = Bn::date($team['tie_schedule'], 'd-m-Y');
			else
			$row[] = $team['t2t_scorew'] . '-' . $team['t2t_scorel'];
			$nb++;
		}

		$gvRows->display();
		return false;
	}

	/**
	 * Ordonne les rencontres de facons a ce que les recontres aller
	 * soient au dessus de la digonale de la poule et les renonctres
	 * retour au dessous. Se base sur la date de la rencontre :
	 * La rencontre planifié le plus tot et la rencontre aller
	 *
	 */
	public function updateAr()
	{
		// Nombre d'equipes du groupe
		$q = new Bn_query('teams, t2r');
		$q->setFields('count(*)');
		$q->addWhere('team_id = t2r_teamid');
		$q->addWhere('t2r_roundid =' . $this->getVal('id', -1));
		$nbTeams = $q->getFirst();

		// Nombre de rencontre aller
		$nbTiesA = 0;
		for ($i=0; $i<$nbTeams;$i++) $nbTiesA += $i;

		// Rencontres du groupe
		$q->setTables('ties');
		$q->setFields('tie_id, tie_schedule, tie_posround');
		$q->setWhere('tie_roundid=' . $this->getVal('id', -1));
		$q->setOrder('tie_posround');
		$ties = $q->getRows();
		// S'il y a des rencontres retour (nombre de rencontre > nombre de rencontre aller)
		$nbTies = count($ties);
		if ($nbTies > $nbTiesA)
		{
			//Pour chaque rencontre aller
			for ($i = 0; $i < $nbTiesA; $i++)
			{
				// Si la date de la renocontre retour est inferieure
				// a la date de la reocntre aller, inverser les recontres dans le groupe
				$dateA = $ties[$i]['tie_schedule'];
				$dateR = $ties[$i+$nbTiesA]['tie_schedule'];
				if ($dateA > $dateR)
				{
					$q->setValue('tie_posround', $ties[$i]['tie_posround']);
					$q->setwhere('tie_id=' . $ties[$i+$nbTiesA]['tie_id']);
					$q->updateRow();
					$q->setValue('tie_posround', $ties[$i+$nbTiesA]['tie_posround']);
					$q->setwhere('tie_id=' . $ties[$i]['tie_id']);
					$q->updateRow();
				}
			}
		}

	}

	/**
	 * Enrgistrement des equipes du groupe
	 */
	public function updateTeams($aTeamIds)
	{
		$roundId = $this->getVal('id', -1);
		// Recuperer les rencontres avec les equipes actuelles
		$q = new Bn_query('ties');
		$q->addTable('t2t', 'th.t2t_tieid=tie_id AND th.t2t_postie='. OTIE_TEAM_RECEIVER, 'th');
		$q->addTable('t2t', 'tv.t2t_tieid=tie_id AND tv.t2t_postie='. OTIE_TEAM_VISITOR, 'tv');
		$q->addField('tie_id', 'tieid');
		$q->addField('th.t2t_teamid', 'teamhid');
		$q->addField('tv.t2t_teamid', 'teamvid');
		$q->addField('tie_posround', 'posround');
		$q->addWhere('tie_roundid=' . $roundId);
		$q->setOrder('tie_posround');
		$currentTies = $q->getRows();

		// Rencontres avec les nouvelles équipes
		$posRound = 0;
		$nbTeams = count($aTeamIds);
		$nbTies = $this->getVal('nbties');
		for($i=0; $i<$nbTies; $i++)
		$newTies[$i] = array('posround'=>$i,
				'teamhid' => -1,
				'teamvid' => -1
		);

		for( $i=0; $i<$nbTeams; $i++)
		{
			for($j=0; $j<$i; $j++)
			{
				$newTies[$posRound] = array('posround'=>$posRound,
				'teamhid' => $aTeamIds[$j],
				'teamvid' => $aTeamIds[$i]
				);
				$posRound++;
			}
		}

		if ($this->getVal('type') == OROUND_TYPEIC_AR)
		{
			$nbTies /= 2;
			for($i=0; $i<$posRound;$i++)
			{
				$newTies[$i+$nbTies] = array('posround'=>$i+$nbTies,
				'teamhid' => $newTies[$i]['teamvid'],
				'teamvid' => $newTies[$i]['teamhid']
				);
			}
		}

		// Mise à jour des relations entre equipes et groupe
		$posRound = 1;
		$q->setTables('t2r');
		foreach($aTeamIds as $teamId)
		{
			$q->setWhere('t2r_roundid=' . $roundId);
			$q->addWhere('t2r_posround=' . $posRound);
			$q->setValue('t2r_teamid', $teamId);
			$q->addValue('t2r_posround', $posRound);
			$q->addValue('t2r_roundid', $roundId);
			$q->replaceRow();
			$posRound++;
		}
		// Supression des relations en trop
		$q->setWhere('t2r_roundid=' . $roundId);
		$q->addWhere('t2r_posround>=' . $posRound);
		$q->deleteRow();

		// Mise à jour des relations entre equipes et rencontres
		// les rencontres identiques sont deplacees a leur nouvelle place
		$q->setTables('ties');
		$restTies = array();
		foreach($newTies as $tie)
		{
			$newPosRound = -1;
			// Recherche de la rencontre dans l'ancienne liste
			foreach($currentTies as $cTie)
			{
				if ( ($tie['teamvid'] ==  $cTie['teamvid']) &&
				($tie['teamhid'] ==  $cTie['teamhid']) )
				{
					$newPosRound = $tie['posround'];
					$oldPosRound = $cTie['posround'];
					$tieId = $cTie['tieid'];
					break;
				}
			}
			// La rencontre existe deja, il suffit de la changer de place dans la poule
			// On inverse les deux rencontres
			if ($newPosRound > -1)
			{
				$q->setWhere('tie_roundid=' . $roundId);
				$q->addWhere('tie_posround=' . $newPosRound);
				$q->setValue('tie_posround', $oldPosRound);
				$q->replaceRow();

				$q->setWhere('tie_id=' . $tieId);
				$q->setValue('tie_posround', $newPosRound);
				$q->replaceRow();
			}
			else $restTies[] = $tie;
		}

		// Traitement des rencontres restantes
		$q2 = new Bn_query('ties');
		$q2->setFields('tie_id');
		$q->setTables('t2t');
		foreach($restTies as $tie)
		{
			$posRound = $tie['posround'];
			$q2->setWhere('tie_roundid=' . $roundId);
			$q2->addWhere('tie_posround=' . $posRound);
			$tieId = $q2->getFirst();

			$q->setWhere('t2t_postie=' . OTIE_TEAM_RECEIVER);
			$q->addWhere('t2t_tieid ='. $tieId);
			if ($tie['teamhid'] > 0)
			{
				$q->setValue('t2t_teamid', $tie['teamhid']);
				$q->addValue('t2t_tieid', $tieId);
				$q->addValue('t2t_postie', OTIE_TEAM_RECEIVER);
				$q->replaceRow();
			}
			else $q->deleteRow();

			$q->setWhere('t2t_postie=' . OTIE_TEAM_VISITOR);
			$q->addWhere('t2t_tieid = ' . $tieId);
			if ($tie['teamvid'] > 0)
			{
				$q->setValue('t2t_teamid', $tie['teamvid']);
				$q->addValue('t2t_tieid', $tieId);
				$q->addValue('t2t_postie', OTIE_TEAM_VISITOR);
				$q->replaceRow();
			}
			else $q->deleteRow();
		}
		return true;
	}

	/**
	 * Constructeur
	 */
	Public function __construct($aRoundId = -1)
	{
		$nbTies = 0;
		if ($aRoundId != -1)
		{
			parent::__construct($aRoundId);

			// Nombre de rencontres du groupe
			$size  = $this->getVal('size');
			for ($i=0; $i<$size; $i++) $nbTies += $i;
			$type = $this->getVal('type');
			if ( $type == OROUND_TYPEIC_AR
			|| $type = OROUND_TYPE_AR) $nbTies *= 2;
		}
		$this->setVal('nbties', $nbTies);
	}

	/**
	 * Importe une division : verifie avant si la division n'existe pas
	 * deja pour le tournoi
	 */
	public function import()
	{
		$where = 'rund_drawid=' . $this->getVal('drawid', -1);
		$where .= " AND rund_stamp='" . $this->getVal('stamp', '') . "'";
		return $this->save($where);
	}

	/**
	 * Enregistre en bdd les donnees du groupe
	 */
	public function save($aWhere = null)
	{
		// Verification du tableau
		$drawId = $this->getVal('drawid');
		if (empty($drawId))
		{
			Bn::log('Ogroup::save: DrawId non renseigné.');
			return false;
		}

		// Clause de recherche
		if ( empty($aWhere) ) $where = 'rund_id=' . $this->getVal('id', -1);
		else $where = $aWhere;

		// Enregistrer les données du groupe
		$roundId = $this->update('rounds', 'rund_', $this->getValues(), $where);
		$uniId = $this->getVal('uniid');
		if (  empty($uniId) )
		{
			// Identifiant unique du groupe
			$where = 'rund_id=' . $roundId;
			$uniId = Bn::getUniId($roundId);
			$this->setVal('uniid', $uniId);
			$roundId = $this->update('rounds', 'rund_', $this->getValues(), $where);
		}

		// Mettre a jour les rencontres du groupe

		// Nombre de rencontres du groupe
		$size  = $this->getVal('entries');
		$this->setVal('size', $size);
		for ($i=0; $i<$size; $i++) $nbTies += $i;
		if ($this->getVal('type') == OROUND_TYPEIC_AR) $nbTies *= 2;
		$this->setVal('nbties', $nbTies);
		$step = array();
		$oTie = new Otie();
		$oTie->setVal('roundid', $this->getVal('id'));
		$oTie->setVal('isbye', 0);
		$oTie->setVal('nbms', $this->getVal('nbms', 2));
		$oTie->setVal('nbws', $this->getVal('nbws', 1));
		$oTie->setVal('nbas', $this->getVal('nbas', 0));
		$oTie->setVal('nbmd', $this->getVal('nbmd', 1));
		$oTie->setVal('nbwd', $this->getVal('nbwd', 1));
		$oTie->setVal('nbad', $this->getVal('nbad', 0));
		$oTie->setVal('nbxd', $this->getVal('nbxd', 2));
		for ($i=0; $i<$nbTies; $i++)
		{
			$oTie->setVal('posround', $i);
			$oTie->setVal('step', $step[$i]);
			$where = 'tie_roundid=' . $this->getVal('id');
			$oTie->saveTie();
			$oTie->saveMatches();
		}

		// Supprimer les  rencontres en trop
		$q = new Bn_query('ties');
		$q->setFields('tie_id');
		$q->addWhere('tie_roundid=' . $this->getVal('id', -1));
		$q->addWhere('tie_posround >=' . $nbTies);
		$tieIds = $q->getCol();
		foreach($tieIds as $tieId)
		{
			$oTie = new Otie($tieId);
			$oTie->delete();
			unset($oTie);
		}
		return $roundId;
	}

	/**
	 * Calcul du classement du groupe
	 */
	function updateTeamRanking()
	{
		$roundId = $this->getVal('id');

		// Calculer pour chaque equipe les cumuls de
		// match gagnes, set gagnes et points marques
		$q = new Bn_query('t2t');
		$q->addTable('ties', 't2t_tieid=tie_id');
		$q->addTable('t2r', 't2r_teamid=t2t_teamid');
		$q->addField('t2t_teamid', 'teamid');
		$q->addField('sum(t2t_matchw) - sum(t2t_matchl)', 'deltaMatch');
		$q->addField('sum(t2t_setw) - sum(t2t_setl)', 'deltaGame');
		$q->addField('sum(t2t_pointw) - sum(t2t_pointl)', 'deltaPoint');
		$q->addField('t2r_penalties', 'penalties');
		$q->addWhere('tie_roundid=' . $roundId);
		$q->group('t2t_teamid');
		$q->setOrder('t2t_teamid');
		$rows = $q->getRows();

		$q->setTables('t2t');
		$q->addTable('ties', 't2t_tieid=tie_id');
		$q->setFields('count(*)');
		foreach($rows as $team)
		{
			$q->setWhere('t2t_tieid=tie_id');
			$q->addWhere('tie_roundid=' . $roundId);
			$q->addWhere('t2t_teamid=' . $team['teamid']);
			$q->addWhere("(t2t_result =".OTIE_RESULT_WIN
			." OR t2t_result =".OTIE_RESULT_WINWO.')');
			$team['tieW'] = $q->getFirst();

			$q->setWhere('t2t_tieid=tie_id');
			$q->addWhere('tie_roundid=' . $roundId);
			$q->addWhere('t2t_teamid=' . $team['teamid']);
			$q->addWhere("t2t_result =".OTIE_RESULT_LOOSE);
			$team['tieL'] = $q->getFirst();

			$q->setWhere('t2t_tieid=tie_id');
			$q->addWhere('tie_roundid=' . $roundId);
			$q->addWhere('t2t_teamid=' . $team['teamid']);
			$q->addWhere("t2t_result =".OTIE_RESULT_EQUAL);
			$team['tieE'] = $q->getFirst();

			$q->setWhere('t2t_tieid=tie_id');
			$q->addWhere('tie_roundid=' . $roundId);
			$q->addWhere('t2t_teamid=' . $team['teamid']);
			$q->addWhere("t2t_result =".OTIE_RESULT_EQUAL_MINUS);
			$team['tieEM'] = $q->getFirst();

			$q->setWhere('t2t_tieid=tie_id');
			$q->addWhere('tie_roundid=' . $roundId);
			$q->addWhere('t2t_teamid=' . $team['teamid']);
			$q->addWhere("t2t_result =".OTIE_RESULT_EQUAL_PLUS);
			$team['tieEP'] = $q->getFirst();

			$q->setWhere('t2t_tieid=tie_id');
			$q->addWhere('tie_roundid=' . $roundId);
			$q->addWhere('t2t_teamid=' . $team['teamid']);
			$q->addWhere("t2t_result =".OTIE_RESULT_LOOSEWO);
			$team['tieWO'] = $q->getFirst();

			$nb = $this->getVal('tiewin', 0);
			$team['points'] = $team['tieW'] * $nb;
			$team['points'] += $team['tieEP'] * $this->getVal('tieequalplus');
			$team['points'] += $team['tieE'] * $this->getVal('tieequal');
			$team['points'] += $team['tieEM'] * $this->getVal('tieequalminus');
			$team['points'] += $team['tieL'] * $this->getVal('tieloose');
			$team['points'] += $team['tieWO'] * $this->getVal('tiewo');
			$team['points'] += $team['t2r_penalties'];
			$team['point'] = $team['points']*100;
			$team['bonus'] = 0;

			$team['tieL'] += $team['tieWO'];
			$teams[] = $team;
		}

		// S'il y  a plus de deux equipes a egalites
		// on regarde le nombre de matchs gagnes/perdus
		$nbTeams = count($teams);
		if ($this->_checkTeamEqual($teams))
		{
			for ($i=0; $i<$nbTeams; $i++)
			{
				$ti =$teams[$i];
				for ($j=$i+1; $j<$nbTeams; $j++)
				{
					$tj =$teams[$j];
					if ($ti['point'] == $tj['point'])
					{
						if ($ti['deltaMatch'] > $tj['deltaMatch'])
						{
							$ti['bonus'] += 10;
							$teams[$i] = $ti;
						}
						if ($ti['deltaMatch'] < $tj['deltaMatch'])
						{
							$tj['bonus'] += 10;
							$teams[$j] = $tj;
						}
					} //end if team equal
				} //next $j
			} // next $i
		}

		// S'il y  a toujours plus de deux equipes a egalites
		// on regarde le nombre de sets gagnes/perdus
		if ($this->_checkTeamEqual($teams))
		{
			for ($i=0; $i<$nbTeams; $i++)
			{
				$ti =$teams[$i];
				for ($j=$i+1; $j<$nbTeams; $j++)
				{
					$tj =$teams[$j];
					if ($ti['point'] == $tj['point'])
					{
						if ($ti['deltaGame'] > $tj['deltaGame'])
						{
							$ti['bonus'] += 1;
							$teams[$i] = $ti;
						}
						if ($ti['deltaGame'] < $tj['deltaGame'])
						{
							$tj['bonus'] += 1;
							$teams[$j] = $tj;
						}
					} //end if team equal
				} //next $j
			} // next $i
		}

		// S'il y  a toujours plus de deux equipes a egalites
		// on regarde le nombre de points gagnes/perdus
		if ($this->_checkTeamEqual($teams))
		{
			for ($i=0; $i<$nbTeams; $i++)
			{
				$ti =$teams[$i];
				for ($j=$i+1; $j<$nbTeams; $j++)
				{
					$tj =$teams[$j];
					if ($ti['point'] == $tj['point'])
					{
						if ($ti['deltaPoint'] > $tj['deltaPoint'])
						{
							$ti['bonus'] += 0.1;
							$teams[$i] = $ti;
						}
						if ($ti['deltaPoint'] < $tj['deltaPoint'])
						{
							$tj['bonus'] += 0.1;
							$teams[$j] = $tj;
						}
					} //end if team equal
				} //next $j
			} // next $i
		}

		// Calcul du classement de chaque equipe
		for ($i=0; $i<$nbTeams; $i++)
		{
			$ti =$teams[$i];
			$ti['rank']=0;
			for ($j=0; $j<$nbTeams; $j++)
			{
				$tj =$teams[$j];
				if ($tj['point']+$tj['bonus'] > $ti['point']+$ti['bonus'])
				{
					$ti['rank'] ++;
				}
				$teams[$j] = $tj;
			}
			$teams[$i] = $ti;
		}

		// Mise a jour de la base
		$q->setTables('t2r');
		for ($i=0; $i<$nbTeams; $i++)
		{
			$ti =$teams[$i];
			$q->setValue('t2r_rank', $ti['rank']);
			$q->addValue('t2r_points', $ti['points']);
			$q->addValue('t2r_tieW', $ti['tieW']);
			$q->addValue('t2r_tieL', $ti['tieL']);
			$q->addValue('t2r_tieE', $ti['tieE']);
			$q->addValue('t2r_tieEP', $ti['tieEP']);
			$q->addValue('t2r_tieEM', $ti['tieEM']);
			$q->addValue('t2r_tieWO', $ti['tieWO']);
			$q->setWhere('t2r_teamId=' . $ti['teamid']);
			$q->addWhere('t2r_roundId =' . $roundId);
			$q->updateRow();
		}
		return;
	}

	/**
	 * Check if some team have the same score
	 */
	function _checkTeamEqual(&$aTeams)
	{

		// Calculer les points de chaque equipe
		$nbTeams = count($aTeams);
		for ($j=0; $j<$nbTeams; $j++)
		{
			$tj = $aTeams[$j];
			$tj['point'] += $tj['bonus'];
			$tj['bonus'] = 0;
			$aTeams[$j] = $tj;
		}

		// Si classemet general rien de plus a faire
		$rankType = $this->getVal('ranktype');
		if ($rankType == OGROUP_RANK_CG) return true;

		// Departager les equipes a egalité 2 a 
		$isEqual = false;
		for ($i=0; $i<$nbTeams; $i++)
		{
			$ti =$aTeams[$i];
			$teamEqual = 0;
			$nbEqual   = 0;
			for ($j=0; $j<$nbTeams; $j++)
			{
				if ($i == $j) continue;
				$tj = $aTeams[$j];
				$tj['point'] += $tj['bonus'];
				$tj['bonus'] = 0;
				$aTeams[$j] = $tj;
				if ($ti['point'] == $tj['point'])
				{
					$teamEqual = $tj['teamId'];
					$nbEqual++;
				}
			}
			if ($nbEqual == 1)
			{
				$winner = $this->_getWinTeam($ti['teamId'], $teamEqual);
				if ($winner == $ti['teamId']) $ti['point'] += 0.01;
				$aTeams[$i] = $ti;
			}
			if ($nbEqual > 1) $isEqual = true;
		}
		return $isEqual;
	}

	/**
	 * Return the winner team of a tie
	 *
	 * @access private
	 * @param  integer  $aTeam1    Id of the first teams
	 * @param  integer  $aTeam2    Id of the second team
	 * @return integer id of the team
	 */
	function _getWinTeam($aTeam1, $aTeam2)
	{
		$roundId = $this->getVal('id');

		// Rencontres de la premiere equipe
		$q = new Bn_query('ties');
		$q->addTable('t2t', 't2t_tieId = tie_id');
		$q->setFields = array('tie_id, t2t_result, t2t_matchw, t2t_matchl,
        t2t_setw, t2t_setl, t2t_pointw, t2t_pointl');
		$q->addWhere('tie_roundId =' . $roundId);
		$q->addWhere('t2t_teamId =' . $aTeam1);
		$tie1s = $q->getRows();
			
		// Rencontres de la premiere equipe
		$q->setWhere('t2t_tieId = tie_id');
		$q->addWhere('tie_roundId =' . $roundId);
		$q->addWhere('t2t_teamId =' . $aTeam2);
		$tie2s = $q->getRows();
			
		$nbTies = count($tie1s);

		$nbTie = 0;
		$deltaTie   = 0;
		$deltaMatch = 0;
		$deltaGame  = 0;
		$deltaPoint = 0;

		foreach($tie2s as $tie2)
		{
			for ($i=0; $i<$nbTies;$i++)
			{
				$tie1 = $tie1s[$i];
				if ($tie2['tie_id'] == $tie1['tie_id'])
				{
					$nbTie++;
					if ($tie1['t2t_result'] == OTIE_RESULT_WIN ||
					$tie1['t2t_result'] == OTIE_RESULT_WINWO ||
					$tie1['t2t_result'] == OTIE_RESULT_EQUAL_PLUS) $deltaTie++;
					if ($tie1['t2t_result'] == OTIE_RESULT_LOOSE ||
					$tie1['t2t_result'] == OTIE_RESULT_LOOSEWO ||
					$tie1['t2t_result'] == OTIE_RESULT_EQUAL_MINUS ) $deltaTie--;
					$deltaMatch += ($tie1['t2t_matchw'] - $tie1['t2t_matchl']);
					$deltaGame += ($tie1['t2t_setw'] - $tie1['t2t_setl']);
					$deltaPoint += ($tie1['t2t_pointw'] - $tie1['t2t_pointl']);
				}
			}
		}
		if ( $deltaTie > 0 ) return $aTeam1;
		else if ($deltaTie < 0) return $aTeam2;
		else if ($deltaMatch > 0) return $aTeam1;
		else if ($deltaMatch < 0) return $aTeam2;
		else if ($deltaGame > 0) return $aTeam1;
		else if ($deltaGame < 0) return $aTeam2;
		else if ($deltaPoint > 0) return $aTeam1;
		else if ($deltaPoint < 0) return $aTeam2;
		else return 0;
	}

}
?>
