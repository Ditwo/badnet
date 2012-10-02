<?PHP
/**
 * example for XML_Parser_Simple
 *
 * @author      Stephan Schmidt <schst@php-tools.net>
 * @package     XML_Parser
 * @subpackage  Examples
 */

/**
 * require the parser
 */
require_once "xml_parser_import.php";

class teamsParser extends importParser
{

	// {{{ properties

	/**
	 * Constructors
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function  teamsParser($rankDef)
	{
		parent::importParser();
		$this->_ranksDef = $rankDef;
		$this->_utbase = new utbase();
	}

	/**
	 * handle begin assoc element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_asso($xp, $name, $attribs)
	{
	  unset($attribs['asso_id']);
	  unset($attribs['asso_idold']);
	  unset($attribs['asso_externId']);
	  $this->_localAssoId = $this->_updateAssoc($attribs);
	}

	/**
	 * handle begin team element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_team($xp, $name, $attribs)
	{
	  //$msg= "{$attribs['team_stamp']}:{$attribs['team_id']}";
	  //$this->log($msg);
	  unset($attribs['team_id']);
	  unset($attribs['team_idold']);
	  unset($attribs['team_externId']);
	  unset($attribs['team_assoId']);
	  $attribs['team_eventId'] = $this->_localEventId;
	  $this->_localTeamId = $this->_updateTeam($attribs);
	  if ($this->_localTeamId > -1)
	  {
	  	$this->_updateRelation($this->_localAssoId, $this->_localTeamId);
	  	$this->_teamsIds[] = $this->_localTeamId;
	  }
	}


	/**
	 * handle begin mber element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_mber($xp, $name, $attribs)
	{
		unset($attribs['mber_id']);
		unset($attribs['mber_idold']);
		$this->_localMemberId = $this->_updateMember($attribs);
	}

	/**
	 * handle begin regi element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_regi($xp, $name, $attribs)
	{
		unset($attribs['regi_id']);
		unset($attribs['regi_idold']);
		$attribs['regi_eventId']  = $this->_localEventId;
		$attribs['regi_teamId']  = $this->_localTeamId;
		$attribs['regi_memberId'] = $this->_localMemberId;
		$this->_localRegiId = $this->_updateRegi($attribs);
	}

	/**
	 * handle begin rank element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_rank($xp, $name, $attribs)
	{
		if ($this->_localRegiId > -1)
		{
	  $attribs['rank_rankdefid'] = $this->_ranksDef[$attribs['rank_label']];
	  $attribs['rank_regiId'] = $this->_localRegiId;
	  unset($attribs['rank_label']);
	  unset($attribs['rank_id']);
	  unset($attribs['rank_idold']);
	  $this->_updateRank($attribs);
		}
	}

	/**
	 * handle begin pair element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_pair($xp, $name, $attribs)
	{
		if ($attribs['pair_uniId'] == -1)
		{
			$eventId = utvars::getEventId();
			$attribs['pair_uniId'] = $eventId . ':' . $attribs['pair_id'] . ';';
		}
		unset($attribs['pair_id']);
		unset($attribs['pair_idold']);
		
		$this->_localPairId = $this->_updatePair($attribs);
	}

	/**
	 * handle end pair element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_pair_($xp, $name)
	{
		$this->_updateI2p($this->_i2p);
	}

	/**
	 * handle begin i2p element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_i2p($xp, $name, $attribs)
	{
		unset($attribs['i2p_id']);
		$this->_i2p = $attribs;
	}


	/**
	 * handle end event element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_event_($xp, $name)
	{
		// To do : supprimer les equipes qui ne sont plus
		// dans le fichier d'import et qui non pas ete cree
		// depuis...?
		//$this->_teamsIds[];

	}

	/**
	 * Update one assoc in the database
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function _updateAssoc(&$data)
	{
		$utbase = $this->_utbase;
		unset($data['asso_logo']);
		
		// Is the association already in this database
		$where = "asso_uniId = '{$data['asso_uniId']}'";
		$assoId = $utbase->_selectFirst('assocs', 'asso_id', $where);
		$assoId = null;
		
		// Not found, we 'll search assos with the same name
		if (is_null($assoId))
		{
	  $name = addslashes($data['asso_name']);
	  $where = "asso_name = '$name'";
	  $where .= " AND asso_type =" . $data['asso_type'];
	  $assoId = $utbase->_selectFirst('assocs', 'asso_id', $where);
	  // Not found, create a new assoc
	  if (is_null($assoId)) $assoId = $utbase->_insert('assocs', $data);
	  else
	  {
	  	// update the association
	  	$where .= " AND asso_updt <= '{$data['asso_updt']}'";
	  	$utbase->_update('assocs', $data, $where);
	  }
		}
		else
		{
	  // update the association
	  $where .= " AND asso_updt <= '{$data['asso_updt']}'";
	  $utbase->_update('assocs', $data, $where);
		}
		return $assoId;
	}

	/**
	 * Update one team in the database
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function _updateTeam(&$data)
	{
		unset($data['team_logo']);
		unset($data['team_photo']);
		$utbase = $this->_utbase;
		// Recherche de l'equipe
		$where = "team_uniId = '{$data['team_uniId']}'".
	  " AND team_eventId = {$this->_localEventId}";
		$teamId = $utbase->_selectFirst('teams', 'team_id', $where);

		// Equipe non trouvee. Creation d'une nouvelle equipe uniquement
		// si la date de creation de l'equipe importee est posterieure
		// a la date de premiere exportation
		if (is_null($teamId))
		{
	  //if ($data["team_cre"] > $this->_dateRef)
			$teamId = $utbase->_insert('teams', $data);
			//else
			// $teamId = -1;
		}
		else
		{
	  // L'equipe est trouvee :mise  a jour
	  $where .= " AND team_updt <= '{$data['team_updt']}'";
	  $utbase->_update('teams', $data, $where);
		}
		return $teamId;
	}


	/**
	 * Find the local reg for an import reg
	 *
	 * @access public
	 * @param string   $begin  first date
	 * @param string   $end    end date
	 * @return array   array of match
	 */
	function _updateRelation($assoId, $teamId)
	{
		$utbase = $this->_utbase;
		$where = "a2t_assoId = $assoId".
	" AND a2t_teamId = $teamId";

		$columns = array("{a2t_id");
		$a2tId = $utbase->_selectFirst('a2t', 'a2t_id', $where);
		if (is_null($a2tId))
		{
	  $fields['a2t_assoId'] = $assoId;
	  $fields['a2t_teamId'] = $teamId;
	  $a2tId = $utbase->_insert('a2t', $fields);
		}
		return $a2tId;
	}

