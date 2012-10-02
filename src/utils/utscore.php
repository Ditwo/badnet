<?php
/*****************************************************************************
!   Module     : utils
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utscore.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.4 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:22 $
!   Mailto     : cage@free.fr
/*****************************************************************************
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

/**
* Classe de base pour la gestion des scores
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/
/** @mainpage Documentation de la kPage
 * @section intro Introduction
 *
 * Le but de la librairie Kpage est de pouvoir créer rapidement des 
 * pages HTLM, avec un ou plusieurs objets sans avoir à écrire 
 * d'instruction HTML. 
 * L'utilisation de cette librairie permet de s'affranchir de l'écriture 
 * parfois fastidieuse et souvent répétitive des codes HTML d'une page.
 * Si l'apport de cette librairie peut sembler minime lorsqu'on pense 
 * aux éléments simples d'une page HTML (par exemple les divisions),
 * son interêt devient plus évident lorsqu'on s'interesse aux éléments
 * plus complexes (par exemple les listes d'option ou les tableaux
 * avec des liens dans chaque case).
 *
 * Une Kpage a des propriétés qui permettent de contrôler son affichage et 
 * sa mise en page. Elle contient aussi des objets. Les objets
 * permettent de regrouper les informations par thème selon le besoin 
 * ou la perception du concepteur de la page. Chaque objet peut 
 * contenir un ou plusieurs autre objets et porte un nom unique. 
 * Ce nom permet de régler la mise en page et la présentation depuis une 
 * ou plusieurs feuilles de style (CSS).
 *
 * 
 * @see kPage
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 */


// {{{
/**
 * La classe utScore permet de manipuler les scores.
 *
 * **see kDiv, kForm, kMenu
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 *
 */

class utScore
{

  // {{{ properties
  /**
   * Winner points for each game
   *
   * @private
   */
  var $_winnerPt = array();
  
  /**
   * Looser points for each game
   *
   * @private
   */
  var $_looserPt = array();
  
  /**
   * Number of winner game
   *
   * @private
   */
  var $_winnerGame = 2;
  
  /**
   * Number of looser game
   *
   * @private
   */
  var $_looserGame = 0;

  /**
   * Flag for WO
   *
   * @private
   */
  var $_isWO = false;
  
  /**
   * Flag for Abort
   *
   * @private
   */
  var $_isAbort = false;
  
  /**
   * Number max of game
   *
   * @private
   */
  var $_nbMaxGame=3;

  /**
   * Number max of game
   *
   * @private
   */
  var $_nbMaxPoint=15;

  /**
   * Number of effective game
   *
   * @private
   */
  var $_nbGame = 0;
  
  // }}}
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function utScore($nbMaxGame= 3, $nbMaxPoint = 15)
    {
      $this->_nbMaxGame=$nbMaxGame;
      $this->_nbMaxPoint=$nbMaxPoint;
    }
  // }}}

  // {{{ reset
  /**
   * Initialise the internal data of the score
   *
   */
  function reset()
    {
      // Control the score
      $this->_nbGame = 0;
      $this->_winnerGame = array();
      $this->_looserGame = array();
      $this->_winnerPt = 0;
      $this->_looserPt = 0;
      $this->_isWO = false;
      $this->_isAbort = false;
    }
  // }}}


  // {{{ _initScore
  /**
   * Initialise the internal data of the score
   *
   * @param array  $scoreLeft  left points for each game
   * @param array  $scoreright right points for each game
   * @param array  $rules      rules which permit the control of the validity. Not Yet implemented
   * @return -1 if the score is not valid, 
   *          0 if the score is looser, 
   *          1 if the score is winner
   * @private
   */
  function _initScore($scoreLeft, $scoreRight, $rules="")
    {
      // Control the score
      $this->_nbGame = count($scoreLeft);
      //if (!$this->_nbGame || !$this->_nbGame > $this->_nbMaxGame)
      //	return -1;
      if ($this->_nbGame != count($scoreRight))
	return -1;

      // Check if the score is winner
      $this->_winnerGame = 0;
      $this->_looserGame = 0;
      for ($i=0; $i < $this->_nbGame; $i++)
	{
	  if ($scoreLeft[$i]>$scoreRight[$i]) $this->_winnerGame++;
	  else $this->_looserGame++;
	}

      $this->_winnerPt = $scoreLeft;
      $this->_looserPt = $scoreRight;
      if (!$this->_isAbort && 
	  $this->_winnerGame < $this->_looserGame)
	{
	  $tmp = $this->_winnerGame;
	  $this->_winnerGame = $this->_looserGame;
	  $this->_looserGame = $tmp;
	  $this->_winnerPt = $scoreRight;
	  $this->_looserPt = $scoreLeft;
	  return 0;
	}
      return 1;
    }
  // }}}
  
  // {{{ isWO
  /**
   * Renvoie true si le score est WO
   *
   * @access public
   * @param  string $score score to set
   * @return void
   */
  function isWO()
    {
      return $this->_isWO;
    }
  // }}}

  // {{{ isAbort
  /**
   * Renvoie true si le score est abort
   *
   * @access public
   * @param  string $score score to set
   * @return void
   */
  function isAbort()
    {
      return $this->_isAbort;
    }
  // }}}

