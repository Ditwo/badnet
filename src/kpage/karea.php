<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/karea.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.4 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
******************************************************************************/

require_once "kelt.php";

/**
* Classe de base pour la creation de form
*
* @author Gerard CANTEGRIL <cage-at-aotb.org>
* @see to follow
*
*/

class kArea extends kElt
{

  // {{{ properties
    /**
     * Valeur a afficher
     *
     * @private
     */
    var $_value = "";
  
  // }}}
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @param string $name      Name of button
   * @param string $attribs   Attribs of the button
   * @access public
   * @return void
   *
   * @protected
   */
  function karea($name, $attribs, $value)
    {
      
      $this->_type = "Area";
      $this->_attribs = $attribs;
      $this->_name = $name;
      $this->_value= $value;
      $this->_isMandatory = true;
      $this->_isActive = true;
    }
  
  
  // {{{ display($file)
  /**
   * @brief 
   * @~english Print the element with html tag.
   * @~french Affiche l'�l�ment. 
   *
   * @par Description:
   * @~french G�n�re le code HTML correspondant � l'�l�ment et l'affiche
   * dans la page. Cette m�thode n'est pas destin�e a �tre appel�
   * directement.
   *
   * @~english
   * See french doc.
   *
   * @param  $file  (string)    @~french nom du fichier contenant les
   * variables des noms des �l�ments.
   *         @~english Filename for vars for the name of the elements
   * @return @~french Aucun
   *         @~english None
   *
   *
   * @protected
   */
  function display($file)
    {
      $this->_stringFile = $file;
      $labelId = $this->_name;

      echo "<div>\n  <label><span class='kLabel'>";
      echo $this->getLabel($labelId);
      echo "</span>\n";

      echo "  <textarea ";
      foreach ($this->_attribs as $key => $value) 
	{ 
	  echo "$key=\"$value\" ";
	}
      echo " >";
      echo $this->_value;
      echo "</textarea>\n  </label>\n</div>\n\n";
    }
  // }}}

  // {{{ noMandatory()
  /**
   * @brief 
   * @~english Allow empty field.
   * @~french  Rend la saisie facultative.
   *
   * @par Description:
   * @~french Lors de sa cr�ation, une zone d'�dition a un statut
   *  obligatoire: la zone de saisie doit �tre obligatoirement 
   * renseign�e. Cette m�thode permet de rendre la saisie d'information 
   * facultative.
   *
   * @~english
   * See french doc.
   *
   * @return @~french Aucun
   *         @~english None
   */
  function noMandatory()
    { 	
      $this->_isMandatory = false;
      if ($this->_attribs['class'] == "kMandatory")
	$this->_attribs['class'] = "kOption";
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
      $control = '';
      if ($this->_isMandatory)
	{
	  $msg = "msg".$this->_name;
	  $msg = $this->getLabel($msg);
	  $control = "msg['".$this->_name."']='".addslashes($msg)."';\n";
	}
      return $control;
    }
  // }}}

}

?>