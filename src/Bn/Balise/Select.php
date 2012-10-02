<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
class Select extends Bn_Balise
{
	 // Label avant la liste
	var $_label = null;

	 // Options selectionnees a partir des valeurs
	var $_values = array();

	 // Options selectionnees a partir des textes
	var $_texts = array();

	 // Options disabled
	var $_disabled = array();
	
	/**
	 * Constructeur
	 *
	 *
	 * @param     string    $name       Nom de la liste Select
	 * @param     int       $size       (optional) Nombre de ligne
	 * @param     bool      $multiple   (optional) Selection multiple ou pas
	 * @param     mixed     $attributes (optional) Attributs HTML
	 * @param     int       $tabOffset  (optional) Tabulation offset
	 * @since     1.0
	 * @access    public
	 * @return    void
	 * @throws
	 */
	function __construct($aName, $aLabel = null, $aAction=null, $aTarget=null)
	{
		$this->_label = $aLabel;
		$this->_isMandatory = true;
		parent::__construct('div', 'div'.$aName);
		$this->setAttribute('class', 'bn-edit');
		$this->_label = new Bn_Balise('label', null, $this->_getLocale($aLabel));
		$this->_label->setAttribute('class', 'bn-label');
		$this->_label->setAttribute('for', $aName);
		$this->_select = $this->addBalise('select', $aName);
		$this->_select->setAttribute('name', $aName);
		$this->_select->completeAttribute('class', 'bn-select');
		if ( !empty($aAction) )
		{
			if ( !empty($aTarget) )
			{
				$this->_select->addMetadata('target', "'#$aTarget'");
				$this->_select->addMetadata('bnAction', $aAction);
				$this->_select->completeAttribute('class', ' selectAjax');
			}	
			else $this->_select->setAction($aAction);
		}
		
	}

	/**
	 * Fixe les valeurs selectionnees a partir des valeurs
	 *
	 * @param     mixed    $values  Array or comma delimited
	 *                              string of selected values
	 */
	function addDisabledValues($aValues)
	{
		if ( is_array($aValues) )
		{
			$this->_disabled = $aValues;
		}
		else if (is_string($aValues))
		{
			$this->_disabled = explode(",", $aValues);
		}
		else
		{
			$this->_disabled[] = $aValues;
		}
	}
	
	
	/**
	 * Fixe les valeurs selectionnees a partir des valeurs
	 *
	 * @param     mixed    $values  Array or comma delimited
	 *                              string of selected values
	 * @since     1.0
	 * @access    public
	 * @return    void
	 * @throws
	 */
	function setSelectedValues($aValues)
	{
		if ( is_array($aValues) )
		{
			$this->_values = $aValues;
		}
		else if (is_string($aValues))
		{
			$this->_values = explode(",", $aValues);
		}
		else
		{
			$this->_values[] = $aValues;
		}
		if ( count($this->_values) > 1)
		{
			$this->_select->setAttribute('multiple');
		}

	}

	/**
	 * Fixe les valeurs selectionnees a partir des textes
	 *
	 * @param     mixed    $values  Array or comma delimited
	 *                              string of selected values
	 * @since     1.0
	 * @access    public
	 * @return    void
	 * @throws
	 */
	function setSelectedTexts($aTexts)
	{
		if ( is_array($aTexts) )
		{
			$this->_texts = $aTexts;
		}
		else if (is_string($aTexts))
		{
			$this->_texts = explode(',', $aTexts);
		}
		else
		{
			$this->_texts[] = $aTexts;
		}
		if ( count($this->_texts) > 1 )
		{
			$this->_select->setAttribute('multiple');
		}
	}

	/**
	 * Ajoute une option a la liste
	 *
	 * @param     string    $text       Texte de l'option a afficher
	 * @param     string    $value      Valeur de l'option
	 * @param     bool      $selected   (Option) l'option est-elle selectionne
	 * @param     mixed     $attributes (Option) Attributs de l'option
	 * @since     1.0
	 * @access    public
	 * @return    void
	 * @throws
	 */
	function addOption($aText, $aValue, $aSelected = false, $aAttributes = array(), $aEncode=false)
	{
		if ($aSelected && !in_array($aValue, $this->_values))
		{
			$this->_values[] = $aValue;
		}
		if ( is_null($aAttributes) )
		{
			$aAttributes = array();
		}
		if ($aEncode) $option = $this->_select->addBalise('option', '', utf8_encode($aText));
		else $option = $this->_select->addBalise('option', '', $aText);
		
		$option->setAttributes($aAttributes);
		$option->setAttribute('value', $aValue);
	}

