<?php
/*****************************************************************************
 !   Module     : Utilitaires pour les traiter les donnees issues du site FFBA
 !   Version    : $Name:  $
 ******************************************************************************/

require_once "nusoap/nusoap.php";
require_once "base_A.php";

/**
 * Classe pour la recuperation de donnes depuis le site Poona
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @author Didier BEUVELOT
 * @see to follow
 *
 */

class Imppoona
{

	// {{{ properties
	// }}}

	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function ImpPoona()
	{
		$ut = new utils();
		$isSquash = $ut->getParam('issquash', false);
		if ($isSquash)  $this->_server = "http://www.squashnet.fr/services/squash.php";
		else $this->_server = "http://www.badnet.org/badnet/services/poona.php";
		//$this->_server = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
		//$this->_server .= "/../services/squash.php";
		$this->_nus = new nusoap_client($this->_server);
		if ($this->_nus->getError())	return $this->_error();
	}
	// }}}

	/**
	 * Prepare la requete pour la recherche des classements
	 *
	 * @access public
	 * @param  array   $where Crteres de recherche
	 * @return void
	 */
	function searchInstance($criteria)
	{
		$param = array('criteria' => $criteria);
		$res = $this->_nus->call('searchInstance', $param);
		//print_r($this);
		if ($this->_nus->fault || $this->_nus->getError() ) $this->_error();
		return $res;
	}

	/**
	 * Renvoi une instance de la base federale a partir de son identifiant
	 *
	 * @access public
	 * @param  array   $where Crteres de recherche
	 * @return array   Champ de l'instance
	 */
	function getInstance($insId)
	{
		$param = array('insId' => $insId);
		$res = $this->_nus->call('getInstance', $param);
		if ($this->_nus->fault || $this->_nus->getError() ) $this->_error();
		return $res;
	}


	/**
	 * Initialise la recherche le classements des joueurs dans la base federale
	 */
	function updateRanks($ids)
	{
		$this->_load($ids);
	}


	/**
	 * Recherche le classements des joueurs dans la base federale
	 *
	 * @access public
	 * @param  array   $ids  liste de joueurs
	 *     ids['regi_id'] = array( 'mber_fedeId', 'mber_licence')
	 * @return array
	 *     ids['regi_id'] = array( 'mber_fedeId', 'mber_licence',
	 *                             'simple' = array(rank_disci,
	 *                                              rank_average,
	 *                                              rank_rankdefid)
	 *                             'double' = array(rank_disci,
	 *                                              rank_average,
	 *                                              rank_rankdefid)
	 *                             'mixte' = array(rank_disci,
	 *                                              rank_average,
	 *                                              rank_rankdefid)
	 */
	function updateRank()
	{
		$eventId = utvars::getEventId();
		$utevent = new utevent();
		$event = $utevent->getEvent($eventId);

		$memberids = kform::getInput('memberids');
		$licenses = kform::getInput('licenses');
		$fedeids = kform::getInput('fedeids');
		$regiids = kform::getInput('regiids');
		$sexes = kform::getInput('sexes');
		$nb= count($memberids);
		$player['mber_id'] = reset($memberids);
		$player['mber_licence'] = reset($licenses);
		$player['mber_fedeid'] = reset($fedeids);
		$player['regi_id'] = reset($regiids);
		$player['mber_sexe'] = reset($sexes);
		$players[] = $player;
		for($i=1; $i<$nb;$i++)
		{
			$player['mber_id'] = next($memberids);
			$player['mber_licence'] = next($licenses);
			$player['mber_fedeid'] = next($fedeids);
			$player['regi_id'] = next($regiids);
			$player['mber_sexe'] = next($sexes);
			$players[] = $player;
		}
		$ids[] = array_shift($players);

		$param = array('ids' => $ids, 'adult'=>$event['evnt_catage']);
		$res = $this->_nus->call('getRanks', $param);
		if ($this->_nus->fault || $this->_nus->getError() ) return $this->_error();
		$dt = new importBase_A();
		if (!empty($res)) $res = $dt->updateRankFromFede($res);
        if (!empty($players) ) $this->_load($players);
        else
        {
	  		$page = new utPage('none');
	  		$page->close();
	  		exit;
        }
        return;
	}

