<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/krows.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.20 $
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

class kRows  extends kElt
{

  // {{{ properties
  /**
   * column of sorting
   *
   * @var     integer
   * @access  private
   */
  var $_sort=1;
  var $_isSort = array();

  /**
   * searching string
   *
   * @var     string
   * @access  private
   */
  var $_search="";
  
  /**
   * first row to display
   *
   * @var     integer
   * @access  private
   */
  var $_first=0;
  
  /**
   * Number of row to display
   *
   * @var     integer
   * @access  private
   */
  var $_step=10;
  
  /**
   * Number max of row to display
   *
   * @var     integer
   * @access  private
   */
  var $_max=-1;
  
  /**
   * Flag to display command header of the rows
   *
   * @var     boolean
   * @access  private
   */
  var $_isPager = false;

  /**
   * Flag to display the first columns
   *
   * @var     boolean
   * @access  private
   */
  var $_isNumber = true;
  var $_displaySelect = true;
  var $_displayEditIcon = false;
  var $_displayTitle = true;
  var $_displayCol = true;
  
   /**
   * Classes for different column
   *
   * @var     array
   * @access  private
   */
   var $_colClass=array();

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
   var $_titles=array();

   /**
   * Rows selected
   *
   * @var     array
   * @access  private
   */
   var $_select=array();

   /**
   * Action for break
   *
   * @var     array
   * @access  private
   */
   var $_breakActions=array();

