<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Oasso.inc';
require_once 'Omember.inc';
require_once 'Oevent.inc';
require_once 'Oplayer.inc';
require_once 'Squashrank/Classe/Cdate.php';

define("CPARAM_POINT_NC_H", 4800);
define("CPARAM_POINT_NC_F", 1400);


class Oextsquash extends Object
{
	private  $_host = 'www.ffsquash.fr';
	private  $_uri = '/club/prive/index.htm';
	private  $_log = 'admin';
	private  $_pwd = 'tournoi';

	function _select($aQuery)
	{
		$_host = 'www.ffsquash.fr';
		$_uri = '/club/prive/index.htm';
		$_log = 'admin';
		$_pwd = 'tournoi';

		$socket = fsockopen($_host, 80, $errno, $errstr, 1);

		// Ouverture de la connexion
		if (!$socket)
		{
			echo "erreur ouverture socket<br>";
			echo $errno . "<br>";
			echo $errstr . "<br>";
		}
		else
		{
			$result = array();
			$query = 'sql_query='.$aQuery;
			// Construction de la requete
			$reqHeader = 'POST ' . $_uri . " HTTP/1.1\n";
			$reqHeader .= 'Host: ' . $_host . "\n";
			$reqHeader .= "Authorization: Basic " . base64_encode($_log .":" .$_pwd)  . "\n";
			$reqHeader .= "Content-Type: application/x-www-form-urlencoded\n";
			$reqHeader .= "Content-Length: " . strlen($query) . "\n\n";

			// Envoi de la requete
			//echo $aQuery;
			fputs($socket, $reqHeader . $query);

			// Lecture de la reponse
			stream_set_timeout($socket, 1);

			$page = '';
			while ( ($l = fgets($socket)) !== false )$page .= $l;
			$ou = strpos($page,'<pre>');
			$fin = strpos($page,'</pre>');
			$result = array();
			if (  $ou  !== false )
			{
				$str = substr($page, $ou, $fin-$ou-1);
				$result = explode("\n", $str);
				// Suppression des deux premieres lignes :
				// La premiere est le debut du paragraphe
				// La deuxieme le titre des colonnes
				array_shift($result);
				array_shift($result);
			}
			fclose($socket);
			return($result);
		}
	}

	/**
	 * Liste des instances d'une instance
	 */
	public function getInstancesInstance($aInstanceId, $aType = null)
	{
		$dept = $aInstanceId;
		if ($aType == OASSO_TYPE_PRIVATE)
		{
			$where = "SUBSTR(cp, 1, 2) = '" . sprintf('%02d', $dept) . "'";
			$sql = "SELECT num_club, club, ville
			FROM CLUB
			WHERE $where
			ORDER BY club
           ";
		}
		else
		{
			$where = "SUBSTR(num_asso, 1, 2) = '" . sprintf('%02d', $dept) . "'";
			$sql = "SELECT num_asso, asso, ville
			FROM ASSO
			WHERE $where
			ORDER BY asso
           ";
		}

		$rows = $this->_select($sql);

		foreach($rows as $row)
		{
			$fields = explode(';' , $row);
			$instances[] = array('id'=>$fields[0], 'name'=>ucwords(strtolower($fields[1])));
		}
		return $instances;
	}

	/**
	 * Information d'une instance
	 */
	public function getInstance($aInstanceId)
	{
		// Construction de la requete
		$sql = "SELECT asso, ville, ligue
		FROM ASSO
		WHERE num_asso = $aInstanceId
           ";
		$res = $this->_select($sql);
		if (empty($res)) return false;

		$line = reset($res);
		$cols = explode(';', $line);
		$stamp = '';
		$token = strtok($cols[0], ' ');
		while ($token)
		{
			$stamp .= substr($token, 0, 1);
			$token = strtok(' ');
		}

		$asso['name'] = ucwords(strtolower($cols[0]));
		$asso['pseudo'] = ucwords(strtolower($cols[1]));
		$asso['number'] = $aInstanceId;
		$asso['type'] = OASSO_TYPE_CLUB;
		$asso['stamp'] = $stamp;
		//$asso['asso_cmt']  = 'Origine fede '.date(DATE_DATE);
		$asso['noc']  = 'FRA';
		$asso['logo'] = '';
		$asso['id']   = -1;
		$asso['fedeid'] =$aInstanceId;
		$asso['url']    ='';
		$asso['dpt']    = substr($aInstanceId, 0, 2);
		$asso['ligue']  = $cols[2];
		$asso['codepid'] = '';
		$asso['ligueid'] = '';
		return $asso;
	}

	/**
	 * Information d'un club
	 */
	public function getClub($aInstanceId)
	{
		// Construction de la requete
		$sql = "SELECT club, ville, cp, ligue
		FROM CLUB
		WHERE num_club = $aInstanceId
           ";
		$res = $this->_select($sql);
		if (empty($res)) return false;

		$line = reset($res);
		$cols = explode(';', $line);
		$stamp = '';
		$token = strtok($cols[0], ' ');
		while ($token)
		{
			$stamp .= substr($token, 0, 1);
			$token = strtok(' ');
		}

		$asso['name'] = ucwords(strtolower($cols[0]));
		$asso['pseudo'] = ucwords(strtolower($cols[1]));
		$asso['number'] = $aInstanceId;
		$asso['type'] = OASSO_TYPE_PRIVATE;
		$asso['stamp'] = $stamp;
		//$asso['asso_cmt']  = 'Origine fede '.date(DATE_DATE);
		$asso['noc']  = 'FRA';
		$asso['logo'] = '';
		$asso['id']   = -1;
		$asso['fedeid'] =$aInstanceId;
		$asso['url']    ='';
		$asso['dpt']    = substr($cols[2], 0, 2);
		$asso['ligue']  = $cols[3];
		$asso['codepid'] = '';
		$asso['ligueid'] = '';
		return $asso;
	}

	/**
	 * Liste des ligues
	 */
	public function getLigues()
	{

		$where = "asso LIKE 'LIGUE%'";
		$sql = "SELECT num_asso, asso,
		ville
		FROM ASSO
		WHERE $where
		ORDER BY asso
           ";
		$rows = $this->_select($sql);
		foreach($rows as $row)
		{
			$fields = explode(';' , $row);
			$instances[$fields[0]] = ucwords(strtolower($fields[1]));
		}
		return $instances;
	}

