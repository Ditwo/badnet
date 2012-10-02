<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kimg.php,v $
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

class kImg  extends kElt
{

  // {{{ properties
  /**
   * Image file to display
   *
   * @var     string
   * @access  private
   */
  var $_file="";

  /**
   * Text with the image
   *
   * @var     string
   * @access  private
   */
  var $_text="";
  
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
  function kimg($name, $attribs, $file, $size)
    {
      $this->_type = "Img";
      $this->_attribs = $attribs;
      $this->_file = $file;
      $this->_name = $name;
      $this->_size = $size;
      $this->_isMandatory = false;
    }
  // }}}

  
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
  
      $filename = $this->_file;
      $size = @getImagesize($filename);
      if (!$size)
	{
	  $filename = "{$this->_imgPath}{$this->_name}.png";
	  $size = @getImagesize($this->_imgPath.$image);
	}
      if ($size)
      {
	$width = $size[0];
	$height= $size[1];
	// Recalculer la taille de l'image
	if (is_array($this->_size))
	  {
	    $few = 1;
	    if (isset($this->_size['maxWidth']))
	      $few = $this->_size['maxWidth'] / $size[0];
	    $feh = 1;
	    if (isset($this->_size['maxHeight']))
	      $feh = $this->_size['maxHeight'] / $size[1];
	    $fe = min(1, $feh, $few);
	    $width = intval($size[0] * $fe);
	    $height= intval($size[1] * $fe);
	  }
	echo $this->_getLink();
	echo "<img id=\"$this->_name\" alt=\"";
        echo $this->getLabel($this->_name);
	echo "\" src=\"$filename\" ";
        echo "width=\"$width\" height=\"$height\"";
        echo " />";
        echo "\n";
	echo $this->_getEndLink();
      }
      if ($this->_text != "")
	echo $this->_text;
      else 
	{
	  $texte = $this->getLabel($this->_name);
	  if ($this->_name != $texte)
	    echo $texte."\n";
	}
    }
  // }}}
  

  // {{{ setText()
  /**
   * set the message text
   *
   * @access public
   * @param string  $msg Message
   * @return void
   */
  function setText($text)
    { 	
      $this->_text  = $text;
    }
  // }}}

}

?>