   /**
   * Action for row
   *
   * @var     array
   * @access  private
   */
   var $_rowActions=array();


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
  function kRows($name, $attribs, $form)
    {
      
      $this->_type = "Rows";
      $this->_attribs = $attribs;
      $this->_name = $name;
      $this->_isMandatory = false;
      $this->_isNumber = true;
      $this->_displaySelect = true;
      $this->_displayEditIcon = false;
      $this->_form = $form;
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

      // Nombre de lignes 
      $nbRows = count($this->_attribs);

      // Nombre de colonnes
      $nbCols = 0;
      foreach($this->_attribs as $row)
	$nbCols = max($nbCols, count($row));

      // Nombre de colonnes affichees
      $nbCmdCols = 0;
      if ($this->_isNumber) $nbCmdCols++;
      if ($this->_displaySelect || $this->_displayEditIcon)
	$nbCmdCols++;
      $nbCmdCols += count($this->_rowActions);

      if ($this->_isPager) $this->_displayPager($nbRows);

      $text ='';
      if ($this->_sort)
	{
	  $text .= "<input type=\"hidden\" name=\"kSort$labelId\" ";
	  $text .= "id=\"kSort$labelId\" value=\"$this->_sort\" />\n";
	}
      if ($this->_search != "")
	{
	  $text .= "<input type=\"hidden\" name=\"kSearch$labelId\" ";
	  $text .= "id=\"kSearch$labelId\" value=\"$this->_search\" />\n";
	}
      //$text .= "<div style=\"clear:both;\"></div>\n";

      if (!$nbRows) return;

      $text .= "<table id=\"$labelId\" class=\"kRow\" >\n";
      if ($nbCmdCols)
	$text .= "<colgroup span=\"$nbCmdCols\" width=\"20\" >\n";

      $head = '';
      if ($this->_displayTitle)
	{
	  $head .= "<thead><tr class=\"kHead\">\n";

	  // First column is the row's number
	  if ($this->_isNumber)
	    {
	      $head .= "<th class=\"kHead\">";
	      if (isset($this->_titles[0]))
		$head .= $this->getLabel($this->_titles[0]);
	      else
		$head .= "&nbsp;";
	      $head .= "</th>\n";
	    }
	  // Second column is the check box to select the row
	  if ($this->_displaySelect || 
	      $this->_displayEditIcon ||
	      count($this->_rowActions) )
	    //$head .= "<th class=\"kActHead\">&nbsp;</th>\n";
	    {
	      $nbSpan = count($this->_rowActions);
	      $nbSpan = 0;
	      if ($this->_displaySelect) $nbSpan++;
	      if ($this->_displayEditIcon) $nbSpan++;
	      $head .= "<th class=\"kActHead\" colspan=\"$nbSpan\" >&nbsp;";
	    }
	  if ($this->_displaySelect) 
	    $head .= "<input type=checkbox onClick=\"setCheckboxes (this, '{$this->_form}', '{$this->_name}[]');\" title=\"Cocher/D�cocher toutes les cases\">";
	  $head .= "</th>\n";
	  
	  foreach($this->_rowActions as $action)
	    $head .= "<th>&nbsp;</th>";

	  // Print the title of other columns
	  $attrib = reset($this->_attribs);
	  if (is_array($attrib))
	    $val = each($attrib);
	  else 
	    $val[1] = $attrib;
	  $limit = $nbCols;
	  for ($j=1; $j<$limit; $j++)
	    {
	      if(isset($this->_size[$j]) && $this->_size[$j]==="0+")
		$this->_displayCol = false;

	      $val = each($attrib);
	      // Print the column only if there is no size specified
	      // or if the size is not null
	      if (  $this->_displayCol &&
		    (! isset($this->_size[$j]) ||
		  (isset($this->_size[$j]) && $this->_size[$j]))  )
		{

		  $head .= "<th ";
		  if (isset($this->_titles[$j]))
		    {
		      $values = $this->_titles[$j];
		      if (is_array($values))
			{
			  if (isset($values['class']))
			    $head .= ' class="'.$values['class'].'"';
			}
		    }
		  else
		    $head .= "class=\"kHead\"";
		  $head .= ">";


		  if ($this->_sort && (!isset($this->_isSort[$j]) ||
				       $this->_isSort[$j]))
		    {
		      // Print the image to sort the column
		      $head .= "<a href=\"#\" onclick=\"uploadSort(";
		      $head .= "'$labelId', $j+1);";
		      $head .= "return false;\">";
		      $fileImg = "kpage/img/sort";
		      if ($this->_sort == $j+1)
			{
			  $fileImg .= "_up_slct";
			}
		      elseif (-$this->_sort == $j+1)
			{
			  $this->_sort *= -1;
			  $fileImg .= "_down_slct";
			}
		      else
			{
			  $fileImg .="_up";
			}
		      $fileImg .= ".png";
		      $size = @getImagesize($fileImg);
		      $head .= "<img src=\"$fileImg\" class=\"kImg\" ";
		      $head .= "alt=\"sort\" $size[3] />";
		      $head .= "</a>&nbsp;";
		    }
		  // Print the title of the column
		  if (isset($this->_titles[$j]))
		    {
		      $values = $this->_titles[$j];
		      if (is_array($values))
			{
			  if (isset($values['class']))
			    unset($values['class']);
			  foreach($values as $value)
			    {
			      $head .= "<p";
			      if (isset($value['class']))
				$head .= ' class="'.$value['class'].'"';
			      $head .= ">";
			      if (isset($value['value']))
				$head .= $value['value'];
			      $head .= "</p>";
			    }
			}
		      else
			{
			  $head .= $this->getLabel($values);
			}
		    }
		  else
		    {
		      $head .= $this->getLabel($labelId.$j);
		    }
		  $head .= "</th>\n";
		}
	      else
		$nbCols--;
	      
	    }
	  $head .= "</tr>\n</thead>\n\n<tbody>\n";

	}

      $nbDispCols = $nbCmdCols + $nbCols;
      //$text .= "<colgroup span=\"$nbCols\" width=\"*\" >\n";
      $text .= "<colgroup span=\"$nbCols\">\n";
      $text .= $head;
      echo $text;

      // Now, print the rows
      $num = 1;
      $row = reset($this->_attribs);
      for($i=1; $i<=$nbRows; $i++)
	{ 
	  $this->_displayCol = true;
	  if (is_array($row)) $val = each($row);
	  else  $val[1] = $row;
	  if ($val[1] !== KOD_BREAK) $this->_printRow($num++, $row);
	  else
	    {
	      $num = 1;
	      $this->_printBreak($row, $nbDispCols);
	    }
	  $row = next($this->_attribs);
	}
      echo "</tbody>\n</table>\n";
      //echo "</div>\n";
    }
  // }}}


