<?php
/*****************************************************************************
 !   Module     : Registrations
 !   File       : $Source: /cvsroot/aotb/badnet/src/regi/regi_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.37 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/04/04 22:55:51 $
 ******************************************************************************/

include_once dirname(__FILE__)."/base_A.php";
include_once dirname(__FILE__)."/line.inc";

include_once dirname(__FILE__)."/../utils/utpage_A.php";

include_once dirname(__FILE__)."/../utils/utimg.php";
include_once dirname(__FILE__)."/../utils/utdraw.php";
include_once dirname(__FILE__)."/../mber/mber.inc";
include_once dirname(__FILE__)."/../ajaj/ajaj.inc";



/**
 * Module de gestion des inscription en ligne
 *
 * @author Didier BEUVELOT <didier.beuvelot@free.fr>
 * @see to follow
 *
 */

class line_A
{

	// {{{ properties
	/**
	* Database access object
	*
	* @var     object
	* @access  private
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
	function line_A()
	{
		$this->_dt = new lineBase_A();
	}
	// }}}

	// {{{ start()
	/**
	* Start the connexion processus
	*
	* @access public
	* @param  integer $action  what to do
	* @return void
	*/
	function start($action)
	{
		$id = kform::getData();
		switch ($action)
		{
	  // Displaying windows for registrations
			case WBS_ACT_LINE:
				$this->_displayTeams();
				break;

			case KID_SELECT:
			case LINE_TEAM:
				$this->_displayTeam();
				break;

			case LINE_VALID:
				$this->_validTeam();
				break;

			case LINE_DEL_MESSAGE:
				$this->_deleteMessage();
				break;

			default :
				echo "line_A->start($action) non autorise <br>";
				exit;
		}
	}
	// }}}

	// {{{ _deleteMessage()
	/**
	* Supprime un message d'avertisement
	*
	* @access private
	* @return void
	*/
	function _deleteMessage()
	{

		$postitId =  kform::getData();
		$posts = $this->_dt->deletePostit($postitId);

		// Changer le statut de l'inscription
		if ( !$posts['nbpostits'] )
		{
	  $res = $this->_dt->validRegistration($posts['lregId']);
		}

		// Avertir s'il n'y a plus rien en attente
		$assoId = $posts['assoId'];
		$regis  = $this->_dt->getFullRegistrations($assoId, true);
		if ( !count($regis) )
		{
	  $this->_dt->deletePostits($assoId);

	  require_once dirname(__FILE__)."/../utils/utmail.php";
	  $mailer = new utmail();

	  // Recuperer les donnees
	  $regis  = $this->_dt->getProposedRegistrations($assoId);
	  $to = $regis['adhr']['adhe_email'];
	  if ($regis['adhr']['adhe_email'] != $regis['user']['user_email'])
	  $to .= ",{$regis['user']['user_email']}";

	  $subject='[BadNet]'.$regis['event']['evnt_name'];
	  $mailer->subject($subject);
	  $from = "no-reply@badnet.org";
	  $mailer->from($from);
	  $cc = $this->_dt->getEmails() ;
	  $mailer->cc($cc);
	  $mailer->body("Bonjour, \nvos inscriptions pour le tournoi en objet ont ete validees par les organisateurs.\nElles seront definitives lorsque vous aurez envoye le reglement correspondant.\nNe tardez pas.\n\n Bon tournoi.\n");
	  // Send the message
	  $res =  $mailer->send($to);
	  if (PEAR::isError($res))
	  echo $res->getMessage();
		}

		// Fermeture de la fenetre
		$page = new kpage('none');
		$page->close();
		exit;
	}
	// }}}


