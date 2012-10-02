<?php
/*****************************************************************************
 !   Module     : utils
 !   File       : $Source: /cvsroot/aotb/badnet/src/utils/utround.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.36 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/03/06 18:02:11 $
 ******************************************************************************/
require_once "utbase.php";
require_once "utko.php";

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

class utRound extends utBase
{

	// {{{ properties
	// }}}

	// {{{ getTiePos
	/**
	 * Return la position du match dans le tour
	 *
	 * @access public
	 * @param  integer  $groupId  Id of the group
	 * @return array   array of users
	 */
	function getTiePos($posRound)
	{
		$pos = $posRound;
		if(!$posRound)
		$pos = '';
		else if($posRound<3)
		$pos = $posRound;
		else if($posRound<7)
		$pos -= 2;
		else if($posRound<15)
		$pos -= 6;
		else if($posRound<31)
		$pos -= 14;
		else if($posRound<63)
		$pos -= 30;

		return $pos;
	}
	//}}}

	// {{{ getTieStep
	/**
	 * Return the label of the tie (final, 1/2 final....)
	 *
	 * @access public
	 * @param  integer  $groupId  Id of the group
	 * @return array   array of users
	 */
	function getTieStep($posTie, $roundType=WBS_ROUND_MAINDRAW)
	{

		if($roundType != WBS_ROUND_GROUP)
		{
	  switch ($posTie)
	  {
	  	case 0:
	  		return WBS_FINAL;
	  	case 1:
	  	case 2:
	  		return WBS_SEMI;
	  	case 3:
	  	case 4:
	  	case 5:
	  	case 6:
	  		return WBS_QUATER;
	  }
	  if (($posTie > 6) &&
	  ($posTie < 15))
	  return WBS_HEIGHT;
	  if (($posTie >= 15) &&
	  ($posTie < 31))
	  return WBS_16;
	  if (($posTie >= 31) &&
	  ($posTie < 63))
	  return WBS_32;
	  return WBS_64;
		}
		else
		{
	  switch ($posTie)
	  {
	  	case 0:
	  		return "1/2";
	  	case 1:
	  		return "1/3";
	  	case 2:
	  		return "2/3";
	  	case 3:
	  		return "1/4";
	  	case 4:
	  		return "2/4";
	  	case 5:
	  		return "3/4";
	  	case 6:
	  		return "1/5";
	  	case 7:
	  		return "2/5";
	  	case 8:
	  		return "3/5";
	  	case 9:
	  		return "4/5";
	  	default :
	  		return "--";
	  }
		}
	}
	// }}}

	// {{{ getRounds
	/**
	 * Return the list of the rounds of a draw
	 *
	 * @access public
	 * @param  integer  $groupId  Id of the group
	 * @return array   array of users
	 */
	function getRounds($drawId = NULL, $type=NULL, $sort=null, $group=null)
	{

		// Select the ties of the group
		$eventId = utvars::getEventId();
		$fields = array('rund_id', 'rund_name', 'rund_stamp', 'rund_type',
		      'rund_drawId', 'rund_size', 'rund_entries', 
		      'rund_qual', 'draw_name', 'rund_del', 'rund_pbl', 'draw_stamp',
				'rund_group', 'rund_pbl', 'rund_rge', 'draw_disci');
		$tables = array('rounds', 'draws');
		$where = " rund_drawId = draw_id".
	" AND draw_eventId = $eventId";
		if ($drawId != NULL) $where .= " AND draw_id = $drawId";
		if ($type != NULL) $where .= " AND rund_type = $type";
		if ($group != NULL) $where .= " AND rund_group ='" . $group . "'";
		if(is_null($sort)) $order = "draw_id, rund_name";
		else $order = $sort;
		$res = $this->_select($tables, $fields, $where, $order, 'rund');
		$trans = array('rund_name', 'rund_stamp');
		$rounds = array();
		while ($round = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$round = $this->_getTranslate('rounds', $trans,
			$round["rund_id"], $round);
			$rounds[] = $round;
		}
		return $rounds;
	}
	// }}}

	// {{{ getRoundDef
	/**
	 * Return the fields of the round
	 *
	 * @access public
	 * @param  integer  $rundId  Id of the group
	 * @return array   array of users
	 */
	function getRoundDef($roundId)
	{

		// Select the ties of the group
		$fields = array('*');
		$tables = array('rounds', 'draws');
		$where = " draw_id=rund_drawid AND rund_id = '$roundId'";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows()) return false;

