<?php
/*****************************************************************************
 !   Module     : Export Dbf
 !   File       : $Source: /cvsroot/aotb/badnet/src/dbf/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.15 $
 !   Author     : G.CANTEGRIL
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/03/28 21:43:16 $
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
require_once "utils/utscore.php";
require_once "utils/utround.php";


/**
 * Exportation au format dbf pour la FFba
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 *
 */

class dbfBase extends utbase
{

   // {{{ properties
   // }}}


   // {{{ getRegistered
   /**
    * Return the list of the tour
    *
    * @access public
    * @param string   $begin  first date
    * @param string   $end    end date
    * @return array   array of match
    */
   function getRegistered()
   {
      $eventId = utvars::getEventId();
      $fields  = array('asso_id', 'asso_name', 'asso_stamp', 'asso_type',
		       'asso_url', 'asso_number', 'asso_dpt', 'asso_noc',
		       'team_id', 'team_name', 'team_stamp', 'team_date', 
		       'team_url', 'team_noc', 'team_captain',
		       'mber_id', 'mber_firstname', 'mber_secondname',
		       'mber_sexe', 'mber_born', 'mber_ibfnumber', 
		       'mber_licence',
		       'regi_id', 'regi_longName', 'regi_shortName', 
		       'regi_type', 'regi_function', 'regi_noc',
		        'regi_date',
		       'rank_disci', 'rank_average'		       
		       );
		       $tables = array('assocs', 'a2t', 'teams', 'members', 'ranks',
		      'registration LEFT JOIN i2p ON regi_id=i2p_regiId'
		      );
		      $where = "asso_id = a2t_assoId".
	" AND a2t_teamId = team_id".
	" AND team_eventId = $eventId".
	" AND team_id = regi_teamId".
	" AND mber_id = regi_memberId".
	" AND mber_id >= 0".
	" AND i2p_regiId = rank_regiId";

		      $order = "asso_name, team_name, mber_secondname, mber_firstname";

		      $res = $this->_select($tables, $fields, $where, $order);
		      $rows = array();
		      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		      {
		         /*
		          $tmp["id"] = $entry["asso_id"];
		          $tmp["association"] = $entry["asso_name"];
		          $tmp["equipe"] = $entry["team_name"];
		          $tmp["nom"] = $entry["mber_secondname"];
		          $tmp["prenom"] = $entry["mber_firstname"];
		          $tmp["disci"] = $entry["rank_disci"];
		          $tmp["average"] = $entry["rank_average"];
		          $rows[] = $tmp;
		          */
		         $rows[] = $entry;
		      }
		      return $rows;

   }
   // }}}