	/**
	 * Update a member  in the database
	 *
	 * @access private
	 * @return integer   id of member
	 */
	function _updateMember(&$member)
	{
		$utbase =& $this->_utbase;
		// The member is already in this database
		$where = "mber_uniId = '{$member['mber_uniId']}'";
		$memberId = $utbase->_selectFirst('members', 'mber_id', $where);
		// Not found, we 'll search member with the same name
		if (is_null($memberId))
		{
			$sname = addslashes($member['mber_secondname']);
			$fname = addslashes($member['mber_firstname']);
			$where = "mber_secondname = '$sname'".
    			" AND mber_firstname = '$fname'".
    			" AND mber_sexe =".$member['mber_sexe'];
			$memberId = $utbase->_selectfirst('members', 'mber_id', $where);
			if (is_null($memberId)) $memberId = $utbase->_insert('members', $member);
			else
			{
				$where .= " AND mber_updt <= '{$member['mber_updt']}'";
				$utbase->_update('members', $member, $where);
			}
		}
		else
		{
			$where = "mber_id = '$memberId'";
			$licence = $utbase->_selectfirst('members', 'mber_licence', $where);
			if ($licence != $member['mber_licence']) $memberId = $utbase->_insert('members', $member);
			else
			// Update the member only if most recently modified
			$where .= " AND mber_updt <= '{$member['mber_updt']}'";
			$utbase->_update('members', $member, $where);
		}
		return $memberId;
	}

	/**
	 * Update the registration
	 *
	 * @access private
	 * @return integer:  id of regi
	 */
	function _updateRegi(&$regi)
	{
		if (isset($regi['regi_longName']) )	$longName = $regi['regi_longName'];
		else $longName = $regi['regi_longname'];
		
		$utbase = $this->_utbase;
		// Recherche de l'equipe
		$teamUniId = $regi['regi_teamUniId'];
		unset($regi['regi_teamUniId']);
		$where = "team_uniId = '$teamUniId'".
	             " AND team_eventId = {$this->_localEventId}";
		$regi['regi_teamId'] = $utbase->_selectFirst('teams', 'team_id', $where);
		if (is_null($regi['regi_teamId']))
		{
			echo "Equipe non trouvee pour $longName\n";
			unset($utbase);
			return -1;
		}

		// Recherche de l'inscription
		$where = "regi_uniId = '{$regi['regi_uniId']}'".
	             " AND regi_eventId = {$this->_localEventId}";
		$regiId = $utbase->_selectFirst('registration', 'regi_id', $where);
		
		// Recherche a partir du nom prenom licence equipe
		if (is_null($regiId))
		{
			$where = 'regi_teamId =' . $regi['regi_teamId'].
	             " AND  regi_longName = '" . addSlashes($longName) . "'".
			" ANd regi_type=" . $regi['regi_type'];
			$regiId = $utbase->_selectFirst('registration', 'regi_id', $where);
		}
		
		// Inscription non trouvee. Creation d'une nouvelle inscription uniquement
		// si la date de creation de l'inscritpion importee est posterieure
		// a la date de premiere exportation
		if (is_null($regiId))
		{
			//$regiId = $utbase->_insert('registration', $regi);
			 if ($regi["regi_cre"] > $this->_dateRef)
			 $regiId = $utbase->_insert('registration', $regi);
			 else
			 {
			 $this->log("parser_teams: Registration out fo date; create={$regi['regi_cre']} ref={$this->_dateRef}.\n");
			 $regiId = -1;
			 }
		}
		else
		{
			// L'equipe est trouvee :mise  a jour
			$where .= " AND regi_updt <= '{$regi['regi_updt']}'";
			$utbase->_update('registration', $regi, $where);
		}
		return $regiId;
	}


