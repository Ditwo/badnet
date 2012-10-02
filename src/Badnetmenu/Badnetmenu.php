<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

require_once "Badnetmenu.inc";

class Badnetmenu
{
	// {{{ properties
	// }}}

	public function __construct()
	{
		$controller = Bn::getController();
		// Declaration des modules
		$controller->addModule('Menu',    BNETMENU_MENU,     BNETMENU_DIALOG);
		$controller->addModule('Dialog',  BNETMENU_DIALOG,   BNETMENU_END);
	}

}