		// Translate the fields
		$round = $res->fetchRow(DB_FETCHMODE_ASSOC);

		$trans = array('rund_name', 'rund_stamp', 'rund_group');
		$round = $this->_getTranslate('rounds', $trans,
		$round["rund_id"], $round);
		return $round;
	}
	// }}}


	// {{{ getMatchs
	/**
	 * Return the list of the matchs of the round
	 *
	 * @access public
	 * @param  integer  $roundId  Id of the group
	 * @param  boolean  $full     do you want all ties or only tie with no by
	 * @return array   array of users
	 */
	function getMatchs($roundId, $full=true)
	{

		// Select the ties of the group
		$fields = array('tie_id', 'tie_step', 'tie_schedule',
		      'tie_place', 'tie_court', 'tie_posRound',
		      'tie_isBye', 'mtch_num', 'tie_pbl', 'tie_del',
		       'mtch_id'
		       );
		       $tables = array('ties','matchs');
		       $where = " tie_roundId = $roundId ".
	" AND mtch_tieId = tie_id";
		       if ($full == false)
		       $where .= " AND tie_isBye=0";
		       $order = "tie_posRound";
		       $ties = $this->_select($tables, $fields, $where, $order);
		       $trans = array('tie_place', 'tie_step', 'tie_court');
		       $utd = new utDate();
		       $rows=array();
		       while ($tie = $ties->fetchRow(DB_FETCHMODE_ASSOC))
		       {
		       	// Format the time
		       	$utd->setIsoDateTime($tie['tie_schedule']);
		       	$tie['tie_schedule'] = $utd->getDateTime();

		       	// translate the data
		       	$tie = $this->_getTranslate('ties', $trans, $tie['tie_id'], $tie);
		       	$rows[] = $tie;
		       }
		       return $rows;
	}
	// }}}

	// {{{ getTies
	/**
	 * Return the list of the ties of the round
	 *
	 * @access public
	 * @param  integer  $roundId  Id of the group
	 * @param  boolean  $full     do you want all ties or only tie with no by
	 * @return array   array of users
	 */
	function getTies($roundId, $full=true)
	{

		// Select the ties of the group
		$fields = array('tie_id', 'tie_step', 'tie_schedule',
		      'tie_place', 'tie_court', 'tie_posRound',
		      'tie_isBye', 'tie_pbl', 'tie_del', 'tie_looserdrawid'
		);
		$tables = array('ties','matchs');
		$tables = array('ties');
		$where = " tie_roundId = $roundId ";
		if ($full == false)
		$where .= " AND tie_isBye=0";
		$order = "tie_posRound";
		$ties = $this->_select($tables, $fields, $where, $order, 'tie');
		$trans = array('tie_place', 'tie_step', 'tie_court');
		$utd = new utDate();
		$rows=array();
		while ($tie = $ties->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  // Format the time
	  $utd->setIsoDateTime($tie['tie_schedule']);
	  $tie['tie_schedule'] = $utd->getDateTime();

	  // translate the data
	  $tie = $this->_getTranslate('ties', $trans, $tie['tie_id'], $tie);
	  $rows[] = $tie;
		}
		return $rows;
	}
	// }}}

	// {{{ getTieDef
	/**
	 * Return the definition of the ties of the round
	 *
	 * @access public
	 * @param  integer  $roundId  Id of the group
	 * @param  boolean  $full     do you want all ties or only tie with no by
	 * @return array   array of users
	 */
	function getTieDef($roundId)
	{

		// Select the ties of the group
		$fields = array('*');
		$tables = array('ties');
		$where = " tie_roundId = $roundId";
		$order = "tie_posRound";
		$ties = $this->_select($tables, $fields, $where, $order);

		$trans = array('tie_place', 'tie_step', 'tie_court');
		$tie = $ties->fetchRow(DB_FETCHMODE_ASSOC);
		// translate the data
		$tie = $this->_getTranslate('ties', $trans, $tie['tie_id'], $tie);
		return $tie;
	}
	// }}}

	// {{{ delRound
	/**
	 * @brief
	 * @~english Delete a round and all associated ties and matchs
	 * @~french Supprime un tour (Round) et tous les matchs et rencontres associ�s
	 *
	 * @par Description:
	 * @~french L'utilisation de cette m�thode permet de supprimer un tour (round)
	 * et tous les enregistrements associ�s en cascade : rencontres (tie), match (matchs) e
	 * les liens entre les matchs et les �quipes s'ils existent. Les enregistrements
	 * sont supprim�s physiquement de la base. Il n'y a pas possibilit� de les restaurer.
	 *
	 * @~english
	 * See french doc.
	 *
	 * @param  $roundId  (integer)    @~french Identifiant du tour (rund_id)
	 *         @~english Round id (database field rund_id)
	 * @return @~french En cas d'erreur, un tableau avec un �l�ment 'errMsg', sinon Tru
	 *         @~english True or table with error msg 'errMsg'
	 *
	 * @~
	 * @see utRound, update
	 * @par Exemple:
	 * @code
	 *    $kpage = new Page('Sample');
	 *    $utr = new utRound();
	 *    $res = $utr->delRound(56);
	 *    if (is_array($res))
	 *       $kpage->addWng($res['errMsg']);
	 * @endcode
	 */
	function delRound($roundId)
	{
		// Select the ties of the round
		$fields[] = 'tie_id';
		$tables[] = 'ties';
		$where = "tie_roundId = $roundId ";
		$res = $this->_select($tables, $fields, $where);

		// delete the ties of the round
		while ($tie = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $this->_delTie($tie['tie_id']);
		}

		// Delete the round
		$where = "rund_id = $roundId ";
		$res = $this->_delete('rounds', $where);

		// Delete the relation between pairs/team and round
		$where = "t2r_roundId = $roundId ";
		$res = $this->_delete('t2r', $where);
		return true;
	}
	// }}}

	// {{{ updateRound
	/**
	 * @brief
	 * @~english Update a round and generate all associated ties and matchs
	 * @~french Met a jour un tour (Round) et tous les matchs et rencontres
	 * associ�s
	 *
	 * @par Description:
	 * @~french L'utilisation de cette m�thode permet de cr�er ou de mettre 
	 * � jour un tour (round) et tous les enregistrements associ�s en cascade :
	 * rencontres (tie) et match (matchs). Suivant le type de tournoi, un tour
	 * peu d�signer :
	 * @li une poule d'un tableau dans un tournoi individuel
	 * @li un tour dans un tableau dans un tournoi individuel (ex: huiti�me 
	 * de finale
	 * @li une poule dans un tournoi par equipe
	 * @li un tour dans un tournoi par �quipe (ex: demi finale des playoff)
	 *
	 * @~english
	 * See french doc.
	 *
	 * @param  $infos  (array)    @~french Tableau associatif contenant les
	 * sp�cifications du tour.
	 *  @li rund_id: identifiant du tour. S'il vaut -1, un nouveau tour est
	 *  cr��. Sinon le tour existant est mis � jour.
	 *  @li rund_name : nom du tour
	 *  @li rund_stamp : sigle du tour
	 *  @li rund_size : nombre total de places dans le tour
	 *  @li rund_entries : nombre pour le tour. Inf�rieur ou �gal � rund_size
	 *  @li rund_byes : nombre de place vacantes dans le tour
	 *  @li rund_qualPlace : nombre de place r�serv�es pour les qualifi�s. 
	 *  On doit toujours avoir :\n rund_size = rund_entries + rund_byes.
	 *
	 *  @li rund_qual : nombre de joueur qualifi�s (si le tour est une poule)
	 *  @li rund_type: type du tour. Les valeurs possibles sont :\n
	 *	WBS_GROUP : poule pour un tournoi individuel\n
	 *    WBS_KO:       �limination directe pour un tournoi individuel\n
	 *    WBS_QUALIF :  qualification pour un tournoi individuel\n
	 *    WBS_TEAM_GROUP: poule pour un tournoi par �quipe\n
	 *    WBS_TEAM_BACK: poule pour un tournoi par �quipe avec rencontre 
	 *    aller/retour\n
	 *    WBS_TEAM_KO:  �limination directe pour un tournoi par �quipe\n
	 *    WBS_TEAM_BACK :  qualification pour un tournoi individuel\n
	 *	WBS_SWISS: ronde suisse\n
	 *  @li rund_rankType : type de calcul utilis� pour le classement quand 
	 *  le tour est de type WBS_TEAM_GROUP:\n
	 *    WBS_CALC_RESULT : Deux equipes � egalit�s sont d�partag�es suivant
	 *    le resultat de la rencontre qui les a oppos�es.\n
	 *    WBS_CALC_RANK : Deux �quipes � �galit�s sont departag�es � la 
	 *     diff�rences de matchs gagn�, puis de set, puis de point.
	 *  @li tie_nbms : nombre de simple homme
	 *  @li tie_nbws : nombre de simple dame
	 *  @li tie_nbmd : nombre de double homme
	 *  @li tie_nbwd : nombre de double dame
	 *  @li tie_nbxd : nombre de double mixte
	 *         @~english Table with the specification of the round.
	 * @return @~french En cas d'erreur, un tableau avec un �l�ment 'errMsg',
	 *   sinon l'id du tour
	 *         @~english Tour Id or table with error msg 'errMsg'
	 *
	 * @~
	 * @see utRound, update
	 * @par Exemple:
	 * @code
	 *    $kpage = new Page('Sample');
	 *    $utr = new utRound();
	 *    $res = $utr->delRound(56);
	 *    if (is_array($res))
	 *       $kpage->addWng($res['errMsg']);
	 * @endcode
	 */
	function updateRound($rund, $tie)
	{
		$rund['rund_id'] = $this->updateRoundDef($rund);
		$rund = $this->getRoundDef($rund['rund_id']);
		// Calculate the number of ties for the group
		$nbTies = 0;
		switch ($rund['rund_type'])
		{
			case WBS_GROUP:
			case WBS_TEAM_GROUP:
			case WBS_ROUND_GROUP:
				for ($i=0; $i<$rund['rund_size']; $i++) $nbTies += $i;
				$firstTie = 0;
				break;
			case WBS_KO:       // dans ce cas 'rund_qual' vaut 1
			case WBS_TEAM_KO:  // dans ce cas 'rund_qual' vaut 1
			case WBS_ROUND_QUALIF:
			case WBS_ROUND_MAINDRAW:
			case WBS_ROUND_THIRD:
			case WBS_ROUND_CONSOL:
			case WBS_ROUND_PLATEAU:
				case WBS_QUALIF:
				$nbTies = $rund['rund_size']-$rund['rund_qual'];
				if($nbTies==0) $nbTies = 1;
				$firstTie = $rund['rund_qual']-1;
				$utk = new utko($rund['rund_entries']);
				$byes = $utk->getByesTie();
				$size = $rund['rund_size'];
				break;
			case WBS_TEAM_BACK:
				for ($i=0; $i<$rund['rund_size']; $i++) $nbTies += $i;
				$nbTies *=2;
				$firstTie = 0;
				break;
			case WBS_SWISS:
			default:
				$err['errMsg']=
	    "Type de round non implemente :{$rund['rund_type']}";
				return $err;
				break;
		}

		/* Update ties  */
		$fields = array('tie_id', 'tie_posRound');
		$tables = array('ties');
		$position = -1;
		$tie['tie_roundId']= $rund['rund_id'];
		$tie['tie_catage']= $rund['draw_catage'];
		for ($i=0; $i<$nbTies; $i++)
		{
			$position = $i+$firstTie;
			$where = "tie_roundId=".$rund['rund_id'].
    			" AND tie_posRound=".$position;
			$res = $this->_select($tables, $fields, $where);
			$tie['tie_id']=-1;
			if ($res->numRows())
			{
				$data = $res->fetchRow(DB_FETCHMODE_ASSOC);
				$tie['tie_id']=$data['tie_id'];
			}
			$tie['tie_posRound'] = $position;
			if (isset($byes) && in_array($position, $byes))
			{
				$tie['tie_isBye'] = 1;
				$tie['tie_place'] = '';
				$tie['tie_schedule'] = '';
			}
			else
			{
				$tie['tie_isBye'] = 0;
				unset($tie['tie_place']);
				unset($tie['tie_schedule']);
			}

			if ($rund['rund_type'] != WBS_TEAM_GROUP &&
			$rund['rund_type'] != WBS_TEAM_BACK &&
			$rund['rund_type'] != WBS_TEAM_KO)
			$tie['tie_step'] = $this->_getTieStep($position,
			$rund['rund_type'],
			$rund['rund_size']);
			$res = $this->_updateTie($tie);
			if (is_array($res)) return $res;
		}
		/* Delete additionnal ties */
		$where = "tie_roundId=".$rund['rund_id'].
			" AND (tie_posRound >$position".
			" OR tie_posRound < $firstTie)";
		$res = $this->_select($tables, $fields, $where);
		while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->_delTie($data['tie_id']);
		}

		/* Delete relation with last teams */
		$where = "t2r_roundId=".$rund['rund_id'].
	" AND t2r_posRound > ".$rund['rund_size'];
		//" AND t2r_posRound > ".$rund['rund_entries'];
		$res = $this->_delete('t2r', $where);
		return $rund['rund_id'];
	}
	// }}}

	// {{{ _getTieStep
	/**
	 * Return the label of the tie (final, 1/2 final....)
	 *
	 * @access public
	 * @param  integer  $groupId  Id of the group
	 * @return array   array of users
	 */
	function _getTieStep($posTie, $roundType, $size)
	{

		if($roundType!=WBS_ROUND_GROUP)
		{
	  if ($posTie == 0)
	  $tmp = 1;
	  elseif (($posTie == 1) ||
	  ($posTie == 2))
	  $tmp = 2;
	  else if (($posTie >=3) &&
	  ($posTie < 7))
	  $tmp = 3;
	  else if (($posTie >= 7) &&
	  ($posTie < 15))
	  $tmp = 4;
	  else if (($posTie >= 15) &&
	  ($posTie < 31))
	  $tmp = 5;
	  else if (($posTie >= 31) &&
	  ($posTie < 63))
	  $tmp = 6;
	  else
	  $tmp = 7;
	  $max = 1;
	  while(($size/=2) > 1) $max++;
	  return $max-$tmp;
		}
		else
		{
	  if ($size == 3) $places = array(3,2,1);
	  else if ($size == 4) $places = array(3,1,2,2,1,3);
	  else if ($size == 5) $places = array(5,4,1,2,3,5,3,4,2,1);
	  if (isset($places[$posTie])) return($places[$posTie]);
	  // Ca pas normal sauf pour des poules > 5
	  else return($posTie);
		}
	}
	// }}}

	// {{{ updateRoundDef
	/**
	 * Update the round definition the database
	 *
	 * @private
	 */
	function updateRoundDef($infos)
	{
		// For KO, the size must be pow(2,x)
		if ($infos['rund_type'] == WBS_KO ||
		$infos['rund_type'] == WBS_ROUND_MAINDRAW ||
		$infos['rund_type'] == WBS_ROUND_QUALIF ||
		$infos['rund_type'] == WBS_ROUND_THIRD ||
		$infos['rund_type'] == WBS_ROUND_CONSOL ||
		$infos['rund_type'] == WBS_TEAM_KO ||
		$infos['rund_type'] == WBS_QUALIF)
		{
	  if ($infos['rund_entries'] === 1)
	  $infos['rund_size'] = 1;
	  elseif ($infos['rund_entries'] <= 2)
	  $infos['rund_size'] = 2;
	  elseif ($infos['rund_entries'] <= 4)
	  $infos['rund_size'] = 4;
	  elseif ($infos['rund_entries'] <= 8)
	  $infos['rund_size'] = 8;
	  elseif ($infos['rund_entries'] <= 16)
	  $infos['rund_size']= 16;
	  elseif ($infos['rund_entries'] <= 32)
	  $infos['rund_size'] = 32;
	  elseif ($infos['rund_entries'] <= 64)
	  $infos['rund_size'] = 64;
	  else
	  $infos['rund_size'] = 64;
	  $infos['rund_byes'] = $infos['rund_size'] -
	  $infos['rund_entries'];
		}

		/* Create round */
		if ($infos['rund_id'] == -1)
		{
	  unset($infos['rund_id']);
	  $res = $this->_insert('rounds', $infos);
	  $infos['rund_id'] = $res;
		}


		/* Translate fields */
		$trans = array('rund_name','rund_stamp');
		$infos = $this->_updtTranslate('rounds', $trans,
		$infos['rund_id'], $infos);


		/* Updating round */
		$where = "rund_id =".$infos['rund_id'];
		$res = $this->_update('rounds', $infos, $where);
		return $infos['rund_id'];
	}
	// }}}

	// {{{ deletePairToMatch
	/**
	 * Delete the relation between the pairs and the matchs
	 * of a round
	 *
	 * @access public
	 * @param  string  $info   column of the pair
	 * @return mixed
	 */
	function deletePairToMatch($roundId)
	{
		// Find the matchs of the round (for KO)
		$fields = array('mtch_id');
		$tables = array('p2m', 'matchs', 'ties');
		$where = "tie_id=mtch_tieId".
	" AND tie_roundId=$roundId".
	" AND p2m_matchId=mtch_id".
	" AND (p2m_result =".WBS_RES_NOPLAY.
	" OR tie_isBye = 1)";
		$order = 'mtch_num';
		$res = $this->_select($tables, $fields, $where,$order);
		if ($res->numRows())
		{
	  while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
	  {
	  	$matchIds[] = $match['mtch_id'];
	  }

	  // Delete relation between pairs and match
	  $ids = implode(',', $matchIds);
	  $where = 'p2m_matchId IN ('.$ids.')';
	  $res = $this->_delete('p2m', $where);
		}
		return true;
	}
	// }}}

	// {{{ createPairToMatch
	/**
	 * Create the relation between the pairs and the matchs
	 * of a round
	 *
	 * @access public
	 * @param  string  $info   column of the pair
	 * @return mixed
	 */
	function createPairToMatch($roundId)
	{
		// Find the pair id in the round
		$fields = array('t2r_pairId', 't2r_posRound');
		$tables = array('t2r');
		$where = "t2r_roundId = $roundId";
		$order = " t2r_posRound";
		$res = $this->_select($tables, $fields, $where, $order);
		$pairs = array();
		while ($pair = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$pairs[] = $pair;

		if (!count($pairs)) return;

		// Use utKo to obtain real position of the pair
		// in the round and byes position
		$round = $this->getRoundDef($roundId);
		$utk = new utko($pairs, $round['rund_entries']);
		$byes  = $utk->getByesTie();
		$start  = ($utk->getSize()/2)-1;

		// Find the matchs of the round (for KO)
		$fields = array('mtch_id', 'tie_posRound');
		$tables = array('matchs', 'ties');
		$where = "tie_id=mtch_tieId".
	" AND tie_roundId=$roundId";
		$order = 'tie_posRound DESC';
		$res = $this->_select($tables, $fields, $where, $order);
		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		$matchIds[$match['tie_posRound']] = $match['mtch_id'];

		// Create relation between pair and match
		foreach($pairs as $pair)
		{
	  		$pairId = $pair['t2r_pairId'];
	  		$numTie = intval($start+($pair['t2r_posRound']-1)/2);
	  		$posInMatch = ($pair['t2r_posRound']%2) ? WBS_PAIR_TOP:WBS_PAIR_BOTTOM;

	  		$fields = array();
	  		$fields['p2m_pairId'] = $pairId;
	  		// There is bye, so create relation between
	  		// pair and match for the next match
	  		if(in_array($numTie, $byes))
	  		{
	  			$nextTie = intval(($numTie-1)/2);
	  			$fields['p2m_posMatch'] = ($numTie%2) ? WBS_PAIR_TOP:WBS_PAIR_BOTTOM;
	  			$fields['p2m_matchId'] = $matchIds[$nextTie];
	  			$mids[] = $matchIds[$nextTie];
	  			
	  			$where = 'p2m_posMatch=' . $fields['p2m_posMatch'];
	  			$where .= " AND p2m_matchId=" . $fields['p2m_matchId']; 
	  			$p2mId = $this->_selectFirst('p2m', 'p2m_id', $where);
	  			if (empty($p2mId))
	  			{
	  				$fields['p2m_result'] = WBS_RES_NOPLAY;
	  				$res = $this->_insert('p2m', $fields);
	  			}
	  			else $this->_update('p2m', $fields, $where);
	  			$fields['p2m_result'] = WBS_RES_WIN;
	  		}

	  		$fields['p2m_posMatch'] = $posInMatch;
	  		$fields['p2m_matchId'] = $matchIds[$numTie];
	  		$mids[] = $matchIds[$numTie];

	  		$where = 'p2m_posMatch=' . $fields['p2m_posMatch'];
	  		$where .= " AND p2m_matchId=" . $fields['p2m_matchId']; 
	  		$p2mId = $this->_selectFirst('p2m', 'p2m_id', $where);
	  		if (empty($p2mId))
	  		{
	  			if (empty($fields['p2m_result']) ) $fields['p2m_result'] = WBS_RES_NOPLAY;
	  			$res = $this->_insert('p2m', $fields);
	  		}
	  		else $this->_update('p2m', $fields, $where);
		}

		// Update the status of the matches
		// Count the pairs of the matches
		$fields = array( 'count(*) as nb', 'p2m_matchId');
		$tables = array('p2m', 'matchs');
		$where = 'p2m_matchId IN ('.implode(',', $mids).')'.
		" AND mtch_id=p2m_matchId".
		' AND mtch_status =' . WBS_MATCH_INCOMPLETE.
		" GROUP BY p2m_matchId";
		$res = $this->_select($tables, $fields, $where);

		// Select match with 2 pairs
		$matchIds = array();
		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  		if ($match['nb'] == 2)
	  		$matchIds[] = $match['p2m_matchId'];
		}

		// Update the status of match with 2 pairs
		if (count($matchIds))
		{
	  		$fields = array();
	  		$fields['mtch_status'] = WBS_MATCH_READY;
	  		$where = "mtch_id IN(".implode(',', $matchIds).')';
	  		$res = $this->_update('matchs', $fields, $where);
		}
		return true;
	}
	// }}}

	//-----------------------------------------
	//  Here are private methode
	//------------------------------------------

	/**
	 * Update or create a tie
	 *
	 * @private
	 */
	function _updateTie($infos)
	{
		$data = $infos;
		unset($infos['tie_catage']);
		if ($infos['tie_id'] == -1)
		{
			/* Create a tie */
			unset($infos['tie_id']);
			$res = $this->_insert('ties', $infos);
			$data['tie_id'] = $res;
		}
		else
		{
			/* Updating the tie */
			$where = "tie_id =".$infos['tie_id'];
			$res = $this->_update('ties', $infos, $where);
		}
		$res = $this->_matchForTie($data);
		if (isset($res['errMsg']))
		return $err;

		return true;
	}

	/**
	 * Update or create a match
	 *
	 * @private
	 */
	function _matchForTie($infos)
	{
		// Initialize field
		$matchs['tie_id'] = $infos['tie_id'];
		$matchs['match_catage'] = $infos['tie_catage'];

		// Men's singles
		$matchs['match_discipline'] = WBS_MS;
		$matchs['group_nbmatch'] = $infos['tie_nbms'];
		$matchs['group_order'] = 1;
		$matchs['group_rank'] = 1;
		$res = $this->_updateMatchs($matchs);
		if (isset($res['errMsg']))
		return $res;

		// Women's singles
		$matchs['match_discipline'] = WBS_LS;
		$matchs['group_nbmatch'] = $infos['tie_nbws'];
		$matchs['group_order'] = 1;
		$matchs['group_rank'] += $infos['tie_nbms'];
		$res = $this->_updateMatchs($matchs);
		if (isset($res['errMsg']))
		return $res;

		// Men's doubles
		$matchs['match_discipline'] = WBS_MD;
		$matchs['group_nbmatch'] = $infos['tie_nbmd'];
		$matchs['group_order'] = 1;
		$matchs['group_rank'] += $infos['tie_nbws'];
		$res = $this->_updateMatchs($matchs);
		if (isset($res['errMsg']))
		return $res;

		// Women's doubles
		$matchs['match_discipline'] = WBS_LD;
		$matchs['group_nbmatch'] = $infos['tie_nbwd'];
		$matchs['group_order'] = 1;
		$matchs['group_rank'] += $infos['tie_nbmd'];
		$res = $this->_updateMatchs($matchs);
		if (isset($res['errMsg']))
		return $res;

		// Mixed
		$matchs['match_discipline'] = WBS_MX;
		$matchs['group_nbmatch'] = $infos['tie_nbxd'];
		$matchs['group_order'] = 1;
		$matchs['group_rank'] += $infos['tie_nbwd'];
		$res = $this->_updateMatchs($matchs);
		if (isset($res['errMsg']))
		return $res;

	 // Simple melange
		$matchs['match_discipline'] = WBS_AS;
		$matchs['group_nbmatch'] = $infos['tie_nbas'];
		$matchs['group_order'] = 1;
		$matchs['group_rank'] += $infos['tie_nbxd'];
		$res = $this->_updateMatchs($matchs);
		if (isset($res['errMsg']))
		return $res;

		return true;
	}

	/**
	 * Update or create a matchs for a discipline
	 *
	 * @private
	 */
	function _updateMatchs($infos)
	{
		//Retieve existing matchs
		$fields[] = 'mtch_id';
		$tables[] = 'matchs';
		$where = "mtch_tieId=".$infos['tie_id'].
	" AND mtch_discipline=".$infos['match_discipline'];
		$res = $this->_select($tables, $fields, $where);

		// Treat existing match
		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  if ($infos['group_nbmatch'])
	  {
	  	$infos['match_id'] = $match['mtch_id'];
	  	$this->_updateMatch($infos);
	  	$infos['group_nbmatch']--;
	  	$infos['group_order']++;
	  	$infos['group_rank']++;
	  }
	  else
	  {
	  	$this->_delMatch($match['mtch_id']);
	  }
		}

		// Now create additional match
		$infos['match_id'] = -1;
		for ($i=0; $i < $infos['group_nbmatch']; $i++)
		{
	  $res = $this->_updateMatch($infos);
	  if (isset($res['errMsg']))
	  return $res;
	  $infos['group_order']++;
	  $infos['group_rank']++;
		}

		return true;
	}
	
	// }}}

	/**
	 * Update or create a match
	 *
	 * @private
	 */
	function _updateMatch($infos)
	{
		$fields['mtch_order']   = $infos['group_order'];
		$fields['mtch_rank']    = $infos['group_rank'];
		$fields['mtch_catage']  = $infos['match_catage'];
		if ($infos['match_id'] == -1)
		{
	  /* Create a match */
	  unset($infos['match_id']);
	  $fields['mtch_tieId']      = $infos['tie_id'];
	  $fields['mtch_discipline'] = $infos['match_discipline'];
	  switch($fields['mtch_discipline'])
	  {
	  	case WBS_MS:
	  	case WBS_WS:
	  	case WBS_AS:
	  		$fields['mtch_disci'] = WBS_SINGLE;
	  		break;
	  	case WBS_MD:
	  	case WBS_WD:
	  		$fields['mtch_disci'] = WBS_DOUBLE;
	  		break;
	  	case WBS_XD:
	  		$fields['mtch_disci'] = WBS_MIXED;
	  		break;
	  }
	  $fields['mtch_status']     = WBS_MATCH_INCOMPLETE;
	  $res  = $this->_insert('matchs', $fields);
	  $matchId = $res;
	  $fields['mtch_num'] = $matchId;
		}
		else
		{
	  $matchId = $infos['match_id'];
		}

		/* t3f Decembre 2005 */
		/* c'est en commentaire, je ne sais pas pourquoi!!! */
		/*
		 $where = "mtch_id =$matchId";
		 $res  = $this->_update('matchs', $fields, $where);
		 */
		return true;
	}

	/**
	 * Delete a tie
	 *
	 * @private
	 */
	function _delTie($tieId)
	{
		// Delete the matches
		$fields[] = 'mtch_id';
		$tables[] = 'matchs';
		$where = "mtch_tieId = $tieId ";
		$res = $this->_select($tables, $fields, $where);
		while ($match = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $this->_delMatch($match['mtch_id']);
		}

		// Delete the relations with team
		$where = "t2t_tieId = $tieId ";
		$res = $this->_delete('t2t', $where);

		// Delete the tie
		$where = "tie_id = $tieId ";
		$res = $this->_delete('ties', $where);

		return true;

	}

	/**
	 * Delete a match
	 *
	 * @private
	 */
	function _delMatch($matchId)
	{
		$where = "p2m_matchId = $matchId ";
		$res = $this->_delete('p2m', $where);

		$where = "mtch_Id = $matchId ";
		$res = $this->_delete('matchs', $where);

		return true;
	}

}

?>
