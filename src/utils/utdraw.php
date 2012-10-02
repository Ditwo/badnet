<?php
/*****************************************************************************
 !   Module     : utils
 !   File       : $Source: /cvsroot/aotb/badnet/src/utils/utdraw.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.32 $
 !   Author     : G.CANTEGRIL
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/01/24 07:34:17 $
 !   Mailto     : cage@free.fr
 ******************************************************************************/
require_once "utround.php";


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
 * @li utMail    Envoi de mail
 * @li utRound   Gestion des donn�es  round , tie et match
 * @li utScore   Manipulations des scores
 *
 * @author Gerard CANTEGRIL
 */

/**
 * @~english See french doc
 *
 * @~french Cette classe permet d'ajouter, de supprimer et d'acceder aux
 * tableaux (draws) dans la base de donn�es.
 * Pour rappel, un tableau (draw) est constitu� de tour (round); un tour
 * contient des rencontres (tie) et pour chaque rencontre on pr�cise
 * le nombre de match de chaque discipline (simple homme, simple dame,
 * double homme double dame et double mixte). Cette architecture  permet
 * de g�rer de la m�me mani�re les tournois individuels et les comp�titions
 * par �quipe.
 *
 * @author Gerard CANTEGRIL
 *
 */

class utDraw extends utBase
{

	// {{{ properties
	// }}}


	// {{{ getFreePlayers
	/**
	 * Retourne la liste des joueurs sans partenaire
	 *
	 * @access public
	 * @param  integer  $groupId  Id of the group
	 * @return array   array of users
	 */
	function getFreePlayers($drawId, $gender, $except = false, $aEncode = false)
	{
		$pairs = array();
		if ($drawId == -1)
		return $pairs;

		// Select all the draws
		$tables = array('pairs', 'i2p', 'registration', 'members');
		$fields = array('pair_id', 'regi_longName', 'count(*) as nb',
		      'mber_sexe');
		$where = " pair_drawId = $drawId".
	" AND pair_id = i2p_pairId".
	" AND i2p_regiId = regi_id".
	" AND regi_memberId = mber_id";
		if ($except)
		$where .= " AND regi_id != $except";
		$where .= " GROUP BY pair_id";
		$order = "pair_id, regi_longName";
		$res = $this->_select($tables, $fields, $where, $order);

		while ($pair = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  if ($pair['nb'] == 1 && $pair['mber_sexe'] == $gender)
	  {
	  	if ($aEncode)
	  	$pairs[$pair['pair_id']] = utf8_encode($pair['regi_longName']);
	  	else
	  	$pairs[$pair['pair_id']] = $pair['regi_longName'];
	  }
		}
		return $pairs;
	}
	// }}}



	// {{{ getDraw
	/**
	 * Return the column of a draw
	 *
	 * @access public
	 * @param  string  $serial   serial of the draw
	 * @param  string  $disci    discipline of the draw
	 * @return array   information of the user if any
	 */
	function getDraw($serial, $disci)
	{
		$eventId = utvars::getEventId();
		$where = "draw_serial = '$serial'".
	" AND draw_eventId = $eventId ".
	" AND draw_disci = '$disci'";
		$res = $this->_select('draws', 'draw_id', $where, false, 'draw');
		if ($res->numRows())
		$draw = $res->fetchRow(DB_FETCHMODE_ASSOC);

		$spec = $this->_getDrawSpec($draw["draw_id"]);

		return $spec;
	}
	// }}}

	// {{{ getDrawById
	/**
	 * Return the column of a draw
	 *
	 * @access public
	 * @param  integer $id   id of the draw
	 * @return array   information of the user if any
	 */
	function getDrawById($drawId)
	{
		$draw = $this->_getDrawSpec($drawId);
		return $draw;
	}
	// }}}

