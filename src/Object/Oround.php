<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Oround.inc';
require_once 'Otie.inc';

class Oround extends Object
{
	/**
	 * Suppression des tours de rondes de la phase
	 *
	 */
	public function deleteRonde()
	{
		// Supprimer les plateaux en trop
		$q = new Bn_query('rounds');
		$q->setFields('rund_id');
		$q->addWhere('rund_drawId=' . $this->getVal('drawid'));
		$q->addWhere('rund_type=' . OROUND_TYPE_RONDE);
		$q->addWhere("rund_group='" . $this->getVal('group') . "'");
		$roundIds = $q->getCol();
		foreach($roundIds as $roundId)
		{
			$oRound = new Oround($roundId);
			$oRound->delete();
			unset($oRound);
		}
	}
	
	/**
	 * Suppression de la rencontre pour la troisieme place
	 */
	function deleteThird()
	{

		// Supprimer la rencontre de la troisième place
		$q = new Bn_query('rounds');
		$q->setFields('rund_id');
		$q->addWhere('rund_drawid=' . $this->getVal('drawid'));
		$q->addWhere('rund_type='. OROUND_TYPE_THIRD);
		$q->addWhere("rund_group='". $this->getVal('group') . "'");
		$id = $q->getFirst();
		if ($id)
		{
			$oRound = new Oround($id);
			$oRound->delete();
			unset($oRound);
		}
	}

	/**
	 * Suppression des plateaux de la phase
	 *
	 */
	public function deletePlateau()
	{
		// Supprimer les plateaux en trop
		$q = new Bn_query('rounds');
		$q->setFields('rund_id');
		$q->addWhere('rund_drawId=' . $this->getVal('drawid'));
		$q->addWhere('rund_type=' . OROUND_TYPE_PLATEAU);
		$q->addWhere("rund_group='" . $this->getVal('group') . "'");
		$roundIds = $q->getCol();
		foreach($roundIds as $roundId)
		{
			$oRound = new Oround($roundId);
			$oRound->delete();
			unset($oRound);
		}
	}

	/**
	 * renvoi le nombre de tour
	 *
	 * @return unknown
	 */
	public function getNbTour()
	{
		$nbtour = $this->getVal('tour', null);
		if (is_null($nbtour))
		{ 
			$this->getFormule();
			$nbtour = $this->getVal('tour');
		}
		return $nbtour;
	}
	
	/**
	 * indique si le match de la troisieme place est present
	 *
	 * @return unknown
	 */
	public function isThird()
	{
		$third = $this->getVal('thirdplace', null);
		if (is_null($third))
		{ 
			$this->getFormule();
			$third = $this->getVal('thirdplace');
		}
		return $third;
	}
	
	
	/**
	 * Renvoi la formule de la phase du groupe
	 * La phase peut avoir une des formules
	 * OROUND_FORMULA_AR        Poules A/R
	 * OROUND_FORMULA_GROUP     Poules
	 * OROUND_FORMULA_GROUPTAB  Poules + tableau + option 3eme place
	 *                          Poules + tableau + plateau (NI)
	 *                          Poules + poules (NI)
	 * OROUND_FORMULA_KO        Tableau + option 3eme place
	 *
	 * OROUND_FORMULA_PLATEAU  Tableau + plateau
	 * OROUND_FORMULA_QUALIF   Qualif + tableau
	 * OROUND_FORMULA_PLAPOULE Tableau + plateau + poule (NI)
	 * OROUND_FORMULA_RONDE    Ronde suisse
	 *
	 */
	public function getFormule()
	{
		$formule = $this->getVal('formule', null);
		if (is_null($formule))
		{
			$q = new Bn_query('rounds');
			$q->setFields('rund_id, rund_type');
			$q->setWhere('rund_drawid=' . $this->getVal('drawid', -1));
			$q->addWhere("rund_group='" . $this->getVal('group') . "'");
			$rounds = $q->getRows();
			foreach($rounds as $round)
			{
				switch($round['rund_type'])
				{
					case OROUND_TYPEIC_GROUP:
					case OROUND_TYPE_GROUP:
						$this->_rounds[OROUND_TYPE_GROUP][] = $round['rund_id'];
						break;
					case OROUND_TYPEIC_AR:
					case OROUND_TYPE_AR:
						$this->_rounds[OROUND_TYPE_AR][] = $round['rund_id'];
						break;
					case OROUND_TYPEIC_KO:
					case OROUND_TYPE_MAINDRAW:
						$this->_rounds[OROUND_TYPE_MAINDRAW][] = $round['rund_id'];
						break;
					default:
						$this->_rounds[$round['rund_type']][] = $round['rund_id'];
						break;
				}
			}
			if (isset($this->_rounds[OROUND_TYPE_RONDE])) $formule = OROUND_FORMULA_RONDE;
			else if (isset($this->_rounds[OROUND_TYPE_QUALIF])) $formule = OROUND_FORMULA_QUALIF;
			else if (isset($this->_rounds[OROUND_TYPE_PLATEAU])) $formule = OROUND_FORMULA_PLATEAU;
			else if (isset($this->_rounds[OROUND_TYPE_MAINDRAW]))
			{
				if (isset($this->_rounds[OROUND_TYPE_AR]) ||
				isset($this->_rounds[OROUND_TYPE_GROUP])) $formule = OROUND_FORMULA_GROUPTAB;
				else $formule = OROUND_FORMULA_KO;
			}
			else if (isset($this->_rounds[OROUND_TYPE_GROUP])) $formule = OROUND_FORMULA_GROUP;
			else if(isset($this->_rounds[OROUND_TYPE_AR])) $formule = OROUND_FORMULA_AR;
			else $formule = 0;
			$this->setVal('formule', $formule);
			$this->setVal('thirdplace', isset($this->_rounds[OROUND_TYPE_THIRD]));
			$this->setVal('tour', count($this->_rounds[OROUND_TYPE_RONDE]));
		}
		return $formule;
	}


