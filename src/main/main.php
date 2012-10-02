<?php
/*****************************************************************************
!   Module    : Main
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
require_once "users/users.inc";
require_once "base.php";

/**
* Module de login d'un utilisateur
*
* @author Gerard CANTEGRIL <cage@aotb.org>
* @see to follow
*
*/

class Main
{

    // {{{ properties
    /**
     * Form to diplay
     *
     * @var     mixed
     * @access  private
     */
    var $_form;

    // }}}

    // {{{ constructor
    /**
     * Constructor. 
     *
     * @access public
     * @return void
     */
    function Main()
    {
    }
    // }}}


    // {{{ _displayForm()
    /**
     * Display a page with the list of the events
     *
     * @access private
     * @param  pointer  $form   form to display
     * @return void
     */
    function _displayForm()
    {
        $ut = new Utils();
        $dt = new mainBase();
        $form = $this->_form;

        $userId = $ut->getUserId();
        $infos  = $dt->getUser($userId);
	if (isset($infos['errMsg']))
           $form->addWng($infos['errMsg']);
        else
        {
	  $form->addinfo("user_name",      $infos['user_name']);
	  $form->addinfo("user_login",     $infos['user_login']);
	  $form->addinfo("user_email",     $infos['user_email']);
	  $form->addinfo("user_cmt",       $infos['user_cmt']);
	  $form->addinfo("user_lang",      $infos['user_lang']);
	  $form->addinfo("user_lastvisit", $infos['user_lastvisit']);
	  $form->addinfo("user_nbcnx",     $infos['user_nbcnx']); 

          // add bouton to modify user information but not for 'demo' user
          if ($infos['user_login'] != 'demo')
          {
  	    $form->addbtn("btnUser");
            $actions = array( 1 => array(KAF_NEWWIN, 'users', KID_EDIT,
                     $userId, 400, 290));
            $form->setActions("btnUser", $actions);
	    $form->addbtn("btnPwd");
            $actions = array( 1 => array(KAF_NEWWIN, 'users', USERS_CHANGEPWD,
                     $userId, 350, 160));
            $form->setActions("btnPwd", $actions);
          }
	  $elts=array("user_login", "user_email", "user_cmt", "user_lang",
                      "user_lastvisit", "user_nbcnx", "btnUser", "btnPwd");

          $form->addBloc("blkUser", $elts); 

          $form->addBloc("blkEvents", "Events"); 
          $form->addBloc("blkBooks", "Books"); 
        }
        $form->display(false);
        exit; 
    }
    // }}}
}

?>