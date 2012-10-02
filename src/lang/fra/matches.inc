<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_matches.inc";

// Titre  du navigateur
$matches    = "Résultats";

// Titres des fenetres
$tMatches      = "Gestion des matches";
$tNewMatch     = "Création d'un match";
$tEditMatch    = "Modification d'un match";
$tStatusMatch  = "Gestion d'un match";

// titre des blocs
$blkScore = "Score du vainqueur";

// Titre des colonnes
$rowsMatchs1  = "Numéros";
$rowsMatchs2  = "Etats";
$rowsMatchs3  = "Scores";
$rowsMatchs4  = "Equipe A";
$rowsMatchs5  = "Commentaires";
$rowsMatchs6  = "Equipe B";

// Label des zones
$mtchUmpire   = "Arbitre :";
$mtchService  = "Juge de service :";
$mtchCourt    = "Court :";
$mtchDraw     = "Tableau :";
$mtchRound    = "Phase :";
$mtchStep     = "Tour :";


$mtchRegiId00   = "Joueurs(s) :";
$mtchRegiId01   = "&nbsp;";
$mtchRegiId10   = "Contre";
$mtchRegiId11   = "&nbsp;";
$blkSet0       = "Set 1";
$blkSet1       = "Set 2";
$blkSet2       = "Set 3";
$blkSet3       = "Set 4";
$blkSet4       = "Set 5";
$mtchWo         = "WO";
$mtchAbort      = "Abandon";
$mtchDiscipline = "Discipline";
$mtchOrder      = "N° :";
$pairIdUn      = "Equipe A";
$pairIdDeux    = "Equipe B";
$updateDraw    = "Mettre à jour le classement de la poule; avancer l'équipe gagnante de la rencontre dans le tableaux";
$luckyLooser   = "Lucky looser";

/* Messages d'erreur */
$msgmtchRegiId00 = "Sélectionner les joueurs !";
$msgmtchRegiId01 = $msgmtchRegiId00;
$msgmtchRegiId10 = $msgmtchRegiId00;
$msgmtchRegiId11 = $msgmtchRegiId00;
$msgWinNotWO      = "Le vainqueur ne peut pas être WO !";
$msgNotSamePlayer = "Une paire doit avoir deux joueurs différents";

// Label des boutons
$btnAddPlayer0  = "Ajout joueur";
$btnAddPlayer1  = $btnAddPlayer0;
$btnPlay  = "Lancer le match";
$btnStop  = "Stopper le match";
$btnRaz = "Annuler résultat";

?>
