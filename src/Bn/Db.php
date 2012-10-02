<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

class Bn_Db
{
	private $_db   = null;
	private static $_dbs   = array();
	private	static $_dsn   = array();
	private	static $_pwd   = array();
	private	static $_user   = array();
	private	static $_base = array();
	private	static $_prefix = array();
	private	static $_charset = array();
	private	static $_type = array();
	private	static $_rbase = array();

	static public function trace()
	{
		echo count(self::$_dbs) .'<br>';
		foreach(self::$_dbs as $key=>$db) echo $key . '<br>';
	}

	/**
	 * Fermeture de tous les acces aux bases de données
	 */
	static public function closeDbs()
	{
		foreach(self::$_dbs as $db)
		{
			unset($db);
		}
	}

	/**
	 * Acces au singleton
	 * @param 	$aIndice	Rubrique de la base de donnee dans le fichier de configuration
	 * @return 	Bn_db
	 */
	static public function getDb($indice = 0)
	{
		if ( empty(self::$_dbs[$indice]) )
		{
			self::$_dbs[$indice]  = new Bn_Db($indice);
		}
		return self::$_dbs[$indice]->_db;
	}

	/**
	 * Constructeur
	 *
	 * @access  public
	 */
	private function __construct($aIndice)
	{
		$dsn = $this->getDsn($aIndice);
		if ( empty($dsn) )
		{
			$this->_db = null;
			Bn::log('Bn_Db::_construct:Pas de dsn dans le fichier de conf indice ' . $aIndice);
			return;
		}
		$pwd   = Bn_Db::getPwd($aIndice);
		$login = Bn_Db::getLogin($aIndice);
		$type  = Bn_Db::getType($aIndice);
		try {
			$this->_db = new PDO($dsn, $login, $pwd);
			$this->_db->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
			if ($type == 'sqlite')
			{
				$db = Bn_Db::getName($aIndice);
				$dbr = Bn_Db::getRealName($aIndice);
				$q = "ATTACH '" . $dbr . "' AS " . $db;
				$res = $this->_db->query($q);
				if ( $res === false )
				{
					Bn::log('Bn_Db::_construct:'. $q);
					Bn::log($this->_db->errorInfo());
				}

			}
		}
		catch(PDOException $e)
		{
			$this->_db == null;
			Bn::log('Bn_Db::_construct:Connection failed: ' . $e->getMessage() . ";$dsn");
		}
	}

	/**
	 * Acces au pwd pour l'acces a la base de donnees
	 *
	 */
	static public function getLogin($aIndice=0)
	{
		if ( empty(self::$_user[$aIndice]) ) Bn_DB::loadConfig($aIndice);
		if ( empty(self::$_user[$aIndice]) ) return null;
		else return self::$_user[$aIndice];
	}

	static public function getUser($aIndice=0){ return Bn_DB::getLogin($aIndice);}

	/**
	 * Acces au pwd pour l'acces a la base de donnees
	 *
	 */
	static public function getPwd($aIndice=0)
	{
		if ( empty(self::$_pwd[$aIndice]) ) Bn_DB::loadConfig($aIndice);
		if ( empty(self::$_pwd[$aIndice]) ) return null;
		else return self::$_pwd[$aIndice];
	}

	/**
	 * Acces au dsn pour l'acces a la base de donnees
	 *
	 */
	static public function getDsn($aIndice=0)
	{
		if ( empty(self::$_dsn[$aIndice]) ) Bn_DB::loadConfig($aIndice);
		if ( empty(self::$_dsn[$aIndice]) ) return null;
		else return self::$_dsn[$aIndice];
	}

	/**
	 * Acces au prefixe des tables
	 *
	 */
	static public function getPrefix($aIndice=0)
	{
		if ( empty(self::$_prefix[$aIndice]) ) Bn_DB::loadConfig($aIndice);
		if ( empty(self::$_prefix[$aIndice]) ) return null;
		else return self::$_prefix[$aIndice];
	}

	/**
	 * Acces au charset  de la base
	 *
	 */
	static public function getCharset($aIndice=0)
	{
		if ( empty(self::$_charset[$aIndice]) ) Bn_DB::loadConfig($aIndice);
		if ( empty(self::$_charset[$aIndice]) ) return null;
		else return self::$_charset[$aIndice];
	}

