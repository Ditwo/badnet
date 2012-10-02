<?php
include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/database.inc";
include dirname(__FILE__)."/t_mber.inc";
include dirname(__FILE__)."/t_regi.inc";

// Titre  du navigateur
$offo   = "Inscriptions officiels et autres";

// Titres des fenetres
$tNewRegOfficial    = "Inscription d'un officiel";
$tNewRegistration   = "Nouvelle inscription";
$tEditRegistration  = "Modification d'une inscription";
$tDelRegistration   = "Suppression d'une inscription";
$tPubliMembers      = "Publication d'inscription";

// Titre  des menus
$itRegisterMember = "Nouvelle inscription";
$itActivite       = "PDF duty form";

// titre des blocs
$blkAdmi  = "Administratif";
$blkRegi  = "Incription";
$blkCriteria   = "Critères de recherche";

// Label des zones
$mberSexeLabel  = "Genre :";

$rowsMembers1   = "Genre";
$rowsMembers2   = "Nom";
$rowsMembers3   = "Prénom";

$rowsOfficials1  = "Date";
$rowsOfficials2  = "Photo";
$rowsOfficials3  = "FAMILY NAME ";
$rowsOfficials4  = "Gender";
$rowsOfficials5  = "Status";
$rowsOfficials6  = "Function";
$rowsOfficials7  = "NOC";
$rowsOfficials8  = "Team";

// Label des listes

// Messages d'erreur
$msgNoMemberFound    = "Aucune personne trouvée avec ces critères !";
$msgNeedRegistration    = "Vous devez sélectionner au moins un inscrit.";
$msgNoRegisteredOfficials = "Aucun officiel enregistré";
$msgNoAutoPubli          = "Publication impossible car l'équipe n'est pas publiée";

// Label des boutons
$btnAddMber     = "New member";
$btnValidate    = "Validate";
$btnSearch      = "Search";
$btnAddPhoto    = "Select photo";

?>