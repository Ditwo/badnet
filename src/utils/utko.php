<?php
/*****************************************************************************
 !   Module     : utils
 !   File       : $Source: /cvsroot/aotb/badnet/src/utils/utko.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.24 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/06 07:59:33 $
 ******************************************************************************/

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

class utKo
{

	// {{{ properties
	/**
	 * Full size of the draw (number of places)
	 *
	 * @var     integer
	 * @access  private
	 */
	var $_size=2;

	/**
	 * Number of places
	 *
	 * @var     integer
	 * @access  private
	 */
	var $_places=2;

	/**
	 * Number of byes
	 *
	 * @var     integer
	 * @access  private
	 */
	var $_nbByes=0;

	/**
	 * Number of qualifying
	 *
	 * @var     integer
	 * @access  private
	 */
	var $_qualif=1;
	/**
	 * Places for seed
	 *
	 * @var     integer
	 * @access  private
	 */
	var $_whereSeed =
	array( 2 => array(1,2),
	4 => array(1,4,2,3),
	8 => array(1,8,3,6,2,7,4,5),
	16 => array(1,16,5,12,3,14,7,10,2,15,6,11,4,13,8,9),
	32 => array(1,32,9,24,5,13,20,28),
	64 => array(1,64,17,48,9,25,40,56,5,13,21,29,36,44,52,60),
	128 => array(1,128,33,96,17,49,80,112,9,25,41,57,72,88,104,120));

	// }}}

