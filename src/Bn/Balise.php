<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'HTML/Common.php';

class Bn_Balise extends HTML_Common
{
	private $_balise  = '';       // Balise html
	private $_balise_ = '';       // Fin de balise html
	private $_imgPath = '';       // Chemin des images
	protected $_body    = array();  // Contenu de l'element
	private $_isMandatory  = false;  // La saisie est-elle obligatoire
	protected $_jQueryFunction  = array();  // tableau contenant les fonctions JQuery
	private $_messagebox = null; //boite de message lie a l'objet
	private $_expandBody = array();  // Chaine ou objet a integrer dans le body
	private $_metaData = array();    // Meta data

	/**
	 * Constructeur
	 * @param 	string	$aBalise	Nom de la balise
	 * @param 	string	$aId		Identifiant de la balise
	 * @param 	mixed	$aBody		Contenu de la balise
	 * @return	Bn_balise
	 */
	public function __construct($aBalise=null, $aId = null, $aBody = null, $aClass=null)
	{
		if (!empty($aBalise) && $aBalise[strlen($aBalise)-1] == '/' )
		{
			$this->_balise = substr($aBalise, 0, -1);
		}
		else
		{
			$this->_balise =  $aBalise;
			$this->_balise_ =  $aBalise;
		}
		if ( ! is_null($aBody) )
		{
			$this->_body[] = $aBody;
		}
		if (!empty($aId))
		{
			$this->setAttribute('id', $aId);
		}
		if (!empty($aClass))
		{
			$this->setAttribute('class', $aClass);
		}
	}