	/**
	 * Update one rank
	 *
	 * @access private
	 * @return integer id of relation
	 */
	function _updateRank(&$rank)
	{
		$utbase =& $this->_utbase;
      switch($rank['rank_disci'])
      {
      	case WBS_MS:
      	case WBS_WS:
      	case WBS_AS:
      		$rank['rank_discipline'] = WBS_SINGLE; 
      		break;
      	case WBS_XD:
      		$rank['rank_discipline'] = WBS_MIXED;
      		break;
      	default :
      		$rank['rank_discipline'] = WBS_DOUBLE;
      		break; 
      }
  
		// Search the rank
		$where = "rank_regiId = {$rank['rank_regiId']}".
	" AND rank_disci = ".$rank['rank_disci'];
		$rankId = $utbase->_selectFirst('ranks', 'rank_id', $where);
		if (is_null($rankId))
		$rankId = $utbase->_insert('ranks', $rank);
		else
		{
	  $where .= " AND rank_updt <= '{$rank['rank_updt']}'";
	  $utbase->_update('ranks', $rank, $where);
		}
		return $rankId;
	}

	/**
	 * Update the draw
	 *
	 * @access private
	 * @return integer  id of draw
	 */
	function _updatePair(&$pair)
	{
		$utbase = $this->_utbase;

		// Recherche du tableau de la paire
		$drawId = $pair['pair_drawId'];
		if ($pair['pair_drawId'] != -1)
		{
			if (isset($this->_localDrawId[$pair['pair_drawUniId']]))
			$drawId = $this->_localDrawId[$pair['pair_drawUniId']];
			else
			{
				$where = "draw_uniId = '{$pair['pair_drawUniId']}'".
" AND draw_eventId = {$this->_localEventId}";
				$drawId = $utbase->_selectFirst('draws', 'draw_id', $where);
				if (is_null($drawId))
				$drawId = -1;
				$this->_localDrawId[$pair['pair_drawUniId']] = $drawId;
			}
			$pair['pair_drawId'] = $drawId;
		}
		unset($pair['pair_drawUniId']);

		// Recherche de la paire
		$tables = array('registration', 'i2p', 'pairs');
		$where = "pair_uniId = '{$pair['pair_uniId']}'".
	" AND regi_eventId = {$this->_localEventId}".
	" AND regi_id = i2p_regiId".
	" AND i2p_pairId = pair_id";
		$pairId = $utbase->_selectFirst($tables, 'pair_id', $where);

		// Mise a jour de la paire
		// si la date de creation de la paire importee est posterieure
		// a la date de premiere exportation
		if (is_null($pairId))
		{
			$pairId = $utbase->_insert('pairs', $pair);
			/*
			 if ($pair["pair_cre"] > $this->_dateRef)
			 $pairId = $utbase->_insert('pairs', $pair);
			 else
			 {
				$this->log("parser_teams: Pair out fo date; create={$pair['pair_cre']} ref={$this->_dateRef}.\n");
				$pairId = -1;
				}
				*/
		}
		else
		{
			// La paire est trouvee :mise  a jour
			$where = "pair_id = $pairId".
    " AND pair_updt <= '{$pair['pair_updt']}'";
			$utbase->_update('pairs', $pair, $where);
		}
		return $pairId;
	}

	/**
	 * Update the draw
	 *
	 * @access private
	 * @return integer  id of draw
	 */
	function _updateI2p(&$i2p)
	{
		if ($this->_localPairId == -1 || $this->_localRegiId == -1)
		return -1;

		$utbase = $this->_utbase;

		unset($i2p['i2p_regiUniId']);
		$i2p['i2p_pairId'] = $this->_localPairId;
		$i2p['i2p_regiId'] = $this->_localRegiId;
		// Recherche de la relation
		$where = "i2p_regiId = {$this->_localRegiId}".
	" AND i2p_pairId = {$this->_localPairId}";
		$i2pId = $utbase->_selectFirst('i2p', 'i2p_id', $where);

		// Creation de la relation
		// si sa date de creation importee est posterieure
		// a la date de premiere exportation
		if (is_null($i2pId))
		{
	  		//if ($i2p["i2p_cre"] > $this->_dateRef)
	  		$i2pId = $utbase->_insert('i2p', $i2p);
	  		/*
	   		else
	   		{
	  			$this->log("parser_teams: Relation out fo date; create={$i2p['i2p_cre']} ref={$this->_dateRef}.\n");
	  			$i2pId = -1;
	  		}*/
		}
		else
		{
	  		// Le relation est trouvee :mise  a jour
	  		//$where .= " AND i2p_updt <= '{$i2p['i2p_updt']}'";
	  		$utbase->_update('i2p', $i2p, $where);
		}
		return $i2pId;
	}

}
?>