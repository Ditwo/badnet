<?php

include dirname(__FILE__)."/t_user.inc";
include dirname(__FILE__)."/menu_V.inc";

// Titre  du navigateur
$users    = "Benutzer";

// Titres des fenetres
$tInfos = "Ihre Angaben";
$tEntries = "Ihre Anmeldungen";
$tArchived = "Turnier archivieren";
$tTeam     = "Mannschaftsturnier";
$tIndividual = "Einzelturnier";
$tManager    = "Verwaltung";


// Messages 
$msgEntries  = "Dies sind die Turniere an denen der Verein teilgenommen hat. Sie können sich alle Ergebnisse anschauen.";
$msgManager  = "Sie sind Turnierverwalter. Sie können Veränderungen vornehmen und Mitgliedern Rechte erteilen.";
$msgAssist   = "Sie sind Betreuer dieser Turniere. Sie können Ergebnisse eingeben.";
$msgNoFriendEvents  = "Kein Turnier.";
$msgNoManageEvents  = $msgNoFriendEvents;
$msgNoAssistEvents  = $msgNoFriendEvents;
$msgNoFriendAsso    = "Kein Verein";
// Boutons
$btnUser =  "Bearbeiten";
$btnPwd  =  "Passwort";

?>