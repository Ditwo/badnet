<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_book.inc";

// Titre  du navigateur
$books      = "Carnet d'adresse";

// Titres des menus
$itForBooks       = "Gestion";
$itForPublication = "Publication";
$itImport         = "Importation";

// Titres des fenetres
$tBook      = "Carnet d'adresse";
$tBooks     = "Gestion des carnets d'adresses";
$tNewBook   = "Création d'un carnet";
$tEditBook  = "Modification d'un carnet";
$tRightBook  = "Attribution des privilèges";
$tEditContact = "Modification d'un contact";
$tNewContact  = "Création d'un contact";
$tAddContact  = "Ajout de contacts au carnet";
$tSendMail    = "Envoyer un mail";

// Titre des colonnes
$rowsBooks   = "Carnets d'adresse";
$rowsBooks1  = "Titre du carnet";
$rowsBooks2  = "Commentaire";
$rowsBooks3  = "Propriétaire";

$rowsContacts   = "Membres du carnet d'adresse";
$rowsContacts1  = "Sexe";
$rowsContacts2  = "Nom";
$rowsContacts3  = "Contact";
$rowsContacts4  = "Commentaire";
$rowsContacts5  = "Mise à jour";

$rowsContactsA1  = "Association";
$rowsContactsA2  = "Sigle";
$rowsContactsA3  = "Contact";
$rowsContactsA4  = "Tel, email";

$rowsMberContacts   = "Contacts des membres";
$rowsMberContacts1  = "Sexe";
$rowsMberContacts2  = "Nom";
$rowsMberContacts3  = "Prénom";
$rowsMberContacts4  = "Contact";
$rowsMberContacts5  = "Type";
$rowsMberContacts6  = "Commentaire";
$rowsMberContacts7  = "Mise à jour";

$rowsAssoContacts1  = "Nom";
$rowsAssoContacts2  = "Sigle";
$rowsAssoContacts3  = "Qui";
$rowsAssoContacts4  = "Contact";

$rowsUsers   = "Utilisateurs enregistrés";
$rowsUsers1  = "Login";
$rowsUsers2  = "Nom usuel";

// Label des zones
$from    = "De :";
$to      = "à :";
$subject = "Sujet :";
$message = "Message :";
$manage     = "Gestionnaire";
$assistant  = "Assistant";
$friend     = "Ami";
$guest      = "Invité";
$user       = "Visiteur";
$type1 = $manage;
$type2 = $assistant;
$type3 = $friend;
$type4 = $guest;
$type5 = $user;
$blkRight = "Droit à octroyer";
$bookName = "Carnet: ";
$importFile = "Fichier ou url :";

// Menus 
$itedit             = "Modifier le carnet";
$itAddMemberContact = "Ajouter un contact individuel";
$itNewAssocContact  = "Ajouter un contact associatif";
$itRight            = "Gérer les privilèges";

// Messages d'erreur
$msgNeedSelect  = "Sélectionnez au moins une ligne de la liste.";
$msgNoBook      = "Aucun carnet d'adresse disponible.";
$msgNoContact   = "Aucun contact pour de carnet d'adresse.";


// Label des boutons
$btnAddMember  = "Créer membre";
$btnAddAssoc   = "Créer association";
$btnDelContact = "Supprimer contact";
$btnMail  = "Envoyer";
$btnEnd   = "Quitter";
$btnRight = "Changer les droits";



$private = "Carnet privée";
$hidden  = "Carnet non publiée";
$visible = "Carnet publiée";
$restore = "Carnet supprimée";

?>