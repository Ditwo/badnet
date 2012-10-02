<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
//require_once 'Odraw.inc';

class Odiv extends Object
{

	static public function getLov($aEventId)
	{
		$q = new BN_query('draws');
		$q->setFields('draw_id, draw_name');
		$q->addWhere('draw_eventid=' . $aEventId);
		$q->setOrder('draw_name');
		return($q->getLov());
	}
	
	/**
	 * Renvoi la listes des equipes de la division
	 *
	 */
	public function getTeams()
	{
		$q = new BN_query('t2r');
		$q->setFields('t2r_teamid');
		$q->addTable('rounds', 'rund_id=t2r_roundid');
		$q->addWhere('rund_drawid=' . $this->getVal('id', -1));
		$teamsIds = $q->getCol();
		return $teamsIds;
	}
	
	/**
	 * Renvoi la liste des groupes (ou rounds) de la division (ou tableau) 
	 *
	 */
	public function getGroups($aType = null){return $this->getRounds($aType);}
	public function getRounds($aType = null)
	{
		$q = new Bn_query('rounds');
		$q->setFields('rund_id');
		$q->setWhere('rund_drawid=' . $this->getVal('id', -1));
		$q->setOrder('rund_name');
		if (!empty($aType) ) $q->addWhere('rund_type=' . $aType);
		$roundIds = $q->getCol();
		unset($q);
		return($roundIds);
	}
	
	/**
	 * Constructeur
	 */
	Public function __construct($aDrawId = -1)
	{
		if ($aDrawId != -1)
		{
			if (strpos($aDrawId, ':') !== false) $where = "draw_uniid = '" . $aDrawId .";'";
			else $where = 'draw_id=' . $aDrawId;
			$this->load('draws', $where);
		}
	}

	/**
	 * Importe un groupe : verifie avant si une division avec le meme stamp
	 * n'existe pas deja pour le tournoi
	 */
	public function import()
	{
		$where = 'draw_eventid=' . $this->getVal('eventid', -1);
		$where .= " AND draw_stamp='" . $this->getVal('stamp', '') . "'";
		return $this->save($where);
	}
	
	/**
	 * Enregistre en bdd les donnees de la division
	 */
	public function save($aWhere = null)
	{
		if ( empty($aWhere) ) $where = 'draw_id=' . $this->getVal('id', -1);
		else $where = $aWhere;

		$id = $this->update('draws', 'draw_', $this->getValues(), $where);
		$uniId = $this->getVal('uniid');
		if (  empty($uniId) )
		{
			// Identifiant unique du tableau
			$where = 'draw_id=' . $id;
			$uniId = Bn::getUniId($id);
			$this->setVal('uniid', $uniId);
			$id = $this->update('draws', 'draw_', $this->getValues(), $where);
		}
		return $id;
	}


}
?>
