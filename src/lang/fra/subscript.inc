<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
//include_once dirname(__FILE__)."/t_teams.inc";

// Titre  du navigateur
$subscript  = "Abonnements";

// Titres des fenetres
$tSubscription    = "Abonnement aux résultats";

// Menus 

// Titre des colonnes
$rowsTeams   = "Equipes engagées";
$rowsTeams1  = "Nom";
$rowsTeams2  = "Sigle";

$Subscripts   = "Abonnements aux résultats";
$Subscripts1  = "Tournois";
$Subscripts2  = "Equipes";


// Label des boutons
$btnRegister  = 'Abonner';
$btnDeregister = 'Supprimer abonnement';
$btnInfo = 'Voir abonnement';


// Label des champs
$email = "Adresse email :";
$news  = "Recevoir les news du tournoi";
$foot = "\n\n------------------------\n".
"Message envoyé automatiquement par BadNet.\n".
"Pour plus d'information consulter le site:\n";

$rightChanged = "Bonjour,\n\nun administrateur a modifié vos droits sur ".
"ce tournoi.\nVous êtes maintenant :";

$rightDesc = "\n\nGestionnaire : vous pouvez tout faire sur le tournoi.\n".
"Assistant  : vous pouvez saisir des résultats.\n".
"Ami : vous pouvez consultez toutes les données.\n".
"Invité : vous pouvez consultez les données cachées et publiées.\n".
"Visiteur : vous pouvez consultez uniquement les données publiées.\n";

$win = "\nbat\n";

// Label des messages
$needrowsTeams   = "Vous devez sélectionner au moins une équipe.";
$msgSubscriptDone  = "Abonnement terminé. Vous recevrez un email chaque fois qu'un résultat de match sera enregistré pour les équipes sélectionnées.";
$msgUnsubscriptDone  = "Desabonnement terminé.";
      $msg = 'msgUnsubscriptDone';
$msgemail   = "Votre adresse email est non valide.";


?>