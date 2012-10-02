<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Oround.php';
//require_once 'Object/Oko.inc';
require_once 'Object/Otie.inc';
require_once 'Object/Omatch.inc';
$locale = BN::getLocale();

class Oko extends Oround
{

	private $_whereSeed = array();
	private $_whereBye  = array();
	private $_wherePos  = array();

	/**
	 * Enrgistrement des equipes du groupe
	 */
	public function updateTeams($aTeamIds)
	{
		$roundId = $this->getVal('id', -1);

		// Mise à jour des relations entre equipes et rund
		$posRound = 1;
		$q = new Bn_query('t2r');
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
		// pour le premier tour
		$size = $this->getVal('size');
		$start  = ($size/2)-1;
		$byes = $this->getByes();
		$byeTies = $this->getByeTies();
		
		$teamId = reset($aTeamIds);
		$q2 = new Bn_query('ties');
		$q2->setFields('tie_id');
		$q->setTables('t2t');
		
		// parcours des positions
		for($slot = 1; $slot <= $size; $slot++)
		{
			// Position de l'equipe dans la rencontre
			$teamPosTie = ($slot%2) ? OMATCH_TEAM_RECEIVER:OMATCH_TEAM_VISITOR;

			// Id de la rencontre du slot
			$tiePosRound = intval($start+($slot-1)/2);
			$q2->setWhere('tie_roundid=' . $roundId);
			$q2->addWhere('tie_posround=' . $tiePosRound);
			$tieId = $q2->getFirst();
			
			// Si ce n'est pas une place vacante, placement de l'equipe
			$q->setWhere('t2t_postie=' . $teamPosTie);
			$q->addWhere('t2t_tieid = ' . $tieId);
			if (! in_array($slot, $byes))
			{
				$q->setValue('t2t_teamid', $teamId);
				$q->addValue('t2t_tieid', $tieId);
				$q->addValue('t2t_postie', $teamPosTie);
				// la rencontre n'est pas un bye
				if( !in_array($tiePosRound, $byeTies))
				{
					$q->addValue('t2t_result', OTIE_RESULT_NOTPLAY);
					$q->replaceRow();
				}
				// la rencontre est un bye, mettre a jour le résultat de la rencontre
				// et mettre l'equipe a la rencontre suivante
				else
				{
					// Enregistrement de l'equipe dansla rencontre du premier tour
					$q->addValue('t2t_result', OTIE_RESULT_STEP);
					$q->replaceRow();

					// Id de la rencontre suivante
	 	 			$nextTiePosRound = intval(($tiePosRound-1)/2);
					$q2->setWhere('tie_roundid=' . $roundId);
					$q2->addWhere('tie_posround=' . $nextTiePosRound);
					$tieId = $q2->getFirst();
					
					// Relation de l'equipe avec la relation suivante
	  				$teamPosTie = ($tiePosRound%2) ? OMATCH_TEAM_RECEIVER:OMATCH_TEAM_VISITOR;
					$q->setWhere('t2t_postie=' . $teamPosTie);
					$q->addWhere('t2t_tieid = ' . $tieId);
					$q->setValue('t2t_teamid', $teamId);
					$q->addValue('t2t_tieid', $tieId);
					$q->addValue('t2t_postie', $teamPosTie);
					$q->addValue('t2t_result', OTIE_RESULT_NOTPLAY);
					$q->replaceRow();
				}

			}
			// Sinon Suppression de l'équipe présente a cette position
			else
			{
				$q->deleteRow();
			}
			$teamId = next($aTeamIds);
		}// fin des slots

		return true;
	}

