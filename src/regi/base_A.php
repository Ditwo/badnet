<?php
/*****************************************************************************
 !   Module     : Registrations
 !   File       : $Source: /cvsroot/aotb/badnet/src/regi/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.33 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/03/06 18:02:10 $
 ******************************************************************************/
require_once "utils/utbase.php";

/**
 * Acces to the dababase for events
 *
 * @author Didier BEUVELOT <didier.beuvelot@free.fr>
 * @see to follow
 *
 */

class registrationBase_A extends utBase
{
	function getFirstDraw($aDisci, $aRank=-1, $aCatage = WBS_CATAGE_SEN)
	{
		$eventId    = utvars::getEventId();
		// Retrieve draw's data
		$where  = 'draw_disci = ' . $aDisci .
		 ' AND draw_catage = ' . $aCatage.
		 ' AND draw_eventid = ' . $eventId;
		if($aRank >0 ) $where .= ' AND draw_rankdefid = ' . $aRank;
		$res = $this->_select('draws', 'draw_id', $where, false, 'draw');
		// Trouve
		$drawId = -1;
		if ($res->numRows())
		{
			$draw = $res->fetchRow(DB_FETCHMODE_ASSOC);
			$drawId = $draw['draw_id'];
		}
		// Recherche du premier tableau sans tenir compte de l'age et du classement
		if ($drawId == -1)
		{
			$where  = 'draw_disci = ' . $aDisci;
		 		' AND draw_eventid = ' . $eventId;
			$res = $this->_select('draws', 'draw_id', $where, false, 'draw');
			if ($res->numRows())
			{
				$draw = $res->fetchRow(DB_FETCHMODE_ASSOC);
				$drawId = $draw['draw_id'];
			}
		}

		// Creation du tableau
		if ($drawId == -1)
		{
			$infos = array('draw_serialName' => 'Sénior',
                     'draw_oldName'    => '',
                     'draw_eventId'    => $eventId,
                     'draw_catage'     => $aCatage,
                     'draw_numcatage'  => 0,
      				 'draw_stamp'      => 'SEN',
                     'draw_rankdefId'  => $aRank,
		     		'tie_nbms' => $aDisci == WBS_MS ? 1:0,
		     		'tie_nbws' => $aDisci == WBS_WS ? 1:0,
		     		'tie_nbmd' => $aDisci == WBS_MD ? 1:0,
		     		'tie_nbwd' => $aDisci == WBS_WD ? 1:0,
		     		'tie_nbas' => 0,
		     		'tie_nbad' => 0,
		     		'tie_nbxd' => $aDisci == WBS_XD ? 1:0);

			// Add the draws
			require_once "draws/base_A.php";
			$dt = new DrawsBase_A();
			$dt->addSerial($infos);
			$where  = 'draw_disci = ' . $aDisci .
		 		' AND draw_catage = ' . $aCatage .
		 		' AND draw_eventid = ' . $eventId;
			if ($aRank>-1)	$where .= ' AND draw_rankdefid = ' . $aRank;
			$res = $this->_select('draws', 'draw_id', $where, false, 'draw');
			$draw = $res->fetchRow(DB_FETCHMODE_ASSOC);
			$drawId = $draw['draw_id'];
		}
		return $drawId;
	}

	function getTeamFromfede($aFedeId)
	{
		if(empty($aFedeId) || $aFedeId < 0) return -1;

		// Chercher l'asso correspondante
		$where = "asso_fedeid='".$aFedeId . "'";
		$cols = array('asso_id', 'asso_name', 'asso_stamp');
		$asso = $this->_selectFirst('assocs', $cols, $where);
		if (empty($asso) )
		{
			require_once "import/imppoona.php";
			$poona = new ImpPoona();
			$asso = $poona->getInstance($aFedeId);
			if ( !empty($asso) )
			{
				$asso['asso_lockid'] = -1;
				if ($asso['asso_type'] == '') $asso['asso_type'] = WBS_CLUB;
				if ($asso['asso_type'] != WBS_CLUB)	$asso['asso_dpt'] = '';
				unset($asso['asso_id']);
				$assoId = $this->_insert('assocs', $asso);
			}
			else $assoId = -1;
		}
		else $assoId = $asso['asso_id'];

		// Chercher l'equipe
		if ($assoId > 0)
		{
			$eventId = utvars::getEventId();
			$where = "a2t_assoid=".$assoId
			. ' AND a2t_teamid = team_id'
			. ' AND team_eventid =' . $eventId;
			$tables = array('teams', 'a2t');
			$teamId = $this->_selectFirst($tables, 'team_id', $where);
			if (empty($teamId) )
			{
				// Inscrire l'equipe
				$team = array('team_name'   =>$asso['asso_name'],
		    'team_stamp'  => $asso['asso_stamp'],
		    'team_noc'    => 'FRA',
			'team_cmt'    => 'Creation lors de inscription joueur',
		    'team_date'   => date('Y-m-d'),
			'team_assoId' => $assoId,
			'team_id' => -1
				);
				require_once "utils/utteam.php";
				$utt = new utTeam();
				$teamId= $utt->updateTeam($team);
			}
		}
		else $teamId = -1;
		return $teamId;
	}


	function getNbPlayers()
	{
		$where = "regi_eventId=".utvars::getEventId().
	" AND regi_type=".WBS_PLAYER.
	" AND regi_memberId >0";
		$order = '1';
		return $this->_selectFirst('registration', 'count(*)', $where);
	}