	/**
	 * Information d'une personne
	 */
	public function getMember($aLicense=null, $aFedeId=-1)
	{
		if ( (empty($aLicense) || $aLicense < 1) && $aFedeId < 1) return;

		if ($aFedeId > 0) $where = 'id=' . $aFedeId;
		else $where = "num_licence = '" . $aLicense . "'";

		$sql = "SELECT id, nom, prenom, num_licence, sexe, jour_n, mois_n, an_n
		FROM LICENCE
		WHERE $where
           ";
		$row = $this->_select($sql);
		if (!empty($row))
		{
			$fields = explode(';' , reset($row));
			$member['fedeid'] = $fields[0];
			$member['familyname']  = strtoupper($fields[1]);
			$member['firstname']  = ucwords(strtolower($fields[2]));
			$member['license']  = $fields[3];
			if ($fields[7] > 20)
			$member['born']  = (1900 + $fields[7]) . '-' . $fields[6]. '-' . $fields[5];
			else
			$member['born']  = (2000 + $fields[7]) . '-' . $fields[6]. '-' . $fields[5];
			$member['gender'] = $fields[4] == 'M' ? 6:7;
			$member['labgender'] = $fields[4];
		}
		return $member;
	}

	/**
	 * Recherche d'un club
	 *
	 * @access public
	 * @param  array   $where Crteres de recherche
	 * @return void
	 */
	function searchInstance($aStr)
	{
		if ( empty($aStr) ) return null;
		$str = addslashes($aStr);

		$where = "asso LIKE '%$str%'";
		$sql = "SELECT num_asso, asso, ville
		FROM ASSO
		WHERE $where
		ORDER BY asso
           ";
		$rows = $this->_select($sql);
		foreach($rows as $row)
		{
			$fields = explode(';' , $row);

			$stamp = '';
			$token = strtok($fields[1], ' ');
			while ($token)
			{
				$stamp .= substr($token, 0, 1);
				$token = strtok(' ');
			}

			$instance['id'] = $fields[0];
			$instance['name'] = ucwords(strtolower($fields[1]));
			$instance['stamp'] = $stamp;
			$instance['town'] = ucwords(strtolower($fields[2]));
			$instances[] = $instance;
		}

		return $instances;
	}

	/**
	 * Recherche des joueurs
	 */
	function searchPlayers($aCriteria)
	{
		$where = 'ASSO.num_asso=LICENCE.num_asso';
		$season = Oseason::getSeasonLabel(date('Y-m-d'), '/');
		$where .= " AND LICENCE.saison='" . $season . "'";
		$glue = "\n AND ";
		$dateId = Cdate::getRankDate();
		// Filtre sur la licence
		if ( !empty($aCriteria['license']) )
		{
			$where = $glue . "num_licence LIKE '%" . $aCriteria['license'] . "%'";
		}

		// Filtre sur le nom
		if ( !empty($aCriteria['familyname']) )
		{
			$where .= $glue . "nom LIKE '%" . addslashes($aCriteria['familyname']) . "%'";
		}

		// Filtre sur le prenom
		if ( !empty($aCriteria['firstname']) )
		{
			$where .= $glue . "prenom LIKE '%" . addslashes($aCriteria['firstname']) . "%'";
		}

		// Filtre sur le genre
		if ( !empty($aCriteria['gender']) )
		{
			if ($aCriteria['gender'] == 6)	$where .= $glue . "sexe = 'M'";
			else $where .= $glue . "sexe = 'F'";
		}

		// Filtre sur le club
		if ( !empty($aCriteria['instanceId']) )
		{
			$where .= $glue . "LICENCE.num_asso = '" . $aCriteria['instanceId'] . "'";
		}

		// Filtre sur licence ou nom
		if ( !empty($aCriteria['licname']) )
		{
			$licname = addslashes($aCriteria['licname']);

			$where .= $glue . "( num_licence LIKE '" . $licname . "%'";
			$where .= " OR nom LIKE '" . $licname . "%')";
		}
		// Filtre sur licence ou nom
		if ( !empty($aCriteria['licensetype']) )
		{
			$types = explode(';', $aCriteria['licensetype']);
			$g = '';
			$str = '';
			foreach($types as $type)
			{
				$str .= $g . "type_lic='$type'";
				$g = ' OR ';
			}
			$where .= $glue . "( $str )";
		}

		// Filtre sur les categories d'age
		if ( !empty($aCriteria['catage']) )
		{
			$catages = explode(';', $aCriteria['catage']);
			$borne[OPLAYER_CATAGE_U11] = array(0, 11);
			$borne[OPLAYER_CATAGE_U13] = array(11, 13);
			$borne[OPLAYER_CATAGE_U15] = array(13, 15);
			$borne[OPLAYER_CATAGE_U17] = array(15, 17);
			$borne[OPLAYER_CATAGE_U19] = array(17, 19);
			$borne[OPLAYER_CATAGE_SEN] = array(19, 35);
			$borne[OPLAYER_CATAGE_VET] = array(35, 100);
			$min = $max = 0;
			$plages = array();
			for ($i=OPLAYER_CATAGE_U11; $i<=OPLAYER_CATAGE_VET;$i++)
			{
				if (!in_array($i, $catages) )
				{
					if($min != $max)
					{
						$plages[] = array($min,$max);
						$min = $max = 0;
					}
				}
				else
				{
					if($min == $max) $min = $borne[$i][0];
					$max = $borne[$i][1];
				}
			}
			if($min != $max && $min) $plages[] = array($min, $max);

			$w = '';
			list($y,$m,$d) = explode('-', date('Y-m-d'));
			$gl = '';
			foreach($plages as $plage)
			{
				$dateMax = ($y-$plage[0]) . '-' . $m . '-' . $d;
				$dateMin = ($y-$plage[1]) . '-' . $m . '-' . $d;
				$str = "(concat(if(an_n>20,1900,2000)  %2b  an_n, '-', mois_n, '-', jour_n) > '$dateMin' ";
				$str .= " AND concat(if(an_n>20,1900,2000) %2b  an_n, '-', mois_n, '-', jour_n) < '$dateMax')";
				$w .= $gl . $str;
				$gl = ' OR ';
			}
			if (!empty($w)) $where .= $glue . "$w";

		}
		// Requete
		$sql = "SELECT id as fedeid,
		nom as familyname,
		prenom as firstname,
		num_licence as license,
		sexe as gender,
		asso, jour_n, mois_n, an_n
		FROM LICENCE, ASSO
		WHERE $where
		ORDER BY nom, prenom
           ";

		$rows = $this->_select($sql);
		$q = new Bn_query('players', '_ffs');
		$q->addTable('classe', 'clas_playerid=play_id');
		$q->addField('clas_label', 'level');
		$q->addField('clas_rank', 'rank');
		$q->addField('clas_points', 'points');
		foreach($rows as $row)
		{
			// Champ lu du site
			$fields = explode(';' , $row);

			// Classement
			$q->setWhere('clas_playerid=play_id');
			$q->addWhere('clas_dateid=' . $dateId);
			$q->addWhere("play_license='" . $fields[3] ."'");
			$player = $q->getRow();
			if ( empty($player) )
			{
				$player['level'] = 'NC';
				$player['rank'] = $fields[4]=='M'?CPARAM_POINT_NC_H:CPARAM_POINT_NC_F;
				$player['points'] = $player['rank'];
			}
			$player['levels'] = $player['level'];
			$player['leveld'] = $player['level'];
			$player['levelm'] = $player['level'];
			$player['ranks'] = $player['rank'];
			$player['rankd'] = $player['rank'];
			$player['rankm'] = $player['rank'];

			// Civilites
			$player['fedeid'] = $fields[0];
			$player['familyname'] = strtoupper($fields[1]);
			$player['firstname'] = utf8_encode(ucwords(strtolower($fields[2])));
			$player['license'] = $fields[3];
			$player['gender'] = $fields[4]=='M'?6:7;
			$player['assoc'] = $fields[5];
			if ($fields[8] > 20) $year = 1900+$fields[8];
			else $year = 2000+$fields[8];
			$player['born'] = $fields[6] . '-' . $fields[7] . '-' . $year;
			$player['ismute'] = '';
			$player['isassimile'] = '';
			$player['dateauto'] = '';
			$player['datesurclasse'] = '';
			$player['ups'] = '';
			$player['upd'] = '';
			$player['upm'] = '';
			$player['downs'] = '';
			$player['downd'] = '';
			$player['downm'] = '';
			$player['catage'] = $this->_getCatage($year .'-' . $fields[7] . '-' . $fields[6]);
			$player['labcatage'] = $player['catage'];
			$player['surclasse'] = OPLAYER_SURCLASSE_NONE;
			$players[] = $player;
		}
		return $players;
	}

