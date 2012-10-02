<?php
/*****************************************************************************
 !   Module     : kPage
 !   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kdraw.php,v $
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

class kDraw  extends kElt
{

	// {{{ properties
	/**
	 * Size of the draw (number of places)
	 *
	 * @var     integer
	 * @access  private
	 */
	var $_size=2;

	/**
	 * Number of places
	 *
	 * @var     integer
	 * @access  private
	 */
	var $_places=2;

	/**
	 * Number of qualifying
	 *
	 * @var     integer
	 * @access  private
	 */
	var $_qualif=1;
	var $_numberCols=0;

	/**
	 * Is seed displayed for each column
	 *
	 * @var     integer
	 * @access  private
	 */
	var $_displaySeed=true;

	/**
	 * Is flag displayed for each column
	 *
	 * @var     integer
	 * @access  private
	 */
	var $_displayFlag=false;

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
	 * Seed for different column
	 *
	 * @var     array
	 * @access  private
	 */
	var $_seeds=array();

	/**
	 * Score for different column
	 *
	 * @var     array
	 * @access  private
	 */
	var $_scores=array();

	/**
	 * Name for different column
	 *
	 * @var     array
	 * @access  private
	 */
	var $_names=array();

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
	function kDraw($name, $qualif)
	{

		$this->_type = "Draw";
		$this->_name = $name;
		$this->_isMandatory = false;

		$this->_qualif = $qualif;
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

		// Size of the draw
		if (isset($this->_names[0])) $size = count($this->_names[0]);
		else $size = 4;

		// Rows number
		$nbRows = $size * 2;

		// Main column numbers
		if ($this->_numberCols) $nbMainCols = $this->_numberCols;
		else
		{
	  		
	  		if (count($this->_names) == 1) 
	  		{
	  			$nbMainCols = 1;
	  			$nbRows = 2;
	  		}
	  		else $nbMainCols = 2;
	  		
	  		while (($size /= 2) > 1) $nbMainCols++;
	   
	  		$size = $this->_qualif;
	  		while (($size /= 2) >= 1) $nbMainCols--;
		}
		// Column numbers of each main column
		$nbCols[0] = 4; // Rows num, seed, flag, player
		for ($i=1; $i < $nbMainCols; $i++)
		{
	  		$nbCols[$i] = 1 + ($this->_displaySeed===true?1:0) +
	  				($this->_displayFlag===true?1:0);
		}

		// First column = num rows
		$i = 1;
		for ($j=1; $j<$nbRows; $j+=2)
		{
	  		$value[$j][0] = $i++;
	  		$class[$j][0] = "kDrawNumber";
		}

		//Values of other columns
		$numCol=1;
		$step=0;
		for ($i=0; $i<$nbMainCols; $i++)
		{
	  		$start = pow(2, $i);
	  		$period = 2*$start;
	  		$numCol += $step;
	  		if(isset($this->_names[$i])) $names = $this->_names[$i];
	  		else  $names = NULL;
	  		if(isset($this->_seeds[$i])) $seeds = $this->_seeds[$i];
	  		else $seeds = NULL;
	  		if(isset($this->_logos[$i])) $logos = $this->_logos[$i];
	  		else $logos = NULL;
	  		if(isset($this->_scores[$i])) $scores = $this->_scores[$i];
	  		else $scores = NULL;
	  		$numRows = 0;
	  		for ($j=$start; $j<$nbRows; $j+=$period)
	  		{
	  			$step = 0;
	  			if (!$i || $this->_displaySeed===true)
	  			if (isset($seeds[$numRows])) $value[$j][$numCol + $step++] = $seeds[$numRows];
				else $value[$j][$numCol + $step++] = "&nbsp;";
		  		if (!$i || $this->_displayFlag===true)
		  		if (isset($logos[$numRows]))
		  		$value[$j][$numCol + $step++] = $this->_getImg($logos[$numRows]);
		  		else $value[$j][$numCol + $step++] = "&nbsp;";
		  		if (isset($names[$numRows]))
		  		{
		  	if (!$i)
		  	{
		  		$link[$j][$numCol + $step] =
		  		$this->_getAct(0, $i, $numRows);
		  		$endLink[$j][$numCol + $step] = $this->_getEndLink(0);
		  	}
		  	$value[$j][$numCol + $step++] = $names[$numRows];
		  }
		  else
		  {
		  	$value[$j][$numCol + $step] = "&nbsp;";
		  }
		  if ($i)
		  if (isset($scores[$numRows]))
		  {
		  	$link[$j+1][$numCol + $step-1] =
		  	$this->_getAct(1, $i, $numRows);
		  	$value[$j+1][$numCol + $step-1] = $scores[$numRows];
		  	$endLink[$j+1][$numCol + $step-1] = $this->_getEndLink(1);
		  }
		  //		else
		  //$value[$j+1][$numCol + $step-1] = "&nbsp;";
		  if (($i == $nbMainCols-1) &&  $this->_qualif>1)
		  {
		  	$value[$j-1][$numCol] = "Qualif ".(($j-$start)/$period+1);
		  	$class[$j-1][$numCol] = "kDrawQualif";
		  }
		  $numRows++;
	  }
		}
		// Construct the classes
		$numCol = 1;
		$step=0;
		for ($i=0; $i<$nbMainCols; $i++)
		{
	  $start = pow(2, $i);
	  $period = 2*$start;
	  $numCol += $step;
	  for ($j=$start; $j<$nbRows; $j+=2*$period)
	  {
	  	$step = 0;
	  	if (!$i || $this->_displaySeed===true)
	  	$class[$j][$numCol + $step++] = "kDrawBottom";
	  	if (!$i || $this->_displayFlag===true)
	  	$class[$j][$numCol + $step++] = "kDrawBottom";
	  	$class[$j][$numCol + $step] = "kDrawBottom";
	  	if ($i <$nbMainCols-1)
	  	$class[$j+1][$numCol + $step] = "kDrawScore";
	  	else
	  	$class[$j+1][$numCol + $step] = "kDrawScore2";
	  	for ($k=2; $k<$period; $k++)
	  	{
	  		$step = 0;
	  		if (!$i || $this->_displaySeed===true) $step++;
	  		if (!$i || $this->_displayFlag===true) $step++;
	  		if ($i < $nbMainCols-1)
	  		$class[$j+$k][$numCol + $step++] = "kDrawRight";
	  	}
	  	$step = 0;
	  	if (!$i || $this->_displaySeed===true)
	  	$class[$j+$k][$numCol + $step++] = "kDrawBottom";
	  	if (!$i || $this->_displayFlag===true)
	  	$class[$j+$k][$numCol + $step++] = "kDrawBottom";
	  	if ($i <$nbMainCols-1)
	  	$class[$j+$k][$numCol + $step] = "kDrawBottomRight";
	  	else
	  	$class[$j+$k][$numCol + $step] = "kDrawBottom";
	  	$class[$j+$k+1][$numCol + $step++] = "kDrawScore2";

	  }
		}
		//echo "<br>lien<br>";print_r($link);
		//echo "<br>action<br>";print_r($this->_actions);
		// Display the draw
		$text = "<table border=0 class=\"kDraw\">\n";
		$text .= "<thead><tr class=\"kHead\">\n";
		for ($i=0; $i<$nbMainCols; $i++)
		{
	  $text .= "<th class=\"kHead\" colspan=\"";
	  $text .= $nbCols[$i]."\">";
	  if (isset($this->_titles[$i]))
	  $text .= $this->getLabel($this->_titles[$i]);
	  else
	  $text .= $this->getLabel("title".($nbMainCols-$i));
	  $text .= "</th>\n";
		}
		$text .= "</tr></thead>\n";
		echo $text;

		for ($i=1; $i<$nbRows; $i++)
		{
	  $numCol = 0;
	  $text = "<tr>\n";
	  for ($j=0; $j<$nbMainCols; $j++)
	  {
	  	for ($k=0; $k<$nbCols[$j];$k++)
	  	{
	  		$text .= "<td class=\"";
	  		if (isset($class[$i][$numCol])) $text .= $class[$i][$numCol];
	  		else $text .= "nothing";
	  		$text .= "\">";
	  		if (isset($value[$i][$numCol]))
	  		{
	  			if (isset($link[$i][$numCol]))
	  			$text .= $link[$i][$numCol];
	  			$text .= $value[$i][$numCol];
	  			if (isset($endLink[$i][$numCol]))
	  			$text .= $endLink[$i][$numCol];
	  		}
	  		else
	  		$text .= "&nbsp;";
	  		$text .= "</td>\n";
	  		$numCol++;
	  	}
	  }
	  $text .= "</tr>\n";
	  echo $text;
		}
		echo "</tbody>\n</table>\n";
		//echo "</div>\n";
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


	// {{{ setLogos()
	/**
	 * Set the logos on a column
	 *
	 * @access public
	 * @param array   $actions  Arrays of the actions
	 * @return void
	 */
	function setLogos($column, $logos)
	{
		$this->_logos[$column-1] = $logos;
	}
	// }}}

	// {{{ setNumCol()
	/**
	 * Set the logos on a column
	 *
	 * @access public
	 * @param array   $actions  Arrays of the actions
	 * @return void
	 */
	function setNumCol($number)
	{
		$this->_numberCols = $number;
	}
	// }}}

	// {{{ setValues()
	/**
	 * Set the values of a column
	 *
	 * @access public
	 * @param array   $actions  Arrays of the actions
	 * @return void
	 */
	function setValues($column, $values)
	{
		//echo "<br>Values<br>";print_r($values);
		foreach($values as $value)
		{
	  		if (is_array($value))
	  		{
	  			if (isset($value['seed']))
	  			if (is_array($value['seed']))
		  		{
		  			$buf = '';
		  			if (isset($value['seed']['action'])) $buf .= $this->_getLink($value['seed']['action']);
		  			$buf .= $value['seed']['name'];
		  			if (isset($value['seed']['action']))$buf .= '</a>';
		  			$seed[] = $buf;
		  			//print_r($buf);
		  		}
		  		else $seed[] = $value['seed'];
		  		else $seed[] = "";
		  		if (isset($value['value']))
		  		if (is_array($value['value']))
		  		{
		  			$lines = $value['value'];
		  			$buf = '';
		  			foreach($lines as $line)
		  			{
		  				$buf .= "<p>";
		  				if (isset($line['logo'])) $buf .= $this->_getImg($line['logo']);
		  				if (isset($line['action'])) $buf .= $this->_getLink($line['action']);
		  				$buf .= $line['name'];
		  				if (isset($line['action'])) $buf .= '</a>';
		  				$buf .= "</p>\n";
		  			}
		  			$val[] = $buf;
		  		}
		  		else $val[] = $value['value'];
		  		else $val[] = "";
		  		if (isset($value['logo'])) $logo[] = $value['logo'];
		  		else $logo[] = "";
		  		if (isset($value['score'])) $score[] = $value['score'];
		  		else $score[] = "";
		  		if (isset($value['link'])) $id[] = $value['link'];
		  		else $id[] = "";
	  		}
	  		else
	  		{
	  			$val[] = $value;
	  			$score[] = '&nbsp;';
	  			$logo[] = '&nbsp;';
	  			$seed[] = '&nbsp;';
	  			$id[] = KAF_NONE;
	  		}
		}
		$this->_names[$column-1] = $val;
		$this->_scores[$column-1] = $score;
		$this->_seeds[$column-1] = $seed;
		$this->_logos[$column-1] = $logo;
		$this->_id[$column-1] = $id;
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
	function _getAct($numAct, $numCol, $numRow)
	{
		if (!isset($this->_actions[$numAct]) ||
		!isset($this->_id[$numCol][$numRow]) ||
		$this->_id[$numCol][$numRow] == KAF_NONE )
		{
	  return '';
		}


		$args = $this->_actions[$numAct];
		$args[3] = $this->_id[$numCol][$numRow];
		$this->_actions[$numAct] = $args;
		$string = $this->_getLink($numAct);
		return $string;
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
	function _getImg($img)
	{
		$tag = "";
		$filename = $img;
		$size = @getImagesize($filename);
		if ($size)
		{
	  $few = 30 / $size[0];
	  $feh = 15 / $size[1];
	  $fe = min(1, $feh, $few);
	  $size[0] *= $fe;
	  $size[1] *= $fe;
	  $tag = "<img class=\"kImg\" src=\"$filename\" ";
	  $tag .= "width=\"$size[0]\" height=\"$size[1]\"";
	  $tag .= " />\n";
		}
		return $tag;
	}
	// }}}

}

?>