<?php
/*****************************************************************************
!   Module     : Members
!   File       : $Source: /cvsroot/aotb/badnet/src/mber/mber_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.6 $
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

require_once "utils/utpage_A.php";
require_once "base_A.php";
require_once "mber.inc";
require_once "regi/regi.inc";
require_once "asso/asso.inc";
require_once "purchase/purchase.inc";
require_once "utils/utfile.php";

/**
* Module de gestion du carnet d'adresse : classe visiteurs
*
* @author Gerard CANTEGRIL
* @see to follow
*
*/

class mber_A
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
  function mber_A()
    {
      $this->_ut = new utils();
      $this->_dt = new memberBase_A();
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
  function start($page  )
    {
      switch ($page)
        {
	  // Display main windows for member
	case KID_NEW:
	  $this->_displayFormMember();
	  break;
	case KID_EDIT:
	  $id = kform::getData();
	  $this->_displayFormMember($id);
	  break;
	case MBER_EDIT_NOREFRESH:
	  $this->_displayFormMember($id, false);
	  break;
	case KID_UPDATE:
	  $this->_updateMember(true);
	  break;
	case MBER_UPDATE_NOREFRESH:
	  $this->_updateMember(false);
	  break;
	  /*
	case MBER_ADM_ALL:
	  $mode['title'] = 'tMembers';
	  $mode['typeMin'] = WBS_PLAYER;
	  $mode['typeMax'] = WBS_EXHIBITOR;
	  $mode['act'] = KID_NEW;
	  $mode['select'] ='itAllMembers';
	  $this->_displayAdmListMember($mode);
	  break;
	case WBS_ACT_MBER:
	  $mode['title'] = 'tPlayers';
	  $mode['typeMin'] = WBS_PLAYER;
	  $mode['typeMax'] = WBS_PLAYER;
	  $mode['act'] = ???;
	  $mode['select'] ='itPlayers';
	  $this->_displayAdmListMember($mode);
	  break;
	case MBER_ADM_OFFICIALS:
	  $mode['title'] = 'tOfficials';
	  $mode['typeMin'] = WBS_REFEREE;
	  $mode['typeMax'] = WBS_LINEJUDGE;
	  $mode['act'] = REGI_NEW_OFFICIAL;
	  $mode['select'] ='itOfficials';
	  $this->_displayAdmListMember($mode);
	  break;
	case MBER_ADM_OTHERS:
	  $mode['title'] = 'tOthers';
	  $mode['typeMin'] = WBS_COACH;
	  $mode['typeMax'] = WBS_EXHIBITOR;
	  $mode['act'] = REGI_NEW_OTHER;
	  $mode['select'] ='itOthers';
	  $this->_displayAdmListMember($mode);
	  break;
	  */
	case MBER_SELECT_PHOTO:
	  $hide['mberId'] = kform::getData();
	  utimg::selectMberPhoto('mber', MBER_UPDATE_PHOTO, $hide);
	  break;

	case MBER_UPDATE_PHOTO:
	  $this->_savePhoto(); 
	  break;
	  
	default:
	  echo "page $page demand�e depuis mber_A<br>";
	  exit();
	}
    }
  // }}}


  // {{{ _savePhoto()
  /**
   * Save the photo of a member
   *
   * @access private
   * @return void
   */
  function _savePhoto()
    {
      $dt = $this->_dt;
      
      // Control the team id
      $infos['mber_id'] = kform::getInput('mberId', -1);
      if ($infos['mber_id'] == -1)
	{
	  $page = new kPage('none');
	  $page->close();	  
	  exit;
	}

      // Verify if the image is local
      $infos['mber_urlphoto'] = '';
      $fileObj = kform::getInput('image', NULL);
      $fileName = $fileObj['name'];
      if ($fileName == NULL)
	$fileName = kform::getData();
      if ($fileName != NULL)
	{
	  // No local image, so try to dowload it
	  if (utimg::getPathPhoto($fileName) == false)
	    {	  
	      $up = new  utFile();
	      $res = $up->upload("image", utimg::getPathPhoto());
	      if (isset($res['errMsg']))
		{
		  echo $res['errMsg'];
		  $hide['mberId'] = kform::getInput('mberId', -1);
		  utimg::selectMberPhoto('mber', MBER_UPDATE_PHOTO, $hide);
		}
	      else
		$fileName = $res['file'];
	    }
	  if (utimg::getPathPhoto($fileName)!= false)
	    $infos['mber_urlphoto'] = $fileName;
	}
      // Update the event
      $res = $dt->updateMember($infos);
      if (is_array($res))
        {
          $infos['errMsg'] = $res['errMsg'];
        }
      $page = new kPage('none');
      $page->close();
      exit;
    }
  // }}}

  // {{{ _displayFormMember()
  /**
   * Display the page for creating or updating a member
   *
   * @access private
   * @param array $member  info of the member
   * @param boolean  $refresh  does the previous window nedd to be refresh
   * @return void
   */
  function _displayFormMember($member="", $refresh=true)
    {
      $ut = $this->_ut;
      $data = $this->_dt;
      
      $utpage = new utPage('mber');
      $content =& $utpage->getPage();
      if ($refresh)
	$form =& $content->addForm('fMber', 'mber', KID_UPDATE);
      else
	$form =& $content->addForm('fMber', 'mber', MBER_UPDATE_NOREFRESH);
      
      // Initialize the field
      $infos = array('mber_id'         => -1,
                     'mber_sexe'       => WBS_MALE,
                     'mber_secondname' => "",
                     'mber_firstname'  => "",
                     'mber_born'       => "",
                     'mber_ibfnumber'  => "",
                     'mber_licence'    => "",
                     'mber_cmt'        => ""
                     );
      if ( is_array($member))
	$infos = $member;
      else
	{
	  if ($member != "") $infos = $data->getMember($member);
	}

      if ($infos['mber_id'] != -1)
	$form->setTitle('tEditMember');
      else
	$form->setTitle('tNewMember');
      
      // Display the error if any 
      if (isset($infos['errMsg']))
	$form->addWng($infos['errMsg']);
      
      
      // Initialize the field
      $form->addHide('mberId', $infos['mber_id']);
      $form->addRadio('mberSexe', $infos['mber_sexe']==WBS_MALE, WBS_MALE);
      $form->addRadio('mberSexe', $infos['mber_sexe']==WBS_FEMALE, WBS_FEMALE);
      
      $kedit =& $form->addEdit('mberSecondName', 
			       $infos['mber_secondname'],30);
      $kedit->setMaxLength(30);
      $kedit =& $form->addEdit('mberFirstName',     
			       $infos['mber_firstname'],30);
      $kedit->setMaxLength(30);
      $kedit =& $form->addEdit('mberBorn', $infos['mber_born']);
      $kedit->setMaxLength(25);
      $kedit->noMandatory();
      $kedit =& $form->addEdit('mberIbfNumber', $infos['mber_ibfnumber']);
      $kedit->setMaxLength(10);
      $kedit->noMandatory();
      $kedit =& $form->addEdit('mberLicence', $infos['mber_licence']);
      $kedit->setMaxLength(8);
      $kedit->noMandatory();
      $kedit =& $form->addArea('mberCmt', $infos['mber_cmt'] , 20 );
      $kedit->noMandatory();
      $elts = array('mberSexe', 'mberSecondName', 'mberFirstName', 
                    'mberBorn', 'mberLicence', 'mberIbfNumber', 
                    'mberCmt');
      $form->addBlock('blkCivil', $elts);

      $form->addBtn('btnRegister', KAF_SUBMIT);
      $form->addBtn('btnCancel');
      $elts = array('btnRegister', 'btnCancel');
      $form->addBlock('blkBtn', $elts);

      //Display the page
      $utpage->display();
      
      exit;
    }
  // }}}

  // {{{ _updateMember()
  /**
   * Create the member in the database
   *
   * @access private
   * @param boolean  $refresh  does the previous window nedd to be refresh
   * @return void
   */
  function _updateMember($refresh)
    {
      $ut = $this->_ut;
      $data = $this->_dt;
      
      // Get the informations
      $infos = array('mber_sexe'       =>kform::getInput("mberSexe"),
                     'mber_firstname'  =>
		     ucwords(strtolower(kform::getInput("mberFirstName"))),
                     'mber_secondname' =>
		     strtoupper(kform::getInput("mberSecondName")),
                     'mber_born'       =>kform::getInput("mberBorn"),
                     'mber_ibfnumber'  =>kform::getInput("mberIbfNumber"),
                     'mber_licence'    =>kform::getInput("mberLicence"),
                     'mber_cmt'        =>kform::getInput("mberCmt"),
                     'mber_id'         =>kform::getInput("mberId"),
                     );
      
      // Control the informations
      if ($infos['mber_sexe'] != WBS_MALE &&
          $infos['mber_sexe'] != WBS_FEMALE)
	{
	  $infos['errMsg'] = 'msgmberSexe';
	  $this->_displayFormMember($infos);
	} 
      
      if ($infos['mber_firstname'] == "")
	{
	  $infos['errMsg'] = 'msgmberFirstName';
	  $this->_displayFormMember($infos);
	} 
      
      if ($infos['mber_secondname'] == "")
	{
	  $infos['errMsg'] = 'msgmberSecondName';
	  $this->_displayFormMember($infos);
	} 
      
      // Add the member
      $res = $data->updateMember($infos);
      if (is_array($res))
	$this->_displayFormMember($res);

      $page = new kPage('none');
      $page->close();
      exit;
    }
  // }}}

}
?>