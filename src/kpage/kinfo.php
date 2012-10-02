<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kinfo.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.3 $
!   Revised by : $Author: cage $
******************************************************************************/

require_once "kelt.php";

/**
 * Un �l�ment de type Edit est constitu� d'une zone �ditable pr�c�d�e 
 * d'un label. Si une variable du nom de l'�l�ment existe, son contenu est
 * affich� comme label, sinon le nom de l'�l�m�nt est affich�.
 * La valeur de la zone editable pourra pas �tre recup�r�e en utilisant 
 * la methode getInput : kpage::getInput()
 *
 * @author Gerard CANTEGRIL 
 *
 * @see setStringFile, kPage, getInput
 *
 */

class kInfo  extends kElt
{
  // {{{ properties
  /**
   * Special class
   *
   * @var     string
   * @access  private
   */
  var $_class='';
  
  // }}}
  
  // {{{ constructor
  /**
   * @brief 
   * @~english Constructor
   * @~french  Constructeur
   *
   * @par Description:
   * @~french Constructeur de l'�l�ment de type Edit. Il ne faut pas cr�er
   * directement des �l�ments de type Edit. Seul l'objet kForm  est autoris�
   * � les cr�er. Cet �l�m�ent est constitu� d'une zone �ditable pr�c�d�e 
   * d'un label. Si une variable du nom de l'�l�ment existe, son contenu est
   * affich� comme label, sinon le nom de l'�l�m�nt est affich�.
   * La valeur de la zone editable pourra pas �tre recup�r�e en utilisant 
   * la methode getInput : kpage::getInput()
   *
   *
   * @~english
   * See french doc.
   *
   * @param  $name  (string)    @~french nom de l'objet Combo
   *         @~english Object name
   * @return @~french Aucun
   *         @~english None
   * @~
   * @see setStringFile, kPage, getInput
   * @private
   */
  function kInfo($name, $attribs)
    {
      $this->_type = "Info";
      $this->_attribs = $attribs;
      $this->_name = $name;
    }
  

  // {{{ display($path)
  /**
   * @brief 
   * @~english Print the object
   * @~french  Affiche l'�l�ment
   *
   * @par Description:
   * @~french Affiche l'�l�ment Edit. Cette m�thode ne doit
   * pas �tre appel�e directement. Elle est appel�e par la kForm contenant 
   * l'�l�ment
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
  function display($path)
    {
      $this->_stringFile = $path;
      $labelId = $this->_name;
      echo "<div id='$labelId' ";
      if ($this->_class != '') echo " class=\"{$this->_class}\"";
      echo ">\n  <label><span class=kLabel>";
      echo $this->getLabel($labelId);
      echo "</span>\n  ";
      echo $this->_getLink();
      if ($this->_attribs['value']!='')
	echo $this->getLabel($this->_attribs['value']);
      else
	echo '&nbsp;';
      echo $this->_getEndLink();
      echo "\n  ";
      echo "  </label>\n";
      echo "</div>\n\n";
    }
  // }}}

  // {{{ setClass()
  /**
   * set the class
   *
   * @access public
   * @param string  $class class
   * @return void
   */
  function setClass($class)
    { 	
      $this->_class  = $class;
    }
  // }}}
}

?>