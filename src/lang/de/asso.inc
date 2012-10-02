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
$tDelAsso      = "Verein löschen";
$tRegisterAsso = "Verein anmelden";
$tMembers      = "Anmeldungen des Vereins";
$tAccount      = "Konto bearbeiten";
$tSearchResult = "Verein suchen";
$tDiscounts    = "Kostenübernahme";
$tCredits      = "Vorräte";
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
$rowsSearch3  = "Abkürzung";
$rowsSearch4  = "Dep";
$rowsSearch5  = "Typ";

$rowsAssocs1  = "Datum";
$rowsAssocs2  = "Name";
$rowsAssocs3  = "Pseudonym";
$rowsAssocs4  = "Typ";
$rowsAssocs5  = "Konto";

$rowsRegis1  = "Geschlecht";
$rowsRegis2  = "Identität";
$rowsRegis3  = "Funktion";
$rowsRegis4  = "Konto";
$rowsRegis5  = "Ankunft";
$rowsRegis6  = "Abfahrt";

// Label des zones
$assoTypeName   = 'Art :';
$assoTypeSearch   = $assoTypeName;
$assoDept         = "Département :";

$blkCriteria = 'Suchkriterien';
$blkAssoc    = 'Verein';
$blkTeam     = 'Mannschaft';

// Label des listes

// Messages d'erreur
$msgNoAssocs        = "Kein Verein gespeichert";
$msgNoPlayers       = "Kein Spieler gespeichert";
$msgNoReservations  = "Keine Reservierungen für die angezeigten Artikel";
$msgNeedAssocs      = "Mindestens einen Verein auswählen";
$msgTeamWithMember = "Spieler sind in der Mannschaft eingeschrieben. Löschen unmöglich!";
$msgNeedMembers   = "Mindestens eine angemeldeten auswählen.";
$msgPurchaseExist = "Dieser angemeldete hat einkäufe oder Reservierungen. löschen unmöglich!";
$msgNoGridsItems  = "Kein Artikel ausgewählt!";
$msgNoCredits     = "Keine Vorräte!";
$msgConfirmDel    = "Die ausgewählten Anmeldungen löschen ?";
$msgNeedCriteria  = "Mindestens ein Suchkriterium angeben.";
$msgNotFound      = "Kein Treffer für die Suchkriterien.";

// Label des boutons
$btnSearch = "Suchen";
$btnNew    = "Neu";
$btnAddLogo = "Logo auswählen";
$btnAddLogoTeam = $btnAddLogo;
?>
