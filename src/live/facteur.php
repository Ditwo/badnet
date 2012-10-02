<?php
/*****************************************************************************
 !   Module     : live
 !   File       : $Source: /cvsroot/aotb/badnet/src/live/facteur.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.13 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/06 07:59:33 $
 ******************************************************************************/
define("KID_USER", 100);

// Inclusion des utilitaires generaux
//include_once dirname(__FILE__)."/../kpage/kpage.php";
require_once dirname(__FILE__)."/../utils/const.inc";
require_once dirname(__FILE__)."/../utils/utdate.php";
require_once dirname(__FILE__)."/../utils/utimg.php";
require_once dirname(__FILE__)."/facteurservices.php";

/*
 Ceci est le serveur qui gere les connexions aux feuilles de match
 electronique. Il assure :
 - la reception de la definitions des matchs
 - l'emission de ces matchs vers le feuille de match electronique
 - la recption des scores des matchs
 - la generation de fichier web pour l'affichage du score en live
 - l'enregistrement pour chaque match de toutes les informations
 - le transfert des fichier WEB versun site de publication
 */


error_reporting(E_ALL);

define("T_ERR", 0x01);
define("T_WNG", 0x02);
define("T_MSG", 0x04);
define("T_LOG", 0x08);
define("T_DBG", 0x10);


class ftp
{
	private $_ftp  = null;
	private $_host = null;
	private $_port = null;
	private $_user = null;
	private $_pwd  = null;
	private $_dir  = null;
	private $_passive  = false;
	
	public function __construct($aParams)
	{
		$this->_host = $aParams['host'];
		$this->_port = $aParams['port'];
		$this->_user = $aParams['user'];
		$this->_pwd  = $aParams['pwd'];
		$this->_dir  = $aParams['dir'];
		$this->_passive  = $aParams['passive'];
	}

	private function _connect()
	{
		$host = $this->_host;
		if ( empty($host) )
		{
			echo "Erreur ftp. Host non renseigne\n";
			return false;
		}

		$port = $this->_port;
		$user = $this->_user;
		if ($user === '')
		{
			echo "Erreur ftp. User non renseigne\n";
			return false;
		}
		$pwd  = $this->_pwd;
		if ($pwd === '')
		{
			echo "Erreur ftp. Pwd non renseigne\n";
			return false;
		}

		// Connexion avec l'hote
		$ftp = ftp_connect($host, $port, 3);
		if ($ftp === false)
		{
			echo "Erreur connexion ftp. $host:$port\n";
			return false;
		}

		// Login avec l'hote
		$res = ftp_login($ftp, $user, $pwd);
		if ($res === false)
		{
			echo "Erreur login ftp. $user@$host:$pwd\n";
			return false;
		}
		
		if ($this->_passive) ftp_pasv($ftp, true);
		
		// Memorisation de la  connexion
		$this->_ftp = $ftp;
	}

	function put($aSrc, $aDest)
	{
		if ( empty($this->_ftp) ) $this->_connect();
		// Transfert du fichier
		if ($this->_ftp)
		{
			$err = false;
			$res = ftp_put($this->_ftp, "{$this->_dir}{$aDest}", $aSrc, FTP_BINARY);
			if ($res === true) echo "Tranfert OK1. $aSrc\n";
			else
			{
				echo "Tranfert NOK. $aSrc vers {$this->_dir}$aDest\n";
				ftp_quit($this->_ftp);
				$this->_ftp = null;
			}
		}
	}
}


class facteur
{

	// {{{ properties
	/**
	 * Ip adress for connection
	 *
	 * @var     object
	 * @access  private
	 */
	var $_address;

	/**
	 * Port number
	 *
	 * @var     object
	 * @access  private
	 */
	var $_port;

	/**
	 * Table of sockets
	 *
	 * @var     object
	 * @access  private
	 */
	var $_readSockets;

	/**
	 * Path for the selection of the font
	 *
	 * @var     object
	 * @access  private
	 */
	var $_fontPath="chars";

	/**
	 * Main socket for connexion
	 *
	 * @var     object
	 * @access  private
	 */
	var $_master;

	/**
	 * Liste de match en cours
	 *
	 * @var     object
	 * @access  private
	 */
	var $_matchs=array();

	/**
	 * Chemin live du match
	 *
	 * @var     object
	 * @access  private
	 */
	var $_path=null;

	/**
	 * Serveur ftp  pour transferer le live par ftp
	 *
	 * @var     object
	 * @access  private
	 */
	var $_ftp=array();

	/**
	 * Niveau de trace
	 *
	 * @var     object
	 * @access  private
	 */
	var $_level = 13;

	/**
	 * Type du tournoi
	 *
	 * @var     object
	 * @access  private
	 */
	var $_typeEvent = WBS_EVENT_INDIVIDUAL;
	// }}}

	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function facteur()
	{

		// Autorise l'ex�cution infinie du script, 
		// en attente de connexion.
		set_time_limit(0);

		// Active le vidage implicite des buffers de sortie,
		//pour que nous puissions vois ce que nous lisons au
		// fur et a mesure.
		ob_implicit_flush();
	}
	// }}}

