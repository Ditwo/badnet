<?php
/*****************************************************************************
!   Module     : live
!   File       : $Source: /cvsroot/aotb/badnet/src/live/live_S.php,v $
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

//require_once dirname(__FILE__)."/base_V.php";
require_once dirname(__FILE__)."/live.inc";

/**
* Module de gestion du live : classe visiteurs
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class live_S
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
  function live_S()
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
  function start($page)
    {
      switch ($page)
	{
	case LIVE_TIE_PDF:
	  $ute = new utevent();
	  $tieId = kform::getData();
	  if (! $ute->isTieAuto($tieId))
	    {
	      require_once dirname(__FILE__)."/live_V.php";
	      $a = new live_V();
	      $a->start($page);
	    }
	  else
	    {
	      require_once dirname(__FILE__)."/live_A.php";
	      $a = new live_A();
	      $a->start($page);
	    }
	  break;

	default:
	  require_once dirname(__FILE__)."/live_V.php";
	  $a = new live_V();
	  $a->start($page);
	  break;
	  exit;
	}
    }
  // }}}

}
?>
