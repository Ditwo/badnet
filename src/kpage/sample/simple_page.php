<?php
/*--------------------------------------------
 * Ceci est un exemple simple de l'utilisation 
 * de la classe kPage pour la creation d'une 
 * page HTML. 
 *
 * Cet exemple utilise les fonctions les plus
 * simples; pour la creation d'une page avec 
 * un formulaire voir l'exemple form_page.php
 *
 * Le but est de creer une page simple dont le
 * contenu est limite a une largeur de 800 px
 * et centre dans la fenetre du navigateur.
 *
 *-------------------------------------------*/

// Faire remonter toutes les erreurs
error_reporting(E_ALL);

// Inclure la kpage
require_once "../kpage.php";

// Creation d'une nouvelle page
$kPage = new kPage('sample');

// Preciser le fichier contenant les chaines 
// de caractères
$kPage->setStringFile('./string.inc');

// Ajouter un fichier de style
$kPage->addStyleFile('./sample.css');

// Ajouter une division principale.
// La feuille de style permettra de la 
// centrer dans la fenetre du navigateur.
// ATTENTION: remarquer le =&
// Une erreur est l'oubli du &
$kDiv =& $kPage->addDiv('page');

// Ajouter des elements
$kDiv->addMsg('titre', 'Ma première page');

// Affichage de la page
$kPage->display();
?>