	/**
	 * Supprime completement le round et aussi les rencontres,
	 * les matchs, les relations avec les équipes
	 *
	 */
	public function delete()
	{
		// Supression des rencontres
		$tieIds = $this->getTies();
		foreach($tieIds as $tieId)
		{
			$oTie = new Otie($tieId);
			$oTie->delete();
			unset($oTie);
		}
		// Suppression des relations avec les equipes
		$q = new Bn_query('t2r');
		$q->deleteRow('t2r_roundid=' . $this->getVal('id', -1));

		// Suppression du groupe
		$q = new Bn_query('rounds');
		$q->deleteRow('rund_id=' . $this->getVal('id', -1));
		return;
	}

	/**
	 * Renvoi la liste des rencontres du groupe (ou round)
	 *
	 */
	public function getTies()
	{
		$q = new Bn_query('ties');
		$q->setFields('tie_id');
		$q->addWhere('tie_roundid=' . $this->getVal('id', -1));
		$q->setOrder('tie_step, tie_schedule');
		$teamIds = $q->getCol();
		unset($q);
		return($teamIds);
	}

	/**
	 * Renvoi la liste des equipes du groupe (ou round)
	 *
	 */
	public function getTeams($aOrderPosRound=false)
	{
		$q = new Bn_query('t2r');
		$q->addTable('teams', 't2r_teamid=team_id');
		$q->setFields('t2r_teamid');
		$q->addWhere('t2r_roundid=' . $this->getVal('id', -1));
		if ($aOrderPosRound) $q->setOrder('t2r_posround');
		else $q->setOrder('team_name');
		$teamIds = $q->getCol();
		unset($q);
		return($teamIds);
	}

	/**
	 * renvoi la division du groupe
	 *
	 * @return unknown
	 */
	public function getDivision()
	{
		return new Odiv($this->getVal('drawid', -1));
	}

	/**
	 * renvoi la tournoi du groupe
	 *
	 * @return unknown
	 */
	public function getEvent()
	{
		$oDiv = new Odiv($this->getVal('drawid', -1));
		$oEvent = new Oevent($oDiv->getVal('eventid', -1));
		unset ($oDiv);
		return $oEvent;
	}

	/**
	 * Constructeur
	 */
	Public function __construct($aRoundId = -1)
	{
		if ($aRoundId != -1)
		{
			if (strpos($aRoundId, ':') !== false) $where = "rund_uniid = '" . $aRoundId .";'";
			else $where = 'rund_id=' . $aRoundId;
			$this->load('rounds', $where);
		}
	}

	/**
	 * Enregistre en bdd les donnees
	 */
	public function save($aWhere = null)
	{
		if ( empty($aWhere) ) $where = 'rund_id=' . $this->getVal('id', -1);
		else $where = $aWhere;

		$id = $this->update('rounds', 'rund_', $this->getValues(), $where);
		$uniId = $this->getVal('uniid');
		if (  empty($uniId) )
		{
			// Identifiant unique du tableau
			$where = 'rund_id=' . $id;
			$uniId = Bn::getUniId($id);
			$this->setVal('uniid', $uniId);
			$id = $this->update('rounds', 'rund_', $this->getValues(), $where);
		}
		return $id;
	}

}
?>
