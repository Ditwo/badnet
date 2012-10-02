<?php
include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/t_user.inc";

// Titre  du navigateur
$cnx    = "Badminton Netware";

// Titres des fenetres
$tUsers    = "Benutzerverwaltung";
$tNewUser  = "Neuer Benutzer";
$tEditUser = "Benutzeränderung";
$tEditPwd  = "Passwortänderung";
$tErrUser  = "Benutzeraktualisierung";

$tInit    = "Verbindung";
$tLogin   = "personalisierte Verbindung";

$fNewPwd  = "Benutzername verloren";
$fNewUser = "Neues Konto eröffnen";

// Titres des cartouches
$tEventInt  = "internationale Stufe";
$tEventNat  = "nationale Stufe";
$tEventReg  = "regionale Stufe";
$tEventDep  = "Departement Stufe";
$tToday     = "----- Heute ------";

$tIndiEventList  = "Individual tournaments";
$tEventIndiInt = $tEventInt;
$tEventIndiNat = $tEventNat;
$tEventIndiReg = $tEventReg;
$tEventIndiDep = $tEventDep;

$tNews       = "Letzte Nachrichten";
$tNextTies   = "Nächste Spiele";
$tEventList  = "Alle Turniere";
$tMostVisited = "die meistbesuchten";
$tLastCreated = "die neusten";

// Titre des colonnes
$rowsUsers1  = "Name";
$rowsUsers2  = "Login";
$rowsUsers3  = "Email";
$rowsUsers4  = "Status";
$rowsUsers5  = "Sprache";
$rowsUsers6  = "Eröffnet";
$rowsUsers7  = "Letzter Besuch";
$rowsUsers8  = "Anz. Verbindungen";

// Label des zones
$Events    = "";
$username  = "Login:";
$password  = "Passwort:";
$OldPwd    = "Altes Passwort:";
$NewPwd    = "Neues Passwort:";
$Lang      = "Sprache:";

$Email     = "Email:";
$Comment   = "Bemerkung:";
$Id        = "Benutzername:";
$Creation  = "eröffnet am:";
$LastModif = "Letzte Aktualisierung:";
$LastVisit = "Letzter Besuch:";
$NbCnx     = "Anzahl Verbindungen:";
$Type1     = "Benutzer";
$Type2     = "Administrator";



// Messages d'erreur
$msgUserExist     = "Dieser Benutzername ist bereits vergeben. Versuchen Sie ein anderes!";
$msgOldPwd      = "Das alte Passwort ist obligatorisch!";
$msgNewPwd      = "Das neue Passwort ist obligatorisch!";
$msgLang        = "Die Sprache ist obligatorisch!";
$msgNeedSelect  = "Sie müssen mindestens einen Benutzer auswählen.";
$msgCnxRefused  = "Passwort oder Benutzername ungültig! Versuchen Sie ein anderes.";
$msgCnxExpired  = "Session ist beendet. Melden Sie sich wieder an!";
$msgCnxIdle     = "Time out. Melden Sie sich wieder an!";
$msgusername    = "Der Benutzername ist obligatorisch!";
$msgpassword    = "Das Passwort ist obligatorisch!";

$msgLostPwd  = "Falls Sie Ihr Passwort verloren haben, geben Sie ihren Benutzername oder Email-Adresse ein.";
$msgNewPwd = "Sie werden automatisch ein neues Passwort erhalten.";
$msgPwdSend = "Ein neues Passwort wurde Ihnen automatisch gesandt.";
$msgPwdNotSend = "Ein Fehler ist während dem Versand Ihr neues Passwort aufgetreten. Versuchen Sie es später wieder.";
$msgInvalidData = "Benutzername oder Email unbekannt";

// Label des boutons
$btnConnect   = "verbinden";
$btnRegister  = "Speichern";
$btnNew    = "Neues Konto";
$btnSend   = "Senden";
$btnLoose  = "Passwort oder Benutzername verloren?";

?>