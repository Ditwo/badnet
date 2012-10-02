<?php
/*****************************************************************************
 !   Module     : Line
 !   File       : $Source: /cvsroot/aotb/badnet/src/regi/base_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.33 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/03/06 18:02:10 $
 ******************************************************************************/
require_once dirname(__FILE__)."/../utils/utbase.php";

/**
 * Acces to the dababase for events
 *
 * @see to follow
 *
 */
class lineBase_A extends utBase
{

	function getFullRegistrations($assoId, $full = false)
	{
		$eventId = $eventId = utvars::getEventId();
		$this->_prefix = 'asso_';

		// Recuperer les inscriptions
		$where = "lreg_eventId = $eventId".
    " AND lreg_assoId = $assoId".
    " AND lreg_datesoumis != '0000-00-00 00:00:00'";
		if ( $full ) $where .= " AND lreg_datereg = '0000-00-00 00:00:00'";
		$regis = $this->_getRows('lineregi', '*', $where);

		$this->_prefix = utvars::getPrfx().'_';
		return $regis;
	}

	function validRegistration($lregId)
	{
		$this->_prefix = 'asso_';
		// Modifier la date des inscriptions
		$cols = array('lreg_datereg' => date('Y-m-d H:i:s'));
		$where = "lreg_id = $lregId";
		$this->_update('lineregi', $cols, $where);
		$this->_prefix = utvars::getPrfx().'_';
		return true;
	}

