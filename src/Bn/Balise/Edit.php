<?php
/*****************************************************************************
!   $Id$
******************************************************************************/
require_once 'Input.php';


class Edit extends Bn_Input
{
	private $_str; 
	
	/**
	 * Constructeur
	 *
	 */
	public function __construct($aName, $aLabel, $aValue, $aMaxchar)
	{
		$this->_input = $this->addBalise("input/", $aName);
		$this->_input->setAttribute("type", "text");
		$this->_input->setAttribute("name", $aName);
		$this->_input->setAttribute("id", $aName);
		$this->_input->setAttribute('class', 'bn-mandatory');
		$this->_input->setAttribute('maxlength', $aMaxchar);
		if (is_null($aValue))
		{
			$this->_input->setAttribute('value', Bn::getValue($aName, ''));
		}
		else
		{
			$this->_input->setAttribute('value', $aValue);
		}
		$this->addValidaterule('required', 'true');
		
		parent::__construct('div', 'div'.$aName);
		$this->setAttribute('class', 'bn-edit');
		$this->_label = new Bn_Balise('label', null, $this->_getLocale($aLabel));
		$this->_label->setAttribute('class', 'bn-label');
		$this->_label->setAttribute('for', $aName);
		$this->_str = $this->_getLocale($aLabel);
	}

	/**
	 * La saisie est inhibee
	 */
	public function noModified()
	{
		$this->_input->setAttribute('readonly');
		$this->_input->setAttribute('class', 'bn-nomodified');
	}

	/**
	 * La saisie est un url
	 */
	public function equalTo($aInput)
	{
		if ( !empty($aInput) )
		{
			$this->addValidaterule('equalTo', "'#$aInput'");
		}
	}
	
	/**
	 * La saisie est une adresse email
	 */
	public function email()
	{
		$this->addValidaterule('email', 'true');
	}
	
	/**
	 * La saisie est un telephone
	 */
	public function phone()
	{
		$this->addValidaterule('digits', 'true');
	}

	
	/**
	 * La saisie est une url
	 */
	public function url()
	{
		$this->addValidaterule('url', 'true');
	}
	
	public function autocomplete($aAction, $aCallbackSelect=null, $aCallbackClose=null)
	{
		$id = $this->_input->getAttribute("id");
		$script = "var md = $('#" . $id . "').metadata();\n";
		$script .= "var url='index.php?ajax=1&bnAction=" . $aAction . "';\n";
		$script .= "for(key in md) url += '&' + key + '=' + md[key];\n";
		$script .= '$("#' . $id . '").autocomplete({';
		$script .= 'source: url';
		$script .= ',minLength: 3';
		$script .= ',delay: 600';
		$script .= ',focus: function(event,ui){return false;}';
		if (!is_null($aCallbackClose)) $script .= ',close: function(event,ui){return '. $aCallbackClose . '(event, ui);}';
		if (!is_null($aCallbackSelect)) $script .= ',select: function(event,ui){return '. $aCallbackSelect . '(event, ui);}';
		$script .= '});';
		//function(event, ui) {
		//		log(ui.item ? ("Selected: " + ui.item.value + " aka " + ui.item.id) : "Nothing selected, input was " + this.value);
		//	}
		$this->addJQReady($script);
	}
	
	public function tooltip($aContent)
	{
		$id = $this->_input->getAttribute("id");
		$img = $this->addImage('img_'.$id, 'help.png', 'Help');
		$msg = $this->_str . ' - ' . $this->_getLocale($aContent);
		$img->setTooltip($msg);
	}
}
?>