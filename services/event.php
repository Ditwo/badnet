<?php
/*****************************************************************************
!   Module     : services
!   File       : $Source: /cvsroot/aotb/badnet/services/event.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.2 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/02/01 18:05:36 $
******************************************************************************/
/**
* Serveur SOAP pour aceder aux donnees des tournois
*
* @author Gerard CANTEGRIL
*
*/
require_once "dba.php";

class badnetEvent
{

  // {{{ properties
  // }}}
  function badnetEvent()
    {
      $this->_db = new dba();
    }

  function getAdmEvents($login, $pwd, $criteria)
    {
      $db =& $this->_db;
      // Recherche du type de l'utilisateur
      $fields = array('user_type');
      $where = "user_login='$login'".
	" AND user_pass='$pwd'";
      $type = $db->selectFirst('users', $fields, $where);
      if (is_null($type)) return "Error identification";
      
      // Administrateur : tout les tournois sont accessibles
      if ($type == WBS_AUTH_ADMIN)
	{
	  $fields = array('evnt_id', 'evnt_name');      
	  $tables = array('events');
	  $where = "evnt_del!=".WBS_DATA_DELETE;
	}
      // Recherche en fonction des privileges
      else
	{
	  $fields = array('evnt_id', 'evnt_name');
	  $tables = array('events', 'rights', 'users');
	  $where = "user_login='$login'".
	    " AND user_pass='$pwd'".
	    " AND user_id=rght_userid".
	    " AND rght_theme=".WBS_THEME_EVENT.
	    " AND rght_status='".WBS_AUTH_MANAGER.
	    "' AND rght_themeid=evnt_id";
	}
      if (!is_null($criteria)) $where .= " AND $criteria";
      $order = 'evnt_name';
      $res = $db->select($tables, $fields, $where, $order);      
      $events = array();
      while ($event = $res->fetch(PDO::FETCH_ASSOC))
	$events[]  = $event;
      return $events;
    }
  //}}}
}
?>