	// {{{ _getDrawSpec
	/**
	 * Return the specification of a draw
	 *
	 * @access private
	 * @param  string  $drawId   id of the draw
	 * @return array   information of the user if any
	 */
	function _getDrawSpec($drawId)
	{
		if ($drawId == '')$drawId = -1;
		// Retrieve draw's data
		$fields = array('draw_id', 'draw_serial', 'draw_name',
		      'draw_stamp', 'draw_disci', 'draw_type',
		      'draw_del', 'draw_pbl', 'draw_catage', 'draw_numcatage',
		      'draw_rankdefId', 'draw_discipline');
		$where  = "draw_id = '$drawId'";
		$res = $this->_select('draws', $fields, $where, false, 'draw');
		if ($res->numRows())
		{
	  $draw = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  $trans = array('draw_serial', 'draw_name','draw_stamp');
	  $draw = $this->_getTranslate('draws', $trans, $drawId, $draw);
		}
		else
		{
	  $draw['draw_id'] = -1;
	  $draw['draw_serial'] = '';
	  $draw['draw_name'] = '';
	  $draw['draw_stamp'] = '';
	  $draw['draw_disci'] = WBS_MS;
	  $draw['draw_discipline'] = WBS_SINGLE;
	  $draw['draw_type'] = WBS_KO;
	  $draw['draw_catage'] = WBS_CATAGE_SEN;
	  $draw['draw_numcatage'] = 0;
	  $draw['draw_rankdefId'] = '';
	  $draw['draw_pbl'] = WBS_DATA_PRIVATE;
		}

		$fields = array('*');
		$tables = array('rounds', 'draws');
		$where = "rund_drawId = $drawId".
	" AND rund_drawId = draw_id";
		$order = "rund_type, rund_size desc, rund_name";
		$res = $this->_select($tables, $fields, $where, $order);
		$draw['draw_nbp3']  = 0;
		$draw['draw_nbp4']  = 0;
		$draw['draw_nbp5']  = 0;
		$draw['draw_nbs3']  = 1;
		$draw['draw_nbs4']  = 2;
		$draw['draw_nbs5']  = 0;
		$draw['draw_nbpl']  = 0;
		$draw['draw_nbq']   = 0;
		$draw['draw_nbplq'] = 0;
		$draw['draw_third'] = false;
		$draw['draw_nbGroupPlaces'] = 0;
		while ($round = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  switch ($round["rund_type"])
	  {
	  	case WBS_GROUP :
	  	case WBS_ROUND_GROUP :
	  		$draw['draw_nbGroupPlaces'] += $round['rund_size'];
	  		if($round['rund_size']==3)
	  		{
	  			$draw['draw_nbp3']++;
	  			$draw['draw_nbs3'] = $round['rund_qual'];
	  		}
	  		if($round['rund_size']==4)
	  		{
	  			$draw['draw_nbp4']++;
	  			$draw['draw_nbs4']=$round['rund_qual'];
	  		}
	  		if($round['rund_size']==5)
	  		{
	  			$draw['draw_nbp5']++;
	  			$draw['draw_nbs5']=$round['rund_qual'];
	  		}
	  		break;
	  	case WBS_QUALIF :
	  	case WBS_ROUND_QUALIF :
	  		$draw['draw_nbplq'] += $round['rund_entries'];
	  		$draw['draw_nbq']++;
	  		break;
	  	case WBS_ROUND_CONSOL :
	  	case WBS_ROUND_PLATEAU :
	  		$draw['draw_nbq']++;
	  		break;
	  	case WBS_KO :
	  	case WBS_ROUND_MAINDRAW :
	  		$draw['draw_nbpl'] = $round['rund_entries'];
	  		break;
	  	case WBS_ROUND_THIRD :
	  		$draw['draw_third']  = true;
	  		break;
	  	default :
	  		break;
	  }
		}

		$draw['draw_nbPairs']= '0';
		if ($drawId != -1)
		{
	  $fields = array('count(*) as nb', 'mber_sexe');
	  $tables = array('pairs' ,'i2p', 'registration', 'members');
	  $where = "pair_drawId = $drawId".
	    " AND i2p_pairId = pair_id".
	    " AND i2p_regiId = regi_id".
	    " AND regi_memberId = mber_id".
	    " GROUP BY mber_sexe";
	  $order = "mber_sexe";
	  $res = $this->_select($tables, $fields, $where, $order);
	  $nb = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  if ($draw['draw_disci'] == WBS_XD)
	  {
	  	$men = $nb['nb'];
	  	$nb = $res->fetchRow(DB_FETCHMODE_ASSOC);
	  	if($nb)
	  	$women = $nb['nb'];
	  	else
	  	$women = 0;
	  	if ($nb['mber_sexe'] == WBS_MALE)
	  	{
	  		$tmp = $men;
	  		$men = $women;
	  		$women = $tmp;
	  	}
	  	$alone = $men - $women;
	  	if ($alone > 0)
	  	$draw['draw_nbPairs'] = "$women + $alone M";
	  	else if ($alone < 0)
	  	{
	  		$alo =  abs($alone);
	  		$draw['draw_nbPairs'] = "$men + $alo W";
	  	}
	  	else
	  	$draw['draw_nbPairs'] = $men;
	  }
	  else if ($draw['draw_disci'] > WBS_WS)
	  {
	  	$draw['draw_nbPairs']= intval($nb['nb']/2);
	  	if ($nb['nb']%2)
	  	$draw['draw_nbPairs'] .= ' + 1';
	  }
	  else
	  $draw['draw_nbPairs']= $nb['nb']==''?0:$nb['nb'];
		}
		return $draw;
	}
	// }}}


