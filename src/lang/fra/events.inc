<?php

include dirname(__FILE__)."/badnet.inc";
include dirname(__FILE__)."/menu.inc";
include dirname(__FILE__)."/t_event.inc";
include dirname(__FILE__)."/t_items.inc";

// Titre  du navigateur
$events = "Tournois";
$itPostit    = "Messages";
$itPractical = "Pratique";
$itSubscribe = "Abonnement";
$itContacts  = "Contacts";
$itStats     = "Statistiques";
$itFees      = "Tarifs";
$itNews      = "Br�ves";
$itRights    = "Privil�ges";
$itParam     = "Param�tres";
$itEmpty     = "Vider";
$itCtl       = "Controle";

// Titres des fenetres
$fEditPostit   = "Edition de postit";
$tEvents    = "Gestion des tournois";
$tNewEvent  = "Cr�ation d'un tournoi";
$tEditEvent = "Modification d'un tournoi";
$tNewNew    = "Cr�ation d'une br�ve";
$tEditNew   = "Modification d'une br�ve";
$tEditRight = "Modification des droits";
$tAddRight  = "Affectation des droits";
$tDelRight  = "Suppression de privil�ges";
$tDelNews   = "Suppression de nouvelles";
$blkInfo    = "Informations g�n�rales";
$tPdf       = "Documents PDF";
$tBadges    = "Badges";
$tMain      = "Pr�sentation";
$tFees      = "Frais d'inscription";
$tGeneral   = "Infos g�n�rales";

// Titre des colonnes
$rowsEvents1  = "Date";
$rowsEvents2  = "Nom";
$rowsEvents3  = "Visites";
$rowsEvents4  = "Id";
$rowsEvents5  = "Type";

$rowsRights1  = "Login";
$rowsRights2  = "Identit�";
$rowsRights3  = "Pseudonyme";
$rowsRights4  = "Privil�ge";

$rowsNews1   = "Date";
$rowsNews2   = "Nouvelle";
$rowsNews3   = "Page";

$private = "Tournoi priv�e";
$hidden  = "Tournoi non publi�e";
$visible = "Tournoi publi�e";
$restore = "Tournoi supprim�e";

// Label des zones
$evntNature1  = "Tournoi individuel";
$evntNature2  = "Interclub/Par �quipe";
$evntNature3  = "Championnat";
$evntNature4  = "Troph�e";
$evntNature5  = "Intercodep";
$evntNature6  = "Autre";

$evntLevel1      = "Club";
$evntLevel2      = "R�gional";
$evntLevel3      = "National";
$evntLevel4      = "D�partemental";
$evntLevel5      = "Inter d�partement";
$evntLevel6      = "Inter r�gion";
$evntLevel7      = "International";

$evntCatage1 = "Tournoi adultes";
$evntCatage2 = "Tournoi jeunes";
$evntCatage3 = "Tournoi jeunes et adultes";


$newsText   = "Br�ve :";
$newsPage   = "Page � afficher :";
$users   = "Utilisateurs";

$right1 = 'Visiteur';
$right2 = 'Invit�';
$right3 = 'Ami';
$right4 = 'Assistant';
$right5 = 'Gestionnaire';
$right6 = 'Juge arbitre';

$titleFont = "Police du titre :";
$titleSize = "Taille du titre :";
$badgeId   = "Mod�le de badge :";
$skin      = "Interface graphique :";
$logoTop  = "Position logo haut :";
$logoLeft  = "Position logo gauche :";
$logoWidth = "Largeur logo :";
$logoHeight  = "Hauteur logo :";
$updateAll  = "Calculer les frais pour tous les joueurs";
$matchOnly  = "Se baser sur les matchs � la place des inscriptions";

$psitTitle  = "Titre :";
$psitTexte  = "Texte :";

$sendNew   = "Envoyer la nouvelle aux abonn�s";
$sendRight = "Notifier l'utilisateur des se privil�ges";

$urlStream    = "Url pour la vid�o :";
$urlLiveScore = "Url pour le live scoring :";

// Messages d'erreur
$msgNoNews   = "Pas de nouvelle pour ce tournoi";
$msgNoUsers  = "Pas d'utilisateur privil�gi�";
$msgNeedNews = "Vous devez s�lectionner au moins une nouvelle � supprimer.";
$msgConfirmDelNews = "Les nouvelles s�lectionn�es vont �tre supprim�es.";
$msgNeedRights     = "Vous devez s�lectionner au moins un utilisateur dont les privil�ges vont �tre supprim�s.";
$msgConfirmDelRights = "Les privil�ges des utilisateurs s�lectionn�s vont �tre supprim�s.";
$msgnewsText  = "Le texte de la nouvelle doit �tre renseign�";
$msgNoEvents  = "Aucun tournoi disponible.";
$msgNoPostits = "Aucun message.";

// Label des boutons
$btnDbf   = "Export FFBa";
$btnPrivate = "Masquer";
$btnPubli   = "Publier";
$btnAddPoster  = "Choisir affiche";
$btnLogo       = "Logo PDF";
$btnBadgeLogo  = "Logo badge";
$btnBadgeSpon  = "Logo annonceur";
$btnInline  = "Activer inscription en  ligne";
$btnOffline  = "D�-activer inscription en  ligne";
$btnEmptyCache  = "Vider le cache";

?>