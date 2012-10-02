<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once "Bn/Db.php";
require_once 'Bn/Json.php';


class GridView extends Bn_Balise
{
	private $_scriptIndice=null;
	private $_name=null;          // Nom de la gridview
	private $_colarray=array();   // Tableau de colonne
	private $_actions = array();  // Action de la datagrid
	private $_options = array();  // Option de construction de la grid

	/**
	 * Constructeur
	 * @param 	string	$aName		Nom de la grille
	 * @param	integer	$aAction	Action a effectuer pour remplir la grille
	 * @param	integer $aNumRow	Nombre de ligne a afficher
	 * @param 	boolean	$aPager		Indicateur d'affichage du pager
	 * @return  GridView
	 */
	public function __construct($aName, $aAction, $aRowNum=25, $aPager=false)
	{
		//creation du container
		parent::__construct('div', 'divGrid'.$aName);
		$this->setAttribute('class', 'bngrid');
		$table = $this->addBalise('table', $aName);

		$this->_name = $aName;
		$this->_options['url'] = "'index.php?ajax=1&bnAction=" . $aAction ."'";
		$this->_options['rowNum'] = $aRowNum;
		$this->_options['rowList'] = '[25,50,75,100]';
		$theme = Bn::getValue('theme', 'Badnet');
		$this->_options['imgpath'] = "'../Themes/$theme/Gridview/images'";
		
		$this->_options['mtype'] = "'post'";
		$this->_options['datatype'] = "'json'";
		$this->_options['hidegrid'] = 'false';
		$this->_options['hoverrows'] = 'false';
		$this->_options['gridview'] = 'true';
		
		//ajout du container du pager
		if ($aPager){
			$pager = $this->addDiv('pagerGrid'.$aName);
			$this->_options['pager'] = "$('#pagerGrid" . $aName . "')";
			$this->_options['viewrecords'] = 'true';
		}
		$this->_createGrid();

		//ajout des scripts
		//$page =& Bn_Page::getPage();
	}
	
	/**
	 * Active la multiselection de ligne
	 */
	public function multiselect()
	{
		$this->addOption('multiselect', "'true'");
		$this->addOption('hoverrows', 'true');
	}
	
	/**
	 * Fixe les options de presentation de la grid
	 * @name setLook
	 * @param	string	aCaption	Titre de la grille
	 * @param 	integer aWidth		Largeur de la grille
	 * @param 	integer aHeight		Hauteur de la grille
	 * @param 	boolean	aHide		Masquage de la grille
	 */
	public function setLook($aCaption=null, $aWidth=0, $aHeight=0, $aHide =true)
	{
		if ( $aWidth )
		{
			$this->_options['width'] = $aWidth;
		}

		if ( $aHeight )
		{
			$this->_options['height'] = $aHeight;
		}
		$this->_options['caption'] = "'" . addslashes($aCaption) ."'";
		if ( !$aHide )
		{
			$this->_options['hidegrid'] = 'false';
		}
		$this->_createGrid();
	}

	/**
	 * Ajoute une option a la grille
	 * @param 	string	$aOption	nom de l'option
	 * @param 	mixed   $aValue		valeur de l'option
	 */
	function addOption($aOption, $aValue)
	{
		$this->_options[$aOption] = $aValue;
		$this->_createGrid();
	}

	/**
	 * Supprime une option
	 * @param 	string	$aOption	nom de l'option
	 */
	function deleteOption($aOption)
	{
		unset($this->_options[$aOption]);
		$this->_createGrid();
	}
	
	/**
	 * Retourne une option
	 * @param 	string	$aOption	nom de l'option
	 */
	function getOption($aOption)
	{
		if (isset($this->_options[$aOption]) ) return $this->_options[$aOption];
		else return null; 
	}
	
	/**
	 * Ajoute la definition d'une colonne
	 * @param	string	$aTitle		titre de la colonne
	 * @param  	string  $aName		nom de la colonne
	 * @param 	boolean	$aSort		La colonne est-elle triable
	 * @param 	boolean $aOrder		Je sais pas @todo
	 * @param   boolean	$aHidden	La colonne est-elle cachee
	 */
	public function addColumn($aTitle, $aName, $aSort=true, $aOrder=false, $aHidden=false)
	{
		$index = count($this->_colarray)+1;
		$col = new GvCol($aTitle, $index, $aSort, $aHidden);
		$this->_colarray[] =& $col;
		if ( $aOrder)
		{
			$this->_options['sortname'] = count($this->_colarray);
		}
		$this->_createGrid();
		return $col;
	}

