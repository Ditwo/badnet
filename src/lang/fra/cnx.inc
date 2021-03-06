<?php
include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/t_user.inc";

// Titre  du navigateur
$cnx    = "Badminton Netware";

// Titres des fenetres
$tUsers    = "Gestion des utilisateurs";
$tNewUser  = "Nouvel utilisateur";
$tEditUser = "Modification d'un utilisateur";
$tEditPwd  = "Modification du mot de passe";
$tErrUser  = "Mise � jour d'un utilisateur";

$tInit    = "Connexion";
$tLogin   = "Connexion personnalis�e";

$fNewPwd  = "Perte d'identifiant";
$fNewUser = "Cr�ation de compte";

// Titres des cartouches
$tEventInt  = "Niveau international";
$tEventNat  = "Niveau national";
$tEventReg  = "Niveau r�gional";
$tEventDep  = "Niveau d�partemental";
$tToday     = "----- A l'affiche aujourd'hui ------";

$tNews       = "Br�ves";
$tNextTies   = "Bient�t";
$tEventList  = "Les tournois par �quipes";
$tMostVisited = "Les plus visit�s";
$tLastCreated = "Les plus r�cents";

$tIndiEventList  = "Les tournois individuels";
$tEventIndiInt = $tEventInt;
$tEventIndiNat = $tEventNat;
$tEventIndiReg = $tEventReg;
$tEventIndiDep = $tEventDep;

// Titre des colonnes
$rowsUsers1  = "Nom";
$rowsUsers2  = "Login";
$rowsUsers3  = "Email";
$rowsUsers4  = "Status";
$rowsUsers5  = "Langue";
$rowsUsers6  = "Cr�e";
$rowsUsers7  = "Derniere visite";
$rowsUsers8  = "Nb connexions";

// Label des zones
$Events    = "";
$username  = "Login :";
$password  = "Mot de passe :";
$OldPwd    = "Ancien mot de passe :";
$NewPwd    = "Nouveau mot de passe :";
$Lang      = "Langue :";
//$Email     = "Email :";
$Comment   = "Commentaire :";
$Id        = "Identifiant :";
$Creation  = "Date de Cr�ation :";
$LastModif = "Derni�re mise � jour :";
$LastVisit = "Derni�re visite :";
$NbCnx     = "Nombre de connexion :";
$Type1     = "Utilisateur";
$Type2     = "Administrateur";
$userInfo  = "Adresse email :";

$from    = "De :";
$fromEmail = "Votre email :";
$to      = "� :";
$subject = "Sujet :";
$message = "Message :";


// Messages d'erreur
$msgUserExist     = "Ce login est d�j� utilis�. Essayez un autre !";

$msgOldPwd      = "L\'ancien mot de passe est obligatoire !";
$msgNewPwd      = "Le nouveau mot de passe est obligatoire !";
$msgLang        = "La langue est obligatoire !";
$msgNeedSelect  = "Vous devez s�lectionner au moins 1 utilisateur.";
$msgCnxRefused  = "Mot de passe ou login incorect! Essayer � nouveau.";
$msgCnxExpired  = "Session termin�e. Reconnectez-vous!";
$msgCnxIdle     = "Inactif trop longtemps. Reconnectez-vous!";
$msgusername    = "Le login est obligatoire !";
$msgpassword    = "Le mot de passe est obligatoire !";

$msgLostPwd  = "Si vous avez perdu votre mot de passe, entrez votre adresse email.";
$msgNewPwd = "Un nouveau mot de passe vous sera envoy� automatiquement.";
$msgPwdSend = "Un nouveau mot de passe vous a �t� envoy�.";
$msgPwdNotSend = "Un probl�me est survenu lors de l'envoi de votre nouveau mot de passe.Essayer plus tard.";
$msgInvalidData = "Login ou adresse email inconnu";
$msgSend = "Votre message a �t� envoy�. V�rifiez que vous avez re�u une copie.";

// Label des boutons
$btnConnect   = "Connecter";
$btnRegister  = "Enregistrer";
$btnNew    = "Nouveau compte";
$btnSend   = "Envoyer";
$btnLoose  = "Mot de passe ou login perdu ?";

?>