	// {{{ getSerial
	/**
	 * Return the fields for a serial
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort users
	 * @return array   array of users
	 */
	function getSerial($serial)
	{
		// Retrieve draws
		$eventId = utvars::getEventId();
		$fields = array('draw_catage', 'draw_rankdefId', 'draw_serial', 'draw_numcatage');
		$tables  = array('draws');
		$where = "draw_del != ".WBS_DATA_DELETE.
	" AND draw_eventId=$eventId".
	" AND draw_serial='$serial'";
		$res = $this->_select($tables, $fields, $where);
		if (!$res->numRows())
		{
	  $rows['errMsg'] = 'msgNoDraw';
	  return $rows;
		}

		$draw = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return ($draw);
	}
	// }}}


	// {{{ getSerialDraws
	/**
	 * Return the list of the draws of an serial
	 *
	 * @access public
	 * @param  string  $sort   criteria to sort users
	 * @return array   array of users
	 */
	function getSerialDraws($serial=false, $sort)
	{
		// Retrieve draws
		$eventId = utvars::getEventId();
		$fields = array('draw_id', 'draw_name', 'draw_stamp',
		      'draw_disci', 'draw_type', 'draw_catage',
		      'draw_type as nbPairs', 'draw_type as Groups',
		      'draw_type as Main', 
		      'draw_pbl', 'draw_del', 'draw_serial', 'draw_numcatage', 'draw_discipline');
		$tables  = array('draws');
		$where = "draw_del != ".WBS_DATA_DELETE.
	" AND draw_eventId=$eventId";
		if ($serial !== false) $where  .= " AND draw_serial='$serial'";
		else $order = 'draw_serial,'.abs($sort);
		if ($sort < 0) $order .=  " DESC";
		$res = $this->_select($tables, $fields, $where, $order);
		if (!$res->numRows())
		{
			$rows['errMsg'] = 'msgNoDraw';
			return $rows;
		}

		$ut = new utils();
		$uti = new utimg();
		$trans = array('draw_name','draw_stamp');
		while ($draw = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$drawSpec = $this->_getDrawSpec($draw['draw_id']);
			$draw['nbPairs'] = $drawSpec['draw_nbPairs'];
			$qual = $drawSpec['draw_nbq'];
			if ($qual == 1) $qual .= $ut->getLabel('STRING_QUALIF');
			else if ($qual > 1) $qual .= $ut->getLabel('STRING_QUALIFS');

			$groups = $drawSpec['draw_nbp3'] +
			$drawSpec['draw_nbp4']+
			$drawSpec['draw_nbp5'];
			if ($groups > 0)
			{
				if ($groups == 1) $groups .= $ut->getLabel('STRING_GROUP');
				else $groups .= $ut->getLabel('STRING_GROUPS');
				$draw['Groups'] = array();
				$draw['Groups'][] = array('value'=>$groups);
				if ($drawSpec['draw_nbq']) $draw['Groups'][] = array('value'=> $qual);
			}
			else $draw['Groups'] = '';
			/*
	   $qualif = $drawSpec['draw_nbplq'];
	   if ($qualif > 0)
	   {
	   if ($qualif == 1)
	   $qualif .= $ut->getLabel('STRING_PLACE');
	   else
	   $qualif .= $ut->getLabel('STRING_PLACES');
	   $draw['Qualif'] = array();
	   $draw['Qualif'][] = array('value'=>$qualif);
	   if ($drawSpec['draw_nbq'])
	   $draw['Qualif'][] = array('value'=>$qual);
	   }
	   else
	   $draw['Qualif'] = '';
	   */
			$draw['Main'] = $drawSpec['draw_nbpl'];
			if ($draw['Main']>0)
			{
				if ($draw['Main'] == 1) $draw['Main'] .= $ut->getLabel('STRING_PLACE');
				else $draw['Main'] .= $ut->getLabel('STRING_PLACES');
			}
			else $draw['Main'] = '';

			$draw = $this->_getTranslate('draws', $trans,
			$draw["draw_id"], $draw);
			$draw['draw_disci'] = $ut->getSmaLabel($draw['draw_disci']);
			$draw['draw_catage'] = $ut->getLabel($draw['draw_catage']);
			if ($draw['draw_numcatage']) $draw['draw_catage'] .= ' ' . $draw['draw_numcatage'];

			$draw['draw_type'] = $ut->getLabel($draw['draw_type']);
			$draw['iconPbl'] = $uti->getPubliIcon($draw['draw_del'],
			$draw['draw_pbl']);
			$draw['warning'] = '';
			if ( ($drawSpec['draw_type'] == WBS_GROUP &&
			$drawSpec['draw_nbPairs'] > $drawSpec['draw_nbGroupPlaces']) ||
			($drawSpec['draw_type'] == WBS_KO &&
			$drawSpec['draw_nbPairs'] > $drawSpec['draw_nbpl']))
			$draw['warning'] = $uti->getIcon('w1');
			if ( ($drawSpec['draw_type'] == WBS_GROUP &&
			$drawSpec['draw_nbPairs'] < $drawSpec['draw_nbGroupPlaces']) ||
			($drawSpec['draw_type'] == WBS_KO &&
			$drawSpec['draw_nbPairs'] < $drawSpec['draw_nbpl']))
			$draw['warning'] = $uti->getIcon('w3');
			$rows[] = $draw;
		}
		return $rows;
	}
	// }}}

