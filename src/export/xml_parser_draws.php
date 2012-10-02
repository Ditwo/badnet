<?PHP
/**
 * example for XML_Parser_Simple
 *
 * @author      Stephan Schmidt <schst@php-tools.net>
 * @package     XML_Parser
 * @subpackage  Examples
 */

require_once "xml_parser_import.php";

class drawsParser extends importParser
{

	/**
	 * Constructor
	 */
	function  drawsParser($rankDef, $isFirstTime)
	{
		parent::importParser();
		$this->_ranksDef = $rankDef;
		$this->_isFirstTime = $isFirstTime;
	}


	/**
	 * handle begin draw element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_draw($xp, $name, $attribs)
	{
		$this->_localDrawId = -1;
		unset($attribs['draw_id']);
		unset($attribs['draw_idold']);
		unset($attribs['draw_externId']);
		$attribs['draw_eventId'] = $this->_localEventId;
		if (isset($this->_ranksDef[$attribs['draw_ranklabel']]))
		$attribs['draw_rankdefId'] = $this->_ranksDef[$attribs['draw_ranklabel']];
		else
		$attribs['draw_rankdefId'] = reset($this->_ranksDef);
		unset($attribs['draw_ranklabel']);
		$this->_localDrawId = $this->_updateDraw($attribs);
		if ($this->_localDrawId > -1)
		$this->_draws[] = $this->_localDrawId ;
	}

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
		$this->_localRoundId = -1;
		if ($this->_localDrawId == -1) return;

		$roundId = $attribs['rund_id']; 
		unset($attribs['rund_id']);
		unset($attribs['rund_idold']);
		unset($attribs['rund_externId']);
		$attribs['rund_drawId'] = $this->_localDrawId;
		$this->_localRoundId = $this->_updateRound($attribs);
		if ($this->_localRoundId > -1)
		{
			$this->_rounds[$roundId] = $this->_localRoundId ;
		}
	}

	/**
	 * handle begin tie element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_tie($xp, $name, $attribs)
	{
		$this->_localTieId = -1;
		if ($this->_localRoundId == -1) return;

		$tieId = $attribs['tie_id'];
		unset($attribs['tie_id']);
		unset($attribs['tie_idold']);
		$attribs['tie_roundId'] = $this->_localRoundId;
		$this->_localTieId = $this->_updateTie($attribs);
		 
		if ($this->_localTieId > -1)
		{
			$this->_ties[] = $this->_localTieId;
			if ($attribs['tie_looserdrawid'] > 0)
			{
				$tie['tieId'] = $this->_localTieId;
				$tie['looserId'] = $attribs['tie_looserdrawid'];
				$this->_looserTie[] = $tie;
			}
		}
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
		$this->_localMatchId = -1;
		if ($this->_localTieId == -1) return;

		unset($attribs['mtch_id']);
		unset($attribs['mtch_idold']);
		unset($attribs['mtch_externId']);
		$attribs['mtch_tieId'] = $this->_localTieId;
		$localMatchId =   $this->_updateMatch($attribs);
		if ($localMatchId > -1) $this->_matchs[] = $localMatchId ;
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
		// To do : supprimer les enregistrementsqui ne sont plus
		// dans le fichier d'import et qui non pas ete cree
		// depuis...?
		//$this->_draws[];
		//$this->_rounds[];
		//$this->_ties[];
		//$this->_matchs[];

	}

	/**
	 * handle end draw element
	 *
	 * @access   private
	 * @param    resource    xml parser resource
	 * @param    string      name of the element
	 * @param    array       attributes
	 */
	function xmltag_draw_($xp, $name)
	{
		$utbase = new utbase();
		foreach ($this->_looserTie as $tie)
		{
			$col['tie_looserdrawid'] = $this->_rounds[$tie['looserId']];
			$where = 'tie_id = ' . $tie['tieId'];
			$utbase->_update('ties', $col, $where);
		}
		unset($utbase);
	}

	/**
	 * Update the draw
	 *
	 * @access private
	 * @return integer  id of draw
	 */
	function _updateDraw(&$draw)
	{
		$utbase = new utbase();

		// Recherche du tableau
		$where = "draw_uniId = '{$draw['draw_uniId']}'".
	  " AND draw_eventId = {$this->_localEventId}";
		$drawId = $utbase->_selectFirst('draws', 'draw_id', $where);

		// Tableau non trouvee. Creation d'un nouveau tableau uniquement
		// si la date de creation du tablea importee est posterieure
		// a la date de premiere exportation
		if (is_null($drawId))
		{
	  if ($draw["draw_cre"] > $this->_dateRef)
	  $drawId = $utbase->_insert('draws', $draw);
	  else
	  {
	  	$drawId = -1;
	  	$this->log("parser_draws: Draw out fo date; create={$draw['draw_cre']} ref={$this->_dateRef}.\n");
	  }
		}
		else
		{
	  // Le tableau est trouvee :mise  a jour
	  $where .= " AND draw_updt <= '{$draw['draw_updt']}'";
	  $utbase->_update('draws', $draw, $where);
		}
		unset($utbase);
		return $drawId;
	}