	function _getRegistrations($assoId, $assos)
	{
		$eventId = $eventId = utvars::getEventId();

		// Tournoi concerne
		$where = "evnt_id = $eventId";
		//    " AND DATE(evnt_deadline) >= CURDATE()+ 0";
		$cols = array('evnt_id', 'evnt_name', 'evnt_ranksystem', 'evnt_nbdrawmax',
		"DATE(evnt_deadline) as datelimite"); 
		$event = $this->_getRow('events', $cols, $where);

		// Tarifs du tournoi
		$fees = array( 'IS' => '0.00',
		 'ID' => '0.00',
		 'IM' => '0.00',
		 'I0' => '0.00',
		 'I1' => '0.00',
		 'I2' => '0.00',
		 'I3' => '0.00');
		$fields = array('item_id', 'item_value', 'item_code',
		  'item_name');
		$where = "item_eventId={$eventId}".
    " AND item_rubrikId=-2";
		$res = $this->_select('items', $fields, $where);
		while($item = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$fees[$item['item_code']] = $item['item_value'];

		// Tableaux du tournoi
		$where = "draw_eventId = $eventId";
		$cols = array('draw_id', 'draw_stamp');
		$res = $this->_getRows('draws', $cols, $where);
		foreach($res as $draw)
		$draws[$draw['draw_id']] = $draw['draw_stamp'];
		$draws[-1] = 'Non';
		$draws[0] = 'Non';

		// Classement
		$where = "rkdf_system = {$event['evnt_ranksystem']}";
		$cols = array('rkdf_id', 'rkdf_label');
		$res = $this->_getRows('rankdef', $cols, $where);
		foreach($res as $rank)
		$levels[$rank['rkdf_id']] = $rank['rkdf_label'];

		// Inscriptions en preparation
		$regis = array();

		$this->_prefix = 'asso_';
		$cols = array('lreg_id as lregiid',
		"DATE(lreg_datesoumis) as datesoumis",
		"DATE(lreg_datereg) as datereg",
		'lreg_familyname', 'lreg_firstname',
		'lreg_singlelevelid', 'lreg_singledrawid',
		'lreg_doubledrawid',  'lreg_doublepartner',
		'lreg_mixeddrawid',   'lreg_mixedpartner',
		'lreg_doublelevelid', 'lreg_mixedlevelid',
		'lreg_mixedpartnername', 'lreg_doublepartnername',
		'lreg_cmt', 'lreg_userid');
		$where = "lreg_eventId = $eventId".
    " AND lreg_assoId = $assoId".
    " AND lreg_datesoumis = '0000-00-00 00:00:00'";
		$tables = array('lineregi');
		$res = $this->_getRows($tables, $cols, $where);
		$uti = new utimg();
		foreach($res as $regi)
		{
			$regi['lreg_singlelevelid'] =  "{$levels[$regi['lreg_singlelevelid']]};{$levels[$regi['lreg_doublelevelid']]};{$levels[$regi['lreg_mixedlevelid']]}";

			$fee = 0; $nb = 0;
			if($regi['lreg_singledrawid'] > 0)
			{ $fee += $fees['IS']; $nb++; }
			if($regi['lreg_doubledrawid'] > 0)
			{ $fee += $fees['ID']; $nb++; }
			if($regi['lreg_mixeddrawid'] > 0)
			{ $fee += $fees['IM']; $nb++; }
			$regi['fees'] = $fee + $fees["I{$nb}"];

			$regi['lreg_singledrawid']  =  $draws[$regi['lreg_singledrawid']];
			$regi['lreg_doubledrawid']  =  $draws[$regi['lreg_doubledrawid']];
			$regi['lreg_mixeddrawid']   =  $draws[$regi['lreg_mixeddrawid']];
			if (empty($regi['lreg_doublepartner']) && $regi['lreg_doubledrawid'] > 0)
			$regi['lreg_doublepartner'] = "Recherche";
			else
			$regi['lreg_doublepartner']  =  $regi['lreg_doublepartnername'] . ' ' . $regi['lreg_doublepartner'];
			if (empty($regi['lreg_mixedpartner']) && $regi['lreg_mixeddrawid'] > 0)
			$regi['lreg_mixedpartner'] = "Recherche";
			else
			$regi['lreg_mixedpartner']  =  $regi['lreg_mixedpartnername'] . ' ' . $regi['lreg_mixedpartner'];

			if( !empty($regi['lreg_cmt']))
			{
				$regi['title'] = $regi['lreg_cmt'];
				$regi['indic'] = $uti->getIcon(1265);
			}
			unset($regi['datesoumis']);
			unset($regi['datereg']);
			unset($regi['lreg_doublelevelid']);
			unset($regi['lreg_mixedlevelid']);
			unset($regi['lreg_mixedpartnername']);
			unset($regi['lreg_doublepartnername']);
			unset($regi['lreg_cmt']);
			$userId = $regi['lreg_userid'];
			unset($regi['lreg_userid']);
			$regi['lreg_userid'] = $userId;
			$regis[] = $regi;
		}


		// Inscriptions soumises
		$regis2 = array();
		$where = "lreg_eventId = $eventId".
    " AND lreg_assoId = $assoId".
    " AND lreg_datesoumis != '0000-00-00 00:00:00'".
    " AND lreg_datereg = '0000-00-00 00:00:00'";
		$res = $this->_getRows('lineregi', $cols, $where);
		foreach($res as $regi)
		{
			$regi['lreg_singlelevelid'] =  "{$levels[$regi['lreg_singlelevelid']]};{$levels[$regi['lreg_doublelevelid']]};{$levels[$regi['lreg_mixedlevelid']]}";
			//$regi['lreg_datesoumis'] = substr($regi['lreg_datesoumis'],3,8);

			$fee = 0; $nb = 0;
			if($regi['lreg_singledrawid'] > 0)
			{ $fee += $fees['IS']; $nb++; }
			if($regi['lreg_doubledrawid'] > 0)
			{ $fee += $fees['ID']; $nb++; }
			if($regi['lreg_mixeddrawid'] > 0)
			{ $fee += $fees['IM']; $nb++; }
			$regi['fees'] = $fee + $fees["I{$nb}"];

			if (empty($regi['lreg_doublepartner']) && $regi['lreg_doubledrawid'] > 0)
			$regi['lreg_doublepartner'] = "Recherche";
			else
			$regi['lreg_doublepartner']  = $regi['lreg_doublepartnername'] . ' ' . $regi['lreg_doublepartner'];
			if (empty($regi['lreg_mixedpartner']) && $regi['lreg_mixeddrawid'] > 0)
			$regi['lreg_mixedpartner'] = "Recherche";
			else
			$regi['lreg_mixedpartner']  =  $regi['lreg_mixedpartnername'] . ' ' . $regi['lreg_mixedpartner'];
			$regi['lreg_singledrawid']  =  $draws[$regi['lreg_singledrawid']];
			$regi['lreg_doubledrawid']  =  $draws[$regi['lreg_doubledrawid']];
			$regi['lreg_mixeddrawid']   =  $draws[$regi['lreg_mixeddrawid']];

			if( !empty($regi['lreg_cmt']))
			{
				$regi['title'] = $regi['lreg_cmt'];
				$regi['indic'] = $uti->getIcon(1265);
			}
			unset($regi['lreg_doublelevelid']);
			unset($regi['lreg_mixedlevelid']);
			unset($regi['datereg']);
			unset($regi['lreg_mixedpartnername']);
			unset($regi['lreg_doublepartnername']);
			unset($regi['lreg_cmt']);
			$userId = $regi['lreg_userid'];
			unset($regi['lreg_userid']);
			$regi['lreg_userid'] = $userId;
			$regis2[] = $regi;
		}

		// Inscriptions acquites par l'organisateur
		$regis3 = array();
		$where = "lreg_eventId = $eventId".
    " AND lreg_assoId = $assoId".
    " AND lreg_datesoumis != '0000-00-00 00:00:00'".
    " AND lreg_datereg != '0000-00-00 00:00:00'";
		$res = $this->_getRows('lineregi', $cols, $where);
		$regis3 = array();
		foreach($res as $regi)
		{
			$regi['lreg_singlelevelid'] =  "{$levels[$regi['lreg_singlelevelid']]};{$levels[$regi['lreg_doublelevelid']]};{$levels[$regi['lreg_mixedlevelid']]}";
			//$regi['lreg_datesoumis'] = substr($regi['lreg_datesoumis'],3,8);
			//$regi['lreg_datereg']    = substr($regi['lreg_datereg'],3,8);

			$fee = 0; $nb = 0;
			if($regi['lreg_singledrawid'] > 0)
			{ $fee += $fees['IS']; $nb++; }
			if($regi['lreg_doubledrawid'] > 0)
			{ $fee += $fees['ID']; $nb++; }
			if($regi['lreg_mixeddrawid'] >0)
			{ $fee += $fees['IM']; $nb++; }
			$regi['fees'] = $fee + $fees["I{$nb}"];

			if (empty($regi['lreg_doublepartner']) && $regi['lreg_doubledrawid'] > 0)
			$regi['lreg_doublepartner'] = "Recherche";
			else
			$regi['lreg_doublepartner']  =  $regi['lreg_doublepartnername'] . ' ' . $regi['lreg_doublepartner'];
			if (empty($regi['lreg_mixedpartner']) && $regi['lreg_mixeddrawid'] > 0)
			$regi['lreg_mixedpartner'] = "Recherche";
			else $regi['lreg_mixedpartner']  =  $regi['lreg_mixedpartnername'] . ' ' . $regi['lreg_mixedpartner'];
			
			if( isset($draws[$regi['lreg_singledrawid']]))
			$regi['lreg_singledrawid']  =  $draws[$regi['lreg_singledrawid']];
			else
			$regi['lreg_singledrawid']  =  '';
			if( isset($draws[$regi['lreg_doubledrawid']]))
			$regi['lreg_doubledrawid']  =  $draws[$regi['lreg_doubledrawid']];
			else
			$regi['lreg_doubledrawid']  =  '';
			if( isset($draws[$regi['lreg_mixeddrawid']]))
			$regi['lreg_mixeddrawid']   =  $draws[$regi['lreg_mixeddrawid']];
			else
			$regi['lreg_mixeddrawid']  =  '';
			
			if( !empty($regi['lreg_cmt']))
			{
				$regi['title'] = $regi['lreg_cmt'];
				$regi['indic'] = $uti->getIcon(1265);
			}

			unset($regi['lreg_doublelevelid']);
			unset($regi['lreg_mixedlevelid']);
			unset($regi['lreg_mixedpartnername']);
			unset($regi['lreg_doublepartnername']);
			unset($regi['lreg_cmt']);
			$userId = $regi['lreg_userid'];
			unset($regi['lreg_userid']);
			$regi['lreg_userid'] = $userId;

			$regis3[] = $regi;
		}
		$this->_prefix = utvars::getPrfx().'_';

		$data['assos'] = $assos;
		$data['event'] = $event;
		$data['regi1'] = $regis;
		$data['regi2'] = $regis2;
		$data['regi3'] = $regis3;
		$data['fees']  = $fees;
		$data['where']  = $where;
		return $data;
	}

	function getProposedRegistrations($assoId)
	{
		// Recuperer les inscriptions
		$result = $this->_getRegistrations($assoId, null);

		// Recuperer les coordonnees du correspondant : le responsable du club
		$where = 'rght_theme =3'.
    " AND rght_status ='M'".
    " AND rght_themeId = {$assoId}";
		$managerId = $this->_selectFirst('rights', 'rght_userId', $where);
		// Inscription individuelle il peut ne pas y avoir de responsable
		// pour son club on recupere son compte
		if ( empty($managerId))
		{
			$regis = $result['regi2'];
			if ( isset($regis[0]['lreg_userid']) )	$managerId = $regis[0]['lreg_userid'];
			else
			{
				$regis = $result['regi3'];
				if ( isset($regis[0]['lreg_userid']) )	$managerId = $regis[0]['lreg_userid'];
				else $managerId = -1;
			}
		}
		$user = $this->_getRow('users', '*', "user_id=$managerId");

		// Recuperer les donnees de
		$this->_prefix = 'asso_';
		$tables = array('adherents', 'u2a');
		$where = "u2a_userid=$managerId AND u2a_adherentid = adhe_id";
		$adhr = $this->_getRow($tables, '*', $where);
		$this->_prefix = utvars::getPrfx().'_';

		// Renvoyer les inscriptions
		$result['adhr']  = $adhr;
		$result['user']  = $user;
		return $result;
	}

	function getPostits($assoId)
	{
		$eventId = utvars::getEventId();
		$this->_prefix = 'asso_';
		$cols = array('post_id', 'post_type', 'post_message');
		$where = "post_assoid=$assoId".
    " AND post_eventid=$eventId";
		$postits = $this->_getRows('postits', $cols, $where);
		$this->_prefix = utvars::getPrfx().'_';
		return $postits;
	}

	function deletePostit($postitId)
	{
		$eventId = utvars::getEventId();
		$this->_prefix = 'asso_';

		// Supprimer le postit apres l'avoir recupere
		$where = "post_id=$postitId";
		$postit = $this->_getRow('postits', '*', $where);
		$this->_delete('postits', $where);

		// Renvoyer le nombre de posts du meme inscrit
		$where = "post_registrationid=".$postit['post_registrationid'];
		$nb = $this->_selectFirst('postits', 'count(*)', $where);
		$res['nbpostits'] = $nb;
		$res['lregId']    = $postit['post_registrationid'];
		$res['assoId']    = $postit['post_assoid'];
		$this->_prefix = utvars::getPrfx().'_';
		return $res;
	}

	function getLovTeams($eventId)
	{
		$this->_prefix = 'asso_';
		// Recuperer les equipes ayant des joueurs inscrits non confirmees
		$cols = array(' DISTINCT lreg_assoid');
		$where = "lreg_eventid = {$eventId}";
		$where .= " AND lreg_datesoumis != '0000-00-00 00:00:00'";
		$teams = $this->_getRows('lineregi', $cols, $where);
		$this->_prefix = utvars::getPrfx().'_';
		$columns = array('asso_id', 'asso_name', 'asso_stamp');
		$assos = array();
		foreach($teams as $team)
		{
			$where = "asso_id={$team['lreg_assoid']}";
			$assos[] = $this->_getRow('assocs', $columns, $where);
		}
		return $assos;
	}


	function getTeams($eventId)
	{
		$this->_prefix = 'asso_';
		// Recuperer les equipes ayant des joueurs inscrits non confirmees
		$cols = array('lreg_assoid',  'lreg_datesoumis','lreg_datereg',
		'count(*) as nb');
		$where = "lreg_eventid = {$eventId}".
    " AND lreg_datesoumis != '0000-00-00 00:00:00'".
    " AND lreg_datereg = '0000-00-00 00:00:00'".
    " GROUP BY lreg_assoId";
		$teamsn = $this->_getRows('lineregi', $cols, $where);

		// Recuperer les equipes ayant des joueurs inscrits acceptees
		$where = "lreg_eventid = {$eventId}".
    " AND lreg_datesoumis != '0000-00-00 00:00:00'".
    " AND lreg_datereg != '0000-00-00 00:00:00'".
    " GROUP BY lreg_assoId";
		$teamsa = $this->_getRows('lineregi', $cols, $where);

		$this->_prefix = utvars::getPrfx().'_';
		$columns = array('asso_id', 'asso_name', 'asso_stamp');
		$assos = array();
		foreach($teamsn as $team)
		{
			$where = "asso_id={$team['lreg_assoid']}";
			$asso = $this->_getRow('assocs', $columns, $where);
			$asso['nb'] = $team['nb'];
			$asso['date'] = $team['lreg_datesoumis'];
			$assos[] = $asso;
		}
		$results['submited'] = $assos;

		// Recuperer les equipes ayant des joueurs inscrits acceptees
		$assos = array();
		foreach($teamsa as $team)
		{
			$where = "asso_id={$team['lreg_assoid']}";
			$asso = $this->_getRow('assocs', $columns, $where);
			$asso['nb'] = $team['nb'];
			$asso['soumis'] = $team['lreg_datesoumis'];
			$asso['accept'] = $team['lreg_datereg'];
			$assos[] = $asso;
		}
		$results['accepted'] = $assos;

		return $results;

	}

	function setPostit($fields)
	{
		$this->_prefix = 'asso_';
		// Enregistrer le message
		$this->_insert('postits', $fields);
		$this->_prefix = utvars::getPrfx().'_';
		return true;
	}

	function deleteOnlinePostits($assoId)
	{
		$eventId = utvars::getEventId();
		$this->_prefix = 'asso_';
		// Supprimer les postits
		$where = "post_assoid=$assoId".
    " AND post_eventid=$eventId";
		$this->_delete('postits', $where);
		$this->_prefix = utvars::getPrfx().'_';
		return true;
	}

	// {{{ deletePostits()
	/**
	* Supprimer les postits du club
	*
	* @access private
	* @return void
	*/
	function deletePostits($assoId)
	{
		$where = "psit_data = $assoId".
	" AND psit_eventId =".utvars::getEventId().
	" AND psit_type=".WBS_POSTIT_LINE;
		$this->_delete('postits', $where);
		return true;
	}
	// }}}


	// {{{ getPlayers()
	/**
	* Inscription effective d'un club
	*
	* @access private
	* @return void
	*/
	function getPlayers($assoId)
	{
		$ut = new utils();
		// Recuperer les joueurs et leur inscription en simple
		$fields = array('regi_id', 'mber_sexe', 'mber_secondname',
		      'mber_firstname', 'regi_catage', 'regi_surclasse',
		);
		$tables = array('a2t', 'teams', 'registration', 'members');
		$where = "a2t_assoid = $assoId".
	" AND a2t_teamId = team_id".
	" AND team_eventId = ".utvars::getEventId().
	" AND team_id=regi_teamid".
	" AND mber_id=regi_memberid".
	" ORDER BY mber_secondname, mber_firstname";
		$res = $this->_select($tables, $fields, $where);
		$players = array();
		while ($player = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $player['mber_sexe'] = $ut->getSmaLabel($player['mber_sexe']);
	  $player['regi_catage'] = $ut->getLabel($player['regi_catage']);
	  $player['regi_surclasse'] = $ut->getLabel($player['regi_surclasse']);

	  // Traitement du simple
	  $fields = array('draw_stamp', 'rkdf_label');
	  $tables = array('pairs LEFT JOIN draws ON draw_id=pair_drawid',
			  'i2p', 'ranks', 'rankdef');
	  $where = "pair_id = i2p_pairid".
	    " AND i2p_regiid = rank_regiid".
	    " AND rank_rankdefid = rkdf_id".
	    " AND i2p_regiid ={$player['regi_id']}".
	    " AND pair_disci =".WBS_SINGLE.
	    " AND (rank_disci =".WBS_MS.
	    " OR rank_disci=".WBS_WS.")";
	  $draw = $this->_selectFirst($tables, $fields, $where);
	  $player['level']  = $draw['rkdf_label'];
	  $player['single'] = $draw['draw_stamp'];

	  // Traitement du double
	  $fields = array('draw_stamp', 'rkdf_label', 'pair_id');
	  $tables = array('pairs LEFT JOIN draws ON draw_id=pair_drawid',
			  'i2p', 'ranks', 'rankdef');
	  $where = " pair_id = i2p_pairid".
	    " AND i2p_regiid = rank_regiid".
	    " AND rank_rankdefid = rkdf_id".
	    " AND i2p_regiid ={$player['regi_id']}".
	    " AND pair_disci =".WBS_DOUBLE.
	    " AND (rank_disci =".WBS_MD.
	    " OR rank_disci=".WBS_WD.")";
	  $draw = $this->_selectFirst($tables, $fields, $where);

	  $player['level']  .= ";{$draw['rkdf_label']}";
	  $player['double'] = $draw['draw_stamp'];

	  $tables = array('registration', 'i2p');
	  $where = " i2p_pairid = '{$draw['pair_id']}'".
	    " AND i2p_regiid = regi_id".
	    " AND regi_id != {$player['regi_id']}";
	  $player['pdouble'] = $this->_selectFirst($tables, 'regi_longName',
	  $where);

	  // Traitement du mixte
	  $fields = array('draw_stamp', 'rkdf_label', 'pair_id');
	  $tables = array('pairs LEFT JOIN draws ON draw_id=pair_drawid',
			  'i2p', 'ranks', 'rankdef');
	  $where = " pair_id = i2p_pairid".
	    " AND i2p_regiid = rank_regiid".
	    " AND rank_rankdefid = rkdf_id".
	    " AND i2p_regiid ={$player['regi_id']}".
	    " AND pair_disci =".WBS_MIXED.
	    " AND rank_disci =".WBS_MX;
	  $draw = $this->_selectFirst($tables, $fields, $where);
	  $player['level']  .= ";{$draw['rkdf_label']}";
	  $player['mixte'] = $draw['draw_stamp'];
	  $player['pmixte'] = "";

	  $tables = array('registration', 'i2p');
	  $where = " i2p_pairid = '{$draw['pair_id']}'".
	    " AND i2p_regiid = regi_id".
	    " AND regi_id != {$player['regi_id']}";
	  $player['pmixte'] = $this->_selectFirst($tables, 'regi_longName',
	  $where);
	  $players[]=$player;
		}
		return $players;
	}
	//}}}

	// {{{ setRegiMixte()
	/**
	* Inscription d'un joueur dans les tableaux de mixte
	*
	* @access private
	* @return void
	*/
	function setRegiMixte($player, $partner=null)
	{
		$ret = array();
		$regiId = $player['regiId'];
		$nom = $player['lreg_familyname'];
		$nom .= " {$player['lreg_firstname']}";
		$msgDraw    = "{$nom} inscrit en mixte dans un autre tableau";
		$msgPartner = "{$nom} inscrit en mixte avec un autre partenaire";
		$msgPartnerNoFound = "{$nom} partenaire de mixte non trouve";
		if (!is_null($partner))
		{
			$nomp = $partner['lreg_familyname'];
			$nomp .= " {$partner['lreg_firstname']}";
		}

		// Verifier la coherence de la paire
		// -- des genres
		if (!is_null($partner) &&
		$player['lreg_gender'] == $partner['lreg_gender'])
		{
			return PEAR::raiseError("{$nom} et $nomp de m�me genre.", WBS_MSG_ERR);
		}

		// -- des tableaux
		if (!is_null($partner) && $player['lreg_mixeddrawId'] != $partner['lreg_mixeddrawId'])
		{
			return PEAR::raiseError("{$nom} et $nomp dans tableaux de mixte differents.", WBS_MSG_ERR);
		}
		// -- des niveaux (classemment)
		// Rechercher la paire de mixte
		$where = "i2p_regiid = $regiId".
	             " AND i2p_pairid = pair_id".
	             " AND pair_disci =".WBS_MIXED;
		$fields = array('pair_id', 'pair_drawid');
		$tables = array('pairs', 'i2p');
		$pair = $this->_selectFirst($tables, $fields, $where);

		// Creation de la paire
		if(is_null($pair))
		{
			$cols = array('pair_drawid'  => $player['lreg_mixeddrawId'],
			'pair_disci'   => WBS_MIXED,
			'pair_natRank' => $player['lreg_mixedrank'],
			'pair_average' => $player['lreg_mixedpoints'],
			'pair_status'  => WBS_PAIR_NONE,
			'pair_state'   => WBS_PAIRSTATE_NOK
			);
			$pair['pair_id'] = $this->_insert('pairs', $cols);

			$cols = array('i2p_pairid' => $pair['pair_id'],
                          'i2p_regiid' => $regiId,
	  	  				'i2p_rankdefid' => $player['lreg_mixedlevelId'],
	  					'i2p_cppp'      => $player['lreg_mixedpoints'],
	  					'i2p_classe'    => $player['lreg_mixedrank']
			);
			$this->_insert('i2p', $cols);
			$pair['pair_drawid'] = $player['lreg_mixeddrawId'];
		}
		$pairId = $pair['pair_id'];

		// Verifier le tableau
		if ($pair['pair_drawid'] != $player['lreg_mixeddrawId'])
		{
			return PEAR::raiseError($msgDraw, WBS_MSG_ERR);
		}

		// Traitemement du partenaire
		// Chercher celui enregistre
		$fields = array('regi_id', 'regi_longName');
		$tables = array('registration', 'i2p');
		$where = "i2p_pairid ={$pair['pair_id']}".
                 " AND i2p_regiid = regi_id".
                 " AND regi_id != $regiId";
		$regiPartner = $this->_selectFirst($tables, $fields, $where);

		// Partenaire demande
		if (!is_null($partner))
		{
			// Verifier si le partenaire demande n'est pas deja inscrit
			// dans une autre paire  ou dans un autre tableau
			$fields = array('pair_id', 'pair_drawid', 'i2p_id', 'pair_state');
			$tables = array('pairs', 'i2p');
			$where = "i2p_pairid = pair_id".
                     " AND i2p_regiid = {$partner['regiId']}".
					 " AND pair_disci =".WBS_MIXED;
			$pairPartner = $this->_selectFirst($tables, $fields, $where);
			// Partenaire deja enregistre
			if (!is_null($pairPartner))
			{
				if (is_null($regiPartner))
				{
					// Dans aucun tableau
					if ($pairPartner['pair_drawid'] == -1)
					{
						// Supprimer l'ancienne paire
						$this->_delete('pairs', "pair_id={$pairPartner['pair_id']}");
						// Mettre  jour la relation i2p
						$this->_update('i2p', array('i2p_pairId' => $pairId), "i2p_id={$pairPartner['i2p_id']}");
						// Mettre a jour l'etat de la paire		}
						$this->_update('pairs', array('pair_state' => WBS_PAIRSTATE_REG), "pair_id=$pairId");
					}
					// Dans un tableau
					else
					{
						// Verifier que c'est le meme tableau
						if ($pairPartner['pair_drawid'] != $player['lreg_mixeddrawId'])
						return PEAR::raiseError($msgDraw, WBS_MSG_ERR);

						// Verifier la paire
						if ($pairPartner['pair_state'] == WBS_PAIRSTATE_REG &&
						$pairPartner['pair_state'] != $pairId)
						return PEAR::raiseError("$msgPartner", WBS_MSG_ERR);
						// Verifier le partenaire
						if ($pairPartner['pair_state'] != WBS_PAIRSTATE_REG)
						{
							// Mettre  jour la relation i2p
							$this->_update('i2p', array('i2p_pairId' => $pairId), "i2p_id={$pairPartner['i2p_id']}");
							// Mettre a jour l'etat de la paire		}
							$this->_update('pairs', array('pair_state' => WBS_PAIRSTATE_REG), "pair_id=$pairId");
						}
					}
				}
				else
				{
					if($regiPartner['regi_id'] != $partner['regiId'])
					return PEAR::raiseError("$msgPartner", WBS_MSG_ERR);
				}
			}
			// Partenaire demande pas encore inscrit
			else
			{
				// Pas de partenaire deja enregistre :
				// Enregistrement du partenaire demande
				if (is_null($regiPartner))
				{
					$cols = array('i2p_pairid'  => $pairId, 
					'i2p_regiid'  => $partner['regiId'],
	  	  			'i2p_rankdefid' => $partner['lreg_mixedlevelId'],
	  				'i2p_cppp'      => $partner['lreg_mixedpoints'],
	  				'i2p_classe'    => $partner['lreg_mixedrank']
					);
					$this->_insert('i2p', $cols);
					// Etat de la paire a complete
					$this->_update('pairs', array('pair_state' => WBS_PAIRSTATE_REG), "pair_id=$pairId");
				}
				// Partenaire deja enregistre:
				// Verification que c'est le meme
				else
				{
					if($regiPartner['regi_id'] != $partner['regiId'])
					return PEAR::raiseError("$msgPartner", WBS_MSG_ERR);
				}
			}
		}

		// Pas de partenaire demande
		else
		{
			// Partenaire deja enregistre :
			if (!is_null($regiPartner))
			{
				if (empty($player['lreg_mixedpartner']))
				return PEAR::raiseError("$msgPartner", WBS_MSG_ERR);
				else
				// Verification que c'est le meme
				if($regiPartner['regi_longName'] !=
				$player['lreg_mixedpartnername'] .' ' .$player['lreg_mixedpartner'])
				return PEAR::raiseError("$msgPartner {$regiPartner['regi_longName']} ", WBS_MSG_ERR);
			}
			else
			if(!empty($player['lreg_mixedpartner']))
			return PEAR::raiseError("$msgPartnerNoFound", WBS_MSG_ERR);
		}
		return $ret;
	}
	// }}}

	// {{{ setRegiDouble()
	/**
	* Inscription d'un joueur dans les tableaux de double
	*
	* @access private
	* @return void
	*/
	function setRegiDouble($player, $partner=null)
	{
		$ret = array();
		$regiId = $player['regiId'];
		$nom = $player['lreg_familyname'];
		$nom .= " {$player['lreg_firstname']}";
		$msgDraw    = "{$nom} inscrit en double dans un autre tableau";
		$msgPartner = "{$nom} inscrit en double avec un autre partenaire";
		$msgPartnerNoFound = "{$nom} partenaire de double non trouve";
		if (!is_null($partner))
		{
			$nomp = $partner['lreg_familyname'];
			$nomp .= " {$partner['lreg_firstname']}";
		}

		// Verifier la coherence de la paire
		// -- des genres
		if (!is_null($partner) && $player['lreg_gender'] != $partner['lreg_gender'])
		{
			return PEAR::raiseError("{$nom} et $nomp de genre diff�rent.", WBS_MSG_ERR);
		}

		// -- des tableaux
		if (!is_null($partner) && $player['lreg_doubledrawId'] != $partner['lreg_doubledrawId'])
		{
			return PEAR::raiseError("{$nom} et $nomp dans tableaux de double differents.", WBS_MSG_ERR);
		}
		// -- des niveaux (classemment)
		// Rechercher la paire de double
		$where = "i2p_regiid = $regiId".
	" AND i2p_pairid = pair_id".
	" AND pair_disci =".WBS_DOUBLE;
		$fields = array('pair_id', 'pair_drawid');
		$tables = array('pairs', 'i2p');
		$pair = $this->_selectFirst($tables, $fields, $where);

		// Creation de la paire
		if(is_null($pair))
		{
			$cols = array('pair_drawid'  => $player['lreg_doubledrawId'],
			'pair_disci'   => WBS_DOUBLE,
			'pair_natRank' => $player['lreg_doublerank'],
			'pair_average' => $player['lreg_doublepoints'],
			'pair_status'  => WBS_PAIR_NONE,
			'pair_state'   => WBS_PAIRSTATE_NOK
			);
			$pair['pair_id'] = $this->_insert('pairs', $cols);

			$cols = array('i2p_pairid' => $pair['pair_id'],
			'i2p_regiid' => $regiId,
	  	  	'i2p_rankdefid' => $player['lreg_doublelevelId'],
	  		'i2p_cppp'      => $player['lreg_doublepoints'],
	  		'i2p_classe'    => $player['lreg_doublerank']
			);
			$this->_insert('i2p', $cols);
			$pair['pair_drawid'] = $player['lreg_doubledrawId'];
		}
		$pairId = $pair['pair_id'];

		// Verification du tableau
		if ($pair['pair_drawid'] != $player['lreg_doubledrawId']) return PEAR::raiseError($msgDraw, WBS_MSG_ERR);

		// Traitemement du partenaire
		// Chercher celui enregistre
		$fields = array('regi_id', 'regi_longName');
		$tables = array('registration', 'i2p');
		$where = "i2p_pairid ={$pair['pair_id']}".
	" AND i2p_regiid = regi_id".
	" AND regi_id != $regiId";
		$regiPartner = $this->_selectFirst($tables, $fields, $where);
		// Partenaire demande
		if (!is_null($partner))
		{
			// Verifier si le partenaire demande n'est pas deja inscrit
			// dans une autre paire  ou dans un autre tableau
			$fields = array('pair_id', 'pair_drawid', 'i2p_id', 'pair_state');
			$tables = array('pairs', 'i2p');
			$where = "i2p_pairid = pair_id".
   			" AND i2p_regiid = {$partner['regiId']}".
		    " AND pair_disci =".WBS_DOUBLE;
			$pairPartner = $this->_selectFirst($tables, $fields, $where);

			// Partenaire deja enregistre
			if (!is_null($pairPartner))
			{
				if (is_null($regiPartner))
				{
					// Dans aucun tableau
					if ($pairPartner['pair_drawid'] == -1)
					{
						// Supprimer l'ancienne paire
						$this->_delete('pairs', "pair_id={$pairPartner['pair_id']}");
						// Mettre  jour la relation i2p
						$this->_update('i2p', array('i2p_pairId' => $pairId),
			     		"i2p_id={$pairPartner['i2p_id']}");
						// Mettre a jour l'etat de la paire		}
						$this->_update('pairs', array('pair_state' => WBS_PAIRSTATE_REG),
			     		"pair_id=$pairId");
					}
					// Dans un tableau
					else
					{
						// Verifier que c'est le meme tableau
						if ($pairPartner['pair_drawid'] != $player['lreg_doubledrawId'])
						return PEAR::raiseError($msgDraw, WBS_MSG_ERR);

						// Verifier la paire
						if ($pairPartner['pair_state'] == WBS_PAIRSTATE_REG &&
						$pairPartner['pair_state'] != $pairId)
						return PEAR::raiseError($msgPartner . '1', WBS_MSG_ERR);
						// Verifier le partenaire
						if ($pairPartner['pair_state'] != WBS_PAIRSTATE_REG)
						{
							// Mettre  jour la relation i2p
							$this->_update('i2p', array('i2p_pairId' => $pairId),
							 "i2p_id={$pairPartner['i2p_id']}");
							// Mettre a jour l'etat de la paire		}
							$this->_update('pairs', array('pair_state' => WBS_PAIRSTATE_REG),
				 			"pair_id=$pairId");
						}
					}
				}
				else
				{
					if($regiPartner['regi_id'] != $partner['regiId'])
					return PEAR::raiseError($msgPartner, WBS_MSG_ERR);
				}
			}
			// Partenaire demande pas encore inscrit
			else
			{
				// Pas de partenaire deja enregistre :
				// Enregistrement du partenaire demande
				if (is_null($regiPartner))
				{
					$cols = array('i2p_pairid'  => $pairId,
					'i2p_regiid'  => $partner['regiId'],
	  	  			'i2p_rankdefid' => $partner['lreg_doublelevelId'],
	  				'i2p_cppp'      => $partner['lreg_doublepoints'],
	  				'i2p_classe'    => $partner['lreg_doublerank']
					);
					$this->_insert('i2p', $cols);
					// Etat de la paire a complete
					$this->_update('pairs', array('pair_state' => WBS_PAIRSTATE_REG),
					 "pair_id=$pairId");

				}
				// Partenaire deja enregistre:
				// Verification que c'est le meme
				else
				{
					if($regiPartner['regi_id'] != $partner['regiId'])
					return PEAR::raiseError($msgPartner, WBS_MSG_ERR);
				}
			}
		}
		// Pas de partenaire demande
		else
		{
			// Partenaire deja enregistre :
			if (!is_null($regiPartner))
			{
				if (empty($player['lreg_doublepartner'])) return PEAR::raiseError($msgPartner, WBS_MSG_ERR);
				else
				// Verification que c'est le meme
				if($regiPartner['regi_longName'] !=
				$player['lreg_doublepartnername'] . ' '. $player['lreg_doublepartner'])
				return PEAR::raiseError("$msgPartner {$regiPartner['regi_longName']}", WBS_MSG_ERR);
			}
			else
			if(!empty($player['lreg_doublepartner']))
			{
				return PEAR::raiseError($msgPartnerNoFound, WBS_MSG_ERR);
			}
		}
		return $ret;
	}
	// }}}


	// {{{ setRegiSingle()
	/**
	* Inscription d'un joueur dans les tableaux
	*
	* @access private
	* @return void
	*/
	function setRegiSingle($player)
	{
		$ret = array();
		$nom = $player['lreg_familyname'];
		$nom .= " {$player['lreg_firstname']}";
		$regiId = $player['regiId'];

		// Rechercher la paire de simple
		$where = "i2p_regiid = $regiId".
	" AND i2p_pairid = pair_id".
	" AND pair_disci =".WBS_SINGLE;
		$fields = array('pair_id', 'pair_drawid');
		$tables = array('pairs', 'i2p');
		$pair = $this->_selectFirst($tables, $fields, $where);

		// Creation de la paire
		if(is_null($pair))
		{
	  $cols = array('pair_drawid'  => $player['lreg_singledrawId'],
			'pair_disci'   => WBS_SINGLE,
			'pair_natRank' => $player['lreg_singlerank'],
			'pair_average' => $player['lreg_singlepoints'],
			'pair_status'  => WBS_PAIR_NONE,
			'pair_state'   => WBS_PAIRSTATE_REG
			);
	  $pairId = $this->_insert('pairs', $cols);

	  $cols = array('i2p_pairid'  => $pairId,
			'i2p_regiid'   => $regiId,
	  	  	'i2p_rankdefid' => $player['lreg_singlelevelId'],
	  		'i2p_cppp'      => $player['lreg_singlepoints'],
	  		'i2p_classe'    => $player['lreg_singlerank']
	  );
	  $this->_insert('i2p', $cols);
		}
		// La paire existe, verifier sa conformite
		else
		{
	  if ($pair['pair_drawid'] != $player['lreg_singledrawId'])
	  $ret = PEAR::raiseError( "{$nom} inscrit dans un autre tableau en simple",
	  WBS_MSG_INFO);
		}
		return $ret;
	}
	// }}}

	// {{{ getRegiId()
	/**
	* Reherche l'inscription d'un joueur saisie manuellement
	*
	* @access private
	* @return void
	*/
	function getRegiId($teamId, $player)
	{
		$prenom = ucwords(strtolower($player['lreg_firstname']));
		$nom = strtoupper($player['lreg_familyname']);

		// Le joueur existe dans badnet : recuperer sont Id
		if (empty($player['lreg_licence']))
		$where = "mber_firstname = '$prenom'".
	  " AND mber_secondname = '$nom'".
	  " AND mber_sexe = {$player['lreg_gender']}";
		else
		$where = "mber_licence={$player['lreg_licence']}";
		$memberId = $this->_selectFirst('members', 'mber_id', $where);
		$date = new utdate();
		// Le joueur n'existe pas le creer
		if (is_null($memberId))
		{
	  $cols['mber_fedeid']      = -1;
	  $cols['mber_sexe']        = $player['lreg_gender'];
	  $cols['mber_secondname']  = $nom;
	  $cols['mber_firstname']   = $prenom;
	  $cols['mber_licence']     = $player['lreg_licence'];
	  $cols['mber_born']        = '';
	  $cols['mber_cmt']         = 'Origine inscription en ligne '.date('Y-m-d H:i:s');
	  $memberId = $this->_insert('members', $cols);
		}

		// Recuperer son inscription
		$where = "regi_memberid = $memberId".
	" AND regi_eventid =".utvars::getEventId();
		$regiId = $this->_selectFirst('registration', 'regi_id', $where);

		// Le joueur n'est pas inscrit, creation de l'inscription
		if (is_null($regiId))
		{
	  // Creation de l'inscription
	  $regi['regi_eventid'] = utvars::getEventId();
	  $regi['regi_date'] = date('Y-m-d H:i:s');
	  $regi['regi_type'] = WBS_PLAYER;
	  $regi['regi_memberId']  = $memberId;
	  $regi['regi_teamId']    = $teamId;
	  $regi['regi_longName']  = "$nom $prenom";
	  $regi['regi_shortName'] = "$nom ".substr($prenom, 0, 1).".";
	  $regi['regi_cmt']       = 'Inscription en ligne '.date('Y-m-d H:i:s');
	  $regi['regi_catage']    = $player['lreg_catage'];
	  $regi['regi_dateauto']      = '';
	  $regi['regi_surclasse']     = $player['lreg_surclasse'];
	  $regi['regi_datesurclasse'] = '';
	  $where  = "team_id = $teamId";
	  $regi['regi_accountId'] = $this->_selectFirst('teams', 'team_accountId', $where);
	  $regiId = $this->_insert('registration', $regi);

	  // Creation du classement de simple
	  $rank['rank_regiId']   = $regiId;
	  $rank['rank_isFede']   = 0;
	  $rank['rank_dateFede'] = '';
	  $rank['rank_rankdefid'] = $player['lreg_singlelevelId'];
	  $rank['rank_average']   = $player['lreg_singlepoints'];
	  $rank['rank_disci']     = $player['lreg_gender'] == WBS_MALE ? WBS_MS:WBS_LS;
	  $rank['rank_discipline']= WBS_SINGLE;
	  $res = $this->_insert('ranks', $rank);

	  // Creation du classement de double
	  $rank['rank_rankdefid'] = $player['lreg_doublelevelId'];
	  $rank['rank_average']   = $player['lreg_doublepoints'];
	  $rank['rank_disci']     = $player['lreg_gender'] == WBS_MALE ? WBS_MD:WBS_LD;
	  $rank['rank_discipline']= WBS_DOUBLE;
	  $res = $this->_insert('ranks', $rank);

	  // Creation du classement de mixte
	  $rank['rank_rankdefid'] = $player['lreg_mixedlevelId'];
	  $rank['rank_average']   = $player['lreg_mixedpoints'];
	  $rank['rank_disci'] = WBS_MX;
	  $rank['rank_discipline']= WBS_MIXED;
	  $res = $this->_insert('ranks', $rank);
		}
		return $regiId;
	}
	// }}}

	// {{{ getPoonaRegiId()
	/**
	* Reherche l'inscription du joueur d'origine poona
	*
	* @access private
	* @return void
	*/
	function getPoonaRegiId($teamId, $regi)
	{
		// Le joueur vient de poona : recuperer ses donnees sur poona
		require_once dirname(__FILE__)."/../import/imppoona.php";
		$poona = new ImpPoona();
		$player = $poona->getPlayer($regi['lreg_poonaId']);
		if (isset($player['errMsg'])) return PEAR::raiseError($player['errMsg'], WBS_MSG_ERR);

		// Le joueur existe dans badnet : recuperer sont Id
		$where = "mber_fedeid = {$regi['lreg_poonaId']}";
		$memberId = $this->_selectFirst('members', 'mber_id', $where);
		$date = new utdate();
		// Le joueur n'existe pas le creer
		if (is_null($memberId))
		{
	  $date->setFrDate($player['mber_born']);
	  $cols['mber_fedeid']      = $player['mber_fedeid'];
	  $cols['mber_sexe']        = $player['mber_sexe'];
	  $cols['mber_secondname']  = $player['mber_secondname'];
	  $cols['mber_firstname']   = $player['mber_firstname'];
	  $cols['mber_licence']     = $player['mber_licence'];
	  $cols['mber_born']        = $date->getIsoDateTime();
	  $cols['mber_cmt']         = 'Origine poona '.date('Y-m-d H:i:s');
	  $memberId = $this->_insert('members', $cols);
		}

		// Recuperer son inscription
		$where = "regi_memberid = $memberId".
	" AND regi_eventid =".utvars::getEventId();
		$regiId = $this->_selectFirst('registration', 'regi_id', $where);

		// Le joueur n'est pas inscrit, creation de l'inscription
		if (is_null($regiId))
		{
	  // Creation de l'inscription
	  $prenom = ucwords(strtolower($player['mber_firstname']));
	  $nom = strtoupper($player['mber_secondname']);
	  $cols = array();
	  $cols['regi_eventid'] = utvars::getEventId();
	  $cols['regi_date'] = date('Y-m-d H:i:s');;
	  $cols['regi_type'] = WBS_PLAYER;
	  $cols['regi_memberId']  = $memberId;
	  $cols['regi_teamId']    = $teamId;
	  $cols['regi_longName']  = "$nom $prenom";
	  $cols['regi_shortName'] = "$nom ".substr($prenom, 0, 1).".";
	  $cols['regi_cmt']       = 'Inscription en ligne '.date('Y-m-d H:i:s');
	  $cols['regi_catage']    = $player['regi_catage'];
	  $date->setFrDate($player['regi_dateauto']);
	  $cols['regi_dateauto']      = $date->getIsoDateTime();
	  $cols['regi_surclasse']     = $player['regi_surclasse'];
	  $date->setFrDate($player['regi_datesurclasse']);
	  $cols['regi_datesurclasse'] = $date->getIsoDateTime();
	  $where  = "team_id = $teamId";
	  $cols['regi_accountId'] = $this->_selectFirst('teams',
							'team_accountId', 
	  $where);
	  $regiId = $this->_insert('registration', $cols);

	  // Creation du classement de simple
	  $rank['rank_regiId']   = $regiId;
	  $rank['rank_isFede']   = 1;
	  $rank['rank_dateFede'] = date('Y-m-d H:i:s');
	  $rank['rank_rankdefid'] = $player['rank_simple'];
	  $rank['rank_average']  = $player['rank_cpppsimple'];
	  $rank['rank_disci']    = $player['mber_sexe'] == WBS_MALE ?
	  WBS_MS:WBS_LS;
	  $rank['rank_discipline']= WBS_SINGLE;
	  $res = $this->_insert('ranks', $rank);

	  // Creation du classement de double
	  $rank['rank_rankdefid'] = $player['rank_double'];
	  $rank['rank_average']   = $player['rank_cpppdouble'];
	  $rank['rank_disci']     = $player['mber_sexe'] == WBS_MALE ?
	  WBS_MD:WBS_LD;
	  $rank['rank_discipline']= WBS_DOUBLE;
	  $res = $this->_insert('ranks', $rank);

	  // Creation du classement de mixte
	  $rank['rank_rankdefid']    = $player['rank_mixte'];
	  $rank['rank_average'] = $player['rank_cpppmixte'];
	  $rank['rank_disci'] = WBS_MX;
	  $rank['rank_discipline']= WBS_MIXED;
	  $res = $this->_insert('ranks', $rank);
		}
		return $regiId;
	}
	// }}}

	// {{{ getTeamId()
	/**
	* reherche l'equipe de l'association. La cree au besoi
	*
	* @access private
	* @return void
	*/
	function getTeamId($assoId)
	{

		$fields = array('team_id');
		$tables = array('a2t', 'teams');
		$where = "a2t_assoid = $assoId".
	" AND a2t_teamId = team_id".
	" AND team_eventId = ".utvars::getEventId();
		$teamId = $this->_selectFirst($tables, $fields, $where);
		if (!is_null($teamId))
		return $teamId;

		// Creation de l'inscription de l'equipe
		$where = "asso_id=$assoId";
		$asso = $this->_selectFirst('assocs', '*', $where);

		$team = array('team_name'   =>$asso['asso_name'],
		    'team_stamp'  =>$asso['asso_stamp'],
		    'team_noc'    =>$asso['asso_noc'],
		    'team_url'    =>$asso['asso_url'],
		    'team_cmt'    =>'Inscription en ligne',
		    'team_date'   =>date('Y-m-d H:i:s'),
		    'team_id'     =>-1,
		    'team_assoId' =>$assoId,
		    'team_captain'=>''
		    );
		    require_once dirname(__FILE__)."/../utils/utteam.php";
		    $utt = new utTeam();
		    $teamId = $utt->updateTeam($team);

		    return $teamId;
	}
	//}}}


	// {{{ getEmails()
	/**
	* renvoi des emails des gestionnaoires du tournois
	*
	* @access private
	* @return void
	*/
	function getEmails()
	{
		$eventId = utvars::getEventId();
		// Recuperer les email des administrateurs du tournoi
		$where = "rght_theme=".WBS_THEME_EVENT.
	" AND rght_themeId={$eventId}".
	" AND rght_status ='". WBS_AUTH_MANAGER."'". 
	" AND rght_userId = user_id".
	" AND user_email != ''";
		$tables = array('rights', 'users');
		$res = $this->_select($tables, 'user_email', $where);
		$emails = array();
		while ($email = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$emails[] = $email['user_email'];
		return $emails;
	}
	//}}}
}
?>
