<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kbtncss.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.1 $
!   Mailto     : cage@free.fr
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

class kBtncss  extends kElt
{

  // {{{ properties
  /**
   * Label of the button
   *
   * @var     array
   * @access  private
   */
  var $_labelBtn="";

  // }}}

  // {{{ constructor
  /**
   * Constructor. 
   *
   * @param string $name      Name of button
   * @param string $attrbs    Attribs of the button
   * @access public
   * @return void
   */
  function kBtncss($name)
    {

      $this->_type = "Btn";
      $this->_name = $name;
      $this->_isMandatory = false;
    }


  // {{{ display($path)
  /**
   * Print the element with html tag
   *
   * @access public
   * @param string Path of the language file
   * @return void
   */
  function display($path)
    {
      $this->_stringFile = $path;

      echo "<p class=\"kBtncss\">\n";
      echo $this->_getLink();
      if ($this->_labelBtn == "")
	echo $this->getLabel($this->_name);
      else
	echo $this->_labelBtn;
      echo $this->_getEndLink();
      echo "</p>\n";
    }
  // }}}

  // {{{ setLabel()
  /**
   * Fix the label of the boutton
   *
   * @access public
   * @param string Label of the bouton
   * @return void
   */
  function setLabel($label)
    {
      $this->_labelBtn = $label;
    }
  // }}}

}

?>