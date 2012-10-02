<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Opair.inc';
require_once 'Object.php';
require_once 'Oplayer.php';


class pair extends Object
{
	private $_players = array();         //Joueurs de la paire
	private $_pairId = -1;
	private $_pairUniId = '';
	private $_team = array('name'=>'', 'stamp'=>'', 'noc'=>'', 'points'=>0, 'id'=>-1 );
	
	// Pour le live scoring
	private $_sc = array(0,0,0,0,0);

	/**
	 * Constructor
	 */
	//function __construct($aPairId, $aTieId=null)
    function __construct($aPairId, $matchId, $aSpecialName)
	{
		require_once ('Object/Oplayer.php');
		/*
		$q = new bn_Query('pairs, i2p, registration, members, teams');
		$q->setFields(' regi_id, pair_uniid, pair_id, team_name, team_stamp, team_id, team_noc');
		$q->addWhere (' pair_id = i2p_pairId');
		$q->addWhere (' regi_memberId = mber_id');
		$q->addWhere (' i2p_regiId = regi_id');
		$q->addWhere (' regi_teamid = team_id');
		if($aTieId !=  null)
		{
			$q->addTable('t2t', 't2t_teamid=team_id');
			$q->addField ('t2t_scorew', 'points');
			$q->addWhere ('t2t_tieid='.$aTieId);
		}
		
		if (strpos($aPairId, ':') !== false)
		{
			if (strpos($aPairId, ':') !== false) $q->addWhere ("pair_uniid = '" . $aPairId . "'");
			else $q->addWhere ("pair_uniid = '" . $aPairId . ";'");
		}
		else $q->addWhere ('pair_id =' . $aPairId );
		$q->setOrder ('mber_sexe, mber_secondname');
		*/
		
		$q = new bn_Query('pairs, registration, i2p, p2m, members');
		$q->setFields('p2m_result, p2m_posmatch, regi_id, pair_uniid, pair_id');
		$q->addWhere (' pair_id = i2p_pairId');
		$q->addWhere (' regi_memberId = mber_id');
		$q->addWhere (' i2p_regiId = regi_id');
		$q->addWhere (' pair_id =' . $aPairId);
		$q->addWhere (' p2m_pairId = pair_id');
		$q->addWhere (' p2m_matchId ='.$matchId);
		$q->setOrder ('mber_sexe, mber_secondname');
		$rows = $q->getRows();
		if ( is_array($rows) )
		{
			foreach ($rows as $row)
			{
				$this->_pairUniId = $row['pair_uniid'];
				$this->_pairId    = $row['pair_id'];
				$this->_players[] = new Oplayer($row['regi_id']);
				$this->_team['name'] = ''; //$row['team_name'];
				$this->_team['noc'] = '';//$row['team_noc'];
				$this->_team['stamp'] = '';//$row['team_stamp'];
				$this->_team['id'] = '';//$row['team_id'];
				$this->_team['points'] = 0;
				//if($aTieId != null) $this->_team['points'] = $row['points'];
				
				unset($row);
			}
		}
		else echo "paire introuvable $aPairId";
		unset($q);
	}

	Public function getId() { return $this->_pairId;}
	Public function getUniId() { return $this->_pairUniId;}
	Public function getPlayers() { return $this->_players;}
	Public function getSc($aNumGame) { return $this->_sc[$aNumGame-1];}
	Public function setSc($aNumGame, $aValue) {$this->_sc[$aNumGame-1] = $aValue;}
	Public function getTeam() 
	{
		return $this->_team;
	}
	

	/**
	 * @todo : en fonction du match
	 */
	function isWinner($aMatchId)
	{
		return $this->_result == OPAIR_RES_WIN ||
		$this->_result == OPAIR_RES_WINAB ||
		$this->_result == OPAIR_RES_WINWO;
	}

	/**
	 *
	 */
	function isLooser($aMatchId)
	{
		return $this->_result == OPAIR_RES_LOOSE ||
		$this->_result == OPAIR_RES_LOOSEAB ||
		$this->_result == OPAIR_RES_LOOSEWO;
	}

	/**
	 *
	 */
	function getName($aLong = true)
	{
		$glue = '';
		$name = '';
		foreach($this->_players as $player)
		{
			$name .= $glue.$player->_name;
			if ($aLong)	$name .= " (".$player->_stamp.")";
			$glue = '-';
		}
		return $name;
	}

	function getFirstName($aLong=true)
	{
		$glue = '';
		$name = '';
		$player = reset($this->_players);
		$name = $player->_name;
		if ($aLong)	$name .= " (".$player->_stamp.")";
		return $name;
	}

	function getSecondName($aLong=true)
	{
		$glue = '';
		$name = '';
			if (count($this->_players) == 2)
			{
				$player = end($this->_players);
				$name = $player->_name;
				if ($aLong)	$name .= " (".$player->_stamp.")";
			}
		return $name;
	}

}

?>