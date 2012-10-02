<?php
/*****************************************************************************
!   Module     : Utils
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utservices.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.16 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/05/06 07:59:33 $
******************************************************************************/
require_once "nusoap/nusoap.php";

/**
* Classe utilitaire pour la manipulation des donnes
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/
class utServices
{

  // {{{ properties
  
  /**
   * Nusoap client
   *
   * @var     object
   * @access  private
   */
  var $_nus;

  /**
   * Debug
   *
   * @var     object
   * @access  private
   */
  var $_debug = false;

  // }}}
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function utServices($host=false, $user=false, $pwd=false)
    {
      $ut = new utils();
      $this->_user = $user;
      $this->_pwd  = $pwd;
      if($host == 'local')
	{
	  $this->_server = dirname("http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");
	  $this->_server .= "/../services/badnet.php";
	}
      else if($host)
	$this->_server = "{$host}/services/badnet.php";
      else
	{
	  $this->_server = $ut->getParam('server', dirname("http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}"));
	  $this->_server .= "/../services/badnet.php";
	}
      $this->_nus = new nusoap_client($this->_server);
      $err = $this->_nus->getError();
      if ($err)
	return $this->_error($this->_server);
    }
  // }}}

  // {{{ getFacteurData
  /**
   * Demander les donnees au serveur
   *
   * @access public
   * @return array   array of users
   */
  function getFacteurData()
    {
      $param = array('login'   => $this->_user,
		     'pwd'     => $this->_pwd
		     );
      $res = $this->_nus->call('getFacteurData', $param);
      if ($this->_nus->fault ||
	  $this->_nus->getError())
	return $this->_error('getFacteurData');
      return $res;
    }
  // }}}

  // {{{ getAdmEventList
  /**
   * Demander les donnees au serveur
   *
   * @access public
   * @return array   array of users
   */
  function getAdmEventList($login, $pwd, $filtre=null)
    {
      $param = array('login'   => $login,
		     'pwd'     => $pwd,
		     'filtre'  => $filtre,
		     );
      $res = $this->_nus->call('getAdmEventList', $param);
      if ($this->_nus->fault ||
	  $this->_nus->getError())
	return $this->_error('getAdmEventList');
      return $res;
    }
  // }}}

  // {{{ select
  /**
   * Demander les donnees au serveur
   *
   * @access public
   * @return array   array of users
   */
  function select($tables, $fields, $where=false, $order=false, 
		   $publiTable=null)
    {
      // Determiner les caracteristique de l'utilisateur
      $tradFields['lang'] = utvars::getLanguage();
      $param = array('tables' => $tables,
		     'fields' => $fields,
		     'where'  => $where,
		     'order'  => $order,
		     //'publish' => $publiTable,
		     );
      $res = $this->_nus->call('select', $param);
      if ($this->_nus->fault ||
	  $this->_nus->getError())
	return $this->_error('select');
      return $res;
    }
  // }}}

  // {{{ updateDraw
  /**
   * Demander la mise a jour de la partie KO du tableau au serveur
   *
   * @access public
   * @return array   array of users
   */
  function updateDraw($eventId, $roundId)
    {
      $res = false;
      if ($this->_user && $this->_pwd)
	{
	  $db = new utbase();
	  // Identifiant du tour
	  $draw['rund_uniId'] = $db->_selectFirst('rounds', 'rund_uniId',
					 "rund_id={$roundId}");
	  // Liste des paires et de leur positions
	  $tables = array('t2r', 'pairs');
	  $fields = array('pair_uniId as t2r_pairId', 't2r_posRound', 't2r_tds',
			  't2r_status');
	  $where = "t2r_roundId = $roundId".
	    " AND t2r_pairId = pair_id";
	  $res = $db->_select($tables, $fields, $where);
	  $pairs = array();
	  while ($pair = $res->fetchRow(DB_FETCHMODE_ASSOC))
	    $pairs[$pair['t2r_posRound']] = $pair;
	  unset($db);

	  $draw['login']   = $this->_user;
	  $draw['pwd']     = $this->_pwd;
	  $draw['eventId'] = $eventId;
	  $draw['pairs']   = $pairs;
	  $data = array($draw);
	  $res = $this->_nus->call('updateDraw', $data);
	  if ($this->_nus->fault ||
	      $this->_nus->getError())
	    return $this->_error('updateDraw');
	}
      return $res;
    }
  // }}}

  // {{{ updateMatchStatus
  /**
   * Demander la mise a jour d'un macth au serveur
   *
   * @access public
   * @return array   array of users
   */
  function updateMatchStatus($eventId, $match)
    {
      $res = false;
      if ($this->_user && $this->_pwd)
	{

	  $db = new utbase();
	  $match['mtch_uniId'] = $db->_selectFirst('matchs', 'mtch_uniId',
					 "mtch_id={$match['mtch_id']}");
	  if (isset($match['mtch_umpireId']))
	    $match['mtch_umpireId'] = $db->_selectFirst('registration', 'regi_uniId',
							"regi_id={$match['mtch_umpireId']}");
	  if (isset($match['mtch_serviceId']))
	    $match['mtch_serviceId'] = $db->_selectFirst('registration', 'regi_uniId',
							 "regi_id={$match['mtch_serviceId']}");

	  $match['login'] = $this->_user;
	  $match['pwd'] = $this->_pwd;
	  $match['eventId'] = $eventId;
	  unset($db);
	  unset($match['mtch_id']);
	  $data = array($match);
	  $res = $this->_nus->call('updateMatchStatus', $data);
	  if ($this->_nus->fault ||
	      $this->_nus->getError())
	    {
	      return $this->_error('updateMatchStatus');
	    }
	}
      return $res;
    }
  // }}}

  /**
   * Demander la mise a jour d'un resultat de macth au serveur
   *
   * @access public
   * @return array   array of users
   */
  function updateMatchResult($eventId, $match, $winPairId, $loosPairId)
    {
      $res = false;
      if ($this->_user && $this->_pwd)
	{
	  $db = new utbase();
	  $match['mtch_uniId'] = $db->_selectFirst('matchs', 'mtch_uniId',
					 "mtch_id={$match['mtch_id']}");
	  $match['winUniId'] = $db->_selectFirst('pairs', 'pair_uniId',
					 "pair_id=$winPairId");
	  $match['loosUniId'] = $db->_selectFirst('pairs', 'pair_uniId',
					 "pair_id=$loosPairId");
	  $match['login'] = $this->_user;
	  $match['pwd'] = $this->_pwd;
	  $match['eventId'] = $eventId;
	  unset($db);
	  unset($match['mtch_id']);
	  $data = array($match);
	  $res = $this->_nus->call('updateMatchResult', $data);
	  if ($this->_nus->fault ||
	      $this->_nus->getError())
	    {
	      print_r($this->_nus);
	      return $this->_error('updateMatchResult');
	    }
	}
      return $res;
    }


  /**
   * Demander la mise a jour d'un resultat de macth au serveur
   *
   * @access public
   * @return array   array of users
   */
  function updateMatchTeamResult($eventId, $match)
    {
      $res = false;
      if ($this->_user && $this->_pwd)
	{
	  $db = new utbase();
	  $match['mtch_uniId'] = $db->_selectFirst('matchs', 'mtch_uniId',
					 "mtch_id={$match['mtch_id']}");
	  if( !empty($match['mtch_winId0']))
	  $match["mtch_winId0"] = $db->_selectFirst('registration', 'regi_uniId',
					 'regi_id=' . $match['mtch_winId0']);
	  if( !empty($match['mtch_winId1']))
	  $match["mtch_winId1"] = $db->_selectFirst('registration', 'regi_uniId',
					 'regi_id=' . $match['mtch_winId1']);
	  if( !empty($match['mtch_loosId0']))
	  $match["mtch_loosId0"] = $db->_selectFirst('registration', 'regi_uniId',
					 'regi_id=' . $match['mtch_loosId0']);
	  if( !empty($match['mtch_loosId1']))
	  $match["mtch_loosId1"] = $db->_selectFirst('registration', 'regi_uniId',
					 'regi_id=' . $match['mtch_loosId1']);
	  $match["teamuniid1"] = $db->_selectFirst('teams', 'team_uniId',
					 'team_id=' . $match['mtch_teamId0']);
	  $match["teamuniid2"] = $db->_selectFirst('teams', 'team_uniId',
					 'team_id=' . $match['mtch_teamId1']);
	  $match['login'] = $this->_user;
	  $match['pwd'] = $this->_pwd;
	  $match['eventId'] = $eventId;
	  unset($db);
	  unset($match['mtch_id']);
	  $data = array($match);
	  $res = $this->_nus->call('updateMatchTeamResult', $data);
	  if ($this->_nus->fault ||
	      $this->_nus->getError())
	    {
	      print_r($this->_nus);
	      return $this->_error('updateMatchTeamResult');
	    }
	}
      return $res;
    }
    
    // {{{ getMatch
  /**
   * Demander la definition d'un match
   *
   * @access public
   * @return array   array of users
   */
  function getMatch($eventId, $matchId)
    {
      $res = false;
      if ($this->_user && $this->_pwd)
	{
	  $db = new utbase();
	  $match['mtch_uniId'] = $db->_selectFirst('matchs', 'mtch_uniId',
					 "mtch_id={$matchId}");
	  $match['login']   = $this->_user;
	  $match['pwd']     = $this->_pwd;
	  $match['eventId'] = $eventId;
	  unset($db);
	  $data = array($match);
	  $res = $this->_nus->call('getMatch', $data);
	  if ($this->_nus->fault ||
	      $this->_nus->getError())
	    {
	      print_r($this->_nus);
	      return $this->_error('getMatch');
	    }
	}
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
  function _error($aFct=null)
    {      
      // Get information in a variable
      ob_start();
      echo "eventId:".utvars::getEventId();
      echo "\nuserId:".utvars::getUserId();
      echo "\nfct:".$aFct;
      echo "\nvariables GET:\n".utvars::getUserId();
      print_r($_GET);
      echo "\nvariables POST:\n".utvars::getUserId();
      print_r($_POST);

      echo '<h2>Error</h2><pre>'; 
      $this->_nus->getError();
      echo '</pre>';
      $buf = ob_get_contents();
      ob_end_clean();
     
      // Send a email for developpers
      require_once "utils/utmail.php";
      $mailer = new utmail();
      $mailer->subject("Erreur base de donnes");
      $mailer->from("no-reply@badnet.org");
      $mailer->body($buf);
      @$mailer->send('cage@free.fr');

      // Display the current page with and error message
      if($this->_debug)
	//      if(1)
	{
	  echo '<h2>Error</h2><pre>'; 
	  echo "{$this->_server} <br\>";
	  $page =& utvars::getPage();	
	  $page->addErr("Erreur d'acces au service");
	  echo '</pre>';
	  $page->addMsg($err); 
	  $page->display();
	  exit;
	}
      else
	{
	  $res['errMsg'] = $this->_nus->getError().":{$this->_server}";
	  return $res;
	}
    }
  // }}}

}
?>