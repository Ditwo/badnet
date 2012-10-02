<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_draw.inc";

// Titre  du navigateur
$draws       = "Tableaux";

$tDraws       = "Tableaux";
$itDefinition   = "Définition";
$itConfection   = "Joueurs/paires";
$itPdf          = "Imprimer liste";
$itPdfIbf       = "Liste PDF Ibf";
$itPdfStats     = "Imprimer stats";
$itPdfGroups    = "Imprimer poules";
$itPdfKo        = "Imprimer plateaux";
$itSelectPairs   = "Sélectionner paires";
$itSelectPlayers = "Sélectionner joueurs";
$itDrawsXls     = "Plateaux Excel";
$itPairsXls     = "Joueurs Excel";
$itNewGroup     = "Nouvelle phase";
$itNewPlayer    = "Nouveau joueur";
$itModifGroup   = "Modifier phase";
$itStatsXls      = "Stat Excel";

$itAdd    = "Ajouter";
$itRemove = "Enlever";
$itReserve = "Réserve";
$itMain    = "Tableau Final";
$itQualif  = "Qualification";

$itPairs    = "Paires";
$itPlayers  = "Joueurs";
$itExplode  = "Séparer";
$itImplode  = "Réunir";
$itRank     = "Maj clt";
$itNewSerial = "Nouveau";
$itGroups2Draw = "Sortie de poule";
$itClasse      = "Classement";
$itPalma      = "Palmares";
$itDeleteDraw  = "Supprimer tableau";
$itDeleteGroup = "Supprimer phase";
$itDeletePlateau = "Supprimer plateau";

// Titres des fenetres
$tNewSerial     = "Création de tableaux";
$tEditSerial    = "Modification d'une série";
$tFusionSerial  = "Fusion de série";
$tEditDraw      = "Modification d'un tableau";
$tNewDraw       = "Création d'un tableau";
$tDelDraws      = "Suppression de tableau";
$tDelGroups     = "Suppression de phase";
$tDelRound     = "Suppression de plateau";
$tPubliDraws    = "Publication de tableau";
$tDrawingLot    = "Tirage au sort";
$tNewGroup      = "Création d'une phase";
$tEditGroup     = "Modification d'une phase";
$tNewRound      = "Création d'un plateau";

$tDrawDef     = "Caractéristiques";
$tDrawScore   = "Comptage";
$tDrawRecap   = "Matches";


// Titre des colonnes
$rowsDraws1  = "Groupe";
$rowsDraws2  = "Nom";
$rowsDraws3  = "Sigle";
$rowsDraws4  = "Nb places";
$rowsDraws5  = "Nb matchs";
$rowsDraws6  = "Nb qualifiés";

$rowsGroups1  = "Poule";
$rowsGroups2  = "Nombres";
$rowsGroups3  = "Sortants";
$rowsGroups4  = "Joueurs";
$rowsGroups5  = "Matches";

$rowsRound1   = "Tableaux";
$rowsRound2   = "Places";
$rowsRound3   = $rowsGroups5;

$rowsPairs1  = "Joueurs";
$rowsPairs2  = "Catégorie";
$rowsPairs3  = "Surclassement";
$rowsPairs4  = "Niveau";
$rowsPairs5  = "Points";
$rowsPairs6  = "Rang Nat.";
$rowsPairs7  = "Date insc";
$rowsPairs8  = "Phase";
$rowsPairs9  = "Evolution";
$rowsPairs10  = "Equipe";

$rowsPairsGroups1  = "Tds";
$rowsPairsGroups2  = "Joueurs";
$rowsPairsGroups3  = "Equipe";
$rowsPairsGroups4  = "Niveau";
$rowsPairsGroups5  = "Points";
$rowsPairsGroups6  = "Rang Nat.";
$rowsPairsGroups7  = "Rang Int.";

$tiesSchedu1  = "Tour";
$tiesSchedu2  = "Date";
$tiesSchedu3  = "Lieu";
$tiesSchedu4  = "Court";
$tiesSchedu5  = "Pos";
$tiesSchedu6  = "Visiteur";
$tiesSchedu7  = "Num";

// Label des zones
$serialList = "Séries :";
$disciList  = "Disciplines :";
$drawDisciLabel   = "Discipline :";
$drawSerialLabel  = "Série :";
$drawTypeLabel    = "Type :";
$drawNbmatch      = "Nombre total de matchs :";
$drawNbSecond     = "Nb meilleurs second :";
$drawPairs        = "Joueurs/paires inscrits:";
$draw3 = "Poules de 3";
$draw4 = "Poules de 4";
$draw5 = "Poules de 5";
$drawTot = "Totaux";
$drawMs = "SH";
$drawWs = "SD";
$drawMd = "DH";
$drawWd = "DD";
$drawXd = "Mx";
$criteria  = "Critére de séparation :";
$degres    = "Degré de séparation :";
$publiSchedu  = "Publier aussi l'échéancier";
$roundType =  $roundTypeLabel   = "Type :";
$roundName    = "Nom :";
$roundStamp   = "Sigle :";
$roundEntries   = "Nombre de joueurs :";
$roundPosQualif = "Position qualifié :";
$theDraw = "Tableau :";
$group   = "Nom de la phase :";
$groupName = "Nom de la phase :";
$players = " joueurs";
$pairs   = " paires";
$rundFinalPlace = "Place finale du vainqueur :";

// Messages d'erreur
$msgExistSerial  = "Cette série existe déjà.";
$msgPositif      = "Tous les nombres doivent être positifs ou nuls.";
$msgNoDraw       = "Aucun tableau déclaré.";
$msgConfirmDel   = "Confirmez la supression du tableau.";
$msgConfirmDelRound   = "Confirmez la supression du plateau.";
$msgNeedDraws    = "Vous devez sélectionner au moins un tableau.";
$msgNeedGroups   = "Vous devez sélectionner au moins un élément d'une phase.";
$msgNoSerials    = "Aucune série définie.";
$msgNoPlayers    = "Aucun joueur dans ce tableau.";
$msgNoPublic     = "Tableau non disponible.";
$msgConfirmPubDraw = "Le status des tableaux sélectionnés va être modifié.";
$msgNeedPairsInRound  = "Il doit y avoir des joueurs dans les poules pour pouvoir effectuer le tirage au sort.";
$msgNeedPairsInKo     = "Il doit y avoir des joueurs dans le tableau pour pouvoir effectuer le tirage au sort.";
$msggroupname     = "Le nom de la phase est obligatoire";
$msgConfirmDelGroup   = "Confirmez la supression de la phase. Toutes ses poules et ses plateaux seront supprimés.";
$msgGroupExist        = "Cette phase existe déjà. Pour la modifier, utiliser le crayon devant son nom dans la liste des phases du tableau.";
$msgserialName        = "Le nom de la série est obligatoire.";
$msgdrawStamp         = "Le sigle de la série est obligatoire.";
$msggroup         = "Le nom de la phase est obligatoire.";

// Label des boutons
$btnModify  = "Modifier";
$btnSelect  = "Choix des paires";
$btnPdfDoc  = "Imprimer Tableaux";
$btnPdfIbf  = "IBF PDF";
$btnGroups2Draw = "Sortie de poule";
$btnAllDraws = "Tous les tableaux";
$btnNewDraw = "Nouveau tableau";
$btnNewPlateau = "Nouveau plateau";


?>