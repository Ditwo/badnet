<?php
/*****************************************************************************
!   Module     : help
!   File       : $Source: /cvsroot/aotb/badnet/src/help/help_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.4 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
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

require_once dirname(__FILE__)."/../regi/regi.inc";


/**
* Module de gestion du l'aide : classe administrateur
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class help_A
{

  // {{{ properties
  
  /**
   * Utils objet
   *
   * @var     object
   * @access  private
   */
  var $_ut;
  
  // }}}

  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function help_A()
    {
      $this->_ut = new utils();
    }
  // }}}

  // {{{ start()
  /**
   * Start the connexion processus
   *
   * @access public
   * @param  integer $action  what to do
   * @return void
   */
  function start($page)
    {
      if ($page == WBS_ACT_PHPINFO)
	{
	  phpinfo();
	  exit;
	}
      $lang = utvars::getLanguage();
      $file = "{$page}_$lang.pdf";
      $path = dirname(__FILE__)."/$file";
      if (!file_exists($path))
	$file = "{$page}_fra.pdf";

      $path = dirname(__FILE__)."/$file";
      if (!file_exists($path))
	echo "Rubrique aide :$page ($path)";
      else
	{
	  $url = dirname($_SERVER['PHP_SELF'])."/help/$file";
	  header("Location: $url");
	}
      exit(); 
    }
  // }}}
}
?>