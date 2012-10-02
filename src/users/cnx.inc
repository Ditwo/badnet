<?php
/*****************************************************************************
!   Module    : Users
!   Version   : v0.0.1
!   Author    : G.CANTEGRIL
!   Co-author :
!   Mailto    : cage@free.fr
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
require_once dirname(__FILE__)."/../utils/const.inc";

// Actions Visiteur et Administrateur 
define("CNX_ANONYM",        WBS_CNX);
define("CNX_NEWUSER",       WBS_CNX+1);
define("CNX_CREATEUSER",    WBS_CNX+2);
define("CNX_VALIDLOG",      WBS_CNX+3);
define("CNX_NEWLANG",       WBS_CNX+4);
define("CNX_SENDPWD",       WBS_CNX+5);
define("CNX_LOOSEPWD",      WBS_CNX+6);
define("CNX_SENDEMAIL",     WBS_CNX+7);

define("CNX_SELECT_EVENT",  KID_USER-10);
define("CNX_SELECT_BOOK",   KID_USER-11);

?>