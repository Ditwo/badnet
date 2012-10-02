<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_book.inc";

// Titre  du navigateur
$books      = "Adressbuch";

// Titres des menus
$itForBooks       = "Verwaltung";
$itForPublication = "Veröffentlichung";
$itImport         = "Importieren";

// Titres des fenetres
$tBook      = "Adressbuch";
$tBooks     = "Verwaltung Adressbücher";
$tNewBook   = "Adressbuch erstellen";
$tEditBook  = "Adressbuch bearbeiten";
$tRightBook  = "Rechte verwalten";
$tEditContact = "Kontakt bearbeiten";
$tNewContact  = "Kontakt erstellen";
$tAddContact  = "Kontakt hinzufügen";
$tSendMail    = "Mail verschicken";

// Titre des colonnes
$rowsBooks   = "Adressbücher";
$rowsBooks1  = "Name";
$rowsBooks2  = "Kommentar";
$rowsBooks3  = "Inhaber";

$rowsContacts   = "Mitglied des Adressbuchs";
$rowsContacts1  = "Geschlecht";
$rowsContacts2  = "Name";
$rowsContacts3  = "Kontakt";
$rowsContacts4  = "Kommentar";
$rowsContacts5  = "Aktualisieren";

$rowsContactsA1  = "Verein";
$rowsContactsA2  = "Abkürzung";
$rowsContactsA3  = "Kontakt";
$rowsContactsA4  = "Tel, Email";

$rowsMberContacts   = "Kontakte der Mitglieder";
$rowsMberContacts1  = "Geschlecht";
$rowsMberContacts2  = "Name";
$rowsMberContacts3  = "Vorname";
$rowsMberContacts4  = "Kontakt";
$rowsMberContacts5  = "Typ";
$rowsMberContacts6  = "Kommentare";
$rowsMberContacts7  = "Aktualisieren";

$rowsAssoContacts1  = "Name";
$rowsAssoContacts2  = "Abkürzung";
$rowsAssoContacts3  = "Wer";
$rowsAssoContacts4  = "Kontakt";

$rowsUsers   = "Gespeicherte Benutzer";
$rowsUsers1  = "Login";
$rowsUsers2  = "Name";

// Label des zones
$from    = "Von :";
$to      = "An :";
$subject = "Betreff :";
$message = "Nachricht :";
$manage     = "Verwalter";
$assistant  = "Betreuer";
$friend     = "Freund";
$guest      = "Eingeladen";
$user       = "Besucher";
$type1 = $manage;
$type2 = $assistant;
$type3 = $friend;
$type4 = $guest;
$type5 = $user;
$blkRight = "Rechte verwalten";
$bookName = "Adressbuch: ";
$importFile = "Datei oder url :";

// Menus 
$itedit             = "Adressbuch bearbeiten";
$itAddMemberContact = "Kontakt hinzufügen (Individuum)";
$itNewAssocContact  = "kontakt hinzufügen (Verein)";
$itRight            = "Rechte verwalten";

// Messages d'erreur
$msgNeedSelect  = "Mindestens eine Zeile in der Liste auswählen.";
$msgNoBook      = "Kein Adressbuch vorhanden.";
$msgNoContact   = "Kein Kontakt im Adressbuch.";


// Label des boutons
$btnAddMember  = "Mitglied erstellen";
$btnAddAssoc   = "Verein erstellen";
$btnDelContact = "Kontakt löschen";
$btnMail  = "Verschicken";
$btnEnd   = "Verlassen";
$btnRight = "Rechte ändern";



$private = "Privates Adressbuch";
$hidden  = "Nicht veröffentlichtes Adressbücher";
$visible = "Veröffentlichte Adressbücher";
$restore = "Gelöschte Adressbücher";

?>