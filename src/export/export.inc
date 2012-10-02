<?php
/*****************************************************************************
!   Module     : Export
!   File       : $Source: /cvsroot/aotb/badnet/src/export/export.inc,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.9 $
******************************************************************************/

define("EXPORT_BADNET",        WBS_ACT_EXPORT+1);
define("EXPORT_SEND_FILE",     WBS_ACT_EXPORT+2);
define("EXPORT_SEND_EVENT",    WBS_ACT_EXPORT+3);
define("EXPORT_BADNET24",      WBS_ACT_EXPORT+5);
define("EXPORT_SQUASHRANK",    WBS_ACT_EXPORT+6);

define("IMPORT_GET_SOURCE",    WBS_ACT_EXPORT+10);
define("IMPORT_GET_FILES",     WBS_ACT_EXPORT+11);

define("IMPORT_LIST_EVENTS",    WBS_ACT_EXPORT+12);
define("IMPORT_SELECT_EVENT",   WBS_ACT_EXPORT+13);
define("IMPORT_EVENT",          WBS_ACT_EXPORT+14);
define("IMPORT_START",          WBS_ACT_EXPORT+15);

define("IMPORT_PARSE_TEAMS",   WBS_ACT_EXPORT+20);
define("IMPORT_PARSE_REGIS",   WBS_ACT_EXPORT+21);
define("IMPORT_PARSE_DRAWS",   WBS_ACT_EXPORT+22);
define("IMPORT_PARSE_PAIRS",   WBS_ACT_EXPORT+23);
define("IMPORT_PARSE_MATCHS",  WBS_ACT_EXPORT+24);
define("IMPORT_PARSE_T2R"   ,  WBS_ACT_EXPORT+25);

?>