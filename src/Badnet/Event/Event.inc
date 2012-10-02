<?php
include_once "Badnet/Badnet.inc";

$indice = BADNET_EVENT;
define('BEVENT_PAGE_EVENTS',        $indice++); // Ne pas changer

define('BEVENT_DISPATCH',           $indice++);
define('BEVENT_PAGE_MANAGER_EVENT', $indice++);
define('BEVENT_PAGE_MANAGER_IC',    $indice++);
define('BEVENT_PAGE_REFEREE',       $indice++);
define('BEVENT_PAGE_VISITOR',       $indice++);

define('BEVENT_FILL_EVENTS',        $indice++);
define('BEVENT_PAGE_DELETE_EVENT',  $indice++);
define('BEVENT_DELETE_EVENT',       $indice++);
define('BEVENT_PAGE_NEW_EVENT',     $indice++);
define('BEVENT_CREATE_EVENT',       $indice++);
define('BEVENT_FILL_NATURE',        $indice++);

define('BEVENT_EMPTY_CACHE',        $indice++);
define('BEVENT_PUBLI_EVENT',        $indice++);
define('BEVENT_FILL_POSTITS',       $indice++);
define('BEVENT_PAGE_DELETE_POSTIT', $indice++);
define('BEVENT_DELETE_POSTIT',     $indice++);
define('BEVENT_PAGE_POSTIT',       $indice++);
define('BEVENT_UPDATE_POSTIT',     $indice++);


define('BEVENT_GOTO_DIVISIONS', $indice++);
define('BEVENT_GOTO_TEAMS',     $indice++);
define('BEVENT_GOTO_PLAYERS',   $indice++);
define('BEVENT_GOTO_TIES',      $indice++);
define('BEVENT_GOTO_CALENDAR',  $indice++);
define('BEVENT_GOTO_DRAWS',     $indice++);
define('BEVENT_GOTO_LIVE',      $indice++);
define('BEVENT_GOTO_VISIT',     $indice++);

?>