	/**
	 * Passage au joueur suivant
	 *
	 * @access private
	 * @return void
	 */
	function _load($aLicenses)
	{
		require_once "regi/regi.inc";
		$hides['kpid'] = 'regi';
		$hides['kaid'] = REGI_UPDATE_RANK;
		
		// Affichage de la page qui charge la nouvelle
		echo "<html><head>";

		if (!empty($aLicenses))
		{
	  		echo "</head><body  onload=\"document.forms['Url'].submit();\">\n<form id=\"Url\" method=\"post\" ";
		}
		else
		{
	  		echo "</head><body><form id=\"Url\" method=\"post\" \"";      
		}
		if (isset($GLOBALS['PHP_SELF'])) echo "action=\"$GLOBALS[PHP_SELF]\">\n";
		else echo "action=\"".$_SERVER['PHP_SELF']."\">\n";

		foreach($hides as $hide=>$value)
		{
			echo "<input type=\"hidden\" name=\"$hide\" id=\"$hide\" value=\"$value\"  />\n";
		}
		foreach($aLicenses as $license)
		{
			echo "<input type=\"hidden\" name=\"memberids[]\"  value=\"". $license['mber_id'] . "\"  />\n";
			echo "<input type=\"hidden\" name=\"licenses[]\"  value=\"". $license['mber_licence'] . "\"  />\n";
			echo "<input type=\"hidden\" name=\"fedeids[]\"  value=\"". $license['mber_fedeid'] . "\"  />\n";
			echo "<input type=\"hidden\" name=\"regiids[]\"  value=\"". $license['regi_id'] . "\"  />\n";
			echo "<input type=\"hidden\" name=\"sexes[]\"  value=\"". $license['mber_sexe'] . "\"  />\n";
		}
		$license = reset($aLicenses);
		echo "Traitement licence " . $license['mber_licence'] . '... reste ' . count($aLicenses);
		echo "</body></html>";
		exit();
	}


	// {{{ searchPlayer()
	/**
	 * Prepare la requete pour la recherche de joueur
	 *
	 * @access public
	 * @param  array   $where Crteres de recherche
	 * @return void
	 */
	function searchPlayer($criteria, $now=true)
	{
		$param = array('criteria' => $criteria,
		     		'now'      => $now);
		$res = $this->_nus->call('searchPlayer', $param);
		if ($this->_nus->fault ||  $this->_nus->getError() )	return $this->_error();
		return $res;
	}
	// }}}

	// {{{ getPlayer()
	/**
	 * Prepare la requete pour la recherche de joueur
	 *
	 * @access public
	 * @param  array   $where Crteres de recherche
	 * @return void
	 */
	function getPlayer($criteria)
	{
		$eventId = utvars::getEventId();
		$utevent = new utevent();
		$event = $utevent->getEvent($eventId);
		$param = array('playerId' => $criteria,
		     'now' => true,
      		 'adult' => $event['evnt_catage']);
		$res = $this->_nus->call('getPlayer', $param);
		if ($this->_nus->fault ||  $this->_nus->getError() )	return $this->_error();

		// Joueur non licencie, la categorie
		// n'est pas renseigne, on recupere les infos
		// dans la saison precedente
		if ($res['regi_catage'] == '')
		{
	  $param = array('playerId' => $criteria,
			 'now' => false);
	  $res = $this->_nus->call('getPlayer', $param);
	  if ($this->_nus->fault) return $this->_error();

	  $res['regi_dateauto'] = '';
	  $res['regi_datesurclasse'] = '';
	  $res['regi_surclasse'] = WBS_SURCLASSE_NONE;
	  if ($res['regi_catage'] < WBS_CATAGE_SEN)
	  $res['regi_catage']++;
	  $res['regi_numcatage'] = 0;
		}

		$utd = new utdate();
		$utd->setIsoDate($res['mber_born']);
		$res['mber_born'] = $utd->getDate();
		$utd->setIsoDate($res['regi_dateauto']);
		$res['regi_dateauto'] = $utd->getDate();
		$utd->setIsoDate($res['regi_datesurclasse']);
		$res['regi_datesurclasse'] = $utd->getDate();

		$dt = new importBase_A();
		$ranksId = $dt->_getC2i();
		$res['rank_simple'] = $ranksId[$res['rank_simple']];
		$res['rank_double'] = $ranksId[$res['rank_double']];
		$res['rank_mixte']  = $ranksId[$res['rank_mixte']];
		if (empty($res['rank_rank'])) $res['rank_rank'] = '999999';
		return $res;
	}
	// }}}

	// {{{ _error
	/**
	 * Gerer les erreurs d'acces a la base
	 *
	 * @access public
	 * @return array   array of users
	 */
	function _error()
	{
		$err['errMsg'] = $this->_nus->getError();
		return $err;
	}
	// }}}

}
?>