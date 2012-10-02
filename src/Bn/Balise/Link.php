<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

class Link extends Bn_Balise
{
	/**
	 * Constructeur
	 * @param string	$aName		Identifiant du lien
	 * @param integer	$aAction	Action du lien
	 * @param string	$aContent	Contenu du lien
	 * @param string    $aTarget	Cible
	 * @return Bn_balise
	 */
	function __construct($aName, $aAction, $aContent=null, $aTarget=null)
	{
		parent::__construct('a', $aName, $this->_getLocale($aContent));
		if ( intval($aAction) > 0 )
		{
			$ref = 'index.php?bnAction=' . $aAction;
			if (is_null($aTarget)) $ref .= '&ajax=false';
		}
		else
		{
			$ref = $aAction;
		}
		if ( defined('SID') && SID != '' )
		{
			$ref .= '&' . SID;
		}
		$this->setAttribute('href', $ref);
		if (!is_null($aTarget))
		{
			$this->setAction($aAction);
			$this->addMetadata('target', "'#$aTarget'");
			$this->completeAttribute('class', 'lnkAjax');
		}
		else $this->setAction($aAction, 0);
	}

	public function addIcon($aIcon = 'document')
	{
		$this->completeAttribute('class', 'bn-link-icon');
		$s = new Bn_balise('span');
		$s->setAttribute('class', "ui-icon ui-icon-" . $aIcon);
		$this->insertContent($s);
	}
	
}
?>