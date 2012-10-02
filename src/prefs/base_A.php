<?php
/*****************************************************************************
!   Module    : Preferences
!   Version   : v0.0.1
!   Author    : G.CANTEGRIL
!   Co-author :
!   Mailto    : cage@free.fr
!   Date      : 28-12-2003
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
******************************************************************************
!   Greetings & thanks to:
!      - 
******************************************************************************
!   History:
!      v0.0.1        creation
******************************************************************************
!   Todo:
!
******************************************************************************/

require_once "utils/utbase.php";

/**
* Acces to the dababase for preferences
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class prefBase_A extends utbase
{

  // {{{ properties
  
  /**
   * Util object
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
  function prefBase_A()
    {
      $this->_ut  = new Utils();
    }

  // {{{ getRanks
  /**
   * Retrieve the list of ranking
   *
   * @access public
   * @return array   array of rankings
   */
  function getRanks()
    {
      // Retreive all defined ranking
      $fields = array('rkdf_id', 'rkdf_label', 'rkdf_point',
		      'rkdf_seuil');
      $order = '3';

      $res = $this->select('rankdef', $field, NULL, $order);
      $ranks=array();
      while ($rank = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $ranks[] = $rank;
	}
      return $ranks;
      
    }
  // }}}


  // {{{ getMailInfo
  /**
   * Retrieve informations for email
   *
   * @access public
   * @return array   array of associations
   */
  function getMailInfos()
    {
      $ut=$this->_ut;

      $param= array("host" => $ut->getParam("smtp_server"),
		    "port" => $ut->getParam("smtp_port", 25),
		    "username" => $ut->getParam("smtp_user"),
		    "password" => $ut->getParam("smtp_password"),
		    "ffbaEmail" => $ut->getParam("ffba_email"),
		    "ffbaUrl" => $ut->getParam("ffba_url"),
		    "ibfUrl" => $ut->getParam("ibf_url")
		    );
      return $param;
      
    }
  // }}}

  // {{{ setMailInfo
  /**
   * Set the informations for email in the database
   *
   * @access public
   * @return array   array of associations
   */
  function setMailInfos($infos)
    {
      $ut=$this->_ut;
      $ut->setParam("smtp_server", $infos["host"]);
      $ut->setParam("smtp_port", $infos["port"]);
      $ut->setParam("smtp_user", $infos["username"]);
      $ut->setParam("smtp_password", $infos["password"]);
      $ut->setParam("ffba_email", $infos["ffbaEmail"]);
      $ut->setParam("ffba_url", $infos["ffbaUrl"]);
      $ut->setParam("ibf_url", $infos["ibfUrl"]);
      return true;      
    }
  // }}}

}
?>