  // {{{ _printBreak
  /**
   * Print the a break row
   *
   * @access public
   * @param string Path of the language file
   * @return void
   */
  function _printBreak($row, $nbDispCols)
    {
      if (isset($row[2]))
	$title = $row[2];
      else
	$title = $this->getLabel($row[1]);
      if (isset($row[3]) && $row[3] != '') 
	$class = $row[3];
      else if (isset($row['class']))
	$class = $row['class'];
      else
	$class = 'kRowBreak';

      if (count($this->_breakActions))
	{
	  $colSpan = $nbDispCols-2;
	  /*$colSpan += count($this->_rowActions);
	  if ($this->_displaySelect) $colSpan++;
	  if ($this->_displayEditIcon) $colSpan++;*/
	  $text = "<tr class=\"$class\"><td colspan=\"$colSpan\" >";
	  if (isset($row['action']))
	    $text .= $this->_getLink($row['action'], '');

	  if (isset($row['icon']))
	    {
	      $icon = $row['icon'];
	      $size = @getImagesize($icon);
	      $text .= "\n<img src=\"$icon\" ".
		"class=\"kImg\" alt=\"edition\" $size[3] />\n";
	    }
	  $text .=  $title;
	  if (isset($row['action']))
	    $text .=  "</a>";
	  $text .= "</td>\n<td colspan=\"2\" class=\"kRowBreakAct\">";
	  foreach($this->_breakActions as $num =>$action)
	    {
	      if (isset($row[4]) &&
		  (!isset($row['listAct']) ||
		   in_array($num, $row['listAct'])))
		{
		  $link = $action['link'];
		  if (isset($action['title']))
		    $title = $this->getLabel($action['title']);
		  else
		    $title = '';
		  
		  $link[3] = $row[4];
		  $text .= $this->_getLink($link, $title);
		  if (isset($action['icon']))
		    {
		      $icon = $action['icon'];
		      $size = @getImagesize($icon);
		      $text .= "\n<img src=\"$icon\" ".
			"class=\"kImg\" alt=\"$title\" $size[3] />\n";
		    }
		  if (isset($action['text']))
		    $text .= $action['text'];
		  $text .=  "</a>";
		}
	    }
	  $text .= "</td>\n</tr>\n";
	}
      else
	{
	  $text = "<tr><td colspan=\"$nbDispCols\" class=\"$class\">";
	  if (isset($row['action']))
	    $text .= $this->_getLink($row['action'], '');
	  if (isset($row['icon']))
	    {
	      $icon = $row['icon'];
	      $size = @getImagesize($icon);
	      $text .= "\n<img src=\"$icon\" ".
		"class=\"kImg\" alt=\"edition\" $size[3] />\n";
	    }
	  $text .=  $title;
	  $text .= "</td></tr>\n";
	  if (isset($row['action']))
	    $text .=  "</a>";
	}
      echo $text;
    }
  // }}}

