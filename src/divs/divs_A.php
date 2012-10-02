<?php
/*****************************************************************************
!   Module     : Division 
!   File       : $Source: /cvsroot/aotb/badnet/src/divs/divs_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.11 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
******************************************************************************/

require_once "divs.inc";
require_once "base_A.php";
require_once "utils/utteam.php";
require_once "utils/utpage_A.php";
require_once "asso/asso.inc";
require_once "teams/teams.inc";

/**
* Module de gestion des tournois
*
* @author Gerard CANTEGRIL <cage@aotb.org>
* @see to follow
*
*/

class Divs_A
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
  function Divs_A()
    {
      $this->_ut = new Utils();
      $this->_dt = new DivsBase_A();
    }
  // }}}
  
  // {{{ start()
  /**
   * Start the connexion processus
   *
   * @access public
   * @return void
   */
  function start($page)
    {
      $dt = $this->_dt;
      switch ($page)
	{
	  // Creation of a new division
	case KID_NEW : 
	  $this->_displayFormNewDiv();
	  break;
	  // Modification of an existing division
	case KID_EDIT : 
	  $divId = kform::getData();
	  $this->_displayFormDiv($divId);
	  break;
	  // Delete the selected serial
	case DIV_CONFIRM_DEL:
	  $this->_displayFormConfirmDiv();
	  break;
	case KID_DELETE:
	  $this->_deleteDiv();
	  break;

	  // Update a division
	case DIV_UPDATE_A:
	  $this->_updateDiv();
	  break;
	case DIV_CREATE_A:
	  $this->_createDiv();
	  break;

	  // Modify a draw
	case GROUP_EDIT_A:
	  $this->_displayFormGroup();
	  break;
	case GROUP_UPDATE_A:
	  $this->_updateGroup();
	  break;
	case DIV_CONFIRM_GROUPS:
	  $this->_displayFormConfirmGroups();
	  break;
	case GROUP_DELETE_A:
	  $this->_deleteGroup();
	  break;
	case DIV_ADD_GROUP:
	  $this->_displayFormGroup(-1);
	  break;

	  // Display form to select team for a group
	case GROUP_TEAM_A:
	  $this->_displayFormTeams();
	  break;
	case GROUP_REGTEAM_A:
	  $this->_registerTeams();
	  break;

	case TEAM_UP_A:
	  $this->_moveTeams(-1);
	  break;
	case TEAM_DOWN_A:
	  $this->_moveTeams(1);
	  break;

	case WBS_ACT_DIVS:
	  $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']
				. '?bnAction=198656&eventId='.utvars::getEventId();
	  header("Location: $url");
   	  exit();
		
	  $this->_displayDiv();
	  break;

	case DIV_PDF:
	  require_once "pdf/pdfgroups.php";
	  $divId = kform::getData();
 	  $pdf = new pdfGroups();
	  $pdf->start();
	  $pdf->affichage_div($divId);
	  $pdf->end();
	  exit;
 	  break;

	default:
	  echo "page $page demandée depuis draws_A<br>";
	  exit();
	}
    }
  // }}}
  


  // {{{ _displayFormConfirmDiv()
  /**
   * Display the page for confirmation the destruction of the division
   *
   * @access private
   * @param array $member  info of the member
   * @return void
   */
  function _displayFormConfirmDiv($err='')
    {
      $dt = $this->_dt;
      
      $utpage = new utPage('divs');
      $content =& $utpage->getPage();
      $form =& $content->addForm('tDelDiv', 'divs', KID_DELETE);

      // Initialize the field
      $divId = kform::getData();
      $infos = $dt->getDiv($divId);
      if (($divId != '') &&
	  !isset($err['errMsg']))
	{
	  $form->addHide('divId', $divId);
	  $form->addWng('msgConfirmDelDiv');
	  $form->addMsg($infos['div_name']);
	  $form->addBtn('btnDelete', KAF_SUBMIT);
	}
      else
	if ($divId === '')
	  $form->addWng('msgNeedDiv');
	else
	  $form->addWng($err['errMsg']);

      $form->addBtn('btnCancel');
      $elts = array('btnDelete', 'btnCancel');
      $form->addBlock('blkBtn', $elts);

      //Display the page
      $utpage->display();
      exit;
    }
  // }}}

  // {{{ _displayFormConfirmGroups()
  /**
   * Display the page for confirmation the destruction of a groups
   *
   * @access private
   * @param array $member  info of the member
   * @return void
   */
  function _displayFormConfirmGroups($err='')
    {
      $dt = $this->_dt;
      
      $utpage = new utPage('divs');
      $content =& $utpage->getPage();
      $form =& $content->addForm('tDelGroups', 'divs', GROUP_DELETE_A);

      // Initialize the field
      $groupId = kform::getData();
      if (($groupId != '') &&
	  !isset($err['errMsg']))
	{
	  
	  $form->addHide("rowsGroups[]", $groupId);
	  $form->addMsg('msgConfirmDelGroups');
	  $form->addBtn('btnDelete', KAF_SUBMIT);
	}
      else
	if ($groupsId === '')
	  $form->addWng('msgNeedGroups');
	else
	  $form->addWng($err['errMsg']);

      $form->addBtn('btnCancel');
      $elts = array('btnDelete', 'btnCancel');
      $form->addBlock('blkBtn', $elts);

      //Display the page
      $utpage->display();
      exit;
    }
  // }}}

  // {{{ _moveTeams()
  /**
   * move the teams selected into the current group
   *
   * @access private
   * @param integer $sens direction where move the team up=-1 or down=1
   * @return void
   */
  function _moveTeams($sens)
    {
      $dt = $this->_dt;
      
      $groupId = kform::getData("groupId");
      $teamsId = kform::getInput("rowsTeams");

      // Treat the data
      if (is_array($teamsId))
	{
	  foreach($teamsId as $team => $id)
	    $dt->moveTeam($id, $groupId, $sens);
	}
      $page = new utPage('none');
      $page->clearCache();
      $page->close();
      exit; 
    }
  // }}}

  // {{{ _registerTeams()
  /**
   * register the teams selected to the current group
   *
   * @access private
   * @return void
   */
  function _registerTeams()
    {
      $dt = $this->_dt;
      
      $groupId = kform::getInput("groupId");
      $teamsId = kform::getInput("selectTeams");

      if (is_array($teamsId))
	$res = $dt->regTeams($teamsId, $groupId);
      if (is_array($res))
	{
	  // An error occured. Display the error
	  $res['groupId'] = $groupId;
	  $this->_displayFormTeams($res);
	}

      $utt =  new utteam();;
      $allTeams = $utt->getTeamsGroup($groupId);
      foreach($allTeams as $team)
	{
	  if (!in_array($team['team_id'],$teamsId))
	    $teamsRem[] = $team['team_id'];
	}
      if (isset($teamsRem))
	{
	  $res = $dt->delTeams($teamsRem, $groupId);
	  if (is_array($res))
	    {
	      // An error occured. Display the error
	      $infos['errMsg'] = $res['errMsg'];
	      $this->_displayFormTeams($infos);
	      exit;
	    }
	}
      // All is OK. Close the window
      $page = new utPage('none');
      $page->close();
      exit;
    }
  // }}}

  // {{{ _displayFormTeams()
  /**
   * Display a form to select team for the current group
   *
   * @access private
   * @return void
   */
  function _displayFormTeams($infos="")
    {
      $dt = $this->_dt;

      $utpage = new utPage('divs');
      $content =& $utpage->getPage();
      $form =& $content->addForm('fSelTeams', 'divs', GROUP_REGTEAM_A);
      $form->setTitle('tSelectTeam');

      if (is_array($infos))
	{
	  $form->addWng($infos['errMsg']);
	  $groupId = $infos['groupId'];
	}
      else $groupId = kform::getData();
      $group = $dt->getGroup($groupId);

      $form->addHide('groupId', $groupId);
      $form->addInfo('groupNameno', $group['rund_name']);
      $form->addDiv('break1', 'blkNewPage');
      
      $sort = $form->getSort('selectTeams', 2);
      $rows = $dt->getTeams($sort);
      if (isset($rows['errMsg']))
	$form->addWng($rows['errMsg']);
      else
	{
	  $utt = new utTeam();
	  $teamsGroup = $utt->getTeamsGroup($groupId);
	  $teamsId=array();
	  foreach($teamsGroup as $team)
	    $teamsId[] = $team['team_id'];

	  $krows =& $form->addRows("selectTeams", $rows);
	  if (is_array($teamsId))
	      $krows->setSelect($teamsId);

	  $sizes = array(5=> 0, 0);
	  $krows->setSize($sizes);
	}

      //$form->addBtn("btnNewTeam", KAF_NEWWIN, 'teams', KID_NEW, 0, 450, 550);
      $form->addBtn("btnRegister", KAF_SUBMIT);
      $form->addBtn("btnCancel");
      $elts = array("btnNewTeam", "btnRegister", "btnCancel");
      $form->addBlock("blkBtn", $elts);
      $utpage->display();
      exit; 
    }
  // }}}

  // {{{ _deleteDiv()
  /**
   * Delete the selected division in the database
   *
   * @access private
   * @return void
   */
  function _deleteDiv()
    {
      $dt = $this->_dt;

      // Get the id's of the divisions to delete
      $div = kform::getInput('divId');
      
     // Delete the division
      $res = $dt->delDiv($div);

      if (is_array($res)) 
	  $this->_displayFormConfirmDiv($res);

      $page = new utPage('none');
      $page->close();
      exit;
    }
  // }}}


  // {{{ _updateGroup()
  /**
   * Update group details in the database for the current tournament 
   *
   * @access private
   * @return void
   */
  function _updateGroup()
    {
      $dt = $this->_dt;
      
      // Get the informations
      $tie['tie_nbms']= kform::getInput('groupNbms');
      $tie['tie_nbws']= kform::getInput('groupNbws');
      $tie['tie_nbmd']= kform::getInput('groupNbmd');
      $tie['tie_nbwd']= kform::getInput('groupNbwd');
      $tie['tie_nbxd']= kform::getInput('groupNbxd');
      $tie['tie_nbas']= kform::getInput('groupNbas');
      $tie['tie_nbad']= kform::getInput('groupNbas', 0);

      $rund['rund_id']        = kform::getInput("groupId");
      $rund['rund_name']      = kform::getInput("groupName");
      $rund['rund_stamp']     = kform::getInput("groupStamp");
      $rund['rund_type']      = kform::getInput("groupType");
      $rund['rund_rankType']  = kform::getInput("groupRankType");
      $rund['rund_drawId']    = kform::getInput("divId");
      $rund['rund_matchWin']  = kform::getInput('matchWin');
      $rund['rund_matchLoose']= kform::getInput('matchLoose');
      $rund['rund_matchRtd']  = kform::getInput('matchRtd');
      $rund['rund_matchWO']   = kform::getInput('matchWO');
      $rund['rund_tieWin']    = kform::getInput('tieWin');
      $rund['rund_tieEqualPlus']  = kform::getInput('tieEqualPlus');
      $rund['rund_tieEqual']  = kform::getInput('tieEqual');
      $rund['rund_tieEqualMinus']  = kform::getInput('tieEqualMinus');
      $rund['rund_tieLoose']  = kform::getInput('tieLoose');
      $rund['rund_tieWO']     = kform::getInput('tieWO');
      $rund['rund_tieranktype']     = kform::getInput('tieranktype', WBS_CACL_EQUAL);
      $rund['rund_tiematchdecisif']     = kform::getInput('tiematchdecisif', WBS_MS);
      $rund['rund_tiematchnum']     = kform::getInput('tiematchnum', 1);
      $rund['rund_entries']   =2;
      $rund['rund_qualPlace'] =0;
      $rund['rund_qual']=1;
      $rund['rund_byes']=0;
      $rund['rund_nbms']= kform::getInput('groupNbms');
      $rund['rund_nbws']= kform::getInput('groupNbws');
      $rund['rund_nbmd']= kform::getInput('groupNbmd');
      $rund['rund_nbwd']= kform::getInput('groupNbwd');
      $rund['rund_nbxd']= kform::getInput('groupNbxd');
      $rund['rund_nbas']= kform::getInput('groupNbas');
      $rund['rund_nbad']= 0;

      // Control the informations
      if ($rund['rund_name'] == "")	$this->_displayFormGroup($rund, $tie, 'msgrund_name');

      if ( $tie['tie_nbms'] < 0 || $tie['tie_nbws'] < 0 ||
	   $tie['tie_nbmd'] < 0 || $tie['tie_nbwd'] < 0 ||
	   $tie['tie_nbxd'] < 0 || $tie['tie_nbas'] < 0 ||
	   $tie['tie_nbad'] < 0 )	
	$this->_displayFormGroup($rund, $tie, 'msgPositif');
      
      // Update the group
      $res = $dt->updateGroup($rund, $tie);
      if (is_array($res))
	$this->_displayFormGroup($rund, $tie, $res['errMsg']);

      // Positionner les equipes dans les rencontres      
      // Si le type de groupe a change
      if ($rund['rund_type'] != kform::getInput("oldGroupType"))
	$dt->updateT2T($rund['rund_id']);

      // Recalculate the ranking (in case of change
      // of rankType field
      $utt =  new utteam();
      $utt->updateGroupRank($rund['rund_id']);

      $page = new utPage('none');
      $page->close();
      exit;
    }
  // }}}

  // {{{ _deleteGroup()
  /**
   * Delte the selected group of the division
   *
   * @access private
   * @return void
   */
  function _deleteGroup()
    {
      $utr = new utround();
      
      // Get the informations
      $groupsId = kform::getInput("rowsGroups");

      // Delete the group
      foreach($groupsId as $id)
	{
	  $res = $utr->delRound($id);
	  if (is_array($res)) 
	    $this->_displayFormConfirmGroups($res);
	}
      $page = new utPage('none');
      $page->close();
      exit;
    }
  // }}}

  // {{{ _updateDiv()
  /**
   * Update a division in the database for the current tournament 
   *
   * @access private
   * @return void
   */
  function _updateDiv()
    {
      $dt = $this->_dt;
      
      // Get the informations
      $infos = array('draw_name'    => kform::getInput('divName'),
		     'draw_stamp'   => kform::getInput('divStamp'),
		     'draw_pbl'     => kform::getInput('divPbl'),
		     'draw_id'      => kform::getInput('divId'),
		     'draw_type'    => WBS_EVENT_IC);

      // Control the informations
      // !!! Todo : control that the name don't exist !!!!!
      if ($infos['draw_name'] == "")
	{
	  $infos['errMsg'] = 'msgdiv_name';
	  $this->_displayFormDiv($infos);
	} 

      if (($infos['draw_pbl'] != WBS_DATA_PUBLIC) &&
	  ($infos['draw_pbl'] != WBS_DATA_PRIVATE) &&
	  ($infos['draw_pbl'] != WBS_DATA_CONFIDENT))
	{
	  $infos['draw_pbl'] = WBS_DATA_CONFIDENT;
	} 

      // update the division
      $utdr = new utdraw();
      $res = $utdr->updateDrawDef($infos);
      if (is_array($res))
        {
          $infos['errMsg'] = $res['errMsg'];
          $this->_displayFormDiv($infos);
        }

      $res = $dt->publiDiv($infos['draw_id'], $infos['draw_pbl'],
			   kform::getInput('propagation'));
      if (is_array($res))
        {
          $infos['errMsg'] = $res['errMsg'];
          $this->_displayFormDiv($infos);
        }

      $page = new utPage('none');
      $page->close();
      exit;
      
    }
  // }}}

  // {{{ _createDiv()
  /**
   * Create a division in the database for the current tournament 
   *
   * @access private
   * @return void
   */
  function _createDiv()
    {
      $dt = $this->_dt;
      
      // Get the informations
      $draw['draw_name']  = kform::getInput('divName');
      $draw['draw_stamp'] = kform::getInput('divStamp');
      $draw['draw_id']    = kform::getInput('divId');
      $draw['draw_pbl']   = WBS_DATA_CONFIDENT;
      $draw['draw_type']  = WBS_EVENT_IC;
      
      $nbRound = kform::getInput('divSize');

      $tie['tie_nbms']= kform::getInput('groupNbms');
      $tie['tie_nbws']= kform::getInput('groupNbws');
      $tie['tie_nbmd']= kform::getInput('groupNbmd');
      $tie['tie_nbwd']= kform::getInput('groupNbwd');
      $tie['tie_nbxd']= kform::getInput('groupNbxd');
      $tie['tie_nbas']= kform::getInput('groupNbas');
      $tie['tie_nbad']= kform::getInput('groupNbas', 0);
      $rund['rund_matchWin']= kform::getInput('matchWin');
      $rund['rund_matchLoose']= kform::getInput('matchLoose');
      $rund['rund_matchWO']= kform::getInput('matchWO');
      $rund['rund_matchRtd']= kform::getInput('matchRtd');
      $rund['rund_tieWin']= kform::getInput('tieWin');
      $rund['rund_tieEqualPlus']= kform::getInput('tieEqualPlus');
      $rund['rund_tieEqual']= kform::getInput('tieEqual');
      $rund['rund_tieEqualMinus']= kform::getInput('tieEqualMinus');
      $rund['rund_tieLoose']= kform::getInput('tieLoose');
      $rund['rund_tieWO']= kform::getInput('tieWO');
      $rund['rund_tieranktype'] = kform::getInput('tieranktype', WBS_CALC_EQUAL);
      $rund['rund_tiematchdecisif'] = kform::getInput('tiematchdecisif', WBS_MS);
      $rund['rund_tiematchnum'] = kform::getInput('tiematchnum', 1);
      $rund['rund_rankType']= WBS_CALC_RESULT;
      $rund['rund_id'] = -1;
      $rund['rund_size'] = 2;
      $rund['rund_entries'] = 2;
      $rund['rund_qualPlace']= 0;
      $rund['rund_qual'] = 1;
      $rund['rund_type'] = WBS_TEAM_GROUP;
      $rund['rund_byes'] = 0;
      $rund['rund_nbms']= kform::getInput('groupNbms');
      $rund['rund_nbws']= kform::getInput('groupNbws');
      $rund['rund_nbmd']= kform::getInput('groupNbmd');
      $rund['rund_nbwd']= kform::getInput('groupNbwd');
      $rund['rund_nbxd']= kform::getInput('groupNbxd');
      $rund['rund_nbas']= kform::getInput('groupNbas');
      $rund['rund_nbad']= 0;

      // Control the informations
      // !!! Todo : control that the name don't exist !!!!!
      if ($draw['draw_name'] == "")
	{
	  $this->_displayFormNewDiv($draw, $rund, $tie, 'msgdiv_name');
	} 

      if ( $tie['tie_nbms'] < 0 || $tie['tie_nbws'] < 0 ||
	   $tie['tie_nbmd'] < 0 || $tie['tie_nbwd'] < 0 ||
	   $tie['tie_nbxd'] < 0 || $tie['tie_nbas'] < 0 || $nbRound < 0 )
	{
	  $this->_displayFormNewDiv($draw, $rund, $tie, 'msgPositif');
	}

      // Create the division
      $utd = new utdraw();
      $drawId  = $utd->updateDrawDef($draw);
      if (empty($drawId)) 
	{
	  $err = __FILE__.' ('.__LINE__.');base inaccessible';
	  $this->_displayFormNewDiv($draw, $rund, $tie, $err);
	} 

      // Create round and ties
      $utr = new utround();
      $rund['rund_drawId'] = $drawId;
      for ($i=0; $i < $nbRound; $i++)
	{
	  $rund['rund_name']=$draw['draw_name']."_$i";
	  $rund['rund_stamp']=$draw['draw_stamp']."_$i";
	  //$infos['rund_byes']=$i++;
	  $res = $utr->updateRound($rund, $tie);
	  if (empty($drawId)) 
	    {
	      $err = __FILE__.' ('.__LINE__.');base inaccessible';
	      $this->_displayFormNewDiv($draw, $rund, $tie, $err);
	    } 
	}      

      $page = new utPage('none');
      $page->close();
      exit;
     }
  // }}}

  // {{{ _displayDiv()
  /**
   * Display a page with the list of the divisions of the current event
   *
   * @access private
   * @return void
   */
  function _displayDiv()
    {
      $utt =  new utteam();;
      
      // Find division to display
      $divSel = kform::getData();
      $divs = $utt->getDivs();
      if ( count($divs) &&
	   ($divSel == '' || !isset($divs[$divSel])))
	{
	  $first = each($divs); 
	  $divSel = $first[0]; 
	}

      // Find group to display
      $groups = $utt->getGroups($divSel);
      $groupSel = '';
      if ( count($groups))
	{
	  $first = each($groups); 
	  $groupSel = $first[0]; 
	}
      
      $this->_displayFormList($divSel, $groupSel);
    }  

  // {{{ _displayFormList()
  /**
   * Display a page with the list of the divisions of the current event
   *
   * @access private
   * @return void
   */
  function _displayFormList($divSel, $groupSel)
    {
      $dt = $this->_dt;
      $utt =  new utteam();;
      
      // Creating a new page
      $utpage = new utPage_A('divs', true, 1);
      $content =& $utpage->getContentDiv();      

      // Adding a menu with the list of divisions
      // (ie the draws in the database)
      $items['itNew'] = array(KAF_NEWWIN, 'divs', KID_NEW,  0, 620, 500);

      $divs = $utt->getDivs();
      if (isset($divs['errMsg']))
	{
	  $content->addWng($divs['errMsg']);
	  unset($divs['errMsg']);
	}
      $itemSel = -1;
      if (count($divs))
	{
	  foreach ($divs as $idDiv=>$nameDiv)
	    {
	      $items[$nameDiv] = array(KAF_UPLOAD, 'divs', 
				       WBS_ACT_DIVS, $idDiv);
	    }
	  $itemSel = $divs[$divSel];
	}

      $kdiv =& $content->addDiv('divMenuEvent', 'onglet3');
      $kdiv->addMenu("menuDiv", $items, $itemSel);
      $kdiv =& $content->addDiv('contDiv', 'cont3');
      if (!count($divs))
	{
	  $kdiv->addWng('msgNoDivs');
	  $utpage->display();
	  exit; 
	}

      // Displaying informations of current division

      $infos = $dt->getDiv($divSel);
      //$kdivMain =& $kdiv->addDiv('divSel');
      $kdivSel =& $kdiv->addDiv('divInfo', 'blkData');

      $kdiv2 =& $kdivSel->addDiv('divImg');
      $size['maxWidth'] = 30;
      $size['maxHeight'] = 30;
      $logo = utimg::getPubliIcon($infos['div_del'], 
				  $infos['div_pbl']);
      $kimg =& $kdiv2->addImg('divName', $logo, $size);
      $kimg->setText($infos['div_name']);

      $kdivSel->addInfo("divStamp",   $infos['div_stamp']);


      $items = array();
      $items['btnModify'] = array(KAF_NEWWIN, 'divs', 
		       KID_EDIT,  $divSel, 350, 220);
      $items['btnDelete'] = array( KAF_NEWWIN, 'divs', 
		       DIV_CONFIRM_DEL, $divSel, 350, 180);
      $kdivSel->addMenu('menuModif', $items, -1, 'classMenuBtn');

      $kdiv->addDiv('break1', 'blkNewPage');

      //--- List of the groups (ie round in the database) 
      //of the selected division 
      $groups = $utt->getFullGroups($divSel);
      if (isset($groups['errMsg']))
	{
	  $content->addWng($Groups['errMsg']);
	  unset($groups['errMsg']);
	}

      $kform =& $kdiv->addForm('fGroups');

      $itms['itNew']    = array(KAF_NEWWIN, 'divs', 
				DIV_ADD_GROUP,  0, 600, 500);
      $itms['itPrint']  = array(KAF_NEWWIN, 'divs', DIV_PDF,
				$divSel, 600, 600);
      $kform->addMenu('menuLeft', $itms, -1);
      $itmj['itTies'] = array(KAF_UPLOAD, 'ties', WBS_ACT_TIES,
			     $divSel);
      $kform->addMenu('menuRight', $itmj, -1);
      $kform->addDiv('page', 'blkNewPage');

      // Display the teams of the groups
      $kdiv =& $kdiv->addDiv('divTeams');
      //$kform =& $kdiv->addForm('fTeams');
      //$kform->addBtn('btnPdf', KAF_NEWWIN, 'divs', DIV_PDF, $divSel, 600, 600);
      $kform->addHide('divId', $divSel);

      for ($k=1;$k<6; $k++)
	$title[$k] = "rowsTeams$k";

      $rows = array();
      foreach($groups as $group)
	{
	  $groupId = $group['rund_id'];
	  $teams = $utt->getTeamsGroup($groupId);
	  $first = $rows;
	  $first[] = array(KOD_BREAK, "title", $group['rund_name'],'', $groupId);
	  if (count($teams) && 
	      !isset($teams['errMsg']))
	    $rows = array_merge($first, $teams);
	  else
	    $rows = $first;
	}
      if (count($rows))
	{
	  $krow =& $kform->addRows("rowsTeams", $rows);
	  $krow->setSort(0);
	  $sizes[6] = '0+';
	  $krow->setSize($sizes);

	  $teamsId = kform::getInput("rowsTeams");
	  if (is_array($teamsId))
	    $krow->setSelect($teamsId);

	  $img[1]='iconPbl';
	  $krow->setLogo($img);

	  $actions[0]= array(KAF_NEWWIN, 'asso', KID_EDIT, 'team_id', 
			     450, 550);
	  $actions[1] = array(KAF_UPLOAD, 'teams', KID_SELECT);
	  $krow->setActions($actions);
	  $krow->setTitles($title);

	  $acts = array();
	  $acts[] = array('link' => array(KAF_NEWWIN, 'divs', DIV_CONFIRM_GROUPS,
					  0, 300, 150),
			   'icon' => utimg::getIcon(WBS_ACT_DROP),
			   'title' => 'Delete Group');
	  $acts[] = array('link' => array(KAF_NEWWIN, 'divs', GROUP_EDIT_A, 
					  0, 600, 500),
			   'icon' => utimg::getIcon(WBS_ACT_EDIT),
			   'title' => 'Edit Group');
	  $acts[] = array('link' => array(KAF_NEWWIN, 'divs', GROUP_TEAM_A, 
					  0, 500, 400),
			   'icon' => utimg::getIcon(GROUP_TEAM_A),
			   'title' => 'Select Team');
	  $acts[] = array('link' => array(KAF_NEWWIN, 'divs', TEAM_UP_A,
					  0, 100, 100),
			   'icon' => utimg::getIcon(TEAM_UP_A),
			   'title' => 'teamUp');
	  $acts[] = array('link' => array(KAF_NEWWIN, 'divs', TEAM_DOWN_A,
					  0, 100, 100),
			   'icon' => utimg::getIcon(TEAM_DOWN_A),
			   'title' => 'teamDown');
	  $krow->setBreakActions($acts);	  


	}
      else
	$kdiv->addWng("msgNoTeams");

      $rows = array();
      foreach($groups as $group)
	{
	  $title = array('firstRound', 'secondRound');
	  $actions[0] = array(KAF_UPLOAD, 'teams', KID_SELECT);
	  if ( $group['rund_type'] == WBS_TEAM_KO)
	    {
	      $groupId = $group['rund_id'];
	      $teams = $utt->getTeamsGroup($groupId);
	      $names= array();
	      foreach($teams as $team)
		$names[] = array('value' => $team['team_name'], 
				 'link' => $team['team_id'],
				 't2r_posRound' => $team['t2r_posRound'],
		) ;
	      require_once "utils/utko.php";
	      $utko = new utKo($names);
	      $vals = $utko->getExpandedValues();

	      $kdiv2 = & $kdiv->addDiv("divDraw$groupId", 'cartouche');
	      $kdiv2->addMsg($group['rund_name'], '', 'kTitre');
	      $kdraw = & $kdiv2->addDraw("draw$groupId");
	      $kdraw->setNumCol(2);
	      $kdraw->setValues(1, $vals);

	      $posTies = $utko->getNumTies(2);
	      $vals = $dt->getDrawTeam($groupId, $posTies);
	      $kdraw->setValues(2, $vals);

	      $kdraw->setActions($actions);

	      $kdraw->setTitles($title);

	    }
	}
      $kdiv->addDiv('break', 'blkNewPage');
      $kdiv2 =& $kdiv->addDiv('divLgd', 'blkLegende');
      $kdiv2->addImg('lgdConfident', utimg::getIcon(WBS_DATA_CONFIDENT));
      $kdiv2->addImg('lgdPrivate', utimg::getIcon(WBS_DATA_PRIVATE));
      $kdiv2->addImg('lgdPublic', utimg::getIcon(WBS_DATA_PUBLIC));

      $utpage->display();
      exit; 
    }
  // }}}

  // {{{ _displayFormDiv()
  /**
   * Display the form to modifiy division
   *
   * @access private
   * @param array $eventId  id of the event
   * @return void
   */
  function _displayFormDiv($div='')
    {
      $dt = $this->_dt;
      
      $utpage = new utPage('divs');
      $content =& $utpage->getPage();
      $form =& $content->addForm('fdivs', 'divs', DIV_UPDATE_A);

      if (is_array($div))
	  $infos = $div;
      else
	  $infos = $dt->getDiv($div);

      $form->setTitle('tEditDiv');

      // Display warning if exist
      if (isset($infos['errMsg']))
	$form->addWng($infos['errMsg']);
      
      // Display information of division
      $form->addHide('divId',   $infos['div_id']);
      $kedit =& $form->addEdit('divName',   $infos['div_name']);
      $kedit->setLength(25);
      $kedit->setMaxLength(25);
      $kedit =& $form->addEdit('divStamp',   $infos['div_stamp']);
      $kedit->setLength(25);
      $kedit->setMaxLength(15);
      $form->addRadio('divPbl', $infos['div_pbl']==WBS_DATA_PUBLIC, 
		      WBS_DATA_PUBLIC);
      $form->addRadio('divPbl', $infos['div_pbl']==WBS_DATA_PRIVATE, 
		      WBS_DATA_PRIVATE);
      $form->addRadio('divPbl', $infos['div_pbl']==WBS_DATA_CONFIDENT, 
		      WBS_DATA_CONFIDENT);
      $elts = array('divPbl3','divPbl2', 'divPbl1');
      $form->addBlock('blkPubli', $elts);

      $form->addCheck('propagation', false,1);
      $form->addBlock('blkPropa', 'propagation');

      $elts = array('divName', 'divStamp', 'blkPubli', 'blkPropa');
      $form->addBlock('blkDiv', $elts);

      $form->addBtn('btnRegister', KAF_SUBMIT);
      $form->addBtn('btnCancel');
      $elts  = array('btnRegister', 'btnCancel');
      $form->addBlock('blkBtn', $elts);
      
      //Display the form
      $utpage->display();
      
      exit;
    }
  // }}}
  

  // {{{ _displayFormNewDiv()
  /**
   * Display the form to create division
   *
   * @access private
   * @param array $eventId  id of the event
   * @return void
   */
  function _displayFormNewDiv($div='')
    {
      $dt = $this->_dt;
      $ut = $this->_ut;
      
      $utpage = new utPage('divs');
      $content =& $utpage->getPage();
      $form =& $content->addForm('fdivs', 'divs', DIV_CREATE_A);

      if (is_array($div))
	  $infos = $div;
      else
	  $infos = array('div_name'     => '',
			 'div_stamp'    => '',
			 'div_size'     => 1,
			 'div_id'       => -1);

      $infos['tie_nbms'] = 1;
      $infos['tie_nbws'] = 1;
      $infos['tie_nbmd'] = 1;
      $infos['tie_nbwd'] = 1;
      $infos['tie_nbxd'] = 1;
      $infos['tie_nbas'] = 0;
      $infos['tie_nbad'] = 0;
      $infos['rund_matchWin']   = 1;
      $infos['rund_matchLoose'] = 0;
      $infos['rund_matchRtd']   = 0;
      $infos['rund_matchWO']    = -1;
      $infos['rund_tieWin']     = 3;
      $infos['rund_tieEqual']   = 2;
      $infos['rund_tieEqualPlus']  = 0;
      $infos['rund_tieEqualMinus'] = 0;
      $infos['rund_tieLoose']   = 1;
      $infos['rund_tieWO']      = 0;			
      $infos['rund_tieranktype'] = WBS_CALC_EQUAL;
      $infos['rund_tiematchdecisif'] = WBS_MS;
      $infos['rund_tiematchnum'] = 1;
      $form->setTitle('tNewDiv');

      // Display warning if exist
      if (isset($infos['errMsg'])) $form->addWng($infos['errMsg']);
      
      // Display information of division
      $form->addHide('divId',   $infos['div_id']);
      $kedit =& $form->addEdit('divName',   $infos['div_name']);
      $kedit->setLength(25);
      $kedit->setMaxLength(25);
      $kedit =& $form->addEdit('divStamp',   $infos['div_stamp']);
      $kedit->setLength(25);
      $kedit->setMaxLength(10);
      $kedit =& $form->addEdit('divSize',   $infos['div_size']);
      $kedit->setLength(2);
      $kedit->setMaxLength(2);
      
      $elts = array("divName", "divStamp", "divSize");
      $form->addBlock("blkDiv", $elts);

      $kedit =& $form->addEdit('groupNbms',  $infos['tie_nbms'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('groupNbws',  $infos['tie_nbws'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('groupNbmd',  $infos['tie_nbmd'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('groupNbwd',  $infos['tie_nbwd'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('groupNbxd',  $infos['tie_nbxd'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('groupNbas',  $infos['tie_nbas'], 2);
      $kedit->setMaxLength(2);
      $elts=array('groupNbms', 'groupNbws', 'groupNbmd', 
		  'groupNbwd', 'groupNbxd', 'groupNbas');
      $form->addBlock('blkNbMatch',  $elts);      

      for ($i=WBS_CALC_RANK; $i<=WBS_CALC_EQUAL; $i++) $list[$i] = $ut->getLabel($i);
	  $kcombo=& $form->addCombo('tieranktype', $list, $infos['rund_tieranktype']);
      for ($i=WBS_MS; $i<=WBS_AS; $i++) $lists[$i] = $ut->getLabel($i);
	  $kcombo=& $form->addCombo('tiematchdecisif', $lists, $infos['rund_tiematchdecisif']);
	  
      $kedit =& $form->addEdit('tiematchnum',  $infos['rund_tiematchnum'], 2);
      $kedit->setMaxLength(2);
      $elts=array('tieranktype', 'tiematchdecisif', 'tiematchnum');
      $form->addBlock('blkResTie',  $elts);      
      
      $kedit =& $form->addEdit('matchWin',  $infos['rund_matchWin'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('matchLoose',  $infos['rund_matchLoose'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('matchRtd',  $infos['rund_matchRtd'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('matchWO',  $infos['rund_matchWO'], 2);
      $kedit->setMaxLength(2);
      $elts=array('matchWin', 'matchLoose', 'matchRtd', 'matchWO');
      $form->addBlock('blkMatchPoint',  $elts);      

      $kedit =& $form->addEdit('tieWin',  $infos['rund_tieWin'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('tieLoose',  $infos['rund_tieLoose'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('tieEqual',  $infos['rund_tieEqual'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('tieEqualPlus',  $infos['rund_tieEqualPlus'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('tieEqualMinus',  $infos['rund_tieEqualMinus'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('tieWO',  $infos['rund_tieWO'], 2);
      $kedit->setMaxLength(2);
      
      $elts=array('tieWin', 'tieEqualPlus', 'tieEqual', 'tieEqualMinus', 'tieLoose', 'tieWO'); 
      $form->addBlock('blkTiePoint',  $elts);      

      $elts=array('blkNbMatch','blkMatchPoint','blkResTie','blkTiePoint'); 
      $form->addBlock('blkNewGroup',  $elts);      
      
      $form->addBtn('btnRegister', KAF_SUBMIT);
      $form->addBtn('btnCancel');
      $elts  = array('btnRegister', 'btnCancel');
      $form->addBlock('blkBtn', $elts);
      
      //Display the form
      $utpage->display();
      
      exit;
    }
  // }}}
  
  
  // {{{ _displayFormGroup()
  /**
   * Display the form to modify a group
   *
   * @access private
   * @param array $group  id or information of the group
   * @return void
   */
  function _displayFormGroup($group='',$tie='', $err='')
    {
      $dt = $this->_dt;
      $utt =  new utteam();;

      $utpage = new utPage('divs');
      $content =& $utpage->getPage();
      $form =& $content->addForm('fGroup', 'divs', GROUP_UPDATE_A);
      $form->setTitle('tEditGroup');

      // Get the data of the draw  
      if (is_array($group))
	{
	  $infos = $group;
	  if (is_array($tie)) $infos = array_merge($group, $tie);
	  $groupId  = $infos['rund_id'];
	}
      else
	{
	  if($group=='') $groupId  = kform::getData();
	  else $groupId  = $group;
	  $infos = $dt->getGroup($groupId);
	}

      // Display warning if exist
      if (isset($infos['errMsg'])) $form->addWng($infos['errMsg']);
      // Display warning if exist
      if ($err!='') $form->addWng($err);
      
      // Initialize the field
      $form->addHide('groupId', $infos['rund_id']);
      $form->addHide('divId', kform::getInput('divId', -1));

      $kedit =& $form->addEdit('groupName',  $infos['rund_name'], 29);
      $kedit->setMaxLength(30);
      $kedit =& $form->addEdit('groupStamp',  $infos['rund_stamp'], 29);
      $kedit->setMaxLength(10);

      $form->addHide('oldGroupType', $infos['rund_type']);
      $form->addRadio('groupType', $infos['rund_type']==WBS_TEAM_GROUP, 
      		      WBS_TEAM_GROUP);
      $form->addRadio('groupType', $infos['rund_type']==WBS_TEAM_BACK, 
      		      WBS_TEAM_BACK);
      //$form->addRadio('groupType', $infos['rund_type']==WBS_QUALIF, 
      //      WBS_QUALIF);
      $form->addRadio('groupType', $infos['rund_type']==WBS_TEAM_KO, 
		      WBS_TEAM_KO);
      //$actions = array( 1 => array(KAF_VALID, 'draws', DRAW_TYPE_A));
      //$form->setActions('draw_type', $actions);
      $elts = array('groupType1', 'groupType2', 'groupType3');
      $form->addBlock('blkType', $elts);
      
      $form->addRadio('groupRankType', 
		      $infos['rund_rankType']==WBS_CALC_RANK, 
		      WBS_CALC_RANK);
      $form->addRadio('groupRankType', 
		      $infos['rund_rankType']==WBS_CALC_RESULT, 
		      WBS_CALC_RESULT);
      $elts = array('groupRankType1', 'groupRankType2');
      $form->addBlock("blkRankType", $elts);
      
      $elts=array('groupName', 'groupStamp', 'groupSize', 
		  'blkType', 'blkRankType');
      $form->addBlock("blkUpdate",  $elts);
      
      $form->addDiv('breakc', 'blkNewPage');
      
      $kedit =& $form->addEdit('groupNbms',  $infos['rund_nbms'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('groupNbws',  $infos['rund_nbws'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('groupNbmd',  $infos['rund_nbmd'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('groupNbwd',  $infos['rund_nbwd'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('groupNbxd',  $infos['rund_nbxd'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('groupNbas',  $infos['rund_nbas'], 2);
      $kedit->setMaxLength(2);
      $elts=array('groupNbms', 'groupNbws', 'groupNbmd',
		  'groupNbwd', 'groupNbxd', 'groupNbas');
      $form->addBlock('blkNbMatch',  $elts);      


      $kedit =& $form->addEdit('matchWin',  $infos['rund_matchWin'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('matchLoose',  $infos['rund_matchLoose'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('matchRtd',  $infos['rund_matchRtd'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('matchWO',  $infos['rund_matchWO'], 2);
      $kedit->setMaxLength(2);
      $elts=array('matchWin', 'matchLoose', 'matchRtd', 'matchWO');
      $form->addBlock('blkMatchPoint',  $elts);      

      $ut = $this->_ut;
      for ($i=WBS_CALC_RANK; $i<=WBS_CALC_EQUAL; $i++) $list[$i] = $ut->getLabel($i);
	  $kcombo=& $form->addCombo('tieranktype', $list, $infos['rund_tieranktype']);
      for ($i=WBS_MS; $i<=WBS_AS; $i++) $lists[$i] = $ut->getLabel($i);
	  $kcombo=& $form->addCombo('tiematchdecisif', $lists, $infos['rund_tiematchdecisif']);
	  
      $kedit =& $form->addEdit('tiematchnum',  $infos['rund_tiematchnum'], 2);
      $kedit->setMaxLength(2);
      $elts=array('tieranktype', 'tiematchdecisif', 'tiematchnum');
      $form->addBlock('blkResTie',  $elts);      
      
      $kedit =& $form->addEdit('tieWin',  $infos['rund_tieWin'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('tieLoose',  $infos['rund_tieLoose'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('tieEqualPlus',  $infos['rund_tieEqualPlus'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('tieEqualMinus',  $infos['rund_tieEqualMinus'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('tieEqual',  $infos['rund_tieEqual'], 2);
      $kedit->setMaxLength(2);
      $kedit =& $form->addEdit('tieWO',  $infos['rund_tieWO'], 2);
      $kedit->setMaxLength(2);
      $elts=array('tieWin', 'tieEqualPlus', 'tieEqual', 'tieEqualMinus', 'tieLoose', 'tieWO'); 
      $form->addBlock('blkTiePoint',  $elts);      

      $form->addDiv('break', 'blkNewPage');
      $form->addBtn('btnRegister', KAF_SUBMIT);
      $form->addBtn('btnCancel');
      $elts=array('btnRegister', 'btnCancel');
      $form->addBlock('btnGroup', $elts, 'blkBtn');

      //Display the form
      $utpage->display();
      exit;
    }
  // }}}

}
?>
