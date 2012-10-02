<?php
/*****************************************************************************
!   Module     : Officials and others
!   File       : $Source: /cvsroot/aotb/badnet/src/offo/offo_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.10 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2006/07/18 21:57:40 $
******************************************************************************/

require_once "base_A.php";
require_once "offo.inc";
require_once "utils/utpage_A.php";
require_once "utils/utimg.php";
require_once "regi/regi.inc";
require_once "teams/teams.inc";
require_once "mber/mber.inc";


/**
* Module de d'enregistrement des officiels et autres inscrits 
* non joueurs
*
* @author Didier BEUVELOT <didier.beuvelot@free.fr>
* @see to follow
*
*/

class offo_A   
{

  // {{{ properties
  
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
  function offo_A()
    {
      $this->_dt = new offoBase_A();
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
      $id = kform::getData();
      switch ($action)
        {

	  // Displaying windows for registrations
	case WBS_ACT_OFFOS:
	  $this->_displayFormOfficials();
	  break;
	case OFFO_OTHER:
	  $this->_displayFormOthers();
	  break;
	case KID_EDIT:
	  $data['regi_id'] = kform::getData();
	  $this->_prepareFormRegistration($data);
	  break;

	case KID_NEW:
	  $data['regi_id'] = kform::getData();
	  $this->_prepareFormRegistration($data);
	  break;

	case OFFO_NEW_OFFICIAL:
	  $data['init_type'] = OFFO_OFFICIAL;
	  $data['init_teamId'] = -1;
	  $data['title'] = 'tNewRegOfficial'; 
	  $this->_displaySelect($data);
	  break;

	case OFFO_NEW_OTHER:
	  $data['init_type'] = OFFO_OTHER;
	  $data['init_teamId'] = -1;
	  $data['title'] = 'tNewRegistration'; 
	  $this->_displaySelect($data);
	  break;

	case OFFO_NEW_OFFICIAL_FROM_TEAM:
	  $data['init_type'] = OFFO_OFFICIAL;
	  $data['init_teamId'] = kform::getData();
	  $data['title'] = 'tNewRegOfficial'; 
	  $this->_displaySelect($data);
	  break;

	case OFFO_NEW_OTHER_FROM_TEAM:
	  $data['init_type'] = OFFO_OTHER;
	  $data['init_teamId'] = kform::getData();
	  $data['title'] = 'tNewRegistration'; 
	  $this->_displaySelect($data);
	  break;


	case OFFO_ACTIVITE:
	  $this->_activite();
 	  break;

	case OFFO_SEARCH:
	  $data['init_type'] = kform::getInput('initType');
	  $data['init_teamId'] = kform::getInput('initTeamId');	  
	  $data['title'] = 'tNewRegOfficial'; 
	  $this->_displaySelect($data);
	  break;

	case OFFO_REG_MEMBER:
	  $data['fromSelect'] = true;
	  $this->_prepareFormRegistration($data);
	  break;

	  // No display but actions
	case KID_UPDATE:
	  $this->_updateRegistration();
	  break;
	case OFFO_ALL:
	  $data['fromSelect'] = true;
	  $data['auto'] = true;
	  $this->_prepareFormRegistration($data);
	  break;


	  // Change the publication status of selected teams
	case KID_CONFIDENT:
	  $this->_displayFormPubli(WBS_DATA_CONFIDENT);
	  break;
	case KID_PRIVATE:
	  $this->_displayFormPubli(WBS_DATA_PRIVATE);
	  break;
	case KID_PUBLISHED:
	  $this->_displayFormPubli(WBS_DATA_PUBLIC);
	  break;
	case TEAM_PUBLISHED:
	  $this->_publiMembers();
	  break;

	default :
	  echo "offo_A->start($action) non autorise <br>";
	  exit;
	}
    }
  // }}}
  

