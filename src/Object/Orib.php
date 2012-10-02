<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
//require_once 'Orib.inc';

class Orib extends Object
{

	/**
	 * Constructeur
	 */
	Public function __construct($aRibId = -1)
	{
		if ($aRibId != -1)
		{
			$this->load('rib', 'rib_id=' . $aRibId, '_asso');
		}
	}

	/**
	 * Enregistre en bdd les donnees du rib
	 */
	public function save($aWhere = null)
	{
		if ( empty($aWhere) ) $where = 'rib_id=' . $this->getVal('id', -1);
		else $where = $aWhere;

		$id = $this->update('rib', 'rib_', $this->getValues(), $where, '_asso');
		return $id;
	}


}
?>
