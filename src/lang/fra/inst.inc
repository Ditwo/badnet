<?php
include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/t_user.inc";

$inst = "BadNet installation";

// Titres des fenetres
$leftTitle = "Badminton Netware";
$rightTitle = "Installation";

// Label des zones de saisies
$serverDb  = "Adresse du server de base de donn&eacute;es :";
$loginDb   = "Login de connexion :";
$passDb    = "Mot de passe de connexion :";
$typeDb    = "Type de base de donn&eacute;es :";
$langDb    = "Langue utilis&eacute;e pour l'installation :";

$choixDb1 = "Cr&eacute;er une nouvelle base de donn&eacute;es";
$choixDb2 = "Utiliser une base de donn&eacute;es existante";
$listDb   = "S&eacute;lectionnez une base de donn&eacute;es&nbsp; :";
$newDb    = "Nom de la nouvelle base de donn&eacute;es&nbsp; :";
$prefxDb  = "Prefixe des tables BadNet dans la base :";

$blkEmail = "Messagerie";
$host = "Serveur SMTP :";
$port = "Port de connexion :";
$userLog = "Utilisateur :";
$password = "Mot de passe :";

$blkTitle  = "Titres";
$ffbaEmail = "Email FFBa :";
$ffbaUrl = "Url fiche joueurs nationaux :";
$ibfUrl = "Url fiche joueurs internationaux :";

$blkUrl    = "Sites externes";
$mainTitle = "Titre du site:";
$subTitle  = "Sous-titre du site:";



// Legende des blocs
$blkServer = "Serveur";
$blkBase   = "Base de donn�es";
$blkAdmi   = "Administrateur";

// Label des boutons
$btnNext = "Suivant >>";

// Messages d'erreur
$msgErrDir = "Les r�pertoires suivants doivent exister et �tre accessibles � tous. L'installation ne peut continuer.";
$msgDirNoRigth = "Ce dossier doit �tre accessible par tous. Changer ses droits.";
$msgDirNoExist = "Ce dossier doit exister.";

$msgserverDb = "L\'adresse du serveur de base de donn�es est obligatoire. Si vous ne le connaissez pas essayez : localhost";
$msgloginDb   = "Le login est obligatoire !";
$msgpassDb    = "Le mot de passe est obligatoire !";
$msgnewDb      = "Donner le nom de la nouvelle base de donn�es!";
$msgConnectNOK = "Connexion au server impossible";
$msgFileCreation = "Erreur lors de la cr�ation du fichier d'installation.";
$msgBaseCreation = "Erreur lors de la cr�ation de la base de donn�es.";
$msgErrorRight   = "Vous devez avoir les droits d'�criture dans le dossier ou BadNet est install�. Impossible de continuer l'installation.";

// Labels explicatifs
$TutServer = "L'installation n�cessite un acc�s au server de base de donn�es. Renseigner ici les informations concernant votre serveur de base de donn�es.";

$TutDatabase = "La connexion au serveur de base de donn�es a r�ussie. Choississez maintenant la base de donn�es pour l'installation.";

$TutAdmin = "La base de donn�es a �t� correctement install�e. Vous devez maintenant cr�er un utilisateur administrateur. <BR>Il sera le seul a avoir acc�s � toutes les donn�es de tous les tournois et a pouvoir les modifier. <BR>Il sera le seul � pouvoir modifier les autres utilisateurs et a leur accorder les droits d'acc�es en consultation ou modificiation pour les tournois. <BR>";

?>