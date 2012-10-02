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

class postitsParser extends importParser
{
  
  
  /**
   * Constructors
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function  postitsParser()
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
  function xmltag_psit($xp, $name, $attribs)
    {
      unset($attribs['psit_id']);
      unset($attribs['psit_idold']);
      $attribs['psit_eventId'] = $this->_localEventId;
      $utbase = $this->_utbase;

      // create a new one
	  $postitId = $utbase->_insert('postits', $attribs);	
      return $postitId;
    }

}
?>