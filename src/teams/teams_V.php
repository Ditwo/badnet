<?php
/*****************************************************************************
 !   Module     : Teams
 !   File       : $Source: /cvsroot/aotb/badnet/src/teams/teams_V.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.20 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/03/28 21:43:16 $
 ******************************************************************************/
require_once "base_V.php";
require_once "teams.inc";
require_once "utils/utimg.php";
require_once "ties/ties.inc";
require_once "regi/regi.inc";

/**
 * Module de gestion des equipes : classe visiteur
 *
 * @author Gerard CANTEGRIL
 * @see to follow
 *
 */

class teams_V
{

	// {{{ properties

	// }}}

	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function teams_V()
	{
		$this->_ut = new utils();
		$this->_dt = new teamsBase_V();
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
	  // Display details of the team
			case KID_SELECT:
				$id = kform::getData();
				$id = kform::getInput('selectList', $id);
				$this->_displayForm($id);
				break;
				// Pdf editions
			case TEAM_PDF_ASSOPLAYERS:
			case TEAM_PDF_RESULTS:
				require_once "teamsPdf.php";
				$pdf = new teamsPdf();
				$pdf->start($page);
				break;

			default:
				echo "page $page demand�e depuis teams_V<br>";
				exit();
		}
	}
	// }}}

	// {{{ _displayForm()
	/**
	 * Display a page with the player/pairs of the selected team
	 *
	 * @access private
	 * @return void
	 */
	function _displayForm($id)
	{
		if (!utvars::IsTeamEvent())
		$this->_displayFormIndiv($id);
		else
		$this->_displayFormTeam($id);
	}
	//}}}

	// {{{ _displayFormTeam()
	/**
	 * Display the page with the detail of the selected team
	 *
	 * @access private
	 * @param array team  info of the team
	 * @return void
	 */
	function _displayFormTeam($teamId)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Create a new page
		$kdiv =& $this->_displayHead();
		$kdiv->addDiv('headCont3', 'headCont3');
		
		$infos = $dt->getTeam($teamId);

		// Display the error if any
		if (isset($infos['errMsg']))
		{
		$kdiv->addDiv('headCont3', 'headCont3');
		$kdiv->addWng($infos['errMsg']);
	  	unset($infos['errMsg']);
	  	$this->_utpage->display();
	  	exit;
		}
		$teams = $dt->getTeams();
		$kcombo=& $kdiv->addCombo('selectList', $teams, $teams[$teamId]);
		$acts[1] = array(KAF_UPLOAD, 'teams', KID_SELECT);
		$kcombo->setActions($acts);

        $kdiv =& $kdiv->addDiv('corp3', 'corpCont3');
		$div =& $kdiv->addDiv('teamNameV');
		$size['maxWidth'] = 30;
		$size['maxHeight'] = 30;
		if ($infos['team_logo'] == '')
		$logo = utimg::getPathFlag($infos['asso_logo']);
		else
		$logo = utimg::getPathFlag($infos['team_logo']);
		$kimg =& $div->addImg("teamLogo", $logo, $size);

		// Display general informations
		$div =& $kdiv->addDiv('blkTeam', 'blkInfo');
		$div->addInfo("teamStamp",     $infos['team_stamp']);
		$div->addInfo("teamCaptain",   $infos['team_captain']);
		$kinfo =& $div->addInfo("teamUrl", $infos['team_url']);
		$kinfo->setUrl($infos['team_url']);
		$kinfo =& $div->addInfo("teamClub", $infos['asso_name']);
		$kinfo->setUrl($infos['asso_url']);

		// Display the photo
		$div =& $kdiv->addDiv('teamPhotoV');
		$size['maxWidth'] = 200;
		$size['maxHeight'] = 100;
		$photo = utimg::getPathPhoto($infos['team_photo']);
		$kimg =& $div->addImg("teamPhoto", $photo, $size);

		$kdiv->addDiv('break', 'blkNewPage');
		// Display list of tie
		$ties = $dt->getTies($teamId);
		if (isset($ties['errMsg']))
		$kdiv->addWng($ties['errMsg']);
		else
		{
	  $group ="";
	  foreach( $ties as $tie)
	  {
	  	if ($tie[1] != $group)
	  	{
	  		$group = $tie[1];
	  		$rows[] = array(KOD_BREAK, "title", $group);
	  	}
	  	unset($tie[1]);
	  	$rows[] = $tie;
	  }
	  $krow =& $kdiv->addRows("rowsTies", $rows);
	  $krow->setSort(0);
	  $img[4]=10;
	  $krow->setLogo($img);
	  $krow->setNumber(false);
	  $sizes[7] = "0+";
	  $acts = array();
	  //$acts[1] = array(KAF_UPLOAD, 'divs',  KID_SELECT, 9);
	  $acts[4] = array(KAF_UPLOAD, 'teams', KID_SELECT, 8);
	  $acts[6] = array(KAF_UPLOAD, 'ties',  TIES_SELECT_TIE);
	  $krow->setSize($sizes);
	  $krow->setActions($acts);
		}

		// Display list of players
		$sort = kform::getSort("rowsPlayers_V", 3);
		$players = $dt->getPlayers($teamId, $sort);
		if (isset($players['errMsg']))
		$kdiv->addWng($players['errMsg']);
		else
		{
	  $krows =& $kdiv->addRows('rowsPlayers_V', $players);
	  $sizesPlayer[6] = "0+";
	  $krows->setSize($sizesPlayer);
	  $actions[2] = array(KAF_UPLOAD, 'resu', KID_SELECT);
	  $krows->setActions($actions);

	  $img[2]=7;
	  $krows->setLogo($img);

	  $urls[3] = array($ut->getParam("ffba_url"), 4);
	  //$krows->setUrl($urls);
		}

		//Display the page
		$cache = "teams_".KID_SELECT;
		$cache .= "_{$teamId}";
		$sort = kform::getSort("", "");
		if ($sort!="")
		$cache .= "_{$sort}";
		$cache .= ".htm";
		$this->_utpage->display($cache);
		exit;
	}
	// }}}

	// {{{ _displayFormIndiv()
	/**
	 * Display the page with the detail of the selected team
	 *
	 * @access private
	 * @param array team  info of the team
	 * @return void
	 */
	function _displayFormIndiv($assoId)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Create a new page
		$kdiv =& $this->_displayHead();
		
		$khead =& $kdiv->addDiv('headCont3', 'headCont3');
		
		$infos = $dt->getAsso($assoId);
		$this->_utpage->_page->addStyleFile("teams/teams_V.css");
		
		// Display the error if any
		if (isset($infos['errMsg']))
		{
        	$kdiv =& $kdiv->addDiv('corp3', 'corpCont3');
			$kdiv->addWng($infos['errMsg']);
	 	 	unset($infos['errMsg']);
	  		$this->_utpage->display();
	  		exit;
		}

		$assos = $dt->getAssos(2);
		$theDiv = $khead->addForm('theDiv', 'teams', KID_SELECT);
		//$div =& $khead->addDiv('teamNameV');
		$size['maxWidth'] = 30;
		$size['maxHeight'] = 30;
		$logo = utimg::getPathFlag($infos['asso_logo']);
		$kimg =& $theDiv->addImg("assoLogo", $logo, $size);
		$kcombo=& $theDiv->addCombo('selectList', $assos, $assos[$assoId]);
		$acts[1] = array(KAF_UPLOAD, 'teams', KID_SELECT);
		$kcombo->setActions($acts);
		$theDiv->addBtn('Go', KAF_SUBMIT);

		// Display general informations
        $kdiv =& $kdiv->addDiv('corp3', 'corpCont3');
		$div =& $kdiv->addDiv('blkTeam', 'blkInfo');
		$div->addInfo("assoPseudo",     $infos['asso_pseudo']);
		$div->addInfo("assoStamp",     $infos['asso_stamp']);
		$div->addInfo("assoNoc",     $infos['asso_noc']);
		$kinfo =& $div->addInfo("assoUrl", $infos['asso_url']);
		$kinfo->setUrl($infos['asso_url']);

		// Display list of players
		$kdiv->addDiv('break', 'blkNewPage');
		$sort = kform::getSort("rowsPlayers", 3);
		$players = $dt->getAssoPlayers($assoId, $sort);
		if (isset($players['errMsg']))
		{
	  		$kdiv->addWng($players['errMsg']);
	  		unset($players['errMsg']);
		}
		if (count($players))
		{
			$divBtn=& $kdiv->addDiv('divBtn', 'divBtn');
	  		$divBtn->addBtn('itConvocsPdf', KAF_NEWWIN, 'teams',
	  			TEAM_PDF_ASSOPLAYERS, $assoId, 400, 300);
	  		$divBtn->addBtn("itResults", KAF_NEWWIN, 'teams',
				TEAM_PDF_RESULTS, $assoId, 400, 300);
		$divBtn->addDiv('breakbtn', 'blkNewPage');
				
	  		$ute = new utevent();
	  		$convocation = $ute->getConvoc();
	  		$div->addMsg(nl2br($convocation['evnt_textconvoc']));
	  		$krows =& $kdiv->addRows('rowsPlayers', $players);
	  		$actions[2] = array(KAF_UPLOAD, 'resu', KID_SELECT);
	  		$actions[6] = array(KAF_UPLOAD, 'schedu', KID_SELECT, 'place');
	  		$krows->setActions($actions);
		}

		// Display list the pairs
		$pairs = $dt->getPairs($assoId);
		if (isset($pairs['errMsg']))
		$kdiv->addWng($pairs['errMsg']);
		else
		{
	  $disci = -1;
	  foreach($pairs as $pair)
	  {
	  	if ($disci != $pair['draw_disci'])
	  	{
	  		$title = $ut->getLabel($pair['draw_disci']);
	  		$rows[] = array(KOD_BREAK, "title", $title);
	  		$disci = $pair['draw_disci'];
	  	}
	  	$rows[] = $pair;
	  }
	  $krows =& $kdiv->addRows('rowsPairs_V', $rows);
	  $sizes[6] = "0+";
	  $krows->setSize($sizes);
	  $krows->setSort(0);
	  $acts[2] = array(KAF_UPLOAD, 'draws', KID_SELECT, 'draw_id');
	  $krows->setActions($acts);
		}

		//Display the page
		$cache = "teams_".KID_SELECT;
		$cache .= "_{$assoId}";
		$cache .= ".htm";
		$this->_utpage->display($cache);
		exit;
	}
	// }}}

	// {{{ _displayHead()
	/**
	 * Display the header page of a team
	 *
	 * @access private
	 * @param array team  info of the team
	 * @return void
	 */
	function & _displayHead()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Create a new page
		$this->_utpage = new utPage_V('teams', true, 'itRegister');
		$content =& $this->_utpage->getContentDiv();

		// Add a menu for different action
		$kdiv =& $content->addDiv('choix', 'onglet3');
		$items['itTeams']     = array(KAF_UPLOAD, 'regi', WBS_ACT_REGI);
		$items['itPlayers']   = array(KAF_UPLOAD, 'regi', REGI_PLAYER);
		$items['itOfficials'] = array(KAF_UPLOAD, 'regi', REGI_OFFICIAL);
		$kdiv->addMenu("menuType", $items, 'itTeams');
		$kdiv =& $content->addDiv('teamDiv', 'cont3');


		return $kdiv;
	}
	// }}}

}

?>