<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$purchase    = "Achats";
$credit      = "Provision";

// Titres des fenetres
$tAccounts      = "Liste des comptes";
$tPurchases     = "Achats du compte";
$tNewAccount    = "Cr�ation d'un compte";
$tEditAccount   = "Modification d'un compte";
$tNewPurchase   = "Cr�ation d'un achat";
$tEditPurchase  = "Modification d'un achat";
$tNewCredit     = "Cr�ation d'une provision";
$tEditCredit    = "Modification d'une provision";
$tDelPurchase   = "Suppression d'achat";
$tDelCredit     = "Suppression de provision";
$tPurchase      = "Saisie d'une commande";
$tLastCommand   = "Commande pr�c�dente";
$tEditAssocResa = "Gestion des r�servations";

// Menus 
$itNewPurchase  = "Ajouter";

// Titre des colonnes
$rowsAccounts1  = "Nom";
$rowsAccounts2  = "Code";
$rowsAccounts3  = "Status";
$rowsAccounts4  = "Cout";
$rowsAccounts5  = "Remise";
$rowsAccounts6  = "Pay�";
$rowsAccounts7  = "Solde";

$itemCmd1 = $rowsAccounts1;
$itemCmd2 = $rowsAccounts4;
$itemCmd3 = "Prise en charge";
$itemCmd4 = "Du";

// Label des zones
$cuntTeam = "Compte du club :";
$cuntName = "Nom :";
$cuntCode = "Code :";
$cmdItemId = "Article :";
$cmdName = "Intitul� :";
$cmdDate = "Date :";
$cmdDateCredit = "Date :";
$cmdNbJour = "Nombre de jour :";
$cmdValue = "Valeur :";
$cmdDiscount = "Remise :";
$cmdPayed    = "Pay� :";
$cmdCredit   = "Provision :";
$cmdCmt      = "Commentaire :";
$isRefund    = "Remboursable";
$regiBarCod  = "Identifiant acheteur";
$members     = "ou nom acheteur :";
$itemBarCod  = "Identifiant de l'article";
$items       = "ou nom de l'article :";
$blkRegi     = "Inscrit";
$regiName    = "Nom :";
$regiSolde   = "Solde :";
$regiRest    = "Prise en charge :";
$blkCunt     = "Compte";
$regiTeam    = "Club :";
$cuntSolde   = "Solde :";
$cuntRest    = "Prise en charge :";
$total       = "Cout :";
$discount    = "Remise :";
$du          = "Restant du :";
$combien     = $du;
$rest        = "Solde prise en charge :";
$cmdType1   = "Autre";
$cmdType2   = "Cr�dit";
$cmdType3   = "Liquide";
$cmdType4   = "Ch�que";
$cmdType5   = "CB";
$resaDate   = "Date de r�servation :";

$hotel = "H�tel :";
$itemCode1 = "Simple";
$itemCode2 = "Twin";
$itemCode3 = "Tripple";
$itemCode4 = "Autre";
$arrival   = "Date d'arriv�e (jj-mm-aa hh:mm):";
$departure = "Date de d�part (jj-mm-aa hh:mm):";
$nbNight   = "Nombre de nuits :";
$room      = "Chambre n� :";
$itemCode  = "Type de chambre :";
$isfree    = "H�bergement pris en charge par l'organisation";

// Label des listes

// Messages d'erreur
$msgNoPurchases  = "Pas d'achat sur ce compte.";
$msgNoAccounts   = "Aucun compte existant.";
$msgNoItems      = "Aucun article d�clar�.";
$msgNeedPurchase = "S�lectionnez au moins un achat.";
$msgNeedCredit   = "S�lectionnez au moins une provision.";
$msgConfirmDel   = "Confirmez la suppression des achats s�lectionn�s.";
$msgConfirmDelCredit  = "Confirmez la suppression des provisions s�lectionn�es.";
$msgConfirmDelCredit  = "Confirmez la suppression des provisions s�lectionn�es.";
$msgNoValidBarCod     = "Code invalide";
$msgNbMaxItems      = "Nombre maximum d'articles atteint.";

// Label des boutons
$btnValid = "Valider";
$btnAbort = "Annuler dernier";
$btnNotPay  = "En compte";
$btnPayedC  = "Ch�que";
$btnPayedM  = "Esp�ce";
$btnSoldeM  = "Solde esp�ce";
$btnSoldeC  = "Solde Ch�que";
$btnPrint   = "Ticket";
?>
