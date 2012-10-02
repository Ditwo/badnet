<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kgrid.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.1 $
!   Revised by : $Author: cage $
******************************************************************************/

require_once "kelt.php";

/**
* Classe de base pour la creation de grid
*
* @author Philippe Midol-Monnet <philippe-at-midol-monnet.org>
* @see to follow
*
*/

class kgrid  extends kelt
{

  // {{{ properties
  /**
   * Name of the form
   *
   * @var     string
   * @access  private
   */
  var $_form;
  
  /**
   * Width of the table
   *
   * @var     string
   * @access  private
   */
  var $_width="100%";



   /**
   * Logos for different column
   *
   * @var     array
   * @access  private
   */
   var $_logos=array();

   /**
   * Title for different column
   *
   * @var     array
   * @access  private
   */
   var $_c_titles=array();

   /**
   * Title for different line
   *
   * @var     array
   * @access  private
   */
   var $_r_titles=array();

   /**
   * Rows selected
   *
   * @var     array
   * @access  private
   */
   var $_select=array();


  // }}}
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @param string $name      Name of grid
   * @param string $attribs   Attribs of the grid
   * @access public
   * @return void
   */
  function kgrid($name, $attribs, $form)
    {
      
      $this->_type = "Grid";
      $this->_c_titles = $attribs['c_titles'];
      $this->_r_titles = $attribs['r_titles'];
      if (isset($attribs['select'])) {
	  $this->_select = $attribs['select'];
      }
      $this->_name = $name;
      $this->_form = $form;
      $this->_isMandatory = false;
      //      $this->_isNumber = true;
      //$this->_isSelect = true;
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
      $this->_path = $path;
      $labelId = $this->_name;
      

      // recuperation de la taille de la grid
      $nbRows = count($this->_r_titles);
      $nbCols = count($this->_c_titles);
      
      $select = $this->_select;

      echo "<div style=\"clear:both;\"></div>\n";

      // Retour si la grid est vide
      if (!$nbRows || !$nbCols) return;

      // debut de la definition du tablea html
      echo "<table name=\"$labelId\" id=\"$labelId\" class=\"kGrid\" >\n";
      echo "<tr class=\"kHead\">\n";

      // traitement de la ligne d'entete
      
      // la premiere colone de la ligne d'entete est vide
      echo "<th class=\"kHead\">&nbsp;</th>\n";

      // Print the title of other columns
      foreach ($this->_c_titles as $c_id => $title)
	{
	  //	  $title=$this->_c_titles[$j];  
	  echo "<th  class=\"kHead\">&nbsp;";

	  // Print the title of the column
	  echo $title;
	  echo "</th>\n";
	}

      // fin de la ligne d'entete
      echo "</tr>\n\n<tbody>\n";
      

      // Now, print the rows
      $i=0;
      foreach ($this->_r_titles as $r_id => $r_title)
	{ 

          echo "<tr class=\"kGrid\">";
	  $i++;
	  
	  // Print the title of the row
	  echo "<td  class=\"kRow".($i%2)."\">";
	  echo $r_title."</td> \n";
	  // affichage de chaque checkbox 
	  foreach ($this->_c_titles as $c_id => $c_title)
	    {

	      // le $i%2 permet d'alterner les styles de lignes
	      echo "<td  class=\"kRow".($i%2)."\">";
	      //echo "<td  class=\"kActcell\">";

	      // simplification: pas de verif sur les permissions etc..
	      // simplification pas de donnees preselectionnee
	      //	      $name=$labelId."_".$i."_".$j;
	      //echo "<input type=\"checkbox\" name=\"$name\"";
	      echo "<input type=\"checkbox\" name=\"$labelId"."[]\"";

	      // if selected, check the box
	      if (isset($select[$r_id][$c_id]))
		{
		  echo " checked=\"checked\"";
		}
	      
	      // la valeur de la checkbox correspond au couple des ids
	      // ligne/colonne
	      echo " value=\"".$r_id." ".$c_id."\" />";


	      // fin de la cellule
	      echo "</td> \n";
	    }

          echo "</tr> \n";

	}	
      echo "</tbody>\n</table>\n</div>\n";
    }
  // }}}


  // {{{ _getAction()
  /**
   * calculate the action of a cell
   *
   * @access private
   * @param integer $index  column of the cell
   * @param array   $row    data of the row
   * @return void
   */


  // {{{ setSize()
  /**
   * set the size of each column of the row
   *
   * @access public
   * @param integer $size
   * @return void
   */
  function setSize($size)
    { 	
      $this->_size = $size;
    }
  // }}}
  


  // {{{ setSelect
  /**
   * Fixs the row to be selected
   *
   * @access public
   * @param array  $select
   * @return void
   */
  function setSelect($select)
    { 	
      $this->_select = $select;
    }
  // }}}

  // {{{ setTitles
  /**
   * Set the titles of the colums of the row element
   *
   * @access public
   * @param array $titles
   * @return void
   */
  function setTitles($titles)
    { 	
      $this->_titles  = $titles;
    }
  // }}}


  // {{{ isGrid()
  /**
   * Return a true if the elt is a row
   *
   * @access private
   * @return void
   */
  function isGrid()
    {
      return true;
    }
  // }}}


  // {{{ setLogos()
  /**
   * Set the logos on a column
   *
   * @access public
   * @param array   $actions  Arrays of the actions
   * @return void
   */
  function setLogo($logos)
    { 
      $this->_logos = $logos;
    }
  // }}}


  // {{{ _getImg()
  /**
   * Calculate the img tag of a cell
   *
   * @access public
   * @param integer $col column of the cell
   * @param array   $row data of the row
   * @return void
   */
  function _getImg($col, $row)
    {
      $tag = "";
      if (isset($this->_logos[$col]))
	{
	    if (isset($row[$this->_logos[$col]]))
	      {
		$filename = $row[$this->_logos[$col]];
		$size = @getImagesize($filename);
		if ($size)
		  {
		    $tag = "<img class=\"kmenuImg\" src=\"$filename\" ";
		    $tag .= $size[3];
		    $tag .= " />\n";
		  }
	      }
	    //else
	    //  echo "colonne non definie:".$this->_logos[$col].'<br>';
	}
      return $tag;
    }
  // }}}
  
}

?>