	/**
	 * Constructeur
	 */
	Public function __construct($aRoundId = -1)
	{
		$isSquash = Bn::getConfigValue('squash', 'params');
		if ($isSquash)
		{
			$this->_whereSeed =
			array( 2 => array(1,2),
			4 => array(1,4,2,3),
			8 => array(1,8,3,6,2,7,4,5),
			16 => array(1,16,5,12,3,14,7,10,2,15,6,11,4,13,8,9),
			32 => array(1,32,9,25,5,13,20,28),
			64 => array(1,64,17,48,9,25,40,56,5,13,21,29,36,44,52,60),
			128 => array(1,128,33,96,17,49,80,112,9,25,41,57,72,88,104,120));

			$this->_whereBye =
			array( 2 => array(2),
			4 => array(2,3),
			8 => array(2,7,5,4),
			16 => array(2,15,11,6,8,9,13,4),
			32 => array(2,31,23,10,14,19,27,6,8,25,17,16,12,21,29,4),
			64 => array(2,63,47,18,26,39,55,10,14,51,35,30,22,43,59,6,
			8,57,41,24,32,33,49,16,12,53,37,28,20,45,61,4));

			$this->_wherePos =
			array( 2 => array(1,2),
			4 => array(1,4,3,2),
			8 => array(1,8,6,3,4,5,7,2,),
			16 => array(1,16,12,5,7,10,14,3,4,13,9,8,6,11,15,2),
			32 => array(1,32,24,9,13,20,28,5,7,26,18,15,11,22,30,3,
			4,29,21,12,16,17,25,8,6,27,19,14,10,23,31,2),
			64 => array(1,64,48,17,25,40,56,9,13,52,
			36,29,21,44,60,5,7,58,42,23,
			31,34,50,15,11,54,38,27,19,46,
			62,3,4,61,45,20,28,37,53,12,
			16,49,33,32,24,41,57,8,6,59,
			43,22,30,35,51,14,10,55,39,26,18,47,63,2));
		}
		else
		{
			$this->_whereSeed =
			array( 2 => array(1,2),
			4 => array(1,4,2,3),
			8 => array(1,8,3,6,5,4,2,7),
			16 => array(1,16, 5,12, 3,14,7,10, 2,15,6,11,4,13,8,9),
			32 => array(1, 32, 9, 24, 5, 28, 13, 20, 3,30,11,22,7,26,15,18),
			64 => array(1, 64, 17,48, 9,56,25,40, 5,13,21,29,36,44,52,60),
			128 => array(1,128,33,96,17,49,80,112,9,25,41,57,72,88,104,120));

			$this->_whereBye =
			array( 2 => array(2),
			4 => array(2,3),
			8 => array(2,7,4,5),
			16 => array(2,15,6,11,4,13,8,9),
			32 => array(2,31,10,23,6,27,14,19,4,29,12,21,8,25,16,17),
			64 => array(2,63,18,47,10,55,26,39,4,61,
			20,45,12,53,28,37,6,59,22,43,
			14,51,30,35,8,57,24,41,16,49,32,33));

			$this->_wherePos =
			array( 2 => array(1,2),
			4 => array(1,4,3,2),
			8 => array(1,8,3,6,5,4,7,2),
			16 => array(1,16,5,12,3,14,7,10,9,8,13,4,11,6,15,2),
			32 => array(1,32,9,24,5,28,13,20,3,30,11,22,7,26,15,18,
			17,16,25,8,21,12,29,4,19,14,27,6,23,10,31,2),

			64 => array(1, 64, 17,48, 9,56,25,40, 5,60,21,44,13,52,29,36,
			3,62,19,46,11,54,27,38,7,58,23,42,15,50,31,34,
			33,32,49,16,41,24,57,8,37,28,53,12,45,20,61,4,
			35,30,51,14,43,22,59, 6,39,26,55,10,47,18,63,2));
		}


		$nbTies = 0;
		if ($aRoundId != -1)
		{
			if (strpos($aRoundId, ':') !== false) $where = "rund_uniid = '" . $aRoundId .";'";
			else $where = 'rund_id=' . $aRoundId;
			$this->load('rounds', $where);

			// Nombre de rencontres du groupe
			$nbTies = $this->getNbTies();

		}
		$this->setVal('nbties', $nbTies);
	}

	/**
	 * Renvoi la taille du tableau en fonction du nombre d'inscrit
	 *
	 */
	public function getSize($aNb=null, $aType=null)
	{
		$entries = is_null($aNb) ? $this->getVal('entries') : $aNb;
		$type = is_null($aType) ? $this->getVal('type') : $aType;

		if ($entries === 1) $size = 1;
		elseif ($entries <= 2) $size = 2;
		elseif ($entries <= 4) $size = 4;
		elseif ($entries <= 8) $size = 8;
		elseif ($entries <= 16) $size= 16;
		elseif ($entries <= 32) $size = 32;
		elseif ($entries <= 64) $size = 64;
		else $size = 128;
		return $size;
	}


