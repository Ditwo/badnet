<?php
/*****************************************************************************
!   $Id$
 *  * Element de type boite � message
 * 	$p_s_type : 
 * 		"alert" = boite d'alert simple
 * 		"confirm" = boite de type Ok Cancel
 * 	
 *  $p_s_event :
 * 		"click" : sur clic de l'objet container
 * 
 *
 */

class MessageBox
{
	var $_scriptindice=null; //indice du script dans la tableau des scripts
	var $_inputHidden=null;//id du champ cach� contenant le retour
	
	/**
	 * Constructeur
	 *
	 */
	function MessageBox(&$p_o_container,$p_x_content=null,$p_s_type="alert",$p_s_event="click",$p_s_return=null)
	{
		//lien vers l'objet jquery
		$l_o_page =& Bn_Page::getPage();
		$l_o_page->addScript("Bn/Js/jquery-impromptu.1.2.js"); //version 1.2
		
		if ($p_s_type=="confirm" && is_null($p_s_return)){
			$this->_inputHidden = "hidMsg".$p_o_container->getAttribute("id");
			$p_o_container->addHidden($this->_inputHidden);
		}
		
		//cr�ation du script jquery
		$this->_scriptindice = $this->createBox($p_o_container,$p_x_content,$p_s_type,$p_s_event,$p_s_return);
		
	}

	
	function createBox(&$p_o_container,$p_x_content,$p_s_type,$p_s_event,$p_s_return){
		
		switch ($p_s_type){
			case "alert":
				$l_s_boite .= "$.prompt('$p_x_content');";		
			break;
			case "confirm":				
				$l_s_boite .= "$.prompt('$p_x_content',{
				callback: confirmCallback, 
				buttons: { 
				".$p_o_container->_getLocale("Ok").": true, 
				".$p_o_container->_getLocale("Cancel").": false } });";		
			break;			
		}		
		if (!is_null($p_s_event)){
			$l_s_script = "function confirmCallback(p_s_click,p_s_message){ \n";
			
				$l_s_script .= "$('#$this->_inputHidden').val(p_s_click); \n";			
				if (!is_null($p_s_return)){
				$l_s_script .= "$p_s_return; \n";			
			}
			$l_s_script .= "} \n";
			$l_s_script .= "$('#".$p_o_container->_input->getAttribute("id")."').$p_s_event(function() { \n";
			$l_s_script .= $l_s_boite ." \n";
			$l_s_script .= "});";
		}
		
		return $p_o_container->addJQReady($l_s_script,$this->_scriptindice);
	}

}
?>