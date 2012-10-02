<?php
/*****************************************************************************
!   Module     : Utilitaires
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/const.inc,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.30 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/05/20 22:20:08 $
******************************************************************************/

include_once dirname(__FILE__)."/../kpage/kpage.inc";

/**************************************
*  These are the constants for globals
*  actions and module actions
***************************************/
define("DATE_FMT", 'Y-m-d H:i:s');
define("DATE_DATE", 'd-m-y');
define("DATE_TIME", 'H:i');
define("DATE_DATETIME", 'd-m-Y H:i');

define("WBS_NEWLANG",           KID_USER-1);
define("WBS_CNX_DIST",          KID_USER-2);

define("WBS_CNX",           KID_USER);
define("WBS_SECTOR_SPORT",  KID_USER + 1);
define("WBS_SECTOR_ADMIN",  KID_USER + 2);

define("WBS_INST",           KID_USER+50);

define("WBS_ACT_SELECT_EVENT",  KID_USER+100);
define("WBS_ACT_EVENTS",        WBS_ACT_SELECT_EVENT+1);

define("WBS_ACT_SELECT_BOOK",   KID_USER+200);
define("WBS_ACT_BOOKS",         WBS_ACT_SELECT_BOOK+1);

define("WBS_ACT_PREFERENCES",   KID_USER+300);
define("WBS_ACT_PARAMETERS",    KID_USER+350);
define("WBS_ACT_USERS",         KID_USER+400);
define("WBS_ACT_TEAMS",         KID_USER+450);
define("WBS_ACT_TIES",          KID_USER+500);
define("WBS_ACT_DIVS",          KID_USER+550);
define("WBS_ACT_REGI",          KID_USER+600);
define("WBS_ACT_PAIRS",         KID_USER+650);
define("WBS_ACT_MBER",          KID_USER+700);
define("WBS_ACT_DRAWS",         KID_USER+750);
define("WBS_ACT_MATCHS",        KID_USER+800);
define("WBS_ACT_DBF",           KID_USER+850);
define("WBS_ACT_MAINT",         KID_USER+900);
define("WBS_ACT_EMAIL",         KID_USER+950);
define("WBS_ACT_SCHEDU",        KID_USER+1000);
define("WBS_ACT_RESU",          KID_USER+1050);
define("WBS_ACT_SUBS",          KID_USER+1100);
define("WBS_ACT_LIVE",          KID_USER+1150);
define("WBS_ACT_ITEMS",         KID_USER+1200);
define("WBS_ACT_PURCHASE",      KID_USER+1250);
define("WBS_ACT_ASSOC",         KID_USER+1300);
define("WBS_ACT_ACCOUNT",       KID_USER+1350);
define("WBS_ACT_OFFOS",         KID_USER+1400);
define("WBS_ACT_HELP",          KID_USER+1450);
define("WBS_ACT_TODAY",         KID_USER+1500);
define("WBS_ACT_STREAM",        KID_USER+1550);
define("WBS_ACT_PHPINFO",       KID_USER+1600);
define("WBS_ACT_EXPORT",        KID_USER+1650);
define("WBS_ACT_IMPORT",        KID_USER+1700);
define("WBS_ACT_TRANSPORT",     KID_USER+1750);
define("WBS_ACT_ACCOMODATION",  KID_USER+1800);
define("WBS_ACT_BADGES",        KID_USER+1850);
define("WBS_ACT_LINE",          KID_USER+1900);

define("WBS_ACT_PRINT",        KID_USER+2000);
define("WBS_ACT_EDIT",         KID_USER+2001);
define("WBS_ACT_DROP",         KID_USER+2002);

/**************************************
*  These are the constants for database 
*  informations
***************************************/
define("WBS_THEME_NONE",   0);
define("WBS_THEME_BOOK",   1);
define("WBS_THEME_EVENT",  2);
define("WBS_THEME_ASSOS",  3);
define("WBS_THEME_MEMBER", 4);

define("WBS_ACCOUNT_OPEN",  0);
define("WBS_ACCOUNT_CLOSE", 1);

define("WBS_DATA_UNDELETE",  0);
define("WBS_DATA_DELETE",    1);
define("WBS_DATA_PUBLIC",    2);
define("WBS_DATA_PRIVATE",   4);
define("WBS_DATA_CONFIDENT", 8);
define("WBS_DATA_ARCHIVED", 16);

define("WBS_AUTH_ADMIN",       'A');
define("WBS_AUTH_MANAGER",     'M');
define("WBS_AUTH_USER",        'U');

