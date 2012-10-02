<?php
/*****************************************************************************
 !   Module     : utils
 !   File       : $Source: /cvsroot/aotb/badnet/src/utils/utgroup.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.11 $
 !   Author     : G.CANTEGRIL
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/03/01 22:17:09 $
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
require_once "utbase.php";


/** @mainpage Documentation des utilitaires de Badnet
 * @section intro Introduction
 *
 * Les utilitaires de Badnet sont des classes sp�cialis�es dans la gestion
 * et la manipulations de donn�es souvent utilis�es dans les modules du projet.
 *
 * @li utDate    Manipulations des dates
 * @li utFfba    Acc�s aux donn�es de la base f�d�rale
 * @li utIbf     Acc�s aux donn�es de la base internationale
 * @li utImg     Traitement des images
 * @li utMail    Envoie de mail
 * @li utRound   Gestion des donn�es  round , tie et match
 * @li utScore   Manipulations des scores
 * @li utko      Manipulations des tableaux par eliminatin directe
 *
 * @author Gerard CANTEGRIL
 */

/**
 * @~english See french doc
 *
 * @~french Cette classe permet d'ajouter et de supprimer des
 * tour (round) dans la base de donn�es pour un tableau d'un tournoi.
 * Pour rappel, un tableau (draw) est constitu� de tour (round); un tour
 * contient des rencontres (tie) et pour chaque rencontre on pr�cise 
 * le nombre de match de chaque discipline (simple homme, simple dame,
 * double homme double dame et double mixte). Cette architecture  permet
 * de g�rer de la m�me mani�re les tournois individuels et les comp�titions
 * par �quipe.
 * L'utilisation de cette classe permet avec un simple appel, de cr�er o
 * supprimer tous les enregistrements des rencontres et des matchs
 * correspondant � un tour en fonction de ses caract�ristiques. 
 *
 * @author Gerard CANTEGRIL
 *
 */

class utGroup extends utBase
{

	// {{{ properties
	// }}}


