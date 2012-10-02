<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Bn/Page.php';

class Page extends Bn_balise
{
	/**
	 * Constructeur
	 * @return Bn_balise
	 */
	public function __construct($aProject=null)
	{
		// Verifier l'existence de la base de donnees
		$q = new Bn_query();
		if ($q->isError())
        {
            Bn::setUserMsg($q->getMsg());
            Bn::log($q->getMsg());
        }
		else if ( !$q->existsDatabase() )
		{
			$q->loadFile();
		}

		// Initialisation de la page
		$locale = BN::getLocale();

		$theme  = Bn::getValue('theme', BN::getConfigValue('theme', 'params'));
		Bn::setValue('theme', $theme);

		$page = Bn_page::getPage();
		$page->setTitle(Bn::getConfigValue('title', 'params'));
		$page->setLang($locale);

		// Ajout des css
		$fileName = "../Themes/$theme/Badnet.css";
		$page->addStyleSheet($fileName, 'text/css', 'screen');

		// Ajout des scripts
		$fileName = "Badnetlib/Page.js";
		$page->addScript($fileName);

		$fileName = "$aProject/$aProject.js";
		if ( file_exists($fileName) )
		{
			//$page->addScript($fileName);
		}

		// Fichier langue communs
		$fileName = "$aProject/Locale/{$locale}/Commun.inc";
		if ( file_exists($fileName) )
		{
			//require_once $fileName;
		}

		// Creation de la div principale de la page
		parent::__construct('div', 'targetBody');
	}

	/**
	 * Fonction d'affichage de la page
	 * @return void
	 */
	public function display()
	{
		@include_once 'Accueil/Main/Main.inc';
		$isSquash = Bn::getConfigValue('squash', 'params');

		// Creation du container de la page : toute la largeur de la fenetre
		// 3 div pour pouvoir faire un titre extensible
		$container = new BN_Balise('div', 'divContainer');
		$container->setAttribute('class', 'container');
		$containerL = $container->addDiv('divContainerL', 'containerL');
		$containerR = $containerL->addDiv('divContainerR', 'containerR');

		// Contenu de la page : pas sur toute la largeur: voir css
		$content = $containerR->addDiv('divContent', 'content');

		// Entete : largeur du contenu
		$head = $content->addDiv('divHeader');
		$theme = Bn::getValue('theme', 'Badnet');
		$url = $head->addLink('lnkHome', "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");

		if ($isSquash)
		{
			$url->addImage('imgBadnet', 'Squashnet.png', 'Squashnet');
			$head->addP('pSlogan', 'La promotion du Squash par le net');
		}
		else
		{
			$url->addImage('imgBadnet', 'Badnet.png', 'BadNet');

		}
		/*
		 $actions = array(MAIN_FR, MAIN_UK, MAIN_DE);
		 $texts = array('', '', '');
		 $loc = $head->addList('lstMenuLocale', $texts, $actions, 1, 'targetBody');
		 */
		$head->addBreak();

		// Menu : largeur du contenu
		// rempli par ajax
		$menu = $content->addDiv('targetMenu');

		// Contenu
		// rempli par ajax
		$body  = $content->addDiv('divBody', 'contentBody');
		$body->addContent($this, false);

		// Pied de page : pas dans le contenu pour qu'il prenne
		// toute la largeur de la fenetre
		$foot = $container->addDiv('divFoot');
		$div = $foot->addDiv('divFootBody');
		$div->addP('pVersion', Bn::getConfigValue('version', 'params'));
		$text = Bn::getValue('user_name');
		$event = Bn::getValue('evnt_name', null);
		if ( !empty($event) )
		{
			$text .= '::' . $event;
		}
		if (Bn::getValue('user_name') != Bn::getValue('loginName'))
		{
			//require_once 'Badnetadm/Users/Users.inc';
			//$div->addLink('lnkLogin', USERS_SELECT_USER, Bn::getValue('loginName'));
			$text .= '-user log in=' . Bn::getValue('loginName');
		}
		$div->addP('pUser', $text);
		$div->addBreak();

		//--- Dialog pour saisie
		$dlg = $menu->addDialog('dlg', '', 800, 380);
		$dlg->addDiv('targetDlg', 'badnetDlg');

		$page = BN_Page::getPage();
		$page->addBodyContent($container);
		$page->display();
	}

}

?>
