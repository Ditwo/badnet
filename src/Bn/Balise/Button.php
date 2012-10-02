<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Image.php';

class Button extends Bn_Balise
{
	private $_name  = array();
	private $_image  = array();
	private $_label  = null;
	private $_isRight  = false;

	/**
	 * Constructeur
	 * @param  string   $aName    Identifiant du bouton
	 * @param  string   $aLabel   Texte du button
	 * @param  integer  $aAction  Action associee
	 * @param  string   $aImg     Nom du fichier image
	 */
	public function __construct($aName, $aLabel, $aAction, $aImg=null)
	{
		parent::__construct('button', $aName);
		$this->setAttribute('class', 'bnbutton bn-button');
		$this->setAttribute('type', 'button');
		$this->_image = $aImg;
		$this->_image = $aImg;
		$this->_label = $aLabel;
		$this->_alt   = $aLabel;
		if (!empty($aName))
		{
			$script = "$('#" . $aName . "').button(";
			if (!empty($aImg) ) $script .= "{ icons: {primary:'ui-icon-" . $aImg . "'}}";
			$script .= ");";
			$this->_scriptIndice = $this->addJQReady($script);
		}
	}

	/**
	 * Affichage du bouton
	 * @return string   chaine html du bouton
	 */
	function toHtml()
	{
		$this->addContent($this->_label);
		return parent::toHtml();
	}

}
?>