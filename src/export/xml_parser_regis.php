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

class regisParser extends importParser
{
  
  
  /**
   * Constructors
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function  regisParser($rankDef)
    {
      parent::importParser();
      $this->_ranksDef = $rankDef;
      $this->_utbase = new utbase();
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
	  unset($attribs['mber_externId']);
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
	  unset($attribs['regi_externId']);
	  $attribs['regi_eventId']  = $this->_localEventId;
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
   * handle end event element
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function xmltag_event_($xp, $name)
    {
      // To do : supprimer les inscriptions qui ne sont plus
      // dans le fichier d'import et qui non pas ete cree 
      // depuis...?
      //$this->_regis[];
    
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
	  if (is_null($memberId))
	    $memberId = $utbase->_insert('members', $member);
	  else
	    {
	      $where .= " AND mber_updt <= '{$member['mber_updt']}'";
	      $utbase->_update('members', $member, $where);
	    }
	}
      else
	{
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
      $utbase = $this->_utbase;

      // Recherche de l'equipe 
      $teamUniId = $regi['regi_teamUniId'];
      unset($regi['regi_teamUniId']);
      $where = "team_uniId = '$teamUniId'".
	" AND team_eventId = {$this->_localEventId}";
      $regi['regi_teamId'] = $utbase->_selectFirst('teams', 'team_id', $where);
      if (is_null($regi['regi_teamId']))
	{
	  echo "Equipe non trouvee pour {$regi['regi_longName']}\n";
	  unset($utbase);
	  return -1;
	}

      // Recherche de l'inscription 
      $where = "regi_uniId = '{$regi['regi_uniId']}'".
	  " AND regi_eventId = {$this->_localEventId}";
      $regiId = $utbase->_selectFirst('registration', 'regi_id', $where);

      // Inscription non trouvee. Creation d'une nouvelle inscription uniquement
      // si la date de creation de l'inscritpion importee est posterieure
      // a la date de premiere exportation
      if (is_null($regiId))
	{
	  if ($regi["regi_cre"] > $this->_dateRef)
	    $regiId = $utbase->_insert('registration', $regi);
	  else
	    {
	      $this->log("parser_regis: Registration out fo date; create={$regi['regi_cre']} ref={$this->_dateRef}.\n");
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


}

?>