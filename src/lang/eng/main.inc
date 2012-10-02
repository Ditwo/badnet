<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/t_user.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$main = "Accueil";

// Titre  des fenetres
$tSendMail  = "Contact administrator";
$tEditParam = "Edit parameters";
$tSubscriptions = "Your subscription";
$tEvents        = "Your events and rights";
$tBooks         = "Address books and rights";
$emailTitle     = "Emails";
$contactTitle   = "Contacts";
$decoTitle      = "Decorations";
$databaseTitle  = "Database";
$divEmail    = "Emails";
$divContact  = "Contacts";
$divDeco     = "Decorations";
$divDatabase  = "Database";

// Menus
$itNewEvent = "New event";
$itNewBook  = "New address book";

// Label des zones
$user_name = "Welcome ";
$Administrators = "Administrators :";
$Events  = "Events :";
$Books   = "Address book :";
$Users   = "Users :";
$from    = "From :";
$to      = "To :";
$subject = "Subject :";
$message = "Message :";

$host = "SMTP server :";
$port = "Connection port :";
$userLog = "User :";
$password = "Password :";
$ffbaEmail = "Email FFBa :";
$ffbaUrl = "Url national ranking :";
$ibfUrl = "Url international ranking :";
$mainTitle = "Title :";
$subTitle = "Subtitle :";
$emailType = "Email server type :";
$skin      = "Default skin :";
$language  = "Default language :";
$emailType1 = "Smtp";
$emailType2 = "Mail";
$prefixe = "Column prefix :";
$version = "Database version :";

// Titre des colonnes
$Events1  = "Events";
$Events2  = "Rights";

$Subscripts   = "Subscriptions to results";
$Subscripts1  = $Events1;
$Subscripts2  = "Teams";
$Subscripts3  = "Email";

$Books   = "Address book";
$Books1  = "Name";
$Books2  = "Right";

// Legende des bloc
$blkUser = "Informations";
$blkLegRight = "Rights";
$blkLegPubl  = "Publication";

// Label des boutons
$btnUser  = "Edit";
$btnPwd   = "Password";
$btnMail  = "Send";
$btnParam = "B@dNet Parameters";
$btnUsers = "Users management";

// Messages
$msgNoEvents = "No event available";
$msgNoBooks  = "No address book available";
$msgNoSubscribe  = "No subscription";

$hlp1 = "-Select an administrator from this list to send him an email";
$hlp2 = "-Select an event or an address book to look at it";

$intro = "Here are some important notions to uses BadNet properly.";

$blkInfData = "Datas";

$visible = "PUBLISHED Datas";
$infVisible = "PUBLISHED Data : they are visible by every user.";

$hidden  = "UNPUBLISHED Datas";
$infHidden = "UNPUBLISHED Datas : They are waiting to be published ( example draws or schedule)";

$private = "PRIVATE Datas";
$infPrivate = "PRIVATE Datas : They are confidential. They are not designed to be published to everybody(example : financial datas).";

$delete = "DELETED Datas";
$infDelete = "DELETED Datas : Some datas could be deleted (logically not physically)). They are still existing but only an administrator could see them or restore them.";

$blkInfUser = "Users";
$admin = "ADMINISTRATORS";
$infAdmin = "ADMINISTRATORS could consult and modify all datas. They could modify BadNet parameters and have access to users management fonctionnality.";

$manager = "MANAGERS";
$infManager = "MANAGERS could consult and modify all datas but uses and BadNet parameters. They could create, modify and delete events or address books...";

$user = "USERS";
$infUser = "USERS could consult all PUBLISHED datas. Some users could have special rights on given event. Only administrators and managers could modify rights of a user on a specific tournament.";

$blkInfRight =  "The rights";
$infRights = "A user could have special rights on specific event. These rights are given by an ADMINISTRATOR or a MANAGER. These rights are only given to the specific event.";
$guest = "GUESTS";
$infGuest = "A GUEST could consult PUBLISHED and UNPUBLISHED datas of an event. He could not consult PRIVATE datas.";
$friend = "FRIENDS";
$infFriend = "A FRIEND could consult all datas of an event : PUBLISHED, UNPUBLISHED and PRIVATE.";
$assistant = "ASSISTANTS";
$infAssistant = "An ASSITANT could modify results of an event. He could not modify draws but could type matches results, register players or teams. But he could not consult PRIVATE and NON PUBLISHED datas";
$manage = "MANAGERS";
$infManage = "A MANAGER could consult and modify all datas of a given tournament.";

?>
