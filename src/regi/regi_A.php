<?php
/*****************************************************************************
 !   Module     : Registrations
 !   File       : $Source: /cvsroot/aotb/badnet/src/regi/regi_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.37 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/04/04 22:55:51 $
 ******************************************************************************/

require_once "base_A.php";
require_once "regi.inc";
require_once "utils/utffba.php";
require_once "utils/utibf.php";
require_once "utils/utpage_A.php";
require_once "utils/utimg.php";
require_once "utils/utdraw.php";
require_once "offo/offo.inc";
require_once "mber/mber.inc";
require_once "ajaj/ajaj.inc";
require_once "teams/teams.inc";



/**
 * Module de gestion du carnet d'adresse : classe visiteurs
 *
 * @author Didier BEUVELOT <didier.beuvelot@free.fr>
 * @see to follow
 *
 */

class regi_A
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
	function regi_A()
	{
		$this->_dt = new registrationBase_A();
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
			case WBS_ACT_REGI:
				$this->_displayFormList();
				break;
			case REGI_SEARCH:
				$this->_displaySearch();
				break;
			case REGI_SEARCH_TEAM:
				$teamId = kform::getData();
				$this->_searchFromTeam($teamId);
				break;
			case REGI_FIND:
				$this->_searchPlayer();
				break;
			case KID_NEW:
				$this->_newPlayer();
				break;
			case KID_EDIT:
				$regiId = kform::getData();
				$this->_editPlayer($regiId);
				break;

			case REGI_GET_FFBA:
				$this->_updateRankFromFede();
				break;
			case REGI_GET_FFBA_SEPT:
				$this->_updateRankFromFede();
				break;
			case REGI_UPDATE_RANK:
				$this->_updateRank();
				break;
				
				// No display but actions
			case KID_UPDATE:
				$this->_updateRegistration();
				break;
			case REGI_ALL:
				$this->_updateRegistration(true);
				break;

			case KID_CONFIRM:
				$this->_displayFormConfirm();
				break;
			case KID_DELETE:
				$this->_delRegistration();
				break;

			case REGI_PLAYERS_ABSENT:
				$this->_absent(WBS_NO);
				break;
			case REGI_PLAYERS_PRESENT:
				$this->_absent(WBS_YES);
				break;

			case REGI_PDF_PLAYERS:
				$this->_pdfPlayers();
				break;

			default :
				echo "regi_A->start($action) non autorise <br>";
				exit;
		}
	}
	// }}}

	function _updateRank()
	{
		// Recuperer les donnees depuis poona
		require_once "import/imppoona.php";
		$poona = new ImpPoona();
		$poona->updateRank();
		return;
	}
	
	// {{{ _absent()
	/**
	* Marque 'absent' les joueurs selectionne
	* Proviosire en attendant la fonction de pointage
	*
	* @access private
	* @param  integer $sort  Column selected for sort
	* @return void
	*/
	function _absent($status)
	{
		$dt = $this->_dt;

		$allId = kform::getInput("rowsRegis", array());
		$dt->absent($allId, $status);
		$page = new utPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _pdfPlayers()
	/**
	* Display a pdf document with the list of the players
	* of the current draw
	*
	* @access private
	* @param  integer $sort  Column selected for sort
	* @return void
	*/
	function _pdfPlayers()
	{
		$dt = $this->_dt;
		//$ut = $this->_ut;

		//--- Details of the players
		$titres = array ('cIdentity', 'cBorn', 'cTeam', 'cNoc', 'cLevel', 'cRank');
		$tailles = array (50, 20, 61, 15, 18, 18);
		$styles = array ('B', '', '', '', '', '');

		$sort = kform::getSort("rowsRegis",5);
		$players = $dt->getRegiTeams($sort);

		foreach($players as $player)
		{
	  $row=array();
	  $row[] = $player['regi_longName'];
	  $row[] = $player['mber_born'];
	  $row[] = $player['team_name'];
	  $row[] = $player['team_noc'];
	  $row[] = $player['rkdf_label'];
	  $row[] = $player['rank_rank'];
	  $rows[] = $row;
		}

		require_once "pdf/pdfbase.php";
		//$rows['orientation'] = 'P';
		$pdf = new pdfBase();
		$rows['top'] = $pdf->start('P', 'tPlayerList');
		$pdf->genere_liste($titres, $tailles, $rows, $styles);
		$pdf->end();

		exit;
	}
	// }}}


	// {{{ _displayFormList()
	/**
	* Display a page with the list of the registrations
	*
	* @access private
	* @return void
	*/
	function _displayFormList($err='')
	{
		$dt =& $this->_dt;

		$content =& $this->_displayHead('itPlayers');
		$form =& $content->addForm('fregi');

		if ($err!= '')
		{
	  $form->addWng($err['errMsg']);
	  if (isset($err['regi_longName']))
	  $form->addWng($err['regi_longName']);
		}

		// Menu for management of registrations
		$itsm['itNew'] = array(KAF_NEWWIN, 'regi', REGI_SEARCH, 0, 650, 450);
		//GI_NEW_PLAYER, 0, 650, 450);
		//$itsm['itDelete'] = array(KAF_NEWWIN, 'regi', KID_CONFIRM, 0, 250, 150);
		$itsm['itErase'] = array(KAF_NEWWIN, 'regi', KID_CONFIRM, 0, 250, 150);
		$itsm['itPdf'] = array(KAF_NEWWIN, 'regi', REGI_PDF_PLAYERS, 0, 500, 400);
	  	$itsm['itXlsEntries'] = array(KAF_NEWWIN, 'teams', TEAM_XLS_ENTRIES, 0, 500, 400);
		$itsm['itClt'] = array(KAF_NEWWIN, 'regi', REGI_GET_FFBA, 0, 300, 100);
		
		$itsp['itPublic'] = array(KAF_NEWWIN, 'offo',
		KID_PUBLISHED, 0, 250, 150);
		$itsp['itPrivate']   = array(KAF_NEWWIN, 'offo',
		KID_PRIVATE, 0, 250, 150);
		$itsp['itConfident'] = array(KAF_NEWWIN, 'offo',
		KID_CONFIDENT, 0, 250, 150);
		$itsp['itPresent'] = array(KAF_NEWWIN, 'regi',REGI_PLAYERS_PRESENT,
		0, 100, 100);
		$itsp['itAbsent'] = array(KAF_NEWWIN, 'regi',REGI_PLAYERS_ABSENT,
		0, 100, 100);
		$form->addMenu('menuLeft', $itsm, -1);
		$form->addMenu('menuRight', $itsp, -1);
		$form->addDiv('break', 'blkNewPage');

		$total = $dt->getNbPlayers();
		$form->addInfo('Total', $total);
		$form->addBlock('blkTotal', 'Total');

		$ut = new utils();
		// DBBN - Debut
		// Modif pour afficher tous les joueurs sur l'ecran de pointage
		$nb  = kform::getInput('Nombre', $ut->getPref('cur_nbPlayer', 500));
		$ut->setPref('cur_nbPlayer', $nb);
		$nbs = array(50=>50, 100=>100, 150=>150, 200=>200, 250=>250, 500=>500);
		// DBBN - fin
		$kcombo =& $form->addCombo('Nombre', $nbs, $nb);
		$acts[1] = array(KAF_UPLOAD, 'regi',
		WBS_ACT_REGI);
		$kcombo->setActions($acts);
		$form->addBlock('blkLast', 'Nombre');

		$nbPage = ceil($total/$nb);
		$page = 1;
		if($nbPage > 1)
		{
	  for($i=1; $i<=$nbPage;$i++) $pages[$i] = $i;
	  $page = kform::getInput('Page', 1);
	  if($page > $nbPage) $page = $nbPage;
	  $kcombo =& $form->addCombo('Page', $pages, $page);
	  $kcombo->setActions($acts);
	  $form->addBlock('blkPage', 'Page');
		}
		$first = ($page-1)*$nb;
		$form->addBtn('btnGo', KAF_UPLOAD, 'regi',
		WBS_ACT_REGI);
		$form->addBlock('blkGo', 'btnGo');

		$ute = new utevent();
		$event = $ute->getEvent(utvars::getEventId());
		if ($event['evnt_urlrank'] == '')
		{
	  $event['evnt_urlrank'] = $ut->getParam('ffba_url');
		}
		// Display the list of players
		$sort = kform::getSort("rowsRegis",5);
		$rows = $dt->getRegiTeams($sort, $first, $nb);

		if (isset($rows['errMsg']))
		$form->addWng($rows['errMsg']);
		else
		{
		
	  $krows =& $form->addRows("rowsRegis", $rows);

	  $column[2] = 0;
	  $column[6] = 0;
	  $column[8] = 0;
	  $krows->setSortAuth($column);

	  $sizes = array(1=>'0', 10=> "0+");
	  $krows->setSize($sizes);

	  $img[2] = 'mber_urlphoto';
	  $img[4] = 'regi_pbl';
	  //$img[5] = 'regi_datesurclasse';
	  $img[7] = 'regi_dateauto';
	  $img[9] = 'team_pbl';
	  $krows->setLogo($img);

	  $actions[0] = array(KAF_NEWWIN, 'regi',
	  KID_EDIT, 0, 650,450);
	  $actions[4] =	 array(KAF_UPLOAD, 'resu', KID_SELECT);
	  $actions[6] =	 array(KAF_NEWWINURL, $event['evnt_urlrank'], 0, 0, 650, 450);
	  $actions[9] =	 array(KAF_UPLOAD, 'teams',
	  KID_SELECT, 'team_id');
	  $krows->setActions($actions);
		}

		// Legend
		$form->addImg('lgdConfident', utimg::getIcon(WBS_DATA_CONFIDENT));
		$form->addImg('lgdPrivate', utimg::getIcon(WBS_DATA_PRIVATE));
		$form->addImg('lgdPublic', utimg::getIcon(WBS_DATA_PUBLIC));
		$elts=array('lgdConfident', 'lgdPrivate', 'lgdPublic');
		$form->addBlock("blkLegende", $elts);

		$this->_utpage->display();
		exit;
	}
	// }}}

	// {{{ _newPlayer()
	/**
	* Prepare les donnees pour la creatin d'une nouvelle inscription
	* Origine : depuis la fenetre de recherche
	*     - bouton nouveau : '-1;-1' est recu comme donnee generale
	*     - bouton valide  : '0;0' est recu comme donnee generale;
	*                        les joueurs a inscrire sont ceux selectionnes dans la liste
	*     - choix d'un joueur : 'localI;fedeId' est recu comme donnee generale
	*                        les joueurs a inscrire sont ceux selectionnes dans la liste
	*                        et celui de la donnees generale
	* Origine : enregistrement du joueur suivant de la liste
	*            '0;0' est recu comme donnee generale;
	*            les joueurs a inscrire sont ceux selectionnes dans la liste
	*
	* @access private
	* @return void
	*/
	function _newPlayer($auto = false, $autoList = array())
	{
		$dt =& $this->_dt;
		$ut = new utils();
		$isSquash = $ut->getParam('issquash', false);
		
		// Initialisation des donnees
		$rank = array('rank_averageS'   => 0,
		    'rank_averageD'   => 0,
		    'rank_averageM'   => 0,
		    'rank_rankS'      => -1,
		    'rank_rankD'      => -1,
		    'rank_rankM'      => -1,
		    'rank_isFedeS'    => 0,
		    'rank_isFedeD'    => 0,
		    'rank_isFedeM'    => 0,
		    'rank_dateS'      => '',
		    'rank_dateD'      => '',
		    'rank_dateM'      => '',
		    'rank_classeS'    => 999999,
		    'rank_classeD'    => 999999,
		    'rank_classeM'    => 999999
		);
		$play = array('single' => -1,
		    'oldSinglePair' => -1,
		    'double'        => -1,
		    'doublePair'    => -1,
		    'oldDoublePair' => -1,
		    'mixed'         => -1,
		    'mixedPair'     => -1,
		    'oldMixedPair'  => -1);
		$regi['regi_longName']      = '';
		$regi['regi_shortName']     = '';
		$regi['regi_teamId'] = kform::getInput('teamId', -1);
		$regi['regi_id']     = -1;
		$regi['regi_accountId'] = -1;
		$regi['regi_cmt']           = '';
		$regi['regi_date'] = date(DATE_DATE);
		$regi['regi_catage']        = WBS_CATAGE_SEN;
		$regi['regi_numcatage']     = 1;
		$regi['regi_dateauto']      = '';
		$regi['regi_surclasse']     = WBS_SURCLASSE_NONE;
		$regi['regi_datesurclasse'] = '';
		$regi['regi_type']          = WBS_PLAYER;
		$regi['regi_status']        = kform::getInput("regiStatus", WBS_REGI_AUTRE);
		$regi['regi_eventId']       = utvars::getEventId();		
		
		// Donnee generale
		$data = kform::getData();
		// Joueur selectionnes
		$sel = kform::getInput('rowsFind', array());
		// Construction de la liste des joueurs selectionnes
		$players = array();
		// Enregistrement automatique des joueurs
		if ($auto) $players = $autoList;
		// Enregistrement manuel des joueurs: passage au suivant
		else if ($data == '0;0') $players = $sel;
		// Premier appel depuis la fenetre de recherche
		else
		{
			$players = $sel;
			if (!in_array($data, $sel) && $data != '') $players[] = $data;
		}

		if (!count($players))
		{
			$page = new utPage('none');
			$page->close();
			exit;
		}

		// Traitement du premier joueur de la liste
		$player = array_pop($players);
		list($memberId, $fedeId) = explode(';', $player);
		if ($memberId != -1 || $fedeId != -1)
		{
			$member = $dt->getMember($memberId, $fedeId);
		}
		else
		{
			$member = array('mber_id'         => -1,
			  'mber_fedeId'     => -1,
			  'mber_sexe'       =>kform::getInput('mberSexe'),
			  'mber_secondname' =>strtoupper(kform::getInput('mberSecondName')),
			  'mber_firstname'  =>'',
			  'mber_ibfnumber'  =>'',
			  'mber_licence'    =>kform::getInput('mberLicence'),
			  'mber_born'       =>'',
			  'mber_urlphoto'   =>'');
		}
		//Cas du squash
		if($isSquash)
		{
			$rank['rank_classeS'] = $member['mber_sexe']==WBS_MALE?4800:1400;
		}
		
		// Joueur issu de la base federale : initialisation des champs sportifs
		// avec les infos recuperees
		if ($fedeId != -1)
		{
			require_once "import/imppoona.php";
			$poona = new ImpPoona();
			$player = $poona->getPlayer($fedeId);
			if (!isset($player['errMsg']))
			{
				$member['mber_fedeId']      = $player['mber_fedeid'];
				$member['mber_sexe']        = $player['mber_sexe'];
				$member['mber_secondname']  = $player['mber_secondname'];
				$member['mber_firstname']   = $player['mber_firstname'];
				$member['mber_licence']     = $player['mber_licence'];
				$member['mber_born']        = $player['mber_born'];
				$member['mber_cmt']         = 'Origine poona '.date(DATE_DATE);
				$prenom = ucwords(strtolower($member['mber_firstname']));
				$nom = strtoupper($member['mber_secondname']);
				$regi['regi_longName']      = "$nom $prenom";
				$regi['regi_shortName']     = "$nom ".substr($prenom, 0, 1).".";
				$regi['regi_cmt']           = 'Origine poona '.date(DATE_DATE);
				if (!utvars::isTeamEvent())
				{
					$regi['regi_teamId'] = $dt->getTeamFromFede($player['asso_fedeid']);
				}
				if ($isSquash) 
				{
					$regi['regi_catage'] = $dt->getCatage($player['mber_born']);
				}
				else $regi['regi_catage']   = $player['regi_catage'];
				$regi['regi_numcatage']     = $player['regi_numcatage'];
				$regi['regi_dateauto']      = $player['regi_dateauto'];
				$regi['regi_surclasse']     = $player['regi_surclasse'];
				$regi['regi_datesurclasse'] = $player['regi_datesurclasse'];
				$rank['rank_rankS']    = $player['rank_simple'];
				$rank['rank_rankD']    = $player['rank_double'];
				$rank['rank_rankM']    = $player['rank_mixte'];
				$rank['rank_averageS'] = $player['rank_cpppsimple'];
				$rank['rank_averageD'] = $player['rank_cpppdouble'];
				$rank['rank_averageM'] = $player['rank_cpppmixte'];
				$rank['rank_classeS'] = $player['rank_ranks'];
				$rank['rank_classeD'] = $player['rank_rankd'];
				$rank['rank_classeM'] = $player['rank_rankm'];

				
				// Enregistrer directement le joueur issu de la fede
				if ($auto)
				{
					$this->_updateCurrentRegistration($member, $regi, $rank, $play);
					$this->_newPlayer($auto, $players);
				}
			}
		}
		if ($isSquash && !utvars::isTeamEvent())
		{
			if ($member['mber_sexe'] == WBS_MALE) $play['single'] = $dt->getFirstDraw(WBS_MS, -1);
			else $play['single'] = $dt->getFirstDraw(WBS_WS, -1);
		}
		
		// Afficher le page de saisie des donnees
		$this->_displayRegisterPlayer($member, $regi, $rank, $play, $players);

	}
	// }}}

	// {{{ _editPlayer()
	/**
	* Prepare les donnes pour modifier une inscription
	*
	* @access private
	* @return void
	*/
	function _editPlayer($regiId)
	{
		$dt =& $this->_dt;
		$member = $dt->getRegiMember($regiId);
		$regi   = $dt->getRegiRegis($regiId);
		$rank   = $dt->getRegiRank($regiId);
		$play   = $dt->getRegiPlay($regiId);

		// Afficher le page de saisie des donnees
		$this->_displayRegisterPlayer($member, $regi, $rank, $play);
	}
	// }}}

	// {{{ _displayRegisterPlayer()
	/**
	* Display the page for creating or updating registration
	*
	* @access private
	* @param array $registration  info of the registration
	* @param boolean $mod         can the user modify member's information
	* @return void
	*/
	function _displayRegisterPlayer($member, $regi, $rank, $play, $players=array())
	{
		$dt =& $this->_dt;
		$ut = new utils();
		for($i=WBS_CATAGE_POU; $i<=WBS_CATAGE_VET; $i++) $catage[$i] = $ut->getLabel($i);
		for($i=WBS_SURCLASSE_NONE; $i<=WBS_SURCLASSE_SP; $i++) $surclasse[$i] = $ut->getLabel($i);

		$utpage = new utPage('regi');
		$utpage->_page->addAction('onload', array('resize', 720, 500));
		$utpage->_page->addJavaFile('regi/regi.js');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tEditPlayer', 'regi', KID_UPDATE);

		// Display the error if any
		if (isset($regi['errMsg']))
		{
			$form->addWng($regi['errMsg']);
			unset($regi['errMsg']);
		}

		// Initialisation des champs caches
		$form->addHide('mberId', $member['mber_id']);
		$form->addHide('mberFedeId', $member['mber_fedeId']);
		$form->addHide('regiId', $regi['regi_id']);
		$form->addHide('teamId', $regi['regi_teamId']);
		$form->addHide('oldSinglePair', $play['oldSinglePair']);
		$form->addHide('oldDoublePair', $play['oldDoublePair']);
		$form->addHide('oldMixedPair',  $play['oldMixedPair']);
		foreach($players as $player) $form->addHide('rowsFind[]', $player);

		// Le membre est modifiable par un administrateur,
		// ou modifiable non import Poona
		$actions[] =	 array(KAF_AJAJ, 'updateDrawList', AJAJ_REGI1);
		if ( utvars::_getSessionVar('userAuth') == WBS_AUTH_ADMIN || $member['mber_fedeId'] < 1)
		{

			$kradio =& $form->addRadio('mberSexe',
			$member['mber_sexe'] == WBS_MALE, WBS_MALE);
			$kradio->setActions($actions);
			$kradio =& $form->addRadio('mberSexe',
			$member['mber_sexe']==WBS_FEMALE, WBS_FEMALE);
			$kradio->setActions($actions);

			$kedit =& $form->addEdit('mberSecondName',
			$member['mber_secondname'],30);
			$kedit->setMaxLength(30);
			$kedit =& $form->addEdit('mberFirstName',
			$member['mber_firstname'],30);
			$kedit->setMaxLength(30);
			$kedit =& $form->addEdit('mberBorn', $member['mber_born']);
			$kedit->setMaxLength(25);
			$kedit->noMandatory();
			$kedit =& $form->addEdit('mberIbfNumber',
			$member['mber_ibfnumber']);
			$kedit->setMaxLength(10);
			$kedit->noMandatory();
			$kedit =& $form->addEdit('mberLicence', $member['mber_licence']);
			$kedit->setMaxLength(8);
			$kedit->noMandatory();
		}
		else
		{
			$form->addHide('mberSexe', $member['mber_sexe']);
			$form->addInfo('mberSexeLabel', $ut->getLabel($member['mber_sexe']));
			$form->addInfo('mberSecondName', $member['mber_secondname']);
			$form->addInfo('mberFirstName',  $member['mber_firstname']);
			$form->addInfo('mberBorn',       $member['mber_born']);
			$form->addInfo('mberIbfNumber',  $member['mber_ibfnumber']);
			$form->addInfo('mberLicence',    $member['mber_licence']);
			//$form->addInfo('regiDateAuto',   $regi['regi_dateauto']);
			//$form->addInfo('regiNumcatage',  $regi['regi_numcatage']);
			//$form->addHide('regiCatage',     $regi['regi_catage']);
			//$form->addInfo('regiCatageLabel', $catage[$regi['regi_catage']]);
			//$form->addHide('regiSurclasse',  $regi['regi_surclasse']);
			//$form->addInfo('regiSurclasseLabel',  $surclasse[$regi['regi_surclasse']]);
			//$form->addInfo('regiDateSurclasse',  $regi['regi_datesurclasse']);
		}
		$kedit =& $form->addEdit('regiDateAuto', $regi['regi_dateauto'],10);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kcombo =& $form->addCombo('regiCatage', $catage, $regi['regi_catage']);
		$kcombo->setActions($actions);

		// Numero des categories d'age
		switch($regi['regi_catage'])
		{
			case WBS_CATAGE_POU: $max = 0; break;
			case WBS_CATAGE_SEN: $max = 0; break;
			case WBS_CATAGE_VET: $max = 5; break;
			default: $max = 2; break;
		}
		for($i=0; $i<=$max; $i++) $num[$i] = $i;
		$kcombo =& $form->addCombo('regiNumcatage', $num, $regi['regi_numcatage']);
		$kcombo->setActions($actions);
		$kcombo =&$form->addCombo('regiSurclasse', $surclasse,
		$surclasse[$regi['regi_surclasse']]);
		$kcombo->setActions($actions);
		$disas = array( WBS_CATAGE_POU => array(WBS_SURCLASSE_SIMPLE,
		WBS_SURCLASSE_DOUBLE,
		WBS_SURCLASSE_VA,
		WBS_SURCLASSE_SE),
		WBS_CATAGE_BEN => array(WBS_SURCLASSE_VA,
		WBS_SURCLASSE_SE,
		WBS_SURCLASSE_SP),
		WBS_CATAGE_MIN => array(WBS_SURCLASSE_VA,
		WBS_SURCLASSE_SP),
		WBS_CATAGE_CAD => array(WBS_SURCLASSE_VA,
		WBS_SURCLASSE_SE,
		WBS_SURCLASSE_SP),
		WBS_CATAGE_JUN => array(WBS_SURCLASSE_DOUBLE,
		WBS_SURCLASSE_VA,
		WBS_SURCLASSE_SE,
		WBS_SURCLASSE_SP),
		WBS_CATAGE_SEN => array(WBS_SURCLASSE_SIMPLE,
		WBS_SURCLASSE_DOUBLE,
		WBS_SURCLASSE_VA,
		WBS_SURCLASSE_SE,
		WBS_SURCLASSE_SP),
		WBS_CATAGE_VET => array(WBS_SURCLASSE_SIMPLE,
		WBS_SURCLASSE_DOUBLE,
		WBS_SURCLASSE_SE,
		WBS_SURCLASSE_SP),
		);
		$kcombo->disabled($disas[$regi['regi_catage']]);
		$kedit =& $form->addEdit('regiDateSurclasse', $regi['regi_datesurclasse'], 10);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();

		// Organisation des zones
		$elts = array('mberSexe', 'mberSexeLabel'
		, 'mberSecondName', 'mberFirstName'
		, 'mberLicence', 'mberIbfNumber', 'mberBorn'
		);
		$form->addBlock('blkAdmi1', $elts);
		$elts = array('regiDateAuto', 'regiCatage', 'regiNumcatage',
		'regiCatageLabel', 'regiSurclasse', 'regiDateSurclasse', 'regiSurclasseLabel'
		, 'regi_date'
		);
		$form->addBlock('blkAdmi2', $elts);

		$elts = array('blkAdmi1', 'blkAdmi2');
		$form->addBlock('blkAdmi', $elts, 'classRegi');

		// Initialize the field for registration
		$kedit =& $form->addEdit('regiDate', $regi['regi_date'],11);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();

		// Get teams from database
		$teams = $dt->getTeams();
		if (isset($teams['errMsg']))
		{
			$teams[''] = $teams['errMsg'];
			unset($teams['errMsg']);
		}
		if($regi['regi_teamId']!=-1) $form->addCombo('regiTeamId', $teams, $regi['regi_teamId']);
		else $form->addCombo('regiTeamId', $teams, '');

		$form->addHide('regiAccountId', $regi['regi_accountId']);
		$form->addHide('regiCmt', $regi['regi_cmt']);

		// Initialize the field for the ranking
		$form->addMsg('mLevel', 'Niveau');
		$form->addMsg('mPoints', 'Points');
		$form->addMsg('mClasse', 'Rang');
		$ranks = $dt->getRanks();
		if (isset($ranks[$rank['rank_rankS']]))	$kcombo =& $form->addCombo('rankRankS',	$ranks, $ranks[$rank['rank_rankS']]);
		else $kcombo =& $form->addCombo('rankRankS', $ranks);
		$kcombo->setActions($actions);
		$form->addHide('rankIsFedeS',$rank['rank_isFedeS']);
		$kedit =& $form->addEdit('rankAverageS',$rank['rank_averageS'], 11);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('rankClasseS',$rank['rank_classeS'], 11);
		$kedit->setMaxLength(10);
		$kedit->noMandatory();

		$isSquash = $ut->getParam('issquash', false);
		if( !$isSquash )
		{
			$form->addHide('rankIsFedeD',$rank['rank_isFedeD']);
			$kedit =& $form->addEdit('rankAverageD',$rank['rank_averageD'], 11);
			$kedit->setMaxLength(10);
			$kedit->noMandatory();
			$kedit =& $form->addEdit('rankClasseD',$rank['rank_classeD'], 11);
			$kedit->setMaxLength(10);
			$kedit->noMandatory();

			$form->addHide('rankIsFedeM',$rank['rank_isFedeM']);
			$kedit =& $form->addEdit('rankAverageM',$rank['rank_averageM'], 11);
			$kedit->setMaxLength(10);
			$kedit->noMandatory();
			$kedit =& $form->addEdit('rankClasseM',$rank['rank_classeM'], 11);
			$kedit->setMaxLength(10);
			$kedit->noMandatory();

			if (isset($ranks[$rank['rank_rankD']]))	$kcombo =& $form->addCombo('rankRankD', $ranks,	$ranks[$rank['rank_rankD']]);
			else $kcombo =& $form->addCombo('rankRankD', $ranks);
			$kcombo->setActions($actions);
			if (isset($ranks[$rank['rank_rankM']]))	$kcombo =& $form->addCombo('rankRankM', $ranks,	$ranks[$rank['rank_rankM']]);
			else $kcombo =& $form->addCombo('rankRankM', $ranks);
			$kcombo->setActions($actions);
		}

		if (utvars::isTeamEvent())
		{
	  $form->addRadio('regiStatus', $regi['regi_status']== WBS_REGI_TITULAIRE, WBS_REGI_TITULAIRE);
	  $form->addRadio('regiStatus', $regi['regi_status']== WBS_REGI_REMPLACANT, WBS_REGI_REMPLACANT);
	  $form->addRadio('regiStatus', $regi['regi_status']== WBS_REGI_AUTRE, WBS_REGI_AUTRE);
		}
		// Initialize the field for select the draws and pairs
		// to followed
		else
		{
			$form->addMsg('mTableau', 'Tableau');
			if( !$isSquash ) $form->addMsg('mPartenaire', 'Partenaire');

			$utd = new utdraw();
			$select = array('mber_sexe'  =>$member['mber_sexe'],
			  'regi_catage'        =>$regi['regi_catage'],
			  'regi_numcatage'     =>$regi['regi_numcatage'],
	  		  'regi_dateauto'      =>$regi['regi_dateauto'],
			  'regi_surclasse'     =>$regi['regi_surclasse'],
			  'regi_datesurclasse' =>$regi['regi_datesurclasse']);
			$select['rank']  = $rank['rank_rankS'];
			$draws = $utd->getDrawsList($select, WBS_SINGLE);
			$draws[-1] = "----";
			if (isset($draws[$play['single']]))  $kcombo =& $form->addCombo('single', $draws, $play['single']);
			else  $kcombo =& $form->addCombo('single', $draws, $draws[-1]);

			if( !$isSquash )
			{
				$select['rank']  = $rank['rank_rankD'];
				$draws = $utd->getDrawsList($select, WBS_DOUBLE);
				$draws[-1] = "----";
				if (isset($draws[$play['double']])) $kcombo =& $form->addCombo('double', $draws, $play['double']);
				else  $kcombo =& $form->addCombo('double', $draws, $draws[-1]);
				$kcombo->setActions($actions);

				$pairs = $utd->getFreePlayers($play['double'], $member['mber_sexe'], $regi['regi_id']);
				$pairs[-1] = "----";
				if (isset($pairs[$play['doublePair']])) $kcombo =& $form->addCombo('doublePair', $pairs, $play['doublePair']);
				else $kcombo =& $form->addCombo('doublePair', $pairs, $pairs[-1]);
				$select['rank']  = $rank['rank_rankM'];
				$draws = $utd->getDrawsList($select, WBS_MIXED);
				$draws[-1] = "----";
				if (isset($draws[$play['mixed']])) $kcombo =& $form->addCombo('mixed', $draws, $play['mixed']);
				else $kcombo =& $form->addCombo('mixed', $draws, $draws[-1]);
				$kcombo->setActions($actions);
				if ($member['mber_sexe'] == WBS_MALE)
				$pairs = $utd->getFreePlayers($play['mixed'], WBS_FEMALE, $regi['regi_id']);
				else $pairs = $utd->getFreePlayers($play['mixed'], WBS_MALE, $regi['regi_id']);
				$pairs[-1] = "----";
				if (isset($pairs[$play['mixedPair']])) $kcombo =& $form->addCombo('mixedPair', $pairs, $play['mixedPair']);
				else $kcombo =& $form->addCombo('mixedPair', $pairs, $pairs[-1]);
			}
		}

		$elts = array('regiDate', 'regiTeamName', 'regiTeamId');
		$form->addBlock('blkStatus', $elts);

		$elts = array('regiDate', 'regiTeamName', 'regiTeamId');
		$form->addBlock('blkInfo', $elts);

		$elts = array('mLevel', 'rankRankS', 'rankRankD', 'rankRankM');
		$form->addBlock('blkRank', $elts);

		$elts = array('mClasse', 'rankClasseS', 'rankClasseD', 'rankClasseM');
		$form->addBlock('blkClasse', $elts);

		$elts = array('mPoints', 'rankAverageS', 'rankAverageD', 'rankAverageM');
		$form->addBlock('blkAverage', $elts);

		$elts = array('mTableau', 'single', 'double',  'mixed');
		$form->addBlock('blkDraw', $elts);

		$elts = array('mPartenaire', 'doublePair',  'mixedPair');
		$form->addBlock('blkPair', $elts);


		$elts = array('blkInfo', 'blkRank','blkAverage', 'blkClasse', 'blkDraw',
		    'blkPair', 'regiStatus1', 'regiStatus2', 'regiStatus3');
		$form->addBlock('blkRegi', $elts, 'classRegi');

		$form->addDiv('page', 'blkNewPage');
		if (utvars::isTeamEvent() &&
		$regi['regi_teamId']!=-1 &&
		count($players))
		$form->addBtn('btnRegisterAll', KAF_VALID, 'regi', REGI_ALL);
		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnSearchLocal', 'btnNew', 'btnRegister',
		    'btnRegisterAll', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the form
		$utpage->display();

		exit;
	}
	// }}}

	// {{{ _searchFromTeam()
	/**
	* prepare l'affichage de la fentre de recherche de joueur
	* pour une equipe
	*
	* @access private
	* @return void
	*/
	function _searchFromTeam($teamId)
	{
		$dt =& $this->_dt;
		$assoId  = $dt->getAssoIdFromTeam($teamId);
		$critere = array(
		       'searchType'    =>1,
		       'mber_sexe'       =>'',
		       'mber_name'       =>'',
		       'mber_licence'    =>'',
		       'regi_catage'     =>'',
		       'regi_numcatage'  =>0,
			   'asso_dpt'     =>'',
		       'regi_instanceId' =>$assoId,
			   'page' =>kform::getInput('page', 1),
		       'nb' => 100
		);

		$players = $dt->searchPlayers($critere, true, $teamId);
		$this->_displaySearch($players, $teamId, $assoId);
	}
	//}}}

	// {{{ _searchPlayer()
	/**
	* reherche les joueurs en fonction des criteres
	* pour une equipe
	*
	* @access private
	* @return void
	*/
	function _searchPlayer()
	{
		$dt =& $this->_dt;
		$critere = array(
		       'searchType'      =>kform::getInput('searchType', 1),
			   'mber_sexe'       =>kform::getInput('mberSexe'),
		       'mber_name'       =>strtoupper(kform::getInput('mberSecondName')),
		       'mber_licence'    =>kform::getInput('mberLicence'),
		       'regi_catage'     =>kform::getInput('regiCatage'),
		       'regi_numcatage'  =>kform::getInput('regiNumcatage'),
			   'regi_instanceId' =>kform::getInput('assoId'),
			   'asso_dpt'        =>kform::getInput('assoDpt', ''),
		       'page' =>kform::getInput('page', 1),
		       'nb' => 100
		);
		$isLicencie = kform::getInput('licencie', 0) == 0;

		/*if ($critere['mber_licence'] == '' &&
		 ($critere['mber_name'] == '' ||
		 strlen($critere['mber_name'])<3)&&
		 $critere['regi_instanceId'] == '-1;-1')
		 $players['errMsg'] = 'msgNeedCriteria';
		 else*/
		$players = $dt->searchPlayers($critere, $isLicencie, kform::getInput('teamId'));
		$this->_displaySearch($players, kform::getInput('teamId'));
	}
	//}}}

	// {{{ _displaySearch()
	/**
	* Affiche la fentre de recherche de joueur
	*
	* @access private
	* @return void
	*/
	function _displaySearch($players = array(), $teamId = -1, $assoId='')
	{
		$dt =& $this->_dt;

		// Creation de la page
		$utpage = new utPage('regi');
		$utpage->_page->addAction('onload', array('resize', 680, 500));
		$utpage->_page->addJavaFile('regi/regi.js');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tSearchPlayer', 'regi', REGI_FIND);
		$form->addHide('teamId', $teamId);

		if (isset($players['errMsg']))
		{
	  $form->addWng($players['errMsg']);
	  unset($players['errMsg']);
		}
			
		// Critere de recherche
		$searchType = kform::getInput('searchType', 1);
		$critere = array(
		       'searchType'      => $searchType,
			   'mber_sexe'       =>kform::getInput('mberSexe', ''),
		       'mber_secondname' =>strtoupper(kform::getInput('mberSecondName','')),
		       'mber_licence'    =>kform::getInput('mberLicence', ''),
		       'regi_catage'     =>kform::getInput('regiCatage', ''),
		       'regi_numcatage'  =>kform::getInput('regiNumcatage', ''),
			   'asso_dpt'        =>kform::getInput('assoDpt', ''),
		       'asso_id'         =>kform::getInput('assoId', $assoId));

		if (isset($players['pager']) )	$pager = $players['pager'];
		unset($players['pager']);

		// Liste des associations inscrites
		// si le departement n'est pas renseigne
		if ($critere['asso_dpt'] == '')
		$assos = $dt->getRegAssos();
		// Liste des associations du departement
		else
		{
	  $asso = array('asso_name'   =>'',
			'asso_pseudo' =>'',
			'asso_stamp'  =>'',
			'asso_dpt'    =>$critere['asso_dpt'],
			'asso_type'   =>''); //WBS_CLUB);
	  require_once "import/imppoona.php";
	  $poona = new ImpPoona();
	  $instances = $poona->searchInstance($critere);
	  //print_r($instances);
	  $assos = array();
	  if (isset($instances['errMsg']))
	  $form->addWng('msgPoonaNotAvailable');
	  else
	  if (!empty($instances))
	  foreach($instances as $instance)
	  $assos[$instance['asso_id']] = $instance['asso_name'];
		}
		$tmp = array('-1;-1' => "----");
		$assos = array_merge($tmp, $assos);

		// Champ de saisie pour la recherche
		$ut = new utils();

		$form->addRadio('searchType', $searchType == 1, 1);
		$form->addRadio('searchType', $searchType == 2, 2);

		$sexe = array( '' => '----',
		WBS_MALE => $ut->getLabel(WBS_MALE),
		WBS_FEMALE => $ut->getLabel(WBS_FEMALE));
		$kcombo =& $form->addCombo('mberSexe', $sexe, $sexe[$critere['mber_sexe']]);
		$kcombo->noMandatory();

		$kedit =& $form->addEdit('mberSecondName',
		$critere['mber_secondname'], 30);
		$kedit->setMaxLength(20);
		$kedit->noMandatory();

		$kedit =& $form->addEdit('mberLicence',
		$critere['mber_licence'], 11);
		$kedit->setMaxLength(8);
		$kedit->noMandatory();

		$catage[''] = '----';
		$ut = new utils();
		for ($i=WBS_CATAGE_POU; $i <= WBS_CATAGE_VET; $i++)
		$catage[$i] = $ut->getLabel($i);
		$kcombo =& $form->addCombo('regiCatage', $catage, $catage[$critere['regi_catage']]);
		$kcombo->noMandatory();

		$lst = utvars::getDepartements();
		$tmp = array('' => "----");
		$lst = $tmp + $lst;
		$kcombo =& $form->addCombo('assoDpt', $lst, $lst[$critere['asso_dpt']]);
		$kcombo->noMandatory();
		$actions[0] = array(KAF_AJAJ, 'updateClubList', WBS_ACT_REGI);
		$kcombo->setActions($actions);

		$kedit =& $form->addCombo('assoId', $assos, $critere['asso_id']);
		$kedit->noMandatory();

		$form->addCheck('licencie', kform::getInput('licencie', false));

		$form->addBtn('btnSearch', KAF_SUBMIT);
		//pager
		if ( !empty($pager) && $pager['nbmax'] > $pager['nb'] )
		{
	  $form->addHide('page', $pager['page']);
	  $form->addBtn('btnPrev', 'pager', $pager['page']-1, 0);
	  $form->addBtn('btnNext', 'pager', $pager['page']+1, 0);
	  $str = ($pager['nb'] * ($pager['page']-1)) + 1 . ' - ' ;
	  $str .= $pager['nb'] * $pager['page'] .' / ' ;
	  $str .= $pager['nbmax'];
	  $form->addInfo('total', $str);
		}

		$elts = array('searchType1', 'licencie', 'searchType2');
		$form->addBlock('blkT', $elts);

		$elts = array('mberLicence', 'mberSecondName');
		$form->addBlock('blk1', $elts);
		$elts = array('mberSexe', 'regiCatage');
		$form->addBlock('blk2', $elts);
		$elts = array('blk2',  'blk1');
		$form->addBlock('blkC', $elts);

		$elts = array('assoDpt',  'assoId');
		$form->addBlock('blkL', $elts);

		$elts = array('btnSearch', 'btnPrev', 'btnNext', 'total');
		$form->addBlock('blkB', $elts);
		$elts = array('blkT', 'blkC', 'blkL', 'blkB');
		$form->addBlock('blkCriteria', $elts, 'classRegi');

		if (isset($assos['errMsg']))
		{
	  $form->addWng($assos['errMsg']);
	  unset($assos['errMsg']);
		}

		if (count($players))
		{
	  $krows =& $form->addRows('rowsFind', $players);
	  $krows->setSort(0);
	  $sizes[7] = '0+';
	  $krows->setSize($sizes);
	  $act = array();
	  $act[2] = array(KAF_UPLOAD, 'regi', KID_NEW);
	  $krows->setActions($act);
		}


		// Boutons de commande
		$form->addBtn('btnNew', KAF_VALID, 'regi', KID_NEW, '-1;-1');
		$form->addBtn('btnValidate', KAF_UPLOAD, 'regi', KID_NEW, '0;0');
		$form->addBtn('btnHelp', KAF_NEWWIN, 'help', REGI_SEARCH, 0, 500, 400);
		$form->addBtn('btnCancel');

		$elts = array('btnHelp', 'btnNew', 'btnValidate', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _delRegistration()
	/**
	* Delete registrations in the database
	*
	* @access private
	* @return void
	*/
	function _delRegistration()
	{
		$dt = $this->_dt;
		// Get the informations
		$ids  = kform::getInput("rowsRegis");

		// Delete the informations
		$err = $dt->delRegistrations($ids);
		if (isset($err['errMsg']))
		$this->_displayFormConfirm($err['errMsg']);

		$page = new utPage('none');
		$page->close();
		exit();
	}
	// }}}


	// {{{ _updateRegistration()
	/**
	* Create the registration in the database
	*
	* @access private
	* @return void
	*/
	function _updateRegistration($auto=false)
	{
		// First get the informations
		$member = $this->_getFieldsMember();
		$regi   = $this->_getFieldsRegi();
		$rank   = $this->_getFieldsRank();
		$play   = $this->_getFieldsPlay();
		$players = kform::getInput('rowsFind', array());

		$res = $this->_updateCurrentRegistration($member, $regi, $rank, $play);
		if(is_array($res))
		{
	  $regi['errMsg'] = $res['errMsg'];
	  $this->_displayRegisterPlayer($member, $regi, $rank, $play, $players);
		}
		$this->_newPlayer($auto, $players);
	}

	// {{{ _updateCurrentRegistration()
	/**
	* Create the registration in the database
	*
	* @access private
	* @return void
	*/
	function _updateCurrentRegistration(&$member, &$regi, &$rank, &$play)
	{
		$dt =& $this->_dt;

		// Control the informations
		if (isset($member['mber_urlphoto']))
		unset($member['mber_urlphoto']);
		if ($member['mber_sexe'] != WBS_MALE &&
		$member['mber_sexe'] != WBS_FEMALE)
		{
	  $infos['errMsg'] = 'msgmberSexe';
	  return $infos;
		}

		if ($member['mber_firstname'] == "")
		{
	  $infos['errMsg'] = 'msgmberFirstName';
	  return $infos;
		}

		if ($member['mber_secondname'] == "")
		{
	  $infos['errMsg'] = 'msgmberSecondName';
	  return $infos;
		}

		if ($regi['regi_longName'] == "")
		{
			$regi['regi_longName'] = $member['mber_secondname']." ".
			$member['mber_firstname'];
		}
		if ($regi['regi_shortName'] == "")
		{
			$regi['regi_shortName'] = $member['mber_secondname']." ".
			substr($member['mber_firstname'], 0, 1).".";
		}

		// Update the member
		require_once "mber/base_A.php";
		$dtm = new memberBase_A();
		$res = $dtm->updateMember($member);
		if (is_array($res))
		return $res;

		$member['mber_id'] = $res;
		// update the registration
		$res = $dt->updateRegistration($member, $regi, $rank, $play);
		return $res;
	}
	// }}}

	// {{{ _updateRankFromFede()
	/**
	* Update the ranking of selected players from federation data
	*
	* @access private
	* @return void
	*/
	function _updateRankFromFede($september=false)
	{
		$dt =& $this->_dt;
		$teamId = kform::getData();
		$players = kform::getInput("rowsPlayers", NULL);
		if (empty($players) ) $players = kform::getInput("rowsRegis", NULL);
		
		$err = $dt->updateRankFromFede($teamId, $players);

		// Display error message if any
		if (is_array($err)) print_r($err);

		$page = new utPage('none');
		//$page->close();
		exit;
	}
	// }}}

	// {{{ _displayFormConfirm()
	/**
	* Display the page for confirmation the destruction of purchase
	*
	* @access private
	* @param array $member  info of the member
	* @return void
	*/
	function _displayFormConfirm($err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('regi');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tDelRegistration', 'regi', KID_DELETE);

		// Initialize the field
		$allId = kform::getInput("rowsRegis", array());
		$playersId = kform::getInput("rowsPlayers", array());
		$offosId = kform::getInput("rowsOffos", array());
		$officialsId = kform::getInput("rowsOfficials", array());
		$regisId = array_merge($allId, $playersId, $offosId, $officialsId);
		if ($err =='' && count($regisId))
		{
	  foreach($regisId as $id)
	  $form->addHide("rowsRegis[]", $id);
	  $form->addMsg('msgConfirmDel');
	  $form->addBtn('btnDelete', KAF_SUBMIT);
		}
		else
		{
	  if($err!='')
	  $form->addWng($err);
	  else
	  $form->addWng('msgNeedRegistration');
		}
		$form->addBtn('btnCancel');
		$elts = array('btnDelete', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _getFieldsMember()
	/**
	* Retrieve data about member
	*
	* @access private
	* @return void
	*/
	function _getFieldsMember()
	{
		$member = array('mber_sexe'    =>kform::getInput("mberSexe", ''),
		      'mber_firstname'  =>
		ucwords(strtolower(kform::getInput("mberFirstName"))),
		      'mber_secondname' =>
		strtoupper(kform::getInput("mberSecondName")),
		      'mber_born'       =>kform::getInput("mberBorn"),
		      'mber_ibfnumber'  =>kform::getInput("mberIbfNumber"),
		      'mber_licence'    =>kform::getInput("mberLicence"),
		      'mber_id'         =>kform::getInput("mberId", -1),
		      'mber_fedeId'     =>kform::getInput("mberFedeId", -1),
		      'mber_urlphoto'   => '',
		      'mber_cmt'        =>kform::getInput('mberCmt'));
		return $member;
	}
	// }}}

	// {{{ _getFieldsRegi()
	/**
	* Retrieve data about member
	*
	* @access private
	* @return void
	*/
	function _getFieldsRegi()
	{

		$prenom = ucwords(strtolower(kform::getInput("mberFirstName")));
		$nom = strtoupper(kform::getInput("mberSecondName"));
		$regi = array('regi_longName'  => "$nom $prenom",
		    'regi_shortName' => "$nom ".substr($prenom, 0, 1).".",
		    'regi_teamId'    =>kform::getInput("regiTeamId", -1),
		    'regi_id'        =>kform::getInput("regiId", -1),
		    'regi_accountId' =>kform::getInput("regiAccountId", -1),
		    'regi_cmt'       =>kform::getInput("regiCmt"),
		    'regi_date'      =>kform::getInput("regiDate", date(DATE_DATE)),
		    'regi_catage'    =>kform::getInput("regiCatage", WBS_CATAGE_SEN),
		    'regi_numcatage' =>kform::getInput("regiNumcatage", 1),
			'regi_dateauto'  =>kform::getInput("regiDateAuto", ''),
		    'regi_surclasse' =>kform::getInput("regiSurclasse", WBS_SURCLASSE_NONE),
		    'regi_datesurclasse' =>kform::getInput("regiDateSurclasse", ''),
		    'regi_type'      =>WBS_PLAYER,
		    'regi_status'    =>kform::getInput("regiStatus", 
		WBS_REGI_AUTRE),
		    'regi_eventId'   =>utvars::getEventId());
		return $regi;
	}
	// }}}

	// {{{ _getFieldsRank()
	/**
	* Retrieve data about ranking
	*
	* @access private
	* @return void
	*/
	function _getFieldsRank()
	{
		$ut = new utils();
		$isSquash = $ut->getParam('issquash', false);

		if($isSquash)
		{
			$rank = array('rank_averageS'   =>kform::getInput("rankAverageS"),
		  'rank_averageD'   =>kform::getInput("rankAverageS"),
		  'rank_averageM'   =>kform::getInput("rankAverageS"),
		  'rank_rankS'      =>kform::getInput("rankRankS"),
		  'rank_rankD'      =>kform::getInput("rankRankS"),
		  'rank_rankM'      =>kform::getInput("rankRankS"),
		  'rank_isFedeS'    =>kform::getInput("rankIsFedeS", 0),
		  'rank_isFedeD'    =>kform::getInput("rankIsFedeS", 0),
		  'rank_isFedeM'    =>kform::getInput("rankIsFedeS", 0),
		  'rank_dateS'      =>kform::getInput("rankDateS"),
		  'rank_dateD'      =>kform::getInput("rankDateS"),
		  'rank_dateM'      =>kform::getInput("rankDateS"),
		  'rank_classeS'    =>kform::getInput("rankClasseS"),
		  'rank_classeD'    =>kform::getInput("rankClasseS"),
		  'rank_classeM'    =>kform::getInput("rankClasseS"),
			);
		}
		else
		{
			$rank = array('rank_averageS'   =>kform::getInput("rankAverageS"),
		  'rank_averageD'   =>kform::getInput("rankAverageD"),
		  'rank_averageM'   =>kform::getInput("rankAverageM"),
		  'rank_rankS'      =>kform::getInput("rankRankS"),
		  'rank_rankD'      =>kform::getInput("rankRankD"),
		  'rank_rankM'      =>kform::getInput("rankRankM"),
		  'rank_isFedeS'    =>kform::getInput("rankIsFedeS", 0),
		  'rank_isFedeD'    =>kform::getInput("rankIsFedeD", 0),
		  'rank_isFedeM'    =>kform::getInput("rankIsFedeM", 0),
		  'rank_dateS'      =>kform::getInput("rankDateS"),
		  'rank_dateD'      =>kform::getInput("rankDateD"),
		  'rank_dateM'      =>kform::getInput("rankDateM"),
		  'rank_classeS'    =>kform::getInput("rankClasseS"),
		  'rank_classeD'    =>kform::getInput("rankClasseD"),
		  'rank_classeM'    =>kform::getInput("rankClasseM"),
			);
		}
		return $rank;
	}
	// }}}

	// {{{ _getFieldsPlay()
	/**
	* Retrieve data about member
	*
	* @access private
	* @return void
	*/
	function _getFieldsPlay()
	{
		$play = array('single'        =>kform::getInput('single', -1),
		    'oldSinglePair' =>kform::getInput('oldSinglePair', -1),
		    'double'        =>kform::getInput('double', -1),
		    'doublePair'    =>kform::getInput('doublePair', -1),
		    'oldDoublePair' =>kform::getInput('oldDoublePair', -1),
		    'mixed'         =>kform::getInput('mixed', -1),
		    'mixedPair'     =>kform::getInput('mixedPair', -1),
		    'oldMixedPair'  =>kform::getInput('oldMixedPair', -1));
		return $play;
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
		$dt = $this->_dt;

		// Create a new page
		$this->_utpage = new utPage_A('regi', true, 'itRegister');
		$content =& $this->_utpage->getContentDiv();

		// Add a menu for different action
		$kdiv =& $content->addDiv('choix', 'onglet3');
		if(utvars::isLineRegi())
		$items['itLine']    = array(KAF_UPLOAD, 'line',  WBS_ACT_LINE);
		$items['itTeams']     = array(KAF_UPLOAD, 'teams', WBS_ACT_TEAMS);
		$items['itPlayers']   = array(KAF_UPLOAD, 'regi', WBS_ACT_REGI);
		$items['itOfficials'] = array(KAF_UPLOAD, 'offo', WBS_ACT_OFFOS);
		$items['itOthers']    = array(KAF_UPLOAD, 'offo', OFFO_OTHER);
		$kdiv->addMenu("menuType", $items, $select);
		$kdiv =& $content->addDiv('register', 'cont3');
		return $kdiv;
	}
	// }}}


}
?>
