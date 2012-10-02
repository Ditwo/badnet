<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_teams.inc";
include dirname(__FILE__)."/t_asso.inc";

// Titre  du navigateur
$teams  = "Teams";

// Titres des fenetres
$tNewTeam     = "Add team";
$tEditTeam    = "Edit team";
$tEraseTeams  = "Delete teams";
$tPubliTeams  = "Teams publication";
$tMergePlayers = "Players fusion";
$tMembers     = "Members";
$tBilan       = "Resume";

// Menus 
$itNewPlayer   = "New player";
$itNewOfficial = "New court official";
$itNewOther    = "New member";
$itUpdate      = 'CPPP FFBa';
$itUpdateSept  = 'CPPP FFBa 01/09';
$itPdfPlayers  = "Entries PDF";
$itPdfPairs    = "Pairs PDF";
$itXlsEntries  = 'Players Xls';

$itMembers     = "Members";
$itCredits     = 'Credits';
$itReservation = "Reservations";
$itDiscount    = 'Discount';

$itNewResa     = 'Add reservation';
$itDiscount    = 'Add reservation';
$itSelectItems = 'Items';
$itCredits     = 'Credits';
$itSolder      = 'Balance';

$itDetailM   = 'Per player';
$itDetailD   = 'Per date';
$itDetailI   = 'Per item';
$itHotel     = 'Hotels';

// Titre des colonnes
$rowsTeams1  = "Association";
$rowsTeams2  = "Team";
$rowsTeams3  = "Stamp";
$rowsTeams4  = "Date";
$rowsTeams5  = "Number";

$rowsTies1  = "Venue";
$rowsTies2  = "Date";
$rowsTies3  = "Stage";
$rowsTies4  = "Opponent";
$rowsTies5  = "Result";
$rowsTies6  = "Score";

$rowsPlayers_V1 = "Gender";
$rowsPlayers_V2 = "FAMILY NAME";
$rowsPlayers_V3 = "Ranking";

$rowsPairs1 = "Players/Pairs";
$rowsPairs2 = "Draw";
$rowsPairs3 = "Level";
$rowsPairs4 = "Points";
$rowsPairs5 = "Rank int.";
$rowsPairs6 = "Rank nat.";
$rowsPairs7 = "Stage";

$rowsPlayers1  = "FAMILY NAME";
$rowsPlayers2  = "Gender";
$rowsPlayers3  = "Category";
$rowsPlayers4  = "Ranking";
$rowsPlayers5  = "Holder";
$rowsPlayers6  = "License";
$rowsPlayers7  = "IBF number";
$rowsPlayers8  = "NOC";
$rowsPlayers9  = "First match";
$rowsPlayers10  = "Convocation";
$rowsPlayers11  = "Venue";

$rowsRoom1  = "Gender";
$rowsRoom2  = "FAMILY NAME";
$rowsRoom3  = "Hotel";
$rowsRoom4  = "Type";
$rowsRoom5  = "Room";
$rowsRoom6  = "Arrival";
$rowsRoom7  = "Nb night";

$rowsOffos1  = "FAMILY NAME";
$rowsOffos2  = "Gender";
$rowsOffos3  = "Status";
$rowsOffos4  = "Fonction";
$rowsOffos5  = "NOC";

$rowsAdmRegis1  = "Gender";
$rowsAdmRegis2  = "Identity";
$rowsAdmRegis3  = "Function";
$rowsAdmRegis4  = "Account";
$rowsAdmRegis5  = "Discount";
$rowsAdmRegis6  = "Paid";
$rowsAdmRegis7  = "Rest";

$rowsAdmTeams1  = "Date";
$rowsAdmTeams2  = "Venue";
$rowsAdmTeams3  = "Team";
$rowsAdmTeams4  = "Account";
$rowsAdmTeams5  = "Discount";
$rowsAdmTeams6  = "Paid";
$rowsAdmTeams7  = "Rest";

$rowsPurchase1  = "Date";
$rowsPurchase2  = "Purchase";
$rowsPurchase3  = "Identity";
$rowsPurchase4  = "Coût";
$rowsPurchase5  = "Discount";
$rowsPurchase6  = "Paid";
$rowsPurchase7  = "Rest";

$rowsCredits1 = "Date";
$rowsCredits2 = "Who";
$rowsCredits3 = "Object";
$rowsCredits4 = "Montant";
$rowsCredits5 = "Reste";			//TBC
$rowsCredits6 = "Refundable";		

$rowsSend1 = "Club";
$rowsSend2 = "Contact";
$rowsSend3 = "Email";
$rowsSend4 = "Result";

// Label des zones
$assoUrl    = "Website :";
$assoTeam   = "Association :";
$assoCount  = "Account :";
$publiPropa = "Propager aux inscrits des équipes";

$assoCount     = 'Account :';
$accountName   = "Old account :";
$accountId     = "New account :";
$resaDate      = "Reservation date :";

$costTeam     = "Total purchase :";
$discountTeam = "Discount :";
$payedTeam    = "Paid :";
$duTeam       = "Rest :";

$teamText   = "Convocation :";

// Label des boutons
$btnAddAssoc = "Add Association";
$btnAddLogo  = "Select logo";
$btnAddPhoto = "Select the picture";


// Label des messages
$msgNoPlayers     = "No player found for this team.";
$msgNoPairs       = "No players/pairs for this team.";
$msgNoTies        = "No tie for this team.";
$msgNoTeams       = "No registered team.";
$msgassoId        = "Association is required.";
$msgNoSelectItems = "No item published.";
$msgNoCredits     = "No credit found.";
$msgNoReservations = "No reservation found.";
$msgConfirmDelTeam = "Supression of selected teams.";
$msgConfirmPubTeam = "Confirm the publication of selected teams.";
$msgNeedTeams     = "You must select at least 1 team";
$msgTeamWithMember = "The team has some player. Desctruction impossible!";
$msgNoOffos = "No official found for this team.";
$msgNoTies = "No tie found for this team.";
$msgNoTeamLogo     = "No logo for this team. We use its association's logo.";
$msgNeedTwoPlayers = "Selet two players!";
$msgLicenceDiffer  = "Licence number have to be identical.";
?>