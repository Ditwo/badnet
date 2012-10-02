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
$msgExportInfos = "Le fichier du tournoi sera envoyé en copie à votre addresse. Pour utiliser le fichier sans envoyer de message, sélectionner son nom en bas de page.";
$msgExportSend  = "Le courrier a été envoyé. Vérifiez que vous avez bien recu une copie.";
$msgTo       = "Le destinataire est obligatoire.";
$msgsubject  = "L'objet du message est obligatoire.";
$msgmessage  = "Le message ne peut être vise.";
$msgNoEventAvailable  = "Aucun tournoi disponible.";
$msgBadUrl            = "Impossible de se connecter au site. Vérifier l'url.";
$msgBadUser           = "Connexion impossible. Vérifier vos identifiants.";
$msgBadFile           = "Ce fichier n'est lisible ou n'est pas un fichier BadNet";
$msgFfbaExport    = "A la fin du tournoi, vous devez envoyer les résultats à la FFBa. Si votre machine est connectée à internet, vous pouvez directement le fichier, sinon enregistrez le et envoyez le plus tard.";
$msgFfbaArchive   = "Vous pouvez consulter les fichiers dèjà envoyés à la FFba pour ce tournoi et les récuperer si vous en avez besoin.";
$msgBadnetExport  = "Pour travailller en local sur une machine non connectée, exportez le tournoi au format BadNet, envoyez par email ou récupérez le fichier obtenu page suivante. Sur votre machine, importez le tournoi à partir de ce fichier.";
$msgBadVersion    = "Le fichier provient d'une version antérieure de BadNet. Il est impossible de le traiter.";
$msgNeedNumAuto   = 'Le numéro d\'autorisation doit être renseigné. Aller dans : Parametres->Pratique->Modifier';
?>