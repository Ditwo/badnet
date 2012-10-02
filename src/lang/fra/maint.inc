<?php
include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_asso.inc";
include dirname(__FILE__)."/t_mber.inc";

// Titre  du navigateur
$maint   = "Maintenance";

// Titres des blocs
$tDraws  = "Tableaux"; 
$tPairs  = "Paires"; 
$tAsso   = "Associations"; 
$tMember = "Membres"; 
$destAssoList   = "Association cible :";
$srcAssoList    = "Association source :";
$destMemberList = "Membre cible :";
$srcMemberList  = "Membre source :";
// Titre des colonnes

// Label des listes

// Messages
$msgAsso   = "Utilitaire de fusion des associations en double.";
$msgMember = "Utilitaire de fusion des membres en double.";
$msgDraws  = "Lors d'import de tournoi, des nouveaux champs utilisés pour la confection d'un échéancier ne sont pas mis jours.";
$msgPairs  = "Lors d'import de tournoi, il peut y avoir création de paires fantomes. Elles pevent être supprimées içi";
$msgMergeAsso  = "Lors de la fusion, toutes les équipes de l'association source seront rattachées à l'association cible puis cette dernière sera supprimée.";
$msgMergeMember  = "Lors de la fusion, toutes les inscriptions du membre source seront rattachées au membre cible puis ce dernier sera supprimé.";

// Label des boutons
$btnDraws  = "Mise à jour des tableaux";
$btnPairs  = "Supprimer paires fantomes";
$btnAsso   = "Fusion";
$btnMember = "Fusion";
$btnMerge  = "Fusionner";
?>
