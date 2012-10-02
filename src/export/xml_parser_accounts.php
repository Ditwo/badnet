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

class accountsParser extends importParser
{
  
  
  /**
   * Constructors
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function  accountsParser()
    {
      parent::importParser();
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
  function xmltag_cunt($xp, $name, $attribs)
    {
      unset($attribs['cunt_id']);
      unset($attribs['cunt_idold']);
      $attribs['cunt_eventId'] = $this->_localEventId;
      $utbase = $this->_utbase;

      // Is the account already in this database
      $where = "cunt_uniId = '{$attribs['cunt_uniId']}'".
	" AND cunt_eventId ={$this->_localEventId}";
      $accountId = $utbase->_selectFirst('accounts', 'cunt_id', $where);

      // Not found, create a new one
      if (is_null($accountId))
	  if ($attribs["cunt_cre"] > $this->_dateRef) $accountId = $utbase->_insert('accounts', $attribs);	
	  else $accountId =-1;
      else
	{
	  // update the account
	  $where .= " AND cunt_updt <= '{$attribs['cunt_updt']}'";
	  $utbase->_update('accounts', $attribs, $where);
	}
      return $accountId;
    }

}

class accountsRelParser extends importParser
{
  
  
  /**
   * Constructors
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function  accountsRelParser()
    {
      parent::importParser();
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
  function xmltag_cunt($xp, $name, $attribs)
    {
      unset($attribs['cunt_id']);
      unset($attribs['cunt_idold']);
      $utbase = $this->_utbase;

      // Find account id
      $where = "cunt_uniId = '{$attribs['cunt_uniId']}'".
	" AND cunt_eventId ={$this->_localEventId}";
      $accountId = $utbase->_selectFirst('accounts', 'cunt_id', $where);

      // Not found, create a new one
      if (is_null($accountId)) $this->_accountId = -1;
      else $this->_accountId = $accountId;
    }

  /**
   * handle begin assoc element
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
      $utbase = $this->_utbase;

      // Find account id
      $where = "regi_uniId = '{$attribs['regi_uniId']}'".
	" AND regi_eventId ={$this->_localEventId}";
      $fields = array('regi_accountId' => $this->_accountId);
      $utbase->_update('registration', $fields, $where);
    }

  /**
   * handle begin assoc element
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function xmltag_team($xp, $name, $attribs)
    {
      unset($attribs['team_id']);
      unset($attribs['team_idold']);
      $utbase = $this->_utbase;

      // Find account id
      $where = "team_uniId = '{$attribs['team_uniId']}'".
	" AND team_eventId ={$this->_localEventId}";
      $fields = array('team_accountId' => $this->_accountId);
      $utbase->_update('teams', $fields, $where);
    }

}

?>