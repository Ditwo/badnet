<?php
/*****************************************************************************
!   Module     : Main
!   File       : $Source: /cvsroot/aotb/badnet/src/main/main_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.11 $
!   Author     : G.CANTEGRIL
!   Mailto     : cage@free.fr
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
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
require_once "base_A.php";
require_once "main.inc";
require_once "users/users.inc";
require_once "users/cnx.inc";
require_once "utils/utimg.php";
require_once "utils/utpage_A.php";

/**
* Module de gestion des tournois
*
* @author Gerard CANTEGRIL <cage@aotb.org>
* @see to follow
*
*/

class Main_A
{
  
  // {{{ properties
  
  // }}}
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @private
   */
  function Main_A()
    {
      $this->_dt = new mainBase_A();

    }
  // }}}
  
  // {{{ start()
  /**
   * Start the connexion processus
   *
   * @public
   * @return void
   */
  function start($action)
    {
      $ut = new Utils();

      switch ($action)
        {
	case MAIN_UPDATE_PARAM:
	  $this->_updateParam();
	  break;
	case WBS_ACT_PARAMETERS:
	  $this->_displayFormParam();
	  break;
	case WBS_ACT_PREFERENCES:
	  $this->_displayFormMain();
	  exit;
	case KID_DEFAULT:
	  $page = $ut->getPref('page','events');
	  $act = $ut->getPref('action', WBS_ACT_EVENTS);
	  $form = $page.'_A';
	  //$file = CUR_DIR."/$page/$form".'.php';
	  $file = "$page/$form".'.php';
	  require_once "$file";
	  //echo "form=$form";
	  $a = new $form();
	  $a->start($act);
          break;
	case KID_EDIT:
	  $this->_displayFormUpdateParam();
          break;
	default :
	  echo "non autorise";
	  break;
	}
      exit; 
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
      $dt = new mainBase_A();
      
      $utPage = new utPage_A('main', true, 'itPref');
      $content =& $utPage->getContentDiv();
      utvars::setSessionVar('theme', WBS_THEME_NONE);

      $userId = utvars::getUserId();
      $infos  = $dt->getUser($userId);
      if (isset($infos['errMsg']))
	$content->addWng($infos['errMsg']);

      // Display  user's informations but not for 'demo' user
      $kdiv1 = &$content->addDiv('blkUser', 'block');
      if ($infos['user_login'] != 'demo')
	{
	  $kdiv = & $kdiv1->addDiv('info', 'blkInfo');
	  $kdiv->addInfo('userName',      $infos['user_name']);
	  $kdiv->addInfo('userPseudo',    $infos['user_pseudo']);
	  $kdiv->addInfo('userLogin',     $infos['user_login']);
	  $kdiv->addInfo('userEmail',     $infos['user_email']);
	  $kdiv->addInfo('userLang',      $infos['user_lang']);
	  $kdiv->addInfo('userSkin',      $infos['user_skin']);
	  $kdiv->addInfo('userLastvisit', $infos['user_lastvisit']);
	  $kdiv->addInfo('userNbcnx',     $infos['user_nbcnx']); 
	  
	  // add bouton to modify user information
	  $kdiv2 = &$kdiv->addDiv('btn', 'blkBtn');
	  $kdiv2->addbtn('btnUser', KAF_NEWWIN, 'users', KID_EDIT,
			$userId, 500, 500);
	  $kdiv2->addbtn('btnPwd', KAF_NEWWIN, 'users', USERS_CHANGEPWD,
			$userId, 350, 160);
	}
      
      $content->addDiv('force', 'blkNewPage');

      // Les abonnements

      // Les messages envoyes (interne,externe)
      // Les messages recus (interne)

      $utPage->display();
      exit; 
    }
  // }}}



