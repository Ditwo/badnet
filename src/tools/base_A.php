<?php
/*****************************************************************************
!   Module     : tools
!   File       : $Source: /cvsroot/aotb/badnet/src/tools/base_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.4 $
!   Author     : G. CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:21 $
!   Mailto     : cage at free.fr
******************************************************************************
!   License   : Licensed under GPL [http://www.gnu.org/copyleft/gpl.html]
!      This program is free software; you can redistribute it and/or
!      modify it under the terms of the GNU General Public License
!      as published by the Free Software Foundation; either version 2
!      of the License, or (at your option) any later version.
!
!      This program is distributed in the hope that it will be useful,
!      but WITHOUT ANY WARRANTY; without even the implied warranty of
!      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
!      GNU General Public License for more details.
!
!      You should have received a copy of the GNU General Public License
!      along with this program; if not, write to the Free Software
!      Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, 
!      USA.
******************************************************************************/
require_once "utils/utbase.php";

/**
* Acces to the dababase for installation
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class ToolsBase_A extends utbase
{

  // {{{ properties
  /**
   * Database server objetct
   *
   * @private
   */
  var $_db;

  /**
   * Utilities object
   *
   * @var     string
   * @access  private
   */
  var $_ut;

  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function ToolsBase_A()
    {
      $this->_ut = new Utils();
      $this->_db = DB::Connect(utvars::getDsn(), $prefix);
      if (DB::isError($this->_db)) 
	echo DB::errorMessage($this->_db);
    }

  
  // {{{ createTransTable
  /**
   * Create the tables for translation in the database
   *
   * @access public
   * @param $database Name of the database
   * @param $version  Version of the soft
   * @return void
   */
  function createTransTable() 
    {
      $db = $this->_db;
      
      /** Table des langues */
      $columns = "
                lang_name      VARCHAR(5)  NOT NULL UNIQUE";
      $this->_newTable("langues", "lang", $columns);
      
      /** Table des fichiers */
      $columns = "
                file_name        VARCHAR(25) NOT NULL UNIQUE";
      $this->_newTable("files", "file", $columns);

      /** Table des variables */
      $columns = "
                var_name        VARCHAR(25) NOT NULL UNIQUE";
      $this->_newTable("vars", "var", $columns);
      
      /** Table des traductions */
      $columns = "
		trad_varId BIGINT NOT NULL,
		trad_langId BIGINT NOT NULL,
		trad_text    VARCHAR(250) NOT NULL";
      $keys =	"INDEX (trad_varId), INDEX (trad_langId)";
      $this->_newTable("traductions", "trad", $columns, $keys);
      
      /** Table des relation fichier/variable */
      $columns = "
                vafi_fileId  BIGINT  NOT NULL,
                vafi_varId   BIGINT  NOT NULL";
      $keys =	"INDEX (vafi_varId), INDEX (vafi_fileId)";
      $this->_newTable("vafi", "vafi", $columns, $keys);
      
      return;
    }
  // }}}
  
  // {{{ _newTable
  /**
   * Create a new tabla in the database. All table have some
   * fixed columns:
   *     _id   : id of the row
   *     _cre  : creation date of the row
   *     _updt : last modification date of the row
   *     _cmt  : comment of the row
   *     _slt  : selection flag of the row
   *     _rge  : range of the row (for a manually sort)
   *     _del  : logical flag for deleted record
   * @access private
   * @param $name name of the table. All table are prefixed with "aotb_"
   * @param $shortName short name of the table. All the columns are prefixed with the shortname
   * @param $columns list of the columns
   * @param $keys keys of the table
   * @return void
   */
  function _newTable($aName, $aShortName, $aColumns, $aKeys="") 
    {
      $query = "CREATE TABLE bn_$aName ($aColumns,".
	$aShortName."_id bigint(21) UNSIGNED DEFAULT '0' NOT NULL,".
	$aShortName."_cre  DATETIME NOT NULL,".
	$aShortName."_updt TIMESTAMP,".
	$aShortName."_cmt TEXT,".
	$aShortName."_pbl TINYINT DEFAULT '".WBS_DATA_CONFIDENT."',".
	$aShortName."_slt TINYINT DEFAULT '0',".
	$aShortName."_del TINYINT DEFAULT '".WBS_DATA_UNDELETE."',".
	$aShortName."_rge TINYINT DEFAULT '0'";
      $query .= ", PRIMARY KEY (`{$aShortName}_id`)";
      if ($aKeys!="") $query .= ", $aKeys";
      $query .= ");";

      $res = $this->_db->query($query);
      //if (DB::isError($res)) 
      //	echo DB::errorMessage($this->_db);
      return;

    }
  // }}}
  
  // {{{ updateFile
  /**
   * Add new file in the database
   *
   * @access public
   * @param  string  $file   name of the file
   * @param  string  $vars   vars declared in the file
   * @return mixed
   */
  function updateFile($file, $vars)
    {
      $db = $this->_db;

      // Search the file
      $query = "SELECT file_id";
      $query .= " FROM bn_files";
      $query .= " WHERE file_name='$file'";
      $res = $db->query($query);
      if (DB::isError($res)) 
	{
	  $err['errMsg'] = DB::errorMessage($res).":$query";
	  return $err;
	} 
      
      // File dont't exist! Create it!
      if (!$res->numRows()) 
	{      
	  // Create the new file
	  $fileId = $db->nextId("file_id");	  
	  $fields['file_id']   = $fileId;
	  $fields['file_name'] = $file;
	  $fields['file_updt'] = date(DATE_FMT);
	  $fields['file_cre']  = date(DATE_FMT);
	  $res = $db->autoExecute('bn_files', $fields, DB_AUTOQUERY_INSERT);
	  if (DB::isError($res)) 
	    {
	      $err['errMsg'] = __FILE__.'('__LINE__.'):'.DB::errorMessage($res);
	      return $err;
	    } 
	}
      else
	{
	  $var = $res->fetchRow(DB_FETCHMODE_ORDERED);
	  $fileId=$var[0];   
	}
      
      foreach($vars as $var)
	{
	  // Search the variable
	  $query = "SELECT var_id";
	  $query .= " FROM bn_vars";
	  $query .= " WHERE var_name='$var'";
	  $res = $db->query($query);
	  if (DB::isError($res)) 
	    {
	      $err['errMsg'] = __FILE__.'('__LINE__.'):'.DB::errorMessage($res).":$query";;
	      return $err;
	    } 
      
	  // Variable dont't exist! Create it!
	  if (!$res->numRows()) 
	    {      
	      // Create the new variable
	      $fields = array();
	      $varId = $db->nextId("var_id");	  
	      $fields['var_id']   = $varId;
	      $fields['var_name'] = $var;
	      $fields['var_updt'] = date(DATE_FMT);
	      $fields['var_cre']  = date(DATE_FMT);
	      $res = $db->autoExecute('bn_vars', $fields, DB_AUTOQUERY_INSERT);
	      if (DB::isError($res)) 
		{
		  $err['errMsg'] = __FILE__.'('__LINE__.'):'.DB::errorMessage($res);
		  return $err;
		} 
	    }
	  else
	    {
	      $row = $res->fetchRow(DB_FETCHMODE_ORDERED);
	      $varId=$row[0];   
	    }
	  
	  // Search the relation with the file
	  $query = "SELECT vafi_id";
	  $query .= " FROM bn_vafi";
	  $query .= " WHERE vafi_fileId=$fileId";
	  $query .= " AND  vafi_varId=$varId";
	  $res = $db->query($query);
	  if (DB::isError($res)) 
	    {
	      $err['errMsg'] = __FILE__.'('__LINE__.'):'.DB::errorMessage($res).":$query";
	      return $err;
	    } 
      
	  // Relation dont't exist! Create it!
	  if (!$res->numRows()) 
	    {      
	      // Create the new variable
	      $fields = array();
	      $vafiId = $db->nextId("vafi_id");	  
	      $fields['vafi_id']     = $vafiId;
	      $fields['vafi_fileId'] = $fileId;
	      $fields['vafi_varId']  = $varId;
	      $fields['vafi_cre']    = date(DATE_FMT);
	      $fields['vafi_updt']   = date(DATE_FMT);
	      $res = $db->autoExecute('bn_vafi', $fields, DB_AUTOQUERY_INSERT);
	      if (DB::isError($res)) 
		{
		  $err['errMsg'] = __FILE__.'('__LINE__.'):'.DB::errorMessage($res);
		  return $err;
		} 
	    }
	}// End foreach vars

      return true;
    }
  // }}}

  // {{{ addLang
  /**
   * Add new language in the database
   *
   * @access public
   * @param  string  $info   column of the event
   * @return mixed
   */
  function addLang($lang)
    {
      $db = $this->_db;

      // Search the language
      $query = "SELECT lang_id";
      $query .= " FROM bn_langues";
      $query .= " WHERE lang_name='$lang'";
      $res = $db->query($query);
      if (DB::isError($res)) 
	{
	  $err['errMsg'] = __FILE__.'('__LINE__.'):'.DB::errorMessage($res).":$query";
	  return $err;
	} 
      
      // Language already exist! Go out!
      if ($res->numRows()) return;
      
      // Create the new language
      $langId = $db->nextId("lang_id");	  
      $query = "INSERT INTO bn_langues (lang_id, lang_name,";
      $query .= "lang_cre)";
      $query .= " VALUES ('$langId";
      $query .= "', '". $lang;
      $query .= "', NOW())";
      $res = $db->query($query);

      $fields = array();
      $langId = $db->nextId("lang_id");	  
      $fields['lang_id']   = $langId;
      $fields['lang_name'] = $lang;
      $fields['lang_cre']  = date(DATE_FMT);
      $fields['lang_updt'] = date(DATE_FMT);
      $res = $db->autoExecute('bn_langues', $fields, DB_AUTOQUERY_INSERT);
      if (DB::isError($res)) 
	{
	  $err['errMsg'] = __FILE__.'('__LINE__.'):'.DB::errorMessage($res);
	  return $err;
	} 
      

      // Get all variables 
      $query = "SELECT var_id";
      $query .= " FROM bn_vars";
      $vars = $db->query($query);
      if (DB::isError($res)) 
	{
	  $err['errMsg'] = __FILE__.'('__LINE__.'):'.DB::errorMessage($res).":$query";
	  return $err;
	} 
      
      // For each variable, create the translation data
      $fields = array();
      $fields['trad_langId'] = $langId;
      $fields['lang_cre']    = date(DATE_FMT);
      $fields['lang_updt']   = date(DATE_FMT);
      while ($var = $vars->fetchRow(DB_FETCHMODE_ORDERED))
	{
	  $fields['trad_id']     = $db->nextId("trad_id");
	  $fields['trad_varId']  = $var[0];
	  $res = $db->autoExecute('bn_traductions', $fields, DB_AUTOQUERY_INSERT);
	  $vars = $db->query($query);
	  if (DB::isError($res)) 
	    {
	      $err['errMsg'] = __FILE__.'('__LINE__.'):'.DB::errorMessage($res);
	      return $err;
	    } 
	}
     
      return true;
    }
  // }}}

}

?>