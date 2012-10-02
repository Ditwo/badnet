<?php
include_once "Badnet/Badnet.inc";

$indice = BADNET_ADM;
define('BADM_PAGE_CHOICE',           $indice++);
define('BADM_PAGE_RANKING',          $indice++);
define('BADM_UPDATE_RANKING',        $indice++);
define('BADM_FILL_RANKING',          $indice++);
define('BADM_RAZ_RANKING',           $indice++);

define('BADM_PAGE_PAIRS',            $indice++);
define('BADM_FILL_PAIRS',            $indice++);
define('BADM_PAGE_PAIR',             $indice++);
define('BADM_FILL_REGIPAIRS',        $indice++);
define('BADM_CONFIRM_DELETE_PAIR',   $indice++);
define('BADM_DELETE_PAIR',           $indice++);
define('BADM_PAGE_WO',               $indice++);
define('BADM_FILL_WO',               $indice++);
define('BADM_UPDATE_WO',             $indice++);
define('BADM_UPDATE_DISCIPLINE',     $indice++);
define('BADM_UPDATE_I2P',            $indice++);
define('BADM_PAGE_MATCHS',           $indice++);
define('BADM_FILL_MATCHS',           $indice++);
define('BADM_PAGE_MATCH',            $indice++);
define('BADM_PAGE_PLAYERS',          $indice++);
define('BADM_FILL_PLAYERS',          $indice++);
define('BADM_PAGE_PLAYER',           $indice++);
define('BADM_UPDATE_UNIID',          $indice++);
define('BADM_PAGE_REGISTRATIONS',    $indice++);
define('BADM_FILL_REGISTRATIONS',    $indice++);
define('BADM_PAGE_REGISTRATION',     $indice++);
define('BADM_UPDATE_REGISTRATION',   $indice++);
define('BADM_FILL_MEMBERS',          $indice++);

define('BADM_PAGE_TEAMS',          $indice++);
define('BADM_FILL_TEAMS',          $indice++);
define('BADM_PAGE_TEAM',           $indice++);
define('BADM_UPDATE_TEAM',         $indice++);
define('BADM_FILL_MATCHPAIRS',     $indice++);
define('BADM_CONFIRM_DELETE_P2M',  $indice++);
define('BADM_DELETE_P2M',          $indice++);
?>