	/**
	 * Information d'un joueur pour la saison
	 */
	public function getPlayer($aLicense, $aDateId=null)
	{

		if (empty($aDateId)) $dateId = Cdate::getRankDate();
		else $dateId = $aDateId;
		$q = new Bn_query('players', '_ffs');
		$q->addTable('classe', 'clas_playerid=play_id');
		$q->addField('clas_label', 'level');
		$q->addField('clas_rank', 'rank');
		$q->addField('clas_points', 'points');
		$q->addField('clas_numasso', 'instanceid');
		$q->addField('clas_ligue', 'ligueid');
		$q->addField('clas_catage', 'labcatage');
		$q->addWhere('clas_dateid=' . $dateId);
		$q->addWhere("play_license='" . $aLicense . "'");
		$player = $q->getRow();
		if ( empty($player) )
		{
			$member = $this->getMember($aLicense);
			$player['level'] = 'NC';
			$player['rank'] = $member['gender']==6?CPARAM_POINT_NC_H:CPARAM_POINT_NC_F;
			$player['points'] = $member['gender']==6?CPARAM_POINT_NC_H:CPARAM_POINT_NC_F;
			$player['instanceid'] = 0;
			$player['ligueid']=0;
			$player['labcatage']=$this->_getCatage($member['born']);
		}
		$player['levels'] = $player['level'];
		$player['leveld'] = $player['level'];
		$player['levelm'] = $player['level'];
		$player['ranks'] = $player['rank'];
		$player['rankd'] = $player['rank'];
		$player['rankm'] = $player['rank'];
		$player['points'] = $player['point'];
		$player['pointd'] = $player['point'];
		$player['pointm'] = $player['point'];

		$player['ismute'] = '';
		$player['isassimile'] = '';
		$player['dateauto'] = '';
		$player['datesurclasse'] = '';
		$player['ups'] = '';
		$player['upd'] = '';
		$player['upm'] = '';
		$player['downs'] = 0;
		$player['downd'] = 0;
		$player['downm'] = 0;
		$player['catage'] = $this->_getCatageId($player['labcatage']);
		$player['surclasse'] = OPLAYER_SURCLASSE_NONE;
		$player['labsurclasse'] = constant('OPLAYER_SURCLASSE_NONE');
		$player['assoc'] = $player['instanceid'];
		$player['codepid'] = 0;
		return $player;
	}

	private function _getCatageId($aLabCatage)
	{
		require_once "Object/Oplayer.inc";
		return constant('OPLAYER_CATAGE_' . $aLabCatage);
	}

	public function _getCatage($aDate=null)
	{
		if (empty($aDate)) $date = date('Y-m-d');
		else $date = $aDate;

		$catage = 'SEN';
		$born = $this->getVal('born');
		list($year, $month, $day) =	sscanf($born, "%04u-%02u-%02u");
		list($lastYear, $lastMonth, $lastDay) =	sscanf($date, "%04u-%02u-%02u");

		$annees = $lastYear - $year;
		if ($lastMonth <= $month) {
			if ($month == $lastMonth)
			{
				if ($day > $lastDay) $annees--;
			}
			else $annees--;
		}

		if ($annees < 11) $catage = 'U11';
		else if ($annees < 13) $catage = 'U13';
		else if ($annees < 15) $catage = 'U15';
		else if ($annees < 17) $catage = 'U17';
		else if ($annees < 19) $catage = 'U19';
		else if ($annees < 35) $catage = 'SEN';
		else if ($annees < 90) $catage = 'VET';
		return $catage;
	}














	// Tableaux de correspondance entre les valeurs BadNet
	// et les valeurs Poona :
	//--- Categorie d'age --- Poona =>Badnet
	var $_catage = array(
	1=> OPLAYER_CATAGE_POU,
	2=> OPLAYER_CATAGE_BEN,
	3=> OPLAYER_CATAGE_MIN,
	4=> OPLAYER_CATAGE_CAD,
	5=> OPLAYER_CATAGE_JUN,
	6=> OPLAYER_CATAGE_SEN,
	7=> OPLAYER_CATAGE_VET,// VET2
	12=> OPLAYER_CATAGE_BEN,// BEN2
	13=> OPLAYER_CATAGE_MIN,// MIN2
	14=> OPLAYER_CATAGE_CAD,// CAD2
	15=> OPLAYER_CATAGE_JUN,// JUN2
	17=> OPLAYER_CATAGE_VET,// VET3
	18=> OPLAYER_CATAGE_VET,// VET4
	19=> OPLAYER_CATAGE_VET, // VET5
	20=> OPLAYER_CATAGE_VET // VET1
	);

