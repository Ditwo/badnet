<?php
/*****************************************************************************
!   $Id$
******************************************************************************/
define('BNETADM_LOGIN',       PROJECT_BADNETADM |0x000000);
define('BNETADM_USERS',       PROJECT_BADNETADM |0x000100);
define('BNETADM_INLINE',      PROJECT_BADNETADM |0x000200);
define('BNETADM_PLAYERS',     PROJECT_BADNETADM |0x000300);
define('BNETADM_ASSOCS',      PROJECT_BADNETADM |0x000400);
define('BNETADM_MY_ACCOUNT',  PROJECT_BADNETADM |0x000500);
define('BNETADM_EVENTS',      PROJECT_BADNETADM |0x000600);
define('BNETADM_PARAMS',      PROJECT_BADNETADM |0x000700);
define('BNETADM_STATS',       PROJECT_BADNETADM |0x000800);
define('BNETADM_END',         PROJECT_BADNETADM |0x00FF00);

define('BNETADM_LOG',          BNETADM_END |0x000001);
define('BNETADM_LOGOUT',       BNETADM_END |0x000002);
define('BNETADM_TO_LOGIN',     BNETADM_END |0x000003);
define('BNETADM_BEFORE_LOGIN', BNETADM_END |0x000004);
define('BNETADM_DISPATCH',     BNETADM_END |0x000005);
?>
