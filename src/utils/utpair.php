<?php
/*****************************************************************************
!   Module     : utils
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utpair.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.4 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/03/06 18:02:11 $
******************************************************************************/
class utPair extends utBase
{

  // {{{ properties
  // }}}

  // {{{ implode
  /**
   * Compose a new pair with the players
   *
   * @access public
   * @param  string  $info   column of the pair
   * @return mixed
   */
  function implode($pairId, $pairId2, $state=WBS_PAIRSTATE_REG)
    {
      // Select the first pairs
      $fields = array('i2p_id', 'i2p_pairId', 'mber_sexe', 'pair_disci',
		      'pair_drawId');
      $tables = array('i2p', 'registration', 'members', 'pairs');
      $where = "i2p_pairId = $pairId".
	" AND i2p_regiId = regi_id".
	" AND i2p_pairId = pair_id".
	" AND regi_memberId=mber_id";
      $res = $this->_select($tables, $fields, $where);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = "msgNoPlayers";
	  return $infos;
	}
      $pair1 = $res->fetchRow(DB_FETCHMODE_ASSOC);

      $where = "i2p_pairId = $pairId2".
	" AND i2p_regiId = regi_id".
	" AND i2p_pairId = pair_id".
	" AND regi_memberId=mber_id";
      $res = $this->_select($tables, $fields, $where);
      if (!$res->numRows())
	{
	  $infos['errMsg'] = 'msgNoPlayers';
	  return $infos;
	}
      $pair2 = $res->fetchRow(DB_FETCHMODE_ASSOC);

      // Control the gender
      if ( ($pair1['mber_sexe'] == $pair2['mber_sexe'] && $pair2['pair_disci'] == WBS_MIXED))
	{
	  $infos['errMsg'] = 'msgSameGender';
	  return $infos;
	} 
      // Control the draws
      if (  $pair1['pair_drawId']  != $pair2['pair_drawId'])
	{
	  $infos['errMsg'] = 'msgDiffDraw';
	  return $infos;
	} 
      $this->addPlayerToPair($pairId, $pair2['i2p_id']);
      $fields = array();
      $fields['pair_state'] = $state;
      $where = "pair_id = $pairId";
      $res = $this->_update('pairs', $fields, $where);
      return $pairId;
    }
  // }}}

  // {{{ explode
  /**
   * Separe les joueurs d'une paire. Chaque  joueur se retrouve
   * dans une nouvelle paire. S'il est renseigne, regiId indique 
   * le joueur qui doit etre converse dans la paire d'origine
   *
   * @access public
   * @param  string  $info   column of the pair
   * @return mixed
   */
  function explode($pairs, $regiId=false)
    {
      if (is_array($pairs))
	$pairIds = $pairs;
      else
	$pairIds[] = $pairs;

      foreach ($pairIds as $pairId)
	{
	  // Select the players of the pair
	  $fields = array('i2p_id');
	  $tables = array('i2p');
	  $where = "i2p_pairId=$pairId";
	  if ($regiId)
	    $where .= " AND i2p_regiId != $regiId";
	  $res = $this->_select($tables, $fields, $where);

	  // Control the number of players
	  if (!$res->numRows()) continue;

	  // Remove the player
	  $buf = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  return $this->removePlayerFromPair($pairId, $buf['i2p_id']);
	}
    }
  // }}}

  // {{{ removePlayerFromPair
  /**
   * Enleve un joueur d'une paire et le place dans une nouvelle paire
   *
   * @access public
   * @param  string  $matchId  id of the match
   * @return array   information of the match if any
   */
  function removePlayerFromPair($pairId, $i2pId)
    {
      // Retrieve the information of the pair
      $fields = array('pair_drawId', 'pair_disci');
      $tables = array('pairs');
      $where = "pair_id = $pairId";
      $res = $this->_select($tables, $fields, $where);
      $pair = $res->fetchRow(DB_FETCHMODE_ASSOC);

      // Mettre a jour l'etat de l'ancienne paire
      $pair['pair_state']  = WBS_PAIRSTATE_NOK;
      $this->_update('pairs', $pair, $where);

      // Create a new pair for the player
      $pair['pair_status'] = WBS_PAIR_NONE;
      $newPairId = $this->_insert('pairs', $pair);

      // Change the relation with the pair of the selected player
      $fields = array();
      $fields['i2p_pairId'] = $newPairId;
      $where = "i2p_id = $i2pId";
      $this->_update('i2p', $fields, $where);

      return $newPairId;
    }
  // }}}


  // {{{ addPlayerToPair
  /**
   * Return the column of the players in a draw	
   *
   * @access public
   * @param  string  $matchId  id of the match
   * @return array   information of the match if any
   */
  function addPlayerToPair($pairId, $i2pId)
    {
      // Retrieve the pairId of the selected player
      $where = "i2p_id = $i2pId";
      $oldPairId = $this->_selectFirst('i2p', 'i2p_pairId', $where);

      // Change the pair of the selected players
      $fields = array();
      $fields['i2p_pairId'] = $pairId;
      $where = "i2p_id = $i2pId";
      $this->_update('i2p', $fields, $where);

      // If there is no more players for the original pair, delete it
      $fields = array('pair_id');
      $tables = array('pairs', 'i2p');
      $where = "pair_id = $oldPairId".
	" AND pair_id = i2p_pairId";
      $res = $this->_select($tables, $fields, $where);
      if (!$res->numRows())
	{
	  $where = "pair_id = $oldPairId";
          $res = $this->_delete('pairs', $where);

	  $where = "p2m_pairId = $oldPairId";
          $res = $this->_delete('p2m', $where);

	  $where = "t2r_pairId = $oldPairId";
          $res = $this->_delete('t2r', $where);
	}

      return $pairId;
    }
  // }}}


  //-----------------------------------------
  //  Here are private methode
  //------------------------------------------

}

?>
