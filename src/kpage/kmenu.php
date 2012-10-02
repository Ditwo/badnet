<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kmenu.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.4 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
******************************************************************************/

require_once "kelt.php";


define("KMN_ORIENTATION_VERTICAL",     1);
define("KMN_ORIENTATION_HORIZONTAL",   2);

define("KMN_DISPLAY_IMG",     10);
define("KMN_DISPLAY_TEXT",    11);
define("KMN_DISPLAY_BOTH",    12);


/**
* Classe de base pour la creation de menu
*
* @author Gerard CANTEGRIL <cage-at-aotb.org>
* @see to follow
*
*/

class kMenu extends kElt
{

  // {{{ properties
  
  /**
   * Items of the menu
   *
   * @private
   */
  var $_items;
  
  /**
   * Selected item of the menu
   *
   * @private
   */
  var $_select='';
  
  /**
   * Position of text and image
   *
   * @private
   */
  var $_display= KMN_DISPLAY_IMG;
  
  /**
   * Full path for images of the menu
   *
   * @private
   */
  var $_imgPath='kpage/img';
  
  // }}}
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @param string  $name   Name of the menu
   * @param array   $items  Items of the menu with the action
   * @param array   $title  Title off the menu
   * @param array   $style  Display type off the menu
   * @access public
   * @return void
   */
  
  function kMenu($name, $items, $select, $class)
    {
      $this->_type   = "Menu";
      $this->_name   = $name;
      $this->_items  = $items;
      $this->_select = $select;
      $this->_class  = $class;
    }
  // }}}
  

  // {{{ display()
  /**
   * Print the menu with html tag
   *
   * @private
   * @return none
   */
  function display($file)
    {
      $this->_stringFile = $file;
      if ($this->_class == '')
	$this->_class = 'kMenu';
      echo "<ul id=\"$this->_name\" class=\"{$this->_class}\">\n";

      foreach( $this->_items as $name => $action)
	{
	  $this->_actions[$name] = $action;
	  $label = $this->getLabel($name);
	  $this->_displayItem($name, $label, 
			      strtolower($name).".png", 
			      ($name==$this->_select));
	}
      echo "</ul>\n\n";

      
    }
  // }}}
  
  
  // {{{ _displayItem
  /**
   * Print the menu with html tag
   *
   * @param  string Name of the item
   * @param  string description
   * @param  string Image name of the item
   * @return none
   * @private
   */
  function _displayItem($name, $item, $image, $select)
    {
      $text = '';
      if ($image == 'separator.png')
        {
	  $text .= '<hr />';
	  //	  $text .= "</ul>\n<ul class=\"wbsMenuright\">";
        }
      else
        {
	  if ($select)
	    $text .= "<li id=\"$name\" class=\"select\" >\n";
	  else
	    $text .= "<li id=\"$name\" class=\"unselect\" >\n";
          $text .= $this->_getLink($name, $item);
	  
	  if ($this->_display == KMN_DISPLAY_IMG ||
	      $this->_display == KMN_DISPLAY_BOTH)
	    {
	      $size = @getImagesize($this->_imgPath.$image);
	      if ($size)
		{
		  $text .= "<img src=\"".$this->_imgPath.$image."\" ";
		  $text .= $size[3];
		  $text .= " alt=\"$item\" />";
		}
	    }
	  //if ( $this->_display == KMN_DISPLAY_BOTH && $size) echo $this->_glue;
	  
	  if ($this->_display == KMN_DISPLAY_TEXT ||
	      $this->_display == KMN_DISPLAY_BOTH ||
	      ($this->_display == KMN_DISPLAY_IMG && !$size))
            $text .= "$item";
	  
          $text .= $this->_getEndLink();
          $text .= "</li>\n";
        }
      echo $text;
    }
  // }}}
  
  // {{{ setDisplay
  /**
   * Set the display type
   *
   * @param  integer type of display/ 
   * Can be KMN_DISPLAY_IMG, KMN_DISPLAY_TEXT or KMN_DISPLAY_BOTH
   * @return void
   */
  function setDisplay($type)
    {
      if ($type == KMN_DISPLAY_IMG ||
	  $type == KMN_DISPLAY_TEXT ||
	  $type == KMN_DISPLAY_BOTH )
        {
	  $this->_display=$type;
	}
      else
	{
	  echo "kmenu->setDisplay: type daffichage inconnu<br>";
	}
    }
  // }}}
  
}
?>