<?php
/*****************************************************************************
 !   Module     : services
 !   File       : $Source: /cvsroot/aotb/badnet/services/dba.php,v $
 !   Version    : $Name: HEAD $
 !   Revision   : $Revision: 1.4 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:24 $
 ******************************************************************************/


/**
 * Classe d'acces a la base de donnee
 *
 * @author Gerard CANTEGRIL
 *
 */

define("DB_TYPE",   0);
define("DB_LOGIN",  1);
define("DB_SERVER", 2);
define("DB_BASE",   3);
define("DEF_LANG",  4);
define("DB_PWD",    5);
define("DB_PREFIX", 6);

class dba
{
	// {{{ properties
	/**
	* Objet d'acces a la base de donnees
	*
	* @var string
	* @access private
	*/
	var $_db = NULL;

	/**
	 * Prefixe des tables de la base de donnees
	 *
	 * @var string
	 * @access private
	 */
	var $_prefix = 'bdnet_';

	/**
	 * Indicateur d'erreur
	 *
	 * @var boolean
	 * @access private
	 */
	var $_isError = false;

	/**
	 * Objet decrivant l'erreur
	 *
	 * @var object
	 * @access private
	 */
	var $_errorObj = NULL;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function dba()
	{
		// Data Source Name: This is the universal connection string
		$file = "data/cnx.dat";
		$file = "../data/cnx.dat";
		$fp = @fopen($file, "r");
		$buf = '';
		if (!$fp)
		{
			$this->_err(new PEAR_ERROR("missing config file $file"));
			return;
		}

		$buf = fgets($fp, 4096);
		fclose($fp);
		$fds = preg_split("/\t/", $buf);
		$this->_prefix = 'aotb_';
		if (isset($fds[DB_PREFIX]) && strlen(trim($fds[DB_PREFIX]))) $this->_prefix = $fds[DB_PREFIX].'_';

		if ($fds[DB_TYPE] == 'mysql')
		{
			$dsn = $fds[DB_TYPE] . ':host=' . $fds[DB_SERVER];
			$dsn .= ';dbname=' . $fds[DB_BASE];
			$this->_db = new PDO($dsn, $fds[DB_LOGIN], $fds[DB_PWD]);
		}
		else
		{
			$dsn = $fds[DB_TYPE] . ':' . $fds[DB_BASE];
			$this->_db = new PDO($dsn);
		}

		if (empty($this->_db)) $this->_err(new PEAR_ERROR("Erreur ouverture bd $dsn"));
	}

	/**
	 * Renvoi le prefixe utilise pour les tables badnet
	 *
	 * @access public
	 * @return string
	 */
	function getPrefix()
	{
		return $this->_prefix;
	}

	/**
	 * Indicateur de connexion
	 *
	 * @access public
	 * @return array   true ou false
	 */
	function isConnected()
	{
		return !is_null($this->_db);
	}

	/**
	 * Ferme la connexion avec la base
	 *
	 * @access public
	 * @return array   true ou false
	 */
	function disconnect()
	{
		$this->_db = null;
	}

	/**
	 * Indicateur d'erreur
	 *
	 * @access public
	 * @return true ou false
	 */
	function isError()
	{
		return $this->_isError;
	}

	/**
	 * Renvoi l objet en erreur
	 *
	 * @access public
	 * @return array   true ou false
	 */
	function getErrorObj()
	{
		return $this->_errorObj;
	}

	/**
	 * Execute la requete et renvoi les resultats
	 *
	 * @access public
	 * @return array
	 */
	function query($query)
	{
		$res = $this->_db->query($query);
		if ($res === false) $this->_err($query);
		return $res;
	}

	// {{{ select
	/**
	* execute a query for select data
	*
	* @access public
	* @return array   array of users
	*/
	function select($tablesList, $fields, $where=false, $order=false, $publiTable=null)
	{
		$prefix = $this->getPrefix();
		if (is_array($tablesList)) $tables = $tablesList;
		else $tables[] = $tablesList;

		foreach($tables as $table)
		{
			$ta = preg_replace("/join ([.]*)/i", "JOIN $prefix$1", $table);
			$i=0;
			while($ta{$i} == '(') $i++;
			if ($i) $tableNames[] = substr($ta, 0, $i)."{$prefix}".substr($ta, $i);
			else  $tableNames[] = "{$prefix}{$ta}";
		}

		if (is_array($fields)) $fieldString = implode(',', $fields);
		else $fieldString = $fields;
		$tableString = implode(',', $tableNames);
		$sql = "SELECT $fieldString FROM $tableString";
		$limit = false;
		if (!is_null($publiTable))
		{
			$mask = utvars::getMask();
			if (!is_array($publiTable)) $filtres[] = $publiTable;
			else $filtres = $publiTable;
			foreach($filtres as $filtre) $limits[] = "{$filtre}_pbl & {$mask} > 0";
			$limit = implode(' AND ', $limits);
		}

		$groupby = false;
		if ($where)
		{
			$groupby = stristr($where, 'group by');
			if ($groupby) $where = substr($where, 0, strlen($where) - strlen($groupby));
		}

		if ($where && $limit) $sql .= " WHERE $where AND $limit";
		else if ($where) $sql .= " WHERE $where";
		else if ($limit) $sql .= " WHERE $limit";

		if ($groupby) $sql .= " $groupby ";

		if ($order)	$sql .= " ORDER BY $order";
		$res = $this->query($sql);
		return $res;
	}

