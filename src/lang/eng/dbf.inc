<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
//include dirname(__FILE__)."/t_div.inc";

// Titre  du navigateur
$dbf    = "Export to FFBa";

// Titres des fenetres
$tExportFede    = "Send results to FFba";
$tListFile    = "Résultats envoyés";
$itArchives   = "Archives";
$itFede       = "French export";
$itExport     = "Exportation";
$itImport     = "Importation";
$tSelectFile  = "File selection";

// Titres des colonnes
$listTies   = "Tie with matches over";
$listTies1  = "Date";
$listTies2  = "Round";
$listTies3  = "Division";
$listTies4  = "Group";
$listTies5  = "Not played";
$listTies6  = "Over";
$listTies7  = "Validate";
$listTies8  = "Sent";

// Label des zones de saisie
$from  = "From :";
$to    = "To :";
$cc    = "Copy To :";
$subject = "Subject :";
$message = "Message :";
$join    = "Attachment :";
$evnt_zone  = "Zone :";
$beginDate  = "From date";
$endDate    = "to";

$importFile = "File or url :";
$inscOne    = "One draw :";
$inscTwo    = "Two draws :";
$inscThree  = "Three draws :";

// Texte par defaut du message
$defSubject = "Results";
$defMessage = "Please find thereby the results of the tournament ";

// Label des boutons
$errs      = "Error detected";
$errs1      = "Division";
$errs2      = "Group";
$errs3      = "Match";
$errs4      = "Error";

// Label des boutons
$btnMail     = "Send";
$btnSelect   = "Matches selection";
$btnWrite    = "Generate file";
$btnImport   = "Import file";

// Label des messages
$msgevnt_zone   = "Please select a zone.";
$msgDescZone    = "Only validated matches are traités. <br />For a country interclub, the zone stands for the country number.<br />For a regional interclub, the zone is the regional code.";
$msgfileName     = "The name of the file is required.";
$msgexportEnded  = "Results sent to the Federation. You are also going to received a copy.";

// Label des erreurs
$errOpenFile       = "Impossible de créer le fichier d'export!";
$errPairMissing    = "Pair missing";
$errPlayerMissing  = "Player misssing";
$errNoMatch        = "No match to be sent.";
$errBadMatch       = "Error on sending ! Please fix the problem then try another time.";

?>
