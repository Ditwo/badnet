<?php
/*****************************************************************************
!   Module     : Utils
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utservices.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.16 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/05/06 07:59:33 $
******************************************************************************/
require_once dirname(__FILE__)."/../nusoap/nusoap.php";

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
      $this->_user = $user;
      $this->_pwd  = $pwd;
      if($host == 'local')
	{
	  $this->_server = dirname("http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");
	  $this->_server .= "/../services/badnet.php";
	}
      else if($host)
	$this->_server = "{$host}/services/badnet.php";

      $this->_nus = new nusoapclient($this->_server);
      $err = $this->_nus->getError();
      if ($err)
	$this->_error();
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
	return $this->_error();
      return $res;
    }
  // }}}

  // {{{ updateMatchResult
  /**
   * Demander la mise a jour d'un resultat de macth au serveur
   *
   * @access public
   * @return array   array of users
   */
  function updateMatchResult($eventId, $match)
    {
      $res = false;
      if ($this->_user && $this->_pwd)
	{
	  $match['login'] = $this->_user;
	  $match['pwd'] = $this->_pwd;
	  $match['eventId'] = $eventId;
	  //unset($db);
	  //unset($match['mtch_id']);
	  $data = array($match);
	  $res = $this->_nus->call('updateMatchResult', $data);
	  if ($this->_nus->fault ||
	      $this->_nus->getError())
	    $this->_error();	  
	}
      return $res;
    }
  // }}}
  
 // {{{ updateMatchTeamResult
  /**
   * Demander la mise a jour d'un resultat de match dans un tournoi
   *  par equipe au serveur
   *
   * @access public
   * @return array   array of users
   */
  function updateMatchTeamResult($eventId, $match)
    {
      $res = false;
      if ($this->_user && $this->_pwd)
	{
	  $match['login'] = $this->_user;
	  $match['pwd'] = $this->_pwd;
	  $match['eventId'] = $eventId;
	  unset($db);
	  unset($match['mtch_id']);
	  $data = array($match);
	  $res = $this->_nus->call('updateMatchTeamResult', $data);
	  if ($this->_nus->fault ||
	      $this->_nus->getError())
	    $this->_error();	  
	}
      return $res;
    }
  // }}}
    
    
  // {{{ getMatch
  /**
   * Demander la definition d'un match
   *
   * @access public
   * @return array   array of users
   */
  function getMatch($eventId, $matchUniId)
    {
      $res = false;
      if ($this->_user && $this->_pwd)
	{
	  $match['mtch_uniId'] = $matchUniId;
	  $match['login']   = $this->_user;
	  $match['pwd']     = $this->_pwd;
	  $match['eventId'] = $eventId;
	  unset($db);
	  $data = array($match);
	  $res = $this->_nus->call('getMatch', $data);
	  if ($this->_nus->fault ||
	      $this->_nus->getError())
	    $this->_error();
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
  function _error()
    {      
      // Get information in a variable
      echo '----- Error ------'; 
      echo "{$this->_server} <br\>";
      $fd = fopen("/tmp/gg", "w");
      $trace = print_r($this->_nus, true);
      fwrite($fd, $trace);
      fclose($fd);
    }
  // }}}

}
?>