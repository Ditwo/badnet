<?php
/*****************************************************************************
!   $Id$
******************************************************************************/
require_once "Edit.php";

class IntEdit extends Edit
{
	var $_minvalue=null;//valeur minimale
	var $_maxvalue=null;//valeur maximale
	var $_format="";//format d'affichage du nombre
	var $_scriptindice = null;
	
	function __construct($p_s_name, $p_s_label, $p_i_value, $p_i_min=null,$p_i_max=null){
		$l_i_maxsize = 0;
							
		if (!is_null($p_i_min)){
			$l_i_maxsize = strlen($p_i_min);			
			$this->_minvalue=$p_i_min;
		}
		if (!is_null($p_i_max)){
			if ($p_i_max>$l_i_maxsize) $l_i_maxsize=$p_i_max;
			$this->_maxvalue=$p_i_max;
		}
		if($l_i_maxsize==0) $l_i_maxsize=5;		
		
		//initialisation de la classe parent
		parent::__construct($p_s_name, $p_s_label, $p_i_value, $l_i_maxsize);	

		//ajout du script de limitation au nombre
		$l_o_page =& Bn_Page::getPage();		
		$l_o_page->addScript('Bn/Js/jquery.numeric.pack.js');//revision 4758
		$l_s_script = "$('#$p_s_name').numeric()";
		$this->addJQReady($l_s_script);
		
		//ajout de la fonction javascript de control du format de date (jquery.validate)
		$l_s_validate = "{";
		if ($this->_isMandatory()) $l_s_validate .= "required:true,number:true,";
		if (!is_null($this->_minvalue)) $l_s_validate .= "min:{$this->_minvalue},";
		if (!is_null($this->_maxvalue)) $l_s_validate .= "max:{$this->_maxvalue},";	
		$l_s_validate .= "}";					
		$l_s_class = $this->_input->getAttribute("class");
		$l_s_class = str_replace("{required:true}",$l_s_validate,$l_s_class);
		$this->_input->setAttribute("class",$l_s_class);
				
	}
	
	/**
	 * Accesseur fixer la valeur max
	 */
	function setMaxValue($p_i_value){
		$this->_maxvalue = $p_i_value;
	}
	
	/**
	 * Accesseur fixer la valeur min
	 */
	function setMinValue($p_i_value){
		$this->_minvalue = $p_i_value;
	}
	
	/**
	 * Accesseur fixer la valeur min et max
	 */
	function setRange($p_i_minvalue,$p_i_maxvalue){
		$this->_minvalue = $p_i_minvalue;
		$this->_maxvalue = $p_i_maxvalue;
	}
	
}

?>