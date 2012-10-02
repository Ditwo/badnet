<?php
/*****************************************************************************
!   $Id$
******************************************************************************/

include_once 'Badnetadm/Badnetadm.inc';
$indice = BNETADM_EVENTS;

define('EVENTS_PAGE_EVENTS',        $indice++);
define('EVENTS_PAGE_MY_EVENTS',        $indice++);
define('EVENTS_FILL_EVENTS',    $indice++);
define('EVENTS_DELETE_EVENT',       $indice++);
define('EVENTS_CONFIRM_DELETE_EVENT', $indice++);
define('EVENTS_PAGE_EDIT_EVENT',    $indice++);
define('EVENTS_UPDATE_EVENT',       $indice++);
?>