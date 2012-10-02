<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Oticknet.inc';


class Oticknet extends Object
{
	/**
	 * Constructeur
	 */
	public function __construct($aTicknetId = -1)
	{
		if ( $aTicknetId > 0)
		{
			$this->load('ticknets', 'tick_id=' . $aTicknetId, '_asso');
		}
	}

	/**
	 * Enregistre en bdd la commande
	 */
	public function save()
	{
		// Enregistrer les donnees
		$where = 'tick_id=' . $this->getVal('id', -1);
		$id = $this->update('ticknets', 'tick_', $this->getValues(), $where, '_asso');
		return $id;
	}

	/**
	 * Supprime de la bdd la commande
	 */
	public function delete()
	{
		$q = new BN_query('ticknets', '_asso');
		$q->deleteRow('tick_id=' . $this->getVal('id', -1));
		if ( $q->isError() )
		{
			$this->_error($q->getMsg());
		}
		return;
	}

}
?>
