<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$ties      = "Terminkalender";

$itConvoc = "Aufruf";
$itAnalyse    = "Analyse";
$itConvocPdf  = "Drucken (PDF)";
$itConvocMail = "Per E-Mail senden";
$itPubli      = "Veröffentlichen";

// Titres des fenetres
$fScheduContact= "Aufruf";
$tScheduEdit   = "Spielzeiten bearbeiten";
$tScheduNum    = "Spiel Numerierung";
$calendarDiv   = "Terminkalender";
$itSchedu      = "Zusammenstellung";
$itPdf         = "PDF Dokument";
$itPlayerPdf   = "Spieler/Halle";
$tScheduAuto   = "Automatischer Plan";
$tConfirmation = "Bestätigung";
$tHour         = "Uhrzeit bearbeiten";

// Titre des colonnes
$scheList1  = "Gruppe";
$scheList2  = "Runde";
$scheList3  = "Ort - Uhrzeit";
$scheList4  = "Heim";
$scheList5  = "Gast";
$scheList6  = "Ergebnis";

$tiesPubli1  = "Runde";
$tiesPubli2  = "Datum";
$tiesPubli3  = "Ort";
$tiesPubli4  = "Spielfeld";
$tiesPubli5  = "Pos";
$tiesPubli6  = "Gast";
$tiesPubli7  = "Num";

$tiesSchedu1  = "Runde";
$tiesSchedu2  = "Datum";
$tiesSchedu3  = "Ort";
$tiesSchedu4  = "Spielfeld";
$tiesSchedu5  = "A/R";
$tiesSchedu6  = "Heim";
$tiesSchedu7  = "Gast";
$tiesSchedu8  = "Ergebnis";

$rowsTeams1  = "Pseudonym";
$rowsTeams2  = "Name";
$rowsTeams3  = "Abkürzung";
$rowsTeams4  = "Email";
$rowsTeams5  = "Nachricht";

$rowsPbs1  = "Spieler";
$rowsPbs2  = "Disziplin";
$rowsPbs3  = "Match";
$rowsPbs4  = "Halle";
$rowsPbs5  = "Uhrzeit";
$rowsPbs6  = "Disziplin";
$rowsPbs7  = "Match";
$rowsPbs8  = "Halle";
$rowsPbs9  = "Uhrzeit";
$rowsPbs10  = "Delta";

// Label des champs
$tdivsList   = "Liga";
$divsList   = "Ligen :";
$groupsList = "Gruppen";
$tiesGroup      = "";
$tieStep     = $tiesSchedu1." :";
$tieSchedule = $tiesSchedu2." (tt-mm-jj [hh:mm]) :";
$tiePlace    = $tiesSchedu3." :";
$tieCourt    = $tiesSchedu4." :";
$displayAll  = "Datum des Spiels anzeigen";
$tiesDate  = 'Tag :';
$tiesPlace = 'Halle :';
$firstNum  = 'Erste Nummer :';
$numOption1  = 'Durchnummerieren';
$numOption2  = 'Changer de plage chaque salle schedu.inc';
$numOption3  = 'Changer de plage chaque salle et chaque jour';
$drawList    = "Disziplin:";
$nbCourt     = "Anzahl der Spielfelder : ";
$firstDay    = "Erster Tag (tt-mm-jj) : ";
$startOne    = "Anfangszeit (hh:mm) : ";
$endOne      = "Ende (hh:mm) : ";
$startSecond = "Wiederbeginn (hh:mm) : ";
$venue       = "Halle :";
$newVenue    = "Neue Halle :";
$day         = "Datum :";
$newDay      = "Neues Datum (tt-mm-jj) :";
$scheduList  = "Terminkalender";
$matchList   = "Spiele";
$propagate   = "Die Dauer auf die nächsten Zeitfenster übertragen";
$time        = "Uhrzeit :";
$length      = "Dauer :";
$blkConvoc   = "Aufruf";
$delayConvoc = "Délai convocation (mn):";
$lieuConvoc  = "Anmeldungsort :";
$typeConvoc1 = "Uhrzeit des Spiels";
$typeConvoc2 = "Spielklassen Beginn";
$ctacContact = "Référant : schedu.inc";
$ctacValue   = "Email :";
$assoConvoc  = "Nachricht :";

// Label des boutons
$btnUpdate  = "Speichern";
$btnReverse = "Inverser accueil";
$btnPdf     = "Terminkalender PDF";
$btnNumber  = "Numerieren";
$btnInsert  = "-- Einfügen -->";
$btnReplace = "-- Ersetzen -->";
$btnRemove  = "<-- Entfernen --";
$btnErase   = "<-- Löschen --";
$btnRaz     = "Alles Löschen";
$btnTime    = "Uhrzeit ändern";
$btnAuto    = "Automatisch";
$btnSave    = "Speichern";
$btnStatus  = "Maj Spieler";
$btnModif   = "Bearbeiten";
$textConvoc = "Nachricht :";

// Messages d'erreur
$msgNoTies     = "Keine Begenung in dieser Gruppe";
$msgNoDivs     = "Keine Liga erstellt";
$msgNoPlaces   = "Kein Ort definiert";
$msgtieSchedule  = "Das Datum ist Pflicht.";
$msgtiePlace    = "Die Halle ist Pflicht.";
$msgtieStep     = "Die Runde ist Pflicht.";
$msgNoCalendar  = "Spielzeiten nicht verfügbar.";
$msgNoGroups    = "Keine Gruppe in dieser Liga.";
$msgNeedVenue   = "Bitte Halle angeben.";
$msgNeedDay     = "Bitte Datum angeben.";
$msgNotRealTime = "Gültige Uhrzeit auswählen.";
$msgNeedCourt   = "Position im Terminkalender auswählen.";
$msgMatchEnded  = "Einige Spiele sind beendet. Die automatische Numerierung ist deaktiviert.";
$msgNoProblems  = "Keine Konflikte entdeckt.";

$msgConvoc = "Benutzen Sie den Knopf '$btnStatus' um den Status aller Spieler auf 'Abwesend' zu setzen, mit einer Ankunftszeit, die der Aufrufzeit entspricht. Diese Information erscheint auf der Seite 'Turnierablauf'.";

$msgConfirm1111   = "Angezeigten Terminkalender löschen?";


?>
