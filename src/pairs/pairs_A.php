<?php
/*****************************************************************************
 !   Module     : Pairs
 !   File       : $Source: /cvsroot/aotb/badnet/src/pairs/pairs_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.52 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:25 $
 ******************************************************************************/

require_once "base_A.php";
require_once "pairs.inc";
require_once "import/impffba.php";
require_once "import/impibf.php";
require_once "draws/draws.inc";
require_once "utils/utservices.php";
require_once "utils/objgroup.php";

/**
 * Module de gestion du carnet d'adresse : classe visiteurs
 *
 * @author Didier BEUVELOT <didier.beuvelot@free.fr>
 * @see to follow
 *
 */

class pairs_A
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
	function pairs_A()
	{
		$this->_ut = new utils();
		$this->_dt = new pairBase_A();
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
	  // Display a form to modify/update a pair
			case KID_EDIT:
				$this->_displayFormPair($id);
				break;
			case KID_UPDATE:
				$this->_updatePair();
				break;

				// Confimation from pair deletion
			case KID_CONFIRM:
				$this->_displayFormConfirm();
				break;
			case KID_DELETE:
				$this->_changeDrawPairs(-1, kform::getInput('rowsPairs'));
				break;

				// Add the pair to the draw
			case PAIR_ADD_PAIRS:
				$this->_changeDrawPairs(kform::getInput('drawId'),
				kform::getInput('rowsSelectPairs'));
				break;
				// Display pairs which are not in a draw
			case PAIR_LIST_PAIRS:
				$this->_displayFormPairs($id);
				break;

				// Display players for a pair
			case PAIR_EXPLODE:
				$this->_explodePair();
				break;
			case PAIR_IMPLODE:
				$this->_implodePair();
				break;

				// Add the pairs to the selected round
			case PAIR_SELECT_KOPAIRS:
				$roundId = kform::getData();
				$this->_displaySelectKoPairs($roundId);
				break;
			case PAIR_SELECT_GROUPPAIRS:
				$this->_displaySelectGroupPairs(kform::getData());
				break;
			case PAIR_GROUP2KO:
				$this->_displaySelectGroup2Ko(kform::getData());
				break;
			case PAIR_UPDATE_GROUP2KO:
				$this->_updateGroup2Ko();
				break;
				// Add the pair to the ko
			case PAIR_ADDKOROUND_PAIRS:
				$this->_updatePairsToKo();
				break;
				// Add the pair to the ko then dram a lot
			case PAIR_LOTKOROUND_PAIRS:
				$this->_updatePairsToKo(true);
				break;
				// Add the pair to the groups
			case PAIR_ADDGROUPROUND_PAIRS:
				$this->_updatePairsToGroups(false);
				break;
			case PAIR_DRAWINGLOT_GROUPS:
				$this->_updatePairsToGroups(true);
				break;

				// Updating pairs rank
			case PAIR_ASK_RANK:
				$this->_displayFromAskRank();
				break;
			case PAIR_GET_INTRANK:
				$this->_getIntRank();
				break;
			case PAIR_GET_NATRANK:
				$this->_getNatRank();
				break;
			case PAIR_UPDATE_NATRANK:
				$this->_updateNatRank();
				break;
			case PAIR_UPDATE_INTRANK:
				$this->_updateIntRank();
				break;

				// Updating pairs status
			case PAIR_UPDATE_STATUS:
				$status = kform::getData();
				$this->_updateStatusPair($status);
				break;

				// Change the publication status of selected draws
			case KID_CONFIDENT:
				$this->_displayFormPubli(WBS_DATA_CONFIDENT);
				break;
			case KID_PRIVATE:
				$this->_displayFormPubli(WBS_DATA_PRIVATE);
				break;
			case KID_PUBLISHED:
				$this->_displayFormPubli(WBS_DATA_PUBLIC);
				break;
			case PAIR_ASK_T2RSTATUS:
				$this->_displayFromAskStatus();
				break;
			case PAIR_UPDATE_T2RSTATUS:
				$this->_updatePairToRoundStatus();
				break;
			default :
				echo "pair_A->start($action) non autorise <br>";
				$this->_displayFormList();
		}
	}
	// }}}

	// {{{ _explodePair()
	/**
	 * Destroy the pair
	 *
	 * @access private
	 * @return void
	 */
	function _explodePair()
	{
		require_once "utils/utpair.php";
		$utp = new utpair();

		// Get the selected pairs
		$pairIds = kform::getInput('rowsPairs', array());

		// Explode the selected pairs
		if (count($pairIds))
		{
	  $res = $utp->explode($pairIds);
	  if (isset($res['errMsg']))
	  $msg = $res['errMsg'];
		}
		else
		$msg = 'msgNeedPairs';

		if (isset($msg))
		{
	  $utpage = new utPage('pairs');
	  $content =& $utpage->getPage();
	  $form =& $content->addForm('fPairs', 'pairs', KID_NONE);
	  $form->setTitle('tImplode');
	  $form->addWng($msg);
	  $form->addBtn('btnCancel');
	  $elts = array('btnCancel');
	  $form->addBlock('blkBtn', $elts);
	  $utpage->display();
	  exit;
		}
		$page = new utPage('none');
		$page->close();
		exit();
	}
	// }}}

	// {{{ _implodePair()
	/**
	 * Update the pair in the database
	 *
	 * @access private
	 * @return void
	 */
	function _implodePair()
	{
		require_once "utils/utpair.php";
		$utp = new utpair();

		// Get the selected pairs
		$pairIds = kform::getInput('rowsPairs');

		// Add the first selected player to the pair
		if (count($pairIds) != 2)
		{
	  $utpage = new utPage('pairs');
	  $content =& $utpage->getPage();
	  $form =& $content->addForm('fPairs', 'pairs', KID_NONE);
	  $form->setTitle('tImplode');
	  $form->addWng('msgNeedTwoPlayers');
	  $form->addBtn('btnCancel');
	  $elts = array('btnCancel');
	  $form->addBlock('blkBtn', $elts);
	  $utpage->display();
	  exit;
		}
		else
		{
	  $utp->implode($pairIds[0], $pairIds[1], WBS_PAIRSTATE_COM);
		}
		$page = new utPage('none');
		$page->close();
		exit();
	}
	// }}}


	// {{{ _updateIntRank()
	/**
	 * Update international ranking of the pairs
	 *
	 * @access private
	 * @return void
	 */
	function _updateIntRank()
	{
		$dt = $this->_dt;

		// First get the informations
		$drawId = kform::getInput('drawId');

		// Recuperer les classements de la page WEB
		$imp = new impibf();
		$ranks = $imp->parseRank();

		if ($ranks!=false)
		{
	  $skip = $ranks['skip'];
	  unset($ranks['skip']);
	  unset($ranks['date']);

	  // Mettre a jour le classement des joueurs/paire du tableau
	  $err = $dt->updateIntRank($drawId, $ranks);
	  if ($skip && $skip<400)
	  $this->_getIntRank($drawId, $skip);
		}
		$page = new kPage('none');
		$page->close();
		exit();
	}
	// }}}


	// {{{ _updateNatRank()
	/**
	 * Search a national ranking of the pairs
	 *
	 * @access private
	 * @return void
	 */
	function _updateNatRank()
	{
		$dt = $this->_dt;

		// First get the informations
		$drawId = kform::getInput('drawId');

		// Recuperer les classements de la page WEB
		$imp = new impffba();
		$ranks = $imp->parseRank();
		if ($ranks!=false)
		{
	  $next = $ranks['next'];
	  unset($ranks['next']);
	  unset($ranks['date']);

	  // Mettre a jour le classement des joueurs/paire du tableau
	  $err = $dt->updateNatRank($drawId, $ranks);
		}
		$page = new kPage('none');
		$page->close();
		exit();
	}
	// }}}


	// {{{ _getIntRank()
	/**
	 * Search a national ranking of the pairs
	 *
	 * @access private
	 * @return void
	 */
	function _getIntRank($drawId='', $skip=0)
	{
		$utd = new utdraw();

		$dt = $this->_dt;
		if ($drawId=='')
		$drawId=kform::getInput('drawId');

		// First get the informations
		$fields['kpid']   = 'pairs';
		$fields['kaid']   = PAIR_UPDATE_INTRANK;
		$fields['drawId'] = $drawId;

		$draw = $utd->getDrawById($drawId);
		switch($draw['draw_disci'])
		{
			case WBS_MS:
				$where['category'] = "MS";
				break;
			case WBS_WS:
				$where['category'] = "WS";
				break;
			case WBS_MD:
				$where['category'] = "MD";
				break;
			case WBS_WD:
				$where['category'] = "WD";
				break;
			case WBS_MX:
				$where['category'] = "MX";
				break;
		}
		$where['from'] = 1;
		$where['to'] = 400;
		$where['skip'] = $skip;

		// Charger la page WEB contenant les infos
		$imp = new impibf();
		$imp->loadRank($fields, $where);
		exit();
	}
	// }}}


	// {{{ _getNatRank()
	/**
	 * Search a national ranking of the pairs
	 *
	 * @access private
	 * @return void
	 */
	function _getNatRank()
	{
		$utd = new utdraw();

		$dt = $this->_dt;
		// First get the informations
		$fields['kpid']   = 'pairs';
		$fields['kaid']   = PAIR_UPDATE_NATRANK;
		$fields['drawId'] = kform::getInput('drawId');

		$draw = $utd->getDrawById($fields['drawId']);
		switch($draw['draw_disci'])
		{
			case WBS_MS:
				$where['disci'] = "S";
				$where['sexe'] = "M";
				break;
			case WBS_WS:
				$where['disci'] = "S";
				$where['sexe'] = "F";
				break;
			case WBS_MD:
				$where['disci'] = "D";
				$where['sexe'] = "M";
				break;
			case WBS_WD:
				$where['disci'] = "D";
				$where['sexe'] = "F";
				break;
			case WBS_MX:
				$where['disci'] = "M";
				$where['sexe'] = "M";
				break;
		}

		// Charger la page WEB contenant les infos
		$imp = new impffba();
		$imp->loadRank($fields, $where);
		exit();
	}
	// }}}


	// {{{ _displayFormAskStatus()
	/**
	 * Search a national ranking of the pairs
	 *
	 * @access private
	 * @return void
	 */
	function _displayFromAskStatus()
	{
		$dt = $this->_dt;

		$utpage = new utPage('pairs');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fPairs', 'pairs', PAIR_UPDATE_T2RSTATUS);
		$form->setTitle('tAskStatus');

		// Get the id's
		$t2rId = kform::getData();
		$form->addHide('t2rId', $t2rId);

		$ut = new utils();
		for ($i=WBS_PAIR_PTQ; $i<=WBS_PAIR_NONE; $i++)
		$status[$i] = $ut->getLabel($i);
		$form->addCombo('status', $status);

		//Display the page
		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit;
	}
	// }}}


	// {{{ _updatePairToRoundStatus()
	/**
	 * Change the status of the selected pairs
	 *
	 * @access private
	 * @param  integer $publi  new publication state of the event
	 * @return void
	 */
	function _updatePairToRoundStatus()
	{
		$dt = $this->_dt;

		$t2rId = kform::getInput("t2rId");
		$status = kform::getInput("status");
		$dt->updatePairToRoundStatus($t2rId, $status);
		$page = new utPage('none');
		$page->close();
		exit;
	}
	// }}}


	// {{{ _displayFormAskRank()
	/**
	 * Search a national ranking of the pairs
	 *
	 * @access private
	 * @return void
	 */
	function _displayFromAskRank($drawId='', $msg='')
	{
		$utd = new utdraw();
		$dt = $this->_dt;

		$utpage = new utPage('pairs');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fPairs', 'pairs', PAIR_GET_INTRANK);
		$form->setTitle('tAskRank');

		// Get the id's
		$pairIds = kform::getInput("rowsPairs", array());
		if (!count($pairIds))
		$form->addWng('msgNeedPairs');
		else
		{
	  if ($drawId == '')
	  $drawId = kform::getData();
	  $form->addHide('drawId', $drawId);
	  foreach($pairIds as $pairId)
	  $form->addHide('pairId[]', $pairId);
	  $form->addBtn('btnInt', KAF_UPLOAD, 'pairs', PAIR_GET_INTRANK);
	  $form->addBtn('btnNat', KAF_UPLOAD, 'pairs', PAIR_GET_NATRANK);
		}
		//Display the page
		$form->addBtn('btnCancel');
		$elts = array('btnInt', 'btnNat', 'btnCancel');
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _updateStatusPair()
	/**
	 * Change the status of the selected pairs
	 *
	 * @access private
	 * @param  integer $publi  new publication state of the event
	 * @return void
	 */
	function _updateStatusPair($status, $err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('pairs');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fPairs', 'pairs', PAIR_UPDATE_STATUS);
		$form->setTitle('tStatusPairs');

		// Get the id's
		$ids = kform::getInput("rowsPairs", array());
		if (!count($ids))
		$form->addWng('msgNeedPairs');
		else
		{
	  $res = $dt->updateStatusPairs($ids, $status);
	  if (isset($res['errMsg']))
	  $form->addWng($res['errMsg']);
	  else
	  $content->close();
		}
		//Display the page
		$form->addBtn('btnCancel');
		$elts = array('btnCancel');
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormPubli()
	/**
	 * Change the publication state of the selected members
	 *
	 * @access private
	 * @param  integer $publi  new publication state of the event
	 * @return void
	 */
	function _displayFormPubli($publi, $err='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('pairs');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fPairs', 'pairs', KID_PUBLISHED);
		$form->setTitle('tPubliPairs');

		// Get the id's
		$ids = kform::getInput("rowsPairs", array());
		if (!count($ids)) $form->addWng('msgNeedPairs');
		else
		{
	  		$res = $dt->publiPairs($ids, $publi);
	  		if (isset($res['errMsg'])) $form->addWng($res['errMsg']);
			else $utpage->close();
		}
		//Display the page
		$form->addBtn('btnCancel');
		$elts = array('btnCancel');
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _updatePairsToGroups()
	/**
	 * Add the selected pairs to the current group round
	 *
	 * @access private
	 * @return void
	 */
	function _updatePairsToGroups($drawingLot=false)
	{
		$utr = new utround();
		$dt = $this->_dt;
		$utd = new utdate();

		$start = $utd->getMicroTime();
		// Get the informations
		$drawId = kform::getInput('drawId');
		$groupName = kform::getInput('groupName');

		// Create/update relation between pairs and round
		$pairId = kform::getInput('pairId');
		$pairTds = kform::getInput('pairTds');
		$pairPos = kform::getInput('pairPos');
		$pairRank = kform::getInput('pairRank');
		$pairSelect = kform::getInput('pairGroups', array());
		$utdr = new utdraw();
		$draw = $utdr->getDrawById($drawId);

		$oGroup = new objgroup();
		$group = $oGroup->getGroup($drawId, $groupName);

		// Construire les listes des paires retenues et non retenues
		$size = count($pairId);
		$pairsIn = array();
		$pairsOut = array();
		$tds[WBS_TDS_1] = 0;
		$tds[WBS_TDS_2] = 0;
		$tds[WBS_TDS_3_4] = array();
		$tds[WBS_TDS_5_8] = array();
		$tds[WBS_TDS_9_16] = array();
		for ($i=0; $i<$size; $i++)
		{
			$pair['id']  = $pairId[$i];
			$pair['tds'] = $pairTds[$i];
			$pair['pos'] = strtoupper($pairPos[$i]);
			if (in_array($pair['id'], $pairSelect) ||
			($pair['tds'] != WBS_TDS_NONE) ||
			($pair['pos'] != ''))
			{
				// Controle du nombre et de la position des tds
				switch ($pair['tds'])
				{
					case WBS_TDS_1:
						if ($tds[WBS_TDS_1]) $pair['tds'] = WBS_TDS_NONE;
						else
						{
							$pair['pos'] = 'A1';
							$tds[WBS_TDS_1]++;
						}
						break;
					case WBS_TDS_2:
						if ($tds[WBS_TDS_2]) $pair['tds'] = WBS_TDS_NONE;
						else
						{
							$pair['pos'] = 'B1';
							$tds[WBS_TDS_2]++;
						}
						break;
					case WBS_TDS_3_4:
						if (count($tds[WBS_TDS_3_4]) == 2) $pair['tds'] = WBS_TDS_NONE;
						else
						{
							$pos = $pair['pos'];
							if (($pos != "C1" && $pos != "D1") ||
							isset($tds[WBS_TDS_3_4][$pos]))
							{
								$pair['pos'] = '';
								for ($j=0; $j<2;$j++)
								{
									$pos = chr(ord('C')+$j).'1';
									if (!isset($tds[WBS_TDS_3_4][$pos]))
									{
										$pair['pos'] = $pos;
										$tds[WBS_TDS_3_4][$pos] = 1;
										break;
									}
								}
							}
							else
							$tds[WBS_TDS_3_4][$pos] = 1;
						}
						break;
					case WBS_TDS_5_8:
						if (count($tds[WBS_TDS_5_8]) == 4) $pair['tds'] = WBS_TDS_NONE;
						else
						{
							$pos = $pair['pos'];
							if (($pos != "E1" && $pos != "F1" &&
							$pos != "G1" && $pos != "H1") ||
							isset($tds[WBS_TDS_5_8][$pos]))
							{
								$pair['pos'] = '';
								for ($j=0; $j<4;$j++)
								{
									$pos = chr(ord('E')+$j).'1';
									if (!isset($tds[WBS_TDS_5_8][$pos]))
									{
										$pair['pos'] = $pos;
										$tds[WBS_TDS_5_8][$pos] = 1;
										break;
									}
								}
							}
							else
							$tds[WBS_TDS_5_8][$pos] = 1;
						}
						break;
					case WBS_TDS_9_16:
						if (count($tds[WBS_TDS_9_16]) == 8) $pair['tds'] = WBS_TDS_NONE;
						else
						{
							$pos = $pair['pos'];
							if (($pos != "I1" && $pos != "J1" &&
							$pos != "K1" && $pos != "L1" && $pos != "M1" && $pos != "N1" &&
							$pos != "O1" && $pos != "P1") ||
							isset($tds[WBS_TDS_9_16][$pos]))
							{
								$pair['pos'] = '';
								for ($j=0; $j<8;$j++)
								{
									$pos = chr(ord('K')+$j).'1';
									if (!isset($tds[WBS_TDS_9_16][$pos]))
									{
										$pair['pos'] = $pos;
										$tds[WBS_TDS_9_16][$pos] = 1;
										break;
									}
								}
							}
							else $tds[WBS_TDS_9_16][$pos] = 1;
						}
						break;
					default:
						break;
				}
				// Memorisation de la paire
				$sort[] = $pairRank[$i];
				$pairsIn[] = $pair;
			}
			else $pairsOut[] = $pair['id'];
		}

		$isSquash = $this->_ut->getParam('issquash', false);
		if (count($pairsIn))
		if ($isSquash) array_multisort($sort, $pairsIn);
		else array_multisort($sort, SORT_DESC, $pairsIn);

		if (count($pairsIn) && count($pairsIn) != $group['nbPlace']) $this->_displaySelectGroupPairs($drawId.';'.$groupName, 'msgBadPairsNum');

		$end = $utd->getMicroTime();
		$laps = $end - $start;
		$start = $end;

		require_once "utils/utgroup.php";
		$dtg = new utgroup();
		$res = $dtg->updatePairsToGroups($drawId, $pairsIn, $pairsOut, $groupName);
		if (isset($res['errMsg'])) $this->_displaySelectGroupPairs($drawId.';'.$groupName, $res['errMsg']);

		$end = $utd->getMicroTime();
		$laps = $end - $start;
		$start = $end;

		if ($drawingLot) $this->_drawingLotGroups($drawId, $groupName);
		$end = $utd->getMicroTime();
		$laps = $end - $start;
		$start = $end;
		//echo "third step:$laps<br>";

		//$this->_ut->loadPage('draws', DRAW_PDF_GROUPS, $drawId.';'.$groupName);
		$page = new utPage('none');
		$page->close();
		exit();
		
	}
	// }}}

	// {{{ _drawingLotGroups()
	/**
	 * Effectue le tirage au sort pour un tableau en poule
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _drawingLotGroups($drawId, $aGroupName)
	{
		$utr = new utRound();

		// Get the pairs of the round
		$rounds = $utr->getRounds($drawId, WBS_ROUND_GROUP, null, $aGroupName);
		$newPairs = array();
		if (isset($rows['errMsg'])) $form->addWng($rows['errMsg']);
		else
		{
			foreach($rounds as $round)
			{
				$pairs = $this->_dt->getRoundPairs($round['rund_id']);
				foreach($pairs as $pair)
				{
					$newPair['id']       = $pair['t2r_pairId'];
					$newPair['tds']      = $pair['t2r_tds'];
					$newPair['level']    = $pair['level'];;
					$newPair['average']  = $pair['average'];;
					$newPair['team']     = $pair['teamId'];
					$newPair['natRank']  = $pair['pair_natRank'];
					$newPair['intRank']  = $pair['pair_intRank'];
					$newPairs[] = $newPair;
				}
			}
		}

		// Initialize
		if (!count($newPairs)) $res['errMsg'] = 'msgNeedPairsInRound';
		else
		{
			// Drawing a lot
			require_once "utils/utgroup.php";
			$utg = new utgroup();
			$res = $utg->goLot($drawId, $newPairs, $aGroupName);
		}
		if (isset($res['errMsg']))
		{
			$utpage = new utPage('pairs');
			$content =& $utpage->getPage();
			$form =& $content->addForm('tDrawingLot', 'pairs', KID_NONE);
			$form->addWng($res['errMsg']);
			$form->addBtn('btnCancel');
			$utpage->display();
			exit();
		}

		return;
	}
	//}}}

	/**
	 * Tirage au sort direct des sorties de poule
	 *
	 * @access private
	 * @return void
	 */
	function _updateGroup2Ko()
	{
		$utr = new utround();
		$dt =& $this->_dt;

		$group = kform::getData();
		list($drawId, $groupName) = explode(';', $group);
		$oGroup = new objgroup();
		$group = $oGroup->getGroup($drawId, $groupName);
		$roundId = $group['mainroundid'];
		if (empty($roundId)) return false;
		$round = $utr->getRoundDef($roundId);

		$utdr = new utdraw();
		$draw = $utdr->getDrawById($drawId);

		$nbSelected = 0;
		$selected = array();
		$pairs = $dt->getPairsForGroup2Ko($drawId, $groupName);
		if (isset($pairs['errMsg'])) return false;
		foreach($pairs as $pair)
		{
			$buf[$nbSelected]['id'] = $pair[0];
			if ($nbSelected >= $round['rund_entries'])  $buf[$nbSelected]['pos'] = 0;
			else $buf[$nbSelected]['pos'] = 1000 + $nbSelected;
			$buf[$nbSelected]['status'] = 0;
			$selects = $pair[1]['select'];
			foreach($selects as $tds=>$option)
			{
				if (isset($option['select']) )
				{
					$buf[$nbSelected]['tds'] = $tds;
					break;
				}
				else $buf[$nbSelected]['tds'] = WBS_TDS_NONE;
			}
			$nbSelected++;
		}

		// First delete old relation between pairs and match
		$res = $utr->deletePairToMatch($roundId);
		$utk = new utko($buf, $round['rund_entries']);
		$posDraw = $utk->getExpandedPositions();
		$freePos = $posDraw;

		// Mettre a jour les paires
		for ($i=0; $i<$nbSelected; $i++)
		{
			$pos = $buf[$i]['pos'];
			if ($pos && !in_array($pos, $posDraw)) $buf[$i]['pos'] = array_shift($freePos);
			// Placer la paire dans le tableau
			$res = $dt->updatePairToRound($roundId, $buf[$i]['id'], $buf[$i]['tds'],
			$buf[$i]['pos'], $buf[$i]['status']);
		}

		// Now create new relation between pairs and match
		$res = $utr->createPairToMatch($roundId);
		$posDraw = $utk->getExpandedPositions();
		$this->_drawingLotKo($groupName, $roundId);

		// Enregistrement du tableau sur le site distant
		$server = $this->_ut->getParam('synchroUrl', false);
		if ($server)
		{
			$user     = $this->_ut->getParam('synchroUser');
			$pwd      = md5($this->_ut->getParam('synchroPwd'));
			$eventId  = $this->_ut->getParam('synchroEvent');
			$uts = new utServices($server, $user, $pwd);
			$res = $uts->updateDraw($eventId, $roundId);
			unset($uts);
		}

		// Affichage du tableau PDF
		$this->_ut->loadPage('draws', DRAW_PDF_KO, $roundId);
		$page = new utPage('none');
		$page->close();
		exit();
	}


	// {{{ _updatePairsToKo()
	/**
	 * Add the selected pairs to the current ko round
	 *
	 * @access private
	 * @return void
	 */
	function _updatePairsToKo($drawingLot=false)
	{
		$utr = new utround();
		$dt =& $this->_dt;

		// Get the informations
		$roundId = kform::getInput('roundId');
		$groupName = kform::getInput('groupName');
		$drawId    = kform::getInput('drawId');

		// Create/update relation between pairs and round
		$pairTds = kform::getInput("pairTds");
		$pairId = kform::getInput("pairId");
		$pairPos = kform::getInput("pairPos");
		$pairStatus = kform::getInput("pairStatus", array());
		$pairSelect = kform::getInput("pairList2ko", array());
		$pairRank   = kform::getInput('pairRank');
		$size = count($pairTds);
		$round = $utr->getRoundDef($roundId);
		$nb=0;

		// Calculer la position des paires
		for ($i=0; $i<$size; $i++)
		{
			if (in_array($pairId[$i], $pairSelect) && (!is_numeric($pairPos[$i]) || $pairPos[$i]==0))
			{
				$pairPos[$i] = 1000+$i;//$pairRank[$i];
			}
			if (($pairTds[$i] != WBS_TDS_NONE) && (!is_numeric($pairPos[$i]) || $pairPos[$i]==0))
			{
				$pairPos[$i] = 1000+$i;//$pairRank[$i];
			}
			if (is_numeric($pairPos[$i]) && ($pairPos[$i]>0))
			{
				$nb++;
			}

			if ($nb > $round['rund_entries'] || $pairPos[$i] == '')
			{
				$pairPos[$i] = 0;
			}

			$buf[$i]['id'] = $pairId[$i];
			$buf[$i]['pos'] = $pairPos[$i];
			$buf[$i]['status'] = isset($pairStatus[$i])? $pairStatus[$i]:0;
			$buf[$i]['tds'] = $pairTds[$i];
		}
		array_multisort($pairPos, $buf);

		$utdr = new utdraw();
		$round = $utr->getRoundDef($roundId);
		$draw = $utdr->getDrawById($round['rund_drawId']);
		$kota = $round['rund_entries'];
		$oGroup = new objgroup();
		$group = $oGroup->getGroup($drawId, $groupName);

		// Controler le nombre de paire
		if ($nb && $nb != $kota)
		{
			if (empty($groupName) )
			{
				$this->_displaySelectKoPairs($roundId, 'msgBadPairsNum');
			}
			else
			{
				$this->_displaySelectGroup2Ko($round['rund_drawId'].';'.$groupName.';'.$roundId, 'msgBadPairsNum');
			}
		}
		// First delete old relation between pairs and match
		$res = $utr->deletePairToMatch($roundId);

		$utk = new utko($buf, $round['rund_entries']);
		$posDraw = $utk->getExpandedPositions();
		$freePos = array_diff($posDraw, $pairPos);
		// Mettre a jour les paires
		for ($i=0; $i<$size; $i++)
		{
			// Calculer la position de la paire
			// La paire est dans le tableau, mais a une position invalide
			// prendre alors la premiere place de libre
			$pos = $buf[$i]['pos'];
			if ($pos && !in_array($pos, $posDraw)) $buf[$i]['pos'] = array_shift($freePos);
			// Placer la paire dans le tableau
			$res = $dt->updatePairToRound($roundId, $buf[$i]['id'], $buf[$i]['tds'],
			$buf[$i]['pos'], $buf[$i]['status']);
		}

		// Now create new relation between pairs and match
		$res = $utr->createPairToMatch($roundId);
		if($nb && $drawingLot) $this->_drawingLotKo($groupName);

		// Enregistrement du tableau sur le site distant
		$server = $this->_ut->getParam('synchroUrl', false);
		if ($server)
		{
			$user     = $this->_ut->getParam('synchroUser');
			$pwd      = md5($this->_ut->getParam('synchroPwd'));
			$eventId  = $this->_ut->getParam('synchroEvent');
			$uts = new utServices($server, $user, $pwd);
			$res = $uts->updateDraw($eventId, $roundId);
			unset($uts);
		}

		// Affichage du tableau PDF
		//$this->_ut->loadPage('draws', DRAW_PDF_KO, $roundId);
		$page = new utPage('none');
		$page->close();
		exit();
	}

	/**
	 * Effectie le tirage au sort pour un tableau KO
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _drawingLotKo($aGroupName, $aRoundId = 0)
	{
		$utr = new utRound();

		// Get the pairs of the round
		$roundId = kform::getInput('roundId', $aRoundId);
		$round = $utr->getRoundDef($roundId);
		$pairs = $this->_dt->getRoundPairs($roundId);

		// Prepare the data
		// Critere de separation pour le tirage au sort
		// Par defaut c'est la poule (rund_stamp) et le classement (cas de sorite de poule)
		// Sinon c'est choisi par l'utilisateur
		$criteria = kform::getInput('criteria', 'rund_stamp');
		$criteria2 = kform::getInput('criteria2', 't2r_rank');
		$newPairs= array();
		foreach($pairs as $pair)
		{
			$newPair['id'] = $pair['t2r_pairId'];
			$newPair['tds'] = $pair['t2r_tds'];
			$newPair['criteria'] = $pair[$criteria];
			if ( $criteria2 != 'none' ) $newPair['secondCriteria'] = $pair[$criteria2];
			else $newPair['secondCriteria'] = '';
			$newPair['intRank'] = $pair['pair_intRank'];;
			$newPair['natRank'] = $pair['pair_natRank'];;
			$newPair['level']   = $pair['level'];;
			$newPair['average'] = $pair['average'];;
			$newPairs[] = $newPair;
		}
		if (!count($newPairs)) $res['errMsg'] = 'msgNeedPairsInKo';
		else
		{
			// Initialize
			$degre = kform::getInput('degres', 2);
			$utk = new utko($newPairs, $round['rund_entries']);
			$res = $utk->initDraw($degre);

			// Drawing a lot
			$resu = $utk->goDraw($degre);
		}
		if (isset($resu['errMsg']))
		{
			$utdr = new utdraw();
			$draw = $utdr->getDrawById($round['rund_drawId']);
			if ($draw['draw_type'] == WBS_QUALIF || $draw['draw_type'] == WBS_KO) $this->_displaySelectKoPairs($roundId, $resu['errMsg']);
			else $this->_displaySelectGroup2Ko($round['rund_drawId'].';'.$aGroupName.';'.$roundId, $resu['errMsg']);
		}

		// First delete old relation between pairs and match
		$utr->deletePairToMatch($roundId);

		// updating the draw
		$this->_dt->updatePairPos($roundId, $resu);

		// Now create new relation between pairs and match
		$utr->createPairToMatch($roundId);
		return;
	}

	/**
	 * Display a page with the list of the pairs of a draw
	 * for selection in a round from groups to main draw
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displaySelectGroup2Ko($aGroup, $err='')
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$utr = new utRound();
		$utd = new utDraw();

		list($drawId, $groupName, $roundId) = explode(';', $aGroup);

		$utpage = new utPage('pairs');
		$utpage->_page->addAction('onload', array('resize', 700, 600));
		$utpage->_page->addAction('onunload', array('refresh'));
		$utpage->_page->addJavaFile('pairs/pairs.js');

		$content =& $utpage->getPage();
		$form =& $content->addForm('tSelectG2KPair', 'pairs', PAIR_ADDKOROUND_PAIRS);

		if ($err != '')	$form->addWng($err);

		$oGroup = new objgroup();
		$group = $oGroup->getGroup($drawId, $groupName);

		$draw = $utd->getDrawById($drawId);
		if ($roundId == -1)
		{
			$round = $utr->getRounds($drawId, WBS_ROUND_MAINDRAW, null, $groupName);
			$roundId=$round[0]['rund_id'];
			$kota = $group['nbQual'];
		}
		else
		{
			$round = $utr->getRoundDef($roundId);
			$kota = $round['rund_entries'];
		}

		$form->addHide('drawId', $drawId);
		$form->addHide('groupName', $groupName);
		$form->addHide('roundId',   $roundId);
		$form->addInfo('drawName',  $draw['draw_name']);
		$form->addInfo('group',     $groupName);

		if ($group['nb3'] && $group['nbs3']) $form->addInfo('nbsp3', $group['nbs3']);
		if ($group['nb4'] && $group['nbs4']) $form->addInfo('nbsp4', $group['nbs4']);
		if ($group['nb5'] && $group['nbs5']) $form->addInfo('nbsp5', $group['nbs5']);
		if ($group['nbsecond'])	$form->addInfo('nbSecond', $group['nbsecond']);
		$form->addInfo('nbQualif', $kota);

		$elts = array('drawName', 'group', 'nbsp3','nbsp4','nbsp5','nbSecond','nbQualif');
		$form->addBlock('definition', $elts);

		$nbSelected = 0;
		$selected = array();
		$pairs = $dt->getPairsForGroup2Ko($drawId, $groupName);
		if (isset($pairs['errMsg']))
		{
			$form->addWng($pairs['errMsg']);
			unset($pairs['errMsg']);
		}
		foreach($pairs as $pair)
		{
			$form->addHide("pairId[]", $pair[0]);
			if ($nbSelected < $draw['draw_nbpl'])
			{
				$selected[] = $pair[0];
				$nbSelected++;
			}
		}
		$krows =& $form->addRows('pairList2ko', $pairs);
		$krows->setSort(false);
		$krows->setSelect($selected);

		if (count($pairs))
		{
			$form->addBtn('btnRaz', 'raz', count($pairs), 'pairList2ko');
			$form->addBtn('btnRegister', KAF_SUBMIT);
			$form->addBtn('btnSeeded', KAF_UPLOAD, 'pairs', PAIR_LOTKOROUND_PAIRS, $drawId);
		}
		$form->addBtn('btnCancel');
		$elts = array('btnRaz', 'btnSeeded', 'btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts, 'blkBtn');

		//Display the form
		$utpage->display();
		exit;
	}

	// {{{ _displaySelectKoPairs()
	/**
	 * Display a page with the list of the pairs of a draw
	 * for selection in a round
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displaySelectKoPairs($roundId, $err='')
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$utr = new utRound();
		$utd = new utDraw();

		$round = $utr->getRoundDef($roundId);
		$draw = $utd->getDrawById($round['rund_drawId']);

		$utpage = new utPage('pairs');
		$utpage->_page->addAction('onload', array('resize', 700, 600));
		$utpage->_page->addAction('onunload', array('refresh'));
		$utpage->_page->addJavaFile('pairs/pairs.js');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tSelectKoPair', 'pairs', PAIR_ADDKOROUND_PAIRS);

		$form->addHide('roundId', $roundId);
		$form->addHide('drawId', $draw['draw_id']);
		$form->addInfo('drawName', $draw['draw_name']);
		$form->addInfo('group', $round['rund_group'] . ' - ' . $round['rund_name']);
		if ($err != '')	$form->addErr($err);

		$criteria = array('noc'=>'Noc',	'teamId'=>'Club');
		//'assoId'=>'Assoc');
		$form->addCombo('criteria', $criteria, 'teamId');

		$form->addHide('criteria2', 'none');

		$degres =array(2=>2, 4=>4, 8=>8, 16=>16);
		$form->addCombo('degres', $degres, 4);

		//		$form->addBtn('btnSeeded', KAF_UPLOAD, 'pairs',
		//		PAIR_LOTKOROUND_PAIRS,  $roundId);

		$form->addInfo('nbPlaces', $round['rund_entries']);

		$elts=array('drawName', 'group', 'nbPlaces', 'criteria', 'criteria2', 'degres');
		$form->addBlock("blkOne",  $elts);
		$form->addDiv('break', 'blkNewPage');

		$pairs = $dt->getPairsForKo($roundId, $round['rund_entries']);
		if (isset($pairs['errMsg']))
		{
			$form->addWng($pairs['errMsg']);
			unset($pairs['errMsg']);
		}
		else
		{
			$form->addBtn('btnSeeded', 'seedKo', PAIR_LOTKOROUND_PAIRS, $roundId);
			$form->addBtn('btnRaz', 'raz', count($pairs), 'pairList2ko');
			$form->addBtn('btnStandard', KAF_SUBMIT);
		}
		$form->addBtn('btnCancel');
		$elts = array('btnStandard', 'btnRaz', 'btnSeeded', 'btnCancel');
		$form->addBlock('blkBtn', $elts, 'blkBtn');

		$nbSelected = 0;
		$selected = array();
		foreach($pairs as $pair)
		{
			$form->addHide("pairId[]", $pair['t2r_pairId']);
			$form->addHide("pairRank[]", $pair['average']);
			if ($nbSelected < $round['rund_entries'])
			{
				$selected[] = $pair['t2r_pairId'];
				$nbSelected++;
			}
		}

		$krows =& $form->addRows('pairList2ko', $pairs);
		$krows->setSelect($selected);
		$krows->setSort(0);

		//Display the form
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displaySelectGroupPairs()
	/**
	 * Display a page with the list of the pairs of a draw
	 * for selection in a round
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displaySelectGroupPairs($aGroup, $err='')
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;
		$utr = new utRound();
		$utd = new utDraw();

		list($drawId, $groupName) = explode(';', $aGroup);

		$utpage = new utPage('pairs');
		$utpage->_page->addAction('onload', array('resize', 700, 600));
		$utpage->_page->addAction('onunload', array('refresh'));
		$utpage->_page->addJavaFile('pairs/pairs.js');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tSelectGroupPair', 'pairs', PAIR_ADDGROUPROUND_PAIRS);

		$form->addHide('drawId', $drawId);
		$form->addHide('groupName', $groupName);
		if ($err != '')	$form->addErr($err);

		$oGroup = new objgroup();
		$group = $oGroup->getGroup($drawId, $groupName);

		$draw = $utd->getDrawById($drawId);
		$form->addInfo('drawName', $draw['draw_name']);
		$form->addInfo('group', $groupName);
		$form->addInfo('nbPlaces', $group['nbPlace']);
		$elts = array('drawName', 'group', 'nbPlaces');

		$form->addblock('blkInfo', $elts);
		$pairs = $dt->getPairsForGroup($drawId, $group['nb3']+$group['nb4']+$group['nb5'], $groupName);
		if (isset($pairs['errMsg']))
		{
			$form->addWng($pairs['errMsg']);
			unset($pairs['errMsg']);
		}
		else
		{
			$form->addBtn('btnRaz', 'raz', count($pairs), 'pairGroups');
			$form->addBtn('btnRegister', KAF_SUBMIT);
			$form->addBtn('btnSeeded', 'seedGroup',	PAIR_DRAWINGLOT_GROUPS, $aGroup);
		//$form->addBtn('btnSeeded', KAF_UPLOAD, 'pairs',
		//              PAIR_DRAWINGLOT_GROUPS, $drawId);
		}
		$nbSelected = 0;
		$selected = array();
		foreach($pairs as $pair)
		{
			$form->addHide("pairId[]", $pair['pair_id']);
			$form->addHide("pairRank[]", $pair['average']);
			if ($nbSelected < $group['nbPlace'])
			{
				$selected[] = $pair['pair_id'];
				$nbSelected++;
			}
		}
		$krows =& $form->addRows('pairGroups', $pairs);
		$krows->setSort(false);
		$krows->setSelect($selected);

		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnRaz', 'btnSeeded', 'btnCancel');
		$form->addBlock('blkBtn', $elts, 'blkBtn');

		//Display the form
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _changeDrawPairs()
	/**
	 * Modify th edraw of the selected pairs
	 *
	 * @access private
	 * @return void
	 */
	function _changeDrawPairs($drawId, $ids)
	{
		$dt = $this->_dt;

		// Get the informations

		// Remove the pairs of the draw
		$err = $dt->changePairDraw($drawId, $ids);

		if (isset($err['errMsg']))
		$this->_displayFormConfirm($err['errMsg']);

		$page = new utPage('none');
		$page->close();
		exit();
	}
	// }}}

	// {{{ _displayFormConfirm()
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

		$utpage = new utPage('pairs');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tDelPairs', 'pairs', KID_DELETE);

		// Initialize the field
		$pairsId = kform::getInput("rowsPairs", array());
		if ($err =='' && count($pairsId))
		{
	  foreach($pairsId as $id)
	  $form->addHide("rowsPairs[]", $id);
	  $form->addMsg('msgConfirmDel');
	  $form->addBtn('btnDelete', KAF_SUBMIT);
		}
		else
		{
	  if($err!='')
	  $form->addWng($err);
	  else
	  $form->addWng('msgNeedPairs');
		}
		$form->addBtn('btnCancel');
		$elts = array('btnDelete', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		//Display the page
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormPairs()
	/**
	 * Display a page with the list of the pairs
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayFormPairs()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage('pairs');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tSelectPair', 'pairs', PAIR_ADD_PAIRS);

		$sort = kform::getSort('rowsSelectPairs', 3);
		$drawId = kform::getData();
		$rows = $dt->getOtherPairs($sort, $drawId);
		$form->addHide('drawId', $drawId);

		if (isset($rows['errMsg']))
		{
	  $form->addWng($rows['errMsg']);
	  unset($rows['errMsg']);
		}
		if (count($rows))
		{
	  $form->addRows("rowsSelectPairs", $rows);
		}

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts, 'blkBtn');

		//Display the form
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormPair($infos)
	/**
	 * Display the page for creating a new Pair
	 *
	 * @access private
	 * @param array $Match  info of the Pair
	 * @return void
	 */
	function _displayFormPair($pair="")
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage('pairs');
		$utpage->_page->addAction('onload', array('resize', 480, 350));
		$content =& $utpage->getPage();
		$form =& $content->addForm('tEditPair', 'pairs', KID_UPDATE);

		// Initialize the field
		if ($pair=="")
		{
	  $infos = array('pair_ibfNum'   =>kform::getInput("pairIbfNum"),
			 'pair_natNum'   =>kform::getInput("pairNatNum"),
			 'pair_natRank'  =>kform::getInput("pairNatRank"),
			 'pair_intRank'  =>kform::getInput("pairIntRank"),
			 'pair_id'       =>kform::getInput("pairId"),
			 'pair_cmt'      =>kform::getInput("pairCmt"),
			 'pair_disci'    =>kform::getData(),
			 'pair_wo'       =>kform::getInput("pairWo"),
			 'pair_datewo'   =>kform::getInput("pairDateWo"),
			 'pair_disci'    =>kform::getData(),
			 'pair_status'   =>kform::getInput("pairStatus",
	  WBS_PAIR_MAINDRAW),
			 'pair_state'    =>kform::getInput("pairState",
	  WBS_PAIRSTATE_NOK),
			 'pair_order'    =>kform::getInput("pairOrder", 0),
			 'pair_drawId'    =>kform::getInput("drawId", -1)
	  );
	  $rids = kform::getInput("rids");
		}
		else
		{
	  $id = $pair;
	  if ( is_array($pair))
	  {
	  	if (isset($pair['rids']))
	  	{
	  		$rids = $pair['rids'];
	  		$id = array_shift($rids);
	  	}
	  	else
	  	{
	  		$infos = $dt->getPair($id);
	  		$id = "";
	  	}
	  }
	  if ($id != "") $players = $dt->getPair($id);
		}
		// Display the error if any
		if (isset($infos['errMsg']))
		$form->addWng($infos['errMsg']);

		// Initialize the field of the pair
		$infos = reset($players);
		$form->addHide("drawId", $infos['pair_drawId']);
		$form->addHide("pairId", $infos['pair_id']);
		$form->addHide('pairDisci', $infos['pair_disci']);
		$form->addInfo('echoDisci',   $ut->getLabel($infos['pair_disci']),11);
		$kedit =& $form->addEdit('pairIbfNum',  $infos['pair_ibfNum'],11);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('pairNatNum',  $infos['pair_natNum'],11);
		$kedit->noMandatory();
		$kedit =& $form->addEdit("pairIntRank", $infos['pair_intRank'],11);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('pairNatRank', $infos['pair_natRank'],11);
		$kedit->noMandatory();
		$status[WBS_PAIR_NONE]= $ut->getLabel(WBS_PAIR_NONE);
		for ($i=WBS_PAIR_MAINDRAW; $i<=WBS_PAIR_RESERVE; $i++)
		$status[$i] = $ut->getLabel($i);
		$status[WBS_PAIR_WDN]= $ut->getLabel(WBS_PAIR_WDN);
		$status[WBS_PAIR_INJ]= $ut->getLabel(WBS_PAIR_INJ);
		$form->addCombo('pairStatus', $status, $status[$infos['pair_status']]);
		$kedit =& $form->addEdit("pairCmt", $infos['pair_cmt'], 11);
		$kedit->noMandatory();
		if ($infos['pair_disci'] != WBS_SINGLE)
		{
	  for ($i=WBS_PAIRSTATE_NOK; $i<=WBS_PAIRSTATE_COM; $i++)
	  $states[$i] = $ut->getLabel($i);
	  $form->addCombo('pairState', $states, $states[$infos['pair_state']]);
		}
		else
		$form->addHide('pairState', WBS_PAIRSTATE_REG);
		$kedit =& $form->addEdit('pairOrder',  $infos['pair_order'],11);
		$kedit->noMandatory();
		$kcheck =& $form->addCheck("pairWo", $infos['pair_wo'], $infos['pair_wo']);
		$utd = new utdate();
		$utd->setIsoDate($infos['pair_datewo']);
		$kedit =& $form->addEdit("pairDateWo", $utd->getDate(), 14);
		$kedit->noMandatory();

		$elts = array('echoDisci', 'pairIbfNum', 'pairNatNum',
		    'pairIntRank', 'pairNatRank', 'pairStatus', 'pairState',
		    'pairOrder', 'pairCmt', 'pairWo', 'pairDateWo');
		$form->addBlock('blkAdmin', $elts);

		// Display the players of the pair
		$form->addDiv('break', 'blkNewPage');
		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts, 'blkBtn');

		//Display the form
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _updatePair()
	/**
	 * Update the pair in the database
	 *
	 * @access private
	 * @return void
	 */
	function _updatePair()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Get the informations
		$infos = array('pair_ibfNum'   =>kform::getInput("pairIbfNum"),
		     'pair_natNum'   =>kform::getInput("pairNatNum"),
		     'pair_natRank'  =>kform::getInput("pairNatRank"),
		     'pair_intRank'  =>kform::getInput("pairIntRank"),
		     'pair_id'       =>kform::getInput("pairId"),
		     'pair_wo'       =>kform::getInput("pairWo"),
		     'pair_datewo'   =>kform::getInput("pairDateWo"),
		     'pair_disci'    =>kform::getInput("pairDisci"), 
		     'pair_status'   =>kform::getInput("pairStatus"),
		     'pair_order'    =>kform::getInput("pairOrder"),
		     'pair_state'    =>kform::getInput("pairState"),
		     'pair_cmt'      =>kform::getInput("pairCmt"),
		);

		// Control the informations

		// Add the registration
		$res = $dt->updatePair($infos);
		if (is_array($res))
		$this->_displayFormPair($res);
		$page = new utPage('none');
		$page->close();
		exit();
	}
	// }}}

}
?>
