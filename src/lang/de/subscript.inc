<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
//include_once dirname(__FILE__)."/t_teams.inc";

// Titre  du navigateur
$subscript  = "Abonnements";

// Titres des fenetres
$tSubscription    = "Ergebnisse abonnieren";

// Menus 

// Titre des colonnes
$rowsTeams   = "Mannschaft";
$rowsTeams1  = "Name";
$rowsTeams2  = "Abkürzung";

$Subscripts   = "Ergebnisse abonnieren";
$Subscripts1  = "Turniere";
$Subscripts2  = "Mannschaft";


// Label des boutons
$btnRegister  = 'Abonnieren';
$btnDeregister = 'Abonnement löschen';
$btnInfo = 'Abonnement einsehen';


// Label des champs
$email = "Email :";
$news  = "Neuigkeiten des Turniers erhalten";
$foot = "\n\n------------------------\n".
"Die nachricht wird von BadNet automatisch verschiclt.\n".
"Mehr Informationen unter:\n";

$rightChanged = "Hallo,\n\nein Administrator hat Ihre Rechte verändert".
"\nSie sind jetzt:";

$rightDesc = "\n\nVerwalter: Sie können für dieses Turnier alles verändern.\n".
"Betreuer  : Sie können Ergebnisse eintragen.\n".
"Freund : Sie können alle Einstellungen einsehen.\n".
"Eingeladen : Sie können versteckte Einstellungen einsehen und veröffentlichen.\n".
"Besucher : Sie können nur veröffentlichte Angaben sehen.\n";

$win = "\nschlägt\n";

// Label des messages
$needrowsTeams   = "Sie müssen mindestens eine Mannschaft auswählen.";
$msgSubscriptDone  = "Abonnement beendet. Sie  erhalten immer eine E-mail, wenn ein Ergebnis einer ausgewählten mannschaft gespeichert wird.";
$msgUnsubscriptDone  = "Abmeldung beendet.";
      $msg = 'msgUnsubscriptDone';
$msgemail   = "Ungültige Email Adresse.";


?>