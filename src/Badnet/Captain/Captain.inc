<?php
include_once "Badnet/Badnet.inc";

$indice = BADNET_CAPTAIN;
define('BCAPTAIN_PAGE_CAPTAIN',       $indice++);

define('BCAPTAIN_PAGE_PLAYERS',   $indice++);
define('BCAPTAIN_FILL_PLAYERS',   $indice++);

define('BCAPTAIN_PAGE_REGIS',     $indice++);
define('BCAPTAIN_FILL_REGIS',     $indice++);
define('BCAPTAIN_FILL_TIES',      $indice++);

define('BCAPTAIN_PAGE_ADD_PLAYER', $indice++);
define('BCAPTAIN_ADD_PLAYER',      $indice++);
define('BCAPTAIN_PAGE_NEW_PLAYER', $indice++);
define('BCAPTAIN_NEW_PLAYER',      $indice++);
define('BCAPTAIN_PAGE_PLAYER',     $indice++); 
define('BCAPTAIN_SEARCH_PLAYER',   $indice++);

define('BCAPTAIN_PAGE_TIE',      $indice++);
define('BCAPTAIN_UPDATE_TIE',    $indice++);
define('BCAPTAIN_SEND_TIE',      $indice++);
define('BCAPTAIN_CONFIRM_TIE',   $indice++);
define('BCAPTAIN_PAGE_RESULT',   $indice++);

define('BCAPTAIN_PAGE_RESULTS',   $indice++);
define('BCAPTAIN_PAGE_EMAIL',     $indice++);
define('BCAPTAIN_SEND_EMAIL',     $indice++);
define('BCAPTAIN_EMAIL_SENDED',   $indice++);

define('BCAPTAIN_PAGE_VCARD',   $indice++);
define('BCAPTAIN_PAGE_GROUP',   $indice++);

define('BCAPTAIN_PAGE_EMAIL_MANAGER',     $indice++);
define('BCAPTAIN_SEND_EMAIL_MANAGER',     $indice++);

define('BCAPTAIN_PDF_VCARD',            $indice++);
define('BCAPTAIN_PDF_CALENDAR',         $indice++);

define('BCAPTAIN_CONFIRM_DEL_PLAYER',   $indice++);
define('BCAPTAIN_DEL_PLAYER',           $indice++);

define('BCAPTAIN_PAGE_EMAIL_PLAYER',     $indice++);
define('BCAPTAIN_SEND_EMAIL_PLAYER',     $indice++);
define('BCAPTAIN_PAGE_EMAIL_PLAYERS',    $indice++);
define('BCAPTAIN_SEND_EMAIL_PLAYERS',    $indice++);

?>