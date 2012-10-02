<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_items.inc";

// Titre  du navigateur
$items  = "Articles";

// Titres des fenetres
$tItems       = "Gestion des articles";
$tNewItem     = "Création d'un article";
$tEditItem    = "Modification d'un article";
$tSelectItem  = "Sélection des articles affichés";
$tDelItems    = "Suppression d'article";
$tDailyStats  = "Consommation par jour";
$tFees        = "Tarifs";
$tNewHotel    = "Création d'un hôtel";
$tEditHotel   = "Modification d'un hôtel";
$tValues      = "Inscriptions";

// Menus 
$itFees   = "Inscriptions";
$itHotel  = "Hôtels";
$itOthers = "Autre";

$itListItem  = "Code barre";
$itSpecialCode  = "Code spéciaux";
$itSelectItems  = "Articles affichés";
$itDailyStats   = "Conso par jour";

// Titre des colonnes
$rowsItems1  = "Nom";
$rowsItems2  = "Code";
$rowsItems3  = "Référence";
$rowsItems4  = "Cout unitaire";
$rowsItems5  = "Stock";
$rowsItems6  = "Suivi stock";
$rowsItems7  = "Créditable";

$rowsRooms1  = "Chambre";
$rowsRooms2  = "Type";
$rowsRooms3  = "Occupants";
$rowsRooms4  = "Equipe";

$rowsCommands1  = "Compte";
$rowsCommands2  = "Qui";
$rowsCommands3  = "Equipe";

$rowsFees1  = "Type";
$rowsFees2  = "Nb";
$rowsFees3  = "Cout";
$rowsFees4  = "Remise";
$rowsFees5  = "Payé";
$rowsFees6  = "Du";

// Labels
$itemOrderGrid  = "Position dans la grille :";
$itemIsInGrid   = "Grille de réservation";

$hotelName = "Nom :";
$priceSW  = "Chambre simple :";
$priceDW  = "Chambre double :";
$priceTW  = "Chambre trible :";
$priceOW  = "Chambre autre :";
$priceSWE  = $priceSW;
$priceDWE  = $priceDW;
$priceTWE  = $priceTW;
$priceOWE  = $priceOW;

$blkPriceW = "Prix semaine";
$blkPriceWE = "Prix week end";

// Titre des messages
$msgNeedItems      = "Sélectionner au moins un article.";
$msgNoItems        = "Aucun article déclaré.";
$msgConfirmDel     = "Confirmez la suppression des articles sélectionnés.";
$msgExistCommand   = "Des achats référencent cet article. Suppression impossible!";
$msgNoReservations = "Aucune réservation";
$msgNoSelectItems  = "Pas d'article sélectionné";
?>
