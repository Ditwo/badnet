<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Div.inc';
require_once 'Object/Otie.inc';
require_once 'Object/Omatch.inc';
require_once 'Object/Odiv.inc';
require_once 'Object/Oround.inc';
require_once 'Object/Ogroup.inc';

/**
 * Module de gestion des equipes groupes et divisions
 */
class Div
{

	public function __construct()
	{
		$userId = Bn::getValue('user_id');
		if (empty($userId) ) return BNETADM_LOGOUT;
		$controller = Bn::getController();
		$controller->addAction(BDIV_PAGE_GROUPS,         $this, 'pageGroups');
		$controller->addAction(BDIV_FILL_GROUPS,         $this, 'fillGroups');
		$controller->addAction(BDIV_PAGE_GROUP,          $this, 'pageGroup');
		$controller->addAction(BDIV_PAGE_GROUPDEF,       $this, 'pageGroupdef');
		$controller->addAction(BDIV_UPDATE_GROUPDEF,     $this, 'updateGroupdef');
		$controller->addAction(BDIV_PAGE_TIESDEF,        $this, 'pageTiesdef');
		$controller->addAction(BDIV_UPDATE_TIESDEF,      $this, 'updateTiesdef');
		$controller->addAction(BDIV_PAGE_MATCHSDEF,      $this, 'pageMatchsdef');
		$controller->addAction(BDIV_UPDATE_MATCHSDEF,    $this, 'updateMatchsdef');
		$controller->addAction(BDIV_PAGE_COMPO,          $this, 'pageCompo');
		$controller->addAction(BDIV_UPDATE_COMPO,        $this, 'updateCompo');
		$controller->addAction(BDIV_PAGE_CALENDAR,       $this, 'pageCalendar');
		$controller->addAction(BDIV_UPDATE_CALENDAR,     $this, 'updateCalendar');
		$controller->addAction(BDIV_PAGE_COMPO,     	 $this, 'pageCompo');
		$controller->addAction(BDIV_UPDATE_COMPO,     	 $this, 'updateCompo');
		$controller->addAction(BDIV_PAGE_ADD_GROUP,      $this, 'pageAddGroup');
		$controller->addAction(BDIV_ADD_GROUP,     	 	 $this, 'addGroup');
		$controller->addAction(BDIV_PAGE_DELETE_GROUP,   $this, 'pageDeleteGroup');
		$controller->addAction(BDIV_DELETE_GROUP,     	 $this, 'deleteGroup');

	}

	/**
	 * Supprime le groupe selectionne
	 */
	public function deleteGroup()
	{
		// Controle du profil

		// Supprimer le groupe
		$roundId = Bn::getValue('roundId');
		$oGroup = new Ogroup($roundId);
		$oDraw = new Odraw($oGroup->getVal('drawid'));
		$oGroup->delete();

		// Supprimer la division si elle est vide
		$roundIds = $oDraw->getRounds();
		if (empty($roundIds)) $oDraw->delete();
		
		// Affichage du message de confirmation
		$body = new Body();
		$body->addP('', LOC_MSG_GROUP_DELETED);
		$b= $body->addButtonCancel('btnClose', LOC_BTN_CLOSE);
		$body->addJQReady('searchGroups();');
		$body->display();
		return false;
	}

	/**
	 * Confirmation de supression d'un groupe
	 */
	public function pageDeleteGroup()
	{
		// Controle du profil

		$roundId = Bn::getValue('roundId');
		$oGroup = new Ogroup($roundId);
		$body = new Body();
		$form = $body->addForm('frmDelete', BDIV_DELETE_GROUP, 'targetDlg');
		$form->addP('', $oGroup->getVal('name'), 'bn-title-4');
		$form->addHidden('roundId', $roundId);
		$form->addError(LOC_MSG_CONFIRM_DELETE_GROUP);

		$div = $form->addDiv('', 'bn-div-btn');
		$div ->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$div ->addButtonValid('btnValid', LOC_BTN_CONFIRM);
		$body->display();
		return false;
	}


