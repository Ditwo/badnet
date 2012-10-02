<?php
/*****************************************************************************
 !   Module     : resu
 !   File       : $Source: /cvsroot/aotb/badnet/src/resu/resu_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.16 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/03/06 18:02:10 $
 ******************************************************************************/
require_once "base_A.php";
require_once "resu.inc";
require_once "ties/ties.inc";
require_once "regi/regi.inc";
require_once "mber/mber.inc";
require_once "utils/utimg.php";
require_once "utils/utpage_A.php";
require_once "offo/offo.inc";

/**
 * Module d'affichage des matchs des joueurs
 *
 * @author Romain JUBERT <romain.jubert@ifrance.com>
 * @see to follow
 *
 */

class resu_A
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
	function resu_A()
	{
		$this->_ut = new utils();
		$this->_dt = new resuBase_A();
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
	function start($action)
	{
		switch ($action)
		{
			case KID_SELECT:
				$id = kform::getData();
				$this->_displayResu($id);
				break;
			case KID_EDIT:
				$id = kform::getData();
				$this->_displayEdit($id);
				break;
			case KID_UPDATE:
				$this->_update();
				break;
			default:
				echo "page $action demand�e depuis resu_A<br>";
				exit();
		}
	}
	// }}}

	// {{{ _displayResu()
	/**
	 * Display a page with the results of the player
	 *
	 * @access private
	 * @return void
	 */
	function _displayResu($regiId)
	{
		$utev = new utEvent();

		$eventId = utvars::getEventId();
		if (!utvars::isTeamEvent())
		$this->_displayResuIndiv($regiId);
		else
		$this->_displayResuTeam($regiId);
	}
	//}}}


	// {{{ _displayResuTeam()
	/**
	 * Display a page with the results of a player for an individual
	 * event.
	 *
	 * @access private
	 * @param
	 * @return void
	 */
	function _displayResuTeam($regiId)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Create a new page
		$kdiv =& $this->_displayHead('itPlayers');
		//$kdiv =& $content->addDiv('teamDiv', 'cont2');

		$infos = $dt->getPlayer($regiId);
		if (isset($infos['errMsg']))
		{
	  $kdiv->addWng($infos['errMsg']);
	  $this->_utpage->display();
	  exit();
		}
		// Display players list
		$players = $dt->getPlayers();
		$kcombo =& $kdiv->addCombo('selectList', $players, $players[$regiId]);
		$acts[1] = array(KAF_UPLOAD, 'resu', KID_SELECT);
		$kcombo->setActions($acts);


		require_once "utils/objplayer.php";
		$player = new objplayer($regiId);


		//display general informations
		$div =& $kdiv->addDiv('playerName');
		$size['maxWidth'] = 100;
		$size['maxHeight'] = 100;
		$photo = utimg::getPathPhoto($infos['mber_urlphoto']);
		$div =& $kdiv->addDiv('photo');
		$kimg =& $div->addImg("playerPhoto", $photo, $size);

		$div =& $kdiv->addDiv('infos', 'blkData');
		$div->addMsg('tCivil', '', 'kTitre');
		$div->addInfo("mberBorn", $player->getBorn());
		$div->addInfo("mberLicence", $player->getLicence());
		$infoIbf =& $div->addInfo("mberIbfNumber",  $player->getIbfNum());
		$div->addInfo("mberCatage",  $ut->getlabel($player->getCatage()));
		$div->addInfo("mberCompet",  $player->getDateCompet());
		$div->addInfo("mberSurclasse",  $ut->getlabel($player->getSurclasse()));
		$div->addInfo("mberDateSurclasse",  $player->getDateSurclasse());

		$items = array();
		$items['btnPhoto'] = array(KAF_NEWWIN, 'mber', MBER_SELECT_PHOTO,
		$infos['mber_id'], 450, 200);
		$div->addMenu('menuPhoto', $items, -1, 'classMenuBtn');

		$div =& $kdiv->addDiv('regis', 'blkData');
		$div->addMsg('tRegis', '', 'kTitre');
		$kinfo =& $div->addInfo('mberDate', $player->getDate());
		$kinfo =& $div->addInfo('mberAsso', $player->getTeamName());
		$act[1] = array(KAF_UPLOAD, 'teams', KID_SELECT,
		$player->getTeamId());
		$kinfo->setActions($act);

		$isSquash = $ut->getParam('issquash', false);
		if ($isSquash) $ranks = $player->getRank(WBS_SINGLE) . ' (' . $player->getRange(WBS_SINGLE) . ')';
		else $ranks = $player->getRank(WBS_SINGLE).";".$player->getRank(WBS_DOUBLE).";".$player->getRank(WBS_MIXED);
		$kinfo =& $div->addInfo('mberRank', $ranks);
		//$kinfo->setUrl($ut->getParam("ffba_url").$infos['mber_licence']);
		if ($isSquash)  $points = $player->getPoint(WBS_SINGLE);
		else $points = $player->getPoint(WBS_SINGLE).";".$player->getPoint(WBS_DOUBLE).";".$player->getPoint(WBS_MIXED);
		$kinfo =& $div->addInfo('mberCPPP',  $points);

		$items = array();
		$items['btnEdit'] = array(KAF_NEWWIN, 'regi', KID_EDIT,
		$regiId, 650, 450);
		$div->addMenu('menuEdit', $items, -1, 'classMenuBtn');

		$div =& $kdiv->addDiv('playerEntrie', 'blkData');
		$div->addMsg('tPlay', '', 'kTitre');
		$div->addInfo('regiRest', $infos['regi_rest']);
		if ($infos['regi_court'] > 0)
		{
			$div->addInfo('regiUmpire', '');
			$div->addInfo('regiCourt', $infos['regi_court']);
		}
		else if ($infos['regi_court'] < 0)
		{
			$div->addInfo('regiUmpire', -$infos['regi_court']);
			$div->addInfo('regiCourt', '');
		}
		else
		{
			$div->addInfo('regiUmpire', '');
			$div->addInfo('regiPlay', '');
		}
		$div->addInfo('regiWo',      $ut->getLabel($infos['regi_wo']));
		$div->addInfo('regiPresent', $ut->getLabel($infos['regi_present']));
		$div->addInfo('regiDelay',  $infos['regi_delay']);
		$div->addInfo('regiDateWo', $infos['regi_datewo']);

		$items = array();
		$items['btnModify'] = array(KAF_NEWWIN, 'resu', KID_EDIT,
		$infos['regi_id'], 450, 200);
		$div->addMenu('menuChange', $items, -1, 'classMenuBtn');

		$kdiv->addDiv('page', 'blkNewPage');
			
		// display list of the matchs
		$matches = $dt->getMatchs($regiId);
		if (isset($matches['errMsg'])) $kdiv->addWng($matches['errMsg']);
		else
		{
			$divName = '';
			$tieName = '';
			$rows= array();
			foreach($matches as $match)
			{
				if ($divName != $match[1])
				{
					$divName = $match[1];
					$rows[] = array(KOD_BREAK, 'fixTitle', $divName, 'titleRow1');
				}
				if ($tieName != $match[2])
				{
					$tieName = $match[2];
					$rows[] = array(KOD_BREAK, 'fixTitle', $tieName, 'titleRow2');
				}
				unset($match[1]);
				unset($match[2]);
				$rows[] = $match;
			}
			$krow =& $kdiv->addRows("rowsResuTeam", $rows);
			$krow->setSort(0);

			$sizes[6] = '0+';
			$krow->setSize($sizes);

			$img[1]= 14;
			$img[4]= 15;
			$krow->setLogo($img);

			$acts[2] = array(KAF_UPLOAD, 'resu', KID_SELECT,13);
			//$krow->setActions($acts);
		}

		$this->_utpage->display();
		exit();
	}
	// }}}


	// {{{ _displayResuIndiv()
	/**
	 * Display a page with the results of a player for an individual
	 * event.
	 *
	 * @access private
	 * @param
	 * @return void
	 */
	function _displayResuIndiv($regiId)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Create a new page
		$kdiv =& $this->_displayHead('itPlayers');
		//$kdiv =& $content->addDiv('teamDiv', 'cont2');

		$infos = $dt->getPlayer($regiId);
		if (isset($infos['errMsg']))
		{
			$kdiv->addWng($infos['errMsg']);
			$this->_utpage->display();
			exit();
		}
		// Display players list
		$players = $dt->getPlayers();
		$kcombo =& $kdiv->addCombo('selectList', $players, $players[$regiId]);
		$acts[1] = array(KAF_UPLOAD, 'resu', KID_SELECT);
		$kcombo->setActions($acts);


		require_once "utils/objplayer.php";
		$player = new objplayer($regiId);


		//display general informations
		$div =& $kdiv->addDiv('playerName');
		$size['maxWidth'] = 100;
		$size['maxHeight'] = 100;
		$photo = utimg::getPathPhoto($infos['mber_urlphoto']);
		$div =& $kdiv->addDiv('photo');
		$kimg =& $div->addImg("playerPhoto", $photo, $size);

		$div =& $kdiv->addDiv('infos', 'blkData');
		$div->addMsg('tCivil', '', 'kTitre');
		$div->addInfo("mberBorn", $player->getBorn());
		$div->addInfo("mberLicence", $player->getLicence());
		$infoIbf =& $div->addInfo("mberIbfNumber",  $player->getIbfNum());
		$div->addInfo("mberCatage",  $ut->getlabel($player->getCatage()));
		$div->addInfo("mberCompet",  $player->getDateCompet());
		$div->addInfo("mberSurclasse",  $ut->getlabel($player->getSurclasse()));
		$div->addInfo("mberDateSurclasse",  $player->getDateSurclasse());

		$items = array();
		$items['btnPhoto'] = array(KAF_NEWWIN, 'mber', MBER_SELECT_PHOTO,
		$infos['mber_id'], 450, 200);
		$div->addMenu('menuPhoto', $items, -1, 'classMenuBtn');


		$div =& $kdiv->addDiv('regis', 'blkData');
		$div->addMsg('tRegis', '', 'kTitre');
		$kinfo =& $div->addInfo('mberDate', $player->getDate());
		$kinfo =& $div->addInfo('mberAsso', $player->getTeamName());
		$act[1] = array(KAF_UPLOAD, 'teams', KID_SELECT,
		$player->getTeamId());
		$kinfo->setActions($act);

		$ranks = $player->getRank(WBS_SINGLE).";".$player->getRank(WBS_DOUBLE).";".$player->getRank(WBS_MIXED);
		$kinfo =& $div->addInfo('mberRank', $ranks);
		//$kinfo->setUrl($ut->getParam("ffba_url").$infos['mber_licence']);
		$points = $player->getPoint(WBS_SINGLE).";".$player->getPoint(WBS_DOUBLE).";".$player->getPoint(WBS_MIXED);
		$kinfo =& $div->addInfo('mberCPPP',  $points);
		$draws = $player->getDrawStamp(WBS_SINGLE).";".$player->getDrawStamp(WBS_DOUBLE).";".$player->getDrawStamp(WBS_MIXED);
		$kinfo =& $div->addInfo('mberDraws',  $draws);
		$kinfo =& $div->addInfo('mberPartnerD',  $player->getPartnerName(WBS_DOUBLE));
		$acts[1] = array(KAF_UPLOAD, 'resu', KID_SELECT, $player->getPartnerId(WBS_DOUBLE));
		$kinfo->setActions($acts);

		$kinfo =& $div->addInfo('mberPartnerM',  $player->getPartnerName(WBS_MIXED));
		$acts[1] = array(KAF_UPLOAD, 'resu', KID_SELECT, $player->getPartnerId(WBS_MIXED));
		$kinfo->setActions($acts);

		$items = array();
		$items['btnEdit'] = array(KAF_NEWWIN, 'regi', KID_EDIT,
		$regiId, 650, 450);
		$div->addMenu('menuEdit', $items, -1, 'classMenuBtn');

		$div =& $kdiv->addDiv('playerEntrie', 'blkData');
		$div->addMsg('tPlay', '', 'kTitre');
		$div->addInfo('regiRest', $infos['regi_rest']);
		if ($infos['regi_court'] > 0)
		{
			$div->addInfo('regiUmpire', '');
			$div->addInfo('regiCourt', $infos['regi_court']);
		}
		else if ($infos['regi_court'] < 0)
		{
			$div->addInfo('regiUmpire', -$infos['regi_court']);
			$div->addInfo('regiCourt', '');
		}
		else
		{
			$div->addInfo('regiUmpire', '');
			$div->addInfo('regiPlay', '');
		}
		$div->addInfo('regiWo',      $ut->getLabel($infos['regi_wo']));
		$div->addInfo('regiDateWo',  $infos['regi_datewo']);
		$div->addInfo('regiPresent', $ut->getLabel($infos['regi_present']));
		$div->addInfo('regiDelay', $infos['regi_delay']);

		$items = array();
		$items['btnModify'] = array(KAF_NEWWIN, 'resu', KID_EDIT,
		$infos['regi_id'], 450, 200);
		$div->addMenu('menuChange', $items, -1, 'classMenuBtn');

		$kdiv->addDiv('page', 'blkNewPage');

		// display list of the matchs
		$matches = $dt->getMatchsIndiv($regiId);
		if (isset($matches['errMsg']))
		$kdiv->addWng($matches['errMsg']);
		else
		{
			$drawId = -1;
			foreach($matches as $match)
			{
				if ($drawId != $match['draw_id'])
				{
					$rows[] = array(KOD_BREAK, "title", $match['draw_name']);
					$drawId = $match['draw_id'];
				}
				$rows[] = $match;
			}
			$krow =& $kdiv->addRows("rowsResuIndiv", $rows);

			$krow->setSort(0);

			$sizes[12] = '0+';
			$krow->setSize($sizes);

			$img[7]= 'p2m_result';
			$krow->setLogo($img);

			$acts[2] = array(KAF_UPLOAD, 'resu', KID_SELECT,13);
			//$krow->setActions($acts);
		}

		$this->_utpage->display();
		exit();
	}
	// }}}

	// {{{ _displayEdit()
	/**
	 * Display the page updating registration informations
	 *
	 * @access private
	 * @param int  $regiId  info of the registration
	 * @return void
	 */
	function _displayEdit($regiId)
	{
		$dt =& $this->_dt;

		$utpage = new utPage('resu');
		$utpage->_page->addAction('onload', array('resize', 720, 400));
		$content =& $utpage->getPage();

		$form =& $content->addForm('tEditRegi', 'resu', KID_UPDATE);

		$regi = $dt->getPlayer($regiId);
		if (isset($regi['errMsg']))
		{
			$form->addWng($regi['errMsg']);
			$utpage->display();
			exit();
		}

		// Initialize the fields definition member
		$form->addHide('regiId', $regiId);
		$form->addInfo('playerName', $regi['regi_longName']);
		$kedit =& $form->addEdit('regiRest', $regi['regi_rest']);
		$kedit->setMaxLength(25);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('regiDelay', $regi['regi_delay']);
		$kedit->setMaxLength(25);
		$kedit->noMandatory();
		if ($regi['regi_court'] < 0)
		{
			$kedit =& $form->addEdit('umpireCourt', abs($regi['regi_court']));
			$kedit->setMaxLength(25);
			$kedit->noMandatory();
			$kedit =& $form->addEdit('regiCourt', '');
			$kedit->setMaxLength(25);
			$kedit->noMandatory();
		}
		else
		{
			$kedit =& $form->addEdit('umpireCourt', '');
			$kedit->setMaxLength(25);
			$kedit->noMandatory();
			$kedit =& $form->addEdit('regiCourt', $regi['regi_court']);
			$kedit->setMaxLength(25);
			$kedit->noMandatory();
		}

		$kedit =& $form->addEdit('regiCmt', $regi['regi_cmt']);
		$kedit->noMandatory();
		$form->addCheck('regiIsPresent', $regi['regi_present']== WBS_YES);
		$form->addCheck('regiIsWo', $regi['regi_wo']== WBS_YES);
		$kedit =& $form->addEdit('regiDateWo', $regi['regi_datewo']);
		$kedit->noMandatory();
		$elts = array('playerName', 'regiRest', 'regiDelay', 'regiCourt', 'umpireCourt',
		    'regiCmt', 'regiIsWo', 'regiDateWo', 'regiIsPresent');
		$form->addBlock('blkCivil', $elts, 'classCivil');

		$form->addDiv('page', 'blkNewPage');

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);


		$matches = $dt->getMatchsIndiv($regiId, KID_EDIT);
		if (isset($matches['errMsg']))
		$form->addWng($matches['errMsg']);
		else
		{
			$drawId = -1;
			foreach($matches as $match)
			{
				if ($drawId != $match['draw_id'])
				{
					$rows[] = array(KOD_BREAK, "title", $match['draw_name']);
					$drawId = $match['draw_id'];
				}
				$rows[] = $match;
			}
			$krow =& $form->addRows("rowsResuIndiv", $rows);

			$krow->setSort(0);

			$sizes[12] = '0+';
			$krow->setSize($sizes);
			$krow->displaySelect(false);

			$img[7]= 'p2m_result';
			$krow->setLogo($img);
		}

		//Display the form
		$utpage->display();

		exit;
	}
	// }}}

	// {{{ _update()
	/**
	 * Update the registration in the database
	 *
	 * @access private
	 * @return void
	 */
	function _update()
	{
		$dt = $this->_dt;
		$regi = array('regi_delay'   =>kform::getInput('regiDelay'),
		    'regi_rest'    =>kform::getInput('regiRest'),
		    'regi_id'      =>kform::getInput('regiId'),
		    'regi_wo'      =>kform::getInput('regiIsWo', 0),
		    'regi_datewo'  =>kform::getInput('regiDateWo', 0),
		    'regi_present' =>kform::getInput('regiIsPresent', 0),
		    'regi_cmt'     =>kform::getInput('regiCmt', '')
		);

		$regi['regi_court'] = kform::getInput('regiCourt');
		if ($regi['regi_court'] == '')
		$regi['regi_court'] =  -kform::getInput('umpireCourt');
		$regi['regi_wo'] = $regi['regi_wo'] ? WBS_YES :WBS_NO;
		$regi['regi_present'] = $regi['regi_present'] ? WBS_YES :WBS_NO;
		// update the registration
		$res = $dt->update($regi);
		if (is_array($res))
		{
			return $res;
		}

		$page = new utPage('none');
		$page->close();
		exit;
	}
	// }}}


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
		$dt = $this->_dt;

		// Create a new page
		$this->_utpage = new utPage_A('resu', true, 'itRegister');
		$content =& $this->_utpage->getContentDiv();

		// Add a menu for different action
		$kdiv =& $content->addDiv('choix', 'onglet3');
		$items['itTeams']     = array(KAF_UPLOAD, 'teams',  WBS_ACT_TEAMS);
		$items['itPlayers']   = array(KAF_UPLOAD, 'regi', WBS_ACT_REGI);
		$items['itOfficials'] = array(KAF_UPLOAD, 'offo', WBS_ACT_OFFOS);
		$items['itOthers']    = array(KAF_UPLOAD, 'offo', OFFO_OTHER);
		$kdiv->addMenu("menuType", $items, $select);
		$kdiv =& $content->addDiv('register', 'cont3');
		return $kdiv;
	}
	// }}}
}
?>