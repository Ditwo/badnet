<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

class Accueil
{
	public function __construct($aAction)
	{
		$controller = Bn::getController();
		$controller->addModule('Main',       ACCUEIL_MAIN,       ACCUEIL_SQUASH_IDF);
		$controller->addModule('Squashidf',  ACCUEIL_SQUASH_IDF, ACCUEIL_SQUASH_FFS);
		$controller->addModule('Squashffs',  ACCUEIL_SQUASH_FFS, ACCUEIL_LIFB);
		$controller->addModule('Lifb',       ACCUEIL_LIFB,       ACCUEIL_SQUASH_NC);
		$controller->addModule('Squashnc',   ACCUEIL_SQUASH_NC,  ACCUEIL_DISTRIB);
		$controller->addModule('Distrib',    ACCUEIL_DISTRIB,    ACCUEIL_END);
	}

}
?>