  // {{{ _printRow
  /**
   * Print the a row
   *
   * @access public
   * @param string Path of the language file
   * @return void
   */
  function _printRow($i, $row)
    {
      $nbCols = count($row);
      if (is_array($row)) $val = reset($row);
      else	$val = $row;
	  $class= "kRow".($i%2);
      if (isset($row['class']))
	{
	  $class .= ' ' . $row['class'];
	  unset ($row['class']);
	}
	$title = '';
      if (isset($row['title']))
	{
	  $title = $row['title'];
	  unset ($row['title']);
	}
	$text =  "<tr class=\"$class\" title=\"$title\">\n";
	  
      // Print the number of the row
      if ($this->_isNumber)
	$text .= "<td class=\"kNumCell".($i%2)."\">".
	  ($i+$this->_first)."</td> \n";

      //$this->_displayEditIcon = $this->_getAct(0, $row);
      if ($this->_displaySelect || $this->_displayEditIcon ||
	  count($this->_rowActions) )
	{
	  // Print the check box to select the row
	  $text .= "<td  nowrap class=\"kActCell".($i%2)."\">\n";
	  if ( $this->_displaySelect && (!isset($this->_perms[$val]) ||
	       $this->_perms[$val] & KOD_SELECT) )
	    {
	      $text .= "<input type=\"checkbox\" name=\"{$this->_name}"."[]\"";
	      $text .= " id=\"{$this->_name}$i\"";
	      if (is_array($this->_select) && in_array($val, $this->_select))
		$text .= " checked=\"checked\"";
	      $text .= " value=\"".$val."\" />\n";
	    }
	  $act = $this->_getAct(0, $row);

	  // Old code to be suppress
	  // keep for compatibiltiy only
	  if ($this->_displayEditIcon)
	    {
	      $text .= "\n</td><td class=\"kIconClass\" >";
	      $text .= $act;
	      $size = @getImagesize("kpage/img/edit.png");
	      $text .= "<img src=\"kpage/img/edit.png\" ".
		"class=\"kImg\" alt=\"edition\" $size[3] />\n";
	      $text .= "</a>";
	    }
	  // New code to add many command for the rows
	  foreach($this->_rowActions as $action)
	    {
	      $link = $action['link'];
	      if (isset($action['title']))
		$title = $this->getLabel($action['title']);
	      else
		$title = '';
	      if (count($link) == 3)
		$link[3] = reset($row);
	      else 
		{
		  $colLnk = $link[3];
		  // If nolink require, then go out
		  if (isset($row[$colLnk]))
		    {
		      if ($row[$colLnk] == KAF_NONE) return '';
		      $link[3] = $row[$colLnk];
		    }
		  else
		    $link[3] = reset($row);
		}
	      $text .= "\n</td><td class=\"kIconClass\" >";
	      $text .= $this->_getLink($link, $title);
	      if (isset($action['icon']))
		{
		  $icon = $action['icon'];
		  $size = @getImagesize($icon);
		  if ($size)
		    $text .= "<img src=\"$icon\" ".
		      "class=\"kImg\" alt=\"edition\" $size[3] />\n";
		  else
		    $text .= $icon;
		}
	      if (isset($action['text']))
		$text .= $action['text'];
	      $text .=  "</a>";
	    }

	  /*	  if (isset($this->_perms[$val]))
	    {
	      if ($this->_perms[$val] & KOD_NONE )
		{
		  $size = @getImagesize("kpage/img/forbid.png");
		  echo "<img src=\"kpage/img/forbid.png\" ".
		    "class=\"wbsImg\" alt=\"forbid\" $size[3] />";
		}
	      if ($this->_perms[$val] & KOD_READ )
		{
		  $size = @getImagesize("kpage/img/read.png");
		  echo "<img src=\"kpage/img/read.png\" ".
		    "class=\"wbsImg\" alt=\"read\" $size[3] />";
		}
	      if ($this->_perms[$val] & KOD_WRITE )
		{
		  $size = @getImagesize("kpage/img/write.png");
		  echo "<img src=\"kpage/img/write.png\" ".
		    "class=\"wbsImg\" alt=\"write\" $size[3] />";
		}
	    }*/
	  $text .= "</td></td>\n";
	}

      // Print other columns of the row
      for ($j=1; $j<$nbCols; $j++)
	{

	  if(isset($this->_size[$j]) && $this->_size[$j]==="0+") $this->_displayCol = false;
	  $val = next($row);
	  // Print the column only if there is no size specified
	  // or if the size is not null
	  if ($this->_displayCol && (! isset($this->_size[$j]) ||
	      (isset($this->_size[$j]) && $this->_size[$j]) ))
	    {
	      if (isset($this->_colClass[$j])) $class = $this->_colClass[$j];
	      //else $class = '';

	      $act = $this->_getAct($j, $row);
	      $img = $this->_getImg($j , $row);
	      $text .= $this->_printCell($val, $class, $act, $img, $i);
	    }
	}
      $text .= "</tr> \n";
      echo $text;
    }
  // }}}