	/**
	 * Renvoi le nombre de rencontre pour le ko
	 *
	 */
	public function getNbTies($aSize=null, $aType=null)
	{
		$size = is_null($aSize) ? $this->getVal('size') : $aSize;
		$type = is_null($aType) ? $this->getVal('type') : $aType;
		$nbTies = 1;
		switch ($type)
		{
			case OROUND_TYPEIC_KO: // dans ce cas 'rund_qual' vaut 1
			case OROUND_TYPE_MAINDRAW: // dans ce cas 'rund_qual' vaut 1
			case OROUND_TYPE_PLATEAU: // dans ce cas 'rund_qual' vaut 1
			case OROUND_TYPE_CONSOL: // dans ce cas 'rund_qual' vaut 1
			case OROUND_TYPE_QUALIF: // dans ce cas 'rund_qual' vaut le nombre de qualifié
				$nbTies = $size - $this->getVal('qual', 1);
				if($nbTies==0) $nbTies = 1;
				break;
			case OROUND_TYPE_THIRD:
				$nbTies = 1;
				break;
			case OROUND_TYPE_RONDE:
				$nbTies = intval($this->getVal('entries')/2);
				break;
			case OROUND_TYPE_PROGRES:
				//@todo : traiter les tableaux progressifs
				$nbTies = 0;
				break;
		}
		if($nbTies == 0) $nbTies++;
		return $nbTies;
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
			Bn::log('Oko::save: DrawId non renseigné.');
			return false;
		}

		// Taille et nombre de rencontres du round
		// A faire dans l'ordre
		$size = $this->getSize();
		$this->setVal('size', $size);

		$nbTies = $this->getNbTies();
		$this->setVal('nbties', $nbTies);

		// Clause de recherche du round
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

		// Gestions des rencontres du round
		$type = $this->getVal('type');

		$oTie = new Otie();
		$oTie->setVal('roundid', $this->getVal('id'));
		$oTie->setVal('nbms', $this->getVal('nbms', 2));
		$oTie->setVal('nbws', $this->getVal('nbws', 1));
		$oTie->setVal('nbas', $this->getVal('nbas', 0));
		$oTie->setVal('nbmd', $this->getVal('nbmd', 1));
		$oTie->setVal('nbwd', $this->getVal('nbwd', 1));
		$oTie->setVal('nbad', $this->getVal('nbad', 0));
		$oTie->setVal('nbxd', $this->getVal('nbxd', 2));

		$byes = array();
		$needBye = ($type == OROUND_TYPEIC_KO) ||
		($type == OROUND_TYPE_MAINDRAW) ||
		($type == OROUND_TYPE_PLATEAU) ||
		($type == OROUND_TYPE_CONSOL) ||
		($type == OROUND_TYPE_QUALIF);
		if ($needBye)
		{
			$nbByes = $size - $this->getVal('entries');
			$this->setVal('byes', $nbByes);
			$byes = $this->getByeTies();
		}

		for ($i=0; $i<$nbTies; $i++)
		{
			$oTie->setVal('posround', $i);
			if ( $needBye AND in_array($i, $byes) ) $oTie->setVal('isbye', 1);
			else $oTie->setVal('isbye', 0);
			$oTie->setVal('step', $this->getStep($i));
			$where = 'tie_roundid=' . $this->getVal('id');
			$oTie->saveTie();
			$oTie->saveMatches();
		}