   // {{{ getMatchs
   /**
    * Return the list of the match according criteria
    *
    * @access public
    * @param string   $begin  first date
    * @param string   $end    end date
    * @return array   array of match
    */
   function getMatchs($begin, $end, $ties, $exportAll, $more=null)
   {
      $ut = new utils();

      $eventId = utvars::getEventId();
      $fields  = array('mtch_id', 'p2m_pairId', 'draw_name', 'draw_stamp','rund_name',
		       'tie_id', 'mtch_discipline', 'mber_licence',
		       'mber_secondname' ,'mber_firstname', 'mber_sexe',
		       'mber_born', 'asso_stamp', 'asso_dpt', 'mtch_order',
		       'mtch_score', 'mtch_discipline', 'p2m_result', 
		       'mtch_id', 'tie_schedule', 'tie_step', 'rank_average',
		       'draw_catage', 'draw_rankDefId',
		       'rkdf_label', 'tie_posRound', 'rund_type');
      $tables = array('matchs', 'p2m', 'i2p', 'registration',
		      'members', 'teams', 'a2t', 'assocs', 'ties',
		      'rounds', 'draws', 'ranks', 'rankdef');
      $where = "mtch_id = p2m_matchId".
	" AND p2m_pairId = i2p_pairId".
	" AND i2p_regiId = regi_id".
	" AND regi_memberId = mber_id".
	" AND regi_teamId = team_id".
	" AND regi_eventId = $eventId".
	" AND regi_teamId = a2t_teamId".
	" AND mtch_tieId = tie_id".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND a2t_assoId = asso_id".
	" AND rank_regiId = regi_id".
	" AND rank_rankdefId = rkdf_id".
	" AND rank_disci = mtch_discipline";
      if (!is_null($more))
      {
         $where .= " AND draw_stamp = '{$more['draw_stamp']}'".
	    " AND rund_stamp = '{$more['rund_stamp']}'";
	    " AND tie_step   = '{$more['tie_step']}'";
      }
      if ($begin != -1 && $end != -1)
      {
         $where .= " AND tie_schedule >= '$begin'".
	    " AND tie_schedule <= '$end'";
      }
      if (count($ties))
      {
         $tiesList = implode(',', $ties);
         $where .= " AND tie_id IN ($tiesList)";
      }
      if ($exportAll)
      $where .= " AND mtch_status >=".WBS_MATCH_CLOSED;
      else
      $where .= " AND mtch_status =".WBS_MATCH_CLOSED;
      $order = "tie_schedule, tie_id, mtch_discipline, mtch_order, p2m_result ";
      $res = $this->_select($tables, $fields, $where, $order);
      $rows = array();
      $sc = new utscore();
      $sexe  = array(WBS_MALE => 'M',
      WBS_FEMALE => 'F');
      $catage = array(WBS_CATAGE_POU => 'P',
      WBS_CATAGE_BEN => 'B',
      WBS_CATAGE_MIN => 'M',
      WBS_CATAGE_CAD => 'C',
      WBS_CATAGE_JUN => 'J',
      WBS_CATAGE_SEN => 'S',
      WBS_CATAGE_VET => 'V');
      $discipline = array(WBS_MS => 'SH',
      WBS_WS => 'SD',
      WBS_MD => 'DH',
      WBS_WD => 'DD',
      WBS_MX => 'DM');

      // Get the ranks definitions
      $fields = array('rkdf_id', 'rkdf_label', 'rkdf_point');
      $tables = array('rankdef', 'events');
      $where = 'evnt_id='.utvars::getEventId().
	' AND  evnt_rankSystem=rkdf_system';
      $order  = "rkdf_point";
      $res2 = $this->_select($tables, $fields, $where, $order);
      $ranks=array();
      while ($rank = $res2->fetchRow(DB_FETCHMODE_ORDERED))
      $ranks[$rank[0]] = $rank[1];

      while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
      {
         $sc->setScore($entry["mtch_score"]);
         $entry["mtch_score"] = $sc->getScoreFfba();
         $entry["mber_sexe"] = $sexe[$entry['mber_sexe']];
         $entry["draw_catage"] = $catage[$entry['draw_catage']];
         if (isset($ranks[$entry['draw_rankDefId']]))
         $entry["draw_rankDefId"] = $ranks[$entry['draw_rankDefId']];
         else
         {
            $entry["draw_rankDefId"] = reset($ranks);
         }
         $entry["discipline"] = $discipline[$entry["mtch_discipline"]];
         $entry["mtch_discipline"] = $ut->getSmaLabel($entry["mtch_discipline"]);
         $entry["idMatch"] = $entry["mtch_discipline"].$entry["mtch_order"];
         $date = getdate(@strtotime($entry["tie_schedule"]));
         $entry["tie_schedule"] = sprintf("%2s%02d%02d", substr($date['year'],-2),
         $date['mon'], $date['mday']);

         //$date = getdate(strtotime($entry["mber_born"]));
         //$entry["mber_born"] = $date['year'];
         $entry['mber_born'] = substr($entry['mber_born'], 0, 4);
         if (($entry['rund_type'] == WBS_ROUND_MAINDRAW) ||
         ($entry['rund_type'] == WBS_ROUND_QUALIF))
         {
            if(!$entry['tie_posRound'])
            {
               $entry['tie_posRound'] = 1;
               $entry['tie_stade'] = 'Fi';
            }
            else if($entry['tie_posRound']<3)
            {
               $entry['tie_posRound'] = $entry['tie_posRound'];
               $entry['tie_stade'] = 2;
            }
            else if($entry['tie_posRound']<7)
            {
               $entry['tie_posRound'] -= 2;
               $entry['tie_stade'] = 4;
            }
            else if($entry['tie_posRound']<15)
            {
               $entry['tie_posRound'] -= 6;
               $entry['tie_stade'] = 8;
            }
            else if($entry['tie_posRound']<31)
            {
               $entry['tie_posRound'] -= 14;
               $entry['tie_stade'] = 16;
            }
            else if($entry['tie_posRound']<63)
            {
               $entry['tie_posRound'] -= 30;
               $entry['tie_stade'] = 32;
            }
            else if($entry['tie_posRound']<63)
            {
               $entry['tie_posRound'] -= 62;
               $entry['tie_stade'] = 64;
            }
         }
         else
         {
            $entry['tie_posRound']++;
            $entry['tie_stade'] = 'Po';
         }
         $rows[] = $entry;
      }
      return $rows;
   }
   // }}}