  // {{{ _displayFormParam()
  /**
   * Display a form to modify the parameters
   *
   * @access public
   * @param integer $userId  Id of the user to modify.
   * @return void
   */
  function _displayFormParam()
    {
      $ut = new Utils();
      $dt = new mainBase_A();
      

      $utPage = new utPage_A('main', true, 'itAdmi');
      $kdiv =& $utPage->getContentDiv();
      utvars::setSessionVar('theme', WBS_THEME_NONE);

      //$kdiv = & $content->addDiv('divInfo', 'blkInfo');

      // Display informations for parameters congiguration
      $infos = $dt->getParamInfos();

      $kdiv2 = & $kdiv->addDiv('divContact', 'blkInfo');
      $kdiv2->addMsg('contactTitle', '', 'kTitre');
      $kdiv2->addInfo('ffbaEmail', $infos['ffbaEmail']);
      $kdiv2->addInfo('ffbaUrl',   $infos['ffbaUrl']);
      $kdiv2->addInfo('ibfUrl',   $infos['ibfUrl']);

      $kdiv2 = & $kdiv->addDiv('divEmail', 'blkInfo');
      $kdiv2->addMsg('emailTitle', '', 'kTitre');
      $kdiv2->addInfo('emailType', $infos['emailType']);
      if ( $infos['emailType'] === 'smtp')
	{
	  $kdiv2->addInfo('host', $infos['host']);
	  $kdiv2->addInfo('port', $infos['port']);
	  $kdiv2->addInfo('userLog', $infos['username']);
	  //$kdiv2->addPwd('password', $infos['password']);
	}

      // Parametre de decoration
      $kdiv2 = & $kdiv->addDiv('divDeco', 'blkInfo');
      $kdiv2->addMsg('decoTitle', '', 'kTitre');
      $kdiv2->addInfo('mainTitle',  $infos['mainTitle']);
      $kdiv2->addInfo('subTitle',   $infos['subTitle']);
      $kdiv2->addInfo('skin',       $infos['skin']);
      $kdiv2->addInfo('language',   $infos['language']);
      $kdiv2->addInfo('footer',     $infos['footer']);
      
      // Parametre de configuration
      $kdiv2 = & $kdiv->addDiv('divDataBase', 'blkInfo');
      $kdiv2->addMsg('databaseTitle', '', 'kTitre');
      $kdiv2->addInfo('prefixe',  $infos['tablePrefixe']);
      $kdiv2->addInfo('version',  $infos['baseVersion']);
      $kdiv2->addInfo('softVersion',  $infos['softVersion']);
      $kdiv2->addInfo('baseid',   $infos['baseId']);
      $kdiv2->addInfo('sport',    $infos['sport']);
      
      // Parametre de configuration de la synchronisation 
      $kdiv2 = & $kdiv->addDiv('divSynchro', 'blkInfo');
      $kdiv2->addMsg('synchroTitle', '', 'kTitre');
      $kdiv2->addInfo('synchroUrl',   $infos['synchroUrl']);
      $kdiv2->addInfo('synchroUser',  $infos['synchroUser']);
      $kdiv2->addInfo('synchroPwd',   $infos['synchroPwd']);
      $kdiv2->addInfo('synchroEvent', $infos['synchroEvent']);

      // Parametre de configuration du live
      $kdiv2 = & $kdiv->addDiv('divLive', 'blkInfo');
      $kdiv2->addMsg('liveTitle', '', 'kTitre');
      $kdiv2->addInfo('liveIp',   $infos['liveIp']);
      $kdiv2->addInfo('liveIp2',  $infos['liveIp2']);
      $kdiv2->addInfo('livePort', $infos['livePort']);

      // add bouton to modify user information
      $items = array();
      $items['btnUser'] = array(KAF_NEWWIN, 'main', KID_EDIT,
				0, 500, 500);  
      $kdiv->addMenu('menu', $items, -1, 'classMenuBtn');

      $kdiv->addDiv('force', 'blkNewPage');
      //Display the form
      $utPage->display();
      exit; 
    }
  // }}}

