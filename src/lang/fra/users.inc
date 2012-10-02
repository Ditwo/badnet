<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/t_user.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$users    = "Utilisateurs";

// Titres des fenetres
$tNewUser  = "Création d'un utilisateur";
$tDelUsers = "Suppression d'utilisateurs";
$tEditUser = "Mise à jour des informations";
$tEditPwd  = "Modification du mot de passe";

// Menus
$itModify   = "Modifier état civil";
$itPassword = "Changer mot de passe";
$itSendMail = "Envoyer email";
$itForEvent = "Tournois";
$itManager  = "Gestionnaires";
$itAssistant = "Assistants";
$itAll       = "Utilisateurs";
$itFriend    = "Ami";
$itGuest     = "Invité";
$itVisitor   = "Visiteur";
$itUserList  = "Liste des utilisateurs";

// Titre des colonnes
$rowsUsers1  = "Nom";
$rowsUsers2  = "Login";
$rowsUsers3  = "Pseudo";
$rowsUsers4  = "Email";
$rowsUsers5  = "Langue";
$rowsUsers6  = "Crée";
$rowsUsers7  = "Dernière visite";
$rowsUsers8  = "Nb connexions";

$rowsManagers1  = "Nom";
$rowsManagers2  = "Login";
$rowsManagers3  = "Pseudo";
$rowsManagers4  = "Email";
$rowsManagers5  = "Langue";
$rowsManagers6  = "Nb visite";
$rowsManagers7  = "Dernière visite";
$rowsManagers8  = "Tournoi";

$selEvents1  = "Tournois";
$selEvents2  = "Droits";

// Label des zones
$user_nameTitle = "";
$oldPwd    = "Ancien mot de passe :";
$userType1     = "Administrateur";
$userType2     = "Utilisateur";
$rghtNotify    = "Notifier le nouvel utilisateur";
$rghtAssoId    = "Association autorisée :";

// Messages d'erreur
$needrowsUsers  = "Vous devez sélectionner au moins 1 utilisateur.";
$needselEvents  = "Vous devez sélectionner au moins un tournois.";
$msgLoginExist  = "Ce login est déjà utilisé. Essayez un autre !";
$msgoldPwd     = "Le précédent mot de passe est obligatoire !";
$msgBadPwd     = "Ancien mot de passe invalide !";
$msgBadUpdate  = "Vous n'avez pas les autorisations pour modifier cet utilisateur !";
$msgConfirmDel = "Confirmez l'effacement des utilisateurs sélectionnés.";
$msgNoManager  = "Aucun gestionnaire de tournoi.";
$msgNoMember   = "Aucun utilisateur enregistré.";

$emailBody    = "Bonjour,\n\n vous pouvez consulter et contrôler vos inscriptions 
sur le site BadNet en utilisant le compte ci dessous. En cas d'erreur ou de question, 
contacter un gestionnaire du tournoi.";

?>