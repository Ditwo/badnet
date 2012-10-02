<?php
/*****************************************************************************
!   Module     : utils
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/objplayer.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/03/06 18:02:11 $
******************************************************************************/
require_once "utbase.php";


/**
* Acces to the dababase for player
*/

class objPlayer
{

  // {{{ properties
  var $_member = 
    array('mber_secondname' => '', 
	  'mber_firstname'  => '',
	  'mber_sexe'       => WBS_MALE,
	  'mber_ibfnumber'  => '',
	  'mber_licence'    => '', 
	  'mber_urlphoto'   => '',
	  'mber_fedeId'     => '',
	  'regi_date'       => '',
	  'regi_catage'     => '', 
	  'regi_arrival'    => '',
	  'regi_departure'  => '', 
	  'regi_arrcmt'     => '', 
	  'regi_depcmt'     => '',
	  'regi_noc'        => '',
	  'regi_rest'       => '',
	  'regi_delay'      => '',
	  'team_name'       => '',
	  'team_stamp'      => '',
	  'team_noc'        => '',
	  'asso_noc'        => '',
	  );

  // }}}

  // {{{ objPlayer()
  /**
   * Constructor
   */
  function objPlayer($regiId)
    {
      $this->_id = $regiId;
      $utbase = new utbase();
      // Etat civil et inscription
      $fields = array('mber_secondname', 'mber_firstname', 'mber_sexe',
		      'mber_ibfnumber', 'mber_licence', 'mber_urlphoto',
		      'mber_fedeId', 'mber_born',
		      'regi_date', 'regi_catage', 'regi_dateauto',
		      'regi_surclasse', 'regi_datesurclasse',
		      'regi_arrival',
		      'regi_departure', 'regi_arrcmt', 'regi_depcmt',
		      'regi_noc', 'regi_rest', 'regi_delay',
		      'team_name', 'team_stamp', 'team_noc', 'team_id',
		      'asso_noc',
		      );
      $tables = array('members', 'registration', 'teams', 'a2t', 'assocs');
      $where = 'mber_id = regi_memberId'.
	" AND regi_id = $regiId".
	' AND regi_teamId = team_id'.
	' AND a2t_assoId = asso_id'.
	' AND a2t_teamId = team_id';
      $res = $utbase->_select($tables, $fields, $where);
      if ($res->numRows()) $this->_member = $res->fetchRow(DB_FETCHMODE_ASSOC);
      unset($res);

      for ($i = WBS_SINGLE; $i<=WBS_MIXED; $i++)
	{
	  $this->_ranks[$i] = '--';
	  $this->_points[$i] = 0;
	  $this->_drawsId[$i] = -1;
	  $this->_drawsName[$i] = '--';
	  $this->_drawsStamp[$i] = '--';
	  $this->_partnersName[$i] = '--';
	  $this->_partnersId[$i] = -1;
	}
      // Classements
      $fields = array('rank_disci', 'rank_average',
		      'rkdf_label', 'rank_rank'
		      );
      $tables = array('ranks', 'rankdef');
      $where = "rank_regiId = $regiId".
	" AND rank_rankdefid = rkdf_id";
      $res = $utbase->_select($tables, $fields, $where);
      while ($rank = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  switch($rank['rank_disci'])
	    {
	    case WBS_MS:
	    case WBS_WS:
	      $i = WBS_SINGLE;
	      break;
	    case WBS_MD:
	    case WBS_WD:
	      $i = WBS_DOUBLE;
	      break;
	    default:
	      $i = WBS_MIXED;	      
	    }
	  $this->_ranks[$i] = $rank['rkdf_label'];
	  $this->_points[$i] = sprintf("%.2f", $rank['rank_average']);
	  $this->_range[$i] = $rank['rank_rank'];
	}
      unset($res);

      // Inscriptions
      $fields = array('draw_id', 'draw_name', 'draw_stamp',
		      'pair_id', 'pair_disci'
		      );
      $tables = array('draws', 'pairs', 'i2p');
      $where = "draw_id = pair_drawId".
	" AND pair_id = i2p_pairid".
	" AND i2p_regiId = $regiId";
      $res = $utbase->_select($tables, $fields, $where);
      while ($pair = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  switch($pair['pair_disci'])
	    {
	    case WBS_MS:
	    case WBS_WS:
	    case WBS_SINGLE:
	      $i = WBS_SINGLE;
	      break;
	    case WBS_MD:
	    case WBS_WD:
	    case WBS_DOUBLE:
	      $i = WBS_DOUBLE;
	      break;
	    default:
	      $i = WBS_MIXED;	      
	    }
	  $this->_drawsId[$i] = $pair['draw_id'];
	  $this->_drawsName[$i] = $pair['draw_name'];
	  $this->_drawsStamp[$i] = $pair['draw_stamp'];
	  // Recherche du partenaire
	  $fields = array('regi_id', 'regi_longName'
			  );
	  $tables = array('registration',  'i2p');
	  $where = " i2p_pairId = {$pair['pair_id']}".
	    " AND i2p_regiId != $regiId".
	    " AND i2p_regiId = regi_id";
	  $partner = $utbase->_selectFirst($tables, $fields, $where);
	  if (!is_null($partner))
	    {
	      $this->_partnersId[$i] = $partner['regi_id'];
	      $this->_partnersName[$i] = $partner['regi_longName'];
	    }	  	  
	}
      unset($res);            
      unset($utbase);
      $utd = new utdate();
      $utd->setIsoDate($this->_member['mber_born']);
      $this->_member['mber_born'] = $utd->getDate();
      $utd->setIsoDate($this->_member['regi_dateauto']);
      $this->_member['regi_dateauto'] = $utd->getDate();
      $utd->setIsoDate($this->_member['regi_datesurclasse']);
      $this->_member['regi_datesurclasse'] = $utd->getDate();
      $utd->setIsoDate($this->_member['regi_date']);
      $this->_member['regi_date'] = $utd->getDate();
      unset($utd);
    }
  // }}}