	// {{{ utKo
	/**
	 * @brief
	 * @~english Constructor of the classe
	 * @~french Cnstrcteur de la classe
	 *
	 * @par Description:
	 * @~french Initialise une instance de la classe. Cette classe permet
	 * d'initialiser les valeurs de la premiere colonne d'un tableau par
	 * �limination directe. Le tableau des valeurs contient la liste des valeurs
	 * a afficher de haut en bas, sans tenir compte des places vacantes.
	 * Ces dernieres sont calculees automatiquement.
	 *
	 * @~english
	 * See french doc.
	 *
	 * @param  $values  (integer)    @~french Entree du tableau.
	 *          La taille du tableau sera automatiquement ajust�e a la puissance
	 *          de deux immediatement superieure au nombre d'entree.
	 *          Les places vacants sont calculees automatiquement.
	 *         @~english
	 * @param  $nb  (integer)    @~french Nombre d'entree. S'il est ommis, le nombre
	 *          d'entree du tableau sera dimensionne avec la dimension des entrees
	 *          La taille du tableau sera automatiquement ajust�e a la puissance
	 *         @~english
	 * @return @~french En cas d'erreur, un tableau avec un �l�ment 'errMsg', sinon Tru
	 *         @~english True or table with error msg 'errMsg'
	 *
	 * @~
	 * @endcode
	 */
	function utKo($values, $nb=false)
	{
		// Definition des position des byes
		$ut = new utils();
		$isSquash = $ut->getParam('issquash', false);
		if ($isSquash)
		{
			$this->_whereSeed =
			array( 2 => array(1,2),
			4 => array(1,4,2,3),
			8 => array(1,8,3,6,2,7,4,5),
			16 => array(1,16,5,12,3,14,7,10,2,15,6,11,4,13,8,9),
			32 => array(1,32,9,25,5,13,20,28),
			64 => array(1,64,17,48,9,25,40,56,5,13,21,29,36,44,52,60),
			128 => array(1,128,33,96,17,49,80,112,9,25,41,57,72,88,104,120));
	
			$this->_whereBye =
			array( 2 => array(2),
			4 => array(2,3),
			8 => array(2,7,5,4),
			16 => array(2,15,11,6,8,9,13,4),
			32 => array(2,31,23,10,14,19,27,6,8,25,17,16,12,21,29,4),
			64 => array(2,63,47,18,26,39,55,10,14,51,35,30,22,43,59,6,
			8,57,41,24,32,33,49,16,12,53,37,28,20,45,61,4));

			$this->_wherePos =
			array( 2 => array(1,2),
			4 => array(1,4,3,2),
			8 => array(1,8,6,3,4,5,7,2,),
			16 => array(1,16,12,5,7,10,14,3,4,13,9,8,6,11,15,2),
			32 => array(1,32,24,9,13,20,28,5,7,26,18,15,11,22,30,3,
			4,29,21,12,16,17,25,8,6,27,19,14,10,23,31,2),
			64 => array(1,64,48,17,25,40,56,9,13,52,
			36,29,21,44,60,5,7,58,42,23,
			31,34,50,15,11,54,38,27,19,46,
			62,3,4,61,45,20,28,37,53,12,
			16,49,33,32,24,41,57,8,6,59,
			43,22,30,35,51,14,10,55,39,26,18,47,63,2));
		}
		else
		{
			$this->_whereSeed =
			array( 2 => array(1,2),
			4 => array(1,4,2,3),
			8 => array(1,8,3,6,5,4,2,7),
			16 => array(1,16, 5,12, 3,14,7,10, 2,15,6,11,4,13,8,9),
			32 => array(1, 32, 9, 24, 5, 28, 13, 20, 3,30,11,22,7,26,15,18),
			64 => array(1, 64, 17,48, 9,56,25,40, 5,13,21,29,36,44,52,60),
			128 => array(1,128,33,96,17,49,80,112,9,25,41,57,72,88,104,120));
			
			$this->_whereBye =
			array( 2 => array(2),
			4 => array(2,3),
			8 => array(2,7,4,5),
			16 => array(2,15,6,11,4,13,8,9),
			32 => array(2,31,10,23,6,27,14,19,4,29,12,21,8,25,16,17),
			64 => array(2,63,18,47,10,55,26,39,4,61,
			20,45,12,53,28,37,6,59,22,43,
			14,51,30,35,8,57,24,41,16,49,32,33));

			$this->_wherePos =
			array( 2 => array(1,2),
			4 => array(1,4,3,2),
			8 => array(1,8,3,6,5,4,7,2),
			16 => array(1,16,5,12,3,14,7,10,9,8,13,4,11,6,15,2),
			32 => array(1,32,9,24,5,28,13,20,3,30,11,22,7,26,15,18,
			17,16,25,8,21,12,29,4,19,14,27,6,23,10,31,2),
			
			64 => array(1, 64, 17,48, 9,56,25,40, 5,60,21,44,13,52,29,36,
			3,62,19,46,11,54,27,38,7,58,23,42,15,50,31,34,
			33,32,49,16,41,24,57,8,37,28,53,12,45,20,61,4,
			35,30,51,14,43,22,59, 6,39,26,55,10,47,18,63,2));
		}
		if (is_array($values))
		{
			if ($nb === false) $this->_places = count($values);
			else $this->_places = $nb;
		}
		else $this->_places = $values;
			
		if ($this->_places <= 2) $this->_size = 2;
		elseif ($this->_places <= 4) $this->_size = 4;
		elseif ($this->_places <= 8) $this->_size = 8;
		elseif ($this->_places <= 16) $this->_size = 16;
		elseif ($this->_places <= 32) $this->_size = 32;
		elseif ($this->_places <= 64) $this->_size = 64;
		else
		{
			$this->_places = 64;
			$this->_size = 64;
		}
		$this->_nbByes = $this->_size - $this->_places;

		$this->_values = $values;
		// Position des tous les vacants pour le tour
		$bye = $this->_whereBye[$this->_size];

		// On ne garde que les positions des vacants effectifs
		$max = count($bye) - $this->_nbByes;
		for($i=0; $i < $max; $i++)array_pop($bye);
		$this->_bye = $bye;
	}

	/**
	 * @brief
	 * @~english Return the length of the draw
	 * @~french Renvoie le nombre de place du tableau
	 *
	 * @par Description:
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french En cas d'erreur, un tableau avec un �l�ment 'errMsg',
	 *   sinon le nombre d'element
	 *         @~english True or number of element
	 *
	 * @~
	 */
	function getSize()
	{
		return $this->_size;
	}

	/**
	 * @brief
	 * @~english Return the values of the column, without byes
	 * @~french Renvoie les valeurs de la colonne, sans ajouter de vacant
	 *
	 * @par Description:
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french En cas d'erreur, un tableau avec un �l�ment 'errMsg',
	 *   sinon le tableau des valeurs
	 *         @~english Table with values
	 *
	 * @~
	 */
	function getValues()
	{
		foreach($this->_values as $entrie) $values[] = $entrie;
		return $values;
	}

