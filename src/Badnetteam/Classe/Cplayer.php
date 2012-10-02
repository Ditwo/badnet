<?php

/**
 * Classe player
 */
class Cplayer
{

	/**
	 * Renvoi la liste des matchs du joueur pour une rencontre
	 *
	 * @param unknown_type $aTieId
	 */
	public function getMatchIds($aTieId)
	{
		$id = $this->getVal('id');
		$q = new Bn_query('match', '_team');
		$q->setFields('mtch_id');
		$where = '((mtch_playh1id=' . $id .')'.
		   ' OR (mtch_playh2id=' . $id .')'.
		   ' OR (mtch_playv1id=' . $id .')'.
		   ' OR (mtch_playv2id=' . $id .'))';
		$q->addWhere($where);
		$q->addWhere('mtch_tieid=' . $aTieId);
		$matchIds = $q->getCol();
		return $matchIds;
	}

	public function __construct($aPlayerId)
	{
		$q = new Bn_query('player', '_team');
		$q->addField('*');
		$q->setWhere('play_id=' . $aPlayerId);
		$fields = $q->getRow();
		if (!empty($fields))
		{
			foreach($fields as $field =>$value)
			{
				$token = explode('_', $field);
				$this->setVal($token[1],  $value);
			}
		}
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

	public function save()
	{
		// Recuperer les listes des champs de la table
		$q = new BN_query('player', '_team');
		$cols = $q->getColumnDef();

		// Filtrer les valeurs a mettre a jour
		foreach( $this->_fields as $key => $value)
		{
			if ( in_array('play_'.$key, $cols) )
			{
				$q->addValue('play_'.$key, $value);
			}
		}

		$q->setWhere('play_id=' . $this->getVal('id', -1));
		$id = $q->updateRow();
		return $id;
	}

}
?>