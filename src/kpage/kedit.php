<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kedit.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.7 $
!   Mailto     : cage@free.fr
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

class kEdit  extends kElt
{
  // {{{ properties
  /**
   * Is a mandatory control
   * @private
   */
  var $_isMandatory = true;

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
  function kEdit($name, $attribs)
    {
      $this->_type = "Edit";
      $this->_name = $name;
      if (isset($attribs['form']))
	{
	  $this->_form = $attribs['form'];
	  unset($attribs['form']);
	}
      $this->_attribs = $attribs;
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
      
      $text = "<div id='k$labelId'";
      if ($this->_class != '') $text .= " class =\"{$this->_class}\"";
      $text .= ">\n  <label><span class='kLabel'>";
      $text .= $this->getLabel($labelId);
      $text .= "</span>\n  ";
      
      if ($this->_attribs['class'] == 'kInfo')
	{
	  $text .= $this->_getLink();
	  if ($this->_attribs['value'] === '')
	    $text .= '&nbsp;';
	  else
	    $text .= $this->_attribs['value'];
	  
	  $text .= $this->_getEndLink();
	  $text .= "\n  ";
	  unset($this->_attribs['class']);
	}
      $text .= "<input ";
      foreach ($this->_attribs as $key => $value) 
	{
	  $text .= "$key=\"$value\" ";
	}
      if ($this->_index  != null)
	$text .= " tabindex=\"".$this->_index."\"";
      $text .= " />\n";
      $text .= "  </label>\n";
      $text .= "</div>\n\n";
      echo $text;
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

  // {{{ autoNextField()
  /**
   * @brief 
   * @~english Allow empty field.
   * @~french  passe automatiquement au champ suivant lorsque
   * tous les caracetres ont ete saisie
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
  function autoNextField($name)
    { 	
      $this->_attribs['onkeyup'] = 
	"if (this.value.length>=this.maxLength)".
	"{".
	"document.forms['{$this->_form}']['$name'].focus();".
	"}";
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

  
  // {{{ setLength()
  /**
   * @brief 
   * @~english Set the lenght of the edit box (number of char)
   * @~french  Fixe la longueur de la zone de saisie en nombre de 
   * caract�res
   *
   * @par Description:
   * @~french Lors de sa cr�ation, une zone d'edition a une longueur
   * fix�e � 30 caract�res. Cette m�thode permet de modifier la longueur.
   * Elle sera bientot abandonn�e, le r�glage de la dimension devant 
   * s'effectuer par le biais des fichiers CSS.
   *
   * @~english
   * See french doc.
   *
   * @param  $size  (integer)    @~french Loguer de la zone de saisie
   * en nombre de caract�res.
   *         @~english Length of the edit box
   * @return @~french Aucun
   *         @~english None
   */
  function setLength($size)
    { 	
      $this->_attribs['size'] = $size;
    }
  // }}}

  // {{{ setMaxLength()
  /**
   * @brief 
   * @~english Set the number max of caracter
   * @~french  Fixe le nombre maximal de caract�res autoris�s
   *
   * @par Description:
   * @~french Le nombre maximal de caract�res autoris�s pour la saisie 
   * d'une information est fix� � 20 � la cr�ation de l'�l�ment. Cette
   * m�thode permet de mofdifier cette valeur.
   *
   * @~english
   * See french doc.
   *
   * @param  $size  (integer)    @~french Nombre maxiaml de caract�res
   * autoris�s.
   *         @~english Number maxiaml of char
   * @return @~french Aucun
   *         @~english None
   */
  function setMaxLength($size)
    { 	
      $this->_attribs['maxlength'] = $size;
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