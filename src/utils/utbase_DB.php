<?php
/*****************************************************************************
 !   Module     : Utils
 !   File       : $Source: /cvsroot/aotb/badnet/src/utils/utbase.php,v $
 !   Version    : $Name: HEAD $
 !   Revision   : $Revision: 1.19 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2006/12/19 12:44:50 $
 !   Mailto     : cage@free.fr
 ******************************************************************************/

require_once "DB.php";

/**
 * Classe utilitaire pour la manipulation des donnes
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */
class utBase
{

	// {{{ properties

	/**
	 * Database access object
	 *
	 * @var     object
	 * @access  private
	 */
	var $_db;

	// }}}

	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function utBase()
	{
		$this->_db = utvars::GetDbConnect();
		$this->_prefix = utvars::getPrfx().'_';
	}
	// }}}

	// {{{ isConnected
	/**
	 * execute a query for insert data
	 *
	 * @access public
	 * @return array   array of users
	 */
	function isConnected()
	{
		return !is_null($this->_db);
	}
	//}}}

	// {{{ _getNewId
	/**
	 * execute a query for insert data
	 *
	 * @access public
	 * @return array   array of users
	 */
	function _getNewId($table)
	{
		$db = $this->_db;
		$prefix = $this->_prefix;
		$tableName = $prefix.$table;
		return $db->nextId($tableName);
	}
	//}}}

	// {{{ _insert
	/**
	 * execute a query for insert data
	 *
	 * @access public
	 * @return array   array of users
	 */
	function _insert($table, $fields)
	{
		$db = $this->_db;
		if (DB::isError($db)) return $db;
		$prefix = $this->_prefix;

		$tableName = $prefix.$table;
		$field = each($fields);
		$items = explode('_', $field['key']);
		$prefixTable = $items[0];
		if (!isset($fields[$prefixTable.'_cre']))
		$fields[$prefixTable.'_cre'] = date(DATE_FMT);
		if (!isset($fields[$prefixTable.'_updt']))
		$fields[$prefixTable.'_updt'] = date(DATE_FMT);
		if (!isset($fields[$prefixTable.'_pbl']))
		$fields[$prefixTable.'_pbl'] = WBS_DATA_CONFIDENT;
		if (!isset($fields[$prefixTable.'_id']))
		$fields[$prefixTable.'_id']= $this->_getNewId($table);
		if(($table == 'members' ||
	 $table == 'assocs' ||
	 $table == 'draws' ||
	 $table == 'matchs' ||
	 $table == 'pairs' ||
	 $table == 'registration' ||
	 $table == 'rounds' ||
	 $table == 'items' ||
	 $table == 'accounts' ||
	 $table == 'teams') &&
	 !isset($fields[$prefixTable.'_uniId']))
	 {
	  $ut = new utils();
	  $localDBId = $ut->getParam('databaseId', -1);
	  if ($localDBId == -1)
	  {
	  	$localDBId = gmmktime();
	  	$ut->setParam('databaseId', $localDBId);
	  }
	  $fields[$prefixTable.'_uniId'] = "$localDBId:{$fields[$prefixTable.'_id']};";
	 }
	 $keys = array_keys($fields);
	 $glue = '';
	 $values = "";
	 foreach($fields as $field=>$value)
	 {
	  $values .= $glue.addslashes($value);
	  $glue = "','";
	 }
	 //$values = array_values($fields);
	 $sql = "INSERT INTO $tableName (".implode(",", $keys).") ";
	 $sql .= "VALUES ('$values')";
	 $res = $db->query($sql);

	 if (DB::isError($res))
	 $this->_error($res);

	 return $fields[$prefixTable.'_id'];
	}
	// }}}

