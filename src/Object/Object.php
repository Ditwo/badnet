<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

require_once 'Bn/Bn.php';
require_once 'Object.inc';


class Object extends BN_error
{
	private $_fields = null;    // Champs de l'objet

	/**
	 * Constructeur
	 */
	private function __construct()
	{
	}

	public function __destruct()
	{
		unset($this->_fields);
	}

	/**
	 * Met a jour un enregistrement
	 * @param $aTable  Table a mettre a jour
	 * @param $aPrefix Prefix de la table (ne pas oublier le _)
	 * @param $aData   Tableau associatif des champs a mettre a jour
	 * @param $aWhere  Clause de rechercher
	 * @param $aBase   Base de donnée a utiliser
	 * @param $aReplace Creer l'enregistrement s'il n'existe pas (oui par defaut)
	 * @return id de l'enregistrement
	 */
	protected function update($aTable, $aPrefix, $aData, $aWhere, $aBase ='', $aReplace = true)
	{
		// Recuperer les listes des champs de la table
		$q = new BN_query($aTable, $aBase);
		$cols = $q->getColumnDef();
	
		$id = $this->getVal('id', -1);
		if ($id == -1) $this->delVal('id');
		
		// Filtrer les valeurs a mettre a jour
		foreach( $aData as $key => $value)
		{
			if ( in_array(strtolower($aPrefix.$key), $cols) )
			{
				$q->addValue($aPrefix.$key, $value);
			}
		}

		$q->setWhere($aWhere);
		if ($aReplace)
		{
			$id = $q->replaceRow();
		}
		else
		{
			$id = $q->updateRow();
		}
		if ( $q->isError() )
		{
			$this->_error($q->getMsg());
			BN::setUserMsg($q->getMsg());
		}
		unset($q);
		$this->setVal('id', $id);
		return $id;
	}

	/**
	 * Charge un enregistrement depuis la base de donnees
	 * @param string $aTable	nom de la table
	 * @param string $aWhere    Clause de recherche de l'enregistrent
	 * @param string $aDatabase Base de données
	 *
	 */
	public function load($aTable, $aWhere, $aDatabase = null)
	{
		$q = new Bn_query($aTable, $aDatabase);
		$q->addField('*');
		$q->addWhere( $aWhere);
		$fields = $q->getRow();
		if (!empty($fields))
		{
			foreach($fields as $field =>$value)
			{
				$token = explode('_', $field);
				$this->setVal($token[1],  $value);
			}
		}
		//print_r($q);
		unset($q);
	}

	/**
	 * Acces a la valeur d'un champ
	 * @param string $aName	nom du champ a recuperer
	 *
	 */
	public function getValue($aName, $aDefault='')
	{
		return $this->getVal($aName, $aDefault);
	}

	/**
	 * Acces a la valeur d'un champ
	 * @param string $aName	nom du champ a recuperer
	 *
	 */
	public function getVal($aName, $aDefault='')
	{
		if ( isset($this->_fields[$aName]) ) $val = $this->_fields[$aName];
		else $val = $aDefault;

		//if ($aName == 'id' && $val <= 0) Bn::log(debug_backtrace());
		return $val;
	}

	/**
	 * Acces a tous les champs
	 */
	public function getValues()
	{
		return $this->_fields;
	}

	/**
	 * Initialiser les champs
	 * @param string $aName	tableau des champs a initialiser
	 * @param boolean $aInit  indicateur de re-initialisation des champs
	 *
	 */
	public function setValues($aFields, $aInit = false)
	{
		if ($aInit) unset($this->_fields);
		if ( is_array($aFields) ) foreach($aFields as $name=>$value) $this->_fields[$name] = $value;
	}

	/**
	 * Initialiser un champ
	 * @param string $aName	nom du champ a recuperer
	 *
	 */
	public function setValue($aName, $aValue)
	{
		$this->setVal($aName, $aValue);
	}

	/**
	 * Initialiser un champ
	 * @param string $aName	nom du champ
	 * @param string $aValue valeur du champ
	 * 	 *
	 */
	public function setVal($aName, $aValue)
	{
		$this->_fields[$aName] = $aValue;
	}

	/**
	 * Supprimer un champ
	 * @param string $aName	nom du champ a supprimer
	 *
	 */
	public function delVal($aName)
	{
		unset($this->_fields[$aName]);
	}
	
	public function isEmpty()
	{
		return $this->getVal('id', -1) <= 0;
	}

	public function nodeValue($aNode, $aElement, $aDefault = null)
	{
		$nodeList = $aNode->getElementsByTagName($aElement);
		if ($nodeList != null) return $nodeList->item(0)->nodeValue;
		else return $aDefault;
	}
	
}
?>