	/**
	 * Ajoute des option a partir d'un tableau
	 *
	 * @param     array    $arr     Tableau des options
	 * @param     mixed    $values  (optional) Array or comma delimited string of selected values
	 * @since     1.0
	 * @access    public
	 * @return    PEAR_Error on error or true
	 */
	function addOptions($aOptions, $aValues=null, $aEncode=false)
	{
		if (!is_array($aOptions) && !is_a($aOptions, 'Bn_query'))
		 {
			return new PEAR_ERROR('First argument to HTML_Select::addOptions is not a valid array or valid Bn_query object');
		}
		if (isset($aValues)) {
			$this->setSelectedValues($aValues);
		}
		if (is_array($aOptions))
		{
			foreach($aOptions as $value => $text)
			{
				$this->addOption($text, $value, false, array(), $aEncode);
			}
		}
		else
		{
			$rows = $aOptions->getRows(false);
			foreach($rows as $row)
			{
				$this->addOption($row[1], $row[0]);
			}
			
		}
		return true;
	}

	/**
	 * Ajoute des options a partir d'un tableau
	 *
	 * @param     array    $arr     Tableau des options
	 * @param     mixed    $values  (optional) Array or comma delimited string of selected values
	 * @since     1.0
	 * @access    public
	 * @return    PEAR_Error on error or true
	 * @throws    PEAR_Error
	 */
	function addLov($aArr, $aValues=null)
	{
		if (!is_array($aArr))
		{
			return new PEAR_ERROR('First argument to HTML_Select::loadArray is not a valid array');
		}
		$values = array();
		if (isset($aValues) )
		{
			if ( is_array($aValues) )
			{
				$values = $aValues;
			}
			else
			{
				$values[] = $aValues;
			}
		}
		else
		{
			$name = $this->_select->getAttribute('name');
			$values[] = Bn::getValue($name, '');
		}

		foreach($aArr as $val)
		{
			$selected = !empty($val['select']) || in_array($val['value'], $values);
			$disabled = empty($val['disabled']) ? null : 'disabled';
			$this->addOption($val['text'], $val['value'], $selected, $disabled);
		}
		return true;
	}

	/**
	 * Returns the SELECT in HTML
	 *
	 * @since     1.0
	 * @access    public
	 * @return    string
	 * @throws
	 */
	function toHtml()
	{
		if ( empty($this->_values) && empty($this->_texts) )
		{
		}
		$options = $this->_select->getContent();
		foreach ($options as $key => $option)
		{ 
			//if( empty($option) ) continue;
			$value = $option->getAttribute('value');
			$text  = $option->getContent();
			if (in_array($value, $this->_values) ||
			( !empty($text) && in_array($text[0],  $this->_texts)))
			{
				$options[$key]->setAttribute('selected');
			}
			if (in_array($value, $this->_disabled))
			{
				$options[$key]->setAttribute('disabled');
			}
		}

		// Ajout du label
		if ( ! empty($this->_label) )
		{
			$this->insertContent($this->_label);
		}
		return parent::toHtml();
	}

	/**
	 * Autorise la selection multiple
	 */
	function multiple()
	{
		$this->_select->setAttribute('multiple');
	}

	/**
	 * La saisie est facultative
	 */
	function noMandatory()
	{
		$this->_isMandatory = false;
	}

	/**
	 * Fixe l'index de tabulation
	 */
	function setTabIndex($p_i_index)
	{
		$this->_select->setAttribute('tabindex', $p_i_index);
	}

	/**
	 * Fixe le nombre de ligne
	 */
	function setSize($p_i_size)
	{
		$this->_select->setAttribute('size', $p_i_size);
	}

	/**
	 * Renvoi un pointeur vers le label
	 */
	function &getLabel()
	{
		return $this->_label;
	}

	/**
	 * Renvoi un pointeur vers la zone de saisie
	 * @return Bn_balise
	 */
	function getSelect()
	{
		return $this->_select;
	}
}
?>
