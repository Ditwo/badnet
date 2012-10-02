<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';

class Oscore extends Object
{
	// Points pour le gagnant
	private $_winnerPt = array();
	private $_winnerCompletePt = array();
	
	// Points pour le perdant
	private $_looserPt = array();
	private $_looserCompletePt = array();
	
	// Nombre de jeu pour le gagnant
	private $_winnerGame = 2;

	// Nombre de jeu pour le perdant
	private $_looserGame = 0;

	// Indicateur de forfait
	private $_isWO = false;

	// Indicateur d'abandon
	private $_isAbort = false;

	// Nombre max de jeux
	protected $_nbMaxGame=3;

	// Nombre max de points
	private $_nbMaxPoint=15;

	// Nombre de jeu effectif
	private $_nbGame = 0;

	protected $_isWinner = true;
	
	/**
	 * Constructor
	 */
	public function __construct($aNbMaxGame= 3, $aNbMaxPoint = 15)
	{
		$this->_nbMaxGame  = $aNbMaxGame;
		$this->_nbMaxPoint = $aNbMaxPoint;
	}

	/**
	 * Renvoie true si le score est WO
	 */
	public function isWO() { return $this->_isWO; }

	/**
	 * Renvoie true si le score est WO
	 */
	public function isAbort() { return $this->_isAbort; }

	/**
	 * Initialise the internal data of the score
	 */
	public function reset()
	{
		$this->_nbGame = 0;
		$this->_winnerGame = 2;
		$this->_looserGame = 0;
		$this->_winnerPt = array();
		$this->_looserPt = array();
		$this->_isWO = false;
		$this->_isAbort = false;
	}

	/**
	 * Renvoi le score du vainqueur
	 */
	public function getWinScore()
	{
		$score = '';
		$glue = '';
		for ($i=0; $i < $this->_nbGame; $i++)
		{
			$score .= $glue . $this->_winnerPt[$i] . '-' . $this->_looserPt[$i];
			$glue = ' ';
		}
		if ($this->_isWO) $score .= ' WO';
		if ($this->_isAbort) $score .= ' Ab';

		return $score;
	}

	/**
	 * Renvoi le score du vainqueur au format FFBa
	 */
	public function getScoreFfba()
	{
		$score = '';
		$glue = '';
		for ($i=0; $i < $this->_nbGame; $i++)
		{
			$score .= $glue . $this->_winnerPt[$i] . '-' . $this->_looserPt[$i] ;
			$glue = ' / ';
		}
		if ($this->_isWO)	$score = 'WO';
		if ($this->_isAbort) $score .= ' Ab';
		return $score;
	}

	/**
	 * Renvoi le score du perdant
	 */
	public function getLooseScore()
	{
		$score = '';
		$glue = '';
		for ($i=0; $i < $this->_nbGame; $i++)
		{
			$score .= $glue . $this->_looserPt[$i] . '-' . $this->_winnerPt[$i];
			$glue = ' ';
		}
		if ($this->_isWO) $score .= " WO";
		if ($this->_isAbort) $score .= " Ab";
		return $score;
	}

	/**
	 * Renvoi les points de chaque jeu du vainqueur
	 */
	public function getWinGames()
	{
		for ( $i = 0; $i < $this->_nbGame; $i++) $games[$i] = $this->_winnerPt[$i];
		for ( $i = $this->_nbGame; $i<$this->_nbMaxGame; $i++) $games[$i] = '';
		return $games;
	}

	/**
	 * Renvoi les points de chaque jeu du perdant
	 */
	public function getLoosGames()
	{
		for ( $i = 0; $i < $this->_nbGame; $i++) $games[$i] = $this->_looserPt[$i];
		for ( $i = $this->_nbGame; $i<$this->_nbMaxGame; $i++) $games[$i] = '';
		return $games;
	}

	/**
	 * Renvoi le nombre de jeu gagnes
	 */
	function getNbWinGames() { return $this->_winnerGame; }

	/**
	 * Renvoi le nombre de jeu perdu
	 */
	public function getNbLoosGames() { return $this->_looserGame; }

	/**
	 * Renvoi le nombre points du vainqueur
	 */
	public function getNbWinPoints() { return array_sum($this->_winnerPt); }

	/**
	 * Renvoi le nombre de points du perdant
	 */
	public function getNbLoosPoints() { return array_sum($this->_looserPt); }

	/**
	 * Fix the score
	 *
	 * @access public
	 * @param  string $score score to set
	 * @return void
	 */
	public function setScore($aScore, $aIsRetired = false)
	{
		$score = trim($aScore);
		$isRetired = $aIsRetired;
		if ($score == '') return -1;

		$end = substr($score, -2);
		$this->_isWO = false;
		if ($end == 'WO')
		{
			$this->_isWO = true;
			$score = substr($score, 0, strlen($score)-3);
		}
		$this->_isAbort = false;
		if ($end == 'Ab')
		{
			$this->_isAbort = true;
			$score = substr($score, 0, strlen($score)-3);
		}
		if ($isRetired)	$this->_isAbort = true;
		$games = @explode(" ", $score);
		$nbGames = count($games);
		$scoreLeft = array();
		$scoreRight = array();
		for ($i=0; $i<$nbGames; $i++)
		{
			$points = @explode("-", $games[$i]);
			$scoreLeft[] = isset($points[0]) ? $points[0]:0;
			$scoreRight[] = isset($points[1]) ? $points[1]:0;
		}
		$res =  $this->_initScore($scoreLeft, $scoreRight);
		$this->_isWinner = ($this->getWinScore() == $aScore);
		return $res;
	}

	function isWinner(){return $this->_isWinner;}
	
	/**
	 * Initialisation des donnée du score
	 *
	 * @param array  $aScoreLeft  left points for each game
	 * @param array  $aScoreright right points for each game
	 * @return -1 if the score is not valid,
	 *          0 if the score is looser,
	 *          1 if the score is winner
	 */
	private function _initScore($aScoreLeft, $aScoreRight)
	{
		// Control the score
		$this->_nbGame = count($aScoreLeft);
		if ($this->_nbGame != count($aScoreRight))
		return -1;

		// Check if the score is winner
		$this->_winnerGame = 0;
		$this->_looserGame = 0;
		for ($i=0; $i < $this->_nbGame; $i++)
		{
			if ($aScoreLeft[$i]>$aScoreRight[$i]) $this->_winnerGame++;
			else $this->_looserGame++;
		}
		
		if ($this->_isAbort)
		{
			$this->_winnerGame = 2;
			$this->_looserGame = $this->_nbGame == 3 ? 1 : 0;
		}
		
		
		$this->_winnerPt = $aScoreLeft;
		$this->_looserPt = $aScoreRight;
		if (!$this->_isAbort &&
		$this->_winnerGame < $this->_looserGame)
		{
			$tmp = $this->_winnerGame;
			$this->_winnerGame = $this->_looserGame;
			$this->_looserGame = $tmp;
			$this->_winnerPt = $aScoreRight;
			$this->_looserPt = $aScoreLeft;
			return 0;
		}
		return 1;
	}


}
?>
