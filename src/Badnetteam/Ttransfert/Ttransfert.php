<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Badnetteam/Classe/Cevent.php';
require_once 'Badnetteam/Classe/Cmatch.php';
require_once 'Badnetteam/Classe/Ctie.php';
require_once 'Badnetteam/Classe/Cteam.php';
require_once 'Badnetteam/Classe/Cplayer.php';
require_once 'Badnetteam/Tevent/Tevent.inc';
require_once 'Ttransfert.inc';
require_once 'Object/Omatch.inc';
require_once 'Object/Opair.inc';

/**
 * Module de gestion des transferts
 */
class Ttransfert
{
	// {{{ properties
	public function __construct()
	{
		$controller = Bn::getController();
		$controller->addAction(TTRANSFERT_PAGE_FILE,        $this, 'pageFile');
		$controller->addAction(TTRANSFERT_OUTPUT_FILE,      $this, 'outputFile');
		$controller->addAction(TTRANSFERT_CONTROL_FILE,     $this, 'controlFile');
		$controller->addAction(TTRANSFERT_PAGE_CONTROL,     $this, 'pageControl');
		$controller->addAction(TTRANSFERT_IMPORT_FILE,      $this, 'importFile');
	}

	/**
	 * Genere le fichier xml contenant les rencontres
	 *
	 */
	public function outputFile()
	{
		$cEvent = new Cevent();

		$path = realpath('../Temp/Tmp');
		$place = $cEvent->getVal('place');
		$stripplace = Bn::stripAccent(utf8_decode($place));
		$filename = $path . "/$stripplace.xml";

		$out =new XMLWriter();
		$out->openURI($filename);
		$out->setIndent(true);
		$out->setIndentstring("   ");
		$out->startDocument('1.0', 'utf-8', 'yes');
		$out->startElement('ties');
		$out->writeElement('version', '0.1');
		$out->writeElement('generate', date('Y-m-d H:i:s'));
		$out->writeElement('generator', 'BadNetTeam');
		$out->writeElement('numid',      $cEvent->getVal('numid'));
		$out->writeElement('eventname',  $cEvent->getVal('name'));
		$out->writeElement('eventdate',  $cEvent->getVal('date'));
		$out->writeElement('eventplace', $cEvent->getVal('place'));
		$out->writeElement('eventstep',  $cEvent->getVal('step'));
		$out->writeElement('rest',       $cEvent->getVal('rest'));
		$out->writeElement('nbcourt',    $cEvent->getVal('nbcourt'));
		$out->writeElement('scoringsystem', $cEvent->getVal('scoringsystem'));
		$out->writeElement('rankingsystem', $cEvent->getVal('rankingsystem'));
		$out->writeElement('allowaddplayer', $cEvent->getVal('allowaddplayer'));
		$out->writeElement('delayaddplayer', $cEvent->getVal('delayaddplayer'));

		// Recuperer les equipes
		$teamIds = Cteam::getTeams();
		// Ecrire les equipes et les joueurs dans le fichier
		foreach($teamIds as $teamId)
		{
			$cTeam = new Cteam($teamId);
			$out->startElement('team');
			$out->writeAttribute('numid',   $cTeam->getVal('numid'));
			$out->writeElement('teamname',  $cTeam->getVal('name'));
			$out->writeElement('teamstamp', $cTeam->getVal('stamp'));
			$out->writeElement('teampos',   $cTeam->getVal('pos'));

			// Recuperer les joueurs de l'equipe
			$playerIds = $cTeam->getPlayers();
			foreach($playerIds as $playerId)
			{
				$cPlayer  = new Cplayer($playerId);
				$out->startElement('player');
				$out->writeAttribute('numid',   $cPlayer->getVal('numid'));
				$out->writeElement('famname',   $cPlayer->getVal('famname'));
				$out->writeElement('firstname', $cPlayer->getVal('firstname'));
				$out->writeElement('gender',    $cPlayer->getVal('gender'));
				$out->writeElement('license',   $cPlayer->getVal('license'));
				$out->writeElement('catage',    $cPlayer->getVal('catage'));
				$out->writeElement('numcatage', $cPlayer->getVal('numcatage'));
				$out->writeElement('surclasse', $cPlayer->getVal('surclasse'));
				$out->writeElement('mute',      $cPlayer->getVal('mute'));
				$out->writeElement('stranger',  $cPlayer->getVal('stranger'));
				$out->writeElement('born',      $cPlayer->getVal('born'));
				$out->writeElement('rest',      $cPlayer->getVal('rest'));
				$out->writeElement('ispresent', $cPlayer->getVal('ispresent'));
				$out->writeElement('court',     $cPlayer->getVal('court'));
				// Classement simple
				$out->startElement('rank');
				$out->writeElement('discipline', OMATCH_DISCIPLINE_SINGLE);
				$out->writeElement('level',    $cPlayer->getVal('levels'));
				$out->writeElement('points', $cPlayer->getVal('points'));
				$out->writeElement('order',  $cPlayer->getVal('ranks'));
				$out->writeElement('range',  $cPlayer->getVal('ranges'));
				$out->endElement(); // Rank

				// Classement double
				$out->startElement('rank');
				$out->writeElement('discipline', OMATCH_DISCIPLINE_DOUBLE);
				$out->writeElement('level',    $cPlayer->getVal('leveld'));
				$out->writeElement('points', $cPlayer->getVal('pointd'));
				$out->writeElement('order',  $cPlayer->getVal('rankd'));
				$out->writeElement('range',  $cPlayer->getVal('ranged'));
				$out->endElement(); // Rank
				// Classement mixte
				$out->startElement('rank');
				$out->writeElement('discipline', OMATCH_DISCIPLINE_MIXED);
				$out->writeElement('level',    $cPlayer->getVal('levelm'));
				$out->writeElement('points', $cPlayer->getVal('pointm'));
				$out->writeElement('order',  $cPlayer->getVal('rankm'));
				$out->writeElement('range',  $cPlayer->getVal('rangem'));
				$out->endElement(); // Rank
				$out->endElement(); // Player;
				unset($cPlayer);
			}
			$out->endElement(); // Team
			unset($cTeam);
		}

		// Recuperer les rencontres
		$tieIds = Ctie::getTieIds();
		foreach($tieIds as $tieId)
		{
			$cTie = new Ctie($tieId);
			$out->startElement('tie');
			$out->writeAttribute('numid',    $cTie->getVal('numid'));
			$out->writeElement('division',   $cTie->getVal('division'));
			$out->writeElement('group',      $cTie->getVal('groupe'));
			$out->writeElement('teamhnumid', $cTie->getVal('teamhnumid'));
			$out->writeElement('step',       $cTie->getVal('step'));
			$out->writeElement('teamvnumid', $cTie->getVal('teamvnumid'));
			$out->writeElement('schedule',   $cTie->getVal('schedule'));
			$out->writeElement('sporthall',  $cTie->getVal('sporthall'));
			$out->writeElement('tiepos',     $cTie->getVal('pos'));
			$out->writeElement('pointh',     $cTie->getVal('pointh'));
			$out->writeElement('pointv',     $cTie->getVal('pointv'));

			// Traiter les matches
			$matchIds = $cTie->getMatchIds();
			foreach($matchIds as $matchId)
			{
				$cMatch = new Cmatch($matchId);
				$out->startElement('match');
				$out->writeAttribute('numid', $cMatch->getVal('numid'));
				$out->writeAttribute('disci', $cMatch->getVal('disci'));
				$out->writeAttribute('discipline', $cMatch->getVal('discipline'));
				$out->writeAttribute('rank',   $cMatch->getVal('rank'));
				$out->writeElement('order',    $cMatch->getVal('morder'));
				$out->writeElement('begin',    $cMatch->getVal('begin'));
				$out->writeElement('end',      $cMatch->getVal('end'));
				$out->writeElement('score',    $cMatch->getVal('score'));
				$out->writeElement('court',    $cMatch->getVal('court'));
				$out->writeElement('resulth',  $cMatch->getVal('resulth'));
				$out->writeElement('resultv',  $cMatch->getVal('resultv'));
				$out->writeElement('status',   $cMatch->getVal('status'));
				$out->writeElement('playh1numid', $cMatch->getVal('playh1numid'));
				$out->writeElement('playh2numid', $cMatch->getVal('playh2numid'));
				$out->writeElement('playv1numid', $cMatch->getVal('playv1numid'));
				$out->writeElement('playv2numid', $cMatch->getVal('playv2numid'));
				$out->endElement(); // match
				unset($cMatch);
			}
			$out->endElement(); // Tie
			unset($cTie);
		}
		//fermeture du fichier
		$out->endElement(); // Ties
		$out->endDocument();

		// Preparer un fichier zip contenant le fichier genere
		$zip = new ZipArchive();
		$zipfilename = $path . "/$stripplace.zip";
		if ($zip->open($zipfilename, ZIPARCHIVE::CREATE)!==TRUE) {   exit("Impossible d'ouvrir <$filename>\n"); }
		$filename = $path . "/$stripplace.xml";
		$zip->addFile($filename, "$stripplace.xml");
		$zip->close();
		unlink($filename);

		// Stream the file to the client
		header("Content-Type: application/zip");
		header("Content-Length: " . filesize($zipfilename));
		header("Content-Disposition: attachment; filename=\"$stripplace.zip\"");
		readfile($zipfilename);
		unlink($zipfilename);
		return false;
	}

