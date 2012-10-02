<?php
/*****************************************************************************
!   Module     : Registrations
!   File       : $Source: /cvsroot/aotb/badnet/src/offo/offo.inc,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.4 $
!   Author     : D.BEUVELOT
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
!   Mailto     : didier.beuvelot@free.fr
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

// Actions Administrateur uniquement
define("OFFO_OFFICIAL",           WBS_ACT_OFFOS+1);
define("OFFO_OTHER",              WBS_ACT_OFFOS+2);
define("OFFO_SEARCH",             WBS_ACT_OFFOS+3);
define("OFFO_ALL",                WBS_ACT_OFFOS+4);
define("OFFO_ACTIVITE",           WBS_ACT_OFFOS+6);

define("OFFO_NEW_OFFICIAL",           WBS_ACT_OFFOS+10);
define("OFFO_NEW_OTHER",              WBS_ACT_OFFOS+11);
define("OFFO_NEW_OFFICIAL_FROM_TEAM", WBS_ACT_OFFOS+12);
define("OFFO_NEW_OTHER_FROM_TEAM",    WBS_ACT_OFFOS+13);
define("OFFO_REG_MEMBER",             WBS_ACT_OFFOS+14);


?>