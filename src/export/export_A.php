<?php
/*****************************************************************************
 !   Module     : Export
 !   File       : $Source: /cvsroot/aotb/badnet/src/export/export_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.26 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/04/03 06:49:02 $
 ******************************************************************************/
require_once "export.inc";
require_once "base_A.php";
require_once "utils/utpage_A.php";
require_once "utils/objgroup.php";
require_once "events/events.inc";
require_once "dbf/dbf.inc";

/**
 * Module d'export des tournois
 *
 * @author Gerard CANTEGRIL
 * @see to follow
 *
 */

define("EXPORT_PATH",     "../tmp/");
define("EXPORT_FILE_ARCHIVE",    'event.zip');

define("CONTENT_TEAM",     1);
define("CONTENT_REGI",     2);
define("CONTENT_DRAW",     3);
define("CONTENT_T2T",      4);
define("CONTENT_T2R",      5);
define("CONTENT_PAIR",     6);
define("CONTENT_P2M",      7);
define("CONTENT_ACCOUNTS", 8);
define("CONTENT_ITEMS",    9);
define("CONTENT_BOOK",     10);
define("CONTENT_POSTIT",   11);
define("CONTENT_ACCOUNTSTEAMS", 12);
define("CONTENT_ACCOUNTSREGIS", 13);
define("CONTENT_END",      20);

class export_A
{

	// {{{ properties

	/**
	 * Utils objet
	 *
	 * @var     object
	 * @access  private
	 */
	var $_ut;

	/**
	 * Database access object
	 *
	 * @var     object
	 * @access  private
	 */
	var $_dt;

	// }}}

	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function export_A()
	{
		$this->_ut = new Utils();
		$this->_dt = new exportBase();
	}
	// }}}

	function stripAccent($aStr)
	{
		return strtr($aStr,
 "\xe1\xc1\xe0\xc0\xe2\xc2\xe4\xc4\xe3\xc3\xe5\xc5".
 "\xaa\xe7\xc7\xe9\xc9\xe8\xc8\xea\xca\xeb\xcb\xed".
 "\xcd\xec\xcc\xee\xce\xef\xcf\xf1\xd1\xf3\xd3\xf2".
 "\xd2\xf4\xd4\xf6\xd6\xf5\xd5\x8\xd8\xba\xf0\xfa\xda".
 "\xf9\xd9\xfb\xdb\xfc\xdc\xfd\xdd\xff\xe6\xc6\xdf\xf8\xb0\x27\x2F\x26?",
 "aAaAaAaAaAaAacCeEeEeEeEiIiIiIiInNoOoOoOoOoOoOoouUuUuUuUyYyaAso  - -");
		//return strtr($aStr,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ',
		//'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');

	}

	// {{{ start()
	/**
	 * Start the connexion processus
	 *
	 * @access public
	 * @return void
	 */
	function start($page)
	{
		switch ($page)
		{
			case EXPORT_SEND_EVENT:
				$userId = utvars::getUserId();
				$eventId = kform::getInput('eventId', utvars::getEventId());
				$fileName = EXPORT_PATH."{$eventId}_{$userId}";
				$this->exportEvent($fileName, $eventId);
				$fd = fopen("{$fileName}_".EXPORT_FILE_ARCHIVE, 'r');
				while ($buf = fgets($fd, 4096))
				echo $buf;
				fclose($fd);
				unlink("{$fileName}_".EXPORT_FILE_ARCHIVE);
				exit;
				break;

			case WBS_ACT_EXPORT:
				$this->displayExport();
				break;
					
			case EXPORT_BADNET:
				$eventId = utvars::getEventId();
				$userId = utvars::getUserId();
				$baseFile = EXPORT_PATH."{$eventId}_{$userId}";
				$this->exportEvent($baseFile, $eventId);
				$this->displayExportBadNet();
				break;

			case EXPORT_SQUASHRANK:
				$this->_exportSquashrank();
				break;

			case EXPORT_SEND_FILE:
				$this->_sendFile();
				break;

			case IMPORT_GET_SOURCE:
			case WBS_ACT_IMPORT:
				$this->displayImport();
				break;
			case IMPORT_GET_FILES:
				$this->_prepareFiles();
				break;
			case IMPORT_LIST_EVENTS:
				$this->_listEvents();
				break;
			case IMPORT_SELECT_EVENT:
				$this->_selectEvent();
				break;

			case IMPORT_START:
				$this->_startImport();
				exit;
				break;

			case IMPORT_EVENT:
				$numFile = kform::getInput('numFile', 1);
				$baseName = kform::getInput('baseName');
				$nbFile   = kform::getInput('nbFile');
				$this->_import($baseName, $numFile, $nbFile);
				break;

			default :
				echo "export_A : page $page not allowed";
				break;
		}
		exit;
	}
	// }}}