	/**
	 * @brief
	 * @~english Return the values of the column with byes
	 * @~french Renvoie les valeurs de la colonne en inserant des vacants
	 * au bonnes places
	 *
	 * @par Description:
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french En cas d'erreur, un tableau avec un �l�ment 'errMsg',
	 *   sinon le tableau des valeurs
	 *         @~english Table with values or error msg 'errMsg'
	 *
	 * @~
	 */
	function getExpandedValues()
	{
		$ut = new utils();

		// parcourt des positions
		for($i = 1; $i <= $this->_size; $i++)
		{
			$values[$i] = null;
			// Pour chaque position, memorisation de l'equipe
			//La position est un bye
			if (in_array($i, $this->_bye))
			{
				$values[$i] = $ut->getLabel("STRING_BYE");
				// Recherche de l'equipe a cette poisition
				foreach($this->_values as $entrie)
				{
					if (isset($entrie['t2r_posRound']) && $entrie['t2r_posRound']==$i)
					{
						// Decaler toutes les paires vers le bas
						$nb = count($this->_values);
						for ( $j=0; $j<$nb; $j++)
						{
							if (!empty($this->_values[$j]))
							{
								$value = $this->_values[$j];
								if($value['t2r_posRound'] >= $i)
								{
									$value['t2r_posRound']++;
									$this->_values[$j] = $value;
								}
							}
						}
						break;
					}
				}
			}
			// La position n'est pas un bail
			else
			{
				foreach($this->_values as $entrie)
				{
					if (isset($entrie['t2r_posRound']) && $entrie['t2r_posRound']==$i)
					{
						$values[$i] = $entrie;
						break;
					}
				}
			}
		}
		return $values;
	}
	// }}}

	/**
	 * @brief
	 * @~english Return the position of the values in the column.
	 * @~french Renvoie la position des valeurs dans la colonne.
	 * Tableaux indexe avec la valeur
	 *
	 * @par Description:
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french En cas d'erreur, un tableau avec un �l�ment 'errMsg',
	 *   sinon la listes des positions
	 *         @~english list of positions or error msg 'errMsg'
	 *
	 * @~
	 */
	function getPositions()
	{
		$pos=1;
		foreach($this->_values as $entrie)
		{
			if (is_array($entrie))  $key = reset($entrie);
			else $key = $entrie;
			$values[$key] = $pos++;
		}

		return $values;
	}

	/**
	 * @brief
	 * @~english Return the position of the values in the column.
	 * @~french Renvoie la position des valeurs dans la colonne, apres
	 * insertion des vacants. Tableaux indexe avec la valeur
	 *
	 * @par Description:
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french En cas d'erreur, un tableau avec un �l�ment 'errMsg',
	 *   sinon la listes des positions
	 *         @~english list of positions or error msg 'errMsg'
	 *
	 * @~
	 */
	function getExpandedPositions()
	{
		$entrie = reset($this->_values);
		// parcour des positons
		$positions = $this->_wherePos[$this->_size];
		foreach($positions as $position)
		{
			// Sauvegarde de la position de chaque equipe
			if (!in_array($position, $this->_bye))
			{
				if (is_array($entrie)) 	$key = reset($entrie);
				else $key = $entrie;
				$values[$key] = $position;
				$entrie = next($this->_values);
			}
		}
		return $values;
	}

	/**
	 * @brief
	 * @~english Return the ties numbers and the the position in the tie
	 * @~french Renvoie les numeros des rencontres et la positions dans
	 * la rencontre de chaque equipe. La finale a le numero 0, les demi 1 et 2
	 * et ainsi de suite
	 *
	 * @par Description:
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french En cas d'erreur, un tableau avec un �l�ment 'errMsg',
	 *   sinon la listes des numero des rencontres dans le tableau
	 *         @~english list of expanded positions or error msg 'errMsg'
	 *
	 * @~
	 */
	function getExpandedTies()
	{
		// Calcul de la positon de la premiere rencontre
		// La rencontre 0 etant la finale
		$start  = ($this->_size/2)-1;

		$entrie = reset($this->_values);
		// parcourt des positons
		for($i = 1; $i <= $this->_size; $i++)
		{
			// Si ce n'est pas une place vacante
			if (! in_array($i, $this->_bye))
			{
				// Calcul du numero de la rencontre
				// et de la position de l'equipe dans la rencontre
				$tie['numTie'] = intval($start+($i-1)/2);
				$tie['posInTie'] = ($i%2) ? WBS_TEAM_TOP:WBS_TEAM_BOTTOM;
				if (is_array($entrie)) $key = reset($entrie);
				else $key = $entrie;
				$ties[$key] = $tie;
				$entrie = next($this->_values);
			}
		}

		return $ties;
	}

