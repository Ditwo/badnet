<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_teams.inc";
include dirname(__FILE__)."/t_asso.inc";

// Titre  du navigateur
$teams  = "Equipes";

// Titres des fenetres
$tNewTeam      = "Création d'une équipe";
$tEditTeam     = "Modification d'une équipe";
$tEraseTeams   = "Suppression d'équipes";
$tPubliTeams   = "Publication d'équipes";
$tMergePlayers = "Fusion de joueur";
$tMembers      = "Inscrits";
$tBilan        = "Synthèse";

// Menus 
$itNewPlayer      = 'Nouveau joueur';
$itNewOfficial    = 'Nouvel officiel';
$itNewOther       = 'Autre';
$itUpdate         = 'Maj clt.';
$itUpdateSept     = 'CPPP FFBa 01/09';
$itPdfPlayers     = 'Imprimer convocations';
$itPdfPairs       = 'Imprimer paires';
$itPdfEntries     = 'Imprimer inscriptions';
$itXlsEntries     = 'Joueurs Xls';

$itMembers       = "Membres";
$itCredits       = "Buvette";
$itReservation   = "Réservations";
$itDiscount      = "Prise en charge";

$itNewResa        = 'Réserver';
$itDiscount       = 'Prise en charge';
$itSelectItems    = 'Articles affichés';
$itCredits        = 'Provisions';
$itSolder         = 'Solder';

$itDetailM   = 'Par Joueur';
$itDetailD   = 'Par date';
$itDetailI   = 'Par article';
$itHotel     = 'Hôtels';

// Titre des colonnes
$rowsTeams1  = "Ville";
$rowsTeams2  = "Equipe";
$rowsTeams3  = "Sigle";
$rowsTeams4  = "Date";
$rowsTeams5  = "Nombre";

$rowsTies1  = "Lieu";
$rowsTies2  = "Date";
$rowsTies3  = "Tour";
$rowsTies4  = "Adversaire";
$rowsTies5  = "Résultat";
$rowsTies6  = "Score";

$rowsPlayers_V1 = "Genre";
$rowsPlayers_V2 = "NOM Prénom";
$rowsPlayers_V3 = "Classement";

$rowsPairs1 = "Joueurs/Paires";
$rowsPairs2 = "Tableau";
$rowsPairs3 = "Niveau";
$rowsPairs4 = "Points";
$rowsPairs5 = "Rang int.";
$rowsPairs6 = "Rang nat.";
$rowsPairs7 = "Stade";

$rowsPlayers1  = "NOM Prénom";
$rowsPlayers2  = "Genre";
$rowsPlayers3  = "Catègorie";
$rowsPlayers4  = "Classement";
$rowsPlayers5  = "Rang";
$rowsPlayers6  = "Titulaire";
$rowsPlayers7  = "Licence";
$rowsPlayers8  = "NOC";
$rowsPlayers9  = "Premier match";
$rowsPlayers10  = "Convocation";
$rowsPlayers11  = "Lieu";

$rowsRoom1  = "Genre";
$rowsRoom2  = "NOM Prénom";
$rowsRoom3  = "Hotel";
$rowsRoom4  = "Type";
$rowsRoom5  = "Chambre";
$rowsRoom6  = "Arrivée";
$rowsRoom7  = "Nb nuit";

$rowsOffos1  = "NOM Prénom";
$rowsOffos2  = "Genre";
$rowsOffos3  = "Status";
$rowsOffos4  = "Fonction";
$rowsOffos5  = "NOC";

$rowsAdmRegis1  = "Genre";
$rowsAdmRegis2  = "Identité";
$rowsAdmRegis3  = "Fonction";
$rowsAdmRegis4  = "Cout";
$rowsAdmRegis5  = "Remise";
$rowsAdmRegis6  = "Payé";
$rowsAdmRegis7  = "Solde";

$rowsAdmTeams1  = "Date";
$rowsAdmTeams2  = "Zone géo";
$rowsAdmTeams3  = "Equipe";
$rowsAdmTeams4  = "Coût";
$rowsAdmTeams5  = "Remise";
$rowsAdmTeams6  = "Payé";
$rowsAdmTeams7  = "Solde";

$rowsPurchase1  = "Date";
$rowsPurchase2  = "Achat";
$rowsPurchase3  = "Identité";
$rowsPurchase4  = "Coût";
$rowsPurchase5  = "Remise";
$rowsPurchase6  = "Payé";
$rowsPurchase7  = "Restant du";

$rowsCredits1 = "Date";
$rowsCredits2 = "Qui";
$rowsCredits3 = "Fonction";
$rowsCredits4 = "Montant";
$rowsCredits5 = "Reste";
$rowsCredits6 = "Remboursable";

$rowsSend1 = "Club";
$rowsSend2 = "Contact";
$rowsSend3 = "Email";
$rowsSend4 = "Résultat";


// Label des zones
$assoUrl     = "Site Web :";
$assoTeam    = "Association :";
$assoCount   = "Compte :";
$publiPropa  = "Propager aux inscrits des équipes";

$assoCount     = 'Compte :';
$accountName   = "Ancien compte :";
$accountId     = "Nouveau compte :";
$resaDate      = "Date de réservation :";

$costTeam     = "Total achats :";
$discountTeam = "Remise :";
$payedTeam    = "Payé :";
$duTeam       = "Restant du :";

$teamText   = "Convocation :";

// Label des boutons
$btnAddAssoc = "Créer association";
$btnAddLogo  = "Choix du logo";
$btnAddPhoto = "Choix de la photo";
$btnMoreClub = "Nouveau club";

// Label des messages
$msgNoPlayers     = "Pas de joueur dans cette équipe.";
$msgNoPairs       = "Pas de joueurs/paires dans cette équipe.";
$msgNoTies        = "Pas de rencontre pour cette équipe.";
$msgNoTeams       = "Aucune équipe engagée.";
$msgassoId        = "L'association est obligatoire.";
$msgNoSelectItems = "Aucun article publié.";
$msgNoCredits     = "Aucun crédit aloué.";
$msgNoReservations = "Aucune réservation.";
$msgConfirmDelTeam = "Les équipes sélectionnées vont être supprimées.";
$msgConfirmPubTeam = "La publication des équipes sélectionnées va être modifiée.";
$msgNeedTeams      = "Vous devez sélectionner au moins une équipe";
$msgTeamWithMember = "Des joueurs sont enregistrés avec une des équipes. Impossible de supprimer!";
$msgNoOffos = "Aucun officiel dans cette équipe.";
$msgNoTies = "Aucune rencontre pour cette équipe.";
$msgNoTeamLogo   = "Pas de logo pour cette équipe.<br />Utilisation du logo de son association.";
$msgNeedTwoPlayers  = "Vous devez sélectionner 2 joueurs.";
$msgNeedTwoPlayers  = "Vous devez sélectionner 2 joueurs.";
$msgLicenceDiffer   = "Les joueurs doivent avoir le même numéro de licence.";
?>