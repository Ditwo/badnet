<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
// Event type
define('OEVENT_TYPE_INDIVIDUAL',  70);
define('OEVENT_TYPE_TEAM',        71);
define('OEVENT_TYPE_IC',        71);

// Nature des tournois
define('OEVENT_NATURE_PRIVATE',      70);
define('OEVENT_NATURE_IC',           71);
define('OEVENT_NATURE_TEAM',         71);
define('OEVENT_NATURE_CHAMPIONSHIP', 72);
define('OEVENT_NATURE_TROPHEE',      73);
define('OEVENT_NATURE_INTERCODEP',   74);
define('OEVENT_NATURE_OTHER',        79);

// Event level
define('OEVENT_LEVEL_DEP',  430); // Departemetal
define('OEVENT_LEVEL_REG',  431); // Regional
define('OEVENT_LEVEL_NAT',  432); // National
define('OEVENT_LEVEL_INT',  433);  // International
define('OEVENT_LEVEL_INTER_DEPT', 434); // Inter departemental
define('OEVENT_LEVEL_INTER_REG',  435); // Inter-regional
define('OEVENT_LEVEL_CLUB',       436); // Club (squash)
define('OEVENT_LEVEL_OTHER',      439); // Autre

// Ranking system
define('OEVENT_RANK_FR1',     320);
define('OEVENT_RANK_FR2',     321);
define('OEVENT_RANK_LUX',     322);
define('OEVENT_RANK_SQUASH',  323);
define('OEVENT_RANK_SQUASH_YOUNG',  324);

// Scoring system
define('OEVENT_SCORING_NONE',  330);
define('OEVENT_SCORING_3X15',  331);
define('OEVENT_SCORING_5X7',   332);
define('OEVENT_SCORING_3X21',  333);
define('OEVENT_SCORING_1X5',   334); // Squash : on ne saisie que les resultats des jeux 3-0, 3-1, 3-2
define('OEVENT_SCORING_3X11',  335); // Squash 3 set gagant de 11
define('OEVENT_SCORING_5X11',  336); // Squash 2 set gagant de 11

// Categorie d'age du tournoi
define("OEVENT_CATAGE_SENIOR",  440);
define("OEVENT_CATAGE_ADULT",   440);
define("OEVENT_CATAGE_YOUNG",   441);
define("OEVENT_CATAGE_BOTH",    442);

// Court official
define("OEVENT_OFFICIAL_REFEREE",   61);
define("OEVENT_OFFICIAL_DEPUTY",    62);
define("OEVENT_OFFICIAL_UMPIRE",    63);
define("OEVENT_OFFICIAL_LINEJUDGE", 64);

// Droit sur les tournois
define('OEVENT_RIGHT_MANAGER',   'M');
define('OEVENT_RIGHT_ASSISTANT', 'S');
define('OEVENT_RIGHT_FRIEND',    'F');
define('OEVENT_RIGHT_GUEST',     'G');
define('OEVENT_RIGHT_VISITOR',   'V');
define('OEVENT_RIGHT_REFEREE',   'R');
define('OEVENT_RIGHT_CAPTAIN',   'T');
define('OEVENT_RIGHT_ORGANIZER', 'O');

// Type de convocation
define('OEVENT_CONVOC_MATCH',   360);
define('OEVENT_CONVOC_DRAW',    361);

// Etat des tournois
define("OEVENT_STATUS_ASK",      390);
define("OEVENT_STATUS_OK",       391);
define("OEVENT_STATUS_NOK",      392);


?>