define("WBS_AUTH_VISITOR",     'V');
define("WBS_AUTH_GUEST",       'G');
define("WBS_AUTH_FRIEND",      'F');
define("WBS_AUTH_ASSISTANT",   'S');
define("WBS_AUTH_REFEREE",     'R');
define("WBS_AUTH_CAPTAIN",     'T');

define("WBS_IMG_LOGO",   0);
define("WBS_IMG_ICON",   1);
define("WBS_IMG_FONT",   2);
define("WBS_IMG_PHOTO",  3);
define("WBS_IMG_POSTER", 4);

define("WBS_MS",      1);
define("WBS_LS",      2);
define("WBS_WS",      2);
define("WBS_MD",      3);
define("WBS_LD",      4);
define("WBS_WD",      4);
define("WBS_MX",      5);
define("WBS_XD",      5);
define("WBS_AS",      6);
define("WBS_AD",      7);

define("WBS_MALE",      6);
define("WBS_FEMALE",    7);

define("WBS_GROUP",   10);
define("WBS_QUALIF",  11);
define("WBS_KO",      12);
define("WBS_SWISS",   13);
define("WBS_TEAM_GROUP", 15);
define("WBS_TEAM_BACK",  16);
define("WBS_TEAM_KO",    17);
define("WBS_CONSOL",     18);
define("WBS_TEAM_PLATEAU",    285);

define("WBS_UMPIRE_OFFICIAL",   20);
define("WBS_UMPIRE_OFFSERVICE", 21);
define("WBS_UMPIRE_AUTO",       22);
define("WBS_UMPIRE_WINNER",     23);
define("WBS_UMPIRE_LOOSER",     24);

// Match status
define("WBS_MATCH_INCOMPLETE", 30);
define("WBS_MATCH_BUSY",    31);// Joueur indisponible 
define("WBS_MATCH_REST",    32);// Temps de repos 
define("WBS_MATCH_READY",   33);// Pret a jouer
define("WBS_MATCH_LIVE",    34);// En cours
define("WBS_MATCH_ENDED",   35);// Termine
define("WBS_MATCH_CLOSED",  36);// Valide
define("WBS_MATCH_SEND",    37);// Transmis(ffba)

// Contact type
define("WBS_EMAIL",    40);
define("WBS_FAX",      41);
define("WBS_MOBILE",   42);
define("WBS_PHONE",    43);

// Association type
define("WBS_FEDE",    50);
define("WBS_UMASSO",  51);
define("WBS_LIGUE",   52);
define("WBS_CODEP",   53);
define("WBS_CLUB",    54);
define("WBS_ECOLE",   55);
define("WBS_OTHER",   56);

// Registration type
define("WBS_PLAYER",  60);
define("WBS_REFEREE",  61);
define("WBS_DEPUTY",  62);
define("WBS_UMPIRE",  63);
define("WBS_LINEJUDGE", 64);
define("WBS_COACH",   65);
define("WBS_CONSEILLER",   66);
define("WBS_DELEGUE",   67);

define("WBS_VOLUNTEER",    170);
define("WBS_ORGANISATION", 171);
define("WBS_VIP",       172);
define("WBS_PRESS",     173);
define("WBS_GUEST",     174);
define("WBS_MEDICAL",   175);
define("WBS_EXHIBITOR", 176);
define("WBS_PLATEAU",   177);
define("WBS_SECURITE",  178);
define("WBS_OTHERB",     179);


// Event type
define("WBS_EVENT_INDIVIDUAL",  70);
define("WBS_EVENT_TEAM",        71);
define("WBS_EVENT_IC",          71); /* alias de WBS_EVENT_TEAM */

// Event nature (for compatibility with old ffba export)
define("WBS_NATURE_TOURN",   70); // Tournoi prive individuel
define("WBS_NATURE_EQUIP",   71); // Tournoi prive par equipe
define("WBS_NATURE_INDIV",   72); // Championnat
define("WBS_NATURE_TROPH",   73); // Trophee
define("WBS_NATURE_INTERCODEP",   74); // Intercodep
define("WBS_NATURE_OTHER",   79); // Autre

// Event level
define("WBS_LEVEL_DEP",  430); // Old 76
define("WBS_LEVEL_REG",  431); // Old 77
define("WBS_LEVEL_NAT",  432); // Old 78
define("WBS_LEVEL_INT",  433); // old 79
define("WBS_LEVEL_INTER_REGION",  434);
define("WBS_LEVEL_INTER_DEPT",    435);
define("WBS_LEVEL_CLUB",          436);
define("WBS_LEVEL_INTER_OTHER",   439);