	/**
	 * Update the round
	 *
	 * @access private
	 * @return integer  id of round
	 */
	function _updateRound(&$round)
	{
		$utbase = new utbase();

		if (empty($round['rund_rge']))
		{
			if ($round['rund_type'] == WBS_ROUND_MAINDRAW) $round['rund_rge'] = 1;
			else
			{
				$round['rund_rge'] = intval($round['rund_name']);
				$round['rund_name'] = 'Plateau ' . $round['rund_name'];
			}
		}
		// Recherche du tableau
		$where = "rund_uniId = '{$round['rund_uniId']}'".
	  " AND rund_drawId = {$this->_localDrawId}";
		$roundId = $utbase->_selectFirst('rounds', 'rund_id', $where);

		// Tour non trouvee. Creation d'un nouveau tour uniquement
		// si la date de creation du tableau importee est posterieure
		// a la date de premiere exportation
		if (is_null($roundId))
		{
	  if ($round["rund_cre"] > $this->_dateRef)
	  $roundId = $utbase->_insert('rounds', $round);
	  else
	  {
	  	$roundId = -1;
	  	$this->log("parser_draws: Round out fo date; create={$round['rund_cre']} ref={$this->_dateRef}.\n");
	  }
		}
		else
		{
	  // Le tour est trouvee :mise  a jour
	  $where .= " AND rund_updt <= '{$round['rund_updt']}'";
	  $utbase->_update('rounds', $round, $where);
		}
		unset($utbase);
		return $roundId;
	}

	/**
	 * Update the tie
	 *
	 * @access private
	 * @return integer  id of tie
	 */
	function _updateTie(&$tie)
	{
		$utbase = new utbase();

		// Recherche du tableau
		$where = "tie_roundId = {$this->_localRoundId}".
	  " AND tie_posRound = {$tie['tie_posRound']}";
		$tieId = $utbase->_selectFirst('ties', 'tie_id', $where);

		// Rencontre non trouvee. Creation d'une nouvelle rencontre uniquement
		// si la date de creation de la rencontre importee est posterieure
		// a la date de premiere exportation
		if (is_null($tieId))
		{
	  if ($tie["tie_cre"] > $this->_dateRef)
	  $tieId = $utbase->_insert('ties', $tie);
	  else
	  {
	  	$tieId = -1;
	  	$this->log("parser_draws: Tie out fo date; create={$tie['tie_cre']} ref={$this->_dateRef}.\n");
	  }
		}
		else
		{
	  // La rencontre  est trouvee :mise  a jour
	  $where .= " AND tie_updt <= '{$tie['tie_updt']}'";
	  $utbase->_update('ties', $tie, $where);
		}
		unset($utbase);
		return $tieId;
	}


	/**
	 * Update the match
	 *
	 * @access private
	 * @return integer   id of the match
	 */
	function _updateMatch(&$match)
	{
		$utbase = new utbase();
		// Search the match
		$where = "mtch_tieId=".$match['mtch_tieId'].
	" AND mtch_discipline=".$match['mtch_discipline'].
	" AND mtch_order=".$match['mtch_order'];
		$matchId = $utbase->_selectFirst('matchs', 'mtch_id', $where);

		switch($match['mtch_discipline'])
		{
			case WBS_MS:
			case WBS_WS:
			case WBS_AS:
				$match['mtch_disci'] = WBS_SINGLE;
				break;
			case WBS_XD:
				$match['mtch_disci'] = WBS_MIXED;
				break;
			default :
				$match['mtch_disci'] = WBS_DOUBLE;
				break;
		}
		// Les matchs importes et termines sont a valider
		// si ce n'est pas le premiere importation
		//if (!$this->_isFirstTime &&
		//$match['mtch_status'] > WBS_MATCH_ENDED)
		//$match['mtch_status'] = WBS_MATCH_ENDED;

		// Not found, create a new one
		if (is_null($matchId))
		{
	  if ($match['mtch_cre'] > $this->_dateRef)
	  $matchId  = $utbase->_insert('matchs', $match);
	  else
	  {
	  	$this->log("parser_draws: Match out fo date; create={$match['mtch_cre']} ref={$this->_dateRef}.\n");
	  	$matchId  = -1;
	  }
		}
		// update it
		else
		{
	  // Les matchs valides ou en cours dans la base
	  // ne sont pas modifies
	  //$where .= " AND mtch_updt <= '{$match['mtch_updt']}'".
	  $where .= " AND mtch_status < ".WBS_MATCH_CLOSED.
	    " AND mtch_status != ".WBS_MATCH_LIVE;
	  $res = $utbase->_update('matchs', $match, $where);
		}
		unset($utbase);
		return $matchId;
	}

}

?>