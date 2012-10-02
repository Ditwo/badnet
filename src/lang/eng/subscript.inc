<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
//include dirname(__FILE__)."/t_teams.inc";

// Titre  du navigateur
$subscript  = "Subscription";

// Titres des fenetres
$tSubscription    = "Results Subscription";

// Menus 

// Titre des colonnes
$rowsTeams   = "Teams";
$rowsTeams1  = "Name";
$rowsTeams2  = "Stamp";

$Subscripts   = "Results Subscription";
$Subscripts1  = "Events";
$Subscripts2  = "Teams";


// Label des boutons
$btnRegister  = 'Subscribe';
$btnDeregister = 'End subscription';
$btnInfo = 'Edit subscription';


// Label des champs
$email = "Email address :";
$news  = "Get news from this event";
$foot = "\n\n------------------------\n".
"Message automatically sent by B@dNet.\n".
"For more informations, go on our site:\n";

$rightChanged = "Hi,\n\nan administrator has given you rights on this events ".
".\nYou're now :";

$rightDesc = "\n\nManager : you could manage the event.\n".
"Assistant  : you could help enter the results.\n".
"Friend : you could see any datas.\n".
"Guest : you could see hidden datas.\n".
"Visitor : you could only see published datas.\n";

$win = "\nbeat(s)\n";

// Label des messages
$needrowsTeams   = "You need to select at least 1 team.";
$msgSubscriptDone  = "Subscription Ok. You will received an email each time a result will be registered for the selected teams.";
$msgemail   = "Your email address is invalid !";



?>