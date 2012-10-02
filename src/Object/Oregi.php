<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Oregi.inc';
$locale = Bn::GetLocale();
require_once 'Object/Locale/' . $locale . '/Omember.inc';
require_once 'Object/Omember.inc';
require_once 'Object/Omatch.inc';
require_once 'Object/Oevent.inc';


class Oregi extends Object
{

	static public function isExist($aRegiId)
	{
		$q = new Bn_query('registration');
		$q->setFields('count(*)');
		$q->addwhere('regi_id = ' . $aRegiId);
		return $q->getFirst();
	}
	
	/**
	 * Mise a jour des infos sportive a partir du site officiel
	 *
	 */
	public function refresh()
	{
		$oMember = new Omember($this->getVal('memberid', -1));
		$license = $oMember->getVal('licence');
		if (!empty($license))
		{
			$oExt = Oexternal::factory();
		
			$player = $oExt->getPlayer($license);
			$this->setValues($player);
			$this->save();
		}
		else
		{
			$player['levels'] = 'NC';
			$player['leveld'] = 'NC';
			$player['levelm'] = 'NC';
			$player['points'] = 0;
			$player['pointd'] = 0;
			$player['pointm'] = 0;
			$player['ranks'] = 0;
			$player['rankd'] = 0;
			$player['rankm'] = 0;
		}
		
		// Infos classement : recuperer le classement Nc qui sera utilise par defaut
		// si le classement du joueur est inconnu
		$system = Bn::getConfigValue('ranking', 'params');
		$q = new bn_Query('rankdef');
		$q->setFields('rkdf_id, rkdf_point');
		$q->setWhere("rkdf_label='NC'");
		$q->addWhere('rkdf_system=' . $system );
		$rankNc = $q->getRow();
		
		// Sauvegarde des classements
		$gender = $oMember->getVal('sexe');
		$regiId = $this->getVal('id');
		$disci = ($gender == OMEMBER_GENDER_MALE) ? OMATCH_DISCI_MS:OMATCH_DISCI_WS;
		$q->setWhere("rkdf_label='" . $player['levels'] . "'");
		$q->addWhere('rkdf_system=' . $system );
		$rank = $q->getRow();
		if ( empty($rank) ) $rank = $rankNc;
		$qr = new bn_Query('ranks');
		$qr->setValue('rank_regiid',     $regiId);
		$qr->addValue('rank_rankdefid',  $rank['rkdf_id']);
		$qr->addValue('rank_disci',      $disci);
		$qr->addValue('rank_discipline', OMATCH_DISCIPLINE_SINGLE);
		$qr->addValue('rank_average',    $player['points']);
		$qr->addValue('rank_rank',       $player['ranks']);
		$qr->setWhere("rank_disci=" . $disci);
		$qr->addWhere('rank_regiid=' .  $regiId);
		$qr->replaceRow();

		$q->setWhere("rkdf_label='" . $player['leveld'] . "'");
		$q->addWhere('rkdf_system=' . $system );
		$rank = $q->getRow();
		if ( empty($rank) ) $rank = $rankNc;
		$disci = ($gender == OMEMBER_GENDER_MALE) ? OMATCH_DISCI_MD:OMATCH_DISCI_WD;
		$qr->setValue('rank_regiid',     $regiId);
		$qr->addValue('rank_rankdefid',  $rank['rkdf_id']);
		$qr->addValue('rank_disci',      $disci);
		$qr->addValue('rank_discipline', OMATCH_DISCIPLINE_DOUBLE);
		$qr->addValue('rank_average',    $player['pointd']);
		$qr->addValue('rank_rank',       $player['rankd']);
		$qr->setWhere("rank_disci=" . $disci);
		$qr->addWhere('rank_regiid=' .  $regiId);
		$qr->replaceRow();

		$q->setWhere("rkdf_label='" . $player['levelm'] . "'");
		$q->addWhere('rkdf_system=' . $system );
		$rank = $q->getRow();
		if ( empty($rank) ) $rank = $rankNc;
		$qr->setValue('rank_regiid',     $regiId);
		$qr->addValue('rank_rankdefid',  $rank['rkdf_id']);
		$qr->addValue('rank_disci',      OMATCH_DISCI_XD);
		$qr->addValue('rank_discipline', OMATCH_DISCIPLINE_MIXED);
		$qr->addValue('rank_average',    $player['pointm']);
		$qr->addValue('rank_rank',       $player['rankm']);
		$qr->setWhere("rank_disci=" . OMATCH_DISCI_XD);
		$qr->addWhere('rank_regiid=' .  $regiId);
		$qr->replaceRow();
		return true;
	}

	/**
	 * Constructeur
	 * @param	integer	$aMemberId	Identifiant badnet du membre
	 * @param	integer	$apoonaId   Identifiant poona du membre
	 * @param	string 	$aLicense	License du membre
	 * @return OMember
	 */
	public function __construct($aRegiId=-1)
	{
		if( $aRegiId > 0)
		{
			$where = 'regi_id=' . $aRegiId;
			$this->load('registration', $where);
		}
	}

	public function getMember()
	{
		return new Omember($this->getVal('memberid', -1));
	}
	
	/**
	 * Inscription d'un joueur
	 *
	 * @return unknown
	 */
	public function register($aWhere = null)
	{
		$where = 'regi_teamid='. $this->getVal('teamid');
		$where .= ' AND regi_memberid='. $this->getVal('memberid');
		$where .= ' AND regi_type='. $this->getVal('type');
		$id = $this->save($where);
		return $id;
	}
	
	/**
	 * Enregistre l'inscription
	 *
	 * @return unknown
	 */
	public function save($aWhere = null)
	{
		$regiId = $this->getVal('id', -1);
		if (empty($aWhere)) $where = 'regi_id=' . $regiId;
		else $where = $aWhere;
		$id = $this->update('registration', 'regi_', $this->getValues(), $where);
		
		//Identifiant unique
		if ($regiId == -1)
		{
			$where = 'regi_id=' . $id;
			$uniId = Bn::getUniId($id);
			$this->setVal('uniid', $uniId);
			$this->update('registration', 'regi_', $this->getValues(), $where);
		}
		return $id;
	}

	/**
	 * Suppression d'un inscrit
	 *
	 * @param unknown_type $aMemberId
	 */
	public function delete($aRegiId)
	{
		// Verification qu'il n'y a pas d'inscription
		// Verifier les achats
		//@todo
		return false;

	}
}
?>
