<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
class Infoarea extends Bn_Balise
{
	/**
	 * Label de la zone
	 */
	var $_label = null;
	 
	/**
	 * Constructeur
	 *
	 */
	function __construct($aName, $aLabel, $aValue)
	{
		parent::__construct('div', 'div'.$aName, '', 'bn-info bn-nomodified');
		$this->addBalise('textarea', $aName, $aValue);
		$this->_label = new Bn_Balise('span', null, $this->_getLocale($aLabel));
		$this->_label->setAttribute('class', 'bn-label');
	}

	/**
	 * Affichage de
	 */
	function toHtml()
	{
		$l_s_lnEnd = $this->_getLineEnd();
		$l_l_id = $this->getAttribute('id');

		if ( ! empty($this->_label) )
		{
			$this->insertContent($this->_label);
		}
		$this->addBreak();
		return parent::toHtml();
	}
}
?>