	/**
	 * reherche les joueurs en fonction des criteres
	 *
	 * @access private
	 * @return void
	 */
	function searchPlayers($critere, $isLicencie, $teamId=false)
	{
		$members = array();
		list($assoId, $instanceId) = explode(';', $critere['regi_instanceId']);

		if ($instanceId == -1) $critere['regi_instanceId'] = '';
		else $critere['regi_instanceId'] = $instanceId;

		if ($critere['mber_licence'] == '' &&
		$critere['mber_name'] == '' &&
		$critere['mber_sexe'] == '' &&
		$critere['regi_catage'] == '' &&
		$critere['asso_dpt'] == '' &&
		$critere['regi_instanceId'] == '')
		return;

		// Recherche des joueurs deja inscrits pour ne pas les proposer
		$regis = array();
		$regips = array();
		if ($teamId)
		{
	  $fields = array('mber_id', 'mber_fedeId');
	  $tables = array('registration', 'members');
	  $where = 'regi_eventId='.utvars::getEventId().
	    ' AND regi_memberId = mber_id'.
	    " AND regi_teamId = $teamId";
	  $res = $this->_select($tables, $fields, $where);
	  while ($mber = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  {
	  	$regis[]= $mber['mber_id'];
	  	$regips[]= "-1;{$mber['mber_fedeId']}";
	  }
		}

		// Chercher dans la base federale Poona
		$joueurs = array();
		$members = array();
		$pager = array('page'=>1, 'nb'=>100, 'nbmax'=>'0');
		if ($critere['searchType'] == 1)
		{
			require_once "import/imppoona.php";
			$poona = new ImpPoona();
			$acteurs = $poona->searchPlayer($critere, $isLicencie);
			if (isset($acteurs['errMsg']))
			{
				$members['errMsg'] = 'msgPoonaError';
				unset($acteurs['errMsg']);
			}
			if(!empty($acteurs))
			{
				$pager = array_shift($acteurs);
				foreach($acteurs as $acteur)
				if (!in_array($acteur['mber_id'], $regips))
				$members[] = $acteur;
			}
		}
		else
		{
			$glue = ''; $where = '';
			if ($critere['mber_name'] != '')
			{
				$where .= "$glue mber_secondname like '{$critere['mber_name']}%'";
				$glue = ' AND';
			}
			if ($critere['mber_sexe'] != '')
			{
				$where .= "$glue mber_sexe = {$critere['mber_sexe']}";
				$glue = ' AND';
			}
			if ($critere['mber_licence'] != '')
			{
				$licence = sprintf("%08d", $critere['mber_licence']);
				$where .= "$glue mber_licence = $licence";
				$glue = ' AND';
			}
			$fields = array('mber_id', 'mber_sexe',
 'mber_secondname', 'mber_firstname',
  'mber_licence', '"" as regi_catage',
			  '"" as team_stamp', 'mber_fedeId');
			$order = "3,4";

			$res = $this->_select('members', $fields, $where, $order);
			$ut = new utils();
			while ($mber = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if (!in_array($mber['mber_id'], $regis))
				{
					$mber['mber_id'] .= ";{$mber['mber_fedeId']}";
					$mber['mber_sexe'] = $ut->getSmaLabel($mber['mber_sexe']);
					$members[] = $mber;
					$idFede = "-1;{$mber['mber_fedeId']}";
					unset($members[$idFede]);
					unset($mber['mber_fedeId']);
				}
			}
		}

		if (!count($members))
		$members['errMsg'] = 'msgNotFound';
		$members['pager'] = $pager;
		return $members;
	}

	// {{{ getAssoIdFromTeam()
	/**
	* recherche l'association d'une equipe
	*
	* @access private
	* @return void
	*/
	function getAssoIdFromTeam($teamId)
	{
		$fields = array('asso_id', 'asso_fedeId');
		$tables = array('assocs', 'a2t');
		$where = "a2t_teamId = $teamId".
      " AND a2t_assoId = asso_id";
		$res = $this->_select($tables, $fields, $where);
		$asso = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$assoId = "{$asso['asso_id']};{$asso['asso_fedeId']}";
		return $assoId;
	}
	//}}}

	// {{{ absent
	/**
	* Update rank for player
	*
	* @access public
	* @param array   $ids  Ids to keep
	* @return none
	*/
	function absent($players, $status)
	{
		if (count($players))
		{
	  $fields['regi_present'] = $status;
	  $where = 'regi_id IN ('.implode(',', $players).')';
	  $this->_update('registration', $fields, $where);
		}
		return true;
	}
	// }}}

	/**
	 * Update rank for player
	 *
	 * @access public
	 * @param array   $ids  Ids to keep
	 * @return none
	 */
	function updateRankFromFede($teamId, $aPlayers)
	{
		// Recuperer les donnees des joueurs
		// de l'equipe
		$tables = array('members', 'registration');
		$fields = array('mber_id', 'mber_licence', 'mber_fedeid', 'regi_id',
		      'mber_sexe');
		$where = "regi_memberId = mber_id".
			" AND regi_eventId=".utvars::getEventId().
			" AND regi_type=".WBS_PLAYER.
			" AND mber_id>0";
		if (!empty($aPlayers) )
		{ 
			$where .= " AND regi_id IN(" . implode(',', $aPlayers) . ')';
		}
		else
		{
			if ($teamId > 0) $where .= " AND regi_teamId=$teamId";
		}
		$order = 'mber_secondname, mber_firstname';
		$res = $this->_select($tables, $fields,  $where, $order);
		if ($res->numRows())
		{
			while ($player = $res->fetchRow(DB_FETCHMODE_ASSOC)) $players[] = $player;
		}

		// Recuperer les donnees depuis poona
		require_once "import/imppoona.php";
		$poona = new ImpPoona();
		$res = $poona->updateRanks($players);
		return;
	}

	// Classement to Index
	function _getC2i()
	{
		$fields = array('rkdf_id', 'rkdf_label');
		$tables = array('events', 'rankdef');
		$where = "evnt_rankSystem=rkdf_system".
	" AND evnt_id=".utvars::getEventId();
		$res = $this->_select($tables, $fields, $where);
		while($infos = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $ranks[$infos['rkdf_label']] = $infos['rkdf_id'];
		}
		return $ranks;
	}
	// }}}


	// {{{ getRanks
	/**
	* Retrieve the list of rankings
	*
	* @access public
	* @return array   array of rankings
	*/
	function getRanks()
	{
		// Retreive all defined ranking
		$fields = array('rkdf_id', 'rkdf_label', 'rkdf_point');
		$tables = array('rankdef', 'events');
		$where = 'evnt_id='.utvars::getEventId().
	' AND  evnt_rankSystem=rkdf_system';
		$order  = "rkdf_point";
		$res = $this->_select($tables, $fields, $where, $order);
		$ranks=array();
		while ($rank = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $ranks[$rank[0]] = $rank[1];
		}
		return $ranks;
	}
	// }}}

	// {{{ getAssocs
	/**
	* Return the list of the associations
	*
	* @access public
	* @return array   array of associations
	*/
	function getAssocs()
	{
		$fields = array('asso_id', 'asso_name');
		$tables = array('assocs');
		$where  = "asso_del !=".WBS_DATA_DELETE;
		$order  = "2";
		$res = $this->_select($tables, $fields, $where, $order);
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $rows[$entry[0]] = $entry[1];
		}
		return $rows;
	}
	// }}}

