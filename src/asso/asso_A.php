<?php
/*****************************************************************************
 !   Module     : Associations
 !   File       : $Source: /cvsroot/aotb/badnet/src/asso/asso_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.16 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/01/18 07:51:18 $
 ******************************************************************************/

require_once "utils/utpage_A.php";
require_once "utils/utclubs.php";
require_once "regi/regi.inc";
require_once "mber/mber.inc";
require_once "teams/teams.inc";
require_once "purchase/purchase.inc";
require_once "base_A.php";
require_once "asso.inc";
require_once "utils/utfile.php";

/**
 * Module de gestion des associations : classe visiteurs
 *
 * @author Gerard CANTEGRIL
 * @see to follow
 *
 */

class asso_A
{

	// {{{ properties
	/**
	 * Tools access object
	 *
	 * @var     object
	 * @access  private
	 */
	var $_utPage;

	// }}}

	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function asso_A()
	{
		$this->_ut = new utils();
		$this->_dt = new assoBase_A();
		$this->_cl = new Utclubs();
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
	  // Fenetre de recherche d'une association
			case ASSOC_SEARCH:
				$this->_displaySearch();
				break;

				// Recherche des associations correspondantes
			case ASSOC_FIND:
				$this->_findAssoc();
				break;

				// Creation d'une association
			case KID_NEW:
				$id = kform::getData();
				$this->_newAsso($id);
				break;

				// Modification d'une paire(assoc+equipe) a partir
				// de l'id de l'equipe
			case KID_EDIT:
				$id = kform::getData();
				$this->_editAsso($id);
				break;

				// Enregistrement des modif Assoc+ equipe
			case KID_UPDATE:
				$this->_registerAsso();
				break;

				// Display main windows for association
			case KID_CONFIRM:
				$this->_displayFormConfirm();
				break;
			case KID_DELETE:
				$this->_delTeams();
				break;

			case ASSOC_SELECT_LOGO:
				$hide['assoId'] = kform::getInput('assoId', -1);
				utimg::selectAssoLogo('asso', ASSOC_UPDATE_LOGO, $hide);
				break;

			case ASSOC_UPDATE_LOGO:
				$this->_saveLogo();
				break;

			default:
				echo "page $page demand�e depuis asso_A<br>";
				exit();
		}
	}
	// }}}

	// {{{ _saveLogo()
	/**
	 * Save the logo of a team
	 *
	 * @access private
	 * @return void
	 */
	function _saveLogo()
	{
		$dt =& $this->_dt;

		// Control the team id
		$infos['asso_id'] = kform::getInput('assoId', -1);
		if ($infos['asso_id'] == -1)
		{
	  $page = new kPage('none');
	  $page->close();
	  exit;
		}

		// Verify if the image is local
		$infos['asso_logo'] = '';
		$fileObj = kform::getInput('image', NULL);
		$fileName = $fileObj['name'];
		if ($fileName == NULL)
		$fileName = kform::getData();
		if ($fileName != NULL)
		{
	  // No local image, so try to dowload it
	  $res = utimg::getPathFlag($fileName);
	  echo "res=$res<br>";
	  if ( $res == false)
	  {
	  	$up = new  utFile();
	  	$res = $up->upload("image", utimg::getPathFlag());
	  	if (isset($res['errMsg']))
	  	{
	  		echo $res['errMsg'];
	  		$hide['assoId'] = kform::getInput('assoId', -1);
	  		utimg::selectAssoLogo('asso', ASSOC_UPDATE_LOGO, $hide);
	  	}
	  	else
	  	$fileName = $res['file'];
	  }
	  if (utimg::getPathFlag($fileName)!= false)
	  $infos['asso_logo'] = $fileName;
		}
		// Update the event
		$res = $dt->updateAsso($infos);
		if (is_array($res))
		{
			$infos['errMsg'] = $res['errMsg'];
		}
		$page = new kPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _editAsso()
	/**
	 * Modification d'une inscription association+equipe
	 *
	 * @access private
	 * @return void
	 */
	function _editAsso($teamId)
	{
		$dt =& $this->_dt;

		// Donnees de l'equipe
		$team = $dt->getTeam($teamId);

		// Donnees de l'association
		$asso = $dt->getAsso($team['asso_id']);
		if ($asso['asso_lockid'] == 0)
		$asso['asso_lockid'] = utvars::getEventId();

		$contact = $dt->getContact($asso['asso_id']);

		// Affichage de la fentre de saisie
		$this->_displayRegisterAsso($asso, $team, $contact);
	}
	// }}}


	// {{{ _newAsso()
	/**
	 * Creation d'une nouvelle paire association+equipe
	 *
	 * @access private
	 * @return void
	 */
	function _newAsso($assoId)
	{
		// Identifiants
		list($localId, $fedeId) = explode(';', $assoId);
		$contact['ctac_id'] = -1;
		$contact['ctac_assocId'] = 0;
		$contact['ctac_type'] = WBS_EMAIL;
		$contact['ctac_value'] = '';
		$contact['ctac_contact'] = '';

		// Recherche de l'association dans la base locale
		$dt =& $this->_dt;
		$asso = $dt->getAsso($localId, $fedeId);
		// Recherche dans la base Poona
		if ( empty($asso) && $fedeId > 0)
		{
			require_once "import/imppoona.php";
			$poona = new ImpPoona();
			$asso = $poona->getInstance($fedeId);
			if ( $asso['asso_fedeId'] != -1 )
			{
				$asso['asso_lockid'] = -1;
			}
			else
			unset($asso);
		}
		// Si echec, nouvelle asso
		if ( empty($asso) )
		{
			$asso = array('asso_name'   =>kform::getInput('assoName'),
'asso_pseudo' =>kform::getInput('assoPseudo'),
'asso_stamp'  =>kform::getInput('assoStamp'),
'asso_type'   =>kform::getInput('assoTypeSearch'),
'asso_dpt'    =>kform::getInput('assoDpt'),
'asso_cmt'    =>'',
'asso_noc'    =>'FRA',
'asso_logo'   =>'',
'asso_id'     =>-1,
'asso_fedeId' =>-1,
'asso_url'    =>'',
'asso_number' =>'',
'asso_lockid' => utvars::getEventId(),
			);
		}

		if ($asso['asso_type'] == '') $asso['asso_type'] = WBS_CLUB;
		if ($asso['asso_type'] != WBS_CLUB)	$asso['asso_dpt'] = '';

		$team['team_id'] = -1;
		$team['team_name'] = $asso['asso_name'];
		$team['team_stamp'] = $asso['asso_stamp'];
		$team['team_captain'] = '';
		$team['team_url']   = $asso['asso_url'];
		$team['team_noc']   = $asso['asso_noc'];
		$team['team_date']  = date(DATE_DATE);
		$team['team_cmt']   = '';
		$team['team_logo']  = '';
		//$team['psit_texte'] = '';
		//$team['cmd_value']  = '';

		// Affichage de la fenetre de saisie
		// Pour un tournoi par equipe, forcer
		// l'affichage de la partie equipe
		// en positionnant un nom d'equipe different
		// du nom de l'association
		if (utvars::isTeamEvent())
		$team['team_name'] .= " 1";

		$this->_displayRegisterAsso($asso, $team, $contact);
	}
	// }}}

	// {{{ _findAssoc()
	/**
	 * Lance la recherche des associations correspondantes
	 * aux criteres de recherche puis affiche le resultat.
	 * Les associations trouvees sont stocke dans une table temporaire
	 *
	 * @access private
	 * @return void
	 */
	function _findAssoc()
	{
		$dt =& $this->_dt;
		$asso = array('asso_name'   =>kform::getInput('assoName'),
		    'asso_pseudo' =>kform::getInput('assoPseudo'),
		    'asso_stamp'  =>kform::getInput('assoStamp'),
		    'asso_dpt'    =>kform::getInput('assoDpt'),
		    'asso_type'   =>kform::getInput('assoTypeSearch'));
		$sort = kform::getSort("rowsSearch", 1);
		$res = $dt->searchAssos($asso, $sort);
		$this->_displaySearch($res);
	}
	// }}}

	// {{{ _displaySearch()
	/**
	 * Search asociation in the database and display the result
	 *
	 * @access private
	 * @return void
	 */
	function _displaySearch($assos = array())
	{
		$dt =& $this->_dt;
		$cl =& $this->_cl;

		$utpage = new utPage('asso');
		$utpage->_page->addAction('onload', array('searchClub'));
		$utpage->_page->addJavaFile('asso/asso.js');
		$content =& $utpage->getPage();

		$asso = array('asso_name'   =>kform::getInput('assoName'),
		    'asso_pseudo' =>kform::getInput('assoPseudo'),
		    'asso_stamp'  =>kform::getInput('assoStamp'),
		    'asso_dpt'    =>kform::getInput('assoDpt', ''),
		    'asso_type'   =>kform::getInput('assoTypeSearch', WBS_CLUB));
		$form =& $content->addForm('tSearchResult', 'asso', ASSOC_FIND);
		$form->addBtn('btnNew', KAF_VALID, 'asso', KID_NEW, '-1;-1', 480, 420);
		$form->addBtn('btnSearch', KAF_SUBMIT);

		$kedit =& $form->addEdit('assoName',  kform::getInput('assoName'), 34);
		$kedit->setMaxLength(100);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('assoPseudo', $asso['asso_pseudo'],34);
		$kedit->setMaxLength(20);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('assoStamp', $asso['asso_stamp'],34);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$tmp[''] = "----";
		$lst = utvars::getDepartements();
		$lst = $tmp+$lst;
		$kedit =& $form->addCombo('assoDpt', $lst, $lst[$asso['asso_dpt']]);
		$kedit->noMandatory();

		$ut =& $this->_ut;
		$types = array( 0 => '----',
		WBS_FEDE    => $ut->getLabel(WBS_FEDE),
		WBS_CLUB    => $ut->getLabel(WBS_CLUB),
		WBS_UMASSO  => $ut->getLabel(WBS_UMASSO),
		WBS_LIGUE   => $ut->getLabel(WBS_LIGUE),
		WBS_CODEP   => $ut->getLabel(WBS_CODEP),
		WBS_ECOLE   => $ut->getLabel(WBS_ECOLE));
		$typeSel = $asso['asso_type'];
		$kcombo =& $form->addCombo('assoTypeSearch', $types, $types[$asso['asso_type']]);
		$kcombo->noMandatory();
		$actions[0] = array('searchClub');
		$kcombo->setActions($actions);
		$elts = array('assoName', 'assoPseudo', 'assoStamp', 'assoDpt',
		    'assoTypeSearch', 'btnSearch');
		$form->addBlock('blkCriteria', $elts);

		if (isset($assos['errMsg']))
		{
	  $form->addWng($assos['errMsg']);
	  unset($assos['errMsg']);
		}

		if (count($assos))
		{
	  $krows =& $form->addRows('rowsSearch', $assos);
	  $sizes[6] = '0+';
	  $krows->setSize($sizes);
	  $krows->displaySelect(false);
	  $krows->setSort(0);
	  $act[1] = array(KAF_UPLOAD, 'asso', KID_NEW, 0, 480, 470);
	  $krows->setActions($act);
		}

		$form->addBtn('btnHelp', KAF_NEWWIN, 'help', ASSOC_SEARCH, 0, 500, 400);
		$form->addBtn('btnCancel');

		$elts = array('btnHelp', 'btnNew', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
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
	function _delTeams()
	{
		$dt = $this->_dt;
		$err = '';

		// Get the id's of the teams to delete
		$teamsId = kform::getInput("rowsAssocs");

		// Delete the teams
		$err = $dt->delTeams($teamsId);
		if (isset($err['errMsg']))
		$this->_displayFormConfirm($err['errMsg']);

		// Close the windows
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
	function & _displayHead($teamId, $select)
	{
		$dt = $this->_dt;

		// Create a new page
		$this->_utPage = new utPage_A('asso', true, 1);
		$content =& $this->_utPage->getContentDiv();

		$team = $dt->getTeam($teamId);
		if (isset($team['errMsg']))
		$content->addWng($team['errMsg']);

		$asso = $dt->getAsso($team['asso_id']);
		if (isset($asso['errMsg']))
		$content->addWng($asso['errMsg']);

		$content->addInfo("teamName", $team['team_name']);

		// Display general informations
		$div =& $content->addDiv('blkTeam');
		$logo = utimg::getPathFlag($asso['asso_logo']);
		$div->addInfo("assoPseudo",    $asso['asso_pseudo']);
		$div->addInfo("assoStamp",     $asso['asso_stamp']);
		$div->addInfo("teamCaptain",   $team['team_captain']);
		$kinfo =& $div->addInfo("assoCount",  $team['cunt_name']);
		$act[1] = array(KAF_UPLOAD, 'account', KID_SELECT, $team['cunt_id']);
		$kinfo->setActions($act);

		$kinfo =& $div->addInfo("assoUrl", $asso['asso_url']);
		$kinfo->setUrl($asso['asso_url']);
		//$div->addImg("assoLogo", $logo);
		$div->addBtn('btnModify', KAF_NEWWIN, 'asso',
		ASSOC_EDIT_ACCOUNT,  $teamId, 350, 200);



		// Add a menu for different action
		$items['itMembers'] = array(KAF_UPLOAD, 'asso',
		ASSOC_DETAIL_ADMIN, $teamId);
		$items['itReservation'] = array(KAF_UPLOAD, 'asso',
		ASSOC_RESERVATION, $teamId);
		$items['itDiscount'] = array(KAF_UPLOAD, 'asso',
		ASSOC_DISCOUNT, $teamId);
		$items['itCredits'] = array(KAF_UPLOAD, 'asso',
		ASSOC_CREDITS, $teamId);
		$content->addMenu("menuType", $items, $select);
		$kdiv =& $content->addDiv('contType');
		$kdiv->addMsg($team['team_cmt']);
		return $kdiv;
	}
	// }}}

	// {{{ _registerAsso()
	/**
	 * Update the association and his registration for an individual event
	 *
	 * @access private
	 * @return void
	 */
	function _registerAsso()
	{
		$data = $this->_dt;

		// Get the informations
		$asso = array('asso_name'   =>kform::getInput('assoName'),
		    'asso_stamp'  =>kform::getInput('assoStamp'),
		    'asso_type'   =>kform::getInput('assoType'),
		    'asso_noc'    =>kform::getInput('assoNoc', 'FRA'),
		    'asso_id'     =>kform::getInput('assoId', -1),
		    'asso_url'    =>kform::getInput('assoUrl'),
		    'asso_logo'   =>kform::getInput('assoLogo'),
		    'asso_dpt'    =>kform::getInput('assoDpt'),
		    'asso_fedeId' =>kform::getInput('assoFedeId'),
		    'asso_pseudo' =>kform::getInput('assoPseudo'),
		    'asso_cmt'    =>kform::getInput('assoCmt'),
		    'asso_lockid' =>kform::getInput('assoLockid'),
		    'asso_fedeId' =>kform::getInput('assoFedeId')
		);
		if ($asso['asso_lockid'] == -1 )
		$asso['asso_lockid'] = utvars::getEventId();

		if (kform::getInput('teamAsso') == 1)
		$team = array('team_name'   => $asso['asso_name'],
		      'team_stamp'  => $asso['asso_stamp'],
		      'team_url'    => $asso['asso_url'],
		      'team_cmt'    => $asso['asso_cmt'],
		      'team_noc'    => $asso['asso_noc']
		);
		else
		$team = array('team_name'   =>kform::getInput('teamName'),
		      'team_stamp'  =>kform::getInput('teamStamp'),
		      'team_url'    =>kform::getInput('teamUrl'),
		      'team_cmt'    =>kform::getInput('teamCmt'),
		      'team_noc'    =>kform::getInput('teamNoc'),
		);
		$team['team_date']    = kform::getInput('teamDate');
		$team['team_id']      = kform::getInput('teamId', -1);
		$team['team_captain'] = kform::getInput('teamCaptain');
		$team['psit_texte']   = kform::getInput('psitTexte');

		$contact = array('ctac_id'       =>kform::getInput('ctacId'),
		       'ctac_associd'  =>kform::getInput('assoId'),
		       'ctac_type'     =>kform::getInput('ctacType'),
		       'ctac_value'    =>kform::getInput('ctacValue'),
		       'ctac_contact'  =>kform::getInput('ctacContact'),
		);

		// Control the informations
		if ($asso['asso_name'] == "")
		{
	  $asso['errMsg'] = 'msgassoName';
	  $this->_displayRegisterAsso($asso, $team, $contact);
		}

		if ($asso['asso_stamp'] == "")
		{
	  $asso['errMsg'] = 'msgassoStamp';
	  $this->_displayRegisterAsso($asso, $team, $contact);
		}

		if ($asso['asso_type'] == "")
		{
	  $asso['errMsg'] = 'msgassoType';
	  $this->_displayRegisterAsso($asso, $team, $contact);
		}

		// Add/update the association
		$res= $data->updateAsso($asso, $contact);
		if (is_array($res))
		{
	  $asso['errMsg'] = $res['errMsg'];
	  $this->_displayRegisterAsso($asso, $team, $contact);
		}

		// Add the registration of the association
		$team['team_assoId'] = $res;
		$contact['ctac_associd'] = $res;
		require_once "utils/utteam.php";
		$utt = new utTeam();
		$res= $utt->updateTeam($team);
		if (is_array($res))
		{
	  $team['errMsg'] = $res['errMsg'];
	  $this->_displayRegisterAsso($asso, $team, $contact);
		}
		$page = new kPage('none');
		if ($team['team_id'] == -1 &&
		!utvars::isTeamEvent())
		$page->close(true, 'teams', KID_SELECT, $res);
		else
		$page->close();
		exit;
	}
	// }}}

	// {{{ _displayRegisterAsso()
	/**
	 * Display the page for creating or updating an association
	 * for individual event
	 *
	 * @access private
	 * @return void
	 */
	function _displayRegisterAsso($asso, $team, $contact)
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;
		$utpage = new utPage('asso');
		$utpage->_page->addAction('onload', array('resize', 600, 700));
		$content =& $utpage->getPage();
		$form =& $content->addForm('tRegisterAsso', 'asso', KID_UPDATE);

		// Error of the team
		if (isset($team['errMsg']))
		$form->addWng($team['errMsg']);

		// Error of the association
		if (isset($asso['errMsg']))
		$form->addWng($asso['errMsg']);

		// Initialize the field
		$form->addHide('assoId',      $asso['asso_id']);
		$form->addHide('assoFedeId',  $asso['asso_fedeId']);
		$form->addHide('teamId',      $team['team_id']);
		$form->addHide('assoLogo',    $asso['asso_logo']);
		$form->addHide('assoLockid',  $asso['asso_lockid']);
		$form->addHide('ctacType',    $contact['ctac_type']);
		$form->addHide('ctacId',      $contact['ctac_id']);
		$size['maxWidth'] = 100;
		$size['maxHeight'] = 50;
		$logo = utimg::getPathFlag($asso['asso_logo']);
		$form->addImg("assoViewLogo", $logo, $size);

		// L'association est modifiable par un administrateur,
		// ou modifiable dans le tournoi qui l'a cree sauf import Poona
		if ( utvars::_getSessionVar('userAuth') == WBS_AUTH_ADMIN ||
		($asso['asso_lockid'] == utvars::geteventId() &&
		$asso['asso_fedeId'] == -1))
		{
	  $kedit =& $form->addEdit('teamDate', $team['team_date'],44);
	  $kedit->setMaxLength(10);
	  $kedit->noMandatory();
	  $kedit =& $form->addEdit('assoName',  $asso['asso_name'], 44);
	  $kedit->setMaxLength(100);
	  $kedit =& $form->addEdit('assoPseudo', $asso['asso_pseudo'],44);
	  $kedit->setMaxLength(20);
	  $kedit->noMandatory();
	  $kedit =& $form->addEdit('assoStamp', $asso['asso_stamp'],44);
	  $kedit->setMaxLength(10);
	  $kedit =& $form->addEdit('assoNoc', $asso['asso_noc'],44);
	  $kedit->setMaxLength(15);
	  $types = array( WBS_FEDE    => $ut->getLabel(WBS_FEDE),
	  WBS_CLUB    => $ut->getLabel(WBS_CLUB),
	  WBS_UMASSO  => $ut->getLabel(WBS_UMASSO),
	  WBS_LIGUE   => $ut->getLabel(WBS_LIGUE),
	  WBS_CODEP   => $ut->getLabel(WBS_CODEP),
	  WBS_ECOLE   => $ut->getLabel(WBS_ECOLE));
	  $typeSel = $asso['asso_type'];
	  $form->addCombo('assoType', $types, $types[$typeSel]);
	  $kedit =& $form->addEdit('assoDpt', $asso['asso_dpt'],44);
	  $kedit->setMaxLength(10);
	  $kedit->noMandatory();
	  $kedit =& $form->addEdit('assoUrl', $asso['asso_url'],44);
	  $kedit->setMaxLength(200);
	  $kedit->noMandatory();
	  $karea =& $form->addArea('assoCmt', $asso['asso_cmt'] , 40, 2);
	  $karea->noMandatory();
	  $kedit =& $form->addEdit('ctacContact', $contact['ctac_contact'], 44);
	  $kedit->noMandatory();
	  $kedit =& $form->addEdit('ctacValue', $contact['ctac_value'], 44);
	  $kedit->noMandatory();
	  if ($asso['asso_id'] != -1)
	  $form->addBtn('btnAddLogo', KAF_NEWWIN, 'asso',
	  ASSOC_SELECT_LOGO,  0, 650, 400);
		}
		else
		{
	  $kedit =& $form->addEdit('teamDate', $team['team_date'],11);
	  $kedit->setMaxLength(10);
	  $kedit->noMandatory();
	  $form->addInfo('assoName',   $asso['asso_name']);
	  //$kedit =& $form->addEdit('assoPseudo', $asso['asso_pseudo']);
	  //$kedit->setMaxLength(20);
	  $form->addInfo('assoPseudo', $asso['asso_pseudo']);
	  $form->addInfo('assoStamp',  $asso['asso_stamp']);
	  $form->addInfo('assoNoc',    $asso['asso_noc']);
	  $form->addInfo('assoDpt',    $asso['asso_dpt']);
	  $form->addHide('assoType',   $asso['asso_type']);
	  $form->addInfo('assoTypeName', $ut->getLabel($asso['asso_type']));
	  //$kedit =& $form->addEdit('assoUrl',    $asso['asso_url']);
	  //$kedit->setMaxLength(200);
	  //$kedit->noMandatory();
	  $form->addInfo('assoUrl',    $asso['asso_url']);
	  //$karea =& $form->addArea('assoCmt',$asso['asso_cmt'], 40, 2);
	  //$karea->noMandatory();
	  $karea =& $form->addInfoArea('assoCmt',$asso['asso_cmt'], 40, 2);
	  $form->addInfo('ctacContact', $contact['ctac_contact'] , 40, 2);
	  $form->addInfo('ctacValue', $contact['ctac_value'] , 40, 2);
		}
		//$kedit =& $form->addArea('psitTexte', $team['psit_texte'], 40);
		//$kedit->noMandatory();
		//$kedit =& $form->addEdit('cmdValue', $team['cmd_value'], 40);
		//$kedit->noMandatory();

		$elts = array('teamDate', 'assoName', 'assoPseudo', 'assoStamp','assoNoc',
		    'assoType', 'assoDpt', 'assoTypeName', 'assoUrl', 'assoCmt',
		    'ctacContact', 'ctacValue', 'psitTexte', 'cmdValue',
		    'btnAddLogo', 'assoViewLogo');
		$form->addBlock('blkAssoc', $elts);

		// Si le nom de l'�quipe est identique a l'association
		// l'equipe est masquee pour simplifier la saisie
		if ($asso['asso_name']== $team['team_name'] &&
		!utvars::IsTeamEvent())
		{
	  // Indicateur d'identite entre l'equipe et l'association
	  // l'equipe et l'asso doivent etre identiques
	  $form->addHide('teamAsso',    1);
	  $form->addHide('teamName',    $team['team_name']);
	  $form->addHide('teamCaptain', $team['team_captain']);
	  $form->addHide('teamStamp',   $team['team_stamp']);
	  $form->addHide('teamUrl',     $team['team_url']);
	  $form->addHide('teamCmt',     $team['team_cmt']);
	  $form->addHide('teamNoc',     $team['team_noc']);
		}
		else
		{
	  // Indicateur d'identite entre l'equipe et l'association
	  // l'equipe et l'asso sont differentes
	  $form->addHide('teamAsso',    0);
	  $kedit =& $form->addEdit('teamName',  $team['team_name'], 44);
	  $kedit->setMaxLength(50);
	  $kedit =& $form->addEdit('teamStamp',  $team['team_stamp'], 44);
	  $kedit->setMaxLength(30);
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
	  $logo = utimg::getPathTeamLogo($team['team_logo']);
	  $form->addImg("assoViewLogoTeam", $logo, $size);
	  if ($team['team_id'] != -1)
	  $form->addBtn('btnAddLogoTeam', KAF_NEWWIN, 'teams',
	  TEAM_SELECT_LOGO,  0, 650, 400);
	  $elts = array('teamName', 'teamStamp', 'teamCaptain', 'teamNoc',
			'teamUrl', 'teamCmt', 
			'btnAddLogoTeam', 'assoViewLogoTeam',
			);
	  $form->addBlock('blkTeam', $elts);
		}

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnSearch', 'btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormConfirm()
	/**
	 * Display the page for confirmation the destruction of purchase
	 *
	 * @access private
	 * @param array $member  info of the member
	 * @return void
	 */
	function _displayFormConfirm($err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('asso');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tDelAsso', 'asso', KID_DELETE);


		// Initialize the field
		$ids = kform::getInput("rowsAssocs");
		if ($ids != '' && $err == '')
		{
	  foreach($ids as $id)
	  $form->addHide("rowsAssocs[]", $id);
	  $form->addMsg('msgConfirmDel');
	  $form->addBtn('btnDelete', KAF_SUBMIT);
		}
		else
		{
	  if ($ids == '')
	  $form->addWng('msgNeedAssocs');
	  else
	  $form->addWng($err);
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