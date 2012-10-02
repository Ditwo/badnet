<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
//require_once 'Oevenmeta.inc';

class OEventmeta extends Object
{
    var $_pathLogo = '../img/poster/'; 
	
	/**
	 * Constructeur
	 * @param	integer	$aEventId	Identifiant du tournoi
	 * @return OEvent
	 */
	public function __construct($aEventId=-1)
	{
		if ($aEventId>0) $this->load('eventsmeta', 'evmt_eventid=' . $aEventId);
	}

	public function getLogo()
	{
		return $this->_pathLogo . $this->getVal('logo');
	}
	
	/**
	 * Ajout du logo pdf
	 */
	public function addLogo($aTmpImgFile, $aName)
	{
		$this->deleteLogo();
		$name = $this->getVal('eventid') . '_' . $aName;
		$dest = $this->_pathLogo . $aName;
		move_uploaded_file($aTmpImgFile, $dest);
	    chmod($dest, 0777);	      
		$this->setVal('logo', $aName);
		$this->save();
		return false;
	}
	
	/**
	 * Supression du logo pdf
	 */
	public function deleteLogo()
	{
		$logo = $this->getVal('logo');
		unlink( $this->_pathLogo . $logo);
		$this->setVal('logo', '');
		$this->save();
		return false;
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
		$where = 'evmt_id=' . $this->getVal('id', -1);
		$id = $this->update('eventsmeta', 'evmt_', $this->getValues(), $where);
		return $id;
	}
	
}
?>