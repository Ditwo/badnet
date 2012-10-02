<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Object/Oregi.inc';
require_once 'Object/Omatch.inc';
require_once 'Object/Oevent.inc';

$locale = BN::getLocale();
require_once "Object/Locale/$locale/Oplayer.inc";

class Oplayer extends Object
{
	// Pour le live scoring
	private $_service = 0;     // Numero de service
	private $_oMember = null;  // Partie membre du joueur

	static public function factory($aRegiId)
	{
		if ( Bn::getConfigValue('squash', 'params') )
		{
			require_once 'Object/Oplayer_squash.php';
			$oPlayer = new Oplayer_squash();
		}
		else $oPlayer = new Oplayer($aRegiId);
		return $oPlayer;
	}

	/**
	 * Constructor
	 */
	public function __construct($aRegiId=-1)
	{
		if($aRegiId > 0)
		{
			$where = 'regi_id=' . $aRegiId;
			$this->load('registration', $where);

			$this->setVal('labcatage', constant('LABEL_' . $this->getVal('catage')));
			$this->setVal('labsurclasse', constant('LABEL_' . $this->getVal('surclasse')));
			
			$q = new Bn_query('ranks');
			$q->addTable('rankdef', 'rank_rankdefid = rkdf_id');
			$q->setFields('rkdf_label, rank_disci, rank_average, rkdf_point, rkdf_id, rank_rank');
			$q->addWhere('rank_regiid=' . $aRegiId);
			$q->setOrder('rank_disci');

			$ranks = $q->getRows();
			$rank = reset($ranks);
			$regi['level'] = '';
			$regi['rank']  = '';
			if ( !empty($rank) )
			{
				$regi['levels']   = $rank['rkdf_label'];
				$regi['levelsid'] = $rank['rkdf_id'];
				$regi['points']   = $rank['rank_average'];
				$regi['ranks']    = $rank['rank_rank'];
				$regi['seuils']   = $rank['rkdf_point'];
				$regi['level']    = $rank['rkdf_label'];
				$regi['rank']     = $rank['rank_rank'];
				$regi['point']    = $rank['rank_average'];
			}
			$rank = next($ranks);
			if ( ! empty($rank) )
			{
				$regi['leveld'] = $rank['rkdf_label'];
				$regi['leveldid'] = $rank['rkdf_id'];
				$regi['pointd']   = $rank['rank_average'];
				$regi['rankd']    = $rank['rank_rank'];
				$regi['seuild']   = $rank['rkdf_point'];
				$regi['level']    .= ',' . $rank['rkdf_label'];
				$regi['rank']     .= ',' . $rank['rank_rank'];
				$regi['point']    .= ',' . $rank['rank_average'];
			}
			$rank = next($ranks);
			if ( !empty($rank) )
			{
				$regi['levelm'] = $rank['rkdf_label'];
				$regi['levelmid'] = $rank['rkdf_id'];
				$regi['pointm']   = $rank['rank_average'];
				$regi['rankm']    = $rank['rank_rank'];
				$regi['seuilm']   = $rank['rkdf_point'];
				$regi['level']    .= ',' . $rank['rkdf_label'];
				$regi['rank']     .= ',' . $rank['rank_rank'];
				$regi['point']    .= ',' . $rank['rank_average'];
			}
			$this->setValues($regi);
		}
	}

	/**
	 * Retourne le nombre de match
	 *
	 */
	public function getNbMatch()
	{
		$q = new Bn_query('matchs');
		$q->addTable('p2m', 'mtch_id=p2m_matchid');
		$q->addTable('i2p', 'i2p_pairid=p2m_pairid');
		$q->addWhere('i2p_regiid=' . $this->getVal('id', -1));
		return $q->getCount();
	}
	
	/**
	 * Equipe du joueur
	 *
	 */
	public function getTeam()
	{
		return new Oteam($this->getVal('teamid', -1));
	}

	/**
	 * Membre du joueur
	 *
	 */
	public function getMember()
	{
		return new Omember($this->getVal('memberid', -1));
	}

	/**
	 * Liste des categories d'age
	 * @return array
	 */
	public function getLovCatage()
	{
		for ($i=OPLAYER_CATAGE_POU; $i<=OPLAYER_CATAGE_VET; $i++)
		{
			$catages[$i] = constant('LABEL_' . $i);
		}
		return $catages;
	}

	/**
	 * Liste des surclassements
	 * @return array
	 */
	public function getLovSurclasse()
	{
		for ($i=OPLAYER_SURCLASSE_NONE; $i<=OPLAYER_SURCLASSE_SP; $i++)
		{
			$surclasses[$i] = constant('LABEL_' . $i);
		}
		return $surclasses;
	}

}
?>