	/**
	 * Acces au nom de la base
	 *
	 */
	static public function getName($aIndice=0)
	{
		if ( empty(self::$_base[$aIndice]) ) Bn_DB::loadConfig($aIndice);
		if ( empty(self::$_base[$aIndice]) ) return null;
		else return self::$_base[$aIndice];
	}

	/**
	 * Acces au nom reel de la base
	 *
	 */
	static public function getRealName($aIndice=0)
	{
		if ( empty(self::$_rbase[$aIndice]) ) Bn_DB::loadConfig($aIndice);
		if ( empty(self::$_rbase[$aIndice]) ) return null;
		else return self::$_rbase[$aIndice];
	}

	/**
	 * Acces au nom de la base
	 *
	 */
	static public function getType($aIndice=0)
	{
		if ( empty(self::$_type[$aIndice]) ) Bn_DB::loadConfig($aIndice);
		if ( empty(self::$_type[$aIndice]) ) return null;
		else return self::$_type[$aIndice];
	}

	/**
	 * Chargement de la configuration
	 *
	 * @param unknown_type $aIndice
	 * @return unknown
	 */
	static public function loadConfig($aIndice)
	{
		$config = parse_ini_file(BN_CONFIG_FILE, true);
		$section = $aIndice ? 'database'.$aIndice:'database';
		if( !isset($config[$section]) )
		{
			$msg = 'Fichier de configuration incorrect. Section '. $section . ' absente';
			return null;
		}
		$basedef = $config[$section];
		$dsn = $basedef['type'] . ':';
		if ($basedef['type'] == 'mysql')
		{
			$dsn .='host=' . $basedef['host'];
			$dsn .= ';dbname=' . $basedef['base'];
		}
		else if ($basedef['type'] == 'sqlite')
		{
			$dsn .=  $basedef['base'];
		}

		self::$_dsn[$aIndice]  = $dsn;
		self::$_pwd[$aIndice]  = $basedef['pwd'];
		self::$_user[$aIndice] = $basedef['user'];
		self::$_base[$aIndice] = basename($basedef['base']);
		self::$_rbase[$aIndice] = $basedef['base'];
		self::$_prefix[$aIndice] = $basedef['prefix'];
		//self::$_charset[$aIndice] = $basedef['charset'];
		self::$_type[$aIndice] = $basedef['type'];
	}

}

class GridParam
{

}

class Bn_query extends Bn_error
{
	private $_charset;
	private $_indice;
	private $_prefix;
	private $_order;
	private $_group;
	private $_db;
	private $_tables = array();
	private $_leftTables = array();
	private $_fields = array();
	private $_ifields = array();
	private $_values = array();
	private $_ivalues = array();
	private $_where  = array();
	private $_unions = array();
	private $_first  = 1;
	private $_nb     = 0;
	private $_gridParam;
	private $_baseName = '';
	private $_type = '';

	public function getConcat($aStrs)
	{
		if($this->_type == 'mysql')
		{
			$str = 'concat(' . implode(',', $aStrs) . ')';
		}
		if($this->_type == 'sqlite')
		{
			$str = implode(' || ', $aStrs);
		}
		return $str;
	}


	/**
	 * Objet d'acces a la base de donnee
	 * @param mixed 	$aTables		Nom des tables pour la requete
	 * @param integer 	$aIndice		Nom de la base dans le fichier de configuration
	 * @param integer 	$aBasename		Nom de la base a ouvrir
	 * @return Bn_query
	 */
	public function __construct($aTables=array(), $aIndice=0, $aDataBase = '')
	{
		$this->_indice = $aIndice;
		$this->_db = Bn_db::getDb($this->_indice);

		if ( empty($this->_db) ) return null;

		$this->_prefix = Bn_db::getPrefix($this->_indice);
		$this->_charset = Bn_db::getCharset($this->_indice);
		$this->_type = Bn_db::getType($this->_indice);
		if ( !empty($aDataBase)) $this->_baseName = $aDataBase;
		$this->_baseName = Bn_db::getName($this->_indice);
		$this->setTables($aTables);
	}

	/**
	 * Destructeur de la classe
	 *
	 */
	public function __destruct() {
	}

