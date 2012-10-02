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

class importParser extends XML_Parser
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
   * Current database id
   *
   * @var     string
   * @access  private
   */
  var $_dbs = array();

  /**
   * Current event id
   *
   * @var     string
   * @access  private
   */
  var $_eventId = NULL;

  /**
   * Flag to indicate the import data come from the current event
   *
   * @var     string
   * @access  private
   */
  var $_isComeBack  = false;

  /**
   * Flag to indicate the import data were already imported earlier
   *
   * @var     string
   * @access  private
   */
  var $_searchId = -1;

  function importParser()
    {
      $ut = new utils();

      parent::XML_Parser('ISO-8859-1', 'event', 'ISO-8859-1');
      $this->_dt = new exportBase();
      // Get the current database Id
      $this->_localDBId = $ut->getParam('databaseId', -1);
      if ($this->_localDBId == -1)
	{
	  $this->_localDBId = gmmktime();
	  $ut->setParam('databaseId', $this->_localDBId);
	}
      $this->_localEventId = utvars::getEventId();
    }
  // }}}

  /**
   * Accessor
   *
   * @access   public
   */
  function getDataBaseId() {return $this->_meta['database'];}
  function getDateRef() {return $this->_dateRef;}
  function getMeta() {return $this->_meta;}
  function getVersion() {return $this->_meta['version'];}


  /**
   * Return the dbs id
   *
   * @access   public
   */
  function getDbs()
    {
      return $this->_dbs;
    }

  /**
   * Return the importation date : ie the exportation date of the file
   *
   * @access   public
   */
  function getImportDate()
    {
      return $this->_importDate;
    }

  // {{{ _getLocalId
  /**
   * Find the local id in the list of extern id
   *
   * @access public
   * @param string   $list of ids
   * @return array   array of match
   */
  function _getLocalId($externIds)
    {
      // $pattern est de la forme databaseId:eventId
      if ($this->_isComeBack)
	{
	  $pattern = "{$this->_localDBId}:{$this->_localEventId}";
	  if (preg_match("/.*{$pattern}:([0-9]*);/", $externIds, $res))
	    return $res[1];
	}
      return -1;
    }
  // }}}

  // {{{ _getSearch
  /**
   * Find the local id in the list of extern id
   *
   * @access public
   * @param string   $begin  first date
   * @param string   $end    end date
   * @return array   array of match
   */
  function _getSearch($externIds)
    {
      // $pattern est de la forme databaseId:eventId
      if ($this->_searchId != -1)
	{
	  if (preg_match("/.*({$this->_searchId}:[0-9]*;).*/", 
			 $externIds, $res))
	      return $res[1];
	}
      return -1;
    }
  // }}}

  // {{{ mergeExternIds
  /**
   * Merge two list of extern Ids
   *
   * @access public
   * @param string   $begin  first date
   * @param string   $end    end date
   * @return array   array of match
   */
  function mergeExternIds(&$el1, $el2)
    {
      $externIds = explode(';', $el2);
      foreach($externIds as $externId)
	{
	  if (!preg_match("/.*{$externId}.*/", $el1))
	    $el1 .= $externId.";";
	}
      return $el1;
    }
  // }}}

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
      $this->_dateLimit = '';
      // Le tournoi est vide: premiere importation
      // Aucun controle d'anciennete ne doit passer
      // pour la creation des donnees
      if (is_null($this->_dt->getDateRef()))
	$this->_dateRef = "1970-01-01 00:00:00";
      else
	{
	  //Cas des anciennes version (< 2.5r2)
	  if (!isset($attribs['dateref']))
	    $this->_dateRef = "1970-01-01 00:00:00";
	  else
	    $this->_dateRef = $attribs['dateref'];
	}
      $this->_fromDbId = "{$this->_meta['database']}:{$this->_meta['event']}:";
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
  function xmltag_db($xp, $name, $attribs)
    {
      // Le tournoi vient-il de cette base ?
      if ($attribs['db_externEventId'] == $this->_localEventId &&
	  $attribs['db_baseId'] == $this->_localDBId &&
	  $attribs['db_date'] > $this->_dateLimit)
	{
	  $this->_isComeBack  = true;
	  $this->_dateLimit = $attribs['db_date'];
	}

      // Le tournoi est-il deja connu dans cette base
      $date = $this->_dt->searchDb($attribs['db_baseId'], 
				   $attribs['db_externEventId']);
      if (!is_null($date) && $this->_dateLimit <= $date)
	{
	  $this->_isComeBack  = false;
	  $this->_searchId = "{$attribs['db_baseId']}:{$attribs['db_externEventId']}";
	  $this->_dateLimit = $date;
	}      
      $this->_dbs[] = $attribs;      

      // A partir de v2.5r2, ce qui est au dessus devient obsolete
      $this->_importDate = $attribs['db_date'];
    }
  // }}}

  /**
   * Test du format du fichiera traiter
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function isOldVersion()
    {
      //return 1;
      //return $this->_meta['version'] < "Version V2_5r0";
      return false;
    }
  // }}}

  /**
   * Log error in file
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function log($error)
    {
      $path = "../tmp/";
      $file = "{$path}{$this->_localEventId}.log";
      $fd = fopen($file, 'a+');
      //$fileName = basename($this->_file);
      fwrite($fd, $error);
      fclose($fd);
    }
  // }}}
  
}

?>