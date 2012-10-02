<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$ties      = "Ties";

// Titres des fenetres
$tieTitle = 'Tie :';
$tTieResult = 'Tie result';

// Menus 

// Titre des colonnes
$titleDraw0   = "First round";
$titleDraw1   = "Second round";
$titleDraw2   = "Third round";
$drawTitle1   = "Winner";
$drawTitle2   = "Final";
$drawTitle3   = "Semi-final";
$drawTitle4   = "Quarter final";

$rowsMatchs1  = "Match";
$rowsMatchs4  = "Score";
$rowsMatchs5  = "Point<br />A";
$rowsMatchs6  = "Point<br />B";
$rowsMatchs7  = "Games<br />A";
$rowsMatchs8  = "Games<br />B";
$rowsMatchs9  = "Pts<br />A";
$rowsMatchs10  = "Pts<br />B";


$rowsRanking1 = "Team";
$rowsRanking2 = "Stamp";
$rowsRanking3 = "Pts";
$rowsRanking4 = "P";
$rowsRanking5 = "V";
$rowsRanking6 = "E";
$rowsRanking7 = "D";
$rowsRanking8 = "Matches<br />++";
$rowsRanking9 = "Matches<br />--";
$rowsRanking10 = "Matches<br />diff";
$rowsRanking11= "Games<br />++";
$rowsRanking12 = "Games<br />--";
$rowsRanking13 = "Games<br />diff";
$rowsRanking14 = "Pts<br />++";
$rowsRanking15 = "Pts<br />--";
$rowsRanking16 = "Pts<br />diff";

$tiesList  = "Ties list";
$tiesList1  = "Team";

// Label des champs
$tdivsList   = "Divisions";
$divsList   = "Divisions :";
$groupsList = "Groups";
$resultsDiv = "Results : ";
$rankingsDiv = "Ranking : ";
$penaltiesL  = "Bonus/malus :";
$penaltiesR  = "Bonus/malus :";
$t2rPenalties = "Bonus(+)/malus(-) :";

$draw_name  = "Division :";
$rund_name  = "Group :";
$tie_place  = "Place :";
$tie_step   = "Stage :";
$tie_schedule  = "Date :";


// Messages d'erreur
$msgNoTeam     = "No team on this group";
$msgNoDivs     = "No division found";
$msgNoGroups   = "No group found for this division";
$msgNoTeams    = "No team found int the groups of this division.";
$msgNotSamePlayer = "A pair must be composed of two different players";

$msgResult = "Deux équipes à égalités sont départagées par le résultat de la rencontre les ayant opposées.";
$msgRank   = "En cas d'égalité, les équipes sont départagées par la différence de match gagnés/perdus. Si l'égalité persiste, c'est la différence set gagnés/perdus qui est utilisée. En dernier lieu, la différence point gagnés/perdus est prise en compte.";
$msgIncompletedTie = "Impcompleted tie.";


// Label des boutons
$btnAddPlayer  = "Add player";
$btnResult     = "Add results";
$btnValid      = "Validate results";
$btnPdf        = "PDF Version";
$btnPdfRanking   = "PDF Results";
$btnPdfSchedule  = "PDF Schedule";
$btnPdfAllTies   = "PDF Ties";

?>