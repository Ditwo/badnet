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

class itemsParser extends importParser
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
  function  itemsParser()
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
  function xmltag_item($xp, $name, $attribs)
    {
      unset($attribs['item_id']);
      unset($attribs['item_idold']);
      $attribs['item_eventId'] = $this->_localEventId;
      $utbase = $this->_utbase;

      // Is the account already in this database
      $where = "item_uniId = '{$attribs['item_uniId']}'".
	" AND item_eventId = {$this->_localEventId}";
      $itemId = $utbase->_selectFirst('items', 'item_id', $where);

      // Not found, create a new one
      if (is_null($itemId))
	if ($attribs["item_cre"] > $this->_dateRef) $itemId = $utbase->_insert('items', $attribs);	
	else $itemId = -1;
      else
	{
	  // update the account
	  $where .= " AND item_updt <= '{$attribs['item_updt']}'";
	  $utbase->_update('items', $attribs, $where);
	}
      $this->_itemId = $itemId;
    }

  /**
   * handle begin assoc element
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function xmltag_cmd($xp, $name, $cmd)
    {
      unset($cmd['cmd_id']);
      unset($cmd['cmd_idold']);
      $utbase = $this->_utbase;

      // Search registration
      $cmd['cmd_regiId'] = $cmd['cmd_regiUniId'];
      unset($cmd['cmd_regiUniId']);
      if ($cmd['cmd_regiId'] != '')
	{
	  $where = "regi_uniId = '{$cmd['cmd_regiId']}'";
	  $regiId = $utbase->_selectFirst('registration', 'regi_id', $where);

	  if (is_null($regiId))
	    $cmd['cmd_regiId'] = -1;
	  else
	    $cmd['cmd_regiId'] = $regiId;
	}

      // Search account
      $where = "cunt_uniId = '{$cmd['cmd_accountUniId']}'".
	" AND cunt_eventId ={$this->_localEventId}";      
      $accountId = $utbase->_selectFirst('accounts', 'cunt_id', $where);
      if (is_null($accountId))
	return;
      $cmd['cmd_accountId'] = $accountId;
      unset($cmd['cmd_accountUniId']);

      // Initialize item
      $cmd['cmd_itemId'] = $this->_itemId;

      // Search the command 
      $where = "cmd_regiId = '{$cmd['cmd_regiId']}'".
	" AND cmd_itemId = '{$cmd['cmd_itemId']}'".
	" AND cmd_accountId = '{$cmd['cmd_accountId']}'".
	" AND cmd_cre = '{$cmd['cmd_cre']}'";
      $commandId = $utbase->_selectFirst('commands', 'cmd_id', $where);
      if (is_null($commandId))
	if ($cmd["cmd_cre"] > $this->_dateRef)
	  $utbase->_insert('commands', $cmd);
      else
	{
	  $where .= " AND cmd_updt <= '{$cmd['cmd_updt']}'";
	  $utbase->_update('commands', $cmd, $where);
	}
    }

}
?>