  // {{{ _displayFormUpdateParam()
  /**
   * Display a form to modify the parameters
   *
   * @access public
   * @param integer $userId  Id of the user to modify.
   * @return void
   */
  function _displayFormUpdateParam()
    {
      $ut = new Utils();
      $dt = new mainBase_A();
      
      $utPage = new utPage('main');
      $content =& $utPage->getPage();
      $kform =& $content->addForm('fMain', 'main', MAIN_UPDATE_PARAM);
      $kform->setTitle('tEditParam');

      // Display informations for parameters congiguration
      $infos = $dt->getParamInfos();
      $infos['emailType'] = kform::getInput('emailType', $infos['emailType']);
      $kradio =& $kform->addRadio('emailType', $infos['emailType']==='smtp', 'smtp');
      $acts[1] = array(KAF_UPLOAD, 'main', KID_EDIT);
      $kradio->setActions($acts);

      $kradio =& $kform->addRadio('emailType', $infos['emailType']==='mail', 'mail');
      $kradio->setActions($acts);
      $elts= array('emailType1', 'emailType2');
      $kform->addBlock('divTypeEmail', $elts);

      if ( $infos['emailType'] === 'smtp')
	{
	  $kedit =& $kform->addEdit('host', $infos['host']);
	  $kedit->noMandatory();
	  $kedit =& $kform->addEdit('port', $infos['port']);
	  $kedit->noMandatory();
	  $kedit =& $kform->addEdit('userLog', $infos['username']);
	  $kedit->noMandatory();
	  $kedit =& $kform->addPwd('password', $infos['password']);
	  $kedit->noMandatory();
	  $kedit =& $kform->addCheck('auth', $infos['auth']);
	}
      $elts= array('divTypeEmail', 'host', 'port', 'userLog', 'password', 'auth' );
      $kform->addBlock('divEmail', $elts, 'blkInfo');

      $kedit =& $kform->addEdit('ffbaEmail', $infos['ffbaEmail']);
      $kedit->noMandatory();
      $kedit =& $kform->addEdit('ffbaUrl',  $infos['ffbaUrl']);
      $kedit->noMandatory();
      $kedit =& $kform->addEdit('ibfUrl',   $infos['ibfUrl']);
      $kedit->noMandatory();
      $elts= array('ffbaEmail', 'ffbaUrl', 'ibfUrl');
      $kform->addBlock('divContact', $elts, 'blkInfo');

      $kedit =& $kform->addEdit('mainTitle', $infos['mainTitle']);
      $kedit =& $kform->addEdit('subTitle',  $infos['subTitle']);

      $handle=opendir('../skins');
      while ($file = readdir($handle))
        {
          if ($file != "." && $file != ".." && $file != "CVS")
	    $skins[$file] = $file;
        }
      closedir($handle);
      $skins["---"] = "---";
      $kform->addCombo('skin', $skins, $infos['skin'] );
      $kedit =& $kform->addEdit('footer',  $infos['footer']);
      $kedit->noMandatory();
      
      $elts= array('mainTitle', 'subTitle', 'skin', 'footer');
      $kform->addBlock('divDeco', $elts, 'blkInfo');

      $kedit =& $kform->addEdit('prefixe', $infos['tablePrefixe']);
      $kedit->noMandatory();
      $kedit =& $kform->addEdit('version',  $infos['baseVersion']);
      $kedit->noMandatory();
      $kedit =& $kform->addEdit('softVersion',  $infos['softVersion']);
      $kedit->noMandatory();
      $kedit =& $kform->addEdit('baseid',  $infos['baseId']);
      $kedit->noMandatory();
      $kradio =& $kform->addRadio('sport', $infos['sport']=='Squash', 'Squash');
      $kradio =& $kform->addRadio('sport', $infos['sport']=='Badminton', 'Badminton');
      $elts= array('sport1', 'sport2');
      $kform->addBlock('divSport', $elts);
      $elts= array('prefixe', 'version', 'softVersion', 'baseid', 'divSport');
      $kform->addBlock('divDatabase', $elts, 'blkInfo');

      $kedit =& $kform->addEdit('synchroUrl',  $infos['synchroUrl']);
      $kedit->noMandatory();
      $kedit =& $kform->addEdit('synchroUser',  $infos['synchroUser']);
      $kedit->noMandatory();
      $kedit =& $kform->addEdit('synchroPwd',  $infos['synchroPwd']);
      $kedit->noMandatory();
      $kedit =& $kform->addEdit('synchroEvent',  $infos['synchroEvent']);
      $kedit->noMandatory();
      $elts= array('synchroUrl', 'synchroUser', 'synchroPwd', 'synchroEvent');
      $kform->addBlock('divSynchro', $elts, 'blkInfo');

      $kedit =& $kform->addEdit('liveIp', $infos['liveIp']);
      $kedit->noMandatory();
      $kedit =& $kform->addEdit('liveIp2', $infos['liveIp2']);
      $kedit->noMandatory();
      $kedit =& $kform->addEdit('livePort',  $infos['livePort']);
      $kedit->noMandatory();
      $elts= array('liveIp', 'liveIp2', 'livePort');
      $kform->addBlock('divLive', $elts, 'blkInfo');


      $kform->addDiv('force', 'blkNewPage');

      $kform->addBtn('btnRegister', KAF_SUBMIT);
      $kform->addBtn('btnCancel');
      $elts = array('btnRegister', 'btnCancel');
      $kform->addBlock('blkBtn', $elts, 'blkBtn');

      //Display the form
      $utPage->display();
      exit; 
    }
  // }}}

  // {{{ _updateParam()
  /**
   * Update mail information in database
   *
   * @access public
   * @return void
   */
  function _updateParam()
    {
      $dt = $this->_dt;
      
      // Get the informations
      $infos = array('emailType' =>kform::getInput('emailType'),
		     'skin'      =>kform::getInput('skin'),
		     'host'      =>kform::getInput('host'),
                     'port'      =>kform::getInput('port'),
                     'auth'      =>kform::getInput('auth'),
                     'username'  =>kform::getInput('userLog'),
                     'password'  =>kform::getInput('password'),
                     'ffbaEmail' =>kform::getInput('ffbaEmail'),
                     'ffbaUrl'   =>kform::getInput('ffbaUrl'),
                     'ibfUrl'    =>kform::getInput('ibfUrl'),
                     'mainTitle' =>kform::getInput('mainTitle'),
                     'subTitle'  =>kform::getInput('subTitle'),
                     'footer'    =>kform::getInput('footer'),
      				 'baseVersion'  =>kform::getInput('version'),
      				 'softVersion'  =>kform::getInput('softVersion'),
      				 'baseId'       =>kform::getInput('baseid'),
                     'sport'       =>kform::getInput('sport'),
      				 'tablePrefixe' =>kform::getInput('prefixe'),
                     'liveIp'      =>kform::getInput('liveIp'),
                     'liveIp2'     =>kform::getInput('liveIp2'),
      				 'livePort'    =>kform::getInput('livePort', 8888),
                     'synchroUrl'  =>kform::getInput('synchroUrl'),
                     'synchroUser' =>kform::getInput('synchroUser'),
                     'synchroPwd'  =>kform::getInput('synchroPwd'),
                     'synchroEvent'=>kform::getInput('synchroEvent')
		     );
      $res = $dt->setParamInfos($infos);
      if (is_array($res)) $this->_displayFormParam($res);
      else
	{
	  // All is OK. Close the window
	  $page = new kPage('none');
	  $page->close();
	  exit;
	}
    }
  // }}}


}

?>