	// {{{ updatePairsToGroups
	/**
	 * Update the relation of a pair in a round
	 *
	 * @access public
	 * @param  integer $drawId   id of the draw
	 * @param  array   $pairs    pairs of the rounds
	 *                           $pair['id'] : id de la paire
	 *                           $pair['tds']: tete de serie
	 *	                       $pair['pos']: position (A1,C4,B3..)
	 *
	 * @return mixed
	 */
	function updatePairsToGroups($drawId, $pairsIn, $pairsOut, $aGroupName)
	{
		// Suprimer les paires hors des groupes
		if (count($pairsOut))
		{
			// Chercher les relations entre les paire et la poule
			$ids = implode(',', $pairsOut);
			$fields = array('t2r_id');
			$tables = array('t2r', 'rounds');
			$where = "rund_drawId=$drawId".
	    	" AND rund_type=".WBS_ROUND_GROUP.
	    	" AND rund_id=t2r_roundId".
	    	" AND rund_group='" . addslashes($aGroupName) . "'".
			" AND t2r_pairId IN ($ids)";
			$res = $this->_select($tables, $fields, $where);

			// Supprimer les relations entre les paires et la poule
			if ($res->numRows())
			{
				while ($t2r = $res->fetchRow(DB_FETCHMODE_ASSOC))
				$id[] = $t2r['t2r_id'];
				$ids = implode(',', $id);
				$where = " t2r_id IN ($ids)";
				$res = $this->_delete('t2r', $where);
			}
			// Chercher les relations entre les paires et les matchs
			$ids = implode(',', $pairsOut);
			$fields = array('p2m_id');
			$tables = array('p2m', 'matchs', 'ties', 'rounds');
			$where = "rund_drawId=$drawId".
    	" AND rund_type=".WBS_ROUND_GROUP.
    	" AND rund_group='" . addslashes($aGroupName) . "'".
	    " AND rund_id=tie_roundId".
	    " AND mtch_tieId=tie_id".
	    " AND p2m_matchId=mtch_id".
	    " AND p2m_pairId IN ($ids)";
			$res = $this->_select($tables, $fields, $where);

			// Supprimer les relations entre les paires et les matches
			if ($res->numRows())
			{
				while ($p2m = $res->fetchRow(DB_FETCHMODE_ASSOC))
				$id[] = $p2m['p2m_id'];
				$ids = implode(',', $id);
				$where = " p2m_id IN ($ids)";
				$res = $this->_delete('p2m', $where);
			}
		}

		// Retrieve the list of the groups
		$fields = array('rund_id', 'rund_stamp', 'rund_size');
		$tables = array('rounds');
		$where = "rund_drawId=$drawId".
	    " AND rund_group='" . addslashes($aGroupName) . "'".
		" AND rund_type=".WBS_ROUND_GROUP;
		$order = "rund_stamp";
		$res = $this->_select($tables, $fields, $where, $order);

		// Liste des places disponibles dans les poules
		// $entry['A1'] = (rund_id, A, 3, 1) A poule de 3
		// $entry['B4'] = (rund_id, B, 5, 4) B poule de 5
		// Principe de la chenille A1, B1, C1, C2, B2, A2, A3, B3, C3 ...
		while ($group = $res->fetchRow(DB_FETCHMODE_ASSOC)) $groups[] = $group;
		$group = reset($groups);
		$funcs = array('next', 'prev');
		$funcss = array('reset', 'end');
		$funcsId = 0;
		$func = $funcs[$funcsId];
		for ($i=1; $i<8; $i++)
		{
			while( $group !== false)
			{
				if ( $i <= $group['rund_size'])
				{
					$pos = $group['rund_stamp'].$i;
					$group['pos'] = $i;
					$freePos[$pos] = $group;
				}
				$group = $func($groups);
			}
			$funcsId = 1 - $funcsId;
			$func = $funcs[$funcsId];
			$group = $funcss[$funcsId]($groups);
		}

		// Traiter les paires qui ont une position
		$waitPairs = array();
		foreach($pairsIn as $pair)
		{
			$pos = $pair['pos'];
			if(!isset($freePos[$pos]))
			{
				$waitPairs[] = $pair;
				continue;
			}
			$free = $freePos[$pos];
			// Chercher le lien entre la paire et un groupe
			// du tableau
			$fields = array('t2r_id');
			$tables = array('t2r', 'rounds');
			$where = "t2r_pairId=".$pair['id'].
   			" AND rund_drawId=$drawId".
	    	" AND rund_group='" . addslashes($aGroupName) . "'".
			" AND rund_type=".WBS_ROUND_GROUP.
	    	" AND rund_id=t2r_roundId";
			$res = $this->_select($tables, $fields, $where);
			$cols['t2r_roundId']  = $free['rund_id'];
			$cols['t2r_tds']      = $pair['tds'];
			$cols['t2r_posRound'] = $free['pos'];
			$cols['t2r_status']   = WBS_PAIR_NONE;
			// Non trouve--> creation
			if (!$res->numRows())
			{
				$cols['t2r_pairId'] = $pair['id'];
				$res = $this->_insert('t2r', $cols);
				unset($cols['t2r_pairId']);
			}
			// Trouve--> mise a jour
			else
			{
				$t2r = $res->fetchRow(DB_FETCHMODE_ASSOC);
				$t2rId = $t2r['t2r_id'];
				$where = "t2r_id=$t2rId";
				$res = $this->_update('t2r', $cols, $where);
			}
			$this->_updatePairToMatches($pair['id'], $drawId, $free['rund_id'], $free['rund_size'], $free['pos'], $aGroupName);
			unset($freePos[$pos]);
		}

		// Traiter les paires qui restent
		$free = reset($freePos);
		if ($free === false) return;
		foreach($waitPairs as $pair)
		{
			// Chercher le lien entre la paire et un groupe
			// du tableau
			$fields = array('t2r_id');
			$tables = array('t2r', 'rounds');
			$where = "t2r_pairId=".$pair['id'].
        	" AND rund_drawId=$drawId".
	    	" AND rund_group='" . addslashes($aGroupName) . "'".
			" AND rund_type=".WBS_ROUND_GROUP.
	    	" AND rund_id=t2r_roundId";
			$res = $this->_select($tables, $fields, $where);
			$cols['t2r_roundId']  = $free['rund_id'];
			$cols['t2r_tds']      = $pair['tds'];
			$cols['t2r_posRound'] = $free['pos'];
			$cols['t2r_status']   = WBS_PAIR_NONE;
			// Non trouve--> creation
			if (!$res->numRows())
			{
				$cols['t2r_pairId'] = $pair['id'];
				$res = $this->_insert('t2r', $cols);
			}
			// Trouve--> mise a jour
			else
			{
				$t2r = $res->fetchRow(DB_FETCHMODE_ASSOC);
				$t2rId = $t2r['t2r_id'];
				$where = "t2r_id=$t2rId";
				$res = $this->_update('t2r', $cols, $where);
			}
			$this->_updatePairToMatches($pair['id'], $drawId, $free['rund_id'],
			$free['rund_size'], $free['pos'], $aGroupName);
			$free = next($freePos);
			if ($free === false) break;
		}
		return true;
	}
	// }}}