	/**
	 * Charge et execute un fichier sql
	 *
	 * @param unknown_type $aFile
	 * @return unknown
	 */
	public function loadFile($aIndice=0)
	{
		if ( empty($this->_db))	return false;

		$file = '../Script/Sql/' . Bn_db::getName($aIndice) . '.sql';
		$sql = file($file, FILE_SKIP_EMPTY_LINES); // on charge le fichier SQL
		$requetes = '';
		foreach($sql as $l)
		{ // on le lit
			if (substr(trim($l),0,2)!="--")
			{ // suppression des commentaires
				$requetes .= $l;
			}
		}

		$reqs = explode(";", $requetes);
		foreach($reqs as $q)
		{
			$q = trim($q);
			if (empty($q)) continue;
			$res = $this->_db->query($q);
			if ( $res === false )
			{
				Bn::log($this->_db->errorInfo());
			}
		}
		return true;
	}


	/**
	 * Debut d'une transaction
	 */
	public function beginTrans()
	{
		if ( empty($this->_db))	return false;
		$this->_db->beginTransaction();
	}

	/**
	 * Fin d'une transaction
	 */
	public function endTrans()
	{
		if ( empty($this->_db))	return false;
		$this->_db->commit();
	}

	/**
	 * Abandon d'une transaction
	 */
	public function cancelTrans()
	{
		if ( empty($this->_db))	return false;
		$this->_db->rollback();
	}

	/**
	 * Teste l'existence d'une base de données
	 * @param $aIndice indice de la section dans le fichier de conf
	 *
	 */
	public function existsDatabase($aIndice=0)
	{
		if ( empty($this->_db))	return false;

		$database =  Bn_db::getName($aIndice);
		// Recherche de la base de données
		$q = "SHOW DATABASES LIKE '" . $database . "'";
		$st = $this->_db->query($q);
		$res = true;
		if (!empty($st)) $res = $st->fetchAll();
		return !empty($res);
	}

	/**
	 * Suppression d'une base de données
	 * @param $aDatabase base de donnes a supprimer
	 *
	 */
	public function dropDatabase($aDatabase)
	{
		if ( empty($this->_db))	return false;
		// Suppression de la base de données
		$q = 'DROP DATABASE IF EXISTS ' . $aDatabase;
		$res = $this->_db->query($q);
		return true;
	}


	/**
	 * Duplication d'une base de données
	 * @param $aSource base de donnes modele
	 * @param $aCible nom de la base de donnees a creer
	 *
	 */
	public function dupDatabase($aSource, $aCible)
	{
		if ( empty($this->_db))	return false;
		// Liste des tables de base de données
		$q = 'SHOW TABLES FROM ' . $aSource;
		$st = $this->_db->query($q);
		$tables = $st->fetchAll(PDO::FETCH_COLUMN);

		// Creer la base de donnee
		$q = 'CREATE DATABASE ' . $aCible;
		$res = $this->_db->query($q);
		// Creer les tables
		foreach($tables as $table)
		{
			$q = 'SHOW CREATE TABLE ' . "$aSource.$table";
			$st = $this->_db->queryAll($q);
			$res = $st->fetchAll();
			$name = $res[0]['table'];
			$q = $res[0]['create table'];
			$q = str_replace("`$name`", "$aCible.$name", $q);
			$res = $this->_db->query($q);
		}
		return true;
	}

	/**
	 * Creation d'une base de données
	 *
	 */
	public function createDatabase($aName)
	{
		// Creer la base de donnee
		$q = 'CREATE DATABASE ' . $aName;
		$res = $this->_db->query($q);
		return true;
	}


	/**
	 * Renvoie les colonnes d'une table
	 */
	public function getColumnDef()
	{
		if ( empty($this->_db))	return false;

		$columns = array();
		$table = reset($this->_tables);
		if ($this->_type == 'mysql')
		{
			$q = "SHOW COLUMNS FROM $table";
			$res = $this->_db->query($q);
			if ( $res === false )
			{
				$this->_error('getColumnDef:'.$q);
				$this->_error($this->_db->errorInfo());
				return $columns;
			}
			$columns = $res->fetchAll(PDO::FETCH_COLUMN);
		}
		if ($this->_type == 'sqlite')
		{
			$token = explode('.', $table);
			$q = "PRAGMA table_info('" . $token[1] . "')";
			$res = $this->_db->query($q);
			if ( $res === false )
			{
				$this->_error('getColumnDef:'.$q);
				$this->_error($this->_db->errorInfo());
				return $columns;
			}
			$columns = $res->fetchAll(PDO::FETCH_COLUMN, 1);
		}

		return array_map('strtolower', $columns);
	}