   // {{{ getDateTies
   /**
    * Return the list of the ties with different date
    */
   function getDateTies($begin, $end, $ties, $exportAll)
   {
      $eventId = utvars::getEventId();
      // Information of the ties
      $fields = array("DISTINCT date(tie_schedule) as date",
		      'tie_step', 'draw_stamp', 'rund_stamp', 'tie_id');
      $tables = array('ties', 'rounds', 'draws', 'matchs');
      $where = "draw_eventId = $eventId".
	" AND rund_drawId = draw_id".
	" AND tie_roundId = rund_id".
	" AND mtch_tieId  = tie_id";
      if ($begin != -1 && $end != -1)
      {
         $where .= " AND tie_schedule >= '$begin'".
	    " AND tie_schedule <= '$end'";
      }
      if (count($ties))
      {
         $tiesList = implode(',', $ties);
         $where .= " AND tie_id IN ($tiesList)";
      }
      if ($exportAll)
      $where .= " AND mtch_status >=".WBS_MATCH_CLOSED;
      else
      $where .= " AND mtch_status =".WBS_MATCH_CLOSED;
      $order = "date, draw_stamp, rund_stamp, tie_step";
      $res = $this->_select($tables, $fields, $where, $order);
      $dates = array();
      if ($res->numRows())
      {
      $saveName = '';
      $ties=array();
      $res = $this->_select($tables, $fields, $where, $order);
      while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
      {
         $name = "{$tie['draw_stamp']}_{$tie['rund_stamp']}_{$tie['tie_step']}_{$tie['date']}";
         if ($name != $saveName)
         {
            if (!empty($saveName))
            {
               $saveTie['tie_id'] = $ties;
               $ties = array();
               $dates[] = $saveTie;
            }
            $saveName = $name;
            $saveTie  = $tie;
         }   
         $ties[] = $tie['tie_id'];
      }
      $saveTie['tie_id'] = $ties;
      $dates[] = $saveTie;      
      }
      return $dates;
   }
   //}}}

   // {{{ getScheduledTies
   /**
    * Return the list of tie ordered by schedule
    *
    * @access public
    * @return array   array of match
    */
   function getScheduledTies()
   {
      $eventId = utvars::getEventId();
      // Information of the ties
      $fields = array('tie_id', 'tie_schedule', 'tie_step',
		      'draw_name', 'rund_name');//, 'count(mtch_id) as nbTotal');
      $tables = array('ties', 'rounds', 'draws', 'matchs');
      $where = "draw_eventId = $eventId".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND mtch_tieId = tie_id".
	" AND mtch_del = ".WBS_DATA_UNDELETE.
	" GROUP BY tie_id, tie_step";
      $order = "tie_schedule, tie_step, tie_id ";
      $res = $this->_select($tables, $fields, $where, $order);

      // Number of not played matches for each tie
      $fields = array('tie_id', 'count(mtch_id) as nbNotPlayed');
      $tables = array('ties', 'rounds', 'draws', 'matchs');
      $where = "draw_eventId = $eventId".
	" AND rund_drawId = draw_id".
	" AND tie_roundId = rund_id".
	" AND mtch_tieId = tie_id".
	" AND mtch_del = ".WBS_DATA_UNDELETE.
	" AND mtch_status <".WBS_MATCH_ENDED.
	" GROUP BY tie_id, tie_step";
      $order = "tie_schedule, tie_step, tie_id ";
      $res1 = $this->_select($tables, $fields, $where, $order);
      while ($tie = $res1->fetchRow(DB_FETCHMODE_ASSOC))
      $nbNotPlayed[$tie['tie_id']] = $tie['nbNotPlayed'];

      // Number of ended matches for each tie
      $fields = array('tie_id', 'count(mtch_id) as nbEnded');
      $tables = array('ties', 'rounds', 'draws', 'matchs');
      $where = "draw_eventId = $eventId".
	" AND rund_drawId = draw_id".
	" AND tie_roundId = rund_id".
	" AND mtch_tieId = tie_id".
	" AND mtch_del = ".WBS_DATA_UNDELETE.
	" AND mtch_status =".WBS_MATCH_ENDED.
	" GROUP BY tie_id, tie_step";
      $order = "tie_schedule, tie_step, tie_id ";
      $res1 = $this->_select($tables, $fields, $where, $order);
      while ($tie = $res1->fetchRow(DB_FETCHMODE_ASSOC))
      $nbEnded[$tie['tie_id']] = $tie['nbEnded'];

      // Number of closed matches for each tie
      $fields = array('tie_id', 'count(mtch_id) as nbClosed');
      $tables = array('ties', 'rounds', 'draws', 'matchs');
      $where = "draw_eventId = $eventId".
	" AND rund_drawId = draw_id".
	" AND tie_roundId = rund_id".
	" AND mtch_tieId = tie_id".
	" AND mtch_del = ".WBS_DATA_UNDELETE.
	" AND mtch_status =".WBS_MATCH_CLOSED.
	" GROUP BY tie_id, tie_step";
      $order = "tie_schedule, tie_step, tie_id ";
      $res1 = $this->_select($tables, $fields, $where, $order);
      while ($tie = $res1->fetchRow(DB_FETCHMODE_ASSOC))
      $nbClosed[$tie['tie_id']] = $tie['nbClosed'];

      // Number of send matches for each tie
      $fields = array('tie_id', 'count(mtch_id) as nbSend');
      $tables = array('ties', 'rounds', 'draws', 'matchs');
      $where = "draw_eventId = $eventId".
	" AND rund_drawId = draw_id".
	" AND tie_roundId = rund_id".
	" AND mtch_tieId = tie_id".
	" AND mtch_del = ".WBS_DATA_UNDELETE.
	" AND mtch_status =".WBS_MATCH_SEND.
	" GROUP BY tie_id, tie_step";
      $order = "tie_schedule, tie_step, tie_id ";
      $res1 = $this->_select($tables, $fields, $where, $order);
      while ($tie = $res1->fetchRow(DB_FETCHMODE_ASSOC))
      $nbSend[$tie['tie_id']] = $tie['nbSend'];

      $ties = array();
      $date = new utdate();
      while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
      {
         $date->setIsoDate($tie['tie_schedule']);
         $tie['tie_schedule'] = $date->getDate();
         if (isset($nbNotPlayed[$tie['tie_id']]))
         $tie[] = $nbNotPlayed[$tie['tie_id']];
         else
         $tie[] = '0';
         if (isset($nbEnded[$tie['tie_id']]))
         $tie[] = $nbEnded[$tie['tie_id']];
         else
         $tie[] = '0';
         if (isset($nbClosed[$tie['tie_id']]))
         $tie[] = $nbClosed[$tie['tie_id']];
         else
         $tie[] = '0';
         if (isset($nbSend[$tie['tie_id']]))
         $tie[] = $nbSend[$tie['tie_id']];
         else
         $tie[] = '0';
         //$tie['nbTotal'] = $stats;
         $ties[] = $tie;
      }
      return $ties;
   }
   // }}}

