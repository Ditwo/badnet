<?php
/*****************************************************************************
 !   Module     : utils
 !   File       : $Source: /cvsroot/aotb/badnet/src/utils/utvars.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.10 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/02/01 18:19:00 $
 ******************************************************************************/
/**
 * Classe utilitaire pour la recupï¿½ration des donnï¿½es spï¿½cifiques
 * ï¿½ l'utilisateur connectï¿½.
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

require_once "const.inc";

static $varsEvent=null;

class utVars
{
	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function utVars()
	{
	}
	// }}}

	// {{{ init()
	/**
	 * @brief
	 * Initialise l'environnement du tournoi courant
	 * Charge les donnees du tournois courant. Ainsi un seul acces la base
	 * est effectue en debut de script.
	 *
	 */
	function init()
	{
		global $varsEvent;
		if (is_null($varsEvent))
		{
	  $eventId = utvars::getEventId();
	  if ($eventId !== -1)
	  {
	  	require_once "utils/utevent.php";
	  	$ute = new utevent();
	  	$varsEvent = $ute->getEvent($eventId);
	  }
		}
	}
	// }}}


	// {{{ getDepartements
	/**
	 * Construit la liste des departement
	 *
	 * @access private
	 * @return void
	 */
	function getDepartements()
	{
		$dpt = array( 1=>'01-Ain',
		2=>'02-Aisne',
		3=>'03-Allier',
		4=>'04-Alpes de Haute Provence',
		5=>'05-Hautes Alpes',
		6=>'06-Alpes Maritimes',
		7=>'07-Ardèche',
		8=>'08-Ardennes',
		9=>'09-Ariège',
		10=>'10-Aube',
		11=>'11-Aude',
		12=>'12-Aveyron',
		13=>'13-Bouches du Rhône',
		14=>'14-Calvados',
		15=>'15-Cantal',
		16=>'16-Charente',
		17=>'17-Charente Maritime',
		18=>'18-Cher',
		19=>'19-Corrèze',
		20=>'20-Corse',
		21=>"21-Côte d'Or",
		22=>"22-Côtes d'Armor",
		23=>'23-Creuse',
		24=>'24-Dordogne',
		25=>'25-Doubs',
		26=>'26-Drôme',
		27=>'27-Eure',
		28=>'28-Eure et Loir',
		29=>'29-Ministère',
		30=>'30-Gard',
		31=>'31-Haute Garonne',
		32=>'32-Gers',
		33=>'33-Gironde',
		34=>'34-Hérault',
		35=>'35-Ile et Vilaine',
		36=>'36-Indre',
		37=>'37-Indre et Loire',
		38=>'38-Isère',
		39=>'39-Jura',
		40=>'40-Landes',
		41=>'41-Loir et Cher',
		42=>'42-Loire',
		43=>'43-Haute Loire',
		44=>'44-Loire Atlantique',
		45=>'45-Loiret',
		46=>'46-Lot',
		47=>'47-Lot et Garonne',
		48=>'48-Lozère',
		49=>'49-Maine et Loire',
		50=>'50-Manche',
		51=>'51-Marne',
		52=>'52-Haute Marne',
		53=>'53-Mayenne',
		54=>'54-Meurthe et Moselle',
		55=>'55-Meuse',
		56=>'56-Morbihan',
		57=>'57-Moselle',
		58=>'58-Nièvre',
		59=>'59-Nord',
		60=>'60-Oise',
		61=>'61-Orne',
		62=>'62-Pas de Calais',
		63=>'63-Puy de Dômes',
		64=>'64-Pyrénées Atlantiques',
		65=>'65-Hautes Pyrénées',
		66=>'66-Pyrénées Orientale',
		67=>'67-Bas Rhin',
		68=>'68-Haut Rhin',
		69=>'69-Rhône',
		70=>'70-Haute Saône',
		71=>'71-Saône et Loire',
		72=>'72-Sarthe',
		73=>'73-Savoie',
		74=>'74-Haute Savoie',
		75=>'75-Paris',
		76=>'76-Seine Maritime',
		77=>'77-Seine et Marne',
		78=>'78-Yvelines',
		79=>'79-Deux Sèvres',
		80=>'80-Somme',
		81=>'81-Tarn',
		82=>'82-Tarn et Garonne',
		83=>'83-Var',
		84=>'84-Vaucluse',
		85=>'85-Vendée',
		86=>'86-Vienne',
		87=>'87-Haute Vienne',
		88=>'88-Vosges',
		89=>'89-Yonne',
		90=>'90-Territoire de Belfort',
		91=>'91-Essone',
		92=>'92-Hauts de Seine',
		93=>'93-Seine St Denis',
		94=>'94-Val de Marne',
		95=>"95-Val d'oise",
		97=>"97-Département Outre-Mer",
		98=>"98-Pays Outre-Mer"
		);
		return $dpt;
	}
	// }}}

	// {{{ getRegions
	/**
	 * Construit la liste des regions
	 *
	 * @access private
	 * @return void
	 */
	function getRegions()
	{
		$reg = array('ALS'=>'Alsace',
		   'AQ'=>'Aquitaine',
		   'AUV'=>'Auvergne',
		   'BNIE'=>'Basse Normandie',
		   'BOUR'=>'Bourgogne',
		   'BRE'=>'Bretagne',
		   'CENT'=>'Centre',
		   'CHAM'=>'Champagne-Ardenne',
		//'CO'=>'Corse',
		//'DOM'=>"Dï¿½partements d'Outre Mer",
		   'FC'=>'Franche Comtï¿½',
		   'GUY'=>'Guyane',
		   'HNIE'=>'Haute Normandie',
		   'LIFB'=>'Ile de France',
		   'LR'=>'Languedoc Roussillon',
		   'LIM'=>'Limousin',
		   'LNBR'=>'La Rï¿½union',		   
		   'LOR'=>'Lorraine',		   
		   'MART'=>'Martinique',
		   'MPYR'=>'Midi-Pyrï¿½nnï¿½es',
		   'NCAL'=>'Nouvelle Calï¿½donie',
		   'NPC'=>'Nord Pas de Calais',
		   'PACA'=>"Provence Alpes Cï¿½te d'Azur",
		   'PL'=>'Pays de Loire',
		   'PIC'=>'Picardie',
		   'PC'=>'Poitou Charente',
		   'RA'=>'Rhï¿½ne Alpes',
		//'TOM'=>"Territoires d'Outre Mer"
		);
		return $reg;
	}
	// }}}

	// {{{ isTeamEvent()
	/**
	 * @brief
	 * Renvoi le type du tournoi
	 *
	 * @return
	 *    Le type du tournoi ou -1
	 *
	 */
	function isTeamEvent()
	{
		global $varsEvent;
		if (!is_null($varsEvent))
		return $varsEvent['evnt_type'] != WBS_EVENT_INDIVIDUAL;
		else
		return false;
	}
	// }}}

	// {{{ isLineRegi()
	/**
	 * @brief
	 * Renvoi si l'inscrption en ligne est active
	 *
	 * @return
	 *    Le type du tournoi ou -1
	 *
	 */
	function isLineRegi()
	{
		global $varsEvent;
		if (!is_null($varsEvent))
		return $varsEvent['evnt_liveentries'] == 160;
		else
		return false;
	}
	// }}}


	// {{{ getEventType()
	/**
	 * @brief
	 * Renvoi le type du tournoi
	 *
	 * @return
	 *    Le type du tournoi ou -1
	 *
	 */
	function getEventType()
	{
		global $varsEvent;
		if (!is_null($varsEvent))
		return $varsEvent['evnt_type'];
		else
		return -1;
	}
	// }}}

	// {{{ getEventLevel()
	/**
	 * @brief
	 * @~french  Renvoi le niveau du tournoi
	 *
	 * @return @~french (string) baseName
	 *
	 */
	function getEventLevel()
	{
		global $varsEvent;
		if (!is_null($varsEvent)) return $varsEvent['evnt_level'];
		else return -1;
	}
	// }}}

	// {{{ getMask()
	/**
	 * @brief
	 * @~english Return the mask for lconnexion to the database
	 * @~french  Renvoi le masque de selection des enregistrements
	 *
	 * @par Description:
	 * @~french Lors de l'installation, le nom de la base de donnees
	 * est enregistrï¿½ dans un fichier. Cette methode permet de le
	 * recuperer
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french (string) baseName
	 *         @~english (string) baseName
	 *
	 */
	function getMask()
	{
		static $mask=null;
		if (is_null($mask))
		{
	  require_once "utevent.php";
	  $ute = new utevent();
	  $mask = $ute->getAuthMask();
		}
		return $mask;
	}
	// }}}

	// {{{ getAuthLevel()
	/**
	 * @brief
	 * Renvoi le niveau d'autorisation pour l'utilisateur connecte
	 */
	function getAuthLevel()
	{
		static $level=null;
		if (is_null($level))
		{
	  require_once "utevent.php";
	  $ute = new utevent();
	  $level = $ute->getAuthLevel();
		}
		return $level;
	}
	// }}}


	// {{{ getPage()
	/**
	 * @brief
	 * @~english Return the connexion to the database
	 * @~french  Renvoi la connexion a la base de donnees
	 *
	 * @par Description:
	 * @~french Lors de l'installation, le nom de la base de donnees
	 * est enregistrï¿½ dans un fichier. Cette methode permet de le
	 * recuperer
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french (string) baseName
	 *         @~english (string) baseName
	 *
	 */
	function &getPage($name=null)
	{
		static $page=null;
		if (is_null($page))
		$page = new kPage($name);
		return $page;
	}
	// }}}

	/**
	 * @brief
	 * @~english Return the connexion to the database
	 * @~french  Renvoi la connexion a la base de donnees
	 *
	 * @par Description:
	 * @~french Lors de l'installation, le nom de la base de donnees
	 * est enregistrï¿½ dans un fichier. Cette methode permet de le
	 * recuperer
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french (string) baseName
	 *         @~english (string) baseName
	 *
	 */
	function getDbConnect()
	{
		static $cnx=null;
		if (is_null($cnx))
		{
			$dsn = utvars::getDsn();
			if (empty($dsn) ) return $cnx;
			$fields = utvars::_readCfgFile();
			if ($fields[DB_TYPE] == 'mysql') $cnx = new PDO($dsn, $fields[DB_LOGIN], $fields[DB_PWD]);
			else $cnx = new PDO($dsn);
		}
		return $cnx;
	}

	function getDbType()
	{
		$fields = utvars::_readCfgFile();
		if (isset($fields[DB_TYPE])) return $fields[DB_TYPE];
		else return false;
	}
	
	function getDbLogin()
	{
		$fields = utvars::_readCfgFile();
		if (isset($fields[DB_LOGIN])) return $fields[DB_LOGIN];
		else return false;
	}
	function getDbPwd()
	{
		$fields = utvars::_readCfgFile();
		if (isset($fields[DB_PWD])) return $fields[DB_PWD];
		else return false;
	}
	

	// {{{ getBaseName()
	/**
	 * @brief
	 * @~english Return the name of the database
	 * @~french  Renvoi le nom de la base de donnees
	 *
	 * @par Description:
	 * @~french Lors de l'installation, le nom de la base de donnees
	 * est enregistrï¿½ dans un fichier. Cette methode permet de le
	 * recuperer
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french (string) baseName
	 *         @~english (string) baseName
	 *
	 */
	function getBaseName()
	{
		$fields = utvars::_readCfgFile();
		if (isset($fields[DB_BASE])) return $fields[DB_BASE];
		else return false;
	}
	// }}}


	// {{{ getPrfx()
	/**
	 * @brief
	 * @~english Return the prefixe of the table of  database
	 * @~french  Renvoi le prefixe des tables de la base
	 *
	 * @par Description:
	 * @~french Lors de l'installation, les parametres de connexion ï¿½
	 * la base de donnï¿½es sont enregistrï¿½s dans un fichier. A chaque 
	 * acces a la base de donnï¿½e, il faut rï¿½cuperer ces paramï¿½tres.
	 * Cette mï¿½thode s'en charge et renvoi la valeur necessaire pour 
	 * la connection
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french (string) Dsn
	 *         @~english (string) Dsn
	 *
	 */
	function getPrfx()
	{
		static $prefix = null;
		if (is_null($prefix))
		{
	  $fds = utvars::_readCfgFile();
	  if (!is_null($fds))
	  {
	  	$prefix = 'aotb';
	  	if (count($fds) > 4)
	  	{
	  		if (isset($fds[DB_PREFIX]) &&
	  		strlen(trim($fds[DB_PREFIX])))
	  		$prefix = $fds[DB_PREFIX];
	  	}
	  }
		}
		return $prefix;
	}
	// }}}


	// {{{ getDsn()
	/**
	 * @brief
	 * @~english Return the dsn for database connexion
	 * @~french  Renvoi le dsn pour la connexion a la base de donnees
	 *
	 * @par Description:
	 * @~french Lors de l'installation, les parametres de connexion ï¿½
	 * la base de donnï¿½es sont enregistrï¿½s dans un fichier. A chaque 
	 * acces a la base de donnï¿½e, il faut rï¿½cuperer ces paramï¿½tres.
	 * Cette mï¿½thode s'en charge et renvoi la valeur necessaire pour 
	 * la connection
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french (string) Dsn
	 *         @~english (string) Dsn
	 *
	 */
	function getDsn()
	{
		static $dsn = null;
		if (is_null($dsn))
		{
			$fds = utvars::_readCfgFile();
			if (count($fds) > 4)
			{
				if ($fds[DB_TYPE] == 'mysql')
				{
					$dsn = $fds[DB_TYPE] . ':host=' . $fds[DB_SERVER];
					$dsn .= ';dbname=' . $fds[DB_BASE];
				}
				else if ($fds[DB_TYPE] == 'sqlite')
				{
					$dsn = $fds[DB_TYPE] . ':' . $fds[DB_BASE];
				}
			}
		}
		return $dsn;
	}
	// }}}

	// {{{ getUserId()
	/**
	 * Retourne l'id de l'utilisateur courant
	 *
	 */
	function getUserId()
	{
		return (utvars::_getSessionVar('userId'));
	}
	// }}}

	// {{{ getUserLogin()
	/**
	 * Retourne le nom de l'utilisateur courant
	 */
	function getUserLogin()
	{
		$db = new utbase();
		$where = "user_id=".utvars::getUserId();
		$login = $db->_selectFirst('users', 'user_login', $where);
		unset($db);
		return $login;
	}
	// }}}

	// {{{ getUserPwd()
	/**
	 * Retourne le mot de passe  de l'utilisateur courant
	 */
	function getUserPwd()
	{
		$db = new utbase();
		$where = "user_id=".utvars::getUserId();
		$pwd = $db->_selectFirst('users', 'user_pass', $where);
		unset($db);
		return $pwd;
	}
	// }}}



	// {{{ getEventId()
	/**
	 * @brief
	 * @~english Return the id of the visited event.
	 * @~french  Renvoi l'identifiant du tournoi en cours d'utilisation
	 *
	 * @par Description:
	 * @~french Lorsqu'un utilisateur choisi un tournoi, l'identifiant
	 * du tournoi est mï¿½morisï¿½ dans la session courante (evnt_id dans 
	 * la base). Cette mï¿½thode permet de rï¿½cuperer cet identifiant. 
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french (integer) Identifiant du tournoi
	 *         @~english (interger) Event id
	 *
	 */
	function getEventId()
	{
		if (utvars::getTheme() == WBS_THEME_EVENT )
		{
	  $themeId =  (utvars::_getSessionVar('themeId'));
	  if ($themeId == '') return -1;
	  else return $themeId;
		}
		else
		return -1;
	}
	// }}}

	// {{{ getSectorId()
	/**
	 * @brief
	 * @~english Return the section id of the visited event.
	 * @~french  Renvoi la section du tournoi en cours d'utilisation
	 *
	 * @par Description:
	 * @~french En mode admnistration, un tournoi est decoupï¿½ en
	 * plusieurs section. Chaque secteur couvre un ensemble de
	 * fonctionnalitï¿½e connexe. Les secteurs definit sont SPORTIF,
	 * COMMUNICATION, FINANCE. Cette mï¿½thode permet de rï¿½cuperer
	 * le secteur en cours d'utilisation.
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french (integer) Identifiant du secteur
	 *         @~english (interger) Sector id
	 *
	 */
	function getSectorId()
	{
		if (utvars::getTheme() == WBS_THEME_EVENT )
		{
	  $sectorId =  (utvars::_getSessionVar('sectorId'));
	  if ($sectorId == '') return WBS_SECTOR_SPORT;
	  else return $sectorId;
		}
		else
		return -1;
	}
	// }}}

	// {{{ getBookId()
	/**
	 * @brief
	 * @~english Return the id of the visited address book.
	 * @~french  Renvoi l'identifiant du carnet d'adresse en
	 * cours d'utilisation
	 *
	 * @par Description:
	 * @~french Lorsqu'un utilisateur choisi un carnet d'adresse,
	 * l'identifiant du carnet est mï¿½morisï¿½ dans la session courante 
	 * (book_id dans la base). Cette mï¿½thode permet de rï¿½cuperer cet 
	 * identifiant.
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french (integer) Identifiant du carnet d'adresse
	 *         @~english (interger) Address book id
	 *
	 */
	function getBookId()
	{
		if (utvars::getTheme() == WBS_THEME_BOOK )
		{
	  $bookId =  (utvars::_getSessionVar('themeId'));
	  if ($bookId == '') return -1;
	  else return $bookId;
		}
		else
		return -1;
	}
	// }}}

	// {{{ getTheme()
	/**
	 * @brief
	 * @~english Return the type of visited theme.
	 * @~french  Renvoi le type de theme en cours d'utilisation.
	 *
	 * @par Description:
	 * @~french Un utilisateur peut choisir de travailler sur un
	 * carnet d'adresse ou sur un tournoi. Ce choix est appelï¿½ un
	 * theme. Cette mï¿½thode permet de savoir sur quel type de theme
	 * l'utilisateur travaille:
	 *  @li WBS_THEME_NONE  aucun thï¿½me de sï¿½lectionnï¿½
	 *  @li WBS_THEME_EVENT l'utilisateur travaille sur un tournoi
	 *  @li WBS_THEME_BOOK  l'utilisateur travaille sur un carnet d'adresse
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french (integer) Type du thï¿½me
	 *         @~english (interger) Theme
	 *
	 */
	function getTheme()
	{
		$theme = utvars::_getSessionVar('theme');
		if ($theme != WBS_THEME_EVENT &&
		$theme != WBS_THEME_BOOK)
		$theme = WBS_THEME_NONE;
		return $theme;
	}
	// }}}

	// {{{ setSectorId()
	/**
	 * Fix the id sector of the current event
	 *
	 * @access public
	 * @param integer Id of the theme
	 * @return int event id
	 */
	function setSectorId($sectorId)
	{
		utvars::setSessionVar("sectorId", $sectorId);
	}
	// }}}


	// {{{ setEventId()
	/**
	 * Fix the id of the current event
	 *
	 * @access public
	 * @param integer Id of the theme
	 * @return int event id
	 */
	function setEventId($eventId)
	{
		utvars::setSessionVar("themeId", $eventId);
	}
	// }}}

	// {{{ setBookId()
	/**
	 * Fix the id of the current adress book
	 *
	 * @access public
	 * @param integer Id of the theme
	 * @return int event id
	 */
	function setBookId($bookId)
	{
		utvars::setSessionVar("themeId", $bookId);
	}
	// }}}

	// {{{ getLanguage()
	/**
	 * @brief
	 * @~english Return the language select by the user
	 * @~french  Renvoi le langage de l'utilisateur
	 *
	 * @par Description:
	 * @~french Lorsque utilisateur se connecte de facon anonyme,
	 * le language qu'il choisi est mï¿½morisï¿½ dans la session. Si 
	 * l'utilisateur a son propre compte,c'est la langue de son
	 * compte qui est mï¿½morisï¿½ dans la session. Cette fonction renvo
	 * la langue de la session courante. Elle est utilisï¿½e pour choisir
	 * les fichiers ï¿½ utiliser pour la traduction du site.
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french (string) Chaine de la langue mï¿½morisï¿½e pour la session
	 *         @~english (string) Language select by the user
	 *
	 */
	function getLanguage()
	{
		$lang = utvars::_getSessionVar('userLang');
		if ($lang == '')
		{
	  $fds = utvars::_readCfgFile();
	  if (isset($fds[DEF_LANG])) $lang = $fds[DEF_LANG];
		}
		if ($lang == '') $lang = 'fra';
		return $lang;
	}
	// }}}

	// {{{ setLanguage()
	/**
	 * Fixe the language select by the user
	 *
	 * @access public
	 * @param string $lang  language
	 * @return string language select
	 */
	function setLanguage($lang)
	{
		utvars::setSessionVar('userLang', $lang);
	}
	// }}}



	//-----------------------------------------
	//  Here are private methode
	//------------------------------------------

	// {{{ _readCfgFile()
	/**
	 * @brief
	 * @~english Return the list of field of ci=onfiguration file
	 * @~french  Renvoi les champs du fichier de configuration
	 *
	 * @par Description:
	 * @~french Lors de l'installation, les parametre de connexion et de gestion de
	 * la base de donnees sont enregistrï¿½s dans un fichier. Cette methode permet de
	 * les recuperer
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french (string) baseName
	 *         @~english (string) baseName
	 *
	 */
	function _readCfgFile()
	{
		static $fields = NULL;
		if (is_null($fields))
		{
	  $file = "../data/cnx.dat";
	  if (file_exists($file))
	  {
	  	$fp = fopen($file, "r");
	  	$buf = '';
	  	if ($fp)
	  	{
	  		$buf = fgets($fp, 4096);
	  		fclose($fp);
	  		$fields = preg_split("/\t/", $buf);
	  	}
	  }
		}
		return $fields;
	}
	// }}}


	// {{{ _getSessionVar()
	/**
	 * Get the attribut with the post, get or session parameters
	 *
	 * @access public
	 * @param  string  $attribut to return
	 * @return mixed   value of the attribute
	 */
	function _getSessionVar($attribut)
	{

		$var = &$GLOBALS['_SESSION'];
		if (isset($var['wbs'][$attribut]))
		{
	  return $var['wbs'][$attribut];
		}

		$var = &$GLOBALS['HTTP_SESSION_VARS'];
		if (isset($var['wbs'][$attribut]))
		{
	  return $var['wbs'][$attribut];
		}
		return '';
	}
	// }}}

	// {{{ setSessionVar()
	/**
	 * Set the attribut for the session
	 *
	 * @access public
	 * @param  string  $attribut name of the attrib to set
	 * @param  string  $value    value of the attrib
	 * @return void
	 */
	function setSessionVar($attribut, $value)
	{
		$var = &$_SESSION;
		if (!isset($var['wbs']) && !isset($_SESSION)) $_SESSION['wbs']=array();
		if (!isset($var['wbs']) || !is_array($var['wbs'])) $var['wbs'] = array();
		$var['wbs'][$attribut] = $value;
	}
	// }}}

}
?>