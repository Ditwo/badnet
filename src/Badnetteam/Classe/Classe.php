<?php
require_once 'Classe.inc';

/**
 * Classe match
 */
class Classe
{

	public function __construct($aTable, $aPrefix, $aBase='_team')
	{
		$this->_table = $aTable;
		$this->_prefix = $aPrefix;
		$this->_db     = $aBase;
	}

	public function setVal($aName, $aValue)
	{
		$this->_fields[$aName] = $aValue;
		return $aValue;
	}

	public function unSetVal($aName)
	{
		$value = $this->_fields[$aName];
		unset($this->_fields[$aName]);
		return $aValue;
	}

	public function getVal($aName, $aDefault = null)
	{
		if ( isset($this->_fields[$aName]) ) $val = $this->_fields[$aName];
		else $val = $aDefault;
		return $val;
	}

	public function save($aWhere=null)
	{
		// Recuperer les listes des champs de la table
		$q = new BN_query($this->_table, $this->_db);
		$cols = $q->getColumnDef();

		// Filtrer les valeurs a mettre a jour
		foreach( $this->_fields as $key => $value)
		{
			if ( in_array($this->_prefix . $key, $cols) )
			{
				$q->addValue($this->_prefix . $key, $value);
			}
		}

		if (!empty($aWhere)) $q->setWhere($aWhere);
		else $q->setWhere($this->_prefix . 'id=' . $this->getVal('id', -1));
		$id = $q->replaceRow();
		$this->setVal('id', $id);
		return $id;
	}

	public function load($aWhere = null)
	{
		$q = new Bn_query($this->_table, $this->_db);
		$q->addField('*');
		if( !empty($aWhere)) $q->setWhere($aWhere);
		$fields = $q->getRow();
		if (!empty($fields))
		{
			foreach($fields as $field =>$value)
			{
				$token = explode('_', $field);
				$this->setVal($token[1],  $value);
			}
		}
		unset($q);
		return;
	}
}
?>