  // {{{ _printCell
  /**
   * Print the a cell
   *
   * @access public
   * @param string Path of the language file
   * @return void
   */
  function _printCell($value, $class, $act, $img, $numRow)
    {
      $text = '<td ';

      if (is_array($value) && isset($value['class']))
	{
	  $text .= 'class="' . $value['class'] . '" ';
	  unset($value['class']);
	}
	else if ($class != '') $text .= 'class="' . $class . '" ';
	
      if (is_array($value) && isset($value['title']))
	{
	  $text .= 'title="' . $value['title'] . '" ';
	  unset($value['title']);
	}
	if (is_array($value) && isset($value['colspan']))
	{
	  $text .= 'colspan="' . $value['colspan'] . '" ';
	  unset($value['colspan']);
	}
	
	
	  $text .= '>';
      if ($act != '' && (!($value === '') || $img != ''))  $text .= $act;

      // Empty cell
      if ($value === '') $text .= "$img&nbsp;";
      // Cell with multiple values
      else if(is_array($value))
	{
	  // Cell with a combo box
	  if (isset($value['select']))
	    {
	      $text .= "$img<select";
	      if (isset($value['name']))
		{
		  $text .= " name=\"{$value['name']}[]\" ";
		  $text .= " id=\"{$value['name']}$numRow\" ";
		}
	      if (isset($value['action']))
		{
		  $act = $this->_getAction($value['action']);
		  $text .= " onchange=\"return($act);\"";
		}
	      $text .= ">\n";
	      $select = $value['select'];
	      foreach ($select as $option) 
		{
		  $text .= "    <option value=\"".$option['key']."\"";
		  if (isset($option['select'])) 
		    $text .= " selected=\"selected\"";
		  $text .= " >".$this->getLabel($option['value']).
		    " </option>\n";
		}
	      $text .= "</select>\n";
	    }
	  // Cell with a edit field
	  else if (isset($value['input']))
	    {
	      $text .= "$img<input type=\"text\" ";
	      $text .= " name=\"{$value['name']}[]\" ";
	      $text .= " id=\"{$value['name']}$numRow\" ";
	      $text .= " value=\"{$value['input']}\" ";
	      if (isset($value['size']))
		$text .= " size=\"{$value['size']}\" ";
	      $text .= " />\n";
	    }
	  // Cell with multi line
	  else
	    {
	      foreach ($value as $line) 
		{
		  $text .= "<p";		    		
		  if (isset($line['class']))  $text .= " class=\"".$line['class']."\" ";
		  $text .= ">";
		  if (isset($line['logo'])) $img = $this->_formatImg($line['logo']);
		  $text .= $img;
		  $img='';
		  if (isset($line['action'])) $text .= $this->_getLink($line['action']);
		  if (isset($line['value']))  $text .= $line['value'];
		  else $text .= "&nbsp;";		    		
		  if (isset($line['img'])) 
		    {
		      $icon = $line['img'];
		      $size = @getImagesize($icon);
		      if ($size)
			{
			  if (isset($line['imgSize']))
			    {
			      $few = 1;
			      $max = $line['imgSize'];
			      if (isset($max['maxWidth']))
				$few = $max['maxWidth'] / $size[0];
			      $feh = 1;
			      if (isset($max['maxHeight']))
				$feh = $max['maxHeight'] / $size[1];
			      $fe = min(1, $feh, $few);
			      $size[0] *= $fe;
			      $size[1] *= $fe;
			    }
			  $text .= "\n<img src=\"$icon\" class=\"kImg\" ".
			    "alt=\"edition\" width=\"$size[0]\" height=\"$size[1]\" />\n";
			}
		    }
		  if (isset($line['action'])) $text .= $this->_getEndLink($line['action']);
		  $text .= "</p>\n";		    		
		}
	    }
	  
	}
      // Cell with simple text
      else 
	{
	  $text .= "$img$value";
	  if ($act != ''  && ($value != '' || $img != ''))  $text .= '</a>';
	}
      $text .= "</td>\n";
      
      return $text;
    }
  // }}}


