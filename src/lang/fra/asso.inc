<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_asso.inc";
include dirname(__FILE__)."/t_teams.inc";
include dirname(__FILE__)."/t_book.inc";

// Titre  du navigateur
$asso      = "Associations";

// Titres des fenetres
$tAssos        = "Associations enregistrées";
$tNewAsso      = "Création d'une association";
$tEditAsso     = "Modification d'une association";
$tDelAsso      = "Suppression d'une association";
$tRegisterAsso = "Inscrire une association";
$tMembers      = "Inscrits de ce club";
$tAccount      = "Modification du compte";
$tSearchResult = "Recherche d'une association";
$tDiscounts    = "Prises en charge";
$tCredits      = "Provisions";
$tReservations = "Réservations";

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
$rowsSearch4  = "Dép";
$rowsSearch5  = "Type";

$rowsAssocs1  = "Date";
$rowsAssocs2  = "Nom";
$rowsAssocs3  = "Ville";
$rowsAssocs4  = "Type";
$rowsAssocs5  = "Compte";

$rowsRegis1  = "Sexe";
$rowsRegis2  = "Identité";
$rowsRegis3  = "Fonction";
$rowsRegis4  = "Compte";
$rowsRegis5  = "Arrivée";
$rowsRegis6  = "Départ";

// Label des zones
$assoTypeName   = 'Type :';
$assoTypeSearch   = $assoTypeName;
$assoDept         = "Département :";

$blkCriteria = 'Critères de recherche';
$blkAssoc    = 'Association';
$blkTeam     = 'Equipe';

// Label des listes

// Messages d'erreur
$msgNoAssocs        = "Aucune association enregistrée";
$msgNoPlayers       = "Aucun joueur inscrit";
$msgNoReservations  = "Aucune réservation enregistrée pour les articles affichés";
$msgNeedAssocs      = "Sélectionner au moins une association";
$msgTeamWithMember = "Des joueurs sont enregistrés avec une des équipes. Impossible de supprimer!";
$msgNeedMembers   = "Sélectionner au moins un inscrit.";
$msgPurchaseExist = "Cet inscrit a des achats ou des provisions. Impossible de le supprimer!";
$msgNoGridsItems  = "Aucun article sélectionné!";
$msgNoCredits     = "Aucune provision enregistrée!";
$msgConfirmDel    = "Voulez-vous supprimer les inscriptions sélectionnées ?";
$msgNeedCriteria  = "Au moins un des critéres de recherche doit être renseigné.";
$msgNotFound      = "Aucune association trouvée avec à ces critères.";

// Label des boutons
$btnSearch = "Chercher";
$btnNew    = "Nouveau";
$btnAddLogo = "Choix du logo";
$btnAddLogoTeam = $btnAddLogo;
?>