	var $_numCatage = array(
	1=> 0, //OPLAYER_CATAGE_POU,
	2=> 1, //OPLAYER_CATAGE_BEN,
	3=> 1, //OPLAYER_CATAGE_MIN,
	4=> 1, //OPLAYER_CATAGE_CAD,
	5=> 1, //OPLAYER_CATAGE_JUN,
	6=> 1, //OPLAYER_CATAGE_SEN,
	7=> 2, //OPLAYER_CATAGE_VET,// VET2
	12=> 2, //OPLAYER_CATAGE_BEN,// BEN2
	13=> 2, //OPLAYER_CATAGE_MIN,// MIN2
	14=> 2, //OPLAYER_CATAGE_CAD,// CAD2
	15=> 2, //OPLAYER_CATAGE_JUN,// JUN2
	17=> 3, //OPLAYER_CATAGE_VET,// VET3
	18=> 4, //OPLAYER_CATAGE_VET,// VET4
	19=> 5, //OPLAYER_CATAGE_VET, // VET5
	20=> 1  //OPLAYER_CATAGE_VET // VET1
	);

	//--- Discipline --- Poona =>Badnet
	var $_discipline = array(
	1=> OPLAYER_DISCIPLINE_MS,
	2=> OPLAYER_DISCIPLINE_WS,
	3=> OPLAYER_DISCIPLINE_MD,
	4=> OPLAYER_DISCIPLINE_WD,
	5=> OPLAYER_DISCIPLINE_MX
	);

	//--- Discipline --- Badnet=>Poona
	var $_disciPoona = array(
	OPLAYER_DISCIPLINE_MS => 1,
	OPLAYER_DISCIPLINE_WS => 2,
	OPLAYER_DISCIPLINE_MD => 3,
	OPLAYER_DISCIPLINE_WD => 4,
	OPLAYER_DISCIPLINE_MX => 5
	);

	//--- Surclassement --- Poona =>Badnet
	var $_surclasse = array(
 ''=> OPLAYER_SURCLASSE_NONE,
	0 => OPLAYER_SURCLASSE_NONE,
	1 => OPLAYER_SURCLASSE_SIMPLE,
	2 => OPLAYER_SURCLASSE_DOUBLE,
	3 => OPLAYER_SURCLASSE_SE,
	4 => OPLAYER_SURCLASSE_SP,
	7 => OPLAYER_SURCLASSE_VA
	);

	//--- Classement --- Poona =>Badnet
	var $_classe = array(
'' => '--',
	1  => 'NC',
	2  => 'D4',
	18 => 'D3',
	17 => 'D2',
	16 => 'D1',
	15 => 'C4',
	14 => 'C3',
	13 => 'C2',
	12 => 'C1',
	11 => 'B4',
	10 => 'B3',
	9  => 'B2',
	8  => 'B1',
	7  => 'A4',
	6  => 'A3',
	5  => 'A2',
	4  => 'A1',
	47 => 'T50',
	46 => 'T20',
	45 => 'T10',
	3  => 'T5',
	);

	//--- Genre --- Poona =>Badnet
	var $_gender = array(
'1' => OMEMBER_GENDER_MALE,
'2' => OMEMBER_GENDER_FEMALE,
	);

	//--- Genre --- BadNet => Poona
	static $_genderBP = array(
	OMEMBER_GENDER_MALE =>1,
	OMEMBER_GENDER_FEMALE=>2,
	);

	//--- Type d'association --- Badnet==>Poona
	var $_typeInstance = array(
	OASSO_TYPE_FEDE  =>1,
	OASSO_TYPE_LIGUE =>2,
	OASSO_TYPE_CODEP =>3,
	OASSO_TYPE_CLUB  =>4
	);

	var $_instanceType = array(
	1=>OASSO_TYPE_FEDE,
	2=>OASSO_TYPE_LIGUE,
	3=>OASSO_TYPE_CODEP,
	4=>OASSO_TYPE_CLUB
	);

	var $_eventNature = array(
	1=>OEVENT_NATURE_IC,
	2=>OEVENT_NATURE_INTERCODEP,
	3=>OEVENT_NATURE_TEAM,
	4=>OEVENT_NATURE_CHAMPIONSHIP,
	5=>OEVENT_NATURE_OTHER, //OEVENT_NATURE_ELITE,
	6=>OEVENT_NATURE_TROPHEE,
	7=>OEVENT_NATURE_OTHER,
	8=>OEVENT_NATURE_OTHER
	);

	var $_eventLevel = array(
	1=>OEVENT_LEVEL_OTHER,
	2=>OEVENT_LEVEL_INTER_REG,
	3=>OEVENT_LEVEL_DEP,
	4=>OEVENT_LEVEL_REG,
	5=>OEVENT_LEVEL_NAT
	);

	/**
	 * Constructeur
	 * @return OEvent
	 */
	public function __construct()
	{
	}

