<?php
/*****************************************************************************
!   Module     : Schedu
!   File       : $Source: /cvsroot/aotb/badnet/src/schedu/base_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.13 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2006/12/08 17:40:08 $
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
require_once "utils/utdate.php";
require_once "utils/utround.php";
require_once "draws/draws.inc";


/**
* Acces to the dababase for ties
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class scheduBase_V extends utbase
{

  // {{{ properties
  // }}}

  // {{{ getTiesIndiv
  /**
   * Return the table with tie results
   *
   * @access public
   * @param  integer  $divId  Id of the division
   * @param  integer  $date   date of the ties
   * @param  integer  $sort     Sorting column
   * @return array   array of users
   */
  function getTiesIndiv($date, $place)
    {
    $utd = new utDate();
    $utd->setFrDate($date);
    
      // Firt select the ties of the group
      $eventId = utvars::getEventId();
      $fields = array('tie_id', 'rund_name', 'tie_step', 'tie_place', 
		      "TIME(tie_schedule) as time",
		      'rund_id', 'rund_stamp', 'draw_id', 'mtch_num',
      		      'tie_posRound', 'draw_name', 'rund_type', 
		      'tie_court', 'draw_stamp');
      $tables = array('rounds', 'draws', 'ties', 'matchs');
      $where = "draw_eventId = $eventId".
	" AND mtch_tieId = tie_id".
	" AND rund_drawId = draw_id".
	" AND tie_roundId = rund_id".
	" AND tie_isBye = 0".
	" AND tie_place = '$place'".
	" AND DATE(tie_schedule) ='". $utd->getIsoDate() . "'";
      $order = "time, tie_court, mtch_num";

      $res = $this->_select($tables, $fields, $where, $order, 'tie');
      if (!$res->numRows())
	{
	  $infos['errMsg'] = "msgNoTies";
	  return $infos;
        } 

      $time = "00:00:00";
      $utd = new utdate();
      $utr = new utround();
      $ut = new utils();
      $fields = array('rund_name');
      $fields2 = array('tie_place','tie_step');
      while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $tie = $this->_getTranslate('rounds', $fields, $tie['rund_id'], $tie);
	  $tie = $this->_getTranslate('ties', $fields2, $tie['tie_id'], $tie);
	  if ($time != $tie['time'])
	    {
	      if ($time!="00:00:00")
		{
		  $cells[] = $lines;
		  $rows[] = $cells;
		}
	      $cells = array();
	      $cells[] = $tie['tie_id'];
	      $lines = array();
	      $lines[0] = array('value' => substr($tie['time'],0,5),
				'class' => 'classScheduTime');
	      $lines['class'] = 'classScheduTime';
	      $cells[] = $lines;
	      $lines = array();
	      $tieId=-1;
	      $time = $tie['time'];
	    }
	  if ($tieId != $tie['tie_id'])
	    {
	      if ($tieId>=0) $cells[] = $lines;
	      $lines = array();
	      $lines['class'] = 'classScheduData';
	      $lines[0] = array('value' => "N° ".$tie['mtch_num'],
				'class' => 'classScheduNum');
	      $step = $utr->getTieStep($tie['tie_posRound'], $tie['rund_type']);
	      if ($tie['rund_type'] ==  WBS_ROUND_GROUP)
		{
		  $action = DRAW_GROUPS_DISPLAY;
		  $step = $tie['rund_name']; //. " $step";
		  $id = $tie['draw_id'];
		}
	      else
		{
		  $pos  = $utr->getTiePos($tie['tie_posRound']);
		  $step = $ut->getLabel($step)." $pos";
		  $action = DRAW_KO_DISPLAY;
		  $id = $tie['rund_id'];
		  $lines[3] = array('value' => $tie['rund_name'],
				    'class' => 'classScheduStep');
		}
	      $lines[1] = array('value' => $tie['draw_stamp'],
				'class' => 'classScheduDraw',
				'action' => array(KAF_UPLOAD, 'draws', 
						  $action, $id));
	      $lines[2] = array('value' => $step,
				'class' => 'classScheduStep');
	      $tieId = $tie['tie_id'];
	    }
	  else
	    {
	      $line = $lines[1];
	      $line['value'] .= " v ".$tie['tie_stamp'];
	      $lines[1] = $line;
	    }
	}
      $cells[] = $lines;
      $rows[] = $cells;
      //print_r($rows);
      return $rows;
    }
  // }}}
  

  // {{{ getTiesSchedu2
  /**
   * Return the table with tie results
   *
   * @access public
   * @param  integer  $divId  Id of the division
   * @param  integer  $date   date of the ties
   * @param  integer  $sort     Sorting column
   * @return array   array of users
   */
  function getTiesSchedu2($divId, $date)
    {
    $utd = new utDate();
    $utd->setFrDate($date);
      // Firt select the ties of the group
      $eventId = utvars::getEventId();
      $fields = array('tie_id', 'rund_name', 'tie_step', 'tie_place', 
		      "TIME(tie_schedule) as time",
		      'team_name', 'team_id', 't2t_matchW', 'team_id', 
		      'team_stamp', 'asso_logo', 'team_logo', 'rund_id',
		      'draw_id');
      $tables = array('rounds', 'draws',
		      'ties LEFT JOIN t2t ON t2t_tieId = tie_id
		      LEFT JOIN teams ON t2t_teamId = team_id
		      LEFT JOIN a2t ON a2t_teamId = team_id
		      LEFT JOIN assocs ON a2t_assoId = asso_id', 
		      //'a2t', 'assocs', 
		      );
      $where = //"t2t_teamId = team_Id".
	//" AND t2t_tieId = tie_id".
	" DATE(tie_schedule) ='". $utd->getIsoDate() . "'".
	" AND draw_id = '$divId'".
	//" AND a2t_assoId = asso_id ".
	//" AND a2t_teamId = team_id ".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId";
      $order = "time, tie_id, t2t_posTie";

      $res = $this->_select($tables, $fields, $where, $order, 'tie');
      if (!$res->numRows())
	{
	  $infos['errMsg'] = "msgNoTies";
	  return $infos;
        } 

      $time = "00:00";
      $time = "00:00:00";
      $utd = new utdate();
      $fields = array('rund_name');
      $fields2 = array('tie_place','tie_step');
      //$tieId=-1;
      while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $tie = $this->_getTranslate('rounds', $fields, $tie['rund_id'], $tie);
	  $tie = $this->_getTranslate('ties', $fields2, $tie['tie_id'], $tie);
	  if ($time != $tie['time'])
	    {
	      if ($time!="00:00:00")
		{
		  $cells[] = $lines;
		  $rows[] = $cells;
		}
	      $cells = array();
	      $cells[] = $tie['tie_id'];
	      $lines = array();
	      if ($tie['time'] =="00:00:00")
		$lines[0] = array('value' => "--:--",
				  'class' => 'classTime');
	      else
		$lines[0] = array('value' => substr($tie['time'],0,5),
				  'class' => 'classTime');
	      $cells[] = $lines;
	      $lines = array();
	      $tieId=-1;
	      $time = $tie['time'];
	    }
	  if ($tieId != $tie['tie_id'])
	    {
	      if ($tieId>=0) $cells[] = $lines;
	      $lines = array();
	      $lines[0] = array('value' => $tie['rund_name'],
				'class' => 'classGroup');
	      $lines[1] = array('value' => $tie['team_stamp'],
				'class' => 'classTie',
				'action' => array(KAF_UPLOAD, 'ties', 
						  TIES_SELECT_TIE,
						  $tie['tie_id']));
	      $lines[3] = array('value' => $tie['tie_step'],
				'class' => 'classStep');
	      $tieId = $tie['tie_id'];
	    }
	  else
	    {
	      $line = $lines[1];
	      $line['value'] .= " v ".$tie['team_stamp'];
	      $lines[1] = $line;
	    }
	}
      $cells[] = $lines;
      $rows[] = $cells;
      return $rows;
    }
  // }}}


  // {{{ getTiesSchedu
  /**
   * Return the table with tie results
   *
   * @access public
   * @param  integer  $divId  Id of the division
   * @param  integer  $date   date of the ties
   * @param  integer  $sort     Sorting column
   * @return array   array of users
   */
  function getTiesSchedu($divId, $date,  $sort)
    {
    $utd = new utDate();
    $utd->setFrDate($date);
    
      // Firt select the ties of the group
      $eventId = utvars::getEventId();
      $fields = array('tie_id', 'rund_name', 'tie_step', 'tie_place',
		      'team_name', 'team_id', 't2t_matchW', 'team_id', 
		      'asso_logo', 'team_logo', 'TIME(tie_schedule)');
      $tables = array('teams', 'ties', 't2t', 'a2t', 'assocs', 'rounds', 'draws');
      $where = "t2t_teamId = team_Id".
	" AND t2t_tieId = tie_id".
	" AND DATE(tie_schedule) ='". $utd->getIsoDate() ."'".
	" AND draw_id = '$divId'".
	" AND a2t_assoId = asso_id ".
	" AND a2t_teamId = team_id ".
	" AND tie_roundId = rund_id".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId";
      if ($sort > 0)
	$order = "$sort, tie_id, t2t_posTie";
      else
	$order = abs($sort) . " desc, tie_id, t2t_posTie";

      $res = $this->_select($tables, $fields, $where, $order, 'tie');
      if (!$res->numRows())
	{
	  $infos['errMsg'] = "msgNoTies";
	  return $infos;
        } 

      $ties = array();
      $tieId=-1;
      $utd = new utdate();
      while ($tie = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  if ($tie[9] == '')
	    $tie[8] = utimg::getPathFlag($tie[8]);
	  else
	    $tie[8] = utimg::getPathFlag($tie[9]);
	  unset($tie[9]);
	  if ($tieId != $tie[0])
	    {
	      if ($tieId>=0) $ties[] = $tmp;
	      $tmp = $tie;
	      $tieId = $tie[0];
	    }
	  else
	    {
	      $tmp[3] = substr($tie[3],0,5) . ' ' . $tie[10];
	      $tmp[9] = $tie[5];
	      $tmp[5] = $tie[4];
	      $tmp[6] .= "/{$tie[6]}";
	      $tmp[] = $tie[8];
	    }
	}
      $ties[] = $tmp;
      return $ties;
    }
  // }}}

  // {{{ getPlaces
  /**
   * Return the table with place of the ties
   *
   * @access public
   * @return array   array of users
   */
  function getPlaces()
    {
      // Firt select the ties of the day
      $eventId = utvars::getEventId();
 
      $fields = array('tie_place');
      $tables = array('ties', 'draws', 'rounds');   
      $where = "tie_roundId = rund_id". 
	" AND tie_place != ''".
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId";
      $order = "1";
      $res = $this->_select($tables, $fields, $where, $order, 'tie');
      if (!$res->numRows())
	{
	  $infos['errMsg'] = "msgNoPlaces";
	  return $infos;
        } 

      while ($tmp = $res->fetchRow(DB_FETCHMODE_ORDERED))
        {
	  $places[$tmp[0]] = $tmp[0];
	}
      return $places;
    }
  // }}}

  // {{{ getDateTies
  /**
   * Return the table with date of the ties of a division
   *
   * @access public
   * @param integer divId Id of the division
   * @return array   array of users
   */
  function getDateTies($divId='', $place='')
    {
     // Firt select the ties of the day
      $eventId = utvars::getEventId();
 
      $fields = array('tie_schedule');
      $tables = array('ties', 'draws', 'rounds');
      $where = "tie_roundId = rund_id". 
	" AND rund_drawId = draw_id".
	" AND draw_eventId = $eventId". 
      " AND tie_schedule != ''" .
      " AND tie_schedule != '0000-00-00 00:00:00'";
      if ($divId != '')	$where .= " AND draw_id = $divId";
      if ($place != '')	$where .= " AND tie_place = '$place'";
      $order = "1";
      
      $res = $this->_select($tables, $fields, $where, $order, 'tie');
      if (!$res->numRows())
	{
	  $infos['errMsg'] = "msgNoCalendar";
	  return $infos;
        } 
      $dates = array();
      $utd = new utDate();
      while ($tmp = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $utd->setIsoDate($tmp['tie_schedule']);
	  $fullDate = $utd->getDateTimeWithDay();
	    $dates[$utd->getDate()] = $fullDate;
	}
      return $dates;
    }
  // }}}


  // {{{ getDateTies
  /**
   * Return the table with date of the ties of a division
   *
   * @access public
   * @param integer divId Id of the division
   * @return array   array of users
   */
  function getDateTiesIndiv($tieId)
    {
     // Firt select the ties of the day
      $eventId = utvars::getEventId();
 
      $fields = array('tie_schedule');
      $tables = array('ties', 'draws', 'rounds');
      $where = "tie_roundId = rund_id". 
	" AND rund_drawId = draw_id".
	" AND tie_Id = $tieId".
	" AND draw_eventId = $eventId GROUP BY date ";
      $order = "1";
      $res = $this->_select($tables, $fields, $where, $order, 'tie');
      if (!$res->numRows())
	{
	  $infos['errMsg'] = "msgNoCalendar";
	  return $infos;
        } 
      $dates = array();
      $utd = new utDate();
      while ($tmp = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
	  $utd->setIsoDate($tmp['tie_schedule']);
	  $fullDate = $utd->getDateTimeWithDay();
	    $dates[$utd->getDate()] = $fullDate;
	}
      return $dates;
    }
  // }}}


}
?>
