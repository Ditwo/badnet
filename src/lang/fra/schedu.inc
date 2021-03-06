<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$ties      = "Calendrier";

$itConvoc = "Convocations";
$itAnalyse    = "Analyse";
$itConvocPdf  = "Impression PDF";
$itConvocMail = "Envoyer par email";
$itPubli      = "Publication";

// Titres des fenetres
$fScheduContact= "Convocation";
$tScheduEdit   = "Modification calendrier";
$tScheduNum    = "Num�rotation des matches";
$calendarDiv   = "Calendrier";
$itSchedu      = "Confection";
$itPdf         = "Document pdf";
$itBlabla      = "Priorit�s";
$itPlayerPdf   = "Joueurs/salles";
$itNbMatch     = "Matchs par joueur";
$tScheduAuto   = "Plannification automatique";
$tConfirmation = "Confirmation";
$tHour         = "Modification de l'heure";

// Titre des colonnes
$scheList1  = "Groupe";
$scheList2  = "Tour";
$scheList3  = "Lieu - heure";
$scheList4  = "H�te";
$scheList5  = "Visiteur";
$scheList6  = "Score";

$tiesPubli1  = "Tour";
$tiesPubli2  = "Date";
$tiesPubli3  = "Lieu";
$tiesPubli4  = "Court";
$tiesPubli5  = "Pos";
$tiesPubli6  = "Visiteur";
$tiesPubli7  = "Num";

$tiesSchedu1  = "Tour";
$tiesSchedu2  = "Date";
$tiesSchedu3  = "Lieu";
$tiesSchedu4  = "Court";
$tiesSchedu5  = "A/R";
$tiesSchedu6  = "Domicile";
$tiesSchedu7  = "Visiteur";
$tiesSchedu8  = "R�sultat";

$rowsTeams1  = "Pseudo";
$rowsTeams2  = "Nom";
$rowsTeams3  = "Sigle";
$rowsTeams4  = "Email";
$rowsTeams5  = "Message";

$rowsPbs1  = "Joueur";
$rowsPbs2  = "Tableau";
$rowsPbs3  = "Num match";
$rowsPbs4  = "Salle";
$rowsPbs5  = "Heure";
$rowsPbs6  = "Tableau";
$rowsPbs7  = "Num match";
$rowsPbs8  = "Salle";
$rowsPbs9  = "Heure";
$rowsPbs10  = "Delta";

$rowsNbs1  = "Joueur";
$rowsNbs2  = "Nb match";

$rowsBlabla1  = "Tableau";
$rowsBlabla2  = "Tour";
$rowsBlabla3  = "Date";
$rowsBlabla4  = "Salle";
$rowsBlabla5  = "Paire 1";
$rowsBlabla6  = "Paire 2";
$rowsBlabla7  = "Poid";

// Label des champs
$tdivsList   = "Divisions";
$divsList   = "Divisions :";
$groupsList = "Groupes";
$tiesGroup      = "";
$tieStep     = $tiesSchedu1." :";
$tieSchedule = $tiesSchedu2." (jj-mm-aa [hh:mm]) :";
$tiePlace    = $tiesSchedu3." :";
$tieCourt    = $tiesSchedu4." :";
$displayAll  = "Afficher aussi les matchs avec date";
$tiesDate  = 'Jour :';
$tiesPlace = 'Salle :';
$firstNum  = 'Premier num�ro :';
$numOption1  = 'Num�roter � la suite';
$numOption2  = 'Changer de plage chaque salle';
$numOption3  = 'Changer de plage chaque salle et chaque jour';
$drawList    = "Tableau:";
$nbCourt     = "Nombre de court : ";
$firstDay    = "Premier jour (jj-mm-aa) : ";
$startOne    = "Heure d�but (hh:mm) : ";
$endOne      = "Heure fin (hh:mm) : ";
$startSecond = "Reprise lendemain (hh:mm) : ";
$venue       = "Salle :";
$newVenue    = "Nouvelle salle :";
$day         = "Date :";
$newDay      = "Nouvelle date (jj-mm-aa) :";
$scheduList  = "Echeancier";
$matchList   = "Matchs";
$propagate   = "Propager la dur�e aux cr�neaux suivants";
$convoc      = "D�lai de convocation (mn):";
$propagateConvoc   = "Propager le d�lai de convocation aux cr�neaux suivants";
$time        = "Heure :";
$length      = "Dur�e (en mn) :";
$blkConvoc   = "Convocations";
$delayConvoc = "D�lai convocation (mn) :";
$lieuConvoc  = "Lieu de convocation :";
$typeConvoc1 = "Heure de match";
$typeConvoc2 = "D�but tableau";
$ctacContact = "R�f�rant :";
$ctacValue   = "Email :";
$assoConvoc  = "Message :";
$rest  = "Temps de repos (en mn) :";
$raz   = "Effacer l'�ch�ancier actuel"; 

// Label des boutons
$btnUpdate  = "Enregistrer";
$btnReverse = "Inverser accueil";
$btnPdf     = "Calendrier PDF";
$btnNumber  = "Num�roter";
$btnInsert  = "-- Ins�rer -->";
$btnReplace = "-- Remplacer -->";
$btnRemove  = "<-- Enlever --";
$btnErase   = "<-- Enlever --";
$btnPause   = "-- Ins�rer pause -->";
$btnInvert  = "Inverser deux matchs";
$btnRaz     = "Tout effacer";
$btnTime    = "Changer heure";
$btnAuto    = "Automatique";
$btnSave    = "Enregistrer";
$btnStatus  = "Maj joueur";
$btnModif   = "Modifier";
$textConvoc = "Message :";

// Messages d'erreur
$msgNoTies     = "Pas de rencontre dans ce groupe";
$msgNoDivs     = "Aucune division d�clar�e";
$msgNoPlaces   = "Aucun lieu d�clar�";
$msgtieSchedule  = "La date est obligatoire.";
$msgtiePlace    = "Le lieu est obligatoire.";
$msgtieStep     = "Le tour est obligatoire.";
$msgNoCalendar  = "Calendrier non disponible.";
$msgNoGroups    = "Aucun groupe dans cette division.";
$msgNeedVenue   = "Veuillez pr�ciser la salle.";
$msgNeedDay     = "Veuillez pr�ciser la date.";
$msgNotRealTime = "S�lectionner une heure valide.";
$msgNeedCourt   = "S�lectionner une position dans l'�ch�ancier.";
$msgMatchEnded  = "Il y a des matchs termin�s. La num�rotation automatique a �t� d�sactiv�e.";
$msgNoProblems  = "Aucun conflit d�tect�.";

$msgConvoc = "Utiliser le bouton '$btnStatus' pour positionner l'�tat de tous les joueurs � 'Absent' avec une heure d'arriv�e correspondant � leur heure de convocation. Ces informations apparaitront dans la page 'D�roulement', lors de la gestion du tournoi.";

$msgConfirm1111   = "Effacement du calendrier affich� ?";


?>