	/**
	 * Fixe l'action pour selectionner une ligne
	 * @param	string		$aCallback	Fonction javascript
	 * @param	integer		$aAction	Action a effectuer
	 * @return void
	 */
	public function setSelectRow($aCallback, $aAction=0)
	{
		$javascript = "function(aRow){
		$aCallback(aRow, $aAction);
	}";
	$this->addOption('onSelectRow', $javascript);
	}

	/**
	 * Fixe l'action pour le sauvegarde d'une ligne editee
	 *
	 * @param int $aAction  action de la sauvegarde
	 */
	public function setEditRow($aAction)
	{
	$this->addOption('editurl', "'index.php?ajax=true&bnAction=" . $aAction ."'");
	}
	
	/**
	 * Fixe l'action pour le sauvegarde d'une ligne editee
	 *
	 * @param int $aAction  action de la sauvegarde
	 */
	public function setEditCell($aAction, $aCallBack)
	{
	$this->addOption('cellurl', "'index.php?ajax=true&bnAction=" . $aAction ."'");
	$this->addOption('afterSaveCell', $aCallBack);
	}
		
	/**
	 * Fixe l'action pour voir le detail d'une ligne
	 * @param	integer		$aAction	Action a effectuer
	 */
	public function setDetailAction($aAction, $aName='rowid')
	{
		$theme = Bn::getValue('theme', 'Badnet');		
		$this->_actions[] = array(
		'name' => 'view',
		'img' =>  "'../Themes/$theme/Img/magnifier.png'",
		'url' =>  'index.php?ajax=true&bnAction=' . $aAction 
		. '&' . $aName . '=\'+id+\''
		);
		$this->_createGrid();
	}

	/**
	 * Fixe l'action pour supprimer une ligne
	 * @param	integer		$aAction	Action a effectuer
	 */
	public function setDeleteAction($aAction, $aMsg)
	{
		$theme = Bn::getValue('theme', 'Badnet');		
		$this->_actions[] = array(
		'name' => 'del',
		//'url' => 'index.php?bnAction=' . $aAction,
		'img' => "'../Themes/$theme/Img/delete.png'",
		'js' =>  'jQuery("#' . $this->_name . '").delGridRow(\'+id+\', {'
		. 'url:"index.php?bnAction=' . $aAction .'"'
		//. ', drag: true'
		. ', modal: true'
		. ', msg:"' . $aMsg .'"'
		. ', caption: "Suppression"'
		. ', bSubmit: "Supprimer"'
		. ', bCancel: "Abandonner"'
		. '});'
		);
		$this->_createGrid();
	}

	/**
	 * Fixe l'action pour editer une ligne
	 * @name setEditAction
	 * @param integer	$aAction	Action a affectuer
	 * @param boolean	$aModal		La fenetre est-elle modale
	 */
	public function setEditAction($aAction, $aModal=true)
	{
		$theme = Bn::getValue('theme', 'Badnet');		
		$js =  'jQuery("#' . $this->_name . '").editGridRow("\'+id+\'", {'
		. 'url:"index.php?bnAction=' . $aAction .'"'
		. ', addCaption: "Ajout"'
		. ', editCaption: "Modification"'
		. ', bSubmit: "Enregistrer"'
		. ', bCancel: "Abandonner"';
		if ( $aModal )
		$js .= ', modal: true';
		$js .= '});';

		$this->_actions[] = array(
		'name' => 'edit',
		//'url' => 'index.php?bnAction=' . $aAction,
		'img' =>  "'../Themes/$theme/Img/edit.png'",
		'js'  => $js 
		);
		$this->_createGrid();
	}

	/**
	 * Ajoute sous grille a la grille
	 * @param 	GridView $aGrid		sous grille
	 */
	public function setSubgrid(GridView $aGrid)
	{
		$this->deleteOption('gridview');
		
		$this->addOption('subGrid', 'true');
		$str = "function(subgrid_id, row_id) {\n";
		$str .= 'var subgrid_table_id,pager_id;'."\n";
    	$str .= 'subgrid_table_id = subgrid_id+"_t";'."\n";
    	$str .= 'pager_id = "p_"+subgrid_id;'."\n";
    	$str .= '$("#"+subgrid_id).html("<table id=\'"+subgrid_table_id+"\' ></table><div id=\'"+pager_id+"\' class=\'scroll\'></div>");'."\n";
    	$str .= 'jQuery("#"+subgrid_table_id).jqGrid({'."\n";
        $url = $aGrid->getoption('url');
        $url .= "+'&q=2&id='+row_id";
        $aGrid->addOption('url', $url);
        $str .= $aGrid->getJsOptions();
		$str .= '});}'; 
		$this->addOption('subGridRowExpanded', $str);
		$this->_createGrid();
	}

	/**
	 * Groupement suivant une colonne
	 * @param 	GridView $aGrid		sous grille
	 */
	public function setGroup($aColumn, $aOrder='asc', $aShow=false)
	{
		$this->deleteOption('subGrid');
		$this->deleteOption('treeGrid');
		$this->deleteOption('scroll');
		$this->deleteOption('rownumbers');
		$this->addOption('gridview', 'true');
		
		$this->addOption('grouping', 'true');
		$str = " {\n";
		$str .= "groupField : ['". $aColumn . "']\n";
		$str .= ",groupDataSorted : true\n";
		if ($aShow) $str .= ",groupColumnShow : [true]\n";
		else $str .= ",groupColumnShow : [false]\n";
		$str .= ",groupText : ['{0} - {1} Item(s)']\n";
		//$str .= ",groupCollapse : true\n";
		$str .= ",groupOrder : ['" . $aOrder . "']\n";
		$str .= " }\n";
		$this->addOption('groupingView', $str);
		$this->_createGrid();
	}
	
	public function _createGrid()
	{
		$lnEnd = $this->_getLineEnd();
		$script = "$('#" . $this->_name . "').jqGrid({" . $lnEnd;
		$script .= $this->getJsOptions();
		$script .= '});'  . $lnEnd;
		$this->_scriptIndice = $this->addJQReady($script, $this->_scriptIndice);
	}
	
	/**
	 * _createGrid : permet de construire le code javascript du gridview
	 *
	 * @access  private
	 */
	public function getJsOptions()
	{
		$lnEnd = $this->_getLineEnd();

		$script = '';

		// Ajout des specifications des colonnes
		$colsname = ", colNames: [";
		$colsmodel = ", colModel: [";
		$glue = '';
		$i = 1;
		foreach($this->_colarray as $col)
		{
			if ( $col->isInitSort() )
			{
				$this->_options['sortname'] =  $i;
				$this->_options['sortorder'] =  "'". $col->getInitOrder() . "'";
			}
			$colsname .= $glue . "'" . $col->getTitle() . "'";
			$colsmodel .=  $glue . $col->_toScript();
			$glue = ',';
			$i++;
		}

		// Ajout de la colonne d'action
		if (count($this->_actions) && $colsname)
		{
			$colsname .= ", 'Action'";
			$width = max(50, 16*count($this->_actions));
			$colsmodel .= ",{name:'act'"
			.",index:'act'"
			.",sortable:false"
			.",editable:false"
			.",align:'center'"
			.",width:" . $width
			."}";
		}

		foreach($this->_options as $key=>$value)
		{
			$options[] = $key . ':' . $value;
		}
		$script .= implode("\n,", $options);
		$script .= $colsname .']' . $lnEnd;
		$script .= $colsmodel . ']' . $lnEnd;

		// Ajout des actions
		$script .= $this->_getActionScript();
		return $script;
	}

	/**
	 * Renvoi le script pour ajouter les actions
	 *
	 * @access  private
	 */
	private function _getActionScript()
	{
		if ( ! count($this->_actions) )
		{
			return '';
		}
		$grid = $this->_name;
		$script = ", loadComplete: function(){
   var ids = jQuery('#" . $grid . "').getDataIDs();
   for(var i=0;i<ids.length;i++)
   { 
      var id = ids[i];\n";

		foreach($this->_actions as $action)
		{
			$names[] = $action['name'];
			$str = '   ' . $action['name'] . "='";

			$bal = new Bn_balise();
			if ( !empty($action['url']) )
			{
				$lnk = $bal->addLink('', $action['url']);				
				$img = $lnk->addImage('', $action['img'], $action['name']);
			}
			if ( !empty($action['js']) )
			{
				$img = $bal->addImage('img'.$action['name'], $action['img'], $action['name']);
				$img->setAttribute('onclick', $action['js']);
			}
			$script .= '   ' . $str . $bal->toHtml() . "';\n";
		}
		$act = implode('+', $names);
		$script .= "      jQuery('#" . $grid . "').setRowData(id, {act:".$act."});\n   }\n}";
		return $script;
	}
}

