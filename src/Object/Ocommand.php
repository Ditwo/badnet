<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Ocommand.inc';
$locale = BN::getLocale();
require_once 'Locale/' . $locale . '/Ocommand.inc';


class Ocommand extends Object
{

	/**
	 * Renvoi les commandes d'une facture
	 *
	 * @param unknown_type $aBillId
	 * @return unknown
	 */
	static public function getCommandBill($aBillId)
	{
		$q = new Bn_query('commands', '_asso');
		$q->setFields('comd_id');
		$q->setWhere('comd_billid=' . $aBillId);
		$ids = $q->getCol();
		return $ids;
	}
	
	/**
	 * Constructeur
	 */
	public function __construct($aCommandId = -1)
	{
		if ( $aCommandId > 0)
		{
			$this->load('commands', 'comd_id=' . $aCommandId, '_asso');
			$this->setVal('labtype',  constant('LABEL_' . $this->getVal('type')));
		}
	}

	/**
	 * Renvoi les objets possibles en fonction du type de commande
	 *
	 * @param integer $aUserId utilisateur
	 * @param integer $aType type de la commande
	 * @param integer $aSeason  saison
	 * 	 * @return OCommand
	 */
	public function getLovObject($aUserId, $aType, $aSeason = null)
	{
		$q = new Bn_query('rights');
		// liste des associations
		if ($aType == OCOMMAND_TYPE_ADHESION)
		{
			$q->addTable('assocs', 'rght_themeId = asso_id');
			$q->addField('asso_id', 'value');
			$q->addField('asso_name', 'text');
			$q->addWhere('rght_userId ='. $aUserId);
			$q->addWhere('rght_theme =' . THEME_ASSOS);
			$q->addWhere("rght_status = '". AUTH_MANAGER."'");
			$q->setOrder = 'asso_name';
			$objs = $q->getLov();
		}
		// liste des tournois
		elseif ($aType == OCOMMAND_TYPE_TOURNAMENT)
		{
			$season =  Oseason::getCurrent();
			$q->addTable('events', 'rght_themeId = evnt_id');
			$q->addField('evnt_id', 'value');
			$q->addField('evnt_name', 'text');
			$q->addWhere('rght_userId='. $aUserId);
			$q->addWhere('rght_theme =' . THEME_EVENT);
			$q->addWhere('evnt_season =' . $season);
			//$q->addWhere("rght_status'". AUTH_MANAGER."'");
			$q->setOrder = 'evnt_name';
			$objs = $q->getLov();
		}
		// liste des circuits
		elseif ($aType == OCOMMAND_TYPE_CIRCUIT)
		$objs = array(-1 => "----");
		// liste des commande en ligne ???
		elseif ($aType == OCOMMAND_TYPE_INLINEREG)
		$objs = array(-1 => "----");
		// rien
		else
		$objs = array(-1 => "----");
		return $objs;
	}

	/**
	 * Liste de valeur des type de commande
	 * @return array
	 */
	public function getLovType()
	{
		for ($i=OCOMMAND_TYPE_NONE; $i<=OCOMMAND_TYPE_FORMATION; $i++)
		{
			$types[$i] = constant('LABEL_' . $i);
		}
		return $types;
	}

	/**
	 * Liste de valeur des moyens de paiement
	 * @return array
	 */
	public function getLovPaiement()
	{
		for ($i=OCOMMAND_PAYED_NONE; $i<=OCOMMAND_PAYED_VIREMENT; $i++)
		{
			$types[$i] = constant('LABEL_' . $i);
		}
		return $types;
	}

	/**
	 * enregistre en bdd la commande
	 */
	public function save()
	{
		// Enregistrer les donnees
		$where = 'comd_id=' . $this->getVal('id', -1);
		$id = $this->update('commands', 'comd_', $this->getValues(), $where, '_asso');
		return $id;
	}

	/**
	 * Supprime de la bdd la commande
	 */
	public function delete()
	{
		// Suppression de la commande
		$billId = $this->getVal('billid');
		$q = new BN_query('commands', '_asso');
		$q->deleteRow('comd_id=' . $this->getVal('id', -1));
		
		// Suppression de la facture associée
		$comdIds = Ocommand::getCommandBill($billId);
		if ( empty($comdIds) )
		{
			$oBill = new Obill($billId);
			$oBill->delete();
		}
		return;
	}

}
?>
