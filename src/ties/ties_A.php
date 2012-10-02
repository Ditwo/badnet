<?php
/*****************************************************************************
 !   Module     : Ties
 !   File       : $Source: /cvsroot/aotb/badnet/src/ties/ties_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.27 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/06 07:59:33 $
 ******************************************************************************/
require_once "base_A.php";
require_once "matches/matches.inc";
require_once "regi/regi.inc";
require_once "utils/utscore.php";
require_once "utils/utteam.php";
require_once "utils/utpage_A.php";
require_once "utils/utimg.php";
require_once "utils/utevent.php";
require_once "live/live.inc";
require_once "ties.inc";
//require_once "utils/utdebug.php";

/**
 * Module de gestion des rencontres : classe administrateur
 *
 * @author Gerard CANTEGRL <cage@free.fr>
 * @see to follow
 *
 */

class ties_A
{

	var $_ut;
	var $_dt;
	var $_db;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function ties_A()
	{
		$this->_ut = new utils();
		$this->_dt = new tiesBase_A();
		//      $this->_db = new Utdebug(1);
	}

	/**
	 * Start the connexion processus
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function start($page)
	{
		$dt = $this->_dt ;
		$id = kform::getData();
		switch ($page)
		{
			case KID_EDIT:
				$this->_displayFormPenalties($id);
				break;
			case TIES_UPDATE_PENALTIES:
				$this->_updatePenalties();
				break;

			case KID_SELECT:
				$this->_displayFormTie($id);
				break;

			case TIES_SELECT_RESULTS:
				$this->_displayFormResults();
				break;
					
			case TIES_EXPORT_POONA:
				$this->_exportPoona();
				break;
			case TIES_ARCHIVE_POONA:
				$this->_archivePoona();
				break;

			case TIES_VALID_RESULTS:
				$tieId = kform::getData();
				$this->_validResults($tieId, WBS_MATCH_CLOSED);
				break;
			case TIES_INVALID_RESULTS:
				$tieId = kform::getData();
				$this->_validResults($tieId, WBS_MATCH_ENDED);
				break;

			case TIES_PDF_RESULTS:
				require_once "pdf/pdfties.php";
				$pdf = new pdfties();
				$pdf->affichage_result($id);
				break;

			case TIES_PDF_RANKING:
				require_once "pdf/pdfgroups.php";
				$pdf = new pdfGroups();
				$pdf->affichage_class($id);
				break;

			case TIES_PDF_SCHEDULE:
				require_once "pdf/pdfschedu.php";
				$pdf = new pdfschedu();
				$pdf->affichage_schedu($id);
				break;

			case TIES_PDF_TIES:
				require_once "pdf/pdfties.php";
				$pdf = new pdfties();
				$pdf->affichage_resultDiv($id);
				break;

			case TIES_UPDATE_RESULTS:
				$this->_updateResults();
				break;

			case WBS_ACT_TIES:
				$this->_displayFormTies();
				break;
			case TIES_RAZ_VALID:
				$this->_razValid($id);
				break;
			case TIES_RAZ_SAISIE:
				$this->_razSaisie($id);
				break;

			default:
				echo "page $page demandée depuis ties_A<br>";
				exit;
		}
	}

	function _razValid($aTieId)
	{
		$dt = $this->_dt;
		$dt->razValid($aTieId);
		return $this->_displayFormTie($aTieId);
	}

	function _razSaisie($aTieId)
	{
		$dt = $this->_dt;
		$dt->razSaisie($aTieId);
		return $this->_displayFormTie($aTieId);
	}

	/**
	 * Archive le fichier resulatat
	 */
	function _archivePoona()
	{
		require_once "dbf/dbf_A.php";
		$a = new dbf_A();
		list( $tieId,$file) = explode(';', kform::getData());
		$a->_archiveFile(null, $file, $tieId);
		$this->_displayFormTie($tieId);
	}
	//}}}


	// {{{ _exportPoona()
	/**
	* Display a page with the list of the Matchs
	*
	* @access private
	* @param  integer $tieId  Id of the tie
	* @return void
	*/
	function _exportPoona()
	{
		require_once "dbf/dbf_A.php";
		$a = new dbf_A();
		$tieId = kform::getData();
		$event = null;

		$res = $a->_writeResults($event, array($tieId), true);
		if( $res['is_error'] )
		{
			$file = "Erreur lors de l'export.";
		}
		else
		{
			$file = reset($res['files']);
		}

		$this->_displayFormTie($tieId, $file);
	}
	//}}}

	// {{{ _displayFormTies()
	/**
	* Display a page with the list of the ties
	*
	* @access private
	* @return void
	*/
	function _displayFormTies()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$utt =  new utteam();;
		$ute = new Utevent();
		$event = $ute->getEvent(utvars::getEventId());

		$utpage = new utPage_A('ties', true, 'itTies');
		$kcontent =& $utpage->getContentDiv();
		$kcontent->addDiv('choix', 'onglet3');
		$content =& $kcontent->addDiv('register', 'cont3');

