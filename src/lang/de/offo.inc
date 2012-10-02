<?php
include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/database.inc";
include dirname(__FILE__)."/t_mber.inc";
include dirname(__FILE__)."/t_regi.inc";

// Titre  du navigateur
$offo   = "Anmeldungen Offizielle und Andere";

// Titres des fenetres
$tNewRegOfficial    = "Offizielle Person anmelden";
$tNewRegistration   = "Neue Anmeldung";
$tEditRegistration  = "Anmeldung bearbeiten";
$tDelRegistration   = "Anmeldung löschen";
$tPubliMembers      = "Anmeldungen veröffentlichen";

// Titre  des menus
$itRegisterMember = "Neue Anmeldung";
$itActivite       = "Aktivitätskartei PDF";

// titre des blocs
$blkAdmi  = "Administrativ";
$blkRegi  = "Anmeldung";
$blkCriteria   = "Suchkriterien";

// Label des zones
$mberSexeLabel  = "Geschlecht :";
$rowsMembers1   = "Geschlecht";
$rowsMembers2   = "Name";
$rowsMembers3   = "Vorname";

$rowsOfficials1  = "Datum";
$rowsOfficials2  = "Photo";
$rowsOfficials3  = "NAME Vorname";
$rowsOfficials4  = "Geschlecht";
$rowsOfficials5  = "Status";
$rowsOfficials6  = "Funktion";
$rowsOfficials7  = "NOC";
$rowsOfficials8  = "Mannschaft";

// Label des listes

// Messages d'erreur
$msgNoMemberFound    = "Keine Person mit diesen Kriterien gefunden !";
$msgNeedRegistration    = "Sie müssen mindestens einen angemeldeten auswählen.";
$msgNoRegisteredOfficials = "Keine Offizieler angemeldet";
$msgNoAutoPubli          = "Veröffentlichung nicht möglich, das Mannschaft nicht veröffentlicht";

// Label des boutons
$btnAddMber     = "Mitglied erstellen";
$btnValidate    = "Bestätigen";
$btnSearch      = "Suchen";
$btnAddPhoto    = "Photo auswählen";

?>