// Match result for a pair
define("WBS_RES_NOPLAY",    80);
define("WBS_RES_WIN",       81);
define("WBS_RES_WINAB",     82);
define("WBS_RES_WINWO",     83);
define("WBS_RES_LOOSE",     84);
define("WBS_RES_LOOSEAB",   85);
define("WBS_RES_LOOSEWO",   86);

// Pair status
define("WBS_PAIR_DNS",        89);
define("WBS_PAIR_MAINDRAW",   90);
define("WBS_PAIR_QUALIF",     91);
define("WBS_PAIR_RESERVE",    92);
define("WBS_PAIR_PTQ",        93);
define("WBS_PAIR_PFR",        94);
define("WBS_PAIR_PTM",        95);
define("WBS_PAIR_PFQ",        96);
define("WBS_PAIR_WDN",        97);
define("WBS_PAIR_INJ",        98);
define("WBS_PAIR_NONE",       99);

// Tie result for a team
define("WBS_TIE_WIN",     100);
define("WBS_TIE_LOOSE",   101);
define("WBS_TIE_EQUAL",   102);
define("WBS_TIE_NOTPLAY", 103);
define("WBS_TIE_LOOSEWO", 104);
define("WBS_TIE_WINWO",   105);
define("WBS_TIE_STEP",    106);
define("WBS_TIE_EQUAL_MINUS",  107);
define("WBS_TIE_EQUAL_PLUS",    108);

// Discipline
define("WBS_SINGLE",  110);
define("WBS_DOUBLE",  111);
define("WBS_MIXED",   112);

// Subscription
define("WBS_SUBS_MATCH",  120);
define("WBS_SUBS_PLAYER", 121);
define("WBS_SUBS_TEAM",   122);
define("WBS_SUBS_NEWS",   123);
define("WBS_SUBS_DEL",    129);

// Position of a team in a tie
define("WBS_TEAM_RECEIVER", 130);
define("WBS_TEAM_VISITOR",  131);
define("WBS_TEAM_TOP",      130);
define("WBS_TEAM_BOTTOM",   131);
// Position of a pair in a match
define("WBS_PAIR_TOP",      130);
define("WBS_PAIR_BOTTOM",   131);

// Comment departager deux equipes a egalites
define("WBS_CALC_RANK",   140); // Classement
define("WBS_CALC_RESULT", 141); // Resultat du match entre les deux
define("WBS_CALC_EQUAL",  142); // Egalité autorisee

// Registration status
define("WBS_REGI_TITULAIRE",  150);
define("WBS_REGI_REMPLACANT", 151);
define("WBS_REGI_AUTRE",      152);

// Registration status
define("WBS_YES",  160);
define("WBS_NO",   161);
define("WBS_WARMUP", 162);
define("WBS_CALL",   163);
define("WBS_UNKNOW", 164);

// Paiement
define("WBS_PAYED_NONE",    180);
define("WBS_PAYED_CREDIT",  181);
define("WBS_PAYED_MONEY",   182);
define("WBS_PAYED_CHECK",   183);
define("WBS_PAYED_CB",      184);

// Badge elements
define("WBS_FIELD_TITLE",    190);
define("WBS_FIELD_NAME",     191);
define("WBS_FIELD_TEAMNAME", 192);
define("WBS_FIELD_STATUS",   193);
define("WBS_FIELD_LOGO",     194);
define("WBS_FIELD_PHOTO",    195);
define("WBS_FIELD_SPONSOR",  196);
define("WBS_FIELD_BAND",     197);
define("WBS_FIELD_BARCODE",  198);
define("WBS_FIELD_TEAMLOGO", 199);
define("WBS_FIELD_NOC",      200);
define("WBS_FIELD_FIXED",    201);
define("WBS_FIELD_FIRSTNAME",  202);
define("WBS_FIELD_SECONDNAME", 203);
define("WBS_FIELD_TEAMPSEUDO", 204);
define("WBS_FIELD_ZONE", 205);

// Tete de serie
define("WBS_TDS_1",    220);
define("WBS_TDS_2",    221);
define("WBS_TDS_3",    222);
define("WBS_TDS_4",    223);
define("WBS_TDS_5",    224);
define("WBS_TDS_6",    225);
define("WBS_TDS_7",    226);
define("WBS_TDS_8",    227);
define("WBS_TDS_9",    228);
define("WBS_TDS_10",   229);
define("WBS_TDS_11",   230);
define("WBS_TDS_12",   231);
define("WBS_TDS_13",   232);
define("WBS_TDS_14",   233);
define("WBS_TDS_15",   234);
define("WBS_TDS_16",   235);
define("WBS_TDS_3_4",  236);
define("WBS_TDS_5_8",  237);
define("WBS_TDS_9_16", 238);
define("WBS_TDS_Q1", 239);
define("WBS_TDS_Q2", 240);
define("WBS_TDS_Q3", 241);
define("WBS_TDS_Q4", 242);
define("WBS_TDS_Q5", 243);
define("WBS_TDS_Q6", 244);
define("WBS_TDS_Q7", 245);
define("WBS_TDS_Q8", 246);
define("WBS_TDS_Q9", 247);
define("WBS_TDS_NONE", 249);

