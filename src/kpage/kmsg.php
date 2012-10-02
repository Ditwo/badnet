<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kmsg.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.1 $
!   Revised by : $Author: cage $
******************************************************************************/

require_once "kelt.php";

/**
* Classe de base pour la creation de form
*
* @author Gerard CANTEGRIL <cage-at-aotb.org>
* @see to follow
*
*/

/**
 * Un �l�ment de type Message est un simple paragraphe. Il permet
 * d'afficher des informations succintes et des messages informatifs.
 * Il est possible d'associer une action ou un lien vers un site
 * ext�rieur en utilisant les methodes setAction et setUrl.
 *
 * Techniquement, un �l�ment Message est un paragraphe HTML compris
 * entre des balises "p"
 *
 * @author Gerard CANTEGRIL
 */

class kMsg  extends kElt
{

  // {{{ properties
  /**
   * Message to display 
   *
   * @private
   */
  var $_msg='';
  
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
  function kMsg($name, $msg='', $class='')
    {
      $this->_type = "Msg";
      $this->_name = $name;
      $this->_msg = $msg;
      $this->_class = $class;
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

      $out = "<p id=\"$this->_name\"";
      if ($this->_class != '')
	$out .= " class=\"$this->_class\"";
      $out.= ">\n";

      $out .= $this->_getLink();

      if($this->_msg === '')
	$out .= $this->getLabel($this->_name);
      else
	$out .=  $this->getLabel($this->_msg);

      $out .= $this->_getEndLink();
      echo $out;
      echo "\n</p>\n";      
    }
  // }}}


  // {{{ setMsg()
  /**
   * set the message text
   *
   * @access public
   * @param string  $msg Message
   * @return void
   */
  function setMsg($msg)
    { 	
      $this->_msg  = $msg;
    }
  // }}}
  
  
  
}

?>