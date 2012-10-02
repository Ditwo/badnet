<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
define('THEME_NONE',   0);
define('THEME_BOOK',   1);
define('THEME_EVENT',  2);
define('THEME_ASSOS',  3);
define('THEME_MEMBER', 4);

// Type de compte
define('AUTH_ADMIN',       'A'); // Administrateur du site
define('AUTH_MANAGER',     'M'); // Manager (responsable fede squash)
define('AUTH_USER',        'U'); // Utilisateur simple
define('AUTH_PLAYER',      'P'); // joueur
define("AUTH_REFEREE",     'R'); // Compte JA
define('AUTH_ASSO',        'S'); // Compte association 
define('AUTH_PRIVATE',     'I'); // Compte structure privee
define('AUTH_CLUB',        'C'); // Compte structure privee
define("AUTH_VISITOR",     'V'); // Compte visiteur

// Droit sur les competition
define("AUTH_GUEST",       'G');
define("AUTH_FRIEND",      'F');
define("AUTH_ASSISTANT",   'S');
define("AUTH_CAPTAIN",     'T');
define("AUTH_ORGANIZER",   'O');

define("DATA_UNDELETE",  0);
define("DATA_DELETE",    1);
define("DATA_PUBLIC",    2);
define("DATA_PRIVATE",   4);
define("DATA_CONFIDENT", 8);
define("DATA_ARCHIVED", 16);

define('YES',       160);
define('NO',        161);

?>