		// Supprimer les  rencontres en trop
		$q = new Bn_query('ties');
		$q->setFields('tie_id');
		$q->addWhere('tie_roundid=' . $roundId);
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
	 * Rencontre pour la troisieme place de la phase
	 */
	function saveThird()
	{
		$oKo = new Oko();
		$oKo->setVal('drawid', $this->getVal('drawid'));
		$oKo->setVal('group', $this->getVal('group'));
		$oKo->setVal('name', 'Troisième place');
		$oKo->setVal('stamp', '3eme');
		$oKo->setVal('rge', 3);
		$oKo->setVal('entries', 2);
		$oKo->setVal('type', OROUND_TYPE_THIRD);
		$oKo->setVal('size', 2);
		$oKo->setVal('matchwin', $this->getVal('matchwin', 1));
		$oKo->setVal('matchloose', $this->getVal('matchloose', 0));
		$oKo->setVal('matchwo', $this->getVal('matchwo', -1));
		$oKo->setVal('matchrtd', $this->getVal('matchrtd', 0));
		$oKo->setVal('nbms', $this->getVal('nbms', 2));
		$oKo->setVal('nbws', $this->getVal('nbws', 1));
		$oKo->setVal('nbas', $this->getVal('nbas', 0));
		$oKo->setVal('nbmd', $this->getVal('nbmd', 1));
		$oKo->setVal('nbwd', $this->getVal('nbwd', 1));
		$oKo->setVal('nbad', $this->getVal('nbad', 0));
		$oKo->setVal('nbxd', $this->getVal('nbxd', 2));
		$oKo->setVal('tiewin', $this->getVal('tiewin', 2));
		$oKo->setVal('tieequalplus', $this->getVal('tieequalplus', 0));
		$oKo->setVal('tieequal', $this->getVal('tieequal', 1));
		$oKo->setVal('tieequalminus', $this->getVal('tieequalminus', 0));
		$oKo->setVal('tieloose', $this->getVal('tieloose', 0));
		$oKo->setVal('tiewo', $this->getVal('tiewo', -1));
		$oKo->setVal('tieranktype', $this->getVal('tieranktype', OTIE_CALC_RANK));
		$oKo->setVal('tiematchdecisif', $this->getVal('tiematchdecisif', OMATCH_DISCI_MS));
		$oKo->setVal('tiematchnum', $this->getVal('tiematchnum', 1));

		$where = 'rund_drawid=' . $this->getVal('drawid')
		. ' AND rund_type='. OROUND_TYPE_THIRD
		. " AND rund_group='". $this->getVal('group') . "'";
		$roundId = $oKo->save($where);
		return $roundId;
	}

