<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
class Form extends Bn_Balise
{
	private $_message = array();
	private $_scriptindice = null;
	private $_form = null;
	private $_options = array();
	private $_id;

	public function __construct($aId, $aAction, $aTarget = null)
	{
		// L'objet Form
		$this->_form = new Bn_Balise('form', $aId);
		$this->_form->setAttribute('method', 'post');
		if (is_numeric($aAction))  $this->_form->setAttribute('action',  $_SERVER['PHP_SELF']);
		else $this->_form->setAttribute('action',  $aAction);
		if ( !is_null($aTarget) ) $this->_form->addMetadata('target', "'#$aTarget'");

		$this->_id = $aId;
			
		// div contenu dans la form
		parent::__construct('div', 'div' . $aId);
		$hide = $this->addHidden('bnAction', $aAction);
		$hide->setAttribute('id', $aId.'Action');

		$hide = $this->addHidden('ajax', true);
		$hide->setAttribute('id', $aId.'Ajax');

		//creation du script des message d'erreur de la validation
		$this->_options['focusInvalid'] = 'false';
		$this->_options['onclick'] = 'false';
		$this->_options['onkeyup'] = 'false';
		$this->_options['meta']    = '"validate"';

		$this->_options['errorElement'] = "'p'";
		$this->_options['errorClass'] = "'bn-error'";
		$this->_options['errorPlacement'] = 'function(error, element) {
     error.prependTo( element.parent("div") );
   }';


		$this->_jready();
	}

	public function addOption($aName, $aValue)
	{
		$this->_options[$aName] = $aValue;
		$this->_jready();
	}

	private function _jready()
	{
		$script = '$("#' . $this->_id . '").validate({';
		$glue = '';
		foreach ($this->_options as $option=>$value)
		{
			$script .= $glue . $option .':' .$value . "\n";
			$glue = ',';
		}
		$script .= "});\n";

		$this->_scriptindice = $this->addJQReady($script, $this->_scriptindice);
	}

	/**
	 * Retourne la chaine HTML de creation de la form
	 */
	public function toHtml()
	{
		$this->_form->addContent(parent::toHtml(), false);
		return $this->_form->toHtml();
	}

	/**
	 * Retourne l'objet form
	 */
	public function getForm()
	{
		return $this->_form;
	}

}

?>