	// {{{ _update
	/**
	 * execute a query for update data
	 *
	 * @access public
	 * @return array   array of users
	 */
	function _update($table, $fields, $where)
	{
		$db = $this->_db;
		if (DB::isError($db)) return $db;
		$prefix = $this->_prefix;

		// Mise a jour de la date de derniere
		// modification du tournoi
		$tables = array('draws', 'matchs', 'pairs', 'registration',
		      'rounds', 'teams', 'ties'); 
		if (in_array($table, $tables))
		{
	  $date = date(DATE_FMT);
	  $sql = "UPDATE {$prefix}events SET evnt_lastupdate='$date'".
	    " where evnt_id=".utvars::getEventId();
	  $res = $db->query($sql);
	  if (DB::isError($res))
	  $this->_error($res);
		}

		$tableName = $prefix.$table;
		$field = each($fields);
		$items = explode('_', $field['key']);
		$prefixTable = $items[0];
		if (isset($fields[$prefixTable.'_cre'])) unset($fields[$prefixTable.'_cre']);
		if (!isset($fields[$prefixTable.'_updt'])) $fields[$prefixTable.'_updt'] = date(DATE_FMT);

		$glue = ' ';
		$sql = "UPDATE $tableName SET ";
		foreach($fields as $field=>$value)
		{
	  if(preg_match("/eval\((.*)\)/", $value, $regs) ) $sql .= "$glue $field=$regs[1]";
	  else if(isset($value[0]) && $value[0] == '#') $sql .= "$glue $field=" . substr($value, 1);
	  else  $sql .= "$glue $field='".addslashes($value)."'";
	  $glue = ',';
		}
		if ($where)
		$sql .= " WHERE $where";
		$res = $db->query($sql);
		if (DB::isError($res))
		$this->_error($res);

		if (isset($fields[$prefixTable.'_id']))
		return $fields[$prefixTable.'_id'];
		else
		return $res;
	}
	// }}}

	function _getRow($tablesList, $fields, $where=false, $order=false, $publiTable=null)
	{
		$res = $this->_select($tablesList, $fields, $where, $order, $publiTable);
		return $res->fetchRow(DB_FETCHMODE_ASSOC);
	}
	function _getRows($tablesList, $fields, $where=false, $order=false, $publiTable=null)
	{
		$res = $this->_select($tablesList, $fields, $where, $order, $publiTable);
		$rows = array();
		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$rows[] = $row;
		}
		return $rows;
		 
