<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'HTML/Page2.php';
require_once 'Bn/Balise.php';

class Bn_Page extends HTML_Page2
{
	private $_errMsg    = array(); // Messages d'erreur
	private $_traceMsg  = array(); // Messages de trace


	/**
	 * Acces au singleton
	 * @return	Bn_page
	 */
	static public function getPage()
	{
		static $page = null;
		if ( empty($page) ) {
			$page = new Bn_Page();
		}
		return $page;
	}

	/**
	 * Constructeur
	 * @param	string	$aMsg	Message a afficher
	 * @return	Bn_Page
	 */
	private function __construct($aMsg=null)
	{
		$title = 'BadNet';
		$this->setDoctype('XHTML 1.0 Strict');
		$this->setTitle($title);
		$this->setMetaContentType();
		//@todo a supprimer. Pour compatibilite avec les anciennes langues
		$locales  = array('Fr' => 'fr',
							'fra' => 'fr',
							'En' => 'en',
							'eng' => 'en',
							'De' => 'de',
							'de' => 'de');
		$locale = $locales[BN::getLocale()];
		$theme  = Bn::getValue('theme');
		$this->setLang($locale);
		$this->setCache(true);

		//Declaration des fichiers script JQuery
		//$this->addScript('Bn/Js/jquery-1.4.2.min.js');
		$this->addScript('Bn/Js/jquery-1.4.4.min.js');
		$this->addScript("Bn/Js/Bn.js");
		$this->addScript('Bn/Js/jquery.metadata.2.1.js');
		$this->addScript('Bn/Js/jquery.preloadcss.js');
		// Fuck ie6
		if (isset($_SERVER['HTTP_USER_AGENT']))	$userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
		else $userAgent = '';
		$t = "(?:msie)";
    	$found = preg_match("/(?P<browser>" . $t . ")(?:\D*)(?P<majorVersion>\d*)(?P<minorVersion>(?:\.\d*)*)/i",
			$userAgent, $matches);
		if( empty($matches) || $matches['majorVersion'] >= 7)
			$this->addScript('Bn/Js/jquery.corner.js');

		$this->addScript('Bn/Js/jquery.timer.js');
		$this->addScript('Bn/Js/jquery.timers.js');
		$this->addScript('Bn/Js/jquery.selectboxes.min.2.2.4.js');
		//$this->addScript('Bn/Js/jquery-ui-1.7.2.custom.min.js');
		//$this->addStyleSheet('../Themes/' . $theme . '/Ui/jquery-ui-1.7.2.css', 'text/css', 'screen');
		//$this->addScript('Bn/Js/jquery-ui-1.8.1.custom.min.js');
		///$this->addScript('Bn/Js/jquery-ui-1.8.5.custom.min.js');
		//$this->addScript('Bn/Js/jquery-ui-1.8.2.custom.min.js');
		//$uiVersion = '1.8rc3';
		$uiVersion = '1.8.7';
		$this->addScript('Bn/Js/jquery-ui-'. $uiVersion . '.custom.min.js');
		$this->addStyleSheet('../Themes/' . $theme . '/Ui/jquery-ui-' . $uiVersion . '.custom.css', 'text/css', 'screen');

		$this->addScript('Bn/Js/jquery.form.2.47.js');
		$this->addScript('Bn/Js/jquery.validate.min.1.7.js');
		$this->addScript("Bn/Js/jquery.livequery.1.1.0.js");
		$this->addScript("Bn/Js/jquery.ajaxify.2.0.js");

		$this->addScript("Bn/Js/jquery.history.js");

		//$jqgridVersion = '3.6.2';
		$jqgridVersion = '3.8.1';
		$this->addScript('Bn/Js/i18n/grid.locale-' . $locale . '-' . $jqgridVersion  . '.js');
		$this->addScript("Bn/Js/jquery.jqGrid.min-" . $jqgridVersion  . ".js");
		$this->addStyleSheet('../Themes/' . $theme . '/ui.jqgrid-' . $jqgridVersion . '.css', 'text/css', 'screen');

		$this->addScript('Bn/Js/Lang/messages_' . $locale . '.js');
		$this->addScript('Bn/Js/i18n/ui.datepicker-' . $locale . '.js');
		$this->addScript('Bn/Js/jquery.maskedinput-1.2.2.js');
		$this->addScript('Bn/Js/jquery.tooltip.js');

		$this->addScript('Bn/Js/swfobject.js');

		$this->addStyleSheet('Bn/Style/Tooltip/jquery.tooltip.css', 'text/css', 'screen');

		//$this->addScript('http://www.google-analytics.com/ga.js');

$script = '
try {
var pageTracker = _gat._getTracker("UA-3845459-1");
pageTracker._trackPageview();
} catch(err) {}';
		//$this->addScriptDeclaration($script);

		$this->addStyleSheet('Bn/Style/Bn.css', 'text/css', 'screen');

		if (!is_null($aMsg)) $this->setErrorMsg($aMsg);
	}