	/**
	 * @brief
	 * @~english Return the ties number with a bye and the position of the bye
	 * inside the tie (top or bottom)
	 * @~french Renvoie les numeros des rencontres avec un vacant
	 * et la position du vacant dans la rencontre (haut ou bas)
	 *
	 * @par Description:
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french En cas d'erreur, un tableau avec un �l�ment 'errMsg',
	 *   sinon la positions des byes dans le tableau
	 *         @~english Table with position of the byes or error msg 'errMsg'
	 *
	 * @~
	 */
	function getExpandedByes()
	{
		// Calcul de la positon de la premiere rencontre
		$start = ($this->_size/2)-1;
		$values = array();
		// parcour des positons des vacants
		foreach($this->_bye as $bye)
		{
			// Calcul du numero de la rencontre
			// et de la position du vacant dans la rencontre
			$ties['posInTie'] = ($bye%2) ? WBS_TEAM_TOP:WBS_TEAM_BOTTOM;
			$ties['numTie'] = intval($start+($bye-1)/2);
			$values[] = $ties;
		}

		return $values;
	}

	/**
	 * @brief
	 * @~english Return the a table with ties number with a bye
	 * inside the tie (top or bottom)
	 * @~french Renvoie un tableau avec les numeros des rencontres avec un vacant
	 *
	 * @par Description:
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french En cas d'erreur, un tableau avec un �l�ment 'errMsg',
	 *   sinon true
	 *         @~english True or table with error msg 'errMsg'
	 *
	 * @~
	 */
	function getByesTie()
	{
		// Calcul de la positon de la premiere rencontre
		$start = ($this->_size/2)-1;
		$values = array();
		// parcour des positons des vacants
		foreach($this->_bye as $bye)
		{
			// Calcul du numero de la rencontre
			$values[] = intval($start+($bye-1)/2);
		}
		return $values;
	}

	/**
	 * @brief
	 * @~english Return the values of the ties numbers
	 * @~french Renvoie les numeros des rencontre de chaque equipe
	 *
	 * @par Description:
	 *
	 * @~english
	 * See french doc.
	 *
	 * @param  $column  (integer)    @~french Numero de la colonne. Si le
	 *      numero est trop grand, les valeurs sont ignorees.
	 *         @~english Column number.
	 * @return @~french En cas d'erreur, un tableau avec un �l�ment 'errMsg',
	 *   sinon true
	 *         @~english True or table with error msg 'errMsg'
	 *
	 * @~
	 */
	function getNumTies($numCol=1)
	{
		// Calcul de la positon de la premiere rencontre
		// La rencontre 0 etant la finale
		$nbTies  = ($this->_size/pow(2,$numCol));
		// Calcul du nombre de rencontre
		$firstTies = $nbTies-1;

		// parcourt des positions
		for($i = 0; $i <  $nbTies; $i++)
		{
			$pos[] = $firstTies+$i;
		}
		return $pos;
	}

