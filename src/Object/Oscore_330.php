<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Oscore.php';
require_once 'Opair.inc';


class Oscore_330 extends Oscore
{
    private $_resulth = OPAIR_RES_NOPLAY;
    private $_resultv = OPAIR_RES_NOPLAY;
    
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Fonction d'affichage
	 *
	 * @param Bn_balise $aDiv
	 * @return unknown
	 */
	public function display(Bn_balise $aDiv, $aId, $aScore, $aWinH=false, $aListNames, $aTabIndex=0)
	{
		$div = $aDiv->addDiv('', 'bn-score');
		$num = 1;
		$score = empty($aScore) ? "" : $aScore;

		$end = strtolower(substr($score, -2));
		if ($end == 'wo' || $end == 'ab') $score = substr($score, 0, strlen($score)-3);
		$games = explode(" ", $score);
		$nbGames = count($games);
		if ($nbGames > 1)
		{
			$scoreh = array();
			$scorev = array();
			for ($i=0; $i<$nbGames; $i++)
			{
				$points = explode("-", $games[$i]);
				$scoreh[] = !empty($points[0]) ? $points[0]:'00';
				$scorev[] = !empty($points[1]) ? $points[1]:'00';
			}
		}
		$dl = $div->addDiv('', 'bn-game-left bn-div-left');
		$dr = $div->addDiv('', 'bn-game-right bn-div-left');

		$edt = $dl->addEdit('sc_'.$aId.'_'.$num++, null, $scoreh[0], 2);
		$edt->noMandatory();
		$edt->getInput()->completeAttribute('class', 'bn-points');
		$edt->getInput()->setAttribute('tabindex', $aTabIndex+1);
		$dl->addContent($aListNames[0]);
		$dl->addBreak();

		$edt = $dr->addEdit('sc_'.$aId.'_'.$num++, null, $scorev[0], 2);
		$edt->noMandatory();
		$edt->getInput()->completeAttribute('class', 'bn-points');
		$edt->getInput()->setAttribute('tabindex', $aTabIndex+2);
		$dr->addContent($aListNames[1]);
		$dr->addBreak();

		if ( !empty($aListNames[2]) )
		{
			$edt = $dl->addEdit('sc_'.$aId.'_'.$num++, null, $scoreh[1], 2);
			$edt->noMandatory();
			$edt->getInput()->completeAttribute('class', 'bn-points');
			$edt->getInput()->setAttribute('tabindex', $aTabIndex+3);
			$dl->addContent($aListNames[2]);
			$dl->addBreak();
				
			$edt = $dr->addEdit('sc_'.$aId.'_'.$num++, null, $scorev[1], 2);
			$edt->noMandatory();
			$edt->getInput()->completeAttribute('class', 'bn-points');
			$edt->getInput()->setAttribute('tabindex', $aTabIndex+4);
			$dr->addContent($aListNames[3]);
			$dr->addBreak();
				
			$edt = $dl->addEdit('sc_'.$aId.'_'.$num++, null, $scoreh[2], 2);
			$edt->noMandatory();
			$edt->getInput()->completeAttribute('class', 'bn-points');
			$edt->setAttribute('tabindex', $aTabIndex+5);
			$chk = $dl->addCheckbox('abh_'.$aId, 'Ab', 1, $end == 'ab' && $aWinH);
			$chk->setAttribute('tabindex', $aTabIndex+8);
			$chk = $dl->addCheckbox('woh_'.$aId, 'Wo', 1, $end == 'wo' && $aWinH);
			$chk->setAttribute('tabindex', $aTabIndex+7);
			$dl->addBreak();

			$edt = $dr->addEdit('sc_'.$aId.'_'.$num++, null, $scorev[2], 2);
			$edt->noMandatory();
			$edt->getInput()->completeAttribute('class', 'bn-points');
			$edt->setAttribute('tabindex', $aTabIndex+6);
			
			$chk = $dr->addCheckbox('abv_'.$aId, 'Ab', 1, $end == 'ab' && !$aWinH);
			$chk->setAttribute('tabindex', $aTabIndex+9);
			$chk = $dr->addCheckbox('wov_'.$aId, 'Wo', 1, $end == 'wo' && !$aWinH);
			$chk->setAttribute('tabindex', $aTabIndex+10);
			$dr->addBreak();

		}
		else
		{
			$edt = $dl->addEdit('sc_'.$aId.'_'.$num++, null, $scoreh[1], 2);
			$edt->noMandatory();
			$edt->getInput()->completeAttribute('class', 'bn-points');
			$edt->getInput()->setAttribute('tabindex', $aTabIndex+3);
			$chk = $dl->addCheckbox('abh_'.$aId, 'Ab', 1, $end == 'ab' && $aWinH);
			$chk->getInput()->setAttribute('tabindex', $aTabIndex+8);
			$chk = $dl->addCheckbox('woh_'.$aId, 'Wo', 1, $end == 'wo' && $aWinH);
			$chk->getInput()->setAttribute('tabindex', $aTabIndex+7);
			$dl->addBreak();

			$edt = $dr->addEdit('sc_'.$aId.'_'.$num++, null, $scorev[1], 2);
			$edt->noMandatory();
			$edt->getInput()->completeAttribute('class', 'bn-points');
			$edt->getInput()->setAttribute('tabindex', $aTabIndex+4);
			$chk = $dr->addCheckbox('abv_'.$aId, 'Ab', 1, $end == 'ab' && !$aWinH);
			$chk->getInput()->setAttribute('tabindex', $aTabIndex+9);
			$chk = $dr->addCheckbox('wov_'.$aId, 'Wo', 1, $end == 'wo' && !$aWinH);
			$chk->getInput()->setAttribute('tabindex', $aTabIndex+10);
			$dr->addBreak();
				
			$edt = $dl->addEdit('sc_'.$aId.'_'.$num++, null, $scoreh[2], 2);
			$edt->noMandatory();
			$edt->getInput()->completeAttribute('class', 'bn-points');
			$edt->getInput()->setAttribute('tabindex', $aTabIndex+5);
			$dl->addBreak();
				
			$edt = $dr->addEdit('sc_'.$aId.'_'.$num++, null, $scorev[2], 2);
			$edt->noMandatory();
			$edt->getInput()->completeAttribute('class', 'bn-points');
			$edt->getInput()->setAttribute('tabindex', $aTabIndex+6);
			$dr->addBreak();
		}

		$div->addBreak();
		return $aTabIndex+11;
	}

