<?php
/*****************************************************************************
!   Module     : Users
!   File       : $Source: /cvsroot/aotb/badnet/src/users/users_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.8 $
!   Author     : G.CANTEGRIL
!   Mailto     : cage@free.fr
!   Revised by : $Author: cage $
!   Date       : $Date: 2006/07/28 16:25:24 $
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
require_once "base_A.php";
require_once "utils/utevent.php";
require_once "utils/utpage_A.php";


/**
* Module de gestion des utilisateurs: classe administrateur
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class Users_A extends users
{

  // {{{ constructor
  /**
   * Constructor. 
   *
   * @param integer $width Width f the new windows
   * @param integer $height Height of the new windows
   * @access public
   * @return void
   */
  function Users_A()
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
      switch ($page)
        {
	  // Display the list of the users
	case WBS_NEWLANG:
	  utvars::setLanguage(kform::getData());
	  $page = new kPage('none');
	  $page->close();
	  exit; 
	  break;
	case WBS_ACT_USERS:
	  $this->_displayFormList();
	  break;
	case USERS_MANAGER:
	  $this->_displayFormListManagers(WBS_AUTH_MANAGER, 'itManager');
	  break;
	case USERS_ASSISTANT:
	  $this->_displayFormListManagers(WBS_AUTH_ASSISTANT, 'itAssistant');
	  break;
	  // Display the form to create a new user
	case KID_NEW : 
	  $this->_displayUpdateUser();
	  break;
	  // Delete selected users 
	case KID_CONFIRM:
	  $this->_displayFormConfirm();
	  break;
	  // Delete selected users 
	case KID_DELETE:
	  $this->_delUsers();
	  break;
	  // Update user's information
	case KID_UPDATE :
	  $this->_updateUser();
	  break;


	  // Display the main form of selected user
	case  KID_SELECT : 
	  $id = kform::getData();
	  $this->_displayFormUser($id);
	  break;
	  // Display the form to modify an existing user
	case  KID_EDIT : 
	  $id = kform::getData();
	  $this->_displayUpdateUser($id);
	  break;
	  // Display the form to change the password
	case  USERS_CHANGEPWD :
	  $id = kform::getData();
	  parent::_displayFormPwd($id);
	  break;
	  
	  // Update user's password
	case USERS_VALIDPWD:
	  $this->_updatePwd();
	  break;
	  

	  // Add right to selected user
	case USERS_MANAGE_EVNT_A:
	  $this->_addRight(WBS_AUTH_MANAGER);
	  break;
	case USERS_ASSIST_EVNT_A:
	  $this->_addRight(WBS_AUTH_ASSISTANT);
	  break;
	case USERS_FRIEND_EVNT_A:
	  $this->_addRight(WBS_AUTH_FRIEND);
	  break;
	case USERS_GUEST_EVNT_A:
	  $this->_addRight(WBS_AUTH_GUEST);
	  break;
	case USERS_VISIT_EVNT_A:
	  $this->_addRight(WBS_AUTH_VISITOR);
	  break;
	default:
	  echo "page $page demande dans users_A<br>";
	  exit;
	  break;
	}
    }
  // }}}
  
  // {{{ _displayFormConfirm()
  /**
   * Display the page for confirmation the destruction of users
   *
   * @access private
   * @param array $member  info of the member
   * @return void
   */
  function _displayFormConfirm($err='')
    {
      $dt = new UserBase_A();
      
      $utpage = new utPage('users');
      $content =& $utpage->getPage();
      $form =& $content->addForm('tDelUsers', 'users', KID_DELETE);

      // Initialize the field
      $usersId = kform::getInput("rowsUsers");
      if (($usersId != '') &&
	  !isset($err['errMsg']))
	{
	  
	  foreach($usersId as $id)
	    $form->addHide("rowsUsers[]", $id);
	  $form->addMsg('msgConfirmDel');
	  $form->addBtn('btnDelete', KAF_SUBMIT);
	}
      else
	if ($usersId === '')
	  $form->addWng('msgNeedUsers');
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

  // {{{ _delUser()
  /**
   * Delete the selected user in the database
   *
   * @access private
   * @param none
   * @return void
   */
  function _delUsers()
    {
      $data = new UserBase_A();
      
      // Get the id's of the users to delete
      $users = kform::getInput("rowsUsers");
      
      // Delete the users
      $err = $data->delUsers($users);
      if (isset($err['errMs']))
	$this->_displayFormConfirm($err['errMsg']);
      
      $page = new kPage('none');
      $page->close();
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
      $dt = new UserBase_A();
      
      // Get the data
      $infos = array('user_name'  =>kform::getInput("userName"),
                     'user_login' =>kform::getInput("userLogin"),
                     'user_pseudo'=>kform::getInput("userPseudo"),
                     'user_type'  =>kform::getInput("userType"),
                     'user_lang'  =>kform::getInput("userLang"),
                     'user_skin'  =>kform::getInput("userSkin"),
                     'user_email' =>kform::getInput("userEmail"),
                     'user_cmt'   =>kform::getInput("userCmt"),
                     'user_id'    =>kform::getInput("userId"));
      if ($infos['user_id'] == -1)
        $infos['user_pass'] = kform::getInput("userPass");

      // Control the informations
      if ($infos['user_name'] == "")
	{
	  $infos['errMsg'] = 'msguserName';
	  $this->_displayUpdateUser($infos);
	} 
      
      if ($infos['user_login'] == "")
	{
	  $infos['errMsg'] = 'msguserLogin';
	  $this->_displayUpdateUser(-1, $infos);
	} 

      if ($infos['user_pseudo'] == "")
	$this->_displayUpdateUser(-1, 'msguserPseudo');
      
      if ($infos['user_lang'] == "")
	$this->_displayUpdateUser(-1, 'msguserLang');
      
      if ($infos['user_type'] != WBS_AUTH_USER &&
          $infos['user_type'] != WBS_AUTH_ADMIN)
	$this->_displayUpdateUser(-1, 'msguserType');
      
      // An administrator can't change himself his type
      $user = utvars::getUserId();
      if ($user == $infos['user_id'])
        $infos['user_type'] = WBS_AUTH_ADMIN;
      
      // Verify if the login already exist
      if ($dt->existLogin($infos['user_login'],$infos['user_id']))
	$this->_displayUpdateUser(-1, 'msgLoginExist');

      //Verify the autorization: 
      // the users "demo" can't be modify
      $userData = $dt->getUser($infos['user_id']);
      if ( $userData !== false && $userData['user_login'] === 'demo')
	{
	  $infos['errMsg'] = 'msgBadUpdate';
	  $this->_displayUpdateUser(-1, $infos); 
	}
      
      // Ok for updating data
      $assoId = kform::getInput("rghtAssoId", -1);
      $res = $dt->updateUser($infos, $assoId);
      if (is_array($res))
        {
          $this->_displayUpdateUser(-1, $res['errMsg']);
        }
     
      if (kform::getInput('rghtNotify', -1) != -1 &&
	  ($assoId != -1 || $memberId != -1) &&
	  $infos['user_email'] != '')
	{
	
	  require_once "utils/utevent.php";
	  
	  require_once "utils/utmail.php";
	  $file = "lang/{$infos['user_lang']}/users.inc";
	  require ($file);
	  
	  $mailer = new utmail();
	  
	  $eventId = utvars::getEventId();
	  $userId = utvars::getUserId();
	  
	  $mailer->subject("Badminton Netware: cr�ation de compte");
	  $from = "no-reply@badnet.org"; 
	  
	  $mailer->from($from);
	  $site = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?tpl=none";
	  $body = "Bonjour,\n votre compte BadNet est maintenant op�rationnel.\nUtilisez le lien ci-dessous pour acc�der � la page de connexion\n {$site}\n\nNous vous rappelons vos identifiants:\n Login: {$infos['user_login']}\nMot de passe: {$infos['user_pass']}\n\n Sportivement\nL'�quipe BadNet\n";
	  $mailer->body($body);
	  
	  // Send the message
	  $res =  $mailer->send($infos['user_email']);
	  if (PEAR::isError($res))
	    {
	      $mailer->trace();
	      $this->_displayUpdateUser(-1, $res->getMessage());
	    }      
	}
            
      $page = new kPage('none');
      $page->close();
      exit; 
    }
  // }}}
    
  // {{{ _displayFormList()
  /**
   * Display a page with the list of the users
   *
   * @access private
   * @return void
   */
  function _displayFormList()
    {

      $content =& $this->_displayHead('itAll');

      // Menu for management of users
      $items['itNew'] = array(KAF_NEWWIN,  'users', 
			      KID_NEW, 0, 500, 450);
      $items['itDelete'] = array(KAF_NEWWIN, 'users', KID_CONFIRM, 0,
				 300, 150);
      $content->addMenu("menuAction", $items, -1);
      $content->addDiv('break', 'blkNewPage');

      // Fix the pager attributes
      $form =& $content->addForm('fusers'); 
      $sort  = kform::getSort("rowsUsers", 2);
      
      $dt = new UserBase_A();
      $rows = $dt->getUsers($sort);
      if (isset($rows['errMsg']))
        $form->addWng($rows['errMsg']);
      else
        {
	  $user = utvars::getUserId();
	  $krow =& $form->addRows("rowsUsers", $rows);
	  $sizes = array(9=> 0, 0);
	  $krow->setSize($sizes);

	  $img[1] = 9;
	  $krow->setLogo($img);

	  $actions[0] = array(KAF_NEWWIN, 'users', KID_EDIT, 0, 500, 400 );
	  $actions[1] = array(KAF_UPLOAD, 'users', KID_SELECT);
	  $krow->setActions($actions); 
	}
      $kdiv2 = &$content->addDiv('blkLegende');
      $kdiv2->addImg('lgdAdmin', utimg::getIcon(WBS_AUTH_ADMIN));
      $kdiv2->addImg('lgdUser', utimg::getIcon(WBS_AUTH_USER));
      $this->_utpage->display();
      exit; 
    }
  // }}}
  
  // {{{ _displayFormListManagers()
  /**
   * Display a page with the list of the users manager
   *
   * @access private
   * @return void
   */
  function _displayFormListManagers($type, $select)
    {
      $content =& $this->_displayHead($select);
      $form =& $content->addForm('fusers'); 

      // Menu for management of users
      $items['itNew'] = array(KAF_NEWWIN,  'users', 
			      KID_NEW, 0, 500, 450);
      $items['itDelete'] = array(KAF_NEWWIN, 'users', KID_CONFIRM, 0,
				 300, 150);
      //$form->addMenu("menuUsers", $items, -1);

      // Fix the pager attributes
      $sort  = kform::getSort("rowsManagers", 2);      
      $dt = new UserBase_A();
      $rows = $dt->getUsersManager($type, $sort);
      if (isset($rows['errMsg']))
        $form->addWng($rows['errMsg']);
      else
        {
	  $krow =& $form->addRows("rowsManagers", $rows);
	  $sizes = array(9=> 0, 0);
	  //$krow->setSize($sizes);

	  $actions[0] = array(KAF_NEWWIN, 'users', KID_EDIT, 0, 500, 400 );
	  $actions[1] = array(KAF_UPLOAD, 'users', KID_SELECT);
	  //$krow->setActions($actions); 
	}
      $this->_utpage->display();
      exit; 
    }
  // }}}
  
  // {{{ _displayFormUser()
  /**
   * Display a form to modify a user
   *
   * @access public
   * @param array  $user  Infos of the user.
   * @return void
   */
  function _displayFormUser($userId)
    {
      $dt = new UserBase_A();
      
      $utPage = new utPage_A('users', true, -1);
      $content =& $utPage->getContentDiv();
      $content->addMsg("tUsers");

      $items['itUserList'] = array(KAF_UPLOAD, 'users', WBS_ACT_USERS);
      $content->addMenu("smenuLeft", $items, -1);
      $itsp['itHome']   = array(KAF_UPLOAD, 'main', KID_HOME);
      $itsp['itExit']  = array(KAF_UPLOAD, 'users',  KID_LOGOUT);
      $content->addMenu('smenuRight', $itsp, -1);

      $infos  = $dt->getUser($userId);
      if (isset($infos['errMsg']))
	$content->addWng($infos['errMsg']);


      // Menu for management of an user
      $items = array('ForUser'  => array(KAF_NONE),
		     'Modify'   => array(KAF_NEWWIN, 'users', KID_EDIT, 
					 $userId, 400, 290),
		     'Password' => array(KAF_NEWWIN, 'users', USERS_CHANGEPWD,
					 $userId, 350, 160),
		     'SendMail' => array(KAF_NEWWIN, 'users', USERS_CHANGEPWD,
					 $userId, 350, 160));
      //$form->addMenu("menuUsers", $items, -1);

      // Display  user's informations 
      $kdiv =& $content->addDiv('blkInfoUser');
      $content->addinfo("userName",      $infos['user_name']);

      $kdiv->addInfo("userLogin",     $infos['user_login']);
      //$kdiv->addArea("userEmail", $infos['user_email'], 20, 1);
      //$kdiv->addArea("userCmt",   $infos['user_cmt']);
      $kdiv->addInfo("userLang",      $infos['user_lang']);
      $kdiv->addInfo("userLastvisit", $infos['user_lastvisit']);
      $kdiv->addInfo("userNbcnx",     $infos['user_nbcnx']); 
      
      // Display list of fevents
      $form =& $content->addForm('fusers'); 
      $form->addhide("userId",      $userId);
      // Menu for management of tournament
      $items = array('itManage'  => array(KAF_UPLOAD, 'users', 
					USERS_MANAGE_EVNT_A, 'selEvents'),
		     'itAssistant'  => array(KAF_UPLOAD, 'users', 
					USERS_ASSIST_EVNT_A, 'selEvents' ),
		     'itFriend'  => array(KAF_UPLOAD, 'users', 
					USERS_FRIEND_EVNT_A, 'selEvents'),
		     'itGuest'   => array(KAF_UPLOAD, 'users', 
					USERS_GUEST_EVNT_A, 'selEvents'),
		     'itVisitor' => array(KAF_UPLOAD, 'users', 
					USERS_VISIT_EVNT_A, 'selEvents'));
      $form->addMenu("menuEvents", $items, -1);

      // Fix the pager attributes
      $sort  = kform::getSort("selEvents");
      $events = $dt->getEvents($userId);
      if (isset($events['errMsg']))
	{
          $form->addWng($events['errMsg']);
          unset($events['errMsg']);
	}

      $krow =& $form->addRows("selEvents", $events);
      $krow->setSort(0);
      $img[1]=3;
      $img[2]=3;
      $krow->setLogo($img);
      $sizes = array(2=> 0,0);
      $krow->setSize($sizes);

      $kdiv2 = &$content->addDiv('blkLegende');
      $kdiv2->addImg('lgdManager', utimg::getIcon(WBS_AUTH_MANAGER));
      $kdiv2->addImg('lgdAssist', utimg::getIcon(WBS_AUTH_ASSISTANT));
      $kdiv2->addImg('lgdFriend', utimg::getIcon(WBS_AUTH_FRIEND));
      $kdiv2->addImg('lgdGuest', utimg::getIcon(WBS_AUTH_GUEST));
      $kdiv2->addImg('lgdVisitor', utimg::getIcon(WBS_AUTH_VISITOR));

      //Display the form
      $utPage->display();
      exit; 
    }
  // }}}


  // {{{ _displayUpdateUser()
  /**
   * Display a form to modify a user
   *
   * @access public
   * @param array  $user  Infos of the user.
   * @return void
   */
  function _displayUpdateUser($userId=-1, $err='')
    {
      $dt = new UserBase_A();
    
      $utpage = new utPage('users');
      $content =& $utpage->getPage();
      
      // Get the data of the users  
      if ($userId != -1)
	  $infos = $dt->getUser($userId);
      else
        {
	  $infos = array('user_id'    => kform::getInput('userId', -1),
			 'user_name'  => kform::getInput('userName'),
			 'user_pseudo'=> kform::getInput('userPseudo'),
			 'user_pass'  => kform::getInput('userPass'),
			 'user_skin'  => kform::getInput('userSkin'),
			 'user_login' => kform::getInput('userLogin'),
			 'user_lang'  => kform::getInput('userLang'),
			 'user_type'  => kform::getInput('userType', 
							 WBS_AUTH_USER),
			 'user_email' => kform::getInput('userEmail'),
			 'user_cmt'   => kform::getInput('userCmt'),
			 'user_right' => kform::getInput('rghtAssoId'), -1);
        } 
      if ($infos['user_id'] == -1)
	$form =& $content->addForm('tNewUser', 'users', KID_UPDATE);
      else
	$form =& $content->addForm('tEditUser', 'users', KID_UPDATE);
      
      // Display a warning if an error occured
      if (! ($err === ''))
	$form->addWng($err);
      if (isset($infos['errMsg']))
	$form->addWng($infos['errMsg']);
      
      // Initialize the field
      $form->addHide('userId',  $infos['user_id']);
      
      $kedit =& $form->addEdit('userName',  $infos['user_name'] , 45);
      $kedit->setMaxLength(50);
      $kedit =& $form->addEdit('userPseudo',  $infos['user_pseudo'], 45);
      $kedit->setMaxLength(20);
      $kedit =& $form->addEdit('userLogin',  $infos['user_login'], 45);
      $kedit->setMaxLength(20);
      if ($infos['user_id'] == -1)
        {
	  if( $infos['user_pass']=='')
	    {
	      require_once "utils/Password.php";
	      $tpwd = new Text_Password();
	      $infos['user_pass'] = $tpwd->create(6, 'unpronounceable', 'alphanumeric');
	    }
          $kedit =& $form->addEdit('userPass',  $infos['user_pass'], 45);
	  $kedit->setMaxLength(20);
        }
      $handle=opendir('./lang');
      while ($file = readdir($handle))
        {
          if ($file != "." && $file != ".." && $file != "CVS")
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

      $kedit =& $form->addEdit('userEmail', $infos['user_email'] , 45 );
      $kedit->setMaxLength(50);
      $kedit->noMandatory();
      $kedit =& $form->addArea('userCmt', $infos['user_cmt'] , 30, 4 );
      $kedit->noMandatory();
      
      $assos = $dt->getAssos();
      $assos[-1] = "------";
      $select = $infos['user_right'];
      if (!isset($assos[$select]))
	$select = -1;
      $kcombo =& $form->addCombo('rghtAssoId', $assos, $assos[$select]);
      if ($infos['user_id'] == -1)
	$form->addCheck('rghtNotify', '1');      

      $elts = array('userName', 'userPseudo', 'userLogin', 'userPass', 
		    'userLang', 'userSkin', 'userEmail', 'userCmt', 
		    'rghtAssoId', 'rghtNotify');
      $form->addBlock('blkUser', $elts);
      
      $user = utvars::getUserId();
      if ($user != $infos['user_id'])
        {
	  $form->addRadio('userType', $infos['user_type']==WBS_AUTH_ADMIN,
			  WBS_AUTH_ADMIN);
	  $form->addRadio('userType', $infos['user_type']==WBS_AUTH_USER,
			  WBS_AUTH_USER);
	  $elts = array('userType1', 'userType2');
	  $form->addBlock('blkType', $elts);
	}
      else
	{
	  $form->addHide('userType', $infos['user_type']);
	}
      
      $form->addBtn('btnRegister', KAF_SUBMIT );
      $form->addBtn('btnCancel');
      $elts = array('btnRegister', 'btnCancel');
      $form->addBlock('blkBtn', $elts);
      
      //Display the page
      $utpage->display('false');
      exit; 
    }
  // }}}
  
  // {{{ _displayFormRight()
  /**
   * Display a form to modify the right of the users
   *
   * @access private
   * @param integer $userId  Id of the user
   * @param integer $err     Error message
   * @return void
   */
  function _displayFormRight($userId, $err="")
    {
      $data = new UserBase_A();
      
      $form = $ut->newForm_A("users", USERS_RIGHT_A); 
      $form->setTitle("");
      $form->setSubTitle("");
      $form->addMsg("tEditRight");
      
      //Verify the autorization
      if ( utvars::getAuthLevel() != WBS_AUTH_ADMIN) exit;
      
      // Initialize the field
      $types = array( WBS_AUTH_INVITED     => 'itInvited',
                      WBS_AUTH_FRIEND      => 'itFriend',
                      WBS_AUTH_GESTPUBLIC  => 'itPublic',
                      WBS_AUTH_GESTPRIVATE => 'itPrivate',
                      WBS_AUTH_ADMIN       => 'itAdmin');
      $typeSel = kform::getInput("type");
      if ($typeSel == "") $typeSel=WBS_AUTH_ADMIN;
      if ($userId == "") $userId = kform::getInput("userId");
      
      $form->addHide("userId", $userId);
      $form->addCombo("type", $types, $types[$typeSel]);
      $actions = array( 1 => array(KAF_UPLOAD, 'users', 
				   USERS_RIGHT_A, $userId));
      $form->setActions("type", $actions);
      
      $events = $data->getEvents($userId, $typeSel);
      if (isset($events['errMsg']))
        {
	  $form->addWng($events['errMsg']);
	  unset($events['errMsg']);
        }
      $form->addCombo("events", $events);
      $form->setLength("events", 8);
      $elts = array("type", "events");
      $form->addBloc("blkChoice", $elts);
      
      $form->addBtn("AddEvent");
      $actions = array( 1 => array(KAF_NEWWIN, 'users', USERS_SELECTEVNT_A, 
				   $typeSel, 500, 400));
      $form->setActions("AddEvent", $actions);
      
      $form->addBtn("RemoveEvent", KAF_CHECKFIELD, USERS_RMEVNT_A, 0);
      $form->addBtn("End");
      $elts = array("AddEvent", "RemoveEvent", "End");
      $form->addBloc("blkBtn", $elts);
      
      
      //Display the form
      $form->display(false);
      exit; 
    }
  // }}}
  
  

  // {{{ _addRight
  /**
   * Add the right fo the select event
   *
   * @access private
   * @param  char  $right  Right of the current user for the selected event
   * @return void
   */
  function _addRight($right)
    {
      $utev = new utEvent();
      $data = new UserBase_A();
      
      // Initialise the informations
      $eventIds = kform::getInput("selEvents");
      $userId   = kform::getInput("userId");

      // Verify the autorization
      if ( utvars::getAuthLevel() != WBS_AUTH_ADMIN)
	{
	  $res['errMsg'] = 'msgNoRights';
	  $this->_displayFormUser($userId);
	}
      
      // Control the informations
      if ($right != WBS_AUTH_MANAGER &&
	  $right != WBS_AUTH_ASSISTANT &&
	  $right != WBS_AUTH_FRIEND &&
	  $right != WBS_AUTH_GUEST &&
	  $right != WBS_AUTH_VISITOR )
	{
	  $res['errMsg'] = 'msgStatus';
	  $this->_displayFormUser($infos['userId'], $res);
	} 
      
      // Add the right
      $max = count($eventIds);
      for ($i=0; $i<$max; $i++)
	{
	  $res = $utev->addEventRight($userId, $eventIds[$i], $right);
	  if (is_array($res))
	    {
	      // An error occured; display the error message
	      $res['errMsg'] = 'msgStatus';
	    }
	}
      $this->_displayFormUser($userId, $res);

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
      // Create a new page
      $this->_utpage = new utPage_A('users', true, 'itUsers');
      $content =& $this->_utpage->getContentDiv();
      
      // Add a menu for different action
      $kdiv =& $content->addDiv('choix', 'onglet2');
      $items['itAll']     = array(KAF_UPLOAD, 'users',  WBS_ACT_USERS);
      $items['itManager']   = array(KAF_UPLOAD, 'users', USERS_MANAGER);
      $items['itAssistant'] = array(KAF_UPLOAD, 'users', USERS_ASSISTANT);
      $kdiv->addMenu("menuType", $items, $select);
      $kdiv =& $content->addDiv('register', 'cont2');
      return $kdiv;
    }
  // }}}

  
}

?>