<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/t_user.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$main = "Empfang";

// Titre  des fenetres
$tSendMail  = "Administrator kontaktieren";
$tEditParam = "Parameter bearbeiten";
$tSubscriptions = "Ihre Abonnements";
$tEvents        = "Turniere und Ihre Benutzerrechte";
$tBooks         = "Adressbücher und Ihre Benutzerrechte";
$emailTitle     = "Email";
$contactTitle   = "Externer Kontakt";
$decoTitle      = "Deko";
$databaseTitle  = "Datenbank";
$liveTitle      = "Live score";
$synchroTitle   = "BadNet ferngesteuert";
$divEmail    = "Emails";
$divContact  = "Kontakte";
$divDeco     = "Dekos";
$divDatabase  = "Datenbanken";

// Menus
$itNewEvent = "Turnier erstellen";
$itNewBook  = "Adressbuch erstellen";

// Label des zones
$user_name = "Willkommen ";
$Administrators = "Administratoren :";
$Events  = "Turniere :";
$Books   = "Adressbücher :";
$Users   = "Benutzer :";
$from    = "Von :";
$to      = "An :";
$subject = "Betreff :";
$message = "Nachricht :";

$host = "SMTP Server :";
$auth = "Authentifikation";
$port = "Port :";
$userLog   = "Benutzer :";
$password  = "Passwort :";
$ffbaEmail = "Email FFBa :";
$ffbaUrl = "Url Spielerkartei (national) :";
$ibfUrl  = "Url Spielerkartei (international) :";
$mainTitle = "Titel :";
$subTitle  = "Untertitel :";
$emailType = "Servertyp :";
$skin      = "Default Skin :";
$language  = "Standardsprache :";
$emailType1 = "Smtp";
$emailType2 = "Mail";
$emailType2 = "Mail";
$emailType2 = "Mail";
$prefixe = "Tabellen Präfix :";
$version = "Datenbank Version :";

$liveIp = "IP-Adresse Briefträger :";
$livePort = "Port Briefträger :";
$ftpHost = "Host FTP Server :";
$ftpPort = "Port FTP Server :";
$ftpUser = "Login FTP Server :";
$ftpPwd  = "Passwort  :";
$ftpDir  = "Ordner :";
$isLiveLight = "Live ohne Bilder";

$synchroUrl  = "Url BadNet :";
$synchroUser = "Benutzer BadNet :";
$synchroPwd  = "Passwort :";
$synchroEvent = "Id Turnier :";


// Titre des colonnes
$Events1  = "Turniere";
$Events2  = "Benutzerrechte";

$Subscripts   = "Ergebnisse abonnieren";
$Subscripts1  = $Events1;
$Subscripts2  = "Mannschaften";
$Subscripts3  = "Email";

$Books   = "Adressbücher";
$Books1  = "Name";
$Books2  = "Rechte";

// Legende des bloc
$blkUser = "Informationen";
$blkLegRight = "Rechte";
$blkLegPubl  = "Veröffentlichen";

// Label des boutons
$btnUser  = "Bearbeiten";
$btnPwd   = "Passwort";
$btnMail  = "Abschicken";
$btnParam = "Parameter B@dNet";
$btnUsers = "Benutzer verwalten";

// Messages
$msgNoEvents = "Kein Turnier verfügbar";
$msgNoBooks  = "Kein Adressbuch verfügbar";
$msgNoSubscribe  = "Keine Ergebnisabonnements";
$msgLiveLight    = "Live mit Bilder";
$msgLiveNoLight  = "Live ohne Bilder";

$hlp1 = "-Wählen Sie einen Administrator aus um ihm eine E-mail zu schicken";
$hlp2 = "-Wählen Sie ein Turnier oder ein Adressbuch aus der Liste aus um es anzuschauen";

$intro = "Hier sind ein paar wichtige Hinweise im Umgang mit BadNet.";

$blkInfData = "Angaben";

$visible = "VERÖFFENTLICHTE Angaben";
$infVisible = "VERÖFFENTLICHTE Angaben : Sie sind von allen eingelogten Benutzern sichtbar.";

$hidden  = "NICHT VERÖFFENTLICHTE Angaben";
$infHidden = "Les données NON PUBLIEES : Angaben die darauf warten veröffentlicht zu werden (z.Bsp. Felder und Terminkalender)";

$private = "PRIVATE Angaben";
$infPrivate = "PRIVATE Angaben : Diese Angaben sind vertraulich. Sie sind nicht dazu gedacht veröffentlicht zu werden (z.Bsp. finanzielle Informationen).";

$delete = "GELÖSCHTE Angaben";
$infDelete = "GELÖSCHTE Angaben : Einige Angaben können selbstverständlich gelöscht werden. Sie verbleiben können allerdings nur von einem Administrator eingesehen und wiederhergestellt werden.";

$blkInfUser = "BENUTZER";
$admin = "ADMINISTRATOREN";
$infAdmin = "ADMINISTRATOREN können alle Angaben einsehen und bearbeiten. Sie sind die einzigen, die die Softwareparametr verändern und die Benutzerrechte verwalten können.";

$manager = "VERWALTER";
$infManager = "Die VERWALTER können alle Angaben verändern außer die Benutzerrechte und Programmeinstellungen. Sie können Turniere, Adressbücher, etc... erstellen und löschen.";

$user = "BENUTZER";
$infUser = "Die BENUTZER können veröffentlichte Informationen einsehen. Einege Benutzer können bei Turnieren Sonderrechte besitzen. Nur Administratoren und Verwalter können die Benutzerrechte verändern.";

$blkInfRight =  "Benutzerrechte";
$infRights = "Ein BENUTZER kann besondere Rechte auf einigen Turnieren besitzen. Diese Rechte werden von Verwaltern und Administratoren erteilt und gelten nur für die besagten Turniere";
$guest = "EINGELADEN";
$infGuest = "Ein EINGELADENER Benutzer kann veröffentlichte und nicht-veröffentlichte Angaben eines Turniers einsehen. Private Angaben kann er allerdings nicht einsehen.";
$friend = "FREUNDE";
$infFriend = "Ein Benutzer mit dem Status FREUND kann sogar die vertraulichen Angaben einsehen.";
$assistant = "BETREUER";
$infAssistant = "Ein BETREUER kann Ergebnisse eines Turniers bearbeiten; Die Felder kann er nicht bearbeiten, er kann allerdings Spiele und Spieler modifizieren. Er kann die nicht veröffentlichten und vertraulichen Angaben nicht einsehen";
$manage = "VERWALTER";
$infManage = "Ein VERWALTER kann die Angaben eines Turniers verwalten.";

?>