  // {{{ _activite
  /**
   * Display pdf activity form of selected umpire
   *
   * @access private
   * @param array team  info of the team
   * @return void
   */
  function _activite()
    {
      $dt = $this->_dt;

      $ids  = kform::getInput("rowsOfficials", NULL);
      
      if (is_array($ids))
	{
	  $activity = $dt->getActivity($ids);
	  $service  = $dt->getServiceActivity($ids);
	  if (isset($activity['errMsg']))
	    {
	      echo $activity['errMsg'];
	      exit;
	    }
	  //print_r($activity);
	  $all_activity = array_merge($activity, $service);
	  require_once "pdf/pdfscoresheet.php";
	  $pdf = new pdfScoreSheet();
	  $pdf->start();
	  $pdf->activite($activity, "Umpire");
	  $pdf->activite($service, "Service judge");
	  $pdf->end();
	  exit;
	}
      else
	{
	  $page = new utPage('none');
	  $page->close(false);	  
	  exit;
	}
    }


  // {{{ _displayFormOthers()
  /**
   * Display a page with the list of the registrations
   *
   * @access private
   * @return void
   */
  function _displayFormOthers($err='')
    {
      $dt = $this->_dt;
      
      $content =& $this->_displayHead('itOthers');
      $form =& $content->addForm('fOffo'); 

      if ($err!= '')
	{
	  $form->addWng($err['errMsg']); 
	  if (isset($err['regi_longName']))
	    $form->addWng($err['regi_longName']); 
	}

      // Menu for management of registrations
      $itsm['itNew'] = array(KAF_NEWWIN, 'offo', 
			     OFFO_NEW_OTHER, 0, 650, 450);
      $itsm['itErase'] = array(KAF_NEWWIN, 'regi', KID_CONFIRM, 0, 250, 150);
      
      $itsp['itPublic'] = array(KAF_NEWWIN, 'offo', KID_PUBLISHED
				, 0, 250, 150);
      $itsp['itPrivate']   = array(KAF_NEWWIN, 'offo', KID_PRIVATE
				   , 0, 250, 150);
      $itsp['itConfident'] = array(KAF_NEWWIN, 'offo', KID_CONFIDENT
				   , 0, 250, 150);
      $form->addMenu('menuLeft', $itsm, -1);
      $form->addMenu('menuRight', $itsp, -1);
      $form->addDiv('break', 'blkNewPage');

      // Display the list of members
      $sort = kform::getSort("rowsOfficials",3);
      $rows = $dt->getOfficials($sort, OFFO_OTHER);

      if (isset($rows['errMsg']))
	{
	  $form->addWng($rows['errMsg']);
	  unset($rows['errMsg']);
	}
      if (count($rows))
	{
	  $krows =& $form->addRows("rowsOfficials", $rows);

	  $column[2] = 0;
	  $krows->setSortAuth($column);

	  $sizes = array(9=> 0,0,0,0,0,0,0,0);
	  $krows->setSize($sizes);

	  $logo[3] = 'iconRegi';
	  $logo[8] = 'iconTeam';
	  $logo[2] = 'mber_urlphoto';
	  $krows->setLogo($logo);
  
	  $actions[0] = array(KAF_NEWWIN, 'offo', 
	  		      KID_EDIT, 0, 350,450);
	  $actions[8] =	 array(KAF_UPLOAD, 'teams', 
	  		       KID_SELECT, 'team_id');
	  $krows->setActions($actions);
	}

      // Legend
      $form->addImg('lgdConfident', utimg::getIcon(WBS_DATA_CONFIDENT));
      $form->addImg('lgdPrivate', utimg::getIcon(WBS_DATA_PRIVATE));
      $form->addImg('lgdPublic', utimg::getIcon(WBS_DATA_PUBLIC));
      $elts=array('lgdConfident', 'lgdPrivate', 'lgdPublic');
      $form->addBlock("blkLegende", $elts);

      $this->_utpage->display();
      exit; 
    }
  // }}}

