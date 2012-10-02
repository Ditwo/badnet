<?php
/*****************************************************************************
!   Module     : Matches
!   File       : $Source: /cvsroot/aotb/badnet/src/matches/matches_S.php,v $
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
require_once "matches.inc";

/**
* Module de gestion des matches: classe assitant
*
*/

class matches_S
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
  function matches_S()
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
	  // Edit match in a tie for a team event
	case KID_EDIT:
 	case MTCH_UPDATE_TEAM0_A:
 	case MTCH_UPDATE_TEAM1_A:
	  require_once "matches_A.php";
	  $a = new matches_A();
	  $a->start($action);
	  break;
	default :
	  echo "match_S->start($action) non autorise <br>";
	}
    }
  // }}}
    
}
?>