<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Object/Omatch.inc';
require_once 'Object/Otie.inc';
$locale = BN::getLocale();
require_once 'Object/Locale/' . $locale . '/Otie.inc';
require_once 'Object/Locale/' . $locale . '/Ogroup.inc';

class Otie extends Object
{

	public function delete()
	{
		// Supprimer les matchs
		$q = new Bn_query('matchs');
		$q->setFields('mtch_id');
		$q->addWhere('mtch_tieid=' . $this->getVal('id', -1));
		$matchIds = $q->getCol();
		foreach($matchIds as $matchId)
		{
			$oMatch = new Omatch($matchId);
			$oMatch->delete();
			unset($oMatch);
		}

		// Supprimer les relations equipe/rencontres
		$q->setTables('t2t');
		$q->deleteRow('t2t_tieid=' . $this->getVal('id', -1));

		// Supprimer la rencontre
		$q->setTables('ties');
		$q->deleteRow('tie_id=' . $this->getVal('id', -1));
	}
	
	static public function getRankLov()
	{
		for ($i=OTIE_CALC_RANK; $i<=OTIE_CALC_EQUAL; $i++)
		$lov[$i] = 'LABEL_'.$i;
		return $lov;
	}
	
	/**
	 * Constructeur
	 */
	Public function __construct($aTieId = -1)
	{
		if ($aTieId != -1)
		{
			$this->load('ties', 'tie_id=' . $aTieId);
		}
	}

	public function getDivision()
	{
		$q = new Bn_query('ties, rounds');
		$q->setFields('rund_drawid');
		$q->addWhere('tie_roundid=rund_id');
		$q->addWhere('tie_id=' . $this->getVal('id', -1));
		$id = $q->getFirst();
		unset($q);
		return new Odiv($id);
	}
	
	public function getGroup()
	{
		return new Oround($this->getVal('roundid', -1));
	}
	
	public function getMatchs()
	{
		$q = new Bn_query('matchs');
		$q->addField('mtch_id');
		$q->addWhere('mtch_tieid=' . $this->getVal('id', -1));
		$q->setorder('mtch_rank');
		$ids = $q->getcol();
		unset($q);
		$oMatchs = array();
		foreach($ids as $id) $oMatchs[$id] = new Omatchteam($id);	
		return $oMatchs;
	}

	/**
	 * Enregistre en bdd les matches de la rencontre
	 */
	public function saveMatches()
	{
		// Men's singles
		$matchs['match_discipline'] = OMATCH_DISCI_MS;
		$matchs['match_disci'] = OMATCH_DISCIPLINE_SINGLE;
		$matchs['group_nbmatch'] = $this->getVal('nbms', -1);
		$matchs['group_order'] = 1;
		$matchs['group_rank'] = 1;
		$res = $this->_updateMatchs($matchs);

		// Women's singles
		$matchs['match_discipline'] = OMATCH_DISCI_WS;
		$matchs['match_disci'] = OMATCH_DISCIPLINE_SINGLE;
		$matchs['group_nbmatch'] = $this->getVal('nbws', -1);
		$matchs['group_order'] = 1;
		$matchs['group_rank'] += $this->getVal('nbms', -1);
		$res = $this->_updateMatchs($matchs);

		// Men's doubles
		$matchs['match_discipline'] = OMATCH_DISCI_MD;
		$matchs['match_disci'] = OMATCH_DISCIPLINE_DOUBLE;
		$matchs['group_nbmatch'] = $this->getVal('nbmd', -1);
		$matchs['group_order'] = 1;
		$matchs['group_rank'] += $this->getVal('nbws', -1);
		$res = $this->_updateMatchs($matchs);

		// Women's doubles
		$matchs['match_discipline'] = OMATCH_DISCI_WD;
		$matchs['match_disci'] = OMATCH_DISCIPLINE_DOUBLE;
		$matchs['group_nbmatch'] = $this->getVal('nbwd', -1);
		$matchs['group_order'] = 1;
		$matchs['group_rank'] += $this->getVal('nbmd', -1);
		$res = $this->_updateMatchs($matchs);

		// Mixed
		$matchs['match_discipline'] = OMATCH_DISCI_XD;
		$matchs['match_disci'] = OMATCH_DISCIPLINE_MIXED;
		$matchs['group_nbmatch'] = $this->getVal('nbxd', -1);
		$matchs['group_order'] = 1;
		$matchs['group_rank'] += $this->getVal('nbwd', -1);
		$res = $this->_updateMatchs($matchs);

		// Simple melangé
		$matchs['match_discipline'] = OMATCH_DISCI_AS;
		$matchs['match_disci'] = OMATCH_DISCIPLINE_SINGLE;
		$matchs['group_nbmatch'] = $this->getVal('nbas', -1);
		$matchs['group_order'] = 1;
		$matchs['group_rank'] += $this->getVal('nbxd', -1);
		$res = $this->_updateMatchs($matchs);

		// Double melangé
		$matchs['match_discipline'] = OMATCH_DISCI_AD;
		$matchs['match_disci'] = OMATCH_DISCIPLINE_DOUBLE;
		$matchs['group_nbmatch'] = $this->getVal('nbad', -1);
		$matchs['group_order'] = 1;
		$matchs['group_rank'] += $this->getVal('nbas', -1);
		$res = $this->_updateMatchs($matchs);
	}

