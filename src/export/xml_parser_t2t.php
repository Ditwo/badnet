<?PHP
/**
 * example for XML_Parser_Simple
 *
 * @author      Stephan Schmidt <schst@php-tools.net>
 * @package     XML_Parser
 * @subpackage  Examples
 */

require_once "xml_parser_import.php";

class t2tParser extends importParser
{
  
  /**
   * Local tie ids
   *
   * @var     string
   * @access  private
   */
  var $_localTieIds = array();

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
	  // Recherche de la rencontre
	  $utbase = new utbase();
	  $tables = array('draws', 'rounds', 'ties');
	  $where = "draw_eventId={$this->_localEventId}".
	    " AND draw_id = rund_drawId".
	    " AND rund_id = tie_roundId".
	    " AND rund_uniId = '{$attribs['tie_roundUniId']}'".
	    " AND tie_posRound = {$attribs['tie_posRound']}";
	  $this->_localTieId = $utbase->_selectFirst($tables, 'tie_id', $where);
	  unset($utbase);
    }

  /**
   * handle begin t2t element
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function xmltag_t2t($xp, $name, $attribs)
    {
	  if( is_null($this->_localTieId))
	    {
	      $this->log("Tie unknow. Relation cancelled for extern tie {$attribs['t2t_tieId']}\n");
	      return;
	    }
	  // Recherche de l'equipe 
	  $utbase = new utbase();
	  $where = "team_eventId={$this->_localEventId}".
	    " AND team_uniId = '{$attribs['t2t_teamUniId']}'";
	  $localTeamId = $utbase->_selectFirst('teams', 'team_id', $where);
	  if( is_null($localTeamId))
	    {
	      $this->log("parser_t2t:Team unknow {$attribs['t2t_teamUniId']}. Relation cancelled for extern tie {$attribs['t2t_tieId']}\n");
	      return;
	    }	  
	  unset($attribs['t2t_id']);
	  unset($attribs['t2t_idold']);
	  unset($attribs['t2t_teamUniId']);
	  $attribs['t2t_teamId'] = $localTeamId;
	  $attribs['t2t_tieId'] = $this->_localTieId;

	  /* Chercher la relation */
	  $where = "t2t_teamId={$localTeamId}".
	    " AND t2t_tieId = {$this->_localTieId}";
	  $t2tId = $utbase->_selectFirst('t2t', 't2t_id', $where);
	  if (is_null($t2tId))
	    {
	      if ($attribs["t2t_cre"] > $this->_dateRef)
		$utbase->_insert('t2t',$attribs);	  
	      else
		{
		  $this->log("parser_t2t: Relation out fo date; create={$t2t['t2t_cre']} ref={$this->_dateRef}.\n");
		  $i2pId = -1;
		}
	    }
	  else
	    {
	      //$this->log("parser_t2t: updating: $where\n");
	      $where .= " AND t2t_updt <= '{$attribs['t2t_updt']}'";
	      $utbase->_update('t2t',$attribs, $where);
	    }
	  
	  unset($utbase);

    }

}

?>