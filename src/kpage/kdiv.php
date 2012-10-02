<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kdiv.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.4 $
!   Mailto     : cage@free.fr
!   Revised by : $Author: cage $
******************************************************************************/
require_once "kcpt.php";

/**
 * Un composant de type Division permet de structurer la
 * page en regroupant les informations. Les �l�ments contenus
 * dans un composant division sont dit passifs: le visiteur de
 * la page ne peut saisir des informations dans la zone d�limit�e
 * par la division. En revanche, les �l�ments d'une division peuvent
 * avoir un ou plusieurs liens vers d'autres pages. Si le lien
 * r�f�rence une page externe il utilisera la m�thode addUrl de 
 * l'�l�ment. En revanche si le lien r�f�rence une autre kPage,
 * on utilisera la methode setAction de l'�l�ment.
 *
 * Techniquement, un composant Division g�n�re un couple de balises 
 * 'div', '/div' en HTML.
 *
 * @author Gerard CANTEGRIL
 * @see kPage, kForm
 */
class kDiv extends kCpt
{

  // {{{ addBtn()
  /**
   * @brief 
   * @~english Add a bouton element.
   * @~french Ajout d'un �l�ment de type Bouton.
   *
   * @par Description:
   * @~french Un �l�ment de type bouton permet d'afficher un bouton avec un 
   * label pour que l'utilisateur puisse d�clencher une action.
   * Si une variable du nom de l'�l�ment existe, son contenu est affich� comme 
   * label de l'�l�ment, sinon le nom de l'�l�ment est affich�e comme label. 
   * Par d�faut un bouton rappelle la m�me page dans la m�me fen�tre. Pour 
   * modifier le comportement par d�faut, utiliser la m�thode setActions().
   *
   * @~english
   * See french doc
   *
   * @param  $labelId  (string)    @~french nom du bouton.
   *         @~english bouton name.
   * @param  $fctId    (string)    @~french nom de la fonction � appeler
   * lors de l'activation du bouton.
   *         @~english name of fonction fired.
   * @param  $pageId   (integer)   @~french num�ro de la page � afficher
   *         @~english number of page to display
   * @param  $dataId   (integer)   @~french donn�e additionnelle
   *         @~english more data
   * @return @~french Pointeur sur le nouvel �l�ment Bouton. 
   *         @~english Pointer on new element.
   *
   * @~
   * @see kbtn, setActions
   * @par Exemple:
   * @code
   *	$kform = new kform('sample') ;
   *	$kform->addBtn( 'Nouveau') ;
   *	$actions = array( 1 => array(KAF_NEWWIN, 'new', KID_NEW, 0, 600, 600));
   *    $kform->setActions("Valider", $actions);
   * @endcode
   */
  function &addBtn($labelId, $fctId, $pageId, $actId=KID_NONE, $dataId=0,
		   $width=0, $height=0)
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($labelId);
      if ($elt)
      {
	$this->addErr("kDiv->addBtn: object already exists: $labelId");
	return $elt;
      }

      require_once "kbtncss.php";
      $this->_objs[$labelId] = new kBtncss($labelId);
      