  // {{{ _displayFormOfficials()
  /**
   * Display a page with the list of the registrations
   *
   * @access private
   * @return void
   */
  function _displayFormOfficials($err='')
    {
      $dt = $this->_dt;
      
      $content =& $this->_displayHead('itOfficials');
      $form =& $content->addForm('fOffo'); 

      if ($err!= '')
	{
	  $form->addWng($err['errMsg']); 
	  if (isset($err['regi_longName']))
	    $form->addWng($err['regi_longName']); 
	}

      // Menu for management of registrations
      $itsm['itNew'] = array(KAF_NEWWIN, 'offo', 
			     OFFO_NEW_OFFICIAL, 0, 650, 450);
      $itsm['itActivite'] = array(KAF_NEWWIN, 'offo', OFFO_ACTIVITE, 0, 400, 400);
      $itsm['itErase'] = array(KAF_NEWWIN, 'regi', KID_CONFIRM, 0, 250, 150);
      
      $itsp['itPublic'] = array(KAF_NEWWIN, 'offo', KID_PUBLISHED
				, 0, 250, 150);
      $itsp['itPrivate']   = array(KAF_NEWWIN, 'offo', KID_PRIVATE
				   , 0, 250, 150);
      $itsp['itConfident'] = array(KAF_NEWWIN, 'offo', KID_CONFIDENT
				   , 0, 250, 150);
      $form->addMenu('menuLeft', $itsm, -1);
      $form->addMenu('menuRight', $itsp, -1);
      $form->addDiv('break', 'blkNewPage');

      // Display the list of officials
      $sort = kform::getSort("rowsOfficials",4);
      $rows = $dt->getOfficials($sort);

      if (isset($rows['errMsg']))
	{
	  $form->addWng($rows['errMsg']);
	  unset($rows['errMsg']);
	}
      if (count($rows))
	{
	  $krows =& $form->addRows("rowsOfficials", $rows);

	  $column[2] = 0;
	  $krows->setSortAuth($column);

	  $sizes = array(9=> 0,0,0,0,0,0,0,0);
	  $krows->setSize($sizes);

	  $logo[3] = 'iconRegi';
	  $logo[8] = 'iconTeam';
	  $logo[2] = 'mber_urlphoto';
	  $krows->setLogo($logo);
  
	  $actions[0] = array(KAF_NEWWIN, 'offo', 
	  		      KID_EDIT, 0, 350,450);
	  $actions[8] =	 array(KAF_UPLOAD, 'teams', 
	  		       KID_SELECT, 'team_id');
	  $krows->setActions($actions);
	}

      // Legend
      $form->addImg('lgdConfident', utimg::getIcon(WBS_DATA_CONFIDENT));
      $form->addImg('lgdPrivate', utimg::getIcon(WBS_DATA_PRIVATE));
      $form->addImg('lgdPublic', utimg::getIcon(WBS_DATA_PUBLIC));
      $elts=array('lgdConfident', 'lgdPrivate', 'lgdPublic');
      $form->addBlock("blkLegende", $elts);

      $this->_utpage->display();
      exit; 
    }
  // }}}
  
