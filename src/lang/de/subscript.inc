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
$rowsTeams2  = "Abk�rzung";

$Subscripts   = "Ergebnisse abonnieren";
$Subscripts1  = "Turniere";
$Subscripts2  = "Mannschaft";


// Label des boutons
$btnRegister  = 'Abonnieren';
$btnDeregister = 'Abonnement l�schen';
$btnInfo = 'Abonnement einsehen';


// Label des champs
$email = "Email :";
$news  = "Neuigkeiten des Turniers erhalten";
$foot = "\n\n------------------------\n".
"Die nachricht wird von BadNet automatisch verschiclt.\n".
"Mehr Informationen unter:\n";

$rightChanged = "Hallo,\n\nein Administrator hat Ihre Rechte ver�ndert".
"\nSie sind jetzt:";

$rightDesc = "\n\nVerwalter: Sie k�nnen f�r dieses Turnier alles ver�ndern.\n".
"Betreuer  : Sie k�nnen Ergebnisse eintragen.\n".
"Freund : Sie k�nnen alle Einstellungen einsehen.\n".
"Eingeladen : Sie k�nnen versteckte Einstellungen einsehen und ver�ffentlichen.\n".
"Besucher : Sie k�nnen nur ver�ffentlichte Angaben sehen.\n";

$win = "\nschl�gt\n";

// Label des messages
$needrowsTeams   = "Sie m�ssen mindestens eine Mannschaft ausw�hlen.";
$msgSubscriptDone  = "Abonnement beendet. Sie  erhalten immer eine E-mail, wenn ein Ergebnis einer ausgew�hlten mannschaft gespeichert wird.";
$msgUnsubscriptDone  = "Abmeldung beendet.";
      $msg = 'msgUnsubscriptDone';
$msgemail   = "Ung�ltige Email Adresse.";


?>