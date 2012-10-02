<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_matches.inc";

// Titre  du navigateur
$matches    = "Ergebnisse";

// Titres des fenetres
$tMatches      = "Match Verwaltung";
$tNewMatch     = "Match Erstellen";
$tEditMatch    = "Match bearbeiten";
$tStatusMatch  = "Match verwalten";

// titre des blocs
$blkScore = "Ergebnis des Gewinners";

// Titre des colonnes
$rowsMatchs1  = "Spiel Nr.";
$rowsMatchs2  = "Status";
$rowsMatchs3  = "Ergebnis";
$rowsMatchs4  = "Mannschaft A";
$rowsMatchs5  = "Kommentare";
$rowsMatchs6  = "Mannschaft B";

// Label des zones
$mtchUmpire   = "Schiri :";
$mtchService  = "Aufschlagrichter :";
$mtchCourt    = "Spielfeld :";
$mtchDraw     = "Disziplin :";
$mtchRound    = "Phase :";
$mtchStep     = "Runde :";


$mtchRegiId00   = "Spieler :";
$mtchRegiId01   = "&nbsp;";
$mtchRegiId10   = "Gegen";
$mtchRegiId11   = "&nbsp;";
$blkSet0       = "1.Satz";
$blkSet1       = "2.Satz";
$blkSet2       = "3.Satz";
$blkSet3       = "4.Satz";
$blkSet4       = "5.Satz";
$mtchWo         = "W-O";
$mtchAbort      = "Aufgabe";
$mtchDiscipline = "Disziplin";
$mtchOrder      = "N° :";
$pairIdUn      = "Mannschaft A";
$pairIdDeux    = "Mannschaft B";

/* Messages d'erreur */
$msgmtchRegiId00 = "Wählen Sie die Spieler aus !";
$msgmtchRegiId01 = $msgmtchRegiId00;
$msgmtchRegiId10 = $msgmtchRegiId00;
$msgmtchRegiId11 = $msgmtchRegiId00;
$msgWinNotWO      = "Der Sieger kann kein W-O sein !";
$msgNotSamePlayer = "Eine Paarung muss aus 2 unterschiedlichen Spielern bestehen.";

// Label des boutons
$btnAddPlayer0  = "Spieler hinzufügen";
$btnAddPlayer1  = $btnAddPlayer0;
$btnPlay  = "Spiel beginnen";
$btnStop  = "Spiel stoppen";
$btnRaz = "Ergebnis annulieren";

?>
