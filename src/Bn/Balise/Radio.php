<?php
/*****************************************************************************
!   $Id$
******************************************************************************/
require_once 'Input.php';

class Radio extends Bn_input
{
	
	/**
	 * Constructeur
	 *
	 */
	function __construct($aName, $aId, $aLabel, $aValue, $aChecked=false)
	{
		parent::__construct('div', 'div'.$aId);
		$this->setAttribute('class', 'bn-radio');
		
		$this->_input = $this->addBalise("input/", $aName);
		$this->_input->setAttribute("type", "radio");
		$this->_input->setAttribute("name", $aName);
		$this->_input->setAttribute("id", $aId);
		if ($aChecked)
		{
			$this->_input->setAttribute("checked", "checked");
		}
		$this->_input->setAttribute('value', $aValue);
		$this->_input->setAttribute('class', 'bn-mandatory');
		//$this->_jsControl[] = 'required:true';
		//$this->addValidateRule('required', 'true');
		if (!empty($aLabel))
		{
			$this->_label = new Bn_Balise('span');
			$label = $this->_label->addBalise('label', '', $aLabel); 
			$label->setAttribute('for', $aId);
		}
		$this->_isLabelLeft = false;
	}
	
	/**
	* La saisie est inhibee
	*/
	function noModified()
	{
		$this->_input->setAttribute('disabled');
		$this->_input->completeAttribute('class', 'bn-nomodified');
	}
	
}
?>