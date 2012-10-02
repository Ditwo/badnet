<?php
/*****************************************************************************
!   Module     : Matches
!   File       : $Source: /cvsroot/aotb/badnet/src/matches/matches.inc,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.9 $
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
define("MTCH_EDIT_MEMBER_A",    WBS_ACT_MATCHS+1);
define("MTCH_REG_MEMBER_A",     WBS_ACT_MATCHS+2);
define("MTCH_EDIT_PAIR_A",      WBS_ACT_MATCHS+3);
define("MTCH_STATUS_MATCH_A",   WBS_ACT_MATCHS+4);

define("MTCH_PLAY_MATCH_A",     WBS_ACT_MATCHS+5);
define("MTCH_RAZ_A",            WBS_ACT_MATCHS+6);

define("MTCH_UPDATE_TEAM0_A",   WBS_ACT_MATCHS + 9);
define("MTCH_UPDATE_TEAM1_A",   WBS_ACT_MATCHS + 10);

define("MTCH_UPDATE_PAIR0_A",   WBS_ACT_MATCHS + 11);
define("MTCH_UPDATE_PAIR1_A",   WBS_ACT_MATCHS + 12);

define("MTCH_PDF_A",   WBS_ACT_MATCHS + 14);
define("MTCH_PDF_INDIV_A",   WBS_ACT_MATCHS + 15);

define("MTCH_PDF_LIST_A",   WBS_ACT_MATCHS + 16);
define("MTCH_PDF_LIST_LITE_A",   WBS_ACT_MATCHS + 17);

?>
