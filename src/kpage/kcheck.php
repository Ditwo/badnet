<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kcheck.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.4 $
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

class kCheck  extends kElt
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
  function kCheck($name, $attribs)
    {
      
      $this->_type = "Check";
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
      $this->_stringFile = $path;
      $labelId = $this->_name;
      echo "<label>\n<input ";
      foreach ($this->_attribs as $key => $value) 
	{
          if ($key == 'select')
	    { 
            if ($value) echo " checked "; 
	    }
          else echo "$key=\"$value\" ";
	}

      $action = $this->_getAction();
      if ($action != '')
	echo "onclick=\"return($action);\"";
      if ($this->_index  != null)
	echo " tabindex=\"".$this->_index."\"";

      echo "/>\n<span class='kLabelR'>"; 
      if ($this->_text != '')
	echo $this->getLabel($this->_text);
      else
	echo $this->getLabel($labelId);
      echo "\n</span>\n</label>\n";
    }
  // }}}

}

?>