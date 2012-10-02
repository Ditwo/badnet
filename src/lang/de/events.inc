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
$itFees      = "Meldegebühr";
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
$tDelNews   = "Nachrichten löschen";
$blkInfo    = "Allegemeinheiten";
$tPdf       = "PDF Dokumente";
$tBadges    = "Badges";
$tMain      = "Darstellung";
$tFees           = "Meldegebühren";
$tGeneral        = "Allgemeinheiten";


// Titre des colonnes
$rowsEvents1  = "Name";
$rowsEvents2  = "Datum";
$rowsEvents3  = "Besuche";
$rowsEvents4  = "Id";

$rowsRights1  = "Login";
$rowsRights2  = "Identität";
$rowsRights3  = "Pseudonym";
$rowsRights4  = "Rechte";

$rowsNews1   = "Datum";
$rowsNews2   = "Neuigkeiten";
$rowsNews3   = "Seite";

$private = "Privates Turnier";
$hidden  = "Turnier nicht veröffentlichen";
$visible = "Turnier veröffentlichen";
$restore = "Turnier entfernen";

// Label des zones
$evntNature1  = "Einzelturnier";
$evntNature2  = "Mannschaftsrunde";
$evntNature3  = "Meisterschaft";
$evntNature4  = "Trophée";

$evntLevel1      = "Bezirk";
$evntLevel2      = "Régional";
$evntLevel3      = "National";
$evntLevel4      = "International";

$newsText   = "Brève :";
$newsPage   = "Page à afficher :";
$users   = "Benutzer";

$right1 = 'Besucher';
$right2 = 'Eingeladen';
$right3 = 'Freund';
$right4 = 'Betreuer';
$right5 = 'Verwalter';

$titleFont = "Schriftart :";
$titleSize = "Schriftgröße :";
$badgeId   = "Badge Modell :";
$skin      = "Grafisches Interface :";

$psitTitle  = "Titel :";
$psitTexte  = "Text :";


$sendNew   = "Nachricht an die Abonnierten verschicken";
$sendRight = "Benutzer über seine Rechte informieren";

$urlStream = "Url des Videos :";

// Messages d'erreur
$msgNoNews   = "Keine Neuigkeiten für dieses Turnier";
$msgNoUsers  = "Keine Benutzer mit besonderen Rechten";
$msgNeedNews = "Sie müssen wenigstens eine Neuigkeiten zum Löschen auswählen.";
$msgConfirmDelNews = "Die ausgewählten Nachrichten werden gelöscht.";
$msgNeedRights     = "Sie müssen mindestens einen Benutzer auswählen, dessen Rechte gelöscht werden sollen.";
$msgConfirmDelRights = "Die Rechte der ausgewählten Benutzer werden gelöscht.";
$msgnewsText  = "events.inc -> übersetzen Le texte de la nouvelle doit être renseigné";
$msgNoEvents  = "Kein Turnier verfügbar.";
$msgNoPostits = "Keine Nachricht.";

// Label des boutons
$btnDbf   = "Export FFBa";
$btnPrivate = "Maskieren";
$btnPubli   = "Veröffentlichen";
$btnAddPoster  = "Plakat auswählen";
$btnLogo       = "Logo PDF";
$btnBadgeLogo  = "Logo badge";
$btnBadgeSpon  = "Logo annonceur";

?>