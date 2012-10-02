<?php
/*****************************************************************************
 !   Module     : Users
 !   File       : $Source: /cvsroot/aotb/badnet/src/users/users_V.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.10 $
 !   Author     : G.CANTEGRIL
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/01/18 07:51:21 $
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

require_once "users.php";
require_once "base_V.php";
require_once "utils/utpage_V.php";

/**
 * Module de gestion des utilisateurs
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class Users_V extends users
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
	function Users_V()
	{
	}
	// }}}

	// {{{ start()
	/**
	 * Start the users module
	 *
	 * @access public
	 * @return void
	 */
	function start($page)
	{
		$ut = new Utils();
		$id = kform::getData();
		switch ($page)
		{
	  // Modification of an existing user
			case KID_EDIT :
				$this->_displayFormUser($id);
				break;
				 
				// Update the database for a user
			case KID_UPDATE :
				$this->_updateUser();
				break;
				 
				// Modification of the password
			case USERS_CHANGEPWD :
				parent::_displayFormPwd($id);
				break;
				 
				// Valid the new password
			case USERS_VALIDPWD:
				parent::_updatePwd();
				break;

			case WBS_NEWLANG:
				utvars::setLanguage(kform::getData());
				$page = new kPage('none');
				$page->close();
				exit;
				break;

			case WBS_ACT_USERS:
				$this->_displayFormMainUser();
				break;
			default:
				//$ut->log("users_V->start: page interdite. page=$page");
				echo "users_V->start: page interdite.";
				exit;
		}
	}
	// }}}



	// {{{ _displayFormMainUser()
	/**
	 * Create the form to display the data of a user
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormMainUser()
	{
		$ute = new utevent();
		$dt = new userBase_V();
		$ut =new utils();
		
		utvars::setSessionVar('theme', WBS_THEME_NONE);
		utvars::setEventId(-1);
		if ( $ut->getParam('limitedUsage') )
		{
			$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']
			. '?bnAction=196357';
			header("Location: $url");
		}

		$utPage = new utPage_V('users', true, 0);
		$content =& $utPage->getContentDiv();


		// $infos['user_skin'] = $ut->getPref('skin', 'base');
		//if (isset($infos['errMsg']))
		//	$kdiv->addWng($infos['errMsg']);

		// Display  user's informations but not for 'demo' user
		$userId = utvars::getUserId();
		$infos  = $dt->getUser($userId);
		if ($infos['user_login'] == 'demo')
		exit;

		$kdiv =& $content->addDiv('blkUser', 'cartouche');
		$kdiv->addMsg('tInfos', '', 'titre');
		$blk =& $kdiv->addDiv('divUser', 'blkInfo');
		$blk->addInfo('userPseudo',    $infos['user_pseudo']);
		$blk->addInfo('userLogin',     $infos['user_login']);
		$blk->addInfo('userEmail',     $infos['user_email']);
		$blk->addInfo('userLang',      $infos['user_lang']);
		$blk->addInfo('userSkin',      $infos['user_skin']);
		$blk->addInfo('userLastvisit', $infos['user_lastvisit']);
		$blk->addInfo('userNbcnx',     $infos['user_nbcnx']);
		 
		// add bouton to modify user information
		$kdiv3 = &$blk->addDiv('btn', 'blkBtn');
		$kdiv3->addbtn('btnUser', KAF_NEWWIN, 'users', KID_EDIT,
		$userId, 400, 350);
		$kdiv3->addbtn('btnPwd', KAF_NEWWIN, 'users', USERS_CHANGEPWD,
		$userId, 350, 160);

		// Display  the list of events with friend right
		$blk =& $content->addDiv('blkEntryEvent', 'cartouche');
		$asso = $dt->getFriendAsso();
		$blk->addMsg($asso['asso_name'],'','titre');
		$blk->addMsg('msgEntries');

		$events = $dt->getFriendEvents();
		if (isset($events['errMsg']))
		{
	  $blk->addWng($events['errMsg']);
	  unset($events['errMsg']);
		}
		if (count($events))
		{
	  $its=array();
	  foreach($events as $key=>$event)
	  $its[$event['evnt_name']] = array(KAF_UPLOAD, 'asso', KID_SELECT,
					      "{$event['a2t_assoId']}&eventId={$event['evnt_id']}");
	  $blk->addMenu('menuLeft', $its, -1);
		}

		// Display  the list of events with management right
		$dt = new userBase_V();
		$blk =& $content->addDiv('blkManageEvent', 'cartouche');
		$blk->addMsg('tManager', '', 'titre');
		$blk->addMsg('msgManager');
		$events = $dt->getManageEvents();
		if (isset($events['errMsg']))
		{
	  $blk->addWng($events['errMsg']);
	  unset($events['errMsg']);
		}
		if (count($events))
		{
	  $its=array();
	  $krow = & $blk->addRows('rowsEvent', $events);
	  $actions[2] = array(KAF_UPLOAD, 'cnx',
	  WBS_ACT_SELECT_EVENT);
	  $krow->setActions($actions);
		}

		$blk->addMsg('msgAssist');
		$events = $dt->getManageEvents(WBS_AUTH_ASSISTANT);
		if (isset($events['errMsg']))
		{
	  $blk->addWng('noManage', $events['errMsg']);
	  unset($events['errMsg']);
		}
		if (count($events))
		{
	  $its=array();
	  $krow = & $blk->addRows('rowsAssist', $events);
	  $actions[2] = array(KAF_UPLOAD, 'cnx',
	  WBS_ACT_SELECT_EVENT);
	  $krow->setActions($actions);
	  /*
	   foreach($events as $key=>$event)
	   $its[$event] = array(KAF_UPLOAD, 'cnx',
	   WBS_ACT_SELECT_EVENT, $key);
	   $blk->addMenu('eventsAssist', $its, -1);
	   */
		}

		$kmsg =& $content->addDiv('break', 'blkNewPage');
		$utPage->display();
		exit;
	}
	// }}}



	// {{{ _updateUser()
	/**
	 * Update the user in the database
	 *
	 * @access private
	 * @return void
	 */
	function _updateUser()
	{
		$ut = new Utils();
		$dt = new UserBase_V();

		// Get the data
		$infos = array('user_name'  =>kform::getInput("userName"),
                     'user_pseudo'=>kform::getInput("userPseudo"),
                     'user_login' =>kform::getInput("userLogin"),
                     'user_lang'  =>kform::getInput("userLang"),
                     'user_email' =>kform::getInput("userEmail"),
                     'user_cmt'   =>kform::getInput("userCmt"),
                     'user_skin'  =>kform::getInput("userSkin"),
                     'user_id'    =>kform::getInput("userId"));


		// Control the informations
		if ($infos['user_name'] == "")
		{
	  $infos['errMsg'] = 'msguserName';
	  $this->_displayFormUser($infos);
		}

		if ($infos['user_pseudo'] == "")
		{
	  $infos['errMsg'] = 'msguserPseudo';
	  $this->_displayFormUser($infos);
		}

		if ($infos['user_login'] == "")
		{
	  $infos['errMsg'] = 'msguserLogin';
	  $this->_displayFormUser($infos);
		}

		if ($infos['user_lang'] == "")
		{
	  $infos['errMsg'] = 'msguserLang';
	  $this->_displayFormUser($infos);
		}

		// Verify if the login already exist
		if ($dt->existLogin($infos['user_login'],$infos['user_id']))
		{
	  $infos['errMsg'] = 'msgLoginExist';
	  $this->_displayFormUser($infos);
		}

		//Verify the autorization: an user can only modify his own data
		$user = utvars::getUserId();
		if ($user != $infos['user_id'])
		{
	  $infos['errMsg'] = 'msgBadUpdate';
	  //$ut->log("users_V->_updateUser: tentative de modification interdite", PEAR_LOG_WARNING);
	  //$ut->log("    Id auteur :$user; Id victime :".$infos['user_id'], PEAR_LOG_WARNING);
	  $this->_displayFormUser($infos);
		}

		// The users "demo" can't be modify
		$userData = $dt->getUser($user);
		if ( $userData['user_login'] == 'demo')
		{
	  $infos['errMsg'] = 'msgBadUpdate';
	  //$ut->log("users_V->_updateUser: tentative de modification interdite", PEAR_LOG_WARNING);
	  //$ut->log("    Id auteur :$user; Id victime :".$infos['user_id'], 		   PEAR_LOG_WARNING);
	  $this->_displayFormUser($infos);
		}

		// Ok for updating database
		$res = $dt->setUser($infos);
		if (is_array($res)) $this->_displayFormUser($res);
		else
		{
	  // All is OK. Close the window
	  $page = new kPage('none');
	  $page->close();
	  exit;
		}
		exit;
	}
	// }}}


	// {{{ _displayFormUser()
	/**
	 * Display a form to modify a user
	 *
	 * @access public
	 * @param integer $userId  Id of the user to modify.
	 * @return void
	 */
	function _displayFormUser($userId)
	{
		$ut = new Utils();
		$dt = new UserBase_V();

		$utpage = new utPage('users');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tEditUser', 'users', KID_UPDATE);

		// Get the data of the users
		if (is_array($userId))
		$infos = $userId;
		else
		$infos = $dt->getUser($userId);

		// Display a warning if an error occured
		if (isset($infos['errMsg']))
		$form->addWng($infos['errMsg']);

		// Initialize the field
		$form->addHide('userId',   $infos['user_id']);
		$kedit =& $form->addEdit('userName', $infos['user_name'] , 30);
		$kedit->setMaxLength(50);
		$kedit =& $form->addEdit('userPseudo', $infos['user_pseudo'] , 30);
		$kedit->setMaxLength(20);
		$kedit =& $form->addEdit('userLogin',  $infos['user_login'], 30);
		$kedit->setMaxLength(20 );
		$handle=opendir('./lang');
		while ($file = readdir($handle))
		{
			if ($file != "." && $file != "..")
			$language[$file] = $file;
		}
		closedir($handle);
		$form->addCombo('userLang', $language, $infos['user_lang'] );

		$handle=opendir('../skins');
		while ($file = readdir($handle))
		{
			if ($file != "." && $file != ".." && $file != "CVS")
			$skin[$file] = $file;
		}
		closedir($handle);
		$form->addCombo('userSkin', $skin, $infos['user_skin'] );

		$kedit =& $form->addEdit('userEmail', $infos['user_email'] , 30 );
		$kedit->setMaxLength(50);
		$kedit->noMandatory();

		$karea =& $form->addArea('userCmt', $infos['user_cmt']);
		$karea->noMandatory();

		$elts = array('userName', 'userPseudo', 'userLogin', 'userSkin',
		    'userEmail', 'userCmt', 'userLang');
		$form->addBlock('blkUser', $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the form
		$utpage->display();
		exit;
	}
	// }}}
}

?>