<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$ties      = "Begegnungen";

// Titres des fenetres
$tieTitle = 'Begegnung :';
$tTieResult = 'Ergebnis der Begegnung';
$tDetail    = 'Details';

// Menus 

// Titre des colonnes
$titleDraw0   = "Erste Runde";
$titleDraw1   = "Zweite Runde";
$titleDraw2   = "Dritte Runde";
$drawTitle1   = "Sieger";
$drawTitle2   = "Finale";
$drawTitle3   = "Halbfinale";
$drawTitle4   = "Viertelfinale";

$rowsMatchs1  = "Spiel";
$rowsMatchs4  = "Tendenz";
$rowsMatchs5  = "Ergebnis<br />A";
$rowsMatchs6  = "Ergebnis<br />B";
$rowsMatchs7  = "Satz<br />A";
$rowsMatchs8  = "Satz<br />B";
$rowsMatchs9  = "Pkt<br />A";
$rowsMatchs10  = "Pkt<br />B";


$rowsRanking1 = "Mannschaft";
$rowsRanking2 = "Abkürzung";
$rowsRanking3 = "Pkt";
$rowsRanking4 = "P";
$rowsRanking5 = "V";
$rowsRanking6 = "E";
$rowsRanking7 = "D";
$rowsRanking8 = "Spiele<br />++";
$rowsRanking9 = "Spiele<br />--";
$rowsRanking10 = "Spiele<br />diff";
$rowsRanking11 = "Sätze<br />++";
$rowsRanking12 = "Sätze<br />--";
$rowsRanking13 = "Sätze<br />diff";
$rowsRanking14 = "Pkt<br />++";
$rowsRanking15 = "Pkt<br />--";
$rowsRanking16 = "Pkt<br />diff";

$tiesList  = "Begegnungen";
$tiesList1  = "Mannschaft";

// Label des champs
$tdivsList   = "Ligen";
$divsList   = "Ligen :";
$groupsList = "Gurppen";
$resultsDiv = "Ergebnisse :";
$rankingsDiv = "Rangliste :";
$penaltiesL  = "Bonus/Malus :";
$penaltiesR  = "Bonus/Malus :";
$t2rPenalties = "Bonus(+)/Malus(-) :";

$draw_name  = "Liga :";
$rund_name  = "Gruppe :";
$tie_place  = "Ort der Begegnung :";
$tie_step   = "Runde :";
$tie_schedule  = "Datum :";

$tieToValid    = "Begegnung zu bestätigen :";
$tieToComplete = "Begegnung aufnehmen :";
$tieFinished   = "Begegnungen beendet :";


// Messages d'erreur
$msgNoTeam     = "Keine Mannschaft in dieser Gruppe";
$msgNoDivs     = "Keine Liga definiert";
$msgNoGroups   = "Keine Grupee in dieser Liga";
$msgNoTeams    = "Keine Mannschaft in den Gruppen dieser Liga.";
$msgNotSamePlayer = "Eine Paarung besteht aus zwei unterschiedlichen Spielern";
$msgpenaltiesL    = "Der bonus/Malus kann nicht leer sein.";
$msgpenaltiesR    = $msgpenaltiesL;

$msgResult = "Deux équipes à égalités sont départagées par le résultat de la rencontre les ayant opposées. ties.inc";
$msgRank   = "Bei Punktgleichheit zählt zuerst die Spieldifferenz, dann die Satzdifferenz und letztendlich die Spieldifferenz.";
$msgIncompletedTie = "Begegnung unvollständig; Eintrag unmöglich.";

// Label des boutons
$btnAddPlayerL  = "Spieler hinzufügen";
$btnAddPlayerR  = $btnAddPlayerL;
$btnResult     = "Ergebnis eintragen";
$btnValid      = "Ergebnis bestätigen";
$btnPdf          = "PDF Version";
$btnPdfRanking   = "Ergebnisse PDF";
$btnPdfSchedule  = "Uhrzeiten PDF";
$btnPdfAllTies   = "Begegnungen PDF";
$btnRaz          = "Löschen";

?>