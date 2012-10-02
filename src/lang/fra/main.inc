<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/t_user.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$main = "Accueil";

// Titre  des fenetres
$tSendMail  = "Contacter l'administrateur";
$tEditParam = "Modification des paramêtres";
$tSubscriptions = "Vos abonnements";
$tEvents        = "Les tournois et vos droits";
$tBooks         = "Les carnets d'adresse et vos droits";
$emailTitle     = "Courrier électronique";
$contactTitle   = "Contacts externes";
$decoTitle      = "Décorations";
$databaseTitle  = "Base de données";
$liveTitle      = "Score en live";
$synchroTitle   = "BadNet distant";
$divEmail    = "Emails";
$divContact  = "Contacts";
$divDeco     = "Decorations";
$divDatabase  = "Base de données";

// Menus
$itNewEvent = "Créer un tournoi";
$itNewBook  = "Créer un carnet d'adresse";

// Label des zones
$user_name = "Bienvenue ";
$Administrators = "Administrateurs :";
$Events  = "Tournois :";
$Books   = "Carnets d'adresses :";
$Users   = "Utilisateurs :";
$from    = "De :";
$to      = "à :";
$subject = "Sujet :";
$message = "Message :";

$host = "Serveur SMTP :";
$auth = "Authentification";
$port = "Port de connexion :";
$userLog   = "Utilisateur :";
$password  = "Mot de passe :";
$ffbaEmail = "Email FFBa :";
$ffbaUrl = "Url fiche joueurs nationaux :";
$ibfUrl  = "Url fiche joueurs internationaux :";
$mainTitle = "Titre :";
$subTitle  = "Sous-titre :";
$footer  = "Pied de page pdf :";
$emailType = "Type de serveur :";
$skin      = "Skin par défaut :";
$language  = "Langue par défaut :";
$emailType1 = "Smtp";
$emailType2 = "Mail";
$sport1 = "Squash";
$sport2 = "Badminton";
$prefixe = "Préfixe des tables :";
$version = "Version de la base :";
$softVersion = "Version du logiciel :";
$baseid = "Identifiant de la base :";
$sport = "Sport :";

$liveIp = "Adresse ip du facteur :";
$livePort = "Port du facteur :";
$ftpHost = "Host du serveur FTP :";
$ftpPort = "Port du serveur FTP :";
$ftpUser = "Login du serveur FTP :";
$ftpPwd  = "Mot de passe  :";
$ftpDir  = "Dossier distant :";
$isLiveLight = "Live sans image";

$synchroUrl  = "Url BadNet :";
$synchroUser = "Utilisateur BadNet :";
$synchroPwd  = "Mot de passe :";
$synchroEvent = "Id tournoi distant :";


// Titre des colonnes
$Events1  = "Tournois";
$Events2  = "Droit";

$Subscripts   = "Abonnements aux résultats";
$Subscripts1  = $Events1;
$Subscripts2  = "Equipes";
$Subscripts3  = "Email";

$Books   = "Carnets d'adresse";
$Books1  = "Nom";
$Books2  = "Droit";

// Legende des bloc
$blkUser = "Informations";
$blkLegRight = "Droits";
$blkLegPubl  = "Publication";

// Label des boutons
$btnUser  = "Modifier";
$btnPwd   = "Mot de passe";
$btnMail  = "Envoyer";
$btnParam = "Paramétrage B@dNet";
$btnUsers = "Gestion des utilisateurs";

// Messages
$msgNoEvents = "Aucun tournoi disponible";
$msgNoBooks  = "Aucun carnet d'adresse disponible";
$msgNoSubscribe  = "Aucun abonnemment aux résultats";
$msgLiveLight    = "Live avec image (plus beau mais trafic important)";
$msgLiveNoLight  = "Live sans image (moins beau mais trafic léger)";

$hlp1 = "-Sélectionner un administrateur dans la liste pour lui envoyer un email";
$hlp2 = "-Sélectionner un tournoi ou un carnet d'adresse dans la liste adequate pour le consulter";

$intro = "Voici quelques notions importantes qui vous aiderons à mieux utiliser Badminton Netware.";

$blkInfData = "Les données";

$visible = "Les données PUBLIEES";
$infVisible = "Les données PUBLIEES : Elles sont visibles par tout utilisateur connecté.";

$hidden  = "Les données NON PUBLIEES";
$infHidden = "Les données NON PUBLIEES : Ce sont des informations en attente d'être publiées (par exemple les tableaux ou les horaires des match)";

$private = "Les données PRIVEES";
$infPrivate = "Les données PRIVEES : Elles sont confidentielles. Elles ne sont pas destinées à être publiées (par exemple les données financières).";

$delete = "Les données EFFACEES";
$infDelete = "Les données EFFACEES : Certaines données peuvent être supprimées logiquement. Elles existent toujours mais seul un administrateur peut les voir et les restaurer.";

$blkInfUser = "Les utilisateurs";
$admin = "Les ADMINISTRATEURS";
$infAdmin = "Les ADMINISTRATEURS peuvent consulter et modifier toutes les données. Ils sont les seuls à pouvoir modifier les paramêtres du logiciel et les seuls à avoir accés aux fonctions de gestion des utilisateurs.";

$manager = "Les GESTIONNAIRES";
$infManager = "Les GESTIONNAIRES peuvent consulter et modifier toutes les données sauf les utilisateurs et les paramêtres du logiciel. Ils peuvent créer et supprimer des tournois, des carnets d'addresse...";

$user = "Les UTILISATEURS";
$infUser = "Les UTILISATEURS peuvent consulter toutes les données PUBLIEES. Certains utilisateurs peuvent avoir des DROITS privilégiés sur certains tournois. Seul les administrateurs et les gestionnaires peuvent modifier des droits d'un utilisateur pour un tournoi données.";

$blkInfRight =  "Les droits";
$infRights = "Un UTILISATEUR peut avoir des droits particuliers sur certains tournois. Ces droits sont octroyés par un ADMINISTRATEUR ou un GESTIONNAIRE. Ils ne sont valables que pour le tournoi considéré.";
$guest = "Les INVITES";
$infGuest = "Un utilisateur INVITE peut consulter les données PUBLIEES et NON PUBLIEES du tournoi. Il ne peut consulter les données confidentielles (PRIVEES).";
$friend = "Les AMIS";
$infFriend = "Un utilisateur AMI peut consulter toutes les données du tournoi y compris les données confidentielles (PRIVEES).";
$assistant = "Les ASSISTANTS";
$infAssistant = "Un utilisateur ASSITANT peut modifier les résultats d'un tournoi; il ne peut modifier les tableaux mais peut saisir les matchs et inscrire des joueurs. En revanche il ne peut pas consulter les données PRIVEES et NON PUBLIEES";
$manage = "Les GESTIONNAIRES";
$infManage = "Un utilisateur GESTIONNAIRE peut consulter et modifier toutes les données du tournoi.";

?>