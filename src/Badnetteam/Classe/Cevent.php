<?php
require_once 'Classe.php';
require_once 'Object/Omatch.inc';

/**
 * Classe event
 */
class Cevent extends Classe
{

	static public function getLovRanking()
	{
		$lov = array ('T5;21' => 'T5',
		'T10;20' => 'T10',
		'T20;19' => 'T20',
		'T50;18' => 'T50',
		'A1;17' => 'A1',
		'A2;16' => 'A2',
		'A3;15' => 'A3',
		'A4;14' => 'A4',
		'B1;13' => 'B1',
		'B2;12' => 'B2',
		'B3;11' => 'B3',
		'B4;10' => 'B4',
		'C1;9' => 'C1',
		'C2;8' => 'C2',
		'C3;7' => 'C3',
		'C4;6' => 'C4',
		'D1;5' => 'D1',
		'D2;4' => 'D2',
		'D3;3' => 'D3',
		'D4;2' => 'D4',
        'NC;1' => 'NC'		
		);
		return $lov;
	}

	public function __construct()
	{
		parent::__construct('event', 'evnt_');
		$this->load();
	}

	public function isEmpty()
	{
		$name = $this->getVal('name');
		return empty($name);
	}

	public function getLiveMatchs()
	{
		$q = new Bn_query('match', '_team');
		$q->setFields('mtch_court, mtch_id, mtch_begin, mtch_disci, mtch_order');
		$q->setWhere('mtch_status=' . OMATCH_STATUS_LIVE);
		$q->setOrder('mtch_begin DESC');
		$matchs = $q->getRows();
		$list = array();
		foreach($matchs as $match)
		{
			$tmp['court'] = $match['mtch_court'];
			$tmp['id'] = $match['mtch_id'];
			$tmp['start'] = substr($match['mtch_begin'], 11);
			$tmp['label'] = constant('SMA_LABEL_'.$match['mtch_disci']) . ' '.  $match['mtch_order'];
			$list[$match['mtch_id']] = $tmp;
		}
		return $list;
	}

	public function setVal($aName, $aValue)
	{
		$this->_fields[$aName] = $aValue;
		return $aValue;
	}

	public function getVal($aName, $aDefault = null)
	{
		if ( isset($this->_fields[$aName]) ) $val = $this->_fields[$aName];
		else $val = $aDefault;
		return $val;
	}
}
?>
