<?php
/*****************************************************************************
!   Module     : utils
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/objmatch.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.5 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/05/06 07:59:33 $
******************************************************************************/
require_once "utbase.php"; 

/**
* Acces to the dababase for matches
*/

class objMatch
{

  // {{{ properties
  var $_pairs = array();// Pairs of the match
  var $_type = '';      // Type
  var $_score = '';     // Score
  var $_begin = '';     // Begin time
  var $_end = '';       // End time
  var $_schedule = '';  // Previsionnal begin time
  var $_venue = '';     // Where is the match
  var $_serial = '';    // Ex: Serie Elite
  var $_drawName = '';  // Ex: Simple Homme Elite
  var $_drawStamp = ''; // Ex: SH El
  var $_stageName = ''; // Ex: Tableau final | Poule A
  var $_stageStamp = '';// Ex: TF | A
  var $_stepName = '';  // Ex : Demi-finale
  var $_stepStamp = ''; // Ex : 1/2
  var $_posStep = '';   // Ex : 2 | 2/3
  var $_court = '';     // Terrain
  var $_status = '';    // Etat du match
  var $_umpire = '';    // Arbitre
  var $_service = '';   // Juge de service
  var $_num = '';       // Numero
  var $_date = '';      // Date prevue
  var $_time = '';      // Heure prevue
  var $_uniId = '';      // Heure prevue
  var $_isDouble = true;      // Match de double
  // }}}

