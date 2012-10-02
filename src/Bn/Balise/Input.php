<?php
/*****************************************************************************
!   $Id$
******************************************************************************/
class Bn_Input extends Bn_Balise
{
	protected $_label = null;
	protected $_input = null;
	protected $_jsControl = array();
	protected $_isLabelLeft = true;
	
	/**
	 * Affichage
	 */
	public function toHtml()
	{
		// Ajout de la fonction javascript de control (jquery.validate)
		if ( count($this->_jsControl) )
		{
			$validate = '{' . implode(',', $this->_jsControl) . '}';
			$this->_input->addMetadata('validate',  $validate);
		}

		// Ajout du label
		if ( ! empty($this->_label) )
		{ 
			$this->_isLabelLeft ? $this->insertContent($this->_label) : $this->addContent($this->_label);
		}

		// Saut de ligne
		$this->addBreak();
		return parent::toHtml();
	}

	/**
	 * La saisie est facultative
	 */
	public function noMandatory()
	{
		$key = array_search('required:true', $this->_jsControl);
		unset($this->_jsControl[$key]);
		$this->_input->setAttribute('class', 'bn-option');
	}
	
	/**
	 * La saisie est obligatoire
	 */
	public function mandatory()
	{
		$this->_jsControl[] = 'required:true';
		$this->_input->setAttribute('class', 'bn-mandatory' );
	}
	
	/**
	 * La saisie est impossible
	 */
	public function readonly()
	{
		$this->_input->setAttribute('readonly', 'readonly');
	}
	
	/**
	 * Fixe l'index de tabulation
	 */
	public function setTabIndex($p_i_index)
	{
		$this->_input->setAttribute('tabindex', $p_i_index);
	}

	/**
	 * Renvoi un pointeur vers le label
	 */
	public function getLabel()
	{
		return $this->_label;
	}

	/**
	 * Renvoi un pointeur vers la szone de saisie
	 */
	public function getInput()
	{
		return $this->_input;
	}
	
	/**
	 * Renseigne l'action a effectuer sur un evenement
	 * @param 	integer	$aAction	Identifiant de l'action
	 * @return 	void
	 */
	public function setAction($aAction, $aAjax=false)
	{
		$this->_input->addMetadata('bnAction', $aAction);
	}

	/**
	 * Renseigne l'action a effectuer sur un evenement
	 * @param 	integer	$aAction	Identifiant de l'action
	 * @return 	void
	 */
	public function addValidateRule($aRule, $aValue)
	{
		$this->_jsControl[] = "$aRule:$aValue";
	}
	
	public function rightLabel()
	{
		$this->_isLabelLeft = false;
	}
	
	public function leftLabel()
	{
		$this->_isLabelLeft = true;
	}
	
	
}
?>