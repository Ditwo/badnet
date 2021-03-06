<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
//include dirname(__FILE__)."/t_div.inc";

// Titre  du navigateur
$dbf    = "Export Verband";

// Titres des fenetres
$tExportFede  = "Ergebnisse zur schicken FFba";
$tListFile    = "Archivierte Ergebnisse";
$itArchives   = "Archive";
$itFede       = "Export FFBa";
$itExport     = "Exportieren";
$itImport     = "Importieren";
$tSelectFile  = "Datei aussuchen";

// Titres des colonnes
$listTies  = "Begegnungen mit beendeten Partien";
$listTies1  = "Datum";
$listTies2  = "Runde";
$listTies3  = "Liga";
$listTies4  = "Gruppe";
$listTies5  = "Nicht gespielt";
$listTies6  = "Beendet";
$listTies7  = "Best�tigt";
$listTies8  = "Verschickt";

// Label des zones de saisie
$from  = "Von :";
$to    = "an :";
$cc    = "Cc :";
$subject = "Betreff :";
$message = "Nachricht :";
$join    = "Dateien :";
$evnt_zone  = "Zone :";
$beginDate  = "Spiele vom";
$endDate    = "bis";
$exportAll  = "Bereits verschickte Spiele ebenfalls aussuchen";

$importFile = "Datei oder url :";
$inscOne    = "Eine Disziplin :";
$inscTwo    = "Zwei Disziplinen :";
$inscThree  = "Drei Disziplinen :";

// Texte par defaut du message
$defSubject = "Ergebnisse";
$defMessage = "Anbei die Ergebnisse des Turniers.";

// Label des boutons
$errs      = "Fehler entdeckt";
$errs1      = "Liga";
$errs2      = "Gruppe";
$errs3      = "Spiel";
$errs4      = "Fehler";

// Label des boutons
$btnMail     = "Abschicken";
$btnSelect   = "Spiele aussuchen";
$btnWrite    = "Datei erstellen";
$btnImport   = "Importieren";
$btnArchiveFile  = "Archivieren";

// Label des messages
$msgevnt_zone   = "Die Zone angeben.";
$msgDescZone    = "Seul les matches valid�s sont trait�s. <br />Pour un interclub d�partemental, la zone correspond au num�ro du d�partement.<br />Pour un interclub r�gional, elle indique le code de la r�gion. dbf.inc";
$msgfileName     = "Der Dateiname ist ein Pflichtfeld.";
$msgexportEnded  = "Die Ergebnisse wurden dem Verband zugeschickt. Eine Kopie geht an Ihre Adresse.";
$msgNoArchive    = "Keine Archiv Datei f�r dieses Turnier.";
$msgSelectTie    = "Sie m�ssen Begegnungen ausw�hlen. Sie m�ssen innerhalb des angegebenen Datum stattgefunden haben.";
$msgFileArchived = "Die Datei wurde archiviert.";

// Label des erreurs
$errOpenFile       = "Unm�glich die Exportdatei zu erstellen!";
$errPairMissing    = "Paarung fehlt";
$errPlayerMissing  = "Spieler fehlt";
$errNoMatch        = "kein Spiel verschickt.";
$errBadMatch       = "Nachricht kann nicht abgeschickt werden! Bitte korrigieren Sie die Fehler und versuchn es erneut";
$errArchiveNotAccessible  = "Der Archivordner ist unerreichbar. Nehmen Sie mit einem Administrator Kontakt auf.";

?>