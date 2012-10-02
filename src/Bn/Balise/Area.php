<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
class Area extends Bn_Balise
{
	/**
	 * Classe de la zone pour obligatoire ou non
	 *
	 * @var       string
	 * @since     1.0
	 * @access    private
	 */
	var $_class = 'bnmandatory';

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
		parent::__construct('div', 'div'.$aName);
		$this->setAttribute('class', 'bn-edit');

		$this->_label = new Bn_Balise('label', null, $this->_getLocale($aLabel));
		$this->_label->setAttribute('class', 'bn-label');
		$this->_label->setAttribute('for', $aName);

		$this->_input =& $this->addBalise('textarea', $aName);
		if (is_null($aValue))
		{
			$l_s_content = Bn::getValue($aName, '');
		}
		else
		{
			$l_s_content = $aValue;
		}
		$this->_input->addContent($l_s_content, false);

		$this->_input->setAttribute('name', $aName);
		$this->_input->setAttribute('class', 'bn-mandatory');
		$this->_jsControl[] = 'required:true';
	}
	/**
	 * Affichage de
	 */
	function toHtml()
	{
		// Ajout de la fonction javascript de control (jquery.validate)
		if ( count($this->_jsControl) )
		{
			$l_s_validate = '{validate : {' . implode(',', $this->_jsControl) . '}}';
			$this->_input->completeAttribute('class', $l_s_validate);
		}

		if ( ! empty($this->_label) )
		{
			$this->insertContent($this->_label);
		}
		$this->addBreak();
		return parent::toHtml();
	}

	/**
	 * La saisie est facultative
	 */
	function noMandatory()
	{
		$l_s_key = array_search('required:true', $this->_jsControl);
		unset($this->_jsControl[$l_s_key]);
		$this->_input->setAttribute('class', 'bn-option');
	}

	/**
	 * La saisie est inhibee
	 */
	function noModified()
	{
		$this->_input->setAttribute('readonly');
		$this->_input->setAttribute('class', 'bn-nomodified');
	}

	/**
	 * Fixe l'index de tabulation
	 */
	function setTabIndex($p_i_index)
	{
		$this->_input->setAttribute('tabindex', $p_i_index);
	}

	/**
	 * Renvoi un pointeur vers le label
	 */
	function &getLabel()
	{
		return $this->_label;
	}

	/**
	 * Renvoi un pointeur vers la szone de saisie
	 */
	function &getInput()
	{
		return $this->_input;
	}

}
?>