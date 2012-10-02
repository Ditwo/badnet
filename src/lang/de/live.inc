<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$matches    = "Live";

// Titres des fenetres
$tOrderMatch    = "Spielreihenfolge";
$tUmpireCourt   = "Schiedsrichterverwaltung";
$tUmpireEdit    = "Schiedsrichter zuweisen";
$tOptionEdit    = "Anzeige Optionen";
$itList      = "Spiele";
$itLive      = "Live";
$itStream    = "Video";
$itPdfList   = "Ergebnisblatt";
$itPdfListLite = "Vereinfachtes Ergebnisblatt";
$itPalma       = "Sieger";
$itValid       = "Bestätigen";
$itAnalyse     = "Analyse";

// titre des blocs
$blkUmpiring = "Schiedsrichterverwaltung";
$blkPrint    = "Schnell Druck";

// Titre des colonnes
$rowOrder1  = "Reihenfolge";
$rowOrder2  = "Disziplin";

$rowsMatchs4  = "Status";
$rowsMatchs5  = "Spielfeld";
$rowsMatchs6  = "Ergebnis";
$rowsMatchs7  = "Dauer";

$rowsControl1  = "Reihenfolge";
$rowsControl2  = "Spiel";
$rowsControl3  = "Mannschaft A";
$rowsControl4  = "Mannschaft B";
$rowsControl5  = "Spielfeld";
$rowsControl6  = "Beginn";
$rowsControl7  = "Ende";
$rowsControl8  = "Ergebnis";

$rowUmpires1  = "NAME Vorname";
$rowUmpires2  = "Spielfeld";
$rowUmpires3  = "Funktion";
$rowUmpires4  = "Rang";
$rowUmpires5  = "Aktiv";
$rowUmpires6  = "Aktuell";

$rowsList1  = "Num";
$rowsList2  = "Spielklasse";
$rowsList3  = "Runde";
$rowsList4  = "Spieler/Paarung A";
$rowsList5  = "Spieler/Paarung B";
$rowsList6  = "Spielfeld";
$rowsList7  = "Beginn";
$rowsList8  = "Ende";
$rowsList9  = "Ergebnis";


// Label des zones
$dateList  = "Datum :";
$placeList = "Ort :";
$stepList  = "Runde :";
$courtNumber  = "Spielfeld :";
$status1  = "Gewinner";
$status2  = "Verlierer";
$status3  = "Offiziel";
$status4  = "Auto-Schiedsrichter";
$umpire        = "Schiedsrichter :";
$umpireAffected   = "Dem Spiel einen Schiedsrichter zuweisen";
$serviceJudge  = "Juge de service :";
$training      = "Einspielzeit (in mn) :";
$rest           = "Pause (in mn) :";
$avg            = "mittlere Spieldauer :";
$nbMatchPlayer  = "Max Spielanzahl :";
$printGroupList = "Gruppen :";
$printKoList    = "Hauptfeld :";
$printRankList  = "Spielstärke :";
//$blkOfficial   = "Choix arbitre";

$teamDeclaration = "Formulaire de déclaration des équipes (PDF) live.inc";
$teamEdit        = "Saisie déclaration et résultats live.inc";
$teamResults     = "Ergebnis der Begegnung (PDF)";
$editSchedule    = "Uhrzeit und Spielfeld";
$matchEdit       = "Mannschaften und Spielergebnis";
$scoresheetEdit  = "Ergebnisblatt";
$matchOrder      = "Spielreihenfolge";
$nbMatch         = "Angezeigte Spiele :";
$nbCourt         = "Anzahl der Spielfelder :";

$levelTrace    = "Niveau de trace live.inc :";
$fontList      = "Schriftart :";
$courtNumberLive  = "Spielfeld anzeigen";
$courtNumberClose = "Spielfeld schließen";

$umpireFunction = "Nächste Funktion :";
$umpireRank     = "Reihenfolge in der Funktion :";


// Messages d'erreur 
$msgNoTies = "Keine Spiele Heute. Bitte werfen Sie einen Blick auf den Terminkalender.";
$msgNoDate = "Kein datum angegeben";
$msgNotie_place = "Keine Ort angegeben";
$msgNotie_step  = "Keine Runde angegeben";
$msgNoLive = "Keine Live Ergebnisse für dieses Turnier.";
$msgNoStream = "Kein Live Video für dieses Turnier.";
$msgNoLiveNow = "Kein Spiel am laufen. Versuchen Sie es später nochmal.";

$msgOnlyIE  = "Es gibt kein Plugin für Il n'existe pas de plugin pour les navigateurs autre que Internet Explorer.";
$msgWinamp = "Sie haben kein Internet Explorer oder sind allergisch dagegen, versuchen Sie es mit Winamp.";
$msgVLC    = "Probieren Sie es mit VideoLan VLC.";
$msgLink   = "In beiden Fällen benutzen Sie die unten angegebene Adresse.";

// Label des boutons
$btnUp   = "Hoch";
$btnDown = "Runter";
$btnEnd  = "Beenden";
$btnMagic = "Reihenfolge vorschlagen";
$btnInitServer = "Live beginnen";
$btnUmpire     = "Schiedsrichter";
$btnOptions    = "Optionen";
$btnDisplayEnded = "Beendete Spiele anzeigen";
$btnMaskEnded    = "Beendete Spiele maskieren";

// Legende
$lgdAbsent  = "Abwesend";
$lgdPlay    = "Spielt";
$lgdUmpire  = "Schiedsrichter";
$lgdRest    = "Pause";

?>