	function _exportSquashrank()
	{
		$ut = new utils();
		$ute = new utevent();
		$eventId = utvars::getEventId();
		$event = $ute->getEvent($eventId);

		$path = realpath('../Temp/Tmp');
		$file = $event['evnt_name'];
		//$file = ereg_replace('/', '-', $event['evnt_name']);
		//$file = $this->stripAccent(utf8_decode($file));
		$file = $this->stripAccent(htmlspecialchars_decode($file));
		$filename = $path . "/$file.xml";

		$out = new XMLWriter();
		$out->openURI($filename);
		//print_r($out);
		$out->setIndentstring("   ");
		$out->setIndent(true);
		$out->startDocument('1.0', 'utf-8', 'yes');
		$out->startElement('event');
		$out->writeElement('version', '1.0');
		$out->writeElement('generate', date('Y-m-d H:i:s'));
		$out->writeElement('generator', 'SquashNet ' . $ut->getParam('softversion'));
		$out->writeElement('eventname',     utf8_encode($event['evnt_name']));
		$out->writeElement('eventdate',     utf8_encode($event['evnt_date']));
		$out->writeElement('eventfirstday', $event['evnt_firstday']);
		$out->writeElement('eventlastday',  $event['evnt_lastday']);
		$out->writeElement('eventplace',    utf8_encode($event['evnt_place']));
		$out->writeElement('eventcatage',   $event['evnt_catage']);
		$out->writeElement('eventtype',     $event['evnt_type']);
		$out->writeElement('eventseason',   $event['evnt_season']);
		$out->writeElement('eventlevel',    $event['evnt_level']);
		$out->writeElement('eventnum',      $event['evnt_numauto']);
		$out->writeElement('eventnature',      $event['evnt_nature']);
		$out->writeElement('eventserial',      1);

		// Recherche du ja
		$utb = new utBase();
		$tables = array('registration', 'members');
		$where = 'regi_memberid=mber_id AND regi_eventid='.$eventId;
		$where .= ' AND regi_type=' . WBS_REFEREE;
		$fields = array('mber_firstname', 'mber_secondname', 'regi_function');
		$referee = $utb->_getRow($tables, $fields, $where);
		if ( !empty($referee) )
		{
			$out->writeElement('eventreferee',     utf8_encode($referee['mber_firstname']) . ' ' . utf8_encode($referee['mber_secondname']));
			$out->writeElement('eventnumreferee',  utf8_encode($referee['regi_function']));
		}
		else
		{
			$out->writeElement('eventreferee',     'unknow');
			$out->writeElement('eventnumreferee',  'unknow');
		}

		// Tableaux
		if ($event['evnt_type'] == WBS_EVENT_INDIVIDUAL)
		{
			$tables = array('draws');
			$where = 'draw_eventid='.$eventId;
			$fields = array('draw_stamp', 'draw_disci', 'draw_catage', 'draw_id');
			$draws = $utb->_getRows($tables, $fields, $where);
			foreach($draws as $draw)
			{
				$out->startElement('draw');
				$out->writeElement('draw_stamp',   utf8_encode($draw['draw_stamp']));
				$out->writeElement('draw_disci',   $draw['draw_disci']);
				$out->writeElement('draw_catage',   $draw['draw_catage']);
				$out->writeElement('draw_id',   $draw['draw_id']);
				$out->endElement();
			}
		}
		else
		{
			$tables = array('ties', 'rounds', 'draws');
			$where = 'tie_roundid=rund_id';
			$where .= ' AND rund_drawid=draw_id';
			$where .= ' AND draw_eventid='.$eventId;
			$fields = array('draw_stamp', 'draw_disci', 'draw_catage', 'draw_id');
			$draw = $utb->_getRow($tables, $fields, $where);

			$out->startElement('draw');
			$out->writeElement('draw_stamp',   $draw['draw_stamp']);
			$out->writeElement('draw_disci',   $draw['draw_disci']);
			$out->writeElement('draw_catage',  $draw['draw_catage']);
			$out->writeElement('draw_id',      $draw['draw_id']);
			$drawId = $draw['draw_id'];
			$out->endElement();
		}

		// Joueurs
		$tables = array('registration', 'members', 'i2p', 'pairs');
		$where = 'regi_memberid=mber_id AND regi_eventid='.$eventId;
		$where .= ' AND regi_type=' . WBS_PLAYER;
		$where .= ' AND i2p_regiid=regi_id';
		$where .= ' AND i2p_pairid=pair_id';
		$where .= ' AND pair_disci='.WBS_SINGLE;
		$where .= ' ORDER BY mber_secondname,mber_firstname';
		$fields = array('regi_id', 'mber_licence', 'mber_firstname', 'mber_secondname', 'mber_sexe', 'mber_born', 'pair_drawid');
		$players = $utb->_getRows($tables, $fields, $where);
		$oGroup = new objGroup();
		foreach($players as $player)
		{
			if ($event['evnt_type'] == WBS_EVENT_INDIVIDUAL && $player['pair_drawid'] < 1) continue;
			$out->startElement('player');
			$out->writeElement('play_license',   $player['mber_licence']);
			$out->writeElement('play_famname',   utf8_encode($player['mber_secondname']));
			$out->writeElement('play_firstname', utf8_encode($player['mber_firstname']));
			$out->writeElement('play_gender',    $player['mber_sexe']);
			$out->writeElement('play_born',      $player['mber_born']);
			if ($event['evnt_type'] == WBS_EVENT_INDIVIDUAL)
			$out->writeElement('play_drawid', $player['pair_drawid']);
			else
			$out->writeElement('play_drawid', $drawId);
			$out->writeElement('play_id',        $player['regi_id']);

			// Place finale du joueur
			if ($event['evnt_type'] == WBS_EVENT_INDIVIDUAL)
			{
				$finalplace = 0;

				// Cas des poules
				$fields = array('i2p_regiid', 'rund_stamp', 't2r_rank', 'rund_rge', 'rund_drawid', 'rund_group');
				$tables = array('i2p', 't2r', 'rounds', 'draws');
				$where = "t2r_roundId = rund_id".
					" AND i2p_regiid=". $player['regi_id'] .
					" AND rund_type=".WBS_ROUND_GROUP.
					" AND rund_rge > 0".
					" AND i2p_pairid=t2r_pairid";
				$res = $utb->_selectFirst($tables, $fields, $where);
				if ( !empty($res) )
				{
					$group = $oGroup->getGroup($res['rund_drawid'], $res['rund_group']);
					$numGroup = ord($res['rund_stamp']) - ord('A');
					$place = $res['rund_rge'] + $res['t2r_rank'] -1;
					while(($numGroup-- > 0) && ($group['nb3']-->0)) $place+=3;
					while(($numGroup-- > 0) && ($group['nb4']-->0)) $place+=4;
					while(($numGroup-- > 0) && ($group['nb4']-->0)) $place+=5;
					$finalplace= $place;
				}
				// Cas d'un tableau ecrase le resultat poule
				$tables = array('i2p', 'pairs', 'p2m', 'matchs', 'ties', 'rounds');
				$where = 'i2p_pairid=pair_id';
				$where .= ' AND p2m_pairid=pair_id';
				$where .= ' AND p2m_matchid=mtch_id';
				$where .= ' AND mtch_tieid=tie_id';
				$where .= ' AND tie_roundid=rund_id';
				$where .= ' AND i2p_regiid=' . $player['regi_id'];
				$where .= ' AND rund_rge > 0';
				$where .= ' AND tie_posRound = 0';
				$where .= ' AND (rund_type =282 OR rund_type =283  OR rund_type =284 OR rund_type =285)';
				$fields = array('rund_rge', 'p2m_result');
				$res = $utb->_selectFirst($tables, $fields, $where);
				if ( !empty($res) )
				{
					$finalplace = $res['rund_rge'];
					if ($res['p2m_result'] >= WBS_RES_LOOSE) $finalplace++;
				}
				$out->writeElement('finalplace', $finalplace);
			}
			else $out->writeElement('finalplace', 0);

			// Matchs gagnes du joueur
			$tables = array('i2p', 'p2m' , 'matchs', 'ties', 'rounds');
			$where = 'i2p_pairid=p2m_pairid';
			$where .= ' AND p2m_matchid=mtch_id';
			$where .= ' AND mtch_tieid=tie_id';
			$where .= ' AND mtch_discipline<=' . WBS_XD;
			$where .= ' AND tie_roundid=rund_id';
			$where .= ' AND (p2m_result=' . WBS_RES_WIN;
			$where .= ' OR p2m_result=' . WBS_RES_WINAB . ')';
			$where .= ' AND i2p_regiid=' . $player['regi_id'];
			$fields = array('mtch_id', 'tie_posRound', 'tie_schedule', 'p2m_pairid', 'rund_drawid');
			$matchs = $utb->_getRows($tables, $fields, $where);
			foreach($matchs as $match)
			{
				/// adversaire
				$tables = array('i2p', 'p2m');
				$where = 'i2p_pairid=p2m_pairid';
				$where .= ' AND p2m_matchid=' . $match['mtch_id'];
				$where .= ' AND p2m_pairid !=' . $match['p2m_pairid'];
				$fields = array('i2p_regiid');
				$regiId = $utb->_selectFirst($tables, $fields, $where);

				$out->startElement('match');
				if ($event['evnt_type'] == WBS_EVENT_INDIVIDUAL)
				{
					$out->writeElement('mtch_date', $event['evnt_lastday']);
					$out->writeElement('mtch_drawid', $match['rund_drawid']);
				}
				else
				{
					$out->writeElement('mtch_date', substr($match['tie_schedule'], 0, 10));
					$out->writeElement('mtch_drawid', $drawId);
				}
				$out->writeElement('mtch_versusid', $regiId);
				$out->endElement(); // Fin match
			}
			$out->endElement(); // Fin player
		}

		//fermeture du fichier
		$out->endElement();$out->text("\n"); // event
		$out->endDocument();

		// Preparer un fichier zip contenant le fichier genere
		$zip = new ZipArchive();
		$zipfilename = $path . "/$file.zip";
		if ($zip->open($zipfilename, ZIPARCHIVE::CREATE)!==TRUE) {   exit("Impossible d'ouvrir <$filename>\n"); }
		$filename = $path . "/$file.xml";
		$zip->addFile($filename, "$file.xml");
		$zip->close();
		@unlink($filename);

		// Stream the file to the client
		header("Content-Type: application/zip");
		header("Content-Length: " . filesize($zipfilename));
		header("Content-Disposition: attachment; filename=\"$file.zip\"");
		readfile($zipfilename);
		unlink($zipfilename);
		return false;
	}

