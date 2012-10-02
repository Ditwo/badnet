<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_asso.inc";
include dirname(__FILE__)."/t_teams.inc";

// Titre  du navigateur
$asso      = "Associations";

// Titres des fenetres
$tAssos        = "Registered associations";
$tNewAsso      = "Create an association";
$tEditAsso     = "Edit an association";
$tDelAsso      = "Delete an association";
$tRegisterAsso = "Register association";
$tMembers      = "Association Members";
$tAccount      = "Edit account";
$tSearchResult = "Search an association";

// Menus 
$itNewAsso  = "Add";
$itDelAsso  = "Delete";
$itUpdate         = 'CPPP FFBa';
$itUpdateSept     = 'CPPP FFBa 01/09';
$itNewPlayer      = 'New player';
$itNewOfficial    = 'New official';
$itNewOther       = 'Other';

// Titre des colonnes
$rowsSearch1  = "Name";
$rowsSearch2  = "Pseudonyme";
$rowsSearch3  = "Sigle";
$rowsSearch4  = "Type";

$rowsAssocs1  = "Date";
$rowsAssocs2  = "Name";
$rowsAssocs3  = "Pseudonym";		//TBC
$rowsAssocs4  = "Type";
$rowsAssocs5  = "Account";

$rowsRegis1  = "Sex";
$rowsRegis2  = "Identity";
$rowsRegis3  = "Function";
$rowsRegis4  = "Account";
$rowsRegis5  = "Arrival";
$rowsRegis6  = "Departure";


// Label des zones
$assoTypeName  = 'Type :';
$assoSearchType= $assoTypeName;
$assoCount     = 'Account :';
$accountName   = "Old account :";
$accountId     = "New account :";
$resaDate      = "Reservation Date :";

$blkAssoc = 'Association';
$blkAssocReg = 'Team';

// Label des listes

// Messages d'erreur
$msgNoAssocs   = "No registered association";
$msgNoPlayers  = "No registered player";
$msgNoReservations  = "No reservation for the selected items";
$msgNeedAssocs = "Select an association";
$msgTeamWithMember = "One or more player is registered with selected team. Cannot delete it !";
$msgNeedMembers = "Select a member.";
$msgPurchaseExist = "This member has got unpaid purchase . Cannot delete it !";
$msgNoGridsItems  = "No selected item !";
$msgNoCredits     = "No registered credit !";
$msgConfirmDel    = "Do you want to delete selected registrations ?";

// Label des boutons
$btnSearch   = "Search";
$btnAddLogo  = "Select logo";
?>