  // {{{ _displayPager
  /**
   * Print the pager command
   *
   * @access public
   * @param string Path of the language file
   * @return void
   */
  function _displayPager($nbRows)
    {
      /* Display pager only if necessary */
      if (!$this->_first &&
       $this->_first + $this->_step > $this->_max)
	{
	  return;
	}

      $labelId = $this->_name;

      echo "<div style=\"text-align:center;\">\n";
      if ($nbRows > $this->_step) $nbRows = $this->_step;

      echo "<h3 style=\"text-align:center; font-size:1.25em;\">\n";
      if ($this->_first)
	{
	  $size = @getImagesize("kpage/img/first.png");
	  echo "<a href=\"#\" onclick=\"UploadFirst(this";
	  echo ",'$labelId');return false;\">\n";
	  echo "<img src=\"kpage/img/first.png\" ";
	  echo " alt=\"first\" $size[3] />\n";
	  echo "</a>\n";
	  
	  $size = @getImagesize("kpage/img/prev.png");
	  echo "<a href=\"#\" onclick=\"UploadPrev(this";
	  echo ",'$labelId');return false;\">\n";
	  echo "<img src=\"kpage/img/prev.png\" ";
	  echo " alt=\"prev\" $size[3] />\n";
	  echo "</a>\n";
	}
      else
	{
	  $size = @getImagesize("kpage/img/first-grey.png");
	  echo "<img src=\"kpage/img/first-grey.png\" ";
	  echo " alt=\"first\" $size[3] />\n";
	  $size = @getImagesize("kpage/img/prev-grey.png");
	  echo "<img src=\"kpage/img/prev-grey.png\" ";
	  echo " alt=\"prev\" $size[3] />\n";
	}
      echo $this->_max;
      
      if ($this->_first + $this->_step < $this->_max)
	{
	  $size = @getImagesize("kpage/img/next.png");
	  echo "<a href=\"#\" onclick=\"UploadNext(this";
	  echo ",'$labelId');return false;\">\n";
	  echo "<img src=\"kpage/img/next.png\" ";
	  echo " alt=\"next\" $size[3] />\n";
	  echo "</a>\n";
	  
	  $size = @getImagesize("kpage/img/last.png");
	  echo "<a href=\"#\" onclick=\"UploadLast(this";
	  echo ",'$labelId');return false;\">\n";
	  echo "<img src=\"kpage/img/last.png\" ";
	  echo "alt=\"last\" $size[3] />\n";
	  echo "</a>\n";
	}
      else
	{
	  $size = @getImagesize("kpage/img/next-grey.png");
	  echo "<img src=\"kpage/img/next-grey.png\" ";
	  echo " alt=\"first\" $size[3] />\n";
	  $size = @getImagesize("page/img/last-grey.png");
	  echo "<img src=\"kpage/img/last-grey.png\" ";
	  echo "alt=\"prev\" $size[3] />\n";
	}
      echo "</h3>\n</div>\n";
  
      echo "<input type=\"hidden\" name=\"Kfirst$labelId\" id=\"Kfirst$labelId\" ";
      echo "value=\"$this->_first\" />\n";
      echo "<input type=\"hidden\" name=\"Kstep$labelId\" id=\"Kstep$labelId\" ";
      echo "value=\"$this->_step\" />\n";
      echo "<input type=\"hidden\" name=\"Kmax$labelId\" id=\"Kmax$labelId\" ";
      echo "value=\"$this->_max\" />\n";
      echo "<div style=\"clear:both;\"></div>\n";
    }
  