	/**
	 * Ajout d'une zone de saisie multiligne avec un label
	 * @param 	string 		$aName		Identifiant de la zone
	 * @param 	string		$aLabel		Label precedent la zone
	 * @param 	mixed		$aValue		Contenu de la balise
	 * @return 	Area
	 */
	public function addArea($aName, $aLabel=null, $aValue=null)
	{
		require_once "Bn/Balise/Area.php";
		$elt = new Area($aName, $aLabel, $aValue);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une balise
	 * @param 	string		$aBalise	Nom de la balise
	 * @param 	string		$aId		Identifiant de la balise
	 * @param 	mixed		$aContent	Contenu de la balise
	 * @return 	Bn_balise
	 */
	public function addBalise($aBalise, $aId=null, $aContent=null, $aClass=null)
	{
		$elt = new Bn_balise($aBalise, $aId, $this->_getLocale($aContent), $aClass);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout une saut de ligne force
	 * @return	Bn_balise
	 */
	public function addBreak()
	{
		$elt = new Bn_balise('p');
		$elt->setAttribute('style', 'clear:both;margin:0;padding:0;');
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'un bouton
	 * @param	integer	$aName		Identifiant du bouton
	 * @param   string	$aLabel		Texte du boutton
	 * @param 	integer	$aAction	Action lors de l'utilisation du bouton
	 * @param 	string	$aImg		Image du boutton
	 * @return 	Button
	 */
	public function addButton($aName, $aLabel, $aAction=0, $aImg=null, $aTarget=null)
	{
		require_once "Bn/Balise/Button.php";
		$btn = new Button($aName, $aLabel, $aAction, $aImg);
		if ( $aAction )
		{
			if ( !empty($aTarget) )
			{
				$btn->addMetadata('target', "'#$aTarget'");
				$btn->addMetadata('bnAction', $aAction);
				$btn->completeAttribute('class', 'btnAjax');
			}
			else $btn->setAction($aAction);
		}
		$this->_body[] = $btn;
		return $btn;
	}

	/**
	 * Ajout d'un bouton
	 * @param	integer	$aName		Identifiant du bouton
	 * @param   string	$aLabel		Texte du boutton
	 * @param 	integer	$aAction	Action lors de l'utilisation du bouton
	 * @param 	string	$aImg		Image du boutton
	 * @return 	Button
	 */
	public function addButtonGoto($aName, $aLabel, $aAction=0, $aImg=null)
	{
		require_once "Bn/Balise/Button.php";
		$elt = new Button($aName, $aLabel, $aAction,  $aImg);
		$elt->setAction($aAction);
		$elt->completeAttribute('class', 'btnGoto');
		$this->_body[] = $elt;
		return $elt;
	}
	
	/**
	 * Ajout d'un bouton cancel
	 * @param   string		$aName		Nom du bouton
	 * @param 	string		$aLabel		Label du bouton
	 * @param   integer		$aAction 	Action a effectuer
	 * @return 	Button
	 */
	public function addButtonCancel($aName, $aLabel, $aAction=null, $aTarget=null)
	{
		$btn = $this->addButton($aName, $aLabel, $aAction, 'close', $aTarget);
		$btn->completeAttribute('class', 'btnClose');
		return $btn;
	}

	/**
	 * Ajout d'un bouton submit
	 * @param 	string	$aName		Nom du bouton
	 * @param 	string	$aLabel		Label du bouton
	 * @param 	integer	$aAction	Action a effectuer
	 * @return 	Button
	 */
	public function addButtonValid($aName, $aLabel, $aImg='check')
	{
		if (empty($aName)) $aName = 'btnValid';
		$btn = $this->addButton($aName, $aLabel, null, $aImg);
		$btn->setAttribute('type', 'submit');
		return $btn;
	}

	// {{{
	/**
	* Ajout d'une case a cocher avec un label
	* @param 	string	$aName		Nom de la case a cocher
	* @param 	string	$aLabel		Label de la case
	* @param 	bool	$aChecked	Indicateur de selection
	* @param 	integer	$aAction	Action a effectuer
	* @return 	Checkbox
	*/
	public function addCheckbox($aName, $aLabel, $aValue = null, $aChecked=false, $aAction = null)
	{
		require_once "Bn/Balise/Checkbox.php";
		$elt = new Checkbox($aName, $aLabel, $aValue, $aChecked);
		if ( !empty($aAction) )
		{
			$elt->setAction($aAction);
		}
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajoute un contenu a la balise. Soit une chaine, soit un objet
	 * avec une methode toHtml
	 * @param	mixed		$aContent		Contenu a ajouter
	 * @param 	bool		$aLocale		Indicateur de traduction
	 * @return 	void
	 */
	public function addContent($aContent, $aLocale=true)
	{
		if ($aLocale && is_string($aContent))
		{
			$this->_body[] = $this->_getLocale($aContent);
		}
		else
		{
			$this->_body[] = $aContent;
		}
	}

	/**
	 * Ajout d'une boite de message
	 * @param 	string	$aName		Nom de la boite de dialogue
	 * @param 	string	$aTitle		Titre de la boite de dialogue
	 * @param 	integer	$aWidth		Largeur de la boite
	 * @param 	integer	$aHeight	Hauteur de la boite
	 * @return 	Dialog
	 */
	public function addDialog($aName, $aTitle, $aWidth, $aHeight)
	{
		require_once "Bn/Balise/Dialog.php";
		$elt = new Dialog($aName, $aTitle, $aWidth, $aHeight);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une DIV
	 *
	 * @param 	string		$aId		Identifiant de la div
	 * @param 	string		$aClass		Classe de la div
	 * @param	mixed		$aContent	Contenu
	 *
	 * @return	Bn_balise
	 */
	public function addDiv($aId=null, $aClass=null, $aContent=null)
	{
		$elt = new Bn_balise('div');

		if (!empty($aId) ) $elt->setAttribute('id', $aId);
		if ( !is_null($aClass) ) $elt->setAttribute('class', $aClass);

		if ( !is_null($aContent) )
		{
			if ( is_numeric($aContent) && !empty($aId) )
			{
				$elt->setAction($aContent);
				$elt->completeAttribute('class', 'divAjax');
			}
			else $elt->addContent($aContent);
		}

		$this->_body[] = $elt;
		return $elt;
	}

	public function addDynlist($aName)
	{
		require_once "Bn/Balise/Dynlist.php";
		$elt = new Dynlist($aName);
		$this->_body[] = $elt;
		return $elt;
	}

	public function addDynpref($aName)
	{
		require_once "Bn/Balise/Dynpref.php";
		$elt = new Dynpref($aName);
		$this->_body[] = $elt;
		return $elt;
	}
	
	/**
	 * Ajout d'une DIV complexe (trois div imbriquee pour pouvoir faire de l'habillage)
	 *
	 * @param 	string		$aId		Identifiant de la div
	 * @param 	string		$aClass		Classe de la div
	 * @param	mixed		$aContent	Contenu
	 *
	 * @return	Bn_balise
	 */
	public function addRichDiv($aId=null, $aClass=null, $aContent=null)
	{
		if ( empty($aClass) ) $aClass = 'bn-rich-div';

		$div = new Bn_balise('div', $aId . '1');
		$div->setAttribute('class', $aClass . '1');
		$div1 = $div->addDiv($aId . 2, $aClass . '2');
		$elt = $div1->addDiv($aId, $aClass);

		if ( !is_null($aContent) )
		{
			if ( is_numeric($aContent) && !empty($aId) )
			{
				$elt->setAction($aContent);
				$elt->completeAttribute('class', 'divAjax');
			}
			else $elt->addContent($aContent);
		}

		$this->_body[] = $div;
		return $elt;
	}


	/**
	 * Ajout d'une zone de saisie avec un label
	 * @param 	string		$aName		Nom et identifiant de la zone de saisie
	 * @param 	string		$aLabel		Label de la zone
	 * @param 	string		$aValue		Valeur initiale
	 * @param	integer		$aMaxchar	Nombre max de caractere
	 * @return	Edit
	 */
	public function addEdit($aName, $aLabel=null, $aValue=null, $aMaxchar=20)
	{
		require_once "Bn/Balise/Edit.php";
		$elt = new Edit($aName, $aLabel, $aValue, $aMaxchar);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une zone de saisie de type date avec un label
	 * @param 	string		$aName		Nom et identifiant de la zone de saisie
	 * @param 	string		$aLabel		Label de la zone
	 * @param 	string		$aValue		Valeur initiale
	 * @return	DateEdit
	 */
	public function addEditDate($aName, $aLabel=null, $aValue=null)
	{
		require_once "Bn/Balise/DateEdit.php";
		$elt = new DateEdit($aName, $aLabel, $aValue);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une zone de saisie avec un label pour une adresse email
	 * @param 	string		$aName		Nom et identifiant de la zone de saisie
	 * @param 	string		$aLabel		Label de la zone
	 * @param 	string		$aValue		Valeur initiale
	 * @param	integer		$aMaxchar	Nombre max de caractere
	 * @return	Edit
	 */
	public function addEditEmail($aName, $aLabel=null, $aValue=null, $aMaxchar=15)
	{
		require_once "Bn/Balise/Edit.php";
		$elt = new Edit($aName, $aLabel, $aValue, $aMaxchar);
		$elt->email();
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une zone de saisie avec un label pour un telephone
	 * @param 	string		$aName		Nom et identifiant de la zone de saisie
	 * @param 	string		$aLabel		Label de la zone
	 * @param 	string		$aValue		Valeur initiale
	 * @param	integer		$aMaxchar	Nombre max de caractere
	 * @return	Edit
	 */
	public function addEditPhone($aName, $aLabel=null, $aValue=null, $aMaxchar=15)
	{
		require_once "Bn/Balise/Edit.php";
		$elt = new Edit($aName, $aLabel, $aValue, $aMaxchar);
		$elt->phone();
		$this->_body[] = $elt;
		return $elt;
	}
	
	
	/**
	 * Ajout d'une zone de saisie avec un label pour la saisie d'un fichier
	 * @param 	string		$aName		Nom et identifiant de la zone de saisie
	 * @param 	string		$aLabel		Label de la zone
	 * @return	Edit
	 */
	public function addEditFile($aName, $aLabel=null)
	{
		require_once "Bn/Balise/Edit.php";
		$elt = new Edit($aName, $aLabel, '', 255);
		$l_o_input = $elt->getInput();
		$l_o_input->setAttribute('type', 'file');
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une zone de saisie numeric avec un label
	 * @param 	string		$aName		Nom et identifiant de la zone de saisie
	 * @param 	string		$aLabel		Label de la zone
	 * @param 	string		$aValue		Valeur initiale
	 * @param	integer		$aMin		Valeur minimale
	 * @param	integer		$aMax		Valeur maximale
	 * @return	IntEdit
	 */
	public function addEditInt($aName, $aLabel, $aValue, $aMin=null, $aMax=null)
	{
		require_once "Bn/Balise/IntEdit.php";
		$elt = new IntEdit($aName, $aLabel, $aValue, $aMin, $aMax);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une zone de saisie avec un label pour un mot de passe
	 * @param 	string		$aName		Nom et identifiant de la zone de saisie
	 * @param 	string		$aLabel		Label de la zone
	 * @param	integer		$aMaxchar	Nombre max de caractere
	 * @param	string		$aControl	Nom de la zone de controle
	 * @return	Edit
	 */
	public function addEditPwd($aName, $aLabel=null, $aMaxchar=15, $aControl=null)
	{
		require_once "Bn/Balise/Edit.php";
		$elt = new Edit($aName, $aLabel, '', $aMaxchar);
		$input = $elt->getInput();
		$input->setAttribute('type', 'password');
		$elt->equalTo($aControl);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une zone de saisie avec un label pour un url
	 * @param 	string		$aName		Nom et identifiant de la zone de saisie
	 * @param 	string		$aLabel		Label de la zone
	 * @param	string		$aValue		Contenu initial
	 * @param	integer		$aMaxchar	Nombre max de caractere
	 * @return	Edit
	 */
	public function addEditUrl($aName, $aLabel=null, $aValue=null, $aMaxchar=15)
	{
		require_once "Bn/Balise/Edit.php";
		$elt = new Edit($aName, $aLabel, $aValue, $aMaxchar);
		$elt->url();
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'un fieldset
	 * @param 	string		$aName		Nom du fieldset
	 * @param 	string		$aLegende	Legende
	 * @param	string		$aClass		Classe
	 * @return	Fieldset
	 */
	public function addFieldset($aName, $aLegende, $aClass=null)
	{
		require_once "Bn/Balise/Fieldset.php";
		$elt = new Fieldset($aName, $aLegende, $aClass);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une form
	 * @param 	string		$aName		Nom de la form
	 * @param 	integer		$aAction	Action
	 * @param 	string		$aTarget	Div cible
	 * @param	string		$aCallback	Callback javascript (submitForm par defaut)
	 * @param	boolean		$aAjax		Submit ajax ou normal
	 * @return	Form
	 */
	public function addForm($aName, $aAction, $aTarget=null, $aCallback=null, $aAjax = true)
	{
		require_once "Bn/Balise/Form.php";
		$elt = new Form($aName, $aAction, $aTarget);
		if ( !empty($aCallback)) $elt->addOption('submitHandler', $aCallback);
		else if ($aAjax) $elt->addOption('submitHandler', 'submitForm');
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une grid view
	 * @param 	string		$aName		Nom de la grid
	 * @param 	integer		$aAction	Action
	 * @param	integer		$aNumrow	Nombre de lignes a renvoyer
	 * @param 	bool		$aPager		Indicateur d'affichage du pager
	 * @return	GridView
	 */
	public function addGridview($aName,$aAction,$aNumrow=20, $aPager=true)
	{
		require_once "Bn/Balise/GridView.php";
		$elt = new Gridview($aName, $aAction, $aNumrow, $aPager);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une zone cache
	 * @param 	string		$aName		Nom et identifiant de la zone
	 * @param 	string		$aValue		Valeur initiale
	 * @return	Bn_balise
	 */
	public function addHidden($aName, $aValue=null)
	{
		$elt = new Bn_Balise('input/');

		$elt->setAttribute('type', 'hidden');
		$elt->setAttribute('name', $aName);
		$elt->setAttribute('id', $aName);
		if ( is_null($aValue) )
		{
			$elt->setAttribute('value', Bn::getValue($aName,''));
		}
		else
		{
			$elt->setAttribute('value', $aValue);
		}
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une image
	 * @param string	$aName		Identifiant de l'image
	 * @param string 	$aFile		Nom du fichier image
	 * @param string	$aAlt		Texte alternatif a afficher
	 * @param array		$aMaxsize	Taille maximale d'affichage : height, width
	 * @return 	Image
	 */
	public function addImage($aName, $aFilename, $aAlt, $aMaxsize=null)
	{
		require_once "Bn/Balise/Image.php";
		$elt = new Image($aName, $aFilename, $aAlt, $aMaxsize);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une zone non saisissable avec un label
	 * @param string	$aName		Identifiant de la zone
	 * @param string 	$aLabel		Label devant la zone
	 * @param string	$aValue		Texte a afficher
	 * @return 	Info
	 */
	public function addInfo($aName, $aLabel, $aValue='')
	{
		require_once "Bn/Balise/Info.php";
		$elt = new Info($aName, $aLabel, $aValue);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une zone d'affichage multiligne avec un label
	 * @param 	string		$aName		Identifiant de la zone
	 * @param 	string		$aLabel		Label affiche devant la zone
	 * @param 	mixed		$aContent	Contenu de la zone
	 * @return	InfoArea
	 */
	public function addInfoArea($aName, $aLabel=null, $aContent=null)
	{
		require_once "Bn/Balise/Infoarea.php";
		$elt = new Infoarea($aName, $aLabel, $aContent);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'un lien
	 * @param 	string		$aName		Identifiant du lien
	 * @param 	integer		$aAction	Action du lien
	 * @param 	mixed		$aContent	Contenu du lien
	 * @return Bn_balise
	 */
	public function addLink($aName, $aAction, $aContent=null, $aTarget=null)
	{
		require_once "Bn/Balise/Link.php";
		$elt = new Link($aName, $aAction, $aContent, $aTarget);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une liste
	 * @param 	string		$aName		Identifiant de la liste
	 * @param 	array		$aTexts		Texte des items de la liste
	 * @param 	array		$aLinks		Action des items de la liste
	 * @param 	integer		$aSelected	Numero de l'item selectionne
	 * @return Liste
	 */
	public function addList($aName, $aTexts, $aLinks, $aSelected=null, $aTarget=null)
	{
		require_once "Bn/Balise/List.php";
		$elt = new Liste($aName, $aTexts, $aLinks, $aSelected, $aTarget);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajoute un element Menu
	 * @param unknown_type $aId
	 * @return MenuItemJQuery
	 */
	public function addMenu($aId)
	{
		require_once 'Balise/Menu.php';
		require_once 'Balise/MenuItem.php';

		$l_o_menu = new MenuJQuery($aId);
		$this->addContent($l_o_menu);

		return $l_o_menu;
	}

	/**
	 * Ajout d'une metadata
	 * @param 	string	$aName	    Identifiant de la metadata
	 * @param	mixed	$aValue 	Valeur de la metadata
	 * @return  void
	 */
	public function addMetadata($aName, $aValue)
	{
		$this->_metaData[$aName] = $aValue;
	}

	/**
	 * Ajout d'un paragraphe
	 * @param 	string	$aId	    Identifiant du paragraphe
	 * @param	mixed	$aContent	Contenu du paragraphe
	 * @param 	string	$aClass		Classe du paragraphe
	 * @return Bn_balise
	 */
	public function addP($aId=null,$aContent=null, $aClass=null)
	{
		$elt = new Bn_Balise('p');

		if (!empty($aId) )
		{
			$elt->setAttribute('id', $aId);
		}
		if ( !is_null($aContent) )
		{
			$elt->addContent($aContent);
		}
		if ( !is_null($aClass) )
		{
			$elt->setAttribute('class', $aClass);
		}

		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'un pager
	 * @param 	string		$aName		Identifiant de la zone
	 * @param 	string		$aAction	Action du lien
	 * @param 	mixed		$aTarget	Cible du lien
	 * @return	InfoArea
	 */
	public function addPager($aName, $aAction, $aTarget)
	{
		require_once "Bn/Balise/Pager.php";
		$elt = new Pager($aName, $aAction, $aTarget);
		$this->_body[] = $elt;
		return $elt;
	}
	
	/**
	 * Ajout d'un bouton radio avec un label
	 * @param 	string	$aName		Nom du bouton
	 * @param 	string	$aId		Identifiant du bouton
	 * @param 	string	$aLabel		Label affiche
	 * @param 	string	$aValue		Valeur du bouton
	 * @param 	boolean	$aChecked	Indicateur de selection
	 * @return Radio
	 */
	public function addRadio($aName, $aId, $aLabel, $aValue, $aChecked=false)
	{
		require_once "Bn/Balise/Radio.php";
		$elt = new Radio($aName, $aId, $aLabel, $aValue, $aChecked);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'une liste de choix deroulante ou non
	 * @param 	string	$aName		Identifiant de la liste
	 * @param 	string	$aLabel		Label devant la liste
	 * @param	integer	$aAction	Action sur changement d'option
	 * @return Select
	 */
	public function addSelect($aName, $aLabel=null, $aAction=null, $aTarget=null)
	{
		require_once "Bn/Balise/Select.php";
		$elt = new Select($aName, $aLabel, $aAction, $aTarget);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'un tableau
	 * @param 	string	$aName		Identifiant de la table
	 * @return	Table
	 */
	public function addTable($aName)
	{
		require_once "Bn/Balise/Table.php";
		$elt = new Bn_Table($aName);
		$this->_body[] = $elt;
		return $elt;
	}

	/**
	 * Ajout d'un titre
	 * @param 	string	$aName		Identifiant du titre
	 * @param 	string	$aTitle		Contenu du titre
	 * @param 	integer	$aLevel		Niveau du titre
	 * @return Title
	 */
	public function addTitle($aName, $aTitle=null, $aLevel=1 )
	{
		$divName = empty($aName) ? "" : "div$aName";
		$elt = new Bn_Balise('div', $divName);
		$elt->setAttribute('class', 'classh'.$aLevel);
		$h = $elt->addBalise("h$aLevel", $aName);
		if (is_null($aTitle))
		{
			$h->addContent($aName);
		}
		else
		{
			$h->addContent($aTitle);
		}
		$this->_body[] = $elt;
		return $elt;
	}
	
	/**
	 * Ajout d'un message d'avertissement
	 * @param 	string	$aContent	Message a afficher
	 * @return Title
	 */
	public function addWarning($aContent)
	{
		$elt = new Bn_Balise('div');
		$elt->setAttribute('class', 'ui-widget');
		$div = $elt->addDiv('', 'ui-state-highlight ui-corner-all');
		$div->setAttribute('style', 'padding: 0pt 0.7em; margin-top: 10px;');
		$p = $div->addP();
		$span = $p->addBalise('span');
		$span->setAttribute('class', 'ui-icon ui-icon-info');
		$span->setAttribute('style', 'float: left; margin-right: 0.3em;');
		$p->addContent($aContent);
		$p->setAttribute('style', 'text-align:left;');
		$this->_body[] = $elt;
		return $elt;
	}
	
	/**
	 * Ajout d'un message d'erreur
	 * @param 	string	$aContent	Message a afficher
	 * @return Title
	 */
	public function addError($aContent)
	{
		$elt = new Bn_Balise('div');
		$elt->setAttribute('class', 'ui-widget');
		$div = $elt->addDiv('', 'ui-state-error ui-corner-all');
		$div->setAttribute('style', 'padding: 0pt 0.7em; margin-top: 10px;');
		$p = $div->addP();
		$span = $p->addBalise('span');
		$span->setAttribute('class', 'ui-icon ui-icon-alert');
		$span->setAttribute('style', 'float: left; margin-right: 0.3em;');
		$p->addContent($aContent);
		$p->setAttribute('style', 'text-align:left;');
		$this->_body[] = $elt;
		return $elt;
	}
	
	/**
	 * Ajout d'un tooltip
	 * @param 	string	$aContent		Contenu de la legende
	 * @return void
	 */
	public function setTooltip($aContent, $aBalise = null)
	{
		$this->setAttribute('title', $this->_getLocale($aContent));
		$this->completeAttribute('class', 'bn-tooltip');
		$id = $this->getAttribute('id');
		if ( is_null($aBalise) ) $bal = $this;
		else $bal = $aBalise;
		
		$bal->addJQReady('$("#' . $id . '").tooltip({'
		. 'track: true,'
		. 'delay: 800,'
		. 'showURL: false,'
		. 'opacity: 1,'
		. 'fixPNG: true,'
		. 'showBody: " - ",'
		. 'extraClass: "pretty fancy",'
		. 'top: 0,'
		. 'left: 0'
		.'});');
	}

	/**
	 * Complete un attribut
	 * @param 	string	$aName		Nom de l'attribut
	 * @param   string  $aValue     Valeur de l'attribut
	 * @return 	string
	 */
	public function completeAttribute($aName, $aValue = null)
	{
		$aName = strtolower($aName);
		if (is_null($aValue))
		{
			$aValue = $aName;
		}
		$l_o_current = $this->getAttribute($aName);
		if (is_null($l_o_current))
		{
			$l_as_att[$aName] = $aValue;
		}
		else
		{
			$l_as_att[$aName] = "{$l_o_current} {$aValue}";
		}
		$this->updateAttributes($l_as_att);
		return $l_as_att[$aName];
	}

	/**
	 * Ajout d'une balise en debut de contenu
	 * @param 	string	$aBalise	Nom de la balise
	 * @param 	string	$aName		Identifiant de la balise
	 * @param	mixed	$aContent	Contenu de la balise
	 * @return	Bn_balise
	 */
	public function insertBalise($aBalise, $aName=null, $aContent=null)
	{
		$elt = new Bn_Balise($aBalise, $aName, $this->_getLocale($aContent));
		array_unshift($this->_body, $elt);
		return $elt;
	}

	/**
	 * Ajoute un contenu en debut de balise. Soit une chaine, soit un objet
	 * avec une methode toHtml
	 * @param 	mixed	$aContent	Valeur du contenu
	 * @param 	boolean	$aLocale	Traduction du contenu
	 * @return	void
	 */
	public function insertContent($aContent, $aLocale=true)
	{
		if ($aLocale && is_string($aContent))
		{
			array_unshift($this->_body, $this->_getLocale($aContent) );
		}
		else
		{
			array_unshift($this->_body, $aContent);
		}
	}

	/**
	 * Renseigne l'action a effectuer sur un evenement
	 * @param 	integer	$aAction	Identifiant de l'action
	 * @return 	void
	 */
	public function setAction($aAction, $aAjax = true)
	{
		$args = explode('&', $aAction);
		$action = array_shift($args);
		$this->addMetadata('bnAction', $action);
		$this->addMetadata('ajax', $aAjax);
		foreach($args as $arg)
		{
			list($key, $value) = explode('=', $arg);
			$this->addMetadata($key, $value);
		}
	}

	/**
	 * Remplace les meta valeur pour le contenu
	 * @param	mixed	$aContent	Meta valeur
	 *
	 * @return 	void
	 */
	public function setMetaContent($aContent)
	{
		if ( is_array($aContent) )
		{
			$this->_expandBody = $aContent;
		}
		else
		{
			$this->_expandBody = array($aContent);
		}
	}

	/**
	 * Ajoute une meta valeur pour le contenu. Le contenu definit une meta valeur avec %bnxxx%
	 * "Monsieur %bnnomprenom% vous dit bonjour"
	 * @param	mixed	$aContent	Meta valeur
	 * @return 	void
	 */
	public function addMetaContent($aContent)
	{
		if ( is_array($aContent) )
		{
			$this->_expandBody = array_merge($this->_expandBody, $aContent);
		}
		else
		{
			$this->_expandBody[] = $aContent;
		}
	}

	/**
	 * Renvoi le contenu de la balise
	 * @name 	getContent
	 * @return 	array
	 */
	public function getContent()
	{
		return $this->_body;
	}

	/**
	 * Converti la balise et tout son contenu en chaine de caractere
	 * @name 	toHtml
	 */
	public function toHtml()
	{
		return $this->__toString();
	}

	/**
	 * Converti la balise et tout son contenu en chaine de caractere
	 * @name __toString
	 */
	public function __toString()
	{
		$lnEnd = $this->_getLineEnd();
		$html = '';
		$comment = $this->getComment();
		if (!empty($comment))
		{
			$strHtml .= '<!--' . $comment . '-->\n' .$lnEnd;
		}

		// Traitement des metadata
		if ( count($this->_metaData) )
		{
			$metadata = '{';
			$glue = '';
			foreach ($this->_metaData as $name=>$value)
			{
				$metadata .= $glue . $name . ':' . $value;
				$glue = ',';
			}
			$metadata .= '}';
			$this->completeAttribute('class', $metadata);
		}
		// Ecriture de la balise et de ses attributs
		if ( !empty($this->_balise) )
		{
			$html .= "<{$this->_balise}";
			$html .= $this->getAttributes(true);
			if ( empty($this->_balise_) )
			{
				$html .= " /";
			}
			$html .= ">";
		}

		// Ecriture du contenu
		foreach($this->_body as $elt)
		{
			if (is_object($elt))
			{
				$html .= $elt->toHtml();
			}
			else
			{
				// Ajout des meta data
				if ( count($this->_expandBody) )
				{
				 $exp = "/(%bn.*?%)/";
				 $res = preg_split($exp, $elt, -1, PREG_SPLIT_DELIM_CAPTURE);
				 $nb = 0;
				 foreach($res as $str)
				 {
				 	if ( preg_match($exp, $str) &&
				 	isset($this->_expandBody[$nb]) )

				 	{
				 		if ( is_object($this->_expandBody[$nb]) )
				 		{
				 			$html .= $this->_expandBody[$nb]->toHtml();
				 		}
				 		else
				 		{
				 			$class = preg_split('/%bn:(.*)%/', $str, -1, PREG_SPLIT_DELIM_CAPTURE);
				 			if (isset($class[1]) )
				 			{
				 				$html .= '<span class="' . $class[1] . '">'
				 				. $this->_expandBody[$nb]. '</span>';
				 			}
				 			else
				 			{
				 				$html .= $this->_expandBody[$nb];
				 			}
				 		}
					 	$nb++;
				 	}
				 	else
				 	{
				 		$html .= $str;
				 	}
				 }
				}
				else
				{
					$html .= "$elt";
				}
			}
		}

		// Fermeture de la balise
		if ( !empty($this->_balise_) )
		{
			// @todo avoir pour le javascript de la grid
			//$html .= "{$lnEnd}</{$this->_balise_}>{$lnEnd}";
			$html .= "</{$this->_balise_}>";
		}

		return $html;
	}

	/**
	 * _getLocale: renvoi un label dans la langue
	 * @param string $aLabel		Chaine a traduire
	 */
	public function _getLocale($aLabel)
	{
		if ( empty($aLabel) ) return $aLabel;
		if (!is_string($aLabel)) return $aLabel;
		$str = $aLabel;
		if (!strchr($str, '::') && defined($aLabel))
		{
			$str = constant($aLabel);
		}
		return nl2br(htmlentities($str, NULL, 'UTF-8'));
	}

	/**
	 * getJQueryFunctions
	 *
	 */
	public function getJQueryFunctions(&$aFunction)
	{
		foreach($this->_body as $elt)
		{
			if (is_object($elt))
			{
				$elt->getJQueryFunctions($aFunction);
			}
		}

		if (count($this->_jQueryFunction))
		{
			foreach($this->_jQueryFunction as $jq){
				$aFunction[] = $jq;
			}
		}
	}

	/**
	 * addJQReady
	 * @param	string		$aFunction 	Fonction javascript
	 * @param	integer		$aIndice 	Indice de la fonction
	 * @return 	integer
	 */
	public function addJQReady($aFunction, $aIndice=null)
	{
		if (is_null($aIndice))
		{
			$this->_jQueryFunction[] = $aFunction;
			$aIndice = count($this->_jQueryFunction)-1;
		}
		else
		{
			$this->_jQueryFunction[$aIndice] = $aFunction;
		}
		return $aIndice;
	}

}

?>