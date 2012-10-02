<?php
/*****************************************************************************
 !   Module     : utils
 !   File       : $Source: /cvsroot/aotb/badnet/src/utils/utevent.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.17 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/03/28 21:43:16 $
 ******************************************************************************/
require_once "utbase.php";

/**
 * Cette classe permet de regrouper les methodes d'acces et de gestion
 * de la table des tournois 'events'.
 *
 * @author Gerard CANTEGRIL
 *
 */

class utevent extends utbase
{

	// {{{ properties
	// }}}

	// {{{ deleteEvent
	/**
	 * Supprime un tournoi
	 *
	 * @access public
	 * @param  integer  $eventId   id of the event
	 * @return string   Name of the event
	 */
	function deleteEvent($eventId)
	{
		if (is_array($eventId)) $events = $eventId;
		else	$events[] = $eventId;
		foreach($events as $event)
		{
	  $this->emptyEvent($event);
	  $this->_delete('events', "evnt_id=$event");
		}
		return;
	}
	//}}}

	// {{{ emptyEvent
	/**
	 * Vide un tournoi
	 *
	 * @access public
	 * @param  integer  $eventId   id of the event
	 * @return string   Name of the event
	 */
	function emptyEvent($event)
	{
		if (is_array($event)) $events = $event;
		else $events[] = $event;

		foreach($events as $eventId)
		{
	  $this->_delete('postits', "psit_eventId=$eventId");
	  $this->_delete('news', "news_eventId=$eventId");
	  $this->_delete('database', "db_eventId=$eventId");
	  $this->_delete('cnx', "cnx_eventId=$eventId");
	  //$this->_delete('eventsmeta', "evmt_eventId=$eventId");
	  $this->_delete('items', "item_eventId=$eventId");
	  $this->_delete('prefs', "pref_eventId=$eventId");
	  $this->_delete('subscriptions', "sub_eventId=$eventId");
	  //$this->_delete('eventsextra', "evxt_eventId=$eventId");
	  //$this->_delete('rights',
	  //		 "rght_themeId=$eventId AND rght_theme=".WBS_THEME_EVENT);
	   
	  // Suppression des equipes
	  $where = "team_eventId=$eventId";
	  $res = $this->_select('teams', 'team_id', $where);
	  while($team = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  {
	  	$teamId = $team['team_id'];
	  	$this->_delete('a2t', "a2t_teamId=$teamId");
	  	$this->_delete('t2t', "t2t_teamId=$teamId");
	  	$this->_delete('t2r', "t2r_teamId=$teamId");
	  }
	  $this->_delete('teams', "team_eventId=$eventId");
	   
	  // Suppression des tableaux (series)
	  $where = "draw_eventId=$eventId";
	  $res = $this->_select('draws', 'draw_id', $where);
	  while($draw = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  $drawId[] = $draw['draw_id'];
	   
	  if (isset($drawId))
	  {
	  	$this->_delete('draws', "draw_eventId=$eventId");
	  	$ids = implode(',', $drawId);
	  	$where = "rund_drawId IN ($ids)";
	  	$res = $this->_select('rounds', 'rund_id', $where);
	  	while($round = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  	$roundId[] = $round['rund_id'];
	  }
	   
	  // Suppression des tours (pole ko)
	  if (isset($roundId))
	  {
	  	$ids = implode(',', $roundId);
	  	$this->_delete('rounds', "rund_id IN ($ids)");
	  	$this->_delete('t2r', "t2r_roundId IN ($ids)");
	  	 
	  	$where = "tie_roundId IN ($ids)";
	  	$res = $this->_select('ties', 'tie_id', $where);
	  	while($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  	$tieId[] = $tie['tie_id'];
	  }
	   
	  // Suppression des rencontres et macth
	  if (isset($tieId))
	  {
	  	$ids = implode(',', $tieId);
	  	$this->_delete('ties', "tie_id IN ($ids)");
	  	$this->_delete('t2t', "t2t_tieId IN ($ids)");
	  	 
	  	$where = "mtch_tieId IN ($ids)";
	  	$res = $this->_select('matchs', 'mtch_id', $where);
	  	while($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  	{
	  		$this->_delete('p2m', "p2m_matchId={$match['mtch_id']}");
	  		$this->_delete('matchs', "mtch_id={$match['mtch_id']}");
	  	}
	  }
	   
	  // Suppression des comptes et commandes (achats)
	  $where = "cunt_eventId=$eventId";
	  $res = $this->_select('accounts', 'cunt_id', $where);
	  while($account = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  {
	  	$accountId = $account['cunt_id'];
	  	$this->_delete('commands', "cmd_accountId=$accountId");
	  }
	  $this->_delete('accounts', "cunt_eventId=$eventId");
	   
	  // Suppression des inscrits
	  $where = "regi_eventId=$eventId";
	  $res = $this->_select('registration', 'regi_id', $where);
	  while($regi = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  $regiId[] = $regi['regi_id'];
	   
	  if (isset($regiId))
	  {
	  	$this->_delete('registration', "regi_eventId=$eventId");
	  	$ids = implode(',', $regiId);
	  	$this->_delete('umpire', "umpi_regiId IN ($ids)");
	  	$this->_delete('ranks', "rank_regiId IN ($ids)");
	  	 
	  	$where = "i2p_regiId IN ($ids)".
		" AND i2p_pairId = pair_id";
	  	$tables = array('i2p', 'pairs');
	  	$res = $this->_select($tables, 'pair_id', $where);
	  	$pairId = array();
	  	while($pair = $res->fetchRow(DB_FETCHMODE_ASSOC))	$pairId[] = $pair['pair_id'];
	  	$this->_delete('i2p', "i2p_regiId IN ($ids)");
	  	if (count($pairId))
	  	{
	  		$ids = implode(',', $pairId);
	  		$this->_delete('pairs', "pair_id IN ($ids)");
	  	}
	  }
		}
		return;
	}
	// }}}


	// {{{ getEvent
	/**
	 * Return an event
	 *
	 * @access public
	 * @param  integer  $eventId   id of the event
	 * @return string   Name of the event
	 */
	function getEvent($eventId)
	{
		if (!$eventId)  return "";

		$fields = array('*');
		$tables[] = 'events';
		$where = "evnt_id = '$eventId'";
		//$this->_db->setOption('portability', DB_PORTABILITY_LOWERCASE);
		$this->_db->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
		$res = $this->_select($tables, $fields, $where);
		$event = $res->fetchRow(DB_FETCHMODE_ASSOC);
		//$this->_db->setOption(PDO::ATTR_CASE, DB_PORTABILITY_NONE);
		$this->_db->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
		return $event;
	}
	// }}}

	// {{{ getEvents
	/**
	 * Return the list of the events
	 *
	 * @access public
	 * @return array   array of events
	 */
	function getEvents($type=false, $level=false)
	{
		$fields = array('evnt_id', 'evnt_name');
		$where = "evnt_pbl !=". WBS_DATA_ARCHIVED;
		if ($type !== false)
		$where .= " AND evnt_type=$type";
		if ($level !== false)
		$where .= " AND evnt_level=$level";
		$order = "2";
		$res = $this->_select('events', $fields, $where, $order, 'evnt');

		$rows = array();
		while($event = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$rows[$event['evnt_id']] = $event['evnt_name'];

		return $rows;
	}
	// }}}

	// {{{ getArchivedEvents
	/**
	 * Return the list of the events
	 *
	 * @access public
	 * @return array   array of events
	 */
	function getArchivedEvents($type=false)
	{
		$fields = array('evnt_id', 'evnt_name', 'evnt_date');
		$tables[] = 'events';
		$where = "evnt_pbl =". WBS_DATA_ARCHIVED;
		if ($type != false)
		$where .= " AND evnt_level=$type";
		$order = "2";

		$res = $this->_select('events', $fields, $where, $order, 'evnt');

		$rows = array();
		while($event = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$rows[$event['evnt_id']] = $event['evnt_name'];
		return $rows;
	}
	// }}}

	// {{{ getLimitedEvents
	/**
	 * Return alimited list of the most visited events
	 *
	 * @access public
	 * @return array   array of events
	 */
	function getLimitedEvents($sort)
	{

		$fields = array('evnt_id', 'evnt_name', 'evnt_nbvisited', 'evnt_cre');
		$tables[] = 'events';
		$where = "evnt_pbl =". WBS_DATA_PUBLIC;
		$order = abs($sort);
		if ($sort<0)
		$order .= " DESC";

		$res = $this->_select('events', $fields, $where, $order);

		$rows = array();
		$nb = 0;
		while($event = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$rows[$event['evnt_id']] = $event['evnt_name'];
			if ($nb++ > 5) break;
		}
		return $rows;
	}
	// }}}

	// {{{ getConvoc
	/**
	 * Return the data for convocation
	 *
	 * @access public
	 * @return array   array of events
	 */
	function getConvoc()
	{
		$fields = array('evnt_delay', 'evnt_convoc',
		      'evnt_lieuconvoc', 'evnt_textconvoc');
		$tables[] = 'events';
		$where = "evnt_id =". utvars::getEventId();
		$res = $this->_select($tables, $fields, $where);
		if (count($res))
		$convoc = $res->fetchRow(DB_FETCHMODE_ASSOC);
		else
		{
	  $convoc = array('evnt_delay' => 30,
			  'evnt_convoc' => WBS_CONVOC_MATCH,
			  'evnt_lieuconvoc' => '', 
			  'evnt_textconvoc' => '');			  
		}
		return $convoc;
	}
	// }}}

	// {{{ update
	/**
	 * Update the number of connexion to the event
	 *
	 * @access public
	 * @param  integer   $eventId id of the visited event
	 * @return boolean
	 */
	function update($infos)
	{
		if (!isset($infos["evnt_id"])) return;
		if ($infos["evnt_id"] != -1)
		{
	  $fields = array('evnt_name', 'evnt_date', 'evnt_place',
			  'evnt_organizer', 'evnt_zone');
	  $data = $this->_updtTranslate('events', $fields,
	  $infos["evnt_id"], $infos);
	  $where = "evnt_id=".$data['evnt_id'];
	  $res = $this->_update('events', $data, $where);
		}
		else
		{
	  unset($infos["evnt_id"]);
	  $res = $this->_insert('events', $infos);
		}
	}
	// }}}

	// {{{ updtEventVisited
	/**
	 * Update the number of connexion to the event
	 *
	 * @access public
	 * @param  integer   $eventId id of the visited event
	 * @return boolean
	 */
	function updtEventVisited($eventId)
	{
		$fields = array('evnt_nbvisited', 'evnt_pbl', 'evnt_updt');
		$tables[] = 'events';
		$where = "evnt_id=$eventId";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		return false;

		$event = $res->fetchRow(DB_FETCHMODE_ASSOC);
		if ($event['evnt_pbl'] != WBS_DATA_PUBLIC)
		return false;

		$fields = array();
		$fields['evnt_nbvisited'] = $event['evnt_nbvisited']+1;
		$fields['evnt_updt'] = $event['evnt_updt'];
		$where = "evnt_id= $eventId";
		$res = $this->_update('events', $fields, $where);
		return true;
	}
	// }}}

	// {{{ isTeamAuto()
	/**
	 * Verifie les autorisation pour l'afficahe d'une equipe
	 *
	 * @access private
	 * @param  integer $tieId  Id of the tie
	 * @return void
	 */
	function isTeamAuto($assoId)
	{

		$level = $this->getAuthLevel();

		if ($level == WBS_AUTH_MANAGER ||
		$level == WBS_AUTH_ADMIN)
		return true;
		if ($level == WBS_AUTH_VISITOR)
		{
	  // Find assos of the team
	  $fields[] = 'rght_id';
	  $tables = array('rights');
	  $where = "rght_theme =".WBS_THEME_ASSOS.
	    " AND rght_themeId = $assoId".
	    " AND rght_userId =".utvars::getUserId();
	  $res = $this->_select($tables, $fields, $where);
	  return ($res->numRows()!=0);
		}
		return false;
	}



	// {{{ isTieAuto()
	/**
	 * Display a page with the list of the Matchs
	 *
	 * @access private
	 * @param  integer $tieId  Id of the tie
	 * @return void
	 */
	function isTieAuto($tieId)
	{

		$level = $this->getAuthLevel();

		if ($level == WBS_AUTH_MANAGER ||
		$level == WBS_AUTH_ADMIN)
		return true;
		if ($level == WBS_AUTH_ASSISTANT)
		{
	  $fields[] = 'mtch_id';
	  $tables = array('ties', 'matchs');
	  $where = "tie_id = $tieId".
	    " AND mtch_tieId = tie_id".
	    " AND mtch_status >". WBS_MATCH_ENDED;
	  $res = $this->_select($tables, $fields, $where);
	  return ($res->numRows()==0);
		}
		return false;
	}


	// {{{ getMatchStatus
	/**
	 * Return the status of an ended match
	 *
	 * @access public
	 * @param  integer  $eventId   id of the event
	 * @return string   Name of the event
	 */
	function getMatchStatus()
	{

		$level = $this->getAuthLevel();

		if ($level == WBS_AUTH_ASSISTANT)
		return WBS_MATCH_ENDED;
		else
		return WBS_MATCH_CLOSED;
	}
	// }}}

	// {{{ getAuthLevel()
	/**
	 * Return the level of autorization of the user connected
	 * WBS_AUTH_ADMIN  :      the user is administraor, he can do everything
	 * WBS_AUTH_GESTPUBLIC :  the user can consult every data
	 *                         and modify only public data (published or not)
	 * WBS_AUTH_GESTPRIVATE :  the user can consult every data
	 *                         and modify only private data
	 * WBS_AUTH_CONSULT     :  user can consult every date (private and public)
	 *                         but can't modify
	 * WBS_AUTH_INVITED     :  user can consult public data (published or not)
	 * WBS_AUTH_VISITOR     :  user can consult only publish public data
	 *
	 * @access public
	 * @param string $eventId  id of the event to check
	 * @return int auth level
	 */
	function getAuthLevel()
	{
		// Get the authorisation level of the user
		$level = utvars::_getSessionVar('userAuth');
		//echo "utils::getAuthLevel:level=$level<br>";

		// For a user, look according the current theme
		if ($level == WBS_AUTH_USER || $level == WBS_AUTH_MANAGER || $level == WBS_AUTH_REFEREE)
		{
	  // The user is a visitor
	  $level = WBS_AUTH_VISITOR;

	  // Get the right of the current users for the current theme
	  $theme = utvars::getTheme();
	  if ($theme == WBS_THEME_NONE) return $level;
	   
	  if ($theme == WBS_THEME_BOOK)
	  $themeId = utvars::getBookId();
	  if ($theme == WBS_THEME_EVENT)
	  $themeId =  utvars::getEventId();
	  //echo "utevents::getAuthLevel:theme=$theme; themeId=$themeId<br>";
	  $fields[] = 'rght_status';
	  $tables[] = 'rights';
	  $where = "rght_userId = ".utvars::getUserId().
	    " AND rght_theme = $theme".
	    " AND rght_themeId = '$themeId'";
	  //echo "utevents::getAuthLevel:query=$query<br>";
	  $res = $this->_select($tables, $fields, $where);
	   
	  // If nothing find, the users is just visitor
	  if (! $res->numRows()) return $level;

	  // The user is more then  visitor
	  $right = $res->fetchRow(DB_FETCHMODE_ORDERED);
	  //if ($right[0] != WBS_AUTH_GUEST &&
	  //    $right[0] != WBS_AUTH_FRIEND)
	  $level = $right[0];
	  // Du provisoire: pour un tournoi, un manager
	  // est un admministrateur
	  if ($level == WBS_AUTH_MANAGER) $level = WBS_AUTH_ADMIN;
		}
		return $level;
	}
	// }}}

	// {{{ getAuthMask()
	/**
	 * Return the data access mask for the user connected
	 *
	 * @access public
	 * @param string $eventId  id of the event to check
	 * @return int auth level
	 */
	function getAuthMask()
	{
		$level = $this->getAuthLevel();
		// Get the authorisation level of the user
		$mask = WBS_DATA_PUBLIC;

		if ( ($level == WBS_AUTH_GUEST) ||
		($level == WBS_AUTH_MANAGER) ||
		($level == WBS_AUTH_ADMIN))
		$mask |= WBS_DATA_PRIVATE;

		if ( ($level == WBS_AUTH_FRIEND) ||
		($level == WBS_AUTH_MANAGER) ||
		($level == WBS_AUTH_ADMIN))
		$mask |= WBS_DATA_CONFIDENT;

		return $mask;
	}
	// }}}


	/**
	 * Return an event
	 *
	 * @access public
	 * @param  integer  $eventId   id of the event
	 * @return string   Name of the event
	 */
	function getMetaEvent($eventId)
	{
		if (!$eventId)  return "";

		$fields = array('*');
		$tables[] = 'eventsmeta';
		$where = "evmt_eventId = '$eventId'";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		$meta = array('evmt_eventId' => $eventId,
		      'evmt_urlStream' => '',
		      'evmt_urlLiveScore' => '',
	          'evmt_titleFont' => 'mtcorsva',
		      'evmt_titleSize' => 22,
		      'evmt_skin' => 'badnet',
		      'evmt_badgeId' => '1',
		      'evmt_logo' => '',
		      'evmt_badgeLogo' => '',
		      'evmt_badgeSpon' => '',
		      'evmt_top' => 28,
		      'evmt_left' => 10,
		      'evmt_width' => 70,
		      'evmt_height' => 20
		);
		else
		$meta = $res->fetchRow(DB_FETCHMODE_ASSOC);
		if ($meta['evmt_titleFont']=='')
		$meta['evmt_titleFont']= 'mtcorsva';
		if ($meta['evmt_titleSize']==0)
		$meta['evmt_titleSize']= 22;
		return $meta;
	}

	/**
	 * Return an event
	 *
	 * @access public
	 * @param  integer  $eventId   id of the event
	 * @return string   Name of the event
	 */
	function getExtraEvent($eventId)
	{
		if (!$eventId)  return "";

		$fields = array('*');
		$tables[] = 'eventsextra';
		$where = "evxt_eventId = '$eventId'";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows()) $extra = array('evxt_liveupdate' => WBS_NO);
		else $extra = $res->fetchRow();
		return $extra;
	}

	// {{{ getBadgeDef
	/**
	 * Return an event
	 *
	 * @access public
	 * @param  integer  $eventId   id of the event
	 * @return string   Name of the event
	 */
	function getBadgeDef($badgeId)
	{
		$fields = array('*');
		$tables[] = 'badges';
		$where = "bdge_id = '$badgeId'";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		{
	  $err['err_msg'] = "msgUnknowBadge";
	  return $err;
		}

		$def = $res->fetchRow(DB_FETCHMODE_ASSOC);

		$fields = array('*');
		$tables = array('eltbadges');
		$where = "eltb_badgeId = '$badgeId'";
		$res = $this->_select($tables, $fields, $where);
		$elts = array();
		while ($elt = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$elts[] = $elt;
		$def['elts'] = $elts;
		return $def;
	}
	// }}}

	// {{{ getEventType
	/**
	 * Return the type of an event
	 *
	 * @access public
	 * @param  integer  $eventId   id of the event
	 * @return string   Name of the event
	 */
	function getEventType($eventId)
	{
		if (!$eventId)  return "";

		$fields = array('evnt_type');
		$tables[] = 'events';
		$where = "evnt_id = '$eventId'";
		$res = $this->_select($tables, $fields, $where);
		$infos = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $infos['evnt_type'];
	}
	// }}}


	// {{{ addEventRight
	/**
	 * Add the right for the select event to the select user
	 *
	 * @access public
	 * @param  array   $infos  Information for the rights
	 * @return void
	 */
	function addEventRight($userId, $eventId, $right)
	{
		//  Search if the right already exist
		$fields[] = 'rght_status';
		$tables[] = 'rights';
		$where = "rght_userId =$userId".
	" AND rght_themeId =$eventId".
	" AND rght_theme   =".WBS_THEME_EVENT;
		$res = $this->_select($tables, $fields, $where);

		$fields = array();
		$fields['rght_status']  = $right;
		$fields['rght_theme']   = WBS_THEME_EVENT;
		$fields['rght_themeId'] = $eventId;
		$fields['rght_userId']  = $userId;
		if (! $res->numRows())
		{
	  $res = $this->_insert('rights', $fields);
		}
		else
		{
	  $where = "rght_userId = $userId".
	    " AND rght_themeId = $eventId".
	    " AND rght_theme   =".WBS_THEME_EVENT;
	  $res = $this->_update('rights', $fields, $where);
		}
		return true;
	}
	// }}}


	// {{{ getManagers
	/**
	 * Return the list of the managers of the current event
	 *
	 * @access public
	 * @return array   array of users
	 */
	function getManagers()
	{
		$eventId = utvars::getEventId();
		$fields = array('user_id', 'user_name', 'user_pseudo', 'user_email');
		$tables = array('users', 'rights');
		$where  = "user_id = rght_userId".
	" AND user_del != ".WBS_DATA_DELETE.
	" AND user_email != ''".
	" AND rght_theme =". WBS_THEME_EVENT.
	" AND rght_themeId = $eventId".
	" AND rght_status = '".WBS_AUTH_MANAGER."'";
		$order = "1 ";
		$res = $this->_select($tables, $fields, $where, $order);
		if ($res->numRows())
		{
	  while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  $rows[] = $entry;
	  return $rows;
		}
		else
		return $this->getAdministrators();
	}
	// }}}

	// {{{ getAdministrators
	/**
	 * Return the list of the administrators
	 *
	 * @access public
	 * @return array   array of users
	 */
	function getAdministrators()
	{
		$fields = array('user_id', 'user_name', 'user_pseudo', 'user_email');
		$tables[] = 'users';
		$where = "user_type ='".WBS_AUTH_ADMIN."'".
	" AND user_email != ''";
		$order = "1 ";
		$res = $this->_select($tables, $fields, $where, $order);
		$rows = array();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $rows[] = $entry;
		}
		return $rows;
	}
	// }}}

}
?>