   // {{{ getIndivTies
   /**
    * Return the list of tie ordered by schedule
    *
    * @access public
    * @return array   array of match
    */
   function getIndivTies()
   {
      $eventId = utvars::getEventId();
      // Information of the ties
      $fields = array('tie_id', 'tie_schedule', 'draw_name',
		      'rund_name', 'tie_posRound','mtch_score');
      $tables = array('ties', 'rounds', 'draws', 'matchs');
      $where = "draw_eventId = $eventId".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND mtch_tieId = tie_id".
	" AND tie_isBye = 0".
	" AND mtch_del = ".WBS_DATA_UNDELETE.
	" GROUP BY tie_id, tie_step";
      $order = "draw_id, rund_id, tie_posRound ";
      $res = $this->_select($tables, $fields, $where, $order);

      // Number of send matches for each tie
      $fields = array('tie_id', 'count(mtch_id) as nbSend');
      $tables = array('ties', 'rounds', 'draws', 'matchs');
      $where = "draw_eventId = $eventId".
	" AND rund_drawId = draw_id".
	" AND tie_roundId = rund_id".
	" AND mtch_tieId = tie_id".
	" AND mtch_del = ".WBS_DATA_UNDELETE.
	" AND mtch_status =".WBS_MATCH_SEND.
	" GROUP BY tie_id, tie_step";
      $order = "tie_schedule, tie_step, tie_id ";
      $res1 = $this->_select($tables, $fields, $where, $order);
      while ($tie = $res1->fetchRow(DB_FETCHMODE_ASSOC))
      $nbSend[$tie['tie_id']] = $tie['nbSend'];

      $ties = array();
      $date = new utdate();
      $utr = new utRound();
      $ut  = new utils();
      while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
      {
         $date->setIsoDate($tie['tie_schedule']);
         $tie['tie_schedule'] = $date->getDate();
         $step = $utr->getTieStep($tie['tie_posRound']);
         $tie['tie_posRound'] = $ut->getLabel($step);
         if (isset($nbSend[$tie['tie_id']]))
         $tie[] = $nbSend[$tie['tie_id']];
         else
         $tie[] = '0';
         //$tie['nbTotal'] = $stats;
         $ties[] = $tie;
      }
      return $ties;
   }
   // }}}

