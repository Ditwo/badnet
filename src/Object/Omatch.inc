<?php
/************************************
 !   $Id$
 ************************************/
// Disscipline
define("OMATCH_DISCI_MS",      1);
define("OMATCH_DISCI_LS",      2);
define("OMATCH_DISCI_WS",      2);
define("OMATCH_DISCI_MD",      3);
define("OMATCH_DISCI_LD",      4);
define("OMATCH_DISCI_WD",      4);
define("OMATCH_DISCI_MX",      5);
define("OMATCH_DISCI_XD",      5);
define("OMATCH_DISCI_AS",      6);
define("OMATCH_DISCI_AD",      7);

// Discipline
define("OMATCH_DISCIPLINE_SINGLE",  110);
define("OMATCH_DISCIPLINE_DOUBLE",  111);
define("OMATCH_DISCIPLINE_MIXED",   112);

// Resultat d'un match
define("OMATCH_RESULT_NOPLAY",    80);
define("OMATCH_RESULT_WIN",       81);
define("OMATCH_RESULT_WINAB",     82);
define("OMATCH_RESULT_WINWO",     83);
define("OMATCH_RESULT_LOOSE",     84);
define("OMATCH_RESULT_LOOSEAB",   85);
define("OMATCH_RESULT_LOOSEWO",   86);

// Status d'un match
define("OMATCH_STATUS_INCOMPLET", 30);
define("OMATCH_STATUS_BUSY",    31);// Joueur indisponible 
define("OMATCH_STATUS_REST",    32);// Temps de repos 
define("OMATCH_STATUS_READY",   33);// Pret a jouer
define("OMATCH_STATUS_LIVE",    34);// En cours
define("OMATCH_STATUS_ENDED",   35);// Termine
define("OMATCH_STATUS_CLOSED",  36);// Valide
define("OMATCH_STATUS_SEND",    37);// Transmis(ffba)

// Tour d'un match
define("OMATCH_STAGE_WINNER",    260);
define("OMATCH_STAGE_FINAL",     261);
define("OMATCH_STAGE_SEMI",      262);
define("OMATCH_STAGE_QUATER",    263);
define("OMATCH_STAGE_HEIGHT",    264);
define("OMATCH_STAGE_16",        265);
define("OMATCH_STAGE_32",        266);
define("OMATCH_STAGE_64",        267);
define("OMATCH_STAGE_ROUND1",    268);
define("OMATCH_STAGE_ROUND2",    269);
define("OMATCH_STAGE_ROUND3",    270);

// Type de tableau
define("OMATCH_DRAW_GROUP",   10); // Individuel Poule + Ko eventuel
define("OMATCH_DRAW_QUALIF",  11); // Individuel Qualif + Ko
define("OMATCH_DRAW_KO",      12); // Individuel Ko seul
define("OMATCH_DRAW_SWISS",   13); // Individuel Ronde suisse
define("OMATCH_DRAW_TEAM_GROUP", 15); // Equipe poule
define("OMATCH_DRAW_TEAM_BACK",  16); // Equipe poule aller/retour
define("OMATCH_DRAW_TEAM_KO",    17); // Equipe Ko
define("OMATCH_DRAW_CONSOL",     18); // Individuel consolante
define("OMATCH_DRAW_IC",         19); // Individuel ronde suisse

// Type de tour
define("OMATCH_ROUND_GROUP",   280); // Poule
define("OMATCH_ROUND_QUALIF",  281); // Qualification
define("OMATCH_ROUND_MAINDRAW",282); // Ko tableau final
define("OMATCH_ROUND_THIRD",   283); // Troisieme place
define("OMATCH_ROUND_CONSOL",  284); // Consolante
define("OMATCH_ROUND_SWISS",   285); // Ronde suisse (un seul tour)
define("OMATCH_ROUND_IC",      286); // Interclub


// Position d'une equipe dans une rencontre
define("OMATCH_TEAM_RECEIVER", 130);
define("OMATCH_TEAM_VISITOR",  131);
define("OMATCH_TEAM_TOP",      130);
define("OMATCH_TEAM_BOTTOM",   131);

// Position d'une paire dans un match
define("OMATCH_PAIR_TOP",      130);
define("OMATCH_PAIR_BOTTOM",   131);






