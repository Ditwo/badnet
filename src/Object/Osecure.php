<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Oevent.inc';

class OSecure extends Object
{
	// {{{ properties
	// }}}

	/**
	 * Acces au singleton d'un tournoi
	 * @param	integer	$aUserId	Identifiant de l'utilisateur
	 * @param	integer	$aEventId	Identifiant du tournoi
	 * @return OEvent
	 */
	public function isEventAdmin($aUserId, $aEventId)
	{
		if (Oaccount::isLoginAdmin()) return true;
		
		$q = new Bn_query('rights');
		$q->setFields('rght_status');
		$q->addWhere('rght_theme=2');
		$q->addWhere('rght_themeid=' . $aEventId);
		$q->addWhere('rght_userid=' . $aUserId);
		$q->addWhere("rght_status='" . OEVENT_RIGHT_MANAGER ."'");
		return is_null($q->getOne());
	}
}
?>