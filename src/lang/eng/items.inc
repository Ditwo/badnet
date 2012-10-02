<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_items.inc";

// Titre  du navigateur
$items  = "Items";

// Titres des fenetres
$tItems       = "Items management";
$tNewItem     = "New Item";
$tEditItem    = "Edit Item";
$tSelectItem  = "Select Item(s)";
$tDelItems    = "Delete item(s)";
$tDailyStats  = "Daily stats";

// Menus 
$itNewItem   = "New";
$itDelItem   = "Delete";
$itListItem  = "Bar Code";
$itSpecialCode  = "Special Code";
$itSelectItems  = "Selected Items";
$itDailyStats   = "Daily stats";

// Titre des colonnes
$rowsItems1  = "Name";
$rowsItems2  = "Code";
$rowsItems3  = "Reference";
$rowsItems4  = "Unitary Cost";
$rowsItems5  = "Stock";
$rowsItems6  = "Suivi stock";
$rowsItems7  = "Créditable";

$rowsCommands1  = "Account";
$rowsCommands2  = "Who";
$rowsCommands3  = "Team";

// Labels
$itemOrderGrid  = "Order on grid :";
$itemIsInGrid   = "Reservation grid";

// Titre des messages
$msgNeedItems  = "Select at least one item.";
$msgNoItems    = "No item known.";
$msgConfirmDel = "Please, confirm you want to delete the selected items.";
$msgExistCommand = "This item is linked to achats. Delete not allowed !";

?>