	// {{{ displayExport()
	/**
	 * display the export form
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function displayExport($err='')
	{
		$dt = $this->_dt;
		$ut = new utils();

		$ute = new utevent();
		$event = $ute->getEvent(utvars::getEventId());

		// Create the page
		$kdiv =& $this->_displayHead('itExport');

		$kdiv2 = & $kdiv->addDiv('divFede', 'blkData');
		$kdiv2->addMsg('tFfbaExport', '', 'kTitre');
		$kdiv2->addMsg('msgFfbaExport');
		$items = array();
		$isSquash = $ut->getParam('issquash', false);
		if (!utvars::isTeamEvent() &&
		(is_null($event['evnt_numauto']) ||
		trim($event['evnt_numauto']) == ''))
		{
			$eventId = utvars::getEventId();
			$kdiv2->addWng('msgNeedNumAuto');
			$items['btnModify']  = array(KAF_NEWWIN, 'events', KID_EDIT,
			$eventId, 500, 500);

		}
		else if (!utvars::isTeamEvent() && !$isSquash && $dt->checkMatch())
		{
			$kdiv2->addWng('msgCheckMatch');
		}
		else
		{
			if ($isSquash) $items['btnFfba'] = array(KAF_UPLOAD, 'export', EXPORT_SQUASHRANK);
			else $items['btnFfba'] = array(KAF_UPLOAD, 'dbf', WBS_ACT_DBF);
		}
		$kdiv2->addMenu('menuFFba', $items, -1, 'classMenuBtn');

		$kdiv2->addMsg('msgFfbaArchive');
		$items = array();
		$items['btnArchive'] = array(KAF_UPLOAD, 'dbf', DBF_ARCHIVES);
		$kdiv2->addMenu('menuArchive', $items, -1, 'classMenuBtn');

		$kdiv2 = & $kdiv->addDiv('divBadnet', 'blkData');
		$kdiv2->addMsg('tBadnetExport', '', 'kTitre');
		$kdiv2->addMsg('msgBadnetExport');
		$items = array();
		$items['btnBadnet'] = array(KAF_UPLOAD, 'export', EXPORT_BADNET);
		$kdiv2->addMenu('menuBadnet', $items, -1, 'classMenuBtn');

		$kdiv->addDiv('break', 'blkNewPage');
		$this->_utpage->display();
		exit;
	}
	//}}}

	// {{{ _listEvents()
	/**
	 * List the events of external badnet site
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function _listEvents()
	{
		require_once "utils/utservices.php";
		$host  = kform::getInput('site', "http://www.badnet.org/badnet");
		$login = kform::getInput('user');
		$pwd   = md5(kform::getInput('password', 'none'));
		$season = kform::getInput('season');

		// Liste des tournois pour un admministrateur
		$serv = new utservices($host);
		$events = $serv->getAdmEventList($login, $pwd, 'evnt_season='.$season);
		if(isset($events['errMsg']))
		$this->displayImport('msgNoEventAvailable');
		$lines = array();
		foreach($events as $event)
		$lines[$event['evnt_id']] = $event['evnt_name'];
		unset($serv);

		// Create the page
		$content =& $this->_displayHead('itImport');

		$kform =& $content->addForm('fImport', "export", IMPORT_SELECT_EVENT);
		$kform->addMsg('tSelectEvent', '', 'kTitre');

		$kcombo = & $kform->addCombo('eventList',  $lines, reset($lines));
		$kcombo->setLength(10);

		$kform->addHide('site',  kform::getInput('site'));
		$kform->addHide('user',  kform::getInput('user'));
		$kform->addHide('password',  kform::getInput('password'));

		//$kform->addBtn("btnStart", KAF_NEWWIN, 'export', IMPORT_SELECT_EVENT, 0, 300, 300);
		$kform->addBtn("btnStart", KAF_SUBMIT);
		$this->_utpage->display();
	}
	//}}}

	// {{{ _selectEvent()
	/**
	 * Extract the files from the archive
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function _selectEvent()
	{
		// Construct the url
		$url = kform::getInput('site', "http://www.demo.org/");
		$url .= "/src/index.php?kaid=".WBS_CNX_DIST;
		$url .= "&uid=".kform::getInput('user');
		$url .= "&puid=".kform::getInput('password');
		$url .= "&kpid=cnx";
		$url .= "&eventId=".kform::getInput('eventList');
		$url .= "&pageId=export"."&actionId=".EXPORT_SEND_EVENT;

		$params['url'] = kform::getInput('site');
		$params['uid'] = kform::getInput('user');
		$params['puid'] = kform::getInput('password');
		$params['eventId'] = kform::getInput('eventList');

		$eventId = utvars::getEventId();
		$userId = utvars::getUserId();
		$fileName = EXPORT_PATH."{$eventId}_{$userId}_import.zip";
		$fdi = fopen($url, 'r');
		$fdo = fopen($fileName, 'w');
		while ($buf = fread($fdi, 4096)) fwrite($fdo, $buf);
		fclose($fdi);
		fclose($fdo);
		chmod($fileName, 0777);

		// Confirm the importation
		$this->_prepareFiles($fileName);
	}
	// }}}


	// {{{ _prepareFiles()
	/**
	 * Extract the files from the archive
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function _prepareFiles($fileName = '')
	{
		// Construct the file name
		$eventId = utvars::getEventId();
		$userId = utvars::getUserId();

		if ($fileName === '')
		{
	  		$fileObj = kform::getInput('importFile', NULL);
	  		if (is_array($fileObj)) $fileName = $fileObj['tmp_name'];
	  		else $fileName = $fileObj;
		}
		// Uncompressed the archive
		require_once "pclzip/pclzip.lib.php";
		$zip = new pclZip($fileName);

		$destPath = EXPORT_PATH;
		$options = PCLZIP_OPT_PATH;
		$res = $zip->extract($options, $destPath);
		if ($res <= 0) $this->displayImport("msgBadFile");

		// Get the list the the files
		$filesName = array();
		$files = @$zip->listContent();
		$nbFile = count($files);
		foreach($files as $file) chmod(EXPORT_PATH.$file['stored_filename'], 0777);

		$file = reset($files);
		$baseName = substr($file['stored_filename'], 0,
		strrpos($file['stored_filename'], '_'));

		// Confirm the exportation
		$this->_confirmEvent(EXPORT_PATH.$baseName, $nbFile);
	}
	// }}}

	// {{{ _confirmEvent()
	/**
	 * Display event's data for confirmation
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function _confirmEvent($baseName, $nbFile)
	{
		// Construct the file name
		require_once "xml_parser_event.php";
		$ep = new eventParser();

		$fileName = "{$baseName}_1.xml";
		$fd = $ep->setInputFile($fileName);
		if (PEAR::isError($fd)) $this->displayImport($fd->getMessage().":$fileName");

		$ep->setMode('func');
		$err = $ep->parse();
		if (PEAR::iserror($err))
		{
	  		echo $err->getMessage();
		}
		$event = $ep->getEvent();
		if (is_null($event)) $this->displayImport('msgBadFile');
		else
		{
	  		// Create the page
	  		$content =& $this->_displayHead('itImport');
	  		if ($event['evnt_type'] == WBS_EVENT_INDIVIDUAL || !$this->_ut->getParam('limitedUsage')
	  		   || (utvars::_getSessionVar('userAuth') == WBS_AUTH_ADMIN) )
	  		{
	  			$kdiv =& $content->addDiv('divEvent', 'blkData');
	  			$kdiv->addMsg('tEventDetail', '', 'kTitre');
	  			$kdiv->addInfo('evntName', $event['evnt_name']);
	  			$kdiv->addInfo('evntDate', $event['evnt_date']);
	  			$kdiv->addInfo('evntPlace', $event['evnt_place']);
	  			$kdiv->addInfo('evntOrganizer', $event['evnt_organizer']);
		  		$date = new utdate();
	  			if (isset($event['evnt_lastupdate']))
	  			{
	  				$date->setIsoDateTime($event['evnt_lastupdate']);
	  				$kdiv->addInfo('evntUpdt', $date->getDateTime());
	  			}
	  			else $kdiv->addInfo('evntUpdt', '???');
	  			$date->setIsoDateTime($event['dateExport']);
	  			$kdiv->addInfo('exportDate', $date->getDateTime());
		    	$kdiv->addInfo('dateRef', $ep->getDateRef());
	  			$kdiv->addInfo('version', $ep->getVersion());
	  			$kform =& $kdiv->addForm('fImport', "export", IMPORT_START);
	  			$kform->addHide('baseName',  $baseName);
	  			$kform->addHide('nbFile',  $nbFile);
	  			if (utvars::getAuthLevel() == WBS_AUTH_ADMIN) $kform->addCheck('purge');
	  			$items = array();
	  			$items['btnStart'] = array(KAF_NEWWIN, 'export', IMPORT_START,0, 400, 200);
	  			$kdiv->addMenu('menuGo', $items, -1, 'classMenuBtn');
	  			$content->addDiv('breakc', 'blkNewPage');
	  		}
	  		else
	  		{
	  			$content->addErr('msgNoTeamImport');
	  		}
	  		$this->_utpage->display();
		}
	}
	//}}}

	// {{{ _startImport()
	/**
	 * Premiere etape de l'omportation
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function _startImport()
	{
		// Import du premier fichier
		$baseName = kform::getInput('baseName');
		$nbFile   = kform::getInput('nbFile');
		$utd= new utdate();
		$start = $utd->getMicroTime();
		if (!is_null(kform::getInput('purge', null)))
		{
	  require_once "utils/utevent.php";
	  $ute = new utevent();
	  $ute->emptyEvent(utvars::getEventId());
	  $this->log("Purge avant import\n");
		}
		$this->log("D�but de traitement: $start\n");
		$this->_import($baseName, 1, $nbFile);
	}
	// }}}

	// {{{ _import()
	/**
	 * Importation d'un fichier
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function _import($baseName, $numFile, $nbFile)
	{
		$fileName = "{$baseName}_{$numFile}.xml";
		if (!is_file($fileName))
		{
	  echo "Terminé mais fichier de fin manquant!";
	  exit;
		}

		// Read the file for event data
		require_once "xml_parser_event.php";
		$ep = new eventParser();
		$fd = $ep->setInputFile($fileName);
		if (PEAR::isError($fd)) $this->displayImport($fd->getMessage());
		$ep->setMode('func');
		$err = $ep->parse();
		if (PEAR::iserror($err)) $this->displayImport($err->getMessage());
		$meta = $ep->getMeta();

		// Import it
		//      $this->_wait("{$meta['cmt']} {$fileName}");
		$this->_wait("{$numFile}:{$meta['cmt']}");
		$this->log("fichier n° $numFile; {$meta['cmt']} :");
		$utd = new utdate();
		$start = $utd->getMicroTime();
		
		// importation des données du tournoi
		if ($numFile == 1)
		{
			require_once "xml_parser_event.php";
			$ep = new eventParser();

			$fd = $ep->setInputFile($fileName);
			if (PEAR::isError($fd))
			{
	  			echo "Erreur na! $fileName";
	  			return;
			}
			$ep->setMode('func');
			$err = $ep->parse();
			if (pear::iserror($err))
			{
	  			echo $err->getMessage();
			}
			$event = $ep->getEvent();
			unset($event['dateExport']);
			$this->_dt->updateEvent($event, true);
		}
		
		
		switch($meta['content'])
		{
			case CONTENT_TEAM:
				$this->importTeams($fileName);
				break;
			case CONTENT_REGI:
				$this->importRegis($fileName);
				break;
			case CONTENT_DRAW:
				$this->importDraws($fileName);
				break;
			case CONTENT_T2T:
				$this->importT2t($fileName);
				break;
			case CONTENT_PAIR:
				$this->importPairs($fileName);
				break;
			case CONTENT_T2R:
				$this->importT2r($fileName);
				break;
			case CONTENT_P2M:
				$this->importP2m($fileName);
				break;
			case CONTENT_ACCOUNTS:
				$this->_importAccounts($fileName);
				break;
			case CONTENT_ACCOUNTSTEAMS:
				$this->_importAccountsTeams($fileName);
				break;
			case CONTENT_ACCOUNTSREGIS:
				$this->_importAccountsRegis($fileName);
				break;
			case CONTENT_ITEMS:
				$this->_importItems($fileName);
				break;
			case CONTENT_POSTIT:
				$this->_importPostits($fileName);
				break;
	  case CONTENT_END:
	  	$end = $utd->getMicroTime();
	  	$this->log("Fin de traitement: $end\n");
	  	$this->_dt->setDateRef($ep->getImportDate());
	  	// Mise a jour des champs classement de i2p
	  	$this->_dt->updatei2p();
	  	unlink($fileName);
	  	echo "Import terminé avec succés !";
	  	$page = new utPage('none');
	  	$page->close(true, 'events', EVNT_MAIN);
	  	exit;
	  	break;
		}
		$end = $utd->getMicroTime();
		$delay=$end-$start;
		$this->log("$delay\n");


		unlink($fileName);
		// Fichier suivant
		$numFile++;
		$this->_load($baseName, $numFile, $nbFile);
	}
	// }}}

	// {{{ importPostits()
	/**
	* Import postits
	*
	* @access public
	* @param string $err Error message
	* @return void
	*/
	function _importPostits($fileName)
	{
		// Import the draws
		require_once "xml_parser_postits.php";
		$ep = new postitsParser();
		$fd = $ep->setInputFile($fileName);
		$ep->setMode('func');
		$err = $ep->parse();
		if (pear::iserror($err))
		echo $err->getMessage();
	}
	//}}}

