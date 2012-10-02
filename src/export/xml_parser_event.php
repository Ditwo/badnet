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

class eventParser extends importParser
{
  
  /**
   * Data of the event
   *
   * @var     object
   * @access  private
   */
  var $_events = NULL;

  
  /**
   * Return the data of the first event 
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function getEvent()
    {
      if (is_array($this->_events))
	return reset($this->_events);
      else
	return $this->_events;
    }

  /**
   * Return the list of events
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function getEvents()
    {
      return $this->_events;
    }

  
  /**
   * handle begin event element
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function xmltag_event($xp, $name, $attribs)
    {
      $attribs['dateExport'] = $this->_meta['date'];
      unset($attribs['evnt_idold']);
      $this->_events[] = $attribs;
    }

}
?>