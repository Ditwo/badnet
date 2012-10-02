<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kform.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.9 $
!   Mailto     : cage@free.fr
!   Revised by : $Author: cage $
******************************************************************************/
require_once "kcpt.php";

/**
 * Classe de base pour la creation de formulaires de saisies
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 * @todo
 * Des am�liorations a amener :
 *  @li D�placer la m�thode noMandatory dans la classe elt
 *  @li D�placer la m�thode setLength dans les classes concern�es
 *  @li D�placer la m�thode setMaxLength dans les classes concern�es
 *
 */
class kForm extends kCpt
{
  
  // {{{ properties
  /**
   * List of hidden element of the form
   *
   * @private
   */
  var $_hideElts;
    
  /**
   * List of blocs of the form
   *
   * @private
   */
  var $_blocs;
  
  /**
   * Width of the form
   *
   * @private
   */
  var $_formWidth = '100%';
  
  /**
   * Name of the form
   *
   * @private
   */
  var $_name = '';
  
  /**
   * Page to load
   *
   * @private
   */
  var $_submitPage = '';

  /**
   * Action of the form
   *
   * @private
   */
  var $_actId = KID_NONE;

  /**
   * Fontion 
   *
   * @private
   */
  var $_fnct = "";
  
  /**
   * Image path of the form
   *
   * @private
   */
  var $_imgPath = "kpage/img/";
  
  
  /**
   * Flags for javascript fonctions
   *
   * @private
   */
  var $_msg = array();

  /**
   * Flags for javascript fonctions
   *
   * @private
   */
  var $_defForm;

  /**
   * Flags for javascript fonctions
   *
   * @private
   */
  var $_defPage;
  
  /**
   * Data receive from old form
   *
   * @private
   */
  var $_data =0;

  /**
   * Edit with the focus
   *
   * @private
   */
  var $_defFocus='';

  /**
   * title of the form
   *
   * @private
   */
  var $_title='';
  
  /**
   * vrai si le formulaire doit permettre l'upload de fichier
   *
   * @private
   */
  var $_upload = false;
  var $_action = '';
  
 
  // }}}
  
  // {{{ addArea()
  /**
   * @brief 
   * @~english Add an Area element.
   * @~french Ajout d'un �l�ment de type Area dans un objet kform. 
   *
   * @par Description:
   * @~french Un �l�ment de type Area permet d'afficher une zone de saisie 
   * multi-ligne pr�c�d�e d'un label. La zone de saisie est initialis�e 
   * avec la valeur de l'argument $value. Un ascenceur vertical permet 
   * de faire d�filer le texte saisi.
   * Si une variable du nom de l'�l�ment existe, son contenu est affich� comme 
   * label de l'�l�ment, sinon le nom de l'�l�ment est affich�e comme label. 
   * @~english
   * See french doc.
   *
   * @param  $labelId  (string)    @~french nom de l'�l�ment. 
   *         @~english element name.
   * @param  $value    (string)    @~french valeur initiale affich�e. 
   *         @~english inital value.
   * @param  $cols     (integer)   @~french nombre de colonnes de la zone
   * de saisie. 
   *         @~english number of column.
   * @param  $rows     (integer)   @~french nombre de lignes de la zone
   * de saisie. 
   *         @~english number of line.
   * @return @~french Pointeur sur le nouvel �l�ment. 
   *         @~english Pointer on new element.
   *
   * @~
   * @see karea, getInput, addInfoArea, addEdit, noMandatory
   * @par Exemple:
   * @code
   *    $kform = new kform('maForm');
   *    $karea =& $kform->addArea('Commentaire :', 'Texte par defaut', 50, 60);
   * @endcode
   */
  function &addArea($labelId, $value = "", $cols="20", $rows="5" )
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($labelId);
      if ($elt)
      {
	$this->addErr("kForm->addArea: object already exists: $labelId");
	return $elt;
      }

      if ($this->_defFocus =='')
	$this->_defFocus = $labelId;
      $attribs = array( 'class' => 'kMandatory',
			'name'  => $labelId,
			'id'    => $labelId,
			'cols'  => $cols,
			'rows'  => $rows);

