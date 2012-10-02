<?PHP
/**
 * example for XML_Parser_Simple
 *
 * @author      Stephan Schmidt <schst@php-tools.net>
 * @package     XML_Parser
 * @subpackage  Examples
 */

require_once "xml_parser_import.php";

class t2rParser extends importParser
{

	/**
	 * Local rounds id
	 *
	 * @var     string
	 * @access  private
	 */
	var $_localRoundIds = array();

	/**
	 * Local Teams ids
	 *
	 * @var     string
	 * @access  private
	 */
	var $_localTeamIds = array();

	/**
	 * Local pair ids
	 *
	 * @var     string
	 * @access  private
	 */
	var $_localPairIds = array();


	/**
	 * handle begin rund element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_rund($xp, $name, $attribs)
	{

	  if (isset($this->_localRoundIds[$attribs['rund_uniId']]))
	  $this->_localRoundId = $this->_localRoundIds[$attribs['rund_uniId']];
	  else
	  {
	  	// Recherche du tour
	  	$utbase = new utbase();
	  	$tables = array('draws', 'rounds');
	  	$where = "draw_eventId={$this->_localEventId}".
		" AND draw_id = rund_drawId".
		" AND rund_uniId = '{$attribs['rund_uniId']}'";
	  	$roundId = $utbase->_selectFirst($tables, 'rund_id', $where);
	  	if (is_null($roundId))
	  	{
	  		$this->log("Local round not found {$attribs['rund_uniId']}");
	  		$this->_localRoundId = -1;
	  		return;
	  	}
	  	$this->_localRoundId = $roundId;
	  	$this->_localRoundIds[$attribs['rund_uniId']] = $this->_localRoundId;
		}
	}

	/**
	 * handle begin t2r element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_t2r($xp, $name, $attribs)
	{
	  unset($attribs['t2r_id']);
	  unset($attribs['t2r_idold']);
	  $this->_updateT2r($attribs);
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
		//print_r($this->_localRoundIds);
	}

	/**
	 * Update the draw
	 *
	 * @access private
	 * @return integer  id of draw
	 */
	function _updateT2r(&$t2r)
	{
		$utbase = new utbase();

		// Recherche de l'equipe
		$localTeamId = $t2r['t2r_teamId'];
		if ($t2r['t2r_teamId'] > 0)
		{
			if (isset($this->_localTeamIds[$t2r['t2r_teamUniId']]))
			{
				$localTeamId = $this->_localTeamIds[$t2r['t2r_teamUniId']];
			   $this->log("parser_t2r: Teamid  $localTeamId \n");
			}
			else
			{
				$where = "team_uniId = '{$t2r['t2r_teamUniId']}'".
					" AND team_eventId = {$this->_localEventId}";
				$localTeamId = $utbase->_selectFirst('teams', 'team_id', $where);
				if (is_null($localTeamId))
				{
					$this->log("parser_t2r: Team unknow {$t2r['t2r_teamUniId']}. Relation cancelled.\n");
					return;
				}
			   $this->log("parser_t2r: Team trouve $localTeamId \n");
				$this->_localTeamId[$t2r['t2r_teamUniId']] = $localTeamId;
			}
			$t2r['t2r_teamId'] = $localTeamId;
		}
		unset($t2r['t2r_teamUniId']);

		// Recherche de la paire
		$localPairId = $t2r['t2r_pairId'];
		if ($t2r['t2r_pairId'] > 0)
		{
			if (isset($this->_localPairIds[$t2r['t2r_pairUniId']]))
			$localPairId = $this->_localPairIds[$t2r['t2r_pairUniId']];
			else
			{
				$tables = array('registration', 'i2p', 'pairs');
				$where = "pair_uniId = '{$t2r['t2r_pairUniId']}'".
						" AND regi_id = i2p_regiId".
						" AND i2p_pairId = pair_id".
						" AND regi_eventId = {$this->_localEventId}";
				$localPairId = $utbase->_selectFirst($tables, 'pair_id', $where);
				if (is_null($localPairId))
				{
					$this->log("parser_t2r: Pair unknow {$t2r['t2r_pairUniId']}. Relation cancelled.\n");
					return;
				}
				$this->_localPairId[$t2r['t2r_pairUniId']] = $localPairId;
			}
			$t2r['t2r_pairId'] = $localPairId;
		}
		unset($t2r['t2r_pairUniId']);
		$t2r['t2r_roundId'] = $this->_localRoundId;

		// Traitement de la relation
		$where = "t2r_roundId = {$this->_localRoundId}".
					" AND t2r_posRound = {$t2r['t2r_posRound']}";
		//	" AND t2r_teamId = {$localTeamId}".
		//	" AND t2r_pairId = {$localPairId}";
		$t2rId = $utbase->_selectFirst('t2r', 't2r_id', $where);

		// Creation de la relation
		if (is_null($t2rId))
		if ($t2r["t2r_cre"] > $this->_dateRef)
		$t2rId = $utbase->_insert('t2r', $t2r);
		else
		{
			$this->log("parser_t2r: Relation out fo date; create={$t2r['t2r_cre']} ref={$this->_dateRef}.\n");
			$t2rId = -1;
		}
		else
		{
			// Le relation est trouvee :mise  a jour
			$where .= " AND t2r_updt <= '{$t2r['t2r_updt']}'";
			$utbase->_update('t2r', $t2r, $where);
		}
		unset($utbase);
		return $t2rId;
	}

}

?>