  // {{{ setScore()
  /**
   * Fix the score 
   *
   * @access public
   * @param  string $score score to set
   * @return void
   */
  function setScore($score, $isRetired = false)
    {
      if ($score == '') return -1;

      $end = substr($score, -2);
      $this->_isWO = false;
      if ($end == 'WO')
	{
	  $this->_isWO = true;
	  $score = substr($score, 0, strlen($score)-3);
	}
      $this->_isAbort = false;
      if ($end == 'Ab')
	{
	  $this->_isAbort = true;
	  $score = substr($score, 0, strlen($score)-3);
	}
      if ($isRetired) 
	$this->_isAbort = true;
      $games = @explode(" ", $score);
      $nbGames = count($games);
      $scoreLeft = array();
      $scoreRight = array();
      for ($i=0; $i<$nbGames; $i++)
	{
	  $points = @explode("-", $games[$i]);
	  $scoreLeft[] = isset($points[0]) ? $points[0]:0;
	  $scoreRight[] = isset($points[1]) ? $points[1]:0;
	}
      return $this->_initScore($scoreLeft, $scoreRight);
    }
  // }}}

  // {{{ getWinScore()
  /**
   * return the winner score on string form
   *
   * @access public
   * @param  none
   * @return void
   */
  function getWinScore()
    {
      $score = "";
      $glue = "";
      for ($i=0; $i < $this->_nbGame; $i++)
	{
	  $score .= ($glue.$this->_winnerPt[$i]."-".$this->_looserPt[$i]);
	  $glue = " ";
	}
      if ($this->_isWO)
	$score .= " WO";
      if ($this->_isAbort)
	$score .= " Ab";

      return $score;
    }
  // }}}

  // {{{ getScoreFfba()
  /**
   * return the winner score on string form according FFBa format
   *
   * @access public
   * @param  none
   * @return void
   */
  function getScoreFfba()
    {
      $score = "";
      $glue = "";
      for ($i=0; $i < $this->_nbGame; $i++)
	{
	  $score .= ($glue.$this->_winnerPt[$i]."-".$this->_looserPt[$i]);
	  $glue = " / ";
	}
      if ($this->_isWO)
	$score = "WO";
      if ($this->_isAbort)
	$score .= " Ab";

      return $score;
    }
  // }}}

  
  // {{{ getLoosScore()
  /**
   * return the looser score on string form
   *
   * @access public
   * @param  none
   * @return void
   */
  function getLoosScore()
    {
      $score = "";
      $glue = "";
      for ($i=0; $i < $this->_nbGame; $i++)
	{
	  $score .= ($glue.$this->_looserPt[$i]."-".$this->_winnerPt[$i]);
	  $glue = " ";
	}
      if ($this->_isWO)
	$score .= " WO";
      if ($this->_isAbort)
	$score .= " Ab";
      return $score;
    }
  // }}}

  // {{{ getWinGames()
  /**
   * return the winner games point
   *
   * @access public
   * @param  none
   * @return void
   */
  function getWinGames()
    {
      for ( $i = 0; $i < $this->_nbGame; $i++)
	$games[$i] = $this->_winnerPt[$i];
      for ( $i = $this->_nbGame; $i<$this->_nbMaxGame; $i++)
	$games[$i] = '';
      return $games;
    }
  // }}}
  
  // {{{ getLoosGames()
  /**
   * return the looser games points
   *
   * @access public
   * @param  none
   * @return void
   */
  function getLoosGames()
    {
      for ( $i = 0; $i < $this->_nbGame; $i++)
	  $games[$i] = $this->_looserPt[$i];
      for ( $i = $this->_nbGame; $i<$this->_nbMaxGame; $i++)
	  $games[$i] = '';
      return $games;
    }
  // }}}

  
  // {{{ getNbWinGames()
  /**
   * return the winner game
   *
   * @access public
   * @param  none
   * @return void
   */
  function getNbWinGames()
    {
      if ($this->_winnerGame)
	return $this->_winnerGame;
      else
	return "0";
    }
  // }}}
  
  // {{{ getLoosGames()
  /**
   * return the looser games
   *
   * @access public
   * @param  none
   * @return void
   */
  function getNbLoosGames()
    {
      if ($this->_looserGame)
	return $this->_looserGame;
      else
	return "0";
    }
  // }}}
  
  // {{{ getNbWinPoints()
  /**
   * return the winner points
   *
   * @access public
   * @param  none
   * @return void
   */
  function getNbWinPoints()
    {
      $points = 0;
      for ($i=0; $i < $this->_nbGame; $i++)
	{
	  $points += $this->_winnerPt[$i];
	}
      if ($points)
	return $points;
      else
	return "0";
    }
  // }}}
  
  // {{{ getNbLoosPoints()
  /**
   * return the looser points
   *
   * @access public
   * @param  none
   * @return void
   */
  function getNbLoosPoints()
    {
      $points = 0;
      for ($i=0; $i < $this->_nbGame; $i++)
	{
	  $points += $this->_looserPt[$i];
	}
      if ($points)
	return $points;
      else
	return "0";
    }
  // }}}
  
  
}


?>