<?php
include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/t_user.inc";

// Titre  du navigateur
$cnx    = "Badminton Netware";

// Titres des fenetres
$tUsers    = "Users management";
$tNewUser  = "New user";
$tEditUser = "Edit user";
$tEditPwd  = "Edit password";
$tErrUser  = "Edit user";

$tInit    = "Connexion";
$tLogin   = "Login";

$fNewPwd  = "Loose password";
$fNewUser = "New account";

// Titres des cartouches
$tEventInt  = "International";
$tEventNat  = "National";
$tEventReg  = "District";
$tEventDep  = "Local";
$tToday     = "----- What's happening today ------";

$tNews       = "Last news";
$tNextTies   = "Soon";
$tEventList  = "Team tournaments";
$tMostVisited = "Most visited";
$tLastCreated = "Newers";

$tIndiEventList  = "Individual tournaments";
$tEventIndiInt = $tEventInt;
$tEventIndiNat = $tEventNat;
$tEventIndiReg = $tEventReg;
$tEventIndiDep = $tEventDep;

// Titre des colonnes
$rowsUsers1  = "Name";
$rowsUsers2  = "Login";
$rowsUsers3  = "Email";
$rowsUsers4  = "Status";
$rowsUsers5  = "Langage";
$rowsUsers6  = "Created";
$rowsUsers7  = "Last connexion";
$rowsUsers8  = "Connexions number";

// Label des zones
$Events    = "";
$username  = "Login :";
$password  = "Password :";
$OldPwd    = "Old password :";
$NewPwd    = "New password :";
$Lang      = "Langage :";
$Email     = "Email :";
$Comment   = "Comment :";
$Id        = "Id :";
$Creation  = "Date de Création :";
$LastModif = "Last update :";
$LastVisit = "Last connexion :";
$NbCnx     = "connexions number :";
$Type1     = "User";
$Type2     = "Administrator";
$from    = "From :";
$fromEmail = "Your email :";
$to      = "To :";
$subject = "Subject :";
$message = "Message :";
$userInfo  = "Login or password :";

// Messages d'erreur
$msgUserExist     = "This login is already used. Try another one !";

$msgOldPwd     = "The old password is required !";
$msgNewPwd     = "The new password is required !";
$msgLang       = "Please choose a langage !";
$msgNeedSelect = "Please select at least one user.";
$cnxRefused    = "Incorrect password or login !";
$cnxExpired    = "Session is over. Try to reconnect !";
$cnxIdle       = "Too long inactivity. Try to reconnect !";
$msgusername   = "Login is required !";
$msgpassword   = "Password is required !";

$msgLostPwd  = "Si vous avez perdu votre mot de passe, entrez votre login ou votre adresse email.";
$msgNewPwd = "Un nouveau mot de passe vous sera envoyé automatiquement.";
$msgPwdSend = "Un nouveau mot de passe vous a été envoyé.";
$msgPwdNotSend = "Un problème est survenu lors de l'envoi de votre nouveau mot de passe.Essayer plus tard.";
$msgInvalidData = "Login ou adresse email inconnu";

$msgfrom       = "Please, let identify yourself.";
$msgfromEmail  = "Your email address is required.";
$msgsubject    = "Please, fill in the subject form.";
$msgnoValidEmail = "Your email is not valid.";
$msgmessage      = "Write here your message !!!";
$msgSend         = "Your message have been successfully sent.";

// Label des boutons
$Cancel    = "Cancel";
$btnConnect   = "Connect";
$btnRegister  = "Save";
$btnNew    = "New account";
$btnSend   = "Send";
$btnLoose  = "Loose password ?";

?>