	// {{{ _validTeam()
	/**
	* Valide l'inscription d'une equipe et de ses joueurs
	*
	* @access private
	* @return void
	*/
	function _validTeam()
	{
		$assoId = kform::getData();
		$postit = array('post_assoid'  => $assoId,
		      'post_eventid' => utvars::getEventId());
		$isErr = false;

		// Supprimer les anciens messages
		// Recuperer les incriptions de l'equipe
		$this->_dt->deleteOnlinePostits($assoId);
		$regis  = $this->_dt->getFullRegistrations($assoId, true);

		// Enregistrement de l'equipe
		$dt =& $this->_dt;
		$teamId = $dt->getTeamId($assoId);

		// Premier passage : traitement des inscriptions
		$nb = count($regis);
		for($i = 0; $i<$nb; $i++)
		{
			$regi =& $regis[$i];
			$regiId = -1;
			if( $regi['lreg_poonaId']>0 )
			{
				$regiId = $dt->getPoonaRegiId($teamId, $regi);
				if (PEAR::isError($regiId))
				{
					$isErr = true;
					$postit['post_message'] = $regiId->getMessage();
					$postit['post_type']    = $regiId->getCode();
					$postit['post_registrationid']  = $regi['lreg_id'];
					$this->_dt->setPostit($postit);
					$regiId = -1;
				}
			}
			
			if ($regiId == -1)
			{
				$regiId = $dt->getRegiId($teamId, $regi);
				if (PEAR::isError($regiId))
				{
					$isErr = true;
					$postit['post_message'] = $regiId->getMessage();
					$postit['post_type']    = $regiId->getCode();
					$postit['post_registrationid']  = $regi['lreg_id'];
					$this->_dt->setPostit($postit);
					$regiId = -1;
				}
			}
			
			$regi['regiId'] = $regiId;
			$regis2[$regi['lreg_id']] = $regi;
		}

		// Enregistrement des tableaux
		foreach($regis as $player)
		{
			$nom = $player['lreg_familyname'];
			$nom .= " {$player['lreg_firstname']}";
			$isOk = true;
			$ret = $dt->setRegiSingle($player);
			if (PEAR::isError($ret))
			{
				$isOk = false;
				$isErr = true;
				$postit['post_message'] = $ret->getMessage();
				$postit['post_type']    = $ret->getCode();
				$postit['post_registrationid']  = $player['lreg_id'];
				$this->_dt->setPostit($postit);
			}

			if (isset($regis2[$player['lreg_doubleId']])) $ret = $dt->setRegiDouble($player, $regis2[$player['lreg_doubleId']]);
			else  $ret = $dt->setRegiDouble($player);
			if (PEAR::isError($ret))
			{
				$isOk = false;
				$isErr = true;
				$postit['post_message'] = $ret->getMessage();
				$postit['post_type']    = $ret->getCode();
				$postit['post_registrationid']  = $player['lreg_id'];
				$this->_dt->setPostit($postit);
			}

			if (isset($regis2[$player['lreg_mixedId']])) $ret = $dt->setRegiMixte($player, $regis2[$player['lreg_mixedId']]);
			else $ret= $dt->setRegiMixte($player);
			if (PEAR::isError($ret))
			{
				$isOk = false;
				$isErr = true;
				$postit['post_message'] = $ret->getMessage();
				$postit['post_type']    = $ret->getCode();
				$postit['post_registrationid']  = $player['lreg_id'];
				$this->_dt->setPostit($postit);
			}
			// Changer le statut de l'inscription
			if ($isOk)
			{
				$res = $this->_dt->validRegistration($player['lreg_id']);
			}
		}

		// Envoyer un message au club et supprimer les postits
		if (!$isErr)
		{
			$dt->deleteOnlinePostits($assoId);
			require_once dirname(__FILE__)."/../utils/utmail.php";
			$mailer = new utmail();
			// Recuperer les donnees
			$regis  = $this->_dt->getProposedRegistrations($assoId);
			$to = $regis['adhr']['adhe_email'];
			if ($regis['adhr']['adhe_email'] != $regis['user']['user_email'])
			$to .= ",{$regis['user']['user_email']}";
			$subject='[BadNet]'.$regis['event']['evnt_name'];
			$mailer->subject($subject);
			$cc = $dt->getEmails() ;
			$from = reset($cc); //"no-reply@badnet.org";
			$mailer->from($from);
			$mailer->cc($cc);
			$mailer->body("Bonjour, \nvos inscriptions pour le tournoi en objet ont ete validees par les organisateurs.\nElles seront definitives lorsque vous aurez envoye le reglement correspondant.\nNe tardez pas.\n\n Bon tournoi.\n");
			// Send the message
			$res =  $mailer->send($to);
			if (PEAR::isError($res))  echo $res->getMessage();
		}
		// Fermeture de la fenetre
		$page = new kpage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _displayTeam()
	/**
	* Display a page with the list of the teams
	*
	* @access private
	* @return void
	*/
	function _displayTeam($err='')
	{
		$content =& $this->_displayHead('itLine');
		$form =& $content->addForm('fline');

		if ($err!= '')
		{
	  $form->addWng($err['errMsg']);
	  if (isset($err['regi_longName']))
	  $form->addWng($err['regi_longName']);
		}

		$assoId = kform::getData();

		// Get the list of teams
		$teams = $this->_dt->getLovTeams(utvars::getEventId());
		foreach($teams as $team)
		{
	  $list[] = array( 'value' => $team['asso_id'],
			   'text'  => $team['asso_name']);
	  $sort[] = $team['asso_name'];
		}
		array_multisort($sort, $list);

		$kcombo=& $form->addCombo('selectList', $list, $assoId);
		$acts[1] = array(KAF_UPLOAD, 'line', KID_SELECT);
		$kcombo->setActions($acts);

		// Recuperer les donnees
		$regis  = $this->_dt->getProposedRegistrations($assoId);

		// Afficher les coordonnees du club
		$adhr = $regis['adhr'];
		$div =& $form->addDiv('infos', 'blkData');
		$div->addMsg('tData', '', 'kTitre');
		$div->addInfo("lineIdentity",
		    "{$adhr['adhe_firstname']} {$adhr['adhe_name']}");
		if (!empty($adhr['adhe_rue']))
		$div->addInfo("lineRue", $adhr['adhe_rue']);
		if (!empty($adhr['adhe_lieu']))
		$div->addInfo("lineLieu", $adhr['adhe_lieu']);
		$div->addInfo("lineVille",
		    "{$adhr['adhe_code']} {$adhr['adhe_ville']}");
		$div->addInfo("lineEmail", $adhr['adhe_email']);
		if (!empty($adhr['adhe_fixe']))
		$div->addInfo("lineFixe", $adhr['adhe_fixe']);
		if (!empty($adhr['adhe_mobile']))
		$div->addInfo("lineMobile", $adhr['adhe_mobile']);
		$adhr = $regis['adhr'];

		$user = $regis['user'];
		$div =& $form->addDiv('infos2', 'blkData');
		$div->addMsg('tCount', '', 'kTitre');
		$div->addInfo("lineUsername", $user['user_name']);
		$div->addInfo("lineUserpseudo", $user['user_pseudo']);
		$div->addInfo("lineUseremail", $user['user_email']);
		$form->addDiv('breaks', 'blkNewPage');

		// Recuperer les messages et les afficher
		$postits  = $this->_dt->getPostits($assoId);

		unset($acts);
		if (count($postits))
		{
	  $form->addErr('msgProblems');
	  $krows =& $form->addRows("rowsPostits", $postits);
	  $krows->setSort(0);
	  $sizes = array(1=>'0');
	  $krows->setSize($sizes);

	  $acts[] = array( 'link' => array(KAF_NEWWIN, 'line',
	  LINE_DEL_MESSAGE,0,150,150),
			   'icon' => utimg::getIcon(2102),
			   'title' => 'delete');
	  $krows->setRowActions($acts);


	  $krows->displaySelect(false);
		}

		// Afficher les inscriptions proposees
		$rows = $regis['regi2'];
		if (count($rows))
		{
	  $form->addWng('msgReceived');
	  // Menu for management of registrations
	  $itsm['itValid'] = array(KAF_NEWWIN, 'line',
	  LINE_VALID, $assoId, 650, 450);
	  $form->addMenu('menuLeft', $itsm, -1);
	  $form->addDiv('break', 'blkNewPage');

	  $krows =& $form->addRows("rowsRegiSubmited", $rows);
	  $krows->setSort(0);

	  $sizes[11] = '0+';
	  $krows->setSize($sizes);

	  $img[2] = 'indic';
	  $krows->setLogo($img);

	  $krows->displaySelect(false);
		}

		$rows = $regis['regi3'];
		if (count($rows))
		{
	  $form->addWng('msgValidated');
	  $krows =& $form->addRows("rowsRegiAccepted", $rows);
	  $krows->setSort(0);

	  $sizes[12] = '0+';
	  $krows->setSize($sizes);

	  $img[3] = 'indic';
	  $krows->setLogo($img);

	  $krows->displaySelect(false);
		}

		// Afficher tous les joueurs du club
		$players = $this->_dt->getPlayers($assoId);
		if (count($players))
		{
	  $form->addWng('msgPlayers');
	  $krows =& $form->addRows("rowsPlayers", $players);
	  $krows->setSort(0);
	  $actions[0] = array(KAF_NEWWIN, 'regi',
	  KID_EDIT, 0, 650,450);
	  $krows->setActions($actions);

		}
		$this->_utpage->display();
		exit;
	}
	// }}}


	// {{{ _displayTeams()
	/**
	* Display a page with the list of the teams
	*
	* @access private
	* @return void
	*/
	function _displayTeams($err='')
	{
		$content =& $this->_displayHead('itLine');
		$form =& $content->addForm('fline');

		if ($err!= '')
		{
	  $form->addWng($err['errMsg']);
	  if (isset($err['regi_longName']))
	  $form->addWng($err['regi_longName']);
		}

		// Menu for management of registrations
		$form->addDiv('break', 'blkNewPage');

		// Get the list of teams
		$teams = $this->_dt->getTeams(utvars::getEventId());

		$sort = kform::getSort("rowsTeams", 5);
		if (isset($teams['submited']) &&
		count($teams['submited']))
		{
	  $form->addWng('msgReceived');
	  $krows =& $form->addRows("rowsSubmited", $teams['submited']);

	  $sizes = array(10=> "0+");
	  $krows->setSize($sizes);
	  $actions[1] =	 array(KAF_UPLOAD, 'line', LINE_TEAM);
	  $krows->setActions($actions);
	  $krows->setSort(0);
	  $krows->displaySelect(false);
		}
		else
		{
	  $form->addWng('msgnosubmitedteams');
		}

		if (isset($teams['accepted']) &&
		count($teams['accepted']))
		{
	  $form->addWng('msgValidated');
	  $krows =& $form->addRows("rowsAccepted", $teams['accepted']);

	  $sizes = array(10=> "0+");
	  $krows->setSize($sizes);
	  $actions[1] =	 array(KAF_UPLOAD, 'line', LINE_TEAM);
	  $krows->setActions($actions);
	  $krows->setSort(0);
	  $krows->displaySelect(false);
		}
		else
		{
	  $form->addWng('msgnoacceptedteams');
		}

		$this->_utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayHead()
	/**
	* Display header of the page
	*
	* @access private
	* @param array team  info of the team
	* @return void
	*/
	function & _displayHead($select)
	{

		// Create a new page
		$this->_utpage = new utPage_A('line', true, 'itRegister');
		$content =& $this->_utpage->getContentDiv();

		// Add a menu for different action
		$kdiv =& $content->addDiv('choix', 'onglet3');
		if(utvars::isLineRegi())
		$items['itLine']    = array(KAF_UPLOAD, 'line',  WBS_ACT_LINE);
		$items['itTeams']     = array(KAF_UPLOAD, 'teams', WBS_ACT_TEAMS);
		$items['itPlayers']   = array(KAF_UPLOAD, 'regi', WBS_ACT_REGI);
		$items['itOfficials'] = array(KAF_UPLOAD, 'offo', WBS_ACT_OFFOS);
		//$items['itOthers']    = array(KAF_UPLOAD, 'offo', OFFO_OTHER);
		$kdiv->addMenu("menuType", $items, $select);
		$kdiv =& $content->addDiv('register', 'cont3');
		return $kdiv;
	}
	// }}}

}