	// {{{ _updatePairToMatches
	/**
	 * Update the relation of a pair with the match
	 *
	 * @access public
	 * @param  integer $drawId   id of the draw
	 * @param  integer   $size   taille de la poule
	 * @param  integer   $pos    posiiotn de la paire dans la poule
	 *
	 * @return mixed
	 */
	function _updatePairToMatches($pairId, $drawId, $rundId, $size, $pos, $aGroupName)
	{
		// En fonction de la position dans la poule, calculer
		// les positions des rencontres concernees. C'est achement complique!!!
		$start = 0;
		for ($i=1; $i<$pos-1; $i++)
		$start+= $i;
		for ($i=1; $i<=$size;$i++)
		{
			if ($i < $pos)
			{
				$posMatch[$start] = WBS_PAIR_BOTTOM;
				$posTies[] = $start++;
			}
			if ($i == $pos) $start += ($i-1);
			if ($i > $pos)
			{
				$posMatch[$start] = WBS_PAIR_TOP;
				$posTies[] = $start;
				$start += ($i-1);
			}
		}

		// Supprimer les relations obsoletes de la paire
		$ids = implode(',', $posTies);
		$fields = array('p2m_id');
		$tables = array('p2m', 'matchs', 'ties', 'rounds');
		$where = "p2m_pairId=$pairId".
		" AND p2m_matchId=mtch_id".
		" AND mtch_tieId=tie_id".
		" AND tie_roundId=rund_id".
		" AND rund_type=".WBS_ROUND_GROUP.
	    " AND rund_group='" . addslashes($aGroupName) . "'".
		" AND rund_drawId=$drawId".
		" AND (rund_id!=$rundId".
		" OR tie_posRound NOT IN ($ids))";
		$res = $this->_select($tables, $fields, $where);
		while ($p2m = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$p2mIds[] = $p2m['p2m_id'];
		if (isset($p2mIds))
		{
			$ids = implode(',', $p2mIds);
			$where = "p2m_id IN ($ids)";
			$this->_delete('p2m', $where);
		}

		// Retrieve the match id
		$ids = implode(',', $posTies);
		$fields = array('mtch_id', 'mtch_status', 'tie_posRound');
		$tables = array('ties', 'matchs');
		$where = "tie_roundId=$rundId".
	" AND mtch_tieId=tie_id".
	" AND tie_posRound IN ($ids)";
		$res = $this->_select($tables, $fields, $where);
		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$matchId = $match['mtch_id'];
			// Chercher le lien entre la paire et le match
			$fields = array('p2m_id');
			$tables = array('p2m');
			$where = "p2m_pairId=$pairId".
	    " AND p2m_matchId=$matchId";
			$res2 = $this->_select($tables, $fields, $where);

			// Non trouve--> creation
			if (!$res2->numRows())
			{
				$cols['p2m_pairId']   = $pairId;
				$cols['p2m_matchId']  = $matchId;
				$cols['p2m_result']   = WBS_RES_NOPLAY;
				$cols['p2m_posMatch'] = $posMatch[$match['tie_posRound']];
				$res2 = $this->_insert('p2m', $cols);
			}
			else
			{
				$cols = array();
				$cols['p2m_posMatch'] = $posMatch[$match['tie_posRound']];
				$res2 = $this->_update('p2m', $cols, $where);
			}
			// Mettre a jour l'etat du match
			if ($match['mtch_status'] < WBS_MATCH_READY)
			{
				$vals['mtch_status']  = WBS_MATCH_READY;
				$where = "mtch_id=$matchId";
				$res2 = $this->_update('matchs', $vals, $where);
			}
		}


		return true;
	}
	// }}}

