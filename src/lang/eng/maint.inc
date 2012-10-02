<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$prefs      = "Maintenance";

// Titres des fenetres
$itForMaint   ="Actions"; 
$itUpdtRank   ="Update ranking"; 
$itUpdtName   ="Update name";
$itImportRank ="Update from FFBa"; 
$itDelRank    ="Delete"; 
$itDisplaySubs  ="Subscriptions"; 
$itUpdtPosT2T   ="Update t2t"; 

// Titres des blocs

// Titre des colonnes
$subsList ="Subscriptions"; 
$subsList1 ="User"; 
$subsList2 ="Event"; 
$subsList3 ="Team"; 

$playersRanking ="Ranking"; 
$playersRanking1 ="Sex"; 
$playersRanking2 ="Name firstname"; 
$playersRanking3 ="Date of Birth"; 
$playersRanking4 ="Licence"; 
$playersRanking5 ="IBF number"; 
$playersRanking6 ="Discipline"; 
$playersRanking7 ="Ranking"; 
$playersRanking8 ="Points"; 
$playersRanking9 ="Team"; 

$errs = "Error on update !";
$errs1 = "Name";
$errs2 = "Firstname";
$errs3 = "Date of birth";
$errs4 = "Licence";
$errs5 = "IBF number";
$errs7 = "Error";

// Label des listes
$eventsList = "Event :";
$assocsList = "Association :";

// Messages d'erreur
$needplayersRanking = "Please, select at least one player.";
$msgWorkingFFBa     = "Downloading from FFBa website...";
$msgNoAssoc         = "No association found for this event";
// Label des boutons

?>
