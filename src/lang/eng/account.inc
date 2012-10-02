<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
//include dirname(__FILE__)."/t_account.inc";

// Titre  du navigateur
$account     = "Comptes";

// Titres des fenetres
$tAccounts      = "Accounts list";
$tPurchases     = "Purchases list";
$tCredits       = "Account credit";
$tMembers       = "Registers on account";
$tNewAccount    = "Create an account";
$tEditAccount   = "Modify an account";
$tDelAccount    = "Delete an account";
$tNewPurchase   = "Create purchase";
$tEditPurchase  = "Modify purchase";

// Menus 
$itPurchases  = "Purchases";
$itCredits    = "Credits";


// Titre des colonnes
$rowsAccounts1  = "Name";
$rowsAccounts2  = "Code";
$rowsAccounts3  = "Status";
$rowsAccounts4  = "Purchase";
$rowsAccounts5  = "Discount";
$rowsAccounts6  = "Payed";
$rowsAccounts7  = "Balance";

$rowsPurchase1  = "Date";
$rowsPurchase2  = "Article";
$rowsPurchase3  = "Who";
$rowsPurchase4  = "Price";
$rowsPurchase5  = $rowsAccounts5;
$rowsPurchase6  = $rowsAccounts6;
$rowsPurchase7  = $rowsAccounts7;

$rowsCredit1  = "Date";
$rowsCredit2  = "Called";
$rowsCredit3  = "Who";
$rowsCredit4  = "Price";
$rowsCredit5  = "Left";
$rowsCredit6  = "Refundable";

$rowsMembers1  = "Sexe";
$rowsMembers2  = "Name";
$rowsMembers3  = "Firstname";
$rowsMembers4  = "Type";
$rowsMembers5  = $rowsAccounts4;
$rowsMembers6  = $rowsAccounts5;
$rowsMembers7  = $rowsAccounts6;
$rowsMembers8  = $rowsAccounts7;




// Label des zones
$cuntTeam = "Team Account :";
$cuntName = "Name :";
$cuntCode = "Code :";
$cuntCmt  = "Comment :";
$cmdName = "Achat :";
$cmdDate = "Date :";
$cmdValue = "Value :";
$cmdDiscount = "Discount :";
$cmdPayed = "Payed :";
$solde    = "Account balance :";
$accountDate   = "Date :";
$accountValue      = "Total cost :";
$accountDiscount   = "Discount :";
$accountPayed      = "Payed :";
$accountSolde      = "Balance :";   

// Label des listes

// Messages d'erreur
$msgcuntName  = "The name is required.";
$msgcuntCode  = "Code is required.";

$msgNoPurchases  = "No purchase on this account.";
$msgNoAccounts   = "No account.";
$msgNoCredits    = "No credit left.";
$msgNoItems      = "No items list.";
$msgNoMembers    = "No members on this account.";
$msgPersonnalAccount = "Personnal Account";
$msgConfirmDel    = "Do you want to delete the selected accounts ?";
$msgPurchaseExist = "An account has got purchase(s). Cannot delete it !";
$msgTeamExist = "One or more team is linked to a selected account. Cannot delete it !";
$msgMemberExist = "One or more member is linked to a selected account. Cannot delete it !";


// Label des boutons
$btnGo ="Go";
?>
