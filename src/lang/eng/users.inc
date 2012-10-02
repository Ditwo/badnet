<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/t_user.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$users    = "Users";

// Titres des fenetres
$tUsers    = "Users management";
$tNewUser  = "Add user";
$tDelUsers = "Delete users";
$tEditUser = "Edit ";
$tEditPwd  = "Change password";

// Menus
$itModify   = "Edit civil information";
$itPassword = "Change password";
$itSendMail = "Send email";
$itForEvent = "Events";
$itManager  = "Managers";
$itAssistant = "Assistant";
$itAll       = "Users";
$itFriend    = "Friend";
$itGuest     = "Guest";
$itVisitor   = "Visitor";
$itUserList  = "User List";

// Titre des colonnes
$rowsUsers1  = "Name";
$rowsUsers2  = "Login";
$rowsUsers3  = "Pseudo";
$rowsUsers4  = "Email";
$rowsUsers5  = "Langage";
$rowsUsers6  = "Created the";
$rowsUsers7  = "Last visit";
$rowsUsers8  = "Connections number";

$selEvents1  = "Events";
$selEvents2  = "Rights";

// Label des zones
$user_nameTitle = "";
$oldPwd    = "Old password :";
$userType1     = "Administrator";
$userType2     = "User";
$rghtEventId   = "Events invited :";
$rghtTeamId    = "Teams invited :";

// Messages d'erreur
$needrowsUsers  = "You must select at least 1 user.";
$needselEvents  = "You must select at least 1 event.";
$msgLoginExist  = "This login is already used. Please, try another one !";
$msgoldPwd     = "The old password is required !";
$msgBadPwd     = "Invalid old password !";
$msgBadUpdate  = "You do not have the rights to edit this user !";
$msgConfirmDel = "Are you sure you want to delete the selected users ...";
$msgNoManager  = "No tournament manager.";
$msgNoMember   = "No registered member.";

// Label des boutons
$btnCancel    = "Cancel";
$btnRegister  = "Registrer";

?>
