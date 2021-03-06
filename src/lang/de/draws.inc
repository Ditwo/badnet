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
$itSelectPairs  = "Spieler/Paarung ausw�hlen";
$itXls          = "K-O Excel";

$itAdd    = "Hinzuf�gen";
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
$tDelDraws      = "Feld l�schen";
$tPubliDraws    = "Feld ver�ffentlichen";
$tDrawingLot    = "Auslosung";

$tDrawDef     = "Caract�ristiques";
$tDrawScore   = "Z�hlweise";
$tDrawRecap   = "Spiele";


// Titre des colonnes
$rowsDraws1  = "Name";
$rowsDraws2  = "Abk�rzung";
$rowsDraws3  = "Disziplin";
$rowsDraws5  = "Altersklasse";
$rowsDraws6  = "Startpl�tze";
$rowsDraws7  = "Gruppen";
$rowsDraws8  = "K-O Runde";

$rowsGroups1  = "Gruppe";
$rowsGroups2  = "Anzahl";
$rowsGroups3  = "Qualifikanten";
$rowsGroups4  = "Spieler";
$rowsGroups5  = "Spiele";

$rowsRound1   = "Feld";
$rowsRound2   = "Startpl�tze";
$rowsRound3   = $rowsGroups5;

$rowsPairs1  = "Spieler/Paarungen";
$rowsPairs2  = "Altersklasse";
$rowsPairs3  = "Surclassement";
$rowsPairs4  = "St�rke";
$rowsPairs5  = "Punkte";
$rowsPairs6  = "Int. Ranking";
$rowsPairs7  = "Nat. Ranking";
$rowsPairs8  = "Phase";
$rowsPairs9  = "Entwicklung";
$rowsPairs10  = "Mannschaft";

$rowsPairsGroups1  = "Setzplatz";
$rowsPairsGroups2  = "Spieler/Paarungen";
$rowsPairsGroups3  = "Mannschaft";
$rowsPairsGroups4  = "St�rke";
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
$criteria  = "Crit�re de s�paration :";
$degres    = "Degr� de s�paration :";
$publiSchedu  = "Zeitplan ver�ffentlichen";
$roundType    = "Type :";
$roundName    = "Nom :";
$roundStamp   = "Sigle :";
$roundEntries   = "Anzahl der Spieler :";
$roundPosQualif = "Position qualifi� :";

// Messages d'erreur
$msgExistSerial  = "Diese Spielklasse gibt es schon!";
$msgPositif      = "Die Zahlen m�ssen > 0 sein.";
$msgNoDraw       = "Kein Feld definiert.";
$msgConfirmDel   = "Best�tigen Sie das Entfernen der ausgew�hlten Felder.";
$msgNeedDraws    = "Sie m�ssen wenigstens ein Feld ausw�hlen.";
$msgNoSerials    = "Aucune s�rie d�finie.";
$msgNoPlayers    = "Kein Spieler in diesem Feld.";
$msgNoPublic     = "Feld nicht verf�gbar.";
$msgConfirmPubDraw = "Le status des tableaux s�lectionn�s va �tre modifi�.";
$msgNeedPairsInRound  = "Il doit y avoir des joueurs/paires dans les poules pour pouvoir effectuer le tirage au sort.";
$msgNeedPairsInKo     = "Il doit y avoir des joueurs/paires dans le tableau pour pouvoir effectuer le tirage au sort.";

// Label des boutons
$btnModify  = "Bearbeiten";
$btnSelect  = "Choix des paires";
$btnPdfDoc  = "PDF";
$btnPdfIbf  = "IBF PDF";
$btnGroups2Draw = "Gruppensieger";


?>