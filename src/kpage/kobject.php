<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kobject.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.1 $
!   Revised by : $Author: cage $
******************************************************************************/

require_once "kelt.php";

/**
* Classe de base pour la creation d'un kobject
*
* @author Gerard CANTEGRIL <cage-at-aotb.org>
* @see to follow
*
*/

/**
 * Un �l�ment de type Object correcpond au type HTML de meme nom
 *
 * @author Gerard CANTEGRIL
 */

class kObject  extends kElt
{

  // {{{ properties
  /**
   * Message to display 
   *
   * @private
   */
  var $_msg='';

  var $_contents=array();
  
  // }}}

  // {{{ constructor
  /**
  /**
   * @brief 
   * @~english Constructor
   * @~french  Constructeur
   *
   * @par Description:
   * @~french Constructeur de l'objet Message. Il ne faut pas cr�er
   * directement des objets Message. Seul les objets Div et Form sont
   * a cr�er des �l�ment Message.
   *
   * @~english
   * See french doc.
   *
   * @param  $name  (string)    @~french Nom de l'�l�ment message
   *         @~english Name of element
   * @param  $attribs (array)    @~french Attributs HTML 
   *         @~english HTML attributes
   * @return @~french Aucun
   *         @~english None
   * @~
   * @see kDiv, kForm, addMsg
   * @private
   */
  function kObject($name, $attribs, $content=array())
    {
      $this->_type = "Object";
      $this->_name = $name;
      $this->_attribs = $attribs;
      $this->_contents = $content;
    }
  
  // {{{ display($file)
  /**
   * Print the element with html tag
   *
   * @access public
   * @param string Path of the language file
   * @return void
   */
  function display($file)
    {
      kbase::display($file);

      $out = "<object id=\"$this->_name\"";
      foreach($this->_attribs as $attrib=>$value)
	$out .= "\n $attrib=\"$value\"";
      $out .= ">\n";
      foreach($this->_contents as $content)
	$out .= "$content\n";
      $out .="</object>\n";      
      echo $out;
    }
  // }}}
}

?>