/**
 * Cette classe gere la definition d'une colonne de la table dynamique
 *
 */
class GvCol
{
	private $_initSort = false;
	private $_initOrder = '';
	// {{{
	/**
	* Constructeur
	* @param	string	$aTitle		titre de la colonne
	* @param	string	$aName		nom de la colonne
	* @param	boolean	$aSort		la colonne est-elle triable
	* @param 	boolean	$aHidden	la colonne est-elle cachee
	* @access  public
	*/
	function __construct($aTitle, $aName, $aSort=true, $aHidden=false)
	{
		$this->_title = $aTitle;
		$this->_options['name'] = "'$aName'";
		if ( !$aSort )
		{
			$this->_options['sortable'] = 'false';
		}
		if ( $aHidden )
		{
			$this->_options['hidden'] = 'true';
		}
	}

	/**
	 * Ajoute une option a la colonne
	 * @name addOption
	 * @param 	string	$aOption	nom de l'option
	 * @param 	mixed   $aValue		valeur de l'option
	 */
	function addOption($aOption, $aValue)
	{
		$this->_options[$aOption] = $aValue;
	}

	/**
	 * Tri de la colonne au premier affichage
	 * @param 	string	$aSort	ordre de tri desc ou asc par defaut
	 */
	function initSort($aSort = 'asc')
	{
		$this->_initSort = true;
		$this->_initOrder = $aSort;
	}

