<?php
/*****************************************************************************
!   Module     : main
!   File       : $Source: /cvsroot/aotb/badnet/src/main/base_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.6 $
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
require_once "utils/utbase.php";

/**
* Acces to the dababase for users
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class mainBase_A extends utbase
{
  
  // {{{ properties
  // }}}

  // {{{ getUser
  /**
   * Return the column of a user 
   *
   * @access public
   * @param  string  $userId   id of the user
   * @return array   information of the user if any
   */
  function getUser($userId)
    {
      $fields = array('user_id', 'user_name', 'user_login', 'user_pass',
		      'user_email', 'user_type', 'user_lang', 'user_cre',
		      'user_lastvisit', 'user_cmt', 'user_nbcnx', 
		      'user_updt', 'user_pseudo');
      $tables[] = 'users';
      $where = "user_id=$userId";
      $res = $this->_select($tables, $fields, $where);
      $infos = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $ut = new utils();
      $infos['user_skin'] = $ut->getPref('skin', 'base');
      return $infos;
    }
  // }}}



  // {{{ getBooks
  /**
   * Return the list of the address books
   *
   * @access public
   * @param  string  $sort   criteria to sort users
   * @return array   array of users
   */
  function getBooks($sort=1)
    {
      $sort++;
      $fields = array('adbk_id', 'adbk_name', 'adbk_del', 'adbk_pbl');
      $tables[] = 'addressbook';
      $order = "adbk_name";
      $res = $this->_select($tables, $fields, false, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgNoBooks';
	  return $infos;
	}
      require_once "utils/utimg.php";
      $uti = new utimg();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $entry[4] = $uti->getPubliIcon($entry['adbk_del'], 
					 $entry['adbk_pbl']);
	  $rows[] = $entry;
        }
      return $rows;
    }
  // }}}

  // {{{ getParamInfo
  /**
   * Retrieve informations for email
   *
   * @access public
   * @return array   array of associations
   */
  function getParamInfos()
    {
      $ut= new utils();

      $param= array('host' => $ut->getParam('smtp_server'),
		    'port' => $ut->getParam('smtp_port', 25),
		    'skin' => $ut->getParam('default_skin', 'base'),
		    'emailType' => $ut->getParam('email_type', 'smtp'),
		    'username' => $ut->getParam('smtp_user'),
		    'auth' => $ut->getParam('smtp_auth'),
		    'password' => $ut->getParam('smtp_password'),
		    'ffbaEmail' => $ut->getParam('ffba_email'),
		    'ffbaUrl' => $ut->getParam('ffba_url'),
		    'ibfUrl' => $ut->getParam('ibf_url'),
		    'mainTitle' => $ut->getParam('mainTitle'),
		    'subTitle' => $ut->getParam('subTitle'),
		    'language' => 'fr',
		    'tablePrefixe' => $ut->getParam('tablePrefixe'),
		    'baseVersion' => $ut->getParam('version'),
		    'softVersion' => $ut->getParam('softVersion'),
      		'baseId' => $ut->getParam('databaseId'),
 		    'footer' => $ut->getParam('logofooter'),
 		    'sport' => $ut->getParam('issquash'),
      		'livePort' => $ut->getParam('livePort', 8888),
		    'liveIp' => $ut->getParam('liveIp', "127.0.0.1"),
		    'liveIp2' => $ut->getParam('liveIp2', "127.0.0.1"),
		    'synchroUser' => $ut->getParam('synchroUser'),
		    'synchroPwd'  => $ut->getParam('synchroPwd'),
		    'synchroUrl'  => $ut->getParam('synchroUrl'),
		    'synchroEvent' => $ut->getParam('synchroEvent'),
		    );
	  if($param['sport'])$param['sport'] = 'Squash';
	  else $param['sport'] = 'Badminton';
      return $param;
      
    }
  // }}}

  // {{{ setParamInfo
  /**
   * Set the informations for email in the database
   *
   * @access public
   * @return array   array of associations
   */
  function setParamInfos($infos)
    {
      $ut= new utils();

      $ut->setParam('default_skin', $infos['skin']);
      $ut->setParam('email_type', $infos['emailType']);
      $ut->setParam('smtp_server', $infos['host']);
      $ut->setParam('smtp_port', $infos['port']);
      $ut->setParam('smtp_user', $infos['username']);
      $ut->setParam('smtp_auth', $infos['auth']);
      $ut->setParam('smtp_password', $infos['password']);
      $ut->setParam('ffba_email', $infos['ffbaEmail']);
      $ut->setParam('ffba_url', $infos['ffbaUrl']);
      $ut->setParam('ibf_url', $infos['ibfUrl']);
      $ut->setParam('mainTitle', $infos['mainTitle']);
      $ut->setParam('subTitle', $infos['subTitle']);
      $ut->setParam('tablePrefixe', $infos['tablePrefixe']);
      $ut->setParam('version', $infos['baseVersion']);
      $ut->setParam('softVersion', $infos['softVersion']);
      $ut->setParam('databaseId', $infos['baseId']);
      $ut->setParam('logofooter', $infos['footer']);
      if ($infos['sport'] == 'Squash') $ut->setParam('issquash', 1);
      else $ut->setParam('issquash', 0);
      $ut->setParam('liveIp', $infos['liveIp']);
      $ut->setParam('liveIp2', $infos['liveIp2']);
      $ut->setParam('livePort', $infos['livePort']);
      $ut->setParam('synchroUser', $infos['synchroUser']);
      $ut->setParam('synchroPwd',  $infos['synchroPwd']);
      $ut->setParam('synchroUrl',  $infos['synchroUrl']);
      $ut->setParam('synchroEvent', $infos['synchroEvent']);
      return true;      
    }
  // }}}


}
?>