	public function searchEvents($aCriteria)
	{
		$rows = array();
		$dbName = Opoona::getDbName($aCriteria['season']);
		$q = new Bn_query("`$dbName`.EVENEMENT", '_poona', $dbName);

		$q->leftJoin('poona_permanent.INSTANCE as LIGUE', 'EVENEMENT.EVN_INS_ID_LIGUE = LIGUE.INS_ID');
		$q->leftJoin('poona_permanent.INSTANCE as DEPT', 'EVENEMENT.EVN_INS_ID_ORGA = DEPT.INS_ID');
		$q->leftJoin("`$dbName`.DEMANDE_EVENEMENT", 'EVENEMENT.EVN_ID = DEMANDE_EVENEMENT.DEV_EVN_ID');
		$q->leftJoin("`$dbName`.DEMANDE", 'DEMANDE_EVENEMENT.DEV_DEM_ID = DEMANDE.DEM_ID');
		$q->leftJoin("`$dbName`.DEMANDE_ETAT", 'DEMANDE.DEM_DET_ID = DEMANDE_ETAT.DET_ID');
		$q->leftJoin("poona_permanent.TYPE_DEMANDE_ETAT", 'poona_permanent.TYPE_DEMANDE_ETAT.TDE_ID = DEMANDE_ETAT.DET_TDE_ID');

		$q->addField('EVN_ID', 'fedeid');
		$q->addField('EVN_NOM', 'name');
		$q->addField('EVN_NOM_ORGA', 'organizer');
		$q->addField('EVN_LIEU', 'place');
		$q->addField('EVN_DEBUT', 'firstday');
		$q->addField('EVN_FIN', 'lastday');
		$q->addField('EVN_MAIL', 'email');
		$q->addField('EVN_TABLEAU_CATEGORIE', 'catage');
		$q->addField('EVN_TABLEAU_DISCIPLINE', 'discipline');
		$q->addField('EVN_TABLEAU_SERIE', 'serial');
		$q->addField('EVN_DATE_LIMITE', 'deadline');
		$q->addField('EVN_NUMERO_AUTORISATION', 'numauto');
		$q->addField('EVN_DATE_TABLEAU', 'datedraw');

		// Filtre sur le type de tournoi
		if ($aCriteria['type'] == OEVENT_TYPE_INDIVIDUAL) $q->addWhere("EVN_TABLEAU_DISCIPLINE NOT LIKE '%E%'");
		else $q->addWhere("EVN_TABLEAU_DISCIPLINE like '%E%'");

		// Filtre sur l'etat des tournois
		// 58 = Tournoi en cours de creation
		// 50 = tournoi annule
		// 46 = demande refusee
		$q->addWhere('TDE_ID NOT IN (58, 46, 50)');

		// Filtre sur le nom du tournoi
		if ( strlen($aCriteria['name'] ) )
		{
			$where = "(EVN_NOM like '%" . $aCriteria['name'] . "%'";
			$where .= " OR EVN_LIEU like '%" . $aCriteria['name'] . "%'";
			$where .= " OR EVN_NOM_ORGA like '%" . $aCriteria['name'] . "%')";
			$q->addWhere($where);
		}
			
		// Filtre sur le mois
		if ( $aCriteria['month'] > 0 )
		$q->addWhere("(MONTH(evn_debut)=" . $aCriteria['month'] . " OR MONTH(evn_fin)=" . $aCriteria['month'] .")");

		// Filtre sur la date limite d'inscription
		if ( strlen($aCriteria['limite']) )
		$q->addWhere("evn_date_limite > '" . $aCriteria['limite'] ."'");

		// Filtre sur la localisation
		if ($aCriteria['dept'] > 0)
		{
			$q->addWhere('DEPT.ins_ins_id_codep=' . $aCriteria['dept']);
		}
		if ($aCriteria['region'] > 0)
		{
			$q->addWhere('evn_ins_id_ligue=' . $aCriteria['region']);
		}

		// Filtre sur les categories d'age
		if ( $aCriteria['young'] == 'true')
		{
			$catage[] = "evn_tableau_categorie like '%1,2,3,4%'";
		}
		if ( $aCriteria['senior'] == 'true')
		{
			$catage[] = "evn_tableau_categorie like '%6%'";
		}
		if ( $aCriteria['veteran'] == 'true')
		{
			$catage[] = "evn_tableau_categorie like '%20,7,17,18%'";
		}
		if ( !empty($catage) && count($catage) < 3)
		{
			$q->addWhere('(' . implode(' OR ', $catage) . ')');
		}

		// Filtre sur les series
		$serial=trim($aCriteria['serial']);
		$serials = preg_split("/[,; ]+/", $aCriteria['serial']);
		if ( strlen($serial) && count($serials) && count($serials)< 6 )
		{
			$tmp = array();
			foreach($serials as $serial) $tmp[] = "EVN_TABLEAU_SERIE LIKE '%" . $serial . "%'";
			$q->addWhere( '(' . implode(' OR ', $tmp) . ')');
		}

		// Filtre sur les disciplines
		$disci=trim($aCriteria['disci']);
		$discis = preg_split("/[,; ]+/", $disci);
		if ( strlen($disci) && count($discis) && count($discis)< 5 )
		{
			$tmp = array();
			foreach($discis as $disci)
			{
				$tmp[] = "EVN_TABLEAU_DISCIPLINE LIKE '%" . Opoona::getDisciPoona($disci) . "%'";
			}
			$q->addWhere( '(' . implode(' OR ', $tmp) . ')');
		}

		// Calcul des enregistrements a recuperer
		$nbmax = $q->getCount();
		$nb = $aCriteria['number'] > 0 ? $aCriteria['number'] : 20;
		$nbPages = ceil($nbRecords/$nb);
		$page = $aCriteria['page'] > 0 ? $aCriteria['page'] : 1;
		$first = ($page-1) * $nb + 1;
		if ( $first > $nbmax )
		{
			$first = 1;
			$page = 1;
		}
		$q->setLimit($first, $nb);

		// Ordre de tri
		$q->setOrder('EVN_DEBUT, EVN_NOM');

		// Obtention des enregistrements
		$events = $q->getRows();
		unset ($q);
		return $events;
	}


	/**
	 * Obtention des resultats d'un joueur
	 */
	function getResults($aLicense, $aDiscipline = null)
	{
		$results = array();

		require_once 'Object/Omatch.inc';
		// selectionner tous les matchs du joueur
		$dbName = Bn::getConfigValue('base', 'database_poona');
		$q = new Bn_query("`$dbName`.EVENEMENT", '_poona');
		$q->addTable("`$dbName`.EVENEMENT_PARTICIPANT", 'EPA_EMA_ID = EMA_ID', 'ep');
		$q->addTable("`$dbName`.EVENEMENT_MATCH", 'EMA_EVN_ID = EVN_ID', 'em');
		$q->addField('EMA_ID');
		$q->addWhere('EPA_LICENCE =' . $aLicense);
		$matches = $q->getCol();

		if (empty($matches)) return $results;

		// Selectionner tout les joueurs de chaque match
		$q->addTable("`$dbName`.TYPE_CLASSEMENT");

		$q->addField('EVN_NOM', 'eventname');
		$q->addField('EVN_DEBUT', 'start');
		$q->addField('EVN_FIN', 'end');
		$q->addField('EPA_PER_ID', 'poonaid');
		$q->addField('EPA_NOM', 'familyname');
		$q->addField('EPA_PRENOM', 'firstname');
		$q->addField('EPA_LICENCE', 'license');
		$q->addField('TCL_NOM', 'level');
		$q->addField('EPA_SIGLE', 'club');
		$q->addField('EMA_SCORE', 'score');
		$q->addField('EPA_IS_VICTOIRE', 'result');
		$q->addField('EPA_IS_WO', 'wo');
		$q->addField('EPA_IS_ABANDON', 'ab');
		$q->addField('EPA_POINT', 'point');
		$q->addField('EMA_DIVISION', 'division');
		$q->addField('EMA_POULE', 'poule');
		$q->addField('EMA_TST_ID', 'stage');

		$q->setWhere('EPA_EMA_ID = EMA_ID');
		$q->addWhere('EMA_EVN_ID = EVN_ID');
		$q->addWhere('TCL_ID = EPA_TCL_ID');
		$q->addWhere('EMA_ID IN (' . implode(',', $matches) .')');
		if ($aDiscipline == OMATCH_DISCIPLINE_SINGLE) $q->addWhere('EMA_TDI_ID < 3');
		if ($aDiscipline == OMATCH_DISCIPLINE_MIXED) $q->addWhere('EMA_TDI_ID = 5');
		if ($aDiscipline == OMATCH_DISCIPLINE_DOUBLE)
		{
			$q->addWhere('EMA_TDI_ID > 2');
			$q->addWhere('EMA_TDI_ID <> 5');
		}

		$sort = 'EVN_DEBUT DESC, EMA_EVN_ID, EMA_TST_ID DESC, EMA_ID, EPA_IS_VICTOIRE';
		$q->setOrder($sort);

		// Recuperation des enregistrements
		$results = $q->getRows();
		if ( $q->isError() )
		{
			print_r($q);
			return false;
		}
		return $results;
	}

