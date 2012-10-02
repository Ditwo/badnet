<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$purchase    = "Einkäufe";
$credit      = "Provision";

// Titres des fenetres
$tAccounts      = "Kontenliste";
$tPurchases     = "Einkäufe des Konots";
$tNewAccount    = "Konto erstellen";
$tEditAccount   = "Konto bearbeiten";
$tNewPurchase   = "Produnkt erstellen";
$tEditPurchase  = "Einkäufe bearbeiten";
$tNewCredit     = "Provision erstellen";
$tEditCredit    = "Provision bearbeiten";
$tDelPurchase   = "Einkauf löschen";
$tDelCredit     = "Provision löschen";
$tPurchase      = "Bestellung speichern";
$tLastCommand   = "Vorherige Bestellung";
$tEditAssocResa = "Reservierungen verwalten";

// Menus 
$itNewPurchase  = "Hinzufügen";

// Titre des colonnes
$rowsAccounts1  = "Name";
$rowsAccounts2  = "Code";
$rowsAccounts3  = "Status";
$rowsAccounts4  = "Kosten";
$rowsAccounts5  = "Ermäßigung";
$rowsAccounts6  = "bezahlt";
$rowsAccounts7  = "Saldo";

$itemCmd1 = $rowsAccounts1;
$itemCmd2 = $rowsAccounts4;
$itemCmd3 = "Prise en charge purchase.inc";
$itemCmd4 = "Von";

// Label des zones
$cuntTeam = "Vereinskonto :";
$cuntName = "Name :";
$cuntCode = "Code :";
$cmdItemId = "Artikel :";
$cmdName = "Titel :";
$cmdDate = "Datum :";
$cmdValue = "Wert :";
$cmdDiscount = "Ermäßigung :";
$cmdPayed    = "Bezahlt :";
$cmdCredit   = "Provision :";
$cmdCmt      = "Kommentar :";
$isRefund    = "Remboursable";
$regiBarCod  = "Käufer login";
$members     = "oder Käufername :";
$itemBarCod  = "Identifiant de l'article";
$items       = "oder Artikelname :";
$blkRegi     = "Angemeldet";
$regiName    = "Name :";
$regiSolde   = "Saldo :";
$regiRest    = "Prise en charge purchase.inc:";
$blkCunt     = "Konto";
$regiTeam    = "Verein :";
$cuntSolde   = "Saldo :";
$cuntRest    = "Prise en charge purchase.inc :";
$total       = "Kosten :";
$discount    = "Ermäßigung :";
$du          = "Restant du :";
$combien     = $du;
$rest        = "Solde prise en charge :";
$cmdType1   = "Andere";
$cmdType2   = "Kredit";
$cmdType3   = "Bar";
$cmdType4   = "Scheck";
$cmdType5   = "EC-Karte";
$resaDate   = "Reservierungsdatum :";

$hotel = "Hotel :";
$itemCode1 = "Einzel";
$itemCode2 = "Doppel";
$itemCode3 = "Dreier";
$itemCode4 = "Andere";
$arrival   = "Ankunftsdatum :";
$nbNight   = "Anzahl Nächte :";
$room      = "Zimmer Nr. :";
$itemCode  = "Zimmer Typ :";

// Label des listes

// Messages d'erreur
$msgNoPurchases  = "Keine Einkäufe in diesem Konto.";
$msgNoAccounts   = "Es existiert kein Konto.";
$msgNoItems      = "Kein Artikel definiert.";
$msgNeedPurchase = "Mindestens einen Einkauf auswählen.";
$msgNeedCredit   = "Eine Provision aussuchen.";
$msgConfirmDel   = "Die ausgewählten Einkäufe werden gelöscht.";
$msgConfirmDelCredit  = "Löschung der ausgewähltne Provisionen bestätigen.";
$msgConfirmDelCredit  = "Löschung der ausgewähltne Provisionen bestätigen.";
$msgNoValidBarCod     = "Code ungültig";
$msgNbMaxItems      = "Maximale Artikel Anzahl erreicht.";

// Label des boutons
$btnValid = "Bestätigen";
$btnAbort = "Letzten Abbrechen";
$btnNotPay  = "Im konto";
$btnPayedC  = "Scheck";
$btnPayedM  = "Bar";
$btnSoldeM  = "Saldo Bar";
$btnSoldeC  = "Saldo Scheck";
?>