	/**
	 * Creation/mise a jour des plateaux de la phase
	 *
	 */
	function savePlateau()
	{
		$phase = $this->getVal('group');
		$drawId = $this->getVal('drawid');

		// requete pour mettre a jour les liens entre les perdants
		$q = new Bn_query('ties');

		// Initialisation
		$ancestors[$this->getVal('rge')] = $this;
		$ancestor = reset($ancestors);
		$step = $size = $this->getVal('size');
		$firstRge = $ancestor->getVal('rge') -1;

		$places = array(1=>array(-1),
		2=>range(1,2),
		4=>range(3,6),
		8=>range(7,14),
		16=>range(15,30),
		32=>range(31,62),
		64=>range(63,126));
		while ($step > 2)
		{
			$step /= 2;
			$isPair = 0;
			$ancestor = reset($ancestors);
			for ($i=1; $i<$size; $i+=$step)
			{
				if($isPair)
				{
					$nbPlayers = $ancestor->getVal('entries') - $step;
					if ($nbPlayers > 128) $nbSlot = 256;
					else if ($nbPlayers > 64) $nbSlot = 128;
					else if ($nbPlayers > 32) $nbSlot = 64;
					else if ($nbPlayers > 16) $nbSlot = 32;
					else if ($nbPlayers > 8) $nbSlot = 16;
					else if ($nbPlayers > 4) $nbSlot = 8;
					else if ($nbPlayers > 2) $nbSlot = 4;
					else if ($nbPlayers > 1) $nbSlot = 2;
					else if ($nbPlayers == 1) $nbSlot = 1;
					else $nbSlot = 0;
					if ($ancestor->getVal('entries') >= $step)
					{
						$ancestor->setVal('entries', $step);
						$ancestors[$ancestor->getVal('rge')] = $ancestor;
					}

					// Creer le plateau
					if ($nbSlot > 0)
					{
						$plateau = ($i + $firstRge) . ' - ' . ($i+ $firstRge+$nbPlayers-1);
						$oKo = new Oko();
						$oKo->setVal('drawid', $drawId);
						$oKo->setVal('group', $phase);
						$oKo->setVal('name', 'Plateau' . ' ' . $plateau);
						$oKo->setVal('stamp', $plateau);
						$oKo->setVal('rge', $i + $firstRge);
						$oKo->setVal('entries', $nbPlayers);
						$oKo->setVal('type', OROUND_TYPE_PLATEAU);
						$oKo->setVal('size', $nbSlot);
						$oKo->setVal('matchwin', $this->getVal('matchwin', 1));
						$oKo->setVal('matchloose', $this->getVal('matchloose', 0));
						$oKo->setVal('matchwo', $this->getVal('matchwo', -1));
						$oKo->setVal('matchrtd', $this->getVal('matchrtd', 0));
						$oKo->setVal('nbms', $this->getVal('nbms', 2));
						$oKo->setVal('nbws', $this->getVal('nbws', 1));
						$oKo->setVal('nbas', $this->getVal('nbas', 0));
						$oKo->setVal('nbmd', $this->getVal('nbmd', 1));
						$oKo->setVal('nbwd', $this->getVal('nbwd', 1));
						$oKo->setVal('nbad', $this->getVal('nbad', 0));
						$oKo->setVal('nbxd', $this->getVal('nbxd', 2));
						$oKo->setVal('tiewin', $this->getVal('tiewin', 2));
						$oKo->setVal('tieequalplus', $this->getVal('tieequalplus', 0));
						$oKo->setVal('tieequal', $this->getVal('tieequal', 1));
						$oKo->setVal('tieequalminus', $this->getVal('tieequalminus', 0));
						$oKo->setVal('tieloose', $this->getVal('tieloose', 0));
						$oKo->setVal('tiewo', $this->getVal('tiewo', -1));
						$oKo->setVal('tieranktype', $this->getVal('tieranktype', OTIE_CALC_RANK));
						$oKo->setVal('tiematchdecisif', $this->getVal('tiematchdecisif', OMATCH_DISCI_MS));
						$oKo->setVal('tiematchnum', $this->getVal('tiematchnum', 1));

						$where = 'rund_drawid=' . $drawId
						. ' AND rund_type='. OROUND_TYPE_PLATEAU
						. " AND rund_group='". $phase . "'"
						. " AND rund_stamp='" . $plateau . "'";
						$roundId = $oKo->save($where);
						$plateauIds[] = $roundId;

						// 	Mettre a jour le tableau des perdant des rencontres
						$q->setValue('tie_looserdrawid', $roundId);
						$q->setWhere('tie_roundid =' . $ancestor->getVal('id'));
						$q->addWhere('tie_posround IN (' . implode(',', $places[$step]) . ')');
						$q->updateRow();
						$tie['tie_looserdrawid'] = -1;
						$ancestors[$i+ $firstRge] = $oKo;
					}
					$ancestor = next($ancestors);
				}
				$isPair = 1 - $isPair;
			}
			ksort($ancestors);
		} // fin while

		// Supprimer les plateaux en trop
		$q->setTables('rounds');
		$q->setFields('rund_id');
		$q->addWhere('rund_drawId=' . $drawId);
		$q->addWhere('rund_type=' . OROUND_TYPE_PLATEAU);
		$q->addWhere("rund_group='" . $phase . "'");
		if (!empty($plateauIds)) $q->addWhere('rund_id NOT IN (' . implode(',', $plateauIds) . ')');
		$roundIds = $q->getCol();
		foreach($roundIds as $roundId)
		{
			$oRound = new Oround($roundId);
			$oRound->delete();
			unset($oRound);
		}

	}