		return $res->fetchRow(MDB2_FETCHMODE_ASSOC);
	}

	// {{{ _select
	/**
	 * execute a query for select data
	 *
	 * @access public
	 * @return array   array of users
	 */
	function _select($tablesList, $fields, $where=false, $order=false, $publiTable=null)
	{
		$db = $this->_db;
		if (is_null($db)) return $db;

		$prefix = $this->_prefix;
		if (is_array($tablesList))
		$tables = $tablesList;
		else
		$tables[] = $tablesList;

		// Liste des tables
		foreach($tables as $table)
		{
	  $ta = preg_replace("/join ([.]*)/i",
			     "JOIN $prefix$1", $table);
	  $i=0;
	  while($ta{$i} == '(') $i++;
	  if ($i)
	  $tableNames[] = substr($ta, 0, $i)."{$prefix}".substr($ta, $i);
	  else
	  $tableNames[] = "{$prefix}{$ta}";
		}

		// Liste des champs
		if (is_array($fields))
		$fieldString = implode(',', $fields);
		else
		$fieldString = $fields;
		$tableString = implode(',', $tableNames);
		$sql = "SELECT $fieldString FROM $tableString";
		$limit = false;
		if (!is_null($publiTable))
		{
	  $mask = utvars::getMask();
	  if (!is_array($publiTable))
	  $filtres[] = $publiTable;
	  else
	  $filtres = $publiTable;
	  foreach($filtres as $filtre)
	  $limits[] = "{$filtre}_pbl & {$mask} > 0";
	  $limit = implode(' AND ', $limits);
		}

		$groupby = false;
		if ($where)
		{
	  $groupby = stristr($where, 'group by');
	  if ($groupby)
	  $where = substr($where, 0, strlen($where) - strlen($groupby));
		}

		if ($where && $limit)
		$sql .= " WHERE $where AND $limit";
		else if ($where)
		$sql .= " WHERE $where";
		else if ($limit)
		$sql .= " WHERE $limit";

		if ($groupby)
		$sql .= " $groupby ";

		if ($order)
		$sql .= " ORDER BY $order";

		$res = $db->query($sql);
		if (DB::isError($res))
		$this->_error($res);

		return $res;
	}
	// }}}

	// {{{ _selectFirst
	/**
	 * Return the first valuof a query
	 *
	 * @access public
	 * @return array   array of users
	 */
	function _selectFirst($tables, $fieldsList, $where=false, $order=false)
	{
		if (is_array($fieldsList))
		$fields = $fieldsList;
		else
		$fields[] = $fieldsList;

		$res = $this->_select($tables, $fields, $where, $order);
		if (DB::isError($res))
		$this->_error($res);

		if (!$res->numRows()) return NULL;
		$entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
		if (count($entry) == 1)
		return reset($entry);
		else
		return $entry;
	}
	// }}}



	// {{{ _delete
	/**
	 * execute a query for delete data
	 *
	 * @access public
	 * @return array   array of users
	 */
	function _delete($table, $where=false)
	{
		$db = $this->_db;
		if (DB::isError($db)) return $db;
		$prefix = $this->_prefix;

		$tableName = $prefix.$table;
		$sql = "DELETE FROM $tableName";
		if ($where)
		$sql .= " WHERE $where";

		return $db->query($sql);
	}
	// }}}


	// {{{ _getTranslate
	/**
	 * Recherche les donnes traduites dans la base
	 *
	 * @access public
	 * @return array   array of users
	 */
	function _getTranslate($table, $fields, $regId, $values)
	{
		$lang = utvars::getLanguage();
		if ($lang != 'fra')
		{
	  $column = array('trad_value');
	  $tables = array('traduction');
	  foreach($fields as $field)
	  {
	  	$where = "trad_table = '$table'".
		" AND trad_field = '$field'".
		" AND trad_regId = $regId".
		" AND trad_lang ='$lang'";
	  	$res = $this->_select($tables, $column, $where);
	  	if (DB::isError($res))
	  	$this->_error($res);
	  	if ($res->numRows())
	  	{
	  		$value = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  		$values[$field] = $value['trad_value'];
	  	}
	  }
		}
		return $values;
	}
	// }}}

	// {{{ _updtTranslate
	/**
	 * Enregistre les donnees traduites dans la base
	 *
	 * @access public
	 * @return array   array of users
	 */
	function _updtTranslate($table, $fields, $regId, $values)
	{
		$lang = utvars::getLanguage();
		if ($lang != 'fra')
		{
	  $column = array('trad_id', 'trad_value');
	  $tables = array('traduction');
	  foreach($fields as $field)
	  {
	  	if (!isset($values[$field])) continue;
	  	$where = "trad_table = '$table'".
		" AND trad_field = '$field'".
		" AND trad_regId = $regId".
		" AND trad_lang ='$lang'";
	  	$res = $this->_select($tables, $column, $where);
	  	if (DB::isError($res))
	  	$this->_error($res);

	  	if ($res->numRows())
	  	{
	  		$value = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  		$ou = "trad_id=".$value['trad_id'];
	  		$data = array();
	  		$data['trad_value'] = $values[$field];
	  		$res = $this->_update('traduction', $data, $ou);
	  	}
	  	else
	  	{
	  		$data = array();
	  		$data['trad_table'] = $table;
	  		$data['trad_field'] = $field;
	  		$data['trad_regId'] = $regId;
	  		$data['trad_lang']  = $lang;
	  		$data['trad_value']  = $values[$field];
	  		$res = $this->_insert('traduction', $data);
	  	}
	  	unset($values[$field]);
	  	if (DB::isError($res))
	  	$this->_error($res);
	  }
		}
		return $values;
	}
	// }}}


	// {{{ _error
	/**
	 * Gerer les erreurs d'acces a la base
	 *
	 * @access public
	 * @return array   array of users
	 */
	function _error($db)
	{

		// Get inforamtion in a variable

		ob_start();
		echo "eventId:".utvars::getEventId();
		echo "\nuserId:".utvars::getUserId();
		echo "\nvariables GET:\n".utvars::getUserId();
		print_r($_GET);
		echo "\nvariables POST:\n".utvars::getUserId();
		print_r($_POST);

		print_r($db->getBacktrace());
		$buf = ob_get_contents();
		ob_end_clean();

		// Send a email for developpers
		require_once "utils/utmail.php";
		$mailer = new utmail();
		$mailer->subject("Erreur base de donnes");
		$mailer->from("no-reply@badnet.org");
		$mailer->body($buf);
		//$res =  @$mailer->send('cage@free.fr');

		print_r($db->getBacktrace());
		// Display the current page with and error message

		$page =& utvars::getPage();
		$page->addErr('msgDataBaseError');
		//$page->addWng($buf);
		$page->display();

		exit;
	}
	// }}}

}
?>