<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

class Dynlist extends Bn_Balise
{
	
	/**
	 * Constructeur
	 * @param string	$aName		Identifiant de la liste
	 * @param array		$aTexts		Texte des items de la liste
	 * @param array		$aActions	Action des items de la liste
	 * @param integer	$aActive	item selectionne
	 * @return Bn_balise
	 */
	public function __construct($aName)
	{
		parent::__construct('ul', $aName);
		$this->setAttribute('class', 'bn-dynlist');
	}

	/**
	 * Ajout d'un item de type neoud (depliable) a la liste
	 * @param $aId		identifient de l'item
	 * @param $aAction  action pour remplir le noeud deplie
	 * @param $aContent contenu de l'item
	 * @return unknown_type
	 */
	public function addNode($aId, $aAction, $aContent)
	{
		$name = $this->getAttribute('id');
		$li = $this->addBalise('li', $name . '_' . $aId);
		$li->completeAttribute('class', 'bn-expandable');
		//$li->completeAttribute('class', 'collapsable');
		$div = $li->addDiv('', 'bn-node bn-expandable-node bn-unload');
		$div->addMetadata('ajax', true);
		$div->addMetadata('bnAction', $aAction);
		$div->addMetadata('id', $aId);
		//$div = $li->addDiv('', 'node collapsable-node');
		$li->addContent($aContent);
		$li->addBreak();
		$this->_lastItem = null;
		$this->_lastNode = $li;
		$this->_lastNodeDiv = $div;
		return $li;
	}

	/**
	 * Ajout d'un item simple (non depliable) a la liste
	 * @param $aId		Identifiant de l'item
	 * @param $aContent Contenu de l'item
	 * @return unknown_type
	 */
	public function addItem($aId, $aContent)
	{
		$name = $this->getAttribute('id');
		$li = $this->addBalise('li', $name . '_' . $aId);
		$div = $li->addDiv('', 'bn-item');
		$li->addContent($aContent);
		$li->addBreak();
		$this->_lastItem = $li;
		$this->_lastItemDiv = $div;
		$this->_lastNode = null;
		$this->_lastNodeDiv = null;
		return $li;
	}
	
	public function toHtml()
	{
		if ( !empty($this->_lastItem) ) $this->_lastItem->completeAttribute('class', 'bn-item-last');
		if ( !empty($this->_lastItemDiv) ) $this->_lastItemDiv->completeAttribute('class', 'bn-item-last');
		if ( !empty($this->_lastNode) ) $this->_lastNode->completeAttribute('class', 'bn-expandable-last');
		if ( !empty($this->_lastNodeDiv) ) $this->_lastNodeDiv->completeAttribute('class', 'bn-lastExpandable-node');
		//if ( !empty($this->_lastNode) ) $this->_lastNode->completeAttribute('class', 'bn-lastCollapsable');
		//if ( !empty($this->_lastNodeDiv) ) $this->_lastNodeDiv->completeAttribute('class', 'bn-lastCollapsable-node');
		return parent::toHtml();
	}
}
?>