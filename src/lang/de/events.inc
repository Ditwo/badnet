<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_event.inc";
include dirname(__FILE__)."/t_items.inc";

// Titre  du navigateur
$events = "Turniere";
$itPostit    = "Nachrichten";
$itPractical = "Informationen";
$itSubscribe = "Abonnement";
$itContacts  = "Kontakte";
$itStats     = "Statistiken";
$itFees      = "Meldegeb�hr";
$itNews      = "Mitteilungen";
$itRights    = "Benutzerrechte";
$itParam     = "Parameter";
$itEmpty     = "Leeren";
$itCtl       = "Kontrolle";

// Titres des fenetres
$fEditPostit   = "Kurznachrichten bearbeiten";
$tEvents    = "Turnierverwaltung";
$tNewEvent  = "Turnier erstellen";
$tEditEvent = "Turnier bearbeiten";
$tNewNew    = "Mitteilung erstellen";
$tEditNew   = "Mitteilung bearbeiten";
$tEditRight = "Bearbeitung der Rechte";
$tAddRight  = "Zuweisung der Rechte";
$tDelRight  = "Benutzerrechte entziehen";
$tDelNews   = "Nachrichten l�schen";
$blkInfo    = "Allegemeinheiten";
$tPdf       = "PDF Dokumente";
$tBadges    = "Badges";
$tMain      = "Darstellung";
$tFees           = "Meldegeb�hren";
$tGeneral        = "Allgemeinheiten";


// Titre des colonnes
$rowsEvents1  = "Name";
$rowsEvents2  = "Datum";
$rowsEvents3  = "Besuche";
$rowsEvents4  = "Id";

$rowsRights1  = "Login";
$rowsRights2  = "Identit�t";
$rowsRights3  = "Pseudonym";
$rowsRights4  = "Rechte";

$rowsNews1   = "Datum";
$rowsNews2   = "Neuigkeiten";
$rowsNews3   = "Seite";

$private = "Privates Turnier";
$hidden  = "Turnier nicht ver�ffentlichen";
$visible = "Turnier ver�ffentlichen";
$restore = "Turnier entfernen";

// Label des zones
$evntNature1  = "Einzelturnier";
$evntNature2  = "Mannschaftsrunde";
$evntNature3  = "Meisterschaft";
$evntNature4  = "Troph�e";

$evntLevel1      = "Bezirk";
$evntLevel2      = "R�gional";
$evntLevel3      = "National";
$evntLevel4      = "International";

$newsText   = "Br�ve :";
$newsPage   = "Page � afficher :";
$users   = "Benutzer";

$right1 = 'Besucher';
$right2 = 'Eingeladen';
$right3 = 'Freund';
$right4 = 'Betreuer';
$right5 = 'Verwalter';

$titleFont = "Schriftart :";
$titleSize = "Schriftgr��e :";
$badgeId   = "Badge Modell :";
$skin      = "Grafisches Interface :";

$psitTitle  = "Titel :";
$psitTexte  = "Text :";


$sendNew   = "Nachricht an die Abonnierten verschicken";
$sendRight = "Benutzer �ber seine Rechte informieren";

$urlStream = "Url des Videos :";

// Messages d'erreur
$msgNoNews   = "Keine Neuigkeiten f�r dieses Turnier";
$msgNoUsers  = "Keine Benutzer mit besonderen Rechten";
$msgNeedNews = "Sie m�ssen wenigstens eine Neuigkeiten zum L�schen ausw�hlen.";
$msgConfirmDelNews = "Die ausgew�hlten Nachrichten werden gel�scht.";
$msgNeedRights     = "Sie m�ssen mindestens einen Benutzer ausw�hlen, dessen Rechte gel�scht werden sollen.";
$msgConfirmDelRights = "Die Rechte der ausgew�hlten Benutzer werden gel�scht.";
$msgnewsText  = "events.inc -> �bersetzen Le texte de la nouvelle doit �tre renseign�";
$msgNoEvents  = "Kein Turnier verf�gbar.";
$msgNoPostits = "Keine Nachricht.";

// Label des boutons
$btnDbf   = "Export FFBa";
$btnPrivate = "Maskieren";
$btnPubli   = "Ver�ffentlichen";
$btnAddPoster  = "Plakat ausw�hlen";
$btnLogo       = "Logo PDF";
$btnBadgeLogo  = "Logo badge";
$btnBadgeSpon  = "Logo annonceur";

?>