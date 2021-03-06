<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_event.inc";

// Titre  du navigateur
$export    = "Export/import de tournoi";

// Titre des onglets
$itArchives   = "Archives";
$itFede       = "Poona Export";
$itExport     = "Exportation";
$itImport     = "Importation";

// Titres des fenetres
$tExportEvent  = "Export tournament";
$tImportFile   = "Import from file";
$tImportWeb    = "Import from BadNet site";
$tEventDetail  = "Tournament details";
$tFfbaExport   = "French Federation";
$tBadnetExport = "Format BadNet";
$tSelectEvent  = "Available tournaments";

// Titres des colonnes

// Label des zones de saisie
$join  = "File to download :";
$importFile = "File to use :";
$site = "BadNet site URL :";
$user = "Login :";
$from = "From :";
$to   = "to :";
$cc   = "Copy to :";
$subject  = "Objet :";
$message  = "Message :";

$objectExportEvent = "BadNet tournament";
$bodyExportEvent = "Voici le fichier du tournoi au format BadNet";

$exportDate = "Exportation date:";
$evntUpdt   = "Last update :";
$purge = "Empty tournament before importation";
$dateRef = "Reference date :";
$version = "BadNet Version :";

//
$rowsVenues1 = "Venue";
$rowsVenues2 = "Date";

// Label des boutons
$btnExport    = "Create file";
$btnImport    = "Read file";
$btnConnect   = "Connect";
$btnMail      = "Send";
$btnStart     = "Import";
$btnBadnet    = "Export BadNet";
$btnFfba      = "Export Poona";
$btnArchive   = "Archives";


// Label des messages
$msgExportInfos = "Le fichier du tournoi sera envoy� en copie � votre addresse. Pour utiliser le fichier sans envoyer de message, s�lectionner son nom en bas de page.";
$msgExportSend  = "Le courrier a �t� envoy�. V�rifiez que vous avez bien recu une copie.";
$msgTo       = "Le destinataire est obligatoire.";
$msgsubject  = "L'objet du message est obligatoire.";
$msgmessage  = "Le message ne peut �tre vise.";
$msgNoEventAvailable  = "Aucun tournoi disponible.";
$msgBadUrl            = "Impossible de se connecter au site. V�rifier l'url.";
$msgBadUser           = "Connexion impossible. V�rifier vos identifiants.";
$msgBadFile           = "Ce fichier n'est lisible ou n'est pas un fichier BadNet";
$msgFfbaExport    = "A la fin du tournoi, vous devez envoyer les r�sultats � la FFBa. Si votre machine est connect�e � internet, vous pouvez directement le fichier, sinon enregistrez le et envoyez le plus tard.";
$msgFfbaArchive   = "Vous pouvez consulter les fichiers d�j� envoy�s � la FFba pour ce tournoi et les r�cuperer si vous en avez besoin.";
$msgBadnetExport  = "Pour travailller en local sur une machine non connect�e, exportez le tournoi au format BadNet, envoyez par email ou r�cup�rez le fichier obtenu page suivante. Sur votre machine, importez le tournoi � partir de ce fichier.";
$msgBadVersion    = "Le fichier provient d'une version ant�rieure de BadNet. Il est impossible de le traiter.";
$msgNeedNumAuto   = 'Le num�ro d\'autorisation doit �tre renseign�. Aller dans : Parametres->Pratique->Modifier';
?>