<?php
/*****************************************************************************
!   Module     : utils
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utmatch.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.10 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/27 22:41:48 $
!   Mailto     : cage@free.fr
******************************************************************************/
require_once "utbase.php";


/**
* Acces to the dababase for matches
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class utMatch extends utbase
{

  // {{{ properties
  // }}}

  function __destruct() 
  {
  }

  // {{{ getMatch
  /**
   * Return the match id of an an individual tie
   *
   * @access public
   * @return mixed
   */
  function getMatch($mtchId)
    {
      // Search  the match
      $fields = array('mtch_id');
      $tables = array('matchs');
      $where = "mtch_tieId=".$tieId;
      $res = $this->_select($tables, $fields, $where);
      $match = $res->fetchRow(DB_FETCHMODE_ASSOC);

      echo"<hr>xx $tieId";print_r($match);echo"<hr>";

      return $match;
    }
  // }}}


  // {{{ getMatchIndiv
  /**
   * Return the match id of an an individual tie
   *
   * @access public
   * @param  string  $info   column of the match
   * @return mixed
   */
  function getMatchIndiv($tieId)
    {
      // Search  the match
      $fields = array('mtch_id');
      $tables = array('matchs');
      $where = "mtch_tieId=".$tieId;
      $res = $this->_select($tables, $fields, $where);
      $match = $res->fetchRow(DB_FETCHMODE_ASSOC);

      echo"<hr>xx $tieId";print_r($match);echo"<hr>";

      return $match;
    }
  // }}}


  // {{{ properties
  // }}}

  // {{{ playMatch
  /**
   * Update the status of a match
   *
   * @access public
   * @param  string  $info   column of the match
   * @return mixed
   */
  function playMatch($infos)
    {
      // Search  the match
      $fields = array('mtch_status', 'mtch_court', 'mtch_umpireId',
		      'mtch_serviceId', 'tie_place');
      $tables = array('matchs', 'ties');
      $where = "mtch_id=".$infos['mtch_id'].
	" AND tie_id=mtch_tieId";
      $res = $this->_select($tables, $fields, $where);
      $match = $res->fetchRow(DB_FETCHMODE_ASSOC);
      if ($match['mtch_status'] == $infos['mtch_status'])
	{
	  unset($infos['mtch_begin']);
	}

      // Updating the match
      $where = "mtch_id=".$infos['mtch_id'];
      $res = $this->_update('matchs', $infos, $where);

      //Updating the status of umpires
      $this->_updateStatusUmpire($match, $infos);

      //Updating the status of all players and matches
      $place = addslashes($match['tie_place']);
      $this->updateStatusMatch($place);

      return true;
    }
  // }}}

  // {{{ _updateStatusUmpire
  /**
   * Update the status of the umpire
   *
   * @access private
   * @return mixed
   */
  function _updateStatusUmpire($oldData, $newData)
    {
      if(isset($newData['mtch_umpireId']))
	{
	  if ($oldData['mtch_umpireId'] != -1 &&
	      $newData['mtch_umpireId'] != $oldData['mtch_umpireId'] )
	    $this->_popUmpire($oldData['mtch_umpireId']);

	  if($newData['mtch_umpireId'] != -1)
	    $this->_pushUmpire($newData['mtch_umpireId'], WBS_UMPIRE_UMPIRE);

	}
      if(isset($newData['mtch_serviceId']))
	{
	  if ($oldData['mtch_serviceId'] != -1 &&
	      $newData['mtch_serviceId'] != $oldData['mtch_serviceId'] )
	    $this->_popUmpire($oldData['mtch_serviceId']);

	  if($newData['mtch_serviceId'] != -1)
	    $this->_pushUmpire($newData['mtch_serviceId'], WBS_UMPIRE_SERVICE);
	}
    }
  // }}}

  // {{{ _pushUmpire
  /**
   * Update the status of the umpire
   *
   * @access private
   * @return mixed
   */
  function _pushUmpire($regiId, $function)
    {
      // Select the umpire
      $fields = array('umpi_id', 'umpi_court', 'umpi_function');
      $tables = array('umpire');
      $where = "umpi_regiId = $regiId";
      $res = $this->_select($tables, $fields, $where);
      if (!$res->numRows())
	return;

      $umpire = $res->fetchRow(DB_FETCHMODE_ASSOC);

      // Get all umpires of the groups
      if ($umpire['umpi_court'] != '')
	{
	  $fields = array('umpi_id', 'umpi_function', 'umpi_order');
	  $tables = array('umpire');
	  $where = "umpi_court =".$umpire['umpi_court'].
	    " AND umpi_regiId != $regiId";
	  $order = "umpi_function, umpi_order";
	  $res = $this->_select($tables, $fields, $where, $order);
	  $umpires = array();
	  $services = array();
	  while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
	    {
	      if ($data['umpi_function'] == WBS_UMPIRE_UMPIRE)
		$umpires[] = $data;
	      else if ($data['umpi_function'] == WBS_UMPIRE_SERVICE)
		$services[] = $data;
	    }
	  // Update the umpire status
	  if ($function == WBS_UMPIRE_UMPIRE)
	    {
	      $umpire['umpi_function'] = WBS_UMPIRE_SERVICE;
	      $umpire['umpi_order'] = count($services)+1;
	    }
	  else
	    {
	      $umpire['umpi_function'] = WBS_UMPIRE_UMPIRE;
	      $umpire['umpi_order'] = count($umpires)+1;
	    }
	  $where = "umpi_regiId = $regiId";
	  $res = $this->_update('umpire', $umpire, $where);

	  // Update the order of umpires and services judge
	  $this->_orderedOfficials($umpires, $services);
	}
      return true;
    }
  //}}}

  // {{{ _popUmpire
  /**
   * Update the status of the umpire
   *
   * @access private
   * @return mixed
   */
  function _popUmpire($regiId)
    {
      // Select the umpire
      $fields = array('umpi_id', 'umpi_court', 'umpi_function');
      $tables = array('umpire');
      $where = "umpi_regiId = $regiId";
      $res = $this->_select($tables, $fields, $where);
      if (!$res->numRows())
	return;
      $umpire = $res->fetchRow(DB_FETCHMODE_ASSOC);

      // Update the umpire status
      if ($umpire['umpi_court'] != '')
	{
	  if ($umpire['umpi_function'] == WBS_UMPIRE_UMPIRE)
	    $umpire['umpi_function'] = WBS_UMPIRE_SERVICE;
	  else
	    $umpire['umpi_function'] = WBS_UMPIRE_UMPIRE;
	  $umpire['umpi_order'] = 0;
	  $where = "umpi_regiId = $regiId";
	  $res = $this->_update('umpire', $umpire, $where);

	  // Get all umpires of the groups
	  $fields = array('umpi_id', 'umpi_function', 'umpi_order');
	  $tables = array('umpire');
	  $where = "umpi_court =".$umpire['umpi_court'];
	  $order = "umpi_function, umpi_order";
	  $res = $this->_select($tables, $fields, $where, $order);
	  $umpires = array();
	  $services = array();
	  while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
	    {
	      if ($data['umpi_function'] == WBS_UMPIRE_UMPIRE)
		$umpires[] = $data;
	      else if ($data['umpi_function'] == WBS_UMPIRE_SERVICE)
		$services[] = $data;
	    }
	  // Update the order of umpires and services judge
	  $this->_orderedOfficials($umpires, $services);
	}
      return true;
    }
  //}}}

  // {{{ _orderedOfficials
  /**
   * Update the order of the officials
   *
   * @access private
   * @return mixed
   */
  function _orderedOfficials($umpires, $services)
    {
      // Update the umpire order
      $order = 1;
      foreach($umpires as $umpire)
	{
	  $column['umpi_order'] = $order++;
	  $where = "umpi_id=".$umpire['umpi_id'];
	  $res = $this->_update('umpire', $column, $where);
	}

      // Update the service judge order
      $order = 1;
      foreach($services as $service)
	{
	  $column['umpi_order'] = $order++;
	  $where = "umpi_id =". $service['umpi_id'];
	  $res = $this->_update('umpire', $column, $where);
	}

      return true;
    }
  // }}}


  // {{{ updateStatusMatch
  /**
   * Update the status of all the match of the competition
   *
   * @access private
   * @return mixed
   */
  function updateStatusMatch($place = null)
    {
      // Updating the matches : first all busy and rest matchs
      // are setting to ready
      $fields = array('mtch_id');
      $tables = array('matchs', 'ties', 'rounds', 'draws');
      $where = " mtch_tieId=tie_id".
	" AND (mtch_status =".WBS_MATCH_BUSY.
	" OR mtch_status =".WBS_MATCH_REST.
	" ) AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId =".utvars::getEventId();
      $res = $this->_select($tables, $fields, $where);
      $matchs = array();
      while ( $match = $res->fetchRow(DB_FETCHMODE_ORDERED))
	$matchs[]= $match[0];

      if (count($matchs))
	{
	  $fields = array();
	  $fields['mtch_status'] = WBS_MATCH_READY;
	  $where = " mtch_id in (".implode(',', $matchs).")";
	  $res = $this->_update('matchs', $fields, $where);
	}

      // Updating the players : all court number are set to zero
      $fields = array();
      $fields['regi_court'] = 0;
      $where = " regi_eventId =".utvars::getEventId();
      $res = $this->_update('registration', $fields, $where);

      // Now, look at the new status of match
      // Select all in live match
      $fields = array('mtch_id', 'mtch_court', 'mtch_umpireId', 'mtch_serviceId');
      $tables = array('matchs', 'ties', 'rounds', 'draws');
      $where = " mtch_tieId=tie_id".
	" AND mtch_status =".WBS_MATCH_LIVE.
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId =".utvars::getEventId();
      $res = $this->_select($tables, $fields, $where);

      $regis = array();
      while ( $match = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  // Select the players of the match
	  $fields = array('i2p_regiId');
	  $tables = array('matchs', 'p2m', 'i2p');
	  $where = "i2p_pairId = p2m_pairId".
	    " AND p2m_matchId = mtch_id".
	    " AND mtch_id =". $match['mtch_id'];
	  $res1 = $this->_select($tables, $fields, $where);
	  $regism = array();
	  while ( $regi = $res1->fetchRow(DB_FETCHMODE_ORDERED))
	    {
	      $regism[] = $regi[0];
	      $regis[] = $regi[0];
	    }

	  // Update the players' court number
	  if (count($regism))
	    {
	      $fields = array();
	      $fields['regi_court'] = $match['mtch_court'];
	      $where = "regi_id in (". implode(',', $regism) .")";
	      $res1 = $this->_update('registration', $fields, $where);
	    }
	  // Update the umpire court number
	  $fields = array();
	  $fields['regi_court'] = -$match['mtch_court'];
	  $where = "regi_id =".$match['mtch_umpireId'].
	      " OR regi_id =".$match['mtch_serviceId'];
	  $res1 = $this->_update('registration', $fields, $where);
	}
      if (!count($regis)) return;

      // Select all completed and not ended matchs of the
      // oncourt players, in the same sporthall
      $fields = array('mtch_id');
      $tables = array('matchs', 'p2m', 'i2p', 'ties');
      $where = "i2p_pairId = p2m_pairId".
	" AND p2m_matchId = mtch_id".
	" AND mtch_tieId = tie_id".
	" AND mtch_status > ".WBS_MATCH_INCOMPLETE.
	" AND mtch_status < ".WBS_MATCH_LIVE.
	" AND i2p_regiId in (". implode(',', $regis) .")";
      if(!is_null($place))
	$where .= " AND tie_place = '$place'";

      $res = $this->_select($tables, $fields, $where);

      if (!$res->numRows()) return;
      $matchs = array();
      while ( $match = $res->fetchRow(DB_FETCHMODE_ORDERED))
	$matchs[]= $match[0];

      // All this match are busy
      $fields = array();
      $fields['mtch_status'] = WBS_MATCH_BUSY;
      $where = " mtch_id in (". implode(',', $matchs) .")";
      $res = $this->_update('matchs', $fields, $where);
      return true;
    }
  // }}}

  // {{{ updateRestTime
  /**
   * Update the rest time of the players of the macth
   *
   * @access private
   * @param integer $matchId Id of the match
   * @param sring   $time    Hour of the ned of the match
   * @param sring   $court   Number of the court
   * @return mixed
   */
  function updateRestTime($matchId, $time)
    {
      // Select all players of the match
      $fields[] = 'i2p_regiId';
      $tables = array('matchs', 'p2m', 'i2p');
      $where = "i2p_pairId = p2m_pairId".
	" AND p2m_matchId = mtch_id".
	" AND mtch_id = $matchId";
      $res = $this->_select($tables, $fields, $where);
      if (!$res->numRows()) return;

      while ( $regi = $res->fetchRow(DB_FETCHMODE_ORDERED))
	$regis[]= $regi[0];

      $fields =  array('regi_rest' => $time);
      $where = "regi_id in (". implode(',', $regis) .")".
	" AND  (regi_rest < '".$fields['regi_rest']."' or regi_rest IS NULL)";
      $res = $this->_update('registration', $fields, $where);
      return true;
    }
  // }}}

}
?>
