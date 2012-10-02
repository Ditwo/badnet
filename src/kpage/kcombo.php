<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kcombo.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.9 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
******************************************************************************/

require_once "kelt.php";

/**
* Classe pour les �l�ments de type Combobox. Une combobox est une
* liste de label. Par d�faut, un �l�ment combo est affich�  sous la forme
* d'une liste d�roulante. Pour l'afficher sous la forme d'une liste
* de longueur fixe, il suffit de pr�ciser le nombre de ligne avec la
* m�thode setLength.
* On peut r�cup�rer la valeur s�lectionner par l'utilisateur en utilisant
* la methode getInput de la kPage avec le nom de l'�l�ment.
*
* @author Gerard CANTEGRIL
*
*/
class kCombo  extends kElt
{

  // {{{ properties
  /**
   * Is a mandatory control
   * @private
   */
  var $_isMandatory = true;

  /**
   * List des items grises
   * @private
   */
  var $_disabled = array();
  
  // }}}
  
  // {{{ constructor
  /**
   * @brief 
   * @~english Constructor
   * @~french  Constructeur
   *
   * @par Description:
   * @~french Constructeur de l'objet Combo. Il ne faut pas cr�er
   * directement des objets Division. Seul les objets kDiv et kForm  
   * sont autoris�s � cr�er des Combo.
   *
   * @~english
   * See french doc.
   *
   * @param  $name  (string)    @~french nom de l'objet Combo
   *         @~english Object name
   * @param  $attribs (array)    @~french tableau des attributs HTML 
   * de la Combo
   *         @~english HTML attribs of combo
   * @return @~french Aucun
   *         @~english None
   * @~
   * @see kDiv, kForm
   * @private
   */
  function kCombo($name, $attribs)
    {
      
      if(substr($name,-2) == '[]')
      {
      	 $name = substr($name, 0, -2);
      	 $attribs['id'] = $name;	
      }
      $this->_type = "Combo";
      $this->_attribs = $attribs;
      $this->_name = $name;
      $this->_isMandatory = true;
    }
  
  
  // {{{ display($path)
  /**
   * @brief 
   * @~english Print the object
   * @~french  Affiche l'�l�ment
   *
   * @par Description:
   * @~french Affiche l'�l�ment Combo. Cette m�thode ne doit pas �tre 
   * appel�e directement. Elle est appel�e par l'objet contenant la
   * la Combo.
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

      $labelId=$this->_name;
      $html = "<div id=k_$labelId>\n  <label><span class='kLabel'>";
      if ($this->getLabel($labelId) != $labelId) $html .= $this->getLabel($labelId);
      $html .= "</span>\n";
      $html .= "  <select ";
      foreach ($this->_attribs as $key => $value) 
	{ 
          if ($key == 'select')
	    $select = $value;
          else if ($key == 'value')
	    $values = $value;
          else
            $html .= "$key=\"$value\" ";
	}
      $action = $this->_getAction();
      if ($action != '')
	$html .= "onchange=\"return($action);\"";
      if ($this->_index  != null)
	$html .= " tabindex=\"".$this->_index."\"";

      $html .= " >\n";
      $i=0;
      foreach ($values as $value => $val) 
	{
	  //echo "  <option class=\"wbsCbn".($i%2)."\" value=\"$key\"";
	  $class = '';
	  $title = '';
	  $disable = false;
	  $selected = '';
	  if (is_array($val))
	    {
	      if (isset($val['text']))
		{
		  $text = $val['text'];
		  $value = $val['value'];
		}
	      else $text = $val['value'];
	      if (isset($val['title']))	$title = $val['title'];
	      if (isset($val['class']))	$class = $val['class'];
	      if (isset($val['disable'])) $disable = $val['disable'];
	      if (isset($val['selected'])) 	$selected = "selected='selected'";
	    }
	  else
	    {
	      $text = $val;
	    }
	  $html .= "    <option value=\"$value\"";
	  if ($class != '') $html .= "    class=\"$class\"";
	  if ($title != '') $html .= "    title=\"$title\"";
	  
          $i++;
          if (in_array($text, $this->_disabled) ||
	      in_array($key, $this->_disabled) ||
	      $disable ) 
	      $html .= " disabled=\"disabled\"";
	  //	  echo "selected=$select;key=$key;value=$value";
          if ($value == $select || $text == $select) 
	    $selected = " selected=\"selected\"";
          $html .= "$selected >".$this->getLabel($text)." </option>\n";
	}
      $html .= "  </select>\n  </label>\n</div>\n\n";

      echo $html;
      
    }
  // }}}
  
  // {{{ setLength()
  /**
   * @brief 
   * @~english Set the number of line of the list
   * @~french  Fixe le nombre de ligne de la liste
   *
   * @par Description:
   * @~french Par defaut, un �l�ment combo est affich�  sous la forme
   * d'une liste d�roulante. Pour l'afficher sous la forme d'une liste
   * de longueur fixe, il suffit de pr�ciser levombre de ligne avec cette
   * m�thode
   *
   * @~english
   * See french doc.
   *
   * @param  $size  (string)    @~french Nombre de ligne de la liste
   *         @~english Number of line of the list
   * @return @~french Aucun
   *         @~english None
   */
  function setLength($size)
    { 	
      $this->_attribs['size'] = $size;
    }
  // }}}

  // {{{ setLength()
  /**
   * @brief 
   * @~english Set the number of line of the list
   * @~french  Fixe le nombre de ligne de la liste
   *
   * @par Description:
   * @~french Par defaut, un �l�ment combo est affich�  sous la forme
   * d'une liste d�roulante. Pour l'afficher sous la forme d'une liste
   * de longueur fixe, il suffit de pr�ciser levombre de ligne avec cette
   * m�thode
   *
   * @~english
   * See french doc.
   *
   * @param  $size  (string)    @~french Nombre de ligne de la liste
   *         @~english Number of line of the list
   * @return @~french Aucun
   *         @~english None
   */
  function disabled($disa)
    { 	
      $this->_disabled = $disa;
    }
  // }}}

  // {{{ setLength()
  /**
   * @brief 
   * @~english Set the number of line of the list
   * @~french  Fixe le nombre de ligne de la liste
   *
   * @par Description:
   * @~french Par defaut, un �l�ment combo est affich�  sous la forme
   * d'une liste d�roulante. Pour l'afficher sous la forme d'une liste
   * de longueur fixe, il suffit de pr�ciser levombre de ligne avec cette
   * m�thode
   *
   * @~english
   * See french doc.
   *
   * @param  $size  (string)    @~french Nombre de ligne de la liste
   *         @~english Number of line of the list
   * @return @~french Aucun
   *         @~english None
   */
  function setMultiple()
    { 	
      $this->_attribs['multiple'] = 'multiple';
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
	  $control = "msg['".$this->_name."']='".$msg."';\n";
	}
      return $control;
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

}

?>