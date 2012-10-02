<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
//include dirname(__FILE__)."/t_div.inc";

// Titre  du navigateur
$divs    = "Divisions";

// Titres des fenetres
$tNewDiv     = "New division";
$tEditDiv    = "Edit division";
$tDelDiv     = "Delete division";
$tEditGroup  = "Edit group";
$tDelGorups  = "Delete groups";
$tSelectTeam = "Select teams";

// Menus 
$itForDivs  = "Divisions";
$itNewDiv   = "Add";
$itDelDiv   = "Delete";
$itUpdtDiv  = "Edit";

// Label des zones
$tGroups   = "Division groups";

$divName  = "Name :";
$divStamp = "Acronym :";
$divSize  = "Number of groups :";

$blkGroup = "Group";
$blkNewGroup = "New group";

$groupName  = "Name :";
$groupStamp = "Sigle :";
$groupSize  = "Number of teams :";

$firstRound  = "First round";
$secondRound = "Second round";


$blkNbMatch    = "Number of matches";
$groupNbms  = "Number of Man Singles :";
$groupNbws  = "Number of Women Singles :";
$groupNbmd  = "Number of Men Doubles :";
$groupNbwd  = "Number of Women Doubles :";
$groupNbxd  = "Number of Mixed Doubles :";
$groupType1 = "Group";
$groupType2 = "Group A/R";
$groupType3 = "KO";
$groupType4 = "Qualif";
$groupRankType1 = "Ranking";
$groupRankType2 = "Résultat particulier"; // TBC
$groupUmpire = "Umpire :";
$groupsDiv   = "Composition : ";
$blkTiePoint = "Tie points";
$tieWin      = "Won tie :";
$tieEqual    = "Draw tie :";
$tieLoose    = "Lost tie :";
$tieWO       = "WO tie :";

$blkMatchPoint = "Matches points";
$matchWin      = "Won match :";
$matchLoose    = "Lost match :";
$matchRtd      = "Retired match :";
$matchWO       = "WO match :";

$groupSizemax  = "Max number of teams:";
$groupNameno   = "Group ";


// Titre de colonnes 
$rowsGroups1 = "Name";
$rowsGroups2 = "Size";
$rowsGroups3 = "Type";
$rowsGroups4 = "MS";
$rowsGroups5 = "LS";
$rowsGroups6 = "MD";
$rowsGroups7 = "LD";
$rowsGroups8 = "XD";

$rowsTeams1  = "Team";
$rowsTeams2  = "Acronym";
$rowsTeams3  = "Association";
$rowsTeams4  = "Captain";
$rowsTeams5  = "Comment";

$selectTeams1 = "Name";
$selectTeams2 = "Acronym";
$selectTeams3 = "Captain";
$selectTeams4 = "Comment";

$rowsGroup1 = "Team";
$rowsGroup2 = "Initials";
$rowsGroup3 = "Captain";

$tiesList  = "Group ties";
$tiesList1  = "Week";
$tiesList2  = "When";
$tiesList3  = "Where";
$tiesList4  = "Host";
$tiesList5  = "Visitor";
$tiesList6  = "Score";


// Messages d'erreur
$msgExistSerial    = "Cette série existe déjà.";
$msgPositif        = "All numbers must be positive.";
$msgNeedSelect     = "Select at least one team";
$msgTooTeam        = "Too much teams in this group";
$msgNoTeam         = "No team in this group";
$msgNeedTeams      = "Select at least one team .";
$msgNoGroups       = "No group in this division";
$msgNoTeams        = "No team in the groups of this division.";
$msgNoDivs         = "No division declared.";
$msgdivStamp       = "Acronym is required.";
$msgNeedGroups     = "Select one group.";
$msgConfirmDelGroups = "Selected group will be delete.";
$msgConfirmDelDiv  = "Selected division will be delete.";
$msgdivName     = "Name is mandatory.";
$msgdivStamp    = "Acronym is mandatory.";
$msgdivSize     = "Group number is mandatory.";
$msggroupName   = "Group name is mandatory.";
$msggroupSramp  = "Group sigle is mandatory.";
$msggroupNbms   = "Single men number is mandatory.";
$msggroupNbws   = "Single women number is mandatory.";
$msggroupNbmd   = "Double men number is mandatory.";
$msggroupNbwd   = "Double women number is mandatory.";
$msggroupNbxd   = "Mixed number is mandatory.";
$msgmatchWin    = "Win match points are mandatory.";
$msgmatchLoose  = "Loose match points are mandatory.";
$msgmatchRtd    = "Retired match points are mandatory";
$msgmatchWO     = "WO match points are mandatory";
$msgtieWin    = "Win tie points are mandatory";
$msgtieLoose  = "Loose tie points are mandatory";
$msgtieEqual  = "Equal tie points are mandatory";
$msgtieWO     = "WO tie points are mandatory";
$msgInfoAddTeam = "To add team, save the group then modify it.";

// Label des boutons
$btnSelectTeam     = "Select team";
$btnNewTeam     = "New team";
$btnTeamUp      = "Up";
$btnTeamDown    = "Down";
$btnDelTeam     = "Delete";
$btnEnd         = "Close";

$btnPdf      = "PDF Version";

?>