	// {{{ getSerialsList
	/**
	 * Return the list of the draws
	 *
	 * @access public
	 * @param  integer  $groupId  Id of the group
	 * @return array   array of users
	 */
	function getSerialsList()
	{
		// Select the draws
		$fields = array('DISTINCT draw_id', 'draw_serial');
		$tables = array('draws');
		$where = " draw_eventId =".utvars::getEventId();
		$order = "draw_serial";
		$draws = $this->_select($tables, $fields, $where, $order, 'draw');
		$trans = array('draw_serial');
		$rows = array();
		while ($draw = $draws->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$draw = $this->_getTranslate('draws', $trans, $draw["draw_id"], $draw);
			$rows[$draw['draw_serial']] = $draw['draw_serial'];
		}
		return $rows;
	}
	// }}}


	// {{{ getDrawsList
	/**
	 * Return the list of the draws
	 *
	 * @access public
	 * @param  integer  $groupId  Id of the group
	 * @return array   array of users
	 */
	function getDrawsList($critere, $disci, $aEncode = false)
	{
		if (!is_array($critere))
		{
			$gender = $critere;
			$where = '';
		}
		else
		{
			if ($critere['regi_catage'] == '') $critere['regi_catage'] == WBS_CATAGE_SEN;
			// Categorie d'age autorisees en fontion
			// de la categorie et du surclassement
			$cateMin = $critere['regi_catage'];
			if ($cateMin == WBS_CATAGE_VET)
			{
				$cateMax = $cateMin;
				// Plus besion de surclassement pour les veterans qui joue en senior
				//if ($critere['regi_surclasse'] == WBS_SURCLASSE_VA ||
				//$critere['regi_numcatage'] == 1 ) $cateMin = WBS_CATAGE_SEN;
				$cateMin = WBS_CATAGE_SEN;
			}
			else
			{
				$cateMax = $cateMin;
				switch ($critere['regi_surclasse'])
				{
					case WBS_SURCLASSE_SIMPLE :
					case WBS_SURCLASSE_SP :
						$cateMax++;
						break;
					case WBS_SURCLASSE_DOUBLE :
						$cateMax +=2;
						break;
					case WBS_SURCLASSE_SE :
						$cateMax +=3;
						break;
				}

			}
			$gender = $critere['mber_sexe'];
			$where = "draw_catage <= $cateMax".
" AND draw_catage >= $cateMin";
			if ( $critere['regi_numcatage'] )$where .= ' AND (draw_numcatage=0 || draw_numcatage=' . $critere['regi_numcatage'] . ')';

			// Recuperer la liste des classements
			$fields = array('rkdf_id', 'rkdf_point');
			$tables = array('events', 'rankdef');
			$ou = "evnt_rankSystem=rkdf_system".
" AND evnt_id=".utvars::getEventId();
			$order = 'rkdf_point';
			$res = $this->_select($tables, $fields, $ou, $order);
			while($infos = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$ranks[$infos['rkdf_id']] = $infos['rkdf_point'];
			}
			if ($critere['rank'] == '' ||
			!isset($ranks[$critere['rank']]))
			$point = end($ranks);
			else
			$point = $ranks[$critere['rank']];
			$where .= " AND ({$point} <= rkdf_point OR rkdf_point=0)  AND ";
		}

		$rows = array();
		if ($gender != WBS_MALE && $gender != WBS_FEMALE) return $rows;

		// Select all the draws
		$tables = array('draws LEFT JOIN rankdef ON draw_rankdefId = rkdf_id');
		$fields = array('draw_id', 'draw_name', 'draw_stamp',
	      'draw_serial', 'draw_disci', 'rkdf_point');
		$ou = " draw_eventId =".utvars::getEventId();
		if ($gender == WBS_MALE)
		{
			switch ($disci)
			{
				case WBS_SINGLE:
					$ou  .= " AND (draw_disci =".WBS_MS;
					$ou  .= " OR draw_disci =".WBS_AS.")";
					break;
				case WBS_DOUBLE:
					$ou  .= " AND (draw_disci =".WBS_MD;
					$ou  .= " OR draw_disci =".WBS_AD.")";
					break;
				case WBS_MIXED:
					$ou .= " AND draw_disci =".WBS_XD;
					break;
			}
		}
		else
		{
			switch ($disci)
			{
				case WBS_SINGLE:
					$ou  .= " AND (draw_disci =".WBS_WS;
					$ou  .= " OR draw_disci =".WBS_AS.")";
					break;
				case WBS_DOUBLE:
					$ou  .= " AND (draw_disci =".WBS_WD;
					$ou  .= " OR draw_disci =".WBS_AD.")";
					break;
				case WBS_MIXED:
					$ou  .= " AND draw_disci =".WBS_XD;
					break;
			}
		}
		$order = "draw_stamp";
		$res = $this->_select($tables, $fields, $ou, $order, 'draw');

		// Recuperer les tableaux autorises
		$where .= $ou;
		$res2 = $this->_select($tables, $fields, $where, $order, 'draw');
		$ids = array();
		while ($draw = $res2->fetchRow(DB_FETCHMODE_ASSOC))
		$ids[$draw['draw_id']] = $draw['draw_id'];

		$trans = array('draw_name','draw_stamp', 'draw_serial');
		$draws = array();
		while ($draw = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$draw = $this->_getTranslate('draws', $trans,
			$draw["draw_id"], $draw);
			if ($aEncode)
			$draws[$draw['draw_id']] = array('value' => utf8_encode($draw['draw_stamp']),
					  'disable'=> !in_array($draw["draw_id"], $ids));
			else
			$draws[$draw['draw_id']] = array('value' => $draw['draw_stamp'],
				  'disable'=> !in_array($draw["draw_id"], $ids));
		}
		return $draws;
	}
	// }}}