  // {{{ _prepareFormRegistration()
  /**
   * Prepare the data to display for cretaing or updating registration
   *
   * @access private
   * @param array $registration  info of the registration
   * @param boolean $mod         can the user modify member's information
   * @return void
   */
  function _prepareFormRegistration($mode='')
    //$mode['type'] insciption d'un joueur, officiel ou autre
    //$mode['regi_id'] modification de l'insciption regi_id
    //$mode['init_teamId'] nouvelle inscription demande depuis une equipe
    //$mode['mber_id'] nouvelle inscription de ce membre
    {
      $dt = $this->_dt;

      if (!isset($mode['init_type'])) $mode['init_type'] =  OFFO_OFFICIAL;
      $mode['init_type'] = kform::getInput('initType', $mode['init_type']);
      
      if (!isset($mode['regi_id']))$mode['regi_id'] = -1;
      $mode['regi_id'] = kform::getInput('regiId', $mode['regi_id']);

      if (!isset($mode['init_teamId']))$mode['init_teamId'] = -1;
      $mode['init_teamId'] = kform::getInput('initTeamId', $mode['init_teamId']);

      if (!isset($mode['mber_id']))$mode['mber_id'] = -1;
      $mode['mber_id'] = kform::getInput('mberId', $mode['mber_id']);

      // Set the title
      if ($mode['regi_id'] != -1)
	$mode['title'] = 'tEditRegistration';
      else
	if ($mode['init_type'] == OFFO_OFFICIAL)
	  $mode['title'] = 'tNewRegOfficial';
	else
	  $mode['title'] = 'tNewRegistration';

      if ($mode['regi_id'] != -1 && !isset($mode['errMsg']))
	{
	  $member = $dt->getRegiMember($mode['regi_id']);
	  $regi   = $dt->getRegiRegis($mode['regi_id']);
	  switch ($regi['regi_type'])
	    {
	    case WBS_REFEREE:
	    case WBS_DEPUTY:
	    case WBS_UMPIRE:
	    case WBS_LINEJUDGE:
	    case WBS_DELEGUE:
	    case WBS_CONSEILLER:
	    	$mode['init_type'] = OFFO_OFFICIAL;
	      break;
	    default:
	      $mode['init_type'] = OFFO_OTHER;
	      break;
	    }
	}
      else
	{
	  // Traiter le premier joueur selectionne
	  if (isset($mode['fromSelect']))
	    {
	      $member = array();
	      $regi   = array();
	      $ids = kform::getInput('rowsMembers');	      
	      if (isset($mode['auto']))
		{
		  $member = $dt->getMember($mode['mber_id']);
		  $regi   = $this->_getFieldsRegi();
		  $dt->updateRegistration($member, $regi);
		  foreach($ids as $id)
		    {
		      $member = $dt->getMember($id);		      
		      $regi   = $this->_getFieldsRegi();
		      $regi['regi_longName'] = 
			strtoupper($member['mber_secondname']).' '.
			ucwords(strtolower($member['mber_firstname']));
		      $regi['regi_shortName'] =
			strtoupper($member['mber_secondname']).' '.
			substr(ucwords(strtolower($member['mber_firstname'])),
			       0,1);
		      // update the registration
		      $res = $dt->updateRegistration($member, $regi);
		      if (is_array($res))
			return $res;
		    }
		  $page = new utPage('none');
		  $page->close();
		  exit;
		}
	      else if (is_array($ids))
		{
		  $id = array_shift($ids);
		  $member = $dt->getMember($id);
		}
	      else
		$member = $this->_getFieldsMember();

	    }
	  else
	    {
	      $member = $this->_getFieldsMember();
	    }
	  $regi   = $this->_getFieldsRegi();
	}	
      $this->_displayFormRegistration($member, $regi, $mode);
    }
  // }}}

