<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Input.php';

class Checkbox extends Bn_input
{

	/**
	* Constructeur
	*/
	function __construct($aName, $aLabel, $aValue, $aChecked=false)
	{
		$this->_input = $this->addBalise('input/', $aName);
		$this->_input->setAttribute('type', 'checkbox');
		$this->_input->setAttribute('name', $aName);
		if ( !strstr($aName, '[]') ) $this->_input->setAttribute('id', $aName);
		if ( !is_null($aValue) ) $this->_input->setAttribute('value', $aValue);
		if ($aChecked) $this->_input->setAttribute('checked', 'checked');

		if ( !strstr($aName, '[]') )
		 	parent::__construct('div', 'div'.$aName);
		 else
		 	parent::__construct('div');
		 $this->setAttribute('class', 'bn-radio bn-check');
		
		if (!empty($aLabel))
		{
			$this->_label = new Bn_Balise('span');
			$label = $this->_label->addBalise('label', '', $aLabel); 
			$label->setAttribute('for', $aName);
			$this->rightLabel();
		}
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