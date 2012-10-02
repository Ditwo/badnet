<?php

/*****************************************************************************
!   $Id$
******************************************************************************/

class MenuJQuery extends Bn_Balise
{
	/**
	 * Label de la zone
	 *
	 * @var       array
	 * @since     1.0
	 * @access    private
	 */
	var $_menuItems = array();
	
	var $_lastItemNiv1 = null;
	
	/**
	 * Constructeur
	 *
	 */
	function __construct($p_s_id)
	{
		if ( empty($p_s_id) )
		{
			parent::__construct();
		}
		else
		{
			parent::__construct('div', 'test');
			//$this->setAttribute('class', 'divMenu');
		}	
		$l_o_tmp =& $this->addBalise("div",'div'.$p_s_id);
		$l_o_tmp->setAttribute('class', 'divMenu');
		$this->_ul  =& $l_o_tmp->addBalise("ul","Nav");
	}

	/**
	 * Ajout d'un element dans le menu courant
	 *
	 * @param string $p_s_label
	 * @param string $p_s_action
	 * @param integer $p_i_level
	 * @return Bn_Balise
	 */
	function &addMenuItem($p_s_label,$p_s_action=null,$p_i_level = 1)
	{
		if($p_i_level == 1)
		{
			$l_o_balItem = new MenuItemJQuery($p_s_label,$p_s_action,$p_i_level);
			$l_o_balMenu =& $this->_ul->addContent($l_o_balItem);
			$this->_lastItemNiv1 =& $l_o_balMenu;
	
		}
		else
			if($this->_lastItemNiv1 != null)
			{
				$l_o_balItem = new MenuItemJQuery($p_s_label,$p_s_action,$p_i_level);
				$this->_lastItemNiv1->addMenuItem($l_o_balMenu);
			}
			else
				$l_o_balItem = null;
		return $l_o_balItem;
	}
}
?>
