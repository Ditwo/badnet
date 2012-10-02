<?php
/*****************************************************************************
 !   Module     : Teams
 !   File       : $Source: /cvsroot/aotb/badnet/src/teams/teams_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.37 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:25 $
 ******************************************************************************/
require_once "utils/utpage_A.php";
require_once "utils/utimg.php";
require_once "base_A.php";
require_once "asso/asso.inc";
require_once "teams/teams.inc";
require_once "regi/regi.inc";
require_once "offo/offo.inc";
require_once "pairs/pairs.inc";
require_once "utils/utfile.php";

/**
 * Module de gestion des equipes : classe administrateur
 *
 * @author Gerard CANTEGRIL
 * @see to follow
 *
 */

class teams_A
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
	function teams_A()
	{
		$this->_ut = new utils();
		$this->_dt = new teamsBase_A();
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
	  // Creation d'une nouvelle equipe (tournoi par equipe)
			case KID_NEW:
				$this->_displayNewTeam();
				break;
			case KID_UPDATE:
				$this->_updateNewTeam();
				break;
				// Logical delete a team
			case KID_SELECT:
				$this->_displaySportDetail(kform::getData());
				break;

			case TEAM_SELECT_LOGO:
				$hide['teamId'] = kform::getInput('teamId', -1);
				utimg::selectTeamLogo('teams', TEAM_UPDATE_LOGO, $hide);
				break;

			case TEAM_UPDATE_LOGO:
				$this->_saveLogo();
				break;

			case TEAM_SELECT_PHOTO:
				$hide['teamId'] = kform::getInput('teamId', -1);
				utimg::selectTeamPhoto('teams', TEAM_UPDATE_PHOTO, $hide);
				break;

			case TEAM_UPDATE_PHOTO:
				$this->_savePhoto();
				break;

			case TEAM_MERGE:
				$this->_mergeTeams();
				break;

			case TEAM_MERGE_PLAYERS:
				$this->_mergePlayers();
				break;

				// Logical delete a team
			case KID_DELETE:
				$this->_delTeams(WBS_DATA_DELETE, 'tDelTeams');
				break;
				// Logical undelete a team
			case KID_UNDELETE:
				$this->_delTeams(WBS_DATA_UNDELETE, 'tRestoreTeams');
				break;
				// Physical delete a team
			case KID_ERASE:
				$this->_eraseTeams();
				break;
			case KID_CONFIRM:
				$this->_displayFormConfirm();
				break;

				// Pdf editions
			case TEAM_PDF_PLAYERS:
			case TEAM_PDF_PAIRS:
			case TEAM_PDF_ALLPLAYERS:
			case TEAM_PDF_ALLPAIRS:
			case TEAM_SEND_CONVOC:
			case TEAM_PDF_RESULTS:
			case TEAM_PDF_ENTRIES:
			case TEAM_XLS_ENTRIES:
				require_once "teamsPdf.php";
				$pdf = new teamsPdf();
				$pdf->start($page);
				break;
				 
				// Change the publication status of selected teams
			case KID_CONFIDENT:
				$this->_displayFormPubliTeams(WBS_DATA_CONFIDENT);
				break;
			case KID_PRIVATE:
				$this->_displayFormPubliTeams(WBS_DATA_PRIVATE);
				break;
			case KID_PUBLISHED:
				$this->_displayFormPubliTeams(WBS_DATA_PUBLIC);
				break;
			case TEAM_PUBLISHED:
				$this->_publiTeams();
				break;

			case WBS_ACT_TEAMS:
				$this->_displayFormList();
				break;
				 
			default:
				echo "page $page demandée depuis teams_A<br>";
				exit();
		}
	}
	// }}}


	// {{{ _updateNewTeam()
	/**
	 * Enregitre les donnees d'une equipe
	 *
	 * @access private
	 * @return void
	 */
	function _updateNewTeam()
	{
		$ut =& $this->_ut;
		if ( utvars::isTeamEvent() && $ut->getParam('limitedUsage') )
		$dt =& $this->_dt;
		$team = array('team_name'   =>kform::getInput('teamName'),
		    'team_stamp'  =>kform::getInput('teamStamp'),
		    'team_noc'    =>kform::getInput('teamNoc'),
		    'team_url'    =>kform::getInput('teamUrl'),
		    'team_cmt'    =>kform::getInput('teamCmt'),
		    'team_date'   =>kform::getInput('teamDate'),
		    'team_id'     =>-1,
		    'team_assoId' =>kform::getInput('teamAssoId'),
		    'team_captain'=>kform::getInput('teamCaptain')
		);

		require_once "utils/utteam.php";
		$utt = new utTeam();
		$res= $utt->updateTeam($team);
		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _displayNewTeam()
	/**
	 * Display the page for creating a new team
	 * for team event
	 *
	 * @access private
	 * @return void
	 */
	function _displayNewTeam($err=false)
	{
		 
		$ut =& $this->_ut;
		$dt =& $this->_dt;

		if ( utvars::isTeamEvent() && $ut->getParam('limitedUsage') ) return false;


		$utpage = new utPage('teams');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tNewTeam', 'teams', KID_UPDATE);

		$team=array('team_id'    => kform::getInput('teamId', -1),
		  'team_date'  => kform::getInput('teamDate', date(DATE_DATE)),
		  'team_name'  => kform::getInput('teamName', ''),
		  'team_stamp' => kform::getInput('teamStamp', ''),
		  'team_captain' => kform::getInput('teamCaptain', ''),
		  'team_noc'     => kform::getInput('teamNoc', ''),
		  'team_url'     => kform::getInput('teamUrl', ''),
		  'team_cmt'     => kform::getInput('teamCmt', ''),
		  'team_logo'    => kform::getInput('teamLogo', ''),
		  'team_assoId'  => kform::getInput('teamAssoId', '')
		);
		// Error of the team
		if (isset($team['errMsg']))
		$form->addWng($team['errMsg']);

		// Initialize the field
		$form->addHide('teamId',      $team['team_id']);
		$form->addHide('teamLogo',    $team['team_logo']);
		$kedit =& $form->addEdit('teamDate', $team['team_date'],11);
		$kedit->setMaxLength(10);
		$kedit =& $form->addEdit('teamName',  $team['team_name'], 44);
		$kedit->setMaxLength(50);
		$kedit =& $form->addEdit('teamStamp',  $team['team_stamp'], 44);
		$kedit->setMaxLength(30);

		$assos = $dt->getAssos();
		$assos[''] = '';
		$kcompo =& $form->addCombo('teamAssoId',  $assos, '');

		$kedit =& $form->addEdit('teamCaptain',  $team['team_captain'], 44);
		$kedit->setMaxLength(30);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('teamNoc',  $team['team_noc'], 44);
		$kedit->setMaxLength(15);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('teamUrl',  $team['team_url'], 44);
		$kedit->setMaxLength(200);
		$kedit->noMandatory();
		$karea =& $form->addArea('teamCmt',$team['team_cmt'], 40, 2);
		$karea->noMandatory();
		$size['maxWidth'] = 100;
		$size['maxHeight'] = 50;
		$logo = utimg::getPathFlag($team['team_logo']);
		$form->addImg("assoViewLogoTeam", $logo, $size);
		if ($team['team_id'] != -1)
		$form->addBtn('btnAddLogoTeam', KAF_NEWWIN, 'teams',
		TEAM_SELECT_LOGO,  0, 650, 400);
		$elts = array('teamDate', 'teamName', 'teamStamp', 'teamAssoId',
		    'teamCaptain', 'teamNoc', 'teamUrl', 'teamCmt', 
		    'btnAddLogoTeam', 'assoViewLogoTeam');
		$form->addBlock('blkTeam', $elts);


		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnMoreClub', KAF_UPLOAD, 'asso',
		ASSOC_SEARCH, 0, 480, 580);
		$form->addBtn('btnCancel');
		$elts = array('btnMoreClub', 'btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	/**
	 * Save the photo of a team
	 *
	 * @access private
	 * @return void
	 */
	function _savePhoto()
	{
		$dt = $this->_dt;

		// Control the team id
		$infos['team_id'] = kform::getInput('teamId', -1);
		if ($infos['team_id'] == -1)
		{
	  		$page = new kPage('none');
	  		$page->close();
	  		exit;
		}

		// Verify if the image is local
		$infos['team_photo'] = '';
		$fileObj = kform::getInput('image', NULL);
		$fileName = $fileObj['name'];
		if ($fileName == NULL) $fileName = kform::getData();
		if ($fileName != NULL)
		{
	  		// No local image, so try to dowload it
	  		if (utimg::getPathTeamPhoto($fileName)==false)
	  		{
	  			$up = new  utFile();
	  			$res = $up->upload("image", utimg::getPathTeamPhoto());
	  			if (isset($res['errMsg']))
	  			{
	  				echo $res['errMsg'];
	  				$hide['teamId'] = kform::getInput('teamId', -1);
	  				utimg::selectTeamPhoto('teams', TEAM_UPDATE_PHOTO, $hide);
	  			}
	  			else $fileName= $res['file'];
	  		}
	  		if (utimg::getPathTeamPhoto($fileName) != false) $infos['team_photo'] = $fileName;
		}
		// Update the team
		$res = $dt->updateTeam($infos);
		if (is_array($res)) $infos['errMsg'] = $res['errMsg'];
		$page = new kPage('none');
		$page->close();
		exit;
	}

	/**
	 * Save the logo of a team
	 *
	 * @access private
	 * @return void
	 */
	function _saveLogo()
	{
		$dt = $this->_dt;

		// Control the team id
		$infos['team_id'] = kform::getInput('teamId', -1);
		if ($infos['team_id'] == -1)
		{
	  		$page = new kPage('none');
	  		$page->close();
	  		exit;
		}

		// Verify if the image is local
		$infos['team_logo'] = '';
		$fileObj = kform::getInput('image', NULL);
		$fileName = $fileObj['name'];
		if ($fileName == NULL) $fileName = kform::getData();
		if ($fileName != NULL)
		{
	  // No local image, so try to dowload it
	  		if (utimg::getPathTeamLogo($fileName)==false)
	  		{
	  			$up = new  utFile();
	  			$res = $up->upload("image", utimg::getPathTeamLogo());
	  			if (isset($res['errMsg']))
	  			{
	  				echo $res['errMsg'];
	  				$hide['teamId'] = kform::getInput('teamId', -1);
	  				utimg::selectTeamLogo('teams', TEAM_UPDATE_LOGO, $hide);
	  			}
	  			else $fileName = $res['file'];
	  		}
	  		if (utimg::getPathTeamLogo($fileName)!=false) $infos['team_logo'] = $fileName;
		}
		// Update the team
		$res = $dt->updateTeam($infos);
		if (is_array($res))
		{
			$infos['errMsg'] = $res['errMsg'];
		}
		$page = new kPage('none');
		$page->close();
		exit;
	}

	// {{{ _displaySportDetail()
	/**
	 * Display the page with the detail of the selected team
	 *
	 * @access private
	 * @param array team  info of the team
	 * @return void
	 */
	function _displaySportDetail($teamId, $err='')
	{
		$dt =& $this->_dt;
		$content =& $this->_displayHead('itTeams');
		$this->_utpage->_page->addAction('onload', array('initForm', 'formTeam'));
		$this->_utpage->_page->addJavaFile('teams/teams.js');

		$team = $dt->getTeam($teamId);
		if (isset($team['errMsg']))
		$content->addWng($team['errMsg']);

		// Afficher l'indicateur de publication
		$size['maxWidth'] = 30;
		$size['maxHeight'] = 30;
		$logo = utimg::getPubliIcon($team['team_del'],
		$team['team_pbl']);
		$content->addImg('state', $logo, $size);

		// Afficher le logo de l'equipe
		$size['maxWidth'] = 30;
		$size['maxHeight'] = 30;
		$logo = utimg::getPathTeamLogo($team['team_logo']);
		if (!@getImagesize($logo))
		{
	  $logo = utimg::getPathFlag($team['asso_logo']);
	  $content->addWng("msgNoTeamLogo");
		}
		$content->addImg("logo", $logo, $size);

		// Afficher la liste deroulante des equipes
		$form =& $content->addForm('formPlus');
		if (utvars::isTeamEvent())
		$form->addBtn('btnNew', KAF_NEWWIN, 'teams', KID_NEW,  0, 450, 350);
		else
		$form->addBtn('btnNew', KAF_NEWWIN, 'asso', ASSOC_SEARCH,  0, 480, 580);

		$teams = $dt->getTeams(2);
		foreach($teams as $row)
		$list[$row['team_id']] = "{$row['asso_pseudo']} : {$row['team_name']} ({$row['team_stamp']})";
		$kcombo=& $form->addCombo('selectList', $list, $list[$teamId]);
		$acts[1] = array(KAF_UPLOAD, 'teams', KID_SELECT);
		$kcombo->setActions($acts);

		// Bouton d'expansion
		$form->addBtn('btnPlus', 'mask', 'formTeam');

		// Display general informations
		$form =& $content->addForm('formTeam', 'teams', KID_UPDATE);
		$form->addEdit('teamDate',    $team['team_date'], 40);
		$form->addEdit('teamName',    $team['team_name'], 40);
		$form->addEdit('teamStamp',   $team['team_stamp'], 40);
		$kedit =& $form->addEdit('teamNoc',     $team['team_noc'], 40);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('teamCaptain', $team['team_captain'], 40);
		$kedit->noMandatory();
		$karea =& $form->addArea('teamCmt',     $team['team_cmt'], 37);
		$karea->noMandatory();
		$karea =& $form->addArea('teamText',    $team['team_textconvoc'], 37);
		$karea->noMandatory();
		$kedit =& $form->addEdit('teamUrl',     $team['team_url'], 40);
		$kedit->noMandatory();
		$elts =array('teamDate', 'teamName', 'teamStamp', 'teamNoc',
		    'teamCaptain', 'teamCmt', 'teamText', 'teamUrl');
		$form->addBlock('blkInfo', $elts);
		//$kinfo->setUrl($team['team_url']);

		$form->addBtn('btnModify', 'activate', 'formTeam', 'teamName');
		$form->addBtn('btnMoins', 'mask', 'formTeam');
		$elts =array('btnModify','btnMoins');
		$form->addBlock('divModif', $elts);

		$form->addBtn('btnRegister', KAF_AJAJ, 'endSubmit', WBS_ACT_TEAMS);
		$form->addBtn('btnCancel', 'cancel', 'formTeam');
		$form->addBtn('btnAddLogo', KAF_NEWWIN, 'teams', TEAM_SELECT_LOGO,  0, 650, 400);
		$form->addBtn('btnAddPhoto', KAF_NEWWIN, 'teams', TEAM_SELECT_PHOTO, 0, 650, 400);
		$elts =array('btnRegister','btnCancel', 'btnAddLogo', 'btnAddPhoto');
		$form->addBlock('divValid', $elts);

		// Display the photo
		$div =& $content->addDiv('teamPhotoV');
		$size['maxWidth'] = 200;
		$size['maxHeight'] = 100;
		$photo = utimg::getPathTeamPhoto($team['team_photo']);
		$div->addImg("teamPhoto", $photo, $size);

		$content->addDiv('page', 'blkNewPage');

		// Display menu for manage members
		$itsm['itNewPlayer'] = array(KAF_NEWWIN, 'regi', REGI_SEARCH_TEAM, $teamId);
		$itsm['itNewOfficial'] = array(KAF_NEWWIN, 'offo', OFFO_NEW_OFFICIAL_FROM_TEAM, $teamId, 650, 310);
		$itsm['itNewOther'] = array(KAF_NEWWIN, 'offo', OFFO_NEW_OTHER_FROM_TEAM, $teamId, 650, 310);
		$itsm['itUpdate'] = array(KAF_NEWWIN, 'regi', REGI_GET_FFBA, $teamId, 300, 100);
		//$itsm['itUpdateSept'] = array(KAF_NEWWIN, 'regi', REGI_GET_FFBA_SEPT,
		//				    0, 150, 150);
		if (!utvars::isTeamEvent())
		{
	  $itsm['itPdfPlayers'] = array(KAF_NEWWIN, 'teams', TEAM_PDF_PLAYERS,
	  $teamId, 400, 400);
	  $itsm['itPdfPairs'] = array(KAF_NEWWIN, 'teams', TEAM_PDF_PAIRS,
	  $teamId, 400, 400);
	  $itsm['itPdfEntries'] = array(KAF_NEWWIN, 'teams', TEAM_PDF_ENTRIES,
	  $teamId, 400, 400);
		}
		$itsm['itErase'] = array(KAF_NEWWIN, 'regi', KID_CONFIRM,0,250,150);
		if (utvars::isTeamEvent())
		$itsm['itMerge'] = array(KAF_NEWWIN, 'teams', TEAM_MERGE_PLAYERS,
		0,250,150);
		else
		$itsm['itResults'] = array(KAF_NEWWIN, 'teams',
		TEAM_PDF_RESULTS, $team['asso_id'], 400, 300);
		$itsp['itPublic']    = array(KAF_NEWWIN, 'offo',
		KID_PUBLISHED, 0, 300, 150);
		$itsp['itPrivate']   = array(KAF_NEWWIN, 'offo',
		KID_PRIVATE, 0, 300, 150);
		$itsp['itConfident'] = array(KAF_NEWWIN, 'offo',
		KID_CONFIDENT, 0, 300, 150);

		$form =& $content->addForm('fteams');
		$form->addMenu("menuLeft", $itsm, -1);
		$form->addMenu("menuRight", $itsp, -1);

		$form->addDiv('page', 'blkNewPage');

		if (isset($err['errMsg']))
		$form->addWng($err['errMsg']);

		$form->addHide('teamId', $teamId);

		// Display list of players
		$sort = kform::getSort("rowsPlayers", 2);
		$players = $dt->getPlayers($teamId, $sort);
		if (isset($players['errMsg']))
		{
	  		$form->addWng($players['errMsg']);
	  		unset($players['errMsg']);
		}
		if (count($players))
		{
	  $krows =& $form->addRows('rowsPlayers', $players);

	  // Pour un tournoi non individuel, masquer les colonnes
	  // convocations
	  if (utvars::isTeamEvent()) $sizes[9] = "0+";
	  else 
	  {
	  	$sizes[6] = 0;
	  	$sizes[12] = "0+";
	  }
	  $krows->setSize($sizes);

	  $ellog[1]='11';
	  $ellog[7]='18';
	  $krows->setLogo($ellog);
	   
	  $actions[0] = array(KAF_NEWWIN, 'regi', KID_EDIT, 0, 720, 400);
	  $actions[1] = array(KAF_UPLOAD, 'resu', KID_SELECT);
	  $actions[11] = array(KAF_UPLOAD, 'schedu', KID_SELECT, 10);
	  $krows->setActions($actions);
		}

		// Display list of officials
	  	$sort = kform::getSort("rowsOffos", 1);
	  	$officials = $dt->getOfficials($teamId, $sort);
	  	if (isset($officials['errMsg']))
	  	{
	  		$form->addWng($officials['errMsg']);
	  		unset($officials['errMsg']);
	  	}
	  	if (count($officials))
	  	{
	  		$krows =& $form->addRows('rowsOffos', $officials);
	  	 
	  		$sizes[6] = "0+";
	  		$krows->setSize($sizes);
	  	 
	  		$logo = array(1=>'iconPbl');
	  		$krows->setLogo($logo);
	  		$actions = array();
	  		$actions[0] = array(KAF_NEWWIN, 'offo', KID_EDIT, 0, 550, 300);
	  		$krows->setActions($actions);
	  	}

		// Display list of tie
		if (utvars::isTeamEvent())
		{
	  		$ties = $dt->getTies($teamId);
	  		if (isset($ties['errMsg'])) $form->addWng($ties['errMsg']);
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
	  			$krow =& $form->addRows("rowsTies", $rows);
	  			$krow->setSort(0);
	  			$img[4]=10;
	  			$krow->setLogo($img);
	  			$krow->setNumber(false);
	  			$sizes =array();
	  			$sizes[7] = "0+";
	  			$acts = array();
	  	//		$acts[1] = array(KAF_UPLOAD, 'divs',  KID_SELECT, 9);
	  			$acts[4] = array(KAF_UPLOAD, 'teams', KID_SELECT, 8);
	  			$acts[6] = array(KAF_UPLOAD, 'ties',  KID_SELECT);
	  			$krow->setSize($sizes);
	  			$krow->setActions($acts);
	  		}
		}
		else
		{
	  		$ute = new utevent();
	  $event = $ute->getEvent(utvars::getEventId());
	  if ($event['evnt_urlrank'] == '')
	  {
	  	$ut = new utils();
	  	$event['evnt_urlrank'] = $ut->getParam('ffba_url');
	  }
	   
	  // Legend
	  $form->addImg('lgdConfident', utimg::getIcon(WBS_DATA_CONFIDENT));
	  $form->addImg('lgdPrivate', utimg::getIcon(WBS_DATA_PRIVATE));
	  $form->addImg('lgdPublic', utimg::getIcon(WBS_DATA_PUBLIC));
	  //$form->addImg('lgdDelete', utimg::getIcon(WBS_DATA_DELETE));
	  $elts=array('lgdConfident', 'lgdPrivate', 'lgdPublic', 'lgdDelete');
	  $form->addBlock("blkLegende", $elts);
	   
	  // Display list the pairs
	  $ut = new utils();
	  $sort = kform::getSort("rowsPlayers_V", 3);
	  $pairs = $dt->getPairs($teamId, $sort);
	  if (isset($pairs['errMsg']))
	  {
	  	$form->addWng($pairs['errMsg']);
	  	unset($pairs['errMsg']);
	  }
	  $disci = -1;
	  $rows = array();
	  foreach($pairs as $pair)
	  {
	  	if ($disci != $pair['draw_disci'])
	  	{
	  		$disci = $pair['draw_disci'];
	  		$title = $ut->getLabel($pair['draw_disci']);
	  		if ($disci != WBS_MS && $disci != WBS_WS)
	  		$rows[] = array(KOD_BREAK, "title", $title,'',$disci);
	  		else
	  		$rows[] = array(KOD_BREAK, "title", $title);
	  	}
	  	$rows[] = $pair;
	  }
	  $krows =& $form->addRows('rowsPairs', $rows);
	  $view[8] = "0+";
	  $krows->setSize($view);

	  $img[1]='pair_stateLogo';
	  $krows->setLogo($img);
	  $actions = array();
	  $actions[0] = array(KAF_NEWWIN, 'pairs', KID_EDIT, 0,
	  650, 480);
	  //$actions[3] = array(KAF_NEWWINURL, $event['evnt_urlrank'], 0, 0, 650, 450);
	  $krows->setActions($actions);
	   
	  $acts = array();
	  $acts[] = array('link' => array(KAF_NEWWIN, 'pairs', PAIR_IMPLODE, 0, 450, 180),
			  'icon' => utimg::getIcon(PAIR_IMPLODE),
			  'title' => PAIR_IMPLODE); //'R�unir');
	  $acts[] = array('link' => array(KAF_NEWWIN, 'pairs', PAIR_EXPLODE, 0, 400, 180),
			  'icon' => utimg::getIcon(PAIR_EXPLODE),
			  'title' => PAIR_EXPLODE);//'S�parer');
	  $krows->setBreakActions($acts);
		}
		//Display the page
		$this->_utpage->display();
		exit;
	}
	// }}}

	// {{{ _mergePlayers()
	/**
	 * Merge the selected players
	 *
	 * @access private
	 * @param  integer $isDelete  new status of the event
	 * @return void
	 */
	function _mergePlayers()
	{
		$dt = $this->_dt;

		// Get the id's of the playrs
		$playersId = kform::getInput("rowsPlayers", array());
		$res['errMsg'] = 'msgNeedTwoPlayers';

		// Merge the players
		if (count($playersId) == 2)
		$res = $dt->mergePlayers($playersId);

		$utpage = new utPage('teams');
		if (isset($res['errMsg']))
		{
	  $content =& $utpage->getPage();
	  $form =& $content->addForm('fTeams', 'teams');
	  $form->setTitle('tMergePlayers');
	  $form->addWng($res['errMsg']);
	  $form->addBtn('btnCancel');
	  $elts = array('btnCancel');
	  $form->addBlock('blkBtn', $elts);
	  $utpage->display();
		}
		else
		$utpage->close();
		exit;
	}
	// }}}


	// {{{ _mergeTeams()
	/**
	 * Merge the selected teams in the database
	 *
	 * @access private
	 * @param  integer $isDelete  new status of the event
	 * @return void
	 */
	function _mergeTeams()
	{
		$dt = $this->_dt;

		// Get the id's of the teams to delete
		$teamsId = kform::getInput("rowsTeams", array());

		// Merge the teams
		if (count($teamsId))
		$dt->mergeTeams($teamsId);

		$page = new utPage('none');
		$page->close();
		exit;
	}
	// }}}


	// {{{ _delTeams()
	/**
	 * Delete the selected teams in the database
	 *
	 * @access private
	 * @param  integer $isDelete  new status of the event
	 * @return void
	 */
	function _delTeams($isDelete, $titre)
	{
		$dt = $this->_dt;

		$utpage = new utPage('teams');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tTeams', 'teams', KID_NONE);
		$form->setTitle($titre);

		// Get the id's of the teams to delete
		$teamsId = kform::getInput("rowsTeams");

		// Delete the teams
		if ($teamsId == '')
		$form->addWng('msgNeedTeams');
		else
		{
	  $res = $dt->delTeams($teamsId, $isDelete);
	  if (isset($res['errMsg']))
	  $form->addWng($res['errMsg']);
	  else
	  $content->close();
		}
		//Display the page
		$form->addBtn('btnCancel');
		$elts = array('btnCancel');
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit;
	}
	// }}}


	// {{{ _publiTeams()
	/**
	 * Change the publication state of the selected teams in the database
	 *
	 * @access private
	 * @param  integer $publi  new publication state of the event
	 * @return void
	 */
	function _publiTeams()
	{
		require_once "utils/utpubli.php";
		$utp = new utpubli();

		// Get the id's of the teams to delete
		$teamsId = kform::getInput("rowsTeams", NULL);
		$publi = kform::getInput("publiType");
		$propa = kform::getInput("publiPropa");

		// Publishing the teams
		$utp->publiTeam($teamsId, $publi, $propa);

		$page = new utPage('none');
		$page->close();
		exit;
	}
	// }}}


	// {{{ _displayFormPubliTeams()
	/**
	 * Change the publication state of the selected teams in the database
	 *
	 * @access private
	 * @param  integer $publi  new publication state of the event
	 * @return void
	 */
	function _displayFormPubliTeams($publi, $err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('teams');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fTeams', 'teams', TEAM_PUBLISHED);
		$form->setTitle('tPubliTeams');

		// Get the id's of the teams to publish
		$teamId = kform::getData();
		if (is_null($teamId) || empty($teamId))
		$teamsId = kform::getInput("rowsTeams");
		else
		$teamsId[] = $teamId;

		// Publish the teams
		if ($err =='' && $teamsId != '')
		{
	  foreach($teamsId as $id)
	  $form->addHide("rowsTeams[]", $id);
	  $form->addHide('publiType', $publi);
	  $form->addWng('msgConfirmPubTeam');
	  $form->addCheck('publiPropa', false);
	  $form->addBtn('btnModify', KAF_SUBMIT);
		}
		else
		{
	  if ($err != '')
	  $form->addWng($err);
	  else
	  $form->addWng('msgNeedTeams');
		}
		//Display the page
		$form->addBtn('btnCancel');
		$elts = array('btnModify', 'btnCancel');
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormList()
	/**
	 * Display a page with the list of the teams
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormList($err='')
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		 
		if ( utvars::isTeamEvent() && $ut->getParam('limitedUsage') )
		{
			$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']
			. '?bnAction=525824';
			header("Location: $url");
		}
		 

		// Creating a new page
		$content =& $this->_displayHead('itTeams');
		$form =& $content->addForm('fteams');

		if ($err!= '')
		$form->addWng($err);

		// Menu for management of serial
		if (utvars::isTeamEvent())
		$itsm['itNew'] = array(KAF_NEWWIN, 'teams', KID_NEW,  0, 450, 350);
		else
		$itsm['itNew'] = array(KAF_NEWWIN, 'asso', ASSOC_SEARCH,  0, 480, 580);

		//$itsm['itDelete'] = array(KAF_NEWWIN, 'teams', KID_DELETE, 0, 300, 150);
		//$itsm['itRestore'] = array(KAF_NEWWIN, 'teams',  KID_UNDELETE, 0, 300, 150);
		$itsm['itErase'] = array(KAF_NEWWIN, 'teams', KID_CONFIRM, 0, 300, 150);
		if (!utvars::isTeamEvent())
		{
	  $itsm['itMerge'] = array(KAF_NEWWIN, 'teams', TEAM_MERGE, 0, 300, 150);
	   
	  $itsm['itPdfPlayers'] = array(KAF_NEWWIN, 'teams', TEAM_PDF_ALLPLAYERS, 0, 500, 400);
	  $itsm['itPdfPairs'] = array(KAF_NEWWIN, 'teams', TEAM_PDF_ALLPAIRS, 0, 500, 400);
	  $itsm['itXlsEntries'] = array(KAF_NEWWIN, 'teams',
	  TEAM_XLS_ENTRIES, 0, 500, 400);
		}

		$itsp['itPublic']    = array(KAF_NEWWIN, 'teams',
		KID_PUBLISHED, 0, 300, 150);
		$itsp['itPrivate']   = array(KAF_NEWWIN, 'teams',
		KID_PRIVATE, 0, 300, 150);
		$itsp['itConfident'] = array(KAF_NEWWIN, 'teams',
		KID_CONFIDENT, 0, 300, 150);
		$form->addMenu("menuLeft", $itsm, -1);
		$form->addMenu("menuRight", $itsp, -1);
		$form->addDiv('break', 'blkNewPage');

		// List of teams
		$sort = kform::getSort("rowsTeams",2);
		$rows = $dt->getTeams($sort);
		if (isset($rows['errMsg']))
		$form->addWng($rows['errMsg']);
		else
		{
	  $krow =& $form->addRows("rowsTeams", $rows);
	  $sizes[6] = '0+';
	  $krow->setSize($sizes);

	  $img[1]='iconPbl';
	  $krow->setLogo($img);

	  $actions[0] = array(KAF_NEWWIN, 'asso', KID_EDIT, 'team_id',
	  450, 450);
	  $actions[2] = array(KAF_UPLOAD, 'teams', KID_SELECT, 'team_id');
	  $krow->setActions($actions);
		}

		// Legend
		$form->addImg('lgdConfident', utimg::getIcon(WBS_DATA_CONFIDENT));
		$form->addImg('lgdPrivate', utimg::getIcon(WBS_DATA_PRIVATE));
		$form->addImg('lgdPublic', utimg::getIcon(WBS_DATA_PUBLIC));
		$form->addImg('lgdDelete', utimg::getIcon(WBS_DATA_DELETE));
		$elts=array('lgdConfident', 'lgdPrivate', 'lgdPublic', 'lgdDelete');
		$form->addBlock("blkLegende", $elts);

		$this->_utpage->display();
		exit;
	}
	// }}}

	// {{{ _eraseTeam()
	/**
	 * Erase the selected teams
	 *
	 * @access private
	 * @return void
	 */
	function _eraseTeams()
	{
		$dt = $this->_dt;

		// Get the informations
		$teamsId = kform::getInput("rowsTeams");

		// Update the team
		$res= $dt->eraseTeams($teamsId);
		if (is_array($res))
		{
			$this->_displayFormConfirm($res['errMsg']);
		}
		// All is OK. Close the window
		$page = new kPage('none');
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
		$this->_utpage = new utPage_A('teams', true, 'itRegister');
		$content =& $this->_utpage->getContentDiv();

		// Add a menu for different action
		$kdiv =& $content->addDiv('choix', 'onglet3');
		//if(utvars::isLineRegi())
		$items['itLine']    = array(KAF_UPLOAD, 'line',  WBS_ACT_LINE);
		//$items['itClub']     = array(KAF_UPLOAD, 'asso',WBS_ACT_ASSOC);
		$items['itTeams']     = array(KAF_UPLOAD, 'teams',WBS_ACT_TEAMS);
		$items['itPlayers']   = array(KAF_UPLOAD, 'regi', WBS_ACT_REGI);
		$items['itOfficials'] = array(KAF_UPLOAD, 'offo', WBS_ACT_OFFOS);
		$items['itOthers']    = array(KAF_UPLOAD, 'offo', OFFO_OTHER);
		$kdiv->addMenu("menuType", $items, $select);
		$kdiv =& $content->addDiv('register', 'cont3');
		return $kdiv;
	}
	// }}}

	// {{{ _displayFormConfirm()
	/**
	 * Display the page for confirmation the destruction of teams
	 *
	 * @access private
	 * @param array $member  info of the member
	 * @return void
	 */
	function _displayFormConfirm($err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('teams');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tEraseTeams', 'teams', KID_ERASE);

		// Initialize the field
		$teamsId = kform::getInput("rowsTeams");
		if ($err =='' && $teamsId != '')
		{
	  foreach($teamsId as $id)
	  $form->addHide("rowsTeams[]", $id);
	  $form->addMsg('msgConfirmDelTeam');
	  $form->addBtn('btnDelete', KAF_SUBMIT);
		}
		else
		{
	  if($err!='')
	  $form->addWng($err);
	  else
	  $form->addWng('msgNeedTeams');
		}
		$form->addBtn('btnCancel');
		$elts = array('btnDelete', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

}
?>