	// {{{ getDraws
	/**
	 * Return the list of the draws
	 *
	 * @access public
	 * @param  integer  $groupId  Id of the group
	 * @return array   array of users
	 */
	function getDraws($sort=2)
	{

		// Select the draws
		$fields = array('draw_id', 'draw_name', 'draw_stamp',
		      'draw_serial', 'draw_disci', 'draw_pbl', 'draw_type');
		$tables = array('draws');
		$where = " draw_eventId =".utvars::getEventId();
		if (is_array($sort)) $sorts = $sort;
		else $sorts[] = $sort;
		foreach($sorts as $sort)
		{
			$order = abs($sort);
			if ($sort < 0) $order .= " DESC";
			$orders[] = $order;
		}
		$tri = implode(',', $orders);
		$draws = $this->_select('draws', $fields, $where, $tri, 'draw');
		if (!$draws->numRows())
		{
			$rows['errMsg'] = 'msgNoDraw';
			return $rows;
		}
		$ut = new utils();
		$trans = array('draw_name','draw_stamp', 'draw_serial');
		while ($draw = $draws->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$draw = $this->_getTranslate('draws', $trans, $draw["draw_id"], $draw);
			$draw['draw_disci'] = $ut->getLabel($draw['draw_disci']);
			$rows[] = $draw;
		}
		return $rows;
	}
	// }}}