  // {{{ _displayFormRegistration()
  /**
   * Display the page for creating or updating registration
   *
   * @access private
   * @param array $registration  info of the registration
   * @param boolean $mod         can the user modify member's information
   * @return void
   */
  function _displayFormRegistration($member, $regi, $mode)
    //$mode['init_type'] insciption d'un officiel ou autre
    //$mode['regi_id'] modification de l'insciption regi_id
    //$mode['init_teamId'] nouvelle inscription demande depuis une equipe
    //$mode['mber_id'] nouvelle inscription de ce membre
    {
      $dt = $this->_dt;
      
      $utpage = new utPage('offo');
      $content =& $utpage->getPage();

      $form =& $content->addForm($mode['title'], 'offo', KID_UPDATE);
      // Display the error if any 
      if (isset($mode['errMsg']))
	$form->addWng($mode['errMsg']);

      // List of all member to register
      $ids = kform::getInput('rowsMembers');
      if (is_array($ids))
	{
	  array_shift($ids);
	  foreach($ids as $id)
	    $form->addHide('rowsMembers[]', $id);
	}

      // Initialize the fields definition member
      $form->addHide('mberId', $member['mber_id']);
      $form->addHide('initType', $mode['init_type']);
      $form->addHide('initTeamId', $mode['init_teamId']);
      if ($member['mber_id']==-1)
	{
	  $form->addRadio('mberSexe', 
			  $member['mber_sexe']==WBS_MALE, WBS_MALE);
	  $form->addRadio('mberSexe', 
			  $member['mber_sexe']==WBS_FEMALE, WBS_FEMALE);
	  
	  $kedit =& $form->addEdit('mberSecondName', 
				   $member['mber_secondname'],30);
	  $kedit->setMaxLength(30);
	  $kedit =& $form->addEdit('mberFirstName',
				   $member['mber_firstname'],30);
	  $kedit->setMaxLength(30);
	  $kedit =& $form->addEdit('mberBorn', $member['mber_born']);
	  $kedit->setMaxLength(25);
	  $kedit->noMandatory();
	}
      else
	{
	  $ut = new utils();
	  $form->addHide('mberSexe', $member['mber_sexe']);	  
 	  $form->addInfo('mberSexeLabel', $ut->getLabel($member['mber_sexe']));
	  $form->addInfo('mberSecondName', $member['mber_secondname']);
	  $form->addInfo('mberFirstName',  $member['mber_firstname']);
	  $form->addInfo('mberBorn',       $member['mber_born']);

	  $size['maxWidth'] = 100;
	  $size['maxHeight'] = 200;
	  $logo = utimg::getPathPhoto($member['mber_urlphoto']);
	  $form->addImg('mberPhoto', $logo, $size);
	  $form->addBlock('blkPhoto', 'mberPhoto', 'classPhoto');

	  if ($member['mber_id'] > -1 && $regi['regi_id'] >-1)
	    {
	      $form->addBtn('btnModify', KAF_NEWWIN, 'mber', KID_EDIT,
			    $member['mber_id'], 370, 340);
	      $form->addBtn('btnAddPhoto', KAF_NEWWIN, 'mber', 
			    MBER_SELECT_PHOTO,  0, 650, 400);
	    }
	}
      $elts = array('mberSexe', 'mberSexeLabel'
		    , 'mberSecondName', 'mberFirstName'
                    , 'mberBorn', 'mberAssoc', 'btnModify', 
		    'btnSearchLocal', 'btnAddPhoto');
      $form->addBlock('blkCivil', $elts, 'classCivil');

      $elts = array('blkCivil', 'blkPhoto');
      $form->addBlock('blkAdmi', $elts, 'classOffo');

      // Initialize the field for registration
      $form->addHide('regiId', $regi['regi_id']);
      $kedit =& $form->addEdit('regiDate', $regi['regi_date'],11);
      $kedit->setMaxLength(10);    
      $kedit->noMandatory();
      $kedit =& $form->addEdit('regiNoc', $regi['regi_noc'],11);
      $kedit->setMaxLength(15);    
      $kedit->noMandatory();
      $kedit =& $form->addHide('regiLongName', $regi['regi_longName']);
      $kedit =& $form->addHide('regiShortName', $regi['regi_shortName']);

      // Get teams from database
      $assocs = $dt->getTeams($mode['init_teamId']);
      if (isset($assocs['errMsg']))
		{
			$assocs[''] = $assocs['Aucune équipe disponible'];
			unset($assocs['errMsg']);
		}
      
      if($mode['init_teamId']!=-1)
	{
	  $form->addHide('regiTeamId', $mode['init_teamId']);
	  $first = each($assocs);
	  $form->addInfo('regiTeamName', $assocs[$first[0]]);
	}
      else
	{
	  $select = "";
	  if (isset($assocs[$regi['regi_teamId']]))
	    $select = $assocs[$regi['regi_teamId']];
	  else
	    {
	      $first = each($assocs);
	      $select = $assocs[$first[0]];
	    }	
	  $form->addCombo('regiTeamId', $assocs, $select);
	}

      $form->addHide('regiAccountId', $regi['regi_accountId']);
      if ($mode['init_type'] == OFFO_OFFICIAL)
	{
	  if ( $regi['regi_type'] != WBS_REFEREE &&
	       $regi['regi_type'] != WBS_UMPIRE &&
	       $regi['regi_type'] != WBS_DEPUTY &&
	       $regi['regi_type'] != WBS_LINEJUDGE &&
	       $regi['regi_type'] != WBS_CONSEILLER &&
	       $regi['regi_type'] != WBS_DELEGUE
	       )
	    $regi['regi_type'] = WBS_UMPIRE;
	  $form->addRadio('regiType', 
			  $regi['regi_type']==WBS_REFEREE, WBS_REFEREE);
	  $form->addRadio('regiType', 
			  $regi['regi_type']==WBS_DEPUTY, WBS_DEPUTY);
	  $form->addRadio('regiType', 
			  $regi['regi_type']==WBS_UMPIRE, WBS_UMPIRE);
	  $form->addRadio('regiType', 
			  $regi['regi_type']==WBS_LINEJUDGE, WBS_LINEJUDGE);
	  $form->addRadio('regiType', 
			  $regi['regi_type']==WBS_DELEGUE, WBS_DELEGUE);
	  $form->addRadio('regiType', 
			  $regi['regi_type']==WBS_CONSEILLER, WBS_CONSEILLER);
			  
	}
      $ut = new utils();
      if ($mode['init_type'] == OFFO_OTHER)
	{
	  $types = array( WBS_COACH => $ut->getLabel(WBS_COACH),
			  WBS_ORGANISATION => $ut->getLabel(WBS_ORGANISATION),
			  WBS_VOLUNTEER    => $ut->getLabel(WBS_VOLUNTEER),
			  WBS_VIP        => $ut->getLabel(WBS_VIP),
			  WBS_PRESS      => $ut->getLabel(WBS_PRESS),
			  WBS_GUEST      => $ut->getLabel(WBS_GUEST),
			  WBS_MEDICAL    => $ut->getLabel(WBS_MEDICAL),
			  WBS_EXHIBITOR  => $ut->getLabel(WBS_EXHIBITOR),
			  WBS_PLATEAU    => $ut->getLabel(WBS_PLATEAU),
			  WBS_SECURITE  => $ut->getLabel(WBS_SECURITE),
			  WBS_OTHERB    => $ut->getLabel(WBS_OTHERB),
			  );
	  if (!isset($types[$regi['regi_type']]))
	    $regi['regi_type'] = WBS_VOLUNTEER;
	  $form->addCombo('regiType', $types, $types[$regi['regi_type']]);
	}

      $karea =& $form->addArea('regiCmt', $regi['regi_cmt'], 20, 3 );
      $karea->noMandatory();
      $kedit =& $form->addEdit('regiFunction', $regi['regi_function'], 30);
      $kedit->noMandatory();
      $elts = array('regiDate', 'regiLongName', 'regiShortName', 'regiNoc',
		    'regiType',
		    'regiType1','regiType2','regiType3','regiType4',
		    'regiAccountId1','regiAccountId2',
		    'regiTeamName', 'regiTeamId', 'regiCmt', 'regiFunction');
      $form->addBlock('blkRegi', $elts, 'classOffo');

      if (is_array($ids) && count($ids))
	$form->addBtn('btnRegisterAll', KAF_VALID, 'offo', OFFO_ALL);
      $form->addBtn('btnRegister', KAF_SUBMIT);
      $form->addBtn('btnCancel');
      $elts = array('btnSearchLocal', 'btnNew', 'btnRegisterAll',
		    'btnRegister', 'btnCancel');
      $form->addBlock('blkBtn', $elts);
      
      //Display the form
      $utpage->display();
      
      exit;
    }
  // }}}
  
