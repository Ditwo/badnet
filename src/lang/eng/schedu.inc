<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$ties      = "Schedule";

$itConvoc     = "Convocations";
$itAnalyse    = "Analyse";
$itConvocPdf  = "Pdf";
$itConvocMail = "Mailing";
$itPubli      = "Publication";

// Titres des fenetres
$fScheduContact= "Convocation";
$tScheduEdit   = "Edit schedule";
$tScheduNum    = "Matches number";
$calendarDiv   = "Schedule";
$itSchedu      = "Confection";
$itPdf         = "Pdf";
$itPlayerPdf   = "Players per sporthall";
$tScheduAuto   = "Automatic schedule";
$tConfirmation = "Confirmation";
$tHour         = "Hour modification";

// Titre des colonnes
$scheList1  = "Group";
$scheList2  = "Round";
$scheList3  = "Place - Date";
$scheList4  = "Host";
$scheList5  = "Visitor";
$scheList6  = "Score";

$tiesPubli1  = "Stage";
$tiesPubli2  = "Date";
$tiesPubli3  = "Venue";
$tiesPubli4  = "Court";
$tiesPubli5  = "Pos";
$tiesPubli6  = "Visitor";
$tiesPubli7  = "Num";

$tiesSchedu1  = "Round";
$tiesSchedu2  = "Date";
$tiesSchedu3  = "Venue";
$tiesSchedu4  = "Court";
$tiesSchedu5  = "Host";
$tiesSchedu6  = "Home";
$tiesSchedu7  = "Visitor";
$tiesSchedu8  = "Result";

$rowsTeams1  = "Pseudo";
$rowsTeams2  = "Name";
$rowsTeams3  = "Acronym";
$rowsTeams4  = "Email";
$rowsTeams5  = "Message";

$rowsPbs1  = "Player";
$rowsPbs2  = "Draw";
$rowsPbs3  = "Match num";
$rowsPbs4  = "Venue";
$rowsPbs5  = "Hour";
$rowsPbs6  = "Draw";
$rowsPbs7  = "Match num";
$rowsPbs8  = "Venue";
$rowsPbs9  = "Hour";
$rowsPbs10 = "Diff";

// Label des champs
$tdivsList   = "Divisions";
$divsList   = "Divisions :";
$groupsList = "Groups";
$tiesGroup      = "";
$tieStep     = $tiesSchedu1." :";
$tieSchedule = $tiesSchedu2." (jj-mm-aa [hh:mm]) :";
$tiePlace    = $tiesSchedu3." :";
$tieCourt    = $tiesSchedu4." :";
$displayAll  = "Display scheduled matchs";
$tiesDate  = 'Day :';
$tiesPlace = 'Sporthall :';
$firstNum  = 'First number :';
$numOption1  = 'Numéroter à la suite';
$numOption2  = 'Changer de plage chaque salle';
$numOption3  = 'Changer de plage chaque salle et chaque jour';
$drawList    = "Draws";
$nbCourt     = "Number of court : ";
$firstDay    = "Premier jour (jj-mm-aa) : ";
$startOne    = "Heure début (hh:mm) : ";
$endOne      = "Heure fin (hh:mm) : ";
$startSecond = "Reprise lendemain (hh:mm) : ";
$venue       = "Venue :";
$newVenue    = "New venue :";
$day         = "Date :";
$newDay      = "New date (dd-mm-aa) :";
$scheduList  = "Schedule";
$matchList   = "Matchs";
$propagate   = "Propager la durée aux créneaux suivants";
$time        = "Hour :";
$length      = "Length :";
$blkConvoc   = "Convocations";
$delayConvoc = "Delay convocation (mn):";
$lieuConvoc  = "Venue :";
$typeConvoc1 = "Match hour";
$typeConvoc2 = "Draw start at";
$ctacContact = "Référant :";
$ctacValue   = "Email :";
$assoConvoc  = "Message :";


// Label des boutons
$btnUpdate  = "Ok";
$btnReverse = "Reverse host";
$btnPdf     = "PDF schedule";
$btnNumber  = "Number";
$btnInsert  = "-- Insert -->";
$btnReplace = "-- Replace -->";
$btnRemove  = "<-- Delete --";
$btnErase   = "<-- Effacer --";
$btnRaz     = "Delete all";
$btnTime    = "Change hour";
$btnAuto    = "Automatique";
$btnSave    = "Save";
$btnStatus  = "Update playe";
$btnModif   = "Modify";
$textConvoc = "Message :";

// Messages d'erreur
$msgNoTies     = "No ties found for this groupe";
$msgNoDivs     = "No division declared";
$msgNoPlaces   = "No places";
$msgtieSchedule  = "Date is required.";
$msgtiePlace   = "Place is required.";
$msgtieStep    = "Round is required.";
$msgNoCalendar = "No Schedule found.";
$msgNoGroups   = "No groups in this division.";
$msgNeedVenue   = "Venue is mandatory.";
$msgNeedDay     = "Date is mandatory.";
$msgNotRealTime = "Select a correct hour.";
$msgNeedCourt   = "Select a match of the schedule.";
$msgMatchEnded  = "Matches ended exist. No automatic numbered.";
$msgNoProblems  = "No conflit.";

$msgConvoc = "Utiliser le bouton '$btnStatus' pour positionner l'état de tous les joueurs à 'Absent' avec une heure d'arrivée correspondant à leur heure de convocation. Ces informations apparaitront dans la page 'Déroulement', lors de la gestion du tournoi.";

$msgConfirm1111   = "Effacement du calendrier affiché ?";

?>