	// {{{ importItems()
	/**
	 * Import commands
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function _importItems($fileName)
	{
		// Import the draws
		require_once "xml_parser_items.php";
		$ep = new itemsParser();
		$fd = $ep->setInputFile($fileName);
		$ep->setMode('func');
		$err = $ep->parse();
		if (pear::iserror($err))
		echo $err->getMessage();
	}
	//}}}

	// {{{ importAccounts()
	/**
	 * Import file
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function _importAccounts($fileName)
	{
		// Import the draws
		require_once "xml_parser_accounts.php";
		$ep = new accountsParser();
		$fd = $ep->setInputFile($fileName);
		$ep->setMode('func');
		$err = $ep->parse();
		if (pear::iserror($err))
		echo $err->getMessage();
	}
	//}}}

	// {{{ importAccountsTeams()
	/**
	 * Import file
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function _importAccountsTeams($fileName)
	{
		// Import the draws
		require_once "xml_parser_accounts.php";
		$ep = new accountsRelParser();
		$fd = $ep->setInputFile($fileName);
		$ep->setMode('func');
		$err = $ep->parse();
		if (pear::iserror($err))
		echo $err->getMessage();
	}
	//}}}

	// {{{ importAccountsRegis()
	/**
	 * Import file
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function _importAccountsRegis($fileName)
	{
		// Import the draws
		require_once "xml_parser_accounts.php";
		$ep = new accountsRelParser();
		$fd = $ep->setInputFile($fileName);
		$ep->setMode('func');
		$err = $ep->parse();
		if (pear::iserror($err))
		echo $err->getMessage();
	}
	//}}}

	// {{{ importP2m()
	/**
	 * Import matches
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function importP2m($fileP2mName)
	{
		// Import the draws
		require_once "xml_parser_p2m.php";
		$ep = new p2mParser(is_null($this->_dt->getDateRef()));
		$fd = $ep->setInputFile($fileP2mName);
		$ep->setMode('func');
		$err = $ep->parse();
		if (pear::iserror($err))
		echo $err->getMessage();
	}
	//}}}

	// {{{ importPairs()
	/**
	 * Import Pairs
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function importPairs($filePairsName)
	{
		// Import the pairs
		require_once "xml_parser_pairs.php";
		$ep = new pairsParser();
		$fd = $ep->setInputFile($filePairsName);
		$ep->setMode('func');
		$err = $ep->parse();
		if (pear::iserror($err))
		{
	  echo $err->getMessage();
		}
	}
	// }}}

	// {{{ importT2r()
	/**
	 * Import Pairs
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function importT2r($filet2rName)
	{
		// Import relation between pairs and rounds
		require_once "xml_parser_t2r.php";
		$ep = new t2rParser();
		$fd = $ep->setInputFile($filet2rName);
		$ep->setMode('func');
		$err = $ep->parse();
		if (pear::iserror($err))
		{
	  echo $err->getMessage();
		}
		//unlink($filet2rName);

	}
	//}}}

	// {{{ importDraws()
	/**
	 * Import draws, round ties and matchs
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function importDraws($fileDrawsName)
	{
		$dt =& $this->_dt;

		// Import the draws
		require_once "xml_parser_draws.php";
		$rankdef = $dt->getRankDef();
		$ep = new drawsParser($rankdef, is_null($dt->getDateRef()));
		$fd = $ep->setInputFile($fileDrawsName);
		$ep->setMode('func');
		$err = $ep->parse();
		if (pear::iserror($err))
		{
	  echo $err->getMessage();
		}
		//$errs = $ep->getErrs();
		//unlink($fileDrawsName);
	}
	//}}}

	// {{{ importT2t()
	/**
	 * Import draws, round ties and matchs
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function importT2t($filet2tName)
	{
		// Import relation between teams and ties
		require_once "xml_parser_t2t.php";
		$ep = new t2tParser();
		$fd = $ep->setInputFile($filet2tName);
		$ep->setMode('func');
		$err = $ep->parse();
		if (pear::iserror($err))
		{
	  echo $err->getMessage();
		}
		//$errs = $ep->getErrs();
		//unlink($filet2tName);
	}
	//}}}


	// {{{ importTeams()
	/**
	 * Import Association and teams
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function importTeams($fileName)
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;


		// Import the associations of the event
		require_once "xml_parser_teams.php";
		$rankDef = $dt->getRankDef();
		$ep = new teamsParser($rankDef);
		$fd = $ep->setInputFile($fileName);
		$ep->setMode('func');
		$err = $ep->parse();
		if (pear::iserror($err))
		{
	  echo $err->getMessage();
		}
		//unlink($fileName);
	}
	//}}}

	// {{{ importRegis()
	/**
	 * Import entries of members
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function importRegis($fileName)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Import the data of the members
		require_once "xml_parser_regis.php";
		$rankDef = $dt->getRankDef();
		$ep = new regisParser($rankDef);

		$fd = $ep->setInputFile($fileName);
		if (PEAR::isError($fd))
		{
	  echo "Erreur na! $teamFile";
	  return;
		}

		$ep->setMode('func');
		$err = $ep->parse();
		if (pear::iserror($err))
		{
	  echo $err->getMessage();
		}
		//unlink($fileName);
	}
	//}}}


	// {{{ exportT2t()
	/**
	 * Create export file with composition of rounds
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function exportT2t($eventId, $baseName, $numFile)
	{
		$dt = $this->_dt;
		$fileName = "{$baseName}_{$numFile}.xml";
		$numFile++;

		// Create the file
		$fd = $this->exportHead($eventId, $fileName, CONTENT_T2T,
			      'import des rencontres');
		if (!$fd)
		{
	  $err[] = array("","",$fileName,"", "errOpenFile");
	  return $err;
		}

		$results = $dt->getT2t();
		$fields = array('tie', 't2t');
		$this->exportData($fd, $results, $fields);

		fwrite($fd," </event>\n");
		fwrite($fd,"</badnet>\n");
		fclose($fd);
		return $numFile;
	}
	//}}}


	// {{{ exportT2r()
	/**
	 * Create export file with composition of rounds
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function exportT2r($eventId, $baseName, $numFile)
	{
		$dt = $this->_dt;
		$fileName = "{$baseName}_{$numFile}.xml";
		$numFile++;

		// Create the file
		$fd = $this->exportHead($eventId, $fileName, CONTENT_T2R,
			      'import des tours...');
		if (!$fd)
		{
	  $err[] = array("","",$fileName,"", "errOpenFile");
	  return $err;
		}

		if (utvars::isTeamEvent())
		$results = $dt->getTeamT2r();
		else
		$results = $dt->getIndivT2r();
		$fields = array('rund', 't2r');
		$this->exportData($fd, $results, $fields);

		fwrite($fd," </event>\n");
		fwrite($fd,"</badnet>\n");
		fclose($fd);
		return $numFile;
	}
	//}}}

	// {{{ exportP2m()
	/**
	 * Create export file with matchs
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function exportP2m($eventId, $baseName, $numFile)
	{
		$dt = $this->_dt;

		$draws = $dt->getDraws();

		while ($draw = $draws->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $fileName = "{$baseName}_{$numFile}.xml";
	  $numFile++;
	  // Create the file
	  $fd = $this->exportHead($eventId, $fileName, CONTENT_P2M,
				  "import des resultats {$draw['draw_name']}");
	  //				  "import des resultats {$draw['draw_name']}-{$draw['rund_name']}");
	  if (!$fd)
	  {
	  	$err[] = array("","",$fileName,"", "errOpenFile");
	  	return $err;
	  }

	  $results = $dt->getP2m($draw['draw_id']);
	  $fields = array('mtch', 'p2m');
	  $this->exportData($fd, $results, $fields);

	  fwrite($fd," </event>\n");
	  fwrite($fd,"</badnet>\n");
	  fclose($fd);
		}

		return $numFile;
	}
	//}}}

	// {{{ exportDraws()
	/**
	 * Create export file with draws and matchs data
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function exportDraws($eventId, $baseName, $numFile)
	{
		$dt = $this->_dt;

		$draws = $dt->getDraws();

		while ($draw = $draws->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $fileName = "{$baseName}_{$numFile}.xml";
	  $numFile++;

	  // Create the file
	  $fd = $this->exportHead($eventId, $fileName, CONTENT_DRAW,
	  //				  "import du tableau {$draw['draw_name']}-{$draw['rund_name']}",
				  "import du tableau {$draw['draw_name']}");
	  if (!$fd)
	  {
	  	$err[] = array("","",$fileName,"", "errOpenFile");
	  	return $err;
	  }

	  // Write matchs'data
	  if(utvars::isTeamEvent())
	  $matches = $dt->getMatchesTeam($draw['draw_id']);
	  else
	  $matches = $dt->getMatchesIndiv($draw['draw_id']);
	  $fields = array('draw', 'rund', 'tie', 'mtch');
	  $this->exportData($fd, $matches, $fields);
	  fwrite($fd," </event>\n");
	  fwrite($fd,"</badnet>\n");
	  fclose($fd);
		}
		return $numFile;
	}
	//}}}

	/**
	 * Create export file with event, assos and team's data
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function exportFullTeams($eventId, $baseName, $numFile)
	{
		$dt = $this->_dt;

		$teams = $dt->getTeamsId();
		while ($team = $teams->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $fileName = "{$baseName}_{$numFile}.xml";
	  $numFile++;

	  // Create the file
	  $fd = $this->exportHead($eventId, $fileName, CONTENT_TEAM,
				  "Import equipe {$team['team_name']}");
	  if (!$fd)
	  {
	  	$err[] = array("","",$fileName,"", 'errOpenFile');
	  	return $err;
	  }

	  // Write teams'data
	  $players = $dt->getTeam($team['team_id']);
	  $fields = array('asso', 'team', 'mber', 'regi', 'rank', 'i2p', 'pair');
	  $this->exportData($fd, $players, $fields);
	  fwrite($fd," </event>\n");
	  fwrite($fd,"</badnet>\n");
	  fclose($fd);
		}
		return $numFile;
	}

	// {{{ exportPostit()
	/**
	 * Create export file with event, assos and team's data
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function exportPostit($eventId, $baseName, $numFile)
	{
		$dt = $this->_dt;
		$fileName = "{$baseName}_{$numFile}.xml";
		$numFile++;

		// Create the file
		$fd = $this->exportHead($eventId, $fileName, CONTENT_POSTIT,
			      'import des postit');
		if (!$fd)
		{
	  $err[] = array("","",$fileName,"", "errOpenFile");
	  return $err;
		}
		// Write postit data
		$posts = $dt->getPostits();
		$fields = array('psit');
		$this->exportData($fd, $posts, $fields);
		fwrite($fd," </event>\n");
		fwrite($fd,"</badnet>\n");
		fclose($fd);
		return $numFile;
	}
	//}}}

	// {{{ exportAccounts()
	/**
	 * Create export file with event, assos and team's data
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function exportAccounts($eventId, $baseName, $numFile)
	{
		$dt = $this->_dt;
		$fileName = "{$baseName}_{$numFile}.xml";
		$numFile++;

		// Create the file
		$fd = $this->exportHead($eventId, $fileName, CONTENT_ACCOUNTS,
			      'import des comptes');
		if (!$fd)
		{
	  $err[] = array("","",$fileName,"", "errOpenFile");
	  return $err;
		}
		// Write accounts data
		$posts = $dt->getAccounts();
		$fields = array('cunt');
		$this->exportData($fd, $posts, $fields);
		fwrite($fd," </event>\n");
		fwrite($fd,"</badnet>\n");
		fclose($fd);
		return $numFile;
	}
	//}}}

	// {{{ exportAccountsTeams()
	/**
	 * Create export file with acccount and teams
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function exportAccountsTeams($eventId, $baseName, $numFile)
	{
		$dt = $this->_dt;
		$fileName = "{$baseName}_{$numFile}.xml";
		$numFile++;

		// Create the file
		$fd = $this->exportHead($eventId, $fileName, CONTENT_ACCOUNTSTEAMS,
			      'import des relations comptes/equipe');
		if (!$fd)
		{
	  $err[] = array("","",$fileName,"", "errOpenFile");
	  return $err;
		}
		// Write accounts data
		$posts = $dt->getAccountsTeams();
		$fields = array('cunt', 'team');
		$this->exportData($fd, $posts, $fields);
		fwrite($fd," </event>\n");
		fwrite($fd,"</badnet>\n");
		fclose($fd);
		return $numFile;
	}
	//}}}

	// {{{ exportAccountsRegis()
	/**
	 * Create export file with acccount and members
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function exportAccountsRegis($eventId, $baseName, $numFile)
	{
		$dt = $this->_dt;
		$fileName = "{$baseName}_{$numFile}.xml";
		$numFile++;

		// Create the file
		$fd = $this->exportHead($eventId, $fileName, CONTENT_ACCOUNTSREGIS,
			      'import des relations comptes/inscrits');
		if (!$fd)
		{
	  $err[] = array("","",$fileName,"", "errOpenFile");
	  return $err;
		}
		// Write accounts data
		$posts = $dt->getAccountsRegis();
		$fields = array('cunt', 'regi');
		$this->exportData($fd, $posts, $fields);
		fwrite($fd," </event>\n");
		fwrite($fd,"</badnet>\n");
		fclose($fd);
		return $numFile;
	}
	//}}}

	// {{{ exportItems()
	/**
	 * Create export file with event, items and command
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function exportItems($eventId, $baseName, $numFile)
	{
		$dt = $this->_dt;

		$items = $dt->getItems();
		foreach($items as $item)
		{
	  $fileName = "{$baseName}_{$numFile}.xml";
	  $numFile++;

	  // Create the file
	  $fd = $this->exportHead($eventId, $fileName, CONTENT_ITEMS,
				  "import des commandes: {$item['item_name']}");
	  if (!$fd)
	  {
	  	$err[] = array("","",$fileName,"", "errOpenFile");
	  	return $err;
	  }
	  // Write commands data
	  $cmds = $dt->getCommands($item['item_id']);
	  $fields = array('item', 'cmd');
	  $this->exportData($fd, $cmds, $fields);
	  fwrite($fd," </event>\n");
	  fwrite($fd,"</badnet>\n");
	  fclose($fd);
		}
		return $numFile;
	}
	//}}}

	// {{{ exportEnd()
	/**
	 * Create export file with end tag
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function exportEnd($eventId, $baseName, $numFile)
	{
		$dt = $this->_dt;
		$fileName = "{$baseName}_{$numFile}.xml";
		$numFile++;

		// Create the file
		$fd = $this->exportHead($eventId, $fileName, CONTENT_END,
			      "fin de l'import");
		if (!$fd)
		{
	  $err[] = array("","",$fileName,"", "errOpenFile");
	  return $err;
		}
		fwrite($fd," </event>\n");
		fwrite($fd,"</badnet>\n");
		fclose($fd);
		return $numFile;
	}
	//}}}


	// {{{ exportData()
	/**
	 * Create export file with event, assos and team's data
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function exportData($fd, &$datas, &$fields)
	{
		$dt = $this->_dt;

		// Write entries data
		foreach($fields as $field)	$id[$field] = null;

		$nbFields = count($fields);
		$nbDatas = 0;
		while ($data = $datas->fetchRow(DB_FETCHMODE_ASSOC))
		{
			//print_r($data);
			$nbDatas++;
			for ($i=0; $i<$nbFields; $i++)
			{
				$field = $fields[$i];
				$lg = strlen($field);
				// New reg
				if ($id[$field] != $data["{$field}_id"])
				{
					// end of previous reg
					if (!is_null($id[$field]))
					{
						for ($j=$nbFields-1; $j>=$i; $j--)
						{
							if (!is_null($id[$fields[$j]]))
							{
								fwrite($fd,"  </{$fields[$j]}>\n");
								$id[$fields[$j]] = null;
							}
						}
					}
					// Start new reg
					$id[$field] = $data["{$field}_id"];
					fwrite($fd,"\n  <{$field}\n");
					foreach($data as $column=>$value)
					{
						//$value = utf8_encode($value);
						$value = htmlspecialchars($value);
						if (substr($column, 0, $lg) == $field) fwrite($fd,"   $column=\"{$value}\" ");
					}
					fwrite($fd,">\n");
				}
			}
		}
		if ($nbDatas)
		{
			for ($j=$nbFields-1; $j>=0; $j--)
			if (!is_null($id[$fields[$j]]))	fwrite($fd,"  </{$fields[$j]}>\n");
		}
		return;
	}

	// {{{ exportHead()
	/**
	 * Create export file and write generals data
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function exportHead($eventId, $fileName, $content, $cmt)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$ute = new utevent();
		static $event = null;
		static $dbs = null;

		// Create the file
		$fd = fopen($fileName, "w");
		if (!$fd)
		return $fd;
		fwrite($fd,'<?xml version="1.0" encoding="ISO-8859-1"?>');
		/*fwrite($fd,'<?xml version="1.0" encoding="UTF-8"?>');*/

