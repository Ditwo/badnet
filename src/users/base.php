<?php
/*****************************************************************************
!   Module     : users
!   File       : $Source: /cvsroot/aotb/badnet/src/users/base.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.9 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2006/07/18 21:57:40 $
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
/**
* Acces to the dababase for users
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class userBase extends utbase
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
      $fields = array('user_id', 'user_name', 'user_login',
		      'user_email', 'user_type', 'user_lang', 'user_cre',
		      'user_lastvisit', 'user_cmt', 'user_nbcnx', 
		      'user_updt', 'user_pseudo');
      $tables[] = 'users';
      $where =  "user_id=$userId";
      $res = $this->_select($tables, $fields, $where);
      
      if ($res->numRows())
	{
	  $infos = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $ut = new utils();
	  $infos['user_skin'] = $ut->getPref('skin', 'base');
	  
	  $where = "rght_userId={$infos['user_id']}".
	    " AND rght_theme =". WBS_THEME_ASSOS;
	  $infos['user_right'] = $this->_selectFirst('rights', 'rght_themeId', 
						    $where);
	  return $infos;
	}
      else
	return false;
    }
  // }}}
  
  // {{{ setPwd
  /**
   * Update the password of the user
   *
   * @access public
   * @param  array  $infos   column of the user
   * @return boolean 
   */
  function setPwd($infos)
    {
      $fields['user_pass'] = md5($infos['user_pass']);
      $where = "user_id=".$infos['user_id'];
      $res = $this->_update('users', $fields, $where);
      return true;
      
    }
  // }}}
  
  
  // {{{ ctlPwd
  /**
   * Verify the pasword of the user
   *
   * @access public
   * @param  array  $id   Id of the user
   * @param  strin  $pwd  password to control
   * @return boolean 
   */
  function ctlPwd($id, $pwd)
    {
      $mdpass = md5($pwd);
      
      $fields[] = 'user_pass';
      $tables[] = 'users';
      $where = "user_id=$id";
      $res = $this->_select($tables, $fields, $where);
      $data = $res->fetchRow(DB_FETCHMODE_ASSOC);
      return ($data['user_pass'] == $mdpass);
    }
  // }}}

  // {{{ checkUserRight
  /**
   * Verify the pasword and right of the user
   *
   * @access public
   * @param  array  $id   Id of the user
   * @param  strin  $pwd  password to control
   * @return boolean 
   */
  function checkUserRight($login, $pwd)
    {
      $mdpass = md5($pwd);
      
      $fields[] = 'user_pass';
      $tables[] = 'users';
      $where = "user_login='$login'";
      $res = $this->_select($tables, $fields, $where);
      $data = $res->fetchRow(DB_FETCHMODE_ASSOC);
      return ($data['user_pass'] == $mdpass);      
    }
  // }}}

  
  // {{{ existLogin
  /**
   * Test the presence of the login in the database
   *
   * @access public
   * @param  string  $users   login of the user to search
   * @param  number  $id      id of the user
   * @return boolean 
   */
  function existLogin($login, $id)
    {
      $fields[] = '*';
      $tables[] = 'users';
      $where = "user_login='$login'".
        " AND user_id != $id";
      $res = $this->_select($tables, $fields, $where);
      return $res->numRows();
    }
  // }}}

  // {{{ connectUser
  /**
   * Update the last connexion date and number of connexion of the user
   * !!!!!!  Call by cnx.php  Must be in basecnx instead here !!!!!
   *
   * @access public
   * @param  string  $username login of the user
   * @return boolean 
   */
  function connectUser($login)
    {
      $fields = array('user_id', 'user_nbcnx', 'user_lang', 'user_type', 'user_name', 'user_email');
      $tables[] = 'users';
      $where = "user_login='$login'";
      $res = $this->_select($tables, $fields, $where);

      $user = $res->fetchRow(DB_FETCHMODE_ASSOC);
      if ($login != 'demo')
	{
	  utvars::setLanguage($user['user_lang']);
	  //setlocale(LC_TIME, "fra");
	}
      utvars::setSessionVar('userId', $user['user_id']);
      utvars::setSessionVar('userAuth', $user['user_type']);

	// Pour que la connexion soit valide avec la nouvelle version
	$_SESSION['bn']['loginId'] = $user['user_id']; 
	$_SESSION['bn']['loginAuth'] = $user['user_type']; 
	$_SESSION['bn']['loginName'] = $user['user_name']; 
	$_SESSION['bn']['loginEmail'] = $user['user_email']; 
	$_SESSION['bn']['theme'] = 'badnet'; 
	$_SESSION['bn']['locale'] = 'Fr'; 
	$_SESSION['bn']['pwd'] = ''; 
	
      $fields = array();
      $fields['user_lastvisit'] = date(DATE_FMT);
      $fields['user_nbcnx']     = $user['user_nbcnx']+1;
      $where = "user_login = '$login'";
      $res = $this->_update('users', $fields, $where);
      return;
      
    }
  // }}}
  
  // {{{ disconnectUser
  /**
   * Update the last connexion date and number of connexion of the user
   * !!!!!!  Call by cnx.php  Must be in basecnx instead here !!!!!
   *
   * @access public
   * @param  string  $username login of the user
   * @return boolean 
   */
  function disconnectUser($login)
    {
      return;      
    }
  // }}}
  
  // {{{ updateUser
  /**
   * Update the database with the information of a user 
   * or create a new user
   *
   * @access public
   * @param  string  $info   column of the user
   * @return boolean 
   */
  function updateUser($infos, $assoId = -1)
    {
      $ut = new utils();
      if (isset($infos['user_skin']))
	{
	  $ut->setPref('skin', $infos['user_skin']);
	  unset($infos['user_skin']);
	}
      if ($infos['user_id'] != -1)
        {
          $where = "user_id=".$infos['user_id'];
	  $res = $this->_update('users', $infos, $where);
	  if (isset($infos['user_lang']))
	    utvars::setLanguage($infos['user_lang']);
        }
        else
        {
          unset($infos['user_id']);
          $infos['user_pass'] = md5($infos['user_pass']);
          $infos['user_lastvisit'] = date(DATE_FMT);
          $infos['user_nbcnx'] = 0;
	  $res = $this->_insert('users', $infos);
	  $infos['user_id'] = $res;
       }
      
      // Enregistrement du privilege sur l'equipe
      if ($assoId !== -1)
	{
          $data['rght_userId'] = $infos['user_id'];
          $data['rght_themeId'] = $assoId;
          $data['rght_theme']  = WBS_THEME_ASSOS;
          $data['rght_status'] = WBS_AUTH_FRIEND;
	  print_r($data);
	  $where = "rght_userId = {$infos['user_id']}".
	    ' AND rght_theme  ='. WBS_THEME_ASSOS;
	  $res = $this->_selectFirst('rights', 'rght_id', $where);
	  if (is_null($res))
	    $this->_insert('rights', $data);
	  else
	    $this->_update('rights', $data, $where);
	}

      return true;
      
    }
  // }}}

  // {{{ getNews
  /**
   * Return the column of the news of an event
   *
   * @access public
   * @param  string  $eventId   id of the event
   * @return array   information of the user if any
   */
  function getNews()
    {
      $fields = array('news_id', 'news_cre', 'news_text', 
		      'news_page', 'evnt_id');
      $tables = array('news', 'events');
      $where = "news_eventId = evnt_id".
	" AND evnt_pbl =". WBS_DATA_PUBLIC;
	" AND DATE(news_cre) > CURDATE() - INTERVAL 3 MONTH";
      $order = "news_cre DESC";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows()) 
	{
	  $infos['errMsg'] = 'msgNoNews';
	  return;
	} 
      $i = 0;
      $utd = new utdate();
      $utev = new utEvent();
      $fields = array('news_text');
      while($new = $res->fetchRow(DB_FETCHMODE_ASSOC) and $i<5)
	{
	  $i++;
	  $utd->setIsoDate($new['news_cre']);
	  $new['news_cre'] = $utd->getDate();
	  $event = $utev->getEvent($new['evnt_id']);
	  $new['evnt_name'] = $event['evnt_name'];

	  $news[] = $this->_getTranslate('news', $fields, 
					 $new['news_id'], $new);
	}
      return $news;
    }
  // }}}

  // {{{ getNextTies
  /**
   * Return the list of the next ties
   *
   * @access public
   * @return array   array of events
   */
  function getNextTies()
    {
      $fields = array('DISTINCT evnt_id', 'evnt_name',
		      'tie_schedule', 'tie_place', 'tie_step');
      $tables = array('events', 'ties', 'rounds', 'draws');
      $where = "evnt_pbl =". WBS_DATA_PUBLIC.
	" AND evnt_del != ".WBS_DATA_DELETE.
	" AND evnt_id = draw_eventId".
	" AND draw_id = rund_drawId".
	" AND rund_id = tie_roundId";
	" AND DATE(tie_schedule) > CURDATE()+ 0";
      $order = 'tie_schedule';
      $res = $this->_select($tables, $fields, $where, $order);
      $rows = array();
      $eventIds = array();
      $utd = new utdate();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  if (! in_array($entry['evnt_id'], $eventIds))
	  {
	    $utd->setIsoDate($entry['tie_schedule']);
	    $entry['tie_schedule'] = $utd->getDate();
	    $eventIds[] = $entry['evnt_id'];
	    $rows[] = $entry;
	  }
	}
      return $rows;
    }
  // }}}

  // {{{ getTodayTies
  /**
   * Return the list of the next ties
   *
   * @access public
   * @return array   array of events
   */
  function getTodayTies()
    {
      $fields = array('DISTINCT evnt_id', 'evnt_name', 'evnt_poster',
		      'tie_schedule', 'tie_place', 'tie_step');
      $tables = array('events', 'ties', 'rounds', 'draws');
      $where = "evnt_pbl =". WBS_DATA_PUBLIC.
	" AND evnt_del != ".WBS_DATA_DELETE.
	" AND evnt_id = draw_eventId".
	" AND draw_id = rund_drawId".
	" AND rund_id = tie_roundId";
	" AND DATE(tie_schedule) = CURDATE()+0";
      $order = 'tie_schedule';
      $res = $this->_select($tables, $fields, $where, $order);
      $rows = array();
      $eventIds = array();
      $utd = new utdate();
      $nb=0;
      while (($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	     && $nb<5
	     )
	{
	  if (! in_array($entry['evnt_id'], $eventIds))
	  {
	    $nb++;
	    $utd->setIsoDate($entry['tie_schedule']);
	    $entry['tie_schedule'] = $utd->getDate();
	    $eventIds[] = $entry['evnt_id'];
	    $rows[] = $entry;
	  }
	}
      return $rows;
    }
  // }}}


  // {{{ getVisits
  /**
   * Return general informations about members
   *
   * @return array   array of informations
   *
   * @private
   */
  function getVisits()
    {
      $fields = array('user_nbcnx');
      $tables = array('users');
      $where = "user_login='demo'";
      $res = $this->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
 	   return $err;
        } 
      $entry = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $infos['nbAnonymous'] = $entry[0];

      $fields = array('sum(user_nbcnx)');
      $tables = array('users');
      $where  = "user_type='". WBS_AUTH_USER."'";
      $res = $this->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
 	   return $err;
        } 
      $entry = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $infos['nbUsers'] = $entry[0];

      $fields = array('sum(user_nbcnx)');
      $tables = array('users');
      $where  = "user_type='". WBS_AUTH_ADMIN."'";
      $res = $this->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
 	   return $err;
        } 
      $entry = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $infos['nbAdministrators'] = $entry[0];

      return $infos;
    }
   // }}}

  // {{{ getUsersStats
  /**
   * Return general informations about members
   *
   * @return array   array of informations
   *
   * @private
   */
  function getUsersStats()
    {
      $fields = array('COUNT(*)');
      $tables = array('users');
      $res = $this->_select($tables, $fields);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
 	   return $err;
        } 
      $entry = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $infos['nbUsers'] = $entry[0]-1;

      $fields = array('user_pseudo', 'user_cre');
      $tables = array('users');
      $order  = "user_cre DESC ";
      $res = $this->_select($tables, $fields, false, $order);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
 	   return $err;
        } 
      $entry = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $utd = new utDate();
      $utd->setIsoDate($entry[1]);
      $infos['lastUser'] = $entry[0] .' ('.$utd->getDate().')';

      $fields = array('subs_email');
      $tables = array('subscriptions');
      $where  = 'subs_userId > 0'.
	' GROUP BY subs_email';
      $res = $this->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
 	   return $err;
        } 
      $infos['nbSubscribe'] = $res->numRows();
      return $infos;
    }
  // }}}

  // {{{ getEventsStats
  /**
   * Return general informations about events
   *
   * @return array   array of informations
   *
   * @private
   */
  function getEventsStats()
    {
      $fields = array('COUNT(*)');
      $tables = array('events');
      $res = $this->_select($tables, $fields);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
 	   return $err;
        } 
      $entry = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $infos['nbEvents'] = $entry[0];

      $tables = array('teams');
      $where = "team_del !=".WBS_DATA_DELETE;
      $res = $this->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
 	   return $err;
        } 
      $entry = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $infos['nbTeams'] = $entry[0];

      $tables = array('registration');
      $where = "regi_del !=".WBS_DATA_DELETE.
	" AND regi_type =".WBS_PLAYER;
      $res = $this->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
 	   return $err;
        } 
      $entry = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $infos['nbPlayers'] = $entry[0];

      $tables = array('matchs');
      $where = "mtch_del !=".WBS_DATA_DELETE;
      $res = $this->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
 	   return $err;
        } 
      $entry = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $infos['nbMatches'] = $entry[0];

      return $infos;
    }
  // }}}

}
?>