	/**
	 * Changer la base de donnee active
	 * @param string 	$aDatabase		Nom de la base de donnees
	 * @return void
	 */
	public function setDatabase($aDatabase)
	{
		echo 'funcion obsolete a abandonner--------';
		//if ( !empty($this->_db) ) $this->_db->setDatabase($aDatabase);
	}

	/**
	 * Mets a jour un enregistrement ou le cree s'il n'existe pas
	 *
	 * @param $aTable    : Nom de la table
	 * @param $p_as_fields  : Champs et leurs valeurs (tableau associatif : field=>value)
	 * @param $aWhere    : Clause d'identification de l'enregistrement a mettre a jour
	 *                 soit une clause where, soit le numero de l'enregistrament,
	 *                 soit rien
	 * @return id du nouvel enregistrement
	 */
	public function replaceRow($aAutoId = true)
	{
		if ( empty($this->_db))	return false;

		if ( $this->getCount() )
		{
			$id = $this->updateRow();
		}
		else
		{
			$id = $this->addRow($aAutoId);
		}
		return $id;
	}

	/**
	 * Ajoute un enregistrement
	 * @name addRow
	 * @param $p_b_autoId   Indicateur pour rajouter un id (nomTable_id) au nouvel enregistrement
	 */
	public function addRow($aAutoId = true, $aBn = true)
	{
		$id = null;
		// Verification de la connexion
		if ( empty($this->_db))	return false;
		if ( !count($this->_values) )
		{
			$this->_error('LOC_MSG_ERR_NO_VALUE');
			return $id;
		}

		// Identifiant de la nouvelle table
		$table = reset($this->_tables);
		reset($this->_values);
		list($key, $val) = each($this->_values);
		$fieldPrefix = explode('_', $key);

		// Identifiant de l'enregistrement
		if ( $aAutoId )	$this->delValue($fieldPrefix[0] . '_id');
		if (!empty($this->_values[$fieldPrefix[0] . '_id']) && ($this->_values[$fieldPrefix[0] . '_id'] == -1))
			$this->delValue($fieldPrefix[0] . '_id');
		if($aBn) $this->addValue($fieldPrefix[0] . '_cre', date('Y-m-d H:i:s'));

		// Execution de la requete
		$query = 'SET NAMES UTF8';
		$st = $this->_db->query($query);
		$query = "INSERT INTO $table (" . implode(', ', $this->_ifields) . ') VALUES (' . implode(', ', $this->_ivalues) . ')';
		$res   = $this->_db->query($query);
		if ( $res === false )
		{
			$this->_error('addRow:'. $query);
			$this->_error($this->_db->errorInfo());
			$this->_error(debug_backtrace(false ));
		}
		$id = $this->_db->lastInsertId();
		return $id;
	}

	public function emptyTable($aTable)
	{
		if ( empty($this->_db))	return false;
		if ( strpos($aTable, '.') === false) $table = $this->_baseName . '.' . $this->_prefix . $aTable;
		else $table = $this->_prefix . $aTable;

		$query = 'DELETE FROM ' .  $table;
		$res   = $this->_db->query($query);
		if ( $res === false )
		{
			$this->_error('emptyTable:'. $query);
			$this->_error($this->_db->errorInfo());
		}

	}

	/**
	 * Ajoute une valeur a la requete
	 *
	 * @param string $aField
	 * @param mixed  $aValue
	 */
	public function addValue($aField, $aValue)
	{
		if ( empty($this->_db))	return false;
		$this->_values[$aField] = $aField . "=" . $this->_db->quote($aValue);

		$pos = array_search($aField, $this->_ifields);
		if (empty($this->_ifields) || $pos === false)
		{
			$this->_ivalues[] = $this->_db->quote($aValue);
			$this->_ifields[] = $aField;
		}
		else
		{
			$this->_ivalues[$pos] = $this->_db->quote($aValue);
		}
		return $aValue;
	}

	/**
	 * Ajoute une valeur a la requete
	 *
	 * @param string $aField
	 * @param mixed  $aValue
	 */
	public function delValue($aField)
	{
		if ( empty($this->_db))	return false;
		$value = null;
		if ( !empty($this->_values[$aField]) )
		{
			$value = $this->_values[$aField];
			unset($this->_values[$aField]);

			$pos = array_search($aField, $this->_ifields);
			unset($this->_ivalues[$pos]);
			unset($this->_ifields[$pos]);
		}
		return $value;
	}