	// {{{ goLot
	/**
	 * Tirage au sort de la position des paires dans les groupes
	 *
	 * @access public
	 * @param  integer $limitation
	 *
	 * @return void
	 */
	function goLot($drawId, $pairs, $aGroupName)
	{
		//	  $pair['id']
		//	  $pair['tds']
		//	  $pair['level']
		//	  $pair['average']
		//	  $pair['criteria']
		// Pour chaque paire
		//   calculer le nombre de pair avec le meme critere
		//   initialiser la liste des poules interdites a vide
		// Trier les paires suivant l'ordre
		//  - Tds
		//  - Rang international
		//  - Rang national
		//  - Niveau
		//  - Point
		// Initialiser la liste des poules avec
		//   - la taille
		//   - nombre de places prises
		// Les P premieres paires sont placees a la premiere
		// place de chaque poule :
		// Pour chaque paire restant
		// Tirer au sort la poule de la paire

		// Initialiser les paires:
		//    compter le nombre de paire par valeur de critere
		//$criteres = array('team', 'level');
		$criteres = array('team');
		$nbPairs = count($pairs);
		$nbCriteres = count($criteres);
		for($i=0; $i<$nbPairs; $i++)
		{
	  $pair = $pairs[$i];
	  foreach($criteres as $critName)
	  {
	  	$critere = array('name'  => $critName,
			       'value' => $pair[$critName]);
	  	if (isset($nb[$critName][$critere['value']]))
	  	$nb[$critName][$critere['value']]++;
	  	else
	  	$nb[$critName][$critere['value']] = 1;
	  	$pair['criteria'][] = $critere;
	  }
	  $pair['plages'][]  = array('length' => 0,
				     'groupsNok' => array());
	  $pair['pos']  = '';
	  $pair['groupNum']  = -1;
	  $pairs[$i]= $pair;
		}

		// Initialiser les paires:
		//  Trier les paires
		for($i=0; $i<$nbPairs; $i++)
		{
	  $pair = $pairs[$i];
	  for($j=0; $j<$nbCriteres; $j++)
	  {
	  	$critere = $pair['criteria'][$j];
	  	$critere['nbMax'] = $nb[$critere['name']][$critere['value']];
	  	$pair['criteria'][$j] = $critere;
	  }
	  $sortTds[] = $pair['tds'];
	  $sortInt[] = $pair['intRank'];
	  $sortNat[] = $pair['natRank'];
	  $sortLevel[]   = $pair['level'];
	  $sortAverage[] = $pair['average'];
	  $sortMaxTeam[]    = -$pair['criteria'][0]['nbMax'];
	  $sortTeam[]    = $pair['criteria'][0];
	  $pairs[$i]= $pair;
		}
		array_multisort($sortTds, $sortInt, $sortNat, $sortLevel,
		$sortMaxTeam, $sortTeam, $sortAverage, $pairs);
		unset($sortMaxTeam);
		unset($sortTeam);
		unset($sortAverage);
		unset($sortLevel);
		unset($sortNat);
		unset($sortInt);
		unset($sortTds);
		//print_r($pairs);
		//exit();
		// Liste des poules
		// Retrieve the list of the groups
		$fields = array('rund_id', 'rund_stamp', 'rund_size');
		$tables = array('rounds');
		$where = "rund_drawId=$drawId".
    	" AND rund_group='" . addslashes($aGroupName) . "'".
		" AND rund_type=".WBS_ROUND_GROUP;
		$order = "rund_stamp";
		$res = $this->_select($tables, $fields, $where, $order);
		while ($group = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $group['nok'] = 0;
	  $groups[] = $group;
		}

		// Placer les premieres paires en tete de poule
		$nbGroups = count($groups);
		for($i=0; $i<$nbGroups; $i++)
		{
	  $pair = $pairs[$i];
	  $group = $groups[$i];
	  $pair['groupNum']  = $i;
	  $pair['pos']  = $group['rund_stamp']."1";
	  $group['nok']  = 1;
	  for ($j = $i+1; $j<$nbPairs; $j++)
	  {
	  	$pairJ = $pairs[$j];
	  	$plage = array_pop($pairJ['plages']);
	  	$plage = $this->_limited($pair, $pairJ,
	  	$plage, $groups);

	  	$pairJ['plages'][] = $plage;
	  	$pairs[$j] = $pairJ;
	  }
	  $pairs[$i] = $pair;
	  $groups[$i] = $group;
	  //$this->_tracePair($pair);
		}

		// initialisation de la fonction random
		mt_srand((float) microtime()*1000000);

		// Tirer au sort le groupe des autres paires
		//$this->_debug($pairs, $groups);
		$nbGroups = count($groups);
		for($i=$nbGroups; $i<$nbPairs; $i++)
		{
			// paire courante
	  $pairCur = $pairs[$i];

	  // Si elle etait deja positionne (cas du retour arriere)
	  // liberer sa place
	  if($pairCur['groupNum'] != -1)
	  {
	  	$group = $groups[$pairCur['groupNum']];
	  	$group['nok']--;
	  	$groups[$pairCur['groupNum']] = $group;
	  	$pairCur['groupNum']  = -1;
	  	$pairCur['pos']  = "--";
	  	$pairs[$i] = $pairCur;
	  }
	  // Liste des groupes impossibles de la paire courante
	  $plage = array_pop($pairCur['plages']);

	  //Tirage au sort du group
	  $groupNum = $this->_random($i, $plage, $groups);

	  if ($groupNum != -1)
	  {
	  	// Dans la derniere listes des groupes interdits
	  	// de la paire, ajouter ce groupe
	  	$plage['groupsNok'][] = $groupNum;
	  	$plage['length']++;
	  	$pairCur['plages'][] = $plage;
	  	$pairs[$i] = $pairCur;
	  	 
	  	$pairCur['groupNum']  = $groupNum;

	  	// pour les paires suivantes,
	  	for ($j = $i+1; $j<$nbPairs; $j++)
	  	{
	  		// recuperer la derniere liste des groupes interdits
	  		// de la paire
	  		$pairNext = $pairs[$j];
	  		$plage = array_pop($pairNext['plages']);
	  		$pairNext['plages'][] = $plage;

	  		// Mettre a jour la liste des groupes interdit
	  		$plage = $this->_limited($pairCur, $pairNext,
	  		$plage, $groups);

	  		// S'il n y a plus de place possible
	  		if ($plage['length'] == $nbGroups)
	  		{
	  			$startPurge = $i+1;
	  			$groupNum=-1;
	  		}

	  		// Rajouter cette liste a la liste des groupes interdits
	  		$pairNext['plages'][] = $plage;
	  		$pairs[$j] = $pairNext;
	  	}
	  	if ($groupNum!=-1)
	  	{
	  		$group = $groups[$groupNum];
	  		// Cette position n'est plus utilisable
	  		$group['nok']++;
	  		$groups[$groupNum] = $group;
	  		// Memoriser la position de la paire
	  		$pairCur['pos']  = $group['rund_stamp'].$group['nok'];
	  		$pairs[$i] = $pairCur;
	  	}
	  }
	  else
	  {
	  	//echo "i=$i: decremente group $groupNum\n";
	  	$startPurge = $i;
	  	$i--;
	  }

	  if ($groupNum < 0)
	  {
	  	//$this->_debug($pairs, $groups);
	  	//echo "********retour arriere $i, start=$startPurge *************\n";
	  	//break;
	  	 
	  	// pour les paires suivantes,
	  	for ($j = $startPurge; $j<$nbPairs; $j++)
	  	{
	  		//supprimer la derniere liste de groupes interdits
	  		$pair = $pairs[$j];
	  		$plage = array_pop($pair['plages']);
	  		//$pair['pos'] = "..";
	  		//$pair['groupNum'] = -1;
	  		if(!count($pair['plages']))
	  		{
	  			$pair['plages'][]  = array('length' => 0,
						 'groupsNok' => array());
	  		}
	  		$pairs[$j] = $pair;
	  	}
	  	$i --;
	  }
	  if ($i < 0)
	  {
	  	$err['errMsg'] = "msgLotAbort";
	  	return $err;
	  }
	  //echo "\n-----------i=$i poule=$groupNum-------------\n";
	  //	  $this->_debug($pairs, $groups);
		}
		return $this->updatePairsToGroups($drawId, $pairs, array(), $aGroupName);
	}
	// }}}

