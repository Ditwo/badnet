<?php
include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/database.inc";
include dirname(__FILE__)."/t_mber.inc";

// Titre  du navigateur
$resu   = "Matches list";

// Titre de la page 
$player_nameV = "Player : ";

$mberAsso = "Association : ";
$mberRank = "Rankings : ";
$mberTeam = "Team : ";

// Titre des colonnes
$rowsResuV1 = "Date";
$rowsResuV2 = "Discipline<br />(with)";
$rowsResuV3 = "Opponent(s)";
$rowsResuV4 = "Result";
$rowsResuV5 = "Score";

$rowsResuIndiv1 = "Step";
$rowsResuIndiv2 = "Round";
$rowsResuIndiv3 = "Versus";
$rowsResuIndiv4 = "Result";
$rowsResuIndiv5 = "Score";
$rowsResuIndiv6 = "Length";

// Messages
$msgNoMatch = "No match for this player";

?>
