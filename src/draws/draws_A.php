<?php
/*****************************************************************************
 !   Module     : Draws
 !   File       : $Source: /cvsroot/aotb/badnet/src/draws/draws_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.59 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:24 $
 ******************************************************************************/
require_once "draws.inc";
require_once "base_A.php";
require_once "utils/utpage_A.php";
require_once "utils/utdraw.php";
require_once "utils/utround.php";
require_once "pairs/pairs.inc";
require_once "regi/regi.inc";
require_once "live/live.inc";
require_once "schedu/schedu.inc";
require_once "ajaj/ajaj.inc";

/**
 * Module de gestion des tableaux
 *
 */

class Draws_A
{

	// {{{ properties

	/**
	 * Utils objet
	 *
	 * @var     object
	 * @access  private
	 */
	var $_ut;

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
	function Draws_A()
	{
		$this->_ut = new Utils();
		$this->_dt = new DrawsBase_A();
		$this->_utd = new utdraw();
	}
	// }}}

	// {{{ start()
	/**
	 * Start the connexion processus
	 *
	 * @access public
	 * @return void
	 */
	function start($page)
	{
		$ut = $this->_ut;
		//echo "$page";
		switch ($page)
		{

	  // Display the first serial and his draws
			case DRAW_ROUND_NEW:
				$this->_displayFormNewRound();
				break;
			case DRAW_ROUND_CREATE:
				$this->_createRound();
				break;
			case DRAW_LIST:
				$this->_displayFormList();
				break;

			case WBS_ACT_DRAWS:
				$utr = new utround();
				$rounds = $utr->getRounds(null, WBS_ROUND_MAINDRAW);
				if (empty($rounds))
				{
					$draws = $this->_utd->getDraws(5);
					if ( isset($draws['errMsg']) ) $this->_displayFormList();
					else
					{
						$draw = reset($draws);
						$this->_displayFormDrawPairs($draw['draw_id']);
					}
				}
				else
				{
					$round = reset($rounds);
					$this->_displayFormKo($round['rund_id']);
				}
				break;

				// Creation of a new serial
			case DRAW_SERIAL_NEW :
				$this->_displayFormNewSerial();
				break;
			case DRAW_SERIAL_EDIT :
				//$serial = utf8_decode(kform::getData());
				$serial = kform::getData();
				$this->_displayFormEditSerial($serial);
				break;
				// Create a new draw
			case DRAW_SERIAL_CREATE:
				$this->_createSerial();
				break;
				// Create a new draw
			case DRAW_SERIAL_UPDATE:
				$this->_updateSerial();
				break;

				// Display form for fusion
			case DRAW_FUSION:
				$this->_displayFormFusion();
				break;

				// Display definition and players of a draw
			case KID_SELECT:
			case DRAW_DISPLAY:
				$drawId = kform::getData();
				$this->_displayFormDrawPairs($drawId);
				break;

			case DRAW_GROUP_NEW:
				$this->_displayFormNewGroup();
				break;

			case DRAW_GROUP_CREATE:
				$this->_createGroup();
				break;
			case DRAW_GROUP_EDIT:
				$this->_displayFormEditGroup();
				break;
			case DRAW_GROUP_UPDATE:
				$this->_createGroup(false);
				break;
				// Confimation from draw deletion
			case DRAW_GROUP_CONFIRM:
				$this->_displayFormConfirmGroup();
				break;
			case DRAW_GROUP_DELETE:
				$this->_deleteGroup();
				break;

				// Display edition form for the definition of a draw
			case KID_EDIT:
				$this->_displayFormEditDraw();
				break;
				// display edition form for the definition of a draw
			case KID_NEW:
				$this->_displayFormEditDraw(-1);
				break;
				// Update the definition of a draw
			case KID_UPDATE:
				$this->_updateDraw();
				break;
				// Confimation from draw deletion
			case KID_CONFIRM:
				$this->_displayFormConfirm();
				break;
			case KID_DELETE:
				$this->_deleteDraws();
				break;

			case DRAW_PUBLISHED:
				$this->_publiRound();
				break;

			case DRAW_CALENDAR:
				$this->_displayFormCalendar();
				break;

			case DRAW_ROUND_CONFIRM:
				$this->_displayFormConfirmRound();
				break;
			case DRAW_ROUND_DELETE:
				$this->_deleteRound();
				break;
			case DRAW_ROUND_CONFIDENT:
				$this->_displayFormPubli(WBS_DATA_CONFIDENT);
				break;
			case DRAW_ROUND_PRIVATE:
				$this->_displayFormPubli(WBS_DATA_PRIVATE);
				break;
			case DRAW_ROUND_PUBLIC:
				$this->_displayFormPubli(WBS_DATA_PUBLIC);
				break;

				// Display a page with the details of the draw
			case DRAW_DEFINITION:
				$drawId = kform::getData();
				$this->_displayFormDef($drawId);
				break;
				// Display a page with the groups of the draw
			case DRAW_GROUPS_DISPLAY:
				$this->_displayFormGroups();
				break;
				// Display a page with the qualification of the draw
			case DRAW_KO_DISPLAY:
				$roundId = kform::getData();
				$this->_displayFormKo($roundId);
				break;
				// Display a page with the qualification of the draw
			case DRAW_FINAL_DISPLAY:
				$drawId = kform::getData();
				$roundId = $this->_dt->getRoundId($drawId, WBS_ROUND_MAINDRAW);
				if ($roundId == '')	$this->_displayFormDrawPairs($drawId);
				else $this->_displayFormKo($roundId);
				break;
				// Display a page with the ko of the draw
			case DRAW_UPDATE_CPPP:
				$this->_updateCppp();
				break;

			case DRAW_PDF_PAIRSIBF:
			case DRAW_PDF_PAIRS:
			case DRAW_PDF_ALL_KO:
			case DRAW_PDF_ALL_GROUPS:
			case DRAW_PDF_GROUP_KO:
			case DRAW_PDF_STATS:
			case DRAW_PDF_GROUPS:
			case DRAW_PDF_KO:
			case DRAW_PDF_KO_IBF:
			case DRAW_XLS_DRAWS:
			case DRAW_XLS_STATS:
			case DRAW_XLS_PAIRS:
			case DRAW_XLS_PAIRS2:
				require_once "drawsPdf.php";
				$pdf = new drawsPdf();
				$pdf->start($page);
				break;


			default:
				echo "page $page demand �e depuis draws_A<br>";
				exit();
		}
	}
	// }}}


	/**
	 * Creation d'un nouveau plateau
	 *
	 * @access private
	 * @return void
	 */
	function _createRound()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$round['rund_id']        = -1;
		$round['rund_name']      = kform::getInput('roundName');
		$round['rund_stamp']     = kform::getInput('roundStamp');
		$round['rund_type']      = kform::getInput('roundType');
		$round['rund_entries']   = kform::getInput('roundEntries');
		$round['rund_drawId']    = kform::getInput('drawId');
		$round['rund_rge']       = kform::getInput('rundFinalPlace');
		$round['rund_group']     = kform::getInput('group');
		$round['rund_qual']      = 1;

		$tie['tie_nbms'] = 0;
		$tie['tie_nbws'] = 0;
		$tie['tie_nbmd'] = 0;
		$tie['tie_nbwd'] = 0;
		$tie['tie_nbxd'] = 0;
		$tie['tie_nbas'] = 0;
		$tie['tie_nbad'] = 0;
		$disci = kform::getInput('drawDisci');
		switch ($disci)
		{
			case WBS_MS :
				$tie['tie_nbms'] = 1;
				break;
			case WBS_LS :
				$tie['tie_nbws'] = 1;
				break;
			case WBS_MD :
				$tie['tie_nbmd'] = 1;
				break;
			case WBS_LD :
				$tie['tie_nbwd'] = 1;
				break;
			case WBS_MX :
				$tie['tie_nbxd'] = 1;
				break;
			case WBS_AS :
				$tie['tie_nbas'] = 1;
				break;
			case WBS_AD :
				$tie['tie_nbad'] = 1;
				break;
		}

		require_once "utils/utround.php";
		$utr = new utround();
		$roundId = $utr->updateRound($round, $tie);

