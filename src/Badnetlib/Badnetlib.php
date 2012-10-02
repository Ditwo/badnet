<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

require_once  "Badnetlib/Badnetlib.inc";

//$locale    = Bn::getLocale();
//require_once  "Badnetadm/Locale/{$locale}/Commun.inc";

class Badnetlib
{
	public function __construct()
	{
		$controller = Bn::getController();

		// Ajout des actions globales (ie accessible depuis toutes les pages)
		// Remplissage de la grid classement d'une poule IC  
		$controller->addAction(BADNETLIB_ROUND_FILL_RANKING, $this, 'roundFillRanking'); 
		$controller->addAction(BADNETLIB_ROUND_FILL_GROUP, $this, 'roundFillGroup'); 
	}
	
	/**
	 * Remplissage de la grid classement d'une poule IC 
	 *
	 * @return void
	 */
	public function roundFillRanking()
	{
		$roundId = Bn::getValue('roundId');
		$oRound = new Ogroup($roundId);
		$oRound->fillRanking();
		unset($oRound);
		return false;
	}

	/**
	 * Remplissage de la grid affichage d'une poule IC 
	 *
	 * @return void
	 */
	public function roundFillGroup()
	{
		$roundId = Bn::getValue('roundId');
		$oRound = new Ogroup($roundId);
		$oRound->fillGroup();
		unset($oRound);
		return false;
	}
}	
?>