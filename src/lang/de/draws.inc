<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_draw.inc";

// Titre  du navigateur
$draws       = "Hauptfelder";

$tDraws       = "Felder";
$itDefinition   = "Einstellungen";
$itConfection   = "Spieler/Paarungen";
$itPdf          = "Liste PDF";
$itPdfIbf       = "Liste PDF Ibf";
$itPdfStats     = "Statistik PDF";
$itPdfGroups    = "Gruppen PDF";
$itPdfKo        = "K-O PDF";
$itSelectPairs  = "Spieler/Paarung auswählen";
$itXls          = "K-O Excel";

$itAdd    = "Hinzufügen";
$itRemove = "Entfernen";
$itReserve = "Ersatz";
$itMain    = "K-O Feld";
$itQualif  = "Qualifikation";

$itPairs    = "Spieler/Paarungen";
$itExplode  = "Trennen";
$itImplode  = "Vereinen";
$itRank     = "CPPP FFba";
$itNewSerial = "Neue Spielklasse";
$itGroups2Draw = "Qualifikanten pro Gruppe";

// Titres des fenetres
$tNewSerial     = "Spielklasse erstellen";
$tEditSerial    = "Spielklasse bearbeiten";
$tFusionSerial  = "Spielklassen vereinen";
$tEditDraw      = "Feld bearbeiten";
$tNewDraw       = "Feld erstellen";
$tDelDraws      = "Feld löschen";
$tPubliDraws    = "Feld veröffentlichen";
$tDrawingLot    = "Auslosung";

$tDrawDef     = "Caractéristiques";
$tDrawScore   = "Zählweise";
$tDrawRecap   = "Spiele";


// Titre des colonnes
$rowsDraws1  = "Name";
$rowsDraws2  = "Abkürzung";
$rowsDraws3  = "Disziplin";
$rowsDraws5  = "Altersklasse";
$rowsDraws6  = "Startplätze";
$rowsDraws7  = "Gruppen";
$rowsDraws8  = "K-O Runde";

$rowsGroups1  = "Gruppe";
$rowsGroups2  = "Anzahl";
$rowsGroups3  = "Qualifikanten";
$rowsGroups4  = "Spieler";
$rowsGroups5  = "Spiele";

$rowsRound1   = "Feld";
$rowsRound2   = "Startplätze";
$rowsRound3   = $rowsGroups5;

$rowsPairs1  = "Spieler/Paarungen";
$rowsPairs2  = "Altersklasse";
$rowsPairs3  = "Surclassement";
$rowsPairs4  = "Stärke";
$rowsPairs5  = "Punkte";
$rowsPairs6  = "Int. Ranking";
$rowsPairs7  = "Nat. Ranking";
$rowsPairs8  = "Phase";
$rowsPairs9  = "Entwicklung";
$rowsPairs10  = "Mannschaft";

$rowsPairsGroups1  = "Setzplatz";
$rowsPairsGroups2  = "Spieler/Paarungen";
$rowsPairsGroups3  = "Mannschaft";
$rowsPairsGroups4  = "Stärke";
$rowsPairsGroups5  = "Punkte";
$rowsPairsGroups6  = "Nat. Ranking";
$rowsPairsGroups7  = "Int. Ranking";

$tiesSchedu1  = "Runde";
$tiesSchedu2  = "Datum";
$tiesSchedu3  = "Ort";
$tiesSchedu4  = "Spielfeld";
$tiesSchedu5  = "Pos";
$tiesSchedu6  = "Visiteur";
$tiesSchedu7  = "Spielnummer";

// Label des zones
$serialList = "Klasse :";
$disciList  = "Disziplinen :";
$drawDisciLabel   = "Disziplin :";
$drawSerialLabel  = "Klasse :";
$drawTypeLabel    = "Type :";
$drawNbmatch      = "GesamtNombre total de matchs :";
$drawNbSecond     = "Anzahl bester zweiter :";
$drawNbPairs      = "Joueurs/paires inscrits:";
$draw3 = "3er Gruppe";
$draw4 = "4er Gruppe";
$draw5 = "5er Gruppe";
$drawTot = "Totaux";
$drawMs = "HE";
$drawWs = "DE";
$drawMd = "HD";
$drawWd = "DD";
$drawXd = "Mx";
$criteria  = "Critére de séparation :";
$degres    = "Degré de séparation :";
$publiSchedu  = "Zeitplan veröffentlichen";
$roundType    = "Type :";
$roundName    = "Nom :";
$roundStamp   = "Sigle :";
$roundEntries   = "Anzahl der Spieler :";
$roundPosQualif = "Position qualifié :";

// Messages d'erreur
$msgExistSerial  = "Diese Spielklasse gibt es schon!";
$msgPositif      = "Die Zahlen müssen > 0 sein.";
$msgNoDraw       = "Kein Feld definiert.";
$msgConfirmDel   = "Bestätigen Sie das Entfernen der ausgewählten Felder.";
$msgNeedDraws    = "Sie müssen wenigstens ein Feld auswählen.";
$msgNoSerials    = "Aucune série définie.";
$msgNoPlayers    = "Kein Spieler in diesem Feld.";
$msgNoPublic     = "Feld nicht verfügbar.";
$msgConfirmPubDraw = "Le status des tableaux sélectionnés va être modifié.";
$msgNeedPairsInRound  = "Il doit y avoir des joueurs/paires dans les poules pour pouvoir effectuer le tirage au sort.";
$msgNeedPairsInKo     = "Il doit y avoir des joueurs/paires dans le tableau pour pouvoir effectuer le tirage au sort.";

// Label des boutons
$btnModify  = "Bearbeiten";
$btnSelect  = "Choix des paires";
$btnPdfDoc  = "PDF";
$btnPdfIbf  = "IBF PDF";
$btnGroups2Draw = "Gruppensieger";


?>