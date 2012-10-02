<?php
require_once 'Badnetadm/Badnetadm.inc';
$indice = BNETADM_LOGIN;

// Attention: page appele si erreur login
// Ne pas changer son numero
define('LOGIN_PAGE_BAD_LOGIN',  $indice++);

// Les autres constantes peuvent changer 
define('LOGIN_FILL_PASSWORD',    $indice++);
define('LOGIN_SEND_PASSWORD',    $indice++);
define('LOGIN_SENDED_PASSWORD',  $indice++);
define('LOGIN_PAGE_ACCOUNT',     $indice++);
define('LOGIN_ASK_ACCOUNT',      $indice++);
define('LOGIN_PAGE_ACCEPT',      $indice++);
define('LOGIN_CONFIRM_ACCOUNT',  $indice++);
define('LOGIN_REQUEST',          $indice++);

define('LOGIN_LOGIN',            $indice++);
define('LOGIN_SELECT_ACCOUNT',   $indice++);
define('LOGIN_PAGE_ACCOUNT_PLAYER',   $indice++);
?>