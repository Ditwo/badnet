<?php
include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/database.inc";
include dirname(__FILE__)."/t_mber.inc";
include dirname(__FILE__)."/t_regi.inc";

// Titre  du navigateur
$regi    = "Inscriptions";

// Titres des fenetres
$tNewRegPlayer       = "Inscription d'un joueur";
$tNewRegOfficial     = "Inscription d'un officiel";
$tNewRegistration    = "Nouvelle inscription";
$tEditRegistration   = "Modification d'une inscription";
$tDelRegistration    = "Suppression d'une inscription";
$tSearchPlayer       = 'Recherche de joueurs';
$tSearchFedePlayer   = 'Connexion au site federal en cours....';
$tSearchIbfPlayer    = 'Connexion au site international en cours...';
$tSelectFees         = "G�n�ration des �critures";

$itPurchases   = "Achats";
$itCredits     = "Provisions";
$itPdf         = "Imprimer liste";
$itFees        = "Inscription";
$itHotel       = "H�bergement";
$itItems       = "Achat";
$itSolder      = 'Solder';
$itArrivals    = 'Arriv�es';
$itDepartures  = 'D�parts';
$itAbsent      = 'Absent';
$itPresent     = 'Present';
$itXlsEntries  = 'Joueur Xls';
$itClt  = 'Maj Clt.';

// Titre  des menus
$itRegisterMember = "Nouvelle inscription";

// titre des blocs
$blkAdmi  = "Etat civil";
$blkRegi  = "Inscription";
$blkCriteria   = "Crit�res de recherche";

// Titre des colonnes
$rowsTeams1  = "Nom";
$rowsTeams2  = "Sigle";
$rowsTeams3  = "Association";
$rowsTeams4  = "Capitaine";

$rowsAssos1  = "Nom";
$rowsAssos2  = "Ville";
$rowsAssos3  = "Site";

$rowsRegiTeam1  = "Date d'inscription";
$rowsRegiTeam2  = 'Genre';
$rowsRegiTeam3  = 'Joueur';
$rowsRegiTeam4  = 'Classement';
$rowsRegiTeam5  = "Licence";
$rowsRegiTeam6  = "Num�ro Ibf";
$rowsRegiTeam7  = "Equipe";

$rowsRegiV1  = "Genre";
$rowsRegiV2  = "NOM Pr�nom";
$rowsRegiV3  = "Pays";
$rowsRegiV4  = "Equipe";
$rowsRegiV5  = "Equipe";

$rowsRegis2  = "Photo";
$rowsRegis3  = "Genre";
$rowsRegis4  = "NOM Pr�nom";
$rowsRegis5  = 'Cat�gorie';
$rowsRegis6  = "Classement";
$rowsRegis7  = "Licence";
$rowsRegis8  = "Rang";
$rowsRegis9  = "Equipe";

$rowsPlayers1  = "Photo";
$rowsPlayers2  = "Genre";
$rowsPlayers3  = "Nom";
$rowsPlayers3  = "Pr�nom";
$rowsPlayers4  = "Licence";
$rowsPlayers5  = "N�(e) le";
$rowsPlayers6  = "Equipe";
$rowsPlayers7  = "Dep";
$rowsPlayers8  = "Simple";
$rowsPlayers9  = "Double";
$rowsPlayers10  = "Mixte";

$rowsFind1  = "Genre";
$rowsFind2  = "Nom";
$rowsFind3  = "Pr�nom";
$rowsFind4  = "Licence";
$rowsFind5  = "Cat�gorie";
$rowsFind6  = "Club";


$rowsAdmRegis1  = "Genre";
$rowsAdmRegis2  = "Identit�";
$rowsAdmRegis3  = "Fonction";
$rowsAdmRegis4  = "Compte";
$rowsAdmRegis5  = "Achat";
$rowsAdmRegis6  = "Remise";
$rowsAdmRegis7  = "Pay�";
$rowsAdmRegis8  = "Solde";
$rowsAdmRegis10  = "Simple";
$rowsAdmRegis11  = "Double";
$rowsAdmRegis12  = "Mixte";

$rowsPurchase1  = "Date";
$rowsPurchase2  = "Achat";
$rowsPurchase3  = "Compte";
$rowsPurchase4  = "Cout";
$rowsPurchase5  = "Remise";
$rowsPurchase6  = "Pay�";
$rowsPurchase7  = "Solde";

