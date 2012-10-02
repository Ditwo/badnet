<?php
/*****************************************************************************
!   Module     : members
!   File       : $Source: /cvsroot/aotb/badnet/src/mber/mber_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.2 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
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

require_once "base_V.php";


/**
* Module de gestion du carnet d'adresse : classe visiteurs
*
* @author Gerard CANTEGRIL
* @see to follow
*
*/

class Mber_V
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
  function mber_V()
    {
      $this->_ut = new utils();
      $this->_dt = new memberBase_V();
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
      $id = kform::getDataId();
      switch ($page)
        {
	  // Display main windows for member
	case KID_SELECT:
	  $this->_displayFormMember($id);
	  break;
	default:
	  echo "page $page demand�e depuis mber_A<br>";
	  exit();
	}
    }
  // }}}
  
  
  // {{{ _displayFormList()
  /**
   * Display a page with the list of the members
   *
   * @access private
   * @param  integer $sort  Column selected for sort
   * @return void
   */
  function _displayFormList($sort=1)
    {
      $ut = $this->_ut;
      $data = $this->_dt;
      
      $form = $ut->newForm_V("addrbook", KID_NONE); 
      
      // Fix the pager attributes
      $sort = $form->getSort("rowsMembers");
      
      $rows = $data->getMembers($sort);
      if (isset($rows['errMsg']))
	$form->addWng($rows['errMsg']);
      else
	{
	  $form->addRows("rowsMembers", $rows);
	  $userPerm = array();
	  foreach( $rows as $row=>$values)
	    { 
	      $userPerm[$values[0]] = KOD_READ;
	    }
	  $form->setPerms("rowsMembers", $userPerm);
	  /*$sizes = array(1=> '0');
         $form->setLength("rowsMembers", $sizes);*/
	  
	  $actions = array( 2 => array(KAF_NEWWIN, 'addrbook', KID_SELECT, 0, 
				       600, 600),
			    3 => array(KAF_NEWWIN, 'addrbook', KID_EDIT, 0, 
				       600, 600));
	  $form->setActions("rowsMembers", $actions);
	}
      $form->setTitle(tMembers);
      $form->display();
      exit; 
    }
  // }}}
  
  // {{{ _displayFormMember()
  /**
   * Display the page for the member
   *
   * @access private
   * @param array $memberId  id of the member
   * @return void
   */
  function _displayFormMember($memberId)
    {
      $ut = $this->_ut;
      $data = $this->_dt;
      
      $form = $ut->newForm_V("addrbook", KID_SELECT); 
      $form->setTitle("");
      
      // Get the data of the event  
      $infos = $data->getMember($memberId);
      if (isset($infos['errMsg']))
	$form->addWng($infos['errMsg']);
      
      $form->addInfo("sexe",       $infos['mber_sexe']);
      $form->addInfo("firstname",  $infos['mber_firstname']);
      $form->addInfo("secondname", $infos['mber_secondname']);
      $form->addInfo("born",       $infos['mber_born']);
      $form->addInfo("ibfnumber",  $infos['mber_ibfnumber']);
      $form->addInfo("licence",    $infos['mber_licence']);
      $form->addInfo("comment",    $infos['mber_cmt']);
      $elts = array("sexe", "firstname", "secondname", "born", "ibfnumber",
		    "licence", "comment");
      $form->addBloc("blkInfo", $elts);
      
      $form->addBtn("Cancel");
      
      //Display the form
      $form->display(true);
      
      exit;
    }
  // }}}
}


?>