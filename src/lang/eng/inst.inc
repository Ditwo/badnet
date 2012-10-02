<?php
include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/t_user.inc";

$inst = "BadNet installation";

// Titres des fenetres
$leftTitle = "Badminton Netware";
$rightTitle = "Install";

// Label des zones de saisies
$serverDb  = "Server Address for Database :";
$loginDb   = "Login :";
$passDb    = "Password :";
$typeDb    = "Database type :";
$langDb    = "Prefered langage for install :";

$choixDb1 = "Create a new database";
$choixDb2 = "Use existing database";
$listDb   = "Select a database :";
$newDb    = "Name for the new database :";

$blkEmail = "Messagerie";
$host = "SMTP server:";
$port = "Connexion port :";
$userLog = "User :";
$password = "Password :";

$blkTitle  = "Title";
$ffbaEmail = "Email FFBa :";
$ffbaUrl = "Url national ranking :";
$ibfUrl = "Url international ranking :";

$blkUrl    = "Others Websites";
$mainTitle = "Website title:";
$subTitle  = "Website subtitle:";



// Legende des blocs
$blkServer = "Server";
$blkBase   = "Database";
$blkAdmi   = "Administrator";

// Label des boutons
$btnNext = "Next >>";

// Messages d'erreur
$msgErrDir = "Installation cannot continue. Correct errors fisrt";
$msgDirNoRigth = "This directorie must be accessible.";
$msgDirNoExist = "This directories  must exist.";
$msgserverDb = "The server address is required. If you don't know, try : localhost";
$msgloginDb   = "Login is missing !";
$msgpassDb    = "Password is missing !";
$msgnewDb      = "Please, give the name of the new database !";
$msgConnectNOK = "Error on connection to server";
$msgFileCreation = "Error when creating install file.";
$msgBaseCreation = "Error when creating database.";
$msgErrorRight   = "Insuffisient right. Installation not possible";

// Labels explicatifs
$TutServer = "Install need an access to a database server. Please, fill in the informations concerning your database server.";

$TutDatabase = "Connection to database server successfull. Now choose the database to continue this install.";

$TutAdmin = "Database install successfull. You now need to create an administrator login. <BR>He will be the only one to have access to the whole data of each events. He will also have the right to modify them. <BR>He's the only one who could modify other users rights on each event. <BR>";

?>