	/**
	 * Creation/mise a jour des rondes de la phase
	 *
	 */
	function saveRonde($aNb)
	{
		$phase = $this->getVal('group');
		$drawId = $this->getVal('drawid');

		for ($i=1; $i<$aNb; $i++)
		{
			$tour = $i + 1;
			$plateau = 'Tour ' . $tour;
			$oKo = new Oko();
			$oKo->setVal('drawid', $drawId);
			$oKo->setVal('group', $phase);
			$oKo->setVal('name', $plateau);
			$oKo->setVal('stamp', $tour);
			$oKo->setVal('rge', $tour);
			$oKo->setVal('entries', $this->getVal('entries'));
			$oKo->setVal('type', OROUND_TYPE_RONDE);
			$oKo->setVal('size', $this->getVal('entries'));
			$oKo->setVal('matchwin', $this->getVal('matchwin', 1));
			$oKo->setVal('matchloose', $this->getVal('matchloose', 0));
			$oKo->setVal('matchwo', $this->getVal('matchwo', -1));
			$oKo->setVal('matchrtd', $this->getVal('matchrtd', 0));
			$oKo->setVal('nbms', $this->getVal('nbms', 2));
			$oKo->setVal('nbws', $this->getVal('nbws', 1));
			$oKo->setVal('nbas', $this->getVal('nbas', 0));
			$oKo->setVal('nbmd', $this->getVal('nbmd', 1));
			$oKo->setVal('nbwd', $this->getVal('nbwd', 1));
			$oKo->setVal('nbad', $this->getVal('nbad', 0));
			$oKo->setVal('nbxd', $this->getVal('nbxd', 2));
			$oKo->setVal('tiewin', $this->getVal('tiewin', 2));
			$oKo->setVal('tieequalplus', $this->getVal('tieequalplus', 0));
			$oKo->setVal('tieequal', $this->getVal('tieequal', 1));
			$oKo->setVal('tieequalminus', $this->getVal('tieequalminus', 0));
			$oKo->setVal('tieloose', $this->getVal('tieloose', 0));
			$oKo->setVal('tiewo', $this->getVal('tiewo', -1));
			$oKo->setVal('tieranktype', $this->getVal('tieranktype', OTIE_CALC_RANK));
			$oKo->setVal('tiematchdecisif', $this->getVal('tiematchdecisif', OMATCH_DISCI_MS));
			$oKo->setVal('tiematchnum', $this->getVal('tiematchnum', 1));

			$where = 'rund_drawid=' . $drawId
			. ' AND rund_type='. OROUND_TYPE_RONDE
			. " AND rund_group='". $phase . "'"
			. " AND rund_stamp='" . $tour . "'";
			$oKo->save($where);
		} // fin for

		// Supprimer les rondes en trop
		$q = new Bn_query('rounds');
		$q->setFields('rund_id');
		$q->addWhere('rund_drawId=' . $drawId);
		$q->addWhere('rund_type=' . OROUND_TYPE_RONDE);
		$q->addWhere("rund_group='" . $phase . "'");
		$q->addWhere('rund_stamp >' . $i);
		$roundIds = $q->getCol();
		foreach($roundIds as $roundId)
		{
			$oRound = new Oround($roundId);
			$oRound->delete();
			unset($oRound);
		}
	}

	public function getStep($aPosTie)
	{
		//@todo voir le step pour ronde suisse
		$posTie = $aPosTie;
		$size = $this->getVal('size');
		if ($posTie == 0) $tmp = 1;
		elseif (($posTie == 1) || ($posTie == 2)) $tmp = 2;
		else if (($posTie >=3) && ($posTie < 7)) $tmp = 3;
		else if (($posTie >= 7) && ($posTie < 15)) $tmp = 4;
		else if (($posTie >= 15) && ($posTie < 31)) $tmp = 5;
		else if (($posTie >= 31) && ($posTie < 63)) $tmp = 6;
		else $tmp = 7;
		$max = 1;
		//echo "$postie;$size;$tmp;";
		while(($size/=2) > 1) $max++;
		//echo "$max-----";
		return $max-$tmp;
	}

	/**
	 * Renvoi la position des rencontres avec un bye en haut ou en bas
	 */
	public function getByeTies()
	{
		// Calcul de la positon de la premiere rencontre
		$size = $this->getVal('size');
		$start = ($size/2)-1;
		$byes = $this->getByes();

		// parcour des positions des vacants
		$values = array();
		foreach ($byes as $bye)
		{
			// Calcul du numero de la rencontre
			$values[] = intval($start+($bye-1)/2);
		}
		return $values;
	}

	/**
	 * Renvoie les slots des vacants
	 */
	function getByes()
	{
		// Calcul de la positon de la premiere rencontre
		$size = $this->getVal('size');
		$entries = $this->getVal('entries');
		$nbByes = $size - $entries;
		$byes = $this->_whereBye[$size];
		return array_slice($byes, 0, $nbByes);
	}
	
	
	/**
	 * Renvoie les numeros des rencontres avec un vacant
	 * et la position du vacant dans la rencontre (haut ou bas)
	 */
	function getExpandedByes()
	{
		// Calcul de la positon de la premiere rencontre
		$size = $this->getVal('size');
		$start = ($size/2)-1;
		$values = array();
		$byes = $this->_whereBye[$size];

		// parcour des positons des vacants
		foreach($byes as $bye)
		{
			// Calcul du numero de la rencontre
			// et de la position du vacant dans la rencontre
			$ties['posInTie'] = ($bye%2) ? OMATCH_TEAM_RECEIVER:OMATCH_TEAM_VISITOR;
			$ties['numTie'] = intval($start+($bye-1)/2);
			$values[] = $ties;
		}

		return $values;
	}



}
?>
