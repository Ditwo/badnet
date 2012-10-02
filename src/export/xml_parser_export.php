<?PHP
/**
 * 
 *
 * @author      Stephan Schmidt <schst@php-tools.net>
 * @package     XML_Parser
 * @subpackage  Examples
 */

/**
 * require the parser
 */
require_once "base_A.php";
require_once "Xml/Parser.php";

class exportParser extends XML_Parser
{
  
  // {{{ properties
  
  /**
   * Meta data
   *
   * @var     string
   * @access  private
   */
  var $_meta = NULL;

  /**
   * Current database id
   *
   * @var     string
   * @access  private
   */
  var $_localDBId = NULL;

  /**
   * Evnet id
   *
   * @var     string
   * @access  private
   */
  var $_eventId = NULL;


  function exportParser()
    {
      $ut = new utils();

      parent::XML_Parser();
      $this->_dt = new exportBase();
      // Get the current database Id
      $this->_localDBId = $ut->getParam('databaseId', -1);
      if ($this->_localDBId == -1)
	{
	  $this->_localDBId = gmmktime();
	  $ut->setParam('databaseId', $this->_localDBId);
	}
      $this->_eventId = utvars::getEventId();
    }


  /**
   * Return the base distant id
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function getDataBaseId()
    {
      return $this->_meta['database'];
    }

  /**
   * Return the meta data
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function getMeta()
    {
      return $this->_meta;
    }

  /**
   * Return the dbs id
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function getDbs()
    {
      return $this->_dbs;
    }
  
  /**
   * handle begin badnet element
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function xmltag_badnet($xp, $name, $attribs)
    {
      $this->_meta = $attribs;
      return;
    }

  /**
   * handle  db element
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function xmltag_db_($xp, $name, $attribs)
    {
      if ($attribs['db_baseId'] != $this->_meta['database'])
	$this->_dbs[] = $attribs;
    }
}

?>