	// {{{ delDraw
	/**
	 * @brief
	 * @~english Delete a draw and all associated round
	 * @~french Supprime un tableau (draw) et tous les tours associ�s
	 *
	 * @par Description:
	 * @~french L'utilisation de cette m�thode permet de supprimer un tableau 
	 * (draw) et tous les enregistrements associ�s en cascade : tour (round)
	 * rencontres (tie), match (matchs) et les liens entre les matchs et
	 * les paires s'ils existent. Les enregistrements sont supprim�s 
	 * physiquement de la base. Il n'y a pas possibilit� de les restaurer.
	 *
	 * @~english
	 * See french doc.
	 *
	 * @param  $drawId  (integer)    @~french Identifiant du tableau (draw_id)
	 *         @~english Draw id (database field draw_id)
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
	function delDraw($drawId)
	{
		// Retrieve the round of the draw
		$fields[] = 'rund_id';
		$tables[] = 'rounds';
		$where = "rund_drawId = $drawId ";
		$res = $this->_select($tables, $fields, $where);

		// Delete the round of the draw
		$utr = new utround();
		while ($round = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
	  $utr->delRound($round['rund_id']);
		}

		// Update the pairs of this draw
		$fields = array();
		$fields['pair_drawId'] = -1;
		$where = "pair_drawId = $drawId ";
		$res = $this->_update('pairs', $fields, $where);

		// Delete the draw
		$where = "draw_id = $drawId ";
		$res = $this->_delete('draws', $where);
		return true;
	}
	// }}}

	// {{{ updateDrawDef
	/**
	 * @brief
	 * @~english Update the definiotn of a draw
	 * @~french Met a jour la definition d'un tableau
	 * associ�s
	 *
	 * @par Description:
	 * @~french L'utilisation de cette m�thode permet de mettre a jour les 
	 * champs d'un tableau, sans modifier les tours qui lui sont associes
	 * @~english
	 * See french doc.
	 *
	 * @param  $infos  (array)    @~french Tableau associatif contenant les
	 * sp�cifications du tablea.
	 *  @li draw_id: identifiant du tableau. S'il vaut -1, un nouveau tableau
	 *  est cr��. Sinon le tableau existant est mis � jour. Le nouveau tableau
	 *  est automatiquement rattache au tournoi courant
	 *  @li draw_name   : nom du tableau
	 *  @li draw_stamp  : sigle du tableau
	 *  @li draw_serial : serie du tableau.Utilise uniquement pour les
	 *  tournois individuels pour regrouper les tableaux.
	 *  @li draw_disci  : discipline du tableau. Utilise uniquement pour les
	 *  tournois individuels. Les valeurs possibles sont :\n
	 *	WBS_MS : simple homme \n
	 *    WBS_WS : simple dame \n
	 *	WBS_MD : double homme \n
	 *    WBS_WD : double dame \n
	 *	WBS_XD : double mixte \n
	 *    WBS_AS : simple melange (homme+dame) \n
	 *	WBS_AD : double melange (homme+dame) \n
	 *  @li draw_type: type du tableau. Les valeurs possibles sont :\n
	 *	WBS_GROUP : poule pour un tournoi individuel\n
	 *    WBS_KO:     �limination directe pour un tournoi individuel\n
	 *    WBS_QUALIF : qualification pour un tournoi individuel\n
	 *    WBS_TEAM_GROUP: poule pour un tournoi par �quipe\n
	 *    WBS_TEAM_BACK: poule pour un tournoi par �quipe avec rencontre 
	 *    aller/retour\n
	 *    WBS_TEAM_KO:  �limination directe pour un tournoi par �quipe\n
	 *    WBS_TEAM_BACK :  qualification pour un tournoi individuel\n
	 *	WBS_SWISS: ronde suisse\n
	 *         @~english Table with the specification of the round.
	 * @return @~french En cas d'erreur, un tableau avec un �l�ment 'errMsg',
	 *   sinon l'id du tableau
	 *         @~english Draw Id or table with error msg 'errMsg'
	 *
	 * @~
	 * @see utRound
	 * @par Exemple:
	 * @code
	 *    $kpage = new Page('Sample');
	 *    $utr = new utRound();
	 *    $res = $utr->delRound(56);
	 *    if (is_array($res))
	 *       $kpage->addWng($res['errMsg']);
	 * @endcode
	 */
	function updateDrawDef($infos)
	{
		if ($infos['draw_id'] == -1)
		{
	  /* Create draw */
	  unset($infos['draw_id']);
	  $infos['draw_eventId'] = utvars::getEventId();
	  $res = $this->_insert('draws', $infos);
	  $infos['draw_id'] = $res;
		}

		/* Translate fields */
		$trans = array('draw_name','draw_stamp', 'draw_serial');
		$infos = $this->_updtTranslate('draws', $trans,
		$infos['draw_id'], $infos);

		/* Updating draw */
		$where = "draw_id =".$infos['draw_id'];
		$res = $this->_update('draws', $infos, $where);
		return $infos['draw_id'];
	}
	// }}}

	//-----------------------------------------
	//  Here are private methode
	//------------------------------------------

}

?>
