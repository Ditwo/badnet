<?php
/*****************************************************************************
 !   Module     : Associations
 !   File       : $Source: /cvsroot/aotb/badnet/src/asso/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.14 $
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

class assoBase_A extends utbase
{
	/**
	 * Recherche des clubs correspondants aux crit�res.
	 * ils sont enregistres dans la table de travail trvassoc
	 */
	function searchAssos($criteria)
	{
		$ut = new utils();
		// S'il n'y a aucun critere : pas de recherche
		// pour ne pas renvoyer la base entiere
		$rows = array();
		if ($criteria['asso_name'] === '' &&
		$criteria['asso_pseudo'] === '' &&
		$criteria['asso_stamp']=== '' &&
		$criteria['asso_type'] === 0)
		{
	  $rows['errMsg']= 'msgNeedCriteria';
	  return $rows;
		}

		if ($criteria['asso_type'] != WBS_CLUB)
		$criteria['asso_dpt'] = '';

		// Chercher dans la base federale Poona
		require_once "import/imppoona.php";
		$poona = new ImpPoona();
		$res = $poona->searchInstance($criteria);
		$instances = array();
		if (is_array($res))
		{
	  foreach ($res as $inst)
	  {
	  	$inst['asso_type'] = $ut->getLabel($inst['asso_type']);
	  	$instances[$inst['asso_id']] = $inst;
	  }
		}
		// Recherche dans la base badnet
		$fields = array('asso_id', 'asso_name',
		      'asso_pseudo', 'asso_stamp', 'asso_dpt',
		      'asso_type', 'asso_fedeId');
		$tables = array('assocs');
		$where = '';
		$glue = '';
		if ($criteria['asso_name'] != '')
		{
	  $where .= "asso_name LIKE '%".addslashes($criteria['asso_name'])."%'";
	  $glue = " AND";
		}
		if ($criteria['asso_pseudo'] != '')
		{
	  $where .= "$glue asso_pseudo LIKE '%".addslashes($criteria['asso_pseudo'])."%'";
	  $glue = " AND";
		}
		if ($criteria['asso_stamp'] != '')
		{
	  $where .= "$glue asso_stamp LIKE '%".addslashes($criteria['asso_stamp'])."%'";
	  $glue = " AND ";
		}
		if ($criteria['asso_type'] != 0)
		{
	  $where .= "$glue asso_type = ".$criteria['asso_type'];
	  $glue = " AND ";
	  if ($criteria['asso_type'] == WBS_CLUB)
	  {
	  	 
	  	$where .= "$glue asso_dpt = '{$criteria['asso_dpt']}'";
	  	//$where .= " OR asso_dpt = '' OR asso_dpt IS NULL)";
	  }
		}
		$order = "asso_name";
		$res = $this->_select($tables, $fields, $where, $order);

		// Stocker dans un tableau de travail
		while ($asso = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $asso['asso_id'] .= ";{$asso['asso_fedeId']}";
	  $asso['asso_type'] = $ut->getLabel($asso['asso_type']);
	  $rows[] = $asso;
	  $instanceId = "-1;{$asso['asso_fedeId']}";
	  unset($instances[$instanceId]);
	  $tri[] = $asso['asso_name'];
		}
		if (count($instances))
		{
	  foreach($instances as $instance)
	  {
	  	$rows[] = $instance;
	  	$tri[] = $instance['asso_name'];
	  }
	  array_multisort($tri, $rows);
		}
		if (!count($rows))
		$rows['errMsg'] = 'msgNotFound';
		return $rows;
	}

	// {{{ getAsso
	/**
	 * Return the column of an association
	 *
	 * @access public
	 * @param  string  $assoId  id of the member
	 * @return array   information of the member if any
	 */
	function getAsso($assoId, $fedeId=-1)
	{
		if ($assoId == -1 && $fedeId == -1) return null;
		$fields = array('asso_id', 'asso_name', 'asso_stamp', 'asso_pseudo',
		      'asso_type', 'asso_cmt', 'asso_url', 'asso_logo',
		      'asso_noc', 'asso_fedeId', 'asso_number',
		      'asso_dpt', 'asso_lockid');
		$tables[] = 'assocs';
		if ($assoId != -1)
		$where = "asso_id = '$assoId'";
		else
		$where = "asso_fedeid = '$fedeId'";
		$res = $this->_select($tables, $fields, $where);
		$asso = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$trans = array('asso_name','asso_pseudo');
		$asso = $this->_getTranslate('assos', $trans,
		$asso['asso_id'], $asso);
		return $asso;
	}
	// }}}

	// {{{ getContact
	/**
	 * Return the column of contact of an association
	 *
	 * @access public
	 * @param  string  $assoId  id of the member
	 * @return array   information of the member if any
	 */
	function getContact($assoId)
	{
		$fields = array('ctac_id', 'ctac_type', 'ctac_associd', 'ctac_value',
		      'ctac_contact');
		$where = "ctac_assocId = $assoId".
	" AND ctac_type=".WBS_EMAIL;
		$res = $this->_select('contacts', $fields, $where);
		if ($res->numRows())
		{
			$res = $this->_select('contacts', $fields, $where);
			$contact = $res->fetchRow(DB_FETCHMODE_ASSOC);
		}
		else
		{
			$contact = array('ctac_id'      => -1,
			 'ctac_type'    => WBS_EMAIL, 
			 'ctac_associd' => 0, 
			 'ctac_value'   => '',
			 'ctac_contact' => '');
		}
		return $contact;
	}
	// }}}

	// {{{ getTeam
	/**
	 * Return the column of a team and his association
	 *
	 * @access public
	 * @param  string  $teamId  id of the team
	 * @return array   information of the member if any
	 */
	function getTeam($teamId)
	{
		$fields = array('team_id', 'team_date', 'team_cmt', 'team_captain',
		      'team_name', 'team_stamp', 'cunt_id', 'cunt_name', 
		      'a2t_assoId as asso_id', 'team_url', 'team_noc', 'team_logo');
		$tables = array('a2t',
		      'teams LEFT JOIN accounts ON team_accountId = cunt_id');
		$where = "team_id = '$teamId'".
	" AND a2t_teamId = team_id".
	' AND team_eventId ='. utvars::getEventId();
		//	" AND team_accountId = cunt_id";
		$res = $this->_select($tables, $fields, $where);
		$data = $res->fetchRow(DB_FETCHMODE_ASSOC);

		$utd = new utdate();
		$utd->setIsoDateTime($data['team_date']);
		$data['team_date'] = $utd->getDate();

		$trans = array('team_name','team_stamp', 'team_cmt');
		$data = $this->_getTranslate('teams', $trans,
		$data['team_id'], $data);

		return $data;
	}
	// }}}

	// {{{ updateAsso
	/**
	 * Add or update a massociation into the database
	 *
	 * @access public
	 * @param  string  $info   column of the event
	 * @return mixed
	 */
	function updateAsso($infos, $contact = false)
	{
		if ($infos['asso_id'] == -1)
		{
	  unset($infos['asso_id']);
	  $res = $this->_insert('assocs', $infos);
	  $infos['asso_id'] = $res;
		}

		$where = "asso_id=".$infos['asso_id'];
		$trans = array('asso_name','asso_stamp', 'asso_pseudo');
		$infos = $this->_updtTranslate('assos', $trans,
		$infos['asso_id'], $infos);

		$res = $this->_update('assocs', $infos, $where);

		if ($contact!==false && $contact['ctac_contact'] !='' &&
		$contact['ctac_value'] != '')
		{
			$contact['ctac_associd'] = $infos['asso_id'];
	  if ($contact['ctac_id'] == -1)
	  {
	  	unset($contact['ctac_id']);
	  	$res = $this->_insert('contacts', $contact);
	  }
	  else
	  {
	  	$where = "ctac_id=".$contact['ctac_id'];
	  	$res = $this->_update('contacts', $contact, $where);
	  }
		}
		return $infos['asso_id'];
	}
	// }}}

	// {{{ delMembers
	/**
	 * Delete some registered members
	 *
	 * @access public
	 * @param  arrays  $regiIds   id's of the registration
	 * @return mixed
	 */
	function delMembers($ids)
	{
		foreach ($ids as $regiId)
		{
	  // Is the member have purchase
	  $fields = array('cmd_id', 'regi_longName');
	  $tables = array('registration', 'commands');
	  $where =  "cmd_regiId = $regiId".
	    ' AND cmd_regiId = regi_id';
	  $res = $this->_select($tables, $fields, $where);
	  if ($res->numRows())
	  {
	  	$regi = $res->fetchRow(DB_FETCHMODE_ORDERED);
	  	$err['errMsg'] = 'msgPurchaseExist';
	  	$err['name'] = $regi[1];
	  	return $err;
	  }

	  // Look at matches of concerned player
	  $fields = array('mtch_num', 'mtch_discipline', 'regi_longName');
	  $tables = array('matchs', 'p2m', 'i2p', 'registration');
	  $where = " i2p_regiId=regi_id".
	    " AND i2p_pairId=p2m_pairId".
	    " AND mtch_id=p2m_matchId".
	    " AND regi_id=$regiId";
	  $res = $this->_select($tables, $fields, $where);

	  // The player has match! Can't delete his registration
	  if($res->numRows())
	  {
	  	$regi = $res->fetchRow(DB_FETCHMODE_ORDERED);
	  	$err['errMsg'] ='msgRegiPlay';
	  	$err['name'] = $regi[2];
	  	return $err;
	  }

	  // Delete his ranking
	  $where = "rank_regiId=$regiId ";
	  $res = $this->_delete('ranks', $where);

	  // Supress the registration
	  $where = "regi_id= $regiId";
	  $res = $this->_delete('registration', $where);
		}
		return true;
	}
	// }}}
}
?>