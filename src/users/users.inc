<?php
/*****************************************************************************
!   Module     : Ties
!   File       : $Source: /cvsroot/aotb/badnet/src/users/users.inc,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.1 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2005/09/07 20:51:00 $
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

// Actions Visiteur et Administrateur 
define("USERS_CHANGEPWD",     WBS_ACT_USERS+1);
define("USERS_VALIDPWD",      WBS_ACT_USERS+2);

// Actions Administrateur uniquement
define("USERS_MANAGE_EVNT_A",     WBS_ACT_USERS+10);
define("USERS_ASSIST_EVNT_A",     WBS_ACT_USERS+11);
define("USERS_FRIEND_EVNT_A",     WBS_ACT_USERS+12);
define("USERS_GUEST_EVNT_A",      WBS_ACT_USERS+13);
define("USERS_VISIT_EVNT_A",      WBS_ACT_USERS+14);


define("USERS_RIGHT_A",       WBS_ACT_USERS+3);
define("USERS_SELECTEVNT_A",  WBS_ACT_USERS+4);
define("USERS_RMEVNT_A",      WBS_ACT_USERS+5);
define("USERS_MANAGER",       WBS_ACT_USERS+6);
define("USERS_ASSISTANT",     WBS_ACT_USERS+7);
?>