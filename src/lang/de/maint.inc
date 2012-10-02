<?php
include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_asso.inc";
include dirname(__FILE__)."/t_mber.inc";

// Titre  du navigateur
$maint   = "Verwaltung";

// Titres des blocs
$tDraws  = "Disziplinen"; 
$tPairs  = "Paarungen"; 
$tAsso   = "Verein"; 
$tMember = "Mitglieder"; 
$destAssoList   = "Zielverein :";
$srcAssoList    = "Quellverein :";
$destMemberList = "Zielmitglied :";
$srcMemberList  = "Quellmitglied :";
// Titre des colonnes

// Label des listes

// Messages
$msgAsso   = "Assistent zur Vereinigung von Mannschaften.";
$msgMember = "Assistent zur Vereinigung von Doppelpaarungen.";
$msgDraws  = "Beim Import eines Turniers neue Felder, die für die Erstellung eines Terminkalender verwendet werden, werden nicht aktualisiert.";
$msgPairs  = "Beim Import eines Turniers können 'Phantompaarungen' entstehen. Sie können hier gelöscht werden.";
$msgMergeAsso  = "Bei der Vereinigung werden alle Spieler dem Zielverein zugeordnet. Der andere Verein wird dann gelöscht.";
$msgMergeMember  = "Bei der Vereinigung wird das Quellmitglied dem Zielmitglied zugeordnet und dann gelöscht.";

// Label des boutons
$btnDraws  = "Felder aktualisieren";
$btnPairs  = "Phatompaarungen löschen";
$btnAsso   = "Fusion";
$btnMember = "Fusion";
$btnMerge  = "Vereinigen";
?>
