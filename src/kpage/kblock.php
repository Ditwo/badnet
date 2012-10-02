<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kblock.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.1 $
!   Mailto     : cage@free.fr
!   Revised by : $Author: cage $
******************************************************************************/

require_once "kelt.php";

/**
* Dans un formulaire un block permet de regrouper les zone de saisies.
* On peut �ventuellement rajouter un titre au block. Il s'affihera au 
* dessus du block.
*
* @author Gerard CANTEGRIL
*
*/
class kBlock  extends kElt
{
  
  // {{{ properties
  /**
   * Title for the block
   *
   * @private
   */
  var $_title="";
  // }}}

  // {{{ constructor
  /**
   * @brief 
   * @~english Constructor
   * @~french  Constructeur
   *
   * @par Description:
   * @~french Constructeur de l'objet block. Il ne faut pas cr�er
   * directement des objets Block. Seul l'objet kForm  est autoris�
   * � cr�er des block.
   *
   * @~english
   * See french doc.
   *
   * @param  $name  (string)    @~french nom de l'objet Combo
   *         @~english Object name
   * @return @~french Aucun
   *         @~english None
   * @~
   * @see kForm, addBlok
   * @private
   */
  function kBlock($name, $class='')
    {
      
      $this->_type = "Block";
      $this->_name = $name;
      $this->_class = $class;
      $this->_isMandatory = false;
    }
  

  // {{{ setTitle
  /**
   * @brief 
   * @~english Set the title of the block
   * @~french  Fixe le titre du block
   *
   * @par Description:
   * @~french Un objet de type block contient des �l�ments d'une kForm.
   * normalement, ces �l�ments sont des kEdit, kHide ou kPwd. Cette m�thode
   * permet d'ajouter les �l�ments au block. Seul l'objet kForm  est autoris�
   * � cr�er utiliser cette m�thode.
   *
   * @~english
   * See french doc.
   *
   * @param  $title  (string)    @~french titre du bloc
   *         @~english Block title
   * @return @~french Aucun
   *         @~english None
   * @~
   */
  function setTitle($title)
    { 	
      $this->_title  = $title;
    }
  // }}}

  // {{{ addElt
  /**
   * @brief 
   * @~english Add element to a block
   * @~french  Ajout des �l�ments dans le block
   *
   * @par Description:
   * @~french Un objet de type block contient des �l�ments d'une kForm.
   * normalement, ces �l�ments sont des kEdit, kHide ou kPwd. Cette m�thode
   * permet d'ajouter les �l�ments au block. Seul l'objet kForm  est autoris�
   * � cr�er utiliser cette m�thode.
   *
   * @~english
   * See french doc.
   *
   * @param  $name  (string)    @~french nom de l'objet Combo
   *         @~english Object name
   * @return @~french Aucun
   *         @~english None
   * @~
   * @private
   */
  function addElt($name, $elt)
    {
      $this->_elts[$name] = $elt;
    }
  
  
  // {{{ display($path)
  /**
   * @brief 
   * @~english Print the object
   * @~french  Affiche l'�l�ment
   *
   * @par Description:
   * @~french Affiche le block et tous ses �l�ments. Cette m�thode ne doit
   * pas �tre appel�e directement. Elle est appel�e par la kForm contenant 
   * le block
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
      if (!isset($this->_elts))return;

      $blocMark = 'fieldset';
//       foreach( $this->_elts as $name => $elt)
// 	if ( ($elt->getType() === 'Btn')||
// 	     ($elt->getType() === 'Radio'))
// 	  $blocMark = 'div';
//       reset($this->_elts);

      $this->_stringFile = $path;
      echo "<$blocMark id=\"$this->_name\"";
      if ($this->_class != '')
	echo " class=\"".$this->_class."\"";
      echo " >\n";
      
      if ($this->_title == '')
	$this->_title = $this->getLabel($this->_name);
      if ( $this->_title != $this->_name)
        {
	  //echo "<legend>".$this->_title."</legend>\n";
	  echo "<legend id=\"Leg".$this->_name;
	  echo "\">".$this->_title."</legend>\n";
        }
      
      foreach( $this->_elts as $name => $elt)
	{
	  $elt->display($path);
	  unset( $this->_elts[$name]);
	}
      echo "</$blocMark>\n\n";
    }
  // }}}
  

  // {{{ getControl()
  /**
   * Return the string for controlling the field
   *
   * @param  string $path Path for the label file
   * @return void
   * @private
   */
  function getControl($path)
    {
      $control="";
      if (isset($this->_elts))
	{
	  foreach( $this->_elts as $name => $elt)
	    $control .=   $elt->getControl($path);
	}
      return $control;
    }
  // }}}
  
}

?>