<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
//include_once dirname(__FILE__)."/t_account.inc";

// Titre  du navigateur
$purchase    = "Purchases";
$credit      = "Credit";

// Titres des fenetres
$tAccounts      = "Accounts list";
$tPurchases     = "Purchases";
$tNewAccount    = "Add account";
$tEditAccount   = "Edit account";
$tNewPurchase   = "Add purchase";
$tEditPurchase  = "Edit purchase";
$tNewCredit     = "Add credit";
$tEditCredit    = "Edit credit";
$tDelPurchase   = "Delete purchase";
$tDelCredit     = "Delete credit";
$tPurchase      = "Purchase";
$tLastCommand   = "Last command";
$tEditAssocResa = "Reservation management";

// Menus 
$itNewPurchase  = "Add";

// Titre des colonnes
$rowsAccounts1  = "Name";
$rowsAccounts2  = "Code";
$rowsAccounts3  = "Status";
$rowsAccounts4  = "Cost";
$rowsAccounts5  = "Discount";
$rowsAccounts6  = "Paid";
$rowsAccounts7  = "Balance";

$itemCmd1 = $rowsAccounts1;
$itemCmd2 = $rowsAccounts4;
$itemCmd3 = "Prise en charge";
$itemCmd4 = "";

// Label des zones
$cuntTeam = "Association account :";
$cuntName = "Name :";
$cuntCode = "Code :";
$cmdItemId = "Item :";
$cmdName = " :";
$cmdDate = "Date :";
$cmdValue = "Value :";
$cmdDiscount = "Discount :";
$cmdPayed    = "Paid :";
$cmdCredit   = "Credit :";
$cmdCmt      = "Comment :";
$isRefund    = "Refundable";
$regiBarCod  = "Buyer's Id";
$members     = "or buyer's name :";
$itemBarCod  = "Item's Id";
$items       = "Item's name :";
$blkRegi     = "Registered";
$regiName    = "Name :";
$regiSolde   = "Balance :";
$regiRest    = "Prise en charge :";
$blkCunt     = "Account";
$regiTeam    = "Team :";
$cuntSolde   = "Balance :";
$cuntRest    = "Prise en charge :";
$total       = "Cost :";
$discount    = "Discount :";
$du          = "Restant du :";
$combien     = $du;
$rest        = "Solde prise en charge :";
$cmdType1   = "Other";
$cmdType2   = "Credit";
$cmdType3   = "Cash";
$cmdType4   = "Check";
$cmdType5   = "CB";
$resaDate   = "Reservation date :";

// Label des listes

// Messages d'erreur
$msgNoPurchases  = "No purchase on this account.";
$msgNoAccounts   = "No account found.";
$msgNoItems      = "No items found.";
$msgNeedPurchase = "You need to select at least 1 purchase.";
$msgNeedCredit   = "You need to select at least 1 credit.";
$msgConfirmDel   = "Are you sure you want to delete the selected purcahses.";
$msgConfirmDelCredit  = "Are you sure you want to delete the selected purcahses.";
//$msgConfirmDelCredit  = "Are you sure you want to delete the selected purcahses.";
$msgNoValidBarCod     = "Invalid barcode";
$msgNbMaxItems      = "Item max number reached.";

// Label des boutons
$btnValid = "Validate";
$btnAbort = "Abort";
$btnNotPay  = "En compte";
$btnPayedC  = "Check";
$btnPayedM  = "Cash";
$btnSoldeM  = "Cash Balance";
$btnSoldeC  = "Check Balance";
?>