  // {{{ Accesseurs
  function getSexe() { return $this->_member['mber_sexe'];}
  function getName() { return $this->_member['mber_firstname'] . ' ' . $this->_member['mber_secondname'];}
  function getBorn() { return $this->_member['mber_born'];}
  function getDate() { return $this->_member['regi_date'];}
  function getLicence() { return $this->_member['mber_licence'];}
  function getIbfNum() { return $this->_member['mber_ibfnumber'];}
  function getPhoto() { return $this->_member['mber_urlphoto'];}
  function getPoona() { return $this->_member['mber_fedeId'];}
  function getId() { return $this->_id;}
  function getCatage() { return $this->_member['regi_catage'];}
  function getDateCompet() { return $this->_member['regi_dateauto'];}
  function getSurclasse() { return $this->_member['regi_surclasse'];}
  function getDateSurclasse() { return $this->_member['regi_datesurclasse'];}
  function getArrival() { return $this->_member['regi_arrival'];}
  function getDeparture() { return $this->_member['regi_departure'];}
  function getNoc() 
    { 
      if ($this->_member['regi_noc'] != '')
	return $this->_member['regi_noc'];
      if ($this->_member['team_noc'] != '')
	return $this->_member['team_noc'];
      else
	return  $this->_member['asso_noc'];
    }
  
  function getTeamName() { return $this->_member['team_name'];}
  function getTeamStamp() { return $this->_member['team_stamp'];}
  function getTeamId() { return $this->_member['team_id'];}
  function getRest() { return $this->_member['regi_rest'];}
  function getDelay() { return $this->_member['regi__delay'];}
  function getPoint($disci=WBS_SINGLE) { return $this->_points[$this->_getStdDisci($disci)];}
  function getRank($disci=WBS_SINGLE) { return $this->_ranks[$this->_getStdDisci($disci)];}
  function getRange($disci=WBS_SINGLE) { return $this->_range[$this->_getStdDisci($disci)];}
  function getDrawName($disci=WBS_SINGLE) { return $this->_drawsName[$this->_getStdDisci($disci)];}
  function getDrawStamp($disci=WBS_SINGLE) { return $this->_drawsStamp[$this->_getStdDisci($disci)];}
  function getDrawId($disci=WBS_SINGLE) { return $this->_drawsId[$this->_getStdDisci($disci)];}
  function getPartnerName($disci=WBS_DOUBLE) { return $this->_partnersName[$this->_getStdDisci($disci)];}
  function getPartnerId($disci=WBS_DOUBLE) { return $this->_partnersId[$this->_getStdDisci($disci)];}

  function _getStdDisci($disci) 
    { 
      if ($disci==WBS_SINGLE ||
	  $disci==WBS_MS ||
	  $disci==WBS_WS ||
	  $disci==WBS_AS)
	return WBS_SINGLE;
      else if ($disci==WBS_DOUBLE ||
	  $disci==WBS_MD ||
	  $disci==WBS_WD ||
	  $disci==WBS_AD)
	return WBS_DOUBLE;
      else 
	return WBS_MIXED;
    }
  
}
?>