	/**
	 * Enregistre un message
	 * @param   string     $Masg    contenu du message
	 * @return void
	 */
	public function addMsg($aMsg)
	{
		$this->_errMsg[] = $aMsg;
	}

	/**
	 * Complete un attribut
	 * @param    string  $aName		Nom de l'attribute
	 * @param    string  $aValue	Valeur de l'attribut
	 * @return   string
	 */
	public function completeAttribute($aName, $aValue = null)
	{
		$aName = strtolower($aName);
		if (is_null($aValue)) {
			$aValue = $aName;
		}
		$current = $this->getAttribute($aName);
		if (is_null($l_o_current))
		{
			$attribs[$aName] = $aValue;
		}
		else
		{
			$attribs[$aName] = "{$current} {$aValue}";
		}
		$this->updateAttributes($attribs);
		return $attribs[$aName];
	}

	/**
	 * Sets the value of the attribute
	 * @param    string  $aName		Nom de l'attribute
	 * @param    string  $aValue	Valeur de l'attribut
	 * @return   void
	 */
	public function setAttribute($aName, $aValue = null)
	{
		$aName = strtolower($aName);
		if (is_null($aValue))
		{
			$aValue = $aName;
		}
		$attrib[$aName] = $aValue;
		$this->setAttributes($attrib);
	}

	/**
	 * Fixe le message d'erreur a afficher a l'affichage de la page
	 *
	 */
	public function setErrorMessage($aMsg)
	{
		if ( !empty($aMsg) )
		{
			if ( defined("$aMsg") )
			{
				$str = addslashes(constant("$aMsg"));
			}
			elseif ( defined("$$aMsg") )
			{
				$str = addslashes(constant("$$aMsg"));
			}
			else
			{
				$str = addslashes($aMsg);
			}
			$atts['onload'] = "displayMsg('$str');";
			$this->setAttributes($atts);
		}
	}

	/**
	 * Ajoute un message de trace en tete de page
	 * L'affichage depend de la configuration
	 * @param   integer	$aLevel		Niveau de la trace
	 * @param   string	$aMsg    	Message a afficher
	 * @param   string  $aLabel     Label devant le message
	 * @return  void
	 */
	public function addTrace($aLevel, $aMsg, $aLabel = null)
	{
		$this->_traceMsg[] = array($aLevel, $aMsg, $aLabel);
	}

	/**
	 * Affichage de la page
	 * @return void
	 */
	public function display()
	{
		//Construction des messages
		if (count($this->_errMsg))
		{
			$ctrlFct = "function getMsg(msgId)\n{\nvar msg = new Object();\n";
			foreach($this->_errMsg as $msg)
			{
				$str = '';
				if (defined("$msg"))
				{
					$str = constant("$msg");
				}
				$ctrlFct .= "msg['{$msg}']=\"$str\";\n";
			}
			$ctrlFct .= "return msg[msgId];\n}\n";
			$this->addScriptDeclaration($ctrlFct);
		}

		//Creation du script de la fonction Ready de JQuery
		$functions = array();
		foreach ($this->_body as $elt)
		{
			if (is_object($elt) && method_exists($elt, 'getJQueryFunctions'))
			{
				$elt->getJQueryFunctions($functions);
			}
		}
		if (count($functions))
		{
			$ctrlFct = "$(document).ready(function() { \n";
			foreach($functions as $function)
			{
				$ctrlFct .= $function." \n";
			}
			$ctrlFct .= "}); \n";
			$this->addScriptDeclaration($ctrlFct);
		}

		// Affichage de la page
		HTML_Page2::display();
	}
}
?>