define("WBS_WINNER",    260);
define("WBS_FINAL",     261);
define("WBS_SEMI",      262);
define("WBS_QUATER",    263);
define("WBS_HEIGHT",    264);
define("WBS_16",    265);
define("WBS_32",    266);
define("WBS_64",    267);
define("WBS_ROUND1",    268);
define("WBS_ROUND2",    269);
define("WBS_ROUND3",    270);

define("WBS_ROUND_GROUP",   280);
define("WBS_ROUND_QUALIF",  281);
define("WBS_ROUND_MAINDRAW",282);
define("WBS_ROUND_THIRD",   283);
define("WBS_ROUND_CONSOL",  284);
define("WBS_ROUND_PLATEAU", 285);

define("WBS_UMPIRE_UMPIRE",  290);
define("WBS_UMPIRE_SERVICE", 291);
define("WBS_UMPIRE_REST",    292);

define("WBS_CATAGE_POU",  301);
define("WBS_CATAGE_BEN",  302);
define("WBS_CATAGE_MIN",  303);
define("WBS_CATAGE_CAD",  304);
define("WBS_CATAGE_JUN",  305);
define("WBS_CATAGE_SEN",  306);
define("WBS_CATAGE_VET",  307);

define("WBS_RANK_FR1",    320);
define("WBS_RANK_FR2",    321);
define("WBS_RANK_LU",     322);
define("WBS_RANK_SQUASH", 323);
define("WBS_RANK_SQUASH_YOUNG", 324);

define("WBS_SCORING_NONE",  330);
define("WBS_SCORING_3X15",  331);
define("WBS_SCORING_5X7",   332);
define("WBS_SCORING_3X21",  333);
define("WBS_SCORING_1X5",   334);
define("WBS_SCORING_3X11",  335);
define("WBS_SCORING_5X11",  336);


define("WBS_RUBRIK_OTHERS", -1);
define("WBS_RUBRIK_FEES",   -2);
define("WBS_RUBRIK_HOTEL",  -3);

define("WBS_ROOM_SINGLE",  340);
define("WBS_ROOM_TWIN",    341);
define("WBS_ROOM_TRIPLEX", 342);
define("WBS_ROOM_OTHER",   343);
define("WBS_PRICE_WEEK",   345);
define("WBS_PRICE_WE",     346);

define("WBS_SURCLASSE_NONE",   350);
define("WBS_SURCLASSE_SIMPLE", 351);
define("WBS_SURCLASSE_DOUBLE", 352);
define("WBS_SURCLASSE_VA",     353);
define("WBS_SURCLASSE_SE",     354);
define("WBS_SURCLASSE_SP",     355);

define("WBS_CONVOC_MATCH",   360);
define("WBS_CONVOC_DRAW",    361);

// Pair state
define("WBS_PAIRSTATE_NOK",  370); // Paire incomplete
define("WBS_PAIRSTATE_REG",  371); // Paire complete inscrite
define("WBS_PAIRSTATE_COM",  372); // Paire complete composee

// Postit type
define("WBS_POSTIT_NONE",     380); // Pas de type
define("WBS_POSTIT_TOODRAW",  381); // Trop de tableau
define("WBS_POSTIT_TEAM",     382); // Associe a une equipe
define("WBS_POSTIT_REGI",     383); // Associe a une inscription
define("WBS_POSTIT_LINE",     384); // Associe a une inscription en ligne

// Etat des tournois
define("WBS_EVENT_STATUS_ASK",      390);
define("WBS_EVENT_STATUS_OK",       391);
define("WBS_EVENT_STATUS_ARCHIVE",  392);
define("WBS_EVENT_STATUS_NOK",      393);

// Niveau des messages
define("WBS_MSG_INFO",  400);
define("WBS_MSG_WNG",   401);
define("WBS_MSG_ERR",   402);
define("WBS_MSG_FATAL", 403);

// Catage du tournoi
define("WBS_EVENT_CATAGE_SENIOR",  440);
define("WBS_EVENT_CATAGE_YOUNG",   441);
define("WBS_EVENT_CATAGE_BOTH",    442);


?>
