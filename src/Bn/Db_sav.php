<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'MDB2.php';

class Bn_Db
{
	private $_mdb2   = null;
	private static $_dbs = array();

	public function trace()
	{
		echo count(self::$_dbs) .'<br>';
		foreach(self::$_dbs as $key=>$db) echo $key . '<br>'; 
	}
	
	/**
	 * Fermeture de tous les acces aux bases de données
	 */
	public function closeDbs()
	{
		foreach(self::$_dbs as $db)
		{
			if (!PEAR::isError($db->_mdb2)) $db->_mdb2->disconnect(); 
		}
	}
	
	/**
	 * Acces au singleton
	 * @param 	$aIndice	Rubrique de la base de donnee dans le fichier de configuration
	 * @return 	Bn_db
	 */
	public function getDb($indice = 0)
	{
		if ( empty(self::$_dbs[$indice]) )
		{
			self::$_dbs[$indice]  = new Bn_Db($indice);
		}
		return self::$_dbs[$indice]->_mdb2;
	}

	/**
	 * Constructeur
	 *
	 * @access  public
	 */
	private function Bn_Db($indice)
	{
		$dsn = Bn_Db::getDsn($indice);
		if ( empty($dsn) ) 
		{
			$this->_mdb2 = PEAR::raiseError('Pas de dsn dans le fichier de conf indice ' . $indice);
			return;
		}
		$options = array('seqname_format' => '%s_seq',
			  			'seqcol_name'    => 'id',
						'persistent' => true,
						'charset'   =>  Bn_Db::getCharset($indice),
						//'debug' => 1
		);
		$this->_mdb2 =& MDB2::connect($dsn, $options);
		if (!PEAR::isError($this->_mdb2))
		{
			$this->_mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);
		}
	}

	/**
	 * Acces au dsn pour l'acces a la base de donnees
	 *
	 */
	public function getDsn($indice=0)
	{
		static $_dsn = array();

		if ( empty($_dsn[$indice]) )
		{
			$config = parse_ini_file(BN_CONFIG_FILE, true);
			$section = $indice ? 'database'.$indice:'database';
			if( !isset($config[$section]) )
			{
				$msg = 'Fichier de configuration incorrect. Section'. $section . 'absente';
				return null;
			}

			$basedef = $config[$section];
			$_dsn[$indice] ="{$basedef['type']}://{$basedef['user']}:{$basedef['pwd']}@{$basedef['host']}";
		}
		return $_dsn[$indice];
	}

	/**
	 * Acces au prefixe des tables
	 *
	 */
	public function getPrefix($indice=0)
	{
		static $_prefix = array();

		if ( empty($_prefix[$indice]) )
		{
			$config = parse_ini_file(BN_CONFIG_FILE, true);
			$section = $indice ? 'database'.$indice:'database';
			if( !isset($config[$section]) )
			{
				$msg = 'Fichier de configuration incorrect. Section '. $section . ' absente';
				return null;
			}
			$basedef = $config[$section];
			$_prefix[$indice] = $basedef['prefix'];

		}
		return $_prefix[$indice];
	}
	
	/**
	 * Acces au charste  de la base
	 *
	 */
	public function getCharset($indice=0)
	{
		static $_charset = array();

		if ( empty($_charset[$indice]) )
		{
			$config = parse_ini_file(BN_CONFIG_FILE, true);
			$section = $indice ? 'database'.$indice:'database';
			if( !isset($config[$section]) )
			{
				$msg = 'Fichier de configuration incorrect. Section '. $section . ' absente';
				return null;
			}
			$basedef = $config[$section];
			$_charset[$indice] = $basedef['charset'];

		}
		return $_charset[$indice];
	}

	/**
	 * Acces au nom de la base
	 *
	 */
	public function getName($indice=0)
	{
		static $_name = array();

		if ( empty($_name[$indice]) )
		{
			$config = parse_ini_file(BN_CONFIG_FILE, true);
			$section = $indice ? 'database'.$indice:'database';
			if( !isset($config[$section]) )
			{
				$msg = 'Fichier de configuration incorrect. Section '. $section . ' absente';
				return null;
			}
			$basedef = $config[$section];
			$_name[$indice] = $basedef['base'];

		}
		return $_name[$indice];
	}
	
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
	private $_values = array();
	private $_where  = array();
	private $_unions = array();
	private $_first  = 1;
	private $_nb     = 0;
	private $_gridParam = null;
	private $_baseName = '';
	

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
		if ( PEAR::isError($this->_db) )
		{
			$this->_error($this->_db->getMessage());
			return null;
		}
		
		$this->_prefix = Bn_db::getPrefix($this->_indice);
		$this->_charset = Bn_db::getCharset($this->_indice);
		if ( !empty($aDataBase)) $this->_baseName = $aDataBase;
		$this->_baseName = Bn_db::getName($this->_indice);
		//$this->setDatabase($this->_baseName);
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
		if ( PEAR::isError($this->_db))	return false;
		
		$file = '../Script/Database/' . Bn_db::getName($aIndice) . '.sql';
		$sql=file($file, FILE_SKIP_EMPTY_LINES); // on charge le fichier SQL
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
			if ( PEAR::isError($res) )
			{
				echo $res->getUserinfo();
				return false;
			}
		}
		return true;
	}
    
    
	/**
	 * Debut d'une transaction
	 */
	public function beginTrans()
	{
		if ( PEAR::isError($this->_db))	return false;
		$this->_db->beginTransaction();
	}
	
	/**
	 * Fin d'une transaction
	 */
	public function endTrans()
	{
		if ( PEAR::isError($this->_db))	return false;
		$this->_db->commit();
	}
	
	/**
	 * Abandon d'une transaction
	 */
	public function cancelTrans()
	{
		if ( PEAR::isError($this->_db))	return false;
		$this->_db->rollback();
	}
	
	/**
	 * Teste l'exixstence d'une base de données
	 * @param $aIndice indice de la section dans le fichier de conf
	 *
	 */
	public function existsDatabase($aIndice=0)
	{
		if ( PEAR::isError($this->_db))	return false;

		$database =  Bn_db::getName($aIndice);
		
		// Recherche de la base de données
		$q = "SHOW DATABASES LIKE '" . $database . "'";
		$res = $this->_db->queryAll($q);
		return !empty($res);
	}
	
	/**
	 * Suppression d'une base de données
	 * @param $aDatabase base de donnes a supprimer
	 *
	 */
	public function dropDatabase($aDatabase)
	{
		if ( PEAR::isError($this->_db))	return false;
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
		if ( PEAR::isError($this->_db))	return false;
		// Liste des tables de base de données
		$q = 'SHOW TABLES FROM ' . $aSource;
		$tables = $this->_db->queryCol($q);

		// Creer la base de donnee
		$q = 'CREATE DATABASE ' . $aCible;
		$res = $this->_db->query($q);
		// Creer les tables
		foreach($tables as $table)
		{
			$q = 'SHOW CREATE TABLE ' . "$aSource.$table";
			$res = $this->_db->queryAll($q);
			$name = $res[0]['table'];
			$q = $res[0]['create table'];
			$q = str_replace("`$name`", "$aCible.$name", $q);
			$res = $this->_db->query($q);
		}
		return true;
	}
	
	/**
	 * Renvoie les colonnes d'une table
	 */
	public function getColumnDef()
	{
		if ( PEAR::isError($this->_db))	return false;
				
	 	$columns = array();
		$table = reset($this->_tables);
		$q = "SHOW COLUMNS FROM $table";
		$res   = $this->_db->query($q);
		if ( PEAR::isError($res) )
		{
			Bn::warning($res->getUserinfo(), 'getColumnDef');
			return $columns;
		}
	  while($field = $res->fetchRow(MDB2_FETCHMODE_ASSOC))
	    {	  
	      $columns[] = strtolower($field['field']);
	    }
	return $columns;
	}
	
	/**
	 * Changer la base de donnee active
	 * @param string 	$aDatabase		Nom de la base de donnees
	 * @return void
	 */
	public function setDatabase($aDatabase)
	{
		if ( !PEAR::isError($this->_db) ) $this->_db->setDatabase($aDatabase);
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
		if ( PEAR::isError($this->_db))	return false;
				
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
	public function addRow($aAutoId = true)
	{
		$id = null;
		// Verification de la connexion
		if ( PEAR::isError($this->_db))	return false;
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
		// Calcul de l'identifiant de l'enregistrement
		if ( $aAutoId )
		{
			$id = $this->_db->nextId($table);
			$this->addValue($fieldPrefix[0] . '_id', $id);
		}
		$this->addValue($fieldPrefix[0] . '_cre', date('Y-m-d H:i:s'));
		
		// Execution de la requete
		$this->_db->setCharset('latin1');		
		$query = "INSERT INTO $table SET " . implode(', ', $this->_values);
		$res   = $this->_db->query($query);
		if ( PEAR::isError($res) )
		{
			Bn::warning($res->getUserinfo(), 'addRow');
			$this->_error($res->getUserinfo(), $query);
		}
		return $id;
	}

	public function emptyTable($aTable)
	{
		if ( PEAR::isError($this->_db))	return false;
		if ( strpos($aTable, '.') === false) $table = $this->_baseName . '.' . $this->_prefix . $aTable;
		else $table = $this->_prefix . $aTable;
		
		$query = 'truncate table ' .  $table;
		$res   = $this->_db->query($query);
		if ( PEAR::isError($res) )
		{
			Bn::warning($res->getUserinfo(), 'addRow');
			$this->_error($res->getUserinfo(), $query);
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
		$this->_values[$aField] = $aField . "='" . addslashes(utf8_decode($aValue)) ."'";
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
		$this->addValue($aField, $aValue);
	}
	
	/**
	 * Supprime un enregistrement
	 *
	 * @param string $aWhere
	 */
	public function deleteRow($aWhere = null)
	{
		if ( PEAR::isError($this->_db))	return false;
		
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
		if ( PEAR::isError($res) )
		{
			Bn::error($res->getUserInfo(), $res->getMessage());
			$this->_error($res->getUserInfo());
			return null;
		}
		return true;
	}

	/**
	 * Renvoi l'ifentifiant suivant
	 *
	 * @param  string $aTable
	 */
	public function getNextId($aTable)
	{
		if ( PEAR::isError($this->_db))	return false;
		$id = $this->_db->nextId($aTable);
		if ( PEAR::isError($id) )
		{
			Bn::error($id->getUserInfo(), $id->getMessage());
			$this->_error($id->getUserInfo());
			return null;
		}
		return $id;
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
		$sortIndex = Bn::getValue('sidx') + $aDecal;
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
		if ( PEAR::isError($this->_db))	return $res;
		
		// construction de la requete
		$query = $this->getQuery();

		// Fixation des limites
		if ( $this->_nb > 0 )
		{
			$res = $this->_db->setLimit($this->_nb, $this->_first-1);
		}
		if ( PEAR::isError($res) )
		{
			Bn::warning($res->getUserInfo(), $res->getMessage());
			$this->_error($res->getUserInfo());
		}

		// Recuperation des donnees
		$mode = $aAssoc ? MDB2_FETCHMODE_ASSOC :MDB2_FETCHMODE_ORDERED;
		$this->_db->setCharset($this->_charset);
		$res = $this->_db->queryAll($query, null, $mode);
		if ( PEAR::isError($res) )
		{
			Bn::error($res->getUserInfo(), $res->getMessage());
			$this->_error($res->getUserInfo());
			unset($res);
			$res = array();
			return $res;
		}
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
		if ( PEAR::isError($this->_db))	return $res;
		
		// construction de la requete
		$query = $this->getQuery();

		// Fixation des limites
		if ( $this->_nb > 0 )
		{
			$res = $this->_db->setLimit($this->_nb, $this->_first-1);
		}
		if ( PEAR::isError($res) )
		{
			Bn::warning($res->getUserInfo(), $res->getMessage());
			$this->_error($res->getUserInfo());
		}

		// Recuperation des donnees
		$this->_db->setCharset($this->_charset);
		$res = $this->_db->queryAll($query, null, MDB2_FETCHMODE_ORDERED, true);
		if ( PEAR::isError($res) )
		{
			Bn::error($res->getUserInfo(), $res->getMessage());
			$this->_error($res->getUserInfo());
			unset($res);
			$res = array();
			return $res;
		}
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
		if ( PEAR::isError($this->_db) ) return $res;
		
		$mode = $aAssoc ? MDB2_FETCHMODE_ASSOC :MDB2_FETCHMODE_ORDERED;
		$query = $this->getQuery();        	
		$this->_db->setCharset($this->_charset);
		$res = $this->_db->queryRow($query, null, $mode);
		if ( PEAR::isError($res) )
		{
			Bn::error($res->getUserInfo(), $res->getMessage());
			$this->_error($res->getUserInfo());
			unset($res);
			$res = array();
			return $res;
		}
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
		if ( PEAR::isError($this->_db) ) return $res;
		
		// Fixation des limites
		if ( $this->_nb > 0 )
		{
			$res = $this->_db->setLimit($this->_nb, $this->_first-1);
		}

		$query = $this->getQuery();
		$this->_db->setCharset($this->_charset);
		$res = $this->_db->queryCol($query, null, $aNumColumn-1);
		if ( PEAR::isError($res) )
		{
			Bn::error($res->getUserInfo(), $res->getMessage());
			$this->_error($res->getUserInfo());
			unset($res);
			$res = array();
		}
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
		if ( PEAR::isError($this->_db) ) return $res;

		$query = $this->_getQueryCount();
		$res = $this->_db->queryOne($query);
		if ( PEAR::isError($res) )
		{
			Bn::error($res->getUserInfo(), $res->getMessage());
			$this->_error($res->getUserInfo());
			unset($res);
			$res = 0;
			return $res;
		}
		return $res;
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
		if ( PEAR::isError($this->_db) ) return null;

		$numCol = max( 0, intval($aNumCol) - 1);

		$query = $this->getQuery();
		$this->_db->setCharset($this->_charset);
		$res = $this->_db->queryOne($query, null, $numCol);
		if ( PEAR::isError($res) )
		{
			$this->_error($res);
			return null;
		}
		return $res;
	}

	/**
	 * Ajoute une jointure gauche
	 *
	 * @param string $aTable
	 * @param string $aWhere
	 */
	public function leftJoin($aTable, $aWhere)
	{

		if ( strpos($aTable, '.') === false) $table = $this->_baseName . '.' . $this->_prefix . $aTable;
		else $table = $aTable;
		///$table = $this->_prefix . $aTable;
		
		$find = array_search($table, $this->_tables);
		if ( $find != false )
		{
			unset($this->_tables[$find]);
		}
		$this->_leftTables[] = ' LEFT JOIN ' . $table . ' ON '. $aWhere;
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
	 * Fixe la plage d'enregistreùent a recuperer
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
		if ( PEAR::isError($this->_db) )return null;
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
		$this->_db->setCharset('latin1');
		$query = "UPDATE $table SET " . implode(', ', $this->_values);
		if ( !empty($where) )
		{
			$query .= " WHERE $where";
		}
		$res   = $this->_db->query($query);
		if ( PEAR::isError($res) )
		{
			BN::warning($res->getUserInfo(), 'updateRow');
			$this->_error($res->getUserInfo());
		}
		
		$query  = 'SELECT ' . reset($tip) . '_id  FROM '. $table . " WHERE $where";
		$id   = $this->_db->queryOne($query);
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
		if ( !empty($order) )
		{
			$query .= " ORDER BY $order";
		}
		foreach( $this->_unions as $union)
		{
			$query .= ' UNION ' . $union->getQuery();
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
		if ( PEAR::isError($this->_db))	return false;
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
