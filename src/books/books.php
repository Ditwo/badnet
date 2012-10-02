<?php
/*****************************************************************************
!   Module    : AddressBooks
!   Version   : v0.0.1
!   Author    : G.CANTEGRIL
!   Co-author :
!   Mailto    : cage@aotb.org
!   Date      : 01-12-2003
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
******************************************************************************
!   Greetings & thanks to:
!      - myself
******************************************************************************
!   History:
!      v0.0.1        creation
******************************************************************************
!   Todo:
!      
******************************************************************************/

require_once "books.inc";
require_once "base.php";


/**
* Module de gestion des carnets d'adresses : classe visiteurs
*
* @author Gerard CANTEGRIL
* @see to follow
*
*/

class Books
{

    // {{{ properties

    /**
     * Utils objet
     *
     * @var     object
     * @access  private
     */
    var $_ut;

    /**
     * Database access object
     *
     * @var     object
     * @access  private
     */
    var $_dt;

    // }}}

    // {{{ constructor
    /**
     * Constructor. 
     *
     * @access public
     * @return void
     */
    function Books()
    {
    }
    // }}}

}

?>