  // {{{ _displaySelect()
  /**
   * Display a page with a list of the candidate members
   *
   * @access private
   * @param  integer $players   Table of players to display
   * @return void
   */
  function _displaySelect($data='')
    {
      $dt = $this->_dt;

      $utpage = new utPage('offo');
      $content =& $utpage->getPage();

      $criteria['name'] = strtoupper(kform::getInput('mberSecondName'));
      $criteria['firstName'] = kform::getInput('mberFisrtName');
      $criteria['country'] = kform::getInput('country');
       $members = $dt->searchMember($criteria);
      if (!count($members) || 
	  (count($members)==1 && isset($member['errmsg'])))
	{
	  $form =& $content->addForm('foffo', 'offo', OFFO_SEARCH);
	  $form->addBtn('btnSearch', KAF_SUBMIT);
	}
      else
	{
	  $form =& $content->addForm('foffo', 'offo', OFFO_REG_MEMBER);
	  $form->addBtn('btnSearch', KAF_VALID, 'offo', OFFO_SEARCH);
	  $form->addBtn('btnValidate', KAF_SUBMIT);
	}

      $form->setTitle($data['title']);
      
      // Add hide context fields
      $form->addHide('initType', $data['init_type']);
      $form->addHide('initTeamId', $data['init_teamId']);

      $kedit =& $form->addEdit('mberSecondName', $criteria['name']);
      $kedit->setMaxLength(20);
      $kedit->noMandatory();
      $kedit =& $form->addEdit('mberFirstName', $criteria['firstName']);
      $kedit->setMaxLength(20);
      $kedit->noMandatory();
      //$kedit =& $form->addEdit('mberCountry', $criteria['mberCountry']);
      //$kedit->setMaxLength(10);
      //$kedit->noMandatory();
      $elts = array('mberSecondName','mberFirstName', 'mberCountry');
      $form->addBlock('blkCriteria', $elts);
      
      // Display the error if any 
      if (isset($err['errMsg']))
	$form->addWng($err['errMsg']);

      // Display the list of found members
      $members = $dt->searchMember($criteria);
      if (isset($members['errMsg']))
	{
	  $form->addWng($members['errMsg']);
	  unset($members['errMsg']);
	}
      if (count($members))
	{
	  $krow =&$form->addRows("rowsMembers", $members);
	  $krow->setSort(0);
	}
      
      $form->addBtn('btnNew', KAF_VALID, 'offo', OFFO_REG_MEMBER);
      $form->addBtn('btnCancel');
      $elts = array('btnSearch', 'btnValidate', 'btnNew', 'btnCancel');
      $form->addBlock('blkBtn', $elts);

      $utpage->display();
      exit; 
    }
  // }}}

