<?php
/*****************************************************************************
 !   Module     : installation
 !   File       : $Source: /cvsroot/aotb/badnet/src/inst/base_A.php,v $
 !   Version    : $Name: HEAD $
 !   Revision   : $Revision: 1.23 $
 !   Author     : G. CANTEGRIL
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/20 22:20:08 $
 !   Mailto     : cage at free.fr
 ******************************************************************************/
require_once "utils/utbase.php";

/**
 * Acces to the dababase for installation
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class instBase_A extends utbase
{


	/**
	 * Connection to the database
	 *
	 * @access public
	 * @return void
	 */
	function testConnect($server, $login, $pwd, $type)
	{
		if ($type != 'sqlite')
		{
			$dsn = $type . ':host=' . $server;
			$db = new PDO($dsn, $login, $pwd);
			if ($db === false)
			{
				$res["errMsg"] = "Connection impossible:$dsn";
				return $res;
			}
		}
		return true;
	}

	// {{{ getDatabases
	/**
	 * Return the list of existing database
	 *
	 * @access public
	 * @return array  list of database
	 */
	function getDatabases($server, $login, $pwd, $type)
	{
		// Connect to the server

		$bases = array();
		if ($type == 'mysql')
		{
			$dsn = $type . ':host=' . $server;
			$db = new PDO($dsn, $login, $pwd);

			$q = "SHOW DATABASES";
			$res = $db->query($q);
			if (!$res->rowCount() ) return false;
			$dbs = $res->fetchAll();
			foreach ($dbs as $base)
			{
				$bases[$base[0]] = $base[0];
			}
		}
		else if ($type == 'sqlite')
		{
			$handle=opendir('../data');
			while ($file = readdir($handle))
			{
				if ($file != '.' && $file != '..' && $file != 'CVS' && $file != 'cnx.dat' && $file != 'lisezmoi')
				$bases["../data/$file"] = $file;
			}
			closedir($handle);
		}
		return $bases;
	}

	/**
	 * Create a new database
	 *
	 * @access public
	 * @param $database Name of the new database
	 * @return void
	 */
	function newDatabase($server, $login, $pwd, $type, $base)
	{
		if ($type != 'sqlite')
		{
			// Connect to the server
			$dsn = $type . ':host=' . $server;
			$db = new PDO($dsn, $login, $pwd);

			// Create de database
			$query = "CREATE DATABASE $base DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
			$ret = $db->query($query);
			if ($ret===false)
			{
				$fault = $db->errorInfo();
				$err['errMsg'] = $fault[2];
				return $err;
			}
		}
		return true;
	}

	// {{{ initTables
	/**
	 * Create the tables in the database
	 *
	 * @access public
	 * @return void
	 */
	function initTables()
	{
		// Create special player
		$this->_createWoPlayer();

		// Create rank definitions
		$this->_createRankDef();

		// Create bagde template
		$this->_createBadge();

		return;
	}
	// }}}

	/**
	 * Update the database
	 *
	 * @access public
	 * @param $database name of the database to update
	 * @param $version new version of the database
	 * @return void
	 */
	function updateDatabase($baseName, $isFirst=false)
	{
		$ut = new utils();
		$version = $isFirst ? '' : $ut->getParam('version');
		$dbType = utvars::getDbType();
		if (empty($version))
		{
			$version = 'Version V2_7r0';
			if ($dbType == 'sqlite') $this->loadFile('database_lite_dist.sql');
			else $this->loadFile('database_dist.sql');
			$this->loadFile('badnetteam_dist.sql');
			$this->initTables();
			$default = dirname($_SERVER['PHP_SELF']);
			$server  = $ut->getParam('server', "http://{$_SERVER['HTTP_HOST']}{$default}");
			$ut->setParam('localhost', $server);
			$ut->setParam('version', $version);
			$ut->setParam('softVersion', 'v2.7r0');
		}
		if ( $version == 'Version V2_5r7')
		{
			$this->loadFile('database_7_8.sql');
			$version = 'Version V2_5r8';
		}
		if ( $version == 'Version V2_5r8' || $version == 'Version V2_5r9')
		{
			$version = 'Version V2_6r0';
			$this->loadFile('database_8_9.sql');
			$this->updateDiscipline();
			$this->updateUniId();
			$default = dirname($_SERVER['PHP_SELF']);
			$server  = $ut->getParam('server', "http://{$_SERVER['HTTP_HOST']}{$default}");
		}
		if ( $version == 'Version V2_6r0')
		{
			$version = 'Version V2_7r0';
			$this->loadFile('database_60_70.sql');
			$this->updateDiscipline();
			$this->updateAutoIncrement();
			$ut->setParam('version', $version);
			$ut->setParam('softVersion', 'v2.7r0');
		}
		if ( $version == 'Version V2_7r0' || $version == 'Version V2_7r1')
		{
			$version = 'Version V2_7r2';
			$this->loadFile('database_70_71.sql');
			$ut->setParam('version', $version);
			$ut->setParam('softVersion', 'v2.7r0');
		}
		if ( $version == 'Version V2_7r2')
		{
			$version = 'Version V2_7r3';
			$this->loadFile('database_72_73.sql');
			$ut->setParam('version', $version);
			$ut->setParam('softVersion', 'v2.7r1');
		}
		if ( $version == 'Version V2_7r3')
		{
			$version = 'Version V2_7r4';
			$this->loadFile('database_73_74.sql');
			$ut->setParam('version', $version);
			$ut->setParam('softVersion', 'v2.7r1');
		}
		if ( $version == 'Version V2_7r4')
		{
			$version = 'Version V2_7r5';
			$this->delOldid();
			$this->updateconfFile();
			$this->loadFile('database_74_75.sql');
			$ut->setParam('version', $version);
			$ut->setParam('softVersion', 'v2.8r1');
		}
		return;
	}

	public function updateConfFile($aLogin=null, $aPwd=null)
	{
		// Lecture du nouveau fichier de conf
		$fileName = '../Conf/Conf.ini';
		$confFile = parse_ini_file($fileName, true);
		
		// Lecture de l'ancien fichier de conf
	  	$file = "../data/cnx.dat";
	  	$fp = fopen($file, "r");
	  	$buf = fgets($fp, 4096);
	  	fclose($fp);
	  	$fields = preg_split("/\t/", $buf);
	  	
	  	$confFile['database']['host'] = '"' . $fields[2] . '"';
	  	$confFile['database']['user'] = '"' . $fields[1] . '"';
	  	$confFile['database']['pwd']  = '"' . $fields[5] . '"';
	  	$confFile['database']['base'] = '"' . $fields[3] . '"';
	  	$confFile['database']['prefix'] = $fields[6].'_';
	  	
	  	if (!empty($aLogin))
	  	{
	  		$confFile['autolog']['user'] = '"' . $aLogin . '"';
	  		$confFile['autolog']['pwd'] = '"' . $aPwd . '"';
	  	}
	  	// Sauvegarder le fichier initial
	  	$res = @copy($fileName, $fileName . '-' . date('d-m-Y H-i-s'));
	  	if ($res === false)
	  	{
	  		echo "Impossible de modifier le fichier Conf/Conf.ini. Donner les droits en lecture/ecriture au dossier Conf et a ses fichiers.";
	  		exit();  
	  	}
	  	
	  	// Ecriture du fichier de conf
	  	$fp = @fopen($fileName, "w");
	  	if ($fp === false)
	  	{
	  		echo "Impossible de modifier le fichier Conf/Conf.ini. Donner les droits en lecture/ecriture au dossier Conf et a ses fichiers.";
	  		exit();  
	  	}
	  	
	  	fwrite($fp, ";Fichier de configuration modifié le " . date('d-m-Y H-i-s'));
	  	foreach($confFile as $section=>$params)
	  	{
	  		fwrite($fp, "\n[" .$section . "]\n");
	  		foreach($params as $param=>$val)
	  		{
	  			fwrite($fp,  $param . '=' . $val . "\n");
	  		}
	  	}
	  	fclose($fp);
	  	@chmod($fileName, 0777);
	}
	
	public function loadFile($aScript)
	{
		// Connect to the server
		$db = utvars::GetDbConnect();
		$prefix = utvars::getPrfx().'_';

		// on charge le fichier SQL
		$file =  'inst/' . $aScript;
		$fd = fopen($file, 'r');
		if ($fd)
		{
			while (!feof($fd))
			{
				$buffer = trim(fgets($fd, 4096));
				if (empty($buffer)) continue;
				if ($buffer[0] == '#')continue;
				if (substr($buffer, 0, 2) == '--')continue;
				
				$q = str_replace('bdnet_', $prefix, $buffer);
				$ret = $db->query($q);
				if ($ret === false)
				{
					//echo "erreur $q<br>";
					//print_r($db->errorInfo());
				}

			}
			fclose($fd);
		}
		return true;
	}

	/**
	 * Update the database
	 *
	 * @access public
	 * @param $database name of the database to update
	 * @param $version new version of the database
	 * @return void
	 */
	function _createBadge()
	{
		// Is model already existing ?
		$nb = $this->_selectFirst('badges', 'count(*)');
		if ($nb) return;

		// Create the badges models
		$infos['bdge_name']= "Modele 1";
		$infos['bdge_width']= 86;
		$infos['bdge_height']= 54;
		$infos['bdge_border']= 'LTRB';
		$badgeId = $this->_insert('badges', $infos);

		$infos = array();
		$infos['eltb_badgeId'] = $badgeId;
		$infos['eltb_font']= 'helvetica';
		$infos['eltb_size']= 18;
		$infos['eltb_top']= 23;
		$infos['eltb_left']= 2;
		$infos['eltb_width']= 60;
		$infos['eltb_height']= 5;
		$infos['eltb_border']= 0;
		$infos['eltb_field']= WBS_FIELD_NAME;
		$this->_insert('eltbadges', $infos);

		$infos['eltb_font']= 'helvetica';
		$infos['eltb_size']= 12;
		$infos['eltb_top']= 32;
		$infos['eltb_left']= 2;
		$infos['eltb_width']= 60;
		$infos['eltb_height']= 4;
		$infos['eltb_field']= WBS_FIELD_TEAMNAME;
		$this->_insert('eltbadges', $infos);

		$infos['eltb_badgeId'] = $badgeId;
		$infos['eltb_font']= 'helvetica';
		$infos['eltb_size']= 12;
		$infos['eltb_top']= 39;
		$infos['eltb_left']= 2;
		$infos['eltb_width']= 30;
		$infos['eltb_height']= 4;
		$infos['eltb_field']= WBS_FIELD_STATUS;
		$this->_insert('eltbadges', $infos);

		$infos['eltb_font']= '';
		$infos['eltb_size']= 0;
		$infos['eltb_top']= 0;
		$infos['eltb_left']= 0;
		$infos['eltb_width']= 89;
		$infos['eltb_height']= 18;
		$infos['eltb_field']= WBS_FIELD_LOGO;
		$this->_insert('eltbadges', $infos);

		$infos['eltb_font']= '';
		$infos['eltb_size']= 0;
		$infos['eltb_top']= 21;
		$infos['eltb_left']= 65;
		$infos['eltb_width']= 20;
		$infos['eltb_height']= 10;
		$infos['eltb_field']= WBS_FIELD_PHOTO;
		$this->_insert('eltbadges', $infos);

		$infos['eltb_top']= 43;
		$infos['eltb_left']= 32;
		$infos['eltb_width']= 55;
		$infos['eltb_height']= 6;
		$infos['eltb_field']= WBS_FIELD_SPONSOR;
		$this->_insert('eltbadges', $infos);

		return;
	}

	/**
	 * Create the administrator for the database
	 *
	 * @access public
	 * @param $name Name of the administrator
	 * @param $login Logi of tha administrator
	 * @param $pwd  Password of the administrator
	 * @param $email Email of the administrator
	 * @return void
	 */
	function createAdmin($infos, $lang)
	{
		// Code the password
		$pwd = $infos['user_pass'];
		$mdpass = md5($infos['user_pass']);

		// Create the administrator
		$infos['user_pass']= $mdpass;
		$infos['user_type']= WBS_AUTH_ADMIN;
		$infos['user_lang']= $lang;
		$infos['user_cmt']= '';
		$infos['user_lastvisit']= date(DATE_FMT);
		$userId = $this->_insert("users", $infos);
		$this->updateconfFile($infos['user_login'], $pwd);

		// Create the anonymous user
		$mdpass = md5("demo");
		$infos = array();
		$infos['user_name']= 'Anonymous user';
		$infos['user_email']= '';
		$infos['user_login']= 'demo';
		$infos['user_pseudo']= 'demo';
		$infos['user_pass']= $mdpass;
		$infos['user_type']= WBS_AUTH_VISITOR;
		$infos['user_lang']= $lang;
		$infos['user_cmt']= '';
		$infos['user_lastvisit']= date(DATE_FMT);
		$res = $this->_insert("users", $infos);
	}

	/**
	 * Create the special player for WO
	 *
	 * @access private
	 * @return void
	 */
	function _createWoPlayer()
	{
		// Is the WO man player already existing ?
		$fields = array('mber_id', 'mber_secondname');
		$where = "mber_id = -1";
		$res = $this->_select('members', $fields, $where);
		// Create it
		if (!$res->numRows())
		{
	  $infos['mber_id'] = -1;
	  $infos['mber_firstname'] = 'WO';
	  $infos['mber_secondname'] = 'WO';
	  $infos['mber_sexe'] = WBS_MALE;
	  $infos['mber_cmt'] = 'Joueur spécial pour les WO. Ne pas supprimer!'; 
	  $infos['mber_cre'] = date(DATE_FMT);
	  $infos['mber_updt'] = date(DATE_FMT);
	  $res = $this->_insert('members', $infos);
		}

		// Is the WO woman player already existing ?
		$where = "mber_id = -2";
		$res = $this->_select('members', $fields, $where);
		if ($res->numRows()>0) return true;

		// Create it
		$infos['mber_id'] = -2;
		$infos['mber_sexe'] = WBS_FEMALE;
		$res = $this->_insert('members', $infos);
	}

	/**
	 * Create the default ranking definition
	 *
	 * @access private
	 * @return void
	 */
	function _createRankDef()
	{
		$ranks =array( array('Top 50',  'T50'),
		array('Top 20',  'T20'),
		array('Top 10',  'T10'),
		array('Top 5',   'T5'));
		foreach( $ranks as  $rank=>$def)
		{
	  // Is the ranking already existing ?
	  $where = "rkdf_label = '{$def[0]}'";
	  $columns['rkdf_label'] = $def[1];
	  $res = $this->_update('rankdef', $columns, $where);
		}

		$ranks =array( 0 => array('--',0,0,WBS_RANK_FR1, '--'),
		array('NC',      0.5,     0.25,WBS_RANK_FR1, 'NC'),
		array('F2',      1,     0.5,WBS_RANK_FR1, 'F'),
		array('F1',      2,     1, WBS_RANK_FR1, 'F'),
		array('E2',      4,     2, WBS_RANK_FR1, 'E'),
		array('E1',      8,     4, WBS_RANK_FR1, 'E'),
		array('D2',     16,     8, WBS_RANK_FR1, 'D'),
		array('D1',     32,    16, WBS_RANK_FR1, 'D'),
		array('C2',     64,    32, WBS_RANK_FR1, 'C'),
		array('C1',    128,    64, WBS_RANK_FR1, 'C'),
		array('B2',    256,   128, WBS_RANK_FR1, 'B'),
		array('B1',    512,   256, WBS_RANK_FR1, 'B'),
		array('A2',   1024,   512, WBS_RANK_FR1, 'A'),
		array('A1',   2048,  1024, WBS_RANK_FR1, 'A'),
		array('A0',   4096,  2048, WBS_RANK_FR1, 'A'),
		array('A-1',  8192,  4096, WBS_RANK_FR1, 'A'),
		array('A-2', 16384,  8192, WBS_RANK_FR1, 'A'),
		array('A-3', 32768,  16384, WBS_RANK_FR1, 'A'),
		array('A-4', 65536,  32768, WBS_RANK_FR1, 'A'),
		array('--',        0,     0, WBS_RANK_FR2, '--'),
		array('NC',        4,     0, WBS_RANK_FR2, 'NC'),
		array('D4',        6,     2, WBS_RANK_FR2, 'D'),
		array('D3',        8,     3, WBS_RANK_FR2, 'D'),
		array('D2',       10,     4, WBS_RANK_FR2, 'D'),
		array('D1',       12,     5, WBS_RANK_FR2, 'D'),
		array('C4',       18,     6, WBS_RANK_FR2, 'C'),
		array('C3',       24,     9, WBS_RANK_FR2, 'C'),
		array('C2',       30,    12, WBS_RANK_FR2, 'C'),
		array('C1',       36,    15, WBS_RANK_FR2, 'C'),
		array('B4',       54,    18, WBS_RANK_FR2, 'B'),
		array('B3',       72,    27, WBS_RANK_FR2, 'B'),
		array('B2',       90,    36, WBS_RANK_FR2, 'B'),
		array('B1',      108,    45, WBS_RANK_FR2, 'B'),
		array('A4',      162,    54, WBS_RANK_FR2, 'A'),
		array('A3',      216,    81, WBS_RANK_FR2, 'A'),
		array('A2',      270,   108, WBS_RANK_FR2, 'A'),
		array('A1',      324,   135, WBS_RANK_FR2, 'A'),
		array('T50',  486,   162, WBS_RANK_FR2, 'Elite'),
		array('T20',  648,   162, WBS_RANK_FR2, 'Elite'),
		array('T10',  810,   162, WBS_RANK_FR2, 'Elite'),
		array('T5',   972,   162, WBS_RANK_FR2, 'Elite'),
		array('--',         0,     0, WBS_RANK_LU, '--'),
		array('NC',         4,     0, WBS_RANK_LU, 'NC'),
		array('D35',        8,     3, WBS_RANK_LU, 'D'),
		array('D30',       12,     5, WBS_RANK_LU, 'D'),
		array('C25',       24,     9, WBS_RANK_LU, 'C'),
		array('C20',       36,    15, WBS_RANK_LU, 'C'),
		array('B15',       72,    27, WBS_RANK_LU, 'B'),
		array('B10',      108,    45, WBS_RANK_LU, 'B'),
		array('A05',      216,    81, WBS_RANK_LU, 'A'),
		array('A00',      324,   135, WBS_RANK_LU, 'A'),
		);

		foreach( $ranks as  $rank=>$def)
		{
	  // Is the ranking already existing ?
	  $fields = array('rkdf_id');
	  $where = "rkdf_label = '".$def[0]."'".
	    " AND rkdf_system = '".$def[3]."'";
	  $res = $this->_selectFirst('rankdef', $fields, $where);
	  // Create it
	  if (is_null($res))
	  {
	  	$columns['rkdf_label'] = $def[0];
	  	$columns['rkdf_point'] = $def[1];
	  	$columns['rkdf_seuil'] = $def[2];
	  	$columns['rkdf_system'] = $def[3];
	  	$columns['rkdf_serial'] = $def[4];
	  	$res = $this->_insert('rankdef', $columns);
	  }
		}
	}

	/**
	 * Verify an administrator exist for the database
	 *
	 * @access public
	 * @return boolean True if the administrator exist
	 */
	function existAdmin()
	{
		$fields = array('user_name');
		$where = "user_type='A'";
		$res = $this->_select('users', $fields, $where);
		return $res->numRows();
	}

	function delOldid()
	{
		$base = utvars::getBaseName();
		$q = 'SHOW TABLES FROM ' . $base;
		$st = $this->_db->query($q);
		$tables = $st->fetchAll();

		// Creer les tables
		foreach($tables as $tabledef)
		{
			$table = $tabledef[0];
			if (substr($table, -4) == '_seq') continue;
			
			$q = "ALTER TABLE " . $table . " DROP idold";
			$this->_db->query($q);
		}

	}
	

	
	
	function updateAutoIncrement()
	{
		$base = utvars::getBaseName();
		$q = 'SHOW TABLES FROM ' . $base;
		$st = $this->_db->query($q);
		$tables = $st->fetchAll();

		// Creer les tables
		foreach($tables as $tabledef)
		{
			$table = $tabledef[0];
			//echo "--$table------<br>";
			if (substr($table, -4) == '_seq') continue;
			$q = 'ALTER TABLE ' . $table .' DROP PRIMARY KEY';
			$res = $this->_db->query($q);
			//if ($res===false){ echo "<br>$q<br>";print_r($this->_db->errorInfo()); continue;}
				
			$q = "SHOW COLUMNS FROM $table";
			$res = $this->_db->query($q);
			if ($res===false){ echo "<br>$q<br>";print_r($this->_db->errorInfo()); continue;}
			$columns = $res->fetch();

			$name = $columns[0];
			$prfx = substr($name, 0, strpos($name, '_')) . '_';
			$q = "ALTER TABLE $table ADD id2 INT NOT NULL AUTO_INCREMENT PRIMARY KEY";
			$res = $this->_db->query($q);
			if ($res===false) {echo "<br>$q<br>";print_r($this->_db->errorInfo());continue;}

			$q = "SELECT id FROM " . $table . "_seq;";
			$res = $this->_db->query($q);
			if ($res===false)  $index = 1000;
			else
			{
				$entry = $res->fetch(PDO::FETCH_ASSOC);
				$index = $entry['id'];
			}
			$index++;
			//echo "<br>index=$index<br>";
				
			$q = "UPDATE $table SET id2 = id2 + $index;";
			$res = $this->_db->query($q);
				
			$q = "UPDATE $table SET id2 = " . $prfx . "id;";
			$res = $this->_db->query($q);
			if ($res===false) {echo "<br>$q<br>";print_r($this->_db->errorInfo());continue;}

			//$q = "ALTER TABLE $table DROP " . $prfx . "_id";
			//$res = $this->_db->query($q);

			$q = "ALTER TABLE $table CHANGE " . $prfx . 'id ' . $prfx . 'idold INT( 11 ) NOT NULL';
			$res = $this->_db->query($q);
			if ($res===false) {echo "<br>$q<br>";print_r($this->_db->errorInfo());continue;}

			$q = "ALTER TABLE $table CHANGE id2 " . $prfx . "id INT( 11 ) NOT NULL AUTO_INCREMENT ";
			$res = $this->_db->query($q);
			if ($res===false) {echo "<br>$q<br>";print_r($this->_db->errorInfo());continue;}

		}

	}

	function updateDiscipline()
	{
		$where = "mtch_discipline<3 OR mtch_discipline=6";
		$fields = array('mtch_disci' => WBS_SINGLE);
		$this->_update('matchs', $fields, $where);

		$where = "mtch_discipline=5";
		$fields = array('mtch_disci' => WBS_MIXED);
		$this->_update('matchs', $fields, $where);

		$where = "mtch_discipline=3 OR mtch_discipline=4 OR mtch_discipline=7";
		$fields = array('mtch_disci' => WBS_DOUBLE);
		$this->_update('matchs', $fields, $where);

		$where = "rank_disci<3";
		$fields = array('rank_discipline' => WBS_SINGLE);
		$this->_update('ranks', $fields, $where);
		$where = "rank_disci=5";
		$fields = array('rank_discipline' => WBS_MIXED);
		$this->_update('ranks', $fields, $where);
		$where = "rank_disci=3 OR rank_disci=4";
		$fields = array('rank_discipline' => WBS_DOUBLE);
		$this->_update('ranks', $fields, $where);

		$cols = array('pair_disci' => WBS_SINGLE);
		$where = 'pair_disci < 3';
		$this->_update('pairs', $cols, $where);

		$cols = array('pair_disci' => WBS_MIXED);
		$where = 'pair_disci = 5';
		$this->_update('pairs', $cols, $where);

		$cols = array('pair_disci' => WBS_DOUBLE);
		$where = 'pair_disci < 5';
		$this->_update('pairs', $cols, $where);
	}

	/**
	 * Met a jour le champ uniId des enregistrements.
	 *
	 * @access public
	 * @return array   array of match
	 */
	function updateUniId()
	{
		// Identifiant de la base locale
		$ut = new utils();
		$localDBId = $ut->getParam('databaseId', -1);
		if ($localDBId == -1)
		{
	  $localDBId = gmmktime();
	  $ut->setParam('databaseId', $localDBId);
		}

		$where = "mber_uniId=''";
		$fields = array('mber_uniId' => "eval(CONCAT('$localDBId:',mber_id,';'))");
		$this->_update('members', $fields, $where);

		$where = "asso_uniId=''";
		$fields = array('asso_uniId' => "eval(CONCAT('$localDBId:',asso_id,';'))");
		$this->_update('assocs', $fields, $where);

		$where = "draw_uniId=''";
		$fields = array('draw_uniId' => "eval(CONCAT('$localDBId:',draw_id,';'))");
		$this->_update('draws', $fields, $where);

		$where = "mtch_uniId=''";
		$fields = array('mtch_uniId' => "eval(CONCAT('$localDBId:',mtch_id,';'))");
		$this->_update('matchs', $fields, $where);

		$where = "pair_uniId=''";
		$fields = array('pair_uniId' => "eval(CONCAT('$localDBId:',pair_id,';'))");
		$this->_update('pairs', $fields, $where);

		$where = "regi_uniId=''";
		$fields = array('regi_uniId' => "eval(CONCAT('$localDBId:',regi_id,';'))");
		$this->_update('registration', $fields, $where);

		$where = "rund_uniId=''";
		$fields = array('rund_uniId' => "eval(CONCAT('$localDBId:',rund_id,';'))");
		$this->_update('rounds', $fields, $where);

		$where = "team_uniId=''";
		$fields = array('team_uniId' => "eval(CONCAT('$localDBId:',team_id,';'))");
		$this->_update('teams', $fields, $where);

		$where = "cunt_uniId=''";
		$fields = array('cunt_uniId' => "eval(CONCAT('$localDBId:',cunt_id,';'))");
		$this->_update('accounts', $fields, $where);

		$where = "item_uniId=''";
		$fields = array('item_uniId' => "eval(CONCAT('$localDBId:',item_id,';'))");
		$this->_update('items', $fields, $where);
	}

}

?>
