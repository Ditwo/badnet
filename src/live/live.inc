<?php
/*****************************************************************************
!   Module     : live
!   File       : $Source: /cvsroot/aotb/badnet/src/live/live.inc,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.10 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2006/12/19 12:44:50 $
!   Mailto     : cage@free.fr
******************************************************************************/

// Actions Administrateur uniquement
define("LIVE_ORDER_MATCH_A",  WBS_ACT_LIVE+1);
define("LIVE_MOVE_UP_A",      WBS_ACT_LIVE+2);
define("LIVE_MOVE_DOWN_A",    WBS_ACT_LIVE+3);
define("LIVE_MOVE_END_A",     WBS_ACT_LIVE+4);

define("LIVE_VIEW_PAIR1_A",     WBS_ACT_LIVE+5);
define("LIVE_VIEW_PAIR2_A",     WBS_ACT_LIVE+6);

define("LIVE_STATUS_MATCH_A",    WBS_ACT_LIVE+7);
define("LIVE_UPDATE_STATUS_A",   WBS_ACT_LIVE+8);
define("LIVE_TIE_PDF",           WBS_ACT_LIVE+9);
define("LIVE_AUTOORDER_MATCH_A", WBS_ACT_LIVE+10);
define("LIVE_INIT_SERVER",       WBS_ACT_LIVE+11);
define("LIVE_SCORE",             WBS_ACT_LIVE+12);
define("LIVE_SEND_SERVER",       WBS_ACT_LIVE+13);
define("LIVE_LAST_RESULTS",      WBS_ACT_LIVE+14);
define("LIVE_SET_UMPIRE",        WBS_ACT_LIVE+15);
define("LIVE_VALIDATE",          WBS_ACT_LIVE+17);

define("LIVE_UMPIRE_LOOK",     WBS_ACT_LIVE+18);
define("LIVE_UMPIRE_EDIT",     WBS_ACT_LIVE+19);
define("LIVE_UMPIRE_UPDATE",   WBS_ACT_LIVE+20);

define("LIVE_OPTION_EDIT",       WBS_ACT_LIVE+21);
define("LIVE_OPTION_UPDATE",     WBS_ACT_LIVE+22);
define("LIVE_SELECT_COURT",      WBS_ACT_LIVE+23);
define("LIVE_SELECT_ENDED",      WBS_ACT_LIVE+25);
define("LIVE_PALMARES",          WBS_ACT_LIVE+26);

define("LIVE_LIVE_MATCH",        WBS_ACT_LIVE+27);
define("LIVE_PRESENCE_PDF",      WBS_ACT_LIVE+28);
define("LIVE_DISPLAY_STATUS",    WBS_ACT_LIVE+29);
define("LIVE_AJAX_COURT_LIVE",   WBS_ACT_LIVE+30);
define("LIVE_AJAX_LIST_COURT",   WBS_ACT_LIVE+31);
define("LIVE_CLASSE",            WBS_ACT_LIVE+32);
define("LIVE_SELECT_ORDER",      WBS_ACT_LIVE+33);


// Actions Visiteur
define("LIVE_ALONE",    WBS_ACT_LIVE+40);


?>