		// Adding a menu with the list of divisions
		// (ie the draws in the database)
		$divSel = kform::getData();
		$divs = $utt->getDivs();
		if (isset($divs['errMsg']))
		{
			$content->addWng($divs['errMsg']);
			unset($divs['errMsg']);
		}
		if (count($divs))
		{
			if ( $divSel == '' || !isset($divs[$divSel]))
			{
				$first = each($divs);
				$divSel = $first[0];
			}

			foreach ($divs as $idDiv=>$nameDiv)
			$list[$idDiv] = $nameDiv;

			$kcombo=& $content->addCombo('selectList', $list, $divs[$divSel]);
			$acts[1] = array(KAF_UPLOAD, 'ties', WBS_ACT_TIES);
			$kcombo->setActions($acts);
		}
		else
		{
			$content->addWng('msgNoDivs');
			$utpage->display();
			exit;
		}
		$kdiv =& $content;

		$items = array();
		$items['btnPdfRanking'] = array(KAF_NEWWIN, 'ties', TIES_PDF_RANKING,
		$divSel,600,450);
		$items['btnPdfSchedule'] = array(KAF_NEWWIN, 'ties', TIES_PDF_SCHEDULE,
		$divSel,600,450);
		$kdiv->addMenu('menuPdf', $items, -1, 'classMenuBtn');

		$groups = $utt->getGroups($divSel);

		for ($k=1;$k<5; $k++) $title[$k] = "tiesList$k";
		for ($k=1;$k<19; $k++) $title2[$k] = "rowsRanking$k";
		foreach($groups as $groupId=>$group)
		{
			if ($group['rund_type'] == WBS_TEAM_GROUP ||
			$group['rund_type'] == WBS_TEAM_BACK)
			{
				//$utt->updateTiesResult($groupId);
				$form =& $kdiv->addDiv("tiesDiv$groupId", 'blkGroup');

				//Display the table with tie results
				$title[1] = $group['rund_name'];
				$form->addMsg($title[1], '', 'kTitre');
				$rows = $dt->getTies($groupId);
				$sizes = array();
				$first = array();
				$first[] = array(KOD_BREAK, "title", $group['rund_name'], '', $group['rund_id']);
				if (isset($rows['errMsg'])) $form->addWng($rows['errMsg']);
				else
				{
					$krow =& $form->addRows("tiesList$groupId",
					array_merge($first, $rows));
					$krow->setSort(0);
					$krow->displaySelect(false);
					$krow->setNumber(false);
					$krow->setTitles($title);

					$nbTeams = count($rows);
					for ($i=0; $i< $nbTeams;$i++)
					{
						$sizes[$nbTeams+$i+2] = 0;
						$acts[$i+2] =  array(KAF_UPLOAD, 'ties', KID_SELECT, $nbTeams+$i+2);
						$team= $rows[$i];
						$title[$i+2] = $team[2*$nbTeams+2];
					}
					$krow->setTitles($title);
					$sizes[2*$nbTeams+1] = 0;
					$sizes[2*$nbTeams+2] = 0;
					$sizes[2*$nbTeams+3] = 0;
					$acts[1] = array(KAF_UPLOAD, 'teams', KID_SELECT);

					$krow->setSize($sizes);
					$krow->setActions($acts);

					$img[1] = 2*$nbTeams+3;
					$krow->setLogo($img);
					$acts = array();
					$acts[] = array( 'link' => array(KAF_NEWWIN, 'ties', TIES_PDF_TIES, $group['rund_id'], 600, 450),
				   'icon' => utimg::getIcon(TIES_PDF_TIES),
				   'title' => 'tieResults');
					$krow->setBreakActions($acts);
				}

				// Display the ranking of the group
				$rows = $dt->getRankGroup($groupId);
				if (isset($rows['errMsg'])) $form->addWng($rows['errMsg']);
				else
				{
					$krow =& $form->addRows("rowsTitleRanking$groupId", $rows);
					$krow->setSort(0);

					$sizes = array();
					if($group['rund_tieranktype'] == WBS_CALC_EQUAL)
					{
						$sizes[6] = 0;
						$sizes[8] = 0;
					}
					else $sizes[7] = 0;
						
					if ($event['evnt_scoringsystem'] == WBS_SCORING_1X5) $sizes[16] = '0+';
					else $sizes[19] = '0+';
					$krow->setSize($sizes);

					//$class[3] = "diff";
					//$class[12] = "diff";
					//$class[15] = "diff";
					//$class[18] = "diff";
					//$krow->setColClass($class);

					$img[1] = 20;
					$krow->setLogo($img);
					$krow->setTitles($title2);

					$krow->displaySelect(false);
					$acts = array( 1 => array(KAF_UPLOAD, 'teams', KID_SELECT, 19),
					4 => array(KAF_NEWWIN, 'ties', KID_EDIT, 21, 250, 80));
					$krow->setActions($acts);
				}
			}
			// Groupe KO
			else
			{
				// ajout du bouton PDF
				$actions[0] = array(KAF_UPLOAD, 'teams', KID_SELECT);
				$actions[1] = array(KAF_UPLOAD, 'ties', KID_SELECT);


				$teams = $utt->getTeamsGroup($group['rund_id']);
				$names= array();
				foreach($teams as $team)
				{
					$logo = utimg::getPathFlag($team['asso_logo']);
					$names[] = array('logo' => $logo,
				   'value' => $team['team_name'],
				   'link' => $team['team_id'],
				   't2r_posRound' => $team['t2r_posRound'],
					) ;
				}
				require_once "utils/utko.php";
				$utko = new utKo($names);
				$vals = $utko->getExpandedValues();
				$kdiv2 = & $kdiv->addDiv("divDraw{$group['rund_id']}",
				       'blkGroup');
				$kdiv2->addMsg($group['rund_name'], '', 'kTitre');
				$kdiv2->addBtn("btnPdfAllTies", KAF_NEWWIN, 'ties', TIES_PDF_TIES, $group['rund_id'],600,450);
				$kdraw = & $kdiv2->addDraw("draw{$group['rund_id']}");
				$kdraw->setValues(1, $vals);

				$size = $utko->getSize();
				$teams = $dt->getWinners($group['rund_id']);
				$ties = $dt->getKoTies($group['rund_id']);
				$numCol = 2;
				while( ($size/=2) >= 1)
				{
					$vals = array();
					$firstTie = $size-1;
					for($i=0; $i < $size; $i++)
					{
						if (isset($teams[$firstTie + $i]))
						{
							$team = $teams[$firstTie + $i];
							$vals[] = array('value' => $team['team_name'],
					  'score' => $team['score'],
					  'link' => $team['tie_id']) ;
						}
						else
						{
							$tie = $ties[$firstTie + $i];
							$vals[] = array('value' => $tie['tie_schedule'],
					  'score' => $tie['score'],
					  'link' => $tie['tie_id']) ;
						}
					}
					$kdraw->setValues($numCol++, $vals);
				}
				$kdraw->setActions($actions);
				for ($k=0;$k<$numCol; $k++)
				$title3[$k] = "titleDraw$k";
				for ($k=$numCol-1;$k>0; $k--)
				$title3[$k] = "drawTitle".($numCol-$k-1);
				$kdraw->setTitles($title3);
			}
		}
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormTie()
	/**
	* Display a page with the list of the Matchs
	*
	* @access private
	* @param  integer $tieId  Id of the tie
	* @return void
	*/
	function _displayFormTie($tieId, $aFile=null)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$utpage = new utPage_A('ties', true, 'itTies');
		$content =& $utpage->getContentDiv();
		$content->addDiv('choix', 'onglet3');
		$kdiv =& $content->addDiv('register', 'cont3');
		$div =& $kdiv->addDiv('fTie');

