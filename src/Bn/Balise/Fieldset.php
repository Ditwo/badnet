<?php
/*****************************************************************************
!   $Id$
******************************************************************************/

class Fieldset extends Bn_Balise
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
	function __construct($p_s_name, $p_s_legende, $p_s_class)
	{		
		parent::__construct('fieldset', $p_s_name);		
		if ( !is_null($p_s_class) )
		{
			$this->setAttribute('class', $p_s_class);			
		}
		
		//ajout de la l�gende
		$l_o_legende = new Bn_Balise('legend');
		$l_o_legende->addContent($p_s_legende);
		$this->addContent($l_o_legende);
	}

	/**
	 * Affichage de
	 */
	function toHtml()
	{
		$l_l_id = $this->getAttribute('id');
		$l_o_div = new Bn_balise('div', 'div'.$l_l_id, parent::toHtml());
		$l_o_div->setAttribute('class', 'bnfieldset');			
		return $l_o_div->toHtml();
	}
}
?>