	/**
	 * Ajout d'un nouveau groupe
	 *
	 */
	public function addGroup()
	{
		$eventId = Bn::getValue('event_id', -1);
		$drawId = Bn::getValue('drawid', -1);

		if ($drawId == -1)
		{
			$oDraw = new Odraw();
			$oDraw->setVal('eventid', $eventId);
			$oDraw->setVal('name', Bn::getValue('division'));
			$oDraw->setVal('stamp', Bn::getValue('divstamp'));
			$oDraw->setVal('type', ODIV_TYPE_IC);
			$oDraw->create();
			$drawId = $oDraw->getVal('id', -1);
		}
		else $oDraw = new Odraw($drawId);

		$formula = Bn::getValue('formula');
		switch($formula)
		{
			case OROUND_FORMULA_GROUP:
				$oGroup = new Ogroup();
				$oGroup->setVal('type', OROUND_TYPEIC_GROUP);
				break;
			case OROUND_FORMULA_AR:
				$oGroup = new Ogroup();
				$oGroup->setVal('type', OROUND_TYPEIC_AR);
				break;
			case OROUND_FORMULA_KO:
				$oGroup = new Oko();
				$oGroup->setVal('type', OROUND_TYPEIC_KO);
				break;
			case OROUND_FORMULA_PLATEAU:
				$oGroup = new Oko();
				$oGroup->setVal('type', OROUND_TYPE_MAINDRAW);
				break;
			case OROUND_FORMULA_RONDE:
				//@todo gerer la ronde suisse
				$oGroup = new Oko();
				$oGroup->setVal('type', OROUND_TYPE_RONDE);
				break;
		}
		//Nom du groupe. Il ne doit pas y en avoir deux identiques pour un tableau
		$phases = $oDraw->getPhases();
		$phase = 'Principal';
		$i = 1;
		while (in_array($phase, $phases) ) $phase = 'Principal_'.$i++; 

		$oGroup->setVal('name', Bn::getValue('roundname'));
		$oGroup->setVal('stamp', Bn::getValue('stamp'));
		$oGroup->setVal('group', $phase);
		$oGroup->setVal('drawid', $drawId);
		$oGroup->setVal('entries', Bn::getValue('entries'));
		$oGroup->setVal('ranktype', Bn::getValue('ranktype'));
		$oGroup->setVal('rge', Bn::getValue('rge', 1));

		// Definition des rencontres
		$oGroup->setVal('nbms', Bn::getValue('nbms', 2));
		$oGroup->setVal('nbws', Bn::getValue('nbws', 1));
		$oGroup->setVal('nbas', Bn::getValue('nbas', 0));
		$oGroup->setVal('nbmd', Bn::getValue('nbmd', 1));
		$oGroup->setVal('nbwd', Bn::getValue('nbwd', 1));
		$oGroup->setVal('nbad', Bn::getValue('nbad', 0));
		$oGroup->setVal('nbxd', Bn::getValue('nbxd', 2));

		$oGroup->setVal('matchwin', Bn::getValue('matchwin', 1));
		$oGroup->setVal('matchloose', Bn::getValue('matchloose', 0));
		$oGroup->setVal('matchwo', Bn::getValue('matchwo', -1));
		$oGroup->setVal('matchrtd', Bn::getValue('matchrtd', 0));

		// Points des rencontres
		$oGroup->setVal('tiewin', Bn::getValue('tiewin', 2));
		$oGroup->setVal('tieequalplus', Bn::getValue('tieequalplus', 0));
		$oGroup->setVal('tieequal', Bn::getValue('tieequal', 1));
		$oGroup->setVal('tieequalminus', Bn::getValue('tieequalminus', 0));
		$oGroup->setVal('tieloose', Bn::getValue('tieloose', 0));
		$oGroup->setVal('tiewo', Bn::getValue('tiewo', -1));
		$oGroup->setVal('tieranktype', Bn::getValue('tieranktype', OTIE_CALC_RANK));
		$oGroup->setVal('tiematchdecisif', Bn::getValue('tiematchdecisif', OMATCH_DISCI_MS));
		$oGroup->setVal('tiematchnum', Bn::getValue('tiematchnum', 1));
		$roundId = $oGroup->save();

		// Mettre a jour les plateaux
		if ($formula == OROUND_FORMULA_PLATEAU) $oGroup->savePlateau();
		else $oGroup->deletePlateau();

		// Mettre a jour la troisième place
		if ($formula == OROUND_FORMULA_KO && Bn::getValue('third', 0)) $oGroup->saveThird();
		else $oGroup->deleteThird();

		$res = array('bnAction'  => BDIV_PAGE_GROUP,
					 	'ajax' => 1,
						'roundId' => $roundId
		);
		echo Bn::toJson($res);
		return false;
	}


	/**
	 * Page pour l'ajout d'un nouveau groupe
	 *
	 */
	public function pageAddGroup()
	{
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$oGroup = new Ogroup(); // pour inclusion des constantes et chaine de caractères
		$body = new Body();

		// Titres
		$body->addP('', LOC_ITEM_GROUPDEF, 'bn-title-4');

		$form = $body->addForm('frmAddGroup', BDIV_ADD_GROUP, 'targetBody');
		$form->getForm()->addMetadata('success', "addGroup");
		$form->getForm()->addMetadata('dataType', "'json'");

		$div = $form->addDiv('divname', 'bn-div-left');
		$d = $div->addDiv('', 'bn-div-line');
		$edit = $d->addEdit('roundname', LOC_LABEL_ROUND_NAME, '', 30);
		$edit = $d->addEdit('stamp', LOC_LABEL_ROUND_STAMP, '', 10);
		$edit = $d->addEdit('entries', LOC_LABEL_TEAM_NUMBER, '', 2);
		
		$d = $div->addDiv('', 'bn-div-line bn-div-clear');
		$edit = $d->addEdit('division', LOC_LABEL_DIVISION_NEW, '', 50);
		$edit->noMandatory();
		$edit = $d->addEdit('divstamp', LOC_LABEL_ROUND_STAMP, '', 10);
		$edit->noMandatory();
		
		$divs = array(-1 => '----') + Odiv::getLov($eventId);
		$slt = $d->addSelect('drawid', LOC_LABEL_DIVISION_OR);
		$slt->addOptions($divs, -1);

		$div = $form->addDiv('divstamp', 'bn-div-left');


		$div = $form->addDiv('divFormulaDef', 'bn-div-clear bn-div-left');
		$div->addP('', 'Formule', 'bn-title-5');
		$d = $div->addDiv('', 'divLol');

		//@todo Ajouter la saisie de la phase
		//@todo ajouter l'option troisième place pour le Ko seul
		$dl = $d->addDiv('', 'bn-div-line bn-div-clear');
		$dl->addRadio('formula', 'formulagroup', 'LABEL_'.OROUND_FORMULA_GROUP, OROUND_FORMULA_GROUP, true);

		$dl = $d->addDiv('', 'bn-div-line bn-div-clear');
		$dl->addRadio('formula', 'formulaar',    'LABEL_'.OROUND_FORMULA_AR, OROUND_FORMULA_AR, false);

		$dl = $d->addDiv('', 'bn-div-line bn-div-clear');
		$dl->addRadio('formula', 'formulako',    'LABEL_'.OROUND_FORMULA_KO, OROUND_FORMULA_KO, false);
		$dl->addCheckBox('third', LOC_LABEL_THIRD, false);

		$dl = $d->addDiv('', 'bn-div-line bn-div-clear');
		$dl->addRadio('formula', 'formulaplateau', 'LABEL_'.OROUND_FORMULA_PLATEAU, OROUND_FORMULA_PLATEAU, false);

		$dl = $d->addDiv('', 'bn-div-line bn-div-clear bn-div-auto');
		$dl->addRadio('formula', 'formularonde', 'LABEL_'.OROUND_FORMULA_RONDE, OROUND_FORMULA_RONDE, false);
		$dl->addEdit('tour', LOC_LABEL_NB_ROUND, 1, 2);
		$d->addBreak();

		$div = $form->addDiv('divRanktype', 'bn-div-left');
		$div->addP('', 'Calcul du classement', 'bn-title-5');
		$d = $div->addDiv('', 'divLol');
		$d->addRadio('ranktype', 'rankcg', 'LABEL_'.OGROUP_RANK_CG, OGROUP_RANK_CG, true);
		$d->addRadio('ranktype', 'rankrp', 'LABEL_'.OGROUP_RANK_RP, OGROUP_RANK_RP, false);
		$d->addBreak();

		$d = $form->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CANCEL);
		$d->addButtonValid('btnValid', LOC_BTN_UPDATE);