  // {{{ objMatch()
  /**
   * Constructor
   */
  function objMatch($matchId)
    {
      $utbase = new utbase();
      $fields = array('mtch_score', 'mtch_begin', 'mtch_end',
		      'mtch_num', 'mtch_id', 'mtch_discipline',
		      'mtch_court', 'mtch_status',
		      'tie_schedule', 'tie_place', 'tie_posRound',
		      'p2m_pairId', 'p2m_posmatch',
		      'draw_serial', 'draw_name', 'draw_stamp', 
		      'rund_stamp', 'rund_name', 'rund_type', 'rund_size',
		      'rund_id', 'mtch_uniId', 'mtch_umpireId', 'mtch_serviceId', 'tie_isbye');
      $tables = array('matchs LEFT JOIN p2m ON p2m_matchId = mtch_id', 'ties', 'draws', 'rounds');
      $where = 'mtch_tieId = tie_id'.
	' AND tie_roundId = rund_id'.
	' AND rund_drawId = draw_id'.
	" AND mtch_id = $matchId";
      $res = $utbase->_select($tables, $fields, $where);
      if ($res->numRows())
	while ( $match = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  {
	    $this->_schedule = $match['tie_schedule'];
	    $this->_time = substr($match['tie_schedule'], 11, 5);
	    $this->_date = substr($match['tie_schedule'], 0, 10);
	    $this->_court = $match['mtch_court'];
	    $this->_venue = $match['tie_place'];
	    $this->_isbye = $match['tie_isbye'];
	    $this->_begin = $match['mtch_begin'];
	    $this->_end   = $match['mtch_end'];
	    $this->_num   = $match['mtch_num'];
	    $this->_score = $match['mtch_score'];
	    $this->_status= $match['mtch_status'];
	    $this->_id    = $match['mtch_id'];
	    $this->_serial   = $match['draw_serial'];
	    $this->_drawName = $match['draw_name'];
	    $this->_drawStamp  = $match['draw_stamp'];
	    $this->_stageName  = $match['rund_name'];  // Ex: Tableau final | Poule A
	    $this->_stageStamp = $match['rund_stamp']; // Ex: TF | A
	    $this->_type       = $match['rund_type']; // Poule, KO, consolante, 
	    $this->_umpireId  = $match['mtch_umpireId']; 
	    $this->_serviceId  = $match['mtch_serviceId']; 
	    $this->_isDouble   = ($match['mtch_discipline'] > WBS_WS) &&
	      ($match['mtch_discipline'] != WBS_AS); 
	    $this->_disci = $match['mtch_discipline']; 
	    $this->_uniId = $match['mtch_uniId']; 
	    
	    $label = $this->getTieStep($match['tie_posRound'], $match['rund_type']);
	    if ($match['rund_type'] == WBS_ROUND_GROUP )
	    {
		   $this->_stepName = "{$this->_stageStamp} ({$label})";
		   $this->_stepStamp = "{$this->_stageStamp} ({$label})";
	    }
	    else if ($match['rund_type'] == WBS_TEAM_GROUP ||
	    		$match['rund_type'] == WBS_TEAM_BACK ||
	    		$match['rund_type'] == WBS_TEAM_KO)
	    {
		   $ut = new utils();
	       $this->_stepName = $ut->getLabel($this->_disci);
		   $this->_stepStamp = $ut->getSmaLabel($this->_disci);
	    }
	    else
	    {
		  $ut = new utils();
		  $this->_stepName   = $ut->getLabel($label);
		  $this->_stepStamp  = $ut->getSmaLabel($label);
	    }
	    $this->_posStep = $label;    // Ex : 2 | 2/3

	    // Initialisation des paires
	    if (is_numeric($match['p2m_pairId']))
	      {
		$specialName = null;
		// Match de poule de 3, deuxieme ou troisieme tour
		if ($match['rund_type'] == WBS_ROUND_GROUP &&
		    $match['rund_size'] == 3 &&
		    $match['p2m_posmatch'] == WBS_PAIR_BOTTOM &&
		    $match['tie_posRound'] < 2)
		  {
		    // Resultats du premier match
		    $fields = array('mtch_num', 'mtch_status');
		    $tables = array('matchs', 'ties', 'rounds');
		    $where = "rund_id=".$match['rund_id'].
		      " AND rund_id=tie_roundId".
		      " AND tie_id=mtch_tieId".
		      " AND tie_posRound=2";
		    $result = $utbase->_selectFirst($tables, $fields, $where);
		    // match joue
		    if ($result['mtch_status'] <= WBS_MATCH_LIVE)
		      {	      
			$tmp = $match['tie_posRound'] ? "Perdant":"Vainqueur";
			$specialName = "{$tmp} match {$result['mtch_num']}";
		      }
		  }
		// Match de poule de 4, a traiter
		//print_r($match);
		if ($match['rund_type'] == WBS_ROUND_GROUP &&
		    $match['rund_size'] == 4 && 
		    $match['tie_posRound'] != 1 &&
		    $match['tie_posRound'] != 4)		   
		  {
		    // Resultats des deux premiers matchs
		    $fields = array('mtch_num', 'mtch_status');
		    $tables = array('matchs', 'ties', 'rounds');
		    $where = "rund_id=".$match['rund_id'].
		      " AND rund_id=tie_roundId".
		      " AND tie_id=mtch_tieId".
		      " AND tie_posRound IN (1,4)";
		    $order = 'tie_posRound';
		    $result = $utbase->_select($tables, $fields, $where, $order);
		    $res1 = $result->fetchRow(DB_FETCHMODE_ASSOC);
		    $res4 = $result->fetchRow(DB_FETCHMODE_ASSOC);

		    // match joue
		    if (($match['tie_posRound']==3 || $match['tie_posRound']==0) && 
			$match['p2m_posmatch'] == WBS_PAIR_TOP &&
			$res1['mtch_status'] <= WBS_MATCH_LIVE)
		      $specialName = "Vainqueur match {$res1['mtch_num']}";
		    else if (($match['tie_posRound']==3 || $match['tie_posRound']==5) && 
			     $match['p2m_posmatch'] == WBS_PAIR_BOTTOM &&
			     $res4['mtch_status'] <= WBS_MATCH_LIVE)
		      $specialName = "Perdant match {$res4['mtch_num']}";
		    else if ($match['tie_posRound']==2 && 
			     $match['p2m_posmatch'] == WBS_PAIR_TOP &&
			     $res4['mtch_status'] <= WBS_MATCH_LIVE)
		      $specialName = "Vainqueur match {$res4['mtch_num']}";
		    else if ($match['tie_posRound']==2 && 
			     $match['p2m_posmatch'] == WBS_PAIR_BOTTOM &&
			     $res1['mtch_status'] <= WBS_MATCH_LIVE)
		      $specialName = "Perdant match {$res1['mtch_num']}";
		    else if ($match['tie_posRound']==0 && 
			     $match['p2m_posmatch'] == WBS_PAIR_BOTTOM &&
			     $res4['mtch_status'] <= WBS_MATCH_LIVE)
		      $specialName = "Vainqueur match {$res4['mtch_num']}";
		    else if ($match['tie_posRound']==5 && 
			     $match['p2m_posmatch'] == WBS_PAIR_TOP &&
			     $res1['mtch_status'] <= WBS_MATCH_LIVE)
		      $specialName = "Perdant match {$res1['mtch_num']}";
		  }
		$this->_pairs[$match['p2m_posmatch']]  = 
		  new _pair($match['p2m_pairId'], $matchId, $specialName);
	      }
	    unset($match);	    
	  }
      unset($res);
      unset($utbase);
    }
  // }}}

  // {{{ Accesseurs
  function getId() { return $this->_id;}
  function getUniId() { return $this->_uniId;}
  function getNum() { return $this->_num;}
  function getDiscipline() { return $this->_disci;}
  function getCourt() { return $this->_court;}
  function getScore() { return $this->_score;}
  function getSerial() { return $this->_serial;}
  function getDrawName() { return $this->_drawName;}
  function getDrawStamp() { return $this->_drawStamp;}
  function getStageName() { return $this->_stageName;}
  function getStageStamp() { return $this->_stageStamp;}
  function getEnd() { return $this->_end;}
  function getStart() { return $this->_begin;}
  function getSchedule() { return $this->_schedule;}
  function getVenue() { return $this->_venue;}
  function getTime() { return $this->_time;}
  function getBeginTime($aLength=5) {return substr($this->_begin, 11, $aLength);}
  function getDate() { return $this->_date;}
  function getDay() {     
    return strftime('%a', mktime(substr($this->_schedule,11,2),
				 substr($this->_schedule,14,2),
				 substr($this->_schedule,17,4),
				 substr($this->_schedule,5,2),
				 substr($this->_schedule,8,2),
				 substr($this->_schedule,0,4)
				 ));
}
  function getStepName() { return $this->_stepName;}
  function getStepStamp() { return $this->_stepStamp;}
  function getPosition() { return $this->_posStep;}
  function isDouble() { return $this->_isDouble;}
  function isEnded() { return $this->_status > WBS_MATCH_LIVE;}
  function isBye() { return $this->_isbye == WBS_YES;}
  function getLength() {
    $utd = new utDate();
    $utd->setIsoDateTime($this->_begin);
    $duree  = $utd->getDiff($this->_end);
    if ($duree < 5 && $duree > 120)
      $duree = '--';
    else
      $duree .= ' mn';
    return $duree;
  }

function getUmpire()
{
      $utbase = new utbase();
		$fields = array('regi_longName');
      $tables = array('registration');
      $where = 'regi_id = '. $this->_umpireId;
      $name = $utbase->_selectFirst($tables, $fields, $where);
      unset($utbase);
      return $name;
}
function getService()
{
      $utbase = new utbase();
		$fields = array('regi_longName');
      $tables = array('registration');
      $where = 'regi_id = '. $this->_serviceId;
      $name = $utbase->_selectFirst($tables, $fields, $where);
      unset($utbase);
      return $name;
}

  
  // {{{ getFirstWinName()
  function getFirstWinName($long=true, $specialName=false)
    {
      if ($this->isEnded())
	foreach($this->_pairs as $pair)
	{
	  if($pair->isWinner())
	    return $pair->getFirstName($long);
	}
      else
	return $this->getFirstTopName($long, $specialName);
      return '';
    }
  // }}}

  // {{{ getFirstLosName()
  function getFirstLosName($long=true, $specialName=false)
    {
      if ($this->isEnded())
	foreach($this->_pairs as $pair)
	{
	  if(is_object($pair) && 
	     $pair->isLooser())
	    return $pair->getFirstName($long);
	}
      else
	return $this->getFirstBottomName($long, $specialName);
      return '';
    }
  // }}}

  // {{{ getSecondWinName()
  function getSecondWinName($long=true)
    {
      if ($this->isEnded())
	foreach($this->_pairs as $pair)
	{
	  if($pair->isWinner())
	    return $pair->getSecondName($long);
	}
      else
	return $this->getSecondTopName($long);
      return '';
    }
  // }}}

  // {{{ getSecondLosName()
  function getSecondLosName($long=true)
    {
      if ($this->isEnded())
	foreach($this->_pairs as $pair)
	{
	  if($pair->isLooser())
	    return $pair->getSecondName($long);
	}
      else
	return $this->getSecondBottomName($long);
      return '';
    }
  // }}}

  // {{{ getWinName()
  function getWinName($long=true)
    {
      foreach($this->_pairs as $pair)
	{
	  if($pair->isWinner())
	    return $pair->getName($long);
	}
      return '';
    }
  // }}}

  // {{{ getLosName()
  function getLosName($long=true)
    {
      foreach($this->_pairs as $pair)
	{
	  if($pair->isLooser())
	    return $pair->getName($long);
	}
      return '';
    }
  // }}}

  // {{{ getTieStep
  /**
   * Return the label of the tie (final, 1/2 final....)
   */
  function getTieStep($posTie, $roundType)
    {
      if($roundType != WBS_ROUND_GROUP)
	{
	  switch ($posTie)
	    {
	    case 0:
	      return WBS_FINAL;
	    case 1:
	    case 2:
	      return WBS_SEMI;
	    case 3:
	    case 4:
	    case 5:
	    case 6:
	      return WBS_QUATER;
	    }
	  if (($posTie > 6) &&
	      ($posTie < 15))
	    return WBS_HEIGHT;
	  if (($posTie >= 15) &&
	      ($posTie < 31))
	    return WBS_16;
	  if (($posTie >= 31) &&
	      ($posTie < 63))
	    return WBS_32;
	  return WBS_64;
	}
      else
	{
	  switch ($posTie)
	    {
	    case 0:
	      return "1/2";
	    case 1:
	      return "1/3";
	    case 2:
	      return "2/3";
	    case 3:
	      return "1/4";
	    case 4:
	      return "2/4";
	    case 5:
	      return "3/4";
	    case 6:
	      return "1/5";
	    case 7:
	      return "2/5";
	    case 8:
	      return "3/5";
	    case 9:
	      return "4/5";
	    default :
	      return "--";
	    }
	}
    }
  // }}}

  // {{{ getFirstTopName()
  function getFirstTopName($long=true, $specialName=false)
    {
      if (isset($this->_pairs[WBS_PAIR_TOP]))
	return $this->_pairs[WBS_PAIR_TOP]->getFirstName($long, $specialName);
      else
	return '';
    }
  // }}}

  // {{{ getFirstBottomName()
  function getFirstBottomName($long=true, $specialName=false)
    {
      if (isset($this->_pairs[WBS_PAIR_BOTTOM]))
	return $this->_pairs[WBS_PAIR_BOTTOM]->getFirstName($long, $specialName);
      else
	return '';
    }
  // }}}

  // {{{ getSecondTopName()
  function getSecondTopName($long=true, $specialName=false)
    {
      if (isset($this->_pairs[WBS_PAIR_TOP]))
	return $this->_pairs[WBS_PAIR_TOP]->getSecondName($long, $specialName);
      else
	return '';
    }
  // }}}

  // {{{ getSecondBottomName()
  function getSecondBottomName($long=true, $specialName=false)
    {
      if (isset($this->_pairs[WBS_PAIR_BOTTOM]))
	return $this->_pairs[WBS_PAIR_BOTTOM]->getSecondName($long, $specialName);
      else
	return '';
      return '';
    }
  // }}}

  function isTopWin()
    {
      if (isset($this->_pairs[WBS_PAIR_TOP]))
      {
        if($this->_pairs[WBS_PAIR_TOP]->isWinner()) return true;
        else return false;
      }
      return false;
    }
    
  function isBottomWin()
    {
      if (isset($this->_pairs[WBS_PAIR_BOTTOM]))
      {
        if($this->_pairs[WBS_PAIR_BOTTOM]->isWinner()) return true;
        else return false;
      }
      return false;
    }
    
  function getFirstTopId()
    {
      if (isset($this->_pairs[WBS_PAIR_TOP]))
	return $this->_pairs[WBS_PAIR_TOP]->getFirstId();
      else
	return -1;
    }

  function getFirstBottomId()
    {
      if (isset($this->_pairs[WBS_PAIR_BOTTOM]))
	return $this->_pairs[WBS_PAIR_BOTTOM]->getFirstId();
      else
	return -1;
    }

  function getSecondTopId()
    {
      if (isset($this->_pairs[WBS_PAIR_TOP]))
	return $this->_pairs[WBS_PAIR_TOP]->getSecondId();
      else
	return -1;
    }

  function getSecondBottomId()
    {
      if (isset($this->_pairs[WBS_PAIR_BOTTOM]))
	return $this->_pairs[WBS_PAIR_BOTTOM]->getSecondId();
      else
	return -1;
      return '';
    }
  // }}}
    
}

class _pair
{
  var $_specialName = null;      //Name of players
  var $_players = array();       //Players of the pair
  var $_result = WBS_RES_NOPLAY; //Result of the pair
  var $_pos = WBS_PAIR_BOTTOM;   //Position of the pair (top, bottom)