		//--- List of the matches of the selected tie
		if ($tieId != "")
		{
			$teams = $dt->getTeams($tieId);
			$group = $dt->getGroup($tieId);
			// Titre : Equipe 1 - Equipe 2
			if (isset($teams[0][1]) &&
			isset($teams[1][1]))
			{
				$titre = $teams[0][1].'('.$teams[0][3].')'."-".
				$teams[1][1].'('.$teams[1][3].')';
			}
			else $titre = '';
			$kdiv =& $div->addDiv('divInfo', 'blkData');
			$kdiv->addMsg('tDetail', '', 'kTitre');
			$kinfo =& $kdiv->addInfo("draw_name", $group['draw_name']);
			$act[1] = array(KAF_UPLOAD, 'ties', WBS_ACT_TIES, $group['draw_id']);
			$kinfo->setActions($act);
			$kinfo =& $kdiv->addInfo("rund_name", $group['rund_name']);
			$kinfo =& $kdiv->addInfo("tie_step", $group['tie_step']);
			$kinfo =& $kdiv->addInfo("tie_place", $group['tie_place']);
			$kinfo =& $kdiv->addInfo("tie_schedule", $group['tie_schedule']);
			$acties[1] = array(KAF_UPLOAD, 'ties', KID_SELECT);
			$kinfo->setActions($acties);
				
			$kinfo =& $kdiv->addInfo("team1", $teams[0][1]);
			$act[1] = array(KAF_UPLOAD, 'teams', KID_SELECT, $teams[0][0]);
			$kinfo->setActions($act);
			$kinfo =& $kdiv->addInfo("team2", $teams[1][1]);
			$act[1] = array(KAF_UPLOAD, 'teams', KID_SELECT, $teams[1][0]);
			$kinfo->setActions($act);
				
				
			$kdiv =& $div->addDiv('divControl', 'blkData');
			$kdiv->addMsg('Dates', '', 'kTitre');
			$kinfo =& $kdiv->addInfo("Saisie le :", $group['tie_entrydate']);
			$kinfo =& $kdiv->addInfo("Saisie par :", $group['entryname']);
			if( $group['tie_entryid'] > 0 )
			$items['btnDelSaisie'] = array(KAF_UPLOAD, 'ties', TIES_RAZ_SAISIE, $tieId);

			$kinfo =& $kdiv->addInfo("Validation le :", $group['tie_validdate']);
			$kinfo =& $kdiv->addInfo("Validation par :", $group['validname']);
			if( $group['tie_validid'] > 0 )
			$items['btnDelValid'] = array(KAF_UPLOAD, 'ties', TIES_RAZ_VALID, $tieId);
			$kinfo =& $kdiv->addInfo("Controle le :", $group['tie_controldate']);
			$kinfo =& $kdiv->addInfo("Controle par :", $group['controlname']);
			$form =& $div->addForm('fTielist');
			$ties = $dt->getTiesList($group['rund_id'], $tieId, 0);
			if (count($ties))
			{
				$ties['-1'] = '----';
				$kcombo =& $form->addCombo('tieToValid', $ties, -1);
				$kcombo->setActions($acties);
			}

			$ties = $dt->getTiesList($group['rund_id'], $tieId, 1);
			if (count($ties))
			{
				$ties['-1'] = '----';
				$kcombo =& $form->addCombo('tieToComplete', $ties, -1);
				$kcombo->setActions($acties);
			}
			$elt = array('tieToComplete', 'tieToValid');
			$form->addBlock('blkList', $elt);
			$content->addDiv('break4', 'blkNewPage');

			// Get the matchs
			if (isset($teams[0][0]) &&
			isset($teams[1][0])) $matchs = $dt->getMatchs($tieId, $teams[0][0], $teams[1][0]);
			else  $matchs['errMsg'] = 'msgIncompletedTie';
			if (isset($matchs['errMsg'])) $div->addWng($matchs['errMsg']);
			else
			{
				$isSquash = $ut->getParam('issquash', false);
				if ( !$isSquash )
				{
					if ( !empty($aFile) )
					{
						$kedit =& $div->addinfo('Fichier :',    basename($aFile));
						$url = dirname($_SERVER['PHP_SELF']).'/../export/' . basename($aFile);
						$kedit->setUrl($url);
							
						$kpoona =& $div->addinfo('Importer dans :',  'Poona');
						$url = 'http://poona.ffba.org';
						$kpoona->setUrl($url);
							
						$items['btnArchive'] = array(KAF_UPLOAD, 'ties', TIES_ARCHIVE_POONA, "$tieId;$aFile");
					}
					else
					{
						$items['btnPoona'] = array(KAF_UPLOAD, 'ties', TIES_EXPORT_POONA, $tieId);
					}
				}
				if ( !empty($items) ) $div->addMenu('menuExport', $items, -1, 'classMenuBtn');
				$break = array(KOD_BREAK, "title", $titre, '', $tieId);
				array_unshift($matchs, $break);
					
				$krow =& $div->addRows("rowsMatchs", $matchs);

				$titles = array(2=>"A&nbsp;:&nbsp;".$teams[0][1], "B&nbsp;:&nbsp;".$teams[1][1]);
				$krow->setTitles($titles);

				$krow->displaySelect(false);
				$krow->setSort(false);
				$sizes = array(11=> 0, 0, 0, 0, 0);
				$krow->setSize($sizes);

				$img[1]= 13;
				$img[2]= 14;
				$img[3]= 15;
				$krow->setLogo($img);
				$actions[1]=  array(KAF_NEWWIN, 'matches',KID_EDIT,0, 750,450);
				$krow->setActions($actions);
				$acts = array();
				$acts[] = array( 'link' => array(KAF_NEWWIN, 'live',
				LIVE_TIE_PDF,  0, 400, 300),
			       'icon' => utimg::getIcon(LIVE_TIE_PDF),
			       'title' => 'teamDeclaration');
				$acts[] = array( 'link' => array(KAF_NEWWIN, 'ties',
				TIES_SELECT_RESULTS,  0, 750, 500),
			       'icon' => utimg::getIcon(WBS_ACT_EDIT),
			       'title' => 'teamEdit');
				$acts[] = array( 'link' => array(KAF_NEWWIN, 'ties',
				TIES_VALID_RESULTS,  0, 400, 300),
			       'icon' => utimg::getIcon(TIES_VALID_RESULTS),
			       'title' => 'Validation');
				$acts[] = array( 'link' => array(KAF_NEWWIN, 'ties',
				TIES_INVALID_RESULTS,  0, 400, 300),
			       'icon' => utimg::getIcon(TIES_INVALID_RESULTS),
			       'title' => 'DeValidation');
				$acts[] = array( 'link' => array(KAF_NEWWIN, 'ties',
				TIES_PDF_RESULTS, 0, 400, 300),
			       'icon' => utimg::getIcon(WBS_ACT_PRINT),
			       'title' => 'teamResults');
				$krow->setBreakActions($acts);
					
			}
		}