	/**
	 * Ecrit un message
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _trace($msg, $level=T_LOG)
	{
		if ($level & $this->_level)
		echo date("H:i:s ")."$msg\n";
	}

	// {{{ _image()
	/**
	 * Connection d'un client
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _image($name, $file)
	{
		$fontPath = "/../img/font/".$this->_fontPath.'/';
		$imgDest = imageCreate(200, 20);
		$xDest = 0;
		//$name=$_GET['id'].$_GET['ids'];
		$nb = strlen($name);
		//echo "name=$name";
		for($i=0; $i<$nb;$i++)
		{
			$imgFile = dirname(__FILE__).$fontPath.ord($name{$i}).".jpg";
			$srcSize = @getimagesize($imgFile);
			if ($srcSize)
			$imgSrc = imageCreateFromjpeg($imgFile);
			else
			{
				$imgFile = dirname(__FILE__).$fontPath.ord($name{$i}).".png";
				$srcSize = @getimagesize($imgFile);
				if ($srcSize)
				$imgSrc = imageCreateFrompng($imgFile);
			}
			if ($srcSize)
			{
				$res = imageCopy($imgDest, $imgSrc, $xDest, 0, 0, 0,
				$srcSize[0], $srcSize[1]);
				$xDest += $srcSize[0];
				imageDestroy($imgSrc);
			}
		}
		$fileDest = $this->_path."../img/$file.png";
		imagepng($imgDest, $fileDest);
		imageDestroy($imgDest);

		$fileSrc = $fileDest;
		$fileDest = "../img/$file.png";
		$this->_putFile($fileSrc, $fileDest);
		return;
	}
	// }}}

	// {{{ connectClient()
	/**
	 * Connection d'un client
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function connectClient()
	{
		$client = socket_accept($this->_master);
		if ($client === false)
		{
			$err = socket_last_error();
			$msg = "socket_accept() failed: " .
			socket_strerror(socket_last_error()) . "\n";
			$this->_trace($msg, T_ERR);
		}
		else
		{
			//socket_set_option($client, SOL_SOCKET,SO_SNDTIMEO, 1);
			if(!socket_set_nonblock($client))
			echo "non block: erreur\n";
			// Rajouter le client dans la liste des socket a surveiller
			$this->_trace("Connexion acceptee $client \n");
			array_push($this->_readSockets, $client);
		}
	}
	// }}}

	// {{{ init()
	/**
	 * Initialisation du serveur
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function init($server, $login, $pwd, $ip, $port)
	{
		$this->_login  = $login;
		$this->_pwd    = md5($pwd);

		$this->_trace("Initialisation du serveur");
		$this->_trace("  ip=$ip:$port");
		// Creation du socket d'attente de connexion
		$this->_trace("   Creation socket de connexion");
		$this->_master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($this->_master === false)
		{
			$msg = "socket_create() failed: reason: " .
			socket_strerror(socket_last_error()) . "\n";
			$this->_trace($msg, T_ERR);
			return false;
		}
		socket_set_option($this->_master, SOL_SOCKET,SO_REUSEADDR, 1);

		$this->_trace("   Bind socket");
		$ret = socket_bind($this->_master, $ip, $port);
		if ($ret === false)
		{
			$msg = "socket_bind() failed:" .
			socket_strerror(socket_last_error()) . "\n";
			$this->_trace($msg, T_ERR);
			return false;
		}
		socket_set_nonblock($this->_master);

		$this->_trace("   Listen socket");
		$ret = socket_listen($this->_master, 5);
		if ($ret < 0)
		{
			$msg = "socket_listen() failed:" .
			socket_strerror(socket_last_error()) . "\n";
			$this->_trace($msg, T_ERR);
			return false;
		}

		// Initialisation du tableau de socket a surveiller
		$this->_trace("   Read socket");
		$this->_readSockets = array($this->_master);

		// Initialisation des parametres de connexion
		// pour la transmission et la mise a jour des resultats
		$this->_uts = new utServices($server, $this->_login, $this->_pwd);
		$this->_paramCnx = $this->_uts->getFacteurData();
		if (isset($this->_paramCnx['errMsg']))
		{
			$this->_trace($this->_paramCnx['errMsg']);
			die();
		}
		if ( empty($this->_paramCnx) )
		{
			$this->_trace('Erreur acces au serveur :' . $server);
			die;
		}
		print_r($this->_paramCnx);

		return true;
	}
	// }}}

	// {{{ setEvent()
	/**
	 * Determination du tournoi de travail
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function setEvent($eventId)
	{
		$this->_eventType = WBS_EVENT_INDIVIDUAL;

		// Creation des dossiers live du tournoi
		$path = dirname(__FILE__)."/../../live";
		if (!is_dir($path))
		{
			@mkdir($path, 0777);
			@chmod($path, 0777);
		}

		$this->_imgPath = $path."/img";
		if (!is_dir($this->_imgPath))
		{
			mkdir($this->_imgPath, 0777);
			chmod($this->_imgPath, 0777);
		}

		$path .= "/$eventId";
		if (!is_dir($path))
		{
			@mkdir($path, 0777);
			@chmod($path, 0777);
		}

		$this->_path = $path.'/';
		$this->_eventId = $eventId;
		$this->_trace("Tournoi pret : $eventId");
		return true;
	}
	// }}}

	// {{{ start()
	/**
	 * Connection d'un client
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function start()
	{
		// Boucle infinie
		$this->_trace("Serveur ready...");
		while (true)
		{
			// Memoriser les sockets a surveiller
			$changedSockets = $this->_readSockets;

			// Surveiller les sockets, 10 ms seulement
			socket_select($changedSockets, $write = NULL,
			$except = NULL, 0, 10);

			// Pour chaque socket
			foreach($changedSockets as $socket)
			{
				// Demande de connexion
				if ($socket == $this->_master)
				$this->connectClient();
				else
				{
					$buffer = socket_read($socket, 2048);
					if (($buffer === false) || ($buffer == ''))
					{
						$index = array_search($socket, $this->_readSockets);
						unset($this->_readSockets[$index]);
						socket_close($socket);
						$this->_trace("Deconnexion. Suppression socket $socket");
					}
					else
					{
						$this->_trace("<-- socket $socket", T_MSG);
						$this->_trace("<-- $buffer", T_MSG);
						$this->_decode($buffer, $socket);
					}
				}
			}// Fin boucle chaque client
		}// Fin boucle infinie
	}
	// }}}


	// {{{ _getMatch
	/**
	 * Retourne la definition du match a partir de son id
	 * (pas de son numero )
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function & _getMatch($matchUniId)
	{
		//if (!isset($this->_matches[$matchUniId]))
		{
			$match = $this->_uts->getMatch($this->_eventId, "{$matchUniId};");
			$this->_matches[$matchUniId] = $match;
		}
		//print_r($this->_matches[$matchUniId]);
		return $this->_matches[$matchUniId];
	}
	// }}}

	// {{{ _sendMatch
	/**
	 * Envoie le match aux feuilles de match electroniques connectees
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _sendMatch($values, $socket)
	{
		$matchUniId = "{$values[3]}";
		$eventId = $values[2];
		if (is_null($this->_path))
		$this->setEvent($eventId);

		$match  = $this->_getMatch($matchUniId);
		$matchDef = "1;{$match['numCourt']};".
      "$eventId;".
      "$matchUniId;".
      "{$match['numMatch']};";
		switch($match['discipline'])
		{
			case 1:
				$matchDef .=  "SH;";
				break;
			case 2:
				$matchDef .=  "SD;";
				break;
			case 3:
				$matchDef .=  "DH;";
				break;
			case 4:
				$matchDef .=  "DD;";
				break;
			case 5:
				$matchDef .=  "Mx;";
				break;
			default:
				$matchDef .=  "SH;";
				break;
		}
		$matchDef .=  $match['serie'].";";
		$matchDef .=  $match['stade'].";";
		$matchDef .=  $match['nbPoint'].";";
		$matchDef .=  $match['nbGame'].";";
		$matchDef .=  $match['nbProl'].";";
		$pairs = $match['pairs'];
		$path = $this->_path."img/";
		foreach($pairs as $pair)
		{
			$nb = 0;
			$matchDef .=  $pair['id'].";";
			$players = $pair['players'];
			foreach($players as $player)
			{
				$matchDef .= $player['id'].";";
				$matchDef .= $player['name'].";";

				// Creation du nom des joueurs en image
				$id = $player['id'];
				if (($id != -1) && !is_file("$path$id.png"))
				$this->_image($player['name'], $id);

				$nb++;
			}
			if ($nb == 1)
			$matchDef .= "0;;";
		}
		$allClients = $this->_readSockets;
		array_shift($allClients);    // remove master
		$index = array_search($socket, $allClients);
		unset($allClients[$index]);
		$this->_trace("--> $matchDef", T_MSG);
		foreach($allClients as $client)
		{
			$this->_trace("--> $matchDef", T_MSG);
			socket_write($client, $matchDef);
		}

		// Creation du dossier live du match
		$path = $this->_path."$matchUniId";
		if (!is_dir($path))
		{
			@mkdir($path, 0777);
			@chmod($path, 0777);
		}
		$this->_writeMatch($match);
	}
	// }}}

	// {{{ _startGame
	/**
	 * Reception de l'annonce de la fin d'un echange
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _startGame($msg, $socket)
	{
		list($idMsg, $numMsg, $numCourt, $matchId, $numSet,
		$numService, $teamg, $jpg, $jig, $teamd, $jpd, $jid,
		$teamServer, $time) = explode(';', $msg);

		$acquit = "2;$numCourt;$matchId;$idMsg;";
		socket_write($socket, $acquit);

		$match = $this->_getMatch($matchId);

		$pairs = $match['pairs'];
		foreach($pairs as $pair)
		{
			$players = $pair['players'];
			$pair["sc$numSet"] = '00';
			foreach($players as $player)
			{
				if (($pair['id'] == $teamServer) &&
				(($player['id'] == $jpg) ||
				($player['id'] == $jpd)))
				$player['serve'] = $numService;
				else
				$player['serve'] = 0;
				$players[$player['id']] = $player;
			}
			$pair['players'] = $players;
			$pairs[$pair['id']] = $pair;
		}
		$match['pairs'] = $pairs;
		$match['numSet'] = $numSet;
		$match['isProlong'] = false;
		$this->_matches[$matchId] = $match;

		// Ecriture du message dans le fichier
		$file = $this->_path."$matchId/set$numSet";
		$fd = @fopen($file, "a+");
		if ($fd)
		{
			fwrite($fd, $msg);
			fwrite($fd, "\n");
			fclose($fd);
		}
		$this->_writeMatch($match);
	}
	// }}}

	// {{{ _endRally
	/**
	 * Reception de l'annonce de la fin d'un echange
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _endRally($msg, $socket)
	{
		list($idMsg, $numMsg, $numCourt, $matchId, $numSet,
		$numService, $pairg, $jpg, $jig, $paird,
		$jpd, $jid, $numServer,
		$scoreD, $scoreG, $time, $score) = explode(';', $msg);

		$acquit = "2;$numCourt;$matchId;$idMsg;";
		socket_write($socket, $acquit);
		echo "fin acquit\n";

		$games = explode(' ', trim($score));
		$nbSet = 0;
		foreach($games as $game)
		{
			$points =  explode('-', $game);
			if (count($points) == 2)
			{
				$nbSet++;
				if(count($games)==2)
				{
					$scd[$nbSet] = $points[0];
					$scg[$nbSet] = $points[1];
				}
				else
				{
					$scg[$nbSet] = $points[0];
					$scd[$nbSet] = $points[1];
				}
			}
		}


		$match  = $this->_getMatch($matchId);
		$pairs = $match['pairs'];
		foreach($pairs as $pair)
		{
			$players = $pair['players'];
			foreach($players as $player)
			{
				$player['serve'] = $player['id']==$numServer?$numService:0;
				$players["_{$player['id']}"] = $player;
				if (($player['id'] == $jpg) || ($player['id'] == $jig))
				{
					for($i=1; $i<=$nbSet;$i++)
					$pair["sc$i"] = $scg[$i];
					$pair["sc$numSet"] = $scoreG;
				}
				else if (($player['id'] == $jpd) || ($player['id'] == $jid))

				{
					for($i=1; $i<=$nbSet;$i++)
					$pair["sc$i"] = $scd[$i];
					$pair["sc$numSet"] = $scoreD;
				}
			}
			$pair['players'] = $players;
			$pairs["_{$pair['id']}"] = $pair;
		}
		$match['pairs'] = $pairs;
		$match['numSet'] = $numSet;
		$this->_matches[$matchId] = $match;

		//print_r($match);
		// Ecriture du message dans le fichier
		$file = $this->_path."$matchId/set$numSet";
		$fd = @fopen($file, "a+");
		if ($fd)
		{
			fwrite($fd, $msg);
			fwrite($fd, "\n");
			fclose($fd);
		}
		$match['numCourt'] = intval($numCourt);
		$this->_writeMatch($match, 1);
	}
	// }}}

	// {{{ _setting
	/**
	 * Reception de l'annonce des prolongations
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _setting($msg, $socket)
	{
		list($idMsg, $numMsg, $numCourt, $matchId, $numSet,
		$maxPoint) = explode(';', $msg);

		$acquit = "2;$numCourt;$matchId;$idMsg;";
		socket_write($socket, $acquit);

		$match  = $this->_getMatch($matchId);

		if ($maxPoint > $match['nbPoint'])
		$match['isProlong'] = true;
		$match['numSet'] = $numSet;
		$this->_matches[$matchId] = $match;

		// Ecriture du message dans le fichier
		$file = $this->_path."$matchId/set$numSet";
		$fd = @fopen($file, "a+");
		if ($fd)
		{
			fwrite($fd, $msg);
			fwrite($fd, "\n");
			fclose($fd);
		}
		$this->_writeMatch($match);
	}
	// }}}


	// {{{ _endMatch
	/**
	 * Reception de l'annonce de la fin d'un echange
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _endMatch($msg, $socket)
	{
		list($idMsg, $numMsg, $numCourt, $matchId, $pairW,
		$pairL, $begin, $end, $isWO, $isAbort, $score) = explode(';', $msg);

		$acquit = "2;$numCourt;$matchId;$idMsg;";
		socket_write($socket, $acquit);

		$match  = $this->_getMatch($matchId);
		// Ecriture du message dans le fichier
		if ($isWO)
		$score = "{$match['nbPoint']}-0 {$match['nbPoint']}-0 WO";
		else if ($isAbort)
		$score .= " Ab";
		$infos["mtch_score"] = ereg_replace('/', '-', $score);
		$utd =new utdate();
		$utd->setTime($begin.":00");
		$infos["mtch_begin"] = $utd->getDateTime();
		$utd->setTime($end.":00");
		$infos["mtch_end"]   = $utd->getDateTime();
		$infos["mtch_status"] = WBS_MATCH_ENDED;
		$infos["mtch_uniId"] = "{$matchId};";
		$infos['winUniId'] = $match['pairs']["_{$pairW}"]['uniid'];
		$infos['loosUniId'] = $match['pairs']["_{$pairL}"]['uniid'];

		if ($this->_eventType == WBS_EVENT_INDIVIDUAL)
		{
			//$this->_popup($numCourt, $infos, $pairW, $pairL);
			$this->_writeMatch($match, 0);
			$res = $this->_uts->updateMatchResult($this->_eventId, $infos);

			// Enregistrement du resultat sur le site distant
			$server = $this->_paramCnx['sync_server'];
			if ($server)
			{
				$user     = $this->_paramCnx['sync_user'];
				$pwd      = $this->_paramCnx['sync_pwd'];
				$eventId  = $this->_paramCnx['sync_event'];
				$uts = new utServices($server, $user, $pwd);
				$uts->updateMatchResult($eventId, $infos);
				unset($uts);
			}
		}
		else
		{
			$pairs = $match['pairs'];
			$pair = $pairs[$pairW];
			$infos["teamuniid1"] = $pair['teamuniid'];
			$pair = $pairs[$pairL];
			$infos["teamuniid1"] = $pair['teamuniid'];
			// l'appel au service comme pour les tournois
			// individuels
			$this->_popup($numCourt, $infos, $pairW, $pairL);
			$this->_uts->updateMatchTeamResult($this->_eventId, $infos);
			 
			// Enregistrement du resultat sur le site distant
			$server = $this->_paramCnx['sync_server'];
			if ($server)
			{
				$user     = $this->_paramCnx['sync_user'];
				$pwd      = $this->_paramCnx['sync_pwd'];
				$eventId  = $this->_paramCnx['sync_event'];
				$uts = new utServices($server, $user, $pwd);
				$uts->updateMatchTeamResult($this->_eventId, $infos);
				unset($uts);
			}
			//$this->_dtm->updateMatchTeam($infos);
		}
	}
	// }}}

	// {{{ _popup
	/**
	 * Ecriture du match pour le live
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _popup($numCourt, $infos, $pairW, $pairL)
	{
		// Ecriture du live dans le fichier
		$file = $this->_path."court".intval($numCourt).".php";
		$fontPath = "../img/font/".$this->_fontPath;
		$iconPath = "../img/icon/";
		$flagPath = "../img/pub/";

		echo "ecriture dans $file\n";
		$this->_trace("ecriture dans $file\n", T_DBG);

		$fd = @fopen($file, "w+");
		if (!$fd) return;

		$path = "../../live/img/";
		$buf = "<html>\n<head>\n<script type=\"text/javascript\">\n<!--\n";
		fwrite($fd, $buf);

		$buf = "parent.frames['court'].document.forms['fCmd'].elements['matchEnd'].value=1;\n";
		fwrite($fd, $buf);
		$msg = "Fin de match court $numCourt";
		$buf = "parent.frames['court'].document.forms['fCmd'].elements['msg'].value='$msg';\n";
		fwrite($fd, $buf);
		/*
		 $ut = new utils();
		 $url = $ut->getParam('synchroUrl');
		 $url .= "?kaid=".WBS_CNX_DIST;
		 $url .= "&kpid=cnx";
		 $url .= "&pageId=matches";
		 $url .= "&actionId=".MTCH_UPDATE_PAIR_SYNCHRO;
		 $url .= "&eventId=".$this->_eventId;

		 $url .= "&mtchId=".$infos['mtch_id'];
		 $url .= "&mtchNum=".$infos['mtch_id'];
		 $url .= "&mtchBegin=".$infos['mtch_begin'];
		 $url .= "&mtchEnd=".$infos['mtch_end'];
		 $url .= "&mtchStatus=".$infos['mtch_status'];
		 $url .= "&mtchScore=".$infos['mtch_score'];
		 $url .= "&winPairId=$pairW";
		 $url .= "&loosPairId=$pairL";
		 $user = $ut->getParam('synchroUser');
		 $pwd  = $ut->getParam('synchroPwd');
		 $url .= "&uid=$user&puid=$pwd";
		 window.open(url, name, param);
		 $name = "update site";

		 $param = "directories=no, menubar=no, toolbar=no,";
		 $param .= "scrollbars=yes, resizable=yes";
		 $param .= ",width=100";
		 $param .= ",height=100"
		 $param .= ",top="+top;
		 $param .= ",left="+left;
		 fwrite($fd, "window.open($url, $name, $param)");

		 */

