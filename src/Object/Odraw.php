<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
//require_once 'Odraw.inc';

class Odraw extends Object
{
	
	/**
	 * liste des phases du tableau
	 */
	public function getPhases()
	{
		$q = new Bn_query('rounds');
		$q->setFields('rund_group');
		$q->addWhere('rund_drawid='.$this->getVal('id', -1));
		return $q->getCol();
	}
	
	/**
	 * Suppression d'un draw
	 */
	public function delete()
	{
		$roundIds = $this->getRounds();
		foreach($roundIds as $roundId)
		{
			$oRound = new Oround($roundId);
			$oRound->delete();
			unset($oRound);	
		}
		
		$q = new Bn_query('draws');
		$q->deleteRow('draw_id=' . $this->getVal('id', -1));
	}
	
	/**
	 * Recuperer les rounds du tableau
	 *
	 * @return unknown
	 */
	public function getRounds()
	{
		$q = new Bn_query('rounds');
		$q->setFields('rund_id');
		$q->addWhere('rund_drawid=' . $this->getVal('id'));
		return $q->getCol();
	}
	
	/**
	 * Crée un tableau 
	 */
	public function create()
	{
		// Verification du tableau
		$eventId = $this->getVal('eventid');
		if (empty($eventId))
		{
			Bn::log('Odraw::save: EventId non renseigné.');
			return false;
		}
		$where = 'draw_eventid='. $eventId;
		$where.= " AND draw_name='" . $this->getVal('name') . "'"; 
		return $this->save($where);
	}
	
	/**
	 * Enregistre en bdd les donnees du tableau
	 */
	public function save($aWhere = null)
	{
		// Verification du tableau
		$eventId = $this->getVal('eventid');
		if (empty($eventId))
		{
			Bn::log('Odraw::save: EventId non renseigné.');
			return false;
		}

		// Clause de recherche
		$drawId = $this->getVal('id', -1);
		if ( empty($aWhere) && ($drawId >0) ) $where = 'draw_id=' . $drawId;
		else $where = $aWhere;

		// Enregistrer les données du groupe
		$drawId = $this->update('draws', 'draw_', $this->getValues(), $where);
		$uniId = $this->getVal('uniid');
		if (  empty($uniId) )
		{
			// Identifiant unique du groupe
			$where = 'draw_id=' . $drawId;
			$uniId = Bn::getUniId($drawId);
			$this->setVal('uniid', $uniId);
			$drawId = $this->update('draws', 'draw_', $this->getValues(), $where);
		}
		return $drawId;
	}
	
	/**
	 * Constructeur
	 */
	Public function __construct($aDrawId = -1)
	{
		if (strpos($aDrawId, ':') !== false) $where = "draw_uniid = '" . $aDrawId .";'";
		else $where = 'draw_id=' . $aDrawId;
		$this->load('draws', $where);
	}

	/**
	 * Creation d'un tableau
	 * @param array	$aData  liste des champs du tableau
	 * @return int id du nouveau tournoi
	 */
	public function newDraw($aData)
	{
		$validCols = array('eventid', 'name', 'serial', 'disci', 'discipline', 'catage', 'numcatage',
                        'type', 'stamp', 'nbgroupstep', 'nbkostep', 'nbmaxstep', 'nobefore', 'rankdefid',
                        'uniid');
		$q = new BN_query('draws');
		foreach( $aData as $key => $value)
		{
			if ( in_array($key, $validCols) )
			{
				$q->addValue('draw_'.$key, $value);
			}
		}
		$q->addWhere('draw_eventid=' . $aData['eventid']);
		$q->addWhere("draw_serial='" . $aData['serial'] ."'");
		$q->addWhere('draw_disci=' . $aData['disci']);
		$id = $q->replaceRow();
		if ( $q->isError() )
		{
			$this->_error($q->getMsg());
		}
		$uniId = Bn::getUniId($id);
		$q->setValue('draw_uniid', $uniId);
		$q->setWhere('draw_id=' . $id);
		$q->updateRow();
		
		$this->_drawId = $id;
		$this->setValues($aData);
		unset ($q);
		return $id;
	}
	
}
?>
