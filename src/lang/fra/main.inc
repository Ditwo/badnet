<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/t_user.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$main = "Accueil";

// Titre  des fenetres
$tSendMail  = "Contacter l'administrateur";
$tEditParam = "Modification des param�tres";
$tSubscriptions = "Vos abonnements";
$tEvents        = "Les tournois et vos droits";
$tBooks         = "Les carnets d'adresse et vos droits";
$emailTitle     = "Courrier �lectronique";
$contactTitle   = "Contacts externes";
$decoTitle      = "D�corations";
$databaseTitle  = "Base de donn�es";
$liveTitle      = "Score en live";
$synchroTitle   = "BadNet distant";
$divEmail    = "Emails";
$divContact  = "Contacts";
$divDeco     = "Decorations";
$divDatabase  = "Base de donn�es";

// Menus
$itNewEvent = "Cr�er un tournoi";
$itNewBook  = "Cr�er un carnet d'adresse";

// Label des zones
$user_name = "Bienvenue ";
$Administrators = "Administrateurs :";
$Events  = "Tournois :";
$Books   = "Carnets d'adresses :";
$Users   = "Utilisateurs :";
$from    = "De :";
$to      = "� :";
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
$skin      = "Skin par d�faut :";
$language  = "Langue par d�faut :";
$emailType1 = "Smtp";
$emailType2 = "Mail";
$sport1 = "Squash";
$sport2 = "Badminton";
$prefixe = "Pr�fixe des tables :";
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

$Subscripts   = "Abonnements aux r�sultats";
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
$btnParam = "Param�trage B@dNet";
$btnUsers = "Gestion des utilisateurs";

// Messages
$msgNoEvents = "Aucun tournoi disponible";
$msgNoBooks  = "Aucun carnet d'adresse disponible";
$msgNoSubscribe  = "Aucun abonnemment aux r�sultats";
$msgLiveLight    = "Live avec image (plus beau mais trafic important)";
$msgLiveNoLight  = "Live sans image (moins beau mais trafic l�ger)";

$hlp1 = "-S�lectionner un administrateur dans la liste pour lui envoyer un email";
$hlp2 = "-S�lectionner un tournoi ou un carnet d'adresse dans la liste adequate pour le consulter";

$intro = "Voici quelques notions importantes qui vous aiderons � mieux utiliser Badminton Netware.";

$blkInfData = "Les donn�es";

$visible = "Les donn�es PUBLIEES";
$infVisible = "Les donn�es PUBLIEES : Elles sont visibles par tout utilisateur connect�.";

$hidden  = "Les donn�es NON PUBLIEES";
$infHidden = "Les donn�es NON PUBLIEES : Ce sont des informations en attente d'�tre publi�es (par exemple les tableaux ou les horaires des match)";

$private = "Les donn�es PRIVEES";
$infPrivate = "Les donn�es PRIVEES : Elles sont confidentielles. Elles ne sont pas destin�es � �tre publi�es (par exemple les donn�es financi�res).";

$delete = "Les donn�es EFFACEES";
$infDelete = "Les donn�es EFFACEES : Certaines donn�es peuvent �tre supprim�es logiquement. Elles existent toujours mais seul un administrateur peut les voir et les restaurer.";

$blkInfUser = "Les utilisateurs";
$admin = "Les ADMINISTRATEURS";
$infAdmin = "Les ADMINISTRATEURS peuvent consulter et modifier toutes les donn�es. Ils sont les seuls � pouvoir modifier les param�tres du logiciel et les seuls � avoir acc�s aux fonctions de gestion des utilisateurs.";

$manager = "Les GESTIONNAIRES";
$infManager = "Les GESTIONNAIRES peuvent consulter et modifier toutes les donn�es sauf les utilisateurs et les param�tres du logiciel. Ils peuvent cr�er et supprimer des tournois, des carnets d'addresse...";

$user = "Les UTILISATEURS";
$infUser = "Les UTILISATEURS peuvent consulter toutes les donn�es PUBLIEES. Certains utilisateurs peuvent avoir des DROITS privil�gi�s sur certains tournois. Seul les administrateurs et les gestionnaires peuvent modifier des droits d'un utilisateur pour un tournoi donn�es.";

$blkInfRight =  "Les droits";
$infRights = "Un UTILISATEUR peut avoir des droits particuliers sur certains tournois. Ces droits sont octroy�s par un ADMINISTRATEUR ou un GESTIONNAIRE. Ils ne sont valables que pour le tournoi consid�r�.";
$guest = "Les INVITES";
$infGuest = "Un utilisateur INVITE peut consulter les donn�es PUBLIEES et NON PUBLIEES du tournoi. Il ne peut consulter les donn�es confidentielles (PRIVEES).";
$friend = "Les AMIS";
$infFriend = "Un utilisateur AMI peut consulter toutes les donn�es du tournoi y compris les donn�es confidentielles (PRIVEES).";
$assistant = "Les ASSISTANTS";
$infAssistant = "Un utilisateur ASSITANT peut modifier les r�sultats d'un tournoi; il ne peut modifier les tableaux mais peut saisir les matchs et inscrire des joueurs. En revanche il ne peut pas consulter les donn�es PRIVEES et NON PUBLIEES";
$manage = "Les GESTIONNAIRES";
$infManage = "Un utilisateur GESTIONNAIRE peut consulter et modifier toutes les donn�es du tournoi.";

?>