	/**
	 * Update or create a matchs for a discipline
	 *
	 */
	function _updateMatchs($infos)
	{
		//Matchs existants
		$q = new Bn_query('matchs');
		$q->setFields('mtch_id');
		$q->addWhere('mtch_tieid=' . $this->getVal('id', -1));
		$q->addWhere('mtch_discipline=' . $infos['match_discipline']);
		$q->setOrder('mtch_order');
		$matchs = $q->getCol();

		// Mettre a jour ou supprimer les match existants
		$qp = new Bn_query('p2m');
		$q->setValue('mtch_tieid', $this->getVal('id', -1));
		$q->addValue('mtch_discipline', $infos['match_discipline']);
		$q->addValue('mtch_disci', $infos['match_disci']);
		
		foreach($matchs as $matchId)
		{
			if ($infos['group_nbmatch'])
			{
				$q->addValue('mtch_order', $infos['group_order']);
				$q->addValue('mtch_rank', $infos['group_rank']);
				$q->setWhere('mtch_id=' . $matchId);
				$q->updateRow();
				$infos['group_nbmatch']--;
				$infos['group_order']++;
				$infos['group_rank']++;
			}
			else
			{
				$q->deleteRow('mtch_id=' . $matchId);
				$qp->deleteRow('p2m_matchid=' . $matchId);
			}
		}

		// Creer les matchs necessaires
		$q->addValue('mtch_status', OMATCH_STATUS_INCOMPLET);
		for ($i=0; $i < $infos['group_nbmatch']; $i++)
		{
			$q->addValue('mtch_order', $infos['group_order']);
			$q->addValue('mtch_rank', $infos['group_rank']);
			$id = $q->addRow();

			$uniid = Bn::getUniId($id);
			$q->addValue('mtch_uniid', $uniid);
			$q->updateRow('mtch_id=' . $id);
			$infos['group_order']++;
			$infos['group_rank']++;
		}
		return true;
	}

	/**
	 * Enregistre en bdd les donnees de la rencontre
	 */
	public function saveTie()
	{
		$where = 'tie_roundid=' . $this->getVal('roundid');
		$where .= ' AND tie_posround=' . $this->getVal('posround');
		$this->delVal('id');
		$id = $this->save($where);
		return $id;
	}
	
	/**
	 * Enregistre en bdd les donnees de la rencontre
	 */
	public function save($aWhere = null)
	{
		if ( empty($aWhere) ) $where = 'tie_id=' . $this->getVal('id', -1);
		else $where = $aWhere;

		$id = $this->update('ties', 'tie_', $this->getValues(), $where);
		return $id;
	}

	public function getTeam($aPosition = OTIE_TEAM_RECEIVER)
	{
		$q = new Bn_query('t2t');
		$q->setFields('t2t_teamid');
		$q->addWhere('t2t_tieid=' . $this->getVal('id', -1));
		$q->addWhere('t2t_postie=' . $aPosition);
		$teamId = $q->getFirst();
		unset($q);
		return (new Oteam($teamId));
	}
}
?>
