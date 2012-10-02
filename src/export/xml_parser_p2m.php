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

class p2mParser extends importParser
{

	// {{{ properties
	/**
	 * Local pair id
	 *
	 * @var     string
	 * @access  private
	 */
	var $_localPairId = array();

	// }}}

	/**
	 * Constructor
	 */
	function  p2mParser($isFirstTime)
	{
		parent::importParser();
		$this->_isFirstTime = $isFirstTime;
	}


	/**
	 * handle begin mtch element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_mtch($xp, $name, $attribs)
	{

		unset($attribs['mtch_idold']);
		$utbase = new utbase();
		// Search the match
		$fields = array('mtch_id', 'mtch_status');
		$tables = array('draws', 'rounds', 'ties', 'matchs');
		$where = "mtch_uniId='{$attribs['mtch_uniId']}'".
	    " AND mtch_tieId=tie_id".
	    " AND tie_roundId=rund_id".
	    " AND rund_drawId = draw_id".
	    " AND draw_eventId = {$this->_localEventId}";
		$match = $utbase->_selectFirst($tables, $fields, $where);
		// Not found, error
		if (is_null($match))
		{
			$this->_localMatchId = -1;
			$this->log("parser_p2m: Match unknow {$attribs['mtch_uniId']}. Relation cancelled.\n");
			return;
		}
		$this->_localMatchId = $match['mtch_id'];
		unset($utbase);
	}

	/**
	 * handle begin p2m element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_p2m($xp, $name, $attribs)
	{
		if ($attribs['p2m_id'] == '') return;

		if ($this->_localMatchId == -1) return;
		unset($attribs['p2m_id']);
		unset($attribs['p2m_idold']);
		$this->_updateP2m($attribs);
	}

	/**
	 * Update the relation
	 *
	 * @access private
	 * @return integer  id of relation
	 */
	function _updateP2m(&$p2m)
	{
		$utbase = new utbase();

		if ($p2m['p2m_pairUniId'] == -1)
		{
			$eventId = utvars::getEventId();
			$p2m['p2m_pairUniId'] = $eventId . ':' . $p2m['p2m_pairId'] . ';';
		}
		
		// Recherche de la paire
		if (isset($this->_localPairIds[$p2m['p2m_pairUniId']]))
		$localPairId = $this->_localPairIds[$p2m['p2m_pairUniId']];
		else
		{
			$tables = array('registration', 'i2p', 'pairs');
			$where = "pair_uniId = '{$p2m['p2m_pairUniId']}'".
	    			" AND regi_id = i2p_regiId".
	   			 	" AND i2p_pairId = pair_id".
	    			" AND regi_eventId = {$this->_localEventId}";
			$localPairId = $utbase->_selectFirst($tables, 'pair_id', $where);
			if (is_null($localPairId))
			{
				$this->log("parser_p2m: pair unknow {$p2m['p2m_pairUniId']}. Relation cancelled.\n");
				return;
			}
			$this->_localPairId[$p2m['p2m_pairUniId']] = $localPairId;
		}
		$p2m['p2m_pairId']  = $localPairId;
		$p2m['p2m_matchId'] = $this->_localMatchId;
		unset($p2m['p2m_pairUniId']);

		// Traitement de la relation
		$where = "p2m_matchId = {$this->_localMatchId}".
				" AND p2m_posmatch = {$p2m['p2m_posmatch']}";
		$p2mId = $utbase->_selectFirst('p2m', 'p2m_id', $where);
		// Creation de la relation
		// si sa date de creation importee est posterieure
		// a la date de reference.
		if (is_null($p2mId))
		{
			$p2mId = $utbase->_insert('p2m', $p2m);
			/*
			 if ($p2m["p2m_cre"] > $this->_dateRef)
			 $p2mId = $utbase->_insert('p2m', $p2m);
			 else
			 {
			 $this->log("parser_p2m: Relation out fo date; create={$p2m['p2m_cre']} ref={$this->_dateRef}.\n");
			 $p2mId = -1;
			 }
			 */
		}
		else
		{
			// Le relation est trouvee :mise  a jour
			$where .= " AND p2m_updt <= '{$p2m['p2m_updt']}'";
			$utbase->_update('p2m', $p2m, $where);
		}
		unset($utbase);
		return $p2mId;
	}
}

?>