<?php
/*****************************************************************************
!   Module     : events
!   File       : $Source: /cvsroot/aotb/badnet/src/events/base_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.10 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2006/10/27 11:04:56 $
******************************************************************************/
require_once "utils/utdate.php";

/**
* Acces to the dababase for events
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class EventBase_A extends utbase
{

  // {{{ properties
  // }}}

  // {{{ updatePostit
  /**
   * Return the column of the postit of an event
   *
   */
  function updatePostit($post)
    {
      $post['psit_eventId'] = utvars::getEventId();

      if ($post['psit_id'] == -1)
	{
	  unset($post['psit_id']);
	  $this->_insert('postits', $post);
	}
      else
	{	  
	  $where  = "psit_id = {$post['psit_id']}";
	  $this->_update('postits', $post, $where);
	}
    }
  // }}}

  // {{{ getPostit
  /**
   * Return the column of the postit of an event
   *
   * @access public
   * @param  string  $eventId   id of the event
   * @return array   information of the user if any
   */
  function getPostit($postitId)
    {
      $fields = array('psit_id', 'psit_texte', 'psit_title',
		      'psit_userId', 'psit_function');
      $tables = array('postits');
      $where  = "psit_id = $postitId";
      $res = $this->_select($tables, $fields, $where);     
      if (!$res->numRows()) 
	{
	  $post  =array('psit_id' => -1,
			'psit_texte' => '',
			'psit_title' => '',
			'psit_userId' => utvars::getUserId(),
			'psit_function' => KAF_NEWWIN,);
	} 
      else
	$post = $res->fetchRow(DB_FETCHMODE_ASSOC);
      return $post;
    }
  // }}}

  // {{{ deletePostit
  /**
   * Controle le nombre max de tableau par joueur
   *
   * @access public
   * @param  integer  $sort   criteria to sort users
   * @return array   array of events
   */
  function deletePostit($psitId)
    {
      $where = "psit_id=$psitId";
      $this->_delete('postits', $where);
      return;
    }
  // }}}

  // {{{ controlNbMaxDraw
  /**
   * Controle le nombre max de tableau par joueur
   *
   * @access public
   * @param  integer  $sort   criteria to sort users
   * @return array   array of events
   */
  function controlNbMaxDraw($eventId)
    {
      $ute = new utevent();
      $event = $ute->getEvent($eventId);
      $nbMax = $event['evnt_nbdrawmax'];

      $fields = array('regi_id', 'regi_longName', 'count(*) as nb');
      $tables = array('registration', 'i2p', 'pairs');
      $where = "regi_eventId = $eventId".
	" AND regi_id = i2p_regiId".
	" AND i2p_pairId = pair_id".
	" AND regi_type  = ".WBS_PLAYER.
	" AND pair_drawId != -1".
	" GROUP BY regi_id";
      $res = $this->_select($tables, $fields, $where);

      $cols['psit_title'] = "Trop de tableau";
      $cols['psit_page'] = "regi";
      $cols['psit_action'] = KID_EDIT;
      $cols['psit_type'] = WBS_POSTIT_TOODRAW;
      $cols['psit_eventId'] = $eventId;
      while ($regi = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  if ($regi['nb'] > $nbMax)
	    {
	      $cols['psit_texte'] = $regi['regi_longName'];
	      $cols['psit_data'] = $regi['regi_id'];
	      $this->_insert('postits', $cols);
	      //print_r($regi);
	    }
        }
      return;
    }
  // }}}
  
  // {{{ getBadges
  /**
   * Return the list of the bages
   *
   * @access public
   * @param  integer  $sort   criteria to sort users
   * @return array   array of events
   */
  function getBadges()
    {
      $fields = array('bdge_id', 'bdge_name');
      $tables[] = 'badges';
      $order = "bdge_name";
      $res = $this->_select($tables, $fields, false, $order);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgNoBadges';
	  return $infos;
	}

      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $rows[$entry['bdge_id']] = $entry['bdge_name'];
        }
      return $rows;
    }
  // }}}



  // {{{ delNews
  /**
   * Delete the news
   *
   * @access public
   * @param  table  $infos   Infos of the draw to delete
   * @return mixed
   */
  function delNews($newsId)
    {
      $listId = implode(',', $newsId);

      // Suppression des nouvelles
      $where = "news_id in (".$listId.')';
      $res = $this->_delete('news', $where);
      return '';  
    }
  // }}}


  // {{{ delRights
  /**
   * Delete the rights
   *
   * @access public
   * @param  table  $rightsId   Ids of the rightd to delete
   * @return mixed
   */
  function delRights($rightsId)
    {
      $listId = implode(',', $rightsId);

      // Suppression des droits
      $where = "rght_id in (". $listId.')';
      $res = $this->_delete('rights', $where);
      return '';  
    }
  // }}}

  // {{{ getEvents
  /**
   * Return the list of the events
   *
   * @access public
   * @param  integer  $sort   criteria to sort users
   * @return array   array of events
   */
  function getEvents($season)
    {
      $fields = array('evnt_id', 'evnt_firstday', 'evnt_name', 'evnt_nbvisited', 
		      'evnt_id as id', 'evnt_type', 'evnt_del', 'evnt_pbl', 'evnt_level');
      $tables[] = 'events';
      $where = "evnt_season=$season";
      //      $order = "evnt_level DESC, evnt_name";
      $order = 'evnt_firstday DESC, evnt_name';

      $res = $this->_select($tables, $fields, $where, $order);

      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgNoEvents';
	  return $infos;
	}
      require_once "utils/utimg.php";
      $uti = new utimg();
      $ut = new utils();

      $fields = array('evnt_name','evnt_date');
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $entry['logo'] = $uti->getPubliIcon($entry['evnt_del'], 
					 $entry['evnt_pbl']);
	  $entry['evnt_type'] = $ut->getLabel($entry['evnt_type']);
	  // Recherche des donnees dans la langue 
      	  $rows[] = $this->_getTranslate('events', $fields, 
					 $entry['evnt_id'], $entry);
        }
      return $rows;
    }
  // }}}

  // {{{ delEvents
  /**
   * Logical delete or restore some events
   *
   * @access public
   * @param  arrays  $events   id's of the event to delete
   * @param  integer $status    new status of the event
   * @return mixed
   */
  function delEvents($events, $status)
    {
      // Treat all the events
      foreach ( $events as $event => $id)
        {
	  // Add ! at the beginning and end of the name  
	  $fields = array('evnt_name');
	  $tables = array('events');
	  $where  = "evnt_id=$id";
	  $res = $this->_select($tables, $fields, $where);
	  $event = $res->fetchRow(DB_FETCHMODE_ORDERED);
	  $name = "!".$event[0]."!";
	  
          // Change the status
	  if ($status == WBS_DATA_DELETE)
	    $fields['evnt_name'] = $name;
	  $fields['evnt_del'] = $status;
	  $where = " evnt_id=$id";
	  $res = $this->_update('events', $fields, $where);
        } 
      return true;
    }
  // }}}
  
  // {{{ publiEvent
  /**
   * Change the publication state of some events
   *
   * @access public
   * @param  arrays  $events   id's of the event to delete
   * @param  integer $status    new publication status of the events
   * @return mixed
   */
  function publiEvent($eventId, $status)
    {
      // Change the status
      $fields['evnt_pbl'] = $status;
      $where = "evnt_id=$eventId";

      $res = $this->_update('events', $fields, $where);
      return true;
    }
  // }}}

  function inlineEvent($eventId, $status)
    {
      // Change the status
      $fields['evnt_liveentries'] = $status;
      $where = "evnt_id=$eventId";

      $res = $this->_update('events', $fields, $where);
      return true;
    }
    
  // {{{ updateEvent
  /**
   * Update the event with the informations
   *
   * @access public
   * @param  string  $info   column of the event
   * @return mixed
   */
  function updateEvent($infos)
    {
      $ute = new utevent();
      return $ute->update($infos);
    }
  // }}}

  // {{{ updateEventMeta
  /**
   * Update the event with the informations
   *
   * @access public
   * @param  string  $info   column of the event
   * @return mixed
   */
  function updateEventMeta($infos)
    {
      $where = "evmt_eventId=".$infos['evmt_eventId'];
      $fields[] = '*';
      $tables[] = 'eventsmeta';
      $res = $this->_select($tables, $fields, $where);

      if (!$res->numRows())
	$res = $this->_insert('eventsmeta', $infos);
      else
	$res = $this->_update('eventsmeta', $infos, $where);
      return true;      
    }
  // }}}

  // {{{ updateNew
  /**
   * Update the news with the informations
   *
   * @access public
   * @param  string  $info   column of the event
   * @return mixed
   */
  function updateNew($infos)
    {
      if ($infos["news_id"] != -1)
	{
	  $trans = array('news_text');
	  $this->_updtTranslate('news', $trans, 
				$infos['news_id'], $infos);



	  $where = "news_id=".$infos['news_id'];
	  $res = $this->_update('news', $infos, $where);
	}
      else
	{
	  unset($infos["news_id"]);
	  $res = $this->_insert('news', $infos);
	}
      return true;
      
    }
  // }}}


  // {{{ updateRight
  /**
   * Update the autorisation for a user
   *
   * @access public
   * @param  string  $info   column of the event
   * @return mixed
   */
  function updateRight($infos)
    {
      $tables = array('rights');
      $fields = array('rght_id', 'rght_status');
      $where = "rght_userId =". $infos['rght_userId'].
	" AND rght_theme =". WBS_THEME_EVENT.
	" AND rght_themeId =". $infos['rght_eventId'];
      $res = $this->_select($tables, $fields, $where);

      if ($res->numRows())
	{
	  unset($infos['rght_eventId']);
	  $right = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $where = 'rght_id='.$right['rght_id']; 
	  $res = $this->_update('rights', $infos, $where);
	}
      else
	{
	  $infos['rght_theme'] = WBS_THEME_EVENT;
	  $infos['rght_themeId'] = $infos['rght_eventId'];
	  unset($infos['rght_eventId']);
	  $res = $this->_insert('rights', $infos);
	}

      $tables = array('users');
      $fields = array('user_email');
      $where = "user_id =". $infos['rght_userId'];
      $res = $this->_select($tables, $fields, $where);

      $ut = new utils();
      $user = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $user['right'] = $ut->getLabel($infos['rght_status']);
      return $user;
    }
  // }}}
  
  // {{{ getRights
  /**
   * Return the users and they status for the current event
   *
   * @access public
   * @param  integer $eventId Id of the event
   * @param  char    $status  Status required for the user
   * @return array  list of users
   */
  function getRights($eventId)
    {
      // Select the users accordind their statut 
      // except the current logged user
      // and the owner of the tournament (event)
      $tables = array('rights', 'users');
      $fields = array('rght_id', 'user_login', 'user_name', 'user_pseudo',
		      'rght_status');
      $where  = "rght_userId = user_id" .
	" AND rght_theme =". WBS_THEME_EVENT .
	" AND rght_themeId = $eventId " .
	" AND rght_userId != ".utvars::getUserId().
	//" AND user_id != ".$event['evnt_ownerId'];
	" AND rght_del =".WBS_DATA_UNDELETE;
      $order = "2";
      
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $users['errMsg'] = 'msgNoUsers';
	  return $users;
        } 
      
      require_once "utils/utimg.php";
      $uti = new utimg();
      while ($user = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $user[5] = $uti->getIcon($user[4]);
	  $user[4] = '';	  
	  $users[] = $user;	  
        }

      return $users;
      
    }
  // }}}

  // {{{ getRight
  /**
   * Return the right informations
   *
   * @access public
   * @param  integer $rightId Id of the right
   * @return array  list of users
   */
  function getRight($rightId)
    {
      $tables = array('users', 'rights');
      $fields = array('rght_id', 'user_login', 'user_name',
		      'user_id', 'rght_status');
      $where = "rght_userId = user_id ".
	" AND rght_theme =". WBS_THEME_EVENT .
	" AND rght_themeId =".utvars::getEventId() .
	" AND rght_id =$rightId";
      $res = $this->_select($tables, $fields, $where);
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
  // }}}
  

  // {{{ getSelUsers
  /**
   * Return the users for a choice
   *
   * @access public
   * @return array  list of users
   */
  function getSelUsers()
    {
      // Select all the users except the current logged user
      // and the owner of the tournament (event)
      //$event  = $this->getEvent(utvars::getEventId());
      $tables = array('users');
      $fields = array('user_id', 'user_login', 'user_name', 'user_pseudo');
      $where  = "user_login != 'demo' ";
	//" AND user_id != ".utvars::getUserId().
	//" AND user_id != ".$event['evnt_ownerId'] .
	//" AND user_del =".WBS_DATA_UNDELETE;
      $order  = "user_name";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows())
	{
	  $users['errMsg'] = 'msgNoUsers';
	  return $users;
        } 
      
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
	{
	  $users[$entry[0]] = "{$entry[2]} ({$entry[3]}-{$entry[1]})";
	}
      return $users;      
    }
  // }}}
  
  // {{{ getNew
  /**
   * Return the column of an news
   *
   * @access public
   * @param  string  $newId   id of the news
   * @return array   information of the user if any
   */
  function getNew($newId)
    {
      $fields = array('news_id', 'news_text', 'news_page');
      $tables = array('news');
      $where  = "news_id = '$newId'";
      $res = $this->_select($tables, $fields, $where);
      $new = $res->fetchRow(DB_FETCHMODE_ASSOC);

      $fields = array('news_text');
      return $this->_getTranslate('news', $fields, 
				  $new['news_id'], $new);

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
  function getNews($eventId)
    {
      $fields = array('news_id', 'news_cre', 'news_text', 'news_page');
      $tables = array('news');
      $where  = "news_eventId = '$eventId'";
      $order  = "news_cre DESC";
      $res = $this->_select($tables, $fields, $where, $order);     
      if (!$res->numRows()) 
	{
	  $infos['errMsg'] = 'msgNoNews';
	  return $infos;
	} 
      $i = 0;
      $utd = new utdate();
      $fields = array('news_text');
      while($new = $res->fetchRow(DB_FETCHMODE_ASSOC) and $i<5)
	{
	  $i++;
	  $utd->setIsoDate($new['news_cre']);
	  $new['news_cre'] = $utd->getDate();
	  $news[] = $this->_getTranslate('news', $fields, 
				  $new['news_id'], $new);
	}
      return $news;
    }
  // }}}

  // {{{ getPostits
  /**
   * Return the column of the postit of an event
   *
   * @access public
   * @param  string  $eventId   id of the event
   * @return array   information of the user if any
   */
  function getPostits($eventId)
    {
      $fields = array('psit_id', 'psit_cre', 'psit_texte', 'psit_title',
		      'psit_page', 'psit_action', 'psit_data', 'psit_width',
		      'psit_height', 'psit_function');
      $tables = array('postits');
      $where  = "psit_eventId = '$eventId'";
      $order  = "psit_cre DESC";
      $res = $this->_select($tables, $fields, $where, $order);     
      if (!$res->numRows()) 
	{
	  $infos['errMsg'] = 'msgNoPostits';
	  return $infos;
	} 

      $utd = new utdate();
      $fields = array('news_text');
      while($post = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $utd->setIsoDate($post['psit_cre']);
	  $post['psit_cre'] = $utd->getDate();
	  //$posts[] = $this->_getTranslate('postits', $fields, 
	  //			  $post['psit_id'], $post);
	  $posts[] = $post;
	}
      return $posts;
    }
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
		      'user_lastvisit', 'user_cmt', 'user_nbcnx', 'user_updt');
      $tables[] = 'users';
      $where = "user_id=$userId";
      $res = $this->_select($tables, $fields, $where);
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
  // }}}


}
?>