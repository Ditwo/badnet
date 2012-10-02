<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$ties      = "Rencontres";

// Titres des fenetres
$tieTitle = 'Rencontre :';
$tTieResult = 'Résultat de la rencontre';
$tDetail    = 'Détails';

// Menus 

// Titre des colonnes
$titleDraw0   = "Premier tour";
$titleDraw1   = "Deuxième tour";
$titleDraw2   = "Troisième tour";
$drawTitle1   = "Vainqueur";
$drawTitle2   = "Finale";
$drawTitle3   = "Demi-finale";
$drawTitle4   = "Quart de finale";

$rowsMatchs1  = "Match";
$rowsMatchs4  = "Résultat";
$rowsMatchs5  = "Score<br />A";
$rowsMatchs6  = "Score<br />B";
$rowsMatchs7  = "Set<br />A";
$rowsMatchs8  = "Set<br />B";
$rowsMatchs9  = "Pt<br />A";
$rowsMatchs10  = "Pt<br />B";


$rowsRanking1 = "Equipe";
$rowsRanking2 = "Sigle";
$rowsRanking3 = "Pts";
$rowsRanking4 = "P";
$rowsRanking5 = "V";
$rowsRanking6 = "EV";
$rowsRanking7 = "E";
$rowsRanking8 = "ED";
$rowsRanking9 = "D";
$rowsRanking10 = "Matchs<br />++";
$rowsRanking11 = "Matchs<br />--";
$rowsRanking12 = "Matchs<br />diff";
$rowsRanking13 = "Sets<br />++";
$rowsRanking14 = "Sets<br />--";
$rowsRanking15 = "Sets<br />diff";
$rowsRanking16 = "Pts<br />++";
$rowsRanking17 = "Pts<br />--";
$rowsRanking18 = "Pts<br />diff";

$tiesList  = "Rencontres du groupe";
$tiesList1  = "Equipe";

// Label des champs
$tdivsList   = "Divisions";
$divsList   = "Divisions :";
$groupsList = "Groupes";
$resultsDiv = "Résultats :";
$rankingsDiv = "Classements :";
$penaltiesL  = "Bonus/malus :";
$penaltiesR  = "Bonus/malus :";
$t2rPenalties = "Bonus(+)/malus(-) :";

$draw_name  = "Division :";
$rund_name  = "Groupe :";
$tie_place  = "Lieu de la rencontre :";
$tie_step   = "Tour :";
$tie_schedule  = "Date prévue :";

$tieToValid    = "Rencontres à valider :";
$tieToComplete = "Rencontre à saisir :";
$tieFinished   = "Rencontres terminées :";

$team1   = "Equipe hote:";
$team2   = "Equipe visiteuse:";


// Messages d'erreur
$msgNoTeam     = "Pas d'équipe dans ce groupe";
$msgNoDivs     = "Aucune division déclarée";
$msgNoGroups   = "Aucun groupe dans cette division";
$msgNoTeams    = "Aucune équipe dans les groupes de cette division.";
$msgNotSamePlayer = "Une paire doit avoir deux joueurs différents";
$msgpenaltiesL    = "Le bonus/malus ne peut etre vide.";
$msgpenaltiesR    = $msgpenaltiesL;

$msgResult = "Deux équipes à égalités sont départagées par le résultat de la rencontre les ayant opposées.";
$msgRank   = "En cas d'égalité, les équipes sont départagées par la différence de match gagnés/perdus. Si l'égalité persiste, c'est la différence set gagnés/perdus qui est utilisée. En dernier lieu, la différence point gagnés/perdus est prise en compte.";
$msgIncompletedTie = "Rencontre incompléte; saisie impossible.";

// Label des boutons
$btnAddPlayerL  = "Ajout joueur";
$btnAddPlayerR  = $btnAddPlayerL;
$btnResult     = "Saisir résultats";
$btnValid      = "Valider les résultats";
$btnPdf          = "Version PDF";
$btnPdfRanking   = "Résultats PDF";
$btnPdfSchedule  = "Horaires PDF";
$btnPdfAllTies   = "Rencontres PDF";
$btnRaz          = "Effacer";
$btnPoona        = "Export Poona";
$btnArchive      = "Archiver";
$btnDelSaisie    = "Raz date de saisie";
$btnDelValid     = "Raz date de validation";
?>