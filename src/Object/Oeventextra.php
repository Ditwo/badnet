<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Oeventextra.inc';
//$locale = BN::getLocale();
//require_once 'Locale/' . $locale . '/Oeventextra.inc';

class OEventextra extends Object
{
	
	/**
	 * Constructeur
	 * @param	integer	$aEventId	Identifiant du tournoi
	 * @return OEvent
	 */
	public function __construct($aEventId=-1)
	{
		$this->setVal('captain', NO);
		$this->setVal('allowaddplayer', OEVENTEXTRA_DELAY_PARAM);
		$this->setVal('delayaddplayer', 3);
		$this->setVal('licenseonly', YES);
		$this->setVal('licensetype', '');
		$this->setVal('licensecatage', '6;7;301;302;303;304;305;306;307');
		$this->setVal('multiteamplayer', NO);
		$this->setVal('multiassoteam', NO);
		$this->setVal('delaycaptain', 5);
		if ($aEventId>0) $this->load('eventsextra', 'evxt_eventid=' . $aEventId);
		$this->setVal('eventid', $aEventId);
	}

	/**
	 * Destructeur
	 */
	public function __destruct()
	{
		parent::__destruct();
	}

	public function save()
	{
		$where = 'evxt_id=' . $this->getVal('id', -1);
		$id = $this->update('eventsextra', 'evxt_', $this->getValues(), $where);
		return $id;
		
	}
	
}
?>