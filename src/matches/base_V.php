<?php
/*****************************************************************************
!   Module     : Matches
!   File       : $Source: /cvsroot/aotb/badnet/src/matches/base_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
!   Author     : D.BEUVELOT
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
!   Mailto     : didier.beuvelot@free.fr
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
* @author Didier BEUVELOT <didier.beuvelot@free.fr>
* @see to follow
*
*/

class matchBase_V extends utbase
{

  // {{{ properties
  
  /**
   * Adress of the database server
   *
   * @var     string
   * @access  private
   */
  var $_dsn = "localhost";
  
  /**
   * Util object
   *
   * @var     string
   * @access  private
   */
  var $_ut;
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function matchBase_V()
    {
      $this->_ut  = new Utils();
    }

  // {{{ getPair
  /**
   * Return the column of a pair	
   *
   * @access public
   * @param  string  $matchId  id of the match
   * @return array   information of the match if any
   */
  function getPair($pairId)
    {

      $query = "SELECT pair_ibfNum, pair_natRank, pair_intRank, pair_status, pair_id, pair_drawId, pair_cmt";
      $query .= " FROM aotb_pairs ";
      $query .= "WHERE pair_ibfNum = '$pairId'";
      
      $res = $db->query($query);
      $entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $q = "SELECT regi_shortName FROM aotb_i2p, aotb_registration ";
      $q .="WHERE regi_id=i2p_regiId AND i2p_pairId='$pairId'";
      //echo $q;print_r($entry);
      $rq= $db->query($q);
      $nb = $rq->numRows(); 
      if($nb==0)
	{
	  $entry[] = "Joueur 1";
	  $entry[] = "Joueur 2";
	}
      elseif($nb==1){
	$entry[] = "Joueur 1";
      }
      
      while ($er = $rq->fetchRow(DB_FETCHMODE_ORDERED)){
	$entry[] = $er[0];
      }
      
      //print_r($entry);
      $rows[] = $entry;
      print_r($rows);
	       return $rows;
    } 
  // }}}


  // {{{ getMatches
  /**
   * Return the list of the matches
   *
   * @access public
   * @param  string  $sort   criteria to sort users
   * @param  integer $first  first user to retrieve
   * @param  integer $step   number of user to retrieve
   * @return array   array of users
   */
  function getMatches($sort, $first, $step)
    {
      // Count number of matches
      $query = "SELECT COUNT(*) FROM aotb_matchs ";
      //echo $query."<br>";
      $res = $db->query($query);
      $entry = $res->fetchRow(DB_FETCHMODE_ORDERED);
      $rows['nbMax']= ($entry[0]);
      
      // Retrieve matches  
      $query = "SELECT mtch_id, mtch_num, mtch_score ";
      $query .= " FROM aotb_matchs ";
      $query .= " WHERE mtch_del != ".WBS_DATA_DELETE;
      if ($sort > 0)
	$query .= " ORDER BY $sort ";
      else
	$query .= " ORDER BY ". abs($sort) . " desc";
      $query .= " LIMIT $first, $step";

      //echo $query;
      
      $res = $db->query($query);
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $q = "SELECT pair_ibfNum, pair_id FROM aotb_p2m, aotb_pairs ";
	  $q .="WHERE pair_id=p2m_pairId AND p2m_matchId='".$entry['0']."'";
	  $rq= $db->query($q);
	  $nb = $rq->numRows(); 
	  if($nb==0)
	    {
	      $entry[] = "Team 1";
	      $entry[] = "";
	      $entry[] = "Team 2";
	      $entry[] = "";
	    }
	  elseif($nb==1)
	    {
	      $entry[] = "Team 1";
	      $entry[] = "";
	    }
	  
	  while ($er = $rq->fetchRow(DB_FETCHMODE_ORDERED)){
	    //on r�cup�re d'abord pair_ibfNumber puis pair_id
	    $entry[] = $er[0];$entry[] = $er[1];
	  }
	  //print_r($entry);
	  $rows[] = $entry;
        }
      //print_r($rows);
      return $rows;
    }
  // }}}
  

  // {{{ getMatch
  /**
   * Return a match	
   *
   * @access public
   * @param  string  $matchId  id of the match
   * @return array   information of the match if any
   */
  function getMatch($matchId)
    {
      $query = "SELECT mtch_num, mtch_status, mtch_discipline, mtch_score, mtch_cmt,mtch_id";
      $query .= " FROM aotb_matchs ";
      $query .= "WHERE mtch_id = '$matchId'";
      //echo $query;
      
      $res = $db->query($query);
      $entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $q = "SELECT pair_ibfNum FROM aotb_p2m, aotb_pairs ";
      $q .="WHERE pair_id=p2m_pairId AND p2m_matchId='$matchId'";
      $rq= $db->query($q);
      $nb = $rq->numRows();
      if($nb==0){
	$entry[] = "Team 1";
	$entry[] = "Team 2";
      }
      elseif($nb==1){
	$entry[] = "Team 1";
      }
      
      while ($er = $rq->fetchRow(DB_FETCHMODE_ORDERED)){
	$entry[] = $er[0];
			}
      
      //print_r($entry);
      return $entry;
    }
  // }}}
  
  function getFreePair()
    {
      $query = "SELECT pair_id ";
      $query .=" FROM aotb_pairs ";
      $res = $db->query($query);
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
	{
	  $rows[$entry['0']] = $this->getPairName($entry['0']);
				}
      //print_r($rows);
      return $rows;
    } 
  // }}}

  // {{{ getPairName
  /**
   * Return the column of a pair	
   *
   * @access public
   * @param  string  $matchId  id of the match
   * @return array   information of the match if any
   */
  function getPairName($pairId)
    {
      $q = "SELECT regi_shortName FROM aotb_i2p, aotb_registration ";
      $q .="WHERE regi_id=i2p_regiId AND i2p_pairId='$pairId'";
      //echo $q;print_r($entry);
      $rq= $db->query($q);
      $nb = $rq->numRows();
      if($nb==0)
	{
	  $entry = "Joueur 1 - Joueur 2";
	}
      elseif($nb==1)
	{
	  $entry[1] = "Joueur 1 - ";
	}
      
      $er1 = $rq->fetchRow(DB_FETCHMODE_ORDERED);
      $er2 = $rq->fetchRow(DB_FETCHMODE_ORDERED);
      $entry = $er1[0]." - ".$er2[0];
      
      //print_r($entry);
      return $entry;
    }
  // }}}

  // {{{ ibfNumber2Name
  /**
   * Return the column of a pair	
   *
   * @access public
   * @param  string  $matchId  id of the match
   * @return array   information of the match if any
   */
  function ibfNumber2Name($ibfNumber)
    {
      $q = "SELECT regi_shortName FROM aotb_pairs, aotb_i2p, aotb_registration ";
      $q .="WHERE pair_ibfNum='$ibfNumber' AND i2p_pairId=pair_Id AND regi_id=i2p_regiId";
      $rq= $db->query($q);
      $nb = $rq->numRows();
      if($nb==0)
	{
	  $entry = "Joueur 1 - Joueur 2";
	}
      elseif($nb==1)
	{
	  $entry[1] = "Joueur 1 - ";
	}

      $er1 = $rq->fetchRow(DB_FETCHMODE_ORDERED);
      $er2 = $rq->fetchRow(DB_FETCHMODE_ORDERED);
      $entry = $er1[0]." - ".$er2[0];
      
      //print_r($entry);
      return $entry;
    }
  // }}}
}
?>