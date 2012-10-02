<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Transfert.inc';
require_once 'Object/Otie.inc';
require_once 'Object/Omatch.inc';
require_once 'Badnet/Event/Event.inc';
require_once 'Badnetadm/Badnetadm.inc';

/**
 * Module dimportation
 */
class Transfert
{
	// {{{ properties
	public function __construct()
	{
		$userId = Bn::getValue('user_id');
		if (empty($userId) ) return BNETADM_LOGOUT;
		$controller = Bn::getController();
		$controller->addAction(BTRANSFERT_PAGE_IO,  $this, 'pageIo');
		$controller->addAction(BTRANSFERT_PAGE_I,    $this, 'PageI');
		$controller->addAction(BTRANSFERT_PAGE_O,    $this, 'PageO');
		$controller->addAction(BTRANSFERT_EXPORT_TIES,  $this, 'exportTies');
		$controller->addAction(BTRANSFERT_EXPORT_HALL,  $this, 'exportHall');
		$controller->addAction(BTRANSFERT_EXPORT_FULL,  $this, 'exportFull');
		$controller->addAction(BTRANSFERT_EXPORT_POONA, $this, 'exportPoona');
		$controller->addAction(BTRANSFERT_IMPORT_TIES,  $this, 'importTies');
		$controller->addAction(BTRANSFERT_IMPORT_DIVS,  $this, 'importDivs');
		$controller->addAction(BTRANSFERT_IMPORT_HALL,  $this, 'importHall');
		$controller->addAction(BTRANSFERT_IMPORT_FULL,  $this, 'importFull');
		$controller->addAction(BTRANSFERT_SELECT_TIES,  $this, 'selectTies');
		$controller->addAction(BTRANSFERT_LOAD_DIVS,    $this, 'loadDivs');
		$controller->addAction(BTRANSFERT_LOAD_TIES,    $this, 'loadTies');
		$controller->addAction(BTRANSFERT_CONTROL_TIES, $this, 'controlTies');
		$controller->addAction(BTRANSFERT_PAGE_TIES,    $this, 'pageTies');
		
	}

	// @todo provisoire: redirection vers l'ancien BadNet
	// tous les goto doivent disparaitre a terme
	private function _goto($aPage, $aAction)
	{
		$eventId = Bn::getValue('event_id');
		$var = &$GLOBALS['HTTP_SESSION_VARS'];
		$var['wbs']['theme'] = 2;
		$var['wbs']['themeId'] = $eventId;
		$var['data'][] = $value;

		$url = $_SERVER['PHP_SELF']
		. '?kpid=' . $aPage
		. '&kaid=' . $aAction;
		header("Location: $url");
		return false;
	}

	public function oldTies() { $this->_goto('ties', 600);}
	public function importFull() { $this->_goto('export', 1800);}
	public function exportFull() { $this->_goto('export', 1751);}
	//public function exportPoona() { $this->_goto('export', 1750);}
	public function exportPoona() {
		$isSquash = Bn::getConfigValue('squash', 'params');
		if ($isSquash) $this->_goto('export', 1756);
		else $this->_goto('dbf', 950);
	}

	/**
	 * Controle du fichier des rencontres et affichage des donnees
	 *
	 */
	public function controlTies()
	{
		$postFiles = $_FILES;
		$value = reset($postFiles);
		$tempFilename = $value['tmp_name'];
		$msg = '';

		// Si le fichier est une archive, la decompresser
		$zip = new ZipArchive;
		$res = $zip->open($tempFilename);
		if ($res === TRUE)
		{
			$zip->extractTo('../Temp/Tmp/');
			$filename = '../Temp/Tmp/' . $zip->getNameIndex(0);
			$zip->close();
		}
		else
		{
			$filename = '../Temp/Tmp/' . $value['name'];
			if ( !move_uploaded_file($tempFilename, $filename) )
			$msg = "Erreur copie fichier temp $tempFilename, $filename" ;
		}

		// controle de la validite du fichier xml
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->validateOnParse = true;
		$doc->load($filename);
		if (!$doc->schemaValidate('../Script/Dtd/badnetteam.xsd') && empty($msg))
		{
			$msg = "Format de fichier incorrect";
		}

		// Verification du tournoi
		if ( empty($msg) )
		{
			$eventId = $this->_nodeValue($doc, 'numid');
			$id = Bn::getValue('event_id');
			if ($eventId != $id) $msg = "Tournoi incorrect $eventId;$id";
		}

		require_once "Badnetlib/Page.php";
		$container = new Page();
		$container->setAction(BTRANSFERT_PAGE_TIES);
		$container->addMetadata('filename', '"'. $filename . '"');
		$container->addMetadata('msg', "'$msg'");
		$corner = Bn::getConfigValue('corner', 'params');
		$container->addJQReady("fillTrame($corner);");
		$container->display();
		return false;
	}