	/**
	 * Ajoute une valeur a la requete apres avoir supprime celle existante
	 *
	 * @param string $aField
	 * @param mixed  $aValue
	 */
	public function setValue($aField, $aValue)
	{
		unset($this->_values);
		unset($this->_ivalues);
		unset($this->_ifields);
		$this->addValue($aField, $aValue);
	}

	/**
	 * Supprime un enregistrement
	 *
	 * @param string $aWhere
	 */
	public function deleteRow($aWhere = null)
	{
		if ( empty($this->_db))	return false;

		// Identifiant de la nouvelle table
		$table  = reset($this->_tables);
		$field  = reset($this->_fields);

		if ( !is_null($aWhere) )
		{
			if ( is_numeric($aWhere) )
			{
				$tip = explode('_', $field);
				$where = reset($tip) . '_id=' . $aWhere;
			}
			else
			{
				$where = $aWhere;
			}
		}
		else
		{
			$where = $this->_getWhere();
		}

		$query = "DELETE FROM $table WHERE $where";
		$res   = $this->_db->query($query);
		if ( $res === false )
		{
			$this->_error('deleteRow:' . $query);
			$this->_error($this->_db->errorInfo());
			return null;
		}
		return true;
	}

	/**
	 * Retourne les enregistrements sous forme de tableau pour une grid
	 * @param int $aDecal  decalage de l'index pour le tri 0 ou 1 s'il y a une colonne avec numero de ligne
	 * @param boolean $aAssoc retourner un tableau associatif ou indexe
	 * @return array
	 */
	public function getGridRows($aDecal = 0, $aAssoc=false)
	{
		// get the requested page
		$page = Bn::getValue('page', 1);
		// get how many rows we want to have into the grid
		// rowNum parameter in the grid
		$nbRow = Bn::getValue('rows', 25);
		// get index row - i.e. user click to sort
		// at first time sortname parameter - after that the index from colModel
		$sortIndex = Bn::getValue('sidx');
		// sorting order - at first time sortorder

		$sortOrder = Bn::getValue('sord');
		//total des enregistrements
		$nbRecords = $this->getCount();
		$totalPage=0;
		if ($nbRecords>0) $totalPage = ceil($nbRecords/$nbRow);
		if ($page > $totalPage) $page=$totalPage;

		// Enregistrement a recuperer
		if ($page){
			$first = (($nbRow*$page)-$nbRow + 1);
		}else{
			$page = 1;
			$first = 1;
		}
		$this->setLimit($first, $nbRow);

		// Critere de tri
		$order = '';
		if ($sortIndex){
			$order = $sortIndex." ".$sortOrder;
		}
		$this->setOrder($order);

		$result = $this->getRows($aAssoc);
//echo $this->getQuery();
		$this->_gridParam = new GridParam();
		$this->_gridParam->page    = $page;
		$this->_gridParam->total   = $totalPage;
		$this->_gridParam->records = $nbRecords;
		$this->_gridParam->nb      = $nbRow;
		$this->_gridParam->first   = $first;
		return $result;
	}

	/**
	 * Retourne les parametres pour la datagrid
	 *
	 * @return array
	 */
	public function getGridParam()
	{
		return $this->_gridParam;
	}

	/**
	 * Retourne les enregistrements sous forme de tableau
	 *
	 * @param boolean $p_b_assoc
	 * @return array
	 */
	public function getRows($aAssoc = true)
	{
		$res  = array();
		if ( empty($this->_db))	return $res;

		// construction de la requete
		$query = $this->getQuery();

		// Fixation des limites
		if ( $this->_nb > 0 )
		{
			//$res = $this->_db->setLimit($this->_nb, $this->_first-1);
			$query .= " LIMIT " . $this->_nb . ' OFFSET ' . ($this->_first-1);
		}

		// Recuperation des donnees
		$mode = $aAssoc ? PDO::FETCH_ASSOC : PDO::FETCH_NUM;
		$st = $this->_db->query('SET NAMES UTF8');
		$st = $this->_db->query($query);
		if ( $st === false )
		{
			$this->_error('getRows:' . $query);
			$this->_error($this->_db->errorInfo());
			$this->_error(debug_backtrace(false ));
			return array();
		}
		$res = $st->fetchAll($mode);
		return $res;
	}

