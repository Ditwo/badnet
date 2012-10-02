<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_event.inc";

// Titre  du navigateur
$export    = "Export/import von Turnieren";

// Titre des onglets
$itArchives   = "Archive";
$itFede       = "Export FFBa";
$itExport     = "Exportieren";
$itImport     = "Importieren";

// Titres des fenetres
$tExportEvent  = "Das Turnier exportieren";
$tImportFile   = "Import aus Datei";
$tImportWeb    = "Import von einer Badnet Homepage";
$tEventDetail  = "Turnierdetails";
$tFfbaExport   = "Fédération Française";
$tBadnetExport = "BadNet Format";
$tSelectEvent  = "Verfügbare Turniere";

// Titres des colonnes

// Label des zones de saisie
$join  = "Herunterzuladene Datei :";
$importFile = "Datei benutzen :";
$site = "Url der Badnet Homepage :";
$user = "Login :";
$from = "Von :";
$to   = "An :";
$cc   = "Cc :";
$subject  = "Betreff :";
$message  = "Nachricht :";

$objectExportEvent = "BadNet Turnier";
$bodyExportEvent = "Hier ist die Turnierdatei im BadNet format";

$exportDate = "Export Datum :";
$evntUpdt   = "Letzte Aktualisierung :";
$purge = "Vor dem Import Turnier leeren ";

//
$rowsVenues1 = "Ort";
$rowsVenues2 = "Datum";

// Label des boutons
$btnExport    = "Datei erstellen";
$btnImport    = "Datei einlesen";
$btnConnect   = "Verbinden";
$btnMail      = "Verschicken";
$btnStart     = "Import";
$btnBadnet    = "Export BadNet";
$btnFfba      = "Export FFba";
$btnArchive   = "Archive";


// Label des messages
$msgExportInfos = "Die Turnierdatei wird Ihnen in Kopie zugesandt. Um die Datei ohne versandte Nachricht zu  verwenden, wählen Sie den Namen auf der Seite unten.";
$msgExportSend = "Die nachricht wurde verschickt. Überprüfen Sie ob Sie die Kopie korrekt erhalten haben.";
$msgTo       = "Der Empfänger ist Pflicht.";
$msgsubject  = "Betreff muss ausgefüllt sein.";
$msgmessage  = "Die Nachricht kann nicht leer sein.";
$msgNoEventAvailable  = "Kein Turnier verfügbar.";
$msgBadUrl            = "Keine Verbindung zur Homepage möglich. Überprüfen Sie die Url.";
$msgBadUser           = "Verbindung unmöglich. Überprüfen Sie Benutzernamen und Passwort.";
$msgBadFile           = "Die Datei ist unlesbar oder keine BadNet Datei.";
$msgFfbaExport    = "A la fin du tournoi, vous devez envoyer les résultats à la FFBa. Si votre machine est connectée à internet, vous pouvez directement le fichier, sinon enregistrez le et envoyez le plus tard.";
$msgFfbaArchive   = "Vous pouvez consulter les fichiers dèjà envoyés à la FFba pour ce tournoi et les récuperer si vous en avez besoin.";
$msgBadnetExport  = "Pour travailller en local sur une machine non connectée, exportez le tournoi au format BadNet, envoyez par email ou récupérez le fichier obtenu page suivante. Sur votre machine, importez le tournoi à partir de ce fichier.";
$msgBadVersion    = "Diese Datei stammt von einer älteren BadNet Version. Unmöglich diese zu bearbeiten.";
$msgNeedNumAuto   = 'Die autorisierungsnummer muss angegeben sein. gehen Sie in: Parameter->Nützliche Infos->Bearbeiten';
?>