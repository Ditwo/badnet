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
$tDelAccount    = "Konto l�schen";
$tNewPurchase   = "Artikel erstellen";
$tEditPurchase  = "Eink�ufe bearbeiten";

// Menus 
$itPurchases  = "Eink�ufe";
$itCredits    = "Provionen";
$itMembers    = "Mitglieder";


// Titre des colonnes
$rowsAccounts1  = "Name";
$rowsAccounts2  = "Code";
$rowsAccounts3  = "Status";
$rowsAccounts4  = "Einkauf";
$rowsAccounts5  = "Erm��igung";
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
$rowsCredit6  = "R�ckzahlbar";

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
$cmdDiscount = "Erm��igung :";
$cmdPayed = "Bezahlt :";
$solde    = "Saldo :";
$accountDate   = "Datum :";
$accountValue      = "Gesamt kosten :";
$accountDiscount   = "Erm��igung :";
$accountPayed      = "Bezahlt :";
$accountSolde      = "Saldo :";

// Label des listes

// Messages d'erreur
$msgcuntName  = "Der Name ist Pflicht.";
$msgcuntCode  = "Der Code ist Pflicht.";

$msgNoPurchases  = "Keine Eink�ufe f�r dieses Konto.";
$msgNoAccounts   = "Kein Konto.";
$msgNoCredits    = "Keine Provision auf diesem Konto.";
$msgNoItems      = "Kein Artikel.";
$msgNoMembers    = "Keine Anemldung in diesem Konto.";
$msgPersonnalAccount = "Pers�nliches Konto";
$msgConfirmDel    = "Ausgew�hlte Konten l�schen?";
$msgPurchaseExist = "Ein Konto hat gespeicherte Eink�ufe und Provisionen. L�schen unm�glich!";
$msgTeamExist = "Mindestens eine Mannschaft ist dem Konto zugeordnet. L�schen unm�glich!";
$msgMemberExist = "mindestens ein Teilnehmer ist dem Konto zugeordnet. L�schen unm�glich!";


// Label des boutons
$btnGo ="Los";
?>