	// {{{ getRegAssos
	/**
	* Return the list of the registered associations
	*
	* @access public
	* @return array   array of associations
	*/
	function getRegAssos()
	{
		$fields = array('DISTINCT asso_id', 'asso_fedeId', 'asso_name');
		$tables = array('assocs', 'a2t', 'teams');
		$where  = "asso_id = a2t_assoId".
	" AND a2t_teamId = team_id".
	" AND team_eventId=".utvars::getEventId();
		$order  = "2";
		$res = $this->_select($tables, $fields, $where, $order);
		$rows = array();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $rows["{$entry['asso_id']};{$entry['asso_fedeId']}"] =
	  $entry['asso_name'];
		}
		return $rows;
	}
	// }}}

	// {{{ getTeams
	/**
	* Return the list of the teams registered or the list of the
	* given match
	*
	* @access public
	* @return array   array of teams
	*/
	function getTeams()
	{
		$eventId = utvars::getEventId();
		$tables = array('teams');
		$where = "team_del !=".WBS_DATA_DELETE.
	" AND team_eventId = $eventId";

		$fields  =array('team_id', 'team_name');
		$order = "2";
		$res = $this->_select($tables, $fields, $where, $order);
		$rows = array();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $rows[$entry[0]] = $entry[1];
		}
		if (!count($rows))	$rows['errMsg'] = 'msgNoTeams';
		return $rows;
	}
	// }}}

	// {{{ getRegiTeams
	/**
	* Return the list of the players registered for a team event
	*
	* @access public
	* @param  string  $sort   criteria to sort users
	* @return array   array of users
	*/
	function getRegiTeams($sort, $first=0, $number=0)
	{
		// Retrieve registered players
		$eventId = utvars::getEventId();
		$fields = array('regi_id', 'regi_date', 'mber_id', 'mber_sexe',
		      'regi_longName', 'regi_catage', 'rkdf_label', 'mber_licence', 
		      'rank_rank', 'team_name', 'team_id', 
		      'regi_memberId', 'rank_isFede', 'rank_dateFede',
		      'regi_pbl', 'regi_del', 'team_del', 'team_pbl',
		      'mber_urlphoto', 'mber_born', 'team_noc', //'asso_noc',
		      'regi_dateauto', 'regi_surclasse', 'regi_datesurclasse',
				'regi_numcatage');
		//      $tables  = array('registration', 'teams', 'ranks', 'rankdef',
		//	       'members', 'a2t', 'assocs');
		$tables  = array('registration LEFT JOIN teams ON regi_teamId = team_id',
// LEFT JOIN a2t ON team_id = a2t_teamId LEFT JOIN assocs ON a2t_assoId = asso_id', 
		       'ranks', 'rankdef', 'members',);
		$where = "regi_del != ".WBS_DATA_DELETE.
	" AND regi_eventId = $eventId".
	" AND regi_memberId >= 0".
	" AND regi_id = rank_regiId".
	" AND rank_rankdefId = rkdf_id".
	" AND regi_memberId = mber_id";
		//" AND team_id = a2t_teamId".
		//" AND a2t_assoId = asso_id";
		if ($sort==6)
		{
		if ($sort > 0) $order = "$sort, regi_numcatage, regi_id, rank_disci ";
		else $order = abs($sort) . " desc, regi_numcatage, regi_id, rank_disci";
		}
		else
		{
		if ($sort > 0) $order = "$sort, regi_id, rank_disci ";
		else $order = abs($sort) . " desc, regi_id, rank_disci";
		}
		if ($number)
		{
			$number *= 3;
			$first *= 3;
			$order .= " LIMIT $first, $number";
		}
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
	  $rows['errMsg'] = 'msgNoRegisteredPlayer';
	  return $rows;
		}
		$id = -1;
		$tmp = array();
		$utd = new utdate();
		$ut = new utils();
		$uti = new utimg();
		$isSquash = $ut->getParam('issquash', false);
		// $tmp[10] contient le nombre de classement issu du
		// site de la fede. Si les trois classements sont issus
		// de la fede, on rajoute la source et la date apres le
		// classement
		$trans = array('team_name');
		while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{

			if ($id != $entry['regi_id'])
			{
				if($id > 0)
				{
					if ($tmp['rank_isFede'] == 3 || ($isSquash && $tmp['rank_isFede'] == 1))
					{
						$utd->setIsoDate($tmp['rank_dateFede']);
						$tmp['rkdf_label'] .= " (Fede ".$utd->getDate().")";
					}
					$tmp = $this->_getTranslate('teams', $trans,
					$tmp['team_id'], $tmp);
					$rows[] = $tmp;
				}
				$tmp = $entry;
				$tmp['mber_id'] = '';
				$tmp['mber_urlphoto'] = utimg::getPathPhoto($entry['mber_urlphoto']);

				$tmp['regi_pbl'] = $uti->getPubliIcon($entry['regi_del'],
				$entry['regi_pbl']);
				$tmp['team_pbl'] = $uti->getPubliIcon($entry['team_del'],
				$entry['team_pbl']);
				if ($tmp['regi_dateauto'] == '0000-00-00') $tmp['regi_dateauto'] = $uti->getIcon(WBS_NO);
				else $tmp['regi_dateauto'] = '';
				$utd->setIsoDate($entry['regi_date']);
				$tmp['regi_date'] = $utd->getDate();
				$utd->setIsoDate($entry['mber_born']);
				$tmp['mber_born'] = $utd->getDate();
				$tmp['mber_sexe'] = $ut->getSmaLabel($tmp['mber_sexe']);
				$catage = $tmp['regi_catage'];
				$tmp['regi_catage'] = $ut->getLabel($tmp['regi_catage']);
				if ($tmp['regi_numcatage'] && ($catage!= WBS_CATAGE_SEN))
				$tmp['regi_catage'] .= ' - ' . $tmp['regi_numcatage'];
				if ($tmp['regi_surclasse'] != WBS_SURCLASSE_NONE)
				$tmp['regi_catage'] .= " (".$ut->getLabel($tmp['regi_surclasse']).")";
				//if ($tmp['team_noc'] == '') $tmp['team_noc'] = $tmp['asso_noc'];


				$id = $entry['regi_id'];
			}
			else if (!$isSquash)
			{
				$tmp['rkdf_label'] .= ",{$entry['rkdf_label']}";
				$tmp['rank_rank'] .= ",{$entry['rank_rank']}";
				$tmp['rank_isFede'] += $entry['rank_isFede'];
			}
		}
		if ($tmp['rank_isFede'] == 3 || ($isSquash && $tmp['rank_isFede'] == 1))
		{
			$utd->setIsoDate($tmp['rank_dateFede']);
			$tmp['rkdf_label'] .= " (Fede ".$utd->getDate().")";
		}
		$tmp = $this->_getTranslate('teams', $trans,
		$tmp['team_id'], $tmp);
		$rows[] = $tmp;

		return $rows;
	}
	// }}}

	// {{{ getRegistrations
	/**
	* Return the list of the registrations
	*
	* @access public
	* @param  string  $sort   criteria to sort users
	* @return array   array of users
	*/
	function getRegistrations($sort)
	{
		// Retrieve registered players
		$fields = array('regi_id', 'regi_date', 'regi_longName',
		      'asso_name');
		$tables = array('registration',  'assocs');
		$where = "regi_teamId = asso_Id".
	"AND regi_del != ".WBS_DATA_DELETE;
		if ($sort > 0)
		$order = "$sort ";
		else
		$order = abs($sort) . " desc";

		$res = $this->_select($tables, $fields, $where, $order);
		$utd = new utdate();
		while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
		{
	  $utd->setIsoDate($entry[1]);
	  $entry[1] = $utd->getDate();
	  $rows[] = $entry;
		}
		return $rows;
	}
	// }}}

	// {{{ getMember
	/**
	* Return the column of a  member
	*
	* @access public
	* @return array   information of the registration if any
	*/
	function getMember($memberId, $fedeId)
	{
		$fields = array('mber_id', 'mber_firstname',
		      'mber_secondname', 'mber_ibfnumber', 'mber_licence',
		      'mber_born', 'mber_sexe', 'mber_urlphoto', 'mber_fedeId');
		$tables = array('members');
		if ($memberId != -1)
		$where = "mber_id = '$memberId'";
		else
		$where = "mber_fedeid = $fedeId";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		{
	  $player = array('mber_id'         => -1,
			  'mber_fedeId'     => -1,
			  'mber_sexe'       =>'',
			  'mber_secondname' =>'',
			  'mber_firstname'  =>'',
			  'mber_ibfnumber'  =>'',
			  'mber_licence'    =>'',
			  'mber_born'       =>'',
			  'mber_urlphoto'   =>'');
		}
		else
		{
	  $player = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $utd = new utdate();
	  $utd->setIsoDate($player['mber_born']);
	  $player['mber_born'] = $utd->getDate();
		}
		return $player;
	}
	// }}}


	// {{{ getRegiMember
	/**
	* Return the column of a registered member
	*
	* @access public
	* @param  string  $registrationId  id of the registration
	* @return array   information of the registration if any
	*/
	function getRegiMember($registrationId)
	{
		$fields = array('mber_id', 'mber_firstname',
		      'mber_secondname', 'mber_ibfnumber', 'mber_licence',
		      'mber_born', 'mber_sexe', 'mber_urlphoto', 'mber_fedeId');
		$tables = array('members', 'registration');
		$where = "regi_id = '$registrationId'".
	" AND mber_id = regi_memberId";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = 'msgErrorMember';
	  return $infos;
		}

		$player = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$utd = new utdate();
		$utd->setIsoDate($player['mber_born']);
		$player['mber_born'] = $utd->getDate();
		return $player;
	}
	// }}}

	// {{{ getRegiRegis
	/**
	* Return the column of a registration
	*
	* @access public
	* @param  string  $registrationId  id of the registration
	* @return array   information of the registration if any
	*/
	function getRegiRegis($registrationId)
	{
		$fields = array('regi_id', 'regi_date', 'regi_longName',
		      'regi_shortName', 'regi_type', 'regi_eventId',
		      'regi_teamId', 'regi_cmt', 'regi_accountId', 
		      'regi_function', 'regi_status', 'regi_noc',
		      'regi_catage', 'regi_dateauto', 'regi_surclasse',
		      'regi_datesurclasse', 'regi_numcatage');
		$tables = array('registration');
		$where = "regi_id = '$registrationId'";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		{
	  $infos['errMsg'] = 'msgTeamNeedClub';
	  return $infos;
		}

		$player = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$utd = new utdate();
		$utd->setIsoDate($player['regi_date']);
		$player['regi_date'] = $utd->getDate();
		$utd->setIsoDate($player['regi_dateauto']);
		$player['regi_dateauto'] = $utd->getDate();
		$utd->setIsoDate($player['regi_datesurclasse']);
		$player['regi_datesurclasse'] = $utd->getDate();
		return $player;
	}
	// }}}

	// {{{ getRegiRank
	/**
	* Return the column of a registration
	*
	* @access public
	* @param  string  $registrationId  id of the registration
	* @return array   information of the registration if any
	*/
	function getRegiRank($registrationId)
	{
		$fields = array('rank_rankdefId', 'rank_disci', 'rank_average',
		      'rank_isFede', 'rank_dateFede', 'rank_updt', 'rank_rank');
		$tables = array('ranks');
		$where  = "rank_regiId = '$registrationId'";
		$order  = "2";
		$res = $this->_select($tables, $fields, $where, $order);
		$player['rank_averageS']=0;
		$player['rank_averageD']=0;
		$player['rank_averageM']=0;
		$player['rank_rankS']=0;
		$player['rank_rankD']=0;
		$player['rank_rankM']=0;
		$player['rank_isFedeS']=0;
		$player['rank_isFedeD']=0;
		$player['rank_isFedeM']=0;
		$player['rank_dateS']='';
		$player['rank_dateD']='';
		$player['rank_dateM']='';
		$player['rank_classeS']=999999;
		$player['rank_classeD']=999999;
		$player['rank_classeM']=999999;
		$utd = new utdate();
		while ($rank = $res->fetchRow(DB_FETCHMODE_ASSOC))

		{
	  if ($rank['rank_isFede'])
	  $utd->setIsoDateTime($rank['rank_dateFede']);
	  else
	  $utd->setTimeStamp($rank['rank_updt']);
	  $lastUpdt = $utd->getDate();
	  switch ($rank['rank_disci'])
	  {
	  	case WBS_MS:
	  	case WBS_LS:
	  	case WBS_AS:
	  		$player['rank_averageS']=$rank['rank_average'];
	  		$player['rank_rankS']=$rank['rank_rankdefId'];
	  		$player['rank_isFedeS']=$rank['rank_isFede'];
	  		$player['rank_classeS']=$rank['rank_rank'];
	  		$player['rank_dateS'] = $lastUpdt;
	  		break;
	  	case WBS_MD:
	  	case WBS_LD:
	  		$player['rank_averageD']=$rank['rank_average'];
	  		$player['rank_rankD']=$rank['rank_rankdefId'];
	  		$player['rank_isFedeD']=$rank['rank_isFede'];
	  		$player['rank_classeD']=$rank['rank_rank'];
	  		$player['rank_dateD'] = $lastUpdt;
	  		break;
	  	case WBS_MX:
	  		$player['rank_averageM']=$rank['rank_average'];
	  		$player['rank_rankM']=$rank['rank_rankdefId'];
	  		$player['rank_isFedeM']=$rank['rank_isFede'];
	  		$player['rank_classeM']=$rank['rank_rank'];
	  		$player['rank_dateM'] = $lastUpdt;
	  		break;
	  }
		}
		return $player;
	}
	// }}}

	// {{{ getRegiPlay
	/**
	* Return the column of a registration
	*
	* @access public
	* @param  string  $registrationId  id of the registration
	* @return array   information of the registration if any
	*/
	function getRegiPlay($regiId)
	{
		$fields = array('pair_drawId', 'pair_disci', 'pair_id');
		$tables = array('pairs', 'i2p');
		$where  = "i2p_regiId = $regiId".
	" AND i2p_pairId=pair_id";
		$order  = "2,1 DESC";
		$res = $this->_select($tables, $fields, $where, $order);
		$play['single']        = -1;
		$play['oldSinglePair'] = -1;
		$play['double']        = -1;
		$play['doublePair']    = -1;
		$play['oldDoublePair'] = -1;
		$play['mixed']         = -1;
		$play['mixedPair']     = -1;
		$play['oldMixedPair']  = -1;
		while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  switch ($data['pair_disci'])
	  {
	  	case WBS_SINGLE:
	  		$play['single']        = $data['pair_drawId'];
	  		$play['oldSinglePair'] = $data['pair_id'];
	  		break;
	  	case WBS_DOUBLE:
	  		$play['double']        = $data['pair_drawId'];
	  		$play['doublePair']    = $data['pair_id'];
	  		$play['oldDoublePair'] = $data['pair_id'];
	  		break;
	  	case WBS_MIXED:
	  		$play['mixed']        = $data['pair_drawId'];
	  		$play['mixedPair']    = $data['pair_id'];
	  		$play['oldMixedPair'] = $data['pair_id'];
	  		break;
	  }
		}
		return $play;
	}
	// }}}


	function getCatage($aBorn)
	{
		$ute = new utevent();
		$event = $ute->getEvent(utvars::getEventId());
		$catages = array(WBS_CATAGE_POU => 11,
		WBS_CATAGE_BEN => 13,
		WBS_CATAGE_MIN => 15,
		WBS_CATAGE_CAD => 17,
		WBS_CATAGE_JUN => 19,
		WBS_CATAGE_SEN => 35,
		WBS_CATAGE_VET => 65
		);
		list($day, $month, $year) =	sscanf($aBorn, "%02u-%02u-%04u");

		list($lastYear, $lastMonth, $lastDay) =
		sscanf($event['evnt_lastday'], "%04u-%02u-%02u %02u:%02u:%02u");

		$annees = $lastYear - $year;
		if ($lastMonth <= $month) {
			if ($month == $lastMonth)
			{
				if ($day > $lastDay)
				$annees--;
			}
			else
			$annees--;
		}

		$catage = WBS_CATAGE_SEN;
		if ($annees < $catages[WBS_CATAGE_POU]) $catage = WBS_CATAGE_POU;
		else if ($annees < $catages[WBS_CATAGE_BEN]) $catage = WBS_CATAGE_BEN;
		else if ($annees < $catages[WBS_CATAGE_MIN]) $catage = WBS_CATAGE_MIN;
		else if ($annees < $catages[WBS_CATAGE_CAD]) $catage = WBS_CATAGE_CAD;
		else if ($annees < $catages[WBS_CATAGE_JUN]) $catage = WBS_CATAGE_JUN;
		else if ($annees < $catages[WBS_CATAGE_SEN]) $catage = WBS_CATAGE_SEN;
		else if ($annees < $catages[WBS_CATAGE_VET]) $catage = WBS_CATAGE_VET;
		return $catage;
	}

	// {{{ updateRegistration
	/**
	* Add or update a registration into the database
	*
	* @access public
	* @param  string  $info   column of the registration
	* @return mixed
	*/
	function updateRegistration($member, $regi, $rank, $play)
	{
		// Create or updating a registration
		$utd = new utdate();
		$utd->setFrDate($regi['regi_date']);
		$regi['regi_date'] = $utd->getIsoDate();
		$utd->setFrDate($regi['regi_dateauto']);
		$regi['regi_dateauto'] = $utd->getIsoDate();
		$utd->setFrDate($regi['regi_datesurclasse']);
		$regi['regi_datesurclasse'] = $utd->getIsoDate();

		$ut = new utils();
		$isSquash = $ut->getParam('issquash', false);
		if ($isSquash)
		{
			$regi['regi_catage'] = $this->getCatage($member['mber_born']);
		}

		// Update administrative infos
		$regi['regi_memberId'] = $member['mber_id'];
		if ($regi['regi_id'] != -1)
		{
			$where = " regi_id=".$regi['regi_id'];
			$res = $this->_update('registration', $regi, $where);
		}
		else
		{
			// Create a personnal account for the member
			if ($regi['regi_accountId']==-2)
			{
				$fields = array();
				$fields['cunt_name'] = $regi['regi_longName'];
				$fields['cunt_code'] = 'PERSO';
				$fields['cunt_eventId'] = utvars::getEventId();
				$tables = array('account');
				$res = $this->_insert('accounts', $fields);
				$regi['regi_accountId'] = $res;
			}
			else
			{
				// Find the account Id of the team
				$fields = array('team_accountId');
				$tables = array('teams');
				$where  = "team_id =".$regi['regi_teamId'];
				$res = $this->_select($tables, $fields, $where);
				$team = $res->fetchRow(DB_FETCHMODE_ASSOC);
				$regi['regi_accountId'] = $team['team_accountId'];
			}
			unset($regi['regi_id']);
			$res = $this->_insert('registration', $regi);
			$regi['regi_id'] = $res;
		}

		if ($regi['regi_type'] == WBS_PLAYER)
		{
			// Update rankings
			$fields = array();
			$fields['rank_regiId'] = $regi['regi_id'];
			$fields['rank_disci'] = $member['mber_sexe'] == WBS_MALE ? WBS_MS:WBS_LS;
			$fields['rank_discipline'] = WBS_SINGLE;
			$fields['rank_rankdefId'] = $rank['rank_rankS'];
			$fields['rank_average'] = $rank['rank_averageS'];
			$fields['rank_isFede'] = $rank['rank_isFedeS'];
			$fields['rank_rank'] = $rank['rank_classeS'];
			$utd->setFrDate($rank['rank_dateS']);
			$fields['rank_dateFede'] = $utd->getIsoDate();
			$res = $this->_updateRank($fields);
			if (is_array($res))
			{
				$infos['errMsg'] = $res['errMsg'];
				return $infos;
			}

			$fields['rank_disci'] = $member['mber_sexe'] == WBS_MALE ? WBS_MD:WBS_LD;
			$fields['rank_discipline'] = WBS_DOUBLE;
			$fields['rank_rankdefId'] = $rank['rank_rankD'];
			$fields['rank_average'] = $rank['rank_averageD'];
			$fields['rank_isFede'] = $rank['rank_isFedeD'];
			$fields['rank_rank'] = $rank['rank_classeD'];
			$res = $this->_updateRank($fields);
			if (is_array($res))
			{
				$infos['errMsg'] = $res['errMsg'];
				return $infos;
			}

			$fields['rank_disci'] = WBS_MX;
			$fields['rank_discipline'] = WBS_MIXED;
			$fields['rank_rankdefId'] = $rank['rank_rankM'];
			$fields['rank_average'] = $rank['rank_averageM'];
			$fields['rank_isFede'] = $rank['rank_isFedeM'];
			$fields['rank_rank'] = $rank['rank_classeM'];
			$res = $this->_updateRank($fields);
			if (is_array($res))
			{
				$infos['errMsg'] = $res['errMsg'];
				return $infos;
			}

			// Update draws for individual event
			if (!utvars::isTeamEvent())
			{
				$column['pair_drawId'] = $play['single'];
				$column['pair_disci']  = WBS_SINGLE;
				$column['pair_ibfNum'] = $member['mber_ibfnumber'];
				$column['pair_natNum'] = $member['mber_licence'];
				$column['pair_state']  = WBS_PAIRSTATE_REG;
				$column['pair_natrank'] = $rank['rank_classeS'];
				$res = $this->_updatePair($regi['regi_id'], $play['oldSinglePair'], $play['oldSinglePair'], $column);
				if (is_array($res))
				{
					$infos['errMsg'] = $res['errMsg'];
					return $infos;
				}
				 
				$column = array();
				$column['pair_drawId'] = $play['double'];
				$column['pair_disci']  = WBS_DOUBLE;
				$column['pair_ibfNum'] = '';
				$column['pair_natNum'] = '';
				$column['pair_state']   = WBS_PAIRSTATE_NOK;
				$column['pair_natrank'] = $rank['rank_classeD'];
				$res = $this->_updatePair($regi['regi_id'], $play['oldDoublePair'], $play['doublePair'], $column);
				if (is_array($res))
				{
					$infos['errMsg'] = $res['errMsg'];
					return $infos;
				}
				 
				$column['pair_drawId'] = $play['mixed'];
				$column['pair_disci']  = WBS_MIXED;
				$column['pair_state']   = WBS_PAIRSTATE_NOK;
				$column['pair_natrank'] = $rank['rank_classeM'];
				$res = $this->_updatePair($regi['regi_id'], $play['oldMixedPair'], $play['mixedPair'], $column);
				if (is_array($res))
				{
					$infos['errMsg'] = $res['errMsg'];
					return $infos;
				}

				// update entry fees
				require_once "utils/utfees.php";
				$utf = new utfees();
				$utf->updateRegiFees($regi['regi_id']);
				 
				// Controle du nombre de tableau et ajout d'un message
				$fields = array('regi_id', 'regi_longName', 'count(*) as nb');
				$tables = array('registration', 'i2p', 'pairs');
				$where = "regi_id = i2p_regiId".
					" AND i2p_pairId = pair_id".
					" AND regi_id  = {$regi['regi_id']}".
					" AND pair_drawId != -1".
					" GROUP BY regi_id";
				$res = $this->_select($tables, $fields, $where);
				$regi = $res->fetchRow(DB_FETCHMODE_ASSOC);
				$ute = new utevent();
				$event = $ute->getEvent(utvars::getEventId());
				if ($regi['nb'] > $event['evnt_nbdrawmax'])
				{
					$cols = array();
					$cols['psit_title'] = "Trop de tableau";
					$cols['psit_page'] = "regi";
					$cols['psit_action'] = KID_EDIT;
					$cols['psit_type'] = WBS_POSTIT_TOODRAW;
					$cols['psit_eventId'] = utvars::getEventId();
					$cols['psit_texte'] = $regi['regi_longName'];
					$cols['psit_data'] = $regi['regi_id'];
					$this->_insert('postits', $cols);
				}
			}
			else
			{
				$where = "psit_type=".WBS_POSTIT_TOODRAW.
				" AND psit_data = {$regi['regi_id']}";
				$this->_delete('postits', $where);

			}
		}
		return $regi['regi_id'];
	}
	// }}}

	// {{{ _updatePair
	/**
	* Add or update a draw for a player
	*
	* @access private
	* @param  array   $infos Rankings data
	* @return mixed
	*/
	function _updatePair($regiId, $oldPairId, $pairId, $cols)
	{
		// Verifier que les paires ne sont pas dans un match
		// en cas de changement de tableau
		$fields = array('p2m_id', 'pair_drawId');
		$tables = array('p2m', 'matchs', 'pairs');
		$where = "(p2m_pairId = $pairId ".
			" OR p2m_pairId = $oldPairId)".
			" AND p2m_pairId = pair_id".
			" AND p2m_matchId = mtch_id";
		//	" AND mtch_status >".WBS_MATCH_LIVE;
		$res = $this->_select($tables, $fields, $where);
		if ($res->numRows())
		{
			$pair = $res->fetchrow(DB_FETCHMODE_ASSOC);
			if($pair['pair_drawId'] != $cols['pair_drawId'])
			{
				$err['errMsg'] = 'msgPairInMatch';
				return $err;
			}
		}

		$colonnes = array('rank_rankdefId, rank_rank, rank_average');
		$oudonc = 'rank_regiid =' . $regiId;
		$oudonc .= ' AND rank_discipline = ' . $cols['pair_disci'];
		$ranks = $this->_getRow('ranks', $colonnes, $oudonc);

		// Pas de changement de paire (toujours vrai pour les simples)
		//echo "Traitemment de $regiId<br>";
		if ($oldPairId == $pairId)
		{
			//echo "oldPairId == pairId == $pairId<br>";
			// Nouvelle paire
			if ($pairId == -1)
			{
				$cols['pair_status']  = WBS_PAIR_NONE;
				$id = $this->_insert('pairs', $cols);
				$column = array();
				$column['i2p_pairId'] = $id;
				$column['i2p_regiId'] = $regiId;
				$column['i2p_rankdefid'] = $ranks['rank_rankdefId'];
				$column['i2p_cppp'] = $ranks['rank_average'];
				$column['i2p_classe'] = $ranks['rank_rank'];
				$pid = $this->_insert('i2p', $column);
				//	echo "Paire creee == $id<br>";
			}
			// Mise a jour de l'existant
			else
			{
				unset($cols['pair_state']);
				$where = "pair_id = {$pairId}";
				$this->_update('pairs', $cols, $where);
				//	echo "paire mise a jour == $pairId<br>";
			}
		}
		// En double uniquement
		else
		{
			// Nouvelle inscription: utiliser la paire du partenaire
			if ($oldPairId == -1)
			{
				$column = array();
				$column['i2p_pairId'] = $pairId;
				$column['i2p_regiId'] = $regiId;
				$column['i2p_rankdefid'] = $ranks['rank_rankdefId'];
				$column['i2p_cppp'] = $ranks['rank_average'];
				$column['i2p_classe'] = $ranks['rank_rank'];
				$this->_insert('i2p', $column);
				$cols['pair_state']  = WBS_PAIRSTATE_REG;
				$where = "pair_id = {$pairId}";
			}
			else
			{
				// Eclater l'ancienne paire
				require_once "utils/utpair.php";
				$utp = new utpair();
				$newPairId = $utp->explode($oldPairId, $regiId);

				// Avec un partenaire: reunir les deux paires
				if($pairId != -1)
				{
					// Mettre a jour l'ancienne paire pour avoir
					// le bon tableau. Sinon la reunion echoue
					$where = "pair_id = {$oldPairId}";
					$this->_update('pairs', $cols, $where);
					$pid = $utp->implode($oldPairId, $pairId);
					$cols['pair_state']  = WBS_PAIRSTATE_REG;
				}
				$where = "pair_id = {$oldPairId}";
			}
			$this->_update('pairs', $cols, $where);
		}
	}
	// }}}

	/**
	 * Add or update a rank for a player
	 *
	 * @access private
	 * @param  array   $infos Rankings data
	 * @return mixed
	 */
	function _updateRank($infos, $isFromFede = false)
	{
		// Select the ranking of registration
		$fields = array('rank_id', 'rank_average', 'rank_rankdefId',
		      'rank_isFede', 'rank_disci', 'rank_discipline', 'rank_rank');
		$tables = array('ranks');
		switch($infos['rank_disci'])
		{
			case WBS_MS:
			case WBS_LS:
				$where  = "(rank_disci=" . WBS_MS . " OR rank_disci=".WBS_LS.')';
				break;
			case WBS_MD:
			case WBS_LD:
				$where  = "(rank_disci=" . WBS_MD . " OR rank_disci=".WBS_LD.')';
				break;
			default:
				$where  = "rank_disci=".WBS_MX;
				break;
		}
		//$where  = "rank_disci=".$infos['rank_disci'];
		$where .= " AND rank_regiId=".$infos['rank_regiId'];
		$res = $this->_select($tables, $fields, $where);
		// Create or Update the rank
		if ($res->numRows())
		{
			$data = $res->fetchrow(DB_FETCHMODE_ASSOC);
			$where = "rank_id=". $data['rank_id'];
			if ($isFromFede) $res = $this->_update('ranks', $infos, $where);
			else
			{
				if ($data['rank_average'] != $infos['rank_average'] ||
				$data['rank_rankdefId'] != $infos['rank_rankdefId'])
				$infos['rank_isFede'] = 0;
				$res = $this->_update('ranks', $infos, $where);
			}
			// Mise a jour des relations avec les paires
			if (!utvars::isTeamEvent())
			{
				$tables = array('i2p', 'pairs');
				$where = 'i2p_pairid=pair_id';
				$where .= ' AND i2p_regiid=' . $infos['rank_regiId'];
				$where .= ' AND pair_disci=' . $infos['rank_discipline'];
				$i2pId = $this->_selectFirst($tables, 'i2p_id', $where);

				if ( !empty($i2pId) )
				{
					$where = 'i2p_id=' . $i2pId;
					$cols['i2p_rankdefid'] = $infos['rank_rankdefId'];
					$cols['i2p_cppp'] = $infos['rank_average'];
					$cols['i2p_classe'] = $infos['rank_rank'];
					$this->_update('i2p', $cols, $where);
				}
			}
			 
		}
		else $res = $this->_insert('ranks', $infos);
		return true;
	}

	// {{{ delRegistrations
	/**
	* Logical delete some registrations
	*
	* @access public
	* @param  arrays  $registrations   id's of the registrations to delete
	* @return mixed
	*/
	function delRegistrations($registrations)
	{
		foreach ( $registrations as $regi => $id)
		{
	  // Look at matches of concerned player
	  $fields = array('i2p_pairId');
	  $tables = array('i2p', 'pairs', 'p2m');
	  $where = " i2p_regiId=$id".
	    " AND i2p_pairId =pair_id".
	    " AND pair_id = p2m_pairId";
	  $res = $this->_select($tables, $fields, $where);

	  // The player has match! Can't delete his registration
	  if($res->numRows())
	  {
	  	$err = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	$err['errMsg'] ='msgRegiPlay';
	  	return $err;
	  }

	  // Look at commands of concerned player
	  $fields = array('cmd_id');
	  $tables = array('commands');
	  $where = " cmd_regiId=$id";
	  $res = $this->_select($tables, $fields, $where);

	  // The player has commands! Can't delete his registration
	  if($res->numRows())
	  {
	  	$err = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	$err['errMsg'] ='msgRegiCommands';
	  	return $err;
	  }

	  // Delete his registration
	  $where = "regi_id=$id ";
	  $res = $this->_delete('registration', $where);

	  // Delete his ranking
	  $where = "rank_regiId=$id ";
	  $res = $this->_delete('ranks', $where);

	  // Delete his pairs
	  $fields = array('pair_id');
	  $tables = array('i2p', 'pairs');
	  $where = " i2p_pairId=pair_id".
	    " AND i2p_regiId=$id";
	  $res = $this->_select($tables, $fields, $where);
	  if($res->numRows())
	  {
	  	// Delete relation with pairs
	  	$where = "i2p_regiId = $id ";
	  	$this->_delete('i2p', $where);

	  	// Delete pairs only if there is no other player
	  	while ($tmp = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  	{
	  		$where = "i2p_pairId = {$tmp['pair_id']} ";
	  		$who = $this->_selectFirst('i2p', 'i2p_id', $where);
	  		if (is_null($who))
	  		{
	  			$where = "pair_id = {$tmp['pair_id']}";
	  			$this->_delete('pairs', $where);

	  			$where = "p2m_pairId = {$tmp['pair_id']}";
	  			$this->_delete('p2m', $where);
	  		}
	  	}
	  }

		}
		return true;

	}
	// }}}
}
?>
