<?php
/*****************************************************************************
!   Module     : Teams
!   File       : $Source: /cvsroot/aotb/badnet/src/teams/base_G.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:21 $
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
require_once "utils/utbase.php";

/**
* Acces to the dababase for events
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class teamsBase_G  extends utbase
{

  // {{{ properties
  // }}}

  // {{{ getPlayers
  /**
   * Return the players of a team
   *
   * @access public
   * @param  integer  $teamId  Id of the team
   * @return array   array of users
   */
  function getPlayers($teamId, $sort)
    {
      $ut = new utils();
      // Select the players of the team
      $eventId = utvars::getEventId();
      $fields = array('regi_id',  'mber_sexe', 'regi_longName', 
		      'mber_licence', 'mber_ibfnumber',  
		      'rkdf_label',  'asso_noc');
      $tables = array('members', 'registration', 'ranks', 'rankdef', 
		      'assocs', 'a2t');
      $where  = "regi_del != ".WBS_DATA_DELETE.
	" AND asso_id = a2t_assoId".
	" AND a2t_teamId = regi_teamId".
	" AND regi_teamId = $teamId".
	" AND regi_memberId = mber_id ".
	" AND regi_eventId = $eventId".
	" AND regi_id = rank_regiId".
	" AND rank_rankdefId = rkdf_id".
	" AND regi_type = ".WBS_PLAYER.
	" AND mber_id >= 0";
      $order = "regi_id, rank_disci";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows()) 
	{
	  echo "blabla";
	  $infos['errMsg'] = "msgNoPlayers";
	  return $infos;
	}
      $id = -1;
      $rows = array();
      if ($sort < 0) $sort++;
      else $sort--;
      $uti = new utimg();
      $utd = new utdate();

      // Boucle sur les joueurs
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
 	  if ($id != $entry['regi_id'])
 	    {
 	      if($id > 0)
		{
		  $tmp['mber_sexe'] = $ut->getSmaLabel($tmp['mber_sexe']);
		  //$sw[] = $tmp[abs($sort)];
		  $rows[$id] = $tmp;
		}
 	      $tmp = $entry;
 	      $id = $entry['regi_id'];
 	    }
 	  else
	    {
 	      $tmp['rkdf_label'] .= ",{$entry['rkdf_label']}";
	    }
        }
      $tmp['mber_sexe'] = $ut->getSmaLabel($tmp['mber_sexe']);
      //$sw[] = $tmp[abs($sort)];
      $rows[$id] = $tmp;
      
      // Pour un tournoi individuel, recuperer les inscriptions
      // Et les heures de premier match
      if (!utvars::isTeamEvent())
	{
	  $fields = array('i2p_regiId',  'pair_drawId', 'pair_disci',
			  'draw_stamp');
	  $tables = array('registration', 'i2p', 'pairs LEFT JOIN draws ON pair_drawId = draw_id');
	  $where  = "regi_teamId = $teamId".
	    " AND regi_eventId = $eventId".
	    " AND regi_id = i2p_regiId".
	    " AND i2p_pairId = pair_id".
	    " AND regi_type = ".WBS_PLAYER;
	  //" AND pair_drawId = draw_id";
	  $order = "1, pair_disci";
	  $res = $this->_select($tables, $fields, $where, $order);
	  if ($res->numRows())
	    {
	      $id = -1;
	      $trans = array('draw_stamp');
	      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
		  $entry = $this->_getTranslate('draws', $trans, 
						$entry['pair_drawId'], $entry);
		  if ($id != $entry['i2p_regiId'])
		    {
		      if ($id > 0) $rows[$id]['asso_noc'] = $tmp;	
		      if ($entry['draw_stamp'] == '') 
			$tmp = '--';
		      else
			$tmp = $entry['draw_stamp'];
		      $id = $entry['i2p_regiId']; 	    
		    }
		  else
		    if ($entry['draw_stamp'] == '') 
		      $tmp .= ",--";
		    else		      
		      $tmp .= ",".$entry['draw_stamp'];
		}
	      $rows[$id]['asso_noc'] = $tmp;	
	    }

	  // Heure de premier match
	  $fields = array('regi_id', 'tie_schedule', 'tie_place');
	  $tables = array('registration', 'i2p',
			  'p2m', 'matchs', 'ties');
	  $where  = "regi_teamId = $teamId".
	    " AND regi_eventId = $eventId".
	    " AND regi_id = i2p_regiId".
	    " AND i2p_pairId = p2m_pairId".
	    " AND p2m_matchId = mtch_id".
	    " AND tie_schedule != ''".
	    " AND mtch_tieId = tie_id";
	  $order = "1, tie_schedule";
	  $res = $this->_select($tables, $fields, $where, $order);
	  if ($res->numRows())
	    {
	      $id = -1;
	      $utd = new utdate();
	      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
		  if ($id != $entry['regi_id'])
		    {
		      $id = $entry['regi_id'];
		      $utd->setIsoDateTime($entry['tie_schedule']);
		      $utd->addMinute(CONVOCATION);   
		      $rows[$id]['8'] = $utd->getDateTime();	
		      $rows[$id]['9'] = $entry['tie_place'];	
		    }
		}
	    }
	}
      /*      if ($sort < 0)
	array_multisort($sw, SORT_DESC,$rows);
      else
	array_multisort($sw, $rows);*/
      return $rows;
    }
  // }}}

  // {{{ getTeams
  /**
   * Return the list of the teams for an association
   *
   * @access public
   * @return array   array of users
   */
  function getTeams($assoId)
    {

      $fields = array('team_id', 'team_name');
      $tables = array('a2t', 'teams');
      $where = "a2t_assoId = $assoId".
	' AND a2t_teamId = team_id'. 
	' AND team_eventId ='. utvars::getEventId(). 
	' AND team_del !='. WBS_DATA_DELETE;
      $order = 'team_name';
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows()) 
	{
	  $err['errMsg'] = "msgNoAssocs";
	  return $err;
        } 

      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	$rows[] = $entry;
	
      return $rows;
    }
  // }}}

  // {{{ getAssos
  /**
   * Return the list of the registered association
   *
   * @access public
   * @param  integer  $sort   criteria to sort assos
   * @return array   array of users
   */
  function getAssos($sort)
    {
      // Select associations in the database
      $eventId = utvars::getEventId();
      $fields = array('DISTINCT asso_id', 'asso_name', 'asso_pseudo',  
		      'asso_url', 'asso_logo',);

      $tables = array('teams', 'assocs', 'a2t');
      $where = "team_eventId = $eventId ".
	" AND a2t_assoId = asso_id ".
	" AND a2t_teamId = team_id ";
      $order = abs($sort);
      if ($sort < 0)
	  $order .= " DESC";      
      $res = $this->_select($tables, $fields, $where, $order, 'team');
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgNoTeams';
	  return $infos;
	}

      // Prepare a table with the assocation 
      $rows = array();
      $trans = array('asso_name','asso_stamp', 'asso_pseudo');
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $entry = $this->_getTranslate('assos', $trans, 
					$entry['asso_id'], $entry);
	  $rows[$entry['asso_id']] = $entry['asso_name'];
        }
      return $rows;
    }
  // }}}






  // {{{ getCredits
  /**
   * Return the list of the purchases for an account
   *
   * @access public
   * @param  string  $sort   criteria to sort associations
   * @return array   array of users
   */
  function getCredits($teamId, $sort=1)
    {

      $fields = array('cmd_id', 'cmd_date', 'regi_longName', 'regi_type',
		      'cmd_payed', 'cmd_discount', 'cmd_value-cmd_payed');
      $tables = array('commands', 'registration');
      $where = "regi_teamId = $teamId".
	" AND cmd_regiId = regi_id".
	" AND cmd_itemId = -1";
      $order = abs($sort); 
      if ($sort < 0)
	$order .= ' DESC';
      $res = $this->_select($tables, $fields, $where, $order);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
        } 
      
      if (!$res->numRows()) 
	{
	  $err['errMsg'] = "msgNoCredits";
	  return $err;
        } 

      $utd = new utdate();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
	{
 	  $utd->setIsoDate($entry[1]);
	  $entry[1] = $utd->getDate();
	  $entry[3] = $this->_ut->getLabel($entry[3]);
	  if ($entry[6] == 0)
	    $entry[6] = $this->_ut->getLabel(WBS_YES);
	  else if ($entry[6] == $entry[5])
	    $entry[6] = $this->_ut->getLabel(WBS_NO);
	  $rows[] = $entry;
	}
      return $rows;
    }
  // }}}
  
  // {{{ searchAssos
  /**
   * Return the list of the associatons acoording criteria
   *
   * @access public
   * @param  string  $sort   criteria to sort associations
   * @return array   array of users
   */
  function searchAssos($criteria, $sort)
    {

      $fields = array('asso_id', 'asso_name', 'asso_pseudo', 'asso_stamp',
		      'asso_type');
      $tables = array('assocs');
      $where = "asso_name LIKE '".$criteria['asso_name']."%'".
	" AND asso_pseudo LIKE '".$criteria['asso_pseudo']."%'".
	" AND asso_stamp LIKE '".$criteria['asso_stamp']."%'";
      if ($criteria['asso_type']!='')
	$where .= ' AND asso_type = '.$criteria['asso_type'];
      $order = abs($sort);
      if ($sort < 0)
	$order .=  ' DESC';
      $res = $this->_select($tables, $fields, $where, $order);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
        } 
      
      if (!$res->numRows()) 
	{
	  $err['errMsg'] = "msgNoAssocs";
	  return $err;
        } 

      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
	{
	  $entry[4] = $this->_ut->getLabel($entry[4]);
	  $rows[] = $entry;
	}
      return $rows;
    }
  // }}}

  // {{{ getMembers
  /**
   * Return the members of a team
   *
   * @access public
   * @param  integer  $teamId  Id of the team
   * @return array   array of users
   */
  function getMembers($assoId, $sort)
    {
      // Select the players of the asso
      $eventId = utvars::getEventId();
      $fields = array('regi_id', 'mber_sexe', 'regi_longName', 
		      'regi_type', 'regi_arrival', 'regi_arrcmt', 
		      'regi_departure', 'regi_depcmt', 'team_id', 'team_name');
      $tables = array('a2t', 'teams', 'members',  'registration');
      $where  = "regi_del != ".WBS_DATA_DELETE.
	" AND a2t_assoId = $assoId".
	" AND regi_teamId = a2t_teamId".
	" AND regi_memberId = mber_id ".
	" AND regi_eventId = $eventId".
	" AND mber_id >= 0".
	" GROUP BY regi_id";
      $order = "team_id,".abs($sort);
      if ($sort < 0)
	$order .= " DESC";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows()) 
	{
	  $infos['errMsg'] = "msgNoPlayers";
	  return $infos;
	}
      $utd = new utdate();
      $ut = new utils();
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $entry[1] = $ut->getSmaLabel($entry[1]);
	  $entry[3] = $ut->getLabel($entry[3]);
	  $utd->setIsoDateTime($entry['4']);
	  $entry[4] = $utd->getDateTime().' '.$entry[5];
	  $utd->setIsoDateTime($entry['6']);
	  $entry[5] = $utd->getDateTime().' '.$entry[7];
	  $rows[]=$entry;
        }
      return $rows;
    }
  // }}}

    
  // {{{ getPairs
  /**
   * Return the plairs of an association
   *
   * @access public
   * @param  integer  $teamId  Id of the team
   * @return array   array of users
   */
  function getPairs($assoId)
    {
      $ut = new utils();
      // Select the pairs  of the team
      $fields = array('i2p_pairId');
      $tables = array('registration', 'i2p', 'pairs', 'draws',
		      'teams', 'a2t', 'assocs');
      $where  = "regi_id = i2p_regiId".
	" AND i2p_pairId = pair_id".
	" AND pair_drawId = draw_id".
	" AND regi_teamId = team_id".
	" AND team_id = a2t_teamId".
	" AND a2t_assoId = asso_id".
	" AND asso_id = $assoId".
	" AND team_eventId = ".utvars::getEventId();
      $res = $this->_select($tables, $fields, $where);
      if (!$res->numRows()) 
	{
	  $infos['errMsg'] = "msgNoPlayers";
	  return $infos;
	}
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	$ids[] = $entry['i2p_pairId'];
      $pairsIds = implode(',', $ids);

      // Select the players of the pairs
      $fields = array('pair_id', 'regi_longName', 'draw_name','pair_intRank',
		      'pair_natRank', 'rkdf_label', 'pair_status', 'pair_wo', 'pair_order',
		      'regi_id', 'team_id', 'team_name', 'draw_disci', 'draw_id',
		      'asso_id', 'asso_pseudo'
		      );
      $tables = array('pairs', 'i2p', 'registration', 'draws',
		      'members', 'teams', 'a2t', 'assocs', 'ranks', 'rankdef');
      $where  = "regi_id = i2p_regiId".
	" AND i2p_pairId = pair_id".
	" AND draw_id=pair_drawId".
	" AND mber_id=regi_memberId".
	" AND regi_teamId=team_id".
	" AND pair_id IN ($pairsIds)".
	" AND team_id = a2t_teamId".
	" AND a2t_assoId = asso_id".
	" AND rank_regiId = regi_id".
	" AND rank_rankdefId = rkdf_id".
        " AND rank_disci = draw_disci";

      $order = "pair_id, mber_sexe, regi_longName";
      $res = $this->_select($tables, $fields, $where, $order);
      if (!$res->numRows()) 
	{
	  $infos['errMsg'] = "msgNoPlayers";
	  return $infos;
	}

      $uti = new utimg();
      $utd = new utdate();

      // Construction des paires: fusion des lignes d'une meme paire
      $pairId = -1;
      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  if ($pairId != $entry['pair_id'])
	    {
	      if ($pairId > -1)
		{
		  $teamSort[] = $row['draw_disci'];
		  $row['regi_longName'] = $cells['name'];
		  $row['rkdf_label'] = $cells['level'];
		  $rows[] = $row;
		}
	      $cells['name']  = array();
	      $cells['level']  = array();
	      $row = $entry;
	      if ($entry['pair_wo']) $class="classWo";
	      else $class="";
	      $row['pair_status'] = $ut->getSmaLabel($entry['pair_status']);
	      if ((($entry['pair_status'] == WBS_PAIR_RESERVE) ||
		   ($entry['pair_status'] == WBS_PAIR_QUALIF))&& 
		  ($entry['pair_order']>0))
		$row['pair_status'] = sprintf("%s %02d",$row['pair_status'],
					      $entry['pair_order']);
	      if($entry['pair_intRank'] <= 0)
		$row['pair_intRank'] = '';
	      if($entry['pair_natRank'] <= 0)
		$row['pair_natRank'] = '';
	      $pairId = $entry['pair_id'];
	    }
	  $lines = $cells['name'];
	  $value = $entry['regi_longName'];
	  if ($entry['asso_id'] != $assoId)
	    $value .= ' ('.$entry['asso_pseudo'].')';
	  $lines[] = array('value' => $value
			   //,'logo' => $logo
			   ,'class' => $class
			   ,'action' => array(KAF_UPLOAD, 'resu', 
					      KID_SELECT, $entry['regi_id'])
			   );
	  $cells['name'] = $lines;

	  $lines = $cells['level'];
	  $value = $entry['rkdf_label'];
	  $lines[] = array('value' => $value
			   //,'logo' => $logo
			   ,'class' => $class
			   );
	  $cells['level'] = $lines;
        }
      $teamSort[] = $row['draw_disci'];
      $row['regi_longName'] = $cells['name'];
      $row['rkdf_label'] = $cells['level'];
      $rows[] = $row;
      array_multisort($teamSort, $rows);
      return $rows;
    }
  // }}}

  // {{{ getAsso
  /**
   * Return the column of an association
   *
   * @access public
   * @param  string  $assoId  id of the member
   * @return array   information of the member if any
   */
  function getAsso($assoId)
    {
      $fields = array('asso_id', 'asso_name', 'asso_stamp', 'asso_pseudo',
		      'asso_type', 'asso_cmt', 'asso_url', 'asso_logo',
		      'asso_noc');
      $tables[] = 'assocs';
      $where = "asso_id = '$assoId'";
      $res = $this->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
        } 
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
  // }}}

  // {{{ getTeam
  /**
   * Return the column of a team and his association
   *
   * @access public
   * @param  string  $teamId  id of the team
   * @return array   information of the member if any
   */
  function getTeam($teamId)
    {
      $fields = array('team_id', 'team_date', 'team_cmt', 'team_captain',
		      'team_name', 'team_stamp', 'cunt_id', 'cunt_name', 
		      'a2t_assoId as asso_id');
      $tables = array('a2t', 
		      'teams LEFT JOIN accounts ON team_accountId = cunt_id');
      $where = "team_id = '$teamId'".
	" AND a2t_teamId = team_id".
	' AND team_eventId ='. utvars::getEventId();
      //	" AND team_accountId = cunt_id";
      $res = $this->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
        } 
      $data = $res->fetchRow(DB_FETCHMODE_ASSOC);

      $utd = new utdate();
      $utd->setIsoDateTime($data['team_date']);
      $data['team_date'] = $utd->getDate();

      return $data;
    }
  // }}}

  // {{{ getTeamAccount
  /**
   * Return the column of a member
   *
   * @access public
   * @param  string  $memberId  id of the member
   * @return array   information of the member if any
   */
  function getTeamAccount($teamId)
    {
      $fields = array('team_id', 'team_accountId', 'team_name',
		      'cunt_id', 'cunt_name');
      $tables = array('teams LEFT JOIN accounts ON
           team_accountId = cunt_id');
      $where = "team_id = '$teamId'";
      $res = $this->_select($tables, $fields, $where);
      if (empty($res))
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
	} 

      $regi =  $res->fetchRow(DB_FETCHMODE_ASSOC);
      return $regi;
    }
  // }}}

  // {{{ getAccounts
  /**
   * Return the list of accounts
   *
   * @access public
   * @return array   information of the member if any
   */
  function getAccounts()
    {
      $fields = array('cunt_id', 'cunt_name');
      $tables = array('accounts');
      $order  = "cunt_name";
      $res = $this->_select($tables, $fields, false, $order);
      if (empty($res))
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
	} 

      while ($account =  $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $rows[$account['cunt_id']] = $account['cunt_name'];
	}
      return $rows;
    }
  // }}}

  // {{{ getPurchaseList
  /**
   * Return the list of the reservation
   *
   * @access public
   * @param  string  $sort   criteria to sort associations
   * @return array   array of users
   */
  function getPurchaseList($teamId)
    {
      $eventId = utvars::getEventId();
      $fields = array('regi_id', 'item_id', 'cmd_date', 'cmd_id', 'cmd_discount');
      $tables = array('commands',  'items', 'registration');
      $where  = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_teamId = $teamId".
	" AND regi_id = cmd_regiId".
	" AND regi_eventId = $eventId".
	" AND cmd_itemId = item_id".
	" AND item_pbl =".WBS_DATA_PRIVATE;

      $order = "cmd_date, regi_id";
      $res = $this->_select($tables, $fields, $where, $order);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
        } 
      
      if (!$res->numRows()) 
	{
	  $err['errMsg'] = "msgNoReservations";
	  return $err;
        } 

      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
	$rows[] = $entry;
      return $rows;
    }
  // }}}

  // {{{ getSelectItems
  /**
   * Return the list of the items' code
   *
   * @access public
   * @param  none
   * @return array   array of codes
   */
  function getSelectItems()
    {

      $fields = array('item_id, item_code', 'item_name');
      $tables = array('items');
      $where = "item_pbl =".WBS_DATA_PRIVATE;
      $res = $this->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
        } 
      
      if (!$res->numRows()) 
	{
	  $err['errMsg'] = "msgNoSelectItems";
	  return $err;
        } 

      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
	  $rows[$entry[0]] = $entry;
      return $rows;
    }
  // }}}
}
?>