	/**
	 * Informations d'un tournoi
	 */
	function getEvent($aEventId, $aSeason=null)
	{
		$dbName = $this->getDbName($aSeason);
		$q = new Bn_query("`$dbName`.EVENEMENT", '_poona', $dbName);
		$q->addField('EVN_ID', 'fedeid');
		$q->addField('EVN_NOM', 'name');
		$q->addField('EVN_DEBUT', 'firstday');
		$q->addField('EVN_FIN', 'lastday');
		$q->addField('EVN_NOM_ORGA', 'organizer');
		$q->addField('EVN_NUMERO_AUTORISATION', 'numauto');
		$q->addField('EVN_LIEU', 'place');
		$q->addField('EVN_DATE_LIMITE', 'deadline');
		$q->addField('EVN_DATE_TABLEAU', 'datedraw');
		$q->addField('EVN_MAIL', 'email');
		$q->addField('EVN_TABLEAU_CATEGORIE', 'catage');
		$q->addField('EVN_TABLEAU_DISCIPLINE', 'disci');
		$q->addField('EVN_TABLEAU_SERIE', 'serial');
		$q->addWhere('EVN_ID=' . $aEventId);
		$event = $q->getRow();
		if ( $q->isError() )
		{
			return false;
		}
		return $event;
	}


	/**
	 * Calcul du classement
	 *
	 * @param integer $aGender
	 * @param double $aPoints
	 * @param double $aPointd
	 * @param double $aPointm
	 */
	public function getRanks($aGender, $aPoints, $aPointd, $aPointm)
	{
		$dbName = Bn::getConfigValue('base', 'database_poona');
		$q = new Bn_query("$dbName.PERSONNE", '_poona');

		$dbName = Opoona::getDbName();
		$q->leftJoin("`$dbName`.JOUEUR",  'JOU_PER_ID=PER_ID');
		$q->setFields('count(*)');
		$q->setWhere('PER_PES_ID =' . self::$_genderBP[$aGender]);
		$q->addWhere('JOU_MOYENNE_MONTEE_SIMPLE >' . $aPoints);
		$ranks['ranks'] = $q->getFirst();
			
		$q->setWhere('PER_PES_ID =' . self::$_genderBP[$aGender]);
		$q->addWhere('JOU_MOYENNE_MONTEE_DOUBLE >' . $aPointd);
		$ranks['rankd'] = $q->getFirst();
			
		$q->setWhere('PER_PES_ID =' . self::$_genderBP[$aGender]);
		$q->addWhere('JOU_MOYENNE_MONTEE_MIXTE >' . $aPointm);
		$ranks['rankm'] = $q->getFirst();
		return $ranks;
	}

