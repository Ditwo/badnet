<?php
/*****************************************************************************
!   Module     : utils
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utdate.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.11 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/03/06 18:02:11 $
!   Mailto     : cage@free.fr
******************************************************************************
!   License   : Licensed under GPL [http://www.gnu.org/copyleft/gpl.html]
!      This program is free software; you can redistribute it and/or
!      modify it under the terms of the GNU General Public License
!      as published by the Free Software Foundation; either version 2
!      of the License, or (at your option) any later version.
!
!      This program is distributed in the hope that it will be useful,
!      but WITHOUT ANY WARRANTY; without even the implied warranty of
!      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
!      GNU General Public License for more details.
!
!      You should have received a copy of the GNU General Public License
!      along with this program; if not, write to the Free Software
!      Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, 
!      USA.
******************************************************************************/
require_once "const.inc";


/**
* Classe de base pour la gestion des dates
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class Utdate
{

  // {{{ properties
  
  /**
   * Year of the date
   *
   * @var     integer
   * @access  private
   */
  var $_year=0;
  /**
   * Month of the date
   *
   * @var     integer
   * @access  private
   */
  var $_month=0;
  /**
   * Day of the date
   *
   * @var     integer
   * @access  private
   */
  var $_day=0;
  /**
   * Hour of the date
   *
   * @var     integer
   * @access  private
   */
  var $_hours=0;
  /**
   * Minute of the date
   *
   * @var     integer
   * @access  private
   */
  var $_minutes=0;
  /**
   * Seconds of the date
   *
   * @var     integer
   * @access  private
   */
  var $_seconds=0;
  
  // }}}
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function Utdate($date="")
    {
      $date = getdate();
      $this->_year    = $date['year'];
      $this->_month   = $date['mon'];
      $this->_day     = $date['mday'];
      $this->_hours   = $date['hours'];
      $this->_minutes = $date['minutes'];
      $this->_seconds = $date['seconds'];
    }
  // }}}

  // {{{ setIsoDate
  /**
   * set the date
   *
   * @access public
   * @return void
   */
  function setIsoDate($date)
    {
      $date .= " 00:00:00";
      $this->setIsoDateTime($date);
    }
  // }}}

  // {{{ setIsoDateTime
  /**
   * set the date
   *
   * @access public
   * @return void
   */
  function setIsoDateTime($date)
    {
      list($this->_year, $this->_month, $this->_day, 
	   $this->_hours, $this->_minutes, $this->_seconds) =
	sscanf($date, "%04u-%02u-%02u %02u:%02u:%02u");
    }
  // }}}

  // {{{ setTimeStamp
  /**
   * set the date
   *
   * @access public
   * @return void
   */
  function setTimeStamp($date)
    {
      list($this->_year, $this->_month, $this->_day, 
	   $this->_hours, $this->_minutes, $this->_seconds) =
	sscanf($date, "%04u%02u%02u%02u%02u%02u");
    }
  // }}}

  // {{{ setFrDate
  /**
   * set the date
   *
   * @access public
   * @return void
   */
  function setFrDate($date, $sep='-')
    {
      $this->setFrDateTime($date, $sep);
    }
  // }}}

  // {{{ setTime
  /**
   * set the time
   *
   * @access public
   * @return void
   */
  function setTime($time)
    {
      list($this->_hours, $this->_minutes, $this->_seconds) =
	sscanf($time, "%u:%u:%u");
    }
  // }}}

  // {{{ setFrDateTime
  /**
   * set the date
   *
   * @access public
   * @return void
   */
  function setFrDateTime($date, $sep='-')
    {
      list($this->_day, $this->_month, $this->_year, 
	   $this->_hours, $this->_minutes, $this->_seconds) =
	sscanf($date, "%u$sep%u$sep%u %u:%u:%u");
      if($this->_day > 0)
	if( $this->_year < 30) $this->_year += 2000;
	else if($this->_year < 100) $this->_year += 1900;

    }
  // }}}

  // {{{ getDate
  /**
   * Return the date
   *
   * @access public
   * @return string date 
   */
  function getDate($sep='-')
    {
      if ($this->_year)
	$date =  sprintf("%02d$sep%02d$sep%02d", $this->_day,
			 $this->_month, $this->_year);
      else
	$date ="";
      return $date;
    }
  // }}}

  // {{{ getDiff
  /**
   * Return the time difference 
   *
   * @access public
   * @return string date 
   */
  function getDiff($isoDateTime)
    {
      list($year, $month, $day,$hours, $minutes, $seconds) =
	sscanf($isoDateTime, "%04u-%02u-%02u %02u:%02u:%02u");

      $start = mktime ( $this->_hours, $this->_minutes, $this->_seconds, 
			$this->_month, $this->_day, $this->_year);
      $end = mktime ( $hours, $minutes, $seconds, $month, $day, $year);
      $elaps = ($end-$start)/60;
      return $elaps;
    }
  // }}}
  
      // {{{ getDayDiff
  /**
   * Return the number of day betwwen to date
   *
   * @access public
   * @return string date 
   */
  function getDayDiff($frDateTime, $sep='-')
    {
      list($day, $month, $year, $hours, $minutes, $seconds) =
	sscanf($frDateTime, "%u$sep%u$sep%u %u:%u:%u");
    	
      $start = mktime ( 0, 0, 0, $this->_month, $this->_day, $this->_year);
      $end = mktime ( 0, 0, 0, $month, $day, $year);
      $nbDay = intval(($end-$start)/(60 * 60 * 24));
      return $nbDay;
    }
  // }}}
    

    
  // {{{ getTime
  /**
   * Return the time
   *
   * @access public
   * @return string date 
   */
  function getTime($sep=':')
    {
      if ($this->_hours || $this->_minutes)
	return sprintf("%02d{$sep}%02d", $this->_hours,
		       $this->_minutes);
      else
	return "";
    }
  // }}}

  // {{{ getFullTime
  /**
   * Return the time
   *
   * @access public
   * @return string date 
   */
  function getFullTime($sep=':')
    {
      if ($this->_hours || $this->_minutes)
	return sprintf("%02d{$sep}%02d{$sep}%02d", 
		       $this->_hours,
		       $this->_minutes,
		       $this->_seconds);
      else
	return "";
    }
  // }}}

  // {{{ getDateTime
  /**
   * Return the time
   *
   * @access public
   * @return string date 
   */
  function getDateTime()
    {
      $str = '';
      if ($this->_hours != 0 || $this->_minutes != 0 || 
	  $this->_seconds != 0 || $this->_month != 0 || 
	  $this->_day != 0 ||  $this->_year != 0)
	{
	  $date = mktime($this->_hours, $this->_minutes, $this->_seconds,
			 $this->_month,$this->_day,  $this->_year, -1 );
		// DBBN - changement format de date
		$str = strftime("%d-%m-%y %H:%M", $date);
		//$str = strftime("%d/%m/%Y %Hh%M", $date);
	}
      return $str;
    }
  // }}}