		$page = new utPage('none');
		//$page->close();
		exit();
	}


	// {{{ _displayFormCalendar()
	/**
	 * Display a page with the list of the ties
	 * for publication for an individual event
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormCalendar()
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;
		$utr =  new utround();;
		$utd =  new utdraw();;

		//--- Details of the draw
		$drawId = kform::getData();
		$draw = $utd->getDrawById($drawId);
		$kdiv =& $this->_displayHeadDraw($draw, 'itCalendar', DRAW_CALENDAR);

		$form =& $kdiv->addForm('fdraws', 'draws', DRAW_CALENDAR);

		//--- List of the rounds
		$rounds = $utr->getRounds($drawId);
		$rows = array();
		foreach($rounds as $round)
		{
	  //Display the table with tie schedule
	  $ties = $dt->getRound($round['rund_id']);
	  if (count($ties) &&
	  !isset($ties['errMsg']))
	  {
	  	$first = $rows;
	  	$first[] = array(KOD_BREAK, "title",
	  	$round['draw_name'].' '.$round['rund_name']);
	  	$rows = array_merge($first, $ties);
	  }
		}
		if (count($rows))
		{
	  $itspi['itPublic'] = array(KAF_NEWWIN, 'schedu', KID_PUBLISHED, 0, 350, 180);
	  $itspi['itPrivate']   = array(KAF_NEWWIN, 'schedu', KID_PRIVATE, 0, 350, 180);
	  $itspi['itConfident'] = array(KAF_NEWWIN, 'schedu', KID_CONFIDENT, 0, 350, 180);
	  $form->addMenu('menuRight', $itspi, -1);
	  $form->addDiv('brkMenu', 'blkNewPage');
	  $krows =& $form->addRows("tiesSchedu", $rows);
	  $krows->setSort(0);

	  $img[1]='tie_pbl';
	  $krows->setLogo($img);

	  $size[6] = '0';
	  $size[8] = '0+';
	  $krows->setSize($size);
	  $actions[0] = array(KAF_NEWWIN, 'schedu',
	  SCHEDU_EDIT, 0, 400, 180);
	  $actions[7] = array(KAF_NEWWIN, 'matches',
	  KID_EDIT, 'mtch_id', 620, 350);
	  $krows->setActions($actions);
	  $krows->displaySelect(true);

		}
		$form->addDiv('page', 'blkNewPage');
		$this->_utpage->display();
		exit;
	}
	// }}}


	/**
	 * Met  jour le CPPP des joueurs
	 *
	 * @access private
	 * @return void
	 */
	function _updateCppp()
	{
		// Modifie la visibilite d'un round
		$drawId = kform::getData();
		$this->_dt->updateRankFromFede($drawId);
		$page = new utPage('none');
		$page->close();
		exit();
	}

	/**
	 * Modifie le status de publicaton, d'un round
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _publiRound()
	{
		$roundIds    = kform::getInput("rowsDraws", array());
		$status      = kform::getInput("status");
		$publiSchedu = kform::getInput("publiSchedu", false);

		// Modifie la visibilite d'un round
		require_once "utils/utpubli.php";
		$utp = new utpubli();
		foreach($roundIds as $roundId) $utp->publiRound($roundId, $status, $publiSchedu);
		$page = new utPage('none');
		$page->close();
		exit();
	}

	/**
	 * Change the publication state of the selected round
	 *
	 * @access private
	 * @param  integer $publi  new publication state of the event
	 * @return void
	 */
	function _displayFormPubli($publi, $err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('draws');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fDraw', 'draws', DRAW_PUBLISHED);
		$form->setTitle('tPubliDraws');

		// Get the id's
		$ids = kform::getInput("rowsDraws", array());
		if ($err == '' && count($ids))
		{
			foreach($ids as $id)  $form->addHide("rowsDraws[]", $id);
			$form->addHide('status', $publi);
			$form->addWng('msgConfirmPubDraw');
			$form->addCheck('publiSchedu', false);
			$form->addBtn('btnModify', KAF_SUBMIT);
			$form->addBlock('blkRounds', 'publiSchedu');
		}
		else
		if ($err == '') $form->addWng('msgNeedDraws');
		else $form->addWng('msgNeedDraws');

		//Display the page
		$form->addBtn('btnCancel');
		$elts = array('btnModify', 'btnCancel');
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit;
	}

	/**
	 * Delete draws in the database
	 *
	 * @access private
	 * @return void
	 */
	function _deleteRound()
	{
		$utr = new utRound();
		$roundId  = kform::getInput("roundId");
		$utr->delRound($roundId);
		$page = new utPage('none');
		$page->close();
		exit;
	}

	/**
	 * Delete draws in the database
	 *
	 * @access private
	 * @return void
	 */
	function _deleteDraws()
	{
		$utd = new utDraw();
		// Get the informations
		$drawId  = kform::getInput("drawId");
		// Delete the draws
		$err = $utd->delDraw($drawId);
		if (isset($err['errMsg'])) $this->_displayFormConfirm($err['errMsg']);
		$page = new utPage('none');
		$page->close();
		exit();
	}

	/**
	 * Delete groups  in the database
	 *
	 * @access private
	 * @return void
	 */
	function _deleteGroup()
	{
		// Get the informations
		$group = kform::getInput('group');
		list($drawId, $groupname) = explode(';', $group);

		// Delete the draws
		$oGroup = new objgroup();
		$oGroup->deleteGroup($drawId, $groupname);
		$page = new utPage('none');
		$page->close(true, 'draws', DRAW_DISPLAY , $drawId);
		exit();
	}

	/**
	 * Display the page for confirmation the destruction of selected round
	 *
	 * @access private
	 * @param array $member  info of the member
	 * @return void
	 */
	function _displayFormConfirmRound($err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('draws');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tDelRound', 'draws', DRAW_ROUND_DELETE);

		// Initialize the field
		$roundId = kform::getData();
		if ($err =='' && count($roundId))
		{
			$form->addHide("roundId", $roundId);
			$form->addMsg('msgConfirmDelRound');
			$form->addBtn('btnDelete', KAF_SUBMIT);
		}
		else
		{
			if($err!='') $form->addWng($err);
			else $form->addWng('msgNeedRound');
		}
		$form->addBtn('btnCancel');
		$elts = array('btnDelete', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}

	/**
	 * Display the page for confirmation the destruction of selected groups
	 *
	 * @access private
	 * @param array $member  info of the member
	 * @return void
	 */
	function _displayFormConfirmGroup($err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('draws');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tDelGroups', 'draws', DRAW_GROUP_DELETE);

		// Initialize the field
		$group  = kform::getData();
		if (empty($ids)) $ids[] = kform::getData();
		if ($err =='' && !empty($group))
		{
			$form->addHide("group", $group);
			$form->addMsg('msgConfirmDelGroup');
			$form->addBtn('btnDelete', KAF_SUBMIT);
		}
		else
		{
			if($err!='') $form->addWng($err);
			else $form->addWng('msgNeedGroups');
		}
		$form->addBtn('btnCancel');
		$elts = array('btnDelete', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}


	/**
	 * Display the page for confirmation the destruction of selected draw
	 *
	 * @access private
	 * @param array $member  info of the member
	 * @return void
	 */
	function _displayFormConfirm($err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('draws');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tDelDraws', 'draws', KID_DELETE);

		// Initialize the field
		$drawId = kform::getData();
		if ($err =='' && count($drawId))
		{
			$form->addHide("drawId", $drawId);
			$form->addMsg('msgConfirmDel');
			$form->addBtn('btnDelete', KAF_SUBMIT);
		}
		else
		{
			if($err!='') $form->addWng($err);
			else $form->addWng('msgNeedDraws');
		}
		$form->addBtn('btnCancel');
		$elts = array('btnDelete', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}

	/**
	 * Update draws details for the current tournament
	 *
	 * @access private
	 * @return void
	 */
	function _updateDraw()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Get the informations
		$infos = array('draw_id'    => kform::getInput("drawId"),
		     		'draw_name'  => kform::getInput("drawName"),
		     		'draw_serial'=> kform::getInput("drawSerial"),
		     		'draw_stamp' => kform::getInput("drawStamp"),
                    'draw_type'  => kform::getInput("drawType"),
                    'draw_catage'=> kform::getInput("drawCatage"),
                    'draw_numcatage'=> kform::getInput("drawNumcatage"),
      				'draw_rankdefId'  => kform::getInput("drawRank"),
                    'draw_disci' => kform::getInput("drawDisci"),
		);

		// Control the informations
		if ($infos['draw_name'] == "")
		{
			$infos['errMsg'] = 'draw_name';
			$this->_displayFormEditDraw($infos);
		}

		// Mise a jour de la definition du tabldeau
		$res = $dt->updateDraw($infos);
		if (is_array($res))
		{
			$infos['errMsg'] = $res['errMsg'];
			$this->_displayFormEditDraw($infos);
		}
		$page = new utPage('none');
		$page->close();
		exit();
	}

	/**
	 * Update groups of match for a draw
	 *
	 * @access private
	 * @return void
	 */
	function _createGroup($aNew=true)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Get the informations
		$infos = array('draw_id'    => kform::getInput("drawId"),
                     'draw_type'  => kform::getInput("drawType"),
                     'draw_nbp3'  => kform::getInput("drawNbp3"),
                     'draw_nbs3'  => kform::getInput("drawNbs3"),
                     'draw_nbp4'  => kform::getInput("drawNbp4"),
                     'draw_nbs4'  => kform::getInput("drawNbs4"),
                     'draw_nbp5'  => kform::getInput("drawNbp5"),
                     'draw_nbs5'  => kform::getInput("drawNbs5"),
                     'draw_nbpl'  => kform::getInput("drawNbpl"),
                     'draw_nbq'   => kform::getInput("drawNbq"),
                     'draw_nbplq' => kform::getInput("drawNbplq"),
                     'draw_third' => kform::getInput("drawThird", false),
                     'draw_name'  => kform::getInput("drawName"),
                     'draw_nbSecond'  => kform::getInput("drawNbSecond"),
					 'group'  => kform::getInput("group", ''),
					 'draw_nbPairs' => kform::getInput('drawNbPairs'),
					 'rund_rge'     => kform::getInput('rundFinalPlace')
		);

		// Control the informations
		if ($infos['group'] == "")
		{
			$infos['errMsg'] = 'groupname';
			$this->_displayFormNewGroup($infos);
		}
		if ( $infos['draw_nbp3'] < 0 || $infos['draw_nbs3'] < 0 ||
		$infos['draw_nbp4'] < 0 || $infos['draw_nbs4'] < 0 ||
		$infos['draw_nbp5'] < 0 || $infos['draw_nbs5'] < 0 ||
		$infos['draw_nbq']  < 0 )
		{
			$infos['errMsg'] = 'msgPositif';
			$this->_displayFormNewGroup($infos);
		}
		if ($infos['draw_type'] == WBS_GROUP)
		{
			$infos['draw_nbpl'] =
			$infos['draw_nbp3'] * $infos['draw_nbs3'] +
			$infos['draw_nbp4'] * $infos['draw_nbs4'] +
			$infos['draw_nbp5'] * $infos['draw_nbs5'] +
			kform::getInput("drawNbSecond",0);
			$infos['draw_nbplq'] = 0;
			$infos['draw_nbq'] = 0;
		}
		else
		{
			$infos['draw_nbp3'] = 0;
			$infos['draw_nbp4'] = 0;
			$infos['draw_nbp5'] = 0;
			if ($infos['draw_type'] == WBS_KO)
			{
				$infos['draw_nbq'] = 0;
				$infos['draw_nbplq'] = 0;
			}
			else if ($infos['draw_type'] == WBS_CONSOL)
			{
				$infos['draw_nbq'] = 0;
				$infos['draw_nbplq'] = 0;
			}
		}
		// Add the draws
		$res = $dt->updateGroups($infos, $aNew);
		if (is_array($res))
		{
			$infos['errMsg'] = $res['errMsg'];
			$this->_displayFormNewGroup($infos);
		}
		$page = new utPage('none');
		$page->close();
		exit();
	}

	// {{{ _updateSerial()
	/**
	 * Update the draws of the serial
	 *
	 * @access private
	 * @return void
	 */
	function _updateSerial()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Get the informations
		$infos = array('draw_serial'     => kform::getInput("drawSerial"),
                     'draw_oldName'    => kform::getInput("drawOldName"),
                     'draw_catage'     => kform::getInput("drawCatage"),
                     'draw_numcatage'  => kform::getInput("drawNumcatage"),
      				 'draw_rankdefId'  => kform::getInput("drawRank")
		);

		if ($infos['draw_oldName'] !=  $infos['draw_serial'])
		{
	  if ($dt->isSerialExist($infos['draw_serial']))
	  {
	  	$infos['errMsg'] = 'msgExistSerial';
	  	$this->_displayFormEditSerial($infos);
	  }
		}

		// Update the draws
		$res = $dt->updateSerial($infos);
		if (is_array($res))
		{
			$infos['errMsg'] = $res['errMsg'];
			$this->_displayFormNewSerial($infos);
		}

		$page = new utPage('none');
		$page->close();
		exit();
	}
	// }}}

	// {{{ _createSerial()
	/**
	 * Create new draws in the database for the current tournament
	 *
	 * @access private
	 * @return void
	 */
	function _createSerial()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Get the informations
		$infos = array('draw_serialName' => kform::getInput("serialName"),
                     'draw_oldName'    => kform::getInput("drawOldName"),
                     'draw_eventId'    => utvars::getEventId(),
                     'draw_catage'     => kform::getInput("drawCatage"),
                     'draw_numcatage'  => kform::getInput("drawNumcatage"),
      				 'draw_stamp'      => kform::getInput("drawStamp"),
                     'draw_rankdefId'  => kform::getInput("drawRank"),
		     'tie_nbms' => kform::getInput("drawMs", 0),
		     'tie_nbws' => kform::getInput("drawWs", 0),
		     'tie_nbmd' => kform::getInput("drawMd", 0),
		     'tie_nbwd' => kform::getInput("drawWd", 0),
		     'tie_nbas' => 0,
		     'tie_nbad' => 0,
		     'tie_nbxd' => kform::getInput("drawXd"), 0);

		// Control the informations
		if ($infos['draw_serialName'] == "")
		{
	  $infos['errMsg'] = 'msgserialName';
	  $this->_displayFormNewSerial($infos);
		}

		if ($infos['draw_oldName'] == "")
		$infos['draw_oldName'] =  $infos['draw_serialName'];

		if ($infos['draw_oldName'] !=  $infos['draw_serialName'])
		{
	  if ($dt->isSerialExist($infos['draw_SerialName']))
	  {
	  	$infos['errMsg'] = 'msgExistSerial';
	  	$this->_displayFormNewSerial($infos);
	  }
		}

		// Add the draws
		$res = $dt->addSerial($infos);
		if (is_array($res))
		{
			$infos['errMsg'] = $res['errMsg'];
			$this->_displayFormNewSerial($infos);
		}

		$page = new utPage('none');
		$page->close();
		exit();
	}
	// }}}

	// {{{ _displayFormList()
	/**
	 * Display a page with the list of the draws of the current serial
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayFormList()
	{
		$ut  =& $this->_ut;
		$utd =& $this->_utd;
		$dt  =& $this->_dt;

		// Create a new page
		$this->_utpage = new utPage_A('draws', true, 'itDraws');
		$kdiv =& $this->_utpage->getContentDiv();
		//$kdiv =& $content->addDiv('contDiv', 'cont3');
		$form =& $kdiv->addForm('fdraw');

		// Display the list of draws
		$itsm['itNewSerial']  = array(KAF_NEWWIN, 'draws', DRAW_SERIAL_NEW, 0, 500, 250);
		//$itsm['itFusionDraw']   = array(KAF_NEWWIN, 'draws', KID_EDIT,450,180);
		//$itsm['itErase']     = array(KAF_NEWWIN, 'draws', DRAW_GROUP_CONFIRM,0,250,150);
		$itsm['itPdfStats']  = array(KAF_NEWWIN, 'draws', DRAW_PDF_STATS, 0, 500, 400);
		$itsm['itPdfGroups'] = array(KAF_NEWWIN, 'draws', DRAW_PDF_ALL_GROUPS, 0, 500, 400);
		$itsm['itPdfKo']     = array(KAF_NEWWIN, 'draws', DRAW_PDF_ALL_KO, 0, 500, 400);
		$itsm['itStatsXls']  = array(KAF_NEWWIN, 'draws', DRAW_XLS_STATS, 0, 500, 400);
		$itsm['itDrawsXls']  = array(KAF_NEWWIN, 'draws', DRAW_XLS_DRAWS, 0, 500, 400);
		$itsm['itPairsXls']  = array(KAF_NEWWIN, 'draws', DRAW_XLS_PAIRS, 0, 500, 400);
		//$itsm['itPairsXls2']  = array(KAF_NEWWIN, 'draws', DRAW_XLS_PAIRS2, 0, 500, 400);

		$itsp['itPublic']    = array(KAF_NEWWIN, 'draws', DRAW_ROUND_PUBLIC, 0, 350, 180);
		$itsp['itPrivate']   = array(KAF_NEWWIN, 'draws', DRAW_ROUND_PRIVATE, 0, 350, 180);
		$itsp['itConfident'] = array(KAF_NEWWIN, 'draws', DRAW_ROUND_CONFIDENT, 0, 350, 180);
		$form->addMenu('menuLeft', $itsm, -1);
		$form->addMenu('menuRight', $itsp, -1);

		$form->addImg('lgdPairs', utimg::getIcon('w1'));
		$form->addImg('lgdPlaces', utimg::getIcon('w3'));

		$form->addDiv('break', 'blkNewPage');

		$sort = kform::getSort("rowsDraws",4);
		$rows = $utd->getSerialDraws(false, $sort);
		include_once 'utils/objgroup.php';
		if (isset($rows['errMsg'])) $form->addWng($rows['errMsg']);
		else
		{
			$utr = new utround();
			$serial = '';
			foreach($rows as $row)
			{
				$serial = $row['draw_serial'] . ' - ' . $row['draw_name'] . ' - ' . $row['draw_stamp']
				. ' - ' . $row['draw_catage'] . ' - ' . $row['nbPairs'];
				if ($row['draw_discipline'] == WBS_SINGLE) $serial .= ' joueurs';
				else $serial .= ' paires';
				$lines[] = array(KOD_BREAK, "title", $serial, '', $row['draw_id']);
					
				$drawId = $row['draw_id'];
				$oGroup = new objGroup();
				$groups = $oGroup->getlistGroups($drawId);
				foreach($groups as $groupname)
				{
					$curgroupname = $groupname;
					$group = $oGroup->getGroup($drawId,$groupname);
					$rounds = $utr->getRounds($drawId, WBS_ROUND_GROUP, null, $groupname);
					foreach($rounds as $round)
					{
						$line = $dt->completeRound($row, $round);
						$line[1] = $curgroupname;
						$curgroupname = '';
						$line['warning'] = $group['warning'];
						$lines[] = $line;
					}
					$rounds = $utr->getRounds($drawId, WBS_ROUND_QUALIF, null, $groupname);
					foreach($rounds as $round)
					{
						$line = $dt->completeRound($row, $round);
						$line[1] = $curgroupname;
						$curgroupname = '';
						$line['warning'] = $group['warning'];
						$lines[] = $line;
					}
					$rounds = $utr->getRounds($drawId, WBS_ROUND_MAINDRAW, null, $groupname);
					foreach($rounds as $round)
					{
						$line = $dt->completeRound($row, $round);
						$line[1] = $curgroupname;
						$curgroupname = '';
						$line['warning'] = $group['warning'];
						$lines[] = $line;
					}
					$rounds = $utr->getRounds($drawId, WBS_ROUND_CONSOL, 'rund_rge', $groupname);
					foreach($rounds as $round)
					{
						$line = $dt->completeRound($row, $round);
						$line[1] = $curgroupname;
						$curgroupname = '';
						$line['warning'] = $group['warning'];
						$lines[] = $line;
					}
					$rounds = $utr->getRounds($drawId, WBS_ROUND_PLATEAU, 'rund_rge', $groupname);
					foreach($rounds as $round)
					{
						$line = $dt->completeRound($row, $round);
						$line[1] = $curgroupname;
						$curgroupname = '';
						$line['warning'] = $group['warning'];
						$lines[] = $line;
					}
					$rounds = $utr->getRounds($drawId, WBS_ROUND_THIRD, null, $groupname);
					foreach($rounds as $round)
					{
						$line = $dt->completeRound($row, $round);
						$line[1] = $curgroupname;
						$curgroupname = '';
						$line['warning'] = $group['warning'];
						$lines[] = $line;
					}
				}
			}
			$krows =& $form->addRows("rowsDraws", $lines);
			$img[1]='iconPbl';
			$img[2]='warning';
			//$img[7]='endGroup';
			$img[3]='pairsKo';
			$img[5]='endMatch';
			$krows->setLogo($img);

			$sizes[7] = '0+';
			$krows->setSize($sizes);

			//$actions[0] = array(KAF_NEWWIN, 'draws', DRAW_GROUP_EDIT, 0);
			$actions[1] = array(KAF_UPLOAD, 'draws', DRAW_DISPLAY, 'draw_id');
			//$actions[7] = array(KAF_UPLOAD, 'draws', DRAW_GROUPS_DISPLAY);
			//$actions[8] = array(KAF_UPLOAD, 'draws', DRAW_FINAL_DISPLAY);
			$krows->setActions($actions);

			$sortAuth[6] = 0;
			$sortAuth[7] = 0;
			$sortAuth[8] = 0;
			$krows->setSortAuth($sortAuth);

			$acts[] = array( 'link' => array(KAF_NEWWIN, 'draws',
			//DRAW_SERIAL_EDIT, 'draw_serial', 380, 210),
			KID_EDIT, 'draw_serial', 380, 210),
	  		'icon' => utimg::getIcon(DRAW_SERIAL_EDIT),
			   'title' => 'Modifier le tableau');
			$acts[] = array( 'link' => array(KAF_NEWWIN, 'draws',
			DRAW_GROUP_NEW, 'draw_serial', 350, 460),
			   'icon' => utimg::getIcon(DRAW_SERIAL_NEW),
			   'title' => 'Ajouter un groupe');
			$acts[] = array( 'link' => array(KAF_UPLOAD, 'draws',
			DRAW_DISPLAY, 'draw_id'),
			   'icon' => utimg::getIcon(DRAW_DISPLAY),
			   'title' => 'Afficher les joueurs');
			$acts[] = array( 'link' => array(KAF_NEWWIN, 'draws',
			KID_CONFIRM, 'draw_serial', 350, 150),
	  		'icon' => utimg::getIcon(WBS_ACT_DROP),
			   'title' => 'Supprimer le tableau');
			$krows->setBreakActions($acts);
		}

		// Legend
		$kdiv = &$kdiv->addDiv('lgd', 'blkLegende');
		$kdiv->addImg('lgdConfident', utimg::getIcon(WBS_DATA_CONFIDENT));
		$kdiv->addImg('lgdPrivate', utimg::getIcon(WBS_DATA_PRIVATE));
		$kdiv->addImg('lgdPublic', utimg::getIcon(WBS_DATA_PUBLIC));
		$this->_utpage->display();
		exit;

	}

	/**
	 * Display a page with the list of the pairs of the current draw
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayFormDrawPairs($drawId)
	{
		$ut = $this->_ut;
		$utd = $this->_utd;
		//--- Details of the draw
		$draw = $utd->getDrawById($drawId);
		if ($draw['draw_discipline'] == WBS_SINGLE)	$kdiv =& $this->_displayHeadDraw($draw, 'itPlayers', DRAW_DISPLAY);
		else $kdiv =& $this->_displayHeadDraw($draw, 'itPairs', DRAW_DISPLAY);

		$div =& $kdiv->addDiv('lgndInit', 'blkLegende');
		$div->addImg('lgdMaindraw', utimg::getIcon(WBS_PAIR_MAINDRAW));
		$div->addImg('lgdQualif',   utimg::getIcon(WBS_PAIR_QUALIF));
		$div->addImg('lgdReserve',  utimg::getIcon(WBS_PAIR_RESERVE));
		$div =& $kdiv->addDiv('lgndCur', 'blkLegende');
		$div->addImg('lgdWdn',      utimg::getIcon(WBS_PAIR_WDN));
		$div->addImg('lgdPtq',      utimg::getIcon(WBS_PAIR_PTQ));
		$div->addImg('lgdPtm',      utimg::getIcon(WBS_PAIR_PTM));

		// Display the pairs of the draws
		$itsm['itAdd']    = array(KAF_NEWWIN, 'pairs', PAIR_LIST_PAIRS, $drawId, 600, 460);
		$itsm['itRemove'] = array(KAF_NEWWIN, 'pairs', KID_CONFIRM, 0, 450, 180);
		$itsm['itReserve']   = array(KAF_NEWWIN, 'pairs', PAIR_UPDATE_STATUS, WBS_PAIR_RESERVE, 450, 180);
		//if($draw['draw_type'] == WBS_QUALIF)
		{
			$itsm['itQualif'] = array(KAF_NEWWIN, 'pairs', PAIR_UPDATE_STATUS, WBS_PAIR_QUALIF, 450, 180);
		}
		$itsm['itMain']   = array(KAF_NEWWIN, 'pairs', PAIR_UPDATE_STATUS, WBS_PAIR_MAINDRAW, 450, 180);

		//$itsm['itWo']   = array(KAF_NEWWIN, 'pairs', PAIR_UPDATE_STATUS,
		//			WBS_PAIR_NONE, 450, 180);

		//$itsm['itRank'] = array(KAF_NEWWIN, 'pairs', PAIR_ASK_RANK,
		//		     $drawId, 450, 300);
		$itsm['itRank'] = array(KAF_NEWWIN, 'draws', DRAW_UPDATE_CPPP, $drawId, 300, 100);

		//$itsm['itPdfIbf'] = array(KAF_NEWWIN, 'draws', DRAW_PDF_PAIRSIBF,
		//$drawId, 450, 300);
		$itsm['itPdf'] = array(KAF_NEWWIN, 'draws', DRAW_PDF_PAIRS, $drawId, 450, 300);
		$itsm['itPairsXls']  = array(KAF_NEWWIN, 'draws', DRAW_XLS_PAIRS, $drawId, 500, 400);

		$itsp['itPublic'] = array(KAF_NEWWIN, 'pairs', KID_PUBLISHED, 0, 250, 150);
		$itsp['itPrivate']   = array(KAF_NEWWIN, 'pairs', KID_PRIVATE, 0, 250, 150);
		$itsp['itConfident'] = array(KAF_NEWWIN, 'pairs', KID_CONFIDENT, 0, 250, 150);
		$kdiv->addMenu('menuRight', $itsp, -1);
		$kdiv->addMenu('menuLeft', $itsm, -1);

		if ($draw['draw_type'] == WBS_KO)
		{
			if ($draw['draw_nbPairs'] > $draw['draw_nbpl']) $kdiv->addImg('lgdPairs', utimg::getIcon('w1'));
			if ($draw['draw_nbPairs'] < $draw['draw_nbpl']) $kdiv->addImg('lgdPlaces', utimg::getIcon('w3'));
		}
		else if ($draw['draw_type'] == WBS_GROUP)
		{
			if ($draw['draw_nbPairs'] > $draw['draw_nbGroupPlaces']) $kdiv->addImg('lgdPairs', utimg::getIcon('w1'));
			if ($draw['draw_nbPairs'] < $draw['draw_nbGroupPlaces'] ) $kdiv->addImg('lgdPlaces', utimg::getIcon('w3'));
		}
		if ($draw['draw_disci'] != WBS_MS &&
		$draw['draw_disci'] != WBS_WS &&
		$draw['draw_disci'] != WBS_AS)
		$kdiv->addImg('lgdCompose', utimg::getIcon(WBS_PAIRSTATE_COM));

		$kdiv->addDiv('page', 'blkNewPage');

		$kform =& $kdiv->addForm('formPairs');
		$sort = kform::getSort("rowsPairs", 2);
		$pairs = $this->_dt->getDrawPairs($drawId, $sort);
		if (isset($pairs['errMsg']))
		{
			$kform->addWng($pairs['errMsg']);
			unset($pairs['errMsg']);
		}
		if (count($pairs))
		{
			$krows =& $kform->addRows('rowsPairs', $pairs);
			$sizes[11] = "0+";
			$krows->setSize($sizes);

			$ellog['1']='pair_pbl';
			$ellog['8']='pair_stateLogo';
			$krows->setLogo($ellog);
			$actions[0] = array(KAF_NEWWIN, 'pairs', KID_EDIT, 0, 650, 480);
			$krows->setActions($actions);

			$acts = array();
			$acts[] = array('link' => array(KAF_NEWWIN, 'pairs', PAIR_IMPLODE, 0, 450, 180),
			  'icon' => utimg::getIcon(PAIR_IMPLODE),
			  'title' => PAIR_IMPLODE); //'R�unir');
			$acts[] = array('link' => array(KAF_NEWWIN, 'pairs', PAIR_EXPLODE, 0, 400, 180),
			  'icon' => utimg::getIcon(PAIR_EXPLODE),
			  'title' => PAIR_EXPLODE);//'S�parer');
			$krows->setBreakActions($acts);
		}

		$this->_utpage->display();
		exit;
	}

	// {{{ _displayFormGroups()
	/**
	 * Display a page with the list of the groups of the draw
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayFormGroups()
	{
		$ut = $this->_ut;
		$utd = $this->_utd;
		$dt = $this->_dt;

		//--- Details of the draw
		$oGroup = new objgroup();
		@list($drawId, $groupname) = explode(';', kform::getData());
		if (empty($groupname))
		{
			$groupNames = objgroup::getListGroups($drawId);
			$groupname = reset($groupNames);
		}
		$utr = new utround();
		$rounds = $utr->getRounds($drawId, WBS_ROUND_GROUP, null, $groupname);
		if (!count($rounds) )
		{
			$rounds = $utr->getRounds($drawId, WBS_ROUND_MAINDRAW, null, $groupname);
			if (count($rounds))
			{
				$roundId = $rounds[0]['rund_id'];
				 $this->_displayFormKo($roundId);
			}
			else $this->_displayFormDrawPairs($drawId);
			return false;
		}

		$draw = $utd->getDrawById($drawId);
		$draw['groupname'] = $groupname;

		// Liste deroulante des groupes
		$kdiv =& $this->_displayHeadDraw($draw, 'itGroup', DRAW_GROUPS_DISPLAY);
		$form =& $kdiv->addForm('formGroup');
		$group = $oGroup->getGroup($drawId, $groupname);
		// Menu
		if($draw['draw_discipline'] <= WBS_SINGLE) $form->addBtn('itSelectPlayers', KAF_NEWWIN, 'pairs',  PAIR_SELECT_GROUPPAIRS, $drawId.';'.$groupname, 600, 460);
		else $form->addBtn('itSelectPairs', KAF_NEWWIN, 'pairs',  PAIR_SELECT_GROUPPAIRS, $drawId.';'.$groupname, 600, 460);
		$form->addBtn('itPdfGroups', KAF_NEWWIN, 'draws',  DRAW_PDF_GROUPS, $drawId.';'.$groupname, 600, 460);
		if ($group['nbQual'])
		$form->addBtn('itGroups2Draw', KAF_NEWWIN, 'pairs',  PAIR_GROUP2KO, $drawId.';'.$groupname.';-1', 600, 460);

		if ($draw['draw_nbPairs'] > $group['nbPlace']) $kdiv->addImg('lgdPairs', utimg::getIcon('w1'));
		if ($draw['draw_nbPairs'] < $group['nbPlace']) $kdiv->addImg('lgdPlaces', utimg::getIcon('w3'));
		$kdiv->addDiv('page', 'blkNewPage');

		// Affichage des poules
		$rounds = $utr->getRounds($drawId, WBS_ROUND_GROUP, null, $groupname);
		if (isset($rows['errMsg']))	$kdiv->addWng($rows['errMsg']);
		else
		{
				
			// Displaying the groups
			$roundId = -1;
			$rows = array();
			$nbCols = 0;
			$break[] = array(KOD_BREAK, "title", '&nbsp;', 'classGroupBreak', '');

			foreach ( $rounds as $round)
			{
				$group = $dt->getGroup($round['rund_id']);
				if (isset($group['errMsg'])) continue;

				if ($nbCols != $round['rund_size'])
				{
					if ($nbCols)
					{
						$kdiv2 = & $kdiv->addDiv("divDraw{$nbCols}", 'group');
						$krow =& $kdiv2->addRows("rowsTies{$nbCols}", $rows);
						$krow->setSort(0);
						$krow->setNumber(false);
						$krow->displayTitle(false);
						$size[1] = "100";
						$krow->setSize($size);

						$class[1] = 'classPair';
						//$krow->setColClass($class);
					}
					$nbCols = $round['rund_size'];
					$rows = array();
				}
				$rows = array_merge($rows, $break, $group);
			}
			if ($nbCols)
			{
				$kdiv2 = & $kdiv->addDiv("divDraw{$nbCols}", 'group');
				$krow =& $kdiv2->addRows("rowsTies{$nbCols}", $rows);
				$krow->setSort(0);
				$krow->setNumber(false);
				$krow->displayTitle(false);
				$class[1] = 'classPair';
				$krow->setColClass($class);
				$nbCols = $round['rund_size'];
			}

		}

		$this->_utpage->display();
		exit;
	}

	/**
	 * Display a page with the list of the KO draw
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayFormKo($roundId)
	{
		$ut =& $this->_ut;
		$utd =& $this->_utd;
		$dt =& $this->_dt;
		$utr = new utround();

		//--- Details of the draw
		if (!empty($roundId)) $round = $utr->getRoundDef($roundId);
		if(!empty($round))
		{
			$groupName = $round['rund_group'];
			$drawId = $round['rund_drawId'];
		}
		// en cas de supression
		else
		{
			$groupName = kform::getInput("group");
			$drawId = kform::getInput("drawId");
			$rounds = $utr->getRounds($drawId, WBS_ROUND_MAINDRAW, null, $groupName);
			$round = reset($rounds);
			$roundId = $round['rund_id'];
		}
		if (empty($roundId)) return;
		$draw = $utd->getDrawById($drawId);
		$draw['groupname'] = $groupName;
		$kdiv =& $this->_displayHeadDraw($draw, 'itMainDraw', DRAW_FINAL_DISPLAY);
		$this->_utpage->_page->addAction('onload', array('initForm', 'formRoundDef'));
		$this->_utpage->_page->addJavaFile('draws/draws.js');

		// Afficher l'indicateur de publication
		$size['maxWidth'] = 30;
		$size['maxHeight'] = 30;
		$logo = utimg::getPubliIcon($round['rund_del'],
		$round['rund_pbl']);
		$kdiv->addImg('rundState', $logo, $size);

		// Bouton d'ajout
		$form =& $kdiv->addForm('formPlus');
		$form->addBtn('btnNewPlateau', KAF_NEWWIN, 'draws',  DRAW_ROUND_NEW, $round['rund_group'] . ';' . $drawId . ';' . $draw['draw_disci']);

		// Bouton de supression
		if ($round['rund_type'] == WBS_ROUND_CONSOL || $round['rund_type'] == WBS_ROUND_PLATEAU)
		$form->addBtn('itDeletePlateau', KAF_NEWWIN, 'draws', DRAW_ROUND_CONFIRM, $roundId, 300, 100);

		// Bouton d'expansion
		$form->addBtn('btnPlus', 'mask', 'formRoundDef');
		//if($draw['draw_discipline'] <= WBS_SINGLE) $itsm['itSelectPlayers'] =  array(KAF_NEWWIN, 'pairs',  PAIR_SELECT_KOPAIRS, $roundId, 600, 460);
		//else $itsm['itSelectPairs'] = array(KAF_NEWWIN, 'pairs',  PAIR_SELECT_KOPAIRS, $roundId, 600, 460);;

		if($draw['draw_discipline'] <= WBS_SINGLE)
		$form->addBtn('itSelectPlayers', KAF_NEWWIN,  'pairs',  PAIR_SELECT_KOPAIRS, $roundId, 600, 460);
		else
		$form->addBtn('itSelectPairs', KAF_NEWWIN, 'pairs',  PAIR_SELECT_KOPAIRS, $roundId, 600, 460);
		// Display  the menu
		$rounds = $utr->getRounds($drawId, WBS_ROUND_GROUP, null, $round['rund_group']);
		if (!empty($rounds))
		{
			$form->addBtn('itPdfGroups', KAF_NEWWIN, 'draws',  DRAW_PDF_GROUPS, $drawId.';'.$round['rund_group'], 600, 460);
			$form->addBtn('btnGroups2Draw', KAF_NEWWIN, 'pairs',  PAIR_GROUP2KO, $drawId.';'.$round['rund_group'].';'.$roundId, 600, 460);
		}

		$form->addBtn('btnPdfDoc', KAF_NEWWIN, 'draws',  DRAW_PDF_GROUP_KO, $drawId.';'.$round['rund_group'], 600, 460);

		// Donnees modifiables du tableau
		$form =& $kdiv->addForm('formRoundDef', 'draws', KID_UPDATE);
		$form->addHide('drawId', $drawId);
		$form->addHide('roundId', $roundId);
		$form->addHide('drawDisci', $draw['draw_disci']);
		$form->addHide('oldEntries', $round['rund_entries']);
		$form->addInfo('roundTypeLabel', $ut->getLabel($round['rund_type']));
		if ($round['rund_type'] == WBS_ROUND_CONSOL)
		{
			$form->addHide('roundType',    $round['rund_type']);
			$form->addEdit('roundName',    $round['rund_name'], 40);
			$form->addEdit('roundStamp',   $round['rund_stamp'], 40);
			$form->addEdit('roundEntries', $round['rund_entries'], 40);
			$form->addEdit('group', $round['rund_group'], 40);
			$kedit =& $form->addEdit('rundFinalPlace',  $round['rund_rge'], 3);
			$kedit->setMaxLength(3);
		}
		else
		{
			$form->addInfo('roundName',    $round['rund_name']);
			$form->addInfo('roundStamp',   $round['rund_stamp']);
			$form->addInfo('roundEntries', $round['rund_entries']);
			$form->addInfo('group', $round['rund_group']);
			$form->addInfo('rundFinalPlace',  $round['rund_rge']);
		}
		$elts =array('roundTypeLabel', 'roundType', 'group', 'roundName', 'roundStamp', 'roundEntries', 'rundFinalPlace');
		$form->addBlock('blkInfo', $elts);

		if ($round['rund_type'] == WBS_ROUND_CONSOL) $form->addBtn('btnModify', 'activate', 'formRoundDef', 'roundName');
		$form->addBtn('btnMoins', 'mask', 'formRoundDef');
		$elts =array('btnModify','btnMoins');
		$form->addBlock('divModif', $elts);

		$form->addBtn('btnRegister', KAF_AJAJ, 'endSubmit', AJAJ_DRAW_SAVEROUND);
		//$form->addBtn('btnRegister', KAF_NEWWIN, 'ajajx', AJAJ_DRAW_SAVEROUND);
		$form->addBtn('btnCancel', 'cancel', 'formRoundDef');
		$elts =array('btnRegister','btnCancel');
		$form->addBlock('divValid', $elts);

		$kdiv->addDiv('page2', 'blkNewPage');

		//$itsm['btnPdfIbf'] = array(KAF_NEWWIN, 'draws',  DRAW_PDF_KO_IBF, $roundId, 600, 460);

		if ($draw['draw_type'] == WBS_KO)
		{
			if ($draw['draw_nbPairs'] > $draw['draw_nbpl']) $kdiv->addImg('lgdPairs', utimg::getIcon('w1'));
			if ($draw['draw_nbPairs'] < $draw['draw_nbpl'])	$kdiv->addImg('lgdPlaces', utimg::getIcon('w3'));
		}
		$kdiv->addDiv('page', 'blkNewPage');

		// Liste des plateaux
		$items =array();
		$rounds = $utr->getRounds($drawId, WBS_ROUND_MAINDRAW, null, $groupName);
		foreach($rounds as $cur)
		{
			if ($cur['rund_group'] == 'Principal') $name = $cur['rund_name'];
			else $name = $cur['rund_group'] .' - '. $cur['rund_name'];
			$items[$name] = array(KAF_UPLOAD, 'draws', DRAW_KO_DISPLAY, $cur['rund_id']);
			if($cur['rund_id'] == $roundId) $select = $name;
		}
		$rounds = $utr->getRounds($drawId, WBS_ROUND_THIRD, null, $groupName);
		foreach($rounds as $cur)
		{
			if ($cur['rund_group'] == 'Principal') $name = $cur['rund_name'];
			else $name = $cur['rund_group'] .' - '. $cur['rund_name'];
			$items[$name] = array(KAF_UPLOAD, 'draws', DRAW_KO_DISPLAY, $cur['rund_id']);
			if($cur['rund_id'] == $roundId) $select = $name;
		}
		$rounds = $utr->getRounds($drawId, WBS_ROUND_QUALIF, null, $groupName);
		foreach($rounds as $cur)
		{
			if ($cur['rund_group'] == 'Principal') $name = $cur['rund_name'];
			else $name = $cur['rund_group'] .' - '. $cur['rund_name'];
			$items[$name] = array(KAF_UPLOAD, 'draws', DRAW_KO_DISPLAY, $cur['rund_id']);
			if($cur['rund_id'] == $roundId) $select = $name;
		}
		$rounds = $utr->getRounds($drawId, WBS_ROUND_CONSOL, 'rund_rge', $groupName);
		foreach($rounds as $cur)
		{
			if ($cur['rund_group'] == 'Principal') $name = $cur['rund_name'];
			else $name = $cur['rund_group'] .' - '. $cur['rund_name'];
			//$list[$cur['rund_id']] = $cur['rund_id']. '-- ' . $list[$cur['rund_id']];
			$items[$name] = array(KAF_UPLOAD, 'draws', DRAW_KO_DISPLAY, $cur['rund_id']);
			if($cur['rund_id'] == $roundId) $select = $name;
		}
		$rounds = $utr->getRounds($drawId, WBS_ROUND_PLATEAU, 'rund_rge', $groupName);
		foreach($rounds as $cur)
		{
			//if($cur['rund_size']==1) continue;
			if ($cur['rund_group'] == 'Principal') $name = $cur['rund_name'];
			else $name = $cur['rund_group'] .' - '. $cur['rund_name'];
			//$list[$cur['rund_id']] = $cur['rund_id']. '-- ' . $list[$cur['rund_id']];
			$items[$name] = array(KAF_UPLOAD, 'draws', DRAW_KO_DISPLAY, $cur['rund_id']);
			if($cur['rund_id'] == $roundId) $select = $name;
		}
		if (count($items) > 1)	$kdiv->addMenu("menuplateau", $items, $select);

		// Affichage du plateau
		$pairs = $dt->getRoundPairs($roundId);
		require_once "utils/utko.php";
		$utko = new utKo($pairs, $round['rund_entries']);
		$vals = $utko->getExpandedValues();
		$kdraw = & $kdiv->addDraw('draw', $round['rund_qual']);
		$kdraw->setValues(1, $vals);
		$size = $round['rund_size'];
		$winners = $dt->getWinners($roundId);
		$allTies = $utr->getTies($roundId);
		if (isset($ties['errMsg'])) return $ties;

		// Construc a table with the ties
		$ties = array();
		foreach($allTies as $data)
		{
			$ties[$data['tie_posRound']] = $dt->getMatch($data);
		}
		$numCol = 2;
		while( ($size/=2) >= 1)
		{
	  $vals = array();
	  $firstTie = $size-1;
	  for($i=0; $i < $size; $i++)
	  {
	  	$val = '';
	  	if (isset($winners[$firstTie + $i]))
	  	{
	  		$tie = $ties[$firstTie + $i];
	  		$winner = $winners[$firstTie + $i];
	  		if (!$tie['tie_isBye'])
	  		{
	  			$val['seed'] = array('name'=>$tie['mtch_num'], 'action' => array(KAF_NEWWIN, 'matches',
	  			KID_EDIT, $tie['mtch_id'], 600, 350));
	  		}
	  		$val['value'] = $winner['value'];
	  		$val['score'] = '';
	  		//$val['score'] = $tie['tie_looserdrawid'] .' - ' . $tie['mtch_id'] .';';
	  		if ($winner['score'] != '0-0') $val['score'] .= $winner['score'];
	  		else  $val['score'] .= $tie['tie_schedule'];
	  	}
	  	else if (isset($ties[$firstTie + $i]))
	  	{
	  		$tie = $ties[$firstTie + $i];
	  		$val = array('seed' => array('name'=>$tie['mtch_num'],
					       'action' => array(KAF_NEWWIN, 'matches', 
	  		KID_EDIT, $tie['mtch_id'], 600, 350)),
			       'value' => $tie['tie_schedule']);
	  	}
	  	$vals[] = $val;
	  }
	  $kdraw->setValues($numCol++, $vals);
		}

		for ($i=0; $i<$numCol; $i++) $title[$numCol-$i-2] = $ut->getLabel(WBS_WINNER + $i);

		$kdraw->setTitles($title);
		$kdiv->addDiv('break', 'blkNewPage');

		$this->_utpage->display();
		exit;
	}
	// }}}



	// {{{ _displayFormFusion()
	/**
	 * Display the form to regroup two serial
	 *
	 * @access private
	 * @param array $eventId  id of the event
	 * @return void
	 */
	function _displayFormFusion()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$form = $ut->newForm_A("draws", DRAW_FUSION);

		// Initialize the title
		$form->setTitle("");
		$form->setSubTitle("");

		$serialSel = kform::getInput("serialList");
		$infos = $dt->getSerial($serialSel);
		$form->addMsg('tFusionSerial');

		$infos['errMsg'] = "wbs_notYet";

		// Display warning if exist
		if (isset($infos['errMsg']))
		$form->addWng($infos['errMsg']);

		// Display information of draws
		$form->addInfo("drawName",   $infos['draw_name']);

		$form->addBloc("blkName", "drawName");

		$form->addCheck('serial_ms', $infos['serial_ms']);
		$form->addCheck('serial_ls', $infos['serial_ls']);
		$form->addCheck('serial_md', $infos['serial_md']);
		$form->addCheck('serial_ld', $infos['serial_ld']);
		$form->addCheck('serial_mx', $infos['serial_mx']);

		//      $form->addBtn("btnRegister");
		//$actions = array( 1 => array(KAF_CHECKFIELD, 'draws', SERIAL_UPDATE_A));
		//$form->setActions("btnRegister", $actions);

		$form->addBtn("btnCancel");
		$elts  = array("btnRegister", "btnCancel");
		$form->addBloc("blkBtn", $elts);

		//Display the form
		$form->display(false);

		exit;
	}
	// }}}

	// {{{ _displayFormNewSerial()
	/**
	 * Display the form to create serial
	 *
	 * @access private
	 * @param array $eventId  id of the event
	 * @return void
	 */
	function _displayFormNewSerial($serial="")
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$utpage = new utPage('draws');
		$utpage->_page->addJavaFile('draws/draws.js');
		$content =& $utpage->getPage();

		$form =& $content->addForm('tNewSerial', 'draws', DRAW_SERIAL_CREATE);

		if (is_array($serial)) $infos = $serial;
		else
		$infos = array('draw_oldName'  => "",
			 'draw_name'     => "",
			 'draw_stamp'    => "",
			 'draw_catage'   => WBS_CATAGE_SEN,
			 'draw_numcatage'   => 0,
	  		 'draw_rankdefId' => -1,
			 'tie_nbms'       => 1,
			 'tie_nbws'       => 1,
			 'tie_nbas'       => 0,
			 'tie_nbmd'       => 1,
			 'tie_nbwd'       => 1,
			 'tie_nbad'       => 0,
			 'tie_nbxd'       => 1
		);

		// Display warning if exist
		if (isset($infos['errMsg']))
		$form->addWng($infos['errMsg']);

		// Display information of draws
		$form->addHide("drawOldName",   $infos['draw_oldName']);
		$kedit  =& $form->addEdit("serialName",  $infos['draw_name']);
		$kedit->setLength(25);
		$kedit->setMaxLength(25);
		$kedit  =& $form->addEdit("drawStamp", $infos['draw_stamp']);
		$kedit->setLength(25);
		$kedit->setMaxLength(25);

		for($i=WBS_CATAGE_POU;  $i<=WBS_CATAGE_VET; $i++) $catages[$i] = $ut->getLabel($i);
		if (isset($catages[$infos['draw_catage']]))
		$kcombo =& $form->addCombo('drawCatage', $catages, $catages[$infos['draw_catage']]);
		else $kcombo =& $form->addCombo('drawCatage', $catages);
		$actions = array( 1 => array('changeCatage'));
		$kcombo->setActions($actions);

		// Numero des categories d'age
		/*
		switch($infos['draw_catage'])
		{
		case WBS_CATAGE_POU: $max = 0; break;
		case WBS_CATAGE_SEN: $max = 0; break;
		case WBS_CATAGE_VET: $max = 5; break;
		default: $max = 2; break;
		}
		for($i=0; $i<=$max; $i++) $num[$i] = $i;
		$kcombo =& $form->addCombo('drawNumcatage', $num, $infos['draw_numcatage']);
		*/
		$form->addHide('drawNumcatage', 1);

		$ranks = $dt->getRanks();
		if (isset($ranks[$infos['draw_rankdefId']]))
		$kcombo =& $form->addCombo('drawRank', $ranks, $ranks[$infos['draw_rankdefId']]);
		else
		$kcombo =& $form->addCombo('drawRank', $ranks, end($ranks));

		$isSquash = $ut->getParam('issquash', false);

		// Select draws to be create
		$form->addCheck('drawMs', $infos['tie_nbms']);
		$form->addCheck('drawWs', $infos['tie_nbws']);
		$elts  = array('drawMs', 'drawWs');
		$form->addBlock('blkSingle', $elts, 'classSelect');

		if (!$isSquash)
		{
			$form->addCheck('drawMd', $infos['tie_nbmd']);
			$form->addCheck('drawWd', $infos['tie_nbwd']);
			$elts  = array('drawMd','drawWd');
			$form->addBlock('blkDouble', $elts, 'classSelect');

			$form->addCheck('drawXd', $infos['tie_nbxd']);
			$elts  = array('drawXd');
			$form->addBlock('blkMixed', $elts, 'classSelect');
		}

		$elts  = array('serialName', 'drawStamp', 'drawCatage', 'drawRank',
		 'blkSingle', 'blkDouble', 'blkMixed');
		$form->addBlock('blkName', $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts  = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts, 'blkBtn');

		//Display the form
		$utpage->display(false);

		exit;
	}
	// }}}

	// {{{ _displayFormEditSerial()
	/**
	 * Display the form to update a serial
	 *
	 * @access private
	 * @param array $eventId  id of the event
	 * @return void
	 */
	function _displayFormEditSerial($serial="")
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$utd = new utDraw();

		$utpage = new utPage('draws');
		$utpage->_page->addJavaFile('draws/draws.js');
		$content =& $utpage->getPage();

		$form =& $content->addForm('tEditSerial', 'draws', DRAW_SERIAL_UPDATE);

		if (is_array($serial)) $infos = $serial;
		else $infos = $utd->getSerial($serial);

		// Display warning if exist
		if (isset($infos['errMsg'])) $form->addWng($infos['errMsg']);

		// Display information of draws
		$form->addHide('drawOldName',   kform::getInput('drawOldName', $infos['draw_serial']));
		$kedit  =& $form->addEdit('drawSerial',   $infos['draw_serial']);
		$kedit->setLength(25);
		$kedit->setMaxLength(25);

		for($i=WBS_CATAGE_POU;  $i<=WBS_CATAGE_VET; $i++) $catages[$i] = $ut->getLabel($i);
		if (isset($catages[$infos['draw_catage']]))
		$kcombo =& $form->addCombo('drawCatage',
		$catages, $catages[$infos['draw_catage']]);
		else
		$kcombo =& $form->addCombo('drawCatage', $catages);
		$actions = array( 1 => array('changeCatage'));
		$kcombo->setActions($actions);

		// Numero des categories d'age
		switch($infos['draw_catage'])
		{
			case WBS_CATAGE_POU: $max = 0; break;
			case WBS_CATAGE_SEN: $max = 0; break;
			case WBS_CATAGE_VET: $max = 5; break;
			default: $max = 2; break;
		}
		for($i=0; $i<=$max; $i++) $num[$i] = $i;
		$kcombo =& $form->addCombo('drawNumcatage', $num, $infos['draw_numcatage']);

		$ranks = $dt->getRanks();
		if (isset($ranks[$infos['draw_rankdefId']]))
		$kcombo =& $form->addCombo('drawRank',
		$ranks, $ranks[$infos['draw_rankdefId']]);
		else
		$kcombo =& $form->addCombo('drawRank', $ranks);

		$elts  = array('drawSerial', 'drawCatage', 'drawNumcatage', 'drawRank');
		$form->addBlock('blkName', $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts  = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts, 'blkBtn');

		//Display the form
		$utpage->display(false);
		exit;
	}
	// }}}

	/**
	 * Display the form to modify a draw
	 *
	 * @access private
	 * @param array $draw  id or information of the draw
	 * @return void
	 */
	function _displayFormEditDraw($draw="")
	{
		$utd =& $this->_utd;
		$ut  =& $this->_ut;

		$utpage = new utPage('draws');
		$utpage->_page->addAction('onload', array('resize', 500, 360));
		$content =& $utpage->getPage();
		$form =& $content->addForm('fdraws', 'draws', KID_UPDATE);
		$form->setTitle('tEditDraw');
		$ranks = $this->_dt->getRanks();

		if (is_array($draw)) $infos = $draw;
		else
		{
			if ( $draw != -1) $drawId  = kform::getData();
			else $drawId  = $draw;
			$infos = $utd->getDrawById($drawId);
			if ($infos['draw_id'] == -1)
			{
				$infos['draw_serial'] = kform::getData();
				$infos['draw_rankdefId'] = reset(array_keys($ranks));
				$form->setTitle('tNewDraw');
			}
		}
		// Display warning if exist
		if (isset($infos['errMsg'])) $form->addWng($infos['errMsg']);

		// Initialize the field
		$form->addHide("drawId", $infos['draw_id']);

		$serials = $utd->getSerialsList();
		$form->addCombo('drawSerial', $serials, $serials[$infos['draw_serial']]);
		if ($infos['draw_id'] == -1)
		{
			$discis[WBS_MS] =  $ut->getLabel(WBS_MS);
			$discis[WBS_WS] =  $ut->getLabel(WBS_WS);
			$discis[WBS_MD] =  $ut->getLabel(WBS_MD);
			$discis[WBS_WD] =  $ut->getLabel(WBS_WD);
			$discis[WBS_XD] =  $ut->getLabel(WBS_XD);
			$form->addCombo('drawDisci',  $discis, $infos['draw_disci']);
		}
		else
		{
			$form->addHide("drawDisci", $infos['draw_disci']);
			$form->addInfo('drawDisciLabel', $ut->getLabel($infos['draw_disci']));
			$form->addInfo('drawPairs', $infos['draw_nbPairs']);
		}

		for($i=WBS_CATAGE_POU;  $i<=WBS_CATAGE_VET; $i++)
		$catages[$i] = $ut->getLabel($i);
		$kcombo =& $form->addCombo('drawCatage',
		$catages, $catages[$infos['draw_catage']]);
		$actions = array( 1 => array('changeCatage'));
		$kcombo->setActions($actions);
		// Numero des categories d'age
		switch($infos['draw_catage'])
		{
			case WBS_CATAGE_POU: $max = 0; break;
			case WBS_CATAGE_SEN: $max = 0; break;
			case WBS_CATAGE_VET: $max = 5; break;
			default: $max = 2; break;
		}
		for($i=0; $i<=$max; $i++) $num[$i] = $i;
		$kcombo =& $form->addCombo('drawNumcatage', $num, $infos['draw_numcatage']);
			
		if (isset($ranks[$infos['draw_rankdefId']])) $sel = $ranks[$infos['draw_rankdefId']];
		else $sel = reset($ranks);
		$kcombo =& $form->addCombo('drawRank', $ranks, $sel);

		$kedit =& $form->addEdit('drawName',  $infos['draw_name'], 29);
		$kedit->setMaxLength(50);
		$kedit =& $form->addEdit('drawStamp',  $infos['draw_stamp'], 29);
		$kedit->setMaxLength(10);

		$elts=array('drawDisciLabel', 'drawPairs', 'drawSerial', 'drawCatage', 'drawNumcatage',
		  'drawRank', 'drawDisci', 'drawName', 'drawStamp');
		$form->addBlock("blkOne",  $elts);

		$form->addDiv('break', 'blkNewPage');
		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts=array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the form
		$utpage->display();
		exit;
	}

	/**
	 * Display the form to create new group of rounds for a draw
	 *
	 * @access private
	 * @param array $draw  id or information of the draw
	 * @return void
	 */
	function _displayFormNewGroup($infos='', $aModif=false)
	{
		$utd =& $this->_utd;
		$ut  =& $this->_ut;

		$utpage = new utPage('draws');
		$utpage->_page->addAction('onload', array('changeDrawType'));
		$utpage->_page->addJavaFile('draws/draws.js');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fdraws', 'draws', DRAW_GROUP_CREATE);
		$form->setTitle('tNewGroup');

		// Display warning if exist
		if (isset($infos['errMsg'])) $form->addWng($infos['errMsg']);

		if (empty($infos))
		{
			$drawId  = kform::getData();
			$infos = $utd->getDrawById($drawId);
			$infos['group'] = 'Principal';
			$infos['rund_rge'] = 1;
		}
		else $drawId = $infos['draw_id'];
			
		$form->addInfo('theDraw', $infos['draw_name']);
		$form->addInfo('drawPairs', $infos['draw_nbPairs']);

		// Initialize the field
		$form->addHide("drawId", $drawId);
		$form->addHide("drawName", $infos['draw_name']);
		$form->addHide('drawNbPairs',  $infos['draw_nbPairs']);

		$kedit =& $form->addEdit('group',  $infos['group'], 29);
		$kedit->setMaxLength(20);
		$actions = array( 1 => array('changeDrawType'));
		$types = array(WBS_GROUP  => $ut->getLabel(WBS_GROUP),
		WBS_QUALIF => $ut->getLabel(WBS_QUALIF),
		WBS_KO     => $ut->getLabel(WBS_KO),
		WBS_CONSOL => $ut->getLabel(WBS_CONSOL),
		);
		$kcombo =& $form->addCombo("drawType", $types, WBS_CONSOL);
		$kcombo->setActions($actions);
		$kedit =& $form->addEdit('rundFinalPlace',  $infos['rund_rge'], 3);
		$kedit->setMaxLength(3);
		$form->addCheck('drawThird', false);
		$form->addBlock("blkThird",  'drawThird');

		$elts=array('theDraw', 'drawPairs', 'group', 'drawType', 'rundFinalPlace', 'blkThird');
		$form->addBlock("blkOne",  $elts);

		$kedit =& $form->addEdit('drawNbp3',  0, 2);
		$kedit->setMaxLength(2);
		$kedit =& $form->addEdit('drawNbs3',  1, 2);
		$kedit->setMaxLength(1);
		$kedit =& $form->addEdit('drawNbp4',  0, 2);
		$kedit->setMaxLength(2);
		$kedit =& $form->addEdit('drawNbs4',  2, 2);
		$kedit->setMaxLength(1);
		$kedit =& $form->addEdit('drawNbp5',  0, 2);
		$kedit->setMaxLength(2);
		$kedit =& $form->addEdit('drawNbs5',  0, 2);
		$kedit->setMaxLength(1);
		$kedit =& $form->addEdit('drawNbSecond', 0, 2);
		$kedit->setMaxLength(1);
		$elts=array('drawNbp3', 'drawNbs3', 'drawNbp4', 'drawNbs4',
  					'drawNbp5', 'drawNbs5', 'drawNbSecond');
		$form->addBlock('blkGroup',  $elts);
		$infos['draw_nbpl'] = intval($infos['draw_nbPairs']);
		$kedit =& $form->addEdit('drawNbpl',  $infos['draw_nbpl'], 3);
		$kedit->setMaxLength(3);
		$kedit =& $form->addEdit('drawNbplq',  0, 3);
		$kedit->setMaxLength(3);
		$kedit =& $form->addEdit('drawNbq',   0, 3);
		$kedit->setMaxLength(2);
		//$kedit =& $form->addEdit('drawNbcons',  $infos['draw_nbq'], 3);
		//$kedit->setMaxLength(3);
		$elts=array('drawNbpl', 'drawNbplq', 'drawNbq');
		$form->addBlock('blkKo',  $elts);

		$elts=array('blkOne', 'blkGroup', 'blkKo');
		$form->addBlock('blkAll',  $elts);

		$form->addDiv('break', 'blkNewPage');
		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts=array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the form
		$utpage->display();
		exit;
	}


	/**
	 * Form pour creation d'un round
	 *
	 */
	function _displayFormNewRound($aInfos=null)
	{
		$utd =& $this->_utd;
		$ut  =& $this->_ut;

		$utpage = new utPage('draws');
		$utpage->_page->addAction('onload', array('changeDrawType'));
		$utpage->_page->addJavaFile('draws/draws.js');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fdraws', 'draws', DRAW_ROUND_CREATE);
		$form->setTitle('tNewRound');

		// Display warning if exist
		if (isset($aInfos['errMsg'])) $form->addWng($aInfos['errMsg']);

		if (empty($aInfos))
		{
			list($group, $drawId, $drawDisci)  = explode(';', kform::getData());
			$infos['rund_drawId'] = $drawId;
			$infos['rund_group']  = $group;
			$infos['rund_name']   = '';
			$infos['rund_stamp']   = '';
			$infos['rund_entries']   = 0;
			$infos['rund_rge']   = 0;
		}
		else $infos = $aInfos;
			
		// Initialize the field
		$form->addHide("drawId", $infos['rund_drawId']);
		$form->addHide("group", $infos['rund_group']);
		$form->addHide("roundType", WBS_ROUND_CONSOL);
		$form->addHide('drawDisci', $drawDisci);
		$form->addEdit('roundName',    $infos['rund_name'], 20);
		$form->addEdit('roundStamp',   $infos['rund_stamp'], 20);
		$form->addEdit('roundEntries', $infos['rund_entries'], 3);
		$kedit =& $form->addEdit('rundFinalPlace',  $infos['rund_rge'], 3);
		$kedit->setMaxLength(3);
		$elts=array('roundName', 'roundStamp', 'roundEntries', 'rundFinalPlace');
		$form->addBlock('blkGroup',  $elts);

		$form->addDiv('break', 'blkNewPage');
		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts=array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the form
		$utpage->display();
		exit;
	}


	/**
	 * Display the form to modify a group of rounds for a draw
	 *
	 * @access private
	 * @param array $draw  id or information of the draw
	 * @return void
	 */
	function _displayFormEditGroup($infos='')
	{
		$utd =& $this->_utd;
		$ut  =& $this->_ut;

		$utpage = new utPage('draws');
		$utpage->_page->addAction('onload', array('changeDrawType'));
		$utpage->_page->addJavaFile('draws/draws.js');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fdraws', 'draws', DRAW_GROUP_UPDATE);
		$form->setTitle('tEditGroup');

		// Display warning if exist
		if (isset($infos['errMsg'])) $form->addWng($infos['errMsg']);

		$round = kform::getData();
		list($drawId, $groupName)  = explode(';', $round);
		$infos = $utd->getDrawById($drawId);
		$infos['group'] = $groupName;

		$oGroup = new objgroup();
		$group = $oGroup->getGroup($drawId, $groupName);

		$form->addInfo('theDraw', $infos['draw_name']);
		$form->addInfo('drawPairs', $infos['draw_nbPairs']);
		$form->addInfo('groupName', $infos['group']);

		// Initialize the field
		$form->addHide("drawId", $drawId);
		$form->addHide("drawName", $infos['draw_name']);
		$form->addHide('drawNbPairs',  $infos['draw_nbPairs']);
		$form->addHide('group',  $infos['group']);

		$actions = array( 1 => array('changeDrawType'));
		$types = array(WBS_GROUP  => $ut->getLabel(WBS_GROUP),
		WBS_QUALIF => $ut->getLabel(WBS_QUALIF),
		WBS_KO     => $ut->getLabel(WBS_KO),
		WBS_CONSOL => $ut->getLabel(WBS_CONSOL),
		);
		$kcombo =& $form->addCombo("drawType", $types, $group['type']);
		$kcombo->setActions($actions);
		$kedit =& $form->addEdit('rundFinalPlace',  $group['rund_rge'], 3);
		$kedit->setMaxLength(3);
		$form->addCheck('drawThird', $group['third']);
		$form->addBlock("blkThird",  'drawThird');

		$elts=array('theDraw', 'drawPairs', 'groupName', 'drawType', 'rundFinalPlace', 'blkThird');
		$form->addBlock("blkOne",  $elts);

		$kedit =& $form->addEdit('drawNbp3',  $group['nb3'], 2);
		$kedit->setMaxLength(2);
		$kedit =& $form->addEdit('drawNbs3',  $group['nbs3'], 2);
		$kedit->setMaxLength(1);
		$kedit =& $form->addEdit('drawNbp4',  $group['nb4'], 2);
		$kedit->setMaxLength(2);
		$kedit =& $form->addEdit('drawNbs4',  $group['nbs4'], 2);
		$kedit->setMaxLength(1);
		$kedit =& $form->addEdit('drawNbp5',  $group['nb5'], 2);
		$kedit->setMaxLength(2);
		$kedit =& $form->addEdit('drawNbs5',  $group['nbs5'], 2);
		$kedit->setMaxLength(1);
		$kedit =& $form->addEdit('drawNbSecond', $group['nbsecond'], 2);
		$kedit->setMaxLength(1);
		$elts=array('drawNbp3', 'drawNbs3', 'drawNbp4', 'drawNbs4',
  					'drawNbp5', 'drawNbs5', 'drawNbSecond');
		$form->addBlock('blkGroup',  $elts);
		$infos['draw_nbpl'] = intval($infos['draw_nbPairs']);
		$kedit =& $form->addEdit('drawNbpl',  $group['nbPlace'], 3);
		$kedit->setMaxLength(3);
		$kedit =& $form->addEdit('drawNbplq',  0, 3);
		$kedit->setMaxLength(3);
		$kedit =& $form->addEdit('drawNbq',   $group['nbQ'], 3);
		$kedit->setMaxLength(2);
		//$kedit =& $form->addEdit('drawNbcons',  $infos['draw_nbq'], 3);
		//$kedit->setMaxLength(3);
		$elts=array('drawNbpl', 'drawNbplq', 'drawNbq');
		$form->addBlock('blkKo',  $elts);

		$form->addDiv('break', 'blkNewPage');
		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts=array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the form
		$utpage->display();
		exit;
	}


	// {{{ _displayFormDef()
	/**
	 * Display a page with the definition of the current draw
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayFormDef($drawId)
	{
		$ut =& $this->_ut;
		$utd =& $this->_utd;

		//--- Details of the draw
		$draw = $utd->getDrawById($drawId);
		$kdiv1 =& $this->_displayHeadDraw($draw, 'itDefinition', DRAW_DEFINITION);

		$ranks = $this->_dt->getRanks();

		if ($draw['draw_type'] == WBS_KO)
		{
			if ($draw['draw_nbPairs'] > $draw['draw_nbpl'])
			$kdiv1->addImg('lgdPairs', utimg::getIcon('w1'));
			if ($draw['draw_nbPairs'] < $draw['draw_nbpl'])
			$kdiv1->addImg('lgdPlaces', utimg::getIcon('w3'));
		}
		else if ($draw['draw_type'] == WBS_GROUP)
		{
			if ($draw['draw_nbPairs'] > $draw['draw_nbGroupPlaces'])
			$kdiv1->addImg('lgdPairs', utimg::getIcon('w1'));
			if ($draw['draw_nbPairs'] < $draw['draw_nbGroupPlaces'])
			$kdiv1->addImg('lgdPlaces', utimg::getIcon('w3'));
		}

		$kdiv2 =& $kdiv1->addDiv('divDef', 'blkData');
		$kdiv2->addMsg('tDrawDef', '', 'kTitre');
		$kdiv2->addInfo("drawDisci",    $ut->getLabel($draw['draw_disci']));
		$kdiv2->addInfo("drawPairs",  $draw['draw_nbPairs']);
		$kdiv2->addInfo("drawSerial",   $draw['draw_serial']);
		$kdiv2->addInfo("drawCatage",   $ut->getLabel($draw['draw_catage']));
		$kdiv2->addInfo("drawNumcatage",   $draw['draw_numcatage']);
		if (isset($ranks[$draw['draw_rankdefId']]))
		$kdiv2->addInfo("drawRank",     $ranks[$draw['draw_rankdefId']]);
		else $kdiv2->addInfo("drawRank", '');
		$kdiv2->addInfo("drawName",     $draw['draw_name']);
		$kdiv2->addInfo("drawStamp",    $draw['draw_stamp']);
		$kdiv2->addInfo("drawTypeLabel",$ut->getLabel($draw['draw_type']));
		$kdiv2->addInfo("drawThirdI",    $draw['draw_third']?$ut->getLabel(WBS_YES):$ut->getLabel(WBS_NO));
		$items = array();
		$items['btnModify'] = array(KAF_NEWWIN, 'draws', KID_EDIT,
		$drawId);
		$kdiv2->addMenu('menuModif', $items, -1, 'classMenuBtn');


		$kdiv2 =& $kdiv1->addDiv('divDetail', 'blkData');
		$kdiv2->addMsg('tDrawRecap', '', 'kTitre');
		$nbMatch = 0;
		if ($draw['draw_type'] == WBS_GROUP)
		{

			$nbMatch += 3*$draw['draw_nbp3'] +
			6*$draw['draw_nbp4'] +
			10*$draw['draw_nbp5'];

			$nbQualif = $draw['draw_nbp3'] * $draw['draw_nbs3'] +
			$draw['draw_nbp4'] * $draw['draw_nbs4'] +
			$draw['draw_nbp5'] * $draw['draw_nbs5'];

			$rows = array( 0 => array('draw_3', 'Poule de 3',
			$draw['draw_nbp3'],
			$draw['draw_nbs3'], 3*$draw['draw_nbp3'],
			3*$draw['draw_nbp3']),
			array('draw_4',  'Poule de 4',
			$draw['draw_nbp4'],
			$draw['draw_nbs4'], 4*$draw['draw_nbp4'],
			6*$draw['draw_nbp4']),
			array('draw_5',  'Poule de 5',
			$draw['draw_nbp5'],
			$draw['draw_nbs5'], 5*$draw['draw_nbp5'],
			10*$draw['draw_nbp5']),
			array('draw_tot', '',
			$draw['draw_nbp3'] +
			$draw['draw_nbp4']+
			$draw['draw_nbp5'],
			$nbQualif,
			3*$draw['draw_nbp3'] +
			4*$draw['draw_nbp4'] +
			5*$draw['draw_nbp5'],
			$nbMatch));

			$krows =& $kdiv2->addRows("rowsGroups",  $rows, false);
			$krows->setSort(0);
			$krows->setNumber(false);
			$nbSecond  = $draw['draw_nbpl']-$nbQualif;
			$kdiv2->addinfo("drawNbSecond",  "$nbSecond");
		}

		// Liste des tours par KO
		$utr = new utround();
		$lines = array();
		for ($i=WBS_ROUND_QUALIF; $i<=WBS_ROUND_PLATEAU; $i++)
		{
			$rounds = $utr->getRounds($drawId, $i, 'rund_rge');
			foreach($rounds as $cur)
			{
				$lines[] = array($cur['rund_id'], $cur['rund_name'],
				$cur['rund_entries'], $cur['rund_entries']-1);
				$nbMatch += $cur['rund_entries']-1;
			}
		}
		if (count($lines))
		{
			$krows =& $kdiv2->addRows("rowsRound",  $lines, false);
			$krows->setSort(0);
			$krows->setNumber(false);

			$acts[1] = array(KAF_UPLOAD, 'draws', DRAW_KO_DISPLAY);
			$krows->setActions($acts);
		}
		$kdiv2->addinfo("drawNbmatch",  $nbMatch);

		$kdiv1->addDiv('page', 'blkNewPage');
		if ($draw['draw_id'] != -1)
		{
		}

		$this->_utpage->display();
		exit();
	}

	/**
	 * Display header of the page
	 *
	 * @access private
	 * @param array team  info of the team
	 * @return void
	 */
	function & _displayHeadDraw($draw, $select, $act)
	{
		$utd =& $this->_utd;
		$dt =& $this->_dt;
		$ut =& $this->_utd;
		$utr = new utround();

		// Creation de la page
		$this->_utpage = new utPage_A('draws', true, 'itDraws');
		$content =& $this->_utpage->getContentDiv();
		$drawId = $draw['draw_id'];

		// Bouton d'ajout
		$form =& $content->addForm('formPlus');
		$form->addBtn('btnNewDraw', KAF_NEWWIN, 'draws',  DRAW_SERIAL_NEW, 0, 500, 250);
		$form->addBtn('itDeleteDraw', KAF_NEWWIN, 'draws', KID_CONFIRM, $drawId, 350, 150);
		$form->addBtn('btnAllDraws', KAF_UPLOAD, 'draws',  DRAW_LIST);

		// Liste des tableaux
		$sort = array(5,4);
		$draws = $utd->getDraws($sort);
		foreach($draws as $theDraw)
		$list[$theDraw['draw_id']] = $theDraw['draw_name'];
		$kcombo=& $form->addCombo('selectList', $list, $list[$drawId]);
		$acts[1] = array(KAF_UPLOAD, 'draws', $act);
		$kcombo->setActions($acts);

		// Liste des groupes du tableau
		$oGroup = new objgroup();
		$groups = $oGroup->getListGroups($drawId);
		$utr = new utround();
		$list = array();
		$groupname = empty($draw['groupname'])?'':$draw['groupname'];
		foreach($groups as $group)
		{
			$url = urlencode($drawId . ';' . $group);
			$list[$url] = $group;
			if (empty($groupname)) $groupname = $group;
		}
		if (count($list) > 1)
		{
			$kcombo=& $form->addCombo('roundList', $list, urlencode($drawId . ';' . $groupname));
			$acts[1] = array(KAF_UPLOAD, 'draws', DRAW_GROUPS_DISPLAY);
			$kcombo->setActions($acts);
		}

		// Lien de redirection vers tous les tableaux
		$form->addBtn('itNewPlayer', KAF_NEWWIN, 'regi', REGI_SEARCH, 0, 650, 450);
		if($draw['draw_discipline'] == WBS_SINGLE) $form->addBtn('itPlayers', KAF_UPLOAD, 'draws',	DRAW_DISPLAY, $drawId);
		else $form->addBtn('itPairs', KAF_UPLOAD, 'draws',	DRAW_DISPLAY, $drawId);
		$isSquash = $this->_ut->getParam('issquash', false);
		if($isSquash) $form->addBtn('itClasse', KAF_NEWWIN, 'live', LIVE_CLASSE, 0, 500, 400);
		else $form->addBtn('itPalma', KAF_NEWWIN, 'live', LIVE_PALMARES, 0, 500, 400);

		// Menu de gestion des groupes
		$itemSel = $select;
		//$items['itDefinition']  = array(KAF_UPLOAD, 'draws', DRAW_DEFINITION, $drawId);
		$items['itNewGroup'] = array(KAF_NEWWIN, 'draws', DRAW_GROUP_NEW, $drawId, 350, 460);
		$rounds = $utr->getRounds($drawId, null, null, $groupname);
		if(count($rounds))
		{
			$items['itDeleteGroup'] = array(KAF_NEWWIN, 'draws', DRAW_GROUP_CONFIRM, urlencode($drawId .';' . $groupname), 300, 100);
			$items['itModifGroup'] = array(KAF_NEWWIN, 'draws', DRAW_GROUP_EDIT, urlencode($drawId. ';' . $groupname));
		}
		$rounds = $utr->getRounds($drawId, WBS_ROUND_GROUP, null, $groupname);
		if (!empty($rounds) )
		{
			$items['itGroup']  = array(KAF_UPLOAD, 'draws', DRAW_GROUPS_DISPLAY, urlencode($drawId. ';' . $groupname));
		}
		$rounds = $utr->getRounds($drawId, WBS_ROUND_MAINDRAW, null, $groupname);
		if ( !empty($rounds) )
		{
			$items['itMainDraw']  = array(KAF_UPLOAD, 'draws', DRAW_KO_DISPLAY, $rounds[0]['rund_id']);
		}
		$items['itCalendar']  = array(KAF_UPLOAD,'draws', DRAW_CALENDAR, $drawId);

		$kdiv =& $content->addDiv('choix', 'onglet3');
		$kdiv->addMenu("menuDiv", $items, $itemSel);
		$kdiv =& $content->addDiv('contDiv', 'cont3');

		return $kdiv;
	}
}

?>