		$body->display();
		return false;

	}


	/**
	 * Enregistrer la composition du groupe
	 *
	 * @return unknown
	 */
	public function updateCompo()
	{
		$roundId = Bn::getValue('roundId');
		$oRound = new Oround($roundId);
		$teamIds = Bn::getValue('teamIds');

		// Type de round a traiter
		$type = $oRound->getVal('type');
		switch($type)
		{
			case OROUND_TYPEIC_GROUP:
			case OROUND_TYPEIC_AR:
			case OROUND_TYPE_GROUP:
			case OROUND_TYPE_AR:
				$oGroup = new Ogroup($roundId);
				$updateRanking = true;
				break;
			case OROUND_TYPEIC_KO:
			case OROUND_TYPE_QUALIF:
			case OROUND_TYPE_MAINDRAW:
			case OROUND_TYPE_THIRD:
			case OROUND_TYPE_CONSOL:
			case OROUND_TYPE_PLATEAU:
			case OROUND_TYPE_RONDE:
			case OROUND_TYPE_PROGRES:
				$oGroup = new Oko($roundId);
				$updateRanking = false;
				break;
		}

		// Mettre  ajour les équipes
		$oGroup->updateTeams($teamIds);

		// Mettre a jour le classement
		if ($updateRanking) $oGroup->updateTeamRanking();

		// Message de fin
		// Preparer les champs de saisie
		$body = new Body();
		$body->addP('', LOC_LABEL_DIV_REGISTERED);
		$d = $body->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CLOSE);

		// Envoi au navigateur
		$res = array('content' => $body->toHtml(),
					'title' => LOC_ITEM_COMPO);
		echo Bn::toJson($res);
		return false;
	}


	/**
	 * Affichage de la page de modification d'un groupe
	 */
	public function pageCompo()
	{
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$roundId = Bn::getValue('roundId');
		$oRound = new Oround($roundId);
		$groupTeamIds = $oRound->getTeams(true);
		$eventTeamIds = $oEvent->getTeams('poids DESC');
		$body = new Body();

		// Titres
		$body->addP('', LOC_ITEM_COMPO, 'bn-title-4');
		$body->addP('', LOC_P_COMPO, 'bn-p-info');
		$size = $oRound->getVal('entries');
		$body->addHidden('teamsize', $size);

		// Equipes du tournoi
		$div = $body->addDiv('diveventteam', 'bn-div-left');
		$t = $div->addP('', LOC_TITLE_TEAMS_EVENT, 'bn-title-5');
		$ul = $div->addDiv('eventteams');
		$pos = 0;
		$weight = 100000;
		$nbEqual= 1;
		foreach($eventTeamIds as $teamId)
		{
			$oTeam = new Oteam($teamId);
			$teamWeight = $oTeam->getVal('poids');
			if ( $teamWeight<$weight)
			{
				$weight = $teamWeight;
				$pos += $nbEqual;
				$nbEqual = 1;
			}
			else $nbEqual++;
			
			if ( !in_array($teamId, $groupTeamIds))
			{
				$name = $pos . ' - (' . sprintf('%.02f', $teamWeight) . ') - ' . $oTeam->getVal('name');
				$ul->addP('teamIds_'.$teamId, $name, 'ui-state-active gr-team');
			}
			else $posTeam[$teamId] = $pos;
			unset($oTeam);
			
		}

		// Emplacement pour positionner les entrees
		$type = $oRound->getVal('type');
		unset($oRound);
		switch($type)
		{
			case OROUND_TYPEIC_GROUP:
			case OROUND_TYPEIC_AR:
			case OROUND_TYPE_GROUP:
			case OROUND_TYPE_AR:
				$oRound = new Ogroup($roundId);
				$byes = array();
				break;
			case OROUND_TYPEIC_KO:
			case OROUND_TYPE_QUALIF:
			case OROUND_TYPE_MAINDRAW:
			case OROUND_TYPE_THIRD:
			case OROUND_TYPE_CONSOL:
			case OROUND_TYPE_PLATEAU:
			case OROUND_TYPE_RONDE:
			case OROUND_TYPE_PROGRES:
				$oRound = new Oko($roundId);
				$size = $oRound->getSize();
				$byes = $oRound->getByes();
				
				break;
		}
		$div = $body->addDiv('divgroupteam', 'bn-div-left-last');
		$str = $oRound->getVal('name') . ' - ' . $size . ' ' . LOC_LABEL_TEAMS;
		$t = $div->addP('', $str, 'bn-title-5');
		$d = $div->addDiv('gr-teams');
		$teamId = reset($groupTeamIds);
		$oTeam = new Oteam($teamId);
		for($i=0; $i<$size; $i++)
		{
			$dl = $d->addDiv('', 'bn-div-clear');
			$dl->addDiv('', 'gr-number')->addP('', $i+1);
			if ( in_array($i+1, $byes))
			{
				$dp = $dl->addDiv('', 'gr-nodrop gr-slot');
				$dp->addP('', 'bye', 'ui-state-default ui-state-active gr-bye');
			}
			else
			{
				$dp = $dl->addDiv('', 'gr-drop gr-slot');
				if ($oTeam->getPosRound($roundId) == ($i+1))
				{
					$name = $posTeam[$teamId] . ' -(' . sprintf('%.02f', $oTeam->getVal('poids')) . ') - ' . $oTeam->getVal('name');
					$dp->addP('teamIds_'.$teamId, $name, 'ui-state-default ui-state-active gr-team');
					$dp->completeAttribute('class', 'gr-busy');
					unset($oTeam);
					$teamId = next($groupTeamIds);
					$oTeam = new Oteam($teamId);
				}
				else $dp->completeAttribute('class', 'gr-empty');
			}
		}

		// bouton d'enregistrement
		$d = $div->addDiv('', 'bn-div-btn');
		$btn = $d->addButton('btnReg', LOC_BTN_UPDATE, BDIV_UPDATE_COMPO, 'disk');
		$btn->addMetaData('roundId', $roundId);
			
		$body->addJqready('pageCompo();');
		$body->display();
		return false;
	}

	/**
	 * Mise a jour du format des matches
	 *
	 */
	public function updateMatchsDef()
	{
		// Recupere tous les rounds de la phase (draw_id+rund_group)
		// si c'est un maindraw
		$roundId = Bn::getValue('roundId');
		$oRound = new Oround($roundId);
		if ($oRound->getVal('type') == OROUND_TYPE_MAINDRAW)
		{
			$q = new Bn_query('rounds');
			$q->setFields('rund_id');

			$q->addWhere('rund_drawid=' . $oRound->getVal('drawid'));
			$q->addWhere("rund_group='" . $oRound->getVal('group') . "'");
			$roundIds = $q->getCol();
		}
		else $roundIds[] = $roundId;
		unset($oRound);

		foreach($roundIds as $roundId)
		{
			$oRound = new Oround($roundId);
			$type = $oRound->getVal('type');
			unset ($oRound);
			switch($type)
			{
				case OROUND_TYPEIC_GROUP:
				case OROUND_TYPEIC_AR:
				case OROUND_TYPE_GROUP:
				case OROUND_TYPE_AR:
					$oGroup = new Ogroup($roundId);
					$updateRanking = true;
					break;
				case OROUND_TYPEIC_KO:
				case OROUND_TYPE_QUALIF:
				case OROUND_TYPE_MAINDRAW:
				case OROUND_TYPE_THIRD:
				case OROUND_TYPE_CONSOL:
				case OROUND_TYPE_PLATEAU:
				case OROUND_TYPE_RONDE:
				case OROUND_TYPE_PROGRES:
					$oGroup = new Oko($roundId);
					$updateRanking = false;
					break;
			}

			// Mettre a jour le format des matches
			$oGroup->setVal('nbms', Bn::getValue('nbms', 0));
			$oGroup->setVal('nbws', Bn::getValue('nbws', 0));
			$oGroup->setVal('nbas', Bn::getValue('nbas', 0));
			$oGroup->setVal('nbmd', Bn::getValue('nbmd', 0));
			$oGroup->setVal('nbwd', Bn::getValue('nbwd', 0));
			$oGroup->setVal('nbxd', Bn::getValue('nbxd', 0));
			$oGroup->setVal('nbad', Bn::getValue('nbad', 0));
			$oGroup->setVal('matchwin', Bn::getValue('matchwin'));
			$oGroup->setVal('matchloose', Bn::getValue('matchloose'));
			$oGroup->setVal('matchwo', Bn::getValue('matchwo'));
			$oGroup->setVal('matchrtd', Bn::getValue('matchrtd'));
			$oGroup->save();

			// Mettre a jour le classement
			if ($updateRanking) $oGroup->updateTeamRanking();

			unset ($oGroup);
		}

		// Message de fin
		$body = new Body();
		$body->addP('', LOC_LABEL_DIV_REGISTERED);
		$d = $body->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CLOSE);

		// Envoi au navigateur
		$res = array('content' => $body->toHtml(),
					'title' => LOC_ITEM_MATCHSDEF);
		echo Bn::toJson($res);
		return false;
	}


	/**
	 * Affichage de la page de modification de la compo des matchs
	 */
	public function pageMatchsdef()
	{
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$roundId = Bn::getValue('roundId');
		$oRound = new Oround($roundId);
		$isSquash = Bn::getConfigValue('squash', 'params');
		$body = new Body();

		// Titre
		$body->addP('', LOC_ITEM_MATCHSDEF, 'bn-title-4');
		$type = $oRound->getVal('type');
		if($type == OROUND_TYPE_PLATEAU) $body->addWarning(LOC_MSG_PLATEAUX_ONLY);
		if($type == OROUND_TYPE_MAINDRAW) $body->addWarning(LOC_MSG_PLATEAUX);

		$form = $body->addForm('frmMatchsdef', BDIV_UPDATE_MATCHSDEF, 'targetDlg');
		$form->getForm()->addMetadata('success', "updated");
		$form->getForm()->addMetadata('dataType', "'json'");
		$form->addHidden('roundId', $roundId);

		$div = $form->addDiv('', 'bn-div-left');
		$t = $div->addP('', LOC_TITLE_MATCH_DEF, 'bn-title-5');
		$edit = $div->addEdit('nbms', LOC_LABEL_ROUND_NBMS, $oRound->getVal('nbms'), 20);
		$edit = $div->addEdit('nbws', LOC_LABEL_ROUND_NBWS, $oRound->getVal('nbws'), 20);
		$edit = $div->addEdit('nbas', LOC_LABEL_ROUND_NBAS, $oRound->getVal('nbas'), 20);
		if (!$isSquash)
		{
			$edit = $div->addEdit('nbmd', LOC_LABEL_ROUND_NBMD, $oRound->getVal('nbmd'), 20);
			$edit = $div->addEdit('nbwd', LOC_LABEL_ROUND_NBWD, $oRound->getVal('nbwd'), 20);
			$edit = $div->addEdit('nbxd', LOC_LABEL_ROUND_NBXD, $oRound->getVal('nbxd'), 20);
			$edit = $div->addEdit('nbad', LOC_LABEL_ROUND_NBAD, $oRound->getVal('nbad'), 20);
		}

		$div = $form->addDiv('', 'bn-div-left');
		$t = $div->addP('', LOC_TITLE_MATCH_POINTS, 'bn-title-5');
		$edit = $div->addEdit('matchwin', LOC_LABEL_ROUND_MATCH_WIN, $oRound->getVal('matchwin'), 20);
		$edit = $div->addEdit('matchloose', LOC_LABEL_ROUND_MATCH_LOOSE, $oRound->getVal('matchloose'), 20);
		$edit = $div->addEdit('matchwo', LOC_LABEL_ROUND_MATCH_WO, $oRound->getVal('matchwo'), 20);
		$edit = $div->addEdit('matchrtd', LOC_LABEL_ROUND_MATCH_RTD, $oRound->getVal('matchrtd'), 20);

		$d = $form->addDiv('', 'bn-div-btn');
		$d->addButtonValid('btnValid', LOC_BTN_UPDATE);
		$body->display();
		return false;
	}

	/**
	 * Mise a jour du format des rencontres
	 *
	 */
	public function updateTiesDef()
	{
		// Recupere tous les rounds de la phase (draw_id+rund_group)
		$roundId = Bn::getValue('roundId');
		$oRound = new Oround($roundId);
		if ($oRound->getVal('type') == OROUND_TYPE_MAINDRAW)
		{
			$q = new Bn_query('rounds');
			$q->setFields('rund_id');
			$q->addWhere('rund_drawid=' . $oRound->getVal('drawid'));
			$q->addWhere("rund_group='" . $oRound->getVal('group') . "'");
			$roundIds = $q->getCol();
		}
		else $roundIds[] = $roundId;

		unset($oRound);

		// Traiter chaque round
		foreach($roundIds as $roundId)
		{
			$oRound = new Oround($roundId);
			$type = $oRound->getVal('type');
			unset ($oRound);
			switch($type)
			{
				case OROUND_TYPEIC_GROUP:
				case OROUND_TYPEIC_AR:
				case OROUND_TYPE_GROUP:
				case OROUND_TYPE_AR:
					$oGroup = new Ogroup($roundId);
					$updateRanking = true;
					break;
				case OROUND_TYPEIC_KO:
				case OROUND_TYPE_QUALIF:
				case OROUND_TYPE_MAINDRAW:
				case OROUND_TYPE_THIRD:
				case OROUND_TYPE_CONSOL:
				case OROUND_TYPE_PLATEAU:
				case OROUND_TYPE_RONDE:
				case OROUND_TYPE_PROGRES:
					$oGroup = new Oko($roundId);
					$updateRanking = false;
					break;
			}

			// Mettre a jour le format des rencontres
			$oGroup->setVal('tiewin', Bn::getValue('tiewin'));
			$oGroup->setVal('tieequalplus', Bn::getValue('tieequalplus'));
			$oGroup->setVal('tieequal', Bn::getValue('tieequal'));
			$oGroup->setVal('tieequalminus', Bn::getValue('tieequalminus'));
			$oGroup->setVal('tieloose', Bn::getValue('tieloose'));
			$oGroup->setVal('tiewo', Bn::getValue('tiewo'));
			$oGroup->setVal('tieranktype', Bn::getValue('tieranktype'));
			$oGroup->setVal('tiematchdecisif', Bn::getValue('tiematchdecisif'));
			$oGroup->setVal('tiematchnum', Bn::getValue('tiematchnum'));
			$oGroup->save();

			// Mettre a jour le classement
			if ($updateRanking) $oGroup->updateTeamRanking();
			unset($oGroup);
		}

		// Message de fin
		$body = new Body();
		$body->addP('', LOC_LABEL_DIV_REGISTERED);
		$d = $body->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CLOSE);

		// Envoi au navigateur
		$res = array('content' => $body->toHtml(),
					'title' => LOC_ITEM_TIESDEF);
		echo Bn::toJson($res);
		return false;
	}


	/**
	 * Affichage de la page de modification d'un groupe
	 */
	public function pageTiesdef()
	{
		require_once 'Object/Locale/'. Bn::getLocale() .'/Oplayer.inc';
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$roundId = Bn::getValue('roundId');
		$oRound = new Oround($roundId);
		$body = new Body();

		// Titres
		$body->addP('', LOC_ITEM_TIESDEF, 'bn-title-4');
		$type = $oRound->getVal('type');
		if($type == OROUND_TYPE_PLATEAU) $body->addWarning(LOC_MSG_PLATEAUX_ONLY);
		if($type == OROUND_TYPE_MAINDRAW) $body->addWarning(LOC_MSG_PLATEAUX);

		$form = $body->addForm('frmtiesdef', BDIV_UPDATE_TIESDEF, 'targetDlg');
		$form->getForm()->addMetadata('success', "updated");
		$form->getForm()->addMetadata('dataType', "'json'");
		$form->addHidden('roundId', $roundId);

		$div = $form->addDiv('divtiespoints', 'bn-div-left');
		$t = $div->addP('', LOC_TITLE_TIE_POINTS, 'bn-title-5');
		$edit = $div->addEdit('tiewin', LOC_LABEL_TIE_WIN, $oRound->getVal('tiewin'), 20);
		$edit = $div->addEdit('tieequalplus', LOC_LABEL_TIE_EQUALPLUS, $oRound->getVal('tieequalplus'), 20);
		$edit = $div->addEdit('tieequal', LOC_LABEL_TIE_EQUAL, $oRound->getVal('tieequal'), 20);
		$edit = $div->addEdit('tieequalminus', LOC_LABEL_TIE_EQUALMINUS, $oRound->getVal('tieequalminus'), 20);
		$edit = $div->addEdit('tieloose', LOC_LABEL_TIE_LOOSE, $oRound->getVal('tieloose'), 20);
		$edit = $div->addEdit('tiewo', LOC_LABEL_TIE_WO, $oRound->getVal('tiewo'), 20);

		$div = $form->addDiv('divtiesrank', 'bn-div-left-last');
		$t = $div->addP('', LOC_TITLE_TIE_RANK, 'bn-title-5');
		$lov = Otie::getRankLov();
		$slt = $div->addSelect('tieranktype', LOC_LABEL_TIE_RANK_TYPE);
		$slt->addOptions($lov, $oRound->getVal('tieranktype'));

		$lov = array();
		for($i=OMATCH_DISCI_MS; $i<=OMATCH_DISCI_XD; $i++) $lov[$i] = constant('LABEL_' . $i);
		$slt = $div->addSelect('tiematchdecisif', LOC_LABEL_TIE_MATCH_DECISIF);
		$slt->addOptions($lov, $oRound->getVal('tiematchdecisif'));

		$edit = $div->addEdit('tiematchnum', LOC_LABEL_TIE_MATCH_NUM, $oRound->getVal('tiematchnum'), 20);

		$d = $form->addDiv('', 'bn-div-btn');
		$d->addButtonValid('btnValid', LOC_BTN_UPDATE);
			
		$body->display();
		return false;
	}

	/**
	 * Mise a jour de la definition du groupe de type poule cot cot codet
	 *
	 */
	public function updateGroupDef()
	{
		$roundId = Bn::getValue('roundId');

		$formula = Bn::getValue('formula');
		switch($formula)
		{
			case OROUND_FORMULA_GROUP:
				$oGroup = new Ogroup($roundId);
				$oGroup->setVal('type', OROUND_TYPEIC_GROUP);
				$oGroup->setVal('qual', 0);
				break;
			case OROUND_FORMULA_AR:
				$oGroup = new Ogroup($roundId);
				$oGroup->setVal('type', OROUND_TYPEIC_AR);
				$oGroup->setVal('qual', 0);
				break;
			case OROUND_FORMULA_KO:
				$oGroup = new Oko($roundId);
				$oGroup->setVal('type', OROUND_TYPEIC_KO);
				$oGroup->setVal('qual', 1);
				break;
			case OROUND_FORMULA_PLATEAU:
				$oGroup = new Oko($roundId);
				$oGroup->setVal('type', OROUND_TYPE_MAINDRAW);
				$oGroup->setVal('qual', 1);
				break;
			case OROUND_FORMULA_RONDE:
				//@todo traiter les rondes suisse
				$oGroup = new Oko($roundId);
				$oGroup->setVal('type', OROUND_TYPE_RONDE);
				$oGroup->setVal('qual', 0);
				break;
			default:
				break;
		}

		$oGroup->setVal('name', Bn::getValue('roundname'));
		$oGroup->setVal('stamp', Bn::getValue('stamp'));
		$oGroup->setVal('drawid', Bn::getValue('drawid'));
		$oldEntries = $oGroup->getVal('entries');
		$entries = Bn::getValue('entries');
		$oGroup->setVal('entries', $entries);
		$oGroup->setVal('rge', Bn::getValue('rge', 1));
		$oldRankType = $oGroup->getVal('ranktype');
		$rankType = Bn::getValue('ranktype');
		$oGroup->setVal('ranktype', $rankType);
		$oGroup->save();

		// Mettre a jour le classement
		if ($oldRankType != $rankType) $oGroup->updateTeamRanking();

		// Mettre a jour les plateaux
		if ($formula == OROUND_FORMULA_PLATEAU) $oGroup->savePlateau();
		else $oGroup->deletePlateau();

		// Mettre a jour la troisième place
		if ($formula == OROUND_FORMULA_KO && Bn::getValue('third', 0)) $oGroup->saveThird();
		else $oGroup->deleteThird();

		// Mettre a jour la ronde suisse
		if ($formula == OROUND_FORMULA_RONDE ) $oGroup->saveRonde(Bn::getValue('tour', 1));
		else $oGroup->deleteRonde();

		// Mettre a jour les equipes
		if ($oldEntries > $entries)
		{
			$entrieIds = $oGroup->getTeams(true);
			$oGroup->updateTeams(array_slice($entrieIds, 0, $entries));
		}
		
		// Message de fin
		// Preparer les champs de saisie
		$body = new Body();
		$body->addp('', LOC_LABEL_DIV_REGISTERED);
		$d = $body->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CLOSE);

		// Envoi au navigateur
		$res = array('content' => $body->toHtml(),
					'title' => LOC_ITEM_GROUPDEF);
		echo Bn::toJson($res);
		return false;
	}

	/**
	 * Affichage de la page de modification d'un groupe
	 */
	public function pageGroupDef()
	{
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$roundId = Bn::getValue('roundId');
		$oRound = new Ogroup($roundId);
		$body = new Body();

		// Titres
		$body->addP('', LOC_ITEM_GROUPDEF, 'bn-title-4');
		$type = $oRound->getVal('type');
		if ($type != OROUND_TYPE_PLATEAU && $type != OROUND_TYPE_THIRD &&
		($type != OROUND_TYPE_RONDE || $oRound->getVal('rge')==1))
		{

			$form = $body->addForm('frmGroupdef', BDIV_UPDATE_GROUPDEF, 'targetDlg');
			$form->getForm()->addMetadata('success', "updated");
			$form->getForm()->addMetadata('dataType', "'json'");
			$form->addHidden('roundId', $roundId);

			$div = $form->addDiv('divname', 'bn-div-left');
			$edit = $div->addEdit('roundname', LOC_LABEL_ROUND_NAME, $oRound->getVal('name'), 30);

			$divs = Odiv::getLov($eventId);
			$slt = $div->addSelect('drawid', LOC_LABEL_DIVISION);
			$slt->addOptions($divs, $oRound->getval('drawid'));

			$div = $form->addDiv('divstamp', 'bn-div-left');
			$edit = $div->addEdit('stamp', LOC_LABEL_ROUND_STAMP, $oRound->getVal('stamp'), 10);
			$edit = $div->addEdit('entries', LOC_LABEL_TEAM_NUMBER, $oRound->getVal('entries'), 2);

			$div = $form->addDiv('divFormulaDef', 'bn-div-clear bn-div-left');
			$div->addP('', LOC_LABEL_FORMULE, 'bn-title-5');
			$d = $div->addDiv('', 'divLol');
			$type = $oRound->getVal('type');
			$formule = $oRound->getFormule();
			//@todo ajouter la saisie de la phase
			switch($formule)
			{
				case OROUND_FORMULA_GROUP:
				case OROUND_FORMULA_AR:
					$d->addRadio('formula', 'formulagroup', 'LABEL_'.OROUND_FORMULA_GROUP, OROUND_FORMULA_GROUP, $formule==OROUND_FORMULA_GROUP);
					$d->addRadio('formula', 'formulaar',    'LABEL_'.OROUND_FORMULA_AR, OROUND_FORMULA_AR, $formule==OROUND_FORMULA_AR);
					$div = $form->addDiv('divRanktype', 'bn-div-left');
					$div->addP('', 'Calcul du classement', 'bn-title-5');
					$d = $div->addDiv('', 'divLol');
					$d->addRadio('ranktype', 'rankcg', 'LABEL_'.OGROUP_RANK_CG, OGROUP_RANK_CG, $oRound->getVal('ranktype')==OGROUP_RANK_CG);
					$d->addRadio('ranktype', 'rankrp', 'LABEL_'.OGROUP_RANK_RP, OGROUP_RANK_RP, $oRound->getVal('ranktype')==OGROUP_RANK_RP);
					break;
				default:
					$dl = $d->addDiv('', 'bn-div-line');
					$dl->addRadio('formula', 'formulako',    'LABEL_'.OROUND_FORMULA_KO, OROUND_FORMULA_KO, $formule==OROUND_FORMULA_KO);
					$dl->addCheckBox('third', LOC_LABEL_THIRD, 1, $oRound->isThird());
					$dl = $d->addDiv('', 'bn-div-line bn-div-clear');
					$dl->addRadio('formula', 'formulaplateau', 'LABEL_'.OROUND_FORMULA_PLATEAU, OROUND_FORMULA_PLATEAU, $formule==OROUND_FORMULA_PLATEAU);
					$dl = $d->addDiv('', 'bn-div-line bn-div-clear bn-div-auto');
					$dl->addRadio('formula', 'formularonde', 'LABEL_'.OROUND_FORMULA_RONDE, OROUND_FORMULA_RONDE, $formule==OROUND_FORMULA_RONDE);
					$dl->addEdit('tour', LOC_LABEL_NB_ROUND, $oRound->getNbTour(), 2);
					$d->addHidden('ranktype', $oRound->getVal('ranktype'));
					$d->addBreak();
					break;
			}


			$d = $form->addDiv('', 'bn-div-btn');
			$d->addButtonValid('btnValid', LOC_BTN_UPDATE);
		}
		else
		{
			$oDraw = new Odraw($oRound->getVal('drawid'));
			$div = $body->addDiv('divname', 'bn-div-left');
			$div->addInfo('roundname', LOC_LABEL_ROUND_NAME, $oRound->getVal('name'));
			$div->addInfo('divname', LOC_LABEL_DIVISION, $oDraw->getVal('name'));
			$div->addInfo('formula', LOC_LABEL_TYPE, 'Plateau');

			$div = $body->addDiv('divstamp', 'bn-div-left');
			$div->addInfo('stamp', LOC_LABEL_ROUND_STAMP, $oRound->getVal('stamp'));
			$div->addInfo('entries', LOC_LABEL_TEAM_NUMBER, $oRound->getVal('entries'));
		}
		$body->display();
		return false;
	}


	/**
	 * Affichage de la page de modification d'un groupe
	 */
	public function pageGroup()
	{
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$roundId = Bn::getValue('roundId');
		$oRound = new Ogroup($roundId);
		$oDraw = new Odraw($oRound->getVal('drawid'));
		$body = new Body();
		$isSquash = Bn::getConfigValue('squash', 'params');

		// Commandes generiques
		$oEvent->header($body);

		// Titres
		$t = $body->addP('', '', 'bn-title-3');
		$str = $oDraw->getVal('name') . ' - ' . $oRound->getVal('name');
		$t->addBalise('span', '', $str);

		$dyn = $body->addDynpref('divDivision');
		$item = $dyn->addItem(BDIV_PAGE_GROUPDEF, LOC_ITEM_GROUPDEF, true);
		$item->addMetaData('roundId', $roundId);
		$item = $dyn->addItem(BDIV_PAGE_TIESDEF, LOC_ITEM_TIESDEF);
		$item->addMetaData('roundId', $roundId);
		$item = $dyn->addItem(BDIV_PAGE_MATCHSDEF, LOC_ITEM_MATCHSDEF);
		$item->addMetaData('roundId', $roundId);
		$item = $dyn->addItem(BDIV_PAGE_COMPO, LOC_ITEM_COMPO);
		$item->addMetaData('roundId', $roundId);
		//$item = $dyn->addItem(BDIV_PAGE_CALENDAR, LOC_ITEM_CALENDAR);
		//$item->addMetaData('roundId', $roundId);
		$body->addBreak();
		$body->display();
		return false;
	}

	/**
	 * Envoi la liste des groupes
	 */
	public function fillGroups()
	{
		require_once 'Object/Locale/' . Bn::getLocale() . '/Ogroup.inc';
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);

		$q = new Bn_query('draws');
		$q->addTable('rounds', 'draw_id=rund_drawid');
		$q->addWhere('draw_eventid=' . $eventId);
		$search = Bn::getValue('search');
		if ( !empty($search) )
		{
			$where = "rund_name LIKE '%" . $search . "%'";
			$q->addWhere($where);
		}
		$drawId = Bn::getValue('drawId', -1);
		if ($drawId > 0)
		{
			$q->addWhere('draw_id=' . $drawId);
		}
		$q->setFields('rund_id, rund_name, rund_type, rund_entries, rund_pbl, draw_name, rund_rge');
		$q->setOrder('draw_name, rund_group, rund_rge');
		//$rounds = $q->getGridRows(0, true);
		//$param  = $q->getGridParam();
		//$i = $param->first;
		$rounds = $q->getRows();
		$i = 1;
		$gvRows = new GvRows($q);
		foreach ($rounds as $round)
		{
			$roundId = $round['rund_id'];
			$row[0] = $i++;
			$b = new Bn_Balise();
			$file = $round['rund_pbl'] . '.png';
			$img = $b->addImage('', $file, $round['rund_pbl']);
			$img->addLink('', BDIV_PAGE_GROUP. '&roundId=' . $roundId, $round['rund_name'], 'targetBody');
			$row[1] = $img->toHtml();
			$row[2] = constant('LABEL_' . $round['rund_type']);
			$row[3] = $round['rund_entries'];
			$row[4] = $round['draw_name'] ;

			$bal= new Bn_balise();

			$lnk = $bal->addLink('', BDIV_PAGE_DELETE_GROUP . '&roundId=' . $roundId, null, 'targetDlg');
			$lnk->addimage('', 'delete.png', 'del');
			$lnk->completeAttribute('class', 'bn-delete');
			$lnk->addMetaData('width', 330);
			$lnk->addMetaData('height', 230);
			$row[5] = $bal->toHtml();
			unset($bal);

			$gvRows->addRow($row, $roundId);
		}
		$gvRows->display();
		return false;
	}

	/**
	 * Affichage de la page de gestion des equipes
	 */
	public function pageGroups()
	{
		$eventId = Bn::getValue('event_id');
		$oEvent = new Oevent($eventId);
		$body = new Body();

		// Commandes generiques
		$oEvent->header($body);

		// Titres
		$t = $body->addP('', '', 'bn-title-3');
		$t->addBalise('span', '', LOC_TITLE_GROUPS_MANAGEMENT);
		//$body->addP('', LOC_MSG_GROUPS, 'bn-p-info');

		// Criteres de filtrage

		$d = $body->addDiv('', 'bn-div-criteria bn-div-line bn-div-auto');
		$form = $d->addForm('frmSearch', BDIV_FILL_GROUPS, 'targetBody', 'searchGroups');

		$edit = $form->addEdit('search', LOC_LABEL_FIND_GROUP, '', 20);
		$edit->noMandatory();

		$lov = Odiv::getLov($eventId);
		$lov = array(-1 => '---') + $lov;
		$select = $form->addSelect('drawId', LOC_LABEL_DIVISION, BDIV_FILL_GROUPS);
		$select->addOptions($lov, '---');


		$btn = $form->addButtonValid('btnFilter', LOC_BTN_SEARCH, 'search', 'search');

		$btn = $form->addButton('btnNew', LOC_BTN_NEW_GROUP, BDIV_PAGE_ADD_GROUP, 'plus', 'targetDlg');
		$btn->addMetaData('title', '"' . LOC_TITLE_ADD_GROUP . '"');
		$btn->addMetaData('width',  680);
		$btn->addMetaData('height', 335);
		$btn->completeAttribute('class', 'bn-dlg');

		// Liste des groupes
		$url = BDIV_FILL_GROUPS;
		$div = $body->addDiv('dd', 'bn-div-clear');
		$grid = $div->addGridview('gridDivs', $url, 50);
		$col = $grid->addColumn('#',   '#', false);
		$col->setLook(30, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_NAME, 'name', false);
		$col->setLook(220, 'left', false);
		$col->initSort('asc');
		$col = $grid->addColumn(LOC_COLUMN_TYPE, 'type', false);
		$col->setLook(220, 'left', false);
		$col = $grid->addColumn(LOC_COLUMN_SIZE, 'entries', false);
		$col->setLook(100, 'left', false);
		$col = $grid->addColumn('div', 'div', false);
		$col->setLook(50, 'center', false);
		$col = $grid->addColumn(LOC_COLUMN_ACTION, 'action', false);
		$col->setLook(50, 'center', false);

		$grid->setLook(LOC_TITLE_GROUPS, 0, "'auto'");
		$grid->setGroup(5, 'desc');

		$body->addJQReady('pageGroups();');
		$body->display();
		return false;
	}

}
?>