$rowsCredit1  = "Date";
$rowsCredit2  = "Achat";
$rowsCredit3  = "Compte";
$rowsCredit4  = "Initial";
$rowsCredit5  = "Reste";
$rowsCredit6  = "Remboursable";

$rowsTransport1  = "Genre";
$rowsTransport2  = "NOM Pr�nom";
$rowsTransport3  = "Status";
$rowsTransport4  = "Arriv�e";
$rowsTransport5  = "Lieu";
$rowsTransport6  = "D�part";
$rowsTransport7  = "Lieu";

// Label des zones
$regiAccount = "Compte :";
$regiTeam    = "Equipe :";
$mberSexeLabel  = "Genre :";
$noc            = "Code NOC :";
$rankRankS      = "Simple :";
$rankRankD      = "Double :";
$rankRankM      = "Mixte :";
$rankAverageS   = "Simple :";
$rankAverageD   = "Double :";
$rankAverageM   = "Mixte :";
$club           = "Sigle club :";
$departement    = "D�partement :";
$licencie       = "Joueurs non licenci�s";
$septembre      = "CPPP septembre";
$single      = "Simple :";
$double      = "Double :";
$mixed       = "Mixte  :";
$inscOne     = "Un tableau :";
$inscTwo     = "Deux tableaux :";
$inscThree   = "Trois tableaux :";
$regiCatage  = "Cat�gorie :";
$assoId      = "Club :";
$assoDpt     = "D�partement :";
$regiCatageLabel = "Cat�gorie:";
$regiSurclasseLabel = "Surclassement :";

$searchType1  = "Base f�d�rale";
$searchType2  = "Base locale";

$sourceS = "Simple : modifi� le";
$sourceD = "Double : modifi� le";
$sourceM = "Mixte : modifi� le";
$sourceSF = "Simple: source f�d� du";
$sourceDF = "Double : source f�d� du";
$sourceMF = "Mixte : source f�d� du";

$Page   = "Page � afficher :";
$Nombre = "Nombre de joueurs par page :";
$Total  = "Nombre total de joueurs :";

$memberSolde     = "Solde de l'inscrit :";
$accountSolde    = "Solde du compte :";


// Label des listes

// Messages d'erreur
$msgNeedLicence     = "Seuls les joueurs avec un num�ro de licence sont trait�s.";
$msgNeedPlayers     = "Vous devez s�lectionner au moins un joueur.";
$msgNotFound    = "Aucun joueur trouv� avec ces crit�res !";
$msgNoRegisteredPlayer = "Pas de joueur inscrit pour le moment !";
$msgNeedCriteria   = "Renseigner au moins le nom (au moins 3 caract�res),<br> le num�ro de licence ou le club.";
$msgTeamNeedClub      = "Inscription non trouv�e. V�rifier l'association de l'�quipe du joueur";
$msgRegiPlay   = "Un joueur est inscrit dans des matchs. Impossible de supprimer son inscription.";
$msgTooMuchPlayers = "Trop de joueurs correspondent � vos crit�res. Veuiller affiner votre recherche.";
$msgNoTeams = "Aucune �quipe enregistr�e";
$msgNeedRegistration    = "Vous devez s�lectionner au moins un inscrit.";
$msgConfirmDel         = "Voulez-vous supprimer les inscriptions s�lectionn�es ?";
$msgRegiCommands = "Un inscrit a des achats. Impossible de supprimer son inscription.";
$msgNoRegisteredOfficial = "Aucun officiel enregistr�";
$msgNoPurchases = "Pas d'achat pour cet incrit.";
$msgNoAccount   = "Pas de compte pour cet incrit. Impossible de g�rer ses achats.";
$msgNoCredit    = "Pas de provision pour cet inscrit";
$msgPairInMatch = "Changement de tableau impossible. Le joueur est dans des matchs;enlevez-le de sa poule ou tableau.";
$msgPoonaError  = "Acc�s base f�d�rale impossible.";

// Label des boutons
$btnAddMber     = "Cr�er un membre";
$btnValidate    = "Enregistrer";
$btnSearchFede  = "Chercher FFBa";
$btnSearchIbf   = "Chercher IBF";
$btnAddPhoto    = "Choix photo";
$btnGo          = "Afficher";
$btnPrev        = "Pr�c�dent";
$btnNext        = "Suivant";

?>