  // {{{ _updateRegistration()
  /**
   * Create the registration in the database
   *
   * @access private
   * @return void
   */
  function _updateRegistration()
    {
      $dt = $this->_dt;

      $res = $this->_updateCurrentRegistration();
      if (is_array($res))
	{
	  print_r($res);
	  $this->_prepareFormRegistration($res);
	}
      
      // List of all member to register
      $ids = kform::getInput('rowsMembers');
      if (is_array($ids))
	{
	  $data['fromSelect'] = true;
	  $this->_prepareFormRegistration($data);
	}
      $page = new utPage('none');
      $page->close();
      exit;
    }

  // {{{ _updateCurrentRegistration()
  /**
   * Create the registration in the database
   *
   * @access private
   * @return void
   */
  function _updateCurrentRegistration()
    {
      $dt = $this->_dt;

      // First get the informations
      $member = $this->_getFieldsMember();
      $regi   = $this->_getFieldsRegi();

      // Control the informations
      if (isset($member['mber_urlphoto']))
	unset($member['mber_urlphoto']);
      if ($member['mber_sexe'] != WBS_MALE &&
          $member['mber_sexe'] != WBS_FEMALE)
	{
	  $infos['errMsg'] = 'msgmberSexe';
	  return $infos;
	} 
      
      if ($member['mber_firstname'] == "")
	{
	  $infos['errMsg'] = 'msgmberFirstName';
	  return $infos;
	} 
      
      if ($member['mber_secondname'] == "")
	{
	  $infos['errMsg'] = 'msgmberSecondName';
	  return $infos;
	} 

      // Update the member
      require_once "mber/base_A.php";
      $dtm = new memberBase_A();
      $res = $dtm->updateMember($member);
      if (is_array($res))
        {
	  return $res;
	}
      $member['mber_id'] = $res;
      
      // update the registration
      $res = $dt->updateRegistration($member, $regi);
      if (is_array($res))
        {
	  return $res;
        }
      // return registratoin id
      return $res;
    }
  // }}}