	/**
	 * Initilisation du tirage au sort. Les paires sont renseignees
	 * avec les infos suivantes :
	 *  - tds      : tete de serie
	 *  - sep      : les criteres de separation
	 *  - intRank  : classement international de la paire
	 *  - natRank  : classement national de la paire
	 *  - level    : niveau de la paire (A1, A2, ... NC)
	 *  - average  : nombre de points
	 * Elles sont completes avec
	 *  - place    : place de la paire dans le tableau (0 = non place)
	 *  - plages   : listes des plages de positions possible pour la paire
	 *  - criteria : tableau des criteres de separation
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function initDraw($limitation)
	{
		$pairs    = $this->_values;  // liste des paires
		$nbPlaces = $this->_places;  // nombre de paires
		$dimDraw  = $this->_size;    // dimension totale du tableau
		$nbByes   = $this->_nbByes;  // nombre de bye
		$posByes  = $this->_bye;      // Position des bye
		$posSeed   = $this->_whereSeed[$dimDraw]; // Position des tds

		//---- Initialiser l'etat des paires ----
		// Pour chaque paire elaborer la plage initiale des places possibles
		// en fonction des tetes de serie designees
		for($i=0; $i<$nbPlaces; $i++)
		{
	  $pair = $pairs[$i];
	  $plage = new plage();
	  foreach ($this->_bye as $bye)
	  $plage->removeValue($bye);
	  switch ($pair['tds'])
	  {
	  	case  WBS_TDS_1:
	  		$plage->addValue($posSeed[0]);
	  		break;
	  	case  WBS_TDS_2:
	  		$plage->addValue($posSeed[1]);
	  		break;
	  	case  WBS_TDS_3_4:
	  		for($j=2;$j<4;$j++)
	  		$plage->addValue($posSeed[$j]);
	  		break;
	  	case  WBS_TDS_5_8:
	  		for($j=4;$j<8;$j++)
	  		$plage->addValue($posSeed[$j]);
	  		break;
	  	case  WBS_TDS_9_16:
	  		for($j=8;$j<16;$j++)
	  		$plage->addValue($posSeed[$j]);
	  		break;
	  	default:
	  		$plage->addInter(1, $dimDraw);
	  		break;
	  }
	  $pair['plages'][] = $plage;
	  $sortCriteria[] = $pair['criteria'];
	  $sortIntRank[]  = $pair['intRank'];
	  $sortNatRank[]  = $pair['natRank'];
	  $sortLevel[]    = $pair['level'];
	  $sortAverage[]  = $pair['average'];
	  $sortTds[] = $pair['tds'];
	  $pair['place'] = 0;
	  $pairs[$i] = $pair;
	  if (isset($secondNb[$pair['secondCriteria']]))
	  $secondNb[$pair['secondCriteria']]++;
	  else
	  $secondNb[$pair['secondCriteria']] = 1;
	  if (isset($nb[$pair['criteria']]))
	  $nb[$pair['criteria']]++;
	  else
	  $nb[$pair['criteria']] = 1;
		}

		//---- Classer la liste des paires ----
		// - les tds en premier
		// - $limitation paires par pays :  $limitation=nombre de paires a separer
		//   par pays ( en principe 2 ). Les tetes de serie sont prise en compte
		// - les paires les plus nombreuses dans le critere choisi:
		// - le classement des paires qui ont le meme  critere

		// Pour chaque paire, calculer son index dans le critere
		array_multisort($sortCriteria, $sortTds, $sortIntRank, $sortNatRank,
		$sortLevel, $sortAverage, $pairs);

		$sortTds = array();
		$sortCriteriaPos = array();
		$sortCriteriaIndex = array();
		$sortCriteriaNb = array();
		for($i=0; $i<$nbPlaces; $i++)
		{
	  $pair = $pairs[$i];

	  if(isset($list[$pair['criteria']]))
	  $list[$pair['criteria']]++;
	  else
	  $list[$pair['criteria']] = 1;
	  $pair['criteriaIndex'] = $list[$pair['criteria']];

	  if(isset($list2[$pair['secondCriteria']]))
	  $list2[$pair['secondCriteria']]++;
	  else
	  $list2[$pair['secondCriteria']] = 1;
	  $pair['secondCriteriaIndex'] = $list2[$pair['secondCriteria']];

	  $pair['criteriaNb'] = $nb[$pair['criteria']];
	  $pair['secondCriteriaNb'] = $secondNb[$pair['secondCriteria']];
	  $pair['criteriaLimit'] = min($nb[$pair['criteria']], $this->_places/2);
	  $pair['secondCriteriaLimit'] = min($secondNb[$pair['secondCriteria']], $this->_places/2);
	  $sortTds[] = $pair['tds'];
	  if ($pair['criteriaIndex'] > $limitation)
	  $sortCriteriaLimit[] = $limitation;
	  else
	  $sortCriteriaLimit[] = $pair['criteriaIndex'];
	  $sortCriteriaNb[] = $nb[$pair['criteria']]+$secondNb[$pair['secondCriteria']];
	  $sortCriteriaIndex[] = $pair['criteriaIndex']+$pair['secondCriteriaIndex'];
	  $pairs[$i] = $pair;
		}
		array_multisort($sortTds, $sortCriteriaNb, SORT_DESC, $sortCriteriaLimit,
		$sortCriteriaIndex, $pairs);
		$this->pairs = $pairs;
		unset($list);
		unset($list2);
		unset($pairs);
		unset($sortTds);
		unset($sortIntRank);
		unset($sortNatRank);
		unset($sortAverage);
		unset($sortLevel);
		unset($sortCriteria);
		unset($sortCriteriaPos);
		unset($sortCriteriaIndex);
		unset($sortCriteriaNb);
		return true;
	}
	// }}}


	// {{{ goDraw
	/**
	 * Tirage au sort de la position des paires
	 *
	 * @access public
	 * @param  integer $limitation : c'est le nombre minimum de paire minimum
	 *   a separer. S'il vaut deux, cela signfie que les deux premiers joueurs
	 *   de chaque pays (ou equipe ou .... suivant le critere selectionne) seront
	 *   place dans les demi tableaux oppose. S'il vaut 4, ce seront les 4 premiers
	 *   et ainsi de suite
	 *
	 * @return void
	 */
	function goDraw()
	{
		//Pour chaque paire
		// Tirer au sort la place de la paire
		// SI le tirage au sort reussi
		//    Memoriser la position de la paire
		//    Marquer la place comme utilisee dans la liste des places
		//    de la paire
		//    Pour toute les paires suivantes:
		//      - marquer cette place comme indisponible
		//      - incrementer le compteur de paire de meme provenance si besoin
		//      - calculer la liste des places disponibles (compteur < NBMAX)
		//      - ajouter cette liste a la liste des etats
		// SINON
		//    Revenir a la paire precedente
		//    Marquer sa place comme impossible
		//    Pour toutes les paires suivantes :
		//      - supprimer le dernier etat de la liste
		//      - decrementer le compteur de paire de meme provenance si besoin
		//---- Initialiser la liste des places occupees avec la liste des ----
		// byes. Elle sera completee au fur et a mesure du placement
		// des paires

		// Tableau des places prises
		$this->_placesNok = array();
		// Les positions vacantes ne sont pas utilisables
		foreach ($this->_bye as $bye)
		$this->_placesNok[$bye]  = $bye;
			
		$pairs = $this->pairs;
		// initialisation de la fonction random
		mt_srand((float) microtime()*1000000);
		$nbPlaces = $this->_places;  // nombre de paires

		// Traitement de chaque paire
		$forceexit = 30;
		for($i=0; $i<$nbPlaces; $i++)
		{
			//if ( $forceexit-- < 0) break;
			//$this->_debug($pairs);

			// paire courante
			$pairCur = $pairs[$i];

			// Liste des places possible de la paire courante
			$plage = array_pop($pairCur['plages']);

			//Tirage au sort de sa place
			$place = $this->_random($plage);

			//echo "pair=$i; place=$place;critere={$pairCur['criteria']}<br>\n";
			// Tirage reussi
			if($place != -1)
			{
				// Dans la derniere plage des places disponibles de la paire,
				// supprimer cette place, puis memoriser l'etat de la paire
				$plage->removeValue($place);
				$pairCur['plages'][] = new plage($plage);
				$pairCur['place'] = $place;
				$pairs[$i] = $pairCur;

				// Cette position n'est plus utilisable
				$this->_placesNok[$place]  = $place;
				asort($this->_placesNok);

				// pour les paires suivantes,
				for ($j = $i+1; $j<$nbPlaces; $j++)
				{
					//echo "<br>j=".($j+1)."<br>\n";
					$pairNext = $pairs[$j];

					// recuperer la derniere plage des places disponibles
					//print_r($pairNext);
					$plage = array_pop($pairNext['plages']);
					$pairNext['plages'][] = new plage($plage);

					//supprimer la place de la liste des places disponibles
					$plage->removeValue($place);
					//echo "apres removeValue $place<br>\n";
					//$plage->trace($this->_placesNok);

					// Supprimer les places pour eviter que les memes criteres
					// se rencontrent trop tot
					$plage = $this->_limited($pairCur, $pairNext, $plage);
					//echo "apres limited<br>\n";
					//$plage->trace($this->_placesNok);

					// S'il n y a plus de place possible
					/* a tester plus serieusement
					if (!$plage->numValues($this->_placesNok))
					{
					//echo "\n******no place for ". ($j+1). " *********  <br>\n";
					$startPurge = $i+1;
					unset($this->_placesNok[$place]);
					$place=-1; //$i--;
					}
					*/
					// Rajouter cette plage a la liste des plages disponibles
					$pairNext['plages'][] = new plage($plage);
					$pairs[$j] = $pairNext;
				}
			}
			else
			{
				$startPurge = $i;
				$i--;
				$pair = $pairs[$i];
				if (isset($this->_placesNok[$pair['place']]))
				unset($this->_placesNok[$pair['place']]);
			}

			if ($place < 0)
			{
				//echo "\n******purge a partir de $startPurge  *********  ";
				//$this->_debug($pairs);
				// pour les paires suivantes,
				for ($j = $startPurge; $j<$nbPlaces; $j++)
				{
					//supprimer la derniere liste de places disponibles
					$pair = $pairs[$j];
					if (isset($this->_placesNok[$pair['place']]))
					unset($this->_placesNok[$pair['place']]);
					array_pop($pair['plages']);
					$pair['place'] = 0;
					$pairs[$j] = $pair;
				}
				$i--;
			}
			if ($i < 0)
			{
				//echo '-------echec-------';
				$err['errMsg'] = "msgLotAbort";
				return $err;
			}
		}
		return $pairs;
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
	function _limited($pairCur, $pairTraited, $plage)
	{
		//echo "limited:";
		// Si les criteres sont differents, pas de limitation
		if (($pairCur['criteria'] != $pairTraited['criteria']) &&
		($pairCur['secondCriteria'] != $pairTraited['secondCriteria']) &&
		($pairCur['secondCriteria'] != '') )
		{
			return $plage;
		}

		// Eviter  une rencontre au premier tour
		// s'il n' y a pas trop de paire avec ce critere
		$maxLimit = ($this->_size/2) - $this->_nbByes;
		if (($pairCur['criteria'] == $pairTraited['criteria'] &&
		//$pairTraited['criteriaIndex'] < $this->_places/2) ||
		$pairTraited['criteriaIndex'] < $maxLimit) ||
		($pairCur['secondCriteria'] == $pairTraited['secondCriteria'] &&
		$pairCur['secondCriteriaIndex'] < $maxLimit &&
		$pairTraited['secondCriteriaIndex'] < $maxLimit))
		//	   $pairTraited['secondCriteriaIndex'] < $this->_places/2))
		{
			$pos = $pairCur['place'];
			if ($pos%2) $place = $pos+1;
			else $place = $pos-1;
			$plage->removeValue($place);
		}

		// Calcul du nombre de places a interdire pour le premier critere
		$start = 0; $end = 0;
		if ($pairCur['criteria'] == $pairTraited['criteria'] &&
		$pairCur['criteriaIndex'] <= $pairCur['criteriaLimit'] &&
		$pairTraited['criteriaIndex'] <= $pairCur['criteriaLimit'])
		{
			$size = $this->_size;
			if ($pairTraited['criteriaLimit'] < 3) $portion = 2;
			else if ($pairTraited['criteriaLimit'] < 5) $portion = 4;
			else if ($pairTraited['criteriaLimit'] < 9) $portion = 8;
			else if ($pairTraited['criteriaLimit'] < 17) $portion = 16;
			else if ($pairTraited['criteriaLimit'] < 33) $portion = 32;
			else if ($pairTraited['criteriaLimit'] < 65) $portion = 64;
			else $portion = 128;

			//echo "remove index $size;$portion;";
			$size /= $portion;
			$pos = $pairCur['place'];
			$start = $pos - (($pos-1)%$size);
			$end = $start+$size-1;
			//echo "$start, $end, $pos<br>\n";
		}

		// Calcul du nombre de places a interdire pour le second critere
		if ($pairCur['secondCriteria'] != '' &&
		$pairCur['secondCriteria'] == $pairTraited['secondCriteria'] &&
		$pairCur['secondCriteriaIndex'] <= $pairCur['secondCriteriaLimit'] &&
		//$pairTraited['secondCriteriaIndex'] < $this->_places/2 &&
		$pairTraited['secondCriteriaIndex'] < $maxLimit &&
		$pairTraited['secondCriteriaIndex'] <= $pairCur['secondCriteriaLimit'])
		//$pairTraited['secondCriteriaIndex'] <= $pairTraited['secondCriteriaLimit'])
		{
			$size = $this->_size;
			if ($pairTraited['secondCriteriaLimit'] < 3) $portion = 2;
			else if ($pairTraited['secondCriteriaLimit'] < 5) $portion = 4;
			else if ($pairTraited['secondCriteriaLimit'] < 9) $portion = 8;
			else if ($pairTraited['secondCriteriaLimit'] < 17) $portion = 16;
			else if ($pairTraited['secondCriteriaLimit'] < 33) $portion = 32;
			else if ($pairTraited['secondCriteriaLimit'] < 65) $portion = 64;
			else $portion = 128;

			//echo "remove secondIndex $size;$portion;";
			$size /= $portion;
			$pos = $pairCur['place'];
			$start2 = $pos - (($pos-1)%$size);
			$end2 = $start2+$size-1;
			if ($start2 > $end ||
			$start > $end2)
			{
				//echo "paire " . $pairTraited['id'] . " remove inter classement [$start2, $end2] pour pos $pos<br>\n";
				$plage->removeInter($start2, $end2);
			}
			else
			{
				$start = min($start, $start2);
				$end = max($end, $end2);
			}
		}

		if ($start && $end)
		{
			//echo "paire " . $pairTraited['id'] . " remove inter geographique [$start, $end] pour pos $pos<br>\n";
			$plage->removeInter($start, $end);
		}
		//echo "finlimited<br>";
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
	function _random($plage)
	{
		$places = $plage->getValues($this->_placesNok);
		$nbChoix = count($places);
		if ($nbChoix)
		{
			$sel = mt_rand(0, $nbChoix-1);
			$values = array_values($places);
			$place = $values[$sel];
		}
		else
		$place = -1;
		return $place;
	}
	// }}}

	function _debug($pairs)
	{
		echo "-----------------{$this->_places} places---------------<br>\n";
		echo 'Place occupees:' . implode(',', $this->_placesNok) ."<br>\n";
		$i = 1;
		foreach($pairs as $pair)
		{
			echo "$i: id={$pair['id']};";
			echo "tds={$pair['tds']};";
			echo "level={$pair['level']};";
			echo "average={$pair['average']};";
			echo "place={$pair['place']}<br>\n";
			echo "criteria={$pair['criteria']};";
			echo "criteriaLimit={$pair['criteriaLimit']};";
			echo "criteriaNb={$pair['criteriaNb']};";
			echo "criteriaIndx={$pair['criteriaIndex']}<br>\n";
			echo "secondCriteria={$pair['secondCriteria']};";
			echo "secondCriteriaLimit={$pair['secondCriteriaLimit']};";
			echo "secondCriteriaNb={$pair['secondCriteriaNb']};";
			echo "secondCriteriaIndex={$pair['secondCriteriaIndex']}<br>\n";
			$plage =$pair['plages'][count($pair['plages'])-1];
			foreach($pair['plages'] as $plage)
	  $plage->trace($this->_placesNok);
	  $i++;
		}

	}
}

class plage
{
	var $_values = array();
	var $_noValues = array();
	var $_inters = array();

	function plage($plage = false)
	{
		if ($plage)
		{
			$this->_values   = $plage->_values;
			$this->_noValues = $plage->_noValues;
			$this->_inters   = $plage->_inters;
		}
	}

	function isIn($value)
	{
		$isIn = in_array($value, $this->_values);
		foreach($this->_inters as $inter)
		{
			$isIn |= ($value >= $inter[0] &&
			$value <= $inter[1]);
		}
		return $isIn;
	}

	function addValue($value)
	{
		$this->_values[$value] = $value;
	}

	function addInter($min, $max)
	{
		// S'il y a des places seules, l'intervalle
		// est interdit
		if (count($this->_values)) return;

		$newInter = array($min, $max);
		foreach($this->_inters as $inter)
		if ($inter[0] == $min && $inter[1]==$max)
		return;
		$this->_inters[] = $newInter;
		return true;
	}

	function removeValue($value)
	{
		if (isset($this->_values[$value]))
		unset($this->_values[$value]);
		else
		$this->_noValues[$value] = $value;
	}

	function removeInter($min, $max)
	{
		// supprimer les valeurs individuelles contenues
		// dans l'intervalle
		for($i=$min; $i<=$max;$i++)
		{
			if (isset($this->_values[$i]))
			unset($this->_values[$i]);
		}

		// Enlever cette plage des intervalles la contenant
		if (count($this->_inters))
		{
			$inters = array();
			foreach($this->_inters as $inter)
			{
				if ($min >= $inter[0] &&
				$max <= $inter[1])
				{
					$plage[0][0] = $inter[0];
					$plage[0][1] = $min-1;
					$plage[1][0] = $max+1;
					$plage[1][1] = $inter[1];
					for ($i=0; $i<2;$i++)
					if($plage[$i][1] > $plage[$i][0])
					{
						$inters[] = $plage[$i];
					}
				}
				else
				$inters[] = $inter;
			}
			unset($this->_inters);
			$this->_inters = $inters;
		}
		return true;
	}

	function getValues(&$placeNok)
	{
		$values = $this->_values;
		foreach($this->_inters as $inter)
		{
			for($i=$inter[0]; $i<= $inter[1]; $i++)
			$values[$i] = $i;
		}
		foreach($this->_noValues as $value)
		{
			if (isset($values[$value]))
			unset($values[$value]);
		}
		foreach($placeNok as $value)
		{
			if (isset($values[$value]))
			unset($values[$value]);
		}
		asort($values);
		return $values;
	}

	function numValues(&$placeNok)
	{
		return count($this->getValues($placeNok));
	}
	function trace(&$placeNok)
	{
		$values = $this->getValues($placeNok);
		asort($values);
		echo "plage autorisee:".implode('+', $this->_values);
		foreach($this->_inters as $inter)
		echo "+[{$inter[0]},{$inter[1]}]";
		echo " -".implode('-', $this->_noValues);

		echo "<br>\n";
	}
}
?>