      require_once "karea.php";
      $this->_objs[$labelId] = new kArea($labelId, $attribs, $value);
      $this->_objs[$labelId]->inForm();
      return  $this->_objs[$labelId];
    }
  // }}}

  // {{{ addBlock()
  /**
   * @brief 
   * @~english Add a bloc to group element.
   * @~french Ajout d'un �l�ment de type Bloc.
   *
   * @par Description:
   * @~french Un �l�ment de type Bloc permet de regrouper plusieurs �l�ments
   * ensemble. Par d�faut, un bloc est entour� d'un cadre. 
   * Si une variable du nom de l'�l�ment existe, son contenu est affich� comme 
   * titre du bloc, sinon le bloc n'a pas de titre.
   *
   * @~english
   * See french doc
   *
   * @param  $blkName  (string)    @~french nom du bloc.
   *         @~english bloc name.
   * @param  $elts     (string or array)    @~french nom de l'�l�ment 
   * (string) ou tableau des noms des �l�ments contenus dans le bloc.
   *         @~english name or array of elements to add into the bloc.
   * @return @~french Pointeur sur le nouvel �l�ment. 
   *         @~english Pointer on new element.
   *
   * @~
   * @see kblok
   * @par Exemple:
   * @code
   *   $kform = new kform('sample');
   *   $kform->addEdit( 'Nom');
   *   $kform->addEdit( 'Prenom');
   *   $kform->addEdit( 'Licence');
   *   $elts = array('Nom', 'Prenom', 'Licence');
   *   $kbloc = & $kform->addBloc('monBloc ", $elts);
   * @endcode
   */
  function &addBlock($blkName, $elts, $class='')
    {
      if (isset( $this->_objs[$blkName] ))
	$this->addErr("kForm->addBloc: object already exists: $blkName");

      
      require_once "kblock.php";
      $this->_objs[$blkName] = new kBlock($blkName, $class);
      $this->_objs[$blkName]->inForm();
      if (is_array($elts))
	{
	  foreach ($elts as $num =>$name)
	    {
	      $this->_addEltToBlock($blkName, $name);
	    }
	}
      else
	{
	  $this->_addEltToBlock($blkName, $elts);
	}
      return $this->_objs[$blkName];
    }
  // }}}

    
  // {{{ addCheck()
  /**
   * @brief 
   * @~english Add a checked button element.
   * @~french Ajout d'un �l�ment de type Case a cocher.
   *
   * @par Description:
   * @~french  Un �l�ment de type Case � cocher permet d'afficher 
   * une case � cocher suivie d'un label. La case � cocher permet 
   * � l'utilisateur de choisir des options. 
   * Si une variable du nom de l'�l�ment existe, son contenu est affich� comme 
   * label de l'�l�ment, sinon le nom de l'�l�ment est affich�e comme label. 
   *
   * @~english
   * See french doc
   *
   * @param  $checkId  (string)    @~french nom de la case � cocher.
   *         @~english check button name.
   * @param  $select   (boolean)   @~french indique si la case est coch�e.
   *         @~english is the box checked
   * @return @~french Pointeur sur le nouvel �l�ment case � cocher.
   *         @~english Pointer on new element.
   *
   * @~
   * @see kcheck, getInput, setActions
   * @par Exemple:
   * @code
   *	$kform = new kform('sample') ;
   *	$kform->addCheck('Simple') ;
   *	$kform->addCheck('Double') ;
   *	$kform->addCheck('Mixte') ;
   * @endcode
   */
  function &addCheck($checkId, $select=false)
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($checkId);
      if ($elt)
      {
	$this->addErr("kForm->addCheck: object already exists: $checkId");
	return $elt;
      }

      $attribs = array( 'class'  => 'kRadio',
			'type'   => 'checkbox',
			'name'   => $checkId,
			'id'     => $checkId,
			'select' => $select,
			'value'  => 1);
      require_once "kcheck.php";
      $this->_objs[$checkId] = new kCheck($checkId, $attribs);
      $this->_objs[$checkId]->inForm();
      return $this->_objs[$checkId];
    }
  // }}}
  
  // {{{ addCombo()
  /**
   * @brief 
   * @~english Add a list box with a label
   * @~french Ajout d'un �l�ment de type Combo.
   *
   * @par Description:
   * @~french  Un �l�ment de type Combo est un ensemble de valeurs 
   * s�lectionnables par l'utilisateur, pr�sent�es dans une liste 
   * d�roulante. La m�thode setLength permet d'obtenir une liste de 
   * longueur fixe plut�t qu'une liste d�roulante. La m�thode 
   * setActions() permet d'associer une action ex�cut�e d�s que 
   * l'utilisateur change la valeur s�lectionn�e. 
   * Si une variable du nom de l'�l�ment existe, son contenu est affich�
   * comme label de l'�l�ment, sinon le nom de l'�l�ment est affich�e 
   * comme label. 
   *
   * @~english
   * See french doc
   *
   * @param  $labelId  (string)  @~french nom de la liste
   *         @~english list name.
   * @param  $values   (array)   @~french items de la liste
   *         @~english list items
   * @param  $select   (string)  @~french items selectionn�s par d�faut
   *         @~english selected item
   * @return @~french Pointeur sur le nouvel �l�ment liste.
   *         @~english Pointer on new element.
   *
   * @~
   * @see kcombo, getInput, setActions, noMandatory, setLength
   * @par Exemple:
   * @code
   *	$kform = new kform('sample') ;
   *	$values = array ( 1 => 'un ', 2=>'deux', 5=>'cinq', 9=>'neuf');
   *	$kcombo =& $kform->addCombo('maCombo ', $values, 'cinq');
   *	$actions = array( 1 => array(KAF_UPLOAD, 'sample', KID_USER+1));
   *    $kform->setActions('maCombo', $actions);
   * @endcode
   */
  function &addCombo($labelId, $values, $select="")
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($labelId);
      if ($elt)
      {
	$this->addErr("kForm->addCombo: object already exists: $labelId");
	return $elt;
      }

      $attribs = array( 'class'  => 'kMandatory',
			'name'   => $labelId,
			'id'     => $labelId,
			'value'  => $values,
			'select' => $select);
      require_once "kcombo.php";
      $this->_objs[$labelId] = new kCombo($labelId, $attribs);
      $this->_objs[$labelId]->inForm();
      return $this->_objs[$labelId];
    }
  // }}}

  // {{{ addEdit()
  /**
   * @brief 
   * @~english Add an edit box with a label
   * @~french Ajout d'un �l�ment de type Edit.
   *
   * @par Description:
   * @~french  Un �l�ment de type Edit permet d'afficher une zone de
   * saisie pr�c�d�e d'un label. La zone de saisie est initialis�e avec 
   * la valeur de l'argument $value. La valeur saisie par l'utilistateur
   * pourra �tre recup�r�e lorsque la page sera envoy�e.
   * Si une variable du nom de l'�l�ment existe, son contenu est affich� 
   * comme label sinon le nom de l'�l�ment est affich�e. 
   *
   * @~english
   * See french doc
   *
   * @param  $labelId  (string)  @~french nom de la zone
   *         @~english edit box name.
   * @param  $value    (string)  @~french valeur affich�e par d�faut
   * dans la zone d'�dition
   *         @~english Default value of the edit box
   * @param  $size     (integer)  @~french longueur de la zone d'�dition 
   * en nombre de caract�res.
   *         @~english length of the edit box
   * @return @~french Pointeur sur le nouvel �l�ment liste.
   *         @~english Pointer on new element.
   *
   * @~
   * @see kedit, getInput, setMaxLength, noMandatory, setLength
   * @par Exemple:
   * @code
   *	$kform = new kform('sample') ;
   *	$kedit =& $kform->addEdit('Nom :', 'toto', 20) ;
   * @endcode
   */
  function &addEdit($labelId, $value = "", $size="30" )
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($labelId);
      if ($elt)
      {
	$this->addErr("kForm->addEdit: object already exists: $labelId");
	return $elt;
      }

      if ($this->_defFocus =='')
	$this->_defFocus = $labelId;
      $attribs = array( 'class' => 'kMandatory',
			'type'  => 'text',
			'name'  => $labelId,
			'id'    => $labelId,
			'value' => $value,
			'size'  => $size,
			'form'  => $this->_name);
      require_once "kedit.php";
      $this->_objs[$labelId] = new kEdit($labelId, $attribs);
      $this->_objs[$labelId]->inForm();
      return  $this->_objs[$labelId];
    }
  // }}}
  
  // {{{ addFile()
  /**
   * @brief 
   * @~english Add an file upload element
   * @~french Ajout d'un �l�ment de type File pour l'upload de fichier.
   *
   * @par Description:
   * @~french  Un �l�ment de type File permet d'afficher un bouton "parcourir" et une zone de
   * saisie pr�c�d�e d'un label. La zone de saisie est initialis�e avec 
   * la valeur de l'argument $value. La valeur saisie par l'utilistateur ou r�cuperer par la boite de dialogue
   * pourra �tre recup�r�e lorsque la page sera envoy�e.
   * Si une variable du nom de l'�l�ment existe, son contenu est affich� 
   * comme label sinon le nom de l'�l�ment est affich�e. 
   *
   * @~english
   * See french doc
   *
   * @param  $labelId  (string)  @~french nom de la zone
   *         @~english edit box name.
   * @param  $value    (string)  @~french valeur affich�e par d�faut
   * dans la zone d'�dition
   *         @~english Default value of the edit box
   * @param  $size     (integer)  @~french longueur de la zone d'�dition 
   * en nombre de caract�res.
   *         @~english length of the edit box
   * @return @~french Pointeur sur le nouvel �l�ment liste.
   *         @~english Pointer on new element.
   *
   * @~
   * @see kedit, getInput, setMaxLength, noMandatory, setLength
   * @par Exemple:
   * @code
   *	$kform = new kform('sample') ;
   *	$kedit =& $kform->addFile('Nom :', 'toto', 20) ;
   * @endcode
   */
  function &addFile($labelId, $value = "", $size="30" )
    {
      // ajout option upload aux formulaire
      $this->_upload = true;	
      
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($labelId);
      if ($elt)
      {
	$this->addErr("kForm->addFile: object already exists: $labelId");
	return $elt;
      }
      
      if ($this->_defFocus =='')
	$this->_defFocus = $labelId;
      $attribs = array( 'class' => 'kMandatory',
			'type'  => 'file',
			'name'  => $labelId,
			'id'    => $labelId,
			'value' => $value,
			'size'  => $size);
      require_once "kedit.php";
      $this->_objs[$labelId] = new kEdit($labelId, $attribs);
      $this->_objs[$labelId]->inForm();
      return  $this->_objs[$labelId];
    }
  // }}}

  // {{{ addHide()
  /**
   * @brief 
   * @~english Add an hidden edit bo
   * @~french Ajout d'un �l�ment de type Edit cach�e.
   *
   * @par Description:
   * @~french  Un �l�ment de type Edit cach�e permet de cr�er une zone
   * de saisie invisible. La zone de saisie est initialis�e avec la valeur
   * de l'argument $value. Les zones de saisie invisibles sont utilis�e 
   * pour m�moriser des valeurs n�cessaires au traitement ult�rieur du 
   * formulaire sans que l'utilisateur y ait acc�s. La valeur
   * pourra �tre recup�r�e lorsque la page sera envoy�e.   
   *
   * @~english
   * See french doc
   *
   * @param  $labelId  (string)  @~french nom de la zone d'edition cach�e
   *         @~english Hidden edit box name.
   * @param  $value    (string)  @~french valeur de la zone d'�dition
   *         @~english Value of the edit box
   * @return @~french Pointeur sur le nouvel �l�ment liste.
   *         @~english Pointer on new element.
   *
   * @~
   * @see khide, getInput
   * @par Exemple:
   * @code
   *	$kform = new kform('sample') ;
   *	$kedit =& $kform->addHide('UserId', '123') ;
   * @endcode
   */
  function &addHide($labelId, $value ="" )
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($labelId);
      if ($elt)
      {
	//$this->addErr("kForm->addHide: object already exists: $labelId");
	return $elt;
      }

      /* Count for previous radio with this name */
      if (substr($labelId, -2) == '[]' &&
	  count($this->_objs))
	{
	  $nb = 1;
          foreach( $this->_objs as $elt)
	    {
	      if (preg_match('/' . $labelId . "[1-9]{1}/", $elt->_name)) $nb++;
	    }
	  $name = $labelId.$nb;
	}
      else
	$name = $labelId;

      $attribs = array( 'type'  => 'hidden',
			'name'  => $labelId,
			'id'    => $labelId,
			'value' => $value);
      require_once "khide.php";
      $this->_objs[$name] = new kHide($name, $attribs);
      $this->_hideElts[$name] = $this->_objs[$name];
      $this->_objs[$name]->inForm();
      return $this->_objs[$name];
    }
  // }}}

  // {{{ addInfo()
  /**
   * @brief 
   * @~english Add an information with a label
   * @~french Ajout d'un champ non �ditable avec un label.
   *
   * @par Description:
   * @~french  Un �l�ment de type Info permet de cr�er une zone 
   * de saisie non modifiable pr�c�d�e d'un label. La zone de saisie
   * est initialis�e avec la valeur de l'argument $value. La valeur
   * pourra �tre recup�r�e lorsque la page sera envoy�e.
   * Si une variable du nom de l'�l�ment existe, son contenu est affich� 
   * comme label sinon le nom de l'�l�ment est affich�e. 
   *
   * @~english
   * See french doc
   *
   * @param  $labelId  (string)  @~french nom de l'�l�ment.
   *         @~english Element name.
   * @param  $value    (string)  @~french contenu a afficher dans la zone
   *         @~english Value to display
   * @return @~french Pointeur sur le nouvel �l�ment.
   *         @~english Pointer on new element.
   *
   * @~
   * @see kedit, getInput, noMandatory
   * @par Exemple:
   * @code
   *	$kform = new kform('sample') ;
   *	$kinfo =& $kform->addInfo('Creation', '15/03/2003') ;
   * @endcode
   */
  function &addInfo($labelId, $value = "")
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($labelId);
      if ($elt)
      {
	$this->addErr("kForm->addInfo: object already exists: $labelId");
	return $elt;
      }

      $attribs = array( 'class' => 'kInfo',
			'type'  => 'hidden',
			//'type'  => 'text',
			//'disabled'  => 'disabled',
			'name'  => $labelId,
			'id'    => $labelId,
			'value' => $value);
      require_once "kedit.php";
      $this->_objs[$labelId] = new kEdit($labelId, $attribs);
      $this->_objs[$labelId]->noMandatory();
      $this->_objs[$labelId]->inForm();
      return $this->_objs[$labelId];
    }
  // }}}

  // {{{ addInfoArea()
  /**
   * @brief 
   * @~english Add an non edit Area element.
   * @~french Ajout d'un �l�ment de type Area non �ditable. 
   *
   * @par Description:
   * @~french Un �l�ment de type Area permet d'afficher une zone de saisie 
   * multi-ligne pr�c�d�e d'un label. La zone de saisie est initialis�e 
   * avec la valeur de l'argument $value. Elle pourra �tre recup�r�e 
   * lorsque la page sera envoy�e. Un ascenceur vertical permet 
   * de faire d�filer le texte saisi.
   * Si une variable du nom de l'�l�ment existe, son contenu est affich� comme 
   * label de l'�l�ment, sinon le nom de l'�l�ment est affich�e comme label. 
   *
   * @~english
   * See french doc.
   *
   * @param  $labelId  (string)    @~french nom de l'�l�ment. 
   *         @~english element name.
   * @param  $value    (string)    @~french valeur initiale affich�e. 
   *         @~english inital value.
   * @param  $cols     (integer)   @~french nombre de colonnes de la zone
   * de saisie. 
   *         @~english number of column.
   * @param  $rows     (integer)   @~french nombre de lignes de la zone
   * de saisie. 
   *         @~english number of line.
   * @return @~french Pointeur sur le nouvel �l�ment. 
   *         @~english Pointer on new element.
   *
   * @~
   * @see karea, getInput, addArea, addEdit
   * @par Exemple:
   * @code
   *    $kform = new kform('maForm');
   *    $karea =& $kform->addInfoArea('Commentaire :', 'Texte par defaut', 
   *                                  50, 60);
   * @endcode
   */
  function &addInfoArea($labelId, $value = "", $cols="20", $rows="5" )
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($labelId);
      if ($elt)
      {
	$this->addErr("kForm->addInfoArea: object already exists: $labelId");
	return $elt;
      }

      $attribs = array( 'class' => 'kInfo',
			'name'  => $labelId,
			'id'    => $labelId,
			'readonly'    => 'readonly',
			'cols'  => $cols,
			'rows'  => $rows);
      require_once "karea.php";
      $this->_objs[$labelId] = new kArea($labelId, $attribs, $value);
      $this->_objs[$labelId]->inForm();
      return  $this->_objs[$labelId];
    }
  // }}}


  // {{{ addPwd()
  /**
   * Print a password box with a label
   *
   * @param  string  $labelId Id of the label to print
   * @param  string  $value   Value of the edit box
   * @param  string  $msg     Additionnal message
   * @param  integer $size    Size of the edit zone
   * @return void
   */
  function &addMenu($labelId, $items="", $select, $class='')
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->_page->isExist($labelId);
      if ($elt)
      {
	$this->addErr("kCpt->addMenu: element deja existant $labelId");
	return $elt;
      }

      require_once "kmenu.php";
      $this->_objs[$labelId] = new kMenu($labelId, $items, $select, $class);
      $this->_objs[$labelId]->setImgPath($this->_imgPath);
      $this->_objs[$labelId]->inForm();
      return $this->_objs[$labelId];
    }
  // }}}

  

  // {{{ addPwd()
  /**
   * Print a password box with a label
   *
   * @param  string  $labelId Id of the label to print
   * @param  string  $value   Value of the edit box
   * @param  string  $msg     Additionnal message
   * @param  integer $size    Size of the edit zone
   * @return void
   */
  function &addPwd($labelId, $value = "", $size="30" )
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($labelId);
      if ($elt)
      {
	$this->addErr("kForm->addPwd: object already exists: $labelId");
	return $elt;
      }
      $attribs = array( 'class' => 'kMandatory',
			'type'  => 'password',
			'name'  => $labelId,
			'id'    => $labelId,
			'value' => $value,
			'size'  => $size);
      require_once "kedit.php";
      $this->_objs[$labelId] = new kEdit($labelId, $attribs);
      $this->_objs[$labelId]->inForm();
      return $this->_objs[$labelId];
    }
  // }}}
  
  // {{{ addRadio()
  /**
   * Add a radio button
   *
   * @access public
   * @param  string  $msgId Id of the message to print
   * @return void
   */
  function &addRadio($radioId, $select=false, $value = "")
    {
      /* Count for previous radio with this name */
      $nb = 1;
      if (count($this->_objs))
	{
          foreach( $this->_objs as $elt => $val)
	    {
	      if (preg_match("/" . $radioId . "[1-9]{1}/", $elt)) $nb++;
	    }
	}
      /* Now the name of the radio element is ufixed with the number */
      $attribs = array( 'class'   => 'kRadio',
			'type'   => 'radio',
			'name'   => $radioId,
			'id'     => $radioId.$nb,
			'select' => $select,
			'value'  => $value,
			'label'  => $radioId.$nb);
      $name = $radioId.$nb;
      require_once "kradio.php";
      $this->_objs[$name] = new kradio($name, $attribs);
      $this->_objs[$name]->inForm();
      return $this->_objs[$name];
    }
  // }}}
  
  // {{{ addRows()
  /**
   * Add some rows
   *
   * @access public
   * @param  string  $msgId Id of the rows
   * @return void
   */
  function &addRows($rowsId, $rows)
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($rowsId);
      if ($elt)
      {
	$this->addErr("kForm->addRows: object already exists: $rowsId");
	return $elt;
      }

      require_once "krows.php";
      $this->_objs[$rowsId] = new krows($rowsId, $rows, $this->_name);
      $this->_objs[$rowsId]->setImgPath($this->_imgPath);
      $sort = $this->getSort($rowsId);
      $this->_objs[$rowsId]->setSort($sort);
      $this->_objs[$rowsId]->inForm();
      return $this->_objs[$rowsId];
    }
  // }}}
  
  // {{{ addGrid()
    /**
     * Add some table
     *
     * @access public
     * @param  string  $gridId Id of the grid
     * @return void
     */
    function &addGrid($gridId, $table)
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($gridId);
      if ($elt)
	{
	  $this->addErr("kForm->addGrid: object already exists: $gridId");
	  return $elt;
	}

      require_once "kgrid.php";
      $this->_objs[$gridId] = new kgrid($gridId, $table, $this->_name);
      $this->_objs[$gridId]->setImgPath($this->_imgPath);
      $this->_objs[$gridId]->inForm();
      return $this->_obj[$gridId];
    }
    // }}}

  // {{{ getData()
  /**
   * Return the ID of the current data
   *
   *
   * @access public
   * @return integer
   */
  function getData()
    {
      if (isset($_POST['kdata']))
	$data =  $_POST['kdata'];
      else if (isset($GLOBALS['HTTP_POST_VARS']['kdata']))
	$data = $GLOBALS['HTTP_POST_VARS']['kdata'];
      else if (isset($_GET['kdata']))
	$data =   $_GET['kdata'];
      else if (isset($GLOBALS['HTTP_GET_VARS']['kdata']))
	$data =  $GLOBALS['HTTP_GET_VARS']['kdata'];
      else
	$data = NULL;
      if ($data=="undefined")
	$data = NULL;
      return $data;
    }
  // }}}

  // {{{ getFirst()
  /**
   * Return the last first row displayed
   *
   *
   * @param string $name  name of the rows
   * @return string
   */
  function getFirst($elt)
    { 
      $name = "Kfirst$elt";
      if (function_exists('version_compare') &&
	  version_compare(phpversion(), '4.1', 'ge'))
        {
	  if (isset($_POST[$name]))
	    return  intval($_POST[$name]);
	  if (isset($_GET[$name]))
	    return  intval($_GET[$name]);
	}
      else
	{
	  if (isset($GLOBALS['HTTP_POST_VARS'][$name]))
	    return  intval($GLOBALS['HTTP_POST_VARS'][$name]);
	  if (isset($GLOBALS['HTTP_GET_VARS'][$name]))
	    return  intval($GLOBALS['HTTP_GET_VARS'][$name]);
	}
      return 0;
    }
  // }}}

  // {{{ getActId()
  /**
   * Return the action 
   *
   *
   * @return string
   */
  function getActId()
    { 
      if (function_exists('version_compare') &&
	  version_compare(phpversion(), '4.1', 'ge'))
        {
	  if (isset($_POST['kaid']))
	    return  $_POST['kaid'];
	  if (isset($_GET['kaid']))
	    return  $_GET['kaid'];
	}
      else
	{
	  if (isset($GLOBALS['HTTP_POST_VARS']['kaid']))
	    return  $GLOBALS['HTTP_POST_VARS']['kaid'];
	  if (isset($GLOBALS['HTTP_GET_VARS']['kaid']))
	    return  $GLOBALS['HTTP_GET_VARS']['kaid'];
	}
      return 0;
    }
  // }}}

  // {{{ getInput()
  /**
   * Get the attribut with the post or get
   *
   * @access public
   * @param  string  $attribut to return
   * @return mixed   value of the attribute
   */
  function getInput($attribut, $default='')
    {
      $res = kform::getInputHtml($attribut);
      if (is_array($res))
	return $res;
      if ($res=='') return $default;

      return htmlspecialchars($res);
    }
  // }}}

  // {{{ getInputHtml()
  /**
   * Get the attribut with the post or get
   *
   * @access public
   * @param  string  $attribut to return
   * @return mixed   value of the attribute
   */
  function getInputHtml($attribut, $default='')
    {
      $value = $default;

      if (function_exists('version_compare') &&
	  version_compare(phpversion(), '4.1', 'ge'))
        {
	  if (isset($_GET[$attribut]))
	    $value = $_GET[$attribut];
	  else if (isset($_POST[$attribut]))
	    $value = $_POST[$attribut];
	  else if (isset($_FILES[$attribut]))
	    $value = $_FILES[$attribut];
	}
      else
	{
	  if (isset($GLOBALS['HTTP_GET_VARS'][$attribut]))
	    $value = $GLOBALS['HTTP_GET_VARS'][$attribut];
            
	  else if (isset($GLOBALS['HTTP_POST_VARS'][$attribut]))
	    $value = $GLOBALS['HTTP_POST_VARS'][$attribut];

	  else if (isset($GLOBALS['HTTP_POST_FILES'][$attribut]))
	    $value = $GLOBALS['HTTP_POST_FILES'][$attribut];
	}
      if (get_magic_quotes_gpc() && !is_array($value))
	return stripslashes($value); 
      else
	return $value;
    }
  // }}}

  // {{{ getPageId()
  /**
   * Return the ID of the page of the current kForm
   *
   * @return string
   */
  function getPageId()
    { 
      if (function_exists('version_compare') &&
	  version_compare(phpversion(), '4.1', 'ge'))
        {
	  if (isset($_POST['kpid']))
	    return  $_POST['kpid'];
	  if (isset($_GET['kpid']))
	    return  $_GET['kpid'];
	}
      else
	{
	  if (isset($GLOBALS['HTTP_POST_VARS']['kpid']))
	    return  $GLOBALS['HTTP_POST_VARS']['kpid'];
	  if (isset($GLOBALS['HTTP_GET_VARS']['kpid']))
	    return  $GLOBALS['HTTP_GET_VARS']['kpid'];
	}
      return '';
    }
  // }}}

  // {{{ getSort()
  /**
   * Return the last sort column
   *
   * @param string $name  name of the rows
   * @param integer $default  num of frfault column
   * @return string
   */
  function getSort($elt='', $default=1)
    { 
      $name = "kSort$elt";
      if (function_exists('version_compare') &&
	  version_compare(phpversion(), '4.1', 'ge'))
        {
	  if (isset($_POST[$name]))
	    return  intval($_POST[$name]);
	  if (isset($_GET[$name]))
	    return  intval($_GET[$name]);
	}
      else
	{
	  if (isset($GLOBALS['HTTP_POST_VARS'][$name]))
	    {
	      return  intval($GLOBALS['HTTP_POST_VARS'][$name]);
	    }
	  if (isset($GLOBALS['HTTP_GET_VARS'][$name]))
	    {
	      return  intval($GLOBALS['HTTP_GET_VARS'][$name]);
	    }
	}
      $_POST[$name] = $default;
      return $default;
    }
  // }}}

  // {{{ constructor
  /**
   * Constructor. 
   *
   * @param string $name     name of the form 
   * @param string $page     page of the form
   * @access public
   * @return void
   */
  //  function KForm($name, $page=KID_DEFAULT)
  function kForm($page, $name, $toSubmit, $actId=KAF_NONE)
    {
      //objet page contenant la form
      $this->_page = $page;

      $this->_name = $name;
      if ($toSubmit=='')
	$this->_submitPage = $name;
      else	
	$this->_submitPage = $toSubmit;
      $this->_actId  = $actId;

      $this->setTitle($name);
    }
  // }}}
  
  // {{{ noMandatory()
  /**
   * A field is not mandatory
   *
   * @param  string  $name    Name of the edit box
   * @return void
   */
  function noMandatory($name)
    {
      if (isset( $this->_objs[$name] ))
	$this->_objs[$name]->noMandatory();
      else 
	$this->addErr("kForm->noMandatory: object don't exist: $name");
    }
  // }}}
  
  
  // {{{ setCommand()
  /**
   * set the command to naviguate in a rows.  For rows only.
   *
   * @access public
   * @param integer $size
   * @return void
   */
  function setCommand($name, $first, $last, $max)
    { 	
      if (isset( $this->_objs[$name] ))
	$this->_objs[$name]->setCommand($first, $last, $max);
      else 
	$this->addErr("kForm->setCommand : object alredy exist: $name");
    }
  // }}}
  
  // {{{ setActId()
  /**
   * Fix the current action
   *
   *
   * @access public
   * @return string
   */
  function setActId($form)
    { 
      $_POST['kaid'] = $form;
    }
  // }}}

  // {{{ setData()
  /**
   * Fix the current action
   *
   *
   * @access public
   * @return string
   */
  function setData($value)
    { 
      $_POST['kdata'] = $value;
    }
  // }}}

  // {{{ setLength()
  /**
   * Modified the length or size of the element
   *
   * @access public
   * @param  string   $name    Name of the edit box
   * @param  integer $size    New size
   * @return void
   */
  function setLength($name, $size)
    {
      if ($name == $this->_name) $this->_formWidth = $size; 
      else if (isset( $this->_objs[$name] ))
	$this->_objs[$name]->setLength($size);
      else 
	$this->addErr("kForm->setLength: object don't exists: $name");
    }
  // }}}
  
  // {{{ setMaxLength()
  /**
   * fixed the maximum lenght of the element (edit box)
   *
   * @access public
   * @param  string   $name    Name of the edit box
   * @param  integer $size     New max length
   * @return void
   */
  function setMaxLength($name, $size)
    {
      if (isset( $this->_objs[$name] ))
	$this->_objs[$name]->setMaxLength($size);
      else 
	$this->addErr("kForm->setMaxLength: object don't exists: $name");
    }
  // }}}
  
  // {{{ setPageId()
  /**
   * Fix the current page
   *
   *
   * @access public
   * @return string
   */
  function setPageId($page)
    { 
      $_POST['kpid'] = $page;
    }
  // }}}
  
  // {{{ setPerms()
  /**
   * Set the authorization for the element
   *
   * @access public
   * @param  string   $name    Name of the element
   * @param  array    $perms   permission of the element
   * @return void
   */
  function setPerms($name, $perms)
    {
      if (isset( $this->_objs[$name] ))
	$this->_objs[$name]->setPerms($perms);
      else
	$this->addErr("kForm->setPerms: object don't exists: $name");

    }
  // }}}
  
  // {{{ setSubTitle()
  /**
   * Fixe the  subtitle of the form
   *
   * @access public
   * @param  string  $titleId Id of the title to print
   * @return void
   */
  function setSubTitle($titleId)
    {
      $this->_subTitle = $titleId;
    }
  // }}}
  
  // {{{ setTitle()
  /**
   * Fixe the  title of the form
   *
   * @access public
   * @param  string  $titleId Id of the title to print
   * @return void
   */
  function setTitle($titleId)
    {
      $this->_title = $titleId;
    }

  // {{{ setAction()
  /**
   * Fixe the  action of the form
   *
   * @access public
   * @param  string  $titleId Id of the title to print
   * @return void
   */
  function setAction($action)
    {
      $this->_action = $action;
    }
  

  //-----------------------------------------
  //  Here are protected methods
  //  You don't have to use this method. These
  //  are only called by kPage
  //------------------------------------------

  // {{{ display()
  /**
   * @brief 
   * @~english Print the division
   * @~french  Affiche le composant Division
   *
   * @par Description:
   * @~french Affiche le composant Division et tous ses �l�ments. Cette
   * m�thode ne doit pas �tre appel�e directement. Elle est appel�e par
   * l'objet kPage lors de la demande d'affichage de la page.
   *
   * @~english
   * See french doc.
   *
   * @param  $file  (string)    @~french fichier contenant les chaines
   * de caracteres
   *         @~english filename for strings
   * @return @~french Aucun
   *         @~english None
   * @private
   */
  function display($file)
    {
      
      // Start a html division 
      $this->_stringFile = $file;

      echo "<!-- form $this->_name -->\n";
      echo "<form id=\"$this->_name\" ";
      if ($this->_upload == true) echo " enctype=\"multipart/form-data\" ";
      if ($this->_actId != KAF_NONE)
	{
	  echo "onsubmit=\"return valid(this, '{$this->_submitPage}',";
	  echo " '{$this->_actId}');\" ";
	}
      if (!empty($this->_action))
	echo "method=\"post\" action=\"{$this->_action}\">\n";
      else if (isset($GLOBALS['PHP_SELF']))
	echo "method=\"post\" action=\"$GLOBALS[PHP_SELF]\">\n";
      else
	echo "method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">\n";
      $this->addHide('kpid');
      $this->addHide('kaid');
      $this->addHide('kdata');

      if (defined("SID") && (SID != ''))
        $this->addHide('PHPSESSID', session_id());

      $title = $this->getLabel($this->_title);
      if( $title != $this->_title)
	{
	  echo "<div class=\"kTitle\"><p id=\"titleForm\">$title</p></div>\n";
	}
      // Display the objects of the division
      kcpt::display($file);

      // End the  html division 
      echo "</form><!-- end form $this->_name -->\n\n";
      if ($this->_defFocus != '')
	{
	  echo "<script type=\"text/javascript\">\n<!--\n\n";
	  echo "document.forms[\"$this->_name\"].elements[\"";
	  echo "$this->_defFocus\"].focus();\n";
	  echo "// -->\n";
	  echo "</script>\n\n";
	}
    }
  // }}}
 
  
  //-----------------------------------------
  //  Here are private methode
  //------------------------------------------

  // {{{ _addEltToBlock
  /**
   * Add an element to a bloc
   *
   * @private
   * @param  string   $name    Name of the bloc
   * @param  array    $elts    Name of the elements of the bloc
   * @return void
   */
  function _addEltToBlock($blkName, $name)
    {
      if (isset($this->_menus[$name]))
        { 
	  $this->_objs[$blkName]->addElt($name, $this->_menus[$name]);
	  unset($this->_menus[$name]);
        }
      
      if (isset($this->_objs[$name]))
        { 
	  $this->_objs[$blkName]->addElt($name, $this->_objs[$name]);
	  unset($this->_objs[$name]);
        }
      
      else
        {
	  foreach ( $this->_objs as $id => $elt)
	    {
	      if (preg_match('/' . $name . "[1-9]{1}/", $id) )
		{
		  $this->_objs[$blkName]->addElt($id, $elt);
		  unset($this->_objs[$id]);
		}
	    }
        }
    }
  // }}}
}

?>