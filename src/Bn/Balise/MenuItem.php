<?php
/*****************************************************************************
!   $Id$
******************************************************************************/
class MenuItemJQuery extends Bn_Balise
{
	/**
	 * Label de la zone
	 *
	 * @var       array
	 * @since     1.0
	 * @access    private
	 */
	var $_label = null;
	
	var $_ul = null;
	
	var $_lvl = 0;
	
	/**
	 * Constructeur
	 * @param string p_s_label Libell� du Menu
	 * @param string p_s_action Action d�clench�e
	 * @param string p_i_level Niveau de l'element au sein du menu (1 ou 2)
	 */
	function __construct($p_s_label,$p_s_action=null,$p_i_level = 1)
	{
		$this->_lvl = $p_i_level;
		
		if (empty($p_s_label))
		{
			parent::__construct();
		}
		else
		{
			if($p_i_level == 2)
			{
				parent::__construct('li');
				
				$l_o_ahref =& $this->addBalise('a');
				if($p_s_action != null)
					$l_o_ahref->setAttribute('href','index.php?bnAction='.$p_s_action);
				else
					$l_o_ahref->setAttribute('href','#');
				$l_o_ahref->addContent($p_s_label);
			}
			else
			{
				parent::__construct('li');
				$l_o_ahref =& $this->addBalise('a');
				$l_o_ahref->addContent($p_s_label);
				if($p_s_action != null){
					$l_o_ahref->setAttribute('href','index.php?dzAction='.$p_s_action);
				}
				else
					$l_o_ahref->setAttribute('href','#');
					
				$this->_ul =& $this->addBalise('ul');
				$this->_ul->setAttribute('class','Menu');
			}
		}	
	}
	
	function getLevel()
	{ 
		return $this->_lvl; 
	}
	
	function &addMenuItem(&$p_o_menuItem)
	{
		$l_o_balise = null;
		// Seulement pour les elements de niveau 2
		if($p_o_menuItem->getLevel()>1)
		{
			$l_o_balise =& $this->_ul->addContent($p_o_menuItem);
		}
		return $l_o_balise;
	}
}
?>