		// Get event data and remove unused field
		if ($event === null)
		$event = $ute->getEvent($eventId);
		$fields = array('ownerId', 'id', 'cre', 'pbl',
		      'del', 'rge', 'nbvisited', 'catage', 'idold');
		foreach($fields as $field)	unset($event['evnt_'.$field]);

		// Write general information
		fwrite($fd, "\n<badnet\n");
		fwrite($fd, " version=\"".$ut->getParam('version')."\"\n");
		$dataBaseId = $ut->getParam('databaseId', -1);
		if ($dataBaseId == -1)
		{
	  $dataBaseId = gmmktime();
	  $ut->setParam('databaseId', $dataBaseId);
		}
		$date = date(DATE_FMT);
		$dateref = $dt->getDateRef($eventId);
		if (is_null($dateref))
		$dateref = $date;
		fwrite($fd, " database=\"{$dataBaseId}\"\n");
		fwrite($fd, " date=\"{$date}\"\n");
		fwrite($fd, " dateref=\"{$dateref}\"\n");
		fwrite($fd, " event=\"{$eventId}\"\n");
		fwrite($fd, " content=\"{$content}\"\n");
		fwrite($fd, ' cmt="' . htmlspecialchars($cmt) . '">');
		//fwrite($fd,">\n");

		// Write event information
		fwrite($fd,"\n <event\n");
		foreach($event as $field=>$value)
		{
			// DBBN - Modif pour retours a la ligne dans les convocs
			fwrite($fd,"  $field=\"" . htmlspecialchars(nl2br($value)) . "\"\n");
		}
		fwrite($fd," >\n");

