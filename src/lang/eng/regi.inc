<?php
include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/database.inc";
include dirname(__FILE__)."/t_mber.inc";
include dirname(__FILE__)."/t_regi.inc";

// Titre  du navigateur
$regi    = "Entries";

// Titres des fenetres
$tNewRegPlayer       = "Player entry";
$tNewRegOfficial     = "Court official entry";
$tNewRegistration    = "New entry";
$tEditRegistration   = "Edit entry";
$tDelRegistration    = "Delete entry";
$tSearchPlayer       = 'Search players';
$tSearchFedePlayer   = 'Connection to federal website....';
$tSearchIbfPlayer    = 'Connection to IBF website...';
$tSelectFees         = "Entrie's fees creation";

$itPurchases   = "Purchases";
$itCredits     = "Provisions";
$itPdf         = "Pdf";
$itFees        = "Entries";
$itHotel       = "Accomodation";
$itItems       = "Purchase";
$itSolder      = 'Balance';
$itArrivals    = 'Arrivals';
$itDepartures  = 'Departures';
$itAbsent      = 'Absent';
$itPresent     = 'Present';

// Titre  des menus
$itRegisterMember = "Add entry";

// titre des blocs
$blkAdmi  = "Management";
$blkRegi  = "Entrie";
$blkCriteria   = "Search keywords";

// Titre des colonnes
$rowsTeams1  = "Name";
$rowsTeams2  = "Stamp";
$rowsTeams3  = "Association";
$rowsTeams4  = "Captain";

$rowsAssos1  = "Name";
$rowsAssos2  = "Pseudo";
$rowsAssos3  = "Web site";

$rowsRegiTeam1  = "Entry date";
$rowsRegiTeam2  = 'Gender';
$rowsRegiTeam3  = 'Player';
$rowsRegiTeam4  = 'Ranking';
$rowsRegiTeam5  = "License";
$rowsRegiTeam6  = "IBF number";
$rowsRegiTeam7  = "<b>Team</b>";

$rowsRegiV1  = "Gender";
$rowsRegiV2  = "Name";
$rowsRegiV3  = "Country";
$rowsRegiV4  = "Team";
$rowsRegiV5  = "Team";

$rowsRegis2  = "Photo";
$rowsRegis3  = "Gender";
$rowsRegis4  = "FAMILY NAME Second Name";
$rowsRegis5  = "Category";
$rowsRegis6  = "Ranking";
$rowsRegis7  = "License";
$rowsRegis8  = "IBF number";
$rowsRegis9  = "Team";

$rowsPlayers1  = "Photo";
$rowsPlayers2  = "Gender";
$rowsPlayers3  = "Family name";
$rowsPlayers3  = "First name";
$rowsPlayers4  = "License";
$rowsPlayers5  = "Born";
$rowsPlayers6  = "Team";
$rowsPlayers7  = "Dep";
$rowsPlayers8  = "Single";
$rowsPlayers9  = "Double";
$rowsPlayers10 = "Mixed";

$rowsFind1  = "Gender";
$rowsFind2  = "Family name";
$rowsFind3  = "First name";
$rowsFind4  = "License";
$rowsFind5  = "Category";
$rowsFind6  = "Club";

$rowsAdmRegis1  = "Gender";
$rowsAdmRegis2  = "Id";
$rowsAdmRegis3  = "Function";
$rowsAdmRegis4  = "Account";
$rowsAdmRegis5  = "Purchase";
$rowsAdmRegis6  = "Discount";
$rowsAdmRegis7  = "Paid";
$rowsAdmRegis8  = "Balance";
$rowsAdmRegis10  = "Single";
$rowsAdmRegis11  = "Double";
$rowsAdmRegis12  = "Mixed";

$rowsPurchase1  = "Date";
$rowsPurchase2  = "Purchase";
$rowsPurchase3  = "Account";
$rowsPurchase4  = "Cost";
$rowsPurchase5  = "Discount";
$rowsPurchase6  = "Paid";
$rowsPurchase7  = "Balance";

$rowsCredit1  = "Date";
$rowsCredit2  = "Purchase";
$rowsCredit3  = "Account";
$rowsCredit4  = "Initial";
$rowsCredit5  = "Left";
$rowsCredit6  = "Refundable";

$rowsTransport1  = "Gender";
$rowsTransport2  = "FAMILY NAME";
$rowsTransport3  = "Status";
$rowsTransport4  = "Arrival";
$rowsTransport5  = "Venue";
$rowsTransport6  = "Departure";
$rowsTransport7  = "Venue";

// Label des zones
$regiAccount = "Account :";
$regiTeam    = "Team :";
$mberSexeLabel  = "Gender :";
$noc            = "NOC code :";
$rankRankS      = "Single :";
$rankRankD      = "Double :";
$rankRankM      = "Mixed :";
$rankAverageS   = "Single :";
$rankAverageD   = "Double :";
$rankAverageM   = "Mixed :";
$club           = "Association stamp :";
$departement    = "Country :";
$licencie       = "Only registered player";
$septembre      = "September CPPP";
$single      = "Single :";
$double      = "Double :";
$mixed       = "Mixed :";
$inscOne     = "One draw :";
$inscTwo     = "Two draws :";
$inscThree   = "Three draws :";
$regiCatage  = "Category :";
$assoId      = "Club :";
$assoDpt     = "Country :";

$searchType1  = "Base fédérale";
$searchType2  = "Base BadNet";

$sourceS = "Single : last modification";
$sourceD = "Double : last modification";
$sourceM = "Mixed : last modification";
$sourceSF = "Single: from federation the";
$sourceDF = "Double : from federation the";
$sourceMF = "Mixed : from federation the";

$Page   = "Page to display :";
$Nombre = "Players per page :";
$Total  = "Number of players :";

$memberSolde  = "Balance :";
$accountSolde = "Account balance :";


// Label des listes

// Messages d'erreur
$msgNeedLicence     = "Only players with licence number are done.";
$msgNeedPlayers     = "You need to select at least 1 player.";
$msgNotFound        = "No player found !";
$msgPlayerNotFound    = "No player found on B@dNet databes !";
$msgPlayerFedeNotFound = "No player found on B@dNet databes !";
$msgPlayerIBFNotFound  = "No player found on B@dNet databes !";
$msgUrlFedeNotAccessible   = "Connection to FFBa website error !";
$msgUrlIbfNotAccessible    = "Connection to IBF website error !";
$msgNoRegisteredPlayer = "No registered player found !";
$msgSearchNeedData    = "To make a search, give name (at least 3 characters),<br> licence number or IBF number.";
$msgTeamNeedClub      = "Registration not found. Check the association of the player team";
$msgRegiPlay   = "One of the player is linked to matches; you're not allowed to delete his registration.";
$msgTooMuchPlayers = "More than 500 players found. Please precise your search keywords.";
$msgNoTeams = "No registered team found";
$msgNeedRegistration    = "You need to select at least 1 registered member.";
$msgConfirmDel         = "Do you really want to delete the selected registrations ?";
$msgRegiCommands = "A registration member is linked to buys. You're not allowed to delete his registration.";
$msgNoRegisteredOfficial = "No registered officials found";
$msgNoPurchases = "No purchase found for this member.";
$msgNoAccount   = "No account found for this member. Unable to manage his purchase.";
$msgNoCredit    = "No credit for this member";

// Label des boutons
$btnAddMber     = "Add member";
$btnValidate    = "Validate";
$btnSearchFede  = "Search (FFBa)";
$btnSearchIbf   = "Search (IBF)";
$btnAddPhoto    = "Select photo";
$btnGo          = "Reload";

?>