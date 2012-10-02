<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_draw.inc";

// Titre  du navigateur
$draws    = "Draws";

$tDraws       = "Draws";
$itDefinition   = "Declaration";
$itConfection   = "Players/pairs";
$itPdf          = "PDF list";
$itPdfIbf       = "Ibf PDF list";
$itPdfStats     = "Stats PDF";
$itPdfGroups    = "Groups PDF";
$itPdfKo        = "Ko PDF";
$itSelectPairs  = "Select players/pairs";
$itDrawsXls     = "Ko Excel";
$itPairsXls     = "Pairs Excel";
$itStatsXls     = "Stats Excel";

$itAdd    = "Add";
$itRemove = "Remove";
$itReserve = "Reserve";
$itMain    = "Main draw";
$itQualif  = "Qualification";

$itPairs    = "Pairs";
$itExplode  = "Separe";
$itImplode  = "Compose";
$itRank     = "Ranks";
$itNewSerial = "New serial";
$itGroups2Draw = "Groups to Ko";

// Titres des fenetres
$tNewSerial     = "New Serial";
$tEditSerial    = "Edit Serial";
$tFusionSerial  = "Fusion serial";
$tNewDraw       = "Draw creation";
$tEditDraw      = "Draw modification";
$tDelDraws      = "Draw deletion";
$tPubliDraws    = "Draws publication";
$tDrawingLot    = "Drawing lot";

$tDrawDef     = "Caractéristiques";
$tDrawScore   = "Comptage";
$tDrawRecap   = "Matches";

// Titre des colonnes
$rowsDraws1  = "Name";
$rowsDraws2  = "Acronym";
$rowsDraws3  = "Event";
$rowsDraws5  = "Category";
$rowsDraws6  = "Pairs num";
$rowsDraws7  = "Groups";
$rowsDraws8  = "Main draw";

$rowsGroups1  = "Group";
$rowsGroups2  = "Number";
$rowsGroups3  = "Qualifies";
$rowsGroups4  = "Players";
$rowsGroups5  = "Matches";

$rowsRound1   = "Draws";
$rowsRound2   = "Places";
$rowsRound3   = $rowsGroups5;

$rowsPairs1  = "Names";
$rowsPairs2  = "Category";
$rowsPairs2  = "Surclasse";
$rowsPairs4  = "Level";
$rowsPairs5  = "Points";
$rowsPairs4  = "Int. Rank";
$rowsPairs5  = "Nat. Rank";
$rowsPairs6  = "Draw";
$rowsPairs7  = "Round";
$rowsPairs8  = "Teams";

$rowsPairsGroups1  = "Seed";
$rowsPairsGroups2  = "Players/pairs";
$rowsPairsGroups3  = "Team";
$rowsPairsGroups4  = "Level";
$rowsPairsGroups5  = "Points";
$rowsPairsGroups6  = "Nat. rank";
$rowsPairsGroups7  = "Int. rank";

$tiesSchedu1  = "Round";
$tiesSchedu2  = "Date";
$tiesSchedu3  = "Venue";
$tiesSchedu4  = "Court";
$tiesSchedu5  = "Pos";
$tiesSchedu6  = "Visitor";
$tiesSchedu7  = "Num";

// Label des zones
$serialList = "Serials :";
$disciList  = "Disciplines :";
$drawDisciLabel   = "Discipline :";
$drawSerialLabel  = "Serial :";
$drawTypeLabel    = "Type :";
$drawNbmatch      = "Matches number :";
$drawNbSecond     = "Second number :";
$drawNbPairs      = "Players/pairs registered :";
$draw3 = "Group of 3";
$draw4 = "Group of 4";
$draw5 = "Group of 5";
$drawTot = "Sum";
$drawMs = "MS";
$drawWs = "WS";
$drawMd = "MD";
$drawWd = "WD";
$drawXd = "XD";
$criteria  = "Criteria :";
$degres    = "Degres :";
$publiSchedu  = "Publier aussi l'échéancier";
$roundType    = "Type :";
$roundName    = "Name :";
$roundStamp   = "Sigle :";
$roundEntries   = "Number of players :";
$roundPosQualif = "Position qualifié :";

// Messages d'erreur
$msgExistSerial    = "This serial already exists.";
$msgPositif        = "Every number must be positive.";
$msgNoDraw         = "No event now.";
$msgConfirmDel   = "Confirm the deletion of the selected draws.";
$msgNeedDraws    = "You must select a draw.";
$msgNoSerials    = "No serial.";
$msgNoPlayers    = "No players in this draw.";
$msgNoPublic     = "Draw not available.";
$msgConfirmPubDraw = "Le status des tableaux sélectionnés va être modifié.";
$msgNeedPairsInRound  = "Il doit y avoir des joueurs/paires dans les poules pour pouvoir effectuer le tirage au sort.";
$msgNeedPairsInKo     = "Il doit y avoir des joueurs/paires dans le tableau pour pouvoir effectuer le tirage au sort.";

// Label des boutons
$btnModify = "Edit";
$btnSelect  = "Select players/pairs";
$btnPdfDoc  = "PDF";
$btnPdfIbf  = "IBF PDF";
$btnGroups2Draw = "Group to Ko";



?>
