<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
//include dirname(__FILE__)."/t_account.inc";

// Titre  du navigateur
$account     = "Konten";

// Titres des fenetres
$tAccounts      = "Kontenliste";
$tNewAccount    = "Konto erstellen";
$tEditAccount   = "Konto bearbeiten";
$tDelAccount    = "Konto löschen";
$tNewPurchase   = "Artikel erstellen";
$tEditPurchase  = "Einkäufe bearbeiten";

// Menus 
$itPurchases  = "Einkäufe";
$itCredits    = "Provionen";
$itMembers    = "Mitglieder";


// Titre des colonnes
$rowsAccounts1  = "Name";
$rowsAccounts2  = "Code";
$rowsAccounts3  = "Status";
$rowsAccounts4  = "Einkauf";
$rowsAccounts5  = "Ermäßigung";
$rowsAccounts6  = "Bezahlt";
$rowsAccounts7  = "Saldo";

$rowsPurchase1  = "Datum";
$rowsPurchase2  = "Artikel";
$rowsPurchase3  = "Wer";
$rowsPurchase4  = "Kosten";
$rowsPurchase5  = $rowsAccounts5;
$rowsPurchase6  = $rowsAccounts6;
$rowsPurchase7  = $rowsAccounts7;

$rowsCredit1  = "Datum";
$rowsCredit2  = "Titel";
$rowsCredit3  = "Qui";
$rowsCredit4  = "Betrag";
$rowsCredit5  = "Rest";
$rowsCredit6  = "Rückzahlbar";

$rowsMembers1  = "Geschlecht";
$rowsMembers2  = "Name";
$rowsMembers3  = "Vorname";
$rowsMembers4  = "Typ";
$rowsMembers5  = $rowsAccounts4;
$rowsMembers6  = $rowsAccounts5;
$rowsMembers7  = $rowsAccounts6;
$rowsMembers8  = $rowsAccounts7;




// Label des zones
$cuntTeam = "Konto des Vereins :";
$cuntName = "Name :";
$cuntCode = "Code :";
$cuntCmt  = "Kommentar :";
$cmdName = "Einkauf :";
$cmdDate = "Datum :";
$cmdValue = "Wert :";
$cmdDiscount = "Ermäßigung :";
$cmdPayed = "Bezahlt :";
$solde    = "Saldo :";
$accountDate   = "Datum :";
$accountValue      = "Gesamt kosten :";
$accountDiscount   = "Ermäßigung :";
$accountPayed      = "Bezahlt :";
$accountSolde      = "Saldo :";

// Label des listes

// Messages d'erreur
$msgcuntName  = "Der Name ist Pflicht.";
$msgcuntCode  = "Der Code ist Pflicht.";

$msgNoPurchases  = "Keine Einkäufe für dieses Konto.";
$msgNoAccounts   = "Kein Konto.";
$msgNoCredits    = "Keine Provision auf diesem Konto.";
$msgNoItems      = "Kein Artikel.";
$msgNoMembers    = "Keine Anemldung in diesem Konto.";
$msgPersonnalAccount = "Persönliches Konto";
$msgConfirmDel    = "Ausgewählte Konten löschen?";
$msgPurchaseExist = "Ein Konto hat gespeicherte Einkäufe und Provisionen. Löschen unmöglich!";
$msgTeamExist = "Mindestens eine Mannschaft ist dem Konto zugeordnet. Löschen unmöglich!";
$msgMemberExist = "mindestens ein Teilnehmer ist dem Konto zugeordnet. Löschen unmöglich!";


// Label des boutons
$btnGo ="Los";
?>
