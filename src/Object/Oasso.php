<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Oasso.inc';
$locale = BN::getLocale();
require_once 'Locale/' . $locale . '/Oasso.inc';


class Oasso extends Object
{

	/**
	 * Recherche d'une association
	 * sinon a partir des autres criteres renseignes
	 *
	 * @param array $aCriteria license, famname, firstname
	 * @param boolean $aCreate
	 */
	static public function search($aCriteria)
	{
		$assoIds = array();
		$q = new Bn_query('assocs');
		$q->setFields('asso_id');
		foreach($aCriteria as $critere=>$value)
		{
			$q->addWhere('asso_' . $critere . "='" . $value ."'");
		}
		$assoIds = $q->getCol();
		return $assoIds;
	}


	/**
	 * Constructeur
	 */
	public function __construct($aAssoId = -1, $aInstanceId = -1, $aForcePoona=false)
	{
		if ($aAssoId > 0) $this->_loadBadnetAsso($aAssoId);
		else if ($aInstanceId > 0)$this->_loadPoonaAsso($aInstanceId, $aForcePoona);
	}

	/**
	 * Liste de valeur des types d'assos
	 * @return array
	 */
	public function getLovType()
	{
		for ($i=OASSO_TYPE_FEDE; $i<=OASSO_TYPE_CORPO; $i++)
		{
			$types[$i] = constant('LABEL_' . $i);
		}
		return $types;
	}

	/**
	 * Liste de valeur des pays
	 * @return array
	 */
	public function getLovNoc()
	{
		$nocs['BEL'] = 'BEL';
		$nocs['DEN'] = 'DEN';
		$nocs['ENG'] = 'ENG';
		$nocs['ESP'] = 'ESP';
		$nocs['FRA'] = 'FRA';
		$nocs['GER'] = 'GER';
		$nocs['IRL'] = 'IRL';
		$nocs['ITA'] = 'ITA';
		$nocs['LUX'] = 'LUX';
		$nocs['NED'] = 'NED';
		$nocs['POR'] = 'POR';
		$nocs['SCO'] = 'SCO';
		$nocs['SUI'] = 'SUI';
		$nocs['SWE'] = 'SWE';
		$nocs['WAL'] = 'WAL';
		return $nocs;
	}

	/**
	 * Rempli les donnees de l'association depuis poona
	 */
	private function _loadPoonaAsso($aInstanceId)
	{
		// Recherche dans BadNet si elle existe deja
		$q = new Bn_query('assocs');
		$q->addField('asso_id',     'id');
		$q->addWhere('asso_fedeid=' . $aInstanceId);
		$assoId = $q->getFirst();
		if ( !empty($assoId) )
		{
			$this->_loadBadnetAsso($assoId);
			return;
		}
			
		// Chercher dans poona
		$o = Oexternal::factory();
		$asso = $o->getInstance($aInstanceId);
		if ( empty($asso) )	$this->_error('Oasso::Association poona inconnue');
		else $asso['labtype'] = constant('LABEL_' . $asso['type']);
		$this->setValues($asso);
		return $this->saveAssoc();
	}

	/**
	 * Rempli les donnees de l'association depuis badnet
	 */
	private function _loadBadnetAsso($aAssoId)
	{
		$this->load('assocs', 'asso_id=' . $aAssoId);
		$this->setVal('labtype', constant('LABEL_' . $this->getVal('type')) );
	}

	public function saveAssoc()
	{
		$fedeId = $this->getVal('fedeid', -1);
		if ($fedeId > 0)
		{
			$where = 'asso_type=' . $this->getVal('type');
			$where .= ' AND asso_fedeid=' . $this->getVal('fedeid');
			$this->delVal('id');
		}
		else $where = null;
		return $this->save($where);
	}

	/**
	 * Enregistre en bdd les donnees de l association
	 */
	public function save($aWhere = null)
	{
		$assoId = $this->getVal('id', -1);
		if ( empty($aWhere) ) $where = 'asso_id=' . $this->getVal('id', -1);
		else $where = $aWhere;

		// Traitement du logo
		$logo = $this->getVal('logo');
		// Le logo est externe: on le ramene sur le site
		if (!empty($logo) && (substr($logo, 0, 7) == 'http://') )		
		{
			$fileName = basename($logo);
			$file = '../img/logo_asso/' . $fileName;
			$res = copy($logo, $file);
			if ($res === true) $this->setVal('logo', $fileName);
		}
		
		// Sauvegarde de l'association
		if ($assoId == -1) $this->delVal('id');
		$id = $this->update('assocs', 'asso_', $this->getValues(), $where);

		// Mise a jour de l'identifiant unique
		$uniId = $this->getVal('uniid');
		if (  empty($uniId) )
		{
			// Identifiant unique de l'association
			$where = 'asso_id=' . $id;
			$uniId = Bn::getUniId($id);
			$this->setVal('uniid', $uniId);
			$id = $this->update('assocs', 'asso_', $this->getValues(), $where);
		}

		
		return $id;
	}

	/**
	 * Renvoi le manager d'une association a partir
	 * @return 	array	Donnees du compte manager
	 */
	public function getManager()
	{
		$assoId = $this->getValue('id', -1);
		if ( $assoId > 0 )
		{
			$q = new BN_query('rights');
			$q->addWhere('rght_theme =' . THEME_ASSOS);
			$q->addWhere("rght_status = '"  . AUTH_MANAGER . "'");
			$q->addWhere('rght_themeid = ' . $this->getval('id',-1));
			$q->setFields('rght_userid');
			$userId = $q->getFirst();
		}
		if ( empty($userId) ) $userId = -1;

		// Champs a recuperer
		return new Oaccount($userId);
	}

	/**
	 * Renvoi l'adherent d'une association a partir
	 * @return 	array	Donnees du compte manager
	 */
	public function getAdhe()
	{
		$assoId = $this->getValue('id', -1);
		if ( $assoId > 0 )
		{
			$q = new BN_query('rights');
			$q->addWhere("rght_status = '"  . AUTH_MANAGER . "'");
			$q->addWhere('rght_themeid = ' . $this->getval('id',-1));
			$q->addWhere('rght_theme =' . THEME_ASSOS);
			$q->setFields('rght_userid');
			$userId = $q->getFirst();
		}
		if ( empty($userId) ) $userId = -1;

		// Champs a recuperer
		return new Oadhe($userId);
	}

	/**
	 * Enregistre le manager d'une assocation
	 * @param  int   Identifiant du compte manager
	 */
	public function setManager($aUserId)
	{
		// Creer la relation entre l'association et le compte
		$q = new BN_query('rights');
		$q->setValue('rght_themeid', $this->getVal('id', -1));
		$q->addValue('rght_theme', THEME_ASSOS);
		$q->addValue('rght_status', AUTH_MANAGER);
		$q->addValue('rght_userid', $aUserId);
		$q->addWhere("rght_status ='".  AUTH_MANAGER . "'");
		$q->addWhere("rght_theme =".  THEME_ASSOS);
		$q->addWhere("rght_userid =".  $aUserId);
		$res = $q->replaceRow();
		if ( $q->isError() )
		{
			$this->_error($q->getMsg());
		}
		return $res;
	}

}
?>
