<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kradio.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.4 $
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

class kRadio  extends kElt
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
  function kradio($name, $attribs)
    {
      
      $this->_type = "Radio";
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
          if ($key == 'select'){ if ($value) echo " checked "; }
          else echo "$key=\"$value\" ";
	}
      
      $action = $this->_getAction();
      if ($action != '')
	  echo "onclick=\"return($action);\" ";
      echo " />\n<span class='kLabelR'>"; 
      if ($this->_text != '')
	echo $this->getLabel($this->_text);
      else
	echo $this->getLabel($labelId);
      echo "\n</span>\n</label>\n";
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
      $this->_stringFile = $file;
      $msg = "msg".$this->_attribs['name'];
      $msg = $this->getLabel($msg);
      $control = "msg['".$this->_attribs['name']."']='".$msg."';\n";
      return $control;
    }
  // }}}

}

?>