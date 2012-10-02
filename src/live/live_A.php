<?php
/*****************************************************************************
 !   Module     : live
 !   File       : $Source: /cvsroot/aotb/badnet/src/live/live_A.php,v $
 !   Version    : $Name: HEAD $
 !   Revision   : $Revision: 1.48 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/20 22:20:08 $
 ******************************************************************************/
require_once "base_A.php";
require_once "live.inc";
require_once "matches/matches.inc";
require_once "utils/utpage_A.php";
require_once "utils/utmatch.php";
require_once "utils/utdraw.php";
require_once "ties/ties.inc";
require_once "schedu/schedu.inc";
require_once "draws/draws.inc";
require_once "pairs/pairs.inc";
require_once "utils/utservices.php";
require_once "utils/objgroup.php";

/**
 * Module de gestion du live : classe administrateur
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class live_A
{

	function numbers()
	{
		putenv('GDFONTPATH=' . "/home/badnet/Public_html/BadmintonNetware/Src/Badnetres/Facteur/Fonts");
		$fontName = 'corbelb';
		$fontName = 'Sugo';
		$font = $fontName.'.ttf';
		$fontSize = 110;
		$width = 4030;
		$height = 250;
		
		// Création de l'image
		$imgDest = imagecreatetruecolor($width, $height);

		// Couleur du texte
		list($r,$v,$b) = explode(';', "252;136;35");
		$color = imagecolorallocate($imgDest, $r, $v, $b);
		
		// Couleur de fond
		list($r,$v,$b) = explode(';', "0;0;0");
		$background = imagecolorallocate($imgDest, $r, $v, $b);
		imagecolortransparent($imgDest, $background);
		
		imagefilledrectangle($imgDest, 0, 0, $width, $height, $background);
		// Ajout du texte
		$widthNumber = 130;
		for ($i=0; $i<31;$i++)
		{
			$bbox = imagettfbbox($fontSize, 0,  $font, "$i");

			// Centrage vertical
			$y = $height;
			$dy = abs($bbox[1] - $bbox[7]);
			$ddy = ($height-$dy)/2;
			$posy = $y - $ddy - $bbox[1];
			
			// Centrage du nombre dans sa case
			$dx = abs($bbox[2] - $bbox[0]);
			$posx = ($widthNumber-$dx)/2;
			
			// Decalage dans l'image
			$posx += ($i*$widthNumber);
			
			imagettftext($imgDest, $fontSize, 0, $posx, $posy, $color, $font, "$i");
		}

		$fileDest = "/home/badnet/Public_html/Ceu2010/Img/chiffre_2_$fontName.png";
		imagepng($imgDest, $fileDest);
		imageDestroy($imgDest);
	}
	
	
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
	function live_A()
	{
		$this->_ut = new utils();
		$this->_dt = new liveBase_A();
		//$this->numbers();
	}
	// }}}

	// {{{ start()
	/**
	 * Start the connexion processus
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function start($page)
	{
		switch ($page)
		{
			case WBS_ACT_LIVE:
				$this->_displayListMatch();
				break;
					
			case LIVE_LIVE_MATCH:
				$matchId = kform::getData();
				$matchUniId = $this->_dt->getMatchUniId($matchId);
				$this->_sendMatchToServer($matchUniId);
				$page = new kpage('none');
				$page->close();
				break;
					
			case LIVE_SELECT_COURT:
				$court = kform::getData();
				$this->_displayListMatch($court);
				break;

			case LIVE_SELECT_ENDED:
				$this->_ut->setPref('cur_displayEnded',	kform::getData());
				$this->_displayListMatch();
				break;
			case LIVE_SELECT_ORDER:
				$this->_ut->setPref('cur_displayOrder',	kform::getData());
				$this->_displayListMatch();
				break;

			case LIVE_TIE_PDF:
				include_once dirname(__FILE__)."/../pdf/pdfties.php";
				$id = kform::getData();
				$pdf = new pdfties();
				$pdf->start();
				$pdf->affichage_declaration($id);
				$pdf->end();
				break;

			case LIVE_PRESENCE_PDF:
				include_once dirname(__FILE__)."/../pdf/pdfties.php";
				$id = kform::getData();
				$pdf = new pdfties();
				$pdf->start();
				$pdf->affichage_presence($id);
				$pdf->end();
				break;

			case LIVE_SET_UMPIRE:
				$id = kform::getData();
				$this->_setUmpire($id);
				break;

			case LIVE_ORDER_MATCH_A:
				$id = kform::getData();
				$this->_displayOrderMatch($id);
				break;

			case LIVE_AUTOORDER_MATCH_A:
				$this->_autoOrderMatch();
				break;

			case LIVE_MOVE_UP_A:
				$this->_updateOrderMatch(-1);
				break;
			case LIVE_MOVE_DOWN_A:
				$this->_updateOrderMatch(1);
				break;
			case LIVE_MOVE_END_A:
				$this->_updateOrderMatch(0);
				break;
					
			case LIVE_DISPLAY_STATUS:
				$this->_displayMatchStatus();
				break;
			case LIVE_UPDATE_STATUS_A:
				$this->_updateMatchStatus();
				break;

			case LIVE_ALONE:
				$this->_displayCourtLive();
				break;

			case LIVE_SEND_SERVER:
				$this->_sendOrderToServer();
				break;

				/* Gestion des arbitres */
			case LIVE_UMPIRE_LOOK:
				$this->_displayUmpires();
				break;
			case LIVE_UMPIRE_EDIT:
				$this->_displayUmpire();
				break;
			case LIVE_UMPIRE_UPDATE:
				$this->_updateUmpire();
				break;

			case LIVE_OPTION_EDIT:
				$this->_displayOptions();
				break;
			case LIVE_OPTION_UPDATE:
				$this->_updateOptions();
				break;
			case LIVE_PALMARES:
				$this->_dt->getPalmares();
				break;
			case LIVE_CLASSE:
				$this->_dt->getClasse();
				break;
			case LIVE_VALIDATE:
				$date  = $this->_ut->getPref('cur_date', -1);
				$place = $this->_ut->getPref('cur_place', -1);
				$this->_dt->validate($date, $place);
				$page = new kpage('none');
				$page->close();
				exit;
				break;
			case KID_PRINT:
				$this->_print();
				exit;
			default:
				echo "live_A->start($page) non autorise <br>";
				exit;
		}
		exit;
	}

	/**
	 * print list of displayed matchs
	 */
	function _print()
	{

		$ut = $this->_ut;
		$date = $ut->getPref('cur_date', '-----');
		$place = $ut->getPref('cur_place', '-----');
		$ended = $ut->getPref('cur_displayEnded', false);
		$this->_dt->printMatchList($date, $place, $ended);
	}

	/**
	 * Register the options
	 */
	function _updateOptions()
	{
		$ut = $this->_ut;

		$ut->setPref('cur_step', kform::getInput('stepList', '-----'));
		$ut->setPref('cur_nbCourt', kform::getInput('nbCourt', 5));
		$ut->setPref('cur_rest', kform::getInput('rest', 20));
		$ut->setPref('cur_training', kform::getInput('training', 3));
		$ut->setPref('cur_umpiring', kform::getInput('umpiring', WBS_UMPIRE_LOOSER));
		$ut->setPref('reload_live', kform::getInput('reload', 0));
		//$ut->setPref('printing', kform::getInput('chkPrint', 0));

		$page = new kpage('none');
		$page->close();
		exit;
	}

	/**
	 * Get data of an umpire and save them in the database
	 */
	function _updateUmpire()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$regiId = kform::getInput('regiId');
		$umpire['umpi_id'] = kform::getInput('umpiId');
		$umpire['umpi_court'] = kform::getInput('courtNumber');
		$umpire['umpi_function'] = kform::getInput('umpireFunction');
		$umpire['umpi_order'] = kform::getInput('umpireRank');
		$umpire['umpi_currentcourt'] = kform::getInput('currentCourt');

		$dt->updateUmpire($regiId, $umpire);

		$page = new kpage('none');
		$page->close();
		exit;
	}

	/**
	 * Display a page with the options for match display
	 */
	function _displayOptions()
	{

		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage('live');
		$content =& $utpage->getPage();
		$kform =& $content->addForm('tOptionEdit', 'live', LIVE_OPTION_UPDATE);

		$steps = $dt->getTiesInfo('tie_step');
		$stepSel = $ut->getPref('cur_step', '-----');
		$kcombo =& $kform->addCombo('stepList', $steps, $stepSel);

		$kform->addEdit('nbCourt', $ut->getPref('cur_nbCourt', 5), 3);
		$kform->addEdit('rest', $ut->getPref('cur_rest', 20), 2);
		$kform->addEdit('training', $ut->getPref('cur_training', 3), 2);

		$status = $ut->getPref('cur_umpiring', kform::getInput('status', WBS_UMPIRE_LOOSER));
		for ($i=WBS_UMPIRE_OFFICIAL; $i<=WBS_UMPIRE_LOOSER;$i++) $choice[$i] = $ut->getLabel($i);
		$kcombo =& $kform->addCombo('umpiring', $choice, $choice[$status]);
		//$kform->addCheck('chkPrint', $ut->getPref('printing', 0));

		$reloads = array(0=>'Jamais', 30=>'30 s', 60=>'1mn', 120=>"2mn", 300=>"5 mn");
		$reload = $ut->getPref('reload_live', 0);
		$kcombo =& $kform->addCombo('reload', $reloads, $reload);
		
		$elts = array('placeList', 'dateList', 'stepList',
		    'training', 'rest', 'nbCourt', 'umpiring', 'reload', 'chkPrint');
		$kform->addBlock('blkOption', $elts);

		$kform->addBtn('btnRegister', KAF_SUBMIT);
		$kform->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$kform->addBlock('blkBtn', $elts);

		$utpage->display();
		exit;
	}

	/**
	 * Display a page with the detail of an umpire
	 */
	function _displayUmpire()
	{

		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage('live');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fLive', 'live', LIVE_UMPIRE_UPDATE);
		$form->setTitle('tUmpireEdit');

		$regiId = kform::getData();
		$umpire = $dt->getUmpire($regiId);
		$form->addHide('regiId', $regiId);
		$form->addHide('umpiId', $umpire['umpi_id']);

		$form->addInfo($umpire['regi_longName']);

		$nbCourt = $ut->getPref('cur_nbcourt', 5);
		for ($i=0; $i <= $nbCourt; $i++)
		$courts[$i] = $i;;
		$court = kform::getInput('courtNumber', 1);
		$kcombo =& $form->addCombo('courtNumber', $courts, $umpire['umpi_court']);

		$func[WBS_UMPIRE_UMPIRE]  = $ut->getLabel(WBS_UMPIRE_UMPIRE);
		$func[WBS_UMPIRE_SERVICE] = $ut->getLabel(WBS_UMPIRE_SERVICE);
		$func[WBS_UMPIRE_REST]    = $ut->getLabel(WBS_UMPIRE_REST);
		$form->addCombo('umpireFunction', $func, $func[$umpire['umpi_function']]);
		$form->addEdit('umpireRank', $umpire['umpi_order'], 3);
		$form->addEdit('currentCourt', $umpire['umpi_currentcourt'], 3);

		$elts = array('courtNumber', 'umpireFunction', 'umpireRank', 'currentCourt');
		$form->addBlock('blkAffect', $elts, 'classLive');

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');

		$utpage->display();
		exit;
	}

	/**
	 * Display a page with the list of umpire by court
	 */
	function _displayUmpires()
	{

		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage('live');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fLive', 'live', LIVE_UMPIRE_EDIT);
		$form->setTitle('tUmpireCourt');


		$sort = kform::getSort('rowUmpires', 3);
		$umpires = $dt->getOffUmpires($sort);
		if (isset($matchs['errMsg']))
		$form->addWng($matchs['errMsg']);
		else
		{
	  $krow =& $form->addRows('rowUmpires', $umpires);

	  $sizes[10] = '0+';
	  $krow->setSize($sizes);

	  $acts[0] = array(KAF_NEWWIN, 'live', LIVE_UMPIRE_EDIT, 0, 310, 170);
	  $krow->setActions($acts);
	  $form->addBtn('btnCancel');
		}

		$utpage->display();
		exit;
	}

	/**
	 * Fix umpire and service judge to the match
	 */
	function _setUmpire($matchId)
	{
		$dt = $this->_dt;
		$infos['mtch_id']      = $matchId;
		$infos['mtch_serviceId'] = kform::getInput('serviceJudge', -1);
		$infos['mtch_umpireId']  = kform::getInput('umpire', -1);
		$ret = $dt->setUmpire($infos);

		//Close the windows
		$page = new kPage('none');
		$page->close(false);
		exit;
	}

	/**
	 * Connect to the facteur and the definition of the match
	 */
	function _sendOrderToServer()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$data = kform::getData();
		list($order, $value) = explode(';', $data);
		$buf = "000;$order;$value;@";

		$ip = $ut->getParam('liveIp', '');
		if (!empty($ip) && ($ip != '127.0.0.1') )
		{
			$port = $ut->getParam('livePort', 8888);
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			$result = socket_connect($socket, $ip, $port);
			$lg = socket_write($socket, $buf, strlen($buf));
			// socket_strerror(socket_last_error()) . "\n";
			socket_close($socket);
		}
		// Envoi au deuxieme facteur
		$ip = $ut->getParam('liveIp2', '');
		if (!empty($ip) && ($ip != '127.0.0.1') )
		{
			$port = $ut->getParam('livePort', 8888);
			$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			$result = @socket_connect($socket, $ip, $port);
			$lg = socket_write($socket, $buf, strlen($buf));
			socket_close($socket);
		}
		$page = new kPage('none');
		$page->close(false);
		exit;
	}

	/**
	 * Affichage d'un match en direct
	 */
	function _displayCourtLive()
	{
		$utpage = new utPage('live');
		$div =& $utpage->getPage();

		$eventId = utvars::getEventId();
		$court   = kform::getData();

		$kdiv =& $div->addDiv("blkLive$court", 'classBlkLive');
		$link = "http://badnet.local/public/badnet.org/badnet/src/live/apeLive.php?event=$eventId&court=$court";
		$iframe=array();
		$iframe['src'] = $link;
		$iframe['class'] = 'classLiveObject';
		$iframe['frameborder'] = '0';
		$iframe['scrolling'] = 'no';
		$kdiv->addIframe("live$court", $iframe);

		$utpage->display();
		exit;
	}

	/**
	 * Connect to the facteur and the definition of the match
	 */
	function _sendMatchToServer($matchId)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$ip = $ut->getParam('liveIp', '');
		if (!empty($ip) && $ip!='127.0.0.1' )
		{
			$port = $ut->getParam('livePort', 8888);
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if ($socket !== false)
			{
				//socket_set_nonblock($socket);
				socket_connect($socket, $ip, $port);
				$eventId = utvars::getEventId();
				$buf = "000;01;$eventId;$matchId;@";
				socket_write($socket, $buf, strlen($buf));
				socket_close($socket);
			}
			//socket_strerror ($socket) . "\n";
		}
		$ip = $ut->getParam('liveIp2', '');
		if (!empty($ip) && $ip!='127.0.0.1' )
		{
			$port = $ut->getParam('livePort', 8888);
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

			if ($socket !== false)
			{
				//socket_set_nonblock($socket);
				@socket_connect($socket, $ip, $port);
				$eventId = utvars::getEventId();
				$buf = "000;01;$eventId;$matchId;@";
				@socket_write($socket, $buf, strlen($buf));
				socket_close($socket);
				//socket_strerror ($socket) . "\n";
			}
		}
		return;
	}

	/**
	 * Display a page with the list of the Matchs of a tie
	 * in orderto change the order of play of the match
	 */
	function _autoOrderMatch()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$tieId = kform::getInput('tieId', -1);
		$graphs = $dt->getMatchDependGraph($tieId);

		//  Liste des matchs dans l'ordre initial
		$temp =  $dt->getDefMatchsTie($tieId);
		foreach($temp as $match)
		$matchs[$match['mtch_id']] = $match['mtch_id'];

		// Table des matchs non disponible
		$notAvailable = array();
		// Table des matchs ordonnes
		$matchOrder = array();
		while(count($matchs))
		{
	  // Match retenu
	  $matchSel = -1;
	  // Nombre de dependance max
	  $max = -1;
	  // Saut dans la table des match non disponible
	  $step = 0;
	  // Recherche du premier match jouable
	  while($matchSel == -1)
	  {
	  	// Boucle sur les match restant
	  	foreach($matchs as $matchId)
	  	{
	  		// Le nombre de match dependant est plus grand
	  		if (count($graphs[$matchId])>$max )
	  		{
	  			// Le match est-il disponible
	  			$matchDispo = true;
	  			foreach($notAvailable as $not)
	  			if(in_array($matchId, $not))
			   $matchDispo = false;
			   if($matchDispo)
			   {
			   	$max = count($graphs[$matchId]);
			   	$matchSel = $matchId;
			   }
	  		}
	  	}
	  	if ($matchSel == -1)
	  	array_shift($notAvailable);
	  }
	  // Memoriser l'ordre du match
	  $matchOrder[] = $matchSel;
	  $notAvailable[] = $graphs[$matchSel];
	  unset($graphs[$matchSel]);
	  unset($matchs[$matchSel]);

	  // Suprimer les match des listes
	  foreach ($graphs as $matchId=>$graph)
	  {
	  	if ( $key = array_search($matchSel, $graph))
	  	unset($graphs[$matchId][$key]);
	  }

		}
		$dt->updateOrderMatch($matchOrder);
		$this->_displayOrderMatch($tieId);
		exit;
	}

	/**
	 * Affiche la fenetre de choix d'un arbitre
	 */
	function _displayMatchStatus()
	{
		$ut=& $this->_ut;
		$dt=& $this->_dt;
		$utpage = new utPage('live');
		$utpage->_page->addAction('onunload', array('refresh'));
		$content =& $utpage->getPage();
		$kform =& $content->addForm('tMatchStatus', 'live', LIVE_UPDATE_STATUS_A);

		$data = kform::getData();
		list($matchId, $court) = explode(';', $data);
		$kform->addHide('court', $court);
		$kform->addHide('matchId', $matchId);

		$status = $ut->getPref('cur_umpiring', kform::getInput('status', WBS_UMPIRE_LOOSER));

		if($status == WBS_UMPIRE_AUTO || $court==0)
		{
			$print =  $ut->getPref('printing', 0);
			$this->_updateMatchStatus($matchId, $court);
		}

		// Liste des arbitres
		$umpires = $dt->getUmpires($court, $status);
		$umpire = reset($umpires);
		$kform->addCombo('umpire', $umpires);
			
		// Liste des juges de services
		if($status == WBS_UMPIRE_OFFSERVICE)
		{
			$services = $dt->getUmpires($court, $status, true);
			$service = reset($services);
			$kform->addCombo('serviceJudge', $services, $service);
			$kform->addBtn('btnUmpire', KAF_NEWWIN, 'live',
			LIVE_UMPIRE_LOOK, 0, 600, 500);
		}

		// Bouton pour modifier les arbitres
		if($status == WBS_UMPIRE_OFFICIAL)
		{
			$kform->addBtn('btnUmpire', KAF_NEWWIN, 'live',
			LIVE_UMPIRE_LOOK, 0, 600, 500);
		}

		$kform->addCheck('chkPrint', $ut->getPref('printing', 1));

		$kform->addBtn('btnRegister', KAF_SUBMIT);
		$kform->addBtn('btnCancel');
		$elts = array('btnUmpire', 'btnRegister', 'btnCancel');
		$kform->addBlock('blkBtn', $elts);

		$utpage->display();
		exit;

	}
	/**
	 * Stop or send the match. Update status of the matches.
	 */
	function _updateMatchStatus($aMatchId = 0, $aCourt = 0, $aPrint = 0)
	{
		// Get the data
		$dt = $this->_dt;
		$ut = new utils();
		$court = kform::getInput('court', $aCourt);
		$matchId = kform::getInput('matchId', $aMatchId);
		$infos['mtch_id']      = $matchId;
		$infos['mtch_court']   = $court;

		$status = $ut->getPref('cur_umpiring', WBS_UMPIRE_LOOSER);
		if($status == WBS_UMPIRE_AUTO)
		$printScoresheet = 0;//$ut->getPref('cur_umpiring', 0);
		else
		{
			$printScoresheet = kform::getInput('chkPrint', 0);
			$ut->setPref('printing', $printScoresheet);
		}
		if ($court)
		{
			$infos['mtch_status'] = WBS_MATCH_LIVE;
			$infos['mtch_begin']   = date(DATE_FMT);
			$infos['mtch_serviceId'] = kform::getInput('serviceJudge', -1);
			if ($infos['mtch_serviceId'] == -1)
			unset($infos['mtch_serviceId']);
			$infos['mtch_umpireId']  = kform::getInput('umpire', -1);
			if ($infos['mtch_umpireId'] == -1)
			unset($infos['mtch_umpireId']);
		}
		else
		{
			$infos['mtch_status'] = WBS_MATCH_READY;
			$infos['mtch_begin']   = NULL;
			$infos['mtch_serviceId'] = -1;
			$infos['mtch_umpireId']  = -1;
			$printScoresheet = 0;
		}
		// Mise a jour du match
		$user     = utvars::getUserLogin();
		$pwd      = utvars::getUserPwd();
		$eventId  = utvars::getEventId();
		$uts = new utServices('local', $user, $pwd);
		$res = $uts->updateMatchStatus($eventId, $infos);
		// Display the error if any
		if (isset($res['errMsg']))
		{
			echo 'erreur maj locale';
			print_r($res);
		}
		unset($uts);

		// Envoyer le match au facteur pour les feuilles de match electronique
		if ($court && extension_loaded('sockets'))
		{
			$matchUniId = $this->_dt->getMatchUniId($matchId);
			$this->_sendMatchToServer($matchUniId);
		}

		// Enregistrement du resultat sur le site distant

		$server = $ut->getParam('synchroUrl', false);
		if ($server)
		{
			$user     = $ut->getParam('synchroUser');
			$pwd      = md5($ut->getParam('synchroPwd'));
			$eventId  = $ut->getParam('synchroEvent');
			$uts = new utServices($server, $user, $pwd);
			$res = $uts->updateMatchStatus($eventId, $infos);
			unset($uts);
		}

		// Display the error if any
		if (isset($res['errMsg']))
		{
			echo 'erreur maj site distant';
			print_r($res);
		}
		// Prepare un fichier html avec les prochains matchs
		$date  = $ut->getPref('cur_date', date(DATE_DATE));
		$place = $ut->getPref('cur_place', -1);
		//$this->_dt->getNextMatchNum($date, $place, 10);
		//$this->_dt->getLastMatchNum($date, $place, 10);

		if ($printScoresheet)
		{
			$ut->loadPage('matches', MTCH_PDF_INDIV_A, $matchId);
		}
		// Close the windows
		$page = new kPage('none');
		$page->close();
		exit;
	}

	/**
	 * Register the new order of the matchs
	 */
	function _updateOrderMatch($sens)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		if (!$sens)
		{
	  $page = new kPage('none');
	  $page->close();
	  exit;
		}
		$matchsId = kform::getInput('rowOrder');
		$tieId = kform::getInput('tieId');

		foreach($matchsId as $matchId)
		$dt->moveMatch($matchId, $sens);

		$this->_displayOrderMatch();
		exit;
	}

	/**
	 * Display a page with the list of the Matchs of a tie
	 * in orderto change the order of play of the match
	 */
	function _displayOrderMatch($tieId='')
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage('live');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fLive', 'live', LIVE_MOVE_UP_A);
		$form->setTitle('tOrderMatch');

		$matchId = kform::getInput('rowOrder');
		$tieId = kform::getInput('tieId', $tieId);

		$matchs = $dt->getDefMatchsTie($tieId);
		if (isset($matchs['errMsg']))
		$form->addWng($matchs['errMsg']);
		else
		{
	  $form->addHide('tieId', $tieId);
	  $krow =& $form->addRows('rowOrder', $matchs);
	  $krow->setSort(0);
	  $krow->displayNumber(false);
	  $krow->setSelect($matchId);
	  $sizes = array(3 => 0,0,0);
	  $krow->setSize($sizes);
	  $form->addBtn('btnUp', KAF_SUBMIT);
	  $form->addBtn('btnDown', KAF_UPLOAD, 'live', LIVE_MOVE_DOWN_A);
	  $form->addBtn('btnMagic', KAF_UPLOAD, 'live', LIVE_AUTOORDER_MATCH_A);
	  $form->addBtn('btnEnd', KAF_UPLOAD, 'live', LIVE_MOVE_END_A);
		}

		$utpage->display();
		exit;
	}

	/**
	 * Display a page with the list of the ties
	 * and the field to update the schedule
	 */
	function _displayListMatch($court=-1)
	{
		$utev = new utEvent();

		$eventId = utvars::getEventId();
		if ($utev->getEventType($eventId)== WBS_EVENT_INDIVIDUAL)
		$this->_displayListMatchIndiv($court);
		else
		$this->_displayListMatchTeam($court);
	}

	/**
	 * Display a page with the list of the matchs form an individual event
	 */
	function _displayListMatchIndiv($court)
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;

		$kdiv =& $this->_displayHead($court);

		$dateSel = $ut->getPref('cur_date', -1);
		$placeSel = $ut->getPref('cur_place', -1);
		$stepSel  = $ut->getPref('cur_step', -1);
		$numPage = kform::getInput('numPage', 1);
			
		$kdiv->addInfo('avg', sprintf('%.2f', $dt->dureeMoyenne($dateSel,$placeSel)));
		$displayEnded = $ut->getPref('cur_displayEnded', false);
		$displayOrder = $ut->getPref('cur_displayOrder', false);
		$matches = $dt->getMatches($dateSel, $placeSel, $stepSel, $displayEnded, $numPage, $displayOrder);
		$kdiv->addInfo('nbMatch', count($matches), 'classNa');
		$kdiv->addInfo('nbMatchPlayer', $dt->nbMatchPlayer($dateSel,$placeSel));
		if (isset($matches['errMsg'])) $kdiv->addWng($matches['errMsg']);
		else
		{
			$kform =& $kdiv->addForm('fMatches');
			$utd = new utdate();
			$date = '';
			$court =  0;
			$rows = array();
			// Pour chaque MATCH
			foreach ($matches as $match)
			{
				if (!$displayOrder && $date != $match['tie_schedule'])
				{
					$utd->setIsoDateTime($match['tie_schedule']);
					if ($utd->getDate() == date('d-m-y')) $title = "Aujourd'hui " . $utd->getTime();
					else 	$title = $utd->getDateTime();
					//if ($placeSel == -1)
					$title .= ' --'.$match['tie_place'].'--';
					$date = $match['tie_schedule'];
					$rows[] = array(KOD_BREAK, "title", $title);
				}
				if ($displayOrder && $court != $match['tosort'])
				{
					$title = 'Court ' . $match['tosort'];
					$court = $match['tosort'];
					$rows[] = array(KOD_BREAK, "title", $title);
				}
				$rows[] = $match;
			}
			$krow =& $kform->addRows("rowsList", $rows);
			$krow->setSort(0);
			$krow->displayNumber(false);
			//$krow->displaySelect(false);

			$sizes[10] = '0+';
			$krow->setSize($sizes);
			$imgs['6'] = 'logoStatus';
			$imgs['4'] = 'logoPairL';
			$imgs['5'] = 'logoPairR';
			$krow->setLogo($imgs);

			$acts = array();
			$acts[] = array( 'link' => array(KAF_NEWWIN, 'matches', KID_EDIT, 0, 620,350),
		   'icon' => utimg::getIcon('edit'),
		   'title' => 'matchEdit');
			$acts[] = array( 'link' => array(KAF_NEWWIN, 'matches',	MTCH_PDF_INDIV_A, 0, 620,350),
		   'icon' => utimg::getIcon('scoresheet'),
		   'title' => 'scoresheetEdit');
			$acts[] = array( 'link' => array(KAF_NEWWIN, 'live', LIVE_SET_UMPIRE,0,150,150),
		   'icon' => utimg::getIcon(LIVE_SET_UMPIRE),
		   'title' => 'umpire');
			$acts[] = array( 'link' => array(KAF_NEWWIN, 'live', LIVE_LIVE_MATCH,0,150,150),
		   'icon' => utimg::getIcon(LIVE_LIVE_MATCH),
		   'title' => 'send');
			$krow->setRowActions($acts);
		}
		//kform::setPageId($page);e      kform::setActId(WBS_ACT_LIVE);
		$reload = $ut->getPref('reload_live', 90);
		if ($reload > 0) $this->_utpage->setReload($reload);
		$this->_utpage->display();
		exit;
	}

	/**
	 * Display a page with the list of the matchs for a team event
	 */
	function _displayListMatchTeam($court)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$kdiv =& $this->_displayHead($court);

		$dateSel = $ut->getPref('cur_date', -1);
		$placeSel = $ut->getPref('cur_place', -1);
		$stepSel  = $ut->getPref('cur_step', -1);
		$numPage = kform::getInput('numPage', 1);

		$ties = $dt->getTies($dateSel, $placeSel, $stepSel);
		if (isset($ties['errMsg'])) $kdiv->addWng($ties['errMsg']);
		else
		{
	  $kform =& $kdiv->addForm('fMatches');
	  $utd = new utdate();
	  $rows = array();
	  $displayEnded = $ut->getPref('cur_displayEnded', false);
	  $pp=0;
	  foreach ($ties as $tie)
	  {

	  	$utd->setIsoDateTime($tie['tie_schedule']);
	  	$title = $utd->getTime(). '&nbsp;'.
	  	$tie['team_name'].'-'.
	  	$tie['team_name2'];
	  	$title .= '&nbsp;&nbsp;'.
	  	$tie['t2t_scoreW'].'/'.
	  	$tie['t2t_scoreW2'];

	  	$first = $rows;
	  	$first[] = array(KOD_BREAK, "title", $title, '', $tie['tie_id']);
		$displayOrder = $ut->getPref('cur_displayOrder', false);
	  	
	  	$matchs = $dt->getMatchsTie($tie['tie_id'], $tie['team_id'], $tie['team_id2'], $displayEnded, $displayOrder);
	  	
	  	if (count($matchs) &&
		  !isset($matchs['errMsg']))
		  {
		  	$rows = array_merge($first, $matchs);
		  }
		  else
		  $rows = $first;

	  }
	  $krow =& $kform->addRows("rowsList", $rows);
	  $krow->setSort(0);
	  $krow->displayNumber(false);
	  //$krow->displaySelect(false);

	  $sizes[3] = '0';
	  $sizes[10] = '0+';
	  $krow->setSize($sizes);

	  $imgs = array(4 => 10,11,12);
	  $krow->setLogo($imgs);

	  $acts = array();
	  $krow->setActions($acts);

	  $acts = array();
	  $acts[] = array( 'link' => array(KAF_NEWWIN, 'schedu',
	  SCHEDU_EDIT,  'tie_id', 400, 180),
			   'icon' => utimg::getIcon(SCHEDU_EDIT),
			   'title' => 'editSchedule');
	  $acts[] = array( 'link' => array(KAF_NEWWIN, 'live',
	  LIVE_PRESENCE_PDF,  'tie_id', 400, 300),
			   'icon' => utimg::getIcon(LIVE_TIE_PDF),
			   'title' => 'teamPresence');
	  $acts[] = array( 'link' => array(KAF_NEWWIN, 'live',
	  LIVE_TIE_PDF,  'tie_id', 400, 300),
			   'icon' => utimg::getIcon(LIVE_TIE_PDF),
			   'title' => 'teamDeclaration');
	  $acts[] = array( 'link' => array(KAF_NEWWIN, 'live',
	  LIVE_ORDER_MATCH_A,  'tie_id', 400,300),
			   'icon' => utimg::getIcon(LIVE_ORDER_MATCH_A),
			   'title' => 'matchOrder');
	  $acts[] = array( 'link' => array(KAF_NEWWIN, 'ties',
	  TIES_SELECT_RESULTS,  0, 750, 500),
			   'icon' => utimg::getIcon(WBS_ACT_EDIT),
			   'title' => 'teamEdit');
	  $acts[] = array( 'link' => array(KAF_NEWWIN, 'ties',
	  TIES_PDF_RESULTS, 0, 400, 300),
			   'icon' => utimg::getIcon(WBS_ACT_PRINT),
			   'title' => 'teamResults');
	  $krow->setBreakActions($acts);

	  $acts = array();
	  $acts[] = array( 'link' => array(KAF_NEWWIN, 'matches',
	  KID_EDIT, 0, 720,550),
			   'icon' => utimg::getIcon('edit'),
			   'title' => 'matchEdit');
	  $acts[] = array( 'link' => array(KAF_NEWWIN, 'matches',
	  MTCH_PDF_A, 0, 620,350),
			   'icon' => utimg::getIcon('scoresheet'),
			   'title' => 'scoresheetEdit');
	  $acts[] = array( 'link' => array(KAF_NEWWIN, 'live',
	  LIVE_SET_UMPIRE,0,150,150),
			   'icon' => utimg::getIcon(LIVE_SET_UMPIRE),
			   'title' => 'umpire');
	  $acts[] = array( 'link' => array(KAF_NEWWIN, 'live',
	  LIVE_LIVE_MATCH,0,150,150),
			   'icon' => utimg::getIcon(LIVE_LIVE_MATCH),
			   'title' => 'send');
	  $krow->setRowActions($acts);

		}
		$this->_utpage->display();
		exit;
	}

	/**
	 * Display the header of the page
	 */
	function & _displayHead($court = -1)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Creating a new page
		$this->_utpage = new utPage_A('live', true, 'itManage');

		$nbCourt = $ut->getPref('cur_nbcourt', 5);
		$this->_utpage->_page->addAction('onload', array('startChrono'));
		$this->_utpage->_page->addJavaFile('live/live.js');
		$content =& $this->_utpage->getContentDiv();
		$kform =& $content->addForm('formLive');
		$kform->addHide('training', $ut->getPref('cur_training', 3));
		$dateSel  = $ut->getPref('cur_date', date(DATE_DATE));
		$placeSel = $ut->getPref('cur_place', -1);

		// Choix de la salle
		$places = $dt->getTiesInfo('tie_place');
		$placeSel = kform::getInput('placeList', $ut->getPref('cur_place', '-----'));
		$ut->setPref('cur_place', $placeSel);
		$kcombo =& $kform->addCombo('placeList', $places, $placeSel);
		$acts[1] = array(KAF_UPLOAD, 'live', WBS_ACT_LIVE);
		$kcombo->setActions($acts);
		$kform->addBlock('blkPlace', 'placeList');

		// Choix de la date
		$dates = $dt->getDateTies();
		$dateSel = kform::getInput('date', $ut->getPref('cur_date', '-----'));
		if ($dateSel == '-----')
		{
			$date = date(DATE_DATE);
			if (in_array($date, $dates)) $dateSel = $date;
		}
		$ut->setPref('cur_date', $dateSel);
		$kcombo=& $kform->addCombo('date', $dates, $dateSel);
		$kcombo->setActions($acts);
		$kform->addBlock('blkDate', 'date');

		// Choix de la page
		$pages = array();
		$nb = $dt->getNbMatch($dateSel, $placeSel);
		for ($i=1; $i<=$nb;$i++) $pages[$i]=$i;
		$kcombo=& $kform->addCombo('numPage', $pages, kform::getInput('numPage', 1));
		$kcombo->setActions($acts);
		$kform->addBlock('blkNumPage', 'numPage');

		// liste des poules
		$sort = array(5,4);
		$utd = new utdraw();
		$draws = $utd->getDraws($sort);
		unset($draws['errMsg']);
		$list['-1'] = '-----';
		$listlot['-1'] = '-----';
		$oGroup = new objgroup();
		foreach($draws as $draw)
		{
			$groups = $oGroup->getListGroups($draw['draw_id'], WBS_ROUND_GROUP);
			foreach($groups as $group)
			{
				if($group=='Principal') $list[$draw['draw_id'].';'.$group] = $draw['draw_stamp'];
				else $list[$draw['draw_id'].';'.$group] = $group . '-' . $draw['draw_stamp'];
				$fgroup = $oGroup->getGroup($draw['draw_id'], $group);
				//print_r($fgroup);
				if ($fgroup['mainroundid'] > 0)
				{
					if($group=='Principal') $listlot[$draw['draw_id'].';'.$group . ';-1'] = $draw['draw_stamp'];
					else $listlot[$draw['draw_id'].';'.$group . ';-1'] = $group . '-' . $draw['draw_stamp'];
				}
			}
		}
		if (count($list) >1)
		{
			$kcombo=& $kform->addCombo('printGroupList', $list, reset($list));
			$acts[1] = array(KAF_NEWWIN, 'draws', DRAW_PDF_GROUPS);
			$kcombo->setActions($acts);
			$kform->addBlock('blkPrintGroup', 'printGroupList');
		}
		if (count($listlot) >1)
		{
			$kcombo=& $kform->addCombo('group2ko', $listlot, reset($listlot));
			$acts[1] = array(KAF_NEWWIN, 'pairs', PAIR_GROUP2KO);
			$kcombo->setActions($acts);
			$kform->addBlock('blkgroup2ko', 'group2ko');
		}
		unset ($utd);
		unset ($list);

		// Liste des tableaux
		$utr = new utround();
		$list['-1'] = '-----';
		$rounds = $utr->getRounds(null, WBS_ROUND_MAINDRAW, 'draw_disci, draw_name');
		foreach($rounds as $round)
		$list[$round['rund_drawId'] . ';' . $round['rund_group']] = "({$round['rund_stamp']}) {$round['draw_stamp']}";
		
		if (count($list) >1)
		{
			$kcombo=& $kform->addCombo('printKoList', $list, reset($list));
			$acts[1] = array(KAF_NEWWIN, 'draws', DRAW_PDF_GROUP_KO);
			$kcombo->setActions($acts);
			$kform->addBlock('blkPrintKo', 'printKoList');
		}
		unset ($utr);
		unset ($list);

		if ($ut->getPref('cur_displayEnded', false))
		$kform->addBtn('btnMaskEnded', KAF_UPLOAD, 'live', LIVE_SELECT_ENDED, false);
		else
		$kform->addBtn('btnDisplayEnded', KAF_UPLOAD, 'live', LIVE_SELECT_ENDED, true);

		if ($ut->getPref('cur_displayOrder', false))
		$kform->addBtn('btnOrderTime', KAF_UPLOAD, 'live', LIVE_SELECT_ORDER, false);
		else
		$kform->addBtn('btnOrderTie', KAF_UPLOAD, 'live', LIVE_SELECT_ORDER, true);


		$elts = array('blkPlace', 'blkDate', 'blkNumPage', 'blkPrintGroup', 'blkgroup2ko','blkPrintRank', 'blkPrintKo',
                     'btnMaskEnded','btnDisplayEnded', 'btnOrderTime','btnOrderTie');
		$kform->addBlock('blkPrint', $elts, 'classLive');

		// Afficher les terrains et les match en cours
		$lives = $dt->getCourtBusy($dateSel, $placeSel);
		$kdivcs =& $kform->addDiv("blkCourts");
		for ($i=1; $i <= $nbCourt; $i++)
		{
			$kdivc =& $kdivcs->addDiv("blkCourt$i", 'classCourt');
			$kdiv =& $kdivc->addDiv("blkNum$i", 'classNum');
			unset($match);
			foreach($lives as $live)
			if($live->getCourt() == $i) $match = $live;

			if (isset($match))
			{
				$kmsg =& $kdiv->addMsg("court$i", $i, 'classCourtFree');
				$acts[1] = array(KAF_NEWWIN, 'matches', KID_EDIT,
				$match->getId(), 620,350);
				$kmsg->setActions($acts);
				$title = '';
				if ($match->getUmpire() != '') $title = 'Arbitre:' . $match->getUmpire();
				if ($match->getService() != '') $title .= "; Service:" . $match->getService();
				$kdiv->setTitle($title);
			}
			else $kmsg =& $kdiv->addMsg("court$i", $i, 'classCourtFree');

			$kdiv =& $kdivc->addDiv("blkCmds$i", 'classCmds');
			if (isset($match))
			{
				$acts[1] = array(KAF_NEWWIN, 'live', LIVE_DISPLAY_STATUS, $match->getId() . ';0', 200,200);
				$kmsg =& $kdiv->addMsg("close$i",  "X", "classCmd1");
				$kmsg->setActions($acts);
				$kmsg =& $kdiv->addMsg("num$i", $match->getNum(), "classCmd2");
				if (extension_loaded('sockets'))
				{
					$acts[1] = array(KAF_NEWWIN, 'live', LIVE_ALONE, $i, 360, 170);
					$kmsg->setActions($acts);
				}
				$kdiv->addMsg("start$i", $match->getBeginTime(), "classStart");
				$kform->addHide("startHide$i", $match->getBeginTime(8));
				unset($lives[$match->getId()]);
			}
			else
			{
				$acts[1] = array(KAF_NEWWIN, 'live', LIVE_SEND_SERVER, '32;'.$i, 200,200);
				$kmsg =& $kdiv->addMsg("close$i",  "X", "classCmd1");
				$kmsg->setActions($acts);
				//$kdiv->addMsg("close$i",  "--", "classCmd1");
				$kdiv->addMsg("num$i", "--", "classCmd2");
				$kdiv->addMsg("start$i", "--", "classStart");
				$kform->addHide("startHide$i", '--');
			}
			$kdiv =& $kdivc->addDiv("blkTimer$i", 'classTimer');
			$kinput = & $kdiv->addInput("chrono$i",  "00:00", 5);
			$kinput->setClass('classChrono');
		}

		foreach($lives as $match)
		{
			$kdivc =& $kdivcs->addDiv("blkCourt$i", 'classCourt');
			$kdiv =& $kdivc->addDiv("blkNum$i", 'classNum');
			$kmsg =& $kdiv->addMsg("court$i", $match->getCourt(), 'classCourtFree');
			$acts[1] = array(KAF_NEWWIN, 'matches', KID_EDIT, $match->getId(), 620,350);
			$kmsg->setActions($acts);
			$kdiv =& $kdivc->addDiv("blkCmds$i", 'classCmds');
			$kmsg =& $kdiv->addMsg("close$i",  "X", "classCmd1");
			$acts[1] = array(KAF_NEWWIN, 'live', LIVE_DISPLAY_STATUS, $match->getId() . ';0', 200,200);
			$kmsg->setActions($acts);

			$kmsg =& $kdiv->addMsg("num$i", $match->getNum(), "classCmd2");
			if (extension_loaded('sockets'))
			{
				$acts[1] = array(KAF_NEWWIN, 'live', LIVE_ALONE, $i, 450, 320);
				$kmsg->setActions($acts);
			}
			$kdiv->addMsg("start$i", $match->getBeginTime(), "classStart");
			$kform->addHide("startHide$i", $match->getBeginTime(8));
			unset($lives[$match->getId()]);
			$kdiv =& $kdivc->addDiv("blkTimer$i", 'classTimer');
			$kinput = & $kdiv->addInput("chrono$i",  "00:00", 5);
			$kinput->setClass('classChrono');
			$i++;
		}
		$kform->addHide('nbCourt', $i-1);

		// Affichage legende
		$div =& $content->addDiv('lgnd', 'blkLegende');

		$itsm['itPdfList'] = array(KAF_NEWWIN, 'matches', MTCH_PDF_LIST_A, 0, 500, 400);
		$itsm['itPdfListLite'] = array(KAF_NEWWIN, 'matches', MTCH_PDF_LIST_LITE_A, 0, 500, 400);
		$itsm['itPrint'] = array(KAF_NEWWIN, 'live', KID_PRINT, 0, 300, 200);
		$itsm['itValid'] = array(KAF_NEWWIN, 'live', LIVE_VALIDATE, 0, 100, 100);
		$div->addMenu('menuLeft', $itsm, -1);

		$itss['itSchedu'] = array(KAF_NEWWIN, 'schedu', SCHEDU_PDF_ALLINDIV, 0, 500, 400);
		$itss['itClasse'] = array(KAF_NEWWIN, 'live', LIVE_CLASSE, 0, 500, 400);
		$itss['itPalma'] = array(KAF_NEWWIN, 'live', LIVE_PALMARES, 0, 500, 400);
		$itss['itAnalyse'] = array(KAF_NEWWIN, 'schedu', SCHEDU_ANALYSE,$placeSel, 500, 400);
		$itss['itPriorite'] = array(KAF_NEWWIN, 'schedu', SCHEDU_DISPLAY_BLABLA, $dateSel, 500, 400);
		$itss['btnOptions'] = array(KAF_NEWWIN, 'live', LIVE_OPTION_EDIT, 0, 450, 350);
		$div->addMenu('menuRight', $itss, -1);

		$div->addImg('lgdAbsent',   utimg::getIcon(WBS_MATCH_INCOMPLETE));
		$div->addImg('lgdPlay',     utimg::getIcon(WBS_MATCH_BUSY));
		$div->addImg('lgdRest',     utimg::getIcon(WBS_MATCH_REST));
		$div->addImg('lgdUmpire',   utimg::getIcon(WBS_MATCH_SEND));
		$div->addImg('lgdReady',    utimg::getIcon(WBS_MATCH_READY));
		$div->addImg('lgdLive',     utimg::getIcon(WBS_MATCH_LIVE));

		$div->addDiv('break', 'blkNewPage');
		$kdiv =& $content->addDiv('blkMatchs');
		return $kdiv;
	}
}
?>
