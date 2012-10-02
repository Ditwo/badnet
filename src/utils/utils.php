<?php
/*****************************************************************************
 !   Module     : Utilitaires
 !   File       : $Source: /cvsroot/aotb/badnet/src/utils/utils.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.11 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:26 $
 ******************************************************************************/
require_once "utbase.php";
include_once "Auth/Auth.php";

/**
 * Classe de base pour la creation de menu
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class Utils extends utBase
{

	// {{{ properties
	// }}}


	// {{{ loadPage()
	/**
	 * load a page
	 *
	 * @access public
	 * @param  integer $numLabel  Position of the label
	 * @return void
	 */
	function loadPage($page, $act, $data)
	{
		$url = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
		$url .= "?kpid=$page".
	"&kaid=$act".
	"&kdata=$data";
		header("Location: $url");
		exit;
	}
	// }}}

	// {{{ synchro()
	/**
	 * send dat to distant BadNet
	 *
	 * @access public
	 * @param  integer $numLabel  Position of the label
	 * @return void
	 */
	function synchro($page, $act, $param)
	{
		$url  = $this->getParam('synchroUrl');
		if ($url == '')
		return;

		$url .= "/src/index.php?kaid=".WBS_CNX_DIST;
		$url .= "&kpid=cnx";
		$url .= "&pageId=$page";
		$url .= "&actionId=$act";
		$url .= "&eventId=".utvars::getEventId();
		$url .= "&langu=".utvars::getLanguage();
		$url .= $param;
		$user = $this->getParam('synchroUser');
		$pwd  = $this->getParam('synchroPwd');
		if ($user != '' && $pwd!="" && $url!='')
		{
	  $url .= "&uid=$user&puid=$pwd";
	  //echo $url;exit;
	  header("Location: $url");
		}
	}
	// }}}

	// {{{ getLabel()
	/**
	 * return the label of a standard information
	 *
	 * @access public
	 * @param  integer $numLabel  Position of the label
	 * @return void
	 */
	function getLabel($numLabel)
	{
		$file = "lang/".utvars::getLanguage(). "/database.inc";
		if ( !file_exists($file)) $file = "lang/fra/database.inc";
			
		require $file;

		$id = "WBS_LABEL_".$numLabel;
		if (isset(${$id}))return ${$id};
		else return $id;
	}
	// }}}

	// {{{ getSmaLabel()
	/**
	 * return the label of a standard information
	 *
	 * @access public
	 * @param  integer $numLabel  Position of the label
	 * @return void
	 */
	function getSmaLabel($numLabel)
	{

		$file = "lang/".utvars::getLanguage()."/database.inc";
		if ( !file_exists($file)) $file = "lang/fra/database.inc";
		require $file;

		$id = "WBS_SMA_LABEL_".$numLabel;
		if ( isset(${$id}) ) return ${$id};
		else return $id;
	}
	// }}}

	// {{{ getParam
	/**
	 * Return the value of  parameter
	 *
	 * @access public
	 * @param  string  $paramName    id of the parameter
	 * @param  string  $default  default value of the parameter
	 * @return string   Value of the parameter
	 */
	function getParam($paramName, $default="")
	{
		$fields = array('meta_value');
		$tables[] = 'meta';
		$where = "meta_name ='$paramName'";
		$res = $this->_select($tables, $fields, $where);
		if (is_null($res)) return $default;
		if ($res->numRows())
		{
			$infos = $res->fetchRow(DB_FETCHMODE_ASSOC);
			return $infos["meta_value"];
		}
		else
		return $default;
	}
	// }}}

	// {{{ setParam
	/**
	 * Fix the value of preference
	 *
	 * @access public
	 * @param  string  $paramName    id of the parameter
	 * @param  string  $paramValue   value of the parameter
	 * @return none
	 */
	function setParam($name, $value)
	{
		$fields = array('meta_value');
		$tables[] = 'meta';
		$where = "meta_name ='$name'";
		$res = $this->_select($tables, $fields, $where);

		$fields = array();
		$fields['meta_value'] = $value;
		$fields['meta_pbl'] = WBS_DATA_PUBLIC;
		if ($res->numRows())
		{
	  $res = $this->_update('meta', $fields, $where);
		}
		else
		{
	  $fields['meta_name'] = $name;
	  $res = $this->_insert('meta', $fields);
		}
		return "";
	}
	// }}}

	// {{{ getPref
	/**
	 * Return the value of  parameter
	 *
	 * @access public
	 * @param  string  $name    id of the parameter
	 * @param  string  $default  default value of the parameter
	 * @return string   Value of the parameter
	 */
	function getPref($name, $default="", $eventPref=true)
	{
		$fields = array('pref_value');
		$tables[] = 'prefs';
		$where = "pref_userId='".utvars::getUserId()."'".
          " AND pref_name='$name'";
		if ($eventPref)
		{
	  $eventId = utvars::getEventId();
	  if ($eventId != -1)
	  $where .= " AND pref_eventId=$eventId";
		}
		$res = $this->_select($tables, $fields, $where);
		if (is_null($res))
		return $default;

		if ($res->numRows())
		{
	  $infos = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  return $infos['pref_value'];
		}
		else
		return $default;
	}
	// }}}

	// {{{ setPref
	/**
	 * Fix the value of preference
	 *
	 * @access public
	 * @param  string  $prefName    id of the preference
	 * @param  string  $prefValue   value of the preference
	 * @return none
	 */
	function setPref($name, $value)
	{
		$fields = array('pref_id');
		$tables[] = 'prefs';
		$where = "pref_userId=".utvars::getUserId().
         " AND pref_name='$name'";
		$eventId = utvars::getEventId();
		if ($eventId != -1)
		$where .= " AND pref_eventId=$eventId";

		$res = $this->_select($tables, $fields, $where);
		if (is_null($res))
		return $value;

		$fields = array();
		$fields['pref_value'] = $value;
		if ($res->numRows())
		{
	  $res = $this->_update('prefs', $fields, $where);
		}
		else
		{
	  $fields['pref_name'] = $name;
	  $fields['pref_userId'] = utvars::getUserId();
	  $fields['pref_eventId'] = $eventId;
	  $res = $this->_insert('prefs', $fields);
		}
		return "";
	}
	// }}}

}
?>