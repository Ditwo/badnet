<?php
/*****************************************************************************
!   $Id$
******************************************************************************/

class Dialog extends Bn_balise
{
	private $_buttons = array();
	private $_options = array();
	private $_scriptIndice = null;
	/**
	 * Constructeur
	 * @param 	string	$aName		Nom du dialogue
	 * @param 	string	$aTitle		Titre du dialogue
	 * @param	int		$aXidth		Largeur
	 * @param 	int		$aHeight	Hauteur
	 * @return 	Dialog
	 */
	public function __construct($aName, $aTitle, $aWidth, $aHeight)
	{		
		$div = new Bn_balise('div', $aName, null, 'bn-dialog');
		parent::__construct('div', $aName);
		//$this->setAttribute('class', 'flora');
		$this->_name = $aName;
		$this->_options['width']  = $aWidth;
		$this->_options['height'] = $aHeight;
		$this->_options['modal']  = 'true';
		$this->_options['hide']  = "'slide'";		
		$this->_options['show']  = "'slide'";
		$this->_options['autoOpen']  = 'false';
        $page = Bn_Page::getPage();
		$this->_jready();
	}
	
	/**
	 * Ajoute une option a la boite de dialogue
	 * @param 	string	$aOption		Nom de l'option
	 * @param 	mixed	$aValue			Valeur de l'option
	 * @return	void
	 */
	public function addOption($aOption, $aValue)
	{
		$this->_options[$aOption] = $aValue;
		$this->_jready();
	}
		
	/**
	 * Preparation de la fonction javascript d'initialisation
	 */
	 private function _jready()
	 {
	 	//$script  = 'if (! $("#'. $this->_name . '").length ) {';
		$script  = '$("#' . $this->_name . '").dialog({';
		$buttons = '{';
		$glue = '';
		foreach($this->_buttons as $label=>$callback)
		{
			$buttons .= $glue . '"' . $label .'":' .$callback. "\n";
			$glue = ',';
		}
		$buttons .= '}';
		$this->_options['buttons'] = $buttons;
		$glue = '';		
		foreach ($this->_options as $option=>$value)
		{
			$script .= $glue . $option .':' .$value . "\n";
			$glue = ',';
		}
		$script .= "});\n";
		//$script .= '$("#' . $this->_name . '").dialog("close");';			
	 	$this->_scriptIndice = $this->addJQReady($script, $this->_scriptIndice);
	 } 
	 
	 
}
?>