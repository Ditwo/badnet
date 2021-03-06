<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_asso.inc";
include dirname(__FILE__)."/t_teams.inc";
include dirname(__FILE__)."/t_book.inc";

// Titre  du navigateur
$asso      = "Vereine";

// Titres des fenetres
$tAssos        = "Gespeicherte Vereine";
$tNewAsso      = "Verein erstellen";
$tEditAsso     = "Verein bearbeiten";
$tDelAsso      = "Verein l�schen";
$tRegisterAsso = "Verein anmelden";
$tMembers      = "Anmeldungen des Vereins";
$tAccount      = "Konto bearbeiten";
$tSearchResult = "Verein suchen";
$tDiscounts    = "Kosten�bernahme";
$tCredits      = "Vorr�te";
$tReservations = "Reservierungen";

// Menus 
$itUpdate         = 'CPPP FFBa';
$itUpdateSept     = 'CPPP FFBa 01/09';
$itNewPlayer      = 'Neuer Spieler';
$itNewOfficial    = 'Neuer offizielle Begleitung';
$itNewOther       = 'Andere';

// Titre des colonnes
$rowsSearch1  = "Name";
$rowsSearch2  = "Pseudonym";
$rowsSearch3  = "Abk�rzung";
$rowsSearch4  = "Dep";
$rowsSearch5  = "Typ";

$rowsAssocs1  = "Datum";
$rowsAssocs2  = "Name";
$rowsAssocs3  = "Pseudonym";
$rowsAssocs4  = "Typ";
$rowsAssocs5  = "Konto";

$rowsRegis1  = "Geschlecht";
$rowsRegis2  = "Identit�t";
$rowsRegis3  = "Funktion";
$rowsRegis4  = "Konto";
$rowsRegis5  = "Ankunft";
$rowsRegis6  = "Abfahrt";

// Label des zones
$assoTypeName   = 'Art :';
$assoTypeSearch   = $assoTypeName;
$assoDept         = "D�partement :";

$blkCriteria = 'Suchkriterien';
$blkAssoc    = 'Verein';
$blkTeam     = 'Mannschaft';

// Label des listes

// Messages d'erreur
$msgNoAssocs        = "Kein Verein gespeichert";
$msgNoPlayers       = "Kein Spieler gespeichert";
$msgNoReservations  = "Keine Reservierungen f�r die angezeigten Artikel";
$msgNeedAssocs      = "Mindestens einen Verein ausw�hlen";
$msgTeamWithMember = "Spieler sind in der Mannschaft eingeschrieben. L�schen unm�glich!";
$msgNeedMembers   = "Mindestens eine angemeldeten ausw�hlen.";
$msgPurchaseExist = "Dieser angemeldete hat eink�ufe oder Reservierungen. l�schen unm�glich!";
$msgNoGridsItems  = "Kein Artikel ausgew�hlt!";
$msgNoCredits     = "Keine Vorr�te!";
$msgConfirmDel    = "Die ausgew�hlten Anmeldungen l�schen ?";
$msgNeedCriteria  = "Mindestens ein Suchkriterium angeben.";
$msgNotFound      = "Kein Treffer f�r die Suchkriterien.";

// Label des boutons
$btnSearch = "Suchen";
$btnNew    = "Neu";
$btnAddLogo = "Logo ausw�hlen";
$btnAddLogoTeam = $btnAddLogo;
?>