	/**
	 * Controle du fichier et affichage des donnees
	 *
	 */
	public function controlFile()
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

		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->validateOnParse = true;
		$doc->load($filename);
		if (!$doc->schemaValidate('../Script/Dtd/badnetteam.xsd') && empty($msg))
		{
			$msg = "Format de fichier incorrect";
		}

		$_POST['filename'] = $filename;
		$_POST['msg'] = $msg;
		return TTRANSFERT_PAGE_CONTROL;
	}

	/**
	 * Page de controle avant importation
	 *
	 */
	public function pageControl()
	{
		$filename = Bn::getValue('filename');

		$body = new Body();
		$form = $body->addForm('frmFiles', TTRANSFERT_IMPORT_FILE, 'targetBody');
		$form->addHidden('filename', $filename);
		$div = $form->addDiv();

		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->load($filename);
		$msg = Bn::getValue('msg');
		//$div->addInfo('', 'fichier', $filename);
		if (!empty($msg))
		{
			$div->addP('', $msg, 'bn-error');
			// Boutons de commande
			$d = $div->addDiv('', 'bn-div-btn');
			$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL, TTRANSFERT_PAGE_FILE, 'targetDlg');
		}
		else
		{
			$div->addInfo('', LOC_LABEL_TOURNAMENT, $this->_nodeValue($doc, 'eventname'));
			$div->addInfo('', LOC_LABEL_DATE, $this->_nodeValue($doc, 'eventdate'));
			$div->addInfo('', LOC_LABEL_PLACE, $this->_nodeValue($doc, 'eventplace'));
			$div->addInfo('', LOC_LABEL_STEP, $this->_nodeValue($doc, 'eventstep'));
			// Boutons de commande
			$form->getForm()->addMetadata('success', "submitFile");
			$form->getForm()->addMetadata('dataType', "'json'");
			$d = $div->addDiv('', 'bn-div-btn');
			$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
			$d->addButtonValid('btnImport', LOC_BTN_VALID);
		}
		$body->display();
		return false;
	}

	/**
	 * Importation du fichier
	 *
	 */
	public function importFile()
	{
		$filename = bn::getValue('filename');
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->validateOnParse = true;
		$doc->load($filename);

		// Vider les tables
		$qt =new Bn_query('event', '_team');
		$qt->emptyTable('event');
		$qt->emptyTable('match');
		$qt->emptyTable('player');
		$qt->emptyTable('team');
		$qt->emptyTable('tie');

		// Traitement des infos generale
		$qt->setValue('evnt_numid',    $this->_nodeValue($doc, 'numid'));
		$qt->addValue('evnt_name',     $this->_nodeValue($doc, 'eventname'));
		$qt->addValue('evnt_date',     $this->_nodeValue($doc, 'eventdate'));
		$qt->addValue('evnt_place',    $this->_nodeValue($doc, 'eventplace'));
		$qt->addValue('evnt_step',     $this->_nodeValue($doc, 'eventstep'));
		$qt->addValue('evnt_rest',     $this->_nodeValue($doc, 'rest'));
		$qt->addValue('evnt_nbcourt',  $this->_nodeValue($doc, 'nbcourt'));
		$qt->addValue('evnt_scoringsystem',  $this->_nodeValue($doc, 'scoringsystem'));
		$qt->addValue('evnt_rankingsystem',  $this->_nodeValue($doc, 'rankingsystem'));
		$qt->addValue('evnt_allowaddplayer',  $this->_nodeValue($doc, 'allowaddplayer'));
		$qt->addValue('evnt_delayaddplayer',  $this->_nodeValue($doc, 'delayaddplayer'));
		$qt->addRow();

		// Traitement des equipes
		$xteams = $doc->getElementsByTagName('team');
		$qt->setTables('team');
		$qp = new Bn_query('player', '_team');
		foreach ($xteams as $xteam)
		{
			$qt->setValue('team_numid', $xteam->getAttribute('numid'));
			$qt->addValue('team_name',  $this->_nodeValue($xteam, 'teamname'));
			$qt->addValue('team_stamp', $this->_nodeValue($xteam, 'teamstamp'));
			$qt->addValue('team_pos',   $this->_nodeValue($xteam, 'teampos'));
			$where = 'team_numid=' . $xteam->getAttribute('numid');
			$qt->setWhere($where);
			$teamId = $qt->replaceRow($where);

			// Joueurs de l'equipe
			$xplayers  = $xteam->getElementsByTagName('player');
			foreach ($xplayers as $xplayer)
			{
				$qp->setValue('play_numid',     $xplayer->getAttribute('numid'));
				$qp->addValue('play_famname',   $this->_nodeValue($xplayer, 'famname'));
				$qp->addValue('play_firstname', $this->_nodeValue($xplayer, 'firstname'));
				$qp->addValue('play_gender',    $this->_nodeValue($xplayer, 'gender'));
				$qp->addValue('play_license',   $this->_nodeValue($xplayer, 'license'));
				$qp->addValue('play_catage',    $this->_nodeValue($xplayer, 'catage'));
				$qp->addValue('play_numcatage', $this->_nodeValue($xplayer, 'numcatage'));
				$qp->addValue('play_surclasse', $this->_nodeValue($xplayer, 'surclasse'));
				$qp->addValue('play_mute',      $this->_nodeValue($xplayer, 'mute'));
				$qp->addValue('play_stranger',  $this->_nodeValue($xplayer, 'stranger'));
				$qp->addValue('play_born',      $this->_nodeValue($xplayer, 'born'));
				$qp->addValue('play_teamid', $teamId);
				// Classement du joueurs
				$xranks  = $xplayer->getElementsByTagName('rank');
				foreach ($xranks as $xrank)
				{
					$discipline = $this->_nodeValue($xrank, 'discipline');
					$suf = 's';
					if ($discipline == OMATCH_DISCIPLINE_DOUBLE) $suf = 'd';
					if ($discipline == OMATCH_DISCIPLINE_MIXED) $suf = 'm';
					$qp->addValue('play_level'.$suf, $this->_nodeValue($xrank, 'level'));
					$qp->addValue('play_point'.$suf, $this->_nodeValue($xrank, 'points'));
					$qp->addValue('play_rank'.$suf,  $this->_nodeValue($xrank, 'order'));
					$qp->addValue('play_range'.$suf,  $this->_nodeValue($xrank, 'range'));
				}
				$qp->addValue('play_rest',      $this->_nodeValue($xplayer, 'rest'));
				$qp->addValue('play_ispresent', $this->_nodeValue($xplayer, 'ispresent'));
				$qp->addValue('play_court',     $this->_nodeValue($xplayer, 'court'));
				$where = 'play_numid='. $xplayer->getAttribute('numid');
				$where .= ' AND play_teamid='. $teamId;
				$qp->setWhere($where);
				$playerId = $qp->replaceRow();
				$players[$xplayer->getAttribute('numid')] = $playerId;
			}
			$players[0] = 0;
			$teams[$xteam->getAttribute('numid')] = $teamId;
		}

		// Traitement des rencontres
		$qt->setTables('tie');
		$qp->setTables('match');
		$xties = $doc->getElementsByTagName('tie');
		foreach ($xties as $xtie)
		{
			$qt->setValue('tie_numid',     $xtie->getAttribute('numid'));
			$qt->addValue('tie_division',  $this->_nodeValue($xtie, 'division'));
			$qt->addValue('tie_group',     $this->_nodeValue($xtie, 'group'));
			$qt->addValue('tie_teamvid',   $teams[$this->_nodeValue($xtie, 'teamvnumid')]);
			$qt->addValue('tie_step',      $this->_nodeValue($xtie, 'step'));
			$qt->addValue('tie_teamhid',   $teams[$this->_nodeValue($xtie, 'teamhnumid')]);
			$qt->addValue('tie_schedule',  $this->_nodeValue($xtie, 'schedule'));
			$qt->addValue('tie_sporthall', $this->_nodeValue($xtie, 'sporthall'));
			$qt->addValue('tie_pos',       $this->_nodeValue($xtie, 'tiepos'));
			$qt->addValue('tie_pointh',    $this->_nodeValue($xtie, 'pointh'));
			$qt->addValue('tie_pointv',    $this->_nodeValue($xtie, 'pointv'));
			$tieId = $qt->addRow();
			// Traitement des matchs
			$xmatchs = $xtie->getElementsByTagName('match');
			foreach ($xmatchs as $xmatch)
			{
				$qp->setValue('mtch_numid', $xmatch->getAttribute('numid'));
				$disci = $xmatch->getAttribute('disci');
				$qp->addValue('mtch_disci',  $disci);
				switch($disci)
				{
					case OMATCH_DISCI_MS:
					case OMATCH_DISCI_WS:
					case OMATCH_DISCI_AS:
						$qp->addValue('mtch_discipline', OMATCH_DISCIPLINE_SINGLE);
						break;
					case OMATCH_DISCI_MD:
					case OMATCH_DISCI_WD:
					case OMATCH_DISCI_AD:
						$qp->addValue('mtch_discipline', OMATCH_DISCIPLINE_DOUBLE);
						break;
					default:
						$qp->addValue('mtch_discipline', OMATCH_DISCIPLINE_MIXED);
				}
				$qp->addValue('mtch_order', $this->_nodeValue($xmatch, 'order'));
				$qp->addValue('mtch_rank',  $xmatch->getAttribute('rank'));
				$qp->addValue('mtch_begin', $this->_nodeValue($xmatch, 'begin'));
				$qp->addValue('mtch_end',   $this->_nodeValue($xmatch, 'end'));
				$id = $this->_nodeValue($xmatch, 'playh1numid');
				if (isset($players[$id])) $qp->addValue('mtch_playh1id',   $players[$id]);
				$id = $this->_nodeValue($xmatch, 'playh2numid');
				if (isset($players[$id])) $qp->addValue('mtch_playh2id',   $players[$id]);
				$id = $this->_nodeValue($xmatch, 'playv1numid');
				if (isset($players[$id])) $qp->addValue('mtch_playv1id',   $players[$id]);
				$id = $this->_nodeValue($xmatch, 'playv2numid');
				if (isset($players[$id])) $qp->addValue('mtch_playv2id',   $players[$id]);
				$qp->addValue('mtch_score',   $this->_nodeValue($xmatch, 'score'));
				$qp->addValue('mtch_tieid',   $tieId);
				$qp->addValue('mtch_court',   $this->_nodeValue($xmatch, 'court'));
				$qp->addValue('mtch_status',  $this->_nodeValue($xmatch, 'status'));
				$qp->addValue('mtch_resulth', $this->_nodeValue($xmatch, 'resulth'));
				$qp->addValue('mtch_resultv', $this->_nodeValue($xmatch, 'resultv'));
				$matchId = $qp->addRow();
			}
		}
		unset($doc);
		unlink($filename);
		$res = array('bnAction'  => TEVENT_PAGE_TIES,
					 'ajax' => 1);
		echo Bn::toJson($res);
		return false;
	}

	private function _nodeValue($aNode, $aElement, $aDefault = null)
	{
		$nodeList = $aNode->getElementsByTagName($aElement);
		if ($nodeList != null) return $nodeList->item(0)->nodeValue;
		else return $aDefault;
	}

	/**
	 * Choix du fichier pour l'importation
	 *
	 * @return unknown
	 */
	public function pageFile()
	{
		$body = new Body();

		$form = $body->addForm('frmFiles', TTRANSFERT_CONTROL_FILE, 'targetDlg');
		$form->getForm()->setAttribute('enctype', 'multipart/form-data');

		$div = $form->addDiv('divFiles');

		$div->addP('', LOC_P_FILES, 'bn-p-info');
		$msg = Bn::getValue('msg');
		if (!empty($msg) ) $div->addP('', $msg, 'bn-error');
		$edt = $div->addEditFile('file', LOC_LABEL_FILE);

		// Boutons de commande
		$d = $div->addDiv('', 'bn-div-btn');
		$btn = $d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$d->addButtonValid('btnImport', LOC_BTN_VALID);
		$body->display();
		return false;
	}

}
?>
