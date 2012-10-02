<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_event.inc";
include dirname(__FILE__)."/t_items.inc";

// Titre  du navigateur
$events = "Events";
$itPostit    = "Messages";
$itPractical = "Practical";
$itSubscribe = "Subscribe";
$itContacts  = "Contacts";
$itStats     = "Statistiques";
$itFees      = "Entry fees";
$itNews      = "News";
$itRights    = "Rights";
$itParam     = "Parameters";
$itEmpty     = "Empty";
$itCtl       = "Control";

// Titres des fenetres
$fEditPostit= "Modifing message";
$tEvents    = "Events management";
$tNewEvent  = "New event";
$tEditEvent = "Edit event";
$tNewNew    = "Add news";
$tEditNew   = "Edit news";
$tEditRight = "Edit rights";
$tAddRight  = "Add right";
$tDelRight  = "Deleting right";
$tDelNews   = "Deleting news";
$blkInfo    = "Informations";
$tPdf       = "Pdf docs";
$tBadges    = "Badges";
$tMain      = "Presentation";
$tFees      = "Entry fees";
$tGeneral   = "Informations";

// Titre des colonnes
$rowsEvents1  = "Name";
$rowsEvents2  = "Date";
$rowsEvents3  = "Visited";
$rowsEvents4  = "Id";

$rowsRights1  = "Login";
$rowsRights2  = "Identity";
$rowsRights3  = "Pseudo";
$rowsRights4  = "Rights";

$rowsNews1   = "Date";
$rowsNews2   = "News";
$rowsNews3   = "Page";

$private = "Private Event";
$hidden  = "Unpublished event";
$visible = "Published event";
$restore = "Deleted event";

// Label des zones
$evntNature1 = "Individual tournament";
$evntNature2 = "Club tournament";
$evntNature3 = "Team tournament";
$evntNature4 = "Trophy";

$evntLevel1      = "Local";
$evntLevel2      = "Regional";
$evntLevel3      = "National";
$evntLevel4      = "International";

$newsText   = "News :";
$newsPage   = "Page published :";
$users      = "Users";

$right1 = 'User';
$right2 = 'Guest';
$right3 = 'Friend';
$right4 = 'Assistant';
$right5 = 'Manager';

$titleFont = "Title font :";
$titleSize = "Title size :";
$badgeId   = "Badge model :";
$skin      = "Graphical skin :";

$psitTitle  = "Title :";
$psitTexte  = "Text :";

$sendNew   = "Send new to subscribers";
$sendRight = "Notify new right to the user";

$urlStream = "Streaming video URL :";

// Messages d'erreur
$msgNoNews  = "No news for this event";
$msgNoUsers = "No rights for any user";
$msgNeedNews = "Select news to be delete.";
$msgConfirmDelNews = "News selected will be deleted.";
$msgNeedRights     = "Select right to be delete";
$msgConfirmDelRights = "Rights selected will be deleted.";
$msgnewsText = "Text is mandatory.";
$msgNoEvents = "No event available.";
$msgNoPostits = "No message.";

// Label des boutons
$btnDbf   = "French export";
$btnPrivate = "Hide";
$btnPubli   = "Published";
$btnAddPoster  = "Select poster";
$btnLogo       = "Pdf logo";
$btnBadgeLogo  = "Badge logo";
$btnBadgeSpon  = "Sponsor logo";

?>
