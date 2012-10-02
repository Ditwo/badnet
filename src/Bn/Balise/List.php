<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

class Liste extends Bn_Balise
{
	private $_actions  = array();
	private $_texts    = array();
	private $_active   = '';
	private $_target   = '';
	
	/**
	 * Constructeur
	 * @param string	$aName		Identifiant de la liste
	 * @param array		$aTexts		Texte des items de la liste
	 * @param array		$aActions	Action des items de la liste
	 * @param integer	$aActive	item selectionne
	 * @return Bn_balise
	 */
	function __construct($aName, $aTexts, $aActions, $aActive, $aTarget=null)
	{
		parent::__construct('ul', $aName);
		$this->setAttribute('class', 'bnMenu');
		$this->_actions = $aActions;
		$this->_texts = $aTexts;
		$this->_active = $aActive;
		$this->_target = $aTarget;
	}

	/**
	 * construction de l achaine HTML de la liste
	 * @return string
	 */
	function toHtml()
	{
		$action = @reset($this->_actions);
		$numTab = 1;
		$id     = $this->getAttribute('id');
		$lnEnd = "\n"; //$this->getOption('linebreak');
		foreach($this->_texts as $text)
		{
			$item = new Bn_balise('li', 'li' . $id . $numTab);
			if ($numTab == $this->_active) $item->setAttribute('class', 'active');
			$lnk = $item->addLink('', $action, null, $this->_target);
			$spa = $lnk->addBalise('span', "$id$numTab", '');
			$spa->setAttribute('class', "menuImg");
			$spa = $lnk->addBalise('span', null, $text);
			$spa->setAttribute('class', "menuText");
			$this->addContent($item, false);
			$action = @next($this->_actions);
			$numTab++;
		}
		$html = '<div id="div' . $id . '">' . parent::toHtml() . '<p style="clear:both;margin:0;padding:0;"></p></div>';
		return $html;
	}

}
?>