		if ($dbs === null)
		$dbs = $dt->getDbs($eventId);
		foreach($dbs as $db)
		{
	  fwrite($fd,"\n <db\n");
	  fwrite($fd," db_baseId=\"{$db['db_baseId']}\"\n");
	  fwrite($fd," db_externEventId=\"{$db['db_externEventId']}\"\n");
	  fwrite($fd," db_date=\"{$db['db_date']}\"\n");
	  fwrite($fd," >\n</db>\n");
		}
		fwrite($fd,"\n <db\n");
		fwrite($fd," db_baseId=\"{$dataBaseId}\"\n");
		fwrite($fd," db_externEventId=\"{$eventId}\"\n");
		fwrite($fd," db_date=\"{$date}\"\n");
		fwrite($fd," >\n</db>\n");
		return $fd;
	}
	//}}}

	// {{{ exportEvent()
	/**
	 * Create a zip file with all export file of the event
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function exportEvent($baseFile, $eventId)
	{
		$numFile = 1;
		$numFile = $this->exportAccounts($eventId, $baseFile, $numFile);
		$numFile = $this->exportDraws($eventId, $baseFile, $numFile);
		$numFile = $this->exportFullTeams($eventId, $baseFile, $numFile);
		$numFile = $this->exportAccountsTeams($eventId, $baseFile, $numFile);
		$numFile = $this->exportAccountsRegis($eventId, $baseFile, $numFile);
		$numFile = $this->exportT2t($eventId, $baseFile, $numFile);
		$numFile = $this->exportT2r($eventId, $baseFile, $numFile);
		$numFile = $this->exportP2m($eventId, $baseFile, $numFile);
		$numFile = $this->exportItems($eventId, $baseFile, $numFile);
		$numFile = $this->exportPostit($eventId, $baseFile, $numFile);
		$numFile = $this->exportEnd($eventId, $baseFile, $numFile);

		if (is_null($this->_dt->getDateRef())) $this->_dt->setDateRef(date(DATE_FMT));

		for($i=1; $i<$numFile;$i++)
		$files[] = "{$baseFile}_{$i}.xml";
		$fileName = "{$baseFile}_".EXPORT_FILE_ARCHIVE;
		require_once "pclzip/pclzip.lib.php";
		$zip = new pclZip($fileName);
		@$zip->create($files, PCLZIP_OPT_REMOVE_ALL_PATH);
		chmod($fileName, 0777);

		foreach($files as $file)
		unlink($file);

		return;
	}
	//}}}


	// {{{ displayExportBadNet()
	/**
	 * display the export form
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function displayExportBadNet($err='')
	{
		$dt = $this->_dt;

		$ute = new utevent();

		// Create the page
		$content =& $this->_displayHead('itExport');
		$kform =& $content->addForm('tExportEvent', 'export', EXPORT_SEND_FILE);

		$eventId = utvars::getEventId();
		$event = $ute->getEvent($eventId);

		$userId = utvars::getUserId();
		$infosUser = $dt->getInfosUser($userId);

		if ($err != '')
		$kform->addWng($err);

		$kform->addMsg('msgExportInfos');

		$file = EXPORT_PATH."{$eventId}_{$userId}_".EXPORT_FILE_ARCHIVE;
		if (file_exists($file))
		{
	  $kpage = $this->_utpage->getPage();

	  $subject = $kpage->getLabel("objectExportEvent");
	  $body    = $kpage->getLabel("bodyExportEvent");
	  $body    .= " '{$event['evnt_name']}'\n\n{$infosUser['user_name']}";
	  $kform->addinfo("from",    $infosUser['user_name']);
	  $kform->addEdit("to", kform::getInput('to',""), 45);
	  $kedit =& $kform->addEdit("cc", kform::getInput('cc',
	  $infosUser['user_email']), 45);
	  $kedit->noMandatory();

	  $file = "{$eventId}_{$userId}_".EXPORT_FILE_ARCHIVE;
	  $kedit =& $kform->addinfo("join", $file);
	  $url = dirname($_SERVER['PHP_SELF']).'/../tmp/'.$file;
	  $kedit->setUrl($url);
	  $kform->addEdit("subject", $subject, 45);
	  $kform->addArea("message", $body, 31, 8);


	  $elts=array("from", "to", "cc", "subject", "message", "join"
	  , "btnMail");
	  $kform->addBlock("blkMail", $elts);

	  $kform->addBtn('btnMail', KAF_SUBMIT);
	  //$kform->addBtn('btnMail', KAF_NEWWIN, 'export',
	  //		 EXPORT_SEND_FILE, 0, 400, 200);
	  $elts = array("btnMail");
	  $kform->addBlock("blkBtn", $elts);
		}
		$this->_utpage->display();
		exit;
	}
	//}}}

	// {{{ _sendFile
	/**
	 * Send the file
	 *
	 * @access private
	 * @return void
	 */
	function _sendFile()
	{
		$dt = $this->_dt;

		// Get event data
		require_once "utils/utmail.php";

		$eventId = utvars::getEventId();
		$userId = utvars::getUserId();

		$zipFileName = EXPORT_PATH."{$eventId}_{$userId}_".EXPORT_FILE_ARCHIVE;

		// Prepare mailer
		$infosUser = $dt->getInfosUser($userId);
		$mailer = new utmail();

		$mailer->subject(kform::getInput('subject'));
		$from = "\"{$infosUser['user_name']}\"<{$infosUser['user_email']}>";
		$mailer->from($from);
		$mailer->cc(kform::getInput('cc'));
		$mailer->body(kform::getInput('message'));
		$mailer->receipt();
		$mailer->addZip($zipFileName);
		// Send the message
		$res =  $mailer->send(kform::getInput('to'));
		if (PEAR::isError($res))
		{
	  $this->displayExportBadNet($res->getMessage());
		}
		$this->displayExportBadNet('msgExportSend');
		exit;
	}
	// }}}

	// {{{ displayImport()
	/**
	 * display the export form
	 *
	 * @access public
	 * @param string $err Error message
	 * @return void
	 */
	function displayImport($err="")
	{
		$dt = $this->_dt;
		// Create the page
		$content =& $this->_displayHead('itImport');

		if ($err != '') $content->addErr($err);
		if ( !utvars::isTeamEvent() || !$this->_ut->getParam('limitedUsage') || (utvars::_getSessionVar('userAuth') == WBS_AUTH_ADMIN))
		{
			$kform =& $content->addForm('fImport', "export", IMPORT_GET_FILES);
			$kform->addMsg('tImportFile', '', 'kTitre');
			$kedit =& $kform->addFile('importFile', '', 30);
			$kedit->setMaxLength(200);
			$kedit->noMandatory();
			$elts = array('importFile');
			$kform->addBlock('blkFile', $elts);
			$kform->addDiv('break', 'blkNewPage');
			$kform->addBtn("btnImport", KAF_SUBMIT);

			$kform =& $content->addForm('fImportc', "export", IMPORT_LIST_EVENTS);
			$kform->addMsg('tImportWeb', '', 'kTitre');
			$ut = new utils();
			$isSquash = $ut->getParam('issquash', false);
			if ($isSquash) $kedit =& $kform->addEdit('site', kform::getInput('site', 'http://www.squashnet.fr'), 30);
			else $kedit =& $kform->addEdit('site', kform::getInput('site', 'http://www.badnet.org/badnet'), 30);
			$kedit->noMandatory();
			$kedit =& $kform->addEdit('user', kform::getInput('user', ''), 30);
			$kedit->noMandatory();
			$kedit =& $kform->addEdit('password', kform::getInput('password', ''), 30);
			$kedit->noMandatory();

			$date = getdate();
			$curSeason = $date['year']-2006;
			if ($date['mon'] >= 8) $curSeason++;
			$fields = array('select' => $curSeason,
		      'link'   => '',
			);
			$seas = $this->getSeasons($fields);
			$kform->addCombo('season', $seas, $curSeason);

			$elts = array('site', 'user', 'password', 'season');
			$kform->addBlock('blkSelect', $elts);

			$kform->addDiv('break2', 'blkNewPage');
			$kform->addBtn("btnConnect", KAF_SUBMIT);
		}
		else
		{
			$content->addErr('msgNoTeamImport');
		}
		$this->_utpage->display();
		exit;
	}
	//}}}

	function getSeasons($param)
	{
		$select = $param['select'];
		$link   = $param['link'];

		if ($select <= 0)
		{
			$date = getdate();
			$select = $date['year']-2006;
			if ($date['mon'] >= 8) $select++;
		}

		$year = 2005;
		for($i=1; $i<10; $i++)
		{
			$start = $year + $i;
			$end   = $year + 1 + $i;
			$sea = array('value' => "{$link}{$i}",
		   'text'  => "{$start}-{$end}");
			if($select == $i) $sea['selected'] = 'selected';
			$seas[] = $sea;
		}
		return $seas;
	}

	// {{{ _displayHead()
	/**
	 * Display header of the page
	 *
	 * @access private
	 * @param array team  info of the team
	 * @return void
	 */
	function & _displayHead($select)
	{
		// Create a new page
		$this->_utpage = new utPage_A('export', true, 'itTransfert');
		$content =& $this->_utpage->getContentDiv();

		// Add a menu for different action
		$kdiv =& $content->addDiv('choix', 'onglet3');
		$items['itExport'] = array(KAF_UPLOAD, 'export', WBS_ACT_EXPORT);
		$items['itImport'] = array(KAF_UPLOAD, 'export', WBS_ACT_IMPORT);
		$items['itHelp']   = array(KAF_NEWWIN, 'help',   WBS_ACT_EXPORT, 0, 500, 400);
		$kdiv->addMenu("menuType", $items, $select);
		$kdiv =& $content->addDiv('register', 'cont3');
		return $kdiv;
	}
	// }}}

	// {{{ _load()
	/**
	 * Recupere la page du site web dans une balise de type textearea
	 *
	 * @access private
	 * @return void
	 */
	function _load($baseName, $numFile, $nbFile)
	{
		$hides['kpid'] = 'export';
		$hides['kaid'] = IMPORT_EVENT;
		$hides['baseName'] = $baseName;
		$hides['numFile']  = $numFile;
		$hides['nbFile']   = $nbFile;
			
		// Affichage de la page qui charge la nouvelle
		echo "<html><head>";

		if ($numFile<500)
		{
	  echo "</head><body  onload=\"document.forms['Url'].submit();\">\n".
	    "<form id=\"Url\" method=\"post\" style=\"{display:none;}\"";
		}
		else
		{
	  echo "</head><body>".
	    "<form id=\"Url\" method=\"post\" \"";      
		}
		if (isset($GLOBALS['PHP_SELF']))
		echo "action=\"$GLOBALS[PHP_SELF]\">\n";
		else
		echo "action=\"".$_SERVER['PHP_SELF']."\">\n";

		foreach($hides as $hide=>$value)
		{
	  echo "<input type=\"hidden\" name=\"$hide\" ";
	  echo "id=\"$hide\" value=\"$value\"  />\n";
		}
		echo "<input type=\"submit\" />\n";
		echo "</body></html>";
		exit();
	}
	// }}}

	// {{{ _wait()
	/**
	 * Recupere la page du site web dans une balise de type textearea
	 *
	 * @access private
	 * @return void
	 */
	function _wait($msg)
	{
		// Affichage de la page qui charge la nouvelle
		echo "<html><head>";
		echo "</head><body>";
		echo "<p><a href=\"http://www.badnet.org\" class=\"kUrl\" >\n";
		echo "<img alt=\"badnet\" src=\"img/logo/badnet.jpg\" />";
		//      echo "width=\"100\" height=\"45\" />\n";
		echo "</a></p>\n";
		echo "Traitement en cours...$msg";
		echo "</body></html>";
	}
	// }}}

	/**
	 * Log error in file
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function log($msg)
	{
		$path = "../tmp/";
		$file = "{$path}/stats.log";
		$fd = fopen($file, 'a+');
		fwrite($fd, $msg);
		fclose($fd);
	}
	// }}}

}
?>