  // }}}

  

  // {{{ _getAct()
  /**
   * calculate the action of a cell
   *
   * @access private
   * @param integer $index  column of the cell
   * @param array   $row    data of the row
   * @return void
   */
  function _getAct($index, $row)
    { 	
      if (is_array($row))
	{
	  reset($row);
	  $val = each($row);
	}
      else 
	return '';

      if (isset($this->_actions[$index]) &&
	  (!isset($this->_perms[$val[0]]) ||
	   $this->_perms[$val[0]] != KOD_NONE) )
	{
	  // Get the action associated to the column
          // and get the column use for obtain data: colLnk
	  $args = $this->_actions[$index];
          if (count($args) == 3)
	    {
	      $colLnk=$val[0];
	    }
          else 
	    {
	      $colLnk = $args[3];
	      if (!$colLnk || $colLnk=='') $colLnk=$val[0];
	      
	      // If nolink require, then go out
	      if ($row[$colLnk] == KAF_NONE) return '';
	    }
	  $args[3] = $row[$colLnk];
	  $this->_actions[$index] = $args;
	  $string = $this->_getLink($index);
	  $args[3] = $colLnk;
	  $this->_actions[$index] = $args;
	  return $string;
	}
      else
	return $this->_getUrl($index,$row);

    }
  // }}}

  // {{{ _getUrl()
  /**
   * Calculate the url of a cell
   *
   * @access private
   * @param integer $index column of the cell
   * @return void
   */
  function _getUrl($index, $row)
    { 	
      $val = each($row);
      if (isset($this->_urls[$index]) &&
	  (!isset($this->_perms[$val[0]]) ||
	   $this->_perms[$val[0]] != KOD_NONE) )
	{

	  // Get the action associated to the column
          // and get the column use for obtain data: colLnk
	  $action = $this->_urls[$index];
	  if ($row[$action[1]] == KAF_NONE) return false;

	  $url = $action[0].$row[$action[1]];
	  if (trim($url) == '') return false;
	  $string = "<a href=\"$url\" ";
	  $string .= "class=\"kExtUrl\" >";
	  return $string;
	}
      return false;
    }
  // }}}


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
  
  // {{{ setMaxLength()
  /**
   * set the width of the row
   *
     * @access public
     * @param integer $size
     * @return void
     */
  function setMaxLength($first, $size)
    { 	
      $this->_step = $size;
      $this->_fisrt = $first;
      $this->_isPager = true;
    }
  // }}}
  
  // {{{ setCommand()
  /**
   * set the command to naviguate in the rows
   *
   * @access public
   * @param integer $size
   * @return void
   */
  function setCommand($first, $max, $step)
    { 	
      if ($first > $max ) $first = $max-$step;
      if ($first < 0 || $first=="") $first = 0;
      $this->_step  = $step;
      $this->_first = $first;
      $this->_max   = $max;
      $this->_isPager = true;
    }
  // }}}

  // {{{ setSort()
  /**
   * set the column to sort
   *
   * @access public
   * @param integer $column number of column
   * @return void
   */
  function setSort($column)
    { 	
      $this->_sort  = $column;
    }
  // }}}

  // {{{ setSortAuth()
  /**
   * set the column to sort
   *
   * @access public
   * @param array  $columns  Value for each column
   * @return void
   */
  function setSortAuth($columns)
    { 	
      if (is_array($columns))
	$this->_isSort  = $columns;
    }
  // }}}