  // {{{ _pair()
  /**
   * Constructor
   */
  function _pair($pairId, $matchId, $specialName)
    {
      $utbase = new utbase();
      $fields = array('p2m_result', 'p2m_posmatch', 'regi_id');
      $tables = array('pairs', 'registration', 'i2p', 'p2m', 'members');
      $where = 'pair_id = i2p_pairId'.
	' AND regi_memberId = mber_id'.
	' AND i2p_regiId = regi_id'.
	" AND pair_id = $pairId".
	" AND p2m_pairId = pair_id".
	" AND p2m_matchId = $matchId";
      $order = "mber_sexe, mber_secondname";
      $res = $utbase->_select($tables, $fields, $where, $order);
      if ($res->numRows())
	while ( $pair = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  {
	    $this->_result = $pair['p2m_result'];
	    $this->_pos    = $pair['p2m_posmatch'];
	    $this->_players[]= new _player($pair['regi_id']);
	    unset($pair);	    
	  }
      else
	echo "paire introuvable $pairId";
      $this->_specialName = $specialName;
      unset($res);
      unset($utbase);
    }
  // }}}

  // {{{ isWinner()
  /**
   * 
   */
  function isWinner()
    {
      return $this->_result == WBS_RES_WIN ||
	$this->_result == WBS_RES_WINAB ||
	$this->_result == WBS_RES_WINWO;
    }
  // }}}

  // {{{ isLooser()
  /**
   * 
   */
  function isLooser()
    {
      return $this->_result == WBS_RES_LOOSE ||
	$this->_result == WBS_RES_LOOSEAB ||
	$this->_result == WBS_RES_LOOSEWO;
    }
  // }}}

  // {{{ getName()
  /**
   * 
   */
  function getName($long = true, $special=false)
    {
      $glue = '';
      $name = '';
      if (!is_null($this->_specialName))
	$name = $special ? $this->_specialName : '';
      else
	foreach($this->_players as $player)
	{
	  $name .= $glue.$player->_name;
	  if ($long)
	    $name .= " (".$player->_stamp.")";
	  $glue = '-';
	}
      return $name;
    }
  // }}}

  // {{{ getFisrtName()
  function getFirstName($long=true, $special=false)
    {
      $glue = '';
      $name = '';
      if (!is_null($this->_specialName))
	$name = $special ? $this->_specialName : '';
      else
	{
	  $player = reset($this->_players);
	  $name = $player->_name;
	  if ($long) $name .= " (" . $player->_stamp . '-' . $player->_catage . ")";
	}
      return $name;
    }
  // }}}
  
  function getFirstId()
    {
	  $player = reset($this->_players);
	  return $player->_regiId;
    }
    
    
  // {{{ getSecondName()
  function getSecondName($long=true, $special = false)
    {
      $glue = '';
      $name = '';
      if (!is_null($this->_specialName))
	$name = $special ? $this->_specialName : '';
      else
	{
	  if (count($this->_players) == 2)
	    {
	      $player = end($this->_players);
	      $name = $player->_name;
	      if ($long) $name .= " (" . $player->_stamp . '-' . $player->_catage . ")";
	    }
	}
      return $name;
    }

  function getSecondId()
    {
	  if (count($this->_players) == 2)
	  {
	      $player = end($this->_players);
	      return $player->_regiId;
	  }
      else return -1;
    }
  // }}}
    
}

class _player
{
  var $_name = ''; //Name of the player
  var $_team = ''; //Name of the team
  var $_stamp = ''; //Stamp of the team

  // {{{ _player()
  /**
   * Constructor
   */
  function _player($regiId)
    {
      $utbase = new utbase();
      $fields = array('regi_id, regi_longName', 'team_stamp', 'team_name', 'regi_catage',
      					'regi_numcatage');
      $tables = array('registration', 'teams');
      $where = 'team_id = regi_teamId'.
	" AND regi_id = $regiId";
      $res = $utbase->_select($tables, $fields, $where);
      if ($res->numRows())
      {
      	$ut = new utils();
		while ( $regi = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  	{
	    	$this->_regiId  = $regi['regi_id'];
	  		$this->_name  = $regi['regi_longName'];
	    	$this->_team  = $regi['team_name'];
	    	$this->_stamp = $regi['team_stamp'];
	    	$this->_catage = $ut->getSmaLabel($regi['regi_catage']);
	    	if ($regi['regi_numcatage']) $this->_catage .= $regi['regi_numcatage'];
	    	unset($regi);	    
	  	}
      }
      else	echo "joueuer introuvable $pairId";
      unset($res);
      unset($utbase);
    }
  // }}}
}

?>
