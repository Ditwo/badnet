<?php
/*****************************************************************************
!   Module     : Export Dbf
!   File       : $Source: /cvsroot/aotb/badnet/src/dbf/xml_parser_regis.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.2 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2005/11/08 23:52:27 $
!   Mailto     : cage@free.fr
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
require_once "Xml/Parser.php";

class regisParser extends XML_Parser
{
  
  // {{{ properties
  
  /**
   * Read data
   *
   * @var     string
   * @access  private
   */
  var $_data = "";

  function regisParser()
    {
      parent::XML_Parser();
    }
  
  /**
   * handle begin assoc element
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function xmltag_assoc($xp, $name, $attribs)
    {
      $this->_assoId = $this->_parseAsso($attribs);
    }

  /**
   * handle begin team element
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function xmltag_team($xp, $name, $attribs)
    {
      $this->_teamId = $this->_parseTeam($attribs);
    }

  /**
   * handle begin member element
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function xmltag_member($xp, $name, $attribs)
    {
      $this->_mberId = $this->_parseMember($attribs);
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
      $this->_regiId = $this->_parseRegi($attribs);
    }

  /**
   * handle data
   *
   * @access   private
   * @param    resource    xml parser resource
   * @param    string      name of the element
   * @param    array       attributes
   */
  function cdataHandler($xp, $data)
    {
      if (strlen(trim($data)))
	{
	  $this->_data .= $data;
	}
    }
  //}}}

  /**
   * Treat the assocation
   *
   * @access   private
   * @param    array       attributes
   */
  function _parseAsso($data)
    {
      $utb = new utBase();
      $tmp['asso_name'] = $data['NAME'];
      if ($data['SIGLE'] != '')
	$tmp['asso_stamp'] = $data['SIGLE'];
      if ($data['NOC'] != '')
	$tmp['asso_noc'] = $data['NOC'];
      if ($data['TYPE'] != '')
	$tmp['asso_type'] = $data['TYPE'];
      if ($data['URL'] != '')
	$tmp['asso_url'] = $data['URL'];
      if ($data['NUMBER'] != '')
	$tmp['asso_number'] = $data['NUMBER'];
      if ($data['DPT'] != '')
	$tmp['asso_dpt'] = $data['DPT'];

      // Search the association 
      $fields  = array('*');
      $tables = array('assocs');
      $where = "asso_name = '".$tmp['asso_name']."'";
      $res = $utb->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
 	   return $err;
        }       
      if ($res->numRows())
	{
      $res = $utb->_select($tables, $fields, $where);
		$asso = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $utb->_update('assocs', $tmp, $where);
	  $assoId = $asso['asso_id'];
	  $err[] = $assoId;
	  $err[] = $tmp['asso_name'];
	  $err[] = $tmp['asso_stamp'];
	  $err[] = "Association mise � jour";
	  $this->_err[] = $err;
	}
      else
	{
	  $assoId = $utb->_insert('assocs', $tmp);
	  $err[] = $assoId;
	  $err[] = $tmp['asso_name'];
	  $err[] = $tmp['asso_stamp'];
	  $err[] = "Nouvelle association cr��";
	  $this->_err[] = $err;
	}
      return $assoId;
    }
  //}}}


  /**
   * Treat the team
   *
   * @access   private
   * @param    array       attributes
   */
  function _parseTeam($data)
    {
      $utb = new utBase();
      $eventId = utvars::getEventId();
      $tmp['team_eventId'] = $eventId;
      $tmp['team_numReg'] = $data['TEAM_ID'];
      if ($data['NAME'] != '')
	$tmp['team_name'] = $data['NAME'];
      if ($data['SIGLE'] != '')
	$tmp['team_stamp'] = $data['SIGLE'];
      if ($data['DATE'] != '')
	$tmp['team_date'] = $data['DATE'];
      if ($data['NOC'] != '')
	$tmp['team_noc'] = $data['NOC'];
      if ($data['URL'] != '')
	$tmp['team_url'] = $data['URL'];
      if ($data['CAPTAIN'] != '')
	$tmp['team_captain'] = $data['CAPTAIN'];      
    

      // Search the team 
      $fields  = array('*');
      $tables = array('teams', 'a2t');
      $where = "team_eventId = $eventId".
	" AND a2t_teamId = team_id".
	" AND a2t_assoId =".$this->_assoId.
	" AND team_numReg =".$tmp['team_numReg'];
      $res = $utb->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  echo  __FILE__.' ('.__LINE__.'):base inaccessible';
        }       
      if ($res->numRows())
	{
      $res = $utb->_select($tables, $fields, $where);
		$team = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $teamId = $team['team_id'];
	  $where = "team_id = $teamId";
	  $res = $utb->_update('teams', $tmp, $where);
	  if (empty($res)) 
	    {
	      echo __FILE__.' ('.__LINE__.'):base inaccessible';
	    }       
	  $err[] = $teamId;
	  $err[] = $tmp['team_name'];
	  $err[] = $tmp['team_stamp'];
	  $err[] = "Equipe mise � jour";
	  $this->_err[] = $err;
	}
      else
	{
	  $teamId = $utb->_insert('teams', $tmp);

	  $err[] = $teamId;
	  $err[] = $tmp['team_name'];
	  $err[] = $tmp['team_stamp'];
	  $err[] = "Nouvelle �quipe cr��";
	  $this->_err[] = $err;

	  $tmp = array();;
	  $tmp['a2t_teamId'] = $teamId;
	  $tmp['a2t_assoId'] = $this->_assoId;
	  $utb->_insert('a2t', $tmp);
	}
      return $teamId;
    }
  //}}}

  /**
   * Treat the member
   *
   * @access   private
   * @param    array       attributes
   */
  function _parseMember($data)
    {
      $utb = new utBase();
      $tmp['mber_secondname'] = $data['FAMILYNAME'];
      $tmp['mber_firstname'] = $data['FIRSTNAME'];
      $tmp['mber_sexe'] = $data['GENDER'];
      if ($data['BORN'] != '')
	$tmp['mber_born'] = $data['BORN'];
      if ($data['LICENSE'] != '')
	$tmp['mber_licence'] = $data['LICENSE'];
      if ($data['IBF'] != '')
	$tmp['mber_ibfnumber'] = $data['IBF'];

      $nbRes = 0;
      $fields  = array('*');
      $tables = array('members');
      // Search the member with ibf number 
      if (($data['IBF'] != '') && ($data['IBF'] != 0))
	{
	  $where = "mber_ibfnumber = '".$tmp['mber_ibfnumber']."'";
	  echo "$where<br>";
	  $res = $utb->_select($tables, $fields, $where);
	  if (empty($res)) 
	    {
	      echo __FILE__.' ('.__LINE__.'):base inaccessible';
	      return -1;
	    }       
	  $nbRes = $res->numRows();
	}
      
      // Search the member with license number       
      if ( ($nbRes == 0) && ($data['LICENSE'] != '') && 
	   ($data['LICENSE'] != 0))
	{
	  $where = "mber_licence = '".$tmp['mber_licence']."'";
	  $res = $utb->_select($tables, $fields, $where);
	  if (empty($res)) 
	    {
	      echo __FILE__.' ('.__LINE__.'):base inaccessible';
	      return -1;
	    }       
	  $nbRes = $res->numRows();
	}
      
      // Search the member with family name 
      if ($nbRes == 0) 
	{
	  $where = "mber_secondname = '".$tmp['mber_secondname']."'".
	  " AND mber_firstname = '".$tmp['mber_firstname']."'";
	  $res = $utb->_select($tables, $fields, $where);
	  if (empty($res)) 
	    {
	      echo __FILE__.' ('.__LINE__.'):base inaccessible';
	      return -1;
	    }       
	  $nbRes = $res->numRows();
	}
      
      // More than one possibility : just warning
      if ($nbRes > 1)
	{
	  $buf = $tmp['mber_secondname'] . " " . $tmp['mber_firstname'].
	    $tmp['mber_sexe'] . '-' . $tmp['mber_born'] .'-'. 
	    $tmp['mber_licence'] . '-' . $tmp['mber_ibfnumber'];
	  $this->_err[] = array(KOD_BREAK, "title", $buf);
	  while ($member = $res->fetchRow(DB_FETCHMODE_ASSOC))
	    {
	      $err = array();
	      $err[] = $member['mber_id'];
	      $err[] = $member['mber_secondname'];
	      $err[] = $member['mber_firstname'];
	      $err[] = $member['mber_born'];
	      $err[] = $member['mber_licence'];
	      $err[] = $member['mber_ibfnumber'];
	      $this->_err[] = $err;
	    }
	  return -1;
	}
      // One member found, update him
      if ($nbRes == 1)
	{
	  $data = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $err = array();
	  $err[] = $data['mber_id'];
	  $err[] = $tmp['mber_secondname'];
	  $err[] = $tmp['mber_firstname'];
	  $err[] = "Membre mis a jour";
	  $this->_err[] = $err;

	  $utb->_update('members', $tmp, $where);

	  return $data['mber_id'];
	}

      // No member found, so create a new one
      $memberId = $utb->_insert('members', $tmp);
      $err[] = $memberId;
      $err[] = $tmp['mber_secondname'];
      $err[] = $tmp['mber_firstname'];
      $err[] = "Nouveau membre cr��";
      $this->_err[] = $err;
      return $memberId;
    }
  //}}}


  /**
   * Treat the registration of a member
   *
   * @access   private
   * @param    array       attributes
   */
  function _parseRegi($data)
    {
      $utb = new utBase();
      $eventId = utvars::getEventId();
      $tmp['regi_eventId'] = $eventId;
      $tmp['regi_memberId'] = $this->_mberId;
      $tmp['regi_teamId'] = $this->_teamId;
      $tmp['regi_numReg'] = $data['REGI_ID'];
      if ($data['LONGNAME'] != '')
	$tmp['regi_longName'] = $data['LONGNAME'];
      if ($data['SHORTNAME'] != '')
	$tmp['regi_shortName'] = $data['SHORTNAME'];
      if ($data['TYPE'] != '')
	$tmp['regi_type'] = $data['TYPE'];
      if ($data['DATE'] != '')
	$tmp['regi_date'] = $data['DATE'];
      if ($data['NOC'] != '')
	$tmp['regi_noc'] = $data['NOC'];

      // Search the regi
      $fields  = array('*');
      $tables = array('registration');
      $where = "regi_eventId = $eventId".
	" AND regi_teamId =".$this->_teamId.
	" AND regi_memberId =".$this->_mberId.
	" AND regi_numReg =".$tmp['regi_numReg'];
      $res = $utb->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  echo  __FILE__.' ('.__LINE__.'):base inaccessible';
        }       
      if ($res->numRows())
	{
      $res = $utb->_select($tables, $fields, $where);
		$regi = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $regiId = $regi['regi_id'];
	  $res = $utb->_update('registration', $tmp, $where);
	  if (empty($res)) 
	    {
	      echo __FILE__.' ('.__LINE__.'):base inaccessible';
	    }       
	  $err[] = $regiId;
	  $err[] = $tmp['regi_longName'];
	  $err[] = $tmp['regi_type'];
	  $err[] = "Inscription mise � jour";
	  $this->_err[] = $err;
	}
      else
	{
	  $regiId = $utb->_insert('registration', $tmp);

	  $err[] = $regiId;
	  $err[] = $tmp['regi_longName'];
	  $err[] = $tmp['regi_type'];
	  $err[] = "Nouvelle inscription cr��";
	  echo "creer les classements !!!";
	  $this->_err[] = $err;
	}
      return $regiId;
    }
  //}}}


}
?>