	// {{{ _limited
	/**
	 * Limitation des places disponibles pour respecter les
	 * criteres de separation
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _limited($pairCur, $pairTraited, $plage, $groups)
	{
		// Securite: si la paire n'est pas positionnee,
		// ne rien faire
		if ($pairCur['groupNum'] < 0 )
		return $plage;

		// Si la poule est complete
		$group = $groups[$pairCur['groupNum']];
		if ($group['nok']+1 >= $group['rund_size'])
		{
	  if (!in_array($pairCur['groupNum'], $plage['groupsNok']))
	  {
	  	$plage['groupsNok'][] = $pairCur['groupNum'];
	  	$plage['length']++;
	  }
		}

		// Examiner tous les critere de separation
		$nbCriteres = count($pairCur['criteria']);
		for( $i=0; $i < $nbCriteres; $i++)
		{


	  $criteriaCur = $pairCur['criteria'][$i];
	  $criteriaTraited = $pairTraited['criteria'][$i];
	  $critere = $criteriaCur['name'];
	  // Si les criteres sont differents, pas de limitation
	  if ($criteriaCur['value'] != $criteriaTraited['value'])
	  continue;

	  // Groupe de la paire courante
	  $group = $groups[$pairCur['groupNum']];

	  // Mettre a jour le nombre de paire ayant le meme critere
	  // dans cette poule
	  if (isset($plage[$critere][$group['rund_stamp']]))
	  $plage[$critere][$group['rund_stamp']] ++;
	  else
	  $plage[$critere][$group['rund_stamp']] = 1;


	  // Mettre a jour la listes des poules interdites
	  // pour la paire traite
	  //Si la poule est deja dans la liste, c'est fini
	  if (in_array($pairCur['groupNum'], $plage['groupsNok']))
	  break;


	  // Si le nombre max de paires ayant ce critere est atteint
	  // pour ce groupe
	  // Nombre de groupe
	  $nbGroups = count($groups);
	  // Nombre maximum de paire du meme critere dans un groupe
	  $nbMaxPair = floor(($criteriaTraited['nbMax']-1)/$nbGroups) + 1;

	  // Nombre de groupes autorises avec ce maximum de paire du meme
	  // critere dans un groupe
	  $nbGroupWithMax = $criteriaTraited['nbMax']%$nbGroups;
	  if (!$nbGroupWithMax)
	  $nbGroupWithMax = $nbGroups;

	  // Nombre de groupe ayant atteint ce max
	  $nb = 0;
	  foreach($groups  as $gr)
	  {
	  	if (isset($plage[$critere][$gr['rund_stamp']]) &&
		  $plage[$critere][$gr['rund_stamp']]==$nbMaxPair) $nb++;
	  }
	  // Si le nombre de groupe ayant atteint le max est suffisant
	  // on autorise moins de paire du meme critere dans les groups
	  // restants

	  if($nb >= $nbGroupWithMax)
	  $nbMaxPair--;

	  // le nombre max est-il atteint dans les poules
	  $indx = 0;
	  foreach($groups  as $gr)
	  {
	  	if (isset($plage[$critere][$gr['rund_stamp']]) &&
		  $plage[$critere][$gr['rund_stamp']]>=$nbMaxPair &&
		  !in_array($indx, $plage['groupsNok'])	      )
		  {
		  	$plage['groupsNok'][] = $indx;
		  	$plage['length']++;
		  }
		  $indx++;
	  }
		}
		return $plage;
	}
	// }}}


	// {{{ _random
	/**
	 * Tirage au sort une place parmi un tableau de place
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function _random($pairNum, $plage, $groups)
	{
		$groupsNok = $plage['groupsNok'];
		// Liste des poules possible pour la paire
		$list = array();
		$nbGroups = count($groups);
		// Limitation des positions dans les poules :
		// si possible ne pas mettre une paire en troisieme
		// position d'une poule alors qu'il reste des poules
		// avec une seule paire
		$maxPos = floor($pairNum / $nbGroups)+1;
		//      echo "limitation repousse: pairnum=$pairNum;nbGroups=$nbGroups;maxPos=$maxPos\n";
		for($i=0; $i<$nbGroups; $i++)
		{
	  //echo "{$groups[$i]['rund_stamp']}{$groups[$i]['nok']};";
	  if ($groups[$i]['nok'] < $groups[$i]['rund_size'] &&
	  $groups[$i]['nok'] < $maxPos &&
	  (!is_array($groupsNok) ||
	  !in_array($i, $groupsNok)))
	  {
	  	$list[] = $i;
	  	//  echo "--$i--";
	  }
		}
		//      echo "\n";
		$nbPos = count($list);

		// Pas de place de libre : on regarde
		// sans la limitation des positions dans les poules :
		if (!$nbPos)
		for($i=0; $i<$nbGroups; $i++)
		{
	  //echo "{$groups[$i]['rund_stamp']}{$groups[$i]['nok']};";
	  if ($groups[$i]['nok'] < $groups[$i]['rund_size'] &&
	  (!is_array($groupsNok) ||
	  !in_array($i, $groupsNok)))
	  $list[] = $i;
		}
		$nbPos = count($list);

		// Choix du groupe pour cette paire
		if ($nbPos)
		{
	  $sel = mt_rand(0, $nbPos-1);
	  return $list[$sel];
		}
		else
		{
	  return -1;
		}
	}
	// }}}

	function _debug($pairs, $groups)
	{
		$i=0;
		foreach($pairs as $pair)
		{
	  foreach ($pair['criteria'] as $criteria)
	  {
	  	echo "$i Pair:{$pair['id']},";
	  	echo "Criteria={$criteria['name']},";
	  	echo "value={$criteria['value']},";
	  	echo "nbCriteria={$criteria['nbMax']}, pos={$pair['pos']},\n";
	  }
	  $j=0;
	  foreach($pair['plages'] as $plage)
	  {
	  	if (is_array($plage['groupsNok']))
	  	echo "\t$j --".implode(',', $plage['groupsNok'])."\n";
	  	else
	  	echo "\t$j -- --vide--\n";
	  	$j++;
	  }
	  $i++;
		}
		foreach($groups as $group)
		{
	  echo "Stamp={$group['rund_stamp']},nok={$group['nok']}/{$group['rund_size']}\n";
		}
	}

	function _tracePair($pair)
	{
		echo "Pair:{$pair['id']},";
		foreach ($pair['criteria'] as $criteria)
		{
	  echo "Criteria={$criteria['name']},";
	  echo "value={$criteria['value']},";
	  echo "nbCriteria={$criteria['nbMax']}, ";
		}
		echo "pos={$pair['pos']},<br>\n";
	}

}
?>