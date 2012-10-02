<?php
/*****************************************************************************
!   $Id$
******************************************************************************/

class Info extends Bn_Balise
{
	/**
	 * Label de la zone
	 *
	 * @var       string
	 * @since     1.0
	 * @access    private
	 */
	var $_label = null;

	/**
	 * Constructeur
	 *
	 */
	function __construct($aName, $aLabel, $aValue)
	{
		parent::__construct('div', 'div'.$aName, '' , 'bn-info');
		$this->_label = new Bn_Balise('span', null, $this->_getLocale($aLabel));
		$this->_label->setAttribute('class', 'bn-label');
		$this->_input = $this->addBalise('p', $aName, $aValue);
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
			$this->_input->insertContent($this->_label);
		}
		$this->addBreak();
		return parent::toHtml();
	}
}
?>