  // {{{ _getFieldsMember()
  /**
   * Retrieve data about member
   *
   * @access private
   * @return void
   */
  function _getFieldsMember()
    {
      $member = array('mber_sexe'    =>kform::getInput("mberSexe", WBS_MALE),
		      'mber_firstname'  =>
		      ucwords(strtolower(kform::getInput("mberFirstName"))),
		      'mber_secondname' =>
		      strtoupper(kform::getInput("mberSecondName")),
		      'mber_born'       =>kform::getInput("mberBorn"),
		      'mber_id'         =>kform::getInput("mberId", -1),
		      'mber_cmt'        =>kform::getInput('mberCmt'));
      return $member;
    }
  // }}}

  // {{{ _getFieldsRegi()
  /**
   * Retrieve data about member
   *
   * @access private
   * @return void
   */
  function _getFieldsRegi()
    {

      $regi = array('regi_longName'  => 
		    strtoupper(kform::getInput("mberSecondName")).' '.
		    ucwords(strtolower(kform::getInput("mberFirstName"))),
		    'regi_shortName' =>
		    strtoupper(kform::getInput("mberSecondName")).' '.
		    substr(ucwords(strtolower(kform::getInput("mberFirstName"))),
			   0,1),	       
		    'regi_teamId'    =>kform::getInput("regiTeamId", -1),
		    'regi_id'        =>kform::getInput("regiId", -1),
		    'regi_accountId' =>kform::getInput("regiAccountId", -1),
		    'regi_cmt'  =>kform::getInput("regiCmt"),
		    'regi_function'  =>kform::getInput("regiFunction"),
		    'regi_noc'  =>kform::getInput("regiNoc"),
		    'regi_date' =>kform::getInput("regiDate", date(DATE_DATE)),
		    'regi_type'      =>kform::getInput('regiType'),
		    'regi_eventId'   =>utvars::getEventId());
      return $regi;
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
      $this->_utpage = new utPage_A('offo', true, 'itRegister');
      $content =& $this->_utpage->getContentDiv();

      // Add a menu for different action
      $kdiv =& $content->addDiv('choix', 'onglet3');
      if(utvars::isLineRegi())
	$items['itLine']    = array(KAF_UPLOAD, 'line',  WBS_ACT_LINE);
      $items['itTeams']     = array(KAF_UPLOAD, 'teams',  WBS_ACT_TEAMS);
      $items['itPlayers']   = array(KAF_UPLOAD, 'regi', WBS_ACT_REGI);
      $items['itOfficials'] = array(KAF_UPLOAD, 'offo', WBS_ACT_OFFOS);
      $items['itOthers']    = array(KAF_UPLOAD, 'offo', OFFO_OTHER);
      $kdiv->addMenu("menuType", $items, $select);
      $kdiv =& $content->addDiv('register', 'cont3');
      return $kdiv;
    }
  // }}}

  // {{{ _displayFormPubli()
  /**
   * Change the publication state of the selected members
   *
   * @access private
   * @param  integer $publi  new publication state of the event
   * @return void
   */
  function _displayFormPubli($publi, $err='')
    {
      $dt = $this->_dt;

      $utpage = new utPage('offo');
      $content =& $utpage->getPage();
      $form =& $content->addForm('fOffo', 'offo', KID_PUBLISHED);
      $form->setTitle('tPubliMembers');

      // Get the id's
      $allId = kform::getInput("rowsRegis", array());
      $playersId = kform::getInput("rowsPlayers", array());
      $offosId = kform::getInput("rowsOffos", array());
      $officialsId = kform::getInput("rowsOfficials", array());
      $ids = array_merge($allId, $playersId, $offosId, $officialsId);

      if (!count($ids))
	$form->addWng('msgNeedMembers');
      else
	{
	  require_once "utils/utpubli.php";
	  $utp = new utpubli();
	  $res = $utp->publiRegi($ids, $publi);
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


}
?>
