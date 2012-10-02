<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
class Pager extends Bn_Balise
{
	private $_target;  // Div cible
	private $_action;  // Action pour le lien 
	
	/**
	 * Constructeur
	 *
	 */
	function __construct($aName, $aAction, $aTarget)
	{
		parent::__construct('p', $aName);
		$this->setAttribute('class', 'bn-pager');
		$this->_action = $aAction; 
		$this->_target = $aTarget; 
	}

	/**
	 * Affichage de
	 */
	function set($aTotal, $aNb, $aPage)
	{
		$nbLink = 15;
		$nbPage = ceil( $aTotal / $aNb );
		$first = 1;
		if ($nbPage > $nbLink)
		{
			$first = max(1, $aPage-($nbLink/2));
			$first = min($first, $nbPage-$nbLink+1);			
		}
		$last = min($first + $nbLink - 1, $nbPage);
		if ($nbPage > 1)
		{
			for ($i = $first; $i<=$last; $i++)
			{
				$url = $this->_action . '&bnPage=' . $i; 
				$lnk = $this->addlink('', $url, $i, $this->_target);
				if ($i == $aPage) $lnk->completeAttribute('class', 'bn-pager-current');
				else $lnk->completeAttribute('class', 'bn-pager-page');
			}
		}
	}
}
?>