// DBBN - debut  
  /**
   * Retourne la date au format "%d/%m/%Y %Hh%M"
   * Utilisee pour les convocations
   *
   */
  function getDateTimeConvoc()
    {
      $str = '';
      if ($this->_hours != 0 || $this->_minutes != 0 || 
	  $this->_seconds != 0 || $this->_month != 0 || 
	  $this->_day != 0 ||  $this->_year != 0)
	{
	  $date = mktime($this->_hours, $this->_minutes, $this->_seconds,
			 $this->_month,$this->_day,  $this->_year, -1 );
		$str = strftime("%d/%m/%Y %Hh%M", $date);
	}
      return $str;
    }
// DBBN - fin

  // {{{ getDateWithDay
  /**
   * Return the time
   *
   * @access public
   * @return string date 
   */
  function getDateWithMonth()
    {
      $date = mktime($this->_hours, $this->_minutes, $this->_seconds,
		     $this->_month,$this->_day,  $this->_year, -1 );
      $str = utf8_decode(strftime("%d %B %Y", $date));
      return $str;
    }
  // }}}

  // {{{ getDateWithDay
  /**
   * Return the time
   *
   * @access public
   * @return string date 
   */
  function getDateWithDay()
    {
      $date = mktime($this->_hours, $this->_minutes, $this->_seconds,
		     $this->_month,$this->_day,  $this->_year, -1 );
      $str = utf8_decode(strftime("%A %d %B %Y", $date));
      return $str;
    }
  // }}}

  // {{{ getDateTimeWithDay
  /**
   * Return the time
   *
   * @access public
   * @return string date 
   */
  function getDateTimeWithDay()
    {
      $date = mktime($this->_hours, $this->_minutes, $this->_seconds,
		     $this->_month,$this->_day,  $this->_year, -1 );
      $str = utf8_decode(strftime("%A %d %B %Y", $date));
      return $str;
    }
  // }}}

  // {{{ getIsoDateTime
  /**
   * Return the date and time
   *
   * @access public
   * @return string date 
   */
  function getIsoDateTime()
    {
      return sprintf("%04d-%02d-%02d %02d:%02d:%02d", $this->_year,
		     $this->_month, $this->_day, $this->_hours,
		     $this->_minutes, $this->_seconds);
    }
  // }}}

  // {{{ getIsoDate
  /**
   * Return the date
   *
   * @access public
   * @return string date 
   */
  function getIsoDate()
    {
      return sprintf("%04d-%02d-%02d", $this->_year,
		     $this->_month, $this->_day);
    }
  // }}}

  // {{{ getYear
  /**
   * Return the date
   *
   * @access public
   * @return string date 
   */
  function getYear()
    {
      return sprintf("%04d", $this->_year);
    }
  // }}}

  // {{{ getIsoDate
  /**
   * Return the date
   *
   * @access public
   * @return string date 
   */
  function getShortDate()
    {
      //setlocale();
      $date = mktime($this->_hours, $this->_minutes, $this->_seconds,
		     $this->_month,$this->_day,  $this->_year, -1 );
      $str = utf8_decode(strftime("%a %d %b", $date));
      return $str;

    }
  // }}}

  // {{{ elaps
  /**
   * Return the elaps time in minute
   *
   * @access public
   * @return string date 
   */
  function elaps($lastIsoTime = null)
    {

      $first = mktime($this->_hours, $this->_minutes, $this->_seconds,
		    $this->_month, $this->_day,$this->_year);
      if ($lastIsoTime === null)
	$last = time();
      else
	{
	  list($year, $month, $day, $hours, $minutes, $seconds) =
	    sscanf($lastIsoTime, "%04u-%02u-%02u %02u:%02u:%02u");
	  $last = mktime($hours, $minutes, $seconds, $month, $day, $year);
	}
      $delta = ($last-$first)/60;
      return $delta;
    }
  // }}}

  // {{{ addMinute
  /**
   * Add some minuts to the current time
   *
   * @access public
   * @return string date 
   */
  function addMinute($minutes)
    {
      $now = mktime($this->_hours, $this->_minutes, $this->_seconds,
		    $this->_month, $this->_day,$this->_year);
      //echo "--$minutes; {$this->_hours}:{$this->_minutes}:{$this->_seconds};";
      if ($now > 0)
	{
	  $now += $minutes*60;
	  $date = getdate($now);
	  $this->_year    = $date['year'];
	  $this->_month   = $date['mon'];
	  $this->_day     = $date['mday'];
	  $this->_hours   = $date['hours'];
	  $this->_minutes = $date['minutes'];
	  $this->_seconds = $date['seconds'];
	}
      //echo "{$this->_hours}:{$this->_minutes}:{$this->_seconds};<br>";
      return;
    }
  // }}}

  // {{{ isWeekEnd
  /**
   * Return true if date is WE (vendredi, samedi ou dimanche)
   *
   * @access public
   * @return string date 
   */
  function isWeekEnd()
    {
      $now = mktime($this->_hours, $this->_minutes, $this->_seconds,
		    $this->_month, $this->_day,$this->_year);
      $date = getdate($now);
      return ($date['wday']== 0 ||$date['wday']>4);
    }
  // }}}
  
  // {{{ getmicrotime
  /**
   * return the time in millisecond
   *
   * @access public
   * @param  integer $num  Position of the label
   * @return void
   */
  function getMicroTime()
    {
      list($usec, $sec) = explode(" ",microtime());
      return ((float)$usec + (float)$sec);
    }
  // }}}

   // {{{ getDateWithDayWithoutYear
  /**
   * Return the time
   *
   * @access public
   * @return string date 
   */
  function getDateWithDayWithoutYear()
    {
      $date = mktime($this->_hours, $this->_minutes, $this->_seconds,
		     $this->_month,$this->_day,  $this->_year, -1 );
      $str = strftime("%A %d %B", $date);
      return $str;
    }
  // }}}

 
}


?>
