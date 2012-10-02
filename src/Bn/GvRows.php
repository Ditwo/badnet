<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

/**
 * Classe pour la gestion des lignes pour les gridview
 */
class GvRows
{
	private $_numAuto = false;

	/**
	 * Constructeur
	 *
	 */
	public function __construct(Bn_query $aQuery = null)
	{
		if ( !is_null($aQuery) ) $this->_return = $aQuery->getGridParam();
		else
		{
			$this->_return->total = 1;
			$this->_return->page = 1;
			$this->_return->records = 0;
			$this->_autoNum  = true;
			$this->_sortIndex = Bn::getValue('sidx', 1)-1;
			$this->_sortOrder = Bn::getValue('sord', 'desc');
			if ($this->_sortIndex < 0) $this->_sortIndex  = 0;
		}
		$this->_numRow = 0;
	}

	/**
	 * Fixe les parametres de la grille
	 * @param $aPage		integer 	numero de la page
	 * @param $aNbPage  	integer		nombre de page
	 * @param $aNbRecords	integer		nombre total de lignes
	 * @return	void
	 *
	 */
	public function setParams($aPage, $aNbPage, $aNbRecords)
	{
		$this->_return->total = $aNbPage;
		$this->_return->page = $aPage;
		$this->_return->records = $aNbRecords;
	}

	/**
	 * Ajoute une ligne
	 * @param $aRow array	cellule de la ligne
	 * @param $aId	integer	identifiant de la ligne
	 */
	public function addRow($aRow, $aId = null)
	{
		if ( !is_null($aId) ) $this->_return->rows[$this->_numRow]['id'] = $aId;
		else	$this->_return->rows[$this->_numRow]['id'] = $this->_numRow;
		$this->_return->rows[$this->_numRow]['cell'] = $aRow;
		
		if ($this->_numAuto)
		{
			$this->_sort[$this->_numRow] = $aRow[$this->_sortIndex];
			$this->_return->records++;
		}
		$this->_numRow++;
	}

	/**
	 * Affichage des lignes
	 */
	public function display()
	{
		if ($this->_numAuto)
		{
			$order = $this->_sortOrder == 'asc' ? SORT_ASC : SORT_DESC;
			array_multisort($this->_sort, $order, $this->_return->rows);
		}
		header('Content-type: text/html; charset=utf-8');
		echo Bn::toJson($this->_return);
		return false;

	}
}
?>