	/**
	 * Retourne les enregistrements sous forme de tableau
	 * tab[champ1] = champ2
	 *
	 * @return array
	 */
	public function getLov()
	{
		$res  =array();
		if ( empty($this->_db))	return $res;

		// construction de la requete
		$query = $this->getQuery();

		// Fixation des limites
		if ( $this->_nb > 0 )
		{
			//$res = $this->_db->setLimit($this->_nb, $this->_first-1);
			$query .= " LIMIT " . $this->_nb . ' OFFSET ' . ($this->_first-1);
		}

		// Recuperation des donnees
		$st = $this->_db->query('SET NAMES UTF8');
		$st = $this->_db->query($query);
		if ( $st === false )
		{
			$this->_error('getLov:' . $query);
			$this->_error($this->_db->errorInfo());
			$this->_error(debug_backtrace(false ));
			return array();
		}
		$res = $st->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP|PDO::FETCH_UNIQUE);
		return $res;
	}

	/**
	 * Retourne le permier enregistrement
	 *
	 * @param boolean $p_b_assoc
	 */
	public function getRow($aAssoc = true)
	{
		$res = array();
		// Verification de la connexion
		if ( empty($this->_db) ) return $res;

		$mode = $aAssoc ? PDO::FETCH_ASSOC : PDO::FETCH_NUM;
		$query = $this->getQuery();
		$st = $this->_db->query('SET NAMES UTF8');
		$st = $this->_db->query($query);
		if ( $st === false )
		{
			$this->_error('getRow:' . $query);
			$this->_error($this->_db->errorInfo());
			$this->_error(debug_backtrace(false ));
			return array();
		}
		$res = $st->fetch($mode);
		return $res;
	}

	/**
	 * Retourne une colonne. La premiere colonne est 1
	 *
	 * @param integer $aNumColumn = 1
	 */
	public function getCol($aNumColumn = 1)
	{
		$res = array();
		// Verification de la connexion
		if ( empty($this->_db) ) return $res;

		// Fixation des limites
		$query = $this->getQuery();
		if ( $this->_nb > 0 )
		{
			//$res = $this->_db->setLimit($this->_nb, $this->_first-1);
			$query .= " LIMIT " . $this->_nb . ' OFFSET ' . ($this->_first-1);
		}
		$st = $this->_db->query('SET NAMES UTF8');
		$st = $this->_db->query($query);
		if ( $st === false )
		{
			$this->_error('getCol->fetch:' . $query);
			$this->_error($this->_db->errorInfo());
			$this->_error(debug_backtrace(false ));
			return array();
		}
		$res = $st->fetchAll(PDO::FETCH_COLUMN, $aNumColumn-1);
		return $res;
	}

	/**
	 * Retourne le nombre d'enregistrement
	 *
	 */
	public function getCount()
	{
		$res = 0;
		// Verification de la connexion
		if ( empty($this->_db) ) return $res;

		$query = $this->_getQueryCount();
		$st = $this->_db->query($query);
		if ( $st === false )
		{
			$this->_error('getCount:' . $query);
			$this->_error($this->_db->errorInfo());
			$this->_error(debug_backtrace(false ));
			return 0;
		}
		$res = $st->fetch();
		return $res[0];
	}

	/**
	 * Retourne la premiere valeur
	 *
	 * @param integer $p_i_numCol
	 */
	public function getFirst($aNumCol = 1)
	{
		return $this->getOne($aNumCol);
	}

	/**
	 * Retourne la premiere valeur
	 *
	 * @param intege $p_i_numCol
	 */
	public function getOne($aNumCol = 1)
	{
		if ( empty($this->_db) ) return null;

		$numCol = max( 0, intval($aNumCol) - 1);

		$query = $this->getQuery();
		$st = $this->_db->query('SET NAMES UTF8');
		$st = $this->_db->query($query);
		if ( $st === false )
		{
			$this->_error('getOne:'.$query);
			$this->_error($this->_db->errorInfo());
			$this->_error(debug_backtrace(false ));
			return array();
		}
		$res = $st->fetch(PDO::FETCH_NUM);
		return $res[$aNumCol-1];
	}

	/**
	 * Ajoute une jointure gauche
	 *
	 * @param string $aTable
	 * @param string $aWhere
	 */
	public function leftJoin($aTable, $aWhere, $aAlias='')
	{

		if ( strpos($aTable, '.') === false) $table = $this->_baseName . '.' . $this->_prefix . $aTable;
		else $table = $aTable;
		///$table = $this->_prefix . $aTable;

		$find = array_search($table, $this->_tables);
		if ( $find != false )
		{
			unset($this->_tables[$find]);
		}
		$this->_leftTables[] = ' LEFT JOIN ' . $table . ' ' . $aAlias . ' ON '. $aWhere;
	}

	/**
	 * Ajoute une union a la requete
	 *
	 * @param  BN_query $aQuery
	 */
	public function addUnion(BN_query $aQuery)
	{
		$this->_unions[] = $aQuery;
	}

	/**
	 * Ajoute une table a la requete
	 *
	 * @param  string $aTable
	 * @param  string $aWhere
	 * @param  string $aAlias
	 * 	 */
	public function addTable($aTable, $aWhere='', $aAlias='')
	{
		if (empty($aTable)) return;
		if ( strpos($aTable, '.') === false) $table = $this->_baseName . '.' . $this->_prefix . $aTable;
		else $table = $aTable;
		if ( !empty($aAlias) )
		{
			$table .= ' ' . $aAlias;
		}
		$this->_tables[] = $table;
		if ( !empty($aWhere) ) $this->_where[] = $aWhere;
	}

	/**
	 * Renseigne les champs de la requete
	 *
	 * @param  string $p_s_field
	 * @param  string $aAlias
	 */
	public function addField($aField, $aAlias='')
	{
		if ( empty($aAlias) )
		{
			$this->_fields[] = $aField;
		}
		else
		{
			$this->_fields[] = $aField . ' AS ' . $aAlias;
		}
	}

	/**
	 * Ajoute une expression conditionelle
	 *
	 * @param string $aWhere
	 */
	public function addWhere($aWhere)
	{
		$this->_where[] = $aWhere;
	}

	/**
	 * Renseigne les tables de la requete
	 *
	 * @param mixed $aTables
	 */
	public function setTables($aTables)
	{
		unset($this->_tables);
		$this->_tables = array();
		unset($this->_leftTables);
		$this->_leftTables = array();
		unset($this->_where);
		$this->_where = array();
		unset($this->_fields);
		$this->_fields = array();
		unset($this->_values);
		$this->_values = array();
		unset($this->_ivalues);
		$this->_ivalues = array();
		unset($this->_ifields);
		$this->_ifields = array();
		$this->_first = 1;
		$this->_nb = 0;

		$this->_group = '';
		$this->_order = '';
		if (is_string($aTables))
		{
			$tables = preg_split("/[,; ]+/", $aTables);
		}
		else
		{
			$tables = $aTables;
		}
		foreach($tables as $table)
		{
			$this->addTable($table);
		}
	}

	/**
	 * Renseigne les champ de la requete
	 *
	 * @param  mixed $aFields
	 */
	public function setFields($aFields)
	{
		unset($this->_fields);
		$this->_fields = array();
		if (is_string($aFields))
		{
			$fields = preg_split("/[,;]+/", $aFields);
		}
		else
		{
			$fields = $aFields;
		}
		foreach($fields as $field)
		{
			$this->addField(trim($field));
		}
	}

	/**
	 * Fixe la clause de recherche de la requete
	 *
	 * @param string $aWhere
	 */
	public function setWhere($aWhere)
	{
		unset($this->_where);
		$this->addWhere($aWhere);
	}

	/**
	 * Fixe la plage d'enregistrement a recuperer
	 *
	 * @param  integer $aFirst  positif
	 * @param  integer $aNb     positif
	 */
	public function setLimit($aFirst, $aNb)
	{

		$this->_first = max (1, intval($aFirst));
		$this->_nb    = max (0, intval($aNb));
	}

	/**
	 * Fixe la clause de classement
	 *
	 * @param  string $aOrder
	 */
	public function setOrder($aOrder)
	{
		$this->_order = $aOrder;
	}

	/**
	 * Fixe la clause de regroupement
	 *
	 * @param  string $aGroup
	 */
	public function group($aGroup)
	{
		$this->_group = $aGroup;
	}


	/**
	 * Enregistre les modifications
	 *
	 * @param string where
	 * @return id du premier enregistrement modifié
	 */
	public function updateRow($aWhere = null)
	{
		// Verification de la connexion
		if ( empty($this->_db) )return null;
		if ( !count($this->_values) )
		{
			$this->_error('LOC_MSG_ERR_NO_VALUE');
		}

		// Identifiant de la table
		$table = reset($this->_tables);
		$field = reset($this->_values);
		$tip = explode('_', $field);

		// Clause where
		if ( !is_null($aWhere) )
		{
			if ( is_numeric($aWhere) )
			{
				$where = reset($tip) . '_id=' . $aWhere;
			}
			else
			{
				$where = $aWhere;
			}
		}
		else
		{
			$where = $this->_getWhere();
		}

		// Mise a jour
		$st = $this->_db->query('SET NAMES UTF8');
		$query = "UPDATE $table SET " . implode(', ', $this->_values);
		if ( !empty($where) )
		{
			$query .= " WHERE $where";
		}
		$res = $this->_db->exec($query);
		if ( $res === false )
		{
			$this->_error('updateRow:' . $query);
			$this->_error($this->_db->errorInfo());
			$this->_error(debug_backtrace(false ));
		}

		$id = -1;
		if ( !empty($where) )
		{
			$query  = 'SELECT ' . reset($tip) . '_id  FROM '. $table . " WHERE $where";
			$st   = $this->_db->query($query);
			if ( $st === false )
			{
				$this->_error('updateRow:' . $query);
				$this->_error($this->_db->errorInfo());
				$this->_error(debug_backtrace(false ));
				return array();
			}
			$res = $st->fetch();
			$id =  $res[0];
		}
		return $id;
	}

	/**
	 * Construit la requete
	 *
	 */
	public function getQuery()
	{
		$tables = implode(", ", $this->_tables);
		$left   = implode(" ", $this->_leftTables);
		$fields = implode(", ", $this->_fields);
		$where  = implode(" AND ", $this->_where);
		$order  = $this->_order;
		$group  = $this->_group;

		$query = 'SELECT ' . $fields . ' FROM ' . $tables;
		if ( !empty($left) )
		{
			$query .= $left;
		}
		if ( !empty($where) )
		{
			$query .= " WHERE $where";
		}
		if ( !empty($group) )
		{
			$query .= " GROUP BY $group";
		}
		foreach( $this->_unions as $union)
		{
			$query .= ' UNION ' . $union->getQuery();
		}
		if ( !empty($order) )
		{
			$query .= " ORDER BY $order";
		}

		return $query;
	}

	/**
	 * Retourne la definition d'un champ d'une table
	 * @param string	$aField	nom du champ
	 * @param string 	$aTable	nom de la table
	 * @return string
	 */
	public function getDef($aField, $aTable=null)
	{
		if ( empty($this->_db))	return false;
		$table = is_null($aTable) ? reset($this->_tables):$aTable;

		$query = "SHOW COLUMNS FROM $table FROM eq24206";
		$res = $this->_db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
		while($field = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			fwrite($fd, "    <field>\n");
			fwrite($fd, "      <field_name>{$field['Field']}</field_name>\n");
			fwrite($fd, "      <type>{$field['Type']}</type>\n");
			fwrite($fd, "      <null>{$field['Null']}</null>\n");
			fwrite($fd, "      <key>{$field['Key']}</key>\n");
			fwrite($fd, "      <default>{$field['Default']}</default>\n");
			fwrite($fd, "      <extra>{$field['Extra']}</extra>\n");
			fwrite($fd, "    </field>\n");
		}
		fwrite($fd, "  </table>\n");

		return $query;
	}

	/**
	 * Construit la requete pour compter le nombre d'enregistrement
	 */
	private function _getQueryCount()
	{
		$tables = implode(", ", $this->_tables);
		$left   = implode(" ", $this->_leftTables);
		$where  = $this->_getWhere();
		$group  = $this->_group;

		$query = 'SELECT count(*) FROM ' . $tables;
		if ( !empty($left) )
		{
			$query .= $left;
		}
		if ( !empty($where) )
		{
			$query .= " WHERE $where";
		}
		/*
		 if ( !empty($group) )
		 {
			$query .= " GROUP BY $group";
			}
			*/
		return $query;
	}

	/**
	 * Construit la clause  de recherche
	 */
	private function _getWhere()
	{
		return implode(" AND ", $this->_where);
	}
}
?>
