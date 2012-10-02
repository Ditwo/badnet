<?php

//include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/t_user.inc";
include dirname(__FILE__)."/menu_V.inc";

// Titre  du navigateur
$users    = "Users";

// Titres des fenetres
$tInfos = "Your data";
$tManager    = "Management";


// Messages 
$msgEntries  = "Your association has been registered in the following tounament. You can consult datas of the players";
$msgManager  = "You can manage these tournaments.";
$msgAssist   = "You can assist the manager of these tournaments.";
$msgNoFriendEvents  = "No tournament.";
$msgNoManageEvents  = $msgNoFriendEvents;
$msgNoAssistEvents  = $msgNoFriendEvents;
$msgNoFriendAsso    = "No association.";

// Boutons
$btnUser =  "Modify";
$btnPwd  =  "Password";

$rowsEvent1 = "Season";
$rowsEvent2 = "Event";
$rowsAssist1 = $rowsEvent1;
$rowsAssist2 = $rowsEvent2;

?>