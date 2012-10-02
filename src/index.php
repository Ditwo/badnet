<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
$sessionPath = '../Temp/Sessions';
session_save_path($sessionPath);
session_start();
error_reporting(E_ALL);

require_once 'Bn/Bn.php';
require_once 'Badnetlib/Body.php';
require_once 'Object/Object.inc';

// Mise a jour du format de l'heure en fonction de la langue
$lang = Bn::GetLocale();
if ($lang=='fra' || $lang=='Fr') setlocale(LC_TIME, 'fr_FR.UTF8', 'fr.UTF8', 'fr_FR.UTF-8', 'fr.UTF-8', 'fr', 'fra', 'fr_FR');
if ($lang=='eng') setlocale(LC_TIME, 'en_EN','en');
if ($lang=='de') setlocale(LC_TIME, 'de_DE','en');

function shutdown()
{
        // Voici notre fonction shutdown
        // dans laquelle nous pouvons faire
        // toutes les dernières opérations
        // avant la fin du script.

        echo 'Script exécuté avec succès', PHP_EOL;
        exit();
}

// Recuperer l'emplacement de ce fichier pour pouvoir inclure
// d'autre fichier en chemin relatif.
define('CUR_DIR', dirname(__FILE__));

// Inclusion des utilitaires generaux
require_once "kpage/kpage.php";
require_once "utils/utvars.php";
require_once "utils/utbase.php";

$theme  = Bn::getValue('theme', BN::getConfigValue('theme', 'params'));
Bn::setValue('theme', $theme);


//if ($page == '' || $page == "inst")
{
  require_once "inst/inst.php";
  $a = new Inst();
  $version = '$Name:  $';
  $a->start($version);
}


$oldAction = Bn::getValue('kpid', null);
// Nouvelle architecture. remplacement progressif
if ( empty($oldAction) || $oldAction=='inst')
{
	define ('BADNETLIB',            0x000000);
	define ('PROJECT_ACCUEIL',      0x010000);
	define ('PROJECT_BADNETADM',    0x020000);
	define ('PROJECT_BADNET',       0x030000);
	define ('PROJECT_BADNETTEAM',   0x040000);
	define ('PROJECT_END',          0xFF0000);

	@include_once 'Badnetmenu/Menu/Menu.inc';
	require_once 'Badnetlib/Badnetlib.inc';
	require_once 'Accueil/Accueil.inc';
	set_error_handler(array('Bn', 'errorHandler'), E_ALL);
		
	//register_shutdown_function('shutdown');
	//set_exception_handler(array('Bn', 'exceptionHandler'));
	$defaultPage = Bn::getConfigValue('accueil', 'params');
	$ajax = Bn::getValue('ajax', false);
	$xls = Bn::getValue('xls', false);
	if ( $ajax == false && $xls==false)
	{
		require_once "Badnetlib/Page.php";
		$container = new Page();
		$container->setAction(Bn::getValue('bnAction', $defaultPage));
		foreach($_GET as $key=>$value) $container->addMetadata($key, "'$value'");
		foreach($_POST as $key=>$value) $container->addMetadata($key, "'$value'");
		$corner = Bn::getConfigValue('corner', 'params');
		$container->addJQReady("fillTrame($corner);");
		$container->display();
		exit;
	}


	// Traitement des appels aux services
	$service = Bn::getValue('bnService', null);
	if (!empty($service))
	{
		require_once 'Services/Server.php';
		exit;
	}
	$controller = Bn::getController();
	$controller->addProject('Badnetlib',   BADNETLIB,           PROJECT_ACCUEIL);
	
	$controller->addProject('Accueil',     PROJECT_ACCUEIL,     PROJECT_BADNETADM);
	$controller->addProject('Badnetadm',   PROJECT_BADNETADM,   PROJECT_BADNET);
	$controller->addProject('Badnet',      PROJECT_BADNET,      PROJECT_BADNETTEAM);
	$controller->addProject('Badnetteam',  PROJECT_BADNETTEAM,     PROJECT_END);
	
	$controller->setDefaultAction($defaultPage);
	$controller->run();
	exit();
}


// Premier chargemement ou installation: verification de la
// version de la base de donnes et mise a jour
$page = kform::getPageId();
$act  = kform::getActId();

// Verification de la connexion
require_once "users/cnx.php";
$cnx = new cnx();
//echo "page=$page;act=$act;";
$cnx->start($page, $act);

// Initialisation des donnees globales du tournoi
utvars::init();

// Ventilation en fonction de la page et de la commande
$userLevel = utvars::getAuthLevel();
//echo "userLevel=$userLevel";

while (true)
{
	$page = kform::getPageId();
	$act  = kform::getActId();
	if ($act == KID_HOME || $page == 'cnx')
	{
		$act  = KID_DEFAULT;
		$page = 'main';
	}

	// Pour les visituer anonyme, verification que la page demandee
	// n'est pas dans la cache
	if (utvars::_getSessionVar('userAuth') == WBS_AUTH_VISITOR)
	{
		$eventId = utvars::getEventId();
		$data  = kform::getData();
		$sort  = kform::getSort('','');
		$lang = utvars::getLanguage();
		$cache = "../cache/{$lang}_{$eventId}_{$page}_{$act}";
		if ($data  != '')
		$cache .= "_{$data}";
		if ($sort  != '')
		$cache .= "_{$sort}";
		$cache .= ".htm";
		if (is_file($cache))
		{
	  include "$cache";
	  exit;
		}
	}

	// Pour la partie administration, les scripts d'entree
	// sont de la forme <page>Amd_<niveau>.php
	// sinon ils sont de la forme <page>_<niveau>.php
	// avec niveau : A administrateur
	//               S assistant
	//               V visiteur
	if (utvars::getSectorId() == WBS_SECTOR_ADMIN)
	{
		$authForm = "{$page}Adm_{$userLevel}";
		$file = "$page/$authForm.php";
		if (!file_exists($file))
		$authForm = "{$page}_{$userLevel}";
	}
	else
	$authForm = "{$page}_{$userLevel}";
	$file = "$page/$authForm.php";
	//echo "file = $file<br>";
	if (file_exists($file))
	{
		require_once "$file";
		$a = new $authForm();
		$a->start($act);
	}
	// The form doesn't exist, so try the form for Visitor
	else
	{
		$authForm = $page."_".WBS_AUTH_VISITOR;
		$file = "$page/$authForm.php";
		//echo "fileVisitor = $file<br>";
		if (file_exists($file))
		{
			require_once "$file";
			$a = new $authForm();
			$a->start($act);
		}
		else
		{
			//echo "Unknow form $file";
			$cnx->start('main', KID_LOGOUT);
			exit;
		}
	}
}
?>
