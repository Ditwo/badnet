<?php
/*****************************************************************************
!   Module     : members
!   File       : $Source: /cvsroot/aotb/badnet/src/mber/base_V.php,v $
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
* Acces to the dababase for events
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class memberBase_V  extends utbase
{

  // {{{ properties
  // }}}
  
  // {{{ getMembers
  /**
   * Return the list of the members
   *
   * @access public
   * @param  string  $sort   criteria to sort users
   * @return array   array of users
   */
  function getMembers($sort=1)
    {
      $fields = array('mber_id', 'mber_sexe', 'mber_firstname',
		      'mber_secondname', 'mber_born', 'mber_ibfnumber',
		      'mber_licence', 'mber_cmt');
      $tables[] = 'members';
      $where = "mber_del != ".WBS_DATA_DELETE;
      if ($sort < 0)
	{
	  $sort *= -1;
	  $order = $sort." desc";
	}
      else
	$order = $sort;
      
      $res = $this->_select($tables, $fields, $where, $order);
      while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  //$entry[1] = $this->_utils->getSexeId($entry[1]);
	  $date = new utdate();
	  $date->setIsoDate($entry[4]);
	  $entry[4] = $date->getDate();
	  $rows[] = $entry;
        }
      return $rows;
    }
  // }}}

  // {{{ getMember
  /**
   * Return the column of a member
   *
   * @access public
   * @param  string  $memberId  id of the member
   * @return array   information of the member if any
   */
  function getMember($memberId)
    {
      $fields = array('mber_id', 'mber_sexe', 'mber_firstname',
		      'mber_secondname', 'mber_born', 'mber_ibfnumber',
		      'mber_licence', 'mber_cmt');
      $tables[] = 'members';
      $where = "mber_id = '$memberId'";
      $res = $this->_select($tables, $fields, $where);
      $player =  $res->fetchRow(DB_FETCHMODE_ASSOC);
      $date = new utdate();
      $date->setIsoDate($player['mber_born']);
      $player['mber_born'] = $date->getDate();
      return $player;
    }
  // }}}
}
?>