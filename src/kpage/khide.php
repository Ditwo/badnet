<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/khide.php,v $
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

class kHide  extends kElt
{

    // {{{ properties

    // }}}

    // {{{ constructor
    /**
     * Constructor. 
     *
     * @param string $name      Name of button
     * @param string $attribs   Attribs of the button
     * @access public
     * @return void
     */
    function kHide($name, $attribs)
    {
      $this->_type = "Hide";
      $this->_attribs = $attribs;
      $this->_name = $name;
      $this->_isMandatory = false;
      $this->_isActive = true;
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
 	echo "<input ";
        foreach ($this->_attribs as $key => $value) 
	{
          echo "$key=\"$value\" ";
	}
        echo " />\n";
      }
    // }}}
    
}

?>