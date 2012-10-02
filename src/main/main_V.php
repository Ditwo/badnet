<?php
/*****************************************************************************
!   Module     : Main
!   File       : $Source: /cvsroot/aotb/badnet/src/main/main_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.5 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2006/04/06 21:23:42 $
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
require_once "users/cnx.inc";
require_once "users/users.inc";
require_once "utils/utimg.php";
require_once "main.inc";
require_once "base_V.php";

/**
* Module de login d'un utilisateur
*
* @author Gerard CANTEGRIL <cage@aotb.org>
* @see to follow
*
*/

class Main_V
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
  function Main_V()
    {
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
	  // Valid the new password
	case KID_DEFAULT:
	  $ut = new utils();
	  $page = $ut->getPref('page','events');
	  $page = $ut->getPref('page','users');
	  $act = $ut->getPref('action', WBS_ACT_EVENTS);
	  $act = $ut->getPref('action', WBS_ACT_USERS);
	  $form = $page.'_V';
	  $file = CUR_DIR."/$page/$form".'.php';
	  require_once "$file";
	  $a = new $form();
	  $a->start($act);
	  break;

	case WBS_ACT_PREFERENCES:
	  $this->_displayFormMain();
	  break;

	default:
	  echo "main_V->start: page interdite. page=$page";
	  exit;
	}
    }
  // }}}
  
  
  // {{{ _displayFormMain()
  /**
   * Create the form to display
   *
   * @access private
   * @return void
   */
  function _displayFormMain()
    {
      $ut = new Utils();
      $dt = new mainBase_V();
      
      $utPage = new utPage_V('main', true, 'itPref');
      $kdiv =& $utPage->getContentDiv();
      utvars::setSessionVar('theme', WBS_THEME_NONE);

      $userId = utvars::getUserId();
      $infos  = $dt->getUser($userId);
      $infos['user_skin'] = $ut->getPref('skin', 'base');
      if (isset($infos['errMsg']))
 	$kdiv->addWng($infos['errMsg']);

      // Display  user's informations but not for 'demo' user
      $kdiv1 = &$kdiv->addDiv('blkUser', 'block');
      if ($infos['user_login'] != 'demo')
	{
	  $kdiv2 = & $kdiv1->addDiv('info', 'blkInfo');
	  $kdiv2->addInfo('userPseudo',    $infos['user_pseudo']);
	  $kdiv2->addInfo('userLogin',     $infos['user_login']);
	  $kdiv2->addInfo('userEmail',     $infos['user_email']);
	  $kdiv2->addInfo('userLang',      $infos['user_lang']);
	  $kdiv2->addInfo('userSkin',      $infos['user_skin']);
	  $kdiv2->addInfo('userLastvisit', $infos['user_lastvisit']);
	  $kdiv2->addInfo('userNbcnx',     $infos['user_nbcnx']); 
	  
	  // add bouton to modify user information
	  $kdiv3 = &$kdiv2->addDiv('btn', 'blkBtn');
	  $kdiv3->addbtn('btnUser', KAF_NEWWIN, 'users', KID_EDIT,
			$userId, 400, 350);
	  $kdiv3->addbtn('btnPwd', KAF_NEWWIN, 'users', USERS_CHANGEPWD,
			$userId, 350, 160);
	}
      $kdiv->addDiv('force', 'blkNewPage');

      // Display  the list of subscriptions
//       $kdiv->addMsg("tSubscriptions");
//       $rows = $dt->getSubscriptions($userId);
//       if (isset($rows['errMsg']))
// 	$kdiv->addWng($rows['errMsg']);
//       else
//         {
// 	  if (count($rows))
// 	    {
// 	      $krow =& $kdiv->addRows("Subscripts", $rows);
// 	      $krow->displaySelect(false);
// 	      $krow->setSort(0);
	      
// 	      $actions[1] = array(KAF_UPLOAD, 'cnx', 
// 				  WBS_ACT_SELECT_EVENT);
// 	      $krow->setActions($actions);
// 	    }
//         }
      
      
      // Display  the list of published books
//       $kdiv->addMsg("tBooks");
//       $rows = $dt->getBooks($userId);
//       if (isset($rows['errMsg']))
// 	$kdiv->addWng($rows['errMsg']);
//       else
//         {
// 	  if (count($rows))
// 	    {
// 	      $krow =& $kdiv->addRows("Books", $rows);
// 	      $krow->displaySelect(false);
// 	      $krow->setSort(0);
// 	      $sizes = array(3=> 0,0,0);
// 	      $krow->setSize($sizes);
	      
// 	      $img[1]=4;
// 	      $img[2]=5;
// 	      $krow->setLogo($img);

// 	      $actions[1] = array(KAF_UPLOAD, 'cnx', 
// 				  WBS_ACT_SELECT_BOOK);
// 	      $krow->setActions($actions); 
// 	    }
//         }
      
      $utPage->display();
      exit; 
    }
  // }}}
  
}

?>