  // {{{ setNumber()
  /**
   * Display or not the number of the rows
   * ***** obsolete : use displayNumber ********
   * @access public
   * @param boolean $act
   * @return void
   */
  function setNumber($act)
    { 	
      $this->_isNumber  = $act;
    }
  // }}}

  // {{{ displayNumber()
  /**
   * Display or not the number of the rows
   *
   * @access public
   * @param boolean $act
   * @return void
   */
  function displayNumber($act)
    { 	
      $this->_isNumber  = $act;
    }
  // }}}

  // {{{ displaySelect
  /**
   * Display or not the column with icon and check box
   *
   * @access public
   * @param boolean $act
   * @return void
   */
  function displaySelect($act)
    { 	
      $this->_displaySelect = $act;
    }
  // }}}

  // {{{ displayEditIcon
  /**
   * Display or not the column with icon and check box
   *
   * @access public
   * @param boolean $act
   * @return void
   */
  function displayEditIcon($act)
    { 	
      $this->_displayEditIcon = $act;
    }
  // }}}

  // {{{ displayTitle
  /**
   * Display or not the title
   *
   * @access public
   * @param boolean $act
   * @return void
   */
  function displayTitle($display)
    { 	
      $this->_displayTitle = $display;
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

  // {{{ isActive
  /**
   * The form is active if it has check box or sorted column
   *
   * @access public
   * @return void
   */
  function isActive()
    { 	
      if ( $this->_isSelect ||
	   $this->_isPager  ||
	   $this->_sort)
	return true;
      else 
	return false;

    }
  // }}}

  // {{{ isRows()
  /**
   * Return a true if the elt is a row
   *
   * @access private
   * @return void
   */
  function isRows()
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

  // {{{ setColClass()
  /**
   * Set the class of the column
   *
   * @access public
   * @param array   $actions  Arrays of the actions
   * @return void
   */
  function setColClass($class)
    { 
      $this->_colClass = $class;
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
      if (isset($this->_logos[$col]) &&
	  isset($row[$this->_logos[$col]]))
	$tag = $this->_formatImg($row[$this->_logos[$col]]);
      if ($tag != '')
	return "<div style=\"float:left;padding-right:2px;\">$tag</div>";
      else 
	return $tag;
    }
  // }}}

  // {{{ _formatImg()
  /**
   * Calculate the img tag of a cell
   *
   * @access public
   * @param integer $col column of the cell
   * @param array   $row data of the row
   * @return void
   */
  function _formatImg($img)
    {
      $tag = "";
      $filename = $img;
      $size = @getImagesize($filename);
      if ($size)
	{
	  $few = 20 / $size[0];
	  $feh = 20 / $size[1];
	  $fe = min(1, $feh, $few);
	  $width = intval($size[0] * $fe);
	  $height = intval($size[1] * $fe);
	  $tag = "<img class=\"kImg\" src=\"$filename\" ";
	  $tag .= "width=\"$width\" height=\"$height\"";
	  $tag .= " />\n";
	}
      return $tag;
    }
  // }}}


  // {{{ setActions()
  /**
   * Set the actions and the fonction of a button or a rows
   *
   * @access public
   * @param array   $actions  Arrays of the actions
   * @return void
   */
  function setActions($actions)
    { 
      $this->_actions = $actions;
      $this->_displayEditIcon = isset($actions[0]);
    }
  // }}}
  
  // {{{ setBreakActions()
  /**
   * Set the actions and the fonction of a button or a rows
   *
   * @access public
   * @param array   $actions  Arrays of the actions
   * @return void
   */
  function setBreakActions($actions)
    { 
      $this->_breakActions = $actions;
    }
  // }}}

  // {{{ setRowActions()
  /**
   * Set the actions and the fonction of a button or a rows
   *
   * @access public
   * @param array   $actions  Arrays of the actions
   * @return void
   */
  function setRowActions($actions)
    { 
      $this->_rowActions = $actions;
      $this->_displayEditIcon = false;
    }
  // }}}

}

?>