	/**
	 * Recupere le score saisi
	 *
	 * @param id du match $aId
	 * @return unknown
	 */
	public function getValue($aId)
	{
		$scs = array();
		$num = 1;
		$this->_resulth = OPAIR_RES_WIN;
		$this->_resultv = OPAIR_RES_LOOSE;
		$abh = Bn::getValue('abh_'.$aId, 0);
		$abv = Bn::getValue('abv_'.$aId, 0);
		for($i=1; $i<=$this->_nbMaxGame; $i++)
		{
			$h = Bn::getValue('sc_'.$aId.'_'.$num++);
			$v = Bn::getValue('sc_'.$aId.'_'.$num++);
			if ( !empty($h) && !empty($v) ) $scs[] = $h . '-' . $v;
		}
		$score = implode(' ', $scs);
		if (!empty($abh) || !empty($abv) ) $score .= ' Ab';
		
		if (Bn::getValue('woh_'.$aId, 0)) $score = $this->wov();
		else if (Bn::getValue('wov_'.$aId, 0)) $score = $this->woh();
		else
		{
			$this->setScore($score);
			if ( !$this->isWinner())
			{
				$this->_resultv = OPAIR_RES_WIN;
				$this->_resulth = OPAIR_RES_LOOSE;
			}
			if (!empty($abh) )
			{
				$this->_isWinner = false;
				$this->_resultv = OPAIR_RES_WINAB;
				$this->_resulth = OPAIR_RES_LOOSEAB;
			}
			if (!empty($abv) )
			{
				$this->_resulth = OPAIR_RES_WINAB;
				$this->_resultv = OPAIR_RES_LOOSEAB;
			}
		}
		return $score;
	}
	
	public function getResulth()
	{
		return $this->_resulth;
	}

	public function getResultv()
	{
		return $this->_resultv;
	}
	
	/**
	 * Fixe le score a WO pour l'hote du match (ou gauche ou top)
	 *
	 * @return unknown
	 */
	public function woh()
	{
		$score = '21-0 21-0 WO';
		$this->setScore($score);
		$this->_resulth = OPAIR_RES_WINWO;
		$this->_resultv = OPAIR_RES_LOOSEWO;
		return $score;
	}

	/**
	 * Fixe le score a WO pour le visiteur du match (ou droite ou bottom)
	 *
	 * @return unknown
	 */
	public function wov()
	{
		$score = '0-21 0-21 WO';
		$this->setScore($score);
		$this->_resultv = OPAIR_RES_WINWO;
		$this->_resulth = OPAIR_RES_LOOSEWO;
		return $score;
	}

}

?>