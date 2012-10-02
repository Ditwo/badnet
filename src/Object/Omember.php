<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Omember.inc';
require_once 'Object/Oregi.inc';
$locale = BN::getLocale();
require_once 'Object/Locale/' . $locale . '/Omember.inc';


class OMember extends Object
{
	/**
	 * Inscrit un membre a une equipe d'un tournoi
	 *
	 * @param unknown_type $aTeamId
	 */
	public function register($aTeam, $aType = OREGI_TYPE_PLAYER)
	{
		$famname = $this->getVal('secondname');
		$firstname = $this->getVal('firstname');
		$oRegi = new Oregi();

		// Enregistrement de l'inscription
		$oRegi->setVal('memberid',  $this->getVal('id', -1));
		$oRegi->setVal('longname',  $famname . ' ' . $firstname);
		$oRegi->setVal('shortname', $famname . ' ' . $firstname[0] . '.');
		$oRegi->setVal('type',      $aType);
		$oRegi->setVal('teamid',    $aTeam->getVal('id'));
		$oRegi->setVal('date',      date('Y-m-d H:M'));
		$oRegi->setVal('eventid',   $aTeam->getVal('eventid'));
		$oRegi->setVal('accountid', $aTeam->getVal('accountid'));
		$id = $oRegi->save();

		// Identifiant unique de l'inscription
		$q = new Bn_query('registration');
		$q->setWhere('regi_id=' . $id);
		$uniId = Bn::getUniId($id);
		$q->addValue('regi_uniid', $uniId);
		$q->updateRow();
		
		$oRegi->setVal('uniid', $uniId);
		return $oRegi;
	}
	
		/**
	 * Obtenir les inscriptions du membre pour une equipe
	 *
	 * @param unknown_type $aTeamId
	 * @param unknown_type $aType
	 * @return unknown
	 */
	public function getTeamRegis($aTeamId = -1, $aType = OREGI_TYPE_PLAYER)
	{
		$q = new Bn_query('registration');
		$q->setFields('regi_id');
		$q->addWhere('regi_memberid=' . $this->getVal('id', -1));
		if ($aTeamId > 0) $q->addwhere('regi_teamid=' . $aTeamId);
		if ($aType > 0) $q->addwhere('regi_type=' . $aType);
		$ids = $q->getCol();
		unset($q);
		return $ids;
	}
	
	
	/**
	 * Recherche d'un membre : a partir du numero de licence en priorite
	 * sinon a partir des autres criteres renseignes
	 *
	 * @param array $aCriteria license, famname, firstname
	 * @param boolean $aCreate
	 */
	static public function search($aCriteria)
	{
		$memberIds = array();
		$q = new Bn_query('members');
		if ( !empty($aCriteria['license']) )
		{
			$q->setFields('mber_id');
			$q->addWhere('mber_licence=' . $aCriteria['license']);
			$memberIds = $q->getCol();
		}
		if ( empty($memberIds) )
		{
			$q->setFields('mber_id'); // Pour vider la clause where
			if ( !empty($aCriteria['famname']) )
			$q->addWhere("mber_secondname = '" . trim($aCriteria['famname']) . "'");
			if ( !empty($aCriteria['firstname']) )
			$q->addWhere("mber_firstname = '" . trim($aCriteria['firstname']) . "'");
			$memberIds = $q->getCol();
		}
		return $memberIds;
	}

	/**
	 * Obtenir les inscriptions du membre pour un tournoi
	 *
	 * @param unknown_type $aEventId
	 * @param unknown_type $aType
	 * @return unknown
	 */
	public function getRegis($aEventId = -1, $aType = OREGI_TYPE_PLAYER)
	{
		$q = new Bn_query('registration');
		$q->setFields('regi_id, regi_teamid');
		$q->addWhere('regi_memberid=' . $this->getVal('id', -1));
		if ($aEventId > 0) $q->addwhere('regi_eventid=' . $aEventId);
		if ($aType > 0) $q->addwhere('regi_type=' . $aType);
		$rows = $q->getRows();
		unset($q);
		return $rows;
	}
	
	
	/**
	 * Constructeur
	 * @param	integer	$aMemberId	Identifiant badnet du membre
	 * @return OMember
	 */
	public function __construct($aMemberId=-3, $aLicence=null)
	{
		if($aMemberId > 0)
		{
			$where = 'mber_id=' . $aMemberId;
			$this->load('members', $where);
		}
		else if ( !empty($aLicence))
		{
			$where = "mber_licence='" . $aLicence . "'";
			$this->load('members', $where);
		}
		$sexe = $this->getVal('sexe', OMEMBER_GENDER_MALE);
		$this->setVal('labsexe', constant('SMA_LABEL_' . $sexe));
	}

	public function save()
	{
		$memberId = $this->getVal('id', -3);
		$where = 'mber_id=' . $memberId;
		$id = $this->update('members', 'mber_', $this->getValues(), $where);

		// Identifiant unique du membre
		if ($memberId == -3)
		{
			$where = 'mber_id=' . $id;
			$uniId = Bn::getUniId($id);
			$this->setVal('uniid', $uniId);
			$this->update('members', 'mber_', $this->getValues(), $where);
		}

		return $id;
	}

	/**
	 * Suppression d'un membre
	 *
	 * @param unknown_type $aMemberId
	 */
	public function delete($aMemberId)
	{
		if ($aMemberId <= 0 ) return;
		// Verification qu'il n'y a pas d'inscription a des tournois
		$q = new Bn_query("registration");
		$q->setFields('count(*)');
		$q->addWhere('regi_memberid=' . $aMemberId);
		$nb = $q->getFirst();
		if ( $nb ) return false;

		// Suppression du membre
		$q->setTables('members');
		$q->deleteRow('mber_id=' . $aMemberId);
		return;
	}
}
?>