	/**
	 * Obtention des joueurs d'une instance
	 */
	function getPlayers($aCriteria)
	{
		// selectionner toutes les instances concernees
		$instances = array();
		if ( !empty($aCriteria->instanceId) )
		{
			$instances[] = $aCriteria->instanceId;
		}
		$dbNameP = Bn::getConfigValue('base', 'database_poona');
		$q = new Bn_query("$dbNameP.PERSONNE", '_poona');
		$q->addField('PER_ID',        'poonaid');
		$q->addField('PER_NOM',       'familyname');
		$q->addField('PER_PRENOM',    'firstname');
		$q->addField('PER_LICENCE',   'license');

		$q->leftJoin("$dbNameP.PERSONNE_SEXE", 'PER_PES_ID=PES_ID');
		$q->addField('PES_NOM_LONG', 'gender');

		$dbName = Opoona::getDbName($aCriteria->season);
		$q->leftJoin("`$dbName`.JOUEUR",  'JOU_PER_ID=PER_ID');
		$q->addField('JOU_IS_MUTE', 'ismute');
		$q->addField('JOU_IS_ASSIMILE', 'isassimile');
		$q->addField('JOU_DATE_AUTO_COMPET', 'dateauto');
		$q->addField('JOU_DATE_SURCLAS', 'datesurclasse');
		$q->addField('JOU_MOYENNE_MONTEE_SIMPLE', 'ups');
		$q->addField('JOU_MOYENNE_MONTEE_DOUBLE', 'upd');
		$q->addField('JOU_MOYENNE_MONTEE_MIXTE', 'upm');
		$q->addField('JOU_MOYENNE_DESCENTE_SIMPLE', 'downs');
		$q->addField('JOU_MOYENNE_DESCENTE_DOUBLE', 'downd');
		$q->addField('JOU_MOYENNE_DESCENTE_MIXTE', 'downm');
		$q->addField('JOU_INS_ID', 'asso');
		$q->addField('JOU_RANK', 'rank');

		$q->leftJoin("$dbNnameP.JOUEUR_CATEGORIE", 'JOC_ID=JOU_JOC_ID');
		$q->addField('JOC_NOM_LONG', 'catage');

		$q->leftJoin("$dbNameP.TYPE_SURCLASS", 'JOU_TSC_ID=TSC_ID');
		$q->addField('TSC_NOM_COURT', 'surclasse');

		$q->leftJoin("$dbNameP.TYPE_CLASSEMENT s", 'JOU_TCL_ID_SIMPLE_OFF=s.TCL_ID');
		$q->addField('s.TCL_NOM', 'levels');
		$q->leftJoin("$dbNameP.TYPE_CLASSEMENT d", 'JOU_TCL_ID_DOUBLE_OFF=d.TCL_ID');
		$q->addField('d.TCL_NOM', 'leveld');
		$q->leftJoin("$dbNameP.TYPE_CLASSEMENT m", 'JOU_TCL_ID_MIXTE_OFF=m.TCL_ID');
		$q->addField('m.TCL_NOM', 'levelm');

		// Filtre sur l'instance
		if (count($instances)) $q->addWhere("JOU_INS_ID IN ('" . implode("','", $instances) . "')");

		// Filtre sur le genre
		$gender = explode(':', $aCriteria->gender);
		if (strlen($aCriteria->gender) && count($gender) == 1)
		$q->addWhere("PER_PES_ID = " . self::$_genderBP[reset($gender)]);

		// Filtre sur les categories d'age
		$where = Opoona::_wcatage($aCriteria->catage);
		if ($where != '') $q->addWhere("JOU_JOC_ID" . $where);

		// Filtre sur les noms
		$where = addslashes($aCriteria->familyname);
		if ($where != '') $q->addWhere("per_nom LIKE '%" . $where . "%'");

		// Filtre sur les prenoms
		$where = addslashes($aCriteria->firstname);
		if ($where != '') $q->addWhere("per_prenom LIKE '%" . $where . "%'");

		// Filtre sur les licences
		$where = $aCriteria->license;
		if ($where != '') $q->addWhere("per_licence LIKE '%" . $where . "%'");

		// Filtre multi colone
		$criter = addslashes($aCriteria->multi);
		if ($criter != '')
		{
			$where = "((per_nom LIKE '%" . $criter . "%')";
			$where .= " OR (per_prenom LIKE '%" . $criter . "%')";
			$where .= " OR (per_licence LIKE '%" . $criter . "%'))";
			$q->addWhere($where);
		}

		// Ordre de tri
		switch ($aCriteria->sort)
		{
			case 'gender':
				$sort = 'PES_NOM_LONG '. $aCriteria->order;
				break;
			case 'familyname':
				$sort = 'PER_NOM '. $aCriteria->order;
				break;
			case 'firstname':
				$sort = 'PER_PRENOM '. $aCriteria->order;
				break;
			case 'name':
				$sort = 'PER_NOM ' . $aCriteria->order  . ', PER_PRENOM ' . $aCriteria->order;
				break;
			case 'catage':
				$sort = 'JOC_NOM_LONG '. $aCriteria->order;
				break;
			case 'surclasse':
				$sort = 'TSC_NOM_COURT '. $aCriteria->order;
				break;
			case 'license':
				$sort = 'PER_LICENCE '. $aCriteria->order;
				break;
			case 'levels':
				$sort = 's.TCL_NOM '. $aCriteria->order . ',d.TCL_NOM '. $aCriteria->order . ',m.TCL_NOM '. $aCriteria->order;
				break;
			case 'single':
				$sort = 'JOU_MOYENNE_MONTEE_SIMPLE '. $aCriteria->order;
				break;
			case 'double':
				$sort = 'JOU_MOYENNE_MONTEE_DOUBLE '. $aCriteria->order;
				break;
			case 'mixed':
				$sort = 'JOU_MOYENNE_MONTEE_MIXTE '. $aCriteria->order;
				break;
			case 'rank':
				$sort = 'JOU_RANK '. $aCriteria->order;
				break;
			case 'asso':
				$sort = 'JOU_INS_ID '. $aCriteria->order;
				break;
		}
		$q->setOrder($sort);

		// Calcul des enregistrements a recuperer
		$nbRecords = $q->getCount();
		$nb = $aCriteria->number > 0 ? $aCriteria->number : 20;
		$nbPages = ceil($nbRecords/$nb);
		$page = $aCriteria->page > 0 ? $aCriteria->page : 1;
		$first = ($page-1) * $nb;
		if ( $first > $nbRecords )
		{
			$first = 1;
			$page = 1;
		}
		$q->setLimit($first, $nb);

		// Recuperation des enregistrements
		$players = $q->getRows();
		if ( $q->isError() )
		{
			return false;
		}
		$i = 1;
		$rows = array();
		foreach($players as $player)
		{
			// Remplissage du joueur
			$row = array(
				'num'       => ($page-1)*$nb + $i++,
				'gender'    => $player['gender'],
				'name'      => $player['familyname'] . ' ' .$player['firstname'],
				'familyname'=> $player['familyname'],
				'firstname' => $player['firstname'],
				'catage'    => $player['catage'],
				'surclasse' => $player['surclasse'],
				'license'   => $player['license'],
				'single'    => $player['ups'],
				'double'    => $player['upd'],
				'mixed'     => $player['upm'],
				'rank'      => $player['rank'],
				'asso'      => $player['asso'],
				'id'=> $player['poonaid'],
		    	'nbPages'   => $nbPages,
		    	'nbRecords' => $nbRecords,
		    	'page'      => $page
			);
			$row['levels'] = $player['levels'];
			$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * Acces aux tournois  de la saison
	 * @param 	integer	$aSeason	Numero de la saison
	 * @return array
	 */
	public function getEvents($aSeason)
	{
		require_once "Object/Oseason.php";
		O_season::getPoonaYears($aSeason);

		// nom de la base : poona_2007_2008
		$qp = new Bn_query('EVENEMENT', '_poona_'.$season);
		$qp->setFields('evn_id, evn_nom, evn_num_auto');
		$qp->addWhere('evn_id IN (' . implode(',', $ids) . ')');
		$qp->setOrder('evn_nom');

		$lst = $container->addSelect('lstEventPoona', 'Poona', EVNT_FILL_EVENT);
		$lst->addOptions($qp);

		return $this->_data;
	}

	/**
	 * Liste des tournois d'une instance
	 */
	function getInstanceEvents($aInstanceId)
	{
		$dbName = Opoona::getDbName();
		$q = new Bn_query("`$dbName`.EVENEMENT", '_poona', $dbName);
		$q->addField('EVN_ID', 'fedeid');
		$q->addField('EVN_NOM', 'name');
		$q->addWhere('EVN_INS_ID_ORGA=' . $aInstanceId);
		$q->addWhere("EVN_DEBUT > '" . date('Y-m-d') ."'");
		$q->addWhere("EVN_NUMERO_AUTORISATION != ''");

		$q->setOrder('EVN_DEBUT');
		$events = $q->getRows();
		unset($q);
		return $events;
	}



	/**
	 * Construit une clause where pour les categorie d'age poona
	 * a partir des categorie d'age badNet
	 *
	 * @param sting $aCatage categorie d'age recherchee separe par une virgule (OPLAYER_CATAGE_POU,OPLAYER_CATAGE_BEN,OPLAYER_CATAGE_MIN)
	 */
	public function _wcatage($aCatage)
	{
		$catagesB = explode(':', $aCatage);
		$catagesP = array();
		foreach($catagesB as $catage)
		{
			switch ($catage)
			{
				case OPLAYER_CATAGE_POU :
					$catagesP[] = 1;
					break;
				case OPLAYER_CATAGE_BEN :
					$catagesP[] = 2;
					$catagesP[] = 12;
					break;
				case OPLAYER_CATAGE_MIN :
					$catagesP[] = 3;
					$catagesP[] = 13;
					break;
				case OPLAYER_CATAGE_CAD :
					$catagesP[] = 4;
					$catagesP[] = 14;
					break;
				case OPLAYER_CATAGE_JUN :
					$catagesP[] = 5;
					$catagesP[] = 15;
					break;
				case OPLAYER_CATAGE_SEN :
					$catagesP[] = 6;
					$catagesP[] = 20;
					break;
				case OPLAYER_CATAGE_VET :
					$catagesP[] = 7;
					$catagesP[] = 17;
					$catagesP[] = 18;
					$catagesP[] = 19;
					$catagesP[] = 20;
					break;
			}

		}
		if (count($catagesP) && count($catagesP)<16)
		$where = ' IN (' . implode(',', $catagesP) .')';
		else
		$where = '';
		return $where;
	}

	/**
	 * Renvoi la categorie d'age BadNet correspondant a une categorie Poona
	 *
	 * @param integer $aCatagePoona  categorie d'age Poona
	 * @return integer
	 */
	public function getCatageBadnet($aCatagePoona)
	{
		$_catage = array(
		1=> OPLAYER_CATAGE_POU,
		2=> OPLAYER_CATAGE_BEN,
		3=> OPLAYER_CATAGE_MIN,
		4=> OPLAYER_CATAGE_CAD,
		5=> OPLAYER_CATAGE_JUN,
		6=> OPLAYER_CATAGE_SEN,
		7=> OPLAYER_CATAGE_VET,// VET2
		12=> OPLAYER_CATAGE_BEN,// BEN2
		13=> OPLAYER_CATAGE_MIN,// MIN2
		14=> OPLAYER_CATAGE_CAD,// CAD2
		15=> OPLAYER_CATAGE_JUN,// JUN2
		17=> OPLAYER_CATAGE_VET,// VET3
		18=> OPLAYER_CATAGE_VET,// VET4
		19=> OPLAYER_CATAGE_VET, // VET5
		20=> OPLAYER_CATAGE_VET // VET1
		);
		if (isset($_catage[$aCatagePoona])) return $_catage[$aCatagePoona];
		else return OPLAYER_CATAGE_SEN;
	}

	/**
	 * Renvoi le  numero de categorie d'age BadNet correspondant a une categorie Poona
	 *
	 * @param integer $aCatagePoona  categorie d'age Poona
	 * @return integer
	 */
	public function getNumCatageBadnet($aCatagePoona)
	{
		$_numCatage = array(
		1=> 0, //OPLAYER_CATAGE_POU,
		2=> 1, //OPLAYER_CATAGE_BEN,
		3=> 1, //OPLAYER_CATAGE_MIN,
		4=> 1, //OPLAYER_CATAGE_CAD,
		5=> 1, //OPLAYER_CATAGE_JUN,
		6=> 1, //OPLAYER_CATAGE_SEN,
		7=> 2, //OPLAYER_CATAGE_VET,// VET2
		12=> 2, //OPLAYER_CATAGE_BEN,// BEN2
		13=> 2, //OPLAYER_CATAGE_MIN,// MIN2
		14=> 2, //OPLAYER_CATAGE_CAD,// CAD2
		15=> 2, //OPLAYER_CATAGE_JUN,// JUN2
		17=> 3, //OPLAYER_CATAGE_VET,// VET3
		18=> 4, //OPLAYER_CATAGE_VET,// VET4
		19=> 5, //OPLAYER_CATAGE_VET, // VET5
		20=> 1  //OPLAYER_CATAGE_VET // VET1
		);

		if (isset($_numCatage[$aCatagePoona])) return $_numCatage[$aCatagePoona];
		else return 1;
	}

	/**
	 * Renvoi la discipline BadNet correspondant a une discipline Poona
	 *
	 * @param integer $aDisciPoona  discipline Poona
	 * @return integer
	 */
	public function getDisciBadnet($aDisciPoona)
	{
		$_discipline = array(
		1=> OPLAYER_DISCIPLINE_MS,
		2=> OPLAYER_DISCIPLINE_WS,
		3=> OPLAYER_DISCIPLINE_MD,
		4=> OPLAYER_DISCIPLINE_WD,
		5=> OPLAYER_DISCIPLINE_MX
		);
		if (isset($_discipline[$aDisciPoona])) return $_discipline[$aDisciPoona];
		else return OPLAYER_DISCIPLINE_MS;
	}

	/**
	 * Renvoi la discipline Poona correspondant a une discipline Badnet
	 *
	 * @param integer $aDisciBadnet  discipline Badnet
	 * @return integer
	 */
	public function getDisciPoona($aDisciBadnet)
	{
		$_discipline = array(
		OPLAYER_DISCIPLINE_MS => 1,
		OPLAYER_DISCIPLINE_WS => 2,
		OPLAYER_DISCIPLINE_MD => 3,
		OPLAYER_DISCIPLINE_WD => 4,
		OPLAYER_DISCIPLINE_MX => 5
		);
		if (isset($_discipline['$aDisciBadnet'])) return $_discipline['$aDisciBadnet'];
		else return 1;
	}

	/**
	 * Renvoi le nom de la base de donnée Pona en fonction de la saison
	 *
	 * @param unknown_type $aDate
	 * @return unknown
	 */
	public function getDbName($aSeason=null)
	{
		// Recuperer le nom de la base saisonniere dans le fichier de configuration
		$dbName = Bn::getConfigValue('bases', 'database_poona');
		// Si le nom est vide (cas de la ffba), il est calcule
		if ( empty($dbName) )
		{
			$year = Oseason::getYear($aSeason);
			$dbName = sprintf("poona_%d-%d", $year, $year+1);
		}
		return $dbName;
	}
}
?>