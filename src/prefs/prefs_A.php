<?php
/*****************************************************************************
!   Module    : preferences
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

require_once "prefs.inc";
require_once "base_A.php";

/**
* Module de gestion des preferences
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class prefs_A
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
  function prefs_A()
    {
      $this->_ut = new utils();
      $this->_dt = new prefBase_A();
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
	case WBS_ACT_PREFERENCES:
	  $this->display();
	  break;
	case PREF_UPDATE_MAIL:
	  $this->_updateMail();
	  break;
	default :
	  echo "prefs_A:Page inconnue $page";
	  exit;
	  break;
	}
      exit; 
    }
  // }}}


  // {{{ updateMail()
  /**
   * Update mail information in database
   *
   * @access public
   * @return void
   */
  function _updateMail()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Get the informations
      $infos = array('host'      =>kform::getInput("host"),
                     'port'      =>kform::getInput("port"),
                     'username'  =>kform::getInput("username"),
                     'password'  =>kform::getInput("password"),
                     'ffbaEmail' =>kform::getInput("ffbaEmail"),
                     'ffbaUrl'   =>kform::getInput("ffbaUrl"),
                     'ibfUrl'   =>kform::getInput("ibfUrl")
		     );
      $dt->setMailInfos($infos);
      $this->display();
    }
  // }}}


  // {{{ Display()
  /**
   * Start the connexion processus
   *
   * @access public
   * @return void
   */
  function display()
    {
      
      $ut = $this->_ut;
      $dt = $this->_dt;
      // Create the form
      $form = $ut->newForm_A("prefs", KID_NONE, true);
      $form->setTitle("wbs_title");         
      
      // Display informations for email congiguration
      $infos = $dt->getMailInfos();
      $form->addEdit("host", $infos['host']);
      $form->addEdit("port", $infos['port']);
      $form->addEdit("username", $infos['username']);
      $form->addPwd("password", $infos['password']);
      $form->addEdit("ffbaEmail", $infos['ffbaEmail']);
      $form->addEdit("ffbaUrl",   $infos['ffbaUrl'],50);
      $form->addEdit("ibfUrl",   $infos['ibfUrl'],50);
      $form->addBtn("btnUpdateMail");
      $actions = array( 1 => array(KAF_UPLOAD, 'prefs', PREF_UPDATE_MAIL));
      $form->setActions("btnUpdateMail", $actions);

      $elts=array("host", "port", "username", "password", 
		  "ffbaEmail", "ffbaUrl", "ibfUrl", "btnUpdateMail");
      $form->addBloc("blkMail", $elts);

      // Display informations for ranking definition
      $ranks = $dt->getRanks();
      $krow =& $form->addRows("rowsRanks", $ranks);
      $krow->setSort(0);
//       $actions = array( 1 => array(KAF_NEWWIN, 'prefs', PREF_EDIT_RANK, 0, 
// 				   450, 350));
//       $form->setActions("rowsRank", $actions);

//       $form->addBtn("btnAddRank");
//       $actions = array( 1 => array(KAF_NEWWIN, 'prefs', PREF_NEW_RANK));
//       $form->addBtn("btnDelRank");
//       $actions = array( 1 => array(KAF_CHECKROWS, 'prefs', 
// 				   PREF_DEL_RANK, "rowsRanks"));
//       $form->setActions("btnDelRank", $actions);

      $elts=array("rowsRanks", "btnAddRank", "btnDelRank");
      $form->addBloc("blkRank", $elts);

      $form->display(false);
      exit; 
    }
  // }}}
  
}

?>