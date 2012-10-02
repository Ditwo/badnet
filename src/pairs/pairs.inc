<?php
/*****************************************************************************
!   Module     : Pairs
!   File       : $Source: /cvsroot/aotb/badnet/src/pairs/pairs.inc,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.9 $
!   Author     : D.BEUVELOT
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/27 22:41:48 $
******************************************************************************/

// Actions Administrateur uniquement
define("PAIR_LIST_PAIRS",      WBS_ACT_PAIRS+1);
define("PAIR_ADD_PAIRS",       WBS_ACT_PAIRS+2);

define("PAIR_SELECT_KOPAIRS",       WBS_ACT_PAIRS+6);
define("PAIR_SELECT_GROUPPAIRS",    WBS_ACT_PAIRS+7);
define("PAIR_ADDKOROUND_PAIRS",     WBS_ACT_PAIRS+8);
define("PAIR_ADDGROUPROUND_PAIRS",  WBS_ACT_PAIRS+9);
define("PAIR_ASK_RANK",         WBS_ACT_PAIRS+10);
define("PAIR_GET_NATRANK",      WBS_ACT_PAIRS+11);
define("PAIR_GET_INTRANK",      WBS_ACT_PAIRS+12);
define("PAIR_UPDATE_NATRANK",   WBS_ACT_PAIRS+13);
define("PAIR_UPDATE_INTRANK",   WBS_ACT_PAIRS+14);
define("PAIR_UPDATE_STATUS",    WBS_ACT_PAIRS+15);

define("PAIR_EXPLODE",           WBS_ACT_PAIRS+16);
define("PAIR_IMPLODE",           WBS_ACT_PAIRS+17);
define("PAIR_DRAWINGLOT_GROUPS", WBS_ACT_PAIRS+18);
define("PAIR_GROUP2KO",          WBS_ACT_PAIRS+19);
define("PAIR_LOTKOROUND_PAIRS",  WBS_ACT_PAIRS+20);
define("PAIR_UPDATE_T2RSTATUS",  WBS_ACT_PAIRS+21);
define("PAIR_ASK_T2RSTATUS",     WBS_ACT_PAIRS+22);
define("PAIR_UPDATE_GROUP2KO",   WBS_ACT_PAIRS+23);

?>