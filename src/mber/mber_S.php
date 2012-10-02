<?php
/*****************************************************************************
!   Module     : Members
!   File       : $Source: /cvsroot/aotb/badnet/src/mber/mber_S.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.2 $
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
require_once "mber.inc";

/**
* Module de gestion des membres : classe assistant
*
* @author Gerard CANTEGRIL
* @see to follow
*
*/

class mber_S
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
  function mber_S()
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
	  // Display main windows for member
	case KID_EDIT:
	case KID_UPDATE:
	  require_once "mber_A.php";
	  $a = new mber_A();
	  $a->start($action);
	  break;
// 	case KID_NEW:
// 	  $this->_displayFormMember($id);
// 	  break;
// 	case MBER_EDIT_NOREFRESH:
// 	  $this->_displayFormMember($id, false);
// 	  break;
// 	  $this->_updateMember(true);
// 	  break;
// 	case MBER_UPDATE_NOREFRESH:
// 	  $this->_updateMember(false);
// 	  break;
	  
	default:
	  echo "page $page demand�e depuis mber_S<br>";
	  exit();
	}
    }
  // }}}  
}

?>