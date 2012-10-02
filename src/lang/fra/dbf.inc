<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
//include dirname(__FILE__)."/t_div.inc";

// Titre  du navigateur
$dbf    = "Export fede";

// Titres des fenetres
$tExportFede  = "Fichier DBF pour Poona";
$tListFile    = "Résultats archivés";
$itArchives   = "Archives";
$itFede       = "Export FFBa";
$itExport     = "Exportation";
$itImport     = "Importation";
$tSelectFile  = "Choix du fichier";

// Titres des colonnes
$listTies  = "Rencontres avec des matchs terminés";
$listTies1  = "Date";
$listTies2  = "Tour";
$listTies3  = "Division";
$listTies4  = "Groupe";
$listTies5  = "Non joués";
$listTies6  = "Terminés";
$listTies7  = "Validés";
$listTies8  = "Envoyés";

// Label des zones de saisie
$from  = "De :";
$to    = "à :";
$cc    = "Copie à :";
$subject = "Sujet :";
$message = "Message :";
$join    = "Fichier joint :";
$evnt_zone  = "Zone :";
$beginDate  = "Matches du";
$endDate    = "au";
$exportAll  = "traiter les matchs déjà envoyés";

$importFile = "Fichier ou url :";
$inscOne    = "Un tableau :";
$inscTwo    = "Deux tableaux :";
$inscThree  = "Trois tableaux :";

// Texte par defaut du message
$defSubject = "Résultats";
$defMessage = "Veuillez trouver ci-joint les résultats du tournoi ";

// Label des boutons
$errs      = "Erreurs détectées";
$errs1      = "Division";
$errs2      = "Groupe";
$errs3      = "Match";
$errs4      = "Erreur";

// Label des boutons
$btnMail     = "Envoyer";
$btnSelect   = "Choix des matchs";
$btnWrite    = "Générer fichier";
$btnImport   = "Importer";
$btnArchiveFile  = "Archiver";

// Label des messages
$msgevnt_zone   = "Veuillez renseigner la zone.";
$msgDescZone    = "Seul les matches validés sont traités. <br />Pour un interclub départemental, la zone correspond au numéro du département.<br />Pour un interclub régional, elle indique le code de la région.";
$msgfileName     = "Le nom du fichier est obligatoire.";
$msgexportEnded  = "Les résultats ont été envoyés à la fédération. Une copie vous a été envoyée sur votre messagerie.";
$msgNoArchive    = "Aucun fichier archive pour ce tournoi.";
$msgSelectTie    = "Vous devez sélectionner les rencontres. Elles doivent etre comprises entre les dates de début et de fin.";
$msgFileArchived = "Le fichier a été archivé.";

$msgListFile = "Voici le(s) fichier(s) résultat(s) prêt(s) à être importé dans Poona.<br>
- Cliquez sur le nom du fichier<br>
- Enregistrez-le sur ordinateur<br>
- Connectez à Poona avec votre accés<br>
- Importez le fichier<br><br>";
$msgArchive = "<br><br>Utilisez le bouton 'Archiver' pour passer les matchs à l'état 'Envoyé'
<br><br>";

// Label des erreurs
$errOpenFile       = "Impossible de créer le fichier d'export!";
$errPairMissing    = "Paire manquante";
$errPlayerMissing  = "Joueur manquant";
$errNoMatch        = "Aucun match à envoyer.";
$errBadMatch       = "Envoi impossible ! Veuillez corriger les problèmes puis essayer à nouveau";
$errArchiveNotAccessible  = "Le dossier des archives est inacessible. Contacter un administrateur du site.";

?>