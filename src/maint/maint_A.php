<?php
/*****************************************************************************
!   Module    : maintenance
!   Version   : v0.0.1
!   Author    : G.CANTEGRIL
!   Co-author :
!   Mailto    : cage@aotb.org
!   Date      : 01-12-2003
******************************************************************************
!   License   : Licensed under GPL [http://www.gnu.org/copyleft/gpl.html]
!      This program is free software; you can redistribute it and/or
!      modify it under the terms of the GNU General Public License
!      as published by the Free Software Foundation; either version 2
!      of the License, or (at your option) any later version.
!
!      This program is distributed in the hope that it will be useful,
!      but WITHOUT ANY WARRANTY; without even the implied warranty of
!      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
!      GNU General Public License for more details.
!
!      You should have received a copy of the GNU General Public License
!      along with this program; if not, write to the Free Software
!      Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, 
!      USA.
******************************************************************************
!   Greetings & thanks to:
!      - myself
******************************************************************************
!   History:
!      v0.0.1        creation
******************************************************************************
!   Todo:
!      
******************************************************************************/

require_once "maint.inc";
require_once "base_A.php";
require_once "mber/mber.inc";
require_once "utils/utpage_A.php";
require_once "ajaj/ajaj.inc";

/**
* Module de gestion des preferences
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class maint_A
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
  function maint_A()
    {
      $this->_ut = new utils();
      $this->_dt = new maintBase_A();
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
      
      switch ($page)
        {
	case WBS_ACT_MAINT:
	  //$this->_displayMaint();
	  $this->_displayHead();
	  break;
	case MAINT_UPDATE_DRAWS:
	  $this->_updateDraws();
	  break;
	case MAINT_UPDATE_PAIRS:
	  $this->_updatePairs();
	  break;
	case MAINT_MERGE_ASSO:
	  $this->_displayMergeAsso();
	  break;
	case MAINT_MERGE_MEMBER:
	  $this->_displayMergeMember();
	  break;
	case MAINT_MERGE_CONTACT:
	  $this->_mergeContact();
	  break;
	  default :
	  echo "maint_A: page non autorisee ou inconnue $page";
	}
      exit; 
    }
  // }}}


  // {{{ _mergeContact()
  /**
   * Met a jour les champ step des rencontres
   *
   * @access public
   * @return void
   */
  function _mergeContact()
    {
      $this->_dt->mergeContact();
	  $this->_displayHead();
      exit; 
    }
  // }}}
    
    // {{{ _updateDraws()
  /**
   * Met a jour les champ step des rencontres
   *
   * @access public
   * @return void
   */
  function _updateDraws()
    {
      $this->_dt->updateStepTie();
      $this->_displayMaint();
      exit; 
    }
  // }}}

  // {{{ _displayMergeMember()
  /**
   * Affiche la page de fusion de deux membres
   *
   * @access public
   * @return void
   */
  function _displayMergeMember()
    {
      $utpage = new utPage_A('maint', true, 'itMaint');
      $utpage->_page->addAction('onload', array('initMergeMember'));
      $utpage->_page->addJavaFile('maint/maint.js');
      $kdiv =& $utpage->getContentDiv();

      // Liste des membres
      for($i=ord('A'); $i<=ord('Z'); $i++)
	  $alpha[chr($i)] = chr($i);
      $alphaSel = kform::getInput('destAlphaList', 'A');
      $members = $this->_dt->getMembers($alphaSel);

      // Affichage du membre cible
      $kdiv->addWng('msgMergeMember');
      $form =& $kdiv->addForm('fMergeMember', 'maint', KID_NONE);
      $acts = array();
      $acts[] = array(KAF_AJAJ, 'fillDestMemberList', AJAJ_MAINT_LIST_MEMBER, 'destAlphaList');
      $kcombo =& $form->addCombo('destAlphaList', $alpha, $alphaSel);
      $kcombo->setActions($acts);
      
      $acts = array();
      $acts[] = array(KAF_AJAJ, 'displayDestMember', AJAJ_MAINT_SELECT_MEMBER, 'destMemberList');
      $kcombo =& $form->addCombo('destMemberList', $members);
      $kcombo->setActions($acts);
      $kcombo->setLength(15);

      $form->addEdit('mberId',      '', 40);
      $form->addEdit('mberFirstName',    '', 40);
      $form->addEdit('mberSecondName',   '', 40);
      $form->addEdit('mberSexe',    '', 40);
      $form->addEdit('mberCountryId',  '', 40);
      $form->addEdit('mberBorn','', 40);
      $form->addEdit('mberIbfNumber',     '', 40);
      $form->addEdit('mberLicence',     '', 40);
      $form->addEdit('mberFedeId',    '', 40);
      //      $form->addEdit('mberUniId',  '', 40);
      $form->addEdit('mberUrlPhoto',     '', 40);
      $form->addEdit('mberLockId',  '', 40);

      $form->addBtn('btnModify', 'activeMember', 'fMergeMember', 'mberFirstName');
      $form->addBtn('btnMerge', KAF_AJAJ, 'endMergeMember', AJAJ_MAINT_MERGE_MEMBER);
      $elts =array('btnModify','btnMerge');
      $form->addBlock('divModif', $elts);

      $form->addBtn('btnRegister', KAF_AJAJ, 'endSubmitMember', AJAJ_MAINT_UPDATE_MEMBER);
      $form->addBtn('btnCancel', 'cancelMember', 'fMergeMember');
      $elts =array('btnRegister','btnCancel');
      $form->addBlock('divValid', $elts);

      $elts =array('destMemberList', 'mberId', 'mberFirstName', 'mberSecondName', 
		   'mberSexe',  'mberCountryId', 'mberBorn', 'mberIbfNumber', 
		   'mberLicence', 'mberFedeId', 'mberUniId',
		   'mberUrlPhoto', 'mberLockId', 'divModif','divValid');
      $form->addBlock('blkInfoDest', $elts);

      // Affichage du membre
      $form =& $kdiv->addForm('fSrcMember', 'maint', KID_NONE);
      $alphaSel = kform::getInput('srcAlphaList', 'A');
      $acts = array();
      $acts[] = array(KAF_AJAJ, 'fillSrcMemberList', AJAJ_MAINT_LIST_MEMBER, 'srcAlphaList');
      $kcombo =& $form->addCombo('srcAlphaList', $alpha, $alphaSel);
      $kcombo->setActions($acts);

      $members = $this->_dt->getMembers($alphaSel);
      $acts = array();
      $acts[] = array(KAF_AJAJ, 'displaySrcMember', AJAJ_MAINT_SELECT_MEMBER, 'srcMemberList');
      $kcombo =& $form->addCombo('srcMemberList', $members);
      $kcombo->setActions($acts);
      $kcombo->setLength(15);
      
      $form->addEdit('srcMberId',      '', 40);
      $form->addEdit('srcMberFirstName',    '', 40);
      $form->addEdit('srcMberSecondName',   '', 40);
      $form->addEdit('srcMberSexe',    '', 40);
      $form->addEdit('srcMberCountryId',  '', 40);
      $form->addEdit('srcMberBorn','', 40);
      $form->addEdit('srcMberIbfNumber',     '', 40);
      $form->addEdit('srcMberLicence',     '', 40);
      $form->addEdit('srcMberFedeId',    '', 40);
      //$form->addEdit('srcMberUniId',  '', 40);
      $form->addEdit('srcMberUrlPhoto',     '', 40);
      $form->addEdit('srcMberLockId',  '', 40);
      $elts =array('srcMemberList', 'srcMberId', 'srcMberFirstName', 'srcMberSecondName', 
		   'srcMberSexe',  'srcMberCountryId', 'srcMberBorn', 'srcMberIbfNumber', 
		   'srcMberLicence', 'srcMberFedeId',  'srcMberUniId',
		   'srcMberUrlPhoto', 'srcMberLockId');
      $form->addBlock('blkInfoSrc', $elts);

      $utpage->display();
      exit; 

    }
  // }}}

  // {{{ _displayMergeAsso()
  /**
   * Affiche la page de fusion de deux associations
   *
   * @access public
   * @return void
   */
  function _displayMergeAsso()
    {
      $utpage = new utPage_A('maint', true, 'itMaint');
      $utpage->_page->addAction('onload', array('initMergeAsso'));
      $utpage->_page->addJavaFile('maint/maint.js');
      $kdiv =& $utpage->getContentDiv();

      // Liste des associations
      $assos = $this->_dt->getAssos();

      // Affichage de l'asso cible
      $kdiv->addWng('msgMergeAsso');
      $form =& $kdiv->addForm('fMergeAsso', 'maint', KID_NONE);
      $actions[] = array(KAF_AJAJ, 'displayDestAsso', AJAJ_MAINT_SELECT_ASSO, 'destAssoList');
      $kcombo =& $form->addCombo('destAssoList', $assos);
      $kcombo->setActions($actions);
      $kcombo->setLength(15);
      $form->addEdit('assoId',      '', 40);
      $form->addEdit('assoName',    '', 40);
      $form->addEdit('assoStamp',   '', 40);
      $form->addEdit('assoType',    '', 40);
      $form->addEdit('assoFedeId',  '', 40);
      $form->addEdit('assoCmt',     '', 40);
      $form->addEdit('assoUrl',     '', 40);
      $form->addEdit('assoLogo',    '', 40);
      $form->addEdit('assoNumber',  '', 40);
      $form->addEdit('assoDpt',     '', 40);
      $form->addEdit('assoPseudo',  '', 40);
      $form->addEdit('assoNoc',     '', 40);
      $form->addEdit('assoLockId',  '', 40);
      $form->addEdit('assoUniId',   '', 40);
      $form->addEdit('assoDateCrea',  '', 40);
      $form->addEdit('assoDateUpdt',  '', 40);
      
      $form->addBtn('btnModify', 'activeAsso', 'fMergeAsso', 'assoName');
      $form->addBtn('btnMerge', KAF_AJAJ, 'endMergeAsso', AJAJ_MAINT_MERGE_ASSO);
      $elts =array('btnModify','btnMerge');
      $form->addBlock('divModif', $elts);

      $form->addBtn('btnRegister', KAF_AJAJ, 'endSubmitAsso', AJAJ_MAINT_UPDATE_ASSO);
      $form->addBtn('btnCancel', 'cancelAsso', 'fMergeAsso');
      $elts =array('btnRegister','btnCancel');
      $form->addBlock('divValid', $elts);

      $elts =array('destAssoList', 'assoId', 'assoName', 'assoStamp', 'assoType',  'assoFedeId',
		   'assoCmt', 'assoUrl', 'assoLogo',
		   'assoNumber', 'assoDpt', 'assoPseudo', 'assoNoc','assoUniId','assoDateCrea','assoDateUpdt',
		   'assoLockId', 'divModif','divValid');
      $form->addBlock('blkInfoDest', $elts);

      // Affichage de l'asso source
      $form =& $kdiv->addForm('fSrcAsso', 'maint', KID_NONE);
      $acts[] = array(KAF_AJAJ, 'displaySrcAsso', AJAJ_MAINT_SELECT_ASSO, 'srcAssoList');
      $kcombo =& $form->addCombo('srcAssoList', $assos);
      $kcombo->setActions($acts);
      $kcombo->setLength(15);
      $form->addEdit('srcAssoId',      '', 40);
      $form->addEdit('srcAssoName',    '', 40);
      $form->addEdit('srcAssoStamp',   '', 40);
      $form->addEdit('srcAssoType',    '', 40);
      $form->addEdit('srcAssoFedeId',  '', 40);
      $form->addEdit('srcAssoCmt',     '', 40);
      $form->addEdit('srcAssoUrl',     '', 40);
      $form->addEdit('srcAssoLogo',    '', 40);
      $form->addEdit('srcAssoNumber',  '', 40);
      $form->addEdit('srcAssoDpt',     '', 40);
      $form->addEdit('srcAssoPseudo',  '', 40);
      $form->addEdit('srcAssoNoc',     '', 40);
      $form->addEdit('srcAssoLockId',  '', 40);
      $elts =array('srcAssoList', 'srcAssoId', 'srcAssoName', 'srcAssoStamp', 
		   'srcAssoType',  'srcAssoFedeId',  
		   'srcAssoCmt', 'srcAssoUrl', 'srcAssoLogo',
		   'srcAssoNumber', 'srcAssoDpt', 'srcAssoPseudo', 'srcAssoNoc',
		   'srcAssoLockId');
      $form->addBlock('blkInfoSrc', $elts);

      $utpage->display();
      exit; 

    }
  // }}}

  // {{{ _displaySubscribe()
  /**
   * Display the list of subscription to results
   *
   * @access public
   * @return void
   */
  function _displaySubscribe()
    {
      
      $ut = $this->_ut;
      $dt = $this->_dt;

      // Create the form
      $form = $ut->newForm_A("maint", MAINT_DISPLAY_SUBSCRIBE, true);
      $form->setTitle("wbs_title");         
      
      // Menu for management of serial
      $items = array('ForMaint'  => array(KAF_NONE),
        	     'DisplaySubs'  => array(KAF_UPLOAD, 'maint', 
					     MAINT_DISPLAY_SUBSCRIBE),
        	     'UpdtRank'  => array(KAF_UPLOAD, 'maint', 
					  MAINT_CREATE_REGIRANK),
        	     'UpdtName'  => array(KAF_UPLOAD, 'maint', 
					  MAINT_UPDATE_NAME),
        	     'UpdtPosT2T'  => array(KAF_UPLOAD, 'maint', 
					    MAINT_UPDATE_POST2T),
        	     'ImportRank'  => array(KAF_UPLOAD, 'maint', 
					    MAINT_IMPORT_REGIRANK),
               	     'DelRank'   => array(KAF_CHECKROWS, 'maint', 
					  MAINT_DELETE_REGIRANK, "playersRanking"));
      $form->addMenu("menuRanks", $items);
      $elts = array("menuRanks");
      $form->addBloc("blkMenu", $elts);

      $sort = $form->getSort("subsList", 3);
      $subs = $dt->getSubscriptions($sort);
      $krow =& $form->addRows("subsList", $subs);
      $krow->displaySelect(false);

      $form->display(false);
      exit; 
    }
  // }}}

  // {{{ _displayHead()
  /**
   * Entete de page commune
   *
   * @access public
   * @return void
   */
  function _displayHead()
    {
      $utpage = new utPage_A('maint', true, 'itMaint');
      $kdiv =& $utpage->getContentDiv();

      /*
      $kdiv2 = & $kdiv->addDiv('divContact', 'blkInfo');
      $kdiv2->addMsg('tContact', '', 'kTitre');
      $kdiv2->addMsg('msgContact');
      $items = array();
      $items['btnContact'] = array(KAF_UPLOAD, 'maint',
				 MAINT_MERGE_CONTACT, 0, 350, 180);
      $kdiv2->addMenu('menuContact', $items, -1, 'classMenuBtn');
      */
      
      $kdiv2 = & $kdiv->addDiv('divAsso', 'blkInfo');
      $kdiv2->addMsg('tAsso', '', 'kTitre');
      $kdiv2->addMsg('msgAsso');
      $items = array();
      $items['btnAsso'] = array(KAF_UPLOAD, 'maint',
				 MAINT_MERGE_ASSO, 0, 350, 180);
      $kdiv2->addMenu('menuAsso', $items, -1, 'classMenuBtn');

      $kdiv2 = & $kdiv->addDiv('divMember', 'blkInfo');
      $kdiv2->addMsg('tMember', '', 'kTitre');
      $kdiv2->addMsg('msgMember');
      $items = array();
      $items['btnMember'] = array(KAF_UPLOAD, 'maint',
				 MAINT_MERGE_MEMBER, 0, 350, 180);
      $kdiv2->addMenu('menuMember', $items, -1, 'classMenuBtn');

      /*
      $kdiv2 = & $kdiv->addDiv('divDraws', 'blkInfo');
      $kdiv2->addMsg('tDraws', '', 'kTitre');
      $kdiv2->addMsg('msgDraws');
      $items = array();
      $items['btnDraws'] = array(KAF_UPLOAD, 'maint',
				 MAINT_UPDATE_DRAWS, 0, 350, 180);
      $kdiv2->addMenu('menuDraws', $items, -1, 'classMenuBtn');

      $kdiv2 = & $kdiv->addDiv('divPairs', 'blkInfo');
      $kdiv2->addMsg('tPairs', '', 'kTitre');
      $kdiv2->addMsg('msgPairs');
      $items = array();
      $items['btnPairs'] = array(KAF_NEWWIN, 'maint',
				 MAINT_UPDATE_PAIRS, 0, 350, 180);
      $kdiv2->addMenu('menuPairs', $items, -1, 'classMenuBtn');
*/
      $kdiv->addDiv('break', 'blkNewPage');

      $utpage->display();
      exit; 

    }
  // }}}

}
?>