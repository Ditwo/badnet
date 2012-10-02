<?PHP
/**
 * example for XML_Parser_Simple
 *
 * @author      Stephan Schmidt <schst@php-tools.net>
 * @package     XML_Parser
 * @subpackage  Examples
 */

require_once "xml_parser_import.php";

class pairsParser extends importParser
{
  
  /**
   * Local regi id
   *
   * @var     string
   * @access  private
   */
  var $_localRegiId = array();

  /**
   * Local draw id
   *
   * @var     string
   * @access  private
   */
  var $_localDrawId = array();

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
      $this->log("parser_pairs: date={$this->_dateLimit}.\n");
	  unset($attribs['pair_id']);
	  unset($attribs['pair_idold']);
	  unset($attribs['pair_externId']);
	  $this->_localPairId = $this->_updatePair($attribs);
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
	  unset($attribs['i2p_idold']);
	  $this->_updateI2p($attribs);
    }


  // {{{ _updatePair
  /**
   * Update the draw
   *
   * @access private
   * @return integer  id of draw
   */
  function _updatePair(&$pair)
    {
      $utbase = new utbase();

      // Recherche du tableau de la paire
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
	}
      $pair['pair_drawId'] = $drawId;
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
	  if ($pair["pair_cre"] > $this->_dateRef)
	    $pairId = $utbase->_insert('pairs', $pair);
	  else
	    {
	      $this->log("parser_pair: Pair out fo date; create={$pair['pair_cre']} ref={$this->_dateRef}.\n");
	      $pairId = -1;
	    }
	}
      else
	{
	  // La paire est trouvee :mise  a jour
	  $where = "pair_id = $pairId".
	    " AND pair_updt <= '{$pair['pair_updt']}'";
	  $utbase->_update('pairs', $pair, $where);
	}
      unset($utbase);
      return $pairId;
    }
  // }}}

  // {{{ _updateI2p
  /**
   * Update the draw
   *
   * @access private
   * @return integer  id of draw
   */
  function _updateI2p(&$i2p)
    {
      $utbase = new utbase();

      // Recherche de l'equipe
      if (isset($this->_localRegiId[$i2p['i2p_regiUniId']]))
	$localRegiId = $this->_localRegiId[$i2p['i2p_regiUniId']];
      else
	{
	  $where = "regi_uniId = '{$i2p['i2p_regiUniId']}'".
	    " AND regi_eventId = {$this->_localEventId}";
	  $localRegiId = $utbase->_selectFirst('registration', 'regi_id', $where);
	  if (is_null($localRegiId))
	    {
	      $this->log("parser_pairs: Entrie unknow. Relation cancelled between entrie and pair.\n");
	      $this->log("  localEventId ={$this->_localEventId};externId=$externId;\n");
	      return;
	    }
	  $this->_localRegiId[$i2p['i2p_regiUniId']] = $localRegiId;
	}

      unset($i2p['i2p_regiUniId']);
      $i2p['i2p_pairId'] = $this->_localPairId;
      $i2p['i2p_regiId'] = $localRegiId;
      // Recherche de la relation
      $where = "i2p_regiId = $localRegiId".
	" AND i2p_pairId = $this->_localPairId";
      $i2pId = $utbase->_selectFirst('i2p', 'i2p_id', $where);
      
      // Creation de la relation
      // si sa date de creation importee est posterieure
      // a la date de premiere exportation
      if (is_null($i2pId))
	{
	  if ($i2p["i2p_cre"] > $this->_dateRef)
	    $i2pId = $utbase->_insert('i2p', $i2p);
	  else
	    {
	      $this->log("parser_pair: Relation out fo date; create={$i2p['i2p_cre']} ref={$this->_dateRef}.\n");
	      $i2pId = -1;
	    }
	}
      else
	{
	  // Le relation est trouvee :mise  a jour
	  $where .= " AND i2p_updt <= '{$i2p['i2p_updt']}'";
	  $utbase->_update('i2p', $i2p, $where);
	}
      unset($utbase);
      return $i2pId;
    }
  // }}}


}

?>