<?php
/*****************************************************************************
!   Module     : Ajaj
!   File       : $Source: /cvsroot/aotb/badnet/src/ajaj/base_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
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
require_once "utils/utbase.php";

/**
* Acces to the dababase for teams for administrator
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class ajajBase_A extends utbase
{

  // {{{ properties
  // }}}

  // {{{ updateTeam
  /**
   * Enregistre une equipe
   *
   * @access public
   * @param  integer  $teamId  Id of the team
   * @return array   array of users
   */
  function updateTeam($team)
    {
      $where = "team_id={$team['team_id']}";
      $res = $this->_update('teams', $team, $where);
    }
  // }}}

  // {{{ updateAsso
  /**
   * Enregistre une association
   *
   * @access public
   * @param  integer  $teamId  Id of the team
   * @return array   array of users
   */
  function updateAsso($asso)
    {
      $where = "asso_id={$asso['asso_id']}";
      $res = $this->_update('assocs', $asso, $where);
    }
  // }}}

  /**
   * Merge deux association
   *
   * @access public
   * @param  integer  $teamId  Id of the team
   * @return array   array of users
   */
  function mergeAsso($src, $dest)
    {
    // Fusion des associations
      $cols = array('a2t_assoId'=>$dest);
      $where = "a2t_assoId= $src";
      $this->_update('a2t', $cols, $where);
      
      //Mise a jour du manager
      $cols = array('rght_themeid' => $dest);
      $where = "rght_theme=" . WBS_THEME_ASSOS
      . " AND rght_status='" . WBS_AUTH_MANAGER . "'"
      . ' AND rght_themeid=' . $src;
      $this->_update('rights', $cols, $where);

      //Mise a jour des inscriptions
	  $this->_prefix = 'asso_';
      $cols = array('lreg_assoid' => $dest);
      $where = 'lreg_assoid=' . $src;
      $this->_update('lineregi', $cols, $where);
	  $this->_prefix = utvars::getPrfx().'_';
      
      
      // Suppression de l'association
      $where = "asso_id= $src";
      $this->_delete('assocs', $where);
    }
  // }}}

  // {{{ getAsso
  /**
   * renvoi une association
   *
   * @access public
   * @param  integer  $teamId  Id of the team
   * @return array   array of users
   */
  function getAsso($assoId)
    {
      $fields = array('*');
      $where = "asso_id={$assoId}";
      $res = $this->_select('assocs', $fields, $where);
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
  // }}}

  // {{{ updateMember
  /**
   * Enregistre un membre
   *
   * @access public
   * @return array   array of users
   */
  function updateMember($mber)
    {
      $where = "mber_id={$mber['mber_id']}";
      $res = $this->_update('members', $mber, $where);
    }
  // }}}

  // {{{ mergeMembers
  /**
   * Fusionne deux membres
   *
   * @access public
   * @param  integer  $teamId  Id of the team
   * @return array   array of users
   */
  function mergeMember($src, $dest)
    {
      $cols = array('ctac_memberId'=>$dest);
      $where = "ctac_memberId = $src";
      $this->_update('contacts', $cols, $where);

      $cols = array('regi_memberId'=>$dest);
      $where = "regi_memberId = $src";
      $this->_update('registration', $cols, $where);

      $where = "mber_id= $src";
      $this->_delete('members', $where);
    }
  // }}}

  // {{{ getMember
  /**
   * renvoi un membre
   *
   * @access public
   * @param  integer  $teamId  Id of the team
   * @return array   array of users
   */
  function getMember($mberId)
    {
      $fields = array('*');
      $where = "mber_id={$mberId}";
      $res = $this->_select('members', $fields, $where);
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
  // }}}

  // {{{ getMembers
  /**
   * Retrieve all members
   *
   * @access public
   * @return  
   */
  function getMembers($alpha)
    {
      // Retrieve all associations
      $fields = array('mber_id', 'mber_firstname', 'mber_secondname');
      $tables = array('members');
      $where = "mber_secondname like '$alpha%'".
	" AND mber_id > 0";
      $order  = 'mber_secondname, mber_firstname';
      $res = $this->_select($tables, $fields, $where, $order);
      $mbers = array();
      while ($mber = $res->fetchRow(DB_FETCHMODE_ASSOC))
	$mbers[$mber['mber_id']]="{$mber['mber_secondname']} {$mber['mber_firstname']}";
      return $mbers;     
    }
  // }}}

}

?>