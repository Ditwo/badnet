<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_book.inc";

// Titre  du navigateur
$books      = "Addresses books";

// Titres des menus
$itForBooks       = "Management";
$itForPublication = "Publication";
$blkLegende       = "Legend";

// Titres des fenetres
$tBook      = "Addresses book";
$tBooks     = "Books management";
$tNewBook   = "New book";
$tEditBook  = "Edit book";
$tRightBook  = "Give right on book;";
$tEditContact = "Edit contact";
$tNewContact  = "New contact";
$tAddContact  = "Add contacts to this book";
$tSendMail    = "Send email";

// Titre des colonnes
$rowsBooks   = "Adresses book";
$rowsBooks1  = "Book title";
$rowsBooks2  = "Comment";
$rowsBooks3  = "Owner";

$rowsContacts   = "Address book members";
$rowsContacts1  = "Sexe";
$rowsContacts2  = "Name";
$rowsContacts3  = "Contact";
$rowsContacts4  = "Comment";
$rowsContacts5  = "Update";

$rowsContactsA   = "Address book associations";
$rowsContactsA1  = "Sigle";    // TBC
$rowsContactsA2  = "Name";
$rowsContactsA3  = "Contact";
$rowsContactsA4  = "Comment";
$rowsContactsA5  = "Update";

$rowsMberContacts   = "Contact members";
$rowsMberContacts1  = "Sexe";
$rowsMberContacts2  = "Name";
$rowsMberContacts3  = "Firstname";
$rowsMberContacts4  = "Contact";
$rowsMberContacts5  = "Type";
$rowsMberContacts6  = "Comment";
$rowsMberContacts7  = "Update";

$rowsAssoContacts   = "Contact associations";
$rowsAssoContacts1  = "Name";
$rowsAssoContacts2  = "Sigle";   // TBC
$rowsAssoContacts3  = "Contact";
$rowsAssoContacts4  = "Type";
$rowsAssoContacts5  = "Comment";
$rowsAssoContacts6  = "Update";

$rowsUsers   = "Registered users";
$rowsUsers1  = "Login";
$rowsUsers2  = "Name";


// Label des zones
$from    = "From :";
$to      = "To :";
$subject = "Subject :";
$message = "Message :";
$manage     = "Manager";
$assistant  = "Assistant";
$friend     = "Friend";
$guest      = "Guest";
$user       = "User";
$type1 = $manage;
$type2 = $assistant;
$type3 = $friend;
$type4 = $guest;
$type5 = $user;
$blkRight = "Rights";
$bookName = "Book : ";


// Menus 
$itedit             = "Edit book";
$itAddMemberContact = "Add a member";
$itNewAssocContact  = "Add an association";
$itRight            = "Rights management";

// Messages d'erreur
$msgNeedSelect  = "Select one or more line from the list.";
$msgNoBook      = "No book avaibable.";

// Messages d'aides
$hlp1 = "- Select then name to edit member personnal informations";
$hlp2 = "- Select the firstname to add a contact to a member";
$hlp3 = "- Select a contact to edit it ";
$hlp4 = "- Select sigle to add a contact to a member";



// Label des boutons
$btnAddMember  = "Add member";
$btnAddAssoc   = "Add association";
$btnDelContact = "Delete contact";
$btnMail  = "Send";
$btnEnd   = "Exit";
$btnRight = "Change rights";



$private = "Private book";
$hidden  = "Hidden book";
$visible = "Visible book";
$restore = "Deleted book";

?>
