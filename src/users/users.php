<?php
/*****************************************************************************
!   Module     : Users
!   File       : $Source: /cvsroot/aotb/badnet/src/users/users.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
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

require_once "utils/utevent.php";
require_once "users.inc";
require_once "base.php";

/**
* Module de gestion des utilisateurs
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class Users
{

  // {{{ properties

  // }}}
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @param integer $width Width f the new windows
   * @param integer $height Height of the new windows
   * @access public
   * @return void
   */
  function Users()
    {
    }
  // }}}
  
  // {{{ _displayFormPwd()
  /**
   * Display a form to modify the password
   *
   * @access public
   * @param  mixed   $userId  Id of the user or information of the users
   * @return void
   */
  function _displayFormPwd($userId)
    {
      $dt = new UserBase();
      
      $utpage = new utPage('users');
      $content =& $utpage->getPage();
      $form =& $content->addForm('fUsers', 'users', USERS_VALIDPWD);
      $form->setTitle("tEditPwd");
               
      // Get the data of the users  
      if (is_array($userId))
	$infos = $userId;
      else
	$infos = $dt->getUser($userId);
      
      //Verify the autorization
      $user = utvars::getUserId();
      if ($user != $infos['user_id'] && 
	  utvars::getAuthLevel() != WBS_AUTH_ADMIN) exit;
      
      // Display a warning if an error occured
      if (isset($infos['errMsg']))
	$form->addWng($infos['errMsg']);
      
      // Initialize the field
      $form->addHide('userId', $infos['user_id']);
      $kedit =& $form->addPwd('oldPwd', '', 10 );
      $kedit->setMaxLength(15);
      $kedit =& $form->addPwd('userPass', '', 10 );
      $kedit->setMaxLength(15 );
      $elts = array("oldPwd", "userPass");
      $form->addBlock("blkUser", $elts);

      $form->addBtn("btnRegister", KAF_SUBMIT);
      $form->addBtn("btnCancel");
      $elts = array("btnRegister", "btnCancel");
      $form->addBlock("blkBtn", $elts);

      // Display the Page
      $utpage->display();
      exit; 
    }
  // }}}


  // {{{ _updatePwd()
  /**
   * Update the pwd of the user
   *
   * @access private
   * @return void
   */
  function _updatePwd()
    {
      $data = new UserBase();
      
      $infos = array('user_name'  =>kform::getInput("userName"),
                     'user_login' =>kform::getInput("userLogin"),
                     'user_id'    =>kform::getInput("userId"),
                     'user_pass'  =>kform::getInput("userPass"));
      $oldPass = kform::getInput("oldPwd"); 
      
      //Verify the autorization
      $user = utvars::getUserId();
      if ($user != $infos['user_id'] && 
          uvars::getAuthLevel() != WBS_AUTH_ADMIN) exit;
      
      // Control the informations
      if ($oldPass == '')
	{
	  $infos['errMsg'] = 'msgoldPwd';
	  $this->_displayFormPwd($infos);
	} 
      
      // Control the informations
      if ($infos['user_pass'] == "")
	{
	  $infos['errMsg'] = 'msguserPass';
	  $this->_displayFormPwd($infos);
	} 
      
      // Control the old pwd
      if (!$data->ctlPwd($infos['user_id'], $oldPass))
        {
          $infos['errMsg'] = 'msgBadPwd';
          $this->_displayFormPwd($infos);
        }
      
      // Modify the password
      $res = $data->setPwd($infos);
      if (is_array($res))
        {
          $this->_displayFormPwd($infos);
        }
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

}

?>