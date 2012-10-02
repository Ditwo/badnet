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
		$q->setFields('mber_id');
		if ( !empty($aCriteria['license']) )
		{
			$q->addWhere('mber_licence=' . $aCriteria['license']);
			$memberIds = $q->getCol();
		}
		if ( empty($memberIds) )
		{
			$q->setFields('mber_id'); // Pour vider la clause where
			if ( !empty($aCriteria['famname']) )
			$q->addWhere("mber_secondname='%" . $aCriteria['famname'] . "%'");
			if ( !empty($aCriteria['firstname']) )
			$q->addWhere("mber_firstname='%" . $aCriteria['firstname'] . "%'");
			$memberIds = $q->getCol();
		}
		return $memberIds;
	}

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
	 * Obtenir les inscriptions du membre pour un tournoi
	 *
	 * @param unknown_type $aEventId
	 * @param unknown_type $aType
	 * @return unknown
	 */
	public function getRegis($aEventId = -1, $aType = OREGI_TYPE_PLAYER)
	{
		$q = new Bn_query('registration');
		$q->setFields('regi_id');
		$q->addWhere('regi_memberid=' . $this->getVal('id', -1));
		if ($aEventId > 0) $q->addwhere('regi_eventid=' . $aEventId);
		if ($aType > 0) $q->addwhere('regi_type=' . $aType);
		$ids = $q->getCol();
		unset($q);
		return $ids;
	}

	/**
	 * Constructeur
	 * @param	integer	$aMemberId	Identifiant badnet du membre
	 * @param	integer	$apoonaId   Identifiant poona du membre
	 * @param	string 	$aLicense	License du membre
	 * @return OMember
	 */
	public function __construct($aMemberId=-3, $aPoonaId=-1, $aLicense=null)
	{
		$this->setVal('fedeid', -1);
		if($aMemberId > 0) $where = 'mber_id=' . $aMemberId;
		else if( $aPoonaId > 0 ) $where = 'mber_fedeid='.$aPoonaId;
		else if( !empty($aLicense))
		{
			$isSquash = Bn::getConfigValue('squash', 'params');
			if ( $isSquash )
				$where = "mber_licence = '" . $aLicense . "' AND mber_fedeid>0";
			else
				$where = sprintf("mber_licence = '%08d'", $aLicense) . " AND mber_fedeid>0";
		}
		else return;
		$this->load('members', $where);
		// Si non  trouve, recherche dans Poona
		if ($this->getVal('id', -3) == -3)
		{
			$oPoona = Oexternal::factory();
			$member = $oPoona->getMember($aLicense, $aPoonaId);
			if ( !empty($member) )
			{
				$q = new Bn_query('members');
				$q->setValue('mber_fedeid', $member['fedeid']);
				$q->addValue('mber_secondname', $member['familyname']);
				$q->addValue('mber_firstname', $member['firstname']);
				$q->addValue('mber_licence', $member['license']);
				$q->addValue('mber_sexe', $member['gender']);
				$q->addValue('mber_born', $member['born']);
				$id = $q->addRow();

				// Identifiant unique du membre
				$q->setWhere('mber_id=' . $id);
				$uniId = Bn::getUniId($id);
				$q->addValue('mber_uniid', $uniId);
				$q->updateRow();
				$this->load('members', 'mber_id=' . $id);

			}
			unset($oPoona);
		}

		// Si l'id poona est vide, verifier dans la base federale pour faire le lien
		$fedeid = $this->getVal('fedeid');
		if ( empty($fedeid) && $this->getVal('id', -3) > 0)
		{
			$oPoona = new Opoona();
			$member = $oPoona->getMember($aLicense, $aPoonaId);
			if ( !empty($member) ) $this->setVal('fedeid', $member['fedeid']);
			else $this->setVal('fedeid', -1);
			unset($oPoona);
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
