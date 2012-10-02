<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

class Badnetteam
{
	public function __construct()
	{
		$controller = Bn::getController();
		// Declaration des modules
		$controller->addModule('Tevent',     BADNETTEAM_EVENT, BADNETTEAM_TRANSFERT);
		$controller->addModule('Ttransfert', BADNETTEAM_TRANSFERT, BADNETTEAM_END);
	}
	
}
