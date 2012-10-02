<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';

class Oseason extends Object
{
	// {{{ properties
	// }}}

	/**
	 * Renvoi la saison pour une date iso
	 */
	static public function getSeasonLabel($aDate, $aSeparator = '-')
	{
		list($year, $month, $day) = explode('-', $aDate);
		$curSeason = $year;
		if ($month > 8) $curSeason++;
		$label = $curSeason-1  . $aSeparator . $curSeason;
		return $label;
	}

	
	/**
	 * Renvoi l'indice de la saison pour une date iso
	 */
	static public function getSeason($aDate)
	{
		list($year, $month, $day) = explode('-', $aDate);
		$curSeason = $year-2006;
		if ($month >= 8) $curSeason++;
		return $curSeason;
	}

	/**
	 * Acces a la remiere annee de la saison
	 */
	static public function getYear($aSaison = null)
	{
		if ( empty($aSaison) || $aSaison < 0 ) $aSaison = Oseason::getCurrent();
		$first = 2006;
		for ($i=1; $i<20; $i++) $season[$i] = $first++;
		return $season[$aSaison];
	}
	
	/**
	 * Acces a la saison courante : 
	 * renvoi le numero de la saison courant. La premiere saison (1) est 2006-2007
	 */
	static public function getCurrent()
	{
		$date = getdate();
		$curSeason = $date['year']-2006;
		if ($date['mon'] > 8) $curSeason++;
		return $curSeason;
	}

	/**
	 * Acces a la saison courante 
	 * Renvoi la saison courante sous forme 2006[separator]2007
	 * Par defaut 2006-2007
	 */
	static public function getCurrentLabel($aSeparator = '-')
	{
		$date = getdate();
		$curSeason = $date['year'];
		if ($date['mon'] >= 8) $curSeason++;
		$label = $curSeason-1  . $aSeparator . $curSeason;
		return $label;
	}
	
	/**
	 * Liste des saisons
	 * @name get
	 *
	 */
	static public function get()
	{
		$year = 2005;
		for($i=1; $i<10; $i++)
		{
			$start = $year + $i;
			$end   = $year + 1 + $i;
			$seas[$i] = "{$start}-{$end}";
		}
		return $seas;
	}
}
?>
