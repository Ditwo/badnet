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
$tScheduNum    = "Numérotation des matches";
$calendarDiv   = "Calendrier";
$itSchedu      = "Confection";
$itPdf         = "Document pdf";
$itBlabla      = "Priorités";
$itPlayerPdf   = "Joueurs/salles";
$itNbMatch     = "Matchs par joueur";
$tScheduAuto   = "Plannification automatique";
$tConfirmation = "Confirmation";
$tHour         = "Modification de l'heure";

// Titre des colonnes
$scheList1  = "Groupe";
$scheList2  = "Tour";
$scheList3  = "Lieu - heure";
$scheList4  = "Hôte";
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
$tiesSchedu8  = "Résultat";

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
$firstNum  = 'Premier numéro :';
$numOption1  = 'Numéroter à la suite';
$numOption2  = 'Changer de plage chaque salle';
$numOption3  = 'Changer de plage chaque salle et chaque jour';
$drawList    = "Tableau:";
$nbCourt     = "Nombre de court : ";
$firstDay    = "Premier jour (jj-mm-aa) : ";
$startOne    = "Heure début (hh:mm) : ";
$endOne      = "Heure fin (hh:mm) : ";
$startSecond = "Reprise lendemain (hh:mm) : ";
$venue       = "Salle :";
$newVenue    = "Nouvelle salle :";
$day         = "Date :";
$newDay      = "Nouvelle date (jj-mm-aa) :";
$scheduList  = "Echeancier";
$matchList   = "Matchs";
$propagate   = "Propager la durée aux créneaux suivants";
$convoc      = "Délai de convocation (mn):";
$propagateConvoc   = "Propager le délai de convocation aux créneaux suivants";
$time        = "Heure :";
$length      = "Durée (en mn) :";
$blkConvoc   = "Convocations";
$delayConvoc = "Délai convocation (mn) :";
$lieuConvoc  = "Lieu de convocation :";
$typeConvoc1 = "Heure de match";
$typeConvoc2 = "Début tableau";
$ctacContact = "Référant :";
$ctacValue   = "Email :";
$assoConvoc  = "Message :";
$rest  = "Temps de repos (en mn) :";
$raz   = "Effacer l'échéancier actuel"; 

// Label des boutons
$btnUpdate  = "Enregistrer";
$btnReverse = "Inverser accueil";
$btnPdf     = "Calendrier PDF";
$btnNumber  = "Numéroter";
$btnInsert  = "-- Insérer -->";
$btnReplace = "-- Remplacer -->";
$btnRemove  = "<-- Enlever --";
$btnErase   = "<-- Enlever --";
$btnPause   = "-- Insérer pause -->";
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
$msgNoDivs     = "Aucune division déclarée";
$msgNoPlaces   = "Aucun lieu déclaré";
$msgtieSchedule  = "La date est obligatoire.";
$msgtiePlace    = "Le lieu est obligatoire.";
$msgtieStep     = "Le tour est obligatoire.";
$msgNoCalendar  = "Calendrier non disponible.";
$msgNoGroups    = "Aucun groupe dans cette division.";
$msgNeedVenue   = "Veuillez préciser la salle.";
$msgNeedDay     = "Veuillez préciser la date.";
$msgNotRealTime = "Sélectionner une heure valide.";
$msgNeedCourt   = "Sélectionner une position dans l'échéancier.";
$msgMatchEnded  = "Il y a des matchs terminés. La numérotation automatique a été désactivée.";
$msgNoProblems  = "Aucun conflit détecté.";

$msgConvoc = "Utiliser le bouton '$btnStatus' pour positionner l'état de tous les joueurs à 'Absent' avec une heure d'arrivée correspondant à leur heure de convocation. Ces informations apparaitront dans la page 'Déroulement', lors de la gestion du tournoi.";

$msgConfirm1111   = "Effacement du calendrier affiché ?";


?>
