<?php
/*****************************************************************************
!   Module     : Services
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utservices.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.15 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/03/01 22:17:09 $
******************************************************************************/
require_once dirname(__FILE__).'/../nusoap/nusoap.php';

/**
* Classe d'acces au server public
*
*/
class svc
{

  // {{{ properties
  var $_nus;            // Nusoap client
  var $_debug = true;  // Debug flag
  // }}}
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   */
  function svc($host='local', $server = 'public', $service='lov')
    {
      if($host == 'local')
	{
	  $this->_server = dirname("http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");
	  $this->_server .= "/server/{$server}/{$service}.php";
	}
      else 
	$this->_server = "{$host}/{$server}/{$service}.php";
      $this->_nus = new nusoapclient($this->_server);
    }
  // }}}

  // {{{ call
  /**
   * Demander les donnees au serveur
   *
   * @access public
   * @return array   array of users
   */
  function call($fct, $data)
    {
      // assign optional features
      $param = array('data'   => $data);
      $res = $this->_nus->call($fct, $param);
      //print_r($res);
      //print_r($this->_nus);
      if ($this->_nus->fault ||
	  $this->_nus->getError())
	return $this->_error();
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
      // Display the current page with and error message
      $res['isErr'] = true;
      $res['errMsg'] = $this->_nus->getError().":{$this->_server}";
      if($this->_debug)
	{
	  require_once 'Log.php';
	  $conf = array('mode' => 0777, 'timeFormat' => '%X %x');
	  $fileName = dirname(__FILE__)."/../../log/err.log";
	  $logger = &Log::singleton('file', $fileName, 'ident', $conf);
	  $logger->log($this->_nus);
	}
      return $res;
    }
  // }}}

}
?>