<?php
/*****************************************************************************
!   $Id$
******************************************************************************/

require_once "Edit.php";
require_once "Image.php";

class DateEdit extends Edit
{
	var $_scriptindice = null;
	var $_datemask = "dd/mm/yy"; //masque par defaut de la date
	var $_name = "";//nom du champ
	var $_lang = 'fr'; //langue du calendrier
	private $_options = array();  // Option de construction
	
	function __construct($aName, $aLabel, $aValue){

		if ($aName == "date") echo "DateEdit : non Date interdit pour un champ date";
		//initialisation de la classe parent
		$name = $aName . 'Disp';
		//parent::__construct($name, $aLabel, $aValue, 10);
		parent::__construct($name, $aLabel, '', 10);
		$this->setAttribute('class', 'bn-date');
		
		$this->_name = $name;		
		if (!empty($aValue) )
			list($this->_day, $this->_month, $this->_year) = explode('-', $aValue);
		
		//ajout du champ cache mysql
		$this->addHidden($aName, $aValue);
		
		//ajout de la fonction javascript de control du format de date (jquery.validate)
		//$this->_jsControl[] = 'dateFR:true';
						
		//$this->addOption('showOn', "'focus'"); 
		$this->addOption('showOn', "'both'"); 
		//$this->addOption('showOn', "'button'"); 
		$this->addOption('buttonImageOnly', 'true'); 
		$this->addOption('buttonImage',  "'Bn/Img/calendar.png'");
		$this->addOption('altField',  "'#$aName'");
		$this->addOption('altFormat',  "'yy-mm-dd'");
		$this->addOption('dateFormat',  "'dd-mm-yy'");
		$this->addOption('changeYear',  "true");
		$this->addOption('constraintInput',  'true');
		
		//Ajout du calendrier		
		$script = $this->_datePicker();
	}

	/**
	 * Ajoute une option
	 * @name addOption
	 * @param 	string	$aOption	nom de l'option
	 * @param 	mixed   $aValue		valeur de l'option
	 */
	function addOption($aOption, $aValue)
	{
		unset($this->_options[$aOption]);
		$this->_options[$aOption] = "$aOption: $aValue";
		$this->_datePicker();
	}
	
	/**
	 * Accesseur pour le changement du format de la date
	 */
	function setFormat($aFormat){
		$this->addOption('dateFormat',  "'$aFormat'");
	}
	
	/**
	 * Accesseur pour le changement de l'image du bouton calendrier
	 */
	function setImage($aImage){
		if (file_exists($aImage))
		{
			$this->addOption('buttonImage',  "'$aImage'");
			$script = $this->_datePicker();
		}
	}
	
	/**
	 * script de creation du calendrier
	 */
	function _datePicker(){	
		$name = $this->_name;				
			
		$script = "$('#$name').datepicker({";
		$script .= implode("\n,", $this->_options);
		$script .= "}";
		//$script .= ",$.datepicker.regional['$this->_lang']";
		$script .= ");\n";
		if ( !empty($this->_year) )
		{
			$month = $this->_month - 1;
		    $script .= "$('#$name').datepicker('setDate', new Date(" . $this->_year . "," . $month . "," . $this->_day ."));\n";
		}
			
		$this->_scriptindice = $this->addJQReady($script, $this->_scriptindice);
	}

}

?>