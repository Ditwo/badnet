<?php
include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/database.inc";
include dirname(__FILE__)."/t_mber.inc";
include dirname(__FILE__)."/t_regi.inc";

// Titre  du navigateur
$resu   = "Spieler Informationen";

// Titre de la page 
$playerName = "Spieler : ";
$tEditRegi  = "Status des Spielers bearbeiten";
$tCivil   =  "Persönliche Daten";
$tPlay    =  "Turnier";
$tDraws   =  "Disziplinen";
$tRegis   =  "Anmeldung";

$mberCatage =  "Kategorie :";
$mberCompet =  "Genehmigung :";
$mberSurclasse =  "Seniorenerklärung :";
$mberDateSurclasse =  "seit :";
$mberDate   =  "Datum :";
$mberDraws  =  "Disziplinen :";
$mberPartnerD  =  "Doppelpartner :";
$mberPartnerM  =  "Mixedpartner :";


$mberAsso = "Verein : ";
$mberRank = "Klassement : ";
$mberCPPP = "Durchschnitt : ";
$mberTeam = "Mannschaft : ";

$regiIsWo = "Spieler Walk-Over";
$regiIsPresent = "Spieler anwesend";
$umpireCourt   = "Schiedsrichter auf Feld :";

// Titre des colonnes
$rowsResuV1 = "Datum";
$rowsResuV2 = "Disziplin<br />(Partner)";
$rowsResuV3 = "Gegner";
$rowsResuV4 = "Tendenz";
$rowsResuV5 = "Ergebnis";

$rowsResuTeam1 = "Datum";
$rowsResuTeam2 = "Disziplin<br />(Partner)";
$rowsResuTeam3 = "Gegner";
$rowsResuTeam4 = "Tendenz";
$rowsResuTeam5 = "Ergebnis";


// Titre des colonnes
$rowsResuIndiv1 = "Datum";
$rowsResuIndiv2 = "Ort";
$rowsResuIndiv3 = "Num";
$rowsResuIndiv4 = "Phase";
$rowsResuIndiv5 = "Runde";
$rowsResuIndiv6 = "Gegner";
$rowsResuIndiv7 = "Tendenz";
$rowsResuIndiv8 = "Ergebnis";
$rowsResuIndiv9 = "Anfang";
$rowsResuIndiv10 = "Ende";
$rowsResuIndiv11 = "Dauer";


$rowsEntries1 = "Hauptfeld";

// Messages
$msgNoMatch = "Kein Spiel für diese(n) SpielerIn";

$btnEdit  = "Bearbeiten";
$btnPhoto = "Foto";
?>