	/**
	 * Tri initial de la colonne
	 */
	function isInitSort()
	{
		return $this->_initSort;
	}

	/**
	 * Ordre de tri initial de la colonne
	 */
	function getInitOrder()
	{
		return $this->_initOrder;
	}


	/**
	 * Fixe les options d'editions de la colonne
	 * @name setEdit
	 * @param 	string	$aType	    type de la colonne ('text', 'select', ...)
	 * @param   mixed	$aOptions 	tableaux des options clef=>valeur
	 * @param	mixed	$aValues	valeurs des options pour une liste texte=>value
	 */
	public function setEdit($aType='text', $aOptions=array(), $aValues=array())
	{
		$this->_options['editable'] = 'true';
		$this->_options['editype'] =  $aType;

		// Traitement des attributs
		$options = array();
		foreach ($aOptions as $key=>$value)
		{
			$options[] = $key . ':' . $value;
		}

		// List des options pour un select
		if ( $aType == 'select')
		{
			if ( is_array($aValues) )
			{
				$list = array();
				foreach ($aValues as $key=>$value)
				{
					$list[] = $key . ':' . $value;
				}
				$options[] = 'value:"' . implode(';', $list) . '"';
			}
			else
			{
				$options[] = 'value:"' . $aValues . '"';
			}
		}

		// Construction des options
		$option = implode(', ', $options);
		$this->_options[] = 'editoptions:{' . $option . '}';
	}

	/**
	 * Fixe les options de presentation de la colonne
	 * @name setLook
	 * @param 	integer	$aWidth		Largeur initiale de la colonne
	 * @param	string	$aAlign		Alignement du texte ('left', 'right', 'center')
	 * @param   boolean $aResize	Redimensionnement dela colonne
	 */
	function setLook($aWidth, $aAlign='left', $aResize=true)
	{
		$this->_options['width'] = $aWidth;
		$this->_options['align'] = "'" . $aAlign . "'";
		if ( !$aResize )
		{
			$this->_options['resizable'] = 'false';
		}
	}

	/**
	 * Renvoi le titre de la colonne
	 * @name getTitle
	 */
	public function getTitle()
	{
		return addslashes($this->_title);
	}

	/**
	 * Retourne la definition du type de colonne
	 *
	 */
	private function _typeToText()
	{
		if ( $this->_type != "texte" )
		{
			$l_s_type = "'{$this->_type}'";
		}
		$l_s_option = "";
		if ( count($this->_option) )
		{
			$l_s_option = "editoption:{";
			$l_s_option .= implode(',', $this->_option);
			$l_s_option .= "}";
		}
		return $l_s_option;
	}

	/**
	 * Retourne le script lie a la definiton de la colonne
	 */
	public function _toScript()
	{
		foreach($this->_options as $key=>$value)
		{
			$options[] = $key . ':' . $value;
		}
		$colModel = '{' . implode(', ', $options) . "}\n";;
		return $colModel;
	}
	// }}}
}

?>