      $actions[1] = array($fctId, $pageId, $actId, 
			  $dataId, $width, $height);
      $this->_objs[$labelId]->setActions($actions);	
      return $this->_objs[$labelId];
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
	$this->addErr("kDiv->addCombo: object already exists: $labelId");
	return $elt;
      }

      $attribs = array( 'class'  => 'kMandatory',
			'name'   => $labelId,
			'id'     => $labelId,
			'value'  => $values,
			'select' => $select);
      require_once "kcombo.php";
      $this->_objs[$labelId] = new kCombo($labelId, $attribs);
      return $this->_objs[$labelId];
    }
  // }}}



  // {{{ addDraw()
  /**
   * @brief 
   * @~english Add a draw
   * @~french Ajout d'un composant de type KO.
   *
   * @par Description:
   * @~french Un composant de type KO permet d'afficher un tableau 
   * par elimination directe
   *
   * @~english
   * See french doc.
   *
   * @param  $formId  (string)    @~french nom du composant.
   *         @~english Component name.
   * @param  $page  (string)    @~french nom de la page a charger
   *         @~english Name of the next page.
   * @param  $action  (integer) @~french action a accomplir
   *         @~english New action
   * @return @~french Pointeur sur le nouveau composant. 
   *         @~english Pointer on new component.
   *
   * @~
   * @see kForm, addDiv, addMenu, addWng
   * @par Exemple:
   * @code
   *    $kpage = new kPage('maPage');
   *    $kpage->addForm('form');
   * @endcode
   */
  function &addDraw($drawId, $nbQualif=1)
    {
      $elt = $this->isExist($drawId);
      if ($this->isExist($drawId))
      {
	$this->addErr("kDiv->addDraw: objet already exists $drawId");
	return $elt;
      }
      require_once "kdraw.php";
      $this->_objs[$drawId] = new kDraw($drawId, $nbQualif);
      return $this->_objs[$drawId];
    }

  // {{{ addForm()
  /**
   * @brief 
   * @~english Add an form
   * @~french Ajout d'un composant de type Formulaire.
   *
   * @par Description:
   * @~french Un composant de type Formulaire possede plusieur items. 
   * Il est utilis� lorsque des informations doivent �tre saisies par
   * l'utilisateur.
   *
   * @~english
   * See french doc.
   *
   * @param  $formId  (string)    @~french nom du composant.
   *         @~english Component name.
   * @param  $page  (string)    @~french nom de la page a charger
   *         @~english Name of the next page.
   * @param  $action  (integer) @~french action a accomplir
   *         @~english New action
   * @return @~french Pointeur sur le nouveau composant. 
   *         @~english Pointer on new component.
   *
   * @~
   * @see kForm, addDiv, addMenu, addWng
   * @par Exemple:
   * @code
   *    $kpage = new kPage('maPage');
   *    $kpage->addForm('form');
   * @endcode
   */
  function &addForm($formId, $page='', $action=KAF_NONE)
    {
      $elt = $this->isExist($formId);
      if ($this->isExist($formId))
      {
	$this->addErr("kDiv->addForm: objet already exists $formId");
	return $elt;
      }
      require_once "kform.php";
      $this->_objs[$formId] = new kForm($this, $formId, $page, $action);
      return $this->_objs[$formId];
    }
  
  // {{{ addInfo()
  /**
   * @brief 
   * @~english Add a Message
   * @~french Ajout d'un �l�ment de type Message.
   *
   * @par Description:
   * @~french Un �l�ment de type Message permet d'afficher du 
   * texte informatif. 
   * Si le contenu du message est renseign�, il est affich�. Sinon
   * si une variable du nom du message existe, son contenu est affich�
   * comme contenu du message, sinon le nom du message est affich�. 
   * Le message ne pourra pas �tre recup�r�e lorsque la page sera envoy�e   
   *
   * @~english
   * See french doc.
   *
   * @param  $msgId  (string)    @~french nom de l'�l�ment. 
   *         @~english element name.
   * @param  $msg    (string)    @~french contenu du message.
   *         @~english Message content
   * @return @~french Pointeur sur le nouvel �l�ment. 
   *         @~english Pointer on new element.
   *
   * @~
   * @see kMsg
   * @par Exemple:
   * @code
   *    $kform = new kform('maForm');
   *    $kform->addMsg('Message', "C'est bien mieux avec");
   * @endcode
   */
  function &addInfo($msgId, $msg='')
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->_page->isExist($msgId);
      if ($elt)
      {
	$this->addErr("kDiv->addInfo: element already exist $msgId");
	return $elt;
      }

      $attribs = array( 'value'  => $msg);
      require_once "kinfo.php";
      $this->_objs[$msgId] = new kInfo($msgId, $attribs);
      $this->_objs[$msgId]->setClass('kInfo');
      return $this->_objs[$msgId];
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

  // {{{ addInput()
  /**
   * @brief 
   * @~english Add an non edit input field element.
   * @~french Ajout d'un �l�ment de type input non �ditable. 
   *
   * @par Description:
   * @~french Un �l�ment de type Input permet d'afficher une zone de saisie 
   * non editable. Elle pourra �tre recup�r�e 
   * lorsque la page sera envoy�e.
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
  function &addInput($labelId, $value = "", $size="5")
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($labelId);
      if ($elt)
      {
	$this->addErr("kForm->addInfoArea: object already exists: $labelId");
	return $elt;
      }

      $attribs = array( 'type'  => 'text',
			'name'  => $labelId,
			'id'    => $labelId,
			'readonly' => 'readonly',
			'disable'  => 'disable',
			'value' => $value,
			'size'  => $size);

      require_once "kinput.php";
      $this->_objs[$labelId] = new kInput($labelId, $attribs);
      $this->_objs[$labelId]->inForm();
      return  $this->_objs[$labelId];
    }
  // }}}

  // {{{ addObject()
  /**
   * @brief 
   * @~english Add a object element.
   * @~french Ajout d'un �l�ment de type Objet.
   *
   * @par Description:
   * @~french Un �l�ment de type bouton permet d'afficher un bouton avec un 
   * label pour que l'utilisateur puisse d�clencher une action.
   * Si une variable du nom de l'�l�ment existe, son contenu est affich� comme 
   * label de l'�l�ment, sinon le nom de l'�l�ment est affich�e comme label. 
   * Par d�faut un bouton rappelle la m�me page dans la m�me fen�tre. Pour 
   * modifier le comportement par d�faut, utiliser la m�thode setActions().
   *
   * @~english
   * See french doc
   *
   * @param  $labelId  (string)    @~french nom du bouton.
   *         @~english bouton name.
   * @param  $fctId    (string)    @~french nom de la fonction � appeler
   * lors de l'activation du bouton.
   *         @~english name of fonction fired.
   * @param  $pageId   (integer)   @~french num�ro de la page � afficher
   *         @~english number of page to display
   * @param  $dataId   (integer)   @~french donn�e additionnelle
   *         @~english more data
   * @return @~french Pointeur sur le nouvel �l�ment Bouton. 
   *         @~english Pointer on new element.
   *
   * @~
   * @see kbtn, setActions
   * @par Exemple:
   * @code
   *	$kform = new kform('sample') ;
   *	$kform->addBtn( 'Nouveau') ;
   *	$actions = array( 1 => array(KAF_NEWWIN, 'new', KID_NEW, 0, 600, 600));
   *    $kform->setActions("Valider", $actions);
   * @endcode
   */
  function &addObject($labelId, $attribs, $content=array())
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($labelId);
      if ($elt)
      {
	$this->addErr("kDiv->addObject: object already exists: $labelId");
	return $elt;
      }

      require_once "kobject.php";
      $this->_objs[$labelId] = new kObject($labelId, $attribs, $content);
      return $this->_objs[$labelId];
    }
  // }}}

  /**
   */
  function &addIframe($labelId, $attribs, $content=array())
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($labelId);
      if ($elt)
      {
	$this->addErr("kDiv->addiframe: object already exists: $labelId");
	return $elt;
      }

      require_once "kiframe.php";
      $this->_objs[$labelId] = new kIframe($labelId, $attribs, $content);
      return $this->_objs[$labelId];
    }

    
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
      $elt = $this->_page->isExist($rowsId);
      if ($elt)
      {
	$this->addErr("kDiv->addRows: element already exist $rowsId");
	return $elt;
      }
      require_once "krows.php";
      $this->_objs[$rowsId] = new krows($rowsId, $rows, $this->_name);
      $this->_objs[$rowsId]->setImgPath($this->_imgPath);
      $sort = kform::getSort($rowsId);
      $this->_objs[$rowsId]->setSort($sort); 
      $this->_objs[$rowsId]->displaySelect(false);
      return $this->_objs[$rowsId];
    }
  // }}}
  
    function addText($aText)
    {
    	$this->_objs['text'] = $aText;
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
      $www =  "<!-- $this->_name -->\n";
      $www .= "<div id=\"$this->_name\"";
      if ($this->_class != '') $www .= " class=\"$this->_class\"";
      if ($this->_title != '') $www .= " title=\"$this->_title\"";
      $www .= ">\n";
      echo $www;

      // Display the objects of the division
      kcpt::display($file);

      // End the  html division 
      echo "</div>\n<!-- end $this->_name -->\n\n";
    }
  // }}}

  // {{{ constructor
  /**
   * @brief 
   * @~english Constructor
   * @~french  Constructeur
   *
   * @par Description:
   * @~french Constructeur de l'objet Division. Il ne faut pas cr�er
   * directement des objets Division. Seul l'objet kPage est autoris�
   * a cr�er des Divisions.
   *
   * @~english
   * See french doc.
   *
   * @param  $page  (string)    @~french Objet Page contenant la division
   *         @~english Page object
   * @param  $name  (string)    @~french nom de la division
   *         @~english Division name
   * @return @~french Aucun
   *         @~english None
   * @~
   * @see kPage, addDiv
   * @private
   */
  function kDiv($page, $name, $class)
    {
      $this->_name = $name;
      $this->_type = "Div";
      $this->_page = $page;
      $this->_class = $class;
    }
  // }}}
  
  //-----------------------------------------
  //  Here are private methode
  //------------------------------------------
}

?>