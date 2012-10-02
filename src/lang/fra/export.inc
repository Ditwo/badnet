<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_event.inc";

// Titre  du navigateur
$export    = "Export/import de tournoi";

// Titre des onglets
$itArchives   = "Archives";
$itFede       = "Export fédéral";
$itExport     = "Exportation";
$itImport     = "Importation";

// Titres des fenetres
$tExportEvent  = "Sauvegarder le tournoi";
$tImportFile   = "Import à partir d'un fichier";
$tImportWeb    = "Import à partir d'un site";
$tEventDetail  = "Détails du tournoi";
$tFfbaExport   = "Fédéral";
$tBadnetExport = "Sauvegarde";
$tSelectEvent  = "Tournois disponibles";

// Titres des colonnes

// Label des zones de saisie
$join  = "Fichier à télécharger :";
$importFile = "Fichier à utiliser :";
$site = "Url du site :";
$user = "Login :";
$from = "De :";
$to   = "à :";
$cc   = "Copie à :";
$subject  = "Objet :";
$message  = "Message :";

$objectExportEvent = "Sauvegarde tournoi";
$bodyExportEvent = "Voici le fichier contenant la sauvegarde du tournoi.";

$exportDate = "Date de la sauvegarde :";
$evntUpdt   = "Dernière mise à jour :";
$purge = "Vider le tournoi avant la restauration";
$dateRef = "Date de référence:";
$version = "Version base:";

//
$rowsVenues1 = "Lieu";
$rowsVenues2 = "Date";

// Label des boutons
$btnExport    = "Créer le fichier";
$btnImport    = "Lire le fichier";
$btnConnect   = "Se connecter";
$btnMail      = "Envoyer";
$btnStart     = "Importer";
$btnBadnet    = "Sauvegarde";
$btnFfba      = "Export Fédéral";
$btnArchive   = "Archives";


// Label des messages
$msgExportInfos = "Le fichier de sauvegarde du tournoi sera envoyé en copie à votre addresse. Pour utiliser le fichier de sauvegarde sans envoyer de message, cliquer sur son nom en bas de page.";
$msgExportSend = "Le courrier a été envoyé. Vérifiez que vous avez bien recu une copie.";
$msgTo       = "Le destinataire est obligatoire.";
$msgsubject  = "L'objet du message est obligatoire.";
$msgmessage  = "Le message ne peut être vise.";
$msgNoEventAvailable  = "Aucun tournoi disponible.";
$msgBadUrl            = "Impossible de se connecter au site. Vérifier l'url.";
$msgBadUser           = "Connexion impossible. Vérifier vos identifiants.";
$msgBadFile           = "Ce fichier n'est lisible ou n'est pas un fichier de sauvegarde";
$msgFfbaExport    = "A la fin du tournoi, vous devez envoyer les résultats à la fédération. Si votre machine est connectée à internet, vous pouvez directement le fichier, sinon enregistrez le et envoyez le plus tard.";
$msgFfbaArchive   = "Vous pouvez consulter les fichiers dèjà envoyés à la fédération pour ce tournoi et les récuperer si vous en avez besoin.";
$msgBadnetExport  = "Pour travailler en local sur une machine non connectée, effectuer une sauvegarde du tournoi, envoyez par email ou récupérez le fichier obtenu page suivante. Sur votre machine, restaurer le tournoi à partir de ce fichier.";
$msgBadVersion    = "Le fichier provient d'une version antérieure. Il est impossible de le traiter.";
$msgNeedNumAuto   = 'Le numéro d\'autorisation doit être renseigné.';
$msgCheckMatch    = 'Il y a des matches non terminés ou non validés. Aller dans la page déroulement et saisissez les résultats ou valider les matchs avec le lien "Valider" à droite au dessus de la liste des matches après avoir choisi la bonne salle';
$msgNoTeamImport  = "L'import pour les interclubs n'est pas autorisé. Utilisez BadNetTeam pour saisir les résultats sur les lieux de la compétition.";
?>