		// Legend
		$kdiv =& $content->addDiv('blkLegende');
		$kdiv->addImg('lgdIncomplete', utimg::getIcon(WBS_MATCH_INCOMPLETE));
		$kdiv->addImg('lgdBusy',       utimg::getIcon(WBS_MATCH_BUSY));
		$kdiv->addImg('lgdRest',       utimg::getIcon(WBS_MATCH_REST));
		$kdiv->addImg('lgdReady',      utimg::getIcon(WBS_MATCH_READY));
		$kdiv->addImg('lgdLive',       utimg::getIcon(WBS_MATCH_LIVE));
		$kdiv->addImg('lgdEnded',      utimg::getIcon(WBS_MATCH_ENDED));
		$kdiv->addImg('lgdClosed',     utimg::getIcon(WBS_MATCH_CLOSED));
		$kdiv->addImg('lgdSend',       utimg::getIcon(WBS_MATCH_SEND));

		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormPenalties()
	/**
	* Display a page with the list of the Matchs
	*
	* @access private
	* @param  integer $tieId  Id of the tie
	* @return void
	*/
	function _displayFormPenalties($data='')
	{
		$dt = $this->_dt;

		$utpage = new utPage('ties');
		$content =& $utpage->getPage();
		$form =& $content->addForm('fPenalties', 'ties', TIES_UPDATE_PENALTIES);

		if (is_numeric($data))
		$t2rId = $data;
		else
		{
			$t2rId = kform::getInput('t2rId');
			$form->addWng($data['errMsg']);
		}
		$infos = $dt->getPenalties($t2rId);
		$penalties = kform::getInput('t2rPenalties', $infos['t2r_penalties']);

		$form->addHide("t2rId", $t2rId);
		$form->addMsg('teamName', $infos['team_name']);
		$form->addEdit('t2rPenalties', $penalties, 2);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayFormResults()
	/**
	* Display a page with the list of the Matchs
	*
	* @access private
	* @param  integer $tieId  Id of the tie
	* @return void
	*/
	function _displayFormResults($err='')
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;
		$uts = new utscore();

		// Creating a new page
		$utpage = new utPage('ties');
		$content =& $utpage->getPage();
		$utpage->_page->addJavaFile('ties/ties.js');
		$form =& $content->addForm('ftieres', 'ties', TIES_UPDATE_RESULTS);
		$form->setTitle('tTieResult');

		// Obtaining informations
		if ($err != '')
		{
			$tieId = kform::getInput('tieId');
			$form->addWng($err);
		}
		else
		$tieId = kform::getData();
		$teams = $dt->getTeams($tieId);
		$teamL = $teams[0][0];
		$teamR = $teams[1][0];
		$matchs = $dt->getMatchsForRes($tieId, $teamL, $teamR);

		// Displaying information
		if (isset($infos['errMsg']))
		{
			$form->addWng($infos['errMsg']);
			unset($infos['errMsg']);
		}

		$i = 0;
		$form->addHide("tieId", $tieId);
		$form->addMsg("nameL", $teams[0][1].'('.$teams[0][3].')');
		$form->addMsg("nameR", $teams[1][1].'('.$teams[1][3].')');

		$form->addEdit('penaltiesL', $teams[0][4], 2);
		$form->addEdit('penaltiesR', $teams[1][4], 2);


		$kbtn =& $form->addBtn('btnAddPlayerL', KAF_NEWWIN, 'regi',
		REGI_SEARCH_TEAM, $teams[0][0]);

		$kbtn =& $form->addBtn('btnAddPlayerR', KAF_NEWWIN, 'regi',
		REGI_SEARCH_TEAM, $teams[1][0]);

		$kcheck =& $form->addCheck('woL', $teams[0][5] == WBS_TIE_LOOSEWO, '1');
		$kcheck->setText('Wo');
		$kcheck =& $form->addCheck('woR', $teams[1][5] == WBS_TIE_LOOSEWO, '1');
		$kcheck->setText('Wo');

		$elts = array('nameL', 'penaltiesL', 'woL', 'btnAddPlayerL');
		$form->addBlock("blkTeamL", $elts);

		$elts = array('nameR', 'penaltiesR', 'woR', 'btnAddPlayerR');
		$form->addBlock("blkTeamR", $elts);

		$menL   = $dt->getRegiPlayers($teamL, WBS_MS);
		$menR   = $dt->getRegiPlayers($teamR, WBS_MS);
		$womenL = $dt->getRegiPlayers($teamL, WBS_LS);
		$womenR = $dt->getRegiPlayers($teamR, WBS_LS);
		$menDL   = $dt->getRegiPairsDouble($teamL, WBS_MD);
		$menDR   = $dt->getRegiPairsDouble($teamR, WBS_MD);
		$womenDL = $dt->getRegiPairsDouble($teamL, WBS_LD);
		$womenDR = $dt->getRegiPairsDouble($teamR, WBS_LD);
		$mixedL  = $dt->getRegiPairsMixed($teamL);
		$mixedR  = $dt->getRegiPairsMixed($teamR);

		$tabIndex = 1;

		foreach($matchs as $match)
		{
			$bigelts =array("mtchNum$i", "blkPair$i", "blkAb$i");
			$form->addHide("mtchId$i", $match['mtch_id']);

			switch($match['mtch_discipline'])
			{
				case WBS_AS:
					$playersL = array_merge($menL, $womenL);
					$playersR = array_merge($menR, $womenR);
					break;
				case WBS_MS:
					$playersL = $menL;
					$playersR = $menR;
					break;
				case WBS_LS:
					$playersL = $womenL;
					$playersR = $womenR;
					break;
				case WBS_MD:
					$playersL = $menDL;
					$playersR = $menDR;
					break;
				case WBS_LD:
					$playersL = $womenDL;
					$playersR = $womenDR;
					break;
				case WBS_MX:
					$playersL = $mixedL;
					$playersR = $mixedR;
					break;
			}

			$player = kform::getInput("mtchPair0$i");
			if ($player != '' && isset($playersL[$player])) $sel=$playersL[$player];
			else
			{
				$indx = $match["mtch_regiId00"].";".$match["mtch_regiId01"];
				$indxi = $match["mtch_regiId01"].";".$match["mtch_regiId00"];
				if (isset($playersL[$indx])) $sel=$playersL[$indx];
				else if (isset($playersL[$indxi]))  $sel=$playersL[$indxi];
				else  $sel = '----';
			}
			$kcombo =& $form->addCombo("mtchPair0$i", $playersL, $sel);
			$kcombo->setTabIndex($tabIndex++);
			$kcombo->noMandatory();

			$player = kform::getInput("mtchPair1$i");
			if ($player != ''&& isset($playersR[$player]))
			$sel=$playersR[$player];
			else
			{
				$indx = $match["mtch_regiId10"].";".$match["mtch_regiId11"];
				$indxi = $match["mtch_regiId11"].";".$match["mtch_regiId10"];
				if (isset($playersR[$indx])) $sel=$playersR[$indx];
				else if (isset($playersR[$indxi])) $sel=$playersR[$indxi];
				else $sel = '----';
			}
			$kcombo =& $form->addCombo("mtchPair1$i", $playersR, $sel);
			$kcombo->setTabIndex($tabIndex++);
			$kcombo->noMandatory();

			$kcheck =& $form->addCheck("mtchAb0$i",
			kform::getInput("mtchAb0$i",
			$match['mtch_result0']== WBS_RES_LOOSEAB));
			$kcheck->setText('Ab');
			$kcheck->setTabIndex($tabIndex++);
			$kcheck =& $form->addCheck("mtchAb1$i",
			kform::getInput("mtchAb1$i",
			$match['mtch_result1']== WBS_RES_LOOSEAB));
			$kcheck->setText('Ab');
			$kcheck->setTabIndex($tabIndex++);

			$uts->reset();
			$uts->setScore($match['mtch_score']);
			$gamesW = array();
			$gamesL = array();
			$ute = new utevent();
			$event = $ute->getEvent(utvars::getEventId());
			$nbSet = 3;
			if ($event['evnt_scoringsystem'] == WBS_SCORING_NONE ||
			$event['evnt_scoringsystem'] == WBS_SCORING_5X7 ||
			$event['evnt_scoringsystem'] == WBS_SCORING_5X11
			)
			$nbSet = 5;

			$gamesW = $uts->getWinGames();
			$gamesL = $uts->getLoosGames();
			$nbGame = count($gamesW);
			for ($j=$nbGame; $j<$nbSet; $j++)
			{
				$gamesW[$j] = '';
				$gamesL[$j] = '';
			}
			$infos["mtch_wo"] = $uts->isWO();
			$infos["mtch_abort"] = $uts->isAbort();
			$eltssco = array();
			for ($j=0; $j<$nbSet; $j++)
			{
				if ($match['mtch_result0']== WBS_RES_WIN ||
				$match['mtch_result0']== WBS_RES_WINAB ||
				$match['mtch_result0']== WBS_RES_WINWO)
				{
					$infos["Sc0$j$i"] = kform::getInput("mtchSc0$j$i", $gamesW[$j]);
					$infos["Sc1$j$i"] = kform::getInput("mtchSc1$j$i", $gamesL[$j]);
				}
				else if ($match['mtch_result0'] != WBS_RES_NOPLAY)
				{
					$infos["Sc0$j$i"] = kform::getInput("mtchSc0$j$i", $gamesL[$j]);
					$infos["Sc1$j$i"] = kform::getInput("mtchSc1$j$i", $gamesW[$j]);
				}
				else
				{
					$infos["Sc0$j$i"] = kform::getInput("mtchSc0$j$i", '');
					$infos["Sc1$j$i"] = kform::getInput("mtchSc1$j$i", '');
				}
				$kedit =& $form->addEdit("mtchSc0$j$i", $infos["Sc0$j$i"], 2);
				$kedit->setTabIndex($tabIndex++);
				$kedit->noMandatory();
				if ($event['evnt_scoringsystem'] != WBS_SCORING_NONE)
				{
					$kedit->setMaxLength(2);
					$kedit->autoNextField("mtchSc1$j$i");
				}
				$kedit =& $form->addEdit("mtchSc1$j$i", $infos["Sc1$j$i"], 2);
				$kedit->setTabIndex($tabIndex++);
				$kedit->noMandatory();
				if ($event['evnt_scoringsystem'] != WBS_SCORING_NONE)
				{
					$kedit->setMaxLength(2);
					if ($j <$nbSet)
					$nextField = "mtchSc0".($j+1).$i;
					else
					$nextField = "mtchPair0".($i+1);
					$kedit->autoNextField($nextField);
				}
				$elts =array("mtchSc0$j$i", "mtchSc1$j$i");
				$form->addBlock("blkSc$j$i", $elts, 'score');
				$bigelts[] = "blkSc$j$i";
			}

			// Titre du match
			$form->addWng("mtchNum$i", $match['mtch_num']);
			$elts =array("mtchPair0$i", "mtchPair1$i");
			$form->addBlock("blkPair$i", $elts);
			$elts =array("mtchAb0$i","mtchAb1$i");
			$form->addBlock("blkAb$i", $elts, 'ab');

			$form->addBlock("blkMatch$i", $bigelts, 'match');

			$i++;
		}

		$kbtn =& $form->addBtn('btnRaz', 'raz', $i, $nbSet);
		$kbtn->setTabIndex($tabIndex++);
		$kbtn =& $form->addBtn('btnRegister', KAF_SUBMIT);
		$kbtn->setTabIndex($tabIndex++);
		$kbtn =& $form->addBtn('btnCancel');
		$kbtn->setTabIndex($tabIndex++);
		$elts = array('btnRaz', 'btnAddPlayer1',
		    'btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);

		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _updatePenalties()
	/**
	* Update the penalties form a team in a group
	*
	* @access private
	* @return void
	*/
	function _updatePenalties()
	{
		$dt =& $this->_dt;

		$t2rId = kform::getInput('t2rId');
		$penalties = kform::getInput('t2rPenalties', 0);
		$res = $dt->updatePenalties($t2rId, $penalties);
		if (isset($res['errMsg']))
		{
			$this->_displayFormPenalties($res);
		}

		//Close the windows
		$page = new utPage('none');
		$page->close();
		exit;

	}
	// }}}

	// {{{ _validResults()
	/**
	* Valide les resulats d'une rencontre
	*
	* @access private
	* @return void
	*/
	function _validResults($tieId, $status= WBS_MATCH_ENDED)
	{
		$dt =& $this->_dt;
		$res = $dt->updateMatchStatus($tieId, $status);
		//Close the windows
		$page = new utPage('none');
		$page->close();
		exit;

	}
	// }}}

	// {{{ _updateResults()
	/**
	* Update the match in the database
	*
	* @access private
	* @return void
	*/
	function _updateResults()
	{
		$ut =& $this->_ut;
		$dt =& $this->_dt;
		$uts = new utscore();
		$ute = new utevent();

		$tieId = kform::getInput('tieId');
		$teams = $dt->getTeams($tieId);
		$teamL = $teams[0][0];
		$teamR = $teams[1][0];
		$groupId = $teams[0][2];
		$matchs = $dt->getMatchsForRes($tieId, $teamL, $teamR);
		$nbMatchs = count($matchs);
		$ute = new utevent();
		$event = $ute->getEvent(utvars::getEventId());
		switch($event['evnt_scoringsystem'])
		{
			case WBS_SCORING_5X7:
			case WBS_SCORING_5X11:
				$nbSet = 5;
				break;
			case WBS_SCORING_3X15:
			case WBS_SCORING_3X21:
			case WBS_SCORING_3X11:
				$nbSet = 3;
				break;
			default:
				$nbSet=1;
		}

		$woL = kform::getInput("woL", NULL);
		$woR = kform::getInput("woR", NULL);
		//$woId = $dt->getWoId($teamL, $teamR);
		require_once "matches/base_A.php";
		$dtm = new matchBase_A();

		for ($i=0; $i<$nbMatchs; $i++)
		{
			// Get the informations
			$data = array('mtch_id'     =>kform::getInput("mtchId$i"),
			"mtch_status"  =>$ute->getMatchStatus());
			for($j=0; $j<$nbSet;$j++)
			{
				$data["mtch_sc0$j"] = kform::getInput("mtchSc0$j$i");
				$data["mtch_sc1$j"] = kform::getInput("mtchSc1$j$i");
			}
			list($data["mtch_regiId00"], $data["mtch_regiId01"]) =
			explode(";", kform::getInput("mtchPair0$i"));
			list($data["mtch_regiId10"], $data["mtch_regiId11"]) =
			explode(";", kform::getInput("mtchPair1$i"));

			$match = $dt->getMatch($data['mtch_id']);
			$infos= array_merge($match, $data);
			$infos['mtch_teamId0'] = $teamL;
			$infos['mtch_teamId1'] = $teamR;

			// Look after score
			/* If WO, force the score to WO */
			$score = "";
			if ( ($dt->isRegiWO($infos['mtch_regiId00']) ||
			$dt->isRegiWO($infos['mtch_regiId01']) ||
			!is_null($woL))  &&
			($dt->isRegiWO($infos['mtch_regiId10']) ||
			$dt->isRegiWO($infos['mtch_regiId11']) ||
			!is_null($woR))
			)
			{
				$score = "";
				$infos["mtch_winId0"] = $infos["mtch_regiId10"];
				$infos["mtch_winId1"] = $infos["mtch_regiId11"];
				$infos["mtch_loosId0"] = $infos["mtch_regiId00"];
				$infos["mtch_loosId1"] = $infos["mtch_regiId01"];
			}
			else if ( $dt->isRegiWO($infos['mtch_regiId00']) ||
			$dt->isRegiWO($infos['mtch_regiId01']) ||
			!is_null($woL)
			)
			{
				if( $event['evnt_scoringsystem'] == WBS_SCORING_5X7 )
				$score = "7-0 7-0 7-0 WO";
				else if( $event['evnt_scoringsystem'] == WBS_SCORING_3X15 )
				if($infos["mtch_discipline"] != WBS_LS)
				$score = "15-0 15-0 WO";
				else
				$score = "11-0 11-0 WO";
				else if( $event['evnt_scoringsystem'] == WBS_SCORING_3X21 )
				$score = "21-0 21-0 WO";
				else
				$score = "WO";

				$infos["mtch_winId0"] = $infos["mtch_regiId10"];
				$infos["mtch_winId1"] = $infos["mtch_regiId11"];
				$infos["mtch_loosId0"] = $infos["mtch_regiId00"];
				$infos["mtch_loosId1"] = $infos["mtch_regiId01"];
			}
			else if ($dt->isRegiWO($infos['mtch_regiId10']) ||
			$dt->isRegiWO($infos['mtch_regiId11']) ||
			!is_null($woR))
			{
				if( $event['evnt_scoringsystem'] == WBS_SCORING_5X7 )
				$score = "7-0 7-0 7-0 WO";
				else if( $event['evnt_scoringsystem'] == WBS_SCORING_3X15 )
				if($infos["mtch_discipline"] != WBS_LS)
				$score = "15-0 15-0 WO";
				else
				$score = "11-0 11-0 WO";
				else if( $event['evnt_scoringsystem'] == WBS_SCORING_3X21 )
				$score = "21-0 21-0 WO";
				else
				$score = "WO";
				$infos["mtch_winId0"] = $infos["mtch_regiId00"];
				$infos["mtch_winId1"] = $infos["mtch_regiId01"];
				$infos["mtch_loosId0"] = $infos["mtch_regiId10"];
				$infos["mtch_loosId1"] = $infos["mtch_regiId11"];
			}
			else
			{
				$score = "";
				$glue  = "";
				for ($j=0; $j<$nbSet; $j++)
				{
					$sc0 = kform::getInput("mtchSc0$j$i");
					$sc1 = kform::getInput("mtchSc1$j$i");
					if ($sc0 != "" &&  $sc1 != "")
					{
						if (kform::getInput("mtchAb0$i") != '')
						$score .= $glue.$sc1.'-'.$sc0;
						else
						$score .= $glue.$sc0.'-'.$sc1;
						$glue  = " ";
					}
				}

				if (kform::getInput("mtchAb0$i") != '')
				{
					$infos["mtch_winId0"] = $infos["mtch_regiId10"];
					$infos["mtch_winId1"] = $infos["mtch_regiId11"];
					$infos["mtch_loosId0"] = $infos["mtch_regiId00"];
					$infos["mtch_loosId1"] = $infos["mtch_regiId01"];
					$score .= " Ab";
				}
				else if (kform::getInput("mtchAb1$i") != '')
				{
					$infos["mtch_winId0"] = $infos["mtch_regiId00"];
					$infos["mtch_winId1"] = $infos["mtch_regiId01"];
					$infos["mtch_loosId0"] = $infos["mtch_regiId10"];
					$infos["mtch_loosId1"] = $infos["mtch_regiId11"];
					$score .= " Ab";
				}
				else
				{
					$res = $uts->setScore($score);
					$infos['mtch_score'] = $uts->getWinScore();
					if ($infos["mtch_score"] == $score)
					{
						$infos["mtch_winId0"] = $infos["mtch_regiId00"];
						$infos["mtch_winId1"] = $infos["mtch_regiId01"];
						$infos["mtch_loosId0"] = $infos["mtch_regiId10"];
						$infos["mtch_loosId1"] = $infos["mtch_regiId11"];
					}
					else
					{
						$infos["mtch_winId0"] = $infos["mtch_regiId10"];
						$infos["mtch_winId1"] = $infos["mtch_regiId11"];
						$infos["mtch_loosId0"] = $infos["mtch_regiId00"];
						$infos["mtch_loosId1"] = $infos["mtch_regiId01"];
					}
				}
			}
			$infos['mtch_score'] = $score;

			/* We can't have the same player in a team */
			if (($infos["mtch_winId0"] == $infos["mtch_winId1"] &&
			!$dt->isRegiWO($infos["mtch_winId0"]) &&
			$infos["mtch_winId0"] != '' &&
			$infos["mtch_winId0"]>=0) ||
			($infos["mtch_loosId0"] == $infos["mtch_loosId1"] &&
			!$dt->isRegiWO($infos["mtch_loosId0"]) &&
			$infos["mtch_loosId0"] != '' &&
			$infos["mtch_loosId0"]>=0) )
			{
				$err = "msgNotSamePlayer";
				$this->_displayFormResults($err);
			}

			// What is the status of the match ?
			if ($score == '')
			{
				$infos['mtch_status'] = WBS_MATCH_READY;
				if ($infos["mtch_winId0"] == -10 ||
				$infos["mtch_loosId0"] == -10 ||
				($infos["mtch_discipline"] > WBS_LS &&
				($infos["mtch_winId1"] == -10 ||
				$infos["mtch_loosId1"] == -10)))
				$infos['mtch_status'] = WBS_MATCH_INCOMPLETE;
			}

			//$infos['mtch_end'] = NULL;
			//$infos['mtch_begin'] = NULL;

			// Add the registration
			$res = $dtm->updateMatchTeam($infos);
			//$res = $dt->updateMatchTeam($infos);
			if (is_array($res))
			{
				$err = $res["errMsg"];
				$this->_displayFormResults($err);
			}
		}


		// Now updating result of the tie and rank of the group
		$utt = new utteam();
		$penaltiesL = kform::getInput("penaltiesL", NULL);
		$penaltiesR = kform::getInput("penaltiesR", NULL);
		$woL = kform::getInput("woL", NULL);
		$woR = kform::getInput("woR", NULL);
		$res = $utt->updateTeamResult($teamL, $tieId, $penaltiesL, $penaltiesR, $woL, $woR);
		if (isset($res['errMsg'])) $this->_displayFormResults($res['errMsg']);

		$res = $utt->updateTeamResult($teamR, $tieId, $penaltiesR, $penaltiesL, $woR, $woL);
		if (isset($res['errMsg'])) $this->_displayFormResults($res['errMsg']);

		$res = $utt->updateGroupRank($groupId);
		if (isset($res['errMsg'])) $this->_displayFormResults($res['errMsg']);

		// Updating the next tie (for a KO draw)
		$res = $dt->updateKo($tieId);
		if (is_array($res))
		{
			$err = $res["errMsg"];
			$this->_displayFormResults($err);
		}

		// Send the results to subscribers
		require_once "subscript/subscript_V.php";
		$subscript = new subscript_V();
		$subscript->sendTieResult($tieId);

		//Close the windows
		$page = new utPage('none');
		$page->close();
		exit;
	}
	// }}}

}
?>