	/**
	 * Page de controle des rencontres avant importation
	 *
	 */
	public function pageTies()
	{
		$eventId = Bn::getValue('event_id', -1);
		if ($eventId <= 1) return false;
		$oEvent = new Oevent($eventId);
		$filename = bn::getValue('filename');

		$body = new Body();
		$oEvent->header($body);
		
		//$t = $body->addP('', $oEvent->getVal('name'), 'bn-title-1');
		//$t->addBalise('span', '', LOC_TITLE_INPUT . '-->' . LOC_TITLE_IMPORT_TIES);

		$form = $body->addForm('frmFiles', BTRANSFERT_LOAD_TIES, 'targetBody');
		$form->addHidden('filename', $filename);
		$div = $form->addRichdiv();

		// Indicateur d'etape
		Body::addLgdStep($div, 4, 4, LOC_TITLE_CONTROL);
		$body->addBreak();

		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->load($filename);
		$msg = Bn::getValue('msg');
		//$div->addInfo('', 'fichier', $filename);
		if (!empty($msg))
		{
			$div->addP('', $msg, 'bn-error');
			// Boutons de commande
			$d = $div->addDiv('', 'bn-div-btn');
			$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL, BTRANSFERT_IMPORT_TIES, 'targetBody');
		}
		else
		{
			$div->addInfo('', LOC_LABEL_TOURNAMENT, $this->_nodeValue($doc, 'eventname'));
			$div->addInfo('', LOC_LABEL_DATE, $this->_nodeValue($doc, 'eventdate'));
			$div->addInfo('', LOC_LABEL_PLACE, $this->_nodeValue($doc, 'eventplace'));
			$div->addInfo('', LOC_LABEL_STEP, $this->_nodeValue($doc, 'eventstep'));
			$div->addCheckbox('morefile', LOC_LABEL_OTHER_FILE, 1, true);

			// Boutons de commande
			$d = $div->addDiv('divValidFile', 'bn-div-btn');
			$btn = $d->addButton('btnBack', LOC_BTN_PREV, BTRANSFERT_IMPORT_TIES, 'arrowthick-1-w', 'targetBody');
			$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL, BTRANSFERT_PAGE_IO, 'targetBody');
			$d->addButtonValid('btnImport', LOC_BTN_IMPORT);
		}
		$body->display();
		return false;
	}

	/**
	 * Chargement des rencontres
	 *
	 */
	public function loadTies()
	{
		// Ouverture du fichier
		$filename = Bn::getValue('filename');
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->load($filename);

		// Traitement des equipes
		$xteams = $doc->getElementsByTagName('team');
		$players = array();
		foreach ($xteams as $xteam)
		{
			$teamId = $xteam->getAttribute('numid');
			// Joueurs de l'equipe
			$xplayers  = $xteam->getElementsByTagName('player');
			foreach ($xplayers as $xplayer)
			{
				$playerId = $xplayer->getAttribute('numid');
				if ( $playerId > 0)
				{
					// Verifier que le joueur est toujours inscrit
					if (Oregi::isExist($playerId) )$players[$playerId] = $playerId;
				}
				// Nouveau joueur
				if ( empty($players[$playerId]) ) 
				{
					// Est-il dans la base BadNet
					$criteria['license']   =  $this->_nodeValue($xplayer, 'license');
					$criteria['famname']   =  $this->_nodeValue($xplayer, 'famname');
					$criteria['firstname'] =  $this->_nodeValue($xplayer, 'firstname');
					$memberIds = Omember::search($criteria);
					if (!empty($memberIds) ) $oMember = new Omember(reset($memberIds));
					else
					{
						// le joueur n'est pas dans la base : creation
						$oMember = new Omember();
						$oMember->setVal('secondname', $this->_nodeValue($xplayer, 'famname'));
						$oMember->setVal('firstname',  $this->_nodeValue($xplayer, 'firstname'));
						$oMember->setVal('license',    $this->_nodeValue($xplayer, 'license'));
						$oMember->setVal('sexe',    $this->_nodeValue($xplayer, 'gender'));
						$oMember->setVal('born',    $this->_nodeValue($xplayer, 'born'));
						$oMember->save();
					}
					// Est-il inscrit au tournoi avec cette equipe
					$regiIds = $oMember->getTeamRegis($teamId);
					if (empty($regiIds) )
					{
						$oTeam = new  Oteam($teamId);
						$oRegi = $oMember->register($oTeam);
						$oRegi->refresh();
						$regiId = $oRegi->getVal('id');
						unset($oRegi);
						unset($oTeam);
					}
					else $regiId = reset($regiIds);
					$players[$playerId] = $regiId;
					unset($oMember);
				}
			}
		}

		// Traitement des rencontres
		$xties = $doc->getElementsByTagName('tie');
		foreach ($xties as $xtie)
		{
			$tieId = $xtie->getAttribute('numid');
			$oTie = new Otie($tieId);
			if ($oTie->getVal('controlid') < 1)
			{
				// Traitement des matchs
				$xmatchs = $xtie->getElementsByTagName('match');
				foreach ($xmatchs as $xmatch)
				{
					$matchId = $xmatch->getAttribute('numid');
					$oMatch = new Omatchteam($matchId);
					$oMatch->setVal('rank',  $xmatch->getAttribute('rank'));
					$oMatch->setVal('begin', $this->_nodeValue($xmatch, 'begin'));
					$oMatch->setVal('end',   $this->_nodeValue($xmatch, 'end'));
					$oMatch->setVal('score', $this->_nodeValue($xmatch, 'score'));
					$oMatch->setVal('court', $this->_nodeValue($xmatch, 'court'));
					$oMatch->setVal('status', $this->_nodeValue($xmatch, 'status'));
					$oMatch->setval('resulth', $this->_nodeValue($xmatch, 'resulth'));
					$oMatch->setval('resultv', $this->_nodeValue($xmatch, 'resultv'));
					$oMatch->setval('playerh1id', $players[$this->_nodeValue($xmatch, 'playh1numid')]);
					$oMatch->setval('playerh2id', $players[$this->_nodeValue($xmatch, 'playh2numid')]);
					$oMatch->setval('playerv1id', $players[$this->_nodeValue($xmatch, 'playv1numid')]);
					$oMatch->setval('playerv2id', $players[$this->_nodeValue($xmatch, 'playv2numid')]);
					$oMatch->saveResult();
					unset($oMatch);
				}
				$oTeamHote  = $oTie->getTeam(OTIE_TEAM_RECEIVER);
				$oTeamHote->updateResult($tieId);
				$oTeamVisit = $oTie->getTeam(OTIE_TEAM_VISITOR);
				$oTeamVisit->updateResult($tieId);
				$oGroup = $oTie->getGroup();
				$oTeamVisit->updateGroupRank($oGroup->getVal('id', -1));
				$oTie->setVal('entrydate', date('Y-m-d H:i:s'));
				$oTie->setVal('entryid', Bn::getValue('user_id'));
				$oTie->setVal('validdate', date('Y-m-d H:i:s'));
				$oTie->setVal('validid', Bn::getValue('user_id'));
				$oTie->save();
			}
			unset($oTie);
		}

		if( Bn::getValue('morefile', -1) > 0 ) return BTRANSFERT_IMPORT_TIES;
		else return BEVENT_DISPATCH;
	}

	/**
	 * Choix du fichier contenant les rencontres a importer
	 *
	 */
	public function importTies()
	{
		$eventId = Bn::getValue('event_id', -1);
		if ($eventId <= 1) return false;
		$oEvent = new Oevent($eventId);

		$body = new Body();
		$oEvent->header($body); 
		
		// Indicateur d'etape
		Body::addLgdStep($body, 4, 3, LOC_TITLE_FILE);
		$body->addBreak();

		$msg = Bn::getValue('msg', null);
		if ( !empty($msg) )
		{
			$hd = fopen($msg, 'r');
			while( $str = fgets($hd)) $body->addP('', $str, 'bn-error');
			fclose($hd);
			unlink($msg);
		}

		$form = $body->addForm('frmFiles', BTRANSFERT_CONTROL_TIES, null, null, false);
		$form->getForm()->setAttribute('enctype', 'multipart/form-data');

		$div = $form->addRichDiv('divFiles');
		$t = $div->addP('','','bn-title-3');
		$t->addBalise('span','', LOC_TITLE_FILE_TIES);

		$div->addP('', LOC_P_IMPORT_TIES, 'bn-p-info');
		$msg = Bn::getValue('msg');
		if ( !empty($msg) ) $div->addP('', $msg, 'bn-error');
		$div->addEditFile('file', LOC_LABEL_FILE);

		// Boutons de commande : enregistrer, suite,Importation abandonner
		$d = $div->addDiv('divValidFile', 'bn-div-btn');
		$btn = $d->addButton('btnBack', LOC_BTN_PREV, BTRANSFERT_PAGE_I, 'arrowthick-1-w', 'targetBody');
		$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL, BTRANSFERT_PAGE_IO, 'targetBody');
		$d->addButtonValid('btnImport', LOC_BTN_VALID);
		$body->display();
		return false;
	}

	/**
	 * Genere les fichiers xml contenant les rencontres de la date choisie
	 *
	 */
	public function selectTies()
	{
		$eventId = Bn::getValue('event_id', -1);
		if ($eventId <= 1) return false;
		$oEvent = new Oevent($eventId);
		$oExtra = new Oeventextra($eventId);

		// Journee choisie
		$day = Bn::getvalue('day');

		// Recuperer les differents lieux
		$q = new Bn_query('ties, rounds, draws');
		$q->setFields('DISTINCT tie_place');
		$q->addWhere('tie_roundid=rund_id');
		$q->addWhere('rund_drawid=draw_id');
		$q->addWhere('draw_eventid=' . $eventId);
		$q->addWhere("date(tie_schedule) = '" . $day . "'");
		$places = $q->getCol();
		$path = realpath('../Temp/Tmp/');

		$qr = new Bn_query('ranks, rankdef');
		$qr->setFields('rkdf_label, rank_rank, rank_average, rank_discipline, rkdf_rge');

		$qm = new Bn_query('matchs');
		$qm->setFields('mtch_id, mtch_begin, mtch_end, mtch_rank, mtch_order, mtch_discipline, mtch_disci');
		$q->leftJoin('p2m t', 't.p2m_matchid=mtch_id AND t.p2m_posmatch='.OMATCH_PAIR_TOP);
		$q->leftJoin('p2m b', 'b.p2m_matchid=mtch_id AND b.p2m_posmatch='.OMATCH_PAIR_BOTTOM);
		$q->addField('t.p2m_paird', 'pairidt');
		$q->addField('t.p2m_result', 'resultt');
		$q->addField('b.p2m_paird', 'pairidb');
		$q->addField('b.p2m_result', 'resultb');

		foreach($places as $place)
		{
			$stripplace = Bn::stripAccent(utf8_decode($place));
			$filename = $path . "/$eventId-$stripplace.xml";
			$out =new XMLWriter();
			$out->openURI($filename);
			$out->setIndent(true);
			$out->setIndentstring("   ");
			$out->startDocument('1.0', 'utf-8', 'yes');
			$out->startElement('ties');
			$out->writeElement('version', '0.2');
			$out->writeElement('generate', date('Y-m-d H:i:s'));
			$out->writeElement('generator', 'BadNet');
			$out->writeElement('numid',     $oEvent->getVal('id'));
			$out->writeElement('eventname', $oEvent->getVal('name'));
			$out->writeElement('eventdate', $day);
			$out->writeElement('eventplace', $place);
			$out->writeElement('eventstep', '1');
			$out->writeElement('rest',   '20');
			$out->writeElement('nbcourt', '5');
			$out->writeElement('scoringsystem', $oEvent->getVal('scoringsystem'));
			$out->writeElement('rankingsystem', $oEvent->getVal('ranksystem'));
			$out->writeElement('allowaddplayer', $oExtra->getVal('allowaddplayer'));
			$out->writeElement('delayaddplayer', $oExtra->setVal('delayaddplayer'));

			// Recuperer les equipes concernees
			$q->setTables('teams, t2t, ties, t2r');
			$q->setFields('DISTINCT team_id, team_name, team_stamp, t2r_posround');
			$q->setWhere('team_id=t2t_teamid');
			$q->addWhere('t2t_tieid=tie_id');
			$q->addWhere('t2r_teamid=team_id');
			$q->addWhere('team_eventid=' . $eventId);
			$q->addWhere("date(tie_schedule) = '" . $day . "'");
			$q->addWhere("tie_place = '" . addslashes($place) . "'");
			$q->setOrder('team_name');
			//$q->group('team_id');
			$teams = $q->getRows();

			// Ecrire les equipes et les joueurs dans le fichier
			foreach($teams as $team)
			{
				$out->startElement('team');
				$out->writeAttribute('numid', $team['team_id']);
				$out->writeElement('teamname', $team['team_name']);
				$out->writeElement('teamstamp', $team['team_stamp']);
				$out->writeElement('teampos', $team['t2r_posround']);
				// Recuperer les joueurs de l'equipe
				$q->setTables('members, registration');
				$q->setFields('mber_firstname, mber_secondname, mber_sexe, mber_licence, mber_born, mber_id, regi_catage, regi_surclasse, regi_numcatage, regi_id');
				$q->setWhere('regi_teamid=' . $team['team_id']);
				$q->addWhere('regi_memberid=mber_id');
				$players = $q->getRows();
				foreach($players as $player)
				{
					$out->startElement('player');
					$out->writeAttribute('numid',      $player['regi_id']);
					$out->writeElement('famname',   $player['mber_secondname']);
					$out->writeElement('firstname', $player['mber_firstname']);
					$out->writeElement('gender',    $player['mber_sexe']);
					$out->writeElement('license',   $player['mber_licence']);
					$out->writeElement('catage',    $player['regi_catage']);
					$out->writeElement('numcatage', $player['regi_numcatage']);
					$out->writeElement('surclasse', $player['regi_surclasse']);
					$out->writeElement('mute',      NO);
					$out->writeElement('stranger',  NO);
					$out->writeElement('born',      $player['mber_born']);
					$out->writeElement('rest',   '');
					$out->writeElement('ispresent',   NO);
					$out->writeElement('court',   0);
					$qr->setWhere('rank_rankdefid=rkdf_id');
					$qr->addWhere('rank_regiid=' . $player['regi_id']);
					$ranks = $qr->getRows();
					foreach($ranks as $rank)
					{
						$out->startElement('rank');
						$out->writeElement('discipline', $rank['rank_discipline']);
						$out->writeElement('level',    $rank['rkdf_label']);
						$out->writeElement('points', $rank['rank_average']);
						$out->writeElement('order',  $rank['rank_rank']);
						$out->writeElement('range',  $rank['rkdf_rge']);
						$out->endElement(); // Rank
					}
					$out->endElement(); // Player;
				}
				$out->endElement(); // Team
			}

			// Recuperer les rencontres
			$q->setTables ('draws, rounds, ties');
			$q->leftJoin('t2t h', 'h.t2t_tieid=tie_id AND h.t2t_postie='.OTIE_TEAM_RECEIVER);
			$q->leftJoin('t2t v', 'v.t2t_tieid=tie_id AND v.t2t_postie='.OTIE_TEAM_VISITOR);
			$q->setFields('draw_id, draw_name, rund_id, rund_name, tie_id, tie_schedule, tie_place, tie_posround');
			$q->addField('h.t2t_teamid', 'teamhid');
			$q->addField('v.t2t_teamid', 'teamvid');
			$q->setWhere('tie_roundid=rund_id');
			$q->addWhere('rund_drawid=draw_id');
			$q->addWhere('draw_eventid=' . $eventId);
			$q->addWhere("date(tie_schedule) = '" . $day . "'");
			$q->addWhere("tie_place = '" . addslashes($place) . "'");
			$ties = $q->getRows();
			foreach($ties as $tie)
			{
				$out->startElement('tie');
				$out->writeAttribute('numid',   $tie['tie_id']);
				$out->writeElement('division',  $tie['draw_name']);
				$out->writeElement('group',     $tie['rund_name']);
				$out->writeElement('teamhnumid', $tie['teamhid']);
				$out->writeElement('step',       $tie['tie_step']);
				$out->writeElement('teamvnumid', $tie['teamvid']);
				$out->writeElement('schedule',   $tie['tie_schedule']);
				$out->writeElement('sporthall',  $tie['tie_place']);
				$out->writeElement('tiepos',     $tie['tie_posround']);
				$out->writeElement('pointh',     0);
				$out->writeElement('pointv',     0);
				// Traiter les matches
				$qm->setWhere('mtch_tieid=' . $tie['tie_id']);
				$matchs = $qm->getRows();
				foreach($matchs as $match)
				{
					$out->startElement('match');
					$out->writeAttribute('numid',    $match['mtch_id']);
					$out->writeAttribute('disci',    $match['mtch_discipline']);
					$out->writeAttribute('discipline',    $match['mtch_disci']);
					$out->writeAttribute('rank',     $match['mtch_rank']);
					$out->writeElement('order',      $match['mtch_order']);
					$out->writeElement('begin',      $match['mtch_begin']);
					$out->writeElement('end',        $match['mtch_end']);
					$out->writeElement('score',      '');
					$out->writeElement('court',      0);
					$out->writeElement('resulth',    OTIE_RESULT_NOTPLAY);
					$out->writeElement('resultv',    OTIE_RESULT_NOTPLAY);
					$out->writeElement('status',     OMATCH_STATUS_INCOMPLET);
					$out->writeElement('playh1numid',    0);
					$out->writeElement('playh2numid',    0);
					$out->writeElement('playv1numid',    0);
					$out->writeElement('playv2numid',    0);
					$out->endElement(); // match
				}
				$out->endElement(); // Tie
			}
			//fermeture du fichier
			$out->endElement(); // Ties
			$out->endDocument();
		}

		// Preparer un fichier zip contenant les fichiers generees
		$zip = new ZipArchive();
		$zipfilename = $path . "/$eventId-$day.zip";
		if ($zip->open($zipfilename, ZIPARCHIVE::CREATE)!==TRUE) {   exit("Impossible d'ouvrir <$filename>\n"); }
		foreach($places as $place)
		{
			$stripplace = Bn::stripAccent(utf8_decode($place));
			$filename = $path . "/$eventId-$stripplace.xml";
			$zip->addFile($filename, "$day/$stripplace.xml");
		}
		$zip->close();
		foreach($places as $place)
		{
			$stripplace = Bn::stripAccent(utf8_decode($place));
			$filename = $path . "/$eventId-$stripplace.xml";
			unlink($filename);
		}

		// Stream the file to the client
		header("Content-Type: application/zip");
		header("Content-Length: " . filesize($zipfilename));
		header("Content-Disposition: attachment; filename=\"$day.zip\"");
		readfile($zipfilename);
		unlink($zipfilename);
		return false;
	}

	/**
	 * Choix de la journee pour exportation des rencontres
	 *
	 */
	public function exportTies()
	{
		$eventId = Bn::getValue('event_id', -1);
		if ($eventId <= 1) return false;
		$oEvent = new Oevent($eventId);

		$body = new Body();
		$oEvent->header($body);

		// Indicateur d'etape
		Body::addLgdStep($body, 3, 3, LOC_TITLE_SELECT_TIES);
		$body->addBreak();

		// Recuperer les dates des rencontres , posterieures a la date actuelle
		$q = new Bn_query('ties, rounds, draws');
		$q->setFields('tie_id, tie_schedule');
		$q->addWhere('tie_roundid=rund_id');
		$q->addWhere('rund_drawid=draw_id');
		$q->addWhere('draw_eventid=' . $eventId);
		//$q->addWhere("tie_schedule > '" . date('Y-m-d') . "'");
		$q->group("date(tie_schedule)");
		$q->setOrder('tie_schedule');
		$ties = $q->getRows();

		$body->addP('', LOC_P_TIES, 'bn-p-info');

		$month = '';
		$div = $body->addDiv('', 'bn-div-left');
		foreach($ties as $tie)
		{
			if (empty($tie['tie_schedule'])) continue;
			if ( Bn::date($tie['tie_schedule'], 'm') != $month)
			{
				$month = Bn::date($tie['tie_schedule'], 'm');
				$div = $body->addDiv('', 'bn-div-left');
				$t = $div->addP('','','bn-title-3');
				$t->addBalise('span','', strftime('%B', Bn::date($tie['tie_schedule'], 'U')));
			}
			$date = Bn::date($tie['tie_schedule'], 'd-m-Y');
			$lnk = Bn::date($tie['tie_schedule'], 'Y-m-d');
			$div->addP('','','bn-p-info')->addLink('', BTRANSFERT_SELECT_TIES . "&day=" . $lnk, $date);
		}

		// Boutons de commande : enregistrer, suite,Importation abandonner
		$d = $body->addDiv('', 'bn-div-btn');
		$btn = $d->addButton('btnBack', LOC_BTN_PREV, BTRANSFERT_PAGE_O, 'arrowthick-1-w', 'targetBody');
		$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL, BTRANSFERT_PAGE_IO, 'targetBody');
		$body->display();
		return false;

	}

	/**
	 * Importation d'un IC
	 *
	 */
	public function loadDivs()
	{
		require_once 'Object/Oevent.inc';

		$postFiles = $_FILES;
		$value = reset($postFiles);
		$file = $value['tmp_name'];

		// WARNING !!!!
		// Je sais pas pourquoi mais en local il faut faire un utf8_encode
		// Pas sur le site.
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->load($file);

		// Ouverture du fichier de trace
		$path = realpath('../Temp/Tmp/');
		$fileTrace = tempnam($path.'/', 'log');
		$hd = fopen($fileTrace, 'w');
		// Traitement des clubs
		$xclubs = $doc->getElementsByTagName('club');
		fwrite($hd, "Traitement des clubs\n");
		$nb = 0;
		$nbError = 0;
		foreach ($xclubs as $xclub)
		{
		if ( ! $xclub->hasAttributes() ) continue;
		$nb++;
		$oClub = new Oasso();
		$oClub->setVal('type', OASSO_TYPE_CLUB);
		$oClub->setVal('noc', 'FRA');
		$oClub->setVal('fedeid', -1);
		$oClub->setVal('name', $xclub->getAttribute('name'));
		$oClub->setVal('stamp', $xclub->getAttribute('stamp'));

		$number = $this->_nodeValue($xclub, 'number');
		if ($number != null) $oClub->setVal('number', $number);
		else
		{
		fwrite($hd, '  - Numéro de club obligatoire manquant pour ' . $xclub->getAttribute('name') . "Club ignoré.\n");
		$nbError ++;
		continue;
		}
		$pseudo = $this->_nodeValue($xclub, 'pseudo');
		if ($pseudo != null) $oClub->setVal('pseudo', $pseudo);

		$dpt = $this->_nodeValue($xclub, 'dpt');
		if ($dpt != null) $oClub->setVal('dpt', $dpt);

		$oClub->save("asso_stamp='" . $oClub->getVal('stamp') . "'");
		$clubs[$oClub->getVal('number')] = $oClub;
		}
		fwrite($hd, '  - club détectés :' . $nb . "\n");
		fwrite($hd, '  - club valides  :' . ($nb-$nbError) . "\n");
		fwrite($hd, '  - club ignorés  :' . $nbError . "\n\n");
		
		// Traitement des divisions
		include_once 'Object/Odiv.inc';
		include_once 'Object/Ogroup.inc';
		include_once 'Object/Oround.inc';
		$groupTypes = array('A' => OROUND_TYPEIC_GROUP,
		'AR' => OROUND_TYPEIC_AR,
		'KO' => OROUND_TYPEIC_KO);
		$rankTypes = array('CG' => OGROUP_RANK_CG,
		'RP' => OGROUP_RANK_RP);
		$xdivs = $doc->getElementsByTagName('division');
		fwrite($hd, "Traitement des divisions\n");
		$nb = 0;
		$nbError = 0;
		foreach ($xdivs as $xdiv)
		{
			if ( ! $xdiv->hasAttributes() ) continue;
			$nb++;
			$oDiv = new Odiv();
			$oDiv->setVal('name', $xdiv->getAttribute('name'));
			$oDiv->setVal('stamp', $xdiv->getAttribute('stamp'));
			$oDiv->setVal('eventid', Bn::getValue('event_id'));
			$oDiv->setVal('type',    ODIV_TYPE_IC);
			//$oDiv->setVal('disci',    Bn::getValue('event_id'));
			//$oDiv->setVal('discipline',    Bn::getValue('event_id'));
			$oDiv->setVal('catage',    306);
			$oDiv->setVal('numcatage',    1);
			$divId = $oDiv->import();

			$xgroups = $xdiv->getElementsByTagName('group');
			foreach($xgroups as $xgroup)
			{
				$oGroup = new Ogroup();
				$oGroup->setVal('drawid', $divId);
				$oGroup->setVal('name',  $xgroup->getAttribute('name'));
				$oGroup->setVal('stamp', $xgroup->getAttribute('stamp'));

				$type = $this->_nodeValue($xgroup, 'type');
				if ( !isset($groupTypes[$type]) )
				{
					fwrite($hd, '  - Division ' . $oDiv->getVal('name'));
					fwrite($hd, ' Group ' . $oGroup->getVal('name'));
					fwrite($hd, ' Type de groupe inconnu :' . $type . ". Remplacé par AR (poule aller/retour)\n");
					$type = 'AR';
				}
				$oGroup->setVal('type', $groupTypes[$type]);

				$ranking = $this->_nodeValue($xgroup, 'ranking');
				if ( !isset($rankTypes[$ranking]) )
				{
					fwrite($hd, '  - Division ' . $oDiv->getVal('name'));
					fwrite($hd, ' Group ' . $oGroup->getVal('name'));
					fwrite($hd, 'Type de classement inconnu :' . $ranking . ". Remplace par CG (classement général)\n");
					$ranking = 'CG';
				}
				$oGroup->setVal('ranktype', $rankTypes[$ranking]);

				$oGroup->setVal('nbms', $this->_nodeValue($xgroup, 'nb_ms', 0) );
				$oGroup->setVal('nbws', $this->_nodeValue($xgroup, 'nb_ws', 0) );
				$oGroup->setVal('nbmd', $this->_nodeValue($xgroup, 'nb_md', 0) );
				$oGroup->setVal('nbwd', $this->_nodeValue($xgroup, 'nb_wd', 0) );
				$oGroup->setVal('nbxd', $this->_nodeValue($xgroup, 'nb_xd', 0) );
				$oGroup->setVal('nbas', $this->_nodeValue($xgroup, 'nb_os', 0) );
				$oGroup->setVal('nbad', $this->_nodeValue($xgroup, 'nb_od', 0) );

				$oGroup->setVal('matchwin', $this->_nodeValue($xgroup, 'match_win', 1) );
				$oGroup->setVal('matchloose', $this->_nodeValue($xgroup, 'match_loose', 0) );
				$oGroup->setVal('matchrtd', $this->_nodeValue($xgroup, 'match_ab', 0) );
				$oGroup->setVal('matchwo', $this->_nodeValue($xgroup, 'match_wo', -1) );

				$oGroup->setVal('tiewin', $this->_nodeValue($xgroup, 'tie_win', 3) );
				$oGroup->setVal('tieequal', $this->_nodeValue($xgroup, 'tie_equal', 2) );
				$oGroup->setVal('tieloose', $this->_nodeValue($xgroup, 'tie_loose', 1) );
				$oGroup->setVal('tiewo', $this->_nodeValue($xgroup, 'tie_wo', 0) );
				$roundId = $oGroup->import();

				$groups[$roundId] = $oGroup;
				$groupIds[$oDiv->getVal('stamp')][$oGroup->getVal('stamp')] = $roundId;

				$xteams = $xgroup->getElementsByTagName('team');
				$teamIds = array();
				foreach($xteams as $xteam)
				{
					$oTeam = new Oteam();
					$oTeam->setVal('eventid', Bn::getValue('event_id'));
					$oTeam->setVal('name', $xteam->getAttribute('name'));
					$oTeam->setVal('stamp', $xteam->getAttribute('stamp'));
					$assoFedeId = $xteam->getAttribute('asso');
					if ( isset($assos[$assoFedeId]) ) $assoId = $assos[$assoFedeId]->getVal('id');
					else
					{
						// Recherche du club dans la base badnet
						$criteria = array('fedeid' => $assoFedeId);
						$assoIds = Oasso::search($criteria);
						
						// Recherche du club dans le base federale
						if ( empty($assoIds) )
						{
							$oExt = Oexternal::factory();
							$club = $oExt->getInstance($assoFedeId);
							$oAsso = new Oasso();
							
							// Donnees de l'association
							$oAsso->setVal('type',   OASSO_TYPE_CLUB);
							$oAsso->setVal('fedeid', $assoFedeId);
							$oAsso->setVal('name',   $club['name']);
							$oAsso->setVal('stamp',  $club['stamp']);
							$oAsso->setVal('pseudo', $club['pseudo']);
							$oAsso->setVal('number', $club['number']);
							$oAsso->setVal('url',    $club['url']);
							$oAsso->setVal('dpt',    $club['dept']);
							$oAsso->setVal('noc',    'FRA');
							$oAsso->setVal('ligue',  $club['ligue']);

							// Sauvegarde avec controle de l'existance dans la base
							$assoId = $oAsso->saveAssoc();
							$assos[$assoFedeId] = $oAsso;
						}
						else
						{
							$assoId = reset($assoIds);
							$assos[$assoFedeId] = new Oasso($assoId);
						}
					}
					
					$oTeam->setVal('assoid', $assoId);
					//$oTeam->setVal('drawid', $divId);
					$oTeam->setVal('date', $this->_nodeValue($xteam, 'date') );
					$oTeam->setVal('noc', 'FRA');
					$oTeam->import();
					$club = $this->_nodeValue($xteam, 'club');
					if (isset($clubs[$club])) $oTeam->setVal('pseudoclub', $clubs[$club]->getVal('pseudo'));
					else
					{
						fwrite($hd, 'Club inconnu :' . $club . "\n");
						$oTeam->setVal('pseudoclub', '');
					}
					$teams[$xteam->getAttribute('number')] = $oTeam;
					$teamIds[] = $oTeam->getVal('id');
				}
				$oGroup->setVal('size', count($teamIds));
				$oGroup->save();
				$oGroup->updateTeams($teamIds);
			}
		}
		fwrite($hd, '  - divisions détectées :' . $nb . "\n\n");

		// Traitement des rencontres
		$q = new Bn_query('ties, t2t');
		$q->addField('tie_id');
		$qt = new Bn_query('ties');
		$xties = $doc->getElementsByTagName('tie');
		fwrite($hd, "Traitement des rencontres\n");
		$nb = 0;
		$nbError = 0;
		foreach ($xties as $xtie)
		{
			$nb++;
			$division = $this->_nodeValue($xtie, 'division');
			$group = $this->_nodeValue($xtie, 'group');
			$groupId = $groupIds[$division][$group];
			$type = $groups[$groupId]->getVal('type');
			$teamv = $this->_nodeValue($xtie, 'team_visitor');
			$teamh = $this->_nodeValue($xtie, 'team_hote');
			if ( isset($teams[$teamv]) ) $visitorId = $teams[$teamv]->getVal('id');
			else
			{
				fwrite($hd, '  - Division ' . $division . " groupe " . $group);
				fwrite($hd, ' team visiteur inconnu :' . $teamv . "\n");
				$nbError++;
				continue;
			}
			if ( isset($teams[$teamh]) ) $hoteId = $teams[$teamh]->getVal('id');
			else
			{
				fwrite($hd, '  - Division ' . $division . " groupe " . $group);
				fwrite($hd, 'Team hote inconnu :' . $teamh . "\n");
				$nbError++;
				continue;
			}

			$q->setWhere('tie_roundid=' . $groupId);
			$q->addWhere('t2t_tieid=tie_id');
			$q->addWhere('t2t_teamid='.$hoteId);
			if ($type == OROUND_TYPEIC_AR) $q->addWhere('t2t_postie='.OTIE_TEAM_RECEIVER);
			$ties = $q->getCol();
			if ( !count($ties) )
			{
				fwrite($hd, '  - Division :' . $division . " groupe :" . $group);
				fwrite($hd, ' hote :' . $teamh . " visiteur :" . $teamv);
				fwrite($hd, ". Pas de rencontre pour l'équipe :" . $teamh . '(' . $teams[$teamh]->getVal('name') . ")\n");
				$nbError++;
				continue;
			}
			$q->setWhere('tie_roundid=' . $groupId);
			$q->addWhere('t2t_tieid=tie_id');
			$q->addWhere('t2t_teamid='.$visitorId);
			if ($type == OROUND_TYPEIC_AR) $q->addWhere('t2t_postie='.OTIE_TEAM_VISITOR);
			$q->addWhere('tie_id IN ('.implode(',', $ties) . ')');
			$tieId = $q->getFirst();
			$date = $this->_nodeValue($xtie, 'date');
			$qt->setValue('tie_schedule', $date);
			$qt->addValue('tie_place', $teams[$teamh]->getVal('pseudoclub'));
			$qt->setWhere('tie_id='.$tieId);
			$qt->updateRow();
		}
		fwrite($hd, '  - Rencontres détectées :' . $nb . "\n");
		fwrite($hd, '  - Rencontres valides   :' . ($nb-$nbError) . "\n");
		fwrite($hd, '  - Rencontres ignorées  :' . $nbError . "\n\n");
		fclose($hd);

		require_once "Badnetlib/Page.php";
		$container = new Page();
		$container->setAction(BTRANSFERT_IMPORT_DIVS);
		$container->addMetadata('msg', "'$fileTrace'");
		$corner = Bn::getConfigValue('corner', 'params');
		$container->addJQReady("fillTrame($corner);");
		$container->display();
		exit;
	}

	private function _nodeValue($aNode, $aElement, $aDefault = null)
	{
		$nodeList = $aNode->getElementsByTagName($aElement);
		if ($nodeList != null) return $nodeList->item(0)->nodeValue;
		else return $aDefault;
	}

	/**
	 * Choix du fichier pour l'importation des divisions et calendrier
	 *
	 * @return unknown
	 */
	public function importDivs()
	{

		$eventId = Bn::getValue('event_id', -1);
		if ($eventId <= 1) return false;
		$oEvent = new Oevent($eventId);

		$body = new Body();
		$oEvent->header($body);

		// Indicateur d'etape
		Body::addLgdStep($body, 3, 3, LOC_TITLE_FILE);
		$body->addBreak();

		$msg = Bn::getValue('msg', null);
		if ( !empty($msg) )
		{
			$hd = fopen($msg, 'r');
			while( $str = fgets($hd)) $body->addP('', $str, 'bn-error');
			fclose($hd);
			unlink($msg);
		}

		$form = $body->addForm('frmFiles', BTRANSFERT_LOAD_DIVS, null, null, false);
		$form->getForm()->setAttribute('enctype', 'multipart/form-data');

		$div = $form->addRichDiv('divFiles');
		$t = $div->addP('','','bn-title-3');
		$t->addBalise('span','', LOC_TITLE_IC);

		$div->addP('', LOC_P_FILES, 'bn-p-info');

		$edt = $div->addEditFile('file', LOC_LABEL_FILE);

		// Boutons de commande : enregistrer, suite,Importation abandonner
		$d = $form->addDiv('', 'bn-div-btn');
		$btn = $d->addButton('btnBack', LOC_BTN_PREV, BTRANSFERT_PAGE_I, 'arrowthick-1-w', 'targetBody');
		$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL, BTRANSFERT_PAGE_IO, 'targetBody');
		$d->addButtonValid('btnImport', LOC_BTN_IMPORT);
		$body->display();
		return false;
	}


	/**
	 * Affichage de la page d'export de données
	 */
	public function pageO()
	{
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$body = new Body();

		// Titre
		$oEvent->header($body);

		// Indicateur d'etape
		Body::addLgdStep($body, 3, 2, LOC_TITLE_O);
		$body->addBreak();

		// Choix de l'etape
		$body->addP('', LOC_P_EXPORT, 'bn-p-info');

		// Type d'exportation
		if ($oEvent->isIc() )
		{
			$t = $body->addP('', '', 'bn-title-2');
			$t->addBalise('span', '', LOC_TITLE_EXPORT_TIES);
			$body->addP('', LOC_LABEL_EXPORT_TIES, 'bn-p-info');
			$btn = $body->addButton('btnTies', LOC_BTN_EXPORT_TIES, BTRANSFERT_EXPORT_TIES, null, 'targetBody');
		}
		else
		{
			$t = $body->addP('', '', 'bn-title-2');
			$t->addBalise('span', '', LOC_TITLE_EXPORT_HALL);
			$body->addP('', LOC_LABEL_EXPORT_HALL, 'bn-p-info');
			$btn = $body->addButton('btnHall', LOC_BTN_EXPORT_HALL, BTRANSFERT_EXPORT_HALL, null, 'targetBody');
		}
		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '', LOC_TITLE_EXPORT_FULL);
		$body->addP('', LOC_LABEL_EXPORT_FULL, 'bn-p-info');
		$btn = $body->addButtonGoto('btnFull', LOC_BTN_EXPORT_FULL, BTRANSFERT_EXPORT_FULL); //, null, 'targetBody');

		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '', LOC_TITLE_EXPORT_POONA);
		$body->addP('', LOC_LABEL_EXPORT_POONA, 'bn-p-info');
		$btn = $body->addButtonGoto('btnPoona', LOC_BTN_EXPORT_POONA, BTRANSFERT_EXPORT_POONA);//, null, 'targetBody');

		// Bouttons
		$d = $body->addDiv('', 'bn-div-btn');
		$btn = $d->addButton('btnCancelAccount', LOC_BTN_PREV, BTRANSFERT_PAGE_IO, 'arrowthick-1-w', 'targetBody');

		// Affichage
		$body->display();
		return false;
	}

	/**
	 * Affichage de la page de choix pour l'import de données
	 */
	public function pageI()
	{
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);

		$body = new Body();
		$oEvent->header($body);
		
		// Indicateur d'etape
		Body::addLgdStep($body, 3, 2, LOC_TITLE_I);
		$body->addBreak();

		// Choix de l'etape
		$body->addP('', LOC_P_IMPORT, 'bn-p-info');


		// Type d'importation
		if ($oEvent->isIc() )
		{
			$t = $body->addP('', '', 'bn-title-2');
			$t->addBalise('span', '', LOC_TITLE_IMPORT_TIES);
			$body->addP('', LOC_LABEL_IMPORT_TIES, 'bn-p-info');
			$btn = $body->addButton('btnTies', LOC_BTN_IMPORT_TIES, BTRANSFERT_IMPORT_TIES, null, 'targetBody');

			$t = $body->addP('', '', 'bn-title-2');
			$t->addBalise('span', '', LOC_TITLE_IMPORT_DIVS);
			$body->addP('', LOC_LABEL_IMPORT_DIVS, 'bn-p-info');
			$btn = $body->addButton('btnDivs', LOC_BTN_IMPORT_DIVS, BTRANSFERT_IMPORT_DIVS, null, 'targetBody');
		}
		else
		{
			$t = $body->addP('', '', 'bn-title-2');
			$t->addBalise('span', '', LOC_TITLE_IMPORT_HALL);
			$body->addP('', LOC_LABEL_IMPORT_HALL, 'bn-p-info');
			$btn = $body->addButton('btnHall', LOC_BTN_IMPORT_HALL, BTRANSFERT_IMPORT_HALL, null, 'targetBody');
		}
		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '', LOC_TITLE_IMPORT_FULL);
		$body->addP('', LOC_LABEL_IMPORT_FULL, 'bn-p-info');
		$btn = $body->addButtonGoto('btnGo', LOC_BTN_IMPORT_FULL, BTRANSFERT_IMPORT_FULL, null, 'targetBody');
		
		// Bouttons
		$d = $body->adddiv('', 'bn-div-btn');
		$btn = $d->addButton('btnCancelAccount', LOC_BTN_PREV, BTRANSFERT_PAGE_IO, 'arrowthick-1-w', 'targetBody');

		// Affichage
		$body->display();
		return false;
	}

	/**
	 * Affichage de la page de la direction du transfert
	 */
	public function pageIo()
	{
		// Type d'importation
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);

		$body = new Body();
		$oEvent->header($body);
		
		// Indicateur d'etape
		Body::addLgdStep($body, 3, 1, LOC_TITLE_SENSE);
		$body->addBreak();

		// Choix de l'etape
		$body->addP('', LOC_P_TRANSFERT, 'bn-p-info');

		// Sens de transfert
		$sense = Bn::getValue('sense', BTRANSFERT_PAGE_O);
		$body->addHidden('valsense', $sense);

		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '', LOC_TITLE_OUTPUT);
		$body->addP('', LOC_LABEL_OUTPUT, 'bn-p-info');
		$btn = $body->addButton('btnExport', LOC_BTN_OUTPUT, BTRANSFERT_PAGE_O, null, 'targetBody');


		$t = $body->addP('', '', 'bn-title-2');
		$t->addBalise('span', '', LOC_TITLE_INPUT);
		$body->addP('', LOC_LABEL_INPUT, 'bn-p-info');
		$btn = $body->addButton('btnImport', LOC_BTN_INPUT, BTRANSFERT_PAGE_I, null, 'targetBody');

		// Affichage
		$body->display();
		return false;
	}
}
?>
