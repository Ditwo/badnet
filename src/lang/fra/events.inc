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
$itNews      = "Brèves";
$itRights    = "Privilèges";
$itParam     = "Paramètres";
$itEmpty     = "Vider";
$itCtl       = "Controle";

// Titres des fenetres
$fEditPostit   = "Edition de postit";
$tEvents    = "Gestion des tournois";
$tNewEvent  = "Création d'un tournoi";
$tEditEvent = "Modification d'un tournoi";
$tNewNew    = "Création d'une bréve";
$tEditNew   = "Modification d'une bréve";
$tEditRight = "Modification des droits";
$tAddRight  = "Affectation des droits";
$tDelRight  = "Suppression de privilèges";
$tDelNews   = "Suppression de nouvelles";
$blkInfo    = "Informations générales";
$tPdf       = "Documents PDF";
$tBadges    = "Badges";
$tMain      = "Présentation";
$tFees      = "Frais d'inscription";
$tGeneral   = "Infos générales";

// Titre des colonnes
$rowsEvents1  = "Date";
$rowsEvents2  = "Nom";
$rowsEvents3  = "Visites";
$rowsEvents4  = "Id";
$rowsEvents5  = "Type";

$rowsRights1  = "Login";
$rowsRights2  = "Identité";
$rowsRights3  = "Pseudonyme";
$rowsRights4  = "Privilège";

$rowsNews1   = "Date";
$rowsNews2   = "Nouvelle";
$rowsNews3   = "Page";

$private = "Tournoi privée";
$hidden  = "Tournoi non publiée";
$visible = "Tournoi publiée";
$restore = "Tournoi supprimée";

// Label des zones
$evntNature1  = "Tournoi individuel";
$evntNature2  = "Interclub/Par équipe";
$evntNature3  = "Championnat";
$evntNature4  = "Trophée";
$evntNature5  = "Intercodep";
$evntNature6  = "Autre";

$evntLevel1      = "Club";
$evntLevel2      = "Régional";
$evntLevel3      = "National";
$evntLevel4      = "Départemental";
$evntLevel5      = "Inter département";
$evntLevel6      = "Inter région";
$evntLevel7      = "International";

$evntCatage1 = "Tournoi adultes";
$evntCatage2 = "Tournoi jeunes";
$evntCatage3 = "Tournoi jeunes et adultes";


$newsText   = "Brève :";
$newsPage   = "Page à afficher :";
$users   = "Utilisateurs";

$right1 = 'Visiteur';
$right2 = 'Invité';
$right3 = 'Ami';
$right4 = 'Assistant';
$right5 = 'Gestionnaire';
$right6 = 'Juge arbitre';

$titleFont = "Police du titre :";
$titleSize = "Taille du titre :";
$badgeId   = "Modèle de badge :";
$skin      = "Interface graphique :";
$logoTop  = "Position logo haut :";
$logoLeft  = "Position logo gauche :";
$logoWidth = "Largeur logo :";
$logoHeight  = "Hauteur logo :";
$updateAll  = "Calculer les frais pour tous les joueurs";
$matchOnly  = "Se baser sur les matchs à la place des inscriptions";

$psitTitle  = "Titre :";
$psitTexte  = "Texte :";

$sendNew   = "Envoyer la nouvelle aux abonnés";
$sendRight = "Notifier l'utilisateur des se privilèges";

$urlStream    = "Url pour la vidéo :";
$urlLiveScore = "Url pour le live scoring :";

// Messages d'erreur
$msgNoNews   = "Pas de nouvelle pour ce tournoi";
$msgNoUsers  = "Pas d'utilisateur privilégié";
$msgNeedNews = "Vous devez sélectionner au moins une nouvelle à supprimer.";
$msgConfirmDelNews = "Les nouvelles sélectionnées vont être supprimées.";
$msgNeedRights     = "Vous devez sélectionner au moins un utilisateur dont les privilèges vont être supprimés.";
$msgConfirmDelRights = "Les privilèges des utilisateurs sélectionnés vont être supprimés.";
$msgnewsText  = "Le texte de la nouvelle doit être renseigné";
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
$btnOffline  = "Dé-activer inscription en  ligne";
$btnEmptyCache  = "Vider le cache";

?>