		$buf = "\n-->\n</script>\n</head>\n<body>\n</body>\n</html>\n";
		fwrite($fd, $buf);
		fclose($fd);

	}
	// }}}

	// {{{ _rest
	/**
	 * Reception de l'annonce de debut de pause
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _rest($msg, $socket)
	{
		list($idMsg, $numMsg, $numCourt, $matchId, $numSet,
		$length) = explode(';', $msg);

		$acquit = "2;$numCourt;$matchId;$idMsg;";
		socket_write($socket, $acquit);

		$match  = $this->_getMatch($matchId);

		// Ecriture du message dans le fichier
		$file = $this->_path."$matchId/set$numSet";
		$fd = @fopen($file, "a+");
		if ($fd)
		{
			fwrite($fd, $msg);
			fwrite($fd, "\n");
			fclose($fd);
		}

		$this->_trace("Pause not traited\n", T_WNG);

	}
	// }}}

	// {{{ _info
	/**
	 * Reception de l'annonce d'un message informatif
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _info($msg, $socket)
	{
		list($idMsg, $numMsg, $numCourt, $matchId, $typeInfo)
		= explode(';', $msg);

		$acquit = "2;$numCourt;$matchId;$idMsg;";
		socket_write($socket, $acquit);

		$this->_trace("Information not traited\n", T_WNG);
	}
	// }}}

	// {{{ _delRally
	/**
	 * Reception de l'annonce de suppression d'un echange
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _delRally($msg, $socket)
	{
		list($idMsg, $numMsg, $numCourt, $matchId, $numSet)
		= explode(';', $msg);

		$acquit = "2;$numCourt;$matchId;$idMsg;";
		socket_write($socket, $acquit);

		// Ecriture du message dans le fichier
		$file = $this->_path."$matchId/set$numSet";
		$fd = @fopen($file, "a+");
		if ($fd)
		{
			fwrite($fd, $msg);
			fwrite($fd, "\n");
			fclose($fd);
		}

	}
	// }}}

	// {{{ _warning
	/**
	 * Reception de l'annonce de avertissement
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _warning($msg, $socket)
	{
		list($idMsg, $numMsg, $numCourt, $matchId, $numSet,
		$color, $numPlayer) = explode(';', $msg);

		$acquit = "2;$numCourt;$matchId;$idMsg;";
		socket_write($socket, $acquit);

		// Ecriture du message dans le fichier
		$file = $this->_path."$matchId/set$numSet";
		$fd = @fopen($file, "a+");
		if ($fd)
		{
			fwrite($fd, $msg);
			fwrite($fd, "\n");
			fclose($fd);
		}

	}
	// }}}

	// {{{ _writeMatch
	/**
	 * Ecriture du match pour le live
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _writeMatchOld($match)
	{
		// Ecriture du live dans le fichier
		$file = $this->_path."court{$match['numCourt']}.php";
		$fontPath = "../img/font/".$this->_fontPath;
		$iconPath = "../img/icon/";
		$logoPath = "../img/logo/";
		$flagPath = "../img/pub/";

		$this->_trace("ecriture dans $file\n", T_DBG);

		$fd = @fopen($file, "w+");
		if (!$fd) return;

		$path = "../../live/img/";
		$buf = "<html>\n<head>\n<script type=\"text/javascript\">\n<!--\n";
		fwrite($fd, $buf);

		if ($this->_paramCnx['live_light'])
		{
			$buf = "function update()\n{\ndoc = parent.frames[\"court\"].document.forms['court'];\n";
			fwrite($fd, $buf);

			$buf = "parent.frames['court'].document.forms['fCmd'].elements['toDisplay'].value=1;\n";
			fwrite($fd, $buf);
			$buf = "parent.frames['court'].document.forms['fCmd'].elements['matchEnd'].value=0;\n";
			fwrite($fd, $buf);

			$pairs = $match['pairs'];
			// Premiere paire
			$pair = $pairs[$match['pairG']];
			$this->_fwrite($fd, 'sc1g', $pair['sc1']);
			$this->_fwrite($fd, 'sc2g', $pair['sc2']);
			$this->_fwrite($fd, 'sc3g', $pair['sc3']);


			$players = $pair['players'];
			$player = $players[$pair['pair']];
			if ($player['serve'] == 0) $serve="";
			else if ($player['serve'] == 1) $serve="*";
			else if ($player['serve'] == 2) $serve="**";

			$this->_fwrite($fd, 'jpg', $player['name']);
			//$this->_fwrite($fd, 'fpg', $player['flag']);
			$this->_fwrite($fd, 'fpg', '');
			$this->_fwrite($fd, 'spg', $serve);

			if (isset($players[$pair['impair']]))
			{
				$player = $players[$pair['impair']];
				if ($player['serve'] == 0) $serve="";
				else if ($player['serve'] == 1) $serve="*";
				else if ($player['serve'] == 2) $serve="**";
				$this->_fwrite($fd, 'jig', $player['name']);
				//$this->_fwrite($fd, 'fig', $player['flag']);
				$this->_fwrite($fd, 'fig', '');
				$this->_fwrite($fd, 'sig', $serve);
			}
			else
			{
				$this->_fwrite($fd, 'jig', '');
				$this->_fwrite($fd, 'fig', '');
				$this->_fwrite($fd, 'sig', '');
			}

			// Deuxieme paire
			$pair = $pairs[$match['pairD']];
			$this->_fwrite($fd, 'sc1d', $pair['sc1']);
			$this->_fwrite($fd, 'sc2d', $pair['sc2']);
			$this->_fwrite($fd, 'sc3d', $pair['sc3']);

			$players = $pair['players'];
			$player = $players[$pair['pair']];
			if ($player['serve'] == 0) $serve="";
			else if ($player['serve'] == 1) $serve="*";
			else if ($player['serve'] == 2) $serve="**";

			$this->_fwrite($fd, 'jpd', $player['name']);
			//$this->_fwrite($fd, 'fpd', $player['flag']);
			$this->_fwrite($fd, 'fpd', '');
			$this->_fwrite($fd, 'spd', $serve);
			if (isset($players[$pair['impair']]))
			{
				$player = $players[$pair['impair']];
				if ($player['serve'] == 0) $serve="";
				else if ($player['serve'] == 1) $serve="*";
				else if ($player['serve'] == 2) $serve="**";
				$this->_fwrite($fd, 'jid', $player['name']);
				//$this->_fwrite($fd, 'fid', $player['flag']);
				$this->_fwrite($fd, 'fid', '');
				$this->_fwrite($fd, 'sid', $serve);
			}
			else
			{
				$this->_fwrite($fd, 'jid', '');
				$this->_fwrite($fd, 'fid', '');
				$this->_fwrite($fd, 'sid', '');
			}

			for ($i=1; $i<$match['nbGame']+1; $i++)
			{
				if ($match['isProlong'] && ($i==$match['numSet']))
				$value = '***';
				else
				$value = '';
				$this->_fwrite($fd, "pro$i", $value);
				 
			}
		}
		else
		{
			$buf = "function update()\n{\ndoc = parent.frames[\"court\"].document;\n";
			fwrite($fd, $buf);

			$buf = "parent.frames['court'].document.forms['fCmd'].elements['toDisplay'].value=1;\n";
			fwrite($fd, $buf);
			$buf = "parent.frames['court'].document.forms['fCmd'].elements['matchEnd'].value=0;\n";
			fwrite($fd, $buf);

			$pairs = $match['pairs'];
			// Premiere paire
			$pair = $pairs[$match['pairG']];
			$this->_frwrite($fd, 'sc1g', "$fontPath/n{$pair['sc1']}.png");
			$this->_frwrite($fd, 'sc2g', "$fontPath/n{$pair['sc2']}.png");
			$this->_frwrite($fd, 'sc3g', "$fontPath/n{$pair['sc3']}.png");


			$players = $pair['players'];
			$player = $players["_{$pair['pair']}"];
			$this->_frwrite($fd, 'jpg', "$path{$player['id']}.png");
			$this->_frwrite($fd, 'fpg', "{$flagPath}{$player['flag']}");
			$this->_frwrite($fd, 'spg', "{$iconPath}s{$player['serve']}.png");

			if (isset($players["_{$pair['impair']}"]))
			{
				$player = $players["_{$pair['impair']}"];
				$this->_frwrite($fd, 'jig', "$path{$player['id']}.png");
				$this->_frwrite($fd, 'fig', "{$flagPath}{$player['flag']}");
				$this->_frwrite($fd, 'sig', "{$iconPath}s{$player['serve']}.png");
			}
			else
			{
				$this->_frwrite($fd, 'jig', "{$iconPath}s0.png");
				$this->_frwrite($fd, 'fig', "{$iconPath}s0.png");
				$this->_frwrite($fd, 'sig', "{$iconPath}s0.png");
			}

			// Deuxieme paire
			$pair = $pairs[$match['pairD']];

			$this->_frwrite($fd, 'sc1d', "$fontPath/n{$pair['sc1']}.png");
			$this->_frwrite($fd, 'sc2d', "$fontPath/n{$pair['sc2']}.png");
			$this->_frwrite($fd, 'sc3d', "$fontPath/n{$pair['sc3']}.png");

			$players = $pair['players'];
			$player = $players["_{$pair['pair']}"];
			$this->_frwrite($fd, 'jpd', "$path{$player['id']}.png");
			$this->_frwrite($fd, 'fpd', "{$flagPath}{$player['flag']}");
			$this->_frwrite($fd, 'spd', "{$iconPath}s{$player['serve']}.png");

			if (isset($players["_{$pair['impair']}"]))
			{
				$player = $players["_{$pair['impair']}"];
				$this->_frwrite($fd, 'jid', "$path{$player['id']}.png");
				$this->_frwrite($fd, 'fid', "{$flagPath}{$player['flag']}");
				$this->_frwrite($fd, 'sid', "{$iconPath}s{$player['serve']}.png");
			}
			else
			{
				$this->_frwrite($fd, 'jid', "{$iconPath}s0.png");
				$this->_frwrite($fd, 'fid', "{$iconPath}s0.png");
				$this->_frwrite($fd, 'sid', "{$iconPath}s0.png");
			}

			for ($i=1; $i<$match['nbGame']+1; $i++)
			{
				if ($match['isProlong'] && ($i==$match['numSet']))
				$value = "{$iconPath}prolo.png";
				else
				$value = "{$iconPath}empty.png";
				$this->_frwrite($fd, "pro$i", $value);
			}
			$value = "{$logoPath}event_{$match['discipline']}.jpg";
			$this->_frwrite($fd, "event", $value);
			$value = "{$logoPath}stage_{$match['stade']}.jpg";
			$this->_frwrite($fd, "stage", $value);


		}
		$buf = "}\n-->\n</script>\n</head>\n<body onload=\"update();\">\n</body>\n</html>\n";
		fwrite($fd, $buf);
		fclose($fd);

		$fileDest = "court{$match['numCourt']}.php";
		$this->_putFile($file, $fileDest);
	}
	// }}}

	// {{{ _writeMatch
	/**
	* Ecriture du match pour le live
	*
	* @access public
	* @param  integer $action  what to do
	* @return void
	*/
	function _writeMatch($match, $play='0')
	{
		// Ecriture du live dans le fichier
		$file = $this->_path."court{$match['numCourt']}.html";
		$fontPath = "../img/font/".$this->_fontPath;
		$iconPath = "../img/icon/";
		$logoPath = "../img/logo/";
		$flagPath = "../img/pub/";

		$this->_trace("ecriture dans $file\n", T_DBG);

		$fd = @fopen($file, "w+");
		if (!$fd) return;

		$path = "../../live/img/";
		$buf = '{';
		fwrite($fd, $buf);
		$pairs = $match['pairs'];
		// Premiere paire
		$pair = $pairs[$match['pairG']];
		fwrite($fd, '"play":"' . $play .'",');
		fwrite($fd, '"sc1g":"' . $pair['sc1'] . '",');
		fwrite($fd, '"sc2g":"' . $pair['sc2'] . '",');
		fwrite($fd, '"sc3g":"' . $pair['sc3'] . '",');

		$players = $pair['players'];
		$player = $players["_{$pair['pair']}"];
		fwrite($fd, '"jpg":"' . $player['id'] . '.png",');
		fwrite($fd, '"fpg":"' . $player['flag'] . '",');
		fwrite($fd, '"spg":"s' . $player['serve'] . '.png",');

		if (isset($players["_{$pair['impair']}"]))
		{
			$player = $players["_{$pair['impair']}"];
			fwrite($fd, '"jig":"' .  $player['id'] . '.png",');
			fwrite($fd, '"fig":"' .  $player['flag'] . '",');
			fwrite($fd, '"sig":"s' .  $player['serve'] . '.png",');
		}
		else
		{
			fwrite($fd, '"jig":"",');
			fwrite($fd, '"fig":"",');
			fwrite($fd, '"sig":"",');
		}

		// Deuxieme paire
		$pair = $pairs[$match['pairD']];

		fwrite($fd, '"sc1d":"' . $pair['sc1'] . '",');
		fwrite($fd, '"sc2d":"' . $pair['sc2'] . '",');
		fwrite($fd, '"sc3d":"' . $pair['sc3'] . '",');

		$players = $pair['players'];
		$player = $players["_{$pair['pair']}"];
		fwrite($fd, '"jpd":"' . $player['id'] . '.png",');
		fwrite($fd, '"fpd":"' . $player['flag'] . '",');
		fwrite($fd, '"spd":"s' . $player['serve'] . '.png",');

		if (isset($players["_{$pair['impair']}"]))
		{
			$player = $players["_{$pair['impair']}"];
			fwrite($fd, '"jid":"' . $player['id'] . '.png",');
			fwrite($fd, '"fid":"' . $player['flag'] . '",');
			fwrite($fd, '"sid":"s' . $player['serve'] . '.png",');
		}
		else
		{
			fwrite($fd, '"jid":"",');
			fwrite($fd, '"fid":"",');
			fwrite($fd, '"sid":"",');
		}

		for ($i=1; $i<$match['nbGame']+1; $i++)
		{
			if ($match['isProlong'] && ($i==$match['numSet']))
			$value = "prolo.png";
			else
			$value = "";
			fwrite($fd, '"pro'.$i . '":"' . $value . '",');
		}
		$value = "event_{$match['discipline']}.jpg";
		fwrite($fd, '"event":"' . $value . '",');
		$value = "stage_{$match['stade']}.jpg";
		fwrite($fd, '"stage":"' . $value . '"');

		$buf = "}";
		fwrite($fd, $buf);
		fclose($fd);

		$fileDest = "court{$match['numCourt']}.html";
		$this->_putFile($file, $fileDest);
	}
	// }}}

	 
	// {{{ _fwrite
	/**
	 * Ecriture de la mise a jour du champ dans le fichier
	 *
	 * @access public
	 * @return void
	 */
	function _fwrite($fd, $name, $value)
	{
		$buf = "if (doc.elements[\"$name\"].value != \"$value\")\n";
		$buf .= "   doc.elements[\"$name\"].value = \"$value\";\n";
		fwrite($fd, $buf);
	}
	//}}}

	// {{{ _frwrite
	/**
	 * Ecriture de la mise a jour du champ dans le fichier
	 *
	 * @access public
	 * @return void
	 */
	function _frwrite($fd, $name, $value)
	{
		$buf = "if (doc.images[\"$name\"].src != \"$value\")\n";
		$buf .= "   doc.images[\"$name\"].src = \"$value\";\n";
		fwrite($fd, $buf);
	}
	//}}}

	/**
	 * Transfert d'un fichier par ftp
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _putFile($aSrc, $aDest)
	{
		// Transfert du fichier
		foreach($this->_ftp as $ftp)
			$ftp->put($aSrc, $aDest);
	}

	/**
	 * jout un ftp 
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function addFtp($aParams)
	{
		echo "Ajout Ftp\n";
		echo "\thost : " . $aParams['host'] . "\n";
		echo "\tport : " . $aParams['port'] . "\n";
		echo "\tuser : " . $aParams['user'] . "\n";
		echo "\tdir  : " . $aParams['dir'] . "\n";
		$this->_ftp[] = new ftp($aParams);
	}
	
	// {{{ _closeCourt
	/**
	 * Suppression du match pour le live
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _closeCourt($numCourt)
	{
		// Ecriture du live dans le fichier
		$file = $this->_path."court{$numCourt}.php";
		$fd = @fopen($file, "w+");
		if ($fd)
		{

			$buf = "<html>\n<head>\n";
			fwrite($fd, $buf);
			$buf = "</head>\n";
			fwrite($fd, $buf);
			$buf = "<body onload=\"parent.window.close();\">\n";
			fwrite($fd, $buf);
			$buf = "</body>\n";
			fwrite($fd, $buf);
			$buf =  "</html>\n";
			fwrite($fd, $buf);
			fclose($fd);
		}

		//$this->_trace("Fermeture :$file");
		//@unlink($file);

	}
	// }}}


	// {{{ _decode
	/**
	 * Decode un message recu par le serveur et appelle le
	 * traitment correspondant
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _decode($buf, $socket)
	{
		$msgs = explode("@", $buf);

		$oldMsg = '';
		foreach($msgs as $msg)
		{
			if ($msg=='') break;
			if ($msg == $oldMsg) continue;
			$oldMsg = $msg;
			$values = explode(";", $msg);
			switch($values[1])
			{
				// Nouveau match
				case 1:
					$this->_sendMatch($values, $socket);
					break;
					// Debut de set
				case 3:
					$this->_startGame($msg, $socket);
					break;
					// fin d'un echange service
				case 4:
					$this->_endRally($msg, $socket);
					break;
					// Resultat de match
				case 5:
					$this->_endMatch($msg, $socket);
					break;
					// Prolongation
				case 6:
					$this->_setting($msg, $socket);
					break;
					// Pause
				case 7:
					$this->_rest($msg, $socket);
					break;
					// Information
				case 8:
					$this->_info($msg, $socket);
					break;
					// Supression d'echange
				case 10:
					$this->_delRally($msg, $socket);
					break;
					// Avertissemement
				case 11:
					$this->_warning($msg, $socket);
					break;
					// Changement du niveau de trace
				case 30:
					echo "level old=".$this->_level.",";
					$this->_level |= $values[2];
					echo "new=".$this->_level."\n";
					break;
					// Choix de la font
				case 31:
					$this->_fontPath = $values[2];
					break;
					// Supression du fichier live d'un terrain
				case 32:
					$this->_closeCourt($values[2]);
					break;
					// Affichage du nombre de client
				case 33:
					$nb = count($this->_readSockets)-1;
					$this->trace("Il y a $nb client(s) connecte(s)");
					break;
				default:
					$this->_trace("Type message inconnu:".$values[0], T_WNG);
					break;
			}
		}
	}
	// }}}
}


$file = 'facteur.ini';
$config = parse_ini_file($file, true);
if ( $config == null)
{
	exit('Impossible de trouver le fichier facteur.ini');
}
$cnx = $config['badnet_serveur'];
$ftps = $config['ftps'];

$a = new facteur();
if ($a->init($cnx['badnet_host'], $cnx['db_user'], $cnx['db_pwd'], $cnx['facteur_host'], $cnx['facteur_port']) )
{
	if ($cnx['event_id'] > 0) $a->setEvent($cnx['event_id']);
	foreach($ftps as $item)
	{
		$ftp = $config[$item];
		if ( $ftp['status'] ) $a->addFtp($ftp);
	}
	$a->start();
}
?>
