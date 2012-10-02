<?php
/*****************************************************************************
!   Module     : subscript
!   File       : $Source: /cvsroot/aotb/badnet/src/subscript/subscript_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.8 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/05/06 07:59:33 $
!   Mailto     : cage@free.fr
******************************************************************************/

require_once "utils/utscore.php";
require_once "utils/utevent.php";
require_once "base_V.php";
require_once "subscript.inc";


/**
* Module de gestion du carnet d'adresse : classe visiteurs
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class subscript_V
{

  // {{{ properties
  
  /**
   * Utils objet
   *
   * @private
   */
  var $_ut;
  
  /**
   * Database access object
   *
   * @private
   */
  var $_dt;
  
  // }}}
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function subscript_V()
    {
      $this->_ut = new utils();
      $this->_dt = new subsBase_V();
    }
  // }}}


  // {{{ start()
  /**
   * Start the subscription
   *
   * @access public
   * @param  integer $action  what to do
   * @return void
   */
  function start($page)
    {
      switch ($page)
        {
	  // Display details of the team
	case WBS_ACT_SUBS :
	  $this->_displaySubscriptTeam();
	  break;

	  // Subscription to the result of the selected team
	case SUBS_TEAM_REG:
	  $this->_subscriptTeam();
	  break;

	  // Subscription to the result of the selected team
	case SUBS_PLAYER_REG:
	  $this->_subscriptPlayer();
	  break;

	  // supression  of registration
	case SUBS_DEL_REG:
	  $this->_delSubscript();
	  break;
	  
	default:
	  echo "page $page demand�e depuis subscript_V<br>";
	  exit();
	}
    }
  // }}}



  // {{{ _displaySubscriptTeam()
  /**
   * Display a page with the list of the teams
   *
   * @access private
   * @return void
   */
  function _displaySubscriptTeam($err='')
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      $action = kform::getData();        
      
      $utpage = new utPage_V('subscript', true, -1);
      $content =& $utpage->getContentDiv();

      $form =& $content->addForm('fsubscript', 'subscript', SUBS_TEAM_REG);

      $form->addMsg('tSubscription');
      if ($err != '')
	$form->addWng($err);

      // Get user's data
      $userId = utvars::getUserId();
      $user = $dt->getUser($userId);
      $email = kform::getInput('email');
      if ($email == '')
	$email = $user['user_email'];
      $kedit =& $form->addEdit('email', $email);
      $kedit->setMaxLength(50);
      $form->addBlock('blkEmail', 'email');

      // Add the list of the teams
      $sort = $form->getSort('rowsTeams', 2);
      $rows = $dt->getTeams($sort);
      $subs = $dt->getSubscripts($email, $userId);
      if (isset($rows['errMsg']))
	$form->addWng($rows['errMsg']);
      else
	{
	  $krow =& $form->addRows('rowsTeams', $rows);
	  $krow->setSelect($subs);
	}
      
      $form->addCheck('news', $dt->isNewsSubs($email, $userId));
      $form->addBlock('blkNews', 'news');

      // Add command bouton
      $form->addBtn('btnRegister', KAF_SUBMIT);
      $form->addBtn('btnDeregister', KAF_UPLOAD, 'subscript', 
		    SUBS_DEL_REG);      
      if ($user['user_login']==='demo')
	$form->addBtn('btnInfo', KAF_UPLOAD, 'subscript', 
		      WBS_ACT_SUBS);      
      $elts = array('btnRegister', 'btnDeregister', 'btnInfo');
      $form->addBlock("blkBtn", $elts);

      $utpage->display();
      exit; 
    }
  // }}}

  // {{{ _subscriptTeam()
  /**
   * Subsciption to result if selected team for the current user
   *
   * @access private
   * @return void
   */
  function _subscriptTeam()
    {
      $dt = $this->_dt;
      
      $eventId = utvars::getEventId();
      $teamsId = kform::getInput('rowsTeams');
      $email = kform::getInput('email');

      $msg = 'msgSubscriptDone';
      if (is_array($teamsId))
	{
	  foreach($teamsId as $teamId)
	    {
	      $res = $dt->addSubsTeam($eventId, $teamId, $email);
	      if (is_array($res))
		$msg = $res['errMsg'];
	    }
	}
      $news = kform::getInput('news');
      if ($news != '')
	{
	  $res = $dt->addSubsNews($eventId, $email);
	  if (is_array($res))
	    $msg = $res['errMsg'];
	}



      $utpage = new utPage('subscript');
      $content =& $utpage->getPage();
      $form =& $content->addForm('fPairs', 'pairs', KID_PUBLISHED);
      $form->setTitle('tSubscription');
      $form->addWng($msg);
      $form->addBtn('btnClose');
      $elts = array('btnClose');
      $form->addBlock('blkBtn', $elts);
      $utpage->display();
      exit; 
    }
  // }}}

  // {{{ _delSubscript()
  /**
   * Suppression of select subsciption 
   *
   * @access private
   * @return void
   */
  function _delSubscript()
    {
      $dt = $this->_dt;
      
      $eventId = utvars::getEventId();
      $teamsId = kform::getInput('rowsTeams');
      $email = kform::getInput('email');

      if (is_array($teamsId))
	foreach($teamsId as $teamId)
	  $dt->delSubsTeams($eventId, $teamId, $email);

      $news = kform::getInput('news');
      if ($news != '')
	$dt->delSubsNews($eventId, $email);

      $msg = 'msgUnsubscriptDone';
      $utpage = new utPage('subscript');
      $content =& $utpage->getPage();
      $form =& $content->addForm('fPairs', 'pairs', KID_PUBLISHED);
      $form->setTitle('tSubscription');
      $form->addWng($msg);
      $form->addBtn('btnClose');
      $elts = array('btnClose');
      $form->addBlock('blkBtn', $elts);
      $utpage->display();
      exit; 
    }
  // }}}

  // {{{ addSubsTie
  /**
   * Add subscription on tie for the current user
   *
   * @access public
   * @param  integer $eventId  Event concerned
   * @param  integer $tieId    Tie concerned
   * @return void
   */
  function addSubsTie($eventId, $tieId)
    {

    }
  // }}}

  // {{{ addSubsTeam
  /**
   * Add subscription on team result for the current user
   *
   * @access public
   * @param  integer $eventId  Event concerned
   * @param  integer $teamId   Team concerned
   * @return void
   */
  function oldaddSubsTeam($eventId, $teamId)
    {
      $dt = $this->_dt;
      $ut = $this->_ut;

      // Send the results to subscribers
      $userId = utvars::getUserId();
      $dt->addSubsTeam($eventId, $userId, $teamId);
    }
  // }}}

  // {{{ addSubsPlayer
  /**
   * Add subscription on player result team for the current user
   *
   * @access public
   * @param  integer $eventId  Event concerned
   * @param  integer $regiId   Registered player
   * @return void
   */
  function addSubsPlayer($eventId, $regiId)
    {

    }
  // }}}


  // {{{ sendMatchResult
  /**
   * Send the result of the match to all subscibers. 
   *
   * @access public
   * @param integer $matchId  Id of the match
   * @return void
   */
  function sendMatchResult($matchId)
    {
      $dt = $this->_dt;
      $ut = $this->_ut;
      $ute = new utevent();

      // Send the results to subscribers
      $event = $dt->getEventInfo();
      $subscribers = $dt->getMatchSubscribers($matchId);
      $eventId = utvars::getEventId();
      // Si la saisie est effectuees par un assistant
      // on envoie un mail aux gestionnaires du tournoi
      if (utvars::getAuthLevel() == WBS_AUTH_ASSISTANT)
	{
	  $managers = $ute->getManagers();
	  foreach($managers as $manager)
	    if ($manager['user_email'] != '')
	      $subscribers[] =$manager['user_email'];
	}
      if ($subscribers)
	{
	  require_once "utils/utmail.php";
	  $mailer = new utmail();

	  $subject='[BadNet]'.$event['evnt_name'];
	  $mailer->subject($subject);
	  $from = "no-reply@badnet.org";
	  $mailer->from($from);
	  $mailer->bcc($subscribers);
	  $mailer->body($this->_getBodyMatch($matchId));
	  // Send the message
	  $res =  $mailer->send('no-reply@badnet.org');
	  if (PEAR::isError($res))
	    echo $res->getMessage();	  
	}
      return;
    }
  // }}}

  // {{{ sendNewUser
  /**
   * Send the last user registered
   *
   * @access public
   * @param integer $userId  Id of the new user
   * @return void
   */
  function sendNewUser($userId)
    {
      $dt = $this->_dt;
      $ut = $this->_ut;

      // Send the results to subscribers
      $admins = $dt->getAdminEmail();
      $user  = $dt->getUser($userId);
      if ($admin)
	{
	  require_once "utils/utmail.php";
	  $mailer = new utmail();

	  $subject='[BadNet]'.$this->_getLabel($newUser);
	  $mailer->subject($subject);
	  $from = "no-reply@badnet.org";
	  $mailer->from($from);
	  $mailer->bcc($admins);
	 
	  $body = $this->_getLabel($userName).$user['user_name'].'\n';
	  $body .= $this->_getLabel($userPseudo).$user['user_login'].'\n';
	  $body .= $this->_getLabel($userLogin).$user['user_pseudo'].'\n';
	  $body .= $this->_getLabel($userEmail).$user['user_email'].'\n';
	  $mailer->body($body);

	  // Send the message
	  $res =  $mailer->send('no-reply@badnet.org');
	  if (PEAR::isError($res))
	    echo $res->getMessage();
	}
      return;
    }
  // }}}

  // {{{ sendNews
  /**
   * Send the last news of the event to all subscibers. 
   *
   * @access public
   * @param integer $matchId  Id of the match
   * @return void
   */
  function sendNews()
    {
      $dt = $this->_dt;
      $ut = $this->_ut;

      // Send the results to subscribers
      $event = $dt->getEventInfo();
      $eventId = utvars::getEventId();
      $subscribers = $dt->getNewsSubscribers($eventId);
      if ($subscribers)
	{
	  require_once "utils/utmail.php";
	  $mailer = new utmail();

	  $subject='[BadNet]'.$event['evnt_name'];
	  $mailer->subject($subject);
	  $from = "no-reply@badnet.org";
	  $mailer->from($from);
	  $mailer->bcc($subscribers);
	  $body = $dt->getLastNews($eventId);
	  $mailer->body($body);

	  // Send the message
	  $res =  $mailer->send('no-reply@badnet.org');
	  if (PEAR::isError($res))
	    echo $res->getMessage();
	}
      return;
    }
  // }}}


  // {{{ sendTieResult
  /**
   * Send the result of a tie to all subscibers. 
   *
   * @access public
   * @param integer $tieId  Id of the tie
   * @return void
   */
  function sendTieResult($tieId)
    {
      $dt = $this->_dt;
      $ut = $this->_ut;
      $ute = new utevent();

      // Send the results to subscribers
      // and to manager if the logged user is and assitant
      $event = $dt->getEventInfo();
      $subscribers = $dt->getTiesSubscribers($tieId);
      $eventId = utvars::getEventId();
      if (utvars::getAuthLevel() == WBS_AUTH_ASSISTANT)
	{
	  $managers = $ute->getManagers();
	  foreach($managers as $manager)
	    if ($manager['user_email'] != '')
	      $subscribers[] =$manager['user_email'];
	}
      if ($subscribers)
	{
	  require_once "utils/utmail.php";
	  $mailer = new utmail();

	  $subject='[BadNet]'.$event['evnt_name'];
	  $mailer->subject($subject);
	  $from = "no-reply@badnet.org";
	  $mailer->from($from);
	  $mailer->bcc($subscribers);
	  // Add the score of the tie
	  $teams  = $dt->getTieResult($tieId);
	  $glue = "\n";
	  $body =  '';
	  foreach($teams as $team)
	    {
	      $body .= $team['team_name'].' : '.$team['t2t_matchW'].$glue;
	      $glue = "\n\n";
	    }
	  $mailer->body($body);
	  // Send the message
	  $res =  $mailer->send('no-reply@badnet.org');
	  if (PEAR::isError($res))
	    echo $res->getMessage();
	}
      return;
    }
  // }}}

  // {{{ sendRight
  /**
   * Send the last right to the users aabout the current event
   *
   * @access public
   * @param integer $matchId  Id of the match
   * @return void
   */
  function sendRight($user)
    {
      $dt = $this->_dt;
      $ut = $this->_ut;

      // Send the results to subscribers
      $event = $dt->getEventInfo();
      if ($user['user_email']!='')
	{
	  require_once "utils/utmail.php";
	  $mailer = new utmail();

	  $subject='[BadNet]'.$event['evnt_name'];
	  $mailer->subject($subject);
	  $mailer->from('no-reply@badnet.org');
	  $body = $this->_getLabel('rightChanged').$user['right'];
	  $body .= $this->_getLabel('rightDesc');
	  $mailer->body( $body);
	  // Send the message
	  $res =  $mailer->send($user['user_email']);
	  if (PEAR::isError($res))
	    return $res->getMessage();
	}
      return true;
    }
  // }}}

  // {{{ _getBodyMatch
  /**
   * construct the body of the message
   *
   * @access public
   * @return void
   */
  function _getBodyMatch($matchId)
    {
      $dt = $this->_dt;
      $ut = $this->_ut;

      // Add the name of the division and group
      $inf = $dt->getMatchGroup($matchId);
      $body = $inf['draw_name']."\n";
      $body .= $inf['rund_name']."\n\n";

      // Add the name of the teams
      $teams  = $dt->getMatchTeams($matchId);
      $glue = '-';
      foreach($teams as $team)
	{
	  $body .= $team['team_name'].$glue;
	  $glue = "\n\n";
	}

      // Add the result of the match
      $players = $dt->getMatchPlayers($matchId);
      $player = $players[0];
      $body.= "{$player['regi_longName']}({$player['team_stamp']})";
      $player = $players[1];
      if ($player['mtch_discipline'] > WBS_LS)
	{
	  $body.= "\n{$player['regi_longName']}({$player['team_stamp']})";
	  $player = $players[2];
	}
      $body.= $this->_getLabel('win');
      $body.= $player['regi_longName'].'('.$player['team_stamp'].')';
      if ($player['mtch_discipline'] > WBS_LS) 
	{
	  $player = $players[3];
	  $body.= "\n{$player['regi_longName']}({$player['team_stamp']})";
	}
      $body.= "\n{$player['mtch_score']}\n";
      //$body.= "{$player['mtch_begin']}\n";
      //$body.= "{$player['mtch_end']}\n";
      

      // Add the score of the tie
      $teams  = $dt->getMatchTeams($matchId);
      $glue = "\n";
      foreach($teams as $team)
	{
	  $body .= $team['team_name'].' : '.$team['t2t_matchW'].$glue;
	  $glue = "\n\n";
	}
      return $body;
    }
  // }}}

  // {{{ _getLabel
  /**
   * construct the body of the message
   *
   * @access public
   * @return void
   */
  function _getLabel($labelId)
    {
      $file = "lang/".utvars::getLanguage().
	"/subscript.inc";
      require ($file);

      if (isset(${$labelId}))
	return "${$labelId}";
      else
	  return $labelId;
    }
  // }}}


}
?>