	function numRows($aSt)
	{
		$all = $aSt->fetchAll();
		if ($all) return count($all);
		else return 0;
	}
	
	/**
	 * Execute la requete et renvoi le premier resultat
	 *
	 * @access public
	 * @return array
	 */
	function selectFirst($tables, $fieldsList, $where=false, $order=false)
	{
		if (is_array($fieldsList)) $fields = $fieldsList;
		else $fields[] = $fieldsList;

		$res = $this->select($tables, $fields, $where, $order);
		if ($this->isError()) return NULL;
		$entry = $res->fetch(PDO::FETCH_ASSOC);
		if ($entry && count($entry) == 1) return reset($entry);
		else return $entry;
	}

	/**
	 * Mise a jour de la base de donnee
	 *
	 * @access public
	 * @return array   array of users
	 */
	function update($table, $fields, $where, $eventId = false)
	{
		$prefix = $this->getPrefix();

		// Mise a jour de la date de derniere
		// modification du tournoi
		$tables = array('draws', 'matchs', 'pairs', 'registration',
		      'rounds', 'teams', 'ties'); 
		if ($eventId && in_array($table, $tables))
		{
			$date = date(DATE_FMT);
			$sql = "UPDATE {$prefix}events SET evnt_lastupdate='$date'".
	    " WHERE evnt_id=$eventId";
			$res = $this->query($sql);
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
			if(preg_match("/eval\((.*)\)/", $value, $regs)) $sql .= "$glue $field=$regs[1]";
			else $sql .= "$glue $field=". $this->_db->quote($value);
			$glue = ',';
		}
		if ($where) $sql .= " WHERE $where";
		$res = $this->query($sql);
		if (isset($fields[$prefixTable.'_id'])) return $fields[$prefixTable.'_id'];
		else return $res;
	}

	/**
	 * execute a query for insert data
	 *
	 * @access public
	 * @return array   array of users
	 */
	function insert($table, $fields)
	{
		$prefix = $this->_prefix;

		$tableName = $prefix.$table;
		$field = each($fields);
		$items = explode('_', $field['key']);
		$prefixTable = $items[0];
		if (!isset($fields[$prefixTable.'_cre'])) $fields[$prefixTable.'_cre'] = date(DATE_FMT);
		if (!isset($fields[$prefixTable.'_updt'])) $fields[$prefixTable.'_updt'] = date(DATE_FMT);
		if (!isset($fields[$prefixTable.'_pbl'])) $fields[$prefixTable.'_pbl'] = WBS_DATA_CONFIDENT;
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
			$fields[$prefixTable.'_uniId'] = "-1";
		}
		$keys = array_keys($fields);
		$glue = '';
		$values = "";
		foreach($fields as $field=>$value)
		{
			$values .= $glue . $this->_db->quote($value);
			$glue = ",";
		}
		//$values = array_values($fields);
		$sql = "INSERT INTO $tableName (" . implode(",", $keys) . ") ";
		$sql .= "VALUES ($values)";
		$res = $this->query($sql);
		
		$id = $this->_db->lastInsertId();
		
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
		(!isset($fields[$prefixTable.'_uniId']) OR $fields[$prefixTable.'_uniId'] == -1))
		{
			$localDBId = $this->selectFirst('meta', 'meta_value', "meta_name='databaseId'");
			if (empty($localDBId))
			{
				$localDBId = gmmktime();
				$this->update('meta', array('meta_value', $localDBId), "meta_name='databaseId'");
			}
			$fields[$prefixTable.'_uniId'] = "$localDBId:$id;";
		}
		
		return $id;
	}

	/**
	 * execute a query for delete data
	 *
	 * @access public
	 * @return array   array of users
	 */
	function delete($table, $where=false)
	{
		$prefix = $this->_prefix;

		$tableName = $prefix.$table;
		$sql = "DELETE FROM $tableName";
		if ($where)	$sql .= " WHERE $where";

		return $this->query($sql);
	}

	/**
	 * Fonctionn erreur
	 *
	 * @access public
	 * @return array
	 */
	function _err($msg=null)
	{
		$this->_isError = true;
		$this->_errorObj = $this->_db->errorInfo();
		$this->_callTree = ''; //$err->getBackTrace();
		echo "...msg=$msg<br>";
		echo "<br>...err="; print_r($this->_db->errorInfo()); echo "<br>";
		/* Todo: envoyer l'erreur a badnet.org pour debug */
	}
}
?>
