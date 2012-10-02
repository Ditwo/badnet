<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kcpt.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
!   Author     : G.CANTEGRIL
!   Mailto     : cage@free.fr
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
******************************************************************************
!   License   : Licensed under GPL [http://www.gnu.org/copyleft/gpl.html]
!      This program is free software; you can redistribute it and/or
!      modify it under the terms of the GNU General Public License
!      as published by the Free Software Foundation; either version 2
!      of the License, or (at your option) any later version.
!
!      This program is distributed in the hope that it will be useful,
!      but WITHOUT ANY WARRANTY; without even the implied warranty of
!      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
!      GNU General Public License for more details.
!
!      You should have received a copy of the GNU General Public License
!      along with this program; if not, write to the Free Software
!      Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, 
!      USA.
******************************************************************************/

require_once "kbase.php";

/**
* Classe de base pour les composants d'une page
*
* @author Gerard CANTEGRIL <cage-at-aotb.org>
* @see to follow
*
*/
class kCpt extends kBase
{

  // {{{ properties
  
  /**
   * Page contening the component
   *
   * @private
   */
  var $_page;

  /**
   * Element list of the objects of the component.
   * Can be component or elements
   *
   * @private
   */
  var $_objs;

  // }}}

  // {{{ constructor
  /**
   * Constructor. 
   *
   * @param string $module Module a traduire 
   * @access public
   * @return void
   *
   * @protected
   */
  
  function kCpt()
    {
      $this->_type = 'none';
      $this->_name = 'unname';
      $this->_page = null;
    }
  // }}}

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
  function &addBtn($labelId, $fctId=KAF_CANCEL, $pageId='none', 
		   $actId=KID_NONE, $dataId=0, $width=0, $height=0)
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($labelId);
      if ($elt)
      {
	$this->addErr("kForm->addBtn: object already exists: $labelId");
	return $elt;
      }

      $attribs = array( 'id'     => $labelId);
      if ($fctId == KAF_SUBMIT)
	  $attribs['type']  = 'submit';
      else
	$attribs['type']  = 'button';
      
      require_once "kbtn.php";
      $this->_objs[$labelId] = new kBtn($labelId, $attribs);
      
      if ($fctId != KAF_SUBMIT)
	{
	  $act = array($fctId, $pageId, $actId, $dataId);
	  if ($width && $height)
	    {
	      $act[] = $width;
	      $act[] = $height;
	    }
	  $actions[1] = $act;
	  $this->_objs[$labelId]->setActions($actions);
	}
      return $this->_objs[$labelId];
    }
  // }}}



  // {{{ addDiv()
  /**
   * @brief 
   * @~english Add an division 
   * @~french Ajout d'un composant de type Division.
   *
   * @par Description:
   * @~french Un composant de type Division poss�de plusieurs items. 
   *
   * @~english
   * See french doc.
   *
   * @param  $divId  (string)    @~french nom du composant.
   *         @~english Component name.
   * @return @~french Pointeur sur le nouveau composant. 
   *         @~english Pointer on new component.
   *
   * @~
   * @see kDiv, addForm, addMenu, addWng
   * @par Exemple:
   * @code
   *    $kpage = new kPage('maPage');
   *    $kpage->addDiv('divDroite');
   * @endcode
   */
  function &addDiv($divId, $class='')
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($divId);
      if ($elt)
      {
	$this->addErr("kCpt->addDiv: object already exists: $divId");
	return $elt;
      }

      require_once "kdiv.php";
      $this->_objs[$divId] = new kDiv($this, $divId, $class);
      return $this->_objs[$divId];
    }


  // {{{ addImg()
  /**
   * @brief 
   * @~english Add a picture
   * @~french Ajout d'une image
   *
   * @par Description:
   * @~french  Un �l�ment de type image permet d'afficher une image 
   * dans la page. Si le nom du fichier image n'est pas fourni, il 
   * est construit avec le nom de l'�l�ment et l'extension '.png' et
   * le fichier est suppose se trouver dans le dossier d�fini par setImgPath.
   * Si le fichier n'existe pas, l'image n'est pas affich�e. Aucun message 
   * d'erreur n'est affich�.
   *
   * @~english
   * See french doc
   *
   * @param  $imgId  (string)  @~french nom de l'�l�ment image.
   *         @~english Picture name.
   * @param  $file   (string)  @~french nom du fichier contenant l'image
   *         @~english Filename of the picture
   * @return @~french Pointeur sur le nouvel �l�ment.
   *         @~english Pointer on new element.
   *
   * @~
   * @see kImg, setImgPath
   * @par Exemple:
   * @code
   *	$kform = new kform('sample') ;
   *	$kimg =& $kform->addImg('monImage') ;
   * @endcode
   */
  function &addImg($imgId, $file="", $size='')
    {
      // Verification qu'il n'y a pas d'objet avec ce nom
      $elt = $this->isExist($imgId);
      if ($elt)
      {
	$this->addErr("kCpt->addImg: object already exists: $imgId");
	return $elt;
      }

      $attribs = array( 'class'  => 'kImg');
      require_once "kimg.php";
      $this->_objs[$imgId] = new kImg($imgId, $attribs, $file, $size);
      $this->_objs[$imgId]->setImgPath($this->_imgPath);
      return $this->_objs[$imgId];
    }
  // }}}


  // {{{ addMenu()
  /**
   * @brief 
   * @~english Add an Menu
   * @~french Ajout d'un �l�ment de type Menu.
   *
   * @par Description:
   * @~french Un �l�ment de type Menu possede plusieur items. A chaque
   * est associe une action. Un item est represent� par un lable ou une
   * icone ou les deux. Ils peuvent �tre dispos�s horizontalement ou 
   * verticalement.
   *
   * @~english
   * See french doc.
   *
   * @param  $labelId  (string)    @~french nom de l'�l�ment. 
   *         @~english element name.
   * @param  $items    (array)    @~french liste des items du menu
   *         @~english Items of the menu
   * @return @~french Pointeur sur le nouvel �l�ment. 
   *         @~english Pointer on new element.
   *
   * @~
   * @see kmenu, getInput, addArea, addEdit
   * @par Exemple:
   * @code
   *    $kform = new kform('maForm');
   *    $items = array('Title' => 'Titre fcultatif',
   *                 'ForDivs'  => array(KAF_NONE),
   *     	     'NewDiv'   => array(KAF_NEWWIN,    'divs', 
   *   					 KID_NEW,  0, 450, 180),
   *     	     'UpdtDiv'  => array(KAF_NEWWIN,    'divs', 
   *					 KID_EDIT,  0, 450, 180),
   *           	     'DelDiv'   => array(KAF_UPLOAD, 'divs', 
   *				 KID_DELETE));
   *   $kform->addMenu("menuDiv", $items);
   * @endcode
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
      return $this->_objs[$labelId];
    }
  // }}}

  // {{{ addMsg()
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
  function &addMsg($msgId, $msg='', $class='kMsg')
    {
      require_once "kmsg.php";
      $this->_objs[$msgId] = new kMsg($msgId, $msg, $class);
      return $this->_objs[$msgId];
    }
  // }}}

  // {{{ addWng()
  /**
   * @brief 
   * @~english Add a Warning message
   * @~french Ajout d'un �l�ment de type Warning.
   *
   * @par Description:
   * @~french Un �l�ment de type Warning permet d'afficher du 
   * texte informatif, g�n�ralement un message d'erreur ou 
   * d'avertissement. 
   * Si le contenu du message est renseign�, il est affich�. Sinon
   * si une variable du nom de l'�l�ment existe, son contenu est affich�
   * comme contenu du message, sinon le nom de l'&l&ment est affich�. 
   * Le message ne pourra pas �tre recup�r�e lorsque la page sera envoy�e   
   *
   * @~english
   * See french doc.
   *
   * @param  $wngId  (string)    @~french nom de l'�l�ment. 
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
   *    $kpage = new kPage('maPage');
   *    $kdiv  =& kpage->addDiv('uneDivision');
   *    $kdiv->addWng('Attention: division vide!!!');
   * @endcode
   */
  function &addWng($wngId, $msg='')
    {
      require_once "kmsg.php";
      $this->_objs[$wngId] = new kMsg($wngId, $msg, 'kWng');
      return $this->_objs[$wngId];
    }
  // }}}


  // {{{ addErr()
  /**
   * @brief 
   * @~english Add a Error message
   * @~french Ajout d'un �l�ment de type Erreur.
   *
   * @par Description:
   * @~french Un �l�ment de type Erreur permet d'afficher du 
   * texte informatif, g�n�ralement un message d'erreur ou 
   * d'avertissement. 
   * Si le contenu du message est renseign�, il est affich�. Sinon
   * si une variable du nom de l'�l�ment existe, son contenu est affich�
   * comme contenu du message, sinon le nom de l'&l&ment est affich�. 
   * Le message ne pourra pas �tre recup�r�e lorsque la page sera envoy�e   
   *
   * @~english
   * See french doc.
   *
   * @param  $wngId  (string)    @~french nom de l'�l�ment. 
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
   *    $kpage = new kPage('maPage');
   *    $kdiv  =& kpage->addDiv('uneDivision');
   *    $kdiv->addErr('Attention: division vide!!!');
   * @endcode
   */
  function &addErr($wngId, $msg='')
    {
      require_once "kmsg.php";
      $this->_objs[$wngId] = new kMsg($wngId, $msg, 'kErr');
      return $this->_objs[$wngId];
    }
  // }}}

  // {{{ display($file)
  /**
   * @brief 
   * @~english Display the conponent
   * @~french Affichage du composant
   *
   * @par Description:
   * @~french Cete m�thode permet de commander l'affichage d'un 
   * composant et de tous les objets qu'il contient. Le fichier contenant
   * les chaines de caract�res � utilise est pass� en parametre.
   * Cette fonction n'est appel� que la focniton display de la kPage
   *
   * @~english
   * See french doc.
   *
   * @param  $name (string) @~french Nom du fichier contenant les 
   * chaines de caract�res.
   *         @~english File name
   * @return @~french Aucun
   *         @~english None
   *
   * @private
   */
  function display($file)
    {  
      // Display the error messages
      for ($i=0; $i < $this->_errNum; $i++)
	echo "&nbsp;&nbsp;".$this->_errs[$i]."<br />";

      // Display the objects of the component
      if (count($this->_objs))
	{
	  foreach( $this->_objs as $name => $obj)
	    {
	     if ( is_object($obj) )
	      $obj->display($file);
	     else
	       echo $obj;
	      unset($this->_objs[$name]);
	    }
	}
    }
  // }}}

  // {{{ isExist()
  /**
   * @~english Check if the component or his ojects have the proposed name
   * @~french Verifie si le composant ou un de ses objets porte le nom propos�
   *
   * @par Description:
   * @~french Dans une page, le nom de chaque objet de la page doit �tre
   * unique quel que soit sa nature (composant ou �l�ment). Pour uncomposant,
   * cette m�thode v�rifie que son nom et le nom de tous ses composants est 
   * different du nom propos�. 
   *
   * @~english
   * See french doc.
   *
   * @param  $name (string) @~french Nom � v�rifier
   *         @~english Name to check
   * @return (boolean) @~french Vrai si un objet de ce nom existe d�j�
   *         @~english True if an object with this name already exists
   *
   * @private
   */
  function isExist($name)
    {
      if ($name == $this->_name &&
	  substr($name,-2) != '[]') 
	{
	  return $this;
	}
      if (count($this->_objs))
	{
	  foreach( $this->_objs as $na => $obj)
	    {
	     $elt = false;	
	     if ( is_object($obj) )
	       $elt = $obj->isExist($name);
	     if ($elt) return $elt;
	    }
	}
      return false;
    }
  // }}}
  

  // {{{ getControl()
  /**
   * @~english Return the string of the control label
   * @~french Renvoi la chaine de caractere du message d'erreur
   *
   * @par Description:
   * @~french Lors de la saisie d'informations dans un formulaire,
   * certains champ sont obligatoire; Cette fonction renvoie le message
   * d'erreur a afficher quand un �l�ment est obligatoire.
   * Cette fonction est virtuelle et renvoi null. Elle doit etre
   * implement�e au niveau des quelques elets concernes.
   *
   * @~english
   * See french doc.
   *
   * @param  $file (string) @~french Fichier langue a utiliser
   *         @~english Name of the string file
   * @return (string) @~french Message � afficher
   *         @~english Message to display
   *
   * @see addStringFile
   * @private
   */
  function getControl($file)
    {
      $control = '';
      if (count($this->_objs))
	{
	  foreach ($this->_objs as $obj)
	    {
	    	$msg = "";
	    	if (is_object($obj) )
	      		$msg = $obj->getControl($file);
	      if (is_array($msg))
		    $control = $msg;
	      else
		    if ($msg!='') $control[]=$msg;
	    }
	}
      return $control;
    }
  // }}}
}
?>