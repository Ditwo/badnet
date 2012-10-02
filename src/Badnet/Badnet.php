<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once "Badnetadm/Badnetadm.inc";

class Badnet
{
	public function __construct()
	{
		// Verification des autorisations
		$needLog = Bn::getConfigValue('auth', 'Badnetlocal');
		if ($needLog && ! Oaccount::isLogin()) return BNETADM_DISPATCH;
		
		$eventId = Bn::getValue('eventId', Bn::getValue('event_id', -1));
		//if ($eventId < 1 ) return BNETADM_DISPATCH;
		
		Bn::setValue('event_id', $eventId); 
		$controller = Bn::getController();
		// Declaration des modules
		$controller->addModule('Event',     BADNET_EVENT,      BADNET_CAPTAIN);
		$controller->addModule('Captain',   BADNET_CAPTAIN,    BADNET_TRANSFERT);
		$controller->addModule('Transfert', BADNET_TRANSFERT,  BADNET_ADM);
		$controller->addModule('Adm',       BADNET_ADM,        BADNET_PUBLI);
		$controller->addModule('Publi',     BADNET_PUBLI,      BADNET_TEAM);
		$controller->addModule('Team',      BADNET_TEAM,       BADNET_PREFERENCE);
		$controller->addModule('Preference',BADNET_PREFERENCE, BADNET_DIV);
		$controller->addModule('Div',       BADNET_DIV,        BADNET_END);
		
	}
	
}
