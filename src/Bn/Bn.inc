<?php
/*****************************************************************************
!   $Id$
******************************************************************************/

define ('BN_BYPASSLOGIN',   0xFF00);
define ('BN_LOGIN',       0x0001);
define ('BN_LOGOUT',      BN_BYPASSLOGIN | 0x0001);
define ('BN_ASKPWD',      BN_BYPASSLOGIN | 0x0002);

define ('BN_LEVEL_CRITICAL',  0x0001);
define ('BN_LEVEL_ERROR',     BN_LEVEL_CRITICAL << 1);
define ('BN_LEVEL_WARNING',   BN_LEVEL_CRITICAL << 2);
define ('BN_LEVEL_TRACE',     BN_LEVEL_CRITICAL << 3);

?>