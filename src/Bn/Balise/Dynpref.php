<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

class Dynpref extends Bn_Balise
{
	private $_active = 0;
	var $_scriptIndice = 0;
	
	/**
	 * Constructeur
	 * @return Bn_balise
	 */
	public function __construct($aId)
	{
		parent::__construct('div', $aId);
		$this->_menu = $this->addBalise('ul');
		$this->addDiv($aId . '_content');
		$script = "$('#" . $aId . "').bndynpref();";
		$this->_scriptIndice = $this->addJQReady($script, $this->_scriptIndice);
	}

	/**
	 * Ajout d'un item au menu
	 * @param $aAction  action pour remplir le noeud deplie
	 * @param $aContent contenu de l'item
	 * @param $aActive item actif 
	 * @return unknown_type
	 */
	public function addItem($aAction, $aContent, $aActive = false)
	{
		$id = $this->getAttribute('id');
		$li = $this->_menu->addBalise('li', 'bn-item-' . $aAction, $aContent);
		$li->setAction($aAction);
		if ($aActive)
		{
			$script = "$('#" . $id . "').bndynpref({'active':" . $aAction . "});";
			$this->_scriptIndice = $this->addJQReady($script, $this->_scriptIndice);
		}
		return $li;
	}

	public function toHtml()
	{
		return parent::toHtml();
	}
}
?>