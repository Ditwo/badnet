<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$matches    = "En direct";

// Titres des fenetres
$tOrderMatch    = "Ordre des matchs";
$tUmpireCourt   = "Gestion des arbitres";
$tUmpireEdit    = "Affectation d'un arbitre";
$tOptionEdit    = "Options d'affichage";
$itList      = "Matches";
$itLive      = "En direct";
$itStream    = "Vid�o";
$itPdfList   = "Feuilles de score";
$itPdfListLite = "Feuilles de score simplifi�es";
$itPalma       = "Palmares";
$itValid       = "Valider";
$itAnalyse     = "Analyse";
$itPriorite    = "Priorit�";
$itClasse      = "Classement";
$itSchedu      = "Ech�ancier";

// titre des blocs
$blkUmpiring = "Arbitrage";

// Titre des colonnes
$rowOrder1  = "ordre";
$rowOrder2  = "Discipline";

$rowsMatchs4  = "Etat";
$rowsMatchs5  = "Court";
$rowsMatchs6  = "Score";
$rowsMatchs7  = "Dur�e";

$rowsControl1  = "Ordre";
$rowsControl2  = "Match";
$rowsControl3  = "Equipe A";
$rowsControl4  = "Equipe B";
$rowsControl5  = "Court";
$rowsControl6  = "D�but";
$rowsControl7  = "Fin";
$rowsControl8  = "Score";

$rowUmpires1  = "NOM Pr�nom";
$rowUmpires2  = "Terrain";
$rowUmpires3  = "Fonction";
$rowUmpires4  = "Rang";
$rowUmpires5  = "Actif";
$rowUmpires6  = "Actuel";

$rowsList1  = "Num";
$rowsList2  = "Tableau";
$rowsList3  = "Tour";
$rowsList4  = "Joueur/Paire A";
$rowsList5  = "Joueur/Paire B";
$rowsList6  = "Court";
$rowsList7  = "D�but";
$rowsList8  = "Fin";
$rowsList9  = "Score";


// Label des zones
$reload  = "Rafraichissement :";
$dateList  = "Date :";
$placeList = "Lieu :";
$stepList  = "Tour :";
$courtNumber  = "Terrain :";
$status1  = "Gagnant";
$status2  = "Perdant";
$status3  = "Officiel";
$status4  = "Auto-arbitrage";
$umpiring      = "Arbitrage :";
$umpire        = "Arbitre :";
$umpireAffected   = "Affecter un arbitre sur ce match";
$serviceJudge  = "Juge de service :";
$training      = "Echauffement (en mn) :";
$rest           = "Repos (en mn) :";
$avg            = "Dur�e moyenne :";
$nbMatchPlayer  = "Nb max match :";
$printGroupList = "Imprimer poules :";
$printKoList    = "Imprimer tableaux :";
$group2ko       = "Sortie de poule :";
$date    = "Date :";
$numPage    = "Page :";
$printRankList  = "Classement :";
$printKoList    = "Imprimer tableaux :";
$chkPrint       = 'Imprimer la feuille de score';
//$blkOfficial   = "Choix arbitre";

$teamDeclaration = "Formulaire de d�claration des �quipes (PDF)";
$teamEdit        = "Saisie d�claration et r�sultats";
$teamResults     = "R�sultat de la rencontre (PDF)";
$editSchedule    = "Horaire et terrain";
$matchEdit       = "Equipes et r�sultat du match";
$scoresheetEdit  = "Feuille de score du match";
$matchOrder      = "Ordre des matchs";
$nbMatch         = "Matches affich�s :";
$nbCourt         = "Nombre de terrains :";

$levelTrace    = "Niveau de trace :";
$fontList      = "Police de caract�res :";
$courtNumberLive  = "Afficher terrrain";
$courtNumberClose = "Fermer terrrain";

$umpireFunction = "Prochaine fonction :";
$umpireRank     = "Ordre dans la fonction :";


// Messages d'erreur 
$msgNoTies = "Pas de match aujourd'hui. Consultez le calendrier.";
$msgNoDate = "Aucune date d�finie";
$msgNotie_place = "Aucun lieu d�fini";
$msgNotie_step  = "Aucun tour d�fini";
$msgNoLive = "Pas de score en direct pour ce tournoi.";
$msgNoStream = "Pas de video en direct pour ce tournoi.";
$msgNoLiveNow = "Aucun match en cours. Revenez plus tard";

$msgOnlyIE  = "Il n'existe pas de plugin pour les navigateurs autre que Internet Explorer.";
$msgWinamp = "Si vous n'avez pas IE, ou si vous �tes allergique, vous pouvez utiliser Winamp.";
$msgVLC    = "Vous pouvez aussi essayer (et adopter) VideoLan VLC.";
$msgLink   = "Dans ces deux cas utilisez l'adresse ci-dessous.";

// Label des boutons
$btnUp   = "Monter";
$btnDown = "Descendre";
$btnEnd  = "Terminer";
$btnMagic = "Proposer ordre";
$btnInitServer = "D�marrer live";
$btnUmpire     = "Arbitres";
$btnOptions    = "Options";
$btnDisplayEnded = "Montrer les matchs termin�s";
$btnMaskEnded    = "Masquer les matchs termin�s";
$btnOrderTime    = "Classer par date";
$btnOrderTie     = "Classer par court";

// Legende
$lgdAbsent  = "Absent";
$lgdPlay    = "Joue";
$lgdUmpire  = "Arbitre";
$lgdRest    = "Repos";

?>
