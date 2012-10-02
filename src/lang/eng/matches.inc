<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_matches.inc";

// Titre  du navigateur
$matches    = "Results";

// Titres des fenetres
$tMatches      = "Matches management";
$tNewMatch     = "New match";
$tEditMatch    = "Edit match";
$tStatusMatch  = "Match management";

// titre des blocs
$blkScore = "Winner score";

// Titre des colonnes
$rowsMatchs1  = "Number";
$rowsMatchs2  = "Status";
$rowsMatchs3  = "Scores";
$rowsMatchs4  = "Team A";
$rowsMatchs5  = "Comments";
$rowsMatchs6  = "Team B";

// Label des zones
$mtchUmpire   = "Umpire :";
$mtchService  = "Service judge :";
$mtchCourt    = "Court :";
$mtchDraw     = "Draw :";
$mtchRound    = "Round :";
$mtchStep    = "Step :";

$mtchRegiId00   = "Player(s) :";
$mtchRegiId01   = "&nbsp;";
$mtchRegiId10   = "Versus";
$mtchRegiId11   = "&nbsp;";
$mtchSc00       = "Game 1 :";
$mtchSc01       = "Game 2 :";
$mtchSc02       = "Game 3 :";
$mtchSc10       = "";
$mtchSc11       = "";
$mtchSc12       = "";
$mtchWo         = "WO";
$mtchAbort      = "Abandon";
$mtchDiscipline = "Discipline";
$mtchOrder      = "N° :";
$pairIdUn      = "Team A";
$pairIdDeux    = "Team B";

/* Messages d'erreur */
$msgmtchRegiId00 = "Select a player !";
$msgmtchRegiId01 = "Select a player !";
$msgmtchRegiId10 = "Select a player !";
$msgmtchRegiId11 = "Select a player !";
$msgWinNotWO      = "Winner can't be WO !";
$msgNotSamePlayer = "A pair must have two different players";

// Label des boutons
$btnAddPlayer  = "Add player";
$btnPlay  = "Start match";
$btnStop  = "End match";
$btnAbort = "Abort result";

?>
