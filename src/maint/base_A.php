<?php
/*****************************************************************************
 !   Module    : Preferences
 !   Version   : v0.0.1
 !   Author    : G.CANTEGRIL
 !   Co-author :
 !   Mailto    : cage@free.fr
 !   Date      : 28-12-2003
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

require_once "utils/utround.php";

/**
 * Acces to the dababase for preferences
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class maintBase_A extends utBase
{

   // {{{ properties

   /**
    * Util object
    *
    * @var     string
    * @access  private
    */
   var $_ut;


   // {{{ mergeContact
   /**
   * Retrieve the list of subscription to results
   *
   * @access public
   * @return array
   */
   function mergeContact()
   {
      // Toutes rencontres
      $fields = array('ctac_id', 'ctac_associd', 'ctac_value');
      $tables = array('contacts');
      $order = 'ctac_associd, ctac_value';
      $res = $this->_select($tables, $fields, null, $order);
      $email = -1;
      $assoId = -1;
      while($contact = $res->fetchRow(DB_FETCHMODE_ASSOC))
      {
         if ( $contact['ctac_associd'] != $email ||
         $contact['ctac_value']   != $assoId)
         {
            $email = $contact['ctac_associd'];
            $assoId = $contact['ctac_value'];
         }
         else
         {
            $this->_delete('contacts', 'ctac_id=' . $contact['ctac_id']);
         }
      }
   }
   // }}}


   // {{{ updateStepTie
   /**
    * Retrieve the list of subscription to results
    *
    * @access public
    * @return array
    */
   function updateStepTie()
   {
      // Toutes rencontres
      $fields = array('tie_id', 'rund_type', 'rund_size',
		      'tie_posRound');
      $tables = array('ties', 'rounds');
      $where = "tie_roundId = rund_id".
	" AND rund_type !=". WBS_TEAM_GROUP.
	" AND rund_type !=". WBS_TEAM_BACK.
	" AND rund_type !=". WBS_TEAM_KO;       
      $res = $this->_select($tables, $fields, $where);
      $utr = new utround();
      while($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
      {

         $cols['tie_step'] = $utr->_getTieStep($tie['tie_posRound'],
         $tie['rund_type'],
         $tie['rund_size']);

         $where = "tie_id = {$tie['tie_id']}";
         $this->_update('ties', $cols, $where);

      }

   }
   // }}}

   // {{{ getSubscriptions
   /**
    * Retrieve the list of subscription to results
    *
    * @access public
    * @return array
    */
   function getSubscriptions($sort)
   {
      $db = $this->_db;

      // Retreive all registered players
      $query = "SELECT subs_id, user_name, evnt_name,  team_name";
      $query .= " FROM aotb_events, aotb_users, aotb_subscriptions, aotb_teams";
      $query .= " WHERE subs_eventId = evnt_id";
      $query .= " AND subs_userId = user_id";
      $query .= " AND subs_subId = team_id";
      $query .= " AND subs_type = ".WBS_SUBS_TEAM;
      $query .= " ORDER BY ";
      if ($sort < 0)
      $query .= abs($sort)." desc";
      else
      $query .= $sort;

      $res = $db->query($query);
      $subs = array();
      while ($sub = $res->fetchRow(DB_FETCHMODE_ORDERED))
      {
         $subs[]=$sub;
      }
      return $subs;

   }
   // }}}


   // {{{ updatePosT2T
   /**
    * Update long name of players
    *
    * @access public
    * @return array
    */
   function updatePosT2T()
   {
      $db = $this->_db;

      // Retreive all registered players
      $query = "SELECT t2t_id, t2t_posTie";
      $query .= " FROM aotb_t2t ";
      $query .= " ORDER BY t2t_tieId,t2t_posTie";

      $res = $db->query($query);
      $pos = WBS_TEAM_RECEIVER;
      while ($t2t = $res->fetchRow(DB_FETCHMODE_ORDERED))
      {
         $query = "UPDATE aotb_t2t SET ";
         $query .= " t2t_posTie = $pos";
         $query .= " WHERE t2t_id={$t2t[0]}";
         $gres = $db->query($query);
         $pos = 1 - $pos + (2*WBS_TEAM_RECEIVER);
      }
      return true;

   }
   // }}}


   // {{{ getEvents
   /**
    * Return the list of the events
    *
    * @access public
    * @return array   array of users
    */
   function getEvents()
   {
      $ut = new Utils();
      $db = $this->_db;
      // Retrieve all the tournament
      $query = "SELECT evnt_id, evnt_name ";
      $query .= " FROM aotb_events";
      $query .= " WHERE evnt_del !=". WBS_DATA_DELETE;
      $query .= " ORDER BY 2 ";
      $res = $db->query($query);

      while ($event = $res->fetchRow(DB_FETCHMODE_ORDERED))
      {
         $events[$event[0]] = $event[1];
      }
      return $events;
   }
   // }}}




   // {{{ updateLongName
   /**
    * Update long name of players
    *
    * @access public
    * @param interger $eventId Id of the event
    * @return array
    */
   function updateLongName($eventId)
   {
      $db = $this->_db;

      // Retreive all registered players
      $query = "SELECT mber_id, mber_firstname, mber_secondname";
      $query .= ", mber_licence, regi_id";
      $query .= " FROM aotb_members, aotb_registration";
      $query .= " WHERE mber_del !=".WBS_DATA_DELETE;
      $query .= " AND mber_id = regi_memberId";
      $query .= " AND regi_eventId = $eventId";
      $query .= " ORDER BY 1 ";

      $res = $db->query($query);
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
      {
         $shortName = $entry[2]." ".substr($entry[1],0,1).".";
         $longName = $entry[2]." ".$entry[1];
         //if ($entry[3] != "")
         //  $longName .= "&nbsp;(".$entry[3].")";
         $query = "UPDATE aotb_registration SET ";
         $query .= " regi_longName =". $db->quote($longName);
         $query .= ",regi_shortName =". $db->quote($shortName);
         $query .= " WHERE regi_id=".$entry[4];

         //echo "query $query<br>";
         $gres = $db->query($query);
      }
      return true;

   }
   // }}}

   // {{{ createRank
   /**
    * Create rank for registered players
    *
    * @access public
    * @param  integer $eventId   Event concerned
    * @return
    */
   function createRank($eventId)
   {
      $db = $this->_db;
      // Select NC ranking
      $query = "SELECT rkdf_id";
      $query .= " FROM aotb_rankdef";
      $query .= " WHERE rkdf_label = 'NC'";
      $res = $db->query($query);
      $tres = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $rank = $tres[0];

      // Retreive all rank of registered players
      $query = "SELECT regi_id, rank_disci, rank_id, mber_sexe";
      $query .= " FROM ( aotb_registration LEFT JOIN aotb_ranks";
      $query .= " ON regi_id = rank_regiId), aotb_members";
      $query .= " WHERE regi_eventId = $eventId";
      $query .= " AND regi_memberId = mber_id";
      $query .= " ORDER BY 1,2";

      $regis = $db->query($query);
      while ($regi = $regis->fetchRow(DB_FETCHMODE_ORDERED))
      {
         $disci ="";
         if ($regi[1] == WBS_SINGLE)
         $disci = $regi[3]==WBS_MALE ? WBS_MS:WBS_LS;

         elseif ($regi[1] == WBS_DOUBLE)
         $disci = $regi[3]==WBS_MALE ? WBS_MD:WBS_LD;

         else if ($regi[1] == WBS_MIXED)
         $disci = WBS_MX;
         else
         $disci = $regi[1];

         if ($disci != "")
         {
            $rankId = $regi[2];
            $q = "UPDATE aotb_ranks SET";
            $q .= " rank_disci = $disci";
            $q .= ", rank_discipline =" .  $regi[1];
            $q .= " WHERE rank_id=$rankId";
            $res = $db->query($q);

         }

         if ($regi[1] == "")
         {
            $regiId = $regi[0];
            $disci = $regi[3]==WBS_MALE ? WBS_MS:WBS_LS;
            $rankId = $db->nextId("rank_id");
            $q = "INSERT INTO aotb_ranks (rank_id, rank_rankdefId";
            $q .= ", rank_disci, rank_regiId, rank_cre, rank_discipline)";
            $q .= "VALUES ($rankId, $rank, $disci,";
            $q .= "$regiId, NOW(), 110)";
            $res = $db->query($q);

            $disci = $regi[3]==WBS_MALE ? WBS_MD:WBS_LD;
            $rankId = $db->nextId("rank_id");
            $q = "INSERT INTO aotb_ranks (rank_id, rank_rankdefId";
            $q .= ", rank_disci, rank_regiId, rank_cre, rank_discipline)";
            $q .= "VALUES ($rankId, $rank, $disci,";
            $q .= "$regiId, NOW(), 111)";
            $res = $db->query($q);

            $disci = WBS_MX;
            $rankId = $db->nextId("rank_id");
            $q = "INSERT INTO aotb_ranks (rank_id, rank_rankdefId";
            $q .= ", rank_disci, rank_regiId, rank_cre, rank_discipline)";
            $q .= "VALUES ($rankId, $rank, $disci,";
            $q .= "$regiId, NOW(), 112)";
            $res = $db->query($q);
         }
      }
      return true;

   }
   // }}}

   // {{{ getRegiRanks
   /**
    * Retrieve the ranking of player for the selected event
    *
    * @access public
    * @param  integer $eventId   Event concerned
    * @param  integer $assocId   Association concerned
    * @param  integer $sort      Column to sort
    * @return
    */
   function getRegiRanks($eventId, $assocId, $sort)
   {
      $db = $this->_db;
      // Retrieve all rank of registered players
      $query = "SELECT rank_id, mber_sexe, regi_longName, mber_born";
      $query .= " , mber_licence, mber_ibfnumber";
      $query .= " , rank_disci, rkdf_label, rank_average, team_stamp, mber_id";
      $query .= " FROM aotb_registration,  aotb_ranks";
      $query .= " , aotb_members, aotb_teams, aotb_rankdef";
      $query .= " , aotb_a2t";
      $query .= " WHERE mber_id = regi_memberId";
      $query .= " AND regi_id = rank_regiId";
      $query .= " AND team_id = regi_teamId";
      $query .= " AND regi_eventId = $eventId";
      $query .= " AND rank_rankdefId = rkdf_id";
      $query .= " AND team_id = a2t_teamId";
      $query .= " AND a2t_assoId = $assocId";
      $query .= " ORDER BY ";
      if ($sort < 0)
      $query .= abs($sort)." desc";
      else
      $query .= $sort;
      $query .= ",rank_disci";

      $regis = $db->query($query);
      $rows = array();
      $date = new utdate();
      while ($regi = $regis->fetchRow(DB_FETCHMODE_ORDERED))
      {
         $regi[6] = $this->_ut->getLabel($regi[6]);
         $regi[1] = $this->_ut->getSmaLabel($regi[1]);
         $date->setIsoDate($regi[3]);
         $regi[3] = $date->getDate();
         $rows[]=$regi;
      }
      return $rows;

   }
   // }}}

   // {{{ getAssos
   /**
    * Retrieve all associations
    *
    * @access public
    * @return
    */
   function getAssos()
   {
      // Retrieve all associations
      $fields = array('asso_id', 'asso_name');
      $tables = array('assocs');
      $order  = 'asso_name';
      $res = $this->_select($tables, $fields, false, $order);
      $assos = array();
      while ($asso = $res->fetchRow(DB_FETCHMODE_ASSOC))
      $assos[$asso['asso_id']]=$asso['asso_name'];
      return $assos;
   }
   // }}}

   // {{{ getMembers
   /**
    * Retrieve all members
    *
    * @access public
    * @return
    */
   function getMembers($alpha)
   {
      // Retrieve all associations
      $fields = array('mber_id', 'mber_firstname', 'mber_secondname');
      $tables = array('members');
      $where = "mber_secondname like '$alpha%'".
	" AND mber_id > 0";
      $order  = 'mber_secondname, mber_firstname';
      $res = $this->_select($tables, $fields, $where, $order);
      $mbers = array();
      while ($mber = $res->fetchRow(DB_FETCHMODE_ASSOC))
      $mbers[$mber['mber_id']]="{$mber['mber_secondname']} {$mber['mber_firstname']}";
      return $mbers;
   }
   // }}}

   // {{{ getAssocStamp
   /**
    * Return the stamp of and association
    *
    * @access public
    * @param  integer $assocId   Id of association
    * @return
    */
   function getAssocStamp($assocId)
   {
      $db = $this->_db;
      // Retrieve all associations registered
      $query = "SELECT asso_stamp, asso_dpt";
      $query .= " FROM aotb_assocs";
      $query .= " WHERE asso_id = $assocId";
      $assocs = $db->query($query);
      $assoc = $assocs->fetchRow(DB_FETCHMODE_ORDERED);
      return $assoc[0];
   }
   // }}}

   // {{{ deleteRanks
   /**
    * Delete ranking of players
    *
    * @access public
    * @param  array  $rankIds   Is's of the ranking to be delete
    * @return
    */
   function deleteRanks($rankIds)
   {
      $db = $this->_db;
      foreach ( $rankIds as $rank => $id)
      {
         $query = "DELETE FROM aotb_ranks ";
         $query .= "WHERE  rank_id = '$id' ";
         $res = $db->query($query);
      }

      return true;

   }
   // }}}



}
?>