   // {{{ getEventDef
   /**
    * Return the event definition
    *
    * @access public
    * @return array   array of match
    */
   function getEventDef()
   {
      $eventId = utvars::getEventId();
      $fields = array('evnt_id', 'evnt_date', 'evnt_level', 'evnt_name',
		      'evnt_nature', 'evnt_zone', 'evnt_numauto', 'evnt_firstday');
      $tables[] = "events";
      $where = "evnt_id = $eventId";

      $res = $this->_select($tables, $fields, $where);

      $event = $res->fetchRow(DB_FETCHMODE_ASSOC);
      switch ($event["evnt_nature"])
      {
         case WBS_NATURE_EQUIP:
         case WBS_NATURE_INTERCODEP:
         case 80:
         case 81:
         case 82:
         case 83:
         	$event["evnt_type"] = "EQUIP";
            break;
         case WBS_NATURE_INDIV:
         case 90:
         	$event["evnt_type"] = "INDIV";
            break;
         case WBS_NATURE_TROPH:
         case 92:
         	$event["evnt_type"] = "TROPH";
            break;
         case WBS_NATURE_TOURN:
            $event["evnt_type"] = "TOURN";
            break;
         default:
            $event["evnt_type"] = "TOURN";
            break;
      }
      switch ($event["evnt_level"])
      {
         case WBS_LEVEL_DEP:
            $event["evnt_level"] = "DEP";
            $event["evnt_longlevel"] = "D�partemental";
            break;
         case WBS_LEVEL_REG:
            $event["evnt_level"] = "REG";
            $event["evnt_longlevel"] = "R�gional";
            break;
         case WBS_LEVEL_NAT:
            $event["evnt_level"] = "NAT";
            $event["evnt_longlevel"] = "National";
            break;
         case WBS_LEVEL_INT:
            $event["evnt_level"] = "MOND";
            $event["evnt_longlevel"] = "International";
            break;
         default:
            $event["evnt_level"] = "NAT";
            $event["evnt_longlevel"] = "National";
            break;
      }
      return $event;
   }
   // }}}


   // {{{ setEventZone
   /**
    * Register the zone of the event
    *
    * @access public
    * @param string  $zone  zone of the event
    * @return array   array of match
    */
   function setEventZone($zone)
   {
      $eventId = utvars::getEventId();
      $fields['evnt_zone'] = $zone;
      $where = "evnt_id = $eventId";
      $res = $this->_update('events', $fields, $where);
      return true;
   }
   // }}}


   // {{{ getInfosUser
   /**
    * Return the column of a user
    *
    * @access public
    * @param  string  $userId   id of the user
    * @return array   information of the user if any
    */
   function getInfosUser()
   {
      $userId = utvars::getUserId();
      $fields = array('user_id', 'user_name', 'user_login', 'user_pass',
		      'user_email', 'user_type', 'user_lang', 'user_cre',
		      'user_lastvisit', 'user_cmt', 'user_nbcnx', 'user_updt');
      $tables[] = 'users';
      $where =  "user_id=$userId";
      $res = $this->_select($tables, $fields, $where);
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
   }
   // }}}

   // {{{ updateStatusMatchs
   /**
    * Return the list of the match according criteria
    *
    * @access public
    * @param string   $begin  first date
    * @param string   $end    end date
    * @return array   array of match
    */
   function updateStatusMatchs($begin, $end, $ties)
   {
      // retieve the id of all transmetted match
      $eventId = utvars::getEventId();
      $fields = array('mtch_id');
      $tables = array('matchs', 'ties', 'rounds', 'draws');
      $where = "mtch_tieId = tie_id".
        " AND draw_eventId = $eventId".
        " AND tie_roundId = rund_id".
        " AND rund_drawId = draw_id".
        " AND mtch_status =".WBS_MATCH_CLOSED;
      if ($begin != -1)
      $where .= " AND tie_schedule >= '$begin'";
      if ($end != -1)
      $where .= " AND tie_schedule <= '$end'";
      if (count($ties))
      {
         $tiesList = implode(',', $ties);
         $where .= " AND tie_id IN ($tiesList)";
      }

      $res = $this->_select($tables, $fields, $where);
      if (!$res->numRows()) return;

      // Concatener les id des matchs
      $res = $this->_select($tables, $fields, $where);
      while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
      $matchsId[] = $match['mtch_id'];
      $ids = implode(',', $matchsId);
       
      // Mettre a jour l'etat des matchs
      $fields  = array();
      $fields['mtch_status'] = WBS_MATCH_SEND;
      $where = "mtch_id in ($ids)";
      $res = $this->_update('matchs', $fields, $where);
      return;
   }
   // }}}


}
?>