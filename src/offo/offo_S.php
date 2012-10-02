<?php
/*****************************************************************************
!   Module     : Registrations
!   File       : $Source: /cvsroot/aotb/badnet/src/offo/offo_S.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
!   Mailto     : cagefree.fr
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
require_once "regi/regi.inc";

/**
* Module de gestion des inscriptions des joueurs: classe assistant
*
* @see to follow
*
*/

class regi_S
{

  // {{{ properties
  // }}}
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function regi_S()
    {
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
  function start($action)
    {
      switch ($action)
        {
 	case KID_UPDATE:
 	case REGI_SEARCH_TEAM:
 	case REGI_FIND:
 	case REGI_NEW:
	case KID_NEW:
	  require_once "regi_A.php";
	  $a = new regi_A();
	  $a->start($action);
	  break;

	case WBS_ACT_REGI:
	  require_once "regi_V.php";
	  $a = new regi_V();
	  $a->start($action);
	  break;

	  // Displaying windows for registrations
// 	case KID_EDIT:
// 	  $this->_displayFormRegistration($id, false);
// 	  break;
	default :
	  echo "regi_S->start($action) non autorise <br>";
	  exit;
	}
    }
  // }}}
}
?>