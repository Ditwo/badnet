<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
//require_once 'Offsrank.inc';

class Offsrank extends Object
{


	/**
	 * Constructeur
	 * @param	integer	$aEventId	Identifiant du tournoi
	 * @return OEvent
	 */
	public function __construct($aEventId=-1)
	{
		if( $aEventId > 0 )
		{
			$where = 'evnt_id=' . $aEventId;
			$this->load('events', $where);
		}
	}

	/**
	 * Le tournoi est-il un tournoi adulte
	 *
	 * @return boolean
	 */
	public function isAdult()
	{
		return ($this->getVal('type') == OEVENT_TYPE_IC);
	}

	/**
	 * Le tournoi est-il un tournoi jeune
	 *
	 * @return boolean
	 */
	public function isYoung()
	{
		return ($this->getVal('type') == OEVENT_TYPE_IC);
	}
	

	/**
	 * Supprime le tournoi
	 * @return void
	 */
	public function deleteEvent()
	{
		$this->emptyEvent();
		$eventId = $this->getVal('id', -1);
		$q = new Bn_query('events');
		$q->deleteRow('evnt_id='. $eventId);
		$q->setTables('eventsextra');
		$q->deleteRow('evxt_eventid='.$eventId);
		$q->setTables('eventsmeta');
		$q->deleteRow('evmt_eventid='.$eventId);
		unset ($q);
	}

	/**
	 * Vide le tournoi
	 * @return void
	 */
	public function emptyEvent()
	{
		$eventId = $this->getVal('id', -1);
		$q = new Bn_query('postits');
		$q->deleteRow('psit_eventId='. $eventId);
		$q->setTables('news');
		$q->deleteRow('news_eventId='. $eventId);
		$q->setTables('news');
		$q->deleteRow('news_eventId='. $eventId);
		$q->setTables('database');
		$q->deleteRow('db_eventId='. $eventId);
		//$q->setTables('cnx');
		//$q->deleteRow('cnx_eventId='. $eventId);
		$q->setTables('eventsmeta');
		$q->deleteRow('evmt_eventId='. $eventId);
		$q->setTables('eventsextra');
		$q->deleteRow('evxt_eventId='. $eventId);
		$q->setTables('items');
		$q->deleteRow('item_eventId='. $eventId);
		$q->setTables('prefs');
		$q->deleteRow('pref_eventId='. $eventId);
		$q->setTables('subscriptions');
		$q->deleteRow('subs_eventId='. $eventId);
			
		// Suppression des equipes
		$q->setTables('teams');
		$q->setWhere('team_eventId=' . $eventId);
		$q->addField('team_id');
		$teamIds = $q->getRows();
		foreach($teamIds as $teamId)
		{
			$q->setTables('a2t');
			$q->deleteRow('a2t_teamId=' . $teamId);

			$q->setTables('t2t');
			$q->deleteRow('t2t_teamId=' . $teamId);

			$q->setTables('t2r');
			$q->deleteRow('t2r_teamId=' . $teamId);

		}
		$q->setTables('teams');
		$q->deleteRow('team_eventId='. $eventId);
			
		// Suppression des tableaux (series)
		$q->setTables('draws');
		$q->setWhere('draw_eventId=' . $eventId);
		$q->setFields('draw_id');
		$drawIds = $q->getCol();
		if ( !empty($drawIds) )
		{
			$q->deleteRow('draw_eventId='. $eventId);

			$q->setTables('rounds');
			$q->setFields('rund_id');
			$ids = implode(',', $drawIds);
			$q->setWhere("rund_drawId IN ($ids)");
			$roundIds = $q->getCol();
		}
			
		// Suppression des tours (poule ko)
		if ( !empty($roundIds) )
		{
			$ids = implode(',', $roundIds);

			$q->setTables('rounds');
			$q->deleteRow('rund_id IN ('. $ids . ')');

			$q->setTables('t2r');
			$q->deleteRow('t2r_roundid IN ('. $ids . ')');

			$q->setTables('ties');
			$q->setFields('tie_id');
			$q->setWhere('tie_roundid IN (' .  $ids . ')');
			$tieIds = $q->getCol();
		}
			
		// Suppression des rencontres et match
		if ( !empty($tieIds) )
		{
			$ids = implode(',', $tieIds);

			$q->setTables('ties');
			$q->deleteRow('ties_id IN ('. $ids . ')');

			$q->setTables('t2t');
			$q->deleteRow('t2t_tieid IN ('. $ids . ')');

			$q->setTables('matchs');
			$q->setFields('mtch_id');
			$q->setWhere('mtch_tieid IN (' .  $ids . ')');
			$matchIds = $q->getCol();
			foreach($matchIds as $matchId)
			{
				$q->setTables('p2m');
				$q->deleteRow('p2m_mtchid =' .$matchId);
					
				$q->setTables('matchs');
				$q->deleteRow('mtch_id ='. $matchId);
			}
		}
			
		// Suppression des comptes et commandes (achats)
		$q->setTables('accounts');
		$q->setFields('cunt_id');
		$q->setWhere('cunt_eventid='.$eventId);
		$accountIds = $q->getCol();
		$q->deleteRow();
		$q->setTables('commands');
		foreach ($accountIds as $accountId)
		{
			$q->deleteRow('cmd_accountid=' . $accountId);
		}
			
		// Suppression des inscrits
		$q->setTables('registration');
		$q->setFields('regi_id');
		$q->setWhere('regi_eventid='.$eventId);
		$regiIds = $q->getCol();
		$q->deleteRow();
		$q->setTables('commands');
			
		if ( count($regiIds) )
		{
			$ids = implode(',', $regiIds);
			$q->setTables('umpire');
			$q->deleteRow('umpi_regiId IN (' . $ids . ')');
			$q->setTables('ranks');
			$q->deleteRow('rank_regiId IN (' . $ids . ')');

			$q->setTables('i2p, pairs');
			$q->setFields('pair_id');
			$q->setWhere("i2p_regiId IN ($ids)");
			$q->addWhere('i2p_pairId = pair_id');
			$pairIds = $q->getCol();

			$q->setTables('i2p');
			$q->deleteRow("i2p_regiId IN ($ids)");
			$q->setTables('pairs');
			$ids = implode(',', $pairId);
			$q->deleteRow("pair_id IN ($ids)");
		}
		unset ($q);
		return;
	}


	/**
	 * Enregistre les donnes d'un tournoi
	 * @return int id du tournoi
	 */
	public function save()
	{
		$where = 'evnt_id=' . $this->getVal('id', -1);
		$id = $this->update('events', 'evnt_', $this->getValues(), $where);
		return $id;
	}


}
?>
