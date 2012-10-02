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

class kIframe  extends kElt
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
   */
  function kIframe($name, $attribs, $content=array())
    {
      $this->_type = "iframe";
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

      $out = "<iframe id=\"$this->_name\"";
      foreach($this->_attribs as $attrib=>$value) $out .= "\n $attrib=\"$value\"";
      $out .= ">\n";
      foreach($this->_contents as $content)
	$out .= "$content\n";
      $out .="</iframe>\n";      
      echo $out;
    }
  // }}}
}

?>