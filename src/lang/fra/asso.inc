<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_asso.inc";
include dirname(__FILE__)."/t_teams.inc";
include dirname(__FILE__)."/t_book.inc";

// Titre  du navigateur
$asso      = "Associations";

// Titres des fenetres
$tAssos        = "Associations enregistr�es";
$tNewAsso      = "Cr�ation d'une association";
$tEditAsso     = "Modification d'une association";
$tDelAsso      = "Suppression d'une association";
$tRegisterAsso = "Inscrire une association";
$tMembers      = "Inscrits de ce club";
$tAccount      = "Modification du compte";
$tSearchResult = "Recherche d'une association";
$tDiscounts    = "Prises en charge";
$tCredits      = "Provisions";
$tReservations = "R�servations";

// Menus 
$itUpdate         = 'CPPP FFBa';
$itUpdateSept     = 'CPPP FFBa 01/09';
$itNewPlayer      = 'Nouveau joueur';
$itNewOfficial    = 'Nouvel officiel';
$itNewOther       = 'Autre';

// Titre des colonnes
$rowsSearch1  = "Nom";
$rowsSearch2  = "Ville";
$rowsSearch3  = "Sigle";
$rowsSearch4  = "D�p";
$rowsSearch5  = "Type";

$rowsAssocs1  = "Date";
$rowsAssocs2  = "Nom";
$rowsAssocs3  = "Ville";
$rowsAssocs4  = "Type";
$rowsAssocs5  = "Compte";

$rowsRegis1  = "Sexe";
$rowsRegis2  = "Identit�";
$rowsRegis3  = "Fonction";
$rowsRegis4  = "Compte";
$rowsRegis5  = "Arriv�e";
$rowsRegis6  = "D�part";

// Label des zones
$assoTypeName   = 'Type :';
$assoTypeSearch   = $assoTypeName;
$assoDept         = "D�partement :";

$blkCriteria = 'Crit�res de recherche';
$blkAssoc    = 'Association';
$blkTeam     = 'Equipe';

// Label des listes

// Messages d'erreur
$msgNoAssocs        = "Aucune association enregistr�e";
$msgNoPlayers       = "Aucun joueur inscrit";
$msgNoReservations  = "Aucune r�servation enregistr�e pour les articles affich�s";
$msgNeedAssocs      = "S�lectionner au moins une association";
$msgTeamWithMember = "Des joueurs sont enregistr�s avec une des �quipes. Impossible de supprimer!";
$msgNeedMembers   = "S�lectionner au moins un inscrit.";
$msgPurchaseExist = "Cet inscrit a des achats ou des provisions. Impossible de le supprimer!";
$msgNoGridsItems  = "Aucun article s�lectionn�!";
$msgNoCredits     = "Aucune provision enregistr�e!";
$msgConfirmDel    = "Voulez-vous supprimer les inscriptions s�lectionn�es ?";
$msgNeedCriteria  = "Au moins un des crit�res de recherche doit �tre renseign�.";
$msgNotFound      = "Aucune association trouv�e avec � ces crit�res.";

// Label des boutons
$btnSearch = "Chercher";
$btnNew    = "Nouveau";
$btnAddLogo = "Choix du logo";
$btnAddLogoTeam = $btnAddLogo;
?>
