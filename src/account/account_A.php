<?php
/*****************************************************************************
!   Module     : Account
!   File       : $Source: /cvsroot/aotb/badnet/src/account/account_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.5 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/05/06 07:59:33 $
!   Mailto     : cage@free.fr
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
******************************************************************************/

require_once "utils/utpage_A.php";
require_once "utils/utimg.php";
require_once "asso/asso.inc";
require_once "purchase/purchase.inc";
require_once "base_A.php";
require_once "account.inc";

/**
* Module de gestion des associations : classe visiteurs
*
* @author Gerard CANTEGRIL
* @see to follow
*
*/

class account_A
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
  function account_A()
    {
      $this->_ut = new utils();
      $this->_dt = new accountBase_A();
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
	  // Display main windows for account
	case KID_NEW:
	  $this->_displayFormAccount();
	  break;
	case KID_EDIT:
	  $id = kform::getData();        
	  $this->_displayFormAccount($id);
	  break;
	case KID_SELECT:
	  $id = kform::getData();        
	  $this->_displayFormMainAccount($id);
	  break;
	case ACCOUNT_SELECT:
	  $id = kform::getInput('accountId');        
	  $this->_displayFormMainAccount($id);
	  break;
	case ACCOUNT_MEMBERS:
	  $id = kform::getData();        
	  $this->_displayFormMembersAccount($id);
	  break;
	case ACCOUNT_CREDITS:
	  $id = kform::getData();        
	  $this->_displayFormCreditsAccount($id);
	  break;
	case ACCOUNT_PRINT:
	  $id = kform::getData();        
	  $this->_print($id);
	  break;
	case KID_UPDATE:
	  $this->_updateAccount();
	  break;
	case KID_CONFIRM:
	  $this->_displayFormConfirm();
	  break;
	case KID_DELETE:
	  $this->_delAccount();
	  break;
	case WBS_ACT_ACCOUNT:
	  $this->_displayFormList();
	  break;

	default:
	  echo "page $page demand�e depuis account_A<br>";
	  exit();
	}
    }
  // }}}

  // {{{ _print()
  /**
   * List the purchase
   * @return void
   */
  function _print($accountId)
    {
      require_once "pdf/pdfbase.php";      
      $dt =& $this->_dt;

      $pdf = new pdfbase();
      $account = $dt->getAccount($accountId);
      $rows['top'] =  $pdf->start('P', $account['team_name']);

      $cmds = $dt->getPurchases($accountId, 1, -1);
      foreach($cmds as $cmd)
	{
   	  unset ($cmd['cmd_id']);
	  unset ($cmd['regi_id']);
  	  $rows[] = $cmd;
	}
      $sums = $dt->getSumPurchases($accountId, 1, -1);
      $rows[] = array('', '', 'Total') + $sums;

      $titres = array ('Date', 'Achat', 'Identite', 'Cout', 'Remise', 'Paye', 'Restant du');
      $tailles = array (20, 80, 40, 15, 15, 15, 20);
      $styles = array ('B','','','','','','');
      $rows['orientation'] = 'L';
      $pdf->genere_liste($titres, $tailles, $rows, $styles);
      $pdf->end();
      return;
    }
  // }}}

  // {{{ _displayFormCeditsAccount()
  /**
   * Display the page for an account with the list of purchase
   *
   * @access private
   * @param array $member  info of the member
   * @return void
   */
  function _displayFormCreditsAccount($accountId)
    {
      $ut =& $this->_ut;
      $dt =& $this->_dt;

      $kdiv =& $this->_displayHead($accountId, 'itCredits');    

      // Display menu for credits
      $form =& $kdiv->addForm('fpurchase'); 
      $itsm['itNew'] = array(KAF_NEWWIN, 'purchase', PURCHASE_ADD_CREDIT, $accountId, 350, 200);
      $itsm['itDelete'] = array(KAF_NEWWIN, 'purchase', PURCHASE_CONFIRM, 0, 
				250, 100);     
      $form->addMenu('menuLeft', $itsm, -1);

      // Add list of purchase of this account
      for($i=1; $i<7; $i++)	$title[$i] = "rowsCredit$i";
      $sort = kform::getSort("rowsPurchase", 2);
      $purchases = $dt->getCredits($accountId, $sort);
      if (isset($purchases['errMsg'])) 	$form->addWng($purchases['errMsg']);
      else
 	{
	  $krows =& $form->addRows('rowsPurchase', $purchases);
	  $sizes[7] = 0;
	  $krows->setSize($sizes);
	  //$actions[0] = array(KAF_NEWWIN, 'purchase', KID_EDIT, 
	  //		      0, 400,250);
	  $actions[3] = array(KAF_UPLOAD, 'regi', KID_SELECT,7);
	  $krows->setActions($actions);
	  $krows->setTitles($title);
	}
      //Display the page
      $this->_utpage->display();
      exit;
    }
  // }}}

  // {{{ _displayFormMembersAccount()
  /**
   * Display the page for an account with the list of members
   *
   * @access private
   * @param array $member  info of the member
   * @return void
   */
  function _displayFormMembersAccount($accountId)
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      $kdiv =& $this->_displayHead($accountId, 'itMembers');    
      // Display menu for members
      $form =& $kdiv->addForm('fmember'); 
      // Add list of purchase of this account
      $sort = kform::getSort("rowsMembers", 3);
      $members = $dt->getMembers($accountId, $sort);
      if (isset($members['errMsg'])) $form->addWng($members['errMsg']);
      else
 	{
 	  $itsm['itNewCredit'] = array(KAF_NEWWIN, 'purchase', PURCHASE_ADD_CREDIT, 0, 350, 200);
      $form->addMenu('menuLeft', $itsm, -1);
 		
	  $krows =& $form->addRows('rowsMembers', $members);
	  $sizes[5] = 0;
	  //$krows->setSize($sizesPlayer);
	  $actions[0] = array(KAF_NEWWIN, 'regi', KID_EDIT, 0, 650,450);
	  $actions[2] = array(KAF_UPLOAD, 'regi', KID_SELECT);
	  $krows->setActions($actions);
	}
      //Display the page
      $this->_utpage->display();
      exit;
    }
  // }}}

  // {{{ _displayFormMainAccount()
  /**
   * Display the page for an account with the list of purchase
   *
   * @access private
   * @param array $member  info of the member
   * @return void
   */
  function _displayFormMainAccount($accountId)
    {
      $ut = $this->_ut;
      $dt = $this->_dt;

      $kdiv =& $this->_displayHead($accountId, 'itPurchases');    

      // Display menu for purchase
      $date = kform::getInput('accountDate', date(DATE_DATE));
      $date = -1;
      $sumPurchases = $dt->getSumPurchases($accountId, 1, $date);
      if (!isset($sumPurchases['errMsg']))
	{
	  $kdiv->addInfo('accountValue',    sprintf('%02.2f', $sumPurchases['value']));
	  $kdiv->addInfo('accountDiscount', sprintf('%02.2f', $sumPurchases['discount']));
	  $kdiv->addInfo('accountPayed',    sprintf('%02.2f', $sumPurchases['payed']));
	  $kdiv->addInfo('accountSolde',    sprintf('%02.2f', $sumPurchases['solde']));
	  $elts = array('accountValue', 'accountDiscount', 'accountPayed', 'accountSolde');
	}

      $kdiv->addDiv('breakI', 'blkNewPage');
      $form =& $kdiv->addForm('fpurchase'); 
      $itsm['itNew'] = array(KAF_NEWWIN, 'purchase', KID_NEW, $accountId, 350, 400);
      $itsm['itDelete'] = array(KAF_NEWWIN, 'purchase', KID_CONFIRM, 0, 250, 100);     
      $itsm['itPrint'] = array(KAF_NEWWIN, 'account', ACCOUNT_PRINT, $accountId, 400, 400);
      $form->addMenu('menuLeft', $itsm, -1);
      $form->addDiv('breakJ', 'blkNewPage');

      $form->addHide('accountId', $accountId);
      //$form->addBtn('btnGo', KAF_UPLOAD, 'account',  ACCOUNT_SELECT);
      //$form->addEdit('accountDate', $date, 12);
      //$form->addDiv('breakM', 'blkNewPage');

      // Add list of purchase of this account
      $sort = kform::getSort("rowsPurchase", 2);
      $purchases = $dt->getPurchases($accountId, $sort, $date);
      if (isset($purchases['errMsg']))
 	$form->addWng($purchases['errMsg']);
      else
 	{
	  $krows =& $form->addRows('rowsPurchase', $purchases);
	  $sizes[8] = '0+';
	  $krows->setSize($sizes);
	  $actions[0] = array(KAF_NEWWIN, 'purchase', KID_EDIT, 0, 400,400);
	  $actions[3] = array(KAF_UPLOAD, 'regi', KID_SELECT, 'regi_id');
	  $krows->setActions($actions);
	}
      //Display the page
      $this->_utpage->display();
      exit;
    }
  // }}}

  // {{{ _delAccount()
  /**
   * Delete the selected account in the database
   *
   * @access private
   * @param  integer $isDelete  new status of the event
   * @return void
   */
  function _delAccount()
    {
      $dt = $this->_dt;
      $err = '';

      // Get the id's of the teams to delete
      $accountsId = kform::getInput("rowsAccounts");

      // Delete the teams
      $err = $dt->delAccounts($accountsId);
      if (isset($err['errMsg']))
	$this->_displayFormConfirm($err['errMsg']);

      // Close the windows
      $page = new kPage('none');
      $page->close();
      exit; 
    }
  // }}}
  
  // {{{ _displayFormList()
  /**
   * Display a page with the list of the accounts
   *
   * @access private
   * @return void
   */
  function _displayFormList($err='')
    {
      $ut = $this->_ut;
      $data = $this->_dt;
      
      // Creating a new page
      $utpage = new utPage_A('account', true, 'itAccounts');
      $content =& $utpage->getContentDiv();
      $form =& $content->addForm('faccount'); 

      $form->addMsg('tAccounts'); 

      if ($err!= '')
	$form->addWng($err); 
      
      // Menu for management of serial
      $itsm['itNew'] = array(KAF_NEWWIN, 'account', 
				  KID_NEW,  0, 340, 300);
      $itsm['itDelete'] = array(KAF_NEWWIN, 'account', KID_CONFIRM, 0, 250, 150);
      $form->addMenu("menuAction", $itsm, -1);
      $form->addDiv('break', 'blkNewPage');

      // List of teams
      $sort = kform::getSort("rowsAccounts",2);     
      $rows = $data->getAccounts($sort);
      if (isset($rows['errMsg'])) $form->addWng($rows['errMsg']);
      else
	{
	  $krow =& $form->addRows("rowsAccounts", $rows);

	  $sizes[3] = 0;
	  $krow->setSize($sizes);

	  $actions[0] = array(KAF_NEWWIN, 'account', KID_EDIT, 0, 350,300);
	  $actions[1] = array(KAF_UPLOAD, 'account', KID_SELECT);
	  $krow->setActions($actions);

	}

      $utpage->display();
      exit; 
    }
  // }}}
  
  // {{{ _displayFormAccount($infos="")
  /**
   * Display the page for creating or updating an account
   *
   * @access private
   * @param array $member  info of the member
   * @return void
   */
  function _displayFormAccount($account='')
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      $utpage = new utPage('account');
      $content =& $utpage->getPage();
      // Initialize the field
      if (is_numeric($account))	$infos = $dt->getAccount($account);
      else
	$infos = array('cunt_id'    => kform::getInput('cuntId', -1),
		       'cunt_name'  => kform::getInput('cuntName'),
		       'cunt_code'  => kform::getInput('cuntCode'),
		       'cunt_status' => kform::getInput('cuntStatus'),
		       'cunt_cmt'    => kform::getInput('cuntCmt')
		     );
      
      if ($infos['cunt_id'] != -1) $form =& $content->addForm('tEditAccount', 'account', KID_UPDATE);
      else	$form =& $content->addForm('tNewAccount', 'account', KID_UPDATE);
      
      // Display the error if any 
      if (isset($infos['errMsg'])) $form->addWng($infos['errMsg']);
            
      // Initialize the field
      $form->addHide("cuntId", $infos['cunt_id']);
      $form->addHide("cuntStatus", $infos['cunt_status']);
      
      $kedit =& $form->addEdit('cuntName',  $infos['cunt_name']);
      $kedit->setMaxLength(30);
      $kedit =& $form->addEdit('cuntCode', $infos['cunt_code']);
      $kedit->setMaxLength(10);
      $karea =& $form->addArea('cuntCmt', $infos['cunt_cmt']);      
      $karea->noMandatory();
      $elts = array('cuntName', 'cuntCode', 'cuntStatus', 'cuntCmt');
      $form->addBlock('blkAccountForm', $elts);
      
      $form->addBtn('btnRegister', KAF_SUBMIT);
      $form->addBtn('btnCancel');
      $elts = array('btnRegister', 'btnCancel');
      $form->addBlock('blkBtn', $elts);

      //Display the page
      $utpage->display();
      exit;
    }
  // }}}
  
  // {{{ _updateAccount()
  /**
   * Create the member in the database
   *
   * @access private
   * @return void
   */
  function _updateAccount()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Get the informations
      $infos = array('cunt_id'     =>kform::getInput('cuntId'),
		     'cunt_name'   =>kform::getInput('cuntName'),
                     'cunt_code'   =>kform::getInput('cuntCode'),
                     'cunt_status' =>kform::getInput('cuntStatus'),
                     'cunt_cmt'    =>kform::getInput('cuntCmt'),
                     'cunt_eventId'=>utvars::getEventId()
                     );
      
      // Control the informations
      if ($infos['cunt_name'] == "")
	{
	 $err['errMsg'] = 'msgcuntName';
 	 $this->_displayFormAccount($err);
	} 

      if ($infos['cunt_code'] == "")
	{
	 $err['errMsg'] = 'msgcuntCode';
 	 $this->_displayFormAsso($err);
	} 
      
      // Add the account
      $res= $dt->updateAccount($infos);
      if (is_array($res))
        {
          $this->_displayFormAccount($res);
        }
      $page = new kPage('none');
      $page->close();
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
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      $utpage = new utPage('account');
      $content =& $utpage->getPage();
      $form =& $content->addForm('tDelAccount', 'account', KID_DELETE);


      // Initialize the field
      $ids = kform::getInput("rowsAccounts");
      if ($ids != '' && $err == '')
	{
	  foreach($ids as $id)
	    $form->addHide("rowsAccounts[]", $id);
	  $form->addMsg('msgConfirmDel');
	  $form->addBtn('btnDelete', KAF_SUBMIT);
	}
      else
	{
	  if ($ids == '')
	    $form->addWng('msgNeedAccounts');
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

  // {{{ _displayHead()
  /**
   * Display header of the page
   *
   * @access private
   * @param array team  info of the team
   * @return void
   */
  function & _displayHead($accountId, $select)
    {
      $dt =& $this->_dt;
      // Create a new page
      $this->_utpage = new utPage_A('account', true, 'itAccounts');
      $content =& $this->_utpage->getContentDiv();

      // List of accounts
      $accounts = $dt->getAccounts(2);
      foreach($accounts as $account) $list[$account['cunt_id']] = $account['cunt_name'];
      $kcombo=& $content->addCombo('selectList', $list, $list[$accountId]);
      $acts[1] = array(KAF_UPLOAD, 'account', KID_SELECT);
      $kcombo->setActions($acts);   

      // Get team's informations
      $account = $dt->getAccount($accountId);
      if (isset($account['errMsg'])) $content->addWng($asso['errMsg']);

      // Display general informations
      $div =& $content->addDiv('blkAccount', 'blkData');
      $div->addMsg($account['cunt_name'], '', 'kTitre');
      $div->addInfo("cuntCode", $account['cunt_code']);
      if ($account['team_id'] != '')
	{
	  $kinfo =& $div->addInfo("cuntTeam", $account['team_name']);
	  $act[1] = array(KAF_UPLOAD, 'teams', KID_SELECT, $account['team_id']);
	  $kinfo->setActions($act);
	}
      else
	  $kinfo =& $div->addInfo("cuntTeam", "msgPersonnalAccount");
      $div->addInfo("cuntCmt", $account['cunt_cmt']);

      $items = array();
      $items['btnModify'] = array(KAF_NEWWIN, 'account', KID_EDIT,  $accountId, 340, 300);
      $div->addMenu('menuModif', $items, -1, 'classMenuBtn');

      $content->addInfo('solde', sprintf('%02.2f', $account['solde']));
      $content->addDiv('breakH', 'blkNewPage');
      // Add a menu for different action
      $div =& $content->addDiv('choix', 'onglet3');
      $items = array();
      $items['itPurchases'] = array(KAF_UPLOAD, 'account', KID_SELECT, $accountId);
      $items['itCredits'] = array(KAF_UPLOAD, 'account', ACCOUNT_CREDITS, $accountId);
      $items['itMembers'] = array(KAF_UPLOAD, 'account', ACCOUNT_MEMBERS, $accountId);
      $div->addMenu("menuType", $items, $select);
      $kdiv =& $content->addDiv('register', 'cont3');

      return $kdiv;
    }
}

?>