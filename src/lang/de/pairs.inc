<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";

// Titre  du navigateur
$pairs     = "Paarungen";

// Titres des fenetres
$tEditPair      = "Paarung bearbeiten";
$tPubliPairs    = "Spieler/Paarung veröffentlichen";
$tDelPairs      = "Spieler/Paarung löschen";
$tSelectKoPair  = "Zusammensetzung Hauptfeld";
$tSelectGroupPair = "Zusammensetzung Gruppen";
$tSelectG2KPair = "Qualifikanten in der Gruppe";
$tSelectPartner = "Partner aussuchen";
$tStatusPairs   = "Status Spieler/Paarung";
$tAskRank       = "Klassement aktualisieren";
$tImplode       = "Paarung zusammenstellen";

$itAdd    = "Hinzufügen";
$itRemove = "Entfernen";

// Titre des colonnes
$rowsSelectPairs1  = "Geschlecht";
$rowsSelectPairs2  = "NAME Vorname";
$rowsSelectPairs3  = "NOC";
$rowsSelectPairs4  = "Niveau";
$rowsSelectPairs5  = "Punkte";
$rowsSelectPairs6  = "Disziplin";

$rowsPlayers1  = "Geschelcht";
$rowsPlayers2  = "NAME Vorname";
$rowsPlayers3  = "NOC";
$rowsPlayers4  = "IBF Num";
$rowsPlayers5  = "Passnr.";

$pairList1 = "Tdz";
$pairList2 = "Position";
$pairList3 = "Neuer status";
$pairList4 = "Status";
$pairList5 = "Spieler";

$pairList2ko1 = "Tdz";
$pairList2ko2 = "Position";
$pairList2ko3 = "Spieler/Paarung";
$pairList2ko4 = "Herkunft";
$pairList2ko5 = "Satz Verhältnis";
$pairList2ko6 = "Punkte";

$pairGroups1 = "Tdz";
$pairGroups2 = "Position";
$pairGroups3 = "Spieler/Paarungen";
$pairGroups4 = "Mannschaft";
$pairGroups5 = "Niveau";
$pairGroups6 = "Punkte";

// Label des zones
$pairId      = " #";
$echoDisci   ="Disziplin :";
$pairIbfNum  = "IBF Nr. :";
$pairNatNum  = "Nat. Nr. :";
$pairIntRank = "Internationale Rangliste:";
$pairNatRank = "Nationale Rangliste :";
$pairStatus  = "Status :";
$pairState   = "Staat :";
$pairOrder   = "Position :";
$pairCmt     = "Kommentar";
$pairWo      = "Walk-Over";
$pairDateWo  = "W-O Datum";
$msgTds      = "An eins gesetzt";
$msgPosition = "Position";
$msgStatus   = "Neuer Status";
$msgPair     = "Spieler/Paarungen";
$nbsp3       = "Qualifikanten in 3er Gruppe :";
$nbsp4       = "Qualifikanten in 4er Gruppe :";
$nbsp5       = "Qualifikanten in 5er Gruppe :";
$nbSecond    = "Anzahl bester Zweiter :";
$nbQualif    = "Qualifizierte (Gesamt) :";
$nbPlaces    = "Gesamtanzahl Plätze :";
$criteria    = "Critère de séparation :";
$drawName    = "Feld :";

/* Messages d'erreur */
$msgNeedPairs  = "Sie müssen mindestns einen Spieler/Paarung auswählen.";
$msgConfirmDel = "Bestätigen Sie Löschung der Felder für den/die Spieler/Paarung.";
$msgSameGender = "Paarung nicht möglich. Die Spieler müssen unterschiedlichen Geschlechts sein.";
$msgDiffDraw   = "Paarung nicht möglich. Die Spieler sind nicht im selben Feld.";
$msgTooMuchPlayers = "Sie müssen Spieler auswählen, die keiner Paarung zugeteilt sind.";
$msgNeedTwoPlayers = "Maximal 2 Spieler auswählen.";
$msgPairInMatch    = "Der Spieler hat schon ein Spiel bestriten. Unmöglichen ihn aus dem Feld zu nehmen.";
$msgBadPairsNum    = "Sie müssen so viele Spieler/Paarungen auswählen wie verfügbare Plätze vorhanden sind.";
$msgLotAbort       = "Auslosung gescheitert. Keine Lösung gefunden.";
$msgNoPairs        = "Keine Spieler/Paarung verfügbar.";


// Label des boutons
$btnNew    = "Neue anmeldung";
$btnInt    = "International";
$btnNat    